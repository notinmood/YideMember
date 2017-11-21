<?php
namespace Common\Model;

use Common\Common\ConfigHelper;
use Think\Model;
use Vendor\Hiland\Utils\Data\GuidHelper;

class UserrolesModel extends Model
{

    /**
     * 获取角色信息
     *
     * @param int|string $roleKey
     *            int类型的roleid或者字符串类型的roleguid
     * @return mixed
     */
    public static function get($roleKey)
    {
        $model = new UserrolesModel();
        $condition['roleid|roleguid'] = $roleKey;
        return $model->where($condition)->find();
    }

    /**
     * 更新角色信息
     *
     * @param array $data
     * @return boolean|number
     */
    public static function interact($data = null)
    {
        if (empty($data)) {
            return false;
        }

        $model = new UserrolesModel();

        $recordid = 0;
        $isaddrecord = true;
        /* 添加或新增基础内容 */
        if (empty($data['roleid'])) { // 新增数据

            if (empty($data['roleguid'])) {
                $data['roleguid'] = GuidHelper::newGuid();
            }

            if (empty($data['scantime'])) {
                $data['scantime'] = time();
            }

            $recordid = $model->data($data)->add(); // 添加基础内容

            if (!$recordid) {
                $model->error = '新增角色出错！';
                return false;
            }
        } else { // 更新数据

            $recordid = (int)$data['roleid'];
            $isaddrecord = false;
            $status = $model->data($data)->save(); // 更新基础内容
            if (false === $status) {
                $model->error = '更新角色出错！';
                return false;
            }
        }

        // TODO:需要并研究添加hook机制
        // hook('documentSaveComplete', array('model_id'=>$data['model_id']));

        // 行为记录
        if ($recordid && $isaddrecord) {
            action_log('add_role', 'role', $recordid, UID);
        }

        // 内容添加或更新完成
        return $recordid;
    }

    /**
     * 获取用户所有未出局的角色
     *
     * @param int $userID
     *            用户id
     * @param int $merchantID
     *            所属商户的id（默认为0，为零的时候获取本系统内用户在所有商户下未出局的角色）
     * @return array 符合条件的角色
     */
    public static function getUnOutRoles($userID, $merchantID = 0)
    {
        $outdegreearray = ConfigHelper::get1DArray('ROLE_OUT_DEGREES', '', 'value');
        $outdegree = $outdegreearray['OUT']; // C('ROLE_OUT_DEGREES')

        $where['outdegree'] = array(
            'neq',
            $outdegree
        );

        $result = self::getRoles($userID, $merchantID, null, $where);
        return $result;
    }

    /**
     *
     * @param int $userID
     * @param int $merchantID
     * @param int|int[] $outDegrees
     * @param array $whereAddon
     *            附加的WHERE过滤条件
     * @param string $orderBy
     * @return array
     */
    public static function getRoles($userID = 0, $merchantID = 0, $outDegrees = null, $whereAddon = null, $orderBy = 'roleid desc')
    {
        $userrole = D('userroles');
        $where = null;

        if ($userID > 0) {
            $where['userid'] = $userID;
        }

        if ($merchantID > 0) {
            $where['merchantid'] = $merchantID;
        }

        //过滤掉不是角色的信息
        $where['paytag'] = 0;//WEIXIN_PAY_TAGS

        if ($outDegrees != null) {
            if (is_numeric($outDegrees)) {
                $where['outdegree'] = $outDegrees;
            } elseif (is_array($outDegrees)) {
                $outdegreeCollection = implode(',', $outDegrees);
                $where['outdegree'] = array(
                    'in',
                    $outdegreeCollection
                );
            }
        }

        if (!empty($whereAddon)) {
            if ($where == null) {
                $where = $whereAddon;
            } else {
                $where = array_merge($where, $whereAddon);
            }
        }

        $roles = $userrole->where($where)
            ->order($orderBy)
            ->select();
        return $roles;
    }

    /**
     * 获取某角色当前的账目额度
     *
     * @param int $roleID
     * @param bool $onlyActived
     *            是否仅展示激活的费用（即如果有费用但未激活亦显示为0）。
     * @return float
     */
    public static function getMoneyAmount($roleID, $onlyActived = true)
    {
        $model = new UserrolesModel();
        $condition['roleid'] = $roleID;

        if ($onlyActived) {
            $condition['moneyamountactived'] = 1;
        }

        $result = $model->where($condition)->getField('moneyamount');

        return (float)$result;
    }

    /**
     * 获取某用户当前的账目额度
     *
     * @param int $userID
     * @param int $merchantID
     * @param bool $onlyActived
     *            是否仅展示激活的费用（即如果有费用但未激活亦显示为0）。
     * @return float
     */
    public static function getMoneyAmountByUserID($userID, $merchantID = 0, $onlyActived = true)
    {
        $totalAmountUsable = 0;

        $condition = null;
        if ($onlyActived) {
            $condition['moneyamountactived'] = 1;
        }

        $rolesUsable = self::getRoles($userID, $merchantID, null, $condition);

        if (empty($rolesUsable)) {
            return 0;
        }

        foreach ($rolesUsable as $role) {
            $totalAmountUsable += (float)$role['moneyamount'];
        }

        return $totalAmountUsable;
    }
}

?>