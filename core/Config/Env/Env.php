<?php

namespace core\Config\Env;


class Env implements EnvInterface
{
    private static $env = [];

    public static function get(string $key = '')
    {
        return $key == '' ? self::$env : (self::$env[$key] ?? null);
    }

    public static function set(array $settings)
    {
        foreach ($settings as $key => $val) {
            self::$env[$key] = $val;
        }
    }
}