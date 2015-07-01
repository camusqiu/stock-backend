<?php
namespace Home\Controller;
use Think\Controller;
import('Org.Util.Date');
class LetterController extends CommonController {
    
	public function checkLogin(){
		$res_isLogin = $this->isLogin();
		if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }
        
        $pageName = I('param.pagename',-1);
        if($pageName != "-1"){
            $is_allow = pageAuthority($pageName, $res_isLogin);
            if($is_allow != "1"){
            	return 20402;
            }
        }
        return $res_isLogin;
	}

	public function getLetterInfo($condition, $order){

		$Model = M('LetterInfo'); 

		$curpage = I('param.curpage',1);
        $pagenum = I('param.pagenum',10);

        $allnum = $Model->where($this->condition)->count();

		$list = $Model->where($condition)->order($order)->page($curpage, $pagenum)->select();

		if($list || $list == null){
            $code = 0;
            $msg = "suc";

            if($list == null){
                $list = Array();
                $msg = "not find";
            }
        }else{
            $list = Array();
            $code = 10001;
            $msg = "sql fail";
        }

        $this->ajaxOutput($code, $msg, array('count'=>$allnum, 'list'=>$list));
	}


    public function get(){
        $admin_id = $this->checkLogin();
        if ($admin_id == 20402) {
        	$this->ajaxOutput(20402, "limit ", array('list'=>Array()));
        }

        $title = I('param.title',"");
        $ctime = I('param.ctime',"");
        $ctimeend = I('param.ctimeend',"");

        $condition = "1=1";
        if ($title) {
        	$condition = " and title='".$title."'";
        }

        if ($ctime) {
        	$condition = " and ctime>='".$ctime."'";
        }

        if ($ctimeend) {
        	$condition = " and ctime<='".$ctimeend."'";
        }

        $this->getLetterInfo($condition);

    }

}
?>
