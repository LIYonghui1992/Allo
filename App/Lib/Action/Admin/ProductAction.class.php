<?php

/**

 * 

 * Webshop (网上购物设置)

 *

 */

if(!defined("App")) exit("Access Denied");

class ProductAction extends AdminbaseAction {

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
        
		$product_group = D('Product_group') -> select();
		
		foreach($product_group as $k=>$v){
			
			//$product_group_list[$v['id']] = $v['title'];
			$product_group_list[$v['id']] = $v;
			
		}
		$this->assign ( 'product_group_list', $product_group_list );
		
		$product_module = D('Product_model') -> select();		//error_log(json_encode($product_module));
		foreach($product_module as $k=>$v){
			
			$product_module_list[$v['id']] = $v['title'];
			
		}//error_log(json_encode($product_module_list));
		$this->assign ( 'product_module_list', $product_module_list );
		
   	$this->_list(MODULE_NAME);
   	
   	$type_array = array(0=>"Allocacoc product",1=>"DesignNest product");
   	$this -> assign('type_array', $type_array);

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
   
}

?>