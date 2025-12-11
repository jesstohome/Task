<template>
  <div class="home">
    <van-nav-bar :title="$t('msg.tikuan')" left-arrow @click-left="$router.go(-1)">
    </van-nav-bar>
    <van-form @submit="onSubmit">
      <van-cell-group inset>
          <div class="ktx">
              <div class="b">{{currency + money}}</div>
              <div class="t">{{$t('msg.my_yu_e')}}</div>
          </div>
          <div class="check_money">
              <div class="text">
                  <span class="tel">{{$t('msg.phone')}}</span>
                  <span>{{tel.slice(0, 3) + '****' +  tel.slice(-4)}}</span>
              </div>
              <div class="text">
                  <span>{{$t('msg.input_yhxz')}}</span>
              </div>
			  <div class="text">
			    <van-radio-group v-model="checked" >
			        <van-radio v-for="(item,index) in info" :key="index" :name="item.id" >
			            {{$t('msg.khlx')}} {{item.bank_type}} - {{item.cardnum.slice(0, 3) + '********' +  item.cardnum.slice(-4)}}
			        </van-radio>
			    </van-radio-group>
			  </div>
<!--              <div class="text"  v-for="(item,index) in info" :key="index">
                <van-radio-group v-model="checked" >
                    <van-radio>
                        <div class="label">{{$t('msg.khlx')}} {{item.bank_type}} - {{item.cardnum}}</div>
                    </van-radio>
                </van-radio-group>
              </div> -->
              <!-- <div class="text">
                  <van-radio-group v-model="types" direction="horizontal">
                    <van-radio name="1">{{$t('msg.bank_tx')}}</van-radio>
                    <van-radio name="2">{{$t('msg.usdt_tx')}}</van-radio>
                  </van-radio-group>
              </div> -->
              <!-- <span class="span" :class="(money_check == item&&!!money_check && 'check ') +  (!item && ' not_b')" @click="function(){if(item){money_check = item}}" v-for="(item,index) in moneys" :key="index">{{item}}</span>
              <span class="span" :class="(money_check == money && 'check not_b') " @click="money_check=money">{{$t('msg.all_tx')}}</span> -->
          </div>
        <div class="tixian_money">{{$t('msg.tixian_money')}}</div>
        <van-field
          class="zdy"
          v-model="money_check"
          :placeholder="$t('msg.tixian_money')"
        />
        <van-field
          class="zdy"
          v-model="paypassword"
          type="password"
          name="paypassword"
          :placeholder="$t('msg.tx_pwd')"
          :rules="[{ required: true, message: $t('msg.input_tx_pwd') }]"
        />
      </van-cell-group>
      <div class="buttons">
        <van-button round block type="primary" native-type="submit">
          {{$t('msg.true_tx')}}
        </van-button>
      </div>
      <div class="text_b" v-html="content">
      </div>
    </van-form>
  </div>
</template>

<script>
import { reactive, ref,getCurrentInstance } from 'vue';
import store from '@/store/index'
import {do_deposit, bind_bank} from '@/api/self/index.js'
import { useRouter,useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n'
import {getdetailbyid} from '@/api/home/index.js'
import { Dialog } from 'vant'
export default {
  name: 'HomeView',
  setup() {
    const { t } = useI18n()
    const { push } = useRouter();
    const route = useRoute();
    const {proxy} = getCurrentInstance()
    const paypassword = ref('')
    const info = ref({})
    const currency = ref(store.state.baseInfo?.currency)
    const tel = ref(store.state.userinfo?.tel)
    const types = ref('1')
    const content = ref('')
    const bank_code = ref('')
    const cardnum = ref('')
	const checked = ref('')
	const is_bind = ref(false)
    bind_bank().then(res => {
        if(res.code === 0) {
			let infoData = res.data?.info || [];
			info.value = infoData;
			
			if (infoData.length > 0) {
			  is_bind.value = true
			  checked.value = infoData[0]?.id || null;
			}
			
		// 	info.value = res.data?.info
		
		// 	if(info.value[0]['id']){
		// 		checked.value = info.value[0]['id']
		// 	}
            // bank_code.value = res.data.info?.bank_type
            // cardnum.value = res.data.info?.cardnum
	
			// bank_code.value = null
			// if(res.data.info){
			// 	bank_code.value = res.data.info.bank_type + '-' + res.data.info.cardnum
			// }
        }
    })

    getdetailbyid(14).then(res => {
        content.value = res.data?.content
    })

    const money_check = ref(100)
    const money = ref(route.query?.param)
    const moneys = ref(store.state.baseInfo?.recharge_money_list)

    
    const clickLeft = () => {
        push('/self')
    }
    const clickRight = () => {
        push('/tel')
    }
    

    const onSubmit = (values) => {
        if (!is_bind.value) {
            Dialog.confirm({
                title: '',
            message:
                t('msg.tjtkxx'),
            })
            .then(() => {
                // on confirm
                push('/bingbank')
            })
            .catch(() => {
                // on cancel
            });
            return false
        }
        let json = {
			bid: checked.value,
            num: money_check.value ==0 ? money.value : money_check.value,
            type: 'bank',
            paypassword: values.paypassword,
            // types: types.value,
        }
        do_deposit(json).then(res => {
            if(res.code === 0) {
                proxy.$Message({ type: 'success', message:res.info});
                push('/self')
            } else {
                proxy.$Message({ type: 'error', message:res.info});
            }
        })
    };



    return {
		checked,
        paypassword,
        types,
        onSubmit,
        clickLeft,
        bank_code,
		cardnum,
        clickRight,
        info,
        money,
        currency,
        money_check,
        moneys,
        content,
        tel
    };
  }
}
</script>

<style scoped lang="scss">
@import '@/styles/theme.scss';
.home{
    margin-bottom: 100px;
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
            font-size: 30px;
            img{
                height: 42px;
            }
        }
    }
    :deep(.van-form){
        padding: 40px 0 0;

        .van-cell.van-cell--clickable{
            border-left: 5px solid $theme;
            padding: 32px;
            text-align: left;
            margin: 20px 0;
            border-bottom: none;
            box-shadow: $shadow;
            .van-cell__right-icon{
                color: $theme;
            }
        }
        .van-cell-group--inset{
            padding: 0 30px;
            background-color: initial;
        }
        .van-cell{
            padding: 23px 10px;
            border-bottom: 1px solid  var(--van-cell-border-color);
            &.zdy {
                margin-bottom: 20px;
                border-radius: 40px;
                padding-left: 30px;
            }
            .van-field__left-icon{
                width:90px;
                text-align: center;
                .van-icon__image{
                    height: 42px;
                    width: auto;
                }
                .icon{
                    height: 42px;
                    width: auto;
                    vertical-align:middle;
                }
                .van-dropdown-menu{
                  .van-dropdown-menu__bar{
                    height: auto;
                    background: none;
                    box-shadow: none;
                  }
                  .van-cell{
                    padding: 30px 80px;
                  }
                }
            }
            .van-field__control{
                font-size: 24px;
            }
            &::after {
                display: none;
            }
        }
        .van-checkbox{
            margin: 30px 0 60px 0;
            .van-checkbox__icon{
                font-size: 50px;
                margin-right: 80px;
                &.van-checkbox__icon--checked .van-icon{
                    background-color:$theme;
                    border-color:$theme;
                }
            }
            .van-checkbox__label{
                font-size: 24px;
            }
        }
        .text_b{
            margin:70px 60px 40px;
            font-size: 27px;
            color: #333;
            text-align: left;
            line-height: 1.5;
            .tex{
                margin-top: 20px;
            }
        }
        .buttons{
            padding: 0 76px;
            .van-button{
                font-size: 28px;
                padding: 20px 0;
                height: auto;
            }
            .van-button--plain{
                margin-top: 40px;
            }
        }
        .tixian_money{
            text-align: left;
            font-size: 30px;
            margin-bottom: 20px;
            color: #333;
        }
        .ktx{
            width: 100%;
            height: 190px;
            border-radius: 20px;
            padding: 24px 50px;
            text-align: left;
            // margin-bottom: 35px;
            background-color: $theme;
            text-align: center;
            .t{
                font-size: 20px;
                color: #fff;
                margin-bottom: 10px;
                opacity: 0.7;
            }
            .b{
                font-size: 50px;
                color: #fefefe;
                margin-bottom: 20px;
            }
        }
        .check_money{
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 40px;
            background-color: #fff;
            padding: 24px;
            border-radius: 20px;
            color: #333;
            .text{
                display: flex;
                width: 100%;
                text-align: left;
                font-size: 28px;
                margin-bottom: 25px;
                height: 40px;
                .van-radio{
                    font-size: 30px;
                    .van-radio__icon{
                        font-size: 30px;
                    }
                    .van-radio__label{
                        line-height: 40px;
                    }
                }
                span{
                    flex: 1;
                    &.tel{
                        color: #999;
                    }
                }
            }
        }
    }

    :deep(.van-){
        .van-dialog__content{
            padding: 50px;
        }
        .van-dialog__footer{
            .van-dialog__confirm{
                color: $theme;
            }
        }
    }
}
</style>
