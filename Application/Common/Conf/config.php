<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 系统配文件
 * 所有系统级别的配置
 */
return array(
    /* 模块相关配置 */
    'AUTOLOAD_NAMESPACE' => array(
        'Addons' => ONETHINK_ADDON_PATH
    ), // 扩展模块列表
    'DEFAULT_MODULE' => 'Home',
    'MODULE_DENY_LIST' => array(
        'Common',
        'User'
    ),
    // 'MODULE_ALLOW_LIST' => array('Home','Admin'),

    /* 系统数据加密设置 */
    'DATA_AUTH_KEY' => 'g!4UdkDm3^~`[OS|/?,qQILo;uxV$e5Trjy1zsRJ', // 默认数据加密KEY

    /* 调试配置 */
    'SHOW_PAGE_TRACE' => FALSE,

    /* 用户相关设置 */
    'USER_MAX_CACHE' => 1000, // 最大缓存用户数
    'USER_ADMINISTRATOR' => 1, // 管理员用户ID

    /* URL配置 */
    'URL_CASE_INSENSITIVE' => true, // 默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL' => 3, // URL模式
    'VAR_URL_PARAMS' => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR' => '/', // PATHINFO URL分割符

    /* 全局过滤配置 */
    'DEFAULT_FILTER' => '', // 全局过滤函数

    /* 数据库配置 */
//    'DB_TYPE' => 'mysql', // 数据库类型
//    'DB_HOST' => 'w.rdc.sae.sina.com.cn', // 服务器地址
//    'DB_NAME' => 'app_hilandwechat', // 数据库名
//    'DB_USER' => 'y05ll4jnl2', // 用户名
//    'DB_PWD' => 'w0ix0110kz30yzm3h0211h5hymhxlml244hy5x12', // 密码
//    'DB_PORT' => '3307', // 端口
//    'DB_PREFIX' => 'ot33binbin_', // 数据库表前缀

    'DB_TYPE' => 'mysql', // 数据库类型
    'DB_HOST' => '127.0.0.1', // 服务器地址
    'DB_NAME' => 'my_yidemember', // 数据库名
    'DB_USER' => 'root', // 用户名
    'DB_PWD' => 'GQxB4LyasPhD8rqN', // 密码
    'DB_PORT' => '3306', // 端口
    'DB_PREFIX' => 'ott_', // 数据库表前缀

    /* 文档模型配置 (文档模型核心配置，请勿更改) */
    'DOCUMENT_MODEL_TYPE' => array(
        2 => '主题',
        1 => '目录',
        3 => '段落'
    ),

    // --[HILAND添加]--------------------------------------------------------------------------------
    /* 定时任务配置 */
    'CRON_CONFIG_ON' => true, // 是否开启自动运行
    'CRON_CONFIG' => array(
        '测试定时任务' => array('Hiland/Task/myTask', '60', ''), //路径(格式同R)、间隔秒（0为一直运行）、指定一个开始时间
    ),

    // 微信授权后的跳转页面，在其内获取的授权code，然后在根据用户配置的state参数进行各种跳转
    'WEIXIN_OAUTH2_REDIRECTPAGE' => '/index.php/tencent/index/oauth2redirectpage',

    // 根据微信认证服务器传递过来的state值，跟本配置节点进行对照然后进行页面跳转
    'WEIXIN_OAUTH2_REDIRECTSTATE' => array(
        0 => 'only show in page', // 0 表示不进行跳转，只是在跳转中转页面进行信息显示
        1 => '/index.php?s=/tencent/bar/dateop', // 1 表示跳转到****页
        2 => '/index.php?s=/tencent/pay/hongbao',//
        100 => '',//经常会错的字游戏
    ),

    /*
     * 站点应用类型分为以下几种
     * 1、资金游戏平台（三三复制游戏）
     * 2、多商户o2o商城
     * 3、统一运行的多商户营销平台
     * 4、作为商品出售的单商户营销系统
     */
    'SITEUSINGTYPES' => array(
        1 => 'GAMEPLATFORM',
        2 => 'MULTIMERCHANTO2OMALL',
        3 => 'MULTIMERCHANTMARKETING',
        4 => 'SINGLEMERCHANTMARKETING'
    ),
    'SITEUSINGTYPE' => 3,

    /**
     * 支付方式
     */
    'SYSTEM_FINANCE_PAY_WAYS' => array(
        0 => '未设置',
        10 => '内部转账',
        20 => '外部支付'
    ),

    /**
     * 会员资金操作的会计科目
     */
    'SYSTEM_FINANCE_SUBJECTS' => array(
        'CZ' => array(
            'direction' => 1,
            'value' => 10,
            'name' => '充值'
        ),

        'ZGSY' => array(
            'direction' => 1,
            'value' => 1,
            'name' => '资格收益'
        ),

        // 线上出售物品（在线商城）
        'SHSM' => array(
            'direction' => 1,
            'value' => 2,
            'name' => '商户售卖营收'
        ),

        // 线下提供服务（实体门店）
        'SHFW' => array(
            'direction' => 1,
            'value' => 3,
            'name' => '商户服务营收'
        ),

        'ZGSQ' => array(
            'direction' => -1,
            'value' => -1,
            'name' => '资格申请'
        ),

        'GWZF' => array(
            'direction' => -1,
            'value' => -2,
            'name' => '购物支付'
        ),

        'HYXF' => array(
            'direction' => -1,
            'value' => -3,
            'name' => '会员商户消费'
        ),

        'TX' => array(
            'direction' => -1,
            'value' => -10,
            'name' => '提现'
        )
    ),

    /**
     * 角色的出局状态
     */
    'ROLE_OUT_DEGREES' => array(
        'UNIN' => array(
            'value' => -1,
            'display' => '未入局'
        ),
        'IN' => array(
            'value' => 0,
            'display' => '在局'
        ), // 未出局
        'PARTIALOUTASLOCK' => array(
            'value' => 1,
            'display' => '锁定未出局'
        ), // 子用户的层级已经到达到系统限制，但因为某个层级未做满，收益被锁定暂不出局
        'OUT' => array(
            'value' => 10,
            'display' => '出局'
        )
    ), // 出局

    // 用户编号中的前 X编号为vip用户的编号
    // （vip用户既有普通跟普通用户一样的编号（这个值大于X），也有其vip编号（这个值小于等于X））
    'WEIXIN_USER_VIPHOLDVALUE' => 100000,

    'HILAND_SYSTEM_USABLESTATUS' => array(
        0 => '无效',
        1 => '有效'
    ),

    //内部转账支付的状态
    'PAY_INNER_NOTICE_STATUSES' => array(
        'LAUNCH_BY_SENDER' => array(
            'value' => 0,
            'display' => '付款请求正发起'
        ),
        'RECEIVED_BY_RECEEIVER' => array(
            'value' => 10,
            'display' => '付款信息已接收'
        ),
        'CONFIRMED_BY_SENDER' => array(
            'value' => 20,
            'display' => '发起人付款成功'
        ),
        'REFUSED_BY_RECEEIVER' => array(
            'value' => -10,
            'display' => '接收人取消收款'
        ),
        'REFUSED_BY_SENDER' => array(
            'value' => -20,
            'display' => '发起人拒绝付款'
        ),

        'CONFIRMED_BY_AUTO' => array(
            'value' => 100,
            'display' => '系统超时自动确认'
        ),
        'REFUSED_BY_AUTO' => array(
            'value' => -100,
            'display' => '系统超时自动关闭'
        ),
    ),

    'TECH_CONTACK_INFO' => 'QQ:9727005,电话/微信:13573290346',
    'BIZ_CONTACK_INFO' => 'QQ:1344541385,电话/微信:18663283012',
    'SYSTEM_ERROR_NOTICES' => '给您带来不便敬请谅解，请联系系统维护人员QQ:9727005,电话/微信:13573290346。',

    //付款时是否显示警告信息
    'SYSTEM_DISPLAY_PAYWARNING' => true,

    //允许显示给用户的推进信息条目数
    'SYSTEM_ALLOW_DISPLAY_RECOMMEND_ITEMCOUNT' => 6,
);
