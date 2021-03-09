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
namespace Seller\Model;

class ExpressModel{
	
	public function update($data)
	{
		
		$ins_data = array();
		$ins_data['name'] = $data['name'];
		$ins_data['simplecode'] = $data['simplecode'];
		
		$id = $data['id'];
		if( !empty($id) && $id > 0 )
		{
			M('lionfish_comshop_express')->where( array('id' => $id) )->save( $ins_data );
			
		}else{
			M('lionfish_comshop_express')->add( $ins_data );
			
		}
	}
	
	public function get_express_info($id)
	{
		$info = M('lionfish_comshop_express')->where( array('id' => $id ) )->find();
		
		return $info;
	}
	
	
	public function load_all_express()
	{
		$list = M('lionfish_comshop_express')->field('id, name')->select();
		return $list;
	}
	public function show_express_page($search = array()){
		
	    $where = array();
	    
	    if(!empty($search) && isset($search['store_id'])) {
	        $where['store_id'] = $search['store_id'];
	    }
	    
		$count=M('seller_express')->where($where)->count();
		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出	
		
		$list = M('seller_express')->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		
		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);	
	}
	
}
?>