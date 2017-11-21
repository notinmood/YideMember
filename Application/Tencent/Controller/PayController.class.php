<?php
namespace Tencent\Controller;

use Hiland\Common\CommonHelper;
use Tencent\Model\MyWxPayNotify;
use Tencent\Model\WxJSAPIMate;
use Think\Controller;
use Vendor\Hiland\Biz\Tencent\Common\WechatConfig;
use Vendor\Hiland\Biz\Tencent\Packet\WxPacket;
use Vendor\Hiland\Biz\Tencent\Pay\WxPayApi;
use Vendor\Hiland\Biz\Tencent\Pay\WxPayData\WxPayDataBaseUnifiedOrder;
use Vendor\Hiland\Utils\Data\RandHelper;
use Vendor\Hiland\Utils\Web\WebHelper;

class PayController extends Controller
{
    public function pay(){

    }

    public function index()
    {
        $payurl = U('Pay/tt');
        
        $this->assign("payurl", $payurl);
        $this->display();
    }

    public function tt()
    {
        
        // ①、获取用户openid
        $tools = new WxJSAPIMate();
        $openId = $tools->GetOpenid();
        
        // ②、统一下单
        $input = new WxPayDataBaseUnifiedOrder();
        $input->SetBody("test");
        $input->SetAttach("test");
        $input->SetOut_trade_no(WechatConfig::MCHID . date("YmdHis"));
        $input->SetTotal_fee("1");
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        $input->SetNotify_url("http://" . WebHelper::getHostName() . "/index.php/tencent/pay/notify");
        // $input->SetNotify_url("http://paysdk.weixin.qq.com/example/notify.php");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $order = WxPayApi::unifiedOrder($input);
        echo '<span style="color: #f00; "><b>统一下单支付单信息</b></span><br/>';
        $this->printf_info($order);
        $jsApiParameters = $tools->GetJsApiParameters($order);
        
        // 获取共享收货地址js函数参数
        $editAddress = $tools->GetEditAddressParameters();
        
        dump($jsApiParameters);
        
        // $this->show('sssssssssssssssssssss');
        
        $this->assign("jsApiParameters", $jsApiParameters);
        $this->assign("editAddress", $editAddress);
        $this->display();
        
        // ③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
    /**
     * 注意：
     * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
     * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
     * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
     */
    }
    
    // 打印输出数组信息
    function printf_info($data)
    {
        foreach ($data as $key => $value) {
            echo "<span style=\"color: #00ff55;; \">$key</span> : $value <br/>";
        }
    }

    public function notify()
    {
        CommonHelper::log('pay notify gate.');
        $notify = new MyWxPayNotify();
        $notify->Handle(false);
    }

    public function hongbao()
    {
        $oauth2openid = $_GET['oauth2openid'];
        $para = array(
            'nonce_str' => RandHelper::rand(30), // 随机字符串
            'mch_billno' => date('YmdHis') . RandHelper::rand(10), // 订单号
            'mch_id' => C('WEIXIN_MERCHANTID'), // 商户号
            'wxappid' => C('WEIXIN_APPID'), // 微信的appid
            'nick_name' => WechatConfig::MCHNAME, // 提供方名称
            'send_name' => WechatConfig::MCHNAME, // 红包发送者名称
            're_openid' => $oauth2openid, // 接受人的openid
            'total_amount' => 100, // 付款金额，单位分
            'min_value' => 100, // 最小红包金额，单位分
            'max_value' => 100, // 最大红包金额，单位分
            'total_num' => 1, // 红包収放总人数
            'wishing' => '恭喜发财', // 红包祝福语
            'client_ip' => '127.0.0.1', // 调用接口的机器 Ip 地址
            'act_name' => '费用提现', // 活动名称
            'remark' => '快来领取吧！'
        ); // 备注信息
        
        $packet = new WxPacket();
        $result = $packet->send($para);
    }


}

?>