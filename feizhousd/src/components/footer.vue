<template>
    <div class="footer">
        <div style="display: flex;width: 750px;justify-content: space-around;">
            <div class="f_li" v-for="item in list" :key="item.key" :class="item.key == 'obj' && 'obj'" @click="checkList(item.key)">
                    <div class="span">
                        <img :src="fooCheck == item.key ? item.img_check : item.img" class="img" alt="">
                        <!-- <div class="text" v-if="item.key != 'obj'" :class="fooCheck == item.key && 'check'">{{$t('msg.'+item.key)}}</div> -->
                        <div class="text" :class="fooCheck == item.key && 'check'">{{$t('msg.'+item.key)}}</div>
                    </div>
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
    padding: 0 0 18px 0;
    /* ⭐ 删除以下样式，因为在 App.vue 中已经统一设置了 */
    /* position: fixed; */
    /* width: 100%; */
    /* max-width: 750px; */
    /* bottom: 0; */
    /* left: 50%; */
    /* transform: translateX(-50%); */
    /* z-index: 999; */
    /* background: #fff; */
    /* box-shadow: ...; */
    
    .f_li{
        width: 20%;
        // 保持原有样式...
        &.obj{
            width: auto;
            position: relative;
            padding-top: 0;
            margin-top: -35px;
            .span{
                background: #ffffff00;
                padding: 5px;
                height: 220px;
                .img{
                    height: 100px;
                    width: 100px;
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
                font-size: 26px;
                color: #a19fa8;
                white-space: wrap;
                line-height: 1;
                &.check{
                    color: #2c7ce7;
                }
            }
        }
    }
}
</style>