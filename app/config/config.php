<?php
/**
 * @see ../../core/Config/Config.php
 */
return [
    'mysql' => [
        'host' => '172.17.207.161',
        'port' => 3306,
        'user' => 'common',
        'password' => '123456',
        'database' => 'test',
    ],
    'delayLoadDir' => [                             // 延迟加载目录
        [
            'path' => APP_DIR . DS . 'controller',  // 控制器目录
            'prefix' => '',                         // 自定义文件前缀
            'suffix' => '.php',                     // 自定义文件后缀，如： controller.php等，可自动加载 path/prefix[classname]suffix
        ],
        [
            'path' => ROOT_DIR . DS . 'libs',
            'prefix' => '',
            'suffix' => '.php',
        ],
        [
            'path' => APP_DIR . DS . 'response',
            'prefix' => '',
            'suffix' => '.php',
        ],
    ],
];