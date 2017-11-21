<?php
/**
 * Created by PhpStorm.
 * User: xiedalie
 * Date: 2016/7/6
 * Time: 10:07
 */

namespace Vendor\Hiland\Utils\IO;


use Vendor\Hiland\Utils\Web\WebHelper;

class Thread
{
    /**
     * 不需要返回值的异步执行（只在后台执行业务逻辑，不能有返回值）
     * @param string $url  请求地址，不包括域名信息
     * @param string $host 域名主机信息
     * @param int $port 端口
     */
    public static function asynExec($url,$host='',$port=80)
    {
        if(empty($host)){
            $host= WebHelper::getHostName();
        }

        $fp = fsockopen($host, $port, $errno, $errstr, 30);
        if (!$fp) {
            echo 'error fsockopen';
        } else {
            stream_set_blocking($fp, 0);
            $http = "GET $url HTTP/1.1\r\n";
            $http .= "Host: $host\r\n";
            $http .= "Connection: Close\r\n\r\n";
            fwrite($fp, $http);
            fclose($fp);
        }
    }
}