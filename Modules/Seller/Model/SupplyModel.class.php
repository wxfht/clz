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

class SupplyModel{
	
	
	public function modify_supply($data)
    {
        global $_W;
        global $_GPC;
        
      
        
        if($data['id'] > 0)
        {
            //update ims_ 
            $id = $data['id'];
            unset($data['id']);
			
			if( empty($data['login_password']) )
			{
				unset($data['login_password']);
			}else{
				$slat = mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9);
				
				$data['login_password'] = md5( $slat.$data['login_password'] );
				$data['login_slat'] = $slat;
			}
			
			M('lionfish_comshop_supply')->where( array('id' => $id ) )->save( $data );
        }else{
            //insert 
			$slat = mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9);
			
			$data['login_password'] = md5( $slat.$data['login_password'] );
			$data['login_slat'] = $slat;
			
			M('lionfish_comshop_supply')->add($data);
        }
        return true;
    }
    
	
	//----begin
	
	
	
	
	public function ins_supply_commiss_order($order_id,$order_goods_id, $add_money)
	{
		
		$add_money = 0;
		
		$order_goods_info = M('lionfish_comshop_order_goods')->field('goods_id,supply_id,total,shipping_fare,fullreduction_money,voucher_credit')
							->where( array('order_goods_id' => $order_goods_id ) )->find();
		
		
		$order_info = M('lionfish_comshop_order')->field('delivery')->where( array('order_id' => $order_id ) )->find();
		
		if( empty($order_goods_info) )
		{
			return true;
		}else {
			//head_id commiss_bili
			
			$supply_info = D('Home/Front')->get_supply_info($order_goods_info['supply_id']); 
			
			//...begin 
			$head_commiss_info_list = M('lionfish_community_head_commiss_order')->field('money,add_shipping_fare')
									  ->where( array('order_goods_id' => $order_goods_id ) )->select();
			
			$head_commiss_money = 0;
			
			if( !empty($head_commiss_info_list) )
			{
				foreach( $head_commiss_info_list  as $val)
				{
					$head_commiss_money += $val['money'] - $val['add_shipping_fare'];
				}
			}
			
			//order_id  
			$member_commiss_list = M('lionfish_comshop_member_commiss_order')->field('money')->where( array('order_goods_id' => $order_goods_id,'order_id' => $order_id) )->select();
			
			$member_commiss_money = 0;
			
			if( !empty($member_commiss_list) )
			{
				foreach( $member_commiss_list  as $val)
				{
					$member_commiss_money += $val['money'];
				}
			}
			
			
			/**
			商品 100  满减2  优惠券3  团长配送费 4 。 实付  100-2-3+4.。。那么  
			
			团长分佣：  10% *（100-2-3）+4（即配送费）  

			供应商得 （100-2-3）*90%  

			**/
			//独立供应商
			
            // $money = round( ( (100 - $supply_info['commiss_bili']) * ($order_goods_info['total']  -$order_goods_info['fullreduction_money']-$order_goods_info['voucher_credit']))/100 ,2 );
									
			// $total_money = round(  ($order_goods_info['total']  -$order_goods_info['fullreduction_money']-$order_goods_info['voucher_credit']) ,2 );
					
			// $money = $money - $head_commiss_money - $member_commiss_money;
			//  （100-比例）* 商品价格/100    htong
			$money = round( ( (100 - $supply_info['commiss_bili']) * $order_goods_info['total'])/100 ,2 );
									
			$total_money = round($order_goods_info['total'],2 );
    


			//end
			
			if($money <=0)
			{
				$money = 0;
			}
			//退款才能取消的
			$ins_data = array();
			$ins_data['supply_id'] = $order_goods_info['supply_id'];
			$ins_data['order_id'] = $order_id;
			$ins_data['order_goods_id'] = $order_goods_id;
			$ins_data['state'] = 0;
			$ins_data['total_money'] = $total_money;
			$ins_data['comunity_blili'] = $supply_info['commiss_bili'];
			
			$ins_data['member_commiss_money'] = $member_commiss_money;
			$ins_data['head_commiss_money'] = $head_commiss_money;
			$ins_data['money'] = $money;
			$ins_data['addtime'] = time();
			
			M('lionfish_supply_commiss_order')->add( $ins_data );
			
			return true;
		}
		
	}
	
	
	public function send_supply_commission($order_id)
	{
		
		$list = M()->query("select * from ".C('DB_PREFIX')."lionfish_supply_commiss_order where  order_id={$order_id} ");
		
		foreach($list as $commiss)
		{
			if( $commiss['state'] == 0)
			{
				//supply_id
				M('lionfish_supply_commiss_order')->where( array('id' => $commiss['id'] ) )->save( array('state' => 1) );
				
				$comiss_info = M('lionfish_supply_commiss')->where( array('supply_id' => $commiss['supply_id'] ) )->find();
				
				if( empty($comiss_info) )
				{
					$ins_data = array();
					$ins_data['supply_id'] = $commiss['supply_id'];
					$ins_data['money'] = 0;
					$ins_data['dongmoney'] = 0;
					$ins_data['getmoney'] = 0;
					
					M('lionfish_supply_commiss')->add($ins_data);
				}
				
				M('lionfish_supply_commiss')->where( array('supply_id' => $commiss['supply_id'] ) )->setInc('money', $commiss['money']);
				
			}
		}
		
		
		
	}
	//----end
	
}
?>