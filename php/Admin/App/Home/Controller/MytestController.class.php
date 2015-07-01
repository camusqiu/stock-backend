<?php
namespace Home\Controller;
use Think\Controller;
import('Org.Util.Date');
class MytestController extends CommonController {

	/*
	PHP截取UTF-8编码的中英文字符串
	*/
	public function utf8_substr($str, $from, $len) {
		return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $from . '}' . '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $len . '}).*#s', '$1', $str);
	}

	/*
	将中英文字符串转换成拼音
	*/
	public function strtopin($chinese,$type=0) {
		$i = 0;
		$pinyin = "";
		$arr = array ();
		while (1) {
			$str = $this->utf8_substr($chinese, $i, 1);
			if (!empty ($str)) {
				$arr[$i] = $str;
				$str = iconv("utf-8", "gb2312", $str);
				if($type==1){ //转换成首字母
					$pinyin .= $this->c($str);
				}else{     //转换成全拼
					$pinyin .= $this->f($str)." ";
				}
				$i = $i +1;
			} else
				break;
		}
		return $pinyin;
	}
    public function atest($t){
        echo "atest:".$t;    
    }
	
	public function test()
	{
		$User = M('Stock');
		
		$name = $_GET['name'];
        $msg = "test";
        import('ORG.Util.Date');

        $form=D('Ask');
       // echo $form;
       $t=time();
       $test="2015-01-10 12:22:00";
       if(strlen($test)>10){
        echo "10:";
       }
       $str=substr($test, $start=0, 10);
       $this->atest($str);
       //echo "time:".date("Y-m-d", $t);
       echo "[".$this->isLogin()."]"; 
       $strDeweight = "12/13/14/15/16/11/14/12"; 
       $sign = "/";
       $strDeweight = implode($sign,array_unique(explode($sign,$strDeweight)));
       echo "deweigth:".$strDeweight."|";
       $url="http://www.gelonghui.com/forum.php?mod=viewthread&amp;tid=5694&amp;extra=";
       $url = str_replace('&amp;','&',$url);
       echo "url:".$url;
      //  echo (date("Y-m-d,strtotime($np['post_time'])));
       // echo ($Think.now);
	//	echo $_GET['name'].{$Think.version};
	//	echo $this->strtopin($name);
	}

    public function paramTest () {
        
         
         $retreat = I('param.retreat',-1);
         if($retreat==1 || $retreat==0){
              $this->retreat = $retreat;
              $this->item["retreat"] = $retreat;
              echo "a";
         }
         echo "'".$retreat."'";
    }

    public function redisRead () {
        $keyall ="bar_post_count_total";
        $key ="bar_post_count_".date('ymd');
        $key1 = "post_view_count_total";
        // $keyPost ="user_post_count_total";
        // $keyFans ="user_fans_count_total";
        // $keyBar ="bar_post_count_total";
        //$key = "ability_conf";
        //$key = "ability_user_1";
        $stime = microtime(true);
        $redis = S(array('type'=>'Redis'));
        $a =  $redis->ZSCORE($key1, 74059);
        echo (microtime(true) - $stime);
        
        //用户粉丝数
		// $listTemp = $redis->ADD($keyFans, 1010111);
  //       $list[$i]['fans'] = $listTemp ? $listTemp : 0;

        // //用户发帖总数
        //$listTemp = $redis->keys($key1);
        //var_dump($listTemp);
        //$list[$i]['post'] = $listTemp ? $listTemp : 0;

        // $listTemp = $redis->ZSCORE($key1);
        //$listTemp = $redis->zrevrange($key1, 0, 9);
    //    print_r($listTemp);
        //$listTempVal = $redis->zrevrange($key1, 0, 9, score);
     //   print_r($listTempVal);
        //for($i=0; $i<count($listTemp);$i++){
        //    $temp = $listTemp[$i];
        //    echo $listTempVal[$temp].",";
       // }
        // $list[$i]['bar'] = $listTemp ? $listTemp : 0;


    }

    public function getStockBar(){
        $Model = M('StockBar');
        //$list = $Model->getField('id,name,subtype');
        $this->StockBar = $Model->where('1=1 and state!=0')->select();
        //$this->ajaxOutput(0, '', array('list'=>$list));
    }


    public function redisAdd () {
      //  $key ="bar_post_count_total";
        //$key ="bar_post_count_".date('ymd');
        $keyPost ="user_post_count_total";
        $keyFans ="user_fans_count_total";
        $keyBar ="bar_post_count_total";
        $redis = S(array('type'=>'Redis'));
        //用户粉丝数
	//	$listTemp = $redis->ZSCORE($keyFans, 1010111);
     //   $list[$i]['fans'] = $listTemp ? $listTemp : 0;

        // //用户发帖总数
//         $listTemp = $redis->ZSCORE($keyPost, 1000001);
//         $listTemp = $redis->ZSCORE($keyFans, 1000001);
//         $listTemp = $redis->zIncrBy($keyPost, 0, 1000001);
        // $list[$i]['post'] = $listTemp ? $listTemp : 0;

         $this->getStockBar();

         var_dump($this->StockBar);

        // $listTemp = $redis->ZSCORE($keyBar, 480);
        // $list[$i]['bar'] = $listTemp ? $listTemp : 0;

        //var_dump($listTemp);

    }


    public function readFile() {
       //  $fp = fopen("/data/www/admin/App/Home/Controller/a.log", "r");//文件被清空后再写入
         
      //   echo "fp:";
      //   if($fp){ 
	     //    for($i=1;$i<=5;$i++) { 
		    //     $flag=fread($fp,100); 
		    //     if(!$flag) 
		    //     { 
			   //      echo mb_convert_encoding( "读文件失败<br>", "GBK", "UTF-8"); 
			   //      break; 
			   //  } 
		    // } 
	        
      //   }else{ 
      //   	echo mb_convert_encoding( "打开文件失败", "GBK", "UTF-8"); 
      //   } 
      //   fclose($fp);
        $Model = M('Tag');
        $ModelTagInit = M('StockTagInit');
        

    	$a = file('/data/camus/a.log');
    	$num = -1;
        $nameNum = 0;
        $codeId = 0;
        $lineTag = 0;
	    foreach($a as $line => $content){

	    	if($num == -1 || $lineTag == 1){
                //echo $num."- ".$lineTag."| ";
                if($num == -1){
                    $num = 0;
                }
                $content = trim($content);
                $arrayName[$nameNum]['name'] = $content; 
                $arrayName[$nameNum++]['tagid'] = $num;
	    		$codeId = $content;
                $lineTag = 0;
	    		continue;
	    	}

            if(strpos($content, '------') !== false){
                $lineTag = 1;
	    	}
            else{
                
	    		$array[$num]['code'] = $content;
	    		$array[$num++]['tagname'] = $codeId;
            }
	    }
	   	//print_r($array);

        for($i = 0; $i <count($arrayName); $i++){
           $condition = " 1=1 and name='".$arrayName[$i]['name']."' and type='2' and subtype='1'";
           $list = $Model->where($condition)->limit(1)->select(); 
           $arrayName[$i]['id'] = $list[0]['id'];
           //echo $condition." value:".$list[0]['id']." |";
           //var_dump($list);

        }
        //print_r($arrayName);
        for($i = 0, $k = 0; $i <count($array); $i++){
           for($j = 0; $j<count($arrayName); $j++){
              
              
              //echo "ij[".$i."][".$j."]arrayName:".$arrayName[$j]['name']." array: ".$array[$i]['tagname']." | ";
           	  if($arrayName[$j]['name'] == $array[$i]['tagname']){

              //echo "ij[".$i."][".$j."]arrayName:".$arrayName[$j]['name']." array: ".$array[$i]['tagname']." | ";
                $array[$i]['code'] = trim($array[$i]['code']);
           	  	$conditionTemp = "code='".$array[$i]['code']."'";
           	  	$listTemp = $ModelTagInit->where($conditionTemp)->select();
           	  	if($listTemp){
           	  		if($listTemp[0]['gn_tag'].length > 0){
           	  			$array[$i]['gn_tag'] = "_".$arrayName[$j]['id'];
           	  		}else{
           	  			$array[$i]['gn_tag'] = $arrayName[$j]['id'];
           	  		}
           	  		echo " newTag[".$array[$i]['gn_tag']."] oldTag:".$listTemp[0]['gn_tag'];
           	//  		print_r($listTemp);
           	  		//$listTemp = $ModelTagInit->where($conditionTemp)->save($array[$i]['gn_tag']);
           	  	}
           	  	break;
           	  }
           }
        }
       
    }

    public function writeToFile1(){


     //F("ggdata.txt", $value='12345', $path="/data/www/admin/data/");
     F("ggdata.txt", $value='12345', $path="/data/www/admin/App/Runtime/Data/");

    }
    public function writeToFile(){
        ///$fp = fopen("/data/www/admin/data/ggdata.txt", "a");//文件被清空后再写入
        //$filename = '/data/www/stock/App/Runtime/Logs/alipay_log_'.date('y_m').".txt";
        $filename = '/data/www/admin/data/ggdata.txt';
        $fp = fopen($filename, "a");//文件被清空后再写入
        var_dump($fp); 
        echo "fp:";
        //if($fp){ 
		        $flag=fwrite($fp,"Hello World"); 
                var_dump($flag);
		        if(!$flag) 
		        { 
			        echo "写入文件失败<br>"; 
			    } 
	        
       // }else{ 
       // 	echo "打开文件失败"; 
        //} 
        fclose($fp); 
    }

	public function ruku(){

		$Model = M('PostBar');
		$time = date('Y-m-d');
		$list = $Model->distinct(true)->field('post_id')->where(" ctime>'".$time."'")->select();
		$num = count($list);
		if($list){
			$k = 0;
			for($i = 0; $i < $num; $i++){
				$listbar = $Model->where(" post_id='".$list[$i]['post_id']."'")->select();
				$listAll[$k] = $listbar;
//				if(($listAll[$k]['bar_id'] == '2504' || $listAll[$k]['bar_id'] == '2505') && ){
//
//				}
				$k++;
			}
			$this->ajaxOutput(0, 'suc', array('count'=>count($listbar), 'list'=>$listAll));
		}
      
	}

    public function getMax() {
         $Model = M('StockBar');
         //$list = $Model->where('1=1')->master(true)->order('id desc')->limit(1)->select();
         $b[0]='1';
         $b[1]='1';
         $b[2]='1';
         //$a = array();
         $a['id']=$b; 
         echo $a['id'];
		 $this->ajaxOutput(0, 'suc', array('count'=>count($listbar), 'list'=>$listAll));
    }

    public function test1 () {
       // $ss = D('Mytest');
       // $ss->testMy();
      $key = "bar_post_count_total";
//      $key = "bar_post_count_".date('ymd');
      //$redis = S(array('type'=>'Redis'));
      $val = RS('bar_HK00700');

///            $redis = $this->getRedis();
       //$val = $redis->ZSCORE($key, "3755");
       var_dump($val);
 //   echo  date("Y-m-d h:i:s");
 //getUinfo("1010102", array(), 'must');
    }

    public function test2(){
        //if(is_numeric($a)){

         //   echo is_numeric($a).",".is_numeric($b);
        //}
 //       echo "a:".intval($a)*100;
//        echo " c:".intval($c)*100;
        
       // echo date("Y-m-d");
        //echo  makeBarCode("600000",'11211');

        $list = array('1','2');
        $data = array();
        foreach($list as $a)
        {
             $temp['a'] = $a;
             $temp['b'] = "x";
             $data[] = $temp;
        }
        var_dump($data);
        $this->ajaxOutput($code, $msg, array('count'=>count($data), 'list'=>$data));

        //echo date("Y-m-d H:i:s");

    }
    
    public function test3(){
        $a = array('a','b'); 
        $b = array('c', 'b'); 
        $c = $a + $b; 
        var_dump($c); 
        var_dump(array_merge($a, $b)); 

        $a = array(0 => 'a', 1 => 'b'); 
        $b = array(0 => 'c', 1 => 'b'); 
        $c = $a + $b; 
        var_dump($c); 
        var_dump(array_merge($a, $b)); 

        $a = array('a', 'b'); 
        $b = array('0' => 'c', 1 => 'b'); 
        $c = $a + $b; 
        var_dump($c); 
        var_dump(array_merge($a, $b)); 

        $a = array(0 => 'a', 1 => 'b'); 
        $b = array('0' => 'c', '1' => 'b'); 
        $c = $a + $b; 
        var_dump($c); 
        var_dump(array_merge($a, $b)); 


        $a=array("a"=>"Horse","b"=>"Dog");
        $b=array("c"=>"Cow","b"=>"Cat");
        var_dump($a+$b);
        var_dump(array_merge($a, $b));
        $this->ajaxOutput($code, $msg, array('count'=>count($data), 'list'=>$data));
    
    }

    public function test4 () {
//        $a=array('1','2');
 //       var_dump($a);
  //     unset($a);
   //     var_dump($a);
         $config =    array(
             'fontSize'    =>    30,    // 验证码字体大小
             'length'      =>    3,     // 验证码位数
             'useNoise'    =>    false, // 关闭验证码杂点
         );

         $Verify = new \Think\Verify($config);
         return $Verify->entry();
    }

    public function test5(){
         $adminModel = M('Admin');
         $list = M('Admin')->where("id='100'")->select();
         if($list !== null){
            var_dump($list);
         }else{
            echo "not exist";
         }
    }

    public function test6 () {
            $this->a = "1";
            $this->test7();
            echo $this->a;
            //echo "Tomorrow:  ".date('Y-m-d',strtotime('+1 day'));
    }

    public function test7 () {
            $Model = M('StockBar');
            $a = write("hello world", "error");
            var_dump($a);
        //   var_dump(getBarByCode("SZ000012"));
    }

    public function test8 () {
 //       $data['hy_tag'] = substr($hy, 0, -1);
        system("/data/camus/1.sh",$status);
        if($status == "true"){
                echo "1";
                echo "1";
        }else{
                echo "0";
                echo "0";
        }
  //      var_dump($data);
    }
    
    public function test9 () {
       // $stockTagModel = M('NewsInfoTemp');
        //$map['source']='财华网';
        //$a['id'] = array('lt',1);

        //$VList= M('VUserApply')->table('v_user_apply v')->join('user u on u.id = v.user_id')->field('v.ctime as ctime, v.user_id as user_id, v.state as state, u.phone_num as phone_num, u.name as name, u.fund as fund')->where('v.state=1')->select();
        //$VList= M('VUserApply')->table('v_user_apply v, user u')->field('v.ctime as ctime, v.user_id as user_id, v.state as state, u.phone_num as phone_num, u.name as name, u.fund as fund')->join('user u on u.id = v.user_id')->where('v.state=1')->select();
        //$VList= M('VUserApply')->table('v_user_apply v')->join('user u on u.id = v.user_id')->where('v.state=1')->select();
//        $UList = M('User')->where("id='1010111'")->getField('v_state');
            $str=" <u>www.baidu.com</u>1213<div>123123121</div> ";
            $regex='/^((http|ftp|https):\/\/)?[\w-_.]+(\/[\w-_]+)*\/?$/';
            $regex = '@(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))@';
            $url = htmlspecialchars_decode($str);
            //$a= preg_replace($regex, "<a href=\"".$arr[0][$i]."\"><font color=\"#1287CF\">".$arr[2][$i]."<font/>", $url);
            preg_match_all($regex,$url, $arr);
            var_dump($arr);
            
            for($i=0,$j=count($arr[0]);$i<$j;$i++){ 
                    $str = str_replace($arr[0][$i],"<a href=\"".$arr[0][$i]."\"><font color=\"#1287CF\">".$arr[0][$i]."</font></a>",$str); 
                    echo $str;
            }
            
 //       var_dump($UList);


    }

    public function test10 (){

        echo makeBarCode("603838","11211");
    }
}
?>
