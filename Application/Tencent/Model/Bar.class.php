<?php
namespace Tencent\Model;

class Bar
{
    private $name='';
    
    public function __construct($name){
        $this->name= $name;
    }
    
    public function getname($prefix){
        return $prefix.$this->name;
    }
}

?>