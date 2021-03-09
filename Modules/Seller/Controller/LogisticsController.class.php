<?php

namespace Seller\Controller;

class LogisticsController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
		
		//'pinjie' => '拼团介绍',
	}

	public function inface()
	{
		$_GPC = I('request.');
		
		if (IS_POST) {
			
			$data = ((is_array($_GPC['parameter']) ? $_GPC['parameter'] : array()));
			$data['kdniao_id'] = trim($data['kdniao_id']);
			$data['kdniao_api_key'] = trim($data['kdniao_api_key']);
			
			D('Seller/Config')->update($data);
			
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		$data = D('Seller/Config')->get_all_config();
		$this->data = $data;
		
		$this->display();
	}

	
}

?>
