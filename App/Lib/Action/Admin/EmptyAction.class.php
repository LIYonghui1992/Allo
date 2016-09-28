<?php

/**

 * 

 * Empty (空模块)

 *

 */

if(!defined("App")) exit("Access Denied");

class EmptyAction extends Action

{	

	public function _empty()

	{
 
		R('Admin/Content/'.ACTION_NAME);

	}

}

?>