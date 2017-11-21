<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.thinkphp.cn>
// +----------------------------------------------------------------------

/**
 * 前台配置文件
 * 所有除开系统级别的前台配置
 */

return array(
    'PICTURE_UPLOAD_DRIVER' => 'Sae',


    'UPLOAD_SAE_CONFIG' => array(
        'rootPath' => '',
        'domain' => 'uploads',
    ),

    'SAE_Domain' => 'http://' . $_SERVER['HTTP_APPNAME'] . '-uploads.stor.sinaapp.com',
);
