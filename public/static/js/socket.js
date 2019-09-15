var webIm = {
    init:function(){
    },
    webImonmessage:function (res) { // 接收到消息
        res = JSON.parse(res.data);
        console.log(res);
        if(res.emit === 'chatMessage'){
            if (res.data.fromid != uid){ // 判断是否是自己发的
                layimy.getMessage(res.data); //res.data即你发送消息传递的数据（阅读：监听发送的消息）
            }
        }
    },
    // 发送消息入库
    sendMessageSave:function (res) {
        // 转发给接收着
        // 消息入库
        $.ajax({
            url:messageSaveUrl,
            type:"post",
            data:{data:res},
            dataType:"json",
            success:function (data) {
                console.log(data);
                if (data.code != 0){
                    layer.msg(data.msg);
                    return false;
                }
                // 通知接收者
                socket.send(JSON.stringify({
                    type: 'chatMessage'//随便定义，用于在服务端区分消息类型
                    ,data: res
                    ,cid:data.cid
                }));
            }
        });
        console.log(res);
    },
    //  获取聊天页面
    getChatRecord:function (res) {
        localStorage.clear();

        im_$.ajax({
            url:getChatRecordUrl,
            type:"post",
            data:{data:res.data},
            dataType:"json",
            success:function (data) {
                if (data.code != 0){
                    layer.msg(data.msg);
                    return false;
                }
                for (let i = 0;i<data.data.length;i++){
                    console.log(data.data[i]);
                    layimy.getMessage(data.data[i]);
                }
                // // 通知接收者
                // socket.send(JSON.stringify({
                //     type: 'chatMessage'//随便定义，用于在服务端区分消息类型
                //     ,data: res
                //     ,cid:data.cid
                // }));
            }
        });

        // layimy.getMessage({
        //     username: "专家答疑" //消息来源用户名
        //     ,avatar: "http://tp1.sinaimg.cn/1571889140/180/40030060651/1" //消息来源用户头像
        //     ,id: uid//消息的来源ID（如果是私聊，则是用户id，如果是群聊，则是群组id）
        //     ,type: "friend" //聊天窗口来源类型，从发送消息传递的to里面获取
        //     ,content:'121212' //消息内容
        //     ,cid: 121 //消息id，可不传。除非你要对消息进行一些操作（如撤回）
        //     ,mine: false //是否我发送的消息，如果为true，则会显示在右方
        //     ,fromid:2 //消息的发送者id（比如群组中的某个消息发送者），可用于自动解决浏览器多窗口时的一些问题
        //     ,timestamp: 12156565656 * 1000 //服务端时间戳毫秒数。注意：如果你返回的是标准的 unix 时间戳，记得要 *1000
        // });
    },
};