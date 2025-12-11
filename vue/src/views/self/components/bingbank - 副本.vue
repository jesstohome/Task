<template>
  <div class="home">
    <van-nav-bar
      :title="$t('msg.tkxx')"
      left-arrow
      @click-left="$router.go(-1)"
    >
    </van-nav-bar>
    <div class="box_bank" v-if="info && Object.keys(info).length > 0">
      <div class="li" v-if="py_status !== 2">
        <span class="span">{{ $t("msg.khlx") }}：</span>
        <span class="span">{{ bank_type }}</span>
      </div>
      <div class="li" v-if="py_status !== 2">
        <span class="span">{{ $t("msg.khxm") }}：</span>
        <span class="span">{{ username }}</span>
      </div>
      <div class="li" v-if="py_status !== 2">
        <span class="span">{{ $t("msg.yhkh") }}：</span>
        <span class="span">{{ id_number }}</span>
      </div>
      <div class="li" v-if="py_status !== 2">
        <span class="span">{{ $t("msg.ylsjh") }}：</span>
        <span class="span">{{ tel }}</span>
      </div>
      <div class="li" v-if="py_status == 2">
        <span class="span">{{ $t("msg.usdt_type") }}：</span>
        <span class="span">{{ usdt_type }}</span>
      </div>
      <div class="li" v-if="py_status == 2">
        <span class="span">{{ $t("msg.usdt_address") }}：</span>
        <span class="span">{{ usdt_diz }}</span>
      </div>
      <van-button round block type="primary" @click="showDialog()">
        {{ $t("msg.edit") }}
      </van-button>
    </div>
    <div class="not_box_bank" v-else>
      <van-empty :description="$t('msg.not_data')" />
      <van-button round block type="primary" class="not" @click="showDialog()">
        {{ $t("msg.add") }}
      </van-button>
    </div>
    <van-popup v-model:show="showHank" position="bottom">
      <van-picker
        :columns="bank_list"
        @confirm="onConfirm"
        @cancel="showHank = false"
        :confirm-button-text="$t('msg.yes')"
        :cancel-button-text="$t('msg.quxiao')"
      />
    </van-popup>
    <van-popup v-model:show="showType" position="bottom">
      <van-picker
        :columns="tondao_type"
        @confirm="onConfirm1"
        @cancel="showType = false"
        :confirm-button-text="$t('msg.yes')"
        :cancel-button-text="$t('msg.quxiao')"
      />
    </van-popup>
    <van-dialog
      v-model:show="showPwd"
      :title="$t('msg.tkxx')"
      @confirm="confirmPwd"
      :confirmButtonText="$t('msg.queren')"
      closeOnClickOverlay
    >
      <van-form>
        <van-cell-group inset>
          <van-cell @click="showType = true" name="bank_type">
            <template #title>
              <span class="khlx">{{ $t("msg.khlx") }}</span>
              {{ bank_type }}
            </template>
          </van-cell>
          <van-field
            class="zdy"
            :label="$t('msg.khxm')"
            v-model="username"
            name="username"
            :placeholder="$t('msg.khxm')"
            :rules="[{ required: true, message: $t('msg.input_zsxm') }]"
          />

          <van-field
            v-if="lang == 'es_mx'"
            class="zdy"
            :label="$t('msg.ylsjh')"
            name="tel"
            v-model="tel"
            :placeholder="$t('msg.ylsjh')"
            :rules="[{ required: true, message: $t('msg.inputsfzh') }]"
          />
          <van-field
            class="zdy"
            v-else
            name="tel"
            :label="$t('msg.ylsjh')"
            v-model="tel"
            :placeholder="$t('msg.ylsjh')"
            :rules="[{ required: true, message: $t('msg.input_tel_phone') }]"
          />
          <van-field
            class="zdy"
            name="mailbox"
            :label="$t('msg.email')"
            v-model="mailbox"
            :placeholder="$t('msg.email')"
            :rules="[{ required: true, message: $t('msg.input_email') }]"
          />
          <van-cell @click="showHank = true" name="bank_name">
            <template #title>
              <span class="khlx">{{ $t("msg.yhmc") }}</span>
              {{ bank_name }}
            </template>
          </van-cell>
          <!-- <van-field
            class="zdy"
            v-model="id_number"
            :label="$t('msg.yhmc')"
            name="id_number"
            :placeholder="$t('msg.yhmc')"
            :rules="[{ required: true, message: $t('msg.input_yhmc') }]"
            /> -->
          <van-field
            class="zdy"
            v-model="id_number"
            :label="$t('msg.yhkh')"
            name="id_number"
            :placeholder="$t('msg.yhkh')"
            :rules="[{ required: true, message: $t('msg.input_yhkh') }]"
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
import { bind_bank, set_bind_bank } from "@/api/self/index.js";
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
    // const customFieldName = ref({})
    bind_bank().then((res) => {
      if (res.code === 0) {
        const json = res.data?.bank_list;
        tondao_type.value = res.data?.tondao_type.map((rr) => {
          return {
            text: rr,
            value: rr,
          };
        });
        for (const key in json) {
          bank_list.value.push({ text: json[key], value: key });
        }
        info.value = { ...res.data?.info };
        bank_type.value = info.value?.bank_type || tondao_type.value[0]?.text;
        bank_name.value = info.value?.bankname;
        bank_code.value = info.value?.bank_code;
        username.value = info.value?.username;
        // paypassword.value = info.value?.paypassword
        tel.value = info.value?.tel;
        mailbox.value = info.value?.mailbox;
        id_number.value = info.value?.cardnum;
        usdt_type.value = info.value?.usdt_type;
        usdt_diz.value = info.value?.usdt_diz;
        py_status.value = res.data?.py_status;
      }
    });

    const clickLeft = () => {
      push("/self");
    };
    const clickRight = () => {
      push("/tel");
    };
    const showDialog = () => {
      if (py_status.value == 2) {
        showUsdt.value = true;
      } else {
        showPwd.value = true;
      }
    };

    const confirmPwd = () => {
      if (py_status.value == 2) {
        form_.value = {
          usdt_type: usdt_type.value,
          usdt_diz: usdt_diz.value,
        };
      } else {
        form_.value = {
          bank_name: bank_name.value,
          bank_code: bank_code.value,
          bank_type: bank_type.value,
          username: username.value,
          tel: tel.value,
          mailbox: mailbox.value,
          id_number: id_number.value,
        };
      }
      const info = { ...form_.value, ...{ paypassword: paypassword.value } };
      console.log(info);
      set_bind_bank(info).then((res) => {
        if (res.code === 0) {
          proxy.$Message({ type: "success", message: res.info });
          push("/self");
        } else {
          proxy.$Message({ type: "error", message: res.info });
        }
      });
    };

    const onConfirm = (value) => {
      if (py_status.value == 2) {
        // bank_name.value = value.text;
        usdt_type.value = value.value;
        showHank.value = false;
      } else {
        bank_name.value = value.text;
        bank_code.value = value.value;
        showHank.value = false;
      }
    };
    const onConfirm1 = (value) => {
      bank_type.value = value.text;
      showType.value = false;
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
      onConfirm,
      onConfirm1,
      bank_name,
      showHank,
      showType,
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
    .van-nav-bar__left {
      .van-icon {
        color: #fff;
      }
    }
    .van-nav-bar__title {
      color: #fff;
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
    .van-dialog__content {
      flex: 1;
      overflow: auto;
    }
    .van-dialog__footer {
      .van-dialog__confirm {
        color: $theme;
      }
    }
  }
}
</style>
