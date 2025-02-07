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
namespace app\api\controller;
 
class Payment extends Base {
        
    /**
     * 析构流函数
     */
    public function  __construct() {   
        parent::__construct();                
    }

    /**
     * app端发起支付宝,支付宝返回服务器端,  返回到这里
     * http://www.tp-shop.cn/index.php/Api/Payment/alipayNotify
     */
    public function alipayNotify(){
             
        $paymentPlugin = M('Plugin')->where("code='alipay' and  type = 'payment' ")->find(); // 找到支付插件的配置
        $config_value = unserialize($paymentPlugin['config_value']); // 配置反序列化        
        
        require_once("plugins/payment/alipay/app_notify/alipay.config.php");
        require_once("plugins/payment/alipay/app_notify/lib/alipay_notify.class.php");
        
        $alipay_config['partner'] = $config_value['alipay_partner'];//合作身份者id，以2088开头的16位纯数字        
     
        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();                        
        //验证成功
        if($verify_result) 
        {                           
                $order_sn = $out_trade_no = trim($_POST['out_trade_no']); //商户订单号
                $trade_no = $_POST['trade_no'];//支付宝交易号
                $trade_status = $_POST['trade_status'];//交易状态
            

            if($_POST['trade_status'] == 'TRADE_FINISHED') 
            {            
                update_pay_status($order_sn); // 修改订单支付状态                
            }
            else if ($_POST['trade_status'] == 'TRADE_SUCCESS') 
            {            
                update_pay_status($order_sn); // 修改订单支付状态                
            }               
            M('order')->where('order_sn', $order_sn)->whereOr('master_order_sn',$order_sn)->save(array('pay_code'=>'alipay','pay_name'=>'app支付宝'));

            echo "success"; //  告诉支付宝支付成功 请不要修改或删除               
        }
        else 
        {                
            echo "fail"; //验证失败         
        }
    }
 
}
