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

        //mailer
        'mailer' => [
            'transport' => [
                'host' => 'smtp.vc265.com',
                'username' => 'guokui@vc265.com',
                'password' => '!234qwer',
                'port' => '25',
                'from' => ['guokui@vc265.com'=>'vc265'],
            ]
        ] ,
    ],
    //钉钉微应用 项目审核配置
    'ddTalk'=>[
        'agentId' => '171995680',
        'corpid' => 'ding2c238b123d0f70fe',
        'corpsecret' => 'WSglHDjrPbTAaEEImMNOZnLYsMTFTqhFHGtOjNp7Rkj2igv5plK51OJ1mW2DfXul',
        'singnatureUrl' => 'http://192.168.1.56:8080/',
        'singnatureNoncestr' => 'jR93Gk5jc0mFgf76',
        'token_url' => 'https://oapi.dingtalk.com/gettoken',
        'ticket_url' => 'https://oapi.dingtalk.com/get_jsapi_ticket',
        'user_info_url' => 'https://oapi.dingtalk.com/user/getuserinfo',
        'permitUser' => [
            "郑熹",
            "郑达希",
            "王泽惠",
            "郭奎",
            "冯武泰"
        ]
    ],
    //钉钉审批流 审批参与人
    'ddAuditPerson' => [
        "03344943296605" => "郭奎",
        "04344536073039" => "彭修文",
        "08304513621212774" => "陈亮",
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
