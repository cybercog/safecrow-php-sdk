<?php

namespace Safecrow\Exceptions;

class IncorrectAttachmentException extends SafecrowException
{
    public function __construct()
    {
        return parent::__construct("������������ ��� �����");
    }
}