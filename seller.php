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
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

header("Content-Type:text/html; charset=utf-8");    

define('APP_DEBUG', true);

define('BIND_MODULE','Seller');
  
define ('APP_PATH', './Modules/' );


define('ROOT_PATH',str_replace('\\','/',dirname(__FILE__)) . '/'); 

define ('RUNTIME_PATH','./Runtime/');

require './ThinkPHP/ThinkPHP.php';