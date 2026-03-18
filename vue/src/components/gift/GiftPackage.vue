<template>
  <van-popup v-model:show="showGift" :style="{ width: '90%', height: '100vh', background: '#ffffff00' }" teleport="body" :z-index="999" @close="onGiftClose">
    <div class="gift-wrapper">
      <div class="gift-header">
        <div class="gift-title">🎁 Congratulations! You received a gift package!</div>
        <div class="gift-close" @click="showGift = false">×</div>
      </div>
      <div class="gift-boxes">
        <div
          class="gift-box"
          v-for="(box, index) in giftBoxes"
          :key="index"
          @click="selectGiftBox(index)"
          :class="{ 'opening': openingIndex === index }"
        >
          <div class="box-icon">{{ openingIndex === index ? '🎉' : '🎁' }}</div>
          <div class="box-text">{{ box.text }}</div>
        </div>
      </div>
    </div>
  </van-popup>

  <!-- 结果弹窗 -->
  <van-popup v-model:show="showResult" :style="{ width: '80%', height: 'auto', background: '#00000000' }" style="border-radius: 20px;" teleport="body" :z-index="9991">
    <div class="result-wrapper">
      <div class="result-content">
        <div class="result-icon">🎉</div>
        <div class="result-text">{{ resultMessage }}</div>
      </div>
    </div>
  </van-popup>
</template>

<script>
import { ref, watch, getCurrentInstance, onMounted, onUnmounted } from 'vue';
import { check_gift, claim_gift } from '@/api/gift/index';
import { Toast } from 'vant';

export default {
  name: 'GiftPackage',
  props: {
    modelValue: {
      type: Boolean,
      default: false
    }
  },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    const showGift = ref(false);
    const showResult = ref(false);
    const openingIndex = ref(-1);
    const giftId = ref(0);
    const resultMessage = ref('');
    const { proxy } = getCurrentInstance()
    let giftCheckTimer = null; // 定时器引用

    // 礼包数据
    const giftBoxes = ref([
      { text: 'Money Reward', type: 'money' },
      { text: 'Order Reward', type: 'order' },
      { text: 'Compound Reward', type: 'compound' }
    ]);

    // 监听外部控制
    watch(() => props.modelValue, (newVal) => {
      // 外部传入false时隐藏弹窗
      if (!newVal) {
        showGift.value = false;
      }
    });

    watch(showGift, (newVal) => {
      emit('update:modelValue', newVal);
    });

    // 检查礼包状态
    const checkGiftStatus = async () => {
      try {
        const res = await check_gift();
        if (res.code === 0 && res.has_gift) {
          giftId.value = res.gift_id;
          // 更新礼包数据
          if (res.gift_data) {
            giftBoxes.value = [
              { text: `Money Reward`, type: 'money', data: res.gift_data.gift1 },
              { text: `Order Reward Amount`, type: 'order', data: res.gift_data.gift2 },
              { text: `Compound Reward Amount`, type: 'compound', data: res.gift_data.gift3 }
            ];
          }
          // 只有确认有礼物时才显示弹窗
          showGift.value = true;
        } else {
          // 没有礼物时确保弹窗不显示
          showGift.value = false;
        }
      } catch (error) {
        console.error('Check gift failed:', error);
        showGift.value = false;
      }
    };

    // 启动定时检查礼包
    const startGiftPolling = () => {
      if (giftCheckTimer) {
        return; // 避免重复启动
      }
      giftCheckTimer = setInterval(() => {
        checkGiftStatus();
      }, 3000); // 每3秒检查一次
      
      // 立即执行一次
      checkGiftStatus();
    };

    // 停止定时检查
    const stopGiftPolling = () => {
      if (giftCheckTimer) {
        clearInterval(giftCheckTimer);
        giftCheckTimer = null;
      }
    };

    // 选择礼包
    const selectGiftBox = async (index) => {
      openingIndex.value = index;
      const selectedIndex = index + 1;

      // 开箱动画
      setTimeout(async () => {
        try {
          const res = await claim_gift({ gift_id: giftId.value, selected_gift: selectedIndex });
          openingIndex.value = -1;

          if (res.code === 0) {
            resultMessage.value = res.info;
            showResult.value = true;
            showGift.value = false;
            // 使用更高的z-index显示成功消息
            proxy.$Message({
              type: 'success',
              message: res.info,
              zIndex: 9999
            });

            // 3秒后关闭结果弹窗
            setTimeout(() => {
              showResult.value = false;
            }, 3000);
          } else {
            // 使用更高的z-index显示错误消息
            proxy.$Message({
              type: 'error',
              message: res.info,
              zIndex: 9999
            });
          }
        } catch (error) {
          openingIndex.value = -1;
          console.error('Claim gift failed:', error);
          proxy.$Message({
            type: 'error',
            message: 'Claim failed',
            zIndex: 9999
          });
        }
      }, 1000); // 1秒开箱动画
    };

    // 关闭礼包
    const onGiftClose = () => {
      showGift.value = false;
    };

    // 生命周期钩子
    onMounted(() => {
      startGiftPolling();
    });

    onUnmounted(() => {
      stopGiftPolling();
    });

    return {
      showGift,
      showResult,
      openingIndex,
      giftBoxes,
      resultMessage,
      selectGiftBox,
      onGiftClose,
      startGiftPolling,
      stopGiftPolling
    };
  }
};
</script>

<style lang="scss" scoped>
.gift-wrapper {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #fff;
  position: relative;
}

.gift-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  padding: 20px;
  position: absolute;
  top: 0;
  left: 0;
  margin-top: 50px;
}

.gift-title {
  font-size: 36px;
  font-weight: bold;
  color: #ffd700;
  text-align: center;
  flex: 1;
}

.gift-close {
  font-size: 48px;
  color: #fff;
  opacity: 0.5;
  pointer-events: none;
}

.gift-boxes {
  display: flex;
  justify-content: space-around;
  align-items: center;
  width: 100%;
  flex: 1;
}

.gift-box {
  width: 200px;
  height: 200px;
  background: linear-gradient(45deg, #ffd700, #ff8c00);
  border-radius: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 10px 20px rgba(0,0,0,0.3);
  animation: bounce 2s infinite;
  position: relative;
}

.gift-box:hover {
  transform: scale(1.1);
}

.gift-box.opening {
  animation: shake 1s ease-in-out;
}

.box-icon {
  font-size: 80px;
  margin-bottom: 10px;
  transition: transform 0.5s;
}

.gift-box.opening .box-icon {
  transform: rotateY(180deg);
}

.box-text {
  font-size: 24px;
  font-weight: bold;
  text-align: center;
  line-height: 1.2;
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% {
    transform: translateY(0);
  }
  40% {
    transform: translateY(-10px);
  }
  60% {
    transform: translateY(-5px);
  }
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
  20%, 40%, 60%, 80% { transform: translateX(5px); }
}

.result-wrapper {
  padding: 40px 20px;
  text-align: center;
}

.result-content {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.result-icon {
  font-size: 80px;
  margin-bottom: 20px;
}

.result-text {
  font-size: 32px;
  color: #ffd700;
  line-height: 1.4;
  max-width: 400px;
}
</style>