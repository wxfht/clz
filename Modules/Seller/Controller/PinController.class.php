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
use Seller\Model\PinModel;
use Admin\Model\GoodsModel;
class PinController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='拼团管理';
		$this->breadcrumb2='拼团商品管理';
		
	}
	
	public function index(){
	    
	        $model=new GoodsModel();
	       
	        $filter=I('get.');
	        
	        $search=array( 'customer_id' => SELLERUID );
			
			if(isset($filter['name'])){
				$search['name']=$filter['name'];		
			}
	         
	        $data=$model->show_pingoods_page($search);
	       
	        $this->assign('empty',$data['empty']);// 赋值数据集
	        $this->assign('list',$data['list']);// 赋值数据集
	        $this->assign('page',$data['page']);// 赋值分页输出
	        
	        $this->display();
	}
	
	public function newman_activity()
	{
		$id = I('post.pin_goods_id');
		M('pin_goods')->where( array('id' => $id) )->save( array('type' => 'newman') );
		
		echo json_encode( array('code' => 1) );
		die();
	}
	
	public function pinlist(){
	    $model=new PinModel();
	
	    $filter=I('get.');
	    $state = I('get.state', -1);
	    $name = I('get.name', '');
	
	    $search=array('store_id' => SELLERUID,'state' => $state,'name' => $name);
	
	    $data=$model->show_order_page($search);
	
	    $this->state = $state;
	    $this->assign('empty',$data['empty']);// 赋值数据集
	    $this->assign('list',$data['list']);// 赋值数据集
	    $this->assign('page',$data['page']);// 赋值分页输出
	   
	    $this->display();
	}
	
	
	public function show_order(){
	     
	    $this->crumbs='拼团详情';
	    $pin_id = I('get.pin_id');
	     
	    $pin_info = M('pin')->where( array('pin_id' => $pin_id) )->find();
	    if($pin_info['state'] == 0 && $pin_info['end_time'] <time()) {
	        $pin_info['state'] = 2;
	    }
			
		//
			
			if( empty($pin_info['qrcode']) )
			{
				//qrcode
				$jssdk = new \Lib\Weixin\Jssdk( C('weprogram_appid'), C('weprogram_appscret') );
				//$weqrcode = $jssdk->getAllWeQrcode('pages/store/index','5');
				
				$weqrcode = $jssdk->getAllWeQrcode('pages/share/index',$pin_info['order_id'] );
				
				//保存图片
				
				$image_dir = ROOT_PATH.'Uploads/image/goods';
				$image_dir .= '/'.date('Y-m-d').'/';
				
				$file_path = C('SITE_URL').'Uploads/image/goods/'.date('Y-m-d').'/';
				$kufile_path = $dir.'/'.date('Y-m-d').'/';
				
				RecursiveMkdir($image_dir);
				$file_name = md5('qrcode_'.$pick_order_info['pick_sn'].time()).'.png';
				//qrcode
				file_put_contents($image_dir.$file_name, $weqrcode);		
				
				M('pin')->where( array('pin_id' => $pin_id) )->save( array('qrcode' => $file_path.$file_name) );
				$this->qrcode = $file_path.$file_name;
			}else{
				$this->qrcode = $pin_info['qrcode'];
			}
			


	    $this->pin_info = $pin_info;
	     
		 $jiapinorder = array();
		
		if($pin_info['is_jiqi'] == 1)
		{
			$jiapinorder = M('jiapinorder')->where( array('pin_id' => $pin_id) )->select();
			
		}
		$this->jiapinorder = $jiapinorder;
		
	     
	    $sql = "select o.order_num_alias,o.total,o.order_id,o.name,o.telephone,o.shipping_name,o.shipping_tel,o.shipping_city_id,
	 	         o.shipping_country_id,o.shipping_province_id,o.shipping_address,o.date_added,o.order_status_id,
	        og.goods_id,og.name as goods_name,og.goods_images,og.name as goods_name,og.quantity,og.price,og.total as atotal,o.shipping_fare    
	 	         from ".C('DB_PREFIX')."order as o,".C('DB_PREFIX')."order_goods as og,".C('DB_PREFIX')."pin_order as p  
		 	         where o.order_status_id !=3 and  o.order_id = og.order_id and o.order_id = p.order_id and p.pin_id ={$pin_id}";
	    $sql.=' ORDER BY o.order_id desc ';
	     
	    $list = M()->query($sql);
		
	    foreach($list as $key => $val)
	    {
	        $province_info =  M('area')->where( array('area_id' =>$val['shipping_province_id'] ) )->find();
	        $city_info =  M('area')->where( array('area_id' =>$val['shipping_city_id'] ) )->find();
	        $country_info =  M('area')->where( array('area_id' =>$val['shipping_country_id'] ) )->find();
	
	        $val['province_name'] = $province_info['area_name'];
	        $val['city_name'] = $city_info['area_name'];
	        $val['area_name'] = $country_info['area_name'];
	
	
	        $list[$key] = $val;
	    }
	     
	    $pin_buy_sql = "select count(o.order_id) as count from ".C('DB_PREFIX')."pin_order as p,".C('DB_PREFIX')."order as o,".C('DB_PREFIX')."order_goods as og
	    where p.order_id= o.order_id and p.order_id = og.order_id and p.pin_id = {$pin_id}  and o.order_status_id in(1,2,4,6,7,8,9,10)
	    ";
	    $pin_buy_count_arr = M()->query($pin_buy_sql);
	    
	    $pin_buy_count = $pin_buy_count_arr[0]['count'];
	    
		$pin_jia_count =  M('jiapinorder')->where( array('pin_id' => $pin_id) )->count();
		   
	     
	    $order = current($list);
	    //$goods_info = M('goods')->where( array('goods_id' => $order['goods_id']) )->find();
	     
	    $goods_images=resize($order['goods_images'], 50,50);
	     
	    $hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
	    $order_id = $hashids->encode($order['order_id']);
	     
	    $config_info = M('config')->where( array('name' => 'SITE_URL') )->find();
	     
	    $pin_url = $config_info['value'].'/index.php?s=/group/info/group_order_id/'.$order_id.'.html';
	    $this->pin_url = $pin_url;
	     
	    $order_status_list = M('order_status')->select();
	    $order_status_arr = array();
	    foreach($order_status_list as $val)
	    {
	        $order_status_arr[$val['order_status_id']] = $val['name'];
	    }
	     
	    $this->order_status_arr = $order_status_arr;
	    $this->list = $list;
	    $this->pin_buy_count = $pin_buy_count + $pin_jia_count;
	    $this->goods_images = $goods_images;
	    $this->order = $order; 
	    $this->display('show');
	}
	
	public function edit_goods()
	{
	    $id = I('get.id');
	    
	    $pin_goods = M('pin_goods')->where( array('id' => $id) )->find();
	    //goods_id
	    
	    $goods_id = $pin_goods['goods_id'];
	    $this->pin_goods = $pin_goods;
	     
	    $goods_info = M('goods')->field('name,goods_id,price,danprice')->where( array('goods_id' => $goods_id) )->find();
	     
	    $model=new GoodsModel();
	    $this->goods_options=$model->get_goods_options($goods_id, SELLERUID);
	     
	    $goods_option_mult_value = M('goods_option_mult_value')->where( array('goods_id' => $goods_id ) )->select();
	    $goods_option_mult_str = '';
	    
	    if( !empty($goods_option_mult_value) )
	    {
	        $goods_option_mult_arr = array();
	        foreach($goods_option_mult_value as $key => $val)
	        {
	            $goods_option_mult_arr[] = 'mult_id:'.$val['rela_goodsoption_valueid'].'@@mult_qu:'.$val['quantity'].'@@mult_image:'.$val['image'];
	            //option_value  option_value_id  value_name
	            $option_name_arr = explode('_', $val['rela_goodsoption_valueid']);
	            $option_name_list = array();
	    
	    
	            foreach($option_name_arr as $option_value_id_tp)
	            {
	                $tp_op_val_info =M('option_value')->where( array('option_value_id' => $option_value_id_tp) )->find();
	                $option_name_list[] = $tp_op_val_info['value_name'];
	            }
	    
	            $val['option_name_list'] = $option_name_list;
	            $goods_option_mult_value[$key] = $val;
	        }
	        $goods_option_mult_str = implode(',', $goods_option_mult_arr);
	    }
	    
	    $this->goods_option_mult_value = $goods_option_mult_value;
	    $this->goods_option_mult_str = $goods_option_mult_str;
	    $this->goods_info = $goods_info;
	     
	    $result = array();
	    $result['html'] = $this->fetch('Goods:goods_option_fetch');
	     
	    echo json_encode($result);
	    die();
	    
	}
	
	public function del_goods()
	{
		$id = I('get.id');
		$pin_goods_info = M('pin_goods')->field('type,goods_id')->where( array('id' => $id) )->find();
		
		//type goods_id
		//pin C('DB_PREFIX')
		//p.order_id p.state=0,p.end_time , po. pin_order
		$sql ="select p.pin_id ,p.state,og.goods_id from ".C('DB_PREFIX')."pin as p left join ".C('DB_PREFIX')."pin_order as po on p.order_id = po.order_id 
				left join ".C('DB_PREFIX')."order_goods as og on og.order_id = po.order_id where p.state =0 and og.goods_id = ".$pin_goods_info['goods_id'];
		
		$pin_list =  M()->query($sql);
		
		$id_arr = array();
		foreach($pin_list as $val)
		{
			$id_arr[] = $val['pin_id'];
		}
		if( !empty($id_arr) )
		{
			M('pin')->where( array( 'pin_id' => array('in', $id_arr) ) )->save( array('end_time' => time() ) );
		}
		
		if( $pin_goods_info['type'] == 'lottery' )
		{
			//抽奖
			M('lottery_goods')->where( array('goods_id' => $pin_goods_info['goods_id'] ) )->delete();
		}
		M('goods')->where( array('goods_id' => $pin_goods_info['goods_id']) )->save( array('type' => 'normal','lock_type' => 'normal') );
		
		M('pin_goods')->where( array('id' => $id) )->delete();
		
		echo json_encode( array('code' => 1) );
		die();
	}
	
	public function addGoods()
	{
	    $this->display();
	}
	public function modify_pin()
	{
	    $data = I('post.');
	     
		 /**
array(1) {
  ["goods_ids_arr"]=>
  array(1) {
    [0]=>
    string(173) "73,0.01,2,69_pin_price:0.01@@70_pin_price:0.08,69_price:0.05@@70_price:0.04,69_quantity:80@@70_quantity:110,normal,2018/03/13 0:00:00,2018/03/16 0:00:00,10.00,
	100_100_100_0_"
  }
}


**/
	    if(empty($data))
	    {
	        $result = array('code' =>0,'msg' =>'请选择商品');
	        echo json_encode($result);
	        die();
	    }
	     
	    //var goods_str = goods_id+','+pin_price+','+pin_count+','+option_pin_price+','+option_price+','+option_quanty;
	   
	    foreach($data['goods_ids_arr'] as $goods_data)
	    {
	        $goods_info = explode(',', $goods_data);
	        
	       
	        //string(172) "18,18.00,0,  36_33_pin_price:2@@37_33_pin_price:2@@38_33_pin_price:2,  36_33_price:1.00@@37_33_price:2.00@@38_33_price:3.00,36_33_quantity:2@@37_33_quantity:2@@38_33_quantity:2"
	        
	        
	        $pin_goods = array();
	        
	        $pin_goods['pin_price'] = $goods_info[1];
	        $pin_goods['pin_count'] = $goods_info[2];
	        $pin_goods['type'] = $goods_info[6];
			//+begin_time+','+end_time
			$pin_goods['begin_time'] = strtotime( $goods_info[7]);
			$pin_goods['end_time'] =  strtotime( $goods_info[8]) ;
			$pin_goods['pin_hour'] =  floatval( $goods_info[9]) ;
			
			$commiss_money_arr  =  explode('_', $goods_info[10]);
			
			
			$pin_goods['commiss_one_pin_disc'] = $commiss_money_arr[0];
			$pin_goods['commiss_two_pin_disc'] = $commiss_money_arr[1];
			$pin_goods['commiss_three_pin_disc'] = $commiss_money_arr[2];
			
			//lottery
			$pin_goods_info =  M('pin_goods')->where( array('goods_id' => $goods_info[0]) )->find();
			if( $pin_goods_info['type'] == 'lottery' )
			{
				unset($pin_goods['type']);
				M('lottery_goods')->where( array('goods_id' => $goods_info[0] ) )->save( array('begin_time' => $pin_goods['begin_time'] , 'end_time' => $pin_goods['end_time'])  );
			}else{
				M('goods')->where( array('goods_id' => $goods_info[0]) )->save( array('type' => 'pintuan') );
			}
			
			
	        M('pin_goods')->where( array('goods_id' => $goods_info[0]) )->save($pin_goods);
			
			
	        
	        $price_arr = array('pin_price' =>$goods_info[1],'pin_count' => $goods_info[2]);
	        S($this->customer_id.'goods_price_cache'.$goods_info[0], $price_arr);
	        $quantity  = 0;
	        
	        if( !empty($goods_info[3]) )
	        {
	            $option_pin_price_arr = explode('@@',$goods_info[3]);
	            foreach($option_pin_price_arr as $pin_price_val)
	            {
	                $price_val = explode(':', $pin_price_val);
	                $option_mult_id_arr = explode('_pin',$price_val[0]);
	                $rela_goodsoption_valueid = $option_mult_id_arr[0];
	                $option_pin_price = $price_val[1];
	                
	                M('goods_option_mult_value')->where( array('rela_goodsoption_valueid' => $rela_goodsoption_valueid,'goods_id' => $goods_info[0]) )
	                ->save( array('pin_price' => $option_pin_price) );
	            }
	        }
	         
	        if( !empty($goods_info[4]) )
	        {


	            $option_price_arr = explode('@@',$goods_info[4]);
	            foreach($option_price_arr as $price_val)
	            {
	                $price_val = explode(':', $price_val);
	                $option_mult_id_arr = explode('_price',$price_val[0]);
	                $rela_goodsoption_valueid = $option_mult_id_arr[0];
	                 
	                $option_price = $price_val[1];
				
	                $c = M('goods_option_mult_value')->where( array('rela_goodsoption_valueid' => $rela_goodsoption_valueid,'goods_id' => $goods_info[0]) )
	                ->save( array('dan_price' => $option_price) );
					
					
	            }
	        }
	         
	        if( !empty($goods_info[5]) )
	        {
	            $option_quantity_arr = explode('@@',$goods_info[5]);
	            foreach($option_quantity_arr as $quantity_val)
	            {
	                $quantity_val = explode(':', $quantity_val);
	                $option_mult_id_arr = explode('_quantity',$quantity_val[0]);
	                $rela_goodsoption_valueid = $option_mult_id_arr[0];
	
	                $option_quantity = $quantity_val[1];
	                
	                $quantity = $quantity+$option_quantity;
	                M('goods_option_mult_value')->where( array('rela_goodsoption_valueid' => $rela_goodsoption_valueid,'goods_id' => $goods_info[0]) )
	                ->save( array('quantity' => $option_quantity) );
	            }
	        }
	       
	        if($quantity > 0)
	        {
	            M('goods')->where( array('goods_id' => $goods_info[0]) )->save( array('quantity' => $quantity) );
	        }
	         
	    }
	     
	    echo json_encode(array('code'=>1));
	    die();
	}
	
	public function sub_pin()
	{
	    $data = I('post.');
	    
		$begin_time = $data['begin_time'];
		$end_time = $data['end_time'];
		
		
	    if(empty($data))
	    {
	        $result = array('code' =>0,'msg' =>'请选择商品');
	        echo json_encode($result);
	        die();
	    }
	    
	    //var goods_str = goods_id+','+pin_price+','+pin_count+','+option_pin_price+','+option_price+','+option_quanty;
		//','+rel_pin_hour+','+rel_type
	    foreach($data['goods_ids_arr'] as $goods_data)
	    {
	        $goods_info = explode(',', $goods_data);
	        
			//避免二次开团
			M('pin_goods')->where( array('goods_id' => $goods_info[0]) )->delete();
			
	        $pin_goods = array();
	        $pin_goods['goods_id'] = $goods_info[0];
	        $pin_goods['customer_id'] = SELLERUID;
	        $pin_goods['pin_price'] = $goods_info[1];
	        $pin_goods['pin_count'] = $goods_info[2];
	        $pin_goods['pin_hour'] = $goods_info[6];
	        $pin_goods['type'] = $goods_info[7];
			
	        $pin_goods['begin_time'] = strtotime($begin_time);
	        $pin_goods['end_time'] = strtotime($end_time);
			
			
			//begin_time  end_time
				
	        $pin_goods['addtime'] = time();
	        M('pin_goods')->add($pin_goods);
	        
	        $price_arr = array('pin_price' =>$goods_info[1],'pin_count' => $goods_info[2]);
	        S($this->customer_id.'goods_price_cache'.$goods_info[0], $price_arr);
	        
	        M('goods')->where( array('goods_id' => $pin_goods['goods_id']) )->save( array('type' => 'pintuan') );
			
			//if($pin_goods['type'] == 'newman')
			//{
			//	M('goods')->where( array('goods_id' => $pin_goods['goods_id']) )->save( array('type' => 'newman') );
	        //}
			
	        if( !empty($goods_info[3]) )
	        {
	            $option_pin_price_arr = explode('@@',$goods_info[3]);
	            foreach($option_pin_price_arr as $pin_price_val)
	            {
	                $price_val = explode(':', $pin_price_val);
	                $option_mult_id_arr = explode('_pin',$price_val[0]);
	                $rela_goodsoption_valueid = $option_mult_id_arr[0];
	                $option_pin_price = $price_val[1];
	                M('goods_option_mult_value')->where( array('rela_goodsoption_valueid' => $rela_goodsoption_valueid,'goods_id' => $pin_goods['goods_id']) )
	                ->save( array('pin_price' => $option_pin_price) );
	            }
	        }
	        
	        if( !empty($goods_info[4]) )
	        {
	            $option_price_arr = explode('@@',$goods_info[4]);
	            foreach($option_price_arr as $price_val)
	            {
	                $price_val = explode(':', $price_val);
	                $option_mult_id_arr = explode('_price',$price_val[0]);
	                $rela_goodsoption_valueid = $option_mult_id_arr[0];
	                
	                $option_price = $price_val[1];
	                M('goods_option_mult_value')->where( array('rela_goodsoption_valueid' => $rela_goodsoption_valueid,'goods_id' => $pin_goods['goods_id']) )
	                ->save( array('dan_price' => $option_price) );
	            }
	        }
	        $quantity = 0;
	        if( !empty($goods_info[5]) )
	        {
	            $option_quantity_arr = explode('@@',$goods_info[5]);
	            foreach($option_quantity_arr as $quantity_val)
	            {
	                $quantity_val = explode(':', $quantity_val);
	                $option_mult_id_arr = explode('_quantity',$quantity_val[0]);
	                $rela_goodsoption_valueid = $option_mult_id_arr[0];
	                 
	                $option_quantity = $quantity_val[1];
	                $quantity = $quantity + $option_quantity;
	                M('goods_option_mult_value')->where( array('rela_goodsoption_valueid' => $rela_goodsoption_valueid,'goods_id' => $pin_goods['goods_id']) )
	                ->save( array('quantity' => $option_quantity) );
	            }
	        }
	        
	        if($quantity > 0)
	        {
	            M('goods')->where( array('goods_id' => $pin_goods['goods_id']) )->save( array('quantity' => $quantity) );
	        }
	        
	    }
	    
	    echo json_encode(array('code'=>1));
	    die();
	}
	public function jia_over_order()
	{
		$pin_id = I('get.pin_id');
		$pin_model = D('Home/Pin');
		
		$pin_info = M('pin')->where( array('pin_id' => $pin_id) )->find();
		$buy_count =  $pin_model->get_tuan_buy_count($pin_id);
		
		$del_count = $pin_info['need_count'] - $buy_count;
		
		if($del_count > 0)
		{
			$jia_list =  M('jiauser')->order(' rand() desc ')->limit($del_count)->select();
			
			foreach($jia_list as $jia_member)
			{
				$tmp_arr = array();
				//jiapinorder
				$tmp_arr['pin_id'] = $pin_id;
				$tmp_arr['uname'] = $jia_member['username'];
				$tmp_arr['avatar'] = $jia_member['avatar'];
				$tmp_arr['order_sn'] = build_order_no($pin_id);;
				$tmp_arr['mobile'] = $jia_member['mobile'];
				$tmp_arr['addtime'] = time() + mt_rand(60,120);
				
				M('jiapinorder')->add($tmp_arr);
			}
		}
		
		//need_count
		
		$pin_model->updatePintuanSuccess($pin_id);
		//
		M('pin')->where( array('pin_id' => $pin_id) )->save( array('is_jiqi' => 1) );
		$return = array(
				'status'=>'success',
				'message'=>'操作成功',
				'jump'=>U('Pin/pinlist', array('state' => 1) )
			);

		$this->osc_alert($return);
	}
	public function buy()
	{
	    if($this->has_plugin)
	    {
	        $return = array(
				'status'=>'success',
				'message'=>'操作成功',
				'jump'=>U('Pin/index')
			);
		
		    $this->osc_alert($return);
	    }else {
	        $pin_plugin =  M('plugin_list')->where( array('plugin_uname' => 'pin') )->find();
	        //money
	        
	        if($pin_plugin['money'] <= 0)
	        {
	            //free
	            $order_data = array();
	            $order_data['plugin_uname'] = 'pin';
	            $order_data['customer_id'] = UID;
	            $order_data['state'] = 0;
	            $order_data['money'] = $pin_plugin['money'];
	            $order_data['val_end_time'] = time() + (86400*36500);
	            $order_data['addtime'] = time();
	            //val_end_time
	            M('plugin_order')->add($order_data);
	            $order_id = M('plugin_order')->getLastInsID();
	            
	            $plugin_model = D('Website/PluginsSlider');
	            
	            $plugin_model->modify_plugin_order_state($order_id);
	            
	            $return = array(
	                'status'=>'success',
	                'message'=>'操作成功',
	                'jump'=>U('Pin/index')
	            );
	            
	            $this->osc_alert($return);
	        }else {
	            
	            //TODO load pay js ....
	        }
	    }
	}
	
	public function guobie()
	{
		$model=new GoodsModel();
		$data=$model->show_guobie_page();	
		
		$this->breadcrumb2='海淘国别';
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出	
		$this->display();
	}
	
	public function editguobie()
	{
		$id = I('get.id');
		
		$guobie = M('guobie')->where( array('id' =>$id) )->find();
		
		$this->guobie = $guobie;
		$this->breadcrumb2='海淘国别';
		$this->display('addguobie');
		
	}
	
	public function get_json_category_tree($pid,$is_ajax=0)
	{
	   // {pid:pid,is_ajax:1}
	   $pid = empty($_GET['pid']) ? 0: intval($_GET['pid']);
	   $is_ajax = empty($_GET['is_ajax']) ? 0:intval($_GET['is_ajax']);
	   
	   $list =  M('goods_category')->field('id,pid,name')->where( array('pid'=>$pid,'customer_id'=>UID) )->order('sort_order asc')->select();
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
	
	
	function del(){
		$model=new GoodsModel();  
		$return=$model->del_goods(I('get.id'));			
		$this->osc_alert($return); 	
	}	
}
?>