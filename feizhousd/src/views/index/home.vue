<template>
    <div class="home">
        <van-nav-bar>
            <template #left>
                <img :src="require('@/assets/images/head.png')" class="logo-head" height="30" alt="">
            </template>
            <template #right>
                <!-- <img :src="$store.state.langImg" class="lang" height="18" width="27" alt=""> -->
                <!-- <lang-vue></lang-vue> -->
                 <img @click="$router.push({path: '/message'})" :src="require('@/assets/images/news/msg2.png')" width="26.5" alt="">
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
            <!-- 开始赚钱 -->
            <!-- <div class="zq">
                <div class="monney">{{currency}}{{monney}}</div>
                <div class="text">{{$t('msg.my_yu_e')}}</div>
            </div>
            <van-button type="primary" class="kszq" round block  @click="toRoute('/obj')">{{$t('msg.kszq')}}</van-button> -->
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
                        <span >{{currency}}
                            <span class="mm">{{monney}}</span>
                        </span>
                        <span >{{currency}}
                            <span class="mm">{{mInfo.freeze_balance}}</span></span>
                    </div>
                    <div class="count-data">
                        <div class="flex-full">
                            <div >{{$t('msg.today_monney')}}</div>
                            <div >{{currency}}{{mInfo.yon1}}</div>
                        </div>
                        <div class="flex-full">
                            <div >{{$t('msg.zrsy')}}</div>
                            <div >{{currency}} {{mInfo.Yesterdaysearnings}}</div>
                        </div>
                    </div>
                    <div class="count-data">
                        <div class="flex-full">
                            <div >{{$t('msg.get_monney')}}</div>
                            <div class="two">{{currency}}{{mInfo.yon3}}</div>
                        </div>
                        <div class="flex-full">
                            <div >{{$t('msg.tdsy')}}</div>
                            <div class="two">{{currency}} {{mInfo.Teambenefits}}</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 次导航 -->
            <div class="n_nav">
                <div class="li nav" @click="toRoute('/chongzhi')">
                    <div class="imge">
                        <img :src="require('@/assets/images/news/ic_recharge.png')" width="40" height="40" alt="">
                    </div>
                    <div class="text" style="color: #8389fb; font-weight: 600;">{{$t('msg.mscz')}}</div>
                </div>
                <div class="li nav" @click="toRoute('/drawing',monney,2)">
                    <div class="imge">
                        <img :src="require('@/assets/images/news/ic_withdraw.png')" width="40" height="40" alt="">
                    </div>
                    <div class="text" style="color: #fb9833; font-weight: 600;">{{$t('msg.kstx')}}</div>
                </div>
                <div class="li nav" @click="toRoute('/libao',monney)">
                    <div class="imge">
                        <img :src="require('@/assets/images/news/ic_finance.png')" width="40" height="40" alt="">
                    </div>
                    <div class="text" style="color: #f85355; font-weight: 600;">{{$t('msg.jflc')}}</div>
                </div>
                <div class="li nav" @click="toRoute('/share')">
                    <div class="imge">
                        <img :src="require('@/assets/images/news/ic_invite.png')" width="40" height="40" alt="">
                    </div>
                    <div class="text" style="color: #3bc180; font-weight: 600;">{{$t('msg.yqhy')}}</div>
                </div>
            </div>
            
            <!-- banner图 -->
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
            <!-- 会员收益 -->
            <!-- <div class="ftitle">
                {{$t('msg.get_monney')}}
            </div> -->
            <!-- <div class="m_nav">
                <div class="li">
                    <div class="monney">{{currency}}{{mInfo.yon1}}</div>
                    <div class="text">{{$t('msg.today_monney')}}</div>
                </div>
                <div class="li">
                    <div class="monney">{{currency}}{{mInfo.yon2}}</div>
                    <div class="text">{{$t('msg.today_yonj')}}</div>
                </div>
                <div class="li">
                    <div class="monney">{{currency}}{{mInfo.yon3}}</div>
                    <div class="text">{{$t('msg.get_monney')}}</div>
                </div>
                <div class="li">
                    <div class="monney">{{currency}}{{mInfo.yon4}}</div>
                    <div class="text">{{$t('msg.ylb')}}</div>
                </div>
            </div> -->
            <!-- 会员收益 -->
            <div class="ftitle">
                {{$t('msg.yhyjsrdt')}}
            </div>
            <vue3-seamless-scroll :list="mInfo.list || []" class="scroll" :waitTime="600" :step="7" :delay="0.5" hover :limitScrollNum="3" :singleHeight="90" >
                <div class="item" v-for="(item, index) in mInfo.list || []" :key="index">
                    <span class="left">{{ item.addtime }}</span>
                    <span class="right">
                        <span class="t">{{$t('msg.sryj')}}：{{currency}}{{ item.today_income }}</span>
                        <span class="b">{{item.name.replace(/(.{3}).*(.{3})/,"$1******$2")}}</span>
                    </span>
                </div>
            </vue3-seamless-scroll>

            <!-- 公司简介 -->
            <div class="n_nav">
                <div class="li nav" @click="toDetails(2,$t('msg.gsjj'))">
                    <img :src="require('@/assets/images/news/poster_1.png')" class="img" alt="">
                    <div class="text">{{$t('msg.gsjj')}}</div>
                </div>
                <div class="li nav" @click="toDetails(3,$t('msg.gzms'))">
                    <img :src="require('@/assets/images/news/poster_2.png')" class="img" alt="">
                    <div class="text">{{$t('msg.gzms')}}</div>
                </div>
                <div class="li nav" @click="toDetails(4,$t('msg.dlhz'))">
                    <img :src="require('@/assets/images/news/poster_3.png')" class="img" alt="">
                    <div class="text">{{$t('msg.dlhz')}}</div>
                </div>
                <div class="li nav" @click="toDetails(12,$t('msg.qyzz'))">
                    <img :src="require('@/assets/images/news/poster_4.png')" class="img" alt="">
                    <div class="text">{{$t('msg.qyzz')}}</div>
                </div>
            </div>
            <!-- 会员收益 -->
            <div class="ftitle">
                {{$t('msg.hzhb')}}
            </div>
            <div class="hzhb">
                <img :src="require('@/assets/images/news/'+num+'.png')" v-for="num in 6" :key="num" class="img" alt="">
            </div>
        </div>
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
import { ref, getCurrentInstance,reactive } from 'vue';
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
        const monney = ref('')
        const mInfo = ref({})

        getHomeData().then(res => {
            if(res.code === 0) {
                banner.value = res.data.banner
                monney.value = res.data.balance
                mInfo.value = {...res.data}
            }
        })
        if (userinfo.value?.tel) {
            get_level_list().then(res => {
                console.log(res)
                if(res.code === 0) {
                    hyList.value = res.data
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
        return {banner,monney,hyList,mInfo,currency,toRoute,addLevel,toDetails,a_content,showA,logo,userinfo,setHeight,app_name}
    }
}
</script>
<style lang="scss" scoped>
@import '@/styles/theme.scss';
    .home{
        position: relative;
        width: 100vw;
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
                    }
                }
            }
            .li{
                text-align: center;
                &.nav{
                    width: 48%;
                    text-align: left;
                    margin-bottom: 24px;
                    padding: 24px 0 24px 24px;
                    border-radius: 12px;
                    background-color: #fff;
                    display: flex;
                    .text{
                        margin-left: 15px;
                        display: flex;
                        flex-direction: column;
                        justify-content: space-around;
                        font-size: 26px;
                    }
                    .imge{
                        width: 40PX;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                    }
                    .img{
                        width: 40PX;
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
            font-size: 30px;
            color: #333;
            margin: 40px 0;
            white-space: nowrap;
            &::before{
                content: '';
                display: inline-block;
                height: 100%;
                width: 10px;
                margin-right: 12px;
                background-color: $theme;
                vertical-align: middle;
            }
        }
        .earnings{
            background: url('~@/assets/images/news/balance_bg.png') no-repeat;
            background-size: 100% 100%;
            padding: 24px;
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
                margin: 20px 0;
                font-size: 30px;
                // font-weight: 500;
                color: #000;
                display: flex;
                justify-content: space-between;
            }
            .balance-val{
                margin: 10px 0 15px;
                font-size: 50px;
                font-family: PingFangSC-Semibold,PingFang SC;
                font-weight: 600;
                color: #000;
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
                margin-top: 15px;
                .flex-full{
                    flex: 1;
                    color: #000;
                    font-size: 24px;
                    .two{
                        display: block;
                        font-weight: 600;
                    }
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
            justify-content: space-between;
            flex-wrap: wrap;
            .img{
                width: 210px;
                box-shadow: $shadow;
                border-radius: 10px;
                margin-bottom: 30px;
            }
        }
    }
</style>