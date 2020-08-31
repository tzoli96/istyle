<?php
namespace Aheadworks\Popup\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Effect
 * @package Aheadworks\Popup\Model\Source
 */
class Effect implements OptionSourceInterface
{
    const FADE_ZOOM = 'mfp-fade-zoom';
    const FADE_SLIDE = 'mfp-fade-slide';
    const NEWSPAPER = 'mfp-newspaper';
    const HORIZONTAL_MOVE = 'mfp-move-horizontal';
    const TOP_MOVE = 'mfp-move-from-top ';
    const UNFOLD_3D = 'mfp-3d-unfold';
    const ZOOM_OUT = 'mfp-zoom-out';

    const DEFAULT_VALUE = 1;

    /**
     * Get option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $newArray = [];
        $positions = $this->toOptionArray();

        foreach ($positions as $position) {
            $newArray[$position['value']] = $position['label'];
        }
        return $newArray;
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::FADE_ZOOM,  'label' => __('Fade-zoom')],
            ['value' => self::FADE_SLIDE,  'label' => __('Fade-slide')],
            ['value' => self::NEWSPAPER,  'label' => __('Newspaper')],
            ['value' => self::HORIZONTAL_MOVE,  'label' => __('Horizontal move')],
            ['value' => self::TOP_MOVE,  'label' => __('Move from top')],
            ['value' => self::UNFOLD_3D,  'label' => __('3d unfold')],
            ['value' => self::ZOOM_OUT,  'label' => __('Zoom-out')],
        ];
    }
}
