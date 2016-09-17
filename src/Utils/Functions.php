<?php

namespace Frobou\Utils;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Functions {

    /**
     * Remove tudo que não for numérico de uma string.
     * $confirm_only retorna boolean informando se a string de saída seria alterada
     * @param type $str
     * @param type $confirm_only
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public static function validateNumbersOnly($str, $confirm_only = false)
    {
        if (is_numeric($str) && $confirm_only === true) {
            return true;
        }
        if (is_array($str) || is_object($str)) {
            throw new \InvalidArgumentException('Comparation argument is invalid');
        }
        if ($confirm_only !== false) {
            if ($confirm_only !== true) {
                throw new \InvalidArgumentException('Confirm-only argument is invalid');
            }
        }
        $out = preg_replace("/[^0-9]/", "", $str);
        //$confirm_only serve para somente confirmar se é numerico
        if (!$confirm_only) {
            //se $confirm_only for false, entendo que é pra retornar a string modificada
            return $out;
        }
        //se $confirm_only for true, entendo que é pra retornar o estado
        return $out === $str;
    }

    /**
     *
     * @param array $value
     * @return boolean
     */
    public static function validateEmptyArray(array $value)
    {
        if (!is_array($value) || count($value) === 0) {
            return false;
        }
        foreach ($value as $val) {
            if ($val === '' || $val === null) {
                return false;
            }
        }
        return true;
    }

    /**
     *
     * @param type $email
     * @return boolean
     */
    public static function validateEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }
        return true;
    }

    /**
     *
     * @param type $domain_name
     * @return boolean
     */
    public static function validateDomainName($domain_name)
    {
        $ret = preg_match('/^([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?/i', $domain_name) //valid chars check
            && preg_match("/^.{1,253}$/", $domain_name) //overall length check
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name);
        if ($ret == true) {
            $dom = explode('.', $domain_name);
            if (count($dom) > 3 || (count($dom) > 2 && strlen($dom[count($dom) - 1]) != 2)) {
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    /**
     *
     * @param type $ip
     * @param type $groups
     * @param type $show_errors
     * @return boolean|string
     * @throws \InvalidArgumentException
     */
    public static function validateIpAddress($ip, $groups = 4, $show_errors = true)
    {
        // quebra o valor informado em partes separadas por '.'
        $_ip = explode('.', $ip);
        // verifica se a quantidade de partes é exatamente 4
        if (count($_ip) != $groups) {
            if ($show_errors) {
                return "Ip deve conter {$groups} octetos com valores de 0 a 255, separados pelo caracter \".\" (ponto)!";
            } else {
                throw new \InvalidArgumentException();
            }
        }
        $ct = 1;
        foreach ($_ip as $value) {
            if ($value == '') {
                if ($show_errors) {
                    return 'Octeto não pode ser vazio!';
                } else {
                    throw new \InvalidArgumentException();
                }
            }
            // verifica se cada parte é numerica
            if (!self::validateNumbersOnly($value, true)) {
                if ($show_errors) {
                    return "Octeto deve ser numérico!<br>Valor recebido no octeto {$ct}: {$value}";
                } else {
                    throw new \InvalidArgumentException();
                }
            }
            // verifica se cada parte está entre 0 e 255
            if (intval($value) < 0 || intval($value) > 255) {
                if ($show_errors) {
                    return "Octeto deve estar entre 0 e 255!<br>Valor recebido no octeto {$ct}: {$value}";
                } else {
                    throw new \InvalidArgumentException();
                }
            }
            $ct ++;
        }
        return true;
    }

    /* pra que eu fiz isso? */
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