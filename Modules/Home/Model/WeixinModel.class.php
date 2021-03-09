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
namespace Home\Model;
class WeixinModel{

	
	public function refundOrder($order_id, $money=0, $uniacid=0,$order_goods_id=0)
	{
		$lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';
		
		set_time_limit(0);
		
		require_once $lib_path."/Weixin/lib/WxPay.Api.php";
		
		
		$order_info = M('lionfish_comshop_order')->where( array('order_id' => $order_id) )->find();
		
				
		$member_info = M('lionfish_comshop_member')->where( array('member_id' => $order_info['member_id'] ) )->find();		
		
		
		$openId = $member_info['openid'];
		$we_openid = $member_info['we_openid'];
		
		if( $order_info['from_type'] == 'wepro' )
		{
			$openId = $we_openid;
		}
		//we_openid
		//money
		$transaction_id = $order_info["transaction_id"];
		
		
		if( $order_info['type'] == 'integral' )
		{
			$total_fee = ( $order_info["shipping_fare"] )*100;
		
		}else{
			$total_fee = ($order_info["total"] + $order_info["shipping_fare"]-$order_info['voucher_credit']-$order_info['fullreduction_money'] - $order_info['score_for_money'] )*100;
		}
		
		
		$refund_fee = $total_fee;
		
		//order_goods_id
		if( !empty($order_goods_id) )
		{
			$order_goods_info = M('lionfish_comshop_order_goods')->where( array('order_goods_id' =>$order_goods_id ) )->find();
			
			$refund_fee = ($order_goods_info["total"] + $order_goods_info["shipping_fare"]-$order_goods_info['voucher_credit']-$order_goods_info['fullreduction_money'] - $order_goods_info['score_for_money'])*100;
		}
		
		
		
		
		if($money > 0)
		{
			$refund_fee = $money * 100;
		}
		
		if($order_info['payment_code'] == 'yuer')
		{
			//余额支付的，退款到余额
			//退款到余额
			
			//增加会员余额
			$refund_fee = $refund_fee / 100;
			
			if( $refund_fee > 0 )
			{
			
				M('lionfish_comshop_member')->where( array('member_id' => $order_info['member_id']) )->setInc('account_money',$refund_fee);
				
				$account_money_info = M('lionfish_comshop_member')->field('account_money')->where( array('member_id' =>$order_info['member_id'] ) )->find();
		
				$account_money = $account_money_info['account_money'];
				
		
				$member_charge_flow_data = array();
				
				$member_charge_flow_data['member_id'] = $order_info['member_id'];
				$member_charge_flow_data['money'] = $refund_fee;
				$member_charge_flow_data['operate_end_yuer'] = $account_money;
				$member_charge_flow_data['state'] = 4;
				$member_charge_flow_data['trans_id'] = $order_id;
				$member_charge_flow_data['order_goods_id'] = $order_goods_id;
				$member_charge_flow_data['charge_time'] = time();
				$member_charge_flow_data['add_time'] = time();
				
				M('lionfish_comshop_member_charge_flow')->add($member_charge_flow_data);
			}
			
			
			if($order_info['order_status_id'] == 12)
			{
				M('lionfish_comshop_order')->where( array('order_id' => $order_info['order_id']) )->save( array('order_status_id' => 7) );
			}
			
			
			
			
			$order_goods_list = M('lionfish_comshop_order_goods')->where( array('order_id' => $order_info['order_id'] ) )->select();
			
			$goods_model = D('Home/Pingoods');
			
			foreach($order_goods_list as $order_goods)
			{
				//$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity'],2);
				
				if( !empty($order_goods_id) && $order_goods_id > 0  )
				{
					if($order_goods_id ==  $order_goods['order_goods_id'] )
					{
						$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity'],2);
						
						$score_refund_info = M('lionfish_comshop_member_integral_flow')->where( array('order_goods_id' => $order_goods['order_goods_id'],'order_id' =>$order_info['order_id'],'type' => 'orderbuy' ) )->find();
						
						if( !empty($score_refund_info) )
						{
							 D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$score_refund_info['score'], 0 ,'退款增加积分', 'refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
						}
					}
				}else if( empty($order_goods_id) || $order_goods_id <=0 ){
					$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity'],2);
					
					$score_refund_info = M('lionfish_comshop_member_integral_flow')->where( array('order_goods_id' => $order_goods['order_goods_id'],'order_id' => $order_info['order_id'] ,'type' => 'orderbuy') )->find();			
										
					if( !empty($score_refund_info) )
					{
						 D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$score_refund_info['score'], 0 ,'退款增加积分','refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
					}
				}
				
				if( $order_info['type'] == 'integral' )
				{
					D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$order_info['total'], 0 ,'退款增加积分', 'refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
				}
			}
			//分佣也要退回去
			D('Seller/Community')->back_order_commission($order_info['order_id'],$order_goods_id);
				
			return array('code' => 1);
			//$this->refundOrder_success($order_info,$openId);
			//检测是否有需要退回积分的订单
		}
		else if($order_info['payment_code'] == 'admin'){
			 
			if($order_info['order_status_id'] == 12)
			{
				M('lionfish_comshop_order')->where( array('order_id' => $order_info['order_id']) )->save( array('order_status_id' => 7) );
			}	
				
			$order_info['total'] = $refund_fee / 100;
			
			$order_goods_list = M('lionfish_comshop_order_goods')->where( array('order_id' => $order_info['order_id'] ) )->select();
			
			
			$goods_model = D('Home/Pingoods');
			foreach($order_goods_list as $order_goods)
			{
				//$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity'],2);
				
				if( !empty($order_goods_id) && $order_goods_id > 0  )
				{
					if($order_goods_id ==  $order_goods['order_goods_id'] )
					{
						$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity'],2);
						
						$score_refund_info = M('lionfish_comshop_member_integral_flow')->where( array('order_goods_id' => $order_goods['order_goods_id'],'order_id' =>$order_info['order_id'],'type' => 'orderbuy' ) )->find();
						
						if( !empty($score_refund_info) )
						{
							 D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$score_refund_info['score'], 0 ,'退款增加积分', 'refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
						}
					}
				}else if( empty($order_goods_id) || $order_goods_id <=0 ){
					$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity'],2);
					
					$score_refund_info = M('lionfish_comshop_member_integral_flow')->where( array('order_goods_id' => $order_goods['order_goods_id'],'order_id' => $order_info['order_id'] ,'type' => 'orderbuy') )->find();			
										
					if( !empty($score_refund_info) )
					{
						 D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$score_refund_info['score'], 0 ,'退款增加积分','refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
					}
				}
				
				if( $order_info['type'] == 'integral' )
				{
					D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$order_goods['total'], 0 ,'退款增加积分','refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
				}
			
			}
			//分佣也要退回去
			D('Seller/Community')->back_order_commission($order_info['order_id'],$order_goods_id);
			return array('code' => 1);
			
		}
		else if($refund_fee == 0)
		{
			if($order_info['order_status_id'] == 12)
			{
				M('lionfish_comshop_order')->where( array('order_id' => $order_info['order_id']) )->save( array('order_status_id' => 7) );	
			}
				
			//ims_ lionfish_comshop_order_goods
			$order_goods = M('lionfish_comshop_order_goods')->where( array('order_id' => $order_info['order_id']) )->select();
			
			$order_goods_name = '';
			$order_goods_name_arr = array();
			$goods_model = D('Home/Pingoods');
			
			//get_config_by_name($name) 
			
			
			
			foreach ($order_goods as $key => $value) {
				//($order_id,$option,$goods_id,$quantity,$type='1')
				$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$value['rela_goodsoption_valueid'],$value['goods_id'],$value['quantity'],2);
				
				$score_refund_info = M('lionfish_comshop_member_integral_flow')->where( array('order_id' =>$order_info['order_id'] ,'order_goods_id' =>$value['order_goods_id'] ,'type' => 'orderbuy') )->find();
				
				if( !empty($score_refund_info) )
				{
					 D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$score_refund_info['score'], 0 ,'退款增加积分','refundorder', $order_info['order_id'] ,$value['order_goods_id'] );
				}
					
				//销量回退
				$order_goods_name_arr[] = $value['name'];
			}
			$order_goods_name = implode('\r\n', $order_goods_name_arr); //."\r\n";	
		
			
			$msg = '订单退款: 您的订单'.$order_info['order_num_alias'].'参团未成功，现退款:'.round($order_info["total"],2).'元，商品名称：'.$order_goods_name;
			
			$url = D('Home/Front')->get_config_by_name('shop_domain');
			
			//weixin_template_refund_order
			//send_template_msg($wx_template_data,$url,$openid,C('weixin_template_refund_order'));
			
			/**
			{{first.DATA}}
			订单编号：{{keyword1.DATA}}
			退款金额：{{keyword2.DATA}}
			{{remark.DATA}}
			---------------------------
			校白君提醒您，您有一笔退款成功，请留意。
			订单编号：20088115853
			退款金额：¥19.00
			更多学生价好货，在底部菜单栏哦~猛戳“校园专区”，享更多优惠！
			**/
			
			$wx_template_data = array();
			$wx_template_data['first'] = array('value' => '退款通知', 'color' => '#030303');
			$wx_template_data['keyword1'] = array('value' => $order_goods_name, 'color' => '#030303');
			$wx_template_data['keyword2'] = array('value' => round($order_info["total"],2), 'color' => '#030303');
			$wx_template_data['remark'] = array('value' => '拼团失败已按原路退款', 'color' => '#030303');
		
			
			if( $order_info['from_type'] == 'wepro' )
			{
				$template_data = array();
				$template_data['keyword1'] = array('value' => $order_info['order_num_alias'], 'color' => '#030303');
				$template_data['keyword2'] = array('value' => '商户名称', 'color' => '#030303');
				$template_data['keyword2'] = array('value' => $order_goods_name, 'color' => '#030303');
				$template_data['keyword3'] = array('value' => $order_info['total'].'元', 'color' => '#030303');
				$template_data['keyword4'] = array('value' => '已按原路退款', 'color' => '#030303');
				$template_data['keyword5'] = array('value' => $member_info['uname'], 'color' => '#030303');
				
				
				$template_id = D('Home/Front')->get_config_by_name('weprogram_template_refund_order');
				
				$pagepath = 'lionfish_comshop/pages/order/order?id='.$order_info['order_id'];
				
				
				$member_formid_info = M('lionfish_comshop_member_formid')->where("member_id=".$order_info['member_id']." and formid != '' and state =0")->order('id desc')->find();
				
				if(!empty( $member_formid_info ))
				{
					D('Seller/User')->send_wxtemplate_msg($template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$member_formid_info['formid'] );
					
					M('lionfish_comshop_member_formid')->where( array('id' => $member_formid_info['id']) )->save(array('state' => 1));
				}
				
				if( $openid != '1')
				{
					//send_template_msg($wx_template_data,$url,$member_info['openid'],C('weixin_template_refund_order'));
				}
			}else{
				//send_template_msg($wx_template_data,$url,$member_info['openid'],C('weixin_template_refund_order'));
			}
			
			//检测是否有需要退回积分的订单
			
		} else {
			
			$relative_list = M('lionfish_comshop_order_relate')->where( array('order_id' => $order_id ) )->find();
			
         	 $account_money = 0;
            if(!empty($relative_list))
            {
              	$order_all_id = $relative_list['order_all_id'];
              
				$relative_list_all = M('lionfish_comshop_order_relate')->where( array('order_all_id' => $order_all_id ) )->select();
				
				
				
              	if( count($relative_list_all) > 1 )
                {
                	foreach($relative_list_all as $val)
                    {
						$order_info_tmp = M('lionfish_comshop_order')->where( array('order_id' => $val['order_id'] ) )->find();
						
                    	$account_money += ($order_info_tmp["total"] + $order_info_tmp["shipping_fare"]-$order_info_tmp['voucher_credit']-$order_info_tmp['fullreduction_money'] );
                    }
					
					
					$account_money = $account_money * 100;
					$total_fee = $account_money;
                }
            }
			
			
			$input = new \WxPayRefund();
			
			$input->SetTransaction_id($transaction_id);
			$input->SetTotal_fee($total_fee);
			$input->SetRefund_fee($refund_fee);
			
			$mchid = D('Home/Front')->get_config_by_name('wepro_partnerid');
			
			$refund_no = $mchid.date("YmdHis").$order_info['order_id'];
			
			$input->SetOut_refund_no($refund_no);
			$input->SetOp_user_id($mchid);
			
			
			$res = \WxPayApi::refund($input,6,$order_info['from_type']);
			
			
			
			if( $res['err_code_des'] == '订单已全额退款' )
			{
				$res['result_code'] = 'SUCCESS';
			}
			
			if($res['result_code'] == 'SUCCESS')
			{
				
				if($order_info['order_status_id'] == 12)
				{
					M('lionfish_comshop_order')->where( array('order_id' => $order_info['order_id']) )->save( array('order_status_id' => 7) );
				}
				
				$order_info['total'] = $refund_fee / 100;
				
				
				$order_goods_list = M('lionfish_comshop_order_goods')->where( array('order_id' => $order_info['order_id']) )->select();	
					
				$order_goods_name_arr = array();
				$order_goods_name = '';
					
				foreach($order_goods_list as $order_goods)
				{
					
					$order_goods_name_arr[] = $order_goods['name'];
					//...
					if( !empty($order_goods_id) && $order_goods_id > 0  )
					{
						if($order_goods_id ==  $order_goods['order_goods_id'] )
						{
							D('Home/Pingoods')->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity'],2);
							
							
							$score_refund_info = M('lionfish_comshop_member_integral_flow')->where( array('order_id' => $order_info['order_id'],'order_goods_id'=>$order_goods['order_goods_id'] ,'type' => 'orderbuy') )->find();
							
							if( !empty($score_refund_info) )
							{
								 D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$score_refund_info['score'], 0 ,'退款增加积分', 'refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
							}
						}
					}else if( empty($order_goods_id) || $order_goods_id <=0 ){
										    
						D('Home/Pingoods')->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity'],2);
						
						$score_refund_info = M('lionfish_comshop_member_integral_flow')->where( array('order_id' => $order_info['order_id'],'order_goods_id' =>$order_goods['order_goods_id'] ,'type' => 'orderbuy') )->find();
						
						if( !empty($score_refund_info) )
						{
							 D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$score_refund_info['score'], 0 ,'退款增加积分', 'refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
						}
					}
					
					if( $order_info['type'] == 'integral' )
					{
						D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$order_goods['total'], 0 ,'退款增加积分','refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
					}
				
				}
				
				$order_goods_name = implode('\r\n', $order_goods_name_arr); //."\r\n";	
		
				 
				//分佣也要退回去 
				D('Seller/Community')->back_order_commission($order_info['order_id'],$order_goods_id);
				
				return array('code' => 1);
				
			} else {
				
				$order_refund_history = array();
				$order_refund_history['order_id'] =  $order_info['order_id'];
				$order_refund_history['order_goods_id'] =  $order_goods_id;
				
				$order_refund_history['message'] = $res['err_code_des'];
				$order_refund_history['type'] = 2;
				$order_refund_history['addtime'] = time();
				
				M('lionfish_comshop_order_refund_history')->add($order_refund_history);
				
				/**
				M('lionfish_comshop_order')->where( array('order_id' => $order_info['order_id']) )->save( array('order_status_id' => 10, 'remarksaler' => $res[err_code_des]) );
				**/
				
				return array('code' => 0, 'msg' => $res['err_code_des']);
				
			}
			
		}
		
		
		
		
	}
	
	public function refundOrder2($order_id,$money =0)
	{
		
		$lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';
		$data_path = dirname( dirname(dirname( dirname(__FILE__) )) ).'/Data/wxpaylogs/'.date('Y-m-d')."/";
		RecursiveMkdir($data_path);
		
		set_time_limit(0);
		
		
		require_once $lib_path."/Weixin/lib/WxPay.Api.php";
		require_once $lib_path."/Weixin/log.php";
		
		//初始化日志
		$logHandler= new \CLogFileHandler( $data_path .date('Y-m-d').'.log');
		
		\Log::Init($logHandler, 15);
		\Log::DEBUG("进行中，订单ID ：".$order_id );
		
		//pin
		$order_info = M('order')->where( array('order_id' => $order_id) )->find();
		
		$member_info = M('member')->where( array('member_id' => $order_info['member_id']) )->find();
		
		$openId = $member_info['openid'];
		$we_openid = $member_info['we_openid'];
		
		if( $order_info['from_type'] == 'wepro' )
		{
			$openId = $we_openid;
		}
		//we_openid
		//money
		$transaction_id = $order_info["transaction_id"];
		
		
		
		$total_fee = ($order_info["total"])*100;
		$refund_fee = $total_fee;
		if($money > 0)
		{
			$refund_fee = $money * 100;
		}
		
		
		
		if($order_info['payment_code'] == 'yuer')
		{
			//余额支付的，退款到余额
			//退款到余额
			$member_charge_flow_data = array();
			$member_charge_flow_data['member_id'] = $order_info['member_id'];
			$member_charge_flow_data['money'] = $order_info["total"];
			$member_charge_flow_data['state'] = 4;
			$member_charge_flow_data['trans_id'] = $order_id;
			$member_charge_flow_data['charge_time'] = time();
			$member_charge_flow_data['add_time'] = time();
			
			M('member_charge_flow')->add($member_charge_flow_data);
			//增加会员余额
			M('member')->where( array('member_id'=> $order_info['member_id'] ) )->setInc('account_money',$order_info["total"] ); 
					
			
			$order_info['total'] = $refund_fee / 100;
			$this->refundOrder_success($order_info,$openId);
			//检测是否有需要退回积分的订单
		}
		else if($refund_fee == 0)
		{
			M('order')->where( array('order_id' => $order_info['order_id']) )->save( array('order_status_id' => 7) );
			
			$config_info = M('config')->where( array('name' => 'SITE_URL') )->find();
			
			$order_goods = M('order_goods')->where( array('order_id' => $order_info['order_id']) )->select();
			
			$order_goods_name = '';
			$order_goods_name_arr = array();
			
			foreach ($order_goods as $key => $value) {
				
				$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$value['rela_goodsoption_valueid'],$value['goods_id'],$value['quantity'],2);
				//销量回退
				//$this->execute("UPDATE " . C('DB_PREFIX') . "goods SET seller_count = (seller_count - " . (int)$value['quantity'] . ") WHERE goods_id = '" . $value['goods_id'] . "' ");
				$order_goods_name_arr[] = $value['name'];
			}
			$order_goods_name = implode('\r\n', $order_goods_name_arr); //."\r\n";	
		
			
			$msg = '订单退款: 您的订单'.$order_info['order_num_alias'].'参团未成功，现退款:'.round($order_info["total"],2).'元，商品名称：'.$order_goods_name;
			$url = $config_info['value'];
			
			//weixin_template_refund_order
			//send_template_msg($wx_template_data,$url,$openid,C('weixin_template_refund_order'));
			$url = $url."/index.php?s=/Order/info/id/{$order_info['order_id']}.html";
			
			/**
			{{first.DATA}}
			订单编号：{{keyword1.DATA}}
			退款金额：{{keyword2.DATA}}
			{{remark.DATA}}
			---------------------------
			校白君提醒您，您有一笔退款成功，请留意。
			订单编号：20088115853
			退款金额：¥19.00
			更多学生价好货，在底部菜单栏哦~猛戳“校园专区”，享更多优惠！
			**/
			
			$wx_template_data = array();
			$wx_template_data['first'] = array('value' => '退款通知', 'color' => '#030303');
			$wx_template_data['keyword1'] = array('value' => $order_goods_name, 'color' => '#030303');
			$wx_template_data['keyword2'] = array('value' => round($order_info["total"],2), 'color' => '#030303');
			$wx_template_data['remark'] = array('value' => '拼团失败已按原路退款', 'color' => '#030303');
		
			
			if( $order_info['from_type'] == 'wepro' )
			{
				$template_data = array();
				$template_data['keyword1'] = array('value' => $order_goods_name, 'color' => '#030303');
				$template_data['keyword2'] = array('value' => '参团未成功', 'color' => '#030303');
				$template_data['keyword3'] = array('value' => '拼团失败已按原路退款', 'color' => '#030303');
				
				$pay_order_msg_info =  M('config')->where( array('name' => 'weprogram_template_fail_pin') )->find();
				$template_id = $pay_order_msg_info['value'];
				
				
				$pagepath = 'pages/order/order?id='.$order_info['order_id'];
				
				/**
				$member_formid_info = M('member_formid')->where( array('member_id' => $order_info['member_id'], 'state' => 0) )->find();
					
				send_wxtemplate_msg($template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$member_formid_info['formid']);
				//更新
				M('member_formid')->where( array('id' => $member_formid_info['id']) )->save( array('state' => 1) );
				
				$order_info['member_id']
				**/
				
				$member_formid_info = M('member_formid')->where( array('member_id' => $order_info['member_id'], 'formid' =>array('neq',''), 'state' => 0) )->order('id desc')->find();
				if(!empty( $member_formid_info ))
				{
					send_wxtemplate_msg($template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$member_formid_info['formid'] );
					M('member_formid')->where( array('id' => $member_formid_info['id']) )->save( array('state' => 1) );
				}
				
				
				if( $openid != '1')
				{
					//notify_weixin_msg($member_info['openid'],$msg,'退款通知',$url);
					send_template_msg($wx_template_data,$url,$member_info['openid'],C('weixin_template_refund_order'));
				}
			}else{
				//notify_weixin_msg($member_info['openid'],$msg,'退款通知',$url);
				send_template_msg($wx_template_data,$url,$member_info['openid'],C('weixin_template_refund_order'));
			}
			
			//检测是否有需要退回积分的订单
			
			\Log::DEBUG("退款成功。。。退款日志:退款订单号:" . $order_info['order_id'].',ten:'.$transaction_id.'   退款金额： '
			.$order_info["total"].',退款给：openid='.$openId);
			
		} else {
			
			$input = new \WxPayRefund();
			$input->SetTransaction_id($transaction_id);
			$input->SetTotal_fee($total_fee);
			$input->SetRefund_fee($refund_fee);
			$refund_no = \WxPayConfig::MCHID.date("YmdHis").$order_info['order_id'];
			
			$input->SetOut_refund_no($refund_no);
			$input->SetOp_user_id(\WxPayConfig::MCHID);
			
			$res = (\WxPayApi::refund($input,6,$order_info['from_type']));
			
			//var_dump($res);die();  wx80131aa7dfc4ff71
			
			if($res['result_code'] == 'SUCCESS')
			{
				$order_info['total'] = $refund_fee / 100;
				$this->refundOrder_success($order_info,$openId);
				
				\Log::DEBUG("退款成功。。。退款日志:退款订单号:" . $order_info['order_id'].',ten:'.$transaction_id.'   退款金额： '.$order_info["total"].',退款给：openid='.$openid);
				//检测是否有需要退回积分的订单
				
			} else {
			
				M('order')->where( array('order_id' => $order_info['order_id']) )->save( array('order_status_id' => 10, 'comment' => $res[err_code_des]) );
				
				\Log::DEBUG("退款失败。原因：{$res[err_code_des]}。。退款日志:退款订单号:" . $order_info['order_id'].',退款金额： '.$order_info["total"].',退款给：openid='.$openId);
			}
			
		}
		
		return true;
	}
	
	/**
		取消已经付款的 待发货订单
		5、处理订单，
		6、处理退款，
	**/
	public  function del_cancle_order($order_id)
	{				
		$order_info = M('lionfish_comshop_order')->where( array('order_id' => $order_id ) )->find();
		
		
		//判断订单状态是否已付款，避免多次退款，不合理
		if( $order_info['order_status_id'] == 1 )
		{
			$result = $this->refundOrder($order_id);
			
			if( $result['code'] == 1 )
			{
				$order_history = array();
				$order_history['order_id'] = $order_id;
				$order_history['order_status_id'] = 5;
				$order_history['notify'] = 0;
				$order_history['comment'] = '会员前台申请取消订单，取消成功，并退款。';
				$order_history['date_added'] = time();
				
				M('lionfish_comshop_order_history')->add( $order_history );
				
				M('lionfish_comshop_order')->where( array('order_id' => $order_id) )->save( array('order_status_id' => 5) );
				
				return array('code' => 0);
			}else{
				$order_history = array();
				$order_history['order_id'] = $order_id;
				$order_history['order_status_id'] = 10;
				$order_history['notify'] = 0;
				$order_history['comment'] = '申请取消订单，但是退款失败。';
				$order_history['date_added'] = time();
				
				M('lionfish_comshop_order_history')->add( $order_history );
				
				M('lionfish_comshop_order')->where( array('order_id' => $order_id ) )->save( array('order_status_id' => 10, 'remarksaler' => $result['msg']) );
				
				return array('code' => 1, 'msg' => $result['msg'] );
			}
			
		}
		 //如果退款成功了。那么就进行
		
	}
	
	
	
	
	public function test_form_msg()
	{
		$member_info = M('member')->where( array('member_id' => 26) )->find();
		
		$form_id_arr = M('member_formid')->where( array('member_id' => 26,'state' =>0) )->find();
		
		M('member_formid')->where( array('id' => $form_id_arr['id'] ) )->save( array('state' =>1) );
		$form_id = $form_id_arr['formid'];
		
		$template_data = array();
		$template_data['keyword1'] = array('value' => '338866', 'color' => '#030303');
		$template_data['keyword2'] = array('value' => '商品名称', 'color' => '#030303');
		$template_data['keyword3'] = array('value' => '18元', 'color' => '#030303');
		$template_data['keyword4'] = array('value' => '已按原路退款', 'color' => '#030303');
		$template_data['keyword5'] = array('value' => '小鱼', 'color' => '#030303');
		
		$pay_order_msg_info =  M('config')->where( array('name' => 'weprogram_template_refund_order') )->find();
		$template_id = $pay_order_msg_info['value'];
		
		
		$pagepath = 'pages/order/order?id='.$order_info['order_id'];
			
		$rs = 	send_wxtemplate_msg($template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$form_id);
			var_dump($rs);die();
	}
	
	public function refundOrder_success($order_info,$openid)
	{
		M('order')->where( array('order_id' => $order_info['order_id']) )->save( array('order_status_id' => 7) );
		
		$member_info = M('member')->where( array('member_id' => $order_info['member_id']) )->find();
		
		
		$config_info = M('config')->where( array('name' => 'SITE_URL') )->find();
		
		$order_goods = M('order_goods')->where( array('order_id' => $order_info['order_id']) )->select();
		$goods_model = D('Home/Goods');
		
		$order_goods_name = '';
		$order_goods_name_arr = array();
		
		foreach ($order_goods as $key => $value) {
			//$this->execute("UPDATE " . C('DB_PREFIX') . "goods SET quantity = (quantity + " . (int)$value['quantity'] . ") WHERE goods_id = '" . $value['goods_id'] . "' ");
			
			$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$value['rela_goodsoption_valueid'],$value['goods_id'],$value['quantity'],2);
			//销量回退
			//$this->execute("UPDATE " . C('DB_PREFIX') . "goods SET seller_count = (seller_count - " . (int)$value['quantity'] . ") WHERE goods_id = '" . $value['goods_id'] . "' ");
			$order_goods_name_arr[] = $value['name'];
		}
			
		$order_goods_name = implode('\r\n', $order_goods_name_arr); //."\r\n";	
		
		$msg = '订单退款: 您的订单'.$order_info['order_num_alias'].'参团未成功，现退款:'.round($order_info["total"],2).'元，商品名称：'.$order_goods_name;
		$url = $config_info['value'];
		
		$wx_template_data = array();
		$wx_template_data['first'] = array('value' => '退款通知', 'color' => '#030303');
		$wx_template_data['keyword1'] = array('value' => $order_goods_name, 'color' => '#030303');
		$wx_template_data['keyword2'] = array('value' => $order_info['total'].'元', 'color' => '#030303');
		$wx_template_data['remark'] = array('value' => '已按原路退款', 'color' => '#030303');
		
		 
		$url = $url."/index.php?s=/Order/info/id/{$order_info['order_id']}.html";
		
		
		if( $order_info['from_type'] == 'wepro' )
		{
			/**
			退款成功通知
			关键词
			订单号
			{{keyword1.DATA}}
			商品名称
			{{keyword2.DATA}}
			退款金额
			{{keyword3.DATA}}
			温馨提示
			{{keyword4.DATA}}
			备注
			{{keyword5.DATA}}
			**/
			
			//$total_money = ($order_info["total"],2);
			
			$template_data = array();
			$template_data['keyword1'] = array('value' => $order_info['order_num_alias'], 'color' => '#030303');
			$template_data['keyword2'] = array('value' => $order_goods_name, 'color' => '#030303');
			$template_data['keyword3'] = array('value' => $order_info['total'].'元', 'color' => '#030303');
			$template_data['keyword4'] = array('value' => '已按原路退款', 'color' => '#030303');
			$template_data['keyword5'] = array('value' => $member_info['uname'], 'color' => '#030303');
			
			$pay_order_msg_info =  M('config')->where( array('name' => 'weprogram_template_refund_order') )->find();
			$template_id = $pay_order_msg_info['value'];
			
			
			$pagepath = 'pages/order/order?id='.$order_info['order_id'];
			
			
			/**
				$member_formid_info = M('member_formid')->where( array('member_id' => $order_info['member_id'], 'state' => 0) )->find();
					
				send_wxtemplate_msg($template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$member_formid_info['formid']);
				//更新
				M('member_formid')->where( array('id' => $member_formid_info['id']) )->save( array('state' => 1) );
				
			**/
			$member_formid_info = M('member_formid')->where( array('member_id' => $order_info['member_id'],'formid' =>array('neq',''), 'state' => 0) )->order('id desc')->find();
				
			//$order_info['member_id']
			if( !empty($member_formid_info) )
			{
				$rs = 	send_wxtemplate_msg($template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$member_formid_info['formid']);
				M('member_formid')->where( array('id' => $member_formid_info['id']) )->save( array('state' => 1) );
			}
				
			
			if( $openid != '1')
			{
				//notify_weixin_msg($openid,$msg,'退款通知',$url);
				send_template_msg($wx_template_data,$url,$openid,C('weixin_template_refund_order'));
			
			}
		}else{
			//notify_weixin_msg($openid,$msg,'退款通知',$url);
			send_template_msg($wx_template_data,$url,$openid,C('weixin_template_refund_order'));
			
		}
		
		
		
	}
}
?>