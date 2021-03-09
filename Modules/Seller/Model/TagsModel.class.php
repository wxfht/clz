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

class TagsModel{
	
	
	public function update($data,$tag_type='normal')
	{
		
		$ins_data = array();
		$ins_data['tagname'] = $data['tagname'];
		$ins_data['type'] = $data['type'];
		$ins_data['tag_type'] = $tag_type;
		
		if($data['type']==0){
			$ins_data['tagcontent'] = $data['tagcontent'];
		} else {
			$ins_data['tagcontent'] = save_media($data['tagimg']);
		}
		$ins_data['state'] = $data['state'];
		$ins_data['sort_order'] = $data['sort_order'];
		
		$id = $data['id'];
		if( !empty($id) && $id > 0 )
		{
			M('lionfish_comshop_goods_tags')->where( array('id' => $id) )->save( $ins_data );
			
		}else{
			M('lionfish_comshop_goods_tags')->add($ins_data);
		}
		
		
		
	}
	
	
}
?>