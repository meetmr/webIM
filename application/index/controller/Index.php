<?php
namespace app\index\controller;
use app\index\controller\Base;

class Index extends Base
{
    protected $beforeActionList = [
        'isLogin',
    ];

    public function index()
    {
        return $this->fetch();
    }

    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }
}
