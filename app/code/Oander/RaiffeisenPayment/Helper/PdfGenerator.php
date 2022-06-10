<?php

namespace Oander\RaiffeisenPayment\Helper;

use Dompdf\Dompdf;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Model\Order;
use Oander\RaiffeisenPayment\Block\Pdf\Index;
use Oander\RaiffeisenPayment\Helper\Config;
use Oander\RaiffeisenPayment\Logger\Logger;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use DateTime;

class PdfGenerator extends AbstractHelper
{
    /**
     * @var TimezoneInterface
     */
    protected $date;
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var FilterProvider
     */
    protected $proccesor;
    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var Filesystem
     */
    protected $filesystem;
    /**
     * @var string
     */
    private $mediaPath;

    /**
     * @param Context $context
     * @param LayoutInterface $layout
     * @param FilterProvider $proccesor
     * @param Filesystem $filesystem
     * @param Config $configHelper
     * @param Logger $logger
     * @param TimezoneInterface $date
     */
    public function __construct(
        Context           $context,
        LayoutInterface   $layout,
        FilterProvider    $proccesor,
        Filesystem        $filesystem,
        Config            $configHelper,
        Logger            $logger,
        TimezoneInterface $date
    )
    {
        parent::__construct($context);
        $this->layout = $layout;
        $this->proccesor = $proccesor;
        $this->filesystem = $filesystem;
        $this->mediaPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('raiffeisen_pdf');
        $this->configHelper = $configHelper;
        $this->logger = $logger;
        $this->date = $date;
    }

    /**
     * @param Order $order
     * @return string
     * @throws \Exception
     */
    public function execute(Order $order)
    {
        $orderExpiration = new DateTime($this->date->date($order->getCreatedAt())->format('Y-m-d'));
        $orderExpiration->modify("+" . $this->configHelper->getOrderExpiration() . " day");
        $productpdf = $this->layout->createBlock(Index::class, "", ['data' => ['orderExpiration' => date('Y-m-d',$orderExpiration->getTimestamp()), 'order' => $order, 'address1' => $this->configHelper->getMerchantAddress1(), 'address2' => $this->configHelper->getMerchantAddress2()]])
            ->setData('area', 'frontend')
            ->setTemplate('Oander_RaiffeisenPayment::pdf.phtml')->toHtml();
        $pdf = $this->renderPdf($this->proccesor->getBlockFilter()->filter($productpdf));
        //pdf save
        $route = $this->mediaPath . "/" . $order->getIncrementId() . ".pdf";
        file_put_contents($route, $pdf);
        $response = chunk_split(base64_encode(file_get_contents($route)));
        $this->logger->addInfo("PDF BASE64:" . $response);
        return base64_encode($pdf);
    }

    /**
     * @param $pdfContent
     * @return string|null
     */
    protected function renderPdf($pdfContent)
    {
        $dompdf = new Dompdf();
        $dompdf->load_html($pdfContent);
        $dompdf->set_option('isRemoteEnabled', TRUE);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf->output();
    }
}