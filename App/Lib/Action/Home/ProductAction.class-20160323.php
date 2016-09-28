<?php

/**

 *

 * ProductAction.class.php (前台产品)

 *

 */

if (!defined("App"))
	exit("Access Denied");

class ProductAction extends BaseAction {

	public function index() {
		//国家id
		$country_id = $_COOKIE['allocacoc_country_id'];

		//国家默认型号
		$country = M('country') -> where('id=' . $country_id) -> find();

		$product_model_id = $country['product_model_id'];

		$map['product_model_id'] = $product_model_id;

		$map['lang'] = LANG_ID;

		$map['status'] = 1;

		$product_groups = M('product_group') -> where($map) -> order('listorder asc') -> select();

		$product_model = M('product_model') -> where('id=' . $product_model_id) -> find();

		$banners = explode(':::', $product_model['product']);

		foreach ($banners as $k => $v) {

			$banners[$k] = strstr($v, '|', TRUE);

		}

		$this -> assign('banners', $banners);

		$this -> assign('product_groups', $product_groups);

		$this -> display();

	}

	public function lists() {
	
		$product_group_id = intval($_GET['product_group_id']);

		$product_group = M('product_group') -> where('id=' . $product_group_id) -> find();
		
		//国家id
		$country_id = $_COOKIE['allocacoc_country_id'];

		//国家默认型号
		$country = M('country') -> where('id=' . $country_id) -> find();

		$product_model_id = $country['product_model_id'];

		//$map['product_model_id'] = $product_model_id;

		$map['lang'] = LANG_ID;

		$map['status'] = 1;

		$map['product_group_id'] = $product_group_id;

		$products = M('product') -> where($map) -> order('listorder asc') -> select();

		foreach ($products as $k => $v) {

			$condition['product_id'] = $v['id'];

			$condition['country_id'] = $country_id;
			$condition['status'] = 1;
			
			$products[$k]['prices'] = M('pricelist') -> where($condition) -> order('listorder asc') -> select();

		}

		$banners = array(0 => $product_group['banner_one'], 1 => $product_group['banner_two'], 2 => $product_group['banner_three']);
		
		//model  socket type
		$product_model = M('Product_model') -> where('id=' .$product_model_id)->find(); 


		//Problem solution

		$problems = M('pgroup_probl') -> where('product_group_id=' . $product_group_id) -> order('listorder asc') -> select();
		
		if (!empty($problems[0]['img'])) {
			
			$problem_img = explode(':::', $problems[0]['img']);
			
			foreach ($problem_img as $k => $v) {

				$problem_img[$k] = strstr($v, '|', TRUE);

			}
		}

		//Feature

		$features = M('pgroup_feature') -> where('product_group_id=' . $product_group_id) -> order('listorder asc') -> select();

		if (!empty($features)) {
			
			$feature_img = explode(':::', $features[0]['img']);
			
			foreach ($feature_img as $k => $v) {

				$feature_img[$k] = strstr($v, '|', TRUE);

			}
		}
		//scenarios

		$where['product_group_id'] = $product_group_id;

		$where['product_model_id'] = $product_model_id;

		$scenarios = M('pgroup_scenarios') -> where($where) -> order('listorder asc') -> select();
		
		cookie('pathinfo_group',$product_group['title']);
		cookie('pathinfo_group_id',$product_group['id']);

		$this -> assign('product_group', $product_group);

		$this -> assign('products', $products);
		
		$this -> assign('product_model', $product_model);
		
		$this -> assign('banners', $banners);

		$this -> assign('problem_img', $problem_img);

		$this -> assign('feature_img', $feature_img);

		$this -> assign('scenarios', $scenarios);

		$this -> display();

	}

	public function detail() {
		
		$id = intval($_GET['id']);

		$product = M('product') -> where('id=' . $id) -> find();

		//国家id
		$country_id = $_COOKIE['allocacoc_country_id'];

		//款色价格

		$condition['product_id'] = $id;

		$condition['country_id'] = $country_id;
		$condition['status'] =1;


		$pricelist = M('pricelist') -> where($condition) -> order('listorder asc') -> select();

		foreach ($pricelist as $k => $v) {

			if ($v['version_id'] != 0) {

				$version = M('version') -> where('id=' . $v['version_id']) -> find();

				$versions[$v['id']] = $version;

			}

		}
		
		

		if (empty($versions))
			$sel = 0;
		else
			$sel = 1;

		//Technical specifications

		$product_technical = M('product_technical') -> where('product_id=' . $id) -> order('listorder asc') -> select();

		$product_group_id = intval($_GET['product_group_id']);

		$product_group = M('product_group') -> where('id=' . $product_group_id) -> find();
		
		$product_model = M('Product_model') -> where('id=' .$product_group['product_model_id'])->find();

		$banners = array(0 => $product_group['banner_one'], 1 => $product_group['banner_two'], 2 => $product_group['banner_three']);

		cookie('pathinfo_prd', $product ['title']);
		
		$this -> assign('sel', $sel);

		$this -> assign('product', $product);
		$this -> assign('product_model', $product_model);

		$this -> assign('pricelist', $pricelist);

		$this -> assign('versions', $versions);

		$this -> assign('product_technical', $product_technical[0]);

		$this -> assign('banners', $banners);

		$this -> display();

	}

	public function pricelist() {

		if (IS_POST) {

			$id = intval($_POST['id']);

			$pricelist = M('pricelist') -> where('id=' . $id) -> find();

			$data = array('stauts' => 1, 'availability' => $pricelist['availability'], 'price' => $pricelist['price']);

			echo json_encode($data);

			exit ;

		}

	}

}
?>