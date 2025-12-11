import { createI18n } from 'vue-i18n' //引入vue-i18n组件
import { Locale } from 'vant'
import messages from './index'
const lang = localStorage.getItem('lang') || 'fa_ir'
import enUS from 'vant/es/locale/lang/en-US';
import zhCN from 'vant/lib/locale/lang/zh-CN'
import thTH from 'vant/lib/locale/lang/th-TH'
import ptBR from 'vant/lib/locale/lang/pt-BR'
import esES from 'vant/lib/locale/lang/es-ES'
import bnBD from 'vant/lib/locale/lang/bn-BD'
import viVN from 'vant/lib/locale/lang/vi-VN'
import faIR from 'vant/lib/locale/lang/fa-IR'
import roRO from 'vant/lib/locale/lang/ro-RO'
// const language = (
//   (navigator.language ? navigator.language : navigator.userLanguage) || "zh_cn"
// ).toLowerCase();
const i18n = createI18n({
  fallbackLocale: 'zh_cn',
  globalInjection:true,
  legacy: false, // you must specify 'legacy: false' option
  locale: lang,
  messages,
});
  // 更新vant组件库本身的语言变化，支持国际化
  function vantLocales (language) {
    if (language === 'en_es') {
      Locale.use(language, enUS)
    } else if (language === 'zh_cn') {
      Locale.use(language, zhCN)
    } else if (language === 'tw_tw') {
      Locale.use(language, thTH)
    } else if (language === 'pt_br') {
      Locale.use(language, ptBR)
    } else if (language === 'es_mx') {
      Locale.use(language, esES)
    } else if (language === 'bn_bd') {
      Locale.use(language, bnBD)
    } else if (language === 'iv_vn') {
      Locale.use(language, viVN)
    } else if (language === 'fa_ir') {
      Locale.use(language, faIR)
    } else if (language === 'rom') {
      Locale.use(language, roRO)
    }
  }

export {i18n,vantLocales}