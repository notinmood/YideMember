<?php

namespace Tencent\Model;

use Common\Model\UserinfoModel;
use Common\Model\UserrolesModel;
use Hiland\Common\CommonHelper;
use Vendor\Hiland\Biz\Tencent\NewsResponseItem;
use Vendor\Hiland\Biz\Tencent\Wechat;
use Vendor\Hiland\Biz\Tencent\WechatHelper;
use Vendor\Hiland\Utils\Data\DateHelper;
use Vendor\Hiland\Utils\Data\StringHelper;
use Vendor\Hiland\Utils\IO\Thread;
use Vendor\Hiland\Utils\Web\WebHelper;

class Mywechat extends Wechat
{

    public function __construct($token, $debug = FALSE)
    {
        parent::__construct($token, $debug);

//         $title = '微信原始数据';
//         $content = $this->originalRequestData;
//         $category = C("WEIXIN_LOG_MODES." . C("WEIXIN_LOG_MODE"));
//
//         CommonHelper::log($title, $content, $category);
    }

    /**
     * 用户关注时触发，回复「欢迎关注」
     *
     * @return void
     */
    protected function onSubscribe()
    {
        $eventkey = $this->getRequest('eventkey');

        // 1、记录用户信息进入数据库
        $recommendUserID = 0;
        $recommendUserName = '';
        if (!empty($eventkey)) {
            $recommendUserID = StringHelper::getSeperatorAfterString($eventkey, 'qrscene_');
        }

        // 根据推荐人$recommenduserid，获取推荐人基础信息,然后展示
        if ($recommendUserID > 0) {
            $recommendUserInfo = UserinfoModel::getByKey($recommendUserID);
            $recommendUserID = $recommendUserInfo['userid'];
            $recommendUserName = $recommendUserInfo['displayname'];
            $recommendUserOpenid = $recommendUserInfo['weixinopenid'];

            $userData['introducer'] = $recommendUserID;
        }

        $subscribeopenid = $this->getRequestOpenid();
        $subscribeuserinfo = WechatHelper::getUserInfo($subscribeopenid);


        $userData['weixinname'] = $subscribeuserinfo->nickname;
        $userData['displayname'] = $subscribeuserinfo->nickname;
        $userData['usersex'] = $subscribeuserinfo->sex;
        $userData['weixinopenid'] = $subscribeuserinfo->openid;
        $userData['userprovince'] = $subscribeuserinfo->province;
        $userData['usercity'] = $subscribeuserinfo->city;
        $userData['usercountry'] = $subscribeuserinfo->country;
        $userData['headurl'] = $subscribeuserinfo->headimgurl;
        $userData['jointime'] = DateHelper::format();

        $userID = UserinfoModel::interact($userData);

        $randCharresult4self = false;
        $randChar4self = null;
        if ($userID) {
            $randChar4self = CharGame::getRandGameChar();
            $randCharresult4self = CharGame::generateChar($userID, $randChar4self);
        }

        // 2、展示告知用户已经成为会员，及其推荐人信息
        if ($recommendUserID == 0) {
            $responseContent = '欢迎关注微信公众平台，您是本平台的第[' . $userID . ']位会员，我们将为你提供全心全意的服务。';
        } else {
            if (empty($recommendUserInfo)) {
                $responseContent = '欢迎关注微信公众平台，您是本平台的第[' . $userID . ']位会员，我们将为你提供全心全意的服务。';
            } else {
                $responseContent = '欢迎关注微信公众平台，您是本平台的第[' . $userID . ']位会员。您的推荐人为：' . $recommendUserID . '号[' . $recommendUserName . ']。';
                //$responseContent .= StringHelper::getNewLineSymbol();
                //$responseContent .= BizHelper::getDisplayRecommendInfo($userID, $subscribeopenid, $recommendUserID);
            }
        }

        if ($randCharresult4self) {
            $allGameChars = CharGame::getAllGameChars();
            $allGameCharsCount = count($allGameChars);
            $allGameCharsString = arr2str($allGameChars);
            $responseContent .= StringHelper::getNewLineSymbol();
            $responseContent .= "健康从自己做起，益德生物群众健康事业部全民健康普及活动正在进行。集齐“ $allGameCharsString ” $allGameCharsCount 个吉祥卡即可免费获赠60亿株益生菌一份。";

            $responseContent .= StringHelper::getNewLineSymbol();
            $responseContent .= "您关注本公众号获得吉祥卡“ $randChar4self ”一枚。快分享你在本平台的二维码（菜单 “自助服务”-“我的二维码”），让你和朋友一起收集其他几枚吉祥卡吧！";
        }

        $randChar4introducer = null;
        $randCharresult4introducer = false;
        if ($recommendUserID > 0) {
            $randChar4introducer = CharGame::getRandGameChar();
            $randCharresult4introducer = CharGame::generateChar($recommendUserID, $randChar4introducer);


            if ($randCharresult4introducer) {
                $weixinName = $userData['weixinname'];
                $customerServiceText = "您的朋友 $weixinName 跟你一起选择健康的生活方式，他获赠吉祥卡“ $randChar4self ”一枚。";
                $customerServiceText .= "同时，您也获赠吉祥卡“ $randChar4introducer ”一枚。";
                WechatHelper::responseCustomerServiceText($recommendUserOpenid, $customerServiceText);
            }
        }


        if ($randCharresult4introducer) {
            $responseContent .= StringHelper::getNewLineSymbol();
            $responseContent .= "本次您的朋友 $recommendUserName 获得吉祥卡“ $randChar4introducer ”一枚。";
        }

        $this->responseText($responseContent);
    }

    /**
     * 用户取消关注时触发
     *
     * @return void
     */
    protected function onUnsubscribe()
    {
        // 「悄悄的我走了，正如我悄悄的来；我挥一挥衣袖，不带走一片云彩。」
    }

    /**
     * 收到扫描二维码的事件（用户扫描公众平台默认的二维码不会触发本事件）
     * （只有已经是微信公众平台用户了，扫描二维码的时候才会触发本事件；否则即便扫描二维码也是触发的为订阅事件onSubscribe）
     */
    protected function onScan()
    {
        $displayContent = '';

        $subscribeopenid = $this->getRequestOpenid();

        // TODO:
        // 1,需要判断当前的站点部署场景（重点关注多商户系统下的情景）
        // 2,需要进行配置是否允许同时有多个未完成角色存在
        // 已经是微信会员和本地会员后，再次扫描的时候，如果原来的角色尚未出局，进行提醒；反之为之再次为之建立新的角色。
        $userData = UserinfoModel::getByOpenID($subscribeopenid);
        $userID = $userData['userid'];
        $userName = $userData['displayname'];

        $recommendUserID = $this->getRequest('eventkey');
        $recommendUserData = UserinfoModel::getByKey($recommendUserID);

        $recommendUserID = $recommendUserData['userid'];
        $recommendUserName = $recommendUserData['displayname'];

        if (!empty($recommendUserData)) {
            $displayContent .= '扫描信息的推荐人为：[' . $recommendUserName . ']。';
        }

        //$this->responseText($displayContent);

        // 如果是推荐人是商户，则弹出支付请求的按钮
        // TODO 需要加入判断：如果当前用户在此商户尚有资金余额，则弹出支付请求的连接（目前是请求发送到商户后台，在判断用户在此商户是否有余额可用）
        if ($recommendUserData['ismerchant']) {
            $payUrl = 'http://' . WebHelper::getHostName() . U("PayInner/launchPayNotice", "fromuserid=$userID&touserid=$recommendUserID&fromusername=$userName&tousername=$recommendUserName");
            $displayContent .= "<a href='" . $payUrl . "'>付款给[$recommendUserName]</a>" . StringHelper::getNewLineSymbol();
        }

        if (!empty($userData)) {
            $roleinfo = UserrolesModel::getUnOutRoles($userID);
        }

        if (empty($roleinfo) || C('WEIXIN_USER_MULTIUNOUTROLES_ALLOW') == true) {
            // 如果本用户没有正在参与游戏的角色（未建立角色或原来的角色已经出局了），那么再次为之建立新的角色，并提示。
            $displayContent .= "选择参加推荐人的以下活动：" . StringHelper::getNewLineSymbol();
            $recommendContent = BizHelper::getDisplayRecommendInfo($userID, $subscribeopenid, $recommendUserID);
            if (empty($recommendContent)) {
                $recommendContent = '推荐人尚未有活动:(';
            }
            $displayContent .= $recommendContent;
        } else {
            $displayContent = $displayContent . '您于[' . date('Y-m-d H:i:s', $roleinfo[0]['scantime']) . ']在本平台注册的角色,尚有层级未做满，请通过分享您的二维码继续努力！';
        }

        $this->responseText($displayContent);
    }

    protected function onClick()
    {
        $envetkey = $this->getRequest('eventkey');
        switch ($envetkey) {
            case 'menu_myqrcode':
                $openID = $this->getRequestOpenid();
                $qrUrl = U("Tencent/Index/responseQRCode", "openID=$openID");
                Thread::asynExec($qrUrl);
                $this->responseText("您的推广二维码生成之中，请稍等片刻。");
                break;
            case 'menu_myfinance':
                $redirecturl = 'http://' . WebHelper::getHostName() . C('WEIXIN_OAUTH2_REDIRECTPAGE');
                $redirectstate = 1;
                $oauth2url = WechatHelper::getOAuth2PageUrl($redirectstate, $redirecturl);
                $this->responseText('财务信息已经准备就绪' . StringHelper::getNewLineSymbol() . '<a href="' . $oauth2url . '">==>请点击查看<==</a>');
                break;
            case 'menu_myuserinfo':
                $userOpenID = $this->getRequestOpenid();
                $redirecturl = 'http://' . WebHelper::getHostName() . U('Tencent/My/index', "useropenid=$userOpenID");
                $this->responseText('用户信息已经准备就绪' . StringHelper::getNewLineSymbol() . '<a href="' . $redirecturl . '">==>请点击查看<==</a>');
                break;
            case 'menu_withdraw':
                $redirecturl = 'http://' . WebHelper::getHostName() . C('WEIXIN_OAUTH2_REDIRECTPAGE');
                $redirectstate = 2;
                $oauth2url = WechatHelper::getOAuth2PageUrl($redirectstate, $redirecturl);
                $this->responseText('取现<a href="' . $oauth2url . '">点击这里体验</a>');
                break;
            case 'menu_roleservice':
                $userOpenID = $this->getRequestOpenid();
                $redirecturl = 'http://' . WebHelper::getHostName() . U('Tencent/My/roleservice', "useropenid=$userOpenID");


                $this->responseText('活动信息已经准备就绪' . StringHelper::getNewLineSymbol() . '<a href="' . $redirecturl . '">==>请点击查看<==</a>');
                break;
            default:
                $this->responseText('收到了点击的菜单：' . $this->getRequest('eventkey'));
                break;
        }
    }

    /**
     * 收到文本消息时触发，回复收到的文本消息内容
     *
     * @return void
     */
    protected function onText()
    {
        $contentReceived = $this->getRequest('content');

        CommonHelper::log('receive text', $contentReceived);

        switch ($contentReceived) {
            case 'cs-tx':
                $redirecturl = 'http://' . WebHelper::getHostName() . C('WEIXIN_OAUTH2_REDIRECTPAGE');
                $redirectstate = 2;
                $oauth2url = WechatHelper::getOAuth2PageUrl($redirectstate, $redirecturl);
                $this->responseText('取现<a href="' . $oauth2url . '">点击这里体验</a>');
                break;
            default:
                $this->responseText('收到了文字消息：' . $this->getRequest('content'));
                break;
        }
    }

    /**
     * 收到图片消息时触发，回复由收到的图片组成的图文消息
     *
     * @return void
     */
    protected function onImage()
    {
        $items = array(
            // new NewsResponseItem ( '标题一', '描述一', $this->getRequest ( 'picurl' ), $this->getRequest ( 'picurl' ) ),
            new NewsResponseItem('标题二', '描述二', $this->getRequest('picurl'), $this->getRequest('picurl'))
        );

        // NewsResponseItem
        $this->responseNews($items);
    }

    /**
     * 收到地理位置消息时触发，回复收到的地理位置
     *
     * @return void
     */
    protected function onLocation()
    {
        $this->responseText('收到了位置消息：' . $this->getRequest('location_x') . ',' . $this->getRequest('location_y'));
    }

    /**
     * 收到链接消息时触发，回复收到的链接地址
     *
     * @return void
     */
    protected function onLink()
    {
        $this->responseText('收到了链接：' . $this->getRequest('url'));
    }

    /**
     * 收到未知类型消息时触发，回复收到的消息类型
     *
     * @return void
     */
    protected function onUnknown()
    {
        $this->responseText('收到了未知类型消息：' . $this->getRequest('msgtype'));
    }
}

?>