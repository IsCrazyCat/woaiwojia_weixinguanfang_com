<?php
/**
 * tpshop
 * ============================================================================
 * * 版权所有 2015-2027 聊城博商网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: IT宇宙人 2015-08-10 $
 */ 
namespace app\home\controller;
use think\Controller;
use think\Session;
class Base extends Controller {
    public $session_id;
    public $cateTrre = array();
    /*
     * 初始化操作
     */
    public function _initialize() {  
        Session::start();
        header("Cache-control: private");  // history.back返回后输入框值丢失问题 参考文章 http://www.tp-shop.cn/article_id_1465.html  http://blog.csdn.net/qinchaoguang123456/article/details/29852881
    	$this->session_id = session_id(); // 当前的 session_id
        define('SESSION_ID',$this->session_id); //将当前的session_id保存为常量，供其它方法调用
        // 判断当前用户是否手机                
        if(isMobile()){
            cookie('is_mobile','1',3600);
            //header("Location: ".U('Mobile/Index/index'));
        }else{
            cookie('is_mobile','0',3600);
        } 
        $first_leader = I('first_leader');
        if($first_leader) setcookie('first_leader',$first_leader);
        $this->public_assign(); 
    }
    /**
     * 保存公告变量到 smarty中 比如 导航 
     */
    public function public_assign()
    {
        
       $tpshop_config = array();
       $tp_config = M('config')->cache(true,TPSHOP_CACHE_TIME)->select();       
       foreach($tp_config as $k => $v)
       {
       	  if($v['name'] == 'hot_keywords'){
       	  	 $tpshop_config['hot_keywords'] = explode('|', $v['value']);
       	  }       	  
          $tpshop_config[$v['inc_type'].'_'.$v['name']] = $v['value'];
       }                        
       
       $goods_category_tree = get_goods_category_tree();    
       $this->cateTrre = $goods_category_tree;
       $this->assign('goods_category_tree', $goods_category_tree);                     
       $brand_list = M('brand')->cache(true,TPSHOP_CACHE_TIME)->field('id,cat_id1,logo,is_hot')->where("cat_id1>0")->select();              
       $this->assign('brand_list', $brand_list);
       $this->assign('tpshop_config', $tpshop_config);          
    }  

    /*
     *
     */
    public function ajaxReturn($data){
        exit(json_encode($data));
    }
    
}