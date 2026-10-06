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
      <TabNav v-if="showTabNav" />
      <div class="app-content" :class="{ 'has-nav': showNavBar, 'has-tabnav': showTabNav }">
        <my-scroll>
          <router-view />
        </my-scroll>
      </div>
    </div>
  </div>
  <!-- 页面自带客服图标：首屏立即显示，点击打开 Libredesk 聊天窗 -->
  <div class="kf-launcher" :style="launcherStyle" @click="onLauncherClick">
    <img class="kf-launcher__logo" :src="launcherLogo" alt="客服" />
    <div v-if="unreadCount > 0 && !chatVisible" class="kf-launcher__badge">{{ unreadCount > 99 ? '99+' : unreadCount }}</div>
  </div>
</template>

<script>
import { common_parameters } from '@/api/login/index'
import { useI18n } from 'vue-i18n'
import store from '@/store/index'
import { vantLocales } from '@/i18n/i18n'
import myScroll from './components/scroll.vue'
import NavBar from './components/navbar.vue'
import TabNav from './components/tabnav.vue'
import { useRoute } from 'vue-router'
import { computed, onMounted, ref, watch } from 'vue'

export default {
  components: { myScroll, NavBar, TabNav },
  setup () {
    const { locale } = useI18n()
    const route = useRoute()

    const showNavBar = computed(() => {
      const noNavPages = ['login', 'register']
      return !noNavPages.includes(route.name)
    })

    const showTabNav = computed(() => {
      const tabPages = ['home', 'obj', 'order', 'self', 'detail']
      return tabPages.includes(route.name)
    })

    const setRem = () => {
      document.documentElement.style.fontSize = 18 + 'px'
    }

    const LIBREDESK_BASE_URL = 'https://chat.robothub.shop'
    const LIBREDESK_INBOX_ID = 'ac32a3db-42a5-4561-a347-7230152593a7'
    const LAUNCHER_DEFAULT_COLOR = '#8c00ff'
    const LAUNCHER_DEFAULT_LOGO = `${LIBREDESK_BASE_URL}/static/public/launcher-logo.png`
    const LAUNCHER_SETTINGS_KEY = 'libredesk_launcher_settings'

    const libredeskConfig = (userinfo) => ({
      baseURL: LIBREDESK_BASE_URL,
      inboxID: LIBREDESK_INBOX_ID,
      visitorName: (userinfo && userinfo.username) || '',
      visitorId: userinfo && userinfo.id ? String(userinfo.id) : '',
      visitorPhone: (userinfo && userinfo.tel) || '',
      visitorEmail: '',
      // 插件自带图标要等设置接口和聊天窗口都加载完才显示，太慢，隐藏它，改用页面自带的即时图标
      hideLauncher: true
    })

    // 页面自带客服图标：首屏即渲染，不等插件
    const launcherLogo = ref(LAUNCHER_DEFAULT_LOGO)
    const launcherStyle = ref({ bottom: '20px', right: '20px', backgroundColor: LAUNCHER_DEFAULT_COLOR })
    const unreadCount = ref(0)
    const chatVisible = ref(false)
    const pendingOpen = ref(false)

    const applyLauncherSettings = (data) => {
      const launcher = (data && data.launcher) || {}
      const spacing = launcher.spacing || {}
      const side = launcher.position === 'left' ? 'left' : 'right'
      launcherStyle.value = {
        bottom: `${spacing.bottom != null ? spacing.bottom : 20}px`,
        backgroundColor: launcher.color || LAUNCHER_DEFAULT_COLOR,
        [side]: `${spacing.side != null ? spacing.side : 20}px`
      }
      if (launcher.logo_url) launcherLogo.value = launcher.logo_url
    }

    // 拉取图标配置（颜色/logo/位置），localStorage 缓存 24 小时，先用默认值渲染
    const loadLauncherSettings = () => {
      try {
        const cached = JSON.parse(localStorage.getItem(LAUNCHER_SETTINGS_KEY))
        if (cached && cached.data && Date.now() - cached.ts < 24 * 60 * 60 * 1000) {
          applyLauncherSettings(cached.data)
        }
      } catch (e) {
        localStorage.removeItem(LAUNCHER_SETTINGS_KEY)
      }
      fetch(`${LIBREDESK_BASE_URL}/api/v1/widget/chat/settings/launcher?inbox_id=${LIBREDESK_INBOX_ID}`)
        .then(res => res.json())
        .then(result => {
          if (result.status === 'success') {
            localStorage.setItem(LAUNCHER_SETTINGS_KEY, JSON.stringify({ data: result.data, ts: Date.now() }))
            applyLauncherSettings(result.data)
          }
        })
        .catch(() => {})
    }

    const onLauncherClick = () => {
      const widget = window.Libredesk
      if (widget && typeof widget.toggle === 'function' && widget.iframe) {
        widget.toggle()
      } else {
        // 插件还没初始化完，记住意图，就绪后自动打开
        pendingOpen.value = true
      }
    }

    // 加载 Libredesk 客服插件（Settings 必须在 widget.js 加载前设置）
    const loadLibredesk = () => {
      window.LibredeskSettings = libredeskConfig(store.state.userinfo)
      const script = document.createElement('script')
      script.src = `${LIBREDESK_BASE_URL}/widget.js`
      script.async = true
      script.onload = () => {
        // 等插件的 iframe 创建完成（此时聊天窗口已就绪）再挂接回调
        let tries = 0
        const hook = () => {
          const widget = window.Libredesk
          if (widget && typeof widget.toggle === 'function' && widget.iframe) {
            widget.onUnreadCountChange(count => { unreadCount.value = count })
            widget.onShow(() => { chatVisible.value = true })
            widget.onHide(() => { chatVisible.value = false })
            if (pendingOpen.value) {
              pendingOpen.value = false
              widget.toggle()
            }
            return
          }
          if (tries++ < 50) setTimeout(hook, 200)
        }
        hook()
      }
      document.body.appendChild(script)
    }

    // 在 setup 阶段就加载，不等待组件挂载，配合 index.html 的 preload 让图标随页面一起出现
    loadLibredesk()
    loadLauncherSettings()

    onMounted(() => {
      setRem()
    })

    // 监听用户登录，更新 Libredesk 访客身份
    watch(() => store.state.userinfo, (newVal) => {
      if (newVal && newVal.id) {
        window.LibredeskSettings = libredeskConfig(newVal)
      }
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
      showNavBar,
      showTabNav,
      launcherLogo,
      launcherStyle,
      unreadCount,
      chatVisible,
      onLauncherClick,
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

.viewport-container > .tabnav{
  position: fixed;
  left: 50%;
  transform: translateX(-50%);
  top: 100px;
  width: 100%;
  max-width: 1000PX;
  z-index: 9998;
  background: rgba(10, 10, 15, 0.92);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

.app-content{
  flex: 1;
  width: 100%;
  min-height: 0;
  box-sizing: border-box;
  overflow: hidden;
}

.app-content.has-nav{
  padding-top: 100px;
}

.app-content.has-tabnav{
  padding-top: 190px;
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

/* 页面自带客服图标（替代插件延迟加载的图标，样式与插件保持一致）
   kf-launcher 已加入 postcss 的 selectorBlackList，px 不会被转成 rem，与插件内联样式保持相同真实像素 */
.kf-launcher{
  position: fixed;
  z-index: 9999;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  box-shadow: 0 1px 4px rgba(9, 14, 21, 0.45), 0 3px 18px rgba(9, 14, 21, 0.55);
  transition: transform 0.3s ease;
  -webkit-tap-highlight-color: transparent;
  &:active{
    transform: scale(0.9);
  }
  @media (max-width: 33.3333rem){
    width: 50px;
    height: 50px;
  }
}

.kf-launcher__logo{
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}

.kf-launcher__badge{
  position: absolute;
  top: -5px;
  right: -5px;
  background-color: #ef4444;
  color: #fff;
  border-radius: 50%;
  min-width: 20px;
  height: 20px;
  padding: 0 4px;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 12px;
  font-weight: bold;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  border: 2px solid #fff;
  box-sizing: border-box;
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
