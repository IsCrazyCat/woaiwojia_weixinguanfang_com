<?php
/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 聊城博商网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * Author: IT宇宙人
 * Date: 2015-09-09
 */

namespace app\home\logic;

use think\Model;
use think\Db;
/**
 * 购物车 逻辑定义
 * Class CatsLogic
 * @package Home\Logic
 */
class CartLogic extends Model
{

    
    /**
     * 加入购物车方法
     * @param type $goods_id  商品id
     * @param type $goods_num   商品数量
     * @param type $goods_spec  选择规格 
     * @param type $user_id 用户id
     */
    function addCart($goods_id,$goods_num,$goods_spec,$session_id,$user_id = 0)
    {       
        $goods = M('Goods')->where("goods_id",$goods_id)->cache(true,TPSHOP_CACHE_TIME)->find(); // 找出这个商品
        $specGoodsPriceList = M('SpecGoodsPrice')->where("goods_id",$goods_id)->cache(true,TPSHOP_CACHE_TIME)->getField("key,key_name,price,store_count,sku"); // 获取商品对应的规格价钱 库存 条码
 
		$where = " session_id = '$session_id' ";
        $user_id = $user_id ? $user_id : 0;
		if($user_id)
		 $where .= "  or user_id= $user_id ";
        $catr_count = M('Cart')->where($where)->count(); // 查找购物车商品总数量
        if($catr_count >= 20) 
            return array('status'=>-9,'msg'=>'购物车最多只能放20种商品','result'=>'');            
        if(!empty($specGoodsPriceList) && empty($goods_spec)) // 有商品规格 但是前台没有传递过来
            return array('status'=>-1,'msg'=>'必须传递商品规格','result'=>'');                        
        if($goods_num <= 0) 
            return array('status'=>-2,'msg'=>'购买商品数量不能为0','result'=>'');            
        if(empty($goods))
            return array('status'=>-3,'msg'=>'购买商品不存在','result'=>'');            
        if($goods['prom_type'] > 0 && $user_id == 0)
            return array('status'=>-101,'msg'=>'购买活动商品必须先登录','result'=>'');
        
        //限时抢购 不能超过购买数量        
        if($goods['prom_type'] == 1) 
        {
            $flash_sale = M('flash_sale')->where("id = {$goods['prom_id']} and ".time()." > start_time and ".time()." < end_time and goods_num > buy_num")->find(); // 限时抢购活动
            if($flash_sale){
                $cart_goods_num = M('Cart')->where("($where) and goods_id = {$goods['goods_id']}")->getField('goods_num');
                // 如果购买数量 大于每人限购数量
                if(($goods_num + $cart_goods_num) > $flash_sale['buy_limit'])
                {  
                    $cart_goods_num && $error_msg = "你当前购物车已有 $cart_goods_num 件!";
                    return array('status'=>-4,'msg'=>"每人限购 {$flash_sale['buy_limit']}件 $error_msg",'result'=>'');
                }                        
                // 如果剩余数量 不足 限购数量, 就只能买剩余数量
                if(($flash_sale['goods_num'] - $flash_sale['buy_num']) < $flash_sale['buy_limit'])
                    return array('status'=>-4,'msg'=>"库存不够,你只能买".($flash_sale['goods_num'] - $flash_sale['buy_num'])."件了.",'result'=>'');                    
            }
        }                
        if(is_array($goods_spec)){
            foreach($goods_spec as $key => $val){ // 处理商品规格
                $spec_item[] = $val; // 所选择的规格项
            }
        }
                              
        if(!empty($spec_item)) // 有选择商品规格
        {
            sort($spec_item);
            $spec_key = implode('_', $spec_item);
            if($specGoodsPriceList[$spec_key]['store_count'] < $goods_num) 
                return array('status'=>-4,'msg'=>'商品库存不足','result'=>'');
            $spec_price = $specGoodsPriceList[$spec_key]['price']; // 获取规格指定的价格
        }else  if(($goods['store_count'] < $goods_num)){
                return array('status'=>-4,'msg'=>'商品库存不足','result'=>'');
        }
                
        $where = " goods_id = $goods_id and spec_key = '$spec_key' "; // 查询购物车是否已经存在这商品
        if($user_id > 0)
            $where .= " and (session_id = '$session_id' or user_id = $user_id) ";
        else
            $where .= " and  session_id = '$session_id' ";
        
        $catr_goods = M('Cart')->where($where)->find(); // 查找购物车是否已经存在该商品
        $price = $spec_price ? $spec_price : $goods['shop_price']; // 如果商品规格没有指定价格则用商品原始价格
        
        // 商品参与促销
        if($goods['prom_type'] > 0)
        {            
            $prom = get_goods_promotion($goods_id,$user_id);
            $price = $prom['price'];
            $goods['prom_type'] = $prom['prom_type'];
            $goods['prom_id']   = $prom['prom_id'];
        }
        
        $data = array(                    
                    'user_id'         => $user_id,   // 用户id
                    'session_id'      => $session_id,   // sessionid
                    'goods_id'        => $goods_id,   // 商品id
                    'goods_sn'        => $goods['goods_sn'],   // 商品货号
                    'goods_name'      => $goods['goods_name'],   // 商品名称
                    'market_price'    => $goods['market_price'],   // 市场价
                    'goods_price'     => $price,  // 购买价
                    'member_goods_price' => $price,  // 会员折扣价 默认为 购买价
                    'goods_num'       => $goods_num, // 购买数量
                    'spec_key'        => "{$spec_key}", // 规格key
                    'spec_key_name'   => "{$specGoodsPriceList[$spec_key]['key_name']}", // 规格 key_name
                    'sku'        => "{$specGoodsPriceList[$spec_key]['sku']}", // 商品条形码                    
                    'add_time'        => time(), // 加入购物车时间
                    'prom_type'       => $goods['prom_type'],   // 0 普通订单,1 限时抢购, 2 团购 , 3 促销优惠
                    'prom_id'         => $goods['prom_id'],   // 活动id
                    'store_id'        => $goods['store_id'],   // 店铺id
        );                

       // 如果商品购物车已经存在 
       if($catr_goods) 
       {            
           // 如果购物车的已有数量加上 这次要购买的数量  大于  库存输  则不再增加数量
            if(($catr_goods['goods_num'] + $goods_num) > $goods['store_count'])
                $goods_num = 0;           
            $result = M('Cart')->where("id =".$catr_goods[id])->save(  array("goods_num"=> ($catr_goods['goods_num'] + $goods_num)) ); // 数量相加        
            $cart_count = cart_goods_num($user_id,$session_id); // 查找购物车数量 
            setcookie('cn',$cart_count,null,'/');
            return array('status'=>1,'msg'=>'成功加入购物车','result'=>$cart_count);
       }
       else
       {         
             $insert_id = M('Cart')->add($data);
             $cart_count = cart_goods_num($user_id,$session_id); // 查找购物车数量
             setcookie('cn',$cart_count,null,'/');
             return array('status'=>1,'msg'=>'成功加入购物车','result'=>$cart_count);
       }     
            $cart_count = cart_goods_num($user_id,$session_id); // 查找购物车数量 
            return array('status'=>-5,'msg'=>'加入购物车失败','result'=>$cart_count);        
    }
    
    /**
     * 购物车列表 
     * @param type $user   用户
     * @param type $session_id  session_id
     * @param type $selected  是否被用户勾选中的 0 为全部 1为选中  一般没有查询不选中的商品情况
     * $mode 0  返回数组形式  1 直接返回result
     */
    function cartList($user = array() , $session_id = '', $selected = 0,$mode =0)
    {                   
        
        $where = " 1 = 1 ";
        //if($selected != NULL)
        //    $where = " selected = $selected "; // 购物车选中状态
        
        if($user[user_id])// 如果用户已经登录则按照用户id查询
        {
             $where .= " and user_id = $user[user_id] ";
             // 给用户计算会员价 登录前后不一样             
        }           
        else
        {
            $where .= " and session_id = '$session_id'";
            $user[user_id] = 0;
        }
                                
        $cartList = M('Cart')->where($where)->select();  // 获取购物车商品 
        $anum = $total_price =  $cut_fee = 0;

        foreach ($cartList as $k=>$val){
        	$cartList[$k]['goods_fee'] = $val['goods_num'] * $val['member_goods_price'];
        	$cartList[$k]['store_count']  = getGoodNum($val['goods_id'],$val['spec_key']); // 最多可购买的库存数量        	
                $anum += $val['goods_num'];
                
                // 如果要求只计算购物车选中商品的价格 和数量  并且  当前商品没选择 则跳过
                if($selected == 1 && $val['selected'] == 0)
                    continue;
                
                $cut_fee += $val['goods_num'] * $val['market_price'] - $val['goods_num'] * $val['member_goods_price'];                
        	$total_price += $val['goods_num'] * $val['member_goods_price'];
        }

        $total_price = array('total_fee' =>$total_price , 'cut_fee' => $cut_fee,'num'=> $anum,); // 总计        
        setcookie('cn',$anum,null,'/');
        if($mode == 1) return array('cartList' => $cartList, 'total_price' => $total_price);
        return array('status'=>1,'msg'=>'','result'=>array('cartList' =>$cartList, 'total_price' => $total_price));
    }       
    
/**
 * 计算商品的的运费 
 * @param type $shipping_code 物流 编号
 * @param type $province  省份
 * @param type $city     市
 * @param type $district  区
 * @param type $weight  商品重量 
 * @param type $store_id 店铺id 
 * @return int
 */
function cart_freight2($shipping_code,$province,$city,$district,$weight,$store_id='0')
{
    
    if($weight == 0) return 0; // 商品没有重量
    if($shipping_code == '') return 0;               
  
   // 先根据 镇 县 区找 shipping_area_id   
      $shipping_area_id = M('AreaRegion')->where("store_id = $store_id and shipping_area_id in (select shipping_area_id from  ".C('database.prefix')."shipping_area where shipping_code = '$shipping_code') and region_id = {$district}")->getField('shipping_area_id');
    
    // 先根据市区找 shipping_area_id
   if($shipping_area_id == false)    
      $shipping_area_id = M('AreaRegion')->where("store_id = $store_id and shipping_area_id in (select shipping_area_id from  ".C('database.prefix')."shipping_area where shipping_code = '$shipping_code') and region_id = {$city}")->getField('shipping_area_id');

   // 市区找不到 根据省份找shipping_area_id
   if($shipping_area_id == false)
        $shipping_area_id = M('AreaRegion')->where("store_id = $store_id and shipping_area_id in (select shipping_area_id from  ".C('database.prefix')."shipping_area where shipping_code = '$shipping_code') and region_id = {$province}")->getField('shipping_area_id');

   // 省份找不到 找默认配置全国的物流费
   if($shipping_area_id == false)
   {           
        // 如果市和省份都没查到, 就查询 tp_shipping_area 表 is_default = 1 的  表示全国的  select * from `tp_plugin`  select * from  `tp_shipping_area` select * from  `tp_area_region`           
       $shipping_area_id = M("ShippingArea")->where("store_id = $store_id and shipping_code = '$shipping_code' and is_default = 1")->getField('shipping_area_id');
   }
   if($shipping_area_id == false)
       return 0;
   /// 找到了 shipping_area_id  找config       
   $shipping_config = M('ShippingArea')->where("shipping_area_id = $shipping_area_id")->getField('config');
   $shipping_config  = unserialize($shipping_config);
   $shipping_config['money'] = $shipping_config['money'] ? $shipping_config['money'] : 0;

   // 1000 克以内的 只算个首重费
   if($weight < $shipping_config['first_weight'])
   {          
       return $shipping_config['money'];     
   }
   // 超过 1000 克的计算方法 
   $weight = $weight - $shipping_config['first_weight']; // 续重
   $weight = ceil($weight / $shipping_config['second_weight']); // 续重不够取整 
   $freight = $shipping_config['money'] +  $weight * $shipping_config['add_money']; // 首重 + 续重 * 续重费       
   
   return $freight;  
}
  
    /**
     * 获取用户可以使用的优惠券
     * @param type $user_id  用户id 
     * @param type $coupon_id 优惠券id
     * @param type $store_id 商家id
     * $mode 0  返回数组形式  1 直接返回result
     */
    public function getCouponMoney($user_id, $coupon_id,$store_id,$mode)
    {
        if($coupon_id == 0)
        {
            if($mode == 1) return 0;    
            return array('status'=>1,'msg'=>'','result'=>0);            
        }
            
        $couponlist = M('CouponList')->where("store_id = $store_id and uid = $user_id and id = $coupon_id")->find(); // 获取用户的优惠券
        if(empty($couponlist)) {
            if($mode == 1) return 0;    
            return array('status'=>1,'msg'=>'','result'=>0);
        }            
        
        $coupon = M('Coupon')->where("id = {$couponlist['cid']}")->find(); // 获取 优惠券类型表
        $coupon['money'] = $coupon['money'] ? $coupon['money'] : 0;
       
        if($mode == 1) return $coupon['money'];
        return array('status'=>1,'msg'=>'','result'=>$coupon['money']);        
    }
    
    /**
     * 根据优惠券代码获取优惠券金额
     * @param type $couponCode 优惠券代码
     * @param type $order_momey Description 订单金额
     * @param type $store_id 商家id
     * return -1 优惠券不存在 -2 优惠券已过期 -3 订单金额没达到使用券条件
     */
    public function getCouponMoneyByCode($couponCode,$order_momey,$store_id)
    {
        if(empty($couponCode))
            return array('status'=>-20,'msg'=>'优惠券码不存在','result'=>'');
       
        $couponlist = M('CouponList')->where("store_id",$store_id)->where("code",$couponCode)->find(); // 获取用户的优惠券
        if(empty($couponlist))
            return array('status'=>-20,'msg'=>'优惠券码不存在','result'=>'');
        if($couponlist['order_id'] > 0){
            return array('status'=>-20,'msg'=>'该优惠券已被使用','result'=>'');
        }
        $coupon = M('Coupon')->where("id = {$couponlist['cid']}")->find(); // 获取优惠券类型表
        if(time() > $coupon['use_end_time'])  
            return array('status'=>-10,'msg'=>'优惠券已经过期','result'=>'');
        if($order_momey < $coupon['condition'])
            return array('status'=>-11,'msg'=>'金额没达到优惠券使用条件','result'=>'');
        return array('status'=>1,'msg'=>'','result'=>$coupon['money']);        
    }
    
    /**
     *  添加一个订单
     * @param type $user_id  用户id     
     * @param type $address_id 地址id
     * @param type $shipping_code 物流编号
     * @param type $invoice_title 发票
     * @param type $coupon_id 优惠券id
     * @param type $car_price 各种价格
     * @return type $order_id 返回新增的订单id
     * @return type $user_note  给卖家留言
     */
    public function addOrder($user_id,$address_id,$shipping_code,$invoice_title,$coupon_id = array(),$car_price,$user_note)
    {
        // 仿制灌水 1天只能下 50 单  // select * from `tp_order` where user_id = 1  and order_sn like '20151217%' 
        $order_count = M('Order')->where("user_id= $user_id and order_sn like '".date('Ymd')."%'")->count(); // 查找购物车商品总数量
        if($order_count >= 50) 
            return array('status'=>-9,'msg'=>'一天只能下50个订单','result'=>'');            
        
         // 插入订单 order
        $address = M('UserAddress')->where("address_id",$address_id)->find();
               
        // 循环添加订单 多少个商家添加多少个订单
        foreach($car_price['store_order_amount'] as $k => $v)
        {                           
                $shipping = M('Plugin')->where("code",$shipping_code[$k])->cache(true,TPSHOP_CACHE_TIME)->find();
                $order_sn = $this->get_order_sn(); // 获取生成订单号                                 
                $master_order_sn = $this->get_order_sn(); // 住订单号
                // 用户使用金币
                $car_price['store_balance'][$k] = $car_price['store_balance'][$k] ? $car_price['store_balance'][$k] : 0;
                // 用户使用银币
                $car_price['store_point_count'][$k] = $car_price['store_point_count'][$k] ? $car_price['store_point_count'][$k] : 0;
                $data = array(
                        'order_sn'         =>$order_sn, // 订单编号
                        'master_order_sn'  =>$master_order_sn, // 主订单号
                        'user_id'          =>$user_id, // 用户id
                        'consignee'        =>$address['consignee'], // 收货人
                        'province'         =>$address['province'],//'省份id',
                        'city'             =>$address['city'],//'城市id',
                        'district'         =>$address['district'],//'县',
                        'twon'             =>$address['twon'],// '街道',
                        'address'          =>$address['address'],//'详细地址',
                        'mobile'           =>$address['mobile'],//'手机',
                        'zipcode'          =>$address['zipcode'],//'邮编',            
                        'email'            =>$address['email'],//'邮箱',
                        'shipping_code'    =>$shipping['code'],//'物流编号',
                        'shipping_name'    =>$shipping['name'], //'物流名称',
                        'invoice_title'    =>$invoice_title, //'发票抬头',
                        'user_note'        =>$user_note[$k], //'给卖家留言',
                        'goods_price'      =>$car_price['store_goods_price'][$k],//每个店铺的商品价格',
                        'shipping_price'   =>$car_price['store_shipping_price'][$k],//'物流价格',                
                        'user_money'       =>$car_price['store_balance'][$k], // 当前订单使用的金币数量
                        'coupon_price'     =>$car_price['store_coupon_price'][$k],//'使用优惠券',                        
                        'integral'         =>$car_price['store_point_count'][$k], // 使用的银币数量
                        'integral_money'   =>($car_price['store_point_count'][$k] / tpCache('shopping.point_rate')),//'使用银币抵多少钱',
                        'total_amount'     =>($car_price['store_goods_price'][$k] + $car_price['store_shipping_price'][$k]),// 订单总额 = 商品总价 + 物流费
                        'order_amount'     =>$car_price['store_order_amount'][$k],//'应付款金额',                
                        'add_time'         =>time(), // 下单时间                
                        'order_prom_id'    =>$car_price['store_order_prom_id'][$k],//'订单优惠活动id',
                        'order_prom_amount'=>$car_price['store_order_prom_amount'][$k],//'订单优惠活动优惠了多少钱',
                        'store_id'         =>$k,  // 店铺id
                );

                $order_id = M("Order")->insertGetId($data);

                // 记录订单操作日志
                $action_info = array(
                    'order_id'        =>$order_id,
                    'action_user'     =>$user_id,
                    'user_type'       =>2,		                  
                    'action_note'     => '您提交了订单，请等待系统确认',
                    'status_desc'     =>'提交订单', //''
                    'log_time'        =>time(),
                );
                M('order_action')->add($action_info);               
              
                // 1插入order_goods 表
                $cartList = M('Cart')->where("store_id = $k and user_id = $user_id and selected = 1")->select();
                foreach($cartList as $key => $val)
                {
                   $goods = M('goods')->where("goods_id = {$val['goods_id']} ")->cache(true,TPSHOP_CACHE_TIME)->find();
                   $data2['order_id']           = $order_id; // 订单id
                   $data2['goods_id']           = $val['goods_id']; // 商品id
                   $data2['goods_name']         = $val['goods_name']; // 商品名称
                   $data2['goods_sn']           = $val['goods_sn']; // 商品货号
                   $data2['goods_num']          = $val['goods_num']; // 购买数量
                   $data2['market_price']       = $val['market_price']; // 市场价
                   $data2['goods_price']        = $val['goods_price']; // 商品价
                   $data2['spec_key']           = $val['spec_key']; // 商品规格
                   $data2['spec_key_name']      = $val['spec_key_name']; // 商品规格名称
                   $data2['sku']                = $val['sku']; // 商品条码
                   $data2['member_goods_price'] = $val['member_goods_price']; // 会员折扣价
                   $data2['cost_price']         = $goods['cost_price']; // 成本价
                   $data2['give_integral']      = $goods['give_integral']; // 购买商品赠送银币         
                   $data2['prom_type']          = $val['prom_type']; // 0 普通订单,1 限时抢购, 2 团购 , 3 促销优惠
                   $data2['prom_id']            = $val['prom_id']; // 活动id
                   $data2['store_id']           = $val['store_id']; // 店铺id
                   $data2['distribut']          = $goods['distribut']; // 三级分销金额

                   $data2['goodstype']          = $goods['goodstype']; // 商品品类 lishibo 20190903

                   $data2['commission']         = M('goods_category')->where("id = {$goods['cat_id3']}")->cache(true,TPSHOP_CACHE_TIME)->getField('commission'); // 商品抽成比例
                   $order_goods_id              = M("OrderGoods")->insert($data2); 
                   // 扣除商品库存  扣除库存移到 付完款后扣除
                   //M('Goods')->where("goods_id = ".$val['goods_id'])->setDec('store_count',$val['goods_num']); // 商品减少库存
                } 
                
                // 如果应付金额为0  可能是金币支付 + 银币 + 优惠券 这里订单支付状态直接变成已支付 
                if($data['order_amount'] == 0)
                {                        
                    update_pay_status($order_sn, 1); // 这里刚刚下的订单必须从主库里面去查
                }                 

                if(!empty($coupon_id[$k])){
                	// 2修改优惠券状态
                	$data3['uid'] = $user_id;
                	$data3['order_id'] = $order_id;
                	$data3['use_time'] = time();
                	M('CouponList')->where("id",$coupon_id[$k])->save($data3);
                	$cid = M('CouponList')->where("id",$coupon_id[$k])->getField('cid');
                	M('Coupon')->where("id",$cid)->setInc('use_num'); // 优惠券的使用数量加一
                }

                // 3 扣除银币 扣除金币
                if($car_price['store_point_count'][$k] != 0)
                    M('Users')->where("user_id = $user_id")->setDec('pay_points',$car_price['store_point_count'][$k]); // 用户的银币减 
                if($car_price['store_balance'][$k] != 0)
                    M('Users')->where("user_id = $user_id")->setDec('user_money',$car_price['store_balance'][$k]); // 用户的金币减         
               
                // 4 清空购物车
                M('Cart')->where("store_id = $k and user_id = $user_id and selected = 1")->delete();

                // 5 记录log 日志
                $data4['user_id'] = $user_id;
                $data4['user_money'] = -$car_price['store_balance'][$k];
                $data4['pay_points'] = -$car_price['store_point_count'][$k];
                $data4['change_time'] = time();
                $data4['desc'] = '下单消费';
                $data4['order_sn'] = $order_sn;
                $data4['order_id'] = $order_id;    
                // 如果使用了银币或者金币才记录
                ($data4['user_money'] || $data4['pay_points']) && M("AccountLog")->add($data4);     

				//分销开关全局
				$order = M("Order")->where("order_id = $order_id")->find();

//                if(file_exists(APP_PATH.'common/logic/DistributLogic.php'))
//				{
//					$distributLogic = new \app\common\logic\DistributLogic();
//					$distributLogic->rebate_log($order); // 生成分成记录
//				}

				// 如果有微信公众号 则推送一条消息到微信
				$user = M('users')->where("user_id = $user_id")->find();
				if($user['oauth']== 'weixin')
				{
					$wx_user = M('wx_user')->find();
					$jssdk = new \app\mobile\logic\Jssdk($wx_user['appid'],$wx_user['appsecret']);
					$wx_content = "你刚刚下了一笔订单:{$order['order_sn']} 尽快支付,过期失效!";
					$jssdk->push_msg($user['openid'],$wx_content);
				}	 
				
				//用户下单, 发送短信给商家
				$res = checkEnableSendSms("3");
					
				if($res && $res['status'] ==1){
				    $store = M('store')->where("store_id = ".$order_id)->find();
				    $sender = (!empty($store) && !empty($store['service_phone'])) ? $store['service_phone'] : false;
				    $params = array('consignee'=>$order['consignee'] , 'mobile' => $order['mobile']);
				    $resp = sendSms("3", $sender, $params);
				} 	
           }        
                
        return array('status'=>1,'msg'=>'提交订单成功','result'=>$master_order_sn); // 返回新增的订单id
    }
    
    
  /**
   * 获取订单 order_sn
   */
   public function get_order_sn()
   {
        // 保证不会有重复订单号存在
        while(true){
            $order_sn = date('YmdHis').rand(1000,9999); // 订单编号
            $order_sn_count = M('order')->where("order_sn = '$order_sn' or master_order_sn = '$order_sn'")->count();
            if($order_sn_count == 0)
                break;
        }
        return $order_sn;
   }
    /**
     * 查看购物车的商品数量
     * @param type $user_id
     * $mode 0  返回数组形式  1 直接返回result
     */
    public function cart_count($user_id,$mode = 0){
        $count = M('Cart')->where("user_id = $user_id and selected = 1")->count();
        if($mode == 1) return  $count;
        
        return array('status'=>1,'msg'=>'','result'=>$count);         
    }
        
   /**
    * 获取商品团购价
    * 如果商品没有团购活动 则返回 0
    * @param type $attr_id
    * $mode 0  返回数组形式  1 直接返回result
    */
   public function get_group_buy_price($goods_id,$mode=0)
   {
       $group_buy = M('GroupBuy')->where("goods_id",$goods_id)->where( time()." >= start_time and ".time()." <= end_time ")->find(); // 找出这个商品                      
       if(empty($group_buy))       
            return 0;
       
        if($mode == 1) return $group_buy['groupbuy_price'];
        return array('status'=>1,'msg'=>'','result'=>$group_buy['groupbuy_price']);       
   }  
   
   /**
    * 用户登录后 需要对购物车 一些操作
    * @param type $session_id
    * @param type $user_id
    */
   public function login_cart_handle($session_id,$user_id)
   {
        // 登录后将购物车的商品的 user_id 改为当前登录的id            
        M('cart')->where("session_id = '$session_id'")->save(array('user_id'=>$user_id));                    
        

        // 查找购物车两件完全相同的商品
        $cart_id_arr = Db::query("select id from `__PREFIX__cart` where user_id = $user_id group by  goods_id,spec_key having count(goods_id) > 1");
        if(!empty($cart_id_arr))
        {
            $cart_id_arr = get_arr_column($cart_id_arr, 'id');
            $cart_id_str = implode(',', $cart_id_arr);
            M('cart')->delete($cart_id_str); // 删除购物车完全相同的商品
        }
   }
}