<?php

/**
 *  if processflag==0 ,then send email,
 *  if send successfully, then set processflag=1
 *   delete record and pic?
 * 
*/

if (!defined("App"))
    exit("Access Denied");

class ProcessSubmitIdeaAction extends BaseAction {

    public function index() {
    	
      ignore_user_abort(true); // 后台运行
      set_time_limit(0); 
      $this->start_process();
    }
    
    
    function start_process()
    {
    	error_log('ProcessSubmitIdeaAction>>>>>start');
    
       $where['processflag'] = array('neq',1);
       
       $idealist = M('submit_idea')->where($where)->order('id asc')->limit(0,100)->select();
       foreach($idealist as $idea)
       {
       	 $fwhere['idea_id'] = array('eq',$idea['id']);
       	 $featurelist = M('submit_idea_feature')->where($fwhere)->select();
       	 if( !empty($featurelist ))
       	   $idea['features'] =  $featurelist;
       	 if($this->sendmail( $idea))
       	 {
       	 	$newwhere[id]=$idea['id'];
       	 	 M('submit_idea')->where($newwhere)->setField('processflag',1);
       	 }
       }
       exit;
     
    }
    
    
    function sendmail($idea)
    {
    	import("@.ORG.PHPMailer");
			$mail = new PHPMailer(); 
			$mail->IsSMTP(); // send via SMTP 
      
      $mail->Host = "mail.allocacoc.com"; // SMTP servers 
      $mail->SMTPAuth = true; // turn on SMTP authentication 
      $mail->Username = "designhouse.submissions@allocacoc.com"; // SMTP username 注意：普通邮件认证不需要加 @域名 
      $mail->Password = "WCW-xf2-4du-7nB"; // SMTP password  
      $mail->From = "designhouse.submissions@allocacoc.com"; // 发件人邮箱  
      
      /*
      $mail->Host = "smtp.exmail.qq.com"; // SMTP servers 
      $mail->SMTPAuth = true; // turn on SMTP authentication 
      $mail->Username = "jing.wang@allocacoc.com.cn"; // SMTP username 注意：普通邮件认证不需要加 @域名 
      $mail->Password = "wj285420WJ"; // SMTP password  
      $mail->From = "jing.wang@allocacoc.com.cn"; // 发件人邮箱  
      */
      $mail->FromName = "allocacoc"; // 发件人 ,比如 中国资金管理网      
      $mail->CharSet = "UTF-8"; // GB2312这里指定字符集！ 
      $mail->Encoding = "base64";      
      $mail->AddAddress('designhouse@allocacoc.com',''); // 收件人邮箱和姓名 
     // $mail->AddAddress('jing.wang@allocacoc.com.cn','');  
      //$mail->AddAddress('wingjang@163.com','');      
      $mail->AddReplyTo("","allocacoc");       
        
      $mail->IsHTML(true); // send as HTML 
      $mail->Subject = "submit_idea:".$idea['title']; 
      $mail->WordWrap = 80; // 设置每行字符串的长度 
      
      $t = str_replace("<img src=\"/Uploads/","<img src=\"http://www.allocacoc.com/Uploads/",$idea['project_desc']);
      
      $body = '<p>FirstName:'.$idea['first_name'].','.$idea['last_name'].'</p>'
       .'<p>email:'. $idea['email'].'</p>'
       .'<p>country:'. $idea['country'].'</p>'
       .'<p>project_desc:<br></p>'.$t.''// $idea['project_desc'].''
       .'<p>videourl:<br>'. $idea['videourl'].'</p>';
       
       
       $filedir = ROOT; //error_log($filedir.$idea['product_pic']);
       $mail->AddAttachment($filedir.$idea['product_pic'],'product_pic'.strstr($idea['product_pic'], '.'));
       $sliders = explode('|',$idea['slider']);
       foreach($sliders as $k=>$slider){
         $mail->AddAttachment($filedir.$slider,'slider_pic_'.$k.strstr($slider, '.'));
         
       }
       
       foreach($idea['features'] as $k=>$v){
       	 $body .=  '<p>feature '.$k.':<br>'. $v['text'].'</p>';
       	 $mail->AddAttachment($filedir.$v['pic'],'feature_pic_'.$k.strstr($v['pic'], '.'));
       }
       
       
       $mail->Body = $body;
       
        
       $mail->AltBody ="To view the message, please use an HTML compatible email viewer!"; 
       if(!$mail->Send()) 
       { 
        error_log('send error:'.$mail->ErrorInfo);   
        return 0;       
  		  
       }
       
       $result = M('submit_idea')->where('id eq'.$idea['id'])->setField('processflag',1) ;
       if($result)
       {
       	//delete pic and record?
       }
       
       return 1;
    
    }
    
    function sendmail_($idea)
    {
    	import("@.ORG.PHPMailer");
			$mail = new PHPMailer(); 
			$mail->IsSMTP(); // send via SMTP 
      /*
      $mail->Host = "mail.allocacoc.com"; // SMTP servers 
      $mail->SMTPAuth = true; // turn on SMTP authentication 
      $mail->Username = "designhouse.submissions@allocacoc.com"; // SMTP username 注意：普通邮件认证不需要加 @域名 
      $mail->Password = "WCW-xf2-4du-7nB"; // SMTP password  
      $mail->From = "designhouse.submissions@allocacoc.com"; // 发件人邮箱  
      */
      
      $mail->Host = "smtp.exmail.qq.com"; // SMTP servers 
      $mail->SMTPAuth = true; // turn on SMTP authentication 
      $mail->Username = "jing.wang@allocacoc.com.cn"; // SMTP username 注意：普通邮件认证不需要加 @域名 
      $mail->Password = "wj285420WJ"; // SMTP password  
      $mail->From = "jing.wang@allocacoc.com.cn"; // 发件人邮箱  
      
      $mail->FromName = "allocacoc"; // 发件人 ,比如 中国资金管理网      
      $mail->CharSet = "UTF-8"; // GB2312这里指定字符集！ 
      $mail->Encoding = "base64";      
      //$mail->AddAddress('designhouse@allocacoc.com',''); // 收件人邮箱和姓名 
     // $mail->AddAddress('jing.wang@allocacoc.com.cn','');  
      $mail->AddAddress('wingjang@163.com','');      
      $mail->AddReplyTo("","allocacoc");       
        
      $mail->IsHTML(true); // send as HTML 
      $mail->Subject = "submit_idea:".$idea['title']; 
      $mail->WordWrap = 80; // 设置每行字符串的长度 
      
      $t = str_replace("<img src=\"/Uploads/","<img src=\"http://www.allocacoc.com/Uploads/",$idea['project_desc']);
      
      $body = '<p>FirstName:'.$idea['first_name'].','.$idea['last_name'].'</p>'
       .'<p>email:'. $idea['email'].'</p>'
       .'<p>country:'. $idea['country'].'</p>'
       .'<p>project_desc:<br></p>'.$t.''// $idea['project_desc'].''
       .'<p>videourl:<br>'. $idea['videourl'].'</p>';
       
       
       $filedir = ROOT; //error_log($filedir.$idea['product_pic']);
       $mail->AddAttachment($filedir.$idea['product_pic'],'product_pic'.strstr($idea['product_pic'], '.'));
       $sliders = explode('|',$idea['slider']);
       foreach($sliders as $k=>$slider){
         $mail->AddAttachment($filedir.$slider,'slider_pic_'.$k.strstr($slider, '.'));
         
       }
       
       foreach($idea['features'] as $k=>$v){
       	 $body .=  '<p>feature '.$k.':<br>'. $v['text'].'</p>';
       	 $mail->AddAttachment($filedir.$v['pic'],'feature_pic_'.$k.strstr($v['pic'], '.'));
       }
       
       
       $mail->Body = $body;
       
        
       $mail->AltBody ="To view the message, please use an HTML compatible email viewer!"; 
       if(!$mail->Send()) 
       { 
        error_log('send error:'.$mail->ErrorInfo);   
        return 0;       
  		  
       }
       
       $result = M('submit_idea')->where('id eq'.$idea['id'])->setField('processflag',1) ;
       if($result)
       {
       	//delete pic and record?
       }
       
       return 1;
    
    }
    
    

}
?>