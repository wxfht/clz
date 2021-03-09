<?php
namespace Home\Model;
use Think\Model;
/**
 * 分销模型模型
 * @author fish
 *
 */
class FenxiaoModel {
	
	public $table = 'pin';
    
	public function getSiteUrl()
	{
	    $config_info = M('config')->where( array('name' => 'SITE_URL') )->find();
	    $url = $config_info['value'];
	    return $url;
	}
	
	/**
	只有拼团成功或者单独购买已经发货的 ， 订单退款取消佣金
	**/
	public function back_order_commiss_money($order_id)
	{
		$member_commiss_order_list = M('member_commiss_order')->where( array('order_id' =>$order_id,'state' => 1 ) )->select();
				
		if(!empty($member_commiss_order_list))
		{
		   foreach($member_commiss_order_list as $member_commiss_order)
		   {
			   //分佣订单
			   M('member_commiss_order')->where( array('id' =>$member_commiss_order['id'] ) )->save( array('state' => 2) );
			   M('member_commiss')->where( array('member_id' => $member_commiss_order['member_id']) )->setDec('money',$member_commiss_order['money']); 
			 }
		}
	}
	
	public function send_order_commiss_money($order_id)
	{
		$member_commiss_order_list = M('member_commiss_order')->where( array('order_id' =>$order_id,'state' => 0 ) )->select();
				
		if(!empty($member_commiss_order_list))
		{
		   foreach($member_commiss_order_list as $member_commiss_order)
		   {
			   //分佣订单
			   M('member_commiss_order')->where( array('id' =>$member_commiss_order['id'] ) )->save( array('state' => 1) );
			   M('member_commiss')->where( array('member_id' => $member_commiss_order['member_id']) )->setInc('money',$member_commiss_order['money']); 
			 }
		}
	}
	
	/**
		给上级会员分佣
	**/
	public function ins_member_commiss_order($member_id,$order_id,$store_id,$order_goods_id)
	{
		$member_info = M('member')->where( array('member_id' => $member_id) )->find();
		//一级
		if(intval($member_info['share_id']) > 0)
		{
			$order_goods = M('order_goods')->where( array('order_goods_id' => $order_goods_id) )->find();
			if(!empty($order_goods))
			{
				$commiss_one_money = $order_goods['commiss_one_money'];
				if($commiss_one_money > 0)
				{
					$data = array();
					$data['member_id'] = $member_info['share_id'];
					$data['child_member_id'] = $member_id;
					$data['order_id'] = $order_id;
					$data['order_goods_id'] = $order_goods_id;
					$data['store_id'] = $store_id;
					$data['state'] = 0;
					$data['level'] = 1;
					$data['money'] = $commiss_one_money;
					$data['addtime'] = time();
					M('member_commiss_order')->add($data);
					
					$share_member = M('member')->field('we_openid,openid')->where( array('member_id' => $member_info['share_id']) )->find();
			
					$member_formid_info = M('member_formid')->where( array('member_id' => $member_info['share_id'],'formid' => array('neq',''), 'state' => 0) )->order('id desc')->find();
					//更新
					/**
					{{first.DATA}}
					商品名称：{{keyword1.DATA}}
					商品佣金：{{keyword2.DATA}}
					订单状态：{{keyword3.DATA}}
					{{remark.DATA}}
					点击了解更多佣金详情
					**/
					$wx_template_data = array();
					$wx_template_data['first'] = array('value' => '1级会员:'.$member_info['uname'].'购买', 'color' => '#030303');
					$wx_template_data['keyword1'] = array('value' => $order_goods['name'], 'color' => '#030303');
					$wx_template_data['keyword2'] = array('value' => round($commiss_one_money,2).'元', 'color' => '#030303');
					$wx_template_data['keyword3'] = array('value' => '支付成功', 'color' => '#030303');
					$wx_template_data['remark'] = array('value' => '点击了解更多佣金详情', 'color' => '#030303');
					
					if(!empty($share_member['openid']))
					{
						$url = C('SITE_URL')."index.php?s=/tuanbonus/groupleaderindex.html";
						send_template_msg($wx_template_data,$url,$share_member['openid'],C('weixin_neworder_commiss'));
					}
					
					if(!empty($member_formid_info))
					{
						\Think\Log::record('测试日志信息,ininin');
						$template_data['keyword1'] = array('value' => 'FX'.$order_id, 'color' => '#030303');
						$template_data['keyword2'] = array('value' => $order_goods['name'], 'color' => '#030303');
						$template_data['keyword3'] = array('value' => round($order_goods['total'],2).'元', 'color' => '#030303');
						$template_data['keyword4'] = array('value' => '1级会员购买，佣金'.$commiss_one_money.'元', 'color' => '#030303');
						
						$pay_order_msg_info =  M('config')->where( array('name' => 'weprog_neworder_commiss') )->find();
						$template_id = $pay_order_msg_info['value'];
						$url =C('SITE_URL');
						$pagepath = 'pages/dan/me';
						send_wxtemplate_msg($template_data,$url,$pagepath,$share_member['we_openid'],$template_id,$member_formid_info['formid']);
						M('member_formid')->where( array('id' => $member_formid_info['id']) )->save( array('state' => 1) );
					}
					
				}
			}
			//二级
			$member_info = M('member')->where( array('member_id' => $member_info['share_id']) )->find();
			if(intval($member_info['share_id']) > 0)
			{
				$commiss_two_money = $order_goods['commiss_two_money'];
				if($commiss_two_money > 0)
				{
					$data = array();
					$data['member_id'] = $member_info['share_id'];
					$data['child_member_id'] = $member_id;
					$data['order_id'] = $order_id;
					$data['order_goods_id'] = $order_goods_id;
					$data['store_id'] = $store_id;
					$data['state'] = 0;
					$data['level'] = 2;
					$data['money'] = $commiss_two_money;
					$data['addtime'] = time();
					M('member_commiss_order')->add($data);
					//TODO 发送模板消息2级下级购买，佣金多少
					
					$share_member = M('member')->field('we_openid,openid')->where( array('member_id' => $member_info['share_id']) )->find();
					
					$wx_template_data = array();
					$wx_template_data['first'] = array('value' => '2级会员购买', 'color' => '#030303');
					$wx_template_data['keyword1'] = array('value' => $order_goods['name'], 'color' => '#030303');
					$wx_template_data['keyword2'] = array('value' => round($commiss_two_money,2).'元', 'color' => '#030303');
					$wx_template_data['keyword3'] = array('value' => '支付成功', 'color' => '#030303');
					$wx_template_data['remark'] = array('value' => '点击了解更多佣金详情', 'color' => '#030303');
					
					if(!empty($share_member['openid']))
					{
						$url = C('SITE_URL')."index.php?s=/tuanbonus/groupleaderindex.html";
						send_template_msg($wx_template_data,$url,$share_member['openid'],C('weixin_neworder_commiss'));
					}
					
					
					$member_formid_info = M('member_formid')->where( array('member_id' => $member_info['share_id'],'formid' => array('neq',''), 'state' => 0) )->order('id desc')->find();
					//更新
					if(!empty($member_formid_info))
					{
						$template_data['keyword1'] = array('value' => 'FX'.$order_id, 'color' => '#030303');
						$template_data['keyword2'] = array('value' => $order_goods['name'], 'color' => '#030303');
						$template_data['keyword3'] = array('value' => round($order_goods['total'],2).'元', 'color' => '#030303');
						$template_data['keyword4'] = array('value' => '2级会员购买，佣金'.$commiss_two_money.'元', 'color' => '#030303');
						
						$pay_order_msg_info =  M('config')->where( array('name' => 'weprog_neworder_commiss') )->find();
						$template_id = $pay_order_msg_info['value'];
						$url =C('SITE_URL');
						$pagepath = 'pages/dan/me';
						send_wxtemplate_msg($template_data,$url,$pagepath,$share_member['we_openid'],$template_id,$member_formid_info['formid']);
						M('member_formid')->where( array('id' => $member_formid_info['id']) )->save( array('state' => 1) );
					}
				}
				//三级
				$member_info = M('member')->where( array('member_id' => $member_info['share_id']) )->find();
				if(intval($member_info['share_id']) > 0)
				{
					$commiss_three_money = $order_goods['commiss_three_money'];
					if($commiss_three_money > 0)
					{
						$data = array();
						$data['member_id'] = $member_info['share_id'];
						$data['child_member_id'] = $member_id;
						$data['order_id'] = $order_id;
						$data['order_goods_id'] = $order_goods_id;
						$data['store_id'] = $store_id;
						$data['state'] = 0;
						$data['level'] = 3;
						$data['money'] = $commiss_three_money;
						$data['addtime'] = time();
						M('member_commiss_order')->add($data);
						//TODO 发送模板消息3级下级购买，佣金多少
						$share_member = M('member')->field('we_openid,openid')->where( array('member_id' => $member_info['share_id']) )->find();
						
						
						$wx_template_data = array();
						$wx_template_data['first'] = array('value' => '3级会员购买', 'color' => '#030303');
						$wx_template_data['keyword1'] = array('value' => $order_goods['name'], 'color' => '#030303');
						$wx_template_data['keyword2'] = array('value' => round($commiss_three_money,2).'元', 'color' => '#030303');
						$wx_template_data['keyword3'] = array('value' => '支付成功', 'color' => '#030303');
						$wx_template_data['remark'] = array('value' => '点击了解更多佣金详情', 'color' => '#030303');
						
						if(!empty($share_member['openid']))
						{
							$url = C('SITE_URL')."index.php?s=/tuanbonus/groupleaderindex.html";
							send_template_msg($wx_template_data,$url,$share_member['openid'],C('weixin_neworder_commiss'));
						}
					
						$member_formid_info = M('member_formid')->where( array('member_id' => $member_info['share_id'],'formid' => array('neq',''), 'state' => 0) )->order('id desc')->find();
						//更新
						if(!empty($member_formid_info))
						{
							$template_data['keyword1'] = array('value' => 'FX'.$order_id, 'color' => '#030303');
							$template_data['keyword2'] = array('value' => $order_goods['name'], 'color' => '#030303');
							$template_data['keyword3'] = array('value' => round($order_goods['total'],2).'元', 'color' => '#030303');
							$template_data['keyword4'] = array('value' => '3级会员购买，佣金'.$commiss_three_money.'元', 'color' => '#030303');
							
							$pay_order_msg_info =  M('config')->where( array('name' => 'weprog_neworder_commiss') )->find();
							$template_id = $pay_order_msg_info['value'];
							$url =C('SITE_URL');
							$pagepath = 'pages/dan/me';
							send_wxtemplate_msg($template_data,$url,$pagepath,$share_member['we_openid'],$template_id,$member_formid_info['formid']);
							M('member_formid')->where( array('id' => $member_formid_info['id']) )->save( array('state' => 1) );
						}
					}
				}
				
			}
		}
		
	}
	
	/**
		给上级会员分佣
	**/
	public function ins_member_commiss_order2($member_id,$order_id,$store_id)
	{
		$member_info = M('member')->where( array('member_id' => $member_id) )->find();
		//share_id
		if(intval($member_info['share_id']) > 0)
		{
			$order_goods = M('order_goods')->where( array('order_id' => $order_id) )->find();
			if(!empty($order_goods))
			{
				$commiss_one_money = $order_goods['commiss_one_money'];
				if($commiss_one_money > 0)
				{
					$data = array();
					$data['member_id'] = $member_info['share_id'];
					$data['child_member_id'] = $member_id;
					$data['order_id'] = $order_id;
					$data['store_id'] = $store_id;
					$data['state'] = 0;
					$data['money'] = $commiss_one_money;
					$data['addtime'] = time();
					M('member_commiss_order')->add($data);
				}
			}
		}
		
	}
	
	/**
		更新普通会员为分销商
	**/
	public function updateCommissUser($member_id = 0)
	{
		$notify_model = D('Home/Weixinnotify');
		
		$member_info = M('member')->where( array('member_id' => $member_id) )->find();
		if($member_info['comsiss_flag'] != 1)
		{
			M('member')->where( array('member_id' => $member_id) )->save( array('comsiss_flag' => 1) );
			//TODO SEND MSG 
			$notify_model->send_super_tuanz_msg($member_info['openid']);
		}
	}
	
	/**
		互为上下级关系
	**/
	public function relation_fenxiao($parent_member_id,$child_member_id)
	{
		$notify_model = D('Home/Weixinnotify');
		//自己不互为上下线
		if($parent_member_id != $child_member_id)
		{
			$parent_info = M('member')->where( array('member_id' => $parent_member_id) )->find();
			$child_info = M('member')->where( array('member_id' => $child_member_id) )->find();
			
			if($parent_info['comsiss_flag'] == 1 && $child_info['share_id'] == 0  && $parent_info['share_id'] != $child_member_id)
			{
				//上级必须是分销商,下级必须无上级
				//开始更新分销商
				M('member')->where( array('member_id' => $child_member_id) )->save( array('share_id' => $parent_member_id) );
				//TODO 去发送消息给分销商
				//send_fenxiao_invitemember($openid,$username) openid
				$notify_model->send_fenxiao_invitemember($parent_info['openid'],$child_info['name']);
			}
			
		}
	}
}