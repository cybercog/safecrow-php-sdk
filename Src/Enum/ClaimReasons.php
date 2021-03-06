<?php

namespace Safecrow\Enum;

class ClaimReasons
{
    const SHIPPING_MISSED = "shipping_missed";
    const WRONG_PACKAGE = "wrong_package";
    const PACKAGE_BROKEN = "package_broken";
    const MISSING_GOODS = "missing_goods";
    const TO_LATE = "to_late";
    const NOT_SENDED = "not_sended";
    const OTHER = "other";
    
    public static function getClaimReasons()
    {
        $oReflect = new \ReflectionClass(__CLASS__);
        return $oReflect->getConstants();
    }
}