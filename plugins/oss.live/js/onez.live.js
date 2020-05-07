//消息
onez.im.$on('message',function(res){
  if(!res.action){
    return;
  }
  if(res.action=='im'){
    if(res.type=='request'){
      /*
      for(var p2pId in onez.p2pList){
        onez.im.$call('sendmsg',{
          to:'user',
          uids:res.fromUserId,
          data:{
            'action':'im',
            'type':'busy',
            'fromUserId':onez.im.option.userid
          }
        });
        return;
      }
      */
      $('.comp-im [data-uid] .btn-cancel').trigger('click');
      setTimeout(function(){
        onez.onEvent({
          token:'imWin',
          type:res.callType,
          uid:res.fromUserId,
          status:'ask'
        });
      },500);
    }else if(res.type=='agree'){
      var imVue=$('.comp-im [data-uid="'+res.fromUserId+'"]').vue();
      if(imVue){
        Vue.set(imVue.data,'status','ing');
      }
      onez.autoP2P().create(res.fromUserId,res.callType);
    }else if(res.type=='busy'){
      var imVue=$('.comp-im [data-uid="'+res.fromUserId+'"]').vue();
      if(imVue){
        Vue.set(imVue.data,'status','busy');
      }
    }else if(res.type=='cancel'){
      var imVue=$('.comp-im [data-uid="'+res.fromUserId+'"]').vue();
      if(imVue){
        Vue.set(imVue.data,'status','cancel');
      }
      onez.closeP2P(res.fromUserId);
    }else if(res.type=='disagree'){
      var imVue=$('.comp-im [data-uid="'+res.fromUserId+'"]').vue();
      if(imVue){
        Vue.set(imVue.data,'status','disagree');
      }
    }else if(res.type=='p2p.request'){
      onez.autoP2P(res.p2pId).offer(res.fromUserId,res.sdp,res.callType);
    }else if(res.type=='p2p.answer'){
      onez.autoP2P(res.p2pId).answer(res.answer);
    }else if(res.type=='p2p.p2p.candidate'){
      onez.autoP2P(res.p2pId).addIceCandidate(res.candidate);
    }
  }
  console.log('message',res);
});
onez.p2pList={};
onez.autoP2P=function(p2pId){
  var key=p2pId||(Math.random()+'');
  if(typeof onez.p2pList[key]=='undefined'){
    onez.p2pList[key]=new onez.P2P(key);
  }
  return onez.p2pList[key];
};
onez.closeP2P=function(uid){
  if(!uid){
    return;
  }
  for(var p2pId in onez.p2pList){
    if(onez.p2pList[p2pId].uid==uid){
      onez.p2pList[p2pId].close();
    }
  }
};
//加入
onez.im.$on('join',function(res){
  onez.addUser(res.userid);
  onez.updateUsers();
});
onez.User=function(uid){
  var that=this;
  var _info={userid:uid,nickname:uid};
  this.online=true;
  this.info=function(){
    _info.isMe=(uid==onez.im.option.userid);
    _info.status='';
    if(_info.isMe){
      _info.status+='<span class="me">自己</span>';
    }else{
      _info.status+='<span class="btn btn-xs btn-info" data-token="imWin" data-type="audio" data-uid="'+uid+'">语音</span> ';
      _info.status+='<span class="btn btn-xs btn-info" data-token="imWin" data-type="video" data-uid="'+uid+'">视频</span> ';
    }
    _info.status+='&nbsp;';
    return _info;
  };
  //离开
  this.remove=function(){
    //$('.comp-im [data-uid="'+uid+'"] .btn-cancel').trigger('click');
    var imVue=$('.comp-im [data-uid="'+uid+'"]').vue();
    if(imVue){
      Vue.set(imVue.data,'status','cancel');
    }
  };
};
//离开
onez.im.$on('leave',function(res){
  onez.removeUser(res.userid);
  onez.updateUsers();
});
onez.users={};
//用户进入
onez.addUser=function(uid){
  if(!uid){
    return;
  }
  if(onez.users[uid]){
    onez.users[uid].online=true;
  }else{
    onez.users[uid]=new onez.User(uid);
  }
};
//用户离开
onez.removeUser=function(uid){
  if(!uid){
    return;
  }
  if(onez.users[uid]){
    onez.users[uid].remove();
    delete onez.users[uid];
  }
};
//刷新用户列表
onez.updateUsers=function(){
  var users=[];
  for(var uid in onez.users){
    if(onez.users[uid].online){
      users.push(onez.users[uid].info());
    }
  }
  var userVue=$('.user-box').vue();
  if(userVue){
    Vue.set(userVue.data,'users',users);
  }
};
//获取用户列表
onez.getUserList=async function(){
  for(var uid in onez.users){
    onez.users[uid].online=false;
  }
  var r=await onez.im.$call('userlist');
  if(r[0]){
    var uids=r[1].split(',');
    for(var i=0;i<uids.length;i++){
      onez.addUser(uids[i]);
    }
  }
  for(var uid in onez.users){
    if(!onez.users[uid].online){
      onez.removeUser(uid);
    }
  }
  onez.updateUsers();
};
//连接状态
onez.im.$on('status',async function(res){
  console.log('status',res);
  if(res.status=='open'){//每次连接成功加入场景
    var r=await onez.im.$call('joinScene',{
      sceneId:'default'
    });
    //获取在线用户列表
    onez.getUserList();
  }
});
//初始化通话窗口
onez.imInit=function(data){
  if(data.status=='request'){//给对方发送请求
    onez.im.$call('sendmsg',{
      to:'user',
      uids:data.callUid,
      data:{
        'action':'im',
        'type':'request',
        'fromUserId':onez.im.option.userid,
        'callType':data.callType
      }
    });
  }
  return data.status;
};

//建立p2p通道 
onez.P2P=function(p2pId){
  var that=this;
  that.id=p2pId;
  that.uid='';
  that.method='called';
  that.peer=async function(){
    var iceServers=[];
    iceServers.push({urls:['turn:turn.onez.cn:3478'], username:'onez', credential:'a123456'});
    //iceServers.push({urls:['stun:stun.l.google.com:19302'], username:'', credential:''});
    var pc=new RTCPeerConnection({
      iceServers:iceServers,
      iceTransportPolicy:'all',
      iceCandidatePoolSize:'0',
      RtpDataChannels:true
    });
    pc.addEventListener('track', function(evt){
      console.log('track',that.method,evt);
      if(that.method=='called'){
        //var remoteVideo=$('.comp-im [data-uid="'+that.uid+'"] .remoteVideo').get(0);
        //remoteVideo.srcObject = evt.streams[0];
      }
    });
    pc.addEventListener('icecandidate', function(e){
      that.onIceCandidate(pc,e);
    });
    pc.addEventListener('iceconnectionstatechange', function(e){
      that.onIceStateChange(pc,e);
    });
    pc.addEventListener('datachannel', function(e){
      that.receiveChannelCallback(pc,e);
    });
    pc.addEventListener('connecting', function(e){
      console.log('connecting',e);
    });
    pc.addEventListener('open', function(e){
      console.log('open',e);
    });
    pc.addEventListener('addstream', function(e){
      console.log('addstream',that.method,e.stream);
      var remoteVideo=$('.comp-im [data-uid="'+that.uid+'"] .remoteVideo').get(0);
      remoteVideo.srcObject = e.stream;
    });
    pc.addEventListener('removestream', function(e){
      console.log('removestream',e);
    });
    return pc;
  };
  that.channel=function(pc){
    var channel=pc.createDataChannel('sendDataChannel', {reliable: false});
    //channel.binaryType='blob';
    channel.addEventListener('open', function(e){
      option.ready(that,option);
    });
    channel.addEventListener('close', function(e){
      //that.init();
    });
    channel.addEventListener('message', function(e){
      that.parseData(e.data);
    });
    return channel;
  };
  that.stream=function(){
    return new Promise((resolve, reject) => {
      try{
        if(document.location.protocol=='http:'){
          resolve(null);
          return;
        }
        var opt={};
        if(that.callType=='audio'){
          opt={
            audio: true,
            video: true,
          };
        }else{
          opt={
            audio: true,
            video: false,
          };
        }
        navigator.mediaDevices.getUserMedia(opt).then(resolve).then(null);
      }catch(e){
        console.log('error',e);
        resolve(null);
      }
    });
  };
  that.delay=function(ms){
    return new Promise((resolve, reject) => {
      setTimeout(resolve,ms);
    });
  };
  that.source=function(){
    return new Promise((resolve, reject) => {
      that.stream().then(function(stream){
        if(stream!=null){
          resolve([1,stream]);
        }else if(that.callType=='video'){
          layer.confirm('读取摄像头失败，请选择替代方案~', {
            btn: ['模拟视频', '无视频', '停止通话']
            ,btn3: function(index, layero){
              layer.close(index);
              resolve([-1]);
            }
          }, function(index, layero){
            layer.close(index);
            var video=$('.comp-im [data-uid="'+that.uid+'"] .localVideo').attr('loop','true').attr('src',onez.im.option.siteurl+'/demo/01.webm');
            setTimeout(function(){
              if(video.get(0).captureStream){
                resolve([2,video.get(0).captureStream()]);
              }else if(video.get(0).mozCaptureStream){
                resolve([2,video.get(0).mozCaptureStream()]);
              }else{
                resolve([0]);
              }
            },1000);
          }, function(index){
            layer.close(index);
            resolve([0]);
          });
        }else{
          layer.confirm('读取麦克风失败，请选择替代方案~', {
            btn: ['模拟音频', '无音频', '停止通话']
            ,btn3: function(index, layero){
              layer.close(index);
              resolve([-1]);
            }
          }, function(index, layero){
            layer.close(index);
            var video=$('.comp-im [data-uid="'+that.uid+'"] .localVideo').attr('loop','true').attr('src',onez.im.option.siteurl+'/demo/01.webm');
            setTimeout(function(){
              resolve([2,video.get(0).captureStream(25)]);
            },1000);
          }, function(index){
            layer.close(index);
            resolve([0]);
          });
        }
      });
    });
  };
  that.offer=async function(uid,sdp,callType){
    that.callType=callType;
    that.uid=uid;
    //创建本地offer
    if(!that.pc){
      that.pc=await that.peer();
    }
    console.log('### RemoteDescription',that.method,sdp);
    await that.pc.setRemoteDescription(new RTCSessionDescription(sdp));
    const answer = await that.pc.createAnswer();
    that.pc.setLocalDescription(answer);
    console.log('### LocalDescription',that.method,answer);
    onez.im.$call('sendmsg',{
      to:'user',
      uids:that.uid,
      data:{
        action:'im',
        type:'p2p.answer',
        p2pId:p2pId.replace('.reply',''),
        answer:answer,
        fromUserId:onez.im.option.userid
      }
    });
    for(var _p2pId in onez.p2pList){
      if(onez.p2pList[_p2pId].uid==that.uid && onez.p2pList[_p2pId].method=='calling'){
        return;
      }
    }
  };
  that.answer=async function(answer){
    await that.pc.setRemoteDescription(new RTCSessionDescription(answer));
    console.log('### RemoteDescription',that.method,that.pc.remoteDescription);
  };
  that.addIceCandidate=async function(candidate){
    that.pc.addIceCandidate(new RTCIceCandidate(candidate), function(e){
      
    },function(e){
      console.log('onIceCandidate','onError',e);
    });
  };
  that.onIceCandidate=function(pc,event){
    if(event.candidate){
      onez.im.$call('sendmsg',{
        to:'user',
        uids:that.uid,
        data:{
          action:'im',
          type:'p2p.candidate',
          p2pId:p2pId,
          candidate:event.candidate,
          fromUserId:onez.im.option.userid
        }
      });
    }
  };
  that.onIceStateChange=function(pc,event){
    console.log(event,pc.iceConnectionState);
  };
  that.create=async function(uid,callType){
    that.uid=uid;
    that.callType=callType;
    that.method='calling';
    var r=await that.source();
    if(r[0]==-1){
      $('.comp-im [data-uid="'+uid+'"] .btn-cancel').trigger('click');
      return;
    }
    if(r[0]<=0){
      return;
    }
    //创建本地offer
    if(!that.pc){
      that.pc=await that.peer();
    }
    if(r[0]==1){
      var localVideo=$('.comp-im [data-uid="'+that.uid+'"] .localVideo').get(0);
      localVideo.srcObject = r[1];
      that.pc.addStream(r[1]);
    }else if(r[0]==2){
      that.pc.addStream(r[1]);
    }
    await that.pc.setLocalDescription(await that.pc.createOffer());
    console.log('### LocalDescription',that.method,that.pc.localDescription);
    onez.im.$call('sendmsg',{
      to:'user',
      uids:uid,
      data:{
        action:'im',
        type:'p2p.request',
        callType:callType,
        p2pId:p2pId,
        sdp:that.pc.localDescription,
        fromUserId:onez.im.option.userid
      }
    });
  };
  that.close=function(){
    if(that.pc){
      that.pc.close();
    }
    if(onez.p2pList[p2pId]){
      delete onez.p2pList[p2pId];
    }  
  };
};
//注册新事件
onez.events.imBtn=function(data){
  var vue=data.$el.vue();
  if(!data.uid){
    data.uid=vue.data.callUid;
  }
  console.log(data.uid);
  if(data.type=='cancel'){//取消呼叫
    onez.im.$call('sendmsg',{
      to:'user',
      uids:data.uid,
      data:{
        'action':'im',
        'type':'cancel',
        'fromUserId':onez.im.option.userid
      }
    });
    data.$el.page().close();
    onez.closeP2P(data.uid);
  }else if(data.type=='agree'){//同意
    onez.im.$call('sendmsg',{
      to:'user',
      uids:data.uid,
      data:{
        'action':'im',
        'type':'agree',
        'callType':vue.data.callType,
        'fromUserId':onez.im.option.userid
      }
    });
    onez.autoP2P().create(data.uid,vue.data.callType);
    Vue.set(vue.data,'status','ing');
  }else if(data.type=='disagree'){//拒接
    onez.im.$call('sendmsg',{
      to:'user',
      uids:data.uid,
      data:{
        'action':'im',
        'type':'disagree',
        'fromUserId':onez.im.option.userid
      }
    });
    data.$el.page().close();
  }else if(data.type=='stop'){//直接关闭
    data.$el.page().close();
    onez.closeP2P(data.uid);
  }
}
onez.events.imWin=function(data){
  onez.loadPage({
    id:'IM'+data.uid,
    width:360,
    height:600,
    action:data.type+'&uid='+data.uid+'&status='+(data.status||'request'),
    options:{
      closeBtn:0
    },
    target:'win'
  });
}