<?php
namespace Tencent\Controller;

use Common\Common\ConfigHelper;
use Common\Model\UserinfoModel;
use Common\Model\UserrolesModel;
use Tencent\Model\FinanceHelper;
use Tencent\Model\Foo;
use Think\Controller;
use Think\Model;
use Vendor\Hiland\Biz\Tencent\WechatHelper;
use Vendor\Hiland\Utils\Data\ArrayHelper;
use Vendor\Hiland\Utils\Data\ByteHelper;
use Vendor\Hiland\Utils\Data\CipherHelper;
use Vendor\Hiland\Utils\Data\ColorHelper;
use Vendor\Hiland\Utils\Data\DBSetHelper;
use Vendor\Hiland\Utils\Data\FontHelper;
use Vendor\Hiland\Utils\Data\GuidHelper;
use Vendor\Hiland\Utils\Data\OperationHelper;
use Vendor\Hiland\Utils\Data\ReflectionHelper;
use Vendor\Hiland\Utils\Data\StringHelper;
use Vendor\Hiland\Utils\DataBase\DAOHelper;
use Vendor\Hiland\Utils\DataModel\ModelMate;
use Vendor\Hiland\Utils\IO\ImageHelper;
use Vendor\Hiland\Utils\IO\Images;
use Vendor\Hiland\Utils\Web\JavaScriptHelper;
use Vendor\Resource\Net\Mail\PHPMailer;
use Vendor\Resource\Net\Snoopy;

class BeeController extends Controller
{

    public function index()
    {
        dump('hello world!');
    }

    public function userfinanceop()
    {
        $data = array(
            'userid' => 10015,
            'roleid' => 1,
            'subjecttype' => 1,
            'moneyamount' => -9,
            'relationuserid' => 10001
        );

        // $finace = new UserfinanceModel();
        $result = FinanceHelper::flowOut($data);

        dump($result);
    }

    /**
     * 访问数组不存在的元素测试
     */
    public function accessarrayunexistelement()
    {
        $array = array(
            'province' => 'shangdong',
            'city' => 'zaozhuang'
        );

        dump($array['city']);
        dump($array['company']);
    }

    public function userroleop()
    {
//        $data = UserrolesModel::get('ddddddddddd');
//        $data['other'] = 'sssssssssssss';
//        $data['userid'] = 15;
//        $data['recommenduserid'] = 15;
//        $data['parentid'] = 15;
//        $data['price'] = 10;
//        $data['paidtime'] = time();
//        $data['masterid'] = 15;
//        $data['merchantid'] = 15;
//        $outdegrees = ConfigHelper::get1DArray('ROLE_OUT_DEGREES', '', 'value');
//        $data['outdegree'] = $outdegrees['OUT'];
//        $data['misc2'] = 'sssssssssssss';

        $json = '{"roleid":"142","roleguid":"076D5DD6-74ED-2B44-E1A5-374391BF8F60","rolename":"\u5546\u5bb6\u767d\u96ea \u7684\u6d3b\u52a81\u5206\u94b1\u5403\u5927\u9910 ","isdefault":"0","userid":"100004","recommenduserid":"100002","recommenddisplayname":"\u767d\u96ea","recommendroleid":"0","scantime":"1457050197","parentid":"0","price":"0.01","paidtime":"1457050223","moneyamount":0.03,"moneyamountactived":"1","moneyamountactivethreshold":"0.03","masterid":"0","merchantid":"100002","outdegree":"0","other":"","misc1":"0","misc2":""}';
        $data = json_decode($json, true);

        $result = UserrolesModel::interact($data);
        dump($result);
        dump(OperationHelper::getResult($result));
    }

    public function setvipidop()
    {
        $result = UserinfoModel::setVIPID(10017);
        dump($result);
    }

    public function imagetypeop()
    {
        // $picfilename= 'http://wx.qlogo.cn/mmopen/Q3auHgzwzM44uqqibwSBkCuaEyrZjJO6OOVv2fjG6rYaULNFdhGRsiaopXicex4a7Cz5B1axppsLFM2AkadvNbwPA/0';
        // $picfilename= 'http://wx.qlogo.cn/mmopen/Xewa2JUmZ1rEUwEGkiacTianbWOZJ9g5TIgwQ5MlPUFVIaMFWGWGxMpm3xHlic3J5Twzq5Lm1c1Rz1VMpn7oWjOZ7E7UzqIAB1v/0';
        $picfilename = PHYSICAL_ROOT_PATH . C('WEIXIN_RECOMMEND_BGPIC');
        $result = ImageHelper::getImageType($picfilename);
        dump($result);
    }

    public function responseCustomerServiceTextop($openid = 'oOjPas1SKwihAMngxQxCqmdYGiU4')
    {
        $content = '我是一个兵来自老百姓！';
        $result = WechatHelper::responseCustomerServiceText($openid, $content);

        dump($result);
    }

    public function jsonop()
    {
        $string = '{"errcode":40003,"errmsg":"invalid openid hint: [nz0OIa0332age7]"}';
        $decoded = json_decode($string);
        // $json= json

        dump($decoded->errcode);
        // dump($decoded['errcode']);
    }

    public function reflectionop()
    {
        $className = 'Tencent\Model\Foo2';
        $foo = new Foo();
        // $foo->getname();
        // $foo = ReflectionHelper::createInstance('Tencent\Model\Foo2');
        // $reuslt = $foo->getname();

        /*
         * $foo= reflection('Tencent\Model\Foo2');
         * $reuslt = $foo;
         */

        /*
         * $methodName= 'getname';
         * $reuslt= ReflectionHelper::executeMethod($className,$methodName,null,null);
         */

        /*
         * $methodName= 'echos';
         * $methodArgs= array('china');
         * $reuslt= ReflectionHelper::executeMethod($className,$methodName,null,$methodArgs);
         */

        /*
         * $methodName = 'display';
         * $methodArgs = array(
         * 'china'
         * );
         * $reuslt = ReflectionHelper::executeMethod($className, $methodName,null, $methodArgs);
         */

        $className = 'Tencent\Model\Bar';
        $constructArgs = array(
            'shandong'
        );
        $methodName = 'getname';
        $methodArgs = array(
            'china'
        );
        $reuslt = ReflectionHelper::executeMethod($className, $methodName, $constructArgs, $methodArgs);

        dump($reuslt);
    }

    public function byteop()
    {
        $bytecount = 456789032;
        $result = ByteHelper::friendlyDisplay($bytecount, ' ');
        dump($result);
    }

    public function merchantmodelop()
    {
        $data['id'] = 1;
        $data['servicename'] = 'tttttttttt';
        $data['price'] = 9;
        $data['memo'] = GuidHelper::newGuid();

        $mate = new ModelMate('merchantservice');
        $result = $mate->interact($data);

        dump($result);
    }

    public function merchantserviceop()
    {
        if (IS_GET) {
            $this->display();
        } else {
            $mate = new ModelMate('merchantservice');
            $result = $mate->interact();
            dump($result);
        }
    }

    public function merchantop()
    {
        if (IS_GET) {
            $this->display();
        } else {
            $mate = new ModelMate('merchant');
            $result = $mate->interact();
            dump($result);
        }
    }

    public function mailop()
    {
        $toaddress = 'develope@163.com';
        $fromaddress = '9727005@qq.com';
        $fromname = '我是解大然';

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->CharSet = 'utf-8'; // C('MAIL_CHARSET');
        $mail->AddAddress($toaddress);
        $mail->Body = 'hello world!!!!';
        $mail->From = $fromaddress; // C('MAIL_ADDRESS');
        $mail->FromName = $fromname;
        $mail->Subject = '我是解大然，祝福你好';
        $mail->Host = 'smtp.qq.com';
        $mail->SMTPAuth = true; // C('MAIL_AUTH');
        // $mail->SMTPSecure='ssl';
        $mail->Port = 25;
        $mail->Username = '9727005@qq.com';
        $mail->Password = 'qingdao158416'; // C('MAIL_PASSWORD');
        $mail->IsHTML(false);

        // 以下代码发送附件有问题，需要继续验证
        $mail->addAttachment(PHYSICAL_ROOT_PATH . PATH_SEPARATOR . 'install.php');
        // return $mail->Send();
        $result = $mail->Send();
        dump($result);
    }

    public function convertkeyvalueop()
    {
        $originalarray = C('SITEUSINGTYPES');
        dump($originalarray);

        $newarray = ArrayHelper::convert1DKeyValue($originalarray);
        dump($newarray);
    }

    public function convert2dTo1dop()
    {
        $originalarray = C('ROLE_OUT_DEGREES');
        // $newarray= ArrayHelper::convert2DTo1D($originalarray, 'value', 'display');
        // $newarray= ArrayHelper::convert2DTo1D($originalarray, 'value');
        $newarray = ArrayHelper::convert2DTo1D($originalarray, '', 'display');
        dump($newarray);
    }

    public function jsapiticketop()
    {
        $result = WechatHelper::getJsApiTicket();
        dump($result);
    }

    public function mergearrayop()
    {
        $ss['a'] = 'shandong';
        $ss['m'] = 'shanxi';
        $tt['b'] = 'liaoning';
        $ss = array_merge($ss, $tt);
        dump($ss);

        //数组跟null合并，结果为null
        $pp = null;
        $pp = array_merge($ss, $pp);
        dump($pp);

        dump(ArrayHelper::merge($ss, $pp));
    }

    public function getuserunoutrolesop()
    {
        $userID = 100001;
        $merchantID = 100001;
        $result = UserrolesModel::getUnOutRoles($userID, $merchantID);

        dump($result);
    }

    public function showphp()
    {
        phpinfo();
    }

    public function dbop($driver = 'mysql')
    {
        $dao = DAOHelper::Instance($driver);

        // dump($dao);

        $sql = 'select * from  ot33binbin_userinfo';

        $result = $dao->query($sql);
        // dump($result);

        // while ($row= $dao->fetchArray($result,3)){
        // dump($row);
        // }

        // while ($row= $dao->fetchAssoc($result)){
        // dump($row);
        // }

        // while ($row= $dao->fetchRow($result)){
        // dump($row);
        // }

        // while ($row= $dao->fetchObject($result)){
        // dump($row);
        // }

        /*
         * $rowCount= $dao->getResultRowCount($result);
         * dump($rowCount);
         */

        // $lastID = $dao->getLastInsertedID();
        // dump($lastID);

        // dump($result);
        // $dao->showFieldNames('ot33binbin_userinfo');

        $tableNames = $dao->getTableNames();
        dump($tableNames);

        $dao->showTableNames();
    }

    public function encryptop()
    {
        $data = "I Love China!";

        $encrypted = CipherHelper::encryt($data);
        dump($encrypted);

        $decrypted = CipherHelper::decryt($encrypted);
        dump($decrypted);
    }

    public function snoopyop()
    {
        $snoopy = new Snoopy();
        $url = 'http://www.baidu.com';
        $snoopy->fetchtext($url);

        dump($snoopy->results);
    }

    public function imagesop()
    {
        $url = 'http://www.sinaimg.cn/dy/slidenews/2_img/2016_05/72682_1705091_624995.jpg';
        $image = new Images($url);

        $image->setMaskPosition(3);

        $textfont = PHYSICAL_ROOT_PATH . C('WEIXIN_RECOMMEND_TEXTFONT');

        /*
         * $image->setMaskWord('Wecome to 山东润拓');
         * $image->setMaskFont($textfont);
         * $image->setMaskFontSize(20);
         */

        $logo = PHYSICAL_ROOT_PATH . '/Public/Admin/images/login_logo.png';
        $image->setMaskImage($logo);
        $image->setMaskOffsetY(-30);

        $image->setImageBorder();
        // $image->flipX();
        // $image->flipY();
        // $image->flipY();

        /*
         * $image->setCutType(2);
         * $image->setCutRectangle(600, 500);
         * $image->setCutPositionOnSourceImage(100,20);
         */

        $destImage = $image->createImage(600, 500);
        ImageHelper::display($destImage, 'jpg', 80);

        // dump($image->getImageWidth());
    }

    public function colorop()
    {
        $dexColor = '#0122ff';
        $rgbArray = ColorHelper::Hex2RGB($dexColor);
        dump($rgbArray);

        $convertedColor = ColorHelper::RGB2Hex($rgbArray[0], $rgbArray[1], $rgbArray[2]);
        dump($convertedColor);
    }

    public function imagefunctionop($extname = 'jpg')
    {
        dump(ImageHelper::getImageCreateFunction($extname));
        dump(ImageHelper::getImageOutputFunction($extname));
    }

    public function fontop()
    {
        $font = PHYSICAL_ROOT_PATH . C('WEIXIN_RECOMMEND_TEXTFONT');
        $fontSize = 25;
        $string = '社会主义中国！very good!';
        $result = FontHelper::getSize($font, $fontSize, $string);

        dump($result);
    }

    public function configop()
    {
        dump(ConfigHelper::get1DArray('ROLE_OUT_DEGREES', '', 'display'));
        dump($noticeStatuses = ConfigHelper::get1DArray('PAY_INNER_NOTICE_STATUSES', 'value', 'display'));
    }

    public function excuteMethodop()
    {
        $className = 'Common\Common\ConfigHelper';
        $methodArgs = array(
            'PAY_INNER_NOTICE_STATUSES',
            'value',
            'display'
        );
        $result = executeMethod($className, 'get1DArray', null, $methodArgs);
        dump($result);
    }

    // 当设定的“表类型”为MyISAM时是不支持事务操作的。
    // 如果要让事务能够执行，需要将表的类型设置为InnoDB
    public function transactionop($noticeid = 0, $financeid = 0)
    {
        $model = new Model();

        $modelA = M('innerpaynotice');
        $modelB = M('userfinance');

        $model->startTrans();
        // $modelA->startTrans();
        // $modelB->startTrans();

        $resultA = $modelA->where("id=$noticeid")->delete();
        $resultB = $modelB->where("financeid=$financeid")->delete();

        if ($resultA && $resultB) {
            $model->commit();
            // $modelA->commit();
            // $modelB->commit();
            $result = "提交成功";
        } else {
            $model->rollback();
            // $modelA->rollback();
            // $modelB->rollback();
            $result = "数据已经回滚";
        }

        // //让开启事务和提交或回滚事务的model不是一个，进行测试
        // $modelNew= new Model();
        // $modelNew->rollback();
        // $result = "数据已经回滚";

        dump($result);
    }

    public function transactionop2()
    {
        $model = new Model();
        $model->startTrans();
        UserinfoModel::changeMoneyAmount(100002, 1);

        $userData = UserinfoModel::getByKey(100002);
        $userData['moneyamount'] -= 0.01;
        UserinfoModel::interact($userData);
        //UserinfoModel::changeMoneyAmount(100002,0.5);
        $model->commit();
    }

    public function javascriptop()
    {
        // JavaScriptHelper::alertLocation('hello world.', 'http://www.163.com');
        JavaScriptHelper::alertClose('hello world!');
        // JavaScriptHelper::headerUrl('http://www.163.com');
    }

    public function systemconstop()
    {
        $ns = __NAMESPACE__;
        $cn = __CLASS__;
        dump("namespace:$ns; classname:$cn");
    }

    public function friendlydisplayop()
    {
        $model = D('merchantservice');
        $where = null;
        $where['merchantid'] = 100001;
        $list = $model->where($where)->select();

        $friendlyMaps = null;
        // $friendlyMaps= array(
        // 'isusable' => C('HILAND_SYSTEM_USABLESTATUS')
        // );

        $friendlyFuncs = array(
            'isusable' => __CLASS__ . '|booo'
        );

        DBSetHelper::friendlyDisplay($list, $friendlyMaps, $friendlyFuncs);

        dump($list);
    }

    function booo($value)
    {
        if ($value) {
            return 'gooddddddddddd';
        } else {
            return 'bad!!!!!!!!';
        }
    }

    public function stringop()
    {
        $data = "中国I love u!";
        $result1 = substr($data, 0, 5);
        $result2 = StringHelper::subString($data, 0, 5);

        dump($result1);
        dump($result2);
    }
}

?>