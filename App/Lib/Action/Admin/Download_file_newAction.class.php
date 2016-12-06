<?php

/**

 * 

 * Webshop (网上购物设置)

 *

 */

if(!defined("App")) exit("Access Denied");

class Download_file_newAction extends AdminbaseAction {

	protected $dao,$fields;

    function bk_initialize()

    {	

		parent::_initialize();
		
		$module =$this->module[$this->moduleid]['name'];

		$this->dao = D($module);
		
		$fields = F($this->moduleid.'_Field'); 

		foreach($fields as $key => $res){

			$res['setup']=string2array($res['setup']);

			$this->fields[$key]=$res;

		}

		unset($fields);

		unset($res);
		
		$this->assign ('fields',$this->fields);

    }



   /**
     rewrite function from AdminBaseAction.class.php
     need select from a_pricelist and a_product
     get listcount and list info
     update at 2015-09-09, by jing wang
   */
   	function bk_list($modelname, $map = '', $sortBy = '', $asc = false ,$listRows = 15)
   	{
  		$model = M($modelname);
  		$id=$model->getPk ();
  		$this->assign ( 'pkid', $id );
  		
  		if (isset ( $_REQUEST ['order'] )) {
  			$order = $_REQUEST ['order'];
  		} else {
  			$order = ! empty ( $sortBy ) ? $sortBy : $id;
  		}
  		if (isset ( $_REQUEST ['sort'])) {
  			$_REQUEST ['sort']=='asc' ? $sort = 'asc' : $sort = 'desc';
  		} else {
  			$sort = $asc ? 'asc' : 'desc';
  		}
       
  		$_REQUEST ['sort'] = $sort;
  		$_REQUEST ['order'] = $order;
  
  		$keyword=$_REQUEST['keyword'];
  		$searchtype=$_REQUEST['searchtype'];
  		$groupid =intval($_REQUEST['groupid']);
  		$catid =intval($_REQUEST['catid']);
  		$posid =intval($_REQUEST['posid']);
  		$typeid =intval($_REQUEST['typeid']);
  		$country = intval($_REQUEST['country']);
  
  		if(APP_LANG)if($this->moduleid)$map['lang']=array('eq',LANG_ID);
  
  
  		if(!empty($keyword) && !empty($searchtype)){
  			$map[$searchtype]=array('like','%'.$keyword.'%'); 
  			//$map['price']=array('like','%'.$keyword.'%'); 
  		}
  		if($groupid)$map['groupid']=$groupid;
  		if($catid)$map['catid']=$catid;
  		if($posid)$map['posid']=$posid;
  		if($typeid) $map['typeid']=$typeid;
  		if(!empty($keyword)) $map['keyword']=$keyword;
  		if(!empty($keyword)) $map['searchtype']=$searchtype;
  		if($country)$map['country']=$country;
  
  		$tables = $model->getDbFields();
  
  		foreach($_REQUEST['map'] as $key=>$res){
  				if(  ($res==='0' || $res>0) || !empty($res) ){					 
  					if($_REQUEST['maptype'][$key]){
  						$map[$key]=array($_REQUEST['maptype'][$key],$res);
  					}else{
  						$map[$key]=intval($res);
  					}
  					$_REQUEST[$key]=$res;
  				}else{					
  					unset($_REQUEST[$key]);
  				}
  		}
   
     
  		$this->assign($_REQUEST);
  		
  
  
  		$sqlCond = ' from a_pricelist as a ,a_product as b where a.product_id=b.id and a.lang= '.LANG_ID;
  		if(!empty($keyword) && !empty($searchtype))
  		{
  			$sqlCond .= ' and b.title like \'%'.$keyword.'%\' ';
  		}
  		
  		if(isset($_REQUEST['status']))
  		{
  			$sqlCond .= ' and a.status='.$_REQUEST['status'];
  			$map['status'] = $_REQUEST['status'];
  		}
  		
  		if(!empty($country)&& $map['country'] != -1)
  		  $sqlCond .= ' and a.country_id='. $map['country'];
  		
  		//var_dump($sqlCond);
  		if($_REQUEST['order'])
  		{
  			$sqlCond .= ' order by a.'.$_REQUEST['order']. ' ' . $_REQUEST['sort'];
  		}

  		//取得满足条件的记录总数
  		//$count = $model->where ( $map )->count ( $id ); 
  		$result = $model->query('select count(a.id) as num'.$sqlCond);
  		if($result)
  		  $count = $result[0]['num'];
  		  
  		//var_dump($_REQUEST);
  		if ($count > 0) {
  			import ( "@.ORG.Page" );
  			//创建分页对象
  			if (! empty ( $_REQUEST ['listRows'] )) {
  				$listRows = $_REQUEST ['listRows'];
  			}
  			
  			$page = new Page ( $count, $listRows );
  			//分页查询数据
  
  			$field=$this->module[$this->moduleid]['listfields'];
  			$field= (empty($field) || $field=='*') ? '*' : 'id,catid,url,posid,title,thumb,title_style,userid,username,hits,createtime,updatetime,status,listorder' ;
  			
  			 $field .= ', a.price as true_price, a.status as true_status,a.id as true_id,a.listorder as true_listorder ';
  			 
  			//$voList = $model->field($field)->where($map)->order( "`listorder` asc ,`" . $order . "` " . $sort)->limit($page->firstRow . ',' . $page->listRows)->select ( );
  			$voList = $model->query('select '. $field .$sqlCond .' limit '.$page->firstRow . ','. $listRows);
  			
  			
  			//分页跳转的时候保证查询条件
  			foreach ( $map as $key => $val ) {
  				if (! is_array ( $val )) {
  					$page->parameter .= "$key=" . urlencode ( $val ) . "&";
  				}
  			}
  
  			$map[C('VAR_PAGE')]='{$page}';
  			unset($map['lang']);
  			$map['lang']=LANG_ID;
  			$page->urlrule = U($modelname.'/index', $map);
  			//分页显示
  			$page = $page->show ();
  			
  			//列表排序显示
  			$sortImg = $sort; //排序图标
  			$sortAlt = $sort == 'desc' ? '升序排列' : '倒序排列'; //排序提示
  			$sort = $sort == 'desc' ? 1 : 0; //排序方式
  			//模板赋值显示
  			
  			$this->assign ( 'list', $voList );
  			$this->assign ( 'page', $page );
  		}
  		return;
  	}





   function bk_index(){ 
        
		$country = D('Country') -> where('lang='.LANG_ID)->order('title')->select();
		
		foreach($country as $k=>$v){
			
			$country_list[$v['id']] = $v['title'];
			
		}
		
		$product = D('Product') -> select();
		
		foreach($product as $k=>$v){
			
			$product_list[$v['id']] = $v['title'];
			
		}
		
		$version = D('Version') -> select();
		
		foreach($version as $k=>$v){
			
			$version_list[$v['id']] = $v['title'];
			
		}
		
		$this->assign ( 'country_list', $country_list );
		
		$this->assign ( 'product_list', $product_list );
		
		$this->assign ( 'version_list', $version_list );
		
   		//$this->_list(MODULE_NAME,'','listorder',true);
		
		$this->_list(MODULE_NAME);

    $this->display ();
   }
   
   function add(){ 
        
		R('Admin/Content/'.ACTION_NAME);
   }
   
   function edit(){ 
        
		R('Admin/Content/'.ACTION_NAME);
   }
	
   function insert() { 
	
	   R('Admin/Content/'.ACTION_NAME);
	   
   }
   
   
   
   function getSubCategory($parent_id)
   {
   	 $where['lang'] = LANG_ID;
   	 $where['parent_id'] = $parent_id;
 	 
   	 $categorylist = M('download_category')->where($where)->order('listorder asc')->select();
   	 if(empty($categorylist))
   	   return array();
   	 
   	 return $categorylist;
   	 
   }
   
   function getCategoryList()
   {
   	 $where['lang'] = LANG_ID;
   	 $where['parent_id'] = -1;
   
   	 $category_list = array();
   	 
   	 $root = M('download_category')->where($where)->find();
   	 if(empty($root))
   	   return $category_list;  	 
   	 
   	 $category_list[] =  $root;
   	 
   	 $where['parent_id'] = $root['id'];   	 
   	 $categorylist = M('download_category')->where($where)->order('listorder asc')->select();
   	 
   	 if(empty($categorylist))
   	   return $category_list;
   	   
   	 foreach($categorylist as $category){
   	 	 $category_list[] = $category;
   	 	 $subcategorys = $this->getSubCategory($category['id']);
   	 	 foreach($subcategorys as $category_2)
   	 	   $category_list[] = $category_2;
   	 	 
   	 }
   	 
   	 return  $category_list;
   }
   
   
   function index()
   {
   	//$this->assign('tree','ttt');
   	
   	$categorylist =  $this->getCategoryList();
   	
   	$this->assign('tree',$categorylist);
   	
   	if(!empty($_REQUEST ['current_category_id']) )
   	  $current_id = intval($_REQUEST ['current_category_id']);  	
   	else{
   		if(!empty($_REQUEST ['category_id']) )
   	    $current_id = intval($_REQUEST ['category_id']);  	
   	  else
   	    $current_id = $categorylist[0]['id'];
   	}
   	  
   	
   	if($categorylist)
   	{
   		$this->assign('current_id',$current_id);
   		
   		$map['category_id'] = $current_id;
   		$keyword=$_REQUEST['keyword'];
  		$searchtype=$_REQUEST['searchtype'];
   		if(!empty($keyword) && !empty($searchtype)){
  			$map[$searchtype]=array('like','%'.$keyword.'%'); 
  		}
   		
   		
   		//page 
   		$count = M('download_file_new')->where($map)->order('id desc')->count ( 'id' ); 
   		if ($count > 0){
   			import ( "@.ORG.Page" );
      	//创建分页对象
      	if (! empty ( $_REQUEST ['listRows'] )) {
      		$listRows = $_REQUEST ['listRows'];
      	}
      	
      	$page = new Page ( $count, $listRows );
      	//分页查询数据      	
      	$field=$this->module[$this->moduleid]['listfields'];
      	$field= (empty($field) || $field=='*') ? '*' : 'id,catid,url,posid,title,thumb,title_style,userid,username,hits,createtime,updatetime,status,listorder' ;
      	$voList = M('download_file_new')->where($map)->order( "`listorder` asc ,`id` desc ")->limit($page->firstRow . ',' . $page->listRows)->select ( );
      	//分页跳转的时候保证查询条件
  			foreach ( $map as $key => $val ) {
  				if (! is_array ( $val )) {
  					$page->parameter .= "$key=" . urlencode ( $val ) . "&";
  				}
  			}
  			
  			$map[C('VAR_PAGE')]='{$page}';
  			unset($map['lang']);
  			$map['lang']=LANG_ID;
  			$page->urlrule = U( MODULE_NAME.'/index', $map);//var_dump($page->urlrule);
  			//分页显示
  			$page = $page->show ();
  			$this->assign('filelist',$voList);
  			$this->assign ( 'page', $page );
  			$this->assign ( 'current_id', $current_id );
   		}
  		
  		
   		
   		
   		//$filelist = M('download_file_new')->where('category_id = '.$current_id)->order('id desc')->select();
   		//$this->assign('filelist',$filelist);
   	}
   	
   	$this->display();
   }
   
   
   
   
   
   
   
   
   
    function generateTree($categorylist){
   	$html = '<ul>';
   	foreach($categorylist as $k=>$category)
   	{
   		if(substr($category['layer'],0,2)=='00')//level 1
   		  $html .= '<li class="level_1" id="category_'.$category['id'].'"><a href="javascript:change_category('.$category['id'].')">'.$category['title'].'</a></li>';
   		else if(substr($category['layer'],2,2)=='00')
   		  $html .= '<li class="level_2" id="category_'.$category['id'].'">--<a href="javascript:change_category('.$category['id'].')">'.$category['title'].'</a></li>';
   		else
   		$html .= '<li class="level_3" id="category_'.$category['id'].'">----<a href="javascript:change_category('.$category['id'].')">'.$category['title'].'</a></li>';
   		
   	}
   	$html .= '</ul>';
   	
   	return $html;
   	
   }
   
   
   function add_file(){
   	 if (IS_POST){
   	 	 
   	 	 $title = $_REQUEST['file_title'];
   	 	 $filedir = $this->getUploadDir();
   	 	 $pic_path = '';
   	 	 $file_path = '';
   	 	 $listorder = 0;
   	 	 
   	 	 if(!empty( $_REQUEST['file_listorder']))
   	 	   $listorder = intval($_REQUEST['file_listorder']);
   	 	 
   	 	 //error_log('11before add record');
   	 	 if(!empty( $_FILES['file_img_upload']['tmp_name']))
   	 	 {
   	 	 	 $back_pic = $_FILES['file_img_upload'];
   	 	 	 $picname = $back_pic['name'];
		 	   $picsize = $back_pic['size'];
   	 	 	 $filetype = pathinfo($picname, PATHINFO_EXTENSION);
   	 	 	 $rand = rand(100, 999);
   	 	 	 $pics = 'design_'.date("YmdHis") . $rand . '.'.$filetype;
   	 	 	 $pic_path = $filedir. $pics;
   	 	 	 $result = move_uploaded_file($back_pic['tmp_name'], $pic_path);
   	 	 	 if(!$result)
     	 	 {
     	 	 	  $data = array('status'=>0,'msg'=>'fail to move file.');
     	 	 	  echo json_encode($data);
     	 	 	  exit ;
     	   }
   	 	 	 $pic_path = substr($pic_path,strlen(ROOT)-1);
   	 	 }
   	 	 //error_log('12before add record');

   	 	 if(!empty( $_FILES['file_file_upload']['tmp_name']))
   	 	 {
   	 	 	 $file = $_FILES['file_file_upload'];
   	 	 	 $filename = $file['name'];
		 	   $filesize = $file['size'];
   	 	 	 $filetype = pathinfo($filename, PATHINFO_EXTENSION);
   	 	 	 $rand = rand(100, 999);
   	 	 	 $file_newname = 'design_'.date("YmdHis") . $rand . '.'.$filetype;
   	 	 	 $file_path = $filedir. $file_newname;
   	 	 	 $result = move_uploaded_file($file['tmp_name'], $file_path);
   	 	 	 if(!$result)
     	 	 {
     	 	 	  $data = array('status'=>0,'msg'=>'fail to move file.');
     	 	 	  echo json_encode($data);
     	 	 	  exit ;
     	   }
     	   $file_path = substr($file_path,strlen(ROOT)-1);
   	 	 }
   	 	  //error_log('before add record');
   	 	 $tags = $_REQUEST['tags'];
   	 	 if(trim($tags)=='tag1,tag2...')
   	 	   $tags= '';
   	 	 $data = array('title'=>$title,
   	 	 'category_id'=>$_REQUEST['current_id'],
   	 	 'lang'=>LANG_ID,
   	 	 'back_img'=>$pic_path,
   	 	 'file_path'=>$file_path,
   	 	 'userid'=>$_SESSION['userid'],
   	 	 'username'=>$_SESSION['username'],
   	 	 'listorder'=>$listorder,
   	 	 'tags'=>$tags,
   	 	 'uploadtime'=>time(),
   	 	 );
   	 	 
   	 	 //error_log('add record');
   	 	 $id = M('download_file_new') -> add($data);	
			 if (!$id){
				$rs = array('status' => 0, 'msg' => 'Submitted Failure!');
				echo json_encode($rs);
				exit ;
		   }
		   
		   $rs = array('status' => 1);
			 echo json_encode($rs);
			 exit ;

   	 }
   }
   
   
   
   function add_category(){
   	 if (IS_POST){
   	 	
   	 	 $listorder = intval($_POST['listorder']);
   	 	 if($listorder<10)
   	 	   $layer = '0'.$listorder;//substr($_POST['layer'],0,4);
   	 	 else
   	 	   $layer = $listorder;//substr($_POST['layer'],0,4);
   	 	 
   	 	 $level = intval($_POST['level']);  
   	 	 if($level==1)
   	 	 {
   	 	 	 $layer = $layer.substr($_POST['layer'],2);
   	 	 }
   	 	 if($level==2)
   	 	 {
   	 	 	 $layer = substr($_POST['layer'],0,4).$layer;
   	 	 }
   	 	 
  		 $data = array('title' => $_POST['title'], 
  		   'background_pic'=>'',
  			 'lang' => LANG_ID,
  			 'status'=>$_POST['status'],
  			  'level' => $level, 
  			  'layer' => $layer, 
  			  'parent_id' => $_POST['parent_id'], 
  			  'img_size' => $_POST['img_size'],
  			  'listorder'=>$_POST['listorder'],
  			);

		   $id = M('download_category') -> add($data);	
			 if (!$id){
				$rs = array('status' => 0, 'msg' => 'Submitted Failure!');
				echo json_encode($rs);
				exit ;
		   }
		   
		   $rs = array('status' => 1);
			 echo json_encode($rs);
			 exit ;
		 
   	 }
   
   }
   
   
   function update_category(){
   	 if (IS_POST){   
   	 	$data = array('title'=>$_POST['title'],
   	 	'status'=>$_POST['status'],
   	 	'img_size'=>$_POST['img_size'],
   	 	'listorder'=>$_POST['listorder']
   	 	);
   	 	
       $category_id = intval($_POST['category_id']);
       $result = M("download_category")->where('id='.$category_id)->setField($data);
       if(!$result)
   	 	 {
   	 		 $rs = array('status' => 0, 'msg' => 'Submitted Failure!');
				 echo json_encode($rs);
				 exit ;
   	 	 }
   	 	
   	 	$rs = array('status' => 1);
			echo json_encode($rs);
			exit ;
   	 	 
   	 }
   }
   
   function del_category(){
   	 if (IS_POST){
   	 	$category_id = intval($_POST['category_id']);
   	 	$result = M("download_category")->where('id='.$category_id)->delete(); 
   	 	if(!$result || $result < 1)
   	 	{
   	 		$rs = array('status' => 0, 'msg' => 'Submitted Failure!');
				echo json_encode($rs);
				exit ;
   	 	}
   	 	
   	 	$rs = array('status' => 1);
			echo json_encode($rs);
			exit ;
				
   	 }
   }
   
   function getUploadDir()
   {
   	 $filedir = UPLOAD_PATH.date('Ym').'/';
   	 //if not exist,create dir
   	 if(!file_exists($filedir))   
   	 	 mkdir($filedir,0777);         //创建文件夹
   	 return $filedir;
   }
}

?>