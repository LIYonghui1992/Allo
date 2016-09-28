<?php

/**

 * 

 * Webshop (网上购物设置)

 *

 */

if(!defined("App")) exit("Access Denied");

class Product_paramAction extends AdminbaseAction {

	protected $dao,$fields;
	
	
	
	function edit(){         
		R('Admin/Content/'.ACTION_NAME);
  }

 
   
   /**
   ** param list of a price(product version)
   */
   function params(){
    	$product_id = $_REQUEST['product_id'];
    	
    	$where['id'] = $product_id; 
    	$where2['product_id'] = $product_id;    
    	$param_list = M('product_param') -> where($where2)->order('listorder asc')->select();
    	     	
    	$product = M('Product') -> where($where)->find();    
    	
    	
    	
    	//$type = array(0=>'product info',1=>'size');
    	$type = array(0=>'Product Info',1=>'Shipping Info');
    	      
    	
    	
    	$this->assign ( 'product_id', $product_id ); 
    	$this->assign ( 'product', $product );	
    	$this->assign ( 'param_list', $param_list );	    	
    	$this->assign ( 'type', $type );
    	
    	$this->display ();
   }
   
   
   
   function save_params(){
 
    	if (IS_POST) {
    		$product_id = $_POST['product_id'];
  	 	  $value = $_POST['value'];
  	 	  $title = $_POST['title'];
  	 	  $type = intval($_POST['type']); 
  	 	  $status = intval($_POST['status']);	  
  	 	  
  	 	  if(empty($product_id) || empty($value) || empty($title)  ){
          $rs = array('status' => 3,'msg'=>'miss param');
 			   echo json_encode($rs);
 			   exit ;
        }
  	 	 
	
  	 	  $data = array('param' => $title,  'value' => $value, 	'status' => $status,		  
 		                  'product_id'=>$product_id, 'type'=>$type,'lang'=>LANG_ID,
 		                  'userid'=>$_SESSION['userid'],'username'=>$_SESSION['username'],
 		                  'createtime'=>time());
 		  
  		   $id = M('Product_param') -> add($data);	
    		 if (!$id){
    			$rs = array('status' => 0, 'msg' => 'Error: Failed to submit!');
    			echo json_encode($rs);
    			exit ;
    	   }
  	  
    	   $rs = array('status' => 1 );
    		 echo json_encode($rs);
    		 exit ;
    	}
   }     
   
}

?>