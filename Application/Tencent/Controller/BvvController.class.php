<?php
namespace Tencent\Controller;

use Common\Model\UserinfoModel;
use Common\Model\UserrolesModel;
use Hiland\Common\CommonHelper;
use Hiland\Model\ViewMate;
use Tencent\Model\BizHelper;
use Tencent\Model\FinanceHelper;
use Think\Controller;
use Think\Model;
use Vendor\Hiland\Biz\Tencent\WechatHelper;
use Vendor\Hiland\Biz\UrlService\ShortenUrl;
use Vendor\Hiland\Utils\Data\ArrayHelper;
use Vendor\Hiland\Utils\Data\CalendarHelper;
use Vendor\Hiland\Utils\Data\DateHelper;
use Vendor\Hiland\Utils\Data\GuidHelper;
use Vendor\Hiland\Utils\Data\ObjectHelper;
use Vendor\Hiland\Utils\Data\OperationHelper;
use Vendor\Hiland\Utils\Data\RegexHelper;
use Vendor\Hiland\Utils\Data\StringHelper;
use Vendor\Hiland\Utils\DataConstructure\Queue;
use Vendor\Hiland\Utils\DataConstructure\Stack;
use Vendor\Hiland\Utils\DataModel\ModelMate;
use Vendor\Hiland\Utils\IO\File\FileUtil;
use Vendor\Hiland\Utils\IO\ImageHelper;
use Vendor\Hiland\Utils\Web\EnvironmentHelper;
use Vendor\Hiland\Utils\Web\NetHelper;
use Vendor\Hiland\Utils\Web\PageHelper;
use Vendor\Hiland\Utils\Web\SaeHelper;
use Vendor\Hiland\Utils\Web\WebHelper;

vendor('Resource.Environment.Mobile_Detect');

class BvvController extends Controller
{
    public function vendorop()
    {
        //vendor('Resource.Environment.Mobile_Detect');
        $detect = new \Mobile_Detect();
        dump($detect->getBrowsers());
    }

    public function queueop()
    {
        $queue = new Queue();
        //$queue->push('A'=>'ssssssssssss');
        $queue->push('1111111111');
        $queue->push(2);
        $queue->push(3);
        $queue->push(4);

        dump($queue->seek());
        dump($queue->pop());
        dump($queue->pop());
        dump($queue->seek());
        dump($queue->pop());
        dump($queue->pop());
        dump($queue->pop());
        dump($queue->seek());
    }

    public function stackop()
    {
        $stack = new Stack();
        $stack->push(1);
        $stack->push("2");
        $stack->push(3);
        $stack->push(4);

        dump($stack->seek());
        dump($stack->pop());
        dump($stack->pop());
        dump($stack->seek());
        dump($stack->pop());
        dump($stack->pop());
        dump($stack->pop());
        dump($stack->pop());
    }

    public function flowcontentop()
    {
//        $userA= UserinfoModel::getByKey(100001);
//        $userB= UserinfoModel::getByKey(100002);
//
//        dump($userA);
//        $result= FinanceHelper::buildFlowContent('ZGSY', $userA,$userB);
//        dump($result);
    }

    /**
     *?????flowin???
     */
    public function flowinmerchantop()
    {
        $financeData = null;
        $financeData['userid'] = 100002;
        $financeData['moneyamount'] = 1.05;
        $financeData['subjecttype'] = 3;//SYSTEM_FINANCE_SUBJECTS ??????
        $financeData['relationuserid'] = 100004;
        $financeData['relationroleid'] = 142;

        dump(FinanceHelper::flowIn($financeData));
    }

    public function flowincommonuserop()
    {
        $financeData = null;
        $financeData['userid'] = 100004;
        $financeData['roleid'] = 142;
        $financeData['moneyamount'] = 0.02;
        $financeData['subjecttype'] = 3;//SYSTEM_FINANCE_SUBJECTS ??????
        $financeData['relationuserid'] = 100001;


        dump(FinanceHelper::flowIn($financeData));
    }

    public function flowoutwithroleop()
    {
        $financeData = null;
        $financeData['userid'] = 100004;
        $financeData['roleid'] = 142;
        $financeData['moneyamount'] = 0.03;
        $financeData['subjecttype'] = -1;//SYSTEM_FINANCE_SUBJECTS ????
        $financeData['relationuserid'] = 100001;

        $result = FinanceHelper::flowOut($financeData);
        dump($result);
        dump(OperationHelper::getResult($result));
        dump(OperationHelper::getErrorMessage($result));
    }

    public function flowoutwithoutroleop()
    {
        $financeData = null;
        $financeData['userid'] = 100004;
        //$financeData['roleid'] = 142;
        $financeData['moneyamount'] = 1;
        $financeData['subjecttype'] = -3;//SYSTEM_FINANCE_SUBJECTS ???????
        $financeData['relationuserid'] = 100002;
        $financeData['merchantid'] = 100002;

        $result = FinanceHelper::flowOut($financeData);
        dump($result);
        dump(OperationHelper::getResult($result));
        dump(OperationHelper::getErrorMessage($result));
    }

    public function displayRecommendInfoop($userid = 100001, $useropenid = 'oOjPas1SKwihAMngxQxCqmdYGiU4', $recommenduserid = 100004)
    {
        PageHelper::renderCoding();
        dump(BizHelper::getDisplayRecommendInfo($userid, $useropenid, $recommenduserid));
    }

    public function createrolebyserviceop()
    {
        //PayOuterController::createAndGetRoleByMerchantService(100004,13);
    }

    public function getvalueop()
    {
        $mate = new ModelMate('userinfo');
        dump($mate->getValue(100004, 'moneyamount'));
    }

    public function setvalueop()
    {
        $mate = new ModelMate('userinfo');
        dump($mate->setValue(100002, 'moneyamount', 208, 'userid'));
    }

    /**
     *
     */
    public function saeinfo()
    {
        dump(SaeHelper::getMysqlConnectionInfo());
    }

    /**
     *
     */
    public function logop()
    {
        CommonHelper::log('sssssssssssss', 'pppppppppppppp');
        echo MyEnum::BY;
    }

    public function toupperop()
    {
        $originalString = 'Welecom to Sae';
        $originalArray['city1'] = 'Qingdao';
        $originalArray['city2'] = 'beijing';

        $convertedString1 = array_change_key_case($originalString, CASE_UPPER);
        dump($convertedString1);

        $convertedArray = array_change_key_case($originalArray, CASE_UPPER);
        dump($convertedArray);

        $convertedString2 = strtoupper($originalString);
        dump($convertedString2);
    }

    public function lunarop()
    {
        dump(CalendarHelper::convertSolarToLunar(2016, 3, 4));
    }

    public function arraylevelop()
    {
        $array = array(
            'a' => 1,
            'b' => array(
                'b1' => '1111',
                'b2' => 2222,
                'b3' => array(
                    'b31' => 'b31',
                    'b32' => array(
                        'b321' => 'ok',
                    )
                ),
                'b4' => array(
                    'b41' => 'b41'
                ),
            ),
            'c' => 'sss'
        );

        dump(ArrayHelper::getLevel($array));
    }

    /**
     * ??????
     */
    public function nestedfunctionop()
    {
        $index = 0;

        function inner(&$value)
        {
            $value++;
            //$index++; //??????????????????????????
        }

        $valueWillPass = 10;
        inner($valueWillPass);

        dump($valueWillPass);
    }

    public function guiddetermineop($data = '')
    {
        if (empty($data)) {
            $data = GuidHelper::newGuid(true);
        }

        dump(GuidHelper::determine($data));
    }

    public function getresultop()
    {
        dump(OperationHelper::getResult(GuidHelper::newGuid()));
        dump(OperationHelper::getResult('12222'));
        dump(OperationHelper::getResult(12222));
        dump(OperationHelper::getResult(true));

        dump(OperationHelper::getResult(false));
        dump(OperationHelper::getResult('ssssssssssssssssss'));
    }

    public function getrudingparentroleidop($recommendroleid = 179)
    {
        //$methodArgs = array('recommendRoleID' => 142);
        //$reuslt = ReflectionHelper::executeMethod('Tencent\Model\BizHelper', 'getRuDingParentRoleID', null, $methodArgs);

//        $where['parentid'] = $recommendroleid;
//        $subRoles = UserrolesModel::getRoles(0, 0, null, $where);
//        dump($subRoles);
//        $subRoleCount = count($subRoles);
//        dump($subRoleCount);


        $reuslt = BizHelper::getRuDingParentRoleID($recommendroleid);
        dump($reuslt);
    }

    public function changeUserMoneyAmount($userid = 0, $money = 10)
    {
        dump(UserinfoModel::changeMoneyAmount($userid, $money));
    }

    public function mtrandop()
    {
        dump(mt_rand(0, 3));
    }

    public function rudingop($rolekey = 289)
    {
        $result = BizHelper::ruDing($rolekey);
        dump($result);
    }

    public function usermoneyamountneedactiveop($userid = 100004, $amount = 0.02)
    {
        UserinfoModel::changeMoneyAmountNeedActive($userid, $amount);
    }

    public function urlop($id = 0)
    {
        $confirmUrl = 'http://' . WebHelper::getHostName() . U("Tencent/PayInner/launchPayConfirm", "id=" . $id);
        dump($confirmUrl);
    }

    public function viewmodelop($roleid = 307)
    {
        PageHelper::renderCoding();
        $modelInfos = array(
            array('userroles', 'A', 'A.roleid,A.rolename')
        );


        $viewMate = new ViewMate($modelInfos, null);
        $where = null;
        $addon = null;

        $where = "A.parentid=$roleid";
        $addon = 'ORDER BY A.roleid desc';

        //dump($viewMate->showSql($where, $addon));

        $roles = $viewMate->select($where, $addon);
        dump($roles);

        /*
        $modelInfos = array(
            array('userroles', 'A', 'A.roleid,A.rolename'),
            array('userroles', 'B', ''),
            array('userinfo', 'C', 'C.winxinname,C.moneyamount')
        );

        $onClauses = array(
            'A.parentid= B.roleid',
            'B.userid=C.userid'
        );


        $viewMate = new ViewMate($modelInfos, $onClauses);
        $where= null;
        $addon= null;

        $where = "A.parentid=$roleid";
        $addon = 'ORDER BY A.roleid desc';

        //dump($viewMate->showSql($where, $addon));

        $roles = $viewMate->select($where, $addon);
        dump($roles);
        */

        /*$view = D("Userroles");
        $list = $view->where('roleid>0')->select();

        dump($view->getTableName());
        dump($view->_sql());
        dump($list);*/
    }

    public function modelmethodop()
    {
        PageHelper::setCoding();
        //$sql= "select * from ot33binbin_userroles";
        $sql = "SELECT A.*,C.displayname,C.headurl FROM ot33binbin_userroles A  LEFT JOIN ot33binbin_userroles B ON A.parentid= B.roleid LEFT JOIN ot33binbin_userinfo C ON B.userid=C.userid WHERE A.parentid=307 ORDER BY A.roleid desc";

        $model = new Model();

        dump($model->query($sql));
    }


    public function isendwithop()
    {
        $whole = 'i like this game';
        $padding1 = 'game';
        $padding2 = 'gam';

        dump(StringHelper::isEndWith($whole, $padding1));
        dump(StringHelper::isEndWith($whole, $padding2));

        $ww = 'A.*,B.displayname,B.weixinopenid,';
        $pp = ',';

        dump(StringHelper::getSeperatorAfterString($ww, $pp));
        dump(StringHelper::isEndWith($ww, $pp));
    }

    public function stringformatop($data = '')
    {
        if (empty($data)) {
            $data = '20160316';
        }

        $formater = '{4} {2} {2}';//'{4}-{2}-{2}';
        dump(StringHelper::format($data, $formater));
    }

    public function regexop()
    {
        $match = null;
        dump(preg_match_all('/\d*/', '{40}', $match));
        dump($match);

        $partten = '/\{\d*\}/';
        $data = '{4}-{2}-{2}';
        $matches = null;
        $result = preg_match_all($partten, $data, $matches);
        dump($result);
        dump($matches);
    }

    public function gettimeop($date = '')
    {
        if (empty($date)) {
            //$date= '2016-03-16 13:12:25';
            //$date= '2016-03-16 13:12';
            //$date= '201603161312';
            $date = '2016-03-16';
        }

        $time = DateHelper::getTimestamp($date);
        dump($time);
        $dateConverted = date('Y-m-d H:i:s', $time);
        dump($dateConverted);
    }

    public function settingop()
    {
        dump(C('SYSTEM_ERROR_NOTICES'));
    }

    public function vartypeop()
    {
        dump(ObjectHelper::getString(false));
        dump(ObjectHelper::getString(123.45));
        dump(ObjectHelper::getString(null));
        dump(ObjectHelper::getString('ssssssssss'));
        dump(ObjectHelper::getString(array('aa', 'bb')));

        $obj = new BvvController();
        dump(ObjectHelper::getString($obj));
    }

    public function fileutilop()
    {
        $file = new FileUtil('Sae');
        dump($file->getList('Uploads/Picture'));
    }

    public function ucop()
    {
        $testStr = "just_test_here";
        $str = ucwords(str_replace("_", " ", $testStr));
        echo str_replace(" ", "", $str);
        echo ucfirst(str_replace("_", " ", $testStr));
    }

    public function getrolesop($userid = 100004)
    {
        PageHelper::setCoding();
        $roles = UserrolesModel::getRoles($userid);
        dump($roles);
    }


    public function kintop()
    {

        //vendor('Resource.Debugging.kint.Kint','','.class.php');
        vendor('Resource/Debugging/kint/Kint', '', '.class.php');

        //require '/ThinkPHP/Library/Vendor/Resource/Debugging/kint/Kint.class.php';
        \Kint::dump('ssssssssss');
    }

    public function webserverop()
    {
        dump(EnvironmentHelper::getWebServerName());
        dump(EnvironmentHelper::getDepositoryPlateformName());
    }

    public function getDisplayRecommendInfo($userID = 100004, $subscribeopenid = 'oOjPaszxPnbbU3CcPXA1OOuuUOfg', $recommendUserID = '100001')
    {
        $roleinfo = UserrolesModel::getUnOutRoles($userID);
        dump($roleinfo);

        $result = BizHelper::getDisplayRecommendInfo($userID, $subscribeopenid, $recommendUserID, '');
        dump($result);
    }

    public function shortenurlop($url = 'http://www.sina.com.cn')
    {
        $result = ShortenUrl::shorten($url);
        dump($result);
    }

    public function shortenurlop2($url = 'http://zhidao.baidu.com/link?url=-GXvXoFda2J5wC2_8Dmp8WHMy6qtdUMPFoOLxtM7Fz_-ZHUd0BwtnoCm7mIN7CCRvVR6GUsl8IBjVcLCAJHyGa')
    {
        $shortUrl = WechatHelper::shortenUrl($url);
        dump($shortUrl);
    }

    public function getlongurlop($url = 'http://hilandwechat.sinaapp.com/_sp/4')
    {
        $result = ShortenUrl::getLongUrl($url);
        dump($result);
    }

    public function childrenrolesop($roleid = 363)
    {
        $modelInfos = array(
            array('userroles', 'A', 'A.*'),
            array('userroles', 'B', ''),
            array('userinfo', 'C', 'C.displayname,C.headurl')
        );

        $onClauses = array(
            'A.parentid= B.roleid',
            'A.userid=C.userid'
        );


        $viewMate = new ViewMate($modelInfos, $onClauses);
        $where = "A.parentid=$roleid";
        $addon = 'ORDER BY A.roleid desc';
        $roles = $viewMate->select($where, $addon);
        $sql = $viewMate->showSql($where, $addon);

        dump($sql);
        dump($roles);
    }

    public function avatarop()
    {
        // 2???????
        $qrcodebgurl = PHYSICAL_ROOT_PATH . C('WEIXIN_RECOMMEND_BGPIC');

        // 3?????????????????????
        $imagebg = imagecreatefromjpeg($qrcodebgurl);
        $imagemegered = imagecreatetruecolor(imagesx($imagebg), imagesy($imagebg));
        imagecopy($imagemegered, $imagebg, 0, 0, 0, 0, imagesx($imagebg), imagesy($imagebg));

        $recommenduseravatar = 'http://wx.qlogo.cn/mmopen/znzHslBzEFd6G4ZBicmUmIvl5CXqqgK4qTcNfL6ialSicOf2G8OCPic922MN3rbloala7qYibdgAsaRworfByrwl0iaTYgKI7dhq6U/0';
        if (empty($recommenduseravatar)) {
            $recommenduseravatar = PHYSICAL_ROOT_PATH . C('WEIXIN_RECOMMEND_DEFAULTAVATAR');
        }

        //dump($recommenduseravatar);
        //ini_set("memory_limit", "60M");
        $imageavatar = ImageHelper::loadImage($recommenduseravatar);
        $imageavatarnew = ImageHelper::resizeImage($imageavatar, 88, 88);
        imagecopy($imagemegered, $imageavatarnew, 6, 13, 0, 0, imagesx($imageavatarnew), imagesy($imageavatarnew));

        header('Content-Type: image/jpeg');

        // ???
        imagejpeg($imagemegered);

        // ????
        imagedestroy($imagemegered);
    }

    public function imageop($info = 'png')
    {
        $url = '';
        $info = strtolower($info);
        switch ($info) {
            case 'png':
                $url = 'http://image27.360doc.com/DownloadImg/2011/04/2015/11077777_5.png';
                break;
            case 'jpg':
                $url = 'http://b.hiphotos.baidu.com/zhidao/pic/item/8326cffc1e178a8240e44d28f403738da877e85a.jpg';
                break;
            case 'bmp':
                $url = 'http://image2.958shop.com/p/2011/04/02/100430818717860.bmp';
                break;
            case 'un':
                $url = 'http://wx.qlogo.cn/mmopen/ajNVdqHZLLAecbK9Pz0ulNgnEhAayibmD0oqacxa3NicEhkwwev6BlOLRboBaj6GGdlxSxGL6TjJN5u8xrMruBXQ/0';
                break;
        }

        $imageType = ImageHelper::getImageType($url);
        //dump($imageType);

        //$url = iconv("UTF-8", "gb2312", $url);

        $imagemegered = ImageHelper::loadImage($url, $info); //imagecreatefromjpeg($url);

        //dump($imagemegered);

        ImageHelper::display($imagemegered, $imageType);
        imagedestroy($imagemegered);

//        ob_clean();
//        header('Content-Type:image/jpeg');
//
//        // ???
//        imagejpeg($imagemegered);
//        // ????
//        imagedestroy($imagemegered);
    }

    public function imageop3()
    {
        $url = 'http://app.rainytop.com/ott/public/temp/one_20141127170506698.bmp';
//        $srcData = '';
//        if (function_exists("file_get_contents")) {
//            $srcData = file_get_contents($url);
//        }
//
//        $image = @ImageCreateFromString($srcData);

        ini_set('memory_limit', '256M');
        $image = ImageHelper::imageCreateFromBMP($url);

        ob_clean();
        header('Content-Type:image/jpeg');
        imagejpeg($image);
    }

    public function imageop4()
    {
        $url = 'http://wx.qlogo.cn/mmopen/Xewa2JUmZ1rEUwEGkiacTianbWOZJ9g5TIgwQ5MlPUFVIaMFWGWGxMpm3xHlic3J5Twzq5Lm1c1Rz1VMpn7oWjOZ7E7UzqIAB1v/0';
        $srcData = '';
        if (function_exists("file_get_contents")) {
            $srcData = file_get_contents($url);
        }

        $image = @ImageCreateFromString($srcData);

        ob_clean();
        header('Content-Type:image/png');
        imagepng($image);
    }

    public function datehelperop()
    {
        dump(DateHelper::getTimestamp('2016-5-25'));
        dump(DateHelper::getTimestamp('2016-5-25 0:0'));
        dump(DateHelper::getTimestamp('2016-5-25 0:0:0'));
        dump(DateHelper::getTimestamp('2016-5-25 0:0:01'));
    }

    public function isprivateipop($ip = '192.168.1.1')
    {
        dump(EnvironmentHelper::isPrivateIP($ip));
    }

    public function ipop($ip = '192.168.1.1')
    {
        $result = preg_match(RegexHelper::IP, $ip);
        dump($result);
    }

    public function isloaclserverop($ip = '202.102.134.68')
    {
        dump(EnvironmentHelper::isLocalServer($ip));
    }

    public function imageop2()
    {
        dump(__ROOT__);
        $physicalRootPath = 'E:\\Applications\\one2three\\';//'E:\\MyWorkSpace\\MyProjectPHP\\多店铺商城\\shequfuwu\\';
        $savingImageRelativePhysicalPathFullName = 'Uploads\\xxxx.jpg';

//        if (StringHelper::isEndWith($physicalRootPath, '\\')) {
//            $physicalRootPath = StringHelper::subString($physicalRootPath, 0, strlen($physicalRootPath)-1);
//        }
//
//        if (StringHelper::isStartWith($savingImageRelativePhysicalPathFullName, '\\')) {
//            $savingImageRelativePhysicalPathFullName = StringHelper::subString($savingImageRelativePhysicalPathFullName, 1);
//        }
//
//        $result = $physicalRootPath . '\\' . $savingImageRelativePhysicalPathFullName;

        $filePhysicalFullName = $physicalRootPath . '\\' . $savingImageRelativePhysicalPathFullName;
        $image = ImageHelper::loadImage('http://n.sinaimg.cn/news/crawl/20160617/Rgv4-fxtfrrc3774857.jpg');
        $result = ImageHelper::save($image, $filePhysicalFullName);
        dump($result);
    }

    public function generateqrcodeop($userID = 100001)
    {
        $userMate = new ModelMate('userinfo');
        $userData = $userMate->get($userID);

        $result = BizHelper::generateAndSaveQRCode($userData);
        dump($result);
    }

    public function getaccesstokenop()
    {
        $result = WechatHelper::getAccessToken('', '', false);
        dump($result);
    }

    public function userinfoop()
    {
        $subscribeuserinfo = UserinfoModel::getByKey(100002);
        //dump($subscribeuserinfo);

        //$userData['weixinopenid']
        $userData['weixinname'] = $subscribeuserinfo['weixinname'];
        $userData['displayname'] = $subscribeuserinfo['displayname'];
        $userData['usersex'] = $subscribeuserinfo['usersex'];
        $userData['weixinopenid'] = 'oOjPas1SKwihAMngxQxCqmdYGiU4';
        $userData['userprovince'] = $subscribeuserinfo['userprovince'];
        $userData['usercity'] = $subscribeuserinfo['usercity'];
        $userData['usercountry'] = $subscribeuserinfo['usercountry'];
        $userData['headurl'] = $subscribeuserinfo['headurl'];
        $userData['jointime'] = time();

        $result = UserinfoModel::interact($userData);

        if ($result) {
            dump('ok');
        } else {
            dump('bad');
        }
    }

    public function getuserinfoop($openid = 'oOjPas1SKwihAMngxQxCqmdYGiU4')
    {
        $result = WechatHelper::getUserInfo($openid);
        dump($result);
    }


    public function extensionloadedop($modulename = 'exif')
    {
        if (extension_loaded($modulename)) {
            dump("$modulename 存在");
        } else {
            dump("$modulename NOOOOOOOOOOO");
        }
    }

    public function wechatop($longkey = 1000)
    {
        $accessToken = WechatHelper::getAccessToken('', '', falas);
        dump($accessToken);

        $qrTicket = WechatHelper::getQRTicket($longkey, $accessToken, 'QR_LIMIT_SCENE');
        dump($qrTicket);

        $qrTicket = WechatHelper::getQRTicket(100001, $accessToken, 'QR_SCENE');
        dump($qrTicket);

        //$qrTicket= BizHelper::getQRTicket(100001,'LONG');
        //dump($qrTicket);

        $qrUrl = BizHelper::getQRCodeUrl($longkey);
        dump($qrUrl);

//        $opts = array(
//            'http' => array(
//                'method' => "GET",
//                'header' => "User-Agent: Mozilla/5.0\n"
//            )
//        );
//        $context = stream_context_create($opts);
//        $srcData = file_get_contents($qrUrl, false, $context);


        $srcData= NetHelper::request($qrUrl);

        if (empty($srcData)) {
            die("图片源为空");
        }
        $image = @ImageCreateFromString($srcData);

        dump($image);
    }


    public function uploadimageop(){
        $file= 'E:\\Applications\\one2three\\Uploads\\userqrcode\\07919B62-84B9-85F7-E47E-EF23B317875C.jpg';
        //$file= 'E:\Applications\one2three\Uploads\userqrcode\07919B62-84B9-85F7-E47E-EF23B317875C.jpg';
        $mediaID= WechatHelper::uploadMedia($file);
        dump($mediaID);
    }


    public function launchPayConfirmop($id=59){
        $url= U("Tencent/PayInner/launchPayConfirm","id=$id");
        echo "<a href=$url>付款</a>";
        
        
    }

}


?>