<?php
/**
 * Oander_ImportM2
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\ImportM2\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Oander\ImportM2\Model\Import;

/**
 * Class Start
 *
 * @package Oander\ImportM2\Controller\Adminhtml\Import
 */
class Start extends Action
{
    /**
     * @var Import
     */
    private $import;

    /**
     * Start constructor.
     *
     * @param Action\Context $context
     * @param Import         $import
     */
    public function __construct(
        Action\Context $context,
        Import $import
    ) {
        var_dump(33333);die;
        $this->import = $import;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $this->import();

            $this->messageManager->addSuccessMessage(
                __('Magento2 Import has finished')
            );
        } catch (\Throwable $throwable) {
            $this->messageManager->addErrorMessage(
                __($throwable->getMessage())
            );
        }

        $resultRedirect->setUrl(
            $this->_redirect->getRefererUrl()
        );

        return $resultRedirect;
    }

    private function import()
    {
        // TODO cron
        $this->import->execute();
    }
}
