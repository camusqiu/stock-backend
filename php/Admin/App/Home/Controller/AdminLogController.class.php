<?php
namespace Home\Controller;
use Think\Controller;
import('Org.Util.Date');
class AdminLogController extends CommonController {
    
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

        $Model = M('AdminLog');
        $ModelAdmin = M('Admin');
        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);

        $ctime = I('param.ctime',-1);
        $ctimeend = I('param.ctimeend',-1);
        $type = I('param.type',-1);
        $admin_id = I('param.admin_id',-1);
        $condition = "1=1";
        if($type != -1 && $type){
            
        }
        if($admin_id != -1 && $admin_id){
             $condition = $condition." and admin_id='".$admin_id."'"; 
        }

        $allnum = $Model->where($condition." and ctime>='".$ctime."' and ctime<='".$ctimeend." 23:59:59'")->count();

        $list = $Model->where($condition." and ctime>='".$ctime."' and ctime<='".$ctimeend." 23:59:59'")->order("ctime desc")->page($this->curpage, $this->pagenum)->select();
        if($list){
            $code = 0;
            $msg = "suc";

            $num = count($list);
            for($i = 0; $i < $num; $i++){
                $AList = $ModelAdmin->where("user_id='".$list[$i]['admin_id']."'")->limit(1)->find();
                $list[$i]['admin_name'] = $AList['chinaname']; 
            }
        }else{
            $code = 10001;
            $msg = "服务器错误";
            $list = array();
        }

        $this->ajaxOutput($code, $msg, array('count'=>$allnum, 'list'=>$list));
    }   
       
}
?>
