<?php

namespace core\Route;


use core\Route\Exceptions\ExceptionInterface;
use core\Route\Exceptions\InvalidMethodException;
use core\Route\Exceptions\InvalidParamException;
use core\Route\Exceptions\RouteNotFoundException;
use core\Util\Reflector;

class Route implements RouteInterface
{
    private static $method;

    private static $path = [];

    public static $map = [];

    public static $staticMap = [];

    //获取请求URI（query参数前面的部分）
    private static function getUriPrefix()
    {
        return '/' . implode('/', self::$path);
    }

    // 解析请求URI
    public static function uriHandle()
    {
        self::$method = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'];
        $requestUri = preg_replace('#/+#','/', $requestUri);
        $isValid = preg_match_all('#/[^?/&]*#', $requestUri, $matches);
        if (!$isValid) {
            throw new RouteNotFoundException(sprintf('Could not positively identify the URI[%s]', $_SERVER['REQUEST_URI']));
        }
        array_walk($matches[0], function (&$i) {
            $i = substr($i, 1);
        });
        self::$path = $matches[0];
    }

    // 注册GET请求路由， 参数设置需要匹配 /\{[a-zA-Z0-9_]*\}/
    public static function get(string $uri, $callback, array $assocArgs = [])
    {
        self::addRoute(['GET'], $uri, $callback, $assocArgs);
    }

    // 注册POST请求路由， 参数设置需要匹配 /\{[a-zA-Z0-9_]*\}/
    public static function post(string $uri, $callback, array $assocArgs = [])
    {
        self::addRoute(['POST'], $uri, $callback, $assocArgs);
    }

    public static function match(array $methods, string $uri,  $callback, array $assocArgs = [])
    {
        self::addRoute($methods, $uri, $callback, $assocArgs);
    }

    private static function addRoute(array $methods, string $uri, $callback, array $assocArgs = [])
    {
        $actionArgs = self::getActionArgs($callback);
        $uri = $uri[0] == '/' ? substr($uri, 1) : $uri;
        $parts = preg_split('#/+#', $uri);
        $uriModel = '/' . $uri;
        $hasParam = preg_match_all('/\{[a-zA-Z0-9_]*\}/', $uriModel, $matches);
        if (!$hasParam) {
            self::$staticMap[$uriModel] = [
                'action' => $callback,
                'methods' => $methods,
                'parts' => $parts,
                'actionArgs' => $actionArgs,
                'arg2param' => $assocArgs,
            ];
            return;
        }

        $param = [];
        array_walk($matches[0], function ($i) use (&$param) {
            $param[] = substr($i, 1, strlen($i) - 2);
        });
        $pattern = '#^' . preg_replace('/\{[a-zA-Z0-9_]*\}/', '([a-zA-Z0-9_]*)', $uriModel) . '$#';
        if (!empty($assocArgs)) {
            self::assertAssocParamIsValid($param, $actionArgs, $assocArgs);
        }
        self::$map[$pattern] = [
            'action' => $callback,
            'methods' => $methods,
            'parts' => $parts,
            'param' => $param,
            'actionArgs' => $actionArgs,
            'arg2param' => $assocArgs,
        ];
    }

    // 请求URI匹配对应路由
    public static function uriMatch()
    {
        self::uriHandle();
        $uri = self::getUriPrefix();

        //匹配静态路由
        if (array_key_exists($uri, self::$staticMap)) {
            self::assertMethodIsValid(self::$staticMap[$uri]['methods']);
            return [
                self::$staticMap[$uri]['action'],
                self::getFunArgsList(self::$staticMap[$uri]['actionArgs'], [], self::$staticMap[$uri]['arg2param']),
                self::$staticMap[$uri]['actionArgs'],
            ];
        }

        $routeInfo = [];
        foreach (self::$map as $pattern => $info) {
            if (preg_match($pattern, $uri)) {
                $param = $info['param'];
                $paramList = [];
                preg_replace_callback($pattern, function ($matches) use (&$paramList, $param) {
                    for ($i = 1; $i < count($matches);$i++) {
                        $paramList[$param[$i-1]] = $matches[$i];
                    }
                }, $uri);

                // 最长规则匹配优先
                if (empty($routeInfo) || strlen($pattern) > strlen($routeInfo['pattern'])) {
                    $routeInfo = [
                        'pattern' => $pattern,
                        'action' => $info['action'],
                        'methods' => $info['methods'],
                        'actionArgs' => $info['actionArgs'],
                        'arg2param' => $info['arg2param'],
                        'paramList' => $paramList,
                    ];
                }

            }
        }

        if (empty($routeInfo)) {
            throw new RouteNotFoundException(sprintf('Route[%s] Could not found', self::getUriPrefix()));
        }

        self::assertMethodIsValid($routeInfo['methods']);

        return [
            $routeInfo['action'],
            self::getFunArgsList($routeInfo['actionArgs'], $routeInfo['paramList'], $routeInfo['arg2param']),
            $routeInfo['actionArgs'],
        ];
    }

    private static function assertMethodIsValid(array $methods)
    {
        $method = strtoupper(self::$method);
        if (!in_array($method, $methods)) {
            throw new InvalidMethodException(sprintf('Request method[%s] is not supported, try method as [%s]', $method, implode('/', $methods)));
        }
    }

    private static function assertAssocParamIsValid($param, $actionArgs, $assocArgs)
    {
        $validKeys = array_filter(array_keys($assocArgs), function ($arg) use ($actionArgs) {
            if (in_array($arg,$actionArgs)) return true;
        });
        if (count($validKeys) != count($assocArgs)) {
            throw new InvalidParamException(sprintf('Function\'s parameter is not supported'));
        }

        $unAssocArgs = array_diff($actionArgs, array_keys($assocArgs));
        if (!empty($unAssocArgs)) {
            $validArgs = array_filter($unAssocArgs, function ($arg) use ($param) {
                if (in_array($arg,$param) || in_array($arg, $_REQUEST)) return true;
            });
            if (count($validArgs) != count($unAssocArgs)) {
                throw new InvalidParamException(sprintf('Undefined function\'s parameter'));
            }
        }
    }

    private static function getFunArgsList($actionArgs, $actionVals, $assocArgs = [])
    {
        $actionVals = array_merge($actionVals, $_REQUEST);
        if (!empty($assocArgs)) {
            foreach ($assocArgs as $k => $v) {
                if (isset($actionVals[$v])) {
                    $actionVals[$k] = $actionVals[$v];
                    // unset($actionVals[$v]);
                } else {
                    throw new InvalidParamException(sprintf('Undefined Param[%s]', $v));
                }

            }
        }
        $paramList = [];
        foreach ($actionArgs as $arg) {
            $paramList[] = $actionVals[$arg];
        }
        return $paramList;
    }

    private static function getActionArgs($callback)
    {
        if ($callback instanceof \Closure) {
            return Reflector::getFunArgsName($callback);
        } else {
            [$class, $method] = explode('@', $callback, 2);
            return Reflector::getClassMethodArgsName($class, $method);
        }
    }

}