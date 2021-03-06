<?php

namespace Safecrow;

use Safecrow\Http\Client;
use Safecrow\Helpers\FilesHelper;

class Shipping
{
    private 
        $client,
        $orderId
    ;
    
    public function __construct(Client $client, $orderId)
    {
        $this->client = $client;
        $this->orderId = $orderId;
    }
    
    /**
     * Создание запроса на возврат/доставку
     * 
     * @param array $fields
     * @param bool $return - если true, то запрос на возврат
     * @return array
     */
    public function create($fields, $return=false)
    {
        if(!empty($fields['attachment'])) {
            $fields['attachment'] = $this->processFiles($fields['attachment']);
        }
        
        $shipping = $return ? "shipping_back" : "shipping";
        $res = $this->getClient()->post("/orders/{$this->getOrderId()}/{$shipping}", array("tracking" => $fields));
        
        return isset($res['tracking']) ? $res['tracking'] : $res;
    }
    
    /**
     * Получение информации о доставке/возврате
     * @param bool $return - если true, то запрос на возврат 
     * @return array
     */
    public function get($return = false)
    {
        $shipping = $return ? "shipping_back" : "shipping";
        $res = $this->getClient()->get("/orders/{$this->getOrderId()}/{$shipping}");
        
        return isset($res['tracking']) ? $res['tracking'] : $res;
    }
    
    private function getClient()
    {
        return $this->client;
    }
    
    private function getOrderId()
    {
        return $this->orderId;
    }
    
    /**
     * Проверяет содержимое массива
     * @param array files
     * @throws \Safecrow\Exceptions\IncorrectAttachmentException
     * @return array
     */
    private function processFiles(array $files)
    {
        //Если передали урлы, то попытаемся получить инфу о файле
        foreach ($files as $k => $file) {
            if(is_string($file)) {
                $files[$k] = FilesHelper::prepareFile($file);
            }
        }
        
        foreach($files as $k => $file) {
            if(!is_array($file)) {
                unset($files[$k]);
            }
            
            if(!App::IsAllowedFileType($file['content_type'])) {
                throw new IncorrectAttachmentException;
            }
        }
    }
}