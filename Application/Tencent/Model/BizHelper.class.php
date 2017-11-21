<?php
namespace Tencent\Model;

use Common\Model\UserinfoModel;
use Common\Model\UserrolesModel;
use Hiland\Common\CommonHelper;
use Think\Model;
use Vendor\Hiland\Biz\Tencent\WechatHelper;
use Vendor\Hiland\Utils\Data\DateHelper;
use Vendor\Hiland\Utils\Data\GuidHelper;
use Vendor\Hiland\Utils\Data\ObjectHelper;
use Vendor\Hiland\Utils\Data\OperationHelper;
use Vendor\Hiland\Utils\Data\StringHelper;
use Vendor\Hiland\Utils\DataConstructure\Queue;
use Vendor\Hiland\Utils\IO\ImageHelper;
use Vendor\Hiland\Utils\Web\EnvironmentHelper;
use Vendor\Hiland\Utils\Web\SaeHelper;
use Vendor\Hiland\Utils\Web\WebHelper;

class BizHelper
{

    private static $queue;

    

    /**
     * 在生成推广二维码
     *
     * @param array $recommendUser
     *            推荐人实体数据
     * @return array 二位数组 第一个元素weburl是sae storage的url,或者是本地图片的web路径（不包括虚拟目录信息）
     *         第二个元素physicalpath是sae的临时物理文件全路径，或本地全物理路径的
     */
    public static function generateAndSaveQRCode($recommendUser)
    {
        $bgType = null;//背景类型，留出来以后可以支持多种微信背景。
        //$bgType='dblc';//多步流程背景
        $bgType = 'xfbbd';//幸福不必等背景

        // 0、获取推荐人的基本信息
        $recommenduserid = 0;
        $isvip = false;

        // 1、获取带有用户信息作为参数的推广二维码

        // 如果是vip（vipid>0的）用户，此处填写vipid
        $qrcodepicurl = '';
        if (!empty($recommendUser)) {
            if ($recommendUser['vipid'] > 0) {
                $recommenduserid = $recommendUser['vipid'];
                $qrcodepicurl = BizHelper::getQRCodeUrl($recommenduserid, 'LONG');
                $isvip = true;
            } else {
                $recommenduserid = $recommendUser['userid'];
                $qrcodepicurl = BizHelper::getQRCodeUrl($recommenduserid, 'TEMP');
            }
        }

        //return $recommenduserid;

        $recommendusername = $recommendUser['displayname'];
        if (empty($recommendusername)) {
            $recommendusername = '平台特约会员';
        }

        $recommenduseravatar = $recommendUser['headurl'];

        // 2、加载背景图片
        $qrcodebgurl = PHYSICAL_ROOT_PATH . C('WEIXIN_RECOMMEND_BGPIC');
        $qrcodebgurl = str_replace('/', '\\', $qrcodebgurl);

        // 3、将推广二维码、用户头像、背景图片进行合并
        $imagebg = imagecreatefromjpeg($qrcodebgurl);
        $imagemegered = imagecreatetruecolor(imagesx($imagebg), imagesy($imagebg));
        imagecopy($imagemegered, $imagebg, 0, 0, 0, 0, imagesx($imagebg), imagesy($imagebg));

        if (empty($recommenduseravatar)) {
            $recommenduseravatar = PHYSICAL_ROOT_PATH . C('WEIXIN_RECOMMEND_DEFAULTAVATAR');
            $recommenduseravatar = str_replace('/', '\\', $recommenduseravatar);
        }

        $imageavatar = ImageHelper::loadImage($recommenduseravatar, 'non'); //由于服务器限制，此处指定图片扩展名为non，系统将使用curl的方式载入图片

        //return $qrcodepicurl;
        $imageqrcode = ImageHelper::loadImage($qrcodepicurl, 'non'); //imagecreatefromjpeg($qrcodepicurl);
        //dump($imageqrcode);

        switch ($bgType) {
            case 'xfbbd':
                $imageavatarnew = ImageHelper::resizeImage($imageavatar, 130, 130);
                imagecopy($imagemegered, $imageavatarnew, 15, 22, 0, 0, imagesx($imageavatarnew), imagesy($imageavatarnew));

                $imageqrcodenew = ImageHelper::resizeImage($imageqrcode, 188, 188);
                $imageqrcodenew = ImageHelper::cropImage($imageqrcodenew, 6, 6, 6, 6);
                imagecopy($imagemegered, $imageqrcodenew, 64, 692, 0, 0, imagesx($imageqrcodenew), imagesy($imageqrcodenew));
                break;
            case 'dblc':
                $imageavatarnew = ImageHelper::resizeImage($imageavatar, 88, 88);
                imagecopy($imagemegered, $imageavatarnew, 6, 13, 0, 0, imagesx($imageavatarnew), imagesy($imageavatarnew));

                $imageqrcodenew = ImageHelper::resizeImage($imageqrcode, 100, 100);
                $imageqrcodenew = ImageHelper::cropImage($imageqrcodenew, 6, 6, 6, 6);
                imagecopy($imagemegered, $imageqrcodenew, 64, 42, 0, 0, imagesx($imageqrcodenew), imagesy($imageqrcodenew));
                break;
            default:
                break;
        }

        // 4、添加文字
        $textfont = PHYSICAL_ROOT_PATH . C('WEIXIN_RECOMMEND_TEXTFONT');
        $textfont = str_replace('/', '\\', $textfont);

        switch ($bgType) {
            case 'xfbbd':
                $textcolor = imagecolorallocate($imagemegered, 255, 255, 255);
                imagefttext($imagemegered, 20, 0, 200, 105, $textcolor, $textfont, $recommendusername);

                $textcolor = imagecolorallocate($imagemegered, 155, 155, 155);
                if (!$isvip) {
                    $overduedate = DateHelper::addInterval(time(), 's', C('WEIXIN_QR_TEMP_EFFECT_SECONDS'));
                    $overduedate = date('Y-m-d', $overduedate);
                    imagefttext($imagemegered, 20, 0, 215, 995, $textcolor, $textfont, '本二维码将在[' . $overduedate . ']过期。');
                } else {
                    imagefttext($imagemegered, 20, 0, 235, 995, $textcolor, $textfont, '我是VIP会员，本二维码永久有效。');
                }
                break;
            case 'dblc':
                $textcolor = imagecolorallocate($imagemegered, 85, 85, 85);
                imagefttext($imagemegered, 16, 0, 94, 35, $textcolor, $textfont, $recommendusername);

                if (!$isvip) {
                    $overduedate = DateHelper::addInterval(time(), 's', C('WEIXIN_QR_TEMP_EFFECT_SECONDS'));
                    $overduedate = date('Y-m-d H:i:s', $overduedate);
                    imagefttext($imagemegered, 10, 0, 50, 150, $textcolor, $textfont, '本二维码将在[' . $overduedate . ']过期。');
                } else {
                    imagefttext($imagemegered, 10, 0, 95, 150, $textcolor, $textfont, '我是VIP会员，本二维码永久有效。');
                }
                break;
            default:
                break;
        }

        // 5、将合并后的图片保存在storage中
        $savedimagebasename = GuidHelper::newGuid() . '.jpg';
        $savedimagebasenamewithrelativepath = 'userqrcode/' . GuidHelper::newGuid() . '.jpg';

        $recommendpicurl = '';
        $uploadPath = '/Uploads/';

        if (EnvironmentHelper::getDepositoryPlateformName() == 'sae') {
            $domainname = C('WEIXIN_SAE_DOMAINNAME');
            $recommendpicurl = SaeHelper::saveImageResource($imagemegered, $savedimagebasenamewithrelativepath, $domainname);
        } else {
            $recommendpicurl = $uploadPath . $savedimagebasenamewithrelativepath;
            $fileFullName = PHYSICAL_ROOT_PATH . $recommendpicurl;
            $fileFullName = str_replace('/', '\\', $fileFullName);
            //return $fileFullName;
            $fileFullName = ImageHelper::save($imagemegered, $fileFullName);
        }

        $recommendpictemppath = '';

        if (EnvironmentHelper::getDepositoryPlateformName() == 'sae') {
            $recommendpictemppath = SaeHelper::saveTempImageResource($imagemegered, $savedimagebasename);
        } else {
            $recommendpictemppath = $fileFullName;
        }

        imagedestroy($imageavatar);
        imagedestroy($imageavatarnew);
        imagedestroy($imageqrcode);
        imagedestroy($imageqrcodenew);
        imagedestroy($imagebg);
        imagedestroy($imagemegered);

        return array(
            'weburl' => $recommendpicurl,
            'physicalpath' => $recommendpictemppath
        );
    }

    /**
     * 获取带参数微信公众平台二维码的地址
     *
     * @param int $key
     *            需要传递进入二维码中的参数，int类型，这里通常传递宣传推广人的id
     * @param string $qrEffectType
     *            二维码有效期类型，取值于 配置文件 WEIXIN_QR_EFFECTTYPES，分为：
     *            1、TEMP 表示临时二维码
     *            2、LONG 表示长效二维码
     * @param int $qrEffectSeconds
     * @return string
     */
    public static function getQRCodeUrl($key, $qrEffectType = 'TEMP', $qrEffectSeconds = 0)
    {
        $qrticket = self::getQRTicket($key, $qrEffectType, $qrEffectSeconds);
        $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($qrticket);
        return $url;
    }

    /**
     * 通过用户信息获取二维码地址
     * @param array $userInfo 本地用户信息
     * @param int $qrEffectSeconds 二维码的有效时间（仅对临时二维码有效）
     * @return string
     */
    public static function getQRCodeUrlByUser($userInfo, $qrEffectSeconds = 0)
    {
        $qrCodePicUrl = '';
        if (!empty($userInfo)) {
            if ($userInfo['vipid'] > 0) {
                $userID = $userInfo['vipid'];
                $qrCodePicUrl = BizHelper::getQRCodeUrl($userID, 'LONG');
            } else {
                $userID = $userInfo['userid'];
                $qrCodePicUrl = BizHelper::getQRCodeUrl($userID, 'TEMP', $qrEffectSeconds);
            }
        }

        return $qrCodePicUrl;
    }

    /**
     * 获取生成带参数微信公众平台二维码的ticket
     *
     * @param int $key
     *            需要传递进入二维码中的参数，int类型，这里通常传递宣传推广人的id
     * @param string $qrEffectType
     *            二维码有效期类型，取值于 配置文件 WEIXIN_QR_EFFECTTYPES，分为：
     *            1、TEMP 表示临时二维码
     *            2、LONG 表示长效二维码
     * @param int $qrEffectSeconds
     * @return string 微信公众平台二维码的ticket
     */
    public static function getQRTicket($key, $qrEffectType = 'TEMP', $qrEffectSeconds = 0)
    {
        $accessToken = WechatHelper::getAccessToken();

        if (empty($qrEffectSeconds)) {
            $qrEffectSeconds = C('WEIXIN_QR_TEMP_EFFECT_SECONDS');
        }

        $qrEffectType = strtoupper($qrEffectType);
        switch ($qrEffectType) {
            case 'QR_SCENE':
            case 'QR_LIMIT_SCENE':
                break;
            case 'TEMP':
                $qrEffectType = 'QR_SCENE';
                break;
            case 'LONG':
                $qrEffectType = 'QR_LIMIT_SCENE';
                break;
            default:
                $qrEffectType = C('WEIXIN_QR_EFFECTTYPES.' . C('WEIXIN_QR_EFFECTTYPE'));
                break;
        }

        $ticket = WechatHelper::getQRTicket($key, $accessToken, $qrEffectType, $qrEffectSeconds);
        return $ticket;
    }

    /**
     * 构建显示在用户微信页面中的通知结果所需要的数据
     *
     * @param string $title
     *            通知的标题
     * @param string $content
     *            通知的内容
     * @param string $noticeType
     *            通知的类型,可以接受的几个值为：
     *            success 操作成功
     *            warn 操作失败
     *            info 提示信息
     *            waiting 提示等待
     *
     * @param string $detailsUrl
     *            如果有详细信息，那么详细信息对应的可以继续跳转的url
     * @return string 通知数据
     */
    public static function buildResultNoticePageData($title, $content, $noticeType = 'success', $detailsUrl = '')
    {
        $noticeTypes = array(
            'success',
            'warn',
            'info',
            'waiting'
        );
        if (!in_array($noticeType, $noticeTypes)) {
            $noticeType = 'info';
        }
        $data['title'] = $title;
        $data['content'] = $content;
        $data['noticeType'] = $noticeType;
        $data['detailsUrl'] = $detailsUrl;

        return $data;
    }

    /**
     * 获取推荐用户的 角色(如果推荐人是一般用户)
     * 或者服务（如果推荐人是商家）
     *
     * @param $userID
     * @param $userOpenID
     * @param int $recommendUserID
     *            推荐人userid
     * @param string $displayItemPrefix 显示在角色项目前的前缀文本
     * @return string
     */
    public static function getDisplayRecommendInfo($userID, $userOpenID, $recommendUserID, $displayItemPrefix = '-->')
    {
        $result = '';

        // 根据推荐人$recommenduserid，获取推荐人基础信息,然后展示
        $recommendUserData = null;
        if ($recommendUserID > 0) {
            $recommendUserData = UserinfoModel::getByKey($recommendUserID);
        }

        $rowCount = 1;
        $allowDisplayItemCount = C('SYSTEM_ALLOW_DISPLAY_RECOMMEND_ITEMCOUNT');
        // 如果推荐人是商家，展示商家的服务列表
        if ((int)$recommendUserData['ismerchant'] == 1) {
            $createRoleBaseUrl = 'http://' . WebHelper::getHostName() . U("PayOuter/createRoleByMerchantService", "userID=$userID&userOpenID=$userOpenID");

            $serviceModel = new Model('merchantservice'); //M('merchantservice');
            $where['merchantid'] = $recommendUserID;
            $where['isusable'] = 1;
            $list = $serviceModel->where($where)->select();
            foreach ($list as $row) {
                if ($allowDisplayItemCount > 0 && $rowCount > $allowDisplayItemCount) {
                    break;
                }
                $rowCount++;

                $paraArray['serviceID'] = $row['id'];
                $createRoleUrl = WebHelper::attachUrlParameter($createRoleBaseUrl, $paraArray);
                $content = $row['servicename'];
                $result .= "$displayItemPrefix <a href='" . $createRoleUrl . "'> $content </a> " . StringHelper::getNewLineSymbol();
            }
        } else { // 如果推荐人是一般用户，则显示其所有未出局的角色
            $createRoleBaseUrl = 'http://' . WebHelper::getHostName() . U("PayOuter/createRoleByRecommendUserRole", "userID=$userID&userOpenID=$userOpenID");

            $outDegrees = array(
                0,
                1
            ); // ROLE_OUT_DEGREES
            $roles = UserrolesModel::getRoles($recommendUserID, 0, $outDegrees);


            foreach ($roles as $role) {
                if ($allowDisplayItemCount > 0 && $rowCount > $allowDisplayItemCount) {
                    break;
                }
                $rowCount++;

                $paraArray['recommendUserRoleID'] = $role['roleid'];
                $createRoleUrl = WebHelper::attachUrlParameter($createRoleBaseUrl, $paraArray);

                $roleTime = DateHelper::format($role['scantime']);
                $content = $role['rolename'] . "(创建时间$roleTime)";
                $result .= $displayItemPrefix . "<a href='" . $createRoleUrl . "'>$content</a>" . StringHelper::getNewLineSymbol();
            }
        }

        return $result;
    }

    /**
     * 付款后处理
     * @param int|string $roleKey
     *            角色id或角色guid
     */
    public static function payedDeal($roleKey)
    {
        $roleData = UserrolesModel::get($roleKey);
        $payTag = (int)$roleData['paytag'];//WEIXIN_PAY_TAGS
        switch ($payTag) {
            case 1:
                $userID = $roleData['userid'];
                self::setVIP($userID);
                break;
            case 0:
            default:
                self::ruDing($roleKey);
        }
    }

    /**
     * 角色入定
     *
     * @param int|string $roleKey
     *            角色id或角色guid
     * @param bool $isInnerPay 为外部（通过微信支付）付款还是内部转账
     * @return bool
     */
    public static function ruDing($roleKey, $isInnerPay = false)
    {
        $result = true;

        $roleData = UserrolesModel::get($roleKey);
        // 1 变更角色的入局状态
        $roleData['outdegree'] = 0; // ROLE_OUT_DEGREES
        $roleData['paidtime'] = time();

        // 如果采用代金券激活的方式，代金券比与缴费额度的倍数（代金券是实时到账，只是未激活）
        // 收入达到阈值时，收入才被激活。这个阈值通常是其最初开通这个角色时缴费的X倍。当收入达到或超过阈值时，收入激活为可用
        // 如果本参数设置为0，那么收入是实时激活的。
        $thresholdMultiple = (int)C('WEIXIN_ZIGE_ACTIVETHRESHOLDMULTIPLE');
        $userMoneyAmountNeedActive = 0;
        if ($thresholdMultiple == 0) {
            $roleData['moneyamountactived'] = 1;
            $roleData['moneyamountactivethreshold'] = 0;
        } else {
            $thresholdAmount = $roleData['price'] * $thresholdMultiple;

            $roleData['moneyamountactived'] = 0;
            $roleData['moneyamountactivethreshold'] = $thresholdAmount;
            $userMoneyAmountNeedActive = $thresholdAmount;
        }

        // 2 排位
        $recommendData = UserinfoModel::getByKey($roleData['recommenduserid']);
        if (((int)$recommendData['ismerchant']) == 1) {
            $roleData['parentid'] = 0;
        } else {
            $recommendRoleID = (int)$roleData['recommendroleid'];
            //dump($recommendRoleID);
            $parenetID = self::getRuDingParentRoleID($recommendRoleID);
            $roleData['parentid'] = $parenetID;
        }
        //dump($roleData);

        //dump($roleData['parentid']);

        $transModel = new Model();
        $transModel->startTrans();

        $resultRole = UserrolesModel::interact($roleData);

        if ($userMoneyAmountNeedActive != 0) {
            UserinfoModel::changeMoneyAmountNeedActive((int)$roleData['userid'], $userMoneyAmountNeedActive);
        }
        //dump($resultRole);

        // 3 更新层级统计信息

        // 4 记录费用流水 (如果父角色是商家，流水进入平台；如果父角色是普通会员，那么流水进入父角色)

        $financeDataIn = null;
        $financeDataIn['moneyamount'] = $roleData['price'];
        $financeDataIn['subjecttype'] = 1;//SYSTEM_FINANCE_SUBJECTS 资格收益
        $financeDataIn['relationuserid'] = $roleData['userid'];
        $financeDataIn['relationroleid'] = $roleData['roleid'];
        $financeDataIn['merchantid'] = $roleData['merchantid'];

        if ($isInnerPay) {
            $financeDataIn['payway'] = 10;//SYSTEM_FINANCE_PAY_WAYS 内部转账
        } else {
            $financeDataIn['payway'] = 20;//SYSTEM_FINANCE_PAY_WAYS 外部支付
        }

        if (((int)$roleData['parentid']) == 0) {
            $financeDataIn['userid'] = 0;
            $financeDataIn['roleid'] = 0;
        } else {
            $financeDataIn['roleid'] = $roleData['parentid'];

            $parentRoleData = UserrolesModel::get($roleData['parentid']);
            $financeDataIn['userid'] = $parentRoleData['userid'];
        }

        //dump($financeDataIn);
        $resultFlowIn = true;
        $resultFlowIn = FinanceHelper::flowIn($financeDataIn, false);

        $resultFlowOut = true;
        //如果是内部转账，需要记录转出情况
        if ($isInnerPay == true) {
            //dump(1111111111111);
            $financeDataOut = null;
            $financeDataOut['moneyamount'] = $roleData['price'];
            $financeDataOut['subjecttype'] = -1;//SYSTEM_FINANCE_SUBJECTS 资格申请
            $financeDataOut['merchantid'] = $roleData['merchantid'];
            $financeDataOut['userid'] = $roleData['userid'];
            $financeDataOut['roleid'] = $roleData['roleid'];
            $financeDataIn['payway'] = 10;//SYSTEM_FINANCE_PAY_WAYS 内部转账

            if (((int)$roleData['parentid']) == 0) {
                $financeDataOut['relationuserid'] = 0;
                $financeDataOut['relationroleid'] = 0;//$roleData['parentid'];
            } else {
                $financeDataOut['relationroleid'] = $roleData['parentid'];
                $relationRoleData = UserrolesModel::get($roleData['parentid']);
                $financeDataOut['relationuserid'] = $relationRoleData['userid'];
            }

            $resultFlowOutTemp = FinanceHelper::flowOut($financeDataOut, false);
            $resultFlowOut = OperationHelper::getResult($resultFlowOutTemp);
        }

        if ($resultRole && $resultFlowIn && $resultFlowOut) {
            $result = true;
            $transModel->commit();
        } else {
            $result = false;
            $transModel->rollback();
            CommonHelper::log('ruding result', ObjectHelper::getString($roleData), ObjectHelper::getString($result));
        }


        return $result;
    }

    /** @noinspection PhpInconsistentReturnPointsInspection
     * 获取角色入定的位置（即父角色的roleid）
     * @param $recommendRoleID
     * @param bool $creatQueue 在外部调用的时候，此值请设置true，保证能够创建队列
     * @return int
     */
    public static function getRuDingParentRoleID($recommendRoleID, $creatQueue = true)
    {
        if ($creatQueue) {
            self::$queue = new Queue();
        }

        $where['parentid'] = $recommendRoleID;
        $subRoles = UserrolesModel::getRoles(0, 0, null, $where, 'userid asc');
        $subRoleCount = count($subRoles);

        if ($subRoleCount < (int)C('WEIXIN_ZIGE_MAXSUBCOUNT')) {
            return $recommendRoleID;
        } else {
            foreach ($subRoles as $subRole) {
                self::$queue->push($subRole['roleid']);
            }
        }

        while ($roleid = self::$queue->pop()) {
            return self::getRuDingParentRoleID($roleid, false);
        }
    }

    public static function setVIP($userID)
    {
        $result = UserinfoModel::setVIPID($userID);
        if (!$result) {
            CommonHelper::log('set vip by pay', "userid:" . $userID, ObjectHelper::getString($result));
        }
    }
}

?>