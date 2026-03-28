<template>
  <van-popup v-model:show="showGift" :style="{ width: '100%', height: '100vh', background: 'transparent' }" teleport="body" :z-index="999" @close="onGiftClose">
    
    <!-- 背景星星 -->
    <div class="stars-bg">
      <div class="star" v-for="i in 20" :key="i" :style="getStarStyle(i)"></div>
    </div>

    <div class="gift-wrapper" :class="{ 'wrapper-enter': wrapperEnter }">
      
      <!-- 头部 -->
      <div class="gift-header">
        <div class="gift-title">
          <span class="title-icon">🎁</span>
          <span class="title-text">You received a gift package!</span>
        </div>
        <div class="gift-close" @click="showGift = false">×</div>
      </div>

      <!-- 提示文字 -->
      <div class="hint-text" v-if="openingIndex === -1">✨ Choose one gift box to open ✨</div>
      <div class="hint-text opening-hint" v-if="openingIndex === -2 && !claimSuccess">🔮 Opening your gift...</div>
      <div class="hint-text success-hint" v-if="claimSuccess">🎉 Congratulations! Here's what you got!</div>

      <!-- 礼盒区域 -->
      <div class="gift-boxes">
        <div
          class="gift-box"
          v-for="(box, index) in giftBoxes"
          :key="index"
          @click="selectGiftBox(index)"
          :class="{
            'box-idle': openingIndex === -1,
            'box-selected': openingIndex === -2 && selectedBoxIndex === index,
            'box-unselected': openingIndex === -2 && selectedBoxIndex !== index,
            'box-revealed-gold': claimSuccess && selectedBoxIndex === index,
            'box-revealed-white': claimSuccess && selectedBoxIndex !== index,
          }"
          :style="openingIndex === -1 ? { animationDelay: `${index * 0.2}s` } : {}"
        >
          <!-- 待选状态 -->
          <template v-if="openingIndex === -1 || (openingIndex === -2 && selectedBoxIndex !== index && !claimSuccess)">
            <div class="box-glow"></div>
            <div class="box-icon">
              <img :src="getBoxImage(index)" alt="gift box" />
            </div>
            <!-- <div class="box-text">{{ box.text }}</div> -->
            <div class="box-shine"></div>
          </template>

          <!-- 选中开启中 -->
          <template v-if="openingIndex === -2 && selectedBoxIndex === index && !claimSuccess">
            <div class="box-opening-anim">
              <div class="opening-ring ring1"></div>
              <div class="opening-ring ring2"></div>
              <div class="opening-ring ring3"></div>
              <div class="opening-icon">✨</div>
            </div>
          </template>

          <!-- 全部开启后展示内容（三个都展示）-->
          <template v-if="claimSuccess">
            <div class="box-result-content" :class="{ 'is-selected': selectedBoxIndex === index }">
              <div class="result-emoji">{{ selectedBoxIndex === index ? '🎉' : '📦' }}</div>
              <div class="result-label" v-if="selectedBoxIndex === index">Your Choice!</div>
              <div class="result-detail-text">{{ getOpenedText(index) }}</div>
            </div>
          </template>
        </div>
      </div>

      <!-- 底部结果信息 + 倒计时 -->
      <transition name="result-fade">
        <div class="result-bottom" v-if="claimSuccess">
          <div class="result-bottom-msg">{{ resultMessage }}</div>
          <div class="result-countdown">
            <div class="countdown-bar" :style="{ width: countdownWidth + '%' }"></div>
          </div>
          <div class="result-bottom-tip">Window closing automatically...</div>
        </div>
      </transition>

    </div>

    <!-- 烟花层 -->
    <div class="fireworks-layer" v-if="claimSuccess">
      <div class="firework" v-for="i in 8" :key="i" :style="getFireworkStyle(i)"></div>
    </div>

  </van-popup>
</template>

<script>
import { ref, watch, getCurrentInstance, onMounted, onUnmounted } from 'vue';
import { check_gift, claim_gift } from '@/api/gift/index';
import { useRouter } from 'vue-router';

export default {
  name: 'GiftPackage',
  props: {
    modelValue: { type: Boolean, default: false }
  },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    const { push } = useRouter();
    const showGift = ref(false);
    const openingIndex = ref(-1);
    const selectedBoxIndex = ref(-1);
    const claimSuccess = ref(false);
    const wrapperEnter = ref(false);
    const giftId = ref(0);
    const resultMessage = ref('');
    const selected = ref(1);
    const countdownWidth = ref(100);
    const { proxy } = getCurrentInstance();
    let giftCheckTimer = null;
    let countdownTimer = null;

    const giftBoxes = ref([
      { text: 'Money Reward',    type: 'money' },
      { text: 'Order Reward',    type: 'order' },
      { text: 'Compound Reward', type: 'compound' }
    ]);

    watch(() => props.modelValue, (newVal) => {
      if (!newVal) showGift.value = false;
    });

    watch(showGift, (newVal) => {
      emit('update:modelValue', newVal);
      if (newVal) {
        setTimeout(() => { wrapperEnter.value = true; }, 50);
      } else {
        wrapperEnter.value = false;
      }
    });

    const checkGiftStatus = async () => {
      try {
        const res = await check_gift();
        if (res.code === 0 && res.has_gift) {
          giftId.value = res.gift_id;
          if (res.gift_data) {
            giftBoxes.value = [
              { text: 'Money Reward',    type: 'money',    data: res.gift_data.gift1 },
              { text: 'Order Reward',    type: 'order',    data: res.gift_data.gift2 },
              { text: 'Compound Reward', type: 'compound', data: res.gift_data.gift3 }
            ];
          }
          selected.value = res.selected;
          showGift.value = true;
        }
      } catch (error) {
        console.error('Check gift failed:', error);
      }
    };

    const startGiftPolling = () => {
      if (giftCheckTimer) return;
      checkGiftStatus();
    };

    const stopGiftPolling = () => {
      if (giftCheckTimer) {
        clearInterval(giftCheckTimer);
        giftCheckTimer = null;
      }
    };

    const startCountdown = (duration) => {
      countdownWidth.value = 100;
      const step = 100 / (duration / 50);
      countdownTimer = setInterval(() => {
        countdownWidth.value = Math.max(0, countdownWidth.value - step);
      }, 50);
      setTimeout(() => { clearInterval(countdownTimer); }, duration);
    };

    const selectGiftBox = async (index) => {
      if (openingIndex.value !== -1) return;
      selectedBoxIndex.value = index;
      openingIndex.value = -2;

      setTimeout(async () => {
        try {
          // 通知后台用户点击了，不依赖它的返回值做数据处理
          const res = await claim_gift({ gift_id: giftId.value, selected_gift: index + 1 });
          if (res.code !== 0) {
            openingIndex.value = -1;
            selectedBoxIndex.value = -1;
            proxy.$Message({ type: 'error', message: res.info, zIndex: 9999 });
            return;
          }
          resultMessage.value = res.info; // 用接口返回的提示语

          // selected.value 是 check_gift 时存好的 1/2/3，转成 0-indexed
          const realWinDataIndex = selected.value - 1;

          // 把用户点击的盒子和预设中奖数据的位置互换
          if (index !== realWinDataIndex) {
            const boxes = [...giftBoxes.value];
            [boxes[index], boxes[realWinDataIndex]] = [boxes[realWinDataIndex], boxes[index]];
            giftBoxes.value = boxes;
          }

          // 高亮用户点击的那个盒子
          selectedBoxIndex.value = index;
          //resultMessage.value = '🎁 Congratulations!';

          claimSuccess.value = true;
          startCountdown(5000);

          proxy.$Message({ type: 'success', message: res.info, zIndex: 9999 });
          
          setTimeout(() => {
            showGift.value = false;
            setTimeout(() => {
              claimSuccess.value = false;
              openingIndex.value = -1;
              selectedBoxIndex.value = -1;
              countdownWidth.value = 100;
              if(selected.value == 2 || selected.value == 3){
                
                push({ name: 'detail', params: { id: res.oid } });
              }
            }, 600);
          }, 5000);

        } catch (error) {
          openingIndex.value = -1;
          selectedBoxIndex.value = -1;
          proxy.$Message({ type: 'error', message: 'Claim failed', zIndex: 9999 });
        }
      }, 1800);
    };

    const onGiftClose = () => {
      showGift.value = false;
      claimSuccess.value = false;
      openingIndex.value = -1;
      selectedBoxIndex.value = -1;
      if (countdownTimer) clearInterval(countdownTimer);
    };

    const getStarStyle = (i) => ({
      left: `${(i * 37 + 11) % 100}%`,
      top: `${(i * 53 + 7) % 100}%`,
      animationDelay: `${(i * 0.3) % 3}s`,
      animationDuration: `${2 + (i % 3)}s`,
      width: `${3 + (i % 4)}px`,
      height: `${3 + (i % 4)}px`,
    });

    const getFireworkStyle = (i) => ({
      left: `${10 + (i * 11)}%`,
      top: `${5 + (i % 4) * 20}%`,
      animationDelay: `${i * 0.25}s`,
      animationDuration: `${1.2 + (i % 3) * 0.3}s`,
    });

    const getBoxImage = (index) => {
      const images = [
        require('@/assets/images/home/gifta.png'),
        require('@/assets/images/home/giftb.png'),
        require('@/assets/images/home/giftc.png')
      ];
      return images[index];
    };

    const getOpenedText = (index) => {
      const box = giftBoxes.value[index];
      if (!box || !box.data) return box?.text ?? '';
      switch (box.type) {
        case 'money':    return `$${box.data.amount} Bonus`;
        case 'order':    return `$${box.data.order_amount} Order\n${box.data.commission}% Commission`;
        case 'compound': return `$${box.data.order_amount} Order\n× ${box.data.order_count} Times`;
        default:         return box.text;
      }
    };

    onMounted(() => { startGiftPolling(); });
    onUnmounted(() => {
      stopGiftPolling();
      if (countdownTimer) clearInterval(countdownTimer);
    });

    return {
      showGift, openingIndex, selectedBoxIndex, claimSuccess,
      wrapperEnter, giftBoxes, resultMessage, countdownWidth,
      selectGiftBox, onGiftClose, startGiftPolling, stopGiftPolling,
      getBoxImage, getOpenedText, getStarStyle, getFireworkStyle
    };
  }
};
</script>

<style lang="scss" scoped>

/* ========== 背景 ========== */
.stars-bg {
  position: fixed;
  inset: 0;
  pointer-events: none;
  background: radial-gradient(ellipse at center, #1a0533cc 0%, #0a0015ee 100%);
}

.star {
  position: absolute;
  border-radius: 50%;
  background: #fff;
  animation: starTwinkle 2s ease-in-out infinite alternate;
}

@keyframes starTwinkle {
  0% { opacity: 0.2; transform: scale(1); }
  100% { opacity: 1; transform: scale(1.6); }
}

/* ========== 主容器 ========== */
.gift-wrapper {
  position: relative;
  z-index: 1;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 30px;
  opacity: 0;
  transform: translateY(40px) scale(0.96);
  transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);

  &.wrapper-enter {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* ========== 头部 ========== */
.gift-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  padding: 0 30px;
  position: absolute;
  top: 60px;
  left: 0;
}

.gift-title {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
}

.title-icon {
  font-size: 48px;
  animation: iconBounce 1.5s ease-in-out infinite;
}

@keyframes iconBounce {
  0%, 100% { transform: rotate(-10deg) scale(1); }
  50% { transform: rotate(10deg) scale(1.2); }
}

.title-text {
  font-size: 32px;
  font-weight: bold;
  color: #ffd700;
  text-shadow: 0 0 20px rgba(255, 215, 0, 0.8);
}

.gift-close {
  font-size: 52px;
  color: rgba(255,255,255,0.6);
  cursor: pointer;
  line-height: 1;
  transition: all 0.2s;
  &:active { transform: scale(0.85); color: #fff; }
}

/* ========== 提示文字 ========== */
.hint-text {
  font-size: 28px;
  color: rgba(255,255,255,0.85);
  letter-spacing: 2px;
  animation: hintPulse 2s ease-in-out infinite;
}

.opening-hint { color: #ffd700; animation: hintPulse 0.8s ease-in-out infinite; }
.success-hint {
  color: #ffd700;
  font-size: 32px;
  font-weight: bold;
  text-shadow: 0 0 20px rgba(255,215,0,0.6);
  animation: hintPulse 1.5s ease-in-out infinite;
}

@keyframes hintPulse {
  0%, 100% { opacity: 0.7; }
  50% { opacity: 1; }
}

/* ========== 礼盒区域 ========== */
.gift-boxes {
  display: flex;
  justify-content: space-around;
  align-items: center;
  width: 100%;
  padding: 0 15px;
}

/* ========== 礼盒卡片 ========== */
.gift-box {
  width: 195px;
  height: 250px;
  border-radius: 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  background: linear-gradient(145deg, #5a2dff99, #9b59b6cc);
  box-shadow: 0 15px 35px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.15);
  transition: transform 0.4s ease, box-shadow 0.4s ease, opacity 0.4s ease, background 0.4s ease;

  &.box-idle {
    animation: boxBounce 2.5s ease-in-out infinite;
    &:active { transform: scale(0.92); }
  }

  &.box-selected {
    animation: boxPulse 0.6s ease-in-out infinite alternate;
    background: linear-gradient(145deg, #7c3aed, #a855f7);
    box-shadow: 0 0 50px rgba(168, 85, 247, 0.9), 0 15px 35px rgba(0,0,0,0.4);
  }

  &.box-unselected {
    opacity: 0.25;
    transform: scale(0.88);
    filter: grayscale(0.6);
    cursor: default;
  }

  // 选中的礼盒 - 金黄色
  &.box-revealed-gold {
    background: linear-gradient(145deg, #f5a623, #ffd700);
    box-shadow: 0 0 60px rgba(255, 215, 0, 0.9), 0 20px 40px rgba(0,0,0,0.4);
    cursor: default;
    animation: revealBounce 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
  }

  // 未选中的礼盒 - 白色
  &.box-revealed-white {
    background: linear-gradient(145deg, #e8e8e8, #ffffff);
    box-shadow: 0 10px 30px rgba(255,255,255,0.2), 0 15px 35px rgba(0,0,0,0.3);
    cursor: default;
    animation: revealFadeIn 0.8s ease forwards;
  }
}

@keyframes boxBounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-12px); }
}

@keyframes boxPulse {
  0% { transform: scale(1.02); }
  100% { transform: scale(1.08); }
}

@keyframes revealBounce {
  0% { transform: scale(0.95); }
  60% { transform: scale(1.12); }
  100% { transform: scale(1.06); }
}

@keyframes revealFadeIn {
  0% { transform: scale(0.9); opacity: 0.5; }
  100% { transform: scale(1); opacity: 1; }
}

/* ========== 光晕 & 光效 ========== */
.box-glow {
  position: absolute;
  inset: -2px;
  border-radius: 26px;
  background: linear-gradient(45deg, #a855f7, #3b82f6, #a855f7);
  background-size: 200% 200%;
  animation: glowRotate 3s linear infinite;
  z-index: -1;
  opacity: 0.5;
}

@keyframes glowRotate {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

.box-shine {
  position: absolute;
  top: -50%;
  left: -60%;
  width: 40%;
  height: 200%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
  transform: skewX(-20deg);
  animation: shineSlide 3s ease-in-out infinite;
}

@keyframes shineSlide {
  0%, 60% { left: -60%; }
  100% { left: 150%; }
}

/* ========== 礼盒图标文字 ========== */
.box-icon {
  margin-bottom: 14px;
  filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));
  animation: iconFloat 3s ease-in-out infinite;
  img { width: 160px; height: 160px; }
}

@keyframes iconFloat {
  0%, 100% { transform: translateY(0) rotate(-3deg); }
  50% { transform: translateY(-6px) rotate(3deg); }
}

.box-text {
  font-size: 22px;
  font-weight: bold;
  color: #fff;
  text-align: center;
  text-shadow: 0 2px 8px rgba(0,0,0,0.5);
  padding: 0 10px;
}

/* ========== 开箱动画 ========== */
.box-opening-anim {
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  width: 100%;
  height: 100%;
}

.opening-ring {
  position: absolute;
  border-radius: 50%;
  border: 3px solid rgba(255, 215, 0, 0.7);
  animation: ringExpand 1.2s ease-out infinite;
  &.ring1 { width: 60px; height: 60px; animation-delay: 0s; }
  &.ring2 { width: 100px; height: 100px; animation-delay: 0.3s; }
  &.ring3 { width: 150px; height: 150px; animation-delay: 0.6s; }
}

@keyframes ringExpand {
  0% { transform: scale(0.5); opacity: 1; }
  100% { transform: scale(1.5); opacity: 0; }
}

.opening-icon {
  font-size: 60px;
  animation: openingIconSpin 0.8s linear infinite;
  z-index: 1;
}

@keyframes openingIconSpin {
  0% { transform: rotate(0deg) scale(1); }
  50% { transform: rotate(180deg) scale(1.3); }
  100% { transform: rotate(360deg) scale(1); }
}

/* ========== 翻开后内容 ========== */
.box-result-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 16px;
  width: 100%;
  height: 100%;
  animation: resultContentIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;

  &.is-selected {
    .result-detail-text { color: #3d1a00; font-size: 23px; }
  }
}

@keyframes resultContentIn {
  0% { opacity: 0; transform: scale(0.6) rotate(-8deg); }
  100% { opacity: 1; transform: scale(1) rotate(0deg); }
}

.result-emoji {
  font-size: 52px;
  margin-bottom: 8px;
  animation: emojiPop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

@keyframes emojiPop {
  0% { transform: scale(0); }
  70% { transform: scale(1.3); }
  100% { transform: scale(1); }
}

.result-label {
  font-size: 20px;
  font-weight: bold;
  color: #7c3a00;
  background: rgba(255,255,255,0.5);
  border-radius: 20px;
  padding: 2px 14px;
  margin-bottom: 8px;
}

.result-detail-text {
  font-size: 21px;
  font-weight: bold;
  color: #333;
  text-align: center;
  line-height: 1.5;
  white-space: pre-line;
}

/* ========== 底部结果信息 ========== */
.result-bottom {
  width: 88%;
  background: linear-gradient(135deg, rgba(26,5,51,0.92), rgba(45,16,102,0.92));
  border: 1px solid rgba(255, 215, 0, 0.35);
  border-radius: 20px;
  padding: 20px 30px;
  text-align: center;
  backdrop-filter: blur(20px);
  box-shadow: 0 10px 40px rgba(0,0,0,0.5), 0 0 30px rgba(255,215,0,0.15);
}

.result-bottom-msg {
  font-size: 26px;
  color: #ffd700;
  font-weight: bold;
  margin-bottom: 14px;
  text-shadow: 0 0 15px rgba(255,215,0,0.5);
}

.result-countdown {
  height: 5px;
  background: rgba(255,255,255,0.15);
  border-radius: 3px;
  overflow: hidden;
  margin-bottom: 10px;
}

.countdown-bar {
  height: 100%;
  background: linear-gradient(90deg, #ffd700, #f5a623);
  border-radius: 3px;
  transition: width 0.05s linear;
}

.result-bottom-tip {
  font-size: 20px;
  color: rgba(255,255,255,0.45);
}

/* ========== 烟花 ========== */
.fireworks-layer {
  position: fixed;
  inset: 0;
  pointer-events: none;
  z-index: 2;
}

.firework {
  position: absolute;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  animation: fireworkBurst 1.4s ease-out infinite;

  &:nth-child(1) { background: #ffd700; left: 10%; top: 15%; }
  &:nth-child(2) { background: #ff6b6b; left: 25%; top: 8%; }
  &:nth-child(3) { background: #4ecdc4; left: 45%; top: 20%; }
  &:nth-child(4) { background: #ffe66d; left: 65%; top: 10%; }
  &:nth-child(5) { background: #a855f7; left: 80%; top: 18%; }
  &:nth-child(6) { background: #3b82f6; left: 90%; top: 30%; }
  &:nth-child(7) { background: #ff9f43; left: 15%; top: 35%; }
  &:nth-child(8) { background: #48dbfb; left: 70%; top: 40%; }
}

@keyframes fireworkBurst {
  0% {
    transform: scale(1);
    opacity: 1;
    box-shadow: 0 0 0 0 currentColor,
                20px -20px 0 0 currentColor,
                -20px -20px 0 0 currentColor,
                20px 20px 0 0 currentColor,
                -20px 20px 0 0 currentColor;
  }
  100% {
    transform: scale(0.3);
    opacity: 0;
    box-shadow: 0 0 0 0 transparent,
                50px -50px 0 0 transparent,
                -50px -50px 0 0 transparent,
                50px 50px 0 0 transparent,
                -50px 50px 0 0 transparent;
  }
}

/* ========== 过渡 ========== */
.result-fade-enter-active {
  animation: resultBottomIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}
.result-fade-leave-active {
  animation: resultBottomOut 0.4s ease-in forwards;
}

@keyframes resultBottomIn {
  0% { opacity: 0; transform: translateY(20px) scale(0.95); }
  100% { opacity: 1; transform: translateY(0) scale(1); }
}

@keyframes resultBottomOut {
  0% { opacity: 1; transform: translateY(0) scale(1); }
  100% { opacity: 0; transform: translateY(15px) scale(0.95); }
}
</style>