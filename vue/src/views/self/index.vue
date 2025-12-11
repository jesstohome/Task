<template>
    <div class="self home">
        <div class="top">
            <div class="info">
                <!-- header card: avatar + credit score (replaces van-nav-bar) -->
                <div class="header-card">
                    <div class="hc-left">
                        <van-image :src="require('@/assets/images/news/users.png')" class="hc-avatar" fit="cover" />
                        <div class="hc-meta">
                            <div class="hc-name">{{ userinfo.tel }}
                                <img v-if="level" :src="require('@/assets/images/self/vip'+ level +'.png')" class="vip" alt="">
                            </div>
                            
                            <div class="hc-score-label">
                                <div>{{$t('msg.xyf') }}:</div>                              
                                <van-progress class="jindutiao" :percentage="Number(creditPercent)" pivot-text="" stroke-width="6" color="#fdb824" inactive-color="#ffeedd" />
                                <div>
                                    {{ creditPercent }}%
                                </div>                          
                            </div>
                            <div class="hc-invite">{{$t('msg.code') }}：
                                <span class="code">{{ userinfo.invite_code }}</span>
                                <van-button size="mini" class="hc-copy" @click="copyInvite($t('msg.copy_s'))">{{$t('msg.copy')}}</van-button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- <van-uploader :after-read="afterRead" ref="upload" v-show="false"/> -->
                <!-- <div class="avaitar">
                    <van-image :src="require('@/assets/images/news/user.png')"  class="img" fit="contain"/>
                    <div class="right">
                        <div class="title">
                            {{userinfo.tel}}
                            <img v-if="info.level" :src="require('@/assets/images/self/vip'+ info.level +'.png')" class="vip" alt="">
                        </div>
                        <div class="b" @click="toShare">
                            {{$t('msg.tgm')}}： {{info.invite_code}}
                        </div>
                    </div>
                </div> -->
                <!-- Earnings block copied from index/home.vue -->
                <div class="earnings mt-2">
                    <div class="earnings_Info">
                        <div class="vip_level ft-16" v-if="userinfo?.tel">
                            <div style="flex: 2 1 0px;">{{userinfo?.tel}}</div>
                        </div>
                        <div class="balance mt-2 d-flex justify-between">
                            <span >{{$t('msg.zhye')}}</span><span >{{$t('msg.djje')}}</span>
                        </div>
                        <div class="balance-val d-flex justify-between">
                            <span >
                                <span class="mm">{{monney || 0}}{{currency}}</span>
                            </span>
                            <span >
                                <span class="mm">{{mInfo.freeze_balance || 0}}{{currency}}</span></span>
                        </div>
                        <div class="count-switch">
                            <van-button class="switch-btn" :class="{active: activeTab===1}" @click="activeTab=1">{{$t('msg.get_m')}}</van-button>
                            <van-button class="switch-btn" :class="{active: activeTab===2}" @click="activeTab=2">{{$t('msg.zsy')}}</van-button>
                        </div>

                        <div class="count-data">
                            <template v-if="activeTab === 1">
                                <div class="flex-full">
                                    <div>{{$t('msg.today_monney')}}</div>
                                    <div class="two">{{mInfo.yon1 || 0}}</div>
                                </div>
                                <div class="flex-full">
                                    <div>{{$t('msg.zrsy')}}</div>
                                    <div class="two">{{mInfo.Yesterdaysearnings || 0}}</div>
                                </div>
                            </template>
                            <template v-else>
                                <div class="flex-full">
                                <div>{{$t('msg.order')}}{{$t('msg.get_m')}}</div>
                                <div class="two">{{mInfo.yon2}}</div>
                            </div>
                            <div class="flex-full">
                                <div>{{$t('msg.tdsy')}}</div>
                                <div class="two">{{mInfo.Teambenefits}}</div>
                            </div>
                            <div class="flex-full">
                                <div>{{$t('msg.zsy')}}</div>
                                <div class="two">{{mInfo.yon3}}</div>
                            </div>
                            </template>
                        </div>
                    </div>
                </div>
                <!-- <div class="money">
                    <div class="li w100">
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
                </div> -->
            </div>
        </div>
        <div class="caiwu">
            <div>
                {{ $t('msg.wdcw') }}
            </div>
            <div class="caiwulist">
                <div @click="toRoute(list[1],1)" class="caiwuitem">
                    <div>
                        <van-image :src="require('@/assets/images/self/chongzhi.png')" class="caiwuimg" fit="cover" />
                    </div>
                    <div>
                        {{ $t('msg.chongzhi') }}
                    </div>
                </div>
                <div @click="toRoute(list[0],0)" class="caiwuitem">
                    <div>
                        <van-image :src="require('@/assets/images/self/tixian.png')" class="caiwuimg" fit="cover" />
                    </div>
                    <div>
                        {{ $t('msg.tixian') }}
                    </div>
                </div>
                <div @click="toRoute(list[3],3)" class="caiwuitem">
                    <div>
                        <van-image :src="require('@/assets/images/self/zhangbian.png')" class="caiwuimg" fit="cover" />
                    </div>
                    <div>
                        {{ $t('msg.zbjl') }}
                    </div>
                </div>
            </div>
        </div>
        <div class="tikuan">
            <div>
                {{ $t('msg.tkxx') }}
            </div>
            <div class="tikuanlist">
                <div @click="toRoute(list[4],4)">
                    <div>
                        <van-image :src="require('@/assets/images/self/tikuanxinxi.png')" class="tikuanimg" fit="cover" />
                    </div>
                    <div>
                        {{ $t('msg.tkxx') }}
                    </div>
                </div>
                <div @click="toRoute(list[2],2)">
                    <div>
                        <van-image :src="require('@/assets/images/self/tixianjilu.png')" class="tikuanimg" fit="cover" />
                    </div>
                    <div>
                        {{ $t('msg.txjl') }}
                    </div>
                </div>
            </div>
        </div>
        <div class="tikuan">
            <div>
                {{ $t('msg.qita') }}
            </div>
        </div>
        <div class="list">
            <van-cell is-link @click="toRoute(qitalist[0],0)">
                <template #title>
                    <img :src="qitalist[0].img" class="img" width="24" height="24" alt="">
                    {{ $t('msg.tel') }}
                </template>
            </van-cell>
            <van-cell is-link @click="toRoute(qitalist[1],1)">
                <template #title>
                    <img :src="qitalist[1].img" class="img img1" width="30" height="30" alt="">
                    {{ $t('msg.pwd') }}
                </template>
            </van-cell>
            <van-cell is-link @click="toRoute(qitalist[2],2)">
                <template #title>
                    <img :src="qitalist[2].img" class="img" width="24" height="24" alt="">
                    {{ $t('msg.xxgg') }}
                </template>
            </van-cell>
            <div class="yuyan">
                <div class="yuyanimg">
                    <van-image :src="require('@/assets/images/self/yuyan.png')" class="tikuanimg" fit="cover" />
                </div>
                <div class="yuyantext">
                    {{ $t('msg.check_lang') }}
                </div>
                <div class="yuyanlist">
                    <langVue />
                </div>
            </div>
            
        </div>
        <div style="margin: 20px 0;">
            <van-button block round plain hairline type="primary" @click="tuichu">{{ $t('msg.out') }}</van-button>
        </div>
    </div>
</template>
<script>
import { ref,getCurrentInstance, onMounted} from 'vue';
import {getself} from '@/api/self/index'
import {uploadImg,headpicUpdatae,getHomeData} from '@/api/home/index.js'
import {logout} from '@/api/login/index'
import store from '@/store/index'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router';
import {bind_bank} from '@/api/self/index.js'
import { Dialog } from 'vant'
import langVue from '@/components/lang.vue'
export default {
    components: {langVue},
    setup(){
        const { push } = useRouter();
        const {proxy} = getCurrentInstance()
        const { t } = useI18n()
        const upload = ref(null)
        const currency = ref(store.state.baseInfo?.currency)
        const userinfo = ref(store.state.userinfo)
    const monney = ref(store.state.minfo?.balance)
    const mInfo = ref(store.state.minfo)
    const level = ref(store.state.minfo?.level || 0)
    const activeTab = ref(1)
    // credit score and invite code (you can set these values later)
    const creditPercent = ref(store.state.minfo?.credit || 0)
    const inviteCode = ref('TPIAA')
        const is_bind = ref(false)
        store.dispatch('changefooCheck','self')
        const list = ref([
            {label: t('msg.tikuan'), img: require('@/assets/images/self/00.png'),path:'/drawing', params: 'balance'},
            {label: t('msg.chongzhi'), img: require('@/assets/images/self/02.png'),path:'/chongzhi'},
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
        const qitalist = ref([
            {label: t('msg.tel'), img: require('@/assets/images/self/kefu.png'),path:'/service'},
            {label: t('msg.pwd'), img: require('@/assets/images/self/tixian.png'),path:'/editPwd'},
            {label: t('msg.xxgg'), img: require('@/assets/images/self/tongzhi.png'),path:'/message'}
        ])
        const tuichu = () => {
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
        }
        const getInfo = () => {
            getself().then(res => {
                if(res.code === 0) {
                    userinfo.value = {...res.data?.info}
                }
            })
        }
        getInfo()

        // load home earnings data
        onMounted(() => {
            getHomeData().then(res => {
                if(res.code === 0) {
                    monney.value = res.data.balance
                    mInfo.value = {...res.data}
                    creditPercent.value = res.data.credit
                    store.dispatch('changeminfo',res.data || {})
                }
            })
        })
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
			if (index == 0 && !is_bind.value && row.path != '/service') {
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
        const copyInvite = (xinxi) => {
            try{
                if (navigator && navigator.clipboard && userinfo.value?.invite_code) {
                    navigator.clipboard.writeText(userinfo.value.invite_code)
                    proxy.$toast?.success && proxy.$toast.success(xinxi)
                }
            }catch(e){
                // fallback
                const ta = document.createElement('textarea')
                ta.value = userinfo.value?.invite_code || ''
                document.body.appendChild(ta)
                ta.select()
                document.execCommand('copy')
                document.body.removeChild(ta)
            }
        }
        return {currency,level,list,qitalist,tuichu,setAvatar,toShare,toRoute,afterRead,upload,clickRight,userinfo,monney,mInfo,activeTab,creditPercent,inviteCode,copyInvite}
    }
}
</script>
<style lang="scss" scoped>
@import '@/styles/theme.scss';
.self{
    :deep(.van-cell__right-icon){
        font-size: 30px;
    }
    overflow: auto;
    display: block !important;
    padding: 0 30px;
    // margin-bottom: 100px;
    //padding: calc(var(--van-nav-bar-height) + 20px) 24px 0;
    .van-nav-bar{
        background:#d4dff5;
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
            }
            /* header-card (avatar + score) */
            .header-card{
                margin-top: 50px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: #fff;
                padding: 12px 16px;
                border-radius: 12px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.08);
                margin-bottom: 12px;
                height: 200px;
                text-align: left;
                font-weight: 900;
                .hc-left{ display:flex; align-items:center; gap:12px }
                .hc-avatar{ width:126px; height:126px; border-radius:50%;}
                .hc-name{ font-size:36px; color:#222;display:flex;align-items: center; gap:15px;}
                .hc-invite{ font-size:28px; color:#666; margin-top:4px;display: flex;align-items: center; }
                .code{ background:#f5f5f5; padding:2px 6px; border-radius:6px; margin:0 8px; font-weight:700 }
                .hc-copy{ background:#fdb824; color:#fff; padding:0 8px;border-radius: 15px;font-size: 30px; height: 40px;}
                .hc-score-label{ font-size:28px; color:#666;display: flex;flex-direction: row;align-items: center;gap: 20px; }
                .jindutiao{width: 150px;}
                .vip{width: 55px;}
            }

            .earnings{
                background: url('~@/assets/images/news/balance_bg.png') no-repeat;
                background-size: 100% 100%;
                padding: 34px 24px;
                min-height: 120px; /* ensure visible area */
                margin-bottom: 24px;
                margin-top: 50px;
                .vip_level{
                    height: 30px;
                    display: flex;
                    box-sizing: border-box;
                    font-size: 20px;
                    color: #333;
                    &>div{
                        flex: 3;
                        display: flex;
                        justify-content: space-between;
                        line-height: 30px;
                        padding-left: 5px;
                        font-size: 40px;
                        // font-weight: 500;
                        color: #ffffff;
                        font-weight: bold;
                        &:first-child{
                            //border-right: 1px solid #adadad;
                            padding-right: 5px;
                            padding-left: 0;
                        }
                    }
                }
                .balance{
                    margin: 20px 0 1px;
                    font-size: 36px;
                    font-weight: 900;
                    color: #fff;
                    display: flex;
                    justify-content: space-between;
                }
                .balance-val{
                    font-size: 40px;
                    font-family: PingFangSC-Semibold,PingFang SC;
                    font-weight: 900;
                    color: #fdb824;
                    display: flex;
                    justify-content: space-between;
                    span{
                        .mm{
                            display: block;
                        }
                    }
                }
                .count-data{
                    display: flex;
                    margin-top: 20px;
                    gap:20px;
                    .flex-full{
                        color: #ffffff;
                        font-size: 28px; 
                        .two{
                            display: block;
                            font-weight: 900;
                            font-size:34px
                        }
                    }
                    .flex-full:not(:first-child) {
                        border-left: 3px solid white;  /* 只有中间和右侧的加线 */
                        padding-left: 20px;
                    }

                        /* 右边项不加额外内边距（可选） */
                    .flex-full:not(:last-child) {
                        padding-right: 15px;
                    }
                }
                .count-switch{
                    margin-top: 12px;
                    display: flex;
                    align-items: center;
                    justify-content: flex-start; /* 靠左排列 */
                    gap: 16px; /* 间隔 */
                    .van-button{
                        height: 44px;
                        border-color: #fff;
                        background: #1a7ae7;
                        color: #fff;
                        padding: 10px;
                        font-weight: 900;
                        font-size: 30px;
                    }
                    .van-button.active{
                        background: #ffffff;
                        color: #fff;
                        color: #1a7ae7;
                    }
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
    .caiwu{
        align-items: center;
        font-size: 34px;
        font-weight: 600;
        color: #333;
        text-align: left;
        .caiwulist{
            display: flex;
            justify-content: space-between;
            margin: 34px 0px;
            
            .caiwuitem{
                display: flex;
                flex-direction: column;
                align-items: center;
                font-size: 28px;
                color: #000000;
                width: 33%;
                text-align: center;
                .caiwuimg{
                    width: 80px;
                    height: 80px;
                    margin-bottom: 20px;
                }
            }
        }
    }
    .tikuan{
        align-items: center;
        font-size: 34px;
        font-weight: 600;
        color: #333;
        text-align: left;
        .tikuanlist{
            display: flex;
            gap: 40px;
            justify-content: space-between;
            margin: 34px 0;           
            div{
                display: flex;
                flex-direction: row;
                align-items: center;
                font-size: 28px;
                color: #000000;
                background-color: #f2f2f2;
                padding: 5px;
                width: 45%;
                .tikuanimg{
                    width: 100px;
                    height: 100px;
                    margin-bottom: 0;
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
        margin-top: 10px;
        .van-cell{
            padding: 22px 10px;
            font-size: 34px;
            color: #000;
            .van-cell__title{
                .img{
                    margin-right: 10px;
                    vertical-align: middle;
                    width: 40px;
                    height: 40px;
                    &.img1{
                        margin-left: -3PX;
                    }
                }
            }
            ::v-deep(.van-icon){
                color: $theme;
            }
        }
    }
    .yuyan{
        display: flex;
        align-items: center;
        padding: 20px 0;
        border-top: 1px solid #f2f2f2;
        margin: 0 10px;
        .yuyanimg{
            margin-right: 20px;
            width: 50px;
            height: 50px;
        }
        .yuyantext{
            flex: 1;
            font-size: 32px;
            color: #333;
        }
        .yuyanlist{
            width: 240px;
        }
    }

</style>