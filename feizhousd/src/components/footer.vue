<template>
    <div class="footer">
        <div class="f_li" v-for="item in list" :key="item.key" :class="item.key == 'obj' && 'obj'" @click="checkList(item.key)">
            <div class="span">
                <img :src="fooCheck == item.key ? item.img_check : item.img" class="img" alt="">
                <!-- <div class="text" v-if="item.key != 'obj'" :class="fooCheck == item.key && 'check'">{{$t('msg.'+item.key)}}</div> -->
                 <div class="text" :class="fooCheck == item.key && 'check'">{{$t('msg.'+item.key)}}</div>
            </div>
        </div>
    </div>
</template>
<script>
import { ref,reactive,watch } from 'vue';
import store from '@/store/index'
import { useRouter } from 'vue-router';
export default {
    setup(){
        const { push } = useRouter();
        const fooCheck = ref(store.state.fooCheck)
        const list = reactive([
            {
                img: require('@/assets/images/footer/homeb.png'),
                img_check: require('@/assets/images/footer/homea.png'),
                key: 'home'
            },
            {
                img: require('@/assets/images/footer/serviceb.png'),
                img_check: require('@/assets/images/footer/servicea.png'),
                key: 'tel'
            },
            
            {
                img: require('@/assets/images/footer/startb.png'),
                img_check: require('@/assets/images/footer/starta.png'),
                key: 'obj'
            },
            {
                img: require('@/assets/images/footer/recordb.png'),
                img_check: require('@/assets/images/footer/recorda.png'),
                key: 'order'
            },
            {
                img: require('@/assets/images/footer/myb.png'),
                img_check: require('@/assets/images/footer/mya.png'),
                key: 'self'
            },
        ])
        const checkList = (key) => {
            fooCheck.value = key
            store.dispatch('changefooCheck',fooCheck.value)
        }
        watch(fooCheck,(newValue)=>{
            push('/'+newValue)
        })
        return {list,fooCheck,checkList}
    }
}
</script>
<style lang="scss" scoped>
    .footer{
        display: flex;
        justify-content: space-around;
        // height: 130px;
        // background-image: url('~@/assets/images/footer/bg.png');
        // background-size: 100% 100%;
        position: fixed;
        width: 100%;
        bottom: -7px;
        left: 0;
        padding: 0 0 10px;
        z-index: 999;
        background: #fff;
        filter: drop-shadow(0 0 4px #bbb);
        .f_li{
            // width: 54px;
            // padding-top: 55px;
            // height: 100px;
            &.obj{
                width: auto;
                position: relative;
                padding-top: 0;
                margin-top: -50px;
                .span{
                    // position: absolute;
                    // left: 0;
                    // top: 0;
                    // transform: translateY(-50%);
                    background: #fff;
                    border-radius: 50%;
                    padding: 5px;
                    height: 300px;
                    .img{
                        height: 120px;
                        width: 120px;
                    }
                }
            }
            .span{
                height: 100%;
                position: relative;
                display: flex;
                flex-direction: column;
                justify-content: center;
                text-align: center;
                .img{
                    height: 64px;
                    width: 64px;
                    margin: 0 auto 10px;
                }
                .text{
                    font-size: 24px;
                    color: #a19fa8;
                    // margin-top: -10px;
                    // position: absolute;
                    // left: 50%;
                    // transform: translate(-50%);
                    // white-space: nowrap;
                    //width: 80PX;
                    white-space: wrap;
                    line-height: 1;
                    margin-bottom: 5px;
                    // height: 55px;
                    &.check{
                        color: #44a8ff;
                    }
                }
            }
        }
    }
</style>