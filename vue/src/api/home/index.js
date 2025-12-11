import http from '@/request/index'

import qs from 'qs';
// 首页数据
export const getHomeData = () => {
    return http.get('/index/home')
      .then((result) => {
        return result.data
      })
  }

// 首页数据
export const get_msg = () => {
    return http.get('/index/get_msg')
      .then((result) => {
        return result.data
      })
  }

// 首页数据
export const get_level_list = () => {
    return http.get('/index/get_level_list')
      .then((result) => {
        return result.data
      })
  }

// 首页数据
export const get_lixibao = () => {
    return http.get('/ctrl/lixibao')
      .then((result) => {
        return result.data
      })
  }
// 首页数据
export const lixibao_chu = (params) => {
    return http.get('/ctrl/lixibao_chu?'+qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }
// 首页数据
export const getdetailbyid = (id) => {
    return http.get('/my/detail?id='+id)
      .then((result) => {
        return result.data
      })
  }

// 首页数据
export const lixibao_ru = (params) => {
    return http.post('/ctrl/lixibao_ru',qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }


// 首页数据
export const get_lixibao_chu = (params) => {
    return http.post('/ctrl/lixibao_chu',qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }


// 首页数据
export const get_recharge = (params) => {
    return http.post('/ctrl/recharge?'+qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }


// 首页数据
export const get_recharge2 = (params) => {
    return http.post('/ctrl/recharge2?'+qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }

// 首页数据
export const bank_recharge = (params) => {
    return http.post('/ctrl/bank_recharge',qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }

// 首页数据
export const headpicUpdatae = (params) => {
    return http.post('/my/headpicUpdatae',qs.stringify(params))
      .then((result) => {
        return result.data
      })
  }


// 首页数据
export const uploadImg = (params) => {
    return http.post('/admin/index.html?s=/admin/api.plugs/upload',params, {
      // 因为我们上传了图片,因此需要单独执行请求头的Content-Type
      headers: {
        // 表示上传的是文件,而不是普通的表单数据
        'Content-Type': 'multipart/form-data'
      }
    })
      .then((result) => {
        return result.data
      })
  }

