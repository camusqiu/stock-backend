<?php
namespace Home\Controller;
use Think\Controller;
import('Org.Util.Date');
class FundManagementController extends CommonController {
    
    //交易编号
    public function makeOrderCode($id){
        $id_str = str_pad(substr($id, -8), 8, "0", STR_PAD_LEFT);
        //唯一订单号 = 6位年月日+8位截断补齐订单自增ID+3位随机数
        return date('ymd', NOW_TIME).$id_str.str_pad(rand(1,999), 3, '0', STR_PAD_LEFT);
    }


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


        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $this->ctime = I('param.ctime',date('Y-m-d'));
        $this->ctimeend = I('param.ctimeend',date('Y-m-d'));
        $type = I('param.type',-1);

        $Model = M('FundManagement');
        $ModelAdmin = M('Admin');
        $ModelUser = M('User');
        $ModelOrder = M('Order');


        $condition = "1=1 and (type='5' or type='6' or type='7')";
        if($this->ctime){
          $condition = $condition." and ctime>='".$this->ctime."'";
        }

        if($this->ctimeend){
          $condition = $condition." and ctime<='".$this->ctimeend." 23:59:59'";
        }

        if($type && $type != -1){
          $condition = $condition." and type='".$type."'";
        }


        $allnum = $ModelOrder->where($condition)->count();

        $list = $ModelOrder->where($condition)->order('ctime desc')->page($this->curpage, $this->pagenum)->select();

        if($list || $list == null){
          $code = 0;
          $msg = "suc";
          if($list == null){
            $list = array();
          }

          $num = count($list);


          for($i=0; $i<$num; $i++){
            $condition = " id=".$list[$i]['user_id'];
            $listTempUser= $ModelUser->where($condition)->select();
            if($listTempUser){
              $list[$i]['user_name'] = $listTempUser[0]['name'];
            }

            $condition = " user_id=".$list[$i]['ref_user_id'];
            $listTempAdmin = $ModelAdmin->where($condition)->select();
            if($listTempAdmin){
              $list[$i]['admin_name'] = $listTempAdmin[0]['chinaname'];
            }

            // $conditionOrder = " code=".$list[$i]['trade_id'];
            // $listTempOrder = $ModelOrder->where($conditionOrder)->select();
            // if($listTempOrder){
            //   $list[$i]['remark'] = $listTempOrder[0]['remark'];
            //   $list[$i]['state'] = $listTempOrder[0]['state'];
            //   $list[$i]['price'] = $listTempOrder[0]['price'];
            // }

          }

        }else{
          $code = -1;
          $msg = "sql failed";
        }

        //今日付款
        $price['today'] = $this->getTodayPrice();

        //涨跌推荐奖励
        $price['recom'] = $this->getRecomPrice();

        //加精支付
        $price['essential'] = $this->getEssentialPrice();

        //其他
        $price['other'] = $this->getOtherPrice();


        $this->ajaxOutput(0, $msg, array('count'=>$allnum, 'price'=>$price, 'list'=>$list));
       
    }

    public function getTodayPrice () {
        $Model = M('Order');
        $condition = " type in ('5','6','7') and state>=20 and ctime>='".$this->ctime."' and ctime<='".$this->ctimeend." 23:59:59'";
        $price = $Model->where($condition)->sum('price');
        if (!$price) {
            $price = 0;
        }
        return $price;
    }


    public function getRecomPrice () {
        $Model = M('Order');
        $condition = " type=5 and state=40 and ctime>='".$this->ctime."' and ctime<='".$this->ctimeend." 23:59:59'";
        $price = $Model->where($condition)->sum('price');
        if (!$price) {
            $price = 0;
        }
        return $price;
    }

    public function getEssentialPrice () {
        $Model = M('Order');
        $condition = " type=6 and state=40 and ctime>='".$this->ctime."' and ctime<='".$this->ctimeend." 23:59:59'";
        $price = $Model->where($condition)->sum('price');
        if (!$price) {
            $price = 0;
        }
        return $price;
    }

    public function getOtherPrice () {
        $Model = M('Order');
        $condition = " type=7 and state=40 and ctime>='".$this->ctime."' and ctime<='".$this->ctimeend." 23:59:59'";
        $price = $Model->where($condition)->sum('price');
        if (!$price) {
            $price = 0;
        }
        return $price;
    }


    //提交活动订单
    public function submitOrderList(){
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

        $this->phone_num = I('param.phone_num',"");
        $this->price = I('param.price',-1);
        $this->type = I('param.type',-1);
        $this->remark = I('param.remark',"");
        $this->admin_id = $res_isLogin;
        $this->ip = get_client_ip(0, true);


        $Model = M('FundManagement');
        $ModelAdmin = M('Admin');
        $ModelUser = M('User');
        $ModelOrder = M('Order');

        //去重
        $phone_num_arrayTemp = explode("/", $this->phone_num);
        $phone_num = array_unique($phone_num_arrayTemp);
        for ($i=0, $j=0; $j < count($phone_num); $i++) { 
            if($phone_num[$i]){
                $phone_num_array[$j] = $phone_num[$i];
                $j++;
            }
        }
        
        $condition = "1=1";
        if($num = count($phone_num_array)){
            if ($num == 1) {
              $condition = $condition." and phone_num='".$phone_num_array[0]."'";
            }else{
                for ($i=0; $i < $num; $i++) { 
                    if($i == 0){
                        $condition = $condition." and phone_num in (".$phone_num_array[$i].",";
                    }else if ($i + 1 == $num){
                        $condition = $condition.$phone_num_array[$i].")";
                    }else{
                        $condition = $condition.$phone_num_array[$i].",";
                    }
                }
            }

             $listTempUser= $ModelUser->where($condition)->select();

             if(count($listTempUser) == $num){
                //所有用户存在，order表写订单
                for ($i=0; $i < $num; $i++) { 
                    $listData[$i]['ip'] = $this->ip;
                    $listData[$i]['price'] = $this->price;
                    $listData[$i]['remark'] = $this->remark;
                    $listData[$i]['type'] = $this->type; //后台添加   
                    $listData[$i]['price'] = $this->price;
                    $listData[$i]['state'] = 10; //创建
                    $listData[$i]['user_id'] = $listTempUser[$i]['id'];
                    $listData[$i]['ref_user_id'] = $this->admin_id;

                    // $ui = getUinfo($listData[$i]['user_id'], array('fund'=>1), 'must');
                    // if($ui['id']){
                    //     $listData[$i]['balance'] = $ui['fund'];
                    // }else{
                    //     $listData[$i]['balance'] = 0;
                    // }
                    // $listData[$i]['balance'] = $ui['fund'];
                    //balance初始化为0， order状态完成后修改balance值
                    $listData[$i]['balance'] = 0;
                    $listData[$i]['num'] = 1; 
                
                    $list = $ModelOrder->add($listData[$i]);
                    if($list){
                        $code = 0;
                        $msg = "创建成功";
                        $addNum = count($list);
                        for ($j=0; $j < $addNum; $j++) { 
                            $id = $list;
                            $ModelOrder->where(" id='".$id."' ")->save(array('code'=>$this->makeOrderCode($id)));
                        }
                    }else{
                        $code = -1;
                        $msg = "创建失败"; 
                    }

                    $LData['table_name'] = "Order";
                    $LData['type'] = 2;
                    $LData['admin_id'] = $this->isLogin();
                    $LData['code'] = $code;
                    $LData['msg'] = "创建活动订单:订单号 ".$id." user_id ".$listData[$i]['user_id']." price ".$listData[$i]['price']." remark ".$listData[$i]['remark'].$msg;
                    aLog($LData); 
                }

             }else{
                $num = count($listTempUser);
                if ($num == 0) {
                    $code = -1;
                    $msg = "添加的用户不存在";
                }
                
                for ($i=0; $i < $num; $i++) { 
                    $condition = " id='".$listTempUser[$i]['id']."'";
                    $listTempUser= $ModelUser->where($condition)->select();
                    if($listTempUser == null){
                        $this->ajaxOutput(-1, $listTempUser[$i]['id']."用户不存在", array('list'=>array()));
                    }
                }
            }

        }else{
          $code = -1;
          $msg = "sql failed";
        }

        $this->ajaxOutput($code, $msg, array('count'=>$num, 'list'=>$list));
       
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

        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $title = I('param.title',-1);
        $this->condition = "1=1";
        if($title != -1){
            $this->condition = $this->condition." and name like '%".$title."%'";
        }

        $list = $ModelUser->where($this->condition)->select();
        if($list){
            $code = 0;
            $msg = "suc";
            $num = count($list);
            $tag = 0;
            for($i=0; $i<$num; $i++){
              $this->condition = " user_id='".$list[$i]['id']."'";
              $listTemp = $Model->where($this->condition)->select();
              for($j=0; $j<count($listTemp); $j++){
                $listTemp[$j]['user_name'] = $list[$i]['name'];
                $listData[$tag] = $listTemp[$j];
                $tag = $tag + 1;
              }
            }
        }else{
            $code = 10001;
            $msg = "search user fail";
            $list = array();
        }

        $this->ajaxOutput($code, $msg, array('count'=>count($listData), 'list'=>$listData));
    }    


    public function delOrderList () {
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

        $this->id = I('param.id',"");

        $ModelAdmin = M('Admin');
        $ModelUser = M('User');
        $ModelOrder = M('Order');

        if(!$this->id){
            $this->ajaxOutput(10005, '缺少参数', array('list'=>Array()));
        }

        $itemData['state'] = 0;
        $itemData['ref_user_id'] = $res_isLogin;
        $list = $ModelOrder->where(" id='".$this->id."'")->save($itemData);

        $LData['table_name'] = "Order";
        $LData['type'] = 3;
        $LData['admin_id'] = $this->isLogin();
        $LData['code'] = 0;
        if ($list) {
            $LData['msg'] = "付款管理页面-删除订单:".$this->id." 删除成功";
            aLog($LData);
            $this->ajaxOutput(0, '删除成功', array('count'=>count($list), 'list'=>$list));
        }else{
            $LData['msg'] = "付款管理页面-删除订单:".$this->id." 删除失败";
            aLog($LData);
            $this->ajaxOutput(-1, '删除失败', array('count'=>count($list), 'list'=>array()));
        }
    }


    public function auditOrderList() {
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

        $this->id = I('param.id',"");

        $ModelAdmin = M('Admin');
        $ModelUser = M('User');
        $ModelOrder = M('Order');


        if(!$this->id){
            $this->ajaxOutput(10005, '缺少参数', array('list'=>Array()));
        }

        $list = $ModelOrder->where(" id='".$this->id."'")->select();
        if(!$list){
            $this->ajaxOutput(-1, '该条记录并不存在', array('list'=>Array()));
        }

        if ($list[0]['state'] != 10 ) {
            if ($list[0]['state'] == 0) {
                $msg = "该条订单已被删除";
            }else if ($list[0]['state'] == 40){
                $msg = "该条订单已经完成";
            }else{
                $msg = "该条订单状态异常";
            }
            $this->ajaxOutput(-1, $msg, array('list'=>Array()));
        }

        $ModelOrder->startTrans();           //开启事务 读写都会在主库中操作
        $user_id = $list[0]['user_id'];
        $price = $list[0]['price'];
        $remark = $list[0]['remark'];
        $type = $list[0]['type'];

        
        $ui = getUinfo($user_id, array('fund'=>1), 'must');
        if(!$ui){
            $this->ajaxOutput(10001, '当前用户不存在', array('list'=>Array()));
        }

        //更新用户账号余额
        $balance = number_format($ui['fund']+$price, 2);
        $reInc = $ModelUser->where(array('id'=>$user_id))->setInc('fund', $price);

        $list = $ModelUser->where(" id='".$user_id."'")->find();

        $ui = getUinfo($user_id, array('fund'=>1), 'must');
       
        if($ui['fund'] != $balance){
           $ModelOrder->rollback();
           $ModelOrder->commit();
           $this->ajaxOutput(-1, '数据异常，操作失败', array('list'=>Array()));
       }
        
        //更新订单状态和订单中当时余额和状态

        $LData['table_name'] = "Order";
        $LData['type'] = 1;
        $LData['admin_id'] = $this->isLogin();
        if($reInc === false){
            //添加/减少用户资金操作失败
            $ModelOrder->rollback();
            $ModelOrder->commit();

            $LData['table_name'] = "User";
            $LData['code'] = -1;
            $LData['msg'] = "付款管理页面-通过订单:".$this->id." 更新订单余额失败(reInc===false)";
            aLog($LData);

            $this->ajaxOutput(-1, '添加/减少资金操作失败!', array('list'=>Array()));
        }else{
            $itemData['state'] = 40;
            $itemData['balance'] = $balance;
            $itemData['ref_user_id'] = $res_isLogin;
            $list = $ModelOrder->where(" id='".$this->id."'")->save($itemData);
            if ($list === false) {
                //资金,状态修改失败
                $ModelOrder->rollback();
                $ModelOrder->commit();

                
                $LData['code'] = -1;
                $LData['msg'] = "付款管理页面-通过订单:".$this->id." 资金状态修改失败";
                aLog($LData);

                $this->ajaxOutput(0, '修改订单失败~', array('list'=>$Array()));
            }
            
            // 发送站内信
            $to_user_id = $user_id;
            $user_id = $res_isLogin;
            $this->sendLetters($type, $remark, $user_id, $to_user_id);

            $ModelOrder->commit();

            $LData['code'] = 0;
            $LData['msg'] = "付款管理页面-通过订单:".$this->id." 成功";
            aLog($LData);

            $this->ajaxOutput(0, '添加/减少资金操作成功~', array('count'=>count($list), 'list'=>$list));
        }
    }

    public function sendLetters ($type, $remark, $user_id, $to_user_id) {
        $ModelOrder = M('Order');
        $LetterInfo = D('LetterInfo');
        $dataLetter['opt'] = "";
        

        if ($type == 5) {
            $dataLetter['title'] = "涨跌推荐奖励";
        }else if ($type == 6) {
            $dataLetter['title'] = "吧贴精华奖励";
        }else if ($type == 7) {
            $dataLetter['title'] = $remark;
        }
        $dataLetter['content'] = $remark;

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
                    $ModelOrder->rollback();

                    //插表失败，写日志
                    $LData['table_name'] = "letter";
                    $LData['type'] = 2;
                    $LData['admin_id'] = $user_id;
                    $LData['code'] = $code;
                    $LData['msg'] = $msg;
                    //写日志
                    $aLog($LData);
                        
                    $this->ajaxOutput($code, $msg, array('count'=>0, 'list'=>array()));
                }
            }
        }
    }


    // public function writeToFile(){
    //     $filename = '/data/www/admin/App/Home/Log/FundManagement.log';
    //     $fp = fopen($filename, "a+");//文件被清空后再写入
    //     if($fp){ 
    //             $flag=fwrite($fp,"Hello World"); 
    //             var_dump($flag);
    //             if(!$flag) 
    //             { 
    //                 echo "写入文件失败<br>"; 
    //             } 
            
    //     }else{ 
    //       echo "打开文件失败"; 
    //     } 
    //     fclose($fp); 
    // }
}
?>
