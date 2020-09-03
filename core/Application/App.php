<?php

namespace core\Application;

use core\Http\Response\JsonResponse;
use core\Http\Response\ResponseInterface;
use core\Route\Route;
use core\Util\Reflector;
use core\Util\File;
use core\Config\Env\Env;
use core\Log\Log;
use core\Util\Str;

class App implements ApplicationInterface
{
    public static function run()
    {
        // 检查并初始化启动配置
        self::init();
        // 定义项目各文件夹路径
        self::defineDir();
        // 加载配置文件
        self::loadConfigFile();
        // 初始化基础配置
        self::initEnv();
        // 加载启动加载文件
        self::loadDir();
        // 处理当前请求
        $ret = self::handle();
        // 发送返回值
        self::send($ret);
    }

    private static function defineDir()
    {
        define('DS', DIRECTORY_SEPARATOR);
        define('ROOT_DIR', File::getDirName(__DIR__, 2));
        define('APP_DIR', ROOT_DIR . DS . 'app');
        define('CORE_DIR', ROOT_DIR . DS . 'core');
        define('CORE_CONFIG_DIR', CORE_DIR . DS . 'Config');
    }

    private static function loadConfigFile()
    {
        $config = require_once CORE_CONFIG_DIR . DS . 'Config.php';
        Env::set($config);

        if (Env::get('configDir')) {
            foreach (Env::get('configDir') as $configDir) {
                foreach (File::loadDir($configDir) as $configPath) {
                    $config = require_once $configPath;
                    Env::set($config);
                }
            }
        }

    }

    private static function loadDir()
    {
        if (Env::get('initLoadDir')) {
            foreach (Env::get('initLoadDir') as $dir) {
                foreach (File::loadDir($dir) as $path) {
                    require_once $path;
                }
            }
        }
    }

    private static function init()
    {
        error_reporting(E_ALL | E_STRICT);
        set_exception_handler(function (\Throwable $ex) {
//            (new Json2Response(
//                $ex->getMessage(),
//                500
//            ))->render();
//            print_r($ex);
            (new JsonResponse(
                $ex->getMessage() . PHP_EOL .
                $ex->getTraceAsString(),
                500
            ))->render();
        });
        register_shutdown_function(function () {
            $error = error_get_last();
//            print_r($error);
            if ($error !== null) {
                (new JsonResponse(
                    $error['message'] . PHP_EOL . $error['file'],
                    500
                ))->render();
            }
        });

    }

    private static function initEnv()
    {
        error_reporting(E_ALL | E_STRICT);
        date_default_timezone_set(Env::get('timezone'));
        spl_autoload_register(function ($class) {
            if (Env::get('delayLoadDir')) {
                foreach (Env::get('delayLoadDir') as $controllerDir) {
                    $path = $controllerDir['path'];
                    $prefix = $controllerDir['prefix'];
                    $suffix = $controllerDir['suffix'];
                    $loadDirs = array_merge([$path], File::loadDir($path, File::LOAD_DIR));
                    foreach ($loadDirs as $dir) {
                        $classPath = $dir . DS . $prefix . $class . $suffix;
                        if (is_file($classPath)) {
                            require_once $classPath;
                        }
                    }
                }
            }
        }, true, true);
        Reflector::setPropertyValue(Log::class, 'dir', Env::get('logDir'));
        Reflector::setPropertyValue(Log::class, 'fileSize', Env::get('logFileSize'));
        set_exception_handler(function (\Throwable $ex) {
            Log::error(sprintf("[ %s ] \n%s", $ex->getMessage(), $ex->getTraceAsString()));
            (new JsonResponse(
                $ex->getMessage(),
                500
            ))->render();
        });
        register_shutdown_function(function () {
            $error = error_get_last();
            if ($error !== null) {
                Log::error(Str::prettyJson($error));
            }
        });
    }

    private static function handle()
    {
        [$action, $paramList, $actionArgs] = Route::uriMatch();
        if ($action instanceof \Closure) {
            $ret = empty($paramList) ? call_user_func($action) : call_user_func($action, ...$paramList);
        } else if(is_string($action)) {
            [$class, $function] = explode('@', $action, 2);
            $paramArr = [];
            for ($i = 0; $i < count($actionArgs);$i++) {
                $paramArr[$actionArgs[$i]] = $paramList[$i];
            }
            $ret = Reflector::call($class, $function, $paramArr);
        } else {
            throw new \RuntimeException(sprintf('Unable to process at present.[%s]', $action));
        }

        return $ret;
    }

    private static function send($ret)
    {
        if ($ret instanceof ResponseInterface) {
            $ret->render();
        } else {
            (new JsonResponse($ret))->render();
        }
    }
}