<?php

namespace Safecrow\Interfaces;

interface IConfig
{
    public function getToken();
    
    public function getSecret();
}