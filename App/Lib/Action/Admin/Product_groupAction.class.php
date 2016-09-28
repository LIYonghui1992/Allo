<?php

/**

 * 

 * Product_group (产品组管理)

 *

 */

if(!defined("App")) exit("Access Denied");

class Product_groupAction extends AdminbaseAction {

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
   	
   	$type_array = array(0=>"Allocacoc product",1=>"DesignNest product");
   	$this -> assign('type_array', $type_array);

    $this->display ();
   }
   
   
   
   
   
   
   function add(){ 
   
		R('Admin/Content/'.ACTION_NAME);
		
		/*$product_model = M('product_model') -> where('lang='.$_SESSION['YP_langid']) -> select(); 
		
		$this->assign ('product_model',$product_model);
		
		$this->display ();*/
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
   
}

?>