<?php

namespace Safecrow\Enum;

class PayerTypes
{
    const BUSINESS = "business";
    const PERSONAL = "personal";
    
    public static function getPayerTypes()
    {
        $oReflection = new \ReflectionClass(__CLASS__);
        return $oReflection->getConstants();
    }
}