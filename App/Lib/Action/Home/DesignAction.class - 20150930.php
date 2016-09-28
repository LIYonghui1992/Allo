<?php

/**

 *

 * DesignAction.class.php (前台Design challenge)

 *

 */

require_once(ROOT.'/Stripe/init.php');

if (!defined("App"))
	exit("Access Denied");

class DesignAction extends BaseAction {

	public function index() {

		//头部banner
		$map['lang'] = LANG_ID;		
		//$map['lang'] = 9;

		$map['status'] = 1;

		$design_challenge = M('design_challenge') -> where($map) -> find();

		$banners = array(0 => $design_challenge['banner_one'], 1 => $design_challenge['banner_two'], 2 => $design_challenge['banner_three']);

		
		//find out current projects, deadline>current>winner_date
		$current = time();//var_dump($current);
		$where['lang'] = LANG_ID;
		$where['status'] = 1;
		$where['deadline'] = array('gt',$current);
		$where['winner_date'] = array('lt',$current);
		
		
		$count = M('design_winner') -> where($where)->count ( $id );
		$pagecount = intval($count/4) ;
		if($count%4 > 0)
		  $pagecount += 1;
		  
		  
		$fund_list = M('design_winner') -> where($where) -> order('listorder asc,updatetime desc') ->limit(0,4)->select();
		
		foreach ($fund_list as $k => $v) {
			
			$pcs_ordered = 0;

			$fund_list[$k]['date'] = date("Y.m.d",$v['winner_date']);  
			//var_dump($fund_list[$k]['date']);     
			
			//$v['winner_date'] intval(($v['deadline'] - time()) / (3600 * 24));

			$pcs_ordereds  = M('design_order') -> where('designwinner_id=' . $v['id']) -> select();
			
			foreach($pcs_ordereds as $key=>$val){
				
				$pcs_ordered += $val['qty'];
				
			}
			
			$fund_list[$k]['pcs_ordered2'] = $pcs_ordered;
			
			if($pcs_ordered>$v['funding_goal']) $pcs_ordered = $v['funding_goal'];

			$fund_list[$k]['pcs_ordered'] = $pcs_ordered;
			
			$fund_list[$k]['winner_text'] = $v['winner_text'];

		}
		
		/**
		deadline
		winner_date
			$fund_list = M('design_winner') -> where($where) -> order('listorder asc,updatetime desc') -> select();

		foreach ($fund_list as $k => $v) {
			
			$pcs_ordered = 0;

			$fund_list[$k]['remaining'] = intval(($v['deadline'] - time()) / (3600 * 24));

			$pcs_ordereds               = M('design_order') -> where('design_id=' . $v['id']) -> select();
			
			foreach($pcs_ordereds as $key=>$val){
				
				$pcs_ordered += $val['num'];
				
			}
			
			$fund_list[$k]['pcs_ordered2'] = $pcs_ordered;
			
			if($pcs_ordered>$v['funding_goal']) $pcs_ordered = $v['funding_goal'];

			$fund_list[$k]['pcs_ordered'] = $pcs_ordered;
			
			$fund_list[$k]['winner_text'] = $v['winner_text'];

		}
		
		*/
		
		
	//年份列表, only select years that have project 	 
	 $yearwhere['deadline'] = array('lt',time());
	 $yearwhere['lang'] = LANG_ID;
	 $yearwhere['status'] = 1;	
	 $yearlist = M('design_winner')->Distinct(true)->field('winneryear')->where($yearwhere)->select();
	 if(!$yearlist)
	 {
	 }
	 
	 foreach ($yearlist as $k => $v)
	 {
	 	if($v['winneryear']!='0')
	 	 $years[] = $v['winneryear'];
	 }
	 
	 $pastwhere['lang'] = LANG_ID;
	 $pastwhere['status'] = 1;		 
	 $pastwhere['winneryear'] = array('eq',$years[0]);
	 $pastwhere['deadline'] = array('lt',time());
	 
	 $pastcount = M('design_winner') -> where($pastwhere)->count ( $id );
	 $pastpagecount = intval($pastcount/4) ;
		if($pastcount%4 > 0)
		  $pastpagecount += 1;
		  
		  
		$pastfund_list = M('design_winner') -> where($pastwhere) -> order('listorder asc,updatetime desc') ->limit(0,4)->select();
		
		foreach ($pastfund_list as $k => $v) {
			
			$pcs_ordered = 0;

			$pastfund_list[$k]['date'] = date("Y.m.d",$v['winner_date']);  
			$pcs_ordereds  = M('design_order') -> where('designwinner_id=' . $v['id']) -> select();
			
			foreach($pcs_ordereds as $key=>$val){
				
				$pcs_ordered += $val['qty'];
				
			}
			
			$pastfund_list[$k]['pcs_ordered2'] = $pcs_ordered;
			
			if($pcs_ordered>$v['funding_goal']) $pcs_ordered = $v['funding_goal'];

			$pastfund_list[$k]['pcs_ordered'] = $pcs_ordered;
			
			$pastfund_list[$k]['winner_text'] = $v['winner_text'];

		}
		//$pastpagecount=1;
		$this -> assign('banners', $banners);  
		$this -> assign('design_challenge', $design_challenge);
		$this -> assign('years', $years);
		//$this -> assign('get_year', $get_year);
		$this -> assign('pastfund_list', $pastfund_list);
		$this -> assign('pastpagecount', $pastpagecount);
		$this -> assign('pastcurrpage', 1);
		$this -> assign('pagecount', $pagecount);
		$this -> assign('currpage', 1);
		$this -> assign('fund_list', $fund_list); //var_dump($fund_list);
		$this -> display();

	}
	
	
	
	public function pastProducts()
	{
		//pageid  pastyear
		if (IS_POST)
		{
			$pageid = intval($_POST['pageid']);
			$pastyear = intval($_POST['pastyear']);
			
			$pastwhere['lang'] = LANG_ID;
   	  $pastwhere['status'] = 1;		 
   	  $paststart = strtotime(($pastyear+1).'-1-1');
   	  $pastend = strtotime($pastyear.'-1-1');
   	  $pastwhere['winner_date'] = array('gt',$pastend);
   	  $pastwhere['deadline'] = array('lt',$paststart);
	 
   	 
			  
   	 $pastfund_list = M('design_winner') -> where($pastwhere) -> order('listorder asc,updatetime desc') ->limit($pageid*4,4)->select();
   		
   	 foreach ($pastfund_list as $k => $v) {
			
			$pcs_ordered = 0;

			$pastfund_list[$k]['date'] = date("Y.m.d",$v['winner_date']);  
			$pcs_ordereds  = M('design_order') -> where('designwinner_id=' . $v['id']) -> select();
			
			foreach($pcs_ordereds as $key=>$val){
				
				$pcs_ordered += $val['qty'];
				
			}
			
			$pastfund_list[$k]['pcs_ordered2'] = $pcs_ordered;
			
			if($pcs_ordered>$v['funding_goal']) $pcs_ordered = $v['funding_goal'];

			$pastfund_list[$k]['pcs_ordered'] = $pcs_ordered;
			
			$pastfund_list[$k]['winner_text'] = $v['winner_text'];

		 }
			
		 $data = array('status' => 1, 'fund_list' => $pastfund_list);
			echo json_encode($data);
			exit ;
		}
		
	}
	
	
	public function pastyearProducts()
	{
		//yearid:yearid,pastyear:pastyear
		if (IS_POST)
		{
			$yearid = intval($_POST['yearid']);
			$pastyear = intval($_POST['pastyear']);
			
			
			//get yearlist first, then, decide yearid
			//年份列表, only select years that have project 	 
   	 $yearwhere['deadline'] = array('lt',time());
   	 $yearlist = M('design_winner')->Distinct(true)->field('winneryear')->where($yearwhere)->select();
   	 if(!$yearlist)
   	 {
   	 }
   	 
   	 foreach ($yearlist as $k => $v)
   	 {
   	 	if($v['winneryear']!='0')
   	 	 $years[] = $v['winneryear'];
   	 }
   	 
	    if(($yearid==0 && $pastyear==$years[0]) || ($yearid==1 && $pastyear==$years[6] ) )
	    {
	    	$data = array('status' => 0);
  			echo json_encode($data);
  			exit ;
	    }
	    
	    if($yearid==0)
	    {
	    	$pastyear = $pastyear+1;
	    	$yearid = $pastyear;
	    }
							
			if($yearid==1)
			{
				$pastyear = $pastyear-1;
				$yearid = $pastyear;
			}

   	 
   	 $pastwhere['lang'] = LANG_ID;
	   $pastwhere['status'] = 1;		 
	   $pastwhere['winneryear'] = array('eq',$yearid);
	   $pastwhere['deadline'] = array('lt',time());
	 
	 
   	 $pastcount = M('design_winner') -> where($pastwhere)->count ( $id );
   	 $pastpagecount = intval($pastcount/4) ;
   		if($pastcount%4 > 0)
   		  $pastpagecount += 1;
   		
   		
			  
   	 $pastfund_list = M('design_winner') -> where($pastwhere) -> order('listorder asc,updatetime desc') ->limit(0,4)->select();
   		
   	 foreach ($pastfund_list as $k => $v) {
			
			$pcs_ordered = 0;

			$pastfund_list[$k]['date'] = date("Y.m.d",$v['winner_date']);  
			$pcs_ordereds  = M('design_order') -> where('designwinner_id=' . $v['id']) -> select();
			
			foreach($pcs_ordereds as $key=>$val){
				
				$pcs_ordered += $val['qty'];
				
			}
			
			$pastfund_list[$k]['pcs_ordered2'] = $pcs_ordered;
			
			if($pcs_ordered>$v['funding_goal']) $pcs_ordered = $v['funding_goal'];

			$pastfund_list[$k]['pcs_ordered'] = $pcs_ordered;
			
			$pastfund_list[$k]['winner_text'] = $v['winner_text'];

		 }
			
		 $data = array('status' => 1, 'fund_list' => $pastfund_list,'pastpagecount'=>$pastpagecount,'pastyear'=>$yearid);
			echo json_encode($data);
			exit ;
			
		}
	}
	
	//find current product list, by page no.  , 4 products per page
	public function currentProducts()
	{
		if (IS_POST){
			$pageid = intval($_POST['pageid']);
		
  		$current = time();
  		$where['lang'] = LANG_ID;
  		$where['status'] = 1;
  		$where['deadline'] = array('gt',$current);
  		$where['winner_date'] = array('lt',$current);
  		
  		$start = 4 * $pageid;
  		
  		  
  		$fund_list = M('design_winner') -> where($where) -> order('listorder asc,updatetime desc') ->limit($start,4)->select();
  		if(!$fund_list)
  		{
  			$data = array('status' => 0);
  			echo json_encode($data);
  			exit ;
  		}
  		
  		
  		foreach ($fund_list as $k => $v) {
  			
  			$pcs_ordered = 0;
  
  			$fund_list[$k]['date'] = date("Y.m.d",$v['winner_date']);    			
  
  			$pcs_ordereds  = M('design_order') -> where('designwinner_id=' . $v['id']) -> select();
  			
  			foreach($pcs_ordereds as $key=>$val){
  				
  				$pcs_ordered += $val['qty'];
  				
  			}
  			
  			$fund_list[$k]['pcs_ordered2'] = $pcs_ordered;
  			
  			if($pcs_ordered>$v['funding_goal']) $pcs_ordered = $v['funding_goal'];
  
  			$fund_list[$k]['pcs_ordered'] = $pcs_ordered;
  			
  			$fund_list[$k]['winner_text'] = $v['winner_text'];
  
  		 }
  		 
  		 //error_log(json_encode($fund_list));
  		$data = array('status' => 1, 'fund_list' => $fund_list);
			echo json_encode($data);
			exit ;
		
			
		}
	}

  /**
  *  get one page of designwinner ,
  
  public function pagelist() {

		if (IS_POST) {

			$page = intval($_POST['pageid']);

			M('design_ideas') -> save($data2);

			$design = M('design_ideas') -> where('id=' . $id) -> find();

			$data = array('stauts' => 1, 'title' => $design['title'], 'developer' => $design['developer'], 'background_img' => $design['background_img'], 'expanded_img' => $design['expanded_img'], 'idea_text' => $design['idea_text'], 'll_num' => $design['ll_num'], 'zan' => $design['zan']);

			echo json_encode($data);

			exit ;

		}

	}
	*/
	public function lists() {

		//头部banner

		//$map['lang'] = LANG_ID;
		
		$map['lang'] = 9;

		$map['status'] = 1;

		$design_challenge = M('design_challenge') -> where($map) -> find();

		$banners = array(0 => $design_challenge['banner_one'], 1 => $design_challenge['banner_two'], 2 => $design_challenge['banner_three']);

		$design_id = intval($_GET['design_id']);

		$design = M('design') -> where('id=' . $design_id) -> find();

		$design_ideas = M('design_ideas') -> where('design_id=' . $design_id) -> order('listorder asc,updatetime desc') -> select();
		
		foreach($design_ideas as $k=>$v){
			
			$design_ideas[$k]['idea_text'] = $v['idea_text'];
		}

		//$where['lang'] = LANG_ID;
		
		$where['lang'] = 9;

		$where['status'] = 1;

		$fund_list = M('design_winner') -> where($where) -> order('listorder asc,updatetime desc') -> select();

		foreach ($fund_list as $k => $v) {
			
			$pcs_ordered = 0;

			$fund_list[$k]['remaining'] = intval(($v['deadline'] - time()) / (3600 * 24));

			$pcs_ordereds               = M('design_order') -> where('design_id=' . $v['id']) -> select();
			
			foreach($pcs_ordereds as $key=>$val){
				
				$pcs_ordered += $val['num'];
				
			}
			
			$fund_list[$k]['pcs_ordered2'] = $pcs_ordered;
			
			if($pcs_ordered>$v['funding_goal']) $pcs_ordered = $v['funding_goal'];

			$fund_list[$k]['pcs_ordered'] = $pcs_ordered;
			
			$fund_list[$k]['winner_text'] = $v['winner_text'];

		}
		
		$this -> assign('banners', $banners);

		$this -> assign('design_challenge', $design_challenge);

		$this -> assign('design', $design);

		$this -> assign('design_ideas', $design_ideas);

		$this -> assign('fund_list', $fund_list);

		$this -> display();

	}
	
	public function fundDetail() {

		if (IS_POST) {

			$id = intval($_POST['id']);

			$fund_list = M('design_winner') -> where('id=' . $id) -> find();
			
			$data = array('stauts' => 1,'id' => $id, 'title' => $fund_list['title'], 'developer' => $fund_list['developer'], 'background_img' => $fund_list['background_img'], 'expanded_img' => $fund_list['expanded_img'], 'winner_text' => $fund_list['winner_text'], 'deadline' => date('Y.m.d',$fund_list['deadline']), 'funding_goal' => $fund_list['funding_goal'], 'pcs_ordered' => $fund_list['pcs_ordered'], 'price' => $fund_list['price']);

			echo json_encode($data);

			exit ;

		}

	}
	
/**
	public function detail() {

		if (IS_POST) {

			$id = intval($_POST['id']);

			$ll_num = intval($_POST['ll_num']) + 1;

			$data2 = array('id' => $id, 'll_num' => $ll_num);

			M('design_ideas') -> save($data2);

			$design = M('design_ideas') -> where('id=' . $id) -> find();

			$data = array('stauts' => 1, 'title' => $design['title'], 'developer' => $design['developer'], 'background_img' => $design['background_img'], 'expanded_img' => $design['expanded_img'], 'idea_text' => $design['idea_text'], 'll_num' => $design['ll_num'], 'zan' => $design['zan']);

			echo json_encode($data);

			exit ;

		}

	}
	*/

  
  public function detail() {
		
		$where['lang'] = 9;
		$where['status'] = 1;
		$designwinner_id = $_GET['id'];
		$where['id'] = intval($designwinner_id);

		$fund_list = M('design_winner') -> where($where) -> select();
		if($fund_list)
		  $fund = $fund_list[0];

		$fund['remaining'] = intval(($fund['deadline'] - time()) / (3600 * 24));
		
		$pcs_ordered = 0;
		$pcs_ordereds = M('design_order') -> where('designwinner_id=' . $fund['id']) -> select();
		foreach($pcs_ordereds as $key=>$val){
				
				$pcs_ordered += $val['qty'];
				
		}
		$fund['pcs_ordered2'] = $pcs_ordered;
		if($pcs_ordered>$fund['funding_goal']) $pcs_ordered = $fund['funding_goal'];

		$fund['pcs_ordered'] = $pcs_ordered;
			
		$fund['winner_text'] = $fund['winner_text'];
		
		$map['lang'] = 9;
		$map['status'] = 1;
		$designwinner_id = $_GET['id'];
		$map['designwinner_id'] = intval($designwinner_id);	
	
		$type_list = M('designwinner_price') -> where($map) -> select();
		
		
		
		$size = 0;
		if($type_list)
		  $size = count($type_list);
		//$this -> assign('banners', $banners);

		//$this -> assign('design_challenge', $design_challenge);

		//$this -> assign('design', $design);

		//$this -> assign('design_ideas', $design_ideas);
		
		//var_dump($type_list);

		$this -> assign('type_list', $type_list);
		$this -> assign('type_listsize', $size);
		$this -> assign('fund', $fund);

		$this -> display();

	}
	
	
	/**
	* find field:buy-page ,of table designchallenge, for designhouse page,find out more
	*/
	public function more()
	{
		//$where['lang'] = 9;
		//$where['status'] = 1;
		$designchallenge_id = $_GET['id'];
		
		$where['id'] = intval($designchallenge_id);

		$designchallenge = M('design_challenge') -> where($where) -> select();
		
		//var_dump($designchallenge);
		
		$this -> assign('challenge', $designchallenge[0]);

		$this -> display();
		
	}
	 
	 
	 
	 
	
	public function zan() {

		if (IS_POST) {

			$id = intval($_POST['id']);

			$ip = get_client_ip();

			$arr = explode(".", $ip);

			$ip = implode('_', $arr);

			$design = M('design_ideas') -> where('id=' . $id) -> find();

			$zan = $design['zan'] + 1;

			$data2 = array('id' => $id, 'zan' => $zan);

			if (empty($_COOKIE['allocacoc_' . $ip . '_' . $id])) {

				$rs = M('design_ideas') -> save($data2);

				cookie($ip . '_' . $id, $ip . '_' . $id, 3600 * 60 * 24 * 30);

			}

			if ($rs) {

				$msg = "Thank you for your upvote!";
				
				$data = array('stauts' => 1, 'zan' => $zan, 'msg' => $msg);

				echo json_encode($data);
	
				exit ;

			} else {

				$msg = "You have already upvoted this design!";
				
				$data = array('stauts' => 0, 'zan' => $zan, 'msg' => $msg);

				echo json_encode($data);
	
				exit ;

			}

			

		}

	}

    
    /**
    *  order from design house
    */
    public function order_designwinner()
    {
    	$designwinner_id = $_REQUEST['id'];
    	$qty = $_REQUEST['qty'];
    	
    	
    	$typeid = $_REQUEST['typeid'];
    
    	
    	$typepic = $_REQUEST['typepic'];
    	$price = $_REQUEST['price'];
    	$bigpic = $_REQUEST['bigpic'];
    
  		
    	$total = 0;
    	$price_no = substr ($price,1);
    	$total =  substr ($price,0,1).($price_no * intval($qty));
    	
   
    	$where['id'] = $designwinner_id;			
  		$winner = M('design_winner') -> where($where)->select();
  		$bigpic = $winner[0]['background_img'];
  		$title = $winner[0]['title'];
  		
  
  		$typename;
  		if(!$typeid)
  		{
  			$map['id'] = $typeid;
    		$type =  M('designwinner_price') -> where($map)->select();
    		$typepic = $type[0]['type_pic'];
    		$typename = $type[0]['type_name'];
  		}
  		
    	
    	$Model = new Model();
    	$sql = 'select a.id,a.title from a_country a,a_ship_price b where a.id=b.country_id order by a.title ';
    	$country_list = $Model->query($sql);

/**
    	$country = $user->table('user_status stats, user_profile profile')->where('stats.id = profile.typeid')->field('stats.id as id, stats.display as display, profile.title as title,profile.content as content')->order('stats.id desc' )->select();
    	
    	
    	$country = D('Country') -> select();	
    	
  		foreach($country as $k=>$v){
  			
  			$country_list[] = array('id'=>$v['id'],'title'=>$v['title']);
  			
  		}
  		*/
  		//select a.id,a.title from a_country a,a_ship_price b where a.id=b.country_id;
  		
  		
  		//var_dump($country_list);
  		
    	$this -> assign('designwinner_id', $designwinner_id);
    	$this -> assign('qty', $qty);
    	$this -> assign('typeid', $typeid);
    	$this -> assign('typepic', $typepic);
    	$this -> assign('typename', $typename);
    	$this -> assign('bigpic', $bigpic);
    	$this -> assign('title', $title);
    	$this -> assign('price', $price);
    	$this -> assign('total', $total);
    	$this -> assign('country_list',$country_list);
  
  		$this -> display();
    }
    
    
    
    function build_order_no()
    {
      return date('Ymd').uniqid();
    }
	
	
	 /**
    *  submit_order
    */
		public function submit_order() {

		if (IS_POST) {

			//$design = M('design_winner') -> where('id=' . $_POST['design_id']) -> find();
			
			//将countryid转化为country
			$countryid = trim($_POST['countryid']);			
			$info =  M('country') -> where('id=' . $countryid) -> find();
			$country = $info['title'];
			$order_id = $this ->build_order_no();
			


			//$data = array('design_id' => intval($_POST['design_id']), 'lang' => 9, 'num' => intval($_POST['num']), 'country' => trim($_POST['ctry']), 'first_name' => $_POST['first_name'], 'last_name' => $_POST['last_name'], 'company' => trim($_POST['company']), 'address' => trim($_POST['address']), 'zip' => trim($_POST['zip']), 'city' => trim($_POST['city']), 'email' => trim($_POST['email']), 'phone' => trim($_POST['phone']), 'winner_name' => $design['title'],'createtime'=>time());
			$data = array(
			'orderid' => $order_id,
			'first_name' => $_POST['first_name'], 
			'last_name' => $_POST['last_name'], 
			'phone' => trim($_POST['phone']),
			'zip' => trim($_POST['zip']),
			'email' => trim($_POST['email']), 
			'qty' => trim($_POST['qty']),			   
			'designwinner_id' => intval($_POST['designwinner_id']),
			'company' => trim($_POST['company']),
			'address' => trim($_POST['address']), 			
			'country' => $country,
			'price_id' => trim($_POST['price_id']),
			'typename' => $_POST['typename'],				
			'price' => trim($_POST['price']),
		  'shipfee' => trim($_POST['shipfee']),
			'total' => trim($_POST['total']),
			'winner_name' => $_POST['designwinner_title'],			  
			'lang' => 9, 
			'payflag'=>0,
			'status'=>0,
			'paytype'=>$_POST['paytype'],
			'createtime'=>time());
			
						
						
			$id = M('design_order') -> add($data);		
			
			//fail tp add record
			if(!$id){

				$rs = array('stauts' => 0, 'msg' => 'Submitted Failure!');

				echo json_encode($rs);
				exit ;

			}

			//paypal
			if ($_POST['paytype']==1) {

        //build payment url,return ,then javascript reward to paypal
        //$gateway = 'https://www.paypal.com/cgi-bin/webscr?';
        $gateway = 'https://www.sandbox.paypal.com/cgi-bin/webscr?';
        $account = 'jing.wang@allocacoc.com.cn';
        $pp_info = array();// 初始化准备提交到Paypal的数据  
        $pp_info['cmd'] = '_xclick';// 告诉Paypal，我的网站是用的我自己的购物车系统  
        $pp_info['business'] = $account;// 告诉paypal，我的（商城的商户）Paypal账号，就是这钱是付给谁的  
        $pp_info['item_name'] = "支付订单：{$order_id}";// 用户将会在Paypal的支付页面看到购买的是什么东西，只做显示，没有什么特殊用途，如果是多件商品，则直接告诉用户，只支付某个订单就可以了  
        $pp_info['amount'] = substr(trim($_POST['total']),1); // 告诉Paypal，我要收多少钱  
        $pp_info['currency_code'] = 'USD';// 告诉Paypal，我要用什么货币。这里需要注意的是，由于汇率问题，如果网站提供了更改货币的功能，那么上面的amount也要做适当更改，paypal是不会智能的根据汇率更改总额的  
        $pp_info['return'] = 'http://www.allocacoc.com/Design/index.html';// 当用户成功付款后paypal会将用户自动引导到此页面。如果为空或不传递该参数，则不会跳转  
        $pp_info['invoice'] = $order_id;  
        $pp_info['charset'] = 'utf-8';  
        $pp_info['no_shipping'] = '1';  
        $pp_info['no_note'] = '1';  
        $pp_info['cancel_return'] = 'http://www.allocacoc.com/Design/index.html';// 当跳转到paypal付款页面时，用户又突然不想买了。则会跳转到此页面  
        $pp_info['notify_url'] = 'http://www.allocacoc.com/Design/paypal_notify/orderid/'.$order_id;
        //'http://www.domain.com/index.php/design/paypal_notify/orderid/'.$order_id;// Paypal会将指定 invoice 的订单的状态定时发送到此URL(Paypal的此操作，是paypal的服务器和我方商城的服务器点对点的通信，用户感觉不到）  
        $pp_info['rm'] = '2';  
  
        $paypal_payment_url = $gateway.http_build_query($pp_info);  
        unset($pp_info);  
            
                    
				$rs = array('stauts' => 1,'paymenturl'=>$paypal_payment_url);
				echo json_encode($rs);
				exit ;

			}  
			
			//stripe,card
			if ($_POST['paytype']==2)
			{
				\Stripe\Stripe::setApiKey("sk_test_f3jgU2hZxqndFQI7j9qpThSD");
				$token = $_POST['stripetoken'];
				try {
            $charge = \Stripe\Charge::create(array(
              "amount" => intval(substr (trim($_POST['price']),1))*100, // amount in cents, again
              "currency" => "usd",
              "source" => $token, 
              "description" => $order_id )
            );          
            
            if ($charge['status']!="succeeded")
            {
            	$rs = array('stauts' => 0,'msg'=>"fail to pay!");
            	echo json_encode($rs);
            	exit ;
            }
            
            //modify payflag            
            $result = M('design_order')->where('orderid=\''. $order_id.'\'')->setField('payflag',1);
            if(!$result)
            {
            	error_log("PayFlag modify error: fail to modify flag, order is ".$order_id."\n");
            }
            
            $rs = array('stauts' => 1,'msg'=>"succeed to pay!");
            echo json_encode($rs);
            exit ;
            	
          
            } catch(Exception $e) {
              // The card has been declined
              $rs = array('stauts' => 0,'msg'=>$e->getMessage());
            	echo json_encode($rs);
            	exit ;
            
          }
          
				
						
						/**
						
						stripetoken: token,
						cardno: document.getElementById("cardno").value,
						expmonth: document.getElementById("expmonth").value,
						expyear: document.getElementById("expyear").value,
						cvc: document.getElementById("cvc").value,
						paytype:type;
						
					 require_once('init.php');
            
            //include("./lib/Stripe.php");
            \Stripe\Stripe::setApiKey("sk_test_f3jgU2hZxqndFQI7j9qpThSD");
            
            // Get the credit card details submitted by the form
            $token = $_POST['stripeToken'];
            
            // Create the charge on Stripe's servers - this will charge the user's card
            try {
            $charge = \Stripe\Charge::create(array(
              "amount" => 500, // amount in cents, again
              "currency" => "cny",
              "source" => $token, //'tok_16nhFYDMM1xdifJiben1E8X4',
              "description" => "Example charge")
            );
            
            
            //var_dump(array(json_decode($charge.status,true)));
            if ($charge['status']!="succeeded")
              echo " not succeed";
          
            //var_dump($charge); $error = false;
            //var_dump(json_decode($charge.status,true));
            //$status = $charge.status;
            //if($status['paid']== true && $status['status']=="succeeded")echo "hhhhhhhhhhhhh";
            
            } catch(Exception $e) {
              // The card has been declined
              var_dump($e->getMessage());
            
          }
          */
	
			}

		}

	}
	
	
	
	
	
	/**
	*  recieve notify info from paypal 
	*/
	 public function paypal_notify() {
        // 由于这个文件只有被Paypal的服务器访问，所以无需考虑做什么页面什么的，这个页面不是给人看的，是给机器看的
        $order_id = (int) $_GET['orderid'];
        $order_info = M('design_order')->where('orderid=\''. $order_id.'\'')->find();

        // 由于该URL不仅仅只有Paypal的服务器能访问，其他任何服务器都可以向该方法发起请求。所以要判断请求发起的合法性，也就是要判断请求是否是paypal官方服务器发起的

        // 拼凑 post 请求数据
        $req = 'cmd=_notify-validate';// 验证请求
        foreach ($_POST as $k=>$v)
        {
            $v = urlencode(stripslashes($v));
            $req .= "&{$k}={$v}";
        }

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,'http://www.paypal.com/cgi-bin/webscr');
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$req);
        $res = curl_exec($ch);
        curl_close($ch);

        if( $res && !empty($order_info) ) {
            // 本次请求是否由Paypal官方的服务器发出的请求
            if(strcmp($res, 'VERIFIED') == 0) {
                /**
                 * 判断订单的状态
                 * 判断订单的收款人
                 * 判断订单金额
                 * 判断货币类型
                 */
                if(($_POST['payment_status'] != 'Completed' && $_POST['payment_status'] != 'Pending')  ) {
                    // 如果有任意一项成立，则终止执行。由于是给机器看的，所以不用考虑什么页面。直接输出即可
                    exit('fail');
                } else {// 如果验证通过，则证明本次请求是合法的
                	
                    $result = M('design_order')->where('orderid=\''. $order_id.'\'')->setField('payflag',1);
                    if(!$result)
                    {
                    	error_log("PayFlag modify error: fail to modify flag, order is ".$order_id."\n");
                    	exit('fail');
                    }
                    exit('success');
                }
            } else {
                exit('fail');
            }
        }
    }
	
	
	
	
	public function order() {

		if (IS_POST) {

			$design = M('design_winner') -> where('id=' . $_POST['design_id']) -> find();

			$data = array('design_id' => intval($_POST['design_id']), 'lang' => 9, 'num' => intval($_POST['num']), 'country' => trim($_POST['ctry']), 'first_name' => $_POST['first_name'], 'last_name' => $_POST['last_name'], 'company' => trim($_POST['company']), 'address' => trim($_POST['address']), 'zip' => trim($_POST['zip']), 'city' => trim($_POST['city']), 'email' => trim($_POST['email']), 'phone' => trim($_POST['phone']), 'winner_name' => $design['title'],'createtime'=>time());
			
			$pcs_ordered = M('design_order') -> where('design_id=' . intval($_POST['design_id'])) -> count();
			
			/*if($pcs_ordered>$design['funding_goal']){
				
				$rs = array('stauts' => 0, 'msg' => 'The quota is full!');

				echo json_encode($rs);
				exit ;
			
			}*/
			
			$id = M('design_order') -> add($data);		

			if ($id) {

				$rs = array('stauts' => 1, 'msg' => 'Submitted successfully. We will get back to you soon!');

				echo json_encode($rs);
				exit ;

			} else {

				$rs = array('stauts' => 0, 'msg' => 'Submitted Failure!');

				echo json_encode($rs);
				exit ;

			}

		}

	}
	
	

	public function shipPrice(){
		

		if (IS_POST) {
			$countryid =  $_POST['countryid'];
			$qty =  $_POST['qty'];
			$designwinner_id =  $_POST['designwinner_id'];
			$total = $_POST['total'];
			
			//calculate shipping price ,according to weight country
			$design = M('design_winner') -> where('id=' .$designwinner_id) -> find();
			if(!$design)
			{
				$rs = array('stauts' => 0, 'msg' => 'Sorry, We Cannot ship product to your country!');
				echo json_encode($rs);
				exit ;
			}
			$total_weight = intval($design['weight']) * intval($qty);
			
			$ship_price = M('ship_price') -> where('country_id=' .$countryid) -> find();
			if(!$ship_price)
			{
				$rs = array('stauts' => 0, 'msg' => 'Sorry, We Cannot ship product to your country now!');
				echo json_encode($rs);
				exit ;
			}
			
			$base = intval($ship_price['first']);
			$additional = intval($ship_price['additional']);
			$firstPrice = $ship_price['price'];
			$additionalPrice = $ship_price['additional_price'];
			
			if($total_weight <= $base)
			  $shipfee = substr($firstPrice,1);
			else
			{
				$pcs = ceil( ($total_weight-$base) / $additional );
				
				$shipfee = substr($additionalPrice,1)*$pcs + substr($firstPrice,1) ;				
			}
			
			$true_total = $shipfee + substr($total,1);	
			
			
			$rs = array('stauts' => 1, 
			'total'=>substr($total,0,1).$true_total, 
			'ship_price' => substr($firstPrice,0,1).$shipfee);
			
			echo json_encode($rs);
			exit ;
			/*

			$design = M('design_winner') -> where('id=' . $_POST['design_id']) -> find();

			$data = array('design_id' => intval($_POST['design_id']), 'lang' => 9, 'num' => intval($_POST['num']), 'country' => trim($_POST['ctry']), 'first_name' => $_POST['first_name'], 'last_name' => $_POST['last_name'], 'company' => trim($_POST['company']), 'address' => trim($_POST['address']), 'zip' => trim($_POST['zip']), 'city' => trim($_POST['city']), 'email' => trim($_POST['email']), 'phone' => trim($_POST['phone']), 'winner_name' => $design['title'],'createtime'=>time());
			
			$pcs_ordered = M('design_order') -> where('design_id=' . intval($_POST['design_id'])) -> count();			
			
			$id = M('design_order') -> add($data);		

			if ($id) {

				$rs = array('stauts' => 1, 'msg' => 'Submitted successfully. We will get back to you soon!');

				echo json_encode($rs);
				exit ;

			} else {

				$rs = array('stauts' => 0, 'msg' => 'Submitted Failure!');

				echo json_encode($rs);
				exit ;

			}
			
			*/

		}

	} 

}
?>