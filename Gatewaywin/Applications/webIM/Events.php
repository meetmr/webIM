<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id){
    }
    
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $message)
   {
       $data = json_decode($message,true);
       switch ($data['type']){
           case 'reg':
               if (!Gateway::isUidOnline($data['data']['uid'])){
                   // 进行登录绑定
                   Gateway::bindUid($client_id,$data['data']['uid']);
                   echo "用户 ".$data['data']['uid'] ."上线\n";
                   // 绑定群组
                   $gid = $data['data']['gid'];
                   if (!empty($gid)){
                       $groups = explode(',',$gid);
                       if (!empty($groups) && is_array($groups)){
                           foreach ($groups as $group){
                               $ClientSessionsByGroup = Gateway::getClientSessionsByGroup($group);
                               foreach ($ClientSessionsByGroup as $item){
                                   if ($item == $client_id){
                                       continue;
                                   }
                               }
                               $return  = [
                                   'type' => 'msg',
                                   'data' => "用户：".$data['data']['uid']."上线:$group"
                               ];
                               echo "用户：".$data['data']['uid']."绑定群:$group\n";

                               Gateway::joinGroup($client_id,$group);
                               Gateway::sendToGroup($group,json_encode($return));
                           }
                       }
                   }

                   $return = [
                       'type' => 'msg',
                       'data' => "用户：".$data['data']['uid']."上线  client_id:$client_id"
                   ];

                   Gateway::sendToAll(json_encode($return));
               }else{
                   $return = [
                       'type' => 'msg',
                       'data' => "用户：".$data['data']['uid']."s"
                   ];

                   Gateway::sendToAll(json_encode($return));
               }

               break;
           case 'chatMessage':
               // 判断是否在线
               if (!Gateway::isUidOnline($data['data']['to']['id'])){
                   // 不在聊天线数据直接入库
               }
               // 在线的话转发给接收者

               // 获取发送者信息
                $mine = $data['data']['mine'];
                // 接收者信息
                $to = $data['data']['to'];

                // 私聊
                if ($to['type'] == 'friend'){
                    $return = [
                        'emit' => 'chatMessage',
                        'data' => [
                            'username' =>  $mine['username'],
                            'avatar'   =>  $mine['avatar'],
                            'id'       =>  $mine['id'],
                            'type'     =>  $to['type'],
                            'content'  =>  $mine['content'],
                            'cid'      =>  intval($data['cid']) ,
                            'mine'     =>  false,
                            'fromid'   =>  $mine['id'],
                            'timestamp'=>  time() * 1000,
                        ]
                    ];
                    Gateway::sendToUid($to['id'], json_encode($return));
                }else if ($to['type'] == 'group'){ // 群聊
                    $return = [
                        'emit' => 'chatMessage',
                        'data' => [
                            'username' =>  $mine['username'],
                            'avatar'   =>  $mine['avatar'],
                            'id'       =>  $to['id'],
                            'type'     =>  $to['type'],
                            'content'  =>  $mine['content'],
                            'cid'      =>  time(),
                            'mine'     =>  false,
                            'fromid'   =>  $mine['id'],
                            'timestamp'=>  time() * 1000,
                        ]
                    ];
                    Gateway::sendToGroup($to['id'],json_encode($return));

                }

               break;
           default:
       }
        // 向所有人发送 
//        Gateway::sendToAll("wid : $client_id said $message  UID：".$data['data']['uid']."\r\n");
   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id)
   {
       // 向所有人发送 
//       GateWay::sendToAll("$client_id logout\r\n");
   }
}
