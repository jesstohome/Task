<template>
  <div class="self-new home">

    <!-- ══════════════════════════════════════════════════════
         ① 顶部汽车背景大图
    ══════════════════════════════════════════════════════ -->
    <div class="profile-hero">
      <img :src="require('@/assets/images/self/head_bg.png')" alt="hero" class="profile-hero-img" />
    </div>

    <!-- ══════════════════════════════════════════════════════
         ② 主白色内容卡片（头像探出背景图）
    ══════════════════════════════════════════════════════ -->
    <div class="profile-main">

      <!-- 用户信息行：头像（探出） + 用户名 + 邀请码 -->
      <div class="profile-user-row">
        <div class="profile-avatar-wrap">
          <img :src="userinfo?.headpic" alt="avatar" class="profile-avatar-img" />
        </div>
        <div class="profile-user-info">
          <div class="profile-username">{{ userinfo.username }}</div>
          <div class="profile-invite">Invitation Code: <span class="profile-invite-code">{{ userinfo.invite_code || 'VRUA37' }}</span></div>
          <div class="profile-invite">Trial bonus: <span class="profile-invite-code">{{ userinfo.lottery_money || '0' }} {{ currency || 'USD' }}</span></div>
        </div>
      </div>

      <!-- Credit Score 行 -->
      <div class="profile-credit-row">
        <span class="profile-credit-label">Credit Score:</span>
        <div class="profile-credit-bar">
          <div class="profile-credit-fill" :style="{ width: creditPercent + '%' }"></div>
        </div>
        <span class="profile-credit-pct">{{ creditPercent }}%</span>
      </div>

      <!-- 两个深色余额卡片 -->
      <div class="profile-balance-cards">
        <!-- 左：Wallet Amount -->
        <div class="profile-balance-card">
          <div class="pbc-label">Wallet Amount</div>
          <div class="pbc-amount">
            <span class="pbc-amount-num pbc-amount-num--neg">{{ monney || '0.00' }}</span>
            <span class="pbc-amount-unit">{{ currency || 'USD' }}</span>
          </div>
        </div>
        <!-- 右：Advertising salary -->
        <div class="profile-balance-card">
          <div class="pbc-label">Advertising salary</div>
          <div class="pbc-amount">
            <span class="pbc-amount-num">{{ mInfo.yon3 || '0' }}</span>
            <span class="pbc-amount-unit">{{ currency || 'USD' }}</span>
          </div>
        </div>
      </div>

    </div><!-- /.profile-main -->

    <!-- ══════════════════════════════════════════════════════
         ③ My Financial 分组
    ══════════════════════════════════════════════════════ -->
    <div class="menu-section">
      <div class="menu-section-title">My Financial</div>
      <div class="menu-group">

        <div class="menu-item" @click="toRoute(list[1], 1)">
          <div class="menu-item-icon">
            <img :src="require('@/assets/images/self/deposit.png')" alt="" />
          </div>
          <span class="menu-item-label">Deposit</span>
          <span class="menu-item-arrow">›</span>
        </div>

        <div class="menu-item" @click="toRoute(list[0], 0)">
          <div class="menu-item-icon">
            <img :src="require('@/assets/images/self/withdraw.png')" alt="" />
          </div>
          <span class="menu-item-label">Withdraw</span>
          <span class="menu-item-arrow">›</span>
        </div>

        <div class="menu-item" @click="toRoute(list[3], 3)">
          <div class="menu-item-icon">
            <img :src="require('@/assets/images/self/transaction.png')" alt="" />
          </div>
          <span class="menu-item-label">Transaction</span>
          <span class="menu-item-arrow">›</span>
        </div>

      </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         ④ My Detail 分组
    ══════════════════════════════════════════════════════ -->
    <div class="menu-section">
      <div class="menu-section-title">My Detail</div>
      <div class="menu-group">

        <div class="menu-item" @click="toRoute(qitalist[4], 4)">
          <div class="menu-item-icon">
            <img :src="require('@/assets/images/self/kyc.png')" alt="" />
          </div>
          <span class="menu-item-label">KYC</span>
          <span class="menu-item-arrow">›</span>
        </div>

        <div class="menu-item" @click="toRoute(qitalist[1], 1)">
          <div class="menu-item-icon">
            <img :src="require('@/assets/images/self/edit.png')" alt="" />
          </div>
          <span class="menu-item-label">Edit Password</span>
          <span class="menu-item-arrow">›</span>
        </div>

        <div class="menu-item" @click="toRoute(list[4], 4)">
          <div class="menu-item-icon">
            <img :src="require('@/assets/images/self/payment.png')" alt="" />
          </div>
          <span class="menu-item-label">Payment Methods</span>
          <span class="menu-item-arrow">›</span>
        </div>

      </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         ⑤ Other 分组
    ══════════════════════════════════════════════════════ -->
    <div class="menu-section">
      <div class="menu-section-title">Other</div>
      <div class="menu-group">

        <div class="menu-item" @click="toRoute(qitalist[0], 0)">
          <div class="menu-item-icon">
            <img :src="require('@/assets/images/self/contactus.png')" alt="" />
          </div>
          <span class="menu-item-label">Contact Us</span>
          <span class="menu-item-arrow">›</span>
        </div>

        <div class="menu-item" @click="toRoute(qitalist[2], 2)">
          <div class="menu-item-icon">
            <img :src="require('@/assets/images/self/notify.png')" alt="" />
          </div>
          <span class="menu-item-label">Notifications</span>
          <span class="menu-item-arrow">›</span>
        </div>

      </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         ⑥ Logout 按钮（底部固定）
    ══════════════════════════════════════════════════════ -->
    <div class="logout-wrap">
      <button class="logout-btn" @click="tuichu">Logout</button>
    </div>

    <div class="copyright">©1999-2026 AWISEE</div>

  </div>
</template>

<script>
import { ref, getCurrentInstance, onMounted } from 'vue';
import { getself, get_id_auth } from '@/api/self/index'
import { uploadImg, headpicUpdatae, getHomeData } from '@/api/home/index.js'
import { logout } from '@/api/login/index'
import store from '@/store/index'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router';
import { bind_bank } from '@/api/self/index.js'
import { Dialog } from 'vant'
import langVue from '@/components/lang.vue'

export default {
  components: { langVue },
  setup() {
    const { push } = useRouter();
    const { proxy } = getCurrentInstance()
    const { t } = useI18n()
    const upload = ref(null)
    const currency = ref(store.state.baseInfo?.currency)
    const userinfo = ref(store.state.userinfo)
    const monney = ref(store.state.minfo?.balance)
    const mInfo = ref(store.state.minfo)
    const level = ref(store.state.minfo?.level || 0)
    const activeTab = ref(1)
    const creditPercent = ref(store.state.minfo?.credit || 0)
    const idStatus = ref(0)
    const idRemark = ref('')
    const inviteCode = ref('TPIAA')
    const is_bind = ref(false)

    store.dispatch('changefooCheck', 'self')

    const list = ref([
      { label: t('msg.tikuan'), img: require('@/assets/images/self/00.png'), path: '/drawing', params: 'balance' },
      { label: t('msg.chongzhi'), img: require('@/assets/images/self/02.png'), path: '/chongzhi' },
      { label: t('msg.txjl'), img: require('@/assets/images/self/02.png'), path: '/deposit' },
      { label: t('msg.zbjl'), img: require('@/assets/images/self/03.png'), path: '/account_details' },
      { label: t('msg.tkxx'), img: require('@/assets/images/self/04.png'), path: '/bingbank' },
      { label: t('msg.xxgg'), img: require('@/assets/images/self/05.png'), path: '/message' },
      { label: t('msg.pwd'), img: require('@/assets/images/self/06.png'), path: '/editPwd' },
      {
        label: t('msg.out'), img: require('@/assets/images/self/07.png'), click: () => {
          proxy.$dialog.confirm({ title: t('msg.ts'), message: t('msg.next_login'), confirmButtonText: t('msg.yes'), cancelButtonText: t('msg.quxiao') })
            .then(() => { logout().then(res => { if (res.code === 0) { proxy.$Message({ type: 'success', message: res.info }); push('/login') } else { proxy.$Message({ type: 'error', message: res.info }) } }) })
            .catch(() => { });
        }
      },
    ])

    const qitalist = ref([
      { label: t('msg.tel'), img: require('@/assets/images/self/kefu.png'), path: '/service' },
      { label: t('msg.pwd'), img: require('@/assets/images/self/password.png'), path: '/editPwd' },
      { label: t('msg.xxgg'), img: require('@/assets/images/self/tongzhi.png'), path: '/message' },
      { label: t('msg.tdbg'), img: require('@/assets/images/self/teams.png'), path: '/team' },
      { label: t('msg.real_name_auth'), img: require('@/assets/images/self/shiming.png'), path: '/realNameAuth' }
    ])

    const tuichu = () => {
      proxy.$dialog.confirm({ title: t('msg.ts'), message: t('msg.next_login'), confirmButtonText: t('msg.yes'), cancelButtonText: t('msg.quxiao') })
        .then(() => { logout().then(res => { if (res.code === 0) { proxy.$Message({ type: 'success', message: res.info }); push('/login') } else { proxy.$Message({ type: 'error', message: res.info }) } }) })
        .catch(() => { });
    }

    const getInfo = () => {
      getself().then(res => { if (res.code === 0) userinfo.value = { ...res.data?.info } })
    }
    getInfo()

    onMounted(() => {
      getHomeData().then(res => {
        if (res.code === 0) {
          monney.value = res.data.balance
          mInfo.value = { ...res.data }
          creditPercent.value = res.data.credit
          store.dispatch('changeminfo', res.data || {})
        }
      })
      get_id_auth().then(res => {
        if (res.code === 0) {
          idStatus.value = res.data?.id_status ?? 0
          idRemark.value = res.data?.id_remark || ''
        }
      })
    })

    bind_bank().then(res => {
      if (res.code === 0) {
        if ((res.data?.info || []).length > 0) is_bind.value = true
      }
    })

    const toRoute = (row, index) => {
      if (index == 0 && !is_bind.value && row.path != '/service') {
        Dialog.confirm({ title: '', message: t('msg.tjtkxx') })
          .then(() => { push('/bingbank') })
          .catch(() => { });
        return false
      }
      if (row.path) { push(row.path + (row.params ? '?param=' + userinfo.value[row.params] : '')) }
      else if (row.click) { row.click(row) }
    }

    const setAvatar = () => { upload.value?.chooseFile() }
    const afterRead = (file) => {
      const formData = new FormData();
      formData.append('file', file.file);
      uploadImg(formData).then(res => { if (res.uploaded) headpicUpdatae({ url: res.url }).then(() => getInfo()) })
    }
    const toShare = () => { push('/share') }
    const copyInvite = (xinxi) => {
      try {
        if (navigator && navigator.clipboard && userinfo.value?.invite_code) {
          navigator.clipboard.writeText(userinfo.value.invite_code)
          proxy.$toast?.success && proxy.$toast.success(xinxi)
        }
      } catch (e) {
        const ta = document.createElement('textarea')
        ta.value = userinfo.value?.invite_code || ''
        document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta)
      }
    }

    return {
      currency, level, list, qitalist, tuichu, setAvatar, toShare, toRoute, afterRead,
      upload, userinfo, monney, mInfo, activeTab, creditPercent, inviteCode, copyInvite,
      idStatus, idRemark, push
    }
  }
}
</script>

<style lang="scss" scoped>
@import '@/styles/theme.scss';

/* ════════════════════════════════════════════════════════════
   新版整体容器
   ════════════════════════════════════════════════════════════ */
.self-new {
  //background: #f5f5f5;
  min-height: 100vh;
  padding-bottom: 140px;  /* 给底部logout按钮留空间 */
  overflow-x: hidden;
}

/* ════════════════════════════════════════════════════════════
   ① 顶部汽车英雄图
   ════════════════════════════════════════════════════════════ */
.profile-hero {
  width: 100%;
  height: 440px;
  position: relative;
  /* 底部保留一些给白卡探出 */
}
.profile-hero-img {
  width: 100%;
  height: 440px;
  object-fit: cover;
  object-position: center 20%;
  display: block;
}

/* ════════════════════════════════════════════════════════════
   ② 白色主信息卡片
   头像从卡片上边探出（负的margin-top）
   ════════════════════════════════════════════════════════════ */
.profile-main {
  background: #ffffff;
  border-radius: 28px;
  margin-top: -30px;  /* 卡片探出背景图 */
  position: relative;
  z-index: 10;
  padding: 0 28px 36px;
}

/* 用户信息行：头像（探出卡片上边缘） + 名字/邀请码 */
.profile-user-row {
  display: flex;
  align-items: flex-end;
  padding-top: 0;
  margin-bottom: 28px;
}

.profile-avatar-wrap {
  /* 头像探出卡片顶部 */
  margin-top: -60px;
  flex-shrink: 0;
  width: 80px;
  height: 80px;
  border-radius: 50%;
  border: 4px solid #ffffff;
  overflow: hidden;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.14);
  background: #f0f0f0;
  margin-right: 20px;
}
.profile-avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.profile-user-info {
  flex: 1;
  text-align: left;
  padding-top: 16px;
}
.profile-username {
  font-size: 36px;
  font-weight: 800;
  color: #1a1a2e;
  margin-bottom: 6px;
  letter-spacing: 0.5px;
}
.profile-invite {
  font-size: 26px;
  color: #666666;
  font-weight: 400;
}
.profile-invite-code {
  font-weight: 700;
  color: #333333;
}

/* Credit Score 行 */
.profile-credit-row {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 28px;
}
.profile-credit-label {
  font-size: 26px;
  color: #555555;
  white-space: nowrap;
  flex-shrink: 0;
}
.profile-credit-bar {
  flex: 1;
  height: 10px;
  background: #e8e8e8;
  border-radius: 999px;
  overflow: hidden;
}
.profile-credit-fill {
  height: 100%;
  /* 蓝紫色渐变进度条，与图中一致 */
  background: linear-gradient(90deg, #4c4cef 0%, #7b6cf6 100%);
  border-radius: 999px;
  transition: width 0.6s ease;
}
.profile-credit-pct {
  font-size: 26px;
  font-weight: 800;
  color: #1a1a2e;
  white-space: nowrap;
  flex-shrink: 0;
}

/* 两张深色余额卡片 */
.profile-balance-cards {
  display: flex;
  gap: 18px;
}
.profile-balance-card {
  flex: 1;
  /* 深色背景：深蓝绿，与图中一致 */
  background: linear-gradient(120deg, #0b2a2d 0%, #0c0d12 100%);
  border-radius: 18px;
  padding: 28px 22px 24px;
  text-align: left;
}
.pbc-label {
  font-size: 24px;
  color: rgba(255, 255, 255, 0.65);
  margin-bottom: 14px;
  font-weight: 400;
}
.pbc-amount {
  display: flex;
  align-items: baseline;
  gap: 8px;
}
.pbc-amount-num {
  font-size: 40px;
  font-weight: 800;
  color: #ffffff;
  letter-spacing: -0.5px;
  &--neg {
    /* 负数保持白色，与图中一致 */
    color: #ffffff;
  }
}
.pbc-amount-unit {
  font-size: 26px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.70);
}

/* ════════════════════════════════════════════════════════════
   ③④⑤ 菜单分组
   ════════════════════════════════════════════════════════════ */
.menu-section {
  margin: 28px 44px 0;
}

.menu-section-title {
  font-size: 34px;
  font-weight: 800;
  color: #1a1a2e;
  margin-bottom: 16px;
  text-align: left;
  padding-left: 4px;
}

/* 菜单卡片容器 */
.menu-group {
  background: #f3f3f3;
  border-radius: 20px;
  overflow: hidden;
}

/* 单行菜单项 */
.menu-item {
  display: flex;
  align-items: center;
  padding: 30px 24px;
  background: #f0f0f0;
  cursor: pointer;
  transition: background 0.15s;
  position: relative;

  /* 分隔线：除最后一项 */
  &:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 76px;
    right: 0;
    bottom: 0;
    height: 1px;
    background: #e0e0e0;
  }

  &:active {
    background: #e8e8e8;
  }
}

/* 左侧图标容器 */
.menu-item-icon {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  overflow: hidden;
  flex-shrink: 0;
  margin-right: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
}

.menu-item-label {
  flex: 1;
  font-size: 30px;
  font-weight: 500;
  color: #1a1a2e;
  text-align: left;
}

/* 右侧箭头 */
.menu-item-arrow {
  font-size: 50px;
  color: #bbbbbb;
  font-weight: 900;
  line-height: 1;
}

/* ════════════════════════════════════════════════════════════
   ⑥ Logout 按钮
   固定在底部
   ════════════════════════════════════════════════════════════ */
.logout-wrap {
  bottom: 0;
  left: 0;
  right: 0;
  padding: 20px 24px 40px;
}
.logout-btn {
  display: block;
  width: 100%;
  height: 90px;
  /* 蓝紫色，与图中一致 */
  background: linear-gradient(90deg, #4c4cef 0%, #6a5cf6 100%);
  color: #ffffff;
  border: none;
  border-radius: 50px;
  font-size: 34px;
  font-weight: 700;
  letter-spacing: 1px;
  cursor: pointer;
  box-shadow: 0 6px 24px rgba(76, 76, 239, 0.35);
  transition: transform 0.15s, box-shadow 0.15s;

  &:active {
    transform: scale(0.98);
    box-shadow: 0 3px 12px rgba(76, 76, 239, 0.22);
  }
}

/* 版权信息 */
.copyright {
  text-align: center;
  font-size: 24px;
  color: #aaaaaa;
  padding: 8px 0 24px;
}
</style>