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
 
class Article extends Base
{

    public function index()
    {
        $article_id = I('article_id/d', 38);
        $article = D('article')->where("article_id", $article_id)->find();
        $this->assign('article', $article);
        return $this->fetch();
    }

    /**
     * 文章内列表页
     */
    public function articleList()
    {
        $article_cat = M('ArticleCat')->where("parent_id  = 0")->select();
        $this->assign('article_cat', $article_cat);
        return $this->fetch();
    }

    /**
     * 文章内容页
     */
    public function detail()
    {
        $article_id = I('article_id/d', 1);
        $article = D('article')->where("article_id", $article_id)->find();
        if ($article) {
            $parent = D('article_cat')->where("cat_id", $article['cat_id'])->find();
            $this->assign('cat_name', $parent['cat_name']);
            $this->assign('article', $article);
        }
        return $this->fetch();
    }

}