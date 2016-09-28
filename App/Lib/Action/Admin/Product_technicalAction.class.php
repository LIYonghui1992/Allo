<?php

/**

 * 

 * Webshop (网上购物设置)

 *

 */

if(!defined("App")) exit("Access Denied");

class Product_technicalAction extends AdminbaseAction {

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
        
		$product = D('Product') -> select();
		
		foreach($product as $k=>$v){
			
			$product_list[$v['id']] = $v['title'];
			
		}
		
		$product_model = D('Product_model') -> select();
		
		foreach($product_model as $k=>$v){
			
			$product_model_list[$v['id']] = $v['title'];
			
		}
		
		$this->assign ( 'product_list', $product_list );
		
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
	
   function insert() { 
	   
	   R('Admin/Content/'.ACTION_NAME);
	   
   }
   
   function update() { 
	   
	   R('Admin/Content/'.ACTION_NAME);
	   
   }
   
}

?>