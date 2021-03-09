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
namespace Seller\Model;

class ArticleModel{
	
	
	public function update($data)
	{
		
		
		$ins_data = array();
		$ins_data['title'] = $data['title'];
		$ins_data['content'] = $data['content'];
		$ins_data['displayorder'] = $data['displayorder'];
		$ins_data['enabled'] = $data['enabled'];
		$ins_data['addtime'] = time();
		
		$id = $data['id'];
		if( !empty($id) && $id > 0 )
		{
			unset($ins_data['addtime']);
			
			M('lionfish_comshop_article')->where( array('id' => $id) )->save( $ins_data );
			$id = $data['id'];
			
		}else{
			
			$id = M('lionfish_comshop_article')->where( array('id' => $id) )->add( $ins_data );
			
			
		}
		
		
	}
	
	
}
?>