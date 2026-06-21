<template>
  <div class="homes">
    <login-top></login-top>
    <van-form @submit="onSubmit">
      <van-cell-group inset>
        <van-field
          :label="$t('msg.account')"
          class="zdy"
          name="tel"
          v-model="tel"
          input-align="right"
          :placeholder="$t('msg.account')"
        >
        </van-field>
        <van-field
          :label="$t('msg.pwd')"
          v-model="pwd"
          type="password"
          name="pwd"
          input-align="right"
          :placeholder="$t('msg.pwd')"
        />
      </van-cell-group>
      <div class="buttons">
        <van-button block round color="#991aff" native-type="submit">
          {{ $t('msg.login') }}
        </van-button>
        <div @click="$router.push({path: '/register'})" style="margin-top: 20px; text-align: center; font-size: 20px;text-decoration: underline; cursor: pointer;">
          {{ $t('msg.register') }}
        </div>
        <div @click="setlang()" style="margin-top: 20px; text-align: center; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
          {{ $t('msg.check_lang') }}
        </div>
<!--        <van-button round block plain  type="primary" @click="toDown()">
          {{$t('msg.appDown')}}
        </van-button> -->
        
      </div>
    </van-form>

    <!-- 语言选择底部弹出层 -->
    <van-popup
      v-model:show="showLangPopup"
      closeable
      position="bottom"
      round
      teleport="body"
      :style="{ height: '50vh' }">
      <div class="lang-popup">
        <div class="lang-popup-title">{{ $t('msg.check_lang') }}</div>
        <div class="lang-list">
          <div
            class="lang-item"
            :class="{ 'lang-item--active': currentLang === item.value }"
            v-for="(item, index) in langOptions"
            :key="index"
            @click="selectLang(item)">
            <span class="lang-item-name">{{ item.label }}</span>
            <van-icon v-if="currentLang === item.value" name="success" color="#991aff" />
          </div>
        </div>
      </div>
    </van-popup>

    <div class="footer-copyright">©2018-2026 AWISEE</div>
  </div>
  
</template>

<script>
import loginTop from './index.vue'
import { watch, ref, getCurrentInstance } from 'vue';
import store from '@/store/index'
import {login} from '@/api/login/index.js'
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n'
import { vantLocales } from '@/i18n/i18n'
export default {
  name: 'HomeView',
  components: {loginTop},
  setup() {
    const { push } = useRouter();
    const {proxy} = getCurrentInstance()
    const { t, locale } = useI18n()
    const baseInfo = ref(store.state.baseInfo)
    const option = ref((baseInfo.value?.area_code) || [])
    const userjs = ref(store.state.user)
    const area_code = ref(option.value.map(rr => {return {text: '+ '+rr, value: rr}}))
    const checked = ref(userjs.value?.checked || false);
    const tel = ref(userjs.value?.tel || '');
    const pwd = ref(userjs.value?.pwd || '');
    const qv = ref(userjs.value?.qv || area_code.value[0]?.value);

    // 语言选择
    const showLangPopup = ref(false)
    const currentLang = ref(store.state.lang || 'en_es') // 默认英语
    // 语言列表 — value 需与后端语言代码一致
    const langOptions = ref([
      { label: 'English', value: 'en_es' },       // 英语 English
      { label: 'Français', value: 'tw_tw' },      // 法语 French
      { label: 'Deutsch', value: 'hy_hy' },       // 德语 German
      { label: 'Español', value: 'es_mx' },       // 西班牙语 Spanish
      { label: 'Português', value: 'pt_br' },     // 葡萄牙语 Portuguese
      { label: 'Italiano', value: 'rus_rus' },    // 意大利语 Italian
    ])

    const setlang = () => {
      showLangPopup.value = true
    }

    const selectLang = (item) => {
      currentLang.value = item.value
      locale.value = item.value
      store.dispatch('changelang', item.value)
      vantLocales(item.value)
      showLangPopup.value = false
    }

    const toDown = () => {
      console.log(baseInfo.value.app_url)
      if(baseInfo.value.app_url){
        window.location.href=baseInfo.value.app_url
      }
    }

    localStorage.clear()

    const onSubmit = (values) => {
      const json = {...values}
      login(json).then(res => {
        console.log(res)
        if(res.code === 0) {
          store.dispatch('changetoken',res.token)
          store.dispatch('changeuserinfo',res.userinfo || {})
          proxy.$Message({ type: 'success', message: res.info});
          // 记住密码
          if (checked.value) {
            const useri = {...json,...{checked: checked.value}}
            store.dispatch('changeUser',useri)
          } else {
            store.dispatch('clearUser','')
          }
          push('/')
        } else {
          proxy.$Message({ type: 'error', message: res.info});
        }

      })
    };
    watch(() => store.state.baseInfo,(newVal)=>{
      console.log(newVal)
      baseInfo.value = {...newVal}
      option.value = (newVal?.area_code) || []
      area_code.value = option.value.map(rr => {return {text: '+ '+rr, value: rr}})
      qv.value = area_code.value[0]?.value
      console.log(area_code.value)
    }, { deep: true })



    return {
      checked,
      tel,
      pwd,
      onSubmit,
      area_code,
      qv,
      toDown,
      showLangPopup, currentLang, langOptions, setlang, selectLang
    };
  }
}
</script>

<style scoped lang="scss">
@import '@/styles/theme.scss';
.homes{
font-weight: 900;
min-height: 100vh;
display: flex;
flex-direction: column;
  //background-image: linear-gradient(180deg,#0a3cff,#0bd3ff);
    :deep(.van-form){
        flex: 1;
        padding: 40px 0 0;
        .van-cell-group--inset{
            padding: 0 60px;
            background-color: initial;
        }
        .van-ellipsis{
          color: #000;
        }
        .van-cell{
            padding: 30px 10px;
            border-bottom: 1px solid #1a7ae7;
            background-color: initial;
            &.zdy{
              .van-field__left-icon{
                margin-right: 30px;
              }
            }
            .van-field__left-icon{
                margin-right: 90px;
                .van-icon__image{
                    height: 42px;
                    width: auto;
                }
                .icon{
                    height: 42px;
                    width: auto;
                    vertical-align:middle;
                }
                display: flex;
                .van-dropdown-menu{
                  .van-dropdown-menu__bar{
                    height: auto;
                    background: none;
                    box-shadow: none;
                  }
                  .van-cell{
                    padding: 30px 80px;
                  }
                }
            }
            .van-field__control{
                font-size: 30px;
            }
            &::after {
                display: none;
            }
        }
        .van-checkbox{
            margin: 30px 0 60px 0;
            .van-checkbox__icon{
                font-size: 50px;
                margin-right: 80px;
                &.van-checkbox__icon--checked .van-icon{
                    background-color:$theme;
                    border-color:$theme;
                }
            }
            .van-checkbox__label{
                font-size: 30px;
            }
        }
        .buttons{
            padding: 0 76px;
            margin-bottom: auto;
            .van-button{
                font-size: 30px;
                padding: 26px 0;
                height: auto;
                margin-top: 35px;
                &+.van-button{
                  margin-top: 20px;
                }
            }
        }
    }
    :deep(.van-nav-bar){
        color: #fff;
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        .van-nav-bar__left{
                .van-icon{
                    color: #fff;
                    font-size: 30px;
                }
            }
            .van-nav-bar__content{
                height: 80px;
            }
            .van-nav-bar__title{
                color: #ffffff;
                font-weight: 600;
                font-size: 32px;
                line-height: 60px;
            }
    }
}

.footer-copyright {
    text-align: center;
    padding: 20px 0;
    width: 100%;
    font-size: 26px;
    font-weight: 500;
}

/* 语言弹出层 */
.lang-popup {
  padding: 20px 24px;
  height: 100%;
  display: flex;
  flex-direction: column;
}
.lang-popup-title {
  font-size: 34px;
  font-weight: 800;
  color: #1a1a2e;
  text-align: center;
  padding: 20px 0;
  flex-shrink: 0;
}
.lang-list {
  flex: 1;
  overflow-y: auto;
}
.lang-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 28px 20px;
  font-size: 30px;
  color: #333;
  border-bottom: 1px solid #f0f0f0;
  cursor: pointer;
  transition: background 0.15s;
  &:active {
    background: #f5f5f5;
  }
  &--active {
    color: #991aff;
    font-weight: 600;
  }
}
.lang-item-name {
  flex: 1;
}
</style>
