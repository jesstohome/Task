<template>
  <div class="home">
    <van-nav-bar :title="$t('msg.chongzhi')" left-arrow @click-left="$router.go(-1)" @click-right="clickRight">
        <template #right>
            {{$t('msg.record')}}
        </template>
    </van-nav-bar>
    <div class="van-form">
        <div class="pay">
            <div class="title">{{$t('msg.zf_type')}}</div>
            <van-radio-group v-model="checked">
                <van-radio :class="checked == item.id && 'check'" :name="item.id" v-for="(item,index) in pay" :key="index"  checked-color="rgb(247, 206, 41)">
                    <div class="label">{{item.name}}</div>
                </van-radio>
            </van-radio-group>
        </div>
        <div class="warn" v-if="checkInfo.min">
            <span class="l">{{$t('msg.jexz')}}</span>
            <span class="r">{{currency + ' ' + checkInfo?.min}} ~ {{currency + ' ' + checkInfo?.max}}</span>
        </div>
        <van-field
          class="zdy"
          v-model="money_check"
          :label="$t('msg.czje')"
          :placeholder="$t('msg.czje')"
        >
            <template #extra>
                {{currency}}
            </template>
        </van-field>
          <div class="check_money">
              <span class="span" :class="(money_check == item&&!!money_check && 'check ') +  (!item && ' not_b')" @click="function(){if(item){money_check = item}}" v-for="(item,index) in moneys" :key="index">{{item}}</span>
          </div>
                
                
        <div class="text_b" v-html="content">
        </div>
        <div class="buttons">
            <van-button round block type="primary" @click="onSubmit">
            {{$t('msg.chongzhi')}}
            </van-button>
        </div>
      </div>
  </div>
</template>

<script>
import { reactive, ref,getCurrentInstance, watch } from 'vue';
import store from '@/store/index'
import {get_recharge,get_recharge2,getdetailbyid} from '@/api/home/index.js'
import { useRouter,useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n'
import { Toast } from 'vant'
export default {
  name: 'HomeView',
  setup() {
    const { t } = useI18n()
    const { push } = useRouter();
    const route = useRoute();
    const {proxy} = getCurrentInstance()
    const paypassword = ref('')
    const info = ref({})
    const checkInfo = ref({})
    const currency = ref(store.state.baseInfo?.currency)
    const pay = ref([])
    const checked = ref('')
    const content = ref('')

    getdetailbyid(15).then(res => {
        content.value = res.data?.content
    })

    get_recharge({vip_id: route.query?.vip}).then(res => {
        pay.value = [...(res.data?.pay || {})]
        checked.value = pay.value[0]?.id
    })
    const money_check = ref(100)
    const moneys = ref(store.state.baseInfo?.recharge_money_list)
    
    const clickLeft = () => {
        push('/self')
    }
    const clickRight = () => {
        push('/account_details')
    }
    watch(()=>checked.value,(val)=>{
        console.log(val)
        checkInfo.value = pay.value.find(rr => rr.id == val)
    })
    // next_cz checked

    const onSubmit = () => {
        const info = pay.value?.find(rr => rr.id == checked.value)
        // console.log(info)
        // return false
        let json = {
            id: info?.id,
            type: info?.type,
            price: money_check.value
        }
        console.log(json)
        Toast.loading({
            message: t('msg.loading')+'...',
            forbidClick: true,
            duration: 0,
        })
        get_recharge2(json).then(res => {
            Toast.clear()
            if(res.code == 0) {
                switch (res.data.py_status*1) {
                    case 1:
                        push({path:'/next_cz',query:res.data})
                        break;
                    case 2:
                        push({path:'/next_cz2',query:res.data})
                        break;
                    case 3:
                        if(res.data?.url){
                            location.href = res.data.url
                        }
                        break;
                    default:
                        break;
                }
            } else {
                proxy.$Message({ type: 'error', message: res.info});
            }
        })
    };



    return {
        checked,
        checkInfo,
        pay,
        onSubmit,
        clickLeft,
        clickRight,
        info,
        currency,
        money_check,
        moneys,
        content,
    };
  }
}
</script>

<style scoped lang="scss">
@import '@/styles/theme.scss';
.home{
    margin-bottom: 100px;
    font-size: 30px;
    :deep(.van-radio__label){
        line-height: 50px;
    }
    :deep(.van-radio__icon){
        width: 40px;
        height: 40px;
        font-size: 30px;
    }
    :deep(.van-nav-bar){
        background-color: $theme;
        // color: #fff;
        // .van-nav-bar__left{
        //     .van-icon{
        //         color: #fff;
        //     }
        // }
        // .van-nav-bar__title{
        //     color: #fff;
        // }
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
            color: #fff;
            font-size: 30px;
            img{
                height: 42px;
            }
        }
    }
    :deep(.van-form){
        padding: 40px 30px 0;
        .zdy{
            padding: 15px;
            background-color: rgba(219,228,246,.8);
            margin-bottom: 15px;
            font-size: 30px;
            line-height: 40px;
        }
        
        .text_b{
            margin: 70px 60px 40px;
            font-size: 28px;
            color: #999;
            text-align: left;
            .tex{
                margin-top: 20px;
            }
        }
        .buttons{
            padding: 0 24px;
            .van-button{
                font-size: 30px;
                padding: 15px 0;
                height: auto;
                border-radius: 5px;
                background-color: rgb(247, 206, 41);
                border: none;
                color: #333;
            }
            .van-button--plain{
                margin-top: 40px;
            }
        }
        .check_money{
            display: flex;
            flex-wrap: wrap;
            // justify-content: space-between;
            margin-bottom: 40px;
            color: #333;
            .span{
                width: 30%;
                line-height: 32px;
                text-align: center;
                border-radius: 6px;
                border: 1px solid $theme;
                font-size: 30px;
                margin-bottom: 20px;
                margin-left: 5%;
                background-color: #fff;
                &:nth-child(3n+1){
                    margin-left: 0;
                }
                &.not_b{
                    border: none;
                }
                &.check{
                    border: none;
                    background-color: $theme;
                    color: #fff;
                }
            }
        }
        .ktx{
            width: 100%;
            height: 190px;
            background-image: url('~@/assets/images/self/money/drawing.png');
            background-size: 100% 100%;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0 50px;
            text-align: left;
            margin-bottom: 35px;
            .t{
                font-size: 30px;
                color: #bfa8ff;
                margin-bottom: 10px;
            }
            .b{
                font-size: 50px;
                color: #fefefe;
            }
        }
        .warn{
            white-space: nowrap;
            color: #333;
            margin: 40px 0;
            text-align: left;
            .l {
                margin-right: 50px;
            }
        }
        .pay{
            text-align: left;
            .title{
                font-size: 32px;
                color: #333;
            }
            .van-radio-group{
                display: flex;
                flex-wrap: wrap;
                margin: 24px 0;
                .van-radio {
                    width: 32%;
                    height: 180px;
                    border: 1px solid #ccc;
                    position: relative;
                    overflow: initial;
                    border-radius: 6px;
                    margin-right: 20px;
                    display: -webkit-box;
                    -webkit-box-direction: reverse;
                    -webkit-box-orient: vertical;
                    -webkit-box-pack: center;
                    &.check {
                        background-color:#3f57e8;
                        color: #fff;
                        border: none;
                        .van-radio__label{
                            color: #fff;
                        }
                    }
                    .van-radio__label{
                        margin-left: 0;
                        .label{
                            text-align: center;
                        }
                    }
                    .van-radio__icon{
                        margin: 0 auto;
                    }
                }
            }
        }
    }
}
</style>
