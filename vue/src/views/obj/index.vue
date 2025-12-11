<template>
    <div class="obj home">
        <div class="img_loading" v-if="loading">
            <img :src="loadImg" class="img" alt="">
            <div>{{loadText}}</div>
        </div>
        <div class="content">
            <div class="header-card">
                    <div class="hc-left">
                        <van-image :src="require('@/assets/images/news/users.png')" class="hc-avatar" fit="cover" />
                        <div class="hc-meta">
                            <div class="hc-name">{{ userinfo.tel }}
                                <img v-if="userinfo.level" :src="require('@/assets/images/self/vip'+ level +'.png')" class="vip" alt="">
                            </div>
                            
                            <div class="hc-score-label">
                                <div>{{$t('msg.xyf') }}:</div>                              
                                <van-progress class="jindutiao" :percentage="Number(creditPercent)" pivot-text="" stroke-width="6" color="#fdb824" inactive-color="#ffeedd" />
                                <div>
                                    {{ creditPercent }}%
                                </div>                          
                            </div>

                        </div>
                    </div>
                </div>
            <!-- Earnings block copied/adapted from home.vue -->
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
            <div class="vipinfo">
                <div class="vipimg">
                    <img v-if="level" :src="require('@/assets/images/self/vip'+ level +'.png')" class="vip" alt="">
                    VIP  {{ level }} ({{info.day_completed_count || 0}}/{{info.order_num || 0}})
                </div>
                <div class="vipright">
                    {{ $t('msg.get_monney') }}: {{(info.level_info?.bili*100 || 0).toFixed(1)}}%
                </div>
            </div>
            <!-- 背景视频（自动循环、无控件，作为背景展示） -->
            <div class="video-bg">
                <video class="video-bg__media" :src="qiangdanMp4" autoplay loop muted playsinline preload="auto"></video>
            </div>
            <div class="yonj">
                <div class="btnn">
                    <van-button round block type="primary" class="ksqg" @click="getDd()">
                        <img :src="require('@/assets/images/news/click.png')" class="img" height="35" alt="">
                        {{loading ? $t('msg.hddd') : $t('msg.obj')}}
                    </van-button>
                </div>
            </div>
            <!-- <div class="qd">
                <div class="title">{{$t('msg.qdsm')}}</div>
                <div class="sub" v-html="content"></div>
            </div> -->
        </div>

        <!-- 会员等级弹窗 -->
        <van-dialog v-model:show="level_show" :title="$t('msg.djsm')" :cancelButtonText="$t('msg.quxiao')" show-cancel-button :showConfirmButton="false">
            <!-- banner图 -->
            <van-swipe class="my-swipe" indicator-color="white">
                <van-swipe-item v-for="item in info.level_list || []" :key="item.id">
                    <div class="hy_box">
                        <div class="t">
                            <img :src="require('@/assets/images/home/huiyuan.png')" class="img" alt="">
                            <span class="text">{{$t('msg.hy_level')}} {{item.name}}</span>
                        </div>
                        <div class="b">
                            <div class="sub">{{$t('msg.sxtz')}}： {{currency}}{{item.num}}</div>
                            <div class="sub">{{$t('msg.yonj')}}： {{item.bili*100}}% <span class="line">|</span>{{item.order_num}}{{$t('msg.order')}}</div>
                        </div>
                        <van-button type="primary" class="txlevel" round block>{{item.level <= info.uinfo?.level ? $t('msg.now_level') : $t('msg.add_level')}}</van-button>
                    </div>
                </van-swipe-item>
            </van-swipe>
        </van-dialog>

        <van-dialog v-model:show="showTj" :confirmButtonText="$t('msg.queren')" :cancelButtonText="$t('msg.close')" :show-confirm-button="true" :title="$t('msg.ddxq')" show-cancel-button @confirm="confirmPwd">
            <template #title>
                <div style="text-align: center">{{$t('msg.ddxq')}}</div>
            </template>
            <div class="list" v-if="onceinfo.data?.group_rule_num == 0 || onceinfo.data?.duorw == 0">
                <div class="cet">
                    <img :src="onceinfo.data?.goods_pic" class="img" alt="">
                </div>
                <div class="monney">
                    <div class="tent">
                        <span class="span">{{$t('msg.ddh')}}</span>
                        <span class="value">{{onceinfo.data?.oid}}</span>
                    </div>
                    <div class="tent">
                        <span class="span">{{$t('msg.xdsj')}}</span>
                        <span class="value">{{formatTime('',onceinfo.data?.addtime)}}</span>
                    </div>
                    <div class="tent">
                        <span class="span">{{$t('msg.spdj')}}</span>
                        <span class="value">{{currency+onceinfo.data?.goods_price}}</span>
                    </div>
                    <div class="tent">
                        <span class="span">{{$t('msg.spsl')}}</span>
                        <span class="value">{{'x ' + onceinfo.data?.goods_count}}</span>
                    </div>
                    <div class="tent">
                        <span class="span">{{$t('msg.order_Num')}}</span>
                        <span class="value">{{currency+onceinfo.data?.num}}</span>
                    </div>
                    <div class="tent">
                        <span class="span">{{$t('msg.yonj')}}</span>
                        <span class="value">{{currency+onceinfo.data?.commission}}</span>
                    </div>
                </div>
            </div>
            <div class="list" v-else>
                <div class="tops">
                    <span class="span">{{$t('msg.ddrws')}}：</span>
                    <span class="span" style="color:red;">{{onceinfo.data?.duorw}}</span>
                </div>
                <div class="tops">
                    <span class="span">{{$t('msg.ywc')}}：</span>
                    <span class="span" style="color:#00a300;">{{onceinfo.data?.completedquantity}}</span>
                </div>
                <div class="box" v-for="item in onceinfo.group_data" :key="item.id">
                    <div class="cet">
                        <img :src="item?.goods_pic" class="img" alt="">
                    </div>
                    <div class="monney">
                        <div class="tent">
                            <span class="span">{{$t('msg.spdj')}}</span>
                            <span class="value">{{currency+item?.goods_price}}</span>
                        </div>
                        <div class="tent">
                            <span class="span">{{$t('msg.spsl')}}</span>
                            <span class="value">{{'x ' + item?.goods_count}}</span>
                        </div>
                        <div class="tent">
                            <span class="span">{{$t('msg.order_Num')}}</span>
                            <span class="value">{{currency+item?.num}}</span>
                        </div>
                        <div class="tent">
                            <span class="span">{{$t('msg.fkzt')}}</span>
                            <span class="value" :class="'value'+item.is_pay">{{item.is_pay === 0 ? $t('msg.dfk') : $t('msg.yfk')}}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pinglun">
                <div class="pingluna">
                    <div>
                        {{ $t('msg.dianjifabiaopinglun') }}
                    </div>
                    <div>
                        <van-rate
                        v-model="pinglun"
                        :size="20"
                        color="#ffd21e"
                        void-icon="star"
                        void-color="#eee"
                        />
                    </div>
                </div>
                <div class="pinglunb">
                    <van-cell-group inset>
                        <van-field
                            v-model="pingluntext"
                            rows="2"
                            type="textarea"
                            placeholder=""
                            :required="true"
                            :center="true"
                        >
                        <template #button>
                            <van-button @click="generateRandomComment" size="mini" color="#ff9800">{{ $t('msg.zidongpinglun') }}</van-button>
                            </template>
                        </van-field>
                    </van-cell-group>
                </div>
            </div>
        </van-dialog>
    </div>
</template>
<script>
import { ref,getCurrentInstance, reactive, computed} from 'vue';
import {rot_order,submit_order,order_info,do_order} from '@/api/order/index'
import store from '@/store/index'
import {getdetailbyid,getHomeData} from '@/api/home/index.js'
import {formatTime} from '@/api/format.js'
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n'
import {getsupport} from '@/api/tel/index'
import qiangdanMp4 from '@/assets/images/qiangdan.mp4'
export default {
    setup(){
         const { t } = useI18n()
         const userinfo = ref(store.state.userinfo)
        const { push } = useRouter();
        const {proxy} = getCurrentInstance()
    const level_show = ref(false)
        const loading = ref(false)
        const loadText = ref('')
        const loadImg = ref('')
        const level = ref(store.state.minfo?.level || 0)
        const currency = ref(store.state.baseInfo?.currency)
        const info = ref(store.state.objInfo)
        const pinglun = ref(0)
        const pingluntext = ref('')
        const creditPercent = ref(store.state.minfo?.credit || 0)
    // earnings related (copied/adapted from home.vue)
    const activeTab = ref(1)
    // 主info
        const monney = ref(store.state.minfo?.balance)
    const mInfo = ref(store.state.minfo)

        const onceinfo = ref({})
        const showTj = ref(false)
        const content = ref('')
        const support = ref('')
        
        const status_list= reactive([
            // {label: t('msg.dsh'),value: -1},
            {label: t('msg.dtj'),value: 0},
            {label: t('msg.ytj'),value: 1},
            {label: t('msg.yhqx'),value: 2},
            {label: t('msg.qzwc'),value: 3},
            {label: t('msg.qzqx'),value: 4},
            {label: t('msg.djz'),value: 5},
        ])
        store.dispatch('changefooCheck','obj')
        getdetailbyid(20).then(res => {
            content.value = res.data?.content
        })
        const initData = () => {
            rot_order().then(res => {
                if(res.code === 0) {
                    console.log(res)
                    info.value = {...res.data}
                    store.dispatch('changeobjInfo',info.value)
                }
            })
        }
        initData()
        const tjOrder = (row) => {
            push({ name: 'detail', params: { id: row.oid } })
            // order_info({id: row.oid}).then(res => {
            //     loading.value = false
            //     onceinfo.value = {...res}
            //     showTj.value = true
            // })
        }
        //tjOrder()

        const confirmPwd = () => {
            let id = ''
            if (onceinfo.value.group_data && onceinfo.value.group_data.length > 0) {
                let info = onceinfo.value.group_data?.find(rr => rr.is_pay === 0)
                id = info?.oid
            } else {
                id = onceinfo.value.data?.oid || onceinfo.value.data?.id
            }
            let json = {
                oid: id,
                status: 1,
                pingfen: pinglun.value,
                pinglun: pingluntext.value
            }
            do_order(json).then(res => {
                if(res.code === 0) {
                    // data不存在或者duorw为0，不匹配订单
                    const group_data = onceinfo.value.group_data || []
                    if ((!onceinfo.value.data || onceinfo.value.data.duorw === 0)) {
                        proxy.$Message({ type: 'success', message:res.info});
                        showTj.value = false
                        initData()
                    } else if (group_data.length == onceinfo.value.data.duorw) {
                        //duorw > 0 但是 grpoup_data.length == duorw的情况，不进行匹配订单
                        proxy.$Message({ type: 'success', message:res.info});
                        showTj.value = false
                        initData()
                    } else {
                        submit_order().then(()=>{
                            showTj.value = false
                            Toast.success(t('msg.tjcg'))
                            initData()
                        })
                    }
                } else {
                    proxy.$Message({message: res.info, type: 'error'})
                }
            })
        }
        const cancelPwd = () => {
            initData()
        }
        const clickRight = () => {
            // push('/tel')
            if (support.value) {
                location.href = support.value
                // window.open(support.value)
            } else {
                push('/tel')
            }
        }
        getsupport().then(res => {
            if(res.code === 0) {
                support.value = res.data[0]?.url
            }
        })

        getHomeData().then(res => {
            if(res.code === 0) {
                monney.value = res.data.balance
                mInfo.value = {...res.data}
                creditPercent.value = res.data.credit
                level.value = res.data.level
                store.dispatch('changeminfo',res.data || {})
            }
        })

        const getDd = async () => {
            if(loading.value) return false
            loading.value = true
            loadText.value = t('msg.zzszsj')
            loadImg.value = require('@/assets/images/1.gif')
            let submit = null
            let time = (info.value.deal_zhuji_time || 1)*1000
            let time2 = (info.value.deal_shop_time || 2)*1000
            setTimeout(async () => {
                loadText.value = t('msg.zzppsp')
                loadImg.value = require('@/assets/images/2.gif')
                submit = await submit_order()
                setout(submit, time2)
            }, time);
        }
        const setout = (json,time) => {
            setTimeout(()=> {
                if (json) {
                    if(json.code === 0) {
                        loadImg.value = require('@/assets/images/3.gif')
                        loadText.value = t('msg.ppcg')
                        setTimeout(()=>{
                            proxy.$Message({message: json.info, type: 'success'})
                            tjOrder(json)
                        }, 1000)
                    } else {
                        proxy.$Message({message: json.info, type: 'error'})
                        loading.value = false
                    }
                    console.log(json)
                } else {
                    setout(json,time)
                }
            },time)
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
    const generateRandomComment = () => {
    const comments = [
        "I absolutely love this product! It exceeded my expectations.",
        "Excellent quality and great value for money.",
        "This is exactly what I was looking for. Highly recommended!",
        "The quality is outstanding and it works perfectly.",
        "Very satisfied with my purchase. Will buy again!",
        "This product is amazing and worth every penny.",
        "Fast shipping and the product is even better than described.",
        "I'm really impressed with the quality and performance.",
        "This has made my life so much easier. Thank you!",
        "Great product with excellent craftsmanship.",
        "Better than I expected! The quality is superb.",
        "I would definitely recommend this to my friends.",
        "Perfect in every way. No complaints at all!",
        "The attention to detail is remarkable.",
        "This product is a game-changer! So glad I bought it.",
        "High-quality materials and excellent workmanship.",
        "Exceeded my expectations in every aspect.",
        "I'm very happy with this purchase. It's fantastic!",
        "Well designed and very functional. Love it!",
        "This is by far the best product I've bought this year."
    ];
    
    const randomIndex = Math.floor(Math.random() * comments.length);
    pingluntext.value = comments[randomIndex];
    };
    return {pingluntext,generateRandomComment,pinglun,info,currency,level,level_show,loading,getDd,clickRight,confirmPwd,tjOrder,showTj,onceinfo,formatTime,cancelPwd,content, loadText,status_list,loadImg,activeTab,monney,mInfo, userinfo,creditPercent,copyInvite, qiangdanMp4}
    }
}
</script>
<style lang="scss" scoped>
.obj{
    display: flex;
    flex-direction: column;
    padding-bottom: 0 !important;
    .content{
        flex: 1;
        // margin-top: 30px;
        background-color: #fff;
        padding: 30px;
        overflow: auto;
        border-top-left-radius: 30px;
        border-top-right-radius: 30px;
        padding-bottom:140px;
        color: #333;
        .header-card{
                margin-top: 20px;
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
                .hc-copy{ background:#fdb824; color:#fff; padding:0 8px;border-radius: 15px; }
                .hc-score-label{ font-size:28px; color:#666;display: flex;flex-direction: row;align-items: center;gap: 20px; }
                .jindutiao{width: 150px;}
                .vip{width: 55px;padding: 0;background-color: #fff;margin-top: 0;box-shadow: 0 0 2.666667vw 0 #ffffff;}
            }
        .vipinfo{
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: $shadow;
            border-radius: 20px;
            height: 100px;
            font-size: 32px;
            font-weight: 900;
            padding: 15px;
            line-height: 100px;
            .vipimg{
                display: flex;
                align-items: center;
                gap: 15px;
                .vip{
                    width: 60px;
                    background-color: #fff;
                    margin-top: 0;
                    box-shadow: 0 0 2.666667vw 0 #ffffff;
                    padding: 0;
                }
            }           
            .vipright{
                font-size: 24px;
                color: #000;
                background-color: #fdb824;
                border-radius: 10px;
                padding: 10px;
                line-height: 30px;
            }
        }
        .video-bg{
            margin-top: 16px;
            width: 100%;
            height: 400px; /* 与 vipinfo 类似的视觉高度，可根据需要调整 */
            overflow: hidden;
            border-radius: 35px;
            box-shadow: $shadow;
            background-color: #000;
            .video-bg__media{
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
                pointer-events: none; /* 禁止交互 */
            }
        }
        .money{
            display: flex;
            justify-content: space-between;
            .left{
                .top{
                    font-size: 60px;
                    .span{
                        margin-left: 3px;
                    }
                }
                .text{
                    font-size: 16px;
                    color: #999;
                    text-align: left;
                }
            }
            .right{
                position: relative;
                .van-button--primary{
                    width: 100px;
                    height: 80%;
                    border-radius: 40px;
                }
                .img{
                    width: 90px;
                    position: absolute;
                    right: 0;
                    top: 50%;
                    transform: translateY(-50%);
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
                    color: #ffffff;
                    font-weight: bold;
                    &:first-child{
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
                    border-left: 3px solid white;
                    padding-left: 20px;
                }
                .flex-full:not(:last-child) {
                    padding-right: 15px;
                }
            }
            .count-switch{
                margin-top: 12px;
                display: flex;
                align-items: center;
                justify-content: flex-start;
                gap: 16px;
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
                    color: #1a7ae7;
                }
            }
        }
        .vip{
            border-radius: 20px;
            box-shadow: $shadow;
            position: relative;
            color: #fff;
            margin-top: 40px;
            padding: 30px;
            background-color: $theme;
            .right{
                position: absolute;
                right: 30px;
                top: 30px;
                width: 40px;
            }
            .top{
                display: flex;
                margin-bottom: 50px;
                // .l{
                //     width: 65px;
                //     margin-right: 20px;
                // }
                .r{
                    display: flex;
                    flex-direction: column;
                    justify-content: space-around;
                    .title{
                        font-size: 60px;
                        text-align: left;
                        margin-bottom: 30px;
                    }
                    .sub{
                        font-size: 32px;
                        margin-bottom: 5px;
                        text-align: left;
                        &.s1{
                            font-size: 18px;
                        }
                    }
                }
            }
            .b{
                font-size: 56px;
                text-indent: 4px;
                .span{
                    background: limegreen;
                    padding: 5px 8px;
                }
            }
        }
        .yonj{
            margin-top: 40px;
            display: flex;
            flex-wrap: wrap;
            box-shadow: $shadow;
            border-radius: 24px;
            margin-bottom: 100px;
            .li{
                width: 50%;
                text-align: left;
                padding:24px 0;
                border-bottom: 1px solid #ccc;
                
                .top{
                    font-size: 30px;
                    color: #333;
                    // margin-bottom: 20px;
                }
                .text{
                    font-size: 14px;
                    color: #999;
                }
                &:nth-child(2n){
                    text-align: right;
                }
            }
            .btnn{
                width: 100%;
                // border: 1px solid #999;
                .ksqg{
                    height: 85px;
                    line-height: 85px;
                    font-size: 30px;
                    border-radius: 24px;
                    .img{
                        vertical-align: middle;
                    }
                }
            }

        }
        .qd{
            margin-top: 40px;
            text-align: left;
            .title{
                font-size: 28px;
                font-weight: 600;
                color: #333;
            }
            .sub{
                font-size: 24px;
                line-height: 30px;
                margin-top: 10px;
                color: #333;
            }
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
        text-align: left;
        .t{
            margin-bottom: 18px;
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
            .sub{
                .line{
                    margin: 0 22px;
                }
            }
        }
        .txlevel{
            position: absolute;
            right: 25px;
            top: 50%;
            transform: translateY(-50%);
            width: 150px;
            height: 60px;
            padding: 0;
            // line-height: 60px;
            font-size: 24px;
            color: #2620ce;
            background-color: #e2e6ff;
            border-radius: 20px;
            border: none;
        }
    }
    :deep(.van-dialog){
        .van-dialog__header{
            text-align: left;
            padding: 20px 40px;
            font-weight: 600;
            
        }
            .list{
                padding: 0 40px;
                box-shadow: none;
                max-height: 40vh;
                overflow: auto;
                display: flex;
                flex-direction: column;
                .tops {
                    margin-bottom: 0;
                    color: #333;
                    .span {
                        margin-right: 24px;
                    }
                }
                .box{
                    padding: 15px;
                    border: 2px solid #ccc;
                    margin-top: 24px;
                    &:first-child{
                        margin-top: 0;
                    }
                    .value0 {
                        padding: 3px 10px;
                        background-color: red;
                        color: #fff;
                    }
                    .value1 {
                        padding: 3px 10px;
                        background-color: #07c160;
                        color: #fff;
                    }
                }
            }
            .van-dialog__content{
                max-height: 60vh;
                overflow: auto;
            }
            .van-dialog__footer{
                margin-top: 40px;
                .van-dialog__confirm{
                    color: $theme;
                }
            }
    }
    .list{
        padding: 30px;
        box-shadow: $shadow;
        color: $subtext;
        text-align: left;
        margin-top: 40px;
        border-radius: 10px;
        .top{
            display: flex;
            justify-content: space-between;
            margin-bottom: 35px;
            .time{
                font-size: 16px;
            }
            .tab{
                font-size: 20px;
                color: $theme;
            }
        }
        .cet{
            display: flex;
            background-color: #fafafa;
            padding: 10px 0;
            .img{
                width: 100%;
                height: 180PX;
            }
            .text{
                font-size: 20px;
            }
        }
        .monney{
            margin-top: 30px;
            .tent{
                display: flex;
                justify-content: space-between;
                font-size: 24px;
                .span{
                    width: 120px;
                    // text-align: justify;
                    // text-justify: distribute-all-lines; 
                    // text-align-last: justify; 
                    color: #333;
                }
            }
        }
        .van-button{
            font-size: 32px;
            margin-top: 50px;
        }
        
    }
}
.img_loading{
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(151, 151, 151, 0.821);
    color: #fff;
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: center;
    font-size: 32px;
    font-weight: 600;
    .img{
        width: 80%;
        background-color: rgba(0,0,0,0.5);
        margin: 0 auto 30px;
        border-radius: 12px;
    }
    z-index: 888;
}
.pinglun{
    margin: 20px 30px;
    margin-bottom: 0px;
    font-size: 26px;
    color: #000000;
    display: flex;
    flex-direction: column;
    align-items: center;
    font-weight: 900;
    .pingluna{
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }
    .pinglunb{
        margin-top: 20px;
            width: 90%;
    border: 1px solid #dadada;
    border-radius: 5px;
    }
}
</style>