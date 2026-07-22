<template>
  <div class="tabnav">
    <div
      v-for="tab in tabs"
      :key="tab.key"
      class="tabnav-item"
      :class="{ active: activeTab === tab.key }"
      @click="switchTab(tab.key)"
    >
      <span>{{ tab.label }}</span>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import store from '@/store/index'

export default {
  setup() {
    const router = useRouter()

    const activeTab = computed(() => store.state.fooCheck)

    const tabs = [
      { key: 'home', label: 'Home' },
      { key: 'obj', label: 'Program' },
      { key: 'order', label: 'Record' },
      { key: 'self', label: 'Profile' },
    ]

    const switchTab = (key) => {
      if (activeTab.value === key) return
      store.dispatch('changefooCheck', key)
      router.push('/' + key)
    }

    return { activeTab, tabs, switchTab }
  }
}
</script>

<style lang="scss" scoped>
.tabnav {
  display: flex;
  justify-content: space-around;
  gap: 16px;
  padding: 4px 20px 10px;
  background: $bg-primary;
}

.tabnav-item {
  padding: 12px 32px;
  border-radius: 50px;
  cursor: pointer;
  transition: all 0.2s ease;
  line-height: 42px;
  span {
    font-size: 28px;
    
    color: $textSecondary;
    font-weight: 500;
    white-space: nowrap;
    transition: color 0.2s;
  }

  &.active {
    background: $theme;

    span {
      color: #fff;
      font-weight: 700;
    }
  }

  &:not(.active):active {
    background: rgba(153, 26, 255, 0.1);

    span {
      color: #fff;
    }
  }
}
</style>
