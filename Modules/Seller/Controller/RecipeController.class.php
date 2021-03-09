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

class RecipeController extends CommonController{
	
	public function index()
	{
		$_GPC = I('request.');
        
		
        $pindex    = max(1, intval($_GPC['page']));
        $psize     = 20;

        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and recipe_name like "%'.$_GPC['keyword'].'%" ';
        }
		
		if( isset($_GPC['state']) && $_GPC['state'] != -1 )
		{
			$condition .= ' and state = '.$_GPC['state'];
		}
		if( isset($_GPC['cate']) && $_GPC['cate'] != '' )
		{
			$condition .= ' and cate_id = '.$_GPC['cate'];
		}
		
		$category = D('Seller/GoodsCategory')->getFullCategory(true, true,'recipe');


        $list = M()->query('SELECT * FROM ' . C('DB_PREFIX') . "lionfish_comshop_recipe  
		WHERE 1  " . $condition . ' order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize );
        
		foreach( $list as $key => $val )
		{
			
			$goods_count = M('lionfish_comshop_recipe_ingredients')->where( array('recipe_id' => $val['id'] ) )->count();
			
			$val['username'] = '';
			$val['cate_name'] = '';
			
			if(  $val['member_id'] > 0)
			{	
				$mb_info = M('lionfish_comshop_member')->where( array('member_id' => $val['member_id'] ) )->find();		
						
				if( !empty($mb_info) )
				{
					$val['username'] = $mb_info['username'];
				}
			}
			
			if( $val['cate_id'] > 0 )
			{		
				$cate_info = M('lionfish_comshop_goods_category')->where( array('id' => $val['cate_id'] ) )->find();
				
				if( !empty($cate_info) )
				{
					$val['cate_name'] = $cate_info['name'];
				}
			}
			
			$val['goods_count'] = $goods_count;
			$list[$key] = $val;
		}
		
		$total = M('lionfish_comshop_recipe')->where("1 ".$condition)->count();
		
        $pager = pagination2($total, $pindex, $psize);

		$this->pager = $pager;
		$this->list = $list;
		$this->category =$category;
		$this->_GPC = $_GPC;
		
		
		$this->display();
	}

	/**
     * 编辑添加
     */
	public function add()
	{
		$_GPC = I('request.');
		

        $id = intval($_GPC['id']);
        if (!empty($id)) {
           
			$item = M('lionfish_comshop_recipe')->where( array('id' => $id ) )->find();
			
			
			$ing_list = M('lionfish_comshop_recipe_ingredients')->where( array('recipe_id' => $id ) )->select();
			
			$limit_goods = array();
			
			if( !empty($ing_list) )
			{
				foreach( $ing_list as $key => $val )
				{
					$need_dd = array();
					
					if( !empty($val['goods_id']) )
					{
						$gd_info_list = M('lionfish_comshop_goods')->field('id,goodsname')->where( "id in (".$val['goods_id'].")" )->select();
						
						if( !empty($gd_info_list) )
						{
							foreach( $gd_info_list as $gd_info )
							{
								$thumb = M('lionfish_comshop_goods_images')->where( array('goods_id' => $gd_info['id'] ) )->order('id asc')->find();
								
								$thumb_img =  tomedia($thumb['image']);
								
								$tmp_dd = array();
								$tmp_dd['gid'] = $gd_info['id'];
								$tmp_dd['title'] = $val['title'];
								$tmp_dd['goodsname'] = $gd_info['goodsname'];
								$tmp_dd['image'] = tomedia( $thumb_img );
								
								$need_dd[] = $tmp_dd;
							}
						}
					}
					$val['limit_goods'] = $need_dd;
					
					$ing_list[$key] = $val;
				}
			}
			//limit_goods
			
			$this->ing_list = $ing_list;
			$this->item = $item;
			
        }
		
		$category = D('Seller/GoodsCategory')->getFullCategory(true, true,'recipe');
		
		$this->category = $category;

        if ( IS_POST ) {
			
			$need_data = array();
			$need_data['data'] = $_GPC['data'];
			$need_data['sub_name'] = $_GPC['sub_name'];
			$need_data['diff_type'] = $_GPC['diff_type'];
			$need_data['sp'] = $_GPC['sp'];
			$need_data['state'] = $_GPC['state'];
			$need_data['limit_goods_list'] = $_GPC['limit_goods_list'];
			
            D('Seller/Recipe')->update($need_data);
            
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
        }

		include $this->display();
	}

	public function change()
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
		

		$type = trim($_GPC['type']);
		$value = trim($_GPC['value']);

		if (!(in_array($type, array('state')))) {
			show_json(0, array('message' => '参数错误'));
		}
		
		$items = M('lionfish_comshop_recipe')->field('id')->where( 'id in( ' . $id . ' )' )->select();
		
		foreach ($items as $item ) {
			M('lionfish_comshop_recipe')->where( array('id' => $item['id']) )->save( array($type => $value) );
		}
		
		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}

	
    public function delete()
    {
        $_GPC = I('request.');
		
        $id = intval($_GPC['id']);

        if (empty($id)) {
            $id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
        }

		$items = M('lionfish_comshop_recipe')->field('id')->where( 'id in( ' . $id . ' )' )->select();		

        if (empty($item)) {
            $item = array();
        }

        foreach ($items as $item) {
            
			M('lionfish_comshop_recipe')->where( array('id' => $item['id']) )->delete();
			M('lionfish_comshop_recipe_ingredients')->where( array('recipe_id' => $item['id']) )->delete();
			M('lionfish_comshop_recipe_fav')->where( array('recipe_id' => $item['id']) )->delete();
			
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
    }
	
	public function config()
	{
		$_GPC = I('request.');
		
		if ( IS_POST ) {
			
			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));
			
			D('Seller/Config')->update($data);
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		
		$data = D('Seller/Config')->get_all_config();
		
		$this->data = $data;
		
		include $this->display();
	
	}
	
	public function slider ()
	{
		$_GPC = I('request.');
		
        $condition = ' and type="recipe" ';
        $pindex    = max(1, intval($_GPC['page']));
        $psize     = 20;

        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and advname like "%'.$_GPC['keyword'].'%" ';
        }

        if (isset($_GPC['enabled']) && $_GPC['enabled'] >= 0) {
            $_GPC['enabled'] = trim($_GPC['enabled']);
            $condition .= ' and enabled = ' . $_GPC['enabled'];
        } else {
            $_GPC['enabled'] = -1;
        }

		
        $list = M()->query('SELECT id,advname,thumb,link,type,displayorder,enabled FROM ' . C('DB_PREFIX'). "lionfish_comshop_adv  
			WHERE 1   " . $condition . ' order by displayorder desc, id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize );
		
		$total = M('lionfish_comshop_adv')->where( '1 '.$condition )->count();
		
        $pager = pagination2($total, $pindex, $psize);
		
		$this->list = $list;
		$this->pager = $pager;
		$this->_GPC = $_GPC;

        $this->display();
	}
	
	
	public function category()
	{
		global $_W;
		global $_GPC;
		
		
		
		$children = array();
		$category = M()->query('SELECT * FROM ' . C('DB_PREFIX'). 'lionfish_comshop_goods_category WHERE  cate_type="recipe"   ORDER BY pid ASC, sort_order DESC');

		foreach ($category as $index => $row) {
			if (!empty($row['pid'])) {
				$children[$row['pid']][] = $row;
				unset($category[$index]);
			}
		}
		
		$this->children = $children;
		$this->category = $category;
		
		$this->display();
	}
	
	public function addcategory()
	{
		$_GPC = I('request.');
		
		$data = array();
		$pid = isset($_GPC['pid']) ? $_GPC['pid']:0;
		$id = isset($_GPC['id']) ? $_GPC['id']:0;
		
		if ( IS_POST ) {
			
			$data = $_GPC['data'];
			
			D('Seller/GoodsCategory')->update($data,'recipe');
			
			show_json(1, array('shopUrl' => U('recipe/category')));
		}
		
		if($id >0 )
		{
			
			$data = M('lionfish_comshop_goods_category')->where( array('id' => $id ) )->find();
			
			$this->data = $data;
			
			$this->id = $id;
		}
		
		$this->pid = $pid;
		
		$this->display();
	}
	public function category_delete()
	{
		$_GPC = I('request.');
		
		$id = intval($_GPC['id']);
		
		$item = M('lionfish_comshop_goods_category')->field( 'id, name, pid' )->where( array('id' => $id ) )->find();

		if (empty($item)) {
			show_json(0, array('message' => '抱歉，分类不存在或是已经被删除！' ));
		}
		pdo_delete('', array('id' => $id, 'pid' => $id), 'OR');
		
		M('lionfish_comshop_goods_category')->where( "id={$id} or pid={$id}" )->delete();
		
		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	
	public function category_enabled()
	{
		$_GPC = I('request.');
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}

		$items = M('lionfish_comshop_goods_category')->field('id,name')->where( 'id in( ' . $id . ' )' )->select();		

		foreach ($items as $item) {
			M('lionfish_comshop_goods_category')->where( array('id' => $item['id']) )->save(  array('is_show' => intval($_GPC['enabled'])) );
		}
		
		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	
	 public function addslider()
    {
       $_GPC = I('request.');

       
        $id = intval($_GPC['id']);
        if (!empty($id)) {
            
			$item = M('lionfish_comshop_adv')->where( array('id' => $id) )->find();
			$this->item = $item;
        }

        if ( IS_POST ) {
            $data = $_GPC['data'];
            D('Seller/Adv')->update($data,'recipe');
            show_json(1, array('url' => U('recipe/slider') ) );
        }

        include $this->display();
    }
	public function changeslider()
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

		
		$items = M('lionfish_comshop_adv')->field('id')->where( 'id in( ' . $id . ' )' )->select();
		
		//id/15  value: 1
		
        foreach ($items as $item) {
           
			M('lionfish_comshop_adv')->where( array('id' => $item['id']) )->save( array($type => $value) );
        }

        show_json(1);

    }

    public function deleteslider()
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

		$items = M('lionfish_comshop_adv')->field('id')->where( 'id in( ' . $id . ' )' )->select();
		
        foreach ($items as $item) {
			M('lionfish_comshop_adv')->where( array( 'id' => $item['id'] ) )->delete();
        }

        show_json(1);
    }
	
	public function order()
	{
		$_GPC = I('request.');
       

        $pindex    = max(1, intval($_GPC['page']));
        $psize     = 20;

        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and order_sn like "%'.$_GPC['keyword'].'%" ';
        }
        
		
		$list = M()->query('SELECT * FROM ' . C('DB_PREFIX'). "lionfish_comshop_member_card_order  
		WHERE state= 1  " . $condition . ' order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
	    
		if( !empty($list) )
		{
			foreach( $list  as  $key => $val )
			{
				$mb_info = M('lionfish_comshop_member')->where( array('member_id' => $val['member_id'] ) )->find();
				
				$val['username'] = $mb_info['username'];
				$list[$key] = $val;
			}
		}
		
		$total = M('lionfish_comshop_member_card_order')->where( 'state= 1 '. $condition )->count();

        $pager = pagination2($total, $pindex, $psize);
		
		$this->_GPC = $_GPC;
		$this->list = $list;
		$this->pager = $pager;

		$this->display();
	}
	
	
}
?>