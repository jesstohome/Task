// support/index

import http from '@/request/index'

import qs from 'qs';
// 首页数据
export const getsupport = () => {
    return http.get('/support/index')
      .then((result) => {
        return result.data
      })
  }
