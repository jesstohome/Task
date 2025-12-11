<template>
    <div class="tel">
        <van-nav-bar @click-right="clickRight">
            <template #right>
                <!-- <van-icon name="comment-o" size="18"/> -->
                <img :src="require('@/assets/images/news/msg3.png')" class="xiaoxiimg" alt="">
            </template>
        </van-nav-bar>
        <!-- <img :src="require('@/assets/images/tel/bg.png')" alt="" class="bg"> -->
        <div class="bg">
            <!-- <span class="span">{{$t('msg.kffw')}}</span> -->
            <!-- <span class="span" @click="toTel()">{{$t('msg.kffw')}}</span> -->
        </div>
        <div class="tent">
            <div class="box" v-for="(item,index) in list" :key="index">
                <div class="right">
                    <div class="flex">
                        <div class="title">{{item.username}}</div>
                        <div class="time">{{item.btime}}——{{item.etime}}</div>
                        <van-button block type="primary" style="padding: 5px;margin: 5px;" @click="tel(item)">{{$t('msg.ljzx')}}</van-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { ref} from 'vue';
import {getsupport} from '@/api/tel/index'
import { useRouter } from 'vue-router';
import store from '@/store/index'
export default {
    setup(){
        const { push } = useRouter();
        const tel = (row) => {
            window.location.href= row.url
        }
        const clickRight = () => {
            push('/message')
        }
        store.dispatch('changefooCheck','tel')
        const list = ref([])
        getsupport().then(res => {
            if(res.code === 0) {
                list.value = res.data || []
            }
        })
        const toTel = () => {
            if (list.value) {
                location.href = list.value
                // window.open(support.value)
            }
        }
        return {tel,list,clickRight, toTel}
    }
}
</script>
<style lang="scss" scoped>
@import '@/styles/theme.scss';
.tel{
    .van-nav-bar{
        width: 100%;
        background-color: $theme;
    }
    .xiaoxiimg{
            width: 40px;
            height: 40px;
        }
    .bg{
        width: 100vw;
        height: 260px;
        background-color: $theme;
        display: flex;
        flex-direction: column;
        justify-content: center;
        // padding-bottom: 46PX;
        font-size: 34px;
        color: #fff;
        font-family: "PingFang SC,Helvetica Neue,Helvetica,Arial,Hiragino Sans GB,Heiti SC,Microsoft YaHei,WenQuanYi Micro Hei,sans-serif"!important;
        // padding-top: var(--van-nav-bar-height);
    }
    .tent{
        width: 100%;
        padding: 0 30px;
        margin-top: -120px;
        position: relative;
        .box{
            width: 100%;
            height: 450px;
            background-image: url('~@/assets/images/tel/tel.png');
            background-size: 100% 100%;
            box-shadow: $shadow;
            border-radius: 30px;
            padding: 85px 45px 72px 0;
            text-align: right;
            .right{
                max-width: 400px;
                display: inline-block;
                text-align: center;
                height: 100%;
                .flex{
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                        align-items: center;
                    height: 100%;
                }
                .title{
                    font-size: 36px;
                    color: #333;
                }
                .time{
                    font-size: 20px;
                    color: #999;
                }
                .van-button{
                    padding: 0;
                    height: 72px;
                    font-size: 30px;
                    width: 290px;
                    margin: 0 auto;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    ::v-deep(.van-button__content){
                        width: 100%;
                    }
                }
            }
        }
    }
}
</style>