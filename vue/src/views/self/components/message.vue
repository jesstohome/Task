<template>
    <div class="self home">
        <van-nav-bar :title="$t('msg.xttz')" left-arrow @click-left="$router.go(-1)"></van-nav-bar>
        
        <van-tabs v-model:active="active" type="card">
            <van-tab :title="$t('msg.xttz')" :name="1"></van-tab>
            <van-tab :title="$t('msg.znx')" :name="2"></van-tab>
        </van-tabs>
        <div class="list">
            <div class="box" v-for="item in info.filter(rr => rr.type == active)" :key="item.id">
                <div class="content">
                    <div class="title">{{item.title}}</div>
                    <div class="text">{{item.content}}</div>
                    <div class="t">{{formatTime('',item.addtime)}}</div>
                </div>
            </div>
            <div class="box not" v-if="info.filter(rr => rr.type == active).length == 0">
                <div class="img">
                    <img :src="require('@/assets/images/self/not_data.png')" class="g" alt="">
                </div>
                <div class="d">{{$t('msg.not_data')}}</div>
            </div>
        </div>
    </div>
</template>
<script>
import { ref} from 'vue';
import {getmsg} from '@/api/self/index'
import { useRouter } from 'vue-router';
import store from '@/store/index'
import {formatTime} from '@/api/format.js'
export default {
    setup(){
        const { push } = useRouter();
        const currency = ref(store.state.baseInfo?.currency)
        const topcheck = ref(0)
        const imgCheck = ref(1)
        const showCalendar = ref(false)
        const time1 = ref('')
        const active = ref(1)
        const info = ref([])
        const start = ref('')
        const end = ref('')

        const getjunior = () => {
            getmsg().then(res => {
                info.value = res.data?.info
            })
        }
        getjunior()
        const clickLeft = () => {
            push('/self')
        }
        
        const formatDate = (date,num) => `${date.getMonth() + 1}-${(date.getDate() - (num || 0))}`;
        const onConfirm = (values) => {
            const [s, e] = values;
            start.value = formatDate(s)
            end.value = formatDate(e)
            console.log(start.value)
            showCalendar.value = false;
            time1.value = `${formatDate(s)} — ${formatDate(e)}`;
            getjunior()
        }
        const topClick = (val) => {
            topcheck.value = val
            switch (val) {
                case 0:
                    time1.value =  ''
                    start.value = ''
                    end.value = ''
                    break;
                case 1:
                    let t = new Date()
                    time1.value = `${formatDate(t)}`
                    start.value = time1.value 
                    end.value = time1.value 
                    break;
                case 2:
                    let b = new Date()
                    time1.value = `${formatDate(b,1)}`
                    start.value = time1.value 
                    end.value = time1.value 
                    break;
                case 3:
                    let c = new Date()
                    time1.value = `${formatDate(c,7)} — ${formatDate(c)}`;
                    start.value = formatDate(c,7)
                    end.value = formatDate(c)
                    break;
            
                default:
                    break;
            }
            getjunior()
        }
        return {info,imgCheck,clickLeft,topcheck,showCalendar,time1,onConfirm,topClick,currency,formatTime,active}
    }
}
</script>
<style lang="scss" scoped>
@import '@/styles/theme.scss';
.self{
    overflow: auto;
    gap: 30px;
    :deep(.van-nav-bar){
        background-color: $theme;
        // position: absolute;
        // top: 0;
        // left: 0;
        color: #fff;
        width: 100%;
        .van-nav-bar__left{
            .van-icon{
                color: #fff;
                font-size: 30px;
            }
        }
        .van-nav-bar__title{
            color: #fff;
            font-size: 34px;
        }
        .van-nav-bar__content{
            line-height: 40px;
        }
        .van-nav-bar__right{
            img{
                height: 42px;
            }
        }
        &::after{
            display: none;
        }
    }
    :deep(.van-tabs) {
        .van-tabs__nav--card {
            border: var(--van-border-width-base) solid #44a8ff;
        }
        .van-tab--card{
            color: #44a8ff;
            border-right: var(--van-border-width-base) solid #44a8ff;
            background-color: #fff;
            &.van-tab--active{
                background-color: #44a8ff;
                color: #fff;
            }
        }
    }
    
    .list{
        padding: 0 30px;
        .box{
            margin-bottom: 30px;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: $shadow;
            .t{
                font-size: 18px;
                color: #999;
                margin-top: 20px;
            }
            .content{
                overflow: hidden;
                position: relative;
                text-align: left;
                .title{
                    font-size: 28px;
                    color: #333;
                    margin-bottom: 20px;
                }
                .text{
                    font-size: 22px;
                    color: #999;
                }
            }
            &.not{
                background-color: initial;
                .img{
                    width: 350px;
                    height: 400px;
                    overflow: hidden;
                    text-align: center;
                    margin: 0 auto;
                    .g{
                        width: 100%;
                    }
                }
                .d{
                    font-size: 30px;
                }
            }
        }
    }
}
</style>