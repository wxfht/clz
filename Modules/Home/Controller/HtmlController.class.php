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

class HtmlController extends CommonController {
	
    public function about(){
       $this->title='关于我们-';		   	
       $this->meta_keywords=C('SITE_KEYWORDS');
       $this->meta_description=C('SITE_DESCRIPTION');
       $this->display();
    }
	
    public function contact(){
       $this->title='联系我们-';		   	
       $this->meta_keywords=C('SITE_KEYWORDS');
       $this->meta_description=C('SITE_DESCRIPTION');
       $this->display();
    }
	
		
	
}