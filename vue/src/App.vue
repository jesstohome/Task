<template>
  <!-- 紫色光束背景装饰层 -->
  <div class="bg-decorations">
    <div class="bg-beam bg-beam--1"></div>
    <div class="bg-beam bg-beam--2"></div>
    <div class="bg-glow bg-glow--tl"></div>
    <div class="bg-glow bg-glow--br"></div>
    <div class="bg-line bg-line--top"></div>
    <div class="bg-line bg-line--bottom"></div>
  </div>
  <div class="viewport-outer">
    <div class="viewport-container">
      <NavBar v-if="showNavBar" />
      <div class="app-content">
        <my-scroll>
          <router-view />
        </my-scroll>
      </div>
    </div>
  </div>
</template>

<script>
import { common_parameters } from '@/api/login/index'
import { useI18n } from 'vue-i18n'
import store from '@/store/index'
import { vantLocales } from '@/i18n/i18n'
import myScroll from './components/scroll.vue'
import NavBar from './components/navbar.vue'
import { useRoute } from 'vue-router'
import { computed, onMounted } from 'vue'

export default {
  components: { myScroll, NavBar },
  setup () {
    const { locale } = useI18n()
    const route = useRoute()

    const showNavBar = computed(() => {
      const noNavPages = ['login', 'register']
      return !noNavPages.includes(route.name)
    })

    const setRem = () => {
      document.documentElement.style.fontSize = 18 + 'px'
    }

    onMounted(() => {
      setRem()
    })

    const changeFavicon = link => {
      let $favicon = document.querySelector('link[rel="icon"]')
      if ($favicon !== null) {
        $favicon.href = link
      } else {
        $favicon = document.createElement('link')
        $favicon.rel = 'icon'
        $favicon.href = link
        document.head.appendChild($favicon)
      }
    }

    Promise.resolve(common_parameters()).then(res => {
      if (res.code === 0) {
        const info = JSON.parse(JSON.stringify(res.data))
        locale.value = info.language
        let languageList = info.languageList
        let json = languageList.find(rr => rr.link == info.language)
        let langImg = json && json.image_url
        store.dispatch('changelang', info.language)
        store.dispatch('changelangImg', langImg)
        store.dispatch('changebaseInfo', info)
        changeFavicon(info.site_icon)
        document.title = info.app_name
        vantLocales(info.language)
      }
    }).catch(err => {
      console.log(err)
    })

    return {
      showNavBar
    }
  }
}
</script>

<style lang="scss">
@import '@/assets/common.scss';
.viewport-outer{
  display: flex;
  justify-content: center;
  width: 100%;
  height: 100%;
  overflow: hidden;
  -webkit-overflow-scrolling: touch;
  position: relative;
}

.viewport-container{
  width: 100%;
  max-width: 1000PX;
  height: 100%;
  position: relative;
  display: flex;
  flex-direction: column;
}

.app-content{
  flex: 1;
  width: 100%;
  min-height: 0;
  box-sizing: border-box;
  overflow: hidden;
  padding-top: 100px;
}

.app-content > *{
  flex: 1;
  min-height: 0;
}

.viewport-container > .navbar{
  position: fixed;
  left: 50%;
  transform: translateX(-50%);
  top: 0;
  width: 100%;
  max-width: 1000PX;
  z-index: 9999;
  background: rgba(10, 10, 15, 0.92);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

#app {
  font-family:"PingFang SC,Helvetica Neue,Helvetica,Arial,Hiragino Sans GB,Heiti SC,Microsoft YaHei,WenQuanYi Micro Hei,sans-serif"!important;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: $textColor;
  height: 100vh;
  position: fixed;
  width: 100%;
  overflow: hidden;
  -webkit-user-select: none;
  user-select: none;
  touch-action: pan-y;
  background-color: $bg-primary;
  background: $bg-primary;
}

/* ── 紫色光束背景装饰层 ── */
.bg-decorations {
  position: fixed;
  inset: 0;
  pointer-events: none;
  z-index: 0;
  overflow: visible;
}

/* 左上角紫色光晕 */
.bg-glow--tl {
  position: absolute;
  top: -15%;
  left: -8%;
  width: 50%;
  height: 50%;
  background: radial-gradient(ellipse at 30% 30%, rgba(153, 26, 255, 0.10) 0%, rgba(120, 20, 220, 0.04) 40%, transparent 70%);
}

/* 右下角蓝色光晕 */
.bg-glow--br {
  position: absolute;
  bottom: -12%;
  right: -8%;
  width: 45%;
  height: 50%;
  background: radial-gradient(ellipse at 70% 70%, rgba(59, 130, 246, 0.08) 0%, rgba(30, 80, 200, 0.03) 45%, transparent 72%);
}

/* 斜向紫色光束 1 */
.bg-beam--1 {
  position: absolute;
  top: -5%;
  left: -5%;
  width: 35%;
  height: 120%;
  background: linear-gradient(
    135deg,
    transparent 30%,
    rgba(153, 26, 255, 0.03) 45%,
    rgba(153, 26, 255, 0.07) 50%,
    rgba(153, 26, 255, 0.03) 55%,
    transparent 70%
  );
  transform: rotate(-15deg);
}

/* 斜向紫色光束 2 */
.bg-beam--2 {
  position: absolute;
  top: 10%;
  right: -8%;
  width: 30%;
  height: 100%;
  background: linear-gradient(
    225deg,
    transparent 25%,
    rgba(120, 80, 220, 0.025) 45%,
    rgba(153, 26, 255, 0.055) 50%,
    rgba(120, 80, 220, 0.025) 55%,
    transparent 75%
  );
  transform: rotate(10deg);
}

/* 顶部横向紫色细线 */
.bg-line--top {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(
    90deg,
    transparent 0%,
    rgba(153, 26, 255, 0.15) 20%,
    rgba(153, 26, 255, 0.3) 50%,
    rgba(153, 26, 255, 0.15) 80%,
    transparent 100%
  );
}

/* 底部横向紫色细线 */
.bg-line--bottom {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(
    90deg,
    transparent 0%,
    rgba(153, 26, 255, 0.10) 30%,
    rgba(153, 26, 255, 0.20) 50%,
    rgba(153, 26, 255, 0.10) 70%,
    transparent 100%
  );
}
</style>
