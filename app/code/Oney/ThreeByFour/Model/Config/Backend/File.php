<?php

namespace Oney\ThreeByFour\Model\Config\Backend;

class File extends \Magento\Config\Model\Config\Backend\File
{
    const ALLOWED_EXTENSIONS = ['pdf'];
    /**
     *
     */
    public function _getAllowedExtensions()
    {
        return self::ALLOWED_EXTENSIONS;
    }
}
