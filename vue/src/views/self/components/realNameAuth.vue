<template>
  <div class="realname auth-page">
    <van-nav-bar :title="$t('msg.real_name_auth')" left-arrow @click-left="$router.back()" />
    <van-form ref="formRef" class="pa-3">
      <van-field v-model="form.real_name" :label="$t('msg.real_name')" placeholder="" :rules="[{ required: true, message: $t('msg.input_real_name') }]" />
      <van-field v-model="form.id_card_num" :label="$t('msg.id_card_num')" placeholder="" :rules="[{ required: true, message: $t('msg.input_id_card') }]" />


      <div class="uploader-group" v-if="id_status !== 0 && id_status !== 1 && id_status !== 3">
        <div class="uploader-item">
          <div class="uploader-label">{{$t('msg.top_pic')}}</div>
          <slot-uploader v-model="form.top_pic" :width="300" :height="200" />
        </div>
        <div class="uploader-item">
          <div class="uploader-label">{{$t('msg.bot_pic')}}</div>
          <slot-uploader v-model="form.bot_pic" :width="300" :height="200" />
        </div>
        <div class="uploader-item">
          <div class="uploader-label">{{$t('msg.headpic')}}</div>
          <slot-uploader v-model="form.headpic" :width="300" :height="200" />
        </div>
      </div>

      <div style="margin:26px">
        <van-button block round type="primary" :disabled="submitDisabled" @click="onSubmit">{{ $t('msg.submit') }}</van-button>
        <div class="status-info">
          <small>{{ $t('msg.id_status_label') }}: {{ statusText }}</small>
          <div v-if="id_remark"><small>{{ id_remark }}</small></div>
        </div>
      </div>
    </van-form>
  </div>
</template>

<script>
import { ref, onMounted, computed, getCurrentInstance } from 'vue'
import { useRouter } from 'vue-router'
import { get_id_auth, save_id_auth } from '@/api/self/index'
import { uploadImg } from '@/api/home/index'
import { useI18n } from "vue-i18n";
import SlotUploader from '@/components/SlotUploader.vue'
export default {
  components: { SlotUploader },
  setup(){
    const { t, locale } = useI18n();
    const router = useRouter()
    const form = ref({ real_name:'', id_card_num:'', top_pic:'', bot_pic:'', headpic:'' })
    const id_status = ref(0)
    const id_remark = ref('')
    const submitDisabled = ref(true)
    const { proxy } = getCurrentInstance();

    const statusText = computed(() => {
      if(id_status.value === 1) return t('msg.id_status_0')
      if(id_status.value === 2) return t('msg.gaojirenzheng')
      if(id_status.value === 3) return t('msg.id_status_2')
      if(id_status.value === 4) return t('msg.id_status_0')
      if(id_status.value === 5) return t('msg.id_status_1')
      if(id_status.value === 6) return t('msg.id_status_2')
      return t('msg.qingshuruxinxin')
    })

    onMounted(()=>{
      get_id_auth().then(res => {
        if(res.code === 0){
          const d = res.data || {}
          form.value.real_name = d.real_name || ''
          form.value.id_card_num = d.id_card_num || ''
          form.value.top_pic = d.top_pic || ''
          form.value.bot_pic = d.bot_pic || ''
          form.value.headpic = d.headpic || ''
          id_status.value = d.id_status ?? 0
          id_remark.value = d.id_remark || ''
          // disable submit when under review or approved
          submitDisabled.value = (id_status.value === 1 || id_status.value === 4 || id_status.value === 5)
        }
      })
    })

    // SlotUploader handles upload and sets form values via v-model

    const onSubmit = () => {
      if(submitDisabled.value) return
      if(!form.value.real_name || !form.value.id_card_num) {
        proxy.$Message({ type: 'error', message:t('msg.qingshuruxinxin')});
        return
      }
      const params = {
        real_name: form.value.real_name,
        id_card_num: form.value.id_card_num,
        top_pic: form.value.top_pic,
        bot_pic: form.value.bot_pic,
        headpic: form.value.headpic
      }
      save_id_auth(params).then(res => {
        if(res.code === 0){
          submitDisabled.value = true
          id_status.value = 0
          id_remark.value = res.info || ''
          proxy.$Message({ type: 'success', message:t('msg.tjcg')});
          router.push('/self')
        } else {
          alert(res.info || 'error')
        }
      })
    }

    return { form, onSubmit, submitDisabled, id_status, id_remark, statusText }
  }
}
</script>

<style lang="scss" scoped>
.uploader-group{ display:flex; gap:12px; flex-direction:column;padding-top: 60px; }
.status-info{ margin-top:8px; color:#666 }
.uploader-label{ margin-bottom:12px;font-size:28px; }
:deep(.van-uploader__upload){
  width: 550px;
  height: 300px;
}
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
            .van-icon{
                color: #fff;
            }
        }
    }
.pa-3{
    padding: 24px;
    padding-top: 50px;
    .van-field{
        //margin-bottom: 16px;
        margin: 20px;
        margin-top: 40px;
    }
}
</style>
