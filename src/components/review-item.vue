<template>
  <article class="review-list__item">
    <router-link :to="{ path: '/review/detail/' + ProjectInfo.id}">
      <section class="review-list__card">
        <header class="list__header">
          <h3 class="review-list__title">{{ ProjectInfo.operator_name }}发起的复核</h3>
          <time class="review-list__time">{{ ProjectInfo.created_at | dateFilters }}</time>
        </header>
        <p class="review__text">项目名称: {{ ProjectInfo.name }}</p>
        <p class="review__text">融资金额: {{ ProjectInfo.amount | commaFilters }}元</p>
        <p class="review__text">融资期限: {{ ProjectInfo.deadline }}个月</p>
        <p class="review__text">融资利率: {{ ProjectInfo.fnc_ret | fnc_retFilters }}%</p>
        <p class="review__statu">{{ ProjectInfo.status }}</p>
      </section>
    </router-link>
  </article>
</template>

<script>
  import {
    numberComma,
    dateFormat
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
    components: {
      numberComma,
      dateFormat
    },
    props: {
      ProjectInfo: {
        type: Object,
        default: function () {
          return {}
        }
      }
    },
    filters: {
      commaFilters: function (number) {
        return numberComma(number)
      },
      dateFilters: function (date) {
        return dateFormat(date, 'MM-DD HH:mm:ss')
      },
      fnc_retFilters: function (number) {
        return toDecimal2(number * 100)
      }
    }
  }
</script>

<style lang="sass">
  @import '../sass/_review-list__item'
</style>
