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

class GroupdoController extends CommonController {
	
	 protected function _initialize()
    {
		
    	parent::_initialize();
       
    }
	
	public function get_goods_detail() {
		
		$_GPC = I('request.');
		
		$id = $_GPC['id'];
		$pin_id = isset($_GPC['pin_id']) ? $_GPC['pin_id'] : 0;
		$token = $_GPC['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		$member_id = $weprogram_token['member_id'];
		
		$pintuan_model_buy = D('Home/Front')->get_config_by_name('pintuan_model_buy');
		
		if( empty($pintuan_model_buy) || $pintuan_model_buy ==0 )
		{
			$pintuan_model_buy = 0;
		}
		
		//团长休息
		$community_id = $_GPC['community_id'];
		$is_comunity_rest = D('Seller/Communityhead')->is_community_rest($community_id);
		
		
        $need_data = array();
		
        $sql = "select g.*,gd.content,gd.begin_time,gd.end_time,gd.video,gd.is_take_fullreduction,gd.share_title,gd.quality,gd.pick_up_type,gd.pick_up_modify,gd.one_limit_count,gd.total_limit_count,gd.seven,gd.repair,gd.labelname,gd.share_title,gd.goods_share_image,gd.relative_goods_list,gd.is_show_arrive,gd.diy_arrive_switch,gd.diy_arrive_details from " . C('DB_PREFIX'). "lionfish_comshop_goods as g," . C('DB_PREFIX') . "lionfish_comshop_good_common as gd 
				where g.id=gd.goods_id and g.id=" . $id;
        
		$goods_arr =  M()->query($sql);
		
		$goods = $goods_arr[0];
		
		$goods['goods_id'] = $id;
		
		
		$goods['is_take_fullreduction'] = 0;
		
	
		$goods['is_video'] = 0;
		$goods['video_size_width'] = 0;
		$goods['vedio_size_height'] = 0;
		$goods['video_src'] = '';

		if( !empty($goods['goods_share_image']) )
		{
			$goods['goods_share_image'] = tomedia($goods['goods_share_image']);
		}
		
		//video
		if( !empty($goods['video']) )
		{
			$goods['video'] = tomedia($goods['video']);
		}
		
        $goods['description'] = htmlspecialchars_decode($goods['content']);
        $qian = array(
            "\r\n"
        );
        $hou = array(
            "<br/>"
        );
        $goods['subtitle'] = str_replace($qian, $hou, $goods['subtitle']);
		
		$hou = array(
            "@EOF@"
        );
        $today_time = strtotime( date('Y-m-d').' 00:00:00' );
        //pick_up_type
      
		
		//gd.begin_time,gd.end_time,
		//over_type =0 未开始，over_type =2已结束，over_type =1距结束
		
		$now_time = time();	
			
		if($goods['begin_time'] > $now_time)
		{
			$goods['over_type'] = 0;
		}else if( $goods['begin_time'] <= $now_time &&  $goods['end_time'] > $now_time ){
			$goods['over_type'] = 1;
		}else if($goods['end_time'] < $now_time){
			$goods['over_type'] = 2;
			$goods['end_date'] = date('m/d H:i', $goods['end_time']);
		}		
		
		$goods['activity_summary'] = '';
		
		
		$onegood_image = D('Home/Pingoods')->get_goods_images($id);
		if( !empty($onegood_image) )
		{
			$goods['image_thumb'] = tomedia($onegood_image['image']);
			$goods['image'] = tomedia($onegood_image['image']);
		}
				
        $buy_record_arr = D('Home/Frontorder')->get_goods_buy_record($id,9);
		
       	$goods_image = D('Home/Pingoods')->get_goods_images($id, 10);
		
		
        if (isset($goods_image)) {
            foreach ($goods_image as $k => $v) {
               $goods_image[$k]['image'] = tomedia($v['image']);
            }
        }
		
        $goods['seller_count']+= $goods['sales'];
		
        $goods_price_arr = D('Home/Pingoods')->get_goods_price($id, $member_id);
		
		$goods['price'] = $goods_price_arr['price'];
		
		$goods['danprice'] = $goods_price_arr['danprice'];
		
        $price_dol = explode('.', $goods_price_arr['price']);
		
		$goods['price_front'] = $price_dol[0];
		$goods['price_after'] = $price_dol[1];
		
	
		$labelname_arr = unserialize( $goods['labelname'] );
		$tag_arr = array();
		
		if( !empty($labelname_arr) )
		{
			$goods['tag'] = $labelname_arr;
		}else{
			if( $goods['quality'] == 1)
			{
				$tag_arr[] = '正品保证';
			}
			if( $goods['seven'] == 1)
			{
				$tag_arr[] = '7天无理由退换';
			}
			if( $goods['repair'] == 1)
			{
				$tag_arr[] = '保修';
			}
			$goods['tag'] = $tag_arr;
			
		}
		
		
        $goods['fan_image'] = $goods['image'];
		
		$one_image = D('Home/Pingoods')->get_goods_images($id, 1);
		$goods['one_image'] = tomedia($one_image['image']);
        
		
        $pin_info = $goods_price_arr;
		
		$pin_goods_info = M('lionfish_comshop_good_pin')->where( array('goods_id' => $id ) )->find();
		  
		  
		$user_favgoods =  D('Home/Pingoods')->fav_goods_state($id, $member_id);
		
		if( !empty($user_favgoods) )
		{
			$goods['favgoods'] = 2;
		}else{
			$goods['favgoods'] = 1;
		}
		$price = $goods['danprice'];
		
       
		$lottery_info = array();
		
		$need_data['lottery_info'] = $lottery_info;

		if(empty($goods['share_title'])) $goods['share_title'] = $price.'元 '.$goods['goodsname'];
		
		
		/** 商品会员折扣begin **/
		$is_show_member_disc = 0;
		$member_disc = 100;
		
		/** 商品会员折扣end **/
		
		$goods['memberprice'] = sprintf('%.2f', round( ($goods['danprice'] * $member_disc) / 100 ,2));
		$max_get_dan_money = round( ($goods['danprice'] * (100 - $max_member_level['discount']) ) / 100 ,2);
		$max_get_money = $max_get_dan_money; 
		if(!empty($pin_info))
		{
			$pin_info['member_pin_price'] = sprintf('%.2f',round( ($pin_info['pin_price'] * $member_disc) / 100 ,2));
			$max_get_pin_money = round( ($pin_info['pin_price'] * (100 - $max_member_level['discount']) ) / 100 ,2);
			$max_get_money = $max_get_pin_money;
		}

		// 商品角标
		$label_id = unserialize($goods['labelname']);
		if($label_id){
			$label_info = D('Home/Pingoods')->get_goods_tags($label_id);
			if($label_info){
				if($label_info['type'] == 1){
					$label_info['tagcontent'] = tomedia($label_info['tagcontent']);
				} else {
					$label_info['len'] = mb_strlen($label_info['tagcontent'], 'utf-8');
				}
			}
			$goods['label_info'] = $label_info;
		}

		$pintuan_newman_notice = D('Home/Front')->get_config_by_name('pintuan_newman_notice');
		
		if( !empty($pintuan_newman_notice) )
		{
			$pintuan_newman_notice = htmlspecialchars_decode($pintuan_newman_notice);
			
			 $qian = array(
				"\r\n"
			);
			$hou = array(
				"<br/>"
			);
			$pintuan_newman_notice = str_replace($qian, $hou, $pintuan_newman_notice);
		}
		
		$pin_info['is_commiss_tuan'] = $pin_goods_info['is_commiss_tuan'];
	
		$pin_info['is_zero_open'] = $pin_goods_info['is_commiss_tuan'] == 1 ? $pin_goods_info['is_zero_open'] : 0;
		
		$pin_info['is_newman'] = $pin_goods_info['is_newman'];
		$pin_info['pintuan_newman_notice'] = $pintuan_newman_notice;
		$pin_info['commiss_type'] = $pin_goods_info['commiss_type'];
		$pin_info['commiss_money'] = $pin_goods_info['commiss_money'];
		
		
        $need_data['pin_info'] = $pin_info;
		
		/**
		if(!empty($member_id) && $member_id > 0 && $goods[0]['type'] == 'integral')
		{
			$member_info =  M('member')->field('score')->where( array('member_id' => $member_id) )->find();
			if($member_info['score'] < $goods[0]['score'])
			{
				$goods[0]['score_enough'] = 0;
			}else{
				$goods[0]['score_enough'] = 1;
			}
		}
		**/
		
		$need_data['member_level_info'] = $member_level_info;
		$need_data['member_level_list'] = $member_level_list;
		$need_data['max_member_level'] = $max_member_level;
		$need_data['max_get_money'] = sprintf('%.2f',$max_get_money);
		
		$need_data['max_get_pin_money'] = $max_get_pin_money;
		$need_data['max_get_dan_money'] = $max_get_dan_money;
		$need_data['buy_record_arr'] = $buy_record_arr;
		
		
		$need_data['is_show_max_level'] = $is_show_max_level;

		$goods['actPrice'] = explode('.', $goods['price']);
		$goods['marketPrice'] = explode('.', $goods['productprice']);
		 
		 
		 ///relative_goods_list member_id 
		$relative_goods_list = array();

		
		unset($goods['relative_goods_list']);
		
		$need_data['relative_goods_list'] = $relative_goods_list;
        $need_data['goods'] = $goods;
        $need_data['goods_image'] = $goods_image;
		
		$need_data['site_name'] = D('Home/Front')->get_config_by_name('shoname');
        $need_data['options'] = D('Home/Pingoods')->get_goods_options($id, $member_id);  // $goods_model->get_goods_options($id);
		 
		$order_comment_count = M('lionfish_comshop_order_comment')->where( array('state' => 1, 'goods_id' => $id ) )->count();		
		
		$comment_list = array();
		
		if($order_comment_count > 0)
		{
			
			$sql = "select o.*,m.username as name2,m.avatar as avatar2 from ".C('DB_PREFIX')."lionfish_comshop_order_comment as o left join ".C('DB_PREFIX')."lionfish_comshop_member as m 
			on o.member_id=m.member_id 
			where  o.state = 1  and o.goods_id = {$id} order by o.add_time desc limit 1";
			
			$comment_list=  M()->query($sql);
			
			$order_comment_images = array();
			
			foreach($comment_list as $key => $val)
			{
				//user_name
				
				if( empty($val['user_name']) )
				{
					$val['name'] = $val['name2'];
					$val['avatar'] = tomedia($val['avatar2']);
				}else{
					$val['name'] = $val['user_name'];
					$val['avatar'] = tomedia($val['avatar']);
				}
				
				if($val['type'] == 0)
				{
					
					$order_goods_info = M('lionfish_comshop_order_goods')->field('order_goods_id')->where( array('goods_id' => $id,'order_id' => $val['order_id'] ) )->find();
					
					
					$order_option_info = M('lionfish_comshop_order_option')->field('value')->where( array('order_goods_id' => $order_goods_info['order_goods_id'],'order_id' => $val['order_id'] ) )->select();
					
					$option_arr = array();
					foreach($order_option_info as $option)
					{
						$option_arr[] = $option['value'];
					}
					$option_str = implode(',', $option_arr);
				}else{
					$option_str = '';
				}
					
				$img_str = unserialize($val['images']);
				if( !empty($img_str) && $img_str != 'undefined' )
				{
					// $img_str = unserialize($val['images']);
					$img_list = explode(',', $img_str);
					$need_img_list = array();
					
					foreach($img_list as $kk => $vv)
					{
						if(!empty($vv) )
						{
							$vv =   tomedia($vv );
							$img_list[$kk] = $vv;
							$need_img_list[$kk] = $vv;
							if(count($order_comment_images) <= 4)
								$order_comment_images[] = $vv;
						}
					}
					$val['images'] = $need_img_list ;
				} else {
					$val['images'] = array();
				}
				$val['option_str'] = $option_str;
				$val['add_time'] = date('Y-m-d', $val['add_time']) ;
				$comment_list[$key] = $val;
			}
			//$this->comment_list = $comment_list;
			
		}
		
		$need_data['cur_time'] = time();
		$need_data['pin_id'] = $pin_id;

		$need_data['is_show_arrive'] = $goods['is_show_arrive'];
		$need_data['diy_arrive_switch'] = $goods['diy_arrive_switch'];
		$need_data['diy_arrive_details'] = $goods['diy_arrive_details'];

		//团长休息
		

		$goodsdetails_addcart_bg_color = D('Home/Front')->get_config_by_name('goodsdetails_addcart_bg_color');
		$goodsdetails_buy_bg_color = D('Home/Front')->get_config_by_name('goodsdetails_buy_bg_color');

		$is_close_details_time = D('Home/Front')->get_config_by_name('is_close_details_time');
		$pintuan_close_stranger = D('Home/Front')->get_config_by_name('pintuan_close_stranger');


		$isopen_community_group_share = D('Home/Front')->get_config_by_name('isopen_community_group_share');
		$group_share_info = '';
		
        echo json_encode(array(
            'code' => 1,
			'comment_list' => $comment_list,
			'order_comment_images' => $order_comment_images,
			'order_comment_count' => $order_comment_count,
			'data' => $need_data,
			'is_comunity_rest' => $is_comunity_rest,
			'open_man_orderbuy' => $open_man_orderbuy,
			'man_orderbuy_money' => $man_orderbuy_money,
			'goodsdetails_buy_bg_color' => $goodsdetails_buy_bg_color,
			'goodsdetails_addcart_bg_color' => $goodsdetails_addcart_bg_color,
			'isopen_community_group_share' => $isopen_community_group_share,
			'group_share_info' => $group_share_info,
			'is_close_details_time' => $is_close_details_time,
			'pintuan_model_buy' => $pintuan_model_buy,
			'pintuan_close_stranger' => $pintuan_close_stranger
        ));
        die();
    }
	
	public function get_goods_fujin_tuan()
	{
		$_GPC = I('request.');
		
		$pintuan_model_buy = D('Home/Front')->get_config_by_name('pintuan_model_buy');
		
		if( empty($pintuan_model_buy) )
		{
			$pintuan_model_buy = 0;
		}
		$head_id = isset($_GPC['head_id']) ? $_GPC['head_id']:0;
		
		
		
		$goods_id = $_GPC['goods_id'];
		$limit = isset($_GPC['limit']) ? $_GPC['limit']:8;
		
		$where = "";
		
		if( $pintuan_model_buy == 1 )
		{
			//o.order_id head_id
			$where .=  " and o.head_id = {$head_id} ";
		}
		
		$pintuan_stranger_zero = D('Home/Front')->get_config_by_name('pintuan_stranger_zero');
		
		if( !isset($pintuan_stranger_zero) || $pintuan_stranger_zero == 0 )
		{
			$where .= " and o.type !='ignore'  ";
		}
	
		
		$fujin_sql = "select distinct(p.pin_id) as pin_id,p.need_count,o.order_id,p.end_time,m.username,m.avatar  from ".C('DB_PREFIX')."lionfish_comshop_pin as p,".C('DB_PREFIX')."lionfish_comshop_order_goods as og,".C('DB_PREFIX')."lionfish_comshop_pin_order as po, 
		   ".C('DB_PREFIX')."lionfish_comshop_order as o,".C('DB_PREFIX')."lionfish_comshop_member as m  
			   where p.pin_id = po.pin_id and po.order_id = o.order_id  and og.order_id=o.order_id and o.member_id = m.member_id {$where} and o.order_status_id =2 and og.goods_id={$goods_id} and p.end_time>".time()."  group by po.pin_id order by p.end_time asc   limit {$limit}";
		
		$fujin_countsql = "select distinct(p.pin_id) as pin_id,p.need_count,o.order_id,p.end_time,m.username,m.avatar  from ".C('DB_PREFIX')."lionfish_comshop_pin as p,".C('DB_PREFIX')."lionfish_comshop_order_goods as og,".C('DB_PREFIX')."lionfish_comshop_pin_order as po, 
		   ".C('DB_PREFIX')."lionfish_comshop_order as o,".C('DB_PREFIX')."lionfish_comshop_member as m  
			   where p.pin_id = po.pin_id and po.order_id=o.order_id  and og.order_id=o.order_id  and o.member_id = m.member_id {$where} and o.order_status_id =2 and og.goods_id={$goods_id} and p.end_time>".time()." group by po.pin_id  order by p.end_time asc   ";
		
		$fujin_tuan_arr_count = M()->query($fujin_countsql );
		
		$fujin_tuan_count = count($fujin_tuan_arr_count);
		
		
		$fujin_tuan = M()->query($fujin_sql);
		
		$result = array();
		
		if(!empty($fujin_tuan))
		{
			foreach($fujin_tuan as $pintuan)
			{
				
			   $buy_count = $this->get_tuan_buy_count($pintuan['pin_id']);
			   $pintuan['buy_count'] =$buy_count;
			   $pintuan['cur_interface_time'] = time();
			   $pintuan['re_need_count'] = $pintuan['need_count'] - $buy_count;
			   //shipping_city_id
			 
			   //$pintuan['area_name'] = $area_info['area_name'];
			   $order_id = $pintuan['order_id'];
			   
			   
			   if($buy_count > 0)
			   {
				   //存在进行中的
				   $result[] = $pintuan;
			   }
			}
			
		}
			
			
		if( empty($result) )
		{
			echo json_encode(  array('code' => 1) );
			die();
		}else{
			echo json_encode( array('code' => 0, 'list' => $result, 'count' => $fujin_tuan_count) );
			die();
		}
		
	}
	
	
	/**
	 * 获取拼团已成功购买价数量
	 */
	public function get_tuan_buy_count($pin_id=0)
	{
		
	    $buy_count_sql =  "select count(o.order_id) as count  from ".C('DB_PREFIX')."lionfish_comshop_pin as p,".C('DB_PREFIX')."lionfish_comshop_pin_order as po," 
		.C('DB_PREFIX')."lionfish_comshop_order_goods as og,
		   ".C('DB_PREFIX')."lionfish_comshop_order as o
		       where p.pin_id = po.pin_id  and po.order_id=o.order_id and og.order_id = o.order_id and o.order_status_id =2 and p.pin_id={$pin_id} ";
	    
	    $count_tuan_arr = M()->query($buy_count_sql );
		
	    return $count_tuan_arr[0]['count'];
	}
	
	
	function group_info()
	{
		$_GPC = I('request.');
	
		$interface_get_time = time();

		$token = $_GPC['token'];

		$order_id = $_GPC['order_id'];
	
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		
		$member_id = $weprogram_token['member_id'];
		
		if( empty($member_id) )
		{
			$member_id = 0;
		}
		
	    $is_show = 0;

	    //获取拼团商品信息

		$order_goods = M('lionfish_comshop_order_goods')->field('name,goods_id,price,total,goods_images,quantity')->where( array('order_id' => $order_id ) )->find();

	    if(empty($order_goods))
	    {
			//未找到
			echo json_encode( array('code' =>1) );
			die();
	    }

		$order_goods['price'] = round($order_goods['price'],2);

		$order_goods['total'] = round($order_goods['total'],2);
		
		$order_info = M('lionfish_comshop_order')->field('member_id,type,head_id')->where( array('order_id' =>$order_id  ) )->find();			

		
		$good_image = D('Home/Pingoods')->get_goods_images($order_goods['goods_id']);
		
		if( !empty($good_image) )
		{
			//$order_goods['goods_images'] = tomedia($good_image['image']);
		}
				
		$goods_info = M('lionfish_comshop_goods')->field('goodsname as name,subtitle,productprice ,seller_count,sales as virtual_count,is_all_sale')->where( array('id' => $order_goods['goods_id'] ) )->find();			
		
		$goods_desc = M('lionfish_comshop_good_common')->field('share_title as share_group_title')->where( array('goods_id' => $order_goods['goods_id'] ) )->find();

		$goods_info['seller_count'] = $goods_info['seller_count'] + $goods_info['virtual_count'];
		
		if( !empty($good_image) )
		{
			$goods_info['goods_images'] = tomedia($good_image['image']);
		}

		unset($goods_info['virtual_count']);
		
		$pin_order = M('lionfish_comshop_pin_order')->where( array('order_id' => $order_id ) )->find();	

	    //获取拼团信息
		$pin_info = M('lionfish_comshop_pin')->where( array('pin_id' =>$pin_order['pin_id']  ) )->find();
		
		//拼团状态：0进行中， 1成功， 2 已经过期了
	    if($pin_info['state'] == 0 && $pin_info['end_time'] < time()){
	        $pin_info['state'] = 2;
	    }
		
		if( !empty($pin_info['success_time']) )
		{
			$pin_info['success_time'] = date('Y-m-d H:i:s', $pin_info['success_time']);
		}
		
		$goods_info['pin_count'] = $pin_info['need_count'];
		
		$price_arr = D('Home/Pingoods')->get_goods_price($order_goods['goods_id'], $member_id);
			
		$goods_info['pinprice'] = $price_arr['price'];
		$goods_info['danprice'] = $price_arr['danprice'];
		
		//ims_ 
		$tuanzhang_info = M('lionfish_comshop_member')->field('member_id,username,telephone,avatar')->where( array('member_id' => $pin_info['user_id'] ) )->find();
		

	    $pin_order_sql = "select po.add_time,m.member_id,m.username as name,m.telephone,m.avatar from ".C('DB_PREFIX')."lionfish_comshop_pin_order as po,".C('DB_PREFIX')."lionfish_comshop_order as o,

	                      ".C('DB_PREFIX')."lionfish_comshop_order_goods as og,".C('DB_PREFIX')."lionfish_comshop_member as m 

	                          where po.pin_id = ".$pin_info['pin_id']." and o.order_status_id in(1,2,4,6,7,8,9,10,11,14)

	                          and og.order_id = po.order_id and o.order_id = po.order_id and o.member_id= m.member_id order by po.add_time asc ";

	     
		$pin_order_arr = M()->query($pin_order_sql);
		
	    $me_take_in = 0;

	    foreach($pin_order_arr as $key =>$val)
	    {
	        if($member_id == $val['member_id'])
	        {
	            $me_take_in = 1;
	        }
	        $pin_order_arr[$key] = $val;
	    }

		
	    $is_me = 0;

	    if($order_info['member_id'] == $member_id)
	    {
	        $is_me = 1;

	    }
		
		$share_title = "不要错过~我".round($order_goods['price'],2)."元拼了".$goods_info['name'];

		if(!empty($goods_desc['share_group_title']) )
		{

			$share_title = $goods_desc['share_group_title'];

			$share_title = str_replace('{pin_price}',round($order_goods['price'],2),$share_title);

			$share_title = str_replace('{name}',$goods_info['name'],$share_title);

		}


	    /* 商品规格begin */

	    /* 商品规格end */

		unset( $tuanzhang_info['reg_type'] );

		unset( $tuanzhang_info['openid'] );

		unset( $tuanzhang_info['we_openid'] );

		unset( $tuanzhang_info['bindmobile'] );

		unset( $tuanzhang_info['uname'] );

		unset( $tuanzhang_info['email'] );

		unset( $tuanzhang_info['pwd'] );

		unset( $tuanzhang_info['address_id'] );

		unset( $tuanzhang_info['share_id'] );

		unset( $tuanzhang_info['comsiss_flag'] );

		unset( $tuanzhang_info['bind_seller_id'] );

		unset( $tuanzhang_info['bind_seller_pickup'] );

		unset( $tuanzhang_info['cart'] );

		unset( $tuanzhang_info['wishlist'] );

		unset( $tuanzhang_info['id_cardreal_name'] );

		unset( $tuanzhang_info['id_card'] );

		unset( $tuanzhang_info['login_count'] );

		unset( $tuanzhang_info['last_login_ip'] );

		unset( $tuanzhang_info['last_ip_region'] );

		unset( $tuanzhang_info['create_time'] );

		unset( $tuanzhang_info['last_login_time'] );

		unset( $tuanzhang_info['status'] );

		$options = D('Home/Pingoods')->get_goods_options($order_goods['goods_id']);


		$need_data = array();

		$need_data['is_me'] = $is_me;

		$need_data['goods_info'] = $goods_info;

		//$need_data['pin_goods'] = $pin_goods;

		//$need_data['pin_order'] = $pin_order;

		$need_data['me_take_in'] = $me_take_in;

		$need_data['share_title'] = $share_title;

		

		//$need_data['tuanzhang_info'] = $tuanzhang_info;

		$need_data['pin_order_arr'] = $pin_order_arr;

		$need_data['order_goods'] = $order_goods;

		$need_data['order_id'] = $order_id;
		
		$need_data['order_type'] = $order_info['type'];
		
		$need_data['community_id'] = $order_info['head_id'];

		$need_data['group_order_id'] = $group_order_id;

		$need_data['options'] = $options;

		$need_data['interface_get_time'] = $interface_get_time;
		$need_data['member_id'] = $member_id;

		$need_data['del_count'] = $pin_info['need_count'] - count($pin_order_arr);

		
		if( $need_data['del_count'] <= 0 && $pin_info['state'] != 2 )
		{
			$pin_info['state'] = 1;
		}
		
		$need_data['pin_info'] = $pin_info;	
		
		$pintuan_model_buy = D('Home/Front')->get_config_by_name('pintuan_model_buy');
		
		if( empty($pintuan_model_buy) || $pintuan_model_buy ==0 )
		{
			$pintuan_model_buy = 0;
		}
	
		$need_data['pintuan_model_buy'] = $pintuan_model_buy;
		
		$hide_community_change_btn = D('Home/Front')->get_config_by_name('hide_community_change_btn');
		
		$need_data['hide_community_change_btn'] = $hide_community_change_btn;
		
		
		$pintuan_show_community_info = D('Home/Front')->get_config_by_name('pintuan_show_community_info');
		$need_data['pintuan_show_community_info'] = $pintuan_show_community_info;

		

	    echo json_encode( array('code' =>0, 'data' => $need_data) );

		die();

	}
	
	/***
		获取会员佣金团的收益账户金额
	**/
	
	public function get_pincommiss_account_info()
	{
		$_GPC = I('request.');
		
		$token =  $_GPC['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		$member_id = $weprogram_token['member_id'];
		
		if( empty($member_id) )
		{
			$member_id = 0;
		}
		
		$member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id ) )->find();
		
		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1,'msg' => '您未登录') );
			die();
		}
		
		//commission_account($member_id)
		
		D('Home/Pin')->commission_account($member_id);
		
		$pintuan_commiss = M('lionfish_comshop_pintuan_commiss')->where( array('member_id' => $member_id ) )->find();
		
		//可提现金额 money
		
		//待结算金额   ims_lionfish_comshop_pintuan_commiss_order
		$wait_statements_money = M('lionfish_comshop_pintuan_commiss_order')->where( array('member_id' =>$member_id,'state' => 0 ) )->sum('money');	
		
		//已结算金额 has_statements_money
		$has_statements_money = M('lionfish_comshop_pintuan_commiss_order')->where( array('member_id' =>$member_id,'state' => 1 ) )->sum('money');
		
		//已提现金额 getmoney
	
		 
		 $pintuan_commiss['wait_statements_money'] = empty($wait_statements_money) ? 0:$wait_statements_money;//待结算金额 
		 $pintuan_commiss['has_statements_money'] = empty($has_statements_money) ? 0 :$has_statements_money;//已结算金额 
		 
		 
		 echo json_encode( array('code' => 0, 'data' => $pintuan_commiss ) );
		 die();
		
	}
	
	/**
		获取佣金订单列表
	**/
	public function listorder_list()
	{
		$_GPC = I('request.');
		
		$token =  $_GPC['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		$member_id = $weprogram_token['member_id'];
		
		if( empty($member_id) )
		{
			$member_id = 0;
		}
		
		$member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id ) )->find();
		
		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1, 'msg' => '会员不存在') );
			die();
		}
		
		
		$per_page = 6;

	    $page =  isset($_GPC['page']) ? $_GPC['page']:1;

	    $offset = ($page - 1) * $per_page;

	    $list = array();

		$where = '';

		$state = isset($_GPC['state']) ? $_GPC['state']: -1;

		//state

		if($state >=0)
		{
			$where .= ' and mco.state = '.$state;
		}


		$sql = 'select mco.money,mco.addtime,mco.statement_time,mco.state,o.order_id,o.order_num_alias,o.order_status_id,
				o.order_num_alias,o.total,o.pay_time,o.date_added,og.goods_id,og.quantity,og.name,og.price,og.goods_images,og.order_goods_id,mco.store_id,m.username as uname from  '
				.C('DB_PREFIX')."lionfish_comshop_pintuan_commiss_order as mco , ".C('DB_PREFIX')."lionfish_comshop_order_goods as og, 
				".C('DB_PREFIX')."lionfish_comshop_order as o  , 
				".C('DB_PREFIX')."lionfish_comshop_member as m  
			where    mco.order_id=og.order_id and mco.order_id = o.order_id and mco.order_goods_id=og.order_goods_id and m.member_id=o.member_id and mco.member_id=".$member_id." {$where} order by mco.id desc limit {$offset},{$per_page}";

		$list = M()->query($sql );
		
		$status_arr = D('Seller/Order')->get_order_status_name();


		foreach($list as $key =>$val)
		{
			
			$val['total'] = round($val['total'],2);

			$val['money'] = round($val['money'],2);

			$val['status_name'] = $status_arr[$val['order_status_id']];

			//$val['addtime'] = date('Y-m-d H:i:s', $val['addtime']);
			
			unset($val['addtime']);
			
			$val['pay_time'] = date('Y-m-d H:i:s', $val['pay_time']);
			$val['date_added'] = date('Y-m-d H:i:s', $val['date_added']);

			
			if( !empty($val['goods_images']))
			{
				
				if(is_array($goods_images))
				{
					$val['goods_images'] = $val['goods_images'];
				}else{
					 $val['goods_images']= tomedia( $val['goods_images'] ); 
				}	
				
			}else{
				 $val['goods_images']= ''; 
			}
			
			$order_option_list = M('lionfish_comshop_order_option')->where( array('order_goods_id' => $val['order_goods_id'] ) )->select();			
							
	        foreach($order_option_list as $option)
			{
				$val['option_str'][] = $option['value'];
			}
			if( !isset($val['option_str']) )
			{
				$val['option_str'] = '';
			}else{
				$val['option_str'] = implode(',', $val['option_str']);
			}    
			
			if( $val['state'] == 1 )
			{
				$val['statement_time'] = date('Y-m-d H:i:s', $val['statement_time']);
			}
			
			$pin_order = M('lionfish_comshop_pin_order')->where( array('order_id' => $val['order_id'] ) )->find();
			
			$pin_info = M('lionfish_comshop_pin')->field('need_count')->where( array('pin_id' => $pin_order['pin_id'] ) )->find();
			
			$val['pin_count'] = $pin_info['need_count'];
			

			$list[$key] = $val;

		}


		if(empty($list))

		{

			echo json_encode( array('code' => 1) );

			die();

		}else {

			echo json_encode( array('code' => 0, 'data' => $list) );

			die();

		}
		
		
	}
	
	
	/**
		获取会员拼团佣金基础数据
	**/
	public function get_commission_info()
	{
		$_GPC = I('request.');
		
		$token =  $_GPC['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		$member_id = $weprogram_token['member_id'];
		
		if( empty($member_id) )
		{
			$member_id = 0;
		}
		
		$member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id ) )->find();
		
		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1, 'msg' => '会员不存在') );
			die();
		}
		
		
		//最小提现金额
		$commiss_min_tixian_money = D('Home/Front')->get_config_by_name('pintuan_min_tixian_money');
		
		if( empty($commiss_min_tixian_money) )
		{
			$commiss_min_tixian_money = 0;
		}
		
		$commiss_tixian_bili = D('Home/Front')->get_config_by_name('pintuan_tixian_bili');
		
		if( empty($commiss_tixian_bili) )
		{
			$commiss_tixian_bili = 0;
		}
		
		$member_commiss = M('lionfish_comshop_pintuan_commiss')->where( array('member_id' => $member_id ) )->find();
		
		$member_commiss['commiss_min_tixian_money'] = $commiss_min_tixian_money;//最小提现金额， 0标识不限制
		
		$member_commiss['commiss_tixian_bili'] = $commiss_tixian_bili;
		
		$member_commiss['total_commiss_money'] = $member_commiss['money'] + $member_commiss['dongmoney'] + $member_commiss['getmoney'];
		
		$commiss_tixianway_yuer  = D('Home/Front')->get_config_by_name('pintuan_tixianway_yuer'); 
		
		$commiss_tixianway_weixin  = D('Home/Front')->get_config_by_name('pintuan_tixianway_weixin'); 
		$commiss_tixianway_alipay  = D('Home/Front')->get_config_by_name('pintuan_tixianway_alipay'); 
		$commiss_tixianway_bank  	= D('Home/Front')->get_config_by_name('pintuan_tixianway_bank');  
		
		
		$member_commiss['commiss_tixianway_yuer'] = empty($commiss_tixianway_yuer) ? 1 : ($commiss_tixianway_yuer == 2 ? 1:0);
		$member_commiss['commiss_tixianway_weixin'] = empty($commiss_tixianway_weixin) ? 1 : ($commiss_tixianway_weixin == 2 ? 1:0);
		$member_commiss['commiss_tixianway_alipay'] = empty($commiss_tixianway_alipay) ? 1 : ($commiss_tixianway_alipay == 2 ? 1:0);
		$member_commiss['commiss_tixianway_bank'] = empty($commiss_tixianway_bank) ? 1 : ($commiss_tixianway_bank == 2 ? 1:0);
		
		
		
		
		//上一微信真实姓名
		$last_weixin_realname = "";
		
		$last_weixin_info = M('lionfish_comshop_pintuan_tixian_order')->where( array('member_id' => $member_id, 'type' => 2 ) )->find();
		
		if( !empty($last_weixin_info) )
		{
			$last_weixin_realname = $last_weixin_info['bankusername'];
		}
		
		//上一支付宝账号
		$last_alipay_name = '';
		$last_alipay_account = '';
		
		$last_alipay_info = M('lionfish_comshop_pintuan_tixian_order')->where( array('member_id' => $member_id, 'type' => 3 ) )->find();
		
		if( !empty($last_alipay_info) )
		{
			$last_alipay_name = $last_alipay_info['bankusername'];
			$last_alipay_account = $last_alipay_info['bankaccount'];
		}
		
		//上一银行卡信息
		$last_bank_bankname = '';
		$last_bank_account = '';
		$last_bank_name = '';
		
		$last_bank_info = M('lionfish_comshop_pintuan_tixian_order')->where( array('member_id' => $member_id, 'type' => 4 ) )->find();
		
		if( !empty($last_bank_info) )
		{
			$last_bank_bankname = $last_bank_info['bankname'];
			$last_bank_account = $last_bank_info['bankaccount'];
			$last_bank_name = $last_bank_info['bankusername'];
		}
		
		$member_commiss['last_weixin_realname'] = $last_weixin_realname;
		$member_commiss['last_alipay_name'] = $last_alipay_name;
		$member_commiss['last_alipay_account'] = $last_alipay_account;
		
		$member_commiss['last_bank_bankname'] = $last_bank_bankname;
		$member_commiss['last_bank_account'] = $last_bank_account;
		$member_commiss['last_bank_name'] = $last_bank_name;
		
		$commiss_tixian_publish = D('Home/Front')->get_config_by_name('pintuan_tixian_publish'); 
		
		$member_commiss['commiss_tixian_publish'] = htmlspecialchars_decode( $commiss_tixian_publish );
		
		$member_commiss['total_money'] = sprintf('%.2f', $member_commiss['money'] + $member_commiss['dongmoney'] + $member_commiss['getmoney']);
		
		echo json_encode( array('code' =>0,'data' => $member_commiss) );

		die();

	}
	
	/**
		会员拼团佣金提现 提交接口
	**/
	public function tixian_sub()
	{
		$_GPC = I('request.');
		
		$token =  $_GPC['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		$member_id = $weprogram_token['member_id'];
		
		if( empty($member_id) )
		{
			$member_id = 0;
		}
		
		$member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id ) )->find();
		
		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1, 'msg' => '会员不存在') );
			die();
		}
		
		$result = array('code' => 1,'msg' => '提现失败');

		$member_commiss = M('lionfish_comshop_pintuan_commiss')->where( array('member_id' => $member_id ) )->find();
		
		$datas = array();
		
		
		$datas['money'] = $_GPC['money'];

		$money = $datas['money'];//I('post.money',0,'floatval');
		
		
		$type = $_GPC['type'];// 1余额 2 微信 3 支付宝 4 银行
		
		$bankname = isset($_GPC['bankname']) ? $_GPC['bankname'] : ''; //银行名称
		
		$bankaccount = isset($_GPC['bankaccount']) ? $_GPC['bankaccount'] : '';//卡号，支付宝账号 使用该字段
		
		$bankusername = isset($_GPC['bankusername']) ? $_GPC['bankusername'] : '';//持卡人姓名，微信名称，支付宝名称， 使用该字段
		
		$commiss_money_limit =  D('Home/Front')->get_config_by_name('pintuan_min_tixian_money');
		
		if(!empty($commiss_money_limit) && $commiss_money_limit >0)
		{

			if($member_commiss['money'] < $commiss_money_limit)

			{

				$result['msg'] = '佣金满'.$commiss_money_limit.'才能提现';

				echo json_encode($result);

				die();

			}

		}

		if($money > 0 && $money <= $member_commiss['money'])

		{

			//判断提现手续费，+ 判断提现金额免审直接到账
			$service_charge = D('Home/Front')->get_config_by_name('pintuan_tixian_bili');
			
			$data = array();

			$data['member_id'] = $member_id;
			
			$data['money'] = $money;
			$data['service_charge'] = $service_charge;
			$data['service_charge_money'] = round( ($money * $service_charge) /100 ,2);

			$data['state'] = 0;

			$data['shentime'] = 0;
			
			$data['type'] = $type;
			$data['bankname'] = $bankname;
			$data['bankaccount'] = $bankaccount;
			$data['bankusername'] = $bankusername;

			$data['addtime'] = time();

			M('lionfish_comshop_pintuan_tixian_order')->add($data);


			

			$com_arr = array();

			$com_arr['money'] = $member_commiss['money'] - $money;

			$com_arr['dongmoney'] = $member_commiss['dongmoney'] + $money;

			M('lionfish_comshop_pintuan_commiss')->where( array('member_id' => $member_id ) )->setInc('money',-$money);
			M('lionfish_comshop_pintuan_commiss')->where( array('member_id' => $member_id ) )->setInc('dongmoney',$money);
			
			

			$result['code'] = 0;
			//commiss_tixian_reviewed 0 , 1
			$commiss_tixian_reviewed = D('Home/Front')->get_config_by_name('pintuan_tixian_reviewed');
			
			if(empty($commiss_tixian_reviewed) || $commiss_tixian_reviewed == 0)
			{
				//手动
			} else if( !empty($commiss_tixian_reviewed) && $commiss_tixian_reviewed == 1 ){
				//自动
			}

		} 

		echo json_encode($result);

		die();

	}
	
	
	/**

		提现记录

	**/

	public function tixian_record()
	{
		$_GPC = I('request.');
		
		$token =  $_GPC['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		$member_id = $weprogram_token['member_id'];
		
		if( empty($member_id) )
		{
			$member_id = 0;
		}
		
		$member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id ) )->find();

		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1, 'msg' => '会员不存在') );
			die();
		}
		
		$per_page = 10;

		$page =  isset($_GPC['page']) ? $_GPC['page']:1;

		
		$offset = ($page - 1) * $per_page;

		

		$list = array();

		$list = M('lionfish_comshop_pintuan_tixian_order')->where( array('member_id' =>$member_id ) )->order('addtime desc')->limit($offset,$per_page )->select();
		
		
		foreach($list as $key => $val)

		{

			$val['addtime'] = date('Y-m-d H:i', $val['addtime']);

			$list[$key] = $val;

		}

		

		if( !empty($list) )

		{

			echo json_encode( array('code' =>0, 'data'=>$list) );

			die();

		}else{

			echo json_encode( array('code' => 1) );

			die();

		}

	}
	
	
}