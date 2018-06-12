<template>
  <div id="app">
    <div>
      <h3>DD API</h3>
      <p>{{ name }}</p>
    </div>
    <hr>
    <router-view></router-view>
  </div>
</template>

<script>
  import crypto from 'crypto'
  import {
    cookie
  } from 'vux'

  var corpid = 'ding2c238b123d0f70fe'
  var corpsecret = 'WSglHDjrPbTAaEEImMNOZnLYsMTFTqhFHGtOjNp7Rkj2igv5plK51OJ1mW2DfXul'
  var singnatureUrl = encodeURI('http://192.168.1.56:8080/')
  var singnatureNoncestr = 'jR93Gk5jc0mFgf76'
  var singnatureTimestamp = new Date().getTime()
  var signature
  var accessToken

  export default {
    name: 'app',
    data() {
      return {
        token: 'undefined',
        signature: signature,
        isSignature: false,
        name: ''
      }
    },
    mounted: async function () {
      var _this = this
      function getJsApiSingnature (jsapiTicket, noncestr, timestamp, url) {
        var plain = 'jsapi_ticket=' +
        jsapiTicket +
        '&noncestr=' +
        noncestr +
        '&timestamp=' +
        timestamp +
        '&url=' +
        url
        var sha1 = crypto.createHash('sha1')
        sha1.update(plain, 'utf8')
        signature = sha1.digest('hex')
        console.log('plain: ' + plain)
      }
      async function isJsticket () {
        var singnatureJsticket = cookie.get('cookieJsapiTicket')
        if (singnatureJsticket === undefined) {
          let gettoken = await _this.$http.get('/apiD/gettoken', {
            params: {
              corpid: corpid,
              corpsecret: corpsecret
            }
          })
          accessToken = gettoken.data.access_token

          let get_jsapi_ticket = await _this.$http.get('/apiD/get_jsapi_ticket', {
            params: {
              access_token: gettoken.data.access_token
            }
          })
          cookie.set('cookieJsapiTicket', get_jsapi_ticket.data.ticket, {
            expires: 7200000
          })

          getJsApiSingnature(singnatureJsticket, singnatureNoncestr, singnatureTimestamp, singnatureUrl)
        } else {
          getJsApiSingnature(singnatureJsticket, singnatureNoncestr, singnatureTimestamp, singnatureUrl)
        }
      }
      try {
        if (!_this.isSignature) {
          isJsticket()
        }
        dd.config({
          agentId: 171995680,
          corpId: corpid,
          timeStamp: singnatureTimestamp,
          nonceStr: singnatureNoncestr,
          signature: signature,
          jsApiList: [
            'runtime.info',
            'device.notification.prompt',
            'biz.chat.pickConversation',
            'device.notification.confirm',
            'device.notification.alert',
            'device.notification.prompt',
            'biz.chat.open',
            'biz.util.open',
            'biz.user.get',
            'biz.contact.choose',
            'biz.telephone.call',
            'biz.ding.post',
            'runtime.permission.requestAuthCode'
          ]
        })
        dd.error(function (e) {
          alert('dd error: ' + JSON.stringify(e))
        })
        dd.ready(function () {
          dd.biz.user.get({
            corpId: corpid,
            onSuccess: function (data) {
              _this.name = data.nickName
              if(data.nickName !== '王泽惠') {
                alert('不是王泽惠')
              }
            },
            onFail: function (err) {
              alert('userGet fail: ' + JSON.stringify(err))
            }
          })
        })
      } catch (e) {
        alert(e)
      }
    }
  }
</script>

<style lang="less">
  @import '~vux/src/styles/reset.less';
</style>

<style lang="sass">
  @import './sass/common'
</style>
