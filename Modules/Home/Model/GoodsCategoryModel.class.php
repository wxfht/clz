<?php
namespace Home\Model;
use Think\Model;
/**
 * 商品分类模型
 * @author fish
 *
 */
class GoodsCategoryModel {
	
	
	/**
		获取首页的商品分类
	**/
	public function get_index_goods_category($pid = 0 ,$cate_type = 'normal')
	{
		// and pid = {$pid}
		if( empty($pid) )
		{
			$pid = 0;
		}
		
		$cate_list = M('lionfish_comshop_goods_category')->where( array('is_show' => 1,'pid' =>$pid,'cate_type' => $cate_type ) )
			->order('sort_order desc, id desc')->select();
		
			
		$need_data = array();	
			
		foreach($cate_list as $key => $cate)		
		{			
			$need_data[$key]['id'] = $cate['id'];			
			$need_data[$key]['name'] = $cate['name'];
			$need_data[$key]['banner'] = $cate['banner'] && !empty($cate['banner']) ? tomedia($cate['banner']) : '';
			$need_data[$key]['logo'] = $cate['logo'] && !empty($cate['logo']) ? tomedia($cate['logo']) : '';
			$need_data[$key]['sort_order'] = $cate['sort_order'];
			
			$sub_cate = M('lionfish_comshop_goods_category')->field('id,name,sort_order')
						->where( array('pid' => $cate['id'], 'is_show' => 1) )->order('sort_order desc, id desc')->select();
			
			$need_data[$key]['sub'] = $sub_cate;
		}				
		
		
		return $need_data;
	}
	
	/** 
	 * 获取 分类下所有id
	 * @Author: flydreame 
	 * @Date: 2020-06-08 11:40:42 
	 * @Desc:  
	 * category_id array 分类id
	 * arr 数组
	 * type 0.加入传入值  1.不加传入值
	 */
	public function get_goodscategory($category_id,$arr =array(),$type = 0){
	
		foreach ($category_id as $v ){
			
			//获取下一级分类id
			$one_category =M('lionfish_comshop_goods_category')->field('id')->WHERE(['pid'=>$v])->select();
			
			//二维数组转换成一维数组
			$one_category=array_column($one_category,'id'); 
			
			//判断是否最底层分类
			if($one_category){

				//继续查询下级分类
				$arrs=$this->get_goodscategory($one_category,$arr);
				if($arrs){
					$arr=	$arrs;
				}
			}
			if($type == 1){
				if(!$one_category){
					$arr[]=$v;
				}
			}else{
				$arr[]=$v;
			}
			
		}
		return $arr;
		
	}
}