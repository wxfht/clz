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

class UserModel{
	
	public function get_member_count($where='')
	{
		
		
		$total = M('lionfish_comshop_member')->where("1 ".$where)->count();
		
		return $total;
	}
	
	//------------------------------------------------begin
	public function delete_use_auto_template()
	{
		@set_time_limit(0);
		
		$config_data = array();
		
		$weixin_config = array();
		$weixin_config['appid'] = D('Home/Front')->get_config_by_name('wepro_appid');
		$weixin_config['appscert'] = D('Home/Front')->get_config_by_name('wepro_appsecret');
		
		$jssdk = new \Lib\Weixin\Jssdk( $weixin_config['appid'], $weixin_config['appscert']);
		
		$re_access_token = $jssdk->getweAccessToken();
		
		$send_url ="https://api.weixin.qq.com/cgi-bin/wxopen/template/list?access_token={$re_access_token}";
		
		$data = array();
		$data['offset'] = 0;
		$data['count'] = 20;
		
		$result_json = $this->sendhttps_post($send_url, json_encode($data));
		
		$result = json_decode($result_json, true);
		
		$del_title_arr = array('申请成功提醒','退款成功通知','订单支付成功通知','订单发货提醒','核销成功通知','提现到账通知');
		$del_template_arr = array();
		
		
		if($result['errcode'] == 0)
		{
			foreach( $result['list'] as $val )
			{
				if( in_array($val['title'], $del_title_arr) )
				{
					$del_template_arr[] = $val['template_id'];
				}
			}
		}
		
		if( !empty($del_template_arr) )
		{
			foreach($del_template_arr as $vv)
			{
				$send_url ="https://api.weixin.qq.com/cgi-bin/wxopen/template/del?access_token={$re_access_token}";
		
				$data = array();
				$data['template_id'] = $vv;
				
				$result_json = $this->sendhttps_post($send_url, json_encode($data));
			}
		}
		
		//https://api.weixin.qq.com/cgi-bin/wxopen/template/list?access_token=ACCESS_TOKEN
	}
	
	public function mange_template_auto()
	{
		
		$this->delete_use_auto_template();
		
		@set_time_limit(0);
		
		
		$config_data = array();
		
		$weixin_config = array();
		$weixin_config['appid'] = D('Home/Front')->get_config_by_name('wepro_appid');
		$weixin_config['appscert'] = D('Home/Front')->get_config_by_name('wepro_appsecret');
		
		$jssdk = new \Lib\Weixin\Jssdk( $weixin_config['appid'], $weixin_config['appscert']);
		
		$re_access_token = $jssdk->getweAccessToken();
		
		$send_url ="https://api.weixin.qq.com/cgi-bin/wxopen/template/add?access_token={$re_access_token}";
		
		//-----------------团长申请成功发送通知------------------------
		$data = array();
		
		$data['id'] = 'AT0197';
		$data['keyword_id_list'] = array(1,13,3,6,77,44,50);
		
		$result_json = $this->sendhttps_post($send_url, json_encode($data));
		
		$result = json_decode($result_json, true);
		if($result['errcode'] == 0)
		{
			$config_data['weprogram_template_apply_community'] = $result['template_id'];
		}
		
		//------------------订单支付成功通知----------------------------
		$data = array();
		
		$data['id'] = 'AT0009';
		$data['keyword_id_list'] = array(1,13,10,11,20);
		
		$result_json = $this->sendhttps_post($send_url, json_encode($data));
		
		$result = json_decode($result_json, true);
		if($result['errcode'] == 0)
		{
			$config_data['weprogram_template_pay_order'] = $result['template_id'];
		}
		//---------------------------------------------------------------
		//------------------订单发货提醒--------------------------------- 
		$data = array();
		
		$data['id'] = 'AT0007';
		$data['keyword_id_list'] = array(5,7,47,34,11);
		
		$result_json = $this->sendhttps_post($send_url, json_encode($data));
		
		$result = json_decode($result_json, true);
		if($result['errcode'] == 0)
		{
			$config_data['weprogram_template_send_order'] = $result['template_id'];
		}
		//---------------------------------------------------------------
		
		//------------------核销成功通知--------------------------------- 
		$data = array();
		
		$data['id'] = 'AT0423';
		$data['keyword_id_list'] = array(5,2,6,3,9);
		
		$result_json = $this->sendhttps_post($send_url, json_encode($data));
		
		$result = json_decode($result_json, true);
		if($result['errcode'] == 0)
		{
			$config_data['weprogram_template_hexiao_success'] = $result['template_id'];
		}
		//---------------------------------------------------------------
		//------------------退款成功通知---------------------------------  
		$data = array();
		
		$data['id'] = 'AT0787';
		$data['keyword_id_list'] = array(8,13,14,17,7,18);
		
		$result_json = $this->sendhttps_post($send_url, json_encode($data));
		
		$result = json_decode($result_json, true);
		if($result['errcode'] == 0)
		{
			$config_data['weprogram_template_refund_order'] = $result['template_id'];
		}
		//---------------------------------------------------------------
		//------------------提现到账通知---------------------------------  
		$data = array();
		
		$data['id'] = 'AT0830';
		$data['keyword_id_list'] = array(5,3,1,6,8,11,2);
		
		$result_json = $this->sendhttps_post($send_url, json_encode($data));
		
		$result = json_decode($result_json, true);
		if($result['errcode'] == 0)
		{
			$config_data['weprogram_template_apply_tixian'] = $result['template_id'];
		}
		//---------------------------------------------------------------
		
		//------------------拼团开团通知---------------------------------  
		
		$data = array();
		
		$data['id'] = 'AT0541';
		$data['keyword_id_list'] = array(15,1,10,26,24);
		
		$result_json = $this->sendhttps_post($send_url, json_encode($data));
		
		$result = json_decode($result_json, true);
		if($result['errcode'] == 0)
		{
			$config_data['weprogram_template_open_tuan'] = $result['template_id'];
		}
		//---------------------------------------------------------------
		
		//------------------参团通知---------------------------------  
		
		$data = array();
		
		$data['id'] = 'AT0933';
		$data['keyword_id_list'] = array(3,18,27);
		
		$result_json = $this->sendhttps_post($send_url, json_encode($data));
		
		$result = json_decode($result_json, true);
		if($result['errcode'] == 0)
		{
			$config_data['weprogram_template_take_tuan'] = $result['template_id'];
		}
		//------------------拼团成功通知---------------------------------  
		
		$data = array();
		
		$data['id'] = 'AT0051';
		$data['keyword_id_list'] = array(13,6,12);
		
		$result_json = $this->sendhttps_post($send_url, json_encode($data));
		
		$result = json_decode($result_json, true);
		if($result['errcode'] == 0)
		{
			$config_data['weprogram_template_pin_tuansuccess'] = $result['template_id'];
		}
		//---------------------------------------------------------------
		
		
		D('Seller/Config')->update($config_data);
		
		
		//AT0197   申请时间 所在地 服务地址 姓名 手机号 申请状态 同意时间1

	}
	

	//-----------------------------------------------end
	//--------begin 
	
	/**
		更改会员余额
	**/
	public function sendMemberMoneyChange($member_id, $num, $changetype, $remark='')
	{
		$member_info = M('lionfish_comshop_member')->field('account_money')->where( array('member_id' => $member_id) )->find();
		
		$account_money = $member_info['account_money'];
		
		$flow_data = array();
		$flow_data['member_id'] = $member_id;
		$flow_data['trans_id'] = '';
		
		//0，未支付，1已支付,3余额付款，4退款到余额，5后台充值 6 后台扣款
		
		//增加
		if($changetype == 0)
		{
			$up_sql = "update ".C('DB_PREFIX')."lionfish_comshop_member set account_money = account_money+ ".$num ." where  member_id=".$member_id;
			$flow_data['state'] = '5';
		}else if($changetype == 1)
		{
		//减少
			$up_sql = "update ".C('DB_PREFIX')."lionfish_comshop_member set account_money = account_money - ".$num ." where  member_id=".$member_id;
			$flow_data['state'] = '8';
		}else if($changetype == 2){
		//最终积分
			$up_sql = "update ".C('DB_PREFIX')."lionfish_comshop_member set account_money = ".$num ." where  member_id=".$member_id;
			if($account_money >= $num)
			{
				$flow_data['state'] = '8';
				$num = $account_money - $num;
			}else{
				$flow_data['state'] = '5';
				$num = $num - $account_money;	
			}
		}
		else if( $changetype == 9 )
		{
			$up_sql = "update ".C('DB_PREFIX')."lionfish_comshop_member set account_money = account_money+ ".$num ." where  member_id=".$member_id;
			$flow_data['state'] = '9';
		}
		else if( $changetype == 10 )
		{
			$up_sql = "update ".C('DB_PREFIX')."lionfish_comshop_member set account_money = account_money+ ".$num ." where  member_id=".$member_id;
			$flow_data['state'] = '10';
		}else if( $changetype == 11 )
		{//拼团佣金
			$up_sql = "update ".C('DB_PREFIX')."lionfish_comshop_member set account_money = account_money+ ".$num ." where  member_id=".$member_id;
			$flow_data['state'] = '11';
		}
		
		
		$flow_data['money'] = $num;
		
		$flow_data['remark'] = $remark;
		
		$flow_data['charge_time'] = time();
		$flow_data['add_time'] = time();
		
		M()->execute( $up_sql );
		
		M('lionfish_comshop_member_charge_flow')->add($flow_data);
	}
	
	/**
		更新会员积分
	**/
	public function sendMemberPointChange($member_id,$num, $changetype ,$remark ='', $ch_type='system_add')
	{
		
		$member_info = M('lionfish_comshop_member')->field('score')->where( array('member_id' => $member_id) )->find();
		
		$member_score = $member_info['score'];
		
		
		$flow_data = array();
		$flow_data['member_id'] = $member_id;
		$flow_data['type'] = $ch_type;
		$flow_data['order_id'] = '0';
		
		//增加
		if($changetype == 0)
		{
			$up_sql = "update ".C('DB_PREFIX')."lionfish_comshop_member set score = score+ ".$num ." where member_id=".$member_id;
			$flow_data['in_out'] = 'in';
		}else if($changetype == 1)
		{
		//减少
			$up_sql = "update ".C('DB_PREFIX')."lionfish_comshop_member set score = score - ".$num ." where  member_id=".$member_id;
			$flow_data['in_out'] = 'out';
		}else if($changetype == 2){
		//最终积分
			$up_sql = "update ".C('DB_PREFIX')."lionfish_comshop_member set score = ".$num ." where  member_id=".$member_id;
			if($member_score >= $num)
			{
				$flow_data['in_out'] = 'out';
				$num = $member_score - $num;
			}else{
				$flow_data['in_out'] = 'in';
				$num = $num - $member_score;	
			}
		}
		
		//after_operate_score
		
		$flow_data['score'] = $num;
		$flow_data['state'] = 1;
		$flow_data['remark'] = $remark;
		$flow_data['addtime'] = time();
		
		M()->execute( $up_sql );
		
		$info = M('lionfish_comshop_member')->field('score')->where( array('member_id' => $member_id ) )->find();
		
		$flow_data['after_operate_score'] = $info['score'];
		
		M('lionfish_comshop_member_integral_flow')->add($flow_data);
		
	}
	//--------end
	
	
	
	
	
	/**
		发送订阅小程序消息
	**/
	
	function send_subscript_msg( $template_data,$url,$pagepath,$to_openid,$template_id, $delay_time = 0 )
	{
		
		
		$weixin_config = array();
		$weixin_config['appid'] = D('Seller/Front')->get_config_by_name('wepro_appid');
		$weixin_config['appscert'] = D('Seller/Front')->get_config_by_name('wepro_appsecret');
		$siteroot = D('Seller/Front')->get_config_by_name('shop_domain');
		
		
		$jssdk = new \Lib\Weixin\Jssdk( $weixin_config['appid'], $weixin_config['appscert']);
		$re_access_token = $jssdk->getweAccessToken();
		
		
		$template = array(
			'touser' => $to_openid,
			'template_id' => $template_id,
			'page' => $pagepath,
			'data' => $template_data
		);
		
		if($delay_time > 0)
			sleep($delay_time);
		
		 $send_url ="https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token={$re_access_token}";
		 $result = $this->sendhttps_post($send_url, json_encode($template));
		
		 //log
		 
		
		 $ck_json_arr = json_decode($result,true); 
		 $template_json = json_encode($template);
		 $date = date('Y/m/d H:i:s');
		 file_put_contents(ROOT_PATH.'Runtime/Logs/wx/wxts.log',$date."---参数---".$template_json."--返回：--".$result.PHP_EOL, FILE_APPEND);

		 return $ck_json_arr;
	}
	
	
	
	/**
	发送小程序模板消息
	**/
	function send_wxtemplate_msg($template_data,$url,$pagepath,$to_openid,$template_id,$form_id='1',$uniacid=0,$wx_template_data = array() ,$delay_time = 0)
	{
		
		
		$weixin_config = array();
		$weixin_config['appid'] = D('Seller/Front')->get_config_by_name('wepro_appid');
		$weixin_config['appscert'] = D('Seller/Front')->get_config_by_name('wepro_appsecret');
		$siteroot = D('Seller/Front')->get_config_by_name('shop_domain');
		
		
		$jssdk = new \Lib\Weixin\Jssdk( $weixin_config['appid'], $weixin_config['appscert']);
		$re_access_token = $jssdk->getweAccessToken();
		
		
		$template = array(
			'touser' => $to_openid,
			'template_id' => $template_id,
			'form_id' => $form_id,
			'page' => $pagepath,
			'data' => $template_data
		);
		
		 
		
		
		 if(!empty($wx_template_data))
		 {
			 $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/uniform_send?access_token={$re_access_token}";
				
				$new_template = array();
				$new_template['touser'] = $to_openid;
													
				$new_template['mp_template_msg'] = array(
														'appid' => $wx_template_data['appid'],
														'template_id' => $wx_template_data['template_id'],
														'url' => $siteroot,
														'miniprogram' => array(
																			'appid' => $weixin_config['appid'],
																			'pagepath' => $wx_template_data['pagepath']
																		),
														'data' => $wx_template_data['data']
													);
				
				$result = $this->sendhttps_post($url, json_encode($new_template));
				
				
				$result = json_encode($result);
				
		 }
		 
		 if( !empty($template_data) )
		 {
			  if($delay_time > 0)
				sleep($delay_time);
			
			 $send_url ="https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token={$re_access_token}";
			$result = $this->sendhttps_post($send_url, json_encode($template));
		
		 }
		 
		
		return json_decode($result,true);
	}
	
	
	public function just_send_wxtemplate($to_openid, $uniacid=0,$wx_template_data = array() )
	{
		$weixin_config = array();
		$weixin_config['appid'] = D('Seller/Front')->get_config_by_name('wepro_appid');
		$weixin_config['appscert'] = D('Seller/Front')->get_config_by_name('wepro_appsecret');
		$siteroot = D('Seller/Front')->get_config_by_name('shop_domain');
		
		
		$jssdk = new \Lib\Weixin\Jssdk( $weixin_config['appid'], $weixin_config['appscert']);
		$re_access_token = $jssdk->getweAccessToken();
		
		
		$url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/uniform_send?access_token={$re_access_token}";
				
		$new_template = array();
		$new_template['touser'] = $to_openid;
											
		$new_template['mp_template_msg'] = array(
												'appid' => $wx_template_data['appid'],
												'template_id' => $wx_template_data['template_id'],
												'url' => $siteroot,
												'miniprogram' => array(
																	'appid' => $weixin_config['appid'],
																	'pagepath' => $wx_template_data['pagepath']
																),
												'data' => $wx_template_data['data']
											);
		
		$result = $this->sendhttps_post($url, json_encode($new_template));
	}
	
	
	
	function sendhttp_get($url)
	{
		
		$curl = curl_init();
		curl_setopt($curl,CURLOPT_URL,$url);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($curl,CURLOPT_POST,1);
		curl_setopt($curl,CURLOPT_POSTFIELDS,array());
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		$result = curl_exec($curl);
		curl_close($curl);
		return $result;
	}


	function sendhttps_post($url,$data)
	{
		$curl = curl_init();
		curl_setopt($curl,CURLOPT_URL,$url);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($curl,CURLOPT_POST,1);
		curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		$result = curl_exec($curl);
		if(curl_errno($curl)){
		  return 'Errno'.curl_error($curl);
		}
		curl_close($curl);
		return $result;
	}
	
	
}
?>