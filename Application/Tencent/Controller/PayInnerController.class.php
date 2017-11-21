<?php
namespace Tencent\Controller;

use Common\Common\ConfigHelper;
use Common\Model\UserinfoModel;
use Hiland\Common\CommonHelper;
use Tencent\Model\BizHelper;
use Tencent\Model\FinanceHelper;
use Think\Controller;
use Think\Model;
use Vendor\Hiland\Biz\Tencent\WechatHelper;
use Vendor\Hiland\Utils\Data\OperationHelper;
use Vendor\Hiland\Utils\DataModel\ModelMate;

/**
 * 平台内账号之间的转账付款
 * @author devel
 *
 */
class PayInnerController extends Controller
{
    /**
     * 用户发起付款通知
     *
     * @param string $fromUserID
     * @param string $fromUserName
     * @param string $toUserID
     * @param string $toUserName
     */
    public function launchPayNotice($fromUserID = '', $fromUserName = '', $toUserID = '', $toUserName = '')
    {
        $touseropenid = '';

        if (empty($fromUserID)) {
            $fromUserID = I("fromuserid");
        }

        $fromUserInfo = UserinfoModel::getByKey($fromUserID);
        $fromUserOpenID = $fromUserInfo['weixinopenid'];

        if (empty($fromUserName)) {
            $fromUserName = I("fromusername");

            if (empty($fromUserName)) {
                $fromUserName = $fromUserInfo['displayname'];
            }
        }

        if (empty($toUserID)) {
            $toUserID = I("touserid");
        }

        $toUserInfo = UserinfoModel::getByKey($toUserID);
        $toUserOpenID = $toUserInfo['weixinopenid'];

        if (empty($toUserName)) {
            $toUserName = I("tousername");

            if (empty($toUserName)) {
                $toUserName = $toUserInfo['displayname'];
            }
        }

        $model = D('innerpaynotice');
        $data['fromuserid'] = $fromUserID;
        $data['fromusername'] = $fromUserName;
        $data['touserid'] = $toUserID;
        $data['tousername'] = $toUserName;
        $data['noticestatus'] = 0; // PAY_INNER_NOTICE_STATUSES
        $data['launchtime'] = time();
        $data['title'] = "用户[$fromUserName]向[$toUserName]发起支付";

        $result = $model->data($data)->add();

        if ($result > 0) {
            $messageForFromUser = "支付请求已经成功发送给[$toUserName]，对方接到本消息后，会在公众平台内再次向你确认收款。";

            //TODO 在用户信息里面设置标志，运行商家自助关闭 用户的支付通知
            $messageForToUser = "用户[$fromUserName]给你的支付请求的已经发送,请到你的管理后台查看收款！";
            WechatHelper::responseCustomerServiceText($toUserOpenID, $messageForToUser);
        } else {
            $messageForFromUser = "给[$toUserName]的支付请求发送失败。" . C('SYSTEM_ERROR_NOTICES');
        }

        // WechatHelper::responseCustomerServiceText($fromUserOpenID, $messageForFromUser);

        $data = BizHelper::buildResultNoticePageData('请求成功', $messageForFromUser, 'success');
        $this->assign('data', $data);
        $this->display('Public/resultnotice');
    }

    /**
     * 微信用户确认付款
     *
     * @param int $id
     *            付款通知id
     */
    public function launchPayConfirm($id)
    {
        $mate = new ModelMate('innerpaynotice');
        $noticeData = $mate->get($id);

        $result = true;
        $messageForFromUser = '';
        $noticeStatuses = ConfigHelper::get1DArray('PAY_INNER_NOTICE_STATUSES', '', 'value');

        // 0 判断当前支付请求的状态
        if ($noticeData['noticestatus'] == $noticeStatuses['RECEIVED_BY_RECEEIVER']) {
            $result = true;
        } else {
            $result = false;
            $messageForFromUser = "您的支付交易失败，请确认本付款信息先前是否已经被确认或拒绝。如果要进行再次付款，可以重新扫描商家二维码。";
        }

        //CommonHelper::log('付款状态',$noticeData['noticestatus']);
        dump('付款处理中。。。');

        $transModel = new Model();
        $transModel->startTrans();

        // 1 账目款项真实的开始转移
        if ($result) {
            $financeDataOut = null;
            $financeDataOut['userid'] = $noticeData['fromuserid'];
            $financeDataOut['roleid'] = 0; // TODO:需要把角色信息加入计算;在商家消费可以不指定roleid，有系统进行推断出用户在此商家的所有角色
            $financeDataOut['subjecttype'] = -3; // SYSTEM_FINANCE_SUBJECTS
            $financeDataOut['moneyamount'] = $noticeData['payamount'];
            $financeDataOut['content'] = '会员[' . $noticeData['fromusername'] . ']在商家' . $noticeData['tousername'] . '消费支付';
            $financeDataOut['relationuserid'] = $noticeData['touserid'];
            $financeDataOut['exchangetime'] = time();

            $resultFlowOut = FinanceHelper:: flowOut($financeDataOut, false);

            $result = OperationHelper::getResult($resultFlowOut);
            $messageForFromUser = OperationHelper::getErrorMessage($resultFlowOut);
        }


        if ($result) {
            $financeDataIn = null;
            $financeDataIn['userid'] = $noticeData['touserid'];
            $financeDataIn['roleid'] = 0; //本处为转账到商家，不指定角色id；如果是会员之间转账需要指定角色id。
            $financeDataIn['subjecttype'] = 3; // SYSTEM_FINANCE_SUBJECTS
            $financeDataIn['moneyamount'] = $noticeData['payamount'];
            $financeDataIn['content'] = '商家' . $noticeData['tousername'] . '为会员[' . $noticeData['fromusername'] . ']支付的消费提供服务';
            $financeDataIn['relationuserid'] = $noticeData['fromuserid'];
            $financeDataIn['exchangetime'] = time();

            $resultFlowIn = FinanceHelper:: flowIn($financeDataIn, false);

            $result = OperationHelper::getResult($resultFlowIn);
            if (!$result) {
                $messageForFromUser = OperationHelper::buildBizErrorResult('费用转入失败。');
            }
        }

        // 2 记录当前付款通知被确认的信息
        if ($result) {
            $noticeStatus = $noticeStatuses['CONFIRMED_BY_SENDER'];
            $noticeData['noticestatus'] = $noticeStatus;
            $noticeData['confirmedtime'] = time();
            $result = $mate->interact($noticeData);
        }

        $displayContent = '';
        if ($result) {
            $transModel->commit();

            $messageForFromUser = "您的支付已经成功交易，期待您的下次光临。";
            $displayContent = BizHelper::buildResultNoticePageData('支付成功', $messageForFromUser, 'success');
        } else {
            $transModel->rollback();

            if (empty($messageForFromUser)) {
                $messageForFromUser = "您的支付交易失败。" . (string)$result . C('SYSTEM_ERROR_NOTICES');
            }
            $displayContent = BizHelper::buildResultNoticePageData('支付失败', $messageForFromUser, 'warn');
        }

        $this->assign('data', $displayContent);
        $this->display('Public/resultnotice');
    }

    /**
     * 微信用户拒绝付款
     *
     * @param int $id
     *            付款通知id
     */
    public function launchPayRefuse($id)
    {
        $mate = new ModelMate('innerpaynotice');
        $data = $mate->get($id);

        $result = true;
        $messageForFromUser = '';
        $noticeStatuses = ConfigHelper::get1DArray('PAY_INNER_NOTICE_STATUSES', '', 'value');

        // 0 判断当前支付请求的状态
        if ($data['noticestatus'] == $noticeStatuses['RECEIVED_BY_RECEEIVER']) {
            $result = true;
        } else {
            $result = false;
            $messageForFromUser = "您的支付交易失败，请确认本付款信息先前是否已经被确认或拒绝。如果要进行再次付款，可以重新扫描商家二维码。";
        }

        // 1 变更支付请求的状态
        if ($result) {
            $noticeStatuses = ConfigHelper::get1DArray('PAY_INNER_NOTICE_STATUSES', '', 'value');
            $noticeStatus = $noticeStatuses['REFUSED_BY_SENDER'];
            $data['noticestatus'] = $noticeStatus;
            $data['confirmedtime'] = time();
            $result = $mate->interact($data);
        }

        if ($result) {
            $messageForFromUser = "您的付款已经成功关闭，期待您的下次光临。";
            $data = BizHelper::buildResultNoticePageData('支付关闭成功', $messageForFromUser, 'info');
        } else {
            if (empty($messageForFromUser)) {
                $messageForFromUser = "您的付款关闭失败。" . C('SYSTEM_ERROR_NOTICES');
            }
            $data = BizHelper::buildResultNoticePageData('支付关闭失败', $messageForFromUser, 'warn');
        }

        $this->assign('data', $data);
        $this->display('Public/resultnotice');
    }
}

?>