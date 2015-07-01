<?php
namespace Home\Controller;
use Think\Controller;
import('Org.Util.Date');


class SynStockChangeController extends CommonController {
    private $filename = "";

    //股吧换代码后。只有资讯cope到新代码
    //////////原来的股吧是否能搜到。是否要展示。历史数据怎么整, 股吧关联等问题暂未讨论
    public function StockCodeChangeForDataCope () {

        $oldcode = I('param.oldcode',-1);
        $newcode = I('param.newcode',-1);

        if (!$oldcode && $oldcode == "-1") {
            $this->ajaxOutput(-1, 'oldcode 参数错误', array('count'=>0, 'list'=>array()));
        }
        if (!$newcode && $newcode == "-1") {
            $this->ajaxOutput(-1, 'newcode 参数错误', array('count'=>0, 'list'=>array()));
        }

        $SList = M('StockBar')->master(true)->where("code='".$newcode."'")->find();
        if (!$SList) {
            $this->ajaxOutput(-1, 'newcode 不存在', array('count'=>0, 'list'=>array()));
        }
        ////$dataList[] = array('name'=>'thinkphp','email'=>'thinkphp@gamil.com');
        $OList = M('PostBar')->where("bar_code='".$oldcode."'")->select();
        if ($OList && $OList != null) {
            $num = count($OList);
            for ($i=0; $i < $num; $i++) { 
                // $NData[$i]['post_id'] = $OList[$i]['post_id'];
                // $NData[$i]['bar_id'] = $SList['id'];
                // $NData[$i]['bar_code'] = $newcode;
                // $NData[$i]['bar_type'] = $SList['type'];
                $dataList[] = array('post_id'=>$OList[$i]['post_id'], 'bar_id'=>$SList['id'], 'bar_code'=>$newcode, 'bar_type'=>$SList['type']);
            }
            $c = M('PostBar')->addAll($dataList);
            var_dump($dataList);
            if ($c && $c != null) {
                $this->ajaxOutput(0, $oldcode.' 历史资讯关联复制到 '.$newcode.' 成功', array('count'=>0, 'list'=>array()));  
            }else{
                $this->ajaxOutput(-1, $oldcode.' 历史资讯关联复制到 '.$newcode.' 失败', array('count'=>0, 'list'=>array()));  
            }
        }else if ($OList == null){
            $this->ajaxOutput(0, $oldcode.'没有关联，不用复制', array('count'=>0, 'list'=>array()));
        }else {
            $this->ajaxOutput(-1, '服务器错误', array('count'=>0, 'list'=>array()));
        }
  }
    
}
?>
