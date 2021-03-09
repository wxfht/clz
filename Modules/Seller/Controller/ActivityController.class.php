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
use Admin\Model\SubjectModel;
use Admin\Model\SuperSpikeModel;
use Admin\Model\SpikeModel;
class ActivityController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='活动中心';
		$this->breadcrumb2='';
		$this->sellerid = SELLERUID;
		$this->subjecttype = array('normal' => '正常活动','zeyuan' => '0元试用','niyuan' => '9.9元','oneyuan' => '1元购','haitao' => '海淘');
	}
	
	/**
		普通主题活动
	**/
	public function subject(){
		$model=new SubjectModel();  
		$now_time = time();
		$type = I('get.type','normal');
		//niyuan
		
		$where = " where type='{$type}' and can_shenqing =1 and begin_time< {$now_time} and end_time > {$now_time}";
		
		$data=$model->show_subject_page($where);		
		
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出	
		$this->state = $state;
		$get_status = 1;
		//'subject','zeyuan','niyuan','oneyuan','haitao'
		
		switch($type)
		{
			case 'normal':
				$get_status = 1;
				break;
			case 'niyuan':
				$get_status = 6;
				break;
			case 'oneyuan':
				$get_status = 7;
				break;
			case 'haitao':
				$get_status = 8;
				break;
		}
		$this->get_status = $get_status;
		
		$this->display();
	}
	/**
		搜索可报名的商品
	**/
	public function goods_search()
	{
		$goods_name = I('post.goods_name','');
		$where = '  store_id='.SELLERUID." and type='normal' and lock_type='normal' and status=1 and quantity>0 ";
		
		if(!empty($goods_name))
		{
			$where .=  "  and name like '%".$goods_name."%' ";
		}
		$goods_list = M('goods')->where($where)->limit(20)->select();
		
		$this->goods_list = $goods_list;
		$result = array();
		$result['html'] = $this->fetch('Activity:goods_list_fetch');
		echo json_encode($result);
		die();
	}
	
	/**
		限时秒杀
	**/
	public function take_spike()
	{
		
		$id = I('get.id','0');
		
		$subject = M('spike')->where( array('id' => $id) )->find();
		if(empty($subject))
		{
			$this->redirect('Activity/spike');
			die();
		}
		$this->subject = $subject;
		
		$this->display();
	}
	
	/**
		报名主题活动
	**/
	public function take_subject()
	{
		$id = I('get.id','0');
		
		$subject = M('subject')->where( array('id' => $id) )->find();
		if(empty($subject))
		{
			$this->redirect('Activity/subject');
			die();
		}
		$this->subject = $subject;
		
		$this->display();
	}
	
	/**
		报名超值大牌活动
	**/
	public function take_superspike()
	{
		$id = I('get.id','0');
		
		$subject = M('super_spike')->where( array('id' => $id) )->find();
		if(empty($subject))
		{
			$this->redirect('Activity/superspike');
			die();
		}
		$this->subject = $subject;
		
		$this->display();
	}
	
	
	/**
		提交抽奖活动申请
	**/
	public function sub_lottery()
	{
		/**
			 ["begin_time"]=>
			  string(18) "2018-06-15 0:00:00"
			  ["end_time"]=>
			  string(18) "2018/06/16 0:00:00"
		**/
		$begin_time = I('post.begin_time');
		$end_time = I('post.end_time');
		
		$voucher_id = I('post.voucher_id',0);
	    $win_quantity = I('post.win_quantity',0); 
	    $is_auto_open = I('post.is_auto_open',0);
	    $pin_hour = I('post.pin_hour',0);
		
	    $real_win_quantity = I('post.real_win_quantity',0);
		
		$result = array('code' => 0);
		$data = I('post.goods_ids_arr');
		
		/**
		array(1) {
		  [0]=>
		  array(3) {
			["goods_id"]=>
			string(2) "27"
			["pin_price"]=>
			string(4) "0.01"
			["pin_count"]=>
			string(1) "2"
		  }
		}
		**/
		
		if($voucher_id == 0){
	        $result['msg'] = '请选择退款时赠送的优惠券';
	        echo json_encode($result);
	        die();
	    }
		
		$can_bao = 1;
		$bao_count =0;
		foreach($data as $val)
		{
			$goods_id = $val['goods_id'];
			$goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
			
			if(!$this->check_lottery_baom())
			{
				$can_bao = 0;
				break;
			}
			$bao_count++;
			
			M('lottery_goods')->where( array('goods_id' => $goods_id) )->save( array('state' => 3) );
			$spike_data = array();
	        $spike_data['goods_id'] = $goods_id;
	        $spike_data['state'] = 1;
	        $spike_data['seller_id'] = SELLERUID;
	        $spike_data['is_open_lottery'] = 0;
	        $spike_data['voucher_id'] = $voucher_id;
	        $spike_data['win_quantity'] = $win_quantity;
	        $spike_data['is_auto_open'] = $is_auto_open;
	        $spike_data['real_win_quantity'] = $real_win_quantity;
	        $spike_data['quantity'] = $goods_info['quantity'];
	        $spike_data['begin_time'] = strtotime($begin_time);
	        $spike_data['end_time'] = strtotime($end_time);
			
		
	        $spike_data['addtime'] = time();
	        $rs = M('lottery_goods')->add($spike_data);
			
	        if($rs) {
				M('pin_goods')->where( array('goods_id' => $goods_id) )->delete();
				//begin_time  end_time
				
				//添加拼团数据
				M('pin_goods')->add( array('goods_id' => $goods_id,'customer_id' => SELLERUID, 
										'pin_price' => $val['pin_price'],'type' => 'lottery','begin_time' => strtotime($begin_time),
										'end_time' => strtotime($end_time),'pin_hour' => $pin_hour,'pin_count' => $val['pin_count'],'addtime' =>time()
										) 
									);
				
	            M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'lottery','type' =>'lottery',  'status' => 1) );
	        }
		}
		$result['code'] = 1;
		$result['can_bao'] = $can_bao;
		$result['bao_count'] = $bao_count;
		echo json_encode($result);
		die();
		
	}
	
	/**
	检测抽奖商品是否有限制报名
	**/
	public function check_lottery_baom()
	{
		
		$now_time = time();
		$where = " ( (state=0) or (state =1 and begin_time <{$now_time} and end_time > {$now_time}) )and seller_id = ".SELLERUID;
		$count = M('lottery_goods')->where($where)->count();
		$subject_bom = M('config')->field('value')->where( array('name' => 'subject_baom') )->find();
		
		if($subject_bom['value'] ==0 || $subject_bom['value']> $count)
		{
			return true;
		}else {
			return false;
		}
	}
	
	/**
		提交限时秒杀
	**/
	public function sub_spike()
	{
		$subject_id = I('get.id');
		$data = I('post.goods_ids_arr');
		$result = array('code' => 0);
		
		if( empty($data))
		{
			$result['msg'] = '未选中商品';
			echo json_encode($result);
			die();
		}
		
		$subject = M('spike')->where( array('id' => $subject_id) )->find();
		
		$man_flag = 0;
		$bao_count = 0;
		
		foreach($data as $goods_id)
		{
			if(!$this->check_spike_goods())
			{
				$man_flag = 1;
				break;
			}
			$bao_count++;
			
			$super_data  = array();
	        $super_data['spike_id'] = $subject_id;
	        $super_data['goods_id'] = $goods_id;
	        $super_data['state'] = 0;
	        $super_data['seller_id'] =  SELLERUID;
	        $super_data['begin_time'] = $subject['begin_time'];
	        $super_data['end_time'] = $subject['end_time'];
	        $super_data['addtime'] = time();
	        	
	        $rs = M('spike_goods')->add($super_data);
			
			if($rs) {
				$up_data = array('lock_type' =>'spike');//,'status' => 0
				M('goods')->where( array('goods_id' => $goods_id) )->save( $up_data );
			}
		}
		$result['code'] = 1;
		$result['man_flag'] = $man_flag;
		$result['bao_count'] = $bao_count;
		echo json_encode($result);
		die();
		
	}
	
	public function check_spike_goods()
	{
		//
		$now_time = time();
		$where = " (state=0 or (state =1  and begin_time <{$now_time} and end_time>{$now_time}) ) and seller_id = ".SELLERUID;
		$count = M('spike_goods')->where($where)->count();
		$subject_bom = M('config')->field('value')->where( array('name' => 'subject_baom') )->find();
		
		if($subject_bom['value'] ==0 || $subject_bom['value']> $count)
		{
			return true;
		}else {
			return false;
		}
	}
	
	/**
		提交超值大牌
	**/
	public function sub_superspike()
	{
		$subject_id = I('get.id');
		$data = I('post.goods_ids_arr');
		$result = array('code' => 0);
		
		if( empty($data))
		{
			$result['msg'] = '未选中商品';
			echo json_encode($result);
			die();
		}
		
		$subject = M('super_spike')->where( array('id' => $subject_id) )->find();
		$man_flag = 0;
		$bao_count = 0;
		
		foreach($data as $goods_id)
		{
			if(!$this->check_superspike_goods())
			{
				$man_flag =1;
				break;
			}
			$bao_count++;
			$super_data  = array();
			$super_data['super_spike_id'] = $subject_id;
			$super_data['goods_id'] = $goods_id;
			$super_data['state'] = 0;
			$super_data['seller_id'] = SELLERUID;
			$super_data['begin_time'] = $subject['begin_time'];
			$super_data['end_time']   = $subject['end_time'];
			$super_data['addtime'] = time();
			
			$rs = M('super_spike_goods')->add($super_data);
		
			if($rs) {
				$up_data = array('lock_type' =>'super_spike');//,'status' => 0
				M('goods')->where( array('goods_id' => $goods_id) )->save( $up_data );
			}
		}
		$result['code'] = 1;
		$result['man_flag'] = $man_flag;
		$result['bao_count'] = $bao_count;
		echo json_encode($result);
		die();
		
	}
	
	public function check_superspike_goods()
	{
		//
		$now_time = time();
		$where = " (state=0 or (state =1  and begin_time <{$now_time} and end_time>{$now_time}) ) and seller_id = ".SELLERUID;
		$count = M('super_spike_goods')->where($where)->count();
		$subject_bom = M('config')->field('value')->where( array('name' => 'subject_baom') )->find();
		
		if($subject_bom['value'] ==0 || $subject_bom['value']> $count)
		{
			return true;
		}else {
			return false;
		}
	}
	
	/**
		提交主题活动申请
	**/
	public function sub_subject()
	{
		$subject_id = I('get.id');
		$data = I('post.goods_ids_arr');
		$result = array('code' => 0);
		
		if( empty($data))
		{
			$result['msg'] = '未选中商品';
			echo json_encode($result);
			die();
		}
		
		$subject = M('subject')->where( array('id' => $subject_id) )->find();
		
		//type  begin_time end_time  price
		$bao_count = 0;
		$man_flag = 0;
		foreach($data as $goods_id)
		{
			$super_data  = array();
			
			if(!$this->check_subject_goods())
			{
				$man_flag = 1;
				break;
			}
			$bao_count++;
			$super_data['subject_id'] = $subject_id;
			$super_data['goods_id'] = $goods_id;
			$super_data['state'] = 0;
			$super_data['seller_id '] = SELLERUID;
			$super_data['begin_time'] = $subject['begin_time'];
			$super_data['end_time'] = $subject['end_time'];
			$super_data['addtime'] = time();
		
			$rs = M('subject_goods')->add($super_data);
		
			if($rs) {
				if($subject['type'] =='normal')
				{
					$subject['type'] = 'subject';
				}
				$up_data = array('lock_type' =>$subject['type']);//,'status' => 0
				
				
				M('goods')->where( array('goods_id' => $goods_id) )->save( $up_data );
			}
		}
		$result['code'] = 1;
		$result['man_flag'] = $man_flag;
		$result['bao_count'] = $bao_count;
		echo json_encode($result);
		die();
	}
	
	public function check_subject_goods()
	{
		//
		$now_time = time();
		$where = " (state=0 or (state =1  and begin_time <{$now_time} and end_time>{$now_time}) ) and seller_id = ".SELLERUID;
		$count = M('subject_goods')->where($where)->count();
		$subject_bom = M('config')->field('value')->where( array('name' => 'subject_baom') )->find();
		
		if($subject_bom['value'] ==0 || $subject_bom['value']> $count)
		{
			return true;
		}else {
			return false;
		}
	}
	
	/**
	dan_lottery
	将商品直接加入抽奖活动
	**/
	public function dan_lottery()
	{
		$voucher_list = M('voucher')->where( "store_id=".SELLERUID." and end_time>".time() )->select();
	    $this->voucher_list = $voucher_list;
		$goods_id = I('get.goods_id');
		
		$goods =  M('goods')->field('goods_id,name')->where( array('goods_id' => $goods_id) )->find();
		
		$this->goods = $goods;
		$this->display();
	}
	
	/**
		将商品加入砍价活动
		add_bargain
	**/
	public function add_bargain()
	{
		$goods_id = I('get.goods_id');
		$goods =  M('goods')->field('goods_id,name')->where( array('goods_id' => $goods_id) )->find();
		
		$this->goods = $goods;
		$this->display();
	}
	
	/**
		抽奖活动
	**/
	public function lottery()
	{
		$voucher_list = M('voucher')->where( "store_id=".SELLERUID." and end_time>".time() )->select();
	    $this->voucher_list = $voucher_list;
		
		$this->display();
	}
	/**
		超值大牌
	**/
	public function superspike()
	{
		$model=new SuperSpikeModel();  
		
		$now_time = time();
		$where = " where begin_time< {$now_time} and end_time > {$now_time}";
		
		$data=$model->show_superspike_page( $where );		
		
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出	
		$this->get_status = 4;
		
		$this->display();
	}
	/**
	限时秒杀
	**/
	public function spike()
	{
		$model=new SpikeModel();  
		$now_time = time();
		$where = " where begin_time< {$now_time} and end_time > {$now_time}";
		
		$data=$model->show_spike_page($where);		
		
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出	
		$this->get_status = 5;
		$this->display();
	}
	
	
	/**
	 * 活动商品
	 */
	public function activity()
	{
	    $this->breadcrumb2='活动商品信息';
	    
	    $model=new GoodsModel();
	    
	    $filter=I('get.');
	    
	    
	    $search=array('store_id' => SELLERUID);
	    
	    if(isset($filter['name'])){
	        $search['name']=$filter['name'];
	    }
	    if(isset($filter['category'])){
	        $search['category']=$filter['category'];
	        $this->get_category=$search['category'];
	    }
	    if(isset($filter['status'])){
	        $search['status']=$filter['status'];
	        $this->get_status=$search['status'];
	    }
	    
	    if(isset($filter['type'])){
	        $search['type']=$filter['type'];
	        $this->type=$search['type'];
	    }else {
	        $search['type']='activity';
	        $this->type=$search['type'];
	    }
	    //type
	    
	    $data=$model->show_goods_page($search);
	    
	    $store_bind_class = M('store_bind_class')->where( array('seller_id' => SELLERUID) )->select();
	    
	    $cate_ids = array();
	    foreach($store_bind_class as $val)
	    {
	        if( !empty($val['class_1'])) {
	            $cate_ids[] = $val['class_1'];
	        }
	        if( !empty($val['class_2'])) {
	            $cate_ids[] = $val['class_2'];
	        }
	        if( !empty($val['class_3'])) {
	            $cate_ids[] = $val['class_3'];
	        }
	    }
	    if(empty($cate_ids)) {
	        $this->category = array();
	    } else {
	        $cate_ids_str = implode(',', $cate_ids);
	        $category=M('goods_category')->where( array('id' => array('in',$cate_ids_str)) )->select();
	        $category_tree =list_to_tree($category);
	        $this->category = $category_tree;
	    }
	    
	    foreach($data['list'] as $key => $goods)
	    {
	        $all_comment  =  M('order_comment')->where( array('goods_id' => $goods['goods_id']) )->count();
	        $wait_comment =  M('order_comment')->where( array('state' => 0 ,'goods_id' => $goods['goods_id']) )->count();
	        $goods['all_comment']  = $all_comment;
	        $goods['wait_comment'] = $wait_comment;
	        $data['list'][$key] = $goods;
	    }
	    
	    $this->assign('empty',$data['empty']);// 赋值数据集
	    $this->assign('list',$data['list']);// 赋值数据集
	    $this->assign('page',$data['page']);// 赋值分页输出
	    
	    $this->display();
	}
	
	///Goods/delcomment/id/1
	/**
	 * 删除评论
	 */
	public function delcomment()
	{
	    $id = I('get.id');
	    $goods_id = I('get.goods_id');
	    M('order_comment')->where( array('comment_id' => $id) )->delete();
	    //echo 
	    $result = array(
	        'status'=>'success',
	        'message'=>'删除成功',
	        'jump'=>U('Goods/comment_info', array('id' =>  $goods_id))
	    );
	    $this->osc_alert($result);
	}
	/**
	 * 审核评论
	 */
	public function toggle_comment_state()
	{
	    $comment_id = I('post.comment_id');
	    $order_comment = M('order_comment')->where( array('comment_id' => $comment_id) )->find();
	    //state
	    $state = $order_comment['state'] == 1 ? 0: 1;
	    M('order_comment')->where( array('comment_id' => $comment_id) )->save( array('state' => $state) );
	   echo json_encode( array('code' => 1) );
	   die();
	}
	/**
	 * 商品评论信息
	 */
	public function comment_info()
	{
	    $goods_id = I('get.id');
	    $model=new GoodsModel();
	    $search = array();
	    $search['goods_id'] = $goods_id;
	    $data=$model->show_comment_page($search);
	    
	    $this->assign('empty',$data['empty']);// 赋值数据集
	    $this->assign('list',$data['list']);// 赋值数据集
	    $this->assign('page',$data['page']);// 赋值分页输出
	    $this->display();
	}
	
	public function lotteryinfo()
	{
	    $goods_id = I('get.id',0);
	    $lottery_goods = M('lottery_goods')->where( array('goods_id' =>$goods_id) )->find();
	    
	    if(empty($lottery_goods)){
	        die('非法操作');
	    }//store_id
	    $page = I('get.page',1);
	    $per_page = 4;
	    $offset = ($page - 1) * $per_page;
	    
	    $sql = "select m.uname,m.avatar,p.pin_id,p.lottery_state,o.lottery_win,o.order_id,o.pay_time from ".C('DB_PREFIX')."pin as p,".C('DB_PREFIX')."pin_order as po,
	           ".C('DB_PREFIX')."order as o,".C('DB_PREFIX')."order_goods as og,".C('DB_PREFIX')."member as m 
	               where p.state = 1 and p.pin_id = po.pin_id and po.order_id = o.order_id 
	                and o.order_id = og.order_id and og.goods_id and o.member_id = m.member_id and og.store_id =".SELLERUID." and og.goods_id = {$goods_id}  
	                    order by p.pin_id asc limit {$offset},{$per_page}";
	    
	    $list=M()->query($sql);
	    $this->list = $list;
	    $this->goods_id = $goods_id;
	    $this->lottery_goods = $lottery_goods;
	    
	    if($page>1){
	        $result = array();
	        $result['code'] = 0;
	        if(!empty($list)) {
	            $content = $this->fetch('Goods:lottery_info_fetch');
	            $result['code'] = 1;
	            $result['html'] = $content;
	        }
	       echo json_encode($result);
	       die();
	    }
	    
	    $this->display();
	}
	
	public function openlottery()
	{
	    $goods_id = I('get.id',0);
	    $oids = I('post.oids');
	    $order_model = D('Home/Order');
	    
	    $order_model->open_goods_lottery_order($goods_id,$oids,false);
	    
	    //$order_model->open_goods_lottery_order($goods_id,'',true);
	    //$map['id'] = array('in','1,3,8')
	    
	    echo json_encode( array('code' => 1) );
	    die();
	}
	
	public function lottery_shenqing()
	{
	    $result = array('code' => 0);
	    $goods_id = I('post.goods_id',0);
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }
	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	        $spike_data = array();
	        $spike_data['goods_id'] = $goods_id;
	        $spike_data['state'] = 0;
	        $spike_data['quantity'] = $goods_info['quantity'];
	        $spike_data['begin_time'] = 0;
	        $spike_data['end_time'] = 0;
	        $spike_data['addtime'] = time();
	        $rs = M('lottery_goods')->add($spike_data);
	        if($rs) {
	            M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'lottery') );
	        }
	        $result['code'] = 1;
	        echo json_encode($result);
	        die();
	    } else{
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}
	
	public function xianshimiaosha_shenqing()
	{
	    $result = array('code' => 0);
	    $goods_id = I('post.goods_id',0);
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }
	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	        $spike_data = array();
	        $spike_data['goods_id'] = $goods_id;
	        $spike_data['state'] = 0;
	        $spike_data['quantity'] = $goods_info['quantity'];
	        $spike_data['begin_time'] = 0;
	        $spike_data['end_time'] = 0;
	        $spike_data['addtime'] = time();
	        $rs = M('spike_goods')->add($spike_data);
	        if($rs) {
	            M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'spike') );
	        }
	        $result['code'] = 1;
	        echo json_encode($result);
	        die();
	    } else{
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	    
	}
	
	public function spike_sub()
	{
	    $spike_id = I('post.spike',0);
	    $goods_id = I('post.goods_id',0);
	
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }
	     
	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    $spike_info = M('spike')->where( array('id' => $spike_id) )->find();
	    
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	        $super_data  = array();
	        $super_data['spike_id'] = $spike_id;
	        $super_data['goods_id'] = $goods_id;
	        $super_data['state'] = 0;
	        $super_data['begin_time'] = $spike_info['begin_time'];
	        $super_data['end_time'] = $spike_info['end_time'];
	        $super_data['addtime'] = time();
	        	
	        $rs = M('spike_goods')->add($super_data);
	        	
	        if($rs) {
	            M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'spike') );
	        }
	         
	        $result['code'] = 1;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}
	
	public function chaozhidapai_sub()
	{
		$super_spike_id = I('post.super_spike',0);
		$goods_id = I('post.goods_id',0);
		
		if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }
	    
		$goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
		
		if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	    	$super_data  = array();
	    	$super_data['super_spike_id'] = $super_spike_id;
	    	$super_data['goods_id'] = $goods_id;
	    	$super_data['state'] = 0;
	    	$super_data['begin_time'] = 0; 
	    	$super_data['end_time'] = 0;
			$super_data['addtime'] = time();
			
			$rs = M('super_spike_goods')->add($super_data);
			
			if($rs) {
	            M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'super_spike') );
	        }
	        
			$result['code'] = 1;
	    	echo json_encode($result);
	    	die();
	    } else {
	    	 $result['msg'] = '已存在其他活动中';
	         echo json_encode($result);
	         die();
	    }
	}
	public function oneyuansubject_sub()
	{
	    $subject_id = I('post.subject',0);
	    $goods_id = I('post.goods_id',0);
	    
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }
	     
	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	    
	        $super_data  = array();
	        $super_data['subject_id'] = $subject_id;
	        $super_data['goods_id'] = $goods_id;
	        $super_data['state'] = 0;
	    
	        $super_data['addtime'] = time();
	         
	        $rs = M('subject_goods')->add($super_data);
	         
	        if($rs) {
	            M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'oneyuan') );
	        }
	         
	        $result['code'] = 1;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}
	public function niyuansubject_sub()
	{
	    $subject_id = I('post.subject',0);
	    $goods_id = I('post.goods_id',0);
	     
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }
	    
	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	     
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	         
	        $super_data  = array();
	        $super_data['subject_id'] = $subject_id;
	        $super_data['goods_id'] = $goods_id;
	        $super_data['state'] = 0;
	         
	        $super_data['addtime'] = time();
	    
	        $rs = M('subject_goods')->add($super_data);
	    
	        if($rs) {
	            M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'niyuan') );
	        }
	    
	        $result['code'] = 1;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}
	public function mianfei_sub()
	{
	    $subject_id = I('post.subject',0);
	    $goods_id = I('post.goods_id',0);
	    
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }
	     
	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	        
	        $super_data  = array();
	        $super_data['subject_id'] = $subject_id;
	        $super_data['goods_id'] = $goods_id;
	        $super_data['state'] = 0;
	      
	        $super_data['addtime'] = time();
	        	
	        $rs = M('subject_goods')->add($super_data);
	        	
	        if($rs) {
	            M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'zeyuan') );
	        }
	         
	        $result['code'] = 1;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}
	public function putongsubject_sub()
	{
	    $subject_id = I('post.subject',0);
	    $goods_id = I('post.goods_id',0);
	     
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }
	    
	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	     
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	         
	        $super_data  = array();
	        $super_data['subject_id'] = $subject_id;
	        $super_data['goods_id'] = $goods_id;
	        $super_data['state'] = 0;
	        $super_data['addtime'] = time();
	    
	        $rs = M('subject_goods')->add($super_data);
	    
	        if($rs) {
	            M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'subject') );
	        }
	    
	        $result['code'] = 1;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}
	
	public function haitaosubject_sub()
	{
	    $subject_id = I('post.subject',0);
	    $goods_id = I('post.goods_id',0);
	
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }
	     
	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	
	        $super_data  = array();
	        $super_data['subject_id'] = $subject_id;
	        $super_data['goods_id'] = $goods_id;
	        $super_data['state'] = 0;
	        $super_data['addtime'] = time();
	         
	        $rs = M('subject_goods')->add($super_data);
	         
	        if($rs) {
	            M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'haitao') );
	        }
	         
	        $result['code'] = 1;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}
	public function yiyuan_form()
	{
	    $result = array('code' => 0);
	    $goods_id = I('post.goods_id',0);
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }
	    
	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	         
	        $subject = M('subject')->where('can_shenqing=1 and type="oneyuan"')->select();
	        $this->subject = $subject;
	        $this->goods_id = $goods_id;
	         
	        $content = $this->fetch('Goods:goods_oneyuansubject_fetch');
	        $result['code'] = 1;
	        $result['html'] = $content;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}
	public function jiukuaijiu_form()
	{
	    $result = array('code' => 0);
	    $goods_id = I('post.goods_id',0);
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }
	     
	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	    
	        $subject = M('subject')->where('can_shenqing=1 and type="niyuan"')->select();
	        $this->subject = $subject;
	        $this->goods_id = $goods_id;
	    
	        $content = $this->fetch('Goods:goods_niyuansubject_fetch');
	        $result['code'] = 1;
	        $result['html'] = $content;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}
	
	public function lottery_form()
	{
		$result = array('code' => 0);
	    $goods_id = I('post.goods_id',0);
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }
	    
	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	         
	        $voucher_list = M('voucher')->where( "store_id=".SELLERUID." and begin_time>".time() )->select();
	        $this->voucher_list = $voucher_list;
	        $this->goods_id = $goods_id;
	         
	        $content = $this->fetch('Goods:goods_lottery_fetch');
	        $result['code'] = 1;
	        $result['html'] = $content;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}
	
	public function lottery_sub()
	{
		$voucher_id = I('post.voucher_id',0);
	    $goods_id = I('post.goods_id',0);
	    $win_quantity = I('post.win_quantity',0); 
	    $is_auto_open = I('post.is_auto_open',0);
	    $real_win_quantity = I('post.real_win_quantity',0);
	    
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }
	    
		if($voucher_id == 0){
	        $result['msg'] = '请选择退款时赠送的优惠券';
	        echo json_encode($result);
	        die();
	    }
	    
	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	     
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	         
	        $spike_data = array();
	        $spike_data['goods_id'] = $goods_id;
	        $spike_data['state'] = 0;
	        $spike_data['is_open_lottery'] = 0;
	        $spike_data['voucher_id'] = $voucher_id;
	        $spike_data['win_quantity'] = $win_quantity;
	        $spike_data['is_auto_open'] = $is_auto_open;
	        $spike_data['real_win_quantity'] = $real_win_quantity;
	        $spike_data['quantity'] = $goods_info['quantity'];
	        $spike_data['begin_time'] = 0;
	        $spike_data['end_time'] = 0;
	        $spike_data['addtime'] = time();
	        $rs = M('lottery_goods')->add($spike_data);
	        if($rs) {
	            M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'lottery') );
	        }
	        $result['code'] = 1;
	        echo json_encode($result);
	        die();
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	    
	}
	public function putongsubject_form()
	{
	    $result = array('code' => 0);
	    $goods_id = I('post.goods_id',0);
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }
	    
	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	         
	        $subject = M('subject')->where('can_shenqing=1 and type="normal"')->select();
	        $this->subject = $subject;
	        $this->goods_id = $goods_id;
	         
	        $content = $this->fetch('Goods:goods_putongsubject_fetch');
	        $result['code'] = 1;
	        $result['html'] = $content;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}
	public function haitaosubject_form()
	{
	    $result = array('code' => 0);
	    $goods_id = I('post.goods_id',0);
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }
	     
	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	    
	        $subject = M('subject')->where('can_shenqing=1 and type="haitao"')->select();
	        $this->subject = $subject;
	        $this->goods_id = $goods_id;
	    
	        $content = $this->fetch('Goods:goods_haitaosubject_fetch');
	        $result['code'] = 1;
	        $result['html'] = $content;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}
	public function mianfeishiyong_form()
	{
	    $result = array('code' => 0);
	    $goods_id = I('post.goods_id',0);
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }
	     
	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	        
	        $subject = M('subject')->where('can_shenqing=1 and type="zeyuan"')->select();
	        $this->subject = $subject;
	        $this->goods_id = $goods_id;
	    
	        $content = $this->fetch('Goods:goods_mianfeishiyong_fetch');
	        $result['code'] = 1;
	        $result['html'] = $content;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}
	public function chaozhidapai_form()
	{
		$result = array('code' => 0);
	    $goods_id = I('post.goods_id',0);
		if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }
	    
	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	    	$super_spike_list = M('super_spike')->where('begin_time>'.time())->select();
	    	$this->super_spike_list = $super_spike_list;
	    	$this->goods_id = $goods_id;
	    	
	    	$content = $this->fetch('Goods:goods_chaozhidapai_fetch');
	    	$result['code'] = 1;
	    	$result['html'] = $content;
	    	echo json_encode($result);
	    	die();
	    } else {
	    	 $result['msg'] = '已存在其他活动中';
	         echo json_encode($result);
	         die();
	    }
	    
	}
	public function spike_form()
	{
	    $result = array('code' => 0);
	    $goods_id = I('post.goods_id',0);
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }
	     
	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	        $spike_list = M('spike')->where()->select();
	        //$spike_list = M('spike')->where('begin_time>'.time())->select();
	       
	        $this->spike_list = $spike_list;
	        $this->goods_id = $goods_id;
	
	        $content = $this->fetch('Goods:goods_spike_fetch');
	        $result['code'] = 1;
	        $result['html'] = $content;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	     
	}
	
	public function get_json_category_tree($pid,$is_ajax=0)
	{
	   // {pid:pid,is_ajax:1}
	   $pid = empty($_GET['pid']) ? 0: intval($_GET['pid']);
	   $is_ajax = empty($_GET['is_ajax']) ? 0:intval($_GET['is_ajax']);
	   $goods_cate_model = D('Seller/GoodsCategory');
	   $list = $goods_cate_model->get_parent_cateory($pid,SELLERUID);
	   $result = array();
	   if($is_ajax ==0)
	   {
	       return $list;
	   } else {
	       if(empty($list)){
	           $result['code'] = 0;
	       } else {
	           $result['code'] = 1;
	           $result['list'] = $list;
	       }
	       echo json_encode($result);
	       die();
	   }
	   
	}
	function add(){
	    $model=new GoodsModel();
		if(IS_POST){
		
			$data=I('post.');
			$data['goods_description']['tag'] = str_replace('，', ',', $data['goods_description']['tag']);
			
			$data['store_id']=SELLERUID;
			
			if($this->goods_is_shenhe()) {
				$data['status'] = 2;
			}
			
			$return=$model->add_goods($data);			
			$this->osc_alert($return);
		}
		
		$parent_area = M('area')->where( array('area_parent_id' => 0) )->order('area_sort asc ,area_id asc')->select();
		foreach($parent_area as $key => $val)
		{
		    $child_ren = M('area')->where( array('area_parent_id' => $val['area_id']) )->order('area_sort asc ,area_id asc')->select();
		    $val['child'] = $child_ren;
		    $parent_area[$key] = $val;
		}
		$this->parent_area = $parent_area;
		
		//库存状态
		$this->stock_status=M('StockStatus')->select();
		
		$this->cate_data = $this->get_json_category_tree(0);
		$this->action=U('Goods/add');
		$this->crumbs='新增';
		$this->display('edit');
	}
	
	/**
	商品是否需要审核
	**/
	function goods_is_shenhe()
	{
		$shenhegoods = M('config')->where( array('name' => 'shenhegoods') )->find();
		
		$is_need_shen = 0;
		
		if(!empty($shenhegoods)) {
			$is_need_shen = $shenhegoods['value'];
		}
		return $is_need_shen;
	}
	function edit(){
		$model=new GoodsModel();  
		
		$cate_data = $this->get_json_category_tree(0);
		
		if(IS_POST){
			
			$data=I('post.');
			
			if($this->goods_is_shenhe()) {
				$data['status'] = 2;
			}
			
			$data['goods_description']['tag'] = str_replace('，', ',', $data['goods_description']['tag']);
			$data['store_id']=SELLERUID;
			$return=$model->edit_goods($data);		
		
			$this->osc_alert($return);
		}
		
		
		
		$goods_area = M('goods_area')->where( array('goods_id' => I('id')) )->find();
		if(!empty($goods_area)) {
		    $goods_area['area_ids'] =unserialize( $goods_area['area_ids_text']);
		}
		$this->goods_area=$goods_area;
		
		$parent_area = M('area')->where( array('area_parent_id' => 0) )->order('area_sort asc ,area_id asc')->select();
		foreach($parent_area as $key => $val)
		{
		    $child_ren = M('area')->where( array('area_parent_id' => $val['area_id']) )->order('area_sort asc ,area_id asc')->select();
		    $val['child'] = $child_ren;
		    $parent_area[$key] = $val;
		}
		$this->parent_area = $parent_area;
		
		$this->crumbs='编辑';		
		$this->action=U('Goods/edit');
		$this->description=M('goods_description')->find(I('id'));		
		//库存状态
		$this->stock_status=M('StockStatus')->select();
		
		$this->goods=$model->get_goods_data(I('id'));
		
		$this->goods_images=$model->get_goods_image_data(I('id'));
		
		$this->goods_discount=M('goods_discount')->where(array('goods_id'=>I('id')))->order('quantity ASC')->select();
		
		$this->goods_categories=$model->get_goods_category_data(I('id'));
		//transport_id
		if($this->goods['transport_id'] > 0)
		{
		    $this->transport = D('Seller/Transport')->getTransportInfo(array('id' => $this->goods['transport_id']));
		}
		//var_dump($this->transport);die();
		
		$this->goods_options=$model->get_goods_options(I('id'));
		//dump($this->goods_options);die;
		$option_model=new \Admin\Model\OptionModel();
		//选项值
		foreach ($this->goods_options as $goods_option) {
				$option_values[$goods_option['option_id']] = $option_model->getOptionValues($goods_option['option_id']);
		}		
		//dump($this->goods_options);
		//dump($option_values);die;
		$this->option_values=$option_values;
		
		$this->assign('cate_data',$cate_data);// 赋值数据集
		$this->display('edit');		
	}
	
	function copy_goods(){
		$id =I('id');
		$model=new GoodsModel();  
		if($id){		
			foreach ($id as $k => $v) {						
				$model->copy_goods($v);
			}	
			$data['redirect']=U('Goods/index');		
			$this->ajaxReturn($data);
			die;
		}
	}

	function del(){
		$model=new GoodsModel();  
		$return=$model->del_goods(I('get.id'));			
		$this->osc_alert($return); 	
	}	
}
?>