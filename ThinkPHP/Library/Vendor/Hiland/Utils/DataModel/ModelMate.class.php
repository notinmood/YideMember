<?php
namespace Vendor\Hiland\Utils\DataModel;

use Think\Model;

/**
 * 模型辅助器
 * 封装模型与数据库交互的常用操作
 * @author devel
 */
class ModelMate
{

    var $model;

    /**
     * 构造函数
     *
     * @param string|model $model
     *            其可以是一个表示model名称的字符串；
     *            也可以是一个继承至Think\Model的类型
     */
    public function __construct($model)
    {
        if (is_string($model)) {
            $this->model = M($model);
        } else {
            $this->model = $model;
        }
    }

    /**
     * 按照主键获取信息
     *
     * @param int|string $key
     *            查询信息的主键值
     * @param string $keyName
     *            查询信息的主键名称
     * @return array 模型实体数据
     */
    public function get($key, $keyName = 'id')
    {
        $condition[$keyName] = $key;
        return $this->model->where($condition)->find();
    }

    /**
     * 根据条件获取一条记录
     * @param array $condition 过滤条件
     * @return array 符合条件的结果，一维数组
     * @example
     * $where= array();
     * $where['shopid'] = $merchantScanedID;
     * $where['openid'] = $openId;
     * $relation = $buyerShopMate->find($where);
     */
    public function find($condition = array())
    {
        return $this->model->where($condition)->find();
    }

    /**
     * 根据条件获取多条记录
     * @param array $condition
     * @return array 符合条件的结果，多维数组
     * @example
     * $where= array();
     * $where['shopid'] = $merchantScanedID;
     * $where['openid'] = $openId;
     * $relation = $buyerShopMate->select($where);
     */
    public function select($condition = array())
    {
        return $this->model->where($condition)->select();
    }

    /**
     * 交互信息
     *
     * @param array $data
     *            待跟数据库交互的模型实体数据
     * @param string $keyName
     *            当前模型的数据库表的主键名称
     * @return boolean|number
     */
    public function interact($data = null, $keyName = 'id')
    {
        if (empty($data)) {
            /* 获取数据对象 */
            $data = $this->model->create($_POST);
        }

        if (empty($data)) {
            return false;
        }

        $recordID = 0;
        $isAddOperation = true;

        //$content= json_encode($data);
        //CommonHelper::log('sssss',$content);

        /* 添加或新增基础内容 */
        if (empty($data[$keyName])) { // 新增数据

            $recordID = $this->model->data($data)->add(); // 添加基础内容

            if (!$recordID) {
                $this->model->setError('新增数据出错！');
                return false;
            }
        } else { // 更新数据
            $recordID = $data[$keyName];
            $isAddOperation = false;
            $status = $this->model->data($data)->save(); // 更新基础内容
            if (false === $status) {
                $this->model->setError('更新数据出错！');
                return false;
            }
        }

        // TODO:需要并研究添加hook机制
        // hook('documentSaveComplete', array('model_id'=>$data['model_id']));

        // 行为记录
        if ($recordID && $isAddOperation) {
            // action_log('add_role', 'role', $recordid, UID);
        }

        // 内容添加或更新完成
        return $recordID;
    }

    /**
     * 获取某记录的字段的值
     * @param int|string $key
     * @param string $feildName
     * @param string $keyName
     * @return mixed 字段的值
     */
    public function getValue($key, $feildName, $keyName = 'id')
    {
        $condition[$keyName] = $key;
        return $this->model->where($condition)->getField($feildName);
    }

    /**
     * 设置某记录的字段的值
     * @param int|string $key
     * @param string $feildName
     * @param mixed $feildValue
     * @param string $keyName
     * @return bool|int 成功时返回受影响的行数，失败时返回false
     */
    public function setValue($key, $feildName, $feildValue, $keyName = 'id')
    {
        $condition[$keyName] = $key;
        return $this->model->where($condition)->setField($feildName, $feildValue);
    }

    /**
     * 查找单个值
     * @param string $searcher 要查找的内容
     * @param string|null $whereClause
     * @return null|mixed
     */
    public function queryValue($searcher, $whereClause = null)
    {
        $tableName = $this->model->getTableName();
        $sql = "SELECT $searcher FROM $tableName";
        if (!empty($whereClause)) {
            $sql .= ' where ' . $whereClause;
        }

        $dbset = $this->query($sql);

        if ($dbset) {
            return $dbset[0][$searcher];
        } else {
            return null;
        }
    }

    /**
     * 执行SQL语句，如果语句里面涉及到本模型对应的表名称，建议不要直接写。可以使用“关键字”  __MODELTABLENAME__,或者__MTN__ ，本函数自动翻译为带前缀的表名称
     * @param $sql
     * @return mixed
     */
    public function query($sql)
    {
        $tableName = '';
        if (strstr($sql, '__MODELTABLENAME__')) {
            $tableName = $this->model->getTableName();
            $sql = str_replace('__MODELTABLENAME__', $tableName, $sql);
        }

        if (strstr($sql, '__MTN__')) {
            if ($tableName == '') {
                $tableName = $this->model->getTableName();
            }

            $sql = str_replace('__MTN__', $tableName, $sql);
        }

        return $this->model->query($sql);
    }
}
