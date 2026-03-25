<template>
    <div class="home">
        <van-nav-bar>
            <template #left>
                <img :src="logo" class="logo-head" height="30" alt="">
            </template>
            <template #right>
                <img @click="showMenu = true" :src="require('@/assets/images/home/list.png')" class="xiaoxiimg" alt="">
            </template>
        </van-nav-bar>
        <!-- 图片列表 -->
        <div class="image-list">
            <img v-for="(img, idx) in imageList" :key="idx" :src="img" class="full-width-img" alt="">
        </div>
        <!-- 左侧菜单弹出层 -->
        <van-popup v-model:show="showMenu" position="left" :style="{ width: '80%', height: '100vh', background: 'linear-gradient(rgb(18, 62, 51), rgb(16, 44, 98)) !important' }" teleport="body" :z-index="99999">
            <div class="menu-wrapper">
                <div class="menu-header">
                    <img :src="logo" class="menu-logo" alt="logo">
                    <div class="menu-close" @click="showMenu = false">✕</div>
                </div>
                <div class="menu-list">
                    <div class="menu-item" @click="toRoute('/wfp'); showMenu = false">
                        <span>WFP</span>
                        <span class="arrow">›</span>
                    </div>
                    <div class="menu-item" @click="toDetails(4, $t('msg.dlhz')); showMenu = false">
                        <span>{{ $t('msg.dlhz') }}</span>
                        <span class="arrow">›</span>
                    </div>
                    <div class="menu-item" @click="toRoute('/drawing', monney, 2); showMenu = false">
                        <span>{{ $t('msg.tixian') }}</span>
                        <span class="arrow">›</span>
                    </div>

                    <div class="menu-item" @click="toRoute('/chongzhi'); showMenu = false">
                        <span>{{ $t('msg.chongzhi') }}</span>
                        <span class="arrow">›</span>
                    </div>
                    <div class="menu-item" @click="toDetails(3, $t('msg.gzms')); showMenu = false">
                        <span>{{ $t('msg.gzms') }}</span>
                        <span class="arrow">›</span>
                    </div>
                    <div class="menu-item" @click="toDetails(10, 'Event'); showMenu = false">
                        <span>Event</span>
                        <span class="arrow">›</span>
                    </div>

                    <!-- <div class="menu-item" @click="toRoute('/service'); showMenu = false">
                        <span>{{ $t('msg.tel') }}</span>
                        <span class="arrow">›</span>
                    </div> -->
                    <!-- <div class="menu-item" @click="toRoute('/libao', monney); showMenu = false">
                        <span>Web3</span>
                        <span class="arrow">›</span>
                    </div> -->
                    
                    <div class="menu-item" @click="toDetails(12, $t('msg.qyzz')); showMenu = false">
                        <span>{{ $t('msg.qyzz') }}</span>
                        <span class="arrow">›</span>
                    </div>
                    <div class="menu-item" @click="toDetails(2, $t('msg.gsjj')); showMenu = false">
                        <span>{{ $t('msg.gsjj') }}</span>
                        <span class="arrow">›</span>
                    </div>
                    <div class="menu-item" @click="toDetails(7, 'AML'); showMenu = false">
                        <span>AML</span>
                        <span class="arrow">›</span>
                    </div>
                    
                    
                </div>
            </div>
        </van-popup>

        <!-- 首次登录弹窗 -->
        <van-popup v-model:show="showFirstLoginModal" position="center" overlay-class="first-login-overlay" :style="{ padding: '0',background: '#ffffff00' }" teleport="body" :z-index="100000">
            <div class="first-login-card">
                <!-- <div class="first-login-content" v-html="firstLoginContent"></div> -->
                <img :src="require('@/assets/images/home/tanchuang.jpg')" alt="" width="100%" height="100%">
            </div>
            <div class="tanchuang_close" @click="closeFirstLoginModal"><van-icon name="close" /></div>
        </van-popup>

        <!-- 礼包组件 -->
        <GiftPackage v-model="showGift" />
    </div>
</template>
<script>
import { ref, getCurrentInstance, onMounted } from 'vue';
import store from '@/store/index'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router';
import { getdetailbyid } from '@/api/home/index.js'
import GiftPackage from '@/components/gift/index.js';
import { Toast } from 'vant';
export default {
    components: {
        GiftPackage
    },
    setup(){
        const { push } = useRouter();
        const { t } = useI18n()
        const showMenu = ref(false)
        const showGift = ref(false)
        const showFirstLoginModal = ref(false)
        const firstLoginContent = ref('')
        const FIRST_LOGIN_FLAG_KEY = 'home_first_login_popup_shown'

        const logo = ref(store.state.baseInfo?.site_icon)
        const monney = ref(store.state.minfo?.balance)
        
        const tryShowFirstLoginPopup = () => {
            if (!localStorage.getItem(FIRST_LOGIN_FLAG_KEY)) {
                showFirstLoginModal.value = true
                // getdetailbyid(1).then(res => {
                //     firstLoginContent.value = res.data?.content || ''
                // }).catch(() => {
                //     firstLoginContent.value = ''
                // })
            }
        }

        const closeFirstLoginModal = () => {
            showFirstLoginModal.value = false
            localStorage.setItem(FIRST_LOGIN_FLAG_KEY, '1')
        }

        // 设置footer导航选中状态
        store.dispatch('changefooCheck','home')

        // 页面加载时初始化礼包检查
        // 礼包检查逻辑已在GiftPackage组件中自动处理（自动定时轮询）
        onMounted(() => {
            showGift.value = true; // 初始化组件，组件会自动启动定时检查
            tryShowFirstLoginPopup()
        })

        // 图片列表
        const imageList = ref([
            require('@/assets/images/h1.png'),
            require('@/assets/images/h2.png'),
            require('@/assets/images/h3.png'),
            require('@/assets/images/h4.png')
        ])

        const toDetails = (id,title) => {
            push('/content?id='+id + '&title='+title)
        }
        const toRoute = (path,param,type = 1) => {
            if (path){
                if(path == '/wfp'){
                    window.location.href= 'https://www.wfp.org/'
                    return false
                }
                push(path + (param? '?param='+param : ''))
            }
        }

        return {showMenu, showGift, showFirstLoginModal, firstLoginContent, closeFirstLoginModal, logo, monney, imageList, toRoute, toDetails}
    }
}
</script>
<style lang="scss" scoped>
@import '@/styles/theme.scss';

.first-login-card {
    width: min(95vw, 760px);
    // background: #ffffff;
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.25);
    max-height: 80vh;
    overflow-y: auto;
    border-radius: 80px;
}
.tanchuang_close{
    color: #bbbbbb;
    text-align: center;
    margin: 15px auto;
    font-size: 50px;
}
.first-login-content {
    padding-bottom: 16px;
    color: #262626;
    font-size: 14px;
    line-height: 1.5;
}

:deep(.first-login-overlay) {
    background: rgba(0, 0, 0, 0.56);
}

.home{
        position: relative;
        overflow-x: hidden;
        overflow-y: auto;
        display: block !important;
        :deep(.van-nav-bar){
            color: #333;
            padding: 20px 0;
            .van-nav-bar__left{
                .van-icon{
                    color: #fff;
                }
            }
            .van-nav-bar__title{
                color: #333;
                font-weight: 600;
                font-size: 28px;
            }
        }
        .logo-head{
            width: 250px;
            height: 65px;
        }
        .xiaoxiimg{
            width: 60px;
            height: 60px;
        }
        // 图片列表样式
        .image-list{
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            .full-width-img{
                width: 100%;
                height: auto;
                display: block;
            }
        }
    }

/* 左侧菜单弹出层样式 */
:deep(.van-popup) {
    z-index: 99999 !important;
    position: fixed !important;
}

:deep(.van-overlay) {
    z-index: 99998 !important;
    position: fixed !important;
}

.menu-wrapper {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    background: linear-gradient(rgb(18, 62, 51), rgb(16, 44, 98));
    position: relative;
    pointer-events: auto;
}
.menu-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 30px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    .menu-logo {
        height: 40px;
        width: auto;
    }
    .menu-close {
        font-size: 36px;
        color: #fff !important;
        cursor: pointer !important;
        width: 40px;
        height: 40px;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        pointer-events: auto !important;
        position: relative !important;
        z-index: 10002 !important;
    }
}
.menu-list {
    flex: 1;
    overflow-y: auto;
    padding-top: 10px;
    pointer-events: auto;
}
.menu-item {
    padding: 24px 30px;
    font-size: 32px;
    color: #fff !important;
    border-bottom: 1px solid #fff !important;
    cursor: pointer !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    pointer-events: auto !important;
    position: relative !important;
    z-index: 10002 !important;
    .arrow {
        font-size: 40px;
        font-weight: 300;
    }
}
</style>