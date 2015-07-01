<?php
namespace Home\Controller;
use Think\Controller;
import('Org.Util.Date');
class OrderController extends CommonController {
    private $ctime = "";
    private $ctimeend = "";
    
    //根据提款记录的状态筛选
    public function getFundList(){
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

        $type = I('param.type',-1);
        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $this->ctime = I('param.ctime',date('Y-m-d'));
        $this->ctimeend = I('param.ctimeend',date('Y-m-d'));
        $name = I('param.title',-1);

        $Model = M('Order');
        $ModelUser = M('User');
        
        $pageName = I('param.pagename',-1);
        if($pageName != "-1"){
            $is_allow = pageAuthority($pageName, $res_isLogin);
        }


        $condition = "1=1";
        if($type == "1"){
          $condition = $condition." and type='1' and state>='20'";
        }else if ($type == "2"){
          $condition = $condition." and type='".$type."'";
        }else if ($type == "3"){
          $condition = $condition." and (type='3' || type='4')";
        }else if ($type == "4"){
          $condition = $condition." and state>='20' and (type='5' || type='6' || type='7')";
        }else if ($type == "5"){
          $condition = $condition." and type='1' and state<'20'";
        }else if ($type == "6"){
          $condition = $condition." and type='4'";
        }else if ($type == "0"){
          $condition = $condition." and state>='20'";
        }
        
        if($this->ctime){
          $condition = $condition." and ctime>='".$this->ctime."'";
        }

        if($this->ctimeend){
          $condition = $condition." and ctime<='".$this->ctimeend." 23:59:59'";
        }

        if($name){
           $user_id = $ModelUser->where("name='".$name."'")->getField('id');
           if($user_id){
              $condition = $condition." and user_id='".$user_id."'";
           }
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
            $condition = " id=".$list[$i]['user_id'];
            $listTemp = $ModelUser->where($condition)->select();
            if($listTemp){
              $list[$i]['user_name'] = $listTemp[0]['name'];
              $list[$i]['phone_num'] = $listTemp[0]['phone_num'];
            }

            $condition = " id=".$list[$i]['ref_user_id'];
            $listTemp = $ModelUser->where($condition)->select();
            if($listTemp){
              $list[$i]['ref_user_name'] = $listTemp[0]['name'];
            }else{
              $list[$i]['ref_user_name'] = "";  
            }
          }

        }else{
          $code = -1;
          $msg = "sql failed";
        }


        //关注金额
        $price['follow'] = $this->getFollowPrice();

        //平台收入
        // $price['Platform'] = $this->getPlatformPrice();
        $price['platform'] = $price['follow']*0.2;

        //充值收款
        $price['recharge'] = $this->getRechargePrice();

        //奖励合计
        $price['sysreward'] = $this->getSysrewardPrice();

        //提款申请
        $price['drawmoney'] = $this->getDrawmoneyPrice();

        //平台用户余额
        $price['sysuserbalance'] = $this->getSysuserbalancePrice();


        if($is_allow == "1"){
            $this->ajaxOutput(0, $msg, array('count'=>$allnum, 'price'=>$price ,'list'=>$list));
        }else{
            $this->ajaxOutput(20402, 'user limited', array('count'=>$allnum, 'price'=>$price ,'list'=>Array()));
        }
    }

    public function getFollowPrice () {
        $Model = M('Order');
        $condition = " type=3 and state=40 and ctime>='".$this->ctime."' and ctime<='".$this->ctimeend." 23:59:59'";
        $price = $Model->where($condition)->sum('price');
        if (!$price) {
            $price = 0;
        }
        return $price;
    }

    public function getPlatformPrice () {
        $Model = M('Order');
        $condition = " type=3 and state=40 and ctime>='".$this->ctime."' and ctime<='".$this->ctimeend." 23:59:59'";
        $price = 0.2*$Model->where($condition)->sum('price');
        if (!$price) {
            $price = 0;
        }
        return $price;
    }

    public function getRechargePrice () {
        $Model = M('Order');
        $condition = " type=1 and state>=20 and ctime>='".$this->ctime."' and ctime<='".$this->ctimeend." 23:59:59'";
        $price = $Model->where($condition)->sum('price');
        if (!$price) {
            $price = 0;
        }
        return $price;
    }


    public function getSysrewardPrice () {
        $Model = M('Order');
        $condition = " type in ('5','6','7') and state=40 and ctime>='".$this->ctime."' and ctime<='".$this->ctimeend." 23:59:59'";
        $price = $Model->where($condition)->sum('price');
        if (!$price) {
            $price = 0;
        }
        return $price;
    }

    public function getDrawmoneyPrice () {
        $Model = M('Order');
        $condition = " type=2 and ctime>='".$this->ctime."' and ctime<='".$this->ctimeend." 23:59:59'";
        $price = $Model->where($condition)->sum('price');
        if (!$price) {
            $price = 0;
        }
        return $price;
    }


    public function getSysuserbalancePrice () {
        $Model = M('User');
        $condition = "1=1";
        $price = $Model->where($condition)->sum('fund');
        if (!$price) {
            $price = 0;
        }
        return $price;
    }



    //搜索提款记录
    public function searchUserFundInfo() {
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


        $Model = M('Order');
        $ModelUser = M('User');

        $this->ctime = I('param.ctime',date('Y-m-d'));
        $this->ctimeend = I('param.ctimeend',date('Y-m-d'));
        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        
        $name = I('param.title',-1);
        $this->condition = "1=1";
        if($name != -1){
            $this->condition = $this->condition." and name='".$name."'";
            //$this->condition = $this->condition." and name like '%".$title."%'";
        }

        $user_id = M('User')->where("name='".$name."'")->getField('id');
        if($user_id){
          $condition = $condition." and user_id='".$user_id."'";
        }

        if($this->ctime){
          $CTime = " and ctime>='".$this->ctime."'";
        }

        if($this->ctimeend){
          $CETime = " and ctime<='".$this->ctimeend." 23:59:59'";
        }

        $allnum = 0;
        $list = $ModelUser->where($this->condition)->select();
        if($list){
            $code = 0;
            $msg = "suc";
            
            $num = count($list);
            $tag = 0;
            for($i=0; $i<$num; $i++){              
              $this->condition = " user_id='".$list[$i]['id']."'".$CTime.$CETime;
              $allnum += $Model->where($this->condition)->count();
              $listTemp = $Model->where($this->condition)->page($this->curpage, $this->pagenum)->select();
              for($j=0; $j<count($listTemp); $j++){
                $listTemp[$j]['user_name'] = $list[$i]['name'];
                $listTemp[$i]['phone_num'] = $list[$i]['phone_num'];
                $listData[$tag] = $listTemp[$j];
                $tag = $tag + 1;
              }
            }

            if ($list == null) {
                $listData = array();
            }
        }else{
            $code = 10001;
            $msg = "search user fail";
            $list = array();
            $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
        }

        $this->ajaxOutput($code, $msg, array('count'=>$allnum, 'list'=>$listData));
    } 

    //充值管理页面
    public function getOrderDetailList(){
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

        $platform = I('param.platform',-1);
        $available = I('param.available',-1);
        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $this->ctime = I('param.ctime',date('Y-m-d'));
        $this->ctimeend = I('param.ctimeend',date('Y-m-d'));

        $Model = M('Order');
        $ModelUser = M('User');
        $ModelAdmin = M('Admin');
        
        $pageName = I('param.pagename',-1);
        if($pageName != "-1"){
            $is_allow = pageAuthority($pageName, $res_isLogin);
        }

        if($platform == 1){
            $condition = "1=1 and (type='5' or type='6' or type='7')";
        }else if($platform == 2 || $platform == 3){
            $condition = "1=1 and type='1' ";
        }else{
            $condition = "1=1 and type in ('1','5','6','7')";
        }

        if($this->ctime){
          $condition = $condition." and ctime>='".$this->ctime."'";
        }

        if($this->ctimeend){
          $condition = $condition." and ctime<='".$this->ctimeend." 23:59:59'";
        }

        if($platform && $platform!="-1"){
          $condition = $condition." and platform='".$platform."'";
        }
        
        if($available == "1"){
          $condition = $condition." and state>='20'";
        }else if ($available == "2"){
          $condition = $condition." and state<'20'";
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
            $condition = " id=".$list[$i]['user_id'];
            $listTemp = $ModelUser->where($condition)->select();
            if($listTemp){
              $list[$i]['user_name'] = $listTemp[0]['name'];
              $list[$i]['phone_num'] = $listTemp[0]['phone_num'];
            }

            if ($list[$i]['type'] <= 4) {
                $condition = " id=".$list[$i]['ref_user_id'];
                $listTemp = $ModelUser->where($condition)->select();
                if($listTemp){
                  $list[$i]['ref_user_name'] = $listTemp[0]['name'];
                }else{
                  $list[$i]['ref_user_name'] = "";  
                }
            }else if ($list[$i]['type'] > 4) {
               $condition = " user_id=".$list[$i]['ref_user_id'];
                $listTemp = $ModelAdmin->where($condition)->select();
                if($listTemp){
                  $list[$i]['ref_user_name'] = $listTemp[0]['chinaname'];
                }else{
                  $list[$i]['ref_user_name'] = "";  
                } 
            }
            

            
          }

        }else{
          $code = -1;
          $msg = "sql failed";
        }

         //支付宝充值金额
        $price['alipay'] = $this->getAlipayPrice();

         //快钱充值金额
        $price['bill'] = $this->get99billPrice();

         //管理后台充值金额
        $price['admin'] = $this->getAdminPrice();

        if($is_allow == "1"){
            $this->ajaxOutput(0, $msg, array('count'=>$allnum, 'price'=>$price, 'list'=>$list));
        }else{
            $this->ajaxOutput(20402, 'user limited', array('count'=>$allnum, 'price'=>$price, 'list'=>Array()));
        }
    }

    public function getAlipayPrice () {
        $Model = M('Order');
        $condition = " platform=2 and type=1 and state>=20 and ctime>='".$this->ctime."' and ctime<='".$this->ctimeend." 23:59:59'";
        $price = $Model->where($condition)->sum('price');
        if (!$price) {
            $price = 0;
        }
        return $price;
    }

    public function get99billPrice () {
        $Model = M('Order');
        $condition = " platform=3 and type=1 and state>=20 and ctime>='".$this->ctime."' and ctime<='".$this->ctimeend." 23:59:59'";
        $price = $Model->where($condition)->sum('price');
        if (!$price) {
            $price = 0;
        }
        return $price;
    }

    public function getAdminPrice () {
        $Model = M('Order');
        $condition = " platform=1 and type in ('1','5','6','7') and state>=20 and ctime>='".$this->ctime."' and ctime<='".$this->ctimeend." 23:59:59'";
        //echo $condition;

        $price = $Model->where($condition)->sum('price');
        //echo "price:".$price.",";
        if (!$price) {
            $price = 0;
        }
        return $price;
    }

   

   //支付订单明细页面搜索
    public function searchOrderDetailList() {
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


        $Model = M('Order');
        $ModelUser = M('User');

        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $ctime = I('param.ctime',date('Y-m-d'));
        $ctimeend = I('param.ctimeend',date('Y-m-d'));

        $title = I('param.title',-1);

        $listData = array();
        $this->condition = "1=1";
        if($title != -1){
            $this->condition = $this->condition." and pay_code='".$title."'";
        }

        if($ctime){
          $this->condition = $this->condition." and ctime>='".$ctime."'";
        }

        if($ctimeend){
          $this->condition = $this->condition." and ctime<='".$ctimeend." 23:59:59'";
        }

        $list = $Model->where($this->condition)->select();
        if($list){
            $code = 0;
            $msg = "suc";
            //按用户名查找
            $this->condition = "1=1";
            if($title != -1){
                $this->condition = $this->condition." and id='".$list[0]['user_id']."'";
            }
            $listTemp = $ModelUser->where($this->condition)->select();
            if ($list) {
                $list[0]['user_name'] = $listTemp[0]['name'];
            }

            $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
        }else if($list == null){
            $code = 0;
            $msg = "suc";

            //按用户名查找
            $this->condition = "1=1";
            if($title != -1){
                $this->condition = $this->condition." and name like '%".$title."%'";
            }
            $list = $ModelUser->where($this->condition)->select();
            if ($list) {
                $code = 0;
                $msg = "suc";
                for($i=0; $i<$num; $i++){  
                    $this->condition = "user_id='".$list[$i]['id']."' and ctime>='".$ctime."' and ctime<='".$ctimeend." 23:59:59'";
                    $listTemp = $Model->where($this->condition)->select();
                    for($j=0; $j<count($listTemp); $j++){
                        $listTemp[$j]['user_name'] = $list[$i]['name'];
                        $listTemp[$j]['phone_num'] = $list[$i]['phone_num'];
                        $listData[$tag] = $listTemp[$j];
                        $tag = $tag + 1;
                    }
                }
            }else if($list == null){
                $code = 0;
                $msg = "suc";
                $listData = array();
            }else{
                $code = -1;
                $msg = "search fail";
                $listData = array();
            }

            $this->ajaxOutput($code, $msg, array('count'=>count($listData), 'list'=>$listData));
        }else{
            $code = -1;
            $msg = "search fail";
            $this->ajaxOutput($code, $msg, array('count'=>0, 'list'=>array()));
        }
    } 

}
?>
