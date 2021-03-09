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


class MarketingController extends CommonController {
	
	public function get_special()
	{
		$_GPC = I('request.');

		$token =  $_GPC['token'];
		
		$id = $_GPC['id'];
		$head_id = $_GPC['head_id'];

		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			//echo json_encode( array('code' => 2,'msg' =>'请先登录') );
			//die();
		}
		
	    $member_id = $weprogram_token['member_id'];

		$data = M('lionfish_comshop_special')->where( array('id' => $id ) )->find();		

		$goodsids = $data['goodsids'];
		$list = array();
		if(!empty($data)) {

			if($data['enabled']==0){
				echo json_encode( array('code' => 1, 'msg' => '专题已关闭') );
				die();
			}

			if($data['begin_time'] > time()){
				echo json_encode( array('code' => 1, 'msg' => '活动未开始') );
				die();
			}

			if($data['end_time'] <= time()){
				echo json_encode( array('code' => 1, 'msg' => '活动已结束') );
				die();
			}

			if( !empty($data['cover']) ) $data['cover'] = tomedia($data['cover']);
			if( !empty($data['special_cover']) ) $data['special_cover'] = tomedia($data['special_cover']);

			// 满减
			$is_open_fullreduction = D('Home/Front')->get_config_by_name('is_open_fullreduction');
			$full_money = D('Home/Front')->get_config_by_name('full_money');
			$full_reducemoney = D('Home/Front')->get_config_by_name('full_reducemoney');
			
			if(empty($full_reducemoney) || $full_reducemoney <= 0) $is_open_fullreduction = 0;

			
			if($goodsids) {
				$now_time = time();
				$where = ' g.grounding = 1 ';
				$where .= " and g.id in ({$goodsids})";
				$where .= " and gc.begin_time <={$now_time} and gc.end_time > {$now_time} ";
				$community_goods = D('Home/Pingoods')->get_community_index_goods('g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction,gc.labelname ', $where, 0, 1000);

				foreach ($community_goods as $key => $value) {
					if($value['is_all_sale']==1){
						$list[] = $this->change_goods_form($value, $head_id, $token, $is_open_fullreduction);
					} else {
						
						$is_head_shop = M('lionfish_community_head_goods')->field('id')->where( array('head_id' => $head_id,'goods_id' => $value['id'] ) )->order('id desc')->select();
						
						if(!empty($is_head_shop)) $list[] = $this->change_goods_form($value, $head_id, $token , $is_open_fullreduction);
					}
				}
			}
		} else {
			echo json_encode( array('code' => 1, 'msg' => '无此专题') );
			die();
		}

		
		$ishow_special_share_btn = D('Home/Front')->get_config_by_name('ishow_special_share_btn');

		echo json_encode( array(
			'code' => 0, 
			'data' => $data, 
			'list' => $list, 
			'ishow_special_share_btn' => $ishow_special_share_btn,
			'full_reducemoney' => $full_reducemoney,
			'full_money' => $full_money,
			'is_open_fullreduction' => $is_open_fullreduction
		));
		
		die();
	}

	private function change_goods_form ($val, $head_id='', $token='' , $is_open_fullreduction=0){
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
		$tmp_data['actPrice'] = explode('.', $price);
		
		$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options($val['id'],$member_id);
		
		if( !empty($tmp_data['skuList']) )
		{
			$tmp_data['car_count'] = 0;
		}else{
			$car_count = D('Home/Car')->get_wecart_goods($val['id'],"",$head_id ,$token);
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
		
		return $tmp_data;
	}

	public function get_special_list()
	{
		$_GPC = I('request.');

		$head_id = $_GPC['head_id'];

		$token =  $_GPC['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			//echo json_encode( array('code' => 2,'msg' =>'请先登录') );
			//die();
		}

		$now_time = time();
		$condition = 'enabled = 1 and is_index = 1 and begin_time<='.$now_time.' and end_time>'.$now_time;
		$special_list = M()->query('SELECT * FROM ' . C('DB_PREFIX'). 'lionfish_comshop_special WHERE '.$condition.'  order by displayorder desc ');


		if(!empty($special_list)) {

			foreach ($special_list as &$data) {
				$list = array();
				$goodsids = $data['goodsids'];
				if( !empty($data['cover']) ) $data['cover'] = tomedia($data['cover']);
				if( !empty($data['special_cover']) ) $data['special_cover'] = tomedia($data['special_cover']);

				if($goodsids && $data['type']==1) {
					$where = ' g.grounding = 1 ';
					$where .= " and g.id in ({$goodsids})";
					$where .= " and gc.begin_time <={$now_time} and gc.end_time > {$now_time} ";
					$community_goods = D('Home/Pingoods')->get_community_index_goods('g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction,gc.labelname ', $where, 0, 1000);

					foreach ($community_goods as $key => $value) {
						if($value['is_all_sale']==1){
							$list[] = $this->change_goods_form($value);
						} else {
							
							$is_head_shop =	M('lionfish_community_head_goods')->field('id')->where( array('goods_id' => $value['id'],'head_id' => $head_id ) )->order('id desc')->select();
									
							if(!empty($is_head_shop)) $list[] = $this->change_goods_form($value);
						}
					}
				}

				$data['list'] = $list;
			}

		} else {
			echo json_encode( array('code' => 1, 'msg' => '无专题') );
			die();
		}

		echo json_encode( array('code' => 0, 'data' => $special_list ) );
		die();
	}
	
	
	/**
	 * 专题列表
	 * @return @return [json] [list]
	 */
	public function get_special_page_list()
	{
		$_GPC = I('request.');

		$head_id = $_GPC['head_id'];
		$page = isset($_GPC['page']) ? $_GPC['page']:'1';
		$pre_page = 10;
		$offset = ($page -1) * $pre_page;

		$token =  $_GPC['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			//echo json_encode( array('code' => 2,'msg' =>'请先登录') );
			//die();
		}

		$now_time = time();
		$condition = 'enabled = 1 and begin_time<='.$now_time.' and end_time>'.$now_time;
		$special_list = M()->query('SELECT * FROM ' . C('DB_PREFIX'). 'lionfish_comshop_special WHERE '.$condition."  order by displayorder desc limit {$offset},{$pre_page} ");


		if(!empty($special_list)) {

			foreach ($special_list as &$data) {
				$list = array();
				$goodsids = $data['goodsids'];
				if( !empty($data['cover']) ) $data['cover'] = tomedia($data['cover']);
				if( !empty($data['special_cover']) ) $data['special_cover'] = tomedia($data['special_cover']);

				if($goodsids) {
					$where = ' g.grounding = 1 ';
					$where .= " and g.id in ({$goodsids})";
					$where .= " and gc.begin_time <={$now_time} and gc.end_time > {$now_time} ";
					$community_goods = D('Home/Pingoods')->get_community_index_goods('g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction,gc.labelname ', $where, 0, 1000);

					foreach ($community_goods as $key => $value) {
						if($value['is_all_sale']==1){
							$list[] = $this->change_goods_form($value);
						} else {
							
							$is_head_shop = M('lionfish_community_head_goods')->field('id')->where( array('head_id' => $head_id, 'goods_id' => $value['id']) )->order('id desc')->select();			
								
							if(!empty($is_head_shop)) $list[] = $this->change_goods_form($value);
						}
					}
				}

				$data['list'] = $list;
			}

		} else {
			echo json_encode( array('code' => 1, 'msg' => '无专题') );
			die();
		}

		echo json_encode( array('code' => 0, 'data' => $special_list ) );
		die();
	}

	
}