import en from './lang/en'
import fr from './lang/fr'
import de from './lang/de'
import es from './lang/es'
import br from './lang/br'
import it from './lang/it'

import enUS from 'vant/es/locale/lang/en-US'
import frFR from 'vant/es/locale/lang/fr-FR'
import deDE from 'vant/es/locale/lang/de-DE'
import esES from 'vant/es/locale/lang/es-ES'
import ptBR from 'vant/es/locale/lang/pt-BR'
import itIT from 'vant/es/locale/lang/it-IT'

export default {
    // 英语 - English
    'en_es': {...en, ...enUS},
    // 法语 - French
    'tw_tw': {...fr, ...frFR},
    // 德语 - German
    'hy_hy': {...de, ...deDE},
    // 西班牙语 - Spanish
    'es_mx': {...es, ...esES},
    // 葡萄牙语 - Portuguese
    'pt_br': {...br, ...ptBR},
    // 意大利语 - Italian
    'rus_rus': {...it, ...itIT},
}
