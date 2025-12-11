<template>
    <div class="footer">
        <!-- <img :src="require('@/assets/images/lang'+(color == 'white' ? 1 : '')+'.png')" class="lang" height="27" width="27" alt="" @click="showLang()"> -->
         <div class="lang_select" @click="showLang()">
            <img :src="require('@/assets/images/lang.png')" class="lang" alt="">
            <div style="margin-left: 10px;">{{ langcheck }}</div>
         
         </div>
         
        <!-- 使用 vant 的右侧 Popup 代替居中 Dialog，宽度为页面一半，高度为页面高度，样式为黑底白字 -->
        <van-popup v-model:show="show" position="right" :style="{ width: '50vw', height: '100vh' }" close-on-click-overlay>
            <div class="lang_box popup">
                <div class="close-btn" @click="show = false">
                    <van-icon name="cross" />
                </div>
                <div class="content">
                    <div class="langs">
                        <span class="li" :class="langcheck==item.link && 'check'" v-for="(item,index) in langs" :key="index"  @click="handSeletlanguages(item)">
                            <span class="text">{{item.name}}</span>
                        </span>
                    </div>
                </div>
            </div>
        </van-popup>
    </div>
</template>
<script>
import { ref,reactive,getCurrentInstance } from 'vue';
import store from '@/store/index'
import { useRouter } from 'vue-router';
import {vantLocales} from '@/i18n/i18n';
import { useI18n } from 'vue-i18n'
export default {
    props: {
        color: String
    },
    setup(){
        // 语言切换
        const {proxy} = getCurrentInstance()
        const { locale,t } = useI18n()
        const langcheck = ref(store.state.lang)
        const langImg = ref('')
        console.log(langcheck.value)
        langImg.value = store.state.langImg
        const langs = ref(store.state.baseInfo?.languageList)
        const show = ref(false);

        const showLang = () => {
            langcheck.value =store.state.lang
            show.value = true
            console.log(langcheck.value)
        }
        const handSeletlanguages = (row) => {
            // langcheck.value = 'bn_bd'
            langcheck.value = row.link
            langImg.value = row.image_url
            submitLang()
        }
        const submitLang = () => {
            locale.value = langcheck.value
            store.dispatch('changelang',langcheck.value)
            // locale.value = 'zh_cn'
            // store.dispatch('changelang','zh_cn')
            store.dispatch('changelangImg',langImg.value)
            vantLocales(locale.value)
            show.value = false
            proxy.$Message({ type: 'success', message: t('msg.switch_lang_success') });
        }
        return {show,submitLang,handSeletlanguages,langs, showLang, langcheck}
    }
}
</script>
<style lang="scss" scoped>
    .lang_select{
        color: white;
        font-size: 28px;
        font-weight: bold;
        background-color: #1a7ae7;
        padding: 10px 20px;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        .lang{
            width: 28px;
            height: 28px;
        }
        }
    .lang_box{
        width: 100%;
        position: relative;
        padding-top: 60px;
        .lang_title {
            margin-bottom: 40px;
        }
        .lang_bg{
        width: 100%;
        position: absolute;
        top: 0;
        left: 0;
        }
        .content{
        position: relative;
        z-index: 1;
        text-align: center;
        margin-top: 80px;
        .qiu{
            width: 175px;
            border-radius: 50%;
            box-shadow: $shadow;
            margin-bottom: 6px;
        }
        .langs{
            margin-bottom: 15px;
            max-height: 70vh;
            overflow: auto;
            border: 1px solid #ccc;
            margin: 24px;
            border-radius: 24px;
            .li{
                padding: 24px;
                display: block;
                text-align: left;
                max-height: 500px;
                overflow: auto;
                border-bottom: 1px solid #ccc;
                &:last-child{
                    border-bottom: none;
                }
                &.ctn{
                    padding: 24px;
                }
                &.check{
                    background-color: #ccc;
                }
                .img{
                    margin-right: 34px;
                    vertical-align: middle;
                }
                .text{
                    font-size: 26px;
                    color:$textColor;
                }
            }
        }
        .btn{
            padding: 50px 54px 50px;
        }
        }

        /* Popup 专用样式：右侧弹出层 */
        &.popup{
            height: 100vh;
            background: #000; /* 弹出层背景黑色 */
            color: #fff; /* 文字白色 */
            padding: 20px 16px;
            box-sizing: border-box;
            position: relative;
            
            .close-btn {
                position: absolute;
                top: 26px;
                right: 26px;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                z-index: 2;
                
                :deep(.van-icon) {
                    font-size: 44px;
                    color: #fff;
                }
                
                &:hover {
                    opacity: 0.8;
                }
            }
            
            .content{
                text-align: left;
                padding-top: 40px;
                .langs{
                    margin: 0;
                    border: none;
                    max-height: calc(100vh - 80px);
                    overflow: auto;
                    .li{
                        padding: 24px 20px;
                        font-size: 24px; /* 文字进一步加大 */
                        font-weight: 800; /* 文字进一步加粗 */
                        color: #fff !important; /* 确保纯白色 */
                        border-bottom: 1px solid rgba(255,255,255,0.06);
                        transition: background-color 0.2s;
                        
                        &:hover {
                            background-color: rgba(255,255,255,0.1);
                        }
                        
                        &.check{
                            background-color: rgba(255,255,255,0.15);
                        }
                        
                        .text {
                            color: #fff !important; /* 确保纯白色 */
                        }
                    }
                }
            }
        }
    }
</style>