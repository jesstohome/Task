<template>
  <div class="navbar">
    <div class="navbar-inner">
      <div class="navbar-left" @click="$router.push('/home')">
        <img :src="logoSrc" class="navbar-logo" alt="logo" />
      </div>
      <div class="navbar-right" @click="showMenu = true">
        <div class="hamburger">
          <span></span>
          <span></span>
          <span></span>
        </div>
      </div>
    </div>

    <van-popup
      v-model:show="showMenu"
      position="right"
      :style="{ width: '82%', height: '100vh' }"
      teleport="body"
      :z-index="100000"
    >
      <div class="menu-wrapper">
        <div class="menu-header">
          <img :src="logoSrc" class="menu-logo" alt="logo" />
          <div class="menu-close" @click="showMenu = false">
            <span></span>
            <span></span>
          </div>
        </div>

        <div class="menu-list">
          <div
            class="menu-item"
            :class="{ active: currentRoute === '/home' }"
            @click="navigate('/home')"
          >
            <span class="menu-icon">🏠</span>
            <span>{{ $t('msg.home') }}</span>
          </div>
          <div
            class="menu-item"
            :class="{ active: currentRoute === '/obj' }"
            @click="navigate('/obj')"
          >
            <span class="menu-icon">⚡</span>
            <span>Ad Match</span>
          </div>
          <div
            class="menu-item"
            :class="{ active: currentRoute === '/order' }"
            @click="navigate('/order')"
          >
            <span class="menu-icon">📋</span>
            <span>{{ $t('msg.order') }}</span>
          </div>
          <div
            class="menu-item"
            :class="{ active: currentRoute === '/self' }"
            @click="navigate('/self')"
          >
            <span class="menu-icon">👤</span>
            <span>{{ $t('msg.self') }}</span>
          </div>

          <div class="menu-divider"></div>

          <div class="menu-item" @click="openWFP">
            <span class="menu-icon">🌐</span>
            <span>WFP</span>
          </div>
          <div class="menu-item" @click="toContent(4, $t('msg.dlhz'))">
            <span class="menu-icon">🤝</span>
            <span>{{ $t('msg.dlhz') }}</span>
          </div>
          <div class="menu-item" @click="navigate('/drawing')">
            <span class="menu-icon">💳</span>
            <span>{{ $t('msg.tixian') }}</span>
          </div>
          <div class="menu-item" @click="navigate('/chongzhi')">
            <span class="menu-icon">💰</span>
            <span>{{ $t('msg.chongzhi') }}</span>
          </div>
          <div class="menu-item" @click="toContent(3, $t('msg.gzms'))">
            <span class="menu-icon">📖</span>
            <span>{{ $t('msg.gzms') }}</span>
          </div>
          <div class="menu-item" @click="toContent(10, 'Event')">
            <span class="menu-icon">📅</span>
            <span>Event</span>
          </div>
          <div class="menu-item" @click="toContent(12, $t('msg.qyzz'))">
            <span class="menu-icon">🏢</span>
            <span>{{ $t('msg.qyzz') }}</span>
          </div>
          <div class="menu-item" @click="toContent(2, $t('msg.gsjj'))">
            <span class="menu-icon">ℹ️</span>
            <span>{{ $t('msg.gsjj') }}</span>
          </div>
          <div class="menu-item" @click="toContent(7, 'AML')">
            <span class="menu-icon">🛡️</span>
            <span>AML</span>
          </div>

          <div class="menu-divider"></div>

          <div class="menu-item" @click="navigate('/tel')">
            <span class="menu-icon">📞</span>
            <span>{{ $t('msg.tel') }}</span>
          </div>
        </div>

        <!-- <div class="menu-footer">
          <langVue />
        </div> -->
      </div>
    </van-popup>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import store from '@/store/index'
import langVue from '@/components/lang.vue'

export default {
  components: { langVue },
  setup() {
    const router = useRouter()
    const route = useRoute()
    const showMenu = ref(false)

    const logoSrc = require('@/assets/images/shiftlogo-header.svg')
    const logo = computed(() => store.state.baseInfo?.site_icon)
    const currentRoute = computed(() => route.path)

    const navigate = (path) => {
      showMenu.value = false
      if (path === '/home') store.dispatch('changefooCheck', 'home')
      else if (path === '/obj') store.dispatch('changefooCheck', 'obj')
      else if (path === '/order') store.dispatch('changefooCheck', 'order')
      else if (path === '/self') store.dispatch('changefooCheck', 'self')
      router.push(path)
    }

    const toContent = (id, title) => {
      showMenu.value = false
      router.push('/content?id=' + id + '&title=' + title)
    }

    const openWFP = () => {
      showMenu.value = false
      window.location.href = 'https://www.wfp.org/'
    }

    return { showMenu, logoSrc, logo, currentRoute, navigate, toContent, openWFP }
  }
}
</script>

<style lang="scss" scoped>
.navbar {
  width: 100%;
  z-index: 9999;
}

.navbar-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 28px;
  height: 100px;
  box-sizing: border-box;
}

.navbar-left {
  cursor: pointer;
  display: flex;
  align-items: center;
}

.navbar-logo {
  height: 52px;
  width: auto;
  max-width: 220px;
  object-fit: contain;
}

.navbar-right {
  cursor: pointer;
  padding: 8px;
}

.hamburger {
  display: flex;
  flex-direction: column;
  gap: 6px;
  width: 44px;

  span {
    display: block;
    height: 3px;
    background: rgba(255, 255, 255, 0.85);
    border-radius: 2px;
    transition: all 0.25s ease;

    &:nth-child(2) {
      width: 70%;
      margin-left: auto;
    }

    &:nth-child(3) {
      width: 50%;
      margin-left: auto;
    }
  }

  &:active span {
    background: $theme;
  }
}

/* --- Slide Menu --- */
.menu-wrapper {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  background: linear-gradient(170deg, #0d0d1f 0%, #0f1025 40%, #0a0c1a 100%);
}

.menu-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 28px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);

  .menu-logo {
    height: 38px;
    width: auto;
  }
}

.menu-close {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;

  span {
    position: absolute;
    display: block;
    width: 24px;
    height: 2px;
    background: rgba(255, 255, 255, 0.65);
    border-radius: 1px;

    &:nth-child(1) { transform: rotate(45deg); }
    &:nth-child(2) { transform: rotate(-45deg); }
  }
}

.menu-list {
  flex: 1;
  overflow-y: auto;
  padding: 12px 0;
}

.menu-item {
  display: flex;
  align-items: center;
  padding: 22px 32px;
  font-size: 30px;
  color: rgba(255, 255, 255, 0.75);
  cursor: pointer;
  transition: all 0.15s ease;

  .menu-icon {
    width: 44px;
    font-size: 28px;
    margin-right: 18px;
    text-align: center;
    flex-shrink: 0;
  }

  &:active {
    background: rgba(153, 26, 255, 0.12);
    color: #fff;
  }

  &.active {
    color: $theme;
    background: rgba(153, 26, 255, 0.08);
    border-right: 3px solid $theme;
  }
}

.menu-divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.06);
  margin: 12px 32px;
}

.menu-footer {
  padding: 20px 32px 40px;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}
</style>
