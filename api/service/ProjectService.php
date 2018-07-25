<?php
class ProjectService{

    private $app;
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    public function release($id,$f_id,$process_code){
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
                "f_project_name"=>$fproject['name'],
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
            if($p_id>0){
                $s = $db->update('fproj_audit',['project_id'=>$p_id],["process_code"=>$process_code]);
                if ($s){
                    $db->commit();
                    return $p_id;
                }else{
                    $db->rollback();
                    return false;
                }
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