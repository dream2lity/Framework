<?php


namespace core\Util;

use core\Util\Str;

class File
{
    const LOAD_DIR = 1;
    const LOAD_FILE = 2;
    const LOAD_BOTH = 3;

    /**
     * 加载目录下所有文件路径
     *
     * @param string    $path       文件路径
     * @param int       $loadType   加载类型
     * @return array
     */
    public static function loadDir(string $path,int $loadType = File::LOAD_FILE)
    {
        static $files = [];
        static $dirs = [];
        $path = Str::endWith($path, [DIRECTORY_SEPARATOR]) ? $path : $path . DIRECTORY_SEPARATOR;
        foreach (glob($path . '*') as $item) {
            if (is_dir($item)) {
                $dirs[] = $item;
                File::loadDir($item);
            } else {
                $files[] = $item;
            }
        }
        switch ($loadType) {
            case File::LOAD_DIR:
                return $dirs;
            case File::LOAD_FILE:
                return $files;
            case File::LOAD_BOTH:
                return array_merge($dirs, $files);
            default:
                throw new \InvalidArgumentException(sprintf('loadType[%s] Undefined. Try [File::LOAD_DIR|File::LOAD_FILE|File::LOAD_BOTH]'), $loadType);
        }
    }

    /**
     * 返回上 level 级目录全路径
     *
     * @param string $path
     * @param int $level
     * @return string
     */
    public static function getDirName(string $path, int $level = 1)
    {
        $path = realpath($path);
        while ($level > 0) {
            $path = dirname($path);
            $level--;
        }
        return $path;
    }

    /**
     * 创建多级目录
     *
     * @param string $path
     */
    public static function createDir(string $path)
    {
        is_dir($path) || mkdir($path, 0755, true);
    }

    /**
     * 删除多级目录
     *
     * @param string $path
     */
    public static function removeDir(string $path)
    {
        if (is_dir($path)) {
            foreach (glob($path . DIRECTORY_SEPARATOR . '*') as $item) {
                self::removeDir($item);
            }
            rmdir($path);
        } else {
            unlink($path);
        }
    }
}

