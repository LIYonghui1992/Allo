<?php

/**

 *

 * DesignAction.class.php (前台Design challenge)

 *

 */

require_once(ROOT.'/Stripe/init.php');

if (!defined("App"))
	exit("Access Denied");

class DesignNestAction extends BaseAction {	
	
	public function bk_index() {

		//头部banner
		$map['lang'] = 9;// LANG_ID;		
		

		$map['status'] = 1;

		$design_challenge = M('design_challenge') -> where($map) -> find();

		$banners = array(0 => $design_challenge['banner_one'], 1 => $design_challenge['banner_two'], 2 => $design_challenge['banner_three']);

		
		//find out current projects, deadline>current>winner_date
		$current = time();//error_log('current:'.$current);
		//$where['lang'] = LANG_ID;
		$where['status'] = 1;
		$where['deadline'] = array('gt',$current);
		$where['winner_date'] = array('lt',$current);
		
		
		$count = M('design_winner') -> where($where)->count ( $id );
		$pagecount = intval($count/4) ;
		if($count%4 > 0)
		  $pagecount += 1;
		  
		  
		//$fund_list = M('design_winner') -> where($where) -> order('listorder asc,updatetime desc') ->limit(0,4)->select();
		$fund_list = M('design_winner') -> where($where) -> order('listorder asc,updatetime desc')->limit(0,8)->select();
    
		foreach ($fund_list as $k => $v) {
			
			$pcs_ordered = 0;

			$fund_list[$k]['date'] = date("Y.m.d",$v['winner_date']);  
			//var_dump($fund_list[$k]['date']);     
			
			//$v['winner_date'] intval(($v['deadline'] - time()) / (3600 * 24));
			
			$orderwhere['designwinner_id'] = $v['id'];
			$orderwhere['payflag'] = 1;
			
			$pcs_ordereds  = M('design_order') -> where($orderwhere) -> select();

			//$pcs_ordereds  = M('design_order') -> where('designwinner_id=' . $v['id']) -> select();
			
			foreach($pcs_ordereds as $key=>$val){
				
				$pcs_ordered += $val['qty'];
				
			}
			
			//$fund_list[$k]['pcs_ordered2'] = $pcs_ordered;
			
			//if($pcs_ordered>$v['funding_goal']) $pcs_ordered = $v['funding_goal'];

			$fund_list[$k]['pcs_ordered'] = $pcs_ordered;
			
			$dayleft = 0;
			$dayleft = ceil(($current - $v['winner_date'])/86400);
			$fund_list[$k]['dayleft'] = $dayleft;
			
			
			$pos = strrpos($v['price'], "."); 
			if(!empty($pos)){
			  $fund_list[$k]['price_cent'] = substr ($v['price'], $pos+1 );
			  $fund_list[$k]['price'] = substr ($v['price'],0, $pos );
			}
			
			//$fund_list[$k]['winner_text'] = $v['winner_text'];

		}
		
		$where2['status'] = 1;
		$where2['deadline'] = array('lt',$current);
		$where2['winner_date'] = array('lt',$current);
		
		$suc_fund_list = M('design_winner') -> where($where2) -> order('listorder asc,updatetime desc')->limit(0,4)->select();
		foreach ($suc_fund_list as $k => $v) {
			
			$pcs_ordered = 0;

			$suc_fund_list[$k]['date'] = date("Y.m.d",$v['winner_date']);  
			
			$orderwhere['designwinner_id'] = $v['id'];
			$orderwhere['payflag'] = 1;
			
			$pcs_ordereds  = M('design_order') -> where($orderwhere) -> select();
			
			foreach($pcs_ordereds as $key=>$val){
				
				$pcs_ordered += $val['qty'];
				
			}
		
			$suc_fund_list[$k]['pcs_ordered'] = $pcs_ordered;
			
			$dayleft = 0;
			$dayleft = ceil(($current - $v['winner_date'])/86400);
			$past_fund_list[$k]['dayleft'] = $dayleft;
			
			
			$pos = strrpos($v['price'], "."); 
			if(!empty($pos)){
			  $suc_fund_list[$k]['price_cent'] = substr ($v['price'], $pos+1 );
			  $suc_fund_list[$k]['price'] = substr ($v['price'],0, $pos );
			}
			
		}

		
		
	//年份列表, only select years that have project 	 
	 $yearwhere['deadline'] = array('lt',time());
	 //$yearwhere['lang'] = LANG_ID;
	 $yearwhere['status'] = 1;	
	 $yearlist = M('design_winner')->Distinct(true)->field('winneryear')->where($yearwhere)-> order('winneryear  desc')->select();
	 if($yearlist)
	 {
	 	 foreach ($yearlist as $k => $v)
  	 {
  	 	if($v['winneryear']!='0')
  	 	 $years[] = $v['winneryear'];
  	 }
  	 /*
  	 $pastwhere['lang'] = LANG_ID;
  	 $pastwhere['status'] = 1;		 
  	 $pastwhere['winneryear'] = array('eq',$years[0]);
  	 $pastwhere['deadline'] = array('lt',time());
  	 $pastcount = M('design_winner') -> where($pastwhere)->count ( $id );
  	 $pastpagecount = intval($pastcount/4) ;
  	 if($pastcount%4 > 0)
  		  $pastpagecount += 1;
  	
  	 $pastfund_list = M('design_winner') -> where($pastwhere) -> order('listorder asc,updatetime desc') ->limit(0,4)->select();
  	 //$pastfund_list = M('design_winner') -> where($pastwhere) -> order('listorder asc,updatetime desc')->select();
  	 
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
		 
    */
	 }
	 if(empty($years))
	   $years = array(date('Y'));	 
		
		//var_dump($years);//$this -> assign('get_year', $get_year);
		//$pastpagecount=1;
		$this -> assign('banners', $banners);  
		$this -> assign('design_challenge', $design_challenge);
		$this -> assign('years', $years);		
		//$this -> assign('pastfund_list', $pastfund_list);
		//$this -> assign('pastpagecount', $pastpagecount);
		$this -> assign('pastcurrpage', 1);
		$this -> assign('pagecount', $pagecount);
		$this -> assign('currpage', 1);
		$this -> assign('fund_list', $fund_list); //var_dump($fund_list);
		
		$this -> assign('past_fund_list', $past_fund_list); 
		
		
		$this -> display();

	}
	
	
	
	public function update_order_qty(){
		$fund_list = M('design_winner')->select();
    
		foreach ($fund_list as $k => $v) {
			
			$pcs_ordered = 0;
			
			$orderwhere['designwinner_id'] = $v['id'];
			$orderwhere['payflag'] = 1;
			
			$pcs_ordereds  = M('design_order') -> where($orderwhere) -> select();
			
			foreach($pcs_ordereds as $key=>$val){
				
				$pcs_ordered += $val['qty'];
				
			}			
			
			M('design_winner')->where('id='. $v['id'])->setField('order_qty',$pcs_ordered);


			//$fund_list[$k]['pcs_ordered'] = $pcs_ordered;
			
		}
	}
	
	
	
	
	public function index() {

		//头部banner
		$map['lang'] = 9;// LANG_ID;		
		

		$map['status'] = 1;

		$design_challenge = M('design_challenge') -> where($map) -> find();

		$banners = array(0 => $design_challenge['banner_one'], 1 => $design_challenge['banner_two'], 2 => $design_challenge['banner_three']);

		
		//find out current projects, deadline>current>winner_date
		$current = time();//error_log('current:'.$current);
		//$where['lang'] = LANG_ID;
		$where['status'] = 1;
		$where['deadline'] = array('gt',$current);
		$where['winner_date'] = array('lt',$current);
		
		
		$count = M('design_winner') -> where($where)->count ( $id );
		$pagecount = intval($count/4) ;
		if($count%4 > 0)
		  $pagecount += 1;
		  
		  
		//$fund_list = M('design_winner') -> where($where) -> order('listorder asc,updatetime desc') ->limit(0,4)->select();
		$fund_list = M('design_winner') -> where($where) -> order('listorder asc,updatetime desc')->limit(0,8)->select();
    
		foreach ($fund_list as $k => $v) {
			
			$pcs_ordered = 0;

			$fund_list[$k]['date'] = date("Y.m.d",$v['winner_date']);  
			//var_dump($fund_list[$k]['date']);     
			
			//$v['winner_date'] intval(($v['deadline'] - time()) / (3600 * 24));	
			
			$dayleft = 0;
			$dayleft = ceil(($current - $v['winner_date'])/86400);
			$fund_list[$k]['dayleft'] = $dayleft;
			
			
			$pos = strrpos($v['price'], "."); 
			if(!empty($pos)){
			  $fund_list[$k]['price_cent'] = substr ($v['price'], $pos+1 );
			  $fund_list[$k]['price'] = substr ($v['price'],0, $pos );
			}
			$fund_list[$k]['target_money'] = intval($fund_list[$k]['funding_goal'])*substr ($v['price'],1);
			
			
			//$fund_list[$k]['winner_text'] = $v['winner_text'];

		}
		
		$where2['status'] = 1;
		//$where2['deadline'] = array('gt',$current);
		//$where2['winner_date'] = array('lt',$current);
		
		$suc_fund_list = M('design_winner') -> where($where2)->where("funding_goal <= order_qty") -> order('listorder asc,updatetime desc')->limit(0,4)->select();
		foreach ($suc_fund_list as $k => $v) {
			
			$pcs_ordered = 0;

			$suc_fund_list[$k]['date'] = date("Y.m.d",$v['winner_date']);  
						
			$dayleft = 0;
			$dayleft = ceil(($current - $v['winner_date'])/86400);
			$suc_fund_list[$k]['dayleft'] = $dayleft;
			
			
			$pos = strrpos($v['price'], "."); 
			if(!empty($pos)){
			  $suc_fund_list[$k]['price_cent'] = substr ($v['price'], $pos+1 );
			  $suc_fund_list[$k]['price'] = substr ($v['price'],0, $pos );
			}
			$suc_fund_list[$k]['target_money'] = intval($fund_list[$k]['funding_goal'])*substr ($v['price'],1);
		}

 
		
		//var_dump($years);//$this -> assign('get_year', $get_year);
		//$pastpagecount=1;
		$this -> assign('banners', $banners);  
		$this -> assign('design_challenge', $design_challenge);
		$this -> assign('pagecount', $pagecount);
		$this -> assign('fund_list', $fund_list); //var_dump($fund_list);
		
		$this -> assign('past_fund_list', $suc_fund_list); 
				
		$this -> display();

	}
	
	
	private function bk_search_current_project($keyword,$page1,&$count){
		
		$current = time();//error_log('current:'.$current);
		//$where['lang'] = LANG_ID;
		$where['status'] = 1;
		$where['deadline'] = array('gt',$current);
		$where['winner_date'] = array('lt',$current);		
		//$where['title|developer']=array('like',"%".$keyword."%");
		$where['title|developer']=array('exp',"like '%".$keyword."%'");
		
		$sort = 'listorder asc,updatetime desc';
		
		
		$count = M('design_winner') -> where($where) ->count();
		
		
		import("@.ORG.Page");
		 
		$p = new Page($count, 16);
		/*		
		foreach($map as $key=>$val) {
		    $p->parameter   .=   "keyword=".urlencode($keyword).'&';
    }
		*/
		$p->parameter   .=   "keyword=".urlencode($keyword).'&search=1&';
		//$blogs = M('blog') -> where($map) -> limit($p -> firstRow . ',' . $p -> listRows) -> order('updatetime desc') -> select();
		
		$fund_list = array();		
		//$fund_list = M('design_winner') -> where($where) -> order($sort)->select();
		$fund_list = M('design_winner') -> where($where) -> limit($p -> firstRow . ',' . $p -> listRows)->order($sort)->select();
		
		
		$page = $p -> show();
		
		$this -> assign('page', $page);
		
		return $fund_list;
	}
	
	
	private function search_current_project($suc,$keyword,&$count,$start,$size){
		
		$current = time();//error_log('current:'.$current);
		//$where['lang'] = LANG_ID;
		$where['status'] = 1;
		if($suc==0){
			$where['deadline'] = array('gt',$current);
		  $where['winner_date'] = array('lt',$current);
		}
		
			
		//$where['title|developer']=array('like',"%".$keyword."%");
		$where['title|developer']=array('exp',"like '%".$keyword."%'");
		
		$sort = 'listorder asc,updatetime desc';
		
		if($suc==0)
		  $count = M('design_winner') -> where($where) ->count();
		else
		  $count = M('design_winner') -> where($where) ->where("funding_goal <= order_qty") ->count();
		
	
		$fund_list = array();		
		//$fund_list = M('design_winner') -> where($where) -> order($sort)->select();
		
		 
		if($suc==0)
		  $fund_list = M('design_winner') -> where($where) -> limit($start.','.$size)->order($sort)->select();
		else
		  $fund_list = M('design_winner') -> where($where) ->where("funding_goal <= order_qty")-> limit($start.','.$size)->order($sort)->select();
		
		return $fund_list;
	}
	
	
	private function sort_current_project($suc,$params,&$count,$start,$size){
		
		//find out current projects, deadline>current>winner_date
		$current = time();//error_log('current:'.$current);
		//$where['lang'] = LANG_ID;
		$where['status'] = 1;
		if($suc==0){
			$where['deadline'] = array('gt',$current);
		  $where['winner_date'] = array('lt',$current);
		}
		
		
		$sort = 'listorder asc,updatetime desc';
		
		if(isset($params['sort']) && intval($params['sort'])==1){
			$sort = 'winner_date desc';
			if(isset($params['type']) && intval($params['type'])==1)
			  $sort = 'winner_date asc';
		}
		
		if($suc==0)
		  $count = M('design_winner') -> where($where) ->count();
		else
		  $count = M('design_winner') -> where($where)->where("funding_goal <= order_qty") ->count();
		
		$fund_list = array();		
		
		if($suc==0)
		  $fund_list = M('design_winner') -> where($where) -> limit($start.','.$size)->order($sort)->select();
		else
		  $fund_list = M('design_winner') -> where($where) ->where("funding_goal <= order_qty")-> limit($start.','.$size)->order($sort)->select();
		
		
		
		return $fund_list;
	}
	
	
	
	private function bk_sort_current_project($params,$page1,&$count){
		
		//find out current projects, deadline>current>winner_date
		$current = time();//error_log('current:'.$current);
		//$where['lang'] = LANG_ID;
		$where['status'] = 1;
		$where['deadline'] = array('gt',$current);
		$where['winner_date'] = array('lt',$current);
		
		$sort = 'listorder asc,updatetime desc';
		
		if(isset($params['sort']) && intval($params['sort'])==1){
			$sort = 'winner_date desc';
			if(isset($params['type']) && intval($params['type'])==1)
			  $sort = 'winner_date asc';
		}
		/*
		if(isset($_GET['sort']) && intval($_GET['sort'])==2){
			$sort = 'winner_date desc';
			if(isset($_GET['type']) && intval($_GET['type'])==1)
			  $sort = 'winner_date asc';
		}
		*/
		
		$count = M('design_winner') -> where($where) ->count();
		
		import("@.ORG.Page");
		 
		$p = new Page($count, 16);
				
		foreach($params as $key=>$val) {
		    $p->parameter   .=   "$key=".urlencode($val).'&';
    }
		
		//$p->parameter   .=   "keyword=".urlencode($keyword).'&search=1&';
		
		$fund_list = array();		
		//$fund_list = M('design_winner') -> where($where) -> order($sort)->select();
		$fund_list = M('design_winner') -> where($where) -> limit($p -> firstRow . ',' . $p -> listRows)->order($sort)->select();
		
		
		$page = $p -> show();
		
		$this -> assign('page', $page);
		  
		
		return $fund_list;
	}
	
	
	
	private function process_fund_list(&$fund_list){
		$current = time();

		foreach ($fund_list as $k => $v) {			
      /*
			$pcs_ordered = 0;
			
			$orderwhere['designwinner_id'] = $v['id'];
			$orderwhere['payflag'] = 1;			
			$pcs_ordereds  = M('design_order') -> where($orderwhere) -> select();			
			foreach($pcs_ordereds as $key=>$val){				
				$pcs_ordered += $val['qty'];				
			}
			
			//$fund_list[$k]['pcs_ordered2'] = $pcs_ordered;			
			$fund_list[$k]['pcs_ordered'] = $pcs_ordered;
			*/
			$fund_list[$k]['date'] = date("Y.m.d",$v['winner_date']); 
			
			$dayleft = 0;
			//$dayleft = ceil(($v['deadline'] - $current)/86400);
			//$fund_list[$k]['dayleft'] = $dayleft;
			$dayleft = ceil(($current - $v['winner_date'])/86400);
			$fund_list[$k]['dayleft'] = $dayleft;
			
			$pos = strrpos($v['price'], "."); 
			if(!empty($pos)){
			  $fund_list[$k]['price_cent'] = substr ($v['price'], $pos+1 );
			  $fund_list[$k]['price'] = substr ($v['price'],0, $pos );
			}
			else
			  $fund_list[$k]['price_cent']='';
			  
			$fund_list[$k]['target_money'] = intval($fund_list[$k]['funding_goal'])*substr ($v['price'],1);
		}
	}
	
	
	/**
	** current project list, default select all current project
	*/
	public function project_list(){
		//头部banner
		$map['lang'] = 9;// LANG_ID;		
		$map['status'] = 1;
		$design_challenge = M('design_challenge') -> where($map) -> find();

		$banners = array(0 => $design_challenge['banner_one'], 1 => $design_challenge['banner_two'], 2 => $design_challenge['banner_three']);

		$searching = 0;
		$fund_list = array();
		
		$count = 0;
		if(isset($_GET['search']) && intval($_GET['search'])==1)
		{
			$fund_list = $this->search_current_project(0,$_GET['keyword'],$count,0,16);
			$searching = 1;
		}
		else{
			$fund_list = $this->sort_current_project(0,$_GET,$count,0,16);
		}
		
		
		$this->process_fund_list($fund_list);
		
		
		if(isset($_GET['type']))
		  $this -> assign('sort_type', $_GET['type']);
		//else
		 // $this -> assign('sort_type', '1');
		
		if(isset($_GET['keyword']))
		  $this -> assign('keyword', $_GET['keyword']);
		else
		  $this -> assign('keyword', '');
	
		$this -> assign('count', $count);
		$this -> assign('curr_count', 16);
		$this -> assign('searching', $searching);
		$this -> assign('banners', $banners);  
		$this -> assign('fund_list', $fund_list); //var_dump($fund_list);
		$this -> display();
	}
	
	
	
	
	public function project_list_suc(){
		//头部banner
		$map['lang'] = 9;// LANG_ID;		
		$map['status'] = 1;
		$design_challenge = M('design_challenge') -> where($map) -> find();

		$banners = array(0 => $design_challenge['banner_one'], 1 => $design_challenge['banner_two'], 2 => $design_challenge['banner_three']);

		$searching = 0;
		$fund_list = array();
		
		$count = 0;
		if(isset($_GET['search']) && intval($_GET['search'])==1)
		{
			$fund_list = $this->search_current_project(1,$_GET['keyword'],$count,0,16);
			$searching = 1;
		}
		else{
			$fund_list = $this->sort_current_project(1,$_GET,$count,0,16);
		}
		
		
		$this->process_fund_list($fund_list);
		
		
		if(isset($_GET['type']))
		  $this -> assign('sort_type', $_GET['type']);
		//else
		 // $this -> assign('sort_type', '1');
		
		if(isset($_GET['keyword']))
		  $this -> assign('keyword', $_GET['keyword']);
		else
		  $this -> assign('keyword', '');
	
		$this -> assign('count', $count);
		$this -> assign('curr_count', 16);
		$this -> assign('searching', $searching);
		$this -> assign('banners', $banners);  
		$this -> assign('fund_list', $fund_list); //var_dump($fund_list);
		$this -> display();
	}
	
	
	
	
	
	public function ajax_getprojectlist(){
		//error_log(json_encode($_POST));
		if (IS_POST){
			$searching = intval($_POST['searching']);
			
			$curr_count = intval($_POST['curr_count']);
			$keyword = $_POST['keyword'];
			
			$suc = 0;
			if(isset($_GET['suc']))
	      $suc = intval($_GET['suc']);
	      
  		$fund_list = array();
  		
  		if($searching==1)
  		{
  			$fund_list = $this->search_current_project($suc,$keyword,$count,$curr_count,8);
  		}
  		else{
  			if(isset($_POST['sort_type'])){
  				$sort_type = $_POST['sort_type'];
  			  $fund_list = $this->sort_current_project($suc,array('sort'=>1,'type'=>$sort_type),$count,$curr_count,8);
  			}
  			else
  			  $fund_list = $this->sort_current_project($suc,array(),$count,$curr_count,8);

  		}
  		
  		//error_log(json_encode($fund_list));
  		
  		$this->process_fund_list($fund_list);
  		
  		$data = array('status' => 1, 'filelist' => $fund_list);
			echo json_encode($data);
			exit ;
  		
		}
	}
	
	
	
	public function pastProducts()
	{
		//pageid  pastyear
		if (IS_POST)
		{
			$pageid = intval($_POST['pageid']);
			$pastyear = intval($_POST['pastyear']);
			
			//$pastwhere['lang'] = LANG_ID;
   	  $pastwhere['status'] = 1;	
			$pastwhere['winneryear'] = array('eq',$pastyear);
	    $pastwhere['deadline'] = array('lt',time());
	 
			  
   	 //$pastfund_list = M('design_winner') -> where($pastwhere) -> order('listorder asc,updatetime desc') ->limit($pageid*4,4)->select();
   	 //$pastfund_list = M('design_winner') -> where($pastwhere) -> order('listorder asc,updatetime desc')->select();
   		  		
			  
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
   	 //$yearlist = M('design_winner')->Distinct(true)->field('winneryear')->where($yearwhere)->select();
   	 
   	 $yearlist = M('design_winner')->Distinct(true)->field('winneryear')->where($yearwhere)-> order('winneryear  desc')->select();


   	 if(!$yearlist)
   	 {
   	 }
   	 
   	 foreach ($yearlist as $k => $v)
   	 {
   	 	if($v['winneryear']!='0')
   	 	 $years[] = $v['winneryear'];
   	 }
   	 foreach ($years as $k => $v)
   	 {
   	 	if($v== $pastyear)
   	 	 $yearno = $k;
   	 }
   	 
   	 
	    if(($yearid==0 && $pastyear==$years[0]) || ($yearid==1 && $pastyear==$years[sizeof($years)-1] ) )
	    {
	    	$data = array('status' => 0,'pastyear'=>$pastyear);
  			echo json_encode($data);
  			exit ;
	    }
	    
	    
	    
	    if($yearid==0)
	    {
	    	$pastyear = $years[$yearno -1] ;
	    	$yearid = $pastyear;
	    }
							
			if($yearid==1) 
			{
				$pastyear = $years[$yearno +1] ; //$pastyear-1;
				$yearid = $pastyear;
			}
/*
   	 
   	 $pastwhere['lang'] = LANG_ID;
	   $pastwhere['status'] = 1;		 
	   $pastwhere['winneryear'] = array('eq',$yearid);
	   $pastwhere['deadline'] = array('lt',time());
	 
	 
   	 $pastcount = M('design_winner') -> where($pastwhere)->count ( $id );
   	 $pastpagecount = intval($pastcount/4) ;
   		if($pastcount%4 > 0)
   		  $pastpagecount += 1;
   		
   		
			  
   	 $pastfund_list = M('design_winner') -> where($pastwhere) -> order('listorder asc,updatetime desc') ->limit(0,4)->select();
   	 //$pastfund_list = M('design_winner') -> where($pastwhere) -> order('listorder asc,updatetime desc')->select();
   		
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

		 */
			
		 $data = array('status' => 1, 'pastyear'=>$yearid);
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
  		//$where['lang'] = LANG_ID;
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
	

  
  
  public function detail() {
  	
  		//头部banner
		//$map['lang'] = LANG_ID;		
		$map['lang'] = 9;

		$map['status'] = 1;

		$design_challenge = M('design_challenge') -> where($map) -> find();

		$banners = array(0 => $design_challenge['banner_one'], 1 => $design_challenge['banner_two'], 2 => $design_challenge['banner_three']);
		$this -> assign('banners', $banners); 
		
		//$where['lang'] = 9;
		$where['status'] = 1;
		$designwinner_id = $_GET['id'];
		$where['id'] = intval($designwinner_id);

		$fund_list = M('design_winner') -> where($where) -> select();
		if($fund_list)
		  $fund = $fund_list[0];
    
    if (!empty($fund['slide_pic'])) {
			
			$problem_img = explode(':::', $fund['slide_pic']);
			
			foreach ($problem_img as $k => $v) {

				$problem_img[$k] = strstr($v, '|', TRUE);

			}
			$fund['slide_pic'] = $problem_img;
			
		}
		
		if (!empty($fund['slide_pic_2'])) {
			
			$problem_img_2 = explode(':::', $fund['slide_pic_2']);
			
			foreach ($problem_img_2 as $k => $v) {

				$problem_img_2[$k] = strstr($v, '|', TRUE);

			}
			$fund['slide_pic_2'] = $problem_img_2;
			
		}
		//$fund['remaining'] = intval(($fund['deadline'] - time()) / (3600 * 24));
		
		$pcs_ordered = 0;
		
		$orderwhere['designwinner_id'] = $fund['id'];
		$orderwhere['payflag'] = 1;
		$pcs_ordereds  = M('design_order') -> where($orderwhere) -> select();
			
		//$pcs_ordereds = M('design_order') -> where('designwinner_id=' . $fund['id']) -> select();
		foreach($pcs_ordereds as $key=>$val){
				
				$pcs_ordered += $val['qty'];
				
		}
		$fund['pcs_ordered2'] = $pcs_ordered;
		//if($pcs_ordered>$fund['funding_goal']) $pcs_ordered = $fund['funding_goal'];

		$fund['pcs_ordered'] = $pcs_ordered;
			
		$fund['winner_text'] = $fund['winner_text'];
		
		$fund['orderflag'] = 0;
		if($fund['deadline'] > time())
		  $fund['orderflag'] = 1;
		
		//$map['lang'] = 9;
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
		
		
		//feature list
		$features = M('design_feature')->where('design_id ='.$designwinner_id)->order('listorder')->select();
		$this -> assign('features', $features);
		
		//error_log(json_encode($features));

		$this -> assign('type_list', $type_list);
		$this -> assign('type_listsize', $size);
		$this -> assign('fund', $fund);

		$this -> display();

	}
	
	
	/**  find out designwinner list of year, default 1st  past year*/
	public function designyear()
	{
		if($_REQUEST['pastyear'])
			$pastyear = intval($_REQUEST['pastyear']);
		else
		{
			//get yearlist first, then, decide yearid
			//年份列表, only select years that have project 	 
     	 $yearwhere['deadline'] = array('lt',time());   	 
     	 $yearlist = M('design_winner')->Distinct(true)->field('winneryear')->where($yearwhere)-> order('winneryear  desc')->select();
  
  
     	 if($yearlist)
     	 {
       	 foreach ($yearlist as $k => $v){
       	 	if($v['winneryear']!='0')
       	 	 $years[] = $v['winneryear'];
       	 }
       	 if(sizeof($years)< 1)
       	   $pastyear = -1;     	   
       	 else
       	   $pastyear = $years[0];
     	 }
     	 else
     	   $pastyear = -1;
  	 
		}	
		
		if($pastyear == -1)
		{
			$this -> assign('designwinnerlist', NULL);
			$this -> display();
			return;
		}
			
	 
			//$pastwhere['lang'] = LANG_ID;
   	  $pastwhere['status'] = 1;	
			$pastwhere['winneryear'] = array('eq',$pastyear);
	    $pastwhere['deadline'] = array('lt',time());  
   	  //$pastfund_list = M('design_winner') -> where($pastwhere) -> order('listorder asc,updatetime desc') ->limit($pageid*4,4)->select();
   	 $pastfund_list = M('design_winner') -> where($pastwhere) -> order('listorder asc,updatetime desc')->select();
   		  		
			  
   		
   	 foreach ($pastfund_list as $k => $v) {
			
			$pcs_ordered = 0;

			$pastfund_list[$k]['date'] = date("Y.m.d",$v['winner_date']); 
			
			
			$orderwhere['designwinner_id'] = $v['id'];
			$orderwhere['payflag'] = 1;
			$pcs_ordereds  = M('design_order') -> where($orderwhere) -> select();
			
			 
			//$pcs_ordereds  = M('design_order') -> where('designwinner_id=' . $v['id']) -> select();
			
			foreach($pcs_ordereds as $key=>$val){
				
				$pcs_ordered += $val['qty'];
				
			}
			
			$pastfund_list[$k]['pcs_ordered2'] = $pcs_ordered;
			
			//if($pcs_ordered>$v['funding_goal']) $pcs_ordered = $v['funding_goal'];

			$pastfund_list[$k]['pcs_ordered'] = $pcs_ordered;
			
			$pastfund_list[$k]['winner_text'] = $v['winner_text'];

		 }
		
		$this -> assign('designwinnerlist', $pastfund_list);
		$this -> display();
				
	}
	
	
	/**
	* find field:buy-page ,of table designchallenge, for designhouse page,find out more
	*/
	public function more()
	{
		/*
		//$where['lang'] = 9;
		//$where['status'] = 1;
		$designchallenge_id = $_GET['id'];
		
		$where['id'] = intval($designchallenge_id);

		$designchallenge = M('design_challenge') -> where($where) -> select();
		
		//var_dump($designchallenge);
		
		$this -> assign('challenge', $designchallenge[0]);
		*/
		
		
				//头部banner
		//$map['lang'] = LANG_ID;		
		$map['lang'] = 9;

		$map['status'] = 1;

		$design_challenge = M('design_challenge') -> where($map) -> find();

		$banners = array(0 => $design_challenge['banner_one'], 1 => $design_challenge['banner_two'], 2 => $design_challenge['banner_three']);
		$this -> assign('banners', $banners); 
		$designchallenge_id = $_GET['id'];
		
		$where['id'] = intval($designchallenge_id);

		$designchallenge = M('design_challenge') -> where($where) -> find();
		
		
		if (!empty($designchallenge['buy_page'])) {
			
			$imgs = explode(':::', $designchallenge['buy_page']);
			
			foreach ($imgs as $k => $v) {

				$imgs[$k] = strstr($v, '|', TRUE);

			}
		}

    $this -> assign('slider_imgs', $imgs);

		$this -> display();
		
	}
	 
	 
	 
	 


    
    /**
    *  order from design house
    */
    public function order_designwinner()
    {
    	//头部banner
  		//$map['lang'] = LANG_ID;		
  		$map['lang'] = 9;
  
  		$map['status'] = 1;
  
  		$design_challenge = M('design_challenge') -> where($map) -> find();
  
  		$banners = array(0 => $design_challenge['banner_one'], 1 => $design_challenge['banner_two'], 2 => $design_challenge['banner_three']);
  		$this -> assign('banners', $banners); 
		
    	$designwinner_id = $_REQUEST['id'];
    	$qty = $_REQUEST['qty'];    	   	
    	$typeid = $_REQUEST['typeid'];    
    	
    	session('dh_price','');
  		session('dh_qty','');
  		session('dh_total','');
  		session('dh_typeid','');
  		session('dh_designwinner_id','');

   
    	$where['id'] = $designwinner_id;			
  		$winner = M('design_winner') -> where($where)->select();
  		$bigpic = $winner[0]['background_img'];
  		$title = $winner[0]['title'];
  		$price = $winner[0]['price'];
  
  		$typename;
  		if(!empty($typeid))
  		{
  			$map['id'] = $typeid;
    		$type =  M('designwinner_price') -> where($map)->select();
    		$typepic = $type[0]['type_pic'];
    		$typename = $type[0]['type_name'];
    		$price = $type[0]['price'];
  		}
  		
  		
  		$session_price = $price;
  		$total = 0;
    	$price_no = substr ($price,1);
    	$total =  substr ($price,0,1).($price_no * intval($qty));
    	
    	
    	$Model = new Model();
    	//$sql = 'select a.id,a.title from a_country a,a_ship_price b where a.id=b.country_id order by a.title ';
    	//a_ship_price_new
    	$sql = 'select a.id,a.country_name as title from  a_ship_price_new a, a_design_winner b,a_ship_price_sets c'
    	.' where b.shipfee_set_id=a.shipfee_set_id  and c.status=1 and c.id=a.shipfee_set_id  and b.id='. $designwinner_id. ' order by country_name ';
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
  		
  		//write into session
  		session('dh_price',$price);
  		session('dh_qty',$qty);
  		session('dh_total',$total);
  		if(!empty($typeid))
  		  session('dh_typeid',$typeid);
  		session('dh_designwinner_id',$designwinner_id);
  		
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
			$countryid = session('dh_country');//trim($_POST['countryid']);			
			//a_ship_price_new
			//$info =  M('country') -> where('id=' . $countryid) -> find();
			$info =  M('ship_price_new') -> where('id=' . $countryid) -> find();
			$country = $info['country_name'];
			$order_id = $this ->build_order_no();
			$typeid = '';
			if(session('?dh_typeid'))
			  $typeid = session('dh_typeid');
			

			//$data = array('design_id' => intval($_POST['design_id']), 'lang' => 9, 'num' => intval($_POST['num']), 'country' => trim($_POST['ctry']), 'first_name' => $_POST['first_name'], 'last_name' => $_POST['last_name'], 'company' => trim($_POST['company']), 'address' => trim($_POST['address']), 'zip' => trim($_POST['zip']), 'city' => trim($_POST['city']), 'email' => trim($_POST['email']), 'phone' => trim($_POST['phone']), 'winner_name' => $design['title'],'createtime'=>time());
			$data = array(
			'orderid' => $order_id,
			'first_name' => $_POST['first_name'], 
			'last_name' => $_POST['last_name'], 
			'phone' => trim($_POST['phone']),
			'zip' => trim($_POST['zip']),
			'email' => trim($_POST['email']), 
			'qty' => session('dh_qty'),//trim($_POST['qty']),			   
			'designwinner_id' => intval(session('dh_designwinner_id')),//intval($_POST['designwinner_id']),
			'company' => trim($_POST['company']),
			'address' => trim($_POST['address']), 
			'city' => trim($_POST['city']), 						
			'country' => $country,
			'price_id' => $typeid,//trim($_POST['price_id']),
			'typename' => $_POST['typename'],				
			'price' => session('dh_price'),//trim($_POST['price']),
		  'shipfee' => session('dh_shipfee'),//trim($_POST['shipfee']),
			'total' => session('dh_true_total'),//trim($_POST['total']),
			'winner_name' => $_POST['designwinner_title'],			  
			'lang' => LANG_ID, 
			'payflag'=>0,
			'status'=>0,
			'paytype'=>$_POST['paytype'],
			'createtime'=>time());
			
						
						
			$id = M('design_order') -> add($data);		
			
			//fail tp add record
			if(!$id){

				$rs = array('stauts' => 0, 'msg' => 'Error: Failed to submit!');

				echo json_encode($rs);
				exit ;

			}

			//paypal
			if ($_POST['paytype']==1) {

        //build payment url,return ,then javascript reward to paypal
        //$gateway = 'https://www.paypal.com/cgi-bin/webscr?';
        $gateway = 'https://www.paypal.com/cgi-bin/webscr?';//https://www.sandbox.paypal.com/cgi-bin/webscr?
        $account = 'arthur.limpens@allocacoc.com';//jing.wang@allocacoc.com.cn
        $pp_info = array();// 初始化准备提交到Paypal的数据  
        $pp_info['cmd'] = '_xclick';// 告诉Paypal，我的网站是用的我自己的购物车系统  
        $pp_info['business'] = $account;// 告诉paypal，我的（商城的商户）Paypal账号，就是这钱是付给谁的  
        $pp_info['item_name'] = "支付订单：{$order_id}";// 用户将会在Paypal的支付页面看到购买的是什么东西，只做显示，没有什么特殊用途，如果是多件商品，则直接告诉用户，只支付某个订单就可以了  
        //$pp_info['amount'] = substr(trim($_POST['total']),1); // 告诉Paypal，我要收多少钱
        $pp_info['amount'] = session('dh_true_total');
        $pp_info['currency_code'] = 'USD';// 告诉Paypal，我要用什么货币。这里需要注意的是，由于汇率问题，如果网站提供了更改货币的功能，那么上面的amount也要做适当更改，paypal是不会智能的根据汇率更改总额的  
        $pp_info['return'] = 'http://www.allocacoc.com/DesignNest/index.html';// http://test.allocacoc.com/Design/index.html 当用户成功付款后paypal会将用户自动引导到此页面。如果为空或不传递该参数，则不会跳转  
        $pp_info['invoice'] = $order_id;  
        $pp_info['charset'] = 'utf-8';  
        $pp_info['no_shipping'] = '1';  
        $pp_info['no_note'] = '1';  
        $pp_info['cancel_return'] = 'http://www.allocacoc.com/DesignNest/index.html';// 当跳转到paypal付款页面时，用户又突然不想买了。则会跳转到此页面  
        $pp_info['notify_url'] = 'http://www.allocacoc.com/DesignNest/paypal_notify/orderid/'.$order_id .'/';
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
				\Stripe\Stripe::setApiKey("sk_live_OghD65j74QWhlUkrwg6zxdic");// sk_test_m4E7TtseOCPUqrcefiRaUJPK  sk_test_f3jgU2hZxqndFQI7j9qpThSD
				$token = $_POST['stripetoken'];
				try {
            $charge = \Stripe\Charge::create(array(
              "amount" => intval(session('dh_true_total')*100), // amount in cents, again
              "currency" => "usd",
              "source" => $token, 
              "description" => $order_id )
            );          
            //error_log('charge:'.json_encode($charge));
            if ($charge['paid']!=true)//succeeded
            {
            	$rs = array('stauts' => 0,'msg'=>"fail to pay!");
            	echo json_encode($rs);
            	exit ;
            }
            
            //modify payflag            
            $result = M('design_order')->where('orderid=\''. $order_id.'\'')->setField('payflag',1);
            
            $result2 = M('design_winner')->where('id='. intval(session('dh_designwinner_id')))->setInc('order_qty',session('dh_qty'));
            
            
            
            if(!$result || !$result2)
            {
            	error_log("PayFlag modify error: fail to modify flag, order is ".$order_id."\n");
            }
            
            //mail to customer
            
            $this->sendmail(trim($_POST['email']),$data);
            
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
        //$order_id = (int) $_REQUEST['orderid'];
        $order_id =  $_GET['orderid'];
        
      
        $order_info = M('design_order')->where('orderid =\''. $order_id.'\'' )->find();
        // 由于该URL不仅仅只有Paypal的服务器能访问，其他任何服务器都可以向该方法发起请求。所以要判断请求发起的合法性，也就是要判断请求是否是paypal官方服务器发起的

        // 拼凑 post 请求数据
        $req = 'cmd=_notify-validate';// 验证请求
        foreach ($_POST as $k=>$v)
        {
            $v = urlencode(stripslashes($v));
            $req .= "&{$k}={$v}";
        }

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,'https://www.paypal.com/cgi-bin/webscr');//https://www.sandbox.paypal.com/cgi-bin/webscr
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$req);
        $res = curl_exec($ch);
        curl_close($ch);
        
        //$res = 'VERIFIED';
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
                    error_log('payment status is fail');
                    exit('fail');
                } else {// 如果验证通过，则证明本次请求是合法的
                	error_log('payment status is succ');
                	
                
                    $result = M('design_order')->where('orderid=\''. $order_id.'\'')->setField('payflag',1);
                    $result2 = M('design_winner')->where('id='. $order_info['designwinner_id'])->setInc('order_qty',$order_info['qty']);
                    
                    if(!$result || !$result2)
                    {
                    	error_log("PayFlag modify error: fail to modify flag, order is ".$order_id."\n");
                    	exit('fail');
                    }
                    //mail to customer
                    $mail = M('design_order')->where('orderid=\''. $order_id.'\'')->find();
                    error_log('$mail:'.$mail['email']);
                    error_log('$order_info:'.json_encode($order_info));
                    $this->sendmail($mail['email'],$order_info);
            
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

				$rs = array('stauts' => 0, 'msg' => 'Error: Failed to submit!');

				echo json_encode($rs);
				exit ;

			}

		}

	}
	
	

	public function shipPrice(){
		

		if (IS_POST) {
	
			$countryid =  $_POST['countryid'];
			session('dh_country',$countryid);
			$qty =  session('dh_qty');//$_POST['qty'];
			$designwinner_id =  session('dh_designwinner_id');//$_POST['designwinner_id'];
			$total = session('dh_total');//$_POST['total'];
			
			//calculate shipping price ,according to weight country
			$design = M('design_winner') -> where('id=' .$designwinner_id) -> find();
			if(!$design)
			{
				$rs = array('stauts' => 0, 'msg' => 'Sorry, no product now!');
				echo json_encode($rs);
				exit ;
			}
			$total_weight = intval($design['weight']) * intval($qty);
			
			//a_ship_price_new
			//$ship_price = M('ship_price') -> where('country_id=' .$countryid) -> find();
			$ship_price = M('ship_price_new') -> where('id=' .$countryid) -> find();
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
			session('dh_shipfee',$shipfee);	
			session('dh_true_total',$true_total);
			
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
	
	
	
	
	public function sendmail($email,$order_info)
   {				
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
      
      
      /*
      $mail->Username = "designhouse.orders@allocacoc.com"; // SMTP username 注意：普通邮件认证不需要加 @域名 
      $mail->Password = "egx-7VW-Y4o-Bps"; // SMTP password 
      */
     
      $mail->IsSMTP(); // send via SMTP 
      $mail->Host = "mail.allocacoc.com"; // SMTP servers 
      $mail->SMTPAuth = true; // turn on SMTP authentication 
      $mail->Username = "designhouse.orders@allocacoc.com"; // SMTP username 注意：普通邮件认证不需要加 @域名 
      $mail->Password = "egx-7VW-Y4o-Bps"; // SMTP password  
      $mail->From = "designhouse.orders@allocacoc.com"; // 发件人邮箱       
   
      
      
      $mail->FromName = "allocacoc"; // 发件人 ,比如 中国资金管理网
      
      $mail->CharSet = "GB2312"; // 这里指定字符集！ 
      $mail->Encoding = "base64"; 
     
      $mail->AddAddress($email,''); // 收件人邮箱和姓名 
        
      $mail->AddReplyTo("","allocacoc"); 
      
      
      $mail->IsHTML(true); // send as HTML 
      $mail->Subject = "Thanks for order our product, from Allocacoc"; 
      $mail->WordWrap = 80; // 设置每行字符串的长度 
      //$mail->Body = "Thank you for order our product, You payed successfully. We will send products to You as soon as possible.<br> Allocacoc.";
       $this->generateEmail($order_info,$content);

      $mail->Body = $content;
      
      $mail->AltBody ="To view the message, please use an HTML compatible email viewer!"; 
      if(!$mail->Send()) 
      { 
      	error_log('send error:'.$mail->ErrorInfo);     	
      }
			
		

   }
   
   
   // generate mail content for order, including customer info,shipping address,product list ,etc.
   public function generateEmail($order_info,&$content)
   {
   	 $paytype = '';
   	 if($order_info['paytype']==1)$paytype='Paypal';
   	 if($order_info['paytype']==2)$paytype='Card';
   	 
   	 $typename = '';
   	 
   	 if($order_info['price_id']!=0)
   	 {
   	 	$price_info = M('designwinner_price')->where('id ='. $order_info['price_id'] )->find();
   	  if($price_info)
   	    $typename = $price_info['type_name'];
   	 }
   	 
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
													.$order_info['orderid'].'</h2>
													<table style="width:100%;border:1px solid #eee" border="1" cellpadding="6" cellspacing="0" class="">
														<thead class="">
															<tr class="">
																<th scope="col" style="text-align:left;border:1px solid #eee" class="">Product</th>
																<th scope="col" style="text-align:left;border:1px solid #eee" class="">Quantity</th>
																<th scope="col" style="text-align:left;border:1px solid #eee" class="">Price</th>
															</tr>
														</thead>
													  <tbody class="">
													  	<tr class="">
													  		<td style="text-align:left;vertical-align:middle;border:1px solid #eee;word-wrap:break-word" class="">'.$order_info['winner_name'].'<br class="">
													  			<small class="">Socket Type: '. $typename.'</small>
													  		</td>
													  		<td style="text-align:left;vertical-align:middle;border:1px solid #eee" class="">'.$order_info['qty'].'</td>
													  		<td style="text-align:left;vertical-align:middle;border:1px solid #eee" class=""><span class="">'.$order_info['price'].'</span></td>
													  	</tr>
													  </tbody>
													  <tfoot class="">
													  	
													  	<tr class="">
													  		<th scope="row" colspan="2" style="text-align:left;border:1px solid #eee" class="">Shipping</th>
													  		<td style="text-align:left;border:1px solid #eee" class=""><span class="">'.$order_info['shipfee'].'</span>&nbsp;</td>
													  	</tr>
													  	<tr class="">
													  		<th scope="row" colspan="2" style="text-align:left;border:1px solid #eee" class="">Payment Method</th>
													  		<td style="text-align:left;border:1px solid #eee" class="">'.$paytype.'</td>
													  	</tr>
													  	<tr class="">
													  		<th scope="row" colspan="2" style="text-align:left;border:1px solid #eee" class="">Order Total</th>
													  		<td style="text-align:left;border:1px solid #eee" class=""><span class="">'.$order_info['total'].'</span> </td>
													  	</tr>
													  </tfoot>
												  </table>
												  
												  <h2 style="color:#505050;display:block;font-family:Arial;font-size:30px;font-weight:bold;margin-top:10px;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;line-height:150%" class="">Customer details</h2>
												  <p class=""><b class="">Email:</b> 
												  	<a href="mailto:'. $order_info['email'].'" target="_blank" class="">'. $order_info['email'].'</a></p><p class=""><b class="">Tel:</b> <a value="'.$order_info['phone'].'" target="_blank" class="">'.$order_info['phone'].'</a></p>
												  
												  <table style="width:100%;vertical-align:top" border="0" cellpadding="0" cellspacing="0" class="">
												  	<tbody class="">
												  		<tr class="">
												  			
												  			<td valign="top" width="50%" class="">
												  				<h3 style="color:#505050;display:block;font-family:Arial;font-size:26px;font-weight:bold;margin-top:10px;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;line-height:150%" class="">Shipping address</h3>
												  				
												  				<p class="">'.$order_info['address'].'
												  					<br class="">'.$order_info['city'].'
												  					<br class="">'.$order_info['country'].'
												  					<br class="">'.$order_info['zip'].'
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
   	 
   	


   }
   
   
   public function submit_design()
   {
   	  //first get cookie pics,pic amount, slider & feature
   /*
   	  $sliderpic_mount = 0;
   	  $feature_mount = 0;
   	  
   	  if(!empty($_COOKIE['slider']))
   	    $sliderpic_mount = count($_COOKIE['slider']);
   	   if(!empty($_COOKIE['feature']))
   	    $sliderpic_mount = count($_COOKIE['feature']);
   	  */
   	  
   	  // delete cookie, when refresh webpage, need upload info again
   	  cookie('slider',null);
   	  cookie('slider_picname',null);
   	  cookie('feature_pic',null);
   	  
   	  
     	$map['lang'] = 9;//LANG_ID;		  
  		$map['status'] = 1;
  
  		$design_challenge = M('design_challenge') -> where($map) -> find();
  
  		$banners = array(0 => $design_challenge['banner_one'], 1 => $design_challenge['banner_two'], 2 => $design_challenge['banner_three']);
  		$this -> assign('banners', $banners); 
		
   	 $country = D('Country') -> order('title')->select();	
    	
  		foreach($country as $k=>$v){
  			
  			$country_list[] = array('id'=>$v['id'],'title'=>$v['title']);
  			
  		}
  		$this -> assign('country_list',$country_list);
  
  		$this -> display();
  		
   }
   
   function getUploadDir()
   {
   	 $filedir = UPLOAD_PATH.date('Ym').'/';
   	 //if not exist,create dir
   	 if(!file_exists($filedir))   
   	 	 mkdir($filedir,0777);         //创建文件夹
   	 return $filedir;
   }
   
   public function submit_product_pic(){
   	
   	 if (IS_POST){
   	 	$filedir = $this->getUploadDir();//'D:/wamp/www/design_pics/';
   	 	
   	 	if( empty($_FILES))
		 	{
		 		$data = array('status'=>2,'msg'=>'Error: Failed to submit.');
		 	  echo json_encode($data);
		 	  exit ;
		 	}
		 	$picfile = $_FILES['choosefile5'];
		 	$picname = $picfile['name'];
		 	$picsize = $picfile['size'];
		 	if ($picsize > 1024000) {
	 	 	 	 $data = array('status'=>0,'msg'=>'Error: File size should not exceed 1MB.');
	 		   echo json_encode($data);
	 		   exit ;
	 	 	}
	 	 	//error_log('picname:'.$picname.','.pathinfo($picname, PATHINFO_EXTENSION));
	 	 	$filetype = pathinfo($picname, PATHINFO_EXTENSION);//strstr($picname, '.');
  	 	if ($filetype != "gif" && $filetype != "jpg" && $filetype != "png" ) {
  	 	 	 $data = array('status'=>0,'msg'=>'Error: File format should be .jpg, .gif or .png.');
  		   echo json_encode($data);
  		   exit ;
  	 	}
  	 	
  	 	if(!empty($_COOKIE['allocacoc_product_pic'])  )
		 	{
		 			unlink($_COOKIE['allocacoc_product_pic']);
		 			error_log('del:'.$_COOKIE['allocacoc_product_pic']);
		 	}	 		
		 	 	 
		 	 	
		 	$rand = rand(100, 999);
		 	$pics = 'design_'.date("YmdHis") . $rand . '.'.$filetype;
		 	//上传路径
		 	$pic_path = $filedir. $pics;
		 	$result = move_uploaded_file($picfile['tmp_name'], $pic_path);

 	 	  if(!$result)
 	 	  {
 	 	 	  $data = array('status'=>0,'msg'=>'Error: Failed to upload file.');
 	 	 	  echo json_encode($data);
 	 	 	  exit ;
 	    }
		 	cookie('product_pic',substr($pic_path,strlen(ROOT)-1));
		 	cookie('product_picname',$picname);
		 	//error_log(json_encode($_COOKIE));
		 			
		 	  
		 	$data = array('status'=>1,'name'=>$picname,'pic'=>substr($pic_path,strlen(ROOT)-1));
		 	echo json_encode($data);
		 	exit ; 	 
		 	
   	 }
   	
   }
   public function submit_designidea_pic(){

		 if (IS_POST) {			 	 	
		 	$filedir = $this->getUploadDir();//'D:/wamp/www/design_pics/';

		 	if( empty($_FILES))
		 	{
		 		$data = array('status'=>2,'msg'=>'Error: Failed to submit.');
		 	  echo json_encode($data);
		 	  exit ;
		 	}
		 	
		 	$key = '';
		 	foreach($_FILES as $k=>$v)
		 	{
		 		$key = $k;
		 		$no = intval( substr($k,12) );
		 		$picfile = $v;
		 	}
		 	
		  
		 	
		 	// 判断是 choosefile1 or choosefile2？
		 	$picname = $picfile['name'];
		 	$picsize = $picfile['size'];
		 	
	
		 	if ($picsize > 1024000) {
	 	 	 	 $data = array('status'=>0,'msg'=>'Error: File size should not exceed 1MB.');
	 		   echo json_encode($data);
	 		   exit ;
	 	 	}
	 	 	$filetype = pathinfo($picname, PATHINFO_EXTENSION);//strstr($picname, '.');
  	 	if ($filetype != "gif" && $filetype != "jpg" && $filetype != "png" ) {
  	 	 	 $data = array('status'=>0,'msg'=>'Error: File format should be .jpg, .gif or .png.');
  		   echo json_encode($data);
  		   exit ;
  	 	} 
		 	
		 	/* 	 	
		 	if(!empty($_COOKIE['allocacoc_slider_'.$no])  )
		 	{
		 			unlink($filedir.$_COOKIE['allocacoc_slider_'.$no]);
		 			error_log('del:'.$filedir.$_COOKIE['allocacoc_slider_'.$no]);
		 	}	 		
		 	*/ 	
		 	if(!empty($_COOKIE['allocacoc_slider'][$no])  )
		 	{
		 			unlink($_COOKIE['allocacoc_slider'][$no]);
		 			error_log('del:'.$_COOKIE['allocacoc_slider'][$no]);
		 	}	 
		 	 	
		 	$rand = rand(100, 999);
		 	$pics = 'design_'.date("YmdHis") . $rand . '.'.$filetype;
		 	//上传路径
		 	$pic_path = $filedir. $pics;
		 	$result = move_uploaded_file($picfile['tmp_name'], $pic_path);

 	 	  if(!$result)
 	 	  {
 	 	 	  $data = array('status'=>0,'msg'=>'Error: Failed to upload file.');
 	 	 	  echo json_encode($data);
 	 	 	  exit ;
 	    }

		 	/*
		 	$_COOKIE['allocacoc_slider_'.$no] = $pics;
		 	$_COOKIE['allocacoc_slider_picname'.$no] = $picname;
		 	*/
		 	
		 	/*
		 	cookie('slider_'.$no,$pics);
		 	cookie('slider_picname'.$no,$picname);
		 	*/
		 	cookie('slider['.$no.']',substr($pic_path,strlen(ROOT)-1));
		 	cookie('slider_picname['.$no.']',$picname);
		 	
		 	//error_log('$no:'.$no);
		 	//error_log(json_encode($_COOKIE['allocacoc_slider']));
		 			
		 	  
		 	$data = array('status'=>1,'name'=>$picname,'pic'=>substr($pic_path,strlen(ROOT)-1));
		 	echo json_encode($data);
		 	exit ; 	
		 	
	  }// end if
	  
	  
	 }
	 
	 
	 
	 public function submit_designidea_feature(){
	 	 error_log(json_encode($_REQUEST));
	 	 if (IS_POST) {	
		 	 	
		 	$filedir = $this->getUploadDir();//'D:/wamp/www/design_pics/';
		 	if( empty($_FILES))
		 	{
		 		$data = array('status'=>2,'msg'=>'Error: Failed to submit.');
		 	  echo json_encode($data);
		 	  exit ;
		 	}
		 	
		 	$key = '';
		 	foreach($_FILES as $k=>$v)
		 	{
		 		$key = $k;
		 		$no = intval( substr($k,13) );
		 		$picfile = $v;
		 	} 
		 	
		 	$picname = $picfile['name'];
		 	$picsize = $picfile['size'];
		 	
	
		 	if ($picsize > 1024000) {
	 	 	 	 $data = array('status'=>0,'msg'=>'Error: File size should not exceed 1MB.');
	 		   echo json_encode($data);
	 		   exit ;
	 	 	}
	 	 	$filetype = pathinfo($picname, PATHINFO_EXTENSION);//strstr($picname, '.');
  	 	if ($filetype != "gif" && $filetype != "jpg" && $filetype != "png" ) {
  	 	 	 $data = array('status'=>0,'msg'=>'Error: File format should be .jpg, .gif or .png.');
  		   echo json_encode($data);
  		   exit ;
  	 	} 
		 	 	 	
		 	if(!empty($_COOKIE['allocacoc_feature_pic'][$no])  )
		 	{
		 			unlink($_COOKIE['allocacoc_feature_pic'][$no]);
		 			error_log('del:'.$_COOKIE['allocacoc_feature_pic'][$no]);
		 	}	 		
		 	 	 
		 	 	
		 	$rand = rand(100, 999);
		 	$pics = 'design_'.date("YmdHis") . $rand . '.'.$filetype;
		 	//上传路径
		 	$pic_path = $filedir. $pics;
		 	$result = move_uploaded_file($picfile['tmp_name'], $pic_path);

 	 	  if(!$result)
 	 	  {
 	 	 	  $data = array('status'=>0,'msg'=>'Error: Failed to upload file.');
 	 	 	  echo json_encode($data);
 	 	 	  exit ;
 	    }
		 	
		 	
		 	
		 //$_COOKIE['allocacoc_feature_pic_'.$no] = $pics;
		 	
		 	//$_COOKIE['allocacoc_feature_text_'.$no] = $_REQUEST['feature_text_'.$no];
		 	
		 	cookie('feature_pic['.$no.']',substr($pic_path,strlen(ROOT)-1));
		 	
		 	  
		 	$data = array('status'=>1,'name'=>$picname,'pic'=>substr($pic_path,strlen(ROOT)-1));
		 	echo json_encode($data);
		 	exit ;
		 	 
		  
		 	
		 	
	  }// end if
	 }

   
   
   public function bk_submit_designidea()
   {
   	 if (IS_POST){  	 	
   	 	 $project_title = $_POST['project_title'];
   	 	 $firstname = $_POST['firstname'];
   	 	 $lastname = $_POST['lastname'];
   	 	 $email = $_POST['email'];
   	 	 $countryid = $_POST['countryid'];
   	 	 $project_desc = $_POST['project_desc'];
   	 	 
   	 	 import("@.ORG.PHPMailer");
  		 $mail = new PHPMailer(); 
  	
       $mail->IsSMTP(); // send via SMTP 
       $mail->Host = "mail.allocacoc.com"; // SMTP servers 
       $mail->SMTPAuth = true; // turn on SMTP authentication 
       $mail->Username = "designhouse.submissions@allocacoc.com"; // SMTP username 注意：普通邮件认证不需要加 @域名 
       $mail->Password = "WCW-xf2-4du-7nB"; // SMTP password  
       $mail->From = "designhouse.submissions@allocacoc.com"; // 发件人邮箱       
     
        
       $mail->FromName = "allocacoc"; // 发件人 ,比如 中国资金管理网      
       $mail->CharSet = "GB2312"; // 这里指定字符集！ 
       $mail->Encoding = "base64";      
       //$mail->AddAddress('designhouse@allocacoc.com',''); // 收件人邮箱和姓名 
       $mail->AddAddress('jing.wang@allocacoc.com.cn','');        
       $mail->AddReplyTo("","allocacoc");       
        
       $mail->IsHTML(true); // send as HTML 
       $mail->Subject = "project_title:".$project_title; 
       $mail->WordWrap = 80; // 设置每行字符串的长度 
       
       $mail->Body = '<p>FirstName:'.$firstname.','.$lastname.'</p>'
       .'<p>email:'. $email.'</p>'
       .'<p>countryid:'. $countryid.'</p>'
       .'<p>project_desc:<br>'. $project_desc.'</p>';
       
       $filedir = 'D:/wamp/www/design_pics/';
    
       $mail->AddAttachment( $filedir.$_COOKIE['allocacoc_concept'],$_COOKIE['allocacoc_concept']); 
       $mail->AddAttachment( $filedir.$_COOKIE['allocacoc_technical'],$_COOKIE['allocacoc_technical']); 
       $mail->AddAttachment( $filedir.$_COOKIE['allocacoc_feature'],$_COOKIE['allocacoc_feature']); 
       $mail->AddAttachment( $filedir.$_COOKIE['allocacoc_thumbnail'],$_COOKIE['allocacoc_thumbnail']); 
        
       $mail->AltBody ="To view the message, please use an HTML compatible email viewer!"; 
       if(!$mail->Send()) 
       { 
        error_log('send error:'.$mail->ErrorInfo);   
        $data = array('status'=>0,'msg'=>'Error: File format should be .jpg, .gif or .png.');
  		  echo json_encode($data);
  		  error_log('data:'.json_encode($data));
  		  exit ;  	
       }
       
       //clear cookie
       
       cookie('concept',null);cookie('technical',null);cookie('feature',null);cookie('thumbnail',null);
       cookie('concept_picname',null);cookie('technical_picname',null);cookie('feature_picname',null);cookie('thumbnail_picname',null);
       $data = array('status'=>1);
  		  echo json_encode($data);
  		  error_log('data:'.json_encode($data));
  		  exit ;
   	 }
   	 
   	 

					
   	 //send mail to : designhouse@allocacoc.com
   	 //from :designhouse.submissions@allocacoc.com , password: WCW-xf2-4du-7nB
   }
   
   
   
   public function submit_designidea()
   {
   	 if (IS_POST){  	 	
   	 	 $project_title = $_POST['project_title'];
   	 	 $firstname = $_POST['firstname'];
   	 	 $lastname = $_POST['lastname'];
   	 	 $email = $_POST['email'];
   	 	 $countryid = $_POST['countryid'];
   	 	 //$project_desc = $_POST['project_desc'];
   	 	 $video_url = $_POST['video_url'];
   	 	 
   	 	 
   	 	 $slider = $_COOKIE['allocacoc_slider'];
   	 	 ksort($slider);
   	 	 $slider_value = '';
   	 	 foreach($slider as $k=>$v)
   	 	 {
   	 	 	 $slider_value .= $v.'|';
   	 	 }
   	
   	 	 //product_image ,in cookie
			 //slider,in cookie
			 
			 $data = array('title' => $project_title, 
			 'lang' => LANG_ID,
			 'status'=>0,
			  'country' => $countryid, //get country name
			  'first_name' => $firstname, 
			  'last_name' => $lastname, 
			  'email' => trim($email),
			  'videourl'=>$video_url,
			  //'project_desc'=>$project_desc,
			  'slider'=>$slider_value,
			  'product_pic'=>  $_COOKIE['allocacoc_product_pic'],
			  'createtime'=>time());
			  
			 $id = M('submit_idea') -> add($data);	
			 if (!$id){error_log('line 1799');
				$rs = array('stauts' => 0, 'msg' => 'Error: Failed to submit!');
				echo json_encode($rs);
				exit ;
		   }
		   
	
			 //write record 
			 //feature, pic in cookie, text, need processing
			 $feature_text_list = explode(",",$_POST['feature_text_list']);
			 $feature_pic_list = $_COOKIE['allocacoc_feature_pic'];
			 ksort($feature_pic_list);
			 
			 error_log('feature_text_list:'.json_encode($feature_text_list));
			 error_log('feature_pic_list:'.json_encode($feature_pic_list));
			 foreach($feature_pic_list as $k=>$v)
			 {
			 	 $data = array( 
  			 'lang' => LANG_ID,
  			 'idea_id'=>$id,
  			 'pic'=>$v,
  			 'text'=>$feature_text_list[$k-1]);
  			  $featureid = M('submit_idea_feature') -> add($data);
  			  if (!$featureid){error_log('line 1822');
  				$rs = array('stauts' => 0, 'msg' => 'Error: Failed to submit!');
  				echo json_encode($rs);
  				exit ;
  		   }
			 	
			 }
			 
			
			$data = array('status'=>1);
  		echo json_encode($data);
  		exit ;		
   	 	 
   	 	}
   }
   
   
   
   public function submit_comment()
   {    
   	 if (IS_POST){
   	 	  $nickname = $_POST['nick_name'];
   	 	  $content = $_POST['content'];
   	 	  $design_id = $_POST['design_id'];
   	 	  $verify_code = $_POST['verify_code'];
   	 	  
   	 	  error_log(md5(trim($verify_code)));
   	 	  if(empty($verify_code)){
           $rs = array('status' => 3);
				   echo json_encode($rs);
				   exit ;
        }elseif($_SESSION['comment_verify']  &&  md5(trim($verify_code)) != $_SESSION['comment_verify']){//$_SESSION['comment_verify']  && 
           $rs = array('status' => 2);
           echo json_encode($rs);
           exit ;
        }
   	 	  

   	 	  $data = array('nick_name' => $nickname, 
			  'content' => $content, 			  
			  'design_winner_id'=>$design_id,
			  'createtime'=>time());
			  
			 $id = M('design_comment') -> add($data);	
			 if (!$id){
				$rs = array('status' => 0, 'msg' => 'Error: Failed to submit!');
				echo json_encode($rs);
				exit ;
		   }
		   
		   $rs = array('status' => 1, );
			 echo json_encode($rs);
			 exit ;
   	 }
   }
   
   
   
   public function comment_list()
	 {
		 if($_REQUEST['design_id']){
			 $design_id = intval($_REQUEST['design_id']);
			 $map['design_winner_id'] = $design_id;
			 $map['status'] = 1;
			 
			 import("@.ORG.Page");
			 $count = M('design_comment') -> where($map) -> count();
			 $p = new Page($count, 8);
			 foreach ($map as $key => $val) {
			 	$p -> parameter .= "$key=" . urlencode($val) . "&";
			 	//赋值给Page
			 }
			 $page = $p -> show();
			 
			 $list = M('design_comment')->where($map)->limit($p->firstRow . ',' . $p->listRows)->order('topflag desc,id desc')->select();
			 /*
			 foreach ($list as $k => $v) {
  			$list[$k]['content'] = strip_tags($v['content']);  			
  			$title = $v['title'];
  			$title = str_replace('&amp;','& ',$title);
  			$list[$k]['title'] = $title;  
  		 }
  		 */
		 }	
		 //var_dump(json_encode($list));
		 $this -> assign('page', $page);
		 $this -> assign('comment_list', $list);
		 $this -> display();
				
	 }
	 
	 
	 
	 public function blog_list()
	 {
	 	 $page_listRows = 6;
		 if($_REQUEST['design_id']){
		 	
		 	 $curr_page = 1;
		 	 if(!empty($_REQUEST['page']))
		 	   $curr_page = intval($_REQUEST['page']);
		 	 //if(!empty($_REQUEST['turn']) && $_REQUEST['turn']=='prev')
			 $design_id = intval($_REQUEST['design_id']);
			 $map['design_winner_id'] = $design_id;
			 $map['status'] = 1;
			 
			 //import("@.ORG.Page");
			 $count = M('design_blog') -> where($map) -> count();
			 $page_count = ceil($count*1.0/$page_listRows);
			 
			 
			 $one_page = 0;
			 $last_page = 0;
			 $first_page = 0;
			 //total 1 page
			 if($page_count < 2)
			   $one_page = 1;
			 else{//first page last page,current page
			 	 if($curr_page == $page_count)
			 	 $last_page = 1;
			 	 if($curr_page == 1)
			 	 $first_page = 1;
			 }  
			 
			  $this -> assign('one_page', $one_page);
			  $this -> assign('last_page', $last_page);
			  $this -> assign('first_page', $first_page);
			 /*
			 $p = new Page($count, 6);
			 foreach ($map as $key => $val) {
			 	$p -> parameter .= "$key=" . urlencode($val) . "&";
			 	//赋值给Page
			 }
			 $page = $p -> show();
			 */
			 $list = M('design_blog')->where($map)->limit(($curr_page-1)*$page_listRows . ',' . $page_listRows)->order('listorder asc,id desc')->select();
			 
		 }	
		 		
		 //$this -> assign('page', $page);
		 $this -> assign('design_id', $design_id);
		 $this -> assign('curr_page', $curr_page);
		 $this -> assign('page_count', $page_count);
		 $this -> assign('blog_list', $list);
		 $this -> display();
				
	 }

   
  /*
  project_title: document.getElementById("project_title").value, 
					firstname: document.getElementById("firstname").value,
					lastname: document.getElementById("lastname").value,
					email: document.getElementById("email").value,
					countryid: document.getElementById("countrylist").value,
					project_desc: document.getElementById("project_desc").value,
					//product_image ,in cookie
					video_url:document.getElementById("video_url").value,
					//slider,in cookie
					//feature, pic in cookie, text, need processing
					feature_text_list:getFeatureText()
  */
  
  
  
  public function disclaimer() {

		$map['lang'] = 9;// LANG_ID;				
		$map['status'] = 1;

		$design_challenge = M('design_challenge') -> where($map) -> find();

		$banners = array(0 => $design_challenge['banner_one'], 1 => $design_challenge['banner_two'], 2 => $design_challenge['banner_three']);
		
	
		$this -> assign('banners', $banners);  
		$this -> assign('disclaimer', $design_challenge['disclaimer']);
		$this -> display();
	}
	
 
}
?>