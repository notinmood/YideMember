<?php
namespace Admin\Controller;

class HilandController extends AdminController
{

    /**
     * 修改数据库的值
     * 
     * @param string $modelname
     *            目标模型名称（通常为数据库表除去前缀的表名称）
     * @param array $changingdataarray
     *            变更后的信息，二维数组： $数据库表字段名称=>$待修改的值
     *            array(
     *            'ismaster' => 1
     *            );
     * @param string $dbwherefeildname
     *            在where条件中，要过滤的数据库字段名称
     * @param string $urlparaname
     *            在url中传递的参数名称
     * @param array $opereateresultmessage
     *            操作完成后的，成败信息
     */
    protected function setDatabaseValue($modelname, $changingdataarray, $dbwherefeildname = 'id', $urlparaname = 'id', $opereateresultmessage = array(
            'success' => '操作成功！',
            'error' => '操作失败！'
        ))
    {
        $id = $this->getUrlParaValue($urlparaname, true);
        
        $wheremap[$dbwherefeildname] = array(
            'in',
            $id
        );
        
        $this->editRow($modelname, $changingdataarray, $wheremap, $opereateresultmessage);
    }

    protected function getUrlParaValue($urlparaname = 'id', $isWarmIfEmpty = false)
    {
        $id = array_unique((array) I($urlparaname, 0));
        
        $id = is_array($id) ? implode(',', $id) : $id;
        if (empty($id) && $isWarmIfEmpty) {
            $this->error('请选择要操作的数据!');
        }
        
        return $id;
    }
}

?>