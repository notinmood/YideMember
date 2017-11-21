<?php
namespace Common\Model;

use Think\Model;
use Vendor\Hiland\Utils\DataModel\ModelMate;

class UserinfoModel extends Model
{

    /**
     * 更新用户
     *
     * @param array $data
     *            在userid为empty的情形下，需要再查找openid是否存在，如果存在save，如果不存在add
     * @param bool $useCache
     * @return mixed 更新失败返回false，更新成功返回会员的id
     */
    public static function interact($data, $useCache = true)
    {
        $model = new UserinfoModel();
        $recordID = 0;
        $isAddOpereation = true;
        /* 添加或新增基础内容 */
        if (empty($data['userid'])) { // 新增数据

            // 通过openid判断此用户是否在系统内存在
            $openid = $data['weixinopenid'];
            $targetuser = self::getByOpenID($openid);

            dump($targetuser);
            if (empty($targetuser)) {
                $recordID = $model->data($data)->add(); // 添加基础内容

                if (!$recordID) {
                    $model->error = '新增注册用户出错！';
                    return false;
                }
            } else {
                $data['userid'] = $targetuser['userid'];
                $recordID = $data['userid'];
                $isAddOpereation = false;
                $status = $model->data($data)->save(); // 更新基础内容
                if (!$status) {
                    $model->error = '更新注册用户出错！';
                    return false;
                }
            }
        } else { // 更新数据
            $recordID = $data['userid'];
            $isAddOpereation = false;
            $status = $model->data($data)->save(); // 更新基础内容
            if (!$status) {
                $model->error = '更新注册用户出错！';
                return false;
            }
        }

        // TODO:需要并研究添加hook机制
        // hook('documentSaveComplete', array('model_id'=>$data['model_id']));

        // 行为记录
        if ($recordID && $isAddOpereation) {
            action_log('add_user', 'user', $recordID, UID);
        }

        if ($useCache) {
            $cacheKey = "userinfo:keyid-$recordID";
            S($cacheKey, $data, 60);
        }

        return $recordID;
    }

    /**
     * 通过微信openid获取本地用户信息
     *
     * @param string $openID
     *            微信openid
     * @return array 本地用户信息
     */
    public static function getByOpenID($openID)
    {
        //$userinfo = new UserinfoModel();
        $mate= new ModelMate('userinfo');
        $condition['weixinopenid'] = $openID;
        $userinfo = $mate->find($condition);//->where($condition)->find();
        return $userinfo;
    }

    /**
     * 依据关联信息，根据系统的用户id获取本地用户
     * @param int $systemUserID onethink系统用户id
     * @return UserinfoModel|mixed
     */
    public static function getBySystemUserID($systemUserID)
    {
        $userinfo = new UserinfoModel();
        $condition['systemuserid'] = $systemUserID;
        $userinfo = $userinfo->where($condition)->find();
        return $userinfo;
    }

    /**
     * 根据传进来的keyid智能识别是userid还是vipid，返回用户信息
     *
     * @param int $keyID
     * @param bool $useCache
     * @return mixed|null
     */
    public static function getByKey($keyID, $useCache = true)
    {
        $data = null;

        $cacheKey = "userinfo:keyid-$keyID";

        if ($useCache) {
            $result = S($cacheKey);
            if (!empty($result)) {
                return $result;
            }
        }

        $userinfo = new UserinfoModel();

        if ($keyID > 0 && $keyID <= C('WEIXIN_USER_VIPHOLDVALUE')) {
            $condition['vipid'] = $keyID;
            $data = $userinfo->where($condition)->find();
        } else {
            $data = $userinfo->find($keyID);
        }

        if ($useCache) {
            S($cacheKey, $data, 60);
        }

        return $data;
    }

    /**
     * 给会员设置vipid信息
     *
     * @param int $keyID
     *            根据传进来的keyid智能识别是userid还是vipid
     * @return mixed
     */
    public static function setVIPID($keyID)
    {
        $result = true;
        $userinfo = new UserinfoModel();

        if ($keyID > 0 && $keyID <= C('WEIXIN_USER_VIPHOLDVALUE')) {
            $condition['vipid'] = $keyID;
        } else {
            $condition['userid'] = $keyID;
        }

        $vipid = $userinfo->where($condition)->getField('vipid');
        if (empty($vipid)) {
            $vipid = self::generateVIPID();
            $result = $userinfo->where($condition)->setField('vipid', $vipid);
        }

        if ($result) {
            self::cleanCache($keyID);
            return $vipid;
        } else {
            return false;
        }
    }

    private static function generateVIPID()
    {
        $vipidmaxhold = C('WEIXIN_USER_VIPHOLDVALUE');

        $vipid = null;
        $needloop = true;
        $loopTimes = 0;

        while ($needloop) {
            if ($loopTimes >= $vipidmaxhold) {
                throw new \Exception("VIP资源已经用尽。");
                break;
            }
            $loopTimes++;

            $vipid = mt_rand(1, $vipidmaxhold);
            $model = new UserinfoModel();
            $where['vipid'] = $vipid;
            $result = $model->where($where)->find();

            if (empty($result)) {
                $needloop = false;
            }
        }

        return $vipid;
    }

    private static function cleanCache($keyID, $cacheKeyFeild = 'keyid')
    {
        if (!empty($keyID)) {
            $cacheKey = "userinfo:$cacheKeyFeild-$keyID";
            S($cacheKey, null);
        }
    }

    /**
     * 获取某用户当前的账目额度
     *
     * @param int $keyID
     * @return float
     */
    public static function getMoneyAmount($keyID)
    {
        $model = new UserinfoModel();

        if ($keyID > 0 && $keyID <= C('WEIXIN_USER_VIPHOLDVALUE')) {
            $condition['vipid'] = $keyID;
        } else {
            $condition['userid'] = $keyID;
        }

        $result = $model->where($condition)->getField('moneyamount');
        return (float)$result;
    }

    /**
     * 变更用户当前的账目额度
     *
     * @param int|string $keyID
     * @param float $moneyAmountDelta
     *            变更的变量
     * @return bool
     */
    public static function changeMoneyAmount($keyID, $moneyAmountDelta)
    {
        $targetModel = new UserinfoModel();
        if ($keyID > 0 && $keyID <= C('WEIXIN_USER_VIPHOLDVALUE')) {
            $condition['vipid'] = $keyID;
        } else {
            $condition['userid'] = $keyID;
        }

        $result = $targetModel->where($condition)->setInc('moneyamount', $moneyAmountDelta);
        if ($result) {
            self::cleanCache($keyID);
        }
        return $result;
    }

    /**
     * 变更用户当前的待激活账目额度
     *
     * @param int|string $keyID
     * @param float $moneyAmountDelta
     *            变更的变量
     * @return bool
     */
    public static function changeMoneyAmountNeedActive($keyID, $moneyAmountDelta)
    {
        $targetModel = new UserinfoModel();
        if ($keyID > 0 && $keyID <= C('WEIXIN_USER_VIPHOLDVALUE')) {
            $condition['vipid'] = $keyID;
        } else {
            $condition['userid'] = $keyID;
        }

        $result = $targetModel->where($condition)->setInc('moneyamountneedactive', $moneyAmountDelta);
        if ($result) {
            self::cleanCache($keyID);
        }
        return $result;
    }
}

?>