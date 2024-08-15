<?php

class Headers
{
    public static $_headers = [];

    public static function parseHeaders()
    {
        $h_data = self::getRequestHeaders();
        $headers = [];
        foreach ($h_data as $key => $item) {
            $headers[strtolower($key)] = $item;
        }

        if (function_exists('getallheaders')) {
            $headers2 = getallheaders();
            $headers3 = [];
            if (count($headers2)) {
                foreach ($headers2 as $key => $item) {
                    $headers3[strtolower($key)] = $item;
                }
                $headers = array_merge($headers, $headers3);
            }
        }

        self::$_headers = $headers;
    }

    public static function get($key = '', $default_value = null)
    {
        if (!isset(self::$_headers[strtolower($key)])) {
            return $default_value;
        }

        return self::$_headers[strtolower($key)];
    }

    public static function getRequestHeaders()
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) != 'HTTP_') {
                continue;
            }
            $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $headers[$header] = $value;
        }

        return $headers;
    }
}
