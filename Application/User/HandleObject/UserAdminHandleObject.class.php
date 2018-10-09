<?php
namespace User\HandleObject;
use Admin\HandleObject\BaseHandleObject;
/**
 * AdminHandleObject
 * 管理员操作对象
 */
class UserAdminHandleObject extends BaseHandleObject{

    public function createUser(){
        common_plus_trans_origin(__METHOD__);

        $userModel  = D('User/User');
        $thisConfig = D("User/User",'ModelSafety')->getConfigData("createUser");
        if(!$thisConfig){
            return array('error'=>1,'info'=>"在ModelSafety中未发现createUser方法");
        }
        // 开始事务
        common_plus_start_trans(__METHOD__,$userModel);

        if (!$userModel->field($thisConfig['field'])->create($_POST,$thisConfig['key'])){
            return array("error"=>1,"info"=>$userModel->getError());
        }
        $res_user = $userModel->add();

        $res_company = D('Company/Company','Service')->reg_common_companay($res_user,"company_user_id_".$res_user);
        $res_store   = D('Store/Store','Service')->reg_store($res_user);
        $res_finance = D('Finance/FinanceUser','Service')->initFinanceUser($res_user);

        if($res_company['error'] == 0 && $res_company['info'] != '' &&
           $res_store['error'] == 0 && $res_store['info'] != '' && 
           $res_finance['error'] == 0 && $res_finance['info'] != '' && 
           $res_user > 0 ){
            common_plus_commit_trans(__METHOD__,$userModel);
             return array("error"=>0,"info"=>"添加".$thisConfig['name']."成功","id"=>$res);
        }else{
            common_plus_rollback_trans(__METHOD__,$userModel);
            return array('error'=>1,'info'=>"添加".$thisConfig['name']."失败".$res_company['info'].$res_store['info'].$res_finance['info']);
        }
    }


}