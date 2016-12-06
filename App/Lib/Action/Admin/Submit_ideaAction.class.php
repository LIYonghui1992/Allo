<?php

/**

 * 

 * Webshop (网上购物设置)

 *

 */

if(!defined("App")) exit("Access Denied");

class Submit_ideaAction extends AdminbaseAction {

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
   
   function detail(){
   	$where['id'] = array('eq',$_REQUEST['id']);
    $idea = M('submit_idea')->where($where)->find();
    $fwhere['idea_id'] = array('eq',$_REQUEST['id']);
    
    
    $sliderpics = explode('|', $idea['slider']);

		foreach ($sliderpics as  $v) {
			if($v!='')
			$pics[] = $v;
		}
		$idea['slider'] = $pics;
		
		$featurelist = M('submit_idea_feature')->where($fwhere)->select();
    $idea['featurelist'] = $featurelist;
    //var_dump($idea);
    
    //var_dump(json_encode($idea));
    $this -> assign('idea', $idea);

		$this -> display();
   	
   }
   
}

?>