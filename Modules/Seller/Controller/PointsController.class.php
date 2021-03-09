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

class PointsController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
	}
	
	public function goods()
	{
		$pindex = I('get.page', 1);
		$psize = 20;
		
		
		
		$starttime_arr = I('get.time');
		
		
		
		$starttime = isset($starttime_arr['start']) ? strtotime($starttime_arr['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		
		$endtime = isset($starttime_arr['end']) ? strtotime($starttime_arr['end']) : strtotime(date('Y-m-d'.' 23:59:59'));
		
		
		
		
		$this->starttime = $starttime;
		$this->endtime = $endtime;
		
		$searchtime = I('get.searchtime','');
		
		$this->searchtime = $searchtime;
		$shop_data = array();
		
		$type =  I('get.type','all');
		
		//---begin
				
		$count_common_where ="";
		if (defined('ROLE') && ROLE == 'agenter' ) {
			
			$supper_info = get_agent_logininfo();
			
			$supper_goods_list = M('lionfish_comshop_good_common')->field('goods_id')->where( array('supply_id' =>$supper_info['id'] ) )->select();
			
			$gids_list = array();
			
			foreach($supper_goods_list as $vv)
			{
				$gids_list[] = $vv['goods_id'];
			}
			
			if( !empty($gids_list) )
			{
				$count_common_where = " and  id in ( ".implode(',', $gids_list )." )";
			}else{
				$count_common_where = " and id in (0)";
			}
		}
		
		
		$all_count =  D('Seller/Goods')->get_goods_count(" and type = 'integral' {$count_common_where}");//全部商品数量
		
		$onsale_count = D('Seller/Goods')->get_goods_count(" and grounding = 1 and type = 'integral' {$count_common_where}");//出售中商品数量
		$getdown_count = D('Seller/Goods')->get_goods_count(" and grounding = 0 and type = 'integral' {$count_common_where}");//已下架商品数量
		$warehouse_count = D('Seller/Goods')->get_goods_count(" and grounding = 2 and type = 'integral' {$count_common_where}");//仓库商品数量
		$recycle_count = D('Seller/Goods')->get_goods_count(" and grounding = 3 and type = 'integral' {$count_common_where}");//回收站商品数量
		$waishen_count = D('Seller/Goods')->get_goods_count(" and grounding = 4 and type = 'integral' {$count_common_where}");//审核商品数量
		$unsuccshen_count = D('Seller/Goods')->get_goods_count(" and grounding = 5 and type = 'integral' {$count_common_where}");//拒绝审核商品数量
		
		$this->assign('waishen_count',$waishen_count);
		$this->assign('unsuccshen_count',$unsuccshen_count);
		
		//recycle 仓库
		
		//--end
		//recycle 仓库 get_config_by_name($name)
		
		$goods_stock_notice = D('Home/Front')->get_config_by_name('goods_stock_notice');
		$goods_stock_notice = intval($goods_stock_notice);
		if( empty($goods_stock_notice) )
		{
			$goods_stock_notice = 0;
		}
		
			
		$stock_notice_count = D('Admin/Goods')->get_goods_count(" and grounding = 1 and total<= {$goods_stock_notice} and type = 'normal' {$count_common_where}  ");//回收站商品数量
		//goods_stock_notice
		
		
		//grounding 1 
		
		//type all  全部
		
		//saleon 1 出售中
		//getdown 0 已下架
		//warehouse 2 仓库中
		//recycle 3 回收站
		
		
		$psize = 20;
		
		$condition = ' WHERE  g.type = "integral" ';
		
		$sqlcondition = "";
		
		if( !empty($type) && $type != 'all')
		{
			switch($type)
			{
				case 'saleon':
					$condition .= " and g.grounding = 1";
				break;
				case 'getdown':
					$condition .= " and g.grounding = 0";
				break;
				case 'warehouse':
					$condition .= " and g.grounding = 2";
				break;	
				case 'wait_shen':
					$condition .= " and g.grounding = 4";
				break;
				case 'refuse':
					$condition .= " and g.grounding = 5";
				break;
				case 'recycle':
					$condition .= " and g.grounding = 3";
				break;	
				case 'stock_notice':
					$condition .= " and g.grounding = 1 and g.total<= {$goods_stock_notice} ";
					break;
			}
			
		}else{
			$condition .= " and g.grounding != 3 ";
		}
		
		$keyword = I('get.keyword','');
		
		$this->keyword = $keyword;
		
		if (!(empty($keyword))) {
			$condition .= " AND (g.`id` = '{$keyword}' or g.`goodsname` LIKE '%{$keyword}%' or g.`codes` LIKE '%{$keyword}%' ) ";
		}
		
		if (defined('ROLE') && ROLE == 'agenter' ) 
		{
			
			$supper_info = get_agent_logininfo();
			
			$sqlcondition .= ' , ' . C('DB_PREFIX'). 'lionfish_comshop_good_common as gm  ';
			$condition .= ' and gm.goods_id =g.id  AND gm.supply_id ='.$supper_info['id'].'  ';
			
		}
		
		
		
		if( !empty($searchtime) )
		{
		    switch( $searchtime )
		    {
		        case 'create':
		            $condition .= ' AND (gm.begin_time >='.$starttime.' and gm.end_time < '.$endtime.' )';
					
					if (!defined('ROLE') && ROLE != 'agenter' )
					{
						$sqlcondition .= ' left join ' . C('DB_PREFIX'). 'lionfish_comshop_good_common as gm on gm.goods_id = g.id ';
					}
					
		            break;
		    }
		}
		
		
		$cate = I('get.cate', '');
		$this->cate = $cate;
		if( !empty($cate) )
		{
			$cate_list = M('lionfish_comshop_goods_to_category')->field('goods_id')->where(array('cate_id' => $cate))->select();
			
			$catids_arr = array();
			
			foreach($cate_list as $val)
			{
				$catids_arr[] = $val['goods_id'];
			}
			
			if( !empty($catids_arr) )
			{
				$catids_str = implode(',', $catids_arr);
				$condition .= ' and g.id in ('.$catids_str.')';
			}else{
				$condition .= " and 1=0 ";
			}
		}
		
		
		$sql = 'SELECT COUNT(g.id) as count FROM ' .C('DB_PREFIX'). 'lionfish_comshop_goods g ' .$sqlcondition.  $condition ;
		
		
		$total_arr = M()->query($sql);
		
		$total = $total_arr[0]['count'];
		
		
		
		
		$pager = pagination2($total, $pindex, $psize);
		
		if (!(empty($total))) {
			
			$sql = 'SELECT g.* FROM ' .C('DB_PREFIX'). 'lionfish_comshop_goods g '  .$sqlcondition . $condition . ' 
					ORDER BY  g.istop DESC, g.settoptime DESC, g.`id` DESC  ';
			
			$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
			
			
			$list = M()->query($sql);
			
			
			foreach ($list as $key => &$value ) {
				
				$price_arr = D('Home/Pingoods')->get_goods_price($value['id']);
				
				$value['price_arr'] = $price_arr;
					
				$thumb = M('lionfish_comshop_goods_images')->where( array('goods_id' => $value['id']) )->order('id asc')->find();
				
				
				if( empty($thumb['thumb']) )
				{
				    $value['thumb'] =  $thumb['image'];
				}else{
				    $value['thumb'] =  $thumb['thumb'];
				}
				
				//is_take_fullreduction
				$gd_common = M('lionfish_comshop_good_common')->field('is_take_fullreduction,supply_id')->where( array('goods_id' => $value['id']) )->find();
				
				$value['is_take_fullreduction'] =  $gd_common['is_take_fullreduction'];
				
				$value['supply_name'] = '';
				
				if( empty($gd_common['supply_id']) || $gd_common['supply_id'] ==0 )
				{
					$value['supply_id'] = 0;
				}else{
					$value['supply_id'] = $gd_common['supply_id'];
							
					$sub_info = M('lionfish_comshop_supply')->field('name')->where( array('id' => $gd_common['supply_id'] ) )->find();
					
					$value['supply_name'] = $sub_info['name'];
					
				}
				
				
				$categorys = M('lionfish_comshop_goods_to_category')->where( array('goods_id' => $value['id']) )->order('id asc')->select();
				
				$value['cate'] = $categorys;
				
			 	$time_info = D('home/front')->get_goods_common_field($value['id'] , 'begin_time,end_time');
			 	$value['begin_time'] = $time_info['begin_time'];
			 	$value['end_time'] = $time_info['end_time'];
				
				
				//团长数量
				$head_count = 0;
				
				if( $value['is_all_sale'] == 1 )
				{			
					$head_count = M('lionfish_community_head')->count();
					
				}else{
					
					$head_count = M('lionfish_community_head_goods')->where( array('goods_id' => $value['id'] ) )->count();
						
				}
				
				$value['head_count'] = $head_count;
				
				
			}
			
		}
		
		$categorys = D('Seller/GoodsCategory')->getFullCategory(true,false,'pintuan');
		$category = array();

		foreach ($categorys as $cate ) {
			$category[$cate['id']] = $cate;
		}
		
		$this->category =$category;
		
		$this->type = $type;
		$this->all_count = $all_count;
		$this->onsale_count = $onsale_count;
		$this->getdown_count = $getdown_count;
		$this->warehouse_count = $warehouse_count;
		$this->recycle_count = $recycle_count;
		$this->stock_notice_count = $stock_notice_count;
		
		
		$this->assign('list',$list);// 赋值数据集
		$this->assign('pager',$pager);// 赋值分页输出	
		$is_open_fullreduction = 0;
		$this->assign('is_open_fullreduction',$is_open_fullreduction);
		
		
		$index_sort_method = D('Home/Front')->get_config_by_name('index_sort_method'); 
		
		if( empty($index_sort_method) || $index_sort_method == 0 )
		{
			$index_sort_method = 0;
		}
		$this->index_sort_method = $index_sort_method;
		
		//---
		$supply_add_goods_shenhe = D('Home/Front')->get_config_by_name('supply_add_goods_shenhe');
		$supply_edit_goods_shenhe = D('Home/Front')->get_config_by_name('supply_edit_goods_shenhe');
		
		
			$supply_add_goods_shenhe = 0;
		
			$supply_edit_goods_shenhe = 0;
		
		
		$is_open_shenhe = 0;
		
		
		
		$this->supply_add_goods_shenhe = $supply_add_goods_shenhe;
		$this->supply_edit_goods_shenhe = $supply_edit_goods_shenhe;
		
		$this->assign('is_open_shenhe',$is_open_shenhe);
		//--
		
		//团长分组
		$group_default_list = array(
			array('id' => 'default', 'groupname' => '默认分组')
		);
		
		$this->group_list = array();
		
		
		$config_data = D('Seller/Config')->get_all_config();
		
		$pintuan_model_buy = 0;
		
		
		$this->pintuan_model_buy = $pintuan_model_buy;
		
		//团长分组
		$group_default_list = array(
			array('id' => 'default', 'groupname' => '默认分组')
		);
		
		$this->group_list = $group_list;
		
		$is_index = false;	
		$is_top = false;
		$is_updown  = false;
		$is_fullreduce  = false;
		$is_vir_count = false;
		$is_newbuy = false;
		$is_goodsspike = false;
		
		$this->config_data = $config_data;
						
		$this->is_index = $is_index;	
		$this->is_top = $is_top;
		$this->is_updown  = $is_updown;
		$this->is_fullreduce  = $is_fullreduce;
		$this->is_vir_count = $is_vir_count;
		$this->is_newbuy = $is_newbuy;
		$this->is_goodsspike = $is_goodsspike;
		
		$this->display();
	
	}
	
	
	public function editgoods()
	{
		$id =  I('get.id');
		
		if (IS_POST) {
			$_GPC = I('post.');
			
			if( !isset($_GPC['thumbs']) || empty($_GPC['thumbs']) )
			{
				show_json(0,  array('message' => '商品图片必须上传' ,'url' => $_SERVER['HTTP_REFERER']) );
				die();
			}
			
			D('Seller/Goods')->modify_goods('integral');
			
			$http_refer = S('HTTP_REFERER');
			
			$http_refer = empty($http_refer) ? $_SERVER['HTTP_REFERER'] : $http_refer;
			
			show_json(1, array('message'=>'修改商品成功！','url' => $http_refer ));
		}
		//sss
		S('HTTP_REFERER', $_SERVER['HTTP_REFERER']);
		$this->id = $id;
		$item = D('Seller/Goods')->get_edit_goods_info($id,0);
		
		
		//-------------------------以上是获取资料
		
		$limit_goods = array();
			
		
		
		$this->limit_goods = $limit_goods;
		
		$category = D('Seller/GoodsCategory')->getFullCategory(true, true);
		
		$this->category = $category;
		
		
		$spec_list = D('Seller/Spec')->get_all_spec();
		
		$this->spec_list = $spec_list;
			
		$dispatch_data = M('lionfish_comshop_shipping')->where( array('enabled' => 1, 'isdefault' => 1) )->order('sort_order desc')->select();		

		$this->dispatch_data = $dispatch_data;
		
		$set = D('Seller/Config')->get_all_config();
		
		$this->set = $set;
		
		$commission_level = array();
		
		$config_data = $set;
		
		$this->config_data = $config_data;
		
		$default = array('id' => 'default', 'levelname' => empty($config_data['commission_levelname']) ? '默认等级' : $config_data['commission_levelname'], 'commission1' => $config_data['commission1'], 'commission2' => $config_data['commission2'], 'commission3' => $config_data['commission3']);
		//$others = pdo_fetchall('SELECT * FROM ' . tablename('lionfish_comshop_commission_level') . ' WHERE uniacid = \'' . $_W['uniacid'] . '\' ORDER BY commission1 asc');
		
		
		//$commission_level = array_merge(array($default), $others);
		
		$commission_level = array();
		
		//$level['key']
		foreach($commission_level as $key => $val)
		{
			$val['key'] = $val['id'];
			$commission_level[$key] = $val;
		}
		$shopset_level = empty($set['commiss_level']) ? 0: $set['commiss_level'];
		$this->shopset_level = $shopset_level;
		
		$open_buy_send_score = empty($set['open_buy_send_score']) ? 0: $set['open_buy_send_score'];
		
		$this->open_buy_send_score = $open_buy_send_score;
		
		$delivery_type_express = $config_data['delivery_type_express'];
		
		if( empty($delivery_type_express) )
		{
			$delivery_type_express = 2;
		}
		
		$this->delivery_type_express = $delivery_type_express;
		
		$is_open_fullreduction =  $config_data['is_open_fullreduction'];
		
		$this->is_open_fullreduction = $is_open_fullreduction;
		
								
		$community_head_level = M('lionfish_comshop_community_head_level')->order('id asc')->select();						
		
		
		$head_commission_levelname = $config_data['head_commission_levelname'];
		$default_comunity_money =  $config_data['default_comunity_money'];
		
		$list_default = array(
			array('id' => '0','level'=>0,'levelname' => empty($head_commission_levelname) ? '默认等级' : $head_commission_levelname, 'commission' => $default_comunity_money, )
		);
		
		$community_head_level = array_merge($list_default, $community_head_level);
		
		
		$community_head_commission_info = D('Seller/Communityhead')->get_goods_head_level_bili( $id );
		
		
		$mb_level = M('lionfish_comshop_member_level')->count();
		
		$this->mb_level = $mb_level;
		
		
		if( !empty($community_head_commission_info) )
		{
			foreach( $community_head_commission_info as $kk => $vv)
			{
				$item[$kk] = $vv;
			}
		}
	
		$this->community_head_commission_info = $community_head_commission_info;
		$this->item = $item;
		$this->community_head_level = $community_head_level;
		//end 
		
		$community_money_type =  $config_data['community_money_type'];
		
		$this->community_money_type = $community_money_type;
		
		$index_sort_method = D('Home/Front')->get_config_by_name('index_sort_method'); 
		
		if( empty($index_sort_method) || $index_sort_method == 0 )
		{
			$index_sort_method = 0;
		}
		$this->index_sort_method = $index_sort_method;
		
		
		$is_open_only_express =  $config_data['is_open_only_express'];
		
		$this->is_open_only_express = $is_open_only_express;
		
		$is_open_goods_relative_goods = 0;
		
		$this->is_open_goods_relative_goods = $is_open_goods_relative_goods;
		
		//供应商权限begin
		
		$is_index = true;	
		$is_top = true;
		$is_updown  = true;
		$is_fullreduce  = true;
		$is_vir_count = true;
		$is_newbuy = true;
		$is_goodsspike = true;

		//供应商权限end
		$this->is_index = $is_index;	
		$this->is_top = $is_top;
		$this->is_updown  = $is_updown;
		$this->is_fullreduce  = $is_fullreduce;
		$this->is_vir_count = $is_vir_count;
		$this->is_newbuy = $is_newbuy;
		$this->is_goodsspike = $is_goodsspike;
		
		$pintuan_model_buy = 0;
		//供应商权限begin community_head_level
		$this->pintuan_model_buy  = $pintuan_model_buy;
		
		$this->display('Points/addgoods');
	}
	
	
	
	
	public function addgoods()
	{
		if (IS_POST) {
			$_GPC = I('request.');
			
			if( !isset($_GPC['thumbs']) || empty($_GPC['thumbs']) )
			{
				show_json(0, array('message' => '商品图片必须上传' ,'url' => $_SERVER['HTTP_REFERER']) );
				die();
			}
			
			D('Seller/Goods')->addgoods('integral');
			
			$http_refer = S('HTTP_REFERER');
			
			$http_refer = empty($http_refer) ? $_SERVER['HTTP_REFERER'] : $http_refer;
			
			show_json(1, array('message' => '添加商品成功！','url' => $http_refer ));
		}
		S('HTTP_REFERER', $_SERVER['HTTP_REFERER']);
		
		$this->category = array();
		
		$spec_list = D('Seller/Spec')->get_all_spec();
		$this->spec_list = $spec_list;
		
		$dispatch_data = M('lionfish_comshop_shipping')->where( array('enabled' => 1,'isdefault' =>1) )->order('sort_order desc')->select();
		$this->dispatch_data = $dispatch_data;
			
		$set =  D('Seller/Config')->get_all_config();
		
		$commission_level = array();
		
		$config_data = $set;
		$this->config_data = $config_data;
		
		$default = array('id' => 'default', 'levelname' => empty($config_data['commission_levelname']) ? '默认等级' : $config_data['commission_levelname'], 'commission1' => $config_data['commission1'], 'commission2' => $config_data['commission2'], 'commission3' => $config_data['commission3']);
				
		$others = M('lionfish_comshop_commission_level')->order('commission1 asc')->select();		
		
		
		$commission_level = array_merge(array($default), $others);
		
		$communityhead_commission = $config_data['default_comunity_money'];
		$this->communityhead_commission = $communityhead_commission;
		
		
		
		
		//$level['key']
		foreach($commission_level as $key => $val)
		{
			$val['key'] = $val['id'];
			$commission_level[$key] = $val;
		}
		$this->commission_level = $commission_level;
		$shopset_level = empty($set['commiss_level']) ? 0: $set['commiss_level'];
		$this->shopset_level = $shopset_level;
		
		
		$open_buy_send_score = empty($set['open_buy_send_score']) ? 0: $set['open_buy_send_score'];
		$this->open_buy_send_score = $open_buy_send_score;
		
		$item = array();
		$item['begin_time'] = time();
		$item['community_head_commission'] = $communityhead_commission;
		
		$item['end_time'] = time() + 86400;
		
		
		
		
		$delivery_type_express =  $config_data['delivery_type_express'];
		
		if( empty($delivery_type_express) )
		{
			$delivery_type_express = 2;
		}
		
		$this->delivery_type_express = $delivery_type_express;
		
		$is_open_fullreduction = $config_data['is_open_fullreduction'];
		
		$this->is_open_fullreduction = $is_open_fullreduction;
		
		
		$community_head_level = M('lionfish_comshop_community_head_level')->order('id asc')->select();
		
		$head_commission_levelname = $config_data['head_commission_levelname'];
		$default_comunity_money = $config_data['default_comunity_money'];
		
		$list_default = array(
			array('id' => '0','level'=>0,'levelname' => empty($head_commission_levelname) ? '默认等级' : $head_commission_levelname, 'commission' => $default_comunity_money, )
		);
		
		$community_head_level = array_merge($list_default, $community_head_level);
		
		if( !empty($community_head_level) )
		{
			foreach( $community_head_level as $head_level)
			{
				$item['head_level'.$head_level['id']] = $head_level['commission'];
			}
		}
		
		$this->item = $item;
		$this->community_head_level = $community_head_level;
		
		$community_money_type =  $config_data['community_money_type'];
		
		$this->community_money_type = $community_money_type;
		
		
		$mb_level = M('lionfish_comshop_member_level')->count();
		
		$this->mb_level = $mb_level;
		
		$is_open_only_express = $config_data['is_open_only_express'];
		
		$this->is_open_only_express = $is_open_only_express;
		
		$is_open_goods_relative_goods = $config_data['is_open_goods_relative_goods'];
		
		$this->is_open_goods_relative_goods = $is_open_goods_relative_goods;
		
		//供应商权限begin
		
		$is_index = true;	
		$is_top = true;
		$is_updown  = true;
		$is_fullreduce  = true;
		$is_vir_count = true;
		$is_newbuy = true;
		$is_goodsspike = true;

		
		//供应商权限end
		$this->is_index = $is_index;	
		$this->is_top = $is_top;
		$this->is_updown  = $is_updown;
		$this->is_fullreduce  = $is_fullreduce;
		$this->is_vir_count = $is_vir_count;
		$this->is_newbuy = $is_newbuy;
		$this->is_goodsspike = $is_goodsspike;
		
		$pintuan_model_buy = isset($config_data['pintuan_model_buy']) ? intval( $config_data['pintuan_model_buy'] ) : 0;
		
		$this->pintuan_model_buy = $pintuan_model_buy;
		
		$this->display();
		
	}
	
	
	public function order()
	{
		$_GPC = I("request.");
		
		$starttime = isset($_GPC['time']['start']) ? strtotime($_GPC['time']['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($_GPC['time']['end']) ? strtotime($_GPC['time']['end']) : strtotime(date('Y-m-d'.' 23:59:59'));
		
		$order_status_arr = D('Seller/Order')->get_order_status_name();
		
		
		$need_data = D('Seller/Order')->load_order_list(0,0,0,1);
		
		$cur_controller = 'points/order';
		
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
		
		
		$open_feier_print = D('Home/Front')->get_config_by_name('open_feier_print');
		
		if( empty($open_feier_print) )
		{
			$open_feier_print = 0;
		}
		
		$is_can_look_headinfo = true;
		$is_can_nowrfund_order = true;
		
		$this->starttime = $starttime;
		
		$this->endtime = $endtime;
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
		
		$supply_can_look_headinfo = D('Home/Front')->get_config_by_name('supply_can_look_headinfo');
		$supply_can_nowrfund_order = D('Home/Front')->get_config_by_name('supply_can_nowrfund_order');
		
		$this->open_feier_print = $open_feier_print;
		
		$this->supply_can_look_headinfo = $supply_can_look_headinfo;
		$this->supply_can_nowrfund_order = $supply_can_nowrfund_order;
		
		$this->_GPC = $_GPC;
		
		$this->cur_controller = $cur_controller;
		
		$this->display();
	}
		
}
?>