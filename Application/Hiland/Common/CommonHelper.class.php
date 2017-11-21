<?php
namespace Hiland\Common;

use Vendor\Hiland\Utils\Data\GuidHelper;

class CommonHelper
{

    /**
     * 进行日志记录
     *
     * @param string $title
     *            日志的标题
     * @param string $content
     *            日志的内容
     * @param string $categoryname
     *            日志的分类名称
     * @param string $other
     *            日志附加信息
     * @param int $misc1
     *            日志附加信息
     * @param string $status
     *            日志状态信息
     * @return boolean 日志记录的成功与失败
     */
    public static function log($title, $content = '', $status = '', $categoryname = 'develop', $other = '', $misc1 = 0)
    {
        $result = true;

        if (C("WEIXIN_LOG_MODE") >= 0) {
            $model = D("infolog");

            $data['guid'] = GuidHelper::newGuid();
            $data['title'] = (string)$title;
            $data['content'] = (string)$content;
            $data['category'] = $categoryname;
            $data['other'] = $other;
            $data['misc1'] = $misc1;
            $data['status'] = $status;
            $data['createtime'] = time();

            if ($model->add($data)) {
                $result = true;
            } else {
                $result = false;
            }
        }

        return $result;
    }


}

?>