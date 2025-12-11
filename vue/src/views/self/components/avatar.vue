<template>
    <div class="self home">
        <div class="top">
            <div class="info">
                <van-nav-bar :title="$t('msg.shaddress')" left-arrow @click-left="$router.go(-1)"></van-nav-bar>
                <div class="avaitar">
                    <img :src="info.headpic || require('@/assets/images/self/avater/'+imgCheck+'.png')" class="img" alt="">
                    <div class="right">
                        <div class="title">
                            {{$t('msg.now_set')}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="list">
            <img :src="require('@/assets/images/self/avater/'+item+'.png')" class="img" alt="" v-for="item in 31" :key="item" :class="imgCheck == item && 'check'" @click="imgCheck=item">
        </div>
    </div>
</template>
<script>
import { ref} from 'vue';
import {getself} from '@/api/self/index'
import store from '@/store/index'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router';
export default {
    setup(){
        const { push } = useRouter();
        const imgCheck = ref(1)
        const info = ref({})
        getself().then(res => {
            if(res.code === 0) {
                info.value = {...res.data?.info}
            }
        })
        const clickLeft = () => {
            push('/self')
        }
        return {info,imgCheck,clickLeft}
    }
}
</script>
<style lang="scss" scoped>
.self{
    overflow: auto;
    :deep(.van-nav-bar){
        background-color: $theme;
        position: absolute;
        top: 0;
        left: 0;
        color: #fff;
        width: 100%;
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
        }
        &::after{
            display: none;
        }
    }
    .top{
        padding: 100px 50px;
        background-color: $theme;
        color: #fff;
        position: relative;
        .info{
            .avaitar{
                // display: flex;
                // height: 155px;
                margin-bottom: 25px;
                .img{
                    height: 155px ;
                    margin-bottom: 20px;
                }
                .right{
                    text-align: center;
                    .title{
                        display: inline-block;
                        font-size: 30px;
                        padding: 7px 30px;
                        color: #fff;
                        border: 1px solid #fff;
                        border-radius: 40px;
                    }
                }
            }
        }
    }
    .list{
        border-radius: 30px;
        position: relative;
        background-color: #fff;
        text-align: left;
        overflow: hidden;
        margin-top: -85px;
        display: flex;
        flex-wrap: wrap;
        .img{
            width: 90px;
            margin: 40px 30px;
            border-radius: 50%;
            &.check{
                box-shadow:0 0 2.666667vw 0 $theme;
            }
        }
    }
}
</style>