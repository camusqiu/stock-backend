<?php
namespace Home\Controller;
use Home\Controller\CommonController;

/**
 * “帖子标签”控制器类。
 */ 
class HkStockFactorController extends CommonController {
    
    private $curpage = 0;
    private $pagenum = 0;

	public function get(){
		$Model = M('HkStockFactor');
		$ucode = I('ucode', '');

		$code = getBarCode($ucode);

        $list = $Model->where("1=1 and code='".$code."'")->order('ex_date desc')->select();
		$this->ajaxOutput(0, '', array(count=>count($list),'list'=>$list));
		
	}

	public function del(){
		$Model = M('HkStockFactor');
		$id = I('id', '');

        $LEXTemp = $Model->where("1=1 and id='".$id."'")->select();
        if (!$LEXTemp) {
            $this->ajaxOutput(-1, '没有找到该条记录', array(count=>0,'list'=>array()));
        }

        $data['id'] = $id;
        $data['code'] = $LEXTemp[0]['code'];
        $data['ex_date'] = $LEXTemp[0]['ex_date'];
        $data['current_factor'] = 1;

        $LEX = $Model->where("1=1 and code='".$data['code']."' and id!='".$data['id']."'")->order('ex_date asc')->select();
        if (!$LEX) {
            $list = $Model->where("1=1 and id='".$id."'")->delete();
            if ($list && $list != null) {
                $this->ajaxOutput(0, '删除复权记录成功', array(count=>count($list),'list'=>$list));
            }     
        }

        $num = count($LEX);
        $tag = 0;
        for ($i=0; $i < $num; $i++) {
            if ($LEX[$i]['ex_date']<=$data['ex_date']) {
                if ($i == 0) {
                    $dataFinal['final_factor'] = $LEX[0]['current_factor'];
                }else if ($i == $num-1 && $LEX[$i]['ex_date']<$data['ex_date']){
                    $dataFinal['final_factor'] = $LEX[$i]['current_factor']*$dataFinal['final_factor'];
                    $data['final_factor'] = $dataFinal['final_factor']*$data['current_factor'];
                }else{
                    $dataFinal['final_factor'] = $LEX[$i]['current_factor']*$dataFinal['final_factor'];
                }
            }else if ($i == 0 && $LEX[$i]['ex_date']>$data['ex_date']){
                $data['final_factor'] = $data['current_factor'];
                $dataFinal['final_factor'] = $data['final_factor'];
                $tag = 1;
            }

            if ($LEX[$i]['ex_date']>$data['ex_date']) {
                if ($tag == 0) {
                    $data['final_factor'] = $dataFinal['final_factor']*$data['current_factor'];
                    $dataFinal['final_factor'] = $LEX[$i]['current_factor']*$data['final_factor'];
                }else{
                    $dataFinal['final_factor'] = $LEX[$i]['current_factor']*$dataFinal['final_factor'];
                }
                $tag = 1;
            }
            
            $LSave = $Model->where("1=1 and id='".$LEX[$i]['id']."'")->save($dataFinal);
            if($LSave === null){
                $this->ajaxOutput(-1, '删除后修改其他复权数据失败', array(count=>count($LSave),'list'=>$LSave));
            } 
        }

        $list = $Model->where("1=1 and id='".$id."'")->delete();
        if ($list && $list != null) {
        	$this->ajaxOutput(0, '删除复权记录成功', array(count=>count($list),'list'=>$list));
        }
		$this->ajaxOutput(-1, '删除复权记录失败', array(count=>0,'list'=>array()));
	}

	public function save(){
        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $LData['table_name'] = "HkStockFactor";
        $LData['type'] = 1;
        $LData['admin_id'] = $res_isLogin;

		$Model = M('HkStockFactor');
		$ModelStock = M('StockBar');
		$data['id'] = I('id', '');
		$ucode = I('code', '');
		$data['code'] = getBarCode($ucode); 
		$data['ex_date'] = I('ex_date', '');
		$data['type'] = I('type', '');
		$data['desc'] = I('desc', '');
		$data['current_factor'] = I('current_factor', '');

		if (!$data['id'] || !$data['ex_date'] || !$data['desc'] || !$data['current_factor']) {
			$this->ajaxOutput(-1, '提交的数据不完整', array(count=>0,'list'=>array()));
		}

		//$a = $ModelStock->where("code='".$data['code']."'")->select();
		//$data['type'] = $a[0]['type'];

        $list = $Model->where("1=1 and id='".$data['id']."'")->select();
        if ($list && $list != null) {
        	if ($list[0]['ex_date'] != $data['ex_date']	) {
        		$LEX = $Model->where("1=1 and code='".$data['code']."' and id!='".$data['id']."'")->order('ex_date asc')->select();
                if (!$LEX) {
                    $data['final_factor'] = $data['current_factor'];
                    $LSave = $Model->save($data);
                    if($LSave){
                        $LData['msg'] = "Post:[复权数据: code ".$data['code']." 复权数据修改成功]";
                        aLog($LData);
                        $this->ajaxOutput(0, '修改成功', array(count=>count($LSave),'list'=>$LSave));
                    }
                }

                $num = count($LEX);
                $tag = 0;
                for ($i=0; $i < $num; $i++) {
                    if ($LEX[$i]['ex_date']<=$data['ex_date']) {
                        if ($i == 0) {
                            $dataFinal['final_factor'] = $LEX[0]['current_factor'];
                        }else if ($i == $num-1 && $LEX[$i]['ex_date']<$data['ex_date']){
                            $dataFinal['final_factor'] = $LEX[$i]['current_factor']*$dataFinal['final_factor'];
                            $data['final_factor'] = $dataFinal['final_factor']*$data['current_factor'];
                        }else{
                            $dataFinal['final_factor'] = $LEX[$i]['current_factor']*$dataFinal['final_factor'];
                        }
                    }else if ($i == 0 && $LEX[$i]['ex_date']>$data['ex_date']){
                        $data['final_factor'] = $data['current_factor'];
                        $dataFinal['final_factor'] = $data['final_factor'];
                        $tag = 1;
                    }

                    if ($LEX[$i]['ex_date']>$data['ex_date']) {
                        if ($tag == 0) {
                            $data['final_factor'] = $dataFinal['final_factor']*$data['current_factor'];
                            $dataFinal['final_factor'] = $LEX[$i]['current_factor']*$data['final_factor'];
                        }else{
                            $dataFinal['final_factor'] = $LEX[$i]['current_factor']*$dataFinal['final_factor'];
                        }
                        $tag = 1;
                    }
                    $LSave = $Model->where("1=1 and id='".$LEX[$i]['id']."'")->save($dataFinal);
                    if(!$LSave){
                        $LData['msg'] = "Post:[复权数据: id ".$LEX[$i]['id']." 复权数据(生效时间更改)修改失败]";
                        aLog($LData);
                        $this->ajaxOutput(-1, '修改失败1', array(count=>count($LSave),'list'=>$LSave));
                    } 
                    
                }
                $LSave = $Model->where("1=1 and id='".$data['id']."'")->save($data);
                
                if(!$LSave){
                    $LData['msg'] = "Post:[复权数据: id ".$data['id']." 复权数据修改失败]";
                    aLog($LData);
                    $this->ajaxOutput(-1, '修改失败2', array(count=>count($LSave),'list'=>$LSave));
                }else{
                    $LData['msg'] = "Post:[复权数据: id ".$data['id']." 复权数据修改成功]";
                    aLog($LData);
                    $this->ajaxOutput(0, '修改成功', array(count=>count($LSave),'list'=>$LSave));
                }        
                
        		
        	}else if ($list[0]['current_factor'] != $data['current_factor']) {
        			$LEXTemp = $Model->where("1=1 and code='".$data['code']."' and ex_date<'".$data['ex_date']."'")->order('ex_date desc')->limit(1)->select();
                    if (!$LEXTemp) {
                        $dataFinal['final_factor'] = $data['current_factor'];
                        $data['final_factor'] = $data['current_factor'];
                    }else{
                        $dataFinal['final_factor'] = $LEXTemp[0]['final_factor'];
                        $data['final_factor'] = $data['current_factor']*$dataFinal['final_factor'];
                        $dataFinal['final_factor'] = $data['final_factor'];
                    }
                    $LSaveTemp = $Model->where("1=1 and id='".$data['id']."'")->save($data);     

                    if (!$LSaveTemp) {
                        $LData['msg'] = "Post:[复权数据: id ".$data['id']." 复权数据(复权因子)修改失败]";
                        aLog($LData);
                        $this->ajaxOutput(-1, '修改失败', array(count=>0,'list'=>array()));
                    }

        			$LEX = $Model->where("1=1 and code='".$data['code']."' and ex_date>='".$data['ex_date']."' and id!='".$data['id']."'")->order('ex_date asc')->select();
        			$num = count($LEX);
                    if ($num > 0) {
                        for ($i=0; $i < $num; $i++) {
                            $dataFinal['final_factor'] = $LEX[$i]['current_factor']*$dataFinal['final_factor'];
                            $LSave = $Model->where("1=1 and id='".$LEX[$i]['id']."'")->save($dataFinal);                  
                        }
                    }
        	}else{
                $LSave = $Model->where("1=1 and id='".$data['id']."'")->save($data);
                
                if(!$LSave){
                    $LData['msg'] = "Post:[复权数据: id ".$data['id']." 复权数据(分类或者描述)修改失败]";
                    aLog($LData);
                    $this->ajaxOutput(-1, '修改失败', array(count=>count($LSave),'list'=>$LSave));
                }else{
                    $LData['msg'] = "Post:[复权数据: id ".$data['id']." 复权数据(分类或者描述)修改成功]";
                    aLog($LData);
                    $this->ajaxOutput(0, '修改成功', array(count=>count($LSave),'list'=>$LSave));
                }        
            }
        }else{
            $this->ajaxOutput(-1, '服务器错误', array(count=>0,'list'=>array()));
        }
        $LData['msg'] = "Post:[复权数据: id ".$data['id']." 复权数据修改失败]";
        aLog($LData);
		$this->ajaxOutput(0, '修改成功', array(count=>0,'list'=>array()));
	}

	public function add(){
		$Model = M('HkStockFactor');
        $ModelStock = M('StockBar');
		$ucode = I('code', '');
		$data['code'] = getBarCode($ucode); 
		$data['ex_date'] = I('ex_date', '');
		$data['type'] = I('type', '');
		$data['desc'] = I('desc', '');
		$data['current_factor'] = I('current_factor', '');

		if (!$data['code'] || !$data['ex_date'] || !$data['desc'] || !$data['current_factor']) {
			$this->ajaxOutput(-1, '提交的数据不完整', array(count=>0,'list'=>array()));
		}


		$a = $ModelStock->where("code='".$data['code']."'")->select();
		$data['type'] = $a[0]['type'];
		

        $list = $Model->where("1=1 and code='".$data['code']."'")->order('ex_date asc')->select();
        if ($list || $list == null) {
        	if ($list == null) {
        		$data['final_factor'] = $data['current_factor'];
        		$LAdd = $Model->add($data);
        		$this->ajaxOutput(0, '添加成功！', array(count=>count($LAdd),'list'=>$LAdd));
        	}

        	$dataFinal['final_factor'] = $list[0]['final_factor'];
        	$num = count($list);
        	if ($data['ex_date']>=$list[$num-1]['ex_date']) {
    			$data['final_factor'] = $data['current_factor']*$list[$num-1]['final_factor'];
      		 	$LAdd = $Model->add($data);
    		 	$this->ajaxOutput(0, '添加成功！', array(count=>count($LAdd),'list'=>$LAdd));
    		}

            $tag = 0;
        	for ($i=0; $i < $num; $i++) {
        		if ($data['ex_date']<$list[$i]['ex_date'] && $tag == 0) {
                    if($i == 0){
                        $data['final_factor'] = $data['current_factor'];
                    }else{
                        $data['final_factor'] = $data['current_factor']*$list[$i-1]['final_factor'];
                    }
        	        $dataFinal['final_factor'] = $data['final_factor'];
                    $tag = 1;
        		 	$LAdd = $Model->add($data);
        		}else if($tag == 0){
        			continue;
        		} 
    			$dataFinal['final_factor'] = $list[$i]['current_factor']*$dataFinal['final_factor'];
    			$LSave = $Model->where("1=1 and id='".$list[$i]['id']."'")->save($dataFinal);         			
    		}

        	$this->ajaxOutput(0, '添加成功!', array(count=>count($list),'list'=>$list));
        }else{
        	
        	$this->ajaxOutput(-1, '服务器错误', array(count=>0,'list'=>array()));
        }
		
	}
}
