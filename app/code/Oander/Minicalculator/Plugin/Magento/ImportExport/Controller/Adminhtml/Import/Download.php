<?php
namespace Oander\Minicalculator\Plugin\Magento\ImportExport\Controller\Adminhtml\Import;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\RawFactory;

class Download
{
    const SAMPLE_FILES_MODULE = 'Oander_Minicalculator';
    const SAMPLE_FILES_DIR = '/Files/Sample/';

    const FILE_NAME = 'minicalculator_entity';
    const FILE_TYPE = '.csv';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var RawFactory
     */
    private $rawFactory;

    /**
     * Download constructor.
     * @param RequestInterface $request
     * @param ComponentRegistrar $componentRegistrar
     * @param ReadFactory $readFactory
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $redirectFactory
     * @param FileFactory $fileFactory
     * @param RawFactory $rawFactory
     */
    public function __construct(
        RequestInterface $request,
        ComponentRegistrar $componentRegistrar,
        ReadFactory $readFactory,
        ManagerInterface $messageManager,
        RedirectFactory $redirectFactory,
        FileFactory $fileFactory,
        RawFactory $rawFactory
    ) {
        $this->request = $request;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
        $this->messageManager = $messageManager;
        $this->redirectFactory = $redirectFactory;
        $this->fileFactory = $fileFactory;
        $this->rawFactory = $rawFactory;
    }

    public function afterExecute(\Magento\ImportExport\Controller\Adminhtml\Import\Download $subject, $resultRaw)
    {

        if ($this->request->getParam('filename','') !== self::FILE_NAME) {
            return $resultRaw;
        }
        $fileName = $this->request->getParam('filename') . self::FILE_TYPE;
        $moduleDir = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, self::SAMPLE_FILES_MODULE);
        $fileAbsolutePath = $moduleDir . self::SAMPLE_FILES_DIR . $fileName;
        $directoryRead = $this->readFactory->create($moduleDir);
        $filePath = $directoryRead->getRelativePath($fileAbsolutePath);

        if (!$directoryRead->isFile($filePath)) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $this->messageManager->addErrorMessage(__('There is no sample file for this entity.'));
            $resultRedirect = $this->redirectFactory->create();
            $resultRedirect->setPath('*/import');
            return $resultRedirect;
        }

        $fileSize = isset($directoryRead->stat($filePath)['size'])
            ? $directoryRead->stat($filePath)['size'] : null;

        $this->fileFactory->create(
            $fileName,
            null,
            DirectoryList::VAR_DIR,
            'application/octet-stream',
            $fileSize
        );

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->rawFactory->create();
        $resultRaw->setContents($directoryRead->readFile($filePath));

        return $resultRaw;
    }
}