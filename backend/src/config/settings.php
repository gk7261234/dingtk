<?php
return [
    //组件设置
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
            'error'=> \Monolog\Logger::ERROR,
        ],
    ],
    //数据库配置
    'dbSetting' => [
        'host' => '192.168.1.22',
        'userName' => 'root',
        'password' => 'root',
        'dbName' => 'vc265'
    ],
    //全局变量配置
    'params' => [
        'q-api' => 'http://www.q2018.com/',
    ]
];
