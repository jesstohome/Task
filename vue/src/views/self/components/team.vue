<template>
    <div class="self home">
        <van-nav-bar :title="$t('msg.tdbg')" left-arrow @click-left="$router.go(-1)"></van-nav-bar>
        <div class="top">
            <div class="info">
                <div class="avaitar">
                    <div class="top_s">
                        <span class="span" :class="topcheck == 0 && 'check'" @click="topClick(0)">{{$t('msg.all')}}</span>
                        <span class="span" :class="topcheck == 1 && 'check'" @click="topClick(1)">{{$t('msg.today')}}</span>
                        <span class="span" :class="topcheck == 2 && 'check'" @click="topClick(2)">{{$t('msg.prev_day')}}</span>
                        <span class="span" :class="topcheck == 3 && 'check'" @click="topClick(3)">{{$t('msg.week')}}</span>
                    </div>
                    <div class="check_rili" @click="function(){if(topcheck == 0){showCalendar = true}}">
                        <img :src="require('@/assets/images/self/team.png')" class="img" alt="">
                        <div class="r" v-if="time1">
                            {{time1}}
                        </div>
                        <div class="r" v-else>{{$t('msg.xzsj')}}</div>
                    </div>
                    <van-calendar v-model:show="showCalendar" type="range" @confirm="onConfirm" color="#6833ff" :readonly="topcheck!=0" :min-date="new Date(2010, 0, 1)"/>
                </div>
            </div>
        </div>
        <div class="list">
            <div class="box">
                <div class="title">
                    <span class="l">{{$t('msg.sysj')}}</span>
                    <span class="r">{{currency + info.team_rebate}}</span>
                </div>
                <div class="address">
                    <div class="text">{{$t('msg.tdsl')}} <span class="span">{{info.team_count}}</span></div>
                    <div class="text">{{$t('msg.tdddyj')}} <span class="span">{{info.team_yj}}</span></div>
                </div>
            </div>
            <div class="box">
                <div class="title">
                    <span class="l">{{$t('msg.oneLevel')}}</span>
                    <span class="r">{{currency + info.team1_rebate}}</span>
                </div>
                <div class="address">
                    <div class="text">{{$t('msg.tdsl')}} <span class="span">{{info.team1_count}}</span></div>
                    <div class="text">{{$t('msg.tdddyj')}} <span class="span">{{info.team1_yj}}</span></div>
                </div>
            </div>
            <div class="box">
                <div class="title">
                    <span class="l">{{$t('msg.twoLevel')}}</span>
                    <span class="r">{{currency + info.team2_rebate}}</span>
                </div>
                <div class="address">
                    <div class="text">{{$t('msg.tdsl')}} <span class="span">{{info.team2_count}}</span></div>
                    <div class="text">{{$t('msg.tdddyj')}} <span class="span">{{info.team2_yj}}</span></div>
                </div>
            </div>
            <div class="box">
                <div class="title">
                    <span class="l">{{$t('msg.threeLevel')}}</span>
                    <span class="r">{{currency + info.team3_rebate}}</span>
                </div>
                <div class="address">
                    <div class="text">{{$t('msg.tdsl')}} <span class="span">{{info.team3_count}}</span></div>
                    <div class="text">{{$t('msg.tdddyj')}} <span class="span">{{info.team3_yj}}</span></div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { ref} from 'vue';
import {junior} from '@/api/self/index'
import { useRouter } from 'vue-router';
import store from '@/store/index'
export default {
    setup(){
        const { push } = useRouter();
        const currency = ref(store.state.baseInfo?.currency)
        const topcheck = ref(0)
        const imgCheck = ref(1)
        const showCalendar = ref(false)
        const time1 = ref('')
        const info = ref({})
        const start = ref('')
        const end = ref('')

        const getjunior = () => {
            let json = {
                ajax: 1,
                start: start.value,
                end: end.value
            }
            junior(json).then(res => {
                info.value = {...(res || {})}
            })
        }
        getjunior()
        const clickLeft = () => {
            push('/self')
        }
        
        const formatDate = (date,num) => `${date.getMonth() + 1}-${(date.getDate() - (num || 0))}`;
        const onConfirm = (values) => {
            const [s, e] = values;
            start.value = formatDate(s)
            end.value = formatDate(e)
            console.log(start.value)
            showCalendar.value = false;
            time1.value = `${formatDate(s)} — ${formatDate(e)}`;
            getjunior()
        }
        const topClick = (val) => {
            topcheck.value = val
            switch (val) {
                case 0:
                    time1.value =  ''
                    start.value = ''
                    end.value = ''
                    break;
                case 1:
                    let t = new Date()
                    time1.value = `${formatDate(t)}`
                    start.value = time1.value 
                    end.value = time1.value 
                    break;
                case 2:
                    let b = new Date()
                    time1.value = `${formatDate(b,1)}`
                    start.value = time1.value 
                    end.value = time1.value 
                    break;
                case 3:
                    let c = new Date()
                    time1.value = `${formatDate(c,7)} — ${formatDate(c)}`;
                    start.value = formatDate(c,7)
                    end.value = formatDate(c)
                    break;
            
                default:
                    break;
            }
            getjunior()
        }
        return {info,imgCheck,clickLeft,topcheck,showCalendar,time1,onConfirm,topClick,currency}
    }
}
</script>
<style lang="scss" scoped>
.self{
    overflow: auto;
    :deep(.van-nav-bar){
        background-color: $theme;
        position: sticky;
        top: 0;
        left: 0;
        color: #fff;
        width: 100%;
        .van-nav-bar__left{
            .van-icon{
                color: #fff;
            }
        }
        .van-nav-bar__title{
            color: #fff;
        }
        .van-nav-bar__right{
            img{
                height: 42px;
            }
        }
        &::after{
            display: none;
        }
    }
    .top{
        padding: 0 50px 100px;
        background-color: $theme;
        color: #fff;
        position: relative;
        .info{
            .avaitar{
                // display: flex;
                // height: 155px;
                margin-bottom: 25px;
                text-align: center;
                .top_s{
                    display: inline-block;
                    border-radius: 20px;
                    border: 2px solid #fff;
                    color: #fff;
                    .span{
                        display: inline-block;
                        padding: 14px 50px;
                        font-size: 30px;
                        font-weight: 600;
                        border-radius: 20px;
                        &.check{
                            background-color: #fff;
                            color: $theme;
                        }
                    }
                }
                .check_rili{
                    display: flex;
                    margin-top: 25px;
                    font-size: 28px;
                    font-weight: 600;
                    letter-spacing: 4px;
                    justify-content: center;
                    .img{
                        height: 35px;
                        margin-right: 30px;
                    }
                }
            }
            :deep(.van-calendar){
                color: #333;
            }
        }
    }
    .list{
        position: relative;
        background-color: #fff;
        text-align: left;
        // overflow: hidden;
        margin-top: -85px;
        display: flex;
        flex-wrap: wrap;
        .box{
            width: 100%;
            .title{
                margin: 30px;
                display: flex;
                justify-content: space-between;
                padding: 0 30px;
                font-size: 26px;
                border-left: 10px solid $theme;
                .r{
                    color: $theme;
                    font-weight: 600;
                }
            }
        }
        .address{
            box-shadow: $shadow;
            border-radius: 12px;
            padding: 30px;
            margin: 0 30px 40px;
            background-image: url('~@/assets/images/self/address/bg.png');
            background-size: 100% 100%;
            text-align: left;
            .text{
                display: flex;
                justify-content: space-between;
                font-size: 30px;
                font-weight: 600;
                    &:first-child{
                        margin-bottom: 40px;
                    }
                .span{
                    color: $theme;
                    font-weight: 600;
                    font-size: 26px;
                }
            }
        }
    }
}
</style>