<?php

if (!defined("App"))
	exit("Access Denied");

class BottomAction extends BaseAction {

	public function disclaimer() {

		$map['lang'] = LANG_ID;

		$map['status'] = 1;

		$disclaimers = M('disclaimer') -> where($map) -> order('listorder asc') -> select();

		//国家id

		$country_id = $_COOKIE['allocacoc_country_id'];

		//国家默认型号

		$country = M('country') -> where('id=' . $country_id) -> find();

		$product_model_id = $country['product_model_id'];

		$product_model = M('product_model') -> where('id=' . $product_model_id) -> find();

		$banners = explode(':::', $product_model['other']);

		foreach ($banners as $k => $v) {

			$banners[$k] = strstr($v, '|', TRUE);

		}

		$this -> assign('banners', $banners);

		$this -> assign('disclaimer', $disclaimers[0]);

		$this -> display();

	}

	public function faq() {

		$map['lang'] = LANG_ID;

		$map['status'] = 1;

		import("@.ORG.Page");

		$count = M('faq') -> where($map) -> count();

		$p = new Page($count, 20);

		$faqs = M('faq') -> where($map) -> limit($p -> firstRow . ',' . $p -> listRows) ->order('listorder asc')-> select();

		$page = $p -> show();

		//国家id

		$country_id = $_COOKIE['allocacoc_country_id'];

		//国家默认型号

		$country = M('country') -> where('id=' . $country_id) -> find();

		$product_model_id = $country['product_model_id'];

		$product_model = M('product_model') -> where('id=' . $product_model_id) -> find();

		$banners = explode(':::', $product_model['other']);

		foreach ($banners as $k => $v) {

			$banners[$k] = strstr($v, '|', TRUE);

		}

		$this -> assign('banners', $banners);

		$this -> assign('faqs', $faqs);

		$this -> assign('page', $page);

		$this -> display();

	}

	public function patents() {

		//$map['lang'] = LANG_ID;
		
		$map['lang'] = 9;

		$map['status'] = 1;

		$patents = M('patents') -> where($map) -> order('listorder asc') -> select();

		//国家id

		$country_id = $_COOKIE['allocacoc_country_id'];

		//国家默认型号

		$country = M('country') -> where('id=' . $country_id) -> find();

		$product_model_id = $country['product_model_id'];

		$product_model = M('product_model') -> where('id=' . $product_model_id) -> find();

		$banners = explode(':::', $product_model['other']);

		foreach ($banners as $k => $v) {

			$banners[$k] = strstr($v, '|', TRUE);

		}

		$this -> assign('banners', $banners);

		$this -> assign('patents', $patents);

		$this -> assign('page', $page);

		$this -> display();

	}

	public function terms() {

		$map['lang'] = LANG_ID;

		$map['status'] = 1;

		$terms = M('terms') -> where($map) -> order('listorder asc') -> select();

		//国家id

		$country_id = $_COOKIE['allocacoc_country_id'];

		//国家默认型号

		$country = M('country') -> where('id=' . $country_id) -> find();

		$product_model_id = $country['product_model_id'];

		$product_model = M('product_model') -> where('id=' . $product_model_id) -> find();

		$banners = explode(':::', $product_model['other']);

		foreach ($banners as $k => $v) {

			$banners[$k] = strstr($v, '|', TRUE);

		}

		$this -> assign('banners', $banners);

		$this -> assign('term', $terms[0]);

		$this -> display();

	}

	public function jobs() {

		//$map['lang'] = LANG_ID;
		
		$map['lang'] = 9;

		$map['status'] = 1;
		
		$offices = M('office') -> where($map) -> order('listorder asc') -> select();
		
		foreach($offices as $k=>$office){
			$map['office_id'] = $office['id'];
			$offices[$k]['jobs'] = M('jobs') -> where($map) -> order('listorder asc') -> select();
		}

		//var_dump(json_encode($offices));

		//国家id

		$country_id = $_COOKIE['allocacoc_country_id'];

		//国家默认型号

		$country = M('country') -> where('id=' . $country_id) -> find();

		$product_model_id = $country['product_model_id'];

		$product_model = M('product_model') -> where('id=' . $product_model_id) -> find();

		$banners = explode(':::', $product_model['other']);

		foreach ($banners as $k => $v) {

			$banners[$k] = strstr($v, '|', TRUE);

		}

		$this -> assign('banners', $banners);

		$this -> assign('offices', $offices);

		$this -> display();

	}

}
?>