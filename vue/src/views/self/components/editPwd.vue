<template>
  <div class="home">
    <van-nav-bar :title="$t('msg.pwd')" left-arrow @click-left="$router.go(-1)" @click-right="clickRight">
        <template #right>
            <img :src="require('@/assets/images/self/hank/tel.png')" class="img" alt="">
        </template>
    </van-nav-bar>
    
    <van-form @submit="onSubmit">
      <van-cell-group inset>
        <van-field name="type" class="radio">
            <template #input>
                <van-radio-group v-model="checked" direction="horizontal">
                    <van-radio name="1">
                        <template #icon="props">
                            <span class="line" :class="props.checked && 'check'"></span>
                        </template>
                        {{$t('msg.login_pwd')}}
                    </van-radio>
                    <van-radio name="2">
                        <template #icon="props">
                            <span class="line" :class="props.checked && 'check'"></span>
                        </template>
                        {{$t('msg.tx_pwd')}}
                    </van-radio>
                </van-radio-group>
            </template>
        </van-field>
        
        <van-field
          class="zdy"
          v-model="old_pwd"
          type="password"
          name="old_pwd"
          :label="$t('msg.old_pwd')"
          :placeholder="$t('msg.old_pwd')"
          :rules="[{ required: true, message: $t('msg.input_old_pwd') }]"
        />
        <van-field
          class="zdy"
          v-model="new_pwd"
          type="password"
          :label="$t('msg.new_pwd')"
          name="new_pwd"
          :placeholder="$t('msg.new_pwd')"
          :rules="[{ required: true, message: $t('msg.input_new_pwd') }]"
        />

        <van-field
          class="zdy"
          name="tel"
          v-model="tel"
          :label="$t('msg.true_pwd')"
          type="password"
          :placeholder="$t('msg.true_pwd')"
          :rules="[{ required: true,validator, message: $t('msg.input_true_pwd') }]"
        />
        
      </van-cell-group>
      <!-- <div class="text_b">
          <p class="tex">{{$t('msg.qljmm')}}</p>
      </div> -->
      <div class="buttons">
        <van-button round block type="primary" native-type="submit">
          {{$t('msg.yes')}}
        </van-button>
      </div>
    </van-form>
  </div>
</template>

<script>
import { ref,getCurrentInstance } from 'vue';

import {set_pwd} from '@/api/self/index.js'
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n'
export default {
  name: 'HomeView',
  setup() {
    const { t } = useI18n()
    const { push } = useRouter();
    const {proxy} = getCurrentInstance()
    const checked = ref('1')
    const showHank = ref(false)
    const showKeyboard = ref(false)
    const bank_name = ref('')
    const bank_code = ref('')
    const new_pwd = ref('')
    const old_pwd = ref('')
    const type = ref('')
    const tel = ref('')
    const paypassword = ref('')
    const bank_list = ref([])
    const info = ref({})
    const form_ = ref({})
    
    const clickLeft = () => {
        push('/self')
    }
    const clickRight = () => {
        push('/service')
    }
    const validator = (val) => {
        if(!val) return false;
        if(val != new_pwd.value) return t('msg.true_new_pwd')
        return true
    }
    
    
    const onConfirm = (value) => {
      bank_name.value = value.text;
      bank_code.value = value.value
      showHank.value = false;
    };

    const onSubmit = (values) => {
        const json = {...values}
        delete json.tel
        set_pwd(json).then(res => {
            if(res.code === 0) {
                proxy.$Message({ type: 'success', message:res.info});
                push('/self')
            } else {
                proxy.$Message({ type: 'error', message:res.info});
            }
        })
    };



    return {
        onConfirm,
        bank_name,
        showHank,
        paypassword,
        tel,
        type,
        old_pwd,
        new_pwd,
        bank_code,
        onSubmit,
        clickLeft,
        clickRight,
        bank_list,
        showKeyboard,
        info,
        checked,
        validator
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
    :deep(.van-form){
        padding: 0;
        padding: 24px;
        .van-radio{
            position: relative;
            // margin-left: 25px;
            padding: 10px 20px;
            width: 50%;
            overflow: hidden;
            .line.check{
                position: absolute;
                width: 100%;
                height: 3px;
                background-color: $theme;
                left:0;
                bottom: 0;
            }
            .van-radio__label{
                margin-left: 0;
                width: 100%;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                word-break: keep-all;
                font-size: 30px;
                line-height: 50px;
            }
            .van-radio__icon--checked+.van-radio__label{
                // font-size: 26px;
                // font-weight: 600;
                color: $theme;
            }
        }

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
            padding: 0 60px;
            border-radius: 3px;
        }
        .van-cell{
            &.radio{
                padding: 45px 10px;
            }
            padding: 23px 10px;
            border-bottom: 1px solid  var(--van-cell-border-color);
            font-size: 30px;
            .van-field__left-icon{
                width:120px;
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
            .van-field__error-message{
                font-size: 20px;
            }
            .van-field__control{
                font-size: 24px;
                justify-content: center;
            }
            &::after {
                display: none;
            }
        }
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
            padding: 0 76px;
            margin-top: 24px;
            .van-button{
                font-size: 36px;
                padding: 20px 0;
                height: auto;
            }
            .van-button--plain{
                margin-top: 40px;
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
