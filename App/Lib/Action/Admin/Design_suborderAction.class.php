<?php

/**

 * 

 * Webshop (网上购物设置)

 *

 */

if(!defined("App")) exit("Access Denied");


class Design_suborderAction extends AdminbaseAction {

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

//
	   $this->_list(MODULE_NAME);

	   $this->display ();


//	   $model=M(MODULE_NAME);
//	   $order_info = $model->select();
//	   if(isset($_GET['orderid'])){
//		   $order_id=$_GET['orderid'];
//		   $order_info = $model->where('orderid =\'' . $order_id . '\'')->select();
//	   }

//	   $this->assign("list",$order_info);
//
//	   $this->display ();
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
   
   
   function sendmail()
   {
   	if (IS_POST) {
			$priceid =  $_POST['priceid'];
			$content = $_POST['content'];
			$title = $_POST['title'];
			$winnerid =  $_POST['winnerid'];
			
			if($priceid )
			  $where['price_id'] = $priceid;
			if($winnerid )
			  $where['designwinner_id'] = $winnerid;
			
			//calculate shipping price ,according to weight country
			$design = M('design_order') -> where($where) -> select();
			if(!$design)
			{
				$rs = array('stauts' => 0, 'msg' => 'Sorry, fail to send or no customer order!');
				echo json_encode($rs);
				exit ;
			}	
			
			  
			
		
			import("@.ORG.PHPMailer");
			$mail = new PHPMailer(); 
			
			/*			
      $mail->IsSMTP(); // send via SMTP 
      $mail->Host = "smtp.exmail.qq.com"; // SMTP servers 
      $mail->SMTPAuth = true; // turn on SMTP authentication 
      $mail->Username = "tell@allocacoc.com.cn"; // SMTP username 注意：普通邮件认证不需要加 @域名 
      $mail->Password = "powercube123"; // SMTP password 
      $mail->From = "tell@allocacoc.com.cn"; // 发件人邮箱
      */
      
      $mail->IsSMTP(); // send via SMTP 
      $mail->Host = "mail.allocacoc.com"; // SMTP servers 
      $mail->SMTPAuth = true; // turn on SMTP authentication 
      $mail->Username = "designhouse.orders@allocacoc.com"; // SMTP username 注意：普通邮件认证不需要加 @域名 
      $mail->Password = "egx-7VW-Y4o-Bps"; // SMTP password  
      $mail->From = "designhouse.orders@allocacoc.com"; // 发件人邮箱       
   
      /*
      $mail->Username = "designhouse.orders@allocacoc.com"; // SMTP username 注意：普通邮件认证不需要加 @域名 
      $mail->Password = "egx-7VW-Y4o-Bps"; // SMTP password 
      
      $mail->From = "designhouse.orders@allocacoc.com"; // 发件人邮箱 
 
      */
      $mail->FromName = "allocacoc"; // 发件人 ,比如 中国资金管理网
      
      $mail->CharSet = "GB2312"; // 这里指定字符集！ 
      $mail->Encoding = "base64"; 
      //$mail->AddAddress('wingjang@163.com','');
      
      foreach($design as $custm)
        $mail->AddAddress($custm['email'],''); // 收件人邮箱和姓名 
        
      $mail->AddReplyTo("","allocacoc"); 
      
      
      $mail->IsHTML(true); // send as HTML 
      $mail->Subject = $title; 
      $mail->WordWrap = 80; // 设置每行字符串的长度 
      //$mail->AddAttachment("f:/test.png"); //可以添加附件      
      $mail->Body = $content;
      $mail->AltBody ="To view the message, please use an HTML compatible email viewer!"; 
      if(!$mail->Send()) 
      { error_log('send error:'.$mail->ErrorInfo);
      	$rs = array('stauts' => 0, 'msg'=>"send mail failed");
      	echo json_encode($rs);
      	exit ;
      }
			
		
			$rs = array('stauts' => 1, 'msg'=>"already sent");			
			echo json_encode($rs);
			exit ;
			

		}

   }
   
   
   /**
     rewrite function from AdminBaseAction.class.php
     get listcount and list info
     update at 2015-10-08, by jing wang
   */
   	function _list($modelname, $map = '', $sortBy = '', $asc = false ,$listRows = 15)
   	{
	   $model=M($modelname);
	   $order_info = $model->select();
	   if(isset($_GET['orderid'])){
		   $order_id=$_GET['orderid'];
		   $order_info = $model->where('orderid =\'' . $order_id . '\'')->select();
	   }

	   $this->assign("list",$order_info);

//  		$model = M($modelname);
//  		$id=$model->getPk ();
//  		$this->assign ( 'pkid', $id );
//
//  		if (isset ( $_REQUEST ['order'] )) {
//  			$order = $_REQUEST ['order'];
//  		} else {
//  			$order = ! empty ( $sortBy ) ? $sortBy : $id;
//  		}
//
//  		if (isset ( $_REQUEST ['sort'])) {
//  			$_REQUEST ['sort']=='asc' ? $sort = 'asc' : $sort = 'desc';
//  		} else {
//  			$sort = $asc ? 'asc' : 'desc';
//  		}
//
//  		$_REQUEST ['sort'] = $sort;
//  		$_REQUEST ['order'] = $order;
//
//  		$keyword=$_REQUEST['keyword'];
//  		$searchtype=$_REQUEST['searchtype'];
//  		$groupid =intval($_REQUEST['groupid']);
//  		$catid =intval($_REQUEST['catid']);
//  		$posid =intval($_REQUEST['posid']);
//  		$typeid =intval($_REQUEST['typeid']);
//  		$country = intval($_REQUEST['country']);
//			$orderid= $_REQUEST['orderid'];
//  		/* modify 20160115,not care lang
//  		if(APP_LANG)if($this->moduleid)$map['lang']=array('eq',LANG_ID);
//  		*/
//
//  			//关键词搜索
//  		if(!empty($keyword) && !empty($searchtype)){
//  			$map[$searchtype]=array('like','%'.$keyword.'%');
//  			//$map['price']=array('like','%'.$keyword.'%');
//  		}
//			if($orderid) $map['orderid']=$orderid;
//  		if($groupid)$map['groupid']=$groupid;
//  		if($catid)$map['catid']=$catid;
//  		if($posid)$map['posid']=$posid;
//  		if($typeid) $map['typeid']=$typeid;
//  		if(!empty($keyword)) $map['keyword']=$keyword;
//  		if(!empty($keyword)) $map['searchtype']=$searchtype;
//  		if($country)$map['country']=$country;
//
//  		$tables = $model->getDbFields();
//
//
//  		foreach($_REQUEST['map'] as $key=>$res){
//  				if(  ($res==='0' || $res>0) || !empty($res) ){
//  					if($_REQUEST['maptype'][$key]){
//  						$map[$key]=array($_REQUEST['maptype'][$key],$res);
//  					}else{
//  						$map[$key]=intval($res);
//  					}
//  					$_REQUEST[$key]=$res;
//  				}else{
//  					unset($_REQUEST[$key]);
//  				}
//  		}
//
//
//  		$this->assign($_REQUEST);
//
//
//
//      $count = $model->where ( $map )->count ( $id );
//      if ($count > 0)
//      {
//      	import ( "@.ORG.Page" );
//      	//创建分页对象
//      	if (! empty ( $_REQUEST ['listRows'] )) {
//      		$listRows = $_REQUEST ['listRows'];
//      	}
//
//      	$page = new Page ( $count, $listRows );
//      	//分页查询数据
//
//      	$field=$this->module[$this->moduleid]['listfields'];
//      	$field= (empty($field) || $field=='*') ? '*' : 'id,catid,url,posid,title,thumb,title_style,userid,username,hits,createtime,updatetime,status,listorder' ;
//      	$voList = $model->field($field)->where($map)->order( "`listorder` asc ,`" . $order . "` " . $sort)->limit($page->firstRow . ',' . $page->listRows)->select ( );
//      	//分页跳转的时候保证查询条件
//  			foreach ( $map as $key => $val ) {
//  				if (! is_array ( $val )) {
//  					$page->parameter .= "$key=" . urlencode ( $val ) . "&";
//  				}
//  			}
//
//  			$map[C('VAR_PAGE')]='{$page}';
//  			/* modify 20160115,not care lang
//  			unset($map['lang']);
//  			$map['lang']=LANG_ID;
//  			*/
//  			$page->urlrule = U($modelname.'/index', $map);
//  			//分页显示
//  			$page = $page->show ();
//
//  			//列表排序显示
//  			$sortImg = $sort; //排序图标
//  			$sortAlt = $sort == 'desc' ? '升序排列' : '倒序排列'; //排序提示
//  			$sort = $sort == 'desc' ? 1 : 0; //排序方式
//  			//模板赋值显示
//
//  			$this->assign ( 'list', $voList );
//  			$this->assign ( 'page', $page );
//      }
      
      return;
      
      /*
  		$sqlCond = ' from a_pricelist as a ,a_product as b where a.product_id=b.id and a.lang= '.LANG_ID;
  		if(!empty($keyword) && !empty($searchtype))
  		{
  			$sqlCond .= ' and b.title like \'%'.$keyword.'%\' ';
  		}
  		
  		*/

  		
  	}
  	
  	
  	
  	
  	function bk_detail(){
  		
  		if (IS_POST){
  			
  		}
  		
  	}
  	
  	function detail()
  	{ 
  		$orderid = $_REQUEST ['orderid'];	
  		
  		$where['orderid']=array('eq',$orderid);
  		$itemlist = M('design_orderdetail')->where($where)->select();
  		
  		
  		$order = M('design_order')->where($where)->find();
  		
  		$this->assign ( 'order', $order );
  		
  		$this->assign ( 'itemlist', $itemlist );
  		$this->display ();
  	}
  	
  	
  	function export_order(){
  		
  		//error_log(json_encode($_GET));
  		import("@.ORG.PHPExcel");
  		
  		
  		$status_array = array(0=>"not approved",1=>"approved");
  		$payflag_array = array(0=>"not paid",1=>"paid");
  		$paytype_array = array(1=>"paypal",2=>"card");
  		
  		
  		$objPHPExcel = new PHPExcel();
  		 
  		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document")
							 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Test result file");
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);		
      $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
      $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
      $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
      $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(100);
      $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(100);
      $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(100);
      $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(100);
      $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(100);
      
      $objPHPExcel->setActiveSheetIndex(0)  
            ->setCellValue('A1', 'id')
            ->setCellValue('B1', 'status')
            ->setCellValue('C1', 'createtime')
            ->setCellValue('D1', 'country')
            ->setCellValue('E1', 'city')
            ->setCellValue('F1', 'first_name')
            ->setCellValue('G1', 'last_name')
            ->setCellValue('H1', 'company')
            ->setCellValue('I1', 'address')
            ->setCellValue('J1', 'zip')
            ->setCellValue('K1', 'email')
            ->setCellValue('L1', 'phone')
            ->setCellValue('M1', 'product_name')
            ->setCellValue('N1', 'qty')
            ->setCellValue('O1', 'address2')          
            ->setCellValue('P1', 'price')           
            ->setCellValue('Q1', 'shipfee') 
            ->setCellValue('R1', 'total')                      
            ->setCellValue('S1', 'type/version')             
            ->setCellValue('T1', 'payflag')
            ->setCellValue('U1', 'orderNo')
            ->setCellValue('V1', 'paytype '); 
      //winner_name,email,id
      if($_GET['winner_name'])
        $where['winner_name']=array('eq',$_GET['winner_name']);        
      if($_GET['email'])
        $where['email']=array('eq',$_GET['email']);
      if($_GET['id'])
        $where['id']=array('eq',$_GET['id']);
        
      $orders = M('design_order')->where($where)->select();

      for ($i = 0, $len = count($orders); $i < $len; $i++){
      	$objPHPExcel->getActiveSheet(0)->setCellValue('A' . ($i + 2), $orders[$i]['id']);
      	$objPHPExcel->getActiveSheet(0)->setCellValue('B' . ($i + 2), $status_array[$orders[$i]['status']]);
        $objPHPExcel->getActiveSheet(0)->setCellValue('C' . ($i + 2), date('Y-m-d H:i:s',($orders[$i]['createtime'])) );//date('Y-m-d H:i:s',($sys_logs[$i]['createtime']))
      	$objPHPExcel->getActiveSheet(0)->setCellValue('D' . ($i + 2), $orders[$i]['country']);       	
      	$objPHPExcel->getActiveSheet(0)->setCellValue('E' . ($i + 2), $orders[$i]['city']);
      	$objPHPExcel->getActiveSheet(0)->setCellValue('F' . ($i + 2), $orders[$i]['first_name']);
      	$objPHPExcel->getActiveSheet(0)->setCellValue('G' . ($i + 2), $orders[$i]['last_name']);
      	$objPHPExcel->getActiveSheet(0)->setCellValue('H' . ($i + 2), $orders[$i]['company']);
      	$objPHPExcel->getActiveSheet(0)->setCellValue('I' . ($i + 2), $orders[$i]['address']);
      	$objPHPExcel->getActiveSheet(0)->setCellValue('J' . ($i + 2), $orders[$i]['zip']);
      	$objPHPExcel->getActiveSheet(0)->setCellValue('K' . ($i + 2), $orders[$i]['email']);
      	$objPHPExcel->getActiveSheet(0)->setCellValue('L' . ($i + 2), $orders[$i]['phone']);
      	
      	$objPHPExcel->getActiveSheet(0)->setCellValue('M' . ($i + 2), $orders[$i]['winner_name']);
      	$objPHPExcel->getActiveSheet(0)->setCellValue('N' . ($i + 2), $orders[$i]['qty']);
      	$objPHPExcel->getActiveSheet(0)->setCellValue('O' . ($i + 2), $orders[$i]['address2']);
      	$objPHPExcel->getActiveSheet(0)->setCellValue('P' . ($i + 2), $orders[$i]['price']);
      	$objPHPExcel->getActiveSheet(0)->setCellValue('Q' . ($i + 2), $orders[$i]['shipfee']);
      	$objPHPExcel->getActiveSheet(0)->setCellValue('R' . ($i + 2), $orders[$i]['total']);
      	$objPHPExcel->getActiveSheet(0)->setCellValue('S' . ($i + 2), $orders[$i]['typename']);      	      	
      	$objPHPExcel->getActiveSheet(0)->setCellValue('T' . ($i + 2), $payflag_array[$orders[$i]['payflag']]);
      	$objPHPExcel->getActiveSheet(0)->setCellValue('U' . ($i + 2), $orders[$i]['orderid']);      	
      	$objPHPExcel->getActiveSheet(0)->setCellValue('V' . ($i + 2), $paytype_array[$orders[$i]['paytype']]);	
      } 
             
      $objPHPExcel->getActiveSheet()->setTitle('design_order');

      $objPHPExcel->setActiveSheetIndex(0);
        
      // Redirect Output To A Client’S Web Browser (Excel5)
      header('content-Type: Application/Vnd.Ms-Excel;charset=utf-8;');
      //Header('content-Disposition: Attachment;Filename="Inventory_Log.Xls"');
      header('content-Disposition: Attachment;Filename="Inventory_Log.Csv"');
       
      header('cache-Control: Max-Age=0');
      // If You're Serving To Ie 9, Then The Following May Be Needed
      header('cache-Control: Max-Age=1');
      
      // If You're Serving To Ie Over Ssl, Then The Following May Be Needed
      header ('expires: Mon, 26 Jul 1997 05:00:00 Gmt'); // Date In The Past
      header ('last-Modified: '.Gmdate('d, D M Y H:I:S').' Gmt'); // Always Modified
      header ('cache-Control: Cache, Must-Revalidate'); // Http/1.1
      header ('pragma: Public'); // Http/1.0
        
      //$Objwriter = PHPExcel_IOFactory::Createwriter($Objphpexcel, 'excel5');
      
      $Objwriter = PHPExcel_IOFactory::Createwriter($objPHPExcel, 'CSV');        
      //error_log(json_encode($Objwriter));
      
      $Objwriter->save('php://output');
      exit;    
          					 
  	}

}


		
?>