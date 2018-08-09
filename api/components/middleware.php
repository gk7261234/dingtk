<?php
// Application middleware

// return BEFORE{"status":200,"error":"","data":"\u90ed\u594e"}AFTER
//$mid = function ($req, $res, $next) {
//    $res->getBody()->write('BEFORE');
//    $response = $next($req, $res);
//    $res->getBody()->write('AFTER');
//    return $response;
//};
//钉钉微应用用户识别
$authUser = function ($req, $response, $next){
    $this->logger->info($_SESSION['userName']);
    if (isset($_SESSION['userName']) && in_array($_SESSION['userName'],$this->get('ddTalk')['permitUser'])){
        $response = $next($req, $response);
    }else{
        $response = $response->withStatus(500)->withHeader('Content-type', 'application/json');
        $response->getBody()->write(json_encode(
            [
                'result' => 'N',
                'error' => '该用户没有访问权限',
            ]
        ));
    }
    return $response;
};

//钉钉审批权限控制
$userPower = function ($req,$res,$next){
    
};