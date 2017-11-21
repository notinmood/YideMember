<?php
namespace Tencent\Controller;

use Common\Common\ConfigHelper;
use Common\Model\UserinfoModel;
use Common\Model\UserrolesModel;
use Tencent\Model\MyWxPayNotify;
use Tencent\Model\WxJSAPIMate;
use Think\Controller;
use Vendor\Hiland\Biz\Tencent\Pay\WxPayApi;
use Vendor\Hiland\Biz\Tencent\Pay\WxPayData\WxPayDataBaseUnifiedOrder;
use Vendor\Hiland\Biz\Tencent\WechatHelper;
use Vendor\Hiland\Utils\Data\GuidHelper;
use Vendor\Hiland\Utils\Data\StringHelper;
use Vendor\Hiland\Utils\DataModel\ModelMate;
use Vendor\Hiland\Utils\Web\EnvironmentHelper;
use Vendor\Hiland\Utils\Web\WebHelper;

class PayOuterController extends Controller
{

    /**
     * 根据商家服务信息为用户创建角色
     *
     * @param int $userID
     * @param $userOpenID
     * @param int $serviceID
     */
    public function createRoleByMerchantService($userID, $userOpenID, $serviceID)
    {
        $serviceMate = new ModelMate('merchantservice');
        $serviceData = $serviceMate->get($serviceID);
        $serviceName = $serviceData['servicename'];
        $servicePrice = $serviceData['price'];
        $merchantID = $serviceData['merchantid'];
        $merchantData = UserinfoModel::getByKey($merchantID);
        $merchantName = $merchantData['displayname'];

        $roleData = array();
        $roleData['paytag'] = 0;//WEIXIN_PAY_TAGS
        $roleData['roleguid'] = GuidHelper::newGuid();
        $roleData['userid'] = $userID;
        $roleData['recommenduserid'] = $merchantID;
        $roleData['recommenddisplayname'] = $merchantName;
        $roleData['recommendroleid'] = 0;
        $roleData['scantime'] = time();
        $roleData['price'] = $servicePrice;
        $roleData['rolename'] = "商家$merchantName 的活动$serviceName ";
        $roleData['merchantid'] = $merchantID;

        $outdegrees = ConfigHelper::get1DArray('ROLE_OUT_DEGREES', '', 'value');
        $outdegree = $outdegrees['UNIN']; // C('ROLE_OUT_DEGREES')
        $roleData['outdegree'] = $outdegree;

        // 如果推荐人本身就是庄家，直接把推荐作为当前用户角色的庄家
        if ((int)$merchantData['ismaster'] == 1) {
            $roleData['masterid'] = $merchantID;
        } else {
            //
        }

        $roleID = UserrolesModel::interact($roleData);

        $jsApiParameters = $this->preparePayData($userOpenID, $roleData);
        $this->assign("jsApiParameters", $jsApiParameters);

//        $signPackage = WechatHelper::getJSAPISignPackage();
//        $this->assign('signPackage', $signPackage);

        $displayData['title'] = '支付信息';
        $displayData['content'] = "参加商户$merchantName 的活动$serviceName 付款$servicePrice 元。";
        if(C('SYSTEM_DISPLAY_PAYWARNING')){
            $displayData['content'] .= StringHelper::getNewLineSymbol(). "本费用为特殊活动所支付，支付费用只可消费不可退回。操作前请确认此信息。";
        }
        $this->assign('data', $displayData);
        $this->display('pay');
    }

    private function preparePayData($userOpenID, $roleData)
    {
        // ①、获取用户openid
        $tools = new WxJSAPIMate();
        $openID = $userOpenID;//$tools->GetOpenid();

        // ②、统一下单
        $input = new WxPayDataBaseUnifiedOrder();
        $input->SetBody($roleData['rolename']);
        $input->SetAttach($roleData['rolename']);
        $input->SetOut_trade_no(GuidHelper::cleanHyphen($roleData['roleguid'])); // 使用角色的guid作为订单号，便于后续对照
        $input->SetTotal_fee($roleData['price'] * 100); // 把元转换成分
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));

        $payTags = C('WEIXIN_PAY_TAGS');
        $payTagInt = $roleData['paytag'];
        $payTag = $payTags[$payTagInt];
        $input->SetGoods_tag($payTag);

        $input->SetNotify_url("http://" . WebHelper::getHostName() . "/index.php/tencent/pay_outer/notify");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openID);
        $order = WxPayApi::unifiedOrder($input);

        $jsApiParameters = $tools->GetJsApiParameters($order);
        // $this->assign("jsApiParameters", $jsApiParameters);
        return $jsApiParameters;

        // 获取共享收货地址js函数参数
        // $editAddress = $tools->GetEditAddressParameters();
        // $this->assign("editAddress", $editAddress);
        // dump($jsApiParameters);

        // ③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
        /**
         * 注意：
         * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
         * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
         * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
         */
    }

    /**
     * 根据推荐人的角色为用户创建角色
     *
     * @param $userID
     * @param $userOpenID
     * @param $recommendUserRoleID
     */
    public function createRoleByRecommendUserRole($userID, $userOpenID, $recommendUserRoleID)
    {
        $recommendRoleData = UserrolesModel::get($recommendUserRoleID);
        $recommendUserID = $recommendRoleData['userid'];
        $recommendUserData = UserinfoModel::getByKey($recommendUserID);
        $recommendUserName = $recommendUserData['displayname'];

        $roleName = $recommendRoleData['rolename'];
        $rolePrice = (float)$recommendRoleData['price'];

        $roleData = array();
        $roleData['paytag'] = 0;//WEIXIN_PAY_TAGS
        $roleData['roleguid'] = GuidHelper::newGuid();
        $roleData['userid'] = $userID;
        $roleData['recommenduserid'] = $recommendUserID;
        $roleData['recommenddisplayname'] = $recommendUserName;
        $roleData['recommendroleid'] = $recommendUserRoleID;
        $roleData['scantime'] = time();
        $roleData['price'] = $rolePrice;
        $roleData['rolename'] = $roleName;
        $roleData['merchantid'] = $recommendRoleData['merchantid'];

        $outdegrees = ConfigHelper::get1DArray('ROLE_OUT_DEGREES', '', 'value');
        $outdegree = $outdegrees['UNIN']; // C('ROLE_OUT_DEGREES')
        $roleData['outdegree'] = $outdegree;

        // 如果推荐人本身就是庄家，直接把推荐作为当前用户角色的庄家，否则把推荐人对应的庄家作为当前用户角色的庄家
        if ((int)$recommendUserData['ismaster'] == 1) {
            $roleData['masterid'] = $recommendUserID;
        } else {
            $roleData['masterid'] = $recommendRoleData['masterid'];
        }

        $roleID = UserrolesModel::interact($roleData);

        $jsApiParameters = $this->preparePayData($userOpenID, $roleData);
        $this->assign("jsApiParameters", $jsApiParameters);

        $displayData['title'] = '支付信息';
        $displayData['content'] = "参加好友$recommendUserName 推荐的活动$roleName 付款$rolePrice 元。";
        if(C('SYSTEM_DISPLAY_PAYWARNING')){
            $displayData['content'] .= StringHelper::getNewLineSymbol(). "本费用为特殊活动所支付，支付费用只可消费不可退回。操作前请确认此信息。";
        }
        $this->assign('data', $displayData);

        $signPackage = WechatHelper::getJSAPISignPackage();
        $this->assign('signPackage', $signPackage);

        $this->display('pay');
    }

    /**
     * @param $userID
     * @param $userOpenID
     */
    public function payForVIP($userID, $userOpenID)
    {
        $vipPrice = (float)C('WEIXIN_ZIGE_VIPPRICE');

        $roleData = array();
        $roleData['paytag'] = 1;//WEIXIN_PAY_TAGS
        $roleData['roleguid'] = GuidHelper::newGuid();
        $roleData['userid'] = $userID;
        $roleData['scantime'] = time();
        $roleData['price'] = $vipPrice;
        $roleData['rolename'] = '申请VIP';

        $roleID = UserrolesModel::interact($roleData);

        $jsApiParameters = $this->preparePayData($userOpenID, $roleData);
        $this->assign("jsApiParameters", $jsApiParameters);

        $displayData['title'] = '支付信息';
        $displayData['content'] = "申请成为VIP会员 付款$vipPrice 元。";
        $this->assign('data', $displayData);

        $signPackage = WechatHelper::getJSAPISignPackage();
        $this->assign('signPackage', $signPackage);

        $this->display('pay');
    }

    public function notify()
    {
        $notify = new MyWxPayNotify();
        $notify->Handle(false);
    }
}

?>