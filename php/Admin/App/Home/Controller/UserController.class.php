<?php
namespace Home\Controller;
use Home\Controller\CommonController;

/**
 * “用户”控制器类。
 */ 
class UserController extends CommonController {
    /**
     * 登录。
     */
    public function login() {
        $this->display();
    }

    public function getUserInfo() {

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

        $orderby = I('param.order',-1);
        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $userType = I('param.usertype',0);
        $this->condition = "1=1";
        $ctime = I('param.ctime',date('Y-m-d'));
        $ctimeend = I('param.ctimeend',date('Y-m-d'));
        $Model = M('User');	
        $ModelLogin = M('LoginStats');	
        $ModelPost = M('Post');	
//        $ModelMoney = M('FundManagement'); 

		$conditionTemp = sprintf(" and ctime>'%s' and ctime<'%s'", $ctime, $ctimeend." 23:59:59");
		$this->condition = $this->condition.$conditionTemp;
        
        if($userType == 1){
            $this->condition = $this->condition." and phone_num not like '1002000%'";
        }else if($userType == 2){
            $this->condition = $this->condition." and phone_num like '1002000%'";
        }else if ($userType == 3){
            $this->condition = $this->condition." and v_state='3'";
        }else if ($userType == 4){
            $this->condition = $this->condition." and state='0'";
            $numall = M('VUserApply')->where($this->condition)->count();
            $VList= M('VUserApply')->table('v_user_apply v')->join('user u on u.id = v.user_id')->field('u.*, v.ctime as ctime, v.user_id as user_id, v.id as vid')->where('v.state=0')->select();
            if ($numall == 0) {
                $this->ajaxOutput(-1, "没有申请用户", array('count'=>0, 'list'=>array()));
            }

            if (!$VList) {
                $this->ajaxOutput(-1, "用户ID不存在", array('count'=>0, 'list'=>array()));
            }

            $list = $VList;
        }        

        if ($userType != 4) {
            $numall = $Model->where($this->condition)->count();
            if ($orderby > 0) {
                $list = $Model->where($this->condition)->order('ctime desc')->select();
            }else{
                $list = $Model->where($this->condition)->order('ctime desc')->page($this->curpage, $this->pagenum)->select();
            }
        }
		

    	$num = count($list);
        if($list){
        	$keyPost ="user_post_count_total";
        	$keyFans ="user_fans_count_total";
		    $redis = S(array('type'=>'Redis'));

        	for($i=0; $i < $num; $i++){
        		$listLogin = $ModelLogin->where("user_id='".$list[$i]['id']."'")->select();
        		if($listLogin){
        			$list[$i]['ip'] = $listLogin[0]['ip'];
        		}else{
        			$list[$i]['ip'] = "";
        		}

        		$listPost = $ModelPost->where("user_id='".$list[$i]['id']."'")->order("ctime desc")->limit(1)->select();
        		if($listPost){
        			$list[$i]['lastposttime'] = $listPost[0]['ctime'];
        		}else{
        			$list[$i]['lastposttime'] = "";
        		}

        		//用户粉丝数
        		$listTemp = $redis->ZSCORE($keyFans, $list[$i]['id']);
	            $list[$i]['fans'] = $listTemp ? $listTemp : 0;

	            //用户发帖总数
	            $listTemp = $redis->ZSCORE($keyPost, $list[$i]['id']);
	            $list[$i]['post'] = $listTemp ? $listTemp : 0;

        	}

        	$code = 0;
        	$msg = "suc";
        }else{
        	$code = 10001;
        	$msg = "search user fail";
        	$list = array();
        }


        if($orderby > 0){
            $this->sortUserInfo($list, $numall, $orderby, $code, $msg);
        }else{
            $this->ajaxOutput($code, $msg, array('count'=>$numall, 'list'=>$list));
        }

    }


    public function sortUserInfo($list, $numall, $value, $code, $msg){

        $listTemp = array();
        $num = count($list);

        if ($value == 1) {  //order by fans
            for ($i=0; $i < $num - 1; $i++) { 
                $max = $list[$i]['fans'];
                $listTemp = $list[$i];
                $tag = $i;
                for ($j=$i+1; $j < $num; $j++) { 
                    if ($list[$j]['fans'] > $max) {
                        $max = $list[$j]['fans'];
                        $list[$i] = $list[$j];
                        $tag = $j;
                    }
                }
                $list[$tag] = $listTemp;
            //echo "num: ".$num." i:".$i." tag:".$tag."|   ";
            //print_r($list[$i]);
            //print_r($list[$j]);
            }
        }else if($value == 2){  //order by post
            for ($i=0; $i < $num - 1; $i++) { 
                $max = $list[$i]['[post]'];
                $listTemp = $list[$i];
                $tag = $i;
                for ($j=$i+1; $j < $num; $j++) { 
                    if ($list[$j]['post'] > $max) {
                        $max = $list[$j]['post'];
                        $list[$i] = $list[$j];
                        $tag = $j;
                    }
                }
                $list[$tag] = $listTemp;

            }
        }else if($value == 3){  //order by balance
            for ($i=0; $i < $num - 1; $i++) { 
                $max = $list[$i]['fund'];
                $listTemp = $list[$i];
                $tag = $i;
                for ($j=$i+1; $j < $num; $j++) { 
                    if ($list[$j]['fund'] > $max) {
                        $max = $list[$j]['fund'];
                        $list[$i] = $list[$j];
                        $tag = $j;
                    }
                }
                $list[$tag] = $listTemp;

            }
        }
        
        $tag = $this->pagenum*($this->curpage-1);
        for ($i=0; $i < $this->pagenum && $i < $num-$tag; $i++) { 
            $listShow[$i] = $list[$tag+$i]; 
        }
        
        $this->ajaxOutput($code, $msg, array('count'=>$numall, 'list'=>$listShow));
    }

    public function updateUserInfo() {
        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $Model = M('User');
        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $this->condition = "1=1";
        $state = I('param.state',-1);
        $id = I('param.id',-1);

        $LData['table_name'] = "User";
        $LData['type'] = 1;
        $LData['admin_id'] = $res_isLogin;

        $condition = "id='".$id."'";
        $list = $Model->where($condition)->select();

        if ($state == 3 && $list) {
            //认证
            $v_state = 3;
            $VUserData['state'] = "1";

            if($list[0]['v_state'] == $v_state){
                $data['v_state'] = "0";
                $VUserData['state'] = "2";

                $LData['code'] = 0;
                $LData['msg'] = "user:[认证用户: id ".$id." v_state ".$v_state." 取消认证]";
                
            }else if($list[0]['v_state'] == 0){
                $data['v_state'] = $state;

                $LData['code'] = 0;
                $LData['msg'] = "user:[认证用户: id ".$id." v_state ".$v_state." 设置认证]";

                $this->sendLetters("你的账号被设置为认证用户", "1010101", $id);
            }else {
                $LData['code'] = -1;
                $LData['msg'] = "user:[认证用户: id ".$id." v_state ".$v_state." 官方/系统账号不可认证]";
                aLog($LData);
                $this->ajaxOutput(-1, "官方/系统账号不可认证", array('count'=>0, 'list'=>array()));
            }

            if($v_state != -1){
                $this->condition = $this->condition." v_state='".$v_state."'";
            }

            $VUserList = M('VUserApply')->where("user_id='".$id."' and state=0")->select();
            if ($VUserList) {
                $VUserList = M('VUserApply')->where("user_id='".$id."' and state=0")->save($VUserData);
            }

            $Model = M('User'); 
            $list = $Model->where("id='".$id."'")->save($data);
            if($list){
                $code = 0;
                $msg = "suc";

                getUinfo($id, array(), 'must');
            }else{
                $code = -1;
                $msg = "updateUserInfo failed!"; 
                $list = array();

                $LData['code'] = -1;
                $LData['msg'] = "user:[认证用户: id ".$id." exv_state ".$list[0]['v_state']." nowv_state ".$v_state." (取消)认证操作失败]";
            }
            aLog($LData);

            //写v用户记录表
            $ModelV = M('VUser');

            $TList = $ModelV->where("user_id='".$id."'")->order('ctime desc')->limit(1)->select();
            if ($TList && $TList != null) {
                $TData['etime'] = date("Y-m-d H:i:s");
                $list = $ModelV->where("id='".$TList[0]['id']."'")->save($TData);
            }

            $VData['user_id'] = $id;
            if ($data['v_state'] > 0) {
                $VData['v_state'] = 1;
            }else{
                $VData['v_state'] = 0;
            }
            $VList = $ModelV->add($VData);

        }else if ($list){
            if($list[0]['state'] == $state){
                $data['state'] = "1";

                $LData['code'] = 0;
                $LData['msg'] = "user:[取消锁定/禁言: id ".$id." state ".$state." 锁定/禁言-->初始状态]";
                
            }else {
                $data['state'] = $state;

                $LData['code'] = 0;
                $LData['msg'] = "user:[锁定/禁言: id ".$id." state ".$state." 锁定/禁言]";
            }

            if($state != -1){
                $this->condition = $this->condition." state='".$state."'";
            }
            $Model = M('User'); 

            $list = $Model->where("id='".$id."'")->save($data);
            if($list){
                $code = 0;
                $msg = "suc";

                getUinfo($id, array(), 'must');
            }else{
                $code = -1;
                $msg = "updateUserInfo failed!"; 
                $list = array();

                $LData['code'] = -1;
                $LData['msg'] = "user:[(取消)锁定/禁言: id ".$id." exstate ".$list[0]['state']." nowstate ".$state." (取消)锁定/禁言操作失败]";
            }
            aLog($LData);

        }else{
            $code = -1;
            $msg = "search user info failed!"; 
            $list = array();
        }

        
        
        
        $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
    }    

    /**
     * 用户搜索
    **/
    public function searchUserInfo() {
        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }
        $Model = M('User');

        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $title = I('param.title',-1);
        $this->condition = "1=1";
        if($title != -1){
            $this->condition = $this->condition." and name like '%".$title."%'";
        }

//        echo $this->condition;
        $list = $Model->where($this->condition)->select();
        if($list){
            $keyPost ="user_post_count_total";
            $keyFans ="user_fans_count_total";
            $redis = S(array('type'=>'Redis'));

            for($i=0; $i < $num; $i++){
                $listLogin = $ModelLogin->where("user_id='".$list[$i]['id']."'")->select();
                if($listLogin){
                    $list[$i]['ip'] = $listLogin[0]['ip'];
                }else{
                    $list[$i]['ip'] = "";
                }

                $listPost = $ModelPost->where("user_id='".$list[$i]['id']."'")->order("ctime desc")->limit(1)->select();
                if($listPost){
                    $list[$i]['lastposttime'] = $listPost[0]['ctime'];
                }else{
                    $list[$i]['lastposttime'] = "";
                }

                //用户粉丝数
                $listTemp = $redis->ZSCORE($keyFans, $list[$i]['id']);
                $list[$i]['fans'] = $listTemp ? $listTemp : 0;

                //用户发帖总数
                $listTemp = $redis->ZSCORE($keyPost, $list[$i]['id']);
                $list[$i]['post'] = $listTemp ? $listTemp : 0;

            }

            $code = 0;
            $msg = "suc";
        }else{
            $code = 10001;
            $msg = "search user fail";
            $list = array();
        }

        $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
    }


    public function sendLetters ($title, $user_id, $to_user_id) {

        $LetterInfo = D('LetterInfo');
        $dataLetter['opt'] = "";

        $dataLetter['title'] = $title;
        $dataLetter['content'] = $title;

        if ($user_id) {
            $dataLetter['user_id'] = $user_id;
        }

        if ($to_user_id) {
            $dataLetter['to_user_id'] = $to_user_id;
        }

        $req = $LetterInfo->sendLetter($dataLetter);
        if($req == flase){
            $code = $LetterInfo->getErrorNo();
            $msg = $LetterInfo->getError();
            if ($code == 10001) {
                //插入失败重试;
                sleep(1);
                $req = $LetterInfo->sendLetter($dataLetter);
                if($req == flase){
                    $code = $LetterInfo->getErrorNo();
                    $msg = $LetterInfo->getError();
                    $this->ajaxOutput($code, $msg, array('count'=>0, 'list'=>array()));
                }
            }
        }
    }    


}
