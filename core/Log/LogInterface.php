<?php

namespace core\Log;


interface LogInterface
{
    const LEVEL = [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'info',
        'debug',
    ];

    public static function write(string $path, string $content);
}