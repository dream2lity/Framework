<?php

return [
    'timezone' => 'Asia/Shanghai',
    'initLoadDir' => [                              // 启动加载目录
        APP_DIR . DS . 'route',                     // 路由配置目录
    ],
    'delayLoadDir' => [                             // 延迟加载目录
        [
            'path' => APP_DIR . DS . 'controller',  // 控制器目录
            'prefix' => '',                         // 自定义文件前缀
            'suffix' => '.php',                     // 自定义文件后缀，如： controller.php等，可自动加载 path/prefix[classname]suffix
        ],
    ],
    'configDir' => [                                // 配置文件目录，后面的会覆盖当前配置及之前的配置
        APP_DIR . DS . 'config',
    ],
    'logDir' => APP_DIR . DS . 'log',               // 日志文件存放路径
    'logFileSize' => 1024 * 1024 * 1024,            // 单个日志文件大小 字节
];
