<template>
  <div class="ad-showcase" :style="cssVars">

    <!-- ① 标题行 -->
    <div class="ads-header">
      <span class="ads-title">Start Promoting</span>
      <span class="ads-progress">
        <span class="ads-cur">{{ completed }}</span>/{{ total }}
      </span>
    </div>

    <!-- ② SVG桶形mask图片带 -->
    <div class="ads-strip-mask">
      <div class="ads-strip-inner">
        <div
          v-for="(img, i) in stripImages"
          :key="i"
          class="ads-strip-card"
          :style="cardStyles[i]"
        >
          <img :src="img.src" :alt="img.alt" draggable="false" />
        </div>
      </div>
    </div>

    <!-- ③ 中央展示台 -->
    <div class="ads-stage">
      <img :src="baseBg" alt="base" class="ads-base-img" />
      <!--
        用 v-if/v-else-if 手动控制单张图片的显示，
        配合 JS 驱动的 inline style 实现 rotate 轨迹动画，
        不依赖 Vue transition（更精确控制时序）
      -->
      <img
        v-for="(img, i) in carImages"
        v-show="stageVisible[i]"
        :key="i"
        :src="img.src"
        :alt="img.alt"
        class="ads-car-img"
        :style="carStyles[i]"
      />
    </div>

    <!-- ④ 按钮 -->
    <div class="ads-btn-wrap">
      <button class="ads-btn" @click="$emit('match')">
        Ad Match ({{ completed }}/{{ total }})
      </button>
    </div>

    <!-- ⑤ 说明文字 -->
    <div class="ads-note">
      <p class="ads-note-title">Today Advertising salary: {{ todaySalary }}</p>
      <p class="ads-note-sub">
        The displayed amount reflects the profits earned today as an indication.
      </p>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted, onBeforeUnmount } from 'vue'

defineProps({
  completed:   { type: Number, default: 0 },
  total:       { type: Number, default: 0 },
  todaySalary: { type: Number, default: 0  },
})
defineEmits(['match'])

/* ─────────────────────────────────────────────────────────
   图片列表 a1↔b1 … a20↔b20
───────────────────────────────────────────────────────── */
const TOTAL = 20

const stripImages = Array.from({ length: TOTAL }, (_, i) => ({
  src: require(`@/assets/images/a${i + 1}.jpg`),
  alt: `ad ${i + 1}`,
}))
const carImages = Array.from({ length: TOTAL }, (_, i) => ({
  src: require(`@/assets/images/b${i + 1}.png`),
  alt: `car ${i + 1}`,
}))
const baseBg = require('@/assets/images/bg.png')

/* ─────────────────────────────────────────────────────────
   固定 px 布局常量
   CARD_W：卡片固定宽度（px），不随屏幕变化
   GAP：卡片间距（px）
   CARD_H：卡片高度 = CARD_W * 1.25
   STEP：每步移动量 = CARD_W + GAP
───────────────────────────────────────────────────────── */
const CARD_W = 80   // px，固定宽度
const GAP    = 12    // px，固定间距
const CARD_H = Math.round(CARD_W * 1.25)  // 188px
const STEP   = CARD_W + GAP               // 162px

// mask 容器高度 = CARD_W / 0.72（与原始公式一致）
const MASK_H = Math.round(CARD_W / 0.52)  // ≈208px
//const MASK_H = 140

// CSS变量只传固定px值，不再用vw
const cssVars = computed(() => ({
  '--card-w':    `${CARD_W}px`,
  '--card-h':    `${CARD_H}px`,
  '--mask-h':    `${MASK_H}px`,
  '--mask-size': '100% 100%',
}))

/* ─────────────────────────────────────────────────────────
   每张卡片初始 X 位置（px，相对容器左边）
───────────────────────────────────────────────────────── */
function getInitX(i) {
  return i * STEP  // px
}

/* ─────────────────────────────────────────────────────────
   卡片样式（JS 全量控制，px单位）
───────────────────────────────────────────────────────── */
const cardStyles = reactive(
  Array.from({ length: TOTAL }, (_, i) => ({
    transform: `translateX(${getInitX(i)}px) translateY(-50%)`,
  }))
)

/* ─────────────────────────────────────────────────────────
   展示台图片样式 + 显隐
───────────────────────────────────────────────────────── */
const carStyles    = reactive(Array.from({ length: TOTAL }, () => ({})))
const stageVisible = reactive(Array.from({ length: TOTAL }, () => false))

/* ─────────────────────────────────────────────────────────
   动画时长参数
───────────────────────────────────────────────────────── */
const SLIDE_MS  = 900   // 单步滑动时长
const PAUSE_MS  = 1800  // 停留时长
const REWIND_MS = 680   // 倒退时长
const REWIND_AT = TOTAL - 3  // 第17张后触发倒退

/* ─────────────────────────────────────────────────────────
   缓动函数
───────────────────────────────────────────────────────── */
function easeIn(t)    { return t * t * t }
function easeOut(t)   { return 1 - Math.pow(1 - t, 3) }
function easeInOut(t) {
  return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2
}

/* ─────────────────────────────────────────────────────────
   飞行轨迹（px，固定偏移量）
   飞出终点 / 飞入起点：右上角 (+320px, -89px) rotate(13.3deg)
───────────────────────────────────────────────────────── */
const FLY_TX  = 320
const FLY_TY  = -89
const FLY_ROT = 13.3

function getFlyOutStyle(t) {
  const e  = easeIn(t)
  return {
    opacity:   Math.max(0, 1 - e * 1.1),
    transform: `translateX(calc(-50% + ${(FLY_TX * e).toFixed(2)}px)) translateY(${(FLY_TY * e).toFixed(2)}px) rotate(${(FLY_ROT * e).toFixed(3)}deg)`,
  }
}

function getFlyInStyle(t) {
  const tc  = Math.min(t, 1)
  const e   = easeOut(tc)
  const inv = 1 - e
  return {
    opacity:   Math.min(1, e * 1.4),
    transform: `translateX(calc(-50% + ${(FLY_TX * inv).toFixed(2)}px)) translateY(${(FLY_TY * inv).toFixed(2)}px) rotate(${(FLY_ROT * inv).toFixed(3)}deg)`,
  }
}

const REST_STYLE = {
  opacity:   1,
  transform: 'translateX(-50%) translateY(0px) rotate(0deg)',
}

/* ─────────────────────────────────────────────────────────
   状态机变量
───────────────────────────────────────────────────────── */
let rafId        = null
let phase        = 'pause'
let phaseStart   = null
let currentStep  = 0      // 当前停留在第几步（左侧第一张 = stripImages[currentStep]）
let offsetPx     = 0      // 当前已移动 px
let stepStartOff = 0      // 本步开始时的 offsetPx
let showingIdx   = 0      // 展示台当前显示的图片索引
let nextIdx      = -1     // 准备飞入的图片索引

/* ── 工具函数 ── */
function applyCards(off) {
  for (let i = 0; i < TOTAL; i++) {
    const x = getInitX(i) - off
    cardStyles[i] = { transform: `translateX(${x.toFixed(2)}px) translateY(-50%)` }
  }
}

function showCar(idx, style) {
  for (let i = 0; i < TOTAL; i++) {
    stageVisible[i] = (i === idx)
    carStyles[i]    = (i === idx) ? (style || REST_STYLE) : {}
  }
}

function showTwoCars(outIdx, outStyle, inIdx, inStyle) {
  for (let i = 0; i < TOTAL; i++) {
    if (i === outIdx)      { stageVisible[i] = true; carStyles[i] = outStyle }
    else if (i === inIdx)  { stageVisible[i] = true; carStyles[i] = inStyle  }
    else                   { stageVisible[i] = false; carStyles[i] = {}       }
  }
}

/* ─────────────────────────────────────────────────────────
   主 rAF 循环
───────────────────────────────────────────────────────── */
function tick(ts) {
  if (!phaseStart) phaseStart = ts
  const elapsed = ts - phaseStart

  /* pause：等待停留时长 */
  if (phase === 'pause') {
    if (elapsed >= PAUSE_MS) {
      if (currentStep >= REWIND_AT) {
        stepStartOff = offsetPx
        phaseStart   = ts
        phase        = 'rewind'
      } else {
        stepStartOff = offsetPx
        nextIdx      = (currentStep + 1) % TOTAL
        phaseStart   = ts
        phase        = 'slide'
      }
    }

  /* slide：卡片步进 + 飞出旧图 + 飞入新图 同步 */
  } else if (phase === 'slide') {
    const t  = Math.min(elapsed / SLIDE_MS, 1)
    const et = easeInOut(t)

    offsetPx = stepStartOff + STEP * et
    applyCards(offsetPx)

    // 飞出全程同步，飞入从 45% 开始
    const inT = Math.max(0, (t - 0.45) / 0.55)
    showTwoCars(showingIdx, getFlyOutStyle(t), nextIdx, getFlyInStyle(inT))

    if (t >= 1) {
      offsetPx    = stepStartOff + STEP
      applyCards(offsetPx)
      currentStep++
      showingIdx  = currentStep % TOTAL
      nextIdx     = -1
      showCar(showingIdx, REST_STYLE)
      phaseStart  = ts
      phase       = 'pause'
    }

  /* rewind：快速倒退到起点 */
  } else if (phase === 'rewind') {
    const t  = Math.min(elapsed / REWIND_MS, 1)
    const et = easeOut(t)

    offsetPx = stepStartOff * (1 - et)
    applyCards(offsetPx)

    // 展示台：前半淡出当前图，后半淡入第0张
    if (t < 0.5) {
      stageVisible[showingIdx] = true
      carStyles[showingIdx]    = {
        opacity:   Math.max(0, 1 - t * 2.5),
        transform: 'translateX(-50%) translateY(0px) rotate(0deg)',
      }
    } else {
      stageVisible[showingIdx] = false
      carStyles[showingIdx]    = {}
      stageVisible[0]          = true
      carStyles[0]             = {
        opacity:   Math.min(1, (t - 0.5) * 2 * 1.5),
        transform: 'translateX(-50%) translateY(0px) rotate(0deg)',
      }
    }

    if (t >= 1) {
      currentStep = 0
      offsetPx    = 0
      showingIdx  = 0
      applyCards(0)
      showCar(0, REST_STYLE)
      phaseStart  = ts
      phase       = 'pause'
    }
  }

  rafId = requestAnimationFrame(tick)
}

/* ─────────────────────────────────────────────────────────
   生命周期
───────────────────────────────────────────────────────── */
onMounted(() => {
  applyCards(0)
  showCar(0, REST_STYLE)
  phase      = 'pause'
  phaseStart = null
  rafId      = requestAnimationFrame(tick)
})

onBeforeUnmount(() => {
  if (rafId) cancelAnimationFrame(rafId)
})
</script>

<style lang="scss" scoped>
$brand: #4c4cef;

/* ══════════════════════════════════════════════════════════
   整体
══════════════════════════════════════════════════════════ */
.ad-showcase {
  width: 100%;
  padding-bottom: 40px;
  background: #fff;
  box-sizing: border-box;
  padding: 50px;
}

/* ══════════════════════════════════════════════════════════
   标题行
══════════════════════════════════════════════════════════ */
.ads-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 32px 30px 20px;
  .ads-title {
    font-size: 36px;
    font-weight: 800;
    color: #111;
  }
  .ads-progress {
    font-size: 34px;
    font-weight: 700;
    color: #888;
    .ads-cur { color: $brand; }
  }
}

/* ══════════════════════════════════════════════════════════
   SVG桶形mask图片带
══════════════════════════════════════════════════════════ */
.ads-strip-mask {
  overflow: hidden;
  position: relative;
  width: 100%;
  height: var(--mask-h, 208px);   /* JS 算好后传入，固定 px */

  $svg: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNDQwIiBoZWlnaHQ9IjUwMCIgdmlld0JveD0iMCAwIDE0NDAgNTAwIiBpZD0iaiI+CiAgPHBhdGggZmlsbD0icmdiKDIwMCwyMDAsMjAwKSIgZmlsbC1ydWxlPSJldmVub2RkIiBkPSJNMCAwczI3NS4wNCA1MCA3MjAgNTBTMTQ0MCAwIDE0NDAgMHY1MDBzLTI3NS4wNC01MC03MjAtNTBTMCA1MDAgMCA1MDBWMHoiLz4KPC9zdmc+");
  -webkit-mask:          $svg;
  mask:                  $svg;
  -webkit-mask-repeat:   no-repeat;
  mask-repeat:           no-repeat;
  -webkit-mask-position: center;
  mask-position:         center;
  -webkit-mask-size:     var(--mask-size, 100% 100%);
  mask-size:             var(--mask-size, 100% 100%);
}

.ads-strip-inner {
  position: absolute;
  inset: 0;
}

.ads-strip-card {
  position: absolute;
  top: 50%;
  left: 0;
  width: var(--card-w, 150px);    /* 固定 px 宽度，JS传入 */
  //height: var(--card-h, 188px);   /* 固定 px 高度，JS传入 */
  border-radius: 16px;
  overflow: hidden;
  background: #ccc;
  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    user-select: none;
    pointer-events: none;
  }
}

/* ══════════════════════════════════════════════════════════
   中央展示台
══════════════════════════════════════════════════════════ */
.ads-stage {
  position: relative;
  width: 100%;
  height: 360px;
  overflow: visible;
  margin-top: 8px;
}

.ads-base-img {
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 78%;
  pointer-events: none;
}

.ads-car-img {
  position: absolute;
  left: 50%;
  bottom: 28px;
  /* transform 完全由 JS 控制，CSS这里只设baseline */
  transform: translateX(-50%);
  width: 86%;
  max-height: 295px;
  object-fit: contain;
  pointer-events: none;
  filter: drop-shadow(0 20px 44px rgba(0, 0, 0, 0.26));
  /* 不设 transition，由JS逐帧更新 */
  will-change: transform, opacity;
}

/* ══════════════════════════════════════════════════════════
   按钮
══════════════════════════════════════════════════════════ */
.ads-btn-wrap {
  padding: 20px 30px 0;
}
.ads-btn {
  display: block;
  width: 100%;
  height: 90px;
  background: $brand;
  color: #fff;
  border: none;
  border-radius: 50px;
  font-size: 34px;
  font-weight: 700;
  letter-spacing: 0.5px;
  cursor: pointer;
  box-shadow: 0 8px 28px rgba(76, 76, 239, 0.38);
  transition: transform 0.15s, box-shadow 0.15s;
  &:active {
    transform: scale(0.97);
    box-shadow: 0 4px 14px rgba(76, 76, 239, 0.22);
  }
}

/* ══════════════════════════════════════════════════════════
   说明文字
══════════════════════════════════════════════════════════ */
.ads-note {
  padding: 24px 30px 0;
  text-align: left;
  .ads-note-title {
    font-size: 28px;
    font-weight: 700;
    color: #222;
    margin: 0 0 10px;
  }
  .ads-note-sub {
    font-size: 24px;
    color: #999;
    line-height: 1.6;
    margin: 0;
  }
}
</style>

<!--
==============================================================
  集成到 obj.vue
==============================================================
① AdShowcase.vue 与 obj.vue 同目录

② <script> 引入：
   import AdShowcase from './AdShowcase.vue'

③ <template> .content 内，header-card 之前：
   <AdShowcase
     :completed="info.day_completed_count || 0"
     :total="info.order_num || 0"
     :today-salary="mInfo.yon1 || 0"
     @match="getDd"
   />

④ Options API 在 components:{ AdShowcase } 注册

⑤ 关键调参：
   SLIDE_MS   滑动时长（越大越慢）
   PAUSE_MS   停留时长
   FLY_MS     飞入/飞出总时长（参考用，实际由slide_t驱动）
   REWIND_AT  第几张开始触发倒退（默认 TOTAL-3 = 第17张）
   REWIND_MS  倒退动画时长
   CARD_VW    卡片宽度占屏幕百分比
==============================================================
-->