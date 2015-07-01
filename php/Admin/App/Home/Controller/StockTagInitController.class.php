<?php
namespace Home\Controller;
use Home\Controller\CommonController;

/**
 * “用户”控制器类。
 */ 
class StockTagInitController extends CommonController {
    /**
     * 登录。
     */

    private $time = ''; 

    public function getStockTag() {
        $codes = I('codes', -1);
        if($codes){
            $arrayCode = explode("/", $codes);
            $size = count($arrayCode);

            $stockTagModel = M('StockTagInit');
            for($i = 0; $i < $size; $i++){
                if($i == 0){
                    $condition = "code='".$arrayCode[$i]."'";
                }else{
                    $condition = $condition."or code='".$arrayCode[$i]."'";    
                } 
            }

            
            $list = $stockTagModel->where($condition)->select();
            if($list || $list == null){
                $code = 0;
                $msg = "suc";
                if($list == null){
                    $list = Array();
                }
                // if($url && $url >= 0){
                //     $arrayUrl = explode(";", $url);
                // }
            }else{
                $list = Array();
                $code = 20401;
                $msg = $codes." is not set inittag";
            }
        }
        $this->ajaxOutput($code, $msg, array('list'=>$list));    
    }


    public function getStockTagName() {
        $codes = I('codes', -1);
        if($codes){
            $stockTagModel = M('StockTagInit');
            $TagModel = M('Tag');
            $condition = "code='".$codes."'";
               
            $list = $stockTagModel->where($condition)->select();
            if($list || $list == null){
                $code = 0;
                $msg = "suc";
                if($list == null){
                    $list = Array();
                }

                $arrayTagHY = explode("_", $list[0]['hy_tag']);
                $num = count($arrayTagHY);
                //var_dump($arrayTagHY);
                for ($i=0; $i < $num; $i++) { 
                    $condition = " id='".$arrayTagHY[$i]."'";
                    $listTag = $TagModel->where($condition)->select();
                    $list[0]['hy_tag_name'] = $list[0]['hy_tag_name'].$listTag[0]['name']."_";
                }
                //var_dump($list[0]);
                $list[0]['hy_tag_name'] = substr($list[0]['hy_tag_name'], 0, strlen($list[0]['hy_tag_name']) - 1);


                $arrayTagGN = explode("_", $list[0]['gn_tag']);
                $num = count($arrayTagGN);
                for ($i=0; $i < $num; $i++) { 
                    $condition = " id='".$arrayTagGN[$i]."'";
                    $listTag = $TagModel->where($condition)->select();
                    $list[0]['gn_tag_name'] = $list[0]['gn_tag_name'].$listTag[0]['name']."_";
                }
                $list[0]['gn_tag_name'] = substr($list[0]['gn_tag_name'], 0, strlen($list[0]['gn_tag_name']) - 1);

            }else{
                $list = Array();
                $code = 20401;
                $msg = $codes." is not set inittag";
            }
        }
        $this->ajaxOutput($code, $msg, array('list'=>$list));    
    }


    public function updateStockHYGNTag(){

        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $LData['table_name'] = "Tag";
        $LData['type'] = 1;
        $LData['admin_id'] = $res_isLogin;
        
        $id = I('id', -1);
        $ucode = I('code', -1);
        $strtag_id = I('strtag_id', -1);
        $hy = "";
        $gn = "";

        if ($id == -1) {
            $this->ajaxOutput(-1, "参数错误", array('list'=>$list)); 
        }

        if ($ucode == -1) {
            $this->ajaxOutput(-1, "参数错误", array('list'=>$list)); 
        }

        $code = getBarCode($ucode);

        if ($this->is_list($id) == "") {
            $this->ajaxOutput(-1, "该股票未上市或上市日期未知，请填写上市日期后再做操作!", array('list'=>$list)); 
        }

        $now = date("Y-m-d H:i:s");
        $day = date('Y-m-d');
        $tagModel = M('Tag');
        $stockTagModel = M('StockTagInit');
        $ModelTagCom = M('TagComponent');
        $listTagInit = $stockTagModel->where("code='".$ucode."'")->select();

        if ($listTagInit && $listTagInit != null && $listTagInit[0]['state'] == 3) {
            $this->ajaxOutput(-1, "该股票已经退市，操作无效!", array('list'=>$list)); 
        }

        //获取关联行业概念
        $arrayTag = explode("_", $strtag_id);
        for($i = 0; $i < count($arrayTag); $i++){

            $listTag = $tagModel->where("id='".$arrayTag[$i]."'")->select();
            //1为行业 2为概念
            if($listTag[0]['type'] == "1"){             
                $hy = $hy.$arrayTag[$i]."_";
            }else if($listTag[0]['type'] == "2"){   
                $gn = $gn.$arrayTag[$i]."_";
            }
        }
        //去掉尾部多余"_"
        $data['hy_tag'] = substr($hy, 0, -1);
        $data['gn_tag'] = substr($gn, 0, -1);
        $hy_isnull = 0;
        $gn_isnull = 0;
        if (!$data['hy_tag']) {
            $data['hy_tag'] = "";
            $hy_isnull = 1;
        }
        if (!$data['gn_tag']) {
            $data['gn_tag'] = "";
            $gn_isnull = 1;
        }

        //没有初始化化则新增(新增的股吧)
        if($listTagInit == null){
            $ecode = 0;
            $msg = "新增关联成功";    

            $data['code'] = $ucode;
            $listTagAdd = $stockTagModel->add($data);
            $LData['msg'] = "行业概念: code ".$ucode." init表新增股吧关联成功";
            aLog($LData);

            $dataCom['in_time'] = $this->is_list($id);

            $hy = explode("_", $data['hy_tag']);
            $hynum = count($hy);
            for ($i=0; $i < $hynum && $hy_isnull == 0; $i++) { 
                $dataCom['tag_id'] = $hy[$i];
                $dataCom['ucode'] = $ucode;
                $dataCom['code'] = $code;
                $listSaveCom = $ModelTagCom->add($dataCom);
                $LData['msg'] = "行业概念: ucode ".$ucode." com表新增股吧成分成功";
                aLog($LData);
            }

            $gn = explode("_", $data['gn_tag']);
            $gnnum = count($gn);
            for ($i=0; $i < $gnnum && $gn_isnull == 0; $i++) { 
                $dataCom['tag_id'] = $gn[$i];
                $dataCom['ucode'] = $ucode;
                $dataCom['code'] = $code;
                $listSaveCom = $ModelTagCom->add($dataCom);
                $LData['msg'] = "行业概念: ucode ".$ucode." com表新增股吧成分成功";
                aLog($LData);
            }
            
        }


        if($listTagInit || $listTagInit !== null){
            $ecode = 0;
            $msg = "修改关联成功";

            if (!$listTagInit[0]['hy_tag']) {
                $hy = explode("_", $data['hy_tag']);
                $hynum = count($hy);
                for ($i=0; $i < $hynum && $hy_isnull == 0; $i++) { 
                    $dataCom['tag_id'] = $hy[$i];
                    $dataCom['ucode'] = $ucode;
                    $dataCom['code'] = $code;
                    $listSaveCom = $ModelTagCom->add($dataCom);
                    $LData['msg'] = "行业概念: ucode ".$ucode." com表新增股吧成分成功";
                    aLog($LData);
                }
            }

            if (!$listTagInit[0]['gn_tag']) {
                $gn = explode("_", $data['gn_tag']);
                $gnnum = count($gn);
                for ($i=0; $i < $gnnum && $gn_isnull == 0; $i++) { 
                    $dataCom['tag_id'] = $gn[$i];
                    $dataCom['ucode'] = $ucode;
                    $dataCom['code'] = $code;
                    $listSaveCom = $ModelTagCom->add($dataCom);
                    $LData['msg'] = "行业概念: ucode ".$ucode." com表新增股吧成分成功";
                    aLog($LData);
                }
            }

            //修改股吧所属行业
            if ($data['hy_tag'] != $listTagInit[0]['hy_tag']){
                //判断如果是未确定的行业概念，则在确定时处理 
                //成分变更一增一减
                $available = $tagModel->where("id='".$listTagInit[0]['hy_tag']."'")->limit(1)->select();
                if ($available && $available[0]['available'] == 1) {
                    //存在
                    $a = $ModelTagCom->where("ucode='".$ucode."' and tag_id='".$data['hy_tag']."'")->select();
                    if ($a && $a != null) {
                        //当天删除，当天新增
                        $shData['out_time'] = null;
                        //非当天添加
                        if (substr($a[0]['out_time'],0,10)> substr($a[0]['in_time'],0,10)) {  
                            $shData['in_time'] = $this->is_list($id);
                        }

                    }else{
                        $shData['out_time'] = $now;

                        $ahData['in_time'] = $this->is_list($id);
                        $ahData['code'] = $code;
                        $ahData['ucode'] = $ucode;
                        $ahData['tag_id'] = $data['hy_tag'];

                        $d = $ModelTagCom->add($ahData);
                        if ($d && $d!=null) {
                            $LData['msg'] = "行业概念: ucode ".$ucode." tag_id ".$ahData['tag_id']." 成分变更成功";
                        }else{
                            $LData['code'] = -1;
                            $LData['msg'] = "行业概念: ucode ".$ucode." tag_id ".$ahData['tag_id']." 成分变更失败";
                        }
                        aLog($LData);
                    }

                    $c = $ModelTagCom->where("ucode='".$ucode."' and tag_id='".$listTagInit[0]['hy_tag']."'")->save($shData);
                    if ($c && $c!=null) {
                        $LData['msg'] = "行业概念: ucode ".$ucode." 成分变更成功";
                    }else{
                        $LData['code'] = -1;
                        $LData['msg'] = "行业概念: ucode ".$ucode." 成分变更失败";
                    }
                    aLog($LData);

                    $d = $ModelTagCom->where("ucode='".$ucode."' and (tag_id='".$listTagInit[0]['hy_tag']."' or tag_id='".$data['hy_tag']."')")->count();
                    if ($d == 2) {
                        $f = $ModelTagCom->where("ucode='".$ucode."' and tag_id='".$data['hy_tag']."'")->save($shData);
                        $shData['out_time'] = $now;
                        $e = $ModelTagCom->where("ucode='".$ucode."' and tag_id='".$listTagInit[0]['hy_tag']."'")->save($shData);
                    }
                }
            }

            //修改初始化记录
            $listTagAdd = $stockTagModel->where("code='".$ucode."'")->save($data);
            if ($listTagAdd && $listTagAdd != null) {
                $LData['msg'] = "行业概念: ucode ".$ucode." init股吧关联修改成功";
                aLog($LData);

                //成分变更,修改tagComponent表
                $old = explode("_", $listTagInit[0]['gn_tag']);
                $new = explode("_", $data['gn_tag']);
                $onum = count($old);
                $nnum = count($new);
                for ($j=0; $j < $onum; $j++) { 
                    //记录删除的
                    $index = strpos($data['gn_tag'], $old[$j]);
                    if ($index === false) {
                        $sData['out_time'] = $now;
                        //失败写日志
                        //判断如果是未确定的行业概念，则在确定时处理
                        $available = $tagModel->where("id=".$old[$j])->limit(1)->select();
                        if ($available && $available[0]['available'] == 1) {
                            $c = $ModelTagCom->where("ucode='".$ucode."' and tag_id='".$old[$j]."'")->save($sData);
                            $LData['msg'] = "行业概念: ucode ".$ucode." tag_id".$old[$j]." com成分关联删除";
                            aLog($LData);
                        }
                    }
                }

                for ($j=0; $j < $nnum; $j++) { 
                    //记录新增的
                    $index = strpos($listTagInit[0]['gn_tag'], $new[$j]);
                    if ($index === false) {
                        //判断如果是未确定的行业概念，则在确定时处理
                        $available = $tagModel->where("id=".$new[$j])->limit(1)->select();
                        if ($available && $available[0]['available'] == 1) {
                            //存在
                            $a = $ModelTagCom->where("ucode='".$ucode."' and tag_id='".$new[$j]."'")->select();
                            if ($a && $a != null) {
                                //当天删除，当天新增
                                $shData['out_time'] = null;
                                //非当天添加
                                //if ($a[0]['out_time']> date('Y-m-d',strtotime('+1 day'))) {
                                if (substr($a[0]['out_time'],0,10)> substr($a[0]['in_time'],0,10)) {  
                                    $shData['in_time'] = $this->is_list($id);
                                }

                                $c = $ModelTagCom->where("ucode='".$ucode."' and tag_id='".$new[$j]."'")->save($shData);
                                if ($c && $c!=null) {
                                    $LData['msg'] = "行业概念: ucode ".$ucode." com成分变更成功";
                                }else{
                                    $LData['code'] = -1;
                                    $LData['msg'] = "行业概念: ucode ".$ucode." com成分变更失败";
                                }
                                aLog($LData);
                            }else{
                                $aData['in_time'] = $this->is_list($id);
                                $aData['code'] = $code;
                                $aData['ucode'] = $ucode;
                                $aData['tag_id'] = $new[$j];

                                $d = $ModelTagCom->add($aData);
                                if ($d && $d!=null) {
                                    $LData['msg'] = "行业概念: ucode ".$ucode." tag_id ".$ahData['tag_id']." com成分变更成功]";
                                }else{
                                    $LData['code'] = -1;
                                    $LData['msg'] = "行业概念: ucode ".$ucode." tag_id ".$ahData['tag_id']." com成分变更失败]";
                                }
                                aLog($LData);
                            }
                        }
                    }
                }

            }else{
                $ecode = -1;
                $msg = "修改taginit初始记录失败";
                $listTagAdd = array();
            }
            
            $this->ajaxOutput($ecode, $msg, array('list'=>$listTagAdd)); 
        }

        $this->ajaxOutput($ecode, $msg, array('list'=>$list)); 
    }

    public function is_list($id){
        $now = date("Y-m-d H:i:s");
        $Model = M('StockBar');
        $list = $Model->where("id='".$id."'")->select();
        if ($list && $list != null) {
            //未上市
            if($list[0]['state'] == 4){
                return "";
            }
            if ($list[0]['list_date'] > $now) {
                return $list[0]['list_date'];
            }else if ($list[0]['list_date'] == ""){
                return "";
            }else{
                return $now;
            }
        }
        return "";
    }


    public function update1(){
        $res["error"] = "";//错误信息
        $res["msg"] = "";//提示信息
        if(move_uploaded_file($_FILES['fileToUpload']['tmp_name'],"Users/ios1/Desktop/js/test.bmp")){
            $res["msg"] = "ok";
        }else{
            $res["error"] = "error";
        }
        echo json_encode($res);
    }

    public function update() {
        $upFilePath = "/data/www/admin/data/";
        // $ok=@move_uploaded_file($_FILES['img']['tmp_name'],$upFilePath);
        // if($ok === FALSE){
        //     $this->ajaxOutput(0, "上传失败", array('list'=>$list));   
        // }else{
        //     $this->ajaxOutput(0, "上传成功", array('list'=>$list)); 
        // }

        //echo "update";

        if ((($_FILES["houseMaps"]["type"] == "image/gif")
            || ($_FILES["houseMaps"]["type"] == "image/jpeg")
            || ($_FILES["houseMaps"]["type"] == "image/bmp")
            || ($_FILES["houseMaps"]["type"] == "image/pjpeg"))
            && ($_FILES["houseMaps"]["size"] < 1000000))
        {            //100KB
            $extend = explode(".",$_FILES["houseMaps"]["name"]);
            $key = count($extend)-1;
            $ext = ".".$extend[$key];
            $newfile = time().$ext;
         
            //if(!file_exists('upload')){mkdir('upload');}
            move_uploaded_file($_FILES["houseMaps"]["tmp_name"],$upFilePath);
            @unlink($_FILES['houseMaps']);
            //$this->ajaxOutput(0, "上传成功", array('list'=>$list)); 
        }else {
            //$this->ajaxOutput(0, "上传失败", array('list'=>$list));
        }


    }
}
