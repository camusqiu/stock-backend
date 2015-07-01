<?php
namespace Home\Controller;
use Think\Controller;


class SearchStockController extends Controller {
	private $condition = "1=1";
	private $iType = "";
	private $data = array(
				'code' => 0,
				'msg' => "",
	    		'count' => 0,
	    		'data' => null
	    	);
	/**
	 * 获取查条件
	 * 规则：1)股票代码(code)左右模糊匹配；
	 *		 2)拼音/首字母/英文名(spell/initial/englishname)左匹配
	 *		 3)汉字(name)  //是否需要支持左右匹配模糊查找	
	 **/
	public function get_search_conditon($param, &$condition){
		if($param)
		{
			if(is_numeric($param))
			{
				//select * from table1 where charindex('c',username)>0   // username字段中含有'c'
				$conditionTemp = sprintf(" and code like %s%s%s","'%", $param, "%'");
				$condition = $condition.$conditionTemp;
			}else if (preg_match('/[A-Za-z]+/',$param)){
				$conditionTemp = sprintf(" and ((spell like %s%s%s) or (name like %s%s%s))", "'", $param, "%'", "'", $param,"%'");
				$condition = $condition.$conditionTemp;
			}else if (preg_match("/[\x7f-\xff]/", $param)){
				$this->data["code"] = -1;
				$this->data["msg"] = "db has not input chinese!";
				return -1;
			}
		}else{
			$this->data["code"] = -1;
			$this->data["msg"] = "param wrong!";
			return -1;
		}

		return 0;
		// if($param)
		// {
		// 	$conditionTemp = sprintf(" and ((code like %s%s%s) or (spell like %s%s%s) or (name like %s%s%s))","'%", $param, "%'", "'%", $param, "%'", "'", $param,"%'");
		// 	$condition = $condition.$conditionTemp;
		// }
	}
	
	/**
	 * 获取增删改条件
	 **/
	public function getConditon($code, $spell, $name, $state, $type, &$condition, &$iType){
		if($code)
		{
			$conditionTemp = sprintf(" and code=%s", $code);
			$condition = $condition.$conditionTemp;
		}
		if(iType != "update")
		{
			if($type)
			{
				$conditionTemp = sprintf(" and type=%s", $type);
				$condition = $condition.$conditionTemp;
			}
			if($name)
			{
				$conditionTemp = sprintf(" and name='%s'", $name);
				$condition = $condition.$conditionTemp;
			}
			if($state)
			{
				$conditionTemp = sprintf(" and state=%s", $state);
				$condition = $condition.$conditionTemp;
			}
			if($spell)
			{
				$conditionTemp = sprintf(" and spell='%s'", $spell);
				$condition = $condition.$conditionTemp;
			}
		}
	}
	
	/**
	 * 查找股票
	**/
    public function getStockInfo(){
		$this->iType = "search";
		$param = $_GET['param'];
	
		$iError = $this->get_search_conditon($param, $this->condition);
		if($iError!=0)
		{
			$this->ajaxReturn($data);
			return -1;
		}else{
			//实例化一个模型
	    	$Model = M('Stock');

	    	//读取10条数据（更多带条件的读取方法参见文档》模型》CURD操作说明）
	    	$list = $Model->where($this->condition)->limit(10)->getField('id,type,code,name,state,spell');

	    	//将数据赋值给页面（MVC的view层），模板中就可以使用这些数据
	    	// $this->assign('list', $list);
	    	// $this->display();

	    	//如果不需要渲染HTML页面，只需要返回json数据用下面的方法
	    	$this->data = array(
				'code' => 0,
				'msg' => "find suc",
	    		'count' => count($list),
	    		'data' => $list
	    	);
			$this->ajaxReturn($this->data, 'JSONP');
		}	
    }
	
	
	/**
	 * 管理端查找股票
	**/
    public function get(){
		$this->iType = "get";
		$code = $_GET['code'];
		$type = $_GET['type'];
		$name = $_GET['name'];
		$state = $_GET['state'];
		$spell = $_GET['spell'];
		
		$this->getConditon($code, $spell, $name, $state, $type, $this->condition, $this->iType);
				
    	//实例化一个模型
    	$Model = M('Stock');

    	//读取10条数据（更多带条件的读取方法参见文档》模型》CURD操作说明）
    	$list = $Model->where($this->condition)->getField('id,code,spell');

    	//将数据赋值给页面（MVC的view层），模板中就可以使用这些数据
    	// $this->assign('list', $list);
    	// $this->display();

    	//如果不需要渲染HTML页面，只需要返回json数据用下面的方法
    	$this->data = array(
    		'count' => count($list),
    		'data' => $list
    	);
		$this->ajaxReturn($this->data, 'JSONP');
    }


    /**
     * 新增股票操作
    **/
    public function addNewStock(){
		$this->iType  = "add";
		$addData["type"] = $_GET['type'];
		$addData["code"] = $_GET['code'];
		$addData["name"] = $_GET['name'];
		$addData["state"] = $_GET['state'];
		$addData["spell"] = $_GET['spell'];
	
    	//实例化一个模型
    	$Model = M('Stock');

    	//验证前端提交上来的数据，验证不通过会返回false，验证方法在Model层做
    	//$data = $Model->create();
		//$data["id"] = '601299.SH'; 
		if(!$addData){
			$this->ajaxReturn('data is null');
		}
		
		//执行添加操作，此时才会把提交的数据写入到数据库
		print_r($addData);
		$re = $Model->add($addData);
		

    	//根据执行结果返回给前端
    	if($re){
    		$this->ajaxReturn('suc');
    	}else{
			echo $Model->getError();
    		$this->ajaxReturn('fail');
    	}
    }
	
	
	/**
	 * 更新数据
	**/
    public function updateStock(){
		//code设置不变
		$this->iType  = "update";
		$code  = $_GET['code'];
		
		if($_GET['type'])
		{
			$item["type"]  = $_GET['type'];
		}
		if($_GET['name'])
		{
			$item["name"]  = $_GET['name'];
		}
		if($_GET['state'])
		{
			$item["state"]  = $_GET['state'];
		}
		if($_GET['spell'])
		{
			$item["spell"]  = $_GET['spell'];
		}

    	//实例化一个模型
    	$Model = M('Stock');
		
		$this->getConditon($code, $spell, $name, $state, $type, $this->condition, $this->iType);
		
    	//读取10条数据（更多带条件的读取方法参见文档》模型》CURD操作说明）
    	$list = $Model->where($this->condition)->save($item);

    	//将数据赋值给页面（MVC的view层），模板中就可以使用这些数据
    	// $this->assign('list', $list);
    	// $this->display();

    	//如果不需要渲染HTML页面，只需要返回json数据用下面的方法
    	$this->data = array(
    		'status' => count($list),
    		'data' => $list
    	);
    	$this->ajaxReturn($this->data, 'JSONP');
    }
	
	
	/**
	 * 删除数据
	**/
    public function deleteStock(){
		$this->iType  = "delete";
		$code = $_GET['code'];
		$type = $_GET['type'];
		$name = $_GET['name'];
		$state = $_GET['state'];
		$spell = $_GET['spell'];

    	//实例化一个模型
    	$Model = M('Stock');
		
		$this->getConditon($code, $spell, $name, $state, $type, $this->condition, $this->iType);
		
    	//读取10条数据（更多带条件的读取方法参见文档》模型》CURD操作说明）
    	$list = $Model->where($this->condition)->delete();

    	//将数据赋值给页面（MVC的view层），模板中就可以使用这些数据
    	// $this->assign('list', $list);
    	// $this->display();

    	//如果不需要渲染HTML页面，只需要返回json数据用下面的方法
    	$this->data = array(
    		'status' => count($list),
    		'data' => $list
    	);
    	$this->ajaxReturn($this->data, 'JSONP');
    }
}
