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
$app->post('/ajax/dAuthCallBack',function (Request $req, Response $response, array $arg){

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
        if ($dBody['staffId'] == '03332210379315'){
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


//钉钉审核详情页
$app->get('/ddAuth/{id}/details',function (Request $req, Response $response, array $args){
    try {
        $query = $this->db->queryAllValue("SELECT *,'fproj_requests' as source FROM fproj_requests where id = :id", [":id" => $args['id']]);
        $this->logger->info($query);
        if (gettype($query) == 'array'&&!empty($query)) {
            $query[0]['status'] = '待复核';
            //还本付息方式
            $r_i_type = ['11'=>'按月等本等息','12'=>'按季等本等息','13'=>'每月付息，到期还本','19'=>'一次性收取利息，到期还本','20'=>'一次性还本付息'];
            if (isset($query[0]['debts_type'])&&!empty($query[0]['debts_type'])&&array_key_exists($query[0]['debts_type'],$r_i_type)){
                $query[0]['debts_type'] = $r_i_type[$query[0]['debts_type']];
            }else{
                $query[0]['debts_type'] = "其他";
            }
            //授信类型
            $p_credit_type = ['0'=>'普通借款','1'=>'循环授信'];
            if (isset($query[0]['p_credit_type'])&&array_key_exists($query[0]['p_credit_type'],$p_credit_type)){
                $query[0]['p_credit_type'] = $p_credit_type[$query[0]['p_credit_type']];
            }else{
                $query[0]['p_credit_type'] = "其他";
            }

            //发生类型
            $occurrence_type = ['0'=>'新发生','1'=>'复贷'];
            if (isset($query[0]['p_occurrence_type'])&&array_key_exists($query[0]['p_occurrence_type'],$occurrence_type)){
                $query[0]['p_occurrence_type'] = $occurrence_type[$query[0]['p_occurrence_type']];
            }else{
                $query[0]['p_occurrence_type'] = "其他";
            }

            //担保措施
            $credit_type = ['10'=>'信用担保','11'=>'第三方保证担保','12'=>'质押担保','13'=>'质押担保及第三方保证担保','14'=>'融资性担保公司担保',];
            if (isset($query[0]['credit_type'])&&!empty($query[0]['credit_type'])&&array_key_exists($query[0]['credit_type'],$credit_type)){
                $query[0]['credit_type'] = $credit_type[$query[0]['credit_type']];
            }else{
                $query[0]['credit_type'] = "其他";
            }

            //企业担保方
            $c_credit_person = json_decode($query[0]['c_credit_person']);
            if ($query[0]['c_credit_status']==2 && !empty($c_credit_person) && $c_credit_person != []){
                $str = '';
                foreach ($c_credit_person as $value){
                    $str = $str.$value->name.',';
                }
                $query[0]['c_credit_person'] = $str;
            }

            //项目图片  
            $query[0]['p_img'] = explode(',',$query[0]['p_img']);
            
            //合同图片
            $query[0]['p_contract_img'] = explode(',',$query[0]['p_contract_img']);

            return $this->renderer->render($response,'index.phtml',['result'=>$query[0]]);
        } else {
            $response = $response->withStatus(404)->withHeader('Content-type', 'application/json');
            $response->getBody()->write(json_encode(
                [
                    'error' => empty($query)?"该项目不符合复核条件":$query,
                ]
            ));
        }
//        return $response;

    } catch (Exception $e) {
        $response = $response->withStatus(500)->withHeader('Content-type', 'application/json');
        $response->getBody()->write(json_encode(
            [
                'error' => $e->getMessage(),
            ]
        ));
        return $response;
    }
});