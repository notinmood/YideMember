<?php
/**
 * Created by PhpStorm.
 * User: devel
 * Date: 2016/3/14 0014
 * Time: 12:55
 */

namespace Hiland\Model;


use Think\Model;
use Vendor\Hiland\Utils\Data\StringHelper;
use Vendor\Hiland\Utils\DataConstructure\Queue;

class ViewMate
{
    /**
     * @var Model
     */
    var $mainModel;
    var $fields = '';
    var $tableQueue = null;
    var $joinQueue = null;
    var $onClauseQueue = null;

    /**
     * ViewMate constructor.
     * @param array $modelInfos 模型数组，二维数组，结构如下
     *$modelInfos = array(
     * array('userroles', 'A', 'A.*'),
     * array('userroles','B',''),
     * array('userinfo', 'C', 'C.displayname,C.weixinopenid')
     * );
     * @param array $onClauses 连接信息，结构如下
     * $onClauses = array(
     * 'A.parentid= B.roleid',
     * 'B.userid=C.userid'
     * );
     * @param  array|null $joinTypes
     */
    public function __construct($modelInfos, $onClauses, $joinTypes = null)
    {
        $this->tableQueue = new Queue();
        $this->onClauseQueue = new Queue($onClauses);
        $this->joinQueue = new Queue($joinTypes);

        foreach ($modelInfos as $modelInfo) {
            $modleName = $modelInfo[0];
            $modleAlias = $modelInfo[1];
            $modelFields = $modelInfo[2];

            if (!empty($modelFields)) {
                $this->fields .= $modelFields . ',';
            }

            $model = new Model($modleName);
            $tableName = $model->getTableName();
            $this->tableQueue->push($tableName . ' ' . $modleAlias);

            if (empty($this->mainModel)) {
                $this->mainModel = $model;
            }
        }


        if (StringHelper::isEndWith($this->fields, ',')) {
            $this->fields = substr($this->fields, 0, strlen($this->fields) - 1);
        }
    }

    /**
     * @param string $where where过滤条件，不带关键字WHERE,例如 "id>10"
     * @param string $addon 其他附件信息，比如排序，需要自己添加关键字，比如 排序的时候， "ORDER BY id desc"
     * @return mixed
     */
    public function select($where = null, $addon = null)
    {
        $sql = self::buildSql($where, $addon);
        //return $this->mainModel->getTableName();
        //return $this->mainModel->query($sql);

        $model= new Model();
        return $model->query($sql);
    }

    private function buildSql($where = null, $addon = null)
    {
        $mainTable = $this->tableQueue->pop();
        $sql = "SELECT $this->fields FROM $mainTable ";

        while ($onClause = $this->onClauseQueue->pop()) {
            $join = $this->joinQueue->pop();
            if (empty($join)) {
                $join = ' LEFT JOIN ';
            }
            $sql .= $join;

            $table = $this->tableQueue->pop();
            $sql .= $table;

            $sql .= ' ON ' . $onClause;
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . $where;
        }

        if (!empty($addon)) {
            $sql .= ' ' . $addon;
        }

        return $sql;
    }

    public function showSql($where = null, $addon = null)
    {
        $sql = self::buildSql($where, $addon);
        return $sql;
    }
}