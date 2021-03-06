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

class PermController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
	}
	
	public function index()
	{
		$_GPC = I('request.');
		
		$this->gpc = $_GPC;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$status = $_GPC['status'];
		$condition = ' and deleted=0';

		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and rolename like  "%'.$_GPC['keyword'].'%" ';
		}

		if ($_GPC['status'] != '') {
			$condition .= ' and status=' . intval($_GPC['status']);
		}

	
		
		$list = M()->query('SELECT *  FROM ' . C('DB_PREFIX') . 'lionfish_comshop_perm_role WHERE 1 ' . $condition . 
				' ORDER BY id desc LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize);

		foreach ($list as &$row) {
			$row['usercount'] = M('seller')->where( array('s_role_id' => $row['id'] ) )->count();	
		}

		unset($row);
		
		$total = M('lionfish_comshop_perm_role')->where("1 ". $condition )->count();		
		$pager = pagination2($total, $pindex, $psize);
		
		$this->pager = $pager;
		$this->list = $list;
			
		$this->display();
	}

	
	public function rolestatus()
	{
		$_GPC = I('request.');
		
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}

		$status = intval($_GPC['status']);
				
		$items = M('lionfish_comshop_perm_role')->field('id,rolename')->where( 'id in( ' . $id . ' )' )->select();			
		
		
		foreach ($items as $item) {
			M('lionfish_comshop_perm_role')->where(  array('id' => $item['id']) )->save( array('status' => $status) );
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	
	
	protected function perm_shop()
	{
		return array(
			'text'          => '商城概况',
			'index'           => array(
				'analys'   => '统计'
			)
		);
	}
	protected function perm_goods()
	{
		return array(
			'text'          => '商品管理',
			'goods'           => array(
				'index'   => '商品列表',
				'goodscategory'   => '商品分类',
				'goodsspec'   => '商品规格',
				'goodstag'   => '商品标签',
				'config'   => '商品设置',
				'settime'   => '统一时间',
				'industrial'   => '工商资质',
				)
		);
		
	}
	
	protected function perm_order()
	{
		return array(
			'text'          => '订单管理',
			'order'           => array(
				'index'   => '订单列表',
				'ordersendall'   => '批量发货',
				'orderaftersales' => '售后管理',
				'ordercomment'   => '评价列表',
				'ordercomment_config'   => '评价设置',
				'config'   => '订单设置',
				)
		);
		
	}
	
	protected function perm_user()
	{
		return array(
			'text'          => '会员管理',
			'user'           => array(
				'index'   => '会员列表',
                'Recharge_record_list' => '会员充值列表',
				'userjia' => '虚拟会员管理',
				'config' => '会员设置',
				'usergroup' => '会员分组',
				'userlevel' => '会员等级',
				)
		);
		
	}
	
	protected function perm_distribution()
	{
		return array(
			'text'          => '会员分销',
			'distribution'           => array(
				'distribution'   => '分销列表',
				'distributionorder'   => '订单管理',
				'config'   => '分销设置',
				'qrcodeconfig'   => '海报设置',
				'withdrawallist'   => '提现列表',
				'withdraw_config'   => '提现设置',
				)
		);
		
	}
	
							
							
	protected function perm_communityhead()
	{
		return array(
			'text'          => '团长管理',
				'communityhead'     => array(
				'index'   => '团长列表',
				'usergroup'   => '团长分组',
				'headlevel'   => '团长等级',
				'config'   => '团长设置',
				'distribulist'   => '提现列表',
				'distributionpostal'   => '提现设置',
				)
		);
	}
	
	

	protected function perm_supply()
	{
		return array(
			'text'          => '供应商管理',
				'supply'     => array(
				'index'   => '供应商列表',
				'admintixianlist'   => '提现申请',
				'distributionpostal'   => '提现设置',
				'baseconfig'   => '基本设置',
				'config'   => '申请页面内容',
				)
		);
	}
	
	protected function perm_article()
	{
		return array(
			'text'          => '文章列表',
				'article'     => array(
				'index'   => '文章列表',
				)
		);
	}
	
	//拼团
	protected function perm_group()
	{
		return array(
			'text'          => '拼团管理',
				'group'     => array(
				'goods'   => '商品管理',
				'goodscategory'   => '商品分类',
				'goodsspec'   => '商品规格',
				'goodstag'   => '商品标签',
				'goodsvircomment'   => '虚拟评价',
				'pintuan'   => '拼团管理',
				'orderlist'   => '订单管理',
				'ordersendall'   => '批量发货',
				'orderaftersales'   => '售后管理',
				'slider'   => '幻灯片',
				'config'   => '拼团设置',
				'pincommiss'   => '拼团佣金',
				'withdrawallist'   => '提现列表',
				'withdraw_config'   => '提现设置',

				)
		);
	}
	
	protected function perm_delivery()
	{
		return array(
			'text'          => '配送单管理',
				'delivery'     => array(
					'delivery'   => '配送单管理',
					'get_delivery_list'   => '生成配送单',
					'delivery_line'   => '配送路线',
					'delivery_clerk'   => '配送人员',
				)
		);
	}
	protected function perm_data_static()
	{
		return array(
			'text'          => '数据',
				'reports'     => array(
					'index'   => '营业数据',
					'datastatics'   => '数据统计',
					'communitystatics'   => '团长统计',
					'active_users'   => '活跃用户统计',
				)
		);
	}
	protected function perm_perm()
	{
		return array(
			'text'          => '角色管理',
				'perm'     => array(
					'index'   => '角色管理',
					'user'   => '后台用户管理',
				)
		);
	}
	protected function perm_attachment()
	{
		return array(
			'text'          => '附件管理',
				'attachment'     => array(
					'index'   => '附件设置',
				)
		);
	}
	
	protected function perm_config()
	{
		return array(
			'text'          => '设置',
				'config'     => array(
					'index'   => '基本设置',
					'picture'   => '图片设置',
				),
				'weprogram'     => array(
					'index'   => '参数设置',
					'templateconfig'   => '模板消息设置',
					'tabbar'   => '底部菜单设置',
				),
				'configpay'     => array(
					'index'   => '支付设置',
					
				),
				'configindex'     => array(
					'slider'   => '幻灯片',
					'notice'   => '公告',
					'navigat'   => '导航图标',
					'noticesetting'   => '公告设置',
					'qgtab'   => '抢购切换',
				),
				'shipping'     => array(
					'templates'   => '运费模板',
				),
				'logistics'     => array(
					'inface'   => '物流接口',
				),
				'express'     => array(
					'config'   => '快递方式',
					'deconfig'   => '配送方式设置',
				),
				'copyright'     => array(
					'index'   => '版权说明',
					'about'   => '关于我们',
					'ordericon'   => '关于我们',
					'account'   => '后台账户',
				),
		);
	}
		
	protected function perm_marketing()
	{
		return array(
			'text'          => '营销活动',
				'marketing'     => array(
					'coupon'   => '优惠券管理',
					'category'   => '优惠券分类',
					'couponsend'   => '手动发送',
					'fullreduction'   => '满减',
					'signinreward'   => '积分签到',
					'points'   => '积分设置',
					'recharge'   => '充值设置',
					'explain'   => '充值说明',
					'recharge_diary'   => '充值流水',
					'special'   => '主题活动',
					'seckill'   => '秒杀',
	
				),
					
				
				'points'     => array(
					'goods'   => '积分商品',
					'order'   => '兑换订单',
				
				),
				
				'recipe'     => array(
					'index'   => '菜谱',
					'equity'   => '菜谱分类',
					'order'   => '幻灯片',
					'config'   => '菜谱设置',
				),
				
				'vipcard'     => array(
					'index'   => '会员卡',
					'equity'   => '会员权益',
					'order'   => '购买会员订单',
					'config'   => '会员卡设置',
				),
				
		);
	}		
						
	public function allPerms()
	{
		
		$perms = array(
						'index' => $this->perm_shop(),
						'goods' => $this->perm_goods(), 
						'order' => $this->perm_order(), 
						'user' => $this->perm_user(), 
						'distribution' => $this->perm_distribution(),
						'communityhead' => $this->perm_communityhead(), 
						'supply' => $this->perm_supply(), 
						'marketing' => $this->perm_marketing(), 
						'article' => $this->perm_article(), 
						'delivery' => $this->perm_delivery(), 
						'reports' => $this->perm_data_static(), 
						'group' => $this->perm_group(), 
						'perm' => $this->perm_perm(), 
						'attachment' => $this->perm_attachment(),
						'config' => $this->perm_config(), 
				);
		
		return $perms;
	}
	
	
	public function formatPerms()
	{
		
			$perms = $this->allPerms();
			$array = array();

			foreach ($perms as $key => $value) {
				if (is_array($value)) {
					foreach ($value as $ke => $val) {
						if (!is_array($val)) {
							$array['parent'][$key][$ke] = $val;
						}

						if (is_array($val) && ($ke != 'xxx')) {
							foreach ($val as $k => $v) {
								if (!is_array($v)) {
									$array['son'][$key][$ke][$k] = $v;
								}

								if (is_array($v) && ($k != 'xxx')) {
									foreach ($v as $kk => $vv) {
										if (!is_array($vv)) {
											$array['grandson'][$key][$ke][$k][$kk] = $vv;
										}
									}
								}
							}
						}
					}
				}
			}


		return $array;
	}
	
	public function addrole()
	{
		
		$_GPC = I('request.');
		
		$id = intval($_GPC['id']);
			
		$item = M('lionfish_comshop_perm_role')->where( array('deleted' => 0 , 'id' => $id) )->find();		
				
		$perms = $this->formatPerms();
		$role_perms = array();
		$user_perms = array();

		if (!empty($item)) {
			$role_perms = explode(',', $item['perms2']);
		}

		$user_perms = explode(',', $item['perms2']);

		$this->item = $item;
		
		
		$this->perms = $perms;
		$this->user_perms = $user_perms;
		if (IS_POST) {
			
			$data = array( 'rolename' => trim($_GPC['rolename']), 'status' => intval($_GPC['status']), 'perms2' => is_array($_GPC['perms']) ? implode(',', $_GPC['perms']) : '');

			if (!empty($id)) {
				
				M('lionfish_comshop_perm_role')->where( array('id' => $id) )->save( $data );
			}
			else {
				M('lionfish_comshop_perm_role')->add( $data );
			}

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}

		$this->display();
	}
	
	public function roledelete()
	{
		$_GPC = I('request.');
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}

		$items = M('lionfish_comshop_perm_role')->field('id,rolename')->where('id in( ' . $id . ' )')->select();		

		foreach ($items as $item) {
			M('lionfish_comshop_perm_role')->where( array('id' => $item['id']) )->delete();
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	public function userstatus()
	{
		$_GPC = I('request.');
		
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}

		$status = intval($_GPC['s_status']);
		
		//$items = M()->query('SELECT s_id FROM ' . C('DB_PREFIX'). 'seller WHERE s_id in( ' . $id . ' )  ');
		$items = M('seller')->field('s_id')->where( 's_id in( ' . $id . ' )' )->select();
		foreach ($items as $item) {
			
			M('seller')->where( array('s_id' => $item['s_id']) )->save( array('s_status' => $status) );
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	public function user()
	{
		$_GPC = I('request.');
		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$status = $_GPC['status'];
		$condition = ' and  u.deleted=0 ';
		
		$this->gpc = $_GPC;
		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and ( u.s_uname like "%'.$_GPC['keyword'].'%" )';
			//or u.s_true_name like "%'.$_GPC['keyword'].'%" or u.s_mobile like "%'.$_GPC['keyword'].'%"	
		}

		if ($_GPC['roleid'] != '') {
			$condition .= ' and u.s_role_id=' . intval($_GPC['roleid']);
		}

		if ($_GPC['status'] != '') {
			$condition .= ' and u.s_status=' . intval($_GPC['status']);
		}

		$list =  M()->query('SELECT u.*,r.rolename FROM ' . C('DB_PREFIX') . 'seller as u  ' . 
			' left join ' . C('DB_PREFIX'). 'lionfish_comshop_perm_role as r on u.s_role_id =r.id  ' . 
			' WHERE 1 ' . $condition . ' ORDER BY s_id desc LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize);
			
		$total_arr =  M()->query('SELECT count(*) as count FROM ' . C('DB_PREFIX'). 'seller as u  ' . 
				' left join ' . C('DB_PREFIX'). 'lionfish_comshop_perm_role as  r on u.s_role_id =r.id  ' 
				. ' WHERE 1 ' . $condition . ' ', $params);
		
		$total = $total_arr[0]['count'];
		
		$pager = pagination2($total, $pindex, $psize);
		
		$roles = M()->query('select id,rolename from ' . C('DB_PREFIX'). 'lionfish_comshop_perm_role where  deleted=0' );
		
		$this->list = $list;
		$this->roles = $roles;
		$this->pager = $pager;
		
		
		$this->display();
	}
	
	
	public function rolequery()
	{
		$_GPC = I('request.');
		
		$this->gpc = $_GPC;
		
		$kwd = trim($_GPC['keyword']);
		$this->kwd = $kwd;
		
		$params = array();
		$condition = ' and deleted=0';

		if (!empty($kwd)) {
			$condition .= ' AND `rolename` LIKE "%'.$kwd.'%" ';
		}

		$ds = M()->query('SELECT id,rolename,perms2 FROM ' . C('DB_PREFIX') . 'lionfish_comshop_perm_role WHERE status=1 ' . $condition . ' order by id asc' );
		
		$this->ds = $ds;
		$this->display();
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
	
	public function adduser()
	{
		$_GPC = I('request.');
		
		$this->gpc = $_GPC;
		
		$id = intval($_GPC['id']);
		
		$item = array();
		if( $id >0 )
		{
			$item = M('seller')->where( array('s_id' => $id ,'deleted' => 0) )->find();
		}
		
		
		$perms = $this->formatPerms();
		
		$this->item = $item;
		$this->perms = $perms;
		
		
		$user_perms = array();
		$role_perms = array();

		if (!empty($item)) {
			
			$role = M('lionfish_comshop_perm_role')->where( array('id' => $item['s_role_id'],'deleted' => 0 ) )->find();
			
			if (!empty($role)) {
				$role_perms = explode(',', $role['perms2']);
			}

			$user_perms = explode(',', $item['perms2']);
		}
		
		$this->user_perms = $user_perms;
		$this->role_perms = $role_perms;
		
		$this->role = $role;
		
		if (IS_POST) {
			$data = array( 
					's_uname' => trim($_GPC['s_uname']), 
					's_true_name' => trim($_GPC['s_true_name']),
					's_mobile' => trim($_GPC['s_mobile']), 
					's_passwd' => ($_GPC['s_passwd']), 
					's_role_id' => ($_GPC['roleid']), 
					's_login_count' => '', 
					's_last_login_ip' => '',
					's_last_ip_region' => '',
					's_create_time' => time(), 
					's_last_login_time' => '', 
					's_status' => intval($_GPC['s_status']), 
					'perms' => '', 
					'deleted' => 0, 
					);
			
			
		

			
			if (!empty($item['s_id'])) {
				
				unset($data['s_create_time']);
				
				
				$user = M('seller')->where( array('s_uname' => $data['s_uname']) )->find();
				
				if (!empty($_GPC['s_passwd'])) {
					$data['s_passwd'] = think_ucenter_encrypt($data['s_passwd'],C('SELLER_PWD_KEY'));
					
					
				}else{
					unset($data['s_passwd']);
				}
				
				M('seller')->where( array('s_id' => $id) )->save($data);
			}
			else {
				
				$user = M('seller')->where( array('s_uname' => $data['s_uname']) )->find();
				
				$data['s_passwd'] = think_ucenter_encrypt($data['s_passwd'],C('SELLER_PWD_KEY'));
				if( !empty($user) )
				{
					show_json(0,  array('msg' => '此用户为系统存在用户，无法添加') );
				}
				
				M('seller')->add( $data );
			}
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}

		

		$this->display();
	}
	
	/**
	 * 删除公告
	 */
    public function userdelete()
    {
       
        $id = I('request.id');
        if (empty($id)) {
			$ids = I('request.ids');
            $id = (is_array($ids) ? implode(',', $ids) : 0);
        }

		$items = M('seller')->field('s_id')->where( array('s_id' => array('in', $id) ) )->select();

        //if (empty($item)) {
        //    $item = array();
        // }

        foreach ($items as $item) {
			M('seller')->where( array('s_id' => $item['s_id']) )->delete();
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
    }
	
}
?>