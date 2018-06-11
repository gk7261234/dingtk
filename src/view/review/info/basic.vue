<template>
  <div>
    <section class="review__info">
      <group label-width="5em" label-align="right" gutter="0" label-margin-right=".5em">
        <cell title="项目名称">{{ detail.name }}</cell>
        <cell title="借款合同">{{ detail.loan_contract_no }}</cell>
        <cell title="借款金额">{{ detail.amount | commaFilters}}元</cell>
        <cell title="借款期限">{{ detail.deadline }}个月</cell>
        <cell title="借款用途">{{ detail.p_fnc_use }}</cell>
        <cell title="付息方式">{{ detail.debts_type }}</cell>
        <cell title="年化利率">{{ detail.fnc_ret | fnc_retFilters }}%</cell>
        <cell title="起息日">放款日+1</cell>
        <cell title="到期日">放款后显示</cell>
        <cell title="还款来源" align-items="flex-start">{{ detail.ret_source }}</cell>
        <cell title="还款保障" align-items="flex-start">{{ detail.p_repayment_guarantee }}</cell>
        <cell title="项目等级">{{ detail.p_rank }}</cell>
        <cell title="企业用户">{{ detail.user_name }}</cell>
        <cell title="业务品种">{{ detail.p_business_variety }}</cell>
        <cell title="罚息率">{{ detail.over_fee_rate | fnc_retFilters }}%</cell>
        <cell title="授信类型">{{ detail.p_credit_type }}</cell>
        <cell title="服务费">{{ detail.service_fee | commaFilters }}元</cell>
        <cell title="发生类型">{{ detail.p_occurrence_type }}</cell>
        <cell title="融资期限">{{ detail.deadline }}个月</cell>
        <cell title="结息日">放款后显示</cell>
        <cell title="推荐人">{{ detail.p_project_recommend }}</cell>
        <cell title="还款期次">{{ detail.p_repayment_count }}</cell>
        <cell title="经办人">{{ detail.p_project_operator }}</cell>
        <cell title="期次间隔">{{ detail.p_repayment_interval }}个月</cell>
        <cell title="借款方">{{ detail.p_debtor_person }}</cell>
        <cell title="还款方">{{ detail.p_repayment_person }}</cell>
        <cell title="行业类型">{{ detail.p_industry }}</cell>
        <cell title="项目地区">{{ detail.p_city_name }}</cell>
        <cell title="项目简介" value-align="left" align-items="flex-start">{{ detail.p_project_introduction }}</cell>
      </group>
    </section>
  </div>
</template>

<script>
  import {
    Group,
    Cell,
    numberComma
  } from 'vux'

  // 保留两位小数，并补0
  function toDecimal2 (x) {
    var f, s, rs
    f = parseFloat(x)
    if (isNaN(f)) {
      return false
    }
    f = Math.round(x * 100) / 100
    s = f.toString()
    rs = s.indexOf('.')
    if (rs < 0) {
      rs = s.length
      s += '.'
    }
    while (s.length <= rs + 2) {
      s += '0'
    }
    return s
  }

  export default {
    name: 'projectInfo',
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
    },
    filters: {
      fnc_retFilters: function (number) {
        return toDecimal2(number * 100)
      },
      commaFilters: function (number) {
        return numberComma(number)
      }
    }
  }
</script>

<style lang="sass">
  @import '../../../sass/info.scss'
</style>
