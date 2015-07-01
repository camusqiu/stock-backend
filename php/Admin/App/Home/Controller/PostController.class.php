<?php
namespace Home\Controller;
use Home\Controller\CommonController;

/**
 * “帖子”控制器类。
 */ 
class PostController extends CommonController {

    private $listData = "";
    private $condition = "";
    private $curpage = 0;
    private $pagenum = 0;
    private $assignNumMax = 5;
    private $msg;
    private $stime = "";
    private $etime = "";

    public function sortNewsInfo(){
        $this->stime=microtime(true);     
    
        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }
        
        $this->condition = "1=1";
        $essential_state = I('param.essential_state',-1);
        $transmit_count = I('param.transmit_count',-1);
        $recom_count = I('param.recom_count',-1);
        $comment_count = I('param.comment_count',-1);
        $viewNum = I('param.viewNum',-1);

        $ctime = I('param.ctime',-1);
        $ctimeend = I('param.ctimeend',-1);
        $type = I('param.type',-1)>2 ? 3 : I('param.type',-1);
        $user_id = I('param.user_id',-1);
        $state = I('param.state',-1);

        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);

        $Model = M('Post'); 
        $ModelView = M('PostView'); 
        $ModelAdmin = M('Admin'); 
        $ModelUser = M('User'); 
        $ModelInfo = M('NewsInfo'); 
        if($essential_state && $essential_state>=0)
        {
            $conditionTemp = sprintf(" and essential_state=%s", $essential_state);
            $this->condition = $this->condition.$conditionTemp;
        }
        if($ctime && $ctime>=0)
        {
            $conditionTemp = sprintf(" and ctime>'%s'", $ctime); 
            $this->condition = $this->condition.$conditionTemp;
        }
        if($ctimeend && $ctimeend>=0)
        {
            $conditionTemp = sprintf(" and ctime<='%s'", $ctimeend." 23:59:59"); 
            $this->condition = $this->condition.$conditionTemp;
        }
        if($type && $type>=0)
        {
            $conditionTemp = sprintf(" and type='%s'", $type); 
            $this->condition = $this->condition.$conditionTemp;
        }else{
            $conditionTemp = sprintf(" and type!='1'" );                                                                          
            $this->condition = $this->condition.$conditionTemp;
        }
        if($user_id && $user_id>=0)
        {
            $conditionTemp = sprintf(" and admin_id='%s'", $user_id); 
            $this->condition = $this->condition.$conditionTemp;
        }
        if($state && $state>=0)
        {
            $conditionTemp = sprintf(" and state='%s'", $state); 
            $this->condition = $this->condition.$conditionTemp;
        }

        //
        $allnum = $Model->where($this->condition)->count();

        if($transmit_count!=-1 && $transmit_count!=0)
        {
            $strOrder = "transmit_count desc";
            $list = $Model->where($this->condition)->order($strOrder)->page($this->curpage, $this->pagenum)->select();
        }else if($recom_count!=-1 && $recom_count!=0)
        {
            $strOrder = "recom_count desc";
            $list = $Model->where($this->condition)->order($strOrder)->page($this->curpage, $this->pagenum)->select();
        }else if($comment_count!=0 && $comment_count!=-1)
        {
            $strOrder = "comment_count desc";
            $list = $Model->where($this->condition)->order($strOrder)->page($this->curpage, $this->pagenum)->select();
        }else if($viewNum!=0 && $viewNum!=-1){

            //从postview表中获取
            $orderBy = 'viewnum desc';
            $conditionTemp = sprintf(" 1=1 and ctime>'%s' and ctime<'%s' and type!=1", $ctime, $ctimeend." 23:59:59");  

            $allnum = $ModelView->where($conditionTemp)->count();

            $list = $ModelView->where($conditionTemp)->order($orderBy)->page($this->curpage, $this->pagenum)->select();
            if($list || $list == null){
                $code = 0;
                $msg = "suc";
            }else{
                $list = Array();
                $code = 10001;
                $msg = "no data";
            }

            $num = count($list);
            for($i=0; $i<$num; $i++){
                $viewnum = $list[$i]['viewnum'];

                $condition = "id='".$list[$i]['post_id']."'";
                $listPost = $Model->where($condition)->select();

                $list[$i]['view'] = $viewnum;
                $list[$i]['recom_count'] = $listPost[0]['recom_count'];
                $list[$i]['comment_count'] = $listPost[0]['comment_count'];
                $list[$i]['transmit_count'] = $listPost[0]['transmit_count'];
                $list[$i]['title'] = $listPost[0]['title'];
                $list[$i]['content'] = $listPost[0]['content'];
                $list[$i]['id'] = $listPost[0]['id'];
                $list[$i]['user_id'] = $listPost[0]['user_id'];
                $list[$i]['essential_state'] = $listPost[0]['essential_state'];
                $list[$i]['index_recom_state'] = $listPost[0]['index_recom_state'];
                $list[$i]['mince_type'] = $listPost[0]['mince_type'];
                //$list[$i]['admin_id'] = $listPost[0]['admin_id'];
                $list[$i]['source_name'] = $listPost[0]['source_name'];
                //$list[$i]['source_url'] = $listPost[0]['source_url'];
                $list[$i]['audit_state'] = $listPost[0]['audit_state'];

                //$list[$i]['source_url'] = "http://www.richba.com/article.html?id=".$listPost[0]['id'];
                $list[$i]['source_url'] = "http://www.richba.com/post/detail/".$listPost[0]['id'].".html";

                //编辑昵称
                $listInfo = $ModelInfo->where("url='".$listPost[0]['source_url']."'")->select();
                $list[$i]['admin_id'] = $listInfo[0]['user_id'] ? $listInfo[0]['user_id'] : "";
                $listAdmin = $ModelAdmin->where("user_id='".$list[$i]['admin_id']."'")->select();
                $list[$i]['adminname'] = $listAdmin[0]['chinaname'] ? $listAdmin[0]['chinaname'] : "";

                //发帖用户昵称
                $listUser = $ModelUser->where("id='".$listPost[0]['user_id']."'")->select();
                $list[$i]['username'] = $listUser[0]['name'] ? $listUser[0]['name'] : "";
            }
          //      var_dump($listPost);

            $this->ajaxOutput($code, $msg, array('count'=>$allnum, 'list'=>$list));    





            // $key = "post_view_count_total";
            // $redis = S(array('type'=>'Redis'));
            // $listTemp = $redis->zrevrange($key, $this->curpage*10 - 1, 99);

            // $listTempVal = $redis->zrevrange($key, $this->curpage*10 - 1, 99, score);
            
            // $listTempTag = $listTemp;

            // //过滤掉前端发帖
            // for ($i=0, $j=0; $i < 99 && $j <= 10; $i++) { 
            //     $strCondition = sprintf("id='%s'", $listTemp[$i]);

            //     $listTitle = $Model->where($strCondition)->select();

            //     if($listTitle){
            //         //$strCondition = sprintf("1=1 and title='%s' and ctime>'%s' and ctime<='%s'", $listTitle[0]['title'], $ctime, $ctimeend." 23:59:59");
            //         $strCondition = sprintf("title='%s'", $listTitle[0]['title']);

            //         $list = $ModelInfo->where($strCondition)->select();

            //         if($list){
            //             $listTempTag[$i] =  1;
            //             $j++;
            //         }else{
            //             $listTempTag[$i] =  0;
            //         }
            //     }
            // }
            
            
            // //过滤掉前端发帖
            // // for ($i=0, $j=0; $i < 99 && $j <= 10; $i++) { 
            // //     $strCondition = sprintf("1=1 and id='%s' and type!='1'", $listTemp[$i]);

            // //     $listTitle = $Model->where($strCondition)->select();

            // //     if($listTitle){
            // //         $listTempTag[$i] =  1;
            // //         $j++;
            // //     }else{
            // //         $listTempTag[$i] =  0;
            // //     }
            // // }
            



            // //echo "--------";
            // //var_dump($listTempTag);
            // $condition = "1=1";
            // for($i=0, $j=0; $i<count($listTemp) && $j < 10;$i++){
            //     if ($listTempTag[$i] == 1) {
            //         $tempId = $listTemp[$i];
            //         $tempNum = $listTempVal[$tempId].",";
            //         if($j == 0){
            //             $condition = $condition." and id in ('".$listTemp[$i]."',";
            //         }else if($j == $this->pagenum - 1){
            //             $condition = $condition." '".$listTemp[$i]."')";    
            //         }else{
            //             $condition = $condition." '".$listTemp[$i]."',";
            //         }
            //         $j++;
            //     }
            // }
            // $this->condition = $condition;
            
            // //echo $condition;

            // $list = $Model->where($this->condition)->select();
        }else{
            $list = $Model->where($this->condition)->order('ctime desc')->page($this->curpage, $this->pagenum)->select();
            $this->etime = microtime(true) - $this->stime;
            //echo $this->etime."\n";
        }

        $this->listData = $list;
        //echo $this->etime."\n";

        $this->getViewNumAndUrl();

        $this->getUrl();

        //var_dump($this->listData);
        $this->ajaxOutput(0, $this->msg, array('count'=>$allnum, 'list'=>$this->listData));                                                                                                                               
    }


    public function getViewNumAndUrl(){
        $ModelAdmin = M('Admin'); 
        $ModelInfo = M('NewsInfo'); 


        $key = "post_view_count_total";
        $redis = S(array('type'=>'Redis'));
        $num = count($this->listData);

        for ($i=0; $i < $num; $i++) { 

            $listTemp = $redis->ZSCORE($key, $this->listData[$i]['id']);
            $this->listData[$i]['PostId'] = $this->listData[$i]['id'];
            $this->listData[$i]['view'] = $listTemp?$listTemp:0;
             //编辑昵称
            $listInfo = $ModelInfo->where("url='".$this->listData[$i]['source_url']."'")->select();
            $this->listData[$i]['admin_id'] = $listInfo[0]['user_id'] ? $listInfo[0]['user_id'] : "";
            $listAdmin = $ModelAdmin->where("user_id='".$this->listData[$i]['admin_id']."'")->select();
            $this->listData[$i]['adminname'] = $listAdmin[0]['chinaname'] ? $listAdmin[0]['chinaname'] : "";

            //$this->listData[$i]['source_url'] = "http://www.richba.com/article.html?id=".$this->listData[$i]['id'];
        }
    }


    /**
     *
    **/
     public function getUrl(){

        $Model = M('NewsInfo');
        $ModelPost = M('Post');

        // $url = I('param.url',-1);
        // if($url && $url >= 0){
        //     $arrayUrl = explode(";", $url);
        // }

        $num = count($this->listData);
        for($i = 0; $i < $num; $i++){
            //$strCondition = sprintf("1=1 and url='%s' and title='%s'", $this->listData[$i]['source_url'], $this->listData[$i]['title']);
            $strCondition = sprintf("1=1 and url='%s'", $this->listData[$i]['source_url']);
            $list = $Model->where($strCondition)->select();

            if($list){
                $code = 0;
                $msg = "suc";
                $this->listData[$i]['strbar_id'] = $list[0]['strbar_id'];
                $this->listData[$i]['strtag_id'] = $list[0]['strtag_id'];
                $this->listData[$i]['postid'] = $this->listData[$i]['id'];
                $this->listData[$i]['id'] = $list[0]['id'];
                $this->listData[$i]['user_id'] = $list[0]['user_id']; 
            }else{//前端发帖,不写info表,导致数据无法查取,这里影响对前端发的帖子排序时的编辑
                $list = Array();
                $code = 0;
                $this->listData[$i]['strbar_id'] = "";
                $this->listData[$i]['strtag_id'] = "";
                $this->listData[$i]['postid'] = $this->listData[$i]['id'];
                $this->listData[$i]['id'] = "";
                $this->listData[$i]['user_id'] = ""; 
                //$msg = "no result";
                //$this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
            }
            $strCondition = sprintf("1=1 and source_url='%s'  and state='1'", $this->listData[$i]['source_url']);
            $list = $ModelPost->where($strCondition)->select();
            if ($list) {
                //$this->listData[$i]['source_url'] = "http://www.richba.com/article.html?id=".$list[0]['id'];
                $this->listData[$i]['source_url'] = "http://www.richba.com/post/detail/".$list[0]['id'].".html";
            }else{
                $list = Array();
                $code = 0;
                $msg = "no result";
                $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
            }
        }

        if($num == 0){
            $this->listData = Array();
        }        
    }

    /**
    * 获取用户发帖
    **/
    public function getUserPost(){

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

        $type = I('param.type',-1);
        $undel = I('param.undel',-1);
        $essential_state = I('param.essential_state',-1);
        $sticky_state = I('param.sticky_state',-1);
        $index_recom_state = I('param.index_recom_state',-1);
        $state = I('param.state',-1);
        $audit_state = I('param.audit_state',-1);

        $this->condition = "1=1 ";
        //if($type == 1){
        $type = 1;
        $this->condition = $this->condition." and type=".$type;
        //}
        if($essential_state == 1){
            $this->condition = $this->condition." and essential_state=".$essential_state;
        }
        if($sticky_state == 1){
            $this->condition = $this->condition." and sticky_state=".$sticky_state;
        }
        if($index_recom_state == 1){
            $this->condition = $this->condition." and index_recom_state=".$index_recom_state;
        }
        if($state == 0){
            $this->condition = $this->condition." and state=".$state;
        }else{
            $this->condition = $this->condition." and state='1'";
        }
        if($audit_state != 0){
            $this->condition = $this->condition." and audit_state='".$audit_state."'";
        }

        $ctime = I('param.ctime',date('Y-m-d'));
        $ctimeend = I('param.ctimeend',date('Y-m-d'));
        $Model = M('Post'); 
        $ModelUser = M('User'); 

        $conditionTemp = sprintf(" and ctime>'%s' and ctime<'%s' and admin_id ='%s'", $ctime, $ctimeend." 23:59:59", $res_isLogin);
        $conditionTempAssignNum = sprintf(" and admin_id ='%s'", $res_isLogin);

        $this->condition = $this->condition.$conditionTemp;
        $conditionTempAssignNum = $this->condition.$conditionTempAssignNum;

        $allnum = $Model->where($this->condition)->count();
        //分配池中已有未处理个数
        $assignNum = $Model->where($conditionTempAssignNum)->count();

        $list = $Model->where($this->condition)->order('ctime desc')->page($this->curpage, $this->pagenum)->select();

        if($list || $list == null){
            $code = 0;
            $msg = "suc";
            
            //分配池小于最大可分配数，补满
            if($assignNum < $this->assignNumMax && $undel == 1){
                $data['admin_id'] = $res_isLogin;
                $conditionTemp = sprintf("1=1 and ctime>'%s' and ctime<'%s' and type='1' and state='1' and admin_id =''", $ctime, $ctimeend." 23:59:59");
                $listAssign = $Model->where($conditionTemp)->order('ctime desc')->limit($this->assignNumMax-$assignNum)->select();

                if ($listAssign) {
                    for ($i=0; $i < count($listAssign); $i++) {
                        $listTemp = $Model->where(" 1=1 and id='".$listAssign[$i]['id']."'")->save($data);
                    }
                }
            }

            $key ="post_view_count_total";
            $redis = S(array('type'=>'Redis'));
            for($i=0; $i<$assignNum && $i<count($list); $i++){
                //帖子总浏览次数
                $listTemp = $redis->ZSCORE($key, $list[$i]['id']);
                $list[$i]['view'] = $listTemp ? $listTemp : 0;

                //发帖用户昵称
                $listUser = $ModelUser->where("id='".$list[$i]['user_id']."'")->select();
                $list[$i]['username'] = $listUser[0]['name'] ? $listUser[0]['name'] : "";

                //内容转义
                //$list[$i]['content'] = html_entity_decode ($list[$i]['content']);
            }
            if($list == null){
                $list = Array();
            }
        }else{
            $list = Array();
            $code = 10001;
            $msg = "no data";
        }

        $this->ajaxOutput($code, $msg, array('count'=>$allnum, 'list'=>$list));

    }


    /**
    * 获取所有帖子(按时间[默认]/评论数/推荐数)
    **/
    public function getAllUserPost() {
        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }
        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);

        $ctime = I('param.ctime',date('Y-m-d'));
        $ctimeend = I('param.ctimeend',date('Y-m-d'));

        $type = I('param.type',-1);
        $comment = I('param.comment',-1);
        $recom = I('param.recom',-1);
        $view = I('param.view',-1);
        $Model = M('Post'); 
        $ModelView = M('PostView');
        $ModelAdmin = M('Admin');
        $ModelUser = M('User');

        $conditionTemp = sprintf(" 1=1 and ctime>'%s' and ctime<'%s' and type=1", $ctime, $ctimeend." 23:59:59");

        //分类全部查看
        if ($type == 0) {
            $conditionTemp = $conditionTemp." and audit_state=1 and state!=0";
        }else if ($type == 1) {
            $conditionTemp = $conditionTemp." and essential_state=1 and state!=0";
        }else if ($type == 2) {
            $conditionTemp = $conditionTemp." and sticky_state=1 and state!=0";
        }else if ($type == 3) {
            $conditionTemp = $conditionTemp." and index_recom_state=1 and state!=0";
        }else if ($type == 4) {
            $conditionTemp = $conditionTemp." and state=0";
        }else if ($type == 5) {
            $conditionTemp = $conditionTemp." and audit_state=2 and state!=0";
        }


        $orderBy = '';
        if($comment == 1){
            $orderBy = 'comment_count desc';
        }else if($recom == 1){
            $orderBy = 'recom_count desc';
        }else if($view == 1){
            //从postview表中获取
            $orderBy = 'viewnum desc';
            $conditionTemp = sprintf(" 1=1 and ctime>'%s' and ctime<'%s' and type=1", $ctime, $ctimeend." 23:59:59");  

            $allnum = $ModelView->where($conditionTemp)->count();

            $list = $ModelView->where($conditionTemp)->order($orderBy)->page($this->curpage, $this->pagenum)->select();
            if($list || $list == null){
                $code = 0;
                $msg = "suc";
            }else{
                $list = Array();
                $code = 10001;
                $msg = "no data";
            }

            $num = count($list);
            for($i=0; $i<$num; $i++){
                $viewnum = $list[$i]['viewnum'];

                $condition = "id='".$list[$i]['post_id']."'";
                $listPost = $Model->where($condition)->select();

                $list[$i]['view'] = $viewnum;
                $list[$i]['recom_count'] = $listPost[0]['recom_count'];
                $list[$i]['comment_count'] = $listPost[0]['comment_count'];
                $list[$i]['title'] = $listPost[0]['title'];
                $list[$i]['content'] = $listPost[0]['content'];
                $list[$i]['id'] = $listPost[0]['id'];
                $list[$i]['user_id'] = $listPost[0]['user_id'];
                $list[$i]['essential_state'] = $listPost[0]['essential_state'];
                $list[$i]['index_recom_state'] = $listPost[0]['index_recom_state'];
                $list[$i]['mince_type'] = $listPost[0]['mince_type'];
                $list[$i]['admin_id'] = $listPost[0]['admin_id'];
                $list[$i]['source_name'] = $listPost[0]['source_name'];
                $list[$i]['source_url'] = $listPost[0]['source_url'];
                $list[$i]['audit_state'] = $listPost[0]['audit_state'];

                //编辑昵称
                $listAdmin = $ModelAdmin->where("user_id='".$listPost[0]['admin_id']."'")->select();
                $list[$i]['adminname'] = $listAdmin[0]['chinaname'] ? $listAdmin[0]['chinaname'] : "";

                //发帖用户昵称
                $listUser = $ModelUser->where("id='".$listPost[0]['user_id']."'")->select();
                $list[$i]['username'] = $listUser[0]['name'] ? $listUser[0]['name'] : "";
            }

            $this->ajaxOutput($code, $msg, array('count'=>$allnum, 'list'=>$list));    

        }else{
            $orderBy = 'ctime desc';
        }

        $allnum = $Model->where($conditionTemp)->count();
        $list = $Model->where($conditionTemp)->order($orderBy)->page($this->curpage, $this->pagenum)->select();
        if($list || $list == null){
            $code = 0;
            $msg = "suc";
            if ($list == null) {
                $list = Array();
            }
        }else{
            $list = Array();
            $code = 10001;
            $msg = "no data";
        }

        $key ="post_view_count_total";
        $redis = S(array('type'=>'Redis'));
        for($i=0; $i<count($list); $i++){
            //帖子总浏览次数
            $listTemp = $redis->ZSCORE($key, $list[$i]['id']);
            $list[$i]['view'] = $listTemp ? $listTemp : 0;

            //编辑昵称
            $listAdmin = $ModelAdmin->where("user_id='".$list[$i]['admin_id']."'")->select();
            $list[$i]['adminname'] = $listAdmin[0]['chinaname'] ? $listAdmin[0]['chinaname'] : "";

            //发帖用户昵称
            $listUser = $ModelUser->where("id='".$list[$i]['user_id']."'")->select();
            $list[$i]['username'] = $listUser[0]['name'] ? $listUser[0]['name'] : "";

            //内容转义
            //$list[$i]['content'] = html_entity_decode ($list[$i]['content']);
        }
        

        $this->ajaxOutput($code, $msg, array('count'=>$allnum, 'list'=>$list));    
    }


    /**
    * 获取首页推荐
    **/
    public function getIndexRecom(){

        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);

        $type = I('param.type',-1);
        $index_recom_state = I('param.index_recom_state',-1);
        if($type == 1){
            $this->condition = "1=1 and type=".$type;
        }else if($index_recom_state == 1){
            $this->condition = "1=1 and index_recom_state=".$index_recom_state;
        }
        
        $ctime = I('param.ctime',date('Y-m-d'));
        $ctimeend = I('param.ctimeend',date('Y-m-d'));
        $Model = M('Post'); 
        $ModelUser = M('User'); 

        $conditionTemp = sprintf(" and ctime>'%s' and ctime<'%s' and state='1'", $ctime, $ctimeend." 23:59:59");
        $this->condition = $this->condition.$conditionTemp;

        $numall = $Model->where($this->condition)->count();

        $list = $Model->where($this->condition)->order('ctime desc')->page($this->curpage, $this->pagenum)->select();
        if($list){
            $code = 0;
            $msg = "suc";

            $key ="post_view_count_total";
            $redis = S(array('type'=>'Redis'));
            for($i=0; $i<count($list); $i++){
                //帖子总浏览次数
                $listTemp = $redis->ZSCORE($key, $list[$i]['id']);
                $list[$i]['view'] = $listTemp ? $listTemp : 0;

                //发帖用户昵称
                $listUser = $ModelUser->where("id='".$list[$i]['user_id']."'")->select();
                $list[$i]['username'] = $listUser[0]['name'] ? $listUser[0]['name'] : "";

                //内容转义
                //$list[$i]['content'] = html_entity_decode ($list[$i]['content']);
            }
        }else{
            $list = Array();
            $code = 10001;
            $msg = "no data";
        }

        $this->ajaxOutput($code, $msg, array('count'=>$numall, 'list'=>$list));

    }


    /**
    * 加精帖子
    **/
    public function setEssential(){

        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $LData['table_name'] = "Post";
        $LData['type'] = 1;
        $LData['admin_id'] = $res_isLogin;

        $pageName = I('param.pagename',-1);
        if($pageName != "-1"){
            $is_allow = pageAuthority($pageName, $res_isLogin);
            if($is_allow != "1"){
                $this->ajaxOutput(20402, "limit ", array('list'=>Array()));
            }
        }

        $id = I('param.id',-1);
        $Model = M('Post'); 
        if($id){
            $list = $Model->where("id='".$id."'")->select();
            if($list){
                $to_user_id = $list[0]['user_id'];
                $data['essential_state'] = ($list[0]['essential_state']+1)%2;
                if ($data['essential_state'] == 1) {
                    $data['hot_state'] = 1;
                }
                //$data['audit_state'] = '2';
                $SList = $Model->where("id='".$id."'")->save($data);
                if($SList){
                    $code = 0;
                    $msg = "suc";

                    $LData['msg'] = "用户发帖: id ".$id." title ".$list[0]['title']."  取消精华帖设置";
                    if ($data['essential_state'] == 1) {
                        // 发送站内信
                        $user_id = $res_isLogin;
                        $this->sendLetters("您的发帖已被我们加精", $user_id, $to_user_id);

                        $LData['msg'] = "用户发帖: id ".$id." title ".$list[0]['title']."  设置为精华帖";
                    }else if($data['essential_state'] == 0){
                        //取消精华时，判断热帖
                        $this->isHotPost($id);
                    }

                }else{
                    $list = Array();
                    $code = 10001;
                    $msg = "no data";

                    $LData['code'] = $code;
                    $LData['msg'] = "用户发帖: id ".$id." title ".$list[0]['title']."  不存在, 设置为精华帖失败";
                }
                
            }else{
                $list = Array();
                $code = 10001;
                $msg = "no data";

                $LData['code'] = $code;
                $LData['msg'] = "用户发帖: id ".$id." title ".$list[0]['title']." 不存在, 查找用户贴失败";
            }
        }else{
            $list = Array();
            $code = 20403;
            $msg = "无帖子ID参数";
            
            $LData['code'] = $code;
            $LData['msg'] = "用户发帖: id ".$id." 参数错误,设置为精华帖失败";
        }
        aLog($LData);
        $this->ajaxOutput($code, $msg, array('list'=>$list));
    }


    /**
    * 置顶帖子
    **/
    public function setSticky(){

        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $id = I('param.id',-1);
        $Model = M('Post'); 
        if($id){
            $list = $Model->where("id='".$id."'")->select();
            if($list){
                //$data['audit_state'] = '2';
                $data['sticky_state'] = ($list[0]['sticky_state']+1)%2;
                if ($data['sticky_state'] == 1) {
                    $data['hot_state'] = 1;
                }
                
                $list = $Model->where("id='".$id."'")->save($data);
                if($list){
                    $code = 0;
                    $msg = "suc";
                }else{
                    $list = Array();
                    $code = 10001;
                    $msg = "no data";
                }
            }else{
                $list = Array();
                $code = 10001;
                $msg = "no data";
            }
        }else{
            $list = Array();
            $code = 20403;
            $msg = "post id wrong";
        }

        //取消置顶时，判断热帖
        if ($code == 0 && $data['sticky_state'] == 0) {
            $this->isHotPost($id);
        }

        $this->ajaxOutput($code, $msg, array('list'=>$list));
    }

    public function isHotPost($id){
        $Model = M('Post');
        $ModelV = M('VUser');
        $list = $Model->master(true)->where("id='".$id."'")->select();
        if ($list[0]['sticky_state'] == 0 && $list[0]['essential_state'] == 0) {
            //查看该贴是否为v用户发帖
            $VList = $ModelV->master(true)->where("user_id='".$list[0]['user_id']."' and v_state='1' and ctime<='".$list[0]['modify_time']."' and etime>='".$list[0]['modify_time']."'")->limit(1)->select();
            if ($VList && $VList != null) {
                //发帖时用户为v，依然为热帖
            }else{
                $PData['hot_state'] = 0;
                $list = $Model->where("id='".$id."'")->save($PData);
            }
        }
    }


    /**
    * 首推帖子
    **/
    public function setToIndex(){

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
        $Model = M('Post'); 
        if($id){
            $list = $Model->where("id='".$id."'")->select();
            if($list){
                //$data['audit_state'] = '2';
                $to_user_id = $list[0]['user_id'];
                $data['index_recom_state'] = ($list[0]['index_recom_state']+1)%2;
                $list = $Model->where("id='".$id."'")->save($data);
                if($list){
                    $code = 0;
                    $msg = "suc";

                    if($data['index_recom_state'] == 1){
                       // 发送站内信
                        $user_id = $res_isLogin;
                        $this->sendLetters("您的发帖已经被我们首页推荐，再接再厉！", $user_id, $to_user_id);
                    }                    
                }else{
                    $list = Array();
                    $code = 10001;
                    $msg = "no data";
                }
            }else{
                $list = Array();
                $code = 10001;
                $msg = "no data";
            }
        }else{
            $list = Array();
            $code = 20403;
            $msg = "post id wrong";
        }

        $this->ajaxOutput($code, $msg, array('list'=>$list));
    }


    public function sendLetters ($title, $user_id, $to_user_id) {

        $LetterInfo = D('LetterInfo');
        $dataLetter['opt'] = "";
        
        $dataLetter['title'] = $title;
        $dataLetter['content'] = $title;

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
                    $this->ajaxOutput($code, $msg, array('count'=>0, 'list'=>array()));
                }
            }
        }
    }

    /**
    * 通过审核
    **/
    public function setAudit(){

        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $id = I('param.id',-1);
        $Model = M('Post'); 
        if($id){
            $list = $Model->where("id='".$id."'")->select();
            if($list){
                $data['audit_state'] = I('param.audit_state',-1);
                if($data['audit_state'] == "0"){
                    $data['audit_state'] = 1;
                }else if($data['audit_state'] == "1"){
                    $data['audit_state'] = 2;
                }else if($data['audit_state'] == "2"){
                    $data['audit_state'] = 0;
                }else{
                    $data['audit_state'] = 1;
                }
                $list = $Model->where("id='".$id."'")->save($data);
                if($list){
                    $code = 0;
                    $msg = "suc";
                }else{
                    $list = Array();
                    $code = 0;
                    $msg = "save no data";
                }
            }else{
                $list = Array();
                $code = 10001;
                $msg = "no data";
            }
        }else{
            $list = Array();
            $code = 20403;
            $msg = "post id wrong";
        }

        $this->ajaxOutput($code, $msg, array('list'=>$list));
    }

    /**
    * 删除帖子
    **/
    public function delUserPost(){

        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $id = I('param.id',-1);
        $state = I('param.state',-1);
        $audit_state = I('param.audit_state',-1);
        $Model = M('Post'); 
        $ModelBar = M('PostBar');
        if($id){
            $data['audit_state'] = $audit_state;
            $data['state'] = $state;
            
            if($state == 0){
                $addValue = -1;
            }else if($state == 1){
                $addValue = 1;
            }

            $listGet = $Model->where("id='".$id."'")->select();
            if($listGet){
                    $code = 0;
                    $msg = $msg." suc";
                    $this->reIndexStock($id, $listGet[0]['user_id']);

    /*
                    //更新今日股吧。历史股吧帖子总数， 删除post_bar关联
                    $listTemp = $ModelBar->where("post_id='".$id."'")->select();
                    $msg = $msg." id=".$id." listTemp:".count($listTemp)." ";
                    if($listTemp){
                        $num = count($listTemp);
                        $redis = S(array('type'=>'Redis'));  
                        $keyBarAll ="bar_post_count_total";
                        $keyBar ="bar_post_count_".date('ymd');
                        for($i = 0; $i < $num; $i++){ 
                            //更新当日吧贴数
                            $listTemp = $redis->zIncrBy($keyBar, $addValue, $listTemp[$i]['bar_id']);
                            //更新总吧贴数
                            $listTempAll = $redis->zIncrBy($keyBarAll, $addValue, $listTemp[$i]['bar_id']);  
                        }     
                    }
                    $listTemp = $ModelBar->where("post_id='".$id."'")->delete();
                    */
            }else{
                $list = Array();
                $code = 10001;
                $msg = $msg." no data";
            }
            
        }else{
            $list = Array();
            $code = 20403;
            $msg = $msg." post id wrong";
        }

        $this->ajaxOutput($code, $msg, array('list'=>$listGet));
    }

    /**
    * 取消首页推荐
    **/
    public function cancelIndexRecom(){

        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $id = I('param.id',-1);
        $Model = M('Post'); 
        if($id){
            $data['index_recom_state'] = "0";
            $list = $Model->where("id='".$id."'")->save($data);
            if($list){
                $code = 0;
                $msg = "suc";
            }else{
                $list = Array();
                $code = 10001;
                $msg = "no data";
            }
        }else{
            $list = Array();
            $code = 20403;
            $msg = "post id wrong";
        }

        $this->ajaxOutput($code, $msg, array('list'=>$list));
    }

   //吧贴管理按用户查找
    public function search(){

        $value = I('param.value',-1);
        $Model = M('Post');
        $ModelUser = M('User');  
        $ModelAdmin = M('Admin');  
            
        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);

        $ctime = I('param.ctime',date('Y-m-d'));
        $ctimeend = I('param.ctimeend',date('Y-m-d'));

        $type = I('param.type',-1);
        $undel = I('param.undel',-1);
        $essential_state = I('param.essential_state',-1);
        $sticky_state = I('param.sticky_state',-1);
        $index_recom_state = I('param.index_recom_state',-1);
        $state = I('param.state',-1);
        $audit_state = I('param.audit_state',-1);

        $this->condition = "1=1 ";
        //if($type == 1){
        $type = 1;
        $this->condition = $this->condition." and type=".$type;
        //}
        if($essential_state == 1){
            $this->condition = $this->condition." and essential_state=".$essential_state;
        }
        if($sticky_state == 1){
            $this->condition = $this->condition." and sticky_state=".$sticky_state;
        }
        if($index_recom_state == 1){
            $this->condition = $this->condition." and index_recom_state=".$index_recom_state;
        }
        if($state == 0){
            $this->condition = $this->condition." and state=".$state;
        }else{
            $this->condition = $this->condition." and state='1'";
        }
        if($audit_state != 0){
            $this->condition = $this->condition." and audit_state='".$audit_state."'";
        }

        if($ctime){
          $this->condition = $this->condition." and ctime>='".$ctime."'";
        }

        if($ctimeend){
          $this->condition = $this->condition." and ctime<='".$ctimeend." 23:59:59'";
        }


        //user表查发帖用户id
        $listTemp = $ModelUser->where("name='".$value."'")->select();
        if($listTemp){
            $code = 0;
            $msg = "suc";
            
            $num = $Model->where("user_id='".$listTemp[0]['id']."'")->count();
            //post表插用户所有贴， admin处理人
            $this->condition = $this->condition." and user_id='".$listTemp[0]['id']."'";
            $list = $Model->where($this->condition)->page($this->curpage, $this->pagenum)->select();
            if($list){
                $code = 0;
                $msg = "suc";

                for($i=0; $i < count($list); $i++){
                    $list[$i]['username'] = $listTemp[0]['name'];
                    $list[$i]['adminname'] = "";

                //    print_r($list);
                    //在user表中获得admin名称
                    if($list[$i]['admin_id']){
                        //echo $list[$i]['admin_id']." |";
                        $listAdmin = $ModelAdmin->where("user_id='".$list[$i]['admin_id']."'")->select();
                        //echo "admin:".$listAdmin[0]['chinaname']."| ";
                        if($listAdmin){
                             $list[$i]['adminname'] = $listAdmin[0]['chinaname'];
                        }

                        $key ="post_view_count_total";
                        $redis = S(array('type'=>'Redis'));
                        
                        //帖子总浏览次数
                        $viewNum = $redis->ZSCORE($key, $list[$i]['id']);
                        $list[$i]['view'] = $viewNum ? $viewNum : 0;
                        $listData[$i] = $list[$i];
                    }
                    
                }
            }else if($list == null){
                $list = Array();
                $code = 0;
                $msg = "no data";
            }else{
                $list = Array();
                $code = 10001;
                $msg = "search error";
            }

        }else if($listTemp == null){
            $list = Array();
            $code = 0;
            $msg = "no data";
        }else {
            $list = Array();
            $code = 10001;
            $msg = "search error";
        }

        $this->ajaxOutput($code, $msg, array('count'=>count($list),'list'=>$list));    
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
