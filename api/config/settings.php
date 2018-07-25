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
        "p_create" => "183312123030024660",  //项目发起人： 盛忠宙 183312123030024660  节点1
        "operate1" => "03344943296605", //审核校验： 李秀清  03343537539687
        "p_audit" => "03344943296605",   //项目审核： 胡欣杰  144106636032582990  节点2
        "p_review" => "03344943296605",  //项目复核： 王泽惠  03331152285524  节点3
        "operate2" => "03344943296605",  //上架提醒： 王梅
        "operate3" => "03344943296605",  //上架提醒： 是蔚瑩  143861100126211227
        "operate4" => "03344943296605",  //提醒确认服务费： 张佳伟 103731640824053036
        "p_loan" => "03344943296605",  //放款审核： 王泽惠  03331152285524  节点4
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
//        'q-api' => 'http://www.q2018.com', //test
        'q-api' => 'http://180.76.145.64:3003', //uat
    ]
];
