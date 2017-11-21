<?php
/**
 * Created by PhpStorm.
 * User: devel
 * Date: 2016/3/4 0004
 * Time: 17:17
 */

namespace Tencent\Controller;


use Common\Common\ConfigHelper;
use Common\Model\UserinfoModel;
use Common\Model\UserrolesModel;
use Hiland\Model\ViewMate;
use Think\App;
use Think\Controller;
use Vendor\Hiland\Utils\Data\ArrayHelper;

class MyController extends Controller
{
    /**
     * @param $useropenid
     */
    public function index($useropenid)
    {
        $userData = UserinfoModel::getByOpenID($useropenid);

        $friendlymaps = array(
            'ismaster' => array(
                0 => '未开通',
                1 => '享有'
            ),
            'ismerchant' => array(
                0 => '未开通',
                1 => '享有'
            ),
            'vipid' => array(
                0 => '未开通'
            )
        );
        ArrayHelper::friendlyDisplayEntity($userData, $friendlymaps);
        $this->assign('openid', $useropenid);
        $this->assign('data', $userData);
        $this->display();
    }

    /**
     * @param $useropenid
     */
    public function roleservice($useropenid)
    {
        $userData = UserinfoModel::getByOpenID($useropenid);
        $userID = $userData['userid'];

//        dump('openid:'.$useropenid);
//        dump($userData);
//        dump('userid:'.$userID);

//        $outDegrees= array(
//            0,
//            1,
//            10
//        );//ROLE_OUT_DEGREES

        $whereAddon['outdegree']= array('NEQ',-1);//ROLE_OUT_DEGREES -1表示未入局的角色，此处不显示

        $roles = UserrolesModel::getRoles($userID,0,null,$whereAddon);

        $friendlymaps = array(
            'moneyamountactived' => array(
                0 => '未激活',
                1 => '已激活'
            ),
            'outdegree' => ConfigHelper::get1DArray('ROLE_OUT_DEGREES', 'value', 'display'),
            'vipid' => array(
                0 => '未开通'
            )
        );
        ArrayHelper::friendlyDisplayDbSet($roles, $friendlymaps);
        $this->assign('data', $roles);
        $this->display();
    }

    public function childrenroles($roleid)
    {
        //$condition['parentid']= $roleid;
        //$roles= UserrolesModel::getRoles(0,0,null,$condition,'roleid desc');
        $modelInfos = array(
            array('userroles', 'A', 'A.*'),
            array('userroles', 'B', ''),
            array('userinfo', 'C', 'C.displayname,C.headurl')
        );

        $onClauses = array(
            'A.parentid= B.roleid',
            'A.userid=C.userid'
        );


        $viewMate = new ViewMate($modelInfos, $onClauses);
        $where = "A.parentid=$roleid";
        $addon = 'ORDER BY A.roleid desc';
        $roles = $viewMate->select($where, $addon);
        $sql = $viewMate->showSql($where, $addon);
        //CommonHelper::log('viewsql', $sql);

        $this->assign('data', $roles);
        $this->display();
    }

    public function advancedfuncs($userid)
    {
        $userData = UserinfoModel::getByKey($userid);
        $friendlymaps = array(
            'ismaster' => array(
                0 => '未开通',
                1 => '享有'
            ),
            'ismerchant' => array(
                0 => '未开通',
                1 => '享有'
            ),
            'vipid' => array(
                0 => '未开通'
            )
        );
        ArrayHelper::friendlyDisplayEntity($userData, $friendlymaps);

        $this->assign('data', $userData);
        $this->display();
    }

}