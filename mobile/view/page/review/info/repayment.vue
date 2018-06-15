<template>
  <div>
    <section class="review__info">
      <group label-width="7.5em" label-align="right" gutter="0">
        <cell title="担保措施">{{ detail.credit_type }}</cell>
        <cell title="经营及财务情况">{{ detail.p_funding_operation }}</cell>
        <cell title="还款能力变化">{{ detail.p_repayment_capacity_change }}</cell>
        <cell title="逾期情况">{{ detail.p_overdue_condition }}</cell>
        <cell title="涉诉情况">{{ detail.p_complaint }}</cell>
        <cell title="受行政处罚情况">{{ detail.p_administrative_sanction }}</cell>
        </group>
    </section>
  </div>
</template>

<script>
  import { Group, Cell } from 'vux'

  export default {
    name: 'repaymentInfo',
    components: {
      Group,
      Cell
    },
    data () {
      return {
        projectID: this.$route.params.id,
        detail: {}
      }
    },
    mounted: async function () {
      var _this = this
      try {
        let reviewDetail = await this.$http.get('/apiQ/project/review/details/' + _this.projectID)
        _this.detail = reviewDetail.data[0]
      } catch (e) {}
    }
  }
</script>

<style lang="sass">
  @import '../../../sass/info.scss'
</style>
