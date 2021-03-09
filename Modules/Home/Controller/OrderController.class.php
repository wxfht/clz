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
use Home\Model\OrderModel;
class OrderController extends CommonController {
	
	protected function _initialize(){
		parent::_initialize();
		 // 获取当前用户ID
	}
	
	/**
		直接取消订单
		1、已付款待发货 状态
		2、是不是自己的订单
		3、判断后台是否开启了状态
		4、记录日志
		5、处理订单，
		6、处理退款，
		7、打印小票
		
		结束
	**/
	public function del_cancle_order()
	{
		$gpc = I('request.');
		$_GPC = I('request.');
		
		$token = $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}
		
		$member_id = $weprogram_token['member_id'];
		
		$order_id = $_GPC['order_id'];
		
		$order_info = M('lionfish_comshop_order')->where( array('member_id' => $member_id,'order_id' => $order_id) )->find();
		
		
		if( empty($order_info) )
		{
			echo json_encode( array('code' => 1, 'msg' => '订单不存在') );
			die();
		}
		
		if( $order_info['order_status_id'] == 1)
		{
			$order_can_del_cancle = D('Home/Front')->get_config_by_name('order_can_del_cancle'); 
			
			if( empty($order_can_del_cancle) || $order_can_del_cancle == 0 )
			{
				//4、记录日志
				$order_history = array();
				$order_history['order_id'] = $order_id;
				$order_history['order_status_id'] = 5;
				$order_history['notify'] = 0;
				$order_history['comment'] = '会员前台申请，直接取消已支付待发货订单';
				$order_history['date_added'] = time();
				
				M('lionfish_comshop_order_history')->add($order_history);
				
				//5、处理订单
				$result = D('Home/Weixin')->del_cancle_order($order_id);
				
				//6、发送取消通知订单给平台
				
				$weixin_template_cancle_order = D('Home/Front')->get_config_by_name('weixin_template_cancle_order'); 
				$platform_send_info_member_id = D('Home/Front')->get_config_by_name('platform_send_info_member'); 
				
				if( !empty($weixin_template_cancle_order) && !empty($platform_send_info_member_id) )
				{
					$weixin_template_order =array();
					$weixin_appid = D('Home/Front')->get_config_by_name('weixin_appid' );
					
					
					if( !empty($weixin_appid) && !empty($weixin_template_cancle_order) )
					{
						$head_pathinfo = "lionfish_comshop/pages/index/index";
						
						$weopenid = M('lionfish_comshop_member')->where( array('member_id' => $platform_send_info_member_id ) )->find();
						
						$weixin_template_order = array(
												'appid' => $weixin_appid,
												'template_id' => $weixin_template_cancle_order,
												'pagepath' => $head_pathinfo,
												'data' => array(
																'first' => array('value' => '您好，您收到了一个取消订单，请尽快处理','color' => '#030303'),
																'keyword1' => array('value' => $order_info['order_num_alias'],'color' => '#030303'),
																'keyword2' => array('value' => '取消','color' => '#030303'),  
																'keyword3' => array('value' => sprintf("%01.2f", $order_info['total']),'color' => '#030303'),
																'keyword4' => array('value' => date('Y-m-d H:i:s'),'color' => '#030303'),
																'keyword5' => array('value' => $weopenid['username'],'color' => '#030303'),
																'remark' => array('value' => '此订单已于'.date('Y-m-d H:i:s').'被用户取消，请尽快处理','color' => '#030303'),
																)
										);
						D('Seller/User')->just_send_wxtemplate($weopenid['we_openid'], 0, $weixin_template_order );					
										
					}				
				}
				
				if( $result['code'] == 0 )
				{
					
					$is_print_cancleorder = D('Home/Front')->get_config_by_name('is_print_cancleorder');
					if( isset($is_print_cancleorder) && $is_print_cancleorder == 1 )
					{
						D('Seller/Printaction')->check_print_order($order_id,'用户取消订单');
					}
					
					echo json_encode( array('code' => 0 ) );
					die();
				}else{
					echo json_encode( array('code' => 1, 'msg' => $result['msg'] ) );
					die();
				}
				
			}else{
				echo json_encode( array('code' => 1, 'msg' => '未开启此项功能') );
				die();
			}
			
		}else{
			echo json_encode( array('code' => 1, 'msg' => '订单状态不正确，只有已支付，未发货的订单才能取消') );
			die();
		}
		
		
	}
	
	public function order_info()
	{
		$gpc = I('request.');
		
		$token = $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}
		
		$member_id = $weprogram_token['member_id'];
		
		
		
		$order_id = $gpc['id'];
	    
	    
		$order_info = M('lionfish_comshop_order')->where( array('member_id' => $member_id,'order_id' => $order_id) )->find();
		
		$pick_up_info = array();
		$pick_order_info = array();
		
		if( $order_info['delivery'] == 'pickup' )
		{
			//查询自提点
			$pick_order_info = array();
					
			$pick_id = 0;
			$pick_up_info = array();
			
		}

		$order_status_info = M('lionfish_comshop_order_status')->where( array('order_status_id' => $order_info['order_status_id'] ) )->find();
		
	    //10 name
		if($order_info['order_status_id'] == 10)
		{
			$order_status_info['name'] = '等待退款';
		}
		else if($order_info['order_status_id'] == 4 && $order_info['delivery'] =='pickup')
		{
			//delivery 6
			$order_status_info['name'] = '待自提';
			//已自提
		}
		else if($order_info['order_status_id'] == 6 && $order_info['delivery'] =='pickup')
		{
			//delivery 6
			$order_status_info['name'] = '已自提';
			
		}
		else if($order_info['order_status_id'] == 1 && $order_info['type'] == 'lottery')
		{
			//等待开奖
			//一等奖
			if($order_info['lottery_win'] == 1)
			{
				$order_status_info['name'] = '一等奖';
			}else {
				$order_status_info['name'] = '等待开奖';
			}
			
		}
		
		//$order_info['type']
		
		//open_auto_delete
		
		
		if($order_info['order_status_id'] == 3)
		{
			$open_auto_delete = D('Home/Front')->get_config_by_name('open_auto_delete');
			$auto_cancle_order_time = D('Home/Front')->get_config_by_name('auto_cancle_order_time');
			
			$order_info['open_auto_delete'] = $open_auto_delete;
			//date_added
			if($open_auto_delete == 1)
			{
				$order_info['over_buy_time'] = $order_info['date_added'] + 3600 * $auto_cancle_order_time;
				$order_info['cur_time'] = time();
			}
		
		}
		
		
		$shipping_province = M('lionfish_comshop_area')->where( array('id' => $order_info['shipping_province_id'] ) )->find();
		
		$shipping_city = M('lionfish_comshop_area')->where( array('id' => $order_info['shipping_city_id'] ) )->find();
		
		$shipping_country = M('lionfish_comshop_area')->where( array('id' => $order_info['shipping_country_id'] ) )->find();
	    
		
	    $order_goods_list = M('lionfish_comshop_order_goods')->where( array('order_id' => $order_id ) )->select();
		
		
		$shiji_total_money = 0;
		$member_youhui = 0.00;
		
		
		$pick_up_time = "";
		$pick_up_type = -1;
		$pick_up_weekday = '';
		$today_time = $order_info['pay_time'];
		
		$arr = array('天','一','二','三','四','五','六');
	
		$url = D('Home/Front')->get_config_by_name('shop_domain').'/';
		
		$attachment_type = D('Home/Front')->get_config_by_name('attachment_type');
		$qiniu_url = D('Home/Front')->get_config_by_name('qiniu_url');
	    
	    foreach($order_goods_list as $key => $order_goods)
	    {
				
			$order_refund_goods = M('lionfish_comshop_order_refund')->where( array('order_id' =>$order_id,'order_goods_id' => $order_goods['order_goods_id'] ) )->order('ref_id desc')->find();			
		
			if(!empty($order_refund_goods))
			{
				$order_refund_goods['addtime'] = date('Y-m-d H:i:s', $order_refund_goods['addtime']);
			}
			
			
			
			$order_option_info = M('lionfish_comshop_order_option')->field('value')->where( array('order_goods_id' => $order_goods['order_goods_id'],'order_id' => $order_id) )->select();
			
			$order_goods['order_refund_goods'] = $order_refund_goods;
			 
	        foreach($order_option_info as $option)
	        {
	            $order_goods['option_str'][] = $option['value'];
	        }
			if(empty($order_goods['option_str']))
			{
				//option_str
				 $order_goods['option_str'] = '';
			}else{
				 $order_goods['option_str'] = implode(',', $order_goods['option_str']);
			}
	       //
		    $order_goods['shipping_fare'] = round($order_goods['shipping_fare'],2);
		    $order_goods['price'] = round($order_goods['price'],2);
		    $order_goods['total'] = round($order_goods['total'],2);
		   
			if( $order_goods['is_vipcard_buy'] == 1 || $order_goods['is_level_buy'] ==1 )
			{
				$order_goods['price'] = round($order_goods['oldprice'],2);
			}
			$order_goods['real_total'] = round($order_goods['quantity'] * $order_goods['price'],2);
			
			/**
					$goods_images = file_image_thumb_resize($vv['goods_images'],400);
					if(is_array($goods_images))
					{
						$vv['goods_images'] = $vv['goods_images'];
					}else{
						 $vv['goods_images']= tomedia( file_image_thumb_resize($vv['goods_images'],400) ); 
					}	
					
			**/
			
			
			if($attachment_type == 1)
			{
				$goods_images = $qiniu_url.resize($order_goods['goods_images'],400,400);
			}else{
				$goods_images = $url.resize($order_goods['goods_images'],400,400);
			}
			
			if( !is_array($goods_images) )
			{
				 $order_goods['image']=  tomedia( $goods_images );
				$order_goods['goods_images']= tomedia( $goods_images ); 
			}else{
				 $order_goods['image']=  $order_goods['goods_images'];
			}
	       
		   $order_goods['hascomment'] = 0;
		   
			$order_goods_comment_info = M('lionfish_comshop_order_comment')->field('comment_id')->where( array('goods_id' => $order_goods['goods_id'],'order_id' =>$order_id) )->find();
			
			
			if( !empty($order_goods_comment_info) )
			{
				$order_goods['hascomment'] = 1;
			}
			
			$order_goods['can_ti_refund'] = 1;
		
			$disable_info = M('lionfish_comshop_order_refund_disable')->where( array('order_id' => $order_id, 'order_goods_id' => $order_goods['order_goods_id']) )->find();
			
			if( !empty($disable_info) )
			{
				$order_goods['can_ti_refund'] = 0;
			}
			
			if($order_goods['is_refund_state'] == 1)
			{
				//已经再申请退款中了。或者已经退款了。
				$order_refund_info = M('lionfish_comshop_order_refund')->field('state')->where( array('order_id' => $order_id,'order_goods_id' => $order_goods['order_goods_id'] ) )->find();
				
				if( $order_refund_info['state'] == 3 )
				{
					$order_goods['is_refund_state'] = 2;
				}
			}
			
			
			//ims_ 
					
			$goods_info = M('lionfish_comshop_goods')->field('productprice as price')->where( array('id' => $order_goods['goods_id']) )->find();		
 			
			$goods_cm_info = M('lionfish_comshop_good_common')->field('pick_up_type,pick_up_modify,goods_share_image')->where( array('goods_id' => $order_goods['goods_id']) )->find();		
						
			if($pick_up_type == -1 || $goods_cm_info['pick_up_type'] > $pick_up_type)
			{
				$pick_up_type = $goods_cm_info['pick_up_type'];
				
				if($pick_up_type == 0)
				{
					$pick_up_time = date('m-d', $today_time);
					//$pick_up_weekday = '周'.$arr[date('w',$today_time)];
				}else if( $pick_up_type == 1 ){
					$pick_up_time = date('m-d', $today_time+86400);
					//$pick_up_weekday = '周'.$arr[date('w',$today_time+86400)];
				}else if( $pick_up_type == 2 )
				{
					$pick_up_time = date('m-d', $today_time+86400*2);
					//$pick_up_weekday = '周'.$arr[date('w',$today_time+86400*2)];
				}else if($pick_up_type == 3)
				{
					$pick_up_time = $goods_cm_info['pick_up_modify'];
				}
			}
			
			if( !empty($goods_cm_info['goods_share_image']) )
			{
				$order_goods['goods_share_image']=  tomedia( $goods_cm_info['goods_share_image'] );
			}else{
				$order_goods['goods_share_image'] = $order_goods['image'];
			}
			
			$order_goods['shop_price'] = $goods_info['price'];
			 
			
			$store_info	= array('s_true_name' =>'','s_logo' => '');
			
			$store_info['s_true_name'] = D('Home/Front')->get_config_by_name('shoname');
			
			//$store_info['s_logo'] = D('Home/Front')->get_config_by_name('shoplogo'); 
			
			if( !empty($store_info['s_logo']) )
			{
				$store_info['s_logo'] = tomedia($store_info['s_logo']);
			}else{
				$store_info['s_logo'] = '';
			}
			
			
			$order_goods['store_info'] = $store_info;
			
			unset($order_goods['model']);
			unset($order_goods['rela_goodsoption_valueid']);
			unset($order_goods['comment']);
			
	        $order_goods_list[$key] = $order_goods;
			$shiji_total_money += $order_goods['quantity'] * $order_goods['price'];
			
			$member_youhui += ($order_goods['real_total'] - $order_goods['total']);
	    }
	    
		unset($order_info['store_id']);
		unset($order_info['email']);
		unset($order_info['shipping_city_id']);
		unset($order_info['shipping_country_id']);
		unset($order_info['shipping_province_id']);
		//unset($order_info['comment']);
		unset($order_info['voucher_id']);
		unset($order_info['is_balance']);
		unset($order_info['lottery_win']);
		unset($order_info['ip']);
		unset($order_info['ip_region']);
		unset($order_info['user_agent']);
		
	
		$order_info['shipping_fare'] = round($order_info['shipping_fare'],2) < 0.01 ? '0.00':round($order_info['shipping_fare'],2) ;
		$order_info['voucher_credit'] = round($order_info['voucher_credit'],2) < 0.01 ? '0.00':round($order_info['voucher_credit'],2) ;
		$order_info['fullreduction_money'] = round($order_info['fullreduction_money'],2) < 0.01 ? '0.00':round($order_info['fullreduction_money'],2) ;
		
		$need_data = array();
		
		if($order_info['type'] == 'integral')
		{
			//暂时屏蔽积分商城
			$order_info['score'] = round($order_info['total'],2);
		}
		
		
		$order_info['total'] = round($order_info['total']+$order_info['shipping_fare']-$order_info['voucher_credit']-$order_info['fullreduction_money'] - $order_info['score_for_money'],2)	;
		
		if($order_info['total'] < 0)
		{
			$order_info['total'] = '0.00';
		}
		
		
		
		$order_info['real_total'] = round($shiji_total_money,2)+$order_info['shipping_fare'];		
		$order_info['price'] = round($order_info['price'],2);		
		$order_info['member_youhui'] = round($member_youhui,2) < 0.01 ? '0.00':round($member_youhui,2);	
		$order_info['pick_up_time'] = $pick_up_time;
		

		
		$order_info['shipping_fare'] = sprintf("%.2f",$order_info['shipping_fare']);
		$order_info['voucher_credit'] = sprintf("%.2f",$order_info['voucher_credit']);
		$order_info['fullreduction_money'] = sprintf("%.2f",$order_info['fullreduction_money']);
		$order_info['total'] = sprintf("%.2f",$order_info['total']);
		$order_info['real_total'] = sprintf("%.2f",$order_info['real_total']);
		
		
		$order_info['date_added'] = date('Y-m-d H:i:s', $order_info['date_added']);
		
	
		if($order_info['delivery'] =='pickup')
		{
			
		
		}else{
			
		
		}
		
		
		if( !empty($order_info['pay_time']) && $order_info['pay_time'] >0 )
		{
			$order_info['pay_date'] = date('Y-m-d H:i:s', $order_info['pay_time']);
		}else{
			$order_info['pay_date'] = '';
		}
		
		$order_info['express_tuanz_date'] = date('Y-m-d H:i:s', $order_info['express_tuanz_time']);
		$order_info['receive_date'] = date('Y-m-d H:i:s', $order_info['receive_time']);
		
		
		//"delivery": "pickup", enum('express', 'pickup', 'tuanz_send')
		if($order_info['delivery'] == 'express')
		{
			$order_info['delivery_name'] = '快递';
		}else if($order_info['delivery'] == 'pickup')
		{
			$order_info['delivery_name'] = '自提';
		}else if($order_info['delivery'] == 'tuanz_send'){
			$order_info['delivery_name'] = '团长配送';
		}
		
		//把多个订单号拆分成数组
		if(strstr($order_info['shipping_no'], '；')){
			
			$order_info['shipping_nos'] =explode('；',$order_info['shipping_no']);
		}elseif(strstr($order_info['shipping_no'], ';')){
			
			$order_info['shipping_nos'] =explode(';',$order_info['shipping_no']);
		}else{
			$order_info['shipping_nos'][0] = $order_info['shipping_no'];
		}
		
		$need_data['order_info'] = $order_info;
		$need_data['order_status_info'] = $order_status_info;
		$need_data['shipping_province'] = $shipping_province;
		$need_data['shipping_city'] = $shipping_city;
		$need_data['shipping_country'] = $shipping_country;
		$need_data['order_goods_list'] = $order_goods_list;
		
		$need_data['goods_count'] = count($order_goods_list);
		
		//$order_info['order_status_id'] 13  平台介入退款
		$order_refund_historylist = array();
		$pingtai_deal = 0;
		
		//判断是否已经平台处理完毕   
		
		$order_refund_historylist = M('lionfish_comshop_order_refund_history')->where( array('order_id' => $order_id) )->order('addtime asc')->select();		
		
		foreach($order_refund_historylist as $key => $val)
		{
			if($val['type'] ==3)
			{
				$pingtai_deal = 1;
			}
		}
		
		$order_refund = M('lionfish_comshop_order_refund')->where( array('order_id' => $order_id) )->find();
		
		if(!empty($order_refund))
		{
			$order_refund['addtime'] = date('Y-m-d H:i:s', $order_refund['addtime']);
		}
		
		$need_data['pick_up'] = $pick_up_info;
		
		
		
		if( empty($pick_order_info['qrcode']) && false)
		{
			
		}
		
		$need_data['pick_order_info'] = $pick_order_info;
		
		$order_pay_after_share = D('Home/Front')->get_config_by_name('order_pay_after_share');
		
		
		if($order_pay_after_share==1){
			$order_pay_after_share_img = D('Home/Front')->get_config_by_name('order_pay_after_share_img');
			$order_pay_after_share_img = $order_pay_after_share_img ? tomedia($order_pay_after_share_img) : '';
			$need_data['share_img'] = empty($order_pay_after_share_img) ? $need_data['order_goods_list'][0]['image']: $order_pay_after_share_img;
		}else{
			if(empty($need_data['order_goods_list'][0]['goods_share_image']))
			{
				$need_data['share_img'] = $need_data['order_goods_list'][0]['image'];
			}
		}
		
		
		$order_can_del_cancle = D('Home/Front')->get_config_by_name('order_can_del_cancle');
		
		$order_can_del_cancle = empty($order_can_del_cancle) || $order_can_del_cancle == 0 ? 1 : 0;
		
		$is_hidden_orderlist_phone = D('Home/Front')->get_config_by_name('is_hidden_orderlist_phone');
		
		$is_show_guess_like = D('Home/Front')->get_config_by_name('is_show_order_guess_like');
		
		$user_service_switch = D('Home/Front')->get_config_by_name('user_service_switch');
		
		
		
		echo json_encode(
			array(
				'code' => 0,
				'data' => $need_data,
				'pingtai_deal' => $pingtai_deal,
				'order_refund' => $order_refund,
				'order_can_del_cancle' => $order_can_del_cancle,
				'order_pay_after_share' => $order_pay_after_share,
				'is_hidden_orderlist_phone' => $is_hidden_orderlist_phone,
				'is_show_guess_like' => $is_show_guess_like,
				'user_service_switch' => $user_service_switch
			)
		);
		
	}
	
	public function sign_dan_order()
	{
		$gpc = I('request.');
		
		$token = $gpc['token'];
		$order_id = $gpc['order_id'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		$member_id = $weprogram_token['member_id'];
		
		
		$community_info = D('Home/Front')->get_member_community_info($member_id);
		
		$order_info = M('lionfish_comshop_order')->where( array('head_id' => $community_info['id'],'order_id' => $order_id) )->find();			
		
		if(!empty($order_info) && $order_info['order_status_id'] == 14)
		{
			$oh = array();	
			$oh['order_id']=$order_id;
			$oh['order_status_id']= 4;
				
			$oh['comment']='团长签收货物';
			$oh['date_added']=time();
			$oh['notify']= $order_info['order_status_id'];
			
			M('lionfish_comshop_order_history')->add( $oh );
			
			//更改订单为已发货
			D('Home/Frontorder')->send_order_operate($order_id);
			echo json_encode( array('code' => 0) );
		}else{
			echo json_encode( array('code' => 1) );
		}
		
		
	}
	
	public function order_commission()
	{
		$gpc = I('request.');
		
		$token = $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		$member_id = $weprogram_token['member_id'];
		
		if( empty($member_id) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		
		$community_info = D('Home/Front')->get_member_community_info($member_id);
		$head_id = $community_info['id'];
		
		$choose_date = $gpc['chooseDate'];
			
		$choose_date = str_replace('年','-', $choose_date);
		$choose_date = str_replace('月','-', $choose_date);
		$choose_date = $choose_date.'01 00:00:00';
		
		$BeginDate=date('Y-m-d', strtotime($choose_date));
		
		$end_date = date('Y-m-d', strtotime("$BeginDate +1 month -1 day")).' 23:59:59';
		 
		$begin_time = strtotime($BeginDate.' 00:00:00');
		$end_time = strtotime($end_date);
		
		$where = " and addtime >= {$begin_time} and addtime < {$end_time} ";
		
		$money = M('lionfish_community_head_commiss_order')->where("head_id={$head_id} and state=0 {$where}")->sum('money');			
		if( empty($money))
		{
			$money = 0;
		}			
		echo json_encode( array('code' => 0, 'money' => $money) );
		die();
			
			
		
	}
	
	public function refundorderlist()
	{
		$gpc = I('request.');
		
		$is_tuanz  = isset($gpc['is_tuanz']) ? $gpc['is_tuanz'] :0;
		$token = $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		$member_id = $weprogram_token['member_id'];
		
		
		
		$page = isset($gpc['page']) ? $gpc['page']:'1';
		
		$size = isset($gpc['size']) ? $gpc['size']:'6';
		$offset = ($page - 1)* $size;
		
		$type =  isset($gpc['type']) ? $gpc['type']:'';
		
		
		$where = ' and o.member_id = '.$member_id;
		
		$fields = " orf.state as refund_state ,orf.order_goods_id as r_order_goods_id, ";
			
		$currentTab = isset($gpc['currentTab']) ? $gpc['currentTab']:0;
		
		
		if($currentTab == 0)
		{
			
		}else if($currentTab == 1){
			//售后
			$where .= ' and o.order_status_id = 12 ';
		}else if($currentTab == 2){
			$where .= ' and  orf.state =3 ';
		}else if($currentTab == 3)
		{
			$where .= ' and orf.state =1 ';
		}
		
		$sql = "select orf.ref_id,orf.state,orf.order_goods_id,o.order_id,o.order_num_alias,o.date_added,o.delivery,o.is_pin,{$fields} o.is_zhuli,o.shipping_fare,o.shipping_tel,o.shipping_name,o.voucher_credit,o.fullreduction_money,o.store_id,o.total,o.order_status_id,o.lottery_win,o.type from ".C('DB_PREFIX')."lionfish_comshop_order_refund as orf left join  " .C('DB_PREFIX')."lionfish_comshop_order as o on orf.order_id = o.order_id  
						where  1  {$where}  
	                    order by orf.addtime desc limit {$offset},{$size}";
	  
	    $list =  M()->query($sql);
	   
	   $lionfish_comshop_order_status_list =  M('lionfish_comshop_order_status')->select();
	   
	   $url = D('Home/Front')->get_config_by_name('shop_domain');
	   
	   $status_arr = array();
	   
	   foreach( $lionfish_comshop_order_status_list as $kk => $val )
	   {
		   $status_arr[ $val['order_status_id'] ] = $val['name'];
	   }
	   
	    //createTime
	    foreach($list as $key => $val)
	    {
			
	        $val['createTime'] = date('Y-m-d H:i:s', $val['date_added']);
			
			switch( $val['state'] )
			{
				case 0:
					$val['status_name'] = '申请中';
				break;
				case 1:
					$val['status_name'] = '商家拒绝';
				break;
				case 2:
				
					break;
				case 3:
					$val['status_name'] = '退款成功';
				break;
				case 4:
					$val['status_name'] = '退款失败';
				break;
				case 5:
					$val['status_name'] = '撤销申请';
					break;
			}
			
			
			if($val['shipping_fare']<=0.001 || $val['delivery'] == 'pickup')
			{
				$val['shipping_fare'] = '免运费';
			}else{
				$val['shipping_fare'] = ''.$val['shipping_fare'];
			}
			
			
			if($val['order_status_id'] == 10)
			{
				$val['status_name'] = '等待退款';
			}
			else if($val['order_status_id'] == 4 && $val['delivery'] =='pickup')
			{
				//delivery 6
				$val['status_name'] = '待自提';
				//已自提
			}
			else if($val['order_status_id'] == 6 && $val['delivery'] =='pickup')
			{
				//delivery 6
				$val['status_name'] = '已自提';
				//已自提
			}
			else if($val['order_status_id'] == 1 && $val['type'] == 'lottery')
			{
				//等待开奖
				//一等奖
				if($val['lottery_win'] == 1)
				{
					$val['status_name'] = '一等奖';
				}else {
					$val['status_name'] = '等待开奖';
				}
			}
			else if($val['order_status_id'] == 2 && $val['type'] == 'lottery')
			{
				//等待开奖
				$val['status_name'] = '等待开奖';
			}
			
	        $quantity = 0;
	        
			if( $val['order_goods_id'] > 0 )
			{
				$goods_sql = "select order_goods_id,head_disc,member_disc,level_name,goods_id,is_pin,shipping_fare,name,goods_images,quantity,price,total,rela_goodsoption_valueid   
					from ".C('DB_PREFIX')."lionfish_comshop_order_goods where  order_goods_id=".$val['order_goods_id']." and  order_id= ".$val['order_id']."";
	        
			}else{
				$goods_sql = "select order_goods_id,head_disc,member_disc,level_name,goods_id,is_pin,shipping_fare,name,goods_images,quantity,price,total,rela_goodsoption_valueid   
					from ".C('DB_PREFIX')."lionfish_comshop_order_goods where   order_id= ".$val['order_id']."";
	        
			}
			
	        
	        $goods_list = M()->query($goods_sql); //M()->query($goods_sql);
			$total_commision = 0;
			if($val['delivery'] =='tuanz_send')
			{
				$total_commision += $val['shipping_fare'];
			}
			
	        foreach($goods_list as $kk => $vv) 
	        {
	            //commision
	            
				$order_option_list =  M('lionfish_comshop_order_option')->where( array('order_goods_id' =>$vv['order_goods_id'] ) )->select();
				
				
				if( !empty($vv['goods_images']))
				{
					
					$goods_images = $url. '/'.resize($vv['goods_images'],400,400);
					if(is_array($goods_images))
					{
						$vv['goods_images'] = $vv['goods_images'];
					}else{
						 $vv['goods_images']= $url.'/'.resize($vv['goods_images'],400,400) ; 
					}	
					
				}else{
					 $vv['goods_images']= ''; 
				}
	           
				
				$goods_filed = M('lionfish_comshop_goods')->field('productprice as price')->where( array('id' => $vv['goods_id'] ) )->find();
				
				$vv['orign_price'] = $goods_filed['price'];
	            $quantity += $vv['quantity'];
	            foreach($order_option_list as $option)
	            {
	                $vv['option_str'][] = $option['value'];
	            }
				if( !isset($vv['option_str']) )
				{
					$vv['option_str'] = '';
				}else{
					$vv['option_str'] = implode(',', $vv['option_str']);
				}
	            // $vv['price'] = round($vv['price'],2);
	            $vv['price'] = sprintf("%.2f",$vv['price']);
	            $vv['orign_price'] = sprintf("%.2f",$vv['orign_price']);
	            $vv['total'] = sprintf("%.2f",$vv['total']);
				
	            
	            $goods_list[$kk] = $vv;
	        }
			$val['total_commision'] = $total_commision;
	        $val['quantity'] = $quantity;
	        if( empty($val['store_id']) )
			{
				$val['store_id'] = 1;
			}
			
			
			$store_info	= array('s_true_name' =>'','s_logo' => '');
			
			$store_info['s_true_name'] = D('Home/Front')->get_config_by_name('shoname');
			
			$store_info['s_logo'] = D('Home/Front')->get_config_by_name('shoplogo'); 
			
			
		
			if( !empty($store_info['s_logo']))
			{
				$store_info['s_logo'] = tomedia($store_info['s_logo']);
			}else{
				
				$store_info['s_logo'] = '';
			}
			
			
			$order_goods['store_info'] = $store_info;
			
			$val['store_info'] = $store_info;
			
	        $val['goods_list'] = $goods_list;
			
			$val['total'] = $val['total'] + $val['shipping_fare']-$val['voucher_credit']-$val['fullreduction_money'];
			if($val['total'] < 0)
			{
				$val['total'] = 0;
			}
			
			$val['total'] = sprintf("%.2f",$val['total']);
	        $list[$key] = $val;
	    }
		
		$need_data = array('code' => 0);
		
		if( !empty($list) )
		{
			$need_data['data'] = $list;
			
		}else {
			$need_data = array('code' => 1);
		}
		
		echo json_encode( $need_data );
		die();
		
	}
	
	
	
	public function orderlist()
	{
		$gpc = I('request.');
		$_GPC = $gpc;
		
		$is_tuanz  = isset($gpc['is_tuanz']) ? $gpc['is_tuanz'] :0;
		$token = $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		$member_id = $weprogram_token['member_id'];
		
		$sqlcondition = "";
		$left_join = "";
		
		$page = isset($gpc['page']) ? $gpc['page']:'1';
		
		$size = isset($gpc['size']) ? $gpc['size']:'6';
		$offset = ($page - 1)* $size;
		
		$type =  isset($gpc['type']) ? $gpc['type']:'';
		
		$order_status = isset($gpc['order_status']) ? $gpc['order_status']:'-1';
	   
		if($is_tuanz == 1)
		{
			$community_info = D('Home/Front')->get_member_community_info($member_id);
			
			if( isset($_GPC['chooseDate']) && !empty($_GPC['chooseDate']) )
			{
				$where = ' and o.head_id = '.$community_info['id'] ;
			}else{
				//$where = ' and o.head_id = '.$community_info['id'].' and o.delivery != "express"  ';
				$where = ' and o.head_id = '.$community_info['id'].'  ';
			}
			
			$searchfield = isset($_GPC['searchfield']) && !empty($_GPC['searchfield']) ? $_GPC['searchfield'] : '';
			
			if( !empty($searchfield) && !empty($_GPC['keyword']))
			{
				$keyword = $_GPC['keyword'];
				
				switch($searchfield)
				{
					case 'ordersn':
						$where .= ' AND locate("'.$keyword.'",o.order_num_alias)>0'; 
					break;
					case 'member':
						$where .= ' AND (locate("'.$keyword.'",m.username)>0 or "'.$keyword.'"=o.member_id )';
						$left_join .= ' left join ' . C('DB_PREFIX'). 'lionfish_comshop_member as  m on m.member_id = o.member_id ';
					break;
					case 'address':
						$where .= ' AND ( locate("'.$keyword.'",o.shipping_name)>0 )';
						
					break;
					case 'mobile':
						$where .= ' AND ( locate("'.$keyword.'",o.shipping_tel)>0 )';
						
					break;
					case 'location':
						$where .= ' AND (locate("'.$keyword.'",o.shipping_address)>0 )';
					break;
					case 'shipping_no':
						$where .= ' AND (locate("'.$keyword.'",o.shipping_no)>0 )';
					break;
					case 'goodstitle':
						$left_join = ' inner join ( select DISTINCT(og.order_id) from ' . C('DB_PREFIX') . 'lionfish_comshop_order_goods as og  where  (locate("'.$keyword.'",og.name)>0)) gs on gs.order_id=o.order_id';
						
					break;
					case 'trans_id':
						$where .= ' AND (locate("'.$keyword.'",o.transaction_id)>0 )';
					break;
					
				}
			}
			
			
		}else{
			$where = ' and o.member_id = '.$member_id;
		}
		//时间查询
		if($gpc['BeginTime'] && $gpc['endTime']){
			//开始时间
		
			$BeginTime = strtotime($gpc['BeginTime'].' 00:00:00');
			//结束时间
			$endTime = strtotime($gpc['endTime'].' 00:00:00');
			$where .= ' and '.$BeginTime.' < o.date_added and '.$endTime.'>o.date_added';
		}
		//按月查询
	    if( isset($gpc['chooseDate']) && !empty($gpc['chooseDate']) )
		{
			$choose_date = $gpc['chooseDate'];
			
			$choose_date = str_replace('年','-', $choose_date);
			$choose_date = str_replace('月','-', $choose_date);
			$choose_date = $choose_date.'01 00:00:00';
			
			$BeginDate=date('Y-m-d', strtotime($choose_date));
			
			$end_date = date('Y-m-d', strtotime("$BeginDate +1 month -1 day")).' 23:59:59';
			 
			$begin_time = strtotime($BeginDate.' 00:00:00');
			$end_time = strtotime($end_date);
			
			
			$where .= ' and o.date_added >= '.$begin_time.' and o.date_added < '.$end_time;
		}
	    
		//全部 -1  待付款 3 待配送1 待提货4 已提货6
		//order_status $order_status
		$join = "";
		$fields = "";
		
		switch($order_status)
		{
			case -1:
			//全部 -1
			
			break;
			case 3:
			//待付款 3
				$where .= ' and o.order_status_id = 3 ';
			break;
			case 1:
			//待配送1
			$where .= ' and o.order_status_id = 1 ';
			
			break;
			case 4:
			//待提货4
			$where .= ' and o.order_status_id = 4 ';
			break;
			case 14:
			//待提货4
			$where .= ' and o.order_status_id = 14 ';
			break;
			
			case 22:
			//待确认佣金的
				$where .= ' and o.order_status_id in (1,4,14) ';
			break;
			case 357:
			//待确认佣金的
				$where .= ' and o.order_status_id in (3,5,7) ';
			break;
			case 6:
			//已提货6
				$where .= ' and o.order_status_id in (6,11) ';
			break;
			case 11:
			//已完成
			$where .= ' and o.order_status_id = 11 ';
			break;
			case 12:
			$fields = " orf.state as refund_state , ";
			
			$currentTab = isset($gpc['currentTab']) ? $gpc['currentTab']:0;
			
			$join = " ".C('DB_PREFIX').'lionfish_comshop_order_refund as orf,  ';
			$where .= ' and o.order_id = orf.order_id ';
			if($currentTab == 0)
			{
				
			}else if($currentTab == 1){
				//售后
				$where .= ' and o.order_status_id = 12 ';
			}else if($currentTab == 2){
				$where .= ' and  orf.state =3 ';
			}else if($currentTab == 3)
			{
				$where .= ' and orf.state =1 ';
			}
			
			break;
			case 7:
			//已退款
			$where .= ' and o.order_status_id = 7 ';
			break;
		}
		
		$where .= ' and o.type != "ignore" ';
		
		if( !empty($type) )
		{
			//$where .= ' and o.type != "ignore" ';
		}
	   
	    $sql = "select o.order_id,o.order_num_alias,o.date_added,o.delivery,o.is_pin,{$fields} o.is_zhuli,o.shipping_fare,o.shipping_tel,o.shipping_name,o.voucher_credit,o.fullreduction_money,o.store_id,o.total,o.order_status_id,o.lottery_win,o.type,os.name as status_name 
				from ".C('DB_PREFIX')."lionfish_comshop_order as o {$left_join}, {$join} 
                ".C('DB_PREFIX')."lionfish_comshop_order_status as os ".$sqlcondition." 
	                    where   o.order_status_id = os.order_status_id {$where}  
	                    order by o.date_added desc limit {$offset},{$size}";
	 
	    $list =  M()->query($sql);
	   
	    $open_auto_delete = D('Home/Front')->get_config_by_name('open_auto_delete');
	   
	    $cancle_hour = D('Home/Front')->get_config_by_name('auto_cancle_order_time');
		$cancle_hour_time = time() - 3600 * $cancle_hour;
		
	   
	   $url = D('Home/Front')->get_config_by_name('shop_domain');
	    //createTime
	    foreach($list as $key => $val)
	    {
			
			//判断是否需要取消订单
			//order_status_id 3 open_auto_delete
			
			if($open_auto_delete == 1 && $val['order_status_id'] == 3 &&  $val['date_added'] < $cancle_hour_time )
			{
				D('Home/Frontorder')->cancel_order($val['order_id'],  true);
				
				$val['order_status_id'] == 5;
			}
			
			
			
			if($val['delivery'] == 'pickup')
			{
				//$val['total'] = round($val['total'],2) - round($val['voucher_credit'],2);
			}else{
				//$val['total'] = round($val['total'],2)+round($val['shipping_fare'],2) - round($val['voucher_credit'],2);
			}
	        $val['createTime'] = date('Y-m-d H:i:s', $val['date_added']);
			
			// $val['delivery'] =='pickup'
			
			if($val['shipping_fare']<=0.001 || $val['delivery'] == 'pickup')
			{
				$val['shipping_fare'] = '免运费';
			}else{
				$val['shipping_fare'] = ''.$val['shipping_fare'];
			}
			
			
			if($val['order_status_id'] == 10)
			{
				$val['status_name'] = '等待退款';
			}
			else if($val['order_status_id'] == 4 && $val['delivery'] =='pickup')
			{
				//delivery 6
				$val['status_name'] = '待自提';
				//已自提
			}
			else if($val['order_status_id'] == 6 && $val['delivery'] =='pickup')
			{
				//delivery 6
				$val['status_name'] = '已自提';
				//已自提
			}
			else if($val['order_status_id'] == 1 && $val['type'] == 'lottery')
			{
				//等待开奖
				//一等奖
				if($val['lottery_win'] == 1)
				{
					$val['status_name'] = '一等奖';
				}else {
					$val['status_name'] = '等待开奖';
				}
			}
			else if($val['order_status_id'] == 2 && $val['type'] == 'lottery')
			{
				//等待开奖
				$val['status_name'] = '等待开奖';
			}
			
	        $quantity = 0;
	        
	        
			$goods_list = M('lionfish_comshop_order_goods')->field('order_goods_id,head_disc,member_disc,level_name,goods_id,is_pin,shipping_fare,name,goods_images,quantity,price,total,rela_goodsoption_valueid')->where( array('order_id' => $val['order_id']) )->select();
			
			$total_commision = 0;
			if($val['delivery'] =='tuanz_send')
			{
				$total_commision += $val['shipping_fare'];
			}
			
			
	        foreach($goods_list as $kk => $vv) 
	        {
	            //commision
				
				if($is_tuanz == 1){
				
					
					$community_order_info = M('lionfish_community_head_commiss_order')->where( array('head_id' => $community_info['id'],'order_goods_id' => $vv['order_goods_id']) )->find();
					
					
					if(!empty($community_order_info))
					{
						$vv['commision'] = $community_order_info['money']-$community_order_info['add_shipping_fare'];
						$vv['commision'] = sprintf("%.2f",$vv['commision']);
						$total_commision += $vv['commision'];
					}else{
						$vv['commision'] = "0.00";
					}						
							
				}
					
				$order_option_list = M('lionfish_comshop_order_option')->where( array('order_goods_id' => $vv['order_goods_id']) )->select();	
	            
				if( !empty($vv['goods_images']))
				{
					
					$goods_images = $url. '/'.resize($vv['goods_images'],400,400);
					if(is_array($goods_images))
					{
						$vv['goods_images'] = $vv['goods_images'];
					}else{
						 $vv['goods_images']= $url.'/'.resize($vv['goods_images'],400,400) ; 
					}	
					
				}else{
					 $vv['goods_images']= ''; 
				}
	           
					
				$goods_filed = M('lionfish_comshop_goods')->field('productprice as price')->where( array('id' => $vv['goods_id']) )->find();			
 			
				$vv['orign_price'] = $goods_filed['price'];
	            $quantity += $vv['quantity'];
	            foreach($order_option_list as $option)
	            {
	                $vv['option_str'][] = $option['value'];
	            }
				if( !isset($vv['option_str']) )
				{
					$vv['option_str'] = '';
				}else{
					$vv['option_str'] = implode(',', $vv['option_str']);
				}
	           
				 // $vv['price'] = round($vv['price'],2);
	            $vv['price'] = sprintf("%.2f",$vv['price']);
	            $vv['orign_price'] = sprintf("%.2f",$vv['orign_price']);
	            $vv['total'] = sprintf("%.2f",$vv['total']);
				
				
	            $goods_list[$kk] = $vv;
	        }
			
			$val['total_commision'] = $total_commision;
	        $val['quantity'] = $quantity;
	        if( empty($val['store_id']) )
			{
				$val['store_id'] = 1;
			}
			
			
			$store_info	= array('s_true_name' =>'','s_logo' => '');
			
			$store_info['s_true_name'] = D('Home/Front')->get_config_by_name('shoname');
			
			$store_info['s_logo'] = D('Home/Front')->get_config_by_name('shoplogo'); 
			
			
		
			if( !empty($store_info['s_logo']))
			{
				$store_info['s_logo'] = tomedia($store_info['s_logo']);
			}else{
				
				$store_info['s_logo'] = '';
			}
			
			
			$order_goods['store_info'] = $store_info;
			
			
			
			
			$val['store_info'] = $store_info;
			
			
	        $val['goods_list'] = $goods_list;
			
			if($val['type'] == 'integral')
			{
				//暂时屏蔽积分
				$val['score'] =  round($val['total'],2);
			}
			
			$val['total'] = $val['total'] + $val['shipping_fare']-$val['voucher_credit']-$val['fullreduction_money'];
			if($val['total'] < 0)
			{
				$val['total'] = 0;
			}
			
			$val['total'] = sprintf("%.2f",$val['total']);
	        $list[$key] = $val;
	    }
		
		$need_data = array('code' => 0);
		
		if( !empty($list) )
		{
			$need_data['data'] = $list;
			
		}else {
			$need_data = array('code' => 1);
		}
		
		echo json_encode( $need_data );
		die();
		
	}
	
	function receive_order_list()
	{
		$gpc = I('request.');
		
		
		
		$order_data = $gpc['order_data'];
		$token = $gpc['token'];
		
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		$member_id = $weprogram_token['member_id'];
	   
		$member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id) )->find();
		
		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		
		$community_info = D('Home/Front')->get_member_community_info($member_id);
		
		$is_member_hexiao = false;
		if( empty($community_info) && $member_info['pickup_id'] > 0  )
		{
			$parent_community_info = M('lionfish_comshop_community_pickup_member')->where( array('member_id' =>$member_id ) )->find();
			
			
			if(!empty($parent_community_info))
			{
				$is_member_hexiao = true;
				$community_info = M('lionfish_community_head')->where( array('id' => $parent_community_info['community_id'] ) )->find();		
			}
		}
		
		
		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 1) );
			die();	
		}
		if( is_array($order_data) )
		{
			$order_data_str = implode(',', $order_data);
		}else{
			$order_data_str = $order_data;
		}
		
		
		$where = ' and o.head_id = '.$community_info['id'];
		
		$where .= ' and o.order_status_id = 4 and order_id in ('.$order_data_str.') ';
		
		
		
		
		$sql = "select o.order_id,o.order_num_alias     
				from ".C('DB_PREFIX')."lionfish_comshop_order  as o , 
                ".C('DB_PREFIX')."lionfish_comshop_order_status  as os 
	                    where   o.order_status_id = os.order_status_id {$where}  
	                    order by o.date_added desc ";
	  
	    $list =  M()->query($sql);
		
		if( !empty($list) )
		{
			foreach($list as $val)
			{
				D('Home/Frontorder')->receive_order($val['order_id']);
				if($is_member_hexiao)
				{
					$pickup_member_record_data = array();
					$pickup_member_record_data['order_id'] = $val['order_id'];
					$pickup_member_record_data['order_sn'] = $val['order_num_alias'];
					$pickup_member_record_data['community_id'] = $community_info['id'];
					$pickup_member_record_data['member_id'] = $member_id;
					$pickup_member_record_data['addtime'] = time();
					
					M('lionfish_comshop_community_pickup_member_record')->add( $pickup_member_record_data );
				}
				
			}
			echo json_encode( array('code' => 0) );
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}
	   
		//load_model_class('frontorder')->receive_order($order_id);
		
		//string(15) "35,34,31,19,5,2"
		//string(32) "b55feabc517fa686f79c1bbd303cdeda"

		
	}
	
	function receive_order()
	{
		$gpc = I('request.');

		$token = $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		$member_id = $weprogram_token['member_id'];
		
		$order_id = $gpc['order_id'];

		if( empty($member_id) )
		{

			$result['code'] = 2;	

	        $result['msg'] = '登录失效';

	        echo json_encode($result);

	        die();

		}
	
		$order_info = M('lionfish_comshop_order')->where( array('order_id' => $order_id,'member_id' => $member_id) )->find();	
		

	    if(empty($order_info)){

			$result['code'] = 1;	

	        $result['msg'] = '非法操作,会员不存在该订单';

	        echo json_encode($result);

	        die();
	    }

		D('Home/Frontorder')->receive_order($order_id);

	    $result['code'] = 0;

	    echo json_encode($result);

	    die();

	}
	
	/**

		取消订单操作

	**/

	public function cancel_order()
	{
		
		$gpc = I('request.');

	    $token = $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		$member_id = $weprogram_token['member_id'];
		
		$order_id = $gpc['order_id'];

		if( empty($member_id) )

		{

			$result['code'] = 2;	

	        $result['msg'] = '登录失效';

	        echo json_encode($result);

	        die();

		}

			
		$order_info = M('lionfish_comshop_order')->where( array('member_id' => $member_id,'order_id' => $order_id) )->find();	
		

	    if(empty($order_info)){

			$result['code'] = 1;	

	        $result['msg'] = '非法操作,会员不存在该订单';

	        echo json_encode($result);

	        die();

	    }

		D('Home/Frontorder')->cancel_order($order_id);
		
	    $result['code'] = 0;

	    echo json_encode($result);

	    die();

	   

	}
	
	public function order_head_info()
	{
		$_GPC = I('request.');
		
		$token = $_GPC['token'];
		
		$is_share = $_GPC['is_share'];
	
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		$member_id = $weprogram_token['member_id'];
		
		
		$order_id = $_GPC['id'];
	    
		$order_info = M('lionfish_comshop_order')->where( array('order_id' => $order_id) )->find();	
		
		$pick_up_info = array();
		$pick_order_info = array();

		
		$community_info = D('Home/Front')->get_member_community_info($member_id);
		
		
		if($is_share){
			
			$userInfo = M('lionfish_comshop_member')->field('avatar')->where( array('member_id' => $order_info['member_id'] ) )->find();
			
			$order_info['avatar'] = $userInfo['avatar'];
		}
		
		if( $order_info['delivery'] == 'pickup' )
		{
			//查询自提点
			//$pick_order_info = M('lionfish_comshop_pick_order')->where( array('order_id' => $order_id) )->find();					
			
			//$pick_id = $pick_order_info['pick_id'];
						
			//$pick_up_info = M('lionfish_comshop_pick_up')->where( array('id' => $pick_id ) )->find();				
			
		}

		$order_status_info = M('lionfish_comshop_order_status')->where( array('order_status_id' => $order_info['order_status_id']) )->find();
		
	    //10 name
		if($order_info['order_status_id'] == 10)
		{
			$order_status_info['name'] = '等待退款';
		}
		else if($order_info['order_status_id'] == 4 && $order_info['delivery'] =='pickup')
		{
			//delivery 6
			$order_status_info['name'] = '待自提';
			//已自提
		}
		else if($order_info['order_status_id'] == 6 && $order_info['delivery'] =='pickup')
		{
			//delivery 6
			$order_status_info['name'] = '已自提';
			
		}
		else if($order_info['order_status_id'] == 1 && $order_info['type'] == 'lottery')
		{
			//等待开奖
			//一等奖
			if($order_info['lottery_win'] == 1)
			{
				$order_status_info['name'] = '一等奖';
			}else {
				$order_status_info['name'] = '等待开奖';
			}
		}
		
		//$order_info['type']
		//open_auto_delete
		
		if($order_info['order_status_id'] == 3)
		{
			$open_auto_delete = D('Home/Front')->get_config_by_name('open_auto_delete');
			
			$auto_cancle_order_time = D('Home/Front')->get_config_by_name('auto_cancle_order_time');
			
			$order_info['open_auto_delete'] = $open_auto_delete;
			//date_added
			if($open_auto_delete == 1)
			{
				$order_info['over_buy_time'] = $order_info['date_added'] + 3600 * $auto_cancle_order_time;
				$order_info['cur_time'] = time();
			}
		
		}
		
	  
	    
		$shipping_province = M('lionfish_comshop_area')->where( array('id' => $order_info['shipping_province_id'] ) )->find();
		
		$shipping_city = M('lionfish_comshop_area')->where( array('id' => $order_info['shipping_city_id'] ) )->find();
		
		$shipping_country = M('lionfish_comshop_area')->where( array('id' => $order_info['shipping_country_id']) )->find();
		
		$order_goods_list = M('lionfish_comshop_order_goods')->where( array('order_id' => $order_id ) )->select();
		
		$shiji_total_money = 0;
		$member_youhui = 0.00;
		
		
		$pick_up_time = "";
		$pick_up_type = -1;
		$pick_up_weekday = '';
		$today_time = $order_info['pay_time'];
		$arr = array('天','一','二','三','四','五','六');
	
	    foreach($order_goods_list as $key => $order_goods)
	    {
			
			$order_option_info = M('lionfish_comshop_order_option')->field('value')->where( array('order_goods_id' => $order_goods['order_goods_id'],'order_id' => $order_id) )->select();
			
			  
	        foreach($order_option_info as $option)
	        {
	            $order_goods['option_str'][] = $option['value'];
	        }
			if(empty($order_goods['option_str']))
			{
				//option_str
				 $order_goods['option_str'] = '';
			}else{
				 $order_goods['option_str'] = implode(',', $order_goods['option_str']);
			}
	       //
		    $order_goods['shipping_fare'] = round($order_goods['shipping_fare'],2);
		    $order_goods['price'] = round($order_goods['price'],2);
		    $order_goods['total'] = round($order_goods['total'],2);
		   
			if( $order_goods['is_vipcard_buy'] == 1 || $order_goods['is_level_buy'] ==1 )
			{
				$order_goods['price'] = round($order_goods['oldprice'],2);
			}
			$order_goods['real_total'] = round($order_goods['quantity'] * $order_goods['price'],2);
			
					
			$community_order_info = M('lionfish_community_head_commiss_order')->where( array('head_id' => $community_info['id'],'order_goods_id' => $order_goods['order_goods_id']) )->find();
			
			if(!empty($community_order_info))
			{
				$order_goods['commision'] = $community_order_info['money'];		
			}else{
				$order_goods['commision'] = 0;
			}
	        
			/**
					$goods_images = file_image_thumb_resize($vv['goods_images'],400);
					if(is_array($goods_images))
					{
						$vv['goods_images'] = $vv['goods_images'];
					}else{
						 $vv['goods_images']= tomedia( file_image_thumb_resize($vv['goods_images'],400) ); 
					}	
					
			**/
			$goods_images = $order_goods['goods_images'];
			
			if( !is_array($goods_images) )
			{
				 $order_goods['image']=  tomedia( $goods_images );
				$order_goods['goods_images']= tomedia( $goods_images ); 
			}else{
				 $order_goods['image']=  $order_goods['goods_images'];
			}
	       
		   $order_goods['hascomment'] = 0;
		   
			$order_goods_comment_info = M('lionfish_comshop_order_comment')->field('comment_id')->where( array('goods_id' => $order_goods['goods_id'],'order_id' => $order_id) )->find();
			
			if( !empty($order_goods_comment_info) )
			{
				$order_goods['hascomment'] = 1;
			}
			
			//ims_ 
							
			$goods_info = M('lionfish_comshop_goods')->field('productprice as price')->where( array('id' => $order_goods['goods_id']) )->find();				
		
			$goods_cm_info = M('lionfish_comshop_good_common')->field('pick_up_type,pick_up_modify')->where( array('goods_id' => $order_goods['goods_id']) )->find();
			
			if($pick_up_type == -1 || $goods_cm_info['pick_up_type'] > $pick_up_type)
			{
				$pick_up_type = $goods_cm_info['pick_up_type'];
				
				if($pick_up_type == 0)
				{
					$pick_up_time = date('m-d', $today_time);
					//$pick_up_weekday = '周'.$arr[date('w',$today_time)];
				}else if( $pick_up_type == 1 ){
					$pick_up_time = date('m-d', $today_time+86400);
					//$pick_up_weekday = '周'.$arr[date('w',$today_time+86400)];
				}else if( $pick_up_type == 2 )
				{
					$pick_up_time = date('m-d', $today_time+86400*2);
					//$pick_up_weekday = '周'.$arr[date('w',$today_time+86400*2)];
				}else if($pick_up_type == 3)
				{
					$pick_up_time = $goods_cm_info['pick_up_modify'];
				}
			}
			
			$order_goods['shop_price'] = $goods_info['price'];
			 
			
			$store_info	= array('s_true_name' =>'','s_logo' => '');
			
			$store_info['s_true_name'] = D('Home/Front')->get_config_by_name('shoname');
			
			if( !empty($store_info['s_logo']) )
			{
				$store_info['s_logo'] = tomedia($store_info['s_logo']);
			}else{
				$store_info['s_logo'] = '';
			}
			
			
			$order_goods['store_info'] = $store_info;
			
			unset($order_goods['model']);
			unset($order_goods['rela_goodsoption_valueid']);
			unset($order_goods['comment']);
			
	        $order_goods_list[$key] = $order_goods;
			$shiji_total_money += $order_goods['quantity'] * $order_goods['price'];
			
			$member_youhui += ($order_goods['real_total'] - $order_goods['total']);
	    }
	    
		unset($order_info['store_id']);
		unset($order_info['email']);
		unset($order_info['shipping_city_id']);
		unset($order_info['shipping_country_id']);
		unset($order_info['shipping_province_id']);
		//unset($order_info['comment']);
		unset($order_info['voucher_id']);
		unset($order_info['is_balance']);
		unset($order_info['lottery_win']);
		unset($order_info['ip']);
		unset($order_info['ip_region']);
		unset($order_info['user_agent']);
		
		
		
		$order_info['shipping_fare'] = round($order_info['shipping_fare'],2) < 0.01 ? '0.00':round($order_info['shipping_fare'],2) ;
		$order_info['voucher_credit'] = round($order_info['voucher_credit'],2) < 0.01 ? '0.00':round($order_info['voucher_credit'],2) ;
		$order_info['fullreduction_money'] = round($order_info['fullreduction_money'],2) < 0.01 ? '0.00':round($order_info['fullreduction_money'],2) ;
		
		
		$order_info['total'] = round($order_info['total'],2)< 0.01 ? '0.00':round($order_info['total']+$order_info['shipping_fare']-$order_info['voucher_credit']-$order_info['fullreduction_money'],2)	;
		
		if($order_info['total'] < 0)
		{
			$order_info['total'] = '0.00';
		}
		
		
		$order_info['total'] = round($order_info['total'],2)< 0.01 ? '0.00':round($order_info['total'],2)	;
		$order_info['real_total'] = round($shiji_total_money,2)+$order_info['shipping_fare'];		
		$order_info['price'] = round($order_info['price'],2);		
		$order_info['member_youhui'] = round($member_youhui,2) < 0.01 ? '0.00':round($member_youhui,2);	
		$order_info['pick_up_time'] = $pick_up_time;
		
			
		$order_info['shipping_fare'] = sprintf("%.2f",$order_info['shipping_fare']);
		$order_info['voucher_credit'] = sprintf("%.2f",$order_info['voucher_credit']);
		$order_info['fullreduction_money'] = sprintf("%.2f",$order_info['fullreduction_money']);
		$order_info['total'] = sprintf("%.2f",$order_info['total']);
		$order_info['real_total'] = sprintf("%.2f",$order_info['real_total']);
		
		
		
		$order_info['date_added'] = date('Y-m-d H:i:s', $order_info['date_added']);
		$need_data = array();
	
		if($order_info['delivery'] =='pickup')
		{
			
		
		}else{
			
		
		}
		
		if($order_info['type'] == 'integral')
		{
			//暂时屏蔽积分商城
			//$integral_order = M('integral_order')->field('score')->where( array('order_id' => $order_id) )->find();
			//$need_data['score'] = intval($integral_order['score']);
			
		}
		if( !empty($order_info['pay_time']) && $order_info['pay_time'] >0 )
		{
			$order_info['pay_date'] = date('Y-m-d H:i:s', $order_info['pay_time']);
		}else{
			$order_info['pay_date'] = '';
		}
		
		$order_info['express_tuanz_date'] = empty($order_info['express_tuanz_time']) ? '' : date('Y-m-d H:i:s', $order_info['express_tuanz_time']);
		$order_info['receive_date'] = date('Y-m-d H:i:s', $order_info['receive_time']);
		
		if($is_share==1){ $order_info['shipping_tel'] = substr_replace($order_info['shipping_tel'],'****',3,4); }
		
		$need_data['order_info'] = $order_info;
		$need_data['order_status_info'] = $order_status_info;
		$need_data['shipping_province'] = $shipping_province;
		$need_data['shipping_city'] = $shipping_city;
		$need_data['shipping_country'] = $shipping_country;
		$need_data['order_goods_list'] = $order_goods_list;
		
		$need_data['goods_count'] = count($order_goods_list);
		
		//$order_info['order_status_id'] 13  平台介入退款
		$order_refund_historylist = array();
		$pingtai_deal = 0;
		
		//判断是否已经平台处理完毕   
		
		$order_refund_historylist = M('lionfish_comshop_order_refund_history')->where( array('order_id' => $order_id) )->order('addtime asc')->select();
		
		foreach($order_refund_historylist as $key => $val)
		{
			if($val['type'] ==3)
			{
				$pingtai_deal = 1;
			}
		}
					
		$order_refund = M('lionfish_comshop_order_refund')->where( array('order_id' => $order_id) )->find();				
		
		if(!empty($order_refund))
		{
			$order_refund['addtime'] = date('Y-m-d H:i:s', $order_refund['addtime']);
		}
		
		$need_data['pick_up'] = $pick_up_info;
		
		if( empty($pick_order_info['qrcode']) && false)
		{
			
		}
		
		$need_data['pick_order_info'] = $pick_order_info;
		
		
		echo json_encode( array('code' => 0,'data' => $need_data,'pingtai_deal' => $pingtai_deal,'order_refund' => $order_refund ) );
		die();
	}
	


}