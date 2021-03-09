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
namespace Admin\Model;
use Think\Model;
class BlogReplyModel extends Model{
	
	
	public function show_blog_reply_page(){
		
		$sql='SELECT br.*,b.title as blog_title FROM '.C('DB_PREFIX').'blog_reply br,'.C('DB_PREFIX').'blog b where b.blog_id=br.blog_id';
		
		$count=count(M()->query($sql));
		
		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		
		$show  = $Page->show();// 分页显示输出	
		
		$sql.=' order by b.blog_id desc LIMIT '.$Page->firstRow.','.$Page->listRows;
		
		$list=M()->query($sql);


		
		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);	
		
	}	
	
}
?>