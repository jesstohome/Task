import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import vant from 'vant'
import vue3SeamlessScroll from "vue3-seamless-scroll";
import Message from '@/components/message.js'
// import { Locale } from 'vant'
// // 引入英文语言包
// import enUS from 'vant/es/locale/lang/en-US';
// Locale.use('en-US', enUS);
// 2. 引入组件样式
import 'vant/lib/index.css';
import {i18n,vantLocales} from '@/i18n/i18n';
const app = createApp(App)
app.config.globalProperties.$Message = Message

vantLocales(i18n.locale)
app.use(vant).use(vue3SeamlessScroll).use(i18n).use(store).use(router).mount('#app')
