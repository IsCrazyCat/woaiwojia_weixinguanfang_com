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
 * Date: 2016-06-09
 */


namespace app\seller\logic;

use think\Model;

class StoreLogic extends Model
{    
    
    /**
     * 获取指定店铺信息
     * @param $uid int 用户UID
     * @param bool $relation 是否关联查询
     *
     * @return mixed 找到返回数组
     */
    public function detail($store_id, $relation = true)
    {
        $user = D('Store')->where(array('store_id' => $store_id))->relation($relation)->find();
        return $user;
    }
  
}