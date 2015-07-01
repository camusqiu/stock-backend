<?php
namespace Home\Controller;
//use Think\Model;
use Think\Controller;

/**
 * 通用控制器基类。
 */
class CommonController extends Controller {
		/**
     * 返回标准json格式数据
     * @param int $code 错误代码
     * @param string $msg 错误信息
     * @param array|object $data 返回数据
     * @return void 返回完，终止程序
     */
    public function ajaxOutput($code=0, $msg='', $data=null){
        if(is_null($data)){
            $data = (object)$data;
        }elseif(is_object($data)){
            //对象直接返回
        }elseif(is_array($data)){
            if(array_keys($data) === range(0, count($data)-1)){ //关联数组直接返回
                $data = array('list'=>$data);
            }
        }elseif(is_string($data) || is_numeric($data)){
            $data = array('data'=>$data);
        }else{
            $data = (object)$data;
        }
    	$data = array(
    		'code' => $code,
    		'msg' => $msg,
    		'data' => $data
    	);
    	return $this->ajaxReturn($data);
    }
	
	
    /**
     * 查询。
     */
    public function select(){
        $Model = M($this->getControllerName());
        
        // get input
        $data = I('param.');
        
        // 排序
        if ($data['order'] != null) {
            $Model->order($data['order']);
        }
        
        // 分页
        if ($data['page'] != null && $data['count'] != null) {
            $Model->page($data['page'],$data['count']);
        }
        
        // 过滤字段
        if (APP_DEBUG === true) {
            $this->checkFields($data);
        }
        
        // select
        $list = $Model->where($data)->select();
        
        // $this->response
        if ($list !== false) {
            // empty
            if ($list == null) {
                $list = array();
            }
            $this->ajaxOutput(0, '', $list);
        }else if ($list == null) {
            $this->ajaxOutput(-2);
        }
    }

    /**
     * 新增。
     */
    public function insert(){
        $Model = D($this->getControllerName());
        
        // check input
        $data = I('param.');
        if (!$Model->create($data, Model::MODEL_INSERT)){
            $this->ajaxOutput(-1, $Model->getError());
            return;
        }
        
        // insert
        $result = $Model->add();
        
        // $this->response
        if($result !== false){
            $this->ajaxOutput(0);
        }else{
            $this->ajaxOutput(-2);
        }
    }
    
    /**
     * 更新。
     */
    public function update() {
        $Model = D($this->getControllerName());
        
        // check input
        $data = I('param.');
        if (!$Model->create($data, Model::MODEL_UPDATE)){
            $this->ajaxOutput(-1, $Model->getError());
            return;
        }
        
        // update
        $result = $Model->save();
        
        // $this->response
        if($result !== false && $result !== 0){
            $this->ajaxOutput(0);
        }else{
            $this->ajaxOutput(-2);
        }
    }
    
    /**
     * 删除。
     */
    public function delete() {
        $Model = M($this->getControllerName());
        
        // check input
        $id = I('id');
        if ($id == null) {
            $this->ajaxOutput(-1);
            return;
        }
        
        // delete
        $result = $Model->delete($id);
        
        // reponse
        if($result !== false && $result !== 0){
            $this->ajaxOutput(0);
        }else{
            $this->ajaxOutput(-2);
        }
    }
    
    /**
     * 登陆检测
     */
    public function isLogin() {
        $uname = session('uname');
        $user_id = session('user_id');
        if($user_id){
            return $user_id;
        }else{
            return false;
        }
    }

    /**
     * 获取Controller的名字。
     * @return Controller的名字。
     */
    protected function getControllerName() {
        return $Think.CONTROLLER_NAME;
    }
    
    /**
     * 过滤：数据库表中不存在的字段。
     * @param data 字段数组。
     * @param name 表名。
     */
    protected function checkFields(&$data, $name = null) {
        if (empty($name)) {
            $name = $this->getControllerName();
        }
        
        $fields = M($name)->getDbFields();
        foreach ($data as $key=>$val){
            if(!in_array($key,$fields)) {
                unset($data[$key]);
            }
        }
    }
    
    /**
     * ajax返回。
     * @param code 返回码。
     * @param msg 信息。
     * @param data 数据。
     * @return 数据包。
     */		
    // protected function ajaxOutput($code = 0, $msg = '',$data = array()) {
        // $data = array(
    		// 'code' => $code,
    		// 'msg' => $msg,
    		// 'data' => $data
    	// );
    	// return $this->ajaxReturn($data);
    // }
}
