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
 * Oander_IstyleBase
 *
 * @author  David Belicza <david.belicza@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types = 1);

namespace Oander\IstyleBase\Plugin\Magento\Customer\Block\Form;

use Magento\Customer\Block\Form\Register\Interceptor;

/**
 * Class Register
 *
 * It disables the newsletter subscription on registration page without disable
 * the newsletter block in Footer.
 *
 * @package Oander\IstyleBase\Plugin\Magento\Customer\Block\Form
 */
class Register
{
    /**
     * @param Interceptor $subject
     * @param \Closure    $method
     *
     * @return bool
     */
    public function aroundIsNewsletterEnabled(Interceptor $subject, \Closure $method): bool
    {
        return false;
    }
}
