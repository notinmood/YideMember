<?php
namespace Admin\Controller;

use Common\Common\ConfigHelper;
use Common\Model\UserinfoModel;
use Vendor\Hiland\Utils\Data\DBSetHelper;
use Vendor\Hiland\Utils\DataModel\ModelMate;

class TencentController extends HilandController
{
    // TODO: 判断站点应用类型
    /**
     * 微信注册用户管理首页
     *
     * @author
     *
     */
    public function index()
    {
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $nickname = I('nickname');
        // $map['status'] = array('egt',0);
        if (is_numeric($nickname)) {
            $where['uid|displayname|weixinname'] = array(
                intval($nickname),
                array(
                    'like',
                    '%' . $nickname . '%'
                ),
                '_multi' => true
            );
        } else {
            $where['displayname|weixinname'] = array(
                'like',
                '%' . (string)$nickname . '%'
            );
        }

        $specialitem = I('specialitem');
        switch ($specialitem) {
            case 'ismaster':
                $where['ismaster'] = 1;
                break;
            case 'ismerchant':
                $where['ismerchant'] = 1;
                break;
            default:
                break;
        }

        $list = $this->lists('userinfo', $where);

        $friendlymaps = array(
            'usersex' => array(
                1 => '男',
                2 => '女'
            ),
            'ismaster' => array(
                0 => '否',
                1 => '是'
            ),
            'vipid' => array(
                0 => '-'
            )
        );
        DBSetHelper::friendlyDisplay($list, $friendlymaps);
        $this->assign('_list', $list);
        $this->assign('meta_title', '微信注册用户信息');

        $this->display($this->getTMPLName('index'));
    }

    /**
     * 根据站点类型获取显示信息的模板名称
     *
     * @param string $defaultTMPLName
     * @return string
     */
    private function getTMPLName($defaultTMPLName)
    {
        $result = $defaultTMPLName;
        $siteusingtype = C('SITEUSINGTYPE');
        switch (C('SITEUSINGTYPE')) {
            case 1:
            case 2:
                $result .= '-' . $siteusingtype;
                break;
            default:
                break;
        }

        return $result;
    }

    /**
     * 设置庄家
     */
    public function masterSetOn()
    {
        $ids = $this->getUrlParaValue('id', false);

        if (!empty($ids)) {
            $idarray = explode(',', $ids);
            foreach ($idarray as $keyid) {
                UserinfoModel::setVIPID($keyid);
            }
        }

        $changingdata = array(
            'ismaster' => 1
        );
        $this->setDatabaseValue('Userinfo', $changingdata, 'userid');
    }

    /**
     * 取消庄家
     */
    public function masterSetOff()
    {
        $changingdata = array(
            'ismaster' => 0
        );
        $this->setDatabaseValue('Userinfo', $changingdata, 'userid');
    }

    /**
     * 设置商户
     */
    public function merchantSetOn()
    {
        $ids = $this->getUrlParaValue('id', false);

        if (!empty($ids)) {
            $idarray = explode(',', $ids);
            foreach ($idarray as $keyid) {
                UserinfoModel::setVIPID($keyid);
            }
        }

        $changingdata = array(
            'ismerchant' => 1
        );
        $this->setDatabaseValue('Userinfo', $changingdata, 'userid');
    }

    /**
     * 取消商户
     */
    public function merchantSetOff()
    {
        $changingdata = array(
            'ismerchant' => 0
        );
        $this->setDatabaseValue('Userinfo', $changingdata, 'userid');
    }

    /**
     * 商户信息调整
     */
    public function merchant()
    {
        $mateMerchant = new ModelMate('merchant');
        if (IS_GET) {
            $id = I('get.merchantid');

            $data = $mateMerchant->get($id, 'merchantid');
            $this->assign('data', $data);

            $this->assign('meta_title', '商户基本信息');
            $this->display();
        } else {
            // 1、修改user表中username
            // 2、修改adddon表中的信息

            $dataMerchant = $mateMerchant->model->create();
            $result = $mateMerchant->interact($dataMerchant, 'merchantid');


            if (!$result) {
                $this->error($mateMerchant->model->getError());
            } else {
                $mateUser = new ModelMate('userinfo');
                $mateUser->setValue($dataMerchant['merchantid'], 'displayname', $dataMerchant['merchantname'], 'userid');
                $this->success('数据交互成功！', Cookie('__forward__'));
            }
        }
    }

    public function roleList($userid)
    {
        $model = D('userroles'); // new UserrolesModel();//'userroles';
        $where['userid'] = $userid;
        $list = $this->lists($model, $where);

        $friendlymaps = array(
            'outdegree' => ConfigHelper::get1DArray('ROLE_OUT_DEGREES', 'value', 'display')
        );
        DBSetHelper::friendlyDisplay($list, $friendlymaps);
        $this->assign('_list', $list);
        $this->assign('meta_title', '微信用户角色信息');

        $this->display();
    }

    public function merchantServiceList($merchantid)
    {
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $model = D('merchantservice');
        $where['merchantid'] = $merchantid;
        $list = $this->lists($model, $where);

        $friendlymaps = array(
            'isusable' => C('HILAND_SYSTEM_USABLESTATUS')
        );
        DBSetHelper::friendlyDisplay($list, $friendlymaps);
        $this->assign('_list', $list);
        $this->assign('meta_title', '商户服务列表');

        $this->display();
    }

    public function merchantService($merchantid = 0)
    {
        $id = I('id');
        if (empty($merchantid) && empty($id)) {
            $this->error('对不起，请先选择指定商户信息，或者选择要修改的服务信息！');
        }

        if (IS_POST) {
            $mate = new ModelMate('merchantservice');
            $result = $mate->interact();

            if ($result) {
                $this->success('更新成功', Cookie('__forward__'));
            } else {
                $this->error($mate->model->getError());
            }
        } else {
            if (!empty($id)) {
                $data = M('merchantservice')->field(true)->find($id);
                $this->assign('data', $data);
            }

            $this->assign('meta_title', '商户服务信息');
            $this->display();
        }
    }

    public function merchantServiceOn()
    {
        // $settings= C('HILAND_SYSTEM_USABLESTATUS');
        $changingdata = array(
            'isusable' => 1
        ); // 请参考配置C('HILAND_SYSTEM_USABLESTATUS');

        $this->setDatabaseValue('merchantservice', $changingdata, 'id');
    }

    public function merchantServiceOff()
    {
        // $settings= C('HILAND_SYSTEM_USABLESTATUS');
        $changingdata = array(
            'isusable' => 0
        ); // 请参考配置C('HILAND_SYSTEM_USABLESTATUS');

        $this->setDatabaseValue('merchantservice', $changingdata, 'id');
    }

    public function merchantServiceDelete()
    {
        $ids = $this->getUrlParaValue('id', true);

        if (!empty($ids)) {
            $idarray = explode(',', $ids);
            $map['id'] = array(
                'in',
                $idarray
            );

            $res = M('merchantservice')->where($map)->delete();

            if ($res) {
                $this->success('删除成功！');
            } else {
                $this->error('删除失败！');
            }
        }
    }

    /**
     *
     */
    public function systemUserMatch()
    {
        $userMate = new ModelMate('userinfo');

        if (IS_GET) {
            $userID = I('get.userid');
            $data = $userMate->getValue($userID, 'systemuserid', 'userid');
            $this->assign('data', $data);

            $this->assign('meta_title', '商户基本信息');
            $this->display();
        } else {
            $userID = $_POST['userid'];
            $systemUserID = $_POST['systemuserid'];
            //$this->success($userID);
            $result = $userMate->setValue($userID, 'systemuserid', $systemUserID, 'userid');

            if ($result) {
                $this->success('数据交互成功！', Cookie('__forward__'));
            } else {
                $this->error($userMate->model->getError());
            }
        }
    }
}

?>