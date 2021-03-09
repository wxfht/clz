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
use Think\Controller;
class ZhaoshangController extends Controller {
	
	
	protected function _initialize()
    {
    	$site_logo = M('config')->where( array('name' => 'SITE_ICON') )->find();
		$site_c = M('config')->where( array('name' => 'SITE_NAME') )->find();
		$site_tel = M('config')->where( array('name' => 'site_tel') )->find();
		$site_woketime = M('config')->where( array('name' => 'site_woketime') )->find();
		$site_qqun = M('config')->where( array('name' => 'site_qqun') )->find();
		
		$this->site_c = $site_c;
		$this->site_logo = $site_logo;
		$this->site_tel = $site_tel;
		$this->site_woketime = $site_woketime;
		$this->site_qqun = $site_qqun;
    }
    
	
	public function index()
	{
	    $this->display();
	}
	/**
	 * 个人入驻申请
	 */
	public function iddEntry()
	{
	    $this->display();
	}
	/**
		个人入驻提交
	**/
	public function idd_sub()
	{
		$result = array('code' => 1);
		$data = I('post.');
		session('idd_sub', $data);
		echo json_encode($result);
		die();
	}
	/**
		商家入驻申请
	**/
	public function bus_sub()
	{
		$result = array('code' => 1);
		$data = I('post.');
		
		session('bus_sub', $data);
		echo json_encode($result);
		die();
	}
	
	/**
	个人入驻店铺资料
	**/
	public function idd_qualification()
	{
		$goods_category = M('goods_category')->where( array('pid' => 0) )->select();
        $this->goods_category = $goods_category;
		
		$this->next_url = U('Zhaoshang/idd_quali_sub');
		$this->display('qualification');
	}
	
	/**
		商家入驻店铺资料提交
	**/
	public function bus_quali_sub()
	{
		$data = I('post.');
		$bus_subdata = session('bus_sub');
		
		$result = array('code' => 0);
		
		if(empty($bus_subdata))
		{
			$result['msg'] = '请返回上一步填写资料';
			echo json_encode($result);
			die();
		}
		
		
		 $ck_apply_name = M('apply')->where( array('store_name' => $data['store_name']) )->find();
		 if(!empty($ck_apply_name))
		 {
			$result['msg'] = '该店铺名称已经被申请';
			echo json_encode($result);
			die();
		 }
		 
		 $ck_apply_mobile = M('apply')->where( array('mobile' => $bus_subdata['admin_mobile']) )->find();
		 if(!empty($ck_apply_mobile))
		 {
			$result['msg'] = '该手机号已经申请入驻';
			echo json_encode($result);
			die();
		 }
		 
		$ckname = M('seller')->where( array('s_true_name' =>$data['store_name']) )->find();
		 if(!empty($ckname))
		 {
			$result['msg'] = '该店铺名称已经存在';
			echo json_encode($result);
			die();
		 }
		 $ckmobile = M('seller')->where( array('s_mobile' =>$bus_subdata['admin_mobile']) )->find();
		 if(!empty($ckname))
		 {
			$result['msg'] = '该手机号已经被店铺注册';
			echo json_encode($result);
			die();
		 }
		
		$apply_data = array();
		$apply_data['username'] = htmlspecialchars($bus_subdata['admin_real_name']);
		$apply_data['member_id'] = 0;
		$apply_data['mobile'] = htmlspecialchars($bus_subdata['admin_mobile']);
		$apply_data['email'] = htmlspecialchars($bus_subdata['admin_email']);
		$apply_data['store_name'] = htmlspecialchars($data['store_name']);
		$apply_data['city'] = htmlspecialchars($data['city_name']);
		$apply_data['category_id'] = htmlspecialchars($data['store_category']);
		$apply_data['type'] = 1;
		$apply_data['state'] = 0;
		$apply_data['addtime'] = time();
		$aid = M('apply')->add($apply_data);
		
		$rel_data = array_merge($data, $bus_subdata);
		
		$apply_relship_data = array();
		$apply_relship_data['aid'] = $aid;
		$apply_relship_data['seller_id'] = 0;
		$apply_relship_data['rel_data'] = serialize($rel_data);
		$apply_relship_data['addtime'] = time();
		
		M('apply_relship')->add($apply_relship_data);
		
		$result['code'] =1;
		echo json_encode($result);
		die();
	}
	/**
	个人入驻店铺资料提交	
	**/
	public function idd_quali_sub()
	{
		$data = I('post.');
		$idd_subdata = session('idd_sub');
		
		$result = array('code' => 0);
		
		if(empty($idd_subdata))
		{
			$result['msg'] = '请返回上一步填写资料';
			echo json_encode($result);
			die();
		}
		 
		 $ck_apply_name = M('apply')->where( array('store_name' => $data['store_name']) )->find();
		 if(!empty($ck_apply_name))
		 {
			$result['msg'] = '该店铺名称已经被申请';
			echo json_encode($result);
			die();
		 }
		 
		 $ck_apply_mobile = M('apply')->where( array('mobile' => $idd_subdata['admin_mobile']) )->find();
		 if(!empty($ck_apply_mobile))
		 {
			$result['msg'] = '该手机号已经申请入驻';
			echo json_encode($result);
			die();
		 }
		 
		 $ckname = M('seller')->where( array('s_true_name' =>$data['store_name']) )->find();
		 if(!empty($ckname))
		 {
			$result['msg'] = '该店铺名称已经存在';
			echo json_encode($result);
			die();
		 }
		 $ckmobile = M('seller')->where( array('s_mobile' =>$idd_subdata['admin_mobile']) )->find();
		 if(!empty($ckname))
		 {
			$result['msg'] = '该手机号已经被店铺注册';
			echo json_encode($result);
			die();
		 }
		
		$apply_data = array();
		$apply_data['username'] = htmlspecialchars($idd_subdata['admin_real_name']);
		$apply_data['member_id'] = 0;
		$apply_data['mobile'] = htmlspecialchars($idd_subdata['admin_mobile']);
		$apply_data['email'] = htmlspecialchars($idd_subdata['admin_email']);
		$apply_data['store_name'] = htmlspecialchars($data['store_name']);
		$apply_data['city'] = htmlspecialchars($data['city_name']);
		$apply_data['category_id'] = htmlspecialchars($data['store_category']);
		$apply_data['state'] = 0;
		$apply_data['addtime'] = time();
		$aid = M('apply')->add($apply_data);
		
		$rel_data = array_merge($data, $idd_subdata);
		
		$apply_relship_data = array();
		$apply_relship_data['aid'] = $aid;
		$apply_relship_data['seller_id'] = 0;
		$apply_relship_data['rel_data'] = serialize($rel_data);
		$apply_relship_data['addtime'] = time();
		
		M('apply_relship')->add($apply_relship_data);
		
		$result['code'] =1;
		echo json_encode($result);
		die();
	}
	/**
	申请成功
	**/
	public function resultCheck()
	{
		$this->display();
	}
	
	/**
	商家入驻店铺资料
	**/
	public function bus_qualification()
	{
		$goods_category = M('goods_category')->where( array('pid' => 0) )->select();
        $this->goods_category = $goods_category;
		$this->next_url = U('Zhaoshang/bus_quali_sub');
		$this->display('qualification');
	}
	/**
	 * 商家入驻申请
	 */
	public function busEntry()
	{
	    $this->display();
	}
}
