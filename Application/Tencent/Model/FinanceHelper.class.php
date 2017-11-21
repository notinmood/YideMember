<?php
namespace Tencent\Model;

use Common\Common\ConfigHelper;
use Common\Model\UserinfoModel;
use Common\Model\UserrolesModel;
use Think\Model;
use Vendor\Hiland\Utils\Data\OperationHelper;
use Vendor\Hiland\Utils\DataModel\ModelMate;

class FinanceHelper
{

    /**
     * 记录会员的账目流水(流入)
     *
     * @param array $financeData userfinance结构的数据实体
     * @param bool $useTrans 是否启用事务
     * @return string|true 正确返回true，错误返回错误原因
     */
    public static function flowIn($financeData, $useTrans = true)
    {
        $result = true;
        $targetuserid = $financeData['userid'];
        $relationuserid = $financeData['relationuserid'];

        $targetuser = UserinfoModel::getByKey($targetuserid);
        $relationuser = UserinfoModel::getByKey($relationuserid);

        $targetroleid = $financeData['roleid'];
        $relationroleid = $financeData['relationroleid'];

        $targetrole = UserrolesModel::get($targetroleid);
        $relationrole = UserrolesModel::get($relationroleid);

        //dump($targetuser);
        //dump($targetrole);

        if (!array_key_exists('exchangetime', $financeData)) {
            $financeData['exchangetime'] = time();
        }

        if (!array_key_exists('content', $financeData)) {
            $financeData['content'] = self::buildFlowContent($financeData['subjecttype'], $targetuser, $relationuser, $targetrole, $relationrole);
        }

        $financeData = self::fixMoneyDirection($financeData);

        if ($useTrans) {
            $transModel = new Model();
            $transModel->startTrans();
        }

        // 1 如果是商户，跳过此步直接记录到用户名下。如果是普通用户则计算流入到哪个角色下
        $resultA = true;
        $resultB = true;

        if ($targetuser['ismerchant'] == 1) {
            //2 更改商户用户名下的moneyamount值
            $resultB = UserinfoModel::changeMoneyAmount($targetuserid, $financeData['moneyamount']);
        } else {
            if (!empty($targetrole)) {
                $targetrole['moneyamount'] += $financeData['moneyamount'];
                $resultA = UserrolesModel::interact($targetrole);

                if ((int)$targetrole['moneyamountactived'] == 1) {
                    // 2 确定是否更改用户名下的moneyamount值
                    $resultB = UserinfoModel::changeMoneyAmount($targetuserid, $financeData['moneyamount']);
                } else {
                    self::tryActiveRoleMoneyAmount($targetrole, false);
                }
            }
        }

        // 3 记录流水信息
        $mate = new ModelMate('userfinance');
        $resultC = $mate->interact($financeData);

        if ($resultA && $resultB && $resultC) {
            $result = true;
            if ($useTrans) {
                /** @noinspection PhpUndefinedVariableInspection */
                $transModel->commit();
            }
        } else {
            $result = false;
            if ($useTrans) {
                /** @noinspection PhpUndefinedVariableInspection */
                $transModel->rollback();
            }
        }

        return $result;
    }

    /**
     * @param $subjectType
     * @param $targetUser
     * @param null $relationUser
     * @param null $targetRole
     * @param null $relationRole
     * @return string
     */
    private
    static function buildFlowContent($subjectType, $targetUser, $relationUser = null, $targetRole = null, $relationRole = null)
    {
        $result = '';
        $targetUserName = $targetUser['displayname'];
        $relationUserName = $relationUser['displayname'];

        $targetRoleName = $targetRole['rolename'];
        $relationRoleName = $relationUser['rolename'];

        $targetRoleID = $targetRole['roleid'];
        $relationRoleID = $relationUser['roleid'];

        switch ($subjectType) {
            case 10:
            case 'CZ': // SYSTEM_FINANCE_SUBJECTS
                $result = "用户$targetUserName 充值";
                break;
            case -10:
            case 'TX':
                $result = "用户$targetUserName 提现";
                break;
            case -3:
            case 'HYXF':
                $result = "用户$targetUserName 在商家$relationUserName 消费,支付";
                if (!empty($targetRole)) {
                    $result .= "(用户角色名称： $targetRoleName,角色id：$targetRoleID)";
                }
                break;
            case -2:
            case 'GWZF':
                $result = "用户$targetUserName 向$relationUserName 购物,支付";

                if (!empty($targetRole)) {
                    $result .= "(用户角色名称： $targetRoleName,角色id：$targetRoleID)";
                }

                if (!empty($relationRole)) {
                    $result .= "(对方用户角色名称： $relationRoleName,角色id：$relationRoleID)";
                }
                break;
            case -1:
            case 'ZGSQ':
                $result = "用户$targetUserName 向$relationUserName 资格申请,支付";

                if (!empty($targetRole)) {
                    $result .= "(用户角色名称： $targetRoleName,角色id：$targetRoleID)";
                }

                if (!empty($relationRole)) {
                    $result .= "(对方用户角色名称： $relationRoleName,角色id：$relationRoleID)";
                }
                break;
            case 1:
            case 'ZGSY':
                $result = "用户$targetUserName 受理$relationUserName 资格申请,收益";

                if (!empty($targetRole)) {
                    $result .= "(用户角色名称： $targetRoleName,角色id：$targetRoleID)";
                }

                if (!empty($relationRole)) {
                    $result .= "(对方用户角色名称： $relationRoleName,角色id：$relationRoleID)";
                }
                break;
            case 2:
            case 'SHSM':
                $result = "商户$targetUserName 向$relationUserName 售卖,营收";

                if (!empty($targetRole)) {
                    $result .= "(用户角色名称： $targetRoleName,角色id：$targetRoleID)";
                }

                if (!empty($relationRole)) {
                    $result .= "(对方用户角色名称： $relationRoleName,角色id：$relationRoleID)";
                }
                break;
            case 3:
            case 'SHFW':
                $result = "商户$targetUserName 向$relationUserName 服务,营收";
                if (!empty($relationRole)) {
                    $result .= "(被服务的用户角色名称： $relationRoleName,角色id：$relationRoleID)";
                }
                break;
            default:
                $result = "用户$targetUserName 向$relationUserName 支付";
                break;
        }

        return $result;
    }

    /**
     * 修正资金的正负符号
     * @param $financeData
     * @return mixed
     */
    private
    static function fixMoneyDirection(&$financeData)
    {
        $directions = ConfigHelper::get1DArray('SYSTEM_FINANCE_SUBJECTS', 'value', 'direction');
        $subjectType = $financeData['subjecttype'];
        if (array_key_exists($subjectType, $directions)) {
            $financeData['moneyamount'] = $directions[$subjectType] * $financeData['moneyamount'];
        }

        return $financeData;
    }

    /**
     * 尝试激活角色的费用
     * @param $roleData
     * @param bool $useTrans
     */
    public
    static function tryActiveRoleMoneyAmount($roleData, $useTrans = true)
    {
        if (!$roleData['moneyamountactived']) {
            if ($roleData['moneyamount'] >= $roleData['moneyamountactivethreshold']) {
                self::activeRoleMoneyAmount($roleData, $useTrans);
            }
        }
    }

    /**
     * 激活角色的费用
     * @param $roleData
     * @param bool|true $useTrans
     * @return bool|string
     */
    public
    static function activeRoleMoneyAmount($roleData, $useTrans = true)
    {
        if ((int)$roleData['moneyamountactived'] == 1) {
            return OperationHelper::buildErrorResult('此角色的费用已经被激活，请不要重复激活。');
        }

        if ($useTrans) {
            $model = new Model();
            $model->startTrans();
        }
        // 1 变更角色的激活状态
        $roleData['moneyamountactived'] = 1;
        $resultA = UserrolesModel::interact($roleData);

        // 2 改变用户的可用费用，待激活费用
        $userID = $roleData['userid'];
        $resultB = UserinfoModel::changeMoneyAmount($userID, (float)$roleData['moneyamount']);
        $resultC = UserinfoModel::changeMoneyAmountNeedActive($userID, (0 - (float)$roleData['moneyamountactivethreshold']));

        if ($useTrans) {
            if ($resultA && $resultB && $resultC) {
                /** @noinspection PhpUndefinedVariableInspection */
                $model->commit();
            } else {
                /** @noinspection PhpUndefinedVariableInspection */
                $model->rollback();
            }
        }

        return $resultA && $resultB;
    }

    /**
     * 记录会员的账目流水(流出)
     *
     * @param array $financeData
     *            userFinance结构的数据数组
     * @param bool $useTrans
     * @return string|true 正确返回true，错误返回错误原因
     */
    public
    static function flowOut($financeData, $useTrans = true)
    {
        $result = true;
        $targetUserID = $financeData['userid'];
        $relationuserid = $financeData['relationuserid'];

        $targetuser = UserinfoModel::getByKey($targetUserID);
        $relationuser = UserinfoModel::getByKey($relationuserid);

        $targetroleid = $financeData['roleid'];
        $relationroleid = $financeData['relationroleid'];

        $targetrole = UserrolesModel::get($targetroleid);
        $relationrole = UserrolesModel::get($relationroleid);

        $merchantid = $financeData['merchantid'];

        if (!array_key_exists('exchangetime', $financeData)) {
            $financeData['exchangetime'] = time();
        }

        if (!array_key_exists('content', $financeData)) {
            $financeData['content'] = self::buildFlowContent($financeData['subjecttype'], $targetuser, $relationuser, $targetrole, $relationrole);
        }

        if ($useTrans) {
            $transModel = new Model();
            $transModel->startTrans();
        }

        $financeData = self::fixMoneyDirection($financeData);

        $totalAmountUsable = 0;
        // 1 更新会员角色的账额
        if (empty($financeData['roleid'])) { // 不指定角色的消费，比如在商家消费时，有系统判断使用当前用户在此商家的某个（些）角色扣费。
            $where['moneyamountactived'] = 1;
            $rolesUsable = UserrolesModel::getRoles($targetUserID, $merchantid, null, $where, 'roleid');
            if (empty($rolesUsable)) {
                if ($useTrans) {
                    /** @noinspection PhpUndefinedVariableInspection */
                    $transModel->rollback();
                }
                return OperationHelper::buildErrorResult('用户尚未参加商家的活动。'); // "错误：用户尚未参加商家的活动。";
            }

            // 费用支出前需要判断 个人各角色下账目的费用是否有足够的钱可以完成支付
            foreach ($rolesUsable as $role) {
                $totalAmountUsable += (float)$role['moneyamount'];
            }

            $amountNeedToCut = abs($financeData['moneyamount']);
            if ($amountNeedToCut > $totalAmountUsable) {
                if ($useTrans) {
                    /** @noinspection PhpUndefinedVariableInspection */
                    $transModel->rollback();
                }

                return OperationHelper::buildErrorResult('用户在商家的余额不足，无法完成支付！'); // '错误：用户在商家的余额不足，无法完成支付！';
            } else {
                $feeNeedToThisCut = 0;
                foreach ($rolesUsable as $role) {
                    if ($amountNeedToCut == 0) {
                        break;
                    }

                    $moneyAmountThisRow = (float)$role['moneyamount'];

                    if ($amountNeedToCut > $moneyAmountThisRow) {
                        $feeNeedToThisCut = $moneyAmountThisRow;
                        $amountNeedToCut -= $moneyAmountThisRow;
                    } else {
                        $feeNeedToThisCut = $amountNeedToCut;
                        $amountNeedToCut = 0;
                    }

                    $role['moneyamount'] = $role['moneyamount'] - $feeNeedToThisCut;
                    $flowData = $financeData;
                    $flowData['moneyamount'] = 0 - $feeNeedToThisCut; // 支出用负数表示
                    $result = self::transferRoleMoneyAndFlow($role, $flowData);

                    if (!OperationHelper::getResult($result)) {
                        if ($useTrans) {
                            /** @noinspection PhpUndefinedVariableInspection */
                            $transModel->rollback();
                        }
                        return $result;
                        break;
                    }
                }
            }
        } else { // 指定角色的转账、消费等
            $model = new UserrolesModel();
            $where['roleid'] = $financeData['roleid'];
            $roleData = $model->where($where)->find();
            if (empty($roleData)) {
                if ($useTrans) {
                    /** @noinspection PhpUndefinedVariableInspection */
                    $transModel->rollback();
                }
                return OperationHelper::buildErrorResult('指定的角色不存在。');
            }

            if ((int)$roleData['moneyamountactived'] == 0) {
                if ($useTrans) {
                    /** @noinspection PhpUndefinedVariableInspection */
                    $transModel->rollback();
                }
                return OperationHelper::buildErrorResult('指定的角色尚未激活。');
            }

            if ($roleData['moneyamount'] < abs($financeData['moneyamount'])) {
                if ($useTrans) {
                    /** @noinspection PhpUndefinedVariableInspection */
                    $transModel->rollback();
                }
                return OperationHelper::buildErrorResult('用户的余额不足，无法完成支付。');
            }

            $roleData['moneyamount'] = $roleData['moneyamount'] + $financeData['moneyamount'];
            $result = self::transferRoleMoneyAndFlow($roleData, $financeData);
            if (!OperationHelper::getResult($result)) {
                if ($useTrans) {
                    /** @noinspection PhpUndefinedVariableInspection */
                    $transModel->rollback();
                }
                return $result;
            }
        }

        // 2 更新会员的当前账额
        $targetModel = new UserinfoModel();
        $where['userid'] = $targetUserID;

        $result = $targetModel->where($where)->setInc('moneyamount', $financeData['moneyamount']);

        if (!OperationHelper::getResult($result)) {
            if ($useTrans) {
                /** @noinspection PhpUndefinedVariableInspection */
                $transModel->rollback();
            }
            return OperationHelper::buildErrorResult('设置会员的账目信息出错');
        }

        if ($useTrans) {
            /** @noinspection PhpUndefinedVariableInspection */
            $transModel->commit();
        }

        return $result;
    }

    private
    static function transferRoleMoneyAndFlow($rowData, $flowData)
    {
        $result = true;

        // 1 核减角色内的费用
        $result = UserrolesModel::interact($rowData);
        $result = OperationHelper::getResult($result);

        if ($result == false) {
            return OperationHelper::buildErrorResult('更改角色信息出错。', '出错的角色信息为' . json_encode($rowData), 0);
        }

        // 2记录会员财务流水信息

        if (!array_key_exists('exchangetime', $flowData)) {
            $flowData['exchangetime'] = time();
        }

        if (!array_key_exists('content', $flowData)) {
            // $flowData['content'] = self::buildFlowContent($flowData['subjecttype'], $targetuser, $relationuser,$rowData);
            // $this->getUserFinance($flowData['subjecttype'])->getFlowContent($targetuser, $relationuser, $flowData['moneyamount']);
        }
        $roleName = $rowData['rolename'];
        $roleID = $rowData['roleid'];
        //$flowData['content'] .= "(角色名称：$roleName,角色id：$roleID)";

        // TODO 需要确定关联的角色信息
        // $flowData['relationroleid'] = ;

        $mate = new ModelMate('userfinance');
        $result = $mate->interact($flowData);

        if ($result == false) {
            return OperationHelper::buildSystemErrorResult('添加财务流水失败。', '出错的财务流水信息为' . json_encode($flowData));
        }

        return $result;
    }
}

?>