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

class SystemController extends \Think\Controller {
    
    function __construct()
    {
       parent::__construct();
    }
	
	public function upgrade_check()
	{
	
		//get_config_by_name($name)
		
		$data = $this->get_site_version(); // D('Home/Config')->get_config_by_name('site_version');
		
		if( empty($data['result']['version']) )
		{
			$data['status'] = 0;
		}
		echo json_encode( $data );
		die();
	}
	
	
	public function auth_upgrade()
	{
		
		$data = $this->get_site_version();
		
		$this->data = $data;
		include $this->display();
	}
	
	public function get_site_version()
	{
	//	$auth_url ="http://pintuan.liofis.com/upgrade_dan.php";
		$auth_url ="http://www.baidu.com";
		
		
		$version_info = M('lionfish_comshop_config')->where( array('name' => 'site_version') )->find();	
		
		$version = $version_info['value'];
		
		$cur_release_info = M('lionfish_comshop_config')->where( array('name' => 'site_version') )->find();	
		
		$cur_release = $cur_release_info['value'];
		
		$url = D('Home/Front')->get_config_by_name('shop_domain');
		$release = $cur_release;
		
		
		$modname = 'lionfish_comshop';
		$domain = trim(preg_replace('/http(s)?:\\/\\//', '', rtrim($url, '/')));
		$ip = gethostbyname($_SERVER['HTTP_HOST']);
		
		
		
		$resp = http_request($auth_url, array('action' => 'check_version','ip' => $ip,'release' => $release,'version' => $version, 'domain' => $domain) );
		
		$data = @json_decode($resp, true);
		
		
		$data['cur_version'] = $version;
		$data['cur_release'] = $cur_release;
		
		return $data;
	}
	
	public function opupdate()
	{
		
		$upgrade = $this->update_site_version();
		
		
			
		S('cloud:shiziyushop:upgradev2', array('files' => $upgrade['result'], 'version' => $upgrade['result']['version'], 'release' => $upgrade['result']['release']));
			
	
		$filecount = count($upgrade['result']['admin_file_list'])+count($upgrade['result']['weprogram_file_list']);
		
		//show_json(1, array('result' => 1, 'version' => $upgrade['version'], 'release' => $upgrade['release'], 'filecount' => count($files), 'database' => !empty($database), 'upgrades' => !empty($upgrade['upgrades']), 'log' => $log, 'templatefiles' => $templatefiles));
			
		$this->filecount = $filecount;
		$this->upgrade = $upgrade;
			
		include $this->display();
	}
	
	
	private function update_site_version()
	{
		
	//	$auth_url ="http://pintuan.liofis.com/upgrade_dan.php";
			$auth_url ="http://www.baidu.com";
		
		$url = D('Home/Front')->get_config_by_name('shop_domain');
		
		$version_info = M('lionfish_comshop_config')->where( array('name' => 'site_version') )->find();
		$release_info = M('lionfish_comshop_config')->where( array('name' => 'cur_release') )->find();
		
		$version = $version_info['value'];
		$release = $release_info['value'];
		
		$modname = 'lionfish_comshop';
		
		$domain = trim(preg_replace('/http(s)?:\\/\\//', '', rtrim($url, '/')));
		$ip = gethostbyname($_SERVER['HTTP_HOST']);
		
		$resp = http_request($auth_url, array('action' => 'get_version_file','ip' => $ip,'release' => $release,'version' => $version, 'domain' => $domain) );
		$data = @json_decode(gzuncompress($resp), true);
		
		$data['cur_version'] = $version;
		$data['cur_release'] = D('Home/Front')->get_config_by_name('cur_release');
		
		
		
		
	
		
		return $data;
	}
	
	private function get_upgrade_file($file)
	{
	//	$auth_url ="http://pintuan.liofis.com/upgrade_dan.php";
			$auth_url ="http://www.baidu.com";
		//M('lionfish_comshop_config')->where( array('name' => 'site_version') )->save( array('value' => $version) ); 
		
		$version_info = M('lionfish_comshop_config')->where( array('name' => 'site_version') )->find();
		$release_info = M('lionfish_comshop_config')->where( array('name' => 'cur_release') )->find();
		
		$version = $version_info['value'];
		$release = $release_info['value'];
		//$version = D('Home/Front')->get_config_by_name('site_version');
		//$release = D('Home/Front')->get_config_by_name('cur_release');
		
		$url = D('Home/Front')->get_config_by_name('shop_domain');
		
		$modname = 'lionfish_comshop';
		$domain = trim(preg_replace('/http(s)?:\\/\\//', '', rtrim($url, '/')));
		$ip = gethostbyname($_SERVER['HTTP_HOST']);
		
		$resp = http_request($auth_url, array('action' => 'down_version_file','file' => $file,'ip' => $ip,'release' => $release,'version' => $version, 'domain' => $domain) );
		
		$data = @json_decode(gzuncompress($resp), true);
		
		$data['cur_version'] = $version;
		$data['cur_release'] = $release;
		
		return $data;
	}
	
	
	public function do_update()
	{
		
		
		$upgrade = S('cloud:shiziyushop:upgradev2');
		$files = $upgrade['files'];
		$version = $upgrade['version'];
		$release = $upgrade['release'];
		
		
		
		$filecount = count($files['admin_file_list'])+count($files['weprogram_file_list']);
		
		
		
		if( !empty($files['admin_file_list']) )
		{
			$file = array_shift($files['admin_file_list']);  
			$filecount = $filecount -1;
			
			$file_data = $this->get_upgrade_file($file);
			
			
			
			//snailsh_shop
			$file = str_replace('snailsh_shop/','', $file);
			$dirpath = dirname($file);
			
			//SNAILFISH_PATH
			if (!is_dir(ROOT_PATH . $dirpath)) {
				RecursiveMkdir(ROOT_PATH . $dirpath);
				@chmod(ROOT_PATH . $dirpath, 511);
			}
			
			$base_file_content = $file_data['result']['base_file_content'];
			
			$content = base64_decode($base_file_content);
			
			
			$f  = @file_put_contents(ROOT_PATH . $file, $content);
			
			if( $f )
			{
				S('cloud:shiziyushop:upgradev2', array('files' => $files, 'version' => $upgrade['version'],'release' => $upgrade['release']));
						
				echo json_encode( array('code' => 0,'msg' => '更新'.$file.'文件成功，还剩'.$filecount.'个文件') );
				die();
			 }else{
				 
				
				echo json_encode( array('code' => 1,'msg' => $dirpath.' 目录不可写') );
				die(); 
			 }
			 
		}else if( !empty($files['weprogram_file_list']) )
		{
			$file = array_shift($files['weprogram_file_list']);  
			$filecount = $filecount -1;
			
			$file_data = $this->get_upgrade_file($file);
			
			//snailsh_shop
			
			$dirpath = dirname($file);
			
			
			//SNAILFISH_PATH
			if (!is_dir(ROOT_PATH .'Data/'.$version.'/'. $dirpath)) {
				
				
				RecursiveMkdir(ROOT_PATH .'Data/'.$version.'/'. $dirpath); 
				@chmod(ROOT_PATH .'Data/'.$version.'/'. $dirpath, 511);
			}
			
			$base_file_content = $file_data['result']['base_file_content'];
			
			$content = base64_decode($base_file_content);
			
			 $f  = @file_put_contents(ROOT_PATH .'Data/'.$version.'/'. $file, $content);
			 if( $f )
			 {
				S('cloud:shiziyushop:upgradev2', array('files' => $files, 'version' => $upgrade['version'],'release' => $upgrade['release']));
				echo json_encode( array('code' => 0,'msg' => '更新'.$file.'文件成功，还剩'.$filecount.'个文件') );
				die();
			 }else{
				
				echo json_encode( array('code' => 1,'msg' => 'Data/'.$version.' 目录不可写') );
				die(); 
			 }
			
		}else if( !empty($files['sql_file']) )
		{
			$file = $files['sql_file'];  
			$files['sql_file'] = '';
			
			$filecount = $filecount -1;
			
			$file_data = $this->get_upgrade_file($file);
			
			$dirpath = dirname($file);
			
			if (!is_dir(ROOT_PATH .'Data/'.$version.'/'. $dirpath)) {
				RecursiveMkdir(ROOT_PATH .'Data/'.$version.'/'. $dirpath);
				
				@chmod(ROOT_PATH .'Data/'.$version.'/'. $dirpath, 511);
			}
			
			$base_file_content = $file_data['result']['base_file_content'];
			
			$content = base64_decode($base_file_content);
			
			
			 $f  = @file_put_contents(ROOT_PATH .'Data/'.$version.'/'. $file, $content);
			
			 if( $f )
			 {
				include ROOT_PATH .'Data/'.$version.'/'. $file;
				M()->execute($sql_content);
				S('cloud:shiziyushop:upgradev2', array('files' => $files, 'version' => $upgrade['version'],'release' => $upgrade['release']));
				@unlink(ROOT_PATH .'Data/'.$version.'/'. $file);
				echo json_encode( array('code' => 0,'msg' => '更新sql文件成功,更新完成') );
				die();
			 }else{
				echo json_encode( array('code' => 1,'msg' => 'Data/'.$version.' 目录不可写') );
				die(); 
			 }
			 
		}else{
			if( !empty($version) && !empty($release) )
			{
				M('lionfish_comshop_config')->where( array('name' => 'cur_release') )->save( array('value' => $release) ); 
				M('lionfish_comshop_config')->where( array('name' => 'site_version') )->save( array('value' => $version) ); 
				
				$c= D('Seller/Config')->get_all_config(true);
			}
																
			echo json_encode( array('code' => 2,'msg' => '更新完成') );
			die();
		} 
	}
	
	
}
