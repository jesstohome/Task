import zh from './lang/zh'
import en from './lang/en'
import tw from './lang/tw'
import br from './lang/br'
import es from './lang/es'
import bn from './lang/bn'
import vn from './lang/vn'
import fa from './lang/fa'
import ro from './lang/ro'
// // 引入英文语言包
import enUS from 'vant/es/locale/lang/en-US';
import zhCN from 'vant/lib/locale/lang/zh-CN'
import thTH from 'vant/lib/locale/lang/th-TH'
import ptBR from 'vant/lib/locale/lang/pt-BR'
// 西班牙语
import esES from 'vant/lib/locale/lang/es-ES'
// 西班牙语
import bnBD from 'vant/lib/locale/lang/bn-BD'
import viVN from 'vant/lib/locale/lang/vi-VN'
import faIR from 'vant/lib/locale/lang/fa-IR'
import roRO from 'vant/lib/locale/lang/ro-RO'

export default {
    'zh_cn': {...zh,...zhCN},
    'en_es': {...en,...enUS},
    'tw_tw': {...tw,...thTH},
    'pt_br': {...br,...ptBR},
    'es_mx': {...es,...esES},
    'bn_bd': {...bn,...bnBD},
    'iv_vn': {...vn,...viVN},
    'fa_ir': {...fa,...faIR},
    'rom': {...ro,...roRO},
}