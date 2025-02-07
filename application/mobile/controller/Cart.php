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
namespace app\mobile\controller;
use think\Db;

class Cart extends MobileBase {
    
    public $cartLogic; // 购物车逻辑操作类    
    public $user_id = 0;
    public $user = array();        
    /**
     * 析构流函数
     */
    public function  __construct() {   
        parent::__construct();                
        $this->cartLogic = new \app\home\logic\CartLogic();                 
        if(session('?user'))
        {
        	$user = session('user');
                $user = M('users')->where("user_id", $user['user_id'])->find();
                session('user',$user);  //覆盖session 中的 user
        	$this->user = $user;
        	$this->user_id = $user['user_id'];
        	$this->assign('user',$user); //存储用户信息
                // 给用户计算会员价 登录前后不一样
                //if($user){
                //    $user[discount] = (empty($user[discount])) ? 1 : $user[discount];
                //    M('Cart')->execute("update `__PREFIX__cart` set member_goods_price = goods_price * {$user[discount]} where (user_id ={$user[user_id]} or session_id = '{$this->session_id}') and prom_type = 0");
                //}

        }
    }
    
    public function cart(){
        return $this->fetch('cart');
    }
    /**
     * 将商品加入购物车
     */
    function addCart()
    {
        $goods_id = I("goods_id/d"); // 商品id
        $goods_num = I("goods_num/d");// 商品数量
        $goods_spec = I("goods_spec"); // 商品规格                
        $goods_spec = json_decode($goods_spec,true); //app 端 json 形式传输过来
        $unique_id = I("unique_id"); // 唯一id  类似于 pc 端的session id
        $user_id = I("user_id/d",0); // 用户id        
        $result = $this->cartLogic->addCart($goods_id, $goods_num, $goods_spec,$unique_id,$user_id); // 将商品加入购物车
        exit(json_encode($result)); 
    }
    /**
     * ajax 将商品加入购物车
     */
    function ajaxAddCart()
    {
        $goods_id = I("goods_id/d"); // 商品id
        $goods_num = I("goods_num/d");// 商品数量
        $goods_spec = I("goods_spec/a"); // 商品规格
        $result = $this->cartLogic->addCart($goods_id, $goods_num, $goods_spec,$this->session_id,$this->user_id); // 将商品加入购物车
        exit(json_encode($result));
    }

    /*
     * 请求获取购物车列表
     */
    public function cartList()
    {
        $cart_form_data = $_POST["cart_form_data"]; // goods_num 购物车商品数量
        $cart_form_data = json_decode($cart_form_data,true); //app 端 json 形式传输过来

        $unique_id = I("unique_id"); // 唯一id  类似于 pc 端的session id
        $user_id = I("user_id/d"); // 用户id
        $where['session_id'] = $unique_id ; // 默认按照 $unique_id 查询
        $user_id && $where['user_id'] = $user_id; // 如果这个用户已经等了则按照用户id查询
        $cartList = M('Cart')->where($where)->getField("id,goods_num,selected");

        if($cart_form_data)
        {
            // 修改购物车数量 和勾选状态
            foreach($cart_form_data as $key => $val)
            {
                $data['goods_num'] = $val['goodsNum'];
                $data['selected'] = $val['selected'];
                $cartID = $val['cartID'];
                if(($cartList[$cartID]['goods_num'] != $data['goods_num']) || ($cartList[$cartID]['selected'] != $data['selected']))
                    M('Cart')->where("id" , $cartID)->save($data);
            }
            //$this->assign('select_all', $_POST['select_all']); // 全选框
        }

        $result = $this->cartLogic->cartList($this->user, $unique_id,0);
        exit(json_encode($result));
    }

    /**
     * 购物车第二步确定页面
     */
    public function cart2()
    {

        if($this->user_id == 0)
            $this->error('请先登陆',U('Mobile/User/login'));
        $address_id = I('address_id/d');
        if($address_id)
            $address = M('user_address')->where("address_id" , $address_id)->find();
        else
            $address = M('user_address')->where(["user_id" => $this->user_id , "is_default" => 1])->find();
        
        if(empty($address)){
        	header("Location: ".U('Mobile/User/add_address',array('source'=>'cart2')));
                exit;
        }else{
        	$this->assign('address',$address);
        }

        if($this->cartLogic->cart_count($this->user_id,1) == 0 )
            $this->error ('你的购物车没有选中商品','Cart/cart');

        $result = $this->cartLogic->cartList($this->user, $this->session_id,1,1); // 获取购物车商品
        
        $store_id_arr = M('cart')->where("user_id = {$this->user_id} and selected = 1")->getField('store_id',true); // 获取所有店铺id        
        $storeList = M('store')->where("store_id in (".implode(',', $store_id_arr).")")->cache(true,TPSHOP_CACHE_TIME)->select(); // 找出所有商品对应的店铺id
        
        $shippingList = M('shipping_area')->where(" store_id in (".implode(',', $store_id_arr).") and is_default = 1 and is_close = 1")->group("store_id,shipping_code")->getField('shipping_area_id,shipping_code,store_id');// 物流公司
        $shippingList2 = M('plugin')->where("type = 'shipping'")->cache(true,TPSHOP_CACHE_TIME)->getField('code,name'); // 查找物流插件
        foreach($shippingList as $k => $v)
            $shippingList[$k]['name']  = $shippingList2[$v['shipping_code']];                
        
        // 找出这个用户的优惠券 没过期的  并且 订单金额达到 condition 优惠券指定标准的               
        $sql = "select c1.name,c1.money,c1.condition, c2.* from __PREFIX__coupon as c1 inner join __PREFIX__coupon_list as c2  on c2.cid = c1.id and c1.type in(0,1,2,3) and order_id = 0
            where c2.uid = {$this->user_id}  and ".time()." < c1.use_end_time and c1.condition <= {$result['total_price']['total_fee']} and c2.store_id in (".implode(',', $store_id_arr).")";
        $couponList = Db::query($sql);

        //print_r($couponList);
        $this->assign('storeList', $storeList); // 店铺列表
        $this->assign('couponList', $couponList); // 优惠券列表
        $this->assign('shippingList', $shippingList); // 物流公司
        $this->assign('cartList', $result['cartList']); // 购物车的商品
        $this->assign('total_price', $result['total_price']); // 总计
        return $this->fetch();
    }

    /**
     * ajax 获取订单商品价格 或者提交 订单
     */
    public function cart3(){
                                
        if($this->user_id == 0)
            exit(json_encode(array('status'=>-100,'msg'=>"登录超时请重新登录!",'result'=>null))); // 返回结果状态
        
        $address_id = I("address_id/d"); //  收货地址id
        $shipping_code =  I("shipping_code/a"); //  物流编号        
        $user_note = I('user_note/a'); // 给卖家留言        
        $couponTypeSelect =  I("couponTypeSelect/a"); //  优惠券类型  1 下拉框选择优惠券 2 输入框输入优惠券代码
        $coupon_id =  I("coupon_id/a"); //  优惠券id
        $couponCode =  I("couponCode/a"); //  优惠券代码
        $invoice_title = I('invoice_title'); // 发票
        $pay_points =  I("pay_points/d",0); //  使用银币
        $user_money =  I("user_money/f",0); //  使用金币        
        $user_money = $user_money ? $user_money : 0;

        if($this->cartLogic->cart_count($this->user_id,1) == 0 ) exit(json_encode(array('status'=>-2,'msg'=>'你的购物车没有选中商品','result'=>null))); // 返回结果状态
        if(!$address_id) exit(json_encode(array('status'=>-3,'msg'=>'请先填写收货人信息','result'=>null))); // 返回结果状态
        if(!$shipping_code) exit(json_encode(array('status'=>-4,'msg'=>'请选择物流信息','result'=>null))); // 返回结果状态
		
		$address = M('UserAddress')->where("address_id" , $address_id)->find();
		$order_goods = M('cart')->where(["user_id" => $this->user_id ,  "selected" => 1])->select();

		$result = calculate_price($this->user_id,$order_goods,$shipping_code,0,$address[province],$address[city],$address[district],$pay_points,$user_money,$coupon_id,$couponCode);

		if($result['status'] < 0)
			exit(json_encode($result));      	

                $car_price = array(
                    'postFee'      => $result['result']['shipping_price'], // 物流费
                    'couponFee'    => $result['result']['coupon_price'], // 优惠券            
                    'balance'      => $result['result']['user_money'], // 使用用户金币
                    'pointsFee'    => $result['result']['integral_money'], // 银币支付            
                    'payables'     => array_sum($result['result']['store_order_amount']), // 订单总额 减去 银币 减去金币
                    'goodsFee'     => $result['result']['goods_price'],// 总商品价格
                    'order_prom_amount' => array_sum($result['result']['store_order_prom_amount']), // 总订单优惠活动优惠了多少钱

                    'store_order_prom_id'=> $result['result']['store_order_prom_id'], // 每个商家订单优惠活动的id号
                    'store_order_prom_amount'=> $result['result']['store_order_prom_amount'], // 每个商家订单活动优惠了多少钱
                    'store_order_amount' => $result['result']['store_order_amount'], // 每个商家订单优惠后多少钱, -- 应付金额
                    'store_shipping_price'=>$result['result']['store_shipping_price'],  //每个商家的物流费
                    'store_coupon_price'=>$result['result']['store_coupon_price'],  //每个商家的优惠券抵消金额
                    'store_point_count' => $result['result']['store_point_count'], // 每个商家平摊使用了多少银币            
                    'store_balance'=>$result['result']['store_balance'], // 每个商家平摊用了多少金币
                    'store_goods_price'=>$result['result']['store_goods_price'], // 每个商家的商品总价
                );   
                // 提交订单        
                if($_REQUEST['act'] == 'submit_order')
                {  
                    if(empty($coupon_id) && !empty($couponCode))
                    {
                        foreach($couponCode as $k => $v)
                        $coupon_id[$k] = M('CouponList')->where("code",$v)->where("store_id",$k)->getField('id');
                    }                
                    $result = $this->cartLogic->addOrder($this->user_id,$address_id,$shipping_code,$invoice_title,$coupon_id,$car_price,$user_note); // 添加订单                        
                    exit(json_encode($result));            
                }
                    $return_arr = array('status'=>1,'msg'=>'计算成功','result'=>$car_price); // 返回结果状态
                    exit(json_encode($return_arr));                   
    }	
    /*
     * 订单支付页面
     */
    public function cart4(){
        $order_id = I('order_id/d',0); 
        
        // 如果是主订单号过来的, 说明可能是合并付款的
        $master_order_sn = I('master_order_sn','');        
        if($master_order_sn)
        {                       
            $sum_order_amount = M('order')->where("master_order_sn", $master_order_sn)->sum('order_amount');
            if($sum_order_amount == 0){                
                $order_order_list = U("Mobile/User/order_list");
                header("Location: $order_order_list");
                exit;
            }           
        }
        else
        {
            $order = M('Order')->where("order_id",$order_id)->find();
            // 如果已经支付过的订单直接到订单详情页面. 不再进入支付页面
            if($order['pay_status'] == 1){

                $order_detail_url = U("Mobile/User/order_detail",array('id'=>$order_id));
                header("Location: $order_detail_url");
            }
        }                 
        //微信浏览器
        if(strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger'))
            $paymentList = M('Plugin')->where("`type`='payment' and status = 1 and code in('weixin','cod')")->select();            
        else 
            $paymentList = M('Plugin')->where("`type`='payment' and status = 1 and  scene in(1)")->select();        
        
        $paymentList = convert_arr_key($paymentList, 'code');                
        foreach($paymentList as $key => $val)
        {
            $val['config_value'] = unserialize($val['config_value']);            
            if($val['config_value']['is_bank'] == 2)
            {
                $bankCodeList[$val['code']] = unserialize($val['bank_code']);        
            }
            //判断当前浏览器显示支付方式
            if(($key == 'weixin' && !is_weixin()) || ($key == 'alipayMobile' && is_weixin())){
                unset($paymentList[$key]);
            }
        }                
        
        $bank_img = include APP_PATH.'home/bank.php'; // 银行对应图片
        $payment = M('Plugin')->where("`type`='payment' and status = 1")->select();        
        $this->assign('paymentList',$paymentList);        
        $this->assign('bank_img',$bank_img);
        $this->assign('master_order_sn', $master_order_sn); // 主订单号
        $this->assign('sum_order_amount', $sum_order_amount); // 所有订单应付金额        
        $this->assign('order',$order);
        $this->assign('bankCodeList',$bankCodeList);        
        $this->assign('pay_date',date('Y-m-d', strtotime("+1 day")));
        return $this->fetch();                   
    }

    /*
    * ajax 请求获取购物车列表
    */
    public function ajaxCartList()
    {
        $post_goods_num = I("goods_num/a"); // goods_num 购物车商品数量
        $post_cart_select = I("cart_select/a"); // 购物车选中状态
        $where['session_id'] = $this->session_id ; // 默认按照 session_id 查询
        $this->user_id && $where['user_id'] = $this->user_id; // 如果这个用户已经等了则按照用户id查询

        $cartList = M('Cart')->where($where)->getField("id,goods_num,selected,prom_type,prom_id"); 

        if($post_goods_num)
        {
            // 修改购物车数量 和勾选状态
            foreach($post_goods_num as $key => $val)
            {                
                $data['goods_num'] = $val < 1 ? 1 : $val;
                if($cartList[$key]['prom_type'] == 1) //限时抢购 不能超过购买数量
                {
                    $flash_sale = M('flash_sale')->where("id" , $cartList[$key]['prom_id'])->find();
                    $data['goods_num'] = $data['goods_num'] > $flash_sale['buy_limit'] ? $flash_sale['buy_limit'] : $data['goods_num'];
                }
                
                $data['selected'] = $post_cart_select[$key] ? 1 : 0 ;
                if(($cartList[$key]['goods_num'] != $data['goods_num']) || ($cartList[$key]['selected'] != $data['selected']))
                    M('Cart')->where("id" ,$key)->save($data);
            }
            $this->assign('select_all', $_POST['select_all']); // 全选框
        }

        $result = $this->cartLogic->cartList($this->user, $this->session_id,1,1);        
        if(empty($result['total_price']))
            $result['total_price'] = Array( 'total_fee' =>0, 'cut_fee' =>0, 'num' => 0, 'atotal_fee' =>0, 'acut_fee' =>0, 'anum' => 0);
        
        $storeList = M('store')->getField("store_id,store_name"); // 找出商家
        $this->assign('storeList', $storeList); // 商家列表       
        $this->assign('cartList', $result['cartList']); // 购物车的商品                
        $this->assign('total_price', $result['total_price']); // 总计       
        return $this->fetch('ajax_cart_list');
    }   

/*
 * ajax 获取用户收货地址 用于购物车确认订单页面
 */
    public function ajaxAddress(){

        $regionList = M('Region')->getField('id,name');

        $address_list = M('UserAddress')->where("user_id ",$this->user_id)->select();
        $c = M('UserAddress')->where(array("user_id" => $this->user_id , 'is_default' => 1))->count(); // 看看有没默认收货地址
        if((count($address_list) > 0) && ($c == 0)) // 如果没有设置默认收货地址, 则第一条设置为默认收货地址
            $address_list[0]['is_default'] = 1;

        $this->assign('regionList', $regionList);
        $this->assign('address_list', $address_list);
        return $this->fetch('ajax_address');
    }

    /**
     * ajax 删除购物车的商品
     */
    public function ajaxDelCart()
    {
        $ids = I("ids"); // 商品 ids
        $result = M("Cart")->where("id" , "in" , $ids)->delete(); // 删除id为5的用户数据
        $return_arr = array('status'=>1,'msg'=>'删除成功','result'=>''); // 返回结果状态
        exit(json_encode($return_arr));
    }
}
