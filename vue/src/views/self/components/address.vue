<template>
  <div class="home">
    <van-nav-bar :title="$t('msg.shaddress')" left-arrow @click-left="$router.go(-1)" @click-right="clickRight"></van-nav-bar>
    
    <van-form @submit="onSubmit">
          <div class="address">
              <div class="title">{{$t('msg.shsfxx')}}</div>
            <van-cell-group inset>
                <van-field
                class="zdy"
                v-model="name"
                name="name"
                :left-icon="require('@/assets/images/self/hank/2.png')"
                :placeholder="$t('msg.zsxm')"
                :rules="[{ required: true, message: $t('msg.input_zsxm') }]"
                />
                <van-field
                class="zdy"
                name="tel"
                v-model="tel"
                :left-icon="require('@/assets/images/self/hank/3.png')"
                :placeholder="$t('msg.tel_phone')"
                :rules="[{ required: true, message: $t('msg.input_tel_phone') }]"
                />
            </van-cell-group>
          </div>
          <div class="address">
              <div class="title">{{$t('msg.shdzxx')}}</div>
            <van-cell-group inset>
                <van-field
                class="zdy"
                v-model="address"
                name="address"
                :left-icon="require('@/assets/images/self/address/dq1.png')"
                :placeholder="$t('msg.input_ress')"
                :rules="[{ required: true, message: $t('msg.input_ress') }]"
                />
                <van-field
                class="zdy"
                name="area"
                v-model="area"
                :left-icon="require('@/assets/images/self/address/dq2.png')"
                :placeholder="$t('msg.input_details_ress')"
                :rules="[{ required: true, message: $t('msg.input_details_ress') }]"
                />
            </van-cell-group>
          </div>
          <div class="address not_top">
              <van-field name="default">
                    <template #input>
                        <div class="checkbox">
                            <div class="span">{{$t('msg.set_cy_ress')}}</div>
                            <van-checkbox v-model="checked" shape="square" />
                        </div>
                    </template>
                </van-field>
          </div>
        <div class="text_b"></div>
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
// import store from '@/store/index'
import {get_address,edit_address} from '@/api/self/index.js'
import { useRouter } from 'vue-router';
// import { useI18n } from 'vue-i18n'
export default {
  name: 'address',
  setup() {
    // const { t } = useI18n()
    const { push } = useRouter();
    const {proxy} = getCurrentInstance()
    const name = ref('')
    const tel = ref('')
    const address = ref('')
    const area = ref('')
    const checked = ref(true)
    
    const info = ref({})
    // const customFieldName = ref({})
    get_address().then(res => {
        if(res.code === 0) {
            info.value = {...(res.data[0] || {})}
            name.value = info.value?.name
            tel.value = info.value?.tel
            address.value = info.value?.address
            area.value = info.value?.area
        }
    })

    const clickLeft = () => {
        push('/self')
    }
    const clickRight = () => {
        push('/self')
    }
    const onSubmit = async (values) => {
        let json = {...values}
        delete json.default
        await edit_address(json).then(res => {
            if(res.code === 0) {
                proxy.$Message({ type: 'success', message:res.info});
                push('/self')
            } else {
                proxy.$Message({ type: 'error', message:res.info});
            }
        }).catch(rr => {
            console.log(rr)
        })
    };



    return {
        tel,
        name,
        checked,
        area,
        address,
        onSubmit,
        info,
        clickLeft,
        clickRight
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
        padding: 40px 0 0;

        .address{
            box-shadow: $shadow;
            border-radius: 12px;
            padding: 30px 30px 0;
            margin: 0 30px 40px;
            background-image: url('~@/assets/images/self/address/bg.png');
            background-size: 100% 100%;
            text-align: left;
            &.not_top{
                padding-top: 0;
            }
            .title{
                font-size: 30px;
                font-weight: 600;
                margin: 15px 0 30px;
            }
            .checkbox{
                width: 100%;
                display: flex;
                justify-content: space-between;
            }
        }
        .van-cell{
            padding: 30px;
            border-bottom: 1px solid  var(--van-cell-border-color);
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
            .van-checkbox__icon{
                font-size: 40px;
                &.van-checkbox__icon--checked .van-icon{
                    background-color:$theme;
                    border-color:$theme;
                }
            }
            .van-icon{
                border-radius: 50%;
            }
            .van-checkbox__label{
                font-size: 24px;
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

    :deep(.van-dialog){
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
