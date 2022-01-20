<?php
namespace Oander\Queue\Enum;

final class Status
{
    const STATUS_INIT           = 0;
    const STATUS_INPROGRESS     = 100;
    const STATUS_CLOSED         = 1000;
    const STATUS_INACTIVATED    = 1001;
    const STATUS_MAXRETRYREACHED= 1002;
    const STATUS_ERROR          = -1;

    static public function getActiveStatuses()
    {
        return [self::STATUS_INIT, self::STATUS_INPROGRESS];
    }

    static public function getInactiveStatuses()
    {
        return [self::STATUS_CLOSED, self::STATUS_ERROR, self::STATUS_MAXRETRYREACHED];
    }
}