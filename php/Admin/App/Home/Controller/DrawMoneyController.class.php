<?php
namespace Home\Controller;
use Think\Controller;
import('Org.Util.Date');
class DrawMoneyController extends CommonController {
    
    private $ctime = "";
    private $ctimeend = "";

    //根据提款记录的状态筛选
    public function getDrawMoneyList(){
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
        $this->ctime = I('param.ctime',date('Y-m-d'));
        $this->ctimeend = I('param.ctimeend',date('Y-m-d'));

        $Model = M('DrawMoney');
        $ModelUser = M('User');

        $condition = "1=1";
        if($state != "-1"){
          $condition = $condition." and state='".$state."'";
        }

        if($this->ctime){
          $condition = $condition." and ctime>='".$this->ctime."'";
        }

        if($this->ctimeend){
          $condition = $condition." and ctime<='".$this->ctimeend." 23:59:59'";
        }

        $allnum = $Model->where($condition)->count();

        $list = $Model->where($condition)->order('ctime desc')->page($this->curpage, $this->pagenum)->select();
        if($list || $list == null){
          $code = 0;
          $msg = "suc";
          if($list == null){
            $list = array();
          }else{
            $num = count($list);
            for ($i=0; $i < $num; $i++) { 
                $Luname = $ModelUser->where(" id='".$list[$i]['user_id']."'")->select();
                $list[$i]['user_name'] = $Luname[0]['name'];
                $list[$i]['phone_num'] = $Luname[0]['phone_num'];
            }
          }

        }else{
          $code = -1;
          $msg = "sql failed";
        }


        //全部、未审核、已审核（待汇）、拒绝、已汇出
        $price['all'] = $this->getAllDrawPrice();
        $price['unaudit'] = $this->getUnauditPrice();
        $price['audit'] = $this->getAuditPrice();
        $price['refuse'] = $this->getRefusePrice();
        $price['remit'] = $this->getRemitPrice();

        $this->ajaxOutput($code, $msg, array('count'=>$allnum, 'price'=>$price , 'list'=>$list));
    }


    public function getAllDrawPrice () {
        $Model = M('DrawMoney');
        $condition = " ctime>='".$this->ctime."' and ctime<='".$this->ctimeend." 23:59:59'";
        $price = $Model->where($condition)->sum('sum_money');
        if (!$price) {
            $price = 0;
        }
        return $price;
    }

    public function getUnauditPrice () {
        $Model = M('DrawMoney');
        $condition = " state=0 and ctime>='".$this->ctime."' and ctime<='".$this->ctimeend." 23:59:59'";
        $price = $Model->where($condition)->sum('sum_money');
        if (!$price) {
            $price = 0;
        }
        return $price;
    }

    public function getAuditPrice () {
        $Model = M('DrawMoney');
        $condition = " state=1 and ctime>='".$this->ctime."' and ctime<='".$this->ctimeend." 23:59:59'";
        $price = $Model->where($condition)->sum('sum_money');
        if (!$price) {
            $price = 0;
        }
        return $price;
    }

    public function getRefusePrice () {
        $Model = M('DrawMoney');
        $condition = " state=2 and ctime>='".$this->ctime."' and ctime<='".$this->ctimeend." 23:59:59'";
        $price = $Model->where($condition)->sum('sum_money');
        if (!$price) {
            $price = 0;
        }
        return $price;
    }

    public function getRemitPrice () {
        $Model = M('DrawMoney');
        $condition = " state=5 and ctime>='".$this->ctime."' and ctime<='".$this->ctimeend." 23:59:59'";
        $price = $Model->where($condition)->sum('sum_money');
        if (!$price) {
            $price = 0;
        }
        return $price;
    }



    //搜索提款记录
    public function searchUserDrawMoneyInfo() {
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

        
        $Model = M('DrawMoney');

        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $title = I('param.title',-1);
        $this->condition = "1=1";
        if($title != -1){
            $this->condition = $this->condition." and user_name like '%".$title."%'";
        }

        $list = $Model->where($this->condition)->select();
        if($list){
            $code = 0;
            $msg = "suc";
        }else{
            $code = 10001;
            $msg = "search user fail";
            $list = array();
        }

        $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
    }    
 

        //审核提款记录
    public function auditDrawMoneyRecord(){
        $Model = M('DrawMoney');
        $draw = D('DrawMoney');
        $LetterInfo = D('LetterInfo');

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

        $state = I('param.state',-1, "intval");
        $order_id = I('param.id',-1, "intval");
        $order_code = I('param.order_code',-1, "htmlspecialchars");

        if ($state == 3) {
            $is_allow = drawMoneyAuthority($res_isLogin);
            if($is_allow != "1"){
                $this->ajaxOutput(20402, "limit ", array('list'=>Array()));
            }
        }

        $list = $Model->where("order_id='".$order_id."' and order_code='".$order_code."'")->find();

        if($list){
            $price = $list['sum_money'];
            $remark = "审核不通过";
            $to_user_id = $list['user_id'];
        }

        $data['admin_id'] = $res_isLogin;
        $user_id = $res_isLogin;
        
        if($order_id){
            $data['order_id'] = $order_id;
        }
        if($order_code){
            $data['order_code'] = $order_code;
        }
        if($state){
            $data['state'] = $state;
        }
        if($price){
            $data['price'] = $price;
        }
        if($remark){
            $data['remark'] = $remark;
        }


        $dataTemp = $data;
        //order表state: 0、删除，5，审核不通过，10、创建，15，审核通过，20、已付款，30、已发货，40、已完成
        //drawmoney表state: 0:未审核1:审核成功2:审核不通过3:扣款成功4:扣款失败5:汇款成功6:汇款失败
        if($state == 1){
            $data['state'] = 20;
            //必须为未审核状态
            if ($list['state'] != 0) {
                $msg = "不是未审核状态，无法通过审核";
                $this->ajaxOutput(-1, $msg, array('count'=>0, 'list'=>array()));
            }
        }else if($state == 2){
            $data['state'] = 5;
            //必须为未审核状态
            if ($list['state'] != 0) {
                $msg = "不是未审核状态，不能设置审核不通过";
                $this->ajaxOutput(-1, $msg, array('count'=>0, 'list'=>array()));
            }
        }else if($state == 3){
            $data['state'] = 40;

            //检测是否审核通过
            if ($list['state'] != 1) {
                $msg = "不是审核成功状态，无法汇款";
                $this->ajaxOutput(-1, $msg, array('count'=>0, 'list'=>array()));
            }
        }

        $msg = $draw->changeDrawOrderState($data);
        if($msg == false){
            $msg = $draw->getError();
            $this->ajaxOutput(-1, $msg, array('count'=>0, 'list'=>array()));
        }
        
        $this->changeDrawMoneyDB($dataTemp);

        // 发送站内信
        $this->sendLetters($state, $user_id, $to_user_id);

        $this->ajaxOutput(0, "suc", array('count'=>1, 'list'=>$list));
    }


    public function sendLetters ($state, $user_id, $to_user_id) {

        $LetterInfo = D('LetterInfo');
        $dataLetter['opt'] = "";
        
        if ($state == 1) {
            $dataLetter['title'] = "提款通知";
            $dataLetter['content'] = "您的提款申请已经通过审核!";
        }else if ($state == 2) {
            $dataLetter['title'] = "提款通知";
            $dataLetter['content'] = "您的提款申请没有通过审核，请@领盈小秘书 和我们联系!";
        }else if ($state == 3) {
            $dataLetter['title'] = "提款通知";
            $dataLetter['content'] = "您的提款申请已经完成, 提款已汇到您的银行账户,请注意查收！";
        }

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

                    //插表失败，写日志
                    $data['table_name'] = "letter";
                    $data['type'] = 2;
                    $data['admin_id'] = $user_id;
                    $data['code'] = $code;
                    $data['msg'] = $msg;
                    //写日志
                    $aLog($data);

                    $this->ajaxOutput($code, $msg, array('count'=>0, 'list'=>array()));
                }
            }
        }
    }


    public function changeDrawMoneyDB ($data) {
        $Model = M('DrawMoney');
        $condition = " order_id='".$data['order_id']."'";
        $list = $Model->where($condition)->select();

        $LData['table_name'] = "draw_money";
        $LData['type'] = 1;
        $LData['admin_id'] = $this->isLogin();

        if ($list) {
            //drawmoney表状态修改
            $itemstate['state'] = ($data['state']==3)?5:$data['state'];
            $itemstate['admin_id'] = $data['admin_id'];
            $Lstate = $Model->where($condition)->save($itemstate);
            if ($Lstate) {
                $msg = "操作成功";
                $code = 0;

                $LData['code'] = $code;
                $LData['msg'] = "drawmoney: [提款管理页面: order_id:".$data['order_id']." 修改为state:".$itemstate['state']." 修改成功]";
                aLog($LData);
               // $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
            }else{
                $code = -1;
                $msg = "drawmoney表修改失败";

                $LData['code'] = $code;
                $LData['msg'] = "drawmoney: [提款管理页面: order_id:".$data['order_id']." 修改为state:".$itemstate['state']." 状态修改失败]";
                aLog($LData);
                $this->ajaxOutput($code, $msg, array('count'=>0, 'list'=>array()));
            }  
        }else{
            $code = -1;
            $msg = "drawmoney表查找失败";

            $LData['type'] = 0;
            $LData['code'] = $code;
            $LData['msg'] = "drawmoney: [提款管理页面: order_id:".$data['order_id']." 查找失败]";
            aLog($LData);
            $this->ajaxOutput($code, $msg, array('count'=>0, 'list'=>array()));
        }
    }

}
?>
