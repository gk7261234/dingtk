<?php
/**
 * Created by PhpStorm.
 * User: GK
 * Date: 2018/7/24
 * Time: 10:31
 */
use Slim\Http\Request;
use Slim\Http\Response;
$app->group('/project/loan',function (){
    //检测是否满足放款状态
    $this->get('/{id}/status',function (Request $request, Response $response, array $args){
        try{
            $err = '';
            $result = $this->db->queryAllValue("SELECT l.id,p.debts_type,p.transfer_type,p.interest_amount,u.auto_repay_term,u.auto_repay_amt,u.auth_st,f.pre_frozen,a.bank_balance FROM project_loan_requests l, projects p, user_bank_reg u, f_projects f,account a WHERE l.project_id = p.id and p.transfer_type is NULL and l.project_owner_id=u.id and p.f_project_id=f.id AND l.project_owner_id = a.id AND p.id=:id",[":id"=>$args['id']]);
            $infos = $result[0];
            if (!is_null($infos)){
                if ( $infos['debts_type'] == '20' && $infos['pre_frozen'] == '1' ){
                    $auto_status = substr($infos['auth_st'],1,1);
                    if ( $auto_status != 1 || is_null($infos['auto_repay_amt']) || is_null($infos['auto_repay_term']) ){
                        $err = "借款企业未进行委托授权";
                    }
                    $auto_repay_term = date("Y-m-d",strtotime($infos['auto_repay_term']));
                    $current_time = date("Y-m-d");
                    if ( $current_time > $auto_repay_term ){
                        $err = "借款企业自动还款授权期限已过期";
                    }elseif ( $infos['interest_amount'] > $infos['auto_repay_amt']/100 ){
                        $err = "借款企业自动还款授权额度不足";
                    }elseif ( $infos['interest_amount'] > $infos['bank_balance']){
                        $err = "借款企业账户余额不足无法后续操作";
                    }
                }
            }else{
                $err = "此项目不存在";
            }

            $this->logger->info($err);
            $response->withStatus(200)->withHeader('Content-type', 'application/json');
            if (empty($err)){
                $response->getBody()->write(json_encode(
                    [
                        'error' => ''
                    ]
                ));
            }else{
                $response->getBody()->write(json_encode(
                    [
                        'error' => $err
                    ]
                ));
            }
        }catch(Exception $e){
            $response->withStatus(500)->withHeader('Content-type', 'application/json');
            $response->getBody()->write(json_encode(
                [
                    'error' => $e->getMessage(),
                ]
            ));
        }
    });
});