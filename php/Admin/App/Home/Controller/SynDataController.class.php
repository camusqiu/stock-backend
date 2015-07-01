<?php
namespace Home\Controller;
use Think\Controller;
import('Org.Util.Date');


class SynDataController extends CommonController {
    private $filename = "";
 
    //上传
    public function upload() {
      if ((($_FILES["file"]["type"] == "image/gif")
      || ($_FILES["file"]["type"] == "image/jpeg")
      || ($_FILES["file"]["type"] == "image/pjpeg")
      || ($_FILES["file"]["type"] == "image/bmp")
      || ($_FILES["file"]["type"] == "text/plain"))
      && ($_FILES["file"]["size"] < 20000000)){
        if ($_FILES["file"]["error"] > 0){
          $msg =  "Return Code: " . $_FILES["file"]["error"] . "<br />";
          $this->ajaxOutput(-1, '上传错误:'.$msg, array('list'=>array()));
        }else{
          // echo "Upload: " . $_FILES["file"]["name"] . "<br />";
          // echo "Type: " . $_FILES["file"]["type"] . "<br />";
          // echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
          // echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";

          if (file_exists("/data/www/admin/data/upload" . $_FILES["file"]["name"])){
            $msg =  $_FILES["file"]["name"] . " already exists. ";
            $this->ajaxOutput(-1, '该文件已经存在:'.$msg, array('list'=>array()));
          }else{
            move_uploaded_file($_FILES["file"]["tmp_name"],  "/data/www/admin/data/upload/".$_FILES["file"]["name"]);
            $msg =  "Stored in: " . "/data/www/admin/data/upload/".$_FILES["file"]["name"];

            //根据上传文件名区分哪个市场概念
            if ($_FILES["file"]["name"] === "AStock_GN.txt") {
              $this->type = "1";
              $this->hygn = "2";
            }else if ($_FILES["file"]["name"] === "HKStock_GN.txt") {
              $this->type = "2";
              $this->hygn = "2";
            }else if ($_FILES["file"]["name"] === "USStock_GN.txt") {
              $this->type = "3";
              $this->hygn = "2";
            }else if ($_FILES["file"]["name"] === "AStock_HY.txt") {
              $this->type = "1";
              $this->hygn = "1";
            }else if ($_FILES["file"]["name"] === "HKStock_HY.txt") {
              $this->type = "2";
              $this->hygn = "1";
            }else if ($_FILES["file"]["name"] === "USStock_HY.txt") {
              $this->type = "3";
              $this->hygn = "1";
            }
            $this->filename = $_FILES["file"]["name"];
            $this->reset();
            //$this->ajaxOutput(0, '上传成功:'.$msg, array('list'=>array()));
          }
        }
      }else{
          $msg =  $_FILES["file"]["type"].", size:".$_FILES["file"]["size"]." ,"."Invalid file";
          $this->ajaxOutput(-1, '上传失败:'.$msg, array('list'=>array()));
      }
    }


    //入库
    public function addTagUpload_AStock_GN () {
        $filePath = file('/data/www/admin/data/upload/AStock_GN.txt');
        $type = 2;
        $subtype = 1;
        $this->addTagUpload($filePath, $type, $subtype);
    }

    public function addTagUpload_HKStock_GN () {
        $filePath = file('/data/www/admin/data/upload/HKStock_GN.txt');
        $type = 2;
        $subtype = 2;
        $this->addTagUpload($filePath, $type, $subtype);
    }

    public function addTagUpload_USStock_GN () {
        $filePath = file('/data/www/admin/data/upload/USStock_GN.txt');
        $type = 2;
        $subtype = 3;
        $this->addTagUpload($filePath, $type, $subtype);
    }

    public function addTagUpload_AStock_HY () {
        $filePath = file('/data/www/admin/data/upload/AStock_HY.txt');
        $type = 1;
        $subtype = 1;
        $this->addTagUpload($filePath, $type, $subtype);
    }

    public function addTagUpload_HKStock_HY () {
        $filePath = file('/data/www/admin/data/upload/HKStock_HY.txt');
        $type = 1;
        $subtype = 2;
        $this->addTagUpload($filePath, $type, $subtype);
    }

    public function addTagUpload_USStock_HY () {
        $filePath = file('/data/www/admin/data/upload/USStock_HY.txt');
        $type = 1;
        $subtype = 3;
        $this->addTagUpload($filePath, $type, $subtype);
    }

    public function addTagUpload ($filePath, $type, $subtype) {
        set_time_limit(0);
        $Model = M('Tag');
        $ModelTagUpload = M('TagUpload');
        $ModelStock = M('StockBar');

        $num = -1;
        $tagId = 0;
        $tagname = '';
        $lineTag = 0;
        $i = 0;

        //分析文本 array存code，所属概念名称；arrayName存放 概念名称和id
        foreach($filePath as $line => $val){
          $i++;
          $content = trim($val);

          //第一行为概念名称，用$codeId记录
          if($num == -1 || $lineTag == 1){
            $tagname = $content;
            if(!$tagname){
                continue;     
            }
            if($num == -1){
                $num = 0;
            }
            $lineTag = 0;

            $tagId = $Model->where("name='".$tagname."' and type='".$type."' and subtype='".$subtype."'")->getField('id');

            continue;
          }

          //'------'为概念分割线
          if(strpos($content, '------') !== false){
              $lineTag = 1;
          }else if (!$content){
              continue;
          }else{
              $data['tag_id'] = $tagId;
              $data['tag_name'] = $tagname;
              $data['code'] = $content;
              $class = $ModelStock->where("code='".$data['code']."'")->limit(1)->getField('class');
              $data['ucode'] = makeBarCode($data['code'], $class);
              $data['type'] = $type;
              $data['subtype'] = $subtype;
              $list = $ModelTagUpload->add($data);
              var_dump($data);
          }

          // 操作2000次  休息一秒
          if ($i%2000 == 0 ) {
            sleep(1);
          }
          
        }
    }


    public function renewStockTagInit_AStock_HY () {
        // 1 A  2 HK 3 US
        $type = 1;
        // 1 HY 2 GN
        $subtype = 1;
        $this->renewStockTagInit($type, $subtype);

    }

    public function renewStockTagInit_HKStock_HY () {
        $type = 2;
        $subtype = 1;
        $this->renewStockTagInit($type, $subtype);
    }

    public function renewStockTagInit_USStock_HY () {
        $type = 3;
        $subtype = 1;
        $this->renewStockTagInit($type, $subtype);
    }

    public function renewStockTagInit_AStock_GN () {
        $type = 1;
        $subtype = 2;
        $this->renewStockTagInit($type, $subtype);
    }

    public function renewStockTagInit_HKStock_GN () {
        $type = 2;
        $subtype = 2;
        $this->renewStockTagInit($type, $subtype);
    }

    public function renewStockTagInit_USStock_GN () {
        $type = 3;
        $subtype = 2;
        $this->renewStockTagInit($type, $subtype);
    }

    public function renewStockTagInit ($type, $subtype) {
        $Model = M('Tag');
        $ModelTagInit = M('StockTagInit');
        $ModelTagUpload = M('TagUpload');
        $ModelTagComp = M('TagComponent');
        $ModelStock = M('StockBar');
        $now = date("Y-m-d H:i:s");
        $nowday = date("Y-m-d");

        //获得所有可用tag,根据dealstates字段判断是否为新增
        $LT = $Model->where("1=1 and available='1'")->select();  //key value
        if ($LT && $LT != null ) {
          $num = count($LT);
          for ($i=0; $i < $num; $i++) {
               //$LTag[$LT[$i]['id']] = 1;
               $LTag[$LT[$i]['id']] = $LT[$i]['dealstatus'];
          }
        }

        //获得所有股吧
        $LSB = $ModelStock->where("1=1 and type='".$type."'")->select();
        if ($LSB && $LSB != null ) {
          $num = count($LSB);
          for ($i=0, $j=0; $i < $num; $i++) {
            //去掉stockbar表中的指数 
            if ($LSB[$i]['class']%10 != 2){
                $data[$j++] = $LSB[$i];
            } 
          }
        }

        //遍历所有股吧，在tagupload表中查找关联关系
        $num = count($data);
        for ($i=0; $i < $num; $i++) {
            $data[$i]['tag'] = ""; 
            $list = $ModelTagUpload->where("code='".$data[$i]['code']."' and type='".$subtype."' and subtype='3' and ctime>'2015-06-01 10:00:00'")->select();
            //$list = $ModelTagUpload->where("code='".$data[$i]['code']."' and type='".$subtype."' and ctime>'2015-05-29 15'")->select();
            if ($list && $list != null) {
                $nj = count($list);
                for ($j=0; $j < $nj; $j++) {
                    if ($j+1 == $nj) {
                        $data[$i]['tag'] = $data[$i]['tag'].$list[$j]['tag_id'];
                    }else{
                        $data[$i]['tag'] = $data[$i]['tag'].$list[$j]['tag_id']."_";
                    } 
                }
                $data[$i]['type'] = $subtype;
            }
        }

        //遍历股吧数组，对比taginit表中gn_tag, 修改tag_componet表：删除的剔除时间设置为当前时间，增加的加入时间为当前时间
        for ($i=0; $i < $num; $i++) { 
            $ucode = makeBarCode($data[$i]['code'], $data[$i]['class']);
            if ($data[$i]['type'] == "2") {
                //概念 taginit表更新数据
                $gn = $ModelTagInit->where("code='".$ucode."'")->getField('gn_tag');
                $tData['gn_tag'] = $data[$i]['tag'];
                if (strlen($tData['gn_tag']) > 0) {
                    $t = $ModelTagInit->where("code='".$ucode."'")->save($tData);
                }
            }else if ($data[$i]['type'] == "1"){
                //行业 taginit表更新数据
                $gn = $ModelTagInit->where("code='".$ucode."'")->getField('hy_tag');
                $hyData['hy_tag'] = $data[$i]['tag'];
                if (strlen($hyData['hy_tag']) > 0) {
                    $t = $ModelTagInit->where("code='".$ucode."'")->save($hyData);
                }
            }

            // 对比gn与data[$i]['tag']
            $onum = 0; $nnum = 0;
            if (strlen($gn) > 0) {
                $old = explode("_", $gn);
                $onum = count($old);
            }
            if (strlen($data[$i]['tag']) > 0) {
                $new = explode("_", $data[$i]['tag']);
                $nnum = count($new);
            }

            for ($j=0; $j < $onum; $j++) { 
                //记录删除的变更
                $index = strpos($data[$i]['tag'], $old[$j]);
                if ($index === false) {
                    $sData['out_time'] = $now;
                    $sData['ucode'] = $ucode;
                    $sData['tag_id'] = $old[$j];
                    //var_dump($sData);
                    $c = $ModelTagComp->where("ucode='".$ucode."' and tag_id='".$old[$j]."'")->save($sData);
                   // if($ucode == "SH600008"){
                    //   echo "[del]old:".$old[$j].", new:".$data[$i]['tag'];
                    //   var_dump($sData);
                    //}
                }
            }

            for ($j=0; $j < $nnum; $j++) { 
                //记录新增的(概念新增，概念成分变更)
                $index = strpos($gn, $new[$j]);
                if ($index === false) {
                    if ($LTag[$new[$j]] == 1) {
                        $aData['in_time'] = "1990-01-01 00:00:00";
                    }else{
                        $aData['in_time'] = $now;
                    }
                    
                    $aData['tag_id'] = $new[$j];
                    $aData['code'] = $data[$i]['code'];
                    $aData['ucode'] = $ucode;
                    //var_dump($aData);
                    //if($ucode == "SH600008"){
                    //   echo "[add]old:".$gn.", new:".$new[$j];
                    //   var_dump($aData);
                   // }
                    //如果component表已经存在，不用新增
                    $isExist = $ModelTagComp->where("tag_id='".$aData['tag_id']."' and ucode='".$ucode."'")->select();
                    if ($isExist == null) {
                        $c = $ModelTagComp->add($aData);
                    }
                }
            }
        }
    }

    //统计各个吧贴用户发帖总数
    public function UserPostBarCount () {
        $ModelPost = M('Post');
        $ModelPostBar = M('PostBar');
        for ($i=0; $i < 20000; $i++) {
            $data[$i] = 0;
        }  


        $LP = $ModelPost->where("1=1 and type='1'")->select();  //key value
        $num = count($LP);
        for ($i=0; $i < $num; $i++) {
            $LPB = $ModelPostBar->where("1=1 and post_id='".$LP[$i]['id']."'")->select();  //key value
            $numPB = count($LPB);
            for ($j=0; $j < $numPB; $j++) { 
                $data[$LPB[$j]['bar_id']]++;
            }
        }
        print_r($data);
  }

  //统计各个吧贴用户今日发帖总数
    public function UserPostBarCountDay () {
        $nowday = date("Y-m-d");
        $ModelPost = M('Post');
        $ModelPostBar = M('PostBar');
        for ($i=0; $i < 20000; $i++) {
            $data[$i] = 0;
        }  


        $LP = $ModelPost->where("1=1 and type='1' and ctime>='".$nowday."'")->select();  //key value
        $num = count($LP);
        //echo $num;
        for ($i=0; $i < $num; $i++) {
            $LPB = $ModelPostBar->where("1=1 and post_id='".$LP[$i]['id']."'")->select();  //key value
            $numPB = count($LPB);
            for ($j=0; $j < $numPB; $j++) { 
                $data[$LPB[$j]['bar_id']]++;
            }
        }
        print_r($data);
  }
    
}
?>
