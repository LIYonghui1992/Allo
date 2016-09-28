<?php

/**

 *

 * NewsAction.class.php (前台新闻)

 *

 */

if (!defined("App"))
	exit("Access Denied");

class NewsAction extends BaseAction {

	public function index() {

		//$map['lang'] = LANG_ID;
		$map['lang'] = 9;

		$map['status'] = 1;

		$news = M('article') -> where($map) -> select();

		foreach ($news as $k => $v) {

			$year[$k] = $v['year'];

		}
		//var_dump($year);

		$years = array_unique($year);
		//年份列表
		rsort($years);
		//以降序对数组排序
    
		$get_year = trim($_GET['year']);
		//筛选年份

		if (empty($get_year)) {

			$get_year = $years[0];

		}

		$map['year'] = $get_year;

		$sear['year'] = $get_year;

		import("@.ORG.Page");

		$count = M('article') -> where($map) -> count();

		$p = new Page($count, 8);

		//分页跳转的时候保存查询条件
		foreach ($sear as $key => $val) {

			$p -> parameter .= "$key=" . urlencode($get_year) . "&";
			//赋值给Page

		}

		$map['year'] = $sear['year'];

		$page = $p -> show();

		$list = M('article') -> where($map) -> limit($p -> firstRow . ',' . $p -> listRows) -> order('updatetime desc') -> select();
		//新闻列表

		foreach ($list as $k => $v) {

			$list[$k]['content'] = strip_tags($v['content']);
			
			$title = $v['title'];
			$title = str_replace('&amp;','& ',$title);
			$list[$k]['title'] = $title;

		}

		//国家id

		$country_id = $_COOKIE['allocacoc_country_id'];

		//国家默认型号

		$country = M('country') -> where('id=' . $country_id) -> find();

		$product_model_id = $country['product_model_id'];

		$product_model = M('product_model') -> where('id=' . $product_model_id) -> find();

		$banners = explode(':::', $product_model['news']);

		foreach ($banners as $k => $v) {

			$banners[$k] = strstr($v, '|', TRUE);

		}

		$this -> assign('banners', $banners);

		$this -> assign('years', $years);

		$this -> assign('get_year', $get_year);

		$this -> assign('list', $list);

		$this -> assign('page', $page);

		$this -> display();

	}

	public function detail() {

		if (IS_POST) {

			$id = intval($_POST['id']);

			$article = M('article') -> where('id=' . $id) -> find();

			$data = array('stauts' => 1, 'title' => htmlspecialchars($article['title']), 'thumb' => $article['thumb'], 'updatetime' => date("Y-m-d", $article['updatetime']), 'content' => $article['content']);

			echo json_encode($data);

			exit ;

		}

	}

}
?>