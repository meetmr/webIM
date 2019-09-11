<?php
namespace app\index\controller;
use app\index\controller\Base;
use app\index\model\GroupsToUser;
use app\index\model\UserInfo as UserModel;
use app\index\model\Grouping as GroupingModel;
use app\index\model\Relation as RelationModel;
use app\index\model\Groups as GroupsModel;
use app\index\model\Message as MessageModel;
use app\index\model\GroupsToUser as GroupsToUserModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\facade\Request;
use think\Request as RequestClass;
use think\response\Json;

class Index extends Base
{
    protected $beforeActionList = [
        'isLogin',
    ];

    public function index(){
        $GroupsToUserListIDArray = $this->getjoinGroup(session('user.id'));
        $this->assign([
            'groups'  => implode(',',$GroupsToUserListIDArray),
        ]);
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
                'group'=>$this->getGroupsToUserList(session('user.id'))
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
     * 获取群组
     * @param int $user_id
     * @return array
     */

    public function getjoinGroup($uid){
        $GroupsToUserList = $this->getGroupsToUserList($uid);
        if (empty($GroupsToUserList)){
            return [];

        }
        $GroupsToUserListIDArray = [];
        foreach ($GroupsToUserList as $value){
            $GroupsToUserListIDArray[] = $value['id'];
        }
        return $GroupsToUserListIDArray;
    }

    /**
     * 获取群组下的成员
     * @param
     * @return array $friend
     */

    public function getGroupUser(){
        if (Request::isAjax()){
            $return = [
                'code' => 0,
                'msg'  => '',
                'data'  => [],

            ];
            $id = Request::get('id');
            $GroupUserList = GroupsToUserModel::where(['ug_groupID'=>$id])
                ->order('ug_ime','desc')
                ->order('id','desc')
                ->select();

            if(empty($GroupUserList)){
                return  json($return);

            }

            $GroupUserListArray = [];
            foreach ($GroupUserList as $value){
                $UserNameInfo = $this->getUserNameInfo($value['ug_userID']);
                $GroupUserListArray['list'][] = [
                    'username' => $UserNameInfo['name'],
                    'id'       => $value['ug_userID'],
                    'avatar' => $UserNameInfo['avatar'],
                    'sign' => $UserNameInfo['sign'],
                ];
            }
            $return['data'] = $GroupUserListArray;
            return json($return);
        }
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
     * 获取我加入的群
     * @param int $u_id
     * @return array $friend
     */
    public function getGroupsToUserList($u_id){
        if (empty($u_id) || !intval($u_id)){
            $this->return_msg('10004','error','参数错误',[]);
        }

        $GroupsUserList = GroupsToUserModel::where(['ug_userID'=>$u_id])
            ->order('ug_ime','desc')
            ->order('id','desc')
            ->field('ug_groupID')
            ->select();

        if (empty($GroupsUserList)){
            return  [];
        }

        $groupsIdAraay = [];
        foreach ($GroupsUserList as $value){
            $groupsIdAraay[] = $value['ug_groupID'];
        }

        $groupsList = GroupsModel::where(['id'=>$groupsIdAraay])
            ->order('time','desc')
            ->order('id','desc')
            ->select();

        if (empty($groupsList)){
            return [];
        }

        $groupsListInfo = [];
        foreach ($groupsList as $value){
            $groupsListInfo[] = [
                'groupname' => $value['ug_name'],
                'id' => $value['id'],
                'avatar' => $value['ug_iCon'],
            ];
        }
        return  $groupsListInfo;
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

        $User = UserModel::where(['id'=>$relationIdArray])
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


    /**
     * 用户信息
     * @param int $user_id
     * @return string
     */
    public function getUserNameInfo($user_id){
        if (empty($user_id) || !is_numeric($user_id)){
            return 'offline';
        }
        $userInfo = UserModel::where(['id'=>$user_id])
            ->field('name,sign,avatar')
            ->find();
        return $userInfo;
    }

    /**
     * 上传图片接口
     * @return array
     */
    public function uploadImg(){
        $return = [
            'code' => 1,
            'msg' => '',
            'data' => [],
        ];
        $file = Request::file('file');
        $info = $file->move( '../public/static/uploads');
        if($info){
            // 成功上传后 获取上传信息
            $finename= $info->getSaveName();
            $return['code'] = 0;
            $return['data'] = [
                'src' => FILE_DOMAIN.$finename
            ];
            return json($return);
        }else{
            $return['msg'] = '上传出错';
            return json($return);
        }
    }

    /**
     * 消息入库
     * @return array
     */
    public function messageSave(){
        $return = [
            'code' =>1,
            'msg' =>'',
            'cid' =>'',
        ];
        if (Request::isAjax()){
            $data = Request::post('data');

            if (empty($data)){
                $return['msg'] = "消息参数错误";
                return json($return);
            }
            // 如果是单聊
            if ($data['to']['type'] == 'friend') {
                $type = 'friend';
                $code = getCode($data['to']['id']);
            }elseif ($data['to']['type'] == 'group'){
                $code = $data['to']['id'];
                $type = 'group';
            }else{
                $return['msg'] = "消息参数错误";
                return json($return);
            }

            $message = [
                'code' => $code,
                'fs_userid' => $data['mine']['id'],
                'js_userid' => $data['to']['id'],
                'message'   => $data['mine']['content'],
                'state'     => 0,
                'sendtime'  => time(),
                'type'      => $type
            ];
            $meg = MessageModel::create($message);
            if (empty($meg)){
                $return['msg'] = "消息保存失败";
                return json($return);
            }
            $return['code'] = 0;
            $return['msg']  = '保存成功';
            $return['cid'] = $meg->id;
            return json($return);
        }
        return json(['code'=>1,'msg'=>'参数错误']);
    }

    /**
     * 获取聊天消息
     * @return array
     */
    public function getChatRecord(){
        if (Request::isAjax()){
            $data = Request::post('data');
            if ($data['type'] == 'friend'){
                $res = $this->getChatRecordFriend($data);
            }
            dd($data);
        }
    }

    /**
     * 获取聊天消息 单聊
     * @param array $data
     * @return array
     */

    public function getChatRecordFriend($data){

    }
}
