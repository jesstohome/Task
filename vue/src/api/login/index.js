import http from '@/request/index'

import qs from 'qs';
// 公共参数 -- 字典值
export const common_parameters = () => {
    return http.get('/user/common_parameters')
      .then((result) => {
        return result.data
      })
  }

// 定义接口的传参
// interface UserInfoParam {
//     tel: string,
//     pwd: string
//     qv: string
// }
//登录
export const login = (params) => {
    return http.post('/user/do_login',qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }
//退出登录
export const logout = (params) => {
    return http.post('/user/logout',qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }
//注册
export const do_register = (params) => {
    return http.post('/user/do_register',qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }
