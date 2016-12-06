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

		'SHOW_PAGE_TRACE'=>false,

//支付宝配置参数
		'alipay_config'=>array(
//			'partner' =>'2088611221573217',   //这里是你在成功申请支付宝接口后获取到的PID； 别人的
//			'key'=>'3qm4goiqs7y7cifw0vdp6hgti15boqyj',//这里是你在成功申请支付宝接口后获取到的Key 别人的
			'partner' =>'2088521231413200',   //这里是你在成功申请支付宝接口后获取到的PID；  正式的 美元账户
			'key'=>'1foalpsf6la6ugsnrhs9752zda3ojbmy',//这里是你在成功申请支付宝接口后获取到的Key  正式的 美元账户
//			'partner' =>'2088101122136241',   //这里是你在成功申请支付宝接口后获取到的PID；  沙盒
//			'key'=>'760bdzec6y9goq7ctyx96ezkz78287de',//这里是你在成功申请支付宝接口后获取到的Key 沙盒
			'sign_type'=>strtoupper('MD5'),
			'input_charset'=> strtolower('utf-8'),
			'cacert'=> getcwd().'/cacert.pem',
			'transport'=> 'https',
			'service' => 'create_forex_trade',
//			"service" => "create_direct_pay_by_user",
		),
		//以上配置项，是从接口包中alipay.config.php 文件中复制过来，进行配置；

		'alipay'   =>array(
			//这里是卖家的支付宝账号，也就是你申请接口时注册的支付宝账号
//			'seller_email'=>'arthur.limpens@allocacoc.com',
//			'seller_email'=>'overseas_kgtest@163.com', //测试的账号
	//这里是异步通知页面url，提交到项目的Pay控制器的notifyurl方法；
//			'notify_url'=>'http://webshop.allocacoc.com/Cart/notifyurl',
			'notify_url'=>'http://www.allocacoc.com/Cart/notifyurl',
	//这里是页面跳转通知url，提交到项目的Pay控制器的returnurl方法；
//			'return_url'=>'http://webshop.allocacoc.com/Cart/returnurl',
			'return_url'=>'http://www.allocacoc.com/Cart/returnurl',
	//支付成功跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参payed（已支付列表）
//			'successpage'=>'User/myorder?ordtype=payed',
//			'successpage'=>'http://webshop.allocacoc.com/Cart/index',
			'successpage'=>'http://www.allocacoc.com/Cart/index',
	//支付失败跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参unpay（未支付列表）
//			'errorpage'=>'User/myorder?ordtype=unpay',
//			'errorpage'=>'http://webshop.allocacoc.com/Cart/index',
			'errorpage'=>'http://www.allocacoc.com/Cart/index',
		),

);
$stripe = array(
	'STRIPE_SK'=>'sk_test_l8mBrddqzmSG62jwR7DcT6ys',
	'STRIPE_PK'=>'pk_test_B5zNGWbAM2an4PuDs60NyMj2',
);
$paypal = array(
	'PAYPAL_IPN_URL'=>'https://www.sandbox.paypal.com/cgi-bin/webscr',
	'PAYPAL_GATEWAY'=>'https://www.paypal.com/cgi-bin/webscr?',
	'PAYPAL_TEST_GATEWAY'=>'https://www.sandbox.paypal.com/cgi-bin/webscr?',
	'PAYPAL_FORMALGATEWAY'=>'https://www.paypal.com/cgi-bin/webscr?',
	'PAYPAL_ACCOUNT'=>'arthur.limpens@allocacoc.com',
	'PAYPAL_CMD'=>'_xclick',
	'PAYPAL_ITEM_NAME'=>"orderNo:",
	'PAYPAL_RETURN'=>'http://www.allocacoc.com/Cart/index.html',
	'PAYPAL_CHARSET'=>'utf-8',
	'PAYPAL_NO_SHIPPING'=>'1',
	'PAYPAL_NO_NOTE'=>'1',
	'PAYPAL_CANCEL_RETURN'=>'http://www.allocacoc.com/Cart/index.html',
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

