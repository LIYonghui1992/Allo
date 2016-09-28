<?php

/**

 *

 * BlogAction.class.php (前台博客)

 *

 */

if (!defined("App"))
	exit("Access Denied");

class BlogAction extends BaseAction {

	public function index() {

		//$map['lang'] = LANG_ID;
		$map['lang'] = 9;

		$map['status'] = 1;

		/*
		if ($_GET['title'] != '') {

			//$map['title|author|article_text'] = array('like', '%' . $_POST['title'] . '%');
			
			$condition['title']  = array('like', '%' . $_GET['title'] . '%');
			
			$condition['author']  = array('like', '%' . $_GET['title'] . '%');
			
			$condition['article_text']  = array('like', '%' . $_GET['title'] . '%');
			
			$condition['_logic'] = 'or';
			
			$map['_complex'] = $condition;

		}
		*/
		
		$values = M('blog') ->distinct(true)->field('category')-> where($map) -> select();

		
		foreach ($values as $k => $v) {

			$categorys[$k] = $v['category'];

		}
		//var_dump($year);
   /*
		$category = array_unique($category);		
		
		rsort($category);
		*/
		sort($categorys);
		error_log(json_encode($categorys));
		$get_category = trim($_GET['category']);

		if (empty($get_category)) {
			$get_category = $categorys[0];
		}
		
		$map['category'] = $get_category;

		$sear['category'] = $get_category;		
		
		$count = M('blog') -> where($map) -> count();
		
		import("@.ORG.Page");
		 
		$p = new Page($count, 6);
		
		//分页跳转的时候保存查询条件
		foreach ($sear as $key => $val) {
			$p -> parameter .= "$key=" . urlencode($get_category) . "&";
			//赋值给Page
		}
		

		
		foreach($map['_complex'] as $key=>$val) {
			 $p->parameter   .=   "$key=".urlencode(substr($val[1],1,-1)).'&'; 
		}
		
		
		$blogs = M('blog') -> where($map) -> limit($p -> firstRow . ',' . $p -> listRows) -> order('updatetime desc') -> select();
		
		foreach($blogs as $k=>$v){
			
			$blogs[$k]['article_text'] = strip_tags($v['article_text']);
		}
		
		//$p -> parameter = '?';
		
		//$p->setConfig('theme','%first%%upPage%%downPage% %prePage%%nextPage%%end%');
		
    $page = $p -> show();




		//国家id

		$country_id = $_COOKIE['allocacoc_country_id'];

		//国家默认型号

		$country = M('country') -> where('id=' . $country_id) -> find();

		$product_model_id = $country['product_model_id'];

		$product_model = M('product_model') -> where('id=' . $product_model_id) -> find();

		$banners = explode(':::', $product_model['blog']);

		foreach ($banners as $k => $v) {

			$banners[$k] = strstr($v, '|', TRUE);

		}
		
		$this -> assign('banners', $banners);

		$this -> assign('blogs', $blogs);
		//var_dump($page);
		$this -> assign('page', $page);

		$this -> assign('categorys', $categorys);
		$this -> assign('get_category', $get_category);

		$this -> display();

	}

	public function detail() {

		if (IS_POST) {

			$id = intval($_POST['id']);

			$blog = M('blog') -> where('id=' . $id) -> find();

			$data = array('stauts' => 1, 'title' => $blog['title'], 'background_image' => $blog['background_image'], 'expanded_image' => $blog['expanded_image'], 'author' => $blog['author'], 'updatetime' => date("Y.m.d", $blog['updatetime']), 'article_text' =>$blog['article_text']);

			echo json_encode($data);

			exit ;

		}

	}
	
	
	public function import_fairs(){
		$map['year'] = 'fairs';
		$map['lang'] = 9;
		
		$news = M('article')->where($map)-> order('id desc') -> select();
		
		foreach($news as $k=>$v){
			
			if(empty($v['start_end']) || empty($v['status'])) continue;
						
			$start_end = $v['start_end'];
			$dates = explode('-',$start_end);var_dump(json_encode($dates));
			if(count($dates)>2){
				var_dump("date format wrong, id:".$v['id'].",start_end:".$v['start_end']);
				continue;
			}			
			
			$temp = explode('.',$dates[0]);
			if(count($temp)!=3){
				var_dump("start time format wrong:".$dates[0]);
				continue;
			}
			
			$temp = explode('.',$dates[1]);
			if(count($temp)!=3){
				var_dump("end time format wrong:".$dates[1]);
				continue;
			}
				
			$start_time = strtotime(str_replace('.',"/",$dates[0]));
			$end_time = strtotime(str_replace('.',"/",$dates[1]));
			
			$blog = array('status'=>1,'userid'=>$v['userid'],'username'=>$v['username'],'url'=>$v['url'],
			              'createtime'=>$v['createtime'],'updatetime'=>$v['updatetime'],'lang'=>$v['lang'],
			              'category'=>'Fairs','title'=>$v['title'],'title_style'=>$v['title_style'],
			              'background_image'=>$v['thumb'],'article_text'=>$v['content'],'address'=>$v['address'],
			              'booth_no'=>$v['booth_no'],'start_time'=>$start_time,'end_time'=>$end_time);
			/*
			$blog = array('status'=>1,'start_time'=>$start_time,'end_time'=>$end_time);
			*/
			
			
			//var_dump(json_encode($blog));			
			$id = M('blog') -> add($blog);
			var_dump("add blog,id:$id");			
		}
		
	}
	
	public function import_awards(){
		$map['year'] = array('NEQ','fairs');
		$map['lang'] = 9;
		$map['status'] = 1;
		
		$news = M('article')->where($map)-> order('id desc') -> select();
		/*
		foreach($news as $k=>$v){
			var_dump($v['id'].":".$v['year']."/n");
		}
		*/
		
		foreach($news as $k=>$v){
			
			if(empty($v['start_end']) || empty($v['status'])) continue;						
			
			$blog = array('status'=>1,'userid'=>$v['userid'],'username'=>$v['username'],'url'=>$v['url'],
			              'createtime'=>$v['createtime'],'updatetime'=>$v['updatetime'],'lang'=>$v['lang'],
			              'category'=>'Awards','title'=>$v['title'],'title_style'=>$v['title_style'],
			              'background_image'=>$v['thumb'],'article_text'=>$v['content'],'address'=>$v['address'],
			              'booth_no'=>$v['booth_no']);
			
			//var_dump(json_encode($blog));			
			$id = M('blog') -> add($blog);
			var_dump("add blog,id:$id");			
		}
		
		
	}

}


/* blog
	6	listorder	int(10)		UNSIGNED	否	0		修改 修改	删除 删除	
	13	background_image	varchar(80)	utf8_general_ci		否			修改 修改	删除 删除	
	14	expanded_image	varchar(80)	utf8_general_ci		否			修改 修改	删除 删除	
	15	author	varchar(255)	utf8_general_ci		否			修改 修改	删除 删除	
	17	watch_out	varchar(255)	utf8_general_ci		否			修改 修改	删除 删除	
   
	*/
	
	/*
	catid	smallint(5)		UNSIGNED	否	0		修改 修改	删除 删除	
	7	keywords	varchar(120)	utf8_general_ci		否			修改 修改	删除 删除	
	8	copyfrom	varchar(255)	utf8_general_ci		否			修改 修改	删除 删除	
	9	fromlink	varchar(80)	utf8_general_ci		否	0		修改 修改	删除 删除	
	10	description	mediumtext	utf8_general_ci		否	无		修改 修改	删除 删除	
	11	content	text	utf8_general_ci		否	无		修改 修改	删除 删除	
	12	template	varchar(30)	utf8_general_ci		否			修改 修改	删除 删除	
	14	posid	tinyint(2)		UNSIGNED	否	0		修改 修改	删除 删除	
	16	recommend	tinyint(1)		UNSIGNED	否	1		修改 修改	删除 删除	
	17	readgroup	varchar(255)	utf8_general_ci		否			修改 修改	删除 删除	
	18	readpoint	int(10)		UNSIGNED	否	0		修改 修改	删除 删除	
	19	listorder	int(10)		UNSIGNED	否	0		修改 修改	删除 删除	
	21	hits	int(11)		UNSIGNED	否	0		修改 修改	删除 删除	
	24	name	varchar(255)	utf8_general_ci		否			修改 修改	删除 删除	
	26	year	varchar(255)	utf8_general_ci		否			修改 修改	删除 删除	
	27	fairflag	tinyint(1)		UNSIGNED	否	0		修改 修改	删除 删除	
	29	start_end	varchar(127)	utf8_general_ci		否			修改 修改	删除 删除	
	
	 
	*/
?>