<template>
    <div class="tel home">
        <van-nav-bar :title="$t('msg.txjl')" left-arrow @click-left="$router.go(-1)" @click-right="clickRight">
            <template #right>
                <van-icon name="comment-o" />
            </template>
        </van-nav-bar>
        <van-list
            :finished="finished"
            :offset="100"
            :finished-text="$t('msg.not_move')"
            @load="getCW"
            >
                <div class="address" v-for="(item,index) in list" :key="index">
                    <div class="l">
                        <div class="time">{{$t('msg.sqsl')}}：{{currency}}{{item.num || '0.00'}}</div>
                        <div class="time">{{$t('msg.sxf')}}：0，{{$t('msg.dz')}}：{{currency}}{{item.num || '0.00'}}</div>
                        <div class="time">{{$t('msg.sqsj')}}：{{formatTime('',item.addtime)}}</div>
                        <div class="time red" style="color: red;">{{tabs.find(rr => rr.value == item.status)?.label || item.status}}</div>
                        <!-- <div class="time">{{formatTime('',item.addtime)}}</div>
                        <div class="tag">{{tabs.find(rr => rr.value == item.status)?.label || item.status}}</div> -->
                    </div>
                    <!-- <div class="r">
                        {{currency}}{{item.num}}
                    </div> -->
                </div>
        </van-list>
    </div>
</template>
<script>
import { ref} from 'vue';
import {deposit_admin} from '@/api/self/index'
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

        const loading = ref(true);
        const finished = ref(false);

        const tabs = ref([
            {label: t('msg.dsh'),value: 1},
            {label: t('msg.withdrawal_successful'),value: 2},
            {label: t('msg.withdrawal_failedl'),value: 3},
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
            deposit_admin(json).then(res => {
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
@import "@/styles/theme.scss";
.tel{
    overflow: auto;
    :deep(.van-nav-bar){
        background-color: $theme;
        margin-bottom: 40px;
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
            .van-icon{
                color: #fff;
                font-size: 32px;
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
			background-color: #FFF;
            // background-image: url('~@/assets/images/self/address/bg.png');
            background-size: 100% 100%;
            text-align: left;
            display: flex;
            justify-content: space-between;
            .l{
                .time{
                    font-size: 28px;
                    // font-weight: 600;
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