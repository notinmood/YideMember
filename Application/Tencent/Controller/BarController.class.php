<?php
namespace Tencent\Controller;

use Common\Model\UserinfoModel;
use Hiland\Common\ExtentiblerepositoryModel;
use Tencent\Model\BizHelper;
use Think\Controller;
use Vendor\Hiland\Biz\Tencent\Common\WechatConfig;
use Vendor\Hiland\Biz\Tencent\Pay\WxPayApi;
use Vendor\Hiland\Biz\Tencent\Pay\WxPayData\WxPayDataBaseReverse;
use Vendor\Hiland\Biz\Tencent\WechatHelper;
use Vendor\Hiland\Utils\Data\ArrayHelper;
use Vendor\Hiland\Utils\Data\ChineseHelper;
use Vendor\Hiland\Utils\Data\CipherHelper;
use Vendor\Hiland\Utils\Data\DateHelper;
use Vendor\Hiland\Utils\Data\ExtentibleRepository;
use Vendor\Hiland\Utils\Data\GuidHelper;
use Vendor\Hiland\Utils\Data\ObjectHelper;
use Vendor\Hiland\Utils\Data\RandHelper;
use Vendor\Hiland\Utils\Data\StringHelper;
use Vendor\Hiland\Utils\IO\FileHelper;
use Vendor\Hiland\Utils\IO\ImageHelper;
use Vendor\Hiland\Utils\Web\ClientHelper;
use Vendor\Hiland\Utils\Web\EnvironmentHelper;
use Vendor\Hiland\Utils\Web\HttpHeader;
use Vendor\Hiland\Utils\Web\MimeHelper;
use Vendor\Hiland\Utils\Web\SaeHelper;
use Vendor\Hiland\Utils\Web\WebHelper;
use Vendor\Hiland\Utils\Web\Widget\TreeTable;

class BarController extends Controller
{
    // -------------------------------------------------------------------------
    // 以下方法均为功能验证方法，具体场景下调用的demo
    public function showaccesstoken()
    {
        $accesstoken = WechatHelper::getAccessToken();
        $this->show($accesstoken);
    }

    public function showqrticket($key = 100001, $effecttype = long)
    {
        //$keyvalue = 10001;
        $ticket = BizHelper::getQRTicket($key, $effecttype);

        // dump($ticket );
        $this->show($ticket);
    }

    public function showqrcodeurl($key = 100001, $effecttype = long)
    {
        //$keyvalue = 10001;
        $url = BizHelper::getQRCodeUrl($key, $effecttype);
        $this->show($url);
    }

    /**
     * @param int $key
     * @param $effecttype
     */
    public function showqrcode($key = 100001, $effecttype = long)
    {
        //$keyvalue = 10001;
        $url = BizHelper::getQRCodeUrl($key, $effecttype);
        //$pic = file_get_contents($url);
        $this->show("<img src=$url />");
    }

    public function imageop()
    {
        $recommenduserid = 10017;
        // 1、获取带有用户信息作为参数的推广二维码
        $qrcodepicurl = BizHelper::getQRCodeUrl($recommenduserid);

        // 2、加载背景图片
        $qrcodebgurl = PHYSICAL_ROOT_PATH . C('WEIXIN_RECOMMEND_BGPIC');

        // 3、将二者进行合并
        $imagebg = imagecreatefromjpeg($qrcodebgurl);
        $imageqrcode = imagecreatefromjpeg($qrcodepicurl);

        $imagemegered = imagecreatetruecolor(imagesx($imagebg), imagesy($imagebg));
        imagecopy($imagemegered, $imagebg, 0, 0, 0, 0, imagesx($imagebg), imagesy($imagebg));

        $imageqrcodenew = ImageHelper::resizeImage($imageqrcode, 200, 200);
        $imageqrcodenew = ImageHelper::cropImage($imageqrcodenew, 14, 14, 14, 14);
        imagecopy($imagemegered, $imageqrcodenew, 100, 160, 0, 0, imagesx($imageqrcodenew), imagesy($imageqrcodenew));

        // 4、添加文字
        $textfont = PHYSICAL_ROOT_PATH . C('WEIXIN_RECOMMEND_TEXTFONT');
        $textcolor = imagecolorallocate($imagemegered, 63, 72, 204);
        imagefttext($imagemegered, 20, 0, 145, 60, $textcolor, $textfont, '解自然');
        imagefttext($imagemegered, 16, 0, 30, 140, $textcolor, $textfont, '让我们共同开启环球创富之路！');

        $overduedate = DateHelper::addInterval(time(), 's', C('WEIXIN_QR_TEMP_EFFECT_SECONDS'));
        $overduedate = date('Y-m-d H:i:s', $overduedate);
        imagefttext($imagemegered, 12, 0, 60, 380, $textcolor, $textfont, '本二维码将在[' . $overduedate . ']过期。');

        // 5、将合并后的图片保存在storage中
        $savedimagebasename = 'userqrcode/' . GuidHelper::newGuid() . '.jpg';
        $domainname = 'sansanbinbin';
        $url = SaeHelper::saveImageResource($imagemegered, $savedimagebasename, $domainname);
        $this->show("<img src='" . $url . "'>");

        imagedestroy($imageqrcodenew);
        imagedestroy($imagebg);
        imagedestroy($imageqrcode);
        imagedestroy($imagemegered);
    }

    public function dateop()
    {
        /*
         * $interval= new \DateInterval('P1D');
         *
         * $target= new \DateTime();
         * $mm= $target->add($interval);
         *
         * //$this->show(date('Y-m-d H:i:s', $target));
         * dump($mm);
         */
        $targettime = DateHelper::addInterval(time(), 'm', 100);
        dump(date('Y-m-d H:i:s', $targettime));
    }

    /**
     * 测试向微信公众平台上传媒体文件
     */
    public function uploadweixinmedia()
    {
        $mediafilename = PHYSICAL_ROOT_PATH . C('WEIXIN_RECOMMEND_BGPIC');
        $result = WechatHelper::uploadMedia($mediafilename);
        dump($result);
    }

    /**
     *
     * @param string $openid
     *            关注用户的openid，这个值来源于用户关注平台时信息中的 fromusername
     *            （oOjPas1SKwihAMngxQxCqmdYGiU4是一个关注的用户，实际中请修改为实际值后后进行测试）
     */
    public function showweixinuserinfo($openid = 'oOjPas9Yl4uOxEEPlhQhTmvPx7dI')
    {
        $userinfo = WechatHelper::getUserInfo($openid);
        dump($userinfo);
    }

    public function fileop()
    {
        $filename = 'http://sports.sina.com.cn/china/j/2016-01-05/doc-ifxneept3737209.shtml';
        $basename = FileHelper::getFileBaseName($filename);
        $extensionname = FileHelper::getFileExtensionName($filename);
        $dirname = FileHelper::getFileDirName($filename);
        $fileprefixname = FileHelper::getFileBaseNameWithoutExtension($filename);

        $this->show('文件的路径为：' . $dirname . ',文件的基本名称为：' . $basename . ',不带后缀的文件名为' . $fileprefixname . ',文件的扩张名称为' . $extensionname);
    }

    /**
     * 添加用户/更新用户测试
     */
    public function adduser()
    {
        $userinfo['weixinname'] = GuidHelper::newGuid();
        $userinfo['displayname'] = GuidHelper::newGuid();
        $userinfo['usersex'] = 1;
        $userinfo['weixinopenid'] = GuidHelper::newGuid();
        $userinfo['userprovince'] = 'province';
        $userinfo['usercity'] = 'city';
        $userinfo['usercountry'] = 'country';
        $userinfo['headurl'] = 'subscribeuserinfo->headimgurl';
        $userinfo['jointime'] = time();

        $result = UserinfoModel::interact($userinfo);
        dump($result);
    }

    /**
     *
     * @param string $openid
     */
    public function getuserbyopenid($openid = 'oOjPas9Yl4uOxEEPlhQhTmvPx7dI')
    {
        $userinfo = UserinfoModel::getbyopenid($openid);
        dump($userinfo);
    }

    /**
     * 根据传进来的id智能识别是userid还是vipid，返回用户信息
     *
     * @param unknown $id
     */
    public function getuserbyid($id = 6)
    {
        dump(UserinfoModel::getbyid($id));
    }

    /**
     * php操作符 -> => []的使用测试
     * 1、-> 用于对象成员的赋值和调用
     * 2、=> [] 用于数组元素的赋值和调用
     */
    public function phpop()
    {
        $userinfo = new UserinfoModel();
        // 使用-> 给对象的成员（属性、方法）进行赋值
        $userinfo->displayname = 'zhangsan';
        // 使用=> 给数组的元素赋值
        $arr = array(
            'first' => 'beijing',
            'second' => 'shanghai'
        );

        // 使用-> 调用对象的成员（属性、方法）
        $username = $userinfo->displayname;
        // 使用[] 调用数组的元素
        $location = $arr['second'];

        $this->show($username . '居住在' . $location);
    }

    public function emptyop()
    {
        $data = false;
        if (empty($data)) {
            $this->show('false is empty<br/>');
        } else {
            $this->show('false 不是 empty<br/>');
        }

        $data = 0;
        if (empty($data)) {
            $this->show('0 is empty<br/>');
        } else {
            $this->show('0 不是 empty<br/>');
        }

        $data = "0";
        if (empty($data)) {
            $this->show('字符串‘0’ is empty<br/>');
        } else {
            $this->show('字符串‘0’ 不是 empty<br/>');
        }

        $data = "00";
        if (empty($data)) {
            $this->show('字符串‘00’ is empty<br/>');
        } else {
            $this->show('字符串‘00’ 不是 empty<br/>');
        }

        $data = "";
        if (empty($data)) {
            $this->show('空字符串 is empty<br/>');
        } else {
            $this->show('空字符串 不是 empty<br/>');
        }

        $data = " ";
        if (empty($data)) {
            $this->show('空格 is empty<br/>');
        } else {
            $this->show('空格 不是 empty<br/>');
        }

        $data = null;
        if (empty($data)) {
            $this->show('null is empty<br/>');
        } else {
            $this->show('null 不是 empty<br/>');
        }

        $noset; // 专门测试未赋值的变量
        if (empty($noset)) {
            $this->show('未赋值变量 is empty<br/>');
        } else {
            $this->show('未赋值变量 不是 empty<br/>');
        }
    }

    /**
     * 获取用户未出局的角色
     *
     * @param string $userid
     */
    public function getunoutrole($userid = '10010')
    {
        $userrole = UserinfoModel::getunoutrole($userid);
        dump($userrole);
    }

    public function objectop()
    {
        /*
         * $arr = array('ssssid'=>1,'weixinname2'=>'aaa');
         * $userinfo= new UserinfoModel();
         * $userinfo->weixinname='ppp';
         * $userinfo= ObjectHelper::arrayToComplexOjbect($arr, $userinfo);
         *
         * dump($userinfo);
         */

        // 二维数组
        $arr = array(
            'zhangsan' => array(
                'id' => 1,
                'name' => 'aaa'
            ),
            'lisi' => array(
                'id' => 2,
                'name' => 'bbb'
            )
        );

        // 一维数组
        // $arr= array('id' => 1,
        // 'name' => 'aaa');

        // $arr= array('zhangsan','lisi');

        $arr = ObjectHelper::arrayToObject($arr);
        $arr = ObjectHelper::objectToArray($arr);

        dump($arr);

        // $userinfo = UserinfoModel::getbyid(10001);
        // $u = new UserinfoModel();
        // $u = ObjectHelper::arrayToComplexOjbect($userinfo, $u);
        // $u->username = 'aaaa';
        // $u = $u->update($u);

        // dump($u);
    }

    public function showrootpath()
    {
        $this->show(PHYSICAL_ROOT_PATH);
    }

    public function getserverinfo()
    {
        $hostname = WebHelper::getHostName();
        $this->show($hostname);
    }

    public function getclientinfo()
    {
        $ip = ClientHelper::getOnlineIP();
        $ipplace = json_encode(ClientHelper::getPlaceFromIP());

        $iplocal = ClientHelper::getLocalIP();
        $os = ClientHelper::getOS();
        $browser = ClientHelper::getBrowser();
        $language = ClientHelper::getLanguage();

        $content = printf('ip:%s,iplocal:%s,place:%s,os:%s,browser:%s,language:%s', $ip, $iplocal, $ipplace, $os, $browser, $language);
        $this->show($content);
    }

    public function redirectpage1()
    {
        WebHelper::redirectUrl("http://www.sina.com.cn");
    }

    public function redirectpage2()
    {
        WebHelper::redirectUrl(U('Index/getclientinfo'));
    }

    /**
     * 读取培训内的数组元素信息
     */
    public function getarraysettingitem()
    {

        // 1 可以
        /*
         * $value= C('WEIXIN_LOG_MODES');
         * $value= $value[C('WEIXIN_LOG_MODE')];
         */

        // 2 可以
        $value = C("WEIXIN_LOG_MODES." . C("WEIXIN_LOG_MODE"));

        // 3 不可以
        // $value= C ( "WEIXIN_LOG_MODES")[C ( "WEIXIN_LOG_MODE" )];
        dump($value);
    }

    public function stringiscontains()
    {
        $substring = "!";
        $wholestring = "Hello world!";

        $result = StringHelper::isContains($wholestring, $substring); // strstr($wholestring, $substring);
        dump($result);
    }

    public function attachurlpara()
    {
        $url1 = "http://www.sina.com.cn/";
        $url2 = "http://www.sina.com.cn/?s=kk";

        $paraArray = array(
            'a' => '中国',
            'c' => 'd',
            'm' => 'ssssssssssssss',
            'n' => '7777',
            'y' => '6666'
        );
        $url1 = WebHelper::attachUrlParameter($url1, $paraArray, true);
        $url2 = WebHelper::attachUrlParameter($url2, $paraArray);

        $paraExclude = array(
            'c',
            'n',
            'a'
        );

        $pastring = WebHelper::formatArrayAsUrlParameter($paraArray, true, $paraExclude);

        dump("url1:$url1 ; url2:$url2 ; para:$pastring");
    }

    public function wxpayfunction()
    {
        $this->show(WechatConfig::APPID);
    }

    public function arraytest()
    {
        $info = array(
            'city1' => 'beijing',
            'city2' => 'shanghai',
            'city3' => 'qingdao',
            'student' => array(
                'name' => 'zhangsan',
                'sex' => 1,
                'school' => 'qdu'
            )
        );

        $xml = ArrayHelper::Toxml($info);

        /*
         * $json= json_encode($array);
         * $xml= XmlHelper::toxml($json);
         */

        dump($xml);
    }

    /**
     * 获取参数的名称测试
     */
    public function getvarname()
    {
        $aaaa = "sssssssssssssss";
        $name = ObjectHelper::getVarName($aaaa, get_defined_vars());
        // $name = ObjectHelper::getVarName($aaaa);
        $display = "变量的名称为：" . $name;
        // $this->show($display);

        $info = array(
            'city1' => 'beijing',
            'city2' => 'shanghai',
            'city3' => 'qingdao',
            'student' => array(
                'name' => 'zhangsan',
                'sex' => 1,
                'school' => 'qdu'
            )
        );

        $this->show(ObjectHelper::getVarName($info, get_defined_vars()));
    }

    public function quoteusing()
    {
        // PHP中值信息尽量用单引号,HTML代码全部用双引号
        // PHP的字符串中包含变量的时候,用双引号可以简化操作
        $name = 'zhangsan';

        // 单引号里面的 $变量名 不被替换。
        $this->show('my name is $name');
        // 双引号里面的 $变量名 会被替换。
        $this->show("my name is $name");
    }

    public function tabledisplay()
    {
        $treecontent = array(
            1 => array(
                'id' => '1',
                'parentid' => 0,
                'name' => '一级栏目一'
            ),
            2 => array(
                'id' => '2',
                'parentid' => 0,
                'name' => '一级栏目二'
            ),
            3 => array(
                'id' => '3',
                'parentid' => 1,
                'name' => '二级栏目一'
            ),
            4 => array(
                'id' => '4',
                'parentid' => 1,
                'name' => '二级栏目二'
            ),
            5 => array(
                'id' => '5',
                'parentid' => 2,
                'name' => '二级栏目三'
            ),
            6 => array(
                'id' => '6',
                'parentid' => 3,
                'name' => '三级栏目一'
            ),
            7 => array(
                'id' => '7',
                'parentid' => 3,
                'name' => '三级栏目二'
            )
        );

        $treetable = new TreeTable();

        $tableProperty = array(
            'class' => 'gridtable',
            'id' => 'mytableid'
        );
        $trProperty = array(
            'class' => 'gridtr',
            'sid' => 'mytableid'
        );
        $tdProperty = array(
            'class' => 'gridtd',
            'ff' => 'mytableid'
        );
        $treetable->init($treecontent, $tableProperty, $trProperty, $tdProperty);
        $tablehtml = $treetable->generateTreeTable();
        // $tablehtml= "<table class='gridtable'>$tablehtml</table>";
        $tablehtml .= '<style type="text/css">
table.gridtable {
	font-family: verdana,arial,sans-serif;
	font-size:11px;
	color:#333333;
	border-width: 1px;
	border-color: #666666;
	border-collapse: collapse;
}
table.gridtable th {
	border-width: 1px;
	padding: 8px;
	border-style: solid;
	border-color: #666666;
	background-color: #dedede;
}
table.gridtable td {
	border-width: 1px;
	padding: 8px;
	border-style: solid;
	border-color: #666666;
	background-color: #ffffff;
}
</style>';
        $this->show($tablehtml);
    }

    public function infolog()
    {
        if (C("WEIXIN_LOG_MODE") >= 0) {
            $data = D("infolog");

            $data->guid = GuidHelper::newGuid();
            $data->title = "日志测试";
            $data->content = "日志内容";
            $data->category = C("WEIXIN_LOG_MODES." . C("WEIXIN_LOG_MODE"));
            $data->other = "oooooooooo";
            $data->misc1 = 100;
            $data->misc2 = 'sssssssssssss';
            $data->createtime = time();

            if ($data->add()) {
                $this->show("日志操作成功！");
                // $this->success ( '操作成功！' );
            } else {
                $this->show("日志写入错误！");
                // $this->error ( '写入错误！' );
            }
        }
    }

    public function getallheader()
    {
        $headers = HttpHeader::getAll();
        dump($headers);
    }

    public function getUserAgent()
    {
        $data = HttpHeader::getUserAgent();
        dump($data);
    }

    public function getmime()
    {
        $filename = "http://www.php186.com/index.php/content/article/web/108.html";
        $mime = MimeHelper::getMime($filename);
        dump($mime);
    }

    public function getphpversion()
    {
        $version = EnvironmentHelper::getPHPVersion();
        dump($version);
    }

    public function chineseop()
    {
        $chinesedata = '中国，I love you!';
        $unicodedata = ChineseHelper::unicodeEncode($chinesedata, 'UTF-8');
        $chineseConverted = ChineseHelper::unicodeDecode($unicodedata);

        dump('unicode编码后的信息为：' . $unicodedata . '<br/>');
        dump('解码后的信息为：' . $chineseConverted);

        dump(urldecode('%u4e2d%u56fd'));

        dump(json_decode('\u4e2d\u56fd'));

        $res = '\u4e2d\u56fd';

        $json = preg_replace("#\\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $res);
        dump($json);
        dump(ChineseHelper::unicodeDecode($res, 'UTF-8'));
    }

    public function cipherop()
    {
        $data = "I love china.";
        $key = 'p123';
        $sign = CipherHelper::signature($data, $key);

        $result = CipherHelper::verifySignature($data, $sign, $key);

        dump($sign . '<br/>');

        dump($result);
    }

    public function randop()
    {
        $result = RandHelper::rand(10);
        dump($result);
    }

    public function dirname()
    {
        $path = PHYSICAL_ROOT_PATH . DIRECTORY_SEPARATOR . 'ThinkPHP' . DIRECTORY_SEPARATOR . 'Library' . DIRECTORY_SEPARATOR . 'Vendor' . DIRECTORY_SEPARATOR . 'Hiland' . DIRECTORY_SEPARATOR . 'Biz' . DIRECTORY_SEPARATOR . 'Tencent' . DIRECTORY_SEPARATOR . 'Common' . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR;
        $certfilearray = array(
            $path . 'apiclient_cert.pem',
            $path . 'apiclient_key.pem',
            $path . 'rootca.pem'
        );
        dump($certfilearray);
    }

    public function joinop()
    {
        $patharray = array(
            'ThinkPHP',
            'Library',
            'Vendor',
            'Hiland',
            'Biz',
            'Tencent',
            'Common',
            'cert'
        );

        $result = join(DIRECTORY_SEPARATOR, $patharray);
        dump($result);
    }

    public function reverseorder()
    {
        $no = $_GET['no'];
        if (empty($no)) {
            echo '请设置no参数！';
        } else {
            $reverse = new WxPayDataBaseReverse();
            $reverse->SetOut_trade_no($no);
            $result = WxPayApi::reverse($reverse);

            dump($result);
        }
    }

    public function showoauth2userinfo()
    {
        $redirecturl = 'http://' . WebHelper::getHostName() . C('WEIXIN_OAUTH2_REDIRECTPAGE');
        $redirectstate = 0;
        $oauth2page = WechatHelper::getOAuth2PageUrl($redirectstate, $redirecturl);
        echo('
OAuth2.0网页授权演示
<a href="' . $oauth2page . '">获取当前用户的信息</a>
技术支持
                    ');
    }

    public function saetmpfileop()
    {
        dump(SAE_TMP_PATH);
    }

    // --------------------------------------------------
    public function extensionfieldop()
    {
        $model = D('extentiblerepository');
        $where['id'] = 1;
        $modelGotten = $model->where($where)->find();
        if (!empty($modelGotten)) {
            $model = $modelGotten;
        }

        $er = new ExtentibleRepository($model->keys, $model->values);
        $er->SetExtentibleProperty('name', 'zhangsan');
        $er->SetExtentibleProperty('age', '25');
        $er->SetExtentibleProperty('city', 'zaozhuang');

        $serilizedData = $er->Serialize();
        // dump($serilizedData);

        $model->keys = $serilizedData['keys'];
        $model->values = $serilizedData['values'];

        // $model->save();

        dump($model->keys);
        dump($model->values);

        /*
         * $nvc= ExtentibleRepository::convertToNameValueCollection($model->keys, $model->values);
         * dump(( $nvc));
         */

        $erconverted = new ExtentibleRepository($model->keys, $model->values);
        dump($erconverted->GetExtentiblePropertyCount());
        dump($erconverted->GetExtentibleProperty('name'));
        dump($erconverted->GetExtentibleProperty('age'));
        dump($erconverted->GetExtentibleProperty('age2'));
    }

    public function extensionModelOp()
    {
        $targetid = 10002;
        $category = 'useraction';
        $model = ExtentiblerepositoryModel::get($targetid, $category);
        // dump($model);
        if (empty($model)) {
            $model['keys'] = ''; // D('extentiblerepository');
            $model['values'] = '';
        }

        $repo = new ExtentibleRepository($model['keys'], $model['values']);
        $repo->SetExtentibleProperty('name', 'zhangsan');
        $repo->SetExtentibleProperty('age', '25');
        $repo->SetExtentibleProperty('city', 'chengdu');

        $arr = $repo->Serialize();
        $model['keys'] = $arr['keys'];
        $model['values'] = $arr['values'];

        /*
         * $model->keys = 'name:S:0:8:age:S:8:2:city:S:10:9:';
         * $model->values = 'zhangsan25zaozhuang';
         */
        $model['category'] = $category;
        $model['targetid'] = $targetid;
        $model['targetguid'] = GuidHelper::newGuid();
        // dump($model);
        $result = ExtentiblerepositoryModel::update($model);
        dump($result);
    }
}

?>