<?php

namespace Oney\ThreeByFour\Api\Marketing;

interface LegalNoticeInterface
{
    /**
     * @param string $type
     * @return mixed
     */
    function getLegalNotice($type = '');
}
