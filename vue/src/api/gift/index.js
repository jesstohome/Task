import http from '@/request/index'
import qs from 'qs';

// 检查是否有礼包
export const check_gift = (params) => {
    return http.get('/index/check_gift?' + qs.stringify(params))
        .then((result) => {
            return result.data
        })
}

// 领取礼包
export const claim_gift = (params) => {
    return http.post('/index/claim_gift', qs.stringify(params))
        .then((result) => {
            return result.data
        })
}