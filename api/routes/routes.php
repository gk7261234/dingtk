<?php

use Slim\Http\Request;
use Slim\Http\Response;
use GuzzleHttp\Client;

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
    try{
        $dBody = $req->getParsedBody();

        $this->logger->info($dBody);
        $this->logger->info($this->get("ddAuditPerson")['p_audit']);
        $this->logger->info($this->get("ddAuditPerson")['p_review']);

        $processInstanceId = $dBody['processInstanceId'];

        $ral = $this->db->queryAllValue("SELECT * FROM fproj_audit WHERE process_code = :process_code ",[":process_code"=>$processInstanceId]);
        $p_id = $ral[0]['project_id'];
        $fpro_request_id = $ral[0]['fpro_request_id'];
        $this->logger->info($fpro_request_id);
        $result = $this->db->queryAllValue("SELECT id,status FROM fproj_requests WHERE id = :f_request_id",[":f_request_id"=>$fpro_request_id]);

        if( $result == [] &&  strpos($dBody['title'],"项目审批") === false  ){
            $this->logger->info("其他审批。。。");
            return;
        }

        $id = $result[0]['id'];
        $status = $result[0]['status'];
        $fp_service = $this->fprService;

        /********************** 重构 start ***************************/
        if( $dBody['type'] == 'start' && $dBody['event'] == 'instance_change' &&  is_null($dBody['result']) ){
            //审批流程发起
            $this->logger->info("这是审批流发起");
        }elseif ( $dBody['type'] == 'finish' && $dBody['result'] == 'agree' && $dBody['event'] == 'task_change' ){
            //中间审批人 用staffId区分审批节点 重复审批人单独处理
            if ($dBody['staffId'] == $this->get("ddAuditPerson")['p_audit']){
                $this->logger->info("项目审核");
                //节点2 项目审核 胡欣杰
                if ( $status == '1' ){
                    $fp_service->review($id,$dBody['staffId'],$dBody['remark']);
                }else{
//                    $this->mailer->send(['1011464909@qq.com'=>'kuhn'],'钉钉审核','项目状态不正确');
                }
            }elseif ($dBody['staffId'] == $this->get("ddAuditPerson")['p_review'] && $status == '4'){
                //节点3 项目复核 王泽惠
                $this->logger->info("项目复核");
                $r = $fp_service->audit($id,$processInstanceId); //return f_id or false
                if ( $r !== false ) {
                    //生成项目 projects
                    $p_service = $this->pService;
                    $r = $p_service->release($id, $r,$processInstanceId);
                }else{
                    $this->logger->info("复核失败");
                }
//                if ( $r !== false ) {
//                    $this->mailer->send(['1011464909@qq.com'=>'kuhn'],'钉钉审核','审核已通过');
//                } else {
//                    $this->mailer->send(['1011464909@qq.com'=>'kuhn'],'钉钉审核','审核失败，项目处理中出错，请及时人工干预');
//                }
            }
        }elseif ( $dBody['type'] == 'finish' && $dBody['event'] == 'instance_change' && $dBody['result'] == "agree" ){
            //审批结束 放款审核 注意观察放款结果
            $this->logger->info("放款审核");
            $client = new Client();
            $response = $client->request('post',$this->get('params')["q-api"].'/projects/investment/project_loan',[
                'form_params' => [
                    'p_id' => $p_id
                ]
            ]);

            //获取请求结果
            $body = $response->getBody();

            if ($body->result == 'Y'){
                $this->logger->info("放款成功");
            }else{
                $this->logger->info("放款放款失败");
            }
        }elseif ($dBody['type'] == 'finish' && $dBody['event'] == 'instance_change' && $dBody['result'] == "refuse"){
            //审批拒绝 要判断项目成立前还是成立后
            if (is_null($p_id) || $p_id == ''){
                //项目未审核通过之前被拒绝
                $fp_service->rollBack($id,$dBody['staffId'],$dBody['remark']);
//                $this->mailer->send(['1011464909@qq.com'=>'kuhn'],'钉钉审核','审核已拒绝');
            }else{
                //项目已成立时被拒绝  处理方案暂未定
                $this->logger->info("项目已成立时被拒绝");
            }
        }else{
            //其他。。。。。 （如： 转交 。。。 以后再说）
            $this->logger->info("其他状况（是允许出现的）");
        }

    }catch (Exception $e){
        $this->logger->info("服务器内部错误");
        $this->logger->info($e->getMessage());
    }
});


//钉钉审核详情页
$app->get('/ajax/ddAuth/{id}/details',function (Request $req, Response $response, array $args){
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
            return $this->renderer->render($response,'index.phtml',['result'=>[]]);
        }
    } catch (Exception $e) {
        $this->logger->info($e->getMessage());
        return $this->renderer->render($response,'index.phtml',['result'=>[]]);
    }
});