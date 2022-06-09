<?php

namespace Oander\RaiffeisenPayment\Controller\Payment;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Dompdf\Dompdf;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;
use Oander\RaiffeisenPayment\Helper\PdfGenerator;

class Index extends Action
{
    /**
     * @var PdfGenerator
     */
    protected $pdfGenerator;
    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    protected $filesystem;

    protected $_storeManager;
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Action\Context $context
     */
    public function __construct(
        Context                    $context,
        Registry                   $registry,
        ProductRepositoryInterface $productRepository,
        LayoutInterface            $layout,
        Filesystem                 $filesystem,
        StoreManagerInterface      $storeManager,
        JsonFactory                $resultJsonFactory,
        PdfGenerator                $pdfGenerator
    )
    {
        parent::__construct($context);
        $this->_registry = $registry;
        $this->productRepository = $productRepository;
        $this->layout = $layout;
        $this->filesystem = $filesystem;
        $this->_storeManager = $storeManager;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->pdfGenerator = $pdfGenerator;
    }


    /**
     * Create PDF for product page.
     */
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orders = $objectManager->create('Magento\Sales\Model\Order')->load(1);
        $this->pdfGenerator->execute($orders);
        echo "PDF CREATED";
        die();
    }
}