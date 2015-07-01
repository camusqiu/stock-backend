<?php
namespace Home\Controller;
use Think\Controller;
import('Org.Util.Date');
class AbilityTagController extends CommonController {
    
    //根据提款记录的状态筛选
    public function getAbilityTagList(){
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

        $order = I('param.order', 1);
        $title = I('param.title',-1);
        
        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $Model = M('AbilityTag');
        $ModelUser = M('UserAbility');

        $condition = "1=1";
        if($title != -1){
            $condition = $condition." and name like '%".$title."%'";
        }

        $countnum = $Model->where($condition)->count();
        if($order == "1"){
            $orderby = "ctime desc";
            $list = $Model->where("$condition")->order($orderby)->page($this->curpage, $this->pagenum)->select();
        }else{
            $list = $Model->where("$condition")->select();
        }

        if($list || $list == null){
          $code = 0;
          $msg = "suc";
          if($list == null){
            $list = array();
          }

          $num = count($list);
          for($i=0; $i<$num; $i++){
            $condition = " ability_tag_id='".$list[$i]['id']."'";
            $numall = $ModelUser->where($condition)->count();
            $list[$i]['refnum'] = $numall;
          }

          if($order == "2"){
            for ($i=0; $i < $num - 1; $i++) { 
                $max = $list[$i]['refnum'];
                $listTemp = $list[$i];
                $tag = $i;
                for ($j=$i+1; $j < $num; $j++) { 
                    if ($list[$j]['refnum'] > $max) {
                        $max = $list[$j]['refnum'];
                        $list[$i] = $list[$j];
                        $tag = $j;
                    }
                }
                $list[$tag] = $listTemp;
            }

            $tag = $this->pagenum*($this->curpage-1);
            for ($i=0; $i < $this->pagenum && $i < $countnum-$tag; $i++) { 
                $listshow[$i] = $list[$tag+$i]; 
            } 
          }
        }else{
          $code = -1;
          $msg = "sql failed";
        }

        if($order == "1"){
            $this->ajaxOutput($code, $msg, array('count'=>$countnum, 'list'=>$list));
        }else if($order == "2"){
            $this->ajaxOutput($code, $msg, array('count'=>$countnum, 'list'=>$listshow));
        }
        
    }

    //搜索用户标签记录
    public function searchAblityTagInfo() {
        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }
        $Model = M('AbilityTag');
        $ModelUser = M('UserAbility');

        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $title = I('param.title',-1);
        $condition = "1=1";
        if($title != -1){
            $condition = $condition." and name like '%".$title."%'";
        }

        $list = $Model->where($condition)->select();
        if($list){
            $code = 0;
            $msg = "suc";

            for($i=0; $i<count($list); $i++){
                $conditionTemp = " ability_tag_id='".$list[$i]['id']."'";

                $numall = $ModelUser->where($conditionTemp)->count();
                $list[$i]['refnum'] = $numall;
            }
        }else{
            $code = 10001;
            $msg = "search user fail";
            $list = array();
        }

        $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
    }    
 
    //审核提款记录
    public function updateState(){
        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $state = I('param.state',-1);
        $uid = I('param.id',-1);
        
        if($uid){
          $condition = " id='".$uid."'";
        }

        $data['state'] = $state;
        $Model = M('AbilityTag');

        $list = $Model->where($condition)->save($data);
        if($list || $list == null){
          $code = 0;
          $msg = "suc";
          if($list == null){
            $list = array();
          }

        }else{
          $code = -1;
          $msg = "audit sql failed";
        }
        $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
    }

    public function addAbilityTag(){
        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $name = I('param.name',-1);
        $Model = M('AbilityTag');

        $LData['table_name'] = "AbilityTag";
        $LData['type'] = 2;
        $LData['admin_id'] = $res_isLogin;

        if($name != "-1"){
            $listData['name'] = $name;
        }

        $list = $Model->add($listData);
        if($list || $list == null){
            $code = 0;
            $msg = "suc";
            if($list == null){
               $list = Array();
            }

            $LData['code'] = 0;
            $LData['msg'] = "AbilityTag:[用户标签: name ".$name." 创建成功]";
            aLog($LData);

            $key = "sys_user_tag";
            $redis = S(array('type'=>'Redis'));
            $listTemp = $redis->del($key);
        }else{
            $list = Array();
            $code = -1;
            $msg = "add failed";
        }

        $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
    }

    public function delAbilityTag(){

        $id = I('param.id',-1);
        $Model = M('AbilityTag');
        $ModelUserAbility = M('UserAbility');

        $LData['table_name'] = "AbilityTag";
        $LData['type'] = 3;
        $LData['admin_id'] = $res_isLogin;

        if($id != "-1"){
            $condition = "ability_tag_id='".$id."'";
        }

        if($condition){
            //删除ability_tag中用户管理id的记录
            $listUserAbility = $ModelUserAbility->where($condition)->select();
            if($listUserAbility){
                $listdel = $ModelUserAbility->where($condition)->delete();
                if($listdel){
                    $num = count($listUserAbility);
                    for($i=0; $i<$num; $i++){
                        getUinfo($listUserAbility[$i]['user_id'], array(), 'must');
                    }
                }else{
                    $list = Array();
                    $code = -1;
                    $msg = "del ability_tag_id failed";
                    $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
                }
            }else if($listUserAbility == null){
                $list = Array();
                $code = 0;
                $msg = "select ability_tag_id 0";
            }else{
                $list = Array();
                $code = -1;
                $msg = "select ability_tag_id failed";
                $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list)); 
            }

            $condition = "id='".$id."'";
            $list = $Model->where($condition)->delete();
            if($list || $list == null){
                $code = 0;
                $msg = "suc";
                if($list == null){
                   $list = Array();
                }

                $LData['code'] = 0;
                $LData['msg'] = "AbilityTag:[用户标签: id ".$id." 删除成功]";
                aLog($LData);

                $key = "sys_user_tag";
                $redis = S(array('type'=>'Redis'));
                $listTemp = $redis->del($key);

                $key = "ability_user_".$id;
                $listTemp = $redis->del($key);

            }else{
                $list = Array();
                $code = -1;
                $msg = "add failed";
            }

            $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
        }else{
            $this->ajaxOutput("-1", "illegal id", array('count'=>"0", 'list'=>array()));
        }
    }

}
?>
