import { createRouter, createWebHistory } from 'vue-router'
import login from '../views/login/login.vue'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: login
  },
  {
    path: '/register',
    name: 'register',
    component: ()=> import('@/views/login/register.vue')
  },
  {
    path: '/service',
    name: 'service',
    component: ()=> import('@/views/tel/service.vue')
  },
  {
    path: '/level',
    name: 'level',
    component: ()=> import('@/views/index/level.vue')
  },
  {
    path: '/detail/:id',
    name: 'detail',
    component: ()=> import('@/views/order/detail.vue')
  },
// 余利宝
  {
    path: '/libao',
    name: 'libao',
    meta: {
      keepAlive: true,
      name: 'libao'
    },
    component: () => import(/* webpackChunkName: "index" */ '@/views/index/components/libao.vue')
  },
  {
    path: '/',
    name: 'index',
    component: ()=> import('@/views/index/index.vue'),
    redirect: '/home',
    children: [
      {
        path: '/home',
        name: 'home',
        meta: {
          keepAlive: true,
          name: 'Home'
        },
        component: () => import(/* webpackChunkName: "index" */ '@/views/index/home.vue')
      },
      {
        path: '/order',
        name: 'order',
        meta: {
          keepAlive: true,
          name: 'order'
        },
        component: () => import(/* webpackChunkName: "index" */ '@/views/order/index.vue')
      },
      {
        path: '/tel',
        name: 'tel',
        meta: {
          keepAlive: true,
          name: 'tel'
        },
        component: () => import(/* webpackChunkName: "index" */ '@/views/tel/index.vue')
      },
      {
        path: '/obj',
        name: 'obj',
        meta: {
          keepAlive: true,
          name: 'obj'
        },
        component: () => import(/* webpackChunkName: "index" */ '@/views/obj/index.vue')
      },
      {
        path: '/self',
        name: 'self',
        meta: {
          keepAlive: true,
          name: 'self'
        },
        component: () => import(/* webpackChunkName: "index" */ '@/views/self/index.vue')
      },
      // 绑定银行卡
      {
        path: '/bingbank',
        name: 'bingbank',
        meta: {
          keepAlive: true,
          name: 'bingbank'
        },
        component: () => import(/* webpackChunkName: "index" */ '@/views/self/components/bingbank.vue')
      },
      // 收获地址
      {
        path: '/address',
        name: 'address',
        meta: {
          keepAlive: true,
          name: 'address'
        },
        component: () => import(/* webpackChunkName: "index" */ '@/views/self/components/address.vue')
      },
      // 设置头像
      {
        path: '/avatar',
        name: 'avatar',
        meta: {
          keepAlive: true,
          name: 'avatar'
        },
        component: () => import(/* webpackChunkName: "index" */ '@/views/self/components/avatar.vue')
      },
      // 团队记录
      {
        path: '/team',
        name: 'team',
        meta: {
          keepAlive: true,
          name: 'team'
        },
        component: () => import(/* webpackChunkName: "index" */ '@/views/self/components/team.vue')
      },
      // 我的消息
      {
        path: '/message',
        name: 'message',
        meta: {
          keepAlive: true,
          name: 'message'
        },
        component: () => import(/* webpackChunkName: "index" */ '@/views/self/components/message.vue')
      },
      // 修改密码
      {
        path: '/editPwd',
        name: 'editPwd',
        meta: {
          keepAlive: true,
          name: 'editPwd'
        },
        component: () => import(/* webpackChunkName: "index" */ '@/views/self/components/editPwd.vue')
      },
      // 账户明细
      {
        path: '/account_details',
        name: 'account_details',
        meta: {
          keepAlive: true,
          name: 'account_details'
        },
        component: () => import(/* webpackChunkName: "index" */ '@/views/self/components/account_details.vue')
      },
      // 分享
      {
        path: '/share',
        name: 'share',
        meta: {
          keepAlive: true,
          name: 'share'
        },
        component: () => import(/* webpackChunkName: "index" */ '@/views/self/components/share.vue')
      },
      // 充值记录
      {
        path: '/recharge',
        name: 'recharge',
        meta: {
          keepAlive: true,
          name: 'recharge'
        },
        component: () => import(/* webpackChunkName: "index" */ '@/views/self/components/recharge.vue')
      },
      // 提现记录
      {
        path: '/deposit',
        name: 'deposit',
        meta: {
          keepAlive: true,
          name: 'deposit'
        },
        component: () => import(/* webpackChunkName: "index" */ '@/views/self/components/deposit.vue')
      },
      // 提现
      {
        path: '/drawing',
        name: 'drawing',
        meta: {
          keepAlive: true,
          name: 'drawing'
        },
        component: () => import(/* webpackChunkName: "index" */ '@/views/self/components/drawing.vue')
      },
      
      // 余利宝转入记录
      {
        path: '/libao_jl',
        name: 'libao_jl',
        meta: {
          keepAlive: true,
          name: 'libao_jl'
        },
        component: () => import(/* webpackChunkName: "index" */ '@/views/index/components/libao_jl.vue')
      },
      // 提升等级
      {
        path: '/addlevel',
        name: 'addlevel',
        meta: {
          keepAlive: true,
          name: 'addlevel'
        },
        component: () => import(/* webpackChunkName: "index" */ '@/views/index/components/addlevel.vue')
      },
      // 充值付款确认
      {
        path: '/next_cz',
        name: 'next_cz',
        meta: {
          keepAlive: true,
          name: 'next_cz'
        },
        component: () => import(/* webpackChunkName: "index" */ '@/views/index/components/next_cz.vue')
      },
      // 充值付款确认
      {
        path: '/next_cz2',
        name: 'next_cz2',
        meta: {
          keepAlive: true,
          name: 'next_cz2'
        },
        component: () => import(/* webpackChunkName: "index" */ '@/views/index/components/next_cz2.vue')
      },
      // 充值
      {
        path: '/chongzhi',
        name: 'chongzhi',
        meta: {
          keepAlive: true,
          name: 'chongzhi'
        },
        component: () => import(/* webpackChunkName: "index" */ '@/views/index/components/chongzhi.vue')
      },
      // 充值
      {
        path: '/content',
        name: 'content',
        meta: {
          keepAlive: true,
          name: 'content'
        },
        component: () => import(/* webpackChunkName: "index" */ '@/views/index/components/content.vue')
      },
    ]
  },
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

// 4. 你还可以监听路由拦截，比如权限验证。
router.beforeEach((to, from, next) => {
  // 1. 每个条件执行后都要跟上 next() 或 使用路由跳转 api 否则页面就会停留一动不动
  // 2. 要合理的搭配条件语句，避免出现路由死循环。
  var token = localStorage.getItem('token')
  console.log(to)
  if (to.name == 'home' || to.name == 'login' || to.name == 'register' || to.name == 'service') {
    next()
  } else if (!token) {
    next('/login')
  } else {
    next()
  }
})

export default router
