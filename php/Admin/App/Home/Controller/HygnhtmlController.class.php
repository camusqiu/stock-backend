<?php
namespace Home\Controller;
use Think\Controller;
import('Org.Util.Date');
class HygnhtmlController extends CommonController {

    public function cn_hy_html () {
        $this->newhtml(1,1);
    }

    public function uk_hy_html () {
        $this->newhtml(1,2);
    }

    public function us_hy_html () {
        $this->newhtml(1,3);
    }

    public function cn_gn_html () {
        $this->newhtml(2,1);
    }

    public function hk_gn_html () {
        $this->newhtml(2,2);
    }

    public function us_gn_html () {
        $this->newhtml(2,3);
    }

    public function newhtml ($type, $subtype) {
        // $type = I('param.type',-1);
        // $subtype = I('param.subtype',-1);

        $condition = "1=1 and available";
        if ($type && $type != -1) {
            $condition = $condition." and type='".$type."'";
        }else{
            $this->ajaxOutput(-1, "type:".$type."参数错误", array('count'=>0, 'list'=>array()));
        }

        if ($subtype && $subtype != -1) {
            $condition = $condition." and subtype='".$subtype."'";
        }else{
            $this->ajaxOutput(-1, "subtype:".$subtype."参数错误", array('count'=>0, 'list'=>array()));
        }

        $list = M('Tag')->where($condition)->order("abbrev asc")->select();
        $num = count($list);
        $html = '<ul class="plate-list">';
        if ($list) {
            for ($i=0; $i < $num; $i++) {
                if ($tmp && substr($tmp['abbrev'],0,1) === substr($list[$i]['abbrev'],0,1) ) {
                    $html = $html.'<li><label>&#12288;</label><a title="'.$list[$i]['name'].'" data-click="selectBolck" data-id="'.$list[$i]['id'].'" href="#"><span class="plate-name">'.$list[$i]['name'].'</span><span class="enter-arrow">&gt;&gt;</span></a></li>';
                }else{
                    $tmp = $list[$i]; 
                    $html = $html."<li><label>".substr($tmp['abbrev'],0,1).'</label><a title="'.$list[$i]['name'].'" data-click="selectBolck" data-id="'.$tmp['id'].'" href="#"><span class="plate-name">'.$tmp['name'].'</span><span class="enter-arrow">&gt;&gt;</span></a></li>';
                }
                
            }
        }
        $html = $html."</ul>";

        $fname = $this->getFileName($type, $subtype);
        if (!$fname) {
            $this->ajaxOutput(-3, "生成文件名错误", array('count'=>0, 'list'=>array()));
        }

        $res = $this->write($html, $fname);
        if ($res == 0) {
            $this->ajaxOutput(0, "suc", array('count'=>0, 'list'=>array()));
        }else if($res == -1){
            $this->ajaxOutput(-1, "写入文件失败", array('count'=>0, 'list'=>array()));
        }else if($res == -2){
            $this->ajaxOutput(-2, "打开文件失败", array('count'=>0, 'list'=>array()));
        }
    }

    public function getFileName ($type, $subtype) {
        if ($type == 1 && $subtype == 1) {
            return "blocklist_cn_hy.html";
        }
        if ($type == 1 && $subtype == 2) {
            return "blocklist_hk_hy.html";
        }
        if ($type == 1 && $subtype == 3) {
            return "blocklist_us_hy.html";
        }
        if ($type == 2 && $subtype == 1) {
            return "blocklist_cn_gn.html";
        }
        if ($type == 2 && $subtype == 2) {
            return "blocklist_hk_gn.html";
        }
        if ($type == 2 && $subtype == 3) {
            return "blocklist_us_gn.html";
        }
        return "";
    }


    public function write($html, $fname){
        $filename = '/data/camus/html/'.$fname;
        $fp = fopen($filename, "w");//文件被清空后再写入
        if($fp){ 
            $flag=fwrite($fp,$html);
            if(!$flag)
            {
                //echo "写入文件失败<br>";
                return -1;
            }

        }else{ 
           //echo "打开文件失败"; 
            return -2;
        } 
        fclose($fp);
        return 0;
    }
}
?>


