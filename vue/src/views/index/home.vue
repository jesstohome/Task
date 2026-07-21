<template>
  <div class="home-dark">

    <!-- ═══════════ Hero ═══════════ -->
    <section class="hero">
      <h1 class="hero-title">The pioneer in digital advertising.</h1>
      <p class="hero-desc">
        For over 10 years, shiftdigital has led the industry with innovative advertising solutions,
        serving thousands of users worldwide through our intelligent matching platform.
      </p>
    </section>

    <!-- ═══════════ Horizontal Scroll Cards ═══════════ -->
    <section class="cards-section">
      <h2 class="section-heading">Our Platform</h2>
      <div class="cards-scroll">
        <div class="cards-track">
          <div class="card" v-for="(img, idx) in imageList" :key="idx">
            <img :src="img" class="card-img" alt="" />
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════ Timeline Section ═══════════ -->
    <section class="timeline-section">
      <h2 class="section-heading">The shiftdigital Roadmap</h2>
      <div class="timeline-years">
        <div class="years-scroll">
          <span
            v-for="year in years"
            :key="year"
            class="year-item"
            :class="{ active: selectedYear === year }"
            @click="selectedYear = year"
          >{{ year }}</span>
        </div>
      </div>
      <div class="timeline-cards">
        <div class="tl-card" v-for="(evt, i) in filteredEvents" :key="i">
          <div class="tl-dot"></div>
          <div class="tl-line"></div>
          <div class="tl-content">
            <div class="tl-date">{{ evt.date }}</div>
            <div class="tl-text">{{ evt.text }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════ Careers CTA ═══════════ -->
    <section class="cta-section">
      <p>Learn more about shiftdigital and our advertising platform.</p>
      <button class="cta-btn" @click="$router.push('/obj')">Start Matching</button>
    </section>

    <!-- ═══════════ First-login Popup ═══════════ -->
    <van-popup
      v-model:show="showFirstLoginModal"
      position="center"
      overlay-class="first-login-overlay"
      :style="{ padding: '0', background: '#ffffff00' }"
      teleport="body"
      :z-index="100000"
    >
      <div class="first-login-card">
        <img :src="require('@/assets/images/home/tanchuang.png')" alt="" width="100%" height="100%" />
      </div>
      <div class="tanchuang_close" @click="closeFirstLoginModal">
        <van-icon name="close" />
      </div>
    </van-popup>

    <!-- ═══════════ Gift Package ═══════════ -->
    <GiftPackage v-model="showGift" />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import store from '@/store/index'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import GiftPackage from '@/components/gift/index.js'

export default {
  components: { GiftPackage },
  setup() {
    const { push } = useRouter()
    const { t } = useI18n()
    const showGift = ref(false)
    const showFirstLoginModal = ref(false)
    const selectedYear = ref(2020)
    const FIRST_LOGIN_FLAG_KEY = 'home_first_login_popup_shown'

    store.dispatch('changefooCheck', 'home')

    const imageList = ref([
      require('@/assets/images/h1.webp'),
      require('@/assets/images/h2.webp'),
      require('@/assets/images/h3.webp'),
    ])

    const years = Array.from({ length: 25 }, (_, i) => 2006 + i)

    const allEvents = [
      { year: 2008, date: 'July 2008', text: 'shiftdigital is born — pioneering intelligent ad matching technology.' },
      { year: 2012, date: 'March 2012', text: 'Launched our first AI-driven matching engine, serving 1,000+ users.' },
      { year: 2015, date: 'October 2015', text: 'Expanded to global markets, supporting multi-currency transactions.' },
      { year: 2018, date: 'January 2018', text: 'Reached 20,000 active users milestone with 99.9% platform uptime.' },
      { year: 2020, date: 'June 2020', text: 'Introduced compound order system and VIP membership tiers.' },
      { year: 2022, date: 'April 2022', text: 'Launched real-time K-line analytics and enhanced matching algorithms.' },
      { year: 2024, date: 'February 2024', text: 'Surpassed 65,000 active users across 8 global regions.' },
      { year: 2026, date: 'January 2026', text: 'Next-gen platform upgrade with improved AI matching and security.' },
      { year: 2028, date: '2028', text: 'Roadmap: Cross-chain integration and decentralized advertising.' },
      { year: 2030, date: '2030', text: 'Vision: Becoming the global leader in programmatic ad technology.' },
    ]

    const filteredEvents = computed(() => allEvents.filter(e => e.year <= selectedYear.value))

    const tryShowFirstLoginPopup = () => {
      if (!localStorage.getItem(FIRST_LOGIN_FLAG_KEY)) {
        showFirstLoginModal.value = true
      }
    }

    const closeFirstLoginModal = () => {
      showFirstLoginModal.value = false
      localStorage.setItem(FIRST_LOGIN_FLAG_KEY, '1')
    }

    onMounted(() => {
      showGift.value = true
      tryShowFirstLoginPopup()
    })

    return {
      showGift, showFirstLoginModal, closeFirstLoginModal,
      imageList, years, selectedYear, filteredEvents,
    }
  }
}
</script>

<style lang="scss" scoped>
@import '@/styles/theme.scss';

/* ═══════════ Hero ═══════════ */
.hero {
  padding: 48px 28px 40px;
  text-align: left;
}

.hero-title {
  font-size: 40px;
  font-weight: 900;
  color: #fff;
  line-height: 1.15;
  margin: 0 0 20px;
  letter-spacing: -0.3px;
}

.hero-desc {
  font-size: 26px;
  color: $textSecondary;
  line-height: 1.6;
  margin: 0;
}

/* ═══════════ Sections ═══════════ */
.section-heading {
  font-size: 32px;
  font-weight: 800;
  color: #fff;
  padding: 0 28px;
  margin: 0 0 24px;
  text-align: left;
}

/* ═══════════ Horizontal Scroll Cards ═══════════ */
.cards-section {
  padding: 24px 0 40px;
}

.cards-scroll {
  overflow-x: auto;
  overflow-y: hidden;
  -webkit-overflow-scrolling: touch;
  padding: 0 28px;

  &::-webkit-scrollbar { display: none; }
}

.cards-track {
  display: flex;
  gap: 16px;
  width: max-content;
}

.card {
  width: 260px;
  flex-shrink: 0;
  border-radius: 16px;
  overflow: hidden;
  background: $bg-card;
}

.card-img {
  width: 100%;
  height: auto;
  display: block;
}

/* ═══════════ Timeline Years ═══════════ */
.timeline-section {
  padding: 0 0 40px;
}

.timeline-years {
  overflow-x: auto;
  overflow-y: hidden;
  -webkit-overflow-scrolling: touch;
  padding: 0 28px 20px;

  &::-webkit-scrollbar { display: none; }
}

.years-scroll {
  display: flex;
  gap: 12px;
  width: max-content;
}

.year-item {
  flex-shrink: 0;
  padding: 10px 22px;
  border-radius: 50px;
  font-size: 24px;
  color: $textSecondary;
  background: transparent;
  border: 1px solid $border;
  cursor: pointer;
  transition: all 0.15s;
  white-space: nowrap;

  &.active {
    background: $theme;
    color: #fff;
    border-color: $theme;
  }

  &:active { background: $bg-card-hover; }
  &.active:active { background: $theme; }
}

/* ═══════════ Timeline Cards ═══════════ */
.timeline-cards {
  padding: 0 28px;
  position: relative;
}

.tl-card {
  display: flex;
  position: relative;
  padding-bottom: 28px;

  &:last-child { padding-bottom: 0; }
}

.tl-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: $theme;
  flex-shrink: 0;
  margin-top: 6px;
  margin-right: 16px;
  position: relative;
  z-index: 1;
}

.tl-line {
  position: absolute;
  left: 5px;
  top: 24px;
  bottom: 0;
  width: 2px;
  background: $border;

  .tl-card:last-child & { display: none; }
}

.tl-content {
  flex: 1;
  text-align: left;
  padding-bottom: 4px;
}

.tl-date {
  font-size: 26px;
  font-weight: 700;
  color: $theme;
  margin-bottom: 6px;
}

.tl-text {
  font-size: 24px;
  color: $textSecondary;
  line-height: 1.5;
}

/* ═══════════ CTA ═══════════ */
.cta-section {
  padding: 40px 28px 48px;
  text-align: center;

  p {
    font-size: 28px;
    color: $textColor;
    margin: 0 0 24px;
    line-height: 1.5;
  }
}

.cta-btn {
  padding: 18px 48px;
  border-radius: 50px;
  font-size: 30px;
  font-weight: 700;
  cursor: pointer;
  border: none;
  background: $theme;
  color: #fff;
  box-shadow: 0 6px 24px rgba(153, 26, 255, 0.35);

  &:active {
    transform: scale(0.97);
  }
}

/* ═══════════ Popups ═══════════ */
.first-login-card {
  width: min(95vw, 760px);
  border-radius: 20px;
  padding: 20px;
  max-height: 80vh;
  overflow-y: auto;
  border-radius: 80px;
}

.tanchuang_close {
  color: #bbbbbb;
  text-align: center;
  margin: 15px auto;
  font-size: 50px;
}

:deep(.first-login-overlay) {
  background: rgba(0, 0, 0, 0.56);
}

/* ═══════════ Page wrapper ═══════════ */
.home-dark {
  display: block !important;
  min-height: 100%;
  background: $bg-primary;
}
</style>
