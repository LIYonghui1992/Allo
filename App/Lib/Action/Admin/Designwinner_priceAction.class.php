<?php

/**

 * 

 * design winner price,

 *

 */

if(!defined("App")) exit("Access Denied");

class Designwinner_priceAction extends AdminbaseAction {

	protected $dao,$fields;

    function _initialize()

    {	

		parent::_initialize();
		
		$module =$this->module[$this->moduleid]['name'];
		

		$this->dao = D($module);
		
		
		$fields = F($this->moduleid.'_Field');

		foreach($fields as $key => $res){

			$res['setup']=string2array($res['setup']);

			$this->fields[$key]=$res;

		}
		//var_dump($fields);

		unset($fields);

		unset($res);
		
		$this->assign ('fields',$this->fields);

    }


   function index()
   { 
   	/**
     	$country = D('Country') -> select();
  		
  		foreach($country as $k=>$v){
  			
  			$country_list[$v['id']] = $v['title'];
  			
  		}
  		
  		$this->assign ( 'country_list', $country_list );
  		
  		*/
  		
   		$this->_list(MODULE_NAME);

      $this->display ();
   }
   
   
   function _list($modelname, $map = '', $sortBy = '', $asc = false ,$listRows = 15) {
   	//var_dump($_REQUEST);
	
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
		$designwinner_id =intval($_REQUEST['designwinner_id']);
	
		
		if(APP_LANG)if($this->moduleid)$map['lang']=array('eq',LANG_ID);


		if(!empty($keyword) && !empty($searchtype)){
			$map[$searchtype]=array('like','%'.$keyword.'%'); 
		}
		if($groupid)$map['groupid']=$groupid;
		if($catid)$map['catid']=$catid;
		if($posid)$map['posid']=$posid;
		if($typeid) $map['typeid']=$typeid;
		if($designwinner_id) $map['designwinner_id']=$designwinner_id;

		

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
		
		//var_dump($map);

		//取得满足条件的记录总数
		$count = $model->where ( $map )->count ( $id ); 
		
		//var_dump($count);
		
		
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
			
			//var_dump($field);  var_dump($map); var_dump($order);var_dump($sort);
			$voList = $model->field($field)->where($map)->order( "`" . $order . "` " . $sort)->limit($page->firstRow . ',' . $page->listRows)->select ( );
			//分页跳转的时候保证查询条件
			foreach ( $map as $key => $val ) {
				if (! is_array ( $val )) {
					$page->parameter .= "$key=" . urlencode ( $val ) . "&";
				}
			}
			//var_dump($page);
			//var_dump($voList);
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
			
			
			//find out designwinner title,to show
			foreach( $voList as $k=>$v)
			{
				$result = M('design_winner')->field('title')->where(array('id'=>array('eq',$voList[$k]['designwinner_id'])))->select( );
				if($result) //var_dump($result);
				  $voList[$k]['design_title'] = $result[0]['title'];
				//var_dump( $result[0]['title']);
			}
			//var_dump( $voList);
			
			
			//模板赋值显示			
			$this->assign ( 'list', $voList );
			
			
			
			
			//var_dump($map);
			$this->assign ( 'page', $page );
		}
		return;
	}
	
	
   
   function add(){ 
        
		//R('Admin/Content/'.ACTION_NAME);
		
		
		$vo['catid']= intval($_GET['catid']);


		$form=new Form($vo);
		
		$form->isadmin=1;

		$this->assign ( 'form', $form );

		$this->display ();
		
		
   }
   
   
   /** */
   function edit()
   { 
        
  		//R('Admin/Content/'.ACTION_NAME);
  		
  		$id = $_REQUEST ['id'];		
  
  
  		if(MODULE_NAME=='Page'){
  
  					$Page=D('Page');
  					$p = $Page->find($id);
  
  					if(empty($p)){
  
  					$data['id']=$id;
  					$data['title'] = $this->categorys[$id]['catname'];
  					$data['keywords'] = $this->categorys[$id]['keywords'];
  					$Page->add($data);	
  					}
  
  		}
  
  		$vo = $this->dao->getById ( $id );
  		
  		$vo['content'] = htmlspecialchars($vo['content']);
   		$form=new Form($vo);
   		
  		$this->assign ( 'vo', $vo );
  		$this->assign ( 'form', $form );		
  		$this->display ();
		
   }
  
   
   function insert() { 
	   
	   R('Admin/Content/'.ACTION_NAME);
	   
   }
   
   function update() { 
	   
	   R('Admin/Content/'.ACTION_NAME);
	   
   }

}

?>