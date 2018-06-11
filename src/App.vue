<template>
  <div id="app">
    <router-view></router-view>
  </div>
</template>

<style lang="less">
  @import '~vux/src/styles/reset.less';
</style>

<style lang="sass">
  @import './sass/common'
</style>

<!--
<script>
// import crypto from 'crypto'
// import {
//   cookie
// } from 'vux'
// import axios from 'axios'

// const corpid = 'ding2c238b123d0f70fe'
// const corpsecret = 'WSglHDjrPbTAaEEImMNOZnLYsMTFTqhFHGtOjNp7Rkj2igv5plK51OJ1mW2DfXul'
// const singnatureUrl = encodeURI('http://192.168.1.56:8080/')
// const singnatureNoncestr = 'jR93Gk5jc0mFgf76'
// var singnatureTimestamp = new Date().getTime()
// dd签名信息
// var signature
// var accessToken
// export default {
//   name: 'app',
//   data() {
//     return {
// name: 'null',
// signature: signature,
// avatar: ''
//   }
// }
// ,
// created: async function () {
//   var _this = this

// 计算签名信息方法
// function getJsApiSingnature (jsapiTicket, noncestr, timestamp, url) {
//   var plain = 'jsapi_ticket=' +
//   jsapiTicket +
//   '&noncestr=' +
//   noncestr +
//   '&timestamp=' +
//   timestamp +
//   '&url=' +
//   url
//   var sha1 = crypto.createHash('sha1')
//   sha1.update(plain, 'utf8')
//   signature = sha1.digest('hex')
//   console.log('plain: ' + plain)
// }
//
// try {
// 访问钉钉接口获取 access_token
// let dtAccessToken = await this.$http.get('/apiD/gettoken', {
//   params: {
//     corpid: corpid,
//     corpsecret: corpsecret
//   }
// })
// accessToken = dtAccessToken.data.access_token
// console.log('GET access_token: ' + accessToken)

// 读取 Cookie 中的 jsapi_ticket
// var cookieJsapiTicket = cookie.get('cookieJsapiTicket')
// alert('cookieJsapiTicket: ' + cookieJsapiTicket)
// 读取 Cookie 中的 jsapi_ticket 成功，计算签名信息，否则请求接口获取新的 jsapi_ticket
// if (cookieJsapiTicket & true) {
//   getJsApiSingnature(cookieJsapiTicket, singnatureNoncestr, singnatureTimestamp, singnatureUrl)
//   console.log('生成的签名信息' + signature)
// } else {
// 访问钉钉接口获取 jsapi_ticket
// let dtTicket = await this.$http.get('/apiD/get_jsapi_ticket', {
//   params: {
//     access_token: dtAccessToken.data.access_token
//   }
// })
// var singnatureJsticket = dtTicket.data.ticket
// console.log('生成新的jsapi_ticket: ' + singnatureJsticket)
// 将新生成的 jsapi_ticket 存到 Cookie 中，2小时后失效
//   cookie.set('cookieJsapiTicket', singnatureJsticket, {
//     expires: 7200000
//   })
// }

// if (singnatureJsticket) {
//
// } else {
//   console.log('找不到jsticket')
// }
// } catch (err) {
// console.log(err)
// alert('err: ' + err)
// }

// dd配置
// dd.config({
//   agentId: 171995680,
//   corpId: 'ding2c238b123d0f70fe',
//   timeStamp: singnatureTimestamp,
//   nonceStr: singnatureNoncestr,
//   signature: signature,
//   jsApiList: [
//     'runtime.info',
//     'device.notification.prompt',
//     'biz.chat.pickConversation',
//     'device.notification.confirm',
//     'device.notification.alert',
//     'device.notification.prompt',
//     'biz.chat.open',
//     'biz.util.open',
//     'biz.user.get',
//     'biz.contact.choose',
//     'biz.telephone.call',
//     'biz.ding.post',
//     'runtime.permission.requestAuthCode'
//   ]
// })
// dd配置失败
// dd.error(function (error) {
//   this.$error('dd error: ' + JSON.stringify(error))
// })
// dd配置成功
// dd.ready(function () {
//   alert('dd配置成功')
// dd.runtime.permission.requestAuthCode({
//   corpId: 'ding2c238b123d0f70fe',
//   onFail: function (err) {
//     alert('requestAuthCode error')
//     alert('requestAuthCode error: ' + JSON.stringify(err))
//   },
//   onSuccess: async function (data) {
//     await getUserInfo(accessToken, data.code)
//   }
// })

// 获取用户信息
// dd.biz.user.get({
//   corpId: 'ding2c238b123d0f70fe',
//   onSuccess: function (data) {
//     // _this.avatar = data.avatar
//     _this.avatar = data.avatar
//     console.log('_this.avatar: ' + _this.avatar)
//     console.log('userGet success: ' + JSON.stringify(data))
//   },
//   onFail: function (err) {
//     this.$error('userGet fail: ' + JSON.stringify(err))
//   }
// })
//   })
// },
// mounted: async function () {
// async function getUserInfo (accessToken, userinfoCode) {
//   try {
//     var userInfoId
//     let getUserInfo = await axios.get('/apiD/user/getuserinfo', {
//       params: {
//         access_token: accessToken,
//         code: userinfoCode
//       }
//     })
//     userInfoId = getUserInfo.data.userid
//     if (userInfoId) {
//       let getUser = await axios.get('/apiD/user/get', {
//         params: {
//           access_token: accessToken,
//           userid: userInfoId
//         }
//       })
//       _this.name = getUser.data.name
//     }
//   } catch (e) {
//     alert(e)
//   }
// }
// }
// }
</script>
-->
