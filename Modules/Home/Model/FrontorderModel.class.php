<?php

namespace Home\Model;

use Think\Model;

/**

 * 圈子模型

 * @author fish

 *

 */

class FrontorderModel {

	

	public $table = 'pin';
	
	
	/**
		开始结算
	**/
	function settlement_order($order_id)
	{
	
		$order_info = M('lionfish_comshop_order')->where( array('order_id' => $order_id ) )->find();
		
		//分配金额，
		if( $order_info['head_id'] > 0 )
		{
			D('Seller/Community')->send_head_commission($order_id, $order_info['head_id']);
		}
		
		D('Home/Commission')->send_order_commiss_money($order_id);
		
		D('Seller/Supply')->send_supply_commission($order_id, $uniacid);
		
		
		if( $order_info['head_id'] > 0)
		{
			$community_info = D('Seller/Community')->get_community_info_by_head_id($order_info['head_id']);
		
			D('Seller/Community')->upgrade_head_level($order_info['head_id']);
		}
		
		
		
		
		
		
		$open_buy_send_score = D('Home/Front')->get_config_by_name('open_buy_send_score');
		if( empty($open_buy_send_score) )
		{
			$open_buy_send_score = 0;
		}
			
		$money_for_score = D('Home/Front')->get_config_by_name('money_for_score');

		$member_model = D('Admin/Member');
		
		
		$goods_list = M('lionfish_comshop_order_goods')->where( array('order_id' => $order_id) )->select();
		
		 $goods_name = "";
		 $quantity = 0;
		 foreach($goods_list as $kk => $vv) 
	     {
			if($vv['is_statements_state'] == 1)
			{
				continue;
			}
			
			D('Home/Pin')->send_pinorder_commiss_money($order_id, $vv['order_goods_id']);
			
			
			$up_order_data = array();
			$up_order_data['is_statements_state'] = 1;
					
			M('lionfish_comshop_order_goods')->where( array('order_id' => $order_id, 'order_goods_id' => $vv['order_goods_id'] ) )->save( $up_order_data );
			 
			$quantity += $vv['quantity'];
			if( $open_buy_send_score == 1 && $order_info['type'] != 'integral')
			{
				$pay_money = $vv['total'] + $vv['shipping_fare'] - $vv['voucher_credit'] - $vv['fullreduction_money'];
				
				//检测商品是否有单独设置积分赠送		
				$gd_info = M('lionfish_comshop_good_common')->field('is_modify_sendscore,send_socre')->where( array('goods_id' =>$vv['goods_id'] ) )->find();
					
				if( $gd_info['is_modify_sendscore'] == 1 && $gd_info['send_socre'] > 0 )
				{
					//quantity
					$send_score = $gd_info['send_socre'] * $vv['quantity'];
					
					$send_score = intval($send_score);
					if( $send_score > 0 )
					{  
						D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$send_score, 0 ,'订单付款赠送积分', 'goodsbuy', $order_info['order_id'] ,$vv['order_goods_id']);
					}
				}else{
					if( !empty($money_for_score) )
					{
						$send_score = $pay_money * $money_for_score;
						$send_score = intval($send_score);
						if( $send_score > 0 )
						{
							D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$send_score, 0 ,'订单付款赠送积分', 'goodsbuy', $order_info['order_id'] ,$vv['order_goods_id']);
						}
					}
				}
				
			}
		
		 }
		 
		$order_history = array();
		$order_history['order_id'] = $order_id;
		$order_history['order_status_id'] = 18;
		$order_history['notify'] = 0;
		$order_history['comment'] = '收货后，订单结算';
		$order_history['date_added']=time();
		
		M('lionfish_comshop_order_history')->add($order_history);
		
		$member_model->check_updategrade( $order_info['member_id'] );
	}
	
	
	
	/**
	 * 确认收货
	 * @param unknown $order_id
	 */
	function receive_order($order_id,  $is_auto = false)
	{
		$gpc = I('request.');
		
		
		$open_aftersale = D('Home/Front')->get_config_by_name('open_aftersale');
		$open_aftersale_time = D('Home/Front')->get_config_by_name('open_aftersale_time');
		
		$statements_end_time = time();
		
		if( !empty($open_aftersale) && !empty($open_aftersale_time) && $open_aftersale_time > 0  )
		{
			$statements_end_time = $statements_end_time + 86400 * $open_aftersale_time;
		}
		
		
		
		
		
		$up_order_data = array();
		$up_order_data['order_status_id'] = 6;
		$up_order_data['receive_time'] = time();
		
		M('lionfish_comshop_order')->where( array('order_id' => $order_id) )->save( $up_order_data );
		
		
		
		
		$order_history = array();
		$order_history['order_id'] = $order_id;
		$order_history['order_status_id'] = 6;
		$order_history['notify'] = 0;
		$order_history['comment'] = $is_auto ? '系统自动收货，等待结算佣金':'用户确认收货，等待结算佣金';
		$order_history['date_added']=time();
		
		
		M('lionfish_comshop_order_history')->add($order_history);
		
		
		//发送信息 订单号， 商品名称 门店 核销时间 温馨提示
		//weprogram_template_hexiao_success
		
		//物品名称 订单号 购买日期  配送方式 温馨提醒
		
		$order_info = M('lionfish_comshop_order')->where( array('order_id' => $order_id) )->find();
		
		if( !empty($open_aftersale) && $open_aftersale == 1 )
		{
			//TODO。。
			
		}else{
			$this->settlement_order($order_id);
		}
		
		 $goods_list = M('lionfish_comshop_order_goods')->where( array('order_id' => $order_id) )->select();
		
		
		 $goods_name = "";
		 $quantity = 0;
		 foreach($goods_list as $kk => $vv) 
	     {
			 $up_order_data = array();
			 $up_order_data['statements_end_time'] = $statements_end_time;
			
			 M('lionfish_comshop_order_goods')->where( array('order_goods_id' => $vv['order_goods_id'],'order_id' => $order_id) )->save( $up_order_data );
			 
			 $quantity += $vv['quantity'];
			
			 $order_option_list = M('lionfish_comshop_order_option')->where( array('order_goods_id' => $vv['order_goods_id']) )->select();
				
						
			$option_str_ml = '';
			
		    foreach($order_option_list as $option)
			{
				$vv['option_str'][] = $option['value'];
			}
			if( !isset($vv['option_str']) )
			{
				$option_str_ml = '';
			}else{
				$option_str_ml = implode(',', $vv['option_str']);
			}  
			$goods_name .=  $vv['name'].' '. $option_str_ml ."\r\n";
		 }
		 
		 $member_info = M('lionfish_comshop_member')->where( array('member_id' => $order_info['member_id'] ) )->find();				
		
		$template_data = array();
		$template_data['keyword1'] = array('value' => $order_info['order_num_alias'], 'color' => '#030303');
		$template_data['keyword2'] = array('value' => $goods_name, 'color' => '#030303');
		$template_data['keyword3'] = array('value' => $community_info['community_name'], 'color' => '#030303');
		$template_data['keyword4'] = array('value' => date('Y-m-d H:i:s',time() ), 'color' => '#030303');
		$template_data['keyword5'] = array('value' => '请记得随身带走贵重物品哦', 'color' => '#030303');
		
		
		$template_id = D('Home/Front')->get_config_by_name('weprogram_template_hexiao_success');
		$url = D('Home/Front')->get_config_by_name('shop_domain');
		
		
		$pagepath = 'lionfish_comshop/pages/user/me';
		
		
		$weprogram_use_templatetype = D('Home/Front')->get_config_by_name('weprogram_use_templatetype');
		
		if( !empty($weprogram_use_templatetype) && $weprogram_use_templatetype == 1 )
		{
			$mb_subscribe = M('lionfish_comshop_subscribe')->where( array('member_id' => $order_info['member_id'], 'type' => 'hexiao_success' ) )->find();
			
			//...todo
			if( !empty($mb_subscribe) )
			{
				$template_id = D('Home/Front')->get_config_by_name('weprogram_subtemplate_hexiao_success');
			
				//判断商品名称是否超过 20字符 超过直接截取
				if(mb_strlen($goods_name,'utf-8') > 20){
					$goods_name = mb_substr($goods_name, 0, 16, 'utf-8').'...';
				}
				$template_data = array();
				$template_data['character_string5'] = array('value' => $order_info['order_num_alias'] );
				$template_data['thing6'] = array('value' => $goods_name );
				$template_data['date7'] = array('value' => date('Y-m-d H:i:s',time() ) );
				
				D('Seller/User')->send_subscript_msg( $template_data,$url,$pagepath,$member_info['we_openid'],$template_id );
				
				M('lionfish_comshop_subscribe')->where( array('id' => $mb_subscribe['id'] ) )->delete();
			}
			
		}else{
			$member_formid_info = M('lionfish_comshop_member_formid')->where("member_id=".$order_info['member_id']." and formid != '' and state =0 ")->order('id desc')->find();
			
			if(!empty( $member_formid_info ))
			{
				
				$wx_template_data = array(); 
				$weixin_appid = D('Home/Front')->get_config_by_name('weixin_appid');
				$weixin_template_hexiao_success = D('Home/Front')->get_config_by_name('weixin_template_hexiao_success');
				
				if( !empty($weixin_appid) && !empty($weixin_template_hexiao_success) )
				{
					$wx_template_data = array(
										'appid' => $weixin_appid,
										'template_id' => $weixin_template_hexiao_success,
										'pagepath' => $pagepath,
										'data' => array(
														'first' => array('value' => '您的订单'.$order_info['order_num_alias'].'核销成功，社区:'.$community_info['community_name'],'color' => '#030303'),
														'keyword1' => array('value' => $goods_name,'color' => '#030303'),
														'keyword2' => array('value' => $quantity,'color' => '#030303'),
														'keyword3' => array('value' => date('Y-m-d H:i:s',time() ),'color' => '#030303'),
														'remark' => array('value' => '请记得随身带走贵重物品哦','color' => '#030303'),
												)
									);
				}
				
				$res = D('Seller/User')->send_wxtemplate_msg($template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$member_formid_info['formid'],0,$wx_template_data);
				
				
				M('lionfish_comshop_member_formid')->where( array('id' => $member_formid_info['id'] ) )->save( array('state' => 1) );
			}
			
		}
		
		
	}
	
	function get_community_head_order_count($head_id, $where="")
	{
		
		$condition = "  head_id ={$head_id} and (type = 'orderbuy' ) ";
		
		if( !empty($where) )
		{
			$condition .= $where;
		}
		
		$count =  M('lionfish_community_head_commiss_order')->where($condition)->count();
		
		return $count;
	}
	
	
	function get_member_order_count($member_id,$where="")
	{
		
		$condition = " member_id ={$member_id} ";
		
		if( !empty($where) )
		{
			$condition .= $where;
		}
		
		$count = M('lionfish_comshop_order')->where($condition)->count();
		
		return $count;
	}
	
	function get_goods_buy_record($goods_id,$limit=9)
	{
		global $_W;
		
		$order_status_id_str = '1,4,6,11,12,13,14';
		$sql ="select og.order_id ,o.pay_time,og.quantity,o.member_id from ".
				C('DB_PREFIX')."lionfish_comshop_order_goods as og left join  ".C('DB_PREFIX')."lionfish_comshop_order as o on og.order_id = o.order_id  
				   
				where  o.order_status_id in (1,4,6,11,12,13,14)  and og.goods_id ={$goods_id} order by o.order_id desc limit {$limit}";
		
		
		$sql_count ="select count(og.order_id) as count from ".
				C('DB_PREFIX')."lionfish_comshop_order_goods as og left join  ".C('DB_PREFIX')."lionfish_comshop_order as o on og.order_id = o.order_id  
				   
				where  o.order_status_id in (1,4,6,11,12,13,14)  and og.goods_id ={$goods_id} order by o.order_id desc ";
		
		$total_arr = M()->query($sql_count);
		
		$total_count = $total_arr[0]['count'];
		
		
		$list = M()->query($sql);	
		
		if( !empty($list) )
		{
			foreach($list as &$value)
			{
					
				$mb_info = M('lionfish_comshop_member')->field('username,avatar')->where( array('member_id' =>  $value['member_id']) )->find();	
				
				$value['username'] = $mb_info['username'];
				$value['avatar'] = $mb_info['avatar'];
				$value['pay_time'] = date('Y-m-d H:i', $value['pay_time']);
				
			}
		}
		
		return array('list' => $list, 'count' => $total_count);
	}
	
	function addOrder($data) {
		$gpc = I('request.');
		
		//暂时屏蔽积分删除模块
		//$integral_model = D('Seller/Integral');
		
		
		$is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');
		$is_open_vipcard_buy = !empty($is_open_vipcard_buy) && $is_open_vipcard_buy ==1 ? 1:0; 

		$is_vip_card_member = 0;

		$member_id = $data['member_id'];
		$is_member_level_buy = 0;
		
		
		if( $member_id > 0 )
		{
			$member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id ) )->find();
			
			if( !empty($is_open_vipcard_buy) && $is_open_vipcard_buy == 1 )
			{
				
				$now_time = time();
				
				if( $member_info['card_id'] >0 && $member_info['card_end_time'] > $now_time )
				{
					$is_vip_card_member = 1;//还是会员
				}else if( $member_info['card_id'] >0 && $member_info['card_end_time'] < $now_time ){
					$is_vip_card_member = 2;//已过期
				}
			}
			
			if($is_vip_card_member != 1 && $member_info['level_id'] >0 )
			{
				$is_member_level_buy = 1;
			}
		}
		
	    $order = array();
		
	    $order['member_id']=$data['member_id'];
	    $order['order_num_alias']=$data['order_num_alias'];
	    $order['name']=$data['name'];
		
		if( isset($data['from_type']) )
		{
			$order['from_type']=$data['from_type'];
		}
	
		
		if($data['delivery'] == 'pickup')
		{
			$order['telephone']=$data['telephone'];
			$order['shipping_name']=$data['shipping_name'];
		}else{
			$order['telephone']=$data['telephone'];
			$order['shipping_name']=$data['shipping_name'];
		}
	    
		
	    $order['type']=$data['type'];
	     $order['score_for_money']=$data['score_for_money'];
		
	    $order['shipping_address']=$data['shipping_address'];
	    $order['shipping_city_id']=$data['shipping_city_id'];
		
		$order['ziti_name']=$data['ziti_name'];
		$order['ziti_mobile']=$data['ziti_mobile'];
		$order['tuan_send_address']=$data['tuan_send_address'];
	
		
		
		$order['shipping_stree_id']=$data['shipping_stree_id'];
	
	
	    $order['shipping_country_id']=$data['shipping_country_id'];
	    $order['shipping_province_id']=$data['shipping_province_id'];
	    $order['shipping_tel']=$data['shipping_tel'];
	    $order['order_status_id'] = 3;
		$order['voucher_id']=$data['voucher_id'];	
		$order['voucher_credit']=$data['voucher_credit'];
		$order['is_free_shipping_fare']=$data['is_free_shipping_fare'];			
		
	    $order['ip']=get_client_ip();
		
		
		if( $data['is_free_shipping_fare'] == 1 )
		{
			 $order['shipping_fare'] = 0;
			 $order['fare_shipping_free'] = $data['shipping_fare'];
			 
			 if($data['delivery'] == 'tuanz_send')
			{
				$man_free_tuanzshipping = D('Home/Front')->get_config_by_name('man_free_tuanzshipping');
				
				$order['man_e_money'] = empty($man_free_tuanzshipping) ? 0 : $man_free_tuanzshipping;
			}else if($data['delivery'] =='express'){
				$man_free_shipping = D('Home/Front')->get_config_by_name('man_free_shipping');
				
				$order['man_e_money'] = empty($man_free_shipping) ? 0 : $man_free_shipping;
			}
		
			
			 
		}else{
			 $order['shipping_fare'] = $data['shipping_fare'];
		}
	   
	
	   // $order['shipping_fare'] = $data['shipping_fare'];
	
	    $order['ip_region'] = '';
	    if($data['total'] <0)
	    {
	        $data['total'] = 0;
	    }
	    $order['date_added'] =time();
	    $order['total'] =$data['total'];
	    $order['old_price'] =$data['total'];
		
		
	    $order['user_agent']=$data['user_agent'];
	
	    $order['shipping_method']=0;//快递id
	    $order['delivery']=$data['delivery'];
	
	
	    $order['payment_code']=$data['payment_method'];
	
	    $order['address_id']=$data['address_id'];
	    $order['comment']=$data['comment'];
		$order['score_for_money']=$data['score_for_money'];
	
	    $order['store_id'] = $data['store_id'];
		$order['supply_id'] = $data['supply_id'];
		$order['head_id'] = $data['pick_up_id'];
		
		$order['fullreduction_money'] = $data['reduce_money'];
		
		$man_total_free = $data['man_total_free'];
		  
		$order_id = M('lionfish_comshop_order')->add($order);
		
		if( !$order_id )
		{
			die();
		}
		
	    //$goods_model = D('Home/Goods');
		
	    $member_info = M('lionfish_comshop_member')->where( array('member_id' => $data['member_id'] ) )->find();
	
	    $is_pin = 0;
	    $pin_id = 0;
		$is_vipcard_buy = 0;
		$is_soli_order = 0;
		
		//$share_model = D('Seller/Fissionsharing');
		//暂时屏蔽分享裂变分佣
		
	    //$kucun_method  = C('kucun_method');
	    //$kucun_method  = empty($kucun_method) ? 0 : intval($kucun_method);
		//暂时下单就减库存
		
		$kucun_method =  D('Home/Front')->get_config_by_name('kucun_method');
						
		if( empty($kucun_method) )
		{
			$kucun_method = 0;
		}
		
		
	    $free_tuan = 0;
	    if(isset($data['goodss'])){
			
			//对优惠券进行商品优惠金额上的拆分
			//goods_id
			$limit_money_total = 0;
			$bili_voucher_goodslist = array();
			
			if( $data['voucher_id'] > 0 )
			{
				$voucher_info = M('lionfish_comshop_coupon_list')->where( array('id' => $data['voucher_id'] ) )->find();
				
				if($voucher_info['is_limit_goods_buy'] == 0)
				{
					//不限制 option
					 foreach ($data['goodss'] as $voucher_goods) {
						 $bili_voucher_goodslist[$voucher_goods['goods_id'].'_'.$voucher_goods['option']] = array(
																					'goods_id' =>$voucher_goods['goods_id'],
																					'money' =>$voucher_goods['total']
																				);
						  $limit_money_total += $voucher_goods['total'];
					 }
					
				}else if($voucher_info['is_limit_goods_buy'] == 1)
				{
					//限制商品
					if( empty($voucher_info['limit_goods_list']) )
					{
						foreach ($data['goodss'] as $voucher_goods) {
							$bili_voucher_goodslist[$voucher_goods['goods_id'].'_'.$voucher_goods['option']] = array(
																				'goods_id' =>$voucher_goods['goods_id'],
																				'money' =>$voucher_goods['total']
																			);
							 $limit_money_total += $voucher_goods['total'];
						}
						
					}else{
						$voucher_goods_ids = explode(',', $voucher_info['limit_goods_list']);
						
						foreach ($data['goodss'] as $voucher_goods) {
							if( in_array($voucher_goods['goods_id'], $voucher_goods_ids ) )
							{
								$bili_voucher_goodslist[$voucher_goods['goods_id'].'_'.$voucher_goods['option']] = array(
																					'goods_id' =>$voucher_goods['goods_id'],
																					'money' =>$voucher_goods['total']
																				);
								$limit_money_total += $voucher_goods['total'];
							}
							
						}
						
					}
					
				}else if($voucher_info['is_limit_goods_buy'] == 2){
					//限制分类
					if( empty($voucher_info['goodscates']) )
					{
						foreach ($data['goodss'] as $voucher_goods) {
							$bili_voucher_goodslist[$voucher_goods['goods_id'].'_'.$voucher_goods['option']] = array(
																				'goods_id' =>$voucher_goods['goods_id'],
																				'money' =>$voucher_goods['total']
																			);
							 $limit_money_total += $voucher_goods['total'];
						}
					}else{
						$voucher_goods_cate = $voucher_info['goodscates'];
						
						$voucher_goods_ids_total_money = 0;
						
						foreach ($data['goodss'] as $voucher_goods) 
						{
							$cate_gd_arr = M('lionfish_comshop_goods_to_category')->field('cate_id')->where( array('goods_id' => $voucher_goods['goods_id']) )->select();
							
							if( !empty($cate_gd_arr) )
							{
								foreach($cate_gd_arr as $cate_val)
								{
									if( $cate_val['cate_id'] == $voucher_goods_cate )
									{
										$bili_voucher_goodslist[$voucher_goods['goods_id'].'_'.$voucher_goods['option']] = array(
																				'goods_id' =>$voucher_goods['goods_id'],
																				'money' =>$voucher_goods['total']
																			);
										 $limit_money_total += $voucher_goods['total'];
									}
								}
							}
							
						}
						
					}
				}
				
				
			}
			
			$score_forbuy_money = D('Home/Front')->get_config_by_name('score_forbuy_money');
			
			if( empty($score_forbuy_money) )
			{
				$score_forbuy_money = 0;
			}
			
	        foreach ($data['goodss'] as $goods) {
	           
				$goods_id = $goods['goods_id'];
				
	            $pin_id = $goods['pin_id'];
	
	            $commiss_one_money = 0;
				
				//is_soli_order
				if( isset($goods['soli_id']) && $goods['soli_id'] > 0 )
				{
					if( $is_soli_order >= 0  )
					{
						$is_soli_order = $goods['soli_id'];
					}
				}else{
					$is_soli_order = -1;
				}
	            
				
				//$goods_info = load_model_class('pingoods')->get_goods_mixinfo($goods_id);
				
				
				//暂时屏蔽一下代码，三级分销部分 积分商城部分
				/**
				$goods_info = M('goods')->field('points,commiss_fen_one_disc,commiss_fen_two_disc,commiss_fen_three_disc,commiss_three_dan_disc,commiss_two_dan_disc,commiss_one_dan_disc,store_id,type,model,image')->where( array('goods_id' => $goods_id) )->find();
				
				if( !empty($goods_info['points']) && $goods_info['points'] > 0 && $goods_info['type'] != 'integral')
				{
					$score = $goods_info['points'] * $goods['quantity'];
					$integral_model->charge_member_score( $data['member_id'] , $score,'in', 'goodsbuy', $order_id);
					
				}else if( C('buy_send_score') > 0 && $goods_info['type'] != 'integral')
				{
					$score = C('buy_send_score') * $goods['quantity'];
					$integral_model->charge_member_score( $data['member_id'] , $score,'in', 'goodsbuy', $order_id);
				}
	            **/
				
	            $is_pin = $goods['is_pin'];
				
	            //判断是否拼团开始
				$commiss_one_money = 0;
				$commiss_two_money = 0;
				$commiss_three_money = 0;
				
				$commiss_fen_one_money = 0;
				$commiss_fen_two_money = 0;
				$commiss_fen_three_money = 0;
				
				
				$commission_info = D('Home/Pingoods')->get_goods_commission_info($goods_id,$data['member_id']);
					
				$commiss_level = D('Home/Front')->get_config_by_name('commiss_level');
				
				
				if($commiss_level > 0)
				{
					if($commiss_level >= 1)
					{
						if( $commission_info['commiss_one']['type'] == 2 )
						{
							$commiss_one_money = $commission_info['commiss_one']['money'] * $goods['quantity'];
						}
						else if( $is_member_level_buy ==1 && $goods['is_mb_level_buy'] == 1 )
						{
							$commiss_one_money = round( ($commission_info['commiss_one']['fen'] * $goods['level_total'] )/100 , 2);
						}
						else
						{
							if( $is_vip_card_member == 1 && $goods['is_take_vipcard'] == 1 )
							{
								$commiss_one_money = round( ($commission_info['commiss_one']['fen'] * $goods['card_total'] )/100 , 2);
							}else{
								$commiss_one_money = round( ($commission_info['commiss_one']['fen'] * $goods['total'] )/100 , 2);
							}
						}
					}
					if($commiss_level >= 2)
					{
						if( $commission_info['commiss_two']['type'] == 2 )
						{
							$commiss_two_money = $commission_info['commiss_two']['money'] * $goods['quantity'];
						}
						else if( $is_member_level_buy ==1 && $goods['is_mb_level_buy'] == 1 )
						{
							$commiss_two_money = round( ($commission_info['commiss_two']['fen'] * $goods['level_total'] )/100 , 2);
						}
						else
						{
							if( $is_vip_card_member == 1 && $goods['is_take_vipcard'] == 1 )
							{
								$commiss_two_money = round( ($commission_info['commiss_two']['fen'] * $goods['card_total'] )/100 , 2);
							}else{
								$commiss_two_money = round( ($commission_info['commiss_two']['fen'] * $goods['total'] )/100 , 2);
							}
							
						}
					}
					if($commiss_level >= 3)
					{
						if( $commission_info['commiss_three']['type'] == 2 )
						{
							$commiss_three_money = $commission_info['commiss_three']['money'] * $goods['quantity'];
						}else{
							if( $is_vip_card_member == 1 && $goods['is_take_vipcard'] == 1 )
							{
								$commiss_three_money = round( ($commission_info['commiss_three']['fen'] * $goods['card_total'] )/100 , 2);
							}
							else if( $is_member_level_buy ==1 && $goods['is_mb_level_buy'] == 1 )
							{
								$commiss_three_money = round( ($commission_info['commiss_three']['fen'] * $goods['level_total'] )/100 , 2);
							}
							else
							{
								$commiss_three_money = round( ($commission_info['commiss_three']['fen'] * $goods['total'] )/100 , 2);
							}
						}
					}
				}
					
	            if($is_pin == 1)
	            {
				 	//$pin_goods = M('pin_goods')->field('commiss_one_pin_disc,commiss_two_pin_disc,commiss_three_pin_disc')->where( array('goods_id' => $goods_id) )->find();
					//commiss_level
					
					
					//暂时屏蔽三级分销代码
					/** get_goods_commission_info($goods_id,$member_id)
					
					if(C('is_open_fissionsharing') == 1)
					{
						if(C('fissionsharing_level') >= 1)
						{
							$commiss_fen_one_money = round( ($goods_info['commiss_fen_one_disc'] * $goods['total'])/100 , 2);
						}
						if(C('fissionsharing_level') >= 2)
						{
							$commiss_fen_two_money = round( ($goods_info['commiss_fen_two_disc'] * $goods['total'])/100 , 2);
						}
						if(C('fissionsharing_level') >= 3)
						{
							$commiss_fen_three_money = round( ($goods_info['commiss_fen_three_disc'] * $goods['total'])/100 , 2);
						}
					}
					**/
					
					$goods_info['type'] = 'pin';
	                $pin_model =   D('Home/Pin');
					
					if($goods['pin_id'] > 0)
					{
						$pin_id = $pin_model->checkPinState($goods['pin_id']);
						
						$is_pin_over = $pin_model->getNowPinState($goods['pin_id']);
						if($is_pin_over == 1 || $is_pin_over == 2)
						{
							$pin_id = 0;
						}
					}else{
						$pin_id = 0;
					}
						
					//addOrder
				
	                if($pin_id ==0) {
	                    //新开团
						
						
						$pin_id = $pin_model->openNewTuan($order_id,$goods_id,$data['member_id']);
	                    
	                    $is_new_tuan = true;
	                }
					
	                //插入拼团订单
	                $pin_model->insertTuanOrder($pin_id,$order_id);
					
					
	            }else{
					
					//暂时关闭分佣
					/**
					
					if(C('is_open_fissionsharing') == 1)
					{
						if(C('fissionsharing_level') >= 1)
						{
							$commiss_fen_one_money = round( ($goods_info['commiss_fen_one_disc'] * $goods['total'])/100 , 2);
						}
						if(C('fissionsharing_level') >= 2)
						{
							$commiss_fen_two_money = round( ($goods_info['commiss_fen_two_disc'] * $goods['total'])/100 , 2);
						}
						if(C('fissionsharing_level') >= 3)
						{
							$commiss_fen_three_money = round( ($goods_info['commiss_fen_three_disc'] * $goods['total'])/100 , 2);
						}
					}
					**/
				}
				//var_dump($goods_info,$goods['total']);die();
				
				$goods['member_disc'] = isset($goods['member_disc']) ? $goods['member_disc'] : 100;
				
	            //判断是否拼团结束
	            $type = ($is_pin == 1) ? 'pintuan': 'normal';
				
				//lionfish_comshop_order_goods
				
				$img_info = D('Home/Pingoods')->get_goods_images($goods_id);
					
				$goods_info['image'] = $img_info['image'];
				
				
				if(!empty($goods['option']) && $goods['option'] != 'undefined')
	            {
					$option_image_info = D('Home/Front')->get_goods_sku_item_image($goods['option']);	
					
					if( !empty($option_image_info) )
					{
						$goods_info['image'] = $option_image_info['thumb'];
					}
				}
				
				$supply_id_info = D('Home/Front')->get_goods_common_field($goods_id , 'supply_id');
				
				//supply_id
				
				//rela_goodsoption_valueid $goods['option'] == 'undefined' ? '':$goods['option'];
				$goods['option'] = $goods['option'] == 'undefined' ? '':$goods['option'];
				
				$codes = '';
				
				if( !empty($goods['option']) )
				{
					$codes_info = M('lionfish_comshop_goods_option_item_value')->field('goodssn')->where( array('option_item_ids' => $goods['option'], 'goods_id' => $goods_id ) )->find();
					if( !empty($codes_info) )
					{
						$codes = $codes_info['goodssn'];
					}
				}else{
					$codes_info = M('lionfish_comshop_goods')->field('codes')->where( array('id' => $goods_id ) )->find();
					if( !empty($codes_info) )
					{
						$codes = $codes_info['codes'];
					}
				}
				
				$order_goods_data = array();
				$order_goods_data['order_id'] = $order_id;
				$order_goods_data['goods_id'] = $goods_id;
				$order_goods_data['store_id'] = $goods_info['store_id'];
				$order_goods_data['supply_id'] = $supply_id_info['supply_id'];
				$order_goods_data['name'] = addslashes($goods['name']);
				$order_goods_data['model'] = $codes;
				$order_goods_data['commiss_one_money'] = $commiss_one_money;
				$order_goods_data['commiss_two_money'] = $commiss_two_money;
				$order_goods_data['commiss_three_money'] = $commiss_three_money;
				$order_goods_data['commiss_fen_one_money'] = $commiss_fen_one_money;
				$order_goods_data['commiss_fen_two_money'] = $commiss_fen_two_money;
				$order_goods_data['commiss_fen_three_money'] = $commiss_fen_three_money;
				$order_goods_data['head_disc'] = $goods['header_disc'];
				$order_goods_data['member_disc'] = $goods['member_disc'];
				$order_goods_data['level_name'] = $goods['level_name'];
				$order_goods_data['is_pin'] = $is_pin;
				$order_goods_data['goods_images'] = $goods_info['image'];
				$order_goods_data['goods_type'] = $type;
				
				//'fenbi_li'      => $fenbi_li,  $data['order_goods_total_money']
				
				if( $data['order_goods_total_money'] > 0)
				{
					if( $is_vip_card_member == 1 && $goods['is_take_vipcard'] == 1 )
					{
						$order_goods_data['shipping_fare'] = round( $data['shipping_fare'] * ($goods['card_total']/$data['order_goods_total_money']), 2);
					}
					else if( $is_member_level_buy ==1 && $goods['is_mb_level_buy'] == 1 )
					{
						$order_goods_data['shipping_fare'] = round( $data['shipping_fare'] * ($goods['level_total']/$data['order_goods_total_money']), 2);
					}
					else
					{
						$order_goods_data['shipping_fare'] = round( $data['shipping_fare'] * ($goods['total']/$data['order_goods_total_money']), 2);
					}
					
					if( $goods['can_man_jian'] ==1 )
					{
						if( $is_vip_card_member == 1 && $goods['is_take_vipcard'] == 1 )
						{
							$order_goods_data['fullreduction_money'] = round( $order['fullreduction_money'] * ($goods['card_total']/$man_total_free) , 2);
						}
						else if( $is_member_level_buy ==1 && $goods['is_mb_level_buy'] == 1 )
						{
							$order_goods_data['fullreduction_money'] = round( $order['fullreduction_money'] * ($goods['level_total']/$man_total_free) , 2);
						}
						else
						{
							$order_goods_data['fullreduction_money'] = round( $order['fullreduction_money'] * ($goods['total']/$man_total_free) , 2);
						}
						
					}else{
						$order_goods_data['fullreduction_money'] = 0;
					}
					
					if( $is_vip_card_member == 1 && $goods['is_take_vipcard'] == 1 )
					{
						$order_goods_data['fenbi_li'] = round($goods['card_total']/$data['order_goods_total_money'],2);
					}
					else if( $is_member_level_buy ==1 && $goods['is_mb_level_buy'] == 1 )
					{
						$order_goods_data['fenbi_li'] = round($goods['level_total']/$data['order_goods_total_money'],2);
					}
					else
					{
						$order_goods_data['fenbi_li'] = round($goods['total']/$data['order_goods_total_money'],2);
					}
				}else{
					$order_goods_data['shipping_fare'] = 0;
					$order_goods_data['fullreduction_money'] = 0;
					//$order_goods_data['voucher_credit'] = 0;
					$order_goods_data['fenbi_li'] = 0;
				}
				
				$order_goods_data['score_for_money'] = round($order['score_for_money'] * $order_goods_data['fenbi_li'],2);
				
				if( $data['voucher_id'] > 0 )
				{
					if( !empty($bili_voucher_goodslist) && $limit_money_total > 0 && isset($bili_voucher_goodslist[$goods_id.'_'.$goods['option']]) )
					{
						$tmp_keys = $goods_id.'_'.$goods['option'];
						
						$order_goods_data['voucher_credit'] = round( $order['voucher_credit'] * ($bili_voucher_goodslist[$tmp_keys]['money']/$limit_money_total), 2);
					}else{
						$order_goods_data['voucher_credit'] = 0;
					}
				}else{
					$order_goods_data['voucher_credit'] = 0;
				}
			
				if( $data['is_free_shipping_fare'] == 1 )
				{
					 $order_goods_data['fare_shipping_free'] = $order_goods_data['shipping_fare'];
					 $order_goods_data['shipping_fare'] = 0;
				}
				
				if( $is_vip_card_member == 1 && $goods['is_take_vipcard'] == 1 )
				{
					$order_goods_data['price'] = $goods['card_price'];
					$order_goods_data['oldprice'] = $goods['price'];
					$order_goods_data['total'] = $goods['card_total'];
					$order_goods_data['old_total'] = $goods['total'];
					$order_goods_data['is_vipcard_buy'] = 1;
					$order_goods_data['is_level_buy'] = 0;
					
					$is_vipcard_buy = 1;
					$is_level_buy = 0;
				}
				else if( $is_member_level_buy ==1 && $goods['is_mb_level_buy'] == 1 )
				{
					$order_goods_data['price'] = $goods['levelprice'];
					$order_goods_data['oldprice'] = $goods['price'];
					$order_goods_data['total'] = $goods['level_total'];
					$order_goods_data['old_total'] = $goods['total'];
					$order_goods_data['is_vipcard_buy'] = 0;
					$order_goods_data['is_level_buy'] = 1;
					
					$is_level_buy = 1;
					$is_vipcard_buy = 0;
				}
				else{
					$order_goods_data['price'] = $goods['price'];
					$order_goods_data['oldprice'] = $goods['price'];
					$order_goods_data['total'] = $goods['total'];
					$order_goods_data['old_total'] = $goods['total'];
					$order_goods_data['is_vipcard_buy'] = 0;
					
					$order_goods_data['is_level_buy'] = 0;
					
					$is_level_buy = 0;
					$is_vipcard_buy = 0;
				}
		
				
				$order_goods_data['quantity'] = $goods['quantity'];
				
				$order_goods_data['rela_goodsoption_valueid'] = $goods['option'] == 'undefined' ? '':$goods['option'];
				$order_goods_data['comment'] = $goods['comment'];
				
				$order_goods_data['is_statements_state'] = 0;
				$order_goods_data['statements_end_time'] = 0;
				$order_goods_data['addtime'] = time();
				
				
				$order_goods_id = M('lionfish_comshop_order_goods')->add($order_goods_data);
	            
				if( !$order_goods_id )
				{
					die();
				}
				
				
				if( $order_goods_data['score_for_money'] > 0 )
				{
					$num = $order_goods_data['score_for_money'] * $score_forbuy_money;
					//扣除会员的积分
					 D('Admin/Member')->sendMemberPointChange($data['member_id'],$num, 1 ,'下单扣除积分','orderbuy', $order_id ,$order_goods_id);
				}
				
				//检测是否需要将订单放入分佣里面
				//暂时先关闭
				/**
				if(C('is_open_fissionsharing') == 1)
				{
					$share_model->add_sharing_order($order_id,$goods_id,$order_goods_id,$data['member_id'],$goods_info['store_id'] );	
				}
				**/
	
	            if(!empty($goods['option']))
	            {
	               
	                $option_value_id_arr = explode('_',$goods['option']);
	                
	                foreach($option_value_id_arr as $id_val)
	                {
						$goods_option_value = M('lionfish_comshop_goods_option_item')->where( array('id' => $id_val ) )->find();
						
						$option_value = M('lionfish_comshop_goods_option')->where( array('id' => $goods_option_value['goods_option_id']) )->find();
						
						$order_option_data = array();
						$order_option_data['order_id'] = $order_id;
						$order_option_data['order_goods_id'] = $order_goods_id;
						$order_option_data['goods_option_id'] = $goods_option_value['goods_option_id'];
						$order_option_data['name'] = $option_value['title'];
						$order_option_data['value'] = $goods_option_value['title'];
						
						M('lionfish_comshop_order_option')->add( $order_option_data );
						
	                }
	            }
	
	            if($kucun_method == 0)
	            {
	                D('Home/Pingoods')->del_goods_mult_option_quantity($order_id,$goods['option'],$goods_id,$goods['quantity'],1);
	            }
	        }
	    }
		
		if( $is_soli_order > 0 )
		{
			//说明是群接龙的
			//$order_id;
			// 
			$soli_data = array();
			$soli_data['uniacid']  = 0;
			$soli_data['soli_id']  = $is_soli_order;
			$soli_data['order_id'] = $order_id;
			$soli_data['addtime'] = time();
			
			M('lionfish_comshop_solitaire_order')->add( $soli_data );
		}
		
		
		//type normal pintuan is_pin
		$order_type = $is_pin == 1 ? 'pintuan': 'normal';
		
		$pintuan_model_buy = D('Home/Front')->get_config_by_name('pintuan_model_buy');
		
		if( empty($pintuan_model_buy) || $pintuan_model_buy ==0 )
		{
			$pintuan_model_buy = 0;
		}
		
		//
		$up_order_data = array();
		$up_order_data['is_pin'] = $is_pin;
		$up_order_data['is_vipcard_buy'] = $is_vipcard_buy;
		$up_order_data['is_level_buy'] = $is_level_buy;
		
		if( $pintuan_model_buy == 0 && $is_pin  == 1)
		{
			$up_order_data['head_id'] = 0;
		}
		
		if( $is_soli_order > 0)
		{
			$up_order_data['soli_id'] = $is_soli_order;
		}
		
		
		M('lionfish_comshop_order')->where( array('order_id' => $order_id) )->save($up_order_data);
		
	    if(isset($data['totals'])){
	        foreach ($data['totals'] as $total) {
				
				$order_total_data = array();
				$order_total_data['order_id'] = $order_id;
				$order_total_data['code'] = $total['code'];
				$order_total_data['title'] = $total['title'];
				$order_total_data['text'] = $total['text'];
				$order_total_data['value'] = $total['value'];
				$order_total_data['sort_order'] = 0;
				
				M('lionfish_comshop_order_total')->add($order_total_data);
				
	        }
	    }
	
	    $oh = array();
	    $oh['order_id']=$order_id;
	    $oh['order_status_id']=3;
	    $oh['comment']='创建订单';
	    $oh['date_added']=time();
		
		M('lionfish_comshop_order_history')->add($oh);
		 
		
	    return $order_id;
	}
	
	
	/**
	 * 通用设置订单状态
	 * 一般拼团成功时使用
	 */
	function change_order_status($order_id,$order_status_id)
	{
		$up_order_data = array();
		$up_order_data['order_status_id'] = $order_status_id;
		
		
		M('lionfish_comshop_order')->where( array('order_id' => $order_id) )->save($up_order_data);
		
	}
	
	
	public function get_area_info($id)
	{
		
		$area_info = M('lionfish_comshop_area')->where( array('id' => $id) )->find();
		
		return $area_info;
	}
	
	public function get_config_by_name($name)
	{
		
		$info = M('lionfish_comshop_config')->where( array('name' => $name) )->find();
		
		return $info['value'];
	}
	
	//$order_comment_count =  M('order_comment')->where( array('goods_id' => $id, 'state' => 1) )->count();
	
	
	public function get_goods_common_field($goods_id , $filed='*')
	{
		
		$info = M('lionfish_comshop_good_common')->field($filed)->where( array('goods_id' => $goods_id ) )->find();
		
		return $info;
	}
	
	function cancel_order($order_id,$is_auto =false, $comment_msg = ''){
	
		
		//检测是否已经支付过了begin
		
		$order_relate_info = M('lionfish_comshop_order_relate')->where( array('order_id' => $order_id ) )->order('id desc')->find();
		
		if( !empty($order_relate_info) && $order_relate_info['order_all_id'] > 0  && !$is_auto)
		{
			$order_all_info = M('lionfish_comshop_order_all')->where( array('id' => $order_relate_info['order_all_id'] ) )->find();
			
			if( !empty($order_all_info) && !empty($order_all_info['out_trade_no']) )
			{
				
				$out_trade_no = $order_all_info['out_trade_no'];
		
				$appid =  D('Home/Front')->get_config_by_name('wepro_appid');
				$mch_id =      D('Home/Front')->get_config_by_name('wepro_partnerid');
				$nonce_str =    nonce_str();
				
				$pay_key = D('Home/Front')->get_config_by_name('wepro_key');
				
				
				$post = array();
				$post['appid'] = $appid;
				$post['mch_id'] = $mch_id;
				$post['nonce_str'] = $nonce_str;
				$post['out_trade_no'] = $out_trade_no;
			
				$sign = sign($post,$pay_key);
				
				$post_xml = '<xml>
							   <appid>'.$appid.'</appid>
							   <mch_id>'.$mch_id.'</mch_id>
							   <nonce_str>'.$nonce_str.'</nonce_str>
							   <out_trade_no>'.$out_trade_no.'</out_trade_no>
							   <sign>'.$sign.'</sign>
							</xml>';
					
				$url = "https://api.mch.weixin.qq.com/pay/orderquery";
				
				$result = http_request($url,$post_xml);
				
				$array = xml($result);
				
				if( $array['RETURN_CODE'] == 'SUCCESS' && $array['RETURN_MSG'] == 'OK' )
				{
					if( $array['TRADE_STATE'] == 'SUCCESS' )
					{
						$json = array();
			
						$json['msg']='商品已下架！';
						$json['code'] = 2;
						$json['msg']='订单已付款，请勿重新付款，请刷新页面!';
						echo json_encode($json);
						die();
					}
				}
				
			}
		}
		
		//检测是否已经支付过了end  
		
		//设置订单状态	
		$up_order_data = array();
		$up_order_data['order_status_id'] = 5;
		
		M('lionfish_comshop_order')->where( array('order_id' => $order_id) )->save( $up_order_data );
		
		
		//写人订单历史
		
		$order_history = array();
		$order_history['order_id'] = $order_id;
		$order_history['order_status_id'] = 5;
		$order_history['notify'] = 0;
		$order_history['comment'] = $is_auto ? '系统回收未支付订单':'用户取消了订单';
		
		if( !empty($comment_msg) )
		{
			$order_history['comment'] = $comment_msg;
		}else{
			$order_history['comment'] = $is_auto ? '系统回收未支付订单':'用户取消了订单';
		}
		
		
		$order_history['date_added']=time();
		
		M('lionfish_comshop_order_history')->add($order_history);
		
		
		//订单商品		
		$goods = M('lionfish_comshop_order_goods')->where( array('order_id' => $order_id) )->select();		
		
		//暂时屏蔽
		//$kucun_method  = C('kucun_method');
		//$kucun_method  = empty($kucun_method) ? 0 : intval($kucun_method);
		
		$kucun_method = D('Home/Front')->get_config_by_name('kucun_method');
						
		if( empty($kucun_method) )
		{
			$kucun_method = 0;
		}
		
		$order_info = M('lionfish_comshop_order')->field('voucher_id,member_id,type')->where( array('order_id' => $order_id ) )->find();
		
		foreach ($goods as $key => $value) {
			
			$score_refund_info = M('lionfish_comshop_member_integral_flow')->where( array('order_id' =>$order_id,'type' => 'orderbuy', 'order_goods_id' => $value['order_goods_id'] ) )->find();
			
			if( !empty($score_refund_info) )
			{
				 D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$score_refund_info['score'], 0 ,'退款增加积分', 'refundorder', $order_id ,$value['order_goods_id'] );
			}
				
			//integral
			
			if($order_info['type'] == 'integral')
			{
				D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$goods['total'], 0 ,'积分兑换取消订单', 'refundorder', $order_id ,$value['order_goods_id'] );
			}	
		}			
				
		
		if(isset($goods) && $kucun_method == 0 ){
			
			foreach ($goods as $key => $value) {
				D('Home/Pingoods')->del_goods_mult_option_quantity($order_id,$value['rela_goodsoption_valueid'],$value['goods_id'],$value['quantity'],2);
				
			}
		}
		
		//判断是否有优惠券退回 $order_id voucher_id 2020年5月28日关闭
		
		// if( $order_info['voucher_id'] > 0 )
		// {
		// 	M('lionfish_comshop_coupon_list')->where( array('id' => $order_info['voucher_id']) )->save( array('consume' => 'N') );
		// }
		
	}
	
	/**
		检查商品限购数量
	**/
	public function check_goods_user_canbuy_count($member_id, $goods_id)
	{
		//per_number
	
		
		$goods_desc = $this->get_goods_common_field($goods_id , 'per_number');
		
		$per_number = $goods_desc['per_number'];
		
		if($per_number > 0)
		{
			$sql = "SELECT sum(og.quantity) as count  FROM " . C('DB_PREFIX') . "lionfish_comshop_order as o,
			" .C('DB_PREFIX'). "lionfish_comshop_order_goods as og where  o.order_id = og.order_id and  og.goods_id =" . (int)$goods_id ." 
			 and o.member_id = {$member_id}   and o.order_status_id in (1,2,3,4,6,7,9,11,12,13)";
			
			$buy_count = M()->query($sql);
			
			if($buy_count >= $per_number)
			{
				return -1;
			} else {
				return ($per_number - $buy_count);
			}
		} else{
			return 0;
		}
	
		
	}
	
	/**
		订单已发货
	**/
	public function send_order_operate($order_id)
	{
		$data = array(
			'express_tuanz_time' => time()
		);
		
		$data['order_status_id'] = 4;
		
		M('lionfish_comshop_order')->where( array('order_id' => $order_id) )->save( $data );
		
		//物品名称 订单号 购买日期  配送方式 温馨提醒
		
		$order_info = M('lionfish_comshop_order')->where( array('order_id' => $order_id) )->find();
		
		$goods_list = M('lionfish_comshop_order_goods')->field('order_goods_id,name,rela_goodsoption_valueid')->where( array('order_id' => $order_id) )->select();
		
		
		 $goods_name = "";
		 foreach($goods_list as $kk => $vv) 
	     {	
			$order_option_list = M('lionfish_comshop_order_option')->where( array('order_goods_id' => $vv['order_goods_id']) )->select();
			
			$option_str_ml = '';
			
		   foreach($order_option_list as $option)
			{
				$vv['option_str'][] = $option['value'];
			}
			if( !isset($vv['option_str']) )
			{
				$option_str_ml = '';
			}else{
				$option_str_ml = implode(',', $vv['option_str']);
			}  
			$goods_name .=  $vv['name'].' '. $option_str_ml ."\r\n";
		}
		 
		$member_info = M('lionfish_comshop_member')->where( array('member_id' => $order_info['member_id'] ) )->find();
		
		$url = D('Home/Front')->get_config_by_name('shop_domain');
		
		$template_data = array();
		$template_data['keyword1'] = array('value' => $goods_name, 'color' => '#030303');
		$template_data['keyword2'] = array('value' => $order_info['order_num_alias'], 'color' => '#030303');
		$template_data['keyword3'] = array('value' => date('Y-m-d H:i:s', $order_info['pay_time']), 'color' => '#030303');
		
		
		//order_info
		if($order_info['delivery'] == 'express')
		{
			//配送方式：  温馨提醒
			$template_data['keyword4'] = array('value' => $order_info['dispatchname'].' 单号: '.$order_info['shipping_no'], 'color' => '#030303');
			$template_data['keyword5'] = array('value' => '包裹已在配送中，请关注物流信息~', 'color' => '#030303');
			
			$key4 =  $order_info['dispatchname'];
			$key5 = $order_info['dispatchname'].'配送中';
			
		
		}else if($order_info['delivery'] == 'pickup'){
			//配送方式：  温馨提醒
			$template_data['keyword4'] = array('value' => '前往团长'.$order_info['ziti_name'].'提货，联系电话：'.$order_info['ziti_mobile'], 'color' => '#030303');
			$template_data['keyword5'] = array('value' => '包裹已到您小区，请尽快提货~', 'color' => '#030303');
			
			$key4 = '自提';
			$key5 = '包裹已到'.$order_info['ziti_name'].'团长~';
			
		}else if($order_info['delivery'] == 'tuanz_send'){
			//配送方式：  温馨提醒
			$template_data['keyword4'] = array('value' => '等待团长'.$order_info['ziti_name'].'配送，联系电话：'.$order_info['ziti_mobile'], 'color' => '#030303');
			$template_data['keyword5'] = array('value' => '包裹已到您小区，请保持电话畅通~', 'color' => '#030303');
			
			$key4 = '团长配送';
			$key5 = '等待团长'.$order_info['ziti_name'].'配送~';
		}
		
		
		
		$template_id = D('Home/Front')->get_config_by_name('weprogram_template_send_order');
		
		
		$pagepath = 'lionfish_comshop/pages/order/order?id='.$order_id;
		
		
		$weprogram_use_templatetype = D('Home/Front')->get_config_by_name('weprogram_use_templatetype');
		
		if( !empty($weprogram_use_templatetype) && $weprogram_use_templatetype == 1 )
		{
			$mb_subscribe = M('lionfish_comshop_subscribe')->where( array('member_id' => $order_info['member_id'], 'type' => 'send_order' ) )->find();
			
			//...todo
			if( !empty($mb_subscribe) )
			{
				
				//判断商品名称是否超过 20字符 超过直接截取
				if(mb_strlen($goods_name,'utf-8') > 20){
					$goods_name = mb_substr($goods_name, 0, 16, 'utf-8').'...';
				}
				$template_data = array();
				$template_id = $mb_subscribe['template_id'];
				$template_data['character_string7'] = array('value' => $order_info['order_num_alias'] );
				$template_data['thing5'] = array('value' => $goods_name );
				$template_data['phrase12'] = array('value' => $key4 );
				$template_data['date6'] = array('value' => date('Y-m-d H:i:s',$order_info['express_time']));
				$template_data['thing4'] = array('value' => $key5);

				D('Seller/User')->send_subscript_msg( $template_data,$url,$pagepath,$member_info['we_openid'],$template_id );
				
				M('lionfish_comshop_subscribe')->where( array('id' => $mb_subscribe['id'] ) )->delete();
			}
		}
		else{
			$member_formid_info = M('lionfish_comshop_member_formid')->where("member_id=".$order_info['member_id']." and formid != '' and state =0 ")->order('id desc')->find();
			
			if( !empty($member_formid_info) )
			{
				
				$wx_template_data = array();
				$weixin_appid = D('Home/Front')->get_config_by_name('weixin_appid');
				$weixin_template_send_order = D('Home/Front')->get_config_by_name('weixin_template_send_order');
				
				if( !empty($weixin_appid) && !empty($weixin_template_send_order) )
				{
					$wx_template_data = array(
										'appid' => $weixin_appid,
										'template_id' => $weixin_template_send_order,
										'pagepath' => $pagepath,
										'data' => array(
														'first' => array('value' => $template_data['keyword4'],'color' => '#030303'),
														'keyword1' => array('value' => $order_info['order_num_alias'],'color' => '#030303'),
														'keyword2' => array('value' => date('Y-m-d H:i:s'),'color' => '#030303'),
														'remark' => array('value' => $template_data['keyword5'],'color' => '#030303'),
												)
										
									);
				}
				
				
				
				$res = D('Seller/User')->send_wxtemplate_msg($template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$member_formid_info['formid'],0,$wx_template_data);
				//更新
				M('lionfish_comshop_member_formid')->where( array('id' => $member_formid_info['id'] ) )->save( array('state' => 1) );
				
			}
		}	
		
	}
	
	/**
		获取商品规格图片
	**/
	public function get_goods_sku_image($snailfish_goods_option_item_value_id)
	{
	
		$info = M('lionfish_comshop_goods_option_item_value')->field('option_item_ids')->where( array('id' => $snailfish_goods_option_item_value_id) )->find();
		
		
		$option_item_ids = explode('_', $info['option_item_ids']);
		$ids_str = implode(',', $option_item_ids);
		
		
		$image_info = M('lionfish_comshop_goods_option_item')->field('thumb')->where("id in ({$ids_str}) and thumb != ''")->find();
		
		return $image_info;
	}
	

}