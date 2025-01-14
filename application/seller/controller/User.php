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
 * Author: 当燃
 * Date: 2015-09-09
 */

namespace app\seller\controller;

use think\AjaxPage;
use think\Db;
use think\Page;

class User extends Base
{
    /**
     * 账户资金调节
     */
    public function return_goods()
    {
        $desc = I('post.desc');
        $return_goods_id = I('return_goods_id/d');
        $return_goods = M('return_goods')->where(['id' => $return_goods_id, 'store_id' => STORE_ID])->find();
        empty($return_goods) && $this->error("参数有误");

        $user_id = $return_goods['user_id'];
        $order_goods = M('order_goods')->where(['order_id' => $return_goods['order_id'], 'goods_id' => $return_goods['goods_id'], 'spec_key' => $return_goods['spec_key']])->find();
        if ($order_goods['is_send'] != 1) {
            $is_send = array(0 => '未发货', 1 => '已发货', 2 => '已换货', 3 => '已退货');
            $this->error("商品状态为: {$is_send[$order_goods['is_send']]} 状态不能退款操作");
        }
        /*
                $order = M('order')->where("order_id = {$return_goods['order_id']}")->find();


                // 计算退回银币公式
                //  退款商品占总商品价比例 =  (退款商品价 * 退款商品数量)  / 订单商品总价      // 这里是算出 退款的商品价格占总订单的商品价格的比例 是多少
                //  退款银币 = 退款比例  * 订单使用银币

                // 退款价格的比例
                $return_price_ratio = ($order_goods['member_goods_price'] * $order_goods['goods_num']) / $order['goods_price'];
                // 退还银币 = 退款价格的比例 *
                $return_integral = ceil($return_price_ratio * $order['integral']);

                 // 退还金额 = (订单商品总价 - 优惠券 - 优惠活动) * 退款价格的比例 - (退还银币 + 当前商品送出去的银币) / 银币换算比例
                 // 因为银币已经退过了, 所以退金额时应该把银币对应金额推掉 其次购买当前商品时送出的银币也要退回来,以免被刷银币情况

                $return_goods_price = ($order['goods_price'] - $order['coupon_price'] - $order['order_prom_amount']) * $return_price_ratio - ($return_integral + $order_goods['give_integral']) /  tpCache('shopping.point_rate');
                $return_goods_price = round($return_goods_price,2); // 保留两位小数
         */

        $refund = order_settlement($return_goods['order_id'], $order_goods['rec_id']);  // 查看退款金额
        //  print_r($refund);
        $return_goods_price = $refund['refund_settlement'] ? $refund['refund_settlement'] : 0; // 这个商品的退款金额
        //$refund_integral = $refund['refund_integral'] ? ($refund['refund_integral'] * -1) : 0; // 这个商品的退银币
        $refund_integral = $refund['refund_integral'] - $refund['give_integral'];


        if (IS_POST) {
            if (!$desc)
                $this->error("请填写操作说明");
            if (!$user_id > 0)
                $this->error("参数有误");

//            $pending_money = M('store')->where(" store_id = ".STORE_ID)->getField('pending_money'); // 商家在未结算资金 
//            if($pending_money < $return_goods_price)
//                $this->error("你的未结算资金不足 ￥{$return_goods_price}");

            //     M('store')->where(" store_id = ".STORE_ID)->setDec('pending_money',$user_money); // 从商家的 未结算自己里面扣除金额
            $result = storeAccountLog(STORE_ID, 0, $return_goods_price * -1, $desc, $return_goods['order_id']);
            if ($result) {
                accountLog($user_id, $return_goods_price, $refund_integral, '订单退货', 0, $return_goods['order_id']);
            } else {
                $this->error("操作失败");
            }
            M('order_goods')->where("rec_id", $order_goods['rec_id'])->save(array('is_send' => 3));//更改商品状态
            // 如果一笔订单中 有退货情况, 整个分销也取消                      
            M('rebate_log')->where("order_id", $return_goods['order_id'])->save(array('status' => 4, 'remark' => '订单有退货取消分成'));

            $this->success("操作成功", U("Order/return_list"));
            exit;
        }

        $this->assign('return_goods_price', $return_goods_price);
        $this->assign('return_integral', $refund_integral);
        $this->assign('order_goods', $order_goods);
        $this->assign('user_id', $user_id);
        return $this->fetch();
    }
    /**
     *
     * @time 2017/03/23
     * @author dyr
     * 商家发送站内信
     */
    public function sendMessage()
    {
        $user_id_array = I('get.user_id_array');
        $users = array();
        if (!empty($user_id_array)) {
            $users = M('users')->field('user_id,nickname')->where(array('user_id' => array('IN', $user_id_array)))->select();
        }
        $this->assign('users', $users);
        return $this->fetch();
    }
    /**
     * 商家发送活动消息
     * @author dyr
     * @time  2017/03/23
     */
    public function doSendMessage()
    {
        $call_back = I('call_back');//回调方法
        $message = I('post.text');//内容
        $seller_id = session('seller_id');
        $users = I('post.user/a');//个体id
        $message = array(
            'seller_id' => $seller_id,
            'message'   => $message,
            'category'  => 1,//活动消息
            'send_time' => time(),
            'type'      =>0,//个体消息
        );
        if (!empty($users)) {
            $create_message_id = Db::name('Message')->insertGetId($message);
            foreach ($users as $key) {
                Db::name('user_message')->insert(['user_id' => $key, 'message_id' => $create_message_id, 'status' => 0, 'category' => 1]);
            }
        }
        echo "<script>parent.{$call_back}(1);</script>";
        exit();
    }
}