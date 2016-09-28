<?php

/**

 *

 * DownloadAction.class.php (前台下载)

 *

 */

if (!defined("App"))
	exit("Access Denied");

class DownloadAction extends BaseAction {

	public function index() {

		//$map['lang']        = LANG_ID;

		$map['lang'] = 9;

		$map['status'] = 1;

		/*import ( "@.ORG.Page" );

		 $count              = M('download') -> where($map) -> count();

		 $p                  = new Page($count,8);

		 $page               = $p -> show();

		 $downloads          = M('download') -> where($map) ->  limit($p->firstRow . ',' . $p->listRows) -> select();*/

		$downloads = M('download') -> where($map) -> order('listorder asc') -> select();

		//国家id
		$country_id = $_COOKIE['allocacoc_country_id'];

		//国家默认型号
		$country = M('country') -> where('id=' . $country_id) -> find();

		$product_model_id = $country['product_model_id'];

		$product_model = M('product_model') -> where('id=' . $product_model_id) -> find();

		$banners = explode(':::', $product_model['downloads']);

		foreach ($banners as $k => $v) {

			$banners[$k] = strstr($v, '|', TRUE);

		}

		$this -> assign('banners', $banners);

		$this -> assign('downloads', $downloads);

		//$this->assign('page',$page);

		$this -> display();

	}

	public function category2() {
		$download_id = intval($_GET['download_id']);

		//$map['lang']        = LANG_ID;
		
		$download = M('download') -> where('id='.$download_id) -> find();
		//error_log('download:'.json_encode($download));
		cookie('pathinfo_download', $download ['title']);
		cookie('pathinfo_download_id', $download ['id']);
		

		$map['lang'] = 9;

		$map['status'] = 1;

		$map['download_id'] = $download_id;

		$download_twos = M('download_two') -> where($map) -> order('listorder asc') -> select();
		

		$this -> assign('download_twos', $download_twos);

		$this -> display();

	}

	public function download_file() {
		$downloadt_id = intval($_GET['downloadt_id']);
		
		$download2 = M('download_two') -> where('id='.$downloadt_id) -> find();
		cookie('pathinfo_download2', $download2 ['title']);
		cookie('pathinfo_download2_id', $download2 ['id']);

		//$map['lang']        = LANG_ID;

		$map['lang'] = 9;

		$map['status'] = 1;

		$map['downloadt_id'] = $downloadt_id;

		$download_files = M('download_file') -> where($map) -> order('listorder asc') -> select();

		$this -> assign('download_files', $download_files);

		$this -> display();

	}

}
?>