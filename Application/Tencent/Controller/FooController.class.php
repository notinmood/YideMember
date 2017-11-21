<?php
namespace Tencent\Controller;

use Tencent\Model\Foo1;
use Tencent\Model\Foo2;
use Think\Controller;

class FooController extends Fb2Controller
{

//    public function __construct()
//    {
//        echo 'in Foo';
//    }

    public function _initialize()
    {
        echo 'iiiiiiiii';
    }

    public function a()
    {
        echo 'aaaaaaaa';
    }

    public function b(){
        self::a();
        echo 'bbbbbbbb';
    }

    public function index()
    {
        if (1 == 2) {
            $foo = new Foo1();
        } else {
            $foo = new Foo2();
        }
        $this->show($foo->getname());
    }
}

?>