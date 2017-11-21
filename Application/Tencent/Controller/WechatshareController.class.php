<?php
namespace Tencent\Controller;

use Think\Controller;
use Vendor\Hiland\Biz\Tencent\WechatHelper;

class WechatshareController extends Controller
{

    public function _initialize()
    {
        $signPackage = WechatHelper::getJSAPISignPackage();
        $this->assign('signPackage', $signPackage);
    }

    public function sharedemo()
    {
        $this->display();
    }
}


?>