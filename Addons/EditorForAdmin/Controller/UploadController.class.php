<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Addons\EditorForAdmin\Controller;

use Home\Controller\AddonsController;
use Think\Upload;

class UploadController extends AddonsController
{
    public $uploader = null;

    /* 上传图片 */
    public function upload()
    {
        /* 上传配置 */
        $setting = C('EDITOR_UPLOAD');

        /* 调用文件上传组件上传文件 */
        $this->uploader = new Upload($setting);
        $info = $this->uploader->upload($_FILES);
        if ($info) {
            $url = $info['imgFile']['savepath'] . $info['imgFile']['savename'];
            $url = str_replace('./', '/', $url);

            if (function_exists('saeAutoLoader')) {
                $info['fullpath'] = 'http://' . $_SERVER['HTTP_APPNAME'] . '-uploads.stor.sinaapp.com/Editor/' . $url;
            } else {
                $info['fullpath'] = __ROOT__ . '/Uploads/Editor/' . $url;
            }
        }
        return $info;
    }

    //keditor编辑器上传图片处理
    public function ke_upimg()
    {
        /* 返回标准数据 */
        $return = array('error' => 0, 'info' => '上传成功', 'data' => '');
        $img = $this->upload();
        /* 记录附件信息 */
        if ($img) {
            $return['url'] = $img['fullpath'];
            unset($return['info'], $return['data']);
        } else {
            $return['error'] = 1;
            $return['message'] = $this->uploader->getError();
        }

        /* 返回JSON数据 */
        exit(json_encode($return));
    }

    //ueditor编辑器上传图片处理
    public function ue_upimg()
    {

        $img = $this->upload();
        $return = array();
        $return['url'] = $img['fullpath'];
        $title = htmlspecialchars($_POST['pictitle'], ENT_QUOTES);
        $return['title'] = $title;
        $return['original'] = $img['imgFile']['name'];
        $return['state'] = ($img) ? 'SUCCESS' : $this->uploader->getError();
        /* 返回JSON数据 */
        $this->ajaxReturn($return);
    }

}
