<?php
/**
 * lionfish 商城系统
 *
 * ==========================================================================
 * @link      http://www.liofis.com/
 * @copyright Copyright (c) 2015 liofis.com. 
 * @license   http://www.liofis.com/license.html License
 * ==========================================================================
 *
 * @author    fish
 *
 */
namespace Home\Controller;
class PlatformController extends CommonController{
	
	public function material()
	{
		
		 //is_seller_login()
		
		
		
		$do = I('get.do');
		
		if('delete' == $do)
		{
			$material_id = I('post.material_id');
			
			M('core_attachment')->where( array('id' => $material_id) )->delete();
			echo '{"message":{"errno":"0","message":"\u5220\u9664\u7d20\u6750\u6210\u529f"},"redirect":"","type":"ajax"}';
			die();
		}
		
	}
	
		
	
}