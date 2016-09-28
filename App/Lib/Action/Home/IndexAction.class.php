<?php

/**

 *

 * IndexAction.class.php (前台首页)

 *

 */

if (!defined("App"))
	exit("Access Denied");

class IndexAction extends BaseAction {

	public function index() {
		$this -> redirect(__ROOT__."/Index/detail");
				exit ;

		//国家id

		$country_id = $_COOKIE['allocacoc_country_id'];

		//国家默认型号

		$country = M('country') -> where('id=' . $country_id) -> find();

		$product_model_id = $country['product_model_id'];

		$product_model = M('product_model') -> where('id=' . $product_model_id) -> find();

		$banners = explode(':::', $product_model['main']);

		foreach ($banners as $k => $v) {

			$banners[$k] = strstr($v, '|', TRUE);

		}

		$this -> assign('banners', $banners);

		$this -> display();


	}

	public function detail() {;
		//国家id
		$country_id = $_COOKIE['allocacoc_country_id'];

		//国家默认型号

		$country = M('country') -> where('id=' . $country_id) -> find();

		$product_model_id = $country['product_model_id'];

		$product_model = M('product_model') -> where('id=' . $product_model_id) -> find();

		$banners = explode(':::', $product_model['main']);
		foreach ($banners as $k => $v) {

			$banners[$k] = strstr($v, '|', TRUE);

		}
		$imglist = implode(',', $banners);
		$this -> assign('banners', $banners);
		$this -> assign('imglist', $imglist);
		$this -> display();

	}

}
?>