<div class="im-box" :data-uid="data.callUid">
  <div class="callBox callBox-video" :data-status="onez.imInit(data)">
    <template v-if="data.callType=='video'">
      <video class="localVideo" muted="muted" autoplay="true"></video>
      <video class="remoteVideo" autoplay="true"></video>
    </template>
    <template v-else>
      <video class="localVideo audio" style="" muted="muted" autoplay="true"></video>
      <video class="remoteVideo audio" style="" autoplay="true"></video>
    </template>
    <template v-if="data.status=='request'">
      <p class="tip">等待对方回应</p>
      <div class="btns btns-calling">
        <div class="mybtn btn-cancel btn-cancel-calling" data-token="imBtn" data-type="cancel">取消</div>
      </div>
      <template v-if="data.callType=='video'">
        <audio :src="data.resurl+'/mp3/phonering.mp3'" loop autoplay />
      </template>
      <template v-else>
        <audio :src="data.resurl+'/mp3/ipcall_phonering2.mp3'" loop autoplay />
      </template>
    </template>
    <template v-else-if="data.status=='ask'">
      <p class="tip">请求与您进行{{data.callTypeName}}通话</p>
      <div class="btns btns-ask">
        <div class="mybtn btn-ok btn-agree" data-token="imBtn" data-type="agree">接受</div>
        <div class="mybtn btn-cancel btn-disagree" data-token="imBtn" data-type="disagree">拒绝</div>
      </div>
      <template v-if="data.callType=='video'">
        <audio :src="data.resurl+'/mp3/phonering.mp3'" loop autoplay />
      </template>
      <template v-else>
        <audio :src="data.resurl+'/mp3/ipcall_phonering2.mp3'" loop autoplay />
      </template>
    </template>
    <template v-else-if="data.status=='ing'">
      <p class="tip">{{data.callTypeName}}通话中</p>
      <div class="btns btns-say">
        <div class="mybtn btn-cancel btn-stop" data-token="imBtn" data-type="cancel">挂断</div>
      </div>
    </template>
    <template v-else-if="data.status=='disagree'">
      <p class="tip">对方拒绝的您通话请求</p>
      <div class="btns btns-say">
        <div class="mybtn btn-cancel btn-stop" data-token="imBtn" data-type="stop">结束</div>
      </div>
    </template>
    <template v-else-if="data.status=='cancel'">
      <p class="tip">对方取消了通话</p>
      <div class="btns btns-say">
        <div class="mybtn btn-cancel btn-stop" data-token="imBtn" data-type="stop">结束</div>
      </div>
    </template>
    <template v-else-if="data.status=='busy'">
      <p class="tip">对方正忙</p>
      <div class="btns btns-say">
        <div class="mybtn btn-cancel btn-stop" data-token="imBtn" data-type="stop">结束</div>
      </div>
    </template>
  </div>
</div>
