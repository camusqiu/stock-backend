<?php
namespace Home\Controller;
use Think\Controller;
import('Org.Util.Date');
class ReportController extends CommonController {
    
    private $tipster_name = "";    
    private $ref_id_name = "";
    private $content = "";

    //根据消息状态筛选
    public function getReportList(){
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

        $state = I('param.state',-1);
        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $ctime = I('param.ctime',date('Y-m-d'));
        $ctimeend = I('param.ctimeend',date('Y-m-d'));

        $Model = M('Report');

        $condition = "1=1";
        if($state != "-1"){
            if($state == "0"){
                $condition = $condition." and state='".$state."'";
            }else {
                $condition = $condition." and state>'0'";
            }
        }

        if($ctime){
          $condition = $condition." and ctime>='".$ctime."'";
        }

        if($ctimeend){
          $condition = $condition." and ctime<='".$ctimeend." 23:59:59'";
        }
        $allnum = $Model->where($condition)->count();

        $list = $Model->where($condition)->order('ctime desc')->page($this->curpage, $this->pagenum)->select();
        if($list || $list == null){
          $code = 0;
          $msg = "suc";
          if($list == null){
            $list = array();
          }

          $num = count($list);
          for($i=0; $i<$num; $i++){
            if($list[$i]['type'] == "1"){
                $this->postName($list[$i]['ref_id']);
                $list[$i]['content'] = $this->content;
                $list[$i]['url'] = "http://www.richba.com/post/detail/".$list[$i]['ref_id'].".html";
            }else if($list[$i]['type'] == "2"){
                $this->comment($list[$i]['ref_id']);
                $list[$i]['content'] = $this->content;
                $list[$i]['url'] = "";
            }else if($list[$i]['type'] == "3"){
                $this->userName($list[$i]['ref_id']);
                $list[$i]['content'] = $this->tipster_name;
                $Luid = uid2LUid($list[$i]['ref_id']);
                $list[$i]['url'] = "http://www.richba.com/user.html?id=".$Luid;
            }
            $list[$i]['ref_id_name'] = $this->tipster_name;

            $this->userName($list[$i]['tipster']);
            $list[$i]['tipster_name'] = $this->tipster_name;
          }

        }else{
          $code = -1;
          $msg = "sql failed";
        }
        $this->ajaxOutput($code, $msg, array('count'=>$allnum, 'list'=>$list));
    }


    //搜索举报人/被举报人记录
    public function searchTipster() {
        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }
        $ModelUser = M('User');
        $ModelReport = M('Report');

        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $title = I('param.title',-1);
        $condition = "1=1";
        if($title != -1){
            // $this->condition = $this->condition." and (ref_id like '%".$title."%' or tipster like '%".$title."%')";
            $condition = $condition." and name='".$title."'";
        }

        $list = $ModelUser->where($condition)->select();
        if($list){
            $code = 0;
            $msg = "suc";

            $condition = " be_tipster='".$list[0]['id']."' or tipster='".$list[0]['id']."'";
            $listTemp = $ModelReport->where($condition)->select();
            if($listTemp){
                $num = count($listTemp);
                for($i=0; $i<$num; $i++){
                    $this->userName($listTemp[$i]['be_tipster']);
                    $listTemp[$i]['ref_id_name'] = $this->tipster_name;
                    $this->userName($listTemp[$i]['tipster']);
                    $listTemp[$i]['tipster_name'] = $this->tipster_name;

                    if($listTemp[$i]['type']=="1"){
                        $this->postName($listTemp[$i]['ref_id']);
                        $listTemp[$i]['content'] = $this->content;
                        $listTemp[$i]['url'] = "http://www.richba.com/post/detail/".$listTemp[$i]['ref_id'].".html";
                    }else if($listTemp[$i]['type']=="3"){
                        $this->userName($listTemp[$i]['ref_id']);
                        $listTemp[$i]['content'] = $this->tipster_name;
                        $Luid = uid2LUid($listTemp[$i]['ref_id']);
                        $listTemp[$i]['url'] = "http://www.richba.com/user.html?id=".$Luid;
                    }else if($listTemp[$i]['type']=="2"){
                        $this->comment($listTemp[$i]['ref_id']);
                        $listTemp[$i]['content'] = $this->content;
                        $listTemp[$i]['url'] = "";
                    }

                }
            }
            $list = $listTemp;
        }else{
            $code = 10001;
            $msg = "search user fail";
            $list = array();
        }
        $this->ajaxOutput($code, $msg, array('count'=>count($listTemp), 'list'=>$listTemp));
    }

     //审核举报记录
    public function auditReportRecord(){
        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $state = I('param.state',-1);
        $uid = I('param.id',-1);
        
        if($uid){
          $condition = " id='".$uid."'";
        }

        $Model = M('Report');

        $LData['table_name'] = "Report";
        $LData['type'] = 1;
        $LData['admin_id'] = $res_isLogin;
        
        $list = $Model->where($condition)->select();
        if($state == "2" || $state == "4"){
            //print_r($list);
            if($list && $list[0]['state'] == "3" && $state == "2"){
                //已经删除且锁定， 取消锁定到删除状态，删除状态不可逆 【解锁】   
                $state = "1";
                $stateTemp = "1";
                $this->updateUserInfo($list[0]['be_tipster'], $stateTemp);

                //其他相同被举报人一起解锁
                $listTemp = $Model->where(" be_tipster='".$list[0]['be_tipster']."'")->select();
                for($i=0; $i<count($listTemp); $i++){
                    $stateArray['state'] = $state;
                    $condition = " id='".$listTemp[$i]['id']."'";

                    if($listTemp[$i]['state'] == "2"){
                        $stateArray['state'] = "0";
                    }else if($listTemp[$i]['state'] == "4"){
                        $stateArray['state'] = "2";
                    }

                    $liststate = $Model->where($condition)->save($stateArray);
                }
                $tag = "1";

                $LData['code'] = 0;
                $LData['msg'] = "举报解锁: id ".$uid." user_id ".$list[0]['be_tipster']." 已删除且锁定-->解除锁定状态";
                aLog($LData);

            }else if($list && $list[0]['state'] == "0" && $state == "2"){
                //report表锁定为2   user表锁定为0 转化下           【锁定】
                $stateTemp = "0";
                $this->updateUserInfo($list[0]['be_tipster'], $stateTemp);

                //其他相同被举报人一起锁定
                $listTemp = $Model->where(" be_tipster='".$list[0]['be_tipster']."'")->select();
                for($i=0; $i<count($listTemp); $i++){
                    $stateArray['state'] = $state;
                    $condition = " id='".$listTemp[$i]['id']."'";

                    if($listTemp[$i]['state'] == "1"){
                        $stateArray['state'] = "3";
                    }
                    $liststate = $Model->where($condition)->save($stateArray);
                }
                $tag = "1";

                $LData['code'] = 0;
                $LData['msg'] = "举报锁定: id ".$uid." user_id ".$list[0]['be_tipster']." 用户被锁定";
                aLog($LData);

            }else if($list && $list[0]['state'] == "2" && $state == "2"){
                //解除锁定                                       【解锁】
                $state = "0";
                $stateTemp = "1";
                $this->updateUserInfo($list[0]['be_tipster'], $stateTemp);

                //其他相同被举报人一起解锁
                $listTemp = $Model->where(" be_tipster='".$list[0]['be_tipster']."'")->select();
                for($i=0; $i<count($listTemp); $i++){
                    $stateArray['state'] = $state;
                    $condition = " id='".$listTemp[$i]['id']."'";

                    if($listTemp[$i]['state'] == "3"){
                        $stateArray['state'] = "1";
                    }
                    $liststate = $Model->where($condition)->save($stateArray);
                }
                $tag = "1";

                $LData['code'] = 0;
                $LData['msg'] = "举报解锁: id ".$uid." user_id ".$list[0]['be_tipster']." 解除锁定状态";
                aLog($LData);

            }else if($list && $list[0]['state'] == "1" && $state == "2"){
                //已删除 继续锁定
                $state = "2";
                $stateTemp = "0";
                $this->updateUserInfo($list[0]['be_tipster'], $stateTemp); 

                 //其他相同被举报人一起锁定
                $listTemp = $Model->where(" be_tipster='".$list[0]['be_tipster']."'")->select();
                for($i=0; $i<count($listTemp); $i++){
                    $stateArray['state'] = $state;
                    $condition = " id='".$listTemp[$i]['id']."'";
                    if($listTemp[$i]['state'] == "1" || $list[0]['id'] == $listTemp[$i]['id']){
                        $stateArray['state'] = "3";
                    }else if($listTemp[$i]['state'] == "2"){
                        $stateArray['state'] = "0";
                    }else if($listTemp[$i]['state'] == "0"){
                        $stateArray['state'] = "2";
                    }

                    $liststate = $Model->where($condition)->save($stateArray);
                }
                $tag = "1";

                $LData['code'] = 0;
                $LData['msg'] = "举报锁定: id ".$uid." user_id ".$list[0]['be_tipster']." 已删除-->已删除且锁定";
                aLog($LData);

            }else if($list && $list[0]['state'] == "0" && $state == "4"){
                //状态为锁定和忽略都可逆，返回到未审核状态(无处理)
                $LData['code'] = 0;
                $LData['msg'] = "举报忽略: id ".$uid." 初始状态-->已忽略";
                aLog($LData);
            }else if($list && $list[0]['state'] == "4"){
                //状态为锁定和忽略都可逆，返回到未审核状态(无处理)
                $state = "0";
                $LData['code'] = 0;
                $LData['msg'] = "举报取消忽略: id ".$uid." 已忽略-->初始状态";
                aLog($LData);
            }
        }else if($state == "1"){
            if($list && $list[0]['type'] == "1"){           //删除帖子
                $this->delPost($list[0]['ref_id'], $list[0]['be_tipster']);
            }else if($list && $list[0]['type'] == "2"){     //删除评论
                //////$this->delPost($uid, $list[0]['user_id']);
            }
            $LData['code'] = 0;
            $LData['msg'] = "举报锁定: id ".$uid." user_id ".$list[0]['be_tipster']." 已删除-->已删除且锁定";
            aLog($LData);
        }

        if($tag == "1"){
            $this->ajaxOutput(0, "suc", array('count'=>count($listTemp), 'list'=>$listTemp));
        }else{
            $data['state'] = $state;
            $listTemp = $Model->where($condition)->save($data);
            if($listTemp || $listTemp == null){
                $code = 0;
                $msg = "suc";
                if($listTemp == null){
                   $listTemp = array();
                }                
            }else{
              $code = -1;
              $msg = "audit sql failed";
            }
            $this->ajaxOutput($code, $msg, array('count'=>count($listTemp), 'list'=>$listTemp));
        }
            
    }
    

    public function userName($uid){

        $Model = M('User');

        if($uid){
          $condition = " id='".$uid."'";
        }

        $list = $Model->where($condition)->select();
        if($list || $list == null){
          $code = 0;
          $msg = "suc";
          $this->tipster_name = $list[0]['name'];
          if($list == null){
            $list = array();
          }
        }else{
          $code = -1;
          $msg = "audit sql failed";
        }
        //$this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
    }

    public function postName($postid){

        $Model = M('Post');

        if($postid){
          $condition = " id='".$postid."'";
        }

        $list = $Model->where($condition)->select();
        if($list || $list == null){
          $code = 0;
          $msg = "suc";

          if($list == null){
            $list = array();
          }else{
            $this->content = $list[0]['title'];
            $this->userName($list[0]['user_id']);
          }
        }else{
          $code = -1;
          $msg = "audit sql failed";
        }
        //$this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
    }

    public function comment($commentid){

        $Model = M('Comment');

        if($commentid){
          $condition = " id='".$commentid."'";
        }

        $list = $Model->where($condition)->select();
        if($list || $list == null){
          $code = 0;
          $msg = "suc";          
          if($list == null){
            $list = array();
          }else{
            $this->content = $list[0]['content'];
            $this->userName($list[0]['user_id']);
          }
          

        }else{
          $code = -1;
          $msg = "audit sql failed";
        }
        //$this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
    }

    public function updateUserInfo($uid, $state) {

        $Model = M('User');

        $data['state'] = $state;
        $list = $Model->where("id='".$uid."'")->save($data);
        if($list){
            $code = 0;
            $msg = "suc";

            getUinfo($uid, array(), 'must');
        }else{
            $code = -1;
            $msg = "update UserInfo failed!"; 
            $list = array();
        }

        //$this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
    }


    public function delPost($id, $user_id){
        // 初始化一个 cURL 对象 
        $curl = curl_init(); 


        //curl 'http://www.richba.com/index.php?m=home&c=cmd&a=delPost&id=25980&user_id=1010101'
        // 设置你需要抓取的URL
        if ($id && $user_id) {
            $url = 'http://www.richba.com/index.php?m=home&c=cmd&a=delPost&id='.$id.'&user_id='.$user_id;
            curl_setopt($curl, CURLOPT_URL, $url); 
        }
        
        // 设置header 
        curl_setopt($curl, CURLOPT_HEADER, 1); 

        // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 

        // 运行cURL，请求网页 
        $data = curl_exec($curl); 

        // 关闭URL请求 
        curl_close($curl); 
    }


}
?>
