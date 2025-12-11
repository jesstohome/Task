// support/index

import http from '@/request/index'

import qs from 'qs';
// 首页数据
export const getself = () => {
    return http.get('/my/index')
      .then((result) => {
        return result.data
      })
  }
// 获取绑定信息
export const bind_bank = () => {
    return http.get('/my/bind_bank')
      .then((result) => {
        return result.data
      })
  }
// 绑卡
export const set_bind_bank = (params) => {
    return http.post('/my/bind_bank',qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }
// 删除卡
export const user_del_bank = (params) => {
    return http.post('/my/del_bank',qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }

// 获取地址
export const get_address = () => {
    return http.get('/my/get_address')
      .then((result) => {
        return result.data
      })
  }
// 获取地址
export const get_invite = () => {
    return http.get('/my/invite')
      .then((result) => {
        return result.data
      })
  }
// 修改地址
export const edit_address = (params) => {
    return http.post('/my/edit_address',qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }
// 团队报告
export const junior = (params) => {
    return http.post('/ctrl/junior',qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }
// 我的消息
export const getmsg = (params) => {
    return http.post('/my/msg',qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }
// 修改密码
export const set_pwd = (params) => {
    return http.post('/ctrl/set_pwd',qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }
// 财务
export const caiwu = (params) => {
    return http.get('/my/caiwu?'+qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }
// 充值记录
export const recharge_admin = (params) => {
    return http.get('/ctrl/recharge_admin?'+qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }
// 充值记录
export const deposit_admin = (params) => {
    return http.get('/ctrl/deposit_admin?'+qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }
// 提现
export const do_deposit = (params) => {
    return http.post('/ctrl/do_deposit',qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }
