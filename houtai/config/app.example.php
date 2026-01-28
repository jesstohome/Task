<?php

return [



    //是否开启谷歌令牌
    'open_google_safe' => false,

    //是否开启多国家手机号
    'open_country_phone' => false,

    'url_route_must' => false,
    // 应用调试模式
    'app_debug' => true,
    // 应用Trace调试
    'app_trace' => false,
    // 0按名称成对解析 1按顺序解析
    'url_param_type' => 1,
    // 当前 ThinkAdmin 版本号
    'thinkadmin_ver' => 'v5',

    'default_lang' => 'zh-cn,en-ww,en-au,en-id,es-mx,iv-vn,pt-br,tr-tr,hy-hy',
    //土耳其TUR  菲律宾PHL  澳大利亚AUS  印度INR  巴西BRA  墨西哥MEX  哥伦比亚COL  南非ZAF
    'default_country' => 'MEX',
    'lang_switch_on' => true,
    'empty_controller' => 'Error',
    'empty_module' => 'index',
    'deny_module_list' => ['lang'],
    'pwd_str' => '!qws6F!xffD2vx80?95jt',  //盐

    'level1_commission'=>'5',//一级佣金比例

    'equation_of_time' => 0,  //时差（小时）

    'default_timezone' => 'America/New_York',//时区设置
    //是否启用代理客服， 如果启用那么每个代理都需要设置自己的客服连接
    'open_agent_chat' => 1,
    //货币符号
    'currency'=>'USD',
    'recharge_money_list'=>'10/30/50/100/200/500/1000/2000/3000',
    'first_deposit_upgrade_level'=>'', //首次提现后升级到指定级别
    'withdrawal_fee_rate'=>'5', //提现手续费%
    'clean_recharge_hour'=>'1',//自动清理未支付订单
    'lang_tel_pix'=>'',
    'lang_tel_pix2'=>'1',
    'enable_lxb'=>'0',//是否启用利息宝
    'is_same_yesterday_order'=>'1',//是否允许做和昨天相同级别任务
    'ip_register_number'=>'1',//同一个IP注册账号数量

    'pwd_error_num' => 10,    //密码连续错误次数

    'allow_login_min' => 1,     //密码连续错误达到次数后的冷却时间，分钟

    'default_filter' => 'trim',

    'zhangjun_sms' => [
        'userid' => '????',
        'account' => '?????',
        'pwd' => '????',
        'content' => '【????】您的验证码为：',
        'min' => 5,  //短信有效时间，分钟
    ],
    //短信宝
    'smsbao' => [
        'user'=>'', //账号  无需md5
        'pass'=>'', //密码
        'sign'=>'', //签名
        "recharge_times" => '1',
    ],


    //提现配置
    'payout_wallet'=>'',
    'payout_bank'=>'1',
    'payout_usdt'=>'',

    //bi支付
    'bipay' => [
        'appKey' => '',
        'appSecret' => '',
    ],
    //paysapi支付
    'paysapi' => [
        'uid' => '',   //bi支付 商户appkey
        'token' => '', //密钥
        'istype' => 1, //默认支付方式  1 支付宝  2 微信  3 比特币
    ],

    'app_only' => 0,            //只允许app访问
    'vip_sj_bu' => 1,            //vip升级 是否补交
    'app_url'=>'',          //app下载地址
    'version'=>'',        //版本号
    "langs" => "3242",
    'free_balance'=>'0', //账户体验金。需要在第一次充值对时候扣掉。
    'free_balance_time'=>'0',
    'invite_one_money'=>'0', //邀请一个用户得到多少钱
    'invite_recharge_money'=>'0.001', //邀请一个用户首次充值得到多少钱 5%
    'verify' => true,
    'mix_time' => '5',                    //匹配订单最小延迟
    'max_time' => '10',                   //匹配订单最大延迟
    'min_recharge' => '68',              //最小充值金额
    'max_recharge' => '50000',             //最大充值金额
    'deal_min_balance'=>'5',          //交易所需最小余额
    'deal_min_num'=>'0',               //匹配区间
    'deal_max_num'=>'0',               //匹配区间
    'deal_count'=>'2000',                 //当日交易次数限制
    'deal_reward_count'=>'0',          //推荐新用户获得额外的交易次数
    'deal_timeout'=>'3600',              //订单超时时间
    'deal_feedze'=>'10800',              //交易冻结时长
    'deal_error'=>'0',                  //允许违规操作次数
    'vip_1_commission'=>'',          //交易佣金
    'min_deposit' => '100',               //最低提现额度
    '1_reward' => '0',                  //充值 - 1代返佣
    '2_reward' => '0',                  //充值 - 2代返佣
    '3_reward' => '0',                  //充值 - 3代返佣
    '1_d_reward' => '0.0031',               //上级会员获得交易奖励
    '2_d_reward' => '0.0032',               //上二级会员获得交易奖励
    '3_d_reward'=>'1',               //上三级会员获得交易奖励
    '4_d_reward'=>'0',               //上四级会员获得交易奖励
    '5_d_reward'=>'20',                  //上五级会员获得交易奖励
    'master_cardnum'=>'2',             //银行卡号
    'master_name'=>'',                              //收款人
    'master_bank'=>'2',                          //所属银行（后改会员升级模式）
    'master_bk_address'=>'2',         //银行地址
    'deal_zhuji_time'=>'1',         //远程主机分配时间
    'deal_shop_time'=>'2',          //等待商家响应时间
    'tixian_time_1'=>'00',           //提现开始时间
    'tixian_time_2'=>'24',          //提现结束时间

    'chongzhi_time_1'=>'00',           //充值开始时间
    'chongzhi_time_2'=>'24',          //充值结束时间


    'order_time_1'=>'00',           //抢单结束时间
    'order_time_2'=>'24',          //抢单结束时间

    //利息宝
    'lxb_bili'=>'0.005',         //利息宝 日利率
    'lxb_time'=>'1',             //利息宝 转出到余额  实际 /小时
    'lxb_sy_bili1'=>'1',         //利息宝 上一级会员收益比例
    'lxb_sy_bili2'=>'1',         //利息宝 上一级会员收益比例
    'lxb_sy_bili3'=>'1',         //利息宝 上一级会员收益比例
    'lxb_sy_bili4'=>'',         //利息宝 上一级会员收益比例
    'lxb_sy_bili5'=>'2',         //利息宝 上一级会员收益比例
    'lxb_ru_max'=>'1000000',
    'lxb_ru_min'=>'100000',
    'a'=>'1',         //利息宝 转入最低金额

    // "recharge_times" => "1",

    'shop_status'=>'1',         //商城状态',


    'db_config2'      => [
        // 数据库类型
        'type'            => 'mysql',
        // 服务器地址
        'hostname'        => '45.144.139.69',
        // 用户名
        'username'        => 'sadasd',
        // 密码
        'password'        => 'YFpSCCyPLPMKkpRB',
        // 数据库名称
        'database'        => 'sadasd',
    ]

];
