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


class DistributionController extends CommonController {
	   
	protected function _initialize(){
		parent::_initialize();
	}
	
	public function distributionpostal()
	{
		
		if (IS_POST) {
			
			$data = I('request.data');
			
			D('Seller/Config')->update($data);
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		
		$data = D('Seller/Config')->get_all_config();
		
		$this->data = $data;
		
		$this->display();
	}
	
	
	public function changename()
	{
		$_GPC = I('request.');
		
		$id = intval($_GPC['id']);
	
		//ids
		if (empty($id)) {
			$id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
		}

		if (empty($id)) {
			show_json(0, array('message' => '参数错误'));
		}
		
		$type = trim($_GPC['type']);
		$value = trim($_GPC['value']);

		
		$items = M('lionfish_community_head_tixian_order')->field('id')->where( 'id in( ' . $id . ' )' )->select();			
				
		foreach ($items as $item ) {
			
			M('lionfish_community_head_tixian_order')->where(  array('id' => $item['id']) )->save( array('bankusername' => $value) );
		}
		
		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	
	
	public function clear_user_member_qrcode()
	{
		M('lionfish_comshop_member')->where( "member_id > 0" )->save( array('wepro_qrcode' => '') );
		
		echo json_encode( array('code' => 0) );
		die();
	}
	
	public function config()
	{
		if (IS_POST) {
			
			$data = I('request.data');
			
			D('Seller/Config')->update($data);
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		
		$data = D('Seller/Config')->get_all_config();
		
		$this->data = $data;
		
		$this->display();
	}
	
	public function qrcodeconfig()
	{
	
		if (IS_POST) {
			$_GPC = I('request.');
			
			$data = array();
			$data['distribution_avatar_left'] = $_GPC['avatar_left'];
			$data['distribution_avatar_top'] = $_GPC['avatar_top'];
			$data['distribution_qrcodes_left'] = $_GPC['qrcodes_left'];
			$data['distribution_qrcodes_top'] = $_GPC['qrcodes_top'];
			$data['distribution_username_left'] = $_GPC['username_left'];
			$data['distribution_username_top'] = $_GPC['username_top'];
			$data['distribution_img_src'] = $_GPC['img_src'];
			$data['commiss_avatar_rgb'] = $_GPC['commiss_avatar_rgb'];
			$data['commiss_nickname_rgb'] = $_GPC['commiss_nickname_rgb'];
			
			D('Seller/Config')->update($data);
			
			M('lionfish_comshop_member')->where( "1" )->save( array('commiss_qrcode' => '') );
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		
		$data = D('Seller/Config')->get_all_config();
		
		$this->data = $data;
		
		$this->display();
	}
	
	public function addForm()
	{
		
		 if (IS_POST) {
			
			$data = I('request.data');
			
			
			D('Seller/Config')->update(array('commiss_diy_form' => serialize( $data ) ));
			
			show_json(0, array('url' => $_SERVER['HTTP_REFERER']));			
		 }
		 
		 $data = M('lionfish_comshop_config')->where( array('name' => 'commiss_diy_form') )->find();
		
		 $form_data = array();
		 
		 if( !empty($data) )
		 {
			 $form_data = unserialize( htmlspecialchars_decode( $data['value'] ));
		 }
		
		
		$this->form_data = $form_data;
		
		include $this->display();
	}
	
	public function withdraw_config()
	{
	   
	    if (IS_POST) {
	        	
	        $data =  I('request.data');
	        
			$data['commiss_tixianway_yuer'] = isset($data['commiss_tixianway_yuer']) ? $data['commiss_tixianway_yuer']:1;
			$data['commiss_tixianway_weixin'] = isset($data['commiss_tixianway_weixin']) ? $data['commiss_tixianway_weixin']:1;
			$data['commiss_tixianway_alipay'] = isset($data['commiss_tixianway_alipay']) ? $data['commiss_tixianway_alipay']:1;
			$data['commiss_tixianway_bank'] = isset($data['commiss_tixianway_bank']) ? $data['commiss_tixianway_bank']:1;
			$data['commiss_tixian_publish'] = isset($data['commiss_tixian_publish']) ? $data['commiss_tixian_publish']:'';
			
			
	        D('Seller/Config')->update($data);
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	    }
	    
	    $data = D('Seller/Config')->get_all_config();
		
		$this->data = $data;
	    $this->display();
	}
	
	public function distributionorder()
	{
		$time = I('request.time');
		$_GPC = I('request.');
		
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
		
		
		$starttime = isset($_GPC['time']['start']) ? strtotime($_GPC['time']['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($_GPC['time']['end']) ? strtotime($_GPC['time']['end']) : strtotime(date('Y-m-d'.' 23:59:59'));
		
		$order_status_arr = D('Seller/Order')->get_order_status_name();
		$this->order_status_arr = $order_status_arr;
		
		$_GPC['is_fenxiao'] = 1;//分销订单
		
		
		$this->_GPC = $_GPC;
		
		$this->is_fenxiao = 1;
		
		$cur_controller = 'distribution/distributionorder';
		
		$need_data = D('Seller/Order')->load_order_list(0,1);
		
		
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
		
		$this->order_status_id = $order_status_id;
		$this->is_community = I('request.is_community', 0);
		$this->headid = I('request.headid', 0);
		
		$open_feier_print = D('Home/Front')->get_config_by_name('open_feier_print');
		
		if( empty($open_feier_print) )
		{
			$open_feier_print = 0;
		}
		
		$this->open_feier_print = $open_feier_print;
		

		
		$this->display('Order/index');
	}
	
	public function communityorder()
	{
		$_GPC = I('request.');
		
		$member_id = $_GPC['member_id'];
		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		
		$where = " and co.member_id = {$member_id} ";
		
		$starttime = strtotime( date('Y-m-d')." 00:00:00" );
		$endtime = $starttime + 86400;
		
		
		
		if( isset($_GPC['searchtime']) && $_GPC['searchtime'] == 'create_time' )
		{
			if (!empty($_GPC['time']['start']) && !empty($_GPC['time']['end'])) {
				$starttime = strtotime($_GPC['time']['start']);
				$endtime = strtotime($_GPC['time']['end']);
				
				$where .= ' AND co.addtime >= '.$starttime.' AND co.addtime <= '.$endtime.' ';
			}
		}
		
		$this->member_id = $member_id;
		$this->starttime = $starttime;
		$this->endtime = $endtime;
		
		
		if ($_GPC['order_status'] != '') {
			$where .= ' and co.state=' . intval($_GPC['order_status']);
		}
		
		
		$sql = "select co.order_id,co.state,co.money,co.level,co.addtime ,og.total,og.name,og.total     
				from ".C('DB_PREFIX')."lionfish_comshop_member_commiss_order as co ,  
                ".C('DB_PREFIX')."lionfish_comshop_order_goods as og  
	                    where   co.order_goods_id = og.order_goods_id {$where}  
	                      order by co.id desc ".' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
		
		$list = M()->query($sql);

		if( !empty($list) )
		{
			foreach($list as $key => $val)
			{
				$val['total'] = sprintf("%.2f",$val['total']);
				$val['money'] = sprintf("%.2f",$val['money']);
				
				$val['addtime'] = date('Y-m-d H:i:s',$val['addtime']);
				
				$order_info = M('lionfish_comshop_order')->field('order_num_alias')->where( array('order_id' => $val['order_id'] ) )->find();
				
				$val['order_num_alias'] = $order_info['order_num_alias'];
				$list[$key] = $val;
			}
		}
		
		$sql_count = "select count(1) as count      
				from ".C('DB_PREFIX')."lionfish_comshop_member_commiss_order as co ,  
                ".C('DB_PREFIX')."lionfish_comshop_order_goods as og  
	                    where co.order_goods_id = og.order_goods_id {$where}  ";
		
		$total_arr = M()->query($sql_count);		
		
		$total = $total_arr[0]['count'];
	
	
		if ($_GPC['export'] == '1') {
			
			$export_sql = "select co.order_id,co.state,co.money,co.level,co.addtime ,og.total,og.name,og.total     
				from ".C('DB_PREFIX')."lionfish_comshop_member_commiss_order as co ,  
                ".C('DB_PREFIX')."lionfish_comshop_order_goods as og  
	                    where   co.order_goods_id = og.order_goods_id {$where}  
	                      order by co.id desc ";
		
			$export_list = M()->query($export_sql);
			
			if( !empty($export_list) )
			{
				foreach($export_list as $key => $val)
				{
					$val['total'] = sprintf("%.2f",$val['total']);
					$val['money'] = sprintf("%.2f",$val['money']);
					
					$val['addtime'] = date('Y-m-d H:i:s',$val['addtime']);
					
					$order_info= M('lionfish_comshop_order')->field('order_num_alias')->where( array('order_id' => $val['order_id'] ) )->find();
					
					$val['order_num_alias'] = $order_info['order_num_alias'];
					$export_list[$key] = $val;
				}
			}
			
			
			
			foreach($export_list as $key =>&$row)
			{
				$row['order_num_alias'] =  "\t".$row['order_num_alias'];
				$row['name'] = $row['name'];
				$row['total'] = $row['total'];
				$row['money'] = $row['money'];
				
				if($row['state'] == 0)
				{
					$row['state'] = '待结算';
				}else if($row[state] == 1)
				{
					$row['state'] = '已结算';
				}else if($row[state] == 2){
					$row['state'] = '订单取消或退款';
				}
				
				$row['addtime'] =  $row['addtime'];

			}
			
			unset($row);
			
			$columns = array(
				array('title' => '订单编号', 'field' => 'order_num_alias', 'width' => 24),
			    array('title' => '商品标题', 'field' => 'name', 'width' => 24),
				array('title' => '订单金额', 'field' => 'total', 'width' => 12),
				array('title' => '佣金金额', 'field' => 'money', 'width' => 12),
				array('title' => '几级分佣', 'field' => 'level', 'width' => 12),
				array('title' => '状态', 'field' => 'state', 'width' => 24),
				array('title' => '下单时间', 'field' => 'addtime', 'width' => 24),
			);
			
		
			D('Seller/Excel')->export($export_list, array('title' => '会员分销收益明细-' . date('Y-m-d-H-i', time()), 'columns' => $columns));
			
		}
		
		$pager = pagination2($total, $pindex, $psize);
		
		
		$this->pager = $pager;
		$this->list = $list;
		$this->_GPC = $_GPC;
		
		$this->display();
	}
	
	public function changecommission()
	{
		$_GPC = I('request.');
		
		$order_id = $_GPC['order_id'];
		$order_goods_id = $_GPC['order_goods_id'];
		
		$commiss_level = D('Seller/Front')->get_config_by_name('commiss_level');
		
		$commission_info = D('Home/Commission')->get_order_goods_commission( $order_id, $order_goods_id);
		
		if (empty($commission_info)) {
			if (IS_POST) {
				show_json(0, array('message' => '未找到订单!'));
			}
			exit('fail');
		}
		
		if (IS_POST) {
			$cm1 = $_GPC['cm1'];
			
			
			if (!is_array($cm1) ) {
				show_json(0, array('message' => '未找到修改数据!'));
			}
			
			foreach ($cm1 as $id => $money) {
				
				M('lionfish_comshop_member_commiss_order')->where( array('id' => $id) )->save( array('money' => $money) );
			}
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		
		foreach( $commission_info as $key => $val )
		{
			$mb_info = M('lionfish_comshop_member')->field('username,avatar')->where( array('member_id' => $val['member_id'] ) )->find();
			
			$val['member_info'] = $mb_info;
			$commission_info[$key] = $val;
		}
		
		$this->order_id = $order_id;
		$this->order_goods_id = $order_goods_id;
		$this->commiss_level = $commiss_level;
		$this->commission_info = $commission_info;
		
		$this->display();
	}
	
	public function level()
	{
		$data = D('Seller/Config')->get_all_config();
		
		$default = array('id' => 'default', 'levelname' => empty($data['commission_levelname']) ? '默认等级' : $data['commission_levelname'], 'commission1' => $data['commission1'], 'commission2' => $data['commission2'], 'commission3' => $data['commission3']);
		
		$others = M('lionfish_comshop_commission_level')->order('commission1 asc')->select();
		
		$list = array_merge(array($default), $others);
		
		$this->data = $data;
		$this->list = $list;
		
		$this->display();
		
	}
	
	
	
	public function addlevel()
	{
		$this->modifylevel();
	}

	public function editlevel()
	{
		$this->modifylevel();
	}
	
	public function become_agent_check()
	{
		$_GPC = I('request.');
		
		$member_id = $_GPC['id'];
		$state = $_GPC['state'];
		
		$member = M('lionfish_comshop_member')->field('member_id,openid,we_openid,comsiss_state')->where( array('member_id' => $member_id) )->find();
		
		if( $state == 1 )
		{
			$time = time();
			
			M('lionfish_comshop_member')->where( array('member_id' => $member['member_id']) )->save( array('comsiss_state' => 1,'comsiss_flag' => 1, 'comsiss_time' => $time) );
			
			//检测是否存在账户，没有就新建
			D('Home/Commission')->commission_account($member['member_id']);
			//TODO....sendmsg  发送成为分销商的信息
		}else{
			
			M('lionfish_comshop_member')->where( array('member_id' => $member_id) )->save( array('comsiss_state' => 0,'comsiss_flag' => 1, 'comsiss_time' => 0) );
		}
		
		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	
	public function agent_check()
	{
		$_GPC = I('request.');
		
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}

		$comsiss_state = intval($_GPC['comsiss_state']);
		
		$members =  M('lionfish_comshop_member')->field('member_id,openid,we_openid,comsiss_state')->where( 'member_id in( ' . $id . ' )' )->select();				
		
		$time = time();

		foreach ($members as $member) {
			if ($member['comsiss_state'] === $status) {
				continue;
			}

			if ($comsiss_state == 1) {
				
				M('lionfish_comshop_member')->where( array('member_id' => $member['member_id'] ) )->save( array('comsiss_state' => 1, 'comsiss_time' => $time) );
				
				//检测是否存在账户，没有就新建
				D('Home/Commission')->commission_account($member['member_id']);
				//TODO....sendmsg  发送成为分销商的信息
			}
			else {
				
				M('lionfish_comshop_member')->where( array('member_id' => $member['member_id']) )->save( array('comsiss_state' => 0, 'comsiss_time' => 0) );
			}
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	
	public function nextchild_list()
	{
		$_GPC = I('request.');
		
		$member_id = $_GPC['id'];
		
		
		$pindex = max(1, intval($_GPC['page']));
		
		$psize = 20;
		$size = 20;
		
		$offset  = ($pindex - 1) * $size;
		
		$keyword =  $_GPC['keyword'];
		
		$this->keyword = $keyword;
		$this->member_id = $member_id;
		$where = '';
		if( !empty($keyword) )
		{
			$where .= " and ( username like '%{$keyword}%' or telephone like '%{$keyword}%' ) ";
		}
		
		$level =  D('Home/Front')->get_config_by_name('commiss_level');
		
		$level_1_ids = array();
		$level_2_ids = array();
		$level_3_ids = array();
		
		$member_id_arr = array($member_id);
		
		if( $level == 1 )
		{
			$list = array();
			
			$sql = "select * from ".C('DB_PREFIX')."lionfish_comshop_member   
	                    where  1 {$where} and agentid in (".implode(',', $member_id_arr).")   
							order by member_id desc limit {$offset},{$size}";
	 
			$list =  M()->query($sql);
			
			
			foreach( $list as $vv )
			{
				$level_1_ids[$vv['id']] = $vv['id'];
			}
			
			$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX') . 
						'lionfish_comshop_member WHERE 1 ' . "{$where} and agentid in (".implode(',', $member_id_arr).") " );
		
			$total = $total_arr[0]['count'];
			
		}else if( $level == 2 )
		{
			$list = array();
			
			$sql = "select member_id from ".C('DB_PREFIX')."lionfish_comshop_member   
	                    where  1  and agentid in (".implode(',', $member_id_arr).")   order by member_id desc ";
	 
			$list1 =  M()->query($sql);
			
		
			if( !empty($list1) )
			{
				foreach( $list1 as $vv )
				{
					$level_1_ids[$vv['member_id']] = $vv['member_id'];
				}
				
				$level_sql2 =" select member_id from ".C('DB_PREFIX').
							"lionfish_comshop_member  where 1 and 
								agentid in (select member_id from ".C('DB_PREFIX')."lionfish_comshop_member  
								where agentid ={$member_id}  order by member_id desc )  order by member_id desc ";
				
				$list2 =  M()->query($level_sql2);
				
				if( !empty($list2) || !empty($list1) )
				{
					foreach( $list2 as $vv )
					{
						$level_2_ids[$vv['member_id']] = $vv['member_id'];
					}
					
					$need_ids = empty($level_1_ids) ? array() : $level_1_ids;
					if(!empty($level_2_ids))
					{
						foreach($level_2_ids as $vv)
						{
							$need_ids[] = $vv;
						}
					}
					
					$sql =" select * from ".C('DB_PREFIX').
								"lionfish_comshop_member  where 1 {$where} and 
									member_id in (".implode(',', $need_ids ).")  order by member_id desc limit {$offset},{$size}";
					
					$list =  M()->query($sql);
					
					$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX'). 
						'lionfish_comshop_member WHERE 1 ' . "{$where} and member_id in (".implode(',', $need_ids).") " );
					
					$total = $total_arr[0]['count'];
				}
			}
			
		}else if( $level == 3 ){
			$sql = "select member_id from ".C('DB_PREFIX')."lionfish_comshop_member   
	                    where  agentid in (".implode(',', $member_id_arr).")   order by member_id desc ";
	 
			$list1 =  M()->query($sql);
			
			if( !empty($list1) )
			{
				foreach( $list1 as $vv )
				{
					$level_1_ids[$vv['member_id']] = $vv['member_id'];
				}
				$need_ids = empty($level_1_ids) ? array() : $level_1_ids;
				
				$level_sql2 =" select * from ".C('DB_PREFIX').
							"lionfish_comshop_member  where  
								agentid in (select member_id from ".C('DB_PREFIX')."lionfish_comshop_member  
								where agentid ={$member_id}  order by member_id desc )  order by member_id desc ";
				
				$list2 =  M()->query($level_sql2);
				
				if( !empty($list2) )
				{
					foreach( $list2 as $vv )
					{
						$level_2_ids[$vv['member_id']] = $vv['member_id'];
					}
					
					if(!empty($level_2_ids))
					{
						foreach($level_2_ids as $vv)
						{
							$need_ids[] = $vv;
						}
					}
				}
				
				
				$level_sql3 =" select * from ".C('DB_PREFIX').
							"lionfish_comshop_member  where uniacid=:uniacid and 
								agentid in (".implode(',', $need_ids).")  order by member_id desc ";
				
				$list3 =  M()->query($level_sql3 );
				
				if( !empty($list3) )
				{
					foreach( $list3 as $vv )
					{
						$level_3_ids[$vv['member_id']] = $vv['member_id'];
					}
					
					if(!empty($level_3_ids))
					{
						foreach($level_3_ids as $vv)
						{
							$need_ids[] = $vv;
						}
					}
				}
				
				$level_sql3 =" select * from ".C('DB_PREFIX').
						"lionfish_comshop_member where 1 {$where} and member_id in (".implode(',',$need_ids).") order by member_id desc limit {$offset},{$size}";
		
				$list =  M()->query($level_sql3);
				
				$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX') . 
						'lionfish_comshop_member WHERE 1 ' . "{$where} and member_id in (".implode(',', $need_ids).") " );
				
				$total = $total_arr[0]['count'];
			}
				
		}
		
		if( !empty($list) )
		{
			foreach($list as $key => $val)
			{
				//member_id
				$val['child_level'] = 1;
				
				if( isset($level_2_ids[$val['member_id']]) )
				{
					$val['child_level'] = 2;
				}
				else if( isset($level_3_ids[$val['member_id']]) )
				{
					$val['child_level'] = 3;
				}
				
				//$val['create_time'] = date('Y-m-d H:i:s', $val['create_time']);
				
				$list[$key] = $val;
			}
		}
		
		$this->_GPC = $_GPC;
		$pager = pagination2($total, $pindex, $psize);
		
		$this->list = $list;
		$this->pager = $pager;
		
		$this->display();
		
	}
	
	public function clear_haibao()
	{
		M('lionfish_comshop_member')->where( "member_id > 0" )->save( array('commiss_qrcode' => '') );
			
		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	
	public function sharedetail()
	{
		$_GPC = I('request.');
		
		$id = $_GPC['id'];
		
		$keyword = $_GPC['keyword'];
		
		$condition = ' and share_id = '.$id."  and  (agentid=0 or agentid = {$id})  ";
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		
		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and ( username like "%'.$_GPC['keyword'].'%" or realname like "%'.$_GPC['keyword'].'%" or telephone like "%'.$_GPC['keyword'].'%") ';
			
		}
		

		if ($_GPC['comsiss_state'] != '') {
			$condition .= ' and comsiss_state=' . intval($_GPC['comsiss_state']);
		}

		
		$sql = 'SELECT * FROM ' . C('DB_PREFIX'). "lionfish_comshop_member                 
						WHERE 1 " . $condition . ' order by member_id desc  ';
				
		if (empty($_GPC['export'])) {
			$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
		}
		
		$list = M()->query($sql);
		
		$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX'). 'lionfish_comshop_member WHERE 1 ' . $condition);
		
		$total = $total_arr[0]['count'];
		
		if ($_GPC['export'] == '1') {
			
			foreach ($list as &$row) {
				
				$row['sharename'] = empty($val['share_parent_info']) ? '总店' : $val['share_parent_info']['username'];
				$row['parentname'] = empty($val['parent_info']) ? '总店' : $val['parent_info']['username'];
				
				
				$next_member_count_arr = D('Home/Commission')->get_member_all_next_count($val['member_id']);
				$row['level1'] = $next_member_count_arr['level_1_count'];
				$row['level2'] = $next_member_count_arr['level_2_count'];
				$row['level3'] = $next_member_count_arr['level_3_count'];
				
				//commission_info
				$row['commission_total'] = $row['commission_info']['commission_total'];
				$row['getmoney'] = $row['commission_info']['getmoney'];
				
				$row['createtime'] = date('Y-m-d H:i', $row['create_time']);
				$row['comsiss_time'] = empty($row['comsiss_time']) ? '': date('Y-m-d H:i', $row['comsiss_time']);
				
				$row['groupname'] = empty($row['groupname']) ? '无分组' : $row['groupname'];
				$row['levelname'] = empty($row['levelname']) ? '普通等级' : $row['levelname'];
				$row['parentname'] = empty($row['parentname']) ? '总店' : '[' . $row['agentid'] . ']' . $row['parentname'];
				$row['statusstr'] = empty($row['status']) ? '' : '通过';
				$row['followstr'] = empty($row['followed']) ? '' : '已关注';
			}
			
			unset($row);
			$columns = array(
				array('title' => 'ID', 'field' => 'member_id', 'width' => 12),
				array('title' => '用户名', 'field' => 'username', 'width' => 12),
				array('title' => '手机号', 'field' => 'telephone', 'width' => 12),
				array('title' => 'openid', 'field' => 'we_openid', 'width' => 24),
				array('title' => '推荐人', 'field' => 'sharename', 'width' => 12),
				array('title' => '上级', 'field' => 'parentname', 'width' => 12),
				array('title' => '分销商等级', 'field' => 'commission_level_name', 'width' => 12),
				array('title' => '下线分销商总数', 'field' => 'next_member_count', 'width' => 12),
				array('title' => '一级下线分销商数', 'field' => 'level1', 'width' => 12),
				array('title' => '二级下线分销商数', 'field' => 'level2', 'width' => 12),
				array('title' => '三级下线分销商数', 'field' => 'level3', 'width' => 12),
				array('title' => '累计佣金', 'field' => 'commission_total', 'width' => 12),
				array('title' => '打款佣金', 'field' => 'getmoney', 'width' => 12),
				
				array('title' => '注册时间', 'field' => 'createtime', 'width' => 12),
				array('title' => '成为分销商时间', 'field' => 'comsiss_time', 'width' => 12),
				array('title' => '审核状态', 'field' => 'comsiss_time', 'width' => 12)
			);
			
			  D('Seller/Excel')->export($list, array('title' => $id.'下级分享人数据-' . date('Y-m-d-H-i', time()), 'columns' => $columns));
			
		}
	
		$pager = pagination2($total, $pindex, $psize);
		
		$this->_GPC = $_GPC;
		
		$this->pager = $pager;
		$this->list = $list;
		$this->keyword = $keyword;
		
		$this->display();
	}
	
	public function distribution()
	{
		$_GPC = I('request.');
		
		$condition = ' and comsiss_flag = 1 ';
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		
		$type = 0;
		
		if( isset($_GPC['type']) && !empty($_GPC['type']) )
		{
			$type = $_GPC['type'];
		}
		
		switch( $type )
		{
			case 0:
				
				break;
			case 1:
				$condition .= " and comsiss_state=1 ";
			break;
			case 2:
				$condition .= " and comsiss_state=0 ";
			break;
		}
		
		
		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and ( username like "%'.$_GPC['keyword'].'%" or realname like "%'.$_GPC['keyword'].'%" or telephone like "%'.$_GPC['keyword'].'%") ';
			
		}
		
		$starttime = strtotime( date('Y-m-d')." 00:00:00" );
		$endtime = $starttime + 86400;
		
		
		if( isset($_GPC['searchtime']) && $_GPC['searchtime'] == 'create_time' )
		{
			if (!empty($_GPC['time']['start']) && !empty($_GPC['time']['end'])) {
				$starttime = strtotime($_GPC['time']['start']);
				$endtime = strtotime($_GPC['time']['end']);
				
				$condition .= ' AND comsiss_time >= '.$starttime.' AND comsiss_time <= '.$endtime.' ';
				
			}
		}
		
		$this->starttime = $starttime;
		$this->endtime = $endtime;
		/**
		if (!empty($_GPC['time']['start']) && !empty($_GPC['time']['end'])) {
			$starttime = strtotime($_GPC['time']['start']);
			$endtime = strtotime($_GPC['time']['end']);
			$condition .= ' AND comsiss_time >= '.$starttime.' AND comsiss_time <= '.$endtime;
			
		}
		**/

		if ($_GPC['comsiss_state'] != '') {
			$condition .= ' and comsiss_state=' . intval($_GPC['comsiss_state']);
		}

		if ($_GPC['commission_level_id'] != '') {
			$condition .= ' and commission_level_id =' . intval($_GPC['commission_level_id']);
		}
		
		if( isset($_GPC['groupid']) && !empty($_GPC['groupid']) )
		{
			$condition .= ' and groupid = '.$_GPC['groupid'];
		}
		
		$sql = 'SELECT * FROM ' . C('DB_PREFIX'). "lionfish_comshop_member                
						WHERE 1 " . $condition . ' order by member_id desc  ';
						
		if (empty($_GPC['export'])) {
			$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
		}
		
		$list = M()->query($sql);
		
		$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX'). 'lionfish_comshop_member WHERE 1 ' . $condition);
		
		$total = $total_arr[0]['count'];
		
		
		$level_list = M()->query("select * from ".C('DB_PREFIX').
						'lionfish_comshop_commission_level order by id asc ');
		
		$keys_level = array();
		
		$keys_level[0] = D('Home/Front')->get_config_by_name('commission_levelname');
		
		if( empty($keys_level[0]) )
		{
			$keys_level[0] = '普通等级';
		}
		
		foreach($level_list as $vv)
		{
			$keys_level[$vv['id']] = $vv['levelname'];
		}
		
		foreach( $list as $key => $val )
		{
			//普通等级 
			
			$val['share_parent_info'] = D('Home/Commission')->get_share_name($val['share_id']);
			
			$val['parent_info'] = D('Home/Commission')->get_parent_info($val['agentid']);
			
			$next_member_count_arr = D('Home/Commission')->get_member_all_next_count($val['member_id']);
			$val['next_member_count'] = $next_member_count_arr['total'];
			
			$val['commission_level_name'] = $keys_level[$val['commission_level_id']];
			
			$val['commission_info'] = D('Home/Commission')->get_commission_info( $val['member_id'] );
			
			$list[$key] = $val;
		}
		if ($_GPC['export'] == '1') {
			
			foreach ($list as &$row) {
				
				$row['sharename'] = empty($val['share_parent_info']) ? '总店' : $val['share_parent_info']['username'];
				$row['parentname'] = empty($val['parent_info']) ? '总店' : $val['parent_info']['username'];
				
				
				$next_member_count_arr = D('Home/Commission')->get_member_all_next_count($val['member_id']);
				$row['level1'] = $next_member_count_arr['level_1_count'];
				$row['level2'] = $next_member_count_arr['level_2_count'];
				$row['level3'] = $next_member_count_arr['level_3_count'];
				
				//commission_info
				$row['commission_total'] = $row['commission_info']['commission_total'];
				$row['getmoney'] = $row['commission_info']['getmoney'];
				
				$row['createtime'] = date('Y-m-d H:i', $row['create_time']);
				$row['comsiss_time'] = empty($row['comsiss_time']) ? '': date('Y-m-d H:i', $row['comsiss_time']);
				
				$row['groupname'] = empty($row['groupname']) ? '无分组' : $row['groupname'];
				$row['levelname'] = empty($row['levelname']) ? '普通等级' : $row['levelname'];
				$row['parentname'] = empty($row['parentname']) ? '总店' : '[' . $row['agentid'] . ']' . $row['parentname'];
				$row['statusstr'] = empty($row['status']) ? '' : '通过';
				$row['followstr'] = empty($row['followed']) ? '' : '已关注';
			}
			
			unset($row);
			$columns = array(
				array('title' => 'ID', 'field' => 'member_id', 'width' => 12),
				array('title' => '用户名', 'field' => 'username', 'width' => 12),
				array('title' => '手机号', 'field' => 'telephone', 'width' => 12),
				array('title' => 'openid', 'field' => 'we_openid', 'width' => 24),
				array('title' => '推荐人', 'field' => 'sharename', 'width' => 12),
				array('title' => '上级', 'field' => 'parentname', 'width' => 12),
				array('title' => '分销商等级', 'field' => 'commission_level_name', 'width' => 12),
				array('title' => '下线分销商总数', 'field' => 'next_member_count', 'width' => 12),
				array('title' => '一级下线分销商数', 'field' => 'level1', 'width' => 12),
				array('title' => '二级下线分销商数', 'field' => 'level2', 'width' => 12),
				array('title' => '三级下线分销商数', 'field' => 'level3', 'width' => 12),
				array('title' => '累计佣金', 'field' => 'commission_total', 'width' => 12),
				array('title' => '打款佣金', 'field' => 'getmoney', 'width' => 12),
				
				array('title' => '注册时间', 'field' => 'createtime', 'width' => 12),
				array('title' => '成为分销商时间', 'field' => 'comsiss_time', 'width' => 12),
				array('title' => '审核状态', 'field' => 'comsiss_time', 'width' => 12)
			);
			
			D('Seller/Excel')->export($list, array('title' => '分销商数据-' . date('Y-m-d-H-i', time()), 'columns' => $columns));
			
		}
		
		$pager = pagination2($total, $pindex, $psize);
		
		$this->pager = $pager;
		$this->_GPC = $_GPC;
		
		$this->list =$list;
		
		$this->display();
	}
	
	private function modifylevel()
	{
		$_GPC = I('request.');
		
		$id = trim($_GPC['id']);

		$set = D('Seller/Config')->get_all_config();
		if ($id == 'default') {
			$level = array('id' => 'default', 'levelname' => empty($set['commission_levelname']) ? '默认等级' : $set['commission_levelname'], 'commission1' => $set['commission1'], 'commission2' => $set['commission2'], 'commission3' => $set['commission3']);
			
			
		}
		else {
			
			$level = M('lionfish_comshop_commission_level')->where( array('id' => intval($id) ) )->find();	
		}

		if (IS_POST) {
			
			$data = array( 
			'levelname' => trim($_GPC['levelname']), 
			'commission1' => trim(trim($_GPC['commission1']), '%'),
			'commission2' => trim(trim($_GPC['commission2']), '%'), 
			'commission3' => trim(trim($_GPC['commission3']), '%'),
			'ordermoney' => $_GPC['ordermoney']
			);
			
			
			if (!empty($id)) {
				if ($id == 'default') {
					$updatecontent = '<br/>等级名称: ' . $set['levelname'] . '->' . $data['levelname'] . '<br/>一级佣金比例: ' . $set['commission1'] . '->' . $data['commission1'] . '<br/>二级佣金比例: ' . $set['commission2'] . '->' . $data['commission2'] . '<br/>三级佣金比例: ' . $set['commission3'] . '->' . $data['commission3'];
					
					$set_data = array();
					$set_data['commission_levelname'] = $data['levelname'];
					$set_data['commission1'] = $data['commission1'];
					$set_data['commission2'] = $data['commission2'];
					$set_data['commission3'] = $data['commission3'];
					
					D('Seller/Config')->update($set_data);
				}
				else {
					$updatecontent = '<br/>等级名称: ' . $level['levelname'] . '->' . $data['levelname'] . '<br/>一级佣金比例: ' . $level['commission1'] . '->' . $data['commission1'] . '<br/>二级佣金比例: ' . $level['commission2'] . '->' . $data['commission2'] . '<br/>三级佣金比例: ' . $level['commission3'] . '->' . $data['commission3'];
					
					M('lionfish_comshop_commission_level')->where( array('id' => $id) )->save( $data );
				}
			}
			else {
				M('lionfish_comshop_commission_level')->add($data);
			}

			show_json(1, array('url' => U('distribution/level')));
		}

		$this->level = $level;
		
		$this->display('Distribution/modifylevel');
	}
	
	public function withdrawallist()
	{
	    $_GPC = I('request.');
		
		
		$condition = '  ';
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
    
		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and (  id = '.intval($_GPC['keyword']).') ';
		}
		
		$starttime = strtotime( date('Y-m-d')." 00:00:00" );
		$endtime = $starttime + 86400;
		
		if (!empty($_GPC['time']['start']) && !empty($_GPC['time']['end'])) {
			$starttime = strtotime($_GPC['time']['start']);
			$endtime = strtotime($_GPC['time']['end']);
			
			$condition .= ' AND addtime >= '.$starttime.' AND addtime <= '.$endtime;
			
		}

		$this->starttime = $starttime;
		$this->endtime = $endtime;
		
		if ($_GPC['comsiss_state'] != '') {
			$condition .= ' and state=' . intval($_GPC['comsiss_state']);
		}

		$sql = 'SELECT * FROM ' . C('DB_PREFIX'). "lionfish_comshop_member_tixian_order                 
						WHERE 1 " . $condition . ' order by id desc  ';
						
		if (empty($_GPC['export'])) {
			$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
		}
		
		$community_tixian_fee = D('Home/Front')->get_config_by_name('community_tixian_fee');
		
		$list = M()->query($sql);
		
		$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX'). 
					'lionfish_comshop_member_tixian_order WHERE 1 ' . $condition);
		
		$total = $total_arr[0]['count'];
		
		$this->_GPC = $_GPC;
		
		//ims_lionfish_community_head_commiss
		
		foreach( $list as $key => $val )
		{
			//普通等级 
			$member_info = M('lionfish_comshop_member')->field('username,avatar,we_openid,telephone')->where( array('member_id' => $val['member_id'] ) )->find();
			
			$val['member_info'] = $member_info;
			
			$list[$key] = $val;
		}
		
		if ($_GPC['export'] == '1') {
			
			foreach($list as $key =>&$row)
			{
				$row['username'] = $row['member_info']['username'];
				
				$row['telephone'] = $row['member_info']['telephone'];
				
				$row['bankname'] = $row['bankname'];
				
				if( $row['type'] == 1 )
				{
					$row['bankname'] = '余额';
				}elseif( $row['type'] == 2 ){
					$row['bankname'] =  '微信零钱';
				}elseif($row['type'] == 3){
					$row['bankname'] =  '支付宝';
				}
				
				$row['bankaccount'] = "\t".$row['bankaccount'];
				$row['bankusername'] = $row['bankusername'];
				
				$row['get_money'] = $row['money']-$row['service_charge_money'];
				$row['addtime'] = date('Y-m-d H:i:s', $row['addtime']);
				if(!empty($row['shentime']))
				{
					$row['shentime'] = date('Y-m-d H:i:s', $row['shentime']);
				}
				
				if($row['state'] ==0)
				{
					$row['state'] = '待审核';
				}else if($row[state] ==1)
				{
					$row['state'] = '已审核，打款';
				}else if($row[state] ==2){
					$row['state'] = '已拒绝';
				}
			}
			unset($row);
			
			$columns = array(
				array('title' => 'ID', 'field' => 'id', 'width' => 12),
				array('title' => '用户名', 'field' => 'username', 'width' => 12),
				array('title' => '联系方式', 'field' => 'telephone', 'width' => 12),
				array('title' => '打款银行', 'field' => 'bankname', 'width' => 24),
				array('title' => '打款账户', 'field' => 'bankaccount', 'width' => 24),
				array('title' => '真实姓名', 'field' => 'bankusername', 'width' => 24),
				array('title' => '申请提现金额', 'field' => 'money', 'width' => 24),
				array('title' => '手续费', 'field' => 'service_charge_money', 'width' => 24),
				array('title' => '到账金额', 'field' => 'get_money', 'width' => 24),
				array('title' => '申请时间', 'field' => 'addtime', 'width' => 24),
				array('title' => '审核时间', 'field' => 'shentime', 'width' => 24),
				array('title' => '状态', 'field' => 'state', 'width' => 24)
			);
			
			D('Seller/Excel')->export($list, array('title' => '会员分销提现数据-' . date('Y-m-d-H-i', time()), 'columns' => $columns));
			
		}
		
		$pager = pagination2($total, $pindex, $psize);
		
		
		$this->pager = $pager;
		$this->list = $list;
		
		$this->display();
	}
	
	public function agent_check_apply()
	{
		$_GPC = I('request.');
		
		$commission_model = D('Home/Commission');
		
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}

		$comsiss_state = intval($_GPC['state']);
		$apply_list = M()->query('SELECT * FROM ' . C('DB_PREFIX'). 'lionfish_comshop_member_tixian_order  
						WHERE id in( ' . $id . ' ) ');
		$time = time();
		
		foreach ($apply_list as $apply) {
			if ($apply['state'] == $comsiss_state || $apply['state'] == 1 || $apply['state'] == 2) {
				continue;
			}
			$money = $apply['money'];
			
			if ($comsiss_state == 1) {
				
				switch( $apply['type'] )
				{
					case 1:
						$result = $commission_model->send_apply_yuer( $apply['id'] );
						break;
					case 2:
						$result = $commission_model->send_apply_weixin_yuer( $apply['id'] );
						break;
					case 3:
						$result = $commission_model->send_apply_alipay_bank( $apply['id'] );
						break;
					case 4:
						$result = $commission_model->send_apply_alipay_bank( $apply['id'] );
						break;
				}
				
				if( $result['code'] == 1)
				{
					show_json(0, array('url' => $_SERVER['HTTP_REFERER'],'message'=>$result['msg']) );
				}
				
				//检测是否存在账户，没有就新建
				//TODO....检测是否微信提现到零钱，如果是，那么就准备打款吧
		
				$commission_model->send_apply_success_msg($apply['id']);
			}
			else if ($comsiss_state == 2) {
				
				M('lionfish_comshop_member_tixian_order')->where( array('id' => $apply['id'] ) )->save( array('state' => 2, 'shentime' => $time) );
				
				//退回冻结的货款
				M()->execute("update ".C('DB_PREFIX')."lionfish_comshop_member_commiss set money=money+{$money},dongmoney=dongmoney-{$money} 
							where  member_id=".$apply['member_id']);
				
			}
			else {
				M('lionfish_comshop_member_tixian_order')->where( array('id' => $apply['id']) )->save( array('state' => 0, 'shentime' => 0) );
			}
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}	
	
}
?>