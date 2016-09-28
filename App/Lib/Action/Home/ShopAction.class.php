<?php

/**

 *

 * ShopAction.class.php (前台产品)

 *

 */

if (!defined("App"))
    exit("Access Denied");

class ShopAction extends BaseAction {

    public function bk_index() {
        //国家id
        $country_id = $_COOKIE['allocacoc_country_id'];

        //国家默认型号
        $country = M('country') -> where('id=' . $country_id) -> find();

        if ($country['webshop'] == 2) {

            redirect($country['link']);

        }

        $map['lang'] = LANG_ID;

        $map['status'] = 1;

        $map['country_id'] = $country['id'];

        $webshop = M('webshop') -> where($map) -> order('listorder asc') -> select();

        $product_model_id = $country['product_model_id'];

        $product_model = M('product_model') -> where('id=' . $product_model_id) -> find();

        $banners = explode(':::', $product_model['shop']);

        foreach ($banners as $k => $v) {

            $banners[$k] = strstr($v, '|', TRUE);

        }

        $this -> assign('ctry', $country);

        $this -> assign('banners', $banners);

        $this -> assign('webshop', $webshop);

        $this -> display();

    }
    
     public function index() {
        //国家id
        $country_id = $_COOKIE['allocacoc_country_id'];

        //国家默认型号
        $country = M('country') -> where('id=' . $country_id) -> find();

       /* if ($country['webshop'] == 2) {

            redirect($country['link']);

        }*/

        $map['lang'] = LANG_ID;

        $map['status'] = 1;

        $map['country_id'] = $country['id'];

        $webshop = M('webshop') -> where($map) -> order('listorder asc') -> select();

        $product_model_id = $country['product_model_id'];

        $product_model = M('product_model') -> where('id=' . $product_model_id) -> find();

        $banners = explode(':::', $product_model['shop']);

        foreach ($banners as $k => $v) {

            $banners[$k] = strstr($v, '|', TRUE);

        }


        $this -> assign('ctry', $country);

        $this -> assign('banners', $banners);

        $this -> assign('webshop', $webshop);

        //查询所有国家
        $country_1 = M('country')->where('status=1 and webshop=2')->distinct('title')->field('title, link')->order('title asc') ->select();
        $this->assign('country_1',$country_1);
      // print_r($country_1);exit;
        $this -> display();

    }

}
?>