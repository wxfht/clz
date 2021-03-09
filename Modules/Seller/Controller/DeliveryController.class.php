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

class DeliveryController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
	}
	public function delivery()
	{
		
		$gpc = I('request.');
		
        $pindex    = max(1, intval($gpc['page']));
        $psize     = 20;
		
		$searchtime = isset($gpc['searchtime']) ? $gpc['searchtime'] : '';
		$starttime = isset($gpc['time']['start']) ? strtotime($gpc['time']['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($gpc['time']['end']) ? strtotime($gpc['time']['end']) : strtotime(date('Y-m-d'.' 23:59:59'));
		
		
		$this->searchtime = $searchtime;
		$this->starttime = $starttime;
		$this->endtime = $endtime;
		
		$condition = "";
		
		if( !empty($searchtime) )
		{
			if(  $searchtime == 'create_time')
			{
				$condition .= " and d.create_time > {$starttime} and d.create_time < {$endtime} ";
			}
			if( $searchtime == 'express_time')
			{
				$condition .= " and d.express_time > {$starttime} and d.express_time < {$endtime} ";
			}
			if( $searchtime == 'head_get_time')
			{
				$condition .= " and d.head_get_time > {$starttime} and d.head_get_time < {$endtime} ";
			}
		}
		

        if (!empty($gpc['keyword'])) {
            $gpc['keyword'] = trim($gpc['keyword']);
            $condition .= ' and (d.head_name like "%'.$gpc['keyword'].'%" or d.head_mobile like "%'.$gpc['keyword'].'%" or d.line_name like "%'.$gpc['keyword'].'%" or d.clerk_name like "%'.$gpc['keyword'].'%" or d.clerk_mobile like "%'.$gpc['keyword'].'%" or h.community_name like "%'.$gpc['keyword'].'%"  )';
            
        }
		
		
		if( isset($gpc['export']) && $gpc['export'] > 0 )
		{
			@set_time_limit(0);
			
			$excel_title = "";
			$search_tiaoj = "";
			
			if( !empty($searchtime) )
			{
				if(  $searchtime == 'create_time')
				{
					$excel_title .= "创建清单时间:".date('Y-m-d H:i:s', $starttime).'  '.date('Y-m-d H:i:s', $endtime);
					
					$search_tiaoj .= "清单时间： ".date('Y-m-d H:i:s', $starttime).'  '.date('Y-m-d H:i:s', $endtime);
				}
				if( $searchtime == 'express_time')
				{
					$excel_title .= "配送时间:".date('Y-m-d H:i:s', $starttime).'  '.date('Y-m-d H:i:s', $endtime);
					$search_tiaoj .= "配送时间： ".date('Y-m-d H:i:s', $starttime).'  '.date('Y-m-d H:i:s', $endtime);
				}
				if( $searchtime == 'head_get_time')
				{
					$excel_title .= "送达时间:".date('Y-m-d H:i:s', $starttime).'  '.date('Y-m-d H:i:s', $endtime);
					$search_tiaoj .= "送达时间： ".date('Y-m-d H:i:s', $starttime).'  '.date('Y-m-d H:i:s', $endtime);
				}
			}
			$excel_title = "";
			if (!empty($gpc['keyword'])) {
				$excel_title .= $gpc['keyword'];
				$search_tiaoj .= "关键词： ".$gpc['keyword'];
			}
			
			$list = M()->query('SELECT d.*,h.community_name FROM ' . C('DB_PREFIX'). "lionfish_comshop_deliverylist as d , ".C('DB_PREFIX')."lionfish_community_head as h 
				WHERE  d.head_id = h.id " . $condition . ' order by d.id desc ');
		
			
			//导出商品总单
			if($gpc['export'] == 1)
			{
				$columns = array(
					array('title' => '商品名称', 'field' => 'goods_name', 'width' => 24),
					array('title' => '商品规格', 'field' => 'sku_name', 'width' => 24),
					array('title' => '数量', 'field' => 'quantity', 'width' => 12),
					array('title' => '单价', 'field' => 'price', 'width' => 12),
					array('title' => '金额', 'field' => 'total_price', 'width' => 12),
				);
				
				$list_id_arr = array();
				foreach($list as $val)
				{
					$list_id_arr[] = $val['id'];
				}
				$need_goods_list = array();
				
				if(!empty($list_id_arr))
				{
					$goods_list = M('lionfish_comshop_deliverylist_goods')->where( "list_id in ( ".implode(',',$list_id_arr )." )" )->select();
				
					foreach($goods_list as $val)
					{
						if(empty($need_goods_list) || !in_array(  $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] , array_keys($need_goods_list) ) )
						{
							//goods_id   rela_goodsoption_valueid 
							$price = 0;
							if( !empty($val['rela_goodsoption_valueid']) )
							{
								
								$price_value = M('lionfish_comshop_order_goods')->where( array('rela_goodsoption_valueid' => $val['rela_goodsoption_valueid'],'goods_id' => $val['goods_id'] ) )->find();
								
								$price = $price_value['price'];
							}else{
								
								$price_value = M('lionfish_comshop_goods')->field('price')->where( array('id' => $val['goods_id'] ) )->find();				
												
								$price = $price_value['price'];
							}
							
							$need_goods_list[ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ] = array('quantity' => $val['goods_count'],'price' => $price,'total_price' => ($val['goods_count'] * $price),'sku_name' => $val['sku_str'],'goods_name' => $val['goods_name']);
						
						}else{
							$need_goods_list[ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ]['quantity'] += $val['goods_count'];
							$need_goods_list[ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ]['total_price'] = $need_goods_list[ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ]['quantity'] * $need_goods_list[ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ]['price'];
						}
					}
				}
				
				
				$lists_info = array(
									'line1' => '商品拣货单',
									'line2' => '检索条件: '.$search_tiaoj,
								);
				
				
				D('Seller/Excel')->export_delivery_goodslist($need_goods_list, array('list_info' => $lists_info,'title' => '商品拣货单', 'columns' => $columns));
				
			}
			//导出团长总单
			if($gpc['export'] == 2)
			{
				//导出配送总单
				
				$columns = array(
					array('title' => '序号', 'field' => 'sn', 'width' => 12),
					array('title' => '商品编码', 'field' => 'goods_goodssn', 'width' => 20), 
					array('title' => '商品名称', 'field' => 'goods_name', 'width' => 24),
					array('title' => '数量', 'field' => 'quantity', 'width' => 12),
					array('title' => '商品规格', 'field' => 'sku_name', 'width' => 24),
					array('title' => '单价', 'field' => 'price', 'width' => 12),
					array('title' => '总价', 'field' => 'total_price', 'width' => 12),
				);
				
				//-----------------  这里要合并开始 downexcel---------------------
				
				$tuanz_data_list = array();
				$exportlist = array();
				
				$list_id_arr = array();
				foreach($list as $val)
				{
					$list_id = $val['id'];
					
							
					$list_data = M('lionfish_comshop_deliverylist_goods')->where( array('list_id' =>$list_id ) )->order('id desc')->select();
				   
				    
					$list_info = M('lionfish_comshop_deliverylist')->where( array('id' => $list_id ) )->find();
					
					if( $val['clerk_id'] > 0)
					{	
						$clerk_name = M('lionfish_comshop_deliveryclerk')->where( array('id' => $val['clerk_id'] ) )->find();
						
						$list_info['clerk_info'] = $clerk_name['name'];
					}
			
					if( !isset($exportlist[$list_info['head_id']]) )
					{
						$exportlist[$list_info['head_id']] = array('list_info' => $list_info ,'data' => array() );
					}
					
					$i =1;
					foreach($list_data as $val)
					{
						$tmp_exval = array();
						$tmp_exval['num_no'] = $i;
						$tmp_exval['name'] = $val['goods_name'];
						$tmp_exval['quantity'] = $val['goods_count'];
						$tmp_exval['sku_str'] = $val['sku_str'];
						
						
						$gd_info = M('lionfish_comshop_goods')->field('codes')->where( array('id' => $val['goods_id'] ) )->find();
						
						$tmp_exval['goods_goodssn'] = $gd_info['codes'];		
						$info = M('lionfish_comshop_order_goods')->field('price')->where( array('rela_goodsoption_valueid' => $val['rela_goodsoption_valueid'],'goods_id' =>$val['goods_id'] ) )->order('order_goods_id desc')->find();
						
						
						$tmp_exval['price'] = $info['price'];
						$tmp_exval['total_price'] = round($info['price'] * $val['goods_count'],2) ;
						
						//goods_id  rela_goodsoption_valueid
						
						if( isset($exportlist[$list_info['head_id']]['data'][ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ]) )
						{
							$tmp_exp = $exportlist[$list_info['head_id']]['data'][ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ];
							
							$tmp_exval['quantity'] += $tmp_exp['quantity'];
							$tmp_exval['total_price'] = round($info['price'] * $tmp_exval['quantity'],2) ;
							
							$exportlist[$list_info['head_id']]['data'][ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ] = $tmp_exval;
						}else{
							$exportlist[$list_info['head_id']]['data'][ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ] = $tmp_exval;
						}
						
						//$exportlist[] = $tmp_exval;
						$i++;
					}
				}
				
				
				$columns = array(
					array('title' => '序号', 'field' => 'num_no', 'width' => 12),
					array('title' => '商品编码', 'field' => 'goods_goodssn', 'width' => 20),
					array('title' => '商品名称', 'field' => 'name', 'width' => 24),
					array('title' => '数量', 'field' => 'quantity', 'width' => 12),
					array('title' => '规格', 'field' => 'sku_str', 'width' => 24),
					array('title' => '单价', 'field' => 'price', 'width' => 24),
					array('title' => '总价', 'field' => 'total_price', 'width' => 24),
				);
				
				
				
				//$params['list_info']
				
				$lists_info = array(
									'line1' => $list_info['head_name'],//团老大
									'line2' => '团长：'.$list_info['head_name'].'     提货地址：'.$list_info['head_address'].'     联系电话：'.$list_info['head_mobile'],//团长：团老大啦     提货地址：湖南大剧院     联系电话：13000000000
									'line3' => '配送单：'.$list_info['list_sn'].'     时间：'.date('Y-m-d H:i:s', $list_info['create_time']),
									'line4' => '配送路线：'.$list_info['line_name'].'     配送员：'.$list_info['clerk_name'],
								);
				
				
				
				
				D('Seller/Excel')->export_delivery_list_pi($exportlist, array('list_info' => $lists_info,'title' => '商品拣货单', 'columns' => $columns));
				
				//-------------------这里要合并结束----------------------
				
			}
			//导出团长旗下订单
			if($gpc['export'] == 3)
			{
				
			}
			//导出配货单
			if($gpc['export'] == 4)
			{
				
			}
			
			//var_dump( $list );die();
			//load_model_class('excel')->export_delivery_list($exportlist, array('list_info' => $lists_info,'title' => '清单数据', 'columns' => $columns));
			//die();
		}
		
		//TODO,.....
        
		$list = M()->query('SELECT d.*,h.community_name FROM ' . C('DB_PREFIX'). "lionfish_comshop_deliverylist as d , ".C('DB_PREFIX')."lionfish_community_head as h 
				WHERE  d.head_id = h.id " . $condition . ' order by d.id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);
		
		
		if( !empty($list) )
		{
			foreach($list as $key => $val )
			{
				
				//$head_info = M('lionfish_community_head')->where( array('id' => $val['head_id'] ) )->find();		
				
				//$val['community_name'] = $head_info['community_name'];
				
				$order_count = M('lionfish_comshop_deliverylist_order')->where( array('list_id' => $val['id'] ) )->count();
				
				$val['order_count'] = $order_count;
				
				$list[$key] = $val;
			}
		}
       
		$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX')."lionfish_comshop_deliverylist as d , ".C('DB_PREFIX')."lionfish_community_head as h ". ' WHERE  d.head_id = h.id ' . $condition );

		$total = $total_arr[0]['count'];
		
        $pager = pagination2($total, $pindex, $psize);
		
		
		
		$this->gpc = $gpc;
		$this->list = $list;
		$this->total = $total;
		$this->pager = $pager;
		
		$this->display();
	}
	
	
	public function downorderexcel()
	{
		$gpc = I('request.');
		
		$list_id = $gpc['list_id'];
		
		
		$paras =array();
		
		
		$sqlcondition = "";
		
		$condition = " 1 ";
		
		$order_ids_arr = M('lionfish_comshop_deliverylist_order')->where( array('list_id' => $list_id ) )->select();
		
		
		$order_ids = array();
		
		foreach($order_ids_arr as $vv)
		{
			$order_ids[] = $vv['order_id'];
		}
		
		if( empty($order_ids) )
		{
			die('无订单数据');
		}
		
		$condition .= " and o.order_id in (".implode(',',$order_ids ).") ";
		
		
		
		$sql = 'SELECT count(o.order_id) as count FROM ' . C('DB_PREFIX') . 'lionfish_comshop_order as o  '.' where ' .  $condition ;
		
		$total_arr = M()->query($sql);
		$total = $total_arr[0]['count']; 
		
		$order_status_arr = D('Seller/Order')->get_order_status_name();
		
		
		
			@set_time_limit(0);
			$columns = array(
				array('title' => '订单编号', 'field' => 'order_num_alias', 'width' => 24),
				array('title' => '昵称', 'field' => 'name', 'width' => 12),
				//array('title' => '会员姓名', 'field' => 'mrealname', 'width' => 12),
				array('title' => 'openid', 'field' => 'openid', 'width' => 24),
				array('title' => '会员手机号', 'field' => 'telephone', 'width' => 12),
				array('title' => '收货姓名(或自提人)', 'field' => 'shipping_name', 'width' => 12),
				array('title' => '联系电话', 'field' => 'shipping_tel', 'width' => 12),
				array('title' => '收货地址', 'field' => 'address_province', 'width' => 12),
				array('title' => '', 'field' => 'address_city', 'width' => 12),
				array('title' => '', 'field' => 'address_area', 'width' => 12),
				//array('title' => '', 'field' => 'address_street', 'width' => 12),
				array('title' => '', 'field' => 'address_address', 'width' => 12),
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
				//array('title' => '积分抵扣', 'field' => 'deductprice', 'width' => 12),
				//array('title' => '余额抵扣', 'field' => 'deductcredit2', 'width' => 12),
				//array('title' => '满额立减', 'field' => 'deductenough', 'width' => 12),
				//array('title' => '优惠券优惠', 'field' => 'couponprice', 'width' => 12),
				array('title' => '订单改价', 'field' => 'changeprice', 'width' => 12),
				array('title' => '运费改价', 'field' => 'changedispatchprice', 'width' => 12),
				array('title' => '应收款', 'field' => 'price', 'width' => 12),
				array('title' => '状态', 'field' => 'status', 'width' => 12),
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
			
			if (!(empty($total))) {
			
				//searchfield goodstitle
				$sqlcondition .= ' left join ' . C('DB_PREFIX') . 'lionfish_comshop_order_goods ogc on ogc.order_id = o.order_id ';
			
				$sql = 'SELECT o.*,ogc.name as goods_title,ogc.order_goods_id ,ogc.quantity as ogc_quantity,ogc.price,
							ogc.total as goods_total 
						FROM ' . C('DB_PREFIX') . 'lionfish_comshop_order as o  '.$sqlcondition.' where '  . $condition . ' ORDER BY o.head_id asc,ogc.goods_id desc,  o.`order_id` DESC  ';
				
				$list = M()->query($sql);
				
				$look_member_arr = array();
				$area_arr = array();
				
				foreach($list as $val)
				{
					if( empty($look_member_arr) || !isset($look_member_arr[$val['member_id']]) )
					{
						$member_info = M('lionfish_comshop_member')->where( array('member_id' => $val['member_id']) )->find();
						
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
					
					$tmp_exval['shipping_name'] = $val['shipping_name'];
					$tmp_exval['shipping_tel'] = $val['shipping_tel'];
					
					//area_arr
					if( empty($area_arr) || !isset($area_arr[$val['shipping_province_id']]) )
					{ 
						$area_arr[$val['shipping_province_id']] = D('Home/Front')->get_area_info($val['shipping_province_id']);
					}
					
					if( empty($area_arr) || !isset($area_arr[$val['shipping_city_id']]) )
					{ 
						$area_arr[$val['shipping_city_id']] = D('Home/Front')->get_area_info($val['shipping_city_id']);
					}
					
					if( empty($area_arr) || !isset($area_arr[$val['shipping_country_id']]) )
					{ 
						$area_arr[$val['shipping_country_id']] = D('Home/Front')->get_area_info($val['shipping_country_id']);
					}
					
					$province_info = $area_arr[$val['shipping_province_id']];
					$city_info = $area_arr[$val['shipping_city_id']];
					$area_info = $area_arr[$val['shipping_country_id']];
					
					$tmp_exval['address_province'] = $province_info['name'];
					$tmp_exval['address_city'] = $city_info['name'];
					$tmp_exval['address_area'] = $area_info['name'];
					$tmp_exval['address_address'] = $val['shipping_address'];
					$tmp_exval['goods_title'] = $val['goods_title'];
					
					$goods_optiontitle = D('Seller/Order')->get_order_option_sku($val['order_id'], $val['order_goods_id']);
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
					
					$community_info = D('Home/Front')->get_community_byid($val['head_id']);
					
						
					$tmp_exval['community_name'] = $community_info['communityName'];
					$tmp_exval['fullAddress'] = $community_info['fullAddress'];
					$tmp_exval['head_name'] = $community_info['disUserName'];
					$tmp_exval['head_mobile'] = $community_info['head_mobile'];
				
				
					$tmp_exval['paytype'] = $paytype;
					
					if($val['delivery'] == 'express')
					{
						$tmp_exval['delivery'] =  '快递';
					}else if($val['delivery'] == 'pickup')
					{
						$tmp_exval['delivery'] =  '自提';
					}else if($val['delivery'] == 'tuanz_send'){
						$tmp_exval['delivery'] =  '团长配送';
					}
					
					
					
					$tmp_exval['dispatchprice'] = $val['shipping_fare'];
					$tmp_exval['changeprice'] = $val['changedtotal'];
					$tmp_exval['changedispatchprice'] = $val['changedshipping_fare'];
					$tmp_exval['price'] = $val['total'];
					$tmp_exval['status'] = $order_status_arr[$val['order_status_id']];
					
					$tmp_exval['createtime'] = date('Y-m-d H:i:s', $val['date_added']);
					$tmp_exval['paytime'] = date('Y-m-d H:i:s', $val['pay_time']);
					
					$tmp_exval['sendtime'] = date('Y-m-d H:i:s', $val['express_time']);
					$tmp_exval['finishtime'] = date('Y-m-d H:i:s', $val['finishtime']);
					
					$tmp_exval['expresscom'] = $val['dispatchname'];
					$tmp_exval['expresssn'] = $val['shipping_no'];
					$tmp_exval['remark'] = $val['comment'];
					$tmp_exval['remarksaler'] = $val['remarksaler'];
					
					$exportlist[] = $tmp_exval;
				}
			}
			
			D('Seller/Excel')->export($exportlist, array('title' => '配送清单-订单数据', 'columns' => $columns));
			
		
	}
	
	public function deldeliverylist()
	{
		
		$line_id = I('request.id');
		 
		M('lionfish_comshop_deliveryline_headrelative')->where( array('line_id' => $line_id) )->delete();
		M('lionfish_comshop_deliveryline')->where( array('id' => $line_id) )->delete();
		
		
		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	
	public function downexcel()
	{
		$gpc = I('request.');
		
		$list_id = $gpc['list_id'];
		
		$condition = " and list_id={$list_id} ";
		
        $list = M()->query('SELECT * FROM ' . C('DB_PREFIX') . "lionfish_comshop_deliverylist_goods 
		WHERE 1 " . $condition . ' order by id desc ');
       
	   
	   
		$exportlist = array();
		
		$i =1;
		foreach($list as $val)
		{
			$tmp_exval = array();
			$tmp_exval['num_no'] = $i;
			$tmp_exval['name'] = $val['goods_name'];
			$tmp_exval['quantity'] = $val['goods_count'];
			$tmp_exval['sku_str'] = $val['sku_str'];
			
			$info = M('lionfish_comshop_order_goods')->field('price')->where( array('rela_goodsoption_valueid' => $val['rela_goodsoption_valueid'],'goods_id' => $val['goods_id']) )->order('order_goods_id desc')->find();
			
			
			$tmp_exval['price'] = $info['price'];
			$tmp_exval['total_price'] = round($info['price'] * $val['goods_count'],2) ;
			
			//goods_id  rela_goodsoption_valueid
			
			$exportlist[] = $tmp_exval;
			$i++;
		}
		
		$columns = array(
			array('title' => '序号', 'field' => 'num_no', 'width' => 12),
			array('title' => '商品名称', 'field' => 'name', 'width' => 24),
			array('title' => '数量', 'field' => 'quantity', 'width' => 12),
			array('title' => '规格', 'field' => 'sku_str', 'width' => 24),
			array('title' => '单价', 'field' => 'price', 'width' => 24),
			array('title' => '总价', 'field' => 'total_price', 'width' => 24),
		);
		
					
		$list_info = M('lionfish_comshop_deliverylist')->where( array('id' => $list_id ) )->find();
		
		//$params['list_info']
		
		$lists_info = array(
							'line1' => $list_info['head_name'],//团老大
							'line2' => '团长：'.$list_info['head_name'].'     提货地址：'.$list_info['head_address'].'     联系电话：'.$list_info['head_mobile'],//团长：团老大啦     提货地址：湖南大剧院     联系电话：13000000000
							'line3' => '配送单：'.$list_info['list_sn'].'     时间：'.date('Y-m-d H:i:s', $list_info['create_time']),
							'line4' => '配送路线：'.$list_info['line_name'].'     配送员：'.$list_info['clerk_name'],
						);
		
		
		
		D('Seller/Excel')->export_delivery_list($exportlist, array('list_info' => $lists_info,'title' => '清单数据', 'columns' => $columns));
		die();
	}
	
	public function list_goodslist()
	{
		$gpc = I('request.');
		
		$list_id = $gpc['list_id'];
		
	
        $pindex    = max(1, intval($gpc['page']));
        $psize     = 20;

		$condition = " and list_id={$list_id} ";
		
        if (!empty($gpc['keyword'])) {
            $gpc['keyword'] = trim($gpc['keyword']);
            $condition .= ' and (name like "%'.$gpc['keyword'].'%"  )';
        }

        $list = M()->query('SELECT * FROM ' .C('DB_PREFIX') . "lionfish_comshop_deliverylist_goods  
		WHERE 1 " . $condition . ' order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);
       
	    $total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX') . 'lionfish_comshop_deliverylist_goods 
					WHERE 1 ' . $condition);
		
		$total = $total_arr[0]['count'];		

        $pager = pagination2($total, $pindex, $psize);
				
		$list_info = M('lionfish_comshop_deliverylist')->where( array('id' => $list_id) )->find();		
		
		
		$this->list = $list;
		$this->list_id = $list_id;
		$this->list_info = $list_info;
		$this->pager = $pager;
		
		$this->display();
		
	}

	
	public function sub_song()
	{
		
		$_GPC = I('request.');
		
		$list_id = $_GPC['id'];
		
		$this->do_sub_song( $list_id  );
 
		show_json(1, array('msg' =>'配送清单成功','url' => $_SERVER['HTTP_REFERER'] ));
	}
	
	/**
		将订单状态为配送中
	**/
	private function do_sub_song( $list_id  )
	{
		$list_info = M('lionfish_comshop_deliverylist')->where( array('id' => $list_id ) )->find();			
						
		if( !empty($list_info) )
		{
			//变更线路状态。变更订单状态为配送中
			
			$order_relates = M('lionfish_comshop_deliverylist_order')->where( array('list_id' => $list_id ) )->select();					
								
			if( !empty($order_relates) )
			{
				foreach($order_relates as $order_val)
				{
					
					$order_status_id_info = M('lionfish_comshop_order')->field('order_status_id')->where( array('order_id' => $order_val['order_id'] ) )->find();
					
					$order_status_id = $order_status_id_info['order_status_id'];
					
					//待发货才行
					if($order_status_id == 1)
					{
						$data = array();
			
						$data['express_time'] = time();
						
						$data['order_status_id'] = 14;
						
						M('lionfish_comshop_order')->where( array('order_id' => $order_val['order_id']) )->save( $data );
						
						$history_data = array();
						$history_data['order_id'] = $order_val['order_id'];
						$history_data['order_status_id'] = 14;
						$history_data['notify'] = 0;
						$history_data['comment'] = '订单配送中，使用清单发货';
						$history_data['date_added'] = time();
						
						M('lionfish_comshop_order_history')->add($history_data);
					}
				}
			}
			
			M('lionfish_comshop_deliverylist')->where( array('id' => $list_id ) )->save( array('state' => 1,'express_time' => time()) );
		}
		
	}
	
	
	public function onekey_tosend()
	{
		$_GPC = I('request.');
		
		$ids_arr = $_GPC['ids_arr'];
		$sec = $_GPC['sec'];
		
		$cache_key = md5(time().count($ids_arr).$sec);
		
		$quene_order_list = array();
		
		if( $sec == 1 )
		{
			//限定配送数组
			S('deliveryquene_'.$cache_key, $ids_arr);
			// lionfish_comshop_deliverylist
		}else{
			//全部群发数组
			
			$deliverylist = M('lionfish_comshop_deliverylist')->field('id')->where( array('state' => 0 ) )->select();
			
			foreach($deliverylist as $val)
			{
				$quene_order_list[]  = $val['id'];
			}
			
			S('deliveryquene_'.$cache_key, $quene_order_list);
		}
		
		$this->cache_key = $cache_key;
		
		$this->display();
	}
	
	public function onekey_tosendover()
	{
		$_GPC = I('request.');
		
		$ids_arr = $_GPC['ids_arr'];
		$sec = $_GPC['sec'];
		
		$cache_key = md5(time().count($ids_arr).$sec);
		
		$quene_order_list = array();
		
		if( $sec == 1 )
		{
			//限定配送数组
			S('deliveryqueneing_'.$cache_key, $ids_arr);
			// lionfish_comshop_deliverylist
		}else{
			//全部群发数组
			$deliverylist = M('lionfish_comshop_deliverylist')->field('id')->where( array('state' => 1 ) )->select();
			
			foreach($deliverylist as $val)
			{
				$quene_order_list[]  = $val['id'];
			}
			
			S('deliveryqueneing_'.$cache_key, $quene_order_list);
		}
		
		$this->cache_key =$cache_key;
		
		$this->display();
	}
	
	/**
		批量处理队列
	**/
	public function do_deliverying_quene()
	{
		$_GPC = I('request.');
		
		$cache_key = $_GPC['cache_key'];
		
		$quene_order_list = S('deliveryqueneing_'.$cache_key);
		
		$delivery_id = array_shift($quene_order_list);
		
		S('deliveryqueneing_'.$cache_key, $quene_order_list);
		
		$delivery_info = M('lionfish_comshop_deliverylist')->where( array('id' => $delivery_id ) )->find();
		
		if( $delivery_info['state'] == 1 )
		{
			
			M('lionfish_comshop_deliverylist')->where( array('id' => $delivery_id ) )->save( array('state' => 2,'head_get_time' => time() ) );
			
			//对订单操作，可以去提货了
			
			$order_ids_all = M('lionfish_comshop_deliverylist_order')->where( array('list_id' => $delivery_id ) )->select();
					
			if( !empty($order_ids_all) )
			{
				foreach($order_ids_all as $order_val)
				{
					
					$order_status_info = M('lionfish_comshop_order')->field('')->where( array('order_id' => $order_val['order_id'] ) )->find();
					
					$order_status_id = $order_status_info['order_status_id'];
					
					//配送中才能
					if($order_status_id == 14)
					{
						$history_data = array();
						$history_data['order_id'] = $order_val['order_id'];
						$history_data['order_status_id'] = 4;
						$history_data['notify'] = 0;
						$history_data['comment'] = '后台一键团长签收配送清单';
						$history_data['date_added'] = time();
						
						M('lionfish_comshop_order_history')->add( $history_data );
			
						//send_order_operate
						D('Home/Frontorder')->send_order_operate($order_val['order_id']);
					}
				}
			}
		}
		
		if( empty($quene_order_list) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}
		
		//清单编号   
		
		echo json_encode( array('code' => 0, 'msg' => '清单编号：'.$delivery_info['list_sn']." 处理成功，还剩余".count($quene_order_list)."个清单未处理") );
		die();
		
	}
	
	/**
		批量处理配送队列
	**/
	public function do_delivery_quene()
	{
		$_GPC = I('request.');
		
		
		$cache_key = $_GPC['cache_key'];
		
		$quene_order_list = S('deliveryquene_'.$cache_key);
		
		$delivery_id = array_shift($quene_order_list);
		
		S('deliveryquene_'.$cache_key, $quene_order_list);
		
		
		$delivery_info = M('lionfish_comshop_deliverylist')->where( array('id' => $delivery_id ) )->find();
		
		if( $delivery_info['state'] == 0 )
		{
			if( $delivery_info['state'] == 0 )
			{
				$this->do_sub_song( $delivery_id  );
			}
		}
		
		if( empty($quene_order_list) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}
		
		//清单编号  
		echo json_encode( array('code' => 0, 'msg' => '清单编号：'.$delivery_info['list_sn']." 处理成功，还剩余".count($quene_order_list)."个清单未处理") );
		die();
		
	}
	
	
	public function delivery_clerk()
	{
		$_GPC = I('request.');
		
		
        $pindex    = max(1, intval($_GPC['page']));
        $psize     = 20;

        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and (name like "%'.$_GPC['keyword'].'%"  )';
        }

        $list = M()->query('SELECT * FROM ' . C('DB_PREFIX') . "lionfish_comshop_deliveryclerk   
		WHERE 1 " . $condition . ' order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);
       
	    $total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX'). 'lionfish_comshop_deliveryclerk WHERE 1 ' . $condition);

		$total = $total_arr[0]['count'];
		
		
        $pager = pagination2($total, $pindex, $psize);
      
	  
		$this->gpc = $_GPC;
		$this->list = $list;
		$this->pager = $pager;
		
		
		$this->display();
	}
	
	
	public function head_ordergoods_detail()
	{
		$_GPC = I('request.');
		
		$head_id = $_GPC['head_id'];
		
		$searchtime = isset($_GPC['searchtime']) ? $_GPC['searchtime'] : '';
		$starttime = isset($_GPC['time']['start']) ? strtotime($_GPC['time']['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($_GPC['time']['end']) ? strtotime($_GPC['time']['end']) : strtotime(date('Y-m-d'.' 23:59:59'));
		
		
		
		$order_condition = "  ";
		
		if( !empty($searchtime) )
		{
			$order_condition .= " and pay_time >={$starttime} and pay_time<= {$endtime} ";
		}
		
		
		$goods_count_sql = "SELECT * FROM ".C('DB_PREFIX')."lionfish_comshop_order_goods where 1 and order_id in 
								(SELECT order_id from ".C('DB_PREFIX')."lionfish_comshop_order where is_refund_state=0 and is_delivery_flag = 0 and head_id={$head_id} and delivery != 'express'   {$order_condition} and order_status_id =1 )";
		
		$goods_list = M()->query($goods_count_sql);
		
		$show_goods_list = array();
		
		foreach($goods_list as $val)
		{
			if( empty($show_goods_list) || !in_array( $val['goods_id'].'_'.$val['rela_goodsoption_valueid'], array_keys($show_goods_list) ) )
			{
				$sku_name = '';
				$sku_arr = array();
				
				$order_option_info = M()->query("select value from ".C('DB_PREFIX')."lionfish_comshop_order_option where order_id=".$val['order_id']." and order_goods_id=".$val['order_goods_id'] );
			  
				foreach($order_option_info as $option)
				{
					$sku_arr[] = $option['value'];
				}
				
				if(empty($sku_arr))
				{
					$sku_name = '';
				}else{
					$sku_name = implode(',', $sku_arr);
				}
				
				$show_goods_list[ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ] = 
								array( 'name' => $val['name'],'sku_name' =>$sku_name,'quantity' => $val['quantity'],'rela_goodsoption_valueid' => $val['rela_goodsoption_valueid'] );
			}else{
				$show_goods_list[ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ]['quantity'] += $val['quantity'];
			}
		}
		
		$this->head_id = $head_id;
		$this->gpc = $_GPC;
		$this->starttime = $starttime;
		$this->endtime = $endtime;
		$this->show_goods_list = $show_goods_list;
		
		$this->display();
	}
	
	/**
		根据团长生成所有未生成的配送清单
	**/
	private function do_su_delivery_list($head_id)
	{
		$head_info = M('lionfish_community_head')->where( array('id' => $head_id ) )->find();
			
			
		$province = D('Home/Front')->get_area_info($head_info['province_id']); 
		$city = D('Home/Front')->get_area_info($head_info['city_id']); 
		$area = D('Home/Front')->get_area_info($head_info['area_id']); 
		$country = D('Home/Front')->get_area_info($head_info['country_id']); 
	
		$full_name = $province['name'].$city['name'].$area['name'].$country['name'].$head_info['address'];
	
	
		$order_condition = "  ";
		
		if( !empty($searchtime) )
		{
			//$order_condition .= " and pay_time >={$starttime} and pay_time<= {$endtime} ";
		}
		
		
		$goods_count_sql = "SELECT * FROM ".C('DB_PREFIX')."lionfish_comshop_order_goods where 1 and order_id in 
								(SELECT order_id from ".C('DB_PREFIX')."lionfish_comshop_order where is_delivery_flag = 0 and head_id={$head_id} {$order_condition} and delivery != 'express'   and order_status_id =1 )";
		
		$goods_list = M()->query($goods_count_sql);
		
		$show_goods_list = array();

		$goods_count =0;
		
		$order_id_list = array();
		
		foreach($goods_list as $val)
		{
			if( empty($order_id_list) || !in_array( $val['order_id'], $order_id_list ) )
			{
				$order_id_list[] = $val['order_id'];
			}
			
			if( empty($show_goods_list) || !in_array( $val['goods_id'].'_'.$val['rela_goodsoption_valueid'], array_keys($show_goods_list) ) )
			{
				$sku_name = '';
				$sku_arr = array();
				
				$order_option_info = M()->query("select value from ".C('DB_PREFIX')."lionfish_comshop_order_option  
						where order_id=".$val['order_id']." and order_goods_id=".$val['order_goods_id']);
						
						
			  
				foreach($order_option_info as $option)
				{
					$sku_arr[] = $option['value'];
				}
				
				if(empty($sku_arr))
				{
					$sku_name = '';
				}else{
					$sku_name = implode(',', $sku_arr);
				}
				$goods_count += $val['quantity'];
				$show_goods_list[ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ] = 
								array('goods_id' => $val['goods_id'], 'name' => $val['name'],'goods_images' => $val['goods_images'],'sku_name' =>$sku_name,'quantity' => $val['quantity'],'rela_goodsoption_valueid' => $val['rela_goodsoption_valueid'] );
			}else{
				$goods_count += $val['quantity'];
				$show_goods_list[ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ]['quantity'] += $val['quantity'];
			}
		}
		
		//ims_ 
		$line_relate_head = M('lionfish_comshop_deliveryline_headrelative')->field('line_id')->where( array('head_id' => $head_id) )->find();
		
		$line_id = 0;
		$line_name = '';
		$clerk_id = 0;
		
		if( !empty($line_relate_head) )
		{
			$line_id = $line_relate_head['line_id'];
			
			$line_info = M('lionfish_comshop_deliveryline')->field('name,clerk_id')->where( array('id' => $line_id ) )->find();
						
			$line_name = $line_info['name'];
			
			$clerk_id = $line_info['clerk_id'];
			//line_name
		}
		
		$clerk_name = '';
		$clerk_mobile = '';
		
		if( $clerk_id > 0 )
		{
			$clerk_info = M('lionfish_comshop_deliveryclerk')->where( array('id' => $clerk_id ) )->find();
			
			$clerk_name = $clerk_info['name'];
			$clerk_mobile = $clerk_info['mobile'];
		}
		
		
		$lionfish_comshop_deliverylist_data = array();
		$lionfish_comshop_deliverylist_data['list_sn'] = build_order_no($head_id);
		$lionfish_comshop_deliverylist_data['head_id'] = $head_id;
		$lionfish_comshop_deliverylist_data['head_name'] = $head_info['head_name'];
		$lionfish_comshop_deliverylist_data['head_mobile'] = $head_info['head_mobile'];
		$lionfish_comshop_deliverylist_data['head_address'] = $full_name;
		$lionfish_comshop_deliverylist_data['line_id'] = $line_id;
		$lionfish_comshop_deliverylist_data['line_name'] = $line_name;
		$lionfish_comshop_deliverylist_data['clerk_id'] = $clerk_id;
		$lionfish_comshop_deliverylist_data['clerk_name'] = $clerk_name;
		$lionfish_comshop_deliverylist_data['clerk_mobile'] = $clerk_mobile;
		$lionfish_comshop_deliverylist_data['state'] = 0;
		$lionfish_comshop_deliverylist_data['goods_count'] = $goods_count;
		$lionfish_comshop_deliverylist_data['express_time'] = 0;
		$lionfish_comshop_deliverylist_data['create_time'] = time();
		$lionfish_comshop_deliverylist_data['addtime'] = time();
		
		$list_id =  M('lionfish_comshop_deliverylist')->add($lionfish_comshop_deliverylist_data);
		
		foreach($show_goods_list as $goods_val)
		{
			//ims_ lionfish_comshop_deliverylist_goods
			$lionfish_comshop_deliverylist_goods_data = array();
			$lionfish_comshop_deliverylist_goods_data['list_id'] = $list_id;
			$lionfish_comshop_deliverylist_goods_data['goods_id'] = $goods_val['goods_id'];
			$lionfish_comshop_deliverylist_goods_data['goods_name'] = $goods_val['name'];
			$lionfish_comshop_deliverylist_goods_data['rela_goodsoption_valueid'] = $goods_val['rela_goodsoption_valueid'];
			$lionfish_comshop_deliverylist_goods_data['sku_str'] = $goods_val['sku_name'];
			$lionfish_comshop_deliverylist_goods_data['goods_image'] = $goods_val['goods_images'];
			$lionfish_comshop_deliverylist_goods_data['goods_count'] = $goods_val['quantity'];
			$lionfish_comshop_deliverylist_goods_data['addtime'] = time();
			
			M('lionfish_comshop_deliverylist_goods')->add($lionfish_comshop_deliverylist_goods_data);
		}
		
		foreach($order_id_list as $order_id)
		{
			//ims_ lionfish_comshop_deliverylist_order
			$lionfish_comshop_deliverylist_order_data = array();
			$lionfish_comshop_deliverylist_order_data['list_id'] = $list_id;
			$lionfish_comshop_deliverylist_order_data['order_id'] = $order_id;
			$lionfish_comshop_deliverylist_order_data['addtime'] = time();
			
			M('lionfish_comshop_deliverylist_order')->add($lionfish_comshop_deliverylist_order_data);
			
			M('lionfish_comshop_order')->where( array('order_id' => $order_id ) )->save( array('is_delivery_flag' => 1) );
		
		}
		
	}
	
	public function sub_delivery_list()
	{
        $_GPC = I('request.');
		
		$head_id = $_GPC['head_id'];
		$searchtime  = isset($_GPC['searchtime']) ? $_GPC['searchtime'] : '';
		$starttime  = isset($_GPC['starttime']) ? $_GPC['starttime'] : '';
		$endtime  = isset($_GPC['endtime']) ? $_GPC['endtime'] : '';
		
		if (empty($head_id)) {
            $head_id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
        }
		
		$head_arr = explode(',', $head_id);
		
		
		foreach( $head_arr as $head_id )
		{
			if( empty($head_id) )
			{
				continue;
			}
			
			$head_info = M('lionfish_community_head')->where( array('id' => $head_id ) )->find();
			
			
			$province = D('Home/Front')->get_area_info($head_info['province_id']); 
			$city = D('Home/Front')->get_area_info($head_info['city_id']); 
			$area = D('Home/Front')->get_area_info($head_info['area_id']); 
			$country = D('Home/Front')->get_area_info($head_info['country_id']); 
		
			$full_name = $province['name'].$city['name'].$area['name'].$country['name'].$head_info['address'];
		
		
			$order_condition = "  ";
			
			if( !empty($searchtime) )
			{
				if( $searchtime == 'create' )
				{
					$order_condition .= " and date_added >={$starttime} and date_added<= {$endtime} ";
				}else if( $searchtime == 'pay' ){
					$order_condition .= " and pay_time >={$starttime} and pay_time<= {$endtime} ";	
				}
			}
			
			
			$goods_count_sql = "SELECT * FROM ".C('DB_PREFIX')."lionfish_comshop_order_goods where 1 and is_refund_state=0  and order_id in 
									(SELECT order_id from ".C('DB_PREFIX')."lionfish_comshop_order where is_refund_state=0 and is_delivery_flag = 0 and head_id={$head_id} {$order_condition} and delivery != 'express'   and order_status_id =1 )";
			
			$goods_list = M()->query($goods_count_sql);
			
			$show_goods_list = array();

			$goods_count =0;
			
			$order_id_list = array();
			
			foreach($goods_list as $val)
			{
				if( empty($order_id_list) || !in_array( $val['order_id'], $order_id_list ) )
				{
					$order_id_list[] = $val['order_id'];
				}
				
				if( empty($show_goods_list) || !in_array( $val['goods_id'].'_'.$val['rela_goodsoption_valueid'], array_keys($show_goods_list) ) )
				{
					$sku_name = '';
					$sku_arr = array();
					
					$order_option_info = M()->query("select value from ".C('DB_PREFIX')."lionfish_comshop_order_option  
							where order_id=".$val['order_id']." and order_goods_id=".$val['order_goods_id']);
							
							
				  
					foreach($order_option_info as $option)
					{
						$sku_arr[] = $option['value'];
					}
					
					if(empty($sku_arr))
					{
						$sku_name = '';
					}else{
						$sku_name = implode(',', $sku_arr);
					}
					$goods_count += $val['quantity'];
					$show_goods_list[ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ] = 
									array('goods_id' => $val['goods_id'], 'name' => $val['name'],'goods_images' => $val['goods_images'],'sku_name' =>$sku_name,'quantity' => $val['quantity'],'rela_goodsoption_valueid' => $val['rela_goodsoption_valueid'] );
				}else{
					$goods_count += $val['quantity'];
					$show_goods_list[ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ]['quantity'] += $val['quantity'];
				}
			}
			
			//ims_ 
			$line_relate_head = M('lionfish_comshop_deliveryline_headrelative')->field('line_id')->where( array('head_id' => $head_id) )->find();
			
			$line_id = 0;
			$line_name = '';
			$clerk_id = 0;
			
			if( !empty($line_relate_head) )
			{
				$line_id = $line_relate_head['line_id'];
				
				$line_info = M('lionfish_comshop_deliveryline')->field('name,clerk_id')->where( array('id' => $line_id ) )->find();
							
				$line_name = $line_info['name'];
				
				$clerk_id = $line_info['clerk_id'];
				//line_name
			}
			
			$clerk_name = '';
			$clerk_mobile = '';
			
			if( $clerk_id > 0 )
			{
				$clerk_info = M('lionfish_comshop_deliveryclerk')->where( array('id' => $clerk_id ) )->find();
				
				$clerk_name = $clerk_info['name'];
				$clerk_mobile = $clerk_info['mobile'];
			}
			
			
			$lionfish_comshop_deliverylist_data = array();
			$lionfish_comshop_deliverylist_data['list_sn'] = build_order_no($head_id);
			$lionfish_comshop_deliverylist_data['head_id'] = $head_id;
			$lionfish_comshop_deliverylist_data['head_name'] = $head_info['head_name'];
			$lionfish_comshop_deliverylist_data['head_mobile'] = $head_info['head_mobile'];
			$lionfish_comshop_deliverylist_data['head_address'] = $full_name;
			$lionfish_comshop_deliverylist_data['line_id'] = $line_id;
			$lionfish_comshop_deliverylist_data['line_name'] = $line_name;
			$lionfish_comshop_deliverylist_data['clerk_id'] = $clerk_id;
			$lionfish_comshop_deliverylist_data['clerk_name'] = $clerk_name;
			$lionfish_comshop_deliverylist_data['clerk_mobile'] = $clerk_mobile;
			$lionfish_comshop_deliverylist_data['state'] = 0;
			$lionfish_comshop_deliverylist_data['goods_count'] = $goods_count;
			$lionfish_comshop_deliverylist_data['express_time'] = 0;
			$lionfish_comshop_deliverylist_data['create_time'] = time();
			$lionfish_comshop_deliverylist_data['addtime'] = time();
			
			$list_id =  M('lionfish_comshop_deliverylist')->add($lionfish_comshop_deliverylist_data);
			
			foreach($show_goods_list as $goods_val)
			{
				//ims_ lionfish_comshop_deliverylist_goods
				$lionfish_comshop_deliverylist_goods_data = array();
				$lionfish_comshop_deliverylist_goods_data['list_id'] = $list_id;
				$lionfish_comshop_deliverylist_goods_data['goods_id'] = $goods_val['goods_id'];
				$lionfish_comshop_deliverylist_goods_data['goods_name'] = $goods_val['name'];
				$lionfish_comshop_deliverylist_goods_data['rela_goodsoption_valueid'] = $goods_val['rela_goodsoption_valueid'];
				$lionfish_comshop_deliverylist_goods_data['sku_str'] = $goods_val['sku_name'];
				$lionfish_comshop_deliverylist_goods_data['goods_image'] = $goods_val['goods_images'];
				$lionfish_comshop_deliverylist_goods_data['goods_count'] = $goods_val['quantity'];
				$lionfish_comshop_deliverylist_goods_data['addtime'] = time();
				
				M('lionfish_comshop_deliverylist_goods')->add($lionfish_comshop_deliverylist_goods_data);
			}
			
			foreach($order_id_list as $order_id)
			{
				//ims_ lionfish_comshop_deliverylist_order
				$lionfish_comshop_deliverylist_order_data = array();
				$lionfish_comshop_deliverylist_order_data['list_id'] = $list_id;
				$lionfish_comshop_deliverylist_order_data['order_id'] = $order_id;
				$lionfish_comshop_deliverylist_order_data['addtime'] = time();
				
				M('lionfish_comshop_deliverylist_order')->add($lionfish_comshop_deliverylist_order_data);
				
				M('lionfish_comshop_order')->where( array('order_id' => $order_id ) )->save( array('is_delivery_flag' => 1) );
			
			}
			
			//line_id  clerk_id   lionfish_comshop_deliverylist_order
			
			//ims_  lionfish_comshop_order
			
			
		}
		
		show_json(1, array('msg' =>'生成清单成功','url' => $_SERVER['HTTP_REFERER'] ));
		
		
	}
	
	/**
		一键生成所有清单
	**/
	public function auto_get_delivery_list()
	{
		
		$this->display();
	}
	
	public function do_quenu_deliverylist()
	{
		@set_time_limit(1);
		$condition = " and is_delivery_flag = 0 and order_status_id =1 and delivery != 'express' ";
		
		$list = M()->query('SELECT head_id FROM ' . C('DB_PREFIX'). "lionfish_comshop_order  
			WHERE 1 " . $condition . ' group by head_id order by head_id desc limit 0,10');
			
		if( !empty($list) )
		{
			foreach( $list as $hd_vv )
			{
				$head_id = $hd_vv['head_id'];
				if( empty($head_id) )
				{
					continue;
				}
				
				$head_info = M('lionfish_community_head')->where( array('id' => $head_id ) )->find();
				
				
				$province = D('Home/Front')->get_area_info($head_info['province_id']); 
				$city = D('Home/Front')->get_area_info($head_info['city_id']); 
				$area = D('Home/Front')->get_area_info($head_info['area_id']); 
				$country = D('Home/Front')->get_area_info($head_info['country_id']); 
			
				$full_name = $province['name'].$city['name'].$area['name'].$country['name'].$head_info['address'];
			
			
				$order_condition = "  ";
				
				
				
				$goods_count_sql = "SELECT * FROM ".C('DB_PREFIX')."lionfish_comshop_order_goods where 1 and order_id in 
										(SELECT order_id from ".C('DB_PREFIX')."lionfish_comshop_order where is_delivery_flag = 0 and head_id={$head_id} {$order_condition} and delivery != 'express'   and order_status_id =1 )";
				
				$goods_list = M()->query($goods_count_sql);
				
				$show_goods_list = array();

				$goods_count =0;
				
				$order_id_list = array();
				
				foreach($goods_list as $val)
				{
					if( empty($order_id_list) || !in_array( $val['order_id'], $order_id_list ) )
					{
						$order_id_list[] = $val['order_id'];
					}
					
					if( empty($show_goods_list) || !in_array( $val['goods_id'].'_'.$val['rela_goodsoption_valueid'], array_keys($show_goods_list) ) )
					{
						$sku_name = '';
						$sku_arr = array();
						
						$order_option_info = M()->query("select value from ".C('DB_PREFIX')."lionfish_comshop_order_option  
								where order_id=".$val['order_id']." and order_goods_id=".$val['order_goods_id']);
								
								
					  
						foreach($order_option_info as $option)
						{
							$sku_arr[] = $option['value'];
						}
						
						if(empty($sku_arr))
						{
							$sku_name = '';
						}else{
							$sku_name = implode(',', $sku_arr);
						}
						$goods_count += $val['quantity'];
						$show_goods_list[ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ] = 
										array('goods_id' => $val['goods_id'], 'name' => $val['name'],'goods_images' => $val['goods_images'],'sku_name' =>$sku_name,'quantity' => $val['quantity'],'rela_goodsoption_valueid' => $val['rela_goodsoption_valueid'] );
					}else{
						$goods_count += $val['quantity'];
						$show_goods_list[ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ]['quantity'] += $val['quantity'];
					}
				}
				
				//ims_ 
				$line_relate_head = M('lionfish_comshop_deliveryline_headrelative')->field('line_id')->where( array('head_id' => $head_id) )->find();
				
				$line_id = 0;
				$line_name = '';
				$clerk_id = 0;
				
				if( !empty($line_relate_head) )
				{
					$line_id = $line_relate_head['line_id'];
					
					$line_info = M('lionfish_comshop_deliveryline')->field('name,clerk_id')->where( array('id' => $line_id ) )->find();
								
					$line_name = $line_info['name'];
					
					$clerk_id = $line_info['clerk_id'];
					//line_name
				}
				
				$clerk_name = '';
				$clerk_mobile = '';
				
				if( $clerk_id > 0 )
				{
					$clerk_info = M('lionfish_comshop_deliveryclerk')->where( array('id' => $clerk_id ) )->find();
					
					$clerk_name = $clerk_info['name'];
					$clerk_mobile = $clerk_info['mobile'];
				}
				
				
				$lionfish_comshop_deliverylist_data = array();
				$lionfish_comshop_deliverylist_data['list_sn'] = build_order_no($head_id);
				$lionfish_comshop_deliverylist_data['head_id'] = $head_id;
				$lionfish_comshop_deliverylist_data['head_name'] = $head_info['head_name'];
				$lionfish_comshop_deliverylist_data['head_mobile'] = $head_info['head_mobile'];
				$lionfish_comshop_deliverylist_data['head_address'] = $full_name;
				$lionfish_comshop_deliverylist_data['line_id'] = $line_id;
				$lionfish_comshop_deliverylist_data['line_name'] = $line_name;
				$lionfish_comshop_deliverylist_data['clerk_id'] = $clerk_id;
				$lionfish_comshop_deliverylist_data['clerk_name'] = $clerk_name;
				$lionfish_comshop_deliverylist_data['clerk_mobile'] = $clerk_mobile;
				$lionfish_comshop_deliverylist_data['state'] = 0;
				$lionfish_comshop_deliverylist_data['goods_count'] = $goods_count;
				$lionfish_comshop_deliverylist_data['express_time'] = 0;
				$lionfish_comshop_deliverylist_data['create_time'] = time();
				$lionfish_comshop_deliverylist_data['addtime'] = time();
				
				$list_id =  M('lionfish_comshop_deliverylist')->add($lionfish_comshop_deliverylist_data);
				
				foreach($show_goods_list as $goods_val)
				{
					//ims_ lionfish_comshop_deliverylist_goods
					$lionfish_comshop_deliverylist_goods_data = array();
					$lionfish_comshop_deliverylist_goods_data['list_id'] = $list_id;
					$lionfish_comshop_deliverylist_goods_data['goods_id'] = $goods_val['goods_id'];
					$lionfish_comshop_deliverylist_goods_data['goods_name'] = $goods_val['name'];
					$lionfish_comshop_deliverylist_goods_data['rela_goodsoption_valueid'] = $goods_val['rela_goodsoption_valueid'];
					$lionfish_comshop_deliverylist_goods_data['sku_str'] = $goods_val['sku_name'];
					$lionfish_comshop_deliverylist_goods_data['goods_image'] = $goods_val['goods_images'];
					$lionfish_comshop_deliverylist_goods_data['goods_count'] = $goods_val['quantity'];
					$lionfish_comshop_deliverylist_goods_data['addtime'] = time();
					
					M('lionfish_comshop_deliverylist_goods')->add($lionfish_comshop_deliverylist_goods_data);
				}
				
				foreach($order_id_list as $order_id)
				{
					//ims_ lionfish_comshop_deliverylist_order
					$lionfish_comshop_deliverylist_order_data = array();
					$lionfish_comshop_deliverylist_order_data['list_id'] = $list_id;
					$lionfish_comshop_deliverylist_order_data['order_id'] = $order_id;
					$lionfish_comshop_deliverylist_order_data['addtime'] = time();
					
					M('lionfish_comshop_deliverylist_order')->add($lionfish_comshop_deliverylist_order_data);
					
					M('lionfish_comshop_order')->where( array('order_id' => $order_id ) )->save( array('is_delivery_flag' => 1) );
				
				}
				
			}
			
			$condition = " and is_delivery_flag = 0 and order_status_id =1 and delivery != 'express' ";
		
			$total_arr = M()->query('SELECT order_id, head_id FROM ' .C('DB_PREFIX'). 'lionfish_comshop_order WHERE 1 ' . $condition.' group by head_id  order by head_id desc ' );
		  
			$total = count($total_arr);
			
			if( $total > 0 )
			{
				echo json_encode( array('code' => 0, 'msg' => '还剩'.$total.'个未生成') );
				die();
			}else{
				echo json_encode( array('code' => 2) );
				die();
			}
		
			//echo json_encode( array('code' => 2) );
			//die();
			
		}else{
			echo json_encode( array('code' => 2) );
			die();
		}
       
	}
	
	public function get_delivery_list()
	{
		$_GPC = I('request.');
		
		$searchtime = isset($_GPC['searchtime']) ? $_GPC['searchtime'] : '';
		$starttime = isset($_GPC['time']['start']) ? strtotime($_GPC['time']['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($_GPC['time']['end']) ? strtotime($_GPC['time']['end']) : strtotime(date('Y-m-d'.' 23:59:59'));
		
		$keyword = isset($_GPC['keyword']) ? $_GPC['keyword'] : '';
		
		$line_id = isset($_GPC['line_id']) ? intval($_GPC['line_id']) : 0;
		
		
		$this->searchtime = $searchtime; 
		$this->starttime = $starttime; 
		$this->endtime = $endtime; 
		$this->keyword = $keyword; 
		$this->line_id = $line_id; 
		
		$condition = " and is_delivery_flag = 0 and order_status_id =1 and delivery != 'express' ";
		
		$timewhere = "";
		
		if( !empty($searchtime) )
		{
			if( $searchtime == 'create' )
			{
				$condition .= " and date_added >={$starttime} and date_added<= {$endtime} ";
				
				$timewhere .= " and date_added >={$starttime} and date_added<= {$endtime} ";
			}else if( $searchtime == 'pay' ){
				$condition .= " and pay_time >={$starttime} and pay_time<= {$endtime} ";
				
				$timewhere .= " and pay_time >={$starttime} and pay_time<= {$endtime} ";
			}
		}
		
		if( !empty($keyword) )
		{
			$key_heads = M('lionfish_community_head')->field('id')->where( "community_name like '%".$keyword."%' " )->select();
			
			if( !empty($key_heads) )
			{
				$head_ids = array();
				foreach($key_heads as $vv)
				{
					$head_ids[] = $vv['id'];
				}
				$head_ids_str = implode(',', $head_ids);
				$condition .= " and head_id in({$head_ids_str}) ";
			}else{
				$condition .= " and 0 ";
			}
		}
		
		if( $line_id > 0 )
		{
			
			$relate_heads = M('lionfish_comshop_deliveryline_headrelative')->where( array('line_id' => $line_id) )->select();
			
			
			if( !empty($relate_heads) )
			{
				$head_ids = array();
				foreach($relate_heads as $vv)
				{
					$head_ids[] = $vv['head_id'];
				}
				$head_ids_str = implode(',', $head_ids);
				
				$condition .= " and head_id in({$head_ids_str}) ";
			}else{
				$condition .= " and 0 ";
			}		
		}
		
		
        $pindex    = max(1, intval($_GPC['page']));
        $psize     = 20;
		
		
		
		$list = M()->query('SELECT order_id, head_id FROM ' . C('DB_PREFIX'). "lionfish_comshop_order  
			WHERE 1 " . $condition . ' and head_id > 0 group by head_id order by head_id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);
       
	 
	   
	    $total_arr = M()->query('SELECT order_id, head_id FROM ' .C('DB_PREFIX'). 'lionfish_comshop_order WHERE 1 ' . $condition.' and head_id > 0 group by head_id  order by head_id desc ' );
      
		$total = count($total_arr);
		
		foreach($list as $key => $val)
		{
			//店铺名称 配送路线  商品总数 	操作
			
			$head_info = M('lionfish_community_head')->field('community_name')->where( array('id' =>$val['head_id'] ) )->find();

			$line_id_info = M('lionfish_comshop_deliveryline_headrelative')->field('line_id')->where( array('head_id' => $val['head_id']) )->find();
			
			$line_info  = array();
			
			if( !empty($line_id_info) )
			{
				//line_id 
				$line_info = M('lionfish_comshop_deliveryline')->where( array('id' => $line_id_info['line_id'] ) )->find();
			}
			
			$goods_count_sql = "SELECT sum(quantity) as total_quantity FROM ".C('DB_PREFIX')."lionfish_comshop_order_goods where 1 and is_refund_state = 0  and order_id in 
								(SELECT order_id from ".C('DB_PREFIX')."lionfish_comshop_order where is_delivery_flag = 0 {$timewhere} and head_id=".$val['head_id']." and delivery != 'express' and order_status_id =1 )";
			
			$goods_count_arr = M()->query($goods_count_sql);
			$goods_count = $goods_count_arr[0]['total_quantity'];
			
			$val['community_name'] = $head_info['community_name'];
			$val['line_name'] = $line_info['name'];
			$val['goods_count'] = $goods_count;
			
			$list[$key] = $val;
		}
		
        $pager = pagination2($total, $pindex, $psize);
		
		$line_list = M('lionfish_comshop_deliveryline')->select();
		
		$this->list = $list;
		$this->line_list = $line_list;
		$this->pager = $pager;
		$this->gpc = $_GPC;
		
		$this->display();
	}
	
	public function delivery_line()
	{
		
        $_GPC = I('request.');
		

        $pindex    = max(1, intval($_GPC['page']));
        $psize     = 20;

        if (!empty($_GPC['keyword'])) {
            $condition .= ' and (name like "%'.$_GPC['keyword'].'%"  )';
        }

        $list = M()->query('SELECT * FROM ' . C('DB_PREFIX') . "lionfish_comshop_deliveryline  
		WHERE 1 " . $condition . ' order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);
        
		foreach($list as $key => $val)
		{
			//clerk_id
			if( $val['clerk_id'] > 0)
			{
				$clerk_name = M('lionfish_comshop_deliveryclerk')->where( array('id' => $val['clerk_id'] ) )->find();
				
				$val['clerk_info'] = $clerk_name;
			}
			
			// lionfish_comshop_deliveryline_headrelative
			
			$head_relative = M()->query("select * from ".C('DB_PREFIX')."lionfish_comshop_deliveryline_headrelative  
								where 1 and line_id=".$val['id']." order by id asc ");
								
								
			
			$val['line_to_str'] = '';
			
			if( !empty($head_relative) )
			{
				$head_id_arr = array();
				foreach($head_relative as $vv)
				{
					$head_id_arr[] = $vv['head_id'];
				}
				$head_list = M()->query("select community_name from ".C('DB_PREFIX')."lionfish_community_head  
							where 1 and id in (".implode(',', $head_id_arr ).")" );
				
				
				$line_to_arr = array();
				
				foreach($head_list as $hd_val)
				{
					$line_to_arr[] = $hd_val['community_name'];
				}
				$val['line_to_str'] = implode('->', $line_to_arr );
			}
			//line_to_str
			
			$list[$key] = $val;
		}
		
	    $total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX'). 'lionfish_comshop_deliveryline  
								WHERE 1 ' . $condition);
		
		$total = $total_arr[0]['count'];

        $pager = pagination2($total, $pindex, $psize);
      
		$this->gpc = $_GPC;
		$this->list = $list;
		$this->pager = $pager;
		$this->display();
	}
	
	public function delivery_allprint_order()
	{
		
		$condition = " state=0 ";
		
		$gpc = I('request.');
		
		$searchtime = isset($gpc['searchtime']) ? $gpc['searchtime'] : '';
		$starttime = isset($gpc['start']) ? strtotime($gpc['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($gpc['end']) ? strtotime($gpc['end']) : strtotime(date('Y-m-d'.' 23:59:59'));


		if( !empty($searchtime) )
		{
			if(  $searchtime == 'create_time')
			{
				$condition .= " and create_time > {$starttime} and create_time < {$endtime} ";
			}
			if( $searchtime == 'express_time')
			{
				$condition .= " and express_time > {$starttime} and express_time < {$endtime} ";
			}
			if( $searchtime == 'head_get_time')
			{
				$condition .= " and head_get_time > {$starttime} and head_get_time < {$endtime} ";
			}
		}

		$count = M('lionfish_comshop_deliverylist')->where( $condition )->count();
		
		$this->searchtime = $gpc['searchtime'];
		$this->starttime = $gpc['start'];
		$this->endtime = $gpc['end'];
		$this->count = $count;
		
		$this->display();
	}
	
	public function delivery_allprint_order_do()
	{
		@set_time_limit(0);
		
		$page = I('get.page',1);
		
		$offset = ($page - 1) * 10;
		
		$condition = " state=0 ";
		
		$gpc = I('request.');
		
		$searchtime = isset($gpc['searchtime']) ? $gpc['searchtime'] : '';
		$starttime = isset($gpc['start']) ? strtotime($gpc['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($gpc['end']) ? strtotime($gpc['end']) : strtotime(date('Y-m-d'.' 23:59:59'));


		if( !empty($searchtime) )
		{
			if(  $searchtime == 'create_time')
			{
				$condition .= " and create_time > {$starttime} and create_time < {$endtime} ";
			}
			if( $searchtime == 'express_time')
			{
				$condition .= " and express_time > {$starttime} and express_time < {$endtime} ";
			}
			if( $searchtime == 'head_get_time')
			{
				$condition .= " and head_get_time > {$starttime} and head_get_time < {$endtime} ";
			}
		}

		
		$list = M('lionfish_comshop_deliverylist')->where( $condition )->order('clerk_id desc,head_id desc')->limit($offset, 10)->select();
		
		if( empty($list) )
		{
			echo json_encode( array('code' => 1 ) );
			die();
		}
		
		if( !empty($list) )
		{
			$need_data = array();
			
			$tuanz_info = array();
			$order_goods_list = array();
			$order_list_arr = array();
			foreach( $list as $delivery_info )
			{
				//if( empty($tuanz_info) )
				//{
					$tuanz_info = $delivery_info;
				//}
				
				$order_list = M('lionfish_comshop_deliverylist_order')->where( array('list_id' => $delivery_info['id']) )->order('id asc')->select();
				
				$need_order_list = array();
				
				if( !empty($order_list) )
				{
					foreach($order_list as $kkk => $vvv)
					{
						$order_id = $vvv['order_id'];
						$order_info = M('lionfish_comshop_order')->where( array('order_id' => $order_id) )->find();
						$order_goods = M('lionfish_comshop_order_goods')->where( array('order_id' => $order_id) )->order('order_goods_id asc')->select();
						
						//username shipping_name shipping_tel 
						
						$mb_info = M('lionfish_comshop_member')->field('username')->where( array('member_id' => $order_info['member_id'] ) )->find();
						$order_info['username'] = $mb_info['username'];
						
						$goods_list = array();
						
						foreach( $order_goods as $og_infos )
						{
							$sku_name = '';
							$sku_arr = array();
							if( !empty($og_infos['rela_goodsoption_valueid']) )
							{
								$order_option_info = M()->query("select value from ".C('DB_PREFIX')."lionfish_comshop_order_option  
									where order_id=".$og_infos['order_id']." and order_goods_id=".$og_infos['order_goods_id']);
							
								foreach($order_option_info as $option)
								{
									$sku_arr[] = $option['value'];
								}
								
								if(empty($sku_arr))
								{
									$sku_name = '';
								}else{
									$sku_name = implode(',', $sku_arr);
								}
							}
							$tmp = array();
							$tmp['name'] = $og_infos['name'];
							$tmp['quantity'] = $og_infos['quantity'];
							$tmp['sku_name'] = $sku_name;
							$tmp['price'] = $og_infos['price'];
							$tmp['total'] = sprintf('%.2f', $og_infos['price'] * $og_infos['quantity']);
							$goods_list[] = $tmp;
						}
						
						$order_info['order_goods'] = $goods_list;
						$need_order_list[] = $order_info;
					}
				}
				
				$last_index_sort = array_column($need_order_list,'username');
				array_multisort($last_index_sort,SORT_DESC,$need_order_list);
				
				
				$need_data[] = array('head_info' =>$tuanz_info, 'need_order_list' => $need_order_list);
				
				/**
				$goods_list = M('lionfish_comshop_deliverylist_goods')->where( array('list_id' => $delivery_info['id'] ) )->select();
				
				if( !empty($goods_list) )
				{
					foreach($goods_list as $val)
					{
						$tmp_gd = M('lionfish_comshop_goods')->field('index_sort')->where( array('id' => $val['goods_id']) )->find();
						$val['index_sort'] = $tmp_gd['index_sort'];
						
						$og_info= M('lionfish_comshop_order_goods')->where( array('goods_id' => $val['goods_id'],'rela_goodsoption_valueid' => $val['rela_goodsoption_valueid'] ) )->find();
						
						if( !empty($og_info) )
						{
							$val['price'] = $og_info['price'];
						}else{
							$val['price'] = 0;
						}
						$val['total'] = sprintf('%.2f',$val['price'] * $val['goods_count']);
						
						if( isset($order_goods_list[$val['goods_id'].'_'.$val['rela_goodsoption_valueid']]) )
						{
							$old_val = $order_goods_list[$val['goods_id'].'_'.$val['rela_goodsoption_valueid']];
							
							$old_val['total'] += $val['total'];
							$old_val['goods_count'] += $val['goods_count'];
							
							$order_goods_list[$val['goods_id'].'_'.$val['rela_goodsoption_valueid']] = $old_val;
						}else{
							$order_goods_list[$val['goods_id'].'_'.$val['rela_goodsoption_valueid']] = $val;
						}
						
						
						//goods_id
					}
				}
				//对数组进行排序
				$last_index_sort = array_column($order_goods_list,'index_sort');
				array_multisort($last_index_sort,SORT_DESC,$order_goods_list);
				
				**/
				
			}
		}
		
		$this->need_data = $need_data;			
		$this->shoname = D('Home/Front')->get_config_by_name('shoname');  
		
		$html = $this->fetch('Delivery/delivery_allprint_order_dofetch');
		
		echo json_encode( array('code' => 0, 'html' => $html) );
		die();
		
		//$this->display();
	}
	
	public function delivery_allprint()
	{
		
		$gpc = I('request.');
		$condition = " state=0 ";
		$searchtime = isset($gpc['searchtime']) ? $gpc['searchtime'] : '';
		$starttime = isset($gpc['start']) ? strtotime($gpc['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($gpc['end']) ? strtotime($gpc['end']) : strtotime(date('Y-m-d'.' 23:59:59'));
		
		if( !empty($searchtime) )
		{
			if(  $searchtime == 'create_time')
			{
				$condition .= " and create_time > {$starttime} and create_time < {$endtime} ";
			}
			if( $searchtime == 'express_time')
			{
				$condition .= " and express_time > {$starttime} and express_time < {$endtime} ";
			}
			if( $searchtime == 'head_get_time')
			{
				$condition .= " and head_get_time > {$starttime} and head_get_time < {$endtime} ";
			}
		}


		$count = M('lionfish_comshop_deliverylist')->where( $condition )->count();
		
		
		$this->searchtime = $gpc['searchtime'];
		$this->starttime = $gpc['start'];
		$this->endtime = $gpc['end'];
		
		$this->count = $count;
		
		$this->display();
		
	}
	public function delivery_allprint_do()
	{
		@set_time_limit(0);
		
		$page = I('get.page', 1);
		$offset = ($page - 1) * 10;
		
		$condition = " state=0 ";
		
		$gpc = I('request.');
		
		$searchtime = isset($gpc['searchtime']) ? $gpc['searchtime'] : '';
		$starttime = isset($gpc['start']) ? strtotime($gpc['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($gpc['end']) ? strtotime($gpc['end']) : strtotime(date('Y-m-d'.' 23:59:59'));


		if( !empty($searchtime) )
		{
			if(  $searchtime == 'create_time')
			{
				$condition .= " and create_time > {$starttime} and create_time < {$endtime} ";
			}
			if( $searchtime == 'express_time')
			{
				$condition .= " and express_time > {$starttime} and express_time < {$endtime} ";
			}
			if( $searchtime == 'head_get_time')
			{
				$condition .= " and head_get_time > {$starttime} and head_get_time < {$endtime} ";
			}
		}


		$list = M('lionfish_comshop_deliverylist')->where( $condition )->order('clerk_id desc,head_id desc')->limit($offset,10)->select();
		
		if( empty($list) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		
		if( !empty($list) )
		{
			$need_data = array();
			
			$tuanz_info = array();
			
			foreach( $list as $delivery_info )
			{
				$order_goods_list = array();
					//line_name 
					//$delivery_info['line_name'] = $line_info['name'];
					//$delivery_info['clerk_name'] = $deliveryclerk_info['name'];
					//$delivery_info['clerk_mobile'] = $deliveryclerk_info['mobile'];
					
					$tuanz_info = $delivery_info;
			
				//id
				$goods_list = M('lionfish_comshop_deliverylist_goods')->where( array('list_id' => $delivery_info['id'] ) )->select();
				
				if( !empty($goods_list) )
				{
					foreach($goods_list as $val)
					{
						$tmp_gd = M('lionfish_comshop_goods')->field('index_sort')->where( array('id' => $val['goods_id']) )->find();
						$val['index_sort'] = $tmp_gd['index_sort'];
						
						$og_info= M('lionfish_comshop_order_goods')->where( array('goods_id' => $val['goods_id'],'rela_goodsoption_valueid' => $val['rela_goodsoption_valueid'] ) )->find();
						
						if( !empty($og_info) )
						{
							$val['price'] = $og_info['price'];
						}else{
							$val['price'] = 0;
						}
						$val['total'] = sprintf('%.2f',$val['price'] * $val['goods_count']);
						
						if( isset($order_goods_list[$val['goods_id'].'_'.$val['rela_goodsoption_valueid']]) )
						{
							$old_val = $order_goods_list[$val['goods_id'].'_'.$val['rela_goodsoption_valueid']];
							
							$old_val['total'] += $val['total'];
							$old_val['goods_count'] += $val['goods_count'];
							
							$order_goods_list[$val['goods_id'].'_'.$val['rela_goodsoption_valueid']] = $old_val;
						}else{
							$order_goods_list[$val['goods_id'].'_'.$val['rela_goodsoption_valueid']] = $val;
						}
						
						
						//goods_id
					}
				}
				//对数组进行排序
				$last_index_sort = array_column($order_goods_list,'goods_name');
				array_multisort($last_index_sort,SORT_ASC,$order_goods_list);
				$need_data[] = array('head_info' =>$tuanz_info, 'order_goods_list' => $order_goods_list);
			}
			
		}
		
		$this->clerk_name = $clerk_name;
		$this->need_data = $need_data;	
		
		$this->shoname = D('Home/Front')->get_config_by_name('shoname');  
		

		$html = $this->fetch('Delivery/delivery_allprint_dofetch');
		
		echo json_encode( array('code' =>0, 'html' => $html ) );
		die();
	}
	
	public function clerk_allprint()
	{
		$gpc = I('request.');
		
		$searchtime = isset($gpc['searchtime']) ? $gpc['searchtime'] : '';
		$starttime = isset($gpc['start']) ? strtotime($gpc['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($gpc['end']) ? strtotime($gpc['end']) : strtotime(date('Y-m-d'.' 23:59:59'));
		
		
		$this->searchtime = $gpc['searchtime'];
		$this->starttime = $gpc['start'];
		$this->endtime = $gpc['end'];
		
		$count = M('lionfish_comshop_deliveryclerk')->count();
		$this->count = $count;
		$this->shoname = D('Home/Front')->get_config_by_name('shoname');  
		
		
		
		$this->display();
		
	}
	
	public function clerk_allprint_do()
	{
		@set_time_limit(0);
		
		$page = I('get.page',1);
		
		$offset = ($page - 1) ;
		
		$clerk_info_list = M('lionfish_comshop_deliveryclerk')->order('id asc ')->limit($offset,1)->select();
		
		if( empty($clerk_info_list ) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		
		$condition = " state=0 ";
		
		$gpc = I('request.');
		
		$searchtime = isset($gpc['searchtime']) ? $gpc['searchtime'] : '';
		$starttime = isset($gpc['start']) ? strtotime($gpc['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($gpc['end']) ? strtotime($gpc['end']) : strtotime(date('Y-m-d'.' 23:59:59'));


		if( !empty($searchtime) )
		{
			if(  $searchtime == 'create_time')
			{
				$condition .= " and create_time > {$starttime} and create_time < {$endtime} ";
			}
			if( $searchtime == 'express_time')
			{
				$condition .= " and express_time > {$starttime} and express_time < {$endtime} ";
			}
			if( $searchtime == 'head_get_time')
			{
				$condition .= " and head_get_time > {$starttime} and head_get_time < {$endtime} ";
			}
		}

		
		$need_data = array();
		
		//分两步，1第一步将改配送员-》线路-》多个社区下的配送单都给生成了。
		foreach($clerk_info_list as $val)
		{
			$id = $val['id'];
			
			$deliveryclerk_info = M('lionfish_comshop_deliveryclerk')->where( array('id' => $id) )->find();
			//查找对应线路
			$line_info = M('lionfish_comshop_deliveryline')->where( array('clerk_id' => $id ) )->find();
			if( empty($line_info) )
			{
				continue;
			}
			//根据线路找团长
			$head_lists = M('lionfish_comshop_deliveryline_headrelative')->where( array('line_id' => $line_info['id'] ) )->select();
			
			if( empty($head_lists) )
			{
				continue;
			}
			
			
			$order_goods_list = array();
			//开始获取配送单信息了
			foreach( $head_lists as  $head_val)
			{
				$head_id = $head_val['head_id'];
				//$this->do_su_delivery_list($head_id); state name condition
				
				$new_condition = $condition.' and head_id = '.$head_id ;
				
				$delivery_info_list = M('lionfish_comshop_deliverylist')->where( $new_condition )->select();
				
				if( !empty($delivery_info_list) )
				{
					$tuanz_info = array();
					
					foreach( $delivery_info_list as $delivery_info )
					{
						if( empty($tuanz_info) )
						{
							//line_name 
							$delivery_info['line_name'] = $line_info['name'];
							$delivery_info['clerk_name'] = $deliveryclerk_info['name'];
							$delivery_info['clerk_mobile'] = $deliveryclerk_info['mobile'];
							
							$tuanz_info = $delivery_info;
						}
						//id
						$goods_list = M('lionfish_comshop_deliverylist_goods')->where( array('list_id' => $delivery_info['id'] ) )->select();
						
						if( !empty($goods_list) )
						{
							foreach($goods_list as $val)
							{
								$tmp_gd = M('lionfish_comshop_goods')->field('index_sort')->where( array('id' => $val['goods_id']) )->find();
								$val['index_sort'] = $tmp_gd['index_sort'];
								
								$og_info= M('lionfish_comshop_order_goods')->where( array('goods_id' => $val['goods_id'],'rela_goodsoption_valueid' => $val['rela_goodsoption_valueid'] ) )->find();
								
								if( !empty($og_info) )
								{
									$val['price'] = $og_info['price'];
								}else{
									$val['price'] = 0;
								}
								$val['total'] = sprintf('%.2f',$val['price'] * $val['goods_count']);
								
								if( isset($order_goods_list[$val['goods_id'].'_'.$val['rela_goodsoption_valueid']]) )
								{
									$old_val = $order_goods_list[$val['goods_id'].'_'.$val['rela_goodsoption_valueid']];
									
									$old_val['total'] += $val['total'];
									$old_val['goods_count'] += $val['goods_count'];
									
									$order_goods_list[$val['goods_id'].'_'.$val['rela_goodsoption_valueid']] = $old_val;
								}else{
									$order_goods_list[$val['goods_id'].'_'.$val['rela_goodsoption_valueid']] = $val;
								}
								
								
								//goods_id
							}
						}
						
					}
					
					
				}
				
			}
			if( !empty($order_goods_list) )
			{
				//对数组进行排序
				$last_index_sort = array_column($order_goods_list,'index_sort');
				array_multisort($last_index_sort,SORT_DESC,$order_goods_list);
				$need_data[] = array('head_info' =>$tuanz_info, 'order_goods_list' => $order_goods_list);
			}
			
			/****/ 
		}
		
		
		$this->need_data = $need_data;
		$this->shoname = D('Home/Front')->get_config_by_name('shoname');  
		
		$html = $this->fetch('Delivery/delivery_allprint_fetch');
		
		echo json_encode( array('code' =>0, 'html' => $html ) );
		die();
		
		//$this->display();
	}
	
	public function adddelivery_clerk()
	{
        $_GPC = I('request.');
		
        $id = intval($_GPC['id']);
        if (!empty($id)) {
			$item = M('lionfish_comshop_deliveryclerk')->where( array('id' => $id) )->find();
        }

        if (IS_POST) {
            $data = $_GPC['data'];
            D('Seller/Delivery')->adddelivery_clerk($data);
			
			show_json(1, array('url' => U('Delivery/delivery_clerk') ));
        }
		
		
		$this->id = $id;
		$this->item = $item;
		
		$this->display();
	}
	
	
	public function queryclerk()
	{
		$_GPC = I('request.');
		
		
		$kwd = trim($_GPC['keyword']);
		$is_ajax = isset($_GPC['is_ajax']) ? intval($_GPC['is_ajax']) : 0;
		
		$condition = '  and line_id<= 0 ';

		if (!empty($kwd)) {
			$condition .= ' AND ( `name` LIKE "%'.$kwd.'%" or `mobile` LIKE "%'.$kwd.'%" )';
		}

		$ds = M()->query('SELECT * FROM ' .C('DB_PREFIX') . 'lionfish_comshop_deliveryclerk WHERE 1 ' . $condition . ' order by id asc');

		foreach ($ds as &$value) {
			$value['nickname'] = htmlspecialchars($value['name'], ENT_QUOTES);
			$value['avatar'] = tomedia($value['logo']);
			
			
			if($is_ajax == 1)
			{
				$ret_html .= '<tr>';
				$ret_html .= '	<td><img src="'.$value['avatar'].'" style="width:30px;height:30px;padding1px;border:1px solid #ccc" />'. $value['nickname'].'</td>';
				   
				$ret_html .= '	<td>'.$value['mobile'].'</td>';
				$ret_html .= '	<td style="width:80px;"><a href="javascript:;" class="choose_dan_link" data-json=\''.json_encode($value).'\'>选择</a></td>';
				$ret_html .= '</tr>';
			}
			
		}

		unset($value);

		if( $is_ajax == 1 )
		{
			echo json_encode( array('code' => 0, 'html' => $ret_html) );
			die();
		}
		
		$this->ds = $ds;
		
		$this->display();
		
	}
	
	public function adddeliverylist()
	{
		$_GPC = I('request.');
      
		
        $id = intval($_GPC['id']);
        if (!empty($id)) {
			
			$item = M('lionfish_comshop_deliveryline')->where( array('id' => $id) )->find();
			
			//clerk_id
			$saler = M('lionfish_comshop_deliveryclerk')->field('id,name as nickname, logo as avatar')->where( array('id' => $item['clerk_id']) )->find();
			
			$headlist = array();
			
			$head_relative = M('lionfish_comshop_deliveryline_headrelative')->where( array('line_id' => $item['id']) )->order('id asc')->select();				
				
			if( !empty($head_relative) )
			{
				$head_id_arr = array();
				foreach($head_relative as $vv)
				{
					$head_id_arr[] = $vv['head_id'];
				}
				
				$headlist = M('lionfish_community_head')->field('id,community_name')->where( array('id' => array('in', $head_id_arr )) )->select();
				
			}
			
			$this->item = $item;
			$this->saler = $saler;
			$this->headlist = $headlist;
        }

        if ( IS_POST ) {
            $data = $_GPC['data'];
			
            $clerk_id = $_GPC['clerk_id'];
            $head_id = $_GPC['head_id'];
			
			$data['clerk_id'] = $clerk_id;
			$data['head_id'] = $head_id;
			
			D('Seller/Delivery')->adddeliverylist($data);
            show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
        }
		
		$this->display();
	}
	
	
	
	
	public function deldelivery_clerk()
	{
		$_GPC = I('request.');
		
		$id = intval($_GPC['id']);

        if (empty($id)) {
            $id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
        }

		$items = M('lionfish_comshop_deliveryclerk')->where( "id in ({$id}) " )->select();		
						
		
        if (empty($item)) {
            $item = array();
        }

        foreach ($items as $item) {
			M('lionfish_comshop_deliveryclerk')->where( array('id' => $item['id']) )->delete();
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
	}
}
?>