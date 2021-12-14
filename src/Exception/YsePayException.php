<?php

namespace Shion\YsePay\Exception;

class YsePayException extends \Exception
{
    public function __construct($error, $message = '')
    {
        parent::__construct($error . ':' . $message);
    }
}
