<?php
namespace Home\Controller;
use Think\Controller;
import('Org.Util.Date');
class LetterInfoController extends CommonController {
	private $allnum = 0;
	private $msg = 0;
	private $code = 0;
	private $list = array();

	public function checkLogin(){
		$res_isLogin = $this->isLogin();
		if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }
        
        $pageName = I('param.pagename',-1);
        if($pageName != "-1"){
            $is_allow = pageAuthority($pageName, $res_isLogin);
            if($is_allow != "1"){
                $this->ajaxOutput(20402, "limit ", array('list'=>Array()));
            }
        }
        return $res_isLogin;
	}

	public function getLetterInfo($condition, $order){

		$Model = M('LetterInfo'); 
		$ModelLetter = M('Letter');
        $ModelUser = M('User');

		$curpage = I('param.curpage',1);
        $pagenum = I('param.pagenum',10);

        $this->allnum = $Model->where($condition)->count();

		$this->list = $Model->where($condition)->order($order)->page($curpage, $pagenum)->select();

		if($this->list || $this->list == null){
            $this->code = 0;
            $this->msg = "suc";

            if($this->list == null){
                $this->list = Array();
                $this->msg = "not find";
            }


            $num = count($this->list);
            for ($i=0; $i < $num; $i++) { 
            	$conditionTemp = "lid='".$this->list[$i]['id']."'";
            	$list = $ModelLetter->where($conditionTemp)->select();
                $numj = count($list);
                for ($j=0; $j < $numj; $j++) { 
                    $conditionUser = "id='".$list[$j]['to_user_id']."'";
                    $listname = $ModelUser->where($conditionUser)->select();
                    $this->list[$i]['to_user_name'] = $this->list[$i]['to_user_name'].$listname[0]['name'].",";

                    $this->list[$i]['to_user_id'] = $this->list[$i]['to_user_id'].$list[$j]['to_user_id'].",";
                }
                $this->list[$i]['to_user_name'] = substr($this->list[$i]['to_user_name'], 0, strlen($this->list[$i]['to_user_name'])-1);
                $this->list[$i]['to_user_id'] = substr($this->list[$i]['to_user_id'], 0, strlen($this->list[$i]['to_user_id'])-1);

            	$this->list[$i]['user_id'] = $list[0]['user_id'];
            	$this->list[$i]['is_read'] = $list[0]['is_read'];
            }


        }else{
            $this->list = Array();
            $this->code = 10001;
            $this->msg = "sql fail";
        }
	}


    public function get(){

        $admin_id = $this->checkLogin();

        $title = I('param.title',"");
        $ctime = I('param.ctime',"");
        $ctimeend = I('param.ctimeend',"");

        $condition = "1=1";
        if ($title) {
        	$condition = $condition." and title='".$title."'";
        }

        if ($ctime) {
        	$condition = $condition." and ctime>='".$ctime."'";
        }

        if ($ctimeend) {
        	$condition = $condition." and ctime<='".$ctimeend." 23:59:59'";
        }

        $this->getLetterInfo($condition, " ctime desc");
        $this->ajaxOutput($this->code, $this->msg, array('count'=>$this->allnum, 'list'=>$this->list));

    }

    public function add(){

        $admin_id = $this->checkLogin();
        if ($admin_id == 20402) {
        	$this->ajaxOutput(20402, "limit ", array('list'=>Array()));
        }

        $LetterInfo = D('LetterInfo');
        $ModelUser = M('User');

        $title = I('param.title',"");
        
        $contentTemp = I('param.content','');
        //$contentTemp = I('param.content','', 'htmlspecialchars');
        $content = htmlspecialchars_decode($contentTemp);
        //$content = strip_tags($contentTemp, "<a>");
        $user_id = $admin_id;
        //to_user_id实际填写为手机号
        $to_user_id = I('param.to_user_id',"");
        $opt = I('param.opt',"");
        $isalluser = I('param.isalluser',"");

        //$regex='/^((http|ftp|https):\/\/)?[\w-_.]+(\/[\w-_]+)*\/?$/';
        $regex = '@(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s(    )<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))@';
        //$url = htmlspecialchars_decode($str);
        //$a= preg_replace($regex, "<a href=\"".$arr[0][$i]."\"><font color=\"#1287CF\">".$arr[2][$i]."<font/>", $url);
        preg_match_all($regex,$content, $arr);         
        for($i=0,$j=count($arr[0]);$i<$j;$i++){
             $content = str_replace($arr[0][$i],"<a href=\"".$arr[0][$i]."\"><font color=\"#1287CF\">".$arr[0][$i]."</font></a>",$content);
             //echo $str;
        }       



        if ($opt) {
            $data['opt'] = $opt;
        }else{
            $data['opt'] = "";
        }

        if ($title) {
        	$data['title'] = $title;
        }

        if ($content) {
        	$data['content'] = $content;
        }

        if ($user_id) {
        	$data['user_id'] = $user_id;
        }

        if ($isalluser == 1) {
            $data['opt'] = "all";

            $userlist = $ModelUser->where("v_state=0 or v_state=3")->select();
            $numalluser = count($userlist);
            $to_user_id = array();
            for ($i=0; $i < $numalluser; $i++) { 
                $to_user_id[$i] = $userlist[$i]['id'];
            }
            $data['to_user_id'] = $to_user_id;
            $to_user_id_log = "alluser";
        }else {
            $phonenumArray = $to_user_id;
            $phonenum = count($phonenumArray);
            for ($i=0; $i < $phonenum; $i++) { 
                if($i + 1 == $phonenum && $i != 0){
                    $condition = $condition."'".$phonenumArray[$i]."')";  
                }else if ($i == 0){
                    if($phonenum == 1){
                        $condition = " phone_num in ('".$phonenumArray[$i]."')";      
                    }else{
                        $condition = " phone_num in ('".$phonenumArray[$i]."',";   
                    }
                }else{
                    $condition = $condition."'".$phonenumArray[$i]."',";   
                }
            }

            $userlist = $ModelUser->where($condition)->select();
            $numalluser = count($userlist);

            //检测错误手机号
            if ($numalluser != $phonenum) {
                for ($i=0; $i < $phonenum; $i++) { 
                    $Muser = $ModelUser->where(" phone_num='".$phonenumArray[$i]."'")->select();
                    if ($Muser == null) {
                        $this->ajaxOutput(-1, "手机号[".$phonenumArray[$i]."]不存在", array('count'=>0, 'list'=>array()));
                    }
                }
            }


            $to_user_id = array();
            for ($i=0; $i < $numalluser; $i++) { 
                $to_user_id[$i] = $userlist[$i]['id'];
                $to_user_id_log = $to_user_id_log.$userlist[$i]['id'].",";
            }

        	$data['to_user_id'] = $to_user_id;
        }

        
        $LData['table_name'] = "LetterInfo";
        $LData['type'] = 2;
        $LData['admin_id'] = $admin_id;


        //不限时执行
        set_time_limit(0);
        $req = $LetterInfo->sendLetter($data);
        if($req == flase){
            $code = $LetterInfo->getErrorNo();
        	$msg = $LetterInfo->getError();

            if ($code == 10001 && is_numeric($msg)) {
                $numUser = count($to_user_id);
                for ($i=0, $j=0; $i < $numUser; $i++) { 
                    if ($to_user_id[$i] >= is_numeric($msg)) {
                       $dataTemp[$j++] = $to_user_id[$i];
                    }
                }
                //插入失败重试
                sleep(1);
                $data['to_user_id'] = $dataTemp;
                $req = $LetterInfo->sendLetter($data);
                if($req == flase){
                    $code = $LetterInfo->getErrorNo();
                    $msg = $LetterInfo->getError();

                    if ($code == 10001 && is_numeric($msg)) {
                        $numUser = count($to_user_id);
                        for ($i=0, $j=0; $i < $numUser; $i++) { 
                            if ($to_user_id[$i] >= is_numeric($msg)) {
                               $dataTemp[$j++] = $to_user_id[$i];
                               $msg = $msg.$to_user_id[$i].",";
                            }
                        }
                        $msg = $msg."发送不成功，请稍后重新发送给这些用户";
                        $LData['msg'] = "站内信: ".$msg;
                    }
                    aLog($LData);
                    $this->ajaxOutput($code, $msg, array('count'=>0, 'list'=>array()));
                }
                $LData['msg'] = "站内信: user_id ".$to_user_id_log." 发送站内信成功";
                aLog($LData);
            }

        	$this->ajaxOutput($code, $msg, array('count'=>0, 'list'=>array()));
        }
        $LData['msg'] = "站内信: user_id ".$to_user_id_log." 发送站内信成功";
        aLog($LData);
        $this->ajaxOutput(0, "suc", array('count'=>0, 'list'=>array()));
    }




    public function search () {
        $admin_id = $this->checkLogin();
        if ($admin_id == 20402) {
            $this->ajaxOutput(20402, "limit ", array('list'=>Array()));
        }
        
        $title = I('param.title',"");
        $condition = "1=1";
        if($title){
            $condition = $condition." and title='".$title."'";
        }


        $Model = M('LetterInfo'); 
        $ModelLetter = M('Letter');
        $ModelUser = M('User');

        $this->list = $Model->where($condition)->select();
        if ($this->list) {
            $this->code = 0;
            $this->msg = "suc";
            if($this->list == null){
                $this->list = array();
                $msg = "no data";
            }

            $num = count($this->list);
            for ($i=0; $i < $num; $i++) { 
                $conditionTemp = "lid='".$this->list[$i]['id']."'";
                $list = $ModelLetter->where($conditionTemp)->select();
                $numj = count($list);
                for ($j=0; $j < $numj; $j++) { 
                    $conditionUser = "id='".$list[$j]['to_user_id']."'";
                    $listname = $ModelUser->where($conditionUser)->select();
                    $this->list[$i]['to_user_name'] = $this->list[$i]['to_user_name'].$listname[0]['name'].",";

                    $this->list[$i]['to_user_id'] = $this->list[$i]['to_user_id'].$list[$j]['to_user_id'].",";
                }
                $this->list[$i]['to_user_name'] = substr($this->list[$i]['to_user_name'], 0, strlen($this->list[$i]['to_user_name'])-1);
                $this->list[$i]['to_user_id'] = substr($this->list[$i]['to_user_id'], 0, strlen($this->list[$i]['to_user_id'])-1);

                $this->list[$i]['user_id'] = $list[0]['user_id'];
                $this->list[$i]['is_read'] = $list[0]['is_read'];
            }


        }else{
            $this->code = -1;
            $this->msg = "sql fail";
            $this->list = array();
        }
        $this->ajaxOutput($this->code, $this->msg, array('count'=>count($this->list), 'list'=>$this->list));

    }

}
?>
