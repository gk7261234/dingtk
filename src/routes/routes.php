<?php

use Slim\Http\Request;
use Slim\Http\Response;

require __DIR__ . '/../validate/register.php';
require __DIR__ . '/../components/middleware.php';
require __DIR__ . '/../middleware/project/FProjectRequest.php';

$container = $app->getContainer();

//测试 csrf
$app->get('/q/test',function ($request, $response, $args) {
    $nameKey = $this->csrf->getTokenNameKey();
    $valueKey = $this->csrf->getTokenValueKey();
    $name = $request->getAttribute($nameKey);
    $value = $request->getAttribute($valueKey);

    $tokenArray = [
        $nameKey => $name,
        $valueKey => $value
    ];
    $this->logger->info($tokenArray);
//     $response->write(json_encode($tokenArray));
    return $this->renderer->render($response, 'index.phtml', $tokenArray);
})->add($container->get('csrf'));

$app->post('/q/test',function ($req, $res, $args){
    $this->logger->info($req->getAttribute('csrf_status'));
})->add($container->get('csrf'));



// 用户查询
$app->get('/q/query/{userId}', function (Request $request, Response $response, array $args) use($app) {
    try {
        $db = new db();
        //test 查询
        $this->logger->info($args['userId']);
//        $query = $db->queryAllValue("select id,login_name,`name` from users WHERE  id = :id", [":id" => $args['userId']]);
//        $query = $db->findOne('users', $args['userId']);  
        $query = $db->querySingleSqlValue('users',$args['userId'],'name');
        $this->logger->info($request->getBody()->auditStatus);
        $this->logger->info($query);
        if ($query) {
            $response = $response->withStatus(200)->withHeader('Content-type', 'application/json');
            $response->getBody()->write(json_encode(
                [
                    'status' => 200,
                    'error' => '',
                    'data' => $query
                ]
            ));
        } else {
            $response = $response->withStatus(404)->withHeader('Content-type', 'application/json');
            $response->getBody()->write(json_encode(
                [
                    'status' => 404,
                    'error' => '',
                    'data' => $query
                ]
            ));
        }
        return $response;
//        return $response->withJson($query);
    } catch (PDOException $e) {
        $response = $response->withStatus(500)->withHeader('Content-type', 'application/json');
        $response->getBody()->write(json_encode(
            [
                'status' => 500,
                'error' => $e->getMessage(),
                'data' => ''
            ]
        ));
        return $response;
    }
})->add($audit);


//注册

$app->post('/q/register', function (Request $request, Response $response, array $args) {
    try {
        $db = new db();
        $validate = new Register();
        $params = $request->getParsedBody();
        //验证数据
        $validate_result = $validate->validate($params);
        if (!is_bool($validate_result)) {
            $response = $response->withStatus(404)->withHeader('Content-type', 'application/json');
            $response->getBody()->write(json_encode(
                [
                    'status' => 404,
                    'error' => $validate_result,
                    'result' => 'N'
                ]
            ));
        } else {
            $insert = $db->insert('user', ["name" => ":name", "login_name" => ":login_name", "password" => ":password"], [":name" => $params['name'], ":login_name" => $params['mp_no'], ":password" => $params['psd']]);
            $this->logger->info($insert);
            if ($insert === true) {
                $response = $response->withStatus(200)->withHeader('Content-type', 'application/json');
                $response->getBody()->write(json_encode(
                    [
                        'status' => 200,
                        'error' => '',
                        'result' => 'Y'
                    ]
                ));
            } else {
                $response = $response->withStatus(404)->withHeader('Content-type', 'application/json');
                $response->getBody()->write(json_encode(
                    [
                        'status' => 404,
                        'error' => $insert,
                        'result' => 'N'
                    ]
                ));
            }
        }

    } catch (PDOException $e) {
        $response = $response->withStatus(500)->withHeader('Content-type', 'application/json');
        $response->getBody()->write(json_encode(
            [
                'status' => 500,
                'error' => $e->getMessage(),
                'result' => 'N'
            ]
        ));
        return $response;
    }

});

