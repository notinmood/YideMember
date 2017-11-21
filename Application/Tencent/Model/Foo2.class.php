<?php
namespace Tencent\Model;

class Foo2 extends Foo
{
    public function getname(){
        return 'foo2';
    }
    
    public function echos($info){
        return '收到的信息为:'.$info;
    }
    
    public static function display($info){
        return '[静态]收到的信息为:'.$info;
    }
}

?>