<?php

namespace Avalon\Costompayment\Controller\Index;

use Avalon\Costompayment\Controller\Status\Update;

/**
 * Class Tbigetid
 * @package Avalon\Costompayment\Controller\Index
 */
class Tbigetid extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $order;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Avalon\Costompayment\Helper\Data
     */
    protected $tbiHelper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * Tbigetid constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Avalon\Costompayment\Helper\Data $tbiHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Avalon\Costompayment\Helper\Data $tbiHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->pageFactory = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->tbiHelper = $tbiHelper;
        $this->imageHelper = $imageHelper;
        $this->filesystem = $filesystem;
        return parent::__construct($context);
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder()
    {
        if ($this->order == null) {
            $sessionOrder = $this->checkoutSession->getLastRealOrder();
            $this->order = $this->orderRepository->get($sessionOrder->getId());
        }

        return $this->order;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $tag = $this->getRequest()->getParam('tag', false);

        if ($tag == 'jLhrHYsfPQ3Gu9JgJPLJ') {

            $tbiro_unicid = $this->tbiHelper->getConfig('avalon_custompaymentmethod_tab_options/properties_tbiro/rotbi_unicid') ?: '';
            $tbiro_store_id = $this->tbiHelper->getConfig('avalon_custompaymentmethod_tab_options/properties_tbiro/rotbi_store_id') ?: '';
            $tbiro_username = $this->tbiHelper->getConfig('avalon_custompaymentmethod_tab_options/properties_tbiro/rotbi_username') ?: '';
            $tbiro_password = $this->tbiHelper->getConfig('avalon_custompaymentmethod_tab_options/properties_tbiro/rotbi_password') ?: '';

            $tbiro_ch = curl_init();
            curl_setopt($tbiro_ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($tbiro_ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($tbiro_ch, CURLOPT_URL, $this->tbiHelper->getTbiroLiveUrl() . '/function/getparameters.php?cid=' . $tbiro_unicid);
            $paramstbiro = json_decode(curl_exec($tbiro_ch), true);
            curl_close($tbiro_ch);

            $tbiro_url = $this->_url->getUrl(Update::ACTION_KEY);
            if (!is_null($this->getOrder()->getBillingAddress())) {
                $billingstreet = $this->getOrder()->getBillingAddress()->getStreet();
            }
            if (!is_null($this->getOrder()->getShippingAddress())) {
                $shippingstreet = $this->getOrder()->getShippingAddress()->getStreet();
            }

            $tbiro_price = $this->getOrder()->getGrandTotal();
            if ($this->getOrder()->getCustomerId() === NULL) {
                $tbiro_fname = is_null($this->getOrder()->getBillingAddress()) ? '' : $this->getOrder()->getBillingAddress()->getFirstname();
                $tbiro_lname = is_null($this->getOrder()->getBillingAddress()) ? '' : $this->getOrder()->getBillingAddress()->getLastname();
            } else {
                $tbiro_fname = $this->getOrder()->getCustomerFirstname();
                $tbiro_lname = $this->getOrder()->getCustomerLastname();
            }
            $tbiro_cnp = '';
            $tbiro_email = $this->getOrder()->getCustomerEmail();
            $tbiro_billing_phone = is_null($this->getOrder()->getBillingAddress()) ? '' : $this->getOrder()->getBillingAddress()->getTelephone();
            $tbiro_billing_address = isset($billingstreet[0]) ? $billingstreet[0] : '';
            $tbiro_billing_city = is_null($this->getOrder()->getBillingAddress()) ? '' : $this->getOrder()->getBillingAddress()->getCity();
            $tbiro_billing_country = is_null($this->getOrder()->getBillingAddress()) ? '' : $this->getOrder()->getBillingAddress()->getRegion();
            $tbiro_shipping_address = isset($shippingstreet[0]) ? $shippingstreet[0] : '';
            $tbiro_shipping_city = is_null($this->getOrder()->getShippingAddress()) ? '' : $this->getOrder()->getShippingAddress()->getCity();
            $tbiro_shipping_country = is_null($this->getOrder()->getShippingAddress()) ? '' : $this->getOrder()->getShippingAddress()->getRegion();
            $tbiro_person_type = '';
            $tbiro_net_income = '';
            $tbiro_instalments = '';

            $ident = 0;
            $tbiro_items[0]['name'] = '';
            $tbiro_items[0]['qty'] = '';
            $tbiro_items[0]['price'] = '';
            $tbiro_items[0]['category'] = '';
            $tbiro_items[0]['sku'] = '';
            $tbiro_items[0]['ImageLink'] = '';
            foreach ($this->getOrder()->getAllVisibleItems() as $cart_item) {
                $_product = $cart_item->getProduct();
                $tbiro_items[$ident]['name'] = $_product['name'];
                $tbiro_quantity = $cart_item->getQtyOrdered();
                $tbiro_items[$ident]['qty'] = "$tbiro_quantity";
                $tbiro_price_items = $cart_item->getPrice();
                $tbiro_items[$ident]['price'] = "$tbiro_price_items";
                $cats = $_product->getCategoryIds();
                foreach ($cats as $cat_id) {
                    $tbiro_category = $cat_id;
                }
                $tbiro_items[$ident]['category'] = "$tbiro_category";
                $tbiro_product_id = $cart_item->getSku();
                $tbiro_items[$ident]['sku'] = "$tbiro_product_id";
                $tbiro_image = $this->imageHelper->init($_product, 'product_page_image_large')->setImageFile($_product->getFile())->getUrl();
                $tbiro_imagePath = isset($tbiro_image) ? $tbiro_image : '';
                $tbiro_items[$ident]['ImageLink'] = "$tbiro_imagePath";
                $ident++;
            }

            // Create tbiro order i data base
            $tbiro_add_ch = curl_init();
            curl_setopt($tbiro_add_ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($tbiro_add_ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($tbiro_add_ch, CURLOPT_URL, $this->tbiHelper->getTbiroLiveUrl() . '/function/addorders.php?cid=' . $tbiro_unicid);
            curl_setopt($tbiro_add_ch, CURLOPT_POST, 1);
            $tbiro_post = [
                'store_id' => $tbiro_store_id,
                'order_id' => $this->getOrder()->getIncrementId(),
                'back_ref' => $tbiro_url,
                'order_total' => $tbiro_price,
                'username' => $tbiro_username,
                'password' => $tbiro_password,
                'customer' => [
                    'fname' => $tbiro_fname,
                    'lname' => $tbiro_lname,
                    'cnp' => $tbiro_cnp, //$tbiro_cnp,
                    'email' => $tbiro_email,
                    'phone' => $tbiro_billing_phone,
                    'billing_address' => $tbiro_billing_address,
                    'billing_city' => $tbiro_billing_city,
                    'billing_county' => $tbiro_billing_country,
                    'shipping_address' => $tbiro_shipping_address,
                    'shipping_city' => $tbiro_shipping_city,
                    'shipping_county' => $tbiro_shipping_country,
                    'person_type' => $tbiro_person_type,
                    'net_income' => $tbiro_net_income,
                    'instalments' => $tbiro_instalments
                ],
                'items' => $tbiro_items
            ];

            curl_setopt($tbiro_add_ch, CURLOPT_POSTFIELDS, http_build_query($tbiro_post));
            curl_setopt($tbiro_add_ch, CURLOPT_VERBOSE, true);
            $paramstbiroadd = json_decode(curl_exec($tbiro_add_ch), true);

            file_put_contents('/var/www/istyle.eu/webroot/var/log/oander/tbi.log', date('Y-m-d H:i:s').' | REQUEST url: '.$this->tbiHelper->getTbiroLiveUrl() . '/function/addorders.php?cid=' . $tbiro_unicid.' data: '. var_export($tbiro_post,true).PHP_EOL,FILE_APPEND);
            file_put_contents('/var/www/istyle.eu/webroot/var/log/oander/tbi.log', date('Y-m-d H:i:s').' | RESPONSE: '. var_export($paramstbiroadd,true).PHP_EOL,FILE_APPEND);
            if ($paramstbiroadd === FALSE) {
                $err = curl_error($tbiro_add_ch);
                file_put_contents('/var/www/istyle.eu/webroot/var/log/oander/tbi.log', date('Y-m-d H:i:s').' | ERROR-RESPONSE: '. var_export($err,true).PHP_EOL,FILE_APPEND);

            }


            curl_close($tbiro_add_ch);
            // Create tbiro order i data base

            if (isset($paramstbiroadd['status']) && ($paramstbiroadd['status'] == 'Yes')) {
                // send to softinteligens
                $tbiro_post = [
                    'store_id' => $tbiro_store_id,
                    'order_id' => $paramstbiroadd['newid'],
                    'back_ref' => $tbiro_url,
                    'order_total' => $tbiro_price,
                    'username' => $tbiro_username,
                    'password' => $tbiro_password,
                    'customer' => [
                        'fname' => $tbiro_fname,
                        'lname' => $tbiro_lname,
                        'cnp' => $tbiro_cnp, //$tbiro_cnp,
                        'email' => $tbiro_email,
                        'phone' => $tbiro_billing_phone,
                        'billing_address' => $tbiro_billing_address,
                        'billing_city' => $tbiro_billing_city,
                        'billing_county' => $tbiro_billing_country,
                        'shipping_address' => $tbiro_shipping_address,
                        'shipping_city' => $tbiro_shipping_city,
                        'shipping_county' => $tbiro_shipping_country,
                        'person_type' => $tbiro_person_type,
                        'net_income' => $tbiro_net_income,
                        'instalments' => $tbiro_instalments
                    ],
                    'items' => $tbiro_items
                ];

                $tbiro_plaintext = json_encode($tbiro_post);
                $tbiro_plaintext_64 = base64_encode($tbiro_plaintext);

                if ($paramstbiro['tbi_testenv'] == 1) {
                    $tbiro_envurl = $paramstbiro['tbi_testurl'];
                } else {
                    $tbiro_envurl = $paramstbiro['tbi_liveurl'];
                }

                if (isset($paramstbiro['tbi_pause_txt'])) {
                    $tbi_pause_txt = $paramstbiro['tbi_pause_txt'];
                } else {
                    $tbi_pause_txt = 'Va rugam asteptati, aplicatia dumneavoastra este directionata catre portalul TBI.';
                }

                $status = 1;
                $retenc = $tbiro_plaintext_64;
            } else {
                $status = 0;
                $retenc = "error add order id!";
                $tbiro_envurl = "";
                $tbi_pause_txt = "";
            }
        } else {
            $status = 0;
            $retenc = "error";
            $tbiro_envurl = "";
            $tbi_pause_txt = "";
        }

        $tbiro_plaintext_64 = $retenc;
        $tbiro_plaintext = base64_decode($tbiro_plaintext_64);

        $tbiro_publicKey = openssl_pkey_get_public(
            file_get_contents(
                $this->filesystem
                    ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
                    ->getAbsolutePath() . 'keys/public.key'
            )
        );
        $tbiro_a_key = openssl_pkey_get_details($tbiro_publicKey);
        $tbiro_chunkSize = ceil($tbiro_a_key['bits'] / 8) - 11;
        $tbiro_output = '';

        while ($tbiro_plaintext) {
            $tbiro_chunk = substr($tbiro_plaintext, 0, $tbiro_chunkSize);
            $tbiro_plaintext = substr($tbiro_plaintext, $tbiro_chunkSize);
            $tbiro_encrypted = '';
            if (!openssl_public_encrypt($tbiro_chunk, $tbiro_encrypted, $tbiro_publicKey)) {
                die('Failed to encrypt data');
            }
            $tbiro_output .= $tbiro_encrypted;
        }
        openssl_free_key($tbiro_publicKey);
        $tbiro_output64 = base64_encode($tbiro_output);

        $result = $this->resultJsonFactory->create();
        $result->setData(
            [
                'msg_status' => $status,
                'retid' => $retenc,
                'tbiro_envurl' => $tbiro_envurl,
                'tbi_pause_txt' => $tbi_pause_txt,
                'tbiro_output64' => $tbiro_output64
            ]
        );

        return $result;
    }
}