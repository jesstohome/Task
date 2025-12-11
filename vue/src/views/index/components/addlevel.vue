<template>
  <div class="home">
    <van-nav-bar left-arrow @click-left="$router.go(-1)" @click-right="clickRight">
        <template #right>
            <img :src="require('@/assets/images/self/hank/tel.png')" class="img" height="25" alt="">
        </template>
    </van-nav-bar>
    <div class="van-form">
        <div class="djzq">{{$t('msg.djzq')}}</div>
        <div class="hy_box">
            <img :src="require('@/assets/images/news/vip.png')" class="img" width="55 " alt="">
            <div class="b">
                <span class="text">{{vip_info?.name}}</span>
                <div class="sub">{{currency}}{{vip_info?.num}}</div>
                <div class="sub">{{$t('msg.yonj1')}}：{{(vip_info?.bili*100).toFixed(1)}}% </div>
                <div class="sub">{{$t('msg.jdsl')}}：{{vip_info?.order_num}} / {{$t('msg.day')}}</div>
                <div class="sub">{{$t('msg.zdye')}}：{{currency}}{{vip_info?.num_min}}</div>
            </div>
        </div>
        <div class="pay" v-if="master_bank == 1">
            <!-- <div class="title">{{$t('msg.zf_type')}}</div> -->
            <van-radio-group>
                <van-cell-group inset>
                    <van-cell clickable>
                        <template #title>
                            <img :src="require('@/assets/images/chongzhi/0.png')" width="15" class="img" alt="">
                            <!-- {{item.name}} -->
                            {{$t('msg.yezf')}}
                        </template>
                        <template #right-icon>
                            <van-radio icon-size="15px" checked-color="#00ff00" v-model="checked"/>
                        </template>
                    </van-cell>
                </van-cell-group>
            </van-radio-group>
        </div>
        <div class="pay" v-if="master_bank == 2">
            <div class="title">{{$t('msg.zf_type')}}</div>
            <van-radio-group v-model="checked">
                <van-cell-group inset>
                    <van-cell :name="item.id" @click="checked=item.id" clickable v-for="(item,index) in pay" :key="index">
                        <template #title>
                            <img :src="require('@/assets/images/chongzhi/'+(index+1 > 3 ? 1 : index+1)+'.png')" class="img" alt="">
                            {{item.name}}
                        </template>
                        <template #right-icon>
                            <van-radio :name="item.id" />
                        </template>
                    </van-cell>
                </van-cell-group>
            </van-radio-group>
        </div>
                
                
        <!-- <div class="text_b">
            <p class="tex">{{$t('msg.fkddyz')}}</p>
            <p class="tex">{{$t('msg.zxsj')}}</p>
        </div> -->
        <div class="buttons">
            <van-button round block type="primary" @click="onSubmit">
            {{$t('msg.lisj')}}
            </van-button>
        </div>
      </div>
  </div>
</template>

<script>
import { reactive, ref,getCurrentInstance } from 'vue';
import store from '@/store/index'
import {get_recharge,get_recharge2} from '@/api/home/index.js'
import { useRouter,useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n'
import {bank_recharge} from '@/api/home/index.js'
export default {
  name: 'HomeView',
  setup() {
    const { t } = useI18n()
    const { push, back } = useRouter();
    const route = useRoute();
    const {proxy} = getCurrentInstance()
    const paypassword = ref('')
    const master_bank = ref('')
    const info = ref({})
    const currency = ref(store.state.baseInfo?.currency)
    const vip_info = ref({})
    const pay = ref([])
    const checked = ref(true)

    get_recharge({vip_id: route.query?.vip}).then(res => {
        vip_info.value = {...(res.data?.vip_info || {})}
        master_bank.value = res.data?.master_bank || ''
        pay.value = [...(res.data?.pay || {})]
    })
    
    const clickLeft = () => {
        push('/self')
    }
    const clickRight = () => {
        push('/tel')
    }
    // next_cz checked

    const onSubmit1 = () => {
        const info = pay.value?.find(rr => rr.id == checked.value)
        let json = {
            num: vip_info.value?.num,
            payId: info?.id,
        }
        bank_recharge(json).then(res => {
            if(res.code === 0) {
                proxy.$Message({ type: 'success', message:res.info});
                back()
            } else {
                proxy.$Message({ type: 'error', message:res.info});
            }
        })
    };

    const onSubmit = () => {
        console.log(master_bank)
        if (master_bank.value != 1) {
            onSubmit1()
            return false
        }
        let json = {
            num: vip_info.value?.num,
            vip_id: route.query?.vip,
        }
        bank_recharge(json).then(res => {
            if(res.code === 0) {
                proxy.$Message({ type: 'success', message:res.info});
                back()
            } else {
                proxy.$Message({ type: 'error', message:res.info});
            }
        })
    };

    return {
        checked,
        vip_info,
        pay,
        master_bank,
        onSubmit,
        onSubmit1,
        clickLeft,
        clickRight,
        info,
        currency,
    };
  }
}
</script>

<style scoped lang="scss">
@import '@/styles/theme.scss';
.home{
    :deep(.van-nav-bar){
        background-color: $theme;
        color: #fff;
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
    }
    :deep(.van-form){
        padding: 40px 30px 0;
        
        .text_b{
            margin: 150px 60px 40px;
            font-size: 18px;
            color: #999;
            text-align: left;
            .tex{
                margin-top: 20px;
            }
        }
        .buttons{
            margin-top: 48px;
            // padding: 0 76px;
            .van-button{
                font-size: 24px;
                padding: 20px 0;
                height: auto;
                border-radius: 0;
            }
            .van-button--plain{
                margin-top: 40px;
            }
        }
        .djzq{
            font-size: 32px;
            line-height: 26px;
            margin-top: 30px;
            margin-bottom: 30px;
            text-transform: uppercase;
            text-align: left;
            font-size: #333;
        }
            .hy_box{
                height: 230px;
                width: 100%;
                padding: 25px;
                color: #fff;
                background: url('~@/assets/images/news/vip-bg.png') 50%/160PX 64PX no-repeat,linear-gradient(240deg,#0c2442,#4f7492)!important;
                border-radius: 10px;
                overflow: hidden;
                position: relative;
                display: flex;
                .img{
                    width: auto;
                    margin-right: 40px;
                    vertical-align: middle;
                }
                .b{
                    font-size: 18px;
                    text-align: left;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    flex: 1;
                    .text{
                        font-size: 32px;
                    }
                    .sub{
                        margin-top: 10px;
                        .line{
                            margin: 0 22px;
                        }
                    }
                }
            }
        .pay{
            margin-top: 24px;
            text-align: left;
            .title{
                padding-left: 30px;
                border-left: 10px solid $theme;
                font-size: 24px;
                color: #333;
                margin-bottom: 5px;
            }
            .van-radio-group{
                .van-cell{
                    padding: 24px;
                }
                .van-cell__title{
                    .img{
                        // width: 25px;
                        margin-right: 10px;
                        vertical-align: middle;
                    }
                }
            }
        }
    }
}
</style>
