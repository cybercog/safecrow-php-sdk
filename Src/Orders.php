<?php
namespace Safecrow;

use Safecrow\Http\Client;
use Safecrow\Enum\Payers;
use Safecrow\Exceptions\OrderCreateException;
use Safecrow\Helpers\FilesHelper;

class Orders
{
    private 
        $client,
        $userId
    ;
    
    public function __construct(Client $client, $userId)
    {
        $this->client = $client;
        $this->userId = $userId;
    }
    
    /**
     * Получение объекта для работы со счетами
     * 
     * @param int $orderId
     * @return \Safecrow\Billing
     */
    public function getBilling($orderId)
    {
        return new Billing($this->getClient(), $orderId);
    }
    
    /**
     * Получение объекта для работы с изменениями заказа
     * 
     * @param int $orderId
     * @return \Safecrow\Changes
     */
    public function getChanges($orderId)
    {
        return new Changes($this->getClient(), $orderId);
    }
    
    /**
     * Получение объекта для работы с жалобами
     * 
     * @param int $orderId
     * @return \Safecrow\Claims
     */
    public function getClaims($orderId)
    {
        return new Claims($this->getClient(), $orderId);
    }
    
    /**
     * Получение объекта для работы с оплатой
     * 
     * @param int $orderId
     * @return \Safecrow\Payments
     */
    public function getPayments($orderId)
    {
        return new Payments($this->getClient(), $orderId);
    }
    
    /**
     * Получение объекта для работы с доставкой/возвратом
     * 
     * @param int $orderId
     * @return \Safecrow\Shipping
     */
    public function getShipping($orderId)
    {
        return new Shipping($this->getClient(), $orderId);
    }
    
    /**
     * Получение объекта для работы с переходами
     * 
     * @param int $orderId
     * @return \Safecrow\Transitions
     */
    public function getTransitions($orderId)
    {
        return new Transitions($this->getClient(), $orderId);
    }
    
    /**
     * Создание нового заказа
     * 
     * @param array $order
     * @return array
     */
    public function create(array $order)
    {
        $this->validate($order);
        if(!empty($order['attachments'])) {
            $order['attachments'] = $this->processFiles($order['attachments']);
        }
        
        if(!isset($order['supplier_id']) || !(int)$order['supplier_id']) {
            $order['supplier_id'] = $this->userId;
        }
        
        if(!isset($order['verify_days']) || !(int)$order['verify_days']) {
            $order['verify_days'] = Config::DEFAULT_VERIFY_DAYS;
        }
        
        $res = $this->getClient()->post("/orders", array('order' => $order));
        
        return isset($res['order']) ? $res['order'] : $res;
    }
    
    /**
     * Предварительный расчет комиссии
     * 
     * @param float $sum
     * @param string $payer
     * 
     * @return array;
     */
    public function calcComission($sum, $payer)
    {
        if(!(float)$sum || !in_array($payer, Payers::getPayers())) {
            return false;
        }
        
        $res = $this->getClient()->post("/orders/calc_commission", array('cost' => (int)$sum, 'commission_payer' => $payer));
        
        return $res;
    }
    
    /**
     * Редактирование заказа
     * 
     * @param int $orderId
     * @param array $fields
     * 
     * @return array|bool
     */
    public function editOrder($orderId, $fields)
    {
        if(!(int)$orderId) {
            return false;
        }
        
        $res = $this->getClient()->patch("/orders/{$orderId}", array('order' => $fields));
        
        return isset($res['order']) ? $res['order'] : $res;
    }
    
    /**
     * Получение списка заказов
     * 
     * @param int $page
     * @param int $per
     * 
     * @return array
     */
    public function getList($page=null, $per=null)
    {
        $params = array();
        if((int)$page) {
            $params['page'] = (int)$page;
        }
        
        if((int)$per) {
            $params['per'] = (int)$per;
        }
        
        $res = $this->getClient()->get("/orders", $params);
        
        return isset($res['orders']) ? $res['orders'] : $res;
    }
    
    /**
     * Получение заказа по ID
     * 
     * @param int $id
     * @return boolean|array
     */
    public function getByID($id)
    {
        if(!isset($id) || !(int)$id) {
            return false;
        }
        
        $res = $this->getClient()->get("/orders/{$id}");
        
        return isset($res['order']) ? $res['order'] : $res;
    }
    
    /**
     * Валидация полей заказа
     * 
     * @param array $order
     * @throws \Safecrow\Exceptions\OrderCreateException
     * @return void
     */
    private function validate(array $order)
    {
        $arErrors = array();
        
        if(empty($order['order_description'])) {
            $arErrors['order_description'] = 'Не заполнено описание сделки';
        }
        
        if(!isset($order['cost']) || !(float)($order['cost'])) {
            $arErrors['cost'] = 'Не указана стоимость сделки';
        }
        
        if(!isset($order['commission_payer']) || !in_array($order['commission_payer'], Payers::getPayers())) {
            $arErrors['commission_payer'] = 'Недопустимый тип плательщика';
        }
        
        if(!empty($arErrors)) {
            $ex = new OrderCreateException("Незаполнены обязательные поля"); 
            $ex->setData($arErrors);
            
            throw $ex;
        }
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

    private function getClient()
    {
        return $this->client;
    }
    
    private function getUsers()
    {
        return $this->$users;
    }
}