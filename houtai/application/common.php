<?php

function getBankList($bank_data = [])
{
    $fileurl = APP_PATH . "../config/bank.txt";
    $bank_data = file_get_contents($fileurl);
    $bank_data = explode("\n", $bank_data);
//        取消usdt
//        $bank_data = Db::table("xy_pay")->where('status',1)->where("is_payout",1)->find();
//        $bank_data = explode("\n", $bank_data["bank_code"]);
    $bank_list = [];
    foreach ($bank_data as $v) {
        $vS = explode('|', $v);
        if (count($vS) != 2) continue;
        $bank_list[trim($vS[0])] = trim($vS[1]);
    }
    return $bank_list;
}

function get_user_order_goods_ids($uid)
{
    return \think\Db::table("xy_convey")
        ->where("uid", $uid)
        ->group('goods_id')
        ->column('goods_id');
}

function convert(&$args)
{
    $data = '';
    if (is_array($args)) {
        foreach ($args as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $k => $v) {
                    $data .= $key . '[' . $k . ']=' . rawurlencode($v) . '&';
                }
            } else {
                $data .= "$key=" . rawurlencode($val) . "&";
            }
        }
        return trim($data, "&");
    }
    return $args;
}	/**
 * [ ASCII 编码 ]
 * @param array  编码数组 
 * @param string 签名键名   => sign
 * @param string 密钥键名   => key
 * @param bool   签名大小写 => false(大写)
 * @param string 签名是否包含密钥 => false(不包含)
 * @return array 编码好的数组
 */
function ASCII2($asciiData, $asciiSign = 'sign', $asciiKey = 'key', $asciiSize = true, $asciiKeyBool = false)
{
    //编码数组从小到大排序
    ksort($asciiData);
    //拼接源文->签名是否包含密钥->密钥最后拼接
    $MD5str = "";
    foreach ($asciiData as $key => $val) {
        if (!$asciiKeyBool && $asciiKey == $key) continue;
        $MD5str .= $key . "=" . $val . "&";
    }
    $sign = $MD5str . $asciiKey . "=" . $asciiData[$asciiKey];
  
    //大小写->md5
    $asciiData[$asciiSign]  = $asciiSize ? strtoupper(md5($sign)) : strtolower(md5($sign));
    return $asciiData;
}

function isAllChinese($str)
{
    if (preg_match("/([\x81-\xfe][\x40-\xfe])/", $str, $match)) {
        return true;//全是中文
    } else {
        return false;//不全是中文
    }
}

function get_http_type()
    {
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        return $http_type;
    }
    
/*
 * 检查图片是不是bases64编码的
 */
function is_image_base64($base64)
{
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)) {
        return true;
    } else {
        return false;
    }
}

function check_pic($dir, $type_img)
{
    $new_files = $dir . date("YmdHis") . '-' . rand(0, 9999999) . "{$type_img}";
    if (!file_exists($new_files))
        return $new_files;
    else
        return check_pic($dir, $type_img);
}

/**
 * 获取数组中的某一列
 * @param array $arr 数组
 * @param string $key_name 列名
 * @return array  返回那一列的数组
 */
function get_arr_column($arr, $key_name)
{
    $arr2 = array();
    foreach ($arr as $key => $val) {
        $arr2[] = $val[$key_name];
    }
    return $arr2;
}

//保留两位小数
function tow_float($number)
{
    return (floor($number * 100) / 100);
}

//生成订单号
function getSn($head = '')
{
    $order_id_main = date('YmdHis') . mt_rand(1000, 9999);
    //唯一订单号码（YYMMDDHHIISSNNN）
    $osn = $head . substr($order_id_main, 2); //生成订单号
    return $osn;
}

/**
 * 修改本地配置文件
 *
 * @param array $name ['配置名']
 * @param array $value ['参数']
 * @return boolean
 */
function setconfig($name, $value)
{
    if (is_array($name) and is_array($value)) {
        for ($i = 0; $i < count($name); $i++) {
            $names[$i] = '/\'' . $name[$i] . '\'(.*?),/';
            $values[$i] = "'" . $name[$i] . "'" . "=>" . "'" . $value[$i] . "',";
        }
        $fileurl = APP_PATH . "../config/app.php";
        $string = file_get_contents($fileurl); //加载配置文件
        $string = preg_replace($names, $values, $string); // 正则查找然后替换
        file_put_contents($fileurl, $string); // 写入配置文件
        return true;
    } else {
        return false;
    }
}

//生成随机用户名
function get_username()
{
    $chars1 = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $chars2 = "abcdefghijklmnopqrstuvwxyz0123456789";
    $username = "";
    for ($i = 0; $i < mt_rand(2, 3); $i++) {
        $username .= $chars1[mt_rand(0, 25)];
    }
    $username .= '_';

    for ($i = 0; $i < mt_rand(4, 6); $i++) {
        $username .= $chars2[mt_rand(0, 35)];
    }
    return $username;
}

/**
 * 判断当前时间是否在指定时间段之内
 * @param integer $a 起始时间
 * @param integer $b 结束时间
 * @return boolean
 */
function check_time($a, $b)
{
    $nowtime = time();
    $start = strtotime($a . ':00:00');
    $end = strtotime($b . ':00:00');

    if ($nowtime >= $end || $nowtime <= $start) {
        return true;
    } else {
        return false;
    }
}


/**
 * 语言切换
 */ 
function  yuylangs($value){
    $language= Request::instance()->header('language');
    $lang = db("xy_lang")->where("value",$value)->find();
    if(empty($lang[$language]) || !$language){
        $langs = Db::table("xy_language")->where(['moryuy'=>1])->find();
        $res = db("xy_lang")->where("value",$value)->value($langs["link"]);
    }else{
        $res = $lang[$language];
    }
    return $res;
}

/**
 * 翻译语言，不存在则添加，最后使用英文key
 * @param $key 语言key
 * @return mixed
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 */
function translate($key)
{
    try {
        $language= Request::instance()->header('language');
        $lang = \think\Db::table("xy_lang")->where("value",$key)->find();
        if(empty($lang)){
            $getTableFields = \think\Db::table('xy_lang')->getTableFields();
            $data = [];
            foreach ($getTableFields as $v) {
                if($v != 'id'){
                    $data[$v] = $key;
                }
            }
            $id = \think\Db::table("xy_lang")->insertGetId($data);
            $lang = \think\Db::table("xy_lang")->where('id',$id)->find();
        }
        if(isset($lang[$language]) || empty($lang[$language]) || !$language){
            $langs = \think\Db::table("xy_language")->where(['moryuy'=>1])->find();
            $res = \think\Db::table("xy_lang")->where("value",$key)->value($langs["link"]);
        }else{
            $res = $lang[$language];
        }
        return $res;
    } catch (\Exception $e) {
        return $e->getMessage();
    }

}

function bl_type($type)
{
    if(isset(bl_type_arr()[$type])){
        return bl_type_arr()[$type];
    }
    return '其他';
}

function bl_type_arr()
{
    return [
        0=>'系统',
        1=>'用户充值',
        2=>'订单金额',
        3=>'订单返佣',
        4=>'强制交易',
        5=>'推广返佣',
        6=>'下级返佣',
        7=>'用户提现',
        8=>'其他',
        21=>'余额宝转入',
        22=>'余额宝转出',
        23=>'余额宝收益',
        30=>'升级vip',
        31=>'手动扣款',
        32=>'后台充值',
        33=>'注册奖励',
        34=>'免费赠送',
        35=>'冻结本金',
        36=>'解冻本金',
        87=>'抽奖',
    ];
}





