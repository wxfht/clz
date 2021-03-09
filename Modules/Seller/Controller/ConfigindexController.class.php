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

class ConfigindexController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
		
		//'pinjie' => '拼团介绍',
	}
	
	 public function navigat()
    {
		$_GPC = I('request.');
      
        $pindex    = max(1, intval($_GPC['page']));
        $psize     = 20;

		$condition = "";
		
        if (!empty($_GPC['keyword'])) {
            $condition .= ' and navname like "%'.trim($_GPC['keyword']).'%"';
        }

        if (isset($_GPC['enabled']) && $_GPC['enabled'] >= 0) {
          
            $condition .= ' and enabled = ' . $_GPC['enabled'];
        } else {
            $_GPC['enabled'] = -1;
        }

        $list = M()->query('SELECT * FROM ' . C('DB_PREFIX'). "lionfish_comshop_navigat 
				WHERE 1   " . $condition . ' order by displayorder desc, id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);
       
		$total = M('lionfish_comshop_navigat')->where('1 '. $condition )->count();		

        $pager = pagination2($total, $pindex, $psize);

		
		$this->gpc = $_GPC;
		
		$this->list = $list;
        $this->display();
    }
	public function slider()
    {
        
        $condition = '  type="slider" ';
        $pindex    = I('request.page', 1);
        $psize     = 20;

		$keyword = I('request.keyword');
		$this->keyword = $keyword;
		
		
        if (!empty($keyword)) {
            $condition .= ' and advname like '.'"%' . $keyword . '%"';
        }

		$enabled = I('request.enabled');
		
        if (!empty($enabled) && $enabled >= 0) {
            $condition .= ' and enabled = ' . $enabled;
        } else {
            $enabled = -1;
        }
		$this->enabled = $enabled;
		
		
		
        $list = M()->query('SELECT id,advname,thumb,link,type,displayorder,enabled FROM ' . 
		C('DB_PREFIX'). "lionfish_comshop_adv  \r\n
		WHERE  " . $condition . ' order by displayorder desc, id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);
        
		
		$total = M('lionfish_comshop_adv')->where($condition)->count();

        $pager = pagination2($total, $pindex, $psize);

		
		$this->list = $list;
		$this->pager = $pager;
        $this->display();
    }
	
	//
	public function addnavigat()
    {
        $_GPC = I('request.');

        $id = intval($_GPC['id']);
		
		 $category = D('Seller/GoodsCategory')->getFullCategory(false, true);
		 $this->category = $category;
		    
        if (!empty($id)) {
			// $category = D('Seller/GoodsCategory')->getFullCategory(false, true);
			// $this->category = $category;
			$item = M('lionfish_comshop_navigat')->where( array('id' => $id) )->find();
			$this->item = $item;
        }

        if (IS_POST) {
            $data = $_GPC['data'];
          if($data['type']==3 || $data['type']==4){
                $data['link'] = $data['cid'];
            }
			D('Seller/Adv')->navigat_update($data);
            show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
        }
        $this->display();
    }

    public function changenavigat()
    {
        $_GPC = I('request.');
        $id = intval($_GPC['id']);

        //ids
        if (empty($id)) {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }

        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }

        $type  = trim($_GPC['type']);
        $value = trim($_GPC['value']);

        if (!(in_array($type, array('enabled', 'displayorder')))) {
            show_json(0, array('message' => '参数错误'));
        }

        	
		$items = M('lionfish_comshop_navigat')->field('id')->where('id in( ' . $id . ' ) ')->select();		

        foreach ($items as $item) {
			M('lionfish_comshop_navigat')->where( array('id' => $item['id']) )->save( array($type => $value) );
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

    }

    public function deletenavigat()
    {
       
        $_GPC = I('request.');
        $id = intval($_GPC['id']);

        //ids
        if (empty($id)) {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }

        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }

		
		$items = M('lionfish_comshop_navigat')->field('id')->where( 'id in( ' . $id . ' )' )->select();

        foreach ($items as $item) {
			M('lionfish_comshop_navigat')->where( array('id' => $item['id']) )->delete();
        }

       show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
    }

	//
	
	public function addslider()
    {
       
        $id = I('request.id');
        if (!empty($id)) {
			$item = M('lionfish_comshop_adv')->where( array('id' => $id) )->find();
			$this->item = $item;
        }

        if (IS_POST) {
            $data = I('request.data');
			
            D('Seller/Adv')->update($data);
            show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
        }

        $this->display();
    }
	
	public function changeslider()
    {

        $id = I('request.id');

        //ids
        if (empty($id)) {
			$ids = I('request.ids');
			
            $id = ((is_array($ids) ? implode(',', $ids) : 0));
        }

        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }

        $type  = I('request.type');
        $value = I('request.value');

        if (!(in_array($type, array('enabled', 'displayorder')))) {
            show_json(0, array('message' => '参数错误'));
        }

		$items = M('lionfish_comshop_adv')->where( array('id' => array('in', $id)) )->select();
		
        foreach ($items as $item) {
           
			M('lionfish_comshop_adv')->where( array('id' => $item['id']) )->save(  array($type => $value) );
        }

        show_json(1 , array('url' => $_SERVER['HTTP_REFERER']));

    }

    public function delete()
    {
       
        $id = I('request.id');

        //ids
        if (empty($id)) {
            $ids = I('request.ids');
			
            $id = ((is_array($ids) ? implode(',', $ids) : 0));
        }

        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }

      
		$items = M('lionfish_comshop_adv')->where( array('id' => array('in', $id)) )->select();
		
        foreach ($items as $item) {
			M('lionfish_comshop_adv')->where( array('id' => $item['id']) )->delete();
        }

         show_json(1 , array('url' => $_SERVER['HTTP_REFERER']));
    }
	
	
	/**
     * 公告管理
     */
    public function notice()
    {
        $pindex    = I('request.page',1);
        $psize     = 20;

		$keyword = I('request.keyword','','trim');
		$this->keyword = $keyword;
		
        if (!empty($keyword)) {
           
            $condition .= ' and content like "%'.$keyword.'%" ';
        }

		$enabled = I('request.enabled',-1);
		
        if (isset($enabled) && $enabled >= 0) {
           
            $condition .= ' and enabled = ' . $enabled;
        } else {
            $enabled = -1;
        }

		$this->enabled = $enabled;
			
		
			
        $list = M()->query('SELECT id,content,displayorder,enabled FROM ' . 
			C('DB_PREFIX')."lionfish_comshop_notice 
				WHERE 1=1   " . $condition . ' order by displayorder desc, id desc 
				limit ' . (($pindex - 1) * $psize) . ',' . $psize);
        
		$total = M('lionfish_comshop_notice')->where( "1=1 ".$condition )->count();			

        $pager = pagination2($total, $pindex, $psize);

		
		$this->list = $list;
		$this->pager = $pager;
        $this->display();
    }

    /**
     * 添加公告
     */
    public function addnotice()
    {
       
        $id = I('request.id');
        if (!empty($id)) {
			$item = M('lionfish_comshop_notice')->where( array('id' => $id) )->find();
			$this->item = $item;
        }

        if (IS_POST) {
            $data = I('request.data');
            D('Seller/Notice')->update($data);
			
            show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
        }

		
        $this->display();
    }

    /**
     * 改变公告状态
     */
    public function changenotice()
    {
		
		$id = I('request.id');

        //ids
        if (empty($id)) {
			$ids = I('request.ids');
            $id = ((is_array($ids) ? implode(',', $ids) : 0));
        }

        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }

        $type  = I('request.type');
        $value =  I('request.value');

        if (!(in_array($type, array('enabled', 'displayorder')))) {
            show_json(0, array('message' => '参数错误'));
        }

		$items = M('lionfish_comshop_notice')->where( array('id' => array('in', $id)) )->select();		
				
        foreach ($items as $item) {
			M('lionfish_comshop_notice')->where( array('id' => $item['id']) )->save( array($type => $value) );
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

    }

	/**
	 * 删除公告
	 */
    public function deletenotice()
    {
       
        $id = I('request.id');

        if (empty($id)) {
			$ids = I('request.ids');
			
            $id = (is_array($ids) ? implode(',', $ids) : 0);
        }

		$items = M('lionfish_comshop_notice')->field('id,content')->where( array('id' => array('in',$id)) )->select();			

        if (empty($item)) {
            $item = array();
        }

        foreach ($items as $item) {
		   M('lionfish_comshop_notice')->where(  array('id' => $item['id']) )->delete();
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
    }
	
	public function index()
	{
		if (IS_POST) {
			
			$data = I('request.parameter');
			$param = array();
			if(trim($data['wechat_apiclient_cert_pem'])) $param['wechat_apiclient_cert_pem'] = trim($data['wechat_apiclient_cert_pem']);
			if(trim($data['wechat_apiclient_key_pem'])) $param['wechat_apiclient_key_pem'] = trim($data['wechat_apiclient_key_pem']);
			if(trim($data['wechat_rootca_pem'])) $param['wechat_rootca_pem'] = trim($data['wechat_rootca_pem']);

			if(trim($data['weapp_apiclient_cert_pem'])) $param['weapp_apiclient_cert_pem'] = trim($data['weapp_apiclient_cert_pem']);
			if(trim($data['weapp_apiclient_key_pem'])) $param['weapp_apiclient_key_pem'] = trim($data['weapp_apiclient_key_pem']);
			if(trim($data['weapp_rootca_pem'])) $param['weapp_rootca_pem'] = trim($data['weapp_rootca_pem']);

			if(trim($data['app_apiclient_cert_pem'])) $param['app_apiclient_cert_pem'] = trim($data['app_apiclient_cert_pem']);
			if(trim($data['app_apiclient_key_pem'])) $param['app_apiclient_key_pem'] = trim($data['app_apiclient_key_pem']);
			if(trim($data['app_rootca_pem'])) $param['app_rootca_pem'] = trim($data['app_rootca_pem']);
			
			D('Seller/Config')->update($param);
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		$data = D('Seller/Config')->get_all_config();
		
		$this->data = $data;
		
		$this->display();
	}
	
	
	
    public function noticesetting()
    {
        
        if (IS_POST) {
			$data = I('request.parameter');
        
            $data['index_notice_horn_image'] = save_media($data['index_notice_horn_image']);
            
            D('Seller/Config')->update($data);
            
            show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
        }
        $data = D('Seller/Config')->get_all_config();
		
		$this->data = $data;
        
        $this->display();
    }

    public function qgtab()
    {
       
        if (IS_POST) {
			$data = I('request.parameter');
            
            $data['index_qgtab_one_select'] = save_media($data['index_qgtab_one_select']);
            $data['index_qgtab_one_selected'] = save_media($data['index_qgtab_one_selected']);
            $data['index_qgtab_two_select'] = save_media($data['index_qgtab_two_select']);
            $data['index_qgtab_two_selected'] = save_media($data['index_qgtab_two_selected']);
            
             
            D('Seller/Config')->update($data);
            
            show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
        }
        $data = D('Seller/Config')->get_all_config();
		
		$this->data = $data;
		$this->display();
    }
	
	
	
    /**
     * 图片魔方
     */
    public function cube()
    {
        $_GPC = I('request.');
		
        
        $condition = '';
        $pindex    = max(1, intval($_GPC['page']));
        $psize     = 20;

        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and name like "%'.$_GPC['keyword'].'%"';
        }

        if (isset($_GPC['enabled']) && $_GPC['enabled'] >= 0) {
            $_GPC['enabled'] = trim($_GPC['enabled']);
            $condition .= ' and enabled = ' . $_GPC['enabled'];
        } else {
            $_GPC['enabled'] = -1;
        }

        $list = M()->query('SELECT * FROM ' . C('DB_PREFIX'). "lionfish_comshop_cube 
        WHERE 1 " . $condition . ' order by displayorder desc, id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize );
       

	    $total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX'). 'lionfish_comshop_cube WHERE 1  ' . $condition );
		
		$total = $total_arr['0']['count'];

        $pager = pagination2($total, $pindex, $psize);

		
		$this->list = $list;
		$this->pager = $pager;
		
		$this->_GPC =$_GPC;
		
        $this->display();
    }

    /**
     * 添加魔方图片
     */
    public function addcube()
    {
        $_GPC = I('request.');

        $id = intval($_GPC['id']);
        if (!empty($id)) {
            	
			$item = M('lionfish_comshop_cube')->where( array('id' => $id ) )->find();	
            
			$item['thumb'] = unserialize($item['thumb']);
			
			$this->item = $item;
        }

        if ( IS_POST ) {
            $data = $_GPC['data'];

            $thumb = $cover = $link = array();
            $cover[] = $data["thumb_0"];
            $cover[] = $data["thumb_1"];
            $cover[] = $data["thumb_2"];
            $cover[] = $data["thumb_3"];
            $link[] = $data["link_0"];
            $link[] = $data["link_1"];
            $link[] = $data["link_2"];
            $link[] = $data["link_3"];
			$linktype[] = $data["linktype_0"];
            $linktype[] = $data["linktype_1"];
            $linktype[] = $data["linktype_2"];
            $linktype[] = $data["linktype_3"];
            $webview[] = $data["webview_0"];
            $webview[] = $data["webview_1"];
            $webview[] = $data["webview_2"];
            $webview[] = $data["webview_3"];

            $num = $data['num'];
            if($num==4){
                $thumb['cover'] = $cover;
                $thumb['link'] = $link;
				$thumb['linktype'] = $linktype;
                $thumb['webview'] = $webview;
            } else {
                $coverArr = array_chunk($cover, $num);
                $linkArr = array_chunk($link, $num);
				$linktypeArr = array_chunk($linktype, $num);
                $webviewArr = array_chunk($webview, $num);
                $thumb['cover'] = $coverArr[0];
                $thumb['link'] = $linkArr[0];
				$thumb['linktype'] = $linktypeArr[0];
                $thumb['webview'] = $webviewArr[0];
            }
            $params = array();
            $params['name'] = $data['name'];
           
            $params['displayorder'] = $data['displayorder'];
            $params['enabled'] = $data['enabled'];
            $params['name'] = $data['name'];
            $params['type'] = $data['type'];
            $params['thumb'] = serialize($thumb);
            $params['num'] = $data['num'];
            $params['linktype'] = 1;
            $params['addtime'] = time();

            
            
            if( !empty($id) && $id > 0 )
            {
                unset($params['addtime']);
                
				M('lionfish_comshop_cube')->where( array('id' => $id) )->save( $params );
				
                $id = $data['id'];
            }else{
				$id = M('lionfish_comshop_cube')->add( $params );
            }
            show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		
		$this->display();
	}
	
	/**
     * 切换魔方图显示隐藏  排序
     */
    public function changeCube()
    {
        $_GPC = I('request.');
        $id = intval($_GPC['id']);

        //ids
        if (empty($id)) {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }

        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }

        $type  = trim($_GPC['type']);
        $value = trim($_GPC['value']);

        if (!(in_array($type, array('enabled', 'displayorder')))) {
            show_json(0, array('message' => '参数错误'));
        }
        
		$items = M('lionfish_comshop_cube')->field('id')->where( 'id in( ' . $id . ' )' )->select();
	
        foreach ($items as $item) {
			M('lionfish_comshop_cube')->where( array('id' => $item['id']) )->save( array($type => $value) );
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

    }

    /**
     * 删除魔方图
     * @return [json] [description]
     */
    public function deleteCube()
    {
        $_GPC = I('request.');
		
        $id = intval($_GPC['id']);

        //ids
        if (empty($id)) {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }

        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }

		$items = M('lionfish_comshop_cube')->field('id')->where( 'id in( ' . $id . ' ) ' )->select();

        foreach ($items as $item) {
			M('lionfish_comshop_cube')->where( array('id' => $item['id'] ) )->delete();
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
    }
	
	
	 /**
     * 首页视频
     * @return [Json] [description]
     */
    public function video()
    {
       $_GPC = I('request.');
    
        if ( IS_POST ) {
			
            $data = ((is_array($_GPC['parameter']) ? $_GPC['parameter'] : array()));
            $data['index_video_poster'] = save_media($data['index_video_poster']);
            $data['index_video_url'] = save_media($data['index_video_url']);
            
            D('Seller/Config')->update($data);
            
           show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
        }
        $data = D('Seller/Config')->get_all_config();
		
		$this->data = $data;
        
        $this->display();
    }
	
}
?>