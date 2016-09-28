<?php

/**

 * 

 * Webshop (网上购物设置)

 *

 */

if(!defined("App")) exit("Access Denied");

class Product_modelAction extends AdminbaseAction {

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

		unset($fields);

		unset($res);
		
		$this->assign ('fields',$this->fields);

    }

   function index(){ 
        
		$product_model = D('Product_model') -> select();
		
		foreach($product_model as $k=>$v){
			
			$product_model_list[$v['id']] = $v['title'];
			
		}
		
		$this->assign ( 'product_model_list', $product_model_list );
		
   		$this->_list(MODULE_NAME);

        $this->display ();
   }
   
   function add(){ 
        
		R('Admin/Content/'.ACTION_NAME);
   }
   
   function edit(){ 
        
		R('Admin/Content/'.ACTION_NAME);
   }
   
   function insert(){ 
        
		R('Admin/Content/'.ACTION_NAME);
   }
   
   function update(){ 
        
		R('Admin/Content/'.ACTION_NAME);
   }
   
   function insert2() { 
	    
		$model = $module ?  M($module) : $this->dao;
		
		$fields = $fields ? $fields : $this->fields ;

		if($fields['verifyCode']['status'] && (md5($_POST['verifyCode']) != $_SESSION['verify'])){

			$this->assign ( 'jumpUrl','javascript:history.go(-1);');

			$this->error(L('error_verify'));

        }
		
		$_POST = checkfield($fields,$_POST);

		if(empty($_POST)) $this->error (L('do_empty'));
		
		$_POST['createtime'] = time();		 

		$_POST['updatetime'] = $_POST['createtime'];	

        $_POST['userid'] = $module ? $userid : $_SESSION['userid'];

		$_POST['username'] = $module ? $username : $_SESSION['username'];
		
		$data = array(
		
					  'status'     => $_POST['status'],
					  
					  'userid'     => $_POST['userid'],
					  
					  'username'   => $_POST['username'],
					  
					  'createtime' => $_POST['createtime'],
					  
					  'updatetime' => $_POST['updatetime'],
					  
					  'lang'       => $_POST['lang'],
					  
					  'title'      => $_POST['title']
				);
		
		$id= $model->add($data);
		
		if ($id !==false) {
		
			$main 	   = explode(':::',$_POST['main']);
			
			$product   = explode(':::',$_POST['product']);
			
			$news 	   = explode(':::',$_POST['news']);
			
			$blog 	   = explode(':::',$_POST['blog']);
			
			$downloads = explode(':::',$_POST['downloads']);
			
			$other     = explode(':::',$_POST['other']);
			
			if(!empty($main)){
				
				foreach($main as $k=>$v){
					
					if(!empty($v)){
						
						$data = array(
						
									  'product_model_id' => $id,
									  
									  'img'              => strstr( $v, '|', TRUE ),
									  
									  'type'             => 'main'
								
								);
						
						D('Product_model_img') -> add($data);
						
					}
					
				}
				
			}
			
			if(!empty($product)){
				
				foreach($product as $k=>$v){
					
					if(!empty($v)){
						
						$data = array(
						
									  'product_model_id' => $id,
									  
									  'img'              => strstr( $v, '|', TRUE ),
									  
									  'type'             => 'product'
								
								);
						
						D('Product_model_img') -> add($data);
						
					}
					
				}
				
			}
			
			if(!empty($news)){
				
				foreach($news as $k=>$v){
					
					if(!empty($v)){
						
						$data = array(
						
									  'product_model_id' => $id,
									  
									  'img'              => strstr( $v, '|', TRUE ),
									  
									  'type'             => 'news'
								
								);
						
						D('Product_model_img') -> add($data);
						
					}
					
				}
				
			}
			
			if(!empty($blog)){
				
				foreach($blog as $k=>$v){
					
					if(!empty($v)){
						
						$data = array(
						
									  'product_model_id' => $id,
									  
									  'img'              => strstr( $v, '|', TRUE ),
									  
									  'type'             => 'blog'
								
								);
						
						D('Product_model_img') -> add($data);
						
					}
					
				}
				
			}
			
			if(!empty($downloads)){
				
				foreach($downloads as $k=>$v){
					
					if(!empty($v)){
						
						$data = array(
						
									  'product_model_id' => $id,
									  
									  'img'              => strstr( $v, '|', TRUE ),
									  
									  'type'             => 'downloads'
								
								);
						
						D('Product_model_img') -> add($data);
						
					}
					
				}
				
			}
			
			if(!empty($other)){
				
				foreach($other as $k=>$v){
					
					if(!empty($v)){
						
						$data = array(
						
									  'product_model_id' => $id,
									  
									  'img'              => strstr( $v, '|', TRUE ),
									  
									  'type'             => 'other'
								
								);
						
						D('Product_model_img') -> add($data);
						
					}
					
				}
				
			}
			
			$this->success (L('add_ok'));
		
		}
	   
   }

}

?>