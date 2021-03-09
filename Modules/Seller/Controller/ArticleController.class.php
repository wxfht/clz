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

class ArticleController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
	}
	
	public function index()
	{
		
        $pindex    = I('request.page', 1);
        $psize     = 20;

		$keyword = I('request.keyword');
		$this->keyword = $keyword;
		
        if (!empty($keyword)) {
            $condition .= ' and title like "%'.$keyword.'%"';
        }

		$enabled = I('request.enabled',-1);
		
        if (isset($enabled) && $enabled >= 0) {
           
            $condition .= ' and enabled = ' . $enabled;
        } else {
            $enabled = -1;
        }
		$this->enabled = $enabled;
		

		
        $list = M()->query('SELECT id,title,content,displayorder,enabled FROM ' . 
		C('DB_PREFIX'). "lionfish_comshop_article  
		WHERE 1=1 " . $condition . ' order by displayorder desc, id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);
        
		$total = M('lionfish_comshop_article')->where("1=1 ".$condition)->count();
			
        $pager = pagination2($total, $pindex, $psize);

		$this->list = $list;
		$this->pager = $pager;
		
		$this->display();
	}

	/**
     * 编辑添加
     */
	public function add()
	{
        $id = I('request.id');
        if (!empty($id)) {
			$item = M('lionfish_comshop_article')->where( array('id' => $id) )->find();
			$this->id = $id;
			$this->item = $item;
        }

        if (IS_POST) {
            $data = I('request.data');
            D('Seller/Article')->update($data);
            
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
        }
		$this->display('Article/post');
	}

	/**
     * 改变状态
     */
    public function change()
    {

        $id = I('request.id');

        //ids
        if (empty($id)) {
			$ids = 	I('request.ids');
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

		$items = M('lionfish_comshop_article')->where( array('id' => array('in', $id) ) )->select();
		
        foreach ($items as $item) {
           
			M('lionfish_comshop_article')->where( array('id' => $item['id']) )->save( array($type => $value) );
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

    }

	/**
	 * 删除公告
	 */
    public function delete()
    {
       
        $id = I('request.id');

        if (empty($id)) {
			$ids = I('request.ids');
            $id = (is_array($ids) ? implode(',', $ids) : 0);
        }

		$items = M('lionfish_comshop_article')->field('id,title')->where( array('id' => array('in', $id) ) )->select();

        if (empty($item)) {
            $item = array();
        }

        foreach ($items as $item) {
			M('lionfish_comshop_article')->where( array('id' => $item['id']) )->delete();
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
    }
}
?>