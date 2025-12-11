<template>
    <div class="tel home">
        <van-nav-bar :title="$t('msg.czjl')" left-arrow @click-left="$router.go(-1)" @click-right="clickRight">
            <template #right>
                <van-icon name="comment-o" size="18"/>
            </template>
        </van-nav-bar>
        <van-list
            v-model:loading="loading"
            :offset="100"
            :finished="finished"
            :finished-text="$t('msg.not_move')"
            @load="getCW"
            >
                <div class="address" v-for="(item,index) in list" :key="index">
                    <div class="l">
                        <div class="time">{{formatTime('',item.addtime)}}</div>
                        <div class="tag">{{tabs.find(rr => rr.value == item.status)?.label || item.status}}</div>
                    </div>
                    <div class="r">
                        {{currency}}{{item.num}}
                    </div>
                </div>
        </van-list>
    </div>
</template>
<script>
import { ref} from 'vue';
import {recharge_admin} from '@/api/self/index'
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n'
import store from '@/store/index'
import {formatTime} from '@/api/format.js'
export default {
    setup(){
        const { push } = useRouter();
        const { t } = useI18n()
        
        const currency = ref(store.state.baseInfo?.currency)
        const list = ref([])
        const page = ref(0)
        const type = ref('all')

        const loading = ref(false);
        const finished = ref(false);

        const tabs = ref([
            {label: t('msg.xdcg'),value: 1},
            {label: t('msg.czcg'),value: 2},
            {label: t('msg.czsb'),value: 3},
        ])

        const clickLeft = () => {
            push('/self')
        }
        const clickRight = () => {
            push('/message')
        }
        const getCW = (num) => {
            if(num) {
                page.value = num
            } else {
                ++page.value
            }
            let json = {
                page: page.value,
                size: 10,
            }
            recharge_admin(json).then(res => {
                if(res.code === 0) {
                    loading.value = false
                    finished.value = !res.data?.paging
                    list.value = list.value.concat(res.data?.list || [])
                }
            })
        }
        return {formatTime,list,clickLeft,clickRight,tabs,getCW,loading,finished,currency}
    }
}
</script>
<style lang="scss" scoped>
.tel{
    overflow: auto;
    :deep(.van-nav-bar){
        background-color: $theme;
        margin-bottom: 40px;
        color: #fff;
        .van-nav-bar__left{
            .van-icon{
                color: #fff;
            }
        }
        .van-nav-bar__title{
            color: #fff;
        }
        .van-nav-bar__right{
            img{
                height: 42px;
            }
            .van-icon{
                color: #fff;
            }
        }
    }
    :deep(.van-list){
        .van-loading{
            background: initial;
            color: #666;
            .van-loading__text{
                color: #666;
            }
        }
        .address{
            box-shadow: $shadow;
            border-radius: 12px;
            padding: 30px;
            margin: 0 30px 40px;
            background-image: url('~@/assets/images/self/address/bg.png');
            background-size: 100% 100%;
            text-align: left;
            display: flex;
            justify-content: space-between;
            .l{
                .time{
                    font-size: 28px;
                    font-weight: 600;
                    color: #333;
                }
                .tag{
                    margin-top: 30px;
                    padding: 2px 20px;
                    background-color: $theme;
                    color: #fff;
                    width: 150px;
                    text-align: center;
                    border-radius: 40px;
                    font-size: 18px;
                }
            }
            .r{
                display: flex;
                flex-direction: column;
                justify-content: center;
                font-size: 24px;
                font-weight: 600;
                color: #48ca7c;
            }
        }
    }
}
</style>