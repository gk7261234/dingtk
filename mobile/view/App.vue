<template>
  <div id="app" v-if="isUser">
    <router-view></router-view>
  </div>
</template>

<script>
  import crypto from 'crypto'
  import {
    cookie
  } from 'vux'

  export default {
    name: 'app',
    data() {
      return {
        isUser: false
      }
    },
    mounted: async function () {
      var _this = this
      var _config

      try {
        let dAuth = await _this.$http.get('/apiQ/dAuth')
        _config = dAuth.data
      } catch (e) {
        alert(e)
      }

      dd.config({
        agentId: _config.agentId,
        corpId: _config.corpId,
        timeStamp: _config.timeStamp,
        nonceStr: _config.nonceStr,
        signature: _config.signature,
        jsApiList: [
          'biz.user.get'
        ]
      })

      dd.error(function (e) {
        alert('dd error: ' + JSON.stringify(e))
      })

      dd.ready(function () {
        dd.biz.user.get({
          corpId: _config.corpId,
          onSuccess: function (data) {
            if(data.nickName == '冯武泰') {
              _this.isUser = true
            } else {
              alert('无权限访问此页面')
            }
          },
          onFail: function (e) {
            alert('user.get fail: ' + JSON.stringify(e))
          }
        })
      })
    }
  }
</script>

<style lang="less">
  @import '~vux/src/styles/reset.less';
</style>

<style lang="sass">
  @import './sass/common'
</style>
