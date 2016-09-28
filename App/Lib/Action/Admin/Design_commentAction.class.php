<?php

/**

 * 

 * Webshop (网上购物设置)

 *

 */

if(!defined("App")) exit("Access Denied");


class Design_commentAction extends AdminbaseAction {

	protected $dao,$fields;


   function _list($modelname, $map = '', $sortBy = '', $asc = false ,$listRows = 15) {
	
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

		if(APP_LANG)if($this->moduleid)$map['lang']=array('eq',LANG_ID);


		if(!empty($keyword) && !empty($searchtype)){
			$map[$searchtype]=array('like','%'.$keyword.'%'); 
		}
		if($groupid)$map['groupid']=$groupid;
		if($catid)$map['catid']=$catid;
		if($posid)$map['posid']=$posid;
		if($typeid) $map['typeid']=$typeid;
		

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

		//取得满足条件的记录总数
		$count = $model->where ( $map )->count ( $id ); 
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
			$voList = $model->field($field)->where($map)->order( "`listorder` asc ,`" . $order . "` " . $sort)->limit($page->firstRow . ',' . $page->listRows)->select ( );
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
			
			
			foreach($voList as $k=>$v){
				
				$voList[$k]['admin_reply'] = str_replace("\"","\'\'",$voList[$k]['admin_reply']);
				$voList[$k]['admin_reply'] = addslashes($voList[$k]['admin_reply']);
			}
			
			$this->assign ( 'list', $voList );
			$this->assign ( 'page', $page );
		}
		return;
	}
	
	
	
	function index(){ 
   	  
   	  $designwinnerlist = M('design_winner')->select();
   	  
   	  $designlist = array();
   	  
   	  foreach($designwinnerlist as $item){
   	  	$designlist[$item['id']] = $item['title'];
   	  }
   	  
   	  $this->assign ( 'designlist', $designlist );
		
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
   
   function update() { 
	   
	   R('Admin/Content/'.ACTION_NAME);
	   
   }
   
   
  public function  reply(){
  	if (IS_POST){
  		$comment_id =  $_POST['comment_id'];
  		$reply =  $_POST['reply'];
  		
  		$reply = str_replace("\'\'","\"",$reply);
  		
  		$result = M('Design_comment')->where('id='. $comment_id)->setField('admin_reply',$reply);
      if(!$result)
      { error_log(M('Design_comment')->getLastsql());
      	$rs = array('status' => 0, 'msg' => 'fail to reply!');
				echo json_encode($rs);
				exit ;
      }
      
      $rs = array('status' => 1);
			echo json_encode($rs);
			exit ;
                    
  	}
  }
  
  
  public function  detail(){
  	if (IS_POST){
  		$comment_id =  $_POST['comment_id'];
  		
  		//$reply = str_replace("\'\'","\"",$reply);
  		
  		$comment = M('Design_comment')->where('id='. $comment_id)->find();
      if(!$comment)
      { 
      	$rs = array('status' => 0, 'msg' => 'fail to reply!');
				echo json_encode($rs);
				exit ;
      }
      
      $rs = array('status' => 1,'content'=>$comment['content'],'reply'=>$comment['admin_reply']);
			echo json_encode($rs);
			exit ;
                    
  	}
  }
 
   

}


		
?>