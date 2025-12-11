<template>
    <div class="home">
        <van-nav-bar :title="title" left-arrow @click-left="$router.go(-1)"></van-nav-bar>
        <div class="content" v-html="content"></div>
    </div>
</template>
<script>
    import { reactive, ref,getCurrentInstance } from 'vue';
    import store from '@/store/index'
    import { useRouter,useRoute } from 'vue-router';
    import {getdetailbyid} from '@/api/home/index'
    export default {
        name: 'HomeView',
        setup() {
            const { push } = useRouter();
            const route = useRoute();
            const id = route.query?.id
            const title = route.query?.title
            const content = ref('')

            getdetailbyid(route.query?.id).then(res => {
                content.value = res.data?.content
            })

            return {id,title,content}
        }
    }
</script>

<style scoped lang="scss">
@import '@/styles/theme.scss';
.home{
    overflow: hidden !important;
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
    .content{
        margin: 40px 30px;
        box-shadow: $shadow;
        text-align: left;
        padding: 40px 30px;
        font-size: 30px;
        color: #333;
        line-height: 1.8;
        overflow: auto;
        border-radius: 12px;
        background-color: #fff;
        flex: 1;
    }
}
</style>