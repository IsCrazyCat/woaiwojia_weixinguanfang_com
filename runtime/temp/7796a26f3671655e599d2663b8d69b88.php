<?php if (!defined('THINK_PATH')) exit(); /*a:6:{s:42:"./template/mobile/new/distribut\index.html";i:1563011439;s:40:"./template/mobile/new/public\header.html";i:1566353273;s:44:"./template/mobile/new/public\uer_topnav.html";i:1491382656;s:40:"./template/mobile/new/public\footer.html";i:1562732638;s:44:"./template/mobile/new/public\footer_nav.html";i:1565843635;s:42:"./template/mobile/new/public\wx_share.html";i:1491382656;}*/ ?>
<!DOCTYPE html >
<html>
<head>
<meta name="Generator" content="TPSHOP v1.1" />
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
<meta name="format-detection" content="telephone=no" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-touch-fullscreen" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="applicable-device" content="mobile">
<title><?php echo $tpshop_config['shop_info_store_title']; ?></title>
<meta http-equiv="keywords" content="<?php echo $tpshop_config['shop_info_store_keyword']; ?>" />
<meta name="description" content="<?php echo $tpshop_config['shop_info_store_desc']; ?>" />
<meta name="Keywords" content="触屏版   手机版" />
<meta name="Description" content="触屏版    "/>
<link rel="stylesheet" href="__STATIC__/css/public.css">
<link rel="stylesheet" href="__STATIC__/css/user.css">
<script type="text/javascript" src="__STATIC__/js/jquery.js"></script>
<script src="__PUBLIC__/js/global.js"></script>
<script src="__PUBLIC__/js/mobile_common.js"></script>
<script type="text/javascript" src="__STATIC__/js/modernizr.js"></script>
<script type="text/javascript" src="__STATIC__/js/layer.js" ></script>

</head>

<body>      
<div id="tbh5v0">
<div class="user_com">
<div class="com_top">
	<h2><a href="<?php echo U('Mobile/User/userinfo'); ?>">设置</a></h2>
	<p class="tuij_cas">
	        <?php echo $user['nickname']; if($first_nickname != ''): ?>
            	<br />
            	<span>由( <?php echo $first_nickname; ?> )推荐</span>
            <?php endif; ?>
    </p>
	<dl>
		<dt>
		<img src="<?php echo (isset($user['head_pic']) && ($user['head_pic'] !== '')?$user['head_pic']:" __STATIC__/images/user68.jpg"); ?>">
        
		<dd><?php echo $level_name; ?></dd>
		</dt>
	</dl>
</div>
<div class="uer_topnav">
	<ul>
		<li class="bain"><a href="<?php echo U('Mobile/User/order_list'); ?>" ><span><?php echo $order_count; ?></span>我的订单</a></li>
		<li class="bain"><a href="<?php echo U('Mobile/User/collect_list'); ?>"><span><?php echo $goods_collect_count; ?></span>我的收藏</a></li>
		<li><a href="<?php echo U('Mobile/User/comment'); ?>"><span><?php echo $comment_count; ?></span>我的评价</a></li>
	</ul>
</div>

<div class="Wallet">
	<ul>
	<li class="bain1 sell_cost"><strong>￥<?php echo $sales_volume; ?>元</strong><span>销售额</span></li>
	<li class="sell_cost"><strong>￥<?php echo $reward; ?>元</strong><span>我的奖励</span></li>
	</ul>
	
</div>

<div class="Wallet">
	<div class="ss_million">
		<a class="dj_mill"><em class="Icon j_million"></em><dl class="b"><dt>我的分销</dt><dd><span class="million_num"><?php echo $lower_count[1]+$lower_count[2]+$lower_count[3]; ?>人</span></dd></dl></a>
        <div class="show_million">
            <a href="<?php echo U('Distribut/lower_list',array('level'=>1)); ?>"><em class="Icon"></em><dl class="b"><dt>一级分销</dt><dd style="color:#aaaaaa;"><span><?php echo $lower_count[1]; ?>人</span></dd></dl></a>
            <a href="<?php echo U('Distribut/lower_list',array('level'=>2)); ?>"><em class="Icon"></em><dl class="b"><dt>二级分销</dt><dd style="color:#aaaaaa;"><span><?php echo $lower_count[2]; ?>人</span></dd></dl></a>
         <!--   <a href="<?php echo U('Distribut/lower_list',array('level'=>3)); ?>"><em class="Icon"></em><dl class="b"><dt>三级分销</dt><dd style="color:#aaaaaa;"><span><?php echo $lower_count[3]; ?>人</span></dd></dl></a>-->
        </div>
    </div>
    <div class="ss_million">
		<a class="dj_mill">
              <em class="Icon j_million"></em>
              <dl class="b">
                  <dt>我的推广</dt>
                  <dd>
                      <span class="million_num">
	                      <?php echo $level_order[0]['c']+$level_order[1]['c']+$level_order[2]['c']+$level_order[3]['c']+$level_order[4]['c']; ?>单
                          （￥<?php echo $level_order[0]['goods_price']+$level_order[1]['goods_price']+$level_order[2]['goods_price']+$level_order[3]['goods_price']; ?>）
                      </span>
                  </dd>
              </dl>
        </a>
        <div class="show_million">
            <a href="<?php echo U('Distribut/order_list',array('status'=>'0')); ?>"><em class="Icon"></em><dl class="b"><dt>下单未购买</dt><dd style="color:#aaaaaa;"><span><?php echo $level_order[0]['c']+$level_order[4]['c']; ?>单（￥<?php echo $level_order[1]['goods_price']+$level_order[2]['goods_price']; ?>）</span></dd></dl></a>
            <a href="<?php echo U('Distribut/order_list',array('status'=>'1,2,3')); ?>"><em class="Icon"></em><dl class="b"><dt>下单已购买</dt><dd style="color:#aaaaaa;"><span><?php echo $level_order[1]['c']+$level_order[2]['c']+$level_order[3]['c']; ?>单（￥<?php echo $level_order[1]['goods_price']+$level_order[2]['goods_price']+$level_order[3]['goods_price']; ?>）</span></dd></dl></a>
        </div>
    </div> 
    <div class="ss_million">
		<a class="dj_mill"><em class="Icon j_million"></em><dl class="b"><dt>我的财富</dt><dd><span class="million_num">￥<?php echo $level_order[0]['goods_price']+$level_order[1]['goods_price']+$level_order[2]['goods_price']+$level_order[3]['goods_price']; ?></span></dd></dl></a>
        <div class="show_million">
            <a href="<?php echo U('Distribut/order_list',array('status'=>'0')); ?>"><em class="Icon"></em><dl class="b"><dt>未付款订单财富</dt><dd style="color:#aaaaaa;"><span>￥<?php echo $level_order[0]['goods_price']; ?></span></dd></dl></a>
            <a href="<?php echo U('Distribut/order_list',array('status'=>'1')); ?>"><em class="Icon"></em><dl class="b"><dt>已付款订单财富</dt><dd style="color:#aaaaaa;"><span>￥<?php echo $level_order[1]['goods_price']; ?></span></dd></dl></a>
            <a href="<?php echo U('Distribut/order_list',array('status'=>'2')); ?>"><em class="Icon"></em><dl class="b"><dt>已收货订单财富</dt><dd style="color:#aaaaaa;"><span>￥<?php echo $level_order[2]['goods_price']; ?></span></dd></dl></a>
            <a href="<?php echo U('Distribut/order_list',array('status'=>'3')); ?>"><em class="Icon"></em><dl class="b"><dt>已分成订单财富</dt><dd style="color:#aaaaaa;"><span>￥<?php echo $level_order[3]['goods_price']; ?></span></dd></dl></a>
            <a href="javascript:void(0);"><em class="Icon"></em><dl class="b"><dt>已提现财富</dt><dd style="color:#aaaaaa;"><span>￥<?php echo (isset($withdrawals_money) && ($withdrawals_money !== '')?$withdrawals_money:"0"); ?></span></dd></dl></a>
            <a href="javascript:void(0);"><em class="Icon"></em><dl class="b"><dt>可提现财富</dt><dd style="color:#aaaaaa;"><span>￥<?php echo (isset($user['user_money']) && ($user['user_money'] !== '')?$user['user_money']:"0"); ?></span></dd></dl></a>
        </div>
    </div>
    <div>
		<!--<a href="<?php echo U('Distribut/withdrawals'); ?>"><em class="Icon j_million"></em><dl class="b"><dt>申请提现</dt><dd>&nbsp;</dd></dl></a>-->
                <a href="<?php echo U('Distribut/qr_code'); ?>"><em class="Icon j_million"></em><dl class="b"><dt>我的推广二维码</dt><dd>&nbsp;</dd></dl></a>
    </div>
</div>


</div>
<!--
<div class="footer" >
	      <div class="links"  id="TP_MEMBERZONE"> 
	      		<?php if($user_id > 0): ?>
	      		<a href="<?php echo U('User/index'); ?>"><span><?php echo $user['nickname']; ?></span></a><a href="<?php echo U('User/logout'); ?>"><span>退出</span></a>
	      		<?php else: ?>
	      		<a href="<?php echo U('User/login'); ?>"><span>登录</span></a><a href="<?php echo U('User/reg'); ?>"><span>注册</span></a>
	      		<?php endif; ?>
	      		<a href="#"><span>反馈</span></a><a href="javascript:window.scrollTo(0,0);"><span>回顶部</span></a>
		  </div>
	      <ul class="linkss" >
		      <li>
		        <a href="#">
		        <i class="footerimg_1"></i>
		        <span>客户端</span></a></li>
		      <li>
		      <a href="javascript:;"><i class="footerimg_2"></i><span class="gl">触屏版</span></a></li>
		      <li><a href="<?php echo U('Home/Index/index'); ?>" class="goDesktop"><i class="footerimg_3"></i><span>电脑版</span></a></li>
	      </ul>
	  	 <p class="mf_o4">备案号:<?php echo $tpshop_config['shop_info_record_no']; ?><br/>&copy; 2005-2016 多商户V1.2 版权所有，并保留所有权利。</p>
</div>
-->
</div>
<div style="height:50px; line-height:50px; clear:both;"></div>
<div class="v_nav">
	<div class="vf_nav">
		<ul>
			<li> <a href="<?php echo U('Index/index'); ?>">
			    <i class="vf_1"></i>
			    <span>首页</span></a></li>
			<li><a href="tel:<?php echo $tpshop_config['shop_info_phone']; ?>">
			<!--<li><a href="https://chat.mqimg.com/dist/standalone.html?eid=161660">-->

			    <i class="vf_2"></i>
			    <span>客服</span></a></li>
			<li><a href="<?php echo U('Goods/categoryList'); ?>">
			    <i class="vf_3"></i>
			    <span>分类</span></a></li>
			<li>
			<a href="<?php echo U('Cart/cart'); ?>">
			   <em class="global-nav__nav-shop-cart-num" id="cart_quantity" style="right:9px;"></em>
			   <i class="vf_4"></i>
			   <span>购物车</span>
			   </a>
			</li>
			<li><a href="<?php echo U('User/index'); ?>">
			    <i class="vf_5"></i>
			    <span>我的</span></a>
			</li>
		</ul>
	</div>
</div>



<script type='text/javascript'>
    (function(m, ei, q, i, a, j, s) {
        m[i] = m[i] || function() {
            (m[i].a = m[i].a || []).push(arguments)
        };
        j = ei.createElement(q),
            s = ei.getElementsByTagName(q)[0];
        j.async = true;
        j.charset = 'UTF-8';
        j.src = 'https://static.meiqia.com/dist/meiqia.js?_=t';
        s.parentNode.insertBefore(j, s);
    })(window, document, 'script', '_MEIQIA');
    _MEIQIA('entId', 161660);
</script>











<script type="text/javascript">
$(document).ready(function(){
	  var cart_cn = getCookie('cn');
	  if(cart_cn == ''){
		$.ajax({
			type : "GET",
			url:"/index.php?m=Home&c=Cart&a=header_cart_list",//+tab,
			success: function(data){								 
				cart_cn = getCookie('cn');
				$('#cart_quantity').html(cart_cn);						
			}
		});	
	  }
	  $('#cart_quantity').html(cart_cn);
});
</script>
<!-- 微信浏览器 调用微信 分享js-->
<script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script type="text/javascript">

<?php if(ACTION_NAME == 'goodsInfo'): ?>
   var ShareLink = "http://<?php echo $_SERVER[HTTP_HOST]; ?>/index.php?m=Mobile&c=Goods&a=goodsInfo&id=<?php echo $goods[goods_id]; ?>"; //默认分享链接
   var ShareImgUrl = "http://<?php echo $_SERVER[HTTP_HOST]; ?><?php echo goods_thum_images($goods[goods_id],400,400); ?>"; // 分享图标
<?php else: ?>
   var ShareLink = "http://<?php echo $_SERVER[HTTP_HOST]; ?>/index.php?m=Mobile&c=Index&a=index"; //默认分享链接
   var ShareImgUrl = "http://<?php echo $_SERVER[HTTP_HOST]; ?><?php echo $tpshop_config['shop_info_store_logo']; ?>"; //分享图标
<?php endif; ?>

var is_distribut = getCookie('is_distribut'); // 是否分销代理
var user_id = getCookie('user_id'); // 当前用户id
//alert(is_distribut+'=='+user_id);
// 如果已经登录了, 并且是分销商
if(parseInt(is_distribut) == 1 && parseInt(user_id) > 0)
{									
	ShareLink = ShareLink + "&first_leader="+user_id;									
}	

$(function() {
	if(isWeiXin() && parseInt(user_id)>0 ||1){
		$.ajax({
			type : "POST",
			url:"/index.php?m=Mobile&c=Index&a=ajaxGetWxConfig&t="+Math.random(),
			data:{'askUrl':encodeURIComponent(location.href.split('#')[0])},		
			dataType:'JSON',
			success: function(res)
			{
				//微信配置
				wx.config({
				    debug: false, 
				    appId: res.appId,
				    timestamp: res.timestamp, 
				    nonceStr: res.nonceStr, 
				    signature: res.signature,
				    jsApiList: ['onMenuShareTimeline', 'onMenuShareAppMessage','onMenuShareQQ','onMenuShareQZone','hideOptionMenu'] // 功能列表，我们要使用JS-SDK的什么功能
				});
			},
			error:function(){
				return false;
			}
		}); 

		// config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在 页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready 函数中。
		wx.ready(function(){
		    // 获取"分享到朋友圈"按钮点击状态及自定义分享内容接口
		    wx.onMenuShareTimeline({
		        title: "<?php echo $tpshop_config['shop_info_store_title']; ?>", // 分享标题
		        link:ShareLink,
		        imgUrl:ShareImgUrl // 分享图标
		    });

		    // 获取"分享给朋友"按钮点击状态及自定义分享内容接口
		    wx.onMenuShareAppMessage({
		        title: "<?php echo $tpshop_config['shop_info_store_title']; ?>", // 分享标题
		        desc: "<?php echo $tpshop_config['shop_info_store_desc']; ?>", // 分享描述
		        link:ShareLink,
		        imgUrl:ShareImgUrl // 分享图标
		    });
			// 分享到QQ
			wx.onMenuShareQQ({
		        title: "<?php echo $tpshop_config['shop_info_store_title']; ?>", // 分享标题
		        desc: "<?php echo $tpshop_config['shop_info_store_desc']; ?>", // 分享描述
		        link:ShareLink,
		        imgUrl:ShareImgUrl // 分享图标
			});	
			// 分享到QQ空间
			wx.onMenuShareQZone({
		        title: "<?php echo $tpshop_config['shop_info_store_title']; ?>", // 分享标题
		        desc: "<?php echo $tpshop_config['shop_info_store_desc']; ?>", // 分享描述
		        link:ShareLink,
		        imgUrl:ShareImgUrl // 分享图标
			});

		   <?php if(CONTROLLER_NAME == 'User'): ?> 
				wx.hideOptionMenu();  // 用户中心 隐藏微信菜单
		   <?php endif; ?>	
		});
	}
});

function isWeiXin(){
    var ua = window.navigator.userAgent.toLowerCase();
    if(ua.match(/MicroMessenger/i) == 'micromessenger'){
        return true;
    }else{
        return false;
    }
}
</script>
<!--微信关注提醒 start-->
<?php if(\think\Session::get('subscribe') == 0 AND $wechat_config['qr'] != ''): ?>
<button class="guide" onclick="follow_wx()">关注公众号</button>
<style type="text/css">
.guide{width:20px;height:100px;text-align: center;border-radius: 8px ;font-size:12px;padding:8px 0;border:1px solid #adadab;color:#000000;background-color: #fff;position: fixed;right: 6px;bottom: 200px;}
#cover{display:none;position:absolute;left:0;top:0;z-index:18888;background-color:#000000;opacity:0.7;}
#guide{display:none;position:absolute;top:5px;z-index:19999;}
#guide img{width: 70%;height: auto;display: block;margin: 0 auto;margin-top: 10px;}
</style>
<script type="text/javascript">
  // 关注微信公众号二维码	 
function follow_wx()
{
	layer.open({
		type : 1,  
		title: '关注公众号',
		content: '<img src="<?php echo $wechat_config['qr']; ?>" width="200">',
		style: ''
	});
}
</script> 
<?php endif; ?>
<!--微信关注提醒  end-->
<!-- 微信浏览器 调用微信 分享js  end-->
</body>
	<script type="text/javascript">
	
    	$(document).ready(function() { 
			    // 收缩展开节点
				$('.dj_mill').click(function(){
					 $(this).siblings('.show_million').toggle(300);
				});
        });
    </script>
</html>