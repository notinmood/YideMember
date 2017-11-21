<?php
namespace Vendor\Hiland\Utils\Web;

class JavaScriptHelper
{

    /**
     * js 弹窗并且跳转
     *
     * @param string $message
     * @param string $url
     * @return string js
     */
    static public function alertLocation($message, $url)
    {
        echo "<script type='text/javascript'>alert('$message');location.href='$url';</script>";
        exit();
    }

    /**
     * js 弹窗返回
     *
     * @param string $message
     * @return string js
     */
    static public function alertBack($message)
    {
        echo "<script type='text/javascript'>alert('$message');history.back();</script>";
        exit();
    }

    /**
     * 页面跳转
     *
     * @param string $url
     * @return  string js
     */
    static public function headerUrl($url)
    {
        echo "<script type='text/javascript'>location.href='{$url}';</script>";
        exit();
    }

    /**
     * 弹窗关闭
     *
     * @param string $message
     * @return string js
     */
    static public function alertClose($message)
    {
        echo "<script type='text/javascript'>alert('$message');close();</script>";
        exit();
    }

    /**
     * 弹窗
     *
     * @param string $message
     * @return string js
     */
    static public function alert($message)
    {
        echo "<script type='text/javascript'>alert('$message');</script>";
        exit();
    }
}

?>