<?php
namespace Home\Controller;
use Think\Controller;
//require_once('/data/www/admin/App/Home/Model/LanguageModel.class.php');

class CountController extends CommonController {
    private $initStockBarId = array();
    private $listData = array();
    private $listDataTemp = array();
    private $listAll = array();
    private $listZHAll = array();
    private $curpage = 1;
    private $pagenum = 10;
    private $countall = 0;

    public function initStockBar() {
        $Model = M('StockBar');
        $list = $Model->where('1=1')->order('id desc')->limit(1)->select();

        $num = $list[0]['id'];

        $this->initStockBarId[0]['isExist'] = 0;

        $count = 0;
        $list = $Model->where('1=1')->select();
        for($i = 1, $j = 1; $i < $num+1; $i++){
            if($list[$j-1]['id'] == $i){
                $this->initStockBarId[$i]['isExist'] = 1;
                $j++;
            }else{
                $this->initStockBarId[$i]['isExist'] = 0;
                $count++;
                //echo "i:".$i."-";
            }
            $this->initStockBarId[$i]['num'] = $count;
        }
        //print_r($this->initStockBarId);
    }

    public function initZHBarArray() {
        $Model = M('StockBar');
        $list = $Model->where('type="4"')->page($this->curpage, $this->pagenum)->select();
        //$list = $Model->where('type="6"')->page($this->curpage, $this->pagenum)->select();

        $num = count($list);
        for($i = 0; $i < $num; $i++){
            $this->listAll[$i]['code'] = "";
            $this->listAll[$i]['today'] = 0;
            $this->listAll[$i]['allnum'] = 0;
            $this->listAll[$i]['atoday'] = 0;
            $this->listAll[$i]['aallnum'] = 0;
            $this->listAll[$i]['utoday'] = 0;
            $this->listAll[$i]['uallnum'] = 0;
            $this->listAll[$i]['fansnum'] = 0;
            // if($i < 6){
            //     $this->listAll[$i]['type'] = "1";
            // }else if ($i >= 6 && $i < 12){
            //     $this->listAll[$i]['type'] = "2";
            // }else if ($i >= 12){
            //     $this->listAll[$i]['type'] = "3";    
            // }
            if($i < 4){
                $this->listAll[$i]['type'] = "1";
            }else if ($i >= 4 && $i < 8){
                $this->listAll[$i]['type'] = "2";
            }else if ($i < 12){
                $this->listAll[$i]['type'] = "3";    
            }
        }
    }

    public function initGGBarArray() {
        $Model = M('Stock_bar');
        $this->countall = $Model->count();
        $list = $Model->where('1=1')->page($this->curpage, $this->pagenum)->select();

       // $num = $list[count($list)-1]['id'] - ($this->curpage-1)*$this->pagenum; 
       $id = $list[0]['id'];
       //echo $id.":";
       $num = $list[count($list)-1]['id'] - ($this->curpage-1)*$this->pagenum - $this->initStockBarId[$id-1]['num']; 
       //echo $this->initStockBarId[$id-1]['num'].":";
        for($i = 0; $i < $num; $i++){
            $this->listAll[$i]['code'] = "";
            $this->listAll[$i]['today'] = 0;
            $this->listAll[$i]['allnum'] = 0;
            $this->listAll[$i]['atoday'] = 0;
            $this->listAll[$i]['aallnum'] = 0;
            $this->listAll[$i]['utoday'] = 0;
            $this->listAll[$i]['uallnum'] = 0;
            $this->listAll[$i]['fansnum'] = 0;
            $this->listAll[$i]['type'] = $list[$i]['type'];
        }
       // print_r($this->listAll);
    }


    public function getStockBar_admin($val, $day) {
        set_time_limit(0);
        $Model = M('Post_bar');
        $time = date('Y-m-d');
        $ctime = I('param.ctime',$time);
        $ctimeend = I('param.ctimeend',$time);
        //$ctime = "2014-05-25";
        //$ctimeend = "2015-05-27";
        if($val == "ZH" && $day == "today"){
            $condition = sprintf("bar_type='4' and ctime>'%s' and bar_code>'018'", $time);
            $list = $Model->where($condition)->select();
        }
        else if($val == "ZH" && $day == "allnum"){
            $condition = sprintf("bar_type='4' and ctime>'%s' and ctime<='%s' and bar_code>'018'", $ctime, $ctimeend." 23:59:59");
            $list = $Model->where($condition)->select();
        }
        else if($val == "GG" && $day == "today"){
            $condition = sprintf("bar_type!='4' and ctime>'%s'", $time);
            $list = $Model->where($condition)->select();
        }
        else if($val == "GG" && $day == "allnum"){
            $condition = sprintf("bar_type!='4' and ctime>'%s' and ctime<='%s'", $ctime, $ctimeend." 23:59:59");
            $list = $Model->where($condition)->select();
        }

        if($list){
            $this->listDataTemp = $list;
        }
    }


	public function getStockBar_user($val) {
		$Model = M('StockBar');
		if($val == "ZH"){
			$list = $Model->where('1=1 and type="4"')->select();
            //$list = $Model->where('1=1 and type="6"')->select();
		}elseif ($val == "GG") {
			$list = $Model->where('1=1 and type!="4"')->page($this->curpage, $this->pagenum)->select();
		}else{
            $list = $Model->where('1=1')->page($this->curpage, $this->pagenum)->select();
        }

		if($list){
			$this->listData = $list;
		}
	}

    public function countZHBarData(){
        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);

        $this->initZHBarArray();
        $this->countZHBarData_user();
        $this->countZHBarData_admin();
        $this->countBarFans();
        
        $this->ajaxOutput(0, '', array('count'=>count($this->listAll), 'list'=>$this->listAll));                                                                                                                               
    }

    public function countGGBarData(){
        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $this->initStockBar();

        $this->initGGBarArray();
        $this->countGGBarData_user();
        $this->countGGBarData_admin();
        $this->countBarFans();
        
        $this->writeToFile();
        $this->ajaxOutput(0, '', array('count'=>$this->countall, 'list'=>$this->listAll));                                                                                                                               
    }

    public function countGGBarDataAndSort(){
        // $this->curpage = I('param.curpage',1);
        // $this->pagenum = I('param.pagenum',30000);
        set_time_limit(0);
        $this->curpage = 1;
        $this->pagenum = 30000;
        $this->initStockBar();

        $this->initGGBarArray();
        //print_r($this->listData);
        
        $this->countGGBarData_user();
        $this->sort();        
        $this->writeSortDataToFile();
        $this->ajaxOutput(0, '', array('count'=>$this->countall, 'list'=>$this->listAll));                                                                                                                               
       
    }

	public function countZHBarData_user(){

		$this->getStockBar_user("ZH");

		//股吧今日发帖数
		$key ="bar_post_count_".date('ymd');
        $redis = S(array('type'=>'Redis'));

        for($i = 0; $i < count($this->listData); $i++){
            // $list = $redis->ZSCORE($key, $i+2504);
            if($i == 0){
                $list = $redis->ZSCORE($key, $i+11771);
            }else{
                $list = $redis->ZSCORE($key, $i+11772); 
            }
            
            $this->listAll[$i]['utoday'] = $list ? $list : 0;
            $this->listAll[$i]['code'] = $this->listData[$i]['code'];
            $this->listAll[$i]['name'] = $this->listData[$i]['name'];
            $this->listAll[$i]['id'] = $this->listData[$i]['id'];
        }

        //股吧总发帖数
		$key ="bar_post_count_total";
        $redis = S(array('type'=>'Redis'));

        for($i = 0; $i < count($this->listData); $i++){
            // $list = $redis->ZSCORE($key, $i+2504);
            if($i == 0){
                $list = $redis->ZSCORE($key, $i+11771);
            }else{
                $list = $redis->ZSCORE($key, $i+11772); 
            }
            $this->listAll[$i]['uallnum'] = $list + $this->listAll[$i]['utoday'];
        }
	}

    public function countZHBarData_admin(){

        //股吧今日发帖数
        $this->getStockBar_admin("ZH", "today");

        $strid = "";
        for($i = 0; $i < count($this->listDataTemp); $i++){
            $strid = intval($this->listDataTemp[$i]['bar_id']);
            //echo $strid.";";
            if($strid == "11771"){
                //$this->listAll[$strid-2504]['atoday']++;
                $this->listAll[$strid-11771]['atoday']++;
            }else{
                $this->listAll[$strid-11772]['atoday']++;
            }
        }

        //echo count($this->listAll)."|";
        //股吧总发帖数
        $this->getStockBar_admin("ZH", "allnum");
        for($i = 0; $i < count($this->listDataTemp); $i++){
            $strid = intval($this->listDataTemp[$i]['bar_id']);
            // $this->listAll[$strid-2504]['aallnum']++;
            if($strid == "11771"){
                $this->listAll[$strid-11771]['aallnum']++;
            }else{
                $this->listAll[$strid-11772]['aallnum']++;
            }
            
        }        
        //echo count($this->listAll);
    }


	public function countGGBarData_user(){

		$this->getStockBar_user();

		//股吧今日发帖数
		$key ="bar_post_count_".date('ymd');
        $redis = S(array('type'=>'Redis'));

        for($i = 0; $i < count($this->listData); $i++){
            $bar_id = $this->listData[$i]['id'];
            $list = $redis->ZSCORE($key, $bar_id);
            $this->listAll[$i]['utoday'] = $list ? $list : 0;
            $this->listAll[$i]['code'] = $this->listData[$i]['code'];
            $this->listAll[$i]['name'] = $this->listData[$i]['name'];
            $this->listAll[$i]['class'] = $this->listData[$i]['class'];
            $this->listAll[$i]['id'] = $this->listData[$i]['id'];
        }


        //股吧总发帖数
		$key ="bar_post_count_total";
        $redis = S(array('type'=>'Redis'));

        for($i = 0; $i < count($this->listData); $i++){
            $list = $redis->ZSCORE($key, $this->listData[$i]['id']);
            $this->listAll[$i]['uallnum'] = $list + $this->listAll[$i]['today'];
        }
	}


    public function countGGBarData_admin(){

        //股吧今日发帖数
        $this->getStockBar_admin("GG", "today");

        $strid = "";
        $Num = count($this->listData);

       // print_r($this->listData);
        $delnum = ($this->curpage - 1)*$this->pagenum+1;
//        $Model = M('StockBar');
//        $list = $Model->where('1=1')->limit($delnum)->select();
//        $delnum = $list[count($list)-1]['id'];
        
        //print_r($this->listAll);
        //echo count($this->listDataTemp);
        for($i = 0; $i < count($this->listDataTemp); $i++){
            if($this->listDataTemp[$i]['bar_id'] >= $this->listData[0]['id'] && $this->listDataTemp[$i]['bar_id'] <= $this->listData[$Num-1]['id']){
                $strid = intval($this->listDataTemp[$i]['bar_id']);
                $delnum = $delnum + $this->initStockBarId[$strid]['num'];
                $this->listAll[$strid-$delnum]['atoday']++;
                if($i == 2503){
                    //print_r($this->listAll[$strid-$delnum]['atoday']);
                }
            }
        }

        //股吧总发帖数
        $this->getStockBar_admin("GG", "allnum");
//        echo count($this->listDataTemp);
        for($i = 0; $i < count($this->listDataTemp); $i++){
            if($this->listDataTemp[$i]['bar_id'] >= $this->listData[0]['id'] && $this->listDataTemp[$i]['bar_id'] <= $this->listData[$Num-1]['id']){    
                $strid = intval($this->listDataTemp[$i]['bar_id']);
                //$delnum = $delnum + $initStockBarId[$i]['num'];
                $delnum = $this->initStockBarId[$strid]['num'];
                $this->listAll[$strid-$delnum-1]['aallnum']++;
            }
            if($this->listDataTemp[$i]['bar_id']==2522){
 //               echo "strid:".$strid."delnum".$delnum."==";
        //        print_r($this->listDataTemp[$i]);
            }
        }
        //print_r($this->listAll);
    }

    public function writeToFile(){
       $fp = fopen("/data/www/admin/data/ggdata.xls", "w");//文件被清空后再写入

        if($fp){ 
            //head
            $content = "序号"."\t"."ID"."\t"."市场"."\t"."股吧"."\t"."代码"."\t"."累计关注人数"."\t"."总帖数(总数)"."\t"."总帖数(今日)"."\t"."用户发帖(总数)"."\t"."用户发帖(今日)"." \r\n";
            $content = mb_convert_encoding( $content, "GBK", "UTF-8");
            $flag=fwrite($fp,$content); 
            //$flag=fwrite($fp,"1".$i." "."%s\r\n", $content); 
            //var_dump($flag);
            if(!$flag) 
            { 
                $this->ajaxOutput(0, 'write title to file failed', array());
            }

            $count = $this->countall; 
            //'count'=>$this->countall, 'list'=>$this->listAll)
//            print_r($this->listAll);
            for($i = 0;$i < $count; $i++) {
                $a =  $this->listAll[$i];

                if($a['type'] == "1"){
                    $a['typename'] = "沪深";
                }else if($a['type'] == "2"){
                    $a['typename'] = "港股";
                }else if ($a['type'] == "3"){
                    $a['typename'] = "美股";
                }

               // $content = $i."\t"."[".$a['id']."]"."\t".$a['typename']."\t".$a['name']."\t".$a['code']."\t".$a['fansnum']."\t".$a['uallnum']."\t".$a['utoday']."\t"."aallnum"."\t"."atoday"." \r\n";
                $content = $i."\t"."[".$a['id']."]"."\t".$a['typename']."\t".$a['name']."\t".$a['code']."\t".$a['fansnum']."\t".$a['uallnum']."\t".$a['utoday']."\t".$a['aallnum']."\t".$a['atoday']." \r\n";
                $content = mb_convert_encoding( $content, "GBK", "UTF-8");
                $flag=fwrite($fp,$content); 
                //$flag=fwrite($fp,"1".$i." "."%s\r\n", $content); 
                if(!$flag) 
                { 
                    $this->ajaxOutput(0, 'write content to file failed', array());
                    break; 
                } 
                //$count+=$flag; 
            } 
            
            //echo "共写入".$count."个字符"; 
        }else{ 
            $this->ajaxOutput(0, 'fopen failed', array());
        } 
        fclose($fp); 
    }

    public function writeSortDataToFile(){
        $time = date('Y-m-d');
        $fp = fopen("/data/www/admin/data/ggdata_sort".$time.".txt", "w");//文件被清空后再写入

        if($fp){ 
            $count = $this->countall; 

            for($i = 0;$i < $count; $i++) {
                $a =  $this->listAll[$i];
                $content = $i.",".$a['type'].",".$a['code'].",".$a['utoday'].",".$a['class']." \r\n";
                $flag=fwrite($fp,$content); 
                if(!$flag) 
                { 
                    $this->ajaxOutput(0, 'writeToFile failed', array());
                    break; 
                } 
            } 
            
        }else{ 
            $this->ajaxOutput(0, 'fopen failed', array());
        } 
        fclose($fp); 
    }

    public function sort() {
        $count = $this->countall; 
        for($i = 0; $i < $count; $i++) {
            $max = $this->listAll[$i]['utoday'];
            $tag = $i;
            for($j = $i + 1; $j < $count; $j++){
                $b = $this->listAll[$j]['utoday'];
                if($max < $b){
                    $tag = $j;
                    $max = $b;
                }
            }
            $temp = $this->listAll[$tag];
            $this->listAll[$tag] = $this->listAll[$i];
            $this->listAll[$i] = $temp;
        } 
    }

	public function countBarFans(){

		// $this->getStockBar("GG");

		// //股吧今日发帖数
		// $key ="bar_post_count_".date('ymd');
  //       $redis = S(array('type'=>'Redis'));

  //       $this->ajaxOutput(0, '', array('count'=>count($this->listAll), 'list'=>$this->listAll));                                                                                                                               
	}

}
?>
