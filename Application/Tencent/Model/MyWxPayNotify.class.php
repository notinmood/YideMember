<?php
namespace Tencent\Model;

use Hiland\Common\CommonHelper;
use Vendor\Hiland\Biz\Tencent\Pay\WxPayApi;
use Vendor\Hiland\Biz\Tencent\Pay\WxPayData\WxPayDataBaseOrderQuery;
use Vendor\Hiland\Biz\Tencent\Pay\WxPayNotify;
use Vendor\Hiland\Utils\Data\GuidHelper;

class MyWxPayNotify extends WxPayNotify
{
    // 查询订单
    public function Queryorder($transaction_id)
    {
        $input = new WxPayDataBaseOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = WxPayApi::orderQuery($input);
        // Log::DEBUG("query:" . json_encode($result));
        if (array_key_exists("return_code", $result) && array_key_exists("result_code", $result) && $result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS") {
            return true;
        }
        return false;
    }
    
    // 重写回调处理函数
    public function NotifyProcess($data, &$msg)
    {
        if (! array_key_exists("transaction_id", $data)) {
            $msg = "输入参数不正确";
            return false;
        }
        // 查询订单，判断订单真实性
        if (! $this->Queryorder($data["transaction_id"])) {
            $msg = "订单查询失败";
            return false;
        }
        
//         $postString = $GLOBALS["HTTP_RAW_POST_DATA"];
//         $postArray= $data;
        $resultCode= strtoupper( $data['result_code']);
        $returnCode= strtoupper($data['return_code']);
        $outTradeNo= $data['out_trade_no'];
        $roleGuid= GuidHelper::addonHyphen($outTradeNo);
        
        $content= '';
        $content.= "|resultCode:$resultCode";
        $content.= "|returnCode:$returnCode";
        $content.= "|outTradeNo:$outTradeNo";
        $content.= "|roleGuid:$roleGuid";
        
        CommonHelper::log('weixin pay debug',$content);
        
        if($resultCode=='SUCCESS'|| $returnCode=='SUCCESS'){
            //BizHelper::ruDing($roleGuid);
            BizHelper::payedDeal($roleGuid);
        }
        
        return true;
    }
}

?>