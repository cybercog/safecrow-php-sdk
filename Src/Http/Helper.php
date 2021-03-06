<?php

namespace Safecrow\Http;

class Helper
{
    public static function getStreamData()
    {
        $arData = array();
        $putdata = fopen("php://input", "r");
        while ($data = fread($putdata, 4096)){
            parse_str($data, $arData);
        }
        
        fclose($putdata);

        return $arData;
    }
    
    public static function getData($method)
    {
        switch(strtolower($method))
        {
            case "post": 
                return $_POST;
            case "get": 
                return $_GET;
            case "put":
            case "delete":
            case "patch":
                return self::getStreamData();
        }
        
        return false;
    }
}