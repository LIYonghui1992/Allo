<?php

/**

 * 

 * AboutAction.class.php (前台关于我们)

 *

 */

if(!defined("App")) exit("Access Denied");

class AboutAction extends BaseAction

{

    public function index()

    {
		//国家id
		$country_id       = $_COOKIE['allocacoc_country_id'];
		
		//国家默认型号
		$country          = M('country') -> where('id='.$country_id) -> find();
		
		$product_model_id = $country['product_model_id'];
		
		$product_model    = M('product_model') -> where('id='.$product_model_id) -> find();
		
		$banners          = explode(':::',$product_model['about_us']);
		
		foreach($banners as $k=>$v){
				
				$banners[$k] = strstr( $v, '|', TRUE );
			
		}
		
		$map['lang']      = LANG_ID;
		
		$map['status']    = 1;
		
		$about            = M('about') -> where($map) -> find();
		
		$this->assign('about',$about);
		
		$this->assign('banners',$banners);

        $this->display();

    }

 

}

?>