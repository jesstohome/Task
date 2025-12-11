import { createStore } from 'vuex'
const lang = localStorage.getItem('lang') || ''
const langImg = localStorage.getItem('langImg') || ''
const fooCheck = localStorage.getItem('fooCheck') || 'home'
// 公用参数
const baseInfo = localStorage.getItem('baseInfo')
// 个人信息
const userinfo = localStorage.getItem('userinfo')
// token
const token = localStorage.getItem('token')
// 记住账号
const user = localStorage.getItem('user')
// 系统弹窗时间
const xtTime = localStorage.getItem('xtTime')
// 抢单界面数据
const objInfo = localStorage.getItem('objInfo')
// 钱包数据缓存
const minfo = localStorage.getItem('minfo')

export default createStore({
  state: {
    lang,
    langImg,
    langList:[
      {label: 'lang.zh', value: 'zh_cn', img: require('@/assets/images/register/zh.png')},
      {label: 'lang.en', value: 'en_es', img: require('@/assets/images/register/en.png')},
    ],
    fooCheck,
    token,
    xtTime,
    objInfo: objInfo ? JSON.parse(objInfo) : {},
    baseInfo: baseInfo ? JSON.parse(baseInfo) : {},
    userinfo: userinfo ? JSON.parse(userinfo) : {},
    user: user ? JSON.parse(user) : {},
    minfo: minfo ? JSON.parse(minfo) : {},
  },
  getters: {
  },
  mutations: {
    settoken(state,value){
      state.token = value
      localStorage.setItem('token',value)
    },
    setxtTime(state,value){
      state.xtTime = value
      localStorage.setItem('xtTime',value)
    },
    setlang(state,value){
      state.lang = value
      localStorage.setItem('lang',value)
    },
    setlangImg(state,value){
      state.langImg = value
      localStorage.setItem('langImg',value)
    },
    setfooCheck(state,value){
      state.fooCheck = value
      localStorage.setItem('fooCheck',value)
    },
    setobjInfo(state,value){
      state.objInfo = {...value}
      localStorage.setItem('objInfo',JSON.stringify(value))
    },
    setbaseInfo(state,value){
      state.baseInfo = {...value}
      localStorage.setItem('baseInfo',JSON.stringify(value))
    },
    setuserinfo(state,value){
      state.userinfo = {...value}
      localStorage.setItem('userinfo',JSON.stringify(value))
    },
    setuser(state,value){
      state.user = {...value}
      localStorage.setItem('user',JSON.stringify(value))
    },
    setminfo(state,value){
      state.minfo = {...value}
      localStorage.setItem('minfo',JSON.stringify(value))
    },
    deluser(state,value){
      state.user = {}
      localStorage.setItem('user', '')
    },
  },
  actions: {
    changetoken(context,params){  //context是一个对象，从它里面把咱们需要的commit方法解构出来
        let {commit} = context
        commit('settoken',params)
    },
    changextTime(context,params){  //context是一个对象，从它里面把咱们需要的commit方法解构出来
        let {commit} = context
        commit('setxtTime',params)
    },
    changeminfo(context,params){  //context是一个对象，从它里面把咱们需要的commit方法解构出来
        let {commit} = context
        commit('setminfo',params)
    },
    changeUser(context,params){  //context是一个对象，从它里面把咱们需要的commit方法解构出来
        let {commit} = context
        commit('setuser',params)
    },
    clearUser(context,params){  //context是一个对象，从它里面把咱们需要的commit方法解构出来
        let {commit} = context
        commit('deluser',params)
    },
    changelang(context,params){  //context是一个对象，从它里面把咱们需要的commit方法解构出来
        let {commit} = context
        commit('setlang',params)
    },
    changelangImg(context,params){  //context是一个对象，从它里面把咱们需要的commit方法解构出来
        let {commit} = context
        commit('setlangImg',params)
    },
    changefooCheck(context,params){  //context是一个对象，从它里面把咱们需要的commit方法解构出来
        let {commit} = context
        commit('setfooCheck',params)
    },
    changeobjInfo(context,params){  //context是一个对象，从它里面把咱们需要的commit方法解构出来
        let {commit} = context
        commit('setobjInfo',params)
    },
    changebaseInfo(context,params){  //context是一个对象，从它里面把咱们需要的commit方法解构出来
        let {commit} = context
        commit('setbaseInfo',params)
    },
    changeuserinfo(context,params){  //context是一个对象，从它里面把咱们需要的commit方法解构出来
        let {commit} = context
        commit('setuserinfo',params)
    },
  },
  modules: {
  }
})
// store.dispatch('changeworkStatus',list)