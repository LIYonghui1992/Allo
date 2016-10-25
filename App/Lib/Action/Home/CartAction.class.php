<?php

/**

 * 

 * CartAction.class.php 

 *
 
 */
ini_set( "error_log",  "D:/tmp/logs/".date('d').".log");//设置保存错误日志的地址
require_once(ROOT.'/Stripe/init.php');
if(!defined("App")) exit("Access Denied");

class CartAction extends BaseAction
{
	/**
	 * add to cart
	 */
	public function updateCart()
	{

		if (IS_POST) {
			$productid = intval($_POST['productid']);
			$typeid = intval($_POST['typeid']);//最小的分类是类型id  在这里如果是一个空值传进来了(没有子型号)，那么强制转换成int后会变成0
			$count = intval($_POST['qty']);

			$id = "$productid" . '-' . "$typeid";
			if ($_POST['action'] == 'add') {
				$num = $this->addcart($id, $count);
			} else if ($_POST['action'] == 'update') {
				$this->modifycart($id, $count, 2);
//				if (isset($_POST['price'])) {
//					$price = $_POST['price'];
//				}
//				$price = floatval(substr($price, 1));
//				$totalprice = $this->getTotal($price, $count);//这里算出的是单条商品的价格总和
//				$this->ajaxReturn($totalprice);
			} else if ($_POST['action'] == 'del') {
				$this->modifycart($id, '', 3);
			}

		}
		$this->cookieQty();
	}

	/**
	 * 计算购物车中商品的数量
	 */
	public function cookieQty()
	{
		$qty = 0;
		if (cookie('cart')) {
			if (count(cookie('cart'))) {
				$cartarry = cookie('cart');
				foreach ($cartarry as $k => $val) {
					$qty += $val;
				}
			}
		}
		cookie("qty", $qty,C('COOKIE_TIME'));
		$this->ajaxReturn($qty);
	}

	/**
	 * 添加商品到购物车
	 *
	 * @param string $id 商品的编号
	 * @param int $count 商品数量
	 * @return 如果商品存在，则进行数量上的增加$count，并返回1
	 */
	public function addcart($id, $count)
	{
		if (cookie('cart')) {
			$cartarray = cookie('cart');
		}
		if ($this->checkitem($id)) { // 检测商品是否存在
			$this->modifycart($id, $count, 0); // 商品数量加$count
			return 1; //根据返回的是1判断这个商品是存在的，所以数量不需要再加了，否则则加1
		}
		$cartarray[$id] = $count;
		cookie("cart", $cartarray, C('COOKIE_TIME'));
		return 0;
	}

	/**
	 * 修改购物车里的商品
	 *
	 * @param string $id 商品编号
	 * @param int $count 商品数量
	 * @param int $flag 修改类型 0：加 1:减 2:修改 3:清空
	 * @return 如果修改失败，则返回false
	 */
	public function modifycart($id, $count, $flag = "")
	{
		if (cookie('cart')) {
			$cartarray = cookie('cart');
		}
		$tmparray = &$cartarray;  // 传递引用
		if (!is_array($tmparray)) return false;
		foreach ($tmparray as $k => $val) {
			if ($k === $id) {
				switch ($flag) {
					case 0: // 添加数量             查看购物车页面点加号
						$tmparray[$id] += $count;
						break;
					case 1: // 减少数量				查看购物车时点减号
						$tmparray[$id] -= $count;
						break;
					case 2: // 修改数量    			查看购物车时直接改数字
						if ($count == 0) {
							unset($tmparray[$k]);
							break;
						} else {
							$tmparray[$k] = $count;
							break;
						}
					case 3: // 清空商品            存在这个类型的商品，则把这个类型的释放掉
						unset($tmparray[$k]);
						break;
					default:
						break;
				}
			}
		}
		cookie("cart", $tmparray, C('COOKIE_TIME'));
	}


	/**
	 * 检查购物车商品是否存在
	 *
	 * @param string $id 商品编号
	 * @return bool 如果存在 true 否则false
	 */
	private function checkitem($id)
	{
		if (cookie('cart')) {
			$cartarray = cookie('cart');
		}
		if (!is_array($cartarray)) return false;
		foreach ($cartarray as $k => $val) {
			if ($k == $id) {
				return true;
			}
		}
		return false;
	}
	//////////////////////////////////////////////////////////////////////////////
	/**
	 * 查看购物车页面主模板
	 *
	 */

	public function index01()
	{
		$cart = array();
		if (cookie('cart')) {
			//在这里进行排序 则在购物车显示中可以按照同种产品给排列出来
			$cart = cookie('cart');
			ksort($cart);
		}
		$cartlist = array();
		//把所有数据都拿出来 然后和查找到的数据一起 最后分配到前端的模板上
		foreach ($cart as $k => $v) {
			$item = array();
			//product_socket_img ,title,price,total,typepic,typeid
			$item['qty'] = $v;
			$productid = explode('-', $k)[0];
			$typeid = explode('-', $k)[1];
			$item['productid'] = $productid;
			$product = M('design_winner')->where('id=' . $productid)->find();
			$item['background_img'] = $product['background_img'];
			$item['title'] = $product['title'];
			$item['price'] = $product['price'];
			$item['typeid'] = $typeid;
			$item['weight'] = $product['weight'];

			//typeid不为0则有多种子产品  typeid 为0说明没有子产品
			if ($typeid != 0) {
				$version = M('designwinner_price')->where('id=' . $typeid)->find();
				$item['type_pic'] = $version['type_pic'];
				$item['typetitle'] = $version['type_name'];

				$item['price'] = $version['price'];

				$item['total'] = $this->getTotal($item['price'], $v);

			} else {
				$item['total'] = $this->getTotal($item['price'], $v);
			}
			$cartlist[] = $item;
		}
		$this->assign('cartlist', $cartlist);
		$this->display('index');
	}

	/**
	 * new index function
	 */
	public function index(){
		$cart = array();
		$cart_save=array();
		if (cookie('cart')) {
		//在这里进行排序 则在购物车显示中可以按照同种产品给排列出来
			$cart = cookie('cart');
			ksort($cart);
		}
//		dump($cart);
		//carlist 用来记录产品都有哪些 用于规定左侧address 地址框的高度 和右侧的的productlist的高度相同
//		$cartlist = array();
		//把所有数据都拿出来 然后和查找到的数据一起 最后分配到前端的模板上
		//$cart 里面的元素 productid-typeid=>qty
		foreach ($cart as $k => $v) {
			$item = array();
			//product_socket_img ,title,price,total,typepic,typeid
			$productid = explode('-', $k)[0];
			$typeid = explode('-', $k)[1];
			$product = M('design_winner')->where('id=' . $productid)->find();
			$item['qty'] = $v;
			$item['productid'] = $productid;
			$item['background_img'] = $product['background_img'];
			$item['expanded_img']=$product['expanded_img'];
			$item['title'] = $product['title'];
			$item['price'] = $product['price'];
			$item['typeid'] = $typeid;
			$item['weight'] = $product['weight'];
//			$cartlist[$productid]=$productid;
		//typeid不为0则有多种子产品  typeid 为0说明没有子产品
			if ($typeid != 0) {
				$version = M('designwinner_price')->where('id=' . $typeid)->find();
				$item['type_pic'] = $version['type_pic'];
				$item['background_img'] = $version['big_pic'];
				$item['expanded_img'] = $version['big_pic'];
				$item['typetitle'] = $version['type_name'];
//				$item['big_pic']=$version['big_pic'];
				$item['price'] = $version['price'];
			}
			//单条商品的价格
			$item['total'] = $this->getTotal($item['price'], $v);
//			$cartlist[] = $item; //这里面存的是单条的商品信息
			$cart_save[$productid][$typeid] = $item;
//			$cart_save=$this->integrate_arr($productid,$typeid,$item,$cart_save);//这里面存的是整合后的Productid数组

		}
//		var_dump($cart_save);
		$Model = new Model();
		//拿到id和国家名字
		$sql = 'select id, country_name as title from a_ship_price_new group by country_name';
		$country_list = $Model->query($sql);
		$this->assign('cart_save',$cart_save);
		$this->assign('country_list', $country_list);
		$this->display('index');
	}

	/**
	 * @param $price 单价
	 * @param $qty     数量
	 * @return mixed
	 */
	function getTotal($price, $qty)
	{
		/*
        if(C('INCL_TAX')=='0')
          return round($qty * ($this->getPrice($price))* C('TAX_PERCENT'),2);
          */

		return floatval($qty) * ($this->getPrice_n($price));
	}

	function getPrice_n($price)
	{
		//get first numric char
		$num = '';

		for ($i = 0; $i < strlen($price); $i++) {
			$char = substr($price, $i, 1);
			if ($char == '0' || $char == '1' || $char == '2' || $char == '3' || $char == '4' || $char == '5'
				|| $char == '6' || $char == '7' || $char == '8' || $char == '9'
			) {
				$num = substr($price, $i);
				break;
			}
		}

		return $num;
	}

	function build_order_no()
	{
		return date('Ymd') . uniqid();
	}

	//get num from string,like $55.6=>55.6
	function getPrice($price)
	{
		$price = substr($price, 1);
		//get first numric char
		$num = '';

		for ($i = 0; $i < strlen($price); $i++) {
			$char = substr($price, $i, 1);
			if ($char == '0' || $char == '1' || $char == '2' || $char == '3' || $char == '4' || $char == '5'
				|| $char == '6' || $char == '7' || $char == '8' || $char == '9'
			) {
				$num = substr($price, $i);
				break;
			}
		}

		return $num;
	}


	public function getOrderList()
	{
		if (IS_POST) {
			$email = trim($_POST['email']);
			$timestamp = time() - 7776000;//90*24*3600;
			$where['createtime'] = array('gt', $timestamp);
			$where['email'] = array('eq', $email);
			$orderlist = M('design_order')->where($where)->select();
			if (empty($orderlist)) {
				$data = array('stauts' => 0);
				echo json_encode($data);
				exit;
			}

			foreach ($orderlist as $k => $order) {
				$wheredetail['orderid'] = array('eq', $order['orderid']);
				$orderlist[$k]['createtime'] = date('Y-m-d H:i:s', $orderlist[$k]['createtime']);
				$orderdetail = M('design_orderdetail')->where($wheredetail)->select();
				$orderlist[$k]['detail'] = $orderdetail;
			}

			//error_log(json_encode($orderlist));
			$data = array('stauts' => 1, 'orderlist' => $orderlist);
			echo json_encode($data);
			exit;

		}
	}



//    public function updateCart()
//    {
////		$this->ajaxReturn("这里");
//    	if (IS_POST)
//    	{
//    		$productid = intval($_POST['productid']);
//    		$typeid = intval($_POST['typeid']);
//    		if($_POST['action']=='add')
//    		{
//    			$qty = intval($_POST['qty']);
//
//    			$exist = 0;
//    			if(session('cart'))
//    			{
//    				$cart = session('cart');
//    				foreach ($cart as $k=>$v)
//    				{
//    					if($v['productid']==$productid && $v['typeid']==$typeid)
//    					{
//    						$exist = 1;
//    						$qty = intval($v['qty']) + $qty;
//    						$cart[$k]['qty'] =  $qty;
//    					}
//    				}
//
//    				if($exist == 0)
//    				{
//    					  $cart[] = array('productid'=>$productid,'qty'=>$qty,'typeid'=>$typeid);
//    				}
//
//
//    			}
//    			else
//    			{
//    					  $cart[] = array('productid'=>$productid,'qty'=>$qty,'typeid'=>$typeid);
//    			}
//
//    			$total = 0;
//    			foreach ($cart as $k=>$v)
//    			{
//    				$total = intval($v['qty']) + $total;
//    			}
//    			session('qtytotal',$total);
//    			//error_log(json_encode($cart));
//    			session('cart',$cart);
//
//    			$data = array('stauts' => 1, 'qty' => $total);
//
//
//  				echo json_encode($data);
//
//  				exit ;
//
//    		}
////
////
//    		if($_POST['action']=='update')
//    		{
//  				$qty = intval($_POST['qty']);
//
//    			if(session('cart'))
//    			{
//    				$cart = session('cart');
//    				foreach ($cart as $k=>$v)
//    				{
//    					if($v['productid']==$productid && $v['typeid']==$typeid)
//    					{
//    						$cart[$k]['qty'] =  $qty;
//    					}
//    				}
//    				$total = 0;
//    				foreach ($cart as $k=>$v)
//      			{
//      				$total = intval($v['qty']) + $total;
//      			}
//      			session('qtytotal',$total);
//      			//error_log(json_encode($cart));
//      			session('cart',$cart);
//
//      			//if typeid not null, find price of type
//      			if($typeid == -1)
//      			{
//      				$product = M('design_winner')->where('id='.$productid)->find();
//      				$price = $product['price'];
//      			}
//
//      			if($typeid != -1)
//      			{
//      				$version = M('designwinner_price')->where('id='.$typeid)->find();
//      				$price = $version['price'];
//      			}
//
//
//      			$data = array('stauts' => 1, 'qty' => $total,'subtotal'=>$this->getTotal($price,$qty));
//      			//error_log(json_encode($data));
//  				  echo json_encode($data);
//  				  exit ;
//      		}
//
//    		}
//
//    		if($_POST['action']=='del')
//    		{
//    			if(session('cart'))
//    			{
//    				$cart = session('cart');
//    				$newcart = array();
//    				foreach ($cart as $k=>$v)
//    				{
//    					if($v['productid']!=$productid || $v['typeid']!=$typeid )
//    					{
//    						$newcart[] = $v;
//    					}
//    				}
//
//
//    				$total = 0;
//    				foreach ($newcart as $k=>$v)
//      			{
//      				$total = intval($v['qty']) + $total;
//      			}
//      			session('qtytotal',$total);
//      			//error_log(json_encode($cart));
//      			session('cart',$newcart);
//      			$data = array('stauts' => 1, 'qty' => $total);
//  				  echo json_encode($data);
//  				  exit ;
//      		}
//
//
//
//    		}
//
//    	}
//    }


	//check stock,and change stock qty, if not paid, update stock
	function checkStock($cart, &$msg)
	{

		$model = new Model();
		$model->startTrans();


		$flag = 1;
		foreach ($cart as $k => $v) {
			if (intval($v['typeid']) == -1) {
				$where['stock'] = array('egt', intval($v['qty']));
				$where['id'] = intval($v['productid']);
				$hid = $model->table(C('DB_PREFIX') . 'product')->where($where)->setDec('stock', intval($v['qty']));

				$temp = $model->table(C('DB_PREFIX') . 'product')->where("safe_stock > stock and id=" . $where['id'])->count();
				if (!empty($temp))
					$this->remind_stock($where['id'], 1);
			} else {
				$where['stock'] = array('egt', intval($v['qty']));
				$where['id'] = intval($v['typeid']);
				$hid = $model->table(C('DB_PREFIX') . 'version')->where($where)->setDec('stock', intval($v['qty']));

				$temp = $model->table(C('DB_PREFIX') . 'version')->where("safe_stock > stock and id=" . $where['id'])->count();
				if (!empty($temp))
					$this->remind_stock($where['id'], 2);
			}

			//$模型->setDec('score','id=5',5); // 积分减5  setField
			if (!$hid && $hid < 1) {
				$flag = 0;
				$productid = $v['productid'];
				$typeid = intval($v['typeid']);

				break;
			}


			//$list = $User->lock(true)->where('status=1')->order('create_time')->limit(10)->select();

		}


		if ($flag == 1)
			$model->commit();
		else {
			$model->rollback();
			$product = M('product')->where('id=' . $productid)->find();
			$msg = L('STOCK_NO_ENOUGH') . $product['title'];

			if ($typeid != -1) {
				$version = M('version')->where('id=' . $typeid)->find();
				$msg = L('STOCK_NO_ENOUGH') . $product['title'] . ',' . L('COLOR') . ':' . $version['title'];
			}

		}


		return $flag;
	}


	public function checkVersionStock()
	{
		if (IS_POST) {
			$status = 1;
			$typeid = $_POST['typeId'];
			$version = M('version')->where('id=' . $typeid)->find();
			if (empty($version)) {
				$status = 0;
			} else if (intval($version['stock']) <= 0) {
				$status = 0;
			}

			$rs = array('stauts' => $status);

			//error_log('checkVersionStock,status'.$status);
			echo json_encode($rs);
			exit;
		}
	}


	//write record to table design_order and design_orderdeail,and record orderid to session,
	//when submit,update table design_order by orderid
	//calculate total price ,write to session	，include total weight
	public function insertOrder($cart, &$msg)
	{
		//添加一个标示，如果数据库写入有任何错误 则事务回滚
		$flag = 1;
		$model = new Model();
//		$m=M('design_order');
		$model->startTrans();
		//我需要存一个键值数组 关于单条订单总价格和订单号与产品id的关系，便于通过运费来计算每条订单总价格 然后存到对应的订单中
		$order_qty_arr = array();
//		$order_weight_arr = array();
		$order_id_arr = array();
//		$order_total_arr = array();
		$allo_pay_total=0;

		//这里要判断 如果productid是相同的则生成一个订单数组array($orderid,$order_total)
		foreach ($cart as $k => $val) { //$val=array('productid'=>'1','typeid'=>'0',....)
			//每遍历一次就产生一个订单
			$order_id = $this->build_order_no();//拿到一个微秒的ID
			$orderid = $order_id;
			$time = time();
//			$productid = $k;
			//这里需要处理一下
			$ids=explode('--', $k);
			$productid=$ids[0];
			$productfee=$ids[1];
			$shipfee=$ids[2];
			$totalfee=$ids[3];

			//根据productid 拿到存的地址信息
			$address_infoid="address_info"."-".$productid;
			$address_info=cookie($address_infoid);

			$winner_name="";
//			每条订单的所有产品的数量和
			$order_qty = 0;
			//每条订单的所有商品价格和
//			$order_total = 0;
			//每条订单的所有商品重量和
//			$order_weight = 0;


			foreach ($val as $data1) {
				$suborder_data=array();
				//这里遍历之后拿到的就是最底层的array，也就是每一个订单的数据
				//而我只需要在前面的过程中 将这个三维数组中的数据给准备齐即可
				//要添加同一个orderid作为订单号
				//总数据 createtime updatetime orderid designwinner_id winner_name winner_text qty type_id type_name price_id price total
				$suborder_data['winner_name'] = $data1['title'];
				$suborder_data['designwinner_id'] = $data1['productid'];
				$suborder_data['type_id'] = $data1["typeid"];
				$suborder_data['qty'] = $data1['qty'];
				$suborder_data['price'] = $data1['price'];//每条产品的单价
				$suborder_data['total'] ="$".$this->getTotal($data1['price'],$data1['qty']); //每条产品的价格
				$suborder_data['weight'] = $data1['weight'];
				$suborder_data['orderid'] = $orderid;
				$suborder_data['createtime'] = $time;
				$suborder_data['type_name'] = $data1['typetitle'];

				$winner_name=$data1['title'];

				error_log("这里的".$shipfee."price:".$productfee."total".$totalfee."dataarr=".json_encode($suborder_data)) ;
				$hid1 = $model->table(C('DB_PREFIX') . 'design_suborder')->add($suborder_data);
				if (!$hid1 || $hid1 < 1) {
					$flag = 0;
				}
				error_log("这里hid1为".$hid1);
//				echo "这里hid1为".$hid1."<br>";
//				$order_total += $data1['total'];//单条订单一个产品的所有类型商品价格和
				//这个订单的所有产品的数量和
				$order_qty += $data1['qty'];
				//这个产品的所有类型的重量和
//				$order_weight += $data1['weight'];
			}
			$data = array(
				'orderid' => $order_id,
				'first_name' => $address_info[1],
				'last_name' =>$address_info[2],
				'phone' => $address_info[3],
				'zip' => $address_info[9],
				'email' => '',
				'company' =>$address_info[4],
				'address' => $address_info[5],
				'designwinner_id'=>$productid,
				'qty'=>$order_qty,
				'winner_name'=>$winner_name,
				'city' => $address_info[6],
				'country' => $address_info[7],
				'price'=>"$".$productfee,
				'shipfee' => "$".$shipfee,//trim($_POST['shipfee']),
				'total' => "$".$totalfee,
				'lang' => LANG_ID,
				'payflag' => 0,
				'status' => 0,
				'paytype' => 9,
				'createtime' => time());
			$hid = $model->table(C('DB_PREFIX') . 'design_order')->add($data);
			if (!$hid || $hid < 1) {
				$flag = 0;
			}
			error_log("这里hid".$hid);
//			echo "这里hid".$hid."<br>";
			//每一个产品所对应的 所有类型商品的价格总和重量总和 订单编号

			$order_id_arr[$productid] = $orderid;
//			$order_total_arr[$productid] = $order_total;
			$order_qty_arr[$productid] = $order_qty;
//			$order_weight_arr[$productid] = $order_weight;
			$allo_pay_total+=$totalfee;
		}

		if ($flag == 0) {
			$model->rollback();
			$msg = 'Sorry, Something wrong, please delete your browser cookies and try again later!';
			return false;
		} else {
			$model->commit();
		}
		cookie('order_id_arr', $order_id_arr, C('COOKIE_TIME'));
		cookie('allo_pay_total',$allo_pay_total,C('COOKIE_TIME'));
//		cookie('order_weight_arr', $order_weight_arr, 3600);
//		cookie('order_total_arr', $order_total_arr, 3600);
		cookie('order_qty_arr', $order_qty_arr, C('COOKIE_TIME'));
//		var_dump($order_qty_arr);
		return 1;
	}

	/**
	 * @param $productid
	 * @param $typeid
	 * @param $data
	 * @param $cart_save
	 * @return array $cart_save a three dimension array about unique productid(one order)
	 */
	public function integrate_arr($productid,$typeid,$data,$cart_save){
		$status = 0;
		if (count($cart_save) > 0) {
			foreach ($cart_save as $k => $val) {
				if ($k == $productid) {
					$cart_save[$productid][$typeid] = $data; //如果是一个productid则加入
					$status = 1;//说明找到了
				}
			}
			if ($status == 0) { //说明没找到 就新建一个
				$cart_save[$productid][$typeid] = $data;
			} else {
				$status = 0;// 重置为0
			}
		} else {
			//$cart_save空数组 新建一个
			$cart_save[$productid][$typeid] = $data;
		}
		return $cart_save;
	}
	/**
	 * pay
	 * 1.分配数据库表单数据
	 * 2.分配供用户填写的字段
	 * 3.支付接口？？
	 */
	public function checkout()
	{

//		$data = array(); //用来存写入数据库数据
//		$cart_save = array();//这个数组用于存$data

		$cart = array(); //用于存$item
		$cartinfo = array();
		if (isset($_POST)) {
			$cartinfo = $_POST;
		}
//		dump($cartinfo);
//		$this->display();
//		exit;
		//存选中的购物信息
		$cart_list = array();
		//遍历产生二维商品信息数组
		// '3--6' => string '3--6--$100--2--200--5' (length=21)
		foreach ($cartinfo as $v) //$v=14-0-2-150
		{
			$item = array(); //用来存显示数据
			$ids = explode('--', $v); //得到分割后的数组 $ids=(productid,typeid,price,qty,productfee,shipfee)
			$productid = $ids[0];
			$typeid = $ids[1];
			$price = $ids[2];
			$qty = $ids[3];
			$productfee=$ids[4];//一条订单里产品的费用
			$shipfee=$ids[5];//单条订单里运费
			$totalfee=floatval($productfee)+floatval($shipfee);//单条订单的产品+运费
			$product = M('design_winner')->where('id=' . $productid)->find();
			$item['title'] = $product['title'];
			$item['background_img'] = $product['background_img'];
			$item['expanded_img']=$product['expanded_img'];
			$item['productid'] = $productid;
			$item['typeid'] = $typeid;
			$item['qty'] = $qty;
			$item['price'] = $price; //前端传过来的价格肯定是与物品对应的价格
			$item['weight'] = $product['weight'] * $item['qty'];//用户购买的所有数量的这个产品的总重量和
			//如果是有子类型 则用子类型里面的图片和title
			$item['type_pic'] = $product['background_img'];
			$item['typetitle'] = "";
			if ($typeid != 0) {
				$version = M('designwinner_price')->where('id=' . $typeid)->find();
				$item['type_pic'] = $version['type_pic'];
				$item['background_img'] = $version['big_pic'];
				$item['expanded_img'] = $version['big_pic'];
				$item['typetitle'] = $version['type_name'];
				$item['weight'] = $version['weight'] * $item['qty'];
			}

			/**
			 * 要存到数据库 design_suborder表中的数据
			 */
			//总数据 createtime updatetime orderid designwinner_id winner_name winner_text qty type_id type_name price_id price total
			//还少  updatetime orderid  winner_text type_name price_id price
//			$cart_save=$this->integrate_arr($productid,$typeid,$item,$cart_save);
			$orderid=$productid."--".$productfee."--".$shipfee."--".$totalfee;
//			$cart=$this->integrate_arr($orderid,$typeid,$item,$cart);
			$cart[$orderid][$typeid]=$item;
			//记录已经选中购买的
			$id = "$productid" . '-' . "$typeid";
			$cart_list[$id] = $qty;
		}
		cookie("cart_list", $cart_list, C('COOKIE_TIME'));//选中付款的列表 只包含$productid-$typeid=>qty
//		dump($cart);


		// 在这里核查 是否用户填写的地址与用户点击提交计算用的地址不一致
		// 这里检查 防止假地址  实际计费地址以多个页面最后用户选中的地址为准计费，而非点击checkout页面的当前地址
		// 返回一个审核后的cart

		$cart=$this->checkSPrice($cart);
//		dump($cart);

		// 将订单数据和子订单数据插入到数据库表中
//		$result = $this->insertOrder($cart_save, $msg);
		$result = $this->insertOrder($cart, $msg);
		if ($result != 1) {
			$this->assign('tip', $msg);
			R('/Cart/index');
			return;
		}




		//update session cart
		$this->assign('cartlist', $cart);
//		$this->assign('allo_total', $totalprice);
//		$this->assign('qty', $totalqty);
		$this->display();
	}



	public function shipPrice_single()
	{
		if (IS_POST) {
			$info = $_POST['info'];
//			$this->ajaxReturn(json_encode($info));
//			exit;
			$total_weight = 0;
			//$info 里面存着多条productid-typeid-price-qty-countryid
			$ids = explode('-', $info); //得到分割后的数组 $ids=(productid,typeid,price,qty,countryid)
//    		if(sizeof($ids)< 2)
//    		  break;
			$productid = $ids[0];
			$typeid = $ids[1];
			$price = $ids[2];
			$qty = $ids[3];
			$countryid = $ids[4];
			$ship_price = M('ship_price_new')->where(array('id' => $countryid))->find();
			if (empty($ship_price)) {
				$rs = array('stauts' => 0, 'msg' => 'Sorry, We Cannot ship product to your country now!');
				echo json_encode($rs);
				exit;
			}
			$product = M('design_winner')->where('id=' . $productid)->find();
			if ($typeid != 0) {
				$version = M('designwinner_price')->where('id=' . $typeid)->find();
				$total_weight += $version['weight'] * $qty;//本条产品的重量 遍历要把这个订单的重量都加
			} else {
				$total_weight += $product['weight'] * $qty;//本条产品的重量 遍历要把这个订单的重量都加
			}
			//实际上是重量决定了运费
			$base = intval($ship_price['first']);//首重
			$additional = intval($ship_price['additional']);
			$firstPrice = $ship_price['price'];
			$additionalPrice = $ship_price['additional_price'];
			if ($total_weight <= $base)
				$shipfee = substr($firstPrice, 1);
			else {
				$pcs = ceil(($total_weight - $base) / $additional);

				$shipfee = intval(substr($additionalPrice, 1) * $pcs) + intval(substr($firstPrice, 1));
			}
			$this->ajaxReturn($shipfee);
		}
	}
	/**
	 * 运费计算
	 * 根据选择的国家的不同，计算运费
	 * 已知：
	 * 1.单条订单的商品费用 根据cookie('order_arr')拿到对应的单条订单的费用
	 * 2.未加运费总的商品的价格之和
	 * 未知：
	 * 2.单条订单的运费？还是单条商品的运费
	 * 3.总的运费=单条运费之和
	 * 4.总的运费加总的商品的价格之和
	 *
	 * 另一种情况： 用于核查 计算由参数传过来的值
	 */
	public function shipPrice()
	{

		if (IS_POST) {
			$info = $_POST['info'];
//			$this->ajaxReturn(json_encode($info));
//			exit;
			$total_weight=0;
			//$info 里面存着多条productid--typeid--price--qty--countryid
			$ship_price=array();
			foreach($info as $v){

				$ids = explode('--', $v); //得到分割后的数组 $ids=(productid,typeid,price,qty,countryid)
//    		if(sizeof($ids)< 2)
//				break;
				$productid = $ids[0];
				$typeid = $ids[1];
				$price = $ids[2];
				$qty = $ids[3];
				$countryid=$ids[4];
//				$this->ajaxReturn($countryid);
//				exit;
				$ship_price = M('ship_price_new')->where(array('id' => $countryid))->find();
				if (empty($ship_price)) {
					$rs = array('stauts' => 0, 'msg' => 'Sorry, We Cannot ship product to your country now!');
					echo json_encode($rs);
					exit;
				}
				$product = M('design_winner')->where('id=' . $productid)->find();
				if($typeid!=0){
					$version = M('designwinner_price')->where('id=' . $typeid)->find();
					$total_weight += $version['weight'] * $qty;//本条产品的重量 遍历要把这个订单的重量都加
				}else{
					$total_weight += $product['weight'] * $qty;//本条产品的重量 遍历要把这个订单的重量都加
				}
			}
			//实际上是重量决定了运费
//			$base = intval($ship_price['first']);//首重
//			$additional = intval($ship_price['additional']);
//			$firstPrice = $ship_price['price'];
//			$additionalPrice = $ship_price['additional_price'];
//			if ($total_weight <= $base)
//				$shipfee = substr($firstPrice, 1);
//			else {
//				$pcs = ceil(($total_weight - $base) / $additional);
//
//				$shipfee = intval(substr($additionalPrice, 1) * $pcs) + intval(substr($firstPrice, 1));
//			}
			$base = floatval($ship_price['first']);//首重
			$additional = floatval($ship_price['additional']);
			$firstPrice = $ship_price['price'];
			$additionalPrice = $ship_price['additional_price'];
			if ($total_weight <= $base)
				$shipfee = substr($firstPrice, 1);
			else {
				$pcs = ceil(($total_weight - $base) / $additional);

				$shipfee = floatval(substr($additionalPrice, 1) * $pcs) + floatval(substr($firstPrice, 1));
			}
			$this->ajaxReturn($shipfee);
		}


	}

	public function checkSPrice($cart=[]){
		$new_cart=array();
		if(!empty($cart)){
			foreach($cart as $k=>$val){
				$ids=explode("--",$k);
				$productid=$ids[0];//必须
				$address_infoid="address_info"."-".$productid;
				$info=cookie($address_infoid);
				$countryid=$info[8];//必须

				$product_fee=$ids[1];
				$ship_fee=$ids[2];
//			$total_fee=$ids[3];
				$total_weight=0;
				$ship_price=array();
				foreach ($val as $key=>$value){
					$typeid=$key;//必须
					$qty=$value['qty'];//必须
					$ship_price = M('ship_price_new')->where(array('id' => $countryid))->find();
					if (empty($ship_price)) {//选中的国家 表里面没有
						$rs = array('stauts' => 0, 'msg' => 'Sorry, We Cannot ship product to your country now!');
						error_log("check_ship_fee".json_encode($rs));

//					echo json_encode($rs);
						exit;
//					return $cart;
					}
					$product = M('design_winner')->where('id=' . $productid)->find();
					if($typeid!=0){
						$version = M('designwinner_price')->where('id=' . $typeid)->find();
						$total_weight += $version['weight'] * $qty;//本条产品的重量 遍历要把这个订单的重量都加
					}else{
						$total_weight += $product['weight'] * $qty;//本条产品的重量 遍历要把这个订单的重量都加
					}

				}
				//实际上是重量决定了运费
				$base = floatval($ship_price['first']);//首重
				$additional = floatval($ship_price['additional']);
				$firstPrice = $ship_price['price'];
				$additionalPrice = $ship_price['additional_price'];
				if ($total_weight <= $base)
					$shipfee = substr($firstPrice, 1);
				else {
					$pcs = ceil(($total_weight - $base) / $additional);

					$shipfee = floatval(substr($additionalPrice, 1) * $pcs) + floatval(substr($firstPrice, 1));
				}
				//相同的也倒一遍，不同的则直接就刷新了
				//不然就再次遍历,如果不一致 则只改键即可
				$ship_fee=$shipfee;
				$total_fee=$ship_fee+$product_fee;
				$k=$productid."--".$product_fee."--".$ship_fee."--".$total_fee;
				foreach($val as $key=>$value){
					$new_cart[$k][$key]=$value;
				}

			}

		}
//		dump($new_cart);
		return $new_cart;
	}


	public function save_address(){
//		cookie("address_arr",null);
		$address_arr=array();
		$arr=unserialize(cookie("address_arr"));
//		$this->ajaxReturn(json_encode($arr));
		if(!empty($arr)){//不为空
			$address_arr=$arr;
		}
		error_log("read address arr:".json_encode($arr));

		if(IS_POST){

			if(isset($_POST['stat'])&&$_POST['stat']==1){
				$this->ajaxReturn(json_encode($arr));
			}else if(isset($_POST['stat'])&&$_POST['stat']==2){
				$nowproduct_id=$_POST['nowproduct_id'];//这是是需要新添加的地址
				$product_id=$_POST['product_id'];//这个是选中的地址productid
//				$address_infoid="address_info"."-".$product_id;
//				$info_last=cookie($address_infoid);
//				$info_last[0]=$nowproduct_id;//修改它里面的productid为新的

				//先拿到原始的，点击连接选中的那个地址的信息，这个信息是在address_arr里
				foreach($address_arr as $val){
					if($val[0]==$product_id){
						$info_last=$val;
					}
				}
				$info_last[0]=$nowproduct_id;
				//然后将新的信息存到cookie里对应新的id
				$address_infonowid="address_info"."-".$nowproduct_id;
				cookie($address_infonowid,$info_last,C('COOKIE_TIME'));
				$this->ajaxReturn(cookie($address_infonowid));
				//如果我们在第二个订单的地址位置直接选择第一个订单所用的地址，则这时候就会出现一个问题，第二个订单是可以被写入正确信息的，
				//但是如何确定第二个订单的已选择三个字 显示在第一个地址的右边呢？ 因为第一个地址里面存的Productid是第一个订单的，如何让第二个订单找到这个地址呢？ 难道必须引入一个addressid?（暂时没有要求 显示已选择）

				//现在的问题 就是如何确定 当用户点击添加地址 ，并且地址只有一条的时候 ，如何让第三个订单在第一个地址的右边显示已选择，因为一二三 这三个订单公用一个地址。
			}

			//添加新地址的时候，默认就更新了上次的地址
			$info=$_POST['info'];
			$productid=$info[0];
			$address_info="address_info"."-".$productid;
			cookie($address_info,$info,3600);

//			$firstname=$info[1];
//			$lastname=$info[2];
//			$phone=$info[3];
//			$company=$info[4];
//			$address=$info[5];
//			$city=$info[6];
//			$country=$info[7];//第7个是国家的名字
//			$postcode=$info[9];
//			$str=$company.",".$address.",".$city.",".$country.",".$firstname.",".$lastname.",".$phone.",".$postcode;
			//这里要遍历判断，如果是新的地址就要加进去，不是新的订单就执行更新
			$flag=0;
			foreach($address_arr as &$val){
				if($val[0]==$productid){
					$val=$info;
					$flag=1;
				}
			}
			if($flag==0){ //如果没有这条则加入新的
				$address_arr[]=$info;//二维数组
			}
			error_log("print address arr".json_encode($address_arr));

			cookie("address_arr",serialize($address_arr),C('COOKIE_TIME'));
//			$this->ajaxReturn(json_encode(cookie($address_info)));
			$this->ajaxReturn(json_encode(cookie("address_arr")));
			//前端的地址显示最好改成从cookie里面拿取的，如果cookie过期了 就没有地址了


		}



	}

	/**
	 * 提交订单支付处理
	 * 这里可能涉及到多条订单的处理
	 * 1.根据顾客信息完善数据库表单
	 * 2.将支付信息传给paypal接口执行支付
	 */

	public function submit_order()
	{
		$model = new Model();
		$model->startTrans();
		$flag = 1;
		if (IS_POST) {

			//$design = M('design_winner') -> where('id=' . $_POST['design_id']) -> find();

			//将countryid转化为country
//			$countryid = trim($_POST['countryid']);
//			$info = M('ship_price_new')->where('id=' . $countryid)->find();
//			$country = $info['country_name'];
//			$this->ajaxReturn($countryid." ".$info." ".$country);

			//calculate  total price，according to session data
			$total = cookie('allo_pay_total');
//			$total = cookie('totalprice'); //这里应该是总的要支付得
//			$shipfee = cookie('allo_shipfee');//这里是总运费
//			$price_total = cookie('allo_total');//这里是物品的总价格
			$data = array(
//				'zip' => trim($_POST['zip']),
				'email' => trim($_POST['email']),
				//'winner_name' => '',//$_POST['designwinner_title'],
				'lang' => LANG_ID,
				'payflag' => 0,
				'status' => 0,
				'paytype' => $_POST['paytype'],
				'updatetime' => time());
			//多条订单 通过遍历 ，然后对每一条订单都写入顾客信息
			$order_id_arr = cookie('order_id_arr');
			$orderid = "";
			foreach ($order_id_arr as $k => $val) {
				$order_id = $val;
				$orderid .= $val;

//				$data['designwinner_id']=$k;
				$where['orderid'] = $order_id;
				//存之前先判断当前order_id是否为空 不过如果为空 也找不到数据库记录 所以也不影响 ，但是可以给顾客返回一个错误提示
				$flag = $model->table(C('DB_PREFIX') . 'design_order')->where($where)->save($data);
			}
//      		$order_id =  cookie('allo_order_id');
			//如果order id是存在cookie里 则我要先根据时间进行一个判断 ，不然在表格中存的这条订单 order id 一条就是空的了
			if (!$flag || $flag < 1) {
				$rs = array('status' => 0, 'msg' => 'Submitted Failure!');
				$model->rollback();
				echo json_encode($rs);
				error_log("failed to save email address"."\r\n",3,"/tmp/error/errors.log");
			} else {
				$model->commit();
				error_log("email data have been saved"."\r\n",3,"/tmp/error/errors.log");
//				$this->ajaxReturn('存到数据库成功'.json_encode($record));
			}

//			foreach($order_id_arr as $k=>$val){
//				$order_id=$val;
//				$order_info = M('design_order')->where('orderid =\'' . $order_id . '\'')->find();
//				$this->sendmail($order_info['email'], $order_info);
//			}
			// 因为cookie存在本地所以不能再notify中删除
			//modify order_qty
			$order_qty_arr = cookie('order_qty_arr');
			$qtystr=urlencode(serialize($order_qty_arr));
//			$qtystr="sfsfsfsfsf";
			//遍历购物车 将已经支付了的商品从购物车列表里删掉
			$cartlist = cookie('cart_list');
			foreach ($cartlist as $k => $v) {
				//update session cart
				$id = $k;
				$this->delfromCart($id);
			}



			//paypal
			if ($_POST['paytype'] == 1) {
				//build payment url,return ,then javascript reward to paypal

				//$formurl = 'https://www.paypal.com/cgi-bin/webscr';//正试提交地址
				$gateway = 'https://www.sandbox.paypal.com/cgi-bin/webscr?';//测试提交地址
//				$gateway = C('PAYPAL_GATEWAY');
//				$account = C('PAYPAL_ACCOUNT');
//				$account = 'jing.wang@allocacoc.com.cn';
//				$account = '624696365@qq.com';
				$account = 'arthur.limpens@allocacoc.com';
				$pp_info = array();// 初始化准备提交到Paypal的数据
				$pp_info['cmd'] = '_xclick';// 告诉Paypal，我的网站是用的我自己的购物车系统
				$pp_info['business'] = $account;// 告诉paypal，我的（商城的商户）Paypal账号，就是这钱是付给谁的
				$pp_info['item_name'] = "orderNo:  {$orderid}";// 用户将会在Paypal的支付页面看到购买的是什么东西，只做显示，没有什么特殊用途，如果是多件商品，则直接告诉用户，只支付某个订单就可以了
				$pp_info['amount'] = $total; // 告诉Paypal，我要收多少钱
				$pp_info['currency_code'] = 'USD';
				//$pp_info['currency_code'] = 'USD';// 告诉Paypal，我要用什么货币。这里需要注意的是，由于汇率问题，如果网站提供了更改货币的功能，那么上面的amount也要做适当更改，paypal是不会智能的根据汇率更改总额的
				$pp_info['return'] = 'http://webshop.allocacoc.com/Cart/index.html';//'http://webshop.allocacoc.com/Product/index.html';// 当用户成功付款后paypal会将用户自动引导到此页面。如果为空或不传递该参数，则不会跳转
				$pp_info['invoice'] = $orderid;
				$pp_info['charset'] = 'utf-8';
				$pp_info['no_shipping'] = '1';
				$pp_info['no_note'] = '1';
				$pp_info['cancel_return'] = 'http://webshop.allocacoc.com/Cart/index.html';//'http://webshop.allocacoc.com/Product/index.html';// 当跳转到paypal付款页面时，用户又突然不想买了。则会跳转到此页面
//				$pp_info['notify_url'] = 'http://webshop.allocacoc.com/Cart/paypal_notify/orderid/' . $orderid . '/';
				$pp_info['notify_url'] = 'http://webshop.allocacoc.com/Cart/paypal_notify/orderid/' . $orderid . '/qtystr/'.$qtystr.'/';

				//'http://www.domain.com/index.php/design/paypal_notify/orderid/'.$order_id;// Paypal会将指定 invoice 的订单的状态定时发送到此URL(Paypal的此操作，是paypal的服务器和我方商城的服务器点对点的通信，用户感觉不到）
				//notify_url=http%3A%2F%2Fwebshop.allocacoc.com%2FCart%2Fpaypal_notify%2Fordesrid%2F2016100857f87e4e543c0%2F&rm=2

				$pp_info['rm'] = 2;
				$paypal_payment_url = $gateway . http_build_query($pp_info);
				unset($pp_info);
				$rs = array('status' => 1, 'paymenturl' => $paypal_payment_url);
				echo json_encode($rs);
				exit;
			}


			/*
//			stripe,card
			if ($_POST['paytype'] == 2) {

//				\Stripe\Stripe::setApiKey(C('STRIPE_SK'));
				//\Stripe\Stripe::setApiKey("sk_test_f3jgU2hZxqndFQI7j9qpThSD");
				$token = $_POST['stripetoken'];
				error_log("开始Card支付");
				try {
					$charge = \Stripe\Charge::create(array(
							"amount" => intval($total * 100), // amount in cents, again
//				  	"currency" =>L('CURRENCY_SYMBOL'),//"currency" => "usd",
							"currency" => 'usd',//"currency" => "usd",
							"source" => $token,
							"description" => $orderid)
					);
					error_log(json_encode("charge:".$charge));

					if ($charge['paid'] != true) {

						$rs = array('status' => 0, 'msg' => "fail to pay!");
						error_log(json_encode($rs));
						echo json_encode($rs);
						exit;
					}

					//遍历购物车 将已经支付了的商品从购物车列表里删掉
					$cartlist = cookie('cart_list');
					foreach ($cartlist as $k => $v) {
						//update session cart
						$id = $k;
						$this->delfromCart($id);
					}

					// $ch = \Stripe\Charge::retrieve($charge['id']);
					//$ch->capture();


					//modify payflag
					foreach ($order_id_arr as $k => $value) {
						$order_id = $value;
						//这里需要记录所有的条数据
						$order_info = M('design_order')->where('orderid =\'' . $order_id . '\'')->find();
						$result = M('design_order')->where('orderid=\'' . $order_id . '\'')->setField('payflag', 1);
						if (!$result) {
							error_log("PayFlag modify error: fail to modify flag, order is " . $order_id . "\n");
						}else{
							error_log("PayFlag modify success: success to modify flag, order is " . $order_id . "\n");
							//mail to customer
							//将顾客的个人信息以及所有商品信息Email 发送
							$this->sendmail(trim($_POST['email']), $order_info);
						}
					}

					//modify order_qty
					$order_qty_arr = cookie('order_qty_arr');
					$model = M('design_winner');
					foreach ($order_qty_arr as $k => $val) {
						$product = $model->where('id=' . $k)->find();
						$order_qty = $product['order_qty'];
						$order_qty += $val;
						$result = $model->where('id=' . $k)->setField('order_qty', $order_qty);
						if (!$result) {
							error_log("Design winner quantity modify error: fail to modify order_qty(design_winner), product id is " . $k . "quantity is :" . $val . "\n");
						}else{
							error_log("Design winner quantity modify success:product id is \" . $k . \"quantity is :\" . $val ");
						}
					}

					$rs = array('status' => 1, 'msg' => "succeed to pay!");
					error_log(json_encode("paytype2 have succeed to pay".$rs));
					echo json_encode($rs);
					exit;

				} catch (Exception $e) {
					// The card has been declined
					$rs = array('status' => 0, 'msg' => $e->getMessage());
					error_log("exception".json_encode($rs));
					echo json_encode($rs);
					exit;

				}
			}
			*/
		}

	}

	//delete from cookie, cart
	/**
	 * @param $id 购物车中商品唯一标示
	 * 刷新购物车，已经购买的商品删除
	 */
	function delfromCart($id)
	{
		$cart = cookie('cart');
		$total = 0;
		$newcart = array();
		foreach ($cart as $k => $v) {
			//如果id不同的 则再次存下来放到新购物车里
			if ($k != $id) {
				$newcart[$k] = $v;
				$total += $v;//重新计算新购物车中未购买的商品数量
			}
		}
		cookie('cart', $newcart,C('COOKIE_TIME'));//新的购物车把老的覆盖掉
		cookie('qtytotal', $total,C('COOKIE_TIME'));//便于更新新购物车 Cart/index.html 上的pcs
	}

/*
	public function paypal_notify(){
// read the post from PayPal system and add 'cmd'
//		$req = 'cmd=_notify-validate';
		$req = 'cmd=' . urlencode('_notify-validate');
		foreach ($_POST as $key => $value) {
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}
		error_log("start ipn notify,req is :$req");
		$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header.="Host: www.sandbox.paypal.com\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
//		$header =
//			"POST /cgi-bin/webscr HTTP/1.0\r\n" .
//			"Host: www.sandbox.paypal.com\r\n" .
//			"Content-Type: application/x-www-form-urlencoded\r\n" .
//			"Content-Length: " . strlen($req) . "\r\n";

		$fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30); // 沙盒用
//$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30); // 正式用

// assign posted variables to local variables
		error_log("fp is $fp");

		if (!$fp) {
// HTTP ERROR
		} else {
			fputs ($fp, $header . $req);
			while (!feof($fp)) {
				$res = fgets ($fp, 1024);
				error_log('res is :'.$res);
				if (strcmp ($res, "VERIFIED") == 0) {
					error_log("success");
// check the payment_status is Completed
// check that txn_id has not been previously processed
// check that receiver_email is your Primary PayPal email
// check that payment_amount/payment_currency are correct
// process payment
// 验证通过。付款成功了，在这里进行逻辑处理（修改订单状态，邮件提醒，自动发货等）
				}
				else if (strcmp ($res, "INVALID") == 0) {
// log for manual investigation
// 验证失败，可以不处理。
					error_log("failed");
				}
			}
			fclose ($fp);
		}
	}
	*/

	/**
	 *  recieve notify info from paypal
	 */
//	public function paypal_notify(){
//		$orderid = $_GET['orderid'];
//		error_log("here notify"."\r\n"."and orderid is:".$_GET['orderid'],3,"/tmp/error/errors.log");
//		$order_id_arr = str_split($orderid, 21);
//		error_log("order arr: ".json_encode($order_id_arr)."\r\n",3,"/tmp/error/errors.log");
//	}

	public function paypal_notify()
	{

//		file_put_contents(serialize($_POST), 'post.log');
		//Now you have the post request serialized we can grab the contents and apply it to a variable for fast testing.
		//www.allocacoc.cc/Cart/paypal_notify/orderid/2016091457d9047f5bafc2016091457d9047f5c6e6
		// 由于这个文件只有被Paypal的服务器访问，所以无需考虑做什么页面什么的，这个页面不是给人看的，是给机器看的
		//$order_id = (int) $_REQUEST['orderid'];
		$orderid = $_GET['orderid'];
		error_log(time()."here notify"."\r\n"."and orderid is:".$_GET['orderid'],3,"/tmp/error/errors.log");
		$order_id_arr = str_split($orderid, 21);
		error_log("order arr: ".json_encode($order_id_arr)."\r\n",3,"/tmp/error/errors.log");
		//这个标示用来区分是不是所有的订单都支付了
//		$flag = 1;
		$order_info=array();
		foreach ($order_id_arr as $value) {
			$orderid = $value;
			$order_info = M('design_order')->where('orderid=\'' . $orderid . '\'')->find();
			error_log("order info:".json_encode($order_info)."\r\n",3,"/tmp/error/errors.log");
//			if(empty($order_info)){
//				error_log("order_info is empty");
//				$flag=0;
//			}
		}


		// 由于该URL不仅仅只有Paypal的服务器能访问，其他任何服务器都可以向该方法发起请求。所以要判断请求发起的合法性，也就是要判断请求是否是paypal官方服务器发起的

		// 拼凑 post 请求数据
//		$req = 'cmd=_notify-validate';// 验证请求
		$req = 'cmd=' . urlencode('_notify-validate');
		foreach ($_POST as $k => $v) {
			error_log("post content:".$k."=>".$v."\r\n",3,"/tmp/error/errors.log");
			$v = urlencode(stripslashes($v));
			$req .= "&$k=$v";
		}
		error_log("start verify Post ,request data is $req"."\r\n",3,"/tmp/error/errors.log");
///////////////////////////////////////////////////////////////////
//		Curl 方法
/////////////////////////////////////////////////////////////////
		$ch = curl_init();
//		curl_setopt($ch,CURLOPT_URL,'https://www.paypal.com/cgi-bin/webscr');//https://www.sandbox.paypal.com/cgi-bin/webscr
		curl_setopt($ch, CURLOPT_URL, 'https://www.sandbox.paypal.com/cgi-bin/webscr');
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$req);
		$res = curl_exec($ch);
		curl_close($ch);
		error_log("res:".json_encode($res)."\r\n",3,"/tmp/error/errors.log");
//////////////////////////////////////////////////////////////////////
//		$ch = curl_init();
//		error_log("curl_init is :".$ch);
//		curl_setopt($ch, CURLOPT_URL, 'https://www.sandbox.paypal.com/cgi-bin/webscr');
//		curl_setopt($ch, CURLOPT_HEADER, 0);
//		curl_setopt($ch, CURLOPT_POST, 1);
//		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
//		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
//		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
//		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
//		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: www.sandbox.paypal.com'));
//		$res = curl_exec($ch);
//		curl_close($ch);
//////////////////////////////////////////////////////////////////
//		$url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
//		$curl = curl_init(); // 启动一个CURL会话
//		curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
//		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
//		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
//		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
//		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
//		curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
//		curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
//		curl_setopt($curl, CURLOPT_POSTFIELDS, $req); // Post提交的数据包
//		curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
//		curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
//		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
//		$res = curl_exec($curl); // 执行操作
//		if (curl_errno($curl)) {
//			$res = 'Errno'.curl_error($curl);//捕抓异常
//		}
//		curl_close($curl); // 关闭CURL会话

//////////////////////////////////////////////////////////////////////
		//将信息POST给paypal验证
//		$cookie_jar=tempnam('./tmp','cookie');
//		$ch = curl_init();
////		curl_setopt($ch, CURLOPT_URL, 'https://www.paypal.com/cgi-bin/webscr'); //正式的
//        curl_setopt($ch, CURLOPT_URL,'https://www.sandbox.paypal.com/cgi-bin/webscr'); //测试的
//		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//如果成功只将结果返回，不自动输出返回的内容。 		如果失败返回FALSE
//		curl_setopt($ch, CURLOPT_POST, 1);
//		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
////		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar); //设定返回的数据是否自动显示
//		$res = curl_exec($ch);
//		curl_close($ch);
//		error_log("res is :" .$res ,3,"/tmp/error_log");
////////////////////////////////////////////////////////////////////////////////////

//		$res = 'VERIFIED';
//		if ($res && !empty($order_info)) {
//		if ($res) {
				error_log("verified order status：" . json_encode($res)."\r\n",3,"/tmp/error/errors.log");
				// 本次请求是否由Paypal官方的服务器发出的请求
				if (strcmp($res, 'VERIFIED') == 0) {
					/**
					 * 判断订单的状态
					 * 判断订单的收款人
					 * 判断订单金额
					 * 判断货币类型
					 */
					error_log(time().'here verified',3,"/tmp/error/errors.log");
					if (($_POST['payment_status'] != 'Completed' && $_POST['payment_status'] != 'Pending')) {
						// 如果有任意一项成立，则终止执行。由于是给机器看的，所以不用考虑什么页面。直接输出即可
						error_log('payment status is fail'."\r\n",3,"/tmp/error/errors.log");
						exit('fail');
					} else {// 如果验证通过，则证明本次请求是合法的

						error_log('payment status is succ'."\r\n",3,"/tmp/error/errors.log");
						// motify payflag

						error_log("order arr: ".json_encode($order_id_arr)."and orderid is:".$_GET['orderid']."\r\n",3,"/tmp/error/errors.log");


						$this->sendmail($order_id_arr);

//						error_log("qty_arr_serialize is:".$_GET['qtystr']."\r\n",3,"/tmp/error/errors.log");
//						$order_qty_arr=unserialize(stripslashes($_GET['qtystr']));
//						$model = M('design_winner');
//						error_log("start to modify design winner qty"."\r\n"."order_qty_arr is:".json_encode($order_qty_arr),3,"/tmp/error/errors.log");
//						foreach ($order_qty_arr as $k => $val) {
//							$product = $model->where('id=' . $k)->find();
//							$order_qty = $product['order_qty'];
//							$order_qty += $val;
//							$result = $model->where('id=' . $k)->setField('order_qty', $order_qty);
//							if (!$result) {
//								error_log("Design winner quantity modify error: fail to modify order_qty(design_winner), product id is " . $k . "quantity is :" . $val ."\r\n",3,"/tmp/error/errors.log");
//							}else{
//								error_log("Productid is".$k.",Design winner quantity modify success,"."quantity is :" . $val ."\r\n",3,"/tmp/error/errors.log");
//							}
//						}

						exit('success');
					}
				} else {
					error_log("pay fail  fail  fail"."\r\n",3,"/tmp/error/errors.log");
					exit('fail');
				}
//		}
	}


		/**
		 * param:$order_info:design_order
		 */
	public function sendmail($order_id_arr)
		{
//			error_log('start to send email, the email address is:'.$email.', orderid is:'.$order_info['orderid']."\r\n",3,"/tmp/error/errors.log");
			import("@.ORG.PHPMailer");
			$mail = new PHPMailer();

			/*			
      $mail->IsSMTP(); // send via SMTP 
      $mail->Host = "smtp.exmail.qq.com"; // SMTP servers 
      $mail->SMTPAuth = true; // turn on SMTP authentication 
      $mail->Username = "tell@allocacoc.com.cn"; // SMTP username 注意：普通邮件认证不需要加 @域名 
      $mail->Password = "powercube123"; // SMTP password  
      $mail->From = "tell@allocacoc.com.cn"; // 发件人邮箱             
      $mail->Username = "designhouse.orders@allocacoc.com"; // SMTP username 注意：普通邮件认证不需要加 @域名 
      $mail->Password = "egx-7VW-Y4o-Bps"; // SMTP password 
      */

			$mail->IsSMTP(); // send via SMTP
//			$mail->Host = "mail.allocacoc.com"; // SMTP servers
			$mail->Host = "smtp.exmail.qq.com"; // SMTP servers
			$mail->SMTPAuth = true; // turn on SMTP authentication
//			$mail->Username = "designhouse.orders@allocacoc.com"; // SMTP username 注意：普通邮件认证不需要加 @域名
			$mail->Username = "leo.li@allocacoc.com.cn"; // SMTP username 注意：普通邮件认证不需要加 @域名
//			$mail->Password = "egx-7VW-Y4o-Bps"; // SMTP password
			$mail->Password = "Liyonghui890"; // SMTP password
			$mail->From = "leo.li@allocacoc.com.cn"; // 发件人邮箱
			$mail->FromName = "allocacoc"; // 发件人 ,比如 中国资金管理网
			$mail->CharSet = "GB2312"; // 这里指定字符集！
			$mail->Encoding = "base64";
			$mail->WordWrap = 80; // 设置每行字符串的长度
			//$mail->Body = "Thank you for order our product, You payed successfully. We will send products to You as soon as possible.<br> Allocacoc.";
			$mail->AddReplyTo("", "allocacoc");
			$mail->IsHTML(true); // send as HTML
			$mail->Subject = "Thanks for order our product, from Allocacoc";

			foreach ($order_id_arr as $order_id) {
				$order_info = M('design_order')->where('orderid =\'' . $order_id . '\'')->find();
				$result = M('design_order')->where('orderid=\'' . $order_id . '\'')->setField('payflag', 1);
				$product = M('design_winner')->where('id='. $order_info['designwinner_id'])->find();
				$order_qty = $product['order_qty'];
				$order_qty+= $order_info['qty'];
				$result2 = M('design_winner')->where('id=' .$order_info['designwinner_id'])->setField('order_qty', $order_qty);
				if(!$result || !$result2)
				{
					$statusflag=0;
					error_log("PayFlag and qty modified error: fail to modify pay flag, order is ".$order_id." qty is ".$order_info['qty']."\r\n",3,"/tmp/error/errors.log");

				}else{
					error_log('orderid is:'.$order_id.'pay flag and qty have been changed successfully'."\r\n",3,"/tmp/error/errors.log");
				}
				$mail->AddAddress($order_info['email'], ''); // 收件人邮箱和姓名
				$this->generateEmail($order_info, $content);
				$mail->Body = $content;
				$mail->AltBody = "To view the message, please use an HTML compatible email viewer!";

				if (!$mail->Send()) {
					error_log('send error:' . $mail->ErrorInfo.', the email address is:'.$order_info['email'].'orderid is:'.$order_info['orderid']."\r\n",3,"/tmp/error/errors.log");
				}else{
					error_log('The confirmation email have been send successfully, the email address is:'.$order_info['email'].'orderid is:'.$order_info['orderid']."\r\n",3,"/tmp/error/errors.log");
				}

			}
			/*
            $mail->IsSMTP(); // send via SMTP
            $mail->Host = C('MAIL_SMTP_SERVER'); // SMTP servers
            $mail->SMTPAuth = true; // turn on SMTP authentication
            $mail->Username = C('MAIL_USERNAME'); // SMTP username 注意：普通邮件认证不需要加 @域名
            $mail->Password = C('MAIL_PASSWD'); // SMTP password
            $mail->From = C('MAIL_FROM'); // 发件人邮箱
            $mail->FromName = C('MAIL_FROMNAME'); // 发件人 ,比如 中国资金管理网
            $mail->CharSet = C('MAIL_CHARSET'); // 这里指定字符集！
            $mail->Encoding = C('MAIL_ENCODING');
            $mail->AddAddress($email,''); // 收件人邮箱和姓名
            $mail->AddAddress(C('MAIL_ORDER'),'');
            $mail->AddReplyTo("",C('MAIL_REPLYTO'));
            $mail->IsHTML(true); // send as HTML
            $mail->Subject = C('MAIL_SUBJECT');
            */

		}
//	public function sendmail($email, $order_info)
//		{
//			error_log('start to send email, the email address is:'.$email.', orderid is:'.$order_info['orderid']."\r\n",3,"/tmp/error/errors.log");
//			import("@.ORG.PHPMailer");
//			$mail = new PHPMailer();
//
//			/*
//      $mail->IsSMTP(); // send via SMTP
//      $mail->Host = "smtp.exmail.qq.com"; // SMTP servers
//      $mail->SMTPAuth = true; // turn on SMTP authentication
//      $mail->Username = "tell@allocacoc.com.cn"; // SMTP username 注意：普通邮件认证不需要加 @域名
//      $mail->Password = "powercube123"; // SMTP password
//      $mail->From = "tell@allocacoc.com.cn"; // 发件人邮箱
//      $mail->Username = "designhouse.orders@allocacoc.com"; // SMTP username 注意：普通邮件认证不需要加 @域名
//      $mail->Password = "egx-7VW-Y4o-Bps"; // SMTP password
//      */
//
//			$mail->IsSMTP(); // send via SMTP
////			$mail->Host = "mail.allocacoc.com"; // SMTP servers
//			$mail->Host = "smtp.exmail.qq.com"; // SMTP servers
//			$mail->SMTPAuth = true; // turn on SMTP authentication
////			$mail->Username = "designhouse.orders@allocacoc.com"; // SMTP username 注意：普通邮件认证不需要加 @域名
//			$mail->Username = "leo.li@allocacoc.com.cn"; // SMTP username 注意：普通邮件认证不需要加 @域名
////			$mail->Password = "egx-7VW-Y4o-Bps"; // SMTP password
//			$mail->Password = "Liyonghui890"; // SMTP password
//			$mail->From = "leo.li@allocacoc.com.cn"; // 发件人邮箱
//			$mail->FromName = "allocacoc"; // 发件人 ,比如 中国资金管理网
//			$mail->CharSet = "GB2312"; // 这里指定字符集！
//			$mail->Encoding = "base64";
//			$mail->AddAddress($email, ''); // 收件人邮箱和姓名
//			$mail->AddReplyTo("", "allocacoc");
//			$mail->IsHTML(true); // send as HTML
//			$mail->Subject = "Thanks for order our product, from Allocacoc";
//
//
//			/*
//            $mail->IsSMTP(); // send via SMTP
//            $mail->Host = C('MAIL_SMTP_SERVER'); // SMTP servers
//            $mail->SMTPAuth = true; // turn on SMTP authentication
//            $mail->Username = C('MAIL_USERNAME'); // SMTP username 注意：普通邮件认证不需要加 @域名
//            $mail->Password = C('MAIL_PASSWD'); // SMTP password
//            $mail->From = C('MAIL_FROM'); // 发件人邮箱
//            $mail->FromName = C('MAIL_FROMNAME'); // 发件人 ,比如 中国资金管理网
//            $mail->CharSet = C('MAIL_CHARSET'); // 这里指定字符集！
//            $mail->Encoding = C('MAIL_ENCODING');
//            $mail->AddAddress($email,''); // 收件人邮箱和姓名
//            $mail->AddAddress(C('MAIL_ORDER'),'');
//            $mail->AddReplyTo("",C('MAIL_REPLYTO'));
//            $mail->IsHTML(true); // send as HTML
//            $mail->Subject = C('MAIL_SUBJECT');
//            */
//
//
//			$mail->WordWrap = 80; // 设置每行字符串的长度
//			//$mail->Body = "Thank you for order our product, You payed successfully. We will send products to You as soon as possible.<br> Allocacoc.";
//			$this->generateEmail($order_info, $content);
//
//			$mail->Body = $content;
//
//			$mail->AltBody = "To view the message, please use an HTML compatible email viewer!";
//
//			if (!$mail->Send()) {
//				error_log('send error:' . $mail->ErrorInfo."\r\n",3,"/tmp/error/errors.log");
//				return false;
//			}else{
//				error_log('The confirmation email have been send successfully, the email address is:'.$email.'orderid is:'.$order_info['orderid']."\r\n",3,"/tmp/error/errors.log");
//				return true;
//			}
//		}


		// generate mail content for order, including customer info,shipping address,product list ,etc.
	public function generateEmail($order_info, &$content)
		{
			$paytype = '';
			if ($order_info['paytype'] == 1) $paytype = 'Paypal';
			if ($order_info['paytype'] == 2) $paytype = 'Card';


			/*
            $typename = '';
            if($order_info['price_id']!=0)
            {
                $price_info = M('designwinner_price')->where('id ='. $order_info['price_id'] )->find();
             if($price_info)
               $typename = $price_info['type_name'];
            }
            */

			$content = '
   	 <html>
	<head></head>
  <body> 

<table style="border-radius:6px!important;background-color:#fdfdfd;border:1px solid #dcdcdc;border-radius:6px!important" border="0" cellpadding="0" cellspacing="0" width="600" class="">
	<tbody class="">
		<tr class="">
			
			<td align="center" valign="top" class="">
				 <table style="background-color:#15869f;color:#ffffff;border-top-left-radius:6px!important;border-top-right-radius:6px!important;border-bottom:0;font-family:Arial;font-weight:bold;line-height:100%;vertical-align:middle" bgcolor="#8d1f82" border="0" cellpadding="0" cellspacing="0" width="600" class="">
				 	<tbody class="">
				 		<tr class="">
				 			<td class="">
				 				<h1 style="color:#ffffff;margin:0;padding:28px 24px;display:block;font-family:Arial;font-size:30px;font-weight:bold;text-align:left;line-height:150%" class="">Your order is complete</h1>
				 		  </td>
				 		</tr>
				 	</tbody>
				 </table>
			</td>
		</tr>
		<tr class="">
			<td align="center" valign="top" class="">
				<table border="0" cellpadding="0" cellspacing="0" width="600" class="">
					<tbody class="">
						<tr class="">
							<td style="background-color:#fdfdfd;border-radius:6px!important" valign="top" class="">
								<table border="0" cellpadding="20" cellspacing="0" width="100%" class="">
									<tbody class="">
										<tr class="">
											<td valign="top" class="">
												<div style="color:#737373;font-family:Arial;font-size:14px;line-height:150%;text-align:left" class=""><p class="">Hi there. Your recent order on allocacoc has been completed. Your order details are shown below for your reference:</p>
													<h2 style="color:#505050;display:block;font-family:Arial;font-size:30px;font-weight:bold;margin-top:10px;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;line-height:150%" class="">Order: '
				. $order_info['orderid'] . '</h2>
													<table style="width:100%;border:1px solid #eee" border="1" cellpadding="6" cellspacing="0" class="">
														<thead class="">
															<tr class="">
																<th scope="col" style="text-align:left;border:1px solid #eee" class="">Product</th>
																<th scope="col" style="text-align:left;border:1px solid #eee" class="">Quantity</th>
																<th scope="col" style="text-align:left;border:1px solid #eee" class="">Price</th>
															</tr>
														</thead>';

			$detaillist = M('design_suborder')->where('orderid=\'' . $order_info['orderid'] . '\'')->select();
			foreach ($detaillist as $detail) {
				$content .=
					' <tbody class="">
									  	<tr class="">
									  		<td style="text-align:left;vertical-align:middle;border:1px solid #eee;word-wrap:break-word" class="">' . $detail['winner_name'] . '<br class="">
									  			<small class="">Type/Color: ' . $detail['type_name'] . '</small>
									  		</td>
									  		<td style="text-align:left;vertical-align:middle;border:1px solid #eee" class="">' . $detail['qty'] . '</td>
									  		<td style="text-align:left;vertical-align:middle;border:1px solid #eee" class=""><span class="">' .  $detail['price'] . '</span></td>
									  	</tr>
									  </tbody>';
			}


			$content .=
				'<tfoot class="">
													  	
													  	<tr class="">
													  		<th scope="row" colspan="2" style="text-align:left;border:1px solid #eee" class="">Shipping</th>
													  		<td style="text-align:left;border:1px solid #eee" class=""><span class="">' . $order_info['shipfee'] . '</span>&nbsp;</td>
													  	</tr>
													  	<tr class="">
													  		<th scope="row" colspan="2" style="text-align:left;border:1px solid #eee" class="">Payment Method</th>
													  		<td style="text-align:left;border:1px solid #eee" class="">' . $paytype . '</td>
													  	</tr>
													  	<tr class="">
													  		<th scope="row" colspan="2" style="text-align:left;border:1px solid #eee" class="">Order Total</th>
													  		<td style="text-align:left;border:1px solid #eee" class=""><span class="">' . $order_info['total'] . '</span> </td>
													  	</tr>
													  </tfoot>
												  </table>
												  
												  <h2 style="color:#505050;display:block;font-family:Arial;font-size:30px;font-weight:bold;margin-top:10px;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;line-height:150%" class="">Customer details</h2>
												  <p class=""><b class="">Email:</b> 
												  	<a href="mailto:' . $order_info['email'] . '" target="_blank" class="">' . $order_info['email'] . '</a></p><p class=""><b class="">Tel:</b> <a value="' . $order_info['phone'] . '" target="_blank" class="">' . $order_info['phone'] . '</a></p>
												  
												  <table style="width:100%;vertical-align:top" border="0" cellpadding="0" cellspacing="0" class="">
												  	<tbody class="">
												  		<tr class="">
												  			
												  			<td valign="top" width="50%" class="">
												  				<h3 style="color:#505050;display:block;font-family:Arial;font-size:26px;font-weight:bold;margin-top:10px;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;line-height:150%" class="">Shipping address</h3>
												  				
												  				<p class="">' . $order_info['address'] . '
												  					<br class="">' . $order_info['city'] . '
												  					<br class="">' . $order_info['country'] . '
												  					<br class="">' . $order_info['zip'] . '
												  					</p>
												  			</td>
												  		</tr>
												  	</tbody>
												  </table>
												</div>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
		
		<tr class="">
			<td align="center" valign="top" class="">
				<table style="border-top:0" border="0" cellpadding="10" cellspacing="0" width="600" class="">
					<tbody class="">
						<tr class="">
							<td valign="top" class="">
								<table border="0" cellpadding="10" cellspacing="0" width="100%" class="">
									<tbody class="">
										<tr class="">
											<td colspan="2" style="border:0;color:#bb79b4;font-family:Arial;font-size:12px;line-height:125%;text-align:center" valign="middle" class=""><p class=""><a href="https://www.facebook.com/Allocacoc" style="color:#505050;font-weight:normal;text-decoration:underline" target="_blank" class=""><img style="min-height:15px" class=""></a> <a href="https://www.facebook.com/Allocacoc" style="color:#505050;font-weight:normal;text-decoration:underline" target="_blank" class="">Like us on Facebook</a>  and be the first one to discover our newest products! </p><p class="">Please do not reply to this email</p>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
            </tr>
          </tbody>
        </table>
      </td>
    </tr>
   </tbody>
  </table>

  </body>                            
</html>
';

			error_log('start to send email, the email content have been edited:'."\r\n",3,"/tmp/error/errors.log");

		}

}