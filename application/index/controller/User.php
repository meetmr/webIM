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
use think\facade\Session;
use app\index\model\UserInfo as UserModel;

class User extends Controller
{
    public function index(){
        if (session('user')){
            return $this->redirect(url('/index'));
        }
        return $this->fetch('login');
    }

    public function login(){
        $return = [
            'msg'   =>'',
            'state' => 'error',
            'data'  =>''
        ];
        // 如果是Ajax请求
        if(Request::isAjax()){
            // 获取数据
            $data['username'] = Request::post('name');
            $data['pwd'] = Request::post('pwd');
            $data = databaseFilt($data);
            if (empty($data) || empty($data['pwd'])) {
                $return['msg'] = '账号或则是密码错误';
                return json($return);
            }

            if(!UserModel::where(['username'=>$data['username']])->find()){
                $return['msg'] = '用户名没有注册';
                return json($return);
            }

            $user = UserModel::where(['username'=>$data['username'],'password'=>$data['pwd']])->find();
            if (empty($user)){
                $return['msg'] = '账号密码错误';
                return json($return);
            }

            // 更新登陆状态
            UserModel::where('id', $user['id'])
                ->update([
                    'status'  => '1',
                    'up_time' => time()
                ]);

            // 存入Session
            session('user',$user);  // 写入session
            $return['msg'] = '登陆成功';
            $return['state'] = 'success';
            return json($return);
        }
    }
}