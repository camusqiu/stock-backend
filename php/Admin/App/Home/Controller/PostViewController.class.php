<?php
namespace Home\Controller;
use Home\Controller\CommonController;

/**
 * “帖子浏览量”控制器类。
 */ 
class PostViewController extends CommonController {

    //今日发帖，每10分钟新增贴一次
    public function addNewPostView(){
    	$ModelPost = M('Post'); 
    	$ModelPostView = M('PostView'); 

    	$key ="post_view_count_total";
    	$redis = S(array('type'=>'Redis'));

        $maxPostId = $ModelPostView->max('post_id');

    	$condition = "";
    	$list = $ModelPost->where("1=1 and state='1' and id>'".$maxPostId."'")->getField('id,type,ctime');
    	$num = count($list);

        // $i < 10000000避免逻辑错误死循环
    	for ($i=$maxPostId+1, $j=0; $j < $num && $i < 10000000; $i++) { 

            if ($list[$i]['id']) {
                $condition = sprintf("1=1 and post_id='%s'", $list[$i]['id']);
                $viewnum = $redis->ZSCORE($key, $list[$i]['id']);
                //新增
                $data['viewnum'] = $viewnum;
                $data['post_id'] = $list[$i]['id'];
                $data['type'] = $list[$i]['type'];
                $data['ctime'] = $list[$i]['ctime'];
                $Lpostview = $ModelPostView->where($condition)->add($data);
                $j++;
            }
     //       var_dump($data);
    	}
    }


    //今日发帖，每小时更新一次
    public function newPostView(){
        $ModelPost = M('Post'); 
        $ModelPostView = M('PostView'); 

        $key ="post_view_count_total";
        $redis = S(array('type'=>'Redis'));

        $maxPostId = $ModelPost->max('id');

        $idStart = $ModelPost->where("1=1 and state='1' and ctime>='".date('Y-m-d')."'")->limit(1)->getField('id');

        $condition = "";
        $list = $ModelPost->where("1=1 and state='1' and id>='".$idStart."'")->getField('id,type,ctime');
        $num = count($list);

//        var_dump($list);

        for ($i=$idStart, $j=0; $j < $num && $i <= $maxPostId; $i++) { 

            $condition = sprintf("1=1 and post_id='%s'", $list[$i]['id']);
            $Lpostview = $ModelPostView->where($condition)->select();
            
            if($Lpostview){
                $viewnum = $redis->ZSCORE($key, $list[$i]['id']);

                //更新
                $dataView['viewnum'] = $viewnum;
                $Lpostview = $ModelPostView->where($condition)->save($dataView);
                $j++;
            }
//            var_dump($list[$i]);
        }
        
    }


    //历史发帖，每天更新一次
    public function allPostView(){
    	$ModelPost = M('Post'); 
    	$ModelPostView = M('PostView'); 

    	$key ="post_view_count_total";
    	$redis = S(array('type'=>'Redis'));

    	$condition = "";
    	$list = $ModelPost->where("1=1 and state='1'")->select();
    	$num = count($list);
    	for ($i=0; $i < $num; $i++) { 
    		$condition = sprintf("1=1 and post_id='%s'", $list[$i]['id']);
    		$viewnum = $redis->ZSCORE($key, $list[$i]['id']);

    		//更新
			$dataView['viewnum'] = $viewnum;
			$Lpostview = $ModelPostView->where($condition)->save($dataView);
    	}
    }


    //历史数据全量添加
  /* 
    public function initAllPostView(){
    	$ModelPost = M('Post'); 
    	$ModelPostView = M('PostView'); 

    	$key ="post_view_count_total";
    	$redis = S(array('type'=>'Redis'));

    	$condition = "";
    	$list = $ModelPost->where("1=1 and id>'51135'")->getField('id,type,ctime');
    	$num = count($list);
       // var_dump($list);
        
        //echo $num;

    	for ($i=51136; $i < $num+51136; $i++) { 
            //var_dump($list[$i]['id']);
    		$condition = sprintf("1=1 and post_id='%s'", $list[$i]['id']);
    		$viewnum = $redis->ZSCORE($key, $list[$i]['id']);

    		//新增
			$data['viewnum'] = $viewnum;
			$data['post_id'] = $list[$i]['id'];
			$data['type'] = $list[$i]['type'];
			$data['ctime'] = $list[$i]['ctime'];
            //var_dump($data);
			$Lpostview = $ModelPostView->where($condition)->add($data);
    	}
       
    }
    
    */


}

?>	
