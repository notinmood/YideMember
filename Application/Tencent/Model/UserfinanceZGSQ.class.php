<?php
namespace Tencent\Model;

class UserfinanceZGSQ extends Userfinance
{
    /**
     * 获取支付费用的用途名称
     */
    protected function getPayUsageName(){
        return '资格申请,支付';
    }
}

?>