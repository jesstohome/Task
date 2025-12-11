<template>
  <div class="homes">
    <login-top></login-top>
    <van-form @submit="onSubmit">
      <van-cell-group inset>
        <van-field
          class="zdy"
          name="tel"
          v-model="tel"
          :placeholder="$t('msg.input_username')"
          :rules="[{ required: true, message: $t('msg.input_username') }]"
          
        >
        </van-field>
        <van-field
          style="border-bottom: 1px solid #fff;"
          v-model="pwd"
          type="password"
          name="pwd"
          :placeholder="$t('msg.input_pwd')"
          :rules="[{ required: true, message: $t('msg.input_pwd') }]"
        />
      </van-cell-group>
      <div class="buttons">
        <van-button block type="primary" native-type="submit">
          {{$t('msg.login')}}
        </van-button>
        <van-button block type="default" @click="$router.push({path: '/register'})">
          {{$t('msg.register')}}
        </van-button>
<!--        <van-button round block plain  type="primary" @click="toDown()">
          {{$t('msg.appDown')}}
        </van-button> -->
      </div>
    </van-form>
  </div>
</template>

<script>
import loginTop from './index.vue'
import { watch, ref, getCurrentInstance } from 'vue';
import store from '@/store/index'
import {login} from '@/api/login/index.js'
import { useRouter } from 'vue-router';
export default {
  name: 'HomeView',
  components: {loginTop},
  setup() {
    const { push } = useRouter();
    const {proxy} = getCurrentInstance()
    const baseInfo = ref(store.state.baseInfo)
    const option = ref((baseInfo.value?.area_code) || [])
    const userjs = ref(store.state.user)
    const area_code = ref(option.value.map(rr => {return {text: '+ '+rr, value: rr}}))
    const checked = ref(userjs.value?.checked || false);
    const tel = ref(userjs.value?.tel || '');
    const pwd = ref(userjs.value?.pwd || '');
    const qv = ref(userjs.value?.qv || area_code.value[0]?.value);

    const toDown = () => {
      console.log(baseInfo.value.app_url)
      if(baseInfo.value.app_url){
        window.location.href=baseInfo.value.app_url
      }
    }

    localStorage.clear()

    const onSubmit = (values) => {
      const json = {...values}
      login(json).then(res => {
        console.log(res)
        if(res.code === 0) {
          store.dispatch('changetoken',res.token)
          store.dispatch('changeuserinfo',res.userinfo || {})
          proxy.$Message({ type: 'success', message: res.info});
          // 记住密码
          if (checked.value) {
            const useri = {...json,...{checked: checked.value}}
            store.dispatch('changeUser',useri)
          } else {
            store.dispatch('clearUser','')
          }
          push('/')
        } else {
          proxy.$Message({ type: 'error', message: res.info});
        }

      })
    };
    watch(() => store.state.baseInfo,(newVal)=>{
      console.log(newVal)
      baseInfo.value = {...newVal}
      option.value = (newVal?.area_code) || []
      area_code.value = option.value.map(rr => {return {text: '+ '+rr, value: rr}})
      qv.value = area_code.value[0]?.value
      console.log(area_code.value)
    }, { deep: true })



    return {
      checked,
      tel,
      pwd,
      onSubmit,
      area_code,
      qv,
      toDown
    };
  }
}
</script>

<style scoped lang="scss">
@import '@/styles/theme.scss';
.homes{
margin-bottom: 200px;
  //background-image: linear-gradient(180deg,#0a3cff,#0bd3ff);
    :deep(.van-form){
        padding: 40px 0 0;
        .van-cell-group--inset{
            padding: 0 60px;
            background-color: initial;
        }
        .van-ellipsis{
          color: #000;
        }
        .van-cell{
            padding: 30px 10px;
            border-bottom: 1px solid #1a7ae7;
            background-color: initial;
            &.zdy{
              .van-field__left-icon{
                margin-right: 30px;
              }
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
                margin-top: 35px;
                &+.van-button{
                  margin-top: 20px;
                }
            }
        }
    }
    :deep(.van-nav-bar){
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
    }
}
</style>
