<?php
namespace Tencent\Model;

use Tencent\Model\Userfinance;
class UserfinanceHYXF extends Userfinance
{
    /**
     * 获取支付费用的用途名称
     */
    protected function getPayUsageName(){
        return '商家消费,支付';
    }
}

?>