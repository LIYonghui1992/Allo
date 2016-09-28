<?php

$default_theme = is_dir(TMPL_PATH.'Home/'.C('DEFAULT_THEME')) ? C('DEFAULT_THEME') : 'Default';

$config=array(

		'DEFAULT_THEME'		=> $default_theme,

		'URL_ROUTER_ON'		=> false,

		'TMPL_CACHE_ON'		=> false,

		'TMPL_CACHE_TIME'	=> 3600,

//		'TMPL_PARSE_STRING'=>array( //添加个人模板变量规则
//			'__CSS__'=>__ROOT__.'/Public/Css',
//			'__JS__'=>__ROOT__.'/Public/Js',
//		),

		'URL_MODEL'			=> 2,

		'DEFAULT_LANG'      => 'en',

		'LANG_AUTO_DETECT'  => true, // 自动侦测语言 开启多语言功能后有效

		//'LANG_LIST'         => 'en,zh,zh-CN,zh-Hk', // 允许切换的语言列表 用逗号分隔

);

return $config;

?>