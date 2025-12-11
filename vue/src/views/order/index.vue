<template>
    <div class="order home">
        <!-- <van-loading size="24px" vertical v-show="loading">{{$t('msg.loading')}}...</van-loading> -->
        <!-- <van-nav-bar :title="$t('msg.order')" @click-right="clickRight"> -->
        <van-tabs v-model:active="active" @click-tab="initData" type="card">
            <van-tab v-for="item in status_data" :key="item.value" :title="item.label">
               <div class="list" v-for="info in list" :key="info.id">
                   <div class="top">
                       <span class="time">{{formatTime('',info.addtime)}}</span>
                       <div class="number">{{info.id}}</div>
                   </div>
                   <div class="cet aaa">
                       <img :src="info.goods_pic" class="img" alt="">
                       <div class="text">
                           {{info.goods_name}}
                           <!-- {{info.status + '=' + 5 + ';' + info.is_pay + '=' + 1 + ';' + info.duorw }} -->
                            <div class="tab" :class="info.status == -1">
                                <span class="span">{{(info.duorw > 0 && info.time_limit > 1) ? $t('msg.dtj') : status_list?.find(rr => rr.value == info.status)?.label}}</span>
                            </div>
                            <div class="tent">
                                <span class="span">{{currency+info?.goods_price}}</span>
                                <span class="value">{{'x ' + info?.goods_count}}</span>
                            </div>
                       </div>
                   </div>
                   <div class="monney">
                       <div class="tent">
                           <span class="span">{{$t('msg.order_Num')}}</span>
                           <span class="value">{{currency+info.num}}</span>
                       </div>
                       <div class="tent">
                           <span class="span">{{$t('msg.yonj2')}}</span>
                           <span class="value">{{currency+info.commission}}</span>
                       </div>
                       <div class="tent" v-if="info.duorw && info.time_limit < 1">
                           <span class="span">{{$t('msg.djje')}}</span>
                           <span class="value">{{currency+info.user_freeze_balance}}</span>
                       </div>
                       <div class="tent" v-else-if="info.status == 5">
                           <span class="span">{{$t('msg.djje')}}</span>
                           <span class="value">{{currency+info.user_freeze_balance}}</span>
                       </div>
                       <div class="tent" v-if="info.duorw">
                           <span class="span">{{$t('msg.dqjd')}}</span>
                           <span class="value">{{(info.completedquantity || 0) + '/' + (info.duorw || 0)}}</span>
                       </div>
                       <div class="tent">
                           <!-- <span class="span">{{$t('msg.rwsx')}}：{{formatTime('',info.endtime)}}</span> -->
                           <!-- <van-count-down :time="(info.time_limit*1000)"  v-if="info.status == 0 && info.time_limit > 0"/> -->
						   
                           <!-- <span class="value" style="color: red">{{countTime(info.addtime, info.endtime)}}</span>
                           <span class="value" style="color: red">{{countTime(info.addtime, info.endtime).hours + ' : ' + countTime(info.addtime, info.endtime).minutes + ' : ' + countTime(info.addtime, info.endtime).seconds}}</span> -->
                       </div>
                       <div class="tent" v-if="info.status == 1">
                           <span class="span"></span>
                           <span class="value"><van-rate v-model="info.pingfen" readonly color="#ffd21e" void-icon="star" void-color="#d1d1d1" /></span>
                       </div>
                   </div>
                    <van-button class="tj-btn" round block type="primary" v-if="info.status == 0 || (info.duorw > 0 && info.time_limit > 1)" @click.stop="goDetail(info.id)">{{$t('msg.tjdd')}}</van-button>
                    <van-button round block type="danger" v-if="info.duorw > 0 &&  info.time_limit < 1" @click.stop="toTei()">{{$t('msg.lxkfjd')}}</van-button>
                    <van-button round block type="danger" v-else-if="info.status == 5" @click.stop="toTei()">{{$t('msg.lxkfjd')}}</van-button>
               </div>
               <van-empty v-if="list.length == 0" :description="$t('msg.zwdd')" />
            </van-tab>
        </van-tabs>
        <!-- dialog 已移至 detail 页面 -->
    </div>
</template>
<script>
import { ref,reactive,getCurrentInstance } from 'vue';
import store from '@/store/index'
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n'
import {getOrderList} from '@/api/order/index'
import {formatTime} from '@/api/format.js'
import { useCountDown } from '@vant/use'
export default {
    setup(){
        const {proxy} = getCurrentInstance()
        const { push } = useRouter();
        const { t } = useI18n()
        const active = ref(0)
        const page = ref(1)
    // dialog-related state removed (moved to detail page)
        const nowTime = ref(new Date().getTime())
        const currency = ref(store.state.baseInfo?.currency)
        
        store.dispatch('changefooCheck','order')
        
        const countTime = (start,end) => { 
            const countDown = useCountDown({
                // 倒计时 24 小时
                time: (end*1 - start*1),
            });
            // 开始倒计时
            countDown.start();
            const current = countDown.current
            return current
        }

        const status_data = reactive([
            {label: t('msg.all'),value: 0},
            {label: t('msg.dtj'),value: -1},
            {label: t('msg.ytj'),value: 1},
            {label: t('msg.djz'),value: 5},
        ])
        const status_list= reactive([
            // {label: t('msg.dsh'),value: -1},
            {label: t('msg.dtj'),value: 0},
            {label: t('msg.ytj'),value: 1},
            {label: t('msg.yhqx'),value: 2},
            {label: t('msg.qzwc'),value: 3},
            {label: t('msg.qzqx'),value: 4},
            {label: t('msg.djz1'),value: 5},
        ])
        const list = ref([]);
        const loading = ref(false)
        const toTei = () => {
            push('/tel')
        }

        // 点击跳转到详情页（详情页负责拉取 order_info 并展示 dialog 内容）
        const goDetail = (id) => {
            if (!id) return
            push({ name: 'detail', params: { id } })
        }

    const timeData = ref({})
    const time = ref(60000)
        
        const clickRight = () => {
            push('/message')
        }

        const initData = () => {
            const info = {
                status: status_data[active.value].value || '',
                page: page.value,
                size: 50
            }
            loading.value = true
            getOrderList(info).then(res => {
                console.log(info)
                loading.value = false
                if(res.code === 0) {
                    if (info.status == 5) {
                        list.value = res.data.list.filter(rr => {
                            if (rr.duorw > 0 && rr.time_limit < 1) {
                                return true
                            } else {
                                return rr.status == 5
                            }
                        })
                    } else {
                        list.value = Object.values(res.data.list).map(rr => {
                            if (rr.status == 5 && rr.is_pay === 1 && rr.duorw > 0) {
                                rr.status = 0
                            }
                            return rr
                        })
                    }
                }
            })
        }
        
        initData()
        // watch(fooCheck,(newValue)=>{
        //     console.log("新值是"+newValue);
        //     push('/'+newValue)
        // })
                return {
                        active,
                        initData,
                        status_data,
                        status_list,
                        list,
                        loading,
                        clickRight,
                        formatTime,
                        timeData,
                        currency,
                        time,
                        nowTime,
                        countTime,
                        toTei,
                        goDetail
            }
    }
}
</script>
<style lang="scss" scoped>
@import '@/styles/theme.scss';

.tj-btn{
	height: 82px;
	line-height: 82px;
}
.order{
    //padding: calc(var(--van-nav-bar-height) + 30px) 0 0;
    background-color: #f1f1f1;
    margin-top: 50px;
    margin-bottom: 100px;
    :deep(.van-nav-bar){
        background-color: #d4dff5;
    }
    :deep(.van-tab__panel){
        padding: 0 var(--van-padding-md);
    }
        :deep(.van-tabs){
        .van-tab--card:last-child{
            border-right: none;
        }
    }
  .van-submit-bar{
      bottom: 88px;
  }
  :deep(.van-button--danger){
    //   background: $theme;
    border-radius: 80px !important;
  }
  :deep(.van-submit-bar__price){
      color: $theme;
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
        .van-dialog__footer{
            margin-top: 40px;
            .van-dialog__confirm{
                color: $theme;
            }
        }
  }
}
  .colon {
    display: inline-block;
    margin: 0 4px;
    color: $theme;
  }
  .block {
    display: inline-block;
    width: 35px;
    color: #fff;
    font-size: 12px;
    text-align: center;
    background-color: $theme;
  }
.list{
    padding: 30px;
    box-shadow: $shadow;
    color: $subtext;
    text-align: left;
    margin-top: 20px;
    border-radius: 10px;
    background-color: #fff;
        .top{
        display: flex;
        justify-content: space-between;
        margin-bottom: 35px;
        font-size: 26px;
    }
    .cet{
        display: flex;
        background-color: #fafafa;
        padding: 10px 0;
        &.aaa{
            .img{
                width: auto;
                height: 150px;
                margin-right: 20px;
            }
        }
        .img{
            width: 100%;
            height: 180PX;
        }
        .text{
            color: #333;
            font-size: 24px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            .tab{
                display: inline-block;
                text-align: left;
                margin-top: 5px;
                .span {
                    padding: 2px 10px;
                    border: 1px solid red;
                    color: red;
                }
            }
            .tent{
                display: flex;
                justify-content: space-between;
                margin-top: 30px;
            }
        }
    }
    .monney{
        margin-top: 30px;
        .tent{
            display: flex;
            justify-content: space-between;
            font-size: 24px;
            margin-top: 15PX;
            color: #333;
            .span{
                // width: 120px;
                // text-align: justify;
                // text-justify: distribute-all-lines; 
                // text-align-last: justify; 
                color: #333;
            }
            .van-count-down{
                color: red;
            }
            .value{
                color: #999;
                :deep(.van-rate__icon) {
                    /* 通过 font-size 控制星星大小（也可设置具体图标宽高） */
                    font-size: 36px;        /* 调整整体尺寸：试 20/24/28 等 */
                }
            }
        }
    }
    .van-button{
        font-size: 32px;
        margin-top: 50px;
        border-radius: 0;
        padding: 0 15PX;
        height: auto;
    }
    
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