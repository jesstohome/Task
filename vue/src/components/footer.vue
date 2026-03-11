<template>
    <div class="footer">
        <div style="display: flex;width: 100%;justify-content: space-around;">
            <div class="f_li" v-for="item in list" :key="item.key" @click="checkList(item.key)">
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
                img: require('@/assets/images/footer/home_b.png'),
                img_check: require('@/assets/images/footer/home_a.png'),
                key: 'home'
            },
            
            {
                img: require('@/assets/images/footer/starting_b.png'),
                img_check: require('@/assets/images/footer/starting_a.png'),
                key: 'obj'
            },
            {
                img: require('@/assets/images/footer/records_b.png'),
                img_check: require('@/assets/images/footer/records_a.png'),
                key: 'order'
            },
            {
                img: require('@/assets/images/footer/my_b.png'),
                img_check: require('@/assets/images/footer/my_a.png'),
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
        .span{
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
            height: 140px;
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