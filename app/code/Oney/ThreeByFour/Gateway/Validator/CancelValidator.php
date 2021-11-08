<?php


namespace Oney\ThreeByFour\Gateway\Validator;


use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Oney\ThreeByFour\Logger\Logger;

class CancelValidator extends AbstractValidator
{
    /**
     * @var Logger
     */
    protected $_logger;

    public function __construct(
        ResultInterfaceFactory $resultFactory,
        Logger $logger)
    {
        $this->_logger = $logger;
        parent::__construct($resultFactory);
    }

    /**
     * @inheritDoc
     */
    public function validate(array $validationSubject)
    {
        $this->_logger->info('Oney :: Cancel Validator :', $validationSubject);
        return $this->createResult(true, [], []);
    }
}
