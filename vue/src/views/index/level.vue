<template>
    <div class="level-page">
        <van-nav-bar
            :title="$t('msg.viplervel')"
            left-arrow
            @click-left="$router.back()"
        />
        <div class="content">
            <div class="vip-list">
                <div class="vip-card" v-for="(item, idx) in levelList" :key="item.id">
                    <div class="card-content">
                        <div class="card-left">
                            <img v-if="item.pic" :src="item.pic" class="thumb" alt="">
                        </div>
                        <div class="card-right">
                            <div class="card-header">
                                <div class="name">
                                    <div>
                                        {{ item.name }}
                                    </div>
                                    
                                    <div class="join-btn" v-if="mInfo.level == item.level" :class="{ 'is-current': mInfo.level == item.level }">
                                        {{ $t('msg.now_level') }}
                                    </div>
                                </div>
                                <div class="price" v-if="item.name == 'VIP1'">{{ $t('msg.shxyh') }}</div>
                                <div class="price" v-else>{{ item.num }} {{ currency }}</div>
                            </div>
                            <div class="card-info">
                                <div class="line">● {{ $t('msg.zhzjcg')}} {{ item.num }} {{ currency }}</div>
                                <div class="line">● {{ $t('msg.mxrwlr') }} {{((item.bili || 0)*100).toFixed(2)}}%</div>
                                <div class="line">● {{ $t('msg.mtrwcs') }} {{ item.order_num }}</div>
                            </div>
                            <!-- <div class="join-btn" @click="addLevel(item)" :class="{ 'is-current': mInfo.level == item.level }">
                                {{ mInfo.level == item.level ? $t('msg.now_level') : mInfo.level < item.level ? $t('msg.join') : '' }}
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import store from '@/store/index'
import { get_level_list } from '@/api/home/index'

export default {
    setup() {
        const router = useRouter()
        const levelList = ref([])
        const currency = ref(store.state.baseInfo?.currency)
        const mInfo = ref(store.state.userinfo || {})

        onMounted(() => {
            getLevelList()
        })


        const getLevelList = async () => {
            try {
                const res = await get_level_list()
                if (res.code === 0) {
                    levelList.value = res.data || []
                }
            } catch (error) {
                console.error('获取VIP等级列表失败:', error)
            }
        }

        const addLevel = (item) => {
            if (item.level <= mInfo.value?.level) {
                router.push('/obj')
            } else {
                router.push('/addlevel?vip=' + item.id)
            }
        }

        return {
            levelList,
            currency,
            mInfo,
            addLevel
        }
    }
}
</script>

<style lang="scss" scoped>
.level-page {
    min-height: 100vh;
    background: #f5f5f5;

    :deep(.van-nav-bar){
        background-color: $theme;
        color: #fff;
        .van-nav-bar__left{
                .van-icon{
                    color: #fff;
                    font-size: 30px;
                }
            }
            .van-nav-bar__content{
                height: 60px;
            }
            .van-nav-bar__title{
                color: #ffffff;
                font-weight: 600;
                font-size: 32px;
                line-height: 60px;
            }
        .van-nav-bar__right{
            img{
                height: 42px;
            }
        }
    }
    
    .content {
        padding: 20px;
    }

    .vip-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .vip-card {
        background: #fff;
        border-radius: 16px;
        margin: 20px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid #e4e4e4;
        .card-content {
            display: flex;
            gap: 24px;
        }

        .card-left {
            .thumb {
                width: 120px;
                height: 120px;
                object-fit: contain;
            }
        }

        .card-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .card-header {
            text-align: left;
            .name {
                font-size: 32px;
                font-weight: 800;
                color: #333;
                margin-bottom: 8px;
                display: flex;
                align-items: flex-end;
                justify-content: flex-start;               
            }
            
            .price {
                font-size: 28px;
                color: #1a7ae7;
                font-weight: 600;
            }
        }

        .card-info {
            display: flex;
            flex-direction: column;
            gap: 12px;

            .line {
                color: #666;
                font-size: 26px;
                line-height: 1.5;
                display: flex;
                align-items: center;
                gap: 8px;
            }
        }

        .join-btn {
            text-align: center;
            background: linear-gradient(135deg, #4c6fff 0%, #3d5afe 100%);
            color: #fff;
            font-size: 26px;
            font-weight: 600;
            margin-top: 8px;
            padding: 3px;
            margin-left: 15px;
            &.is-current {
                background: #2b7ae7;
            }
        }
    }
}
</style>