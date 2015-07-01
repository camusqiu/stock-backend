<?php
namespace Home\Controller;
use Think\Controller;
class GetDateController extends Controller {
	
	/**
	 * 获取增删改条件
	 **/
	public function getConditon($param, $need, &$condition, &$needdate){
		if($param)
		{
			$Arry = explode("|", $param, 100);
			$Len = count($Arry);
			for($i = 0; $i < $Len; $i++)
			{
				$conditionTemp = sprintf(" and %s", $Arry[$i]);
				$condition = $condition.$conditionTemp;
			}
			echo $condition;
		}
		
		if($need)
		{
			$Arry = explode("|", $need, 100);
			$Len = count($Arry);
			for($i = 0; $i < $Len; $i++)
			{
				if($i+1 < $Len)
				{
					$needdateTemp = sprintf("%s,", $Arry[$i]);
				}else{
					$needdateTemp = sprintf("%s", $Arry[$i]);
				}
				$needdate = $needdate.$needdateTemp;
			}
		}else{
			$needdate = '*';
		}
		echo $needdate;
	}
	
	/**
	 * 管理端查找股票
	**/
    public function get(){
		$condition = "1=1";
		$needdate = "";
		$param = $_GET['param'];
		$need = $_GET['need'];
		
		$this->getConditon($param, $need, $condition, $needdate);
				
    	//实例化一个模型
    	$Model = M('Stock');

    	//读取10条数据（更多带条件的读取方法参见文档》模型》CURD操作说明）
    	//$list = $Model->where($condition)->getField($needdate);
	$list = $Model->where($condition)->field($needdate)->select();
		
    	//将数据赋值给页面（MVC的view层），模板中就可以使用这些数据
    	// $this->assign('list', $list);
    	// $this->display();

    	//如果不需要渲染HTML页面，只需要返回json数据用下面的方法
    	$data = array(
    		'count' => count($list),
    		'data' => $list
    	);
		$this->ajaxReturn($data);
    }
}
