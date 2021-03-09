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

class HaitaoController extends CommonController {
	
    
    protected function _initialize()
    {
        parent::_initialize();
        $this->cur_page = 'haitao';
    }
    
    public function info()
    {
        
        $per_page = 2;
        $page = I('post.page',1);
         
        $offset = ($page - 1) * $per_page;
         
        $subject_id =  I('get.subject_id',0);
        $this->subject_id = $subject_id;
        
        $this->fromindex = I('get.fromindex',0);
         
        
        $subject = M('guobie')->where( array('id' => $subject_id) )->find();
        $this->subject = $subject; 
        
        $sql = 'select    g.goods_id,g.name,g.quantity,g.pinprice,g.price,g.danprice,g.pin_count,g.image,g.fan_image,g.store_id,g.seller_count from  '.C('DB_PREFIX')."guobie as sg , ".C('DB_PREFIX')."goods as g
        where  sg.id ={$subject_id} and g.type='haitao'  and sg.id = g.guobie_id and g.status =1 and g.quantity >0  order by sg.id asc limit {$offset},{$per_page}";
        
        $list = M()->query($sql);
         
        if(!empty($list)) {
            foreach($list as $key => $v){
                
				if(!empty($v['fan_image'])){
					$list[$key]['image']=resize($v['fan_image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
				}else {
					$list[$key]['image']=resize($v['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
				}
				
            }
        }
        $this->list = $list;
        
        $type_template = array();
        $type_template['haitao'] = array('html' => 'haitao_info',
            'fetch_html' => 'Haitao:haitao_ajax_info_fetch');
        
        
        if($page > 1) {
            $result = array('code' => 0);
            if(!empty($list)) {
                $result['code'] = 1;
                $result['html'] = $this->fetch($type_template['haitao']['fetch_html']);
            }
            echo json_encode($result);
            die();
        }
        
        $this->display($type_template['haitao']['html']);
    }
    
	//进行中
	public function index(){
		
	    $haitao_list = M('guobie')->where( array('is_index' => 1) )->order('id asc')->limit(5)->select();
	   
	    $this->haitao_list = $haitao_list;
	    
	    $parent_category = M('goods_category')->where( array('is_haitao' => 1,'pid' => 0) )->order('sort_order asc')->find();
	    
	    $category_list = M('goods_category')->where( array('pid' =>$parent_category['id'],'is_haitao' => 1 ) )->order('sort_order asc')->select();
	    
	    $this->category_list = $category_list;
	    
	    $per_page = 4;
	    $page = I('post.page',1);
	    
	    $offset = ($page - 1) * $per_page;
	    
	   
	    $this->fromindex = I('get.fromindex',0);
	    
        $sql = 'select    g.goods_id,g.name,g.quantity,g.pinprice,g.price,g.danprice,g.pin_count,g.image,g.store_id,g.seller_count from  '.C('DB_PREFIX')."guobie as sg , ".C('DB_PREFIX')."goods as g
        where  g.type='haitao' and  sg.is_index =1 and sg.id = g.guobie_id and g.status =1 and g.quantity >0  order by sg.id asc limit {$offset},{$per_page}";
     
       $list = M()->query($sql);
       $this->list = $list;
	   
	   
	   $type_template = array();
	   $type_template['haitao'] = array('html' => 'haitao_index', 
	       'fetch_html' => 'Haitao:haitao_ajax_fetch');
	   
	   
		if($page > 1) {
		    $result = array('code' => 0);
		    if(!empty($list)) {
		        $result['code'] = 1;
		        $result['html'] = $this->fetch($type_template['haitao']['fetch_html']);
		    }
		    echo json_encode($result);
		    die();
		}
	
		$this->display($type_template['haitao']['html']);	
	}	
	
	
	
}