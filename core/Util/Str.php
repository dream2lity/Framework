<?php


namespace core\Util;


class Str
{
    /**
     * 判断字符串str是否是以chars中任意一个字符子串结尾
     *
     * @param string $str
     * @param array $chars
     * @return bool
     */
    public static function endWith(string $str, array $chars)
    {
        $flag = false;
        foreach ($chars as $char) {
            if (substr($str, - strlen($char)) === $char) {
                $flag = true;
                break;
            }
        }
        return $flag;
    }

    /**
     * 解析json结构，数组只保留第一个元素
     *
     * @param array $arr    json_decode后的数组，json_decode($str, true)
     */
    public static function analysisJson(&$arr)
    {
        if (is_array($arr)) {
            if (array_keys($arr) === range(0, count($arr) - 1)) {
                foreach($arr[0] as $k => $v) {
                    analysisJson($arr[0][$k]);
                }
                $arr = [$arr[0]];
            } else {
                foreach($arr as $k => $v) {
                    analysisJson($arr[$k]);
                }
            }
        }
    }

    /**
     * 美化json数据，空格填充
     *
     * @param mixed $json
     * @return string
     */
    public static function prettyJson($json)
    {
        if (is_string($json)) {
            $json = json_decode($json, true);
        }
        return json_encode(
            $json,
            JSON_PRETTY_PRINT //用空白字符格式化返回的数据
            | JSON_UNESCAPED_UNICODE  //以字面编码多字节 Unicode 字符（默认是编码成 \uXXXX）
            | JSON_UNESCAPED_SLASHES  //不要编码 /
        );
    }

    /**
     * json美化并取出一段
     * @param  string $json        json字符串
     * @param  int    $offset      开始位置
     * @param  [int   $length        = null]   截取长度
     * @param  [bool  $needLineNum = false]  是否需要显示行号
     * @return string 美化后的json字符串
     */
    public static function subJson(string $json, int $offset, int $length = null, bool $needLineNum = false)
    {
        $json = prettyJson($json);
        $arr = explode("\n", $json);
        $totalLine = count($arr);
        $newArr = array_slice($arr, $offset, $length, true);
        if (empty($newArr)) {
            return '';
        }
        $totalNewLine = count($newArr);
        if ($totalLine != $totalNewLine) {
            if (($firstKey = array_key_first($newArr)) != 0) {
                $newArr[$firstKey-1] = '...';
                ksort($newArr);
            }
            if (($lastKey = array_key_last($newArr)) != $totalLine - 1) {
                $newArr[$lastKey+1] = '...';
                ksort($newArr);
            }
        }
        if ($needLineNum) {
            $format = '%' . strlen($totalLine) . 'd  ';
            array_walk($newArr, function (&$raw, $line) use ($format) {
                $raw = sprintf($format, $line + 1) . $raw;
            });
        }
        return implode("\n", $newArr);
    }
}