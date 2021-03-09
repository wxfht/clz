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
namespace Admin\Controller;
use Admin\Model\BlogReplyModel;
class BlogReplyController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='博客';
		$this->breadcrumb2='博客回复';
	}
	
	public function index(){
		$model=new BlogReplyModel();   
		
		$data=$model->show_blog_reply_page();		
		
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出	
		/**/
		$this->display();
	}
	
	function set_status(){
		$d['reply_id']=I('get.id');
		$d['status']=I('get.status');
		$r=M('BlogReply')->save($d);
		if($r){
			$this->redirect('BlogReply/index');
		}
	}
	
}
?>