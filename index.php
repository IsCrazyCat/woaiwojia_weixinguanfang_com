<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
// 应用入口文件
//if (extension_loaded('zlib')){
//    ob_end_clean();
//    ob_start('ob_gzhandler');
//}
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.4.0','<'))  die('require PHP > 5.4.0 !');
//error_reporting(E_ALL ^ E_NOTICE);//显示除去 E_NOTICE 之外的所有错误信息
error_reporting(E_ERROR | E_WARNING | E_PARSE);//报告运行时错误

//检测是否已安装系统
//if(file_exists("./install/") && !file_exists("./install/install.lock")){
//	if($_SERVER['PHP_SELF'] != '/index.php'){
//		header("Content-type: text/html; charset=utf-8");
//		exit("请在域名根目录下安装,如:<br/> www.xxx.com/index.php 正确 <br/>  www.xxx.com/www/index.php 错误,域名后面不能圈套目录, 但项目没有根目录存放限制,可以放在任意目录,apache虚拟主机配置一下即可");
//	}
//	header('Location:/install/index.php');
//	exit();
//}

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
//define('APP_DEBUG',false); debug tp5 里面已经改为 config.php 里面
// 定义应用目录
//define('APP_PATH','./Application/');
//  定义插件目录
define('PLUGIN_PATH', __DIR__ . '/plugins/');
define('UPLOAD_PATH','public/upload/'); // 编辑器图片上传路径
define('TPSHOP_CACHE_TIME',31104000); //  缓存时间  31104000
define('SITE_URL','http://'.$_SERVER['HTTP_HOST']); // 网站域名
//define('HTML_PATH','./Application/Runtime/Html/'); //静态缓存文件目录，HTML_PATH可任意设置，此处设为当前项目下新建的html目录
define('INSTALL_DATE',1463741583);
define('SERIALNUMBER','20160520065303oCWIoa');
// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');
// 定义时间
define('NOW_TIME',$_SERVER['REQUEST_TIME']);
// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';
