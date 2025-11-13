<template>
        <my-scroll>
          <router-view />
        </my-scroll>
    
    <!-- ⭐ Footer 必须放在这里，在 viewport-scaler 外面 -->
    <Footer v-if="showFooter" />
</template>

<script>
import { common_parameters } from '@/api/login/index'
import { useI18n } from 'vue-i18n'
import store from '@/store/index'
import { vantLocales } from '@/i18n/i18n'
import myScroll from './components/scroll.vue'
import Footer from './components/footer.vue' // ⭐ 引入 footer
import { useRoute } from 'vue-router'
import { computed } from 'vue'

export default {
  components: { myScroll, Footer }, // ⭐ 注册
  setup () {
    const { locale } = useI18n()
    const route = useRoute()

    const showFooter = computed(() => {
      const noFooterPages = ['login', 'register', 'service','level','libao','detail']
      return !noFooterPages.includes(route.name)
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


    return {showFooter}
  }
}
</script>

<style lang="scss">

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