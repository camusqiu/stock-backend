<?php
namespace Home\Controller;
use Think\Controller;


class NewsInfoController extends CommonController {
//class NewsInfoController extends Controller {
    private $num = 30;

    private $condition = "1=1";
    private $id= "";
    private $iType = "";
    private $curpage = 1;
    private $pagenum = 10;
    private $type = "";
    private $title = "";
    private $url= "";
    private $time = "";
    private $ctime = "";
    private $ctimeend = "";
    private $state= "";
    private $source= "";
    private $item= "";
    private $content= "";
    private $retreat= "";
    private $data= "";
    
    private $sticky_state= "";
    private $essential_state= "";
    private $index_recom_state= "";
    private $strtag_id= "";
    private $strbar_id= "";
    private $strbar_id_hide= "";
    private $user_id= "";
    
    /*
    private $data = array(
                'code' => 0,
                'msg' => "",
                'count' => 0,
                'data' => null
            );
    */
     
    /**
    *  获取请求参数   
    **/
    //public function getReqParam(&$id, &$type, &$title, &$state, &$time, &$ctime, &$source, &$sticky_state, &$essential_state,  &$index_recom_state, &$strtag_id, $strbar_id, &$content, &$user_id, $curpage, &$pagenum, &$item){
    public function getReqParam(){    
    /*
        $idTemp = I('param.id',-1);
        if($idTemp>=0){
            $this->id = $idTemp;
            $this->item["id"] = $idTemp;
        }
     */   
        $typeTemp = I('param.type',-1);
        if($typeTemp>=0){
            $this->type = $typeTemp;
            $this->item["type"] = $typeTemp;
        }
        
        $titleTemp = I('param.title',-1);
        if($titleTemp>=0){
            $this->title = $titleTemp;
            $this->item["title"] = $titleTemp;
        }

        $urlTemp = I('param.url',-1);
        if($urlTemp>=0){
            $this->url = $urlTemp;
            $this->item["url"] = $urlTemp;
        }
        
        $stateTemp = I('param.state',-1);
        if($stateTemp>=0){
            $this->state = $stateTemp;
            $this->item["state"] = $stateTemp;
        }
        
        $timeTemp = I('param.time',-1);
        if($timeTemp>=0){
            $this->time = $timeTemp;
            $this->item["time"] = $timeTemp;
        }
        
        $ctimeTemp = I('param.ctime',-1);
        if($ctimeTemp>=0){
            $this->ctime = $ctimeTemp;
            $this->item["ctime"] = $ctimeTemp;
        }

        $ctimeendTemp = I('param.ctimeend',-1);
        if($ctimeendTemp>=0){
            $this->ctimeend = $ctimeendTemp;
            $this->item["ctimeend"] = $ctimeendTemp;
        }
        
        $sourceTemp = I('param.source',-1);
        if($sourceTemp>=0){
            $this->source = $sourceTemp;
            $this->item["source"] = $sourceTemp;
        }
        
        $content = I('param.content',-1);
        if($content>=0){
            $this->content = $content;
            $this->item["content"] = $content;
        }

        $retreat = I('param.retreat',-1);
        if($this->iType === "release"){
            $this->retreat = 0;
            $this->item["retreat"] = 0;
        }else if($retreat==1 || $retreat==0){
            $this->retreat = $retreat;
            $this->item["retreat"] = $retreat;
        }
        
        $curpage = I('param.curpage',-1);
        if($curpage>=0){
            $this->curpage = $curpage;
            $this->item["curpage"] = $curpage;
        }
        
        $pagenum = I('param.pagenum',-1);
        if($pagenum>=0){
            $this->pagenum = $pagenum;
            $this->item["pagenum"] = $pagenum;
        }
        
        $sticky_state_Temp = I('param.sticky_state',-1);
        if($sticky_state_Temp>=0){
            $this->sticky_state = $sticky_state_Temp;
            $this->item["sticky_state"] = $sticky_state_Temp;
        }
        
        $essential_state_Temp = I('param.essential_state',-1);
        if($essential_state_Temp>=0){
            $this->essential_state = $essential_state_Temp;
            $this->item["essential_state"] = $essential_state_Temp;
        }
        
        $index_recom_state_Temp = I('param.index_recom_state',-1);
        if($index_recom_state_Temp>=0){
            $this->index_recom_state = $index_recom_state_Temp;
            $this->item["index_recom_state"] = $index_recom_state_Temp;
        }
        
        $strtag_id_Temp = I('param.strtag_id',-1);
        if($strtag_id_Temp>=0){
            $this->strtag_id = $strtag_id_Temp;
            $this->item["strtag_id"] = $strtag_id_Temp;
        }

        $strbar_id_Temp = I('param.strbar_id',-1);
        if($strbar_id_Temp>=0){
            $this->strbar_id = $strbar_id_Temp;
            $this->item["strbar_id"] = $strbar_id_Temp;
            //$this->strbar_id = str_replace(';', '/', $strbar_id_Temp);
            //$this->item["strbar_id"] = str_replace(';', '/', $strbar_id_Temp);
        }


        $user_id_Temp = I('param.user_id',-1);
        if($user_id_Temp>=0){
            $this->user_id = $user_id_Temp;
            $this->item["user_id"] = $user_id_Temp;
        }
    }
    /**
     * 获取增删改条件
     **/
    //public function getConditon($id, $type, $title, $state, $ctime, $source, $user_id, &$condition, &$iType){
    public function getConditon(){
        $this->id = I('param.id');
        if($this->id)
        {
            $conditionTemp = sprintf(" and id=%s", $this->id);
            $this->condition = $this->condition.$conditionTemp;
        }
        if($this->iType === "get" || $this->iType === "search")
        {
            if($this->type)
            {
                $conditionTemp = sprintf(" and type=%s", $this->type);
                $this->condition = $this->condition.$conditionTemp;
            }

            if ($this->state == "6") {// 处理
                $conditionTemp = sprintf(" and state!='0'");
                $this->condition = $this->condition.$conditionTemp;
            }else if($this->state>=0 && $this->state != ""){
                $conditionTemp = sprintf(" and state=%s", $this->state);
                $this->condition = $this->condition.$conditionTemp;
            }

            if($this->ctime)
            {
                if($this->ctimeend){
                    $conditionTemp = sprintf(" and ctime>'%s' and ctime<'%s'", $this->ctime, $this->ctimeend." 23:59:59"); 
                }else{
                    $conditionTemp = sprintf(" and ctime>'%s' and ctime<'%s'", $this->ctime, $this->ctime." 23:59:59"); 
                }    
                $this->condition = $this->condition.$conditionTemp;
            }
            if($this->source)
            {
                $conditionTemp = sprintf(" and source='%s'", $this->source);
                $this->condition = $this->condition.$conditionTemp;
            }
            if($this->user_id)
            {
                $conditionTemp = sprintf(" and user_id='%s'", $this->user_id);
                $this->condition = $this->condition.$conditionTemp;
            }
            if($this->essential_state)
            {
                $conditionTemp = sprintf(" and essential_state='%s'", $this->essential_state);
                $this->condition = $this->condition.$conditionTemp;
            }
            if($this->iType === "get"){
                 if($this->title)
                {
                    $conditionTemp = sprintf(" and title='%s'", $this->title);
                    $this->condition = $this->condition.$conditionTemp;
                }   
            }else if($this->iType === "search"){
                if($this->title)
                {
                    $conditionTemp = sprintf(" and title like %s%s%s", "'%", $this->title, "%'");
                    $this->condition = $this->condition.$conditionTemp;
                }
            }

        }
    }
    
    /**
     * 获取资讯信息
     **/
    public function getAddData(&$item, &$data, &$itemTemp){
        // $data['user_id'] = $itemTemp['user_id'];                          //编辑id
        
        //资讯标题
        if($this->item['title']){
            $this->data['title'] = $this->item['title'];
        }else{
            $this->data['title'] = $itemTemp['title'];
        }
        
        //资讯分类
        if($this->item['type']){
            $this->data['type'] = $this->item['type'];
        }else{
            $this->data['type'] = $itemTemp['type'];
        }
        
        //是否首页推荐
        if($this->item['index_recom_state']){
            $this->data['index_recom_state'] = $this->item['index_recom_state'];
        }else{
            $this->data['index_recom_state'] = $itemTemp['index_recom_state'];
        }
        
        //是否精华帖
        if($this->item['essential_state']){
            $this->data['essential_state'] = $this->item['essential_state'];
        }else{
            $this->data['essential_state'] = $itemTemp['essential_state'];
        }
        
        //是否置顶
        if($this->item['sticky_state']){
            $this->data['sticky_state'] = $this->item['sticky_state'];
        }else{
            $this->data['sticky_state'] = $itemTemp['sticky_state'];
        }
        
        //来源网站名称
        if($this->item['source_name']){
            $this->data['source_name'] = $this->item['source_name'];
        }else{
            $this->data['source_name'] = $itemTemp['source'];
        }
        
        //资讯来源地址
        if($this->item['source_url']){
            $this->data['source_url'] = $this->item['source_url'];
        }else{
            $this->data['source_url'] = $itemTemp['url'];
        }
        
        //默认审核通过
        $this->data['audit_state'] = 1;

        //admin
        if($this->item['user_id']){
            $this->data['user_id'] = $this->item['user_id'];
        }else{
            $this->data['user_id'] = 1010101;
        }


        //发布状态
        if($this->item['state']){
            $this->data['state'] = $this->item['state'];
        }else{
            $this->data['state'] = $itemTemp['state'];
        }
        
        //资讯内容
        if($this->item['content']){
            $this->data['content'] = $this->item['content'];
        }else{
            $this->data['content'] = $itemTemp['content'];
        }
    }
    

    /**
     * 新分配数据
    **/
    public function allocateNews($dayNow, $stateTemp){
        $res_isLogin = $this->isLogin();
        if($res_isLogin){
            if(strlen($dayNow)>=10 && $stateTemp == 0){
                $str=substr($test, 0, 10);
                $extCondition = sprintf(" state=0 and user_id!='' and ctime>'%s' and ctime<'%s'", $dayNow, $dayNow." 23:59:59");
                $this->item['user_id'] = $res_isLogin;
                $addNewList = $Model->where($extCondition)->order('modify_time desc')->limit(1)->save($this->item);
            } 
        }
    }
    

        /**
     * 获取资讯信息
    **/
    public function search(){

        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $this->iType = "search";
        $this->getReqParam();

        $this->user_id = I('param.user_id',$res_isLogin);
        $this->getConditon();
                
        $Model = M('NewsInfo');
        $ModelAdmin = M('Admin');
        $ModelPost = M('Post');

        $allnum = $Model->where($this->condition)->count();

        $listNewsInfo = $Model->where($this->condition)->order('ctime desc')->select();

        if($allnum > 0){
            $code = 0;
            $msg = "suc";
            $list = $Model->where($this->condition)->order('ctime desc')->page($this->curpage, $this->pagenum)->select();
            for ($i=0; $i < count($list); $i++) { 
                $listAdmin = $ModelAdmin->where("user_id='".$list[$i]['user_id']."'")->select();
                $list[$i]['chinaname'] = $listAdmin[0]['chinaname'];

                $listPost = $ModelPost->where("title='".$list[$i]['title']."'")->select();
                $list[$i]['recom_count'] = $listPost[0]['recom_count'];
                $list[$i]['comment_count'] = $listPost[0]['comment_count'];
                $list[$i]['transmit_count'] = $listPost[0]['transmit_count'];
                $list[$i]['url'] = $listPost[0]['source_url']?$listPost[0]['source_url']:$listNewsInfo[0]['url'];


                $key = "post_view_count_total";
                $redis = S(array('type'=>'Redis'));
                $listView = $redis->ZSCORE($key, $listPost[0]['id']);
                $list[$i]['view'] = $listView?$listView:0;
            }
        }else{
            $list = Array();
            $code = 0;
            $msg = "no result";
        }

        $this->ajaxOutput(0, '', array('count'=>$allnum, 'user_id'=>$res_isLogin, 'list'=>$list));
    }

    /*
    ** 获取该股吧置顶贴总数
    **/
    public function check_Sticky_State(){
        $strbarT = str_replace(';', '/', $this->item['strbar_id']);
        $arrayBarT = explode("/", $strbarT);

        $num = count($arrayBarT);
        $Post_Model = M('Post');
        $Post_bar_Model = M('PostBar');

        for($i = 0; $i < $num; $i++){
            $allnum = 0;
            $list = $Post_bar_Model->where("bar_code='".$arrayBarT[$i]."'")->select();
            $numTemp = count($list);
            for($j = 0; $j < $numTemp; $j++){
                $listPost = $Post_Model->where("id='".$numTemp[$j]['id']."'")->select;
                if($listPost[0]['sticky_state'] == '1'){
                    $allnum++;
                }
                if($allnum >= 10){
                    $this->ajaxOutput(20403, '', array('code'=>$arrayBarT[$i], 'list'=>Array()));
                }
            }
        }  

    }

    /**
     * 获取资讯信息
    **/
    public function get(){

        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $this->iType = "get";

        // $this->getReqParam($id, $type, $title, $state, $time, $ctime, $source, $sticky_state, $essential_state, $index_recom_state, $strtag_id, $strbar_id, $content, $user_id, $curpage, $pagenum, $item);
        // $this->getConditon($id, $type, $title, $state, $ctime, $source, $user_id, $condition, $iType);
        $this->getReqParam();
        $this->getConditon();
                
        //实例化一个模型
        $Model = M('NewsInfo');

        //读取10条数据（更多带条件的读取方法参见文档》模型》CURD操作说明）

        //echo "type:".$this->type."| ";
        if($this->type){
            //替换
            //$this->condition = str_replace('type', $this->type, $this->condition);

            $allnum = $Model->where($this->condition)->count();

            if($allnum > 0){
                $code = 0;
                $msg = "suc";
                $list = $Model->where($this->condition)->order('modify_time desc')->page($this->curpage, $this->pagenum)->select();
            }else{
                $list = Array();
                $code = 0;
                $msg = "no result";
            }

        }else{

            $allnum = $Model->master(true)->where($this->condition)->count();

            if($allnum >=0 && $this->state == 0){
                if ($allnum < $this->num) {
                    //分配的资讯任务不够
                    $extCondition = sprintf(" state=%s and user_id='' and ctime>'%s' and ctime<'%s'", $this->state, $this->ctime, $this->ctime." 23:59:59");
                    ///
                    $infoData['user_id'] = $this->user_id;
                    $addNewList = $Model->where($extCondition)->order('modify_time desc')->limit($this->num-$allnum)->save($infoData);
                }
                $code = 0;
                $msg = "suc";
                $list = $Model->where($this->condition)->order('modify_time desc')->page($this->curpage, $this->pagenum)->select();
            }if($allnum > 0){
                $code = 0;
                $msg = "suc";
                $list = $Model->where($this->condition)->order('modify_time desc')->page($this->curpage, $this->pagenum)->select();
            }else{
                $list = Array();
                $code = 0;
                $msg = "no result";
            }

        }

        //$this->newUrl($list);

        $this->ajaxOutput(0, '', array('count'=>$allnum, 'user_id'=>$res_isLogin, 'list'=>$list));
    //  $this->ajaxReturn($this->data);
    }
    
    /**
     * 发贴跳到股吧
    **/
    public function newUrl($list){
        print_r($list);
    }

    /**
     * 新增资讯
    **/
    public function add () {

        $this->iType  = "add";
        $this->getReqParam();
        $this->getConditon();

        //实例化一个模型
        $Model = M('NewsInfo');
        $myuser_id = 1010101;
        $res_isLogin = $this->isLogin();
        if($res_isLogin){
            $myuser_id = $res_isLogin;
        }

        if($this->item["content"]){
            $this->item["content"]=CH($this->item["content"], "tiezi");
        //    echo "/*   end:".$this->item['content']."| */";
        }
        if($this->item["title"]){
            $this->item["title"]=CH($this->item["title"], "tiezi");
        }
        if($this->item["source"]){
            $this->item["source"]=CH($this->item["source"], "tiezi");
        }
        if($this->item["url"]){
            $this->item["url"]=CH($this->item["url"], "tiezi");
            $this->item["url"] = str_replace('&amp;','&',$this->item["url"]);
        }


        $this->item["user_id"] = $myuser_id;
        $list = $Model->add($this->item);
        if($list || 0 === $list){
            $code = 0;
            $msg = "add suc".$this->item["url"];
            
            if(0 === $list){
                $code = 20400;
                $msg = "newsinfo table[no data add]!";
                $this->item = Array();
            }

        }else if (false === $list){
            $code = 10001;
            $msg = "sql failed";
            $this->item = Array();
        }

       //  //echo "list:".$list."|";
       //  $condition = " 1=1 and id='".$list."'";

       // // echo "cond:".$condition."|";
       //  $newData = $Model->where($condition)->select();
       //  if($newData || 0 === $newData){
       //      $code = 0;
       //      $msg = "select addnew suc";
       //  }else if (false === $newData){
       //      $code = 10001;
       //      $msg = "sql failed";
       //      $this->item = Array();
       //  }
        $newData["id"] = $list;
        $this->ajaxOutput($code, $msg, array('list'=>$newData));
    }

    /**
     * 更新数据
    **/
    public function update(){

        $detailmsg = "";
        $this->iType  = "update";
        
        // $this->getReqParam($id, $type, $title, $state, $time, $ctime, $source, $sticky_state, $essential_state, $index_recom_state, $strtag_id, $strbar_id, $content, $user_id, $curpage, $pagenum);
        // $this->getConditon($id, $type, $title, $state, $ctime, $source, $user_id, $condition, $iType);
        $this->getReqParam();
        $this->getConditon();
        
        //实例化一个模型
        $Model = M('NewsInfo');
        $myuser_id = 1010101;
        $res_isLogin = $this->isLogin();
        if($res_isLogin){
            $myuser_id = $res_isLogin;
        }
        
        if($this->item["content"]){
            $this->item["content"]=CH($this->item["content"], "tiezi");
        //    echo "/*   end:".$this->item['content']."| */";
        }
        if($this->item["title"]){
            $this->item["title"]=CH($this->item["title"], "tiezi");
        }
        if($this->item["source"]){
            $this->item["source"]=CH($this->item["source"], "tiezi");
        }

        
        $list = $Model->where($this->condition)->select();
        //记录发布多条退回补救
        $isRetreat = 0;
        if($list && $list[0]['state'] == 1){
           $isRetreat = 1;
        }
    

        $list = $Model->where($this->condition)->save($this->item);
        if($list || 0 === $list){
            $code = 0;
            $msg = "update suc";
            
            if(0 === $list){
                $code = 20400;
                $msg = "newsinfo table[no data change]!";
                $this->item = Array();
            }
            $t=$list[0]['ctime'];
            $stateTemp = $list[0]['state'];
            $this->allocateNews($t, $stateTemp);

        }else if (false === $list){
            $code = 10001;
            $msg = "sql failed";
            $this->item = Array();
        }
    
        $detailmsg = $detailmsg."state:".$this->item['state'];
       
        if($this->item['state'] == 5 || $this->item['state'] == 3 || $isRetreat == 1){
            $NewsModel = M('NewsInfo');
            $PostModel = M('Post');
            $Post_tag_Model = M('PostTag');
            $Post_bar_Model = M('PostBar');
            $Dynamic = D('Dynamic');
            //获取newsinfo资讯状态
            $idTemp = I('param.id',-1);
            if($idTemp>=0){
                 $this->id = $idTemp;
                 $this->item["id"] = $idTemp;
            }
            
            $detailmsg = $detailmsg." id:".$this->item['id'];
            $selectData = $NewsModel->where('id='.$this->item['id'])->select();
            if($selectData){
                $itemTemp = $selectData[0];
            }


            $detailmsg = $detailmsg." url:".$itemTemp['url'];
            $strUrl = "state='1' and source_url='".$itemTemp['url']."'";
            $post_idTemp = $PostModel->where($strUrl)->select();
            $post_id = $post_idTemp[0]['id'];
            
            $detailmsg = $detailmsg." post_id:".$post_id;
            //删除不存在post
            $strCondition = "id='".$post_id."'";
            $Post_list = $PostModel->where($strCondition)->select();

            
            $this->reIndexStock($post_id, "1000001");
            
            //删除新闻公告动态消息
            $delPostData['post_id'] = $post_id;
            $Dynamic->delStockDynamicInfo($delPostData);
            /*
            if($code != 10001 && $Post_list){
                $num = count($Post_list);
                if($num > 0){
                    $strDel = "id='".$post_id."'";
                    $Post_del = $PostModel->where($strDel)->delete();
                    if($Post_del){
                        $code = 0;
                        $msg = $msg."delete post_tag suc!";
                    }else{
                        $code = 10001;
                        $msg = $msg."post_tag table[delete failed]!";
                    }
                }
            }


            //删除不存在的关联posttag
            $strCondition = "post_id='".$post_id."'";
            $Post_tag_list = $Post_tag_Model->where($strCondition)->select();

            if($code != 10001 && $Post_tag_list){
                $num = count($Post_tag_list);
                if($num > 0){
                    $strDel = "post_id='".$post_id."'";
                    $Post_tag_del = $Post_tag_Model->where($strDel)->delete();
                    if($Post_tag_del){
                        $code = 0;
                        $msg = $msg."delete post_tag suc!";
                    }else{
                        $code = 10001;
                        $msg = $msg."post_tag table[delete failed]!";
                    }
                }
            }

            //删除不存在的关联postbar
            $Post_bar_list = $Post_bar_Model->where($strCondition)->select();

            if($code != 10001 && $Post_bar_list){
                $num = count($Post_bar_list);
                if($num > 0){
                    $strDel = "post_id='".$post_id."'";
                    $Post_bar_del = $Post_bar_Model->where($strDel)->delete();
                    if($Post_bar_del){
                        $code = 0;
                        $msg = $msg."delete post_tag suc!";
                        $detailmsg = $detailmsg." num:".$num." ";

                        for($i=0; $i < $num; $i++){
                            // $redis = S(array('type'=>'Redis'));  
                            // $keyBarAll ="bar_post_count_total";
                            // $keyBar ="bar_post_count_".date('ymd');
                            // //更新当日吧贴数
                            // $listTemp = $redis->zIncrBy($keyBar, -1, $Post_bar_list[$i]['bar_id']);
                            // //更新总吧贴数
                            // $listTempAll = $redis->zIncrBy($keyBarAll, -1, $Post_bar_list[$i]['bar_id']);  
                             $detailmsg = $detailmsg." bar_id:".$Post_bar_list[$i]['bar_id']." |";
                             $this->reIndexStock($Post_bar_Model[0]['post_id'], $myuser_id);
                        }
                    }else{
                        $code = 10001;
                        $msg = $msg."post_tag table[delete failed]!";
                    }
                }
            }
            */
        }


        $this->ajaxOutput(0, $detailmsg."pid:".$post_id, array('count'=>$list,'list'=>$this->item));
    }
    

    /**
     * 更新数据
    **/
    public function ignoreBanch(){

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



        $strid = I('id', -1);
        if($strid != -1){
            $arrayID = explode("_", $strid);
        }

        //实例化一个模型
        $Model = M('NewsInfo');


        $num = count($arrayID);
        for ($i=0; $i < $num; $i++) { 
            if($i + 1 == $num && $i != 0){
                $condition = $condition."'".$arrayID[$i]."')";  
            }else if ($i == 0){
                if($num == 1){
                    $condition = " id in ('".$arrayID[$i]."')";      
                }else{
                    $condition = " id in ('".$arrayID[$i]."',";   
                }
            }else{
                $condition = $condition."'".$arrayID[$i]."',";   
            }
        }

        //批量忽略
        $data['state'] = '5';                           
        $list = $Model->where($condition)->save($data);
        if($list || $list == null){
            $code = 0;
            $msg = "suc";
            if($list == null){
               $list = Array();
            }
        }else{
            $list = Array();
            $code = -1;
            $msg = "no result";
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
    

    public function getRideSymbol(){
        //$abc = I('param.abc',-1);
        $abc = $this->item['strbar_id'];
        $id = "";
        while(strpos($abc, '[') || strpos($abc, ']')){
            $left = strpos($abc, '[');
            $right = strpos($abc, ']');
            if($left == 0){
                $idTemp = substr($abc, 1 , $right-1);
                $abc = substr($abc, $right + 1, strlen($abc) - $right - 1);
                $id = $id."/".$idTemp;
                continue;
            }else{
                $abcleft = substr($abc, 0, $left-1);
                $abcright = substr($abc, $right+1, strlen($abc) - $right -1);
                $idTemp = substr($abc, $left+1 , $right-$left-1);
                $id = $id."/".$idTemp;
                $abc = $abcleft."/".$abcright;
             //   echo $id;
            }
        }
        $this->strbar_id_hide= substr($id, 1, strlen($id));
        $this->item['strbar_id'] = $abc;
  //      echo $this->strbar_id_hide.";".$this->item['strbar_id'];
    }


    /**
     * 发布
    **/
     public function release(){
         
         //第一步保存到newsinfo表
         $this->iType  = "release";
         // $this->getReqParam($id, $type, $title, $state, $time, $ctime, $source, $sticky_state, $essential_state, $index_recom_state, $strtag_id, $strbar_id, $content, $user_id, $curpage, $pagenum);
         // $this->getConditon($id, $type, $title, $state, $ctime, $source, $user_id, $condition, $iType);
         $this->getReqParam();
         $this->getConditon();
         if($this->item["content"]){
             $this->item["content"] = CH($this->item["content"], "tiezi");
         }
         if($this->item["title"]){
             $this->item["title"] = CH($this->item["title"], "tiezi");
         }
         if($this->item["source"]){
             $this->item["source"] = CH($this->item["source"], "tiezi");
         }
         
         if($this->item['sticky_state'] == '1'){
            $this->check_Sticky_State();
         }


         $NewsModel = M('NewsInfo');
         //获取newsinfo资讯状态
         $selectData = $NewsModel->where($this->condition)->select();
         $stateTemp = $selectData[0]['state'];
         //$strtag_id_Temp = $selectData[0]['strtag_id'];
         // $strbar_id_Temp = "/".$selectData[0]['strbar_id']."/";
         // $strbar_id_Temp_hide = "/".$selectData[0]['strbar_id_hide']."/";

                 
         //保存数据
         $this->item['state'] = 1;
         $myuser_id = 1010101;
         $res_isLogin = $this->isLogin();
         if($res_isLogin){
           $myuser_id = $res_isLogin;
         }

         $listNewsInfo = $NewsModel->where($this->condition)->save($this->item);
         if($listNewsInfo || 0 === $listNewsInfo){
             $code = 0;
             $msg = "update suc!";

             $t=$listNewsInfo[0]['ctime'];
             $this->allocateNews($t, $stateTemp);
             if(0 === $listNewsInfo && ($stateTemp != 4 || $stateTemp != 2)){
                 $code = 20400;
                 $msgNewsInfo = "1:newsinfo table[no data change]!";
                 $this->data = Array();
             }
         }else if (false === $listNewsInfo){
             $code = 10001;
             $msg = "sql failed!";
             $this->data = Array();
         }
         
         
         $this->getRideSymbol();
         //step 2，判断post表是否已经存在 存在则update数据，否则写入
         $PostModel = M('post');
         if(!$code && $selectData){
             $itemTemp = $selectData[0];
             
             if($itemTemp["content"]){
                 $itemTemp["content"]=CH($itemTemp["content"], "tiezi");
                 //$itemTemp["content"] = urldecode($itemTemp["content"]);

             }
             if($itemTemp["title"]){
                 $itemTemp["title"]=CH($itemTemp["title"], "tiezi");
             }
             if($itemTemp["source"]){
                 $itemTemp["source"]=CH($itemTemp["source"], "tiezi");
             }
            
             //获取发布||更新的数据
             $this->getAddData($this->item, $this->data, $itemTemp);
            
             $Post_tag_Model = M('PostTag');
             $Post_bar_Model = M('PostBar');
             if($stateTemp == 1 || $stateTemp == 4 || $stateTemp == 3){
                 //3资讯为发布回退状态, 重置state为1(已发布),4发布修改中, 更新post表数据
                 $strUrl = "source_url='".$this->data['source_url']."'";
                 $this->data['mince_type'] = $this->data['type'];
                 if($this->data['type'] > 2){
                    $this->data['type'] = 3;
                 }
                 //$this->data['user_id'] = $myuser_id;
                 $this->data['user_id'] = "1000001";
                 $listPost = $PostModel->where($strUrl)->save($this->data);
                 
                 if($listPost || 0 === $listPost){
                     $code = 0;
                     $msg = "release suc!";
                     if(0 === $listPost && !$this->item['strtag_id'] && !$this->item['strbar_id']){
                         $code = 20400;
                         $msgPost = "post table[no data change]!";
                         $this->data = Array();
                     }
                 }else if (false === $listPost){
                     $code = 10001;
                     $msg = "sql failed!";
                     $this->data = Array();
                 }
                
                 
                 //写post_tag标签s关联
                 $post_id = "";
                 if(!$code){
                     $strUrl = "source_url='".$this->data['source_url']."'";
                     $post_idTemp = $PostModel->where($strUrl)->select();
                     $post_id = $post_idTemp[0]['id'];
                     //删除不存在的关联
                     $strCondition = "post_id='".$post_id."'";
                     $Post_tag_list = $Post_tag_Model->where($strCondition)->select();



                     if($code != 10001 && $Post_tag_list){
                         $num = count($Post_tag_list);


                         for($i = 0; $i < $num; $i++){
                             if((!strstr("_".$this->item['strtag_id']."_", "_".$Post_tag_list[$i]['tag_id']."_")) || !$this->item['strtag_id'] === "nil"){
                                 $strDel = "post_id='".$post_id."' and tag_id='".$Post_tag_list[$i]['tag_id']."'";


                                 $Post_tag_add = $Post_tag_Model->where($strDel)->delete();
                                 if($Post_tag_add){
                                     $code = 0;
                                     $msg = $msg."delete post_tag suc!";
                                 }else{
                                     $code = 10001;
                                     $msg = $msg."post_tag table[delete failed]!";
                                 }
                             }
                             $strtag_id_Post = $strtag_id_Post."_".$Post_tag_list[$i]['tag_id'];
                         }
                         $strtag_id_Post = $strtag_id_Post."_";
                     }
                     
                     //插入新的关联
                     if($this->item['strtag_id'] && $this->item['strtag_id'] != "nil"){
                         $arrayTag = explode("_", $this->item['strtag_id']);
                         $num = count($arrayTag);


                         for($i = 0; $i < $num; $i++){
                             if($code != 10001 && !strstr($strtag_id_Post, "_".$arrayTag[$i]."_")){
                                 $Post_tag_item['post_id'] = $post_id;
                                 $Post_tag_item['tag_id'] = $arrayTag[$i];
                                 $Post_tag_add = $Post_tag_Model->add($Post_tag_item);


                                 if($Post_tag_add){
                                     $code = 0;
                                     $msg = $msg."add post_tag suc!";
                                 }else{
                                     $code = 10001;
                                     $msg = $msg."post_tag table old[add failed]!";
                                 }
                             }
                         }
                     }
                 }



                 //写post_bar标签s关联
                 if(!$code){


                     //删除不存在的关联
                     $strCondition = "post_id='".$post_id."'";

                     //$this->strbar_id_hide = str_replace(';', '/', $this->strbar_id_hide);
                     $this->item['strbar_id'] = str_replace(';', '/', $this->item['strbar_id']);
                     $Post_bar_list = $Post_bar_Model->where($strCondition)->select();
                     if($code != 10001 && $Post_bar_list){
                         $num = count($Post_bar_list);
                         for($i = 0; $i < $num; $i++){
                             if((!strstr("/".$this->item['strbar_id']."/", "/".$Post_bar_list[$i]['bar_code']."/")) || $this->item['strbar_id'] === "nil") {
                                 $strDel = "post_id='".$post_id."' and bar_code='".$Post_bar_list[$i]['bar_code']."'";


                                 $Post_bar_add = $Post_bar_Model->where($strDel)->delete();
                                 if($Post_bar_add){
                                     $code = 0;
                                     $msg = $msg."delete post_bar suc!";

                                     // $redis = S(array('type'=>'Redis'));  
                                     // $keyBarAll ="bar_post_count_total";
                                     // $keyBar ="bar_post_count_".date('ymd');
                                     //更新当日吧贴数
                                     //$listTemp = $redis->zIncrBy($keyBar, -1, $Post_bar_list[$i]['bar_id']);
                                     //更新总吧贴数
                                     //$listTempAll = $redis->zIncrBy($keyBarAll, -1, $Post_bar_list[$i]['bar_id']);

                                     $this->reIndexStock($Post_bar_Model[0]['post_id'], $myuser_id);

                                 }else{
                                     $code = 10001;
                                     $msg = $msg."post_bar table[delete failed]!";
                                 }
                             }
                             $strbar_id_Post = $strbar_id_Post."/".$Post_bar_list[$i]['bar_code'];
                         }
                         $strbar_id_Post = $strbar_id_Post."/";
                     }   
                           
                     //插入新的关联      
                     //if($this->item['strbar_id'] && $this->item['strbar_id'] != "nil"){
                     if($this->strbar_id_hide && $this->strbar_id_hide != "nil"){
                         //$arrayBar = explode("/", $this->item['strbar_id']);
                         $arrayBar = explode("/", $this->strbar_id_hide);

                         $num = count($arrayBar);
                         $Stock_bar_Model = M('StockBar');
                         $datalist = array();
                         $bar_ids = array();
                         for($i = 0, $j = 0, $k = 0; $i < $num; $i++){
                            if($code != 10001 && !strstr($strbar_id_Post, "/".$arrayBar[$i]."/")){
                                 //$strCondition = "code='".$arrayBar[$i]."'";
                                 $strCondition = "id='".$arrayBar[$i]."'";

                                 $Stock_bar_list = $Stock_bar_Model->where($strCondition)->select();
                                 if($Stock_bar_list != false && $Stock_bar_list){
                                    $bar_idTemp =  $Stock_bar_list[0]['id'];
                                    $bar_codeTemp =  $Stock_bar_list[0]['code'];
                                    $bar_typeTemp =  $Stock_bar_list[0]['type'];
                                    $datalist[$j++] = array('post_id'=>$post_id,'bar_id'=>$bar_idTemp, 'bar_code'=>$bar_codeTemp, 'bar_type'=>$bar_typeTemp);
                                    $bar_ids[$k++] = $bar_idTemp;

                                    $redis = S(array('type'=>'Redis'));
                                    $keyBarAll = "bar_post_count_total";
                                    $keyBar ="bar_post_count_".date('ymd');
                                    //$keyBarUser = "user_post_count_total";
                                    //更新当日吧贴数
                                    $listTemp = $redis->zIncrBy($keyBar, 1, $bar_idTemp);
                                   //更新总吧贴数
                                    $listTempAll = $redis->zIncrBy($keyBarAll, 1, $bar_idTemp);
                                    // //增加个人发帖数
                                    // $listTempUser = $redis->zIncrBy($keyBarUser, 1, $myuser_id);


                                 }else{
                                    $code = 10001;                                                                                                                     
                                    $msg = $msg."select stock_bar failed!";
                                 }
                             }
                         }                                                                                                                                     
                        
                         if(count($datalist) > 0){
                            $list_post_bar = $Post_bar_Model->addAll($datalist);                                                                                      
                         
                            if($list_post_bar){                                                                                                                       
                              $code = 0;                                                                                                                            
                              $msg = $msg."add post_bar suc!";
                              //发送关注动态
                              if($stateTemp == 3){
                                $this->dynamicInfo($post_id, $this->data['user_id'], $this->data['type'], $bar_ids);                                                                              }
                            }else{                                                                                                                                    
                              $code = 10001;                                                                                                                        
                              $msg = $msg."post_bar table[add failed]!";                                                                                            
                            } 
                         }
                     }                                                                                                                                        
                 }                                                                                                                                             
                   

                                  
                 $msg = $msg.$msgNewsInfo.$msgPost;
             }else{
                 //原始未编辑状态 || 修改未发布状态   写post表数据
                 $this->data['mince_type'] = $this->data['type'];
                 if($this->data['type'] > 2){
                    $this->data['type'] = 3;
                 }
                 $this->data['admin_id'] = $myuser_id;
                 $this->data['user_id'] = "1000001";
                 $this->data['modify_time'] = date("Y-m-d h:i:s");
                 $listPost = $PostModel->add($this->data);
                 if($listPost){
                     $code = 0;
                     $msg = "new:release suc!".$listPost."|";

                     $t=$listPost[0]['ctime'];
                     $stateTemp=$listPost[0]['state'];
                     $this->allocateNews($t, $stateTemp);
                 }else{
                     $code = 10001;
                     $msg = "post table[add failed]!";
                 }
                 
                 $post_id = $listPost;
                 if(!$code && $this->item['strtag_id']){
                     $arrayTag = explode("_", $this->item['strtag_id']);
                     $num = count($arrayTag);
                     
                     for($i = 0; $i < $num; $i++){
                         $datalist[] = array('post_id'=>$post_id,'tag_id'=>$arrayTag[$i]);
                     }
                     if(count($datalist) > 0){
                        $list_post_tag = $Post_tag_Model->addAll($datalist);
                        if($list_post_tag){
                          $code = 0;
                          $msg = $msg."add post_tag suc!";
                        }else{
                          $code = 10001;
                          $msg = $msg."new:post_tag table[add failed]!";
                        }
                     }
                 }

                 //写post_bar标签s关联
                 // if(!$code && $this->item['strbar_id']){
                 //     $this->item['strbar_id'] = str_replace(';', '/', $this->item['strbar_id']);
                 //     $arrayBar = explode("/", $this->item['strbar_id']);
                 if(!$code && $this->strbar_id_hide){
                     $this->strbar_id_hide = str_replace(';', '/', $this->strbar_id_hide);
                     $arrayBar = explode("/", $this->strbar_id_hide);

                     $num = count($arrayBar);

                     $Stock_bar_Model = M('StockBar');
                     $datalist = array();
                     $bar_ids = array();
                     for($i = 0, $j = 0, $k = 0; $i < $num; $i++){
                         //$strCondition = "code='".$arrayBar[$i]."'";
                         $strCondition = "id='".$arrayBar[$i]."'";
                         $Stock_bar_list = $Stock_bar_Model->where($strCondition)->select();
                         if($Stock_bar_list != false && $Stock_bar_list){
                            $bar_idTemp =  $Stock_bar_list[0]['id'];
                            $bar_codeTemp =  $Stock_bar_list[0]['code'];
                            $bar_typeTemp =  $Stock_bar_list[0]['type'];
                            $datalist[$j++] = array('post_id'=>$post_id,'bar_id'=>$bar_idTemp, 'bar_code'=>$bar_codeTemp, 'bar_type'=>$bar_typeTemp);
                            $bar_ids[$k++] = $bar_idTemp;


                            $redis = S(array('type'=>'Redis'));
                            $keyBarAll ="bar_post_count_total";
                            $keyBar ="bar_post_count_".date('ymd');
                            //$keyBarUser = "user_post_count_total";
                            //更新当日吧贴数
                            $listTemp = $redis->zIncrBy($keyBar, 1, $bar_idTemp);
                            //更新总吧贴数
                            $listTempAll = $redis->zIncrBy($keyBarAll, 1, $bar_idTemp);
                            //增加个人发帖数
                            //$listTempUser = $redis->zIncrBy($keyBarUser, 1, $myuser_id);
                         }else{
                            $code = 10001;                                                                                                                     
                            $msg = $msg."select stock_bar failed!";
                         }
                     }

                     $list_post_bar = $Post_bar_Model->addAll($datalist);
                     if($list_post_bar){
                         $code = 0;
                         $msg = $msg."add post_bar suc!";
                         //发送关注动态
                         $this->dynamicInfo($post_id, $this->data['user_id'], $this->data['type'], $bar_ids);
                     }else{
                         $code = 10001;
                         $msg = $msg."post_bar table[add failed]!";
                     }
                 }

             }//else
             

         }else if ($code === 20400){
             $msg = "2:newsinfo table[no data change]!";
         }else{
             //不存在的id || 数据库查询错误
             $code = 10001;
             $msg = "newsinfo table[select id failed]!";
         }
         
        $this->ajaxOutput($code, $msg, array('count'=>$listPost,'list'=>$this->data));
    }

     //发送关注动态
    public function dynamicInfo ($post_id, $user_id, $post_type, $bar_ids) {

        $Dynamic = D('Dynamic');

        if ($post_id) {
            $dataLetter['post_id'] = $post_id;
        }

        if ($user_id) {
            $dataLetter['user_id'] = $user_id;
        }

        if ($post_type) {
            $dataLetter['post_type'] = $post_type;
        }

        if ($bar_ids) {
            $dataLetter['bar_ids'] = $bar_ids;
        }


        //不限时执行
        set_time_limit(0);
        $req = $Dynamic->addStockDynamicInfo($dataLetter);
        if($req == flase){
            $code = $Dynamic->getErrorNo();
            $msg = $Dynamic->getError();

            if ($code == 10001) {
                //插表失败，写日志
                $data['table_name'] = "dynamic";
                $data['type'] = 2;
                $data['admin_id'] = $this->isLogin();
                $data['code'] = $code;
                $data['msg'] = $msg;
                //写日志
                aLog($data);
            }
        }
        
            //插表失败，写日志
            /*
            $data['table_name'] = "dynamic";
            $data['type'] = 2;
            $data['admin_id'] = $this->isLogin();
            $data['code'] = $code;
            $data['msg'] = $req;
            //写日志
            aLog($data); 
        
        */
        
    }
}
