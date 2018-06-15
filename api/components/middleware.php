<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

//require __DIR__ . "./mylog.php";

// return BEFORE{"status":200,"error":"","data":"\u90ed\u594e"}AFTER
$mid = function ($req, $res, $next) {
    $res->getBody()->write('BEFORE');
    $response = $next($req, $res);
    $res->getBody()->write('AFTER');
    return $response;
};
