<?php
namespace Avalon\Costompayment\Controller\Index;

class Tbigetid extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $_resultJsonFactory;
	protected $_checkoutSession;
	protected $_orderFactory;
	protected $_order_id;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory
	)
	{
		$this->_pageFactory = $pageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_checkoutSession = $checkoutSession;
		$this->_orderFactory = $orderFactory;
		$this->_order_id = $this->getRealOrderId();
		return parent::__construct($context);
	}

    public function getRealOrderId()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
		$orderId = $order->getEntityId();
        return $order->getIncrementId();
    }

    public function getOrder()
    {
        $order = $this->_orderFactory->create()->loadByIncrementId($this->_order_id);
        return $order;
    }

	public function execute()
	{
		if ((isset($_GET['tag'])) && ($_GET['tag'] == 'jLhrHYsfPQ3Gu9JgJPLJ')){
			if ($this->_objectManager->create('Avalon\Costompayment\Helper\Data')->getConfig('avalon_custompaymentmethod_tab_options/properties_tbiro/rotbi_unicid')){
				$tbiro_unicid = $this->_objectManager->create('Avalon\Costompayment\Helper\Data')->getConfig('avalon_custompaymentmethod_tab_options/properties_tbiro/rotbi_unicid');
			}else{
				$tbiro_unicid = "";
			}	
			if ($this->_objectManager->create('Avalon\Costompayment\Helper\Data')->getConfig('avalon_custompaymentmethod_tab_options/properties_tbiro/rotbi_store_id')){
				$tbiro_store_id = $this->_objectManager->create('Avalon\Costompayment\Helper\Data')->getConfig('avalon_custompaymentmethod_tab_options/properties_tbiro/rotbi_store_id');
			}else{
				$tbiro_store_id = "";
			}	
			if ($this->_objectManager->create('Avalon\Costompayment\Helper\Data')->getConfig('avalon_custompaymentmethod_tab_options/properties_tbiro/rotbi_username')){
				$tbiro_username = $this->_objectManager->create('Avalon\Costompayment\Helper\Data')->getConfig('avalon_custompaymentmethod_tab_options/properties_tbiro/rotbi_username');
			}else{
				$tbiro_username = "";
			}	
			if ($this->_objectManager->create('Avalon\Costompayment\Helper\Data')->getConfig('avalon_custompaymentmethod_tab_options/properties_tbiro/rotbi_password')){
				$tbiro_password = $this->_objectManager->create('Avalon\Costompayment\Helper\Data')->getConfig('avalon_custompaymentmethod_tab_options/properties_tbiro/rotbi_password');
			}else{
				$tbiro_password = "";
			}	

			$tbiro_mod_version = '';
			$tbiro_mod_version = $this->_objectManager->get('Magento\Framework\Module\ModuleList')->getOne('Avalon_Costompayment')['setup_version'];
			
			$tbiro_ch = curl_init();
			curl_setopt($tbiro_ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($tbiro_ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($tbiro_ch, CURLOPT_URL, $this->_objectManager->create('Avalon\Costompayment\Helper\Data')->getTbiroLiveUrl() . '/function/getparameters.php?cid='.$tbiro_unicid);
			$paramstbiro = json_decode(curl_exec($tbiro_ch), true);
			curl_close($tbiro_ch);
	
			$tbiro_minprice_tbi = $paramstbiro['tbi_minstojnost'];
			$tbiro_theme = $paramstbiro['tbi_theme'];
			$tbiro_btn_theme = $paramstbiro['tbi_btn_theme'];
			$tbiro_custom_button_status = $paramstbiro['tbi_custom_button_status'];
			$tbiro_url = $this->_objectManager->create('Avalon\Costompayment\Helper\Data')->getTbiroLiveUrl() . "/function/status.php";
			if (!is_null($this->getOrder()->getBillingAddress())){
				$billingstreet = $this->getOrder()->getBillingAddress()->getStreet();
			}
			if (!is_null($this->getOrder()->getShippingAddress())){
				$shippingstreet = $this->getOrder()->getShippingAddress()->getStreet();
			}
			
			$tbiro_price = $this->getOrder()->getGrandTotal();
			if ($this->getOrder()->getCustomerId() === NULL){
				$tbiro_fname = is_null($this->getOrder()->getBillingAddress()) ? '' : $this->getOrder()->getBillingAddress()->getFirstname();
				$tbiro_lname = is_null($this->getOrder()->getBillingAddress()) ? '' : $this->getOrder()->getBillingAddress()->getLastname();		
			}else{
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
			foreach($this->getOrder()->getAllVisibleItems() as $cart_item){
				$_product = $cart_item->getProduct();
				$_product_id = $cart_item->getProductId();
				$tbiro_items[$ident]['name'] = $_product['name'];
				$tbiro_quantity = $cart_item->getQtyOrdered();
				$tbiro_items[$ident]['qty'] = "$tbiro_quantity";
				$tbiro_price_items = $cart_item->getPrice();
				$tbiro_items[$ident]['price'] = "$tbiro_price_items";
				$cats = $_product->getCategoryIds();
				foreach ($cats as $cat_id){
					$tbiro_category = $cat_id;
				}
				$tbiro_items[$ident]['category'] = "$tbiro_category";
				$tbiro_product_id = $cart_item->getSku();
				$tbiro_items[$ident]['sku'] = "$tbiro_product_id";
				$helperImport = $this->_objectManager->get('\Magento\Catalog\Helper\Image');
				$tbiro_image = $helperImport->init($_product, 'product_page_image_large')->setImageFile($_product->getFile())->getUrl();
				$tbiro_imagePath = isset($tbiro_image) ? $tbiro_image : '';
				$tbiro_items[$ident]['ImageLink'] = "$tbiro_imagePath";
				$ident++;
			}

			// Create tbiro order i data base
			$tbiro_add_ch = curl_init();
			curl_setopt($tbiro_add_ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($tbiro_add_ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($tbiro_add_ch, CURLOPT_URL, $this->_objectManager->create('Avalon\Costompayment\Helper\Data')->getTbiroLiveUrl() . '/function/addorders.php?cid='.$tbiro_unicid);
			curl_setopt($tbiro_add_ch, CURLOPT_POST, 1);
			$tbiro_post = [
				'store_id'      => $tbiro_store_id,
				'order_id'      =>  '',
				'back_ref'      =>  $tbiro_url,
				'order_total'   =>  $tbiro_price,
				'username'	=> $tbiro_username,
				'password'	=> $tbiro_password,
				'customer'      =>  [
				'fname'         => $tbiro_fname,
				'lname'         => $tbiro_lname,
				'cnp'           => $tbiro_cnp, //$tbiro_cnp,
				'email'         => $tbiro_email,
				'phone'         => $tbiro_billing_phone,
				'billing_address'      => $tbiro_billing_address,
				'billing_city'          => $tbiro_billing_city,
				'billing_county'        => $tbiro_billing_country,
				'shipping_address'      => $tbiro_shipping_address,
				'shipping_city'          => $tbiro_shipping_city,
				'shipping_county'        => $tbiro_shipping_country,
				'person_type'   => $tbiro_person_type,
				'net_income'    => $tbiro_net_income,
				'instalments'	=> $tbiro_instalments
				],
				'items' => $tbiro_items
			];
	
			curl_setopt($tbiro_add_ch, CURLOPT_POSTFIELDS, http_build_query($tbiro_post));
			$paramstbiroadd=json_decode(curl_exec($tbiro_add_ch), true);
			curl_close($tbiro_add_ch);				
			// Create tbiro order i data base
	
			if (isset($paramstbiroadd['status']) && ($paramstbiroadd['status'] == 'Yes')){					
				// send to softinteligens
				$tbiro_post = [
					'store_id'      => $tbiro_store_id,
					'order_id'      =>  $paramstbiroadd['newid'],
					'back_ref'      =>  $tbiro_url,
					'order_total'   =>  $tbiro_price,
					'username'	=> $tbiro_username,
					'password'	=> $tbiro_password,
					'customer'      =>  [
					'fname'         => $tbiro_fname,
					'lname'         => $tbiro_lname,
					'cnp'           => $tbiro_cnp, //$tbiro_cnp,
					'email'         => $tbiro_email,
					'phone'         => $tbiro_billing_phone,
					'billing_address'      => $tbiro_billing_address,
					'billing_city'          => $tbiro_billing_city,
					'billing_county'        => $tbiro_billing_country,
					'shipping_address'      => $tbiro_shipping_address,
					'shipping_city'          => $tbiro_shipping_city,
					'shipping_county'        => $tbiro_shipping_country,
					'person_type'   => $tbiro_person_type,
					'net_income'    => $tbiro_net_income,
					'instalments'	=> $tbiro_instalments
					],
					'items' => $tbiro_items
				];
				
				$tbiro_plaintext = json_encode($tbiro_post);
				$tbiro_plaintext_64 = base64_encode($tbiro_plaintext);

				if ($paramstbiro['tbi_testenv'] == 1){
					$tbiro_envurl = $paramstbiro['tbi_testurl'];
				}else{
					$tbiro_envurl = $paramstbiro['tbi_liveurl'];
				}

				if (isset($paramstbiro['tbi_pause_txt'])){
					$tbi_pause_txt = $paramstbiro['tbi_pause_txt'];
				}else{
					$tbi_pause_txt = 'Va rugam asteptati, aplicatia dumneavoastra este directionata catre portalul TBI.';
				}

				$status = 1;
				$retenc = $tbiro_plaintext_64;
			}else{
				$status = 0;
				$retenc = "error add order id!";
				$tbiro_envurl = "";
				$tbi_pause_txt = "";
			}
		}else{
			$status = 0;
			$retenc = "error";
			$tbiro_envurl = "";
			$tbi_pause_txt = "";
		}
		
		$result = $this->_resultJsonFactory->create();
		$result->setData(['msg_status' => $status, 'retid' => $retenc, 'tbiro_envurl' => $tbiro_envurl, 'tbi_pause_txt' => $tbi_pause_txt]);
		return $result;
	}
}