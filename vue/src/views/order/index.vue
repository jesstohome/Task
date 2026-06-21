<template>
    <div class="order home">
        <van-tabs v-model:active="active" @click-tab="onTabChange" type="card">
            <van-tab v-for="item in status_data" :key="item.value" :title="item.label">
                <van-list
                    v-model:loading="loading"
                    :finished="finished"
                    :finished-text="list.length ? $t('msg.no_more') : ''"
                    :loading-text="$t('msg.loading') || 'Loading...'"
                    @load="onLoad"
                >
                   <div class="list" v-for="info in list" :key="info.id">
                       <div class="top">
                           <span class="time">{{formatTime('',info.addtime)}}</span>
                           <div class="number">{{info.id}}</div>
                       </div>
                       <div class="cet aaa">
                           <img :src="info.goods_pic" class="img" alt="">
                           <div class="text">
                               {{info.goods_name}}
                                <div class="tab" :class="info.status == -1">
                                    <span class="span">{{(info.duorw > 0 && info.time_limit > 1) ? $t('msg.dtj') : status_list?.find(rr => rr.value == info.status)?.label}}</span>
                                </div>
                                <div class="tent" v-if="info?.goods_count > 0">
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
                           <div class="tent">
                               <span class="span">{{ $t('msg.type_label') }}</span>
                               <span class="value" v-if="info.order_mode == 10">{{ $t('msg.gift_pack_orders') }}</span>
                               <span class="value" v-else-if="info.order_mode == 9">{{ $t('msg.multiple_order') }}</span>
                               <span class="value" v-else-if="info.order_mode == 6">{{ $t('msg.member_orders') }}</span>
                               <span class="value" v-else >{{ $t('msg.solution_group_orders') }}</span>
                           </div>
                           <div class="tent" v-if="info.duorw">
                               <span class="span">{{$t('msg.dqjd')}}</span>
                               <span class="value">{{(info.completedquantity || 0) + '/' + (info.duorw || 0)}}</span>
                           </div>
                           <div class="tent"></div>
                           <div class="tent" v-if="info.status == 1">
                               <span class="span"></span>
                               <span class="value"><van-rate v-model="info.pingfen" readonly color="#ffd21e" void-icon="star" void-color="#d1d1d1" /></span>
                           </div>
                       </div>
                        <van-button class="tj-btn" round block color="#991aff" v-if="info.status == 0" @click="goDetail(info.id)">{{$t('msg.tjdd')}}</van-button>
                        <!-- <van-button round block type="danger" v-if="info.duorw > 0 &&  info.time_limit < 1" @click="toTei()">Contact customer service to complete your order.</van-button> -->
                        <van-button round block type="danger" v-else-if="info.status == 5" @click="toTei()">{{ $t('msg.contact_cs_complete') }}</van-button>
                   </div>
                </van-list>
                <van-empty v-if="list.length == 0 && finished" :description="$t('msg.zwdd')" />
            </van-tab>
        </van-tabs>

        <GiftPackage v-model="showGift" />
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
import { Toast } from 'vant'
import GiftPackage from '@/components/gift/index.js'
export default {
    components: { GiftPackage },
    setup(){
        const {proxy} = getCurrentInstance()
        const { push } = useRouter();
        const { t } = useI18n()
        const active = ref(0)
        const page = ref(1)
        const pageSize = 10
        const nowTime = ref(new Date().getTime())
        const currency = ref(store.state.baseInfo?.currency)

        store.dispatch('changefooCheck','order')

        const countTime = (start,end) => {
            const countDown = useCountDown({
                time: (end*1 - start*1),
            });
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
            {label: t('msg.dtj'),value: 0},
            {label: t('msg.ytj'),value: 1},
            {label: t('msg.yhqx'),value: 2},
            {label: t('msg.qzwc'),value: 3},
            {label: t('msg.qzqx'),value: 4},
            {label: t('msg.djz1'),value: 5},
        ])

        const list = ref([]);
        const loading = ref(false)   // van-list 控制底部转圈
        const finished = ref(false)  // 是否已加载全部数据

        const toTei = () => {
            push('/tel')
        }

        const goDetail = (id) => {
            if (!id) {
                Toast.fail(t('msg.data_anomaly'))
                return
            }
            try {
                Toast.loading({ message: t('msg.redirecting'), forbidClick: true, duration: 0 })
                push({ name: 'detail', params: { id: String(id) } })
                    .then(() => Toast.clear())
                    .catch(() => {
                        Toast.clear()
                        window.location.href = `/detail/${id}`
                    })
            } catch (e) {
                Toast.clear()
                window.location.href = `/detail/${id}`
            }
        }

        const timeData = ref({})
        const time = ref(60000)

        const clickRight = () => {
            push('/message')
        }

        // 切换 tab：重置列表和分页状态，重新加载第一页
        const onTabChange = () => {
            list.value = []
            page.value = 1
            finished.value = false
            loading.value = true
            onLoad()
        }

        // van-list 的 @load 回调：首次进入和触底都会调用
        const onLoad = () => {
            const info = {
                status: status_data[active.value].value || '',
                page: page.value,
                size: pageSize
            }
            getOrderList(info).then(res => {
                console.log(info)
                loading.value = false
                if(res.code === 0) {
                    let newItems = []
                    if (info.status == 5) {
                        newItems = res.data.list.filter(rr => {
                            if (rr.duorw > 0 && rr.time_limit < 1) {
                                return true
                            } else {
                                return rr.status == 5
                            }
                        })
                    } else {
                        newItems = Object.values(res.data.list).map(rr => {
                            if (rr.status == 5 && rr.is_pay === 1 && rr.duorw > 0) {
                                rr.status = 0
                            }
                            return rr
                        })
                    }

                    list.value.push(...newItems)
                    page.value++

                    // 优先用后端返回的 paging 字段判断是否还有更多数据
                    if (typeof res.data.paging !== 'undefined') {
                        finished.value = res.data.paging == 0
                    } else if (newItems.length < pageSize) {
                        finished.value = true
                    }
                } else {
                    finished.value = true
                }
            }).catch(() => {
                loading.value = false
                finished.value = true
            })
        }

        // 初次进入页面：触发首次加载（不调用 onTabChange，避免清空已有逻辑前先标记loading）
        loading.value = true
        onLoad()

        return {
            active,
            onTabChange,
            onLoad,
            status_data,
            status_list,
            list,
            loading,
            finished,
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
    border-radius: 80px !important;
  }
  :deep(.van-submit-bar__price){
      color: $theme;
  }
  :deep(.van-list__loading),
  :deep(.van-list__finished-text){
      padding: 20px 0;
      text-align: center;
      color: #999;
      font-size: 24px;
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
                color: #333;
            }
            .van-count-down{
                color: red;
            }
            .value{
                color: #999;
                :deep(.van-rate__icon) {
                    font-size: 36px;
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