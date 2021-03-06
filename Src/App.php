<?php
namespace Safecrow;

use Safecrow\Http\Client;
use Safecrow\Exceptions\AuthException;
use Safecrow\Interfaces\IConfig;

class App
{
    private
        $config,
        $sysClient,
        $usrClient
    ;
    
    public function __construct(IConfig $config)
    {
        $this->config = $config;
        
        $this->sysClient = new Client($this->getKey(), $this->getSecret(), $this->getHost());

        $this->usrClient = clone $this->sysClient;
        $this->usrClient->useUserRequests();
        
        $this->users = new Users($this->sysClient);
        $this->orders = new Orders($this->usrClient, $this->users);
    }
    
    public function getUsers()
    {
        return new Users($this->sysClient);
    }
    
    public function getSubscriptions()
    {
        return new Subscriptions($this->sysClient);
    }
    
    public function getOrders($userId)
    {
        if(!$this->getUsers()->getUserToken($userId)) {
            throw new AuthException();
        }
        
        return new Orders($this->usrClient, $userId);
    }
    
    private function getKey()
    {
        return $this->config->getToken();
    }
    
    private function getSecret()
    {
        return $this->config->getSecret();
    }
    
    public function getHost()
    {
        return $this->config->getHost();
    }
    
    public static function IsAllowedFileType($type)
    {
        foreach (Config::$arAllowedFileTypes as $group) {
            if(in_array($type, $group)) {
                return true;
            }
        }
        
        return false;
    }
}