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

class SupplyController extends CommonController {
	
	/**
	 * 获取申请页面
	 */
	public function get_apply_page()
	{
		
		$info = M('lionfish_comshop_config')->where( array('name' => 'supply_apply_page') )->find();
		
		$supply_diy_name = D('Home/Front')->get_config_by_name('supply_diy_name');

		
	    if(!empty($info['value'])){
	    	echo json_encode( array('code' => 0, 'data' => htmlspecialchars_decode($info['value']) , 'supply_diy_name' => $supply_diy_name ) );
			die();
	    }else{
	    	echo json_encode( array('code' => 1 , 'supply_diy_name' => $supply_diy_name));
			die();
	    }
	}
	
	/**
	 * 已申请信息
	 */
	public function apply_info()
	{
		$_GPC = I('request.');
	
		$token =  $_GPC['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token ) )->find();
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		
	    $member_id = $weprogram_token['member_id'];
		
		$supp_info = M('lionfish_comshop_supply')->field('id,shopname,mobile,product,state')->where( array('member_id' => $member_id ) )->find();
		
		$supply_diy_name = D('Home/Front')->get_config_by_name('supply_diy_name');
			
			
		if( !empty($supp_info) )
		{
			echo json_encode( array('code' => 0,'data' => $supp_info , 'supply_diy_name' => $supply_diy_name) );
			die();
		} else {
			echo json_encode( array('code' => 2,'msg' => '未申请供应商' , 'supply_diy_name' => $supply_diy_name) );
			die();
		}
		
		echo json_encode( array('code' => 0 , 'supply_diy_name' => $supply_diy_name) );
		die();
	}

	
	/**
	 * 供应商列表
	 */
	public function get_list()
	{
		$_GPC = I('request.');

		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		
		$head_id = intval($_GPC['head_id']);
		$token = $_GPC['token'];

	
		$sql = 'SELECT * FROM ' . C('DB_PREFIX'). 'lionfish_comshop_supply WHERE  state=1 order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize;
		
		$list = M()->query($sql);
		
		$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX'). 'lionfish_comshop_supply WHERE  state=1 ');
		$total = $total_arr[0]['count'];
		
		
		foreach( $list as $key => $val )
		{
			$now_time = time();
			
			
			$val['banner'] = tomedia($val['banner']);
			
			$goods_count_arr = M()->query("SELECT count(gc.id) as count  FROM ".C('DB_PREFIX')."lionfish_comshop_good_common gc , ".C('DB_PREFIX')."lionfish_comshop_goods as g WHERE gc.goods_id = g.id and gc.supply_id = {$val['id']} and g.grounding = 1 and gc.begin_time < {$now_time}  and gc.end_time > {$now_time} ");
			
			$goods_count = $goods_count_arr[0]['count'];
					
			$val['goods_count'] = $goods_count;
			if( !empty($val['logo']) ) $val['logo'] = tomedia($val['logo']);

			$goods_list = array();
			$now_time = time();
			$where = ' g.grounding = 1 ';
			$where .= " and gc.begin_time <={$now_time} and gc.end_time > {$now_time} ";
			$where .= " and gc.supply_id = " . $val['id'];
			
			$community_goods = D('Home/Pingoods')->get_community_index_goods('g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction,gc.labelname ', $where, 0, 10);

			foreach ($community_goods as $key => $value) {
				if($value['is_all_sale']==1){
					$goods_list[] = $this->change_goods_form($value, $head_id, $token);
				} else {
					
					$is_head_shop = M('lionfish_community_head_goods')->field('id')->where( array('goods_id' => $value['id'],'head_id' => $head_id) )->order('id desc')->select();
					
					if(!empty($is_head_shop)) $goods_list[] = $this->change_goods_form($value, $head_id, $token);
				}
			}

			$val['goods_list'] = $goods_list;
			$list[$key] = $val;
		}

		$supply_diy_name = D('Home/Front')->get_config_by_name('supply_diy_name');

	    if(!empty($list)){
	    	echo json_encode( array('code' => 0, 'data' => $list, 'supply_diy_name' => $supply_diy_name) );
			die();
	    }else{
	    	echo json_encode( array('code' => 1 , 'supply_diy_name' => $supply_diy_name));
			die();
	    }
	}

	/**
	 * 供应商主页
	 */
	public function get_details()
	{
		$_GPC = I('request.');

		
		$pindex = max(1, intval($_GPC['page']));
		$id = max(0, intval($_GPC['id']));
		$psize = 20;
		$head_id = intval($_GPC['head_id']);
		$token = $_GPC['token'];

		$per_page = 20;
		$offset = ($pindex - 1) * $per_page;
		$limit = "{$offset},{$per_page}";
		
		$item = M('lionfish_comshop_supply')->where( array('state' => 1, 'id' => $id) )->order('id desc')->find();
		
		
		$goods_list = array();
		if(!empty($item)) {
			$item['banner'] = tomedia($item['banner']);
			$now_time = time();
			$where = ' g.grounding = 1 ';
			$where .= " and gc.begin_time <={$now_time} and gc.end_time > {$now_time} ";
			$where .= " and gc.supply_id = " . $item['id'];
			$community_goods = D('Home/Pingoods')->get_community_index_goods('g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction,gc.labelname ', $where, $offset, $per_page);

			foreach ($community_goods as $key => $value) {
				if($value['is_all_sale']==1){
					$goods_list[] = $this->change_goods_form($value, $head_id, $token);
				} else {
					
					$is_head_shop =	M('lionfish_community_head_goods')->field('id')->where( array('head_id' => $head_id,'goods_id' => $value['id'] ) )->order('id desc')->select();
							
					if(!empty($is_head_shop)) $goods_list[] = $this->change_goods_form($value, $head_id, $token);
				}
			}
		}

	    if(!empty($item)){
	    	echo json_encode( array('code' => 0, 'data' => $item, 'list' => $goods_list) );
			die();
	    }else{
	    	echo json_encode( array('code' => 1 ));
			die();
	    }
	}

	
	private function change_goods_form ($val, $head_id="", $token=""){
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

    
}