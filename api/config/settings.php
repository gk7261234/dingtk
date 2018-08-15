<?php
return [
    //组件设置
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
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
                'host' => 'xxx',
                'username' => 'xxxx',
                'password' => 'xxx',
                'port' => '25',
                'from' => ['xxx'=>'vc265'],
            ]
        ] ,
    ],
    //钉钉微应用 项目审核配置
    'ddTalk'=>[
        'agentId' => 'xx',
        'corpid' => 'xxx',
        'corpsecret' => 'xxx',
        'singnatureUrl' => 'http://192.168.1.56:8080/',
        'singnatureNoncestr' => 'xxx',
        'token_url' => 'https://oapi.dingtalk.com/gettoken',
        'ticket_url' => 'https://oapi.dingtalk.com/get_jsapi_ticket',
        'user_info_url' => 'https://oapi.dingtalk.com/user/getuserinfo',
        'permitUser' => [
            "郑x熹",
            "郑达x",
            "王x惠",
            "郭x奎",
            "冯x泰"
        ]
    ],
    //staff_id => name
    'allHands' => [

    ],
    //钉钉审批流 审批参与人
    'ddAuditPerson' => [

    ],
    //数据库配置
    'dbSetting' => [
        'host' => '192.168.1.22',
        'userName' => 'root',
        'password' => 'root',
        'dbName' => 'vc265'
    ],
    //全局变量配置 q系统地址
    'params' => [
        'q-api' => 'http://www.q2018.com', //test
//        'q-api' => 'http://180.76.145.64:3003', //uat
    ]
];
