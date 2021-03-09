<?php
/**
 * lionfish 商城系统
 *
 * ==========================================================================
 * @link      http://www.liofis.com/
 * @copyright Copyright (c) 2015 liofis.com. 
 * @license   http://www.liofis.com/license.html License
 * ==========================================================================
 * 拼团模块
 * @author    fish
 *
 */
namespace Home\Controller;

class VipcardController extends CommonController {
	
	 protected function _initialize()
    {
		
    	parent::_initialize();
       
    }
	
	
	public function get_vipcard_baseinfo()
	{
		$_GPC = I('request.');
		
		$token =  $_GPC['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		
		$member_id = $weprogram_token['member_id'];
		
		
		if( empty($member_id) )
		{
			echo json_encode( array('code' =>1,'msg' =>'未登录') );
			die();
		}
		
	    $member_id = $weprogram_token['member_id'];
		
		
		$member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id ) )->find();
		
		
		$vipcard_unopen_headbg = D('Home/Front')->get_config_by_name('vipcard_unopen_headbg');
		$vipcard_effect_headbg = D('Home/Front')->get_config_by_name('vipcard_effect_headbg');
		$vipcard_afterefect_headbg = D('Home/Front')->get_config_by_name('vipcard_afterefect_headbg');
		
		if(!empty($vipcard_unopen_headbg)) $vipcard_unopen_headbg = tomedia($vipcard_unopen_headbg);
		if(!empty($vipcard_effect_headbg)) $vipcard_effect_headbg = tomedia($vipcard_effect_headbg);
		if(!empty($vipcard_afterefect_headbg)) $vipcard_afterefect_headbg = tomedia($vipcard_afterefect_headbg);

		
		$vipcard_buy_pagenotice = D('Home/Front')->get_config_by_name('vipcard_buy_pagenotice');
		$vipcard_equity_notice = D('Home/Front')->get_config_by_name('vipcard_equity_notice');
		
		
		$vipcard_buy_pagenotice = htmlspecialchars_decode($vipcard_buy_pagenotice);
		$vipcard_equity_notice = htmlspecialchars_decode($vipcard_equity_notice);
		
		$card_list = M('lionfish_comshop_member_card')->select();
		
		$card_equity_list = M('lionfish_comshop_member_card_equity')->select();
		
		$result = array();
		
		//判断是否开启了 会员卡 is_open_vipcard_buy
		$is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');
		$modify_vipcard_name = D('Home/Front')->get_config_by_name('modify_vipcard_name');
		
		$is_hide_vipcard_vipgoods = D('Home/Front')->get_config_by_name('is_hide_vipcard_vipgoods');
		
		$is_show_vipgoods = empty($is_hide_vipcard_vipgoods) || $is_hide_vipcard_vipgoods == 0 ? 1 : 0;
		
		
		$modify_vipcard_name = empty($modify_vipcard_name) ? '天机会员': $modify_vipcard_name;
		
		$result['is_open_vipcard_buy'] = $is_open_vipcard_buy;
		$result['modify_vipcard_name'] = $modify_vipcard_name;
		
		$result['is_vip_card_member'] = 0;
		
		if( !empty($is_open_vipcard_buy) && $is_open_vipcard_buy == 1 )
		{
			
			$now_time = time();
			
			if( $member_info['card_id'] >0 && $member_info['card_end_time'] > $now_time )
			{
				$result['is_vip_card_member'] = 1;//还有会员
				
				$del_day = ceil( ($member_info['card_end_time'] - $now_time) / 86400 ) ;
				
				$result['del_vip_day'] = $del_day;
				
			}else if( $member_info['card_id'] >0 && $member_info['card_end_time'] < $now_time ){
				$result['is_vip_card_member'] = 2;//已过期
			}
		}
		
		if( !empty($card_equity_list) )
		{
			foreach($card_equity_list as $key => $val)
			{
				$val['image'] = tomedia($val['image'] );
				$card_equity_list[$key] = $val;
			}
		}

		$member_info['card_end_time'] = date('Y-m-d', $member_info['card_end_time']);
		
		$result['vipcard_unopen_headbg'] =  $vipcard_unopen_headbg;
		$result['vipcard_effect_headbg'] =  $vipcard_effect_headbg;
		$result['vipcard_afterefect_headbg'] =  $vipcard_afterefect_headbg;
		$result['vipcard_buy_pagenotice'] =  $vipcard_buy_pagenotice;
		$result['vipcard_equity_notice'] =  $vipcard_equity_notice;
		$result['card_list'] =  $card_list;
		$result['card_equity_list'] =  $card_equity_list;
		$result['member_info'] =  $member_info;
		
		$result['is_show_vipgoods'] =  $is_show_vipgoods;//是否显示会员卡商品
		
		$category_list = M('lionfish_comshop_goods_category')->where( "cate_type='normal' and is_show=1 and pid=0" )->order('sort_order desc,id asc')->select();
		
		foreach( $category_list as &$val )
		{
			unset($val['uniacid']);
			unset($val['pid']);
			unset($val['is_hot']);
			unset($val['logo']);
			unset($val['banner']);
			unset($val['cate_type']);
			unset($val['sort_order']);
			unset($val['is_show_topic']);
		}
		
		$result['category_list'] =  $category_list;
		
		echo json_encode( array('code' => 0, 'data' => $result) );
		die();
		
	}
	
	
	public function get_vipgoods_list()
	{
		$_GPC = I('request.');
		
		$head_id = $_GPC['head_id'];
		
		if($head_id == 'undefined') $head_id = '';

		$pageNum = isset($_GPC['pageNum']) && $_GPC['pageNum'] > 0 ? $_GPC['pageNum'] : 1 ;
		$gid = $_GPC['gid'];
		$keyword = '';
		
		$is_random = 0;
		$per_page = isset($_GPC['per_page']) ? $_GPC['per_page'] : 10;
		
		$cate_info = '';
		
		if($gid > 0){
			
			$sub_cate_list = M('lionfish_comshop_goods_category')->where( "is_show=1 and cate_type='normal' and pid = {$gid}" )->order('sort_order desc, id desc')->select();
			
			$gidArr = array();
			$gidArr[] = $gid;
			foreach ($sub_cate_list as $key => $val) {
				$gidArr[] = $val['id'];
			}
			$gid = implode(',', $gidArr);
		}
		$offset = ($pageNum - 1) * $per_page;
		$limit = "{$offset},{$per_page}";
		
		$token =  $_GPC['token'];
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		$is_vip_card_member = 0;
		$is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');
		$is_open_vipcard_buy = !empty($is_open_vipcard_buy) && $is_open_vipcard_buy ==1 ? 1:0; 
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			
		}else{
			$member_id = $weprogram_token['member_id'];
			
			$is_vip_card_member = 0;
		
			//member_id
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
			}
		}
	    
		
		$now_time = time();
	    
	    $where = " g.is_take_vipcard =1 and  g.grounding =1 and  g.type ='normal'   ";
		//head_id
		
		if( !empty($head_id) && $head_id >0 )
		{
			
			if($gid == 0){
				
				$goods_ids_arr = M('lionfish_community_head_goods')->field('goods_id')->where( array('head_id' => $head_id ) )->select();
				
			} else { 
				$sql_goods_ids = "select pg.goods_id from ".C('DB_PREFIX')."lionfish_community_head_goods as pg,"
						.C('DB_PREFIX')."lionfish_comshop_goods_to_category as g  
					   where  pg.goods_id = g.goods_id  and g.cate_id in ({$gid}) and pg.head_id = {$head_id}  order by pg.id desc ";
		
				$goods_ids_arr = M()->query($sql_goods_ids);
			}
			
	    
			$ids_arr = array();
			foreach($goods_ids_arr as $val){
				$ids_arr[] = $val['goods_id'];
			}

			
			if($gid == 0){
				
				$goods_ids_nolimit_arr = M('lionfish_comshop_goods')->field('id')->where( array('is_all_sale' => 1) )->select();
				
			} else {
				$goods_ids_nolimit_sql = "select pg.id from ".C('DB_PREFIX')."lionfish_comshop_goods as pg,"
						.C('DB_PREFIX')."lionfish_comshop_goods_to_category as g  
					   where pg.id = g.goods_id and g.cate_id in ({$gid}) and pg.is_all_sale=1 ";
		
				$goods_ids_nolimit_arr = M()->query($goods_ids_nolimit_sql);
			}
			
			
			if( !empty($goods_ids_nolimit_arr) )
			{
				foreach($goods_ids_nolimit_arr as $val){
					$ids_arr[] = $val['id'];
				}
			}
			
			
			$ids_str = implode(',',$ids_arr);
			
			if( !empty($ids_str) )
			{
				$where .= "  and g.id in ({$ids_str})";
			} else{
				$where .= " and 0 ";
			}
		}else{
			//echo json_encode( array('code' => 1) );
			//	die();
			
			if($gid == 0){
				
				$goods_ids_nohead_arr = M('lionfish_comshop_goods')->field('id')->where( array('type' => 'normal' ) )->select();
				
			} else {
				$goods_ids_nohead_sql = "select pg.id from ".C('DB_PREFIX')."lionfish_comshop_goods as pg,"
						.C('DB_PREFIX')."lionfish_comshop_goods_to_category as g where pg.id = g.goods_id and g.cate_id in ({$gid}) and type='normal' ";
				$goods_ids_nohead_arr = M()->query($goods_ids_nohead_sql);
			}
			

			$ids_arr = array();
			if( !empty($goods_ids_nohead_arr) )
			{
				foreach($goods_ids_nohead_arr as $val){
					$ids_arr[] = $val['id'];
				}
			}
			
			$ids_str = implode(',',$ids_arr);
			
			if( !empty($ids_str) )
			{
				$where .= "  and g.id in ({$ids_str})";
			} else{
				$where .= " and 0 ";
			}
		}
		
		
		$where .= " and gc.begin_time <={$now_time} and gc.end_time > {$now_time} ";
		

		$where .= " and gc.is_new_buy=0 and gc.is_spike_buy = 0 ";
		
		$community_goods = D('Home/Pingoods')->get_community_index_goods('g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction,gc.labelname,gc.video ', $where,$offset,$per_page);
		
		if( !empty($community_goods) )
		{
			$is_open_fullreduction = D('Home/Front')->get_config_by_name('is_open_fullreduction');
			$full_money = D('Home/Front')->get_config_by_name('full_money');
			$full_reducemoney = D('Home/Front')->get_config_by_name('full_reducemoney');
			
			$is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');
			
			$is_open_vipcard_buy = !empty($is_open_vipcard_buy) && $is_open_vipcard_buy == 1 ? 1:0;
			
			if(empty($full_reducemoney) || $full_reducemoney <= 0)
			{
				$is_open_fullreduction = 0;
			}
			
			$cart= D('Home/Car');
			
			$list = array();
			$copy_text_arr = array();
			foreach($community_goods as $val)
			{
				$tmp_data = array();
				$tmp_data['actId'] = $val['id'];
				$tmp_data['spuName'] = $val['goodsname'];
				
				$tmp_data['spuCanBuyNum'] = $val['total'];
				$tmp_data['spuDescribe'] = $val['subtitle'];
				$tmp_data['end_time'] = $val['end_time'];
				$tmp_data['is_take_vipcard'] = $val['is_take_vipcard'];
				$tmp_data['soldNum'] = $val['seller_count'] + $val['sales'];
				
				$productprice = $val['productprice'];
				$tmp_data['marketPrice'] = explode('.', $productprice);

				if( !empty($val['big_img']) )
				{
					$tmp_data['bigImg'] = tomedia($val['big_img']);
				}
				
				$good_image = D('Home/Pingoods')->get_goods_images($val['id']);
				if( !empty($good_image) )
				{
					$tmp_data['skuImage'] = tomedia($good_image['image']);
				}
				$price_arr = D('Home/Pingoods')->get_goods_price($val['id'], $member_id);
				$price = $price_arr['price'];
				
				if( $pageNum == 1 )
				{
					$copy_text_arr[] = array('goods_name' => $val['goodsname'], 'price' => $price);
				}
				
				$tmp_data['actPrice'] = explode('.', $price);
				$tmp_data['card_price'] = $price_arr['card_price'];
				
				//card_price
				
				$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options($val['id'],$member_id);
				
				if( !empty($tmp_data['skuList']) )
				{
					$tmp_data['car_count'] = 0;
				}else{
						
					$car_count = $cart->get_wecart_goods($val['id'],"",$head_id ,$token);
					
					if( empty($car_count)  )
					{
						$tmp_data['car_count'] = 0;
					}else{
						$tmp_data['car_count'] = $car_count;
					}
					
					
				}
				
				if($is_open_fullreduction == 0)
				{
					$tmp_data['is_take_fullreduction'] = 0;
				}else if($is_open_fullreduction == 1){
					$tmp_data['is_take_fullreduction'] = $val['is_take_fullreduction'];
				}

				// 商品角标
				$label_id = unserialize($val['labelname']);
				if($label_id){
					$label_info = D('Home/Pingoods')->get_goods_tags($label_id);
					if($label_info){
						if($label_info['type'] == 1){
							$label_info['tagcontent'] = tomedia($label_info['tagcontent']);
						} else {
							$label_info['len'] = mb_strlen($label_info['tagcontent'], 'utf-8');
						}
					}
					$tmp_data['label_info'] = $label_info;
				}

				$tmp_data['is_video'] = empty($val['video']) ? false : true;
				
				$list[] = $tmp_data;
			}

			$is_show_list_timer = D('Home/Front')->get_config_by_name('is_show_list_timer');
			$is_show_cate_tabbar = D('Home/Front')->get_config_by_name('is_show_cate_tabbar');

			echo json_encode(array('code' => 0, 'list' => $list ,'is_vip_card_member' => $is_vip_card_member,'copy_text_arr' => $copy_text_arr, 'cur_time' => time() ,'full_reducemoney' => $full_reducemoney,'full_money' => $full_money,'is_open_vipcard_buy' => $is_open_vipcard_buy,'is_open_fullreduction' => $is_open_fullreduction,'is_show_list_timer'=>$is_show_list_timer, 'cate_info' => $cate_info, 'is_show_cate_tabbar'=>$is_show_cate_tabbar ));
			die();
		}else{
			$is_show_cate_tabbar = D('Home/Front')->get_config_by_name('is_show_cate_tabbar');
			echo json_encode( array('code' => 1, 'cate_info' => $cate_info, 'is_show_cate_tabbar'=>$is_show_cate_tabbar) );
			die();
		}
		
		
	}
	
	/**
		微信购买会员卡
	**/
	public function wxcharge()
	{
		$_GPC = I('request.');
		
		$token = $_GPC['token'];
	
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		$member_id = $weprogram_token['member_id'];
		
		if( empty($member_id) )
		{
			echo json_encode( array('code' =>1,'msg' =>'未登录') );
			die();
		}
		
		$rech_id = isset($_GPC['rech_id']) && $_GPC['rech_id'] > 0 ? $_GPC['rech_id'] : 0;
		
		$card_info = M('lionfish_comshop_member_card')->where( array('id' => $rech_id ) )->find();
		
		if( empty($card_info) )
		{
			echo json_encode( array('code' =>1,'msg' =>'无此会员卡') );
			die();
		}
		
		$money = $card_info['price'];
		
		$member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id ) )->find();
		
		$now_time = time();
			
		$order_type = 1;
		
		if( $member_info['card_id'] >0 && $member_info['card_end_time'] > $now_time )
		{
			$order_type = 2;
		}else if( $member_info['card_id'] >0 && $member_info['card_end_time'] < $now_time ){
			$order_type = 3;
		}
		
		$member_charge_flow_data = array();
		
		$member_charge_flow_data['order_sn'] = build_order_no($member_id);
		$member_charge_flow_data['member_id'] = $member_id;
		$member_charge_flow_data['pay_type'] = 'weixin';
		$member_charge_flow_data['state'] = 0;
		$member_charge_flow_data['car_id'] = $card_info['id'];
		$member_charge_flow_data['expire_day'] = $card_info['expire_day'];
		$member_charge_flow_data['price'] = $card_info['price'];
		
		$member_charge_flow_data['order_type'] = $order_type; // 1,2,3
		
		$member_charge_flow_data['begin_time'] = 0;
		$member_charge_flow_data['end_time'] = 0;
		$member_charge_flow_data['pay_time'] = 0;
		
		$member_charge_flow_data['addtime'] = time();
		
		$order_id = M('lionfish_comshop_member_card_order')->add( $member_charge_flow_data );
		$shop_domain = D('Home/Front')->get_config_by_name('shop_domain');
		
		$fee = $money;
		$appid =  D('Home/Front')->get_config_by_name('wepro_appid');
		$body =         '会员卡购买';
		$mch_id =      D('Home/Front')->get_config_by_name('wepro_partnerid');
		$nonce_str =    nonce_str();
		$notify_url =   $shop_domain.'/notify.php';
		$openid =       $member_info['we_openid'];
		$out_trade_no = $order_id.'-'.time().'-buycard-'.$rech_id;
		$spbill_create_ip = $_SERVER['REMOTE_ADDR'];
		$total_fee =    $fee*100;
		$trade_type = 'JSAPI';
		$pay_key = D('Home/Front')->get_config_by_name('wepro_key');
		
		
		$post['appid'] = $appid;
		$post['body'] = $body;
		$post['mch_id'] = $mch_id;
		$post['nonce_str'] = $nonce_str;
		$post['notify_url'] = $notify_url;
		$post['openid'] = $openid;
		$post['out_trade_no'] = $out_trade_no;
		$post['spbill_create_ip'] = $spbill_create_ip;
		$post['total_fee'] = $total_fee;
		$post['trade_type'] = $trade_type;
		
		$sign = sign($post,$pay_key);
		
		//sign()
		$post_xml = '<xml>
			   <appid>'.$appid.'</appid>
			   <body>'.$body.'</body>
			   <mch_id>'.$mch_id.'</mch_id>
			   <nonce_str>'.$nonce_str.'</nonce_str>
			   <notify_url>'.$notify_url.'</notify_url>
			   <openid>'.$openid.'</openid>
			   <out_trade_no>'.$out_trade_no.'</out_trade_no>
			   <spbill_create_ip>'.$spbill_create_ip.'</spbill_create_ip>
			   <total_fee>'.$total_fee.'</total_fee>
			   <trade_type>'.$trade_type.'</trade_type>
			   <sign>'.$sign.'</sign>
			</xml> ';
		$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
		$xml = http_request($url,$post_xml);
		$array = xml($xml);
		if($array['RETURN_CODE'] == 'SUCCESS' && $array['RESULT_CODE'] == 'SUCCESS'){
			$time = time();
			$tmp= array();
			$tmp['appId'] = $appid;
			$tmp['nonceStr'] = $nonce_str;
			$tmp['package'] = 'prepay_id='.$array['PREPAY_ID'];
			$tmp['signType'] = 'MD5';
			$tmp['timeStamp'] = "$time";
			
			
			M('lionfish_comshop_member_card_order')->where( array('id' => $order_id) )->save( array('formid' => $array['PREPAY_ID'] ) );
				
			
			$data['code'] = 0;
			$data['timeStamp'] = "$time";
			$data['nonceStr'] = $nonce_str;
			$data['signType'] = 'MD5';
			$data['package'] = 'prepay_id='.$array['PREPAY_ID'];
			$data['paySign'] =   sign($tmp, $pay_key);
			$data['out_trade_no'] = $out_trade_no;
			
			$data['redirect_url'] = '../dan/me';
			
		}else{
			$data['code'] = 1;
			$data['text'] = "错误";
			$data['RETURN_CODE'] = $array['RETURN_CODE'];
			$data['RETURN_MSG'] = $array['RETURN_MSG'];
		}
		
		
		
		echo json_encode($data);
		die();
		
	}
	
}