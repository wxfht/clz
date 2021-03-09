<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('order');



$condition = array(
	
);



/* filter */
$uemail = strval($_GET['uemail']);
if ($uemail) {
	
	$uuser = Table::Fetch('user', $uemail, 'username');
	if($uuser) $condition['user_id'] = $uuser['id'];
	else $uemail = null;
}
$id = abs(intval($_GET['id'])); 
if ($id) $condition['id'] = $id;


$cbday = strval($_GET['cbday']);
$ceday = strval($_GET['ceday']);

if ($cbday) { 
	$cbtime = strtotime($cbday);
	$condition[] = "addtime >= '{$cbtime}'";
}
if ($ceday) { 
	$cetime = strtotime($ceday);
	$condition[] = "addtime <= '{$cetime}'";
}
$is_fahuo = -1;
//is_fahuo  -1 0 1 2 
if(isset($_GET['is_fahuo']) && $_GET['is_fahuo']>=0 )
{
	$is_fahuo = intval($_GET['is_fahuo']);
	$condition['state'] = $is_fahuo;
}
/* end fiter */

$count = Table::Count('tixian_order', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 20);

$orders = DB::LimitQuery('tixian_order', array(
	'condition' => $condition,
	'order' => 'ORDER BY id DESC',
	'size' => $pagesize,
	'offset' => $offset,
));

$pay_ids = Utility::GetColumn($orders, 'pay_id');
$pays = Table::Fetch('pay', $pay_ids);

$user_ids = Utility::GetColumn($orders, 'user_id');
$users = Table::Fetch('user', $user_ids);

$team_ids = Utility::GetColumn($orders, 'team_id');
$teams = Table::Fetch('team', $team_ids);

if(isset($_GET['daochu']) )
{
	$orders = DB::LimitQuery('tixian_order', array(
		'condition' => $condition,
		'order' => 'ORDER BY id DESC',
		'size' => $pagesize,
		'offset' => $offset,
	));
	$list = array();
	
	$name = '提现订单信息'.date('Ymd').'.csv';
				
	$kn = array(
				'id' => 'ID',
				'name' => '用户',
				'mobile' => '转账银行',
				'province' => '转账账户',
				'city' => '账户名',
				'area' => '提现金额',
				'street' => '状态',
				'address' => '处理时间',
				'express_id' => '申请时间',
		);
  
	$kn_str  = implode(',',$kn);
	$str = $kn_str."\n"; 
    $str = iconv('utf-8','gb2312',$str); 
				
	foreach($orders as $one)
	{
		
		$str .= $one['id'].",";
		$str .= iconv('utf-8','gb2312',$users[$one['user_id']]['username']).",";
		
		$str .= iconv('utf-8','gb2312',$users[$one['user_id']]['bankname']).",";
		$str .= "\t".iconv('utf-8','gb2312',$users[$one['user_id']]['bankaccount']).",";
		$str .= iconv('utf-8','gb2312',$users[$one['user_id']]['bankusername']).",";
		
		$str .= iconv('utf-8','gb2312',$currency.moneyit($one['money'])).",";
		
		if($one['state'] ==0)
		{
			$str .= iconv('utf-8','gb2312','申请中').",";
		}elseif( $one['state'] ==1)
		{
			$str .= iconv('utf-8','gb2312','已放款').",";
		}elseif($one['state'] ==2)
		{
			$str .= iconv('utf-8','gb2312','已拒绝').",";
		}
		
		if(!empty($one['shentime']))
		{
			$str .= iconv('utf-8','gb2312',date('Y-m-d H:i:s',$one['shentime'])).",";
		}else{
			$str .= iconv('utf-8','gb2312','-').",";
		}
		$str .= iconv('utf-8','gb2312',date('Y-m-d H:i:s',$one['addtime']))."\n";
	
	}
	
	header("Content-type:text/csv"); 
    header("Content-Disposition:attachment;filename=".$name); 
    header('Cache-Control:must-revalidate,post-check=0,pre-check=0'); 
    header('Expires:0'); 
    header('Pragma:public'); 
    echo $str; 
	
	die();
}



include template('manage_order_tixian');
