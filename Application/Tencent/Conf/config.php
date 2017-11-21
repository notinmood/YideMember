<?php
use Vendor\Hiland\Biz\Tencent\Common\WechatConfig;

/**
 * 关于微信的接口和业务逻辑的所有配置
 */
return array(
    // 配置说明：
    // 1、创建完成ot33binbin_userinfo 表后，进行初始化，让其userid从X+1开始增长,请使用如下语句：
    // alter table AAAAAA_userinfo AUTO_INCREMENT=X+1;（其中AAAAA为用户表的前缀）
    // X的具体值参看下面的配置项 WEIXIN_USER_VIPHOLDVALUE
    // (X之内保留，主要是为vipid使用。vipid字段取值范围1-X，
    // 这样可以让vip用户生成永久二维码，如果扫描二维码获取到的推荐人id，小于X，
    // 则到vipid字段中查找对应用户，否则到userid字段查找用户).
    // 可以参看userinfo表的设计：
    // vip用户既有普通跟普通用户一样的编号（这个值大于X），也有其vip编号（这个值小于等于X）

    // 2、需要在程序根目录index.php中配置系统的物理根目录 define('PHYSICAL_ROOT_PATH', dirname(__FILE__));
    // 3、请配置ThinkPHP\Library\Vendor\Hiland\Biz\Tencent\Common\WechatConfig.class.php内的各个参数
    // 4、如果发起支付、红包、提现，请替换ThinkPHP\Library\Vendor\Hiland\Biz\Tencent\Common\cert目录下的3个证书文件

    // 微信二次开发入口token
    'WEIXIN_GATE_TOKEN' => WechatConfig::GATETOKEN, // 'bigseagull20140112',

    // 微信开发的应用id
    'WEIXIN_APPID' => WechatConfig::APPID, // 'wx13856a19b29cc66b'
    // 微信开发的密钥
    'WEIXIN_APPSECRET' => WechatConfig::APPSECRET, // 'd85f4fb686e6e225c677a090029af211',

    // 微信支付的商户号
    'WEIXIN_MERCHANTID' => WechatConfig::MCHID, // '1239423402',

    // 微信支付密钥
    'WEIXIN_PAYKEY' => WechatConfig::MCHKEY, // 'jxDVOSfIi7l0oMCitb4sHoaXNQtdh1uE',

    // 微信的日志记录模式枚举信息
    'WEIXIN_LOG_MODES' => array(
        0 => 'deploy', // 部署模式
        10 => 'develop',// 开发调试模式
    ),

    // 微信的日志记录模式 0:部署模式 10：开发调试模式;具体值请看配置WEIXIN_LOG_MODES
    'WEIXIN_LOG_MODE' => 10,

    'WEIXIN_SAE_DOMAINNAME' => 'sansanbinbin',

    // 二维码有效性类型枚举
    'WEIXIN_QR_EFFECTTYPES' => array(
        'TEMP' => 'QR_SCENE', // 临时二维码
        'LONG' => 'QR_LIMIT_SCENE',// 长效二维码
    ),

    // 使用二维码有效性类型（默认值）
    'WEIXIN_QR_EFFECTTYPE' => 'TEMP',

    // 临时二维码有效时间长度，以秒单位。（2592000表示30天,微信可以支持的最大时间；604800表示一周；86400表示一天）
    'WEIXIN_QR_TEMP_EFFECT_SECONDS' => 604800,

    // 微信推广二维码所使用的背景图片路径（从网站根目录“/”写起）
    'WEIXIN_RECOMMEND_BGPIC' => '/Public/static/common/sansanbg.jpg',

    // 微信推广二维码所使用的文字字体（从网站根目录“/”写起）
    'WEIXIN_RECOMMEND_TEXTFONT' => '/Public/static/common/stxingkai.ttf',

    // 微信推广码使用的缺省的头像（用户头像不存在时使用的头像）
    'WEIXIN_RECOMMEND_DEFAULTAVATAR' => '/Public/static/common/defaultavatar.jpg',

    // 用户编号中的前 X编号为vip用户的编号
    // （vip用户既有普通跟普通用户一样的编号（这个值大于X），也有其vip编号（这个值小于等于X））
    // 本配置信息移入Application\Common\Conf\config.php下
    // 'WEIXIN_USER_VIPHOLDVALUE' => 100000,

    // 是否允许用户在系统内有多个未出局角色
    // （如果是多商户系统，本值设置为false时，用户在本平台的每个商户下都可以有一个未出局角色；
    // 本值设置为true 时, 用户在本平台的每个商户下都可以有多个未出局角色）
    'WEIXIN_USER_MULTIUNOUTROLES_ALLOW' => true,

    // 用户从下级会员获取收益的时候，是下级做满的时候才能一次性全部收益，
    // 还是有下级某个会员加入的时候马上能收益
    // 如果是某个层级做满子会员的时候才能一次性收益，称为本层锁定。
    // 如果锁定层级的话，当前层级未做满，即便某个子分支已经到达出局的条件了，此角色也不能出局
    'WEIXIN_ZIGE_INCOME_LOCKLEVEL' => 1,

    // 用户出局的层级数
    'WEIXIN_ZIGE_OUTLEVEL' => 8,
    // 允许每角色下层挂接的最大子角色数
    'WEIXIN_ZIGE_MAXSUBCOUNT' => 3,

    //如果采用代金券激活的方式，代金券比与缴费额度的倍数（代金券是实时到账，只是未激活）
    //收入达到阈值时，收入才被激活。这个阈值通常是其最初开通这个角色时缴费的X倍。当收入达到或超过阈值时，收入激活为可用
    //如果本参数设置为0，那么收入是实时激活的。
    'WEIXIN_ZIGE_ACTIVETHRESHOLDMULTIPLE' => 3,

    // 如果商家推出多种价格的产品，那么会员在开通各层资格的时候，
    // 是否要按照与BASEPRICE折算的比例进行缴费
    'WEIXIN_ZIGE_USESCALE' => TRUE,

    // 默认情形下，开通下一级会员资格要向上一级缴纳的费用
    'WEIXIN_ZIGE_BASEPRICE' => 9,
    // 默认情形下，开通下X级会员资格要向上X级缴纳的费用
    'WEIXIN_ZIGE_LEVEL2PRICE' => 20,
    'WEIXIN_ZIGE_LEVEL3PRICE' => 40,
    'WEIXIN_ZIGE_LEVEL4PRICE' => 60,
    'WEIXIN_ZIGE_LEVEL5PRICE' => 90,
    'WEIXIN_ZIGE_LEVEL6PRICE' => 150,
    'WEIXIN_ZIGE_LEVEL7PRICE' => 220,
    'WEIXIN_ZIGE_LEVEL8PRICE' => 300,
    'WEIXIN_ZIGE_LEVEL9PRICE' => 500,
    'WEIXIN_ZIGE_LEVEL10PRICE' => 900,

    //通过微信进行支付的时候，显示在用户支付界面中的 标签信息
    'WEIXIN_PAY_TAGS'=>array(
        0=>'申请资格',
        1=>'申请VIP',
    ),

    //开通VIP资格的费用
    'WEIXIN_ZIGE_VIPPRICE'=>10.00
);
