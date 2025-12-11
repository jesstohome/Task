import axios from 'axios'
import router from "@/router";
import { Notify,Toast } from 'vant';
import {i18n} from '@/i18n/i18n';
import Message from '@/components/message.js'
console.log(i18n)
const { t } = i18n.global;

// 创建一个 axios 实例
const service = axios.create({
	// baseURL: 'https://shop7779.com/index/', // 所有的请求地址前缀部分
	// baseURL: 'https://amazonbrazill.world/index/', // 所有的请求地址前缀部分
	baseURL: window.config.api, // 所有的请求地址前缀部分/
	// baseURL: 'https://ok77168.space/index/', // 所有的请求地址前缀部分
	// baseURL: 'http://www.jake1006.space/index/', // 所有的请求地址前缀部分
	timeout: 60000, // 请求超时时间毫秒
	withCredentials: true, // 异步请求携带cookie
})

// 添加请求拦截器
// var load = null;
service.interceptors.request.use(
	function (config) {
		// 在发送请求之前做些什么
        // 1.获取token
		config.headers['Access-Control-Allow-Credentials']=true
        var token = localStorage.getItem('token')
        if (token) {
            // 2.将值传递到服务器
            config.headers['token'] = token
        }
        // 1.获取语种
        var lang = localStorage.getItem('lang')
        if (lang) {
            // 2.将值传递到服务器
            config.headers['language'] = lang
        }
		// load=Toast.loading({
		// 	duration: 0,
		// 	message: t('msg.jzz')+'...',
		// 	forbidClick: true,
		//   });
		return config
	},
	function (error) {
		// 对请求错误做些什么
		console.log(error)
		return Promise.reject(error)
	}
)

// 添加响应拦截器
service.interceptors.response.use(
	function (response) {
		// Toast.clear()
		// 2xx 范围内的状态码都会触发该函数。
		// 对响应数据做点什么
		// dataAxios 是 axios 返回数据中的 data
		const dataAxios = response.data
		// 这个状态码是和后端约定的
		const code = dataAxios.code
		if(code == -400) {
			Message({ type: 'error', message:dataAxios.info});
			router.push('/login')
		}
		return response
	},
	function (error) {
		// 超出 2xx 范围的状态码都会触发该函数。
		// 对响应错误做点什么
  		// Toast.clear();  // 清除加载
		console.log(error)
		return Promise.reject(error)
	}
)
export default service
