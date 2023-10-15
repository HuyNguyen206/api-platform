<?php
namespace Ben\Ancestor;
declare(strict_types=1);
class Human {
    public $age = 100;
    public function eat(self &$human)
    {
        $human->age = 20;
    }
}

$a = new Human();
$b = new Human();
var_dump($b);
$a->eat($b);
var_dump($b);