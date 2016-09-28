<?php

/**

 * 

 * Webshop (网上购物设置)

 *

 */

if(!defined("App")) exit("Access Denied");

class Design_winnerAction extends AdminbaseAction {

	protected $dao,$fields;

    


   function features()
   { 
   	 $design_id = $_REQUEST['id'];
   	 $where1['id'] = $design_id;    
   	 $design_winner = M('Design_winner') -> where($where1)->find();
   	 
   	 
   	 $where['design_id'] = $design_id;        
   	 $design_features = M('Design_feature') -> where($where)->select();
   	 
   	 $this->assign ( 'design_winner', $design_winner );	
   	 $this->assign ( 'design_features', $design_features );	
   	 $this->display ();
   }
   
   function add_feature()
   {
   	  if( !empty($_FILES['feature_img']['name']) )
		 	{
		 		$picfile = $_FILES['feature_img'];
		 		$picname = $picfile['name'];
		 	  $picsize = $picfile['size'];
		 	  
		 	  if ($picsize > 1024000) {
	 	 	 	 $data = array('status'=>0,'msg'=>'file size should in 1MB.');
	 		   echo json_encode($data);
	 		   exit ;
	 	 	  }
	 	 	  $filetype = strstr($picname, '.');
    	 	if ($filetype != ".gif" && $filetype != ".jpg" && $filetype != ".png" ) {
    	 	 	 $data = array('status'=>0,'msg'=>'file format should be .jpg, .gif or .png.');
    		   echo json_encode($data);
    		   exit ;
    	 	} 
    	 	
    	 	$filedir = $this->getimgdir();    	 	
    	 	$rand = rand(100, 999);
  		 	$pics = 'design_'.date("YmdHis") . $rand . $filetype;
  		 	//上传路径
  		 	$pic_path = ROOT.$filedir. $pics; 
  		 	$result = move_uploaded_file($picfile['tmp_name'], $pic_path);
   	 	  if(!$result)
   	 	  {
   	 	 	  $data = array('status'=>0,'msg'=>'fail to move file.');
   	 	 	  echo json_encode($data);
   	 	 	  exit ;
 	      }
		 	}
	

		 	$feature = array(
			'title' => $_REQUEST['title'],
			'img' => '/'.$filedir. $pics, 
			'text' => $_REQUEST['feature_text'], 			
			'design_id' => $_POST['design_id'],
			'listorder' => 0,
			'userid'=>$_SESSION['userid'],
			'username'=>$_SESSION['username'],
			'lang' => LANG_ID, 
			'createtime'=>time());			
						
			$id = M('design_feature') -> add($feature);		
			
			if(!$id){
				$data = array('status' => 0, 'msg' => 'Submitted Failure!');
				echo json_encode($data);
				exit ;
			}
		 	
		 	$data = array('status'=>1);
		 	echo json_encode($data);
		 	exit ;
		 	
   	 //error_log( json_encode($_REQUEST) );
   	 //error_log( json_encode($_FILES) );
   }
   
   
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
   
   function getimgdir()
   {
   	  return 'Uploads/' .date("Ym").'/';
   }
   
   
   function update_fake_order_qty(){
   	if (IS_POST){
   		$winnerid =  $_POST['winnerid'];
			$fake_order_qty = $_POST['fake_order_qty'];
			
			if($winnerid )
			  $where['id'] = $winnerid;
			
			$result = M('design_winner')->where($where)->setField('fake_order_qty',$fake_order_qty);
			if($result === false)
			{
				$rs = array('status' => 0, 'msg' => 'fail to update!');
				echo json_encode($rs);
				exit ;
			}	 
			
			
			$rs = array('status' => 1);			
			echo json_encode($rs);
			exit ;
   	}
   }
   
   
}

?>