<?php
namespace Home\Model;

/**
 * 拼团模型模型
 * @author fish
 *
 */
class CarModel {
private $data = array();
	
	
	public function get_wecartall_goods($goods_id, $sku_str, $community_id,$token,$car_prefix='cart.')
	{
		
		if( $community_id <=0 )
		{
			return 0;
		}
		
		$key = (int)$goods_id . ':'.$community_id.':';
       
        $key = $car_prefix . $key;
		
		
		$s_arr = M('lionfish_comshop_car')->field('format_data')->where( " token='{$token}' and carkey like '%{$key}%' " )->select();
		
		$quantity = 0;
		
		foreach($s_arr as $val)
		{
			$tmp_format_data = unserialize($val['format_data']);
			
			$quantity += $tmp_format_data['quantity'];
		}
		
		return $quantity;
	}
	
	public function get_wecart_goods($goods_id, $sku_str, $community_id,$token,$car_prefix='cart.')
	{
		
		if( $community_id <=0 )
		{
			return 0;
		}
		
		
		$key = (int)$goods_id . ':'.$community_id.':';
       
        if ($sku_str) {
            $key.= base64_encode($sku_str) . ':';
        } else {
            $key.= ':';//xx
        }
        $key = $car_prefix . $key;
		
		
		$car_param = array();
		$car_param[':uniacid'] = $_W['uniacid'];
		$car_param[':token'] = $token;
		$car_param[':carkey'] = $key;
		
		//C('DB_PREFIX')
		
		$s_arr = M('lionfish_comshop_car')->field('format_data')->where( array('carkey' => $key,'token' => $token) )->find();
		
		$tmp_format_data = unserialize($s_arr['format_data']);
		
		return $tmp_format_data['quantity'];
	}
	
	//得到商品数量
	public function get_goods_quantity($goods_id) {
		
		$quantity = D('Seller/Redisorder')->get_goods_total_quantity($goods_id);
		if($quantity <= 0)
		{
			$info = M('lionfish_comshop_goods')->field('total as quantity')->where(array('id' =>$goods_id))->find();
			$quantity = $info['quantity'];
		}
		return $quantity;
	}
	
	 public function addwecar($token, $goods_id, $format_data = array() , $option, $community_id,$car_prefix='cart.') {
		
        $key = (int)$goods_id . ':'.$community_id.':';
        $qty = $format_data['quantity'];
        if ($option) {
            $key.= base64_encode($option) . ':';
        } else {
            $key.= ':'; 
        }
		
		
		if( $format_data['soli_id'] > 0 )
		{
			//如果是群接龙，移除群接龙以外的购物车内容begin
			
			//移除购物车以外的内容end
		}
		
		if( $format_data['is_just_addcar'] == 0 )
		{
			
			$cart_sql = "select * from ".C('DB_PREFIX')."lionfish_comshop_car  
						where token='{$token}'  and carkey like '".$car_prefix."%' ";
			$all_cart = M()->query($cart_sql);
			
			
			
			
			if(!empty($all_cart))
			{
				foreach($all_cart as $val)
				{
					$tmp_format_data = unserialize($val['format_data']);
					$tmp_format_data['singledel'] = 0;
					
					$tmp_format_data_new = array('format_data' => serialize($tmp_format_data) );
					
					M('lionfish_comshop_car')->where( array('id' => $val['id']) )->save( $tmp_format_data_new );
				}
			}
		}
		
		
	
		$carkey = $car_prefix.$key; 
		
		$s_arr = M('lionfish_comshop_car')->field('format_data')->where( array('carkey' => $carkey,'token' => $token)  )->find();
		
		
        if ((int)$qty && ((int)$qty > 0)) {
            $key = $car_prefix . $key;
            $s = array();
            if (!empty($s_arr)) {
                $s = unserialize($s_arr['format_data']);
            }
            if (!empty($s)) {
				if( $format_data['is_just_addcar'] == 1 )
				{
					$format_data['quantity']+= $s['quantity']; 
				}
            }
        }
        if (!empty($s_arr)) {
			
			$car_data = array();
			$car_data['format_data'] = serialize($format_data);
			$car_data['modifytime'] = time();
			
			
			M('lionfish_comshop_car')->where( array('token' => $token,'carkey' => $key) )->save( $car_data );
				
        } else {
			
			$car_data = array();
			$car_data['token'] = $token;
			$car_data['community_id'] = $community_id;
			$car_data['carkey'] = $key;
			$car_data['modifytime'] = time();
			$car_data['format_data'] = serialize($format_data);
			
			M('lionfish_comshop_car')->add( $car_data );
			
           
        }
        $this->data = array();
    }
	
	public function add_activitycar($token, $goods_id, $format_data = array() , $option) {
		global $_W;
		global $_GPC;
		
        $this->removeActivityAllcar($token);
        $key = (int)$goods_id . ':';
        $qty = $format_data['quantity'];
        if ($option) {
            $key.= base64_encode($option) . ':';
        } else {
            $key.= ':';
        }
        $key.= "buy_type:" ;
        if ((int)$qty && ((int)$qty > 0)) {
            $key = 'cart_activity.' . $key;
			
			$carkey = 'cart.'.$key;
		
			$s_arr = M('lionfish_comshop_car')->where( array('token' => $token, 'carkey' => $carkey) )->find();
		
           
            $s = array();
            if (!empty($s_arr)) {
                $s = unserialize($s_arr['format_data']);
            }
            if (!empty($s)) {
                $format_data['quantity']+= $s['quantity'];
            }
            if (!empty($s_arr)) {
				
				$car_data = array();
				$car_data['format_data'] = serialize($format_data);
				
				M('lionfish_comshop_car')->where( array('token' => $token,'carkey' => $key) )->save( $car_data );
			
            } else {
				$car_data = array();
				$car_data['token'] = $token;
				$car_data['carkey'] = $key;
				$car_data['format_data'] = serialize($format_data);
				
				M('lionfish_comshop_car')->add($car_data);
            }
        }
        $this->data = array();
    }
	
	 public function get_all_goodswecar($buy_type = 'dan', $token,$is_pay_need = 1, $community_id) {
		
		
        if (!($this->data)) {
			
			if ($buy_type == 'dan') {
				$cart_sql = "select * from ".C('DB_PREFIX')."lionfish_comshop_car  
						where token='{$token}'  and community_id='{$community_id}' and carkey like 'cart.%' order by modifytime desc ";
				$cart = M()->query($cart_sql);

			} 
			else if( $buy_type == 'pindan' )
			{
				$cart_sql = "select * from ".C('DB_PREFIX')."lionfish_comshop_car  
						where token='{$token}' and community_id={$community_id}  and carkey like 'pindancart.%' order by modifytime desc ";
				$cart = M()->query($cart_sql);
				
			}else if( $buy_type == 'pintuan' )
			{
				$cart_sql = "select * from ".C('DB_PREFIX')."lionfish_comshop_car  
						where token='{$token}' and community_id={$community_id} and carkey like 'pintuancart.%' order by modifytime desc ";
				$cart = M()->query($cart_sql);
			}
			else if( $buy_type == 'integral' )
			{
				$cart_sql = "select * from ".C('DB_PREFIX')."lionfish_comshop_car  
						where token='{$token}'  and community_id={$community_id} and carkey like 'integralcart.%' order by modifytime desc ";
				
				$cart = M()->query($cart_sql);
			}
			else {
				$cart_sql = "select * from ".C('DB_PREFIX')."lionfish_comshop_car  
						where token='{$token}' and community_id='{$community_id}' and carkey like 'cart_activity.%' order by modifytime desc ";
				$cart =  M()->query($cart_sql);
				
			}
			
			$is_open_vipcard_buy =  D('Home/Front')->get_config_by_name('is_open_vipcard_buy');
			$is_open_vipcard_buy = !empty($is_open_vipcard_buy) && $is_open_vipcard_buy ==1 ? 1:0; 
			
			
			$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
			
			$member_id = $weprogram_token['member_id'];
			
			
			//$goods_model = D('Home/Goods');
		//	var_dump($cart);die;
            foreach ($cart as $key => $val_uns) {
				
				$val = unserialize( $val_uns['format_data'] );
				
				if($buy_type =='dan' && $is_pay_need == 1)
				{
					if(isset($val['singledel']) &&  $val['singledel'] == 0)
					{
						continue;
					}						
				}else if($buy_type == 'dan' && $is_pay_need == 0)
				{
					//判断是否支持自提，如果支持自提，那么就不要剔除购物车列表
					//$val['goods_id'] pick_just pick_just
					
					$goods_common_info = D('Seller/Front')->get_goods_common_field($val['goods_id'] , 'pick_just');
					
					$pick_just = $goods_common_info['pick_just'];
					
					if(!empty($pick_just) && isset($pick_just['pick_just']) && $pick_just['pick_just'] > 0)
					{
						//continue;
					}else {
						
						
					}
				}
				
			
				
                //$pin_type = $val['pin_type'];
                $quantity = $val['quantity'];
                //$quantity
                $goods = explode(':', $key);
                $goods_id = $val['goods_id'];
                $stock = true;
                // Options sku_str
				$options  = $val['sku_str'];
               
                
				$goods_query_sql = "select * from ".C('DB_PREFIX')."lionfish_comshop_goods as p left join ".C('DB_PREFIX')."lionfish_comshop_good_commiss as pd 
									on p.id = pd.goods_id where p.id ={$goods_id} and p.grounding = 1  ";
				
				$goods_query_arr = M()->query($goods_query_sql);
				
				$goods_query = $goods_query_arr[0];
				
				
				
                if ($goods_query) {
                    $option_price = null;
                    $option_weight = $goods_query['weight'];
                    $option_data = array();
					
					$max_quantity = $goods_query['total'];
                    if (!empty($options)) {
                        
						$goods_option_mult_value = M('lionfish_comshop_goods_option_item_value')->where( array('goods_id' => $goods_id,'option_item_ids' => $options)  )->find();
						
						
						$goods_option_mult_value['pin_price'] = $goods_option_mult_value['pinprice'];
						$goods_option_mult_value['dan_price'] = $goods_option_mult_value['marketprice'];
						
						$goods_query['card_price'] = $goods_option_mult_value['card_price'];
						
                        $options_arr = array();
                        $option_value_id_arr = explode('_', $options);
                        foreach ($option_value_id_arr as $id_val) {
							$goods_option_value = M('lionfish_comshop_goods_option_item')->where( array('goods_id' => $goods_id,'id' => $id_val) )->find();
                            $options_arr[$goods_option_value['goods_option_id']] = $goods_option_value['id'];
                        }
                    }

					$option_value_image = '';
                    if($options_arr){
	                    foreach ($options_arr as $goods_option_id => $goods_option_item_id ) {
							//option_value
							$option_query = M('lionfish_comshop_goods_option')->where( array('goods_id' => $goods_id,'id' => $goods_option_id) )->find();
							  
	                        if ($option_query) {
	                            
								$option_value_query = M('lionfish_comshop_goods_option_item')->where( array('id' => $goods_option_item_id,'goods_id' => $goods_id) )->find();
								
	                            if ($option_value_query) {
									
									if( !empty($option_value_query['thumb']) )
									{
										$option_value_image = $option_value_query['thumb'];
									}
									
									$max_quantity = $goods_option_mult_value['stock'];
									
	                                //根据商品类型获取不同价格 begin  pinprice pin_price  productprice  dan_price
	                                if ($buy_type == 'pintuan' && $goods_query['type'] != 'spike') {
	                                    $option_price = $goods_option_mult_value['pin_price'];
	                                }else if($goods_query['type'] == 'spike')
									{
										$option_price = $goods_option_mult_value['dan_price'];
									}
	                                 else {
	                                    $option_price = $goods_option_mult_value['dan_price'];
	                                }
	                                //根据商品类型获取不同价格 begin
	                                $option_weight = $goods_option_mult_value['weight'];
	                                
	                                $option_data[] = array(
	                                    'goods_option_id' => $goods_option_id,
	                                    'goods_option_value_id' => $goods_option_item_id,
	                                    'option_id' => $goods_option_id,
	                                    'option_value_id' => $goods_option_item_id,
	                                    'name' => $option_query['title'],
	                                    'value' => $option_value_query['title'],
	                                    'quantity' => $quantity,
	                                    'price' => $option_price,
										'card_price' => $goods_option_mult_value['card_price'],
	                                    'weight' => $option_weight,
	                                );
	                            }
	                        }
	                    }
                    }
					
					
                    $header_disc = 100;
					$shop_price = $goods_query['productprice'];
					
					$goods_query['price'] = $goods_query['price'];
					
					$thumb_image = D('Home/Pingoods')->get_goods_images($goods_id);
					
					if( !empty($thumb_image) )
					{
						$thumb_image = tomedia($thumb_image['image']);
					}
                   
				   
					if(!empty($option_value_image))
					{
						$thumb_image = tomedia($option_value_image);
					}
					
					$store_info = array('s_true_name' => '');
					$s_logo = D('Seller/Front')->get_config_by_name('shoplogo');
					
					if( !empty($s_logo) )
					{
						$s_logo = tomedia($s_logo);
					}
					
					
                    //$goods_query['price']
					
                    if ( !is_null($option_price)) {
                        $goods_query['price'] = $option_price;
                    } else {
                        //根据商品类型获取不同价格 begin
                        if ($buy_type == 'pintuan') {//判断类型是否是积分商品
							if($goods_query['type'] == 'integral')
							{
								//TODO....等待打开积分
								//$intgral_goods_info = M('intgral_goods')->field('score')->where( array('goods_id' => $goods_id) )->find();
								//$goods_query['price'] = $intgral_goods_info['score'];
							}
							else if($goods_query['type'] == 'spike')
							{
								
							}
							else{
								
								if($goods_query['type'] == 'pin')
								{
									//ims_ 
									$pin_goods = M('lionfish_comshop_good_pin')->where( array('goods_id' => $goods_id) )->find();
									
									
									$goods_query['price'] = $pin_goods['pinprice'];
								}
							}
                            
                        }
                        //根据商品类型获取不同价格 begin
                        
                    }
                    if (!empty($option_weight)) {
                        $goods_query['weight'] = $option_weight;
                    }
                    //拼团才会有pin_id
                    $pin_id = 0;
                    if ($buy_type == 'pintuan' && isset($val['pin_id'])) {
                        $pin_id = $val['pin_id'];
                    }
					$price = $goods_query['price'];
					//判断是否有团长折扣 暂时屏蔽 TODO.....
					/**
					if( $buy_type == 'pin' && $pin_id == 0 && $goods_query['head_disc'] != 100)
					{
						$price = round(( $price * intval($goods_query['head_disc']) )/100,2);
						$header_disc = intval($goods_query['head_disc']);
					}
					**/
					
					//判断是否会员折扣  TODO。。。先关闭
					$level_info = array('member_discount' => 100,'level_name' =>'');
					
					$member_info = M('lionfish_comshop_member')->field('level_id')->where( array('member_id' => $member_id) )->find();
					
					$goods_common = M('lionfish_comshop_good_common')->field('is_mb_level_buy')->where( array('goods_id' => $goods_id ) )->find();

					$goods_query['is_mb_level_buy'] = 0;
					$goods_query['levelprice'] = 0;
					
					
					if( $buy_type == 'dan' )
					{
						if($member_info['level_id'] > 0 && $goods_common['is_mb_level_buy'] == 1)
						{
							$member_level_info = M('lionfish_comshop_member_level')->where( array('id' => $member_info['level_id'] ) )->find();
							
							$level_info['member_discount'] = $member_level_info['discount'] ;
							$level_info['level_name'] = $member_level_info['levelname'];
							
							$price2 = round(( $price * $member_level_info['discount'] )/100,2);
							
							$goods_query['is_mb_level_buy'] = 1;
							$goods_query['levelprice'] = $price2;
							
							//$goods_query['price'] = $price ;
						}
					}
					
					
					//判断商品能否参与满减活动  fullreduction_money
					$is_open_fullreduction = D('Home/Front')->get_config_by_name('is_open_fullreduction');
					$can_man_jian = 0;
					
					if( $buy_type == 'dan' )
					{
						if( !empty($is_open_fullreduction) && $is_open_fullreduction == 1)
						{
							 $gd_full_info = D('Home/Front')->get_goods_common_field($goods_id , 'is_take_fullreduction,supply_id,is_new_buy');
							 $is_take_fullreduction = $gd_full_info['is_take_fullreduction'];
							 
							 //supply_id 
							 if( $gd_full_info['supply_id'] > 0)
							 {
								
								$supply_info = M('lionfish_comshop_supply')->field('type')->where( array('id' => $gd_full_info['supply_id'] ) )->find();
								
								
								if(!empty($supply_info) && $supply_info['type'] == 1)
								{
									$can_man_jian = 0;
								}else{
									
									 if( $is_take_fullreduction == 1 )
									 {
										 $can_man_jian = 1;
									 }
								}
							 }else{
								if( $is_take_fullreduction == 1 )
								 {
									 $can_man_jian = 1;
								 } 
							 }
							  $is_new_buy = $gd_full_info['is_new_buy'];
						}
					}
					
					
					$is_open_only_express = D('Home/Front')->get_config_by_name('is_open_only_express');
					
					$is_only_express = 0;
					
					if( !empty($is_open_only_express) && $is_open_only_express == 1)
					{
						$gd_s_info = D('Home/Front')->get_goods_common_field($goods_id , 'is_only_express');
						
						$is_only_express = $gd_s_info['is_only_express'];
					}
					
					// $is_open_vipcard_buy 
					if( $is_open_vipcard_buy == 1 && $goods_query['is_take_vipcard'] == 1 )
					{
						
					}else{
						$goods_query['is_take_vipcard'] == 0;
					}
					
                    //拼团 end
                    $this->data[$key] = array(
                        'key' => $val_uns['carkey'],
                        'goods_id' => $goods_id ,
						'is_only_express' => $is_only_express,
                        'name' => $goods_query['goodsname'],
						'song_type'=>$goods_query['song_type'],
						'seller_name' => $store_info['s_true_name'],
						'seller_logo' => $s_logo,
                        'weight' => $option_weight,
						'singledel' => $val['singledel'],
						//$val['singledel']
						'can_man_jian' => $can_man_jian,
                        'header_disc' => $header_disc,
						'member_disc' => $level_info['member_discount'],
                        'level_name' => $level_info['level_name'],
                        'pin_id' => $pin_id,
                        'shipping' => $goods_query['dispatchtype'],
                        'goods_freight' => $goods_query['dispatchprice'],
                        'transport_id' => $goods_query['dispatchid'],
                        'image' => $thumb_image,
                        'quantity' => $quantity,
						'max_quantity' => $max_quantity,
						'shop_price' => $shop_price,
                        'price' => $goods_query['price'],
						'levelprice' => $goods_query['levelprice'],
						'card_price' => $goods_query['card_price'],
                        'total' => ($price) * $quantity,
						'level_total' => $goods_query['levelprice'] * $quantity,
						'card_total' => $goods_query['card_price'] * $quantity,
						'is_take_vipcard' => $goods_query['is_take_vipcard'],
						'is_mb_level_buy' => $goods_query['is_mb_level_buy'],
                        //'model' => $goods_query[0]['model'],
                        'option' => $option_data,
						'sku_str' => $val['sku_str'],
						'soli_id' => isset($val['soli_id']) ?  intval($val['soli_id']) : 0,
						'is_new_buy' => $is_new_buy
						
                    );
                } else {
                    $this->removecar($key,$token);
                }
            }
        }
		
		
        return $this->data;
    }
	
	//删除商品
    public function removecar($key,$token) {
		
        $key =  $key; //重新给$key赋值
		
		M('lionfish_comshop_car')->where( array('token' => $token,'carkey' => $key) )->delete();					
    }
	
	//购物车是否为空
	public function has_goodswecar($buy_type = 'dan', $token, $community_id) {
		
		
		if ($buy_type == 'dan') {
			$cart_sql = "select * from ".C('DB_PREFIX')."lionfish_comshop_car  
						where token='{$token}' and community_id='{$community_id}' and carkey like 'cart.%' ";
			$s = M()->query($cart_sql);
//var_dump($cart_sql);die;
		} 
		else if( $buy_type == 'pindan' )
		{
			$cart_sql = "select * from ".C('DB_PREFIX')."lionfish_comshop_car  
						where token='{$token}'  and carkey like 'pindancart.%' ";
			$s = M()->query($cart_sql);
			
		}else if( $buy_type == 'pintuan' )
		{
			
			$cart_sql = "select * from ".C('DB_PREFIX')."lionfish_comshop_car  
						where token='{$token}'  and carkey like 'pintuancart.%' ";
			$s = M()->query($cart_sql);
		}
		else if( $buy_type == 'integral' ) 
		{
			$cart_sql = "select * from ".C('DB_PREFIX')."lionfish_comshop_car  
						where token='{$token}' and carkey like 'integralcart.%' ";
			$s = M()->query($cart_sql);
		}
		else if ($buy_type == 'pin') {
			
			$cart_sql = "select * from ".C('DB_PREFIX')."lionfish_comshop_car  
						where token='{$token}'  and carkey like 'cart_activity.%' ";
			$s = M()->query($cart_sql);
		}
		return count($s);
	}
	
	
	public function count_activitycar($token) {
		
        $quantity = 0; 
		
		$cart_sql = "select * from ".C('DB_PREFIX')."lionfish_comshop_car  
						where token='{$token}' and carkey like 'cart_activity.%' ";
		$cart = M()->query($cart_sql);
		
		foreach ($cart as $key => $val) {
			$format_data = unserialize($val['format_data']);
			$quantity += $format_data['quantity'];		
		}
		return $quantity;
	}
	
	public function removeActivityAllcar($token ,$car_prefix='pindancart.') {
					
		M('lionfish_comshop_car')->where(" token = '{$token}' and carkey like '".$car_prefix."%' ")->delete();		
    }
	
	public function count_goodscar($token, $community_id) {
		
        $quantity = 0; 
		
		$cart_sql = "select * from ".C('DB_PREFIX')."lionfish_comshop_car 
						where token='{$token}' and community_id='{$community_id}' and carkey like 'cart.%' ";
		$cart = M()->query($cart_sql);
			
		
		foreach ($cart as $key => $val) {
			$format_data = unserialize($val['format_data']);
			if(isset($format_data['quantity']))
				$quantity += $format_data['quantity'];		
		}
		
		return $quantity;
	}
	
}