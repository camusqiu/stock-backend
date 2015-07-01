<?php
namespace Home\Controller;
use Home\Controller\CommonController;

/**
 * “帖子标签”控制器类。
 */ 
class TagController extends CommonController {
    
    private $curpage = 0;
    private $pagenum = 0;

	public function get(){
		$Model = M('Tag');
		//$list = $Model->getField('id,name,subtype');
        $list = $Model->where("1=1 and available!='0'")->order("abbrev asc")->select();
		$this->ajaxOutput(0, '', array('list'=>$list));
	}

    public function getbak(){
        $Model = M('Tag');
        //$list = $Model->getField('id,name,subtype');
        $list = $Model->where("1=1 and available!='0'")->order("abbrev asc")->select();
        $this->ajaxOutput(0, '', array('list'=>$list));
    }


    public function getTag() {
        $Model = M('Tag');

        $id = I('id', -1);
        $subtype = I('subtype', -1);
        $type = I('type', -1);
        $name = I('name', '');

        $this->curpage = I('curpage',-1);
        $this->pagenum = I('pagenum',-1);

        $condition = "1=1 and available!='0'";

        if($id != -1 && $id != 0){
            $condition = $condition." and id='".$id."'";
        }

        if($subtype != -1 && $subtype != 0){
            $condition = $condition." and subtype='".$subtype."'";
        }else{
            $condition = $condition." and subtype!='0'";
        }

        if($type != -1 && $type != 0){
            $condition = $condition." and type='".$type."'";
        }

        if($name != -1 && $name != 0){
            $condition = $condition." and name='".$name."'";
        }

        $allnum = $Model->where($condition)->count();
        if ($this->curpage != -1 && $this->curpage != 0 && $this->pagenum != -1 && $this->pagenum != 0) {
            $list = $Model->where($condition)->page($this->curpage, $this->pagenum)->select();
        }else{
            $list = $Model->where($condition)->select();
        }

        if($list || $list == null){
            $code = 0;
            $msg = "suc";
            if($list == null){
                $list = Array();
            }
        }else{
            $list = Array();
            $code = -1;
            $msg = $codes." is not set inittag";
        }
        $this->ajaxOutput($code, $msg, array('count'=>$allnum,'list'=>$list));    
    }

    public function getHYGN_StockBar(){
        $id = I('id', -1);

        $Model = M('StockTagInit');

        $TList = M('Tag')->where("id='".$id."'")->select();
        if (!$TList) {
            $this->ajaxOutput(-1, "id 不存在", array('list'=>array()));
        }

        if ($TList[0]['type'] == 1) {
            $condition = "hy_tag='".$id."'";
        }else if ($TList[0]['type'] == 2){
            $condition = "gn_tag='".$id."' or gn_tag regexp '\_".$id."$' or gn_tag regexp '^".$id."\_' or gn_tag like '%\_".$id."\_%'";
        }

        $SList = $Model->where($condition)->select();
        if ($SList) {
            $code = 0;
            $msg = "suc";
            if ($SList == null) {
                $code = -1;
                $msg = "没有股吧关联";
            }

            $num = count($SList);
            for ($i=0; $i < $num; $i++) { 
                $a = getBarByCode($SList[$i]['code']);
                $SList[$i]['SBName'] = $a['name'];
            }

            $this->ajaxOutput(0, "suc", array('count'=>count($SList), 'list'=>$SList));
        }else{
            $this->ajaxOutput(-1, "没有股吧关联", array('count'=>0,'list'=>array()));
        }
    }

    public function saveTag() {
        $id = I('id', -1);

        $type = I('type', -1);
        $subtype = I('subtype', -1);
        $name = I('name', "");
        $abbrev = I('abbrev', -1);
        $spell = I('spell', "");

        $condition = "1=1";

        if($id != -1 && $id != 0){
            $condition = $condition." and id='".$id."'";
        }else{
            $this->ajaxOutput(-1, "id为空", array('list'=>array()));   
        }
        
        $Model = M('Tag');
        $list = $Model->where($condition)->select();

        if($list && $list != null){
            if($type != -1 && $type != "" && $subtype != -1 && $subtype != "" && $name != "" && $abbrev != -1 && $abbrev != "" && $spell != ""){
                $data['type'] = $type;
                $data['subtype'] = $subtype;
                $data['name'] = $name;
                $data['abbrev'] = $abbrev;
                $data['spell'] = $spell; 
                $listSave = $Model->where($condition)->save($data);
                if($listSave){
                    $code = 0;
                    $msg = "save suc";
                }else{
                    $listSave = Array();
                    $code = -1;
                    $msg = "修改标签失败";
                }
                $list = $listSave;
            }
        }else{
            $list = Array();
            $code = -1;
            $msg = "修改标签失败";
        }
        
        $this->ajaxOutput($code, $msg, array('list'=>$list));    
    }


	public function getStockTag() {
        $HYGN = I('HYGN', -1);
        $market = I('market', -1);
        $name = I('name', -1);
        $abbrev = I('abbrev', -1);
        $spell = I('spell', -1);
        $Model = M('Tag');

        $condition = "1=1 and available='1'";
        if($HYGN != -1 && $HYGN != 0){
            $condition = $condition." and type='".$HYGN."'";
        }

        if($market != -1 && $market != 0){
            $condition = $condition." and subtype='".$market."'";
        }

        if($name != -1 && $name != ""){
            $condition = $condition." and name='".$name."'";
        }

        if($abbrev != -1 && $abbrev != ""){
            $condition = $condition." and abbrev='".$abbrev."'";
        }

        if($spell != -1 && $spell != ""){
            $condition = $condition." and spell='".$spell."'";
        }

        $list = $Model->where($condition)->select();

        if($list || $list == null){
            $code = 0;
            $msg = "suc";
            if($list == null){
                $list = Array();
            }
        }else{
            $list = Array();
            $code = -1;
            $msg = $codes." is not set inittag";
        }
        $this->ajaxOutput($code, $msg, array('list'=>$list));    
    }

    public function addStockTag() {

        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $LData['table_name'] = "Tag";
        $LData['type'] = 2;
        $LData['admin_id'] = $res_isLogin;

    	$HYGN = I('HYGN', -1);
        $market = I('market', -1);
        $name = I('name', "");
        $abbrev = I('abbrev', -1);
        $spell = I('spell', "");

        $condition = "1=1";

        if($HYGN != -1 && $HYGN != 0){
            $condition = $condition." and type='".$HYGN."'";
        }
        if($market != -1 && $market != 0){
            $condition = $condition." and subtype='".$market."'";
        }
        if($name != ""){
            $condition = $condition." and name='".$name."'";
        }

        $Model = M('Tag');
        $list = $Model->where($condition)->select();

        if($list == null){
            $data['type'] = $HYGN;
            $data['subtype'] = $market;
            $data['class'] = intval($HYGN)*100+intval($market);
            $data['name'] = $name;
            $data['abbrev'] = $abbrev;
            $data['spell'] = $spell; 
            $data['level'] = 2; 
            $data['available'] = 2; 
            $listAdd = $Model->add($data);
            if($listAdd){
                $code = 0;
                $msg = "添加成功";

                $LData['msg'] = "行业概念: name ".$data['name']." type".$data['type']." subtype".$data['subtype']." 新增行业概念成功";
            }else{
                $listAdd = Array();
                $code = -1;
                $msg = "添加失败";

                $LData['msg'] = "行业概念: name ".$data['name']." type".$data['type']." subtype".$data['subtype']." 新增行业概念失败";
            }
            $list = $listAdd;
        }elseif($list && $list != null && $list[0]['available'] == 0){
            $data['available'] = 2;
            $list = $Model->where($condition)->save($data);
            $code = 0;
            $msg = "添加成功";

            $LData['type'] = 1;
            $LData['msg'] = "行业概念: name ".$name." type".$HYGN." subtype".$market." 修改行业概念状态(删除-->未确认)";
        }else{
            $list = Array();
            $code = -1;
            $msg = "已经存在，不要重复添加";

            $LData['msg'] = "行业概念: name ".$name." type".$HYGN." subtype".$market." 新增行业概念失败(已经存在)";
        }
        aLog($LData);
        
        $this->ajaxOutput($code, $msg, array('list'=>$list));    
    }



    public function saveStockTag() {

        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $LData['table_name'] = "Tag";
        $LData['type'] = 1;
        $LData['admin_id'] = $res_isLogin;

        $HYGN = I('HYGN', -1);
        $market = I('market', -1);
        $name = I('name', "");
        $abbrev = I('abbrev', -1);
        $spell = I('spell', "");

        $nameOrc = I('nameOrc', "");
        $abbrevOrc = I('abbrevOrc', -1);
        $spellOrc = I('spellOrc', "");

        $condition = "1=1";

        if($HYGN != -1 && $HYGN != 0){
            $condition = $condition." and type='".$HYGN."'";
        }else{
            $this->ajaxOutput(-1, "HYGN为空", array('list'=>array()));   
        }
        if($market != -1 && $market != 0){
            $condition = $condition." and subtype='".$market."'";
        }else{
            $this->ajaxOutput(-1, "market为空", array('list'=>array()));   
        }
        if($nameOrc != ""){
            $condition = $condition." and name='".$nameOrc."'";
        }else{
            $this->ajaxOutput(-1, "nameOrc为空", array('list'=>array()));   
        }
        if($abbrevOrc != ""){
            $condition = $condition." and abbrev='".$abbrevOrc."'";
        }
        if($spellOrc != ""){
            $condition = $condition." and spell='".$spellOrc."'";
        }
        

        $Model = M('Tag');
        $list = $Model->where($condition)->select();

        if($list && $list != null){
            if($name != "" && $abbrev != -1 && $abbrev != "" && $spell != ""){
                $data['type'] = $HYGN;
                $data['subtype'] = $market;
                $data['name'] = $name;
                $data['abbrev'] = $abbrev;
                $data['spell'] = $spell; 

                if ($data['name'] != $list[0]['name']) {
                    $LMsg = $LMsg." name[".$list[0]['name']."]修改为[".$data['name']."] ";
                }
                if ($data['spell'] != $list[0]['spell']) {
                    $LMsg = $LMsg." spell[".$list[0]['spell']."]修改为[".$data['spell']."] ";
                }
                if ($data['abbrev'] != $list[0]['abbrev']) {
                    $LMsg = $LMsg." abbrev[".$list[0]['abbrev']."]修改为[".$data['abbrev']."] ";
                }


                $listSave = $Model->where($condition)->save($data);
                if($listSave){
                    $code = 0;
                    $msg = "修改成功";
                    
                    $LData['msg'] = "行业概念: name ".$data['name']." type".$data['type']." subtype".$data['subtype']."  ".$LMsg."  修改行业概念成功";
                }else{
                    $listSave = Array();
                    $code = -1;
                    $msg = "修改失败";

                    $LData['msg'] = "行业概念: name ".$data['name']." type".$data['type']." subtype".$data['subtype']."  ".$LMsg."  修改行业概念失败";
                }
                $list = $listSave;
            }
        }else{
            $list = Array();
            $code = -1;
            $msg = "修改失败";

            $LData['type'] = 0;
            $LData['msg'] = "Tag:[行业概念: name ".$nameOrc." type".$HYGN." subtype".$market." 查找行业概念失败]";
        }

        aLog($LData);
        $this->ajaxOutput($code, $condition, array('list'=>$list));    
    }



    public function delStockTag() {

        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $LData['table_name'] = "Tag";
        $LData['type'] = 1;
        $LData['admin_id'] = $res_isLogin;

        $tagid = I('id', -1);
        if($tagid != -1 && $tagid != 0){
            $condition = $condition."id='".$tagid."'";
        }else {
            $this->ajaxOutput(-1, "没有tagid", array('list'=>array())); 
        }

        $TagModel = D('Tag');
        $available = I('available', -1);

        if ($available == 2) {
            $data['available'] = 1;
            //新增行业概念确认
            $result = $TagModel->getNewTagInit($tagid);
        }else{
            $data['available'] = 0;
            //删除行业概念
            $result = $TagModel->delStockTagInit($tagid);   
        }
        if ($result['code'] != '0') {
            $this->ajaxOutput($result['code'], $result['msg'], array('list'=>array()));
        }

        $Model = M('Tag');
        $list = $Model->where($condition)->save($data);

        if($list || $list !== null){
            $code = 0;
            $msg = "操作成功";
            
            $LData['msg'] = "Tag:[行业概念: id ".$tagid." 删除行业概念成功]";
        }else{
            $list = Array();
            $code = -1;
            $msg = "操作失败";

            $LData['code'] = $code;
            $LData['msg'] = "Tag:[行业概念: id ".$tagid." 删除行业概念失败]";
        }

        aLog($LData);
        $this->ajaxOutput($code, $msg, array('list'=>$list));    
    }

    public function search () {
        $Model = M('Tag');
        $value = I('value', '');
        if ($value != '') {
            $condition = "available!='0' and (name='".$value."' or abbrev='".$value."' or spell='".$value."')";
        }
        $list = $Model->where($condition)->select();
        if($list || $list == null){
            $code = 0;
            $msg = "suc";
            if($list == null){
                $list = Array();
            }
        }else{
            $list = Array();
            $code = -1;
            $msg = "没有找到";
        }
        $this->ajaxOutput($code, $msg, array('list'=>$list)); 
    }
}
