<?php
use GuzzleHttp\Client;

class FProjectRequestService {
    private $app;
    public function __construct($app)
    {
        $this->app = $app;
    }

    private function logInfo ($str) {
        $dataType = gettype($str);
        if($dataType=='array'){
            $str = json_encode($str);
        }
        $file  = __DIR__ . '\..\..\logs\app.log';
        $time = (string)date("Y-m-d H:i:s");
        file_put_contents($file, "[".$time."] [内部打印]:"." dataType ".$dataType." ".$str."\n", FILE_APPEND);
    }

    public function audit($id,$process_code) {
        $db = $this->app->db;
        $db->begin();
        try {
            //获取复核项目信息
            $req = $db->findOne("fproj_requests",$id);

            //验证项目状态 4 ： 待复核
            //注：钉钉审批没有中间状态 需注释掉 两种方式 确定后在处理
//            if ($req['status'] != 4) {
//                return false;
//            }

            //更新fproj_requests项目状态
            $r = $db->update('fproj_requests',['status'=>10,'updated_at'=>date("Y-m-d H:i:s"),'auditor_id'=>19 ],["id"=>$id]);
            if (!$r){
                $db->rollback();
                return false;
            }

            //添加操作记录
            $l = $db->insert('fproj_requests_logs',[
                'req_id'=>$id,
                'op_type'=>3,
                'operator_id'=>19,  //ceo => 19
//                'operator_name'=>$_SESSION['userName'],
                'operator_name'=>"王泽惠",
                'memo'=>"钉钉审核",
                'created_at'=>date("Y-m-d H:i:s"),
            ]);
            
            if ($l === false){
                $db->rollback();
                return false;
            }
            $req['pro_type'] = 10;
            $req['industry'] = 100;
            $req['status'] = 10;
            //添加原始项目  返回该条目的id
            $f_id = $db->insert('f_projects',$req);
            if ($f_id === false){
                $db->rollback();
                return false;
            }

            $this->logInfo("zhunbei f_project_id更新通过");
            $s = $db->update('fproj_audit',['f_project_id'=>$f_id],["process_code"=>$process_code]);
            if (!$s){
                $this->logInfo("关联表f_project_id更新失败");
                $db->rollback();
                return false;
            }
            $this->logInfo("关联表f_project_id更新通过");
            //复核后添加 f_project_id
            $p = $db->update('fproj_requests',['f_project_id'=>$f_id],["id"=>$id]);
            if(!$p){
                $db->rollback();
                return false;
            }

            $login_name = $db->querySingleSqlValue('users',$req['user_id'],'login_name');
            $req['f_project_id'] = $f_id;
            $req['name'] = $req['user_name'];
            $req['login_name'] = $login_name;
            //拆分到others表中
            $o = $db->insert('project_other_info',$req);
            if ($o === false){
                $db->rollback();
                return false;
            }
            //添加项目图片 以原始项目id为父级目录
            $client = new Client();
            if ($req['p_img']){
                $pArr = explode(",",$req['p_img']);
                if ($pArr){
                    foreach ($pArr as $img){
                        $p_img = $db->insert('f_project_contract_imgs',['f_project_id'=>(int)$f_id,'img'=>$img,'img_type'=>1]);
                        if ($p_img>0){
                            $response = $client->request('post','http://www.q2018.com/projects/fproject/fprojrequest/project_img_handle',[
                                'form_params' => [
                                    'f_id' => $f_id,
                                    'img' => $img,
                                ]
                            ]);
                            $this->logInfo($response->getHeaders());
                        }else{
                            return false;
                        }
                    }
                }
            }

            //添加合同图片   以预备项目id为父及目录
            if($req['p_contract_img']){
                $contractArr = explode(",",$req['p_contract_img']);
                if ($contractArr){
                    foreach ($contractArr as $cimg){
                        $fr_id = $req['id'];
                        $p_img = $db->insert('f_project_contract_imgs',['f_project_id'=>$f_id,'img'=>$fr_id."/".$cimg,'img_type'=>2]);
                        if ($p_img>0){
                            $response = $client->request('post','http://www.q2018.com/projects/fproject/fprojrequest/contract_img_handle',[
                                'form_params' => [
                                    'fr_id' => $fr_id,
                                    'img' => $cimg,
                                ]
                            ]);
                            $this->logInfo($response->getHeaders());
                            $this->logInfo($response->getBody());
                            $this->logInfo("添加合同图片ok");
                        }else{
                            $this->logInfo("添加合同图片false");
                            return false;
                        }
                    }
                }
            }

            $db->commit();
            return $f_id;

        }catch(PDOException $e){
            $db->rollback();
            $this->logInfo($e->getMessage());
            return false;
        }
    }

    public function rollBack($id,$staffId,$remark){
        $db = $this->app->db;
        $db->begin();
        try{
            $r = $db->update('fproj_requests',['status'=>2,'updated_at'=>date("Y-m-d H:i:s"),'auditor_id'=>19 ],["id"=>$id]);
            $this->app->logger->info($r);
            $l = $db->insert('fproj_requests_logs',[
                'req_id'=>$id,
                'op_type'=>2,
                'operator_id'=>2,  //审批人暂未确定
                'operator_name'=>$staffId,   //钉钉员工唯一标识
                'memo'=>"钉钉审批拒绝",
                'created_at'=>date("Y-m-d H:i:s"),
            ]);
            if ($l){
                $db->commit();
                return true;
            }else{
                $db->rollback();
                return false;
            }
        }catch(PDOException $e){
            $db->rollback();
            $this->logInfo($e->getMessage());
            return false;
        }

    }

    public function review($id,$staffId,$remark){
        $this->logInfo("review");
        $db = $this->app->db;
        $db->begin();
        try{
            $r = $db->update('fproj_requests',['status'=>4,'updated_at'=>date("Y-m-d H:i:s"),'auditor_id'=>19 ],["id"=>$id]);
            if (!$r){
                $this->app->mailer->send(['1011464909@qq.com'=>'kuhn'],'钉钉项目审核 复核','项目更新状态失败');
            }
            $l = $db->insert('fproj_requests_logs',[
                'req_id'=>$id,
                'op_type'=>4,
                'operator_id'=>2,  //审批人暂未确定
                'operator_name'=>$staffId,   //钉钉员工唯一标识
                'memo'=>"钉钉审批复核=>".$remark,
                'created_at'=>date("Y-m-d H:i:s"),
            ]);
            if ($l){
                $db->commit();
                return true;
            }else{
                $db->rollback();
                return false;
            }
        }catch(PDOException $e){
            $db->rollback();
            $this->logInfo($e->getMessage());
            return false;
        }
    }


}
