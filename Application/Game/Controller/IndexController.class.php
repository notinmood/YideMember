<?php
namespace Game\Controller;

use Game\Model\GameBiz;
use Think\Controller;
use Vendor\Hiland\Biz\Tencent\WechatHelper;
use Vendor\Hiland\Utils\Data\GuidHelper;
use Vendor\Hiland\Utils\Web\WebHelper;

/**
 * Created by PhpStorm.
 * User: devel
 * Date: 2016/4/2 0002
 * Time: 7:38
 */
class IndexController extends Controller
{
    public function index()
    {
        $openID = GuidHelper::newGuid();
        $result = GameBiz::generateCharactorGameSet($openID);

        dump($result);

        //dump(C('WEIXIN_OAUTH2_REDIRECTPAGE'));
        $redirecturl = 'http://' . WebHelper::getHostName() . C('WEIXIN_OAUTH2_REDIRECTPAGE');
        $redirectstate = 100;
        $oauth2url = WechatHelper::getOAuth2PageUrl($redirectstate, $redirecturl,'','snsapi_base');

        dump('aaaaaaaaaaaaaa:'.$oauth2url);
        dump($oauth2url);


        WebHelper::redirectUrl($oauth2url);
        $this->show("<a href='". $oauth2url ."'>开始</a>");
    }

    public function character()
    {
        //WxJSAPIMate
//        //dump('ssssssssssssss');
//        $code= WechatHelper::getOAuth2Code();
//        dump($code);
//        $openID= WechatHelper::getOAuth2OpenID($code);
//
//        dump($openID);
    }
}