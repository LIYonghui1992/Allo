<?php

/**

 * 

 * Urlrule(URL规则)

 *

 */

if(!defined("App")) exit("Access Denied");

class UrlruleAction extends AdminbaseAction {



	protected $dao;

    function _initialize()

    {	

		parent::_initialize();

		$this->dao = D('Admin/urlrule');

    }

}

?>