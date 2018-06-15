<?php
use GuzzleHttp\Client;

class FProjectRequestService {

    private function logInfo ($str) {
        $dataType = gettype($str);
        if($dataType=='array'){
            $str = json_encode($str);
        }
        $file  = __DIR__ . '\..\..\logs\app.log';
        $time = (string)date("Y-m-d H:i:s");
        file_put_contents($file, "[".$time."] [内部打印]:"." dataType ".$dataType." ".$str."\n", FILE_APPEND);
    }

    public function audit($id,$memo='') {
        $db = new db();
        $db->begin();
        try {
            //获取复核项目信息
            $req = $db->findOne("fproj_requests",$id);
            $this->logInfo("first step is ok");
            $this->logInfo($req['status']);

            //验证项目状态 4 ： 待复核
            if ($req['status'] != 4) {
                return false;
            }
            //更新fproj_requests项目状态
            $r = $db->update('fproj_requests',['status'=>10,'updated_at'=>date("Y-m-d H:i:s"),'auditor_id'=>19 ],["id"=>$id]);
            if (!$r){
                $this->logInfo("second step is false");
                $db->rollback();
                return false;
            }
            $this->logInfo("second step is ok");
            //添加操作记录
            $l = $db->insert('fproj_requests_logs',[
                'req_id'=>$id,
                'op_type'=>3,
                'operator_id'=>19,  //ceo => 19
                'operator_name'=>'王泽惠',
                'memo'=>$memo,
                'created_at'=>date("Y-m-d H:i:s"),
            ]);
            $this->logInfo("=========我要下面的结果================================");
            
            if ($l === false){
                $this->logInfo("third step is false");
                $db->rollback();
                return false;
            }
            $this->logInfo("third step is ok");
            $req['pro_type'] = 10;
            $req['industry'] = 100;
            //添加原始项目  返回该条目的id
            $f_id = $db->insert('f_projects',$req);
            $this->logInfo($f_id);
            if ($f_id === false){
                $this->logInfo("four step is false");
                $db->rollback();
                return false;
            }
            $this->logInfo("four step is ok");
            //复核后添加 f_project_id
            $p = $db->update('fproj_requests',['f_project_id'=>$f_id],["id"=>$id]);
            if(!$p){
                $this->logInfo("five step is false");
                $db->rollback();
                return false;
            }
            $this->logInfo("five step is ok");
            //拆分到others表中
//            $o = $this->saveOtherInfo($req,$f_id);

            $this->logInfo($req['user_id']);

            $login_name = $db->querySingleSqlValue('users',$req['user_id'],'login_name');
            $this->logInfo("other info");
            $req['f_project_id'] = $f_id;
            $req['name'] = $req['user_name'];
            $req['login_name'] = $login_name;
            $this->logInfo($req);
            $o = $db->insert('project_other_info',$req);
            $this->logInfo($o);
            if ($o === false){
                $this->logInfo("six step is false");
                $db->rollback();
                return false;
            }
            $this->logInfo("six step is ok");
            //添加项目图片 以原始项目id为父级目录
            $client = new Client();
            if ($req['p_img']){
                $pArr = explode(",",$req['p_img']);
                if ($pArr){
                    foreach ($pArr as $img){
                        $p_img = $db->insert('f_project_contract_imgs',['f_project_id'=>(int)$f_id,'img'=>$img,'img_type'=>1]);
                        if ($p_img>0){
                            $this->logInfo("添加项目图片ok");
                            $response = $client->request('post','http://www.q2018.com/projects/fproject/fprojrequest/project_img_handle',[
                                'form_params' => [
                                    'f_id' => $f_id,
                                    'img' => $img,
                                ]
                            ]);
                            $this->logInfo($response->getHeaders());
                        }else{
                            $this->logInfo("添加项目图片false");
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
            $this->logInfo("end");
            return $f_id;

        }catch(PDOException $e){
            $db->rollback();
            $this->logInfo($e->getMessage());
            return false;
        }
    }
}