<?php
/**
 * Created by PhpStorm.
 * User: xiaowang
 * Date: 2019/9/4
 * Time: 14:29
 */

namespace app\index\controller;
use think\Controller;

class Base extends Controller
{
    public function isLogin (){
        if(!session('user')){
            return $this->redirect(url('/login'));
        }
    }

}