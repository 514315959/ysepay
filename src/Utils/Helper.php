<?php

namespace Shion\YsePay\Utils;

use Shion\YsePay\Exception\YsePayException;

class Helper
{
    public static function GetMethodClass($method)
    {
        $arr = explode('.', $method);
        array_shift($arr);
        $str = implode(' ', $arr);
        $str = ucwords($str);

        $className = 'Shion\YsePay\Commands\\' . str_replace(' ', '', $str) . 'Command';

        if (!class_exists($className)) {
            throw new YsePayException($method . ' class file does not exist');
        }
        $class = new $className();

        return $class->setMethod($method);
    }
}
