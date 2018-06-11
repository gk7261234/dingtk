<template>
  <article class="review-detail">

    <actionsheet v-model="isAdopt" :menus="menuAdopt" :close-on-clicking-mask="false" @on-click-menu-ok="reviewSubmit" show-cancel @on-click-mask="console('on click mask')"></actionsheet>
    <actionsheet v-model="isReturn" :menus="menuReturn" :close-on-clicking-mask="false" @on-click-menu-return="reviewRollback" show-cancel @on-click-mask="console('on click mask')"></actionsheet>
    <toast v-model="toastSubmit" type="text" :time="1000" is-show-mask text="复核通过"></toast>
    <toast v-model="toastReturn" type="text" :time="1000" is-show-mask text="复核退回"></toast>

    <section class="review-operator">
      <UserAvatar class="a7"></UserAvatar>
      <p class="operator__name">{{ detail.operator_name }}</p>
      <p class="review__statu">{{ detail.status }}</p>
    </section>
    <group>
      <cell value-align="left">{{ detail.name }}</cell>
      <cell title="借款金额">{{ detail.amount  | commaFilters }}元</cell>
      <cell title="融资利率">{{ detail.fnc_ret | fnc_retFilters }}%</cell>
      <cell title="融资期限">{{ detail.deadline }}个月</cell>
    </group>
    <group>
      <cell title="项目信息" :link="{ path: '/review/info/basic/' + this.id }" is-link></cell>
      <cell title="还款信息" :link="{ path: '/review/info/repayment/' + this.id }" is-link></cell>
    </group>
    <box gap="20px 15px">
      <x-button class="btn" type="default" @click.native="showActionsheetReturn()">退回</x-button>
      <x-button class="btn btn_primary" type="primary" @click.native="show4Actionsheet()">通过</x-button>
    </box>
    <!--
    <box class="btn__box">
      <x-button class="btn btn_primary" type="primary" @click.native="reviewSubmit()">通过</x-button>
    </box>
    -->
  </article>
</template>

<script>
import UserAvatar from '../../components/user-avatar'
import {
  Cell,
  Group,
  XButton,
  Box,
  XSwitch,
  Actionsheet,
  Toast,
  numberComma,
  numberPad,
  numberRandom,
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
  name: 'reviewListDetail',
  components: {
    UserAvatar,
    Group,
    Cell,
    XButton,
    Box,
    XSwitch,
    Actionsheet,
    Toast
  },
  data () {
    return {
      id: this.$route.params.id,
      detail: {},
      menuAdopt: {
        ok: '确认通过'
      },
      menuReturn: {
        return: '确认退回'
      },
      isAdopt: false,
      isReturn: false,
      toastSubmit: false,
      toastReturn: false
    }
  },
  mounted: async function () {
    var _this = this
    try {
      let ReviewDetail = await this.$http.get('/apiQ/project/review/details/' + _this.id)
      _this.detail = ReviewDetail.data[0]
      console.log(ReviewDetail.data[0])
    } catch (e) {}
  },
  methods: {
    reviewSubmit: async function () {
      var _this = this
      try {
        let ReviewSubmit = await this.$http.post('/apiQ/project/review/submit', {
          id: _this.id
        })
        _this.toastSubmit = true
        setTimeout(function () {
          this.$router.push('/review-list/')
        }, 1300)
      } catch (e) {
        console.log(e)
      }
    },
    reviewRollback: async function () {
      var _this = this
      try {
        let ReviewRollback = await this.$http.post('/apiQ/project/review/rollback', {
          id: _this.id
        })
        _this.toastReturn = true
        setTimeout(function () {
          this.$router.push('/review-list/')
        }, 1300)
      } catch (e) {
        console.log(e)
      }
    },
    console (msg) {
      console.log(msg)
    },
    show4Actionsheet: async function () {
      var _this = this
      _this.isAdopt = true
    },
    showActionsheetReturn: async function () {
      var _this = this
      _this.isReturn = true
    }
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
  @import '../../sass/review-detail'
</style>
