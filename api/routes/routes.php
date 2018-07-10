<?php

use Slim\Http\Request;
use Slim\Http\Response;

//require __DIR__ . '/../service/FProjectRequestService.php';
//require __DIR__ . '/../service/ProjectService.php';
//require_once ("lib/swift_required.php");

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
$app->post('/dAuthUser',function (Request $req, Response $response, array $arg){
    $userName = $req->getParam('userName');
    if (in_array($userName,$this->get('ddTalk')['permitUser'])){
        $_SESSION['userName'] = $userName;
        $response = $response->withStatus(200)->withHeader('Content-type', 'application/json');
        $response->getBody()->write(json_encode(
            [
                'result'=>'Y'
            ]
        ));
    }else{
        $response = $response->withStatus(500)->withHeader('Content-type', 'application/json');
        $response->getBody()->write(json_encode(
            [
                'result'=>'N',
                'error' => '该用户没有访问权限',
            ]
        ));
    }
    
});

//钉钉审批回调  service 需传入$app实例
$app->post('/dAuthCallBack',function (Request $req, Response $response, array $arg){

    $dBody = $req->getParsedBody();

    $this->logger->info($dBody);
    $this->logger->info($this->get('ddAuditPerson')[$dBody['staffId']]);

    $result = $this->db->queryAllValue("SELECT id,status FROM fproj_requests WHERE process_code = :process_code",[":process_code"=>$dBody['processInstanceId']]);

    if( $result == [] &&  strpos($dBody['title'],"项目审批") === false  ){
        $this->logger->info("其他审批。。。");
        return;
    }

    $fp_service = $this->fprService;
    $id = $result[0]['id'];
    
    if ( $dBody['type'] == 'finish' && $dBody['result'] == 'agree' ){
        try{
            $r = $fp_service->audit($id); //return f_id or false
            if ( $r !== false ) {
                //生成项目 projects
                $p_service = $this->pService;
                $r = $p_service->release($id, $r);
            }
            if ( $r !== false ) {
                $this->mailer->send(['1011464909@qq.com'=>'kuhn'],'钉钉审核','审核已通过');
            } else {
                $this->mailer->send(['1011464909@qq.com'=>'kuhn'],'钉钉审核','审核失败，项目处理中出错，请及时人工干预');
            }
        }catch (Exception $e){
            $this->logger->info($e->getMessage());
            $this->mailer->send(['1011464909@qq.com'=>'kuhn'],'钉钉审核','内部错误 500 ');
        }
    }else if ( $dBody['type'] == 'finish' && $dBody['result'] == 'refuse' ){
        $fp_service->rollBack($id,$dBody['processInstanceId'],$dBody['staffId'],$dBody['remark']);
        $this->mailer->send(['1011464909@qq.com'=>'kuhn'],'钉钉审核','审核已拒绝');
    } else if ( $dBody['type'] == 'finish' && $dBody['event'] == 'task_change' && $dBody['remark'] != null ) {
        // 中间人审批 需用指定固定用户id区别
        if ($dBody['staffId'] == '03344943296605'){
            $status = $result[0]['status'];
            $this->logger->info($status);
            if ( $status == '1' ){
                $fp_service->review($id,$dBody['processInstanceId'],$dBody['staffId'],$dBody['remark']);
            }else{
                $this->mailer->send(['1011464909@qq.com'=>'kuhn'],'钉钉审核','项目状态不正确');
            }
        }
    }
});
