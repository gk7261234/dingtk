<?php
/**
 * Created by PhpStorm.
 * User: GK
 * Date: 2018/8/7
 * Time: 12:02
 */
class DDRemarkService {
    
    private $app;
    
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    public function log($staff_id, $process_code,$result,$remark){
        $db = $this->app->db;
        $db->begin();
        try{
            $res = $db->insert("dd_log",[
                "staff_id"=>$staff_id,
                "process_code"=>$process_code,
                "result"=>$result,
                "remark"=>$remark
            ]);
            if ($res){
                $db->commit();
                return true;
            }else{
                $db->rollback();
                return false;
            }
        }catch(Exception $e){
            $db->rollback();
            $this->app->logger->info($e->getMessage());
            return false;
        }
    }
}