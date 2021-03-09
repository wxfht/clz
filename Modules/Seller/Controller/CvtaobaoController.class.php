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
use Admin\Model\GoodsModel;
class CvtaobaoController extends CommonController{
	

	
	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='商品管理';
		$this->breadcrumb2='采集淘宝商品';
	}
	
	function index(){
		
		//SELLERUID
		
    	$this->display();
	}
	
	function caiji_ajax()
	{
		$arr_data = I('post.');
		$itemid = $arr_data['id'];
		$arr = $arr_data['ld_data'];
	
		//$itemInfoModel = $arr['data']['itemInfoModel'];
		
		$data = $arr['data'];
		$itemInfoModel = $data['itemInfoModel'];
		
		$item = array();
		$item['id'] = $g['id'];
		$item['merchid'] = $merchid;
		if (!(empty($merchid))) 
		{
			if (empty($_W['merch_user']['goodschecked'])) 
			{
				$item['checked'] = 1;
			}
			else 
			{
				$item['checked'] = 0;
			}
		}
		$item['pcate'] = $pcate;
		$item['ccate'] = $ccate;
		$item['tcate'] = $tcate;
		$item['cates'] = $pcate . ',' . $ccate . ',' . $tcate;
		$item['itemId'] = $itemInfoModel['itemId'];
		$item['title'] = $itemInfoModel['title'];
		$item['pics'] = $itemInfoModel['picsPath'];
		$params = array();
		if (isset($data['props'])) 
		{
			$props = $data['props'];
			foreach ($props as $pp ) 
			{
				$params[] = array('title' => $pp['name'], 'value' => $pp['value']);
			}
		}
		$item['params'] = $params;
		$specs = array();
		$options = array();
		if (isset($data['skuModel'])) 
		{
			$skuModel = $data['skuModel'];
			if (isset($skuModel['skuProps'])) 
			{
				$skuProps = $skuModel['skuProps'];
				foreach ($skuProps as $prop ) 
				{
					$spec_items = array();
					foreach ($prop['values'] as $spec_item ) 
					{
						$spec_items[] = array('valueId' => $spec_item['valueId'], 'title' => $spec_item['name'], 'thumb' => $spec_item['imgUrl']);
					}
					$spec = array('propId' => $prop['propId'], 'title' => $prop['propName'], 'items' => $spec_items);
					$specs[] = $spec;
				}
			}
			if (isset($skuModel['ppathIdmap'])) 
			{
				$ppathIdmap = $skuModel['ppathIdmap'];
				foreach ($ppathIdmap as $key => $skuId ) 
				{
					$option_specs = array();
					$m = explode(';', $key);
					foreach ($m as $v ) 
					{
						$mm = explode(':', $v);
						$option_specs[] = array('propId' => $mm[0], 'valueId' => $mm[1]);
					}
					$options[] = array('option_specs' => $option_specs, 'skuId' => $skuId, 'stock' => 0, 'marketprice' => 0, 'specs' => '');
				}
			}
		}
		$item['specs'] = $specs;
		$stack = $data['apiStack'][0]['value'];
		$value = json_decode($stack, true);
		$item1 = array();
		$data1 = $value['data'];
		$itemInfoModel1 = $data1['itemInfoModel'];
		$item['total'] = $itemInfoModel1['quantity'];
		$item['sales'] = $itemInfoModel1['totalSoldQuantity'];
		if (isset($data1['skuModel'])) 
		{
			$skuModel1 = $data1['skuModel'];
			if (isset($skuModel1['skus'])) 
			{
				$skus = $skuModel1['skus'];
				foreach ($skus as $key => $val ) 
				{
					$sku_id = $key;
					foreach ($options as &$o ) 
					{
						if ($o['skuId'] == $sku_id) 
						{
							$o['stock'] = $val['quantity'];
							foreach ($val['priceUnits'] as $p ) 
							{
								$o['marketprice'] = $p['price'];
							}
							
							$item['marketprice'] = $o['marketprice'];
							
							$titles = array();
							foreach ($o['option_specs'] as $osp ) 
							{
								foreach ($specs as $sp ) 
								{
									if ($sp['propId'] == $osp['propId']) 
									{
										foreach ($sp['items'] as $spitem ) 
										{
											if ($spitem['valueId'] == $osp['valueId']) 
											{
												$titles[] = $spitem['title'];
											}
										}
									}
								}
							}
							$o['title'] = $titles;
						}
					}
					unset($o);
				}
			}
			else 
			{
				$mprice = 0;
				foreach ($itemInfoModel1['priceUnits'] as $p ) 
				{
					$mprice = $p['price'];
				}
				$item['marketprice'] = $mprice;
			}
		}
		else 
		{
			$mprice = 0;
			foreach ($itemInfoModel1['priceUnits'] as $p ) 
			{
				$mprice = $p['price'];
			}
			$item['marketprice'] = $mprice;
		}
		$item['options'] = $options;
		$item['content'] = array();
		
		
		$url = 'https://hws.m.taobao.com/cache/wdesc/5.0/?id=' . $itemid;
		
		$response = ihttp_get($url);
		$response = preg_replace('/ (?:width)=(\'|").*?\\1/', ' width="100%"', $response);
		$response = preg_replace('/ (?:height)=(\'|").*?\\1/', ' ', $response);
		$item['content'] = $response;
		
		
		$thumb_url = array();
		$pics = $item['pics'];
		$piclen = count($pics);
		
		
		if (0 < $piclen) 
		{
				if (substr($pics[0], 0, 2) == '//') 
				{
					$pics[0] = 'http://' . substr($pics[0], 2);
				}
				
			$data['thumb'] = $this->save_image($pics[0], false);
			if (1 < $piclen) 
			{
				$i = 1;
				while ($i < $piclen) 
				{
					if (substr($pics[$i], 0, 2) == '//') 
					{
						$pics[$i] = 'http://' . substr($pics[$i], 2);
					}
					$img = $this->save_image($pics[$i], false);
					$thumb_url[] = $img;
					++$i;
				}
			}
		}
		$image_lists = $thumb_url;
		
		
		
		$response = $item['content'];
		$content = $response['content'];
		preg_match_all('/<img.*?src=[\\\\\'| \\"](.*?(?:[\\.gif|\\.jpg]?))[\\\\\'|\\"].*?[\\/]?>/', $content, $imgs);
		if (isset($imgs[1])) 
		{
			foreach ($imgs[1] as $img ) 
			{
				$catchimg = $img;
				if (substr($catchimg, 0, 2) == '//') 
				{
					$img = 'http://' . substr($img, 2);
				}
				$im = array('catchimg' => $catchimg, 'system' => $this->save_image($img, true));
				$images[] = $im;
			}
		}
		preg_match('/tfsContent : \\\'(.*)\\\'/', $content, $html);
		$html = iconv('GBK', 'UTF-8', $html[1]);
		if (isset($images)) 
		{
			foreach ($images as $img ) 
			{
				$html = str_replace($img['catchimg'], $img['system'], $html);
			}
		}
		
		//'content' => $html  $item['total']
		//d['marketprice'] = $minprice;  ["sales"]=>
  //string(5) "12418"
		//$item['title'], 'quantity' => $item['total']
		
		
		$goods_data = array();
		$goods_data['title'] = $item['title'];
		$goods_data['marketprice'] = $item['marketprice'];
		$goods_data['sales'] = $item['sales'];
		$goods_data['quantity'] = $item['total'];
		$goods_data['image_lists'] = $image_lists;
		$goods_data['store_id'] = SELLERUID;
		$goods_data['html'] = $html;
		
		
		//$image_lists 
		$model=new GoodsModel();   
		
		if( empty($item['title']) )
		{
			echo json_encode( array('code' =>0,'msg' =>'采集失败') );
		}else{
			$model->add_caiji_Goods($goods_data);
			echo json_encode( array('code' =>1) );
		}
		
		die();
		
	}
	function caiji()
	{
		$url = I('url');
		
		preg_match('/id\\=(\\d+)/i', $url, $matches);
		if (isset($matches[1])) 
		{
			$itemid = $matches[1];
		}
		
		$url = 'http://hws.m.taobao.com/cache/wdetail/5.0/?id=' . $itemid;
		
		$response = ihttp_get($url);
		//$response_2 = file_get_contents($url);
		//var_dump($response,$response_2);die();
		
		if (!(isset($response['content']))) 
		{
			//return array('result' => '0', 'error' => '未从淘宝获取到商品信息!');
			echo json_encode( array('code' =>0,'msg' => '宝贝不存在!') );
			die();
		}
		
		$content = $response['content'];
		if (strexists($response['content'], 'ERRCODE_QUERY_DETAIL_FAIL')) 
		{
			echo json_encode( array('code' =>0,'msg' => '宝贝不存在!请检查链接中是否有干扰参数') );
			die();
			//return array('result' => '0', 'error' => '宝贝不存在!');
		}
		$arr = json_decode($content, true);
		$data = $arr['data'];
		$itemInfoModel = $data['itemInfoModel'];
		
		$item = array();
		$item['id'] = $g['id'];
		$item['merchid'] = $merchid;
		if (!(empty($merchid))) 
		{
			if (empty($_W['merch_user']['goodschecked'])) 
			{
				$item['checked'] = 1;
			}
			else 
			{
				$item['checked'] = 0;
			}
		}
		$item['pcate'] = $pcate;
		$item['ccate'] = $ccate;
		$item['tcate'] = $tcate;
		$item['cates'] = $pcate . ',' . $ccate . ',' . $tcate;
		$item['itemId'] = $itemInfoModel['itemId'];
		$item['title'] = $itemInfoModel['title'];
		$item['pics'] = $itemInfoModel['picsPath'];
		$params = array();
		if (isset($data['props'])) 
		{
			$props = $data['props'];
			foreach ($props as $pp ) 
			{
				$params[] = array('title' => $pp['name'], 'value' => $pp['value']);
			}
		}
		$item['params'] = $params;
		$specs = array();
		$options = array();
		if (isset($data['skuModel'])) 
		{
			$skuModel = $data['skuModel'];
			if (isset($skuModel['skuProps'])) 
			{
				$skuProps = $skuModel['skuProps'];
				foreach ($skuProps as $prop ) 
				{
					$spec_items = array();
					foreach ($prop['values'] as $spec_item ) 
					{
						$spec_items[] = array('valueId' => $spec_item['valueId'], 'title' => $spec_item['name'], 'thumb' => $spec_item['imgUrl']);
					}
					$spec = array('propId' => $prop['propId'], 'title' => $prop['propName'], 'items' => $spec_items);
					$specs[] = $spec;
				}
			}
			if (isset($skuModel['ppathIdmap'])) 
			{
				$ppathIdmap = $skuModel['ppathIdmap'];
				foreach ($ppathIdmap as $key => $skuId ) 
				{
					$option_specs = array();
					$m = explode(';', $key);
					foreach ($m as $v ) 
					{
						$mm = explode(':', $v);
						$option_specs[] = array('propId' => $mm[0], 'valueId' => $mm[1]);
					}
					$options[] = array('option_specs' => $option_specs, 'skuId' => $skuId, 'stock' => 0, 'marketprice' => 0, 'specs' => '');
				}
			}
		}
		$item['specs'] = $specs;
		$stack = $data['apiStack'][0]['value'];
		$value = json_decode($stack, true);
		$item1 = array();
		$data1 = $value['data'];
		$itemInfoModel1 = $data1['itemInfoModel'];
		$item['total'] = $itemInfoModel1['quantity'];
		$item['sales'] = $itemInfoModel1['totalSoldQuantity'];
		if (isset($data1['skuModel'])) 
		{
			$skuModel1 = $data1['skuModel'];
			if (isset($skuModel1['skus'])) 
			{
				$skus = $skuModel1['skus'];
				foreach ($skus as $key => $val ) 
				{
					$sku_id = $key;
					foreach ($options as &$o ) 
					{
						if ($o['skuId'] == $sku_id) 
						{
							$o['stock'] = $val['quantity'];
							foreach ($val['priceUnits'] as $p ) 
							{
								$o['marketprice'] = $p['price'];
							}
							
							$item['marketprice'] = $o['marketprice'];
							
							$titles = array();
							foreach ($o['option_specs'] as $osp ) 
							{
								foreach ($specs as $sp ) 
								{
									if ($sp['propId'] == $osp['propId']) 
									{
										foreach ($sp['items'] as $spitem ) 
										{
											if ($spitem['valueId'] == $osp['valueId']) 
											{
												$titles[] = $spitem['title'];
											}
										}
									}
								}
							}
							$o['title'] = $titles;
						}
					}
					unset($o);
				}
			}
			else 
			{
				$mprice = 0;
				foreach ($itemInfoModel1['priceUnits'] as $p ) 
				{
					$mprice = $p['price'];
				}
				$item['marketprice'] = $mprice;
			}
		}
		else 
		{
			$mprice = 0;
			foreach ($itemInfoModel1['priceUnits'] as $p ) 
			{
				$mprice = $p['price'];
			}
			$item['marketprice'] = $mprice;
		}
		$item['options'] = $options;
		$item['content'] = array();
		
		$url = 'http://hws.m.taobao.com/cache/wdesc/5.0/?id=' . $itemid;
		
		$response = ihttp_get($url);
		$response = preg_replace('/ (?:width)=(\'|").*?\\1/', ' width="100%"', $response);
		$response = preg_replace('/ (?:height)=(\'|").*?\\1/', ' ', $response);
		$item['content'] = $response;
		
		$thumb_url = array();
		$pics = $item['pics'];
		$piclen = count($pics);
		
		
		if (0 < $piclen) 
		{
			$data['thumb'] = $this->save_image($pics[0], false);
			if (1 < $piclen) 
			{
				$i = 1;
				while ($i < $piclen) 
				{
					$img = $this->save_image($pics[$i], false);
					$thumb_url[] = $img;
					++$i;
				}
			}
		}
		$image_lists = $thumb_url;
		
		$response = $item['content'];
		$content = $response['content'];
		preg_match_all('/<img.*?src=[\\\\\'| \\"](.*?(?:[\\.gif|\\.jpg]?))[\\\\\'|\\"].*?[\\/]?>/', $content, $imgs);
		if (isset($imgs[1])) 
		{
			foreach ($imgs[1] as $img ) 
			{
				$catchimg = $img;
				if (substr($catchimg, 0, 2) == '//') 
				{
					$img = 'http://' . substr($img, 2);
				}
				$im = array('catchimg' => $catchimg, 'system' => $this->save_image($img, true));
				$images[] = $im;
			}
		}
		preg_match('/tfsContent : \\\'(.*)\\\'/', $content, $html);
		$html = iconv('GBK', 'UTF-8', $html[1]);
		if (isset($images)) 
		{
			foreach ($images as $img ) 
			{
				$html = str_replace($img['catchimg'], $img['system'], $html);
			}
		}
		
		//'content' => $html  $item['total']
		//d['marketprice'] = $minprice;  ["sales"]=>
  //string(5) "12418"
		//$item['title'], 'quantity' => $item['total']
		
		
		$goods_data = array();
		$goods_data['title'] = $item['title'];
		$goods_data['marketprice'] = $item['marketprice'];
		$goods_data['sales'] = $item['sales'];
		$goods_data['quantity'] = $item['total'];
		$goods_data['image_lists'] = $image_lists;
		$goods_data['store_id'] = SELLERUID;
		$goods_data['html'] = $html;
		
		//$image_lists 
		$model=new GoodsModel();   
		
		if( empty($item['title']) )
		{
			
			echo json_encode( array('code' =>0) );
		}else{
			$model->add_caiji_Goods($goods_data);
			echo json_encode( array('code' =>1) );
		}
		
		die();
	}
	
	public function save_image($url, $iscontent = false) 
	{
		
		$ext = strrchr($url, '.');
		if (($ext != '.jpeg') && ($ext != '.gif') && ($ext != '.jpg') && ($ext != '.png')) 
		{
			return $url;
		}
		if (trim($url) == '') 
		{
			return $url;
		}
		$filename = time().md5($url). $ext;
		
		$image_dir = ROOT_PATH.'Uploads/image/goods';
	    $image_dir .= '/'.date('Y-m-d').'/';
	    
	    $file_path = '/Uploads/image/goods'.'/'.date('Y-m-d').'/';
	    
	    RecursiveMkdir($image_dir);
		
		$save_dir = $image_dir;
		
		$img = @file_get_contents($url);
		
		
		if (strlen($img) != 0) 
		{
			file_put_contents($save_dir . $filename, $img);
			
			if(!$iscontent)
			{
				return 'goods'.'/'.date('Y-m-d').'/'.$filename;
			}else 
				return $file_path.$filename;
		}
		return '';
	}
	
}
?>
