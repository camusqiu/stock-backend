<?php
namespace Home\Controller;
use Home\Controller\CommonController;

/**
 * “帖子标签”控制器类。
 */ 
class IndexController extends CommonController {
    
	public function getdata() {
        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
           $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }
        
        //当天时间
        $dateTime = date('ymd');
        $date = date('Y-m-d');
        $list['time'] = $date;

        //用户发贴
        $ModelPost = M('Post'); 
        $postnum = $ModelPost->where(" type=1 and ctime>='".$dateTime."'")->count();    
        $list['post'] = $postnum;

        //回复数
        $ModelComment = M('Comment'); 
        $commentnum = $ModelComment->where("modify_time>='".$dateTime."'")->count();    
        $list['respose'] = $commentnum;

        //用户登录次数
        $ModelLogin = M('LoginStats'); 
        $loginnum = $ModelLogin->where("ctime>='".$dateTime."'")->count();    
        $list['login'] = $loginnum;


        //用户登录个数
        $loginnumper = $ModelLogin->Distinct(true)->field('user_id')->where("ctime>='".$dateTime."'")->select();
        $list['loginPerson'] = count($loginnumper);

        //用户举报次数
        $ModelReport = M('Report'); 
        $reportnum = $ModelReport->where("ctime>='".$dateTime."'")->count();    
        $list['report'] = $reportnum;

        //用户关注次数
        $ModelFollow = M('Follow'); 
        $follownum = $ModelFollow->where("ctime>='".$dateTime."'")->count();    
        $list['follow'] = $follownum;


        //个股新闻
        $newsnum = $ModelPost->where("type=3 and ctime>='".$dateTime."'")->count();    
        $list['news'] = $newsnum;

        //个股公告
        $announcementnum = $ModelPost->where("type=2 and ctime>='".$dateTime."'")->count();    
        $list['announcement'] = $announcementnum;

        //个股资料变动
        $ModelStockBar = M('StockBar'); 
        $stockbarnum = $ModelStockBar->where("modify_time>='".$dateTime."'")->count();    
        $list['stockbarchange'] = $stockbarnum;

        //当前在线用户
        // $ModelStockBar = M('StockBar'); 
        // $stockbarnum = $ModelStockBar->where("modify_time>='".$dateTime."'")->count();    
        $list['usernowonline'] = 0;

        //今日最高在线用户
        // $ModelStockBar = M('StockBar'); 
        // $stockbarnum = $ModelStockBar->where("modify_time>='".$dateTime."'")->count();    
        $list['usermostonline'] = 0;

        //新增用户
        $ModelUser = M('User'); 
        $usernum = $ModelUser->where("ctime>='".$dateTime."'")->count();    
        $list['useradd'] = $usernum;


        $this->ajaxOutput(0, "suc", array('list'=>$list));    
    }


    public function getFiveDayData() {
        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
           $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }
        
        //当天时间
        $dateTime = date('ymd');
        $dateTime2 = date('ymd',strtotime("-1 day"));
        $dateTime3 = date('ymd',strtotime("-2 day"));
        $dateTime4 = date('ymd',strtotime("-3 day"));
        $dateTime5 = date('ymd',strtotime("-4 day"));

        $date = date('Y-m-d');
        $date2 = date('Y-m-d',strtotime("-1 day"));
        $date3 = date('Y-m-d',strtotime("-2 day"));
        $date4 = date('Y-m-d',strtotime("-3 day"));
        $date5 = date('Y-m-d',strtotime("-4 day"));
        $list[0]['time'] = $date;
        $list[1]['time'] = $date2;
        $list[2]['time'] = $date3;
        $list[3]['time'] = $date4;
        $list[4]['time'] = $date5;

        //用户发贴
        $ModelPost = M('Post'); 
        $postnum = $ModelPost->where(" type=1 and ctime>='".$dateTime."'")->count();    
        $list[0]['post'] = $postnum;
        $postnum = $ModelPost->where(" type=1 and ctime>='".$dateTime2."' and ctime<'".$dateTime."'")->count();    
        $list[1]['post'] = $postnum;
        $postnum = $ModelPost->where(" type=1 and ctime>='".$dateTime3."' and ctime<'".$dateTime2."'")->count();    
        $list[2]['post'] = $postnum;
        $postnum = $ModelPost->where(" type=1 and ctime>='".$dateTime4."' and ctime<'".$dateTime3."'")->count();    
        $list[3]['post'] = $postnum;
        $postnum = $ModelPost->where(" type=1 and ctime>='".$dateTime5."' and ctime<'".$dateTime4."'")->count();    
        $list[4]['post'] = $postnum;

        //回复数
        $ModelComment = M('Comment'); 
        $commentnum = $ModelComment->where("modify_time>='".$dateTime."'")->count();    
        $list[0]['respose'] = $commentnum;
        $commentnum = $ModelComment->where("modify_time>='".$dateTime2."' and ctime<'".$dateTime."'")->count();     
        $list[1]['respose'] = $commentnum;
        $commentnum = $ModelComment->where("modify_time>='".$dateTime3."' and ctime<'".$dateTime2."'")->count();     
        $list[2]['respose'] = $commentnum;
        $commentnum = $ModelComment->where("modify_time>='".$dateTime4."' and ctime<'".$dateTime3."'")->count();     
        $list[3]['respose'] = $commentnum;
        $commentnum = $ModelComment->where("modify_time>='".$dateTime5."' and ctime<'".$dateTime4."'")->count();     
        $list[4]['respose'] = $commentnum;


        //用户登录次数
        $ModelLogin = M('LoginStats'); 
        $loginnum = $ModelLogin->where("ctime>='".$dateTime."'")->count();    
        $list[0]['login'] = $loginnum;
        $loginnum = $ModelLogin->where("ctime>='".$dateTime2."' and ctime<'".$dateTime."'")->count();  
        $list[1]['login'] = $loginnum;
        $loginnum = $ModelLogin->where("ctime>='".$dateTime3."' and ctime<'".$dateTime2."'")->count();  
        $list[2]['login'] = $loginnum;
        $loginnum = $ModelLogin->where("ctime>='".$dateTime4."' and ctime<'".$dateTime3."'")->count();  
        $list[3]['login'] = $loginnum;
        $loginnum = $ModelLogin->where("ctime>='".$dateTime5."' and ctime<'".$dateTime4."'")->count();  
        $list[4]['login'] = $loginnum;


        //用户登录个数
        $loginnumper = $ModelLogin->Distinct(true)->field('user_id')->where("ctime>='".$dateTime."'")->select();
        $list[0]['loginPerson'] = count($loginnumper);
        $loginnumper = $ModelLogin->Distinct(true)->field('user_id')->where("ctime>='".$dateTime2."' and ctime<'".$dateTime."'")->select();  
        $list[1]['loginPerson'] = count($loginnumper);
        $loginnumper = $ModelLogin->Distinct(true)->field('user_id')->where("ctime>='".$dateTime3."' and ctime<'".$dateTime2."'")->select();  
        $list[2]['loginPerson'] = count($loginnumper);
        $loginnumper = $ModelLogin->Distinct(true)->field('user_id')->where("ctime>='".$dateTime4."' and ctime<'".$dateTime3."'")->select();  
        $list[3]['loginPerson'] = count($loginnumper);
        $loginnumper = $ModelLogin->Distinct(true)->field('user_id')->where("ctime>='".$dateTime5."' and ctime<'".$dateTime4."'")->select();  
        $list[4]['loginPerson'] = count($loginnumper);

        //用户举报次数
        $ModelReport = M('Report'); 
        $reportnum = $ModelReport->where("ctime>='".$dateTime."'")->count();    
        $list[0]['report'] = $reportnum;
        $reportnum = $ModelReport->where("ctime>='".$dateTime2."' and ctime<'".$dateTime."'")->count();    
        $list[1]['report'] = $reportnum;
        $reportnum = $ModelReport->where("ctime>='".$dateTime3."' and ctime<'".$dateTime2."'")->count();    
        $list[2]['report'] = $reportnum;
        $reportnum = $ModelReport->where("ctime>='".$dateTime4."' and ctime<'".$dateTime3."'")->count();    
        $list[3]['report'] = $reportnum;
        $reportnum = $ModelReport->where("ctime>='".$dateTime5."' and ctime<'".$dateTime4."'")->count();    
        $list[4]['report'] = $reportnum;

        //用户关注次数
        $ModelFollow = M('Follow'); 
        $follownum = $ModelFollow->where("ctime>='".$dateTime."'")->count();    
        $list[0]['follow'] = $follownum;
        $follownum = $ModelFollow->where("ctime>='".$dateTime2."' and ctime<'".$dateTime."'")->count(); 
        $list[1]['follow'] = $follownum;
        $follownum = $ModelFollow->where("ctime>='".$dateTime3."' and ctime<'".$dateTime2."'")->count(); 
        $list[2]['follow'] = $follownum;
        $follownum = $ModelFollow->where("ctime>='".$dateTime4."' and ctime<'".$dateTime3."'")->count(); 
        $list[3]['follow'] = $follownum;
        $follownum = $ModelFollow->where("ctime>='".$dateTime5."' and ctime<'".$dateTime4."'")->count(); 
        $list[4]['follow'] = $follownum;



        //个股新闻
        $newsnum = $ModelPost->where("type=3 and ctime>='".$dateTime."'")->count();    
        $list[0]['news'] = $newsnum;
        $newsnum = $ModelPost->where("type=3 and ctime>='".$dateTime2."' and ctime<'".$dateTime."'")->count();
        $list[1]['news'] = $newsnum;
        $newsnum = $ModelPost->where("type=3 and ctime>='".$dateTime3."' and ctime<'".$dateTime2."'")->count();
        $list[2]['news'] = $newsnum;
        $newsnum = $ModelPost->where("type=3 and ctime>='".$dateTime4."' and ctime<'".$dateTime3."'")->count();
        $list[3]['news'] = $newsnum;
        $newsnum = $ModelPost->where("type=3 and ctime>='".$dateTime5."' and ctime<'".$dateTime4."'")->count();
        $list[4]['news'] = $newsnum;

        //个股公告
        $announcementnum = $ModelPost->where("type=2 and ctime>='".$dateTime."'")->count();    
        $list[0]['announcement'] = $announcementnum;
        $announcementnum = $ModelPost->where("type=2 and ctime>='".$dateTime2."' and ctime<'".$dateTime."'")->count();
        $list[1]['announcement'] = $announcementnum;
        $announcementnum = $ModelPost->where("type=2 and ctime>='".$dateTime3."' and ctime<'".$dateTime2."'")->count();
        $list[2]['announcement'] = $announcementnum;
        $announcementnum = $ModelPost->where("type=2 and ctime>='".$dateTime4."' and ctime<'".$dateTime3."'")->count();
        $list[3]['announcement'] = $announcementnum;
        $announcementnum = $ModelPost->where("type=2 and ctime>='".$dateTime5."' and ctime<'".$dateTime4."'")->count();
        $list[4]['announcement'] = $announcementnum;

        //资讯处理条数
        $ModelNew = M('NewsInfo');
        $newsnum = $ModelNew->where("state!=0 and ctime>='".$dateTime."'")->count();    
        $list[0]['newsnum'] = $newsnum;
        $newsnum = $ModelNew->where("state!=0 and ctime>='".$dateTime2."' and ctime<'".$dateTime."'")->count();
        $list[1]['newsnum'] = $newsnum;
        $newsnum = $ModelNew->where("state!=0 and ctime>='".$dateTime3."' and ctime<'".$dateTime2."'")->count();
        $list[2]['newsnum'] = $newsnum;
        $newsnum = $ModelNew->where("state!=0 and ctime>='".$dateTime4."' and ctime<'".$dateTime3."'")->count();
        $list[3]['newsnum'] = $newsnum;
        $newsnum = $ModelNew->where("state!=0 and ctime>='".$dateTime5."' and ctime<'".$dateTime4."'")->count();
        $list[4]['newsnum'] = $newsnum;

        //个股资料变动
        /*
        $ModelStockBar = M('StockBar'); 
        $stockbarnum = $ModelStockBar->where("modify_time>='".$dateTime."'")->count();    
        $list[0]['stockbarchange'] = $stockbarnum;
        $stockbarnum = $ModelStockBar->where("modify_time>='".$dateTime2."' and ctime<'".$dateTime."'")->count();   
        $list[1]['stockbarchange'] = $stockbarnum;
        $stockbarnum = $ModelStockBar->where("modify_time>='".$dateTime3."' and ctime<'".$dateTime2."'")->count();   
        $list[2]['stockbarchange'] = $stockbarnum;
        $stockbarnum = $ModelStockBar->where("modify_time>='".$dateTime4."' and ctime<'".$dateTime3."'")->count();   
        $list[3]['stockbarchange'] = $stockbarnum;
        $stockbarnum = $ModelStockBar->where("modify_time>='".$dateTime5."' and ctime<'".$dateTime4."'")->count();   
        $list[4]['stockbarchange'] = $stockbarnum;
        */

        //当前在线用户
        // $ModelStockBar = M('StockBar'); 
        // $stockbarnum = $ModelStockBar->where("modify_time>='".$dateTime."'")->count();    
        $list[0]['usernowonline'] = 0;
        $list[1]['usernowonline'] = 0;
        $list[2]['usernowonline'] = 0;
        $list[3]['usernowonline'] = 0;
        $list[4]['usernowonline'] = 0;


        //今日最高在线用户
        // $ModelStockBar = M('StockBar'); 
        // $stockbarnum = $ModelStockBar->where("modify_time>='".$dateTime."'")->count();    
        $list[0]['usermostonline'] = 0;
        $list[1]['usermostonline'] = 0;
        $list[2]['usermostonline'] = 0;
        $list[3]['usermostonline'] = 0;
        $list[4]['usermostonline'] = 0;

        //新增用户
        $ModelUser = M('User'); 
        $usernum = $ModelUser->where("ctime>='".$dateTime."'")->count();    
        $list[0]['useradd'] = $usernum;
        $usernum = $ModelUser->where("ctime>='".$dateTime2."' and ctime<'".$dateTime."'")->count();     
        $list[1]['useradd'] = $usernum;
        $usernum = $ModelUser->where("ctime>='".$dateTime3."' and ctime<'".$dateTime2."'")->count();     
        $list[2]['useradd'] = $usernum;
        $usernum = $ModelUser->where("ctime>='".$dateTime4."' and ctime<'".$dateTime3."'")->count();     
        $list[3]['useradd'] = $usernum;
        $usernum = $ModelUser->where("ctime>='".$dateTime5."' and ctime<'".$dateTime4."'")->count();     
        $list[4]['useradd'] = $usernum;

        //支付宝充值
        $Model = M('Order');
        $price = $Model->where(" platform=2 and type=1 and state>=20 and ctime>='".$dateTime."'")->sum('price');
        if (!$price) {
            $price = 0;
        }
        $list[0]['price'] = $price;

        $price = $Model->where(" platform=2 and type=1 and state>=20 and ctime>='".$dateTime2."'and ctime<'".$dateTime."'")->sum('price');
        if (!$price) {
            $price = 0;
        }
        $list[1]['price'] = $price;

        $price = $Model->where(" platform=2 and type=1 and state>=20 and ctime>='".$dateTime3."'and ctime<'".$dateTime2."'")->sum('price');
        if (!$price) {
            $price = 0;
        }
        $list[2]['price'] = $price;

        $price = $Model->where(" platform=2 and type=1 and state>=20 and ctime>='".$dateTime4."'and ctime<'".$dateTime3."'")->sum('price');
        if (!$price) {
            $price = 0;
        }
        $list[3]['price'] = $price;

        $price = $Model->where(" platform=2 and type=1 and state>=20 and ctime>='".$dateTime5."'and ctime<'".$dateTime4."'")->sum('price');
        if (!$price) {
            $price = 0;
        }
        $list[4]['price'] = $price;

        $this->ajaxOutput(0, "suc", array('list'=>$list));    
    }
}
