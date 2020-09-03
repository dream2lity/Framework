<?php

use core\Util\Reflector;
use core\Util\Str;

if (!function_exists('make')) {
    function make($className, $paramArr = [])
    {
        return Reflector::make($className, $paramArr);
    }
}

if (!function_exists('call')) {
    function call($className, $method, $paramArr = [])
    {
        return Reflector::call($className, $method, $paramArr);
    }
}

if (!function_exists('getPropertyValue')) {
    function getPropertyValue($class, $name)
    {
        return Reflector::getPropertyValue($class, $name);
    }
}


if (!function_exists('analysisJson')) {
    /**
     * 解析json结构，数组只保留第一个元素
     *
     * @param array $arr    json_decode后的数组，json_decode($str, true)
     */
    function analysisJson(&$arr)
    {
        Str::analysisJson($arr);
    }
}

if (!function_exists('prettyJson')) {
    /**
     * 美化json数据，空格填充
     *
     * @param mixed $json
     * @return string
     */
    function prettyJson($json)
    {
        return Str::prettyJson($json);
    }
}

if (!function_exists('subJson')) {
    /**
     * json美化并取出一段
     * @param  string $json        json字符串
     * @param  int    $offset      开始位置
     * @param  [int   $length        = null]   截取长度
     * @param  [bool  $needLineNum = false]  是否需要显示行号
     * @return string 美化后的json字符串
     */
    function subJson(string $json, int $offset, int $length = null, bool $needLineNum = false)
    {
        return Str::subJson($json, $offset, $length, $needLineNum);
    }
}