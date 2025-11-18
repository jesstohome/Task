<template>
  <div class="viewport-outer" :class="{ 'is-scaled': isScaled }" :style="{ '--current-scale': scale }">
    <div class="viewport-scaler" :style="scalerStyle">
      <div class="app-content" :style="{ width: `${innerWidth}px` }">
        <my-scroll>
          <router-view />
        </my-scroll>
      </div>
    </div>
    <!-- ⭐ Footer 必须放在这里，在 viewport-scaler 外面 -->
    <Footer v-if="showFooter" />
  </div>
</template>

<script>
import { common_parameters } from '@/api/login/index'
import { useI18n } from 'vue-i18n'
import store from '@/store/index'
import { vantLocales } from '@/i18n/i18n'
import myScroll from './components/scroll.vue'
import Footer from './components/footer.vue' // ⭐ 引入 footer
import { useRoute } from 'vue-router'
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'

export default {
  components: { myScroll, Footer }, // ⭐ 注册
  setup () {
    const { locale } = useI18n()
    const route = useRoute()
    const innerWidth = ref(window.innerWidth)
    const innerHeight = ref(window.innerHeight)
    const targetWidth = ref(1000)
    const scale = ref(1)

    const showFooter = computed(() => {
      const noFooterPages = ['login', 'register', 'service','level','libao','detail']
      return !noFooterPages.includes(route.name)
    })

    const computeScale = () => {
      const w = window.innerWidth
      const h = window.innerHeight
      innerWidth.value = w
      innerHeight.value = h
      scale.value = w > targetWidth.value ? targetWidth.value / w : 1
    }

    onMounted(() => {
      computeScale()
      window.addEventListener('resize', computeScale)
    })
    onBeforeUnmount(() => {
      window.removeEventListener('resize', computeScale)
    })

    const scalerStyle = computed(() => ({
      transform: `scale(${scale.value})`,
      transformOrigin: 'top center',
      width: `${innerWidth.value}px`,
      height: `${innerHeight.value / (scale.value || 1)}px`,
      '--current-scale': scale.value // 暴露scale值给CSS使用
    }))

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

    const isScaled = computed(() => scale.value < 1)
    return {
      innerWidth,
      scalerStyle,
      isScaled,
      showFooter,
      scale // 将scale暴露给模板使用
    }
  }
}
</script>

<style lang="scss">
.viewport-outer{
  display: flex;
  justify-content: center;
  width: 100%;
  height: 100vh;
  overflow: auto;
  -webkit-overflow-scrolling: touch;
  position: relative;
}
.viewport-scaler{
  will-change: transform;
}
.app-content{
  box-sizing: border-box;
  min-height: 100%;
}
.app-content > *{
  height: 100%;
  min-height: 100%;
}

.viewport-outer.is-scaled .home_box{
  height: 100% !important;
  min-height: 100% !important;
}

/* ⭐ Footer 样式 - 固定在视口底部 */
.viewport-outer > .footer{
  position: fixed !important;
  left: 50% !important;
  bottom: 0 !important;
  z-index: 9999 !important;
  background: #fff !important;
  box-shadow: 0 -2px 8px rgba(187, 187, 187, 0.3) !important;
  transform-origin: center bottom !important;
}

/* 未缩放时（手机端） */
.viewport-outer:not(.is-scaled) > .footer{
  width: 100% !important;
  max-width: 1000px !important;
  transform: translateX(-50%) !important;
}

/* 缩放时（PC端） */
.viewport-outer.is-scaled > .footer{
  /* Footer需要与主内容等宽
     主内容实际宽度是innerWidth，缩放后视觉宽度是1000px
     所以Footer原始宽度应该是innerWidth，缩放scale后视觉是1000px */
  width: 100vw !important;
  max-width: 100vw !important;
  transform: translateX(-50%) scale(var(--current-scale, 1)) !important;
}

#app {
  font-family:"PingFang SC,Helvetica Neue,Helvetica,Arial,Hiragino Sans GB,Heiti SC,Microsoft YaHei,WenQuanYi Micro Hei,sans-serif"!important;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: $textColor;
  height: 100vh;
  /* iOS Safari 优化 */
  position: fixed;
  width: 100%;
  overflow: hidden;
  -webkit-user-select: none;
  user-select: none;
  /* 防止 iOS 上的双击延迟 */
  touch-action: pan-y;
}
</style>