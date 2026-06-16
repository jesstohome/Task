import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import vant, { Toast } from 'vant'  // 加上 Toast
import vue3SeamlessScroll from "vue3-seamless-scroll";
import Message from '@/components/message.js'
import 'vant/lib/index.css';
import {i18n,vantLocales} from '@/i18n/i18n';

const app = createApp(App)
app.config.globalProperties.$Message = Message

vantLocales(i18n.locale)

// 全局错误处理：防止白屏，自动刷新
const RELOAD_KEY = 'app_error_reload_guard'

app.config.errorHandler = (err, instance, info) => {
    console.error('Vue Error:', err, info)

    // 防止刷新后又报错形成死循环：用sessionStorage记录次数，限制最多自动刷新一次
    const reloadFlag = sessionStorage.getItem(RELOAD_KEY)
    if (reloadFlag) {
        // 已经刷新过一次还报错，不再自动刷新，避免无限循环刷新
        console.warn('Already reloaded once, skip auto reload to avoid loop')
        return
    }

    Toast.fail('Page load error, reloading...')
    sessionStorage.setItem(RELOAD_KEY, '1')

    setTimeout(() => {
        window.location.reload()
    }, 1500)
}

// 捕获Promise未处理的rejection（比如某个await接口报错没有catch）
window.addEventListener('unhandledrejection', (event) => {
    console.error('Unhandled Promise Rejection:', event.reason)

    const reloadFlag = sessionStorage.getItem(RELOAD_KEY)
    if (reloadFlag) return

    Toast.fail('Network error, reloading...')
    sessionStorage.setItem(RELOAD_KEY, '1')

    setTimeout(() => {
        window.location.reload()
    }, 1500)
})

app.use(vant).use(vue3SeamlessScroll).use(i18n).use(store).use(router).mount('#app')

// 页面刷新成功并正常渲染后，清除标记，允许下次再次自动刷新
router.isReady().then(() => {
    sessionStorage.removeItem(RELOAD_KEY)
})