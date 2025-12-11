<?php

namespace app\index\pay;

use think\Db;

class Mbitpay
{
    private $mch_id = 'BXFF1';
    private $secret = '1322cd36c613a22788278baa6cb60e43';
 
    const PAY_URL = 'https://api.vgnnh.com/api/startCollectFiatForAnotherOrder';
    const PAYOUT_URL = 'https://api.vgnnh.com/api/startPayFiatForAnotherOrder';
    const channelCode = 'brazil_pix';
    const currency = 'BRL';

    public static function instance()
    {
        return new self();
    }

    public function get_appid()
    {
        return config('pay.mbitpay.mch_id');
    }

    public function get_secret()
    {
        return config('pay.mbitpay.secret');
    }

    public function get_pay_url()
    {
        return self::PAY_URL;
    }

    /**
     * 创建支付订单
     * @param array $data
     * @return json
     */
    public function create_order(array $data)
    { 
        $data2 = [];
        $data2['merchantNum'] = $this->get_appid();
        $data2['orderNo'] = $data['orderno'];
        $data2['amount'] = $data['orderamount'];
        $data2['notifyUrl'] = $data['notifyurl'];
        $data2['returnUrl'] = $data['returnurl'];
        $data2['channelCode'] = self::channelCode;
        $data2['currency'] =  self::currency;
        $data2['sign'] = md5(  $data2['merchantNum'].$data2['orderNo'].$data2['amount'].$data2['notifyUrl'].self::get_secret() );
        
        $json = $this->_post(self::PAY_URL, $data2 );
        $arr = json_decode( $json, true );
        if( $arr['code'] == 200 )
        {
            $url = $arr['data']['payUrl'];
            header("Location: {$url}");
            exit;
        }
        else
        {
            
            echo "<PRE>";
            
            print_r($arr);
            exit;
        }
       
        return $data;
    }

    /**
     * 支付回掉- 验证签名
     * @param $data array  数据包
     * @return bool
     */
    public function check_sign(array $data): bool
    {
        $md5SrcStr = $this->get_appid() . $data['orderno'] . $data['actualamount'] . $data['status'] . $this->get_secret();
        //全部转成大写
        $md5SrcStr = strtoupper($md5SrcStr);
        //MD5 摘要信息计算
        $local_sign = md5($md5SrcStr);
        return $data['sign'] == $local_sign;
    }


    public $_payout_msg = '';
    public $_payout_id = '';

    /**
     * 创建付款订单--银行卡付款
     * @param $oinfo array  申请提现记录 对应xy_deposit表
     * @param $blank_info array  银行卡信息 对应xy_bankinfo表
     * 
     * Array
(
    [id] => CO2204081516573770
    [uid] => 211
    [bk_id] => 1
    [num] => 100.00
    [num2] => 0.00
    [addtime] => 1649402217
    [endtime] => 0
    [status] => 1
    [agent_status] => 1
    [type] => bank
    [real_num] => 100.00
    [shouxu] => 0
    [usdt] => 
    [payout_id] => 
    [payout_type] => 
    [payout_status] => 0
    [payout_time] => 0
    [payout_err_msg] => 
)
Array
(
    [id] => 1
    [uid] => 211
    [bankname] => ANZ
    [cardnum] => 6232187677299313
    [username] => 李四
    [document_type] => 
    [document_id] => 
    [bank_code] => WBC
    [bank_branch] => 
    [bank_type] => 
    [account_digit] => 
    [wallet_tel] => 
    [wallet_document_id] => 
    [wallet_document_type] => 
    [site] => 
    [tel] => 18888888888
    [status] => 1
    [address] => 
    [qq] => 
)
    accountHolder:"Jackson",
bankCardAccount:"39483948394",
openAccountBank:"PIX",
mobile:"32145847784",
identityType:"CPF",
identityNo:"46438701840",
note:"test",



     * @return bool
     */
    public function create_pix_payout($oinfo, $blank_info)
    {
      #  
      #  echo "<PRE>";
     #   print_r($oinfo);
      #  print_r($blank_info);
      #  exit;
        
       
 
$data2 = [];
$data2['merchantNum'] = config('pay.mbitpay.mch_id');
$data2['orderNo'] = $oinfo['id'];
$data2['amount'] = $oinfo['num'];
$data2['notifyUrl'] =  url('/index/callback/doposit_mbitpay', '', true, true);
$data2['channelCode'] =  self::channelCode;
$data2['currency'] =   self::currency;
$data2['payeeAccInfo'] =  json_encode( [
     
        'accountHolder' => $blank_info['username'],
        'bankCardAccount' => $blank_info['cardnum'],
        'openAccountBank' => "PIX",
        'mobile' =>  $blank_info['tel'],
        'identityType' =>  "CPF",
        'identityNo' =>  "",
        'note' =>  "",
    ]);
$data2['sign'] = md5( $data2['merchantNum'].$data2['orderNo'].$data2['amount'].$data2['notifyUrl'] .$data2['payeeAccInfo'] .self::get_secret() );

$json = $this->_post( self::PAYOUT_URL, $data2 );

$arr = json_decode( $json, true );
 
if( $arr['code'] != 200 )
{
 
    $this->_payout_msg =  $arr['msg'] ;
    return  false;
}
else
{
    $this->_payout_id = isset($arr['data']['platformOrderNo']) ? isset($arr['data']['platformOrderNo']): '' ;
    return true;
}
echo "<PRE>";
print_r( $arr );
exit;

        $params = [
            'appid' => $this->get_appid(),
            'settAmount' => $oinfo['real_num'],
            'orderno' => $oinfo['id'],
            'notifyurl' => url('/index/callback/payout_tokpay', '', true, true),
            'payType' => 'PIX',
        ];
        $params['ifscCode'] = 'cpf';
        $params['bankaccountname'] = $blank_info['username'];
        $params['cardno'] = $blank_info['document_id'];
        $params['sign'] = $this->_make_payout_sign($params);
        $log_file = APP_PATH . 'payout_tokpay_pix.log';
        file_put_contents($log_file, date('Y-m-d H:i:s') . ': ' . json_encode($params, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
        $ret = $this->_post(self::PAYOUT_URL, $params);
        if (!$ret) {
            file_put_contents($log_file, date('Y-m-d H:i:s') . ': not content' . "\n", FILE_APPEND);
            return false;
        }
        file_put_contents($log_file, 'response: ' . $ret . "\n", FILE_APPEND);
        if ($ret == 'SUCCESS') {
            return true;
        }
        $this->_payout_msg = $ret;
        return false;
    }

    /**
     * 生成代付签名
     * @param $data array
     * @return string
     */
    private function _make_payout_sign($data)
    {
        $data['privateKey'] = $this->get_secret();
        ksort($data);
        $str = '';
        foreach ($data as $key => $value) {
            $str .= $key . '=' . $value . '&';
        }
        $str = strtoupper(substr($str, 0, -1));

        $log_file = APP_PATH . 'payout_tokpay_pix.log';
        file_put_contents($log_file, date('Y-m-d H:i:s') . ': ' . $str . "\n", FILE_APPEND);
        return md5($str);
    }

    /**
     * 生成支付签名
     * 注意：空值不参与加密，加密字段顺序按照下方示例顺序
     * //字段顺序为 固定 顺序:appid+支付类型+金额+订单号+私钥
     * $md5SrcStr = $appid . $paytype . $orderamount . $orderno . $privateKey;
     * //全部转成大写
     * $md5SrcStr = strtoupper($md5SrcStr);
     * //MD5 摘要信息计算
     * $sign = md5( $md5SrcStr );
     * @param $data array
     * @return string
     */
    private function _make_sign($data)
    {
        $md5SrcStr = $data['appid'] . $data['paytype'] . $data['orderamount'] . $data['orderno'] . $this->get_secret();
        $md5SrcStr = strtoupper($md5SrcStr);
        return md5($md5SrcStr);
    }

    /**
     * 发起请求
     * @param $payUrl string
     * @param $data array
     * @return string|null
     */
    private function _post(string $payUrl, array $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $payUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POST, 1);
        
       curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
       curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
       
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $output = curl_exec($ch);
        if (curl_error($ch)) return null;
        curl_close($ch);
        return $output;
    }
}