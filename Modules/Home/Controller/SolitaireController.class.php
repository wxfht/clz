<?php
/**
 * lionfish 商城系统
 *
 * ==========================================================================
 * @link      http://www.liofis.com/
 * @copyright Copyright (c) 2015 liofis.com. 
 * @license   http://www.liofis.com/license.html License
 * ==========================================================================
 * 群接龙模块
 * @author    fish
 *
 */
namespace Home\Controller;

class SolitaireController extends CommonController {
	
	 protected function _initialize()
    {
		
    	parent::_initialize();
       
    }
	
	/**
		获取团长群接龙的列表,分页
	**/
	public function get_head_solitairelist()
	{
		$_GPC = I('request.');

		$token = $_GPC['token'];
		
	    $weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token ) )->find();

	    $member_id = $weprogram_token['member_id'];
		
		$member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id ) )->find();
		
		if( empty($member_info) )
		{
			// 未登录
			echo json_encode( array('code' => 2) );
			die();
		}
		
		$community_info = D('Home/Front')->get_member_community_info($member_id);
		
		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 3 , 'msg' => '团长不存在' ) );
			die();	
		}
		$head_id = $community_info['id'];

	    
		$page = isset($_GPC['page']) ? $_GPC['page']:'1';
	
		$size = isset($_GPC['size']) ? $_GPC['size']:'20';
		$offset = ($page - 1)* $size;
		// 
		
		$where = " and head_id = {$head_id} ";
		
		$keyword = isset($_GPC['keyword']) ? addslashes($_GPC['keyword']) : '';
		
		if( !empty($keyword) )
		{
			$where .= "  and solitaire_name like '%{$keyword}%' ";
		}
		
		$sql = "select *  from ".C('DB_PREFIX')."lionfish_comshop_solitaire   
						where  1  {$where}  
						  order by id desc limit {$offset},{$size}";
		
		$list =  M()->query($sql);
		
		if( !empty( $list ) )
		{
			$need_data = array();
			
			$now_time = time();
			
			foreach( $list as $key => $val )
			{
				$tmp_arr = array();
				$tmp_arr['id'] = $val['id'];
				$tmp_arr['solitaire_name'] = $val['solitaire_name'];
				$tmp_arr['begin_time'] = date('Y-m-d', $val['begin_time']);
				
				$tmp_arr['state'] = $val['state'];
				$tmp_arr['appstate'] = $val['appstate'];
				
				$tmp_arr['state_str'] = '';
				
				if( $val['appstate'] == 0 )
				{
					$tmp_arr['state_str'] = '等待平台审核';
				}else if( $val['appstate'] == 1 ){
					
					if( $val['state'] == 0 )
					{
						$tmp_arr['state_str'] = '已禁用';
					}else if( $val['end'] == 1 ){
						$tmp_arr['state_str'] = '已终止';
					}else if( $val['state'] == 1 ){
						if( $val['begin_time'] > $now_time )
						{
							$tmp_arr['state_str'] = '未开始';
						}else if( $val['begin_time'] < $now_time && $val['end_time'] > $now_time  )
						{
							$tmp_arr['state_str'] = '进行中';
						}else if( $val['end_time'] < $now_time ){
							$tmp_arr['state_str'] = '已结束';
						}
					}
				}else if( $val['appstate'] == 2 ){
					$tmp_arr['state_str'] = '平台拒绝';
				}
				
				//几人看过 //几人参加
				$order_count = M('lionfish_comshop_solitaire_order')->where( array('soli_id' => $val['id'] ) )->count();
				
				$tmp_arr['order_count'] = $order_count;
						  
				$invite_count = M('lionfish_comshop_solitaire_invite')->where( array('soli_id' => $val['id'] ) )->count();			  
				
				$tmp_arr['invite_count'] = $invite_count;
				
				$need_data[$key] = $tmp_arr;
			}
			
			echo json_encode( array('code' => 0, 'data' => $need_data ) );
			die();
			
		}else{
			
			echo json_encode( array('code' => 1) );
			die();
		}
			
		
	}
	
	/**
		获取团长基本信息
	**/
	public function get_solitaire_headinfo()
	{
		$_GPC = I('request.');

		$token = $_GPC['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token ) )->find();

	    $member_id = $weprogram_token['member_id'];
		
		$member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id ) )->find();
		
		if( empty($member_info) )
		{
			// 未登录
			echo json_encode( array('code' => 1) );
			die();
		}
		
		$community_info = D('Home/Front')->get_member_community_info($member_id);
		
		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 3 , 'msg' => '团长不存在' ) );
			die();	
		}
		
		$community_info['avatar'] = $member_info['avatar'];
		
		
		$need_data = array();
		
		$need_data['community_name'] = $community_info['community_name'];//绑定小区
		$need_data['head_name'] = $community_info['head_name'];//团长昵称
		$need_data['avatar'] = $community_info['avatar'];//头像
		
		echo json_encode( array('code' =>0, 'data' => $need_data ) );
		die();
		
	}
	
	/***
		搜索团长可售 商品
	**/
	public function search_head_goodslist()
	{
		$_GPC = I('request.');

		$token = $_GPC['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token ) )->find();

	    $member_id = $weprogram_token['member_id'];
		
		$member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id ) )->find();
		
		if( empty($member_info) )
		{
			// 未登录
			echo json_encode( array('code' => 2) );
			die();
		}
		
		$community_info = D('Home/Front')->get_member_community_info($member_id);
		
		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 3 , 'msg' => '团长不存在' ) );
			die();	
		}
		
		$page = isset($_GPC['page']) ? $_GPC['page']:'1';
	
		$size = isset($_GPC['size']) ? $_GPC['size']:'20';
		$offset = ($page - 1)* $size;
		
		$head_id = $community_info['id'];
		
		
		$kwd = trim($_GPC['keyword']);
		$is_recipe = 0;
		
		$is_only_express = isset($_GPC['is_only_express']) ?  intval($_GPC['is_only_express']) : 0;
		
		
		$is_soli = 1;
		
		
		
		$type = 'normal';
		
		$condition = '  and g.type = "'.$type.'" and g.grounding = 1 and g.is_seckill = 0 ';

		if( $is_only_express == 1 )
		{
			$condition .= ' and gc.is_only_express=1 ';
		}else{
			$condition .= ' and gc.is_only_express=0 ';
		}
		
		if (!empty($kwd)) {
			$condition .= ' AND `g.goodsname` LIKE "%'.$kwd.'%"';
		}
		
		if( $is_soli == 1 )
		{
			
			$sql_goods_ids = "select pg.goods_id from ".C('DB_PREFIX')."lionfish_community_head_goods as pg   
				   where   pg.head_id = {$head_id}  order by pg.id desc ";
	
			$goods_ids_arr = M()->query($sql_goods_ids);
			
			
			$ids_arr = array();
			foreach($goods_ids_arr as $val){
				$ids_arr[] = $val['goods_id'];
			}
			if( !empty($ids_arr) )
			{
				$ids_str = implode(',',$ids_arr);
				
				$condition .= "  and ( g.is_all_sale = 1 or g.id in ({$ids_str}) )   ";
			}else{
				$condition .= "  and ( g.is_all_sale = 1  )  ";
			}
			//is_all_sale
		}
		//todo.... g.

		$ds = M()->query("SELECT g.id as gid, g.goodsname, g.subtitle, g.price, g.productprice,g.seller_count ,g.sales FROM " . 
				C('DB_PREFIX'). 'lionfish_comshop_goods as g left join '.C('DB_PREFIX').'lionfish_comshop_good_common as gc on  g.id =gc.goods_id  
					WHERE 1 ' . $condition . ' order by g.id desc limit '.$offset.','.$size );

		//$tmp_data['soldNum'] = $val['seller_count'] + $val['sales'];
		
		foreach ($ds as $key => $d) {
			//thumb
			$thumb = M('lionfish_comshop_goods_images')->where( array('goods_id' => $d['gid']) )->order('id asc')->find();
			
			$d['thumb'] =  tomedia($thumb['image']);
			
			$d['seller_count'] = $d['seller_count'] + $d['sales'];
			
			unset($d['sales']);
			
			$ds[$key] = $d;
		}

		if( !empty($ds) )
		{
			echo json_encode( array('code' => 0, 'data' => $ds ) );
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}
		
	}
	
	/**
		团长发布群接龙
	**/
	public function sub_head_solitaire()
	{
		$_GPC = I('request.');

		$token = $_GPC['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token ) )->find();

	    $member_id = $weprogram_token['member_id'];
		
		$member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id ) )->find();
		
		if( empty($member_info) )
		{
			// 未登录
			echo json_encode( array('code' => 2) );
			die();
		}
		
		$community_info = D('Home/Front')->get_member_community_info($member_id);
		
		$solitaire_is_needexamine = D('Home/Front')->get_config_by_name('solitaire_is_needexamine');
		
		if( empty($solitaire_is_needexamine) )
		{
			$solitaire_is_needexamine = 0;
		}
		
		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 3 , 'msg' => '团长不存在' ) );
			die();	
		}
		
		$head_id = $community_info['id'];
		
	
		
		$solitaire_name = htmlspecialchars( $_GPC['title'] );
		$images_list_str = $_GPC['images_list'];
		
		$images_list = explode(',', $images_list_str);
		
		$addtype =	1;
		
		$appstate =	$solitaire_is_needexamine == 1 ? 0 : 1;//是否需要审核
		
		$state 	  =	1;//状态
		
		$begin_time = $_GPC['begin_time'];
		$end_time = $_GPC['end_time'];
		
		$content =  htmlspecialchars( $_GPC['content'] );
		
		$goods_list = $_GPC['goods_list'];//'1,2,3,4'
		
		if( empty($solitaire_name) )
		{
			echo json_encode( array('code' => 1, 'msg' => '请填写接龙标题') );
			die();
		}
		if( empty($content) )
		{
			echo json_encode( array('code' => 1, 'msg' => '请填写接龙内容') );
			die();
		}
		if( empty($goods_list) )
		{
			echo json_encode( array('code' => 1, 'msg' => '请选择商品') );
			die();
		}
		
		if( empty($begin_time) )
		{
			echo json_encode( array('code' => 1, 'msg' => '请选择开始时间') );
			die();
		}
		
		if( empty($end_time) )
		{
			echo json_encode( array('code' => 1, 'msg' => '请选择结束时间') );
			die();
		}
		
		$data = array();
		
		$data['id'] = 0;
		
		$data['solitaire_name'] = $solitaire_name;
		$data['state'] = $state;
		$data['content'] = $content;
		
		$need_data = array();
		$need_data['data'] = $data;
		
		$need_data['images_list'] = $images_list;
		
		$need_data['time']['start'] = $begin_time;
		$need_data['time']['end'] = $end_time;
		
		$need_data['goods_list'] = $goods_list;
		
		$need_data['head_id'] = $head_id;
		$need_data['head_dan_id'] = $head_id;
		
		
	
		D('Seller/Solitaire')->updatedo($need_data, $_W['uniacid'] ,$addtype , $appstate );
		
		echo json_encode( array('code' => 0) );
		die();
		
	}
	
	
	/**
		获取用户查看 团长群接龙的列表
	**/
	public function get_head_index_solitairelist()
	{
		$_GPC = I('request.');

		
		$head_id = $_GPC['head_id'];
		
		$community_info = M('lionfish_community_head')->where( array('id' => $head_id ) )->find();
		
		$member_info = M('lionfish_comshop_member')->where( array('member_id') )->find();
		
		//$community_info = load_model_class('front')->get_member_community_info($member_id);
		
		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 3 , 'msg' => '团长不存在' ) );
			die();	
		}
		
		$community_info['avatar'] = $member_info['avatar'];
		
		
	    
		$page = isset($_GPC['page']) ? $_GPC['page']:'1';
	
		$size = isset($_GPC['size']) ? $_GPC['size']:'20';
		$offset = ($page - 1)* $size;
		// 
		
		$where = " and state = 1 and appstate = 1 and head_id = {$head_id} ";
		
		$keyword = isset($_GPC['keyword']) ? addslashes($_GPC['keyword']) : '';
		
		if( !empty($keyword) )
		{
			$where .= " and solitaire_name like '%{$keyword}%' ";
		}
		
		$sql = "select * from ".C('DB_PREFIX')."lionfish_comshop_solitaire   
						where  1  {$where}  
						  order by id desc limit {$offset},{$size}";
		
		$list =  M()->query($sql);
		
		$head_data = array();
		
		$head_data['head_id'] = $head_id;//绑定小区
		$head_data['community_name'] = $community_info['community_name'];//绑定小区
		$head_data['head_name'] = $community_info['head_name'];//团长昵称
		$head_data['avatar'] = $community_info['avatar'];//头像
		
		if( !empty( $list ) )
		{
			$need_data = array();
			
			$now_time = time();
			
			foreach( $list as $key => $val )
			{
				$tmp_arr = array();
				$tmp_arr['id'] = $val['id'];
				$tmp_arr['solitaire_name'] = $val['solitaire_name'];
				$tmp_arr['begin_time'] = date('Y-m-d', $val['begin_time']);
				$tmp_arr['end_time'] = date('Y-m-d', $val['end_time']);
				
				$state_str = '';
				
				if($val['end']==0){
					if( $val['begin_time'] > $now_time )
					{
						$state_str = '未开始';
					}else if( $val['begin_time'] <= $now_time && $val['end_time'] > $now_time )
					{
						$state_str = '进行中';
					}else if( $val['end_time'] < $now_time ){
						$state_str = '已结束';
					}
				} else {
					$state_str = '已结束';
				}
				
				
				$tmp_arr['state_str'] = $state_str;
				
				//接龙图片
				$images_list = unserialize($val['images_list']);
				
				if( empty($images_list) )
				{
					$images_list = array();
				}
				
				if( !empty($images_list) && is_array($images_list) )
				{
					foreach( $images_list as $kk => $vv )
					{
						$vv = tomedia( $vv );
						
						$images_list[$kk] = $vv;
					}
				}
				
				$tmp_arr['images_list'] = $images_list;
				
				//几人看过 //几人参加
				$order_count = M('lionfish_comshop_solitaire_order')->where( array('soli_id' => $val['id'] ) )->count();
				
				$tmp_arr['order_count'] = $order_count;
				
				$invite_count = M('lionfish_comshop_solitaire_invite')->where( array('soli_id' => $val['id'] ) )->count();
				
				$tmp_arr['invite_count'] = $invite_count;
				
				$need_data[$key] = $tmp_arr;
			}
			
			
			
			
			echo json_encode( array('code' => 0,'head_data' => $head_data, 'data' => $need_data ) );
			die();
			
		}else{
			
			echo json_encode( array('code' => 1, 'head_data' => $head_data ) );
			die();
		}
			
	}
	
	/**
		接龙详情接口
	**/
	public function get_solitaire_detail()
	{
		$_GPC = I('request.');

		$id = intval( $_GPC['id'] );
		$token = isset( $_GPC['token'] ) ? $_GPC['token']:'';
		$is_head = isset( $_GPC['is_head'] ) ? $_GPC['is_head']:0;
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token ) )->find();
					
	    $member_id = $weprogram_token['member_id'];
	    $is_Login = true;
	    if(empty($weprogram_token) ||  empty($member_id)) $is_Login = false;
		
		$soli_info = M('lionfish_comshop_solitaire')->where( array('id' => $id ) )->find();
		
		if( empty($soli_info) || $soli_info['state'] != 1 )
		{
			echo json_encode( array('code' => 1 , 'msg' => '接龙不存在' ) );
			die();	
		}

		// 团长信息
		$head_id = $soli_info['head_id'];
		
		$community_info = M('lionfish_community_head')->where( array('id' => $head_id ) )->find();
		
		$member_info = M('lionfish_comshop_member')->where( array('member_id' => $community_info['member_id'] ) )->find();
			
		$soli_info['is_involved'] = false; //是否参与
		// 团长中心详情
		if($is_head==1) {
			if(!$is_Login)
			{
				echo json_encode( array('code' => 2, 'msg'=>"团长未登录") );
				die();
			}
			// 团长访问非本团详情
			if(empty($community_info['member_id']) || $community_info['member_id'] != $member_id)
			{
				echo json_encode( array('code' => 1, 'msg'=>"接龙不存在") );
				die();
			}
		} else {
			//是否已参与
			if($is_Login) {
				$involved = M('lionfish_comshop_order')->where( "member_id={$member_id} and soli_id={$id} and order_status_id in (1,4,6,7,11,12,14)" )->find();
				
				if(!empty($involved)) $soli_info['is_involved'] = true;
			}
		}

		if( empty($soli_info['qrcode_image']))
		{
			$path = "lionfish_comshop/moduleA/solitaire/details"; // 首页地址测试
			$scene = $soli_info['id']."_".$member_id;
			$qrcode_image = D('Home/Pingoods')->_get_commmon_wxqrcode($path, $scene);
			
			M('lionfish_comshop_solitaire')->where( array('id' => $soli_info['id'] ) )->save( array('qrcode_image' => $qrcode_image) );
			
			$soli_info['qrcode_image'] = tomedia($qrcode_image);
		}else{
			$soli_info['qrcode_image'] = tomedia($soli_info['qrcode_image']);
		}
		
		
		$head_data = array();
		$head_data['head_id'] = $head_id;//绑定小区
		$head_data['community_name'] = $community_info['community_name'];//绑定小区
		$head_data['head_name'] = $community_info['head_name'];//团长昵称
		$head_data['avatar'] = $member_info['avatar'];//头像
		
		
		$order_count = M('lionfish_comshop_solitaire_order')->where( array('soli_id' => $id ) )->count();				  
			
		$invite_count = M('lionfish_comshop_solitaire_invite')->where( array('soli_id' => $id ) )->count();
		
		//lionfish_comshop_solitaire_order  soli_id  	order_id
		
		//sum(total+shipping_fare-voucher_credit-fullreduction_money) as total
		
		$sql = "select sum(o.total+o.shipping_fare-o.voucher_credit-o.fullreduction_money) as total from 
				".C('DB_PREFIX')."lionfish_comshop_solitaire_order as so, ".C('DB_PREFIX')."lionfish_comshop_order as o 
				where o.order_id = so.order_id and so.soli_id={$id}  and o.order_status_id in (1,4,6,7,11,14) ";
		
		$soli_total_money_arr = M()->query($sql);
		
		$soli_total_money = $soli_total_money_arr[0]['total'];
		
		
		$soli_info['content'] = htmlspecialchars_decode($soli_info['content']);
		$soli_info['order_count'] = $order_count;
		$soli_info['invite_count'] = $invite_count;
		$soli_info['soli_total_money'] = $soli_total_money;
		
		$soli_info['begin_time_str'] = date('Y-m-d H:i:s', $soli_info['begin_time'] );
		$soli_info['end_time_str']   = date('Y-m-d H:i:s', $soli_info['end_time'] );
		
		$soli_info['now_time'] = time();
		$soli_info['activity_state'] = 0;// 0未开始 ,1进行中，2已过期
		
		$now_time = time();
		
		if( $soli_info['begin_time'] > $now_time )
		{
			$soli_info['activity_state'] = 0;
		}else if( $soli_info['begin_time'] <= $now_time && $soli_info['end_time'] > $now_time )
		{
			$soli_info['activity_state'] = 1;
		}else if( $soli_info['end_time'] < $now_time ){
			$soli_info['activity_state'] = 2;
		}
		
		//$ims_
		$solitaire_goods_arr = M('lionfish_comshop_solitaire_goods')->field('goods_id')->where( array('soli_id' => $id ) )->order('id asc')->select();
		
		
		$solitaire_goods_str = "";
		
		$soli_info['goods_list'] = array();
		
		if( !empty( $solitaire_goods_arr ) )
		{
			$tp_arr = array();
			foreach( $solitaire_goods_arr as $val )
			{
				$tp_arr[] = $val['goods_id'];
			}
			
			$solitaire_goods_str = implode(',', $tp_arr );
			
			$gd_info_list =  M()->query('select g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction,gc.labelname,gc.video from 
				'.C('DB_PREFIX')."lionfish_comshop_goods as g ,".C('DB_PREFIX')."lionfish_comshop_good_common as gc 
					where  g.id in(".$solitaire_goods_str.") and g.id =gc.goods_id  ");
			
			$goods_data = array();
			
			$cart = D('Home/Car');
			
			foreach($gd_info_list as  $gd_info )
			{
				
				$tmp_data = array();
				$tmp_data['actId'] = $gd_info['id'];
				$tmp_data['spuName'] = $gd_info['goodsname'];
				
				$tmp_data['spuCanBuyNum'] = $gd_info['total'];
				$tmp_data['spuDescribe'] = $gd_info['subtitle'];
				
				$tmp_data['is_take_vipcard'] = $gd_info['is_take_vipcard'];
				$tmp_data['soldNum'] = $gd_info['seller_count'] + $gd_info['sales'];
				
				$productprice = $gd_info['productprice'];
				$tmp_data['marketPrice'] = explode('.', $productprice);

				if( !empty($gd_info['big_img']) )
				{
					$tmp_data['bigImg'] = tomedia($gd_info['big_img']);
				}
				
				$good_image = D('Home/Pingoods')->get_goods_images($gd_info['id']);
				if( !empty($good_image) )
				{
					$tmp_data['skuImage'] = tomedia($good_image['image']);
				}
				$price_arr = D('Home/Pingoods')->get_goods_price($gd_info['id'], $member_id);
				$price = $price_arr['price'];
				
				if( $pageNum == 1 )
				{
					$copy_text_arr[] = array('goods_name' => $gd_info['goodsname'], 'price' => $price);
				}
				
				$tmp_data['actPrice'] = explode('.', $price);
				$tmp_data['card_price'] = $price_arr['card_price'];
				
				//card_price $id
				
				$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options($gd_info['id'],$member_id);
				
				if( !empty($tmp_data['skuList']) )
				{
					$tmp_data['car_count'] = 0;
				}else{
					$car_count = $cart->get_wecart_goods($gd_info['id'],"",$head_id ,$token);
					
					if( empty($car_count)  )
					{
						$tmp_data['car_count'] = 0;
					}else{
						$tmp_data['car_count'] = $car_count;
					}
				}
				
				$goods_total_count = $cart->get_wecart_goods_solicount($gd_info['id'], $head_id,$token, $id );
				
				$tmp_data['goods_total_count'] = $goods_total_count;
				
				
				if($is_open_fullreduction == 0)
				{
					$tmp_data['is_take_fullreduction'] = 0;
				}else if($is_open_fullreduction == 1){
					$tmp_data['is_take_fullreduction'] = $gd_info['is_take_fullreduction'];
				}

				// 商品角标
				$label_id = unserialize($gd_info['labelname']);
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

				$tmp_data['is_video'] = false;
				
				$goods_data[] = $tmp_data;
			}
			
			$soli_info['goods_list'] = $goods_data;	
		}

		//接龙图片
		$images_list = unserialize($soli_info['images_list']);
		
		if( empty($images_list) )
		{
			$images_list = array();
		}
		
		if( !empty($images_list) )
		{
			foreach( $images_list as $kk => $vv )
			{
				$vv = tomedia( $vv );
				
				$images_list[$kk] = $vv;
			}
		}
		
		$soli_info['images_list'] = $images_list;
		
		//
		$solitaire_target = D('Home/Front')->get_config_by_name('solitaire_target');
		
		if( empty($solitaire_target) )
		{
			$solitaire_target = 0; //是否开启目标
		}
		
		$solitaire_target_type = D('Home/Front')->get_config_by_name('solitaire_target_type');
		if( empty($solitaire_target_type) )
		{
			$solitaire_target_type = 0;
		}//0 参与人数， 1接龙金额
		
		$solitaire_target_takemember = D('Home/Front')->get_config_by_name('solitaire_target_takemember');
		
		if( empty($solitaire_target_takemember) )
		{
			$solitaire_target_takemember = 0;
		}
		
		//    参与人数
		//solitaire_target_takemoney	 接龙金额
		$solitaire_target_takemoney = D('Home/Front')->get_config_by_name('solitaire_target_takemoney');
		
		if( empty($solitaire_target_takemoney) )
		{
			$solitaire_target_takemoney =0;
		}
		
		$solitaire_notice = D('Home/Front')->get_config_by_name('solitaire_notice');
		$solitaire_notice = htmlspecialchars_decode( $solitaire_notice );

		$soli_info['comment_total'] = M('lionfish_comshop_solitaire_post')->where( array('soli_id' => $id, 'pid' => 0 ) )->count();
		
		echo json_encode(  array('code' => 0, 
							'head_data' => $head_data, 
							'solitaire_target' => $solitaire_target, 
							'solitaire_target_type' => $solitaire_target_type,
							'solitaire_target_takemoney' => $solitaire_target_takemoney,
							'solitaire_target_takemember' => $solitaire_target_takemember, 
							'solitaire_notice' => $solitaire_notice, 
							'soli_info' => $soli_info
						) );
		die();
		
		
	}
	
	
	
	/**
		增加访问量接口
	**/
	public function send_visite_record()
	{
		$_GPC = I('request.');

		$token = $_GPC['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token ) )->find();	
						
	    $member_id = $weprogram_token['member_id'];
	    
		if( empty($member_id) )
		{
			// 未登录
			echo json_encode( array('code' => 1) );
			die();
		}
		
		$soli_id = $_GPC['soli_id'];
		
		$ck_info = M('lionfish_comshop_solitaire_invite')->where( array('soli_id' => $soli_id, 'member_id' => $member_id ) )->find();
		
		if( !empty($ck_info) )
		{
			echo json_encode( array('code' => 1, 'msg' => '已加入') );
			die();
		}
		
		$ins_data = array();
		$ins_data['uniacid'] = 0;
		$ins_data['soli_id'] = $soli_id;
		$ins_data['member_id'] = $member_id;
		$ins_data['addtime'] = time();
		
		M('lionfish_comshop_solitaire_invite')->add($ins_data);
		
		echo json_encode( array('code' => 0) );
		die();
	}
	
	/**
		评价群接龙
	**/
	
	public function sub_solipost()
	{
		$_GPC = I('request.');
		
		$token = $_GPC['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token ) )->find();
						
	    $member_id = $weprogram_token['member_id'];
	    
		if( empty($member_id) )
		{
			// 未登录
			echo json_encode( array('code' => 1) );
			die();
		}
		
		$soli_id = isset($_GPC['soli_id']) ? intval($_GPC['soli_id']) : 0 ;
		$pid 	 = isset($_GPC['pid']) ? intval($_GPC['pid']) : 0;
		$content 	 = isset($_GPC['content']) ? htmlspecialchars($_GPC['content']) : '';
		
		if( empty($soli_id) || $soli_id <=0 )
		{
			echo json_encode( array('code' => 2, 'msg' => '非法请求') );
			die();
		}
		
		if( empty($content) )
		{
			echo json_encode( array('code' => 2, 'msg' => '评价内容不能为空') );
			die();
		}
		
		$end_time = time();
		$begin_time = $end_time - 30;
		
		//20 member_id
		
		$total = M('lionfish_comshop_solitaire_post')->where( "member_id={$member_id} and addtime>={$begin_time} and addtime <={$end_time} " )->count();
		
		if( $total >=  20)
		{
			echo json_encode( array('code' => 2, 'msg' => '评论太过频繁') );
			die();
		}
		
		$ins_data = array();
		$ins_data['uniacid'] = 0;
		$ins_data['soli_id'] = $soli_id;
		$ins_data['member_id'] = $member_id;
		$ins_data['pid'] = $pid;
		$ins_data['fav_count'] = 0;
		$ins_data['content'] = $content;
		$ins_data['addtime'] = time();
		
		$id = M('lionfish_comshop_solitaire_post')->add( $ins_data );
		
		echo json_encode( array( 'code' =>0, 'post_id' => $id, 'cur_time' => date('Y-m-d H:i:s', time()) ) );
		die();
	}
	
	/**
		点赞评论
	**/
	public function fav_soli_post()
	{
		$_GPC = I('request.');

		$token = $_GPC['token'];
		
		$weprogram_token =	M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token ) )->find();				
							
	    $member_id = $weprogram_token['member_id'];
	    
		if( empty($member_id) )
		{
			// 未登录
			echo json_encode( array('code' => 1) );
			die();
		}
		
		$soli_id = isset($_GPC['soli_id']) ? intval($_GPC['soli_id']) : 0 ;
		$post_id = isset($_GPC['post_id']) ? intval($_GPC['post_id']) : 0 ;
		
		if( empty($post_id) )
		{
			echo json_encode( array('code' => 2, 'msg' => '未选择需要点赞的评论') );
			die();
		}
		if( empty($soli_id) )
		{
			echo json_encode( array('code' => 2, 'msg' => '未选择需要点赞的群接龙') );
			die();
		}
		
		$fav_info = M('lionfish_comshop_solitaire_post_fav')->where( array('member_id' =>$member_id,'post_id' => $post_id ) )->find();
		
		if( empty($fav_info) )
		{
			//增加
			M('lionfish_comshop_solitaire_post')->where( array('id' => $post_id ) )->setInc('fav_count',1);
			
			
			$ins_data = array();
			$ins_data['uniacid'] = 0;
			$ins_data['member_id'] = $member_id;
			$ins_data['soli_id'] = $soli_id;
			$ins_data['post_id'] = $post_id;
			$ins_data['addtime'] = time();
			
			M('lionfish_comshop_solitaire_post_fav')->add( $ins_data );
			
			echo json_encode( array('code' => 0,'do' => 1) );
			die();
		}else{
			//减少
			$result = M('lionfish_comshop_solitaire_post_fav')->where( array('id' => $fav_info['id']) )->delete();
			
			if (!empty($result)) {
			    
				M('lionfish_comshop_solitaire_post')->where( array('id' => $post_id ) )->setInc('fav_count', -1);
				
				echo json_encode( array('code' => 0,'do' => 2) );
				die();
			} else {
				echo json_encode( array('code' => 2, 'msg' => '取消点赞失败') );
				die();
			}
		}
	}

	/**
	*接龙规则
	**/
	public function get_rule()
	{
		$_GPC = I('request.');

		$solitaire_notice = D('Home/Front')->get_config_by_name('solitaire_notice');
		$solitaire_notice = htmlspecialchars_decode( $solitaire_notice );
		
		echo json_encode( array('code' => 0, 'solitaire_notice'=>$solitaire_notice) );
		die();
	}
	
	
	/**
		接龙海报
	**/
	public function get_haibao()
	{
		$_GPC = I('request.');
		
		$soli_id = $_GPC['soli_id'];
		 
		$solitaire_info = M('lionfish_comshop_solitaire')->where( array('id' => $soli_id ) )->find();
		
		$head_id = $solitaire_info['head_id'];
		
		if( !empty($solitaire_info['qrcode_image'] ) )
		{
			$image = tomedia( $solitaire_info['qrcode_image'] );
			
			echo json_encode( array('code' =>0, 'image' => $image ) );
			die();
		}else{
			
			
		}
		
	}


	/**
		*获取用户群接龙的列表,分页
	**/
	public function get_member_solitairelist()
	{
		$_GPC = I('request.');

		$token = $_GPC['token'];
				
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token ) )->find();			
					
	    $member_id = $weprogram_token['member_id'];
		
	    		
		$member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id ) )->find();				
		
		if( empty($member_info) )
		{
			// 未登录
			echo json_encode( array('code' => 2) );
			die();
		}
	    
		$page = isset($_GPC['page']) ? $_GPC['page']:'1';
		$size = isset($_GPC['size']) ? $_GPC['size']:'20';
		$offset = ($page - 1)* $size;

		$list =  M()->query('select gc.* from '.C('DB_PREFIX')."lionfish_comshop_order as g ,".C('DB_PREFIX')."lionfish_comshop_solitaire as gc 
					where  g.member_id={$member_id} and g.soli_id>0 and g.soli_id = gc.id order by g.order_id desc limit {$offset}, {$size} " );
		
		if( !empty( $list ) )
		{
			$need_data = array();
			$now_time = time();
			foreach( $list as $key => $val )
			{
				$tmp_arr = array();
				$tmp_arr['id'] = $val['id'];
				$tmp_arr['solitaire_name'] = $val['solitaire_name'];
				$tmp_arr['begin_time'] = date('Y-m-d', $val['begin_time']);
				$tmp_arr['end_time'] = date('Y-m-d', $val['end_time']);
				
				$state_str = '';
				
				if($val['end']==0) {
					if( $val['begin_time'] > $now_time )
					{
						$state_str = '未开始';
					}else if( $val['begin_time'] <= $now_time && $val['end_time'] > $now_time )
					{
						$state_str = '进行中';
					}else if( $val['end_time'] < $now_time ){
						$state_str = '已结束';
					}
				} else {
					$state_str = '已结束';
				}
				
				
				$tmp_arr['state_str'] = $state_str;
				
				//接龙图片
				$images_list = unserialize($val['images_list']);
				
				if( empty($images_list) )
				{
					$images_list = array();
				}
				
				if( !empty($images_list) && is_array($images_list) )
				{
					foreach( $images_list as $kk => $vv )
					{
						$vv = tomedia( $vv );
						
						$images_list[$kk] = $vv;
					}
				}
				$tmp_arr['images_list'] = $images_list;
				
				$need_data[$key] = $tmp_arr;
			}
			
			echo json_encode( array('code' => 0, 'data' => $need_data ) );
			die();
		}else{
			
			echo json_encode( array('code' => 1) );
			die();
		}
	}

	/**
		*获取接龙详情留言列表,分页
	**/
	public function get_comment_list()
	{
		$_GPC = I('request.');

		$id = intval( $_GPC['id'] );
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token ) )->find();
			
	    $member_id = $weprogram_token['member_id'];
	    		
		$member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id ) )->find();
		
		$is_login = true;
		if( empty($member_info) )
		{
			// 未登录
			$is_login = false;
			// echo json_encode( array('code' => 2) );
			// die();
		}
	    
		$page = isset($_GPC['page']) ? $_GPC['page']:'1';
		$size = isset($_GPC['size']) ? $_GPC['size']:'20';
		$offset = ($page - 1)* $size;


		$list =  M()->query('select g.*,gc.username,gc.avatar from '.C('DB_PREFIX')."lionfish_comshop_solitaire_post as g ,".C('DB_PREFIX')."lionfish_comshop_member as gc 
				where  g.soli_id={$id} and g.pid=0 and g.member_id = gc.member_id order by g.id desc limit {$offset}, {$size} " );
		
		if(!empty($list))
		{
			$need_data = array();
			foreach( $list as $key => $val )
			{
				$tmp_arr = array();
				$tmp_arr['id'] = $val['id'];
				$tmp_arr['soli_id'] = $val['soli_id'];
				$tmp_arr['pid'] = $val['pid'];
				$tmp_arr['username'] = $val['username'];
				$tmp_arr['avatar'] = $val['avatar'];
				$tmp_arr['fav_count'] = $val['fav_count'];
				$tmp_arr['content'] = $val['content'];
				$tmp_arr['addtime'] = date('Y-m-d H:i:s', $val['addtime']);

				//查询回复	
				$reply = M('lionfish_comshop_solitaire_post')->field('id,content')->where( array('pid' => $val['id'], 'soli_id' => $val['soli_id'] ) )->find();		
						
				if(empty($reply)) {
					$tmp_arr['reply'] = array();
				} else {
					$tmp_arr['reply'][] = $reply;
				}

				//是否点赞
				$is_agree = false;
				if($is_login) {	
					$agree = M('lionfish_comshop_solitaire_post_fav')->field('id')->where( array('post_id' => $val['id'],'soli_id' => $val['soli_id'],'member_id' => $member_id ) )->find();		
							
					if(!empty($agree)) $is_agree = true;
				}
				$tmp_arr['is_agree'] = $is_agree;

				$need_data[$key] = $tmp_arr;
			}
			
			echo json_encode( array('code' => 0, 'data' => $need_data ) );
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}
	}

	/**
	 * 删除留言
	 * @return [json] [status]
	 */
	public function delete_comment()
	{
		$_GPC = I('request.');

		$id = intval( $_GPC['id'] );
		
		$token = $_GPC['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token ) )->find();
		
	    $member_id = $weprogram_token['member_id'];
	    				
		$member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id ) )->find();	
		
		if( empty($member_info) )
		{
			// 未登录
			echo json_encode( array('code' => 2) );
			die();
		}

		$result = M('lionfish_comshop_solitaire_post')->where( array('id' => $id ) )->delete();
		
		if (!empty($result)) {
			echo json_encode( array('code' => 0, 'msg' => '删除成功') );
			die();
		} else {
			echo json_encode( array('code' => 1, 'msg' => '删除失败') );
			die();
		}
	}


	/**
	 * 团长手动结束
	 * @return [json] [status]
	 */
	public function end_solitaire()
	{
		$_GPC = I('request.');

		$id = intval( $_GPC['id'] );
		$token = $_GPC['token'];
		
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token ) )->find();
		
	    $member_id = $weprogram_token['member_id'];
	    	
		$member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id ) )->find();		
		
		if( empty($member_info) )
		{
			// 未登录
			echo json_encode( array('code' => 2) );
			die();
		}

		$soli_info = M('lionfish_comshop_solitaire')->where( array('id' => $id ) )->find();
		
		if( empty($soli_info) || $soli_info['state'] != 1 )
		{
			echo json_encode( array('code' => 1 , 'msg' => '接龙不存在' ) );
			die();	
		}

		// 团长信息
		$head_id = $soli_info['head_id'];
					
		$community_info = M('lionfish_community_head')->where( array('id' => $head_id ) )->find();				
			
		$member_info = M('lionfish_comshop_member')->where( array('member_id' => $community_info['member_id'] ) )->find();	

		// 团长访问非本团详情
		if(empty($community_info['member_id']) || $community_info['member_id'] != $member_id)
		{
			echo json_encode( array('code' => 1, 'msg'=>"接龙不存在") );
			die();
		}

		$result = M('lionfish_comshop_solitaire')->where( array('id' => $id) )->save(  array( 'end' => 1 ) );
		
		if (!empty($result)) {
			echo json_encode( array('code' => 0, 'msg' => '操作成功') );
			die();
		} else {
			echo json_encode( array('code' => 1, 'msg' => '操作失败') );
			die();
		}
	}

	/**
		*获取接龙详情订单列表,分页
	**/
	public function get_soli_order_list()
	{
		$_GPC = I('request.');

		$id = intval( $_GPC['id'] );
		
		$page = isset($_GPC['page']) ? $_GPC['page']:'1';
		$size = isset($_GPC['size']) ? $_GPC['size']:'20';
		$offset = ($page - 1)* $size;

		$sql = "select so.order_id,so.addtime,m.username,m.avatar from ".C('DB_PREFIX')."lionfish_comshop_solitaire_order as so, ".C('DB_PREFIX')."lionfish_comshop_order as o, ".C('DB_PREFIX')."lionfish_comshop_member as m 
				where o.order_id = so.order_id and so.soli_id={$id} and o.order_status_id in (1,4,6,7,11,14) and o.member_id=m.member_id order by so.id desc limit {$offset}, {$size} ";
		$list = M()->query($sql);
		
		if(!empty($list))
		{
			$need_data = array();
			foreach( $list as $key => &$val )
			{
				$val['addtime'] = date('Y-m-d H:i:s', $val['addtime']);
				
				$goods_list = M('lionfish_comshop_order_goods')->field('order_goods_id,goods_id,name,goods_images,quantity,price,total')->where( array('order_id' => $val['order_id'] ) )->select();
				
	        	foreach($goods_list as $kk => $vv){
	        		
					$order_option_list = M('lionfish_comshop_order_option')->where( array('order_goods_id' => $vv['order_goods_id'] ) )->select();	
	            
					if( !empty($vv['goods_images']))
					{
						$goods_images = $vv['goods_images'];
						if(is_array($goods_images))
						{
							$vv['goods_images'] = $vv['goods_images'];
						}else{
							$vv['goods_images']= tomedia( $vv['goods_images'] ); 
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
		            $vv['price'] = sprintf("%.2f",$vv['price']);
		            $vv['orign_price'] = sprintf("%.2f",$vv['orign_price']);
		            $vv['total'] = sprintf("%.2f",$vv['total']);

		            $goods_list[$kk] = $vv;
	        	}

	        	$val['goodslist'] = $goods_list;
	        	$val['goodsnum'] = count($goods_list);

			}
			
			echo json_encode( array('code' => 0, 'data' => $list ) );
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}
	}
	
}