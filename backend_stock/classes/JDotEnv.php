<?php

define('HD', __DIR__);
define('ENV_TYPE', 'local');

class JDotEnv
{
    private static $_env_filename = false;
    private static $_env_sections_data = null; // Multi-dimensional array for sectioned data
    private static $_env_simple_data = null; // Single-dimensional array for simple data (non-sectioned)

    public static function parseEnvData()
    {
        $filesArr = scandir(HD);

        // Determine the environment file
        if (in_array('.env.' . ENV_TYPE, $filesArr)) {
            self::$_env_filename = '.env.' . ENV_TYPE;
        } elseif (in_array('.env', $filesArr)) {
            self::$_env_filename = '.env';
        }

        if (!self::$_env_filename) {
            throw new Exception("Environment file not found.");
        }

        $filePath = HD . '/' . self::$_env_filename;
        self::$_env_sections_data = parse_ini_file($filePath, true, INI_SCANNER_TYPED);
        self::$_env_simple_data = parse_ini_file($filePath, false, INI_SCANNER_TYPED);
    }

    public static function get($key = false, $default = null, $section = false)
    {
        if (!$key) {
            return $default;
        }

        $dataArr = $section ? self::$_env_sections_data : self::$_env_simple_data;

        if ($section) {
            return $dataArr[$section][$key] ?? $default;
        }

        return $dataArr[$key] ?? $default;
    }
}
