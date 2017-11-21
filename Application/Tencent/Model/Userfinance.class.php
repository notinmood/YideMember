<?php
namespace Tencent\Model;

class Userfinance
{

    public function getFlowContent($targetUser, $relationUser, $amount = 0)
    {
        $targetUserName = $targetUser['displayname'];
        $targetUserID = $targetUser['userid'];
        
        $relationUserName = $relationUser['displayname'];
        $relationUserID = $relationUser['userid'];
        
        $payUsageName= $this->getPayUsageName();
        
        $message = '会员[' . $targetUserName . '](' . $targetUserID . ')向会员[' . $relationUserName . '](' . $relationUserID . ')'.$payUsageName;
        if ($amount) {
            $message .= $amount . '元';
        }
        return $message;
    }
    
    /**
     * 获取支付费用的用途名称
     */
    protected function getPayUsageName(){
        return '支付';
    }
}

?>