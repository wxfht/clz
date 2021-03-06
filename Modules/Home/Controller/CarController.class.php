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

namespace Home\Controller;

class CarController extends CommonController {



    //--------begin
    public function reduce_car_goods()
    {

        $_GPC = I('request.');
        $data = array();
        $data['goods_id'] = $_GPC['goods_id'];
        $data['community_id'] = $_GPC['community_id'];
        $data['quantity'] = $_GPC['quantity'];
        $data['sku_str'] = $_GPC['sku_str'];

        if($_GPC['sku_str'] == 'undefined')
        {
            $_GPC['sku_str'] = '';
            $data['sku_str']  = '';
        }

        $data['buy_type'] = $_GPC['buy_type'];
        $data['pin_id'] = $_GPC['pin_id'];
        $data['is_just_addcar'] = $_GPC['is_just_addcar'];
        $data['soli_id'] = isset($_GPC['soli_id']) ? intval($_GPC['soli_id']) : '';

        if( !isset($data['buy_type']) || empty($data['buy_type']) )
        {
            $data['buy_type'] = 'dan';
        }

        $token = $_GPC['token'];
        $weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
        $member_id = $weprogram_token['member_id'];
        $is_just_addcar = empty($data['is_just_addcar']) ? 0: 1;
        $goods_id = $data['goods_id'];

        if( empty($member_id))
        {
            $result = array('code' =>4);
            echo json_encode($result);
            die();
        }

        if (isset($data['goods_id'])) 
        {
            $goods_id = $data['goods_id'];

        } else {

            $goods_id = 0;

        }

        $goods_param = array();
        $product = M('lionfish_comshop_goods')->where( array('id' => $goods_id ) )->find();

        if( $product['grounding'] != 1)
        {
            $json['code'] =6;
            $json['msg']='商品已下架!';
            echo json_encode($json);
            die();
        }

        $is_community = D('Seller/Communityhead')->is_community($data['community_id']);

        if( !$is_community )
        {
            $json['code'] =6;
            $json['msg']='该小区已经不存在!';
            echo json_encode($json);
            die();
        }

        if($is_just_addcar == 1)
        {
            if($product['pick_just'] > 0)
            {
                $json['code'] =6;
                $json['msg']='自提商品，请立即购买';
                echo json_encode($json);
                die();
            }
        }

        //商品存在
        if($product)
        {
            $cart= D('Home/Car');

            if (isset($data['quantity']))
            {
                $quantity = $data['quantity'];

            } else {

                $quantity = 1;

            }

            $option = array();

            if( !empty($data['sku_str']))
            {
                $option = explode('_', $data['sku_str']);

            }

            $cart_goods_quantity = $cart->get_wecart_goods($goods_id,$data['sku_str'],$data['community_id'] ,$token,$data['soli_id'] );
            $key = (int)$goods_id . ':'.$data['community_id'].':';

            if( !empty($data['soli_id']) )
            {
                $key .= $data['soli_id'].':';

            }

            if ($data['sku_str'])
            {
                $key.= base64_encode($data['sku_str']) . ':';

            } else {

                $key.= ':';//xx

            }

            $car_prefix = 'cart.';

            if( !empty($data['buy_type']) && $data['buy_type'] == 'soitaire' )
            {

                $key = 'soitairecart.' . $key;
                $car_prefix = 'soitairecart.';

            }else{

                $key = 'cart.' . $key;

            }

            $json=array('code' =>0);
            $car_info = M('lionfish_comshop_car')->where( array('carkey' => $key,'community_id' => $data['community_id'] ) )->find();
            $tmp_format_data = unserialize($car_info['format_data']);
            //$tmp_format_data['quantity']

            if($tmp_format_data['quantity'] == 1)
            {

                $all_cart = M('lionfish_comshop_car')->where( array('carkey' => $key ,'community_id' =>$data['community_id'],'token' => $token  ) )->delete();

            }else{

                $tmp_format_data['quantity'] = $tmp_format_data['quantity'] - 1;
                M('lionfish_comshop_car')->where( array('id' => $car_info['id'], 'community_id' => $data['community_id'] ) )->save( array('format_data' => serialize($tmp_format_data) ) );

            }

            $cart= D('Home/Car');
            $total=$cart->count_goodscar($token, $data['community_id']);
            $json ['code']  = 1;

            if( $data['buy_type'] != 'dan' )
            {
                $json ['code']  = 2;

            }

            $cart_goods_quantity = $cart->get_wecart_goods($goods_id,$data['sku_str'],$data['community_id'] ,$token);
            $json['success']='成功加入购物车！！';
            $json['total']=$total;
            $json['cur_count']=$cart_goods_quantity;
            $is_limit_distance_buy = D('Home/Front')->get_config_by_name('shop_limit_buy_distance');
            $json['is_limit_distance_buy']=$is_limit_distance_buy;
            $json['goods_total_count'] = 0;

            if( !empty($data['buy_type']) && $data['buy_type'] == 'soitaire' )
            {

                $json['goods_total_count'] = $cart->get_wecart_goods_solicount($goods_id, $data['community_id'],$token, $data['soli_id'] );

            }

            echo json_encode($json);
            die();

        }

    }


    /** 
     * pintuan_newman_notice
     * @Author: flydreame 
     * @Date: 2020-05-20 15:14:07 
     * @Desc:  
     */
    public function add_newcar()
    {
        $_GPC = I('request.');
        $data = array();
        $data['goods_id'] = $_GPC['goods_id'];
        $data['buy_type'] = 'pintuan';
        $data['community_id'] = $_GPC['community_id'];
        $community_id= $data['community_id'];
        $data['quantity'] = 1;
        $token = $_GPC['token'];
        $weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
        $member_id = $weprogram_token['member_id'];
        $goods_id = $data['goods_id'];

        if( empty($member_id))
        {
            $result = array('code' =>4);
            echo json_encode($result);
            die();
        }

        $goods_param = array();
        $product = M('lionfish_comshop_goods')->where( array('id' => $goods_id ) )->find();

        if( $product['grounding'] != 1)
        {
            $json['code'] =6;
            $json['msg']='商品已下架!';
            echo json_encode($json);
            die();
        }

        $goods_description = D('Home/Front')->get_goods_common_field($goods_id , 'total_limit_count,one_limit_count,is_new_buy');
        $pin_model = D('Home/Pin');
        $iszero_opentuan = $pin_model->check_goods_iszero_opentuan( $goods_id );

        if($iszero_opentuan != 1)
        {

            $json['code'] =6;
            $json['msg']='非邀请团商品!';
            echo json_encode($json);
            die();

        }

        $cart= D('Home/Car');

        if($product)
        {
            if( !empty($data['buy_type']) && $data['buy_type'] == 'pintuan' )
            {
                $car_prefix = 'pintuancart.';
            }

            $cart_goods_quantity = $cart->get_wecart_goods($goods_id,$data['sku_str'],$data['community_id'] ,$token,$car_prefix);
            $json=array('code' =>0);
            //$goods_model = D('Home/Goods');
            $goods_quantity=$cart->get_goods_quantity($goods_id);

            //检测商品限购 6 one_limit_count
            /**
            $can_buy_count = D('Home/Front')->check_goods_user_canbuy_count($member_id, $goods_id);

            if(!empty($cart_goods_quantity) && $cart_goods_quantity > 0)
            {

                if($goods_description['one_limit_count'] > 0 && $cart_goods_quantity >= $goods_description['one_limit_count'] )
                {
                    $json['code'] =6;
                    //$json['msg']='已经不能再买了';
                    $json['msg']='您本次只能购买'.$goods_description['one_limit_count'].'个';
                    $json['max_quantity'] = $goods_description['one_limit_count'];
                    echo json_encode($json);
                    die();
                }
                $can_buy_count = $can_buy_count - $cart_goods_quantity;

                if($can_buy_count <= 0)
                {
                   $can_buy_count = -1;
                }
            }

            if($can_buy_count == -1 && $goods_description['total_limit_count'] >0)
            {
                $json['code'] =6;
                //$json['msg']='已经不能再买了';
                $json['msg']='您本次只能购买'.$goods_description['total_limit_count'].'个';
                $json['max_quantity'] = $goods_description['total_limit_count'];
                echo json_encode($json);
                die();
            }else if($can_buy_count >0 && $quantity >$can_buy_count)
            {
                $json['code'] =6;
                $json['msg']='您还能购买'.$can_buy_count.'份';
                $json['max_quantity'] = $can_buy_count;
                echo json_encode($json);
                die();
            }

            //已加入购物车的总数
            if($goods_quantity<$quantity+$cart_goods_quantity){
                $json['code'] =3;
                if ($goods_quantity==0) {
                $json['msg']='已抢光';
            }else{
                // $json['msg']='商品数量不足，剩余'.$goods_quantity.'个！！';
                $json['msg']='商品数量不足';
                $json['max_quantity'] = $goods_quantity;
            }
            echo json_encode($json);
            die();

            }

             **/

            //开始生产订单 TODO...

            $payment = M('lionfish_comshop_member')->where( array('member_id' => $member_id ) )->find();
            $data = array();
            $data['member_id']=$member_id;
            $data['name']= $payment['username'];
            $data['use_score']= 0;//是否使用积分抵扣
            $data['telephone']= '0000';
            $data['shipping_name']= '0000';
            $data['shipping_tel']= '0000';
            $data['shipping_address'] = '';
            $data['shipping_province_id']=0;
            $data['shipping_city_id']=0;
            $data['shipping_stree_id']=0;
            $data['shipping_country_id']=0;
            $data['shipping_method'] = 0;
            $data['delivery']='express';
            $data['pick_up_id']=$community_id;
            $data['ziti_name']='';
            $data['ziti_mobile']='';
            $data['payment_method']='yuer';
            $data['address_id']= 0;
            $data['voucher_id'] = 0;//目前都是平台券
            $data['user_agent']=$_SERVER['HTTP_USER_AGENT'];
            $data['date_added']=time();
            $data['type'] = 'pintuan';
            $data['shipping_fare'] = 0;
            $goods_data = array();
            $goods_data[] = array(
                'goods_id'   => $product['id'],
                'store_id' => 0,
                'name'       => $product['goodsname'],
                'model'      => '',
                'is_pin' => 1,
                'pin_id' => 0,
                'header_disc' => 0,
                'member_disc' => 0,
                'level_name' => '',
                'option'     => '',
                'quantity'   => 1,
                'shipping_fare' => 0,
                'price'      => $product['price'],
                'card_price' => 0,
                'total'      => 0,
                'card_total' => 0 ,
                'is_take_vipcard' => 0,
                'fenbi_li'      => 0,
                'can_man_jian'  => 0,
                'comment' => ''
            );
            $data['is_free_shipping_fare']= 0;
            $data['store_id']= 0;
            $data['order_goods_total_money']= 0;
            $data['goodss'] = $goods_data;
            $data['order_num_alias']=build_order_no($member_id);
            $data['voucher_credit'] = 0;
            $data['score_for_money'] = 0;
            $data['reduce_money'] = 0;
            $data['man_total_free'] = 0;

            $oid = D('Home/Frontorder')->addOrder($data);// D('Order')->addOrder($data);
            $o = array();
            $o['payment_code'] = 'yuer';
            $o['order_status_id'] =  2;
            $o['date_modified']=time();
            $o['pay_time']=time();
            $o['transaction_id'] = '余额支付';
            $o['type'] = 'ignore';

            //ims_
            M('lionfish_comshop_order')->where( array('order_id' => $oid ) )->save( $o );

            //更新到0元开团订单类型

            echo json_encode( array('code' => 0, 'order_id' => $oid ) );
            die();
        }
    }

    //end-
    public function add()
    {
        $gpc = I('request.');
        $data = array();
        $data['goods_id'] = $gpc['goods_id'];
        $data['community_id'] = $gpc['community_id'];
        $data['quantity'] = $gpc['quantity'];
        $data['sku_str'] = $gpc['sku_str'];
        $data['soli_id'] = isset($gpc['soli_id']) ? intval($gpc['soli_id']) :'';

        if($gpc['sku_str'] == 'undefined')
        {
            $gpc['sku_str'] = '';
            $data['sku_str']  = '';
        }

        $pintuan_model_buy = D('Home/Front')->get_config_by_name('pintuan_model_buy');

        if( empty($pintuan_model_buy) || $pintuan_model_buy ==0 )
        {
            $pintuan_model_buy = 0;
        }

        $data['buy_type'] = $gpc['buy_type'];
        $data['pin_id'] = $gpc['pin_id'];
        $data['is_just_addcar'] = $gpc['is_just_addcar'];

        if( !isset($data['buy_type']) || empty($data['buy_type']) )
        {
            $data['buy_type'] = 'dan';

        }else if( !empty($data['buy_type']) && $data['buy_type'] == 'soitaire' )
        {
            $data['buy_type'] = 'soitaire';

        }else if( !empty($data['buy_type']) && $data['buy_type'] == 'pindan' )
        {

            $data['buy_type'] = 'pindan';

        }else if( !empty($data['buy_type']) && $data['buy_type'] == 'pintuan' )
        {
            $data['buy_type'] = 'pintuan';

        }else if( !empty($data['buy_type']) &&  $data['buy_type'] == 'integral' )
        {

            $data['buy_type'] = 'integral';

        }

        $token = $gpc['token'];
        $weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
        $member_id = $weprogram_token['member_id'];
        $puis_not_buy =  D('Home/Front')->get_config_by_name('puis_not_buy');

        if( !empty($puis_not_buy) && $puis_not_buy ==1 )
        {
            $member_info = M('lionfish_comshop_member')->field('level_id')->where( array('member_id' => $member_id) )->find();
            if($member_info['level_id'] == 0)
            {
                $json['code'] =6;
                $json['msg']='普通会员不能购买!';
                echo json_encode($json);
                die();
            }
        }

        $is_just_addcar = empty($data['is_just_addcar']) ? 0: 1;
        $goods_id = $data['goods_id'];

        if( empty($member_id))
        {
            $result = array('code' =>4);
            echo json_encode($result);
            die();
        }

        if (isset($data['goods_id'])) {

            $goods_id = $data['goods_id'];

        } else {

            $goods_id = 0;

        }

        $product = M('lionfish_comshop_goods')->where( array('id' => $goods_id ) )->find();

        if( $product['grounding'] != 1)
        {
            $json['code'] =6;
            $json['msg']='商品已下架!';
            echo json_encode($json);
            die();
        }





        $goods_description = D('Home/Front')->get_goods_common_field($goods_id , 'total_limit_count,one_limit_count,is_new_buy,is_limit_levelunbuy,is_limit_vipmember_buy,is_only_express');



        //is_limit_levelunbuy

        $is_default_levellimit_buy = D('Home/Front')->get_config_by_name('is_default_levellimit_buy');



        if( isset($is_default_levellimit_buy) && $is_default_levellimit_buy == 1 && $goods_description['is_limit_levelunbuy'] == 1 )

        {

            // member_id

            $mb_info = M('lionfish_comshop_member')->field('level_id')->where( array('member_id' => $member_id ) )->find();



            if( $mb_info['level_id'] == 0 )

            {

                $json['code'] =6;

                $json['msg']='默认等级不能购买，请联系客服';

                echo json_encode($json);

                die();

            }

        }



        //is_limit_vipmember_buy 付费会员专享



        $is_default_vipmember_buy = D('Home/Front')->get_config_by_name('is_default_vipmember_buy');



        if( isset($is_default_vipmember_buy) && $is_default_vipmember_buy == 1 && $goods_description['is_limit_vipmember_buy'] == 1 )

        {

            $mb_vip = M('lionfish_comshop_member')->field('card_id,card_begin_time,card_end_time')->where( array('member_id' => $member_id ) )->find();



            //当前时间

            $present_time = time();



            if( $mb_vip['card_id'] == 0 || ( $present_time > $mb_vip['card_end_time'] ) )

            {

                $json['code'] =6;

                $json['msg']='付费会员专享，普通会员不能购买';

                echo json_encode($json);

                die();

            }



        }



        if( !empty($goods_description['is_new_buy']) &&  $goods_description['is_new_buy'] == 1)

        {



            $ck_buy_order = M('lionfish_comshop_order')->field('order_id')->where( "member_id={$member_id} and order_status_id in (1,4,6,7,10,11,12,14)"  )->find();



            if( !empty($ck_buy_order) )

            {

                $json['code'] =6;

                $json['msg']='新人专享!';

                echo json_encode($json);

                die();

            }

        }









        //$data['community_id']
        if($goods_description['is_only_express'] == 0){
        if( $data['buy_type'] == 'dan' || $data['buy_type'] =='soitaire'  || ($pintuan_model_buy ==1 && $data['buy_type'] != 'dan') )

        {

            $is_community = D('Seller/Communityhead')->is_community($data['community_id']);

            if( !$is_community )

            {

                $json['code'] =6;

                $json['msg']='该小区已经不存在!';

                echo json_encode($json);

                die();

            }
            }
        }
        



        //判断是否积分兑换

        if( $product['type'] == 'integral')

        {

            //判断积分是否足够 member_id 暂时关闭以下代码



            $integral_model = D('Home/Integral');

            $check_result = $integral_model->check_user_score_can_pay($member_id, $data['sku_str'], $goods_id);



            if( $check_result['code'] == 1 )

            {

                $json['code'] =6;

                $json['msg']='剩余'.$check_result['cur_score'].'积分，积分不足!';

                echo json_encode($json);

                die();

            }

            /****/

        }



        //6

        if($is_just_addcar == 1)

        {

            if($product['pick_just'] > 0)

            {

                $json['code'] =6;

                $json['msg']='自提商品，请立即购买';

                echo json_encode($json);

                die();

            }

        }



        //商品存在

        if($product){



            $cart= D('Home/Car');



            if (isset($data['quantity'])) {

                $quantity = $data['quantity'];

            } else {

                $quantity = 1;

            }



            $option = array();



            if( !empty($data['sku_str'])){

                $option = explode('_', $data['sku_str']);

            }



            $car_prefix = "cart.";



            if( !empty($data['buy_type']) && $data['buy_type'] == 'pindan' )

            {

                $car_prefix = 'pindancart.';//cart.

            }

            else if( !empty($data['buy_type']) && $data['buy_type'] == 'soitaire' )

            {

                $car_prefix = 'soitairecart.';

            }

            else if( !empty($data['buy_type']) && $data['buy_type'] == 'pintuan' )

            {

                $car_prefix = 'pintuancart.';

            }else if( !empty($data['buy_type']) && $data['buy_type'] == 'integral' ){

                $car_prefix = 'integralcart.';

            }



            //$data['soli_id']

            $cart_goods_quantity = $cart->get_wecart_goods($goods_id,$data['sku_str'],$data['community_id'] ,$token,$car_prefix,$data['soli_id']);





            $cart_goods_all_quantity = $cart->get_wecartall_goods($goods_id,$data['sku_str'],$data['community_id'] ,$token,$car_prefix);





            $json=array('code' =>0);

            //$goods_model = D('Home/Goods');

            $goods_quantity=$cart->get_goods_quantity($goods_id);



            //检测商品限购 6



            $can_buy_count = D('Home/Front')->check_goods_user_canbuy_count($member_id, $goods_id);



            if(!empty($cart_goods_all_quantity) && $cart_goods_all_quantity > 0)

            {

                if($goods_description['one_limit_count'] > 0 && $cart_goods_all_quantity >= $goods_description['one_limit_count'] )

                {

                    $json['code'] =6;

                    //$json['msg']='已经不能再买了';



                    $json['msg']='您本次只能购买'.$goods_description['one_limit_count'].'个';



                    $json['max_quantity'] = $goods_description['one_limit_count'];



                    echo json_encode($json);

                    die();

                }



                $can_buy_count = $can_buy_count - $cart_goods_all_quantity;

                if($can_buy_count <= 0)

                {

                    $can_buy_count = -1;

                }

            }

            if($can_buy_count == -1 && $goods_description['total_limit_count'] >0)

            {

                $json['code'] =6;

                //$json['msg']='已经不能再买了';

                $json['msg']='您本次只能购买'.$goods_description['total_limit_count'].'个';



                $json['max_quantity'] = $goods_description['total_limit_count'];



                echo json_encode($json);

                die();

            }else if($can_buy_count >0 && $quantity >$can_buy_count)

            {

                $json['code'] =6;

                $json['msg']='您还能购买'.$can_buy_count.'份';



                $json['max_quantity'] = $can_buy_count;

                echo json_encode($json);

                die();

            }



            //已加入购物车的总数



            if($goods_quantity<$quantity+$cart_goods_quantity){

                $json['code'] =3;

                if ($goods_quantity==0) {

                    $json['msg']='已抢光';

                }else{

                    $json['msg']='商品数量不足';

                    $json['max_quantity'] = $goods_quantity;

                }



                echo json_encode($json);

                die();

            }

            //rela_goodsoption_valueid





            if(!empty($option))

            {

                $mul_opt_arr = array();



                //ims_

                //$goods_option_mult_value = M('lionfish_comshop_goods_option_item_value')->where( array('goods_id' => $goods_id,'option_item_ids' => $data['sku_str']) )->find();

                $open_redis_server = D('Home/Front')->get_config_by_name('open_redis_server');



                if(!empty($open_redis_server) && $open_redis_server == 1)

                {

                    $goods_option_mult_value_stock = D('Seller/Redisorder')->get_goods_sku_quantity($goods_id, $data['sku_str']);

                }else{

                    $goods_option_mult_value = M('lionfish_comshop_goods_option_item_value')->where( array('goods_id' => $goods_id,'option_item_ids' => $data['sku_str']) )->find();

                }





                if( !empty($goods_option_mult_value_stock) )

                {

                    if($goods_option_mult_value_stock<$quantity+$cart_goods_quantity){

                        $json['code'] =3;

                        $json['msg']='商品数量不足，剩余'.$goods_option_mult_value_stock.'个！！';



                        $json['max_quantity'] = $goods_option_mult_value['stock'];

                        echo json_encode($json);

                        die();

                    }

                }

            }



            //buy_type



            // $this->clear_all_cart(); $data['community_id']



            $format_data_array = array(

                'quantity' => $quantity,

                'community_id' => $data['community_id'],

                'goods_id' => $goods_id,

                'sku_str'=>$data['sku_str'],

                'buy_type' =>$data['buy_type'],

                'soli_id' => $data['soli_id']

            );

            //区分活动商品还是普通商品。做两个购物车，活动商品是需要直接购买的，单独购买商品加入正常的购物车TODO....

            //is_just_addcar 0  1

            if($data['buy_type'] == 'dan' && $is_just_addcar == 0)

            {



                //$cart->removedancar($token);

                //清空一下购物车

                //singledel

                $format_data_array['is_just_addcar'] = 0;

                $format_data_array['singledel'] = 1;



                $cart->addwecar($token,$goods_id,$format_data_array,$data['sku_str'],$data['community_id']);

                $total=$cart->count_goodscar($token ,$data['community_id']);

            }

            else if($data['buy_type'] == 'dan' && $is_just_addcar == 1)

            {

                //singledel

                $format_data_array['is_just_addcar'] = 1;

                $format_data_array['singledel'] = 1;

                $cart->addwecar($token,$goods_id,$format_data_array,$data['sku_str'],$data['community_id']);

                $total=$cart->count_goodscar($token, $data['community_id']);

            }

            else if( !empty($data['buy_type']) && $data['buy_type'] == 'soitaire' )

            {

                //清理单独购买的商品

                $format_data_array['is_just_addcar'] = 0;

                $format_data_array['singledel'] = 1;



                $cart->addwecar($token,$goods_id,$format_data_array,$data['sku_str'],$data['community_id'],$car_prefix,$data['soli_id']);

                $total=0;

            }

            else if( !empty($data['buy_type']) && $data['buy_type'] == 'pindan' )

            {

                //清理单独购买的商品

                $cart->removeActivityAllcar($token, 'pindancart.');

                $format_data_array['is_just_addcar'] = 0;

                $format_data_array['singledel'] = 1;



                $cart->addwecar($token,$goods_id,$format_data_array,$data['sku_str'],$data['community_id'],$car_prefix);

                $total=0;

            }

            else if( !empty($data['buy_type']) && $data['buy_type'] == 'pintuan' )

            {

                $pin_id = isset($data['pin_id']) ? $data['pin_id'] : 0;



                if( $pin_id > 0 )

                {

                    $pin_info_tmp = M('lionfish_comshop_pin')->where( array('pin_id' => $pin_id ) )->find();



                    if( !empty($pin_info_tmp) && $pin_info_tmp['is_newman_takein'] == 1 )

                    {

                        //检测是否新人

                        //检测是否购买过

                        $od_status = "1,2,4,6,7,8,9,10,11,12,14";



                        $od_buy_count = M('lionfish_comshop_order')->where("order_status_id in ({$od_status}) and member_id=".$member_id)->count();



                        if( !empty($od_buy_count) && $od_buy_count >0 )

                        {

                            $json['code'] =3;

                            $json['msg']='新人专享';



                            echo json_encode($json);

                            die();

                        }

                    }



                }



                //清理拼团的商品

                $cart->removeActivityAllcar($token, 'pintuancart.');

                $format_data_array['is_just_addcar'] = 0;

                $format_data_array['singledel'] = 1;

                $format_data_array['pin_id'] = $pin_id;



                $cart->addwecar($token,$goods_id,$format_data_array,$data['sku_str'],$data['community_id'],$car_prefix);

                $total=0;

            }

            else if( !empty($data['buy_type']) && $data['buy_type'] == 'integral' )

            {



                //清理拼团的商品 $data['pin_id']

                $cart->removeActivityAllcar($token, 'integralcart.');

                $format_data_array['is_just_addcar'] = 0;

                $format_data_array['singledel'] = 1;



                $cart->addwecar($token,$goods_id,$format_data_array,$data['sku_str'],$data['community_id'],$car_prefix);

                $total=0;

            }

            else {

                //buy_type:pin  活动购物车。

                $pin_id = isset($data['pin_id']) ? $data['pin_id'] : 0;



                //lottery

                if( $product['type'] == 'lottery' && $product['type'] == 'lottery' )

                {

                    /**

                    //等待把抽奖的活动打开

                    $now_time = time();

                    $lottery_goods_info =  M('lottery_goods')->where( array('goods_id' => $goods_id) )->find();



                    if($lottery_goods_info['end_time'] < $now_time)

                    {

                    $json['code'] =6;

                    $json['msg']='抽奖活动已结束';

                    echo json_encode($json);

                    die();

                    }

                     **/

                }



                //检测商品是否老带新，新人才能参团

                if($pin_id > 0 )

                {

                    //等待把老带新的活动打开

                    /**

                    if($product['type'] == 'newman')

                    {

                    $new_mamn_buy = $goods_model->check_goods_new_manbug($member_id);

                    if($new_mamn_buy>0)

                    {

                    $json['code'] =5;

                    $json['msg']='该商品只能新人参团';

                    echo json_encode($json);

                    die();

                    }

                    }

                     **/

                }



                $format_data_array['pin_id'] = $pin_id;



                $cart->add_activitycar($token, $goods_id,$format_data_array,$data['sku_str']);

                $total=$cart->count_activitycar($token);

            }





            $carts = M('lionfish_comshop_car')->where( array('token' => $token,'community_id' => $data['community_id'],'carkey' => 'cart_total') )->find();



            if( !empty($data['buy_type']) && $data['buy_type'] == 'dan' )

            {

                if( !empty($carts) )

                {

                    $car_data = array();

                    $car_data['format_data'] = serialize(array('quantity' => $total));

                    $car_data['modifytime'] = 1;



                    M('lionfish_comshop_car')->where( array('token' => $token,'community_id' => $data['community_id'],'carkey' => 'cart_total') )->save($car_data);



                } else{



                    $car_data = array();

                    $car_data['token'] = $token;



                    $car_data['community_id'] = $data['community_id'];

                    $car_data['carkey'] = 'cart_total';

                    $car_data['format_data'] = serialize(array('quantity' => $total));



                    M('lionfish_comshop_car')->add($car_data);

                }

            }



            //session('cart_total',$total);

            $json ['code']  = 1;

            if( $data['buy_type'] != 'dan' )

            {

                $json ['code']  = 2;

            }

            $json['success']='成功加入购物车！！';

            $json['total']=$total;



            $cart_goods_quantity = $cart->get_wecart_goods($goods_id,$data['sku_str'],$data['community_id'] ,$token , $car_prefix);

            $json['cur_count']=$cart_goods_quantity;





            //soitaire

            $is_limit_distance_buy = D('Home/Front')->get_config_by_name('shop_limit_buy_distance');



            $json['is_limit_distance_buy']=$is_limit_distance_buy;



            $json['goods_total_count'] = 0;



            if( !empty($data['buy_type']) && $data['buy_type'] == 'soitaire' )

            {

                $json['goods_total_count'] = $cart->get_wecart_goods_solicount($goods_id, $data['community_id'],$token, $data['soli_id'] );

            }





            echo json_encode($json);

            die();

        }



    }



    //显示购物车中商品列表

    function show_cart_goods(){



        $gpc = I('request.');



        $token = $gpc['token'];

        $community_id = $gpc['community_id'];

        $soli_id = isset($gpc['soli_id']) ? intval($gpc['soli_id']) : '';



        $weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();



        $member_id = $weprogram_token['member_id'];



        $is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');

        $is_open_vipcard_buy = !empty($is_open_vipcard_buy) && $is_open_vipcard_buy ==1 ? 1:0;

        $modify_vipcard_name = D('Home/Front')->get_config_by_name('modify_vipcard_name');





        $is_vip_card_member = 0;

        $is_member_level_buy = 0;



        if( $member_id > 0 )

        {

            $member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id ) )->find();



            if( !empty($is_open_vipcard_buy) && $is_open_vipcard_buy == 1 )

            {



                $now_time = time();



                if( $member_info['card_id'] >0 && $member_info['card_end_time'] > $now_time )

                {

                    $is_vip_card_member = 1;//还是会员

                }else if( $member_info['card_id'] >0 && $member_info['card_end_time'] < $now_time ){

                    $is_vip_card_member = 2;//已过期

                }

            }



            if($is_vip_card_member != 1 && $member_info['level_id'] >0 )

            {

                $is_member_level_buy = 1;

            }

        }



        $buy_type = isset($gpc['buy_type']) ? $gpc['buy_type']: 'dan';



        if( empty($member_id) )

        {

            //需要登录

            echo json_encode( array('code' =>5) );

            die();

        }



        $cart =  D('Home/Car');



        $goods = $cart->get_all_goodswecar($buy_type, $token, 0, $community_id,$soli_id);





        $seller_goodss = array();



        $seller_goodss_mult = array();



        //is_only_express

        $tp_ar = array();



        foreach($goods as $key => $val)

        {

            //$goods_store_field =  M('goods')->field('store_id')->where( array('goods_id' => $val['goods_id']) )->find();

            //$seller_goodss[ $goods_store_field['store_id'] ]['goods'][$key] = $val;



            $supply_id = D('Home/Front')->get_goods_supply_id($val['goods_id']);

            if($supply_id > 0)

            {

                $supply_info = D('Home/Front')->get_supply_info($supply_id);



                if($supply_info['type'] ==0)

                {

                    $supply_id = 0;

                }

            }





            $seller_goodss[ $supply_id ]['goods'][$key] = $val;



            $tp_ar[] = $val['is_only_express'];

            $seller_goodss_mult[$val['is_only_express']][ $supply_id ]['goods'][$key] = $val;//new 0719



        }



        $ck_goodstype_count = 0;

        $vipcard_save_money = 0;



        $level_save_money = 0;



        foreach($seller_goodss_mult as $key => $seller_goodss_tp)

        {



            foreach($seller_goodss_tp as $store_id => $val)

            {

                //total

                $seller_voucher_list = array();

                $seller_total_fee = 0;

                $total_trans_free = 0;



                $tmp_goods = array();



                $is_store_ck = false;



                foreach($val['goods'] as $kk =>$d_goods)

                {

                    $seller_total_fee += $d_goods['total'];



                    $total_trans_free  += $d_goods[$kk]['trans_free'];

                    $val['goods'][$kk] = $d_goods;



                    $tp_val = array();

                    $tp_val['id'] = $d_goods['goods_id'];

                    $tp_val['key'] = $d_goods['key'];

                    if($d_goods['singledel'] == 1)

                    {

                        $tp_val['isselect'] = true;

                        $is_store_ck = true;

                        $ck_goodstype_count++;

                        if($d_goods['is_take_vipcard'] == 1)

                        {

                            $vipcard_save_money = $d_goods['total'] - $d_goods['card_total'];

                        }else if($d_goods['is_mb_level_buy']  == 1 ){

                            $level_save_money = $d_goods['total'] - $d_goods['level_total'];

                        }

                    } else {

                        $tp_val['isselect'] = false;

                    }



                    $tp_val['imgurl'] = $d_goods['image'];

                    $tp_val['edit'] = 'inline';

                    $tp_val['title'] = $d_goods['name'];

                    $tp_val['finish'] = 'none';

                    $tp_val['description'] = 'description';



                    $option_arr  = array();

                    $option_str = "";

                    foreach($d_goods['option'] as $option_val)

                    {

                        //$option_arr[] = $option_val['name'].':'.$option_val['value'];

                        $option_arr[] = $option_val['value'];

                    }

                    if(!empty($option_arr))

                    {

                        $option_str = implode(';', $option_arr);

                    }





                    $tp_val['can_buy'] = D('Home/Pingoods')->get_goods_time_can_buy($d_goods['goods_id']);



                    $tp_val['option_can_buy'] = D('Home/Pingoods')->get_goods_option_can_buy( $d_goods['goods_id'], $d_goods['sku_str']);





                    $tp_val['goodstype'] = $option_str;

                    $tp_val['goodstypeedit'] = $option_str;

                    $tp_val['goodsnum'] = $d_goods['quantity'];

                    $tp_val['can_man_jian'] = $d_goods['can_man_jian'];

                    $tp_val['max_quantity'] = $d_goods['max_quantity'];

                    $tp_val['cartype'] = 'inline';

                    $tp_val['currntprice'] = $d_goods['price'];

                    $tp_val['card_price'] = $d_goods['card_price'];



                    $tp_val['levelprice'] = $d_goods['levelprice'];// 会员等级价格

                    $tp_val['is_mb_level_buy'] = $d_goods['is_mb_level_buy'];//是否可以会员等级价格购买



                    $tp_val['is_take_vipcard'] = $d_goods['is_take_vipcard'];

                    $tp_val['price'] = $d_goods['shop_price'];

                    $tp_val['is_new_buy'] = $d_goods['is_new_buy'];



                    $tmp_goods[] = $tp_val;



                }



                //$store_info = M('seller')->field('s_id,s_true_name,s_logo')->where( array('s_id' => $store_id) )->find();

                //$store_info['s_logo'] = C('SITE_URL').'Uploads/image/'.$store_info['s_logo'];



                $store_info = array('s_true_name' => '','s_id' => 1);

                $s_logo = D('Home/Front')->get_config_by_name('shoplogo');



                if( !empty($s_logo) )

                {

                    $s_logo = tomedia($s_logo);

                }



                $val['store_info'] = $store_info;



                $store_data = array();

                $store_data['id'] = $store_info['s_id'];

                if($is_store_ck)

                {

                    $store_data['isselect'] = true;

                } else {

                    $store_data['isselect'] = false;

                }



                $store_data['shopname'] = $store_info['s_true_name'];

                $store_data['caredit'] = 'inline';

                $store_data['finish'] = 'none';

                $store_data['count'] = '0.00';



                $is_open_fullreduction = D('Home/Front')->get_config_by_name('is_open_fullreduction');

                $full_money = D('Home/Front')->get_config_by_name('full_money');

                $full_reducemoney = D('Home/Front')->get_config_by_name('full_reducemoney');



                if(empty($full_reducemoney) || $full_reducemoney <= 0)

                {

                    $is_open_fullreduction = 0;

                }



                $store_data['is_open_fullreduction'] = $is_open_fullreduction;

                $store_data['full_money'] = $full_money;

                $store_data['full_reducemoney'] = $full_reducemoney;







                $store_data['goodstype'] = 2;

                $store_data['goodstypeselect'] = 0;

                $store_data['shopcarts'] = $tmp_goods;



                $seller_goodss_tp[$store_id] = $store_data;

                $i++;

            }



            $seller_goodss_mult[$key] = $seller_goodss_tp;



        }









        foreach($seller_goodss as $store_id => $val)

        {

            //total

            $seller_voucher_list = array();

            $seller_total_fee = 0;

            $total_trans_free = 0;



            $tmp_goods = array();



            $is_store_ck = false;



            foreach($val['goods'] as $kk =>$d_goods)

            {

                $seller_total_fee += $d_goods['total'];



                $total_trans_free  += $d_goods[$kk]['trans_free'];

                $val['goods'][$kk] = $d_goods;



                $tp_val = array();

                $tp_val['id'] = $d_goods['goods_id'];

                $tp_val['key'] = $d_goods['key'];

                if($d_goods['singledel'] == 1)

                {

                    $tp_val['isselect'] = true;

                    $is_store_ck = true;

                    $ck_goodstype_count++;

                } else {

                    $tp_val['isselect'] = false;

                }



                $tp_val['imgurl'] = $d_goods['image'];

                $tp_val['edit'] = 'inline';

                $tp_val['title'] = $d_goods['name'];

                $tp_val['finish'] = 'none';

                $tp_val['description'] = 'description';



                $option_arr  = array();

                $option_str = "";

                foreach($d_goods['option'] as $option_val)

                {

                    //$option_arr[] = $option_val['name'].':'.$option_val['value'];

                    $option_arr[] = $option_val['value'];

                }

                if(!empty($option_arr))

                {

                    $option_str = implode(';', $option_arr);

                }





                $tp_val['can_buy'] = D('Home/Pingoods')->get_goods_time_can_buy($d_goods['goods_id']);



                $tp_val['goodstype'] = $option_str;

                $tp_val['goodstypeedit'] = $option_str;

                $tp_val['goodsnum'] = $d_goods['quantity'];

                $tp_val['can_man_jian'] = $d_goods['can_man_jian'];

                $tp_val['max_quantity'] = $d_goods['max_quantity'];

                $tp_val['cartype'] = 'inline';

                $tp_val['currntprice'] = $d_goods['price'];

                $tp_val['price'] = $d_goods['shop_price'];

                $tp_val['card_price'] = $d_goods['card_price'];

                $tp_val['is_take_vipcard'] = $d_goods['is_take_vipcard'];

                $tp_val['is_new_buy'] = $d_goods['is_new_buy'];



                $tmp_goods[] = $tp_val;



            }



            //$store_info = M('seller')->field('s_id,s_true_name,s_logo')->where( array('s_id' => $store_id) )->find();

            //$store_info['s_logo'] = C('SITE_URL').'Uploads/image/'.$store_info['s_logo'];



            $store_info = array('s_true_name' => '','s_id' => 1);

            $s_logo = D('Home/Front')->get_config_by_name('shoplogo');



            if( !empty($s_logo) )

            {

                $s_logo = tomedia($s_logo);

            }



            $val['store_info'] = $store_info;



            $store_data = array();

            $store_data['id'] = $store_info['s_id'];

            if($is_store_ck)

            {

                $store_data['isselect'] = true;

            } else {

                $store_data['isselect'] = false;

            }



            $store_data['shopname'] = $store_info['s_true_name'];

            $store_data['caredit'] = 'inline';

            $store_data['finish'] = 'none';

            $store_data['count'] = '0.00';



            $is_open_fullreduction = D('Home/Front')->get_config_by_name('is_open_fullreduction');

            $full_money = D('Home/Front')->get_config_by_name('full_money');

            $full_reducemoney = D('Home/Front')->get_config_by_name('full_reducemoney');



            if(empty($full_reducemoney) || $full_reducemoney <= 0)

            {

                $is_open_fullreduction = 0;

            }



            $store_data['is_open_fullreduction'] = $is_open_fullreduction;

            $store_data['full_money'] = $full_money;

            $store_data['full_reducemoney'] = $full_reducemoney;







            $store_data['goodstype'] = 2;

            $store_data['goodstypeselect'] = 0;

            $store_data['shopcarts'] = $tmp_goods;





            $seller_goodss[$store_id] = $store_data;

            $i++;

        }



        // 商家是否休息

        $is_comunity_rest = D('Seller/Communityhead')->is_community_rest($community_id);

        $open_man_orderbuy = D('Home/Front')->get_config_by_name('open_man_orderbuy');

        $man_orderbuy_money = D('Home/Front')->get_config_by_name('man_orderbuy_money');

        $is_show_guess_like = D('Home/Front')->get_config_by_name('is_show_guess_like');





        // 免配送 man_free_tuanzshipping>0开启

        $delivery_type_ziti = D('Home/Front')->get_config_by_name('delivery_type_ziti');

        $delivery_type_express = D('Home/Front')->get_config_by_name('delivery_type_express');

        $delivery_type_tuanz = D('Home/Front')->get_config_by_name('delivery_type_tuanz');

        $man_free_tuanzshipping = $delivery_tuanz_money = 0;

        if($delivery_type_ziti!=1 && $delivery_type_express!=1 && $delivery_type_tuanz==1) {

            $man_free_tuanzshipping = D('Home/Front')->get_config_by_name('man_free_tuanzshipping');

            if($man_free_tuanzshipping>1 && !empty($man_free_tuanzshipping)) {

                $delivery_tuanz_money = D('Home/Front')->get_config_by_name('delivery_tuanz_money');

            }

        }



        $need_data = array();

        $need_data['code'] = 0;

        $need_data['carts'] = $seller_goodss;

        $need_data['mult_carts'] = $seller_goodss_mult;

        $need_data['is_comunity_rest'] = $is_comunity_rest;

        $need_data['open_man_orderbuy'] = $open_man_orderbuy;

        $need_data['man_orderbuy_money'] = $man_orderbuy_money;

        $need_data['is_show_guess_like'] = $is_show_guess_like;

        $need_data['man_free_tuanzshipping'] = $man_free_tuanzshipping;

        $need_data['delivery_tuanz_money'] = $delivery_tuanz_money;



        $need_data['is_member_level_buy'] = $is_member_level_buy;//当前会员折扣 购买，1是，0否

        $need_data['level_save_money'] = $level_save_money;//会员折扣省的钱



        $need_data['is_vip_card_member'] = $is_vip_card_member;//当前会员是否是 会员卡会员 0 不是，1是，2已过期

        $need_data['vipcard_save_money'] = $vipcard_save_money;//vip能节约的金额

        $need_data['is_open_vipcard_buy'] = $is_open_vipcard_buy;//vip能节约的金额

        $need_data['modify_vipcard_name'] = $modify_vipcard_name;



        echo json_encode( $need_data );

        die();



    }



    public function checkout_flushall()

    {

        $gpc = I('request.');



        $token = $gpc['token'];


        if($gpc['community_id'] =='undefined'){
            $community_id = 0  ;
        }else{
        $community_id = $gpc['community_id'];
        }



        $weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

        $member_id = $weprogram_token['member_id'];



        //dan soitaire

        $buy_type  = isset($gpc['buy_type']) ? $gpc['buy_type'] : 'dan';



        //$car_prefix = 'soitairecart.';



        //buy_type和soli_id吗





        $data = array();

        $data['car_key'] = $gpc['car_key'];

        $data['all_keys_arr'] = $gpc['all_keys_arr'];



        //car_key:cart.6:MTc0:,cart.13:MjcwXzI3Mw==:

        //all_keys_arr:cart.6:MTc0:_1,cart.13:MjcwXzI3Mw==:_1



        $car_key = explode(',', $data['car_key']);

        $all_keys_arr = explode(',', $data['all_keys_arr']) ;



        $save_keys = array();

        if(!empty($all_keys_arr)){

            foreach($all_keys_arr as $val)

            {

                $tmp_val = explode('_', $val);

                $save_keys[ $tmp_val[0] ] = $tmp_val[1];

            }

        }







        if( $buy_type == 'dan')

        {

            $all_cart = M('lionfish_comshop_car')->where("community_id={$community_id} and token='{$token}' and carkey like 'cart.%'")->select();



        }else if( $buy_type == 'soitaire' ){

            $all_cart = M('lionfish_comshop_car')->where("community_id={$community_id} and token='{$token}' and carkey like 'soitairecart.%'")->select();

        }



        if(!empty($all_cart))

        {

            foreach($all_cart as $val)

            {

                $tmp_format_data = unserialize($val['format_data']);

                $tmp_format_data['singledel'] = 0;





                $tmp_format_data['quantity'] = isset( $save_keys[$val['carkey']] ) ? $save_keys[$val['carkey']] : $tmp_format_data['quantity'];





                M('lionfish_comshop_car')->where( array('id' => $val['id'] ,'community_id' => $community_id) )->save( array('format_data' => serialize($tmp_format_data) ) );

            }

        }



        if(!empty($car_key)){
            $song_type_arr=[];
            foreach( $car_key as $key )

            {



                $car_info = M('lionfish_comshop_car')->where( array('community_id' => $community_id,'carkey' => $key ,'token' => $token) )->find();

                if( !empty($car_info) )

                {

                    $tmp_format_data = unserialize($car_info['format_data']);

                    $tmp_format_data['singledel'] = 1;

                    $quantity = $tmp_format_data['quantity'];

                    $goods_id = $tmp_format_data['goods_id'];

                    $sku_str = $tmp_format_data['sku_str'];



                    $check_json = $this->_check_can_buy($member_id, $goods_id,$quantity);







                    if($check_json['code'] != 0)

                    {

                        $tmp_format_data['quantity'] = $check_json['count'];





                        M('lionfish_comshop_car')->where( array('id' =>  $car_info['id'], 'community_id' => $community_id) )->save( array('format_data' => serialize($tmp_format_data) ) );

                        echo json_encode( array('code' => 6,'msg' => $check_json['msg']) );

                        die();

                    }

                    if($check_json['song_type']){
                        if(!$song_type_arr){
                            $song_type_arr=explode(',',$check_json['song_type']);
                        }else{
                            if(count($song_type_arr)>count(explode(',',$check_json['song_type']))){
                                $son_tp=explode(',',$check_json['song_type']);
                                foreach($song_type_arr as $v){
                                    if(!in_array($v,$son_tp)){
                                        // echo json_encode( array('code' => 6,'msg' => '所选商品配送不一致') );

                                        // die();
                                    }
                                }
                            }
                            $son_tp=explode(',',$check_json['song_type']);
                            foreach($son_tp as $v){
                                if(!in_array($v,$song_type_arr)){
                                    // echo json_encode( array('code' => 6,'msg' => '所选商品配送不一致') );

                                    // die();
                                }
                            }
                        }
                    }

                    //check sku is ok



                    $check_json = $this->_check_goods_sku_canbuy($goods_id,$sku_str);



                    if($check_json['code'] != 0)

                    {

                        echo json_encode( array('code' => 6,'msg' => $check_json['msg']) );

                        die();

                    }







                    $check_json = $this->_check_goods_quantity($goods_id,$quantity,$sku_str);



                    if($check_json['code'] != 0)

                    {

                        echo json_encode( array('code' => 6,'msg' => $check_json['msg']) );

                        die();

                    }



                    M('lionfish_comshop_car')->where( array('id' => $car_info['id'], 'community_id' => $community_id) )->save( array('format_data' => serialize($tmp_format_data) ) );

                }

            }

        }

        $is_limit_distance_buy = D('Home/Front')->get_config_by_name('shop_limit_buy_distance');



        echo json_encode( array('code' => 0, 'data' => $is_limit_distance_buy) );

        die();

    }



    public function _check_goods_sku_canbuy($goods_id,$sku_str)

    {



        $json = array('code' => 0);



        $goods_info = M('lionfish_comshop_goods')->field('goodsname as name')->where( array('id' => $goods_id ) )->find();



        if(!empty($sku_str))

        {

            $goods_option_mult_value = M('lionfish_comshop_goods_option_item_value')->field('stock as quantity')->where( array('option_item_ids' => $sku_str,'goods_id' => $goods_id) )->find();



            if( empty($goods_option_mult_value) )

            {

                $json['code'] =3;

                $json['msg']=mb_substr($goods_info['name'],0,4,'utf-8').'，规格已失效，删除后再结算';

            }

        }



        return $json;

    }





    public function del_car_goods()

    {

        $gpc = I('request.');





        $token = $gpc['token'];

        $community_id = $gpc['community_id'];



        $carkey = $gpc['carkey'];





        M('lionfish_comshop_car')->where( "community_id={$community_id} and token='{$token}' and carkey='{$carkey}'" )->delete();



        echo json_encode( array('code' => 0) );

        die();



    }



    public function _check_goods_quantity($goods_id,$quantity,$sku_str)

    {

        $gpc = I('request.');





        $goods_info = M('lionfish_comshop_goods')->field('goodsname as name')->where( array('id' => $goods_id) )->find();



        $goods_quantity= D('Home/Car')->get_goods_quantity($goods_id);







        $json = array('code' => 0);



        if($goods_quantity<$quantity){

            $json['code'] =3;

            $json['msg']= mb_substr($goods_info['name'],0,4,'utf-8').'...，商品数量不足，剩余'.$goods_quantity.'个！！';



        }else if(!empty($sku_str))

        {

            $mul_opt_arr = array();



            $goods_option_mult_value = M('lionfish_comshop_goods_option_item_value')->field('stock as quantity')->where( array('option_item_ids' => $sku_str,'goods_id' => $goods_id) )->find();



            if( !empty($goods_option_mult_value) )

            {

                if($goods_option_mult_value['quantity']<$quantity+$cart_goods_quantity){

                    $json['code'] =3;

                    $json['msg']=mb_substr($goods_info['name'],0,4,'utf-8').'...，商品数量不足，剩余'.$goods_option_mult_value['quantity'].'个！！';

                }

            }

        }

        return $json;

    }



    private function _check_can_buy($member_id, $goods_id,$quantity)

    {

        $gpc = I('request.');



        $can_buy_count =  D('Home/Front')->check_goods_user_canbuy_count($member_id, $goods_id);



        $goods_info = M('lionfish_comshop_goods')->where( array('id' => $goods_id) )->find();



        $goods_description = D('Home/Front')->get_goods_common_field($goods_id , 'per_number');







        $json = array();

        if($can_buy_count == -1)

        {

            $json['code'] =6;

            $json['msg']=mb_substr($goods_info['name'],0,4,'utf-8').'...，您本次只能购买'.$goods_description['per_number'].'个';





        }else if($can_buy_count >0 && $quantity >$can_buy_count)

        {

            $json['code'] =6;

            //$json['msg']=mb_substr($goods_info['name'],0,4,'utf-8').'...，您还能购买'.$can_buy_count.'份';



            $json['msg']='本次最多购买'.$can_buy_count.'份';

            $json['count']=$can_buy_count;



        }else{

            $json['code'] = 0;
            $json['song_type']=$goods_info['song_type'];

        }

        return $json;

    }



    private function _add_address($token,$userName,$telNumber,$provinceName,$cityName, $countyName,$detailInfo,$latitude='',$longitude='',$lou_meng_hao='')

    {

        $_GPC = I('request.');



        $weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

        $member_id = $weprogram_token['member_id'];





        //  lionfish_comshop_area



        $province_info = M('lionfish_comshop_area')->where("name like '%{$provinceName}%' ")->find();



        //htong  地区表不能更改 开始
        // if( !empty($province_info))
        // {

            $province_id = $province_info['id'];

        // }else{





        //     $area_data = array();

        //     $area_data['name'] = $provinceName;

        //     $area_data['pid'] = 0;

        //     $area_data['code'] = $max_dp['code']+1;



        //     $province_id = M('lionfish_comshop_area')->add($area_data);



        //     $up_data = array();

        //     $up_data['code'] = $province_id;



        //     M('lionfish_comshop_area')->where( array('id' => $province_id ) )->save($up_data);



        // }



        $city_info = M('lionfish_comshop_area')->where("name like '%{$cityName}%'")->find();



        // if( !empty($city_info))

        // {

            $city_id = $city_info['id'];

        // }else{



        //     $max_dp = M('lionfish_comshop_area')->order('code desc')->find();



        //     $area_data = array();

        //     $area_data['name'] = $cityName;

        //     $area_data['pid'] = $province_id;

        //     $area_data['code'] = $max_dp['code']+1;



        //     $city_id = M('lionfish_comshop_area')->add( $area_data);



        //     $up_data = array();

        //     $up_data['code'] = $city_id;



        //     M('lionfish_comshop_area')->where( array('id' => $city_id ) )->save($up_data);



        // }



        $country_info = M('lionfish_comshop_area')->where( "name like '%{$countyName}%' " )->find();



        // if( !empty($country_info))

        // {

            $country_id = $country_info['id'];

        // }else{



        //     $max_dp = M('lionfish_comshop_area')->order('code desc')->find();



        //     $area_data = array();

        //     $area_data['name'] = $countyName;

        //     $area_data['pid'] = $city_id;

        //     $area_data['code'] = $max_dp['code']+1;



        //     $country_id = M('lionfish_comshop_area')->add( $area_data );



        //     $up_data = array();

        //     $up_data['code'] = $country_id;



        //     M('lionfish_comshop_area')->where( array('id' => $country_id ) )->save( $up_data );



        // }
		//htong  地区表不能更改 结束




        $address_param = array();



        $address_param['member_id'] = $member_id;

        $address_param['province_id'] = $province_id;

        $address_param['country_id'] = $country_id;

        $address_param['city_id'] = $city_id;

        $address_param['address'] = $detailInfo;

        $address_param['name'] = $userName;

        $address_param['telephone'] = $telNumber;



        $address_param['lon_lat'] = '';

        $address_param['lou_meng_hao'] = $lou_meng_hao;



        if(!empty($latitude))

        {

            $address_param['lon_lat'] = $longitude.','.$latitude;

        }







        $has_addre = M('lionfish_comshop_address')->where($address_param)->find();



        if(empty($has_addre))

        {



            $has_default_address = M('lionfish_comshop_address')->where( array('is_default' => 1,'member_id' => $member_id) )->find();





            $address_data = array();

            $address_data['member_id'] = $member_id;

            $address_data['name'] = $userName;

            $address_data['telephone'] = $telNumber;

            $address_data['lou_meng_hao'] = $lou_meng_hao;

            if(!empty($latitude))

            {

                $address_data['lon_lat'] = $longitude.','.$latitude;

            }else{

                $address_data['lon_lat'] = '';

            }



            $address_data['address'] = $detailInfo;

            $address_data['address_name'] = empty($data['address_name']) ? 'HOME' : $data['address_name'];

            if(!empty($has_default_address) && false)

            {

                $address_data['is_default'] = 0;

            }else{

                $data = array();

                $data['is_default'] = 0;



                M('lionfish_comshop_address')->where( array('member_id' => $member_id ) )->save($data);



                $address_data['is_default'] = 1;

            }



            $address_data['city_id'] = $city_id;

            $address_data['country_id'] = $country_id;

            $address_data['province_id'] = $province_id;



            $res = M('lionfish_comshop_address')->add($address_data);

        }else{

            $res = $has_addre['address_id'];

        }



        return $res;

    }



    public function checkout_copy()

    {

        $gpc = I('request.');





        $buy_type = isset($gpc['buy_type']) ? $gpc['buy_type'] : 'dan';



        $pintuan_model_buy = D('Home/Front')->get_config_by_name('pintuan_model_buy');



        if( empty($pintuan_model_buy) || $pintuan_model_buy ==0 )

        {

            $pintuan_model_buy = 0;

        }





        $mb_city_name = isset($gpc['mb_city_name']) ? $gpc['mb_city_name'] : '';



        if($buy_type == 'undefined')

        {

            $buy_type = 'dan';

        }



        $community_id = $gpc['community_id'];

        $token = $gpc['token'];



        $voucher_id = isset($gpc['voucher_id']) ? $gpc['voucher_id'] : 0;



        $soli_id = isset($gpc['soli_id']) ? intval($gpc['soli_id']) : '';



        $use_quan_str = isset($gpc['use_quan_str']) ? $gpc['use_quan_str'] : '';

        $use_quan_arr = array();



        if($use_quan_str != '')

        {

            $use_quan_arr_tmp = explode('@',$use_quan_str );

            foreach($use_quan_arr_tmp as $val)

            {

                $tmp_arr = explode('_', $val);

                $use_quan_arr[$tmp_arr[0]] = $tmp_arr[1];

            }

        }



        $weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

        $member_id = $weprogram_token['member_id'];





        $is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');

        $is_open_vipcard_buy = !empty($is_open_vipcard_buy) && $is_open_vipcard_buy ==1 ? 1:0;



        $is_vip_card_member = 0;

        $is_member_level_buy = 0;



        if( $member_id > 0 )

        {

            $member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id ) )->find();



            if( !empty($is_open_vipcard_buy) && $is_open_vipcard_buy == 1 )

            {



                $now_time = time();



                if( $member_info['card_id'] >0 && $member_info['card_end_time'] > $now_time )

                {

                    $is_vip_card_member = 1;//还是会员

                }else if( $member_info['card_id'] >0 && $member_info['card_end_time'] < $now_time ){

                    $is_vip_card_member = 2;//已过期

                }

            }



            if($is_vip_card_member != 1 && $member_info['level_id'] >0 )

            {

                $is_member_level_buy = 1;

            }

        }







        if( empty($member_id) )

        {

            //需要登录

            echo json_encode( array('code' =>5) );

            die();

        }



        $cart = D('Home/Car');







        if ((!$cart->has_goodswecar($buy_type,$token,$community_id) ) ) {

            //购物车中没有商品

            echo json_encode( array('code' =>4) );

            die();

        }





        $member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id) )->find();



        //soitaire

        $goods=$cart->get_all_goodswecar($buy_type, $token,1,$community_id,$soli_id);





        $address = M('lionfish_comshop_address')->where( array('member_id' => $member_id) )->order('is_default desc,address_id desc')->find();



        $add_old_order = M('lionfish_comshop_order')->field('address_id')->where(  array('delivery' => 'express','member_id' => $member_id) )->order('order_id desc')->find();



        if( !empty($add_old_order) && $add_old_order['address_id'] > 0)

        {

            $address = M('lionfish_comshop_address')->where( array('address_id' => $add_old_order['address_id'],'member_id' => $member_id) )->find();

        }



        $mb_city_id = 0;

        if(!empty($mb_city_name))

        {

            $mb_city_info = M('lionfish_comshop_area')->where(" name='{$mb_city_name}' ")->find();



            if( !empty($mb_city_info) )

            {

                $mb_city_id = $mb_city_info['id'];

            }



        }



        if($address){





            //get_area_info($id)

            $province_info =  D('Home/Front')->get_area_info($address['province_id']);// M('area')->field('area_name')->where( array('area_id' => $address['province_id']) )->find();

            $city_info = D('Home/Front')->get_area_info($address['city_id']);//M('area')->field('area_name')->where( array('area_id' => $address['city_id']) )->find();

            $country_info = D('Home/Front')->get_area_info($address['country_id']);//M('area')->field('area_name')->where( array('area_id' => $address['country_id']) )->find();



            $address['province_name'] = $province_info['name'];

            $address['city_name'] = $city_info['name'];

            $address['country_name'] = $country_info['name'];

        }else{

            $address = array();

        }



        $seller_goodss = array();

        $show_voucher = 0;



        //判断是否仅快递的配送方式  is_only_express



        $is_open_only_express = D('Home/Front')->get_config_by_name('is_open_only_express');



        $is_only_express = 0;



        if( !empty($is_open_only_express) && $is_open_only_express == 1)

        {

            $is_only_express = 1;

        }



        foreach($goods as $key => $val)

        {

            //$goods_store_field =  M('goods')->field('store_id')->where( array('goods_id' => $val['goods_id']) )->find();

            if( $is_only_express == 1 && $val['is_only_express'] == 0 )

            {

                $is_only_express = 0;

            }





            $supply_id = D('Home/Front')->get_goods_supply_id($val['goods_id']);

            if($supply_id > 0)

            {

                $supply_info = D('Home/Front')->get_supply_info($supply_id);



                if($supply_info['type'] ==0)

                {

                    $supply_id = 0;

                }

            }

            $seller_goodss[ $supply_id ]['goods'][$key] = $val;

        }



        $quan_model = D('Home/Voucher');

        $pin_model = D('Home/Pin');





        $voucher_price = 0;

        $reduce_money = 0;

        $cha_reduce_money = 0;



        $is_pin_over = 0;

        $is_moban =  false;







        $is_open_fullreduction = D('Home/Front')->get_config_by_name('is_open_fullreduction');

        $full_money = D('Home/Front')->get_config_by_name('full_money');

        $full_reducemoney = D('Home/Front')->get_config_by_name('full_reducemoney');



        if(empty($full_reducemoney) || $full_reducemoney <= 0)

        {

            $is_open_fullreduction = 0;

        }





        $show_voucher = 0;



        $voucher_can_use =1;//目前都是平台券，

        $man_total_free = 0;

        $store_buy_total_money = 0;



        $pin_id = 0;



        $is_zero_buy = 0;

        $vipcard_save_money = 0;

        $level_save_money = 0;





        //计算优惠券

        foreach($seller_goodss as $store_id => $val)

        {



            $seller_voucher_list = array();

            $seller_total_fee = 0;

            $total_trans_free = 0;

            $is_no_quan = false;





            $total_weight = 0;

            $total_quantity = 0;

            $vch_goods_ids =  array();





            foreach($val['goods'] as $kk =>$d_goods)

            {

                if($d_goods['is_take_vipcard'] == 1)

                {

                    $vipcard_save_money += $d_goods['total'] - $d_goods['card_total'];

                }else if( $d_goods['is_mb_level_buy'] == 1 && $is_member_level_buy == 1)

                {

                    $level_save_money += $d_goods['total'] - $d_goods['level_total'];

                }



                if( $is_vip_card_member == 1 && $d_goods['is_take_vipcard'] == 1 )

                {

                    $seller_total_fee += $d_goods['card_total'];

                    if( $store_id == 0 )

                    {

                        $store_buy_total_money += $d_goods['card_total'];

                    }

                    if( $d_goods['can_man_jian'] == 1)

                    {

                        $man_total_free += $d_goods['card_total'];



                    }

                }

                else if( $d_goods['is_mb_level_buy'] == 1 && $is_member_level_buy == 1 )

                {

                    $seller_total_fee += $d_goods['level_total'];

                    if( $store_id == 0 )

                    {

                        $store_buy_total_money += $d_goods['level_total'];

                    }

                    if( $d_goods['can_man_jian'] == 1)

                    {

                        $man_total_free += $d_goods['level_total'];

                    }

                }

                else

                {

                    $seller_total_fee += $d_goods['total'];

                    if( $store_id == 0 )

                    {

                        $store_buy_total_money += $d_goods['total'];

                    }

                    if( $d_goods['can_man_jian'] == 1)

                    {

                        $man_total_free += $d_goods['total'];



                    }

                }





                if($buy_type == 'pintuan' && $d_goods['pin_id'] > 0)

                {

                    $is_pin_over = $pin_model->getNowPinState($d_goods['pin_id']);

                    if( $is_pin_over == 2 )

                    {

                        $is_zero_buy = $pin_model->check_goods_iszero_opentuan( $d_goods['goods_id'] );

                    }

                }else if($buy_type == 'pintuan' && $d_goods['pin_id'] == 0)

                {

                    $is_zero_buy = $pin_model->check_goods_iszero_opentuan( $d_goods['goods_id'] );

                }



                $tp_goods_info = M('lionfish_comshop_goods')->field('type')->where( array('id' => $d_goods['goods_id']) )->find();



                $vch_goods_ids[$d_goods['goods_id']] = $d_goods['total'];

                //$is_no_quan = true;



                if($tp_goods_info['type'] == 'integral')

                {

                    $is_no_quan = true;

                }



                if($d_goods['shipping']==0)

                {

                    $is_moban = true;

                    $total_weight += $d_goods['weight']*$d_goods['quantity'];

                    $total_quantity += $d_goods['quantity'];

                }



                $d_goods[$kk]['trans_free'] = 0;

                /**

                if($d_goods['shipping']==1)

                {

                //统一运费

                $d_goods[$kk]['trans_free'] = $d_goods['goods_freight'];

                }else {

                //运费模板

                if(!empty($address))

                {

                $trans_free = load_model_class('transport')->calc_transport($d_goods['transport_id'], $d_goods['quantity'],$d_goods['quantity']*$d_goods['weight'], $address['city_id'] );

                }else{

                $trans_free = 0;

                }

                $d_goods[$kk]['trans_free'] = $trans_free;

                }

                 **/



                $total_trans_free  += $d_goods[$kk]['trans_free'];

                $val['goods'][$kk] = $d_goods;



            }



            $chose_vouche = array();









            if(!$is_no_quan)

            {



                $vouche_list = $quan_model->get_user_canpay_voucher($member_id,$store_id,$seller_total_fee,'',$buy_type,$vch_goods_ids);





                if(!empty($vouche_list) && empty($use_quan_arr) ) {

                    if($voucher_can_use == 1)

                    {

                        $voucher_can_use++;



                        $show_voucher = 1;

                        reset($vouche_list);

                        $chose_vouche = current($vouche_list);

                        $voucher_price += $chose_vouche['credit'];



                        $seller_total_fee = round( $seller_total_fee - $chose_vouche['credit'], 2);

                    }



                }else if( !empty($vouche_list) &&  !empty($use_quan_arr) )

                {



                    foreach($vouche_list as $tmp_voucher)

                    {

                        if($tmp_voucher['id'] == $use_quan_arr[$store_id])

                        {

                            $show_voucher = 1;

                            $chose_vouche = $tmp_voucher;

                            $seller_total_fee = round( $seller_total_fee - $chose_vouche['credit'], 2);

                            $voucher_price += $chose_vouche['credit'];

                            break;

                        }

                    }

                }



            }



            $val['chose_vouche'] = $chose_vouche;

            $val['show_voucher'] = $show_voucher;



            $val['voucher_list'] = $vouche_list;

            $val['total'] = $seller_total_fee;



            if($val['total'] < 0)

            {

                $val['total'] = 0;

            }



            $val['trans_free'] = $total_trans_free;



            $val['reduce_money'] = 0;



            //pindan （拼团商品单独购买）   pintuan （拼团）



            if($buy_type == 'pindan' || $buy_type == 'pintuan' || $buy_type == 'integral')

            {

                $is_open_fullreduction = 0;

            }





            //man_total_free

            if($is_open_fullreduction == 1 && $man_total_free >= $full_money )

            {

                $val['reduce_money'] = $full_reducemoney;

                $reduce_money = $full_reducemoney;

            }else if($is_open_fullreduction == 1 && $man_total_free < $full_money)

            {

                $cha_reduce_money = $full_money - $man_total_free;

            }











            $val['total_weight'] = $total_weight;

            $val['total_quantity'] = $total_quantity;



            $s_logo = D('Home/Front')->get_config_by_name('shoplogo');

            $shoname = D('Home/Front')->get_config_by_name('shoname');

            if( !empty($s_logo) )

            {

                $s_logo = tomedia( $s_logo );

            }



            $store_info = array('s_id' => $store_id,'s_true_name' => $shoname,'s_logo' => $s_logo );



            if( !empty($store_id) && $store_id > 0 )

            {

                $supply_info = D('Home/Front')->get_supply_info($store_id);

                //logo

                if( !empty($supply_info) )

                {

                    $store_info['s_true_name'] = $supply_info['shopname'];

                    $store_info['s_logo'] = tomedia( $supply_info['logo'] );

                }

            }



            $val['store_info'] = $store_info;



            $seller_goodss[$store_id] = $val;

        }



        $trans_free_toal = 0;//运费



        //delivery_type_ziti  delivery_type_express    delivery_type_tuanz  delivery_tuanz_money



        $delivery_type_ziti = D('Home/Front')->get_config_by_name('delivery_type_ziti');

        $delivery_type_express = D('Home/Front')->get_config_by_name('delivery_type_express');

        $delivery_type_tuanz = D('Home/Front')->get_config_by_name('delivery_type_tuanz');

        $delivery_tuanz_money = D('Home/Front')->get_config_by_name('delivery_tuanz_money');

        //---

        $man_free_tuanzshipping = D('Home/Front')->get_config_by_name('man_free_tuanzshipping');

        $man_free_shipping = D('Home/Front')->get_config_by_name('man_free_shipping');



        $delivery_express_name = D('Home/Front')->get_config_by_name('delivery_express_name');





        if( empty($man_free_tuanzshipping) || $buy_type == 'integral')

        {

            $man_free_tuanzshipping = 0;

        }



        if( empty($man_free_shipping) || $buy_type == 'integral' )

        {

            $man_free_shipping = 0;

        }



        //if( $buy_type == 'dan' )

        if( $buy_type == 'dan'  || $buy_type == 'soitaire' || ($pintuan_model_buy == 1 && $buy_type != 'dan' && $buy_type != 'integral'  ) )

        {

            //...判断团长是否开启自定义的情况 store_buy_total_money

            $community_info_modify = M('lionfish_community_head')->field('is_modify_shipping_method,is_modify_shipping_fare,shipping_fare')->where( array('id' => $community_id) )->find();





            if( !empty($community_info_modify['is_modify_shipping_method']) && $community_info_modify['is_modify_shipping_method'] == 1  )

            {

                //开启配送

                $delivery_type_tuanz = 1;



                if( !empty($community_info_modify['is_modify_shipping_fare']) && $community_info_modify['is_modify_shipping_fare'] == 1 && $community_info_modify['shipping_fare'] > 0 )

                {

                    $delivery_tuanz_money = $community_info_modify['shipping_fare'];

                }





            }else if( !empty($community_info_modify['is_modify_shipping_method']) && $community_info_modify['is_modify_shipping_method'] == 2 )

            {

                //关闭配送

                $delivery_type_tuanz = 0;

            }

        }else if( $buy_type == 'pindan' || $buy_type == 'pintuan' ){

            if($pintuan_model_buy == 0)

            {

                $delivery_type_tuanz = 0;

                $delivery_type_express = 1;

                $delivery_type_ziti = 2;

            }

        }else if( $buy_type == 'integral' )

        {

            $delivery_type_tuanz = 0;

            $delivery_type_express = 1;

            $delivery_type_ziti = 2;

        }







        $is_man_delivery_tuanz_fare = 0;//是否达到满xx减团长配送费

        $fare_man_delivery_tuanz_fare_money = 0;//达到满xx减团长配送费， 减了多少钱



        if( ( $buy_type == 'dan' || $buy_type == 'soitaire' ) && !empty($man_free_tuanzshipping) && $man_free_tuanzshipping > 0 && $man_free_tuanzshipping <= $store_buy_total_money )

        {

            //$delivery_tuanz_money = 0;

            $is_man_delivery_tuanz_fare = 1;

            $fare_man_delivery_tuanz_fare_money = $delivery_tuanz_money;

        }



        $is_man_shipping_fare = 0;//是否达到满xx减运费

        $fare_man_shipping_fare_money = 0;//达到满xx减运费，司机减了多少运费



        //----开始计算运费 //dispatchtype

        if($delivery_type_express == 1)

        {

            //ims_

            if($mb_city_id > 0){



                $shipping_default = M('lionfish_comshop_shipping')->where(array('enabled' => 1))->order('isdefault desc,id desc')->find();



                foreach($seller_goodss as $store_id => $val)

                {



                    $store_shipping_fare = 0;

                    if($is_moban)

                    {

                        $store_shipping_fare = D('Home/Transport')->calc_transport($shipping_default['id'], $val['total_quantity'],$val['total_weight'], $mb_city_id );



                    }





                    $val['store_shipping_fare'] = $store_shipping_fare;



                    $trans_free_toal += $store_shipping_fare;



                    foreach($val['goods'] as $kk =>$d_goods)

                    {

                        if($d_goods['shipping']==1)

                        {

                            //统一运费

                            $trans_free_toal += $d_goods['goods_freight'];

                        }

                    }

                }



            }else if(!empty($address) && $mb_city_id == 0)

            {



                $shipping_default = M('lionfish_comshop_shipping')->where( array('enabled' => 1) )->order('isdefault desc,id desc')->find();



                foreach($seller_goodss as $store_id => $val)

                {

                    $store_shipping_fare = 0;

                    if($is_moban)

                    {

                        $store_shipping_fare = D('Home/Transport')->calc_transport($shipping_default['id'], $val['total_quantity'],$val['total_weight'], $address['city_id'] );



                    }

                    $val['store_shipping_fare'] = $store_shipping_fare;



                    $trans_free_toal += $store_shipping_fare;



                    foreach($val['goods'] as $kk =>$d_goods)

                    {

                        if($d_goods['shipping']==1)

                        {

                            //统一运费

                            $trans_free_toal += $d_goods['goods_freight'];

                            $val['store_shipping_fare'] += $d_goods['goods_freight'];

                        }

                    }

                }



            }else{

                $trans_free_toal = 0;

            }



            //



            if( !empty($man_free_shipping) && $man_free_shipping > 0 && $man_free_shipping <= $store_buy_total_money )

            {



                $fare_man_shipping_fare_money = $trans_free_toal;

                $is_man_shipping_fare = 1;

                //$trans_free_toal = 0;

            }

        }

        //---结束结算运费



        if( empty($delivery_type_ziti) )

        {

            $delivery_type_ziti = 1;//开启

        }

        if( empty($delivery_type_express) )

        {

            $delivery_type_express = 2;

        }

        if( empty($delivery_type_tuanz) )

        {

            $delivery_type_tuanz = 2;

        }



        //is_only_express

        if( $is_only_express == 1 )

        {

            $delivery_type_ziti = 2;

            $delivery_type_express = 1;

            $delivery_type_tuanz = 2;

        }







        $total_free = 0;

        $is_ziti = 2;



        $pick_up_time = "";

        $pick_up_type = -1;

        $pick_up_weekday = '';

        $today_time = time();



        $arr = array('天','一','二','三','四','五','六');



        $pick_up_arr = array();

        foreach($goods as $key => $good)

        {

            //暂时关闭





            //ims_lionfish_comshop_goods

            //ims_ lionfish_comshop_good_common


            $tp_goods_info = M('lionfish_comshop_goods')->field('pick_start_time,pick_end_time')->where( array('id' => $good['goods_id']) )->find();

            $goods_info = M('lionfish_comshop_good_common')->field('pick_up_type,pick_up_modify')->where( array('goods_id' => $good['goods_id']) )->find();



            if($pick_up_type == -1 || $goods_info['pick_up_type'] > $pick_up_type)

            {

                $pick_up_type = $goods_info['pick_up_type'];



//			if($pick_up_type == 0)
//
//			{
//
//				$pick_up_time = date('m-d', $today_time);
//
//				$pick_up_weekday = '周'.$arr[date('w',$today_time)];
//
//			}else if( $pick_up_type == 1 ){
//
//				$pick_up_time = date('m-d', $today_time+86400);
//
//				$pick_up_weekday = '周'.$arr[date('w',$today_time+86400)];
//
//			}else if( $pick_up_type == 2 )
//
//			{
//
//				$pick_up_time = date('m-d', $today_time+86400*2);
//
//				$pick_up_weekday = '周'.$arr[date('w',$today_time+86400*2)];
//
//			}else if($pick_up_type == 3)
//
//			{
//
//				$pick_up_time = $goods_info['pick_up_modify'];
//
//			}
                if($pick_up_type == 0)

                {
                    if(!empty($tp_goods_info['pick_start_time']) || !empty($tp_goods_info['pick_end_time'])){
                        if(date('H:s')>$tp_goods_info['pick_start_time'] && date('H:s')<$tp_goods_info['pick_end_time'])
                        {
                            $pick_up_time = date('m-d', $today_time);
//
                            $pick_up_weekday = '周'.$arr[date('w',$today_time)];
                            //       $pick_up_modify = date('Y-m-d', $today_time);
                        }else{
                            $pick_up_time = date('m-d', $today_time+86400);
//
                            $pick_up_weekday = '周'.$arr[date('w',$today_time+86400)];
                            //      $pick_up_modify = date('Y-m-d', $today_time+86400);
                        }
                    }else{
                        $pick_up_time = date('m-d', $today_time);
//
                        $pick_up_weekday = '周'.$arr[date('w',$today_time)];

                    }



                }else if( $pick_up_type == 1 ){
                    if(!empty($tp_goods_info['pick_start_time']) || !empty($tp_goods_info['pick_end_time'])) {
                        if (date('H:s') > $tp_goods_info['pick_start_time'] && date('H:s') < $tp_goods_info['pick_end_time']) {
                            //   $pick_up_modify = date('Y-m-d', $today_time + 86400);
                            $pick_up_time = date('m-d', $today_time+86400);
                            $pick_up_weekday = '周'.$arr[date('w',$today_time+86400)];
                        } else {
                            $pick_up_time = date('m-d', $today_time+86400*2);
                            //    $pick_up_modify= date('Y-m-d', $today_time + 86400 * 2);
                            $pick_up_weekday = '周'.$arr[date('w',$today_time+86400*2)];
                        }

//

                    }else{
                        // $pick_up_modify = date('Y-m-d', $today_time + 86400);
                        $pick_up_time = date('m-d', $today_time+86400);
                        $pick_up_weekday = '周'.$arr[date('w',$today_time+86400)];
                    }
                    //$goods['pick_up_modify'] = date('Y-m-d', $today_time+86400);

                }else if( $pick_up_type== 2 )

                {
                    $pick_up_time = date('m-d', $today_time+86400*2);
//
                    $pick_up_weekday = '周'.$arr[date('w',$today_time+86400*2)];
                    //      $pick_up_modify = date('Y-m-d', $today_time+86400*2);

                }
            }



            /**

            if($goods_info['pick_just'] >= 1)

            {

            $pick_up = $goods_info['pick_up'];

            $is_ziti = $goods_info['pick_just'];

            }

             **/





            //$trans_free_toal += $good['goods_freight'];

            $goods[$key]['trans_free'] = $good['goods_freight'];



            /**

            if($good['shipping']==1)

            {

            //统一运费

            $trans_free_toal += $good['goods_freight'];

            $goods[$key]['trans_free'] = $good['goods_freight'];

            }else {

            //运费模板

            if(!empty($address))

            {

            $trans_free =   load_model_class('transport')->calc_transport($good['transport_id'], $good['quantity'], $good['quantity']*$good['weight'], $address['city_id'] );

            }else{

            $trans_free = 0;

            }



            $goods[$key]['trans_free'] = $trans_free;

            $trans_free_toal +=$trans_free;



            }

             **/



            if( $is_vip_card_member == 1 && $good['is_take_vipcard'] == 1 )

            {

                $total_free += $good['card_total'];

            }

            else if( $good['is_mb_level_buy'] == 1  && $is_member_level_buy == 1 )

            {

                $total_free += $good['level_total'];

            }

            else

            {

                $total_free += $good['total'];

            }



        }



        //暂时关闭自提代码

        /**

        if(!empty($pick_up))

        {

        $pick_up = unserialize($pick_up);

        $pick_up_ids = implode(',',$pick_up);

        $pick_up_arr = M('pick_up')->where( array('id'=>array('in',$pick_up_ids)) )->select();

        }

         **/





        $pick_up_name = '';

        $pick_up_mobile = '';

        $tuan_send_address = '';



        $tuan_send_address_info = array();



        $shop_limit_buy_distance = D('Home/Front')->get_config_by_name('shop_limit_buy_distance');









        if($is_ziti >= 1)

        {

            //寻找上一个订单的自提电话 自提姓名



            $last_order_info = M('lionfish_comshop_order')->where( array('member_id' => $member_id,'delivery' => 'pickup') )->order('order_id desc')->find();

            if(!empty($last_order_info))

            {

                $pick_up_name = $last_order_info['shipping_name'];

                $pick_up_mobile = $last_order_info['telephone'];

            }



            $last_tuanz_send_order_info = M('lionfish_comshop_order')->field('tuan_send_address,address_id')->where( array('member_id' =>$member_id,'delivery' => 'tuanz_send' ) )->order('order_id desc')->find();



            if(!empty($last_tuanz_send_order_info))

            {

                $tuan_send_address = $last_tuanz_send_order_info['tuan_send_address'];



                if( !empty($last_tuanz_send_order_info['address_id']) )

                {



                    if($shop_limit_buy_distance == 1)

                    {

                        $tuan_send_address_info = M('lionfish_comshop_address')->where( "address_id=".$last_tuanz_send_order_info['address_id']." and lon_lat != '' " )->find();



                    }else{



                        $tuan_send_address_info = M('lionfish_comshop_address')->where( "address_id=".$last_tuanz_send_order_info['address_id'] )->find();



                    }



                    if( !empty($tuan_send_address) && !empty($tuan_send_address_info['lon_lat']) )

                    {

                        $province_info =  D('Home/Front')->get_area_info($tuan_send_address_info['province_id']);// M('area')->field('area_name')->where( array('area_id' => $address['province_id']) )->find();

                        $city_info = D('Home/Front')->get_area_info($tuan_send_address_info['city_id']);//M('area')->field('area_name')->where( array('area_id' => $address['city_id']) )->find();

                        $country_info = D('Home/Front')->get_area_info($tuan_send_address_info['country_id']);//M('area')->field('area_name')->where( array('area_id' => $address['country_id']) )->find();



                        $tuan_send_address_info['province_name'] = $province_info['name'];

                        $tuan_send_address_info['city_name'] = $city_info['name'];

                        $tuan_send_address_info['country_name'] = $country_info['name'];



                        $tuan_send_address = $tuan_send_address_info['address'];

                    }else{

                        //todo...

                        $tuan_send_address = '';

                    }



                }else{

                    $tuan_send_address = '';

                }



            }

        }

        /**

         **/

        /**

        tuan_region

        store_buy_total_money



         **/



        //open_score_buy_score $shop_limit_buy_distance = load_model_class('front')->get_config_by_name('shop_limit_buy_distance');



        $open_score_buy_score =  D('Home/Front')->get_config_by_name('open_score_buy_score');



        if( empty($open_score_buy_score) || $buy_type == 'integral' )

        {

            $open_score_buy_score = 0;

        }



        $score_forbuy_money_maxbi = D('Home/Front')->get_config_by_name('score_forbuy_money_maxbi');



        if( empty($score_forbuy_money_maxbi) )

        {

            $score_forbuy_money_maxbi = 100;

        }



        $score_for_money = 0;

        $bue_use_score = 0;



        if( $open_score_buy_score == 1 )

        {

            if( $member_info['score'] > 0 )

            {

                //计算能兑换多少钱

                $score_forbuy_money = D('Home/Front')->get_config_by_name('score_forbuy_money');

                //只有兑换比例大于0才能允许兑换

                if( !empty($score_forbuy_money) && $score_forbuy_money >0 )

                {

                    $score_for_money = round( $member_info['score'] / $score_forbuy_money ,2);



                    if( $store_buy_total_money < $score_for_money )

                    {

                        $score_for_money = $store_buy_total_money;

                        $bue_use_score = $store_buy_total_money * $score_forbuy_money;

                    }



                    $max_store_buy_total_money = round( ($score_forbuy_money_maxbi * $store_buy_total_money) /100,2);



                    if($score_for_money > $max_store_buy_total_money)

                    {

                        $score_for_money = $max_store_buy_total_money;



                        $bue_use_score = $max_store_buy_total_money * $score_forbuy_money;

                    }else if($score_for_money <= $max_store_buy_total_money){



                        $bue_use_score = $score_for_money;

                    }

                }

            }

        }

        //score_forbuy_money score



        $delivery_ziti_name = D('Home/Front')->get_config_by_name('delivery_ziti_name');

        $delivery_tuanzshipping_name = D('Home/Front')->get_config_by_name('delivery_tuanzshipping_name');

        $delivery_diy_sort = D('Home/Front')->get_config_by_name('delivery_diy_sort');



        if(empty($delivery_diy_sort) || !isset($delivery_diy_sort)) $delivery_diy_sort = '0,1,2';





        if( empty($tuan_send_address_info) )

        {

            $tuan_send_address_info = array();

        }



        $need_data = array();

        $need_data['code'] = 1;



        $need_data['open_score_buy_score'] = $open_score_buy_score;//1开启积分抵扣

        $need_data['score'] = $member_info['score'];//会员持有的积分

        $need_data['score_for_money'] = sprintf('%.2f', $score_for_money);//会员能抵扣的金额

        $need_data['bue_use_score'] = sprintf('%.2f', $bue_use_score);//会员能抵扣的积分数





        $need_data['delivery_type_ziti'] = $delivery_type_ziti;

        $need_data['delivery_type_express'] = $delivery_type_express;

        $need_data['delivery_type_tuanz'] = $delivery_type_tuanz;

        $need_data['delivery_express_name'] = $delivery_express_name;

        $need_data['delivery_ziti_name'] = $delivery_ziti_name;

        $need_data['delivery_tuanzshipping_name'] = $delivery_tuanzshipping_name;

        $need_data['delivery_diy_sort'] = $delivery_diy_sort;





        $need_data['delivery_tuanz_money'] = $delivery_tuanz_money;

        $need_data['man_free_tuanzshipping'] = empty($man_free_tuanzshipping) ? 0 : $man_free_tuanzshipping;//团长配送，满多少免配送费，0或者为空表示不减免

        $need_data['man_free_shipping'] = empty($man_free_shipping) ? 0 : $man_free_shipping;//快递配送，满多少免配送费，0或者为空表示不减免



        $need_data['address'] = $address;





        $need_data['pick_up_time'] = $pick_up_time;

        $need_data['pick_up_type'] = $pick_up_type;

        $need_data['pick_up_weekday'] = $pick_up_weekday;



        $need_data['is_pin_over'] = $is_pin_over;

        $need_data['is_integer'] = 0;//$is_no_quan ? 1: 0;

        $need_data['pick_up_arr'] = $pick_up_arr;

        $need_data['is_ziti'] = 2;



        $need_data['ziti_name'] = $pick_up_name;

        $need_data['ziti_mobile'] = $pick_up_mobile;

        $need_data['tuan_send_address'] = $tuan_send_address;

        $need_data['tuan_send_address_info'] = $tuan_send_address_info;

        $need_data['seller_goodss'] = $seller_goodss;

        $need_data['show_voucher'] = $show_voucher;



        $need_data['buy_type'] = $buy_type;

        $need_data['address'] = $address;

        $need_data['trans_free_toal'] = $trans_free_toal;



        $need_data['is_limit_distance_buy'] = 0;

        $need_data['limit_distance'] = 100;//km



        $need_data['is_member_level_buy'] = $is_member_level_buy;//km

        $need_data['level_save_money'] = $level_save_money;//km



        $need_data['is_vip_card_member'] = $is_vip_card_member;//km

        $need_data['vipcard_save_money'] = $vipcard_save_money;//km

        $need_data['is_open_vipcard_buy'] = $is_open_vipcard_buy;//km







        if( !empty($shop_limit_buy_distance) && $shop_limit_buy_distance ==1 )

        {

            $latitude = 0;

            $longitude = 0;



            if( !empty($tuan_send_address_info) && !empty($tuan_send_address_info['lon_lat']) )

            {

                //lon_lat

                $lon_lat_arr = explode(',', $tuan_send_address_info['lon_lat']);

                $longitude = $lon_lat_arr[0];

                $latitude = $lon_lat_arr[1];

            }





            if( isset($gpc['latitude']) && !empty($gpc['latitude']) )

            {

                $latitude = $gpc['latitude'];

            }

            if( isset($gpc['longitude']) && !empty($gpc['longitude']) )

            {

                $longitude = $gpc['longitude'];

            }



            if( !empty($latitude) && !empty($longitude) )

            {

                $shop_buy_distance = D('Home/Front')->get_config_by_name('shop_buy_distance');



                $shop_buy_distance = $shop_buy_distance * 1000;





                $community_info = M('lionfish_community_head')->field('lon,lat')->where( array('id' => $community_id ) )->find();





                $distince = D('Seller/Communityhead')->GetDistance($longitude,$latitude,$community_info['lon'],$community_info['lat']);



                $need_data['current_distance'] = $distince;

                $need_data['shop_buy_distance'] = $shop_buy_distance;

                if($distince > $shop_buy_distance )

                {

                    $need_data['is_limit_distance_buy'] = 1;

                    $need_data['limit_distance'] = $distince/1000;

                }

            }

        }







        $need_data['reduce_money'] = $reduce_money;

        $need_data['is_open_fullreduction'] = $is_open_fullreduction;

        $need_data['cha_reduce_money'] = $cha_reduce_money;





        $need_data['is_man_delivery_tuanz_fare'] = $is_man_delivery_tuanz_fare; //是否达到满xx减团长配送费

        $need_data['fare_man_delivery_tuanz_fare_money'] = $fare_man_delivery_tuanz_fare_money;	//达到满xx减团长配送费， 减了多少钱

        $need_data['is_man_shipping_fare'] = $is_man_shipping_fare; //是否达到满xx减运费

        $need_data['fare_man_shipping_fare_money'] = $fare_man_shipping_fare_money;	//达到满xx减运费，司机减了多少运费





        $dispatching = isset($gpc['dispatching']) ? $gpc['dispatching']:'pickup';

        //is_ziti == 2

        if($dispatching == 'express')

        {

            $need_data['total_free'] = $total_free + $trans_free_toal - $voucher_price -$reduce_money;

        }else{

            $need_data['total_free'] = $total_free  - $voucher_price -$reduce_money;

        }

        if($is_ziti == 2)

        {

            $need_data['total_free'] = $total_free  - $voucher_price -$reduce_money;

        }



        //积分兑换 不算总金额，但是算总积分

        if( $buy_type == 'integral' )

        {

            $need_data['total_free'] = $trans_free_toal;

            $need_data['total_integral'] = $total_free;

        }



        if($need_data['total_free'] < 0)

        {

            $need_data['total_free'] = 0;

        }



        //判断是否可以余额支付



        //暂时关闭 会员余额功能

        /**

        $is_yue_open_info =	M('config')->where( array('name' => 'is_yue_open') )->find();

        $is_yue_open =  $is_yue_open_info['value'];

         **/



        $is_yue_open = 0;



        $is_yue_open = D('Home/Front')->get_config_by_name('is_open_yue_pay');

        if( empty($is_yue_open) )

        {

            $is_yue_open = 0;

        }





        $need_data['is_yue_open'] = $is_yue_open;



        $need_data['can_yupay'] = 0;



        //暂时关闭 会员余额功能



        if($is_yue_open == 1 && $need_data['total_free'] >=0 && $member_info['account_money'] >= $need_data['total_free'])

        {

            $need_data['can_yupay'] = 1;

        }



        //前端隐藏 团长信息

        $index_hide_headdetail_address = D('Home/Front')->get_config_by_name('index_hide_headdetail_address');



        if( empty($index_hide_headdetail_address) )

        {

            $index_hide_headdetail_address = 0;

        }

        $need_data['index_hide_headdetail_address'] = $index_hide_headdetail_address;





        //订单留言

        $is_open_order_message = D('Home/Front')->get_config_by_name('is_open_order_message');



        $need_data['yu_money'] = $member_info['account_money'];

        $need_data['goods'] = $goods;

        $need_data['is_open_order_message'] = $is_open_order_message;



        $need_data['is_zero_opentuan'] = 0;

        //拼团特殊情况0元开团

        if( $buy_type == 'pintuan' && $is_zero_buy == 1 )

        {

            //$need_data['total_free'] = 0;

            //$need_data['trans_free_toal'] = 0;

            //$need_data['is_zero_opentuan'] = 1;

        }





        //订阅消息begin

        //  weprogram_use_templatetype

        $weprogram_use_templatetype = D('Home/Front')->get_config_by_name('weprogram_use_templatetype');



        $is_need_subscript = 0;

        $need_subscript_template = array();



        if( !empty($weprogram_use_templatetype) &&  $weprogram_use_templatetype == 1 )

        {

            //'pay_order','send_order','hexiao_success','apply_community','open_tuan','take_tuan','pin_tuansuccess','apply_tixian'

            //$member_id

            if( $buy_type == 'pintuan' )

            {

                //pin_tuansuccess

                //send_order  parameter[weprogram_subtemplate_pin_tuansuccess]

                //hexiao_success



                $pin_tuansuccess_info = M('lionfish_comshop_subscribe')->where( array('member_id' => $member_id, 'type' => 'pin_tuansuccess' ) )->find();



                if( empty($pin_tuansuccess_info) )

                {

                    $weprogram_subtemplate_pin_tuansuccess = D('Home/Front')->get_config_by_name('weprogram_subtemplate_pin_tuansuccess');



                    if( !empty($weprogram_subtemplate_pin_tuansuccess) )

                    {

                        $need_subscript_template['pin_tuansuccess'] = $weprogram_subtemplate_pin_tuansuccess;

                    }

                }



            }else{

                //pay_order

                $pay_order_info = M('lionfish_comshop_subscribe')->where( array('member_id' => $member_id , 'type' => 'pay_order') )->find();



                if( empty($pay_order_info) )

                {

                    $weprogram_subtemplate_pay_order = D('Home/Front')->get_config_by_name('weprogram_subtemplate_pay_order');


                    if( !empty($weprogram_subtemplate_pay_order) )

                    {

                        $need_subscript_template['pay_order'] = $weprogram_subtemplate_pay_order;

                    }

                }

            }

            //send_order



            $send_order_info = M('lionfish_comshop_subscribe')->where( array('member_id' => $member_id, 'type' => 'send_order' ) )->find();



            if( empty($send_order_info) )

            {

                $weprogram_subtemplate_send_order = D('Home/Front')->get_config_by_name('weprogram_subtemplate_send_order');



                if( !empty($weprogram_subtemplate_send_order) )

                {

                    $need_subscript_template['send_order'] = $weprogram_subtemplate_send_order;

                }

            }

            //hexiao_success

            $hexiao_success_info = M('lionfish_comshop_subscribe')->where( array('member_id' => $member_id, 'type' => 'hexiao_success' ) )->find();





            if( empty($hexiao_success_info) )

            {

                $weprogram_subtemplate_hexiao_success = D('Home/Front')->get_config_by_name('weprogram_subtemplate_hexiao_success');
					


                if( !empty($weprogram_subtemplate_hexiao_success) )

                {

                    $need_subscript_template['hexiao_success'] = $weprogram_subtemplate_hexiao_success;

                }

            }



            if( !empty($need_subscript_template) )

            {

                $is_need_subscript = 1;

            }

        }

        //订阅消息end
        


        $need_data['is_need_subscript'] = $is_need_subscript;

        $need_data['need_subscript_template'] = $need_subscript_template;





        echo json_encode($need_data);

        die();

    }

    public function checkout()
    { 
    	
    		
        $gpc = I('request.');

        $buy_type = isset($gpc['buy_type']) ? $gpc['buy_type'] : 'dan';

        $pintuan_model_buy = D('Home/Front')->get_config_by_name('pintuan_model_buy');

        if( empty($pintuan_model_buy) || $pintuan_model_buy ==0 )
        {

            $pintuan_model_buy = 0;

        }

        $mb_city_name = isset($gpc['mb_city_name']) ? $gpc['mb_city_name'] : '';

        if($buy_type == 'undefined')
        {

            $buy_type = 'dan';

        }

        $community_id = $gpc['community_id'];

        $token = $gpc['token'];

        $voucher_id = isset($gpc['voucher_id']) ? $gpc['voucher_id'] : 0;

        $soli_id = isset($gpc['soli_id']) ? intval($gpc['soli_id']) : '';

        $use_quan_str = isset($gpc['use_quan_str']) ? $gpc['use_quan_str'] : '';

        $use_quan_arr = array();

        if($use_quan_str != '')
        {

            $use_quan_arr_tmp = explode('@',$use_quan_str );

            foreach($use_quan_arr_tmp as $val)

            {

                $tmp_arr = explode('_', $val);

                $use_quan_arr[$tmp_arr[0]] = $tmp_arr[1];

            }

        }

        $weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

        $member_id = $weprogram_token['member_id'];

        $is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');

        $is_open_vipcard_buy = !empty($is_open_vipcard_buy) && $is_open_vipcard_buy ==1 ? 1:0;

        $is_vip_card_member = 0;

        $is_member_level_buy = 0;

        if( $member_id > 0 )
        {

            $member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id ) )->find();

            if( !empty($is_open_vipcard_buy) && $is_open_vipcard_buy == 1 )
            {

                $now_time = time();

                if( $member_info['card_id'] >0 && $member_info['card_end_time'] > $now_time )
                {

                    $is_vip_card_member = 1;//还是会员

                }else if( $member_info['card_id'] >0 && $member_info['card_end_time'] < $now_time ){

                    $is_vip_card_member = 2;//已过期

                }

            }

            if($is_vip_card_member != 1 && $member_info['level_id'] >0 )
            {

                $is_member_level_buy = 1;

            }

        }

        if( empty($member_id) )
        {

            //需要登录

            echo json_encode( array('code' =>5) );

            die();

        }

        $cart = D('Home/Car');

        if ((!$cart->has_goodswecar($buy_type,$token,$community_id) ) ) {

            //购物车中没有商品

            echo json_encode( array('code' =>4) );

            die();

        }

        $member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id) )->find();

        $goods=$cart->get_all_goodswecar($buy_type, $token,1,$community_id,$soli_id);

        $address = M('lionfish_comshop_address')->where( array('member_id' => $member_id) )->order('is_default desc,address_id desc')->find();

        $add_old_order = M('lionfish_comshop_order')->field('address_id')->where(  array('delivery' => 'express','member_id' => $member_id) )->order('order_id desc')->find();

        if( !empty($add_old_order) && $add_old_order['address_id'] > 0)
        {

            $address = M('lionfish_comshop_address')->where( array('address_id' => $add_old_order['address_id'],'member_id' => $member_id) )->find();

        }

        $mb_city_id = 0;

        if(!empty($mb_city_name))
        {

            $mb_city_info = M('lionfish_comshop_area')->where(" name='{$mb_city_name}' ")->find();

            if( !empty($mb_city_info) )
            {

                $mb_city_id = $mb_city_info['id'];

            }

        }

        if($address){

            $province_info =  D('Home/Front')->get_area_info($address['province_id']);// M('area')->field('area_name')->where( array('area_id' => $address['province_id']) )->find();

            $city_info = D('Home/Front')->get_area_info($address['city_id']);//M('area')->field('area_name')->where( array('area_id' => $address['city_id']) )->find();

            $country_info = D('Home/Front')->get_area_info($address['country_id']);//M('area')->field('area_name')->where( array('area_id' => $address['country_id']) )->find();

            $address['province_name'] = $province_info['name'];

            $address['city_name'] = $city_info['name'];

            $address['country_name'] = $country_info['name'];

        }else{

            $address = array();

        }

        $seller_goodss = array();

        $show_voucher = 0;

        //判断是否仅快递的配送方式  is_only_express
        $is_open_only_express = D('Home/Front')->get_config_by_name('is_open_only_express');

        $is_only_express = 0;

        if( !empty($is_open_only_express) && $is_open_only_express == 1)
        {

            $is_only_express = 1;

        }

        $dao_dian_zi_ti=0;
        $pei_song_dao_jia=0;
        $kuai_di=0;
        $song_type_arr=[];
        $tongji=array();
        
        foreach($goods as $key => $val)
        {  
           
            //判断是否是同一运费模板累计数量 和 金额
            $tongji[$val['transport_id']]['num'] +=$val['quantity'];
            $tongji[$val['transport_id']]['price'] +=$val['price']*$val['quantity'];
            $tongji[$val['transport_id']]['weight'] +=$val['weight'];
           
            if(!$song_type_arr){

                $song_type_arr=explode(',',$val['song_type']);
                $son_tp=explode(',',$val['song_type']);

                foreach($son_tp as $v){

                        if(in_array(1,$song_type_arr)){
                            $dao_dian_zi_ti=1;
                        }else{
                            $dao_dian_zi_ti=0;
                        }
                        if(in_array(2,$song_type_arr)){
                            $pei_song_dao_jia=1;
                        }else{
                            $pei_song_dao_jia=0;
                        }
                        if(in_array(3,$song_type_arr)){
                            $kuai_di=1;
                        }else{
                            $kuai_di=0;
                        }
                }
            }else{

                if(count($song_type_arr)>count(explode(',',$val['song_type']))){

                    $son_tp=explode(',',$val['song_type']);

                    foreach($song_type_arr as $v){

                        if(!in_array($v,$son_tp)){

                            if(in_array(1,$song_type_arr)){

                                $dao_dian_zi_ti=1;

                            }else{

                                $dao_dian_zi_ti=0;

                            }
                            if(in_array(2,$song_type_arr)){

                                $pei_song_dao_jia=1;

                            }else{

                                $pei_song_dao_jia=0;

                            }
                            if(in_array(3,$song_type_arr)){

                                $kuai_di=1;

                            }else{

                                $kuai_di=0;
                            }

                        }else{

                            $dao_dian_zi_ti=1;
                            $pei_song_dao_jia=1;
                            $kuai_di=1;
                        }
                    }
                }
                $son_tp=$song_type_arr;

                
                foreach($son_tp as $v){

                        if(in_array(1,$song_type_arr)){
                            $dao_dian_zi_ti=1;

                        }else{
                            $dao_dian_zi_ti=0;
                        }
                        if(in_array(2,$song_type_arr)){
                            $pei_song_dao_jia=1;

                        }else{
                            $pei_song_dao_jia=0;
                        }
                        if(in_array(3,$song_type_arr)){
                            $kuai_di=1;
                        }else{
                            $kuai_di=0;
                        }

                }
            }
  
            if( $is_only_express == 1 && $val['is_only_express'] == 0 )
            {

                $is_only_express = 0;

            }

            $supply_id = D('Home/Front')->get_goods_supply_id($val['goods_id']);

            if($supply_id > 0)
            {
                $supply_info = D('Home/Front')->get_supply_info($supply_id);

                if($supply_info['type'] ==0)
                {

                    $supply_id = 0;

                }

            }

            $seller_goodss[ $supply_id ]['goods'][$key] = $val;

        }
   
        $quan_model = D('Home/Voucher');

        $pin_model = D('Home/Pin');

        $voucher_price = 0;

        $reduce_money = 0;

        $cha_reduce_money = 0;

        $is_pin_over = 0;

        $is_moban =  false;

        $is_open_fullreduction = D('Home/Front')->get_config_by_name('is_open_fullreduction');

        $full_money = D('Home/Front')->get_config_by_name('full_money');

        $full_reducemoney = D('Home/Front')->get_config_by_name('full_reducemoney');

        if(empty($full_reducemoney) || $full_reducemoney <= 0)
        {

            $is_open_fullreduction = 0;

        }

        $show_voucher = 0;

        $voucher_can_use =1;//目前都是平台券，

        $man_total_free = 0;

        $store_buy_total_money = 0;

        $pin_id = 0;

        $is_zero_buy = 0;

        $vipcard_save_money = 0;

        $level_save_money = 0;
       
        //计算优惠券

        foreach($seller_goodss as $store_id => $val)
        {

            $seller_voucher_list = array();

            $seller_total_fee = 0;

            $total_trans_free = 0;

            $is_no_quan = false;

            $total_weight = 0;

            $total_quantity = 0;

            $vch_goods_ids =  array();
            $pick_up_times = date("m-d");
            foreach($val['goods'] as $kk =>$d_goods)
            {

                if($d_goods['is_take_vipcard'] == 1)
                {

                    $vipcard_save_money += $d_goods['total'] - $d_goods['card_total'];

                }else if( $d_goods['is_mb_level_buy'] == 1 && $is_member_level_buy == 1)
                {

                    $level_save_money += $d_goods['total'] - $d_goods['level_total'];

                }

                if( $is_vip_card_member == 1 && $d_goods['is_take_vipcard'] == 1 )
                {

                    $seller_total_fee += $d_goods['card_total'];

                    if( $store_id == 0 )

                    {

                        $store_buy_total_money += $d_goods['card_total'];

                    }

                    if( $d_goods['can_man_jian'] == 1)

                    {

                        $man_total_free += $d_goods['card_total'];



                    }

                }
                else if( $d_goods['is_mb_level_buy'] == 1 && $is_member_level_buy == 1 )
                {

                    $seller_total_fee += $d_goods['level_total'];

                    if( $store_id == 0 )

                    {

                        $store_buy_total_money += $d_goods['level_total'];

                    }

                    if( $d_goods['can_man_jian'] == 1)

                    {

                        $man_total_free += $d_goods['level_total'];

                    }

                }
                else
                {

                    $seller_total_fee += $d_goods['total'];

                    if( $store_id == 0 )

                    {

                        $store_buy_total_money += $d_goods['total'];

                    }

                    if( $d_goods['can_man_jian'] == 1)

                    {

                        $man_total_free += $d_goods['total'];



                    }

                }

                if($buy_type == 'pintuan' && $d_goods['pin_id'] > 0)
                {

                    $is_pin_over = $pin_model->getNowPinState($d_goods['pin_id']);

                    if( $is_pin_over == 2 )

                    {

                        $is_zero_buy = $pin_model->check_goods_iszero_opentuan( $d_goods['goods_id'] );

                    }

                }else if($buy_type == 'pintuan' && $d_goods['pin_id'] == 0)
                {

                    $is_zero_buy = $pin_model->check_goods_iszero_opentuan( $d_goods['goods_id'] );

                }

                $tp_goods_info = M('lionfish_comshop_goods')->field('type')->where( array('id' => $d_goods['goods_id']) )->find();

                $vch_goods_ids[$d_goods['goods_id']] = $d_goods['total'];

                if($tp_goods_info['type'] == 'integral')
                {

                    $is_no_quan = true;

                }

                if($d_goods['shipping']==0)
                {

                    $is_moban = true;

                    $total_weight += $d_goods['weight']*$d_goods['quantity'];

                    $total_quantity += $d_goods['quantity'];

                }

                $d_goods[$kk]['trans_free'] = 0;
		
                $total_trans_free  += $d_goods[$kk]['trans_free'];

                //2020年6月17日 修改规整 超过设置时间往后延迟一天
                $tp_goods_info = M('lionfish_comshop_goods')->field('pick_start_time,pick_end_time')->where( array('id' => $d_goods['goods_id']) )->find();
                $goods_info = M('lionfish_comshop_good_common')->field('pick_up_type,pick_up_modify')->where( array('goods_id' => $d_goods['goods_id']) )->find();
                $pick_up_type = $goods_info['pick_up_type'];
                $dataz=date('H:i');//现在时间
                $d_goods['is_delivery_tips']=0;
                $d_goods['delivery_tips']='';
                $weekarray=array("日","一","二","三","四","五","六");
                if(strtotime($dataz)>strtotime($tp_goods_info['pick_end_time']) && $pick_up_type != 3 && $d_goods['is_only_express'] != 1 && $d_goods['song_type'] != 3){
                    switch ($pick_up_type) {
                        case '0'://当日
                            $next_day = time()+86400;
                            break;
                        case '1'://次日
                            $next_day = time()+86400*2;
                            break;
                        case '2'://隔日
                            $next_day = time()+86400*3;
                            break;
                    }
                    $tomorrow=date("m-d", $next_day);
                    $d_goods['is_delivery_tips']=1;
                    $d_goods['delivery_tips']='预计'.$tomorrow.'（周'.$weekarray[date("w",$next_day)].')';
                    if($pick_up_times<$tomorrow){
                        $pick_up_times =$tomorrow;
                        $pick_up_weekdays = '周'.$weekarray[date('w',$next_day)];
                    }
                }
                $val['goods'][$kk] = $d_goods;

            }
         
            $chose_vouche = array();

            if(!$is_no_quan)
            {

                $vouche_list = $quan_model->get_user_canpay_voucher($member_id,$store_id,$seller_total_fee,'',$buy_type,$vch_goods_ids);

                if(!empty($vouche_list) && empty($use_quan_arr) ) {

                    if($voucher_can_use == 1)
                    {

                        $voucher_can_use++;

                        $show_voucher = 1;

                        reset($vouche_list);

                        $chose_vouche = current($vouche_list);

                        $voucher_price += $chose_vouche['credit'];

                        $seller_total_fee = round( $seller_total_fee - $chose_vouche['credit'], 2);

                    }

                }else if( !empty($vouche_list) &&  !empty($use_quan_arr) )
                {

                    foreach($vouche_list as $tmp_voucher)
                    {

                        if($tmp_voucher['id'] == $use_quan_arr[$store_id])
                        {

                            $show_voucher = 1;

                            $chose_vouche = $tmp_voucher;

                            $seller_total_fee = round( $seller_total_fee - $chose_vouche['credit'], 2);

                            $voucher_price += $chose_vouche['credit'];

                            break;

                        }

                    }

                }

            }

            $val['chose_vouche'] = $chose_vouche;

            $val['show_voucher'] = $show_voucher;

            $val['voucher_list'] = $vouche_list;

            $val['total'] = $seller_total_fee;

            if($val['total'] < 0)
            {

                $val['total'] = 0;

            }

            $val['trans_free'] = $total_trans_free;

            $val['reduce_money'] = 0;

            //pindan （拼团商品单独购买）   pintuan （拼团）
            if($buy_type == 'pindan' || $buy_type == 'pintuan' || $buy_type == 'integral')
            {

                $is_open_fullreduction = 0;

            }

            //man_total_free
            if($is_open_fullreduction == 1 && $man_total_free >= $full_money )
            {

                $val['reduce_money'] = $full_reducemoney;

                $reduce_money = $full_reducemoney;

            }else if($is_open_fullreduction == 1 && $man_total_free < $full_money)
            {

                $cha_reduce_money = $full_money - $man_total_free;

            }

            $val['total_weight'] = $total_weight;

            $val['total_quantity'] = $total_quantity;

            $s_logo = D('Home/Front')->get_config_by_name('shoplogo');

            $shoname = D('Home/Front')->get_config_by_name('shoname');

            if( !empty($s_logo) )
            {

                $s_logo = tomedia( $s_logo );

            }

            $store_info = array('s_id' => $store_id,'s_true_name' => $shoname,'s_logo' => $s_logo );

            if( !empty($store_id) && $store_id > 0 )
            {

                $supply_info = D('Home/Front')->get_supply_info($store_id);

                //logo
                if( !empty($supply_info) )
                {

                    $store_info['s_true_name'] = $supply_info['shopname'];

                    $store_info['s_logo'] = tomedia( $supply_info['logo'] );

                }

            }

            $val['store_info'] = $store_info;

            $seller_goodss[$store_id] = $val;

        }

        $trans_free_toal = 0;//运费

        $delivery_type_ziti = D('Home/Front')->get_config_by_name('delivery_type_ziti');//是否开启自提

        $delivery_type_express = D('Home/Front')->get_config_by_name('delivery_type_express');//是否开启快递配送

        $delivery_type_tuanz = D('Home/Front')->get_config_by_name('delivery_type_tuanz');//是否开启团长配送

        $delivery_tuanz_money = D('Home/Front')->get_config_by_name('delivery_tuanz_money');//团长配送费

        $man_free_tuanzshipping = D('Home/Front')->get_config_by_name('man_free_tuanzshipping');

        $man_free_shipping = D('Home/Front')->get_config_by_name('man_free_shipping');

        $delivery_express_name = D('Home/Front')->get_config_by_name('delivery_express_name');

        if( empty($man_free_tuanzshipping) || $buy_type == 'integral')
        {

            $man_free_tuanzshipping = 0;

        }

        if( empty($man_free_shipping) || $buy_type == 'integral' )
        {

            $man_free_shipping = 0;

        }

        if( $buy_type == 'dan'  || $buy_type == 'soitaire' || ($pintuan_model_buy == 1 && $buy_type != 'dan' && $buy_type != 'integral'  ) )
        {

            //...判断团长是否开启自定义的情况 store_buy_total_money
            $community_info_modify = M('lionfish_community_head')->field('is_modify_shipping_method,is_modify_shipping_fare,shipping_fare')->where( array('id' => $community_id) )->find();

            if( !empty($community_info_modify['is_modify_shipping_method']) && $community_info_modify['is_modify_shipping_method'] == 1 && $pei_song_dao_jia)
            {

                //开启配送

                $delivery_type_tuanz = 1;

                if( !empty($community_info_modify['is_modify_shipping_fare']) && $community_info_modify['is_modify_shipping_fare'] == 1 && $community_info_modify['shipping_fare'] > 0 )
                {

                    $delivery_tuanz_money = $community_info_modify['shipping_fare'];

                }

            }else if( !empty($community_info_modify['is_modify_shipping_method']) && $community_info_modify['is_modify_shipping_method'] == 2 )
            {
                //关闭配送
                $delivery_type_tuanz = 0;
            }

        }else if( $buy_type == 'pindan' || $buy_type == 'pintuan' ){

            if($pintuan_model_buy == 0)
            {

                $delivery_type_tuanz = 0;

                $delivery_type_express = 1;

                $delivery_type_ziti = 2;

            }

        }else if( $buy_type == 'integral' )
        {

            $delivery_type_tuanz = 0;

            $delivery_type_express = 1;

            $delivery_type_ziti = 2;

        }

        $is_man_delivery_tuanz_fare = 0;//是否达到满xx减团长配送费

        $fare_man_delivery_tuanz_fare_money = 0;//达到满xx减团长配送费， 减了多少钱

        if( ( $buy_type == 'dan' || $buy_type == 'soitaire' ) && !empty($man_free_tuanzshipping) && $man_free_tuanzshipping > 0 && $man_free_tuanzshipping <= $store_buy_total_money )
        {

            //$delivery_tuanz_money = 0;

            $is_man_delivery_tuanz_fare = 1;

            $fare_man_delivery_tuanz_fare_money = $delivery_tuanz_money;

        }

        $is_man_shipping_fare = 0;//是否达到满xx减运费

        $fare_man_shipping_fare_money = 0;//达到满xx减运费，司机减了多少运费

        //----开始计算运费 //dispatchtype
        if($delivery_type_express == 1)
        {

            if($mb_city_id > 0){

                foreach($seller_goodss as $store_id => $val)
                {

                    $trans_free_toal= D('Home/Transport')->freight_calculation($val['goods'],$mb_city_id,$tongji);
                   
		 	
                }

            }else if(!empty($address) && $mb_city_id == 0)
            {

                foreach($seller_goodss as $store_id => $val)
                {
                    $trans_free_toal= D('Home/Transport')->freight_calculation($val['goods'],$address['city_id'],$tongji);   

                }
                   
            }else{

                $trans_free_toal = 0;

            }

            if( !empty($man_free_shipping) && $man_free_shipping > 0 && $man_free_shipping <= $store_buy_total_money )
            {
                $fare_man_shipping_fare_money = $trans_free_toal;

                $is_man_shipping_fare = 1;

                //$trans_free_toal = 0;

            }

        }
       
        //---结束结算运费

        if( empty($delivery_type_ziti) && $dao_dian_zi_ti)
        {

            $delivery_type_ziti = 1;//开启

        }
  
        if(!$dao_dian_zi_ti){
            $delivery_type_ziti=0;
        }

        if(!$pei_song_dao_jia){
            $delivery_type_tuanz=0;
        }

        if(!$kuai_di){
            $delivery_type_express = 0;
        }

        if( empty($delivery_type_express) )
        {

            $delivery_type_express = 2;

        }

        if( empty($delivery_type_tuanz) )
        {

            $delivery_type_tuanz = 2;

        }

        //is_only_express
        if( $is_only_express == 1 )
        {

            $delivery_type_ziti = 2;

            $delivery_type_express = 1;

            $delivery_type_tuanz = 2;

        }

        $total_free = 0;

        $is_ziti = 2;

        $pick_up_time = "";

        $pick_up_type = -1;

        $pick_up_weekday = '';

        $today_time = time();

        $arr = array('天','一','二','三','四','五','六');

        $pick_up_arr = array();

        foreach($goods as $key => $good)
        {

            //暂时关闭

            //ims_lionfish_comshop_goods

            //ims_ lionfish_comshop_good_common

            $tp_goods_info = M('lionfish_comshop_goods')->field('pick_start_time,pick_end_time')->where( array('id' => $good['goods_id']) )->find();

            $goods_info = M('lionfish_comshop_good_common')->field('pick_up_type,pick_up_modify')->where( array('goods_id' => $good['goods_id']) )->find();

            if($pick_up_type == -1 || $goods_info['pick_up_type'] > $pick_up_type)
            {

                $pick_up_type = $goods_info['pick_up_type'];

           
                if($pick_up_type == 0)
                {
                
                            $pick_up_time = date('m-d', $today_time);

                            $pick_up_weekday = '周'.$arr[date('w',$today_time)];
                




                }else if( $pick_up_type == 1 ){
                   
                      
                            $pick_up_time = date('m-d', $today_time+86400);
                

                        $pick_up_weekday = '周'.$arr[date('w',$today_time+86400)];
                

                }else if( $pick_up_type== 2 )
                {
                    $pick_up_time = date('m-d', $today_time+86400*2);

                    $pick_up_weekday = '周'.$arr[date('w',$today_time+86400*2)];
                
                }else if($pick_up_type == 3)
                {
                    $pick_up_time = $goods_info['pick_up_modify'];
                }
            //  print_r($pick_up_weekday);die;
                if($pick_up_type != 3 && $pick_up_time<$pick_up_times){
                    $pick_up_time = $pick_up_times;
                    $pick_up_weekday  = $pick_up_weekdays;
                }
            }
          
            //$trans_free_toal += $good['goods_freight'];

            $goods[$key]['trans_free'] = $good['goods_freight'];
            
            if( $is_vip_card_member == 1 && $good['is_take_vipcard'] == 1 )
            {

                $total_free += $good['card_total'];

            }
            else if( $good['is_mb_level_buy'] == 1  && $is_member_level_buy == 1 )
            {

                $total_free += $good['level_total'];

            }
            else
            {

                $total_free += $good['total'];

            }

        
        }

        //暂时关闭自提代码

        /**

        if(!empty($pick_up))

        {

        $pick_up = unserialize($pick_up);

        $pick_up_ids = implode(',',$pick_up);

        $pick_up_arr = M('pick_up')->where( array('id'=>array('in',$pick_up_ids)) )->select();

        }

         **/


        $pick_up_name = '';

        $pick_up_mobile = '';

        $tuan_send_address = '';

        $tuan_send_address_info = array();

        $shop_limit_buy_distance = D('Home/Front')->get_config_by_name('shop_limit_buy_distance');

        if($is_ziti >= 1)
        {

            //寻找上一个订单的自提电话 自提姓名
            $last_order_info = M('lionfish_comshop_order')->where( array('member_id' => $member_id,'delivery' => 'pickup') )->order('order_id desc')->find();

            if(!empty($last_order_info))
            {

                $pick_up_name = $last_order_info['shipping_name'];

                $pick_up_mobile = $last_order_info['telephone'];

            }

            $last_tuanz_send_order_info = M('lionfish_comshop_order')->field('tuan_send_address,address_id')->where( array('member_id' =>$member_id,'delivery' => 'tuanz_send' ) )->order('order_id desc')->find();

            if(!empty($last_tuanz_send_order_info))
            {

                $tuan_send_address = $last_tuanz_send_order_info['tuan_send_address'];

                if( !empty($last_tuanz_send_order_info['address_id']) )
                {

                    if($shop_limit_buy_distance == 1)
                    {

                        $tuan_send_address_info = M('lionfish_comshop_address')->where( "address_id=".$last_tuanz_send_order_info['address_id']." and lon_lat != '' " )->find();

                    }else{

                        $tuan_send_address_info = M('lionfish_comshop_address')->where( "address_id=".$last_tuanz_send_order_info['address_id'] )->find();

                    }

                    if( !empty($tuan_send_address) && !empty($tuan_send_address_info['lon_lat']) )
                    {

                        $province_info =  D('Home/Front')->get_area_info($tuan_send_address_info['province_id']);// M('area')->field('area_name')->where( array('area_id' => $address['province_id']) )->find();

                        $city_info = D('Home/Front')->get_area_info($tuan_send_address_info['city_id']);//M('area')->field('area_name')->where( array('area_id' => $address['city_id']) )->find();

                        $country_info = D('Home/Front')->get_area_info($tuan_send_address_info['country_id']);//M('area')->field('area_name')->where( array('area_id' => $address['country_id']) )->find();

                        $tuan_send_address_info['province_name'] = $province_info['name'];

                        $tuan_send_address_info['city_name'] = $city_info['name'];

                        $tuan_send_address_info['country_name'] = $country_info['name'];

                        $tuan_send_address = $tuan_send_address_info['address'];

                    }else{

                        //todo...
                        $tuan_send_address = '';

                    }

                }else{

                    $tuan_send_address = '';

                }

            }

        }

        //open_score_buy_score $shop_limit_buy_distance = load_model_class('front')->get_config_by_name('shop_limit_buy_distance');



        $open_score_buy_score =  D('Home/Front')->get_config_by_name('open_score_buy_score');



        if( empty($open_score_buy_score) || $buy_type == 'integral' )
        {

            $open_score_buy_score = 0;

        }



        $score_forbuy_money_maxbi = D('Home/Front')->get_config_by_name('score_forbuy_money_maxbi');



        if( empty($score_forbuy_money_maxbi) )

        {

            $score_forbuy_money_maxbi = 100;

        }



        $score_for_money = 0;

        $bue_use_score = 0;



        if( $open_score_buy_score == 1 )

        {

            if( $member_info['score'] > 0 )

            {

                //计算能兑换多少钱

                $score_forbuy_money = D('Home/Front')->get_config_by_name('score_forbuy_money');

                //只有兑换比例大于0才能允许兑换

                if( !empty($score_forbuy_money) && $score_forbuy_money >0 )

                {

                    $score_for_money = round( $member_info['score'] / $score_forbuy_money ,2);



                    if( $store_buy_total_money < $score_for_money )

                    {

                        $score_for_money = $store_buy_total_money;

                        $bue_use_score = $store_buy_total_money * $score_forbuy_money;

                    }



                    $max_store_buy_total_money = round( ($score_forbuy_money_maxbi * $store_buy_total_money) /100,2);



                    if($score_for_money > $max_store_buy_total_money)

                    {

                        $score_for_money = $max_store_buy_total_money;



                        $bue_use_score = $max_store_buy_total_money * $score_forbuy_money;

                    }else if($score_for_money <= $max_store_buy_total_money){



                        $bue_use_score = $score_for_money;

                    }

                }

            }

        }

        //score_forbuy_money score



        $delivery_ziti_name = D('Home/Front')->get_config_by_name('delivery_ziti_name');

        $delivery_tuanzshipping_name = D('Home/Front')->get_config_by_name('delivery_tuanzshipping_name');

        $delivery_diy_sort = D('Home/Front')->get_config_by_name('delivery_diy_sort');



        if(empty($delivery_diy_sort) || !isset($delivery_diy_sort)) $delivery_diy_sort = '0,1,2';





        if( empty($tuan_send_address_info) )

        {

            $tuan_send_address_info = array();

        }



        $need_data = array();

        $need_data['code'] = 1;



        $need_data['open_score_buy_score'] = $open_score_buy_score;//1开启积分抵扣

        $need_data['score'] = $member_info['score'];//会员持有的积分

        $need_data['score_for_money'] = sprintf('%.2f', $score_for_money);//会员能抵扣的金额

        $need_data['bue_use_score'] = sprintf('%.2f', $bue_use_score);//会员能抵扣的积分数





        $need_data['delivery_type_ziti'] = $delivery_type_ziti;

        $need_data['delivery_type_express'] = $delivery_type_express;

        $need_data['delivery_type_tuanz'] = $delivery_type_tuanz;

        $need_data['delivery_express_name'] = $delivery_express_name;

        $need_data['delivery_ziti_name'] = $delivery_ziti_name;

        $need_data['delivery_tuanzshipping_name'] = $delivery_tuanzshipping_name;

        $need_data['delivery_diy_sort'] = $delivery_diy_sort;





        $need_data['delivery_tuanz_money'] = $delivery_tuanz_money;

        $need_data['man_free_tuanzshipping'] = empty($man_free_tuanzshipping) ? 0 : $man_free_tuanzshipping;//团长配送，满多少免配送费，0或者为空表示不减免

        $need_data['man_free_shipping'] = empty($man_free_shipping) ? 0 : $man_free_shipping;//快递配送，满多少免配送费，0或者为空表示不减免



        $need_data['address'] = $address;





        $need_data['pick_up_time'] = $pick_up_time;

        $need_data['pick_up_type'] = $pick_up_type;

        $need_data['pick_up_weekday'] = $pick_up_weekday;



        $need_data['is_pin_over'] = $is_pin_over;

        $need_data['is_integer'] = 0;//$is_no_quan ? 1: 0;

        $need_data['pick_up_arr'] = $pick_up_arr;

        $need_data['is_ziti'] = 2;



        $need_data['ziti_name'] = $pick_up_name;

        $need_data['ziti_mobile'] = $pick_up_mobile;

        $need_data['tuan_send_address'] = $tuan_send_address;

        $need_data['tuan_send_address_info'] = $tuan_send_address_info;

        $need_data['seller_goodss'] = $seller_goodss;

        $need_data['show_voucher'] = $show_voucher;



        $need_data['buy_type'] = $buy_type;

        $need_data['address'] = $address;

        $need_data['trans_free_toal'] = $trans_free_toal;



        $need_data['is_limit_distance_buy'] = 0;

        $need_data['limit_distance'] = 100;//km



        $need_data['is_member_level_buy'] = $is_member_level_buy;//km

        $need_data['level_save_money'] = $level_save_money;//km



        $need_data['is_vip_card_member'] = $is_vip_card_member;//km

        $need_data['vipcard_save_money'] = $vipcard_save_money;//km

        $need_data['is_open_vipcard_buy'] = $is_open_vipcard_buy;//km







        if( !empty($shop_limit_buy_distance) && $shop_limit_buy_distance ==1 )

        {

            $latitude = 0;

            $longitude = 0;



            if( !empty($tuan_send_address_info) && !empty($tuan_send_address_info['lon_lat']) )

            {

                //lon_lat

                $lon_lat_arr = explode(',', $tuan_send_address_info['lon_lat']);

                $longitude = $lon_lat_arr[0];

                $latitude = $lon_lat_arr[1];

            }





            if( isset($gpc['latitude']) && !empty($gpc['latitude']) )

            {

                $latitude = $gpc['latitude'];

            }

            if( isset($gpc['longitude']) && !empty($gpc['longitude']) )

            {

                $longitude = $gpc['longitude'];

            }



            if( !empty($latitude) && !empty($longitude) )

            {

                $shop_buy_distance = D('Home/Front')->get_config_by_name('shop_buy_distance');



                $shop_buy_distance = $shop_buy_distance * 1000;





                $community_info = M('lionfish_community_head')->field('lon,lat')->where( array('id' => $community_id ) )->find();





                $distince = D('Seller/Communityhead')->GetDistance($longitude,$latitude,$community_info['lon'],$community_info['lat']);



                $need_data['current_distance'] = $distince;

                $need_data['shop_buy_distance'] = $shop_buy_distance;

                if($distince > $shop_buy_distance )

                {

                    $need_data['is_limit_distance_buy'] = 1;

                    $need_data['limit_distance'] = $distince/1000;

                }

            }

        }







        $need_data['reduce_money'] = $reduce_money;

        $need_data['is_open_fullreduction'] = $is_open_fullreduction;

        $need_data['cha_reduce_money'] = $cha_reduce_money;





        $need_data['is_man_delivery_tuanz_fare'] = $is_man_delivery_tuanz_fare; //是否达到满xx减团长配送费

        $need_data['fare_man_delivery_tuanz_fare_money'] = $fare_man_delivery_tuanz_fare_money;	//达到满xx减团长配送费， 减了多少钱

        $need_data['is_man_shipping_fare'] = $is_man_shipping_fare; //是否达到满xx减运费

        $need_data['fare_man_shipping_fare_money'] = $fare_man_shipping_fare_money;	//达到满xx减运费，司机减了多少运费





        $dispatching = isset($gpc['dispatching']) ? $gpc['dispatching']:'pickup';

        //is_ziti == 2

        if($dispatching == 'express')

        {

            $need_data['total_free'] = $total_free + $trans_free_toal - $voucher_price -$reduce_money;

        }else{

            $need_data['total_free'] = $total_free  - $voucher_price -$reduce_money;

        }

        if($is_ziti == 2)

        {

            $need_data['total_free'] = $total_free  - $voucher_price -$reduce_money;

        }



        //积分兑换 不算总金额，但是算总积分

        if( $buy_type == 'integral' )

        {

            $need_data['total_free'] = $trans_free_toal;

            $need_data['total_integral'] = $total_free;

        }



        if($need_data['total_free'] < 0)

        {

            $need_data['total_free'] = 0;

        }



        //判断是否可以余额支付



        //暂时关闭 会员余额功能

        /**

        $is_yue_open_info =	M('config')->where( array('name' => 'is_yue_open') )->find();

        $is_yue_open =  $is_yue_open_info['value'];

         **/



        $is_yue_open = 0;



        $is_yue_open = D('Home/Front')->get_config_by_name('is_open_yue_pay');

        if( empty($is_yue_open) )

        {

            $is_yue_open = 0;

        }





        $need_data['is_yue_open'] = $is_yue_open;



        $need_data['can_yupay'] = 0;



        //暂时关闭 会员余额功能



        if($is_yue_open == 1 && $need_data['total_free'] >=0 && $member_info['account_money'] >= $need_data['total_free'])

        {

            $need_data['can_yupay'] = 1;

        }



        //前端隐藏 团长信息

        $index_hide_headdetail_address = D('Home/Front')->get_config_by_name('index_hide_headdetail_address');



        if( empty($index_hide_headdetail_address) )

        {

            $index_hide_headdetail_address = 0;

        }

        $need_data['index_hide_headdetail_address'] = $index_hide_headdetail_address;





        //订单留言

        $is_open_order_message = D('Home/Front')->get_config_by_name('is_open_order_message');



        $need_data['yu_money'] = $member_info['account_money'];

        $need_data['goods'] = $goods;

        $need_data['is_open_order_message'] = $is_open_order_message;



        $need_data['is_zero_opentuan'] = 0;

        //拼团特殊情况0元开团

        if( $buy_type == 'pintuan' && $is_zero_buy == 1 )

        {

            //$need_data['total_free'] = 0;

            //$need_data['trans_free_toal'] = 0;

            //$need_data['is_zero_opentuan'] = 1;

        }





        //订阅消息begin

        //  weprogram_use_templatetype

        $weprogram_use_templatetype = D('Home/Front')->get_config_by_name('weprogram_use_templatetype');



        $is_need_subscript = 0;

        $need_subscript_template = array();



        if( !empty($weprogram_use_templatetype) &&  $weprogram_use_templatetype == 1 )

        {

            //'pay_order','send_order','hexiao_success','apply_community','open_tuan','take_tuan','pin_tuansuccess','apply_tixian'

            //$member_id

            if( $buy_type == 'pintuan' )

            {

                //pin_tuansuccess

                //send_order  parameter[weprogram_subtemplate_pin_tuansuccess]

                //hexiao_success



                $pin_tuansuccess_info = M('lionfish_comshop_subscribe')->where( array('member_id' => $member_id, 'type' => 'pin_tuansuccess' ) )->find();



                if( empty($pin_tuansuccess_info) )

                {

                    $weprogram_subtemplate_pin_tuansuccess = D('Home/Front')->get_config_by_name('weprogram_subtemplate_pin_tuansuccess');



                    if( !empty($weprogram_subtemplate_pin_tuansuccess) )

                    {

                        $need_subscript_template['pin_tuansuccess'] = $weprogram_subtemplate_pin_tuansuccess;

                    }

                }



            }else{

                //pay_order

                $pay_order_info = M('lionfish_comshop_subscribe')->where( array('member_id' => $member_id , 'type' => 'pay_order') )->find();

			

                if( empty($pay_order_info) )

                {

                    $weprogram_subtemplate_pay_order = D('Home/Front')->get_config_by_name('weprogram_subtemplate_pay_order');



                    if( !empty($weprogram_subtemplate_pay_order) )

                    {

                        $need_subscript_template['pay_order'] = $weprogram_subtemplate_pay_order;

                    }

                }

            }

            //send_order



            $send_order_info = M('lionfish_comshop_subscribe')->where( array('member_id' => $member_id, 'type' => 'send_order' ) )->find();



            if( empty($send_order_info) )

            {

                $weprogram_subtemplate_send_order = D('Home/Front')->get_config_by_name('weprogram_subtemplate_send_order');



                if( !empty($weprogram_subtemplate_send_order) )

                {

                    $need_subscript_template['send_order'] = $weprogram_subtemplate_send_order;

                }

            }

            //hexiao_success

            $hexiao_success_info = M('lionfish_comshop_subscribe')->where( array('member_id' => $member_id, 'type' => 'hexiao_success' ) )->find();





            if( empty($hexiao_success_info) )

            {

                $weprogram_subtemplate_hexiao_success = D('Home/Front')->get_config_by_name('weprogram_subtemplate_hexiao_success');



                if( !empty($weprogram_subtemplate_hexiao_success) )

                {

                    $need_subscript_template['hexiao_success'] = $weprogram_subtemplate_hexiao_success;

                }

            }



            if( !empty($need_subscript_template) )

            {

                $is_need_subscript = 1;

            }

        }

        //订阅消息end

		

        $need_data['is_need_subscript'] = $is_need_subscript;

        $need_data['need_subscript_template'] = $need_subscript_template;





        echo json_encode($need_data);

        die();

    }

    public function sub_order()

    {

        $gpc = I('request.');



        $token = $gpc['token'];



        $weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

        $member_id = $weprogram_token['member_id'];



        $pintuan_model_buy = D('Home/Front')->get_config_by_name('pintuan_model_buy');



        if( empty($pintuan_model_buy) || $pintuan_model_buy ==0 )

        {

            $pintuan_model_buy = 0;

        }



        $is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');

        $is_open_vipcard_buy = !empty($is_open_vipcard_buy) && $is_open_vipcard_buy ==1 ? 1:0;



        $is_vip_card_member = 0;

        $is_member_level_buy = 0;



        if( $member_id > 0 )

        {



            $member_info = M('lionfish_comshop_member')->where( array('member_id' => $member_id ) )->find();



            if( !empty($is_open_vipcard_buy) && $is_open_vipcard_buy == 1 )

            {



                $now_time = time();



                if( $member_info['card_id'] >0 && $member_info['card_end_time'] > $now_time )

                {

                    $is_vip_card_member = 1;//还是会员

                }else if( $member_info['card_id'] >0 && $member_info['card_end_time'] < $now_time ){

                    $is_vip_card_member = 2;//已过期

                }

            }



            if($is_vip_card_member != 1 && $member_info['level_id'] >0 )

            {

                $is_member_level_buy = 1;

            }

        }





        //use_score = 1

        $use_score = isset($gpc['use_score']) ? intval($gpc['use_score']) : 0;



        $puis_not_buy =  D('Home/Front')->get_config_by_name('puis_not_buy');



        if( !empty($puis_not_buy) && $puis_not_buy ==1 )

        {

            $member_info = M('lionfish_comshop_member')->field('level_id')->where( array('member_id' => $member_id) )->find();



            if($member_info['level_id'] == 0)

            {

                echo json_encode( array('code' => 2, 'msg' => '普通会员不能购买') );

                die();

            }

        }



        $data_s  = array();

        $data_s['pay_method'] = $gpc['wxpay'];

        $data_s['buy_type'] = isset($gpc['buy_type']) ? $gpc['buy_type'] : 'dan';
        if($gpc['pick_up_id'] == 'undefined'){
            $data_s['pick_up_id'] = 0;
        }else{
        $data_s['pick_up_id'] = $gpc['pick_up_id'];
        }
        $data_s['dispatching'] = $gpc['dispatching'];

        $data_s['soli_id'] = isset($gpc['soli_id']) ? intval($gpc['soli_id']) : 0 ;





        if($data_s['dispatching'] != 'express' && empty($data_s['pick_up_id']))

        {

            $last_community = M('lionfish_community_history')->where( array('member_id' => $member_id) )->order('id desc')->find();



            if( !empty($last_community) )

            {

                $data_s['pick_up_id'] = $last_community['head_id'];

            }





            //$data_s['pick_up_id']

        }



        if( $data_s['buy_type'] == 'dan' || $data_s['buy_type'] == 'soitaire' || ($pintuan_model_buy == 1 && $data_s['buy_type'] != 'dan' && $data_s['buy_type'] != 'integral'  ) )

        {

            D('Seller/Community')->in_community_history($member_id,$data_s['pick_up_id']);

        }



        //'express'  快递, 'pickup'  自提, 'tuanz_send'  团长配送)   tuan_send_address



        $data_s['ziti_name'] = $gpc['ziti_name'];

        $data_s['quan_arr'] = $gpc['quan_arr'];





        $data_s['comment'] = $gpc['comment'];

        $data_s['ziti_mobile'] = $gpc['ziti_mobile'];

        $data_s['tuan_send_address'] = $gpc['tuan_send_address'];

        $data_s['ck_yupay'] = $gpc['ck_yupay'];







        $data_s['province_name'] = isset($gpc['province_name']) ? $gpc['province_name']:'' ;

        $data_s['city_name'] = isset($gpc['city_name']) ? $gpc['city_name']: '';

        $data_s['country_name'] = isset($gpc['country_name']) ? $gpc['country_name']: '';

        $data_s['address_name'] = isset($gpc['address_name']) ? $gpc['address_name']:'' ;



        $data_s['latitude'] = isset($gpc['latitude']) ? $gpc['latitude']:'' ;

        $data_s['longitude'] = isset($gpc['longitude']) ? $gpc['longitude']:'' ;

        $data_s['lou_meng_hao'] = isset($gpc['lou_meng_hao']) ? $gpc['lou_meng_hao']:'' ;



        //$data_s['tuan_send_address'] .= $data_s['lou_meng_hao'];

        //$data_s['tuan_send_address'] .= $data_s['lou_meng_hao'];



        //tuan_send_address



        $province_name = isset($data_s['province_name']) ? $data_s['province_name'] : '';

        $city_name = isset($data_s['city_name']) ? $data_s['city_name'] : '';

        $country_name = isset($data_s['country_name']) ? $data_s['country_name'] : '';

        $address_name = isset($data_s['address_name']) ? $data_s['address_name'] : '';









        $json=array();



        $pay_method = $data_s['pay_method'];//支付类型

        $order_msg_str = $data_s['order_msg_str'];//商品订单留言

        $comment = $data_s['comment'];//商品订单留言



        $comment_arr = array();

        if( !empty($data_s['comment']) )

        {

            $comment_arr = explode('@EOF@', $data_s['comment']);

        }







        $pick_up_id = $data_s['pick_up_id'];

        $dispatching = $data_s['dispatching'];

        $ziti_name = $data_s['ziti_name'];

        $ziti_mobile = $data_s['ziti_mobile'];





        //新增快递

        $province_name = isset($data_s['province_name']) ? $data_s['province_name'] : '';

        $city_name = isset($data_s['city_name']) ? $data_s['city_name'] : '';

        $country_name = isset($data_s['country_name']) ? $data_s['country_name'] : '';

        $address_name = isset($data_s['address_name']) ? $data_s['address_name'] : '';





        $ck_yupay = $data_s['ck_yupay'];



        if($dispatching == 'express')

        {

            $data_s['address_id'] = $this->_add_address($token,$ziti_name,$ziti_mobile,$province_name,$city_name, $country_name,$address_name);

        }else if($dispatching == 'tuanz_send'){

            $data_s['address_id'] = $this->_add_address($token,$ziti_name,$ziti_mobile,$province_name,$city_name, $country_name,$data_s['tuan_send_address'],$data_s['latitude'],$data_s['longitude'],$data_s['lou_meng_hao'] );



            $data_s['tuan_send_address'] .= $data_s['lou_meng_hao'];

        }



        /**



        pick_up_id: that.data.pick_up_id,

        dispatching: that.data.dispatching, //express  pickup

        ziti_name: t_ziti_name,

        ziti_mobile: t_ziti_mobile

         **/

        $order_msg_arr = explode('@,@', $order_msg_str);



        $quan_arr = $data_s['quan_arr'];//商品订单留言



        $order_quan_arr = array();





        if( !empty($quan_arr) )

        {

            if( !is_array($quan_arr) )

            {

                $quan_arr = array($quan_arr);

            }



            foreach($quan_arr as $q_val)

            {

                $tmp_q = array();

                $tmp_q = explode('_',$q_val);





                $voucher_info = M('lionfish_comshop_coupon_list')->where("consume='N' and id=".$tmp_q[1]." and user_id=".$member_id." and end_time >".time())->find();



                if( !empty($voucher_info) )

                {

                    //$order_quan_arr[$tmp_q[0]] = $tmp_q[1];

                    $order_quan_arr[1] = $tmp_q[1];

                }





            }

        }









        $msg_arr = array();

        foreach($order_msg_arr as $val)

        {

            $tmp_val = explode('@_@', $val);

            $msg_arr[ $tmp_val[0] ] = $tmp_val[1];

        }





        $cart= D('Home/Car');



        // 验证商品数量

        //buy_type:buy_type

        $buy_type = $data_s['buy_type'];//I('post.buy_type');





        $is_pin = 0;

        if($buy_type == 'pintuan')

        {

            $is_pin = 1;

        }



        $goodss = $cart->get_all_goodswecar($buy_type,$token,1,$data_s['pick_up_id'], $data_s['soli_id']);

        //付款人



        $payment = M('lionfish_comshop_member')->where( array('member_id' => $member_id) )->find();



        //收货人

        $addr_param = array();

        $addr_param[':uniacid'] = $_W['uniacid'];

        $addr_param[':member_id'] = $member_id;



        //$addr_sql = "select * from ".tablename('lionfish_comshop_address')." where uniacid=:uniacid and member_id=:member_id order by  is_default desc,address_id desc limit 1";

        //$address = pdo_fetch($addr_sql, $addr_param);



        $seller_goodss = array();



        /** 计算每个订单的优惠券占比begin */

        $zanbi_total_money = 0;



        foreach($goodss as $key => $val)

        {

            //单商户先屏蔽

            //$goods_store_field =  M('goods')->field('store_id')->where( array('goods_id' => $val['goods_id']) )->find();



            $supply_id = D('Home/Front')->get_goods_supply_id($val['goods_id']);

            if($supply_id > 0)

            {

                $supply_info = D('Home/Front')->get_supply_info($supply_id);



                if($supply_info['type'] ==0)

                {

                    $supply_id = 0;

                }

            }

            $seller_goodss[ $supply_id ][$key] = $val;



            //$cart->removecar($val['key'],$token);





            if( $is_vip_card_member == 1 && $val['is_take_vipcard'] == 1 )

            {

                $zanbi_total_money += $val['card_total'];

            }

            else if( $val['is_mb_level_buy'] > 0  && $is_member_level_buy == 1)

            {

                $zanbi_total_money += $val['level_total'];

            }

            else

            {

                $zanbi_total_money += $val['total'];

            }





        }



        //....看看有没有满多少才能下单begin

        $open_man_orderbuy = D('Home/Front')->get_config_by_name('open_man_orderbuy');

        $man_orderbuy_money = D('Home/Front')->get_config_by_name('man_orderbuy_money');



        //pindan （拼团商品单独购买）   pintuan （拼团）

        if($buy_type == 'pintuan' || $buy_type == 'pindan' || $buy_type == 'integral' )

        {

            $open_man_orderbuy = 0;

        }



        if( !empty($open_man_orderbuy) &&  $open_man_orderbuy == 1 )

        {

            if( !empty($man_orderbuy_money) && $man_orderbuy_money >0 )

            {

                if($man_orderbuy_money > $zanbi_total_money)

                {

                    echo json_encode( array('code' => 2,'msg' => '满'.$man_orderbuy_money.'元才可以下单') );

                    die();

                }

            }

        }

        //....看看有没有满多少才能下单end

        //清除购物车

        foreach($goodss as $key => $val)

        {

            $cart->removecar($val['key'],$token);



        }

        /** 计算每个订单的优惠券占比end */



        $pay_total = 0;

        //M('order_all')





        $order_all_data = array();

        $order_all_data['member_id'] = $member_id;

        $order_all_data['order_num_alias'] = build_order_no($member_id);

        $order_all_data['transaction_id'] = '';

        $order_all_data['order_status_id'] = 3;

        $order_all_data['is_pin'] = $is_pin;

        $order_all_data['paytime'] = 0;



        $order_all_data['addtime'] = time();



        $order_all_id = M('lionfish_comshop_order_all')->add($order_all_data);





        //暂时屏蔽积分商城模块



        $integral_model = D('Home/Integral');



        $order_ids_arr = array();

        $del_integral = 0;



        if( ($buy_type == 'pintuan' || $buy_type == 'pindan') && $pintuan_model_buy == 0 )

        {

            $community_info = array();

            $community_detail_info = array();

        }

        else if( $buy_type == 'integral' )

        {

            $community_info = array();

            $community_detail_info = array();

        }

        else if( ($buy_type == 'pintuan' || $buy_type == 'pindan') && $pintuan_model_buy == 1 )

        {

            $community_info = M('lionfish_community_head')->where( array('id' => $data_s['pick_up_id'] ) )->find();

            $community_detail_info = D('Home/Front')->get_community_byid($data_s['pick_up_id']);

        }else{

            $community_info = M('lionfish_community_head')->where( array('id' => $data_s['pick_up_id'] ) )->find();

            $community_detail_info = D('Home/Front')->get_community_byid($data_s['pick_up_id']);

        }







        $address_info = M('lionfish_comshop_address')->where( array('address_id' => $data_s['address_id'] ) )->find();





        $is_open_fullreduction = D('Home/Front')->get_config_by_name('is_open_fullreduction');

        $full_money = D('Home/Front')->get_config_by_name('full_money');

        $full_reducemoney = D('Home/Front')->get_config_by_name('full_reducemoney');





        $man_free_tuanzshipping = D('Home/Front')->get_config_by_name('man_free_tuanzshipping');

        $man_free_shipping = D('Home/Front')->get_config_by_name('man_free_shipping');



        if( empty($man_free_tuanzshipping) )

        {

            $man_free_tuanzshipping = 0;

        }



        if( empty($man_free_shipping) )

        {

            $man_free_shipping = 0;

        }



        if(empty($full_reducemoney) || $full_reducemoney <= 0)

        {

            $is_open_fullreduction = 0;

        }



        if( ($buy_type == 'pintuan' || $buy_type == 'pindan') && $pintuan_model_buy == 0  )

        {

            $man_free_tuanzshipping = 0;

            $man_free_shipping = 0;

            $is_open_fullreduction = 0;

        }

        else if( $buy_type == 'integral' )

        {

            $man_free_tuanzshipping = 0;

            $man_free_shipping = 0;

            $is_open_fullreduction = 0;

        }

        else if( ($buy_type == 'pintuan' || $buy_type == 'pindan') && $pintuan_model_buy == 1 )

        {

            $man_free_shipping = 0;

            $is_open_fullreduction = 0;

        }



        $is_moban = false;





        $cart = D('Home/Car');

        $is_just_1 = 0;

        $index_comment = 0;



        $pay_goods_name = "";



        $store_buy_total_money = 0;



        $open_score_buy_score = D('Home/Front')->get_config_by_name('open_score_buy_score');



        $score_for_money = 0;//use_score



        if( $buy_type == 'integral' )

        {

            $open_score_buy_score = 0;

        }





        if($open_score_buy_score == 1 && $use_score == 1 && $payment['score'] > 0 )

        {

            //计算能兑换多少钱

            $score_forbuy_money = D('Home/Front')->get_config_by_name('score_forbuy_money');

            //只有兑换比例大于0才能允许兑换

            if( !empty($score_forbuy_money) && $score_forbuy_money >0 )

            {

                $score_for_money = round( $payment['score'] / $score_forbuy_money ,2);

            }

        }
      
        
     
       
        foreach($seller_goodss as $kk => $vv)

        {

            $is_just_1++;



            $data = array();



            $data['member_id']=$member_id;

            $data['name']= $payment['username'];

            $data['use_score']= $use_score;//是否使用积分抵扣



            $data['telephone']= $data_s['ziti_mobile'];

            $data['shipping_name']= $data_s['ziti_name'];

            $data['shipping_tel']= $data_s['ziti_mobile'];





            if($dispatching == 'express' || $dispatching == 'tuanz_send')

            {

                $data['shipping_address'] = $address_info['address'];

                $data['shipping_province_id']=$address_info['province_id'];

                $data['shipping_city_id']=$address_info['city_id'];

                $data['shipping_stree_id']= 0;

                $data['shipping_country_id']=$address_info['country_id'];



            }else{

                $data['shipping_address'] = $community_detail_info['fullAddress'];

                $data['shipping_province_id']=$community_info['province_id'];

                $data['shipping_city_id']=$community_info['city_id'];

                $data['shipping_stree_id']=$community_info['country_id'];

                $data['shipping_country_id']=$community_info['area_id'];

            }





            $data['shipping_method'] = 0;

            $data['delivery']=$dispatching;

            $data['pick_up_id']=$pick_up_id;



            $data['ziti_name']=$community_info['head_name'];

            $data['ziti_mobile']=$community_info['head_mobile'];





            $data['payment_method']=$pay_method;



            $data['address_id']= $data_s['address_id'];

            $data['voucher_id'] = isset($order_quan_arr[1]) ? $order_quan_arr[1]:0;//目前都是平台券





            $data['user_agent']=$_SERVER['HTTP_USER_AGENT'];

            $data['date_added']=time();



            $subject='';

            $fare = 0;

            $order_total = 0;





            $trans_free_toal = 0;//运费



            $reduce_money = 0;



            $man_total_free = 0;

            $score_buy_money = 0;



            $is_lottery = 0;

            $is_integral = 0;

            $is_spike = 0;



            $total_weight = 0;

            $total_quantity = 0;



            $redis_has_add_list = array();



            $is_free_shipping_fare = 0;//是否免除运费



            $order_goods_total_money = 0;

            $goods_data = array();





            //comment_arr comment_arr

            $tongji=array();

            foreach($vv as $key => $good)

            {
               
        
     
                //判断是否是同一运费模板累计数量 和 金额
                $tongji[$good['transport_id']]['num'] +=$good['quantity'];
                $tongji[$good['transport_id']]['price'] +=$good['price']*$good['quantity'];
                $tongji[$good['transport_id']]['weight'] +=$good['weight'];
       


                if( $kk == 0 )

                {

                    if( $is_vip_card_member == 1 && $good['is_take_vipcard'] == 1 )

                    {

                        $store_buy_total_money += $good['card_total'];

                    }

                    else if( $good['is_mb_level_buy'] == 1 && $is_member_level_buy == 1)

                    {

                        $store_buy_total_money += $good['level_total'];

                    }

                    else

                    {

                        $store_buy_total_money += $good['total'];

                    }



                }

                /**

                if($good['shipping']==1)

                {

                //统一运费

                $trans_free_toal += $good['goods_freight'];

                $trans_free = $good['goods_freight'];

                }else {

                //运费模板

                $trans_free = load_model_class('transport')->calc_transport($good['transport_id'], $good['quantity'], $good['quantity']*$good['weight'], $address['city_id'] );



                //$trans_free = D('Home/Transport')->calc_transport($good['transport_id'], $good['quantity']*$good['weight'], $address['city_id'] );

                $trans_free_toal +=$trans_free;

                }

                 **/

                $trans_free = 0;

                //$trans_free_toal +=$trans_free;

                //sku_str



                if( $is_vip_card_member == 1 && $good['is_take_vipcard'] == 1 )

                {

                    $order_goods_total_money += $good['card_total'];

                    $order_total += $good['card_total'];

                }

                else if( $good['is_mb_level_buy'] == 1 && $is_member_level_buy == 1)

                {

                    $order_goods_total_money += $good['level_total'];

                    $order_total += $good['level_total'];

                }

                else

                {

                    $order_goods_total_money += $good['total'];

                    $order_total += $good['total'];

                }



                $tp_goods_info = M('lionfish_comshop_goods')->field('type')->where( array('id' => $good['goods_id']) )->find();



                $tp_goods_info['store_id'] = 1;



                if($tp_goods_info['type'] == 'lottery')

                {

                    $is_lottery = 1;

                }

                if($tp_goods_info['type'] == 'spike')

                {

                    $is_spike = 1;

                    $is_pin = 0;

                }

                //暂时屏蔽积分商城模块



                if($tp_goods_info['type'] == 'integral')

                {

                    $is_integral = 1;

                    $is_pin = 0;

                    $check_result = $integral_model->check_user_score_can_pay($member_id, $good['sku_str'], $good['goods_id'] );

                    if($check_result['code'] == 1)

                    {

                        echo json_encode( array('code' => 2, 'msg' => '剩余'.$check_result['cur_score'].'积分，积分不足!' ) );

                        die();

                    }



                }





                if($good['shipping']==0)

                {

                    $is_moban = true;

                    //统一运费

                    $total_weight += $good['weight']*$good['quantity'];

                    $total_quantity += $good['quantity'];

                }



                $fenbi_li = 1;

                if(  $zanbi_total_money > 0 )

                {

                    if( $is_vip_card_member == 1 && $good['is_take_vipcard'] == 1 )

                    {

                        $fenbi_li = round($good['card_total'] / $zanbi_total_money, 2);

                    }

                    else if( $good['is_mb_level_buy'] == 1 && $is_member_level_buy == 1)

                    {

                        $fenbi_li = round($good['level_total'] / $zanbi_total_money, 2);

                    }

                    else

                    {

                        $fenbi_li = round($good['total'] / $zanbi_total_money, 2);

                    }

                }



                if( isset($comment_arr[$index_comment]) )

                {

                    $comment = $comment_arr[$index_comment];



                }





                //监测库存数量

                $quantity_flag = D('Seller/Redisorder')->check_goods_can_buy($good['goods_id'], $good['sku_str'],$good['quantity']);

                if( $quantity_flag <=0 )

                {

                    echo json_encode(  array('code' => 2, 'msg' => '已抢光') );

                    die();

                }

                //如果是下单减库存,那么用占坑法来避免超库存---begin

                $kucun_method = D('Home/Front')->get_config_by_name('kucun_method');



                if( empty($kucun_method) )

                {

                    $kucun_method = 0;

                }



                if($kucun_method == 0)

                {

                    //$ret = $redis->rPush('city', 'guangzhou');

                    $check_redis_quantity = D('Seller/Redisorder')->add_goods_buy_user($good['goods_id'], $good['sku_str'],$good['quantity'],$member_id);

                    //注意要回滚

                    //$redis_has_add_list[]  = array('member_id' => $member_id, 'goods_id' => $good['goods_id'], 'sku_str' => $good['sku_str'] );



                    //$key = "user_goods_{$member_id}_{$goods_id}_{$sku_str}";



                    if($check_redis_quantity == 0)

                    {

                        //cancle_redis_user_list

                        //D('Seller/Redisorder')->cancle_goods_buy_user($redis_has_add_list);



                        echo json_encode( array('code' => 2, 'msg' => '已抢光') );

                        die();

                    }

                }

                //----------------redis   end

                if( $good['can_man_jian'] == 1)

                {

                    $man_total_free += $good['total'];

                }



                $pay_goods_name .= $good['name'];



                if( $good['is_mb_level_buy'] == 1 && $is_member_level_buy == 1 )

                {

                    $good['is_mb_level_buy'] == 1;

                }else{

                    $good['is_mb_level_buy'] == 0;

                }



                $goods_data[] = array(

                    'goods_id'   => $good['goods_id'],

                    'store_id' => $tp_goods_info['store_id'],

                    'name'       => $good['name'],

                    'model'      => $good['model'],

                    'is_pin' => $is_pin,

                    'pin_id' => $good['pin_id'],

                    'header_disc' => $good['header_disc'],

                    'member_disc' => $good['member_disc'],

                    'level_name' => $good['level_name'],

                    'option'     => $good['sku_str']== 'undefined' ? '':$good['sku_str'],

                    'quantity'   => $good['quantity'],

                    'shipping_fare' => $trans_free,

                    'price'      => $good['price'],

                    'card_price' => $good['card_price'],

                    'levelprice' => $good['levelprice'],

                    'total'      => $good['total'],

                    'card_total' => $good['card_total'] ,

                    'level_total' => $good['level_total'] ,

                    'is_mb_level_buy' => $good['is_mb_level_buy']  ,

                    'is_take_vipcard' => $good['is_take_vipcard'],

                    'fenbi_li'      => $fenbi_li,

                    'can_man_jian'      => $good['can_man_jian'],

                    'soli_id'      => $good['soli_id'],

                    'comment' => htmlspecialchars($comment)

                );



            }



            $index_comment++;

            //$total_weight = 0;

            //$total_quantity = 0;



            if($dispatching == 'express')

            {

                //                //结算运费新模式
                //
                //                $trans_free_toal = 0;//运费
                //
                //
                //
                //                //----开始计算运费
                //
                //
                //
                //                //ims_
                //
                //
                //
                //                $shipping_default = M('lionfish_comshop_shipping')->where("enabled=1")->order('isdefault desc,id desc')->find();
                //
                //
                //
                //                $seller_goodss_re = $seller_goodss;
                //
                //
                //
                //
                //
                //
                //
                //                $store_shipping_fare = 0;
                //
                //                if($is_moban)
                //
                //                {
                //
                //                    $store_shipping_fare = D('Home/Transport')->calc_transport($shipping_default['id'], $total_quantity,$total_weight, $address_info['city_id'] );
                //
                //
                //
                //
                //
                //                }
                //
                //
                //
                //                $trans_free_toal += $store_shipping_fare;



                // foreach($vv as $kkc =>$d_goods)

                // {
                //     //结算运费新模式

                //     $trans_free_toal = 0;//运费



                //     //----开始计算运费



                //     //ims_



                //  //   $shipping_default = M('lionfish_comshop_shipping')->where("enabled=1")->order('isdefault desc,id desc')->find();
                //     $shipping_default = M('lionfish_comshop_shipping')->where(array('id' => $d_goods['transport_id']))->find();




                //     $seller_goodss_re = $seller_goodss;







                //     $store_shipping_fare = 0;

                //     if($is_moban)

                //     {

                //         $store_shipping_fare = D('Home/Transport')->calc_transport($shipping_default['id'], $total_quantity,$total_weight, $address_info['city_id'] );





                //     }



                //     $trans_free_toal += $store_shipping_fare;
                //     if($d_goods['shipping']==1)

                //     {

                //         //统一运费

                //         $trans_free_toal += $d_goods['goods_freight'];



                //     }

                // }


                $trans_free_toal= D('Home/Transport')->freight_calculation($vv,$address_info['city_id'],$tongji);


                if( $kk == 0 && !empty($man_free_shipping) && $man_free_shipping > 0 && $order_goods_total_money >= $man_free_shipping )

                {



                    //$trans_free_toal = 0;

                    $is_free_shipping_fare = 1;

                }



                //---结束结算运费 address_id



                $data_s['address_id'] = $this->_add_address($token,$ziti_name,$ziti_mobile,$province_name,$city_name, $country_name,$address_name);

            }else if('tuanz_send' == $dispatching)

            {

                $trans_free_toal = 0;

                $delivery_tuanz_money = D('Home/Front')->get_config_by_name('delivery_tuanz_money');





                $community_info_modify = $community_info;



                if( !empty($community_info_modify['is_modify_shipping_method']) && $community_info_modify['is_modify_shipping_method'] == 1 )

                {

                    if( !empty($community_info_modify['is_modify_shipping_fare']) && $community_info_modify['is_modify_shipping_fare'] == 1 && $community_info_modify['shipping_fare'] > 0 )

                    {

                        $delivery_tuanz_money = $community_info_modify['shipping_fare'];

                    }

                }





                $trans_free_toal = $delivery_tuanz_money;



                $data['tuan_send_address'] = $data_s['tuan_send_address'];



                if( $kk == 0 && !empty($man_free_tuanzshipping) && $man_free_tuanzshipping > 0 && $order_goods_total_money >= $man_free_tuanzshipping )

                {

                    $is_free_shipping_fare = 1;

                    //$trans_free_toal = 0;

                }

            }









            //$is_pin; is_lottery

            //'pintuan', 'normal', 'lottery'

            $data['type'] = 'normal';

            if($is_pin == 1)

            {

                $data['type'] = 'pintuan';

                if($is_lottery == 1)

                {

                    $data['type'] = 'lottery';

                }

            }

            if($is_integral == 1)

            {

                $data['type'] = 'integral';

                $is_pin = 0;

            }

            if($is_spike == 1)

            {

                $data['type'] = 'spike';

                $is_pin = 0;

            }



            $data['shipping_fare'] = floatval($trans_free_toal);



            if($is_free_shipping_fare == 1)

            {

                $trans_free_toal = 0;

            }



            if($is_open_fullreduction == 1 && $man_total_free >= $full_money )

            {



                //order_goods_total_money

                $bili = 1;

                /**

                if( $man_total_free > 0 )

                {

                $bili = round( ($order_goods_total_money / $man_total_free), 2);

                }

                 **/

                $reduce_money = round( $full_reducemoney * $bili ,2);

            }



            $data['is_free_shipping_fare']= $is_free_shipping_fare;

            $data['store_id']= $kk;

            $data['order_goods_total_money']= $order_goods_total_money;





            $data['goodss'] = $goods_data;

            $data['order_num_alias']=build_order_no($member_id);



            $data['totals'][0]=array(

                'code'=>'sub_total',

                'title'=>'商品价格',

                'text'=>'￥'.$order_total,

                'value'=>$order_total

            );

            $data['totals'][1]=array(

                'code'=>'shipping',

                'title'=>'运费',

                'text'=>'￥'.$trans_free_toal,

                'value'=>$trans_free_toal

            );



            $data['totals'][2]=array(

                'code'=>'total',

                'title'=>'总价',

                'text'=>'￥'.($order_total+$trans_free_toal-$reduce_money),

                'value'=>($order_total+$trans_free_toal-$reduce_money)

            );



            $data['from_type'] = 'wepro';



            //目前都是平台券

            if($data['voucher_id'] > 0) {



                //暂时屏蔽优惠券，等待开启

                /**

                $voucher_info = pdo_fetch("select * from ".tablename('lionfish_comshop_coupon_list')." where uniacid=:uniacid and id=:id ",

                array(':uniacid' => $_W['uniacid'],':id' => $data['voucher_id']));



                $data['voucher_credit'] = $voucher_info['credit'];



                pdo_update('lionfish_comshop_coupon_list', array('ordersn' => $data['order_num_alias'],'consume' => 'Y','usetime' => time()), array('id' => $data['voucher_id'] ));

                 **/



                $voucher_info = M('lionfish_comshop_coupon_list')->where( array('id' => $data['voucher_id']) )->find();



                $data['voucher_credit'] = $voucher_info['credit'];



                $bili = 1;



                if( $zanbi_total_money > 0 )

                {

                    $bili = round( ($order_goods_total_money / $zanbi_total_money), 2);

                }

                $data['voucher_credit'] = $data['voucher_credit'] * $bili;





                //判断是否超出

                if($data['voucher_credit'] > $order_total+$trans_free_toal - $reduce_money )

                {

                    $data['voucher_credit'] = $order_total+$trans_free_toal - $reduce_money;

                }





                M('lionfish_comshop_coupon_list')->where( array('id' => $data['voucher_id'] ) )->save( array('ordersn' => $data['order_num_alias'],'consume' => 'Y','usetime' => time() ) );



            } else {

                $data['voucher_credit'] = 0;

            }



            $use_score_total = 0;//用掉用户多少积分了.

            $data['score_for_money'] = 0;



            if( $kk == 0 && $score_for_money  > 0)

            {

                if( $order_total+$trans_free_toal - $reduce_money - $data['voucher_credit'] <= 0)

                {

                    //没必要扣积分了，单价已经是0

                }else{

                    //只能抵扣扣除优惠券部分的金额

                    $del_money = $order_total - $data['voucher_credit'];



                    if( $score_for_money >= $del_money)

                    {

                        $score_for_money = $del_money;

                    }

                    //计算多少积分了。



                    $score_forbuy_money_maxbi = D('Home/Front')->get_config_by_name('score_forbuy_money_maxbi');



                    if( empty($score_forbuy_money_maxbi) )

                    {

                        $score_forbuy_money_maxbi = 100;

                    }

                    $max_dikou_money =  round( ($order_total * $score_forbuy_money_maxbi) /100,2);



                    if($max_dikou_money < $score_for_money)

                    {

                        $score_for_money = $max_dikou_money;

                    }

                    //$score_buy_money = 0;

                    $data['score_for_money'] = $score_for_money;

                    //TODO...扣除会员积分，将积分分拆入每个商品订单，写入日志

                }

            }





            $data['comment'] = htmlspecialchars($comment);



            $data['reduce_money'] = $reduce_money;



            $data['man_total_free'] = $man_total_free;



            //判断自提 dispatching:"pickup"

            //dispatching, //express  pickup



            if($dispatching == 'express')

            {

                $data['total']=($order_total);//+$fare - $data['voucher_credit']

            }else if('tuanz_send' == $dispatching){

                $data['total']=($order_total);// - $data['voucher_credit']);

            }

            else{

                $data['total'] = ($order_total );// - $data['voucher_credit']

            }

            //积分商城

            //暂时屏蔽积分商城模块





            if($data['type'] == 'integral')

            {

                $del_integral += $order_total;//扣除积分

                $data['total'] = $order_total;



            }







            $oid=  D('Home/Frontorder')->addOrder($data);// D('Order')->addOrder($data);





            //暂时屏蔽自提模块

            /**

            if($data['delivery'] == 'pickup')

            {

            $verify_bool = true;

            $verifycode = 0;

            while($verify_bool)

            {

            $code  = (ceil(time()/100)+rand(10000000,40000000)).rand(1000,9999);

            $verifycode = $code ? $code : rand(100000,999999);

            $verifycode = str_replace('1989','9819',$verifycode);

            $verifycode = str_replace('1259','9521',$verifycode);

            $verifycode = str_replace('12590','95210',$verifycode);

            $verifycode = str_replace('10086','68001',$verifycode);



            $pick_order = M('pick_order')->where( array('pick_sn' => $verifycode) )->find();

            if(empty($pick_order))

            {

            $verify_bool = false;

            }

            }

            $pick_data = array();

            $pick_data['pick_sn'] = $verifycode;

            $pick_data['pick_id'] = $pick_up_id;

            $pick_data['order_id'] = $oid;

            $pick_data['state'] = 0;



            $pick_data['ziti_name'] = $ziti_name;

            $pick_data['ziti_mobile'] = $ziti_mobile;





            $pick_data['addtime'] = time();

            M('pick_order')->add($pick_data);

            }

             **/



            $order_ids_arr[] = $oid;

            //$pay_total = $pay_total + $order_total+$trans_free_toal - $data['voucher_credit'];



            if($dispatching == 'express' && $data['type'] != 'integral')

            {

                $pay_total = $pay_total + $order_total+$trans_free_toal - $data['voucher_credit']- $reduce_money - $data['score_for_money'];

            }

            else if( $dispatching == 'express' && $data['type'] == 'integral' )

            {

                $pay_total = $trans_free_toal;

            }

            else if('tuanz_send' == $dispatching){

                $pay_total = $pay_total + $order_total+$trans_free_toal - $data['voucher_credit'] -$reduce_money - $data['score_for_money'];

            }

            else{

                $pay_total = $pay_total + $order_total - $data['voucher_credit'] -$reduce_money - $data['score_for_money'];

            }







            $order_relate_data = array();



            $order_relate_data['order_all_id'] = $order_all_id;

            $order_relate_data['order_id'] = $oid;

            $order_relate_data['addtime'] = time();



            M('lionfish_comshop_order_relate')->add($order_relate_data);



        }



        $order_all_data = array();

        $order_all_data['total_money'] = $pay_total;



        M('lionfish_comshop_order_all')->where(  array('id' => $order_all_id) )->save($order_all_data);



        if($order_all_id){



            $order = M('lionfish_comshop_order')->where( array('order_id' => $oid) )->find();



            $member_info = M('lionfish_comshop_member')->field('we_openid,account_money')->where( array('member_id' => $member_id ) )->find();



            $is_yue_open = 0;



            $is_yue_open = D('Home/Front')->get_config_by_name('is_open_yue_pay');

            if( empty($is_yue_open) )

            {

                $is_yue_open = 0;

            }



            //检测是否需要扣除积分

            if($data['type'] == 'integral' && $del_integral> 0 && $is_integral == 1)

            {



                $order_goods_tp = M('lionfish_comshop_order_goods')->field('order_goods_id')->where( array('order_id' => $oid ) )->find();



                D('Admin/Member')->sendMemberPointChange($member_id,$del_integral, 1 ,'积分兑换商品', 'integral_exchange', $oid ,$order_goods_tp['order_goods_id']);

            }



            if( $order['type'] == 'ignore' || $pay_total<=0 || ($is_yue_open == 1 && $ck_yupay == 1 && $member_info['account_money'] >= $pay_total) )

            {

                /****

                //暂时关闭





                //检测是否需要扣除积分

                if($del_integral> 0 && $is_integral == 1)

                {

                $integral_model->charge_member_score( $member_id, $del_integral,'out', 'orderbuy', $oid);

                }



                 ***/



                if($ck_yupay == 1 && $pay_total >0 && $order['type'] != 'ignore')

                {

                    //开始余额支付

                    $member_charge_flow_data = array();

                    $member_charge_flow_data['formid'] = '';

                    $member_charge_flow_data['member_id'] = $member_id;

                    $member_charge_flow_data['trans_id'] = $oid;

                    $member_charge_flow_data['money'] = $pay_total;

                    $member_charge_flow_data['state'] = 3;

                    $member_charge_flow_data['charge_time'] = time();

                    $member_charge_flow_data['remark'] = '会员前台余额支付';

                    $member_charge_flow_data['add_time'] = time();



                    M('lionfish_comshop_member_charge_flow')->add($member_charge_flow_data);



                    $charge_flow_id = M('lionfish_comshop_member_charge_flow')->getLastInsID();







                    //开始处理扣钱

                    M('lionfish_comshop_member')->where( array('member_id' => $member_id) )->setInc('account_money',-$pay_total);



                    $mb_info = M('lionfish_comshop_member')->field('account_money')->where( array('member_id' =>$member_id ) )->find();



                    M('lionfish_comshop_member_charge_flow')->where( array('id' => $charge_flow_id ) )->save( array('operate_end_yuer' => $mb_info['account_money']) );



                }





                //lionfish_comshop_order_all can_yupay



                //开始处理订单状态

                //$order_all = M('order_all')->where( array('id' => $order_all_id) )->find();



                $order_all = M('lionfish_comshop_order_all')->where( array('id' => $order_all_id) )->find();





                if( !empty($order)  )

                {

                    //支付完成

                    $o = array();

                    $o['order_status_id'] =  $order['is_pin'] == 1 ? 2:1;

                    $o['paytime']=time();

                    $o['transaction_id'] = $transaction_id;



                    M('lionfish_comshop_order_all')->where( array('id' => $out_trade_no) )->save($o);



                    // ims_



                    $order_relate_list = M('lionfish_comshop_order_relate')->where( array('order_all_id' => $order_all['id']) )->select();



                    foreach($order_relate_list as $order_relate)

                    {

                        $order = M('lionfish_comshop_order')->where( array('order_id' => $order_relate['order_id'] ) )->find();



                        if( $order && $order['order_status_id'] == 3)

                        {

                            $o = array();

                            $o['payment_code'] = 'yuer';

                            $o['order_id']=$order['order_id'];

                            $o['order_status_id'] =  $order['is_pin'] == 1 ? 2:1;

                            $o['date_modified']=time();

                            $o['pay_time']=time();

                            $o['transaction_id'] = $is_integral ==1? '积分兑换':'余额支付';



                            M('lionfish_comshop_order')->where( array('order_id' => $order['order_id'] ) )->save($o);





                            //暂时屏蔽



                            $kucun_method = D('Home/Front')->get_config_by_name('kucun_method');



                            if( empty($kucun_method) )

                            {

                                $kucun_method = 0;

                            }



                            if($kucun_method == 1)

                            {//支付完减库存，增加销量



                                $order_goods_list = M('lionfish_comshop_order_goods')->where( array('order_id' => $order['order_id']) )->select();



                                foreach($order_goods_list as $order_goods)

                                {

                                    D('Home/Pingoods')->del_goods_mult_option_quantity($order['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity'],1);



                                }

                            }



                            $oh = array();

                            $oh['order_id']=$order['order_id'];

                            $oh['order_status_id']= $order['is_pin'] == 1 ? 2:1;

                            $oh['comment']='买家已付款';

                            $oh['date_added']=time();

                            $oh['notify']=1;



                            M('lionfish_comshop_order_history')->add($oh);





                            //发送购买通知

                            //TODO 先屏蔽，等待调试这个消息

                            D('Home/Weixinnotify')->orderBuy($order['order_id'], true);

                            if($order['is_pin'] == 1)

                            {



                                $pin_order = M('lionfish_comshop_pin_order')->where( array('order_id' =>$order['order_id'] ) )->find();



                                D('Home/Pin')->insertNotifyOrder($order['order_id']);



                                $is_pin_success = D('Home/Pin')->checkPinSuccess($pin_order['pin_id']);



                                if($is_pin_success) {

                                    //todo send pintuan success notify

                                    D('Home/Pin')->updatePintuanSuccess($pin_order['pin_id']);

                                }





                            }

                        }



                    }

                    //返回支付成功给app

                    $data = array();

                    $data['code'] = 0;

                    $data['has_yupay'] = 1;

                    $data['is_integral'] = $is_integral;

                    $data['is_spike'] = $is_spike;

                    $data['is_go_orderlist'] = $is_just_1;

                    $data['order_id'] = $oid;

                    $data['order_all_id'] = $order_all_id;



                    echo json_encode($data);

                    die();

                }



            }

            else if($member_id == 1164 && false)

            {

                //......

                // https://api.liantuofu.com/open/precreate





                $post_url = "https://api.liantuofu.com/open/precreate";



                $pay_key = '42ce7d4c703052761bc6615ba9a85f26';



                $appId = 'EW_N0773331642';

                $merchantCode = 'EW_N2907654350';

                $outTradeNo = $order_all_id.'-'.time();

                $totalAmount = $pay_total;



                $random = mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9);



                $channel = 'WXPAY';

                $tradeType = 'MINIAPP';



                $shop_domain = D('Home/Front')->get_config_by_name('shop_domain');



                $notifyUrl = $shop_domain.'/notify.php';



                $openId = 'oHucvv64jHMx6hXbPRIdI2FXI_i4';

                $subAppId = D('Home/Front')->get_config_by_name('wepro_appid');



                $post_data = array();

                $post_data['appId'] = $appId;

                $post_data['channel'] = $channel;



                $post_data['merchantCode'] = $merchantCode;

                $post_data['notifyUrl'] = $notifyUrl;

                $post_data['openId'] = $openId;

                $post_data['outTradeNo'] = $outTradeNo;

                $post_data['random'] = $random;

                $post_data['subAppId'] = $subAppId;

                $post_data['totalAmount'] = $totalAmount;

                $post_data['tradeType'] = $tradeType;



                ksort($post_data);



                //string(43) "{"code":"FAILED","msg":"appId 不能为空"}"



                $sign = sign($post_data,$pay_key);



                $sign = strtolower($sign);



                $post_data['sign'] = $sign;



                //post_url

                $array = $this->requestAsHttpPOST($post_data, $post_url);



     

            }

            else{



                $fee = $pay_total;

                $appid = D('Home/Front')->get_config_by_name('wepro_appid');



                $body =  $pay_goods_name;//'商品购买';



                $body = mb_substr($body,0,32,'utf-8');



                if( empty($body) )

                {

                    $body = '商品购买';

                }





                $mch_id =       D('Home/Front')->get_config_by_name('wepro_partnerid');



                $shop_domain = D('Home/Front')->get_config_by_name('shop_domain');



                $nonce_str =    nonce_str();

                $notify_url =   $shop_domain.'/notify.php';





                $openid =       $payment['we_openid'];

                $out_trade_no = $order_all_id.'-'.time();



                //out_trade_no

                M('lionfish_comshop_order_all')->where( array('id' => $order_all_id ) )->save( array('out_trade_no' => $out_trade_no ) );





                $spbill_create_ip = $_SERVER['REMOTE_ADDR'];

                $total_fee =    $fee*100;

                $trade_type = 'JSAPI';

                $pay_key = D('Home/Front')->get_config_by_name('wepro_key');



                $post['appid'] = $appid;

                $post['body'] = $body;

                $post['mch_id'] = $mch_id;

                $post['nonce_str'] = $nonce_str;

                $post['notify_url'] = $notify_url;



                $post['openid'] = $openid;

                $post['out_trade_no'] = $out_trade_no;

                $post['spbill_create_ip'] = $spbill_create_ip;

                $post['total_fee'] = $total_fee;

                $post['trade_type'] = $trade_type;

                $sign = sign($post,$pay_key);





                $post_xml = '<xml>

				   <appid>'.$appid.'</appid>

				   <body>'.$body.'</body>

				   <mch_id>'.$mch_id.'</mch_id>

				   <nonce_str>'.$nonce_str.'</nonce_str>

				   <notify_url>'.$notify_url.'</notify_url>

				   <openid>'.$openid.'</openid>

				   <out_trade_no>'.$out_trade_no.'</out_trade_no>

				   <spbill_create_ip>'.$spbill_create_ip.'</spbill_create_ip>

				   <total_fee>'.$total_fee.'</total_fee>

				   <trade_type>'.$trade_type.'</trade_type>

				   <sign>'.$sign.'</sign>

				</xml> ';

                $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

                $xml = http_request($url,$post_xml);

                $array = xml($xml);



                if($array['RETURN_CODE'] == 'SUCCESS' && $array['RESULT_CODE'] == 'SUCCESS'){

                    $time = time();

                    $tmp=array();

                    $tmp['appId'] = $appid;

                    $tmp['nonceStr'] = $nonce_str;

                    $tmp['package'] = 'prepay_id='.$array['PREPAY_ID'];

                    $tmp['signType'] = 'MD5';

                    $tmp['timeStamp'] = "$time";



                    $prepay_id = (string)$array['PREPAY_ID'];





                    M('lionfish_comshop_order')->where( array('order_id' => array('in', $order_ids_arr)) )->save( array('perpay_id' => $prepay_id) );







                    //M('order')->where( array('order_id' => array('in',$order_ids_arr) ) )->save( array('perpay_id' => (string)$array['PREPAY_ID']) );

                    $data = array();

                    $data['code'] = 0;

                    $data['appid'] = $appid;

                    $data['timeStamp'] = "$time";

                    $data['nonceStr'] = $nonce_str;

                    $data['signType'] = 'MD5';

                    $data['package'] = 'prepay_id='.$array['PREPAY_ID'];

                    $data['paySign'] = sign($tmp,$pay_key);

                    $data['out_trade_no'] = $out_trade_no;



                    $data['is_go_orderlist'] = $is_just_1;



                    if($is_pin == 1)

                    {

                        $data['redirect_url'] = '../groups/group?id='.$oid.'&is_show=1';

                    } else {

                        $data['redirect_url'] = '../orders/order_show_all?order_all_id=' + $order_all_id;

                    }



                }else{

                    $data = array();

                    $data['code'] = 1;

                    $data['text'] = "错误";

                    $data['RETURN_CODE'] = $array['RETURN_CODE'];

                    $data['RETURN_MSG'] = $array['RETURN_MSG'];

                }

                $data['has_yupay'] = 0;

            }



            if($is_pin == 1)

            {

                $data['order_id'] = $oid;

                $data['order_all_id'] = $order_all_id;

            }else{

                $data['order_id'] = $oid;

                $data['order_all_id'] = $order_all_id;

            }

            $data['is_go_orderlist'] = $is_just_1;



            $data['is_spike'] = $is_spike;
			 file_put_contents(ROOT_PATH.'Runtime/Logs/wx/zhifu.log',date('Y/m/d H:i:s')."---参数---".json_encode($data).PHP_EOL, FILE_APPEND);
            echo json_encode($data);

            die();

        }else{

            echo json_encode( array('code' =>1,'order_all_id' =>$order_all_id) );

            die();

        }



    }



    function requestAsHttpPOST($data, $service_url){

        $HTTP_TIME_OUT= "20";

        ksort(array_filter($data)); //删除数组中的空值并排序

        $post_data = http_build_query($data);



        $options = array(

            'http' => array(

                'method'  => 'POST',

                'header'  => 'Content-type:application/x-www-form-urlencoded;charset=MD5',

                'content' => $post_data,

                'timeout' => $HTTP_TIME_OUT * 1000 //超时时间,*1000将毫秒变为秒（单位:s）

            )

        );

        $context = stream_context_create($options);

        $result = file_get_contents($service_url, false, $context);

        return $result;

    }





    /**

    微信充值

     **/

    public function wxcharge()

    {

        $_GPC = I('request.');



        $token = $_GPC['token'];





        $weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();



        $member_id = $weprogram_token['member_id'];



        if( empty($member_id) )

        {

            echo json_encode( array('code' =>1,'msg' =>'未登录') );

            die();

        }



        $money = $_GPC['money'];



        $rech_id = isset($_GPC['rech_id']) && $_GPC['rech_id'] > 0 ? $_GPC['rech_id'] : 0;







        $member_info = M('lionfish_comshop_member')->field('we_openid')->where( array('member_id' => $member_id) )->find();



        $give_money = 0;



        if( $rech_id > 0 )

        {

            $rech_info = M('lionfish_comshop_chargetype')->where( array('id' => $rech_id ) )->find();



            if( !empty($rech_info) )

            {

                $give_money = $rech_info['send_money'];

            }



            $money = $rech_info['money'];

        }







        $member_charge_flow_data = array();

        $member_charge_flow_data['member_id'] = $member_id;

        $member_charge_flow_data['money'] = $money;

        $member_charge_flow_data['state'] = 0;

        $member_charge_flow_data['give_money'] = $give_money;

        $member_charge_flow_data['charge_time'] = 0;

        $member_charge_flow_data['remark'] = '会员前台微信充值';

        $member_charge_flow_data['add_time'] = time();



        $order_id = M('lionfish_comshop_member_charge_flow')->add( $member_charge_flow_data );





        $shop_domain = D('Home/Front')->get_config_by_name('shop_domain');











        $fee = $money;

        $appid = D('Home/Front')->get_config_by_name('wepro_appid');

        $body =         '会员充值';

        $mch_id =      D('Home/Front')->get_config_by_name('wepro_partnerid');

        $nonce_str =    nonce_str();

        $notify_url =   $shop_domain.'/notify.php';

        $openid =       $member_info['we_openid'];

        $out_trade_no = $order_id.'-'.time().'-charge-'.$id;

        $spbill_create_ip = $_SERVER['REMOTE_ADDR'];

        $total_fee =    $fee*100;

        $trade_type = 'JSAPI';

        $pay_key = D('Home/Front')->get_config_by_name('wepro_key');



        $post = array();



        $post['appid'] = $appid;

        $post['body'] = $body;

        $post['mch_id'] = $mch_id;

        $post['nonce_str'] = $nonce_str;

        $post['notify_url'] = $notify_url;

        $post['openid'] = $openid;

        $post['out_trade_no'] = $out_trade_no;

        $post['spbill_create_ip'] = $spbill_create_ip;

        $post['total_fee'] = $total_fee;

        $post['trade_type'] = $trade_type;



        $sign = sign($post,$pay_key);



        //sign()

        $post_xml = '<xml>

			   <appid>'.$appid.'</appid>

			   <body>'.$body.'</body>

			   <mch_id>'.$mch_id.'</mch_id>

			   <nonce_str>'.$nonce_str.'</nonce_str>

			   <notify_url>'.$notify_url.'</notify_url>

			   <openid>'.$openid.'</openid>

			   <out_trade_no>'.$out_trade_no.'</out_trade_no>

			   <spbill_create_ip>'.$spbill_create_ip.'</spbill_create_ip>

			   <total_fee>'.$total_fee.'</total_fee>

			   <trade_type>'.$trade_type.'</trade_type>

			   <sign>'.$sign.'</sign>

			</xml> ';

        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

        $xml = http_request($url,$post_xml);

        $array = xml($xml);

        if($array['RETURN_CODE'] == 'SUCCESS' && $array['RESULT_CODE'] == 'SUCCESS'){

            $time = time();

            $tmp= array();

            $tmp['appId'] = $appid;

            $tmp['nonceStr'] = $nonce_str;

            $tmp['package'] = 'prepay_id='.$array['PREPAY_ID'];

            $tmp['signType'] = 'MD5';

            $tmp['timeStamp'] = "$time";



            M('lionfish_comshop_member_charge_flow')->where( array('id' => $order_id) )->save( array('formid' => $array['PREPAY_ID'] ) );



            $data['code'] = 0;

            $data['timeStamp'] = "$time";

            $data['nonceStr'] = $nonce_str;

            $data['signType'] = 'MD5';

            $data['package'] = 'prepay_id='.$array['PREPAY_ID'];

            $data['paySign'] =   sign($tmp, $pay_key);

            $data['out_trade_no'] = $out_trade_no;



            $data['redirect_url'] = '../dan/me';



        }else{

            $data['code'] = 1;

            $data['text'] = "错误";

            $data['RETURN_CODE'] = $array['RETURN_CODE'];

            $data['RETURN_MSG'] = $array['RETURN_MSG'];

        }




		
		

        echo json_encode($data);

        die();



    }





    public function wxpay()

    {

        $gpc = I('request.');



        $token = $gpc['token'];

        $order_id = $gpc['order_id'];





        $weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();





        $member_id = $weprogram_token['member_id'];





        if( empty($member_id) )

        {

            echo json_encode( array('code' =>1,'msg' =>'未登录') );

            die();

        }





        $member_info = M('lionfish_comshop_member')->field('we_openid')->where( array('member_id' => $member_id) )->find();





        $order = M('lionfish_comshop_order')->where( array('order_id' => $order_id ) )->find();





        //order_status_id



        if( $order['order_status_id'] != 3)

        {

            $json = array();



            $json['msg']='商品已下架！';

            $json['code'] = 2;

            if($order['order_status_id'] == 1)

            {

                $json['msg']='订单已付款，请勿重新付款!';

            }

            else if( $order['order_status_id'] == 5){

                $json['msg']='订单已取消，请重新选择商品下单!';

            }

            echo json_encode($json);

            die();

        }







        //检测商品是否下架 begin

        $sql = "select name,quantity,rela_goodsoption_valueid,goods_id from ".C('DB_PREFIX')."lionfish_comshop_order_goods  

					where order_id={$order_id} ";



        $order_goods_list = M()->query($sql);



        foreach($order_goods_list as $tp_val)

        {

            $tp_gd_info = M('lionfish_comshop_goods')->field('grounding')->where( array('id' => $tp_val['goods_id'] ) )->find();



            if( empty($tp_gd_info) || $tp_gd_info['grounding'] != 1 )

            {

                $json['code'] = 2;



                $json['msg']='商品已下架！';



                echo json_encode($json);

                die();

            }

        }



        //检测商品是否下架end





        //检测是否已经支付过了begin



        $order_relate_info = M('lionfish_comshop_order_relate')->where( array('order_id' => $order_id ) )->order('id desc')->find();



        if( !empty($order_relate_info) && $order_relate_info['order_all_id'] > 0 )

        {

            $order_all_info = M('lionfish_comshop_order_all')->where( array('id' => $order_relate_info['order_all_id'] ) )->find();



            if( !empty($order_all_info) && !empty($order_all_info['out_trade_no']) )

            {



                $out_trade_no = $order_all_info['out_trade_no'];



                $appid =  D('Home/Front')->get_config_by_name('wepro_appid');

                $mch_id =      D('Home/Front')->get_config_by_name('wepro_partnerid');

                $nonce_str =    nonce_str();



                $pay_key = D('Home/Front')->get_config_by_name('wepro_key');





                $post = array();

                $post['appid'] = $appid;

                $post['mch_id'] = $mch_id;

                $post['nonce_str'] = $nonce_str;

                $post['out_trade_no'] = $out_trade_no;



                $sign = sign($post,$pay_key);



                $post_xml = '<xml>

							   <appid>'.$appid.'</appid>

							   <mch_id>'.$mch_id.'</mch_id>

							   <nonce_str>'.$nonce_str.'</nonce_str>

							   <out_trade_no>'.$out_trade_no.'</out_trade_no>

							   <sign>'.$sign.'</sign>

							</xml>';



                $url = "https://api.mch.weixin.qq.com/pay/orderquery";



                $result = http_request($url,$post_xml);



                $array = xml($result);



                if( $array['RETURN_CODE'] == 'SUCCESS' && $array['RETURN_MSG'] == 'OK' )

                {

                    if( $array['TRADE_STATE'] == 'SUCCESS' )

                    {

                        $json = array();



                        $json['msg']='商品已下架！';

                        $json['code'] = 2;

                        $json['msg']='订单已付款，请勿重新付款，请刷新页面!';

                        echo json_encode($json);

                        die();

                    }

                }



            }

        }



        //检测是否已经支付过了end



        //支付才减库存，才需要判断

        $kucun_method = D('Home/Front')->get_config_by_name('kucun_method');



        if( empty($kucun_method) )

        {

            $kucun_method = 0;

        }



        if($kucun_method == 1)

        {

            /*** 检测商品库存begin  **/









            //goods_id

            foreach($order_goods_list as $val)

            {

                $quantity = $val['quantity'];



                $goods_id = $val['goods_id'];



                $can_buy_count = D('Home/Front')->check_goods_user_canbuy_count($member_id, $goods_id);



                $goods_description = D('Home/Front')->get_goods_common_field($goods_id , 'total_limit_count');



                if($can_buy_count == -1)

                {

                    $json['code'] = 2;



                    $json['msg']='您还能购买'.$goods_description['total_limit_count'].'个';



                    echo json_encode($json);

                    die();

                }else if($can_buy_count >0 && $quantity >$can_buy_count)

                {

                    $json['code'] = 2;

                    $json['msg']='您还能购买'.$can_buy_count.'份';

                    echo json_encode($json);

                    die();

                }

                //rela_goodsoption_valueid

                if(!empty($val['rela_goodsoption_valueid']))

                {

                    $mul_opt_arr = array();



                    //ims_



                    $goods_option_mult_value = M('lionfish_comshop_goods_option_item_value')->where( array('goods_id' => $goods_id,'option_item_ids' => $val['rela_goodsoption_valueid']) )->find();





                    if( !empty($goods_option_mult_value) )

                    {

                        if($goods_option_mult_value['stock']<$quantity){

                            $json['code'] =2;

                            $json['msg']='商品数量不足，剩余'.$goods_option_mult_value['stock'].'个！！';

                            echo json_encode($json);

                            die();

                        }

                    }

                }



            }

            /*** 检测商品库存end **/

        }



        $pin_order = array();

        if( !empty($pin_order) )

        {

            /**

            $pin_model =  load_model_class('pin');

            $is_pin_over = $pin_model->getNowPinState($pin_order['pin_id']);

            if($is_pin_over != 0)

            {

            pdo_query("delete from ".tablename('lionfish_comshop_pin_order')." where order_id = {$order_id} ");



            pdo_query("delete from ".tablename('lionfish_comshop_pin')." where pin_id = ".$pin_order['pin_id']." and order_id = ".$order_id);



            $order_goods_info = pdo_fetch("select goods_id from ".tablename('lionfish_comshop_order_goods')." where uniacid=:uniacid and order_id=:order_id ", array(':order_id' => $order_id,':uniacid' => $_W['uniacid']));



            //新开团



            $pin_id = $pin_model->openNewTuan($order_id,$order_goods_info['goods_id'],$member_id);

            //插入拼团订单

            $pin_model->insertTuanOrder($pin_id,$order_id);



            }

             **/

        }





        //单独支付一个店铺的订单

        //M('lionfish_comshop_order_relate')->where( array('order_id' => $order_id) )->delete();



        $order_all_data = array();

        $order_all_data['member_id'] = $member_id;

        $order_all_data['order_num_alias'] = build_order_no($member_id);

        $order_all_data['transaction_id'] = '';

        $order_all_data['order_status_id'] = 3;

        $order_all_data['is_pin'] = $order['is_pin'];

        $order_all_data['paytime'] = 0;

        $order_all_data['total_money'] = $order['total']+ $order['shipping_fare']-$order['voucher_credit']-$order['fullreduction_money'];

        $order_all_data['addtime'] = time();



        $order_all_id = M('lionfish_comshop_order_all')->add($order_all_data);



        $order_relate_data = array();

        $order_relate_data['order_all_id'] = $order_all_id;

        $order_relate_data['order_id'] = $order_id;

        $order_relate_data['addtime'] = time();



        M('lionfish_comshop_order_relate')->add($order_relate_data);





        if( $order['delivery'] == 'pickup' )

        {

            $fee = $order['total']+ $order['shipping_fare']-$order['voucher_credit']-$order['fullreduction_money'] - $order['score_for_money'];

        }else {

            $fee = $order['total']+ $order['shipping_fare']-$order['voucher_credit']-$order['fullreduction_money'] - $order['score_for_money'];

        }







        $appid = D('Home/Front')->get_config_by_name('wepro_appid');





        $body =  $pay_goods_name;//'商品购买';



        $body = mb_substr($body,0,32,'utf-8');



        if( empty($body) )

        {

            $body =         '商品购买';

        }





        $mch_id =       D('Home/Front')->get_config_by_name('wepro_partnerid');

        $nonce_str =    nonce_str();



        $shop_domain = D('Home/Front')->get_config_by_name('shop_domain');



        $notify_url =    $shop_domain.'/notify.php';



        $openid =       $member_info['we_openid'];

        $out_trade_no = $order_all_id.'-'.time();

        $spbill_create_ip = $_SERVER['REMOTE_ADDR'];

        $total_fee =    $fee*100;

        $trade_type = 'JSAPI';

        $pay_key = D('Home/Front')->get_config_by_name('wepro_key');





        $post = array();

        $post['appid'] = $appid;

        $post['body'] = $body;

        $post['mch_id'] = $mch_id;

        $post['nonce_str'] = $nonce_str;

        $post['notify_url'] = $notify_url;

        $post['openid'] = $openid;

        $post['out_trade_no'] = $out_trade_no;

        $post['spbill_create_ip'] = $spbill_create_ip;

        $post['total_fee'] = $total_fee;

        $post['trade_type'] = $trade_type;

        $sign = sign($post,$pay_key);





        $post_xml = '<xml>

			   <appid>'.$appid.'</appid>

			   <body>'.$body.'</body>

			   <mch_id>'.$mch_id.'</mch_id>

			   <nonce_str>'.$nonce_str.'</nonce_str>

			   <notify_url>'.$notify_url.'</notify_url>

			   <openid>'.$openid.'</openid>

			   <out_trade_no>'.$out_trade_no.'</out_trade_no>

			   <spbill_create_ip>'.$spbill_create_ip.'</spbill_create_ip>

			   <total_fee>'.$total_fee.'</total_fee>

			   <trade_type>'.$trade_type.'</trade_type>

			   <sign>'.$sign.'</sign>

			</xml> ';

        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

        $xml = http_request($url,$post_xml);

        $array = xml($xml);

        if($array['RETURN_CODE'] == 'SUCCESS' && $array['RESULT_CODE'] == 'SUCCESS'){

            $time = time();

            $tmp= array();

            $tmp['appId'] = $appid;

            $tmp['nonceStr'] = $nonce_str;

            $tmp['package'] = 'prepay_id='.$array['PREPAY_ID'];

            $tmp['signType'] = 'MD5';

            $tmp['timeStamp'] = "$time";



            $prepay_id = (string)$array['PREPAY_ID'];



            M('lionfish_comshop_order')->where( array('order_id' => $order_id) )->save( array('perpay_id' => $prepay_id) );





            $data['code'] = 0;

            $data['timeStamp'] = "$time";

            $data['nonceStr'] = $nonce_str;

            $data['signType'] = 'MD5';

            $data['package'] = 'prepay_id='.$array['PREPAY_ID'];

            $data['paySign'] = sign($tmp, $pay_key);

            $data['out_trade_no'] = $out_trade_no;

            $data['is_pin'] = $order['is_pin'];



            if($order['is_pin'] == 1)

            {

                $data['redirect_url'] = '../groups/group?id='.$order_id.'&is_show=1';

            } else {

                $data['redirect_url'] = '../orders/order?id=' + $order_id;

            }



        }else{

            $data['code'] = 1;

            $data['text'] = "错误";

            $data['RETURN_CODE'] = $array['RETURN_CODE'];

            $data['RETURN_MSG'] = $array['RETURN_MSG'];

        }





        echo json_encode($data);

        die();

    }



    /**

     * 获取购物车总数

     */

    public function count() {



        $gpc = I('request.');



        $data = array();

        $token = $gpc['token'];

        $community_id = $gpc['community_id'];



        $cart= D('Home/Car');

        $total=$cart->count_goodscar($token, $community_id);



        $data['code'] = 0;

        $data['data'] = $total;

        echo json_encode($data);

        die();



    }
    /** 
     * 判断是否是超过时间提示
     * @Author: flydreame 
     * @Date: 2020-05-26 14:50:02 
     * buy_type 下单类型 dan 普通 pindan pintuan 拼团
     * token 客户端token,会员身份
     * community_id 团长id
     * soli_id 
     * @Desc:  
     */
    public function is_overtime(){
        $gpc = I('request.');
        $buy_type = isset($gpc['buy_type']) ? $gpc['buy_type'] : 'dan';
        $token = $gpc['token'];
        $community_id = $gpc['community_id'];


        $cart = D('Home/Car');
        $goods=$cart->get_all_goodswecar($buy_type, $token,1,$community_id);
        $one = '';
        $two = '';
        $Three = '';
        $onenum = 0;
        $twonum = 0;
        $Threenum = 0;
        foreach($goods as  $k=>$v){
           //2020年6月17日 修改规整 超过设置时间往后延迟一天
           $tp_goods_info = M('lionfish_comshop_goods')->field('pick_start_time,pick_end_time')->where( array('id' => $v['goods_id']) )->find();
           $goods_info = M('lionfish_comshop_good_common')->field('pick_up_type,pick_up_modify')->where( array('goods_id' => $v['goods_id']) )->find();
           $pick_up_type = $goods_info['pick_up_type'];
            if(mb_strlen($v['name'])>11){
                $v['name']=  mb_substr($v['name'],0,10).'…';
            }

           $dataz=date('H:i');//现在时间
           $weekarray=array("日","一","二","三","四","五","六");
           if(strtotime($dataz)>strtotime($tp_goods_info['pick_end_time']) && $pick_up_type != 3 && $v['is_only_express'] != 1 && $v['song_type'] != 3){
               switch ($pick_up_type) {
                   case '0'://当日
                        $tomorrow_one=date("m-d", time()+86400);
                        if($one){
                            $one .= '、';
                        }
                        if($onenum >2){
                            $one .= '等';
                        }else{
                            $one .= $v['name'];
                            $onenum ++ ;
                        }
                        break;
                   case '1'://次日
                        $tomorrow_two=date("m-d", time()+86400*2);
                        if($two){
                            $two .= '、';
                        }
                        if($twonum >2){
                            $two .= '等';
                        }else{
                            $two .= $v['name'];
                            $twonum ++ ;
                        }
                        break;
                   case '2'://隔日
                        $tomorrow_Three=date("m-d", time()+86400*3);
                        if($Three){
                            $Three .= '、';
                        }
                        if($Threenum >2){
                            $Three .= '等';
                        }else{
                            $Three .= $v['name'];
                            $Threenum ++;
                        }
                        break;
               }
           }
        }
        $d_goods['is_delivery_tips']=0;
        $d_goods['delivery_tips']='';
        $tips['delivery_tips'] ='';
        // if($one || $two || $Three ){
        //     $tips['delivery_tips'] .='您加购的
        //     ';
        // }
        if($one){
            $tips['is_delivery_tips']=1;
            $tips['delivery_tips'] .= $one.'商品,预计'.$tomorrow_one.'（周'.$weekarray[date("w",time()+86400)].')可自提
            ';
        }
        if($two){
            $tips['is_delivery_tips']=1;
            $tips['delivery_tips'] .= $two.'商品,预计'.$tomorrow_two.'（周'.$weekarray[date("w",time()+86400*2)].')可自提
            ';
        }
        if($Three){
            $tips['is_delivery_tips']=1;
            $tips['delivery_tips']  .=  $Three.'商品,预计'.$tomorrow_Three.'（周'.$weekarray[date("w",time()+86400*3)].')可自提';;
        }
        $data['code'] = 0;
        $data['data'] = $tips;
        echo json_encode($data);
    }
}