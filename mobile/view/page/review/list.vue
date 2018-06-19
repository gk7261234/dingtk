<template>
  <div>
    <div class="review-list" v-if="list.length > 0">
      <ReviewItem v-for="item in list" :key="item.id" :project-info="item"></ReviewItem>
    </div>
    <div v-else>Loading...</div>
  </div>
</template>

<script>
  import ReviewItem from '../../components/review-item'

  export default {
    components: {
      ReviewItem
    },
    data () {
      return {
        list: {},
        userName: ''
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

      dd.ready(async function () {
        dd.biz.user.get({
          corpId: _config.corpId,
          onSuccess: async function (data) {
            _this.userName = data.nickName
            try {
              let dAuthUser = await _this.$http.post('/apiQ/dAuthUser', {
                userName: _this.userName
              })
              if(dAuthUser.data.result == 'Y') {
                let ReviewList = await _this.$http.get('/apiQ/project/review/list')
                _this.list = ReviewList.data
              } else {
                alert(dAuthUser.data.error)
                return false
              }
            } catch (e) {
              alert(e)
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

<style lang="sass">
  @import '../../sass/review-list'
</style>
