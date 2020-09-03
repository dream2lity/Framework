<?php

namespace core\Config\Env;


interface EnvInterface
{
    public static function get(string $key);

    public static function set(array $settings);
}