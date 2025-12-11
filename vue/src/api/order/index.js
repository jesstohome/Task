
import http from '@/request/index'

import qs from 'qs';
// 首页数据
export const getOrderList = (params) => {
    console.log(qs.stringify(params))
    return http.get('/order/index?'+qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }
// 首页数据
export const rot_order = (params) => {
    return http.get('/rot_order/index')
      .then((result) => {
        return result.data
      })
  }
// 首页数据
export const submit_order = (params) => {
    return http.post('/rot_order/submit_order')
      .then((result) => {
        return result.data
      })
  }
// 首页数据
export const do_order = (params) => {
    return http.post('/order/do_order',qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }
// 首页数据
export const order_info = (params) => {
    return http.post('/order/order_info',qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }
