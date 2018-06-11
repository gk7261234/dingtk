<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
//    $dt = new DateTime();
//    $mailHandler = new Monolog\Handler\NativeMailerHandler(
//        'guokui@vc265.com',    //to someone
//        'q-mobile is error',
//        'kuhn@vc265.com',  //from
//        $settings['level'],
//        true,
//        2000
//    );
//    $logger->pushHandler(new Monolog\Handler\FingersCrossedHandler($mailHandler,Monolog\Logger::INFO,0,false));
    return $logger;
};

//session
$container['session'] = function ($c) {
    return new \SlimSession\Helper;
};

//csrf
$container['csrf'] = function ($c) {
    $guard = new \Slim\Csrf\Guard();
    $guard->setFailureCallable(function ($request, $response, $next) {
        $request = $request->withAttribute("csrf_status", false);
        return $next($request, $response);
    });
    return $guard;
};

$container['db'] = function ($c) {
    $db = $c->get('dbSetting');
    return new db($db['host'], $db['userName'], $db['password'], $db['dbName']);
};

$container['client'] = function ($c) {
    return new \GuzzleHttp\Client();
};