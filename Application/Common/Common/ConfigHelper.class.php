<?php
namespace Common\Common;

use Vendor\Hiland\Utils\Data\ArrayHelper;

class ConfigHelper
{

    /**
     * 从表格类型的二维配置节点里获取信息，然后根据key和value的设置形成一维数组
     *
     * @param string $configName
     *            配置节点的名称
     * @param string $newKeyName
     *            二维数组中的某个元素的名称，其对应的值将作为一维数组的key
     *            如果这个值为空，那么将会把原一维数组的key作为此值
     * @param string $newValueName
     *            二维数组中的某个元素的名称，其对应的值将作为一维数组的value
     *            如果这个值为空，那么将会把原一维数组的key作为此值
     * @return string[]
     *
     * @example 需要转换的二维数组，类似如下
     *          'ROLE_OUT_DEGREES' => array(
     *          ----'UNIN' => array(
     *          --------'value' => - 1,
     *          -------- 'display' => '未入局'
     *          #### ),
     *          #### 'IN' => array(
     *          -------- 'value' => 0,
     *          -------- 'display' => '在局'
     *          #### ), // 未出局
     *          #### 'PARTIALOUTASLOCK' => array(
     *          -------- 'value' => 1,
     *          -------- 'display' => '锁定未出局'
     *          #### ), // 子用户的层级已经到达到系统限制，但因为某个层级未做满，收益被锁定暂不出局
     *          #### 'OUT' => array(
     *          -------- 'value' => 10,
     *          -------- 'display' => '出局'
     *          #### )// 出局
     *          )
     */
    public static function get1DArray($configName, $newKeyName, $newValueName, $useCache = true)
    {
        $result = null;
        $cacheKey = "ConfigInfo20160225:configname-$configName ,newkeyname-$newKeyName ,newvaluename-$newValueName";
        if ($useCache) {
            $result = S($cacheKey);
        }
        
        if ($result == null) {
            $originalArray = C($configName);
            $result = ArrayHelper::convert2DTo1D($originalArray, $newKeyName, $newValueName);
        }
        
        if ($useCache && $result != null) {
            S($cacheKey, $result, 3600);
        }
        
        return $result;
    }
}

?>