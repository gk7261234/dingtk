<?php

use Slim\Http\Request;
use Slim\Http\Response;

//钉钉鉴权信息
$app->get('/dAuth',function (Request $request, Response $response, $args){
    try{
        $token_url = $this->get('ddTalk')['token_url'];

        $corpid = $this->get('ddTalk')['corpid'];
        $corpsecret = $this->get('ddTalk')['corpsecret'];
        $singnatureTimestamp = time();
        $singnatureNoncestr = $this->get('ddTalk')['singnatureNoncestr'];
        $url = $this->get('ddTalk')['singnatureUrl'];
        $agentId = $this->get('ddTalk')['agentId'];


        $this->logger->info($singnatureTimestamp);
        $this->logger->info($url);

        //获取token
        $get_token = $this->client->request('get',$token_url.'?corpid='.$corpid.'&corpsecret='.$corpsecret)->getBody();
        $get_token = json_decode((string) $get_token, true);
        $this->logger->info($get_token);
        if ($get_token['errcode'] == '0'){
            $ticket_url = $this->get('ddTalk')['ticket_url'];
            //获取ticket
            $get_ticket = $this->client->request('get',$ticket_url.'?access_token='.$get_token['access_token'])->getBody();
            $get_ticket = json_decode((string) $get_ticket, true);
            $this->logger->info($get_ticket);
            if ($get_token['errcode'] != '0'){
                $response = $response->withStatus(500)->withHeader('Content-type', 'application/json');
                $response->getBody()->write(json_encode(
                    [
                        'error'=>$get_ticket['errmsg']
                    ]
                ));
            }else{
                $str = 'jsapi_ticket='.$get_ticket['ticket'].'&noncestr='.$singnatureNoncestr.'&timestamp='.$singnatureTimestamp.'&url='.$url;
                $signature = sha1($str);
                $this->logger->info($str);
                $response = $response->withStatus(200)->withHeader('Content-type', 'application/json');
                $response->getBody()->write(json_encode(
                    [
                        'signature'=>$signature,
                        'nonceStr'=>$singnatureNoncestr,
                        'timeStamp'=>$singnatureTimestamp,
                        'agentId'=>$agentId,
                        'corpId'=>$corpid,
                    ]
                ));
            }
        }else{
            $response = $response->withStatus(500)->withHeader('Content-type', 'application/json');
            $response->getBody()->write(json_encode(
                [
                    'error'=>$get_token['errmsg']
                ]
            ));
        }
    }catch (\Exception $e){
        $response = $response->withStatus(500)->withHeader('Content-type', 'application/json');
        $response->getBody()->write(json_encode(
            [
                'error' => $e->getMessage()
            ]
        ));
    };
});

//验证用户信息
//$app->post('/authUser',function (){
//
//});
