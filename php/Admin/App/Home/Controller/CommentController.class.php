<?php
namespace Home\Controller;
use Home\Controller\CommonController;

/**
 * “帖子”控制器类。
 */ 
class CommentController extends CommonController {

    private $res_isLogin = "";
    private $ctime = "";
    private $ctimeend = "";
    private $curpage = "";
    private $pagenum = "";
    private $condition = "";

    public function init(){
        $this->res_isLogin = $this->isLogin();
        if(!$this->res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);

        $this->ctime = I('param.ctime',"1970-01-01");
        $this->ctimeend = I('param.ctimeend',date("Y-m-d H:i:s"));

        $this->condition = "1=1";
        if (isset($this->ctime)) {
            $this->condition = $this->condition." and ctime>='".$this->ctime."'";
        }

        if (isset($this->ctimeend)) {
            $this->condition = $this->condition." and ctime<'".$this->ctimeend." 23:59.59'";
        }
    }
    /**
    * 获取评论
    **/
    public function get(){
        $this->init();

        $value = I('param.title',"");
        if (!empty($value)) {
            $map['user_id'] = getUidByName($value);
            $num = M('Comment')->where($map)->count();
            $list = M('Comment')->where($map)->page($this->curpage, $this->pagenum)->select();
            if($list || $list === null){
                $code = 0;
                $msg = "suc";
                if ($list === null) {
                    $this->ajaxOutput(-1, "没有这个用户", array('count'=>0, 'list'=>array()));
                }
                for ($i=0; $i < count($list); $i++) { 
                    $list[$i]['name'] = $value;
                }
                
            }else{
                $list = Array();
                $code = 10001;
                $msg = "服务器错误";
            }
            $this->ajaxOutput($code, $msg, array('count'=>$num, 'list'=>$list));
        }

        $num = M('Comment')->where($this->condition)->count();
        $list = M('Comment')->where($this->condition)->page($this->curpage, $this->pagenum)->select();
        if ($list) {
            if($list || $list === null){
                $code = 0;
                $msg = "suc";
                if ($list === null) {
                    $this->ajaxOutput(-1, "没有数据", array('count'=>0, 'list'=>array()));
                }
            }else{
                $list = Array();
                $code = 10001;
                $msg = "服务器错误";
            }

            for ($i=0; $i < count($list); $i++) { 
                $list[$i]['url'] = "http://www.richba.com/post/detail/".$list[$i]['post_id'].".html";
                $UInfo = getUinfo($list[$i]['user_id']);
                $list[$i]['name'] = $UInfo['name'];
            }

        }

        $this->ajaxOutput($code, $msg, array('count'=>$num, 'list'=>$list));
    }


    /**
    * 删除评论
    **/
    public function del(){

        $this->init();

        $id = I('param.id',"");
        if (!isset($id)) {
            $this->ajaxOutput(-1, "评论id参数错误", array('list'=>$array()));
        }


        if($id){
            $CData['state'] = 0;
            $list = M('Comment')->where("id='".$id."'")->save($CData);
            if($list){
                $code = 0;
                $msg = "suc";

                //$this->reIndexStock($id, $listGet[0]['user_id']);
            }else{
                $list = Array();
                $code = 10001;
                $msg = "服务器错误";
                if ($list === null) {
                    $this->ajaxOutput($code, "评论id错误", array('list'=>$listGet));
                }
            }
            
        }

        $this->ajaxOutput($code, $msg, array('list'=>$list));
    }

    public function reIndexStock($id, $uid){
        // 初始化一个 cURL 对象 
        $curl = curl_init(); 

        // 设置你需要抓取的URL
        //a=delPost&id=帖子ID&user_id=帖子用户ID
        $url = 'http://www.richba.com/index.php?m=home&c=cmd&a=delPost&id='.$id."&user_id=".$uid;
        
        curl_setopt($curl, CURLOPT_URL, $url); 
       
        
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
