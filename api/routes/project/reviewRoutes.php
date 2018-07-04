<?php

use Slim\Http\Request;
use Slim\Http\Response;

//require __DIR__ . '/../../service/FProjectRequestService.php';
//require __DIR__ . '/../../service/ProjectService.php';
require __DIR__ . '/../../middleware/project/FProjectRequest.php';
require __DIR__ . '/../../components/middleware.php';

$app->group('/project/review', function () {
    // 项目复核列表
    $this->get('/list', function (Request $request, Response $response, array $args) {
        try {
            $query = $this->db->queryAllValue("SELECT id, `name`, amount, deadline, fnc_ret, `status`, operator_id, operator_name, created_at FROM fproj_requests where status = 4");
            if (gettype($query) == 'array') {
                foreach ($query as &$val){
                    $val['status'] = '待复核';
                }
                unset($v); //取消引用
                $response = $response->withStatus(200)->withHeader('Content-type', 'application/json');
                $response->getBody()->write(json_encode($query));
            } else {
                $response = $response->withStatus(404)->withHeader('Content-type', 'application/json');
                $response->getBody()->write(json_encode(
                    [
                        'error' => $query
                    ]
                ));
            }
            return $response;
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

    // 项目详情
    $this->get('/details/{id}', function (Request $request, Response $response, array $args) {
        try {
            $query = $this->db->queryAllValue("SELECT *,'fproj_requests' as source FROM fproj_requests where status = 4 and id = :id", [":id" => $args['id']]);
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
                if (isset($query[0]['p_credit_type'])&&!empty($query[0]['p_credit_type'])&&array_key_exists($query[0]['p_credit_type'],$r_i_type)){
                    $query[0]['p_credit_type'] = $p_credit_type[$query[0]['p_credit_type']];
                }else{
                    $query[0]['p_credit_type'] = "其他";
                }

                //发生类型
                $occurrence_type = ['0'=>'新发生','1'=>'复贷'];
                if (isset($query[0]['p_occurrence_type'])&&!empty($query[0]['p_occurrence_type'])&&array_key_exists($query[0]['p_occurrence_type'],$r_i_type)){
                    $query[0]['p_occurrence_type'] = $occurrence_type[$query[0]['p_occurrence_type']];
                }else{
                    $query[0]['p_occurrence_type'] = "其他";
                }

                //担保措施
                $credit_type = ['10'=>'信用担保','11'=>'第三方保证担保','12'=>'质押担保','13'=>'质押担保及第三方保证担保','14'=>'融资性担保公司担保',];
                if (isset($query[0]['credit_type'])&&!empty($query[0]['credit_type'])&&array_key_exists($query[0]['credit_type'],$r_i_type)){
                    $query[0]['credit_type'] = $credit_type[$query[0]['credit_type']];
                }else{
                    $query[0]['credit_type'] = "其他";
                }


                $response = $response->withStatus(200)->withHeader('Content-type', 'application/json');
                $response->getBody()->write(json_encode($query));
            } else {
                $response = $response->withStatus(404)->withHeader('Content-type', 'application/json');
                $response->getBody()->write(json_encode(
                    [
                        'error' => empty($query)?"该项目不符合复核条件":$query,
                    ]
                ));
            }
            return $response;
//        return $response->withJson($query);
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

    //复核通过
    $this->post('/submit', function (Request $req, Response $response, array $arg) {
        $fr_id = $req->getParam('id');
//        $memo = $req->getParam('memo');
        $memo = "钉钉复核";
//        $amount = $req->getParam('amount');
        try{
            $fp_service = $this->fprService;
            $r = $fp_service->audit($fr_id, $memo); //return f_id or false
            if ($r !== false) {
                //生成项目 projects
                $p_service = $this->pService;
                $r = $p_service->release($fr_id, $r);
            }
            if ($r !== false) {
                $response = $response->withStatus(200)->withHeader('Content-type', 'application/json');
                $response->getBody()->write(json_encode(
                    [
                        'result' => 'Y',
                    ]
                ));
            } else {
                $response = $response->withStatus(404)->withHeader('Content-type', 'application/json');
                $response->getBody()->write(json_encode(
                    [
                        'result' => 'N',
                    ]
                ));
            }
            return $response;
        }catch (Exception $e){
            $response = $response->withStatus(500)->withHeader('Content-type', 'application/json');
            $response->getBody()->write(json_encode(
                [
                    'result' => 'N',
                    'error' => $e->getMessage(),
                ]
            ));
            return $response;
        }
    });

    //复核 退回
    $this->post('/rollback', function (Request $req, Response $response, array $arg) {
        $fr_id = $req->getParam('id');
        $this->logger->info($fr_id);
//        $memo = $req->getParam('memo');
        try {
            $db = $this->db;
            $db->begin();
            $r = $db->update('fproj_requests', ["status" => 2, "updated_at" => date("Y-m-d H:i:s")], ["id" => $fr_id]);
            $this->logger->info($r);
            if ($r === true) {
                $l = $db->insert('fproj_requests_logs',[
                    'req_id'=>$fr_id,
                    'op_type'=>2,
                    'operator_id'=>19,  //ceo => 19
                    'operator_name'=>$_SESSION['userName'],
                    'memo'=>"钉钉退回",
                    'created_at'=>date("Y-m-d H:i:s"),
                ]);
                if ($l===false){
                    $db->rollback();
                    $response = $response->withStatus(404)->withHeader('Content-type', 'application/json');
                    $response->getBody()->write(json_encode(
                        [
                            'result' => 'N',
                            'error' => '操作记录失败'
                        ]
                    ));
                }else{
                    $db->commit();
                    $response = $response->withStatus(200)->withHeader('Content-type', 'application/json');
                    $response->getBody()->write(json_encode(
                        [
                            'result' => 'Y',
                        ]
                    ));
                }
            } else {
                $db->rollback();
                $response = $response->withStatus(404)->withHeader('Content-type', 'application/json');
                $response->getBody()->write(json_encode(
                    [
                        'result' => 'N',
                        'error'=> '状态更新失败'
                    ]
                ));
            }
            return $response;
        } catch (Exception $e) {
            $this->logger->info($e->getMessage());
            $response = $response->withStatus(500)->withHeader('Content-type', 'application/json');
            $response->getBody()->write(json_encode(
                [
                    'result' => 'N',
                    'error' => $e->getMessage(),
                ]
            ));
            return $response;
        }
    });

})->add($authUser);

