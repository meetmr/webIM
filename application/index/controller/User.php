<?php
/**
 * 用户登陆
 * User: xiaowang
 * Date: 2019/9/4
 * Time: 11:10
 */

namespace app\index\controller;

use think\Controller;
use think\facade\Request;

class User extends Controller
{
    public function index(){
        return $this->fetch('login');
    }

    public function login(){
        $return = [
            'msg'   =>'',
            'state' => 'error',
            'data'  =>''
        ];
        if(Request::isAjax()){
            $data['name'] = Request::post('name');
            $data['pwd'] = Request::post('pwd');
            if (!empty($data) || $data['pwd']){
                $return['msg'] = '账号或则是密码错误';
                return json($return);
            }
        }
    }
}