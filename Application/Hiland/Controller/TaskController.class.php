<?php
/**
 * Created by PhpStorm.
 * User: devel
 * Date: 2016/3/8 0008
 * Time: 9:40
 */

namespace Hiland\Controller;


use Hiland\Common\CommonHelper;
use Think\Controller;
use Vendor\Hiland\Utils\Data\DateHelper;

class TaskController extends Controller
{
    public function myTask()
    {
        CommonHelper::log('定时测试', '执行的时刻为' . DateHelper::format());
    }

    public function b()
    {
        //B('Behavior\CronRun');
    }
}