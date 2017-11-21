<?php
namespace Vendor\Hiland\Utils\Web;

use Vendor\Hiland\Biz\Tencent\Common\WechatException;
use Vendor\Hiland\Utils\Data\StringHelper;
use Vendor\Hiland\Utils\IO\FileHelper;

class NetHelper
{

    /**
     * 模拟网络POST请求
     *
     * @param string $url
     * @param mixed $data
     * @param null $optionalheaders
     * @return string
     * @throws \Exception
     */
    public static function Post($url, $data = null, $optionalheaders = null)
    {
        $params = array(
            'http' => array(
                'method' => 'POST',
                'content' => $data
            )
        );
        if ($optionalheaders !== null) {
            $params['http']['header'] = $optionalheaders;
        }
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            throw new \Exception("Problem with $url");
        }
        $response = @stream_get_contents($fp);
        if ($response === false) {
            throw new \Exception("Problem reading data from $url");
        }
        return $response;
    }

    /**
     * 模拟网络Get请求
     *
     * @param string $url
     * @return string
     */
    public static function Get($url)
    {
        $result = file_get_contents($url);
        return $result;
    }

    /**
     * 根据是否有$data值进行智能判断是发起post还是get请求
     *
     * @param string $url
     *            被请求的url
     * @param mixed $data
     *            post请求时发送的数据
     * @param int $timeoutsecond
     *            请求超时时间
     * @param bool $issslverify
     *            是否进行ssl验证
     * @param array $headerarray
     *            请求头信息
     * @param array $cretfilearray
     *            请求的证书信息（证书需要带全部的物理路径）并且证书的文件名命名格式要求如下：
     *            cert证书 命名格式为 *****cert.pem
     *            key证书命名格式为 *****key.pem
     *            ca证书命名格式为 *****ca.pem
     * @return mixed
     * @throws WechatException
     */
    public static function request($url, $data = null, $timeoutsecond = 30, $issslverify = false, $headerarray = array(), $cretfilearray = array())
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_TIMEOUT, $timeoutsecond);
        // 要求结果为字符串且输出到屏幕上
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        // curl_setopt ( $curl, CURLOPT_SAFE_UPLOAD, false);
        curl_setopt($curl, CURLOPT_URL, $url);

        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_SAFE_UPLOAD, false);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        if (count($headerarray) >= 1) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headerarray);
        }

        if ($issslverify) {
            // 检测服务器的证书是否由正规浏览器认证过的授权CA颁发的
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, TRUE);
            // 检测服务器的域名与证书上的是否一致
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 严格校验
        } else {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }

        foreach ($cretfilearray as $key) {
            $filebasename = FileHelper::getFileBaseName($key);
            if (StringHelper::isContains($filebasename, "cert.pem")) {
                curl_setopt($curl, CURLOPT_SSLCERT, $key);
            }

            if (StringHelper::isContains($filebasename, "key.pem")) {
                curl_setopt($curl, CURLOPT_SSLKEY, $key);
            }

            if (StringHelper::isContains($filebasename, "ca.pem")) {
                curl_setopt($curl, CURLOPT_CAINFO, $key);
            }
        }

        $output = curl_exec($curl);

        // 返回结果
        if ($output) {
            curl_close($curl);
            return $output;
        } else {
            $error = curl_errno($curl);
            curl_close($curl);
            throw new WechatException("curl出错，错误码:$error");
        }
    }
}

?>