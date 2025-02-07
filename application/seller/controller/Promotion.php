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
 * 专题管理
 * Date: 2016-06-09
 */

namespace app\seller\controller;
 
use app\seller\logic\GoodsLogic;
use think\Page;

class Promotion extends Base
{

    public $store_id;

    public function __construct()
    {
        parent::__construct();
        $this->store_id = STORE_ID;
    }
    /**
     * 商品活动列表
     */
    public function prom_goods_list()
    {
        $parse_type = array('0' => '直接打折', '1' => '减价优惠', '2' => '固定金额出售', '3' => '买就赠优惠券');
        $level = M('user_level')->select();
        if ($level) {
            foreach ($level as $v) {
                $lv[$v['level_id']] = $v['level_name'];
            }
        }
        $this->assign("parse_type", $parse_type);

        $count = M('prom_goods')->where("store_id", $this->store_id)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $res = M('prom_goods')->where("store_id", $this->store_id)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        if ($res) {
            foreach ($res as $val) {
                if (!empty($val['group']) && !empty($lv)) {
                    $val['group'] = explode(',', $val['group']);
                    foreach ($val['group'] as $v) {
                        $val['group_name'] .= $lv[$v] . ',';
                    }
                }
                $val['state'] = empty($val['status']) ? '管理员关闭' : '正常';
                if ($val['end_time'] < time()) {
                    $val['state'] = '已结束';
                }
                $prom_list[] = $val;
            }
        }
        $this->assign('page', $show);// 赋值分页输出
        $this->assign('prom_list', $prom_list);
        return $this->fetch();
    }

    public function prom_goods_info()
    {
        $this->assign('min_date', date('Y-m-d'));
        $level = M('user_level')->select();
        $this->assign('level', $level);
        $prom_id = I('id/d');
        $info['start_time'] = date('Y-m-d');
        $info['end_time'] = date('Y-m-d', time() + 3600 * 60 * 24);
        if ($prom_id > 0) {
            $info = M('prom_goods')->where("id", $prom_id)->find();
            $info['start_time'] = date('Y-m-d', $info['start_time']);
            $info['end_time'] = date('Y-m-d', $info['end_time']);
            $prom_goods = M('goods')->where(['prom_id' => $prom_id, 'prom_type' => 3])->select();
            $this->assign('prom_goods', $prom_goods);
        }
        $this->assign('info', $info);
        $this->assign('min_date', date('Y-m-d'));
        $this->initEditor();
        return $this->fetch();
    }

    public function prom_goods_save()
    {
        $prom_id = I('id/d');
        $data = I('post.');
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time'] = strtotime($data['end_time']);
        $data['group'] = (empty($data['group'])) ? '' : implode(',', $data['group']);
        if ($prom_id) {
            M('prom_goods')->where(['id' => $prom_id, 'store_id' => $this->store_id])->save($data);
            $last_id = $prom_id;
            sellerLog("管理员修改了商品促销 " . I('name'));
        } else {
            $data['store_id'] = $this->store_id;
            $last_id = M('prom_goods')->add($data);
            sellerLog("管理员添加了商品促销 " . I('name'));
        }

        if (is_array($data['goods_id'])) {
            $goods_id = implode(',', $data['goods_id']);
            if ($prom_id > 0) {
                M("goods")->where(['prom_id' => $prom_id, 'prom_type' => 3, 'store_id' => $this->store_id])->save(array('prom_id' => 0, 'prom_type' => 0));
            }
            M("goods")->where("goods_id", "in", $goods_id)->save(array('prom_id' => $last_id, 'prom_type' => 3));
        }
        $this->success('编辑促销活动成功', U('Promotion/prom_goods_list'));
    }

    public function prom_goods_del()
    {
        $prom_id = I('id/d');
        $order_goods = M('order_goods')->where(['prom_type' => 3, 'prom_id' => $prom_id])->find();
        if (!empty($order_goods)) {
            $this->error("该活动有订单参与不能删除!");
        }
        M("goods")->where(['prom_id' => $prom_id, 'prom_type' => 3, 'store_id' => $this->store_id])->save(array('prom_id' => 0, 'prom_type' => 0));
        M('prom_goods')->where(['prom_id' => $prom_id, 'store_id' => $this->store_id])->delete();
        $this->success('删除活动成功', U('Promotion/prom_goods_list'));
    }


    /**
     * 订单活动列表
     */
    public function prom_order_list()
    {
        $parse_type = array('0' => '满额打折', '1' => '满额优惠金额', '2' => '满额送银币', '3' => '满额送优惠券');
        $count = M('prom_order')->where("store_id", $this->store_id)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $res = M('prom_order')->where("store_id", $this->store_id)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $level = M('user_level')->select();
        if ($level) {
            foreach ($level as $v) {
                $lv[$v['level_id']] = $v['level_name'];
            }
        }
        if ($res) {
            foreach ($res as $val) {
                if (!empty($val['group']) && !empty($lv)) {
                    $val['group'] = explode(',', $val['group']);
                    foreach ($val['group'] as $v) {
                        $val['group_name'] .= $lv[$v] . ',';
                    }
                }
                $val['state'] = empty($val['status']) ? '管理员关闭' : '正常';
                if ($val['end_time'] < time()) {
                    $val['state'] = '已结束';
                }
                $prom_list[] = $val;
            }
        }
        $this->assign('page', $show);// 赋值分页输出
        $this->assign("parse_type", $parse_type);
        $this->assign('prom_list', $prom_list);
        return $this->fetch();
    }

    public function prom_order_info()
    {
        $this->assign('min_date', date('Y-m-d'));
        $level = M('user_level')->select();
        $this->assign('level', $level);
        $prom_id = I('id/d');
        $info['start_time'] = date('Y-m-d');
        $info['end_time'] = date('Y-m-d', time() + 3600 * 24 * 60);
        if ($prom_id > 0) {
            $info = M('prom_order')->where("id", $prom_id)->find();
            $info['start_time'] = date('Y-m-d', $info['start_time']);
            $info['end_time'] = date('Y-m-d', $info['end_time']);
        }
        $this->assign('info', $info);
        $this->assign('min_date', date('Y-m-d'));
        $this->initEditor();
        return $this->fetch();
    }

    public function prom_order_save()
    {
        $prom_id = I('id/d');
        $data = I('post.');
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time'] = strtotime($data['end_time']);
        $data['group'] = implode(',', $data['group']);
        if ($prom_id) {
            M('prom_order')->where(['id' => $prom_id, 'store_id' => $this->store_id])->save($data);
            sellerLog("管理员修改了商品促销 " . I('name'));
        } else {
            $data['store_id'] = $this->store_id;
            M('prom_order')->add($data);
            sellerLog("管理员添加了商品促销 " . I('name'));
        }
        $this->success('编辑促销活动成功', U('Promotion/prom_order_list'));
    }

    public function prom_order_del()
    {
        $prom_id = I('id/d');
        $order = M('order')->where(['order_prom_id' => $prom_id, 'store_id' => $this->store_id])->find();
        if (!empty($order)) {
            $this->error("该活动有订单参与不能删除!");
        }

        M('prom_order')->where(['id' => $prom_id, 'store_id' => $this->store_id])->delete();
        $this->success('删除活动成功', U('Promotion/prom_order_list'));
    }

    public function group_buy_list()
    {
        $Ad = M('group_buy');
        $count = $Ad->where("store_id", $this->store_id)->count();
        $Page = new Page($count, 10);
        $res = $Ad->order('id desc')->where("store_id", $this->store_id)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        if ($res) {
            foreach ($res as $val) {
                $val['start_time'] = date('Y-m-d H:i', $val['start_time']);
                $val['end_time'] = date('Y-m-d H:i', $val['end_time']);
                $list[] = $val;
            }
        }
        $this->assign('list', $list);
        $this->assign('state', array('审核中', '正常', '未通过', '管理员关闭'));
        $show = $Page->show();
        $this->assign('page', $show);
        return $this->fetch();
    }

    //团购促销
    public function group_buy()
    {
        $act = I('GET.act', 'add');
        $groupbuy_id = I('get.id/d');
        $group_info = array();
        $group_info['start_time'] = date('Y-m-d');
        $group_info['end_time'] = date('Y-m-d', time() + 3600 * 365);
        if ($groupbuy_id) {
            $group_info = D('group_buy')->where('id', $groupbuy_id)->find();
            $group_info['start_time'] = date('Y-m-d H:i', $group_info['start_time']);
            $group_info['end_time'] = date('Y-m-d H:i', $group_info['end_time']);
            $act = 'edit';
        }
        $this->assign('min_date', date('Y-m-d'));
        $this->assign('info', $group_info);
        $this->assign('act', $act);
        return $this->fetch();
    }

    public function groupbuyHandle()
    {
        $data = I('post.');
        $data['groupbuy_intro'] = htmlspecialchars(stripslashes($_POST['groupbuy_intro']));
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time'] = strtotime($data['end_time']);
        if ($data['act'] == 'del') {
            $r = D('group_buy')->where(['id' => $data['id'], 'store_id' => $this->store_id])->delete();
            M('goods')->where(['prom_type' => 2, 'prom_id' => $data['id']])->save(array('prom_id' => 0, 'prom_type' => 0));
            if ($r) exit(json_encode(1));
        }
        if ($data['price'] >= $data['goods_price']) {
            $this->error('团购价格不能大于或等于原商品价格');
        } else {
            $data['rebate'] = number_format($data['price'] / $data['goods_price'] * 10, 1);
        }
        if ($data['act'] == 'add') {
            $data['store_id'] = $this->store_id;
            $r = D('group_buy')->add($data);
            M('goods')->where("goods_id", $data['goods_id'])->save(array('prom_id' => $r, 'prom_type' => 2));
        }
        if ($data['act'] == 'edit') {
            $r = D('group_buy')->where(['id' => $data['id'], 'store_id' => $this->store_id])->save($data);
            M('goods')->where(['prom_type' => 2, 'prom_id' => $data['id']])->save(array('prom_id' => 0, 'prom_type' => 0));
            M('goods')->where("goods_id", $data['goods_id'])->save(array('prom_id' => $data['id'], 'prom_type' => 2));
        }
        if ($r) {
            $this->success("操作成功", U('Promotion/group_buy_list'));
        } else {
            $this->error("操作失败", U('Promotion/group_buy_list'));
        }
    }

    public function get_goods()
    {
        $prom_id = I('id/d');
        $count = M('goods')->where(['prom_id' => $prom_id, 'prom_type' => 3, 'store_id' => $this->store_id])->count();
        $Page = new Page($count, 10);
        $goodsList = M('goods')->where(['prom_id' => $prom_id, 'prom_type' => 3, 'store_id' => $this->store_id])->order('goods_id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $show = $Page->show();
        $this->assign('page', $show);
        $this->assign('goodsList', $goodsList);
        return $this->fetch();
    }

    public function search_goods()
    {
        $GoodsLogic = new GoodsLogic;
        $brandList = $GoodsLogic->getSortBrands();
        $this->assign('brandList', $brandList);
        $categoryList = $GoodsLogic->getSortCategory();
        $this->assign('categoryList', $categoryList);
        $brand_id = I('brand_id/d');
        $keywords = I('keywords');
        /*
         * 可以用这两条sql 语句代替 
         * select group_concat(concat( class_1,',',class_2,',',class_3 )) from tp_store_bind_class group by store_id
         * select group_concat(concat_ws( ',',class_1,class_2,class_3 )) from tp_store_bind_class group by store_id         
         */
        $bind_class_id = array();
        $store_bind_class = M('store_bind_class')->where(['store_id' => STORE_ID, 'state' => 1])->select();
        foreach ($store_bind_class as $key => $val) {
            $bind_class_id[] = $val['class_1'];
            $bind_class_id[] = $val['class_2'];
            $bind_class_id[] = $val['class_3'];
        }
        $this->assign('bind_class_id', $bind_class_id);

        $goods_id = I('goods_id/d');
        $where = array('is_on_sale' => 1, 'prom_type' => 0, 'store_id' => $this->store_id);//搜索条件
        if (!empty($goods_id)) {
            $where['goods_id'] = array('not in', $goods_id);
        }
        I('intro') && $where[I('intro')] = 1;
        $cat_id = I('cat_id/d');
        if ($cat_id) {
            $this->assign('cat_id', $cat_id);
            $goods_category = M('goods_category')->where("id", $cat_id)->find();
            $where['cat_id' . $goods_category['level']] = $cat_id;
        }
        if ($brand_id) {
            $this->assign('brand_id', $brand_id);
            $where['brand_id'] = $brand_id;
        }
        if ($keywords) {
            $this->assign('keywords', $keywords);
            $where['goods_name|keywords'] = array('like', '%' . $keywords . '%');
        }
        $count = M('goods')->where($where)->count();
        $Page = new Page($count, 10);
        $goodsList = M('goods')->where($where)->order('goods_id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $show = $Page->show();//分页显示输出
        $this->assign('page', $show);//赋值分页输出
        $this->assign('goodsList', $goodsList);
        $tpl = I('get.tpl', 'search_goods');
        return $this->fetch($tpl);
    }

    //限时抢购
    public function flash_sale()
    {
        $model = M('flash_sale');
        $condition['store_id'] = $this->store_id;
        $count = $model->where($condition)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $prom_list = $model->where($condition)->order("id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('state', array('审核中', '正常', '已结束', '审核失败', '管理员关闭'));
        $this->assign('prom_list', $prom_list);
        //dump($prom_list);
        $this->assign('page', $show);// 赋值分页输出
        return $this->fetch();
    }

    public function flash_sale_info()
    {
        if (IS_POST) {
            $data = I('post.');
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
            if (empty($data['id'])) {
                $data['store_id'] = $this->store_id;
                $r = M('flash_sale')->add($data);
                M('goods')->where("goods_id", $data['goods_id'])->save(array('prom_id' => $r, 'prom_type' => 1));
                sellerLog("管理员添加抢购活动 " . $data['name']);
            } else {
                $r = M('flash_sale')->where(['id' => $data['id'], 'store_id' => $this->store_id])->save($data);
                M('goods')->where(['prom_type' => 1, 'prom_id' => $data['id']])->save(array('prom_id' => 0, 'prom_type' => 0));
                M('goods')->where("goods_id", $data['goods_id'])->save(array('prom_id' => $data['id'], 'prom_type' => 1));
            }
            if ($r) {
                $this->success('编辑抢购活动成功', U('Promotion/flash_sale'));
                exit;
            } else {
                $this->error('编辑抢购活动失败', U('Promotion/flash_sale'));
            }
        }
        $id = I('id/d');
        $info['start_time'] = date('Y-m-d H:i:s');
        $info['end_time'] = date('Y-m-d 23:59:59', time() + 3600 * 24 * 60);
        if ($id > 0) {
            $info = M('flash_sale')->where("id", $id)->find();
            $info['goods_price'] = M('goods')->where(array('goods_id' => $info['goods_id']))->getField('shop_price', false);
            $info['start_time'] = date('Y-m-d H:i', $info['start_time']);
            $info['end_time'] = date('Y-m-d H:i', $info['end_time']);
        }
        $this->assign('info', $info);
        $this->assign('min_date', date('Y-m-d'));
        return $this->fetch();
    }

    public function flash_sale_del()
    {
        $id = I('del_id/d');
        if ($id) {
            M('flash_sale')->where(['id' => $id, 'store_id' => $this->store_id])->delete();
            M('goods')->where(['prom_type' => 1, 'prom_id' => $id])->save(array('prom_id' => 0, 'prom_type' => 0));
            exit(json_encode(1));
        } else {
            exit(json_encode(0));
        }
    }

    private function initEditor()
    {
        $this->assign("URL_upload", U('Admin/Ueditor/imageUp', array('savepath' => 'promotion')));
        $this->assign("URL_fileUp", U('Admin/Ueditor/fileUp', array('savepath' => 'promotion')));
        $this->assign("URL_scrawlUp", U('Admin/Ueditor/scrawlUp', array('savepath' => 'promotion')));
        $this->assign("URL_getRemoteImage", U('Admin/Ueditor/getRemoteImage', array('savepath' => 'promotion')));
        $this->assign("URL_imageManager", U('Admin/Ueditor/imageManager', array('savepath' => 'promotion')));
        $this->assign("URL_imageUp", U('Admin/Ueditor/imageUp', array('savepath' => 'promotion')));
        $this->assign("URL_getMovie", U('Admin/Ueditor/getMovie', array('savepath' => 'promotion')));
        $this->assign("URL_Home", "");
    }

}