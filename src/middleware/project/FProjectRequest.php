<?php
/**
 * Created by PhpStorm.
 * User: GK
 * Date: 2018/6/6
 * Time: 17:26
 */
$audit = function ($request, $response, $next) {
    //获取路由信息
    $router = $request->getAttributes('router');
    $this->logger->info($router);
    $db = $this->db;
    $db->begin();
    try{
        $fr_id = $request->getParam('id');
        //获取复核项目信息
        $req = $db->findOne("fproj_requests", $fr_id);
        //验证项目状态 4 ： 待复核
        if ($req['status'] != 4) {
            $request->getBody()->auditStatus = false;
            $response = $next($request,$response);
            return $response;
        }
        //更新fproj_requests项目状态
        $r = $db->update('fproj_requests', ['status' => 10, 'updated_at' => date("Y-m-d H:i:s"), 'auditor_id' => 19], ["id" => $fr_id]);
        if (!$r) {
            $db->rollback();
            $request->getBody()->auditStatus = false;
            $response = $next($request,$response);
            return $response;
        }
        //添加操作记录
        $l = $db->insert('fproj_requests_logs', [
            'req_id' => $fr_id,
            'op_type' => 3,
            'operator_id' => 19,  //ceo => 19
            'operator_name' => '王泽惠',
            'memo' => '钉钉复核',
            'created_at' => date("Y-m-d H:i:s"),
        ]);

        if ($l === false) {
            $db->rollback();
            $request->getBody()->auditStatus = false;
            $response = $next($request,$response);
            return $response;
        }

        $req['pro_type'] = 10;
        $req['industry'] = 100;
        //添加原始项目  返回该条目的id
        $f_id = $db->insert('f_projects', $req);
        if ($f_id === false) {
            $db->rollback();
            $request->getBody()->auditStatus = false;
            $response = $next($request,$response);
            return $response;
        }

        //复核后添加 f_project_id
        $p = $db->update('fproj_requests', ['f_project_id' => $f_id], ["id" => $fr_id]);
        if (!$p) {
            $db->rollback();
            $request->getBody()->auditStatus = false;
            $response = $next($request,$response);
            return $response;
        }

        $login_name = $db->querySingleSqlValue('users', $req['user_id'], 'login_name');
        $req['f_project_id'] = $f_id;
        $req['name'] = $req['user_name'];
        $req['login_name'] = $login_name;
        $o = $db->insert('project_other_info', $req);
        if ($o === false) {
            $db->rollback();
            $request->getBody()->auditStatus = false;
            $response = $next($request,$response);
            return $response;
        }

        $q_api = $this->get('params')['q-api'];

        //添加项目图片 以原始项目id为父级目录
        if ($req['p_img']) {
            $pArr = explode(",", $req['p_img']);
            if ($pArr) {
                foreach ($pArr as $img) {
                    $p_img = $db->insert('f_project_contract_imgs', ['f_project_id' => (int)$f_id, 'img' => $img, 'img_type' => 1]);
                    if ($p_img > 0) {
                        $response = $this->client->request('post', $q_api . 'projects/fproject/fprojrequest/project_img_handle', [
                            'form_params' => [
                                'f_id' => $f_id,
                                'img' => $img,
                            ]
                        ]);
                        $this->logger->info($response->getHeaders());
                        $this->logger->info($response->getBody());
                    } else {
                        $request->getBody()->auditStatus = false;
                        $response = $next($request,$response);
                        return $response;
                    }
                }
            }
        }

        //添加合同图片   以预备项目id为父及目录
        if ($req['p_contract_img']) {
            $contractArr = explode(",", $req['p_contract_img']);
            if ($contractArr) {
                foreach ($contractArr as $cimg) {
                    $fr_id = $req['id'];
                    $p_img = $db->insert('f_project_contract_imgs', ['f_project_id' => $f_id, 'img' => $fr_id . "/" . $cimg, 'img_type' => 2]);
                    if ($p_img > 0) {
                        $this->client->request('post', $q_api . 'projects/fproject/fprojrequest/contract_img_handle', [
                            'form_params' => [
                                'fr_id' => $fr_id,
                                'img' => $cimg,
                            ]
                        ]);
                    } else {
                        $request->getBody()->auditStatus = false;
                        $response = $next($request,$response);
                        return $response;
                    }
                }
            }
        }

        $db->commit();
        $request->getBody()->f_id = $f_id;
        $request->getBody()->auditStatus = true;
        $response = $next($request,$response);
        return $response;
    }catch(\Exception $e){
        $db->rollback();
        $this->logger->info($e->getMessage());
        $request->getBody()->auditStatus = false;
        $response = $next($request,$response);
        return $response;
    }
};