import { createI18n } from 'vue-i18n'
import { Locale } from 'vant'
import messages from './index'

// 默认语言为英语 en_es
const lang = localStorage.getItem('lang') || 'en_es'

import enUS from 'vant/es/locale/lang/en-US'
import frFR from 'vant/es/locale/lang/fr-FR'
import deDE from 'vant/es/locale/lang/de-DE'
import esES from 'vant/es/locale/lang/es-ES'
import ptBR from 'vant/es/locale/lang/pt-BR'
import itIT from 'vant/es/locale/lang/it-IT'

const i18n = createI18n({
  fallbackLocale: 'en_es', // 回退语言：英语
  globalInjection: true,
  legacy: false,
  locale: lang,
  messages,
})

/**
 * 切换 Vant 组件库的语言
 * @param {string} language 语言代码
 *   en_es   - 英语 English
 *   tw_tw   - 法语 French
 *   hy_hy   - 德语 German
 *   es_mx   - 西班牙语 Spanish
 *   pt_br   - 葡萄牙语 Portuguese
 *   rus_rus - 意大利语 Italian
 */
function vantLocales(language) {
  if (language === 'en_es') {
    Locale.use(language, enUS)       // 英语
  } else if (language === 'tw_tw') {
    Locale.use(language, frFR)       // 法语
  } else if (language === 'hy_hy') {
    Locale.use(language, deDE)       // 德语
  } else if (language === 'es_mx') {
    Locale.use(language, esES)       // 西班牙语
  } else if (language === 'pt_br') {
    Locale.use(language, ptBR)       // 葡萄牙语
  } else if (language === 'rus_rus') {
    Locale.use(language, itIT)       // 意大利语
  }
}

export { i18n, vantLocales }
