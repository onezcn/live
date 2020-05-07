<h1>佳蓝音视频实时通话系统</h1>
<div class="sys-tip">
  <p>友情提示：复制下面网址在新窗口打开，可以以新的用户身份出现。</p>
  <code>{{data.url}}</code>
</div>
<div class="user-box row">
  <div class="col-md-3 col-xs-12" v-for="item in data.users">
    <div class="user-item" :class="{isMe:item.isMe}">
      <div class="user-item-border">
        <div class="avatar"></div>
        <h3 v-html="item.nickname"></h3>
        <p v-html="item.status"></p>
      </div>
    </div>
  </div>
  <div class="noTip" v-if="!data.users">
    正在加载用户列表...
  </div>
</div>