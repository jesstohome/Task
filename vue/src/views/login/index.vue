<template>
  <div class="home" :class="$route.name">
    <van-nav-bar :title="title" :left-arrow="leftArrow" @click-left="$router.go(-1)">
        <template #right>
            <template v-if="$route.name === 'login'">
                <img @click="$router.push({path: '/service'})" :src="require('@/assets/images/service.png')" class="lang-icon" alt="">
                <lang-vue v-if="!hideLang" color='white'></lang-vue>
            </template>
            <template v-else>
                <lang-vue v-if="!hideLang" color='white'></lang-vue>
            </template>
        </template>
    </van-nav-bar>
    <img :src="logo" class="logo" alt="" :class="!leftArrow && 'lo'" width="80">
    <!-- <div class="title">{{app_name}}</div> -->

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
// import logo from '@/assets/images/news/logo.png'
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
    watch(() => store.state.baseInfo,(newVal)=>{
      logo.value = newVal?.site_icon
      langs.value = (newVal?.languageList) || []
    }, { deep: true })

    return {show,langs,handSeletlanguages,langcheck,submitLang,logo,app_name}
  }
}
</script>

<style scoped lang="scss">
.home{
  position: relative;
  padding-top: calc(var(--van-nav-bar-height) + 100px);
  
  &.login {
    background-color: white;
    .lang-icon {
      width: 60px;
      height: 60px;
      vertical-align: middle;
      margin-right: 20px;
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
    width: 535px;
    display: block;
    margin: 50px auto 10px;
    position: relative;
    z-index: 2;
    &.lo {
      margin-top: 180px;
    }
  }
  .title{
    font-size: 32px;
    width: 60%;
    margin: 0 auto;
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
</style>
