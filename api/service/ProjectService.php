<?php
class ProjectService{

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
    public function release($id,$f_id){
        $db = $this->app->db;
        $db->begin();
        try{
            $fproject = $db->findOne('f_projects',$f_id);
            $fr_project = $db->findOne('fproj_requests',$id);

            $data = [
                "f_project_id"=>$f_id,
                "amount"=>$fproject['amount'],
                "rcv_amount"=>0,
                "ser_no"=>1,
                "tender_count"=>0,
                "created_at"=>date("Y-m-d H:i:s"),
                "updated_at"=>date("Y-m-d H:i:s"),
                "status"=>0,
                "over_rate"=>$fproject['over_fee_rate'],
                "interest_rate"=>$fproject['fnc_ret'],
                "debts_months"=>$fproject['deadline'],
                "f_project_name"=>$fproject['f_project_name'],
                "user_id"=>$fproject['user_id'],
                "credit_type"=>$fproject['credit_type'],
                "service_fee_rate"=>$fproject['service_fee_rate'],
                "service_fee"=>0,
                "pro_type"=>$fproject['pro_type'],
                "debts_type"=>$fproject['debts_type'],
                "ver"=>2,
                "p_repayment_person"=>$fr_project['p_repayment_person'],
                "p_debtor_person"=>$fr_project['p_debtor_person']
            ];
            $p_id = $db->insert('projects',$data);
            $this->logInfo("下面是项目id");
            $this->logInfo($p_id);
            if($p_id>0){
                $db->commit();
                return $p_id;
            }else{
                $db->rollback();
                return false;
            }
        }catch (PDOException $e){
            $db->rollback();
            return false;
        }
    }
}