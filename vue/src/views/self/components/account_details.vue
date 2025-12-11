<template>
    <div class="tel home">
        <van-nav-bar :title="$t('msg.zbjl')" left-arrow @click-left="$router.go(-1)">
            <!-- <template #right>
                <van-icon name="comment-o" size="18"/>
            </template> -->
        </van-nav-bar>
        <!-- <van-tabs v-model:active="type" swipeable @change="getCW">
            <van-tab v-for="(item,index) in tabs" :key="index" :title="item.label" :name="item.value"></van-tab>
        </van-tabs> -->
        <van-list
            v-model:loading="loading"
            :finished="finished"
            :immediate-check="false"
            :offset="100"
            :finished-text="$t('msg.not_move')"
            @load="getCW"
            :loading-text="$t('msg.loading')"
            >
                <div class="address" v-for="(item,index) in list" :key="index">
                    <div class="l">
                        <div class="time red">{{$t('msg.zblx')}}：{{tabs?.find(rr=>rr.value==item.type)?.label || $t('msg.all')}}</div>
                        <div class="time">{{$t('msg.zqje')}}：{{currency}} {{item.balance || '0.00'}}</div>
                        <div class="time green">{{$t('msg.zbje')}}：{{currency}} {{item.num}}</div>
                        <div class="time">{{$t('msg.zhje')}}：{{currency}} {{item.newnum}}</div>
                        <div class="time">{{$t('msg.zbsj')}}：{{formatTime('',item.addtime)}}</div>
                    </div>
                </div>
        </van-list>
    </div>
</template>
<script>
import { ref} from 'vue';
import {caiwu} from '@/api/self/index'
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
            // {label: t('msg.all'),value: 'all'},
            {label: t('msg.chongzhi'),value: 1},
            {label: t('msg.tixian'),value: 7},
            {label: t('msg.jiaoyi'),value: 2},
            {label: t('msg.fanyong'),value: 3},
            {label: t('msg.tgfy'),value: 5},
            {label: t('msg.xjjyfy'),value: 6},
            {label: 'Web3' + t('msg.zr'),value: 21},
            {label: 'Web3' + t('msg.out_money'),value: 22},
            {label: 'Web3' + t('msg.get_m'),value: 23},
        ])

        const clickLeft = () => {
            push('/self')
        }
        const clickRight = () => {
            push('/message')
        }
        const getCW = (name,num) => {
            if(num) {
                page.value = num
            } else {
                ++page.value
            }
            type.value = name && name != 'all' ? name : 0
            let json = {
                page: page.value,
                size: 10,
                type: type.value
            }
            caiwu(json).then(res => {
                loading.value = false
                if(res.code === 0) {
                    finished.value = !(res.data?.paging)
                    list.value = list.value.concat(res.data?.list)
                }
            })
        }
        getCW()
        return {formatTime,list,clickLeft,clickRight,tabs,getCW,loading,finished,currency}
    }
}
</script>
<style lang="scss" scoped>
.tel{
    overflow: auto;
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
            font-size: 30px;
            img{
                height: 42px;
            }
            .van-icon{
                color: #fff;
            }
        }
    }
    :deep(.van-tabs){
        padding: 20px 0;
        .van-tabs__nav{
            // justify-content: space-around;
            .van-tab{
                // flex: auto;
                &.van-tab--active{
                    font-weight: 600;
                    color: $theme;
                }
            }
            .van-tabs__line{
                background-color: $theme;
            }
        }
    }
    :deep(.van-list){
        padding-top: 24px;
        
        .address{
            box-shadow: $shadow;
            border-radius: 12px;
            padding: 30px;
            margin: 0 30px 40px;
            text-align: left;
            display: flex;
            justify-content: space-between;
            background-color: #fff;
            .l{
                .time{
                    font-size: 28px;
                    // font-weight: 600;
                    color: #333;
                    &.red{
                        color: red;
                    }
                    &.green{
                        color: green;
                    }
                }
            }
        }
    }
}
</style>