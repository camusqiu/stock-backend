<?php
namespace Home\Controller;
use Think\Controller;
import('Org.Util.Date');
class PostIndexController extends CommonController {

	public function setPostTitle () {

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

        $post_id = I('param.id',-1);
        $title = I('param.value',-1);

        $this->condition = "1=1";
        if($post_id && $post_id != -1){
            $this->condition = $this->condition." and post_id=".$post_id;
        }

        $Model = M('PostIndex'); 
        $ModelPost = M('Post');

        $listPost = $ModelPost->where( "id='".$post_id."'")->select();

        $list = $Model->where($this->condition)->select();

        if($title && $title != -1){
            $this->condition = $this->condition." and title=".$title;
        }


        if ($listPost) {
            $dataPost['new_title'] = $title;
            $listTemp = $ModelPost->where(" id='".$post_id."'")->save($dataPost);
            if ($listTemp) {
                $code = 0;
                //$this->ajaxOutput($code, "修改新标题成功", array('count'=>1, 'list'=>$list));
            }else {
                $code = -1;
                $this->ajaxOutput($code, "修改新标题失败", array('count'=>0, 'list'=>$listTemp));
            }
        }


        if($list){
            $data['title'] = $title;
            $list = $Model->where(" post_id='".$post_id."'")->save($data);
            if ($list) {
                $code = 0;
                $this->ajaxOutput($code, "修改新标题成功", array('count'=>1, 'list'=>$list));
            }else {
                $code = -1;
                $this->ajaxOutput($code, "修改新标题失败", array('count'=>0, 'list'=>$list));
            }
            
        }else{
            $data['post_id'] = $post_id;
            $data['title'] = $title;
            $list = $Model->add($data);
            if ($list) {
                $code = 0;
                $this->ajaxOutput($code, "添加新标题成功", array('count'=>1, 'list'=>$list));
            }else {
                $code = -1;
                $this->ajaxOutput($code, "添加新标题失败", array('count'=>0, 'list'=>$list));
            }
        }

        $this->ajaxOutput(-1, "数据库查找错误", array('count'=>0, 'list'=>array()));

	}


	public function getPostTitle () {
		$post_id = I('param.id',-1);

        $this->condition = "1=1";
        if($post_id && $post_id != -1){
            $this->condition = $this->condition." and post_id=".$post_id;
        }

        $Model = M('PostIndex'); 

        $title = $Model->where($this->condition)->getField('title');
        if ($title) {
        	$this->ajaxOutput(0, "获取新标题成功", array('count'=>1, 'list'=>$title));
        }
        $this->ajaxOutput(0, "获取新标题失败", array('count'=>0, 'list'=>array()));
	}

}
?>
