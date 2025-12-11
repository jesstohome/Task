<template>
    <div class="tel home">
        <van-nav-bar :title="$t('msg.zrjl')" left-arrow @click-left="$router.go(-1)" @click-right="clickRight">
            <template #right>
                <van-icon name="comment-o" size="18"/>
            </template>
        </van-nav-bar>
        <van-list
            v-model:loading="loading"
            :finished="finished"
            :finished-text="$t('msg.not_move')"
            @load="getCW"
            >
                <div class="address" :class="item.is_qu&&'mb30'" v-for="(item,index) in list" :key="index">
                    <div class="l">
                        <div class="time">{{$t('msg.zr') + 'Web3 ' + item.day + $t('msg.day') + item.bili*100 + '%'}}</div>
                        <div class="tag">{{item.num}}</div>
                    </div>
                    <div class="c">
                        <div class="time">{{$t('msg.crsj')}}：{{formatTime('',item.addtime)}}</div>
                        <div class="tag">{{$t('msg.zr') + 'Web3 '}}</div>
                    </div>
                    <div class="r" v-if="!item.is_qu" @click="qu_money(item)">
                       {{$t('msg.out_money')}}
                    </div>
                </div>
        </van-list>
    </div>
</template>
<script>
import { ref,getCurrentInstance} from 'vue';
import {lixibao_chu,get_lixibao_chu} from '@/api/home/index'
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n'
import store from '@/store/index'
import {formatTime} from '@/api/format.js'
export default {
    setup(){
    const {proxy} = getCurrentInstance()
        const { push } = useRouter();
        const { t } = useI18n()
        
        const currency = ref(store.state.baseInfo?.currency)
        const list = ref([])
        const page = ref(1)
        const type = ref('all')

        const loading = ref(true);
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
        const getCW = () => {
            let json = {
                page: 1,
                size: 10,
            }
            lixibao_chu(json).then(res => {
                if(res.code === 0) {
                    finished.value = !res.data?.paging
                    list.value = res.data?.list
                }
            })
        }
        getCW()
        const qu_money = (row) => {
            proxy.$dialog.confirm({
                title: t('msg.wxts'),
                message: t('msg.sure_qc'),
                confirmButtonText:t('msg.queren'),
                cancelButtonText:t('msg.quxiao')
            })
            .then(() => {
                get_lixibao_chu({id:row.id}).then(res => {
                    if(res.code === 0) {
                        proxy.$Message({ type: 'success', message:res.info});
                        getCW()
                    } else {
                        proxy.$Message({ type: 'error', message:res.info});
                    }
                }).catch(rr => {
                    console.log(rr)
                })
            })
            .catch(() => {
                // on cancel
            });
        }
        return {formatTime,list,clickLeft,clickRight,tabs,getCW,loading,finished,currency,qu_money}
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
            padding: 30px 30px 120px;
            margin: 0 30px 40px;
            // background-image: url('~@/assets/images/self/address/bg.png');
            // background-size: 100% 100%;
            text-align: left;
            background-color: #fff;
            &.mb30{
                padding-bottom: 30px;
            }
            .l{
                display: flex;
                justify-content: space-between;
                margin-bottom: 30px;
                .time{
                    font-size: 30px;
                    font-weight: 600;
                    color: #333;
                }
                .tag{
                    font-size: 18px;
                    font-weight: 600;
                    color: $theme;
                }
            }
            .c{
                display: flex;
                justify-content: space-between;
                margin-bottom: 30px;
                .time{
                    font-size: 22px;
                    font-weight: 600;
                    color: #999;
                }
                .tag{
                    font-size: 22px;
                    font-weight: 600;
                    color: #333;
                }
            }
            .r{
                width: 200px;
                height: 60px;
                text-align: center;
                line-height: 60px;
                background-color: $theme;
                color: #fff;
                border-radius: 10px;
                float: right;
                font-size: 25px;
            }
        }
    }
}
</style>