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

    public function return_msg($code, $state, $msg = '', $data = []) {

        /*********** 组合数据  ***********/
        $return_data['code'] = $code;
        $return_data['msg']  = $msg;
        $return_data['data'] = $data;
        $return_data['state'] = $state;

        /*********** 返回信息并终止脚本  ***********/
        echo json_encode($return_data);die;
    }

}