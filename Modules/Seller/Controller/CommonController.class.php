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
use Think\Controller;
class CommonController extends Controller{
	
     /* 初始化,权限控制,菜单显示 */
     protected function _initialize(){
        // 获取当前用户ID
        define('SELLERUID',is_seller_login());
		
		
		//string(6) "Supply" string(5) "index"
		
		if( CONTROLLER_NAME == 'Supply' && (ACTION_NAME == 'login' || ACTION_NAME == 'login_do') )
		{
			
		}else{
			if(!SELLERUID){// 还没登录 跳转到登录页面
				if(is_agent_login())
				{
					define('ROLE','agenter');
				}else{
					//cookie('last_login_page', $rmid);
					
					$last_login_page = cookie('last_login_page');
					
						$this->redirect('Public/login');
					
					
					
				}
			}
		}
		
        
		/* 读取数据库中的配置 */
        $config =   S('DB_CONFIG_DATA');
        if(!$config){
            $config =   api('Config/lists');
            S('DB_CONFIG_DATA',$config);
        }
        C($config); //添加配置
        
		$blog_seller_order_ids = M('blog_seller_order')->field('blog_id')->where( array('seller_id' =>SELLERUID) )->select();
		
		$blog_ids_arr = array();
		foreach($blog_seller_order_ids as $val)
		{
			array_push($blog_ids_arr, $val['blog_id']);
		}
		
		if(!empty($blog_ids_arr))
		{
			$blog_ids_str = '';
			$map = array();
			$map['status'] = 1;
			$map['type'] = 'seller';	

			$map['seller_id'] = SELLERUID;
			$map['blog_id']= array('not in',$blog_ids_arr );
			
			$blog_not_count = M('blog')->where( $map )->count();
		
			$blog_not_list = M('blog')->field('blog_id,title')->where( $map )->limit(10)->select();
		} else{
			$blog_ids_str = '';
			$map = array();
			$map['type'] = 'seller';
			$map['status'] = 1;
			$blog_not_count = M('blog')->where( $map )->count();
			$blog_not_list = M('blog')->field('blog_id,title')->where( $map )->limit(10)->select();
		}
		
		//http://mall.shiziyu888.com/dan/seller.php?s=/Member/info/id/1668
		//strpos("You love php, I love php too!","php");
		$unsave_action_arr = array();
		$unsave_action_arr[] = 'Member/info/id';
		$can_save = true;
		
		foreach($unsave_action_arr as $val)
		{
			if( strpos($_SERVER['HTTP_REFERER'],$val) )
			{
				$can_save = false;
			}
		}
		if($can_save)
		{
			cookie('http_refer',$_SERVER['HTTP_REFERER']);
		}
		
		$this->blog_not_count = $blog_not_count;
		$this->blog_not_list = $blog_not_list;
		
		$this->system_hide_wepro = false;
		$this->system_hide_dan = true;
		
		
         // 权限过滤
       // $this->filterAccess();
     }
	 
	/**
     * 权限过滤
     * @return
     */
    protected function filterAccess() {
    	
        if (!C('USER_AUTH_ON')) {
            return ;
        }

        //Admin
        //var_dump( \Org\Util\Rbac::AccessDecision(C('GROUP_AUTH_NAME')) );die();

        if (\Org\Util\Rbac::AccessDecision(C('GROUP_AUTH_NAME'))) {
            return ;
        }

        if (!$_SESSION [C('USER_AUTH_KEY')]) {
            // 登录认证号不存在
            return $this->redirect(C('USER_AUTH_GATEWAY'));
        }

        if ('Index' === CONTROLLER_NAME && 'index' === ACTION_NAME) {
            // 首页无法进入，则登出帐号
            D('Admin', 'Service')->logout();
        }

        return $this->error('您没有权限执行该操作！');
    }
     
	/* 空操作，用于输出404页面 */
	public function _empty(){	
		// $this->display('Public:404');die();
		die('空操作');
	}
	
	/**
	 *跳转控制	 
	 */
	public function osc_alert($status){
				
		if($status['status']=='back'){
			$this->error($status['message']);
			die;					
		}elseif($status['status']=='success'){
			$this->success($status['message'],$status['jump']);
			die;
		}elseif($status['status']=='fail'){
			$this->error($status['message'],$status['jump']);
			die;
		}
	}
	 
}
?>