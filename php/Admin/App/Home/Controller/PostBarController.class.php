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

    public function sortNewsInfo(){

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
            $conditionTemp = sprintf(" and user_id='%s'", $user_id); 
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
        }else if($viewNum!=0 && $viewNum!=-1)
        {
            $key = "post_view_count_total";
            $redis = S(array('type'=>'Redis'));
            $listTemp = $redis->zrevrange($key, $this->curpage*10 - 1, 99);

            $listTempVal = $redis->zrevrange($key, $this->curpage*10 - 1, 99, score);
            
            $listTempTag = $listTemp;

            //过滤掉前端发帖
            for ($i=0, $j=0; $i < 99 && $j <= 10; $i++) { 
                $strCondition = sprintf("id='%s'", $listTemp[$i]);

                $listTitle = $Model->where($strCondition)->select();

                if($listTitle){
                    //$strCondition = sprintf("1=1 and title='%s' and ctime>'%s' and ctime<='%s'", $listTitle[0]['title'], $ctime, $ctimeend." 23:59:59");
                    $strCondition = sprintf("title='%s'", $listTitle[0]['title']);

                    $list = $ModelInfo->where($strCondition)->select();

                    if($list){
                        $listTempTag[$i] =  1;
                        $j++;
                    }else{
                        $listTempTag[$i] =  0;
                    }
                }
            }
            
            
            //过滤掉前端发帖
            // for ($i=0, $j=0; $i < 99 && $j <= 10; $i++) { 
            //     $strCondition = sprintf("1=1 and id='%s' and type!='1'", $listTemp[$i]);

            //     $listTitle = $Model->where($strCondition)->select();

            //     if($listTitle){
            //         $listTempTag[$i] =  1;
            //         $j++;
            //     }else{
            //         $listTempTag[$i] =  0;
            //     }
            // }
            



            //echo "--------";
            //var_dump($listTempTag);
            $condition = "1=1";
            for($i=0, $j=0; $i<count($listTemp) && $j < 10;$i++){
                if ($listTempTag[$i] == 1) {
                    $tempId = $listTemp[$i];
                    $tempNum = $listTempVal[$tempId].",";
                    if($j == 0){
                        $condition = $condition." and id in ('".$listTemp[$i]."',";
                    }else if($j == $this->pagenum - 1){
                        $condition = $condition." '".$listTemp[$i]."')";    
                    }else{
                        $condition = $condition." '".$listTemp[$i]."',";
                    }
                    $j++;
                }
            }
            $this->condition = $condition;
            
            //echo $condition;

            $list = $Model->where($this->condition)->select();
        }else{
            $list = $Model->where($this->condition)->order('ctime desc')->page($this->curpage, $this->pagenum)->select();
        }

        $this->listData = $list;

        $this->getViewNum();

        $this->getUrl();

        

        //var_dump($this->listData);
        $this->ajaxOutput(0, $this->msg, array('count'=>$allnum, 'list'=>$this->listData));                                                                                                                               
    }


    public function getViewNum(){

        $key = "post_view_count_total";
        $redis = S(array('type'=>'Redis'));
        $num = count($this->listData);

        for ($i=0; $i < $num; $i++) { 

             $listTemp = $redis->ZSCORE($key, $this->listData[$i]['id']);
             $this->listData[$i]['PostId'] = $this->listData[$i]['id'];
             $this->listData[$i]['viewNum'] = $listTemp?$listTemp:0;
        }

        //按浏览数排
        for($i = 0; $i < $num - 1; $i++) {
            $max = $this->listData[$i]['viewNum'];
            $tag = $i;
            for($j = $i + 1; $j < $num; $j++){
                $b = $this->listData[$j]['viewNum'];
                if($max < $b){
                    $tag = $j;
                    $max = $b;
                }
            }
            $temp = $this->listData[$tag];
            $this->listData[$tag] = $this->listData[$i];
            $this->listData[$i] = $temp;
        } 
        
    }


    /**
     *
    **/
     public function getUrl(){
        //实例化一个模型
        $Model = M('NewsInfo');
        $ModelPost = M('Post');

        //读取10条数据（更多带条件的读取方法参见文档》模型》CURD操作说明）
        $url = I('param.url',-1);
        if($url && $url >= 0){
            $arrayUrl = explode(";", $url);
        }

        $num = count($this->listData);
        for($i = 0; $i < $num; $i++){
            //$strCondition = sprintf("1=1 and url='%s' and title='%s'", $this->listData[$i]['source_url'], $this->listData[$i]['title']);
            $strCondition = sprintf("1=1 and title='%s'", $this->listData[$i]['title']);
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
            $strCondition = sprintf("1=1 and source_url='%s'", $this->listData[$i]['source_url']);
            $list = $ModelPost->where($strCondition)->select();
            if ($list) {
                $this->listData[$i]['source_url'] = "http://www.richba.com/article.html?id=".$list[0]['id'];
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
        if($type == 1){
            $this->condition = $this->condition." and type=".$type;
        }
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
        $this->condition = $this->condition.$conditionTemp;
        $allnum = $Model->where($this->condition)->count();
        $list = $Model->where($this->condition)->order('ctime desc')->page($this->curpage, $this->pagenum)->select();
        //echo count($list);
        //echo $this->condition;

        if($list || $list == null){
            $code = 0;
            $msg = "suc";
            $assignNum = 0;
            if($list){
               $assignNum = count($list); 
            }
            

            //echo "ass:".$assignNum;
            //echo $assignNum;
            if($assignNum < $this->assignNumMax && $undel == 1){
                $data['admin_id'] = $res_isLogin;
                $conditionTemp = sprintf("1=1 and ctime>'%s' and ctime<'%s' and type='1' and state='1' and admin_id =''", $ctime, $ctimeend." 23:59:59");
                $listAssign = $Model->where($conditionTemp)->order('ctime desc')->limit($this->assignNumMax-$assignNum)->select();
                //echo $conditionTemp;
                //echo count($listAssign);
                if ($listAssign) {
                    for ($i=0; $i < count($listAssign); $i++) {
                        $listTemp = $Model->where(" 1=1 and id='".$listAssign[$i]['id']."'")->save($data);
                    }
                }
            }

            $key ="post_view_count_total";
            $redis = S(array('type'=>'Redis'));
            for($i=0; $i<$assignNum; $i++){
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

        $comment = I('param.comment',-1);
        $recom = I('param.recom',-1);
        $Model = M('Post'); 
        $ModelAdmin = M('Admin');
        $ModelUser = M('User');

        $conditionTemp = sprintf(" 1=1 and ctime>'%s' and ctime<'%s' and type=1 and state!=0", $ctime, $ctimeend." 23:59:59");
        $orderBy = '';

        if($comment == 1){
            $orderBy = 'comment_count desc';
        }else if($recom == 1){
            $orderBy = 'recom_count desc';
        }else{
            $orderBy = 'ctime desc';
        }

        $allnum = $Model->where($conditionTemp)->count();
        $list = $Model->where($conditionTemp)->order($orderBy)->page($this->curpage, $this->pagenum)->select();
        if($list || $list == null){
            $code = 0;
            $msg = "suc";
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

        $id = I('param.id',-1);
        $Model = M('Post'); 
        if($id){
            $list = $Model->where("id='".$id."'")->select();
            if($list){
                $data['essential_state'] = ($list[0]['essential_state']+1)%2;
                //$data['audit_state'] = '2';
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

        $this->ajaxOutput($code, $msg, array('list'=>$list));
    }


    /**
    * 首推帖子
    **/
    public function setToIndex(){

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
                $data['index_recom_state'] = ($list[0]['index_recom_state']+1)%2;
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

        $this->ajaxOutput($code, $msg, array('list'=>$list));
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

    //
    public function search(){

        $value = I('param.value',-1);
        $Model = M('Post');
        $ModelUser = M('User');  
        $ModelAdmin = M('Admin');  
            
        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        
        //user表查发帖用户id
        $listTemp = $ModelUser->where("name='".$value."'")->select();
        if($listTemp){
            $code = 0;
            $msg = "suc";
            
            $num = $Model->where("user_id='".$listTemp[0]['id']."'")->count();
            //post表插用户所有贴， admin处理人
            $list = $Model->where("user_id='".$listTemp[0]['id']."'")->page($this->curpage, $this->pagenum)->select();
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

        $this->ajaxOutput($code, $msg, array('count'=>$num,'list'=>$list));    
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
