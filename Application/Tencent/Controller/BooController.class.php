<?php
namespace Tencent\Controller;

use Think\Controller;
use Common\Model\UserinfoModel;
use Common\Model\UserrolesModel;
use Vendor\Hiland\Utils\Data\OperationHelper;
use Tencent\Model\UserfinanceModel;
use Vendor\Hiland\Utils\Web\EnvironmentHelper;
use Vendor\Hiland\Utils\Data\Enum;
use Vendor\Hiland\Utils\Data\GuidHelper;

class BooController extends Controller
{

    public function cacheop($keyid = 100001, $usecache = null)
    {
        if (empty($usecache)) {
            $usecache = false;
        } else {
            $usecache = true;
        }
        
        $data = UserinfoModel::getByKey($keyid, $usecache);
        // $data['misc2']='ppp';
        // UserinfoModel::interact($data);
        // $data= UserinfoModel::getByKey($keyid);
        dump($usecache);
        dump($data);
    }
    
    public function usermoneyamountop($keyid=100001){
        $result= UserinfoModel::getMoneyAmount($keyid);
        dump($result);
    }
    
    public function rolemoneyamountop($keyid=100001){
        $result= UserrolesModel::getMoneyAmount($keyid);
        dump($result);
    }
    
    public function rolemoneyamountop2($userid=100001,$merchantid=100002){
        dump(UserrolesModel::getRoles($userid,$merchantid));
        dump(UserrolesModel::getMoneyAmountByUserID($userid,$merchantid));
    }
    
    public function oprationresultop(){
        dump(OperationHelper::getResult(''));
        dump(OperationHelper::getResult('0'));
        dump(OperationHelper::getResult('sss'));
        dump(OperationHelper::getResult(0));
        dump(OperationHelper::getResult(1));
        dump(OperationHelper::getResult(-1));
        dump(OperationHelper::getResult(true));
        dump(OperationHelper::getResult(false));
        dump(OperationHelper::getResult(null));
        
        dump('-------------------');
        $errorResult= OperationHelper::buildErrorResult('余额不足，付款失败','用户aa账号的余额不足，付款失败，请充值后再付款。');
        dump(OperationHelper::getResult($errorResult));
        dump( OperationHelper::getErrorMessage($errorResult));
        dump( OperationHelper::getErrorElement($errorResult,'details'));
        
    }
    
    public function userfinanceop($subject=-1){
        $userFinance= UserfinanceModel::getUserFinance($subject);
        dump($userFinance);
        
//         $result= $userFinance->getPayUsageName();
//         dump($result);
    }
    
    public function typeconvertop(){
        dump((string)false);
    }
    
    public function osop(){
        dump(EnvironmentHelper::getOS());
    }
    
    public function enumop(){
        dump(new MyEnum(MyEnum::HI));
        dump(new MyEnum(MyEnum::BY));
        //Use __default
        dump(new MyEnum());
        
        try {
            new MyEnum("I don't exist");
        } catch (\UnexpectedValueException $e) {
            dump($e->getMessage());
        }
    }
    

    
    public function funcexist($funcname='pcntl_alarm'){
        if(function_exists($funcname)){
            dump("$funcname OK是可以使用的");
        }else{
            dump("sorry,$funcname 不可以使用");
            //str_get_html($str)
            //str2arr($str, ')
        }
    }
    
    public function guidop(){
        $guid= GuidHelper::newGuid();
        dump($guid);
        $guidWithoutHyphen= GuidHelper::cleanHyphen($guid);
        dump($guidWithoutHyphen);
        $guidWithHyphen= GuidHelper::addonHyphen($guidWithoutHyphen);
        dump($guidWithHyphen);
    }
}

class MyEnum extends Enum{
    const HI = "Hi";
    
    const BY = "By";
    
    const NUMBER = 1;
    
    const __default = self::BY;
}

?>