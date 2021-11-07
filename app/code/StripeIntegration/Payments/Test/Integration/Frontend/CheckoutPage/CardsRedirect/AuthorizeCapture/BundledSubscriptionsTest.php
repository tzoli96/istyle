<?php

namespace StripeIntegration\Payments\Test\Integration\Frontend\CheckoutPage\CardsRedirect\AuthorizeCapture;

class BundledSubscriptionsTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->tests = $this->objectManager->get(\StripeIntegration\Payments\Test\Integration\Helper\Tests::class);

        $this->cartManagement = $this->objectManager->get(\Magento\Quote\Api\CartManagementInterface::class);
        $this->webhooks = $this->objectManager->get(\StripeIntegration\Payments\Helper\Webhooks::class);
        $this->request = $this->objectManager->get(\Magento\Framework\App\Request\Http::class);
        $this->helper = $this->objectManager->get(\StripeIntegration\Payments\Helper\Generic::class);
        $this->stripeConfig = $this->objectManager->get(\StripeIntegration\Payments\Model\Config::class);
        $this->quote = new \StripeIntegration\Payments\Test\Integration\Helper\Quote();
        $this->subscriptions = $this->objectManager->get(\StripeIntegration\Payments\Helper\Subscriptions::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/checkout_mode 1
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     */
    public function testDynamicBundleMixedTrialCart()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart("DynamicBundleMixedTrial")
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("StripeCheckoutCard");

        $quote = $this->quote->getQuote();

        // Checkout totals should be correct
        $trialSubscriptionsConfig = $this->subscriptions->getTrialingSubscriptionsAmounts($quote);

        $this->assertEquals(40, $trialSubscriptionsConfig["subscriptions_total"], "Subtotal");
        $this->assertEquals(40, $trialSubscriptionsConfig["base_subscriptions_total"], "Base Subtotal");

        $this->assertEquals(20, $trialSubscriptionsConfig["shipping_total"], "Shipping");
        $this->assertEquals(20, $trialSubscriptionsConfig["base_shipping_total"], "Base Shipping");

        $this->assertEquals(0, $trialSubscriptionsConfig["discount_total"], "Discount");
        $this->assertEquals(0, $trialSubscriptionsConfig["base_discount_total"], "Base Discount");

        $this->assertEquals(3.3, $trialSubscriptionsConfig["tax_total"], "Tax");
        $this->assertEquals(3.3, $trialSubscriptionsConfig["tax_total"], "Base Tax");

        // Place the order
        $order = $this->quote->placeOrder();

        $orderIncrementId = $order->getIncrementId();
        $currency = $order->getOrderCurrencyCode();
        $expectedChargeAmount = $order->getGrandTotal()
            - $trialSubscriptionsConfig["subscriptions_total"]
            - $trialSubscriptionsConfig["shipping_total"]
            + $trialSubscriptionsConfig["discount_total"]
            - $trialSubscriptionsConfig["tax_total"];

        $expectedChargeAmount = $this->helper->convertMagentoAmountToStripeAmount($expectedChargeAmount, $currency);

        // Retrieve the created session
        $checkoutSessionId = $order->getPayment()->getAdditionalInformation('checkout_session_id');
        $this->assertNotEmpty($checkoutSessionId);

        $stripe = $this->stripeConfig->getStripeClient();
        $session = $stripe->checkout->sessions->retrieve($checkoutSessionId);

        $this->assertEquals($expectedChargeAmount, $session->amount_total);

        // Confirm the payment
        $paymentMethod = $stripe->paymentMethods->create([
          'type' => 'card',
          'card' => [
            'number' => '4242424242424242',
            'exp_month' => 7,
            'exp_year' => 2022,
            'cvc' => '314',
          ],
          'billing_details' => [
            'address' => [
                'city' => 'New York',
                'country' => 'US',
                'line1' => '1255 Duncan Avenue',
                'postal_code' => "10013",
                'state' => "New York"
            ],
            'email' => 'jerryflint@example.com',
            'name' => 'Jerry Flint',
            'phone' => "917-535-4022"
          ],
        ]);

        $params = [
            'eid' => 'NA',
            'payment_method' => $paymentMethod->id,
            'expected_amount' => $session->amount_total,
            'expected_payment_method_type' => 'card'
        ];

        $response = $stripe->request('post', "/v1/payment_pages/{$session->id}/confirm", $params, $opts = null);

        // Assert order status, amount due, invoices
        $this->assertEquals("new", $order->getState());
        $this->assertEquals("pending", $order->getStatus());
        $this->assertEquals(0, $order->getInvoiceCollection()->count());

        // Stripe subscription checks
        $customer = $stripe->customers->retrieve($session->customer);
        $this->assertCount(1, $customer->subscriptions->data);
        $subscription = $customer->subscriptions->data[0];
        $this->assertEquals("trialing", $subscription->status);
        $this->assertEquals(6330, $subscription->items->data[0]->price->unit_amount);

        $subscriptionId = $subscription->id;

        // Process the charge.succeeded event
        $paymentIntentId = $response->payment_intent->id;
        $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);
        $charge =  $paymentIntent->charges->data[0];
        $this->tests->event()->trigger("charge.succeeded", $charge, $this);

        // Process invoice.payment_succeeded event
        $ordersCount = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->count();
        $customer = $stripe->customers->retrieve($session->customer);
        $invoiceId = $customer->subscriptions->data[0]->latest_invoice;
        $this->tests->event()->trigger("invoice.payment_succeeded", $invoiceId, $this);

        // Ensure that no new order was created
        $newOrdersCount = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->count();
        $this->assertEquals($ordersCount, $newOrdersCount);

        // Refresh the order object
        $order = $this->helper->loadOrderByIncrementId($orderIncrementId);

        // Assert order status, amount due, invoices, invoice items, invoice totals
        $this->assertEquals("processing", $order->getState());
        $this->assertEquals("processing", $order->getStatus());
        $this->assertEquals(63.3, $order->getTotalDue());
        $this->assertEquals(1, $order->getInvoiceCollection()->count());

        // End the trial
        $stripe->subscriptions->update($subscriptionId, ['trial_end' => "now"]);
        $subscription = $stripe->subscriptions->retrieve($subscriptionId, ['expand' => ['latest_invoice']]);

        $ordersCount = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->count();

        // Trigger webhook events for the trial end
        $this->tests->event()->trigger("charge.succeeded", $subscription->latest_invoice->charge, $this);

        $this->tests->event()->trigger("invoice.payment_succeeded", $subscription->latest_invoice->id, $this);

        // Check that the order invoice was marked as paid
        $order = $this->helper->loadOrderByIncrementId($order->getIncrementId());
        $this->assertEquals(84.95, $order->getTotalPaid());
        $this->assertEquals(0, $order->getTotalDue());
        $invoicesCollection = $order->getInvoiceCollection();
        $invoice = $invoicesCollection->getFirstItem();
        $this->assertEquals(\Magento\Sales\Model\Order\Invoice::STATE_PAID, $invoice->getState());
        $this->assertEquals($paymentIntentId, $invoice->getTransactionId());

        // Check that the transaction IDs have been associated with the order
        $transactions = $this->helper->getOrderTransactions($order);
        $this->assertEquals(2, count($transactions));
        foreach ($transactions as $key => $transaction)
        {
            if ($transaction->getTxnId() == $subscription->latest_invoice->payment_intent)
            {
                $this->assertEquals("capture", $transaction->getTxnType());
                $this->assertTrue($transaction->getAdditionalInformation("is_subscription"));
                $this->assertEquals(63.3, $transaction->getAdditionalInformation("amount"));
            }
            else
            {
                $this->assertEquals($paymentIntentId, $transaction->getTxnId());
                $this->assertEquals("capture", $transaction->getTxnType());
                $this->assertEquals(21.65, $transaction->getAdditionalInformation("amount"));
            }
        }

        // Ensure that a new order was created
        $newOrdersCount = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->count();
        $this->assertEquals($ordersCount + 1, $newOrdersCount);

        // Check the newly created order
        $newOrder = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->setOrder('increment_id','DESC')->getFirstItem();
        $this->assertNotEquals($order->getIncrementId(), $newOrder->getIncrementId());
        $this->assertEquals("processing", $newOrder->getState());
        $this->assertEquals("processing", $newOrder->getStatus());
        $this->assertEquals(63.3, $newOrder->getGrandTotal());
        $this->assertEquals(63.3, $newOrder->getTotalPaid());
        $this->assertEquals(1, $newOrder->getInvoiceCollection()->getSize());

        // Process a recurring subscription billing webhook
        $this->tests->event()->trigger("invoice.payment_succeeded", $invoiceId, $this);

        // Get the newly created order
        $newOrder = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->setOrder('entity_id','DESC')->getFirstItem();

        // Assert new order, invoices, invoice items, invoice totals
        $this->assertNotEquals($order->getIncrementId(), $newOrder->getIncrementId());
        $this->assertEquals("processing", $newOrder->getState());
        $this->assertEquals("processing", $newOrder->getStatus());
        $this->assertEquals(0, $order->getTotalDue());
        $this->assertEquals(1, $order->getInvoiceCollection()->count());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/checkout_mode 1
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     */
    public function testFixedBundleMixedTrialCart()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart("FixedBundleMixedTrial")
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("StripeCheckoutCard");

        $quote = $this->quote->getQuote();

        // Checkout totals should be correct
        $trialSubscriptionsConfig = $this->subscriptions->getTrialingSubscriptionsAmounts($quote);

        $this->assertEquals(80, $trialSubscriptionsConfig["subscriptions_total"], "Subtotal");
        $this->assertEquals(80, $trialSubscriptionsConfig["base_subscriptions_total"], "Base Subtotal");

        $this->assertEquals(20, $trialSubscriptionsConfig["shipping_total"], "Shipping");
        $this->assertEquals(20, $trialSubscriptionsConfig["base_shipping_total"], "Base Shipping");

        $this->assertEquals(0, $trialSubscriptionsConfig["discount_total"], "Discount");
        $this->assertEquals(0, $trialSubscriptionsConfig["base_discount_total"], "Base Discount");

        $this->assertEquals(6.6, $trialSubscriptionsConfig["tax_total"], "Tax");
        $this->assertEquals(6.6, $trialSubscriptionsConfig["tax_total"], "Base Tax");

        // Place the order
        $order = $this->quote->placeOrder();

        $orderIncrementId = $order->getIncrementId();
        $currency = $order->getOrderCurrencyCode();
        $expectedChargeAmount = $order->getGrandTotal()
            - $trialSubscriptionsConfig["subscriptions_total"]
            - $trialSubscriptionsConfig["shipping_total"]
            + $trialSubscriptionsConfig["discount_total"]
            - $trialSubscriptionsConfig["tax_total"];

        $expectedChargeAmount = $this->helper->convertMagentoAmountToStripeAmount($expectedChargeAmount, $currency);

        // Retrieve the created session
        $checkoutSessionId = $order->getPayment()->getAdditionalInformation('checkout_session_id');
        $this->assertNotEmpty($checkoutSessionId);

        $stripe = $this->stripeConfig->getStripeClient();
        $session = $stripe->checkout->sessions->retrieve($checkoutSessionId);

        $this->assertEquals($expectedChargeAmount, $session->amount_total);

        // Confirm the payment
        $paymentMethod = $stripe->paymentMethods->create([
          'type' => 'card',
          'card' => [
            'number' => '4242424242424242',
            'exp_month' => 7,
            'exp_year' => 2022,
            'cvc' => '314',
          ],
          'billing_details' => [
            'address' => [
                'city' => 'New York',
                'country' => 'US',
                'line1' => '1255 Duncan Avenue',
                'postal_code' => "10013",
                'state' => "New York"
            ],
            'email' => 'jerryflint@example.com',
            'name' => 'Jerry Flint',
            'phone' => "917-535-4022"
          ],
        ]);

        $params = [
            'eid' => 'NA',
            'payment_method' => $paymentMethod->id,
            'expected_amount' => $session->amount_total,
            'expected_payment_method_type' => 'card'
        ];

        $response = $stripe->request('post', "/v1/payment_pages/{$session->id}/confirm", $params, $opts = null);

        // Assert order status, amount due, invoices
        $this->assertEquals("new", $order->getState());
        $this->assertEquals("pending", $order->getStatus());
        $this->assertEquals(0, $order->getInvoiceCollection()->count());

        // Stripe subscription checks
        $customer = $stripe->customers->retrieve($session->customer);
        $this->assertCount(1, $customer->subscriptions->data);
        $subscription = $customer->subscriptions->data[0];
        $this->assertEquals("trialing", $subscription->status);
        $this->assertEquals(10660, $subscription->items->data[0]->price->unit_amount);

        $subscriptionId = $subscription->id;

        // Process the charge.succeeded event
        $paymentIntentId = $response->payment_intent->id;
        $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);
        $charge =  $paymentIntent->charges->data[0];
        $this->tests->event()->trigger("charge.succeeded", $charge, $this);

        // Process invoice.payment_succeeded event
        $ordersCount = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->count();
        $customer = $stripe->customers->retrieve($session->customer);
        $invoiceId = $customer->subscriptions->data[0]->latest_invoice;
        $this->tests->event()->trigger("invoice.payment_succeeded", $invoiceId, $this);

        // Ensure that no new order was created
        $newOrdersCount = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->count();
        $this->assertEquals($ordersCount, $newOrdersCount);

        // Refresh the order object
        $order = $this->helper->loadOrderByIncrementId($orderIncrementId);

        // Assert order status, amount due, invoices, invoice items, invoice totals
        $this->assertEquals("processing", $order->getState());
        $this->assertEquals("processing", $order->getStatus());
        $this->assertEquals(106.6, $order->getTotalDue());
        $this->assertEquals(1, $order->getInvoiceCollection()->count());

        // End the trial
        $stripe->subscriptions->update($subscriptionId, ['trial_end' => "now"]);
        $subscription = $stripe->subscriptions->retrieve($subscriptionId, ['expand' => ['latest_invoice']]);

        $ordersCount = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->count();

        // Trigger webhook events for the trial end
        $this->tests->event()->trigger("charge.succeeded", $subscription->latest_invoice->charge, $this);

        $this->tests->event()->trigger("invoice.payment_succeeded", $subscription->latest_invoice->id, $this);

        // Check that the order invoice was marked as paid
        $order = $this->helper->loadOrderByIncrementId($order->getIncrementId());
        $this->assertEquals(128.25, $order->getTotalPaid());
        $this->assertEquals(0, $order->getTotalDue());
        $invoicesCollection = $order->getInvoiceCollection();
        $invoice = $invoicesCollection->getFirstItem();
        $this->assertEquals(\Magento\Sales\Model\Order\Invoice::STATE_PAID, $invoice->getState());
        $this->assertEquals($paymentIntentId, $invoice->getTransactionId());

        // Check that the transaction IDs have been associated with the order
        $transactions = $this->helper->getOrderTransactions($order);
        $this->assertEquals(2, count($transactions));
        foreach ($transactions as $key => $transaction)
        {
            if ($transaction->getTxnId() == $subscription->latest_invoice->payment_intent)
            {
                $this->assertEquals("capture", $transaction->getTxnType());
                $this->assertTrue($transaction->getAdditionalInformation("is_subscription"));
                $this->assertEquals(106.6, $transaction->getAdditionalInformation("amount"));
            }
            else
            {
                $this->assertEquals($paymentIntentId, $transaction->getTxnId());
                $this->assertEquals("capture", $transaction->getTxnType());
                $this->assertEquals(21.65, $transaction->getAdditionalInformation("amount"));
            }
        }

        // Ensure that a new order was created
        $newOrdersCount = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->count();
        $this->assertEquals($ordersCount + 1, $newOrdersCount);

        // Check the newly created order
        $newOrder = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->setOrder('increment_id','DESC')->getFirstItem();
        $this->assertNotEquals($order->getIncrementId(), $newOrder->getIncrementId());
        $this->assertEquals("processing", $newOrder->getState());
        $this->assertEquals("processing", $newOrder->getStatus());
        $this->assertEquals(106.6, $newOrder->getGrandTotal());
        $this->assertEquals(106.6, $newOrder->getTotalPaid());
        $this->assertEquals(1, $newOrder->getInvoiceCollection()->getSize());

        // Process a recurring subscription billing webhook
        $this->tests->event()->trigger("invoice.payment_succeeded", $invoiceId, $this);

        // Get the newly created order
        $newOrder = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->setOrder('entity_id','DESC')->getFirstItem();

        // Assert new order, invoices, invoice items, invoice totals
        $this->assertNotEquals($order->getIncrementId(), $newOrder->getIncrementId());
        $this->assertEquals("processing", $newOrder->getState());
        $this->assertEquals("processing", $newOrder->getStatus());
        $this->assertEquals(0, $order->getTotalDue());
        $this->assertEquals(1, $order->getInvoiceCollection()->count());
    }
}
