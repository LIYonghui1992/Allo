<?php

/**

 * 

 * Design House

 * create at 20150909, by jing wang

 */

if(!defined("App")) exit("Access Denied");

class Design_challengeAction extends ContentAction {

	public function edit()



    {



		



		$id = $_REQUEST ['id'];		



		if(MODULE_NAME=='Page'){



					$Page=D('Page');



					$p = $Page->find($id);



					if(empty($p)){



					$data['id']=$id;



					$data['title'] = $this->categorys[$id]['catname'];



					$data['keywords'] = $this->categorys[$id]['keywords'];



					$Page->add($data);	



					}



		}



		$vo = $this->dao->getById ( $id );



		$vo['content'] = htmlspecialchars($vo['content']);



 		$form=new Form($vo);



		



 



		$this->assign ( 'vo', $vo );



		$this->assign ( 'form', $form );


//var_dump(THEME_PATH.MODULE_NAME);var_dump(APP_PATH.'Tpl/Admin/Default/'.MODULE_NAME);

		//$template =  file_exists(THEME_PATH.MODULE_NAME.'_edit.html') ? MODULE_NAME.':edit' : 'Content:edit';
		$template =  file_exists(APP_PATH.'Tpl/Admin/Default/'.MODULE_NAME.'_edit.html') ? MODULE_NAME.':edit' : 'Content:edit';



		$this->display ( $template);





		

	   

	}


}

?>