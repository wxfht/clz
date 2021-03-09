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

class ArticleController extends CommonController {
	
	
    public function get_article_list()
	{
		$gpc = I('request.');

	
		
		$token = $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		
		$member_id = $weprogram_token['member_id'];
		$member_info =  M('lionfish_comshop_member')->where( array('member_id' => $member_id) )->find();			
		
		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$list = M('lionfish_comshop_article')->where( array('enabled' => 1))->order('displayorder desc')->limit(100)->select();
		
		if( empty($list) )
		{
			echo json_encode(array('code' => 1));
			die();
		}else{
			echo json_encode( array('code' =>0, 'data' => $list) );
			die();
		}

	}

	public function get_article()
	{
		$gpc = I('request.');

		$uniacid = $_W['uniacid'];
		
		$token = $gpc['token'];
		
		
		$id = $gpc['id'];
		
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		$member_id = $weprogram_token['member_id'];
		$member_info =  M('lionfish_comshop_member')->where( array('member_id' => $member_id) )->find();			
		
		
		
		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		
		$list = M('lionfish_comshop_article')->where( array('id' => $id,'enabled' => 1) )->find();
		
		//htmlspecialchars_decode
		$list["content"] = htmlspecialchars_decode($list["content"]);
		
		if( empty($list) )
		{
			echo json_encode(array('code' => 1));
			die();
		}else{
			echo json_encode( array('code' =>0, 'data' => $list) );
			die();
		}

	}
}