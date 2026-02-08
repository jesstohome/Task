<template>
  <div class="slot-uploader" :style="uStyle" @click="chooseFile">
    <input ref="fileRef" type="file" accept="image/*" @change="onFileChange" style="display:none" />
    <img :src="displaySrc" class="slot-img" :style="imgStyle" />
    <div v-if="uploading" class="overlay">
      <van-loading color="#0094ff" />
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { uploadImg } from '@/api/home/index'
import { Toast } from 'vant';

export default {
  name: 'SlotUploader',
  props: {
    modelValue: { type: String, default: '' },
    width: { type: [Number,String], default: 300 },
    height: { type: [Number,String], default: 200 },
    defaultImage: { type: String, default: require('@/assets/images/self/img_up.png') }
  },
  emits: ['update:modelValue'],
  setup(props, { emit }){
    const fileRef = ref(null)
    const uploading = ref(false)

    const displaySrc = computed(() => {
      return props.modelValue || props.defaultImage
    })

    const chooseFile = () => {
      fileRef.value && fileRef.value.click()
    }

    const onFileChange = (e) => {
      const f = e.target.files && e.target.files[0]
      if (!f) return
      const fd = new FormData()
      fd.append('file', f)
      //uploading.value = true
      const toast = Toast.loading({ message: 'Loading...', forbidClick: true, duration: 0 });
      uploadImg(fd).then(res => {
        if (res && res.uploaded) {
          emit('update:modelValue', res.url)
        }
      }).finally(() => {
        //uploading.value = false
        toast.close();
        // reset input value to allow re-upload same file
        e.target.value = ''
      })
    }

    const uStyle = computed(() => ({ width: typeof props.width === 'number' ? props.width + 'px' : props.width, height: typeof props.height === 'number' ? props.height + 'px' : props.height }))
    const imgStyle = computed(() => ({ width: '300px', height: '200px' }))

    return { fileRef, chooseFile, onFileChange, displaySrc, uStyle, imgStyle, uploading }
  }
}
</script>

<style scoped>
.slot-uploader{
  border: 3px dashed #2b7cff;
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  background:#fff;
  margin: 0 auto;
}
.slot-img{ display:block;}
</style>
