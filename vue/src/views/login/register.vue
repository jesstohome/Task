<template>
  <div class="homes">
    <login-top hide-lang :title="$t('msg.register')" left-arrow></login-top>
    <van-form @submit="onSubmit">
      <van-cell-group inset>
        
        <!-- <van-field
         label-width="100"
          class="zdy"
          v-model="tel"
          name="tel"
          :label="$t('msg.phone')+'('+area_code[0].text+')'"
          :placeholder="$t('msg.phone')"
          :rules="[{ required: true, message: $t('msg.input_phone') }]"
          
        > -->
        <van-field
         label-width="100"
          class="zdy"
          v-model="tel"
          name="tel"
          :label="$t('msg.phone')"
          :placeholder="$t('msg.phone')"
          :rules="[{ required: true, message: $t('msg.input_phone') }]"
          
        >

          <!-- <template #left-icon>
            <van-dropdown-menu active-color="#6833ff">
              <van-dropdown-item v-model="qv" :options="area_code" />
            </van-dropdown-menu>
          </template> -->
        </van-field>
        <!-- <van-field
         label-width="100"
          v-model="userName"
          name="userName"
          :placeholder="$t('msg.input_username')"
          :rules="[{ required: true, message: $t('msg.input_username') }]"
        /> -->
        <van-field
         label-width="100"
          v-model="pwd"
          type="password"
          name="pwd"
          :label="$t('msg.pwd')"
          :placeholder="$t('msg.pwd')"
          :rules="[{ required: true, message: $t('msg.input_pwd') }]"
        />
        <van-field
         label-width="100"
          v-model="pwd2"
          type="password"
          name="pwd2"
          :label="$t('msg.true_pwd')"
          :placeholder="$t('msg.true_pwd')"
          :rules="[{ required: true, message: $t('msg.input_true_pwd') }]"
        />
        <van-field        
         label-width="100"
          v-model="depositPwd"
          name="depositPwd"
          :label="$t('msg.tx_pwd')"
          :placeholder="$t('msg.tx_pwd')"
          :rules="[{ required: true, message: $t('msg.input_t_pwd') }]"
        />
        <van-field
        style="border-bottom: 1px solid #fff;"
         label-width="100"
          v-model="invite_code"
          name="invite_code"
          :label="$t('msg.code')"
          :placeholder="$t('msg.code')"
          :rules="[{ required: true, message: $t('msg.input_code') }]"
        />
      </van-cell-group>
      <div class="buttons">
        <van-button block type="primary" native-type="submit">
          {{$t('msg.register1')}}
        </van-button>
      </div>
    </van-form>
  </div>
</template>

<script>
import loginTop from './index.vue'
import { ref,getCurrentInstance,watch,computed } from 'vue';
import store from '@/store/index'
import { useRouter,useRoute } from 'vue-router';
import {do_register,login} from '@/api/login/index'
import { Toast } from 'vant';
export default {
  name: 'HomeView',
  components: {loginTop},
  setup() {
    const { push } = useRouter();
    const route = useRoute();
    const {proxy} = getCurrentInstance()
    const baseInfo = ref(store.state.baseInfo)


    const invite_code = ref('');
    if(route.query?.invite_code){
      invite_code.value = route.query?.invite_code
    }
	
	const type = ref('1');
	if(route.query?.type){
	  type.value = route.query?.type
	}
	
    const tel = ref('');
    const pwd = ref('');
    const pwd2 = ref('');
    const depositPwd = ref('');
    const userName = ref('');
    const option = ref((baseInfo.value?.area_code) || [])
    const area_code = ref(option.value.map(rr => {return {text: rr, value: rr}}))
    const onSubmit = (values) => {
	  values.type = type.value
      if (values.pwd != values.pwd2) {
        Toast.fail('两次输入的密码不正确')
        return false
      }
      const json = JSON.parse(JSON.stringify(values))
      delete json.pwd2
      do_register(json).then(res => {
        if(res.code === 0) {
          proxy.$Message({ type: 'success', message: res.info});
          let info = {
            tel: tel.value,
            pwd: pwd.value,
          }
          login(info).then(red => {
            if(red.code === 0) {
              store.dispatch('changetoken',red.token)
              store.dispatch('changeuserinfo',red.userinfo || {})
              proxy.$Message({ type: 'success', message: red.info});
              // 记住密码
                const useri = {...json,...{checked: true}}
                store.dispatch('changeUser',useri)
              push('/')
            } else {
              proxy.$Message({ type: 'error', message: red.info});
            }

          })
        } else {
          proxy.$Message({ type: 'error', message: res.info});
        }
      })
    };
    const qv = ref(area_code.value[0]?.value || '55');
    watch(() => store.state.baseInfo,(newVal)=>{
      baseInfo.value = {...newVal}
      option.value = (newVal?.area_code) || []
      area_code.value = option.value.map(rr => {return {text: '+ '+rr, value: rr}})
    }, { deep: true })
    return {
      tel,
      pwd,
      pwd2,
      depositPwd,
      userName,
      invite_code,
      onSubmit,
      area_code,
      qv
    };
  }
}
</script>

<style scoped lang="scss">
@import '@/styles/theme.scss';
.homes{
  margin-bottom: 200px;
  //background-image: linear-gradient(rgb(10, 66, 255), rgb(11, 199, 255));
    :deep(.van-cell){
        font-size: 30px;
        line-height: 30px;
      }
    :deep(.van-field__error-message){
      font-size: 30px;
    }
    :deep(.van-form){
      padding-bottom: 40px;
        .van-cell-group--inset{
            padding: 0 50px;
            background-color: initial;
        }
        .van-ellipsis{
          color: #000;
        }
        .van-cell{
            padding: 34px 10px;
            border-bottom: 1px solid #b2b2b2;
            background-color: initial;
            &.zdy{
              .van-field__left-icon{
                margin-right: 30px;
              }
            }
            .van-field__label{
              width: 200px !important;
            }
            .van-field__left-icon{
                margin-right: 90px;
                .van-icon__image{
                    height: 42px;
                    width: auto;
                }
                .icon{
                    height: 42px;
                    width: auto;
                    vertical-align:middle;
                }
                display: flex;
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
                font-size: 30px;
            }
            .van-field__label{
              color: #000;
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
                font-size: 30px;
            }
        }
        .buttons{
            padding: 0 76px;
            .van-button{
                font-size: 30px;
                padding: 26px 0;
                height: auto;
                margin-top: 40px;
            }
        }
    }
  :deep(.van-nav-bar){
        background-color: #000000;
        color: #fff;
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
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
}
</style>
