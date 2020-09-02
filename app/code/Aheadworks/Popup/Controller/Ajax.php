<?php
namespace Aheadworks\Popup\Controller;

/**
 * Class Ajax
 * @package Aheadworks\Popup\Controller
 */
abstract class Ajax extends \Magento\Framework\App\Action\Action
{
    /**
     * Customer session
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Formkey validator
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Action\Context $context
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
    }

    /**
     * Validate Form Key
     *
     * @return bool
     */
    protected function _validateFormKey()
    {
        return $this->formKeyValidator->validate($this->getRequest());
    }
}
