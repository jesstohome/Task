<template>
  <div class="home">
    <van-nav-bar
      :title="$t('msg.tkxx')"
      left-arrow
      @click-left="$router.go(-1)"
    >
    </van-nav-bar>
    <div class="box_bank"  v-if="info && Object.keys(info).length > 0" v-for="(item,index) in info" :key="index">
	  <!-- Bank 模式显示 -->
	  <template v-if="item.bank_type == 'Bank'">
		  <div class="li">
	        <span class="span">{{ $t("msg.txlx") }}：</span>
	        <span class="span">Bank</span>
	      </div>
		  <!-- <div class="li">
	        <span class="span">{{ $t("msg.aba") }}：</span>
	        <span class="span">{{ item.bank_branch }}</span>
	      </div> -->
	      <div class="li">
	        <span class="span">{{ $t("msg.yhmc") }}：</span>
	        <span class="span">{{ item.bankname }}</span>
	      </div>
	      <!-- <div class="li">
	        <span class="span">{{ $t("msg.yhdz") }}：</span>
	        <span class="span">{{ item.bank_address }}</span>
	      </div> -->
	      <div class="li">
	        <span class="span">{{ $t("msg.yhkh") }}：</span>
	        <span class="span">{{ item.cardnum ? item.cardnum.slice(0, 3) + '********' + item.cardnum.slice(-4) : '' }}</span>
	      </div>
	      <!-- <div class="li">
	        <span class="span">{{ $t("msg.khxm") }}：</span>
	        <span class="span">{{ item.name }}</span>
	      </div> -->
	      <!-- <div class="li" v-if="item.swift_bic">
	        <span class="span">{{ $t("msg.swift_bic") }}：</span>
	        <span class="span">{{ item.swift_bic }}</span>
	      </div> -->
	  </template>
	  
	  <!-- USDT 模式显示 -->
	  <template v-else-if="item.bank_type == 'USDT'">
	      <div class="li">
	        <span class="span">{{ $t("msg.txlx") }}：</span>
	        <span class="span">USDT</span>
	      </div>
	      <div class="li">
	        <span class="span">{{ $t("msg.usdt_type") }}：</span>
	        <span class="span">{{ item.usdt_type }}</span>
	      </div>
	      <div class="li">
	        <span class="span">{{ $t("msg.usdt_address") }}：</span>
	        <span class="span">{{ item.usdt_diz }}</span>
	      </div>
	  </template>
	  
	  <!-- 旧的显示逻辑（向后兼容） -->
	  <!-- <div class="li" v-if="py_status !== 2 && item.bank_type">
        <span class="span">{{ $t("msg.khlx") }}：</span>
        <span class="span">{{ item.bank_type }}</span>
      </div>
      <div class="li" v-if="py_status !== 2 && item.username">
        <span class="span">{{ $t("msg.khxm") }}：</span>
        <span class="span">{{ item.username }}</span>
      </div>
      <div class="li" v-if="py_status !== 2 && item.cardnum">
        <span class="span">{{ $t("msg.yhkh") }}：</span>
        <span class="span">{{ item.cardnum.slice(0, 3) + '********' +  item.cardnum.slice(-4)}}</span>
      </div>
      <div class="li" v-if="py_status !== 2 && item.tel">
        <span class="span">{{ $t("msg.ylsjh") }}：</span>
        <span class="span">{{ item.tel.slice(0, 3) + '****' +  item.tel.slice(-4) }}</span>
      </div>
      <div class="li" v-if="py_status == 2 && item.usdt_type">
        <span class="span">{{ $t("msg.usdt_type") }}：</span>
        <span class="span">{{ item.usdt_type }}</span>
      </div>
      <div class="li" v-if="py_status == 2 && item.usdt_diz">
        <span class="span">{{ $t("msg.usdt_address") }}：</span>
        <span class="span">{{ item.usdt_diz }}</span>
      </div> -->
      <van-button round  v-if="edit_card_switch == true" type="primary" @click="editShowDialog(index)">
        {{ $t("msg.edit") }}
      </van-button>
      <van-button round  v-if="del_card_switch == true" type="primary" @click="del_bank(item.id)" style="margin-left: 5px;background-color: #f78989;">
        {{ $t("msg.del") }}
      </van-button>
    </div>
    <div class="not_box_bank">
      <van-empty v-if="Object.keys(info).length < 1" :description="$t('msg.not_data')" />
      <van-button round block type="primary" v-if="Object.keys(info).length < 3" class="not" @click="showDialog()">
        {{ $t("msg.add") }}
      </van-button>
    </div>
    <!-- 选择银行下拉框 (原 van-picker 已注释，替换为可在 PC 上滚动的列表) -->
    <!--
    <van-popup v-model:show="showHank" position="bottom">
      <van-picker
        :columns="bank_list"
        @confirm="onConfirm"
        @cancel="showHank = false"
        :model="edit_data.bankname"
        :confirm-button-text="$t('msg.yes')"
        :cancel-button-text="$t('msg.quxiao')"
      />
    </van-popup>
    <van-popup v-model:show="showType" position="bottom">
      <van-picker
        :columns="tondao_type"
        @confirm="onConfirm1"
        @cancel="showType = false"
        :model="edit_data.bank_type"
        :confirm-button-text="$t('msg.yes')"
        :cancel-button-text="$t('msg.quxiao')"
      />
    </van-popup>
    -->

    <!-- 替代实现：使用 van-popup 从底部弹出，高度 50%，内部为可滚动列表（兼容 PC 鼠标滚轮） -->
    <van-popup v-model:show="showHank" position="bottom" round class="custom-popup-bottom">
      <div class="picker-list">
        <ul>
          <li v-for="(b, idx) in bank_list" :key="b.value" @click="selectBank(b)">
            {{ b.text }}
          </li>
        </ul>
      </div>
    </van-popup>

    <van-popup v-model:show="showType" position="bottom" round class="custom-popup-bottom">
      <div class="picker-list">
        <ul>
          <li v-for="(t, idx) in tondao_type" :key="t.value || t.text" @click="selectType(t)">
            {{ t.text }}
          </li>
        </ul>
      </div>
    </van-popup>
	
	<!-- 统一的提现方式对话框 -->
    <van-dialog
      v-model:show="showPwd"
      :title="$t('msg.tkxx')"
      @confirm="confirmPwd"
      :confirmButtonText="$t('msg.queren')"
      closeOnClickOverlay
    >
      <van-form>
        <van-cell-group inset>
          <!-- 第一行：提现类型选择 (Bank / USDT) -->
          <van-field
            class="zdy"
            :label="$t('msg.txlx')"
            :model-value="edit_data.tx_type"
            @click="showTxType = true"
            readonly
            :placeholder="$t('msg.txlx')"
          >
            <template #right-icon>
              <span class="chevron">></span>
            </template>
          </van-field>
          
          <!-- Bank 模式的字段 -->
          <template v-if="edit_data.tx_type === 'Bank'">
            <van-field
              class="zdy"
              :label="$t('msg.aba')"
              v-model="edit_data.routing_number"
              name="routing_number"
              :placeholder="$t('msg.aba')"
              :rules="[{ required: true, message: $t('msg.aba') }]"
            />
            <van-field
              class="zdy"
              :label="$t('msg.yhmc')"
              v-model="edit_data.bank_name"
              name="bank_name"
              :placeholder="$t('msg.yhmc')"
              :rules="[{ required: true, message: $t('msg.yhmc') }]"
            />
            <van-field
              class="zdy"
              :label="$t('msg.yhdz')"
              v-model="edit_data.bank_address"
              name="bank_address"
              :placeholder="$t('msg.yhdz')"
              :rules="[{ required: true, message: $t('msg.yhdz') }]"
            />
            <van-field
              class="zdy"
              :label="$t('msg.yhkh')"
              v-model="edit_data.bank_card_number"
              name="bank_card_number"
              :placeholder="$t('msg.yhkh')"
              :rules="[{ required: true, message: $t('msg.yhkh') }]"
            />
            <van-field
              class="zdy"
              :label="$t('msg.khxm')"
              v-model="edit_data.name"
              name="name"
              :placeholder="$t('msg.khxm')"
              :rules="[{ required: true, message: $t('msg.khxm') }]"
            />
            <van-field
              class="zdy"
              :label="$t('msg.swift_bic')"
              v-model="edit_data.swift_bic"
              name="swift_bic"
              :placeholder="$t('msg.swift_bic')"
            />
          </template>
          
          <!-- USDT 模式的字段 -->
          <template v-if="edit_data.tx_type === 'USDT'">
            <van-field
              class="zdy"
              :label="$t('msg.usdt_type')"
              :model-value="edit_data.usdt_type"
              @click="showUsdtType = true"
              readonly
              :placeholder="$t('msg.usdt_type')"
              :rules="[{ required: true, message: $t('msg.usdt_type') }]"
            >
              <template #right-icon>
                <span class="chevron">></span>
              </template>
            </van-field>
            <van-field
              class="zdy"
              :label="$t('msg.usdt_address')"
              v-model="edit_data.usdt_address"
              name="usdt_address"
              :placeholder="$t('msg.usdt_address')"
              :rules="[{ required: true, message: $t('msg.usdt_address') }]"
            />
          </template>
          
          <!-- 通用：交易密码 -->
          <van-field
            v-model="paypassword"
            :label="$t('msg.tx_pwd')"
            type="password"
            :placeholder="$t('msg.input_tx_pwd')"
          />
        </van-cell-group>
      </van-form>
    </van-dialog>

    <!-- 提现类型选择 (Bank / USDT) -->
    <van-popup v-model:show="showTxType" position="bottom" round class="custom-popup-bottom">
      <div class="picker-list">
        <ul>
          <li @click="selectTxType('Bank')">Bank</li>
          <li @click="selectTxType('USDT')">USDT</li>
        </ul>
      </div>
    </van-popup>

    <!-- USDT 类型选择 (TRC20 / ERC20) -->
    <van-popup v-model:show="showUsdtType" position="bottom" round class="custom-popup-bottom">
      <div class="picker-list">
        <ul>
          <li @click="selectUsdtType('USDT-TRC20')">USDT-TRC20</li>
          <li @click="selectUsdtType('USDT-ERC20')">USDT-ERC20</li>
        </ul>
      </div>
    </van-popup>
	
    <van-dialog
      v-model:show="showUsdt"
      :title="$t('msg.tkxx')"
      @confirm="confirmPwd"
      :confirmButtonText="$t('msg.queren')"
      closeOnClickOverlay
    >
      <van-form>
        <van-cell-group inset>
          <van-cell @click="showHank = true" name="usdt_type">
            <template #title>
              <span class="khlx">{{ $t("msg.usdt_type") }}</span>
              {{ usdt_type }}
            </template>
          </van-cell>
          <van-field
            class="zdy"
            v-model="usdt_diz"
            :label="$t('msg.usdt_address')"
            name="usdt_diz"
            :placeholder="$t('msg.usdt_address')"
            :rules="[{ required: true, message: $t('msg.input_usdt_address') }]"
          />
          <van-field
            v-model="paypassword"
            :label="$t('msg.tx_pwd')"
            type="password"
            :placeholder="$t('msg.input_tx_pwd')"
          />
        </van-cell-group>
      </van-form>
    </van-dialog>
  </div>
</template>

<script>
import { reactive, ref, getCurrentInstance } from "vue";
import store from "@/store/index";
import { bind_bank, set_bind_bank, user_del_bank } from "@/api/self/index.js";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
export default {
  name: "HomeView",
  setup() {
    const { t, locale } = useI18n();
    const { push } = useRouter();
    const { proxy } = getCurrentInstance();
    const showPwd = ref(false);
    const showUsdt = ref(false);
    const showHank = ref(false);
    const showType = ref(false);
    const showTxType = ref(false);
    const showUsdtType = ref(false);
    const showKeyboard = ref(false);
    const bank_name = ref("");
    const bank_code = ref("");
    const bank_type = ref("");
    const username = ref("");
    const id_number = ref("");
    const usdt_type = ref("");
    const usdt_diz = ref("");
    const py_status = ref(1);
    const tel = ref("");
    const mailbox = ref("");
    const paypassword = ref("");
    const bank_list = ref([]);
    const tondao_type = ref([]);
    const info = ref({});
    const form_ = ref({});
    const lang = ref(locale.value);
    const default_bank_type = ref("");
    const default_bankname = ref("");
    const default_bank_code = ref("");
	
	
    const bank_cardnumber_switch = ref(true);
    const bank_cci_switch = ref(true);
    const bank_mail_switch = ref(true);
    const bank_name_switch = ref(true);
    const bank_phone_switch = ref(true);
    const branch_bank_name_switch = ref(true);
    const user_bank_name_switch = ref(true);
	
	const edit_data = ref({
		"id":"",
		"tx_type":"Bank",
		"routing_number":"",
		"bank_name":"",
		"bank_address":"",
		"bank_card_number":"",
		"name":"",
		"swift_bic":"",
		"usdt_type":"usdt-trc20",
		"usdt_address":"",
		"bankname":"",
		"cardnum":"",
		"username":"",
		"document_type":"",
		"document_id":"",
		"bank_code":"",
		"bank_branch":"",
		"bank_type":"",
		"account_digit":"",
		"wallet_tel":"",
		"wallet_document_id":"",
		"wallet_document_type":"",
		"site":"",
		"tel":"",
		"address":"",
		"qq":"",
		"mailbox":"",
		"cci":"",
	});
    // const edit_data = ref("");
    const edit_card_switch = ref(false);
    const del_card_switch = ref(false);
    // const customFieldName = ref({})
    bind_bank().then((res) => {
      if (res.code === 0) {
        const json = res.data?.bank_list;
        tondao_type.value = res.data?.tondao_type.map((rr,key) => {
			if(key === 0 && edit_data.value.bank_type == ''){
				edit_data.value.bank_type = rr
				default_bank_type.value = rr
			}
          return {
            text: rr,
            value: rr,
          };
        });
        for (const key in json) {
          bank_list.value.push({ text: json[key], value: key });
        }
		if(json && edit_data.value.bankname == ''){
			let firstKey = Object.keys(json)[0]
			edit_data.value.bankname = json[firstKey]
			default_bankname.value = json[firstKey]
			default_bank_code.value = firstKey
			edit_data.value.bank_code = firstKey
		}
		
		// edit_data.value.bankname = res.data?.bank_list.map((v,k) => {
		// 	console.log('数据：',k,v);return ;
		// 	if(k === 0 && edit_data.value.bankname == ''){
		// 		edit_data.value.bankname = v
		// 	}
		// });
		
        info.value = { ...res.data?.info };
		// console.log(info.value[0].id)
		edit_card_switch.value =  res.data?.edit_card_switch
		del_card_switch.value =  res.data?.del_card_switch
		py_status.value = res.data?.py_status;
		
		bank_cardnumber_switch.value =  res.data?.bank_cardnumber_switch
		bank_cci_switch.value =  res.data?.bank_cci_switch
		bank_mail_switch.value =  res.data?.bank_mail_switch
		bank_name_switch.value =  res.data?.bank_name_switch
		bank_phone_switch.value =  res.data?.bank_phone_switch
		branch_bank_name_switch.value =  res.data?.branch_bank_name_switch
		user_bank_name_switch.value =  res.data?.user_bank_name_switch
		
        // bank_type.value = info.value?.bank_type || tondao_type.value[0]?.text;
        // bank_name.value = info.value?.bankname;
        // bank_code.value = info.value?.bank_code;
        // username.value = info.value?.username;
        // paypassword.value = info.value?.paypassword
        // tel.value = info.value?.tel;
        // mailbox.value = info.value?.mailbox;
        // id_number.value = info.value?.cardnum;
        // usdt_type.value = info.value?.usdt_type;
        // usdt_diz.value = info.value?.usdt_diz;
      }
    });

    const clickLeft = () => {
      push("/self");
    };
	
    const del_bank = (bid) => {
		proxy.$dialog.confirm({
			title: t('msg.del'),
			message: t('msg.yes')+t('msg.del')+'?',
			confirmButtonText: t('msg.yes'),
			cancelButtonText: t('msg.quxiao'),
		}).then(() => {           
			user_del_bank({bid:bid}).then(res => {
				if(res.code === 0) {
					proxy.$Message({ type: 'success', message:res.info});
					window.location.reload();
				} else {
					proxy.$Message({ type: 'error', message:res.info});
				}
			})
		})
		.catch(() => {
			// on cancel
		});
    };
    const clickRight = () => {
      push("/tel");
    };
    const editShowDialog = (i) => {
	  edit_data.value = { ...info.value[i] };
	  // 根据现有数据确定 tx_type
	  if (info.value[i].bank_type == "USDT") {
		edit_data.value.tx_type = "USDT";
		edit_data.value.usdt_type = info.value[i].usdt_type;
		edit_data.value.usdt_address = info.value[i].usdt_diz;
	  } else {
		edit_data.value.tx_type = "Bank";
    edit_data.value.routing_number = info.value[i].bank_branch;
    edit_data.value.bank_card_number = info.value[i].cardnum;
    edit_data.value.bank_name = info.value[i].bankname;
    edit_data.value.name = info.value[i].username;
    edit_data.value.swift_bic = info.value[i].account_digit;
    edit_data.value.bank_address = info.value[i].site;
	  }
	  showPwd.value = true;
    };
    const showDialog = () => {
	  for (const key in edit_data.value) {
		edit_data.value[key] = "";
	  }
	  edit_data.value.bankname = default_bankname;
	  edit_data.value.bank_type = default_bank_type;
	  edit_data.value.bank_code = default_bank_code;
	  edit_data.value.tx_type = "Bank";
	  edit_data.value.usdt_type = "usdt-trc20";
      if (py_status.value == 2) {
        showUsdt.value = true;
      } else {
        showPwd.value = true;
      }
    };

    const confirmPwd = () => {
	  var submit_data = {};
      // 统一处理所有提现方式
      edit_data.value = { ...edit_data.value, ...{ paypassword: paypassword.value } }
      let edit = edit_data.value
      for (const key in edit) {
        if (Object.hasOwnProperty.call(edit, key)) {
          submit_data[key] = edit[key];
        }
      }

      set_bind_bank(submit_data).then((res) => {
        if (res.code === 0) {
          proxy.$Message({ type: "success", message: res.info });
          push("/self");
        } else {
          proxy.$Message({ type: "error", message: res.info });
		  // setTimeout(() => {
		  //   window.location.reload()
		  // }, 2000);
        }
      });
    };
	
    const onConfirm = (value) => {
      if (py_status.value == 2) {
        // bank_name.value = value.text;
        usdt_type.value = value.value;
        showHank.value = false;
      } else {
        edit_data.value.bankname = value.text;
        edit_data.value.bank_code = value.value;
        showHank.value = false;
      }
    };
    const onConfirm1 = (value) => {
      edit_data.value.bank_type = value.text;
      showType.value = false;
    };

    const selectBank = (b) => {
      if (py_status.value == 2) {
        usdt_type.value = b.value;
      } else {
        edit_data.value.bankname = b.text;
        edit_data.value.bank_code = b.value;
      }
      showHank.value = false;
    };

    const selectType = (t) => {
      // t may be {text,value} or plain string
      const text = t?.text ?? t;
      edit_data.value.bank_type = text;
      showType.value = false;
    };

    const selectTxType = (type) => {
      edit_data.value.tx_type = type;
      showTxType.value = false;
    };

    const selectUsdtType = (type) => {
      edit_data.value.usdt_type = type;
      showUsdtType.value = false;
    };

    const onSubmit = (values) => {
      if (!bank_code.value) {
        proxy.$Message({ type: "error", message: t("msg.input_yhxz") });
      } else {
        form_.value = { ...values, ...{ bank_code: bank_code.value } };
        console.log(form_.value);
      }
    };

    return {
		default_bank_code,
		default_bankname,
		default_bank_type,
		bank_cardnumber_switch,
		bank_phone_switch,
		bank_cci_switch,
		bank_mail_switch,
		bank_name_switch,
		branch_bank_name_switch,
		user_bank_name_switch,
	  del_bank,
	  editShowDialog,
	  edit_data,
	  edit_card_switch,
	  del_card_switch,
      onConfirm,
      onConfirm1,
      bank_name,
      showHank,
      showType,
      showTxType,
      showUsdtType,
      bank_type,
      paypassword,
      tel,
      mailbox,
      id_number,
      usdt_type,
      usdt_diz,
      username,
      bank_code,
      onSubmit,
      clickLeft,
      clickRight,
      bank_list,
      tondao_type,
      selectBank,
      selectType,
      selectTxType,
      selectUsdtType,
      showKeyboard,
      info,
      showPwd,
      showUsdt,
      confirmPwd,
      lang,
      py_status,
      showDialog,
    };
  },
};
</script>

<style scoped lang="scss">
@import "@/styles/theme.scss";
.home {
  :deep(.van-nav-bar) {
    background-color: $theme;
    color: #fff;
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
    .van-nav-bar__right {
      img {
        height: 42px;
      }
    }
  }
  .box_bank {
    margin: 20px;
    box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
    padding: 40px;
    background: #fff;
    border-radius: 12px;
    font-size: 24px;
    color: #333;
    text-align: left;
    .li {
      margin-bottom: 10px;
    }
    .van-button--primary {
      width: 120px;
      border-radius: 6px;
      padding: 0;
      height: 60px;
      background-color: #6d00be;
      border: none;
    }
  }
  .not_box_bank {
    margin-top: 50px;
    .not {
      width: 90%;
      margin: 120px auto 0;
    }
  }
  :deep(.van-form) {
    padding: 40px 0 0;

    .van-cell.van-cell--clickable {
      padding: 32px;
      margin: 20px 0;
    }
    .van-cell-group--inset {
      padding: 0 24px;
      .van-cell__title {
        display: flex;
        line-height: 1;
      }
      .khlx {
        width: var(--van-field-label-width);
        margin-right: var(--van-field-label-margin-right);
      }
    }
    .van-cell {
      padding: 23px 0;
      text-align: left;
      border-bottom: 1px solid var(--van-cell-border-color);
      .van-field__left-icon {
        width: 90px;
        text-align: center;
        .van-icon__image {
          height: 42px;
          width: auto;
        }
        .icon {
          height: 42px;
          width: auto;
          vertical-align: middle;
        }
        .van-dropdown-menu {
          .van-dropdown-menu__bar {
            height: auto;
            background: none;
            box-shadow: none;
          }
          .van-cell {
            padding: 30px 80px;
          }
        }
      }
      .van-field__control {
        font-size: 24px;
      }
      &::after {
        display: none;
      }
    }
    .van-checkbox {
      margin: 30px 0 60px 0;
      .van-checkbox__icon {
        font-size: 50px;
        margin-right: 80px;
        &.van-checkbox__icon--checked .van-icon {
          background-color: $theme;
          border-color: $theme;
        }
      }
      .van-checkbox__label {
        font-size: 24px;
      }
    }
    
    /* 选择字段右侧的箭头指示符 */
    .chevron {
      font-size: 28px;
      color: #177be7;
      font-weight: bold;
      margin-right: 10px;
    }
    .text_b {
      margin: 150px 60px 40px;
      font-size: 18px;
      color: #999;
      text-align: left;
      .tex {
        margin-top: 20px;
      }
    }
    .buttons {
      padding: 0 76px;
      .van-button {
        font-size: 36px;
        padding: 20px 0;
        height: auto;
      }
      .van-button--plain {
        margin-top: 40px;
      }
    }
  }

  :deep(.van-dialog) {
    width: 90%;
    max-height: 85%;
    display: flex;
    flex-direction: column;
    font-size: 30px;
    .van-dialog__content {
      flex: 1;
      overflow: auto;
    }
    .van-cell{
      font-size: 30px;
    }
    .van-button{
      font-size: 30px;
      height: 60px;
    }
    .van-dialog__footer {
      .van-dialog__confirm {
        color: $theme;
      }
    }
  }

  /* Picker list used to replace van-picker for PC (scrollable) */
  .picker-list {
    padding: 0 0 10px;
  }
  .picker-list ul {
    list-style: none;
    margin: 0;
    padding: 0;
    max-height: 45vh;
    overflow: auto;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
  }
  .picker-list li {
    padding: 14px 20px;
    border-bottom: 1px solid #f2f2f2;
    font-size: 28px;
    cursor: pointer;
  }
  .picker-list li:hover {
    background: #f5f5f7;
  }

  /* Ensure the popup container is constrained to 50% height and inner body allows wheel */
  :deep(.van-popup.custom-popup-bottom.van-popup--bottom) {
    max-height: 50vh !important;
    height: auto !important;
    margin-bottom: 200px !important;
  }

  :deep(.van-popup.custom-popup-bottom.van-popup--bottom) :deep(.van-popup__body),
  .picker-list ul {
    overflow: auto;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
  }
}
</style>
