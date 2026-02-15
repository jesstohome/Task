<template>
	<div class="order-detail home">
		<van-nav-bar :title="$t('msg.ddxq')" left-arrow @click-left="$router.go(-1)"></van-nav-bar>

		<div class="content">
			<div class="list" v-if="onceinfo.data?.group_rule_num == 0 || (!onceinfo.data && !onceinfo.duorw) || onceinfo.data?.duorw == 0">
				<div class="cet">
					<img :src="onceinfo.data?.goods_pic" class="img" alt="">
				</div>
				<div class="monney">
					
					
					<!-- <div class="tent">
						<span class="span">{{$t('msg.spsl')}}</span>
						<span class="value">{{'x ' + onceinfo.data?.goods_count}}</span>
					</div> -->
					
                    <div class="tent">
							<!-- <span class="span">{{$t('msg.shangpinmingcheng')}}</span> -->
							<span class="value">{{onceinfo.data?.goods_name}}</span>
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
						<!-- <div class="tent">
							<span class="span">{{$t('msg.spdj')}}</span>
							<span class="value">{{currency+item?.goods_price}}</span>
						</div> -->
						<!-- <div class="tent">
							<span class="span">{{$t('msg.spsl')}}</span>
							<span class="value">{{'x ' + item?.goods_count}}</span>
						</div> -->
						<!-- <div class="tent">
							<span class="span">{{$t('msg.order_Num')}}</span>
							<span class="value">{{currency+item?.num}}</span>
						</div> -->
                        <div class="tent">
							<!-- <span class="span">{{$t('msg.shangpinmingcheng')}}</span> -->
							<span class="value">{{currency+item?.goods_name}}</span>
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
					<div>{{ $t('msg.dianjifabiaopinglun') }}</div>
					<div>
						<van-rate v-model="pinglun" color="#ffd21e" void-icon="star" void-color="#d1d1d1" />
					</div>
				</div>
				<div class="pinglunb">
					<van-cell-group inset>
						<van-field v-model="pingluntext" rows="3" type="textarea" :placeholder="$t('msg.xiepinglun')" :required="true" :center="true">
							<template #button>
								<van-button @click="generateRandomComment" size="mini" color="#ff9800">{{ $t('msg.zidongpinglun') }}</van-button>
							</template>
						</van-field>
					</van-cell-group>
				</div>
			</div>
            <div class="vipinfo">
                <img v-if="level" :src="require('@/assets/images/self/vip'+ level +'.png')" class="vip" alt="">
                <div> VIP {{ level }}</div>
            </div>
            <div class="ordernum">
                    <div class="tent">
						<span class="span">{{$t('msg.order_Num')}}</span>
						<span class="value">{{onceinfo.data?.num}} {{ currency }}</span>
					</div>
                    <div class="tent">
						<span class="span">{{$t('msg.bili')}}</span>
						<span class="value">{{uinfo.level_bili}}</span>
					</div>
                    <div class="tent">
						<span class="span">{{$t('msg.yonj')}}</span>
						<span class="value">{{onceinfo.data?.commission}} {{ currency }}</span>
					</div>
            </div>
            <div class="orderinfo">
                    <!-- <div class="tent">
						<span class="span">{{$t('msg.spdj')}}</span>
						<span class="value">{{currency+onceinfo.data?.goods_price}}</span>
					</div> -->
                    <div class="tent">
						<span class="span">{{$t('msg.xdsj')}}</span>
						<span class="value">{{formatTime('',onceinfo.data?.addtime)}}</span>
					</div>
                    <div class="tent">
						<span class="span">{{$t('msg.order_Num')}}</span>
						<span class="value">{{currency+onceinfo.data?.num}}</span>
					</div>
            </div>

			<div style="padding: 20px">
			<van-button
				block
				type="primary"
				@click="confirmPwd"
				:loading="confirmLoading"
				:disabled="confirmLoading"
			>
				{{$t('msg.tjdd')}}
			</van-button>
			<van-button
				v-if="onceinfo.data?.isluck === 1"
				block
				type="danger"
				style="margin-top: 20px; background-color: red;"
				@click="cancelLuckyOrder"
				:loading="cancelLoading"
				:disabled="cancelLoading"
			>
				{{$t('msg.qxxyd')}}
			</van-button>
			</div>
		</div>
	</div>
</template>

<script>
import { ref, getCurrentInstance, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import store from '@/store/index'
import { order_info, do_order, submit_order } from '@/api/order/index'
import { formatTime } from '@/api/format.js'
import { Toast } from 'vant'

export default {
	setup() {
		const { proxy } = getCurrentInstance()
		const route = useRoute()
		const { push } = useRouter()
		const { t } = useI18n()

		const id = route.params.id || route.query.id
		const onceinfo = ref({})
		const currency = ref(store.state.baseInfo?.currency)
        const uinfo = ref(store.state.objInfo)
		const pinglun = ref(0)
		const pingluntext = ref('')
        const level = ref(store.state.minfo?.level || 0)

        // loading state for buttons to prevent multiple clicks
        const confirmLoading = ref(false)
        const cancelLoading = ref(false)

		const fetchDetail = () => {
			if (!id) return
			order_info({ id }).then(res => {
				onceinfo.value = { ...res }
			}).catch(err => {
				console.error(err)
			})
		}

		const confirmPwd = () => {
			if (confirmLoading.value) return
			confirmLoading.value = true
			let oid = ''
			if (onceinfo.value.group_data && onceinfo.value.group_data.length > 0) {
				let info = onceinfo.value.group_data?.find(rr => rr.is_pay === 0)
				oid = info?.oid
			} else {
				oid = onceinfo.value?.id
			}
			let json = {
				oid: oid,
				status: 1,
				pingfen: pinglun.value,
				pinglun: pingluntext.value
			}
			do_order(json)
				.then(res => {
					if (res.code === 0) {
						const group_data = onceinfo.value.group_data || []
						if ((!onceinfo.value.data || onceinfo.value.data.duorw === 0)) {
							proxy.$Message({ type: 'success', message: res.info })
							push({ name: 'obj' })
						} else if (group_data.length == onceinfo.value.data.duorw) {
							proxy.$Message({ type: 'success', message: res.info })
							push({ name: 'obj' })
						} else {
							submit_order().then(() => {
								Toast.success(t('msg.tjcg'))
								push({ name: 'obj' })
							})
						}
					} else {
						proxy.$Message({ type: 'error', message: res.info })
					}
				})
				.catch(err => {
					console.error(err)
				})
				.finally(() => {
					confirmLoading.value = false
				})
		}
		const cancelLuckyOrder = () => {
			if (cancelLoading.value) return
			cancelLoading.value = true
			let oid = ''
			if (onceinfo.value.group_data && onceinfo.value.group_data.length > 0) {
				let info = onceinfo.value.group_data?.find(rr => rr.is_pay === 0)
				oid = info?.oid
			} else {
				oid = onceinfo.value?.id
			}
			let json = {
				oid: oid,
				status: 2,
				pingfen: pinglun.value,
				pinglun: pingluntext.value
			}
			do_order(json)
				.then(res => {
					if (res.code === 0) {
						const group_data = onceinfo.value.group_data || []
						if ((!onceinfo.value.data || onceinfo.value.data.duorw === 0)) {
							proxy.$Message({ type: 'success', message: res.info })
							push({ name: 'obj' })
						} else if (group_data.length == onceinfo.value.data.duorw) {
							proxy.$Message({ type: 'success', message: res.info })
							push({ name: 'obj' })
						} else {
							submit_order().then(() => {
								Toast.success(t('msg.tjcg'))
								push({ name: 'obj' })
							})
						}
					} else {
						proxy.$Message({ type: 'error', message: res.info })
					}
				})
				.catch(err => {
					console.error(err)
				})
				.finally(() => {
					cancelLoading.value = false
				})
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
				"Great product with excellent craftsmanship."
			]
			const randomIndex = Math.floor(Math.random() * comments.length)
			pingluntext.value = comments[randomIndex]
            pinglun.value = 5
		}

		onMounted(() => {
			fetchDetail()
		})

		return {
			onceinfo,
			formatTime,
			currency,
			pinglun,
			pingluntext,
			generateRandomComment,
			confirmPwd,
			cancelLuckyOrder,
            level,
            uinfo,
            confirmLoading,
            cancelLoading
		}
	}
}
</script>

<style lang="scss" scoped>
@import '@/styles/theme.scss';
.content{
    background-color: #f3f3f3;
    font-size: 30px;
    font-weight: 900;
}
:deep(.van-nav-bar){
        background-color: #000000;
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
.list{
		padding: 0 40px;
		box-shadow: none;
		max-height: 30vh;
		overflow: auto;
		display: flex;
		flex-direction: column;
        margin-top: 100px;
        .cet{
            display: flex;
            justify-content: center;
            .img{
                height: 400px;
                border-radius: 10px;
                margin: 20px 0;
            }
        }
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
.vipinfo{
        display: flex;
        justify-content: flex-start;
        margin: 20px;
        background-color: #ffe69c;
        border-radius: 10px;
        line-height: 100px;
        align-items: center;
        font-size: 30px;
        font-weight: 900;
        .vip{
            height: 65px;
            margin: 0 20px;
        }
}
.ordernum{
        padding: 20px;
        border-radius: 10px;
        margin: 0 20px 20px 20px;
        background-color: #fff;
        display: flex;
        justify-content: space-between;
        .tent{
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            margin-bottom: 10px;
            gap: 10px;
            .span{
                font-size: 28px;
                color: #333;
            }
            .value{
                font-size: 28px;
                color: #666;
            }
        }
}
.orderinfo{
        padding: 20px;
        border-radius: 10px;
        margin: 0 20px 20px 20px;
        background-color: #fff;
        .tent{
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            .span{
                font-size: 28px;
                color: #333;
            }
            .value{
                font-size: 28px;
                color: #666;
            }
        }
}
.pinglun{
		margin: 20px 20px;
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
        .pingluna :deep(.van-rate__icon) {
            /* 通过 font-size 控制星星大小（也可设置具体图标宽高） */
            font-size: 36px;        /* 调整整体尺寸：试 20/24/28 等 */
        }
		.pinglunb{
				margin-top: 20px;
				width: 98%;
                padding: 10px;
		border: 1px solid #dadada;
		border-radius: 5px;
        background-color: #fff;
        font-size: 30px;
        :deep(.van-cell__title) {
            font-size: 30px;    /* 例如 28/30 按需调整 */
            color: #333;
            line-height: 50px;
        }

            /* 调整 cell 值（右侧）文字大小 */
        :deep(.van-cell__value) {
            font-size: 28px;
            color: #666;
            line-height: 50px;
            }
        :deep(.van-button__content){
            height: 30px;
        }
        :deep(.van-button--mini){
            font-size: 20px;
            height: 30px;
        }
		}
}
</style>