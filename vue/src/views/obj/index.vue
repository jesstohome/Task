<template>
  <div class="obj home">

    <!-- ═══════════════════════════════════════════════════════
         加载遮罩（原有，不动）
    ═══════════════════════════════════════════════════════ -->
    <div class="img_loading" v-if="loading">
      <img :src="loadImg" class="img" alt="">
      <div>{{ loadText }}</div>
    </div>

    <!-- ═══════════════════════════════════════════════════════
         ① 新增：顶部英雄区（Hero Section）
         深色半透明背景 + 汽车背景图，底部白色大圆弧过渡
    ═══════════════════════════════════════════════════════ -->
    <div class="hero-section">

      <!-- 背景图层 -->
      <div class="hero-bg">
        <img :src="require('@/assets/images/starting_bg.png')" alt="" class="hero-bg-img" />
        <!-- <div class="hero-bg-overlay"></div> -->
      </div>

      <!-- 顶部导航行 -->
      <div class="hero-nav">
        <!-- 左：头像 + 用户名 -->
        <div class="hero-nav-left">
          <div class="hero-avatar">
            <img :src="userinfo?.headpic" alt="avatar" />
          </div>
          <span class="hero-greeting">Hi, {{userinfo?.username}} 👋</span>
        </div>
        <!-- 右：等级标签 -->
        <div class="hero-rank">VIP {{ level }}</div>
      </div>

      <!-- 副标题文字 -->
      <div class="hero-subtitle">
        Join <strong>65,000</strong> others and learn the secrets to <strong>SEO</strong> success with our weekly blog posts.
      </div>

      <!-- 两张余额卡片 -->
      <div class="hero-cards">

        <!-- 左卡：Wallet Balance -->
        <div class="hero-card">
          <!-- 卡片顶部圆形图标 -->
          <div class="hero-card-icon hero-card-icon--wallet">
            <img :src="require('@/assets/images/usdt.png')" alt="" class="hero-bg-img" />
          </div>
          <div class="hero-card-label">Wallet Balance</div>
          <div class="hero-card-amount">
            <span class="hero-card-amount--negative">{{monney}}</span>
            <span class="hero-card-currency">{{currency}}</span>
          </div>
          <div class="hero-card-desc">
            The total balance reflects both the deposited amount and profits earned
          </div>
        </div>

        <!-- 右卡：Advertising salary -->
        <div class="hero-card">
          <!-- 卡片顶部圆形图标 -->
          <div class="hero-card-icon hero-card-icon--ad">
            <img :src="require('@/assets/images/qianbao.png')" alt="" class="hero-bg-img" />
          </div>
          <div class="hero-card-label">Advertising salary</div>
          <div class="hero-card-amount">
            <span class="hero-card-amount--zero">{{mInfo.yon3}}</span>
            <span class="hero-card-currency">{{currency}}</span>
          </div>
          <div class="hero-card-desc">
            Fixed balance where there is a mixed product pending in process.
          </div>
        </div>

      </div>

      <!-- 底部白色弧形（让hero平滑过渡到白色区域） -->
      <div class="hero-arc"></div>
    </div>

    <!-- ═══════════════════════════════════════════════════════
         ② AdShowcase 组件（已有，不改动）
    ═══════════════════════════════════════════════════════ -->
    <AdShowcase
      v-if="info && mInfo"
      :completed="info.day_completed_count || 0"
      :total="info.order_num || 0"
      :today-salary="mInfo.yon1 || 0"
      @match="getDd"
    />

    <!-- ═══════════════════════════════════════════════════════
         ③ 新增：AdShowcase 下方区块
         Important Notes + 版权
    ═══════════════════════════════════════════════════════ -->
    <div class="below-showcase">

      <!-- Important Notes 卡片 -->
      <div class="notes-card">
        <!-- 背景图（半透明叠加汽车图片） -->
        <div class="notes-card-bg">
          <img :src="require('@/assets/images/notice.png')" alt="" class="notes-bg-img" />
          <div class="notes-bg-overlay"></div>
        </div>
        <!-- 内容 -->
        <div class="notes-content">
          <div class="notes-title">Important Notes</div>
          <div class="notes-body">
            <p>* Online Support Hours 09:00 - 21:59</p>
            <p>- For any further questions, Please contact Online Customer Service</p>
          </div>
        </div>
      </div>

      <!-- 版权信息 -->
      <div class="copyright">©1999-2026 skyrise</div>

    </div>

    <!-- ═══════════════════════════════════════════════════════
         原有弹窗（完全保留）
    ═══════════════════════════════════════════════════════ -->
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

  </div>
</template>

<script>
import { ref, getCurrentInstance, reactive, computed } from 'vue';
import { rot_order, submit_order, order_info, do_order } from '@/api/order/index'
import store from '@/store/index'
import { getdetailbyid, getHomeData } from '@/api/home/index.js'
import { formatTime } from '@/api/format.js'
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n'
import { getsupport } from '@/api/tel/index'
import AdShowcase from '@/components/adshowcase.vue'

export default {
  components: { AdShowcase },
  setup() {
    const { t } = useI18n()
    const userinfo = ref(store.state.userinfo)
    const { push } = useRouter();
    const { proxy } = getCurrentInstance()
    const level_show = ref(false)
    const loading = ref(false)
    const loadText = ref('')
    const loadImg = ref('')
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

    const getDd = async () => {
      if (loading.value) return false
      loading.value = true
      loadText.value = t('msg.zzszsj')
      loadImg.value = require('@/assets/images/1.gif')
      let submit = null
      let time = (info.value.deal_zhuji_time || 1) * 1000
      let time2 = (info.value.deal_shop_time || 2) * 1000
      setTimeout(async () => {
        loadText.value = t('msg.zzppsp')
        loadImg.value = require('@/assets/images/2.gif')
        submit = await submit_order()
        setout(submit, time2)
      }, time);
    }

    const setout = (json, time) => {
      setTimeout(() => {
        if (json) {
          if (json.code === 0) {
            loadImg.value = require('@/assets/images/3.gif')
            loadText.value = t('msg.ppcg')
            setTimeout(() => {
              proxy.$Message({ message: json.info, type: 'success' })
              tjOrder(json)
            }, 1000)
          } else {
            proxy.$Message({ message: json.info, type: 'error' })
            loading.value = false
          }
        } else {
          setout(json, time)
        }
      }, time)
    }

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

    return {
      pingluntext, generateRandomComment, pinglun, info, currency, level, level_show,
      loading, getDd, clickRight, confirmPwd, tjOrder, showTj, onceinfo, formatTime,
      cancelPwd, content, loadText, status_list, loadImg, activeTab, monney, mInfo,
      userinfo, creditPercent, copyInvite
    }
  }
}
</script>

<style lang="scss" scoped>

/* ════════════════════════════════════════════════════════════
   ① 顶部英雄区 Hero Section
   ════════════════════════════════════════════════════════════ */

.hero-section {
  position: relative;
  width: 100%;
  /* 底部留出足够空间给白色卡片下沿 */
  padding-bottom: 0;
  /* 底部白色弧形需要overflow: visible让卡片探出 */
  overflow: visible;
  z-index: 0;
}

/* 背景图 + 深色遮罩 */
.hero-bg {
  position: absolute;
  inset: 0;
  /* 底部弧形裁切：让英雄区视觉上呈圆弧底边 */
  border-bottom-left-radius: 48px;
  border-bottom-right-radius: 48px;
  overflow: hidden;
  z-index: 0;
}
.hero-bg-img {
  width: 100%;
  object-fit: cover;
  object-position: center 30%;
  display: block;
}
.hero-bg-overlay {
  position: absolute;
  inset: 0;
  /* 深色半透明叠加，与图中一致：深蓝灰色调 */
  background: linear-gradient(
    160deg,
    rgba(18, 28, 45, 0.82) 0%,
    rgba(15, 22, 38, 0.70) 60%,
    rgba(10, 16, 30, 0.60) 100%
  );
}

/* 顶部导航行 */
.hero-nav {
  position: relative;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 48px 32px 20px;
}
.hero-nav-left {
  display: flex;
  align-items: center;
  gap: 18px;
}
.hero-avatar {
  width: 88px;
  height: 88px;
  border-radius: 50%;
  overflow: hidden;
  border: 3px solid rgba(255,255,255,0.6);
  flex-shrink: 0;
  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
}
.hero-greeting {
  font-size: 34px;
  font-weight: 700;
  color: #ffffff;
  letter-spacing: 0.3px;
}
.hero-rank {
  font-size: 30px;
  font-weight: 700;
  color: #ffffff;
  letter-spacing: 0.5px;
  /* 图中右侧等级文字白色、粗体 */
}

/* 副标题文字 */
.hero-subtitle {
  position: relative;
  z-index: 2;
  padding: 16px 32px 28px;
  font-size: 28px;
  line-height: 1.55;
  color: rgba(255, 255, 255, 0.90);
  text-align: left;
  strong {
    color: #ffffff;
    font-weight: 800;
  }
}

/* 两张余额卡片行 */
.hero-cards {
  position: relative;
  z-index: 2;
  display: flex;
  gap: 20px;
  padding: 0 24px;
  /* 卡片底部探出英雄区，形成"悬浮"感 */
  padding-bottom: 60px;
  top: 70px;
}

/* 单张卡片 */
.hero-card {
  flex: 1;
  background: #ffffff;
  border-radius: 24px;
  padding: 28px 24px 28px;
  position: relative;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
  text-align: left;
  overflow: visible;
}

/* 卡片顶部圆形图标（探出卡片上边缘） */
.hero-card-icon {
  position: absolute;
  top: -48px;
  left: 80%;
  transform: translateX(-50%);
  width: 86px;
  height: 86px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 26px;
  font-weight: 800;
  color: #fff;
  box-shadow: 0 4px 16px rgba(0,0,0,0.22);
  &--wallet {
    /* 美元图标：紫色/蓝紫 */
    background: linear-gradient(135deg, #7b6cf6 0%, #5b4fcf 100%);
  }
  &--ad {
    /* 广告图标：紫色偏深 */
    background: linear-gradient(135deg, #8b6cf6 0%, #6a3fcf 100%);
  }
}

.hero-card-label {
  font-size: 26px;
  font-weight: 700;
  color: #1a1a2e;
  margin-top: 8px;
  margin-bottom: 10px;
}

.hero-card-amount {
  display: flex;
  align-items: baseline;
  gap: 8px;
  margin-bottom: 10px;
}
.hero-card-amount--negative {
  /* 负数金额：蓝紫色 */
  font-size: 38px;
  font-weight: 800;
  color: #4c4cef;
}
.hero-card-amount--zero {
  /* 零余额：蓝紫色，与左卡一致 */
  font-size: 38px;
  font-weight: 800;
  color: #4c4cef;
}
.hero-card-currency {
  font-size: 26px;
  font-weight: 600;
  color: #555;
}

.hero-card-desc {
  font-size: 24px;
  line-height: 1.5;
  color: #000;
}

/* 英雄区底部白色弧形过渡
   让英雄区与AdShowcase之间平滑衔接 */
.hero-arc {
  position: relative;
  z-index: 1;
  width: 100%;
  height: 0;
  /* 弧形由 hero-section 的 padding-bottom + 卡片定位实现
     此元素不需要额外高度 */
}

/* ════════════════════════════════════════════════════════════
   ② AdShowcase 上方的间距补偿
   （卡片探出hero-section，需要给AdShowcase留出空白）
   ════════════════════════════════════════════════════════════ */
/* AdShowcase组件内部已有 Start Promoting 标题，
   给整个页面的AdShowcase容器加上顶部margin */
:deep(.ad-showcase) {
  margin-top: -20px;  /* 微调让弧形区域和AdShowcase平滑衔接 */
}

/* ════════════════════════════════════════════════════════════
   ③ AdShowcase 下方区块
   ════════════════════════════════════════════════════════════ */

.below-showcase {
  width: 100%;
  padding: 0 24px 40px;
  box-sizing: border-box;
  background: #ffffff;
}

/* Important Notes 卡片 */
.notes-card {
  position: relative;
  border-radius: 20px;
  overflow: hidden;
  margin-bottom: 32px;
  min-height: 180px;
}
.notes-card-bg {
  position: absolute;
  inset: 0;
  z-index: 0;
  img.notes-bg-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center 40%;
    display: block;
  }
  /* 深色半透明叠加，颜色比英雄区略浅，偏蓝灰 */
  .notes-bg-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, rgb(0 0 0 / 16%) 0%, rgb(0 0 0 / 31%) 100%);
  }
}
.notes-content {
  position: relative;
  z-index: 1;
  padding: 32px 28px;
  text-align: left;
}
.notes-title {
  font-size: 32px;
  font-weight: 800;
  color: #ffffff;
  margin-bottom: 18px;
}
.notes-body {
  font-size: 26px;
  line-height: 1.7;
  color: rgba(255, 255, 255, 0.88);
  p {
    margin: 0 0 6px;
  }
}

/* 版权信息 */
.copyright {
  text-align: center;
  font-size: 24px;
  color: #aaaaaa;
  padding: 8px 0 24px;
}

/* ════════════════════════════════════════════════════════════
   ④ 原有 .obj / .content 样式（完全保留）
   ════════════════════════════════════════════════════════════ */

.obj {
  display: flex;
  flex-direction: column;
  padding-bottom: 160px !important;

  .content {
    flex: 1;
    background-color: #fff;
    padding: 30px;
    overflow: auto;
    border-top-left-radius: 30px;
    border-top-right-radius: 30px;
    padding-top: 0px;
    padding-bottom: 650px;
    color: #333;


    .qd {
      margin-top: 40px;
      text-align: left;
      .title { font-size: 28px; font-weight: 600; color: #333; }
      .sub { font-size: 24px; line-height: 30px; margin-top: 10px; color: #333; }
    }
  }

  .hy_box {
    height: 230px;
    width: 100%;
    padding: 25px;
    color: #fff;
    background-image: url('~@/assets/images/home/hybj.png');
    background-size: 100% 100%;
    border-radius: 10px;
    overflow: hidden;
    position: relative;
    text-align: left;
    .t {
      margin-bottom: 18px;
      .img { width: 65px; height: auto; margin-right: 20px; vertical-align: middle; }
      .text { font-size: 27px; }
    }
    .b {
      padding-left: 85px;
      font-size: 18px;
      .sub { .line { margin: 0 22px; } }
    }
    .txlevel {
      position: absolute;
      right: 25px;
      top: 50%;
      transform: translateY(-50%);
      width: 150px;
      height: 60px;
      padding: 0;
      font-size: 24px;
      color: #2620ce;
      background-color: #e2e6ff;
      border-radius: 20px;
      border: none;
    }
  }

  :deep(.van-dialog) {
    .van-dialog__header { text-align: left; padding: 20px 40px; font-weight: 600; }
    .list {
      padding: 0 40px;
      box-shadow: none;
      max-height: 40vh;
      overflow: auto;
      display: flex;
      flex-direction: column;
      .tops { margin-bottom: 0; color: #333; .span { margin-right: 24px; } }
      .box {
        padding: 15px;
        border: 2px solid #ccc;
        margin-top: 24px;
        &:first-child { margin-top: 0; }
        .value0 { padding: 3px 10px; background-color: red; color: #fff; }
        .value1 { padding: 3px 10px; background-color: #07c160; color: #fff; }
      }
    }
    .van-dialog__content { max-height: 60vh; overflow: auto; }
    .van-dialog__footer {
      margin-top: 40px;
      .van-dialog__confirm { color: $theme; }
    }
  }

  .list {
    padding: 30px;
    box-shadow: $shadow;
    color: $subtext;
    text-align: left;
    margin-top: 40px;
    border-radius: 10px;
    .top {
      display: flex;
      justify-content: space-between;
      margin-bottom: 35px;
      .time { font-size: 16px; }
      .tab { font-size: 20px; color: $theme; }
    }
    .cet {
      display: flex;
      background-color: #fafafa;
      padding: 10px 0;
      .img { width: 100%; height: 180PX; }
      .text { font-size: 20px; }
    }
    .monney {
      margin-top: 30px;
      .tent {
        display: flex;
        justify-content: space-between;
        font-size: 24px;
        .span { width: 120px; color: #333; }
      }
    }
    .van-button { font-size: 32px; margin-top: 50px; }
  }
}

.img_loading {
  position: absolute;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(151, 151, 151, 0.821);
  color: #fff;
  display: flex;
  flex-direction: column;
  justify-content: center;
  text-align: center;
  font-size: 32px;
  font-weight: 600;
  .img {
    width: 80%;
    background-color: rgba(0, 0, 0, 0.5);
    margin: 0 auto 30px;
    border-radius: 12px;
  }
  z-index: 888;
}

.pinglun {
  margin: 20px 30px;
  margin-bottom: 0px;
  font-size: 26px;
  color: #000000;
  display: flex;
  flex-direction: column;
  align-items: center;
  font-weight: 900;
  .pingluna {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    width: 100%;
  }
  .pinglunb {
    margin-top: 20px;
    width: 90%;
    border: 1px solid #dadada;
    border-radius: 5px;
  }
}
</style>