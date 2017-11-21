<?php
namespace Tencent\Model;

class UserfinanceTX extends Userfinance
{
    public function getFlowContent($targetUser, $relationUser, $amount = 0)
    {
        $targetUserName = $targetUser['displayname'];
        $targetUserID = $targetUser['userid'];
        
        $relationUserName = $relationUser['displayname'];
        $relationUserID = $relationUser['userid'];
        
        $payUsageName= $this->getPayUsageName();
        
        $message = '会员[' . $targetUserName . '](' . $targetUserID . ')提现';
        if ($amount) {
            $message .= $amount . '元';
        }
        return $message;
    }
}

?>