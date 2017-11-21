<?php
namespace Admin\Controller;

use Common\Common\ConfigHelper;
use Common\Model\UserinfoModel;
use Common\Model\UserrolesModel;
use Tencent\Model\BizHelper;
use Vendor\Hiland\Biz\Tencent\WechatHelper;
use Vendor\Hiland\Utils\Data\DateHelper;
use Vendor\Hiland\Utils\Data\DBSetHelper;
use Vendor\Hiland\Utils\Data\StringHelper;
use Vendor\Hiland\Utils\DataModel\ModelMate;
use Vendor\Hiland\Utils\Web\WebHelper;

class MerchantController extends HilandController
{
    /**
     * 用户付款请求列表
     */
    public function innerPayNoticeList()
    {
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $where = null;

        $nickname = I('nickname');
        if (!empty($nickname)) {
            $where['fromusername|tousername'] = array(
                'like',
                '%' . (string)$nickname . '%'
            );
        }

        $specialitem = I('specialitem');
        if ($specialitem != '--' && is_numeric($specialitem)) {
            $where['noticestatus'] = $specialitem;
        }

        $merchantFilter = self::getMerchantFilter();
        if (!empty($merchantFilter)) {
            if ($where == null) {
                $where = $merchantFilter;
            } else {
                $where = array_merge($where, $merchantFilter);
            }
        }

        $list = $this->lists('innerpaynotice', $where);

        $noticeStatuses = ConfigHelper::get1DArray('PAY_INNER_NOTICE_STATUSES', 'value', 'display');

        $friendlymaps = array(
            'noticestatus' => $noticeStatuses
        );

        DBSetHelper::friendlyDisplay($list, $friendlymaps);

        $this->assign('_list', $list);
        //$this->meta_title = '转账支付通知列表';
        $this->assign('meta_title', '转账支付通知列表');
        $this->display();
    }

    /**
     * 获取商户的过滤条件
     * @return null|array
     * TODO：目前仅支持超级管理员和商户可以查看付款信息，后续开通其他“功能组”用户（比如系统管理，代理商等）也可以查看
     */
    private function getMerchantFilter()
    {
        if (IS_ROOT) {
            return null;
        } else {
            $userData = UserinfoModel::getBySystemUserID(UID);
            if (empty($userData)) {
                //如果不是关联的商户，那么给其一个用户不会出现的条件，以不显示任何列表信息
                $where['touserid'] = '-999';
            } else {
                $where['touserid'] = $userData['userid'];
            }

            return $where;
        }
    }

    /**
     * 预扣款-向用户发起扣款（包括数额在内）的通知
     * （在其他逻辑中等待用户确认后才真实扣款）
     *
     * @param int $id
     */
    public function innerPrePay($id = 0)
    {
        if (empty($id)) {
            $id = I('id');
        }

        if (empty($id)) {
            $id = $_POST['id'];
        }

        $mate = new ModelMate('innerpaynotice');
        $data = $mate->get($id);

        if (IS_POST) {
            $result = true;
            $errorMessage = '';

            $payamount = $_POST['payamount'];
            $data['payamount'] = $payamount;
            $userfeeamountcanuse = $_POST['userfeeamountcanuse'];

            if ((float)$data['payamount'] > (float)$userfeeamountcanuse) {
                $result = false;
                $errorMessage = '请确保本次消费的费用不能多于用户的可用费用。';
            }

            if ((float)$data['payamount'] <= 0) {
                $result = false;
                $errorMessage = '请确保本次消费的费用不能为0。';
            }

            if ($result) {
                $noticeStatuses = ConfigHelper::get1DArray('PAY_INNER_NOTICE_STATUSES', '', 'value');
                $noticeStatus = $noticeStatuses['RECEIVED_BY_RECEEIVER']; // C('PAY_INNER_NOTICE_STATUSES')
                $data['noticestatus'] = $noticeStatus;
                $data['receivedtime'] = time();

                $result = $mate->interact($data);
            }

            if ($result) {
                $fromuserinfo = UserinfoModel::getByKey($data['fromuserid']);
                $targetOpenid = $fromuserinfo['weixinopenid']; // 'oOjPas1SKwihAMngxQxCqmdYGiU4';

                $confirmUrl = 'http://' . WebHelper::getHostName() . U("Tencent/PayInner/launchPayConfirm", "id=" . $id);
                $refuseUrl = 'http://' . WebHelper::getHostName() . U("Tencent/PayInner/launchPayRefuse", "id=" . $id);

                $content = "本次消费扣减[$payamount]元，请 <a href='" . $confirmUrl . "'>点击确认</a>。" . StringHelper::getNewLineSymbol();
                $content .= "如果本付款信息有误，请  <a href='" . $refuseUrl . "'>点击取消</a>。";

                $result = WechatHelper::responseCustomerServiceText($targetOpenid, $content);

                if ($result == true) {
                    $this->success('更新成功', Cookie('__forward__'));
                } else {
                    $this->error('发送客户通知失败。');
                }
            } else {

                if (empty($errorMessage)) {
                    $errorMessage = $mate->model->getError();
                }
                if (empty($errorMessage)) {
                    $errorMessage = '未知错误。' . C('SYSTEM_ERROR_NOTICES');
                }
                $this->error($errorMessage);
            }
        } else {
            $data['userfeeamountcanuse'] = UserrolesModel::getMoneyAmountByUserID($data['fromuserid'], $data['touserid']); // TODO:需要计算用户在当前商户的可以使用的费用
            $this->assign('data', $data);
            //$this->meta_title = '转账支付通知信息';
            $this->assign('meta_title', '转账支付通知信息');
            $this->display();
        }
    }

    public function closePay()
    {
        $mate = new ModelMate('innerpaynotice');
        $noticeStatuses = ConfigHelper::get1DArray('PAY_INNER_NOTICE_STATUSES', '', 'value');
        $noticeStatus = $noticeStatuses['REFUSED_BY_RECEEIVER']; // C('PAY_INNER_NOTICE_STATUSES')

        $ids = $this->getUrlParaValue('id', false);

        if (!empty($ids)) {
            $idarray = explode(',', $ids);
            foreach ($idarray as $id) {
                $data = $mate->get($id);
                $data['noticestatus'] = $noticeStatus;
                $data['receivedtime'] = time();

                $result = $mate->interact($data);
            }
        }
    }


    public function setPayStatus()
    {

    }

    /**
     * 获取商户的二维码
     */
    public function qrCode()
    {
        $qrUrl = '';

        $userData = UserinfoModel::getBySystemUserID(UID);

        if (!empty($userData)) {
            $qrUrl = BizHelper::getQRCodeUrlByUser($userData);
        }

        $this->assign('qrUrl', $qrUrl);
        $this->display();
    }

    /**
     * 对账
     */
    public function checkBalance()
    {
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $where = null;

        $queryTimeBegin = I('queryTimeBegin');
        $queryTimeEnd = I('queryTimeEnd');

        if (empty($queryTimeBegin)) {
            $queryTimeBegin= date('Y-m-d',time());
        }
        $queryTimeBeginStamp = DateHelper::getTimestamp($queryTimeBegin . ' 0:0:0');

        if (empty($queryTimeEnd)) {
            $queryTimeEnd=  date('Y-m-d',time());
        }
        $queryTimeEndStamp = DateHelper::getTimestamp($queryTimeEnd . ' 23:59:59');

        $where['confirmedtime'] = array(
            'BETWEEN',
            "$queryTimeBeginStamp,$queryTimeEndStamp"
        );

        $where['noticestatus'] = 20 ;//PAY_INNER_NOTICE_STATUSES

        $merchantFilter = self::getMerchantFilter();
        if (!empty($merchantFilter)) {
            if ($where == null) {
                $where = $merchantFilter;
            } else {
                $where = array_merge($where, $merchantFilter);
            }
        }

        $list = $this->lists('innerpaynotice', $where);

        $noticeStatuses = ConfigHelper::get1DArray('PAY_INNER_NOTICE_STATUSES', 'value', 'display');
        $friendlymaps = array(
            'noticestatus' => $noticeStatuses
        );

        DBSetHelper::friendlyDisplay($list, $friendlymaps);

        $data['queryTimeBegin']=$queryTimeBeginStamp;
        $data['queryTimeEnd']=$queryTimeEndStamp;
        $this->assign('data', $data);

        $this->assign('_list', $list);
        $this->assign('meta_title', '对账功能列表');
        $this->display();
    }
}

?>