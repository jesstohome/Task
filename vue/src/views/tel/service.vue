<template>
    <div class="tel">
        <van-nav-bar :title="$t('msg.kffw')" left-arrow @click-left="$router.go(-1)">
            <template #right>
                <!-- <van-icon name="comment-o" size="18"/> -->
                <!-- <img :src="require('@/assets/images/news/msg3.png')" width="26.5" alt=""> -->
            </template>
        </van-nav-bar>
        <!-- <img :src="require('@/assets/images/tel/bg.png')" alt="" class="bg"> -->
        
        <div class="tent">
            <div class="box" v-for="(item,index) in list" :key="index">
                <div class="right">
                    <div class="flex">
                        <div class="title">{{item.username}}</div>
                        <div class="time">{{item.btime}}——{{item.etime}}</div>
                        <van-button block round style="padding: 2px;" color="#991aff" @click="tel(item)">{{$t('msg.ljzx')}}</van-button>
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
            window.location.href= row.url+'&metadata={"name":"'+store.state.userinfo.username+'","comment":"UserID:'+store.state.userinfo.id+'"}'
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
    :deep(.van-nav-bar){
            width: 100%;
            background-color: $theme;
            color: #333;
            padding: 20px 0;
            .van-nav-bar__left{
                .van-icon{
                    color: #fff;
                    font-size: 30px;
                }
            }
            .van-nav-bar__content{
                height: 80px;
            }
            .van-nav-bar__title{
                color: #ffffff;
                font-weight: 600;
                font-size: 32px;
                line-height: 60px;
            }
        }

    .bg{
        width: 100%;
        height: 60px;
        background-color: $theme;
        display: flex;
        flex-direction: column;
        justify-content: center;
        font-size: 34px;
        color: #fff;
        font-family: "PingFang SC,Helvetica Neue,Helvetica,Arial,Hiragino Sans GB,Heiti SC,Microsoft YaHei,WenQuanYi Micro Hei,sans-serif"!important;
    }
    .tent{
        width: 100%;
        padding: 30px 30px 0;
        position: relative;
        .box{
            width: 100%;
            height: 250px;
            background-image: url('~@/assets/images/tel/tel.png');
            background-size: 100% 100%;
            box-shadow: $shadow;
            border-radius: 30px;
            padding: 85px 45px 72px 0;
            text-align: right;
            margin-bottom: 30px;
            .right{
                max-width: 400px;
                display: inline-block;
                text-align: center;
                height: 100%;
                .flex{
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                    height: 100%;
                }
                .title{
                    font-size: 36px;
                    color: $textColor;
                }
                .time{
                    font-size: 20px;
                    color: $textSecondary;
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