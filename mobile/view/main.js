import Vue from 'vue'
import FastClick from 'fastclick'
import App from './App'
import router from './router/router.js'
import axios from 'axios'
import {
  AjaxPlugin,
  LoadingPlugin
} from 'vux'

Vue.use(AjaxPlugin)
Vue.use(LoadingPlugin)

Vue.config.productionTip = false
Vue.prototype.$http = axios

FastClick.attach(document.body)

new Vue({
  router,
  render: h => h(App)
}).$mount('#app-box')
