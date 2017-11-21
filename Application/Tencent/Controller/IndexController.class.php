<?php
namespace Tencent\Controller;

use Common\Model\UserinfoModel;
use Hiland\Common\CommonHelper;
use Tencent\Model\BizHelper;
use Tencent\Model\Mywechat;
use Think\Controller;
use Vendor\Hiland\Biz\Tencent\WechatHelper;
use Vendor\Hiland\Utils\Web\WebHelper;

class IndexController extends Controller
{

    /**
     * 微信公众平台开发接口
     */
    public function index()
    {
        $wechat = new Mywechat(C('WEIXIN_GATE_TOKEN'));
        $wechat->run();
    }

    /**
     * 创建微信菜单
     */
    public function createweixinmenu()
    {
        $readmeUrl = 'http://' . WebHelper::getHostName() . '/ott/index.php?s=/home/article/detail/id/1.html';
        $marketingUrl = 'http://' . WebHelper::getHostName() . '/ott/index.php?s=/home/article/detail/id/3.html';
        $menujson = '
            {
                 "button":[
                 {
                       "type":"view",
                       "name":"平台说明",
                       "url":"' . $readmeUrl . '"
                 },
                 {
                      "type":"view",
                      "name":"招商加盟",
                      "url":"' . $marketingUrl . '"
                 },
                 {
                       "name":"自助服务",
                       "sub_button":[
                       {
                           "type":"click",
                           "name":"个人信息",
                           "key":"menu_myuserinfo"
                       },
                       {
                           "type":"click",
                           "name":"活动与服务",
                           "key":"menu_roleservice"
                       },
                       {
                           "type":"click",
                           "name":"我的推广码",
                           "key":"menu_myqrcode"
                       }]
                 }]
            }    
            ';

        $result = WechatHelper::createMenu($menujson);

        $this->show($result);
    }

    /**
     * 展示微信菜单
     */
    public function showweixinmenu()
    {
        $result = WechatHelper::getMenu(); //BizHelper::getweixinmenu();
        dump($result);
    }

    /**
     * oauth2认证的中间跳转页面
     * 根据微信认证服务器传递过来的state值，跟配置文件中的配置节点WEIXIN_OAUTH2_REDIRECTSTATE
     * 进行对照，然后进行页面跳转
     */
    public function oauth2redirectpage()
    {
        // 微信服务器跳转过来的时候，传递的授权code
        $oauth2code = false;
        // 开发人员自定义的跳转标示（参看Application\Tencent\Conf\config.php内的配置项）
        $oauth2state = 0;
        if (isset($_GET['code'])) {
            $oauth2code = $_GET['code'];
            $oauth2state = $_GET['state'];
        } else {
            $this->show("NO CODE");
        }

        if (!empty($oauth2code)) {
            switch ($oauth2state) {
                case 0: // 展示信息
                    $oauth2accesstoken = WechatHelper::getOAuth2AccessToken($oauth2code, C('WEIXIN_APPID'), C('WEIXIN_APPSECRET'));
                    $oauth2openid = WechatHelper::getOAuth2OpenID($oauth2code, C('WEIXIN_APPID'), C('WEIXIN_APPSECRET'));

                    $oauth2userinfo = WechatHelper::getOAuth2UserInfo($oauth2openid, $oauth2accesstoken);
                    dump($oauth2userinfo);
                    break;
                default: // 进行页面跳转
                    $oauth2accesstoken = WechatHelper::getOAuth2AccessToken($oauth2code, C('WEIXIN_APPID'), C('WEIXIN_APPSECRET'));
                    $oauth2openid = WechatHelper::getOAuth2OpenID($oauth2code, C('WEIXIN_APPID'), C('WEIXIN_APPSECRET'));

                    $paraArray = array(
                        'oauth2openid' => $oauth2openid,
                        'oauth2accesstoken' => $oauth2accesstoken
                    );

                    $targetUrl = C('WEIXIN_OAUTH2_REDIRECTSTATE.' . $oauth2state);
                    $targetUrl = WebHelper::attachUrlParameter($targetUrl, $paraArray);

                    WebHelper::redirectUrl($targetUrl);
                    break;
            }
        }
    }

    public function responseQRCode($openID){
        // 1、根据当前用户的openid获取其在本地系统的userinfo
        $userinfo = UserinfoModel::getByOpenID($openID);
        CommonHelper::log('用户二维码',$openID);

        // 2、生成推广二维码并保持
        $patharray = BizHelper::generateAndSaveQRCode($userinfo);
        //$this->responseText('本功能修复中，稍后再试。'."(g)$patharray");
        $recommendpicurl = $patharray['weburl'];
        $physicalpath = $patharray['physicalpath'];
        if (!empty($userinfo)) {
            $userinfo['recommendpicture'] = $recommendpicurl;
            UserinfoModel::interact($userinfo);
        }

        // 3、上传保存的图片到微信服务器，得到保存文件的mediaid
        $mediaid = WechatHelper::uploadMedia($physicalpath); //根据用户生成具体的推广二维码

        // 4、将这个图片信息推送到用户微信中
        WechatHelper::responseCustomerServiceImage($openID,$mediaid);
    }
}

?>