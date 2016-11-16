<?php

namespace Frobou\Utils;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Functions {

    /**
     * @param $value
     * @return string
     */
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
    public static function getLogger($log_name, $absolute_path, $level)
    {
        $log = new Logger($log_name);
        switch (strtoupper($level)){
            case 'INFO':
                $lvl = Logger::INFO;
                break;
            case 'NOTICE':
                $lvl = Logger::NOTICE;
                break;
            case 'WARNING':
                $lvl = Logger::WARNING;
                break;
            case 'ALERT':
                $lvl = Logger::ALERT;
                break;
            case 'CRITICAL':
                $lvl = Logger::CRITICAL;
                break;
            default:
                $lvl = Logger::INFO;
        }
        $log->pushHandler(new StreamHandler($absolute_path, $lvl));
        return $log;
    }

}