<template>
    <div class="self home">
        <div class="top">
            <div class="info">
                <van-nav-bar @click-right="clickRight">
                    <template #right>
                        <!-- <van-icon name="comment-o" size="18"/> -->
                        <img :src="require('@/assets/images/news/msg2.png')" width="26.5" alt="">
                    </template>
                </van-nav-bar>
                <van-uploader :after-read="afterRead" ref="upload" v-show="false"/>
                <div class="avaitar">
                    <van-image :src="require('@/assets/images/news/user.png')"  class="img" fit="contain"/>
                    <div class="right">
                        <div class="title">
                            {{userinfo.tel}}
                            <!-- <img v-if="info.level" :src="require('@/assets/images/self/vip'+ info.level +'.png')" class="vip" alt=""> -->
                        </div>
                        <!-- <div class="b" @click="toShare">
                            {{$t('msg.tgm')}}： {{info.invite_code}}
                        </div> -->
                    </div>
                </div>
                <div class="money">
                    <div class="li w100">
                        <!-- <div class="t">{{currency}} {{userinfo.balance*1 + userinfo.freeze_balance*1}} ---</div> -->
                        <div class="t">{{currency}} {{userinfo.balance_all_format}}</div>
                        <div class="b">{{$t('msg.my_yu_e')}}</div>
                        <van-button icon="plus" class="plus" type="primary" to="chongzhi"/>
                    </div>
                    <div class="li t1">
                        <div class="t" style="color: #00a300">{{currency}} {{userinfo.balance_format}}</div>
                        <div class="b">{{$t('msg.kyye')}}</div>
                    </div>
                    <div class="li">
                        <div class="t" style="color: red">{{currency}} {{userinfo.freeze_balance_format}}</div>
                        <div class="b">{{$t('msg.djje')}}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="list">
            <van-cell is-link v-for="(item,index) in list" :key="index" @click="toRoute(item,index)">
                <template #title>
                    <img :src="item.img" :class="index == 0 ? 'img img1' : 'img'" :width="index == 0 ? '30' : '24'" :height="index == 0 ? '30' : '24'" alt="">
                    {{item.label}}
                </template>
            </van-cell>
        </div>
    </div>
</template>
<script>
import { ref,getCurrentInstance, onMounted} from 'vue';
import {getself} from '@/api/self/index'
import {uploadImg,headpicUpdatae} from '@/api/home/index.js'
import {logout} from '@/api/login/index'
import store from '@/store/index'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router';
import {bind_bank} from '@/api/self/index.js'
import { Dialog } from 'vant'
export default {
    setup(){
        const { push } = useRouter();
        const {proxy} = getCurrentInstance()
        const { t } = useI18n()
        const upload = ref(null)
        const currency = ref(store.state.baseInfo?.currency)
        const userinfo = ref(store.state.userinfo)
        const is_bind = ref(false)
        store.dispatch('changefooCheck','self')
        const list = ref([
            {label: t('msg.tikuan'), img: require('@/assets/images/self/00.png'),path:'/drawing', params: 'balance'},
            {label: t('msg.txjl'), img: require('@/assets/images/self/02.png'),path:'/deposit'},
            {label: t('msg.zbjl'), img: require('@/assets/images/self/03.png'),path:'/account_details'},
            {label: t('msg.tkxx'), img: require('@/assets/images/self/04.png'),path:'/bingbank'},
            {label: t('msg.xxgg'), img: require('@/assets/images/self/05.png'),path:'/message'},
            // {label: t('msg.tdbg'), img: require('@/assets/images/self/6.png'),path:'/team'},
            // {label: t('msg.czjl'), img: require('@/assets/images/self/2.png'),path:'/recharge'},
            {label: t('msg.pwd'), img: require('@/assets/images/self/06.png'),path:'/editPwd'},
            // {label: t('msg.shaddress'), img: require('@/assets/images/self/7.png'),path:'/address'},
            {label: t('msg.out'), img: require('@/assets/images/self/07.png'),click:() => {
                proxy.$dialog.confirm({
                    title: t('msg.ts'),
                    message: t('msg.next_login'),
                    confirmButtonText: t('msg.yes'),
                    cancelButtonText: t('msg.quxiao'),
                })
                .then(() => {
                    // on confirm
                    logout().then(res => {
                        if(res.code === 0) {
                            if(res.code === 0) {
                                proxy.$Message({ type: 'success', message:res.info});
                                push('/login')
                            } else {
                                proxy.$Message({ type: 'error', message:res.info});
                            }
                        }
                    })
                })
                .catch(() => {
                    // on cancel
                });
            }},
        ])
        
        const getInfo = () => {
            getself().then(res => {
                if(res.code === 0) {
                    userinfo.value = {...res.data?.info}
                }
            })
        }
        getInfo()
        const clickRight = () => {
            push('/message')
        }
		
		bind_bank().then(res => {
		    if(res.code === 0) {
				let infoData = res.data?.info || [];
				if (infoData.length > 0) {
				  is_bind.value = true
				}
		    }
		})

        const toRoute = (row,index) => {
			if (index == 0 && !is_bind.value) {
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
            if (row.path){
                push(row.path + (row.params? '?param='+userinfo.value[row.params] : ''))
            } else if (row.click) {
                row.click(row)
            }
        }
        const setAvatar = () => {
            console.log(upload.value)
            upload.value?.chooseFile()
        }
        const afterRead = (file) => {
            const formData = new FormData();
            formData.append('file', file.file);
            uploadImg(formData).then(res => {
                if(res.uploaded) {
                    headpicUpdatae({url:res.url}).then(res => {
                        getInfo()
                    })
                }
            })
        }
        const toShare = () => {
            push('/share')
        }
        return {currency,list,setAvatar,toShare,toRoute,afterRead,upload,clickRight,userinfo}
    }
}
</script>
<style lang="scss" scoped>
.self{
    overflow: auto;
    display: block !important;
    padding: calc(var(--van-nav-bar-height) + 20px) 24px 0;
    .van-nav-bar{
        //background:#d4dff5;
        left: 0;
    }
    .top{
        // padding: 135px 50px;
        color: #fff;
        .info{
            .avaitar{
                display: flex;
                height: 200px;
                background-color: $theme;
                border-radius: 12px;
                padding: 46px;
                // margin-bottom: 70px;
                .img{
                    height: 107px;
                    width: 107px;
                    border-radius: 50%;
                    margin-right: 35px;
                    overflow: hidden;
                    padding: 15px;
                    background-color: #fff;
                    img{
                        height: 100%;
                        width: auto;
                    }
                }
                .right{
                    flex: 1;
                    height: 100%;
                    display: flex;
                    justify-content: space-around;
                    flex-direction: column;
                    text-align: left;
                    .title{
                        font-size: 46px;
                        .vip{
                            width: 25px;
                        }
                    }
                }
            }
            .money{
                display: flex;
                border-radius: 12px;
                background-color: #ffffff;
                color: #333;
                flex-wrap: wrap;
                .li{
                    text-align: center;
                    flex: 1;
                    padding: 50px 24px;
                    &.w100{
                        width: 100%;
                        flex: auto;
                        padding: 24px;
                        // display: flex;
                        // justify-content: space-between;
                        position: relative;
                        text-align: left;
                        border-bottom: 1px solid #dee2e6;
                        .t{
                            font-size: 50px;
                            margin-bottom: 5px;
                            color: #000;
                        }
                        .plus{
                            position: absolute;
                            width: 120px;
                            height: 80px;
                            right: 24px;
                            top: 50%;
                            transform: translate(0,-50%);
                            border-radius: 40px;
                            font-weight: 600;
                        }
                    }
                    &.t1{
                        border-right: 1px dashed rgba(0,0,0,.1);
                    }
                    .t{
                        font-size: 24px;
                        // font-weight: 600;
                        margin-bottom: 5px;
                    }
                    .b{
                        font-size: 18px;
                    }
                }
            }
        }
    }
    .list{
        border-radius: 30px;
        position: relative;
        background-color: #fff;
        text-align: left;
        overflow: hidden;
        margin-top: 48px;
        .van-cell{
            padding: 22px 45px;
            font-size: 34px;
            color: #000;
            .van-cell__title{
                .img{
                    margin-right: 10px;
                    vertical-align: middle;
                    &.img1{
                        margin-left: -6PX;
                    }
                }
            }
            ::v-deep(.van-icon){
                color: $theme;
            }
        }
    }
}
</style>