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
namespace Seller\Controller;
use Admin\Model\OrderModel;
class OrderController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
		
	}
	
    public function index(){
     	
		$time = I('request.time');
  
		//htong 团长统计 查看订单明细修改 开始
		if(!$time && I('request.timeend') && I('request.timestart')){
			$time =array();
			$time['start'] = I('request.timestart');
			$time['end'] = I('request.timeend');
		}
		//htong 团长统计 查看订单明细修改 结束
		$starttime = isset($time['start']) ? strtotime($time['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($time['end']) ? strtotime($time['end']) : strtotime(date('Y-m-d'.' 23:59:59'));
	
		
		$this->searchfield = I('request.searchfield','');
		$this->keyword = I('request.keyword','');
		$this->searchtime = I('request.searchtime','');
		$this->delivery = I('request.delivery','');
		$this->starttime = $starttime;
		$this->endtime = $endtime;
		$this->time = $time;
		
		$order_status_id = I('request.order_status_id',0);
		
		$order_status_arr = D('Seller/Order')->get_order_status_name();
		
		$this->order_status_arr = $order_status_arr;
		
		$is_soli_type = I('request.type', '');// isset($_GPC['type']) && $_GPC['type'] == 'soli' ? 1: 0;
		
		$is_soli = 0;
		if($is_soli_type == 'soli')
		{
			$is_soli = 1;
		}
		//soli
		$_GPC['is_soli'] = $is_soli;
		
		$_GET['type'] = 'normal';
		
		
		$this->is_soli = $is_soli;
		
		$need_data = D('Seller/Order')->load_order_list();
	
		$cur_controller = 'order/index';
		$total = $need_data['total'];
		$total_money = $need_data['total_money'];
        $today_total_money = $need_data['today_total_money'];
		$list = $need_data['list'];
		$pager = $need_data['pager'];
		$all_count = $need_data['all_count'];
		$count_status_1 = $need_data['count_status_1'];
		$count_status_3 = $need_data['count_status_3'];
		$count_status_4 = $need_data['count_status_4'];
		$count_status_5 = $need_data['count_status_5'];
		$count_status_7 = $need_data['count_status_7'];
		$count_status_11 = $need_data['count_status_11'];
		$count_status_14 = $need_data['count_status_14'];
		
	
		$this->cur_controller = $cur_controller;
		$this->total = $total;
		$this->today_total_money = $today_total_money;

		$this->total_money = $total_money;
		$this->list = $list;
		$this->pager = $pager;
		$this->all_count = $all_count;
		$this->count_status_1 = $count_status_1;
		$this->count_status_3 = $count_status_3;
		$this->count_status_4 = $count_status_4;
		$this->count_status_5 = $count_status_5;
		$this->count_status_7 = $count_status_7;
		$this->count_status_11 = $count_status_11;
		$this->count_status_14 = $count_status_14;
		
		$this->order_status_id = $order_status_id;
		$this->is_community = I('request.is_community', 0);
		$this->headid = I('request.headid', 0);
		
		$open_feier_print = D('Home/Front')->get_config_by_name('open_feier_print');
		
		if( empty($open_feier_print) )
		{
			$open_feier_print = 0;
		}
		
		$this->open_feier_print = $open_feier_print;
		
		$is_can_look_headinfo = true;
		$is_can_nowrfund_order = true;
		
		$is_can_confirm_delivery = true;
		$is_can_confirm_receipt = true;
		
		
		$supply_can_look_headinfo = D('Home/Front')->get_config_by_name('supply_can_look_headinfo');
		
		$supply_can_nowrfund_order = D('Home/Front')->get_config_by_name('supply_can_nowrfund_order');
		
		
		$supply_can_confirm_delivery = D('Home/Front')->get_config_by_name('supply_can_confirm_delivery');
		
		$supply_can_confirm_receipt = D('Home/Front')->get_config_by_name('supply_can_confirm_receipt');
		
		
		
		if (defined('ROLE') && ROLE == 'agenter' )
		{
			if( isset($supply_can_look_headinfo) && $supply_can_look_headinfo == 2 )
			{
				$is_can_look_headinfo = false;
			}
			if( isset($supply_can_nowrfund_order) && $supply_can_nowrfund_order == 2 )
			{
				$is_can_nowrfund_order = false;
			}
			
			
			if( isset($supply_can_confirm_delivery) && $supply_can_confirm_delivery == 2 )
			{
				$is_can_confirm_delivery = false;
			}
			if( isset($supply_can_confirm_receipt) && $supply_can_confirm_receipt == 2 )
			{
				$is_can_confirm_receipt = false;
			}
	
		}
		
		$this->is_can_look_headinfo = $is_can_look_headinfo;
		$this->is_can_nowrfund_order = $is_can_nowrfund_order;
		$this->is_can_confirm_delivery = $is_can_confirm_delivery;
		$this->is_can_confirm_receipt = $is_can_confirm_receipt;
		$this->display();
	 }
	 
	 public function opremarksaler()
	{
		
		$opdata = $this->check_order_data();
		extract($opdata);

		if (IS_POST) {
			$remark = I('request.remark');
			
			M('lionfish_comshop_order')->where( array('order_id' => $item['order_id']) )->save( array('remarksaler' => $remark) );
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}

		$this->item = $item;
		$this->id = $item['order_id'];
		include $this->display();
	}
	private function check_order_data()
	{
		
	
		$id = I('request.id',0);
		
		$item = M('lionfish_comshop_order')->where( array('order_id' => $id) )->find();
		
		if (empty($item)) {
			
				show_json(0, '未找到订单!');
			
		}

		return array('id' => $id, 'item' => $item);
	}
	
	public function opsendcancel()
	{
		
		
		$opdata = $this->check_order_data();
		extract($opdata);
		
		
		$sendtype = I('request.sendtype','');
		$gpc = I('request.');

		if (($item['order_status_id'] != 4) ) {
			show_json(0, '订单未发货，不需取消发货！');
		}

		if (IS_POST) {
			
			$remark = trim($gpc['remark']);
			$data = array('express_time' => 0,'shipping_no' =>'','shipping_method' => 0);
			$data['order_status_id'] = 1;
			
			M('lionfish_comshop_order')->where( array('order_id' => $item['order_id']) )->save($data);
			
			$history_data = array();
			$history_data['order_id'] = $item['order_id'];
			$history_data['order_status_id'] = 1;
			$history_data['notify'] = 0;
			$history_data['comment'] = '订单取消发货 ID: ' . $item['order_id'] . ' 订单号: ' . $item['order_num_alias'];
			$history_data['date_added'] = time();
			
			M('lionfish_comshop_order_history')->add($history_data);
			
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}


		$sendgoods = array();
		$bundles = array();

		$this->id = $item['order_id'];
		$this->item = $item;
		$this->sendtype = $sendtype;

		include $this->display();
	}
	
	
	public function opchangeaddress()
	{
		$_GPC = I('request.');
		$opdata = $this->check_order_data();
		extract($opdata);
		
		
		$new_area = 0;
		$address_street = 0;

		$province_info = D('Home/Front')->get_area_info($item['shipping_province_id']);
		$city_info = D('Home/Front')->get_area_info($item['shipping_city_id']);
		$area_info = D('Home/Front')->get_area_info($item['shipping_country_id']);

		if (IS_POST) {
			$realname = $_GPC['realname'];
			$mobile = $_GPC['mobile'];
			$province = $_GPC['province'];
			$city = $_GPC['city'];
			$area = $_GPC['area'];
			$street = $_GPC['street'];
			$changead = intval($_GPC['changead']);
			$address = trim($_GPC['address']);

			
			
			
			if (!(empty($id))) {
				if (empty($realname)) {
					$ret = '请填写收件人姓名！';
					show_json(0,  array('msg' => $ret) );
				}


				if (empty($mobile)) {
					$ret = '请填写收件人手机！';
					show_json(0,  array('msg' => $ret) );
				}
				if ($changead) {
					if ($province == '请选择省份') {
						$ret = '请选择省份！';
						show_json(0,  array('msg' => $ret) );
					}
					if (empty($address)) {
						$ret = '请填写详细地址！';
						show_json(0,  array('msg' => $ret) );
					}
				}

				$address_array = array();
				$address_array['shipping_name'] = $realname;
				$address_array['shipping_tel'] = $mobile;
				
				if ($changead) {
					
					
					$province_info = D('Home/Front')->get_area_ninfo_by_name($province);
					$city_info = D('Home/Front')->get_area_ninfo_by_name($city);
					$area_info = D('Home/Front')->get_area_ninfo_by_name($area);
					
					$address_array['shipping_province_id'] = $province_info['id'];
					$address_array['shipping_city_id'] = $city_info['id'];
					$address_array['shipping_country_id'] = $area_info['id'];
			
					$address_array['shipping_address'] = $address;
				}
				
				M('lionfish_comshop_order')->where( array('order_id' => $id) )->save( $address_array );
				
				show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
			}
		}
		
		$this->id = $item['order_id'];
		$this->item = $item;
		$this->province_info = $province_info;
		$this->city_info = $city_info;
		$this->area_info = $area_info;
		
		$this->display();
	}
	
	//确认送达团长
	public function opsend_tuanz_over()
	{
	
		$opdata = $this->check_order_data();
		extract($opdata);
		//express_tuanz_time D('Home/Frontorder')->send_order_operate($order_info['order_id']);
		
		D('Seller/Order')->do_tuanz_over($item['order_id']);
		//D('Seller/Frontorder')->send_order_operate($item['order_id']);
		
		$history_data = array();
		$history_data['order_id'] = $item['order_id'];
		$history_data['order_status_id'] = 4;
		$history_data['notify'] = 0;
		$history_data['comment'] = '后台手动操作发货到团长';
		$history_data['date_added'] = time();
		
		M('lionfish_comshop_order_history')->add( $history_data );
		
		D('Home/Frontorder')->send_order_operate($item['order_id']);
		
		
		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	
	public function opprint()
	{
		$_GPC = I('request.');
		
		$order_id = $_GPC['id'];
	
		$print_model = D('Seller/Printaction');
		
		$result = $print_model->check_print_order( $order_id );
		
		if( $result['code'] == 1 )
		{
			M('lionfish_comshop_order')->where( array('order_id' => $order_id) )->save( array('is_print_suc' => 1) );
			
			show_json(1, array('msg' => '打印成功！' ) );
		}else{
			show_json(0,  array('msg' => $result['msg'] ) );
		}
		
	}
	
	public function oprefund_do()
	{
		$_GPC = I('request.');
		
		$opdata = $this->check_order_data();		
		extract($opdata);
		
		
		$id = $_GPC['id'];
		
		//付款总额
		$free_tongji = $opdata['item']['total']+$opdata['item']['shipping_fare']-$opdata['item']['voucher_credit']-$opdata['item']['fullreduction_money'];
		
		if( IS_POST )
		{
			$refund_money = isset($_GPC['refund_money']) && $_GPC['refund_money'] >0  ? $_GPC['refund_money']: 0;
			
			if($refund_money > $free_tongji){
					show_json(0, array('message' => '填写金额大于总退款金额' ) );
			}else{
				
				$weixin_model = D('Home/Weixin');
						
				$id = $_GPC['id'];
				
				$model = M('lionfish_comshop_order');
				//$model->startTrans();  // 开启事务
				$order_info = $model->where(array('order_id'=>$id))->find();

				if( $order_info['order_status_id'] != 7 )
				{
					$res = $weixin_model->refundOrder($id,$refund_money);
					$model->commit();  // 开启事务   
					if( $res['code'] == 0 )
					{
						show_json(0, array('message' => $res['msg']) );
					}else{
						
						
						//integral
						$order_info = M('lionfish_comshop_order')->where( array('order_num_alias' => $id ) )->find();
						
						$comment = '后台操作立即退款,退款金额:'.$refund_money.'元';
						
						if( $order_info['type'] == 'integral' )
						{
							
							if( $order_info['shipping_fare'] > 0 )
							{
								$comment = '后台操作立即退款,退款金额:'.$order_info['shipping_fare'].'元，积分:'.$order_info['total'];
							}else{
								$comment = '后台操作立即退款,退还积分:'.$order_info['total'];
							}
							//$comment = '后台操作立即退款,退款金额:'.$refund_money.'元';
						}
						
						$history_data = array();
						$history_data['order_id'] = $id;
						$history_data['order_status_id'] = 7;
						$history_data['notify'] = 0;
						$history_data['comment'] = $comment;
						$history_data['date_added'] = time();
						
						M('lionfish_comshop_order_history')->add($history_data);
						
						M('lionfish_comshop_order')->where( array('order_id' => $id) )->save( array('order_status_id' => 7) );
						//将所有在退款中的状态，全部重置成已退款成功
						M('lionfish_comshop_order_refund')->where( array('order_id' => $id, 'state' => 0) )->save( array('state' => 3) );
						
						//将退款中的 申请订单，全部改成已退款  
						
						$refund_all = M('lionfish_comshop_order_refund')->where( array('order_id' =>$id, 'state' => 0 ) )->select();
						
						if( !empty($refund_all) )
						{
							foreach( $refund_all as $val )
							{
								$ins_data = array();
								
								$ins_data['ref_id'] = $val['ref_id'];
								$ins_data['order_id'] = $val['order_id'];
								$ins_data['order_goods_id'] = $val['order_goods_id'];
								$ins_data['message'] = '平台同意退款   ,退款成功';
								$ins_data['type'] = 2;
								$ins_data['addtime'] = time();
								
								M('lionfish_comshop_order_refund_history')->add( $ins_data );
								
								M('lionfish_comshop_order_refund')->where( array('ref_id' => $val['ref_id']) )->save( array('state' => 3) );
								
							}
						}
						
						
						//$data[''] = isset($data['is_print_admin_cancleorder']) ? $data['is_print_admin_cancleorder'] : 0;
						$is_print_admin_cancleorder = D('Home/Front')->get_config_by_name('is_print_admin_cancleorder');
						
						if( isset($is_print_admin_cancleorder) && $is_print_admin_cancleorder == 1 )
						{
							D('Seller/Printaction')->check_print_order($id,'后台操作取消订单');
						}
					
					
						show_json(1, array('message' => '退款成功！') );
					}
					 
				}else{
					//$model->rollback();  // 回滚
					show_json(0,  array('message' => '请勿重复提交'));
				}
		
			}
			
		}
		
		$this->id = $id;
		$this->free_tongji = $free_tongji;
		$this->item = $item;
		
		$commiss_state = '未结算';
		
		$commiss_info = M('lionfish_community_head_commiss_order')->where( array('order_id' => $id, 'type' => 'orderbuy' )  )->find();
		
		if( !empty($commiss_info) && $commiss_info['state'] == 1 )
		{
			$commiss_state = '已结算';
		}
		
		$this->commiss_state = $commiss_state;
		
		$this->display();
		
	}
	
	
	function test()
	{
		//D('Seller/Printaction')->check_print_order(63); 
		// lionfish_comshop_order
		
		$shop_name = D('Home/Front')->get_config_by_name('shoname');
		
		
		$this->shop_name = $shop_name;
		$order_id = 174;
		
		$order_info = M('lionfish_comshop_order')->where( array('order_id' => $order_id) )->find();
		
		
		$order_goods = M('lionfish_comshop_order_goods')->where( array('order_id' => $order_id) )->select();
		
		$need_order_goods = array();
		
			
		foreach($order_goods as $key => $value)
		{
			$value['option_sku'] = D('Seller/Order')->get_order_option_sku($order_id, $value['order_goods_id']);
			
			$need_order_goods[$key] = $value;		
		}
		
		$province_info = D('Home/Front')->get_area_info($order_info['shipping_province_id']);
		$city_info = D('Home/Front')->get_area_info($order_info['shipping_city_id']);
		$area_info = D('Home/Front')->get_area_info($order_info['shipping_country_id']);
		$member = M('lionfish_comshop_member')->where( array('member_id' => $item['member_id'] ) )->find();	
		
		
		$this->province_info = $province_info;
		$this->city_info = $city_info;
		$this->area_info = $area_info;
		$this->member = $member;
		
		$this->order_info = $order_info;
		$this->need_order_goods = $need_order_goods;
		$this->display('forms');
	}
	
	/**
		打印配送单
	**/
	public function order_print_dan()
	{
		$order_id = I('get.id');
		
		$shop_name = D('Home/Front')->get_config_by_name('shoname');
		
		
		$this->shop_name = $shop_name;
		
		$order_info = M('lionfish_comshop_order')->where( array('order_id' => $order_id) )->find();
		
		
		$order_goods = M('lionfish_comshop_order_goods')->where( array('order_id' => $order_id) )->select();
		
		$need_order_goods = array();
		
			
		foreach($order_goods as $key => $value)
		{
			$value['option_sku'] = D('Seller/Order')->get_order_option_sku($order_id, $value['order_goods_id']);
			
			$need_order_goods[$key] = $value;		
		}
		
		$province_info = D('Home/Front')->get_area_info($order_info['shipping_province_id']);
		$city_info = D('Home/Front')->get_area_info($order_info['shipping_city_id']);
		$area_info = D('Home/Front')->get_area_info($order_info['shipping_country_id']);
		$member = M('lionfish_comshop_member')->where( array('member_id' => $order_info['member_id'] ) )->find();	
		
		
		$this->province_info = $province_info;
		$this->city_info = $city_info;
		$this->area_info = $area_info;
		$this->member = $member;
		
		$this->order_info = $order_info;
		$this->need_order_goods = $need_order_goods;
		$this->display('forms');
	}
	
	//配送团长
	public function opsend_tuanz()
	{
	
		$opdata = $this->check_order_data();
		extract($opdata);
		
		D('Seller/Order')->do_send_tuanz($item['order_id']);
		
		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	
	public function opsend()
	{
		$_GPC = I('request.');
		
		$opdata = $this->check_order_data();
		extract($opdata);

		if (empty($item['address_id'])) {
			show_json(0,  array('message' => '无收货地址，无法发货！'));
		}


		if ($item['order_status_id'] == 3) {
			show_json(0, array('message' => '订单未付款，无法发货！'));
		}


		if (IS_POST) {
			
			//express shipping_no  $express_info['name'] dispatchname
			
			if (!(empty($_GPC['shipping_no'])) && empty($_GPC['shipping_no'])) {
				show_json(0, array('message' => '请输入快递单号！')  );
			}
			
			if ( empty($_GPC['express']) ) {
				show_json(0, array('message' => '请选择快递公司！'));
			}
			
			
			if (!(empty($item['transid']))) {
			}

			$express_info = D('Seller/Express')->get_express_info($_GPC['express']);
			
			$time = time();
			$data = array(
				'shipping_method' => trim($_GPC['express']), 
				'shipping_no' => trim($_GPC['shipping_no']), 
				'dispatchname' => $express_info['name'], 
				'express_time' => $time
			);
			
			$data['order_status_id'] = 4;
			
			
			M('lionfish_comshop_order')->where( array('order_id' => $item['order_id']) )->save( $data );
			
			
		
			$history_data = array();
			$history_data['order_id'] = $item['order_id'];
			$history_data['order_status_id'] = 4;
			$history_data['notify'] = 0;
			$history_data['comment'] = '订单发货 ID: ' . $item['order_id'] . ' 订单号: ' . $item['order_num_alias'] . ' <br/>快递公司: ' . $express_info['name'] . ' 快递单号: ' . $_GPC['shipping_no'];
			$history_data['date_added'] = time();
			
			M('lionfish_comshop_order_history')->add($history_data);
				
			D('Home/Frontorder')->send_order_operate($item['order_id']);
				
			//TODO...发送已经发货的消息通知
			//m('notice')->sendOrderMessage($item['id']);
			//plog('order.op.send', '订单发货 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'] . ' <br/>快递公司: ' . $_GPC['expresscom'] . ' 快递单号: ' . $_GPC['expresssn']);
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}

		$noshipped = array();
		$shipped = array();

		$province_info = D('Home/Front')->get_area_info($item['shipping_province_id']);
		$city_info = D('Home/Front')->get_area_info($item['shipping_city_id']);
		$area_info = D('Home/Front')->get_area_info($item['shipping_country_id']);
		

		$order_goods = M()->query('select og.order_goods_id as id,og.name as title,og.goods_images as thumb from ' . 
						C('DB_PREFIX'). 'lionfish_comshop_order_goods as og  where og.order_id= '.$item['order_id']);
		
		

		$express_list = D('Seller/Express')->load_all_express();
		
		
		$this->province_info = $province_info;
		$this->city_info = $city_info;
		$this->area_info = $area_info;
		$this->order_goods = $order_goods;
		$this->item = $item;
		$this->express_list = $express_list;
		$this->id = $item['order_id'];
		
		$this->display();
	}
	
	// 11  已完成
	public function opfinish()
	{
		
		$opdata = $this->check_order_data();
		extract($opdata);
		
		
		M('lionfish_comshop_order')->where(array('order_id' => $item['order_id']) )->save( array('order_status_id' => 11, 
			'finishtime' => time()) );	
		
		$history_data = array();
		$history_data['order_id'] = $item['order_id'];
		$history_data['order_status_id'] = 11;
		$history_data['notify'] = 0;
		$history_data['comment'] = '后台操作，已完成' ;
		$history_data['date_added'] = time();
		
		M('lionfish_comshop_order_history')->add($history_data);
		
		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	
	public function oppay()
	{
		
		if (defined('ROLE') && ROLE == 'agenter' )
		{
			show_json(0, array('msg' => '您无此权限') );
		}
		
		$order_id = I('request.id');
		
		
		$res_arr = D('Seller/Order')->admin_pay_order($order_id);
		
		
		if( $res_arr['code'] == 0)
		{
			show_json(0, array('msg' => $res_arr['msg']) );
		}else{
			show_json(1,  array('url' => $_SERVER['HTTP_REFERER']));	
		}
	}
	
	public function detail()
	{
		$id = I('get.id');
		
		
		$item = M('lionfish_comshop_order')->where( array('order_id' => $id ) )->find();

		if($item['type'] == 'pintuan'){			
			
			$pin_order = M('lionfish_comshop_pin_order')->field('pin_id')->where( array('order_id' => $id ) )->find();
			
			$pin_id = $pin_order['pin_id'];	
			
			$this->pin_id = $pin_id;
		}
		
		$order_goods = array();

	
		
		$order_goods = M('lionfish_comshop_order_goods')->where( array('order_id' => $id) )->select();
		
		$need_order_goods = array();
		
		$shipping_fare = 0;
		$fullreduction_money = 0;
		$voucher_credit = 0;
		$total = 0;
			
		foreach($order_goods as $key => $value)
		{
			// lionfish_community_head_commiss_order
			
			$head_commission_order_info = M('lionfish_community_head_commiss_order')->where( array('order_goods_id' => $value['order_goods_id'],'order_id' => $item['order_id']) )->select();
			
			
			if( !empty($head_commission_order_info) )
			{
				foreach( $head_commission_order_info as  &$vv)
				{
					$head_info_tp = M('lionfish_community_head')->field('head_name')->where( array('id' => $vv['head_id'] ) )->find();
					
					$vv['head_name'] = $head_info_tp['head_name'];		
				}
				unset($vv);
			}
			
			if( $value['is_refund_state'] == 1 )
			{
				$refund_info = M('lionfish_comshop_order_refund')->where( array('order_id' => $id,'order_goods_id' => $value['order_goods_id'] ) )->find();
				
				$value['refund_info'] = $refund_info;
			}
			
			
			
			if( $item['is_commission'] == 1 )
			{
				$member_commission_list = M('lionfish_comshop_member_commiss_order')->where( array('order_goods_id' => $value['order_goods_id'],'order_id' => $item['order_id']) )->order('id asc')->select();
				
				if( !empty($member_commission_list) )
				{
					foreach( $member_commission_list as $kk => $vv )
					{
						$tmp_if = M('lionfish_comshop_member')->field('username')->where( array('member_id' => $vv['member_id'] ) )->find();
						
						$vv['username'] = $tmp_if['username'];
						
						$member_commission_list[$kk] = $vv;
					}
					
				}
				
				
				
				$value['member_commission_list'] = $member_commission_list;
			}
			
			
			$value['head_commission_order_info'] = $head_commission_order_info;
			
			
			$value['option_sku'] = D('Seller/Order')->get_order_option_sku($item['order_id'], $value['order_goods_id']);
			


			if (defined('ROLE') && ROLE == 'agenter' )
			{
				$supper_info = get_agent_logininfo();
				if($supper_info['id'] != $value['supply_id'])
				{
					continue;
				}
			}
					
			if( $value['supply_id'] > 0 )
			{
				$supply_info = D('Home/Front')->get_supply_info($value['supply_id']);
				$value['supply_name'] = $supply_info['shopname'];
				$value['supply_type'] = $supply_info['type'] == 1 ? '独立' :'自营';
			}else{
				$value['supply_name'] = '平台自营';
				$value['supply_type'] = '自营';
			}
			
			$shipping_fare += $value['shipping_fare'];
			$fullreduction_money += $value['fullreduction_money'];
			$voucher_credit += $value['voucher_credit'];
			$total += $value['total'];
			
			$need_order_goods[$key] = $value;		
		}
		
		if (defined('ROLE') && ROLE == 'agenter' )
		{
			$item['shipping_fare'] = $shipping_fare;
			$item['fullreduction_money'] = $fullreduction_money;
			$item['voucher_credit'] = $voucher_credit;
			$item['total'] = $total;
		}
			
		$order_goods = $need_order_goods;
					
		

		if (empty($item)) {
			$this->message('抱歉，订单不存在!', $_SERVER['HTTP_REFERER'], 'error');
		}

			
		$member = M('lionfish_comshop_member')->where( array('member_id' => $item['member_id'] ) )->find();	
		
		
		$province_info = D('Home/Front')->get_area_info($item['shipping_province_id']);
		$city_info = D('Home/Front')->get_area_info($item['shipping_city_id']);
		$area_info = D('Home/Front')->get_area_info($item['shipping_country_id']);
		
		$express_info = array();
		if( !empty($item['shipping_method']) )
		{
			$express_info = D('Seller/Express')->get_express_info($item['shipping_method']);
		}
		$this->express_info = $express_info;
		
		$coupon = array();
		//voucher_id voucher_credit
		//ims_ 
		if( $item['voucher_id'] > 0 )
		{
			$coupon = array();
			
			//$coupon = pdo_fetch("select * from ".tablename('lionfish_comshop_coupon_list')." where uniacid=:uniacid and id=:id ", 
			//array(':uniacid' => $_W['uniacid'], ':id' => $item['voucher_id']));
		}
		
		
		$this->id = $id;
		$this->item = $item;
		$this->order_goods = $order_goods;
		$this->member = $member;
		$this->province_info = $province_info;
		$this->city_info = $city_info;
		$this->area_info = $area_info;
		
		
		$history_list = M('lionfish_comshop_order_history')->where( array('order_id' => $id ) )->order('order_history_id asc')->select();
		
		$order_status_arr = D('Seller/Order')->get_order_status_name();
		
		//$order_history['order_status_id'] = 18;
		
		$order_status_arr[18] = '已结算';
		$history_list=$this->second_array_unique_bykey($history_list,'date_added');
		foreach( $history_list as  &$val )
		{
			$val['order_status_name'] = $order_status_arr[$val['order_status_id']];
		}
		unset($val);
		
		$this->history_list = $history_list;
		$this->order_status_arr = $order_status_arr;
		
		$is_can_look_headinfo = true;
		$is_can_nowrfund_order = true;
		
		$supply_can_look_headinfo = D('Home/Front')->get_config_by_name('supply_can_look_headinfo');
		
		$supply_can_nowrfund_order = D('Home/Front')->get_config_by_name('supply_can_nowrfund_order');
		
		if (defined('ROLE') && ROLE == 'agenter' )
		{
			if( isset($supply_can_look_headinfo) && $supply_can_look_headinfo == 2 )
			{
				$is_can_look_headinfo = false;
			}
			if( isset($supply_can_nowrfund_order) && $supply_can_nowrfund_order == 2 )
			{
				$is_can_nowrfund_order = false;
			}
		}
		
		$this->is_can_look_headinfo = $is_can_look_headinfo;
		$this->is_can_nowrfund_order = $is_can_nowrfund_order;
		
		$this->display();
	}
	
	
	public function refund_mult()
	{
		$_GPC = I('request.');
		
		$ids_arr = $_GPC['ids_arr'];
		
		$cache_key = md5(time().count($ids_arr).'_sendmulutrefund');
		
		$quene_order_list = array();
		
		//限定配送数组
		S('_multrefund_'.$cache_key, $ids_arr);
		
		$this->_GPC = $_GPC;
		$this->cache_key = $cache_key;
		
		$this->display();
	}
	
	
	public function refund_mult_do()
	{
		$_GPC = I('request.');
		
		
		$cache_key = $_GPC['cache_key'];
		
		$quene_order_list = S('_multrefund_'.$cache_key);
		
		$order_id = array_shift($quene_order_list);
		
		S('_multrefund_'.$cache_key, $quene_order_list);
		
		
		//...
		$order_info =  M('lionfish_comshop_order')->where( array('order_id' => $order_id ) )->find();
		
		$weixin_model = D('Home/Weixin');
		
		$title = '订单编号：'.$order_info['order_num_alias']." 处理成功，还剩余".count($quene_order_list)."个订单未处理";
		
		//order_status_id
		if( in_array($order_info['order_status_id'], array(1,4,6,10,11,12,14)) ){ 
			
			$res = $weixin_model->refundOrder($order_id);
				
			if( $res['code'] == 0 )
			{
				$title = '订单编号：'.$order_info['order_num_alias']." 退款失败，还剩余".count($quene_order_list)."个清单未处理";
			}else{
				
				$order_info = M('lionfish_comshop_order')->where( array('order_id' => $order_id ) )->find();
				
				$comment = '群接龙批量退款,全额退款';
				
				$order_history = array();
			
				$order_history['order_id'] = $order_id;
				$order_history['order_status_id'] = 7;
				$order_history['notify'] = 0;
				$order_history['comment'] =  $comment;
				$order_history['date_added'] = time();
			
				M('lionfish_comshop_order_history')->add( $order_history );
				
				M('lionfish_comshop_order')->where( array('order_id' => $order_id) )->save( array('order_status_id' => 7)  );
							
				$refund_all = M('lionfish_comshop_order_refund')->where( array('order_id' => $order_id, 'state' => 0 ) )->select();
						
				if( !empty($refund_all) )
				{
					foreach( $refund_all as $val )
					{
						$ins_data = array();
						
						$ins_data['ref_id'] = $val['ref_id'];
						$ins_data['order_id'] = $val['order_id'];
						$ins_data['order_goods_id'] = $val['order_goods_id'];
						$ins_data['message'] = '群接龙批量退款 ,退款成功';
						$ins_data['type'] = 2;
						$ins_data['addtime'] = time();
						
						M('lionfish_comshop_order_refund_history')->add( $ins_data );
						
						M('lionfish_comshop_order_refund')->where( array('ref_id' => $val['ref_id']) )->save( array('state' => 3) );	
					}
				}
				
				$is_print_admin_cancleorder = D('Home/Front')->get_config_by_name('is_print_admin_cancleorder');
				
				if( isset($is_print_admin_cancleorder) && $is_print_admin_cancleorder == 1 )
				{
					D('Seller/Printaction')->check_print_order($id,'群接龙后台取消订单');
				}
			}
			
		}
		
		if( empty($quene_order_list) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}
		
		//清单编号   
		
		echo json_encode( array('code' => 0, 'msg' => $title ) );
		die();
	}
	
	
	
	
	public function opreceive()
	{
		
		$opdata = $this->check_order_data();
		extract($opdata);
		
		//pdo_update('lionfish_comshop_order', array('order_status_id' => 6, 'receive_time' => time()), array('order_id' => $item['order_id'], 'uniacid' => $_W['uniacid']));
		
		D('Seller/Order')->receive_order($item['order_id']);
		
		M('lionfish_comshop_order_history')->where( array('order_id' => $item['order_id'],'order_status_id' => 6) )->save( array( 'comment' => '后台操作，确认收货') );
		
		
		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	
	 public function orderaftersales()
	{
		//$_GPC['order_status_id'] = 12;
		
		$gpc = I('request.');
		$this->gpc = $gpc;
		$this->_GPC = $gpc;
		$time = I('request.time');
		
		$starttime = isset($time['start']) ? strtotime($time['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($time['end']) ? strtotime($time['end']) : strtotime(date('Y-m-d'.' 23:59:59'));
		
		
		$this->searchfield = I('request.searchfield','');
		$this->keyword = I('request.keyword','');
		$this->searchtime = I('request.searchtime','');
		$this->delivery = I('request.delivery','');
		$this->starttime = $starttime;
		$this->endtime = $endtime;
		$this->time = $time;
		
		
		$order_status_arr =  D('Seller/Order')->get_order_status_name();
		$this->order_status_arr = $order_status_arr;
		
		$need_data = D('Seller/Order')->load_afterorder_list();//改造原来的加载方法
		
		$this->need_data = $need_data;
		
		$cur_controller = 'order/order';
		$total = $need_data['total'];
		$total_money = $need_data['total_money'];
		$list = $need_data['list'];
		$pager = $need_data['pager'];
		$all_count = $need_data['all_count'];
		$count_status_1 = $need_data['count_status_1'];
		$count_status_3 = $need_data['count_status_3'];
		$count_status_4 = $need_data['count_status_4'];
		$count_status_5 = $need_data['count_status_5'];
		$count_status_7 = $need_data['count_status_7'];
		$count_status_11 = $need_data['count_status_11'];
		$count_status_14 = $need_data['count_status_14'];
		
		$this->cur_controller = $cur_controller;
		$this->total = $total;
		$this->total_money = $total_money;
		$this->list = $list;
		$this->pager = $pager;
		$this->all_count = $all_count;
		$this->count_status_1 = $count_status_1;
		$this->count_status_3 = $count_status_3;
		$this->count_status_4 = $count_status_4;
		$this->count_status_5 = $count_status_5;
		$this->count_status_7 = $count_status_7;
		$this->count_status_11 = $count_status_11;
		$this->count_status_14 = $count_status_14;
		
		
		$open_feier_print =  D('Home/Front')->get_config_by_name('open_feier_print');
		
		if( empty($open_feier_print) )
		{
			$open_feier_print = 0;
		}
		
		$this->open_feier_print = $open_feier_print;
		
		//退款状态：0申请中，1商家拒绝，2平台介入，3退款成功，4退款失败,5:撤销申请
		$order_refund_state = array(0=>'申请中',1=>'商家拒绝', 2=>'平台介入',3=>'退款成功',4=>'退款失败',5=>'撤销申请');
		
		$this->order_refund_state = $order_refund_state;
		
		$is_can_look_headinfo = true;
		$is_can_nowrfund_order = true;
		
		$supply_can_look_headinfo = D('Home/Front')->get_config_by_name('supply_can_look_headinfo');
		
		$supply_can_nowrfund_order = D('Home/Front')->get_config_by_name('supply_can_nowrfund_order');
		
		if (defined('ROLE') && ROLE == 'agenter' )
		{
			if( isset($supply_can_look_headinfo) && $supply_can_look_headinfo == 2 )
			{
				$is_can_look_headinfo = false;
			}
			if( isset($supply_can_nowrfund_order) && $supply_can_nowrfund_order == 2 )
			{
				$is_can_nowrfund_order = false;
			}
		}
		
		$this->is_can_look_headinfo = $is_can_look_headinfo;
		$this->is_can_nowrfund_order = $is_can_nowrfund_order;
		
		$this->display();
	}
	
	public function oprefund_doform()
	{
		$_GPC = I('request.');
		
		$ref_id = $_GPC['ref_id'];

		$refund_info = M('lionfish_comshop_order_refund')->where( array('ref_id' => $ref_id ) )->find();
		
		$this->ref_id = $ref_id;
		$this->refund_info = $refund_info;
		
		if (IS_POST) {
			
			$order_history = array();
			$order_history['order_id'] = $refund_info['order_id'];
		
			$order_history['order_status_id'] = 0;
			$order_history['notify'] = 0;
			$order_history['comment'] = '';
			$order_history['date_added'] = time();
			
			
			$remarkrefund = $_GPC['remarkrefund'];
			$is_forbidden = $_GPC['is_forbidden'];
			
			$cansub = $_GPC['cansub'];
			
			if( isset($_GPC['is_forbidden']) && $_GPC['is_forbidden'] > 0 )
			{
				//添加
				$refund_disable = M('lionfish_comshop_order_refund_disable')->where( array('ref_id' => $ref_id ) )->find();
				
				if( empty($refund_disable) )
				{
					//插入
					$ins_data = array();
					$ins_data['ref_id'] = $ref_id;
					$ins_data['order_id'] = $refund_info['order_id'];
					$ins_data['order_goods_id'] = $refund_info['order_goods_id'];
					$ins_data['addtime'] = time();
					
					M('lionfish_comshop_order_refund_disable')->add($ins_data);
				}
				
			}else{
				//删除
				M('lionfish_comshop_order_refund_disable')->where( array('ref_id' => $ref_id ) )->delete();
				
			}
			
			if($cansub == 1)
			{
				//确认退款 remarkrefund  
				
				$weixin_model = D('Home/Weixin');
				
				$order_refund = M('lionfish_comshop_order_refund')->field('ref_money')->where( array('ref_id' => $ref_id ) )->find();
				
				$res = $weixin_model->refundOrder($refund_info['order_id'], $order_refund['ref_money'],$_W['uniacid'],$refund_info['order_goods_id']);
				
				
				if($res['code'] == 1)
				{
					$order_history['order_status_id'] = 7;
					$order_history['comment'] = '商家同意退款';
					
					M('lionfish_comshop_order_history')->add( $order_history );
					
					$order_refund_history = array();
					$order_refund_history['ref_id'] = $ref_id;
					$order_refund_history['order_id'] = $refund_info['order_id'];
					$order_refund_history['order_goods_id'] = $refund_info['order_goods_id'];
					
					$order_refund_history['message'] = '平台同意退款'.' '.$remarkrefund.'  ,退款成功';
					$order_refund_history['type'] = 2;
					$order_refund_history['addtime'] = time();
					
					M('lionfish_comshop_order_refund_history')->add( $order_refund_history );
					
					//通过 lionfish_comshop_order_refund
					
					M('lionfish_comshop_order_refund')->where( array('ref_id' => $ref_id) )->save( array('state' => 3,'modify_time' => time(),'remarkrefund' => $remarkrefund) );
					
					show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
				}else{
					
					if( empty($res['msg']) )
					{
						$res['msg'] = '请检查商户号与cert证书';
					}
					
					$order_refund_history = array();
					$order_refund_history['ref_id'] = $ref_id;
					$order_refund_history['order_id'] = $refund_info['order_id'];
					$order_refund_history['order_goods_id'] = $refund_info['order_goods_id'];
					
					$order_refund_history['message'] = '平台同意退款'.' '.$remarkrefund.'  ,但是退款失败：'.$res['msg'];
					$order_refund_history['type'] = 2;
					$order_refund_history['addtime'] = time();
					
					M('lionfish_comshop_order_refund_history')->add( $order_refund_history );
					
					show_json(0, array('msg' => $res['msg']) );
				}
				
			}else if($cansub == 2){
				//拒绝退款
				$order_refund_history = array();
				$order_refund_history['ref_id'] = $ref_id;
				$order_refund_history['order_id'] = $refund_info['order_id'];
				$order_refund_history['order_goods_id'] = $refund_info['order_goods_id'];
				
				$order_refund_history['message'] = '平台拒绝退款'.' '.$remarkrefund;
				$order_refund_history['type'] = 2;
				$order_refund_history['addtime'] = time();
				
				M('lionfish_comshop_order_refund_history')->add( $order_refund_history );
				
				M('lionfish_comshop_order_refund')->where( array('ref_id' => $ref_id) )->save( array('state' => 1,'modify_time' => time(),'remarkrefund' => $remarkrefund ) );
				
				$item = M('lionfish_comshop_order')->field('order_status_id,last_refund_order_status_id')->where( array('order_id' => $refund_info['order_id'] ) )->find();
				
				
				//如果是部分退款，那么就不是12了
				if( $item['order_status_id'] == 12)
				{
					$order_history['order_status_id'] = 12;
					if( $item['last_refund_order_status_id'] > 0 )
					{
						$order_history['order_status_id'] = $item['last_refund_order_status_id'];
						
						M('lionfish_comshop_order')->where( array('order_id' => $refund_info['order_id'] ) )->save( array('order_status_id' => $item['last_refund_order_status_id']) );
						
						$order_history['order_status_id'] = $item['last_refund_order_status_id'];
					}			
				}
				
				if( !empty($refund_info['order_goods_id']) && $refund_info['order_goods_id'] > 0 )
				{
					
					M('lionfish_comshop_order_goods')->where( array('order_goods_id' => $refund_info['order_goods_id'] ) )->save( array('is_refund_state' => 0) );
					
				}
				
				//拒绝  order_status_id
				$order_history['comment'] = '商家拒绝退款，订单回退上一状态';
				
				M('lionfish_comshop_order_history')->add( $order_history );
				
				show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
				
			}
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}

		
		
		$this->display();
	}
	
	
	public function oprefund()
	{
		$_GPC = I('request.');
		
		$opdata = $this->check_order_data();
		extract($opdata);
		
		
		$ref_id = $_GPC['ref_id'];
		
		$ref_info = M('lionfish_comshop_order_refund')->where( array('ref_id' => $ref_id ) )->find();
		
		
		$step_array = array();
		$step_array[1]['step'] = 1;
		$step_array[1]['title'] = '客户发起退款';
		$step_array[1]['time'] = $ref_info['addtime'];
		$step_array[1]['done'] = 1;
		$step_array[2]['step'] = 2;
		$step_array[2]['title'] = '平台处理维权申请';
		$step_array[2]['done'] = 0;
		$step_array[2]['time'] = '';
		$step_array[3]['step'] = 3;
		$step_array[3]['done'] = 0;
		$step_array[3]['title'] = '商家处理退款完成';
		$step_array[3]['time'] = '';

		
		$ref_id = I('request.ref_id');
		$this->ref_id = $ref_id;
		
		$this->ref_info = $ref_info;
		
		$order_goods_id = $ref_info['order_goods_id'];
		
		if( !empty($order_goods_id) && $order_goods_id > 0 )
		{
			$goods = M('lionfish_comshop_order_goods')->where( array('order_goods_id' => $order_goods_id, 'order_id' => $id ) )->select();
		}else{
			
			$goods = M('lionfish_comshop_order_goods')->where( array('order_id' => $id ) )->select();
		}
		
		
		$total_fare = 0;
		
		$total_shipping_fare = 0;
		$total_voucher_credit =0;
		$total_fullreduction_money = 0;
		$total_total_fare = 0;
		
		$total_score_for_money = 0;
		
		/**
			php echo number_format($item['total']+$item['shipping_fare']-$item['voucher_credit']-$item['fullreduction_money'],2)
		**/
		
		
		foreach($goods as &$value)
		{
			$value['option_sku'] = D('Seller/Order')->get_order_option_sku($item['order_id'], $value['order_goods_id']);
			

			$total_fare += $value['total'];
			$total_shipping_fare += $value['shipping_fare'];
			$total_voucher_credit += $value['voucher_credit'];
			$total_fullreduction_money += $value['fullreduction_money'];
			$total_score_for_money += $value['score_for_money'];
			
			$total_total_fare += $value['total']+$value['shipping_fare']-$value['voucher_credit']-$value['fullreduction_money']-$value['score_for_money'];
						
		}
		$this->goods = $goods;
		
		$this->total_fare = $total_fare;
		$this->total_shipping_fare = $total_shipping_fare;
		$this->total_voucher_credit = $total_voucher_credit;
		$this->total_fullreduction_money = $total_fullreduction_money;
		$this->total_score_for_money = $total_score_for_money;
		$this->total_total_fare = $total_total_fare;

		unset($r);
		$item['goods'] = $goods;
		
		$member = M('lionfish_comshop_member')->where( array('member_id' => $item['member_id']) )->find();
		
		
		$this->member = $member;
		
	
		$express_list = array();
		
		$r_type = array(1=>'仅退款',2 => '退款退货');
		//ims_
		$this->r_type =$r_type;		
		
		$order_refund = M('lionfish_comshop_order_refund')->where( array('ref_id' => $ref_id) )->find();	
	
		
		$this->order_refund = $order_refund;
		
		if($order_refund['modify_time'] != 0 && $order_refund['state']== 3)
		{
			$step_array[3]['done'] = 1;
			$step_array[3]['time'] = $order_refund['modify_time'];
		}
		
		
		
		$refund_imgs = M('lionfish_comshop_order_refund_image')->where( array('ref_id' => $order_refund['ref_id'] ) )->select();
		
		$this->refund_imgs = $refund_imgs;
		
		/**
		if( !empty($order_goods_id) && $order_goods_id > 0 )
		{
			$order_refund_history = M('lionfish_comshop_order_refund_history')->where( array('order_id' =>$id,'order_goods_id' => $order_goods_id ) )->order('addtime asc')->select();
		}
		else{
			
			$order_refund_history = M('lionfish_comshop_order_refund_history')->where( array('order_id' =>$id ) )->order('addtime asc')->select();
		}
		**/
		$order_refund_history = M('lionfish_comshop_order_refund_history')->where( array('ref_id' => $ref_id ) )->order('addtime asc')->select();
		
		$i = 1;
		
		foreach($order_refund_history as $key => $val)
		{
			if( $i == 1 && $val['type'] == 2 )
			{
				$step_array[2]['done'] = 1;
				$step_array[2]['time'] = time();
				$i++;
			}
			$val['type'] = $val['type'] == 1 ?'用户反馈':'商家反馈';
			
				
			switch($val['type'])
			{
				case 1:
						$val['type'] = '用户反馈';
						break;
				case 2: 
						$val['type'] = '商家反馈';
						break;
				case 3: 
						$val['type'] = '平台反馈';
						break;
				
			}
			
			
			$order_refund_history_image = M('lionfish_comshop_order_refund_history_image')->where( array('orh_id' => $val['id']) )->select();
			
			if(!empty($order_refund_history_image))
			{
				foreach($order_refund_history_image as $kk => $vv)
				{
					$vv['thumb_image'] =  resize ($vv['image'], 200,200);
					$order_refund_history_image[$kk] = $vv;
				}
			}
			$val['order_refund_history_image'] = $order_refund_history_image;
			$order_refund_history[$key] = $val;
		}
		
		$this->step_array = $step_array;
		
		$this->order_refund_history = $order_refund_history;
		$this->item = $item;
		$this->display();	
	}
	
	
	public function oprefund_submit()
	{
		$gpc = I('request.');
		
		$opdata = $this->check_order_data();
		extract($opdata);
		
		if (IS_POST) {
			
			$id = $gpc['id'];
			//refundstatus  message refundcontent
			
			$refundstatus = $gpc['refundstatus'];
			$message = $gpc['message'];
			$refundcontent = $gpc['refundcontent'];
			
			$ref_id = $gpc['refundid'];
			
			/**
			int(37)
			string(1) "1"
			string(0) ""
			string(15) "天天来退货"
			**/
			//1 ,3
			
			$comment = '';
			
			switch( $refundstatus )
			{
				case 1:
					$comment = $refundcontent;
					break;
				case 3:
					$comment = $message;
					break;
			}
			
			$ref_info = M('lionfish_comshop_order_refund')->where( array('ref_id' => $ref_id ) )->find();
			
			$result = array('code' =>1);
		
			$order_refund_history = array();
			$order_refund_history['order_id'] = $ref_info['order_id'];
			$order_refund_history['order_goods_id'] = $ref_info['order_goods_id'];
			$order_refund_history['message'] = htmlspecialchars($comment);
			$order_refund_history['type'] = 2;
			$order_refund_history['addtime'] = time();
			
			M('lionfish_comshop_order_refund_history')->add($order_refund_history);
			
			
			
			$order_history = array();
			$order_history['order_id'] = $id;
			$order_history['order_status_id'] = 0;
			$order_history['notify'] = 0;
			$order_history['comment'] = '';
			$order_history['date_added'] = time();
			
			if($refundstatus ==1)
			{
				M('lionfish_comshop_order_refund')->where( array('ref_id' => $ref_id) )->save( array('state' => 1) );
				
				//id item $order_info  $item
				
				$item = M('lionfish_comshop_order')->field('order_status_id,last_refund_order_status_id')->where( array('order_id' => $id ) )->find();		
				
				
				//如果是部分退款，那么就不是12了
				if( $item['order_status_id'] == 12)
				{
					$order_history['order_status_id'] = 12;
					if( $item['last_refund_order_status_id'] > 0 )
					{
						$order_history['order_status_id'] = $item['last_refund_order_status_id'];
						
						M('lionfish_comshop_order')->where( array('order_id' => $id) )->save(  array('order_status_id' =>$item['last_refund_order_status_id'] ) );	
					
						$order_history['order_status_id'] = $item['last_refund_order_status_id'];
					}
						
				}
				
				if( !empty($ref_info['order_goods_id']) && $ref_info['order_goods_id'] > 0 )
				{
						M('lionfish_comshop_order_goods')->where( array('order_goods_id' => $ref_info['order_goods_id'] ) )->save( array('is_refund_state' =>0 ) );	
				}
				
				//拒绝  order_status_id
				$order_history['comment'] = '商家拒绝退款，订单回退上一状态';
				
				M('lionfish_comshop_order_history')->add( $order_history );
				
				show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
			} else {
				
				$weixin_model = D('Home/Weixin');
				
				$order_refund = M('lionfish_comshop_order_refund')->field('ref_money')->where( array('ref_id' => $ref_id) )->find();	  
				
			
				$res = $weixin_model->refundOrder($id, $order_refund['ref_money'],0,$ref_info['order_goods_id']);
				
				
				//array('code' => 0, 'msg' => $res['err_code_des']);
				if($res['code'] == 1)
				{
					$order_history['order_status_id'] = 7;
					$order_history['comment'] = '商家同意退款';
					
					M('lionfish_comshop_order_history')->add( $order_history );
				
					//通过 lionfish_comshop_order_refund
					
					M('lionfish_comshop_order_refund')->where( array('ref_id' => $ref_id) )->save( array('state' => 3) );
				
					show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
				}else{
					if( empty($res['msg']) )
					{
						$res['msg'] = '请检查商户号与cert证书';
					}
					
					show_json(0, array('message' => $res['msg']) );
				}
			}
			
			
		}
		
		$r_type = array(1=>'仅退款',2 => '退款退货');
		//ims_ 
		
		$order_refund = M('lionfish_comshop_order_refund')->where( array('order_id' => $id) )->find();
		
		$refund_imgs = M('lionfish_comshop_order_refund_image')->where( array('ref_id' => $order_refund['ref_id']) )->find();
		
		$order_refund_history = M('lionfish_comshop_order_refund_history')->where( array('order_id' => $id)  )->order('addtime asc')->select();
		
		foreach($order_refund_history as $key => $val)
		{
			$val['type'] = $val['type'] == 1 ?'用户反馈':'商家反馈';
			switch($val['type'])
			{
				case 1:
						$val['type'] = '用户反馈';
						break;
				case 2: 
						$val['type'] = '商家反馈';
						break;
				case 3: 
						$val['type'] = '平台反馈';
						break;
				
			}
			
			
			$order_refund_history_image = M('lionfish_comshop_order_refund_history_image')->where( array('orh_id' => $val['id']) )->select();							
			
			if(!empty($order_refund_history_image))
			{
				foreach($order_refund_history_image as $kk => $vv)
				{
					$vv['thumb_image'] =  resize ($vv['image'], 200,200);
					$order_refund_history_image[$kk] = $vv;
				}
			}
			$val['order_refund_history_image'] = $order_refund_history_image;
			$order_refund_history[$key] = $val;
		}
		
		$this->order_refund = $order_refund;
		$this->r_type = $r_type;
		$this->item = $item;
		$this->display();	
	}
	public function ordercomment_config()
	{
		
		
		if (IS_POST) {
			$data = array();
			
			$data = I('request.data');
			$data['open_comment_shenhe'] = trim($data['open_comment_shenhe']);
			
			D('Seller/Config')->update($data);
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		$data = D('Seller/Config')->get_all_config();
		
		$this->data = $data;
		$this->display();
	}
	
	public function ordercomment()
	{
		
		$condition = ' 1 ';//0
		$pindex = I('request.page', 1);
		$psize = 20;

		
		$keyword = I('request.keyword');
		$this->keyword = $keyword;
		
		if (!empty($keyword)) {
			$condition .= ' and content like '.'"%' . $keyword . '%" ';
		}

		
		
		$label = M()->query('SELECT * FROM ' . C('DB_PREFIX'). "lionfish_comshop_order_comment                 
				WHERE  " . $condition . ' order by comment_id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);
				
		
		$total = M('lionfish_comshop_order_comment')->where($condition)->count();
		
		$pager = pagination2($total, $pindex, $psize);
		
		$this->pager = $pager;
		$this->total = $total;
		$this->label = $label;
		$this->display(); 
	}
	
	public function deletecomment()
	{
		
		$id = I('request.id');

		if (empty($id)) {
			$ids = I('request.ids');
			$id = (is_array($ids) ? implode(',', $ids) : 0);
		}

		$items = M('lionfish_comshop_order_comment')->field('comment_id')->where( array('comment_id' => array('in',$id) ) )->select();	

		if (empty($item)) {
			$item = array();
		}

		foreach ($items as $item) {
			M('lionfish_comshop_order_comment')->where( array('comment_id' => $item['comment_id']) )->delete();
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	
	public function commentstate()
	{
		
		$id =  I('request.id');

		if (empty($id)) {
			$ids = I('request.ids');
			$id = (is_array($ids) ? implode(',', $ids) : 0);
		}

		$items = pdo_fetchall('SELECT comment_id FROM ' . tablename('lionfish_comshop_order_comment') . ' 
					WHERE comment_id in( ' . $id . ' ) ' );
		
		$items = M('lionfish_comshop_order_comment')->where( array() )->select();	

		if (empty($item)) {
			$item = array();
		}

		foreach ($items as $item) {
			pdo_update('lionfish_comshop_order_comment', array('state' => intval($_GPC['state'])), array('comment_id' => $item['comment_id']));
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		
	}
	
	public function config()
	{
		if (IS_POST) {
			$data = array();
			
			$data = I('request.data');
			$data['open_auto_receive'] = trim($data['open_auto_receive']);
			$data['shop_limit_buy_distance'] = trim($data['shop_limit_buy_distance']);
			$data['is_open_order_message'] = trim($data['is_open_order_message']);
			$data['is_hidden_orderlist_phone'] = trim($data['is_hidden_orderlist_phone']);
			$data['order_pay_after_share_img'] = save_media($data['order_pay_after_share_img']);
			
			//-----begin  
			
			if( $data['shop_limit_buy_distance'] == 1)
			{
				if( empty($data['shop_buy_distance']) || $data['shop_buy_distance'] <= 0  )
				{
					show_json(0, array('message' => '开启限制购买距离，购买距离不能为空') );
				}
			}
			if( $data['open_auto_delete'] == 1)
			{
				if( empty($data['auto_cancle_order_time']) || $data['auto_cancle_order_time'] <= 0  )
				{
					show_json(0, array('message' => '开启自动取消订单，自动取消订单时间不能为空') );
				}
			}
			
			//open_aftersale   
			if( $data['open_auto_recive_order'] == 1)
			{
				if( empty($data['auto_recive_order_time']) || $data['auto_recive_order_time'] <= 0  )
				{
					show_json(0, array('message' => '开启系统自动签收，自动签收天数不能为空') );
				}
			}
			
			
			$open_aftersale = isset($data['open_aftersale']) ? $data['open_aftersale']:0;
			
			$open_aftersale_time = isset($data['open_aftersale_time']) ? $data['open_aftersale_time']:0;
			
			if( $open_aftersale == 1 && ($open_aftersale_time ==0 || empty($open_aftersale_time) ) )
			{
				show_json(0, array('message' => '开启售后期，请填写售后期天数') );
			}
			
			
			if( $data['open_redis_server'] == 1)
			{
				if( empty($data['redis_host']))
				{
					show_json(0, array('message' => '开启redis服务，redis-host不能为空') );
				}
				if( empty($data['redis_port']))
				{
					show_json(0, array('message' => '开启redis服务，redis-port不能为空') );
				}
			}
			
			
			$open_feier_print = isset($data['open_feier_print']) ? $data['open_feier_print']:0;
			if(empty($open_feier_print) || $open_feier_print == 0)
			{
				$data['open_feier_print'] = $open_feier_print;
				
			}else if($open_feier_print == 1){
				$feier_print_sn = isset($data['feier_print_sn']) ? $data['feier_print_sn']:'';
				$feier_print_key = isset($data['feier_print_key']) ? $data['feier_print_key']:'';
				
				$data['open_feier_print'] = $open_feier_print;
				$data['feier_print_sn'] = $feier_print_sn;
				$data['feier_print_key'] = $feier_print_key;
				
				$feier_print_sn_old_arr = M('lionfish_comshop_config')->where( array('name' => 'feier_print_sn') )->find();
				
				$feier_print_sn_old = $feier_print_sn_old_arr['value'];
				
				$feier_print_key_old_arr = M('lionfish_comshop_config')->where( array('name' => 'feier_print_key') )->find();
				
				$feier_print_key_old = $feier_print_key_old_arr['value'];
				
				if($feier_print_sn_old != $feier_print_sn || $feier_print_key_old != $feier_print_key)
				{
					//开始添加打印机 
					//printaction
					$print_model = D('Seller/Printaction');
					$snlist = "{$feier_print_sn}#{$feier_print_key}";
					
					$print_model->addprinter($snlist);
					
				}
				//...todo测试订单自动打印
			}else if($open_feier_print == 2){
				$yilian_machine_code = isset($data['yilian_machine_code']) ? $data['yilian_machine_code']:'';
				
				$yilian_msign = isset($data['yilian_msign']) ? $data['yilian_msign']:'';
				$yilian_client_id = isset($data['yilian_client_id']) ? $data['yilian_client_id']:'';
				$yilian_client_key = isset($data['yilian_client_key']) ? $data['yilian_client_key']:'';
				
				
				$data['open_feier_print'] = $open_feier_print;
				$data['yilian_machine_code'] = $yilian_machine_code;
				$data['yilian_msign'] = $yilian_msign;
				$data['yilian_client_id'] = $yilian_client_id;
				$data['yilian_client_key'] = $yilian_client_key;
				
				$yilian_client_id_old = D('Home/Front')->get_config_by_name('yilian_client_id'); 
				
				$yilian_machine_code_old = D('Home/Front')->get_config_by_name('yilian_machine_code');
				
				$yilian_msign_old = D('Home/Front')->get_config_by_name('yilian_msign');  
				
				
				
				if(true || $yilian_client_id != $yilian_client_id_old || $yilian_machine_code_old != $yilian_machine_code || $yilian_msign_old != $yilian_msign)
				{
					
					//开始添加打印机 
					//printaction
					$print_model =  D('Seller/Printaction');
					
					$res = $print_model->addyilianyunprinter($yilian_client_id,$yilian_client_key,$yilian_machine_code, $yilian_msign );
					
					if($res != 0)
					{
						show_json(0, array('msg' => '添加易联云打印机失败！'));
					}
				}
				
				//...todo测试订单自动打印
			
			}
			//----end
			
			$data['is_print_cancleorder'] = isset($data['is_print_cancleorder']) ? $data['is_print_cancleorder'] : 0;
			$data['is_print_admin_cancleorder'] = isset($data['is_print_admin_cancleorder']) ? $data['is_print_admin_cancleorder'] : 0;
			$data['is_print_dansupply_order'] = isset($data['is_print_dansupply_order']) ? $data['is_print_dansupply_order'] : 0;
			
			
			
			
			//----------redis begin 
			$data['open_redis_server'] = intval($data['open_redis_server']);
			if($data['open_redis_server'] == 1 && !class_exists('Redis')){
				$data['open_redis_server'] = 0;
			}
			//----------redis end
			
			D('Seller/Config')->update($data);
			
			//将商品库存写入redis
			if($data['open_redis_server'] == 1 )
				D('Seller/Redisorder')->sysnc_allgoods_total();
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		$data = D('Seller/Config')->get_all_config();
		
		$this->data = $data;
		
		$this->display();
		
	}
	 
	 public function opclose()
	{
	
		$opdata = $this->check_order_data();
		extract($opdata);

		if ($item['order_status_id'] == 5) {
			show_json(0, '订单已关闭，无需重复关闭！');
		}
		 else if (3 != $item['order_status_id']) {
			show_json(0, '订单已付款，不能关闭！');
		}


		if (IS_POST) {
			
			
			//load_model_class('frontorder')->cancel_order($item['order_id']);
			D('Home/Frontorder')->cancel_order($item['order_id'], false, '后台操作，取消订单');
			
			
			/**
			
			$time = time();

			pdo_update('lionfish_comshop_order', array('order_status_id' => 5, 'canceltime' => $time),  array('order_id' => $item['order_id'], 'uniacid' => $_W['uniacid'])) ;

			//'remarkclose' => $_GPC['remark']),
			
			$history_data = array();
			$history_data['uniacid'] = $_W['uniacid'];
			$history_data['order_id'] = $item['order_id'];
			$history_data['order_status_id'] = 5;
			$history_data['notify'] = 0;
			$history_data['comment'] = '后台操作，取消订单' ;
			$history_data['date_added'] = time();
			
			pdo_insert('lionfish_comshop_order_history', $history_data);
			
			**/

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
			
		}

		$this->item = $item;
		 $this->display();
	}
	
	//--begin
	public function ordersendall()
	{
		$_GPC = I('request.');
		
		$express_list = D('Seller/Express')->load_all_express();
		
		
		
		if( IS_POST )
		{
			
			$type = isset($_GPC['type']) && !empty($_GPC['type']) ? $_GPC['type']:'normal';
			
			
			$fext = substr($_FILES['excelfile']['name'], strrpos($_FILES['excelfile']['name'], '.') + 1); 
			  
			
			
			
			$express = trim($_GPC['express']);
			$expresscom = trim($_GPC['expresscom']);
			
			
			if( $fext == 'csv' )
			{
				$file_name = $_FILES['excelfile']['tmp_name'];
				$file = fopen($file_name,'r');
				
				$rows = array();
				$i =0;
				while ($data = fgetcsv($file)) { 
					
					$rows[] = eval('return '.iconv('gbk','utf-8',var_export($data,true)).';');
					
				}
				
				//var_dump( $rows );
				//die();
			}else{
				
				$rows = D('Seller/Excel')->import('excelfile');
			}
			
			$num = count($rows);
			$time = time();
			
			$express_arr = array();
			
			foreach($express_list as $val)
			{
				$express_arr[ $val['id'] ] = $val['name'];
			}
			
			$i = 0;
			$err_array = array();
			
			$quene_order_list = array();
			
			$cache_key = md5(time().count($rows));
			
			$j =0;
			foreach ($rows as $rownum => $col) 
			{
				$order_id = trim($col[0]);
				
				if (empty($order_id)) {
					$err_array[] = $order_id;
					continue;
				}
				if($j == 0)
				{
					$j++;
					continue;
				}
				
				$quene_order_list[]  = array('order_num_alias' => $order_id , 'shipping_no' => $col[1], 'express' => $express,'expresscom' => $expresscom );
				
			}
			
			S('_orderquene_'.$cache_key, $quene_order_list);
			
			$this->cache_key = $cache_key;
			$this->type = $type;
			
			$this->display('Order/oploadexcelorder');
			die();
		}
		
		
		
		
		$this->express_list = $express_list;
		$this->type = I('request.type');
		$this->display();
	}
	
	public function do_order_quene()
	{
		$_GPC = I('request.');
		
		$type = $_GPC['type'];
		$cache_key = $_GPC['cache_key'];
		
		$quene_order_list = S('_orderquene_'.$cache_key);
		
		$tmp_info = array_shift($quene_order_list);
		
		S('_orderquene_'.$cache_key, $quene_order_list);
		
		$express = $tmp_info['express'];
		$expresscom = $tmp_info['expresscom'];
		$shipping_no = $tmp_info['shipping_no'];
			
		//$rows = D('Seller/Excel')->import('excelfile');
			
		$order_info = M('lionfish_comshop_order')->where( array('order_num_alias' => $tmp_info['order_num_alias'] ) )->find();
		
		
		if(!empty($order_info) && $order_info['order_status_id'] == 1)
		{
			if( $type == 'mult' && $order_info['delivery'] == 'express' )
			{
				$ex_info = D('Seller/Express')-> get_express_info($express);
				
				
				$data = array();
					
				$data['express_time'] = time();
				
				$data['order_status_id'] = 4;
				$data['shipping_no'] = $shipping_no;
				$data['shipping_method'] = $express;
				$data['dispatchname'] = $ex_info['name'];
				
				M('lionfish_comshop_order')->where( array('order_id' => $order_info['order_id'] ) )->save( $data );
				
				$history_data = array();
				
				$history_data['order_id'] = $order_info['order_id'];
				$history_data['order_status_id'] = 4;
				$history_data['notify'] = 0;
				$history_data['comment'] = '订单快递已发货，后台导入批量发货';
				$history_data['date_added'] = time();
				
				M('lionfish_comshop_order_history')->add($history_data);
				//TODO..发送已发货的模板消息
				D('Home/Frontorder')->send_order_operate($order_info['order_id']);
			}
			else {
				
				if($order_info['delivery'] != 'express')
				{
					$data = array();
			
					$data['express_time'] = time();
					
					$data['order_status_id'] = 14;
					
					M('lionfish_comshop_order')->where( array('order_id' => $order_info['order_id'] ) )->save($data);
					
					$history_data = array();
					$history_data['order_id'] = $order_info['order_id'];
					$history_data['order_status_id'] = 14;
					$history_data['notify'] = 0;
					$history_data['comment'] = '订单配送中';
					$history_data['date_added'] = time();
					
					M('lionfish_comshop_order_history')->add($history_data);	
				}	
			}
			//TODO...发送已经发货给团长的消息通知
			
		}
		if($type =='mult_send_tuanz' && $order_info['delivery'] != 'express' && $order_info['order_status_id'] == 14  )
		{
			//订单批量团长签收  2019012749451499751
			
			$history_data = array();
			$history_data['order_id'] = $order_info['order_id'];
			$history_data['order_status_id'] = 4;
			$history_data['notify'] = 0;
			$history_data['comment'] = '后台批量导入发货到团长';
			$history_data['date_added'] = time();
			
			M('lionfish_comshop_order_history')->add( $history_data );
			
			D('Home/Frontorder')->send_order_operate($order_info['order_id']);
		
		}
		
		if($type =='mult_member_receive_order' && $order_info['order_status_id'] == 4  )
		{
			//批量用户确认收货
			D('Home/Frontorder')->receive_order($order_info['order_id'], true);
		}
		
		if( empty($quene_order_list) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}
		
		echo json_encode( array('code' => 0, 'msg' => '订单号：'.$tmp_info['order_num_alias']." 处理成功，还剩余".count($quene_order_list)."个订单未处理") );
		die();
			
	}
	
	//--end
	
	 public function ordersendall2()
	{
		
		
		if( IS_POST )
		{
			
			$type =  I('request.type', 'normal');
			$express =  I('request.express', '');
			$expresscom = I('request.expresscom', '');
			
			$rows = D('Seller/Excel')->import('excelfile');
			
			$num = count($rows);
			$time = time();
			
			$express_arr = array();
			
			foreach($express_list as $val)
			{
				$express_arr[ $val['id'] ] = $val['name'];
			}
			
			$i = 0;
			$err_array = array();
			
			$j =0;
			foreach ($rows as $rownum => $col) {
				$order_id = trim($col[0]);
				
				if (empty($order_id)) {
					$err_array[] = $order_id;
					continue;
				}
				if($j == 0)
				{
					$j++;
					continue;
				}
				
					
				$order_info = M('lionfish_comshop_order')->where( array('order_num_alias' => $order_id ) )->find();	
				
				if(!empty($order_info) && $order_info['order_status_id'] == 1)
				{
				
					//判断是否快递类型 type  //normal  mult
					
					
					if( $type == 'mult' && $order_info['delivery'] == 'express' )
					{
						
						
						$data = array();
					
						$data['express_time'] = time();
						
						$data['order_status_id'] = 4;
						$data['shipping_no'] = $col[1];
						$data['shipping_method'] = $express;
						$data['dispatchname'] = $expresscom;
						
						M('lionfish_comshop_order')->where( array('order_id' => $order_info['order_id'] ) )->save( $data );
						
						$history_data = array();
						
						$history_data['order_id'] = $order_info['order_id'];
						$history_data['order_status_id'] = 4;
						$history_data['notify'] = 0;
						$history_data['comment'] = '订单快递已发货';
						$history_data['date_added'] = time();
						
						M('lionfish_comshop_order_history')->add($history_data);
						//TODO..发送已发货的模板消息
						D('Home/Frontorder')->send_order_operate($order_info['order_id']);
						
					}else {
						
						if($order_info['delivery'] != 'express')
						{
							$data = array();
					
							$data['express_time'] = time();
							
							$data['order_status_id'] = 14;
							
							M('lionfish_comshop_order')->where( array('order_id' => $order_info['order_id'] ) )->save($data);
							
							$history_data = array();
							$history_data['order_id'] = $order_info['order_id'];
							$history_data['order_status_id'] = 14;
							$history_data['notify'] = 0;
							$history_data['comment'] = '订单配送中';
							$history_data['date_added'] = time();
							
							M('lionfish_comshop_order_history')->add($history_data);	
						}
	
					}
					
					//TODO...发送已经发货给团长的消息通知
					
					
					++$i;
				}
				
				if($type =='mult_send_tuanz' && $order_info['delivery'] != 'express' && $order_info['order_status_id'] == 14  )
				{
					//订单批量团长签收  2019012749451499751
					D('Home/Frontorder')->send_order_operate($order_info['order_id']);
					++$i;
				}
				
				if($type =='mult_member_receive_order' && $order_info['order_status_id'] == 4  )
				{
					//批量用户确认收货
					
					D('Home/Frontorder')->receive_order($order_info['order_id'], true);
					
					++$i;
				}
				
			}
			
			$tip = '';
			
			if($type =='mult_send_tuanz')
			{
				$msg = $i . '个订单批量送达团长成功！';
			}
			else if($type == 'mult_member_receive_order')
			{
				$msg = $i . '个订单批量用户确认收货！';
			}
			else{
				$msg = $i . '个订单发货成功！';
			}

			if ($i < $num) {
				$url = '';

				if (!empty($err_array)) {
					$j = 1;
					
					
					if($type =='mult_send_tuanz')
					{
						$tip .= '<br>' . count($err_array) . '个订单批量送达团长失败,失败的订单编号: <br>';
					}else if($type == 'mult_member_receive_order'){
						$tip .= '<br>' . count($err_array) . '个订单批量用户确认收货,失败的订单编号: <br>';
					}
					else{
						$tip .= '<br>' . count($err_array) . '个订单发货失败,失败的订单编号: <br>';
					}
					

					foreach ($err_array as $k => $v) {
						$tip .= $v . ' ';

						if (($j % 2) == 0) {
							$tip .= '<br>';
						}

						++$j;
					}
				}
			}
			else {
				$url = U('order/ordersendall', array('type' => $type) );
			}

			$redirect = $url;
			
			$this->message = $msg.$tip;
			$this->redirect = $redirect;
			$this->display('Public/_message');
			die();
			
		}
		
		$express_list = D('Seller/Express')->load_all_express();
		
		
		$this->express_list = $express_list;
		$this->type = I('request.type');
		$this->display();
	}
	
	public function opchangeexpress()
	{
		$_GPC = I('request.');
		
		$opdata = $this->check_order_data();
		extract($opdata);
		
		$changeexpress = 1;
		$sendtype = intval($_GPC['sendtype']);
		$edit_flag = 1;

		if (IS_POST) {
			
			if (!(empty($_GPC['shipping_no'])) && empty($_GPC['shipping_no'])) {
				show_json(0,  array('msg' => '请输入快递单号！') );
			}

			if (!(empty($item['transid']))) {
			}
			
			$express_info = D('Seller/Express')->get_express_info($_GPC['express']);
		
			$time = time();
			$data = array(
				'shipping_method' => trim($_GPC['express']),
				'dispatchname' => $express_info['name'], 				
				'shipping_no' => trim($_GPC['shipping_no']), 
				'express_time' => $time
			);
			
			M('lionfish_comshop_order')->where( array('order_id' => $item['order_id']) )->save( $data );
			
			$history_data = array();
			$history_data['order_id'] = $item['order_id'];
			$history_data['order_status_id'] = 4;
			$history_data['notify'] = 0;
			$history_data['comment'] = '修改发货物流，订单发货 ID: ' . $item['order_id'] . ' 订单号: ' . $item['order_num_alias'] . ' <br/>快递公司: ' . $express_info['name'] . ' 快递单号: ' . $_GPC['shipping_no'];
			$history_data['date_added'] = time();
			
			M('lionfish_comshop_order_history')->add( $history_data );
				
			//TODO...发送已经发货的消息通知
			//m('notice')->sendOrderMessage($item['id']);
			//plog('order.op.send', '订单发货 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'] . ' <br/>快递公司: ' . $_GPC['expresscom'] . ' 快递单号: ' . $_GPC['expresssn']);
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}

		$noshipped = array();
		$shipped = array();

		$province_info = D('Home/Front')->get_area_info($item['shipping_province_id']);
		$city_info = D('Home/Front')->get_area_info($item['shipping_city_id']);
		$area_info = D('Home/Front')->get_area_info($item['shipping_country_id']);
		
		$order_goods = M('lionfish_comshop_order_goods')->field('order_goods_id as id,name as title,goods_images as thumb')->where( array('order_id' => $item['order_id']) )->select();				
		
		$express_list = D('Seller/Express')->load_all_express();
		
		$this->id = $item['order_id'];
		$this->item = $item;
		$this->province_info = $province_info;
		$this->city_info = $city_info;
		$this->area_info = $area_info;
		$this->order_goods = $order_goods;
		$this->express_list = $express_list;
		
		
		$this->display('Order/opsend');
	}
	
	public function batchsend_import()
	{
		global $_W;
		global $_GPC;
		
		$type = I('request.type','normal');
		
		$this->type = $type;
		
		$columns = array();
		$columns[] = array('title' => '订单编号', 'field' => '', 'width' => 32);
		//$columns[] = array('title' => '快递单号', 'field' => '', 'width' => 32);
		
		if($type == 'normal')
		{
			D('Seller/Excel')->temp('批量发货数据模板', $columns);
		}else{
			$columns[] = array('title' => '快递编号', 'field' => '', 'width' => 32);
			
			D('Seller/Excel')->temp('批量发货数据模板', $columns);
		}
		
		
	}
	
	 /**
	  * 上传订单Excel批量发货
	  */
	 function sendexpress_excel_done()
	 {
	      set_time_limit(0);
    	  if(isset($_FILES["file"]) && ($_FILES["file"]["error"] == 0)){
    	      
    	      $excel_dir = ROOT_PATH.'Uploads/image/'.date('Y-m-d');
    	      $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    	      RecursiveMkdir( $excel_dir );
    	      
    	      $path = $excel_dir.'/'.md5($_FILES['file']['name'].mt_rand(1, 999)).'.'.$extension;
    	      $rs = move_uploaded_file($_FILES["file"]["tmp_name"],$path);
    	      
    	    
    	    $notify_model = D('Home/Weixinnotify');
    	    //
    	     
            $result = importExecl($path);
            if(!empty($result)) {
                $order_ids = array();
                //开始导入数据库，并发货
                foreach($result as $val){
                   if(empty($val[0]))
                   {
                       continue;
                   }
                    $order_info = M('order')->field('order_id,order_status_id,delivery')->where( array('order_num_alias' => trim($val[0])) )->find();
					//order_status_id  1
					if($order_info['order_status_id'] != 1)
					{
						continue;
					}
					//['delivery'] == 'pickup'
                    $order_ids[] = $order_info['order_id'];
					if($order_info['delivery'] == 'pickup')
					{
						M('order')->where( array('order_id' => $order_info['order_id'] ) )->save( array('order_status_id' => 4) );
					} else {
						M('order')->where( array('order_id' => $order_info['order_id'] ) )->save( array('shipping_method' =>$val[5],'shipping_no' =>$val[6],'order_status_id' => 4) );
					}
					
					$oh = array();
					$oh['order_id']=$order_info['order_id'];
					$oh['order_status_id']=4;
					$oh['notify'] = 0;
					$oh['comment']='导入excel批量发货';
					$oh['date_added']=time();
					$oh_id=M('OrderHistory')->add($oh);
                    
                }
                foreach($order_ids as $order_id) {
					
					$order_info = M('order')->field('delivery')->where( array('order_id' => $order_id) )->find();
					
					if($order_info['delivery'] == 'pickup')
					{
						$notify_model->sendPickupMsg($order_id);
					} else {
						$notify_model->sendExpressMsg($order_id);
					}
                    
                }
            }
          }
          echo json_encode( array('code' => 1) );
          die();
	 }
	 
	 
	 
	 function sendexpress()
	 {
		 $this->breadcrumb2='批量发货';
	     $this->display();
	 }
	 function print_order(){
	 	$model=new OrderModel();   
		
		$this->order=$model->order_info(I('id'));
		$this->print=true;
		$this->display('./Themes/Home/default/Mail/order.html');
	 }
	 
	 public function refunddone()
	 {
		$id = I('get.id',0,'intval');
		$comment = I('post.comment');
		$order_refund_type = I('post.order_refund_type',0,'intval');
		$result = array('code' =>1);
		
		$order_refund_history = array();
		$order_refund_history['order_id'] = $id;
		$order_refund_history['message'] = htmlspecialchars($comment);
		$order_refund_history['type'] = 2;
		$order_refund_history['addtime'] = time();
		
		M('order_refund_history')->add($order_refund_history);
		
		
		$order_history = array();
		$order_history['order_id'] = $id;
		$order_history['order_status_id'] = 0;
		$order_history['notify'] = 0;
		$order_history['comment'] = '';
		$order_history['date_added'] = time();
		
		if($order_refund_type ==1)
		{
			//拒绝
			M('order_refund')->where( array('order_id' => $id) )->save( array('state' => 1) );
			$order_history['order_status_id'] = 12;
			$order_history['comment'] = '商家拒绝退款';
			M('order_history')->add($order_history);
			
		} else {
			
			$order_history['order_status_id'] = 12;
			$order_history['comment'] = '商家统一退款';
			M('order_history')->add($order_history);
			
			$weixin_model = D('Home/Weixin');
			//通过
			M('order_refund')->where( array('order_id' => $id) )->save( array('state' => 3) );
			$order_refund = M('order_refund')->where( array('order_id' => $id) )->find();
			$weixin_model->refundOrder($id, $order_refund['ref_money']);
			
			
		}
		echo json_encode($result);
		die();
		
	 }
	 public function show_refund()
	 {
		$this->crumbs='订单退款详情';
		
		$model=new OrderModel();   
		
		$data = $model->order_info(I('id'));
		
		$order_statuses = $data['order_statuses'];
		
		$need_status = array();
		foreach($order_statuses as $key => $val)
		{
			if( in_array($val['order_status_id'], array(4)) )
			{
				$need_status[$key] = $val;
			}
		}
		$data['order_statuses'] = $need_status;
		
		$refund_reason = array(
							'97' =>'商品有质量问题',
							'98' =>'没有收到货',
							'99' =>'商品少发漏发发错',
							'100' =>'商品与描述不一致',
							'101' =>'收到商品时有划痕或破损',
							'102' =>'质疑假货',
							'111' =>'其他',
						);
		$order_refund = M('order_refund')->where( array('order_id' =>I('id')) )->find();
		//ref_type
		$order_refund['ref_type'] = $order_refund['ref_type'] ==1 ? '仅退款': '退款退货';
		$order_refund['ref_name'] = empty($refund_reason[$order_refund['ref_name']]) ? $order_refund['ref_name']: $refund_reason[$order_refund['ref_name']] ;
		
		$refund_state = array(
							0 => '申请中',
							1 => '商家拒绝',
							2 => '平台介入',
							3 => '退款成功',
							4 => '退款失败',
							5 => '撤销申请',
						);
		$order_refund['state'] = $refund_state[$order_refund['state']];
		
		$this->order_refund = $order_refund;
		$order_refund_image = M('order_refund_image')->where( array('ref_id' => $order_refund['ref_id']) )->select();
		$refund_images = array();
		
		if(!empty($order_refund_image))
		{
			foreach($order_refund_image as $refund_image)
			{
				$refund_image['thumb_image'] = resize($refund_image['image'], 100, 100);
				$refund_images[] = $refund_image;
			}
		}
		
		$order_refund_history = M('order_refund_history')->where( array('order_id' => I('id')) )->order('addtime asc')->select();
		
		foreach($order_refund_history as $key => $val)
		{
			$val['type'] = $val['type'] == 1 ?'用户反馈':'商家反馈';
			switch($val['type'])
			{
				case 1:
						$val['type'] = '用户反馈';
						break;
				case 2: 
						$val['type'] = '商家反馈';
						break;
				case 3: 
						$val['type'] = '平台反馈';
						break;
				
			}
			$order_refund_history_image = M('order_refund_history_image')->where( array('orh_id' => $val['id']) )->select();
			if(!empty($order_refund_history_image))
			{
				foreach($order_refund_history_image as $kk => $vv)
				{
					$vv['thumb_image'] = resize($vv['image'], 100, 100);
					$order_refund_history_image[$kk] = $vv;
				}
			}
			$val['order_refund_history_image'] = $order_refund_history_image;
			$order_refund_history[$key] = $val;
		}
		
		$this->order_refund_history = $order_refund_history;
		$this->refund_images = $refund_images;
		$this->data = $data;
		$this->display('refund');
	 }
	 public function show_order(){
	 	
	 	$this->crumbs='订单详情';
		//is_ziti/1 commiss_list
		$is_ziti = I('get.is_ziti', 0);
		if($is_ziti == 1)
		{
			$this->breadcrumb1='自提管理';
			$this->breadcrumb2='自提管理';
		}
	 	$model=new OrderModel();   
		
		$data = $model->order_info(I('id'));
		
		$order_statuses = $data['order_statuses'];
		
		$need_status = array();
		foreach($order_statuses as $key => $val)
		{
			if( in_array($val['order_status_id'], array(1,2,3,4,5,6,7,8,11,12,13)) )
			{
				$need_status[$key] = $val;
			}
		}
		$data['order_statuses'] = $need_status;
		
		//$data['order']
		if($data['order']['type'] == 'integral')
		{
			$integral_order =  M('integral_order')->where( array('order_id' => I('id') ) )->find();
			$this->integral_order = $integral_order;
		}
		
		$this->data = $data;
		
		$pick_order_info = array();
		$pick_up = array();
		if($data['order']['delivery'] == 'pickup')
		{
			$pick_order_info = M('pick_order')->where( array('order_id' => $data['order']['order_id']) )->find();
			$pick_up = M('pick_up')->where( array('id' => $pick_order_info['pick_id']) )->find();
		}
		
		$this->pick_order_info = $pick_order_info;
		$this->pick_up = $pick_up;
		
		$sql="select s.* from ".C('DB_PREFIX')."seller_express as s, ".C('DB_PREFIX')."seller_express_relat as ser 
		      where s.id = ser.express_id and ser.store_id = ".SELLERUID;
		$express_list= M()->query($sql);
		
		$this->express_list = $express_list;
		
		//$data['order']['order_id']
		$order_goods_haitao = M('order_goods_haitao')->where( array('order_id' => $data['order']['order_id']) )->find();
		$this->order_goods_haitao = $order_goods_haitao;
	 	$this->display('show');
	 }
	 function history(){
	 		$model=new OrderModel();
			
			if(IS_POST){				
				
				if(I('order_status_id')==C('cancel_order_status_id')){
					$Order = new \Home\Model\OrderModel();
					$Order->cancel_order($_GET['id']);					
					storage_user_action(session('user_auth.uid'),session('user_auth.username'),C('BACKEND_USER'),'取消了订单  '.$_GET['id']);
					$result=true;
				}else{
					//order_status_id 4
					
					if($_POST['order_status_id'] != 4)
					{
						unset($_POST['shipping_no']);
						unset($_POST['shipping_method']);
						die();
					}else {
						
					}
					$result=$model->addOrderHistory($_GET['id'],$_POST);
				}
				if($result){
					$this->success='新增成功！！';
				}else{
					$this->error='新增失败！！';
				}
			}
			
			$results = $model->getOrderHistories($_GET['id']);
		
			foreach ($results as $result) {
				$histories[] = array(
					'notify'     => $result['notify'] ? '是' : '否',
					'status'     => $result['status'],
					'comment'    => nl2br($result['comment']),
					'date_added' => date('Y/m/d H:i:s', $result['date_added'])
				);
			}	
			
			$this->histories=$histories;
			
			$this->display();
	}
	
	function del(){
		$model=new OrderModel();  
		$return=$model->del_order(I('get.id'));			
		$this->osc_alert($return); 	
	}	
	
	//begin 
	public function printconfig()
	{
		
		if (defined('ROLE') && ROLE == 'agenter' )
		{
			
			$supper_info = get_agent_logininfo();
				
			
			if (IS_POST) {
				
				$_GPC = I('request.');
				
				$data = array();
				
				$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));
				
				//$supper_info['id']
				$open_feier_print = isset($data['open_feier_print'.$supper_info['id']]) ? $data['open_feier_print'.$supper_info['id']]:0;
				if(empty($open_feier_print) || $open_feier_print == 0)
				{
					$data['open_feier_print'.$supper_info['id']] = $open_feier_print;
					
				}else if($open_feier_print == 1){
					$feier_print_sn = isset($data['feier_print_sn'.$supper_info['id']]) ? $data['feier_print_sn'.$supper_info['id']]:'';
					$feier_print_key = isset($data['feier_print_key'.$supper_info['id']]) ? $data['feier_print_key'.$supper_info['id']]:'';
					
					$data['open_feier_print'.$supper_info['id']] = $open_feier_print;
					$data['feier_print_sn'.$supper_info['id']] = $feier_print_sn;
					$data['feier_print_key'.$supper_info['id']] = $feier_print_key;
					
					$feier_print_sn_old_arr =  M('lionfish_comshop_config')->where( array('name' => 'feier_print_sn'.$supper_info['id'] ) )->find();
					
					$feier_print_sn_old = $feier_print_sn_old_arr['value'];
					
					$feier_print_key_old_arr = M('lionfish_comshop_config')->where(  array('name' => 'feier_print_key'.$supper_info['id'] ) )->find();
					
					$feier_print_key_old = $feier_print_key_old_arr['value'];
					
					if($feier_print_sn_old != $feier_print_sn || $feier_print_key_old != $feier_print_key)
					{
						//开始添加打印机 
						//printaction
						$print_model = D('Seller/Printaction');
						$snlist = "{$feier_print_sn}#{$feier_print_key}";
						
						$print_model->addprinter($snlist);
						
					}
					
					//...todo测试订单自动打印
				}else if($open_feier_print == 2){
					$yilian_machine_code = isset($data['yilian_machine_code'.$supper_info['id']]) ? $data['yilian_machine_code'.$supper_info['id']]:'';
					
					$yilian_msign = isset($data['yilian_msign'.$supper_info['id']]) ? $data['yilian_msign'.$supper_info['id']]:'';
					$yilian_client_id = isset($data['yilian_client_id'.$supper_info['id']]) ? $data['yilian_client_id'.$supper_info['id']]:'';
					$yilian_client_key = isset($data['yilian_client_key'.$supper_info['id']]) ? $data['yilian_client_key'.$supper_info['id']]:'';
					
					
					$data['open_feier_print'.$supper_info['id']] = $open_feier_print;
					$data['yilian_machine_code'.$supper_info['id']] = $yilian_machine_code;
					$data['yilian_msign'.$supper_info['id']] = $yilian_msign;
					$data['yilian_client_id'.$supper_info['id']] = $yilian_client_id;
					$data['yilian_client_key'.$supper_info['id']] = $yilian_client_key;
					
					$yilian_client_id_old_arr =  M('lionfish_comshop_config')->where( array('name' => 'yilian_client_id'.$supper_info['id'] ) )->find();
					
					$yilian_client_id_old = $yilian_client_id_old_arr['value'];
					
					$yilian_machine_code_old_arr = M('lionfish_comshop_config')->where( array('name' => 'yilian_machine_code'.$supper_info['id'] ) )->find();
					
					$yilian_machine_code_old = $yilian_machine_code_old_arr['value'];
					
					$yilian_msign_old_arr = M('lionfish_comshop_config')->where( array('name' => 'yilian_msign'.$supper_info['id'] ) )->find();
					
					$yilian_msign_old = $yilian_msign_old_arr['value'];
					
					
					if(true || $yilian_client_id != $yilian_client_id_old || $yilian_machine_code_old != $yilian_machine_code || $yilian_msign_old != $yilian_msign)
					{
						
						//开始添加打印机 
						//printaction
						$print_model = D('Seller/Printaction');
						
						$res = $print_model->addyilianyunprinter($yilian_client_id,$yilian_client_key,$yilian_machine_code, $yilian_msign );
						
						if($res != 0)
						{
							show_json(0,  array('msg' => '添加易联云打印机失败！' ) );
						}
					}
					
					//...todo测试订单自动打印
				}
				
				D('Seller/Config')->update($data);

				show_json(1);
			}
			$data = D('Seller/Config')->get_all_config();
			
			$this->supper_info = $supper_info;
			$this->data = $data;
			
			include $this->display();
		}
	}
	//end
	/** 
	 * 保存修改商品订单号
	 * @Author: flydreame 
	 * @Date: 2020-05-29 13:43:21 
	 * @Desc:  
	 */
	public function update_shipping_no(){
		$date['order_goods_id'] = I('order_goods_id');
		$date['goods_shipping_no'] = I('goods_shipping_no');
		$re = M('lionfish_comshop_order_goods')->data($date)->save();
		
		if($re){
			echo 1;die;
		}else{
			echo 0;die;
		}
	}
	
	/**
	 * 数组去重
	 * 
	 * 
	 */
	public function second_array_unique_bykey($arr, $key){
        $tmp_arr = array();
        foreach($arr as $k => $v)
        {
            if(in_array($v[$key], $tmp_arr))  //搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
            {
                unset($arr[$k]); //销毁一个变量 如果$tmp_arr中已存在相同的值就删除该值
            }else {
                $tmp_arr[$k] = $v[$key]; //将不同的值放在该数组中保存
            }
         }
        //ksort($arr); //ksort函数对数组进行排序(保留原键值key) sort为不保留key值
        return $arr;
    }

}
?>