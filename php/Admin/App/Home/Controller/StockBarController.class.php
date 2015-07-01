<?php
namespace Home\Controller;
use Home\Controller\CommonController;

/**
 * “股吧”控制器类。
 */ 
class StockBarController extends CommonController {
		private $id = "";
		private $type = "";
        private $code = "";
		private $order = "";
        private $state = "";
		private $condition = "1=1";
		private $curpage = 1;
    	private $pagenum = 10;
    	private $countall = 0;
    	private $listData = array();

		public function getReqParam(){    
            $code = I('param.code',-1);
            if($code>=0){
                $this->code = $code;
            }

	        $typeTemp = I('param.type',-1);
	        if($typeTemp>=0){
	            $this->type = $typeTemp;
	        }

            $StateTemp = I('param.state',-1);
            if($StateTemp>=0){
                $this->state = $StateTemp;
            }
	        
	        $orderTemp = I('param.order',-1);
	        if($orderTemp>=0){
	            $this->order = $orderTemp;
	        }

	        $curpageTemp = I('param.curpage',$this->curpage);
	        if($curpageTemp>=0){
	            $this->curpage = $curpageTemp;
	        }

	        $pagenumTemp = I('param.pagenum',$this->pagenum);
	        if($pagenumTemp>=0){
	            $this->pagenum = $pagenumTemp;
	        }
	    }

		public function getCondition(){
			if($this->type){
                if($this->type!=5){
	                $conditionTemp = sprintf(" and type='%s'", $this->type);
                }
	            $this->condition = $this->condition.$conditionTemp;
	        }

            if($this->code){
                $conditionTemp = sprintf(" and code='%s'", $this->code);
                $this->condition = $this->condition.$conditionTemp;
            }

            if($this->state){
                if ($this->state!=6 && $this->state==5) {
                    $conditionTemp = sprintf(" and list_date=''");
                }else if ($this->state!=6){
                    $conditionTemp = sprintf(" and state='%s'", $this->state);
                }
                $this->condition = $this->condition.$conditionTemp;
            }
		}

        public function get(){
            $Model = M('StockBar');
            //$list = $Model->getField('id,name,subtype');
            $list = $Model->where('type=4')->select();
            $this->ajaxOutput(0, '', array('list'=>$list));
        }

        public function getbak(){
            $Model = M('StockBar');
            //$list = $Model->getField('id,name,subtype');
            $list = $Model->where('type=6')->select();
            $this->ajaxOutput(0, '', array('list'=>$list));
        }

        public function getStockNum(){

            if($type != "-1" && $type >= 1 && $type <= 4){
                $condition = $condition." and type='".$type."'";
            }

            $Model = M('StockBar');

            $allnum = $Model->where($condition)->count();
            
            $this->ajaxOutput(0, "suc", array('count'=>$allnum, 'list'=>array()));

        }

        public function getStockCode(){

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

            $type = I('param.type',-1);
            $this->curpage = I('param.curpage',1);
            $this->pagenum = I('param.pagenum',10);

            $condition = "1=1";
            //if($type != "-1" && $type >= 1 && $type <= 4){
            if($type != "-1" && $type >= 1 && $type <= 4){
                $condition = $condition." and type='".$type."'";
            }

            $Model = M('StockBar');

            $allnum = $Model->where($condition)->count();
            
            $list = $Model->where($condition)->order(" id asc")->page($this->curpage, $this->pagenum)->select();

            if($list || $list == null){
              $code = 0;
              $msg = "suc";
              if($list == null){
                $list = array();
              }

              $num = count($list);
              for($i=0; $i < $num; $i++){
                $list[$i]['ucode'] =  makeBarCode($list[$i]['code'], $list[$i]['class']);
              }


            }else{
              $code = -1;
              $msg = "sql failed";
            }
            $this->ajaxOutput($code, $msg, array('count'=>$allnum, 'list'=>$list));

        } 


        public function getStockList(){
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

            $this->getReqParam();
            $this->getCondition();

            $Model = M('StockBar');
            $ModelTagInit = M('StockTagInit');
            $ModelTag = M('Tag');

            $num = $Model->where($this->condition)->count();

            $list = $Model->where($this->condition)->order(" id asc")->page($this->curpage, $this->pagenum)->select();
//            print_r($list);
            if($list){
                for($i = 0; $i < count($list); $i++){
                    $list[$i]['ucode'] =  makeBarCode($list[$i]['code'], $list[$i]['class']);
//                    echo "code:".$list[$i]['code']." class:".$list[$i]['class']." ucode:".$list[$i]['ucode'];

                    $listTagInit = $ModelTagInit->where("code='".$list[$i]['ucode']."'")->select();
                    if($listTagInit || $listTagInit == null){
                        $code = 0;
                        $msg = "tag init find suc";
                        if($listTagInit == null){
                            $listTagInit = array();
                            //$this->ajaxOutput(0, 'suc', array('count'=>$num, 'list'=>$list));
                        }

                        //获取所属行业名称
                        $arrayTagHY = explode("_", $listTagInit[0]['hy_tag']);
                        for ($j=0; $j < count($arrayTagHY); $j++) { 
                             $listTagHY = $ModelTag->where("id='".$arrayTagHY[$j]."'")->select();
                             $list[$i]['hy_tag'] = $listTagHY[0]['id'];
                             $list[$i]['hy_name'] = $list[$i]['hy_name']." / ".$listTagHY[0]['name'];
                        }

                        //获取所属行业名称
                        $arrayTagGN = explode("_", $listTagInit[0]['gn_tag']);
                        for ($j=0; $j < count($arrayTagGN); $j++) { 
                             $listTagGN = $ModelTag->where("id='".$arrayTagGN[$j]."'")->select();
                             $list[$i]['gn_tag'] = $listTagGN[0]['id'];
                             $list[$i]['gn_name'] = $list[$i]['gn_name']." / ".$listTagGN[0]['name'];
                        }
                    }else{
                        $code = -1;
                        $msg = "code not find in tag init ";
                        $list = array();
                    }
                }
            }

            $this->ajaxOutput(0, 'suc', array('count'=>$num, 'list'=>$list));
        }


        public function getBanchStockList(){
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
            
            $code = I('param.code',"");
            $codeBar = explode(",", $code);

            $Model = M('StockBar');

            $this->condition = " code in (".$code.")";

            $list = $Model->where($this->condition)->select();

            if($list){
                $code = 0;
                $msg = "get banch stock id suc";
                if($list == null){
                    $list = array();
                    $this->ajaxOutput(0, 'suc', array('count'=>0, 'list'=>$list));
                }                       
            }else{
                $code = -1;
                $msg = "code not find in tag init ";
                $list = array();
            }

            $this->ajaxOutput(0, 'suc', array('count'=>count($list), 'list'=>$list));
        }




         public function getStock(){

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
            

        	$this->getReqParam();
        	$this->getCondition();

            $Model = M('StockBar');
            $ModelComment = M('CommentCount');
            $ModelPostTime = M('PostBar');
           
            if($this->order != "2"){
            	//按发帖数排序
            	$this->readFile();
            	$num = count($this->listData);
            	$j = $this->pagenum*($this->curpage-1);
            	$conditionTemp = "(";
	            for($i = $j; $i < $this->pagenum + $j && $i < $num; $i++){
	            	if($i + 1 == $this->pagenum + $j || $i + 1 == $num){
	            		$conditionTemp = $conditionTemp." code='".$this->listData[$i]['code']."' and class='".$this->listData[$i]['class']."') and state='1'";
	            	}else{
	            		$conditionTemp = $conditionTemp." code='".$this->listData[$i]['code']."' and class='".$this->listData[$i]['class']."' or";
					}	            
	            }
            	$list = $Model->where($conditionTemp)->select();
            	if($list){
            		for($i = 0; $i < count($list); $i++){
                        $condition = " time>'".date('Y-m-d')."' and code='".$list[$i]['code']."'";
                        for($k = $j; $k < $this->pagenum + $j; $k++){
                            if($list[$i]['code'] == $this->listData[$k]['code']){
                                $list[$i]['todayPost'] = $this->listData[$k]['post_num'];
                            }
                        }
                        $listTemp = $ModelComment->where($condition)->select();
                        $list[$i]['todayResp'] = $listTemp[0]['resp_num'] ? $listTemp[0]['resp_num'] : 0;
                        $listTime = $ModelPostTime->where(" bar_code='".$list[$i]['code']."'")->order('ctime desc')->select();
                        $list[$i]['lastPost'] = $listTime[0]['ctime'] ? $listTime[0]['ctime'] : "00-00-00 00:00:00";
                    }

                    for ($i=0; $i < count($list) - 1; $i++) { 
                        $max = $list[$i]['todayPost'];
                        $listTempSwap = $list[$i];
                        $tag = $i;
                        for ($j=$i+1; $j < count($list); $j++) { 
                            if ($list[$j]['todayPost'] > $max) {
                                $max = $list[$j]['todayPost'];
                                $list[$i] = $list[$j];
                                $tag = $j;
                            }
                        }
                        $list[$tag] = $listTempSwap;

                    }
            	}
            }else if ($this->order == "2"){
            	//按回复数排序
            	$this->userRespNumInsert();
                if($this->type){
                    if (intval($this->type) < 5 && intval($this->type) > 0) {
                        $conditionTemp = sprintf("time>'%s' and type='%s'", date('Y-m-d'), $this->type);
                    }else{
                        $conditionTemp = sprintf("time>'%s'", date('Y-m-d'));
                    }
                }
            	$list = $ModelComment->where($conditionTemp)->order('resp_num desc')->page($this->curpage, $this->pagenum)->select();
            	$num = count($list);
            	if($list){
            		for($i = 0; $i < count($list); $i++){
                        $condition = " code='".$list[$i]['code']."'";
            			$listTemp = $Model->where($condition)->select();
            			$list[$i]['todayPost'] = $list[$i]['post_num'] ? $list[$i]['post_num'] : 0;
            			$list[$i]['todayResp'] = $list[$i]['resp_num'];
            			$list[$i]['type'] = $listTemp[0]['type'];
            			$list[$i]['name'] = $listTemp[0]['name'];
            			
            			$listTime = $ModelPostTime->where(" bar_code='".$list[$i]['code']."'")->order('ctime desc')->select();
            			$list[$i]['lastPost'] = $listTime[0]['ctime'] ? $listTime[0]['ctime'] : "00-00-00 00:00:00";
            		}
            	}
            }
            
            

            $this->ajaxOutput(0, 'suc', array('count'=>$num, 'list'=>$list));
        }

        public function saveStockBar () {
        	//$Model = M('NewsInfo');
        	$Model = M('StockBar');
        	$res_isLogin = $this->isLogin();
	        if(!$res_isLogin){
	            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
	        }

        	$id = I('param.id',-1);
        	$type = I('param.type',-1);
        	$code = I('param.code',-1);
        	$name = I('param.name',-1);
            $en_title = I('param.en_title',-1);
        	$abbrev = I('param.abbrev',-1);
        	$spell = I('param.spell',-1);
        	$state = I('param.state',-1);
            $class = I('param.classtype',-1);
            $list_date = I('param.list_date',-1);
            $updata_lock = I('param.updata_lock',-1);

        	if($id != -1){
        		$listData['id'] = $id;
        	}
        	if($type != -1){
        		$listData['type'] = $type;
        	}
        	if($code != -1){
        		$listData['code'] = $code;
        	}
        	if($name != -1){
        		$listData['name'] = $name;
        	}
            if($en_title != -1){
                $listData['eng_name'] = $en_title;
            }
        	if($abbrev != -1){
        		$listData['abbrev'] = $abbrev;
        	}
        	if($spell != -1){
        		$listData['spell'] = $spell;
        	}
        	if($state != -1){
        		$listData['state'] = $state;
        	}
            if($class != -1){
                $listData['class'] = $class;
            }
            if($list_date != -1){
                $listData['list_date'] = $list_date;
            }
            if($updata_lock != -1){
                $listData['updata_lock'] = $updata_lock;
            }

            $LData['table_name'] = "StockBar";
            $LData['type'] = 2;
            $LData['admin_id'] = $res_isLogin;

        	if($id>0){
                $SBList = $Model->master(true)->where("id='".$id."'")->select();
                //if ($listData['type'] != $SBList[0]['type']) {
                //    $LMsg = $LMsg." type[".$SBList[0]['type']."]修改为[".$listData['type']."] ";
                //}
                if ($listData['name'] != $SBList[0]['name']) {
                    $LMsg = $LMsg." name[".$SBList[0]['name']."]修改为[".$listData['name']."] ";
                }
                if ($listData['eng_name'] != $SBList[0]['eng_name']) {
                    $LMsg = $LMsg." eng_name[".$SBList[0]['eng_name']."]修改为[".$listData['eng_name']."] ";
                }
                if ($listData['abbrev'] != $SBList[0]['abbrev']) {
                    $LMsg = $LMsg." abbrev[".$SBList[0]['abbrev']."]修改为[".$listData['abbrev']."] ";
                }
                if ($listData['spell'] != $SBList[0]['spell']) {
                    $LMsg = $LMsg." spell[".$SBList[0]['spell']."]修改为[".$listData['spell']."] ";
                }
                if ($listData['state'] != $SBList[0]['state']) {
                    $LMsg = $LMsg." state[".$SBList[0]['state']."]修改为[".$listData['state']."] ";
                }
                if ($listData['updata_lock'] != $SBList[0]['updata_lock']) {
                    $LMsg = $LMsg." updata_lock[".$SBList[0]['updata_lock']."]修改为[".$listData['updata_lock']."] ";
                }
                if ($listData['list_date'] != $SBList[0]['list_date']) {
                    $LMsg = $LMsg." list_date[".$SBList[0]['list_date']."]修改为[".$listData['list_date']."] ";
                }

        		$list = $Model->where("id='".$id."'")->save($listData);
                $LData['msg'] = "股吧修改: 股吧(code ".$SBList[0]['code']." name ".$SBList[0]['name'].")     ".$LMsg." 修改成功";
                aLog($LData);
        	}else {
                $list = $Model->where("code='".$code."' and class='".$class."'")->select();
                if($list){
                    $code = 0;
                    $msg = "suc";
                    
                    if($list == null)
                    {
                        $list = $Model->add($listData);
                        $this->reIndexStock($list[0]['id']);
                        $LData['msg'] = "股吧创建: code ".$code." name ".$name." 创建成功]";
                        aLog($LData);
                    }else{
                         $this->ajaxOutput(-1, 'code exist', array('count'=>count($list), 'list'=>$list));
                    }
                }else{

                    $list = $Model->add($listData);
                    $this->reIndexStock($list);
                }
        	}
        	
            //print_r($list);
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
            if ($state != -1 && $id>0) {
                $this->reIndexStock($id);
            }

            //交易中变退市  或者  退市变交易中
            if ($id>0 && (($state == 3 && $SBList[0]['state'] == 1) || ($state == 3 && $SBList[0]['state'] == 2) || ($state == 1 && $SBList[0]['state'] == 3) || ($state == 2 && $SBList[0]['state'] == 3))) {
                $this->id = $id;
                $this->saveStockBarComponent();
            }


            $this->ajaxOutput($code, 'suc', array('count'=>count($list), 'list'=>$list));
        }


        public function saveStockBarComponent(){
            $condition = "id='".$this->id."'";
            if (!$this->id) {
                $code = I('param.code',-1);
                $class = I('param.class',-1);
                if ($code == -1) {
                    $this->ajaxOutput(-1, 'code参数错误', array('count'=>0, 'list'=>array()));
                }

                if ($class == -1) {
                    $this->ajaxOutput(-1, 'class参数错误', array('count'=>0, 'list'=>array()));
                }
                $condition = "code='".$code."' and class='".$class."'";
            }
            
            $Model = M('StockBar');
            $ModelCom = M('TagComponent');

            $LData['table_name'] = "Tag";
            $LData['type'] = 1;
            $LData['admin_id'] = $this->isLogin();

            $list = $Model->master(true)->where($condition)->select();
            if ($list && $list != null && $list[0]['state'] == 3) {
                //修改为退市状态

                $CData['out_time'] = date("Y-m-d H:i:s");
                $CList = $ModelCom->where("code='".$list[0]['code']."'")->save($CData); 
                if (!$CList) {
                    $LData['msg'] = "行业概念: code ".$list[0]['code']." name ".$list[0]['code']." 更新为退市状态， TagComponent表修改股吧关联失败";
                }else{
                    $LData['msg'] = "行业概念: code ".$list[0]['code']." name ".$list[0]['code']." 更新为退市状态， TagComponent表修改股吧关联成功";
                }
                aLog($LData);
            }else if($list && $list != null && $list[0]['state'] != 3){
                //删除退市状态为  交易中/停牌
                $CData['out_time'] = null;
                $CList = $ModelCom->where("code='".$list[0]['code']."'")->save($CData); 
                if (!$CList) {
                    $LData['msg'] = "行业概念: code ".$list[0]['code']." name ".$list[0]['code']." 更新为退市状态， TagComponent表修改股吧关联失败";
                }else{
                    $LData['msg'] = "行业概念: code ".$list[0]['code']." name ".$list[0]['code']." 更新为退市状态， TagComponent表修改股吧关联成功";
                }
                aLog($LData);
            }else{
                $this->ajaxOutput(-1, '参数错误', array('count'=>0, 'list'=>array()));
            }

            if (!$this->id){
                $this->ajaxOutput(0, $code." 退市修改成功", array('count'=>0, 'list'=>array()));
            }
        }

        public function delStockBar () {
        	$Model = M('StockBar');
        	$res_isLogin = $this->isLogin();
	        if(!$res_isLogin){
	            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
	        }

        	$id = I('param.id',-1);
        	$listData[0]['state'] = 0;
        	$list = $Model->where("id='".$id."'")->save($listData[0]);
            //print_r($list);
        	if($list || $list == null){
	            $code = 0;
	            $msg = "suc";
	            if($list == null){
	               $list = Array();
	            }
	        }else{
	            $list = Array();
	            $code = 0;
	            $msg = "no result";
        	}

            $this->reIndexStock('0');
            $this->ajaxOutput(0, 'suc', array('count'=>count($list), 'list'=>$list));
        }

        public function reIndexStock($id){
            // 初始化一个 cURL 对象 
            $curl = curl_init(); 

            // 设置你需要抓取的URL
            if ($id > 0) {
                $url = 'http://www.richba.com/index.php?m=home&c=cmd&a=addNewStockBar&bid='.$id;
                curl_setopt($curl, CURLOPT_URL, $url); 
            }else{
                curl_setopt($curl, CURLOPT_URL, 'http://www.richba.com/index.php?m=home&c=cmd&a=reIndexStock'); 
            }

            

            // 设置header 
            curl_setopt($curl, CURLOPT_HEADER, 1); 

            // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。 
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 

            // 运行cURL，请求网页 
            $data = curl_exec($curl); 

            // 关闭URL请求 
            curl_close($curl); 
            
            
            //刷新缓存
            getBarById($id, array(), 'must');
        }

        public function readFile () {
        	$ModelComment = M('CommentCount');
        	if($this->type == 5){//全部
        		$i = 0;
        		$a = file('/data/www/admin/data/ggdata_sort'.date('Y-m-d').'.txt');
		        foreach($a as $line => $content){
		        	$array = explode(",", $content);
		        	if($array[1] != 5){
		        		$this->listData[$i]['code'] = $array[2];
			        	$this->listData[$i]['post_num'] = $array[3];
			        	if($array[3] != "0"){
			        		$list = $ModelComment->where("code='".$this->listData[$i]['code']."'")->select();
						 	if($list){
						 		$list = $ModelComment->where("code='".$this->listData[$i]['code']."'")->save($this->listData[$i]);
						 	}else{
						 		$list = $ModelComment->add($listData[$i]);
						 	}
			        	}
			        	$this->listData[$i]['class'] = $array[4];
			        	$i++;
		        	}
		        }
        	}else if($this->type == 1){//沪深
        		$i = 0;
        		$a = file('/data/www/admin/data/ggdata_sort'.date('Y-m-d').'.txt');
		        foreach($a as $line => $content){
		        	$array = explode(",", $content);
		        	if($array[1] == 1){
			        	$this->listData[$i]['code'] = $array[2];
			        	$this->listData[$i]['post_num'] = $array[3];
			        	$this->listData[$i]['class'] = $array[4];
			        	$i++;
		        	}
		        }
        	}else if($this->type == 2){//港股
        		$i = 0;
        		$a = file('/data/www/admin/data/ggdata_sort'.date('Y-m-d').'.txt');
		        foreach($a as $line => $content){
		        	$array = explode(",", $content);
		        	if($array[1] == 2){
			        	$this->listData[$i]['code'] = $array[2];
			        	$this->listData[$i]['post_num'] = $array[3];
			        	$this->listData[$i]['class'] = $array[4];
			        	$i++;
		        	}
		        }
        	}else if($this->type == 3){//港股
        		$i = 0;
        		$a = file('/data/www/admin/data/ggdata_sort'.date('Y-m-d').'.txt');
		        foreach($a as $line => $content){
		        	$array = explode(",", $content);
		        	if($array[1] == 3){
			        	$this->listData[$i]['code'] = $array[2];
			        	$this->listData[$i]['post_num'] = $array[3];
			        	$this->listData[$i]['class'] = $array[4];
			        	$i++;
		        	}
		        }
        	}else if($this->type == 4){//港股
        		$i = 0;
        		$a = file('/data/www/admin/data/ggdata_sort'.date('Y-m-d').'.txt');
		        foreach($a as $line => $content){
		        	$array = explode(",", $content);
		        	if($array[1] == "4"){
			        	$this->listData[$i]['code'] = $array[2];
			        	$this->listData[$i]['post_num'] = $array[3];
			        	$this->listData[$i]['class'] = $array[4];
			        	$i++;
		        	}
		        }
        	}

        }


        public function userRespNumInsert(){

        	$Model = M('Comment');
			$ModelBar = M('PostBar');
			$ModelComment= M('CommentCount');
            $ModelStockBar= M('StockBar');
			$time = date('Y-m-d');
			//$time = "2015-01-26";
			$list = $Model->distinct(true)->field('post_id')->where(" ctime>'".$time."'")->select();
			$num = count($list);
			if($list){
				for($i = 0; $i < $num; $i++){
					$listBar = $ModelBar->where(" post_id='".$list[$i]['post_id']."'")->order('ctime')->limit(1)->select();
					if($listBar){
	                    $listData[$i]['code'] = $listBar[0]['bar_code'];
                        $listDataStockBarClass = $ModelStockBar->where(" id='".$listBar[0]['bar_id']."'")->select();
	                    //$listData[$i]['class'] = $listBar[0]['bar_code'];
						$count = $Model->where(" post_id='".$list[$i]['post_id']."'")->count();
						$listData[$i]['resp_num'] = $count;
                        $listData[$i]['class'] =  $listDataStockBarClass[0]['class']; 
                        $listData[$i]['type'] =  $listDataStockBarClass[0]['type']; 
                       // $listData[$i]['lastPost'] = $listBar[0]['ctime'];
					} 
				}
			}

	        
			 $num = count($listData);
			 for($i = 0; $i < $num; $i++){
			 	$list = $ModelComment->where("code='".$listData[$i]['code']."' and class='".$listData[$i]['class']."'")->select();
			 	if($list){
			 		$list = $ModelComment->where()->save($listData[$i]);
			 	}else{
			 		$list = $ModelComment->add($listData[$i]);
			 	}
			 }

        }

        //public function 
    /**
     * 
    **/
    public function search(){

        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $curpage = I('param.curpage',1);
        $pagenum = I('param.pagenum',10);
        $value = I('param.value',-1);
        $this->type = '5';

        $this->user_id = I('param.user_id',$res_isLogin);
                
        $Model = M('StockBar');
        $ModelComment = M('CommentCount');
        $ModelPostTime = M('PostBar');
        $condition = "1=1 and (code='".$value."' or abbrev='".$value."' or spell='".$value."' or name='".$value."')";

        $allnum = $Model->where($condition)->count();
        $list = $Model->where($condition)->page($curpage, $pagenum)->select();
       // $list = $Model->where($condition)->select();

       // echo "con:".$condition."| count:".count($list);
 
        if($list || $list == null){
            $code = 0;
            $msg = "suc";
            if($list == null){
               $list = Array();
            }

            $this->readFile();
        	$num = count($this->listData);
        	$j = $this->pagenum*($this->curpage-1);
            
            $num = count($list);
            for($i=0; $i < $num; $i++){
                $list[$i]['ucode'] =  makeBarCode($list[$i]['code'], $list[$i]['class']);
            }
           // print_r($this->listData);
           /*
            for($i = $j; $i < $this->pagenum + $j && $i < $num; $i++){
            	if($i + 1 == $this->pagenum + $j || $i + 1 == $num){
            		//$conditionTemp = $conditionTemp." code='".$this->listData[$i]['code']."' and class='".$this->listData[$i]['class'].",)";
                    $conditionTemp = $conditionTemp." (code='".$this->listData[$i]['code']."')";
            	}else{
            		//$conditionTemp = $conditionTemp." code='".$this->listData[$i]['code']."' and class='".$this->listData[$i]['class']."') or";
                    $conditionTemp = $conditionTemp." (code='".$this->listData[$i]['code']."') or";
				}	            
            }
            */
           // echo count($list)."|".print_r($list);
        	if($list){
        		for($i = 0; $i < count($list); $i++){
                    for($j = 0; $j < count($this->listData); $j++){
                        if($list[$i]['code']== $this->listData[$j]['code']){
                            //echo "j:".$j."list:".$list[$i]['code']." listdata:".$this->listData[$j]['code'];
        		        	$list[$i]['todayPost'] = $this->listData[$j]['post_num'];
        		          	$listTemp = $ModelComment->where(" code='".$list[$i]['code']."'")->select();
        			        $list[$i]['todayResp'] = $listTemp[0]['resp_num'] ? $listTemp[0]['resp_num'] : 0;
        			        $listTime = $ModelPostTime->where(" bar_code='".$list[$i]['code']."'")->order('ctime desc')->select();
        			        $list[$i]['lastPost'] = $listTime[0]['ctime'] ? $listTime[0]['ctime'] : "00-00-00 00:00:00";
                        }
                    }
        		}
        	}

           
        }else{
            $list = Array();
            $code = 0;
            $msg = "no result";
        }

        $this->ajaxOutput(0, '', array('count'=>$allnum, 'user_id'=>$res_isLogin, 'list'=>$list));
    }

}
