<?php
namespace Home\Controller;
use Think\Controller;
import('Org.Util.Date');
class MpSysversionController extends CommonController {
    
    private $ctime = "";
    private $ctimeend = "";
    private $curpage = 1;
    private $pagenum = 10;

    public function check () {
        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, '没有登录', array('list'=>Array()));
        }

        $pageName = I('param.pagename',-1);
        if($pageName != "-1"){
            $is_allow = pageAuthority($pageName, $res_isLogin);
            if($is_allow != "1"){
                $this->ajaxOutput(20402, "没有权限操作", array('list'=>Array()));
            }
        }
    }


    public function get() {

        $this->check();

        $Model = M('MpSysversion');
        $type = I('param.type',-1);
        $otype = I('param.otype',-1);
        $condition = "1=1";
        if($type != -1 && $type != null){
            $condition = $condition." and type='".$type."'";
        }

        if($otype != -1 && $otype != null){
            $condition = $condition." and otype='".$otype."'";
        }

        $list = $Model->where($condition)->select();
        if($list){
            $code = 0;
            $msg = "suc";
        }else{
            $code = 10001;
            $msg = "客户端类型错误";
            $list = array();
        }

        $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
    }

    public function add() {

        $this->check();

        $Model = M('MpSysversion');
        $data['type'] = I('param.type',-1);
        $data['otype'] = I('param.otype',-1);
        $data['version'] = I('param.ver',-1);
        
        $list = $Model->add($data);
        if($list){
            $code = 0;
            $msg = "suc";
        }else{
            $code = 10001;
            $msg = "新增系统版本失败";
            $list = array();
        }

        $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
    }      
    
}
?>
