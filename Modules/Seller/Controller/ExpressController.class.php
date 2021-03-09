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
use Seller\Model\ExpressModel;
class ExpressController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='发货设置';
			$this->breadcrumb2='快递管理';
	}
	
     public function index(){
     	
		$model=new ExpressModel();   
		
		$search = array();
		$search['store_id']  = array('in','0,'.SELLERUID);
		
		$data=$model->show_express_page($search);	
		$seller_express_relat = M('seller_express_relat')->where( array('store_id' => SELLERUID) )->select();
		
		$express_ids = array();
		foreach($seller_express_relat as $express)
		{
		    $express_ids[] = $express['express_id'];
		}
		
		foreach($data['list'] as $key => $val)
		{
		    $val['is_selected'] = 0;
		    if(!empty($express_ids) && in_array($val['id'], $express_ids)) 
		    {
		        $val['is_selected'] = 1;
		    }
		    $data['list'][$key] = $val;
		}
		$this->assign('seller_id',SELLERUID);
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出	
		
    	$this->display();
	 }
    
	 function toggle_express_show()
	 {
	     $eid = intval(I('post.eid'));
	     $rel_ex = M('seller_express_relat')->where( array('store_id' => SELLERUID, 'express_id' => $eid) )->find();
	     if(empty($rel_ex)) 
	     {
	         $data = array();
	         $data['express_id'] = $eid;
	         $data['store_id'] = SELLERUID;
	         M('seller_express_relat')->add($data);
	     } else {
	         M('seller_express_relat')->where( array('store_id' => SELLERUID, 'express_id' => $eid) )->delete();
	     }
	     
	     echo json_encode( array('code' => 1) );
	     die();
	 }
	 
	function add(){
		
		if(IS_POST){
			
			$data=I('post.');
			$data['store_id'] = SELLERUID;
			$data['addtime'] = time();
			
			
			if( empty($data['express_name']) )
			{
				$return = array(
					'status'=>'fail',
					'message'=>'请填写快递名称',
					'jump'=>U('Express/index')
        		);
			}else{
				$res = M('seller_express')->add($data);	
			
				if($res) {
				   $return = array(
						'status'=>'success',
						'message'=>'新增成功',
						'jump'=>U('Express/index')
					);
				} else {
					$return = array(
						'status'=>'fail',
						'message'=>'新增失败',
						'jump'=>U('Express/index')
					);
				}
				
			}
			
			
			$this->osc_alert($return);
		}
		
		$this->crumbs='新增';		
		$this->action=U('Express/add');
		$this->display('edit');
	}

	function edit(){
		if(IS_POST){
		    
		    $data=I('post.');
			
			$data['addtime'] = time();
			
			$ck_info = M('seller_express')->where(array('id' =>$data['id'],'store_id' =>SELLERUID))->find();
			if(empty($ck_info)) {
				$return = array(
        			        'status'=>'fail',
        			        'message'=>'非法操作',
        			        'jump'=>U('Express/index')
        			    );
				$this->osc_alert($return);
			}
			$res = M('seller_express')->save($data);	
			
			if($res) {
			   $return = array(
        			        'status'=>'success',
        			        'message'=>'编辑成功',
        			        'jump'=>U('Express/index')
        			     );
			} else {
			    $return = array(
        			        'status'=>'fail',
        			        'message'=>'编辑失败',
        			        'jump'=>U('Express/index')
        			    );
			}		
			$this->osc_alert($return);
		}
		$this->crumbs='编辑';		
		$this->action=U('Express/edit');
		$this->d=M('seller_express')->find(I('id'));
		$this->display('edit');		
	}

	 public function del(){
	     
	    $id = I('get.id', 0);
	    $res = M('seller_express')->where( array('id' => $id) )->delete();
	     
	    if($res) {
	        $return = array(
	            'status'=>'success',
	            'message'=>'删除成功',
	            'jump'=>U('Express/index')
	        );
	    } else {
	        $return = array(
	            'status'=>'fail',
	            'message'=>'删除失败',
	            'jump'=>U('Express/index')
	        );
	    }		
		$this->osc_alert($return); 	
	 }
	 
	 public function config()
	{
		
	    $_GPC = I('request.');
	    $this->gpc = $_GPC;
	   
	    $condition = '';
	    $pindex = max(1, intval($_GPC['page']));
	    $psize = 20;
	    
	   
	    
	    if (!empty($_GPC['keyword'])) {
	        $condition .= ' and name like "%'.$_GPC['keyword'].'%" ';
	    }
	    
	    $label = M()->query('SELECT id,name,simplecode FROM ' . C('DB_PREFIX') . "lionfish_comshop_express  
						WHERE 1 " . $condition . ' order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);
	    
		
		$total = M('lionfish_comshop_express')->where('1 ' . $condition)->count();
		
	    
	    $pager = pagination2($total, $pindex, $psize);
		
		
		$this->label = $label;
		$this->total = $total;
		$this->pager = $pager;
	    
		$this->display();
	}
	
	public function addexpress()
	{
		$_GPC = I('request.');
		
		if (IS_POST) {
			
			$data = $_GPC['data'];
			
			D('Seller/Express')->update($data);
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		
		$this->display();
	}
	
	public function editexpress()
	{
		$_GPC = I('request.');
		
		
		$id = intval($_GPC['id']);
		if (!empty($id)) {
			$item = M('lionfish_comshop_express')->field('id,name,simplecode')->where( array('id' => $id) )->find();
			
			$this->item = $item;
		}	
		
		if (IS_POST) {
			
			$data = $_GPC['data'];
			
			D('Seller/Express')->update($data);
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		
		$this->display('Express/addexpress');
	}
	
	public function delexpress()
	{
		$_GPC = I('request.');
		
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}
		
		$items = M('lionfish_comshop_express')->field('id,name')->where('id in( ' . $id . ' ) ')->select();			

		if (empty($item)) {
			$item = array();
		}

		foreach ($items as $item) {
			M('lionfish_comshop_express')->where( array('id' => $item['id']) )->delete();
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	
	
	public function deconfig()
	{
		$_GPC = I('request.');
		
		if (IS_POST) {
			$data = ((is_array($_GPC['parameter']) ? $_GPC['parameter'] : array()));
			
			$data['delivery_type_ziti'] = trim($data['delivery_type_ziti']);
			$data['delivery_type_express'] = $data['delivery_type_express'];
			
			$data['delivery_type_tuanz'] = $data['delivery_type_tuanz'];
			$data['delivery_tuanz_money'] = $data['delivery_tuanz_money'];
			$data['delivery_express_name'] = $data['delivery_express_name'];
			$data['delivery_diy_sort'] = $data['delivery_diy_sort'];
			
			
			D('Seller/Config')->update($data);
			
			if(empty($data['delivery_diy_sort']) || !isset($data['delivery_diy_sort'])) 
				$data['delivery_diy_sort'] = '0,1,2';
			
			$data['delivery_diy_sort_arr'] = explode(",", $data['delivery_diy_sort']);
		
		
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		
		$data = D('Seller/Config')->get_all_config();
		
		if(empty($data['delivery_diy_sort']) || !isset($data['delivery_diy_sort'])) $data['delivery_diy_sort'] = '0,1,2';
		$data['delivery_diy_sort_arr'] = explode(",", $data['delivery_diy_sort']);
		
		$this->data = $data;
		$this->display();
	}
	
	
	
	 public function config2()
	 {
		 $open_info = M('config')->where( array('name' => 'EXPRESS_OPEN') )->find();
		 $ebuss_info = M('config')->where( array('name' => 'EXPRESS_EBUSS_ID') )->find();
		 $exappkey = M('config')->where( array('name' => 'EXPRESS_APPKEY') )->find();
		 
		$is_open = $open_info['value'];
		$ebuss_id = $ebuss_info['value'];
		$express_appkey = $exappkey['value'];
		 
		$this->is_open = $is_open;
		$this->ebuss_id = $ebuss_id;
		$this->express_appkey = $express_appkey;
		
		 $this->type = 1;
		 $this->display();
	 }
	 function configadd()
	 {
		 $data = I('post.');
		 /**
		 array(4) { ["is_open"]=> string(1) "1" ["ebuss_id"]=> string(7) "1276098" ["express_appkey"]=> string(36) "9933541f-2d17-4312-8250-a9cecdbe633d" ["send"]=> string(6) "提交" }
		 **/
		 M('config')->where( array('name' => 'EXPRESS_OPEN') )->save( array('value' => $data['is_open']) );
		 M('config')->where( array('name' => 'EXPRESS_EBUSS_ID') )->save( array('value' => $data['ebuss_id']) );
		 M('config')->where( array('name' => 'EXPRESS_APPKEY') )->save( array('value' => $data['express_appkey']) );
		 $return = array(
        			        'status'=>'success',
        			        'message'=>'保存成功',
        			        'jump'=>U('Express/config')
        			     );
		$this->osc_alert($return); 
	 }
	 
}
?>