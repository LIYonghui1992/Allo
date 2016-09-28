<?php



/**



 * 



 * ContactAction.class.php (前台联系)



 *



 */



if(!defined("App")) exit("Access Denied");
class ContactAction extends BaseAction
{

    public function index()
    {
		
		//$map['lang']      = LANG_ID;

		$map['lang'] = 9;

		$map['status']    = 1;

		$contact  = M('Contact') -> where($map) -> order('listorder asc') ->  select();
		
		
		foreach($contact as $k=>$v){
		
			$img=explode( '|',$v['imgs'] );
			
			$contact[$k]['imgs']=$img[0];
		
		}

		$imgs     = explode(":::",$contact['imgs']);

		foreach($imgs as $k=>$v){

			$imgs[$k] = strstr( $v, '|', TRUE );

		}

		//国家id

		$country_id       = $_COOKIE['allocacoc_country_id'];

		//国家默认型号

		$country          = M('country') -> where('id='.$country_id) -> find();

		$product_model_id = $country['product_model_id'];

		$product_model    = M('product_model') -> where('id='.$product_model_id) -> find();

		$banners          = explode(':::',$product_model['about_us']);

		foreach($banners as $k=>$v){

				$banners[$k] = strstr( $v, '|', TRUE );

		}

		$this->assign('banners',$banners);

		$this->assign('contact',$contact);

		$this->assign('imgs',$imgs);

        $this->display();

    }

}

?>