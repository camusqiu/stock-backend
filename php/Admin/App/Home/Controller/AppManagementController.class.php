<?php
namespace Home\Controller;
use Think\Controller;
import('Org.Util.Date');
class AppManagementController extends CommonController {
    
    //获取公告信息
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

        $id = I('param.id',-1);
        $type = I('param.type',-1);
        $page = I('param.page',-1);
        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);

        $Model = M('Notice');
        if ($page == 2) {
            $Model = M('AppManagement');
        }

        $condition = "1=1";
        if($id && $id != "-1"){
          $condition = $condition." and id='".$id."'";
        }

        if($type != "-1"){
          $condition = $condition." and type='".$type."'";
        }

        $allnum = $Model->where($condition)->count();

        $list = $Model->where($condition)->order('ctime desc')->page($this->curpage, $this->pagenum)->select();
        if($list || $list == null){
          $code = 0;
          $msg = "suc";
          if($list == null){
            $list = array();
          }

        }else{
          $code = -1;
          $msg = "sql failed";
        }
        $this->ajaxOutput($code, $msg, array('count'=>$allnum, 'list'=>$list));
    }

    //搜索公告记录
    public function searchAppNotice() {
        
        $Model = M('Notice');

        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $title = I('param.title',-1);
        $this->condition = "1=1";
        if($title != -1){
            $this->condition = $this->condition." and name like '%".$title."%'";
        }

        $list = $Model->where($this->condition)->select();
        if($list){
            $code = 0;
            $msg = "suc";
            $num = count($list);
        }else{
            $code = 10001;
            $msg = "search user fail";
            $list = array();
        }

        $this->ajaxOutput($code, $msg, array('count'=>$num, 'list'=>$list));
    }    



     //添加公告记录
    public function addAppNotice() {
        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $Model = M('Notice');

        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $title = I('param.title',-1);
        $id = I('param.id',-1);
        $content = I('param.content',-1);

        $LData['table_name'] = "Notice";
        $LData['type'] = 1;
        $LData['admin_id'] = $res_isLogin;

        if($title != "-1"){
            $data['title'] = $title;
        }

        if($content != "-1"){
            $data['content'] = $content;
        }

        if($id > "0"){
            $list = $Model->where("id='".$id."'")->save($data);
            $LData['msg'] = "app系统公告: id ".$id." title [".$data['title']."] 公告修改成功";
        }else{
            $list = $Model->add($data);
            $LData['msg'] = "app系统公告: id ".$list." title [".$data['title']."] 公告发表成功";
        }
        aLog($LData);
        if($list){
            $code = 0;
            $msg = "suc";
            $num = count($list);
           
        }else{
            $code = 10001;
            $msg = "search user fail";
            $list = array();
        }

        $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
    }   

     //修改状态
    public function delAppNotice() {
        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $LData['table_name'] = "AppManagement";
        $LData['type'] = 3;
        $LData['admin_id'] = $res_isLogin;
        
        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);

        $id = I('param.id',-1);
        $page = I('param.page',-1);

        $Model = M('AppManagement');
        if ($page == 1) {
            $Model = M('Notice');
        }

        $condition = "1=1";
        if($id && $id>=0){
            $condition = $condition." and id='".$id."'";
        }

        if($id > "0"){
            $listTemp = $Model->where("id='".$id."'")->select();
            if($listTemp){
                $code = 0;
                $msg = "suc";
                if($listTemp[0]['status'] == "0"){
                    $data['status'] = 1;
                }else{
                    $data['status'] = 0;
                }
           
                $list = $Model->where("id='".$id."'")->save($data);

                if($list){
                    $code = 0;
                    $msg = "suc";
                    $LData['msg'] = "AppManagement:[app系统公告: id ".$id." 删除公告成功]";
                }else{
                    $code = 10001;
                    $msg = "id不存在";
                    $list = array();
                    $LData['msg'] = "AppManagement:[app系统公告: id ".$id." 不存在，删除公告失败]";
                }

                $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));    

            }else{
                $code = 10001;
                $msg = "search user fail";
                $list = array();
            }
        }

        $this->ajaxOutput($code, $msg, array('count'=>0, 'list'=>array()));
    }   


    public function updateAppWelPic(){
        $Model = M('AppManagement');

        $id = I('param.id',-1);
        $content = I('param.content',-1);
        $title = I('param.title',-1);

        if($content != "-1"){
            $data['content'] = $content;
        }

        if($title != "-1"){
            $titleorgi = str_replace('480_', 'orgi', $title);
            $data['title'] = $titleorgi;
        }

        if($id > "0"){
            $list = $Model->where("id='".$id."'")->save($data);
        }
        //print_r($list);

        if($list){
            $code = 0;
            $msg = "suc";
            $num = count($list);
           
        }else{
            $code = 10001;
            $msg = "search user fail";
            $list = array();
        }

        $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list)); 
    }  

    // public function delAppWelPic(){
    //     $Model = M('AppManagement');

    //     $id = I('param.id',-1);

    //     if($id > "0"){
    //         $list = $Model->where("id='".$id."'")->save($data);
    //     }

    //     if($list){
    //         $code = 0;
    //         $msg = "suc";
    //         $num = count($list);
           
    //     }else{
    //         $code = 10001;
    //         $msg = "search user fail";
    //         $list = array();
    //     }

    //     $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list)); 
    // } 

    public function getAppWelPic(){
        $Model = M('AppManagement');

        $id = I('param.id',-1);
        $content = I('param.content',-1);

        if($id > "0"){
            $list = $Model->where("id='".$id."'")->select();
            if($list){
                $code = 0;
                $msg = "suc";
                $num = count($list);
               
            }else{
                $code = 10001;
                $msg = "search user fail";
                $list = array();
            }
        }

    
        $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list)); 
    } 

}
?>
