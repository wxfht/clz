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

class SupplyController extends CommonController {
	
	
	public function index()
	{
		$export = I('get.export',0);
		$condition = ' 1=1 ';
		$pindex = $pindex = I('get.page', 1);
		$psize = 20;
    
		$keyword = I('get.keyword','','trim');
		if (!empty($keyword)) {
			
			$condition .= ' and ( shopname like "'.'%' . $keyword . '%'.'" or name like "'.'%' . $keyword . '%'.'" or mobile like "'.'%' . $keyword . '%'.'" ) ';
			
		}
		$this->keyword = $keyword;
		
		$time = I('get.time');
		if (!empty($time['start']) && !empty($time['end'])) {
			$starttime = strtotime($time['start']);
			$endtime = strtotime($time['end']);
			
			$this->starttime = $starttime;
			$this->endtime = $endtime;
			
			$condition .= ' AND apptime >= '.$starttime.' AND apptime <= '.$endtime;
		}
		
		$comsiss_state = I('get.comsiss_state','');
		
		if ($comsiss_state != '') {
			$condition .= ' and state=' . intval($comsiss_state);
		}
		$this->comsiss_state = $comsiss_state;

		$sql = 'SELECT * FROM ' . C('DB_PREFIX') . "lionfish_comshop_supply \r\n                
						WHERE  " . $condition . ' order by id desc  ';
		
						
		if (empty($export)) {
			$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
		}
		
		$list = M()->query($sql);
			
		$total = M('lionfish_comshop_supply')->where($condition)->count();
		
		
		foreach( $list as $key => $val )
		{
			//goods_count
			$goods_count = M('lionfish_comshop_good_common')->where( array('supply_id' => $val['id']) )->count();
			
			$val['goods_count'] = $goods_count;
			$list[$key] = $val;
		}
		
		if ($export == '1') {
			
			foreach ($list as &$row) {
				
			    $row['username'] = $val['member_info']['username'];
			    $row['we_openid'] = $val['member_info']['we_openid'];
			    $row['commission_total'] = 0;
			    $row['getmoney'] = 0;
			    $row['fulladdress'] = $row['province_name'].$row['city_name'].$row['area_name'].$row['country_name'].$row['address'];
			    $row['addtime'] = date('Y-m-d H:i:s', $row['addtime']);
			    $row['apptime'] = date('Y-m-d H:i:s', $row['apptime']);
			    $row['state'] = $row['state'] == 1 ? '已审核':'未审核';
			}
			
			unset($row);
			
			
			$columns = array(
				array('title' => 'ID', 'field' => 'id', 'width' => 12),
				array('title' => '店铺名称', 'field' => 'shopname', 'width' => 12),
			    array('title' => '供应商名称', 'field' => 'name', 'width' => 12),
				array('title' => '联系方式', 'field' => 'mobile', 'width' => 12),
			    array('title' => '商品数量', 'field' => 'goods_count', 'width' => 12),
				
				array('title' => '注册时间', 'field' => 'addtime', 'width' => 12),
				array('title' => '成为团长时间', 'field' => 'apptime', 'width' => 12),
				array('title' => '审核状态', 'field' => 'state', 'width' => 12)
			);
			
			
			D('Seller/Excel')->export($list, array('title' => '供应商数据-' . date('Y-m-d-H-i', time()), 'columns' => $columns));
			
		}
		
		$pager = pagination2($total, $pindex, $psize);
		$this->list = $list;
		$this->pager = $pager;
		
		$this->display('Supply/supply');
	}
	
	
	public function authority()
	{
		$_GPC = I('request.');
		
		if (IS_POST) {
			
			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));
				
			$data['supply_can_goods_updown'] = isset($data['supply_can_goods_updown']) ? $data['supply_can_goods_updown'] : 2;
			$data['supply_can_vir_count'] = isset($data['supply_can_vir_count']) ? $data['supply_can_vir_count'] : 2;
			$data['supply_can_goods_istop'] = isset($data['supply_can_goods_istop']) ? $data['supply_can_goods_istop'] : 2;
			$data['supply_can_goods_isindex'] = isset($data['supply_can_goods_isindex']) ? $data['supply_can_goods_isindex'] : 2;
			$data['supply_can_goods_sendscore'] = isset($data['supply_can_goods_sendscore']) ? $data['supply_can_goods_sendscore'] : 2;
			$data['supply_can_goods_newbuy'] = isset($data['supply_can_goods_newbuy']) ? $data['supply_can_goods_newbuy'] : 2;
			$data['supply_can_look_headinfo'] = isset($data['supply_can_look_headinfo']) ? $data['supply_can_look_headinfo'] : 2;
			$data['supply_can_nowrfund_order'] = isset($data['supply_can_nowrfund_order']) ? $data['supply_can_nowrfund_order'] : 2;
			$data['supply_can_goods_spike'] = isset($data['supply_can_goods_spike']) ? $data['supply_can_goods_spike'] : 2;
			
			$data['supply_can_confirm_delivery'] = isset($data['supply_can_confirm_delivery']) ? $data['supply_can_confirm_delivery'] : 2;
			$data['supply_can_confirm_receipt'] = isset($data['supply_can_confirm_receipt']) ? $data['supply_can_confirm_receipt'] : 2;
			
		
		
			D('Seller/Config')->update($data);
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		
		$data = D('Seller/Config')->get_all_config();
		
		$this->data = $data;
		
		$this->_GPC = $_GPC;
		$this->display();
	}
	
	
	
	public function distributionpostal()
	{
		$_GPC = I('request.');
		if (IS_POST) {
			
			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));
				
			$data['supply_commiss_tixianway_yuer'] = isset($data['supply_commiss_tixianway_yuer']) ? $data['supply_commiss_tixianway_yuer'] : 1;
			$data['supply_commiss_tixianway_weixin'] = isset($data['supply_commiss_tixianway_weixin']) ? $data['supply_commiss_tixianway_weixin'] : 1;
			$data['supply_commiss_tixianway_alipay'] = isset($data['supply_commiss_tixianway_alipay']) ? $data['supply_commiss_tixianway_alipay'] : 1;
			$data['supply_commiss_tixianway_bank'] = isset($data['supply_commiss_tixianway_bank']) ? $data['supply_commiss_tixianway_bank'] : 1;
			
			$data['supply_commiss_tixianway_weixin_offline'] = isset($data['supply_commiss_tixianway_weixin_offline']) ? $data['supply_commiss_tixianway_weixin_offline'] : 1;
			
			
			
			D('Seller/Config')->update($data);
			
			show_json(1, array('url' => U('Supply/distributionpostal')) );
		}
		
		$data = D('Seller/Config')->get_all_config();
		
		$this->data = $data;
		
		$this->display();
	}
	
	
	public function agent_check_first()
	{
		$_GPC = I('request.');
		
		$id = intval($_GPC['id']);
		
		if ( IS_POST ) {
			$type = $_GPC['type'];
			
			$time = time();
			
			M('lionfish_comshop_supply')->where( array('id' => $id )  )->save( array('type' => $type,'state' => 1, 'apptime' => $time) );
			
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		
		$this->id = $id;
		include $this->display();
	}
	
	
	public function agent_tixian()
	{
		$_GPC = I('request.');
		
		$id = intval($_GPC['id']);
		
		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}

		$comsiss_state = intval($_GPC['state']);
		$members = M()->query('SELECT * FROM ' . C('DB_PREFIX'). 'lionfish_supply_tixian_order 
						WHERE id in( ' . $id . ' ) ');
		$time = time();

		$open_weixin_qiye_pay = D('Home/Front')->get_config_by_name('open_weixin_qiye_pay');
		
		$lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';
		
		require_once $lib_path."/Weixin/lib/WxPay.Api.php";
		
		foreach ($members as $member) {
			if ($member['state'] === $comsiss_state) {
				continue;
			}

			if ($comsiss_state == 1) {
				
				if( $member['state'] == 0 )
				{
					
					if( $member['supply_apply_type'] == 1 )
					{
						if( !empty($open_weixin_qiye_pay) && $open_weixin_qiye_pay == 1 )
						{
							
							$supper_info = M('lionfish_comshop_supply')->field('member_id')->where( array('id' => $member['supply_id'] ) )->find();
							
							if( !empty($supper_info['member_id']) && $supper_info['member_id'] > 0 )
							{
								
								$mb_info = M('lionfish_comshop_member')->field('we_openid')->where( array('member_id' => $supper_info['member_id'] ) )->find();
							
								$partner_trade_no = build_order_no($member['id']);
								$desc = date('Y-m-d H:i:s', $member['addtime']).'申请的提现已到账';
								$username = $member['bankaccount'];
								$amount = ($member['money']) * 100;
								
								$openid = $mb_info['we_openid'];
								
								$res = \WxPayApi::payToUser($openid,$amount,$username,$desc,$partner_trade_no,$_W['uniacid']);
								
								
								if(empty($res) || $res['result_code'] =='FAIL')
								{
									show_json(0, array('message' => $res['err_code_des'] ));
								}
								
							}else{
								show_json(0, array('message' => '请编辑供应商资料绑定会员，才能进行微信零钱提现'));
							}
							
							
							
						}else{
							show_json(0, array('message' => '请前往团长提现设置开启微信企业付款，供应商提现公用资料'));
						}
					}
					
					M('lionfish_supply_tixian_order')->where( array('id' => $member['id'] ) )->save( array('state' => 1, 'shentime' => $time) );
					//打款
					
					M()->execute("update ".C('DB_PREFIX')."lionfish_supply_commiss set dongmoney=dongmoney-{$member[money]},getmoney=getmoney+{$member[money]}  
							where  supply_id=".$member['supply_id']);
				}
			}
			else {
				
				if( $member['state'] == 0 )
				{
					M('lionfish_supply_tixian_order')->where( array('id' => $member['id']) )->save( array('state' => 2, 'shentime' => 0) );
					//退款
					
					M()->execute( "update ".C('DB_PREFIX')."lionfish_supply_commiss set dongmoney=dongmoney-{$member[money]},money=money+{$member[money]}  
							where supply_id=".$member['supply_id'] );
				}
			}
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	
	public function test()
	{
	    $list = M('lionfish_supply_commiss_order')->where( array('state' =>0) )->select();
	
	    foreach( $list as $val )
	    {
	        M('lionfish_supply_commiss_order')->where( array('id' => $val['id'])  )->save(  array('money' =>  $val['money'] - $val['head_commiss_money'] ) );
	    }
	
	    echo 'success';
	    die();
	}
	//---begin
	public function floworder()
	{
		$_GPC = I('request.');
		
		$supper_info = get_agent_logininfo();
			
		$condition = '  supply_id='. $supper_info['id'].' ';
		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		
		$sql = 'SELECT * FROM ' . C('DB_PREFIX'). "lionfish_supply_commiss_order                 
						WHERE  " . $condition . ' order by id desc  ';
						
		
		if( isset($_GPC['export']) && $_GPC['export'] == 1 )
		{
			
		}else{
			$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
		}		
		
		
		$list = M()->query($sql);
		
		$total = M('lionfish_supply_commiss_order')->where( $condition )->count();
		
		
		foreach( $list as $key => $val )
		{
			
			$order_goods = M('lionfish_comshop_order_goods')->where( array('order_goods_id' => $val['order_goods_id'] ) )->find();
			$val['total_money'] =  $order_goods['old_total']; //显示优惠之前的价格
			$val['goods_name'] = $order_goods['name'];
			$val['option_sku'] = D('Seller/Order')->get_order_option_sku($order_goods['order_id'], $order_goods['order_goods_id']);
			
			
			$commission_list = 	M('lionfish_community_head_commiss_order')->where( "order_id=".$order_goods['order_id']." and order_goods_id=".$order_goods['order_goods_id'] )->select();				
					
			$val['commission_list'] =  $commission_list;
			
			
			$list[$key] = $val;
		}
		
		if( isset($_GPC['export']) && $_GPC['export'] == 1 )
		{
			$columns = array(
					array('title' => '订单id', 'field' => 'order_id', 'width' => 16),
					array('title' => '商品名称', 'field' => 'goods_name', 'width' => 32),
					array('title' => '金额', 'field' => 'total_money', 'width' => 16),
					array('title' => '团长佣金', 'field' => 'head_commiss_money', 'width' => 16),
					array('title' => '服务费比例', 'field' => 'comunity_blili', 'width' => 16),
					array('title' => '服务费金额', 'field' => 'fuwu_money', 'width' => 16),
					array('title' => '实收金额', 'field' => 'money', 'width' => 16),
					array('title' => '状态', 'field' => 'state', 'width' => 16),
			);
			
			$exportlist = array();
			
			foreach($list as $val)
			{
				$tmp_exval = array();
				$tmp_exval['order_id'] = $val['order_id'];
				$tmp_exval['goods_name'] = $val['goods_name'].$val['option_sku'];
				$tmp_exval['total_money'] = $val['total_money'];
				$tmp_exval['head_commiss_money'] = '-'.$val['head_commiss_money'];
				$tmp_exval['comunity_blili'] = $val['comunity_blili'].'%';
				$tmp_exval['fuwu_money'] = '-'.( round($val['total_money']*$val['comunity_blili']/100,2));
				$tmp_exval['money'] = $val['money'];
				
				if( $val[state] ==2 ){	
					$tmp_exval['state'] = '订单取消';
				}else if( $val[state] ==1 ){
					$tmp_exval['state'] = '已结算';
				}else{
					$tmp_exval['state'] = '待结算';
				}
				
				$exportlist[] = $tmp_exval;
			}
				
			D('Seller/Excel')->export($exportlist, array('title' => '资金流水', 'columns' => $columns));
		}
		
		$pager = pagination2($total, $pindex, $psize);
		
		
		$this->list = $list;
		$this->pager = $pager;
		
		
		$this->display('Supply/floworder');
	}
	
	public function admintixianlist()
	{
		$_GPC = I('request.');
		
		
		$condition = ' 1 ';
		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		
		$sql = 'SELECT * FROM ' . C('DB_PREFIX') . "lionfish_supply_tixian_order                 
						WHERE  " . $condition . ' order by id desc  ';
						
		$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
		
		
		$list = M()->query($sql);
		
		$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX'). 
					'lionfish_supply_tixian_order WHERE  ' . $condition );
					
		$total = $total_arr[0]['count'];			
		
		foreach( $list as $key => $val )
		{
			$supper_info = D('Home/Front')->get_supply_info($val['supply_id']);
			$val['supper_info'] = $supper_info;
			$list[$key] = $val;
		}
		
		$pager = pagination2($total, $pindex, $psize);
		
		
		$this->list = $list;
		$this->pager = $pager;
		
		$this->display();
	}
	public function tixianlist()
	{
		$_GPC = I('request.');
		
		$supper_info = get_agent_logininfo();
			
		
		$condition = ' supply_id= '.$supper_info['id'];
		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		
		$sql = 'SELECT * FROM ' . C('DB_PREFIX') . "lionfish_supply_tixian_order                 
						WHERE  " . $condition . ' order by id desc  ';
						
		$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
		
		$list = M()->query($sql);
		
		$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX'). 
					'lionfish_supply_tixian_order WHERE  ' . $condition);
		
		$total = $total_arr[0]['count'];
		
		foreach( $list as $key => $val )
		{
			
			$list[$key] = $val;
		}
		
		$pager = pagination2($total, $pindex, $psize);
		
		
		$supply_commiss = M('lionfish_supply_commiss')->where( array('supply_id' => $supper_info['id'] ) )->find();
		
		//TODO...
		if( empty($supply_commiss) )
		{
			$lionfish_supply_commiss_data = array();
			$lionfish_supply_commiss_data['supply_id'] = $supper_info['id'];
			$lionfish_supply_commiss_data['money'] = 0;
			$lionfish_supply_commiss_data['dongmoney'] = 0;
			$lionfish_supply_commiss_data['getmoney'] = 0;
			
			M('lionfish_supply_commiss')->add( $data );
				
			
			$supply_commiss = array();
			$supply_commiss['money'] = 0;
			$supply_commiss['dongmoney'] = 0;
			$supply_commiss['getmoney'] = 0;
		}
		
		
		$this->supply_commiss = $supply_commiss;
		$this->list = $list;
		$this->pager = $pager;
		
		$this->display('Supply/tixianlist');
	}
	
	
	public function apply_money()
	{
		$_GPC = I('request.');
		
		$supper_info = get_agent_logininfo();
		
		$supply_min_apply_money = D('Home/Front')->get_config_by_name('supply_min_money');
		
		
		if( empty($supply_min_apply_money) )
		{
			$supply_min_apply_money = 0;
		}
		
		$supply_commiss = M('lionfish_supply_commiss')->where( array('supply_id' =>$supper_info['id'] ) )->find();
		
		$last_tixian_order = array('bankname' =>'微信','bankaccount' => '','bankusername' => '' ,'supply_apply_type' => -1 );
		
		$lionfish_supply_tixian_order = M('lionfish_supply_tixian_order')->where( array('supply_id' =>$supper_info['id'] ) )->order('id desc')->find();
		
		if( !empty($lionfish_supply_tixian_order) )
		{
			$last_tixian_order['bankname'] = $lionfish_supply_tixian_order['bankname'];
			$last_tixian_order['bankaccount'] = $lionfish_supply_tixian_order['bankaccount'];
			$last_tixian_order['bankusername'] = $lionfish_supply_tixian_order['bankusername'];
			$last_tixian_order['supply_apply_type'] = $lionfish_supply_tixian_order['supply_apply_type'];
		}
		
		$this->supply_min_apply_money  = $supply_min_apply_money;
		$this->supply_commiss  = $supply_commiss;
		$this->last_tixian_order  = $last_tixian_order;
		$this->lionfish_supply_tixian_order  = $lionfish_supply_tixian_order;
		
		
		$sup_info = M('lionfish_comshop_supply')->where( array('id' => $supper_info['id'] ) )->find();
		
		//member_id
		$bind_member = array();
		
		if( $sup_info['member_id'] > 0 )
		{				
			$bind_member = M('lionfish_comshop_member')->where( array('member_id' => $sup_info['member_id'] ) )->find();				
		}
		
		$this->bind_member = $bind_member;
		
		if (IS_POST) {
			
			$supply_apply_type = $_GPC['supply_apply_type'];
			//1 微信 2 支付宝  3银行卡
			//bankname bankaccount bankusername
			$weixin_account = $_GPC['weixin_account'];
			$alipay_account = $_GPC['alipay_account'];
			$card_name = $_GPC['card_name'];
			$card_account = $_GPC['card_account'];
			$card_username = $_GPC['card_username'];
			$weixin_account_xx = $_GPC['weixin_account_xx'];
			$ti_money =  floatval( $_GPC['ti_money'] );
			
			
			if($ti_money < $supply_min_apply_money){
				show_json(0, array('message' => '最低提现'.$supply_min_apply_money));
			}
			
			if($ti_money <=0){
				show_json(0, array('message' => '最低提现大于0元'));
			}
			
			if($ti_money > $supply_commiss['money']){
				show_json(0, array('message' => '当前最多提现'.$supply_commiss['money']));
			}
			
							
			$supper_in = M('lionfish_comshop_supply')->field('commiss_bili')->where( array('id' => $supper_info['id'] ) )->find();	
				
			$ins_data = array();
			$ins_data['supply_id'] = $supper_info['id'];
			$ins_data['money'] = $ti_money;
			$ins_data['service_charge'] = round( ($ti_money * $supper_in['commiss_bili']) /100  ); 
			
			$ins_data['state'] = 0;
			$ins_data['shentime'] = 0;
			$ins_data['is_send_fail'] = 0;
			$ins_data['fail_msg'] = '';
			$ins_data['supply_apply_type'] = $supply_apply_type;
			
			//1 微信 2 支付宝  3银行卡
			if($supply_apply_type == 1)
			{
				$ins_data['bankname'] = '微信零钱';
				$ins_data['bankaccount'] = $weixin_account;
				$ins_data['bankusername'] = '';
			}else if($supply_apply_type == 2){
				$ins_data['bankname'] = '支付宝';
				$ins_data['bankaccount'] = $alipay_account;
				$ins_data['bankusername'] = '';
			}else if($supply_apply_type == 3){
				$ins_data['bankname'] = $card_name;
				$ins_data['bankaccount'] = $card_account;
				$ins_data['bankusername'] = $card_username;
			}else if($supply_apply_type == 4){
				$ins_data['bankname'] = '微信私下转';
				$ins_data['bankaccount'] = $weixin_account_xx;
				$ins_data['bankusername'] = '';
			}
			
			$ins_data['addtime'] = time();
			
			M('lionfish_supply_tixian_order')->add($ins_data);
			
			
			M('lionfish_supply_commiss')->where( array('supply_id' => $supper_info['id'] ) )->setInc('money',-$ti_money);
			M('lionfish_supply_commiss')->where( array('supply_id' => $supper_info['id'] ) )->setInc('dongmoney',$ti_money);
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		
		
		$data = D('Seller/Config')->get_all_config();
		
		
		$supply_commiss_tixianway_weixin = !isset($data['supply_commiss_tixianway_weixin']) || (isset($data['supply_commiss_tixianway_weixin']) &&  $data['supply_commiss_tixianway_weixin'] ==2) ? 2 : 0;
		$supply_commiss_tixianway_alipay = !isset($data['supply_commiss_tixianway_alipay']) || (isset($data['supply_commiss_tixianway_alipay']) &&  $data['supply_commiss_tixianway_alipay'] ==2) ? 2 : 0;
		$supply_commiss_tixianway_bank   = !isset($data['supply_commiss_tixianway_bank']) || (isset($data['supply_commiss_tixianway_bank']) &&  $data['supply_commiss_tixianway_bank'] ==2) ? 2 : 0;
		$supply_commiss_tixianway_weixin_offline   = !isset($data['supply_commiss_tixianway_weixin_offline']) || (isset($data['supply_commiss_tixianway_weixin_offline']) &&  $data['supply_commiss_tixianway_weixin_offline'] ==2) ? 2 : 0;
		
		
		
		$this->data = $data;
		$this->supply_commiss_tixianway_weixin = $supply_commiss_tixianway_weixin;
		$this->supply_commiss_tixianway_alipay = $supply_commiss_tixianway_alipay;
		$this->supply_commiss_tixianway_bank = $supply_commiss_tixianway_bank;
		
		$this->supply_commiss_tixianway_weixin_offline = $supply_commiss_tixianway_weixin_offline;
		
		
		
		$this->display();
	}
	
	/**
		供应商登录
	**/
	public function login()
	{
		
		
		$this->display();
	}
	/**
		供应商登录提交密码
	**/
	public function login_do()
	{
		$_GPC = I('request.');
		
		
		//mobile:mobile, password:password}
		$mobile = trim($_GPC['mobile']);
		$password = trim($_GPC['password']);
			
		if( empty($mobile) || empty($password) )
		{
			echo json_encode( array('code' => 1, 'msg' => '请填写您的账号密码！') );
			die();
		}			
			
		$record = array( );
		
		
		$temp = M('lionfish_comshop_supply')->where(  array('login_name' => $mobile ) )->find();
		
		if( !empty($temp) ) 
		{
			$password = md5( $temp["login_slat"].$password );
			if( $password == $temp["login_password"] ) 
			{
				$record = $temp;
			}
		}
		
		
		if( !empty($record) ) 
		{
			if( $record["state"] == 0) 
			{
				echo json_encode( array('code' => 1, 'msg' => '您的账号正在审核或是已经被系统禁止，请联系网站管理员解决！') );
				die();
			}
			if( !empty($_W["siteclose"]) ) 
			{
				echo json_encode( array('code' => 1, 'msg' => "站点已关闭，关闭原因：" . $_W["setting"]["copyright"]["reason"] ) );
				die();
			}
			
			if (C('USER_AUTH_ON')) {
				unset($_SESSION[C('USER_AUTH_KEY')]);
				unset($_SESSION[C('ADMIN_AUTH_KEY')]);
			}
			
			 session('seller_auth', array());
	    	session('seller_auth_sign', -1);	
		
			$auth = array(
				'uid'             => $record["id"],
				'shopname'        => $temp['shopname'],
				'username'        => $temp['shopname'],
				'type'       	  => $record['type'],
				'role'	  	  => 'agenter', 
				'role_id'	  	  => 0,
				'last_login_time' => $seller['s_last_login_time'],
			 );			
			
			session('agent_auth', $auth);
			session('agent_auth_sign', data_auth_sign($auth));	
			
			if( empty($forward) ) 
			{
				$forward = $_GPC["forward"];
			}
			if( empty($forward) ) 
			{
				$forward = U('index/index');
			}
			//message("欢迎回来，" . $record["title"] . "。", $forward, "success" );
			
			cookie('last_login_page',2);
			
			echo json_encode( array('code' => 0, 'url' => $forward ) );
			die();
		}
		else 
		{
			
			echo json_encode( array('code' => 1, 'msg' => "您的账号密码错误！") );
			die();
		}
		die();
	}
	
	//---end 
	
	public function config()
	{
		
		$gpc = I('request.');
		
		if (IS_POST) {
			
			$data = ((is_array($gpc['data']) ? $gpc['data'] : array()));
			
			D('Seller/Config')->update($data);
			
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		
			$data = D('Seller/Config')->get_all_config();
			$this->data = $data;
		 $this->display();
	}
	
	public function baseconfig()
	{
		$gpc = I('request.');
		if (IS_POST) {
			
			$data = ((is_array($gpc['data']) ? $gpc['data'] : array()));
			
			D('Seller/Config')->update($data);
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		
		$data = D('Seller/Config')->get_all_config();
		
		
		$this->data = $data;
			
		$this->display();
	}
	
	
	public function agent_check()
	{
		
		$id = I('get.id',0);

		if (empty($id)) {
			$ids = I('post.ids');
			$id =  (is_array($ids) ? implode(',', $ids) : 0);
		}

		$comsiss_state = I('request.state');
					
		$members = M('lionfish_comshop_supply')->field('id,state')->where( array('id' => array('in', $id) ) )->select();		
					
					
		$time = time();

		
		foreach ($members as $member) {
			if ($member['state'] === $comsiss_state) {
				continue;
			}

			if ($comsiss_state == 1) {
				$res = M('lionfish_comshop_supply')->where(array('id' => $member['id']))->save( array('state' => 1, 'apptime' => $time) );
				
				
			}
			else if($comsiss_state == 2)
			{
				
				M('lionfish_comshop_supply')->where(  array('id' => $member['id']) )->save( array('state' => 2, 'apptime' => $time ) );
				
			}
			else {
				M('lionfish_comshop_supply')->where(array('id' => $member['id']))->save( array('state' => 0, 'apptime' => 0) );
			}
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	
	public function zhenquery()
	{
	   
	    $kwd =  I('get.keyword','','trim');
	    
		$is_ajax =  I('request.is_ajax',0,'intval');
		
	    $condition = ' state=1 ';
	    
	    if (!empty($kwd)) {
	        $condition .= ' AND ( `shopname` LIKE "'.'%' . $kwd . '%'.'" or `name` like  "'.'%' . $kwd . '%'.'" or `mobile` like  "'.'%' . $kwd . '%'.'" )';
	       
	    }
	    /**
			分页开始
		**/
		$page =  I('request.page',1,'intval');
		$page = max(1, $page);
		$page_size = 10;
		/**
			分页结束
		**/
		
		$ds = M('lionfish_comshop_supply')->where( $condition )->order('id desc')->limit( (($page - 1) * $page_size) . ',' . $page_size )->select();
		
		$total = M('lionfish_comshop_supply')->where( $condition )->count();
		
		$ret_html = '';
	    foreach ($ds as &$value) {
	        $value['nickname'] = htmlspecialchars($value['username'], ENT_QUOTES);
	        $value['logo'] = tomedia($value['logo']);
	        $value['supply_id'] = $value['id'];	
			
			if($is_ajax == 1)
			{
				$ret_html .= '<tr>';
				$ret_html .= '<td><img src="'.$value['logo'].'" style="width:30px;height:30px;padding1px;border:1px solid #ccc" />';
				$ret_html .= $value['shopname'];
				$ret_html .=  '<td>'.$value['name'].'</td>';
				$ret_html .=  '<td>'.$value['mobile'].'</td>';
				
				$ret_html .=  '<td style="width:80px;"><a href="javascript:;" class="choose_dan_link" data-json=\''.json_encode($value).'\'>选择</a></td>';
				$ret_html .=  '</tr>';
			}
	    }
	    
		$pager = pagination($total, $page, $page_size,'',$context = array('before' => 5, 'after' => 4, 'isajax' => 1));
		
	    unset($value);
	    
		if( $is_ajax == 1 )
		{
			echo json_encode( array('code' => 0, 'html' => $ret_html,'pager' => $pager) );
			die();
		}
		
	   $this->ds = $ds;
	   $this->pager = $pager;
	    
	    $this->display('Supply/query');
	}
	
	public function addsupply()
	{
		$id =  I('get.id', 0);
		
		if (IS_POST) {
		    $data = array();
		    
		    $data['id'] = $id;
		   
		    $data['shopname'] = I('post.shopname');
		    $data['logo'] = I('post.logo');
		    $data['name'] = I('post.name');
		    $data['mobile'] = I('post.mobile');
		    $data['state'] = I('post.state');
			
			
		    $data['login_name'] = I('post.login_name');
		    $data['login_password'] = I('post.login_password');
		    $data['type'] = I('post.type');
		    $data['commiss_bili'] = I('post.commiss_bili');
			$data['member_id'] =  I('post.member_id');
			
			
		    $data['storename'] = I('post.storename');
		    $data['banner'] = I('post.banner');
			
		    $data['apptime'] = time();
		    $data['addtime'] = time();
		    
		    $rs = D('Seller/supply')->modify_supply($data);
		    
		    if($rs)
		    {
		        show_json(1, array('url' => U('supply/index')));
		    }else{
		        show_json(0, array('message' => '保存失败'));
		    }
		    //show_json(1, array('url' => U('distribution/level')));
		    // show_json(0, array('message' => '未找到订单!'));
		    //show_json(1, array('url' => referer()));
		}
		
		if($id > 0)
		{
			$item = M('lionfish_comshop_supply')->where( array('id' => $id) )->find();
			$this->item = $item;
			
			$saler = array();
			
		    if( $item['member_id'] > 0 )
			{
				$saler = M('lionfish_comshop_member')->field('member_id, username as nickname,avatar')->where( array('member_id' => $item['member_id'] ) )->find();
			
			}
			
		    $this->saler = $saler;
		}
		$this->id = $id;
		
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

			
		$items = M('lionfish_supply_tixian_order')->field('id')->where( 'id in( ' . $id . ' )' )->select();		

		foreach ($items as $item ) {
			
			M('lionfish_supply_tixian_order')->where( array('id' => $item['id']) )->save(  array('bankaccount' => $value)  );
			
		}  
		
		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	
	public function deletesupply()
	{
		$_GPC = I('request.');
		
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
		}

		$items = M('lionfish_comshop_supply')->field('id')->where( 'id in( ' . $id . ' )' )->select();
				
		foreach ($items as $item ) {
			
			M('lionfish_comshop_supply')->where( array('id' => $item['id']) )->delete();
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	
	
	
	
}
?>