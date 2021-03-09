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
use Seller\Model\FissionsharingModel;
use Admin\Model\GoodsModel;
use Admin\Model\MemberModel;
class FissionsharingController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='营销活动';
		$this->breadcrumb2='裂变分享';
	}
	
	public function index(){
	    
		$model=new FissionsharingModel();
	   
		$filter=I('get.');
		
		$search=array( );
		
		if(isset($filter['name'])){
			$search['name']=$filter['name'];		
		}
		 
		$data=$model->show_fission_page($search);
	   
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出
		
		$this->display();
	}
	
	public function config()
	{
		if(IS_POST){
			$config=I('post.');					
			
			if($config && is_array($config)){
				$c=M('Config');    
	            foreach ($config as $name => $value) {
	                $map = array('name' => $name);
					$c->where($map)->setField('value', $value);					
	            }
				
	        }
	       $return = array(
				'status'=>'success',
				'message'=>'操作成功',
				'jump'=>U('Fissionsharing/config')
			);
		
		    $this->osc_alert($return);
		}
		$this->site=$this->get_config_by_group('site');
		$this->display();
	}
	
	function get_config_by_group($group){
		
		$list=M('config')->where(array('config_group'=>$group))->select();
		if(isset($list)){
			foreach ($list as $k => $v) {
				$config[$v['name']]=$v;
			}
		}
		return $config;
	}
	
	/**
		分销提现申请
	 **/
	 public function commissapply()
	 {
		 
		 $model=new MemberModel();   
		
		$filter=I('get.');
		
		$search=array();
		
		if(isset($filter['name'])){
			$search['name']=$filter['name'];		
		}
		
		$data=$model->show_fen_applymembercomiss_page($search);	
		
		foreach($data['list'] as $key => $val)
		{
			$address_info = M('address')->where( array('member_id' => $val['member_id']) )->order('is_default desc')->find();
			
			if(!empty($address_info)) {
				$val['telephone'] = $address_info['telephone'];
			}
			$data['list'][$key] = $val;
		}
		
		
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出	
		$this->display();
	 }
	 
	 /**
		分佣提现申请
	**/
	function commissmoneyapply()
	{
		$aid = I('get.aid',0);
		$id = I('get.id',0);
		$state = I('get.state',0,'intval');
		
		$member_commiss = M('member_sharing')->where( array('member_id' => $id) )->find();
		$tixian_order = M('fen_tixian_order')->where( array('id' => $aid) )->find();
		
		if($state == 1)
		{
			//money dongmoney  getmoney
			$data = array(); 
			$data['getmoney'] = $member_commiss['getmoney'] + $tixian_order['money'];
			$data['dongmoney'] = $member_commiss['dongmoney'] - $tixian_order['money'];
			
			M('member_sharing')->where( array('member_id' => $id) )->save($data);
			
			M('fen_tixian_order')->where( array('id' => $aid) )->save( array('state' => 1,'shentime' => time()) );
			
		} else if($state == 2){
			
			$data = array(); 
			$data['money'] = $member_commiss['money'] + $tixian_order['money'];
			$data['dongmoney'] = $member_commiss['dongmoney'] - $tixian_order['money'];
			M('member_sharing')->where( array('member_id' => $id) )->save($data);
			
			M('fen_tixian_order')->where( array('id' => $aid) )->save( array('state' => 2,'shentime' => time()) );
		}
		
		$return = array();
		$return['status'] = 'success';
		$return['message'] = '操作成功';
		$return['jump']  = U('Fissionsharing/commissapply');
		
		$this->osc_alert($return); 
	}
	
	
}
?>