<?php
namespace Hiland\Common;

use Think\Model;

class ExtentiblerepositoryModel extends Model
{

    public static function get($key, $category)
    {
        $model = new ExtentiblerepositoryModel();
        if (is_numeric($key)) {
            $where['targetid'] = $key;
        } else {
            $where['targetid|targetguid'] = $key;
        }
        
        $where['category'] = $category;
        $modelGotten = $model->where($where)->find();
        
        return $modelGotten;
    }
    
    public static function update($data){
        $model = new ExtentiblerepositoryModel();
        
        if ($data['id']) {
            $status = $model->save($data); // 更新信息内容
            if (false === $status) {
                $model->error = '更新信息出错！';
                return false;
            }
        } else {
            $status = $model->add($data); // 建立信息内容
            if (false === $status) {
                $model->error = '建立信息出错！';
                return false;
            }
        }
        
        return $status;
    }

}

?>