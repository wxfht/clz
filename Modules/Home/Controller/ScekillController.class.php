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

class ScekillController extends CommonController {
	
	 protected function _initialize()
    {
		
    	parent::_initialize();
       
    }
	
	
	public function get_scekill_info()
	{
		//秒杀设置begin
		$seckill_is_open 		= D('Home/Front')->get_config_by_name('seckill_is_open');
		$seckill_is_show_index 	= D('Home/Front')->get_config_by_name('seckill_is_show_index');
		$scekill_show_time 	= D('Home/Front')->get_config_by_name('scekill_show_time');
		
		if( empty($seckill_is_open) )
		{
			$seckill_is_open = 0;
		}
		if( empty($seckill_is_show_index) )
		{
			$seckill_is_show_index = 0;
		}
		
		$scekill_time_arr = array();
		
		if( isset($scekill_show_time) && !empty($scekill_show_time) )
		{
			$scekill_show_time_arr = unserialize($scekill_show_time);
			
			foreach($scekill_show_time_arr as $vv)
			{
				if( $vv != 25 && $vv >=date('H'))//htong 限制整点秒杀 && date('H')<=$vv
				{
					$scekill_time_arr[] = $vv;
				}
			}
		}
		
		//秒杀主页标题 
		$seckill_page_title = D('Home/Front')->get_config_by_name('seckill_page_title');
		
		if( empty($seckill_page_title) )
		{
			$seckill_page_title = '商品秒杀';
		}
		
		//首页秒杀活动背景色 
		$seckill_bg_color = D('Home/Front')->get_config_by_name('seckill_bg_color');
		
		//秒杀活动分享标题
		$seckill_share_title = D('Home/Front')->get_config_by_name('seckill_share_title');
		
		$seckill_share_image = D('Home/Front')->get_config_by_name('seckill_share_image');
		
		if( !empty($seckill_share_image) )
		{
			$seckill_share_image = tomedia( $seckill_share_image );
		}
		
		//整点秒杀结束
		
		$need_data = array();
		$need_data['seckill_is_open'] = $seckill_is_open;
		$need_data['seckill_is_show_index'] = $seckill_is_show_index;
		$need_data['scekill_time_arr'] = $scekill_time_arr;
		$need_data['seckill_page_title'] = $seckill_page_title;
		$need_data['seckill_bg_color'] = $seckill_bg_color;
		$need_data['seckill_share_title'] = $seckill_share_title;
		$need_data['seckill_share_image'] = $seckill_share_image;
		
		
		echo json_encode( array('code' => 0 ,'data' => $need_data ) );
		die();
		
	}
	
	
	public function load_gps_goodslist()
	{
		$_GPC = I('request.');
		
		$head_id = $_GPC['head_id'];
		if($head_id == 'undefined') $head_id = '';

		$pageNum = $_GPC['pageNum'];
		$gid = $_GPC['gid'];
		$keyword = $_GPC['keyword'];
		
		$is_random = isset($_GPC['is_random']) ? $_GPC['is_random'] : 0;
		$per_page = isset($_GPC['per_page']) ? $_GPC['per_page'] : 10;
		
		$cate_info = '';
		if($gid == 'undefined' || $gid =='')
		{
			$gid = 0;
		} else {
			
			$cate_info = M('lionfish_comshop_goods_category')->field('banner,name')->where( array('id' => $gid ) )->find();		
						
			if(!empty($cate_info['banner'])) $cate_info['banner'] = tomedia($cate_info['banner']);
		}
		if(!$keyword){
			$gids = D('Home/GoodsCategory')->get_index_goods_category($gid);
			$gidArr = array();
			$gidArr[] = $gid;
			foreach ($gids as $key => $val) {
				$gidArr[] = $val['id'];
			}
			$gid = implode(',', $gidArr);
		}
		$offset = ($pageNum - 1) * $per_page;
		$limit = "{$offset},{$per_page}";
		
		$token =  $_GPC['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token ) )->find();
		
		$is_member_level_buy = 0;
		
		$is_vip_card_member = 0;
		$is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');
		$is_open_vipcard_buy = !empty($is_open_vipcard_buy) && $is_open_vipcard_buy ==1 ? 1:0; 
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
		//	echo json_encode( array('code' => 2) );
		//	die();
		}else{
			$member_id = $weprogram_token['member_id'];
			
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
				
				if($is_vip_card_member != 1 && $member_info['level_id'] >0 )
				{
					$is_member_level_buy = 1;
				}
				
			}
		}
	    
	    
	    $now_time = time();
	    
	    $where = " g.grounding =1 and  g.type ='normal'   ";
		//head_id
		
		if( !empty($head_id) && $head_id >0 )
		{
			
			if(!empty($keyword)) {
				$sql_goods_ids = "select pg.goods_id from ".C('DB_PREFIX')."lionfish_community_head_goods as pg,"
	                        .C('DB_PREFIX')."lionfish_comshop_goods as g  
	        	           where pg.goods_id = g.id and g.goodsname like '%{$keyword}%' and pg.head_id = {$head_id}  order by pg.id desc ";
			
				$goods_ids_arr = M()->query($sql_goods_ids);
			} else {
				if($gid == 0){
					$goods_ids_arr = M()->query('SELECT goods_id FROM ' . C('DB_PREFIX'). "lionfish_community_head_goods                 
						WHERE  head_id=".$head_id."  order by id desc " );
				} else {
					$sql_goods_ids = "select pg.goods_id from ".C('DB_PREFIX')."lionfish_community_head_goods as pg,"
	                        .C('DB_PREFIX')."lionfish_comshop_goods_to_category as g  
	        	           where  pg.goods_id = g.goods_id  and g.cate_id in ({$gid}) and pg.head_id = {$head_id} order by pg.id desc ";
			
					$goods_ids_arr = M()->query($sql_goods_ids);
				}
			}
		
	    
			$ids_arr = array();
			foreach($goods_ids_arr as $val){
				$ids_arr[] = $val['goods_id'];
			}

			if(!empty($keyword)) {
				$goods_ids_nolimit_arr = M()->query('SELECT id FROM ' . C('DB_PREFIX'). "lionfish_comshop_goods                 
					WHERE  is_all_sale=1 and goodsname like '%{$keyword}%' " );
			} else {
				if($gid == 0){
					$goods_ids_nolimit_arr = M()->query('SELECT id FROM ' . C('DB_PREFIX') . "lionfish_comshop_goods                 
					WHERE is_all_sale=1  " );
				} else {
					$goods_ids_nolimit_sql = "select pg.id from ".C('DB_PREFIX')."lionfish_comshop_goods as pg,"
	                        .C('DB_PREFIX')."lionfish_comshop_goods_to_category as g  
	        	           where pg.id = g.goods_id and g.cate_id in ({$gid}) and pg.is_all_sale=1 ";
			
					$goods_ids_nolimit_arr = M()->query($goods_ids_nolimit_sql);
				}
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
			if(!empty($keyword)) {
				$goods_ids_nolimit_arr = M()->query('SELECT id FROM ' . C('DB_PREFIX'). "lionfish_comshop_goods                 
					WHERE  goodsname like '%{$keyword}%' and type='normal' " );
			} else {
				if($gid == 0){
					$goods_ids_nohead_arr = M()->query('SELECT id FROM ' . C('DB_PREFIX'). "lionfish_comshop_goods                 
					WHERE  type='normal' " );
				} else {
					$goods_ids_nohead_sql = "select pg.id from ".C('DB_PREFIX')."lionfish_comshop_goods as pg,"
	                        .C('DB_PREFIX')."lionfish_comshop_goods_to_category as g where pg.id = g.goods_id and g.cate_id in ({$gid}) and type='normal' ";
					$goods_ids_nohead_arr = M()->query($goods_ids_nohead_sql);
				}
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
		
		if($gid == 0 && $keyword == ''){
			$where .= " and g.is_index_show = 1 and gc.begin_time <={$now_time} and gc.end_time > {$now_time} ";
			// $where .= " and gc.begin_time <={$now_time} and gc.end_time > {$now_time} ";
		} else {
			$where .= " and gc.begin_time <={$now_time} and gc.end_time > {$now_time} ";
		}

		$where .= " and gc.is_new_buy=0 and gc.is_spike_buy = 0 ";
		
		 //$is_random $order='g.istop DESC, g.settoptime DESC,g.index_sort desc,g.id desc '
		 
		if($is_random == 1)
		{
			$community_goods = D('Home/Pingoods')->get_community_index_goods('g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction,gc.labelname,gc.video ', $where,$offset,$per_page,' rand() ');
		}else{
			$community_goods = D('Home/Pingoods')->get_community_index_goods('g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction,gc.labelname,gc.video ', $where,$offset,$per_page);
		}
		
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
				
				$tmp_data['levelprice'] = $price_arr['levelprice']; // 会员等级价格
				$tmp_data['is_mb_level_buy'] = $price_arr['is_mb_level_buy']; //是否 会员等级 可享受
				
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

			echo json_encode(array('code' => 0, 'list' => $list ,'is_vip_card_member' => $is_vip_card_member,'is_member_level_buy' => $is_member_level_buy ,'copy_text_arr' => $copy_text_arr, 'cur_time' => time() ,'full_reducemoney' => $full_reducemoney,'full_money' => $full_money,'is_open_vipcard_buy' => $is_open_vipcard_buy,'is_open_fullreduction' => $is_open_fullreduction,'is_show_list_timer'=>$is_show_list_timer, 'cate_info' => $cate_info, 'is_show_cate_tabbar'=>$is_show_cate_tabbar ));
			die();
		}else{
			$is_show_cate_tabbar = D('Home/Front')->get_config_by_name('is_show_cate_tabbar');
			echo json_encode( array('code' => 1, 'cate_info' => $cate_info, 'is_show_cate_tabbar'=>$is_show_cate_tabbar) );
			die();
		}
		
	}
	
}