<?php

namespace Frobou\Utils;

class GetOpts
{
    public static function get($options, $longopts)
    {
        if (PHP_SAPI === 'cli' || empty($_SERVER['REMOTE_ADDR']))  // command line
        {
            return getopt($options, $longopts);
        } else if (isset($_REQUEST))  // web script
        {
            $found = array();
            $shortopts = preg_split('@([a-z0-9][:]{0,2})@i', $options, 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $opts = array_merge($shortopts, $longopts);
            foreach ($opts as $opt) {
                if (substr($opt, -2) === '::')  // optional
                {
                    $key = substr($opt, 0, -2);
                    if (isset($_REQUEST[$key]) && !empty($_REQUEST[$key]))
                        $found[$key] = $_REQUEST[$key];
                    else if (isset($_REQUEST[$key]))
                        $found[$key] = false;
                } else if (substr($opt, -1) === ':')  // required value
                {
                    $key = substr($opt, 0, -1);
                    if (isset($_REQUEST[$key]) && !empty($_REQUEST[$key]))
                        $found[$key] = $_REQUEST[$key];
                } else if (ctype_alnum($opt))  // no value
                {
                    if (isset($_REQUEST[$opt]))
                        $found[$opt] = false;
                }
            }
            return $found;
        }
        return false;
    }
}