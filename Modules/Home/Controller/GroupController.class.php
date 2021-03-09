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

class GroupController extends CommonController {
	
	 protected function _initialize()
    {
		
    	parent::_initialize();
       
    }
	
	public function pintuan_slides()
	{
		$_GPC = I('request.');
		
		// 活动商品展示方式  pintuan_show_type  0横向布局 1左右布局	
		$pintuan_show_type_info= D('Home/Front')->get_config_by_name('pintuan_show_type');
		
		if( empty($pintuan_show_type_info) )
		{
			$pintuan_show_type_info = 0;
		}
			
		//拼团页面浮窗  hide_pintuan_page_windows  0开启 1关闭
		$hide_pintuan_page_windows_info= D('Home/Front')->get_config_by_name('hide_pintuan_page_windows' );
		if( empty($hide_pintuan_page_windows_info) )
		{
			$hide_pintuan_page_windows_info = 0;
		}
			
			
		//拼团规则介绍  pintuan_publish
		$pintuan_publish_info= D('Home/Front')->get_config_by_name('pintuan_publish');	
		$category_list =  D('Home/GoodsCategory')->get_index_goods_category(0,'pintuan');
		
		
		$params = array();
	    $params[':uniacid'] = $uniacid;
	    $params[':type'] = 'pintuan';
	    $params[':enabled'] = 1;		

		//拼团幻灯片	
		$slider_list = M()->query('SELECT * FROM ' . C('DB_PREFIX'). "lionfish_comshop_adv                 
			WHERE  type='pintuan' and enabled=1 " . ' order by displayorder desc, id desc ');
	    
		
		
		if(!empty($slider_list))
		{
			foreach($slider_list as $key => $val)
			{
				$val['image'] = tomedia($val['thumb']);
				
				$slider_list[$key] = $val;
			}
		}else{
			$slider_list = array();
		}

		//tabbar开关
		//$open_tabbar_out_weapp = D('Home/Front')->get_config_by_name('open_tabbar_out_weapp');
		//$tabbar_out_type = D('Home/Front')->get_config_by_name('tabbar_out_type');
		
		//分享信息
		$pintuan_index_share_title = D('Home/Front')->get_config_by_name('pintuan_index_share_title');
		$pintuan_index_share_img = D('Home/Front')->get_config_by_name('pintuan_index_share_img');
		if(!empty($pintuan_index_share_img)){
			$pintuan_index_share_img = tomedia($pintuan_index_share_img);
		}
		
		
		echo json_encode(
			array(
				'code'=>0,
				'pintuan_show_type' => $pintuan_show_type_info,
				'category_list' => $category_list, 
				'hide_pintuan_page_windows' => $hide_pintuan_page_windows_info, 
				'pintuan_publish' => htmlspecialchars_decode($pintuan_publish_info), 
				'slider_list' => $slider_list,
				// 'open_tabbar_out_weapp' => $open_tabbar_out_weapp,
				// 'tabbar_out_type' => $tabbar_out_type,
				'pintuan_index_share_title' => $pintuan_index_share_title,
				'pintuan_index_share_img' => $pintuan_index_share_img
			)
		);
		
		die();	
	}
	
	//拼团商品首页
	public function get_pintuan_list()
	{
		$_GPC = I('request.');
		
		
		$head_id = $_GPC['head_id'];
		if($head_id == 'undefined') $head_id = '';
		
		
		$pintuan_model_buy = D('Home/Front')->get_config_by_name('pintuan_model_buy');
		
		if( empty($pintuan_model_buy) || $pintuan_model_buy ==0 )
		{
			$pintuan_model_buy = 0;
			$head_id = '';
		}
		
		
		
		
		$pageNum = isset($_GPC['pageNum']) ? intval($_GPC['pageNum']) : 1;
		$gid = $_GPC['gid'];
		$keyword = $_GPC['keyword'];
		
		$is_random = isset($_GPC['is_random']) ? $_GPC['is_random'] : 0;
		$is_index = isset($_GPC['is_index']) ? $_GPC['is_index'] : 0;
		$per_page = isset($_GPC['per_page']) ? $_GPC['per_page'] : 10;
		
		if($gid == 'undefined' || $gid =='')
		{
			$gid = 0;
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
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
		
		}else{
			$member_id = $weprogram_token['member_id'];
		}
	    
	    
	    $now_time = time();
	    
	    $where = " g.grounding =1 and g.type ='pin'  ";
		
		if( isset($is_index) && $is_index == 1 )
		{
			$where .= " and g.is_index_show = 1  ";
			$per_page = 20;
		}

		if( !empty($head_id) && $head_id >0 )
		{
			$params = array();
			$params['uniacid'] = $_W['uniacid'];
			$params['head_id'] = $head_id;

			
			if($gid == 0){
				$goods_ids_arr = M()->query('SELECT goods_id FROM ' .C('DB_PREFIX'). "lionfish_community_head_goods                 
					WHERE  head_id={$head_id}  order by id desc ");
			} else {
				$sql_goods_ids = "select pg.goods_id from ".C('DB_PREFIX')."lionfish_community_head_goods as pg, "
						.C('DB_PREFIX')."lionfish_comshop_goods_to_category as g  
					   where  pg.goods_id = g.goods_id  and g.cate_id in ({$gid}) and pg.head_id = {$head_id} order by pg.id desc ";
		
				$goods_ids_arr = M()->query($sql_goods_ids);
			}
			
		
	    
			$ids_arr = array();
			foreach($goods_ids_arr as $val){
				$ids_arr[] = $val['goods_id'];
			}

			if(!empty($keyword)) {
				$goods_ids_nolimit_arr = M()->query('SELECT id FROM ' . C('DB_PREFIX') . "lionfish_comshop_goods                 
					WHERE  is_all_sale=1 and goodsname like '%{$keyword}%' " );
			} else {
				if($gid == 0){
					$goods_ids_nolimit_arr = M()->query('SELECT id FROM ' . C('DB_PREFIX'). "lionfish_comshop_goods                 
					WHERE  is_all_sale=1  " );
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
			
			if($gid > 0){
				
				$goods_ids_nohead_sql = "select pg.id from ".C('DB_PREFIX')."lionfish_comshop_goods as pg,"
						.C('DB_PREFIX')."lionfish_comshop_goods_to_category as g where pg.id = g.goods_id and g.cate_id in ({$gid}) ";
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
			}
		}
		
		if($gid == 0 && $keyword == ''){
			$where .= "  and gc.begin_time <={$now_time} and gc.end_time > {$now_time} ";
		} else {
			$where .= " and gc.begin_time <={$now_time} and gc.end_time > {$now_time} ";
		}

		$where .= " and gc.is_new_buy=0 and gc.is_spike_buy = 0 ";
		
		 
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
			
			
			$is_open_fullreduction = 0;
			
			
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
				$tmp_data['danPrice'] =  $price_arr['danprice'];
				
				$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options($val['id'],$member_id);
				
				if( !empty($tmp_data['skuList']) )
				{
					$tmp_data['car_count'] = 0;
				}else{
					
					$car_count = 0;
					
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
				
				//根据商品信息查询拼团信息  $val['id'];
				
				$good_pin_list = M('lionfish_comshop_good_pin')->where( array('goods_id' => $val['id']  ) )->find();
				
				//拼团价格
				//$tmp_data['pinprice'] = $price ;
			
				//拼团人数
				$tmp_data['pin_count'] =$good_pin_list['pin_count'];
				//拼团时间
				$tmp_data['pin_hour'] =$good_pin_list['pin_hour'];
				//拼团开始时间
				$tmp_data['pin_begin_time'] =$good_pin_list['begin_time'];
				//拼团结束时间
				$tmp_data['pin_end_time'] =$good_pin_list['end_time'];
				
				
				$list[] = $tmp_data;
			}

			$is_show_list_timer = D('Home/Front')->get_config_by_name('is_show_list_timer');

			$pintuan_index_coming_img = D('Home/Front')->get_config_by_name('pintuan_index_coming_img');
			$pintuan_index_show = D('Home/Front')->get_config_by_name('pintuan_index_show');
			if( !isset($pintuan_index_show) )
			{
				$pintuan_index_show = 0;
			}
			
			if( isset($pintuan_index_coming_img) && !empty($pintuan_index_coming_img) )
			{
				$pintuan_index_coming_img = tomedia($pintuan_index_coming_img);
			}
			
			
			//pintuan_index_coming_img
			echo json_encode(array('code' => 0,'pintuan_model_buy' => $pintuan_model_buy,'pintuan_index_show' => $pintuan_index_show,'pintuan_index_coming_img' => $pintuan_index_coming_img, 'list' => $list ,'copy_text_arr' => $copy_text_arr, 'cur_time' => time() ,'full_reducemoney' => $full_reducemoney,'full_money' => $full_money,'is_open_fullreduction' => $is_open_fullreduction,'is_show_list_timer'=>$is_show_list_timer ));
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}
		
		
	}
	
	
	//猜你喜欢
	public function pintuan_like_list()
	{
		$_GPC = I('request.');

		
		//猜你喜欢开关
		$is_show_order_guess_like= D('Home/Front')->get_config_by_name('is_open_pipntuan_like' );
		if( empty($is_show_order_guess_like) )
		{
			$is_show_order_guess_like = 0;
		}

		
		$community_id = $_GPC['community_id'];
		//community_id
		
		
		//购买的商品id
		$order_list = M('lionfish_comshop_order_goods')->field('goods_id')->where( array('order_id' =>$_GPC["order_id"] ) )->find();
		
		$now_time = time();
		
		
		$pintuan_model_buy  = D('Home/Front')->get_config_by_name('pintuan_model_buy');
		
		if( empty($pintuan_model_buy) )
		{
			$pintuan_model_buy = 1;
		}

		if(!empty($community_id) && $pintuan_model_buy == 1){
			//有社区
			$head_info = M('lionfish_community_head')->field('id')->where( array('id' => $community_id ) )->find();
			
			//团长商品和全部可售
			//lionfish_community_head_goods
					
			$head_goods = M('lionfish_community_head_goods')->field('goods_id')->where( array('head_id' => $head_info['id'] ) )->select();			
			
			foreach ($head_goods as $hg) {
				$hg = join(",",$hg);
				$temp_array[] = $hg;
			}
				
			$all_goods = M('lionfish_comshop_goods')->field('id')->where( array('type' => 'pin', 'is_all_sale' => 1) )->select();		
			
			if( !empty($all_goods) )
			{
				foreach( $all_goods as $vv )
				{
					$temp_array[] = $vv['id'];
				}
			}
			
			//团长商品id
			 $goods_id_list = implode(",", $temp_array);
			 
			$sql_likegoods = "select g.*,gc.end_time,gc.begin_time from ".C('DB_PREFIX')."lionfish_comshop_goods as g,".C('DB_PREFIX')."lionfish_comshop_good_common as gc 
							  where g.id = gc.goods_id and gc.begin_time <={$now_time} and gc.end_time > {$now_time}  and g.grounding =1 and (  g.id in (".$goods_id_list.") and g.id <> ".$order_list['goods_id']." ) and g.type = 'pin'  order by rand() limit 6";
		}else{
			//猜你喜欢  随机6条数据
			$sql_likegoods = "select g.*,gc.end_time,gc.begin_time from ".C('DB_PREFIX')."lionfish_comshop_goods as g,".C('DB_PREFIX')."lionfish_comshop_good_common as gc   
        	           where  gc.begin_time <={$now_time} and gc.end_time > {$now_time}  and g.id=gc.goods_id and g.id <> ".$order_list['goods_id']." and g.grounding =1 and g.type = 'pin' order by rand() limit 6";
			
		}
		
		$likegoods_list = M()->query($sql_likegoods);
		
		$list = array();
		
		if( !empty($likegoods_list) )
		{
			
			foreach($likegoods_list as $val)
			{
				$tmp_data = array();
				$tmp_data['actId'] = $val['id'];
				$tmp_data['spuName'] = $val['goodsname'];
				
				$tmp_data['spuCanBuyNum'] = $val['total'];
				$tmp_data['spuDescribe'] = $val['subtitle'];
				$tmp_data['end_time'] = $val['end_time'];
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
				$tmp_data['danPrice'] =  $price_arr['danprice'];
				
				$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options($val['id'],$member_id);
				
				if( !empty($tmp_data['skuList']) )
				{
					$tmp_data['car_count'] = 0;
				}else{
					
					$car_count = 0;
					
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
				
				//根据商品信息查询拼团信息
				$good_pin_list = M('lionfish_comshop_good_pin')->where( array('goods_id' => $val['id'] ) )->find();
				
				//拼团价格
			
				//拼团人数
				$tmp_data['pin_count'] =$good_pin_list['pin_count'];
				//拼团时间
				$tmp_data['pin_hour'] =$good_pin_list['pin_hour'];
				//拼团开始时间
				$tmp_data['pin_begin_time'] =$good_pin_list['begin_time'];
				//拼团结束时间
				$tmp_data['pin_end_time'] =$good_pin_list['end_time'];
				
				
				$list[] = $tmp_data;
			}
				
		}
		
		echo json_encode(array('code'=>0,
				'is_show_order_guess_like' => $is_show_order_guess_like,
				'list' => $list,
				)
		);
		die();
		
	}
	
}