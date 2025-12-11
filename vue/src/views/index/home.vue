<template>
    <div class="home">
        <van-nav-bar>
            <template #left>
                <img :src="require('@/assets/images/head.png')" class="logo-head" height="30" alt="">
            </template>
            <template #right>
                <!-- <img :src="$store.state.langImg" class="lang" height="18" width="27" alt=""> -->
                <!-- <lang-vue></lang-vue> -->
                 <img @click="$router.push({path: '/message'})" :src="require('@/assets/images/news/msg2.png')" class="xiaoxiimg" alt="">
            </template>
        </van-nav-bar>
        <!-- <div class="top">
        </div> -->
        <!-- banner图 -->
        <!-- <van-swipe class="my-swipe banner" :autoplay="3000" indicator-color="white" v-if="banner.length > 0">
            <van-swipe-item v-for="item in banner" :key="item.id">
                <img :src="item.image" alt="" class="img">
            </van-swipe-item>
        </van-swipe> -->
        <!-- 内容 -->
        <div class="content">
            <!-- 次导航 -->
            <div class="earnings mt-2" style="">
                <div class="earnings_Info">
                    <div class="vip_level ft-16" v-if="userinfo?.tel">
                        <div style="flex: 2 1 0px;">{{userinfo?.tel}}</div>
                        <!-- <div style="flex: 1 1 0px; justify-content: center;">{{userinfo?.invite_code}}</div> -->
                    </div>
                    <div class="balance mt-2 d-flex justify-between">
                        <span >{{$t('msg.zhye')}}</span><span >{{$t('msg.djje')}}</span>
                    </div>
                    <div class="balance-val d-flex justify-between">
                        <span >
                            <span class="mm">{{monney}}{{currency}}</span>
                        </span>
                        <span >
                            <span class="mm">{{mInfo.freeze_balance}}{{currency}}</span></span>
                    </div>
                    <!-- 切换按钮：同一行两个按钮 -->
                    <div class="count-switch">
                        <van-button class="switch-btn" :class="{active: activeTab===1}" @click="activeTab=1">{{$t('msg.get_m')}}</van-button>
                        <van-button class="switch-btn" :class="{active: activeTab===2}" @click="activeTab=2">{{$t('msg.zsy')}}</van-button>
                    </div>

                    <!-- 根据选中按钮显示对应内容 -->
                    <div class="count-data">
                        <template v-if="activeTab === 1">
                            <div class="flex-full">
                                <div>{{$t('msg.today_monney')}}</div>
                                <div class="two">{{mInfo.yon1}}</div>
                            </div>
                            <div class="flex-full">
                                <div>{{$t('msg.zrsy')}}</div>
                                <div class="two">{{mInfo.Yesterdaysearnings}}</div>
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
            <!-- 次导航 -->
            <div class="n_nav">
                <div class="li nav" @click="toRoute('/service')">
                    <div class="imge">
                        <img :src="require('@/assets/images/news/service.png')" alt="">
                    </div>
                    <div class="text">{{$t('msg.tel')}}</div>
                </div>
                <div class="li nav" @click="toRoute('/libao',monney)">
                    <div class="imge">
                        <img :src="require('@/assets/images/news/ic_finance.png')" alt="">
                    </div>
                    <div class="text">Web3</div>
                </div>
                <div class="li nav" @click="toRoute('/chongzhi')">
                    <div class="imge">
                        <img :src="require('@/assets/images/news/ic_recharge.png')" alt="">
                    </div>
                    <div class="text">{{$t('msg.chongzhi')}}</div>
                </div>
                <div class="li nav" @click="toRoute('/drawing',monney,2)">
                    <div class="imge">
                        <img :src="require('@/assets/images/news/ic_withdraw.png')" alt="">
                    </div>
                    <div class="text"
                    >{{$t('msg.tixian')}}</div>
                </div>
                <!-- 公司简介 -->
                 <div class="li nav" @click="toDetails(2,$t('msg.gsjj'))">
                    <div class="imge">
                        <img :src="require('@/assets/images/news/aboult.png')" alt="">
                    </div>
                    <div class="text"
                    >{{$t('msg.gsjj')}}</div>
                </div>
                <div class="li nav" @click="toDetails(3,$t('msg.gzms'))">
                    <div class="imge">
                        <img :src="require('@/assets/images/news/tiaokuan.png')" alt="">
                    </div>
                    <div class="text"
                    >{{$t('msg.gzms')}}</div>
                </div>
                <div class="li nav" @click="toDetails(4,$t('msg.dlhz'))">
                    <div class="imge">
                        <img :src="require('@/assets/images/news/zhengshu.png')" alt="">
                    </div>
                    <div class="text"
                    >{{$t('msg.dlhz')}}</div>
                </div>
                <div class="li nav" @click="toDetails(12,$t('msg.qyzz'))">
                    <div class="imge">
                        <img :src="require('@/assets/images/news/ask.png')" alt="">
                    </div>
                    <div class="text"
                    >{{$t('msg.qyzz')}}</div>
                </div>
            </div>
             <!-- 信息公告 -->
            <div class="ftitle">
                {{$t('msg.sssj')}}
            </div>
            <div class="hzhb">
                <div class="card" v-for="(item, idx) in hzhbItems" :key="idx" :class="idx < 2 ? 'small' : 'large'">
                    <div class="bg" :style="item.bg ? { backgroundImage: 'url(' + item.bg + ')' } : {}"></div>
                    <div class="card-content" :class="{ 'text-white': idx===1 || idx===2, 'text-black': idx===0 || idx===3 }">
                        <div class="card-title">{{ $t(item.title) }}</div>
                        <div class="card-value">{{ item.value }}</div>
                    </div>
                </div>
            </div>
            <!-- banner图 -->
            <!-- 原始 hy_box 代码（已注释，保留以供参考）
            <van-row gutter="20" class="hy_box" v-if="hyList.length > 0">
                <van-col span="12" v-for="item in hyList" :key="item.id" >
                    <div class="box" @click="addLevel(item)">
                        <div class="t" >
                            <img :src="item.pic" class="img" :id="'img'+item.id" alt="" :style="'max-height:'+ setHeight('img'+item.id)">
                            <div class="ts">
                                <span class="text">{{item.name}}</span>
                                <van-button type="primary" class="txlevel">
                                    {{mInfo.level == item.level ? $t('msg.now_level') : mInfo.level < item.level ?  $t('msg.join') : ''}}
                                </van-button>
                            </div>
                        </div>
                        <div class="b">
                            <div class="sub">{{$t('msg.sjje')}}
                                <span class="span">{{currency}}{{item.num}}</span>
                            </div>
                            <div class="sub">{{$t('msg.yonj')}}
                                <span class="span">{{((item.bili || 0)*100).toFixed(1)}}%</span>
                            </div>
                        </div>
                    </div>
                </van-col>
            </van-row>
            -->
            <div class="ftitle flex-between">
                <div>{{$t('msg.viplervel')}}</div>
                <div class="load-more" @click="$router.push('/level')">{{$t('msg.ckgd')}}>></div>
            </div>
            <!-- 新的横向可滑动卡片（左右滑动，在同一行显示） -->
            <div class="vip-swiper" v-if="hyList.length > 0">
                <div class="vip-cards">
                    <div class="vip-card" v-for="item in hyList" :key="item.id" :style="{ backgroundImage: `url(${vipBg})` }">
                        <div class="card-top">
                            <div class="name">{{ item.name }}</div>
                            <img v-if="item.pic" :src="item.pic" class="thumb" alt="">
                        </div>
                        <div class="card-bottom">
                            <div v-if="item.name == 'VIP1'" class="line">● {{ $t('msg.shxyh')}}</div>
                            <div v-else class="line">● {{ $t('msg.zhzjcg')}} {{ item.num }} {{ currency }}</div>
                            <div class="line">● {{ $t('msg.mxrwlr') }} {{((item.bili || 0)*100).toFixed(2)}}%</div>
                            <div class="line">● {{ $t('msg.mtrwcs') }} {{ item.order_num }}</div>
                        </div>
                    </div>
                </div>
            </div>

            
           
        </div>
        <!-- 系统通知弹窗 -->
        <van-dialog v-model:show="showA" width="90%" :showConfirmButton="false">
            <div class="lang_box">
                <div class="lang_title">{{$t('msg.System_notification')}}</div>
                <!-- <img :src="require('@/assets/images/register/lang_bg.png')" class="lang_bg" /> -->
                <div class="content">
                    <!-- <img :src="require('@/assets/images/register/qiu.png')" class="qiu" /> -->
                    <div class="langs">
                        <span class="li ctn" v-html="a_content"></span>
                    </div>
                    <div class="btn">
                    <van-button round block type="primary" @click="showA=false">
                        {{$t('msg.yes')}}
                    </van-button>
                    </div>
                </div>
            </div>
        </van-dialog>
    </div>
</template>
<script>
import { ref, getCurrentInstance, reactive, onMounted, onUnmounted, onActivated } from 'vue';
import store from '@/store/index'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router';
import {getHomeData,get_level_list,getdetailbyid} from '@/api/home/index'
import langVue from '@/components/lang.vue'
import {bind_bank} from '@/api/self/index.js'
import { Dialog } from 'vant'
export default {
  components: {langVue},
    setup(){
        const { push } = useRouter();
        // 语言切换
        const { locale,t } = useI18n()
        const a_content = ref('')
        const showA = ref(false)
        const logo = ref(store.state.baseInfo?.site_icon)
        const userinfo = ref(store.state.userinfo)
        const currency = ref(store.state.baseInfo?.currency)
        const xtTime = ref(store.state.xtTime)
        const app_name = ref(store.state.baseInfo?.app_name)
		const is_bind = ref(false)
        if (!xtTime.value && userinfo.value?.tel) {
            getdetailbyid(1).then(res => {
                a_content.value = res.data?.content
                showA.value = true
                store.dispatch('changextTime','true')
            })

        }
		
		bind_bank().then(res => {
		    if(res.code === 0) {
				let infoData = res.data?.info || [];
				if (infoData.length > 0) {
				  is_bind.value = true
				}
		    }
		})
        
        store.dispatch('changefooCheck','home')

        const setHeight = (ref) => {
            const el = document.getElementById(ref)
            return el?.offsetWidth ? el?.offsetWidth + 'px' : ''
        }
        // banner图轮播
        const banner = ref([])
        // banner图轮播
        const hyList = ref([])
        // 主info
        const monney = ref(store.state.minfo?.balance)
    const mInfo = ref(store.state.minfo)
        const activeTab = ref(1)
        const hzhbItems = ref([
            { title: 'msg.zxyhzs', value: '764.90K', bg: require('@/assets/images/bg_a.png') },
            { title: 'msg.pjyhyhl', value: '766.80K', bg: require('@/assets/images/bg_b.png') },
            { title: 'msg.yhxyrt', value: '762.24K', bg: require('@/assets/images/bg_c.png') },
            { title: 'msg.pttgyhsl', value: '780.06K', bg: require('@/assets/images/bg_d.png') }
        ])

        // background for vip cards
        const vipBg = require('@/assets/images/vip_bg.png')

        // Helper: parse display value like '764.90K' -> { num: 764.90, suffix: 'K' }
        const parseDisplayValue = (str) => {
            if (typeof str === 'number') return { num: str, suffix: '' }
            if (!str) return { num: 0, suffix: '' }
            const s = String(str).trim()
            // capture trailing non-digit characters as suffix
            const match = s.match(/^([\-0-9,.]+)\s*([a-zA-Z%]*)$/)
            if (!match) return { num: 0, suffix: '' }
            const numStr = match[1].replace(/,/g, '')
            const suffix = match[2] || ''
            const num = parseFloat(numStr) || 0
            return { num, suffix }
        }

        const formatDisplay = (num, suffix) => {
            // keep two decimals
            return `${Number(num).toFixed(2)}${suffix || ''}`
        }

        // initialize numeric fields for each item
        hzhbItems.value.forEach(item => {
            const p = parseDisplayValue(item.value)
            item._num = p.num
            item._suffix = p.suffix
            // normalize initial display to two decimals
            item.value = formatDisplay(item._num, item._suffix)
        })

        // random change between 0.01 and 1.99
        const randomDelta = () => {
            return +(Math.random() * (1.99 - 0.01) + 0.01).toFixed(2)
        }

        let _intervalId = null
        const applyRandomUpdate = () => {
            hzhbItems.value.forEach(item => {
                const delta = randomDelta()
                // randomly add or subtract
                const sign = Math.random() < 0.5 ? -1 : 1
                item._num = +(item._num + sign * delta).toFixed(2)
                // prevent negative
                if (item._num < 0) item._num = 0
                item.value = formatDisplay(item._num, item._suffix)
            })
        }

        onMounted(() => {
            // immediate update when component is first mounted / displayed
            applyRandomUpdate()
            // update once every 60s
            _intervalId = setInterval(() => {
                applyRandomUpdate()
            }, 60 * 1000)
        })

        // if component is wrapped in <keep-alive>, also update when activated
        onActivated(() => {
            applyRandomUpdate()
        })

        onUnmounted(() => {
            if (_intervalId) clearInterval(_intervalId)
        })

        getHomeData().then(res => {
            if(res.code === 0) {
                banner.value = res.data.banner
                monney.value = res.data.balance
                mInfo.value = {...res.data}
                store.dispatch('changeminfo',res.data || {})
            }
        })
        if (userinfo.value?.tel) {
            get_level_list().then(res => {
                console.log(res)
                if(res.code === 0) {
                    // ensure each item has optional text lines for the new card bottom
                    hyList.value = (res.data || []).map(i => ({ ...i, line1: i.line1 || '', line2: i.line2 || '', line3: i.line3 || '' }))
                }
            })
        }
        const toDetails = (id,title) => {
            push('/content?id='+id + '&title='+title)
        }
        const toRoute = (path,param,type = 1) => {
			if(type == 2){
				if (!is_bind.value) {
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
			}
            if (path){
                push(path + (param? '?param='+param : ''))
            }
        }
        const addLevel = (row) => {
            if (row.level <= mInfo.value?.level){
                push('/obj')
            } else {
                push('/addlevel?vip='+row.id)
            }
        }
    return {banner,monney,hyList,mInfo,currency,toRoute,addLevel,toDetails,a_content,showA,logo,userinfo,setHeight,app_name,activeTab,hzhbItems,vipBg}
    }
}
</script>
<style lang="scss" scoped>
@import '@/styles/theme.scss';
    .home{
        position: relative;
        // width: 100vw;
        overflow-x: hidden;
        overflow-y: auto;
        display: block !important;
        :deep(.van-nav-bar){
            // position: sticky;
            // top: 0;
            //background-color: #d4dff5;
            color: #333;
            padding: 40px 0;
            .van-nav-bar__left{
                .van-icon{
                    color: #fff;
                }
            }
            .van-nav-bar__title{
                color: #333;
                font-weight: 600;
                font-size: 28px;
            }
        }
        .logo-head{
            width: 250px;
            height: 100px;
        }
        .xiaoxiimg{
            width: 40px;
            height: 40px;
        }
        .top{
            height: 62px;
            position: absolute;
            top: 25px;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 25px;
            z-index: 888;
            .lang{
                height: 30px;
            }
        }
        .lang_box{
            width: 100%;
            position: relative;
            padding-top: 60px;
            font-size: 30px;
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
            .qiu{
                width: 175px;
                border-radius: 50%;
                box-shadow: $shadow;
                margin-bottom: 6px;
            }
            .langs{
                margin-bottom: 15px;
                .li{
                    padding: 24px;
                    display: block;
                    text-align: left;
                    margin-bottom: 10px;
                    max-height: 500px;
                    overflow: auto;
                    &.ctn{
                        padding: 24px;
                    }
                    &.check{
                        box-shadow: $shadow;
                    }
                    .img{
                        width: 80px;
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
        }
        .hy_box{
            width: 100%;
            color: #333;
            overflow: hidden;
            position: relative;
            .box{
                // background-image: url('~@/assets/images/home/hybj.png');
                // background-size: 100% 100%;
                // border-radius: 10px;
                background-color: #fff;
                border-radius: 12px;
                margin-bottom: 20PX;
                padding: 24px;
            }
            .t{
                margin-bottom: 18px;
                // height: 200px;
                display: flex;
                width: 100%;
                position: relative;
                .img{
                    width: 100%;
                }
                .ts{
                    width: 100%;
                    position: absolute;
                    top: 0;
                    left: 0;
                    display: flex;
                    justify-content: space-between;
                }
                .text{
                    display: inline-block;
                    border-radius: 30PX;
                    text-align: center;
                    vertical-align: middle;    
                    padding: 0 15px;
                    border-radius: 15px;
                    font-weight: 700;
                    background: red;
                    color: #fff;
                    font-size: 27px;
                    height: 25PX;
                    line-height: 25PX;
                }
                .txlevel {
                    border: none;
                    background-color: initial;
                    color: red;
                    line-height: 1;
                    height: auto;
                    text-align: right;
                    width: auto;
                    font-size: 0.8rem;
                    font-weight: 600;
                    .van-button__content{
                        justify-content: right;
                    }
                }
            }
            .b{
                font-size: 18px;
                display: flex;
                .sub{
                    color: orange;
                    font-size: 0.5rem;
                    font-weight: 600;
                    &:first-child{
                        flex: 1;
                    }
                    &:last-child{
                        width: 60PX;
                        text-align: right;
                        .span{
                            width: 100%;
                            height: 40px;
                            line-height: 40px;
                            font-size: 36px;
                            background-color: green;
                            color: #fff;
                            text-align: center;
                        }
                    }
                    .span{
                        display: block;
                        font-size: 24px;
                        margin-top: 15px;
                    }
                }
            }
        }
        .my-swipe{
            .van-swipe-item{
                padding: 0 30px;
                overflow: hidden;
            }
            .img{
                width: 100%;
                height: 400px;
                border-radius: 24px;
            }
        }
        .content{
            padding: 0 30px;
            text-align: left;
            margin-top: 50px;
            .zq{
                padding: 46px;
                margin-top: 40px;
                box-shadow: $shadow;
                border-radius: 24px;
                position: relative;
                overflow: hidden;
                .monney{
                    font-size: 50px;
                    background-image:-webkit-linear-gradient(left,#a570fb,#2620ce); 
                    background-clip: text;
                    -webkit-background-clip:text; 
                    -webkit-text-fill-color:transparent; 
                    font-weight: 600;
                }
                .text{
                    font-size: 32px;
                    color: #999;
                }
                &::after{
                    position: absolute;
                    content: '';
                    height: 8px;
                    width: 100%;
                    left: 0;
                    bottom: 0;
                    background-image:linear-gradient(90deg,#a570fb,#2620ce)
                }
            }
            .kszq{
                margin-top: 40px;
                font-size: 44px;
                height: 88px;
                line-height: 88px;
            }
        }
        .n_nav{
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            flex-wrap: wrap;
            text-align: center;
            &.jj{
                margin: 20px 0 60px;
                padding: 0 38px;
                .li{
                    .img{
                        width: 40px;
                        margin-bottom: 8px;
                    }
                    .text{
                        font-size: 18px;
                        color: $sub_theme;
                        text-align: center;
                    }
                }
            }
            .li{
                text-align: center;
                &.nav{
                    width: 25%;
                    text-align: left;
                    margin-bottom: 24px;
                    padding: 20px 0;
                    border-radius: 12px;
                    background-color: #fff;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    gap: 10px;
                    .text{
                        display: flex;
                        flex-direction: column;
                        justify-content: space-around;
                        font-size: 26px;
                        text-align: center;
                    }
                    .imge{
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        img{
                            width: 80px;
                            height: 80px;
                        }
                    }
                }
                .img{
                    width: 106px;
                    vertical-align: middle;
                }
                .text{
                    // white-space: nowrap;
                    font-size: 24px;
                    color: #333;
                }
            }
        }
        .ftitle{
            height: 34px;
            line-height: 34px;
            font-size: 40px;
            color: #333;
            margin: 40px 0px;
            white-space: nowrap;
            font-weight: 900;
            &.flex-between {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .load-more {
                font-size: 28px;
                color: #666;
                font-weight: normal;
                cursor: pointer;
                &:hover {
                    color: #1a7ae7;
                }
            }
        }
        .earnings{
            background: url('~@/assets/images/news/balance_bg.png') no-repeat;
            background-size: 100% 100%;
            padding: 54px 34px;
            margin-bottom: 24px;
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
                    // width: 60PX;
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
        .m_nav{
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            .li{
                width: 327px;
                margin-bottom: 36px;
                box-shadow: $shadow;
                display: flex;
                flex-direction: column;
                justify-content: center;
                font-size: 28px;
                color: $textColor;
                padding: 44px 0;
                text-align: center;
                border-radius: 10px;
                .monney{
                    font-size: 30px;
                    background-image:-webkit-linear-gradient(left,#856cf0,#a35df1); 
                    background-clip: text;
                    -webkit-background-clip:text; 
                    -webkit-text-fill-color:transparent; 
                    font-weight: 600;
                    margin-bottom: 15px;
                }
            }
        }
        .scroll{
            overflow: hidden;
            height: 270PX;
            .item{
                width: 100%;
                height: 75PX;
                margin-top: 15PX;
                // background-image: url('@/assets/images/home/dang.png');
                background-size: 100% 100%;
                display: flex;
                // justify-content: space-between;
                padding: 12PX 20px 12PX 0;
                // box-shadow: $shadow;
                background-color: #fff;
                border-radius: 12px;
                box-shadow: 0 2px 4px #dedede!important;
                .left{
                    line-height: 50PX;
                    padding: 0 50px;
                    font-size: 26px;
                    color: #333;
                }
                .right{
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    padding-left: 20px;
                    border-left: 1px solid #eee;
                    .t{
                        color: #333;
                        font-size: 26px;
                        margin-bottom: 25px;
                    }
                    .b{
                        color: #999;
                        font-size: 24px;
                    }
                }
            }
        }
        .hzhb{
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-between;
            .card{
                width: calc(50% - 10px);
                position: relative;
                border-radius: 30px;
                overflow: hidden;
                box-shadow: $shadow;
                background-color: transparent; /* 取消白色遮罩，显示背景图 */
                .bg{
                    position: absolute;
                    left: 0;
                    right: 0;
                    top: 0;
                    bottom: 0;
                    background-size: cover;
                    background-position: center;
                    opacity: 1; /* 不透明显示图片颜色 */
                    filter: none;
                }
                &.small{
                    height: 140px;
                }
                &.large{
                    height: 340px;
                }
                .card-content{
                    position: relative;
                    z-index: 2;
                    padding: 20px;
                    height: 100%;
                    display: flex;
                    flex-direction: column; /* 上下排列 */
                    align-items: flex-start; /* 左对齐文本 */
                    gap: 8px;
                    .card-title{
                        font-size: 24px; /* 字体变大 */
                        font-weight: 700;
                    }
                    .card-value{
                        font-size: 40px; /* 字体变大 */
                        font-weight: 900;
                    }
                }
                /* 文字颜色变体 */
                .text-white{
                    .card-title,
                    .card-value{
                        color: rgba(255,255,255,0.95);
                        text-shadow: 0 1px 3px rgba(0,0,0,0.35);
                    }
                }
                .text-black{
                    .card-title,
                    .card-value{
                        color: rgba(0,0,0,0.9);
                        text-shadow: none;
                    }
                }
            }
        }
        /* 横向可滑动 VIP 卡片 */
        .vip-swiper{
            margin-top: 40px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 20px;
            margin-bottom: 50px;
        }
        .vip-cards{
            display: flex;
            gap: 32px;
            align-items: stretch;
        }
        .vip-card{
            min-width: 520px; /* 原260px的2倍 */
            height: 520px; /* 原360px的2倍 */
            flex: 0 0 auto;
            border-radius: 24px;
            background-size: cover;
            background-position: center;
            position: relative;
            overflow: hidden;
            box-shadow: $shadow;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }
        .vip-card .card-top{
            padding: 36px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            align-items: center;
        }
        .vip-card .name{
            color: #fff;
            font-size: 44px; /* 原22px的2倍 */
            font-weight: 800;
        }
        .vip-card .thumb{
            width: 112px; /* 原56px的2倍 */
            height: 112px;
            object-fit: cover;
            border-radius: 16px;
        }
        .vip-card .card-bottom{
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            padding: 32px;
            background: #ffffff63; /* 半透明涂层 */
            color: #000;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .vip-card .card-bottom .line{
            font-size: 28px; /* 原14px的2倍 */
            line-height: 1.4;
            font-weight: 500;
        }
    }
</style>