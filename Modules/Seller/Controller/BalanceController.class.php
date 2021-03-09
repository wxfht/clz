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
use Admin\Model\BalanceModel;
class BalanceController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='结算中心';
			$this->breadcrumb2='结算管理';
	}
	
     public function index(){
     	
		$model=new BalanceModel();   
		$search = 'seller_id='.SELLERUID;
		
		$post_data = I('get.');
		
		if( isset($post_data['begin_time']) && !empty($post_data['begin_time']))
		{
			$search .= ' and  balance_time >= '.strtotime($post_data['begin_time']);
		}
		
		if( isset($post_data['end_time']) && !empty($post_data['end_time']))
		{
			$search .= ' and  balance_time <= '.strtotime($post_data['end_time']);
		}
		
		$this->post_data = $post_data;
		$data=$model->show_balance_page($search);		
		
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出	
		$this->display();
	 }
    /**
     * 个人资产
     */
	 public function assets()
	 {
	     $this->breadcrumb2='资产';
	     $model=new BalanceModel();
	     $search = ' and st.seller_id='.SELLERUID;
	     $data=$model->show_balance_assets_page($search);
	     
	     $this->assign('empty',$data['empty']);// 赋值数据集
	     $this->assign('list',$data['list']);// 赋值数据集
	     $this->assign('page',$data['page']);// 赋值分页输出
	     
	     $seller_balance =  M('seller_balance')->where( array('seller_id' => SELLERUID) )->find();
	     if( empty($seller_balance) ) {
	         $seller_balance = array();
	         $seller_balance['money'] = 0;
	         $seller_balance['hasgetmoney'] = 0;
	         $seller_balance['dongmoney'] = 0;
	     }
	     
	     $wait_balance_money = $model->wait_balance_order(SELLERUID);
	     
	     $this->wait_balance_money = $wait_balance_money;
	     $this->seller_balance = $seller_balance;
	     $this->display();
	 }
	 
	 /**
	  * 申请提现
	  */
	 public function shenqing()
	 {
	     $shenmoney  = I('post.shenmoney'); 
	     
	     $seller_balance =  M('seller_balance')->where( array('seller_id' => SELLERUID) )->find();
	     
	     $result = array('code' => 0,'msg' => '可提现余额不足');
	     
	     if(empty($seller_balance) || $seller_balance['money'] < $shenmoney) {
	         echo json_encode($result);
	         die();
	     }
	     
	     $data = array();
	     $data['seller_id'] = SELLERUID;
	     $data['money'] = $shenmoney;
	     $data['state'] = 0;
	     $data['addtime'] = time();
	     
	     $res = M('seller_tixian')->add($data);
	     
	     if($res) {
	         
	         $se_data = array();
	         $se_data['money'] = $seller_balance['money'] - $shenmoney;
	         $se_data['dongmoney'] = $seller_balance['dongmoney'] + $shenmoney;
	         
	         M('seller_balance')->where( array('id' => $seller_balance['id']) )->save($se_data);
	         $result['code'] = 1;
	     }
	     echo json_encode($result);
	     die();
	 }
	 public function orderlook()
	 {
	     $model=new BalanceModel();
	     $bid = I('get.bid');
	     $data=$model->show_balance_order_page($bid);
	     
	     $this->assign('empty',$data['empty']);// 赋值数据集
	     $this->assign('list',$data['list']);// 赋值数据集
	     $this->assign('page',$data['page']);// 赋值分页输出
	     $this->display();
	 }
	
	 public function suremoney()
	 {
	     $bid =  I('get.bid');
	     M('balance')->where( array('bid' => $bid) )->save( array('state' => 1) );
	     $return = array(
	         'status'=>'success',
	         'message'=>'确认成功',
	         'jump'=>U('Balance/index')
	     );
	     $this->osc_alert($return);
	 }
	 
}
?>