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
 
use think\Verify;
use think\Db;
use app\home\logic\StoreLogic;

class Index extends Base
{


    public function index()
    {
        // 如果是手机跳转到 手机模块
        if (true == isMobile()) {
            header("Location: " . U('Mobile/Index/index'));
            exit;
        }

        $hot_goods = $hot_cate = $cateList = array();
        $sql = "select a.goods_name,a.goods_id,a.shop_price,a.market_price,a.cat_id1,b.parent_id_path,b.name from __PREFIX__goods as a left join ";
        $sql .= " __PREFIX__goods_category as b on a.cat_id1=b.id where a.is_hot=1 and a.is_on_sale=1 and a.goods_state = 1 order by a.sort";//二级分类下热卖商品       
        $index_hot_goods = Db::query($sql);//首页热卖商品
        if ($index_hot_goods) {
            foreach ($index_hot_goods as $val) {
                $cat_path = explode('_', $val['parent_id_path']);
                $hot_goods[$cat_path[1]][] = $val;
            }
        }

        $hot_category = M('goods_category')->where("is_hot=1 and level=3 and is_show=1")->select();//热门三级分类
        foreach ($hot_category as $v) {
            $cat_path = explode('_', $v['parent_id_path']);
            $hot_cate[$cat_path[1]][] = $v;
        }

        foreach ($this->cateTrre as $k => $v) {
            if ($v['is_hot'] == 1) {
                $v['hot_goods'] = empty($hot_goods[$k]) ? '' : $hot_goods[$k];
                $v['hot_cate'] = empty($hot_cate[$k]) ? '' : $hot_cate[$k];
                $cateList[] = $v;
            }
        }

        $this->assign('cateList', $cateList);
        return $this->fetch();
    }

    /**
     *  公告详情页
     */
    public function notice()
    {
        return $this->fetch();
    }

    // 二维码
    public function qr_code()
    {
        // 导入Vendor类库包 Library/Vendor/Zend/Server.class.php
        //http://www.tp-shop.cn/Home/Index/erweima/data/www.99soubao.com
        vendor('phpqrcode.phpqrcode');
        //import('Vendor.phpqrcode.phpqrcode');
        error_reporting(E_ERROR);
        $url = urldecode($_GET["data"]);
        \QRcode::png($url);
        exit;
    }

    // 验证码
    public function verify()
    {
        //验证码类型
        $type = I('get.type') ? I('get.type') : '';
        $fontSize = I('get.fontSize') ? I('get.fontSize') : '40';
        $length = I('get.length') ? I('get.length') : '4';

        $config = array(
            'fontSize' => $fontSize,
            'length' => $length,
            'useCurve' => true,
            'useNoise' => false,
        );
        $Verify = new Verify($config);
        $Verify->entry($type);
    }


    /**
     * 店铺街
     * @author dyr
     * @time 2016/08/26
     */
    public function street()
    {
        $sc_id = I('get.sc_id/d');
        $province = I('get.province', 0);
        $city = I('get.city', 0);
        $order = I('order', 0);
        if (empty($province) && empty($city)) {
            //header("Content-type:text/html;charset=utf-8");
            $address = GetIpLookup();
            if (!empty($address)) {
                $address = M('region')->where(['level' => 2, 'name' => ['like', '%' . $address['city'] . '%']])->find();
                $province = $address['parent_id'];
                $city = $address['id'];
                $location = U('street', array('province' => $province, 'city' => $city));
                $address && header("Location: $location");// 根据城市来帅选               
            }
        }
        $store_class = M('store_class')->field('sc_id,sc_name')->where('')->select();
        $store_logic = new StoreLogic();
        $store_list = $store_logic->getStoreList($sc_id, $province, $city, $order, 10);
        $region = M('region')->where("`level` = 1")->getField("id,name");
        $this->assign('province', $province);
        $this->assign('city', $city);
        $this->assign('region', $region);
        $this->assign('page', $store_list['show']);// 赋值分页输出
        $this->assign('pages', $store_list['pages']);
        $this->assign('store_list', $store_list['result']);
        $this->assign('store_class', $store_class);//店铺分类
        return $this->fetch();
    }

    public function store_qrcode()
    {
        require_once 'vendor/phpqrcode/phpqrcode.php';

        error_reporting(E_ERROR);
        $store_id = I('store_id/d', 1);
        \QRcode::png(U('Mobile/Store/index', array('store_id' => $store_id), true, true));
    }

    function truncate_tables()
    {
        $tables = DB::query("show tables");
        //print_r($tables);
        $table = array('tp_admin', 'tp_config', 'tp_region', 'tp_system_module', 'tp_admin_role', 'tp_system_menu', 'tp_store_grade', 'tp_article_cat','tp_wx_user');
        foreach ($tables as $key => $val) {
//            if(!in_array($val['Tables_in_tpshop_bbc'], $table))                             
//                echo "truncate table ".$val['Tables_in_tpshop_bbc'].' ; ';
//                echo "<br/>";         
        }
        // 清空完之后 执行下面两个sql 插入自营店 和 用户
        // insert  into `tp_store`(`store_id`,`store_name`,`grade_id`,`user_id`,`user_name`,`seller_name`,`sc_id`,`company_name`,`province_id`,`city_id`,`district`,`store_address`,`store_zip`,`store_state`,`store_close_info`,`store_sort`,`store_rebate_paytime`,`store_time`,`store_end_time`,`store_logo`,`store_banner`,`store_avatar`,`seo_keywords`,`seo_description`,`store_aliwangwang`,`store_qq`,`store_phone`,`store_zy`,`store_domain`,`store_recommend`,`store_theme`,`store_credit`,`store_desccredit`,`store_servicecredit`,`store_deliverycredit`,`store_collect`,`store_slide`,`store_slide_url`,`store_printdesc`,`store_sales`,`store_presales`,`store_aftersales`,`store_workingtime`,`store_free_price`,`store_warning_storage`,`store_decoration_switch`,`store_decoration_only`,`is_own_shop`,`bind_all_gc`,`qitian`,`certified`,`returned`,`store_free_time`,`mb_slide`,`mb_slide_url`,`deliver_region`,`cod`,`two_hour`,`ensure`,`deposit`,`deposit_icon`,`store_money`,`pending_money`,`deleted`,`goods_examine`,`service_phone`) values (1,'TPSHP旗舰店',1,1,'wyp001','wyp002',2,'聊城博商网络有限公司',28240,28626,28646,'上梅林中康创业园7楼735','',1,NULL,0,'1463979921',0,NULL,'/public/upload/seller/2017/02-16/58a54113dcda5.jpg','/public/upload/seller/2017/02-16/58a547417999b.png','/public/upload/seller/2017/02-16/58a54a810aa75.png','','','','','','女装,桑德菲杰',NULL,0,'style3',0,4.83,4.67,4.83,1,'/public/upload/seller/2017/01-07/58707ff4e3c3c.jpg,/public/upload/seller/2016/10-28/5812acda24797.png,/public/upload/seller/2016/10-28/5812acdee9132.png,,','http://,http://,http://,http://,http://',NULL,0,NULL,NULL,'工作时间: 周一到周日10:00~23:00',1000.00,100,0,0,1,1,0,0,0,NULL,'/public/upload/seller/2017/01-04/586c92a1d7ff6.jpg,/public/upload/seller/2017/01-04/586c930207fdd.png,,,','http://,http://,http://,http://,http://','8 131 1700|黑龙江 齐齐哈尔市 富拉尔基区',0,0,0,0.00,0,31220.68,-532.35,0,0,'15889560679');
        // insert into `tp_users` (`user_id`, `email`, `password`, `sex`, `birthday`, `user_money`, `frozen_money`, `distribut_money`, `pay_points`, `paypwd`, `reg_time`, `last_login`, `last_ip`, `qq`, `mobile`, `mobile_validated`, `oauth`, `openid`, `unionid`, `head_pic`, `bank_name`, `bank_card`, `realname`, `idcard`, `email_validated`, `nickname`, `level`, `discount`, `total_amount`, `is_lock`, `is_distribut`, `first_leader`, `second_leader`, `third_leader`, `token`) values(62, '511482696@qq.com', '519475228fe35ad067744465c42a19b2', 2, 1480521600, '0.00', '0.00', '1000.00', 1288, '1', 1245048540, 1484277061, '0.0.0.0', '', '13800138006', 1, '', null, null, 'http://imgsrc.baidu.com/forum/pic/item/e4dde71190ef76c6eae6f0fb9d16fdfaaf51672d.jpg', '0', '0', '0', null, 1, '测试人员', 3, '0.95', '43574.91', 0, 1, 2, 5, 13, 'cc8e38f888d9ff221674830adf718049');
    }

    /**
     * 猜你喜欢
     * @author dyr
     */
    public function ajax_favorite()
    {
        $p = I('p', 1);
        $item = I('i', 5);
        $tpl = I('tpl');
        $goods_where = array('g.is_recommend' => 1, 'g.is_on_sale' => 1, 'g.goods_state' => 1);
        $favourite_goods = M('goods')->alias('g')->join('__STORE__ s' ,'g.store_id = s.store_id')
            ->field('g.*,s.store_name')
            ->where($goods_where)
            ->order('sort DESC')
            ->page($p, $item)
            ->cache(true, TPSHOP_CACHE_TIME)
            ->select();
        $this->assign('favourite_goods', $favourite_goods);
        if ($tpl) {
            return $this->fetch($tpl);
        } else {
            return $this->fetch();
        }
    }
}