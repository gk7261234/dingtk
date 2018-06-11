import Vue from 'vue'
import VueRouter from 'vue-router'
import AppMenu from '../components/app-menu'
import ReviewList from '../view/review/list'
import ReviewDetail from '../view/review/detail'
import ReviewInfoBasic from '../view/review/info/basic'
import ReviewInfoRepayment from '../view/review/info/repayment'

Vue.use(VueRouter)

const RouterView = '<router-view></router-view>'
const routes = [
  {
    path: '*',
    component: AppMenu
  },
  {
    path: '/review',
    component: {
      template: RouterView
    },
    children: [
      {
        path: 'list',
        component: ReviewList
      },
      {
        path: 'detail/:id',
        component: ReviewDetail
      },
      {
        path: 'info',
        component: {
          template: RouterView
        },
        children: [
          {
            path: 'basic/:id',
            component: ReviewInfoBasic
          },
          {
            path: 'repayment/:id',
            component: ReviewInfoRepayment
          }
        ]
      }
    ]
  },
  {
    name: 'projectInfo',
    path: '/project-info/:id',
    component: ReviewInfoBasic
  },
  {
    name: 'repaymentInfo',
    path: '/repayment-info/:id',
    component: ReviewInfoRepayment
  }
]

var router = new VueRouter({
  routes
})

export default router
