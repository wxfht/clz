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
use Admin\Model\CommentModel;
class CommentController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='系统';
		$this->breadcrumb2='访客留言';
	}
	
	public function index(){
		$model=new CommentModel();   
		
		$data=$model->show_comment_page();		
		
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出	
		/**/
		$this->display();
	}

}
?>