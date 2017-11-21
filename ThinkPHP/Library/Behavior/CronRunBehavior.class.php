<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Behavior;

use Think\Behavior;

defined('THINK_PATH') or exit();

/**
 * 自动执行任务 在官方的版本上有修改
 * @category   Extend
 * @package  Extend
 * @subpackage  Behavior
 * @author   liu21st <liu21st@gmail.com>
 * http://www.sucaihuo.com/php/712.html
 * TODO 本方法待测试验证
 */
class CronRunBehavior extends Behavior
{
    public function run(&$params)
    {
        if (C('CRON_CONFIG_ON')) {
            $this->checkTime();
        }

    }

    private function checkTime()
    {
        if (S('CRON_CONFIG')) {
            $crons = S('CRON_CONFIG');
        } else if (C('CRON_CONFIG')) {
            $crons = C('CRON_CONFIG');
        }
        if (!empty($crons) && is_array($crons)) {
            $update = false;
            $log = array();
            foreach ($crons as $key => $cron) {
                if (empty($cron[2]) || $_SERVER['REQUEST_TIME'] > $cron[2]) {
                    G('cronStart');
                    R($cron[0]);
                    G('cronEnd');
                    $_useTime = G('cronStart', 'cronEnd', 6);
                    $cron[2] = $_SERVER['REQUEST_TIME'] + $cron[1];
                    $crons[$key] = $cron;
                    $log[] = 'Cron:' . $key . ' Runat ' . date('Y-m-d H:i:s') . ' Use ' . $_useTime . ' s ' . "\r\n";
                    $update = true;
                }
            }
            if ($update) {
                //\Think\Log::write(implode('', $log));
                S('CRON_CONFIG', $crons);
            }
        }

    }
}