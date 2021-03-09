<?php
namespace Home\Model;
use Think\Model;
class TransportModel{
	public function __construct()
	{
	}
	
	/**
	 * 计算某地区某运费模板ID下的商品总运费，如果运费模板不存在或，按免运费处理
	 *
	 * @param int $transport_id 运费模版id
	 * @param int $quantity 商品件数
	 * @param int $buy_num 商品重量
	 * @param int $area_id 地区id
	 * @return number/boolean
	 */
    public function calc_transport($transport_id, $quantity, $buy_num, $area_id) {
		
		
        //$good['transport_id'], $good['quantity'], $address
		if (empty($transport_id)  || empty($area_id)) return 0;
		
		
		$extend_list = M('lionfish_comshop_shipping')->where( array('id' => $transport_id ) )->select();
		
		
		// lionfish_comshop_shipping
		
		if (empty($extend_list)) {
		    return 0;
		} else {
		    return $this->calc_unit($area_id,$quantity, $buy_num,$extend_list);
		}
    }	

	/**
	 * 计算某个具单元的运费
	 *
	 * @param 配送地区 $area_id
	 * @param 购买数量 $quantity
	 * @param 购买重量 $weight
	 * @param 运费模板内容 $extend
	 * @return number 总运费
	 ($area_id,$quantity, $buy_num,$extend_list);
	 */
	private function calc_unit($area_id, $quantity, $weight, $extend){		
		
		
		$area_info = M('lionfish_comshop_area')->where( array('id' => $area_id ) )->find();
	
		if (!empty($extend) && is_array($extend)){
			
			 $calc_total=array(
				'error'=>'该地区不配送！！'
			);
			
			$defult_extend = array();
			
			
			foreach ($extend as $v) {
				
				/**
				 * strpos函数返回字符串在另一个字符串中第一次出现的位置，没有该字符返回false
				 * 参数1，字符串
				 * 参数2，要查找的字符
				 */	
				$area_price_list = unserialize($v['areas']);
				$cal_price = 0;
				
				if( !empty($area_price_list) )
				{
					$s_count = count($area_price_list);
					$i =0;
						
					foreach($area_price_list as $area_price)
					{
							$area_price['citys_code'] = explode(';', $area_price['citys_code']);
							
							$i++;
					
							if( !empty($area_info['code']) && !empty($area_price['citys_code']) && in_array($area_info['code'], $area_price['citys_code']) )
							{
								unset($calc_total['error']);
								
								$frist = $area_price['frist'];
								$frist_price = $area_price['frist_price'];
								$second = $area_price['second'];
								$second_price = $area_price['second_price'];
								
								//按照重量
								if($v['type'] == 1)
								{	
									if ($weight <= $frist){
										//在首重数量范围内
										$calc_total['price'] = $frist_price;
									}else{
										//超出首重数量范围，需要计算续重
										$calc_total['price'] = sprintf('%.2f',($frist_price + ceil(($weight-$frist)/$second)*$second_price));
									}
									
									//var_dump($weight , $frist);die();
									
									//return $calc_total['price'];//20190618
									$cal_price = $calc_total['price'];
									break;
								}else if($v['type'] == 2){
									//按照件数  firstnum firstnumprice  secondnum  secondnumprice
									if ($quantity <= $frist){
										//在首重数量范围内
										$calc_total['price'] = $frist_price;
									}else{
										//超出首重数量范围，需要计算续重
										$calc_total['price'] = sprintf('%.2f',($frist_price + ceil(($quantity-$frist)/$second)*$second_price));
									}
									
								
									//return $calc_total['price'];//20190618
									$cal_price = $calc_total['price'];
									break;
								}
								
							}else if($i == $s_count){
								//使用默认的
								unset($calc_total['error']);
								
								//按照重量
								if($v['type'] == 1)
								{
									if ($weight <= $v['firstweight']){
										//在首重数量范围内
										$calc_total['price'] = $v['firstprice'];
									}else{
										//超出首重数量范围，需要计算续重
										$calc_total['price'] = sprintf('%.2f',($v['firstprice'] + ceil(($weight-$v['firstweight'])/$v['secondweight'])*$v['secondprice']));
									}
									//return $calc_total['price'];//20190618
									$cal_price = $calc_total['price'];
								}else if($v['type'] == 2){
									//按照件数  firstnum firstnumprice  secondnum  secondnumprice
									if ($quantity <= $v['firstnum']){
										//在首重数量范围内
										$calc_total['price'] = $v['firstnumprice'];
									}else{
										//超出首重数量范围，需要计算续重
										$calc_total['price'] = sprintf('%.2f',($v['firstnumprice'] + ceil(($quantity-$v['firstnum'])/$v['secondnum'])* $v['secondnumprice']));
									}
									
									//return $calc_total['price'];//20190618
									$cal_price = $calc_total['price'];
									break;
								}
							}
								
							if (strpos($v['area_id'],",".$area_id.",") !== false){
								
								unset($calc_total['error']);
								
								
								if ($num <= $v['snum']){
									//在首重数量范围内
									$calc_total['price'] = $v['sprice'];
								}else{
									//超出首重数量范围，需要计算续重
									$calc_total['price'] = sprintf('%.2f',($v['sprice'] + ceil(($num-$v['snum'])/$v['xnum'])*$v['xprice']));
								}				
								
								//return $calc_total['price'];//20190618
								$cal_price = $calc_total['price'];
								break;
							}
					}
				}else{
					
					//按照重量 $v
					if($v['type'] == 1)
					{
						if ($weight <= $v['firstweight']){
							//在首重数量范围内
							$calc_total['price'] = $v['firstprice'];
						}else{
							//超出首重数量范围，需要计算续重
							$calc_total['price'] = sprintf('%.2f',($v['firstprice'] + ceil(($weight-$v['firstweight'])/$v['secondweight'])*$v['secondprice']));
						}
						
						$cal_price = $calc_total['price'];
					}else if($v['type'] == 2){
						//按照件数  firstnum firstnumprice  secondnum  secondnumprice
						if ($quantity <= $v['firstnum']){
							//在首重数量范围内
							$calc_total['price'] = $v['firstnumprice'];
						}else{
							//超出首重数量范围，需要计算续重
							$calc_total['price'] = sprintf('%.2f',($v['firstnumprice'] + ceil(($quantity-$v['firstnum'])/$v['secondnum'])* $v['secondnumprice']));
						}
						
						$cal_price = $calc_total['price'];
					}
					
				}
				
				return $cal_price;
				
			}
			return 0;
		}
		
	}

	/** 
	 * 运费计算
	 * @Author: flydreame 
	 * @Date: 2020-05-21 14:24:42 
	 * @param 订单信息 $val
	 * @param 城市id $mb_city_id
	 *  @param 统计信息 $tongji
	 * @Desc: 重写运费计算方法 原来方法作废 
	 */public function freight_calculation($val,$mb_city_id,$tongji){

		$dispatchids=array();   
		$area_code = M('lionfish_comshop_area')->field('code')->where( array('id' => $mb_city_id ) )->find();//查询城市code
        
		//循环商品信息
		foreach($val as $kk =>$d_goods)
		{	
			//判断是否是统一运费
			if($d_goods['shipping']==0){

				//判断是不是同一模板
				if(!in_array($d_goods['transport_id'],$dispatchids) ){
					
					$dispatchids[]=$d_goods['transport_id'];
					
					//查询运费模板信息
					$shipping_default = M('lionfish_comshop_shipping')->where(array('id' => $d_goods['transport_id']))->find();
						
					//判断有没有模板信息
					if (!empty($shipping_default)) {
						
						$area_price_list = unserialize($shipping_default['areas']);
						
						$num =0;
						$price =0;
						$is_existence = -1;

						//循环
						foreach($area_price_list as $k=> $are){
							$are['citys_code'] = explode(';', $are['citys_code']);
					
							if(in_array($area_code['code'],$are['citys_code'] )){
								$num = $are['chao_num'];
								$price = $are['chao_price'];
								$is_existence = $k;
							}
						}
						
						//判断是否存在运费规则 有并且是否该城市在运费规则里 
						if(!empty($area_price_list) && $is_existence>=0){
							
							//判断是否满足包邮
							if(($num <= $tongji[$d_goods['transport_id']]['num'] &&  $num > 0) || ($price <= $tongji[$d_goods['transport_id']]['price'] &&  $price > 0 )){
								$calc_total = 0;
							}else{

								$frist = $area_price_list[$is_existence]['frist'];
								$frist_price = $area_price_list[$is_existence]['frist_price'];
								$second = $area_price_list[$is_existence]['second'];
								$second_price = $area_price_list[$is_existence]['second_price'];
								
								//以重量计算
								if($shipping_default['type'] == 1){
									
									//在首重数量范围内
									if ($tongji[$d_goods['transport_id']]['weight'] <= $frist){
									
										$calc_total= $frist_price;
									}else{//超出首重数量范围，需要计算续重
										
										$calc_total = sprintf('%.2f',($frist_price + ceil(($tongji[$d_goods['transport_id']]['weight']-$frist)/$second)*$second_price));
									}
								}
								//以件数计算
								if($shipping_default['type'] == 2){
									//在首件数范围内
									if ($tongji[$d_goods['transport_id']]['num'] <= $frist){
										
										$calc_total= $frist_price;
									}else{//超出首重数量范围，需要计算续重
										
										$calc_total = sprintf('%.2f',($frist_price + ceil(($tongji[$d_goods['transport_id']]['num']-$frist)/$second)*$second_price));
									}
								}
							}

						}else{//全国
							
							if($shipping_default['type'] == 1 ){
								$chao_price_country = $shipping_default['weight_chao_price'];
								$chao_num_country = $shipping_default['weight_chao_num'];
							}
							if($shipping_default['type'] == 2 ){
								$chao_price_country = $shipping_default['jian_chao_price'];
							
								$chao_num_country = $shipping_default['jian_chao_num'];
							}
						
                 
							//判断是否满足包邮
							if(($chao_num_country<= $tongji[$d_goods['transport_id']]['num'] && $chao_num_country > 0 ) || ($chao_price_country <= $tongji[$d_goods['transport_id']]['price'] && $chao_price_country > 0  ) ){
								$calc_total = 0;
							}else{
								
								//以重量计算
								if($shipping_default['type'] == 1){
										
									//在首重数量范围内
									if ($tongji[$d_goods['transport_id']]['weight'] <= $shipping_default['firstweight']){
										
										$calc_total= $shipping_default['firstprice'];
									}else{//超出首重数量范围，需要计算续重
									
										$calc_total = sprintf('%.2f',($shipping_default['firstprice'] + ceil(($tongji[$d_goods['transport_id']]['weight']-$shipping_default['firstweight'])/$shipping_default['secondweight'])*$shipping_default['secondprice']));
									}
								}
								//以件数计算
								if($shipping_default['type'] == 2){
									//在首件数范围内
									if ($tongji[$d_goods['transport_id']]['num'] <= $shipping_default['firstnum']){
										
										$calc_total= $shipping_default['firstnumprice'];
									}else{//超出首重数量范围，需要计算续重
										
										$calc_total = sprintf('%.2f',( $shipping_default['firstnumprice'] + ceil(($tongji[$d_goods['transport_id']]['num']-$shipping_default['firstnum'])/$shipping_default['secondnum'])* $shipping_default['secondnumprice']));
									}
								}

							}
						}
					
					} else {
						$calc_total=0;
					}
				}else{
					$calc_total=0;
				}

			}else{
				$calc_total = $d_goods['goods_freight'];
			}
		
			$calc_total_total +=$calc_total;
		}

		return $calc_total_total;
	}
	
}