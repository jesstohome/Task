<template>
  <div class="home">
    <van-nav-bar :title="$t('msg.chongzhi')" left-arrow @click-left="$router.go(-1)"></van-nav-bar>
    <div class="van-form">
        <div class="item">
            <img :src="info?.ewm" alt="" class="img">
        </div>
        <div class="item code">
            <span class="span">{{info?.usercode}}</span>
            <span class="r" @click="copy(info?.usercode)">{{$t('msg.copy')}}</span>
        </div>
        <!-- 上传图片 -->
        <div class="upload_">
            <van-uploader v-model="fileList" multiple :max-count="1" :after-read="afterRead"/>
            {{$t('msg.scfkjt')}}
        </div>
        <div class="buttons">
            <van-button round block type="primary" @click="onSubmit">
            {{$t('msg.yjfk')}}
            </van-button>
        </div>
      </div>
  </div>
</template>

<script>
import { reactive, ref,getCurrentInstance } from 'vue';
import store from '@/store/index'
import {uploadImg,bank_recharge} from '@/api/home/index.js'
import { useRouter,useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n'
// 复制函数
import useClipboard from 'vue-clipboard3';
export default {
  name: 'HomeView',
  setup() {
    const { toClipboard } = useClipboard();
    const { t } = useI18n()
    const { push,back } = useRouter();
    const route = useRoute();
    const {proxy} = getCurrentInstance()
    const info = ref(route.query)
    const currency = ref(store.state.baseInfo?.currency)
    const pay = ref([])
    const checked = ref('')
    const url = ref('')
    const fileList = ref([]);
    
    const clickLeft = () => {
        push('/self')
    }
    const clickRight = () => {
        push('/tel')
    }
    const copy = (value) => {
        try {
            toClipboard(value);
            proxy.$Message({ type: 'success', message:t('msg.copy_s')});
        } catch (e) {
            proxy.$Message({ type: 'error', message:t('msg.copy_b')});
        }
    }
    const afterRead = (file) => {
        file.status = 'uploading'
        file.message = t('msg.scz')
        const formData = new FormData();
        formData.append('file', file.file);
        uploadImg(formData).then(res => {
            url.value = res.url || ''
            file.status = 'success'
        })
    }
    // next_cz checked

    const onSubmit = () => {
        if(!url.value) {
            proxy.$Message({ type: 'error', message:t('msg.qscfkjt')});
        } else {
            let json = {
                num: info.value?.num,
                url: url.value,
                vip_id: route.query?.vip_id,
                payId: route.query?.id
            }
            bank_recharge(json).then(res => {
                if(res.code === 0) {
                    proxy.$Message({ type: 'success', message:res.info});
                    back()
                } else {
                    proxy.$Message({ type: 'error', message:res.info});
                }
            })
        }

    };



    return {
        afterRead,
        copy,
        checked,
        pay,
        onSubmit,
        clickLeft,
        clickRight,
        info,
        currency,
        fileList
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
            .hy_box{
                height: 230px;
                width: 100%;
                padding: 25px;
                color: #fff;
                background-image: url('~@/assets/images/home/hybj.png');
                background-size: 100% 100%;
                border-radius: 10px;
                overflow: hidden;
                position: relative;
                .t{
                    margin-bottom: 18px;
                    text-align: left;
                    .img{
                        width: 65px;
                        height: auto;
                        margin-right: 20px;
                        vertical-align: middle;
                    }
                    .text{
                        font-size: 27px;
                    }
                }
                .b{
                    padding-left: 85px;
                    font-size: 18px;
                    text-align: right;
                    .sub{
                        .line{
                            margin: 0 22px;
                        }
                    }
                }
            }
        .pay{
            margin-top: 80px;
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
                    padding: 32px 0;
                }
                .van-cell__title{
                    .img{
                        width: 52px;
                        margin-right: 30px;
                        vertical-align: middle;
                    }
                }
            }
        }
        .item{
            width: 100%;
            margin-top: 30px;
            display: flex;
            padding: 30px;
            box-shadow: $shadow;
            justify-content: space-around;
            .r{
                color: $theme;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
            .span{
                display: inline-block;
                word-break: break-all;
                flex: 1;
            }
            .img{
                width: 60%;
            }
        }
        .upload_{
            padding: 60px 0;
            margin: 40px 0;
            width: 100%;
            text-align: center;
            background-color: #efefef;
            display: flex;
            flex-direction: column;
        }
        .van-uploader{
            .van-uploader__upload{
                width: 200px;
                height: 200px;
                
            }
            .van-uploader__wrapper{
                justify-content: center;
            }
        }
    }
}
</style>
