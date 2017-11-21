<?php
namespace Tencent\Model;

use Common\Common\ConfigHelper;
use Think\Model;
use Vendor\Hiland\Utils\Data\ReflectionHelper;

class UserfinanceModel extends Model
{

    /**
     * 使用反射的方式构建实例 （此方法暂时不使用）
     * 
     * @param unknown $financeSubject            
     * @return \Tencent\Model\Userfinance|unknown|object
     */
    public static function getUserFinance($financeSubject)
    {
        $isint = false;
        $financeSubjectConverted = (int) $financeSubject;
        if ((string) $financeSubjectConverted = $financeSubject) {
            $isint = true;
        }
        
        if ($isint) {
            $financeSubjects = ConfigHelper::get1DArray('SYSTEM_FINANCE_SUBJECTS', 'value', '');
            $financeSubject = $financeSubjects[$financeSubject];
        }
        
        $instance = null;
        try {
            $className = __NAMESPACE__ . '\Userfinance' . $financeSubject;
            $instance = ReflectionHelper::createInstance($className);
        } catch (\Exception $e) {}
        
        if ($instance == null) {
            $instance = new Userfinance();
        }
        
        return $instance;
    }
}

?>