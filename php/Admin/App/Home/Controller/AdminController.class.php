<?php
namespace Home\Controller;
use Home\Controller\CommonController;

/**
 * “用户”控制器类。
 */ 
class AdminController extends CommonController {
    /**
     * 登录。
     */
    public function login() {
        $name = I('username', -1);
        $pwd = I('password', -1);

        $adminModel = M('Admin');

        $condition = "name='".$name."' and password='".$pwd."'";
        $list = $adminModel->where($condition)->select();
        if($list){
			$code = 0;
			$msg = "登陆成功";
			session('uname', $name);
			session('user_id', $list[0]['user_id']);
		}else{
            $list = Array();
			$code = 20401;
			$msg = "用户名:".$name." 或者密码 ".$pwd." 错误";
		}

        $LData['table_name'] = "Admin";
        $LData['type'] = 0;
        $LData['admin_id'] = $list[0]['user_id'];
        $LData['code'] = $code;
        $LData['msg'] = "登陆验证: 用户 ".$name." 执行登陆验证 ".$msg;
        aLog($LData);

        $this->ajaxOutput($code, $msg, array('list'=>$list));
    }

    public function getUserid() {
        $user_id = $this->isLogin();
        if($user_id){
            $this->ajaxOutput(0, 'suc', array('user_id'=>$user_id, 'list'=>Array()));
        }else{
            $this->ajaxOutput(20401, 'unlogin', array('user_id'=>$user_id, 'list'=>Array()));
        }
    }

    public function getManagerid() {
        $user_id = $this->isLogin();
        if(!$user_id){
            $this->ajaxOutput(20401, 'unlogin', array('user_id'=>$user_id, 'list'=>Array()));
        }

        $adminModel = M('Admin');
        $condition = "1=1 and user_id='".$user_id."'";
        $list = $adminModel->where($condition)->select();
        if($list){
            if($list[0]['level'] > 1){
                $this->ajaxOutput(0, 'suc', array('user_id'=>$user_id, 'list'=>Array()));
            }else{
                $this->ajaxOutput(20402, 'user limited', array('user_id'=>$user_id, 'list'=>Array()));
            }
        }else{
            $list = Array();
            $code = 10001;
            $msg = "sql fail!";
            $this->ajaxOutput(20401, 'unlogin', array('user_id'=>$user_id, 'list'=>Array()));
        }
    }

    public function getUser() {
        $adminModel = M('Admin');
        $condition = "1=1 and (level='1' or level='2' or level='3')";
        $all = I('param.all',-1);
        if($all && $all != null && $all != -1){
           $condition = "1=1"; 
        }
        $listdata = $adminModel->where($condition)->select();
        //$list = $adminModel->where($condition)->getField('id,user_id,name,chinaname');
        if($listdata){
            $code = 0;
            $msg = "suc";
            if($listdata==null){
                $list = Array();
            }
            for ($i=0; $i < count($listdata); $i++) { 
                $list[$i]['id'] = $listdata[$i]['id'];
                $list[$i]['user_id'] = $listdata[$i]['user_id'];
                $list[$i]['name'] = $listdata[$i]['name'];
                $list[$i]['chinaname'] = $listdata[$i]['chinaname'];
            }
        }else{
            $list = Array();
            $code = 10001;
            $msg = "sql fail!";
        }
        $this->ajaxOutput($code, $msg, array('list'=>$list));
    }
}
