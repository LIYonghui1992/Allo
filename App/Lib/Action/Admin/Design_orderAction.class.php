<?php

/**

 * 

 * Webshop (网上购物设置)

 *

 */

if(!defined("App")) exit("Access Denied");


class Design_orderAction extends AdminbaseAction {

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
	   $flag=1;
//	   R('Admin/Content/'.ACTION_NAME);
	   $model=new Model();
	   $model->startTrans();
	   $suborder_data=array();
	   $createtime=$_POST['createtime'];
	   if(!empty($createtime)){
		   $createtime=strtotime($createtime);
	   }else{
		   $createtime=time();
	   }
	   $orderid=$_POST['orderid'];
	   if(empty($orderid)){
		   $orderid = $this->build_order_no();//拿到一个微秒的ID
	   }
	   $order_id=$orderid;
	   $suborder_data['status']=$_POST['status'];
	   $suborder_data['country']=$_POST['country'];
	   $suborder_data['createtime']=$createtime;
	   $suborder_data['orderid']=$order_id;
	   $suborder_data['designwinner_id']=$_POST['designwinner_id'];
	   $suborder_data['winner_name']=$_POST['winner_name'];
	   $suborder_data['qty']=$_POST['qty'];
	   $suborder_data['type_name']=$_POST['typename'];
	   $suborder_data['total']=$_POST['price'];//这个是价格

	   $total=floatval(substr($_POST['price'],1));
	   $qty=intval($_POST['qty']);
//	   if(preg_match('//',$price,$m)){
//			$total=$m[0];
//	   }
	   //将价格分离出来$120 为120
	   $price=round($total/$qty,2);
	   $suborder_data['price']="$".$price;
	   $where=array();
	   $where['designwinner_id']=$_POST['designwinner_id'];
	   $where['type_name']=$_POST['typename'];

	   $typeid=$model->table(C('DB_PREFIX')."designwinner_price")->where($where)->getField('id');
	   if(empty($typeid)){
			$typeid='0';
	   }
	   $suborder_data['type_id']=$typeid;
	   $result1=$model->table(C('DB_PREFIX')."design_suborder")->add($suborder_data);
	   if($result1=="false"){
		   $flag=0;
	   }




	   $order_data=array();
	   $order_data['status']='0';
	   $order_data['country']=$_POST['country'];
	   $order_data['userid']=$_SESSION['userid'];
	   $order_data['username']=$_SESSION['username'];
	   $order_data['createtime']=$createtime;
	   $order_data['first_name']=$_POST['first_name'];
	   $order_data['last_name']=$_POST['last_name'];
	   $order_data['company']=$_POST['company'];
	   $order_data['address']=$_POST['address'];
	   $order_data['zip']=$_POST['zip'];
	   $order_data['email']=$_POST['email'];
	   $order_data['phone']=$_POST['phone'];
	   $order_data['designwinner_id']=$_POST['designwinner_id'];
	   $order_data['qty']=$_POST['qty'];
	   $order_data['winner_name']=$_POST['winner_name'];
	   $order_data['address2']=$_POST['address2'];
	   $order_data['price']=$_POST['price'];
	   $order_data['total']=$_POST['total'];
	   $order_data['shipfee']=$_POST['shipfee'];
	   $order_data['payflag']=$_POST['payflag'];
	   $order_data['orderid']=$_POST['orderid'];
	   $order_data['paytype']=$_POST['paytype'];
	   $order_data['city']=$_POST['city'];

	   $where1['orderid']=$order_id;
	   $count=$model->table(C('DB_PREFIX')."design_order")->where($where1)->count();
	   $result2=true;
	   $result3=true;
	   if($count>0){
		//要在原有的基础上加上 地址什么的相同信息作更新
		   $data['updatetime']=time();
		   $data['status']='0';
		   $data['userid']=$_SESSION['userid'];
		   $data['username']=$_SESSION['username'];
		   $add_money=floatval(substr($_POST['price'],1));
		   $original_price=$model->table(C('DB_PREFIX')."design_order")->where($where1)->getField('price');
		   $original_price=floatval(substr($original_price,1));
		   $result_price=$original_price+$add_money;
		   $data['price']="$".$result_price;
		   $original_total=$model->table(C('DB_PREFIX')."design_order")->where($where1)->getField('price');
		   $original_total=floatval(substr($original_total,1));
		   $result_total=$original_total+$add_money;
		   $data['total']="$".$result_total;

		   $result2=$model->table(C('DB_PREFIX')."design_order")->where($where1)->setField($data);

		   if(!empty($_POST['qty'])){
			   $add_qty=intval($_POST['qty']);
			   $result3=$model->table(C('DB_PREFIX')."design_order")->where($where1)->setInc('qty',$add_qty);
		   }

	   }else{
		   //否则创建新的订单
		   $order_data['orderid']=$order_id;
		   $result2=$model->table(C('DB_PREFIX')."design_order")->add($order_data);
	   }
//	   echo $result2." ".$result3;
//	   exit;

	   if(!$result2||!$result3){
			$flag=0;
	   }

//	   echo $result2." ".$result3." ".$flag;
//	   exit;
	   if($flag!=0){
		   $model->commit();
		   $this->success("success");
	   }else{
		   $model->rollback();
		   $this->error("Sorry,save failed!");
	   }

   }
	function build_order_no()
	{
		return date('Ymd') . uniqid();
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
  
  		/* modify 20160115,not care lang
  		if(APP_LANG)if($this->moduleid)$map['lang']=array('eq',LANG_ID);
  		*/
  
  
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
  		
  
      $count = $model->where ( $map )->count ( $id ); 
      if ($count > 0)
      {
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
  			/* modify 20160115,not care lang
  			unset($map['lang']);
  			$map['lang']=LANG_ID;
  			*/
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
            ->setCellValue('N1', 'product_type')
            ->setCellValue('O1', 'qty')
//            ->setCellValue('O1', 'address2')
            ->setCellValue('P1', 'price')           
            ->setCellValue('Q1', 'shipfee') 
            ->setCellValue('R1', 'total')                      
            ->setCellValue('S1', 'type/version')             
            ->setCellValue('T1', 'payflag')
            ->setCellValue('U1', 'orderNo')
            ->setCellValue('V1', 'paytype ')
            ->setCellValue('W1', 'type_id ');
      //winner_name,email,id
		$where=array();
      if($_GET['winner_name'])
        $where['winner_name']=array('eq',$_GET['winner_name']);        
      if($_GET['email'])
        $where['email']=array('eq',$_GET['email']);
      if($_GET['id'])
        $where['id']=array('eq',$_GET['id']);

		$orders=array();
		//不管是按照同一个产品 还是用一个邮箱地址，都不可能有重复的订单号，所以按照订单号查出来的肯定是全部的
      	$order = M('design_order')->where($where)->select();
		$option=array('type_id','type_name','qty');
		foreach($order as $val){
			$order_id=$val['orderid'];
			$suborders=M('design_suborder')->where('orderid =\'' . $order_id . '\'')->select($option);
			foreach($suborders as $value){
				$orders[]=array_merge($val,$value);
			}
		}
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
      	$objPHPExcel->getActiveSheet(0)->setCellValue('N' . ($i + 2), $orders[$i]['type_name']);
      	$objPHPExcel->getActiveSheet(0)->setCellValue('O' . ($i + 2), $orders[$i]['qty']);
//      	$objPHPExcel->getActiveSheet(0)->setCellValue('O' . ($i + 2), $orders[$i]['address2']);
      	$objPHPExcel->getActiveSheet(0)->setCellValue('P' . ($i + 2), $orders[$i]['price']);
      	$objPHPExcel->getActiveSheet(0)->setCellValue('Q' . ($i + 2), $orders[$i]['shipfee']);
      	$objPHPExcel->getActiveSheet(0)->setCellValue('R' . ($i + 2), $orders[$i]['total']);
      	$objPHPExcel->getActiveSheet(0)->setCellValue('S' . ($i + 2), $orders[$i]['typename']);      	      	
      	$objPHPExcel->getActiveSheet(0)->setCellValue('T' . ($i + 2), $payflag_array[$orders[$i]['payflag']]);
      	$objPHPExcel->getActiveSheet(0)->setCellValue('U' . ($i + 2), $orders[$i]['orderid']);
      	$objPHPExcel->getActiveSheet(0)->setCellValue('V' . ($i + 2), $paytype_array[$orders[$i]['paytype']]);
	  	$objPHPExcel->getActiveSheet(0)->setCellValue('W' . ($i + 2), $orders[$i]['type_id']);
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
  	
  	
  	
  	/**
  	** backup, Leo update this function
  	*/
  	function export_order_20161021(){
  		
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

	/**
	 * 删除
	 *
	 */

	function delete(){
		$flag=1;
		$name = MODULE_NAME;
//		$model = M ( $name );
		$model=new Model();
		$model->startTrans();
		$pk = $model->table(C('DB_PREFIX') . 'design_order')->getPk ();
		$id = $_REQUEST [$pk];
		$where['id']=$id;
		$orderid=$model->table(C('DB_PREFIX') . 'design_order')->where($where)->getField("orderid");
		$where1['orderid']=$orderid;
		error_log(time()."delete_order is".$orderid);
		$result2=$model->table(C('DB_PREFIX') . 'design_suborder')->where($where1)->delete();
		$result1=$model->table(C('DB_PREFIX') . 'design_order')->delete($id);
		if(false!==$result1&&false!==$result2){
			$model->commit();
			$flag=1;
		}else{
			$model->rollback();
			$flag=0;
		}

		$model = M ( $name );
		if (isset ( $id )) {
			if($flag==1){
				if(in_array($name,$this->cache_model)) savecache($name);
				if($this->moduleid){
					$fields =  $model->getDbFields();
					//var_dump($fields);
					delattach(array('moduleid'=>$this->moduleid,'id'=>$id));
					if($fields['keywords']){
						$olddata  = $model->field('keywords')->find($id);
						$where['name']=array('in',$olddata['keywords']);
						$where['moduleid']=array('eq',$this->moduleid);
						if(APP_LANG)$where['lang']=array('eq',LANG_ID);
						M('Tags')->where($where)->setDec('num');
						M('Tags_data')->where("id=".$id)->delete();
					}
				}
				if($name=="Language"){
					savecache('Language');
				}

				if($name=="Country"){
					savecache('Country');
				}

				if($name=="Product_model"){
					savecache('Product_model');
				}

				if($name=="Product_group"){
					savecache('Product_group');
				}

				if($name=="Product"){
					savecache('Product');
				}

				if($name=="Version"){
					savecache('Version');
				}

				if($name=="Design"){
					savecache('Design');
				}

				if($name=="Design_years"){
					savecache('Design_years');
				}
				if($name=="Design_winner"){
					savecache('Design_winner');
				}

				if($name=="Download"){
					savecache('Download');
				}

				if($name=="Download_two"){
					savecache('Download_two');
				}

				if($name=='Order')M('Order_data')->where('order_id='.$id)->delete();
				$this->success(L('delete_ok'));
			}else{
				$this->error(L('delete_error').': '.$model->getDbError());
			}
		}else{
			$this->error (L('do_empty'));
		}
	}

	/**
	 * 批量删除
	 *
	 */

	function deleteall(){
		$flag=1;
		$name = MODULE_NAME;
		$model=new Model();
		$model->startTrans();
//		$model = M ( $name );
		$ids=$_POST['ids'];
		if(!empty($ids) && is_array($ids)){
			foreach($ids as $id){
				$where['id']=$id;
				$orderid=$model->table(C('DB_PREFIX') . 'design_order')->where($where)->getField("orderid");
				$where1['orderid']=$orderid;
				error_log(time()."delete_order is".$orderid);
				$result2=$model->table(C('DB_PREFIX') . 'design_suborder')->where($where1)->delete();
				$result1=$model->table(C('DB_PREFIX') . 'design_order')->delete($id);
				if(false!==$result1&&false!==$result2){
					$flag=1;
				}else{
					$flag=0;
				}
			}
			if($flag==1){
				$model->commit();
			}else{
				$model->rollback();
			}

			$id=implode(',',$ids);
//			if(false!==$model->delete($id)){
			$model = M ( $name );
			if($flag==1){
				if(in_array($name,$this->cache_model)) savecache($name);
				if($this->moduleid){
					$fields =  $model->getDbFields();
					delattach("moduleid=$this->moduleid and id in($id)");
					if($fields['keywords']){
						$olddata  = $model->field('keywords')->where("id in($id)")->select();
						foreach((array)$olddata as $r){
							$where['name']=array('in',$r['keywords']);
							$where['moduleid']=array('eq',$this->moduleid);
							if(APP_LANG)$where['lang']=array('eq',LANG_ID);
							M('Tags')->where($where)->setDec('num');
						}
						M('Tags_data')->where("id in($id)")->delete();
						M('Tags')->where('num<=0')->delete();
					}
				}
				if($name=='Order')M('Order_data')->where('order_id in('.$id.')')->delete();
				$this->success(L('delete_ok'));
			}else{
				$this->error(L('delete_error').': '.$model->getDbError());
			}
		}else{
			$this->error(L('do_empty'));
		}
	}
}


		
?>