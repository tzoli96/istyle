<?php
namespace Oander\CustomerFileUploadLimit\Plugin\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Store\Model\StoreManagerInterface;
use Oander\CustomerFileUpload\Api\Data\FileInterfaceFactory;
use Oander\CustomerFileUpload\Api\FileRepositoryInterface;
use Oander\CustomerFileUpload\Controller\Customer\Upload as OriginalClass;
use Oander\CustomerFileUpload\Enum\FileUploadEnum;
use Oander\CustomerFileUpload\Helper\Config;
use Oander\CustomerFileUpload\Model\Email;
use Oander\CustomerFileUpload\Model\File;
use Oander\CustomerFileUpload\Model\FileUploader;

class Upload extends OriginalClass
{

    /**
     * @var FileUploader
     */
    protected $uploader;

    /**
     * @var FileInterfaceFactory
     */
    protected $fileFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FileRepositoryInterface
     */
    private $fileRepository;

    /**
     * @var Email
     */
    private $email;

    /**
     * Upload constructor.
     *
     * @param Context                 $context
     * @param Session                 $customerSession
     * @param Config                  $config
     * @param FileUploader            $uploader
     * @param FileInterfaceFactory    $fileFactory
     * @param StoreManagerInterface   $storeManager
     * @param FileRepositoryInterface $fileRepository
     * @param Email                   $email
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Config $config,
        FileUploader $uploader,
        FileInterfaceFactory $fileFactory,
        StoreManagerInterface $storeManager,
        FileRepositoryInterface $fileRepository,
        Email $email
    ) {
        parent::__construct($context, $customerSession, $config, $uploader ,$fileFactory,$storeManager,$fileRepository,$email);
        $this->uploader = $uploader;
        $this->fileFactory = $fileFactory;
        $this->storeManager = $storeManager;
        $this->fileRepository = $fileRepository;
        $this->email = $email;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        try {
            $customerId = (int)$this->customerSession->getCustomerId();
            $result = $this->uploader->saveFileToTmpDir('file');
            if($result['size'] < 5000000) {
                $fileName = $result['name'];
                $currentFilesCount = $this->fileRepository->getFilesByCustomerWebsite(
                    $customerId,
                    (int)$this->storeManager->getStore()->getWebsiteId()
                )->count();
                /** @var File $file */
                $file = $this->fileFactory->create();

                $file->setCustomerId($customerId);
                $file->setFileName($fileName);
                $file->setWebsiteId((int)$this->storeManager->getStore()->getWebsiteId());
                $file->getResource()->save($file);
                if ($this->config->getCustomerGroupAfterUpload()) {
                    $customer = $this->customerSession->getCustomer();
                    $customer->setGroupId($this->config->getCustomerGroupAfterUpload());
                    $customer->getResource()->save($customer);
                }
                $this->email->send($this->customerSession->getCustomer());
                $this->messageManager->addSuccessMessage(__('Document successfully saved'));
            }else{
                $this->messageManager->addErrorMessage(__("A feltöltött fájl mérete meghaladj a 5MB-os limitet."));
            }
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        /** @var \Magento\Framework\Controller\Result\Redirect\Interceptor $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(FileUploadEnum::ACTION_INDEX);

        return $resultRedirect;
    }

}