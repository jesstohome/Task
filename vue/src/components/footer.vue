<template>
    <div class="footer">
        <div style="display: flex;width: 100%;justify-content: space-around;">
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
import { ref,reactive,watch,computed } from 'vue';
import store from '@/store/index'
import { useRouter } from 'vue-router';
export default {
    setup(){
        const { push } = useRouter();
        // 使用 computed 来直接监听 store 的状态变化
        const fooCheck = computed({
            get: () => store.state.fooCheck,
            set: (value) => {
                store.dispatch('changefooCheck', value)
            }
        })
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
            // 无论值是否改变，都进行导航
            push('/'+key)
        }
        watch(() => fooCheck.value, (newValue) => {
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
    // height: 150px;
    // position: fixed;
    //     width: 100%;
        // bottom: -18px;
        // left: 0;
        // padding: 0;
        // z-index: 999;
        // background: #fff;
        // filter: drop-shadow(0 0 4px #bbb);
        /* iOS Safari 优化 */
        -webkit-transform: translateZ(0);
        transform: translateZ(0);
        will-change: transform;
    
    .f_li{
        width: 20%;
        // 保持原有样式...
        &.obj{
            // width: auto;
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