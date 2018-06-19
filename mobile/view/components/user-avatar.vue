<template>
  <div class="avatar">
    <img class="avatar__img" :src="imgUrl">
  </div>
</template>

<script>
  export default {
    name: 'Avatar',
    data () {
      return {
        imgUrl: './mobile/view/assets/img/avatar.png'
      }
    },
    mounted: function () {
      var _this = this
      dd.biz.user.get({
        onSuccess: function (data) {
          _this.imgUrl = data.avatar
        },
        onFail: function (err) {}
      })
    }
  }
</script>

<style scoped lang="sass">
  @import '../sass/avatar'
</style>
