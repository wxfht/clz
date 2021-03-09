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

class UpgradeController extends \Think\Controller {
    
    function __construct()
    {
       parent::__construct();
	    
		
		
		//单商户服务号更新
        $domain_list = array();
        $domain_list[] = 'shop.kuailegongyi.org';
        $domain_list[] = 'tg.zazhipu.com';
        $domain_list[] = 'yunyuxiaozhu.com';
		
		
        //小程序后台更新
		$domain_weprog_list = array();
		$domain_weprog_list[] = 'pintuan.js-css.cn';
		$domain_weprog_list[] = 'feiniao.fanhouwan.com';
		$domain_weprog_list[] = 'xingfu.shiziyu888.com';
		$domain_weprog_list[] = 'chanelyumo.liofis.com';
		$domain_weprog_list[] = 'zmr031.com';
		$domain_weprog_list[] = 'tg.zazhipu.com';
		$domain_weprog_list[] = 'wx.oufeizhifa.com';
		$domain_weprog_list[] = 'pinchufang.yaoshiji.com.cn';
		$domain_weprog_list[] = 'chanelyumo.liofis.com';
		$domain_weprog_list[] = 'yirentuan.shiziyu888.com';
		
		
		//既有服务号，又有多商户版本的客户
		
		$domain_all_list = array();
		$domain_all_list[] = 'mall.shiziyu888.com';
		
		//两个号都有的人
        $all_list = array(
							'V3.7' =>array('name'=>'b1.zip','desc'=>'初始版本',),
							'V3.8' =>array('name'=>'b1.zip','desc'=>'初始版本',),
						);
		//多商户更新内容
        $banben_list = array(
							'V3.7' =>array('name'=>'b1.zip','desc'=>'初始版本',),
							'V3.8' =>array('name'=>'path_20180905_danfuwuV3.8.zip','desc'=>'
							<br/>V3.8
							<br/>【优化】自营后台，订单各种状态可变更，
							<br/>【优化】优化首页商品加载数据
							<br/>【优化】优化拼团页面广告
							<br/>【优化】修复 我的喜欢列表 取消喜欢
							<br/>【优化】优化随机商品，去除 砍价 积分兑换商品
							<br/>【优化】优化我的积分页面
							<br/>【优化】0积分兑换购物车商品
							<br/>【优化】砍价页面随机商品 价格等显示（不显示积分与砍价商品）
							<br/>【优化】统一积分说明页面
							',
							),
						);
		//小程序更新内容				
        $weprog_banben_list = array(
							'V1.7' =>array('name'=>'w1.zip','desc'=>'初始版本',
							),
							'V2.8' =>array('name'=>'path_20180313_V2.8.zip','desc'=>'
								<br/>V2.8
								<br/>【新增】商品详情页，分享按钮，
								<br/>【新增】商品详情页底部猜你喜欢
								<br/>【优化】倒计时为0，仅可以单独购买
								<br/>【优化】拼团详情页，划线价
								<br/>【优化】后台优惠券列表，加 每人最多领取显示 
							'),
							'V2.9' =>array('name'=>'path_20180323_V2.9.zip','desc'=>'
								<br/> 2.9
								<br/>优化
							'),
							'V3.0' =>array('name'=>'path_20180402_V3.0.zip','desc'=>'
								<br/>
								<br/>V3.0
								<br/>【新增】拼团管理，删除拼团商品，正在进行中的拼团活动立即结束
								<br/>【优化】排行，新品，广告位独立
								<br/>【优化】广告后台显示优化
								<br/>【优化】分销功能开关    
							'),
							'V3.1' =>array('name'=>'path_20180514_V3.1.zip','desc'=>'
								<br/>
								<br/>V3.1
								<br/>【新增】首页样式，购物车，分类搜索。
								<br/>【新增】更改授权登录流程
								<br/>【优化】小程序页面一级页面、二级页面切换方式
								<br/>【优化】团长折扣订单显示
								<br/>【优化】自动确认收货方式，按照发货时间来计算

								<br/>本次修改小程序utils/utils.js 文件，上传小程序时，需要更改里面的配置域名
							'),
							'V3.2' =>array('name'=>'path_20180524_V3.2.zip','desc'=>'
								<br/>V3.2
							<br/>【新增】分享方式，分享到好友+生成个人商品二维码到相册，带分销参数
							<br/>【新增】在线客服不在线时自动回复，24小时后访问在线客服首次自动回复
							<br/>【新增】小程序视频模块
							<br/>【优化】商品详情优化
							<br/>本次修改小程序utils/utils.js 文件，上传小程序时，需要更改里面的配置域名
							'),
							'V3.3' =>array('name'=>'path_20180607_V3.3.zip','desc'=>'
								<br/>V3.3
								<br/>【优化】首页普通商品加载
								<br/>【优化】首页广告轮播图，高度自适应
								<br/>【优化】商品详情页轮播图高度自适应，图片保真
								<br/>【优化】普通商品购买未登录授权方式。
								<br/>【优化】我的拼团底部菜单
								<br/>【优化】佣金提现页面。提示方式
							'),
							'V3.4' =>array('name'=>'path_20180610_V3.4.zip','desc'=>'
								<br/>V3.4
								<br/>【优化】后台商品编辑，图片上传，规格图片、轮播图片删除
								<br/>【优化】后台商品分类，删除逻辑。当该分类下面有商品时，商品下架
							'),
							'V3.5' =>array('name'=>'path_2018613_V3.5.zip','desc'=>'
								<br/>V3.5
								<br/>【优化】商品详情页图片无缝展示
								<br/>【优化】分类页面商品单价
								<br/>【优化】分类页面底部菜单跳转
								<br/>【优化】下单运费跟自提免运费
							'),
							'V3.6' =>array('name'=>'path_20180622_V3.6.zip','desc'=>'
								<br/>V3.6
								<br/>【新增】砍价模块
								<br/>【优化】小程序首页广告位2图、单图排列
								<br/>【优化】普通商品显示间距
								<br/>【优化】未登录时点击普通商品
								<br/>【优化】商品分类页面
								<br/>【优化】后台广告位提示
								<br/>【优化】后台商品分类显示
							'),
							'V3.7' =>array('name'=>'path_20180718_V3.7.zip','desc'=>'
								<br/>V3.6
								<br/>【新增】砍价列表，新增砍价规则
								<br/>【新增】分类、搜索页面新增与优化
								<br/>【优化】订单列表，拼团列表，分割线加粗
								<br/>【优化】首页顶部分类自动加载分页内容
								<br/>【优化】购物车页面，删除商品逻辑
								<br/>【优化】排行、新品页面支持关联到所有广告位
								<br/>【优化】统一后台商品编辑图片，简化图片上传
								<br/>【优化】后台订单搜索优化
								<br/>【优化】小程序搜索列表页，排序优化
								<br/>【优化】砍价地址优化
								<br/>【优化】后台订单详情显示细节优化
								<br/>【优化】前端猜你喜欢商品改为拼团+普通商品，页面切换回到本页面继续随机变换猜你喜欢商品（商品详情页+购物车页面 +会员中心）
							'),
							'V3.8' =>array('name'=>'path_20180822_V3.8.zip','desc'=>'
								<br/>V3.8
								<br/>【新增】积分商城模块
								<br/>【新增】超级团长海报（小程序二维码）
								<br/>【新增】后台添加商品虚拟评价
								<br/>【新增】前端颜色模板选择
								<br/>【新增】后台增加下级查看列表
								<br/>【新增】会员中心增加常用帮助，详细页
								<br/>【修复】优惠券领取
								<br/>【修复】商品库存扣除、回退
								<br/>【优化】砍价模块分享后价格同步
								<br/>【优化】小程序页面标题
								<br/>【优化】后台商品快捷上下架
							')
						);
		
		$this->domain_weprog_list = $domain_weprog_list;
		$this->weprog_banben_list = $weprog_banben_list;
		
        $this->domain_list = $domain_list;
        $this->banben_list = $banben_list;
		
		$this->all_list = $all_list;
        $this->domain_all_list = $domain_all_list;
    }
    /**
		获取不同类型的域名
	**/
	public function get_type_domain( $type  )
	{
		$domain_list = array();
		switch( $type )
		{
			case 'all':
				$domain_list = $this->domain_all_list;
			break;
			case 'mall':
				$domain_list = $this->domain_list;
			break;
			case 'weprog':
				$domain_list = $this->domain_weprog_list;
			break;
		}
		return $domain_list;
	}
	/**
		获取不同类型的版本
	**/
	public function get_type_banben( $type  )
	{
		$banben_list = array();
		switch( $type )
		{
			case 'all':
				$banben_list = $this->all_list;
			break;
			case 'mall':
				$banben_list = $this->banben_list;
			break;
			case 'weprog':
				$banben_list = $this->weprog_banben_list;
			break;
		}
		return $banben_list;
	}
    
    public function down_version_file()
    {
        $version = trim(I('get.version'));
		$type = trim(I('get.type','weprog'));
        $host = base64_decode(I('get.host'));
       
		$domain_list = $this->get_type_domain( $type  );
		
		
        
        if(!in_array($host,$domain_list))
        {
            $data = array();
            $data['domain'] = $host;
            $data['add_time'] = time();
            M('bad_domain')->add($data);
            die('-');
        }
        
		$banben = $this->get_type_banben( $type  );
		
		
        header("Content-type:text/html;charset=utf-8");
        $file_name=  $banben['name'];
        $file_name=iconv("utf-8","gb2312",$file_name);
       
        $file_sub_path= "/www/web/mall_shiziyu888_com/public_html/dan/updandandan/";
        $file_path=$file_sub_path.$file_name;
		
		
        //首先要判断给定的文件存在与否
        if(!file_exists($file_path)){
            echo "没有该文件文件";
            return ;
        }
        $fp=fopen($file_path,"r");
        $file_size=filesize($file_path);
        //下载文件需要用到的头
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:".$file_size);
        Header("Content-Disposition: attachment; filename=".$file_name);
        $buffer=1024;
        $file_count=0;
        //向浏览器返回数据
        while(!feof($fp) && $file_count<$file_size){
            $file_con=fread($fp,$buffer);
            $file_count+=$buffer;
            echo $file_con;
        }
        fclose($fp);
        
    }
    
    public function req_version()
    {
        $version = trim(I('get.version'));
		$type = trim(I('get.type'));
		
        $host = base64_decode(I('get.host'));
        
        //$domain_list = $this->domain_list;
       
	   
	   $domain_list = $this->get_type_domain($type);
	   
	    
	 
       if(!in_array($host,$domain_list))
       {
            $data = array();
            $data['domain'] = $host;
            $data['add_time'] = time();
            M('bad_domain')->add($data);
            die('-');
       }
	  
       
        $need_updrade_list = array();
        $is_find_cur = false;
		
		
		$banben_list = $this->get_type_banben($type);
		
		
        foreach($banben_list as $key => $val)
        {
            if($is_find_cur)
            {
                $need_updrade_list[$key] = $val;
            }
            if($version == $key)
            {
                $is_find_cur = true;
            }
        }
		
		
        echo json_encode($need_updrade_list);
        die();
    }
    
}
