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
namespace Seller\Model;

class OrderModel{
	
	
	public function do_tuanz_over($order_id)
	{
		//express_time
		
		M('lionfish_comshop_order')->where( array('order_id' => $order_id) )->save( array('order_status_id' => 4, 'express_tuanz_time' => time()) );
		
		//todo ... send member msg goods is ing
		
		$history_data = array();
		$history_data['order_id'] = $order_id;
		$history_data['order_status_id'] = 4;
		$history_data['notify'] = 0;
		$history_data['comment'] = '后台操作，确认送达团长' ;
		$history_data['date_added'] = time();
		
		
		M('lionfish_comshop_order_history')->add( $history_data );
		
		D('Home/Frontorder')->send_order_operate($order_id);
	}
	
	public function do_send_tuanz($order_id)
	{
		
		//express_time
				
		M('lionfish_comshop_order')->where( array('order_id' => $order_id) )->save( array('order_status_id' => 14, 'express_time' => time()) );
		
		//todo ... send tuanz msg 
		
		$history_data = array();
		$history_data['order_id'] = $order_id;
		$history_data['order_status_id'] = 14;
		$history_data['notify'] = 0;
		$history_data['comment'] = '后台操作，确认开始配送货物' ;
		$history_data['date_added'] = time();
		
		M('lionfish_comshop_order_history')->add($history_data);
		
	}
	
	public function update($data)
	{
		
		$ins_data = array();
		$ins_data['tagname'] = $data['tagname'];
		$ins_data['tagcontent'] = serialize(array_filter($data['tagcontent']));
		$ins_data['state'] = $data['state'];
		$ins_data['sort_order'] = $data['sort_order'];
		
		$id = $data['id'];
		if( !empty($id) && $id > 0 )
		{
			M('lionfish_comshop_goods_tags')->where( array('id' => $id) )->save( $ins_data );
			
		}else{
			M('lionfish_comshop_goods_tags')->add( $ins_data );
		}
	}
	
	public function load_order_list($reorder_status_id = 0,$is_fenxiao =0,$is_pin =0,$integral =0,$is_soli=0)
	{
		
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
		
		$order_status_id =  I('request.order_status_id', 0);
		
		if($reorder_status_id >0)
		{
			$order_status_id = $reorder_status_id;
		}
		
		$searchtime = I('request.searchtime','');
		$searchfield = I('request.searchfield', '');
		
		$searchtype = I('request.type');
		
		if( $is_pin == 1  )
		{
			$searchtype = 'pintuan';
		}
		
		if( $integral == 1 )
		{
			$searchtype = 'integral';
		}
		
		$delivery = I('request.delivery', '');
		
		
		$count_where = "";
		$agentid = I('request.agentid', '');
		
		
		$head_id = I('request.headid', ''); 
		//$is_fenxiao = I('request.is_fenxiao', 0); 
		
		$pindex = I('request.page', 1);
		$psize = 20;
		
		
		$paras =array();
		
		$sqlcondition = "";
		
		$condition = " 1 ";
		
		
		
		if($is_soli > 0)
		{
			$condition .= " and o.soli_id > 0 ";
		}
		
		
		
		if (defined('ROLE') && ROLE == 'agenter' ) 
		{
			$supper_info = get_agent_logininfo();
				
			$order_ids_list_tmp = M('lionfish_comshop_order_goods')->field('order_id')->where( array('supply_id' => $supper_info['id'] ) )->select();								
											
			if( !empty($order_ids_list_tmp) )
			{
				$order_ids_tmp_arr = array();
				foreach($order_ids_list_tmp as  $vv)
				{
					$order_ids_tmp_arr[] = $vv['order_id'];
				}
				$order_ids_tmp_str = implode(',', $order_ids_tmp_arr);
				
				$condition .= " and o.order_id in({$order_ids_tmp_str}) ";
			}
			else{
				$condition .= " and o.order_id in(0) ";
			}
		}
		
		if( $is_fenxiao == 1)
		{
			//分销订单
			
			$condition .= " and o.is_commission = 1  ";
			$count_where .= " and is_commission = 1 ";
			
			$commiss_member_id = I('request.commiss_member_id', '');
			
			if( $commiss_member_id > 0 )
			{
				$order_ids = M('lionfish_comshop_member_commiss_order')->field('order_id')->where( array('member_id' => $commiss_member_id ) )->select();
				
				if(!empty($order_ids))
				{
					$order_ids_arr = array();
					foreach($order_ids as $vv)
					{
						$order_ids_arr[] = $vv['order_id'];
					}
					$order_ids_str = implode(",",$order_ids_arr);
					$condition .= ' AND ( o.order_id in('.$order_ids_str.') ) ';
					$count_where .= ' AND ( order_id in('.$order_ids_str.') ) ';
				}else{
					$condition .= " and o.order_id in(0) ";
					$count_where .= ' AND order_id in(0)  ';
				}
			}
			
			
			
		}
		
		if( !empty($searchtype) && in_array($searchtype, array('normal','pintuan','integral')) && empty($supper_info))
		{
			$condition .= " and o.type ='{$searchtype}'  ";
		}
		
		if( !empty($delivery) )
		{
			$condition .= " and o.delivery ='{$delivery}'  ";
		}
		
		
		if( !empty($head_id) && $head_id >0 )
		{
			$condition .= " and o.head_id ='{$head_id}'  ";
			
			$count_where .= " and head_id ='{$head_id}'  ";
		}
		
		if($order_status_id > 0)
		{
			//$condition .= " and o.order_status_id={$order_status_id} ";
			
			if($order_status_id ==12 )
			{
				$condition .= " and (o.order_status_id={$order_status_id} or o.order_status_id=10 ) ";
			
			}else if($order_status_id ==11)
			{
				$condition .= " and (o.order_status_id={$order_status_id} or o.order_status_id=6 ) ";
			}
			else{
				$condition .= " and o.order_status_id={$order_status_id} ";
			}
			
		}
		
		//$is_fenxiao = I('request.is_fenxiao','intval',0);
		
		$keyword = I('request.keyword');
		if( !empty($searchfield) && !empty($keyword))
		{
			$keyword = trim($keyword);
			
			$keyword = htmlspecialchars_decode($keyword, ENT_QUOTES);
			
			switch($searchfield)
			{
				case 'ordersn':
					$condition .= ' AND locate("'.$keyword.'",o.order_num_alias)>0'; 
				break;
				case 'member':
					$condition .= ' AND (locate("'.$keyword.'",m.username)>0 or locate("'.$keyword.'",m.telephone)>0 or "'.$keyword.'"=o.member_id ) and o.member_id >0 ';
					$sqlcondition .= ' left join ' . C('DB_PREFIX') . 'lionfish_comshop_member m on m.member_id = o.member_id ';
				break;
				case 'address':
					$condition .= ' AND ( locate("'.$keyword.'",o.shipping_name)>0 )';
					//shipping_address
				break;
				case 'mobile':
					$condition .= ' AND ( locate("'.$keyword.'",o.shipping_tel)>0 )';
					//shipping_address
				break;
				case 'location':
					$condition .= ' AND (locate("'.$keyword.'",o.shipping_address)>0 )';
				break;
				case 'shipping_no':
					$condition .= ' AND (locate("'.$keyword.'",o.shipping_no)>0 )';
				break;
				
				case 'head_address':
					$head_ids = M('lionfish_community_head')->field('id')->where( 'community_name like "%'.$keyword.'%"' )->select();
					
					
					if(!empty($head_ids))
					{
						$head_ids_arr = array();
						foreach($head_ids as $vv)
						{
							$head_ids_arr[] = $vv['id'];
						}
						$head_ids_str = implode(",",$head_ids_arr);
						$condition .= ' AND ( o.head_id in('.$head_ids_str.') )';
					}else{
						$condition .= " and o.order_id in(0) ";
					}
					
				break;
				case 'head_name':
					// SELECT * FROM `ims_lionfish_community_head` WHERE `head_name` LIKE '%黄%'
					
					$head_ids = M('lionfish_community_head')->field('id')->where( 'head_name like "%'.$keyword.'%"' )->select();
					
					if(!empty($head_ids))
					{
						$head_ids_arr = array();
						foreach($head_ids as $vv)
						{
							$head_ids_arr[] = $vv['id'];
						}
						$head_ids_str = implode(",",$head_ids_arr);
						$condition .= ' AND ( o.head_id in('.$head_ids_str.') )';
					}else{
						
						$condition .= " and o.order_id in(0) ";
		
					}
					
				break;
				case 'goodstitle':
					$sqlcondition = ' inner join ( select DISTINCT(og.order_id) from ' . C('DB_PREFIX').'lionfish_comshop_order_goods og  where  (locate("'.$keyword.'",og.name)>0)) gs on gs.order_id=o.order_id';
				//var_dump($sqlcondition);
				//die();
				
				break;
				case 'supply_name':
					
					$supply_name_sql = 'SELECT id FROM ' . C('DB_PREFIX'). 
										'lionfish_comshop_supply where shopname like "%'.$keyword.'%"';
					$supply_ids = M()->query($supply_name_sql);
					
					
					if(!empty($supply_ids))
					{
						$supply_ids_arr = array();
						foreach($supply_ids as $vv)
						{
							$supply_ids_arr[] = $vv['id'];
						}
						$supply_ids_str = implode(",",$supply_ids_arr);
						
						$order_ids_list_tmp = M('lionfish_comshop_order_goods')->field('order_id')->where( "supply_id in ({$supply_ids_str})" )->select();
						
						if( !empty($order_ids_list_tmp) )
						{
							$order_ids_tmp_arr = array();
							foreach($order_ids_list_tmp as  $vv)
							{
								$order_ids_tmp_arr[] = $vv['order_id'];
							}
							$order_ids_tmp_str = implode(',', $order_ids_tmp_arr);
							
							$condition .= " and o.order_id in({$order_ids_tmp_str}) ";
						}else{
							$condition .= " and o.order_id in(0) ";
						}
					}else{
						$condition .= " and o.order_id in(0) ";
					}
				break;
				case 'trans_id':
					$condition .= ' AND (locate('.$keyword.',o.transaction_id)>0 )';
				break;
				
			}
		}
		
		if( !empty($searchtime) )
		{
			switch( $searchtime )
			{
				case 'create':
					//下单时间 date_added
					$condition .= " and o.date_added>={$starttime} and o.date_added <= {$endtime}";
				break;
				case 'pay':
					//付款时间 
					$condition .= " and o.pay_time>={$starttime} and o.pay_time <= {$endtime}";
				break;
				case 'send':
					//发货时间 
					$condition .= " and o.express_time>={$starttime} and o.express_time <= {$endtime}";
				break;
				case 'finish':
					//完成时间 
					$condition .= " and o.finishtime>={$starttime} and o.finishtime <= {$endtime}";
				break;
			}
		}
		
		
		//----begin----
		
		if (defined('ROLE') && ROLE == 'agenter' ) {
			
			$supper_info = get_agent_logininfo();
		
		
			$total_where = " and supply_id= ".$supper_info['id'];
			
			$order_ids_list = M()->query("select og.order_id,og.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money from ".
								C('DB_PREFIX')."lionfish_comshop_order_goods as og , ".C('DB_PREFIX')."lionfish_comshop_order as o  where  og.order_id =o.order_id and og.supply_id = ".$supper_info['id'] );
			
			$order_ids_arr = array();
			$order_ids_arr_dan = array();
			
			$total_money = 0;
			foreach($order_ids_list as $vv)
			{
				if( empty($order_ids_arr) || !isset($order_ids_arr[$vv['order_id']]) )
				{
					$order_ids_arr[$vv['order_id']] = $vv;
					$order_ids_arr_dan[] = $vv['order_id'];
				}
			}
			
			if( !empty($order_ids_arr_dan) )
			{
				$sql = 'SELECT count(o.order_id) as count FROM ' . C('DB_PREFIX'). 'lionfish_comshop_order as o  '.$sqlcondition.' where ' .  $condition." and o.order_id in (".implode(',', $order_ids_arr_dan).") " ;
				
				$total_arr = M()->query($sql);
				
				$total = $total_arr[0]['count'];
				
				
				$order_ids_list = M()->query("select og.order_id,og.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money from ".C('DB_PREFIX').
								"lionfish_comshop_order_goods as og , ".C('DB_PREFIX')."lionfish_comshop_order as o  where {$condition} and og.order_id =o.order_id and og.supply_id = ".$supper_info['id']."  ");
				
				if( !empty($order_ids_list) )
				{
					foreach($order_ids_list as $vv)
					{
						$total_money += $vv['total']+$vv['shipping_fare']-$vv['voucher_credit']-$vv['fullreduction_money'];
					}
				}
			}else{
				$total = 0; 
			}
			
			
		}else{
      
			$sql = 'SELECT count(o.order_id) as count FROM ' . C('DB_PREFIX'). 'lionfish_comshop_order as o  '.$sqlcondition.' where ' .  $condition ;
		
			$total_arr = M()->query( $sql );
			
   
  
  
			$total = $total_arr[0]['count'];
   
			//修改 htong
   
			
			
			$sql = 'SELECT (o.total+o.shipping_fare-o.voucher_credit-o.fullreduction_money) as total_money ,o.order_id,o.order_status_id  FROM ' .  C('DB_PREFIX') . 'lionfish_comshop_order as o '.$sqlcondition.' where ' .  $condition ;
  
			$total_money_arr = M()->query($sql);

   
			foreach($total_money_arr as $v){
    
				if($v['order_status_id'] != 7 && $v['order_status_id'] != 5 ){
					$ref_sql_money = 'SELECT sum(ref_money) as ref_sum_money FROM ' . C('DB_PREFIX'). 'lionfish_comshop_order_refund WHERE order_id = '.$v['order_id'].' and state =3';
					$total_arr_money = M()->query( $ref_sql_money );
					$money = $v['total_money'] - $total_arr_money[0]['ref_sum_money'];
					$total_money += $money;
				}
    

			}
			
			// print_r($total_money);die;
			$s = strtotime(date('Y-m-d').'00:00:00');
			$l = strtotime(date('Y-m-d').'23:59:59');

			$sqls= 'SELECT (o.total+o.shipping_fare-o.voucher_credit-o.fullreduction_money) as totalss,o.order_id,o.order_status_id FROM ' .  C('DB_PREFIX') . 'lionfish_comshop_order as o  where  o.date_added >= '.$s.' and o.date_added <='.$l;
			$today_total= M()->query($sqls);
			$today_total_money = 0;
			foreach($today_total as $v){
				if($v['order_status_id'] != 7 && $v['order_status_id'] != 5 ){
					$ref_sql_money = 'SELECT sum(ref_money) as ref_sum_money FROM ' . C('DB_PREFIX'). 'lionfish_comshop_order_refund WHERE order_id = '.$v['order_id'];
					$total_arr_money = M()->query( $ref_sql_money );
					
					$money = $v['totalss'] - $total_arr_money[0]['ref_sum_money'];
				$today_total_money += $money;
				}
			}
			
		}
		//---------end----
		
		if($total_money < 0)
		{
			$total_money = 0;
		}
		
		
		$total_money =  number_format($total_money,2);
		
		$order_status_arr = $this->get_order_status_name();
		
		$export = I('request.export', 0);
		
		if ($export == 1) 
		{
			@set_time_limit(0);
			
			$is_can_look_headinfo = true;
			$supply_can_look_headinfo = D('Home/Front')->get_config_by_name('supply_can_look_headinfo');
			
		
			if (defined('ROLE') && ROLE == 'agenter' ) 
			{
				if( isset($supply_can_look_headinfo) && $supply_can_look_headinfo == 2 )
				{
					$is_can_look_headinfo = false;
				}
			}
			
			
			$columns = array(
				array('title' => '订单编号', 'field' => 'order_num_alias', 'width' => 100),
				array('title' => '昵称', 'field' => 'name', 'width' => 12),
				//array('title' => '会员姓名', 'field' => 'mrealname', 'width' => 12),
				// array('title' => 'openid', 'field' => 'openid', 'width' => 24),
				array('title' => '会员手机号', 'field' => 'telephone', 'width' => 12),
				array('title' => '会员备注', 'field' => 'member_content', 'width' => 24),
				array('title' => '小区名称', 'field' => 'community_name', 'width' => 12),
				array('title' => '收货姓名(或自提人)', 'field' => 'shipping_name', 'width' => 12),
				array('title' => '联系电话', 'field' => 'shipping_tel', 'width' => 12),
				array('title' => '收货地址', 'field' => 'address_province', 'width' => 12),
				// array('title' => '', 'field' => 'address_city', 'width' => 12),
				// array('title' => '', 'field' => 'address_area', 'width' => 12),
				//array('title' => '', 'field' => 'address_street', 'width' => 12),
				array('title' => '提货详细地址', 'field' => 'address_address', 'width' => 12),
				array('title' => '团长配送送货详细地址', 'field' => 'tuan_send_address', 'width' => 42),
				array('title' => '商品名称', 'field' => 'goods_title', 'width' => 24),
				array('title' => '商品编码', 'field' => 'goods_goodssn', 'width' => 12),
				array('title' => '商品规格', 'field' => 'goods_optiontitle', 'width' => 12),
				array('title' => '商品数量', 'field' => 'quantity', 'width' => 12),
				array('title' => '商品单价', 'field' => 'goods_price1', 'width' => 12),
				//array('title' => '商品单价(折扣后)', 'field' => 'goods_price2', 'width' => 12),
				//array('title' => '商品价格(折扣前)', 'field' => 'goods_rprice1', 'width' => 12),
				array('title' => '商品价格', 'field' => 'goods_rprice2', 'width' => 12),
				array('title' => '支付方式', 'field' => 'paytype', 'width' => 12),
				array('title' => '配送方式', 'field' => 'delivery', 'width' => 12),
				//array('title' => '自提门店', 'field' => 'pickname', 'width' => 24),
				//array('title' => '商品小计', 'field' => 'goodsprice', 'width' => 12),
				array('title' => '运费', 'field' => 'dispatchprice', 'width' => 12),
				array('title' => '积分抵扣', 'field' => 'score_for_money', 'width' => 12),
				//array('title' => '余额抵扣', 'field' => 'deductcredit2', 'width' => 12),
				array('title' => '满额立减', 'field' => 'fullreduction_money', 'width' => 12),
				array('title' => '优惠券优惠', 'field' => 'voucher_credit', 'width' => 12),
				//array('title' => '订单改价', 'field' => 'changeprice', 'width' => 12),
				//array('title' => '运费改价', 'field' => 'changedispatchprice', 'width' => 12),
				array('title' => '应收款(该笔订单总款)', 'field' => 'price', 'width' => 12),
			
				array('title' => '团长佣金', 'field' => 'head_money', 'width' => 12),
				array('title' => '下单时间', 'field' => 'createtime', 'width' => 24),
				array('title' => '付款时间', 'field' => 'paytime', 'width' => 24),
				array('title' => '发货时间', 'field' => 'sendtime', 'width' => 24),
				
				array('title' => '收货时间', 'field' => 'receive_time', 'width' => 24),
				
				array('title' => '完成时间', 'field' => 'finishtime', 'width' => 24),
				array('title' => '状态', 'field' => 'status', 'width' => 12),
				array('title' => '快递公司', 'field' => 'expresscom', 'width' => 24),
				array('title' => '快递单号', 'field' => 'expresssn', 'width' => 24),
				
				
				array('title' => '团长姓名', 'field' => 'head_name', 'width' => 12),
				array('title' => '团长电话', 'field' => 'head_mobile', 'width' => 12),
				array('title' => '完整地址', 'field' => 'fullAddress', 'width' => 24),
				
				
				array('title' => '订单备注', 'field' => 'remark', 'width' => 36),
				array('title' => '卖家订单备注', 'field' => 'remarksaler', 'width' => 36),
				//array('title' => '核销员', 'field' => 'salerinfo', 'width' => 24),
				//array('title' => '核销门店', 'field' => 'storeinfo', 'width' => 36),
				//array('title' => '订单自定义信息', 'field' => 'order_diyformdata', 'width' => 36),
				//array('title' => '商品自定义信息', 'field' => 'goods_diyformdata', 'width' => 36)
			);

			$exportlist = array();
			
			
		
			if (!(empty($total))) {
					
					//begin 
					set_time_limit(0);
					 
					$fileName = date('YmdHis', time());
					header('Content-Type: application/vnd.ms-execl');
					header('Content-Disposition: attachment;filename="订单数据' . $fileName . '.csv"');

					$begin = microtime(true);
					
					$fp = fopen('php://output', 'a');
					
					$step = 100;
					$nums = 10000;
					
					//设置标题
					//$title = array('ID', '用户名', '用户年龄', '用户描述', '用户手机', '用户QQ', '用户邮箱', '用户地址');
					
					$title  = array();
					
					foreach($columns as $key => $item) {
						$title[$item['field']] = iconv('UTF-8', 'GBK', $item['title']);
					}

					fputcsv($fp, $title);
					
					//$page = ceil($total / 500);
			
					
					$sqlcondition .= ' left join ' .C('DB_PREFIX') . 'lionfish_comshop_order_goods ogc on ogc.order_id = o.order_id ';
					
					
					$sql_count = 'SELECT count(o.order_id) as count   
								FROM ' . C('DB_PREFIX'). 'lionfish_comshop_order as o  '.$sqlcondition.' where '  . $condition ;
						
					$total_arr = M()->query($sql_count);	
					
					$total = $total_arr[0]['count'];
					
					$page = ceil($total / 500);
			
					
					
					for($s = 1; $s <= $page; $s++) {
						$offset = ($s-1)* 500;	
						
						
						$sql = 'SELECT o.*,ogc.name as goods_title,ogc.supply_id,ogc.order_goods_id ,ogc.quantity as ogc_quantity,ogc.price,ogc.statements_end_time, 
									ogc.total as goods_total ,ogc.model,ogc.score_for_money as g_score_for_money, ogc.fullreduction_money as g_fullreduction_money,ogc.voucher_credit as g_voucher_credit ,ogc.shipping_fare as g_shipping_fare 
								FROM ' . C('DB_PREFIX') . 'lionfish_comshop_order as o  '.$sqlcondition.' where '  . $condition . ' ORDER BY o.head_id asc,ogc.goods_id desc,  o.`order_id` DESC  limit '."{$offset},500";
						
						$list = M()->query( $sql );
						
					
						$look_member_arr = array();
						$area_arr = array();
						
						if( !empty($list) )
						{
							foreach($list as $val)
							{
								if (defined('ROLE') && ROLE == 'agenter' ) 
								{
									$supper_info = get_agent_logininfo();
									if($supper_info['id'] != $val['supply_id'])
									{
										continue;
									}
								}
					
					
								if( empty($look_member_arr) || !isset($look_member_arr[$val['member_id']]) )
								{
									$member_info = M('lionfish_comshop_member')->where( array('member_id' =>  $val['member_id']) )->find();
									
									$look_member_arr[$val['member_id']] = $member_info;
								}
								$tmp_exval= array();
								$tmp_exval['order_num_alias'] = $val['order_num_alias']."\t";
								$tmp_exval['name'] = $look_member_arr[$val['member_id']]['username'];
								//from_type
								if($val['from_type'] == 'wepro')
								{
									$tmp_exval['openid'] = $look_member_arr[$val['member_id']]['we_openid'];
								}else{
									$tmp_exval['openid'] = $look_member_arr[$val['member_id']]['openid'];
								}
								$tmp_exval['telephone'] = $look_member_arr[$val['member_id']]['telephone'];
								$tmp_exval['member_content'] = $look_member_arr[$val['member_id']]['content'];
								
								$tmp_exval['shipping_name'] = $val['shipping_name'];
								$tmp_exval['shipping_tel'] = $val['shipping_tel'];
								
								//area_arr
								if( empty($area_arr) || !isset($area_arr[$val['shipping_province_id']]) )
								{ 
									$area_arr[$val['shipping_province_id']] = D('Seller/Front')->get_area_info($val['shipping_province_id']);
								}
								
								if( empty($area_arr) || !isset($area_arr[$val['shipping_city_id']]) )
								{ 
									$area_arr[$val['shipping_city_id']] = D('Seller/Front')->get_area_info($val['shipping_city_id']);
								}
								
								if( empty($area_arr) || !isset($area_arr[$val['shipping_country_id']]) )
								{ 
									$area_arr[$val['shipping_country_id']] = D('Seller/Front')->get_area_info($val['shipping_country_id']);
								}
								

								$province_info = $area_arr[$val['shipping_province_id']];

								$city_info = $area_arr[$val['shipping_city_id']];
								$area_info = $area_arr[$val['shipping_country_id']];
								

							

								$tmp_exval['address_city'] = $city_info['name'];
								$tmp_exval['address_area'] = $area_info['name'];
								$tmp_exval['goods_goodssn'] = $val['model'];
								
								
								if($val['delivery'] == 'express'){
									$tmp_exval['address_province'] = $province_info['name']. $city_info['name'].$area_info['name'].$val['shipping_address'];
									$tmp_exval['address_address'] = '';
								}elseif($val['delivery'] == 'pickup'){
									$tmp_exval['address_address'] = $val['shipping_address'];
									$tmp_exval['address_province'] = '';
								}elseif($val['delivery'] == 'tuanz_send'){
									$tmp_exval['tuan_send_address'] = $province_info['name']. $city_info['name'].$area_info['name'].$val['tuan_send_address'];
									$tmp_exval['address_province'] = '';
									$tmp_exval['address_address'] = '';
								}
								
								$tmp_exval['goods_title'] = $val['goods_title'];
								
								$goods_optiontitle = $this->get_order_option_sku($val['order_id'], $val['order_goods_id']);
								$tmp_exval['goods_optiontitle'] = $goods_optiontitle;
								$tmp_exval['quantity'] = $val['ogc_quantity'];
								$tmp_exval['goods_price1'] = $val['price'];
								$tmp_exval['goods_rprice2'] = $val['goods_total'];
								
								$paytype = $val['payment_code'];
								switch($paytype)
								{
									case 'admin':
										$paytype='后台支付';
										break;
									case 'yuer':
										$paytype='余额支付';
										break;
									case 'weixin':
										$paytype='微信支付';
									break;
									default:
										$paytype = '未支付';

								}
								
								
								
								if(!empty($val['head_id'])){
							
									$community_info = D('Seller/Front')->get_community_byid($val['head_id']);
									$tmp_exval['community_name'] = $community_info['communityName'];
									
									if( $is_can_look_headinfo )
									{
										$tmp_exval['fullAddress'] = $community_info['fullAddress'];
										$tmp_exval['head_name'] = $community_info['disUserName'];
										$tmp_exval['head_mobile'] = $community_info['head_mobile'];
									}else{
										$tmp_exval['fullAddress'] = '';
										$tmp_exval['head_name'] = '';
										$tmp_exval['head_mobile'] = '';
									}
								}else{
										$tmp_exval['community_name'] = '';
										$tmp_exval['fullAddress'] = '';
										$tmp_exval['head_name'] = '';
										$tmp_exval['head_mobile'] = '';
								}
								
								
								
								$tmp_exval['paytype'] = $paytype;
								
								//express 快递, pickup 自提, tuanz_send 团长配送
								//$tmp_exval['delivery'] =  $val['delivery'] == 'express'? '快递':'自提';
								if($val['delivery'] == 'express'){
									$tmp_exval['delivery'] = '快递';
								}elseif($val['delivery'] == 'pickup'){
									$tmp_exval['delivery'] = '自提';
								}elseif($val['delivery'] == 'tuanz_send'){
									$tmp_exval['delivery'] = '团长配送';
								}
								
								$tmp_exval['dispatchprice'] = $val['g_shipping_fare'];
								
								$tmp_exval['score_for_money'] = $val['g_score_for_money'];
								$tmp_exval['fullreduction_money'] = $val['g_fullreduction_money'];
								$tmp_exval['voucher_credit'] = $val['g_voucher_credit'];
								
								
								
								$tmp_exval['changeprice'] = $val['changedtotal'];
								$tmp_exval['changedispatchprice'] = $val['changedshipping_fare'];
								
								
								$val['total'] = $val['goods_total']+$val['g_shipping_fare']-$val['g_score_for_money']-$val['g_fullreduction_money'] - $val['g_voucher_credit'];
							
							
								if($val['total'] < 0)
								{
									$val['total'] = 0;
								}
								
								
								$tmp_exval['price'] = $val['total'];
								
								
								$tmp_exval['head_money'] = 0;
								
								
								$head_commiss_order = M('lionfish_community_head_commiss_order')->where( array('order_id' => $val['order_id'],'order_goods_id' => $val['order_goods_id']) )->find();
								
								if( !empty($head_commiss_order) )
								{
									$tmp_exval['head_money'] = $head_commiss_order['money'];
								}
								
								
								
								
								$tmp_exval['status'] = $order_status_arr[$val['order_status_id']];
								
								$tmp_exval['createtime'] = date('Y-m-d H:i:s', $val['date_added']);
								
								
								$tmp_exval['paytime'] = empty($val['pay_time']) ? '' : date('Y-m-d H:i:s', $val['pay_time']);
								$tmp_exval['sendtime'] = empty($val['express_time']) ? '': date('Y-m-d H:i:s', $val['express_time']);
								$tmp_exval['finishtime'] =  empty($val['finishtime']) ? '' : date('Y-m-d H:i:s', $val['finishtime']);
								
								$tmp_exval['receive_time'] =  empty($val['receive_time']) ? '' : date('Y-m-d H:i:s', $val['receive_time']);
						
								$tmp_exval['expresscom'] = $val['dispatchname'];
								$tmp_exval['expresssn'] = $val['shipping_no'];
								$tmp_exval['remark'] = $val['comment'];
								$tmp_exval['remarksaler'] = $val['remarksaler'];
								
								$exportlist[] = $tmp_exval;
								
								$row_arr = array();
						
								foreach($columns as $key => $item) {
									$row_arr[$item['field']] = iconv('UTF-8', 'GBK', $tmp_exval[$item['field']]);
								}
								
								fputcsv($fp, $row_arr);
							}
							
							ob_flush();
							flush();
							
							unset($list);
						}
						
					}
					
					die();
			
				//D('Seller/Excel')->export($exportlist, array('title' => '订单数据', 'columns' => $columns));
			}
		
		}
		
			
		if (!(empty($total))) {
			
			$sql = 'SELECT o.* FROM ' .C('DB_PREFIX'). 'lionfish_comshop_order as o  '.$sqlcondition.' where '  . $condition . ' ORDER BY  o.`order_id` DESC LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize;
			
			
			$list = M()->query($sql);
			$need_list = array();
			
			
			foreach ($list as $key => &$value ) {
				$sql_goods = "select og.* from ".C('DB_PREFIX')."lionfish_comshop_order_goods as og  
								where og.order_id = {$value[order_id]} ";
				
				$goods = M()->query($sql_goods);
				
				
				
				$need_goods = array();
				
				$shipping_fare = 0;
				$fullreduction_money = 0;
				$voucher_credit = 0;
				$totals = 0;
				
				foreach($goods as $key =>$goods_val)
				{
					$goods_val['option_sku'] = $this->get_order_option_sku($value['order_id'], $goods_val['order_goods_id']);
					
					$goods_val['commisson_info'] = array();//load_model_class('commission')->get_order_goods_commission( $value['order_id'], $goods_val['order_goods_id']);
					
					//供应商名称
					$goods_val['shopname'] =  M('lionfish_comshop_supply')->field('shopname')->where( array('id' => $goods_val['supply_id'] ) )->find();

					
					if( $goods_val['is_refund_state'] == 1 )
					{
						
						$refund_info = M('lionfish_comshop_order_refund')->where( array('order_id' => $value['order_id'] ,'order_goods_id' => $goods_val['order_goods_id']) )->find();
						
						$goods_val['refund_info'] = $refund_info;
					}
					
					if (defined('ROLE') && ROLE == 'agenter' ) 
					{
						$supper_info = get_agent_logininfo();
						
						if($supper_info['id'] != $goods_val['supply_id'])
						{
							continue;
						}
					}
					$shipping_fare += $goods_val['shipping_fare'];
					$fullreduction_money += $goods_val['fullreduction_money'];
					$voucher_credit += $goods_val['voucher_credit'];
					$totals += $goods_val['total'];
					
					$need_goods[$key] = $goods_val;
				}
				
				if (defined('ROLE') && ROLE == 'agenter' ) 
				{
					$value['shipping_fare'] = $shipping_fare;
					$value['fullreduction_money'] = $fullreduction_money;
					$value['voucher_credit'] = $voucher_credit;
					$value['total'] = $totals;
				}
				
				//member_id ims_  nickname
				
				$nickname_info = M('lionfish_comshop_member')->field('username as nickname,content')->where( array('member_id' =>  $value['member_id']) )->find();
				
				$nickname = $nickname_info['nickname'];
					
				$value['nickname'] = $nickname;
				$value['member_content'] = $nickname_info['content'];
				
				
				$value['goods'] = $need_goods;
				
				if($value['head_id'] <=0 )
				{
					$value['community_name'] = '';
					$value['head_name'] = '';
					$value['head_mobile'] = '';
					
					$value['province'] = '';
					$value['city'] = '';

				}else{
					$community_info = D('Seller/Front')->get_community_byid($value['head_id']);
					
				
					$value['community_name'] = $community_info['communityName'];
					$value['head_name'] = $community_info['disUserName'];
					$value['head_mobile'] = $community_info['head_mobile'];
					
					$value['province'] = $community_info['province'];
					$value['city'] = $community_info['city'];
				}
				
				
				
				
			}
			$pager = pagination2($total, $pindex, $psize);
		}
		
		//get_order_count($where = '',$uniacid = 0)
		
		if( !empty($searchtype) )
		{
			$count_where .= " and type = '{$searchtype}' ";
		}
		
		
		if (defined('ROLE') && ROLE == 'agenter' ) 
		{
			$supper_info = get_agent_logininfo();
				
			$order_ids_list = M()->query("select og.order_id,og.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money from ".C('DB_PREFIX').
								"lionfish_comshop_order_goods as og , ".C('DB_PREFIX')."lionfish_comshop_order as o  where  og.order_id =o.order_id  and og.supply_id = ".$supper_info['id']."  ");
			$order_ids_arr = array();
			
			$seven_refund_money= 0;
			
			foreach($order_ids_list as $vv)
			{
				if( empty($order_ids_arr) || !isset($order_ids_arr[$vv['order_id']]) )
				{
					$order_ids_arr[$vv['order_id']] = $vv['order_id'];
				}
			}
			if( !empty($order_ids_arr) )
			{
				$count_where .= " and order_id in (".implode(',', $order_ids_arr).")";
			}else{
				$count_where .= " and order_id in (0)";
			}
			
		}
		
		
		$all_count = $this->get_order_count($count_where);
		$count_status_1 = $this->get_order_count(" {$count_where} and order_status_id = 1 ");
		$count_status_3 = $this->get_order_count(" {$count_where} and order_status_id = 3 ");
		$count_status_4 = $this->get_order_count(" {$count_where} and order_status_id = 4 ");
		$count_status_5 = $this->get_order_count(" {$count_where} and order_status_id = 5 ");
		$count_status_7 = $this->get_order_count(" {$count_where} and order_status_id = 7 ");
		$count_status_11 = $this->get_order_count(" {$count_where} and (order_status_id = 11 or order_status_id = 6) ");
		$count_status_14 = $this->get_order_count(" {$count_where} and order_status_id = 14 ");
		

		
		
		return array('total' => $total, 'total_money' => $total_money,'today_total_money' => $today_total_money,'pager' => $pager, 'all_count' => $all_count,
				'list' =>$list,
				'count_status_1' => $count_status_1,'count_status_3' => $count_status_3,'count_status_4' => $count_status_4,
				'count_status_5' => $count_status_5, 'count_status_7' => $count_status_7, 'count_status_11' => $count_status_11,
				'count_status_14' => $count_status_14
				);
	}
	
	//---copy begin 
	
	public function load_afterorder_list($is_pintuan = 0)
	{
		$time = I('request.time');
		
		$starttime = isset($time['start']) ? strtotime($time['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($time['end']) ? strtotime($time['end']) : strtotime(date('Y-m-d'.' 23:59:59'));
		
		$order_status_id =  I('request.order_status_id', 0);
		$state = I('request.state',  -1);
		if($reorder_status_id >0)
		{
			$order_status_id = $reorder_status_id;
		}
		
		$searchtime = I('request.searchtime','');
		$searchfield = I('request.searchfield', '');
		
		$searchtype = I('request.type', '');
		
		if( $is_pintuan == 1 && empty($searchtype) )
		{
			$searchtype = 'pintuan';
		}
		
		$delivery = I('request.delivery', '');
		
		
		$count_where = "";
		$agentid = I('request.agentid', '');
		
		$head_id = I('request.headid', ''); 
		
		
		$pindex = I('request.page', 1);
		$psize = 20;
		
		
		$paras =array();
		
		$sqlcondition = "";
		
		$condition = " 1 ";
		
		if( $_W['role'] == 'agenter' )
		{
			$supper_info = json_decode(base64_decode($_GPC['__lionfish_comshop_agent']), true);
			//$condition .= " and o.supply_id= ".$supper_info['id'];
			
			$order_ids_list_tmp = pdo_fetchall("select order_id from ".tablename('lionfish_comshop_order_goods').
											" where supply_id = ".$supper_info['id']." and uniacid=:uniacid ", array(':uniacid' => $uniacid));
			if( !empty($order_ids_list_tmp) )
			{
				$order_ids_tmp_arr = array();
				foreach($order_ids_list_tmp as  $vv)
				{
					$order_ids_tmp_arr[] = $vv['order_id'];
				}
				$order_ids_tmp_str = implode(',', $order_ids_tmp_arr);
				
				$condition .= " and o.order_id in({$order_ids_tmp_str}) ";
			}
			else{
				$condition .= " and o.order_id in(0) ";
			}
		}
		
		
		if( !empty($searchtype) )
		{
			$condition .= " and o.type ='{$searchtype}'  ";
		}
		
		if( !empty($delivery) )
		{
			$condition .= " and o.delivery ='{$delivery}'  ";
		}
		
		if( !empty($head_id) && $head_id >0 )
		{
			$condition .= " and o.head_id ='{$head_id}'  ";
			
			$count_where .= " and head_id ='{$head_id}'  ";
		}
		
		
		if( $state >= 0 )
		{
			$condition .= " and ore.state ='{$state}'  ";
		}
		
		
		if($order_status_id > 0)
		{
			if($order_status_id ==12 )
			{
				$condition .= " and (o.order_status_id={$order_status_id} or o.order_status_id=10 ) ";
			}else if($order_status_id ==11)
			{
				$condition .= " and (o.order_status_id={$order_status_id} or o.order_status_id=6 ) ";
			}
			else{
				$condition .= " and o.order_status_id={$order_status_id} ";
			}
			
			
		}
		if( $is_fenxiao == 1)
		{
			//分销订单
			
			$condition .= " and o.is_commission = 1  ";
			$count_where = " and is_commission = 1 ";
			
		}
		
		$keyword = I('request.keyword');
		if( !empty($searchfield) && !empty($keyword))
		{
			$keyword = trim($keyword);
			
			$keyword = htmlspecialchars_decode($keyword, ENT_QUOTES);
			
			switch($searchfield)
			{
				case 'ordersn':
					$condition .= ' AND locate("'.$keyword.'",o.order_num_alias)>0'; 
				break;
				case 'member':
					$condition .= ' AND (locate("'.$keyword.'",m.username)>0 or locate("'.$keyword.'",m.telephone)>0 or "'.$keyword.'"=o.member_id )';
					$sqlcondition .= ' left join ' . C('DB_PREFIX') . 'lionfish_comshop_member m on m.member_id = o.member_id ';
				break;
				case 'address':
					$condition .= ' AND ( locate("'.$keyword.'",o.shipping_name)>0 )';
					//shipping_address
				break;
				case 'mobile':
					$condition .= ' AND ( locate("'.$keyword.'",o.shipping_tel)>0 )';
					//shipping_address
				break;
				case 'location':
					$condition .= ' AND (locate("'.$keyword.'",o.shipping_address)>0 )';
				break;
				case 'shipping_no':
					$condition .= ' AND (locate("'.$keyword.'",o.shipping_no)>0 )';
				break;
				
				case 'head_address':
					$head_ids = M('lionfish_community_head')->field('id')->where( 'community_name like "%'.$keyword.'%"' )->select();
					
					
					if(!empty($head_ids))
					{
						$head_ids_arr = array();
						foreach($head_ids as $vv)
						{
							$head_ids_arr[] = $vv['id'];
						}
						$head_ids_str = implode(",",$head_ids_arr);
						$condition .= ' AND ( o.head_id in('.$head_ids_str.') )';
					}else{
						$condition .= " and o.order_id in(0) ";
					}
					
				break;
				case 'head_name':
					// SELECT * FROM `ims_lionfish_community_head` WHERE `head_name` LIKE '%黄%'
					
					$head_ids = M('lionfish_community_head')->field('id')->where( 'head_name like "%'.$keyword.'%"' )->select();
					
					if(!empty($head_ids))
					{
						$head_ids_arr = array();
						foreach($head_ids as $vv)
						{
							$head_ids_arr[] = $vv['id'];
						}
						$head_ids_str = implode(",",$head_ids_arr);
						$condition .= ' AND ( o.head_id in('.$head_ids_str.') )';
					}else{
						
						$condition .= " and o.order_id in(0) ";
		
					}
					
				break;
				case 'goodstitle':
					$sqlcondition = ' inner join ( select DISTINCT(og.order_id) from ' . C('DB_PREFIX').'lionfish_comshop_order_goods og  where  (locate("'.$keyword.'",og.name)>0)) gs on gs.order_id=o.order_id';
				//var_dump($sqlcondition);
				//die();
				
				break;
				case 'supply_name':
					
					$supply_name_sql = 'SELECT id FROM ' . C('DB_PREFIX'). 
										'lionfish_comshop_supply where shopname like "%'.$keyword.'%"';
					$supply_ids = M()->query($supply_name_sql);
					
					
					if(!empty($supply_ids))
					{
						$supply_ids_arr = array();
						foreach($supply_ids as $vv)
						{
							$supply_ids_arr[] = $vv['id'];
						}
						$supply_ids_str = implode(",",$supply_ids_arr);
						
						$order_ids_list_tmp = M('lionfish_comshop_order_goods')->field('order_id')->where( "supply_id in ({$supply_ids_str})" )->select();
						
						if( !empty($order_ids_list_tmp) )
						{
							$order_ids_tmp_arr = array();
							foreach($order_ids_list_tmp as  $vv)
							{
								$order_ids_tmp_arr[] = $vv['order_id'];
							}
							$order_ids_tmp_str = implode(',', $order_ids_tmp_arr);
							
							$condition .= " and o.order_id in({$order_ids_tmp_str}) ";
						}else{
							$condition .= " and o.order_id in(0) ";
						}
					}else{
						$condition .= " and o.order_id in(0) ";
					}
				break;
				case 'trans_id':
					$condition .= ' AND (locate('.$keyword.',o.transaction_id)>0 )';
				break;
				
			}
		}
		
		if( !empty($searchtime) )
		{
			switch( $searchtime )
			{
				case 'create':
					//下单时间 date_added
					$condition .= " and o.date_added>={$starttime} and o.date_added <= {$endtime}";
				break;
				case 'pay':
					//付款时间 
					$condition .= " and o.pay_time>={$starttime} and o.pay_time <= {$endtime}";
				break;
				case 'send':
					//发货时间 
					$condition .= " and o.express_time>={$starttime} and o.express_time <= {$endtime}";
				break;
				case 'finish':
					//完成时间 
					$condition .= " and o.finishtime>={$starttime} and o.finishtime <= {$endtime}";
				break;
			}
		}
		
		
		if (defined('ROLE') && ROLE == 'agenter' ) {
			
			$supper_info = get_agent_logininfo();
		
		
			$total_where = " and supply_id= ".$supper_info['id'];
			
			$order_ids_list = M()->query("select og.order_id,og.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money from ".
								C('DB_PREFIX')."lionfish_comshop_order_goods as og , ".C('DB_PREFIX')."lionfish_comshop_order as o  where  og.order_id =o.order_id and og.supply_id = ".$supper_info['id'] );
			
			$order_ids_arr = array();
			$order_ids_arr_dan = array();
			
			$total_money = 0;
			foreach($order_ids_list as $vv)
			{
				if( empty($order_ids_arr) || !isset($order_ids_arr[$vv['order_id']]) )
				{
					$order_ids_arr[$vv['order_id']] = $vv;
					$order_ids_arr_dan[] = $vv['order_id'];
				}
			}
			
			if( !empty($order_ids_arr_dan) )
			{
				$sql = 'SELECT count(o.order_id) as count FROM ' . C('DB_PREFIX'). 'lionfish_comshop_order as o  '.$sqlcondition.' where ' .  $condition." and o.order_id in (".implode(',', $order_ids_arr_dan).") " ;
				
				$total_arr = M()->query($sql);
				
				$total = $total_arr[0]['count'];
				
				
				$order_ids_list = M()->query("select og.order_id,og.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money from ".C('DB_PREFIX').
								"lionfish_comshop_order_goods as og , ".C('DB_PREFIX')."lionfish_comshop_order as o  where {$condition} and og.order_id =o.order_id and og.supply_id = ".$supper_info['id']."  ");
				
				if( !empty($order_ids_list) )
				{
					foreach($order_ids_list as $vv)
					{
						$total_money += $vv['total']+$vv['shipping_fare']-$vv['voucher_credit']-$vv['fullreduction_money'];
					}
				}
			}else{
				$total = 0; 
			}
			
			
		}else{
			
			$sql = 'SELECT count(o.order_id) as count FROM '.C('DB_PREFIX')."lionfish_comshop_order_refund as ore, " . C('DB_PREFIX'). 'lionfish_comshop_order as o  '.$sqlcondition.' where ore.order_id = o.order_id and  ' .  $condition ;
			
			$total_arr =  M()->query($sql);

			
			$total = $total_arr[0]['count'];
			
			
			
		}
				
		
		
		
		$order_status_arr = $this->get_order_status_name();
		
		$export = I('request.export', 0);
		
		
		if ($export == 1) 
		{
			$is_can_look_headinfo = true;
			$supply_can_look_headinfo = D('Home/Front')->get_config_by_name('supply_can_look_headinfo');
			
			if( $_W['role'] == 'agenter' )
			{
				if( isset($supply_can_look_headinfo) && $supply_can_look_headinfo == 2 )
				{
					$is_can_look_headinfo = false;
				}
			}
		
		
			@set_time_limit(0);
			$columns = array(
				array('title' => '订单编号', 'field' => 'order_num_alias', 'width' => 24),
				array('title' => '昵称', 'field' => 'name', 'width' => 12),
				//array('title' => '会员姓名', 'field' => 'mrealname', 'width' => 12),
				array('title' => 'openid', 'field' => 'openid', 'width' => 24),
				array('title' => '会员手机号', 'field' => 'telephone', 'width' => 12),
				array('title' => '会员备注', 'field' => 'member_content', 'width' => 24),
				
				array('title' => '收货姓名(或自提人)', 'field' => 'shipping_name', 'width' => 12),
				array('title' => '联系电话', 'field' => 'shipping_tel', 'width' => 12),
				array('title' => '收货地址', 'field' => 'address_province', 'width' => 12),
				array('title' => '', 'field' => 'address_city', 'width' => 12),
				array('title' => '', 'field' => 'address_area', 'width' => 12),
				//array('title' => '', 'field' => 'address_street', 'width' => 12),
				array('title' => '提货详细地址', 'field' => 'address_address', 'width' => 12),
				array('title' => '团长配送送货详细地址', 'field' => 'tuan_send_address', 'width' => 22),
				array('title' => '商品名称', 'field' => 'goods_title', 'width' => 24),
				//array('title' => '商品编码', 'field' => 'goods_goodssn', 'width' => 12),
				array('title' => '商品规格', 'field' => 'goods_optiontitle', 'width' => 12),
				array('title' => '商品数量', 'field' => 'quantity', 'width' => 12),
				array('title' => '商品单价', 'field' => 'goods_price1', 'width' => 12),
				//array('title' => '商品单价(折扣后)', 'field' => 'goods_price2', 'width' => 12),
				//array('title' => '商品价格(折扣前)', 'field' => 'goods_rprice1', 'width' => 12),
				array('title' => '商品价格', 'field' => 'goods_rprice2', 'width' => 12),
				array('title' => '支付方式', 'field' => 'paytype', 'width' => 12),
				array('title' => '配送方式', 'field' => 'delivery', 'width' => 12),
				//array('title' => '自提门店', 'field' => 'pickname', 'width' => 24),
				//array('title' => '商品小计', 'field' => 'goodsprice', 'width' => 12),
				array('title' => '运费', 'field' => 'dispatchprice', 'width' => 12),
				array('title' => '积分抵扣', 'field' => 'score_for_money', 'width' => 12),
				//array('title' => '余额抵扣', 'field' => 'deductcredit2', 'width' => 12),
				array('title' => '满额立减', 'field' => 'fullreduction_money', 'width' => 12),
				array('title' => '优惠券优惠', 'field' => 'voucher_credit', 'width' => 12),
				//array('title' => '订单改价', 'field' => 'changeprice', 'width' => 12),
				//array('title' => '运费改价', 'field' => 'changedispatchprice', 'width' => 12),
				array('title' => '应收款(该笔订单总款)', 'field' => 'price', 'width' => 12),
				array('title' => '状态', 'field' => 'status', 'width' => 12),
				array('title' => '团长佣金', 'field' => 'head_money', 'width' => 12),
				array('title' => '下单时间', 'field' => 'createtime', 'width' => 24),
				array('title' => '付款时间', 'field' => 'paytime', 'width' => 24),
				array('title' => '发货时间', 'field' => 'sendtime', 'width' => 24),
				array('title' => '完成时间', 'field' => 'finishtime', 'width' => 24),
				array('title' => '快递公司', 'field' => 'expresscom', 'width' => 24),
				array('title' => '快递单号', 'field' => 'expresssn', 'width' => 24),
				
				array('title' => '小区名称', 'field' => 'community_name', 'width' => 12),
				array('title' => '团长姓名', 'field' => 'head_name', 'width' => 12),
				array('title' => '团长电话', 'field' => 'head_mobile', 'width' => 12),
				array('title' => '完整地址', 'field' => 'fullAddress', 'width' => 24),
				
				
				array('title' => '订单备注', 'field' => 'remark', 'width' => 36),
				array('title' => '卖家订单备注', 'field' => 'remarksaler', 'width' => 36),
				//array('title' => '核销员', 'field' => 'salerinfo', 'width' => 24),
				//array('title' => '核销门店', 'field' => 'storeinfo', 'width' => 36),
				//array('title' => '订单自定义信息', 'field' => 'order_diyformdata', 'width' => 36),
				//array('title' => '商品自定义信息', 'field' => 'goods_diyformdata', 'width' => 36)
			);
			$exportlist = array();
			
			
			set_time_limit(0);
			 
			$fileName = date('YmdHis', time());
			header('Content-Type: application/vnd.ms-execl');
			header('Content-Disposition: attachment;filename="退款订单数据' . $fileName . '.csv"');

			$begin = microtime(true);
			
			$fp = fopen('php://output', 'a');
			
			$step = 100;
			$nums = 10000;
			
			//设置标题
			//$title = array('ID', '用户名', '用户年龄', '用户描述', '用户手机', '用户QQ', '用户邮箱', '用户地址');
			
			$title  = array();
			
			foreach($columns as $key => $item) {
				$title[$item['field']] = iconv('UTF-8', 'GBK', $item['title']);
			}

			fputcsv($fp, $title);
			
			
			$sql_count = 'SELECT count(o.order_id) as count FROM '.C('DB_PREFIX')."lionfish_comshop_order_refund as ore, " 
					. C('DB_PREFIX'). 'lionfish_comshop_order as o  '.$sqlcondition.' where ore.order_id = o.order_id and '  
					. $condition . ' ORDER BY  ore.`ref_id` DESC  ';
			
			$total_arr = M()->query($sql_count);	
			
			$total = $total_arr[0]['count'];
			
			$sqlcondition .= ' left join ' .C('DB_PREFIX') . 'lionfish_comshop_order_goods ogc on ogc.order_id = o.order_id ';
			
			
			$page = ceil($total / 500);
						
			
			if (!(empty($total))) {
			
					//searchfield goodstitle goods_goodssn
					
					for($s = 1; $s <= $page; $s++) {
				
						$offset = ($s-1)* 500;
						
						$sql = 'SELECT o.*,ogc.name as goods_title,ogc.supply_id,ogc.order_goods_id ,ogc.quantity as ogc_quantity,ogc.price,
								ogc.total as goods_total ,ogc.score_for_money as g_score_for_money,ogc.fullreduction_money as g_fullreduction_money,ogc.voucher_credit as g_voucher_credit ,ogc.shipping_fare as g_shipping_fare FROM '.C('DB_PREFIX').
							"lionfish_comshop_order_refund as ore, " . C('DB_PREFIX'). 'lionfish_comshop_order as o  '.$sqlcondition.' where ore.order_id = o.order_id and '  . 
							$condition . ' ORDER BY  ore.`ref_id` DESC limit  ' . "{$offset}, 500";
				
						$list = M()->query($sql);
				
						
						
					
						$look_member_arr = array();
						$area_arr = array();
						
						foreach($list as $val)
						{
							if (defined('ROLE') && ROLE == 'agenter' ) 
							{
								$supper_info = get_agent_logininfo();
								if($supper_info['id'] != $val['supply_id'])
								{
									continue;
								}
							}
				
				
							if( empty($look_member_arr) || !isset($look_member_arr[$val['member_id']]) )
							{
								$member_info = M('lionfish_comshop_member')->where( array('member_id' =>  $val['member_id']) )->find();
								
								$look_member_arr[$val['member_id']] = $member_info;
							}
							$tmp_exval= array();
							$tmp_exval['order_num_alias'] = $val['order_num_alias']."\t";
							$tmp_exval['name'] = $look_member_arr[$val['member_id']]['username'];
							//from_type
							if($val['from_type'] == 'wepro')
							{
								$tmp_exval['openid'] = $look_member_arr[$val['member_id']]['we_openid'];
							}else{
								$tmp_exval['openid'] = $look_member_arr[$val['member_id']]['openid'];
							}
							$tmp_exval['telephone'] = $look_member_arr[$val['member_id']]['telephone'];
							$tmp_exval['member_content'] = $look_member_arr[$val['member_id']]['content'];
							
							$tmp_exval['shipping_name'] = $val['shipping_name'];
							$tmp_exval['shipping_tel'] = $val['shipping_tel'];
							
							//area_arr
							if( empty($area_arr) || !isset($area_arr[$val['shipping_province_id']]) )
							{ 
								$area_arr[$val['shipping_province_id']] = D('Seller/Front')->get_area_info($val['shipping_province_id']);
							}
							
							if( empty($area_arr) || !isset($area_arr[$val['shipping_city_id']]) )
							{ 
								$area_arr[$val['shipping_city_id']] = D('Seller/Front')->get_area_info($val['shipping_city_id']);
							}
							
							if( empty($area_arr) || !isset($area_arr[$val['shipping_country_id']]) )
							{ 
								$area_arr[$val['shipping_country_id']] = D('Seller/Front')->get_area_info($val['shipping_country_id']);
							}
							
							$province_info = $area_arr[$val['shipping_province_id']];
							$city_info = $area_arr[$val['shipping_city_id']];
							$area_info = $area_arr[$val['shipping_country_id']];
							
							$tmp_exval['address_province'] = $province_info['name'];
							$tmp_exval['address_city'] = $city_info['name'];
							$tmp_exval['address_area'] = $area_info['name'];
							$tmp_exval['goods_goodssn'] = $val['model'];
							
							
							$tmp_exval['address_address'] = $val['shipping_address'];
							
							if( $val['delivery'] == 'tuanz_send'){ 
								//$tmp_exval['address_address'] = $val['tuan_send_address'];							
							}
							$tmp_exval['tuan_send_address'] = $val['tuan_send_address'];
							
							$tmp_exval['goods_title'] = $val['goods_title'];
							
							$goods_optiontitle = $this->get_order_option_sku($val['order_id'], $val['order_goods_id']);
							$tmp_exval['goods_optiontitle'] = $goods_optiontitle;
							$tmp_exval['quantity'] = $val['ogc_quantity'];
							$tmp_exval['goods_price1'] = $val['price'];
							$tmp_exval['goods_rprice2'] = $val['goods_total'];
							
							$paytype = $val['payment_code'];
							switch($paytype)
							{
								case 'admin':
									$paytype='后台支付';
									break;
								case 'yuer':
									$paytype='余额支付';
									break;
								case 'weixin':
									$paytype='微信支付';
								break;
								default:
									$paytype = '未支付';

							}
							
							$community_info = D('Seller/Front')->get_community_byid($val['head_id']);
							
								
							$tmp_exval['community_name'] = $community_info['communityName'];
							
							if($is_can_look_headinfo){
								$tmp_exval['fullAddress'] = $community_info['fullAddress'];	
								$tmp_exval['head_name'] = $community_info['disUserName'];
								$tmp_exval['head_mobile'] = $community_info['head_mobile'];	
							}else{
								$tmp_exval['fullAddress'] = '';	
								$tmp_exval['head_name'] = '';
								$tmp_exval['head_mobile'] = '';
							}
							
						
							$tmp_exval['paytype'] = $paytype;
					
							if($val['delivery'] == 'express'){
								$tmp_exval['delivery'] = '快递';
							}elseif($val['delivery'] == 'pickup'){
								$tmp_exval['delivery'] = '自提';
							}elseif($val['delivery'] == 'tuanz_send'){
								$tmp_exval['delivery'] = '团长配送';
							}
							$tmp_exval['dispatchprice'] = $val['g_shipping_fare'];
							$tmp_exval['score_for_money'] = $val['g_score_for_money'];
							
							
							$tmp_exval['fullreduction_money'] = $val['g_fullreduction_money'];
							$tmp_exval['voucher_credit'] = $val['g_voucher_credit'];
							
							
							
							$tmp_exval['changeprice'] = $val['changedtotal'];
							$tmp_exval['changedispatchprice'] = $val['changedshipping_fare'];
							
							
							$val['total'] = $val['goods_total']+$val['g_shipping_fare']-$val['g_score_for_money']-$val['g_fullreduction_money'] - $val['g_voucher_credit'];
						
						
							if($val['total'] < 0)
							{
								$val['total'] = 0;
							}
							
							
							$tmp_exval['price'] = $val['total'];
							
							
							$tmp_exval['head_money'] = 0;
							
							
							$head_commiss_order = M('lionfish_community_head_commiss_order')->where( array('order_id' => $val['order_id'],'order_goods_id' => $val['order_goods_id']) )->find();
							
							if( !empty($head_commiss_order) )
							{
								$tmp_exval['head_money'] = $head_commiss_order['money'];
							}
							
							
							
							
							$tmp_exval['status'] = $order_status_arr[$val['order_status_id']];
							
							$tmp_exval['createtime'] = date('Y-m-d H:i:s', $val['date_added']);
							
							
							$tmp_exval['paytime'] = empty($val['pay_time']) ? '' : date('Y-m-d H:i:s', $val['pay_time']);
							$tmp_exval['sendtime'] = empty($val['express_time']) ? '': date('Y-m-d H:i:s', $val['express_time']);
							$tmp_exval['finishtime'] =  empty($val['finishtime']) ? '' : date('Y-m-d H:i:s', $val['finishtime']);
							
							
							$tmp_exval['expresscom'] = $val['dispatchname'];
							$tmp_exval['expresssn'] = $val['shipping_no'];
							$tmp_exval['remark'] = $val['comment'];
							$tmp_exval['remarksaler'] = $val['remarksaler'];
							
							$exportlist[] = $tmp_exval;
							
							$row_arr = array();
						
							foreach($columns as $key => $item) {
								$row_arr[$item['field']] = iconv('UTF-8', 'GBK', $tmp_exval[$item['field']]);
							}
							
							fputcsv($fp, $row_arr);
						}
						
						ob_flush();
						flush();
						
						unset($list);
					}
					
				die();
				//D('Seller/Excel')->export($exportlist, array('title' => '订单数据', 'columns' => $columns));
			}
			
		}
		
		if (!(empty($total))) {
			
			$sql = 'SELECT ore.ref_id, ore.order_goods_id,ore.state as ore_state, o.* FROM '.
					C('DB_PREFIX')."lionfish_comshop_order_refund as ore, " . C('DB_PREFIX') . 'lionfish_comshop_order as o  '.
					$sqlcondition.' where ore.order_id = o.order_id and '  . $condition . 
					' ORDER BY  ore.`ref_id` DESC LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize;
			
			$list = M()->query($sql);
			$need_list = array();
			foreach ($list as $key => &$value ) {
				
				$sql_goods = "select og.* from ".C('DB_PREFIX')."lionfish_comshop_order_goods as og  
								where  og.order_id = {$value[order_id]} ";
				if( !empty($value['order_goods_id']) && $value['order_goods_id'] > 0 )
				{
					$sql_goods = "select og.* from ".C('DB_PREFIX')."lionfish_comshop_order_goods as og  
								where  og.order_goods_id = ".$value['order_goods_id']." and og.order_id = {$value[order_id]} ";
				}
				
				$goods = M()->query($sql_goods);
				
				
				$need_goods = array();
				
				$shipping_fare = 0;
				$fullreduction_money = 0;
				$voucher_credit = 0;
				$totals = 0;
				
				
				//ref_id 
				
				$refund_disable = M('lionfish_comshop_order_refund_disable')->where( array('ref_id' => $value['ref_id'] ) )->find();
				
				if( !empty($refund_disable) )
				{
					
					$value['is_forbidden'] = 1;
				}else{
					$value['is_forbidden'] = 0;
				}
				
				
				foreach($goods as $key =>$goods_val)
				{
					$goods_val['option_sku'] = $this->get_order_option_sku($value['order_id'], $goods_val['order_goods_id']);
					
					$goods_val['commisson_info'] = array();
				
					if (defined('ROLE') && ROLE == 'agenter' ) 
					{
						$supper_info = get_agent_logininfo();
						
						if($supper_info['id'] != $goods_val['supply_id'])
						{
							continue;
						}
					}
					$shipping_fare += $goods_val['shipping_fare'];
					$fullreduction_money += $goods_val['fullreduction_money'];
					$voucher_credit += $goods_val['voucher_credit'];
					$totals += $goods_val['total'];
					
					$need_goods[$key] = $goods_val;
				}
				
				//if( $_W['role'] == 'agenter' )
				//{
					$value['shipping_fare'] = $shipping_fare;
					$value['fullreduction_money'] = $fullreduction_money;
					$value['voucher_credit'] = $voucher_credit;
					$value['total'] = $totals;
			//	}
				//member_id ims_  nickname
			
				$nickname_row = M('lionfish_comshop_member')->field('username as nickname,content')->where( array('member_id' =>$value['member_id'] ) )->find();
				
				$value['nickname'] = $nickname_row['nickname'];
				$value['member_content'] = $nickname_row['content'];
				
				
				$value['goods'] = $need_goods;
				
				$community_info = D('Seller/Front')->get_community_byid($value['head_id']);
					
				
				
			
				$value['community_name'] = $community_info['communityName'];
				$value['head_name'] = $community_info['disUserName'];
				$value['head_mobile'] = $community_info['head_mobile'];
				
				$value['province'] = $community_info['province'];
				$value['city'] = $community_info['city'];
				
				
			}
			$pager = pagination2($total, $pindex, $psize);
		}
		
		//get_order_count($where = '',$uniacid = 0)
		
		if( !empty($searchtype) )
		{
			$count_where = " and type = '{$searchtype}' ";
		}
		
		if (defined('ROLE') && ROLE == 'agenter' ) 
		{
			
			$supper_info = get_agent_logininfo();
				
			$order_ids_list = M()->query("select og.order_id,og.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money from ".C('DB_PREFIX').
								"lionfish_comshop_order_goods as og , ".C('DB_PREFIX')."lionfish_comshop_order as o  where  og.order_id =o.order_id  and og.supply_id = ".$supper_info['id']."  ");
			$order_ids_arr = array();
			
			$seven_refund_money= 0;
			
			foreach($order_ids_list as $vv)
			{
				if( empty($order_ids_arr) || !isset($order_ids_arr[$vv['order_id']]) )
				{
					$order_ids_arr[$vv['order_id']] = $vv['order_id'];
				}
			}
			if( !empty($order_ids_arr) )
			{
				$count_where .= " and order_id in (".implode(',', $order_ids_arr).")";
			}else{
				$count_where .= " and order_id in (0)";
			}
			
		}
		
		$all_count = $this->get_order_count($count_where);
		$count_status_1 = $this->get_order_count(" {$count_where} and order_status_id = 1 ");
		$count_status_3 = $this->get_order_count(" {$count_where} and order_status_id = 3 ");
		$count_status_4 = $this->get_order_count(" {$count_where} and order_status_id = 4 ");
		$count_status_5 = $this->get_order_count(" {$count_where} and order_status_id = 5 ");
		$count_status_7 = $this->get_order_count(" {$count_where} and order_status_id = 7 ");
		$count_status_11 = $this->get_order_count(" {$count_where} and (order_status_id = 11 or order_status_id = 6) ");
		$count_status_14 = $this->get_order_count(" {$count_where} and order_status_id = 14 ");
		
		
		return array('total' => $total, 'total_money' => $total_money,'pager' => $pager, 'all_count' => $all_count,
				'list' =>$list,
				'count_status_1' => $count_status_1,'count_status_3' => $count_status_3,'count_status_4' => $count_status_4,
				'count_status_5' => $count_status_5, 'count_status_7' => $count_status_7, 'count_status_11' => $count_status_11,
				'count_status_14' => $count_status_14
				);
	}
	
	
	//---copy end
	
	
	
	public function admin_pay_order($order_id)
	{
		$order = M('lionfish_comshop_order')->where( array('order_id' => $order_id) )->find();
		
		
		$member_id = $order['member_id'];

		//支付才减库存，才需要判断
		$kucun_method = D('Home/Front')->get_config_by_name('kucun_method');
						
		if( empty($kucun_method) )
		{
			$kucun_method = 0;
		}
		
		$error_msg = '';
		
		if($kucun_method == 1)
		{
			/*** 检测商品库存begin  **/
			$order_goods_list = M('lionfish_comshop_order_goods')->where( array('order_id' => $order['order_id'] ) )->select();
			
			//goods_id
			foreach($order_goods_list as $val)
			{
				$quantity = $val['quantity'];
				
				$goods_id = $val['goods_id'];
				
				$can_buy_count = D('Home/Front')->check_goods_user_canbuy_count($member_id, $goods_id);
				
				
				//TODO.这里有问题
				$goods_description = D('Home/Front')->get_goods_common_field($goods_id , 'total_limit_count');
				
				if($can_buy_count == -1)
				{
					$error_msg = '每人最多购买'.$goods_description['total_limit_count'].'个哦';
				}else if($can_buy_count >0 && $quantity >$can_buy_count)
				{
					$error_msg = '您还能购买'.$can_buy_count.'份';
				}
				
				$goods_quantity= D('Home/Car')->get_goods_quantity($goods_id);
				
				if($goods_quantity<$quantity){
					
					if ($goods_quantity==0) {
						$error_msg ='已抢光';
					}else{
						$error_msg ='商品数量不足，剩余'.$goods_quantity.'个！！';
					}
				}
			
				//rela_goodsoption_valueid
				if(!empty($val['rela_goodsoption_valueid']))
				{
					$mul_opt_arr = array();
					 
					$goods_option_mult_value = M('lionfish_comshop_goods_option_item_value')->where( array('option_item_ids' => $val['rela_goodsoption_valueid'],'goods_id' => $goods_id) )->find();
					
					if( !empty($goods_option_mult_value) )
					{
						if($goods_option_mult_value['stock']<$quantity){
							
							$error_msg = '商品数量不足，剩余'.$goods_option_mult_value['stock'].'个！！';
						}
					}
				}
				
			}
			/*** 检测商品库存end **/
		}

		if( !empty($error_msg) )
		{
			return array('code' => 0,'msg' => $error_msg);
		}else{
			if( $order && $order['order_status_id'] == 3)
			{
				$o = array();
				$o['payment_code'] = 'admin';
				$o['order_id']=$order['order_id'];
				$o['order_status_id'] =  $order['is_pin'] == 1 ? 2:1;
				$o['date_modified']=time();
				$o['pay_time']=time();
				$o['transaction_id'] = $is_integral ==1? '积分兑换':'余额支付';
				
				//ims_ 
				M('lionfish_comshop_order')->where( array('order_id' => $order['order_id']) )->save($o);
				
				
				
				$kucun_method = D('Home/Front')->get_config_by_name('kucun_method', $_W['uniacid']);
							
				if( empty($kucun_method) )
				{
					$kucun_method = 0;
				}
							
				if($kucun_method == 1)
				{//支付完减库存，增加销量		
								
					$order_goods_list = M('lionfish_comshop_order_goods')->where( array('order_id' => $order['order_id']) )->select();			
					
					foreach($order_goods_list as $order_goods)
					{
						D('Home/Pingoods')->del_goods_mult_option_quantity($order['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity'],1);
						
					}
				}
				
				$oh = array();	
			
				$oh['order_id']=$order['order_id'];
				$oh['order_status_id']= $order['is_pin'] == 1 ? 2:1;
				$oh['comment']='后台付款';
				$oh['date_added']=time();
				$oh['notify']=1;
				
				M('lionfish_comshop_order_history')->add($oh);
				
				D('Home/Weixinnotify')->orderBuy($order['order_id'],true);
				
				//发送购买通知
				//TODO 先屏蔽，等待调试这个消息
				//$weixin_nofity = D('Home/Weixinnotify');
				//$weixin_nofity->orderBuy($order['order_id']);
				return array('code' => 1);
			}
		}
		
		
	}
	
	public function admin_pay_order2($order_id)
	{
		
				
		$order = M('lionfish_comshop_order')->where( array('order_id' => $order_id) )->find();
		
					
		if( $order && $order['order_status_id'] == 3)
		{
			$o = array();
			$o['payment_code'] = 'admin';
			$o['order_id']=$order['order_id'];
			$o['order_status_id'] =  $order['is_pin'] == 1 ? 2:1;
			$o['date_modified']=time();
			$o['pay_time']=time();
			$o['transaction_id'] = $is_integral ==1? '积分兑换':'余额支付';
			
			//ims_ 
			M('lionfish_comshop_order')->where( array('order_id' => $order['order_id']) )->save($o);
			
			
			//暂时屏蔽
			//$kucun_method = C('kucun_method');
			//$kucun_method  = empty($kucun_method) ? 0 : intval($kucun_method);
			$kucun_method = 0;
			
			//$goods_model = D('Home/Goods');
			
			if($kucun_method == 1)
			{//支付完减库存，增加销量
							
				$order_goods_list = M('lionfish_comshop_order_goods')->where( array('order_id' => $order['order_id']) )->select();			
				
				foreach($order_goods_list as $order_goods)
				{
					D('Home/Pingoods')->del_goods_mult_option_quantity($order['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity'],1);
					
				}
			}
			
			$oh = array();	
			
			$oh['order_id']=$order['order_id'];
			$oh['order_status_id']= $order['is_pin'] == 1 ? 2:1;
			$oh['comment']='后台付款';
			$oh['date_added']=time();
			$oh['notify']=1;
			
			M('lionfish_comshop_order_history')->add($oh);
			
				
			//发送购买通知
			//TODO 先屏蔽，等待调试这个消息
			//$weixin_nofity = D('Home/Weixinnotify');
			//$weixin_nofity->orderBuy($order['order_id']);
	
			
		}
	}
	
	
	
	public function receive_order($order_id)
	{
					
		M('lionfish_comshop_order')->where( array('order_id' => $order_id) )->save( array('order_status_id' => 6, 'receive_time' => time()) );	
		
		D('Home/Frontorder')->receive_order($order_id);
		
	}
	
	/**
		获取订单规格值
	**/
	public function get_order_option_sku($order_id, $order_goods_id)
	{
		$option_list = M('lionfish_comshop_order_option')->field('name,value')->where( array('order_goods_id' => $order_goods_id,'order_id' => $order_id) )->select();
		
		$sku_str = "";
		
		if( !empty($option_list) )
		{
			$tmp_arr = array();
			foreach($option_list as $val)
			{
				$tmp_arr[] = $val['name'].",".$val['value'];
			}
			$sku_str = implode(' ', $tmp_arr);
		}
		return $sku_str;
	}
	
	public function get_order_status_name()
	{
		
		$data = S('order_status_name');
		
		if (empty($data)) {
			
			$all_list = M('lionfish_comshop_order_status')->select();

			if (empty($all_list)) {
				$data = array();
			}else{
				$data = array();
				foreach($all_list as $val)
				{
					$data[$val['order_status_id']] = $val['name'];
				}
			}
			S('order_status_name', $data); 
		}
		return $data;
	}
	
	/**
		获取商品数量
	**/
	public function get_order_count($where = '')
	{
		$total = M('lionfish_comshop_order')->where("1 ".$where)->count();	
	    
		return $total;
	}
	
	public function get_wait_shen_order_comment()
	{
		$total = M('lionfish_comshop_order_comment')->where( array('state' => 0, 'type' =>0) )->count();	
	     
	    return $total;
	}
	/**
		获取商品数量
	**/
	public function get_order_sum($field=' sum(total) as total ' , $where = '',$uniacid = 0)
	{
		
		$info = M('lionfish_comshop_order')->field($field)->where("1 ".$where )->find();
	    
		return $info;
	}
	
	/**
	
	**/
	public function get_order_goods_group_paihang($where = '',$uniacid = 0)
	{
		
		//total
		//SELECT name , sum(`quantity`) as total_quantity , goods_id FROM `ims_lionfish_comshop_order_goods` GROUP by goods_id order by total_quantity desc 
		$sql ="SELECT name , sum(`quantity`) as total_quantity, sum(`total`) as m_total , goods_id FROM ". 
				C('DB_PREFIX') ."lionfish_comshop_order_goods where 1 {$where} GROUP by goods_id 
				order by total_quantity desc limit 10 ";
		$list = M()->query($sql);
		
		
		return $list;
	}
	
	
}
?>