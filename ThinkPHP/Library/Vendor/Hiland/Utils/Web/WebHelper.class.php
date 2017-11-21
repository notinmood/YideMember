<?php
namespace Vendor\Hiland\Utils\Web;

use Vendor\Hiland\Utils\Data\StringHelper;

/**
 *
 * @author 然
 */
class WebHelper
{

    /**
     * 下载文件
     *
     * @param string $filename
     *            带全路径的文件
     */
    public static function download($filename)
    {
        header('Content-Type:' . MimeHelper::getMime($filename));
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Content-Length:' . filesize($filename));
        readfile($filename);
    }

    /**
     * 获取网站的域名信息
     * 不包括前面的"http://"和后面的"/"
     *
     * @return string
     */
    public static function getHostName()
    {
        return EnvironmentHelper::getServerHostName();
    }

    /**
     * 网页跳转
     *
     * @param string $targetUrl
     *            待跳转的页面
     */
    public static function redirectUrl($targetUrl)
    {
        header('location:' . $targetUrl);
    }

    /**
     * 给url附加参数信息
     *
     * @param string $url
     *            原url
     * @param array|string $paraData
     *            将要作为url参数被附加在url后面，的带名值对类型的数组或者已经排列好的参数名值对字符串
     * @param bool $isUrlEncode
     *            是否对参数的值进行url编码
     * @return string 附加了参数信息的url
     */
    public static function attachUrlParameter($url, $paraData, $isUrlEncode = false)
    {
        $paraString = '';
        if (is_string($paraData)) {
            $paraString = $paraData;
        } else {
            $paraString = self::formatArrayAsUrlParameter($paraData, $isUrlEncode);
        }

        if (StringHelper::isContains($url, "?")) {
            $url .= "&$paraString";
        } else {
            $url .= "?$paraString";
        }
        return $url;
    }

    /**
     * 对一个名值对数组格式化为url的参数
     *
     * @param array $paraArray
     *            需要格式化的名值对数组
     * @param bool $isUrlEncode
     *            是否对参数的值进行url编码
     * @param array $excludeParaArray
     *            不编制在url参数列表中的参数名数组（只有参数名称的一维数组）
     * @param bool $isSortPara 是否对参数进行排序
     * @return string
     */
    public static function formatArrayAsUrlParameter($paraArray, $isUrlEncode = false, $excludeParaArray = null, $isSortPara = true)
    {
        $buffString = "";

        if ($isSortPara) {
            ksort($paraArray);
        }

        foreach ($paraArray as $k => $v) {
            if (in_array($k, $excludeParaArray)) {
                continue;
            }

            if (empty($v)) {
                $v = '';
            }

            if ($isUrlEncode) {
                $v = urlencode($v);
            }

            $buffString .= $k . "=" . $v . "&";
        }
        $result = '';
        if (strlen($buffString) > 0) {
            $result = substr($buffString, 0, strlen($buffString) - 1);
        }
        return $result;
    }
}

?>