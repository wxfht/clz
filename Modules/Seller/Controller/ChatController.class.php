<?php
namespace Seller\Controller;
use Admin\Model\StatisticsModel;
class ChatController extends CommonController {
   	protected function _initialize(){
   	    parent::_initialize();
   	    $this->breadcrumb1='首页';
   	    $this->breadcrumb2='首页';
   	}
    public function index(){
	
		$seller_info = M('seller')->field('s_true_name,s_logo')->where( array('s_id' =>SELLERUID) )->find();
		$site_url_info = M('config')->field('value')->where( array('name' =>'SITE_URL') )->find();
		
		$kefu_wait_msg = M('config')->field('value')->where( array('name' =>'kefu_wait_msg') )->find();
		
		$tfhours_new_sg = M('config')->field('value')->where( array('name' =>'24hours_new_sg') )->find();
		
		$site_url = $site_url_info['value'];
		$seller_info['s_logo'] = $site_url.'/Uploads/image/'.$seller_info['s_logo'];
		
		$this->kefu_wait_msg = $kefu_wait_msg['value'];
		$this->tfhours_new_sg = $tfhours_new_sg['value'];
		
		$this->seller_info = $seller_info;
		
        $this->display();
    }
	
	
}