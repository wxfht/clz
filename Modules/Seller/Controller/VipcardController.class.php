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

class VipcardController extends CommonController{
	
	public function index()
	{
		$_GPC = I('request.');
		  

        $pindex    = max(1, intval($_GPC['page']));
        $psize     = 20;

		$condition = " 1 ";
		
        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and cardname like "%'.$_GPC['keyword'].'%"';
        }

		$list = M('lionfish_comshop_member_card')->where($condition)->order('id desc')->limit( (($pindex - 1) * $psize) , $psize )->select();
		
		
		$total = M('lionfish_comshop_member_card')->where( $condition )->count();

        $pager = pagination2($total, $pindex, $psize);

		
		$this->list = $list;
		$this->pager = $pager;
		
		$this->_GPC = $_GPC;
		
		$this->display();
	}

	public function order ()
	{
		$_GPC = I('request.');
		
       
        $pindex    = max(1, intval($_GPC['page']));
        $psize     = 20;

		$condition = " state= 1 ";
		
        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and order_sn like "%'. $_GPC['keyword'] .'%"';
        }
		
		$list = M('lionfish_comshop_member_card_order')->where( $condition )->order('id desc')->limit( (($pindex - 1) * $psize) , $psize )->select();
        
		
		if( !empty($list) )
		{
			foreach( $list  as  $key => $val )
			{
				//member_id
				$mb_info = M('lionfish_comshop_member')->where( array('member_id' => $val['member_id']) )->find();
				$val['username'] = $mb_info['username'];
				$list[$key] = $val;
			}
		}
		
		$total = M('lionfish_comshop_member_card_order')->where( $condition )->count();
		
        $pager = pagination2($total, $pindex, $psize);

		$this->_GPC = $_GPC;
		
		$this->list = $list;
		$this->pager = $pager;
		
		$this->display();
	}
	
	public function equity ()
	{
		$_GPC = I('request.');
		
       
        $pindex    = max(1, intval($_GPC['page']));
        $psize     = 20;

		$condition = " 1 ";
		
        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and equity_name like "%'. $_GPC['keyword'] .'%"';
        }
		
		$list = M('lionfish_comshop_member_card_equity')->where( $condition )->order('id desc')->limit( (($pindex - 1) * $psize) , $psize )->select();
        
		
		$total = M('lionfish_comshop_member_card_equity')->where( $condition )->count();
		
        $pager = pagination2($total, $pindex, $psize);

		$this->_GPC = $_GPC;
		
		$this->list = $list;
		$this->pager = $pager;
		
		$this->display();
	}
	
	/**
     * 编辑添加
     */
	public function add_equity()
	{
		$_GPC = I('request.') ;
		

        $id = intval($_GPC['id']);
		
        if (!empty($id)) {
            
			$item = M('lionfish_comshop_member_card_equity')->where( array('id' => $id ) )->find();
			
			$this->item = $item;
        }

        if (IS_POST) {
			
            $data = $_GPC['data'];
            D('Home/Vipcard')->updateequity($data);
			
			show_json(1,  array('url' => $_SERVER['HTTP_REFERER']));
        }

		$this->display('Vipcard/add_equity');
	}
	
	public function deleteequity()
    {
		$_GPC = I('request.');
		
        $id = intval($_GPC['id']);

        if (empty($id)) {
            $id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
        }

		$items =  M('lionfish_comshop_member_card_equity')->field('id')->where('id in( ' . $id . ' )')->select();
		
        if (empty($item)) {
            $item = array();
        }

        foreach ($items as $item) {
			M('lionfish_comshop_member_card_equity')->where( array('id' => $item['id']) )->delete();
        }

        show_json(1,  array('url' => $_SERVER['HTTP_REFERER']));
    }
	
	/**
     * 编辑添加
     */
	public function add()
	{
		$_GPC = I('request.');
		
       

        $id = intval($_GPC['id']);
		
        if (!empty($id)) {
			$item = M('lionfish_comshop_member_card')->where( array('id' => $id ) )->find();
			$this->item = $item;
        }

        if ( IS_POST ) {
            $data = $_GPC['data'];
            D('Home/Vipcard')->update($data);
            show_json(1,  array('url' => $_SERVER['HTTP_REFERER']));
        }

		$this->display('Vipcard/post');
	}

	

	
    public function delete()
    {
        $_GPC = I('request.');
		
        $id = intval($_GPC['id']);

        if (empty($id)) {
            $id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
        }

		$items = M('lionfish_comshop_member_card')->field('id')->where( 'id in( ' . $id . ' )' )->select();

        if (empty($item)) {
            $item = array();
        }

        foreach ($items as $item) {
           
			M('lionfish_comshop_member_card')->where( array('id' => $item['id']) )->delete();
        }

        show_json(1,  array('url' => $_SERVER['HTTP_REFERER']));
    }
	
	public function config()
	{
		
		$_GPC = I('request.');
		if ( IS_POST ) {
			
			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));
			
			D('Seller/Config')->update($data);
			
			show_json(1,  array('url' => $_SERVER['HTTP_REFERER']));
		}
		
		$data = D('Seller/Config')->get_all_config();
		
		$this->data = $data;
		include $this->display();
	
	}
	
	
	
}
?>