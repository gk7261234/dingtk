<template>
  <div>
    <div class="review-list" v-if="list.length > 0">
      <ReviewItem v-for="item in list" :key="item.id" :project-info="item"></ReviewItem>
    </div>
    <div v-else>空列表</div>
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
        list: {}
      }
    },
    mounted: async function () {
      let ReviewList = await this.$http.get('/apiQ/project/review/list')
      this.list = ReviewList.data
    }
  }
</script>

<style lang="sass">
  @import '../../sass/review-list'
</style>
