<template>
  <div class="viewport-outer">
    <div class="viewport-container">
      <div class="app-content">
        <my-scroll>
          <router-view />
        </my-scroll>
      </div>
      <Footer v-if="showFooter" />
    </div>
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
import { computed, onMounted } from 'vue'

export default {
  components: { myScroll, Footer },
  setup () {
    const { locale } = useI18n()
    const route = useRoute()

    const showFooter = computed(() => {
      const noFooterPages = ['login', 'register', 'service','level','libao','detail']
      return !noFooterPages.includes(route.name)
    })

    // 设置rem基准，让1rem始终等于设计稿中的1px/20
    // 在不同设备上保持字体和尺寸一致
    const setRem = () => {
      //取消整体尺寸自动缩放1
                    // const width = window.innerWidth
                    // // 限制最大宽度为1000px，超过1000px时rem基准不再增大
                    // const baseWidth = Math.min(width, 1000)
                    // // 设计稿750px，除以20得到37.5作为基准
                    // // 在1000px宽度时：1000/20 = 50
                    // const rem = baseWidth / 20
                    // document.documentElement.style.fontSize = rem + 'px'
      document.documentElement.style.fontSize = 18 + 'px'
    }

    onMounted(() => {
      setRem()

      //取消整体尺寸自动缩放2
                  //window.addEventListener('resize', setRem)
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
      showFooter
    }
  }
}
</script>

<style lang="scss">
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
  max-width: 1000PX; /* 最大宽度1000px */
  height: 100%;
  position: relative;
  display: flex;
  flex-direction: column;
}

.app-content{
  flex: 1;
  width: 100%;
  height: 100%;
  box-sizing: border-box;
  overflow: hidden;
}

.app-content > *{
  height: 100%;
}

/* ⭐ Footer 样式 - 固定在底部 */
.viewport-container > .footer{
  position: fixed;
  left: 50%;
  transform: translateX(-50%);
  bottom: 0;
  width: 100%;
  max-width: 1000PX; /* 与container一致 */
  z-index: 9999;
  background: #fff;
  box-shadow: 0 -2px 8px rgba(187, 187, 187, 0.3);
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