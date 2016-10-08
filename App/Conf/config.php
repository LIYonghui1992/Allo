<?php

$database = require (RUNTIME_PATH.'config.php'); //数据库配置

$sys_config =  include DATA_PATH.  'sys.config.php'; //系统配置

if(empty($sys_config)){$sys_config=array();$sys_config['LAYOUT_ON']=1;}

if($sys_config['URL_MODEL']) $RULES = include DATA_PATH.  'Routes.php'; //URL路由规则


$config	= array(

		'DEFAULT_THEME'	=> 'Default',

		'DEFAULT_CHARSET' => 'utf-8',

		'APP_GROUP_LIST' => 'Home,Admin',

		'DEFAULT_GROUP' =>'Home',

		'TMPL_FILE_DEPR' => '_',

		'DB_FIELDS_CACHE' => false,

		'DB_FIELDTYPE_CHECK' => true,

		'URL_ROUTER_ON' => true,

		'LANG_SWITCH_ON'=> true,

		'TAGLIB_LOAD' => true,

		'TAGLIB_PRE_LOAD' => 'Yp',

		'TMPL_ACTION_ERROR' => APP_PATH.'/Tpl/Home/Default/Public/success.html',

		'TMPL_ACTION_SUCCESS' =>  APP_PATH.'/Tpl/Home/Default/Public/success.html',

		'COOKIE_PREFIX'=>'allocacoc_',

		'COOKIE_EXPIRE'=>'',

		'COOKIE_TIME'=>3600,//新加

		'VAR_PAGE' => 'p',

		'LAYOUT_HOME_ON'=>$sys_config['LAYOUT_ON'],

		'URL_ROUTE_RULES' => $RULES,

		'TMPL_EXCEPTION_FILE' => APP_PATH.'/Tpl/Home/Default/Public/exception.html',

		'SHOW_PAGE_TRACE'=>true,


);
$stripe = array(
	'STRIPE_SK'=>'sk_test_l8mBrddqzmSG62jwR7DcT6ys',
	'STRIPE_PK'=>'pk_test_B5zNGWbAM2an4PuDs60NyMj2',
);
$paypal = array(
	'PAYPAL_IPN_URL'=>'https://www.sandbox.paypal.com/cgi-bin/webscr',
	'PAYPAL_GATEWAY'=>'https://www.sandbox.paypal.com/cgi-bin/webscr?',
	'PAYPAL_FORMALGATEWAY'=>'https://www.paypal.com/cgi-bin/webscr?',
	'PAYPAL_ACCOUNT'=>'jing.wang@allocacoc.com.cn',
	'PAYPAL_CMD'=>'_xclick',
	'PAYPAL_ITEM_NAME'=>"orderNo:",
	'PAYPAL_RETURN'=>'http://webshop.allocacoc.com/Product/index.html',
	'PAYPAL_CHARSET'=>'utf-8',
	'PAYPAL_NO_SHIPPING'=>'1',
	'PAYPAL_NO_NOTE'=>'1',
	'PAYPAL_CANCEL_RETURN'=>'http://webshop.allocacoc.com/Product/index.html',
	'PAYPAL_NOTIFY_URL'=>'http://webshop.allocacoc.com/Cart/paypal_notify/orderid/',
	'PAYPAL_RM'=>'2',
);

$mail = array(
	'MAIL_SMTP_SERVER'=>'mail.allocacoc.com',
	'MAIL_USERNAME'=>'designhouse.orders@allocacoc.com',
	'MAIL_PASSWD'=>'egx-7VW-Y4o-Bps',
	'MAIL_FROM'=>'designhouse.orders@allocacoc.com',
	'MAIL_FROMNAME'=>'allocacoc',
	'MAIL_CHARSET'=>'UTF-8',//GB2312
	'MAIL_ENCODING'=>'base64',
	'MAIL_REPLYTO'=>'allocacoc',
	'MAIL_SUBJECT'=>'Thanks for order our product, from Allocacoc',//title of email
);
$tax = array(
	'INCL_TAX'=>'0',
	'TAX_PERCENT'=>1.08,
	'EXCL_VAT'=>'excl VAT',
	'INCL_VAT'=>'incl VAT'
);
return array_merge($database, $config ,$sys_config,$paypal,$stripe,$mail,$tax);

?>

