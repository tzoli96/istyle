<?php
/**
 * Oander_FrameworkCollectionWorkaround
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Oander_FrameworkCollectionWorkaround',
    __DIR__
);
