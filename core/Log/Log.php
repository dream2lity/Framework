<?php

namespace core\Log;

use core\Util\File;

class Log implements LogInterface
{
    private static $dir;

    private static $fileSize;

    public static function write(string $path, string $content)
    {
        File::createDir(dirname($path));
        $time = date('Y-m-d H:i:s', time());
        @file_put_contents($path, sprintf("[%s]  %s \r\n", $time, $content), FILE_APPEND);
    }

    public static function __callStatic($name, $arguments)
    {
        if (in_array($name, self::LEVEL)) {
            if (!isset(self::$dir)) {
                throw new \Exception('logDir Undefined');
            }
            if (!isset(self::$fileSize)) {
                throw new \Exception('fileSize Undefined');
            }
            $logFilePath = self::$dir . DIRECTORY_SEPARATOR . date('Y-m-d', time()) . '.' . $name . '.log';
            $l = 1;
            while (file_exists($logFilePath) && filesize($logFilePath) > self::$fileSize) {
                $logFilePath = self::$dir . DIRECTORY_SEPARATOR . date('Y-m-d', time()) . '.' . $name . '.' . $l++ . '.log';
            }
            self::write($logFilePath, $arguments[0]);
        }
    }
}