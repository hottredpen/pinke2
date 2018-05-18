<?php
namespace Admin\HandleObject;
/**
 * AdminHandleObject
 * 管理员操作对象
 */
class AdminBaseHandleObject extends BaseHandleObject {
    /**
     * 登录
     */
    public function login(){
        $username    = I('loginusername','','common_filter_one_word');
        $password    = I('loginpassword','','trim');
        $verify_code = I('verify_code','','trim');
        $res         = D('Admin/Admin','Service')->loginByUserNameAndPassword($username,$password,$verify_code);
        return $res;
    }
    /**
     * 退出
     */
    public function logout(){
        session('admin', null);
        $_SESSION['_admin_mock_user_info_'] = null;
        // unset($_SESSION);
        return array("error"=>0,"info"=>"登出成功");
    }
}