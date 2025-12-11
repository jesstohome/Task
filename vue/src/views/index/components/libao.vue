<template>
  <div class="home">
    <van-nav-bar title="Web3" left-arrow @click-left="$router.go(-1)">
        <!-- <template #right>
            {{$t('msg.record')}}
        </template> -->
    </van-nav-bar>
    <div class="content">
        <div class="all_sy">
            <div class="moneya">
                    <span class="text">{{$t('msg.zongzichan')}} ({{currency}}) 
                        <span class="texta"><img :src="require('@/assets/images/safe.png')" class="logo-head" height="20" alt="">{{ $t('msg.jijinanquan') }}</span>
                    </span>
                    <span class="value"> {{info?.balance}}</span>
                   
            </div>
            <div class="top">
                <!-- <div class="money">
                    <span class="text">{{$t('msg.zongzichan')}}</span>
                    <span class="value">{{currency}} {{info?.ubalance}}</span>
                </div> -->
                
                <div class="money">
                    <span class="text">{{$t('msg.zsy')}}</span>
                    ({{currency}})
                    <span class="value"> {{info.balance_shouru}}</span>
                </div>
                <div class="money">
                    <span class="text">{{$t('msg.zrsy')}}</span>
                    ({{currency}})
                    <span class="valuea"> {{info.yes_shouyi}}</span>
                </div>
            </div>
            <div class="bottom">
                <!-- <div class="yezr">{{$t('msg.yezr')}}</div> -->
                <div class="licai">
                    <div class="licaia">{{$t('msg.lccp')}}：</div>
                    <van-cell is-link @click="showlicai">{{lcname + $t('msg.day') }}</van-cell>
                </div>              
                <van-field
                    v-model="zr_money"
                    clearable
                    :placeholder="$t('msg.input_zr_money')"
                >
                    <template #left-icon>
                        {{currency}} 
                    </template>
                </van-field>
                <div class="yjsy">
                    <div class="l">
                        {{$t('msg.yjsy')}}
                    </div>
                    <div class="r">{{currency}} {{zr_money*lx_bili}}</div>
                </div>
                <div class="buttons">
                    <van-button block type="primary"  @click="onSubmit">
                        <span class="span">{{$t('msg.zr')}}</span>
                    </van-button>
                </div>
            </div>

        </div>
        <van-popup v-model:show="showlicaistate" position="bottom" round :style="{ height: '60%' }" >
        
        <div class="list">
            <div class="li" v-for="item in info?.lixibao || []" :key="item.id" :class="lcid == item.id && 'check'" @click="lcid = item.id;lx_bili=item.bili*1;showlicaistate=false;lcname=item.day;">
                <div class="tent">
                    <div class="l">
                        {{item.day + $t('msg.day')}}
                    </div>
                    <div class="r">
                        <p>{{$t('msg.get_m')}} + {{item.bili*100 + '%'}}</p>
                        <p class="ll">{{$t('msg.sxf')}} {{item.shouxu*100}}%</p>
                        <p class="ll">{{$t('msg.min')}} {{currency}} {{item.min_num}}</p>
                        <p class="ll">{{$t('msg.max')}} {{currency}} {{item.max_num || 0}}</p>
                        <!-- <p class="ll">Free {{$t('msg.djxz')}}</p> -->
                    </div>
                </div>
            </div>
        </div>
        </van-popup>

        <div class="yezr">{{$t('msg.record')}}：</div>

        <!-- 来自 libao_jl.vue 的记录列表 -->
        <van-list
            v-model:loading="loading"
            :finished="finished"
            :finished-text="$t('msg.not_move')"
            @load="getCW"
            >
                <div class="address" v-for="(item,index) in list" :key="index">
                    <div class="l">
                        <div class="time">{{$t('msg.zr') + 'Web3 ' + item.day + $t('msg.day') + item.bili*100 + '%'}}</div>
                        <div class="tag">{{item.num}}</div>
                    </div>
                    <div class="c">
                        <div class="time">{{$t('msg.crsj')}}：{{formatTime('',item.addtime)}}</div>
                        <div class="tag">{{$t('msg.zr') + 'Web3 '}}</div>
                    </div>
                    <div class="ra" v-if="!item.is_qu">
                       {{$t('msg.shouyizhong')}}
                    </div>
                    <div class="r" v-if="!item.is_qu" @click="qu_money(item)">
                       {{$t('msg.out_money')}}
                    </div>
                    <div class="rc" v-else>
                       {{$t('msg.ywc')}}
                    </div>
                </div>
        </van-list>
        
    </div>
  </div>
</template>

<script>
import { ref,getCurrentInstance } from 'vue';
import store from '@/store/index'
import {get_lixibao,lixibao_ru,lixibao_chu,get_lixibao_chu} from '@/api/home/index.js'
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n'
import {formatTime} from '@/api/format.js'
export default {
  name: 'address',
  setup() {
    const { t } = useI18n()
    const { push } = useRouter();
    const {proxy} = getCurrentInstance()
    const zr_money = ref('')
    const yjsy_money = ref(0)
    const lcid = ref(1)
    const lcname = ref(1)
    const lx_bili = ref(0.03)
    const info = ref({})
    const currency = ref(store.state.baseInfo?.currency)

    // === 来自 libao_jl.vue 的列表相关 state & 方法 ===
    const list = ref([])
    const page = ref(1)
    const type = ref('all')

    const loading = ref(true);
    const finished = ref(false);

    const tabs = ref([
        {label: t('msg.xdcg'),value: 1},
        {label: t('msg.czcg'),value: 2},
        {label: t('msg.czsb'),value: 3},
    ])

    const getCW = () => {
        let json = {
            page: 1,
            size: 10,
        }
        lixibao_chu(json).then(res => {
            if(res.code === 0) {
                finished.value = !res.data?.paging
                list.value = res.data?.list
            }
        })
    }
    // 立刻加载一次（van-list 也会在滚动触发时调用）
    getCW()

    const qu_money = (row) => {
        proxy.$dialog.confirm({
            title: t('msg.wxts'),
            message: t('msg.sure_qc'),
            confirmButtonText:t('msg.queren'),
            cancelButtonText:t('msg.quxiao')
        })
        .then(() => {
            get_lixibao_chu({id:row.id}).then(res => {
                if(res.code === 0) {
                    proxy.$Message({ type: 'success', message:res.info});
                    get_licai();
                    getCW();
                } else {
                    proxy.$Message({ type: 'error', message:res.info});
                }
            }).catch(rr => {
                console.log(rr)
            })
        })
        .catch(() => {
            // on cancel
        });
    }

    // === end 列表相关 ===

    const showlicaistate = ref(false);
    const showlicai = () => {
      showlicaistate.value = true;
    };
    const get_licai = () => {
      get_lixibao().then(res => {
            console.log(res)
            info.value = {...(res.data || {})}
        })
    };
    get_licai();

    const clickLeft = () => {
        push('/self')
    }
    const clickRight = () => {
        push('/libao_jl')
    }
    const onSubmit = async () => {
        let json = {
            price: zr_money.value,
            lcid: lcid.value
        }
        
        proxy.$dialog.confirm({
            title: t('msg.wxts'),
            message: t('msg.sure_zr'),
            confirmButtonText:t('msg.queren'),
            cancelButtonText:t('msg.quxiao')
        })
        .then(() => {
            lixibao_ru(json).then(res => {
                if(res.code === 0) {
                    proxy.$Message({ type: 'success', message:res.info});
                    zr_money.value = 0
                    get_licai();
                    getCW();
                } else {
                    proxy.$Message({ type: 'error', message:res.info});
                }
            }).catch(rr => {
                console.log(rr)
            })
        })
        .catch(() => {
            // on cancel
        });
    };



    return {
        onSubmit,
        clickLeft,
        clickRight,
        zr_money,
        info,
        yjsy_money,
        lcid,
        lx_bili,
        currency,
        showlicaistate,
        showlicai,
        lcname,
        // 列表相关导出给模板
        list,
        loading,
        finished,
        getCW,
        qu_money,
        formatTime
    };
  }
}
</script>

<style scoped lang="scss">
@import '@/styles/theme.scss';
.home{
    overflow: auto;
    background: linear-gradient(to bottom, #2e64eb 10%, #f6f7f9 40%);
    padding-top: 100px;
    :deep(.van-nav-bar){
        background-color: #2e64eb;
        color: #fff;
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        .van-nav-bar__left{
                .van-icon{
                    color: #fff;
                    font-size: 30px;
                }
            }
            .van-nav-bar__content{
                height: 60px;
            }
            .van-nav-bar__title{
                color: #ffffff;
                font-weight: 600;
                font-size: 32px;
                line-height: 60px;
            }
        .van-nav-bar__right{
            img{
                height: 42px;
            }
        }
    }
    :deep(.van-cell--clickable) {
        .van-cell__right-icon{
            color: #ff5e0e;
            font-size: 32px;
            line-height: 40px;
        }
    }
    :deep(.van-cell__value--alone){
        font-size: 32px;
        line-height: 40px;
    }
    .content{
        padding: 0 30px;
        position: relative;
        margin: 50px 15px;
        .yezr{
            text-align: left;
            color: #333;
            margin-top: 50px;
            margin-bottom: 50px;
            font-size: 30px;
        }
        .licai{
            margin-top: 20px;
            margin-bottom: 10px;
            color: #333;
            display: flex;
            font-size: 30px;
            .licaia{
                width: 50%;
                text-align: left;
            }
        }
        .all_sy{
            width: 100%;
            border-radius: 30px;
            padding: 30px;
            background-color: #fff;
            .top{
                padding: 24px 0;
                display: flex;
                justify-content: flex-start;
                .money{
                    text-align: center;
                    display: flex;
                    flex-direction: column;
                    font-size: 32px;
                    width: 50%;
                    text-align: left;
                    .text{
                        color: #333;
                    }
                    .value{
                        margin-top: 20px;
                        color: red;
                        font-weight: 900;
                    }
                    .valuea{
                        margin-top: 20px;
                        color: rgb(0, 0, 0);
                        font-weight: 900;
                    }
                }
            }
            .moneya{
                    text-align: left;
                    .text{
                        font-size: 28px;
                        color: #333;
                        margin-bottom: 20px;
                        .texta{
                            font-size: 28px;
                            color: #ff5e0e;
                            margin-left: 30px;
                            vertical-align: middle;
                            flex-direction: row;
                            align-items: center;
                            display: inline-flex;
                            border: 1px solid #ff5e0e;
                            border-radius: 5px;
                            .logo-head{
                                width: 40px;
                                height: 40px;
                            }
                        }
                    }
                    .value{
                        font-size: 104px;
                        display: block;
                        font-weight: 900;
                        color: rgb(0, 0, 0);
                    }
                }
            :deep(.bottom){
                .van-field{
                    margin-top: 20px;
                    padding: 20px 0;
                    border-bottom: 1px solid #ccc;
                    background-color: initial;
                    .van-field__left-icon{
                        margin-right: 30px;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        font-size: 30px;
                    }
                    .van-field__control{
                        font-size: 24px;
                    }
                }
                .yjsy{
                    display: flex;
                    margin-top: 40px;
                    .l{
                        font-size: 22px;
                        color: #333;
                        margin-right: 20px;
                    }
                    .r{
                        font-size: 24px;
                        color: red;
                    }
                }
            }
        }
        .list{
            width: 80%;
            margin: 0 auto;
            .li{
                width: 100%;
                display: flex;
                flex-direction: column;
                justify-content: center;
                background-color: #fff;
                margin-top: 20px;
                border-radius: 10px;
                color: #333;
                border: 1px solid #ccc;
                padding: 15px 0;
                &.check{
                    // box-shadow: 0 0 20px 0 $theme;
                    color: #fff;
                    background-color: #2c7ce7;
                }
                .tent{
                    display: flex;
                    justify-content: space-between;
                    padding: 0 58px;
                    .l{
                        font-size: 38px;
                        margin-right: 58px;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        .img{
                            height: 60px;
                            vertical-align: middle;
                        }
                    }
                    .r{
                        font-size: 24px;
                        flex: 1;
                        text-align: left;
                        .ll{
                            &:nth-child(2){
                                margin-top: 15px;
                            }
                        }
                    }
                }
            }
        }
        .buttons{
            padding: 60px 30px;
            .van-button{
                margin: 10px 0;
            }
        }
        /* 来自 libao_jl.vue 的 van-list 样式 */
        :deep(.van-list){
            .van-loading{
                background: initial;
                color: #666;
                .van-loading__text{
                    color: #666;
                }
            }
            .address{
                box-shadow: $shadow;
                border-radius: 12px;
                padding: 30px 30px 120px;
                margin: 0 0px 40px;
                text-align: left;
                background-color: #fff;
                &.mb30{
                    padding-bottom: 30px;
                }
                .l{
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 30px;
                    .time{
                        font-size: 30px;
                        font-weight: 600;
                        color: #333;
                    }
                    .tag{
                        font-size: 28px;
                        font-weight: 600;
                        color: $theme;
                    }
                }
                .c{
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 30px;
                    .time{
                        font-size: 22px;
                        font-weight: 600;
                        color: #999;
                    }
                    .tag{
                        font-size: 22px;
                        font-weight: 600;
                        color: #333;
                    }
                }
                .ra{
                    width: 200px;
                    height: 60px;
                    text-align: center;
                    line-height: 60px;
                    border: 1px solid #ff5e0e;
                    color: #ff5e0e;
                    border-radius: 10px;
                    float: left;
                    font-size: 25px;
                }
                .r{
                    width: 200px;
                    height: 60px;
                    text-align: center;
                    line-height: 60px;
                    background-color: $theme;
                    color: #fff;
                    border-radius: 10px;
                    float: right;
                    font-size: 25px;
                }
                .rc{
                    width: 200px;
                    height: 60px;
                    text-align: center;
                    line-height: 60px;
                    background-color: #2cade7;
                    color: #fff;
                    border-radius: 10px;
                    float: right;
                    font-size: 25px;
                }
            }
        }
    }
}
</style>
