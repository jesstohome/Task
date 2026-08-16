<template>
  <div class="obj home">

    <!-- 加载遮罩（优化版） -->
    <transition name="loading-fade">
      <div class="img_loading" v-if="loading">
            <div class="loading-body">
      <!-- K线图容器 -->
      <div class="loading-kline-wrap">
        <div class="kline-header">
          <span class="kline-symbol">📈 </span>
          <span class="kline-badge" :class="klineTrend === 'up' ? 'kline-badge--up' : 'kline-badge--down'">
            {{ klineTrend === 'up' ? '▲' : '▼' }} {{ klinePct }}%
          </span>
        </div>
        <canvas ref="klineCanvasRef" class="loading-kline-canvas"></canvas>
        <div class="kline-footer">
          <span class="kline-label">VOL</span>
          <canvas ref="klineVolRef" class="loading-vol-canvas"></canvas>
        </div>
      </div>

      <!-- 步骤文字 -->
      <div class="loading-text-wrap">
        <div class="loading-step-text">{{ loadText }}</div>
        <div class="loading-dots">
          <span></span><span></span><span></span>
        </div>
      </div>

      <!-- 进度条 -->
      <div class="loading-progress">
        <div class="loading-progress-bar" :class="loadProgressClass"></div>
      </div>

      <!-- 步骤指示器 -->
      <div class="loading-steps">
        <div class="step-item" :class="{ active: loadStep >= 1, done: loadStep > 1 }">
          <div class="step-dot">
            <span v-if="loadStep > 1">✓</span>
            <span v-else>1</span>
          </div>
          <div class="step-label">Matching</div>
        </div>
        <div class="step-line" :class="{ active: loadStep > 1 }"></div>
        <div class="step-item" :class="{ active: loadStep >= 2, done: loadStep > 2 }">
          <div class="step-dot">
            <span v-if="loadStep > 2">✓</span>
            <span v-else>2</span>
          </div>
          <div class="step-label">Pairing</div>
        </div>
        <div class="step-line" :class="{ active: loadStep > 2 }"></div>
        <div class="step-item" :class="{ active: loadStep >= 3 }">
          <div class="step-dot"><span>3</span></div>
          <div class="step-label">Success</div>
        </div>
      </div>
    </div>
      </div>
    </transition>

    <!-- 抢单成功结果遮罩 -->
    <transition name="result-slide">
      <div class="order-result-mask" v-if="showOrderResult">
        <!-- 背景烟花 -->
        <div class="result-fireworks">
          <div class="fw-dot" v-for="i in 16" :key="i" :style="getFwStyle(i)"></div>
        </div>

        <div class="result-card">
          <!-- 顶部光晕圆 -->
          <div class="result-card-halo"></div>

          <!-- 成功图标 -->
          <div class="result-icon-wrap">
            <div class="result-icon-ring ring-outer"></div>
            <div class="result-icon-ring ring-inner"></div>
            <div class="result-icon">🚀</div>
          </div>

          <!-- 标题 -->
          <div class="result-title">Order Matched!</div>
          <div class="result-subtitle">Your order has been successfully created</div>

          <!-- 分隔线 -->
          <div class="result-divider">
            <span class="divider-dot"></span>
            <span class="divider-line"></span>
            <span class="divider-dot"></span>
          </div>

          <!-- 信息展示 -->
          <div class="result-info-grid">
            <div class="result-info-item">
              <div class="info-label">Commission</div>
              <div class="info-value info-value--highlight">+{{ resultOrderInfo.commission }} {{ currency }}</div>
            </div>
            <div class="result-info-item">
              <div class="info-label">Order Amount</div>
              <div class="info-value">{{ resultOrderInfo.amount }} {{ currency }}</div>
            </div>
          </div>

          <!-- 底部提示 -->
          <div class="result-tip">Redirecting to order details...</div>

          <!-- 倒计时条 -->
          <div class="result-countdown-bar">
            <div class="result-countdown-fill" :style="{ width: resultCountdown + '%' }"></div>
          </div>
        </div>
      </div>
    </transition>

    <!-- ═══════════════ 以下页面内容完全不动 ═══════════════ -->

    <div class="hero-section">
      <div class="hero-bg">
        <canvas ref="heroKlineRef" class="hero-kline-canvas"></canvas>
        <div class="hero-bg-overlay"></div>
      </div>
      <div class="hero-nav">
        <div class="hero-nav-left">
          <div class="hero-avatar">
            <img :src="userinfo?.headpic || require('@/assets/images/touxian.webp')" alt="avatar" />
          </div>
          <span class="hero-greeting">Hi, {{userinfo?.username}} 👋</span>
        </div>
        <div class="hero-rank">{{ info?.level_name }}</div>
      </div>
      <div class="hero-subtitle">
        Join <strong>65,000</strong> others and learn the secrets to <strong>SEO</strong> success with our weekly blog posts.
      </div>
      <div class="hero-cards">
        <div class="hero-card">
          <div class="hero-card-icon hero-card-icon--wallet">
            <img :src="require('@/assets/images/usdt.png')" alt="" class="hero-bg-img" />
          </div>
          <div class="hero-card-label">Wallet Balance</div>
          <div class="hero-card-amount">
            <span class="hero-card-amount--negative" style="font-size: 11px;color:white;" v-if="Number(monney.replace(/,/g, '')) < 0">Exceed </span>
            <span class="hero-card-amount--negative">{{monney}}</span>
            <span class="hero-card-currency">{{currency}}</span>
          </div>
          <div class="hero-card-desc">The total balance reflects both the deposited amount and profits earned</div>
        </div>
        <div class="hero-card">
          <div class="hero-card-icon hero-card-icon--ad">
            <img :src="require('@/assets/images/qianbao.png')" alt="" class="hero-bg-img" />
          </div>
          <div class="hero-card-label">Today's Advertising salary</div>
          <div class="hero-card-amount">
            <span class="hero-card-amount--zero">{{mInfo.yon1}}</span>
            <span class="hero-card-currency">{{currency}}</span>
          </div>
          <div class="hero-card-desc">Fixed balance where there is a mixed product pending in process.</div>
        </div>
      </div>
      <div class="hero-arc"></div>
    </div>
    <div class="hero-info">
      <div>Current Balance：<span style="color: #991aff;">{{ mInfo.dongjiejine }}</span></div>
      <!-- <div>Today's earnings：<span style="color: #991aff;">{{ mInfo.yon1 }}</span></div> -->
    </div>

    <AdShowcase
      v-if="info && mInfo"
      :completed="info.day_completed_count || 0"
      :total="info.order_num || 0"
      :today-salary="mInfo.yon1 || 0"
      @match="getDd"
    />

    <div class="below-showcase">
      <div class="notes-card">
        <div class="notes-card-bg">
          <img :src="require('@/assets/images/notice.webp')" alt="" class="notes-bg-img" />
          <div class="notes-bg-overlay"></div>
        </div>
        <div class="notes-content">
          <div class="notes-title">Important Notes</div>
          <div class="notes-body">
            <p>* Online Support Hours 09:00 - 21:59</p>
            <p>- For any further questions, Please contact support team</p>
          </div>
        </div>
      </div>
      <div class="copyright"> All Rights Reserved. | Privacy Policy | Terms of Service | <span @click="push('/content?id=20&title=Force Platform User Confidentiality Agreement')">Non-Disclosure Agreement (NDA)</span></div>
    </div>

    <van-dialog v-model:show="level_show" :title="$t('msg.djsm')" :cancelButtonText="$t('msg.quxiao')" show-cancel-button :showConfirmButton="false">
      <van-swipe class="my-swipe" indicator-color="white">
        <van-swipe-item v-for="item in info.level_list || []" :key="item.id">
          <div class="hy_box">
            <div class="t">
              <img :src="require('@/assets/images/home/huiyuan.png')" class="img" alt="">
              <span class="text">{{$t('msg.hy_level')}} {{item.name}}</span>
            </div>
            <div class="b">
              <div class="sub">{{$t('msg.sxtz')}}： {{currency}}{{item.num}}</div>
              <div class="sub">{{$t('msg.yonj')}}： {{item.bili*100}}% <span class="line">|</span>{{item.order_num}}{{$t('msg.order')}}</div>
            </div>
            <van-button type="primary" class="txlevel" round block>{{item.level <= info.uinfo?.level ? $t('msg.now_level') : $t('msg.add_level')}}</van-button>
          </div>
        </van-swipe-item>
      </van-swipe>
    </van-dialog>

    <van-dialog v-model:show="showTj" :confirmButtonText="$t('msg.queren')" :cancelButtonText="$t('msg.close')" :show-confirm-button="true" :title="$t('msg.ddxq')" show-cancel-button @confirm="confirmPwd">
      <template #title>
        <div style="text-align: center">{{$t('msg.ddxq')}}</div>
      </template>
      <div class="list" v-if="onceinfo.data?.group_rule_num == 0 || onceinfo.data?.duorw == 0">
        <div class="cet">
          <img :src="onceinfo.data?.goods_pic" class="img" alt="">
        </div>
        <div class="monney">
          <div class="tent"><span class="span">{{$t('msg.ddh')}}</span><span class="value">{{onceinfo.data?.oid}}</span></div>
          <div class="tent"><span class="span">{{$t('msg.xdsj')}}</span><span class="value">{{formatTime('',onceinfo.data?.addtime)}}</span></div>
          <div class="tent"><span class="span">{{$t('msg.spdj')}}</span><span class="value">{{currency+onceinfo.data?.goods_price}}</span></div>
          <div class="tent"><span class="span">{{$t('msg.spsl')}}</span><span class="value">{{'x ' + onceinfo.data?.goods_count}}</span></div>
          <div class="tent"><span class="span">{{$t('msg.order_Num')}}</span><span class="value">{{currency+onceinfo.data?.num}}</span></div>
          <div class="tent"><span class="span">{{$t('msg.yonj')}}</span><span class="value">{{currency+onceinfo.data?.commission}}</span></div>
        </div>
      </div>
      <div class="list" v-else>
        <div class="tops"><span class="span">{{$t('msg.ddrws')}}：</span><span class="span" style="color:red;">{{onceinfo.data?.duorw}}</span></div>
        <div class="tops"><span class="span">{{$t('msg.ywc')}}：</span><span class="span" style="color:#00a300;">{{onceinfo.data?.completedquantity}}</span></div>
        <div class="box" v-for="item in onceinfo.group_data" :key="item.id">
          <div class="cet"><img :src="item?.goods_pic" class="img" alt=""></div>
          <div class="monney">
            <div class="tent"><span class="span">{{$t('msg.spdj')}}</span><span class="value">{{currency+item?.goods_price}}</span></div>
            <div class="tent"><span class="span">{{$t('msg.spsl')}}</span><span class="value">{{'x ' + item?.goods_count}}</span></div>
            <div class="tent"><span class="span">{{$t('msg.order_Num')}}</span><span class="value">{{currency+item?.num}}</span></div>
            <div class="tent"><span class="span">{{$t('msg.fkzt')}}</span><span class="value" :class="'value'+item.is_pay">{{item.is_pay === 0 ? $t('msg.dfk') : $t('msg.yfk')}}</span></div>
          </div>
        </div>
      </div>
      <div class="pinglun">
        <div class="pingluna">
          <div>{{ $t('msg.dianjifabiaopinglun') }}</div>
          <div>
            <van-rate v-model="pinglun" :size="20" color="#ffd21e" void-icon="star" void-color="#eee" />
          </div>
        </div>
        <div class="pinglunb">
          <van-cell-group inset>
            <van-field v-model="pingluntext" rows="2" type="textarea" placeholder="" :required="true" :center="true">
              <template #button>
                <van-button @click="generateRandomComment" size="mini" color="#ff9800">{{ $t('msg.zidongpinglun') }}</van-button>
              </template>
            </van-field>
          </van-cell-group>
        </div>
      </div>
    </van-dialog>

    <GiftPackage v-model="showGift" ref="giftRef" />

    <van-dialog
      v-model:show="showCompoundOrder"
      title="Bundle Order"
      :show-cancel-button="false"
      :show-confirm-button="false"
      :close-on-click-overlay="false"
      :teleport="'body'"
    >
      <div class="compound-order-modal">
        <div class="compound-order-header">
          <div class="celebration-icon">🎉</div>
          <h3>Congratulations on triggering the bundle order privilege!</h3>
          <p class="compound-order-desc">
            You have completed the specified number of orders, unlocking the bundle order privilege!<br>
            Please select an order sequence, and the system will automatically create bundle orders for you.
          </p>
        </div>
        <div class="compound-order-options" v-if="compoundOrderData">
          <div
            v-for="option in compoundOrderData.log.custom_options"
            :key="option.option_id"
            class="compound-option-card"
            @click="selectCompoundOrderOption(option.option_id)"
          >
            <div class="option-header">
              <h4>{{ option.title }}</h4>
            </div>
            <div class="option-desc" v-if="option.description">{{ option.description }}</div>
          </div>
        </div>
      </div>
    </van-dialog>

  </div>
</template>

<script>
import { ref, computed, getCurrentInstance, reactive, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { rot_order, submit_order, order_info, do_order, start_compound_order, process_compound_order_next } from '@/api/order/index'
import store from '@/store/index'
import { getdetailbyid, getHomeData } from '@/api/home/index.js'
import { formatTime } from '@/api/format.js'
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n'
import { getsupport } from '@/api/tel/index'
import AdShowcase from '@/components/adshowcase.vue'
import GiftPackage from '@/components/gift/index.js'
import TabNav from '@/components/tabnav.vue'

export default {
  components: { AdShowcase, GiftPackage, TabNav },
  setup() {
    const { t } = useI18n()
    const giftRef = ref(null)
    const userinfo = ref(store.state.userinfo)
    const { push } = useRouter();
    const { proxy } = getCurrentInstance()
    const level_show = ref(false)
    const loading = ref(false)
    const klineCanvasRef = ref(null)
    const klineVolRef = ref(null)
    const klineTrend = ref('up')
    const klinePct = ref('0.00')
    let klineAnimFrame = null
    let klineStopFn = null

    // ── Hero 背景K线动画 ──
    const heroKlineRef = ref(null)
    let heroKlineTimer = null
    let heroResizeObserver = null

    const startHeroKline = () => {
      const canvas = heroKlineRef.value
      if (!canvas) return

      const dpr = window.devicePixelRatio || 1
      const parent = canvas.parentElement
      const rand = (min, max) => Math.random() * (max - min) + min
      const totalCandles = 24
      let W, H, candleW, bodyW, ctx

      const initCandles = () => {
        const arr = []
        let p = rand(60, 140)
        for (let i = 0; i < totalCandles; i++) {
          const isGreen = Math.random() > 0.42
          const bodySize = rand(1.5, 8)
          const open = p
          const close = isGreen ? p + bodySize : p - bodySize
          const high = Math.max(open, close) + rand(0.5, 4)
          const low = Math.min(open, close) - rand(0.5, 4)
          arr.push({ open, close, high, low, isGreen })
          p = close + rand(-2, 2)
          p = Math.max(p, 20)
        }
        return arr
      }

      let candles = initCandles()

      const resize = () => {
        W = parent.offsetWidth
        H = parent.offsetHeight
        canvas.width = W * dpr
        canvas.height = H * dpr
        canvas.style.width = W + 'px'
        canvas.style.height = H + 'px'
        ctx = canvas.getContext('2d')
        ctx.scale(dpr, dpr)
        candleW = W / totalCandles
        bodyW = Math.max(candleW * 0.55, 2)
      }

      resize()

      heroResizeObserver = new ResizeObserver(() => {
        resize()
        draw()
      })
      heroResizeObserver.observe(parent)

      const padT = 8, padB = 8

      const draw = () => {
        if (!ctx) return
        ctx.clearRect(0, 0, W, H)

        const bgGrad = ctx.createLinearGradient(0, 0, W, H)
        bgGrad.addColorStop(0, 'rgba(10, 12, 30, 1)')
        bgGrad.addColorStop(0.5, 'rgba(15, 10, 40, 1)')
        bgGrad.addColorStop(1, 'rgba(8, 15, 35, 1)')
        ctx.fillStyle = bgGrad
        ctx.fillRect(0, 0, W, H)

        const visible = candles
        const allHigh = Math.max(...visible.map(c => c.high))
        const allLow = Math.min(...visible.map(c => c.low))
        const range = allHigh - allLow || 1
        const toY = (p) => padT + (allHigh - p) / range * (H - padT - padB)

        ctx.setLineDash([2, 6])
        ctx.strokeStyle = 'rgba(255,255,255,0.04)'
        ctx.lineWidth = 0.5
        for (let i = 0; i <= 3; i++) {
          const y = padT + (H - padT - padB) * i / 3
          ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(W, y); ctx.stroke()
        }
        ctx.setLineDash([])

        visible.forEach((c, i) => {
          const x = i * candleW + candleW / 2
          const yOpen = toY(c.open)
          const yClose = toY(c.close)
          const yHigh = toY(c.high)
          const yLow = toY(c.low)

          ctx.strokeStyle = c.isGreen ? 'rgba(0, 200, 130, 0.45)' : 'rgba(255, 80, 80, 0.45)'
          ctx.lineWidth = 1
          ctx.beginPath(); ctx.moveTo(x, yHigh); ctx.lineTo(x, Math.max(yOpen, yClose)); ctx.stroke()
          ctx.beginPath(); ctx.moveTo(x, Math.min(yOpen, yClose)); ctx.lineTo(x, yLow); ctx.stroke()

          const bodyH = Math.max(Math.abs(yClose - yOpen), 1)
          ctx.fillStyle = c.isGreen ? 'rgba(0, 200, 130, 0.3)' : 'rgba(255, 80, 80, 0.3)'
          ctx.fillRect(x - bodyW / 2, Math.min(yOpen, yClose), bodyW, bodyH)
        })

        candles.shift()
        const last = candles[candles.length - 1]
        const isGreen = Math.random() > 0.45
        const bodySize = rand(1.5, 8)
        const open = last.close
        const close = isGreen ? open + bodySize : open - bodySize
        const high = Math.max(open, close) + rand(0.5, 3)
        const low = Math.min(open, close) - rand(0.5, 3)
        candles.push({ open, close, high, low, isGreen: close >= open })
      }

      draw()
      heroKlineTimer = setInterval(draw, 500)
    }

    const stopHeroKline = () => {
      if (heroKlineTimer) { clearInterval(heroKlineTimer); heroKlineTimer = null }
      if (heroResizeObserver) { heroResizeObserver.disconnect(); heroResizeObserver = null }
    }
    const loadText = ref('')
    const loadImg = ref('')
    const loadStep = ref(0)           // 新增：步骤状态 1/2/3
    const showOrderResult = ref(false) // 新增：成功结果遮罩
    const resultOrderInfo = ref({ commission: '0.00', amount: '0.00' }) // 新增：结果数据
    const resultCountdown = ref(100)   // 新增：倒计时进度
    let resultCountdownTimer = null

    const level = ref(store.state.minfo?.level || 0)
    const currency = ref(store.state.baseInfo?.currency)
    const info = ref(store.state.objInfo)
    const pinglun = ref(0)
    const pingluntext = ref('')
    const creditPercent = ref(store.state.minfo?.credit || 0)
    const activeTab = ref(1)
    const monney = ref(store.state.minfo?.balance)
    const mInfo = ref(store.state.minfo)
    const onceinfo = ref({})
    const showTj = ref(false)
    const content = ref('')
    const support = ref('')
    const showGift = ref(false)
    const showCompoundOrder = ref(false)
    const compoundOrderData = ref(null)


    const startKlineAnimation = (duration, getResultTrend) => {
  if (klineAnimFrame) cancelAnimationFrame(klineAnimFrame)
  if (klineStopFn) klineStopFn()

  return new Promise(async (resolve) => {
    await nextTick()
    const canvas = klineCanvasRef.value
    const volCanvas = klineVolRef.value
    if (!canvas) { resolve(); return }

    const dpr = window.devicePixelRatio || 1
    const W = canvas.offsetWidth || 320
    const H = canvas.offsetHeight || 200
    canvas.width = W * dpr
    canvas.height = H * dpr
    const ctx = canvas.getContext('2d')
    ctx.scale(dpr, dpr)

    let vW = 0, vH = 0, vCtx = null
    if (volCanvas) {
      vW = volCanvas.offsetWidth || 320
      vH = volCanvas.offsetHeight || 50
      volCanvas.width = vW * dpr
      volCanvas.height = vH * dpr
      vCtx = volCanvas.getContext('2d')
      vCtx.scale(dpr, dpr)
    }

    const totalCandles = 32
    const splitAt = 28
    const rand = (min, max) => Math.random() * (max - min) + min

    const candles = []
    let price = rand(80, 120)

    for (let i = 0; i < splitAt; i++) {
      const isGreen = Math.random() > 0.45
      const bodySize = rand(1.5, 8)
      const open = price
      const close = isGreen ? price + bodySize : price - bodySize
      const high = Math.max(open, close) + rand(0.5, 4)
      const low = Math.min(open, close) - rand(0.5, 4)
      candles.push({ open, close, high, low, vol: rand(30, 100), isGreen })
      price = close + rand(-2, 2)
      price = Math.max(price, 20)
    }

    let tailGenerated = false

    const generateTail = (trend) => {
      if (tailGenerated) return
      tailGenerated = true
      for (let i = 0; i < 4; i++) {
        const isGreen = trend === 'up' ? Math.random() > 0.15 : Math.random() < 0.15
        const bodySize = rand(3, 10)
        const open = price
        const close = isGreen ? price + bodySize : price - bodySize
        const high = Math.max(open, close) + rand(0.3, 2)
        const low = Math.min(open, close) - rand(0.3, 2)
        candles.push({ open, close, high, low, vol: rand(60, 100), isGreen })
        price = close
      }
      const startPrice = candles[0].open
      const endPrice = candles[candles.length - 1].close
      klineTrend.value = trend
      klinePct.value = Math.abs((endPrice - startPrice) / startPrice * 100).toFixed(2)
    }

    const padL = 8, padR = 8, padT = 16, padB = 8
    const candleW = (W - padL - padR) / totalCandles
    const bodyW = Math.max(candleW * 0.55, 3)
    const maxVol = 100

    const getAllHighLow = () => {
      const visible = candles.slice(0, candles.length)
      const high = visible.length ? Math.max(...visible.map(c => c.high)) : 120
      const low = visible.length ? Math.min(...visible.map(c => c.low)) : 80
      return { high, low }
    }

    const drawBackground = () => {
      // 深色渐变背景
      const bgGrad = ctx.createLinearGradient(0, 0, W, H)
      bgGrad.addColorStop(0, 'rgba(15, 12, 40, 0.95)')
      bgGrad.addColorStop(0.5, 'rgba(20, 10, 50, 0.92)')
      bgGrad.addColorStop(1, 'rgba(10, 18, 45, 0.95)')
      ctx.fillStyle = bgGrad
      ctx.fillRect(0, 0, W, H)

      // 装饰：左上角放射光晕（紫色）
      const glow1 = ctx.createRadialGradient(W * 0.1, H * 0.15, 0, W * 0.1, H * 0.15, W * 0.4)
      glow1.addColorStop(0, 'rgba(120, 60, 220, 0.12)')
      glow1.addColorStop(1, 'transparent')
      ctx.fillStyle = glow1
      ctx.fillRect(0, 0, W, H)

      // 装饰：右下角放射光晕（蓝色）
      const glow2 = ctx.createRadialGradient(W * 0.85, H * 0.8, 0, W * 0.85, H * 0.8, W * 0.45)
      glow2.addColorStop(0, 'rgba(30, 80, 200, 0.10)')
      glow2.addColorStop(1, 'transparent')
      ctx.fillStyle = glow2
      ctx.fillRect(0, 0, W, H)

      // 水平网格线（4条，带刻度标签）
      const { high, low } = getAllHighLow()
      const priceRange = high - low || 1
      ctx.setLineDash([3, 5])
      for (let i = 0; i <= 3; i++) {
        const y = padT + (H - padT - padB) * i / 3
        const priceLabel = (high - priceRange * i / 3).toFixed(1)

        ctx.strokeStyle = 'rgba(255,255,255,0.06)'
        ctx.lineWidth = 0.5
        ctx.beginPath()
        ctx.moveTo(padL, y)
        ctx.lineTo(W - padR, y)
        ctx.stroke()

        // 价格刻度
        ctx.setLineDash([])
        ctx.fillStyle = 'rgba(255,255,255,0.2)'
        ctx.font = '9px monospace'
        ctx.textAlign = 'right'
        ctx.fillText(priceLabel, W - padR - 2, y - 2)
      }
      ctx.setLineDash([])
      ctx.textAlign = 'left'
    }

    const drawMA = (visibleCandles, priceToY) => {
      const period = 5
      if (visibleCandles.length < period) return
      // MA5 黄色
      ctx.strokeStyle = 'rgba(255, 200, 0, 0.55)'
      ctx.lineWidth = 1
      ctx.setLineDash([])
      ctx.beginPath()
      for (let i = period - 1; i < visibleCandles.length; i++) {
        const avg = visibleCandles.slice(i - period + 1, i + 1).reduce((s, c) => s + c.close, 0) / period
        const x = padL + i * candleW + candleW / 2
        const y = priceToY(avg)
        if (i === period - 1) ctx.moveTo(x, y)
        else ctx.lineTo(x, y)
      }
      ctx.stroke()

      // MA10 蓝紫色
      const period2 = 10
      if (visibleCandles.length < period2) return
      ctx.strokeStyle = 'rgba(130, 100, 255, 0.45)'
      ctx.lineWidth = 1
      ctx.beginPath()
      for (let i = period2 - 1; i < visibleCandles.length; i++) {
        const avg = visibleCandles.slice(i - period2 + 1, i + 1).reduce((s, c) => s + c.close, 0) / period2
        const x = padL + i * candleW + candleW / 2
        const y = priceToY(avg)
        if (i === period2 - 1) ctx.moveTo(x, y)
        else ctx.lineTo(x, y)
      }
      ctx.stroke()
    }

    const startTime = performance.now()
    let stopped = false
    klineStopFn = () => { stopped = true }

    const frame = (now) => {
      if (stopped) return
      const elapsed = now - startTime
      const progress = Math.min(elapsed / duration, 1)
      const easedProgress = 1 - Math.pow(1 - progress, 2)

      let targetDrawn = Math.floor(easedProgress * totalCandles)

      // 后4根未生成时，最多只画到 splitAt
      if (!tailGenerated) {
        targetDrawn = Math.min(targetDrawn, splitAt)
        const result = getResultTrend ? getResultTrend() : null
        if (result) generateTail(result)
      }
      targetDrawn = Math.min(targetDrawn, candles.length)

      const visibleCandles = candles.slice(0, targetDrawn)

      // 动态计算价格范围
      const allHigh = visibleCandles.length ? Math.max(...visibleCandles.map(c => c.high)) : 120
      const allLow = visibleCandles.length ? Math.min(...visibleCandles.map(c => c.low)) : 80
      const priceRange = allHigh - allLow || 1
      const priceToY = (p) => padT + (allHigh - p) / priceRange * (H - padT - padB)

      // 清空并画背景
      ctx.clearRect(0, 0, W, H)
      drawBackground()

      // 画面积图（收盘价连线填充）
      if (visibleCandles.length > 1) {
        ctx.beginPath()
        visibleCandles.forEach((c, i) => {
          const x = padL + i * candleW + candleW / 2
          const y = priceToY(c.close)
          if (i === 0) ctx.moveTo(x, y)
          else ctx.lineTo(x, y)
        })
        const lastX = padL + (visibleCandles.length - 1) * candleW + candleW / 2
        ctx.lineTo(lastX, H)
        ctx.lineTo(padL + candleW / 2, H)
        ctx.closePath()
        const areaGrad = ctx.createLinearGradient(0, 0, 0, H)
        const isUp = klineTrend.value === 'up'
        areaGrad.addColorStop(0, isUp ? 'rgba(38,201,112,0.08)' : 'rgba(239,83,80,0.08)')
        areaGrad.addColorStop(1, 'transparent')
        ctx.fillStyle = areaGrad
        ctx.fill()
      }

      // 画MA线
      drawMA(visibleCandles, priceToY)

      // 画K线
      visibleCandles.forEach((c, i) => {
        const x = padL + i * candleW + (candleW - bodyW) / 2
        const openY = priceToY(c.open)
        const closeY = priceToY(c.close)
        const highY = priceToY(c.high)
        const lowY = priceToY(c.low)
        const color = c.isGreen ? '#26c970' : '#ef5350'

        // 影线
        ctx.strokeStyle = c.isGreen ? 'rgba(38,201,112,0.7)' : 'rgba(239,83,80,0.7)'
        ctx.lineWidth = 1
        ctx.beginPath()
        ctx.moveTo(x + bodyW / 2, highY)
        ctx.lineTo(x + bodyW / 2, lowY)
        ctx.stroke()

        // 实体
        const bodyTop = Math.min(openY, closeY)
        const bodyH = Math.max(Math.abs(openY - closeY), 1.5)
        ctx.fillStyle = color
        ctx.fillRect(x, bodyTop, bodyW, bodyH)

        // 最后一根闪烁光晕
        if (i === visibleCandles.length - 1) {
          const pulse = (Math.sin(now / 200) + 1) / 2
          ctx.fillStyle = c.isGreen
            ? `rgba(38,201,112,${0.1 + pulse * 0.15})`
            : `rgba(239,83,80,${0.1 + pulse * 0.15})`
          ctx.fillRect(x - 3, bodyTop - 3, bodyW + 6, bodyH + 6)
        }
      })

      // 画量柱
      if (vCtx && visibleCandles.length) {
        vCtx.clearRect(0, 0, vW, vH)
        // vol背景
        const volBg = vCtx.createLinearGradient(0, 0, vW, 0)
        volBg.addColorStop(0, 'rgba(15,12,40,0.9)')
        volBg.addColorStop(1, 'rgba(10,18,45,0.9)')
        vCtx.fillStyle = volBg
        vCtx.fillRect(0, 0, vW, vH)

        const maxV = Math.max(...visibleCandles.map(c => c.vol), 1)
        visibleCandles.forEach((c, i) => {
          const x = padL + i * candleW + (candleW - bodyW) / 2
          const vh = (c.vol / maxV) * (vH - 4)
          vCtx.fillStyle = c.isGreen ? 'rgba(38,201,112,0.55)' : 'rgba(239,83,80,0.55)'
          vCtx.fillRect(x, vH - vh, bodyW, vh)
        })
      }

      if (progress < 1 || !tailGenerated) {
        klineAnimFrame = requestAnimationFrame(frame)
      } else {
        resolve()
      }
    }

    klineAnimFrame = requestAnimationFrame(frame)
  })
}

    // 进度条 class 根据步骤切换宽度
    const loadProgressClass = computed(() => {
      if (loadStep.value === 1) return 'progress-step1'
      if (loadStep.value === 2) return 'progress-step2'
      if (loadStep.value === 3) return 'progress-step3'
      return ''
    })

    const status_list = reactive([
      { label: t('msg.dtj'), value: 0 },
      { label: t('msg.ytj'), value: 1 },
      { label: t('msg.yhqx'), value: 2 },
      { label: t('msg.qzwc'), value: 3 },
      { label: t('msg.qzqx'), value: 4 },
      { label: t('msg.djz'), value: 5 },
    ])

    store.dispatch('changefooCheck', 'obj')
    getdetailbyid(20).then(res => { content.value = res.data?.content })

    const initData = () => {
      rot_order().then(res => {
        if (res.code === 0) {
          info.value = { ...res.data }
          store.dispatch('changeobjInfo', info.value)
        }
      })
    }
    initData()

    onMounted(() => {
      showGift.value = true;
      nextTick(() => startHeroKline())
    })

    onBeforeUnmount(() => {
      stopHeroKline()
    })

    const tjOrder = (row) => { push({ name: 'detail', params: { id: row.oid } }) }

    const confirmPwd = () => {
      let id = ''
      if (onceinfo.value.group_data && onceinfo.value.group_data.length > 0) {
        let info = onceinfo.value.group_data?.find(rr => rr.is_pay === 0)
        id = info?.oid
      } else {
        id = onceinfo.value.data?.oid || onceinfo.value.data?.id
      }
      let json = { oid: id, status: 1, pingfen: pinglun.value, pinglun: pingluntext.value }
      do_order(json).then(res => {
        if (res.code === 0) {
          const group_data = onceinfo.value.group_data || []
          if ((!onceinfo.value.data || onceinfo.value.data.duorw === 0)) {
            proxy.$Message({ type: 'success', message: res.info });
            showTj.value = false
            initData()
          } else if (group_data.length == onceinfo.value.data.duorw) {
            proxy.$Message({ type: 'success', message: res.info });
            showTj.value = false
            initData()
          } else {
            submit_order().then(() => { showTj.value = false; initData() })
          }
        } else {
          proxy.$Message({ message: res.info, type: 'error' })
        }
      })
    }

    const cancelPwd = () => { initData() }

    const clickRight = () => {
      if (support.value) { location.href = support.value } else { push('/tel') }
    }

    getsupport().then(res => {
      if (res.code === 0) support.value = res.data[0]?.url
    })

    getHomeData().then(res => {
      if (res.code === 0) {
        monney.value = res.data.balance
        mInfo.value = { ...res.data }
        creditPercent.value = res.data.credit
        level.value = res.data.level
        store.dispatch('changeminfo', res.data || {})
      }
    })

    // 粒子样式
    const getParticleStyle = (i) => ({
      left: `${(i * 31 + 7) % 100}%`,
      top: `${(i * 47 + 13) % 100}%`,
      width: `${4 + (i % 5)}px`,
      height: `${4 + (i % 5)}px`,
      animationDelay: `${(i * 0.4) % 4}s`,
      animationDuration: `${3 + (i % 3)}s`,
      background: i % 3 === 0 ? '#ffd700' : i % 3 === 1 ? '#a855f7' : '#3b82f6'
    })

    // 烟花点样式
    const getFwStyle = (i) => ({
      left: `${(i * 23 + 5) % 90 + 5}%`,
      top: `${(i * 37 + 11) % 70 + 5}%`,
      animationDelay: `${(i * 0.15) % 1.5}s`,
      animationDuration: `${1 + (i % 3) * 0.4}s`,
      background: ['#ffd700','#ff6b6b','#4ecdc4','#a855f7','#3b82f6','#ff9f43','#48dbfb','#ff6b9d'][i % 8]
    })

    // 启动成功结果倒计时
    const startResultCountdown = (duration, onDone) => {
      resultCountdown.value = 100
      const step = 100 / (duration / 50)
      resultCountdownTimer = setInterval(() => {
        resultCountdown.value = Math.max(0, resultCountdown.value - step)
      }, 50)
      setTimeout(() => {
        clearInterval(resultCountdownTimer)
        onDone && onDone()
      }, duration)
    }

    const getDd = async () => {
      if (loading.value) return false
      loading.value = true
      loadStep.value = 1
      loadText.value = t('msg.zzszsj')

      let orderResult = null  // 用来存接口结果
      const time = (info.value.deal_zhuji_time || 1) * 1000
      const time2 = (info.value.deal_shop_time || 2) * 1000
      const totalDuration = time + time2

      // 传入一个getter，让K线动画可以随时获取结果
      startKlineAnimation(totalDuration, () => orderResult)

      setTimeout(async () => {
        loadStep.value = 2
        loadText.value = t('msg.zzppsp')
        const submit = await submit_order()
        // 接口返回后立即设置结果方向
        if (submit?.code === 0) {
          orderResult = 'up'
        } else {
          orderResult = 'down'
        }
        setout(submit, time2)
      }, time)
    }

    const setout = (json, time) => {
      setTimeout(async () => {
        if (json) {
          if (json.code === 1 && json.status === 1) {
            if (klineAnimFrame) cancelAnimationFrame(klineAnimFrame)
            compoundOrderData.value = json.data
            showCompoundOrder.value = true
            loading.value = false
            loadStep.value = 0
            giftRef.value?.checkGiftStatus()
            return
          }

          if (json.code === 0) {
            loadStep.value = 3
            loadText.value = t('msg.ppcg')
            // K线动画已经快结束了，再给500ms跑完
            await new Promise(r => setTimeout(r, 500))

            loading.value = false
            loadStep.value = 0
            showOrderResult.value = true
            resultOrderInfo.value = {
              commission: json.commission || json.data?.commission || '0.00',
              amount: json.amount || json.data?.amount || '0.00'
            }

            startResultCountdown(2000, () => {
              showOrderResult.value = false
              proxy.$Message({ message: json.info, type: 'success' })
              tjOrder(json)
            })
          } else {
            if (klineAnimFrame) cancelAnimationFrame(klineAnimFrame)
            proxy.$Message({ message: json.info, type: 'error' })
            loading.value = false
            loadStep.value = 0
            giftRef.value?.checkGiftStatus()
            return
          }
        } else {
          setout(json, time)
        }
      }, time)
    }

    const generateRandomComment = () => {
      const comments = [
        "I absolutely love this product! It exceeded my expectations.",
        "Excellent quality and great value for money.",
        "This is exactly what I was looking for. Highly recommended!",
        "The quality is outstanding and it works perfectly.",
        "Very satisfied with my purchase. Will buy again!",
        "This product is amazing and worth every penny.",
        "Fast shipping and the product is even better than described.",
        "I'm really impressed with the quality and performance.",
        "This has made my life so much easier. Thank you!",
        "Great product with excellent craftsmanship.",
        "Better than I expected! The quality is superb.",
        "I would definitely recommend this to my friends.",
        "Perfect in every way. No complaints at all!",
        "The attention to detail is remarkable.",
        "This product is a game-changer! So glad I bought it.",
        "High-quality materials and excellent workmanship.",
        "Exceeded my expectations in every aspect.",
        "I'm very happy with this purchase. It's fantastic!",
        "Well designed and very functional. Love it!",
        "This is by far the best product I've bought this year."
      ];
      pingluntext.value = comments[Math.floor(Math.random() * comments.length)];
    };

    const copyInvite = (xinxi) => {
      try {
        if (navigator && navigator.clipboard && userinfo.value?.invite_code) {
          navigator.clipboard.writeText(userinfo.value.invite_code)
          proxy.$toast?.success && proxy.$toast.success(xinxi)
        }
      } catch (e) {
        const ta = document.createElement('textarea')
        ta.value = userinfo.value?.invite_code || ''
        document.body.appendChild(ta)
        ta.select()
        document.execCommand('copy')
        document.body.removeChild(ta)
      }
    }

    const selectCompoundOrderOption = async (optionId) => {
      try {
        const result = await start_compound_order({ option_id: optionId })
        if (result.code === 0) {
          proxy.$Message({ message: result.info, type: 'success' })
          showCompoundOrder.value = false
          compoundOrderData.value = null
          initData()
          tjOrder(result)
        } else {
          proxy.$Message({ message: result.info, type: 'error' })
        }
      } catch (error) {
        proxy.$Message({ message: t('msg.czsb'), type: 'error' })
      }
    }

    const skipCompoundOrder = () => {
      showCompoundOrder.value = false
      compoundOrderData.value = null
      proxy.$Message({ message: '下单成功！', type: 'success' })
    }

    return {
  push, heroKlineRef,
  pingluntext, generateRandomComment, pinglun, info, currency, level, level_show,
  loading, getDd, clickRight, confirmPwd, tjOrder, showTj, onceinfo, formatTime,
  cancelPwd, content, loadText, status_list, loadImg, activeTab, monney, mInfo,
  userinfo, creditPercent, copyInvite, showGift, showCompoundOrder, compoundOrderData,
  selectCompoundOrderOption, skipCompoundOrder,
  giftRef, loadStep, loadProgressClass, showOrderResult, resultOrderInfo, resultCountdown,
  getParticleStyle, getFwStyle,klineCanvasRef, klineVolRef, klineTrend, klinePct,
}
  }
}
</script>

<style lang="scss" scoped>
@import '@/styles/theme.scss';

.hero-info{
    display: flex;
    justify-content: space-between;
    margin: 30px 30px;
    font-size: 28px;
    color: $textColor;
    font-weight: 500;
}

/* ════════════════════════════════════════════════════════════
   加载遮罩 - 优化版
   ════════════════════════════════════════════════════════════ */

.loading-fade-enter-active { animation: loadingFadeIn 0.4s ease forwards; }
.loading-fade-leave-active { animation: loadingFadeOut 0.4s ease forwards; }

@keyframes loadingFadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
@keyframes loadingFadeOut {
  from { opacity: 1; }
  to { opacity: 0; }
}

.img_loading {
  position: fixed;
  inset: 0;
  background: linear-gradient(160deg, #0d0d2b 0%, #1a0533 40%, #0a1628 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 888;
  overflow: hidden;
}

/* 背景粒子 */
.loading-particles {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.particle {
  position: absolute;
  border-radius: 50%;
  animation: particleFloat linear infinite;
  opacity: 0.6;
}

@keyframes particleFloat {
  0% { transform: translateY(0) scale(1); opacity: 0.6; }
  50% { opacity: 1; }
  100% { transform: translateY(-120px) scale(0.3); opacity: 0; }
}

/* 主体 */
.loading-body {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 88%;
  max-width: 640px;
}

/* K线图容器 */
.loading-kline-wrap {
  width: 100%;
  background: rgba(0, 0, 0, 0.55);
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  padding: 16px;
  margin-bottom: 40px;
  box-sizing: border-box;
  box-shadow: 0 0 40px rgba(38, 201, 112, 0.15), 0 8px 32px rgba(0,0,0,0.5);
}

.kline-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.kline-symbol {
  font-size: 24px;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.85);
  letter-spacing: 0.5px;
}

.kline-badge {
  font-size: 22px;
  font-weight: 700;
  padding: 4px 16px;
  border-radius: 20px;

  &--up {
    color: #26c970;
    background: rgba(38, 201, 112, 0.15);
    border: 1px solid rgba(38, 201, 112, 0.3);
  }

  &--down {
    color: #ef5350;
    background: rgba(239, 83, 80, 0.15);
    border: 1px solid rgba(239, 83, 80, 0.3);
  }
}

.loading-kline-canvas {
  width: 100%;
  height: 200px;
  display: block;
  border-radius: 8px;
}

.kline-footer {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 8px;
}

.kline-label {
  font-size: 20px;
  color: rgba(255, 255, 255, 0.35);
  flex-shrink: 0;
}

.loading-vol-canvas {
  flex: 1;
  height: 50px;
  display: block;
}


@keyframes neonBorder {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

@keyframes gifShine {
  0%, 50% { left: -80%; }
  100% { left: 150%; }
}

/* 文字区域 */
.loading-text-wrap {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 28px;
}

.loading-step-text {
  font-size: 34px;
  font-weight: 700;
  color: #ffffff;
  text-shadow: 0 0 20px rgba(168, 85, 247, 0.8);
  letter-spacing: 1px;
}

/* 跳动点 */
.loading-dots {
  display: flex;
  gap: 8px;
  span {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #a855f7;
    display: block;
    animation: dotBounce 1.2s ease-in-out infinite;
    &:nth-child(2) { animation-delay: 0.2s; background: #3b82f6; }
    &:nth-child(3) { animation-delay: 0.4s; background: #ffd700; }
  }
}

@keyframes dotBounce {
  0%, 80%, 100% { transform: translateY(0); }
  40% { transform: translateY(-10px); }
}

/* 进度条 */
.loading-progress {
  width: 100%;
  height: 6px;
  background: rgba(255,255,255,0.1);
  border-radius: 3px;
  overflow: hidden;
  margin-bottom: 40px;
}

.loading-progress-bar {
  height: 100%;
  border-radius: 3px;
  background: linear-gradient(90deg, #a855f7, #3b82f6, #ffd700);
  background-size: 200% 100%;
  animation: progressShimmer 1.5s linear infinite;
  transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);

  &.progress-step1 { width: 30%; }
  &.progress-step2 { width: 65%; }
  &.progress-step3 { width: 100%; }
}

@keyframes progressShimmer {
  0% { background-position: 0% 50%; }
  100% { background-position: 200% 50%; }
}

/* 步骤指示器 */
.loading-steps {
  display: flex;
  align-items: center;
  width: 100%;
  justify-content: center;
  gap: 0;
}

.step-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

.step-dot {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  border: 3px solid rgba(255,255,255,0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  font-weight: 700;
  color: rgba(255,255,255,0.4);
  transition: all 0.4s ease;
  background: rgba(255,255,255,0.05);

  .step-item.active & {
    border-color: #a855f7;
    color: #fff;
    background: rgba(168, 85, 247, 0.3);
    box-shadow: 0 0 20px rgba(168, 85, 247, 0.6);
    animation: stepPulse 1.2s ease-in-out infinite;
  }

  .step-item.done & {
    border-color: #ffd700;
    color: #ffd700;
    background: rgba(255, 215, 0, 0.2);
    box-shadow: 0 0 16px rgba(255, 215, 0, 0.5);
    animation: none;
  }
}

@keyframes stepPulse {
  0%, 100% { box-shadow: 0 0 20px rgba(168, 85, 247, 0.6); }
  50% { box-shadow: 0 0 35px rgba(168, 85, 247, 1); }
}

.step-label {
  font-size: 22px;
  color: rgba(255,255,255,0.5);
  .step-item.active & { color: #fff; }
  .step-item.done & { color: #ffd700; }
}

.step-line {
  width: 80px;
  height: 3px;
  background: rgba(255,255,255,0.1);
  border-radius: 2px;
  margin-bottom: 32px;
  transition: background 0.4s ease;
  &.active { background: linear-gradient(90deg, #ffd700, #a855f7); }
}

/* ════════════════════════════════════════════════════════════
   抢单成功结果遮罩
   ════════════════════════════════════════════════════════════ */

.result-slide-enter-active { animation: resultSlideIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
.result-slide-leave-active { animation: resultSlideOut 0.4s ease-in forwards; }

@keyframes resultSlideIn {
  from { opacity: 0; transform: scale(0.85); }
  to { opacity: 1; transform: scale(1); }
}
@keyframes resultSlideOut {
  from { opacity: 1; transform: scale(1); }
  to { opacity: 0; transform: scale(0.9); }
}

.order-result-mask {
  position: fixed;
  inset: 0;
  background: rgba(10, 5, 30, 0.88);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 889;
  backdrop-filter: blur(8px);
}

/* 烟花 */
.result-fireworks {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.fw-dot {
  position: absolute;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  animation: fwBurst 1.4s ease-out infinite;
}

@keyframes fwBurst {
  0% {
    transform: scale(1);
    opacity: 1;
    box-shadow: 0 0 0 0 currentColor,
                18px -18px 0 0 currentColor,
                -18px -18px 0 0 currentColor,
                18px 18px 0 0 currentColor,
                -18px 18px 0 0 currentColor,
                26px 0 0 0 currentColor,
                -26px 0 0 0 currentColor;
  }
  100% {
    transform: scale(0.2);
    opacity: 0;
    box-shadow: 0 0 0 0 transparent,
                55px -55px 0 0 transparent,
                -55px -55px 0 0 transparent,
                55px 55px 0 0 transparent,
                -55px 55px 0 0 transparent,
                75px 0 0 0 transparent,
                -75px 0 0 0 transparent;
  }
}

/* 结果卡片 */
.result-card {
  position: relative;
  z-index: 1;
  width: 80%;
  max-width: 580px;
  background: linear-gradient(145deg, #1a0533ee, #0d1a3aee);
  border: 1px solid rgba(255, 215, 0, 0.3);
  border-radius: 32px;
  padding: 50px 40px 36px;
  text-align: center;
  box-shadow: 0 30px 80px rgba(0,0,0,0.7), 0 0 60px rgba(255,215,0,0.15);
  overflow: hidden;
}

/* 卡片顶部光晕 */
.result-card-halo {
  position: absolute;
  top: -80px;
  left: 50%;
  transform: translateX(-50%);
  width: 300px;
  height: 300px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(255,215,0,0.2) 0%, transparent 70%);
  pointer-events: none;
}

/* 成功图标 */
.result-icon-wrap {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 130px;
  height: 130px;
  margin-bottom: 28px;
}

.result-icon-ring {
  position: absolute;
  border-radius: 50%;
  border: 2px solid rgba(255, 215, 0, 0.5);
  animation: iconRingExpand 2s ease-out infinite;

  &.ring-outer { width: 130px; height: 130px; animation-delay: 0s; }
  &.ring-inner { width: 100px; height: 100px; animation-delay: 0.4s; }
}

@keyframes iconRingExpand {
  0% { transform: scale(0.8); opacity: 1; }
  100% { transform: scale(1.3); opacity: 0; }
}

.result-icon {
  font-size: 72px;
  z-index: 1;
  animation: iconPop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

@keyframes iconPop {
  0% { transform: scale(0) rotate(-30deg); }
  60% { transform: scale(1.2) rotate(5deg); }
  100% { transform: scale(1) rotate(0deg); }
}

.result-title {
  font-size: 48px;
  font-weight: 800;
  color: #ffd700;
  text-shadow: 0 0 30px rgba(255,215,0,0.7);
  margin-bottom: 10px;
  animation: titleGlow 2s ease-in-out infinite alternate;
}

@keyframes titleGlow {
  from { text-shadow: 0 0 20px rgba(255,215,0,0.5); }
  to { text-shadow: 0 0 40px rgba(255,215,0,0.9), 0 0 60px rgba(255,215,0,0.4); }
}

.result-subtitle {
  font-size: 26px;
  color: rgba(255,255,255,0.65);
  margin-bottom: 28px;
}

/* 分隔线 */
.result-divider {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 28px;
  justify-content: center;
}

.divider-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255,215,0,0.5);
}

.divider-line {
  flex: 1;
  max-width: 160px;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(255,215,0,0.4), transparent);
}

/* 信息网格 */
.result-info-grid {
  display: flex;
  gap: 16px;
  margin-bottom: 28px;
}

.result-info-item {
  flex: 1;
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 16px;
  padding: 20px 16px;
}

.info-label {
  font-size: 22px;
  color: rgba(255,255,255,0.5);
  margin-bottom: 8px;
}

.info-value {
  font-size: 34px;
  font-weight: 800;
  color: #ffffff;

  &--highlight {
    color: #4ade80;
    text-shadow: 0 0 16px rgba(74, 222, 128, 0.5);
  }
}

.result-tip {
  font-size: 22px;
  color: rgba(255,255,255,0.35);
  margin-bottom: 16px;
}

/* 倒计时条 */
.result-countdown-bar {
  height: 4px;
  background: rgba(255,255,255,0.1);
  border-radius: 2px;
  overflow: hidden;
}

.result-countdown-fill {
  height: 100%;
  background: linear-gradient(90deg, #ffd700, #f5a623);
  border-radius: 2px;
  transition: width 0.05s linear;
}

/* ════════════════════════════════════════════════════════════
   以下为原有样式，完全不动
   ════════════════════════════════════════════════════════════ */

.hero-section {
  position: relative;
  width: 100%;
  padding-bottom: 0;
  overflow: visible;
  z-index: 0;
}
.hero-bg {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 400px;
  border-bottom-left-radius: 48px;
  border-bottom-right-radius: 48px;
  overflow: hidden;
  z-index: 0;
}
.hero-bg-img { width: 100%; object-fit: cover; object-position: center 30%; display: block; }
.hero-kline-canvas { width: 100%; height: 100%; display: block; }
.hero-bg-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(160deg, rgba(18,28,45,0.82) 0%, rgba(15,22,38,0.70) 60%, rgba(10,16,30,0.60) 100%);
}
.hero-nav {
  position: relative;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 48px 32px 20px;
}
.hero-nav-left { display: flex; align-items: center; gap: 18px; }
.hero-avatar {
  width: 88px; height: 88px; border-radius: 50%; overflow: hidden;
  border: 3px solid rgba(255,255,255,0.6); flex-shrink: 0;
  img { width: 100%; height: 100%; object-fit: cover; }
}
.hero-greeting { font-size: 34px; font-weight: 700; color: #ffffff; letter-spacing: 0.3px; }
.hero-rank { font-size: 30px; font-weight: 700; color: #ffffff; letter-spacing: 0.5px; }
.hero-subtitle {
  position: relative; z-index: 2; padding: 16px 32px 28px;
  font-size: 28px; line-height: 1.55; color: rgba(255,255,255,0.90);
  strong { color: #ffffff; font-weight: 800; }
}
.hero-cards { position: relative; z-index: 2; display: flex; gap: 20px; padding: 0 24px; padding-bottom: 60px; top: 70px; }
.hero-card {
  flex: 1; background: $bg-card; border-radius: 24px; padding: 28px 24px 28px;
  position: relative; box-shadow: 0 8px 32px rgba(0,0,0,0.4); text-align: left; overflow: visible;
}
.hero-card-icon {
  position: absolute; top: -48px; left: 80%; transform: translateX(-50%);
  width: 86px; height: 86px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 16px rgba(0,0,0,0.22);
  &--wallet { background: linear-gradient(135deg, #7b6cf6 0%, #5b4fcf 100%); }
  &--ad { background: linear-gradient(135deg, #8b6cf6 0%, #6a3fcf 100%); }
}
.hero-card-label { font-size: 26px; font-weight: 700; color: $textColor; margin-top: 8px; margin-bottom: 10px; }
.hero-card-amount { display: flex; align-items: baseline; gap: 8px; margin-bottom: 10px; }
.hero-card-amount--negative { font-size: 38px; font-weight: 800; color: $theme; }
.hero-card-amount--zero { font-size: 38px; font-weight: 800; color: $theme; }
.hero-card-currency { font-size: 26px; font-weight: 600; color: $textSecondary; }
.hero-card-desc { font-size: 24px; line-height: 1.5; color: $textSecondary; }
.hero-arc { position: relative; z-index: 1; width: 100%; height: 0; }
:deep(.ad-showcase) { margin-top: -20px; }

.below-showcase { width: 100%; padding: 0 24px 40px; box-sizing: border-box; background: $bg-primary;margin-top: 80px; }
.notes-card { position: relative; border-radius: 20px; overflow: hidden; margin-bottom: 32px; min-height: 180px; }
.notes-card-bg {
  position: absolute; inset: 0; z-index: 0;
  img.notes-bg-img { width: 100%; height: 100%; object-fit: cover; object-position: center 40%; display: block; }
  .notes-bg-overlay { position: absolute; inset: 0; background: linear-gradient(120deg, rgba(0,0,0,0.16) 0%, rgba(0,0,0,0.31) 100%); }
}
.notes-content { position: relative; z-index: 1; padding: 32px 28px; text-align: left; }
.notes-title { font-size: 32px; font-weight: 800; color: #ffffff; margin-bottom: 18px; }
.notes-body { font-size: 26px; line-height: 1.7; color: rgba(255,255,255,0.88); p { margin: 0 0 6px; } }
.copyright { text-align: center; font-size: 24px; color: $textMuted; padding: 8px 0 24px; }

.obj {
  display: flex; flex-direction: column;
  .content {
    flex: 1; background-color: $bg-primary; padding: 30px; overflow: auto;
    border-top-left-radius: 30px; border-top-right-radius: 30px;
    padding-top: 0px; padding-bottom: 40px; color: $textColor;
    .qd { margin-top: 40px; text-align: left;
      .title { font-size: 28px; font-weight: 600; color: #333; }
      .sub { font-size: 24px; line-height: 30px; margin-top: 10px; color: #333; }
    }
  }
  .hy_box {
    height: 230px; width: 100%; padding: 25px; color: #fff;
    background-image: url('~@/assets/images/home/hybj.png');
    background-size: 100% 100%; border-radius: 10px; overflow: hidden; position: relative; text-align: left;
    .t { margin-bottom: 18px; .img { width: 65px; height: auto; margin-right: 20px; vertical-align: middle; } .text { font-size: 27px; } }
    .b { padding-left: 85px; font-size: 18px; .sub { .line { margin: 0 22px; } } }
    .txlevel {
      position: absolute; right: 25px; top: 50%; transform: translateY(-50%);
      width: 150px; height: 60px; padding: 0; font-size: 24px;
      color: #2620ce; background-color: #e2e6ff; border-radius: 20px; border: none;
    }
  }
  :deep(.van-dialog) {
    .van-dialog__header { text-align: left; padding: 20px 40px; font-weight: 600; }
    .list {
      padding: 0 40px; box-shadow: none; max-height: 40vh; overflow: auto;
      display: flex; flex-direction: column;
      .tops { margin-bottom: 0; color: #333; .span { margin-right: 24px; } }
      .box {
        padding: 15px; border: 2px solid #ccc; margin-top: 24px;
        &:first-child { margin-top: 0; }
        .value0 { padding: 3px 10px; background-color: red; color: #fff; }
        .value1 { padding: 3px 10px; background-color: #07c160; color: #fff; }
      }
    }
    .van-dialog__content { overflow: auto; }
    .van-dialog__footer { margin-top: 40px; .van-dialog__confirm { color: $theme; } }
  }
  .list {
    padding: 30px; box-shadow: $shadow; color: $subtext; text-align: left;
    margin-top: 40px; border-radius: 10px;
    .top { display: flex; justify-content: space-between; margin-bottom: 35px; .time { font-size: 16px; } .tab { font-size: 20px; color: $theme; } }
    .cet { display: flex; background-color: #fafafa; padding: 10px 0; .img { width: 100%; height: 180PX; } .text { font-size: 20px; } }
    .monney { margin-top: 30px; .tent { display: flex; justify-content: space-between; font-size: 24px; .span { width: 120px; color: #333; } } }
    .van-button { font-size: 32px; margin-top: 50px; }
  }
}

.img_loading {
  position: absolute; left: 0; top: 0; width: 100%; height: 100%;
  background-color: rgba(151,151,151,0.821); color: #fff;
  display: flex; flex-direction: column; justify-content: center;
  text-align: center; font-size: 32px; font-weight: 600;
  .img { width: 80%; background-color: rgba(0,0,0,0.5); margin: 0 auto 30px; border-radius: 12px; }
  z-index: 888;
}

.pinglun {
  margin: 20px 30px; margin-bottom: 0px; font-size: 26px; color: #000000;
  display: flex; flex-direction: column; align-items: center; font-weight: 900;
  .pingluna { display: flex; flex-direction: row; justify-content: space-between; align-items: center; width: 100%; }
  .pinglunb { margin-top: 20px; width: 90%; border: 1px solid #dadada; border-radius: 5px; }
}

.compound-order-modal { padding: 20px; overflow-y: auto; max-height: 60vh; background-color: #fff; }
.compound-order-header {
  text-align: center; margin-bottom: 30px;
  .celebration-icon { font-size: 48px; margin-bottom: 16px; }
  h3 { font-size: 32px; font-weight: 700; color: #000; margin: 0 0 16px 0; }
  .compound-order-desc { font-size: 26px; line-height: 1.6; color: #000; margin: 0; }
}
.compound-order-options { margin-bottom: 30px; }
.compound-option-card {
  background: #f8f9fa; border: 2px solid #e9ecef; border-radius: 16px;
  padding: 24px; margin-bottom: 16px; cursor: pointer; transition: all 0.3s ease;
  &:hover { border-color: #007bff; background: #fff; box-shadow: 0 4px 12px rgba(0,123,255,0.15); }
  .option-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;
    h4 { font-size: 28px; font-weight: 600; color: #333; margin: 0; }
  }
  .option-desc { font-size: 24px; color: #666; line-height: 1.5; margin-bottom: 16px; }
}
.compound-order-footer {
  .skip-btn { background: #f8f9fa; color: #666; border: 1px solid #dee2e6; font-size: 26px; border-radius: 8px; padding: 16px 0; }
}
</style>