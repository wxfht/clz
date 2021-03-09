<?php
namespace Home\Model;
use Think\Model;
/**
 * 拼团模型模型 
 * @author fish
 *
 */
class VipcardModel {
	
    public function update($data)
	{
		
		$ins_data = array();
		
		$ins_data['cardname'] = $data['cardname'];
		$ins_data['orignprice'] = $data['orignprice'];
		$ins_data['price'] = $data['price'];
		$ins_data['expire_day'] = $data['expire_day'];
		$ins_data['addtime'] = time();
		
		$id = $data['id'];
		if( !empty($id) && $id > 0 )
		{
			unset($ins_data['addtime']);
			M('lionfish_comshop_member_card')->where( array('id' => $id) )->save( $ins_data );
			
			$id = $data['id'];
		}else{
			$id = M('lionfish_comshop_member_card')->add( $ins_data );
		}
	}
	
	public function updateequity($data)
	{
		
		$ins_data = array();
		
		$ins_data['equity_name'] = $data['equity_name'];
		$ins_data['image'] = $data['image'];
		$ins_data['addtime'] = time();
		
		$id = $data['id'];
		if( !empty($id) && $id > 0 )
		{
			unset($ins_data['addtime']);
			
			M('lionfish_comshop_member_card_equity')->where( array('id' => $id) )->save( $ins_data );
			$id = $data['id'];
		}else{
			$id = M('lionfish_comshop_member_card_equity')->add( $ins_data );
		}
	}
	
	
}