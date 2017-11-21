<?php
namespace Vendor\Hiland\Utils\Data;

class XmlHelper
{

    /**
     * 将xml转换成json
     *
     * @param string $xml
     *            待转换的xml 其可以是一个xml文件地址，也可以是一个xml原始字符串
     * @return string
     */
    public static function toJson($xml)
    {
        // 传的是文件，还是xml的string的判断
        if (is_file($xml)) {
            $xml_array = simplexml_load_file($xml);
        } else {
            $xml_array = simplexml_load_string($xml);
        }
        $json = json_encode($xml_array); // php5，以及以上，如果是更早版本，请查看JSON.php
        return $json;
    }

    /**
     * 将json转换成xml
     *
     * @param string $json
     * @param string $charset            
     * @return boolean|string
     */
    public static function toXml($json, $charset = 'utf8')
    {
        if (empty($json)) {
            return false;
        }
        
        $array = json_decode($json); // php5，以及以上，如果是更早版本，請下載JSON.php
        $xml = ArrayHelper::Toxml($array,'myxml',true,$charset);
        return $xml;
    }
}

?>