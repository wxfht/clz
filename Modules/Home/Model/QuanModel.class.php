<?php
namespace Home\Model;
use Think\Model;
/**
 * 圈子模型
 * @author fish
 *
 */
class QuanModel {
	
	public $table = 'pin';
    
	public function getSiteUrl()
	{
	    $config_info = M('config')->where( array('name' => 'SITE_URL') )->find();
	    $url = $config_info['value'];
	    return $url;
	}
	
	
	/**
		检测圈子是否存在
	**/
	public function check_quan($seller_id)
	{
		$group_info = M('group')->where( array('seller_id' => $seller_id) )->find();
		if(empty($group_info))
		{
			$seller_info = M('seller')->field("s_true_name")->where( array('s_id' => $seller_id) )->find();
			//s_true_name 
			$ins_data = array();
			$ins_data['seller_id'] = $seller_id;
			$ins_data['title'] = $seller_info['s_true_name'];
			$ins_data['post_count'] = 0;
			$ins_data['status'] = 1;
			$ins_data['member_count'] = 0;
			$ins_data['create_time'] = time();
			$group_id = M('group')->add($ins_data);
		}else {
			$group_id = $group_info['id'];
		}
		return $group_id;
	}
		
	/**
		用户加入圈子
	**/
	public function member_add_group($member_id, $group_id)
	{
		$group_member_data = array();
		$group_member_data['group_id'] = $group_id;
		$group_member_data['member_id'] = $member_id;
		$group_member_data['status'] = 1;
		$group_member_data['last_view'] = time();
		$group_member_data['position'] = 1;
		$group_member_data['create_time'] = time();
		M('group_member')->add($group_member_data);
	}
	
	/**
		点赞/取消赞
	**/
	public function member_fav_post($member_id,$post_id)
	{
		$post_fav =  M('group_post_fav')->where( array('member_id' => $member_id,'post_id' => $post_id) )->find();
		if(empty($post_fav))
		{
			$fav_data = array();
			$fav_data['member_id'] = $member_id;
			$fav_data['post_id'] = $post_id;
			$fav_data['fav_time'] = time();
			
			$rs = M('group_post_fav')->add($fav_data);
			
			
			if($rs)
			{
				 M('group_post')->where( array('id' => $post_id) )->setInc('fav_count', 1);
			}
			return array('code' => 1,'fav_id' => $rs);
		} else{
			 $fav_info = M('group_post_fav')->where( array('member_id' => $member_id,'post_id' => $post_id) )->find();
			 $fav_id = $fav_info['id'];
			 
			 M('group_post_fav')->where( array('member_id' => $member_id,'post_id' => $post_id) )->delete();
			 M('group_post')->where( array('id' => $post_id) )->setDec('fav_count', 1);
			return array('code' => 2, 'fav_id' => $fav_id);
		}
	}

	/**
		评论
	**/
	public function comment_group_post($post_id,$content,$member_id,$to_member_id)
	{
		//group_lzl_reply
		$reply_data = array();
		$reply_data['post_id'] = $post_id;
		$reply_data['content'] = htmlspecialchars($content);
		$reply_data['member_id'] = $member_id;
		$reply_data['to_member_id'] = $to_member_id;
		$reply_data['status'] = 1;
		$reply_data['create_time'] = time();
		$rs = M('group_lzl_reply')->add($reply_data);
		
		if($rs)
		{
			M('group_post')->where( array('id' => $post_id) )->setInc('reply_count', 1);
			M('group_post')->where( array('id' => $post_id) )->save( array('last_reply_time' => time()) );
		}
		return $rs;
	}
	
	/**
		发布
	**/
	public function send_group_post($data)
	{
		//group_post
		$ins_data = array();
		$ins_data['member_id'] = $data['member_id'];
		$ins_data['group_id'] = $data['group_id'];
		$ins_data['goods_id'] = $data['goods_id'];
		$ins_data['title'] = $data['title'];
		$ins_data['content'] = $data['content'];
		$ins_data['link'] = isset($data['link']) ? $data['link'] : '' ;
		$ins_data['is_share'] = isset($data['is_share']) ? $data['is_share'] : 0;
		$ins_data['status'] = 1;
		$ins_data['is_vir'] = isset($data['is_vir']) ? $data['is_vir'] : 0;
		$ins_data['avatar'] = isset($data['avatar']) ? $data['avatar'] : '';
		$ins_data['user_name'] = isset($data['user_name']) ? $data['user_name'] : '';				
		$ins_data['last_reply_time'] = time();
		$ins_data['fav_count'] = 0;
		$ins_data['reply_count'] = 0;
		$ins_data['create_time'] = time();
		$res = M('group_post')->add($ins_data);
		return $res;
	}
	
	/**
		加载圈子内容
	**/
	public function load_group_post($group_id,$post_id,$up_down,$limit=10)
	{
		//up_down  1 底部加载， 2顶部加载。
		$where = " and p.group_id = {$group_id} ";
		$order_by = "";
		
		if($up_down == 1)
		{
			if($post_id>0)
			{	
				$where .= " and p.id <{$post_id} ";
			}
			$order_by = " p.id desc ";
		}else {
			$where .= " and p.id >{$post_id} ";
			$order_by = " p.id desc ";
		}
		$sql = "select p.*,m.uname,m.avatar as avatar2 from ".C('DB_PREFIX')."group_post p,".C('DB_PREFIX')."member as m 
				where p.member_id = m.member_id {$where} order by {$order_by} limit {$limit} ";
				
				
		$list = M()->query($sql);
		
		//htmlspecialchars_decode
		foreach($list as $key => $val)
		{
			$has_fav = M('group_post_fav')->where( array('post_id' => $val['id'],'member_id' => is_login()) )->find();							if( $val['is_vir']  == 1)			{				//,m.uname,m.avatar				$val['uname'] = $val['user_name'];							}else{				$val['avatar'] = $val['avatar2'];			}
			$val['has_fav'] = empty($has_fav) ? false : true;
			$val['title'] = htmlspecialchars_decode($val['title']);						$val['content'] = unserialize($val['content']);						$image_list =  $val['content'];			foreach($image_list as $kkk => $vvv)			{				$vvv =  C('SITE_URL').'Uploads/image/'.$vvv;				$image_list[$kkk] = $vvv;			}			$val['content'] = $image_list;
			$val['fav_list'] = $this->get_post_fav_info($val['id']);
			//获取评论信息
			$val['comment_list'] = $this->get_post_comment_info($val['id']);
			if($val['goods_id'] != 0){
				$val['goods_info'] = $this->get_goods_info(array('goods_id'=>$val['goods_id']));
				$val['goods_info']['buy_user'] = $this->get_goods_pin_avatar($val['goods_id'],5);
			}
			$val['last_reply_date'] = date('Y-m-d H:i:s', $val['last_reply_time']);
			
			$list[$key] = $val;
		}
		
		return $list;
	}
	
	public function get_goods_pin_avatar($goods_id,$limit =5)
	{
		$sql = "select distinct(m.member_id), m.avatar from ".C('DB_PREFIX')."order_goods as og ,".C('DB_PREFIX')."order as o,".C('DB_PREFIX')."member as m 
		 where og.order_id=o.order_id and o.member_id=m.member_id  and og.goods_id={$goods_id} order by o.order_id desc limit {$limit}";

		$avatar_list = M()->query($sql);
		
		if( empty($avatar_list) || count($avatar_list) < $limit )
		{
			$del = $limit - count($avatar_list);
			//look for jiaorder 
			$list = M('jiauser')->order(' rand() ')->limit($del)->select();
			
			foreach($list as $val)
			{
				$tmp = array();
				$tmp['avatar'] = $val['avatar'];
				$avatar_list[] = $tmp;
			}
			
		}
		
		return $avatar_list;
	}
	
	/**
		获取评论信息
	**/
	public function get_post_comment_info($post_id)
	{
		//group_lzl_reply
		
		$sql = "select p.*,m.uname,m.avatar from ".C('DB_PREFIX')."group_lzl_reply p,".C('DB_PREFIX')."member as m 
				where p.member_id = m.member_id and p.post_id = {$post_id} order by id asc ";
		$list = M()->query($sql);
		
		foreach($list as $key => $val)
		{
			$val['content'] = htmlspecialchars_decode($val['content']);
			//to_member_id
			if($val['to_member_id'] >0)
			{
				$to_member_info = M('member')->field('uname')->where( array('member_id' => $val['to_member_id']) )->find();
				$val['to_member_name'] = $to_member_info['uname'];
			}
			$list[$key] = $val;
		}
		return $list;	
	}
	
	/**
		获取点赞信息
	**/
	public function get_post_fav_info($post_id)
	{
		$sql = "select p.*,m.uname,m.avatar from ".C('DB_PREFIX')."group_post_fav p,".C('DB_PREFIX')."member as m 
				where p.member_id = m.member_id and p.post_id = {$post_id}  order by id asc ";
		$list = M()->query($sql);
		return $list;
	}
	
	/**
		获取圈子信息
	**/
	public function get_quan_info($where = array())
	{
		$group_info = M('group')->where( $where )->find();
		return $group_info;
	}
	
	/**
		获取圈子信息
	**/
	public function get_goods_info($where = array())
	{
		$goods_info = M('goods')->field('seller_count,virtual_count,goods_id,name,image,fan_image')->where( $where )->find();
		
		$goods_model = D('Home/goods');
		
		$price_arr = $goods_model->get_goods_price($goods_info['goods_id']);
		
		$goods_info['pin_price'] = $price_arr['pin_price'];
		$goods_info['danprice'] = $price_arr['danprice'];
		$goods_info['price'] = $price_arr['price'];
		$goods_info['seller_count'] += $goods_info['virtual_count'];
		
		unset($goods_info['virtual_count']);
		
		//pinprice
		
		$goods_info['image']= C('SITE_URL'). resize($goods_info['image'], 80, 80);
		
		if(!empty($goods_info['fan_image'])){
			$goods_info['image']= C('SITE_URL'). resize($goods_info['fan_image'], 80, 80);
		}
		return $goods_info;
	}
	
	/**
		加载圈子内容
	**/
	public function load_dynamic_post($goods_id,$post_id,$up_down,$limit=10,$is_goods_info=1)
	{
		//up_down  1 底部加载， 2顶部加载。
		$where = " and p.goods_id = {$goods_id} ";
		$order_by = "";
		
		if($up_down == 1)
		{
			if($post_id>0)
			{	
				$where .= " and p.id <{$post_id} ";
			}
			$order_by = " p.id desc ";
		}else {
			$where .= " and p.id >{$post_id} ";
			$order_by = " p.id desc ";
		}
		$sql = "select p.*,m.uname,m.avatar from ".C('DB_PREFIX')."group_post p,".C('DB_PREFIX')."member as m 
				where p.member_id = m.member_id {$where} order by {$order_by} limit {$limit} ";
				
				
		$list = M()->query($sql);
		
		//htmlspecialchars_decode
		foreach($list as $key => $val)
		{
			$has_fav = M('group_post_fav')->where( array('post_id' => $val['id'],'member_id' => is_login()) )->find();
			
			$val['has_fav'] = empty($has_fav) ? false : true;
			$val['title'] = htmlspecialchars_decode($val['title']);
			$val['fav_list'] = $this->get_post_fav_info($val['id']);
			if($is_goods_info==1){
				//获取评论信息
				$val['comment_list'] = $this->get_post_comment_info($val['id']);
				if($val['goods_id'] != 0){
					$val['goods_info'] = $this->get_goods_info(array('goods_id'=>$val['goods_id']));
				}
			}
			$list[$key] = $val;
		}
		
		return $list;
	}

	/**
		加载圈子内容
	**/
	public function load_dynamic_view($id,$post_id)
	{
		$where = " and p.id = {$id} ";
		$order_by = "";
		$sql = "select p.*,m.uname,m.avatar from ".C('DB_PREFIX')."group_post p,".C('DB_PREFIX')."member as m 
				where p.member_id = m.member_id {$where} limit 1";
				
				
		$list = M()->query($sql);
		
		//htmlspecialchars_decode
		foreach($list as $key => $val)
		{
			$has_fav = M('group_post_fav')->where( array('post_id' => $val['id'],'member_id' => is_login()) )->find();
			
			$val['has_fav'] = empty($has_fav) ? false : true;
			$val['title'] = htmlspecialchars_decode($val['title']);
			$val['fav_list'] = $this->get_post_fav_info($val['id']);
			//获取评论信息
			$val['comment_list'] = $this->get_post_comment_info($val['id']);
			if($val['goods_id'] != 0){
				$val['goods_info'] = $this->get_goods_info(array('goods_id'=>$val['goods_id']));
			}
			$list[$key] = $val;
		}
		
		return $list;
	}
	
}