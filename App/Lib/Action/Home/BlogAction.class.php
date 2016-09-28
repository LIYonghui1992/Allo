<?php

/**
 *
 * BlogAction.class.php (前台博客)
 *
 */

if (!defined("App"))
	exit("Access Denied");
class BlogAction extends BaseAction {
	 public function get_page() {
	     if (IS_GET) {
	     	$get_lang = trim($_GET['lang']);
	     	$category_new = ($_GET['category']);
	     	$get_type = trim($_GET['type']);
 			$page = intval($_GET['page']);  //获取请求的页数 
            $start = $page*12;
            $map3=array();
            
            $map3['category']=$category_new;
            $map3['status'] = 1;
            if (!empty($get_lang)){
               $map3['lang']="9";
            }
             if (!empty($get_type)){
               $map3['article_type']=$get_type;
            }
			$blog_info = M('blog') ->where($map3)->order('updatetime desc')->limit($start,12)-> select();

			  foreach($blog_info as $k=>$v){
			
			$blog_info[$k]['article_text'] = strip_tags($v['article_text']);

		}
			//var_dump($blog_info);
          foreach ($blog_info as $key => $value) {
          	   error_log(json_encode($value));
          } 
			// error_log(json_encode($blog_info));
			echo json_encode($blog_info);
			exit ;

		}

	 }


	 public function get_fairs() {
	     if (IS_GET) {
	     	$get_lang = trim($_GET['lang']);
	     	$category_new = ($_GET['category']);
	     	$get_type = trim($_GET['type']);
 			$page = intval($_GET['page']);  //获取请求的页数 
            $start = $page*8;
            $map4=array();
            
            $map4['category']=$category_new;
            $map4['status'] = 1;
            if (!empty($get_type)){
               $map4['article_type']=$get_type;
            }
            if (!empty($get_lang) && $get_lang !== '0'){
               $map4['lang']=$get_lang;
            }
		 //得到所有的时间
        $new_data3 =M('blog')->distinct(true)->where($map4)->order('start_time desc')->limit($start,8)->select();
        foreach($new_data3 as $k=>$v){
                  $new_data3[$k]["month"] = date("Ym",$v['start_time']);
         }
        // error_log(json_encode($new_data3));
		$start_time=array();
		foreach ($new_data3 as $key => $value) {
				$start_time[]=$value['month'];
		}
        $start_time=array_unique($start_time);
		rsort($start_time); 
		// error_log(json_encode($start_time)); 
        $data_desc = array();
		 foreach ($start_time as $k1 => $v1) {
		 	foreach ($new_data3 as $k2 => $v2) {
		 		if($v1==$v2['month']){
		 			$data_desc[$v1][]=$v2;
				}		
		 	}
		 }
		 $new_desc = array();
		 foreach ($data_desc as $key => $vo) {
		     $new_desc[] = array("month"=>$key , "conten"=>$vo);  

		 }
         
		  // error_log(json_encode($new_desc));
	      echo json_encode($new_desc);
          
		}
		
	 }

	

	public function index(){
		//准备条件
     	$get_category = trim($_GET['category']);
		$get_lang = trim($_GET['m_lang']);
		$get_type = trim($_GET['type']);
		$used_lang = 0; 
		$used_category = 0;
        $used_type = 0; 
		if(empty($get_type)){
     	   $map['lang'] = "9";  
        }else{
         $map['article_type']=$get_type; 
         $used_type =  $map['article_type'];
        }

        if(empty($get_lang)){
     	  $map['lang'] = "9"; 
        }else{
         $map['lang']=$get_lang; 
         $used_lang =  $map['lang'];
        }
 
        if(empty($get_category)){
     	    $map['category']="Blog";
        }else{
            $map['category']=$get_category; 
           
        }
         $used_category = $map['category'];
        
        //查询数据
        if($used_category=="Fairs"){
        	 $data =M('blog')->where($map)->order('start_time desc')->limit(0,8)->select();
        	 foreach($data as $k=>$v){
                  $data[$k]["month"] = date("Ym",$v['start_time']);
         }
        }else{
        	 $data =M('blog')->where($map)->order('updatetime desc')->limit(0,12)->select();
        }
       
        
     

        foreach($data as $k=>$v){
			
			$data[$k]['article_text'] = strip_tags($v['article_text']);

		}
        if($used_category=="Fairs"){
        	$start_time=array();
		    foreach ($data as $key => $value) {
				$start_time[]=(int)$value['month'];
						}
				$start_time=array_unique($start_time);
				rsort($start_time);
				// var_dump($start_time);
				$data_desc = array(); 
				 foreach ($start_time as $k1 => $v1) {
				 	foreach ($data as $k2 => $v2) {
				 		if($v1==$v2['month']){
				 			$data_desc[$v1][]=$v2;
				 }		
		 	  }
		   }
        } 

		//得到不同的目录
		// $values = M('blog') ->distinct(true)->field('category')-> where($map) -> select();

		// foreach ($values as $k => $v) {
		// 	$categorys[$k] = $v['category'];
		// }
		// sort($categorys);


		
		
		//国家id

		$country_id = $_COOKIE['allocacoc_country_id'];

		//国家默认型号

		$country = M('country') -> where('id=' . $country_id) -> find();

		$product_model_id = $country['product_model_id'];

		$product_model = M('product_model') -> where('id=' . $product_model_id) -> find();

		$banners = explode(':::', $product_model['blog']);

		foreach ($banners as $k => $v) {

			$banners[$k] = strstr($v, '|', TRUE);

		}

		//var_dump($get_lang);
		$this -> assign('banners', $banners);
        $this -> assign('blogs', $data);
		$this -> assign('get_lang', $used_lang);
		$this -> assign('get_type', $used_type);
		$this -> assign('get_category', $used_category);
		$this -> assign('data_desc', $data_desc);

		if ($get_category == "Fairs" ) {
			$this -> display("fairs");
		}else
		$this -> display();

		

	}
	public function detail(){

		if (IS_POST) {

			$id = intval($_POST['id']);

			$blog = M('blog') -> where('id=' . $id) -> find();

			$data = array('stauts' => 1, 'title' => $blog['title'], 'background_image' => $blog['background_image'], 'expanded_image' => $blog['expanded_image'], 'author' => $blog['author'], 'updatetime' => date("Y.m.d", $blog['updatetime']), 'article_text' =>$blog['article_text']);

			echo json_encode($data);

			exit ;

		}

	}
	
	
	public function bk_import_fairs(){
		$map['year'] = 'fairs';
		$map['lang'] = 9;
		
		$news = M('article')->where($map)-> order('id desc') -> select();
		
		foreach($news as $k=>$v){
			
			if(empty($v['start_end']) || empty($v['status'])) continue;
						
			$start_end = $v['start_end'];
			$dates = explode('-',$start_end);var_dump(json_encode($dates));
			if(count($dates)>2){
				var_dump("date format wrong, id:".$v['id'].",start_end:".$v['start_end']);
				continue;
			}			
			
			$temp = explode('.',$dates[0]);
			if(count($temp)!=3){
				var_dump("start time format wrong:".$dates[0]);
				continue;
			}
			
			$temp = explode('.',$dates[1]);
			if(count($temp)!=3){
				var_dump("end time format wrong:".$dates[1]);
				continue;
			}
				
			$start_time = strtotime(str_replace('.',"/",$dates[0]));
			$end_time = strtotime(str_replace('.',"/",$dates[1]));
			
			$blog = array('status'=>1,'userid'=>$v['userid'],'username'=>$v['username'],'url'=>$v['url'],
			              'createtime'=>$v['createtime'],'updatetime'=>$v['updatetime'],'lang'=>$v['lang'],
			              'category'=>'Fairs','title'=>$v['title'],'title_style'=>$v['title_style'],
			              'background_image'=>$v['thumb'],'article_text'=>$v['content'],'address'=>$v['address'],
			              'booth_no'=>$v['booth_no'],'start_time'=>$start_time,'end_time'=>$end_time);
			/*
			$blog = array('status'=>1,'start_time'=>$start_time,'end_time'=>$end_time);
			*/
			
			
			//var_dump(json_encode($blog));			
			$id = M('blog') -> add($blog);
			var_dump("add blog,id:$id");			
		}
		
	}
	
	public function bk_import_awards(){
		$map['year'] = array('NEQ','fairs');
		$map['lang'] = 9;
		$map['status'] = 1;
		
		$news = M('article')->where($map)-> order('id desc') -> select();
		/*
		foreach($news as $k=>$v){
			var_dump($v['id'].":".$v['year']."/n");
		}
		*/
		
		foreach($news as $k=>$v){
			
			if(empty($v['start_end']) || empty($v['status'])) continue;						
			
			$blog = array('status'=>1,'userid'=>$v['userid'],'username'=>$v['username'],'url'=>$v['url'],
			              'createtime'=>$v['createtime'],'updatetime'=>$v['updatetime'],'lang'=>$v['lang'],
			              'category'=>'Awards','title'=>$v['title'],'title_style'=>$v['title_style'],
			              'background_image'=>$v['thumb'],'article_text'=>$v['content'],'address'=>$v['address'],
			              'booth_no'=>$v['booth_no']);
			
			//var_dump(json_encode($blog));			
			$id = M('blog') -> add($blog);
			var_dump("add blog,id:$id");			
		}
		
		
	}

}
?>