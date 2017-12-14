<?php

namespace Tencent\Controller;

use Tencent\Model\CharGame;
use Tencent\Model\Foo1;
use Tencent\Model\Foo2;

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

    public function b()
    {
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

    public function randomchar($userid = 100)
    {
        $randChar = CharGame::getRandGameChar();
        //dump($randChar);
        $result = CharGame::generateChar($userid, $randChar);
        dump($randChar . $result);
    }

    public function chartest($userid = 100)
    {
        dump(CharGame::getCurrentChars($userid));
    }
}

?>