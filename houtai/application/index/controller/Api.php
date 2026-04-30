<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2019 
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 

// +----------------------------------------------------------------------

namespace app\index\controller;

use library\Controller;
use think\Db;
use app\index\controller\Tool;

/**
 * 支付控制器
 */
class Api extends Controller
{

    public $BASE_URL = "https://test.com";
    public $appKey = '';
    public $appSecret = '';

    const POST_URL = "https://test.com";


    public function __construct()
    {
        $this->appKey = config('app.bipay.appKey');
        $this->appSecret = config('app.bipay.appSecret');
    }

    public function bipay()
    {

        $oid = isset($_REQUEST['oid']) ? $_REQUEST['oid']: '';
        if ($oid) {
            $r = db('xy_recharge')->where('id',$oid)->find();
            if ($r) {
                $server_url = $_SERVER['SERVER_NAME']?"http://".$_SERVER['SERVER_NAME']:"http://".$_SERVER['HTTP_HOST'];
                $notifyUrl = $server_url.url('/index/api/bipay_notify');
                $returnUrl = $server_url.url('/index/api/bipay_return');
                $price = $r['num'] * 100;
                $res = $this->create_order($oid,$price,'用户充值',$notifyUrl, $returnUrl);

                if ($res && $res['code']==200) {
                    $url = $res['data']['pay_url'];
                    $this->redirect($url);
                }
            }
        }
    }

    public function bipay_return()
    {
        return $this->fetch();
    }
    
    
    public function one_pay1(){}
    
    public function one_pay2()
    {}
    
     public function entpay_notify_url(){}
    
     public function paytm_t_hui(){}
    
     public function paytm_hui(){}
    
    
    public function k11pay_hui(){}
    
    
     public function cgbh_gbkyd2()
    {
        
        $data = $_POST;
        $merchantOrderId = $data['mer_order_no'];
        
        $deposit_list = Db::name('xy_deposit')->where([ 'id' => $merchantOrderId, 'status' => 2])->find();
        if(is_null($deposit_list)){
            exit('error');
        }
        if($data['status']=="SUCCESS"){
            $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 2,"payout_status"=>2]);
             return 'SUCCESS';
        }elseif($data['status']=="FAIL"){
            $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 3,"payout_status"=>"3"]);
            Db::name("xy_users")->where('id',$deposit_list['uid'])->setInc("balance",$deposit_list['num']);
             return 'SUCCESS';
        }else{
            
            return "kkk";
        }
       return "kkk";
    }
    
    
     public function zepay1(){
        $data = $_POST;
       
      	$charge_list = Db::name('xy_recharge')->where([ 'id' => $data['mchOrderNo'], 'status' => 1])->find();
        //订单不存在
        if(is_null($charge_list)) exit('error');

        if($data['state'] == "2"){
         
            $res = model('admin/Users')->recharge_success($charge_list['id'],1);
        
           exit("SUCCESS");
        }
        
       
            exit('success');
       
       
       
        
    }
    
    public function zepay2()
    {
         $data = $_POST;
        $merchantOrderId = $data['mchOrderNo'];
        
        $deposit_list = Db::name('xy_deposit')->where([ 'id' => $merchantOrderId, 'status' => 2])->find();
        if(is_null($deposit_list)){
            exit('error');
        }
        if($data['state']=="2"){
            $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 2,"payout_status"=>2]);
             return 'SUCCESS';
        }elseif($data['state']=="3"){
            $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 3,"payout_status"=>"3"]);
            Db::name("xy_users")->where('id',$deposit_list['uid'])->setInc("balance",$deposit_list['num']);
             return 'SUCCESS';
        }else{
            
            return "kkk";
        }
       return "kkk";
    }
    
    public function wepayplus1(){}
    
    public function wepayplus2()
    {}
    
    
    public function nicepay1(){}
    
    public function nicepay2()
    {}
    
    
     public function hxpayment1(){}
    
    public function hxpayment2()
    {}
    
    public function cgbh_gbkyd1(){}
    
    
    public function speedlycp_hui(){}
    
    public function speedlycp_hui1(){}
    
    
    
    public function k11pay_hui1(){}
    
    
      /**解密
     * @param $aesSecret
     * @return false|string
     */
    public function decryptAes($aesSecret,$method,$merchantKey)
    {
        $str="";
        for($i=0;$i<strlen($aesSecret)-1;$i+=2){
            $str.=chr(hexdec($aesSecret[$i].$aesSecret[$i+1]));
        }
        $jsonData =  openssl_decrypt($str,$method,$merchantKey, OPENSSL_RAW_DATA,$merchantKey);
        $data = json_decode($jsonData,true);
        return $data;
    }
    
    public function CrushPay_hui_fus()
    {}
    
    public function qepay_fuhui()
    {}
    
    public function qepay_fuhui1(){
        if(request()->isPost()){}else{
    		exit('error');
    	}
    }
    
    
    public function CrushPay_hui(){}
    
    
    
    public function sasdas()
    {
        
        $post = input();
        if (!$post['order_sn']) {
            die('fail');
        }
        $oid = $post['order_sn'];
        $r = Db::name('xy_recharge')->where(['id'=>$oid])->find();
        if (!$r) {
            die('fail');
        }
        if ($post['status']!=1) {
            die('fail');
        }

        if ($r['status'] == 2){
            die('SUCCESS');
        }

       // if ($post['order_state']) {
            $res = Db::name('xy_recharge')->where('id',$oid)->update(['endtime'=>time(),'status'=>2,"pay_status"=>1]);
            $oinfo = $r;
            $res1 = Db::name('xy_users')->where('id',$oinfo['uid'])->setInc('balance',$oinfo['num']);
            $res2 = Db::name('xy_balance_log')
                ->insert([
                    'uid'=>$oinfo['uid'],
                    'oid'=>$oid,
                    'num'=>$oinfo['num'],
                    'type'=>1,
                    'status'=>1,
                    'addtime'=>time(),
                ]);
            /************* 发放推广奖励 *********/
            $uinfo = Db::name('xy_users')->field('id,active')->find($oinfo['uid']);
            if($uinfo['active']===0){
                Db::name('xy_users')->where('id',$uinfo['id'])->update(['active'=>1]);
                //将账号状态改为已发放推广奖励
                $userList = model('admin/Users')->parent_user($uinfo['id'],3);
                if($userList){
                    foreach($userList as $v){
                        if($v['status']===1 && ($oinfo['num'] * config($v['lv'].'_reward') != 0)){
                            Db::name('xy_reward_log')
                                ->insert([
                                    'uid'=>$v['id'],
                                    'sid'=>$uinfo['id'],
                                    'oid'=>$oid,
                                    'num'=>$oinfo['num'] * config($v['lv'].'_reward'),
                                    'lv'=>$v['lv'],
                                    'type'=>1,
                                    'status'=>1,
                                    'addtime'=>time(),
                                ]);
                        }
                    }
                }
            }
            /************* 发放推广奖励 *********/
            die('SUCCESS');
       // }
        
    }

    public function bipay_notify()
    {}


    public function create_order(
        $orderId, $amount, $body, $notifyUrl, $returnUrl, $extra = '', $orderIp = '', $amountType = 'CNY', $lang = 'zh_CN')
    {}

    /**
     * @return {
     * bapp_id: "2019081308272299266f",
     * order_id: "1565684838",
     * order_state: 0,
     * body: "php-sdk sample",
     * notify_url: "https://sdk.b.app/api/test/notify/test",
     * order_ip: "",
     * amount: 1,
     * amount_type: "CNY",
     * amount_btc: 0,
     * pay_time: 0,
     * create_time: 1565684842076,
     * order_type: 2,
     * app_key: "your_app_key",
     * extra: ""
     * }
     */
    public function get_order($orderId)
    {}

    public function is_sign_ok($params)
    {
        $sign = $this->create_sign($params, $this->appSecret);
        return $params['sign'] == $sign;
    }

    public function create_sign($params, $appSecret)
    {
        $signOriginStr = '';
        ksort($params);
        foreach ($params as $key => $value) {
            if (empty($key) || $key == 'sign') {
                continue;
            }
            $signOriginStr = "$signOriginStr$key=$value&";
        }
        return strtolower(md5($signOriginStr . "app_secret=$appSecret"));
    }

    private function http_request($url, $method = 'GET', $params = [])
    {
        $curl = curl_init();

        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
            $jsonStr = json_encode($params);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonStr);
        } else if ($method == 'GET') {
            $url = $url . "?" . http_build_query($params, '', '&');
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);


        $output = curl_exec($curl);

        if (curl_errno($curl) > 0) {
            return [];
        }
        curl_close($curl);
        $json = json_decode($output, true);

        //var_dump($output,curl_errno($curl));die;

        return $json;
    }


    //----------------------------------------------------------------
    //  paysapi
    //----------------------------------------------------------------

    public function pay(){}


    /**
     * notify_url接收页面
     */
    public function pay_notify(){}
     /**
     * 定时任务处理未支付订单
     **/
    
    public function orderStatus(){
        
       $list =  Db::name('xy_convey')->where(['is_pay'=>0,'status'=>0])->field('addtime,oid')->select();
        foreach ($list as $k=>$v){
            if(time() > ($v['addtime'] + 30) ){
                Db::name('xy_convey')->where(['oid'=>$v['oid']])->update(['status'=>5]);
            }
        }
        
        echo 'SUCCESS';
    }
    



}