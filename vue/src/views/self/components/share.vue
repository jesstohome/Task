<template>
    <div class="tel home">
        <van-nav-bar left-arrow @click-left="$router.go(-1)"></van-nav-bar>
        <div class="yqhy">{{$t('msg.yqhylqyj')}}</div>
        <div class="content">
            <div class="top">
                <div class="title">
                    <img :src="require('@/assets/images/news/money.png')" alt="" class="img" width="20">
                    <img :src="require('@/assets/images/news/money.png')" alt="" class="img" width="20">
                    <img :src="require('@/assets/images/news/money.png')" alt="" class="img" width="20">
                </div>
                <div class="c" v-html="a_content"></div>
                <div class="b">
                    {{$t('msg.tgm')}} <span class="span">{{info.invite_code}}</span>
                </div>
                <div class="bottom" @click="copy(info?.url)">{{$t('msg.fzyqlj')}}</div>
            </div>
        </div>
    </div>
</template>
<script>
import { ref,getCurrentInstance} from 'vue';
import {get_invite} from '@/api/self/index'
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n'
import store from '@/store/index'
import {formatTime} from '@/api/format.js'
// import {getdetailbyid} from '@/api/home/index'
// 复制函数
import useClipboard from 'vue-clipboard3';
export default {
    setup(props, ctx){
        const {proxy} = getCurrentInstance()
        const { t } = useI18n()
        const { toClipboard } = useClipboard();
        const { push } = useRouter();
        const a_content = ref('')
        const info = ref({})
        ctx.emit('hideFooter',true)
        const clickLeft = () => {
            push('/self')
        }
        get_invite().then(res => {
            info.value = {...res.data}
            a_content.value = res.data?.invite_msg
        })
        // getdetailbyid(1).then(res => {
        // })
        const copy = (value) => {
            try {
                toClipboard(value);
                proxy.$Message({ type: 'success', message:t('msg.copy_s')});
            } catch (e) {
                proxy.$Message({ type: 'error', message:t('msg.copy_b')});
            }
        }
        return {clickLeft,a_content,copy,info}
    }
}
</script>
<style lang="scss" scoped>
.tel{
    overflow: hidden;
    // background-image: url("~@/assets/images/self/share.png");
    // background-size: 100% 100%;
    position: relative;
    .yqhy {
        height: 400px;
        width: 100%;
        background-color: $theme;
        display: flex;
        flex-direction: column;
        justify-content: center;
        color: #fff;
        font-size: 50px;
        padding-top: 60px;
    }
    :deep(.van-nav-bar){    
        position: absolute !important;
        width: 100%;
        background-color: inherit;
        color: #fff;
        z-index: 10;
        .van-nav-bar__left{
            .van-icon{
                color: #fff;
            }
        }
        .van-nav-bar__title{
            color: #fff;
        }
        .van-nav-bar__right{
            .van-icon{
                color: #fff;
            }
        }
    }
    .content{
        width: 100%;
        padding: 0 25px;
        border-radius: 30px;
        flex: 1;
        overflow: auto;
        // background-color: #fff;
        .bottom{
            width: 100%;
            height: 74px;
            line-height: 74px;
            font-size: 32px;
            background-color: $theme;
            color: #fff;
            border-radius: 6px;
            margin-top: 20px;
        }
        .top{
            padding:20px;
            background-color: #fff;
            border-radius: 20px;
            .title{
                margin-bottom: 20px;
            }
            .c{
                font-size: 22px;
                line-height: 2;
                text-indent: 2em;
                color: #666;
                margin-bottom: 20px;
            }
            .b{
                width: 290px;
                margin: 0 auto;
                font-size: 26px;
                color: #333;
                position: relative;
                .span{
                    margin-left: 5px;
                }
            }
        }
    }
}
</style>