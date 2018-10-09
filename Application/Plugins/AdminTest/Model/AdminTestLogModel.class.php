<?php
namespace Plugins\AdminTest\Model;
use Common\Model\CommonModel;
use Common\Util\PkTest;
class AdminTestLogModel extends CommonModel{
    // 测试验证内容，（目前还包括测试修改数据）
    const ADMIN_START_TEST    = 1111; // 新的测试

    protected $tmp_data;
    protected $old_data;
    protected $scene_id;
    //字段衍射
    protected $_map = array(
                            
                        );
    //修改插入后自动完成
    protected $_auto = array(
        // 新的测试
        array('item_title','set_item_title',self::ADMIN_START_TEST,'callback'),
        array('need_assert','set_need_assert',self::ADMIN_START_TEST,'callback'),
        array('return_assert','set_return_assert',self::ADMIN_START_TEST,'callback'),
        array('handle_return_data','set_handle_return_data',self::ADMIN_START_TEST,'callback'),
        array('assert_status','set_assert_status',self::ADMIN_START_TEST,'callback'),
        array('status',1,self::ADMIN_START_TEST),
    );

    protected $_validate = array(
        // 新的测试
        array('test_id', 'is_test_id_pass', '错误的测试id', self::MUST_VALIDATE,'callback',self::ADMIN_START_TEST),
        array('is_real_do', 'get_is_real_do', 'return_true', self::MUST_VALIDATE,'callback',self::ADMIN_START_TEST),
        array('test_data_group_index', 'is_test_data_group_index_pass', '[test]不存在测试数据组', self::MUST_VALIDATE,'callback',self::ADMIN_START_TEST),
        array('test_data_items_index', 'is_test_data_items_index_pass', '[test]不存在测试数据item', self::MUST_VALIDATE,'callback',self::ADMIN_START_TEST),

        array('test_id', 'get_this_model_test_data_by_test_id', 'return_true', self::MUST_VALIDATE,'callback',self::ADMIN_START_TEST),
        array('test_id', 'get_ok_post_data_from_old_data', 'return_true', self::MUST_VALIDATE,'callback',self::ADMIN_START_TEST),
        array('test_id', 'get_cur_post_data', 'return_true', self::MUST_VALIDATE,'callback',self::ADMIN_START_TEST),
        array('test_id', 'start_test', 'return_true', self::MUST_VALIDATE,'callback',self::ADMIN_START_TEST),
    );
    /**
     ***********************
     * 对外方法
     ***********************
     */
    public function getTestHandelReturn(){
        return $this->tmp_data['return'];
    }
    /**
     ***********************
     * 记录方法
     ***********************
     */
    protected function _after_insert($data, $options) {

    }
    protected function _after_update($data, $options) {

    }
    protected function _after_delete($data, $options) {

    }
    /**
     ***********************
     * 业务方法
     ***********************
     */
    protected function get_is_real_do($is_real_do=0){
        $this->tmp_data['is_real_do'] = $is_real_do;
        return true;
    }
    protected function is_model_name_pass($model_name=""){
        $this->tmp_data['model_name'] = $model_name;
        return true;
    }

    protected function get_uuid($uuid=""){
        $this->tmp_data['uuid'] = $uuid;
        return true;
    }

    protected function is_scene_id_pass($scene_id=0){
        $this->tmp_data['scene_id'] = $scene_id;
        return true;
    }

    protected function is_test_data_group_index_pass($test_data_group_index=0){
        $this->tmp_data['test_data_group_index'] = $test_data_group_index;
        return true;
    }

    protected function is_test_data_items_index_pass($test_data_items_index){
        $this->tmp_data['test_data_items_index'] = $test_data_items_index;
        return true;
    }

    protected function get_this_model_test_data_by_test_id($test_id){
        $test_info      = D('Plugins://AdminTest/AdminTest','Datamanager')->getInfo($test_id);

        $Plugins        = $test_info['is_plugin'] ? 'Plugins://' : '';
        $module_name    = $test_info['module_name'];
        $model_name     = $test_info['model_name'];
        $scene_id       = $test_info['scene_id'];
        $group_id       = $test_info['group_id'];
        $handle_object  = $test_info['handle_object'];
        $handle_action  = $test_info['handle_action'];

        $modelTest      = D($Plugins.$module_name.'/'.$model_name,'Test');

        $this->tmp_data['all_data']        = $modelTest->getTestData($scene_id,$this->tmp_data['test_data_group_index'],$this->tmp_data['test_data_items_index'],$test_id);
        $this->tmp_data['handle_action']   = $modelTest->getHandleAction($scene_id);
        $this->tmp_data['success_assert']  = $modelTest->getSuccessAssert($scene_id);
        $this->tmp_data['handle_object']   = $modelTest->getHandleObject();
        $this->tmp_data['test_title']      = $modelTest->getTestTitle($scene_id);
        
        $this->tmp_data['is_plugin']       = $modelTest->getIsPlugin();
        $this->tmp_data['module_name']     = $modelTest->getModuleName();
        $this->tmp_data['controller_name'] = $modelTest->getContrllerName();


    }

    protected function set_title(){
        return $this->tmp_data['test_title'];
    }

    protected function set_handle_object(){
        return $this->tmp_data['handle_object'];
    }

    protected function set_handle_action(){
        return $this->tmp_data['handle_action'];
    }

    protected function set_need_assert(){
        return $this->tmp_data['need_assert'];
    }

    protected function set_return_assert(){
        return $this->tmp_data['return_assert'];
    }

    protected function get_ok_post_data(){
        $field_array                    = array();
        $this->tmp_data['ok_post_data'] = array();
        foreach ($this->tmp_data['all_data']['test_data'] as $key => $value) {
            if(!in_array($value['field_name'], $field_array)){
                array_push($field_array, $value['field_success_value']);
                $this->tmp_data['ok_post_data'][$value['field_name']] = $value['field_success_value']; 
            }
        }
    }

    protected function get_ok_post_data_from_old_data(){
        $this->tmp_data['ok_post_data'] = unserialize($this->old_data['success_post_data']);
        return true;
    }

    protected function get_cur_post_data(){
        if($this->tmp_data['is_real_do'] == 1){
            return true;
        }else{
            $cur_post_data  = $this->tmp_data['ok_post_data'];
            $cur_error_test = $this->tmp_data['all_data']['cur_error'];
            foreach ($cur_post_data as $key => $value) {
                if($key == $cur_error_test['field_name']){
                    $cur_post_data[$key] = $cur_error_test['field_error_value']; // 替换上错误的字段
                }
            }
            $this->tmp_data['fail_post_data'] = $cur_post_data;
            return true;
        }
    }

    protected function start_test(){
        C('PK_TESTING',1);
        $cur_post_data = $this->tmp_data['is_real_do'] ? $this->tmp_data['ok_post_data'] : $this->tmp_data['fail_post_data'];
        $handleObj     = new $this->tmp_data['handle_object'](1);
        $handle_action   = $this->tmp_data['handle_action'];
        $_POST         = $this->_format_post_data($cur_post_data);

        
        C('PK_MODULE_NAME',$this->tmp_data['module_name']);
        C('PK_PLUGIN_NAME','');
        C('PK_ADMIN_CONTROLLER_NAME',$this->tmp_data['controller_name']);
        // C('PK_ADMIN_ACTION_NAME','Admin');


        $res           = $handleObj->$handle_action();


        // dump($this->tmp_data['handle_object']);
        // dump($this->tmp_data['handle_action']);
        // exit();
        $this->tmp_data['handle_return_data'] = serialize($res);
        $this->tmp_data['return_assert'] = $res['info'];
        $this->tmp_data['need_assert'] = $this->tmp_data['is_real_do'] ? $this->tmp_data['success_assert'] : $this->tmp_data['all_data']['cur_error']['assert'];

        // 如果有id返回（create）则返回
        // $this->tmp_data['handle_return_id'] = $res['id'] > 0 ? $res['id'] : 0;

        if( ($res['error'] > 0 && $this->tmp_data['is_real_do'] != 1) || ($res['error'] == 0 && $res['info'] != '' && $this->tmp_data['is_real_do'] == 1) ){

            if($res['info'] == $this->tmp_data['need_assert']){
                // 
                $this->tmp_data['assert_status'] = 1;
                $return = array('error'=>0,'assert_status'=>$this->tmp_data['assert_status'],'assert_info'=>$res['info'],'info'=>'断言成功','need_log'=>1);
            }else{
                // 
                $this->tmp_data['assert_status'] = 0;
                $return = array('error'=>0,'assert_status'=>$this->tmp_data['assert_status'],'assert_info'=>$res['info'],'info'=>'断言失败','need_log'=>0);
            }
        }else{
            // 因测试数据有误，在验证阶段就进行了修改操作
            $this->tmp_data['assert_status'] = 0;
            $return = array('error'=>0,'assert_status'=>$this->tmp_data['assert_status'],'assert_info'=>$res['info'],'info'=>'意外错误','need_log'=>1);
        }
        $this->tmp_data['return'] = $return;
        return true;
    }

    protected function set_test_data_items_index(){
        if($this->tmp_data['is_real_do'] == 1){
            return 0; // 
        }else{
            return $this->tmp_data['test_data_items_index'];
        }
    }

    protected function set_assert_status(){
        return $this->tmp_data['assert_status'];
    }

    // protected function set_handle_return_id(){
    //     return $this->tmp_data['handle_return_id'];
    // }

    private function _format_post_data($postdata){
        // todo 转移
        include_once APP_PATH.'Test/Common/function.php';

        foreach ($postdata as $key2 => $value2) {
            // {{$test_last_id}}
            if(strstr($value2,'{{$test_last_id}}')){
                $postdata[$key2] = str_replace("{{$test_last_id}}", admin_test_plugins_config_test_last_id(), $value2);
            }
            // @rand 
            if(strstr($value2,'@rand')){
                $postdata[$key2] = str_replace("@rand", common_filter_strs(guid()), $value2);
            }
        }
        return $postdata;
    }



    ////////////////////////////////////////////////////////////////////////


    protected function is_test_id_pass($test_id){
        $has = M('admin_test')->where(array('id'=>$test_id))->find();
        if($has){
            $this->old_data = $has;
            return true;
        }
        return false;
    }

    protected function set_item_title(){
        return $this->tmp_data['all_data']['cur_error']['title'];
    }

    protected function set_handle_return_data(){
        return $this->tmp_data['handle_return_data'];
    }



}