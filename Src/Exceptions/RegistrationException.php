<?php
namespace Safecrow\Exceptions;

class RegistrationException extends SafecrowException
{
    public function __construct()
    {
        parent::__construct("Заполните обязательные поля");
    }
}