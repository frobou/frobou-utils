<?php

namespace Frobou\Utils;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Functions {

    public static function getIpNetwork($value)
    {
        $range = '';
        $res = explode('.', $value);
        array_pop($res);
        foreach ($res as $vl) {
            $range = $range . $vl . '.';
        }
        return substr($range, 0, strlen($range) - 1);
    }

    public static function getDocumentRoot()
    {
        return filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/';
    }

    /**
     *
     * @return Logger
     */
    public static function getLogger($absolute_path, $level)
    {
        $log = new Logger('radius');
        $log->pushHandler(new StreamHandler($absolute_path, $level));
        return $log;
    }

}