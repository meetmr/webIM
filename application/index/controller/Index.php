<?php
namespace app\index\controller;
use app\index\controller\Base;
use app\index\model\UserInfo as UserModel;

class Index extends Base
{
    protected $beforeActionList = [
        'isLogin',
    ];

    public function index()
    {
        return $this->fetch();
    }

    public function hello($name = 'ThinkPHP5'){
        return 'hello,' . $name;
    }

    // 初始化主页
    public function iniHomepage(){
        // 初始化数据
        $userid = session('user.id');
        $userInfo = $this->getUserInfo($userid);
        // 登陆用户信息
        $mine = [
            'username' => $userInfo['name'],
            'id'       => $userInfo['id'],
            'status'   => $this->getUserStatus($userInfo['id']),
            'sign'     => $userInfo['sign'],
            'avatar'     => $userInfo['avatar'],
        ];
        $return = [
            'code'   =>'',
            'msg' => 'error',
            'data'  =>[
                'mine' => $mine,
                'friend'=>[],
                'group'=>[]
            ]
        ];
        return json($return);
    }

    public function getUserInfo($user_id){
        // 验证参数
        if (empty($user_id) || !is_numeric($user_id)){
            $this->return_msg('10001','error','参数错误',[]);
        }
        $user = UserModel::get($user_id);
        if (empty($user)){
            $this->return_msg('10002','error','用户不存在',[]);
        }
        return $user->toArray();
    }

    public function getUserStatus($user_id){
        if (empty($user_id) || !is_numeric($user_id)){
            return 'hide';
        }
        $status = UserModel::where(['id'=>$user_id])->value('status');
        return $status == 1 ? 'online' : 'hide';
    }
}
