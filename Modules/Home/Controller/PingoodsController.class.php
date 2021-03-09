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
namespace Home\Controller;

class PingoodsController extends CommonController {
    protected function _initialize()
    {
    	parent::_initialize();
        $this->cur_page = 'index';
    }
	
	
    public function index(){
		//首页关键词
		$index_searchkeywords = C('index_searchkeywords');
		$keywords_arr = explode(',', $index_searchkeywords);
		
		$this->keywords_arr = $keywords_arr;
		
		$type = I('get.type', 'all');
		$this->type = $type;
		//all newman lottery
		
		//首页轮播导航
		
		//首页轮播导航
		
		$slider_nav_list=M('plugins_slider')->where( array('type' => 'pin_index_ad') )->order('sort_order desc')->find();
		if( !empty($slider_nav_list) )
		{
			$slider_nav_list['image'] = C('SITE_URL') . 'Uploads/image/'.$slider_nav_list['image'];
		}
		$this->slider_nav =$slider_nav_list;
		
		//1分拼团广告
		$slider_newman_list=M('plugins_slider')->where( array('type' => 'newman_wepro_head') )->order('sort_order desc')->find();
		if( !empty($slider_newman_list) )
		{
			$slider_newman_list['image'] = C('SITE_URL') . 'Uploads/image/'.$slider_newman_list['image'];
		}
		$this->slider_newman =$slider_newman_list;
		
		//抽奖广告 
		$slider_lottery_list=M('plugins_slider')->where( array('type' => 'lottery_wepro_head') )->order('sort_order desc')->find();
		if( !empty($slider_lottery_list) )
		{
			$slider_lottery_list['image'] = C('SITE_URL') . 'Uploads/image/'.$slider_lottery_list['image'];
		}
		$this->slider_lottery =$slider_lottery_list;
		
		
		$appid_info =  M('config')->where( array('name' => 'APPID') )->find();
		$appsecret_info =  M('config')->where( array('name' => 'APPSECRET') )->find();
	   
		$weixin_config = array();
		$weixin_config['appid'] = $appid_info['value'];
		$weixin_config['appscert'] = $appsecret_info['value'];
		
		$jssdk = new \Lib\Weixin\Jssdk( $weixin_config['appid'], $weixin_config['appscert']);
		
		
		$uid = is_login();
		
		$member_info = M('member')->where( array('member_id' => $uid) )->find();
		
		
		$sub_url = C('SHORT_URL');
		$site_title = C('SITE_TITLE');
		$site_name = C('SITE_NAME');
		$site_logo = C('SITE_ICON');
		
		$this->is_sub = $is_sub;
		$this->sub_url = $sub_url;
		$this->site_title = $site_title;
		$this->site_name = $site_name;
		$this->site_logo = $site_logo;
		
		
	   $this->display();
    }
	
	public function pinlist()
	{
		$type = I('get.type','0');
	    $this->type = $type;
	    $page = I('post.page',1);
	    $pre_page = 10;
	    $offset = ($page -1) * $pre_page;
	     
	    $where = ' ';
	     
	    if($type == 1)
	    {
	        $where .= ' and p.state = 0 and p.end_time >'.time();
	    } else if($type == 2){
	        $where .= ' and p.state = 1 ';
	    } else if($type == 3){
	        $where .= ' and (p.state = 2 or  (p.state =0 and p.end_time <'.time().')) ';
	    }
	    $hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
	    
		//og
	    $sql = "select og.name,og.goods_images,p.need_count,p.state,p.is_lottery,p.lottery_state,p.end_time,o.order_id,og.price,po.pin_id,o.order_status_id from ".C('DB_PREFIX')."order as o, ".C('DB_PREFIX')."order_goods as og, 
	        ".C('DB_PREFIX')."pin as p,".C('DB_PREFIX')."goods as g ,".C('DB_PREFIX')."pin_order as po   
	            where  po.order_id = o.order_id and  o.order_id = og.order_id and og.goods_id = g.goods_id and po.pin_id = p.pin_id 
	            and o.member_id = ".is_login()." and o.order_status_id !=3   {$where} order by o.date_added desc limit {$offset},{$pre_page}";
	  
	    $list = M()->query($sql);
	    
	    $hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
	    
	    
	    foreach($list as $key => $val)
	    {
	        $val['price'] = round($val['price'],2);
			
			//if(!empty($val['fan_image'])){
			//	$val['image']=resize($val['fan_image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
			//}else {
				$val['image']=resize($val['goods_images'], C('common_image_thumb_width'), C('common_image_thumb_height'));
			//}
			
	        $val['hash_order_id'] = $hashids->encode($val['order_id']);
	        
	        if($val['state'] == 0 && $val['end_time'] < time())
	        {
	            $val['state'] = 2;
	        }
	        $list[$key] = $val;
	    } //order_status_id
	    $this->list = $list;
	    
	    if($page > 1) {
	        $result = array('code' => 0);
	        if(!empty($list)) {
	            $result['code'] = 1;
	            $result['html'] = $this->fetch("Group:pin_ajax_fetch");
	        }
	        echo json_encode($result);
	        die();
	    }
		$this->display();
	}
	
	
}