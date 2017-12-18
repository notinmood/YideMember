<?php

namespace Tencent\Controller;

use Tencent\Model\CharGame;
use Tencent\Model\Foo1;
use Tencent\Model\Foo2;
use Vendor\Hiland\Utils\Data\StringHelper;

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
        $result = CharGame::generateChar4User($userid, $randChar);
        dump($randChar . $result);
    }

    public function chartest($userid = 100)
    {
        dump(CharGame::getCurrentGameChars4User($userid));
    }

    public function tbs($tableName = '')
    {
        if ($tableName) {
            $m = M();
            $sql = "show columns from $tableName;";
            $columns = $m->query($sql);


            $tableNameWithoutPrefix = StringHelper::getSeperatorAfterString($tableName, "_");
            //dump($columns);
//            dump($tableName);
//            dump($tableNameWithoutPrefix);

            $className = StringHelper::upperStringFirstChar($tableNameWithoutPrefix) . "Struct";
            $output = "class $className" . StringHelper::getNewLineSymbol();
            $output .= "{" . StringHelper::getNewLineSymbol();
            $output .= "\tconst TN= '$tableNameWithoutPrefix';" . StringHelper::getNewLineSymbol() . StringHelper::getNewLineSymbol();
            foreach ($columns as $column) {
                $fieldName = $column['Field'];
                $upperFieldName = StringHelper::upper($fieldName);
                $output .= "\tconst $upperFieldName = '$fieldName';" . StringHelper::getNewLineSymbol();
            }
            $output .= "}";
            dump($output);
        } else {
            dump("请通过参数tableName指定表的名称");
        }

//        $dbName= C('DB_NAME');
//        $sql= "SELECT table_name FROM information_schema.tables WHERE table_schema = '$dbName' and table_type='BASE TABLE' ORDER BY table_name DESC;";
    }
}

?>