<template>
  <div class="home" :class="$route.name">
    <van-nav-bar :title="title" :left-arrow="leftArrow" @click-left="$router.go(-1)" v-if="$route.name === 'login'">
        <template #right>
            <template v-if="$route.name === 'login'">
                <img @click="$router.push({path: '/service'})" :src="require('@/assets/images/service.png')" class="service-icon" alt="">
                <img @click="setlang()" :src="require('@/assets/images/self/lang.png')" class="lang-icon" alt="">
            </template>
        </template>
    </van-nav-bar>

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

    <img :src="logo" class="logo" alt="" :class="!leftArrow && 'lo'" width="80">
    <div class="title" v-if="$route.name === 'login'">{{ $t('msg.login_now') }}</div>
    <div class="title" v-else>{{ $t('msg.register_now') }}</div>
    <van-dialog v-model:show="show" :showConfirmButton="false">
      <div class="lang_box">
        <img :src="require('@/assets/images/register/lang_bg.png')" class="lang_bg" />
        <div class="content">
            <img :src="require('@/assets/images/register/qiu.png')" class="qiu" />
            <div class="langs">
              <span class="li" :class="langcheck==item.link && 'check'" v-for="(item,index) in langs" :key="index"  @click="handSeletlanguages(item)">
                <img :src="item.image_url" class="img" alt="">
                <span class="text">{{item.name}}</span>
              </span>
            </div>
            <div class="btn">
              <van-button round block type="primary" @click="submitLang">
                {{$t('msg.nowQh')}}
              </van-button>
            </div>
        </div>
      </div>
    </van-dialog>
  </div>
</template>

<script>
import { ref, getCurrentInstance,watch } from 'vue';
import { useI18n } from 'vue-i18n'
import store from '../../store/index'
import { vantLocales } from '@/i18n/i18n'
import langVue from '@/components/lang.vue'
export default {
  components: {langVue},
  name: 'HomeView',
  props: {
    hideLang: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: false
    },
    leftArrow: {
      type: Boolean,
      default: false
    },
  },
  setup(){
    const {proxy} = getCurrentInstance()
    const { locale,t } = useI18n()
    const show = ref(false);
    const langcheck = ref('')
    const langImg = ref('')
    const logo = ref(store.state.baseInfo?.site_icon)
    const app_name = ref(store.state.baseInfo?.app_name)
    langcheck.value = store.state.lang
    langImg.value = store.state.langImg
    const langs = ref(store.state.baseInfo?.languageList)
    const handSeletlanguages = (row) => {
      langcheck.value = row.link
      langImg.value = row.image_url
    }
    const submitLang = () => {
      locale.value = langcheck.value
      store.dispatch('changelang',langcheck.value)
      store.dispatch('changelangImg',langImg.value)
      show.value = false
      console.log(proxy)
      proxy.$Message({ type: 'success', message: t('msg.switch_lang_success') });
    }

    // 语言选择底部弹出层
    const showLangPopup = ref(false)
    const currentLang = ref(store.state.lang || 'en_es')
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
    watch(() => store.state.baseInfo,(newVal)=>{
      logo.value = newVal?.site_icon
      langs.value = (newVal?.languageList) || []
    }, { deep: true })

    return {show,langs,handSeletlanguages,langcheck,submitLang,logo,app_name,showLangPopup,currentLang,langOptions,setlang,selectLang}
  }
}
</script>

<style scoped lang="scss">
.home{
  position: relative;
  padding-top: calc(var(--van-nav-bar-height) + 10px);
  
  &.login {
    background-color: white;
    .service-icon {
      width: 60px;
      height: 60px;
      vertical-align: middle;
      margin-right: 34px;
    }
    .lang-icon {
      width: 50px;
      height: 50px;
      vertical-align: middle;
      cursor: pointer;
    }
  }
  
  /* 当路由为 register 时，只把 nav-bar 设为黑色背景，避免整页变黑 */
  &.register {
    :deep(.van-nav-bar) {
      background-color: #000;
    }
  }
        :deep(.van-nav-bar){
            // position: fixed !important;
            // top: 0;
            color: #333;
            padding: 10px 0;
            width: 100%;
            z-index: 3;
            &::after{
              border-bottom-width: 0;
            }
            .van-nav-bar__left{
                .van-icon{
                    color: #fff;
                }
            }
            .van-nav-bar__title{
                color: #fff;
                // font-weight: 600;
                font-size: 32px;
            }
        }
}
  .bg{
    width: 100%;
  }
  .logo{
    width: 435px;
    display: block;
    margin: 50px auto 10px;
    position: relative;
    z-index: 2;
    &.lo {
      margin-top: 100px;
    }
  }
  .title{
    font-size: 42px;
    width: 60%;
    margin: 80px auto;
    margin-bottom: 50px;
    font-weight: 900;
  }
  .lang_box{
    width: 100%;
    position: relative;
    padding-top: 80px;
    .lang_bg{
      width: 100%;
      position: absolute;
      top: 0;
      left: 0;
    }
    .content{
      position: relative;
      z-index: 1;
      .qiu{
        width: 175px;
        border-radius: 50%;
        box-shadow: $shadow;
        margin-bottom: 6px;
      }
      .langs{
        margin-bottom: 15px;
        .li{
          padding: 24px 112px;
          display: block;
          text-align: left;
          margin-bottom: 10px;
          &.check{
            box-shadow: $shadow;
          }
          .img{
            width: 80px;
            margin-right: 34px;
            vertical-align: middle;
          }
          .text{
            font-size: 26px;
            color: #666;
          }
        }
      }
      .btn{
        padding: 50px 54px 50px;
      }
    }
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
