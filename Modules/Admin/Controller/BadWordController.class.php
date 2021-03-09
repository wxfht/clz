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
use Admin\Model\BadWordModel;

class BadWordController extends CommonController{
	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='系统';
			$this->breadcrumb2='违禁词管理';
	}
	
     public function index(){
   
		$model=new BadWordModel();   
		
		$data=$model->show_bad_word_page();		
		
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出	
		
    	$this->display();
	 }

	function add(){
		
		if(IS_POST){
			
			$model=new BadWordModel();  
			$data=I('post.');
			$return=$model->add_bad_word($data);			
			$this->osc_alert($return);
		}
		
		$this->crumbs='新增';		
		$this->action=U('BadWord/add');
		$this->display('edit');
	}

	function edit(){
		if(IS_POST){
			$model=new BadWordModel();  
			$data=I('post.');
			$return=$model->edit_bad_word($data);		
		
			$this->osc_alert($return);
		}
		$this->crumbs='编辑';		
		$this->action=U('BadWord/edit');
		$this->d=M('BadWord')->find(I('id'));
		$this->display('edit');		
	}
	
	public function del(){
		$model=new BadWordModel();  
		$return=$model->del_bad_word();			
		$this->osc_alert($return); 	
	 }

}

?>