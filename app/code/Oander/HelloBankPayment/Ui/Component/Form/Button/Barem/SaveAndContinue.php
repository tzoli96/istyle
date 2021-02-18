<?php
namespace Oander\HelloBankPayment\Ui\Component\Form\Button\Barem;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Oander\HelloBankPayment\Enum\Request;

class SaveAndContinue extends GeneralButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label'          => __('Save and Continue Edit'),
            'class'          => 'save',
            'on_click'       => sprintf("location.href = '%s';", $this->getSaveAndContinueUrl()),
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'saveAndContinueEdit'],
                ],
            ],
            'sort_order'     => 80,
        ];
    }

    private function getSaveAndContinueUrl()
    {
        return $this->getUrl(
            Request::ACTION_BAREM_FORM_SAVE,
            [Request::PARAM_BACK => true]
        );
    }
}