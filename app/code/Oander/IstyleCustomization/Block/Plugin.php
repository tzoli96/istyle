<?php
/**
 *   /$$$$$$   /$$$$$$  /$$   /$$ /$$$$$$$  /$$$$$$$$ /$$$$$$$
 *  /$$__  $$ /$$__  $$| $$$ | $$| $$__  $$| $$_____/| $$__  $$
 * | $$  \ $$| $$  \ $$| $$$$| $$| $$  \ $$| $$      | $$  \ $$
 * | $$  | $$| $$$$$$$$| $$ $$ $$| $$  | $$| $$$$$   | $$$$$$$/
 * | $$  | $$| $$__  $$| $$  $$$$| $$  | $$| $$__/   | $$__  $$
 * | $$  | $$| $$  | $$| $$\  $$$| $$  | $$| $$      | $$  \ $$
 * |  $$$$$$/| $$  | $$| $$ \  $$| $$$$$$$/| $$$$$$$$| $$  | $$
 *  \______/ |__/  |__/|__/  \__/|_______/ |________/|__/  |__/
 *
 *
 *.......................................................................................................
 *................................................/&&&&&&&&&\............................................
 *..........................................*&&&&&&&&&&&&&&&&&&&&&&*.....................................
 *.......................................*&&&&&&&&&&&&&&&&&&&&&&&&&&&*...................................
 *....................................*&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&*...............................
 *..................................*&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&*.............................
 *................................*&&&&&&&&&&&*__   &&&&&&&&&&&&&&&&&&&&&&&*.............................
 *...............................*&&&&&&&&&&&&&&&*________*&&&&&&&&&&&&&&&&&*............................
 *.............................*&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&*..........................
 *............................*&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&*..........................
 *............................*&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&*.........................
 *...........................*&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&*........................
 *...........................*&&&&&&&&&&*...&&&&&&&&&&&&&&&&&&*...&&&&&&&&&&&&&&*........................
 *...........................*&&&&&&\                                   /&&&&&&&&*.......................
 *...........................*&&&&&&&.ˇ*****ˇˇˇˇˇˇˇˇˇˇˇˇˇˇˇˇˇˇˇˇˇˇ*****ˇ.&&&&&&&*........................
 *............................*&&&&&&|.                                .|&&&&&&&*........................
 *......................../&|*&&&&&&&\.  *                       *    ./&&&&&&&*.........................
 *.....................*&&&&&&|.&&&&&&\.  *\&&&&\.        ./&&&&/*   ./&&&&&&&*..........................
 *...................*&&&&&&&/&&&&&&&&&\.    .&&&&&.    .&&&&&.     ./&&&&&&&*...........................
 *.................*&&&&&&.&&&&&&&&&&&&&\.    \&&&/      \&&&/    ./&&&&&&&&*............................
 *.................&&&&&&&&.&&&&&&&&&&&&&&\.                    ./&&&&&&&&&*.............................
 *.................&&&&&&&.&&&&     *&&&&&&&&&\.              ./&&&&&&&&&*...............................
 *.................*&&&&.&&&&        *&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&*..................................
 *....................*&&&&.&&&&.       *&&&&&&&&&&&&&&&&&&&&&&&&&&&*....................................
 *.....................*&&.   *&*           *&&&&&&&&&&&&&&&&&&&*........................................
 *..............................................*\&&&&&&&&&/*............................................
 *._   _ _____ _   _      _          _____   _         _____ _____     _         __  __           _____
 *| \ | |_   _| \ | |    | |  /\    / ____| (_)       |  __ \_   _|   | |  /\   |  \/  |   /\    / ____|
 *|  \| | | | |  \| |    | | /  \  | (___    _ _ __   | |__) || |     | | /  \  | \  / |  /  \  | (___
 *| . ` | | | | . ` |_   | |/ /\ \  \___ \  | | '_ \  |  ___/ | | _   | |/ /\ \ | |\/| | / /\ \  \___ \
 *| |\  |_| |_| |\  | |__| / ____ \ ____) | | | | | | | |    _| || |__| / ____ \| |  | |/ ____ \ ____) |
 *|_| \_|_____|_| \_|\____/_/    \_\_____/  |_|_| |_| |_|   |_____\____/_/    \_\_|  |_/_/    \_\_____/
 *
 *
 *
 * Plugin
 * @team    Ninjas in Pijamas
 * @author  Róbert Betlen <robert.betlen@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */
namespace Oander\IstyleCustomization\Block;

/**
 * Class Plugin
 * @package Oander\IstyleCustomization\Block
 */
class Plugin extends \Anowave\Ec\Block\Plugin
{

    /**
     * @var \Anowave\Ec\Model\Apply
     */
    private $canApply = false;

    /**
     * @param \Magento\Framework\View\Element\Template $block
     * @param $content
     * @return string
     */
    public function afterToHtml($block, $content)
    {
        if ($this->_helper->isActive() && $this->canApply)
        {
            switch($block->getNameInLayout())
            {
                case 'add.to.cart.right':
                case 'product.info.addtocart':
                case 'product.info.addtocart.additional':
                case 'product.info.addtocart.bundle':
                    return $this->augmentAddCartBlock($block, $content);
                case 'category.products.list': 										return $this->augmentListBlock($block, $content);
                case 'catalog.product.related':										return $this->augmentListRelatedBlock($block, $content);
                case 'product.info.upsell':											return $this->augmentListUpsellBlock($block, $content);
                case 'view.addto.wishlist':											return $this->augmentWishlistBlock($block, $content);
                case 'wishlist_sidebar':											return $this->augmentWishlistSidebarBlock($block, $content);
                case 'view.addto.compare':											return $this->augmentCompareBlock($block, $content);
                case 'checkout.cart':												return $this->augmentCartBlock($block, $content);
                case 'checkout.root': 												return $this->augmentCheckoutBlock($block, $content);
                case 'checkout.cart.item.renderers.simple.actions.remove':
                case 'checkout.cart.item.renderers.bundle.actions.remove':
                case 'checkout.cart.item.renderers.virtual.actions.remove':
                case 'checkout.cart.item.renderers.default.actions.remove':
                case 'checkout.cart.item.renderers.grouped.actions.remove':
                case 'checkout.cart.item.renderers.downloadable.actions.remove':
                case 'checkout.cart.item.renderers.configurable.actions.remove':    return $this->augmentRemoveCartBlock($block, $content);
                case 'ec_noscript':													return $this->augmentAmp($block, $content);
                default:
                    switch(true)
                    {
                        case $block instanceof \Magento\Catalog\Block\Product\Widget\NewWidget: 	return $this->augmentWidgetBlock($block, $content);
                        case $block instanceof \Magento\CatalogWidget\Block\Product\ProductsList:	return $this->augmentWidgetListBlock($block, $content);
                    }
                    break;
            }
        }

        return $content;
    }
}
