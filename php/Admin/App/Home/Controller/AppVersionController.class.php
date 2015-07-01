<?php
namespace Home\Controller;
use Think\Controller;
import('Org.Util.Date');
class AppVersionController extends CommonController {
    
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

        $Model = M('AppVersion');
        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $type = I('param.type',-1);
        $condition = "1=1";
        if($type != -1 && $type){
            $condition = $condition." and client_type='".$type."'";
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


    public function save() {

        $this->check();

        $Model = M('AppVersion');
        $id = I('param.id',-1);
        $client_type = I('param.client_type',-1);
        $os = I('param.os','');
        $osto = I('param.osto','');
        $lver = I('param.lver','');
        $hver = I('param.hver','');
        $url = I('param.url','');
        
        $condition = "1=1";
        if($id == -1 || $id){
            $this->ajaxOutput(-1, 'ID错误，请刷新再试', array('list'=>Array()));
        }else{
            $condition = $condition." and id='".$id."'";
        }

        if($client_type == -1 || $client_type != null){
            $data['client_type'] = $client_type;
        }
        if($os == '' || $os != null){
            $data['os'] = $os;
        }
        if($osto == '' || $osto != null){
            $data['osto'] = $osto;
        }
        if($lver == '' || $lver != null){
            $data['lver'] = $lver;
        }
        if($hver == '' || $hver != null){
            $data['hver'] = $hver;
        }
        if($url == '' || $url != null){
            $data['url'] = $url;
        }

        $list = $Model->where($condition)->save($data);
        if($list){
            $code = 0;
            $msg = "suc";
        }else{
            $code = 10001;
            $msg = "修改失败";
            $list = array();
        }

        $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
    }   

    public function del() {

        $this->check();

        $Model = M('AppVersion');
        $id = I('param.id',-1);
        $condition = "1=1";
        if($id != -1 && $id){
            $condition = $condition." and id='".$id."'";
        }

        $list = $Model->where($condition)->del();
        if($list){
            $code = 0;
            $msg = "suc";
        }else{
            $code = 10001;
            $msg = "ID错误，请刷新再试";
            $list = array();
        }

        $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
    }     
}
?>
