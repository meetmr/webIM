<?php
namespace app\index\controller;
use app\index\controller\Base;
use app\index\model\UserInfo as UserModel;
use app\index\model\Grouping as GroupingModel;
use app\index\model\Relation as RelationModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\response\Json;

class Index extends Base
{
    protected $beforeActionList = [
        'isLogin',
    ];

    public function index(){
        return $this->fetch();
    }

    /**
     * 初始化主页
     * @return Json
     */
    public function iniHomepage(){
        // 初始化数据
       return json([
            'code'   =>'',
            'msg' => 'error',
            'data'  =>[
                'mine' => $this->getUserInfo(session('user.id')),
                'friend'=>$this->getRelation(session('user.id')),
                'group'=>[]
            ]
        ]);
    }

    /**
     * 获取用户信息
     * @param int $user_id
     * @return array
     */
    public function getUserInfo($user_id){
        // 验证参数
        if (empty($user_id) || !is_numeric($user_id)){
            $this->return_msg('10001','error','参数错误',[]);
        }
        $user = UserModel::get($user_id);
        if (empty($user)){
            $this->return_msg('10002','error','用户不存在',[]);
        }
        $userInfo = $user->toArray();
        return [
            'username' => $userInfo['name'],
            'id'       => $userInfo['id'],
            'status'   => $this->getUserStatus($userInfo['id']),
            'sign'     => $userInfo['sign'],
            'avatar'     => $userInfo['avatar'],
        ];
    }

    /**
     * 获取分组
     * @param int $user_id
     * @return array
     */
    public function getGroping($user_id){
        if (empty($user_id) || !is_numeric($user_id)){
            $this->return_msg('10001','error','参数错误',[]);
        }
        return $groping = GroupingModel::where(['uid'=>$user_id])
            ->order('id','asc')
            ->select();
    }

    /**
     * 获取好友
     * @param int $user_id
     * @return array $friend
     */
    public function getRelation($user_id){
        $friend  = [];
        $groping = $this->getGroping($user_id);
        foreach ($groping as $value){
            $friend[] = [
                'groupname' => $value['name'],
                'id' => $value['id'],
                'list' =>$this->getGropingRelation($value['id'],$user_id),
            ];
        }
        return $friend;
    }


    /**
     * 获取此分组下的好友
     * @param int $g_id
     * @param int $uid
     * @return array $UserArray
     */
    public function getGropingRelation($g_id,$uid){
        $relation = RelationModel::where(['g_id'=>$g_id, 'uid'=>$uid])
            ->field('friend_id')
            ->order('time','desc')
            ->select();

        $relationIdArray = [];
        foreach ($relation as $relationValue){
            $relationIdArray[] = $relationValue['friend_id'];
        }

        $User = UserModel::where(['id'=>$relationIdArray,])
            ->order('status','desc')
            ->select();

        $UserArray = [];
        foreach ($User as $item){
            $UserArray[] = [
                'username' => $item['name'],
                'id'       => $item['id'],
                'avatar'   => $item['avatar'],
                'sign'     => $item['sign'],
                'status'   => $this->getUserRelationStatus($item['id']),
            ];
        }
        return $UserArray;
    }

    /**
     * 获取用户登陆状态
     * @param int $user_id
     * @return string
     */
    public function getUserStatus($user_id){
        if (empty($user_id) || !is_numeric($user_id)){
            return 'hide';
        }
        $status = UserModel::where(['id'=>$user_id])
            ->value('status');
        return $status == 1 ? 'online' : 'hide';
    }

    /**
     * 获取用户登陆在线状态
     * @param int $user_id
     * @return string
     */
    public function getUserRelationStatus($user_id){
        if (empty($user_id) || !is_numeric($user_id)){
            return 'offline';
        }
        $status = UserModel::where(['id'=>$user_id])
            ->value('status');
        return $status == 1 ? 'online' : 'offline';
    }
}
