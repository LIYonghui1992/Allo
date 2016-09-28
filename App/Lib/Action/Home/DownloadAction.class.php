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
		
		$searchflag = 0;
		if(!empty($_REQUEST['search'])  && trim($_REQUEST['search'])=='1' )
		  $searchflag = 1;
		$keyword = $_REQUEST['keyword'];
		

		//$map['lang'] = LANG_ID;
		$map['lang'] = 9;
		$map['status'] = 1;
		
		$flag = -1;
		$list = M('download_category')->where($map)->order('layer asc,listorder asc')->select();
		$category_list = array();
		foreach($list as $category)
		{
			if($category['level']==1){
				$category_list[$category['id']] = array('id'=>$category['id'],'title'=>$category['title'],'level'=>$category['level'],'haschild'=>0,'selected'=>0);
        if($flag == -1)
          $flag = $category['id'];
			}
			  
			if($category['level']==2 && !empty($category_list[$category['parent_id']])){
				  $category_list[$category['parent_id']]['child'][$category['id']] =  array('id'=>$category['id'],'title'=>$category['title'],'level'=>$category['level'],'listorder'=>$category['listorder'],'selected'=>0);
				  $category_list[$category['parent_id']]['haschild'] = 1;
			}
		}
		
		//var_dump($category_list);
		foreach($category_list as $k=>$category){
			 $name = array();
			 foreach ($category_list[$k]['child'] as $key => $value) {
			 	$name[$key] = $value['listorder'];
			 }
			array_multisort($name,$category_list[$k]['child']);
			//var_dump($category_list[$k]['child']);
		}
		
		
		
		
		$category_id = $_REQUEST['category_id'];		
		if(empty($category_id))
		{
			$category_id = $flag;
		}
		
		if($searchflag != 1){
  		//find info of selected category,if level==2;then it's parent category and itself is selected
  		$selected = M('download_category')->where('id ='.$category_id )->order('layer asc')->find();
  		if($selected['level']==2 && !empty($category_list[$selected['parent_id']]))
  		{			
  			$category_list[$selected['parent_id']]['child'][$selected['id']]['selected'] = 1;
  			$category_list[$selected['parent_id']]['selected'] = 1;
  		}			
  		if($selected['level']==1 && !empty($category_list[$category_id]))
  		  $category_list[$category_id]['selected'] = 1;	
	  }
		
		$this->assign('category_list',$category_list);
		$this->assign('category_id',$category_id);
		
		//var_dump($category_list);
		$level = 0;
		$downloads = array();
		if($searchflag != 1){
			if($selected['level']==2)
			{
				$level = $selected['level'];
				$where['b.category_id'] = $category_id;	
				$where['b.lang'] = 9;
		    $downloads = M()->table(array('a_download_category'=>'a','a_download_file_new'=>'b'))
            				 ->where('a.id=b.category_id and a.status=1' )->where($where)->field('b.*')
            				 ->order('a.level desc,a.listorder asc,b.listorder asc,b.uploadtime desc')-> limit(0,16)->select();
            				 
			}
			 //need to order by category id first,then by download file's listorder
			if($selected['level']==1)
			{
				$level = $selected['level'];
				
				$category_ids = array($category_id);				
				$childlist = M('download_category')->where('parent_id ='.$category_id )->select();
				foreach($childlist as $child)
				$category_ids[] = $child['id'];
				//var_dump($category_ids);
				//search on all child category
				$where['b.category_id']  = array('in',$category_ids);
				
				$downloads = M()->table(array('a_download_category'=>'a','a_download_file_new'=>'b'))
            				 ->where('a.id=b.category_id and a.status=1' )->where($where)->field('b.*')
            				 ->order('a.level desc,a.listorder asc,b.listorder asc,b.uploadtime desc')-> limit(0,16)->select();
			}
		}
		  
		else{
			$where['b.tags'] = array('like','%'.$keyword.'%');	
		  //$where['b.lang'] = LANG_ID;
		  $where['b.lang'] = 9;
		  if($searchflag == 1 || $selected['level']!=1)
		    $downloads = M()->table(array('a_download_category'=>'a','a_download_file_new'=>'b'))
            				 ->where('a.id=b.category_id and a.status=1' )->where($where)->field('b.*')
            				 ->order('a.level desc,a.listorder asc,b.listorder asc,b.uploadtime desc')-> limit(0,16)->select();
            				 
       //error_log(json_encode($where));     				 
		  //$downloads = M('download_file_new') -> where($where) -> order('listorder asc,uploadtime desc') -> limit(0,16)->select();
		
		}
		  
		
		
		
		
		foreach($downloads as $k=>$v){
			if(strlen($v['title']) > 42)
			  $downloads[$k]['title'] = substr($v['title'],0,42).'...';
		}
		
		$this->assign('file_list',$downloads);
		$this->assign('filelistcount',count($downloads));
		$this->assign('img_size',$selected['img_size']);
		$this->assign('level',$level);
		
		if($searchflag == 1)
		{
			$this->assign('keyword',$keyword);
			cookie('download_keyword',$keyword);
			$this->assign('search',1);
		}

		

		
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
	
	public function ajax_getfilelist(){
		if (IS_POST){
			$category_id = $_POST['category_id'];
			$filelistcount = $_POST['filelistcount'];
			$level = $_POST['level'];
			
			
			$serachflag = 0;
			if(!empty($_POST['searching']) && $_POST['searching']==1)
			  $serachflag = 1;
			
			if($serachflag == 1){
			 			  
			}
			else
			{
				if($level==2)
				  $where['category_id'] = $category_id;	
				if($level==1){
					$category_ids = array($category_id);
					$childlist = M('download_category')->where('parent_id ='.$category_id )->select();
    				foreach($childlist as $child)
    				$category_ids[] = $child['id'];
    				//var_dump($category_ids);
    				//search on all child category
   				
    				$where['b.category_id']  = array('in',$category_ids);
    				$downloads = M()->table(array('a_download_category'=>'a','a_download_file_new'=>'b'))
            				 ->where('a.id=b.category_id and a.status=1' )->where($where)->field('b.*')
            				 ->order('a.level desc,a.listorder asc,b.listorder asc,b.uploadtime desc')-> limit($filelistcount,8)->select();
				}
	
			}
			  
		  //$where['lang'] = LANG_ID;
		  
		  if($searchflag == 1 || $level!=1){
		  	$where['b.tags'] = array('like','%'.$_COOKIE['allocacoc_download_keyword'].'%');	
		    //$where['b.lang'] = LANG_ID;
		    $where['b.lang'] = 9;
		    $downloads = M()->table(array('a_download_category'=>'a','a_download_file_new'=>'b'))
            				 ->where('a.id=b.category_id and a.status=1' )->where($where)->field('b.*')
            				 ->order('a.level desc,a.listorder asc,b.listorder asc,b.uploadtime desc')-> limit($filelistcount,8)->select();
            			
		  }
		    //$downloads = M('download_file_new') -> where($where) -> order('listorder asc,uploadtime desc') -> limit($filelistcount,8)->select();
		  foreach($downloads as $k=>$v){
			if(strlen($v['title']) > 42)
			  $downloads[$k]['title'] = substr($v['title'],0,42).'...';
		}
		  //error_log(json_encode($downloads));
		  if($downloads){
		  	$rs = array('status' => 1,'filelistcount'=>$filelistcount+count($downloads),'filelist'=>$downloads);
			  echo json_encode($rs);
			  exit ;
		  }
		  
		  $rs = array('status' => 0);
			echo json_encode($rs);
			exit ;
			
		}
	}

	public function bk_index() {

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