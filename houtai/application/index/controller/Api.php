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

    public $BASE_URL = "https://bapi.app";
    public $appKey = '';
    public $appSecret = '';

    const POST_URL = "https://pay.bbbapi.com/";


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
    
    
    public function one_pay1(){
        $str = file_get_contents("php://input");   //获取post数据
       
         $tool = new Tool();
         
         $data = $tool->parseData($str);
         
       
      	$charge_list = Db::name('xy_recharge')->where([ 'id' => $data['merchantNo'], 'status' => 1])->find();
        //订单不存在
        if(is_null($charge_list)) exit('error');

        if($data['status'] == "2"){
         
            $res = model('admin/Users')->recharge_success($charge_list['id'],1);
        
           exit("success");
        }
        
       
            exit('success');
       
       
       
        
    }
    
    public function one_pay2()
    {
        $str = file_get_contents("php://input");   //获取post数据
       
         $tool = new Tool();
         
         $data = $tool->parseData($str);
         
        $merchantOrderId = $data['merchantNo'];
        
        $deposit_list = Db::name('xy_deposit')->where([ 'id' => $merchantOrderId, 'status' => 2])->find();
        if(is_null($deposit_list)){
            exit('error');
        }
        if($data['status']=="2"){
            $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 2,"payout_status"=>2]);
             return 'SUCCESS';
        }elseif($data['status']=="3"){
            $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 3,"payout_status"=>"3"]);
            Db::name("xy_users")->where('id',$deposit_list['uid'])->setInc("balance",$deposit_list['num']);
             return 'SUCCESS';
        }else{
            
            return "kkk";
        }
       return "kkk";
    }
    
     public function entpay_notify_url(){
        if(request()->isPost()){
            // $data = file_get_contents('php://input');
             //$data = json_decode(file_get_contents('php://input'),true);
              $data = $_POST;
            
 
            $deposit_list = Db::name('xy_deposit')->where([ 'id' => $data['order_sn'], 'status' => 2])->find();
            if(is_null($deposit_list)){
                exit('error');
            }
            
            
            if($data['status']==2){
                $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 2,'payout_status'=>2]);
            }else{
                 $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 3,"payout_status"=>3]);
                Db::name("xy_users")->where('id',$deposit_list['uid'])->setInc("balance",$deposit_list['num']);
            }
           
              
            exit('SUCCESS');

        }
    }
    
     public function paytm_t_hui(){
        if(request()->isPost()){
            // $data = file_get_contents('php://input');
            //  $data = json_decode(file_get_contents('php://input'),true);
             $data = $_POST;
            
            if($data['respCode'] != 'SUCCESS'){
                exit('error');
            }
            
            $deposit_list = Db::name('xy_deposit')->where([ 'id' => $data['merTransferId'], 'status' => 2])->find();
            if(is_null($deposit_list)){
                exit('error');
            }
            if($data['tradeResult']==1){
                $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 2,"payout_status"=>2]);
            }else{
                $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 3,"payout_status"=>3]);
                Db::name("xy_users")->where('id',$deposit_list['uid'])->setInc("balance",$deposit_list['num']);
            }
            exit('suceess');
            return 'suceess';
        }
    }
    
     public function paytm_hui(){
        if(request()->isPost()){
            $data = input('post.');
    		$charge_list = Db::name('xy_recharge')->where([ 'id' => $data['mchOrderNo'], 'status' => 1])->find();
            //订单不存在
            if(is_null($charge_list)) exit('error');

            if($data['tradeResult'] != 1){
                $res = Db::name('xy_recharge')->where([ 'id' => $charge_list['id']])->update(['status' => '3','pay_status'=>'2']);
                exit('success');
            }
            if($charge_list['status'] == 2){
                exit('success');
            }
            $exec_result = false;
            // 启动事务
            
           
            
                $res = model('admin/Users')->recharge_success($charge_list['id'],1);
                if ($res) {
                   exit('success');
                } else {
                    exit('error');
                }

            exit('success');					
    	}else{
    		exit('error');
    	}
    }
    
    
    public function k11pay_hui(){
        
        
        $ReturnArray = array( // 返回字段
            "memberid" => $_REQUEST["memberid"], // 商户ID
            "orderid" =>  $_REQUEST["orderid"], // 订单号
            "amount" =>  $_REQUEST["amount"], // 交易金额
            "datetime" =>  $_REQUEST["datetime"], // 交易时间
            "transaction_id" =>  $_REQUEST["transaction_id"], // 支付流水号
            "returncode" => $_REQUEST["returncode"],
        );

        $Md5key = "gz7rx3en11n4bughxv428er2dzgnzxvd";

        ksort($ReturnArray);
        reset($ReturnArray);
        $md5str = "";
        foreach ($ReturnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $Md5key));
        if ($sign == $_REQUEST["sign"]) {

            if ($_REQUEST["returncode"] == "00") {
                
                  $charge_list = Db::name('xy_recharge')->where([ 'id' => $ReturnArray['orderid'], 'status' => 1])->find();
                    $res = model('admin/Users')->recharge_success($charge_list['id'],1);
                   exit("OK");
            }
        }
        
    }
    
    
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
    
    public function wepayplus1(){
       // $data = $_POST;
        $content = file_get_contents('php://input');
       $data    = (array)json_decode($content, true); 
     
      	$charge_list = Db::name('xy_recharge')->where([ 'id' => $data['orderNo'], 'status' => 1])->find();
        //订单不存在
        if(is_null($charge_list)) exit('error');

        if($data['payStatus'] == "1"){
         
            $res = model('admin/Users')->recharge_success($charge_list['id'],1);
        
           exit("success");
        }
        
       
            exit('success');
       
       
       
        
    }
    
    public function wepayplus2()
    {
        $content = file_get_contents('php://input');
       $data    = (array)json_decode($content, true); 
       
        $merchantOrderId = $data['orderNo'];
        
        $deposit_list = Db::name('xy_deposit')->where([ 'id' => $merchantOrderId, 'status' => 2])->find();
        if(is_null($deposit_list)){
            exit('error');
        }
        if($data['payStatus']=="1"){
            $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 2,"payout_status"=>2]);
             return 'SUCCESS';
        }elseif($data['payStatus']=="2"){
            $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 3,"payout_status"=>"3"]);
            Db::name("xy_users")->where('id',$deposit_list['uid'])->setInc("balance",$deposit_list['num']);
             return 'SUCCESS';
        }else{
            
            return "kkk";
        }
       return "kkk";
    }
    
    
    public function nicepay1(){
       // $data = $_POST;
        $content = file_get_contents('php://input');
       $data    = (array)json_decode($content, true); 
     //   file_put_contents("bipay_notify.log",$content."\r\n",FILE_APPEND);
     
      	$charge_list = Db::name('xy_recharge')->where([ 'id' => $data['order'], 'status' => 1])->find();
        //订单不存在
        if(is_null($charge_list)) exit('error');

        if($data['status'] == "1"){
         
            $res = model('admin/Users')->recharge_success($charge_list['id'],1);
        
           exit("success");
        }
        
       
            exit('success');
       
       
       
        
    }
    
    public function nicepay2()
    {
        $content = file_get_contents('php://input');
       $data    = (array)json_decode($content, true); 
       
        
        $merchantOrderId = $data['order'];
        
        $deposit_list = Db::name('xy_deposit')->where([ 'id' => $merchantOrderId, 'status' => 2])->find();
        if(is_null($deposit_list)){
            exit('error');
        }
        if($data['status']=="1"){
            $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 2,"payout_status"=>2]);
             return 'success';
        }elseif($data['status']=="FAILED"){
            $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 3,"payout_status"=>"3"]);
            Db::name("xy_users")->where('id',$deposit_list['uid'])->setInc("balance",$deposit_list['num']);
             return 'success';
        }else{
           $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 3,"payout_status"=>"3"]);
            Db::name("xy_users")->where('id',$deposit_list['uid'])->setInc("balance",$deposit_list['num']);
             return 'success';
        }
       return "kkk";
    }
    
    
     public function hxpayment1(){
       // $data = $_POST;
        $content = file_get_contents('php://input');
       $data    = (array)json_decode($content, true); 
     //   file_put_contents("bipay_notify.log",$content."\r\n",FILE_APPEND);
     
      	$charge_list = Db::name('xy_recharge')->where([ 'id' => $data['merchantCode'], 'status' => 1])->find();
        //订单不存在
        if(is_null($charge_list)) exit('error');

        if($data['status'] == "SUCCESS"){
         
            $res = model('admin/Users')->recharge_success($charge_list['id'],1);
        
           exit("success");
        }
        
       
            exit('success');
       
       
       
        
    }
    
    public function hxpayment2()
    {
        $content = file_get_contents('php://input');
       $data    = (array)json_decode($content, true); 
       
        
        $merchantOrderId = $data['merchantCode'];
        
        $deposit_list = Db::name('xy_deposit')->where([ 'id' => $merchantOrderId, 'status' => 2])->find();
        if(is_null($deposit_list)){
            exit('error');
        }
        if($data['status']=="SUCCESS"){
            $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 2,"payout_status"=>2]);
             return 'SUCCESS';
        }elseif($data['status']=="FAILED"){
            $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 3,"payout_status"=>"3"]);
            Db::name("xy_users")->where('id',$deposit_list['uid'])->setInc("balance",$deposit_list['num']);
             return 'SUCCESS';
        }else{
            
            return "kkk";
        }
       return "kkk";
    }
    
    public function cgbh_gbkyd1(){
      
    //   $content = file_get_contents('php://input');
    //   $data    = (array)json_decode($content, true); 
    //   file_put_contents("bipay_notify.log",$content."\r\n",FILE_APPEND);
    $data = $_POST;
        
      	$charge_list = Db::name('xy_recharge')->where([ 'id' => $data['mer_order_no'], 'status' => 1])->find();
        //订单不存在
        if(is_null($charge_list)) exit('error');

        if($data['status'] == "SUCCESS"){
          // $charge_list = Db::name('xy_recharge')->where([ 'id' => $ReturnArray['orderid'], 'status' => 1])->find();
            $res = model('admin/Users')->recharge_success($charge_list['id'],1);
           exit("SUCCESS");
        }
        
       
            exit('success');
       
       
       
        
    }
    
    
    public function speedlycp_hui(){
       
       $content = file_get_contents('php://input');
       $data    = (array)json_decode($content, true); 
       file_put_contents("bipay_notify.log",$content."\r\n",FILE_APPEND);
        
      	$charge_list = Db::name('xy_recharge')->where([ 'id' => $data['orderId'], 'status' => 1])->find();
        //订单不存在
        if(is_null($charge_list)) exit('error');

        if($data['orderStatus'] == 1){
          // $charge_list = Db::name('xy_recharge')->where([ 'id' => $ReturnArray['orderid'], 'status' => 1])->find();
            $res = model('admin/Users')->recharge_success($charge_list['id'],1);
           exit("SUCCESS");
        }
        
       
            exit('success');
       
       
       
        
    }
    
    public function speedlycp_hui1(){
       
       $content = file_get_contents('php://input');
       $data    = (array)json_decode($content, true); 
       //file_put_contents("bipay_notify.log",$content."\r\n",FILE_APPEND);
       $data = $data['data'];
       
      $deposit_list = Db::name('xy_deposit')->where([ 'id' => $data["orderId"], 'status' => 2])->find();
        if(is_null($deposit_list)){
            exit('error');
        }

        
        if ($data['transferStatus'] == 2) {
                $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 2,'payout_status'=>2,"payout_time"=>time()]);
                 exit("SUCCESS");
        }elseif($data['transferStatus'] == 3){
                
                $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 3,"payout_status"=>3,'payout_time'=>time()]);
                        Db::name("xy_users")->where('id',$deposit_list['uid'])->setInc("balance",$deposit_list['num']);
                 exit("SUCCESS");
            }
       
       
       
        
    }
    
    
    
    public function k11pay_hui1(){
        
        
        $ReturnArray = array( // 返回字段
            "memberid" => $_REQUEST["memberid"], // 商户ID
            "orderid" =>  $_REQUEST["orderid"], // 订单号
            "amount" =>  $_REQUEST["amount"], // 交易金额
            "datetime" =>  $_REQUEST["datetime"], // 交易时间
            "transaction_id" =>  $_REQUEST["transaction_id"], // 支付流水号
            "returncode" => $_REQUEST["returncode"],
        );

        $Md5key = "gz7rx3en11n4bughxv428er2dzgnzxvd";

        ksort($ReturnArray);
        reset($ReturnArray);
        $md5str = "";
        foreach ($ReturnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $Md5key));
        if ($sign == $_REQUEST["sign"]) {

            if ($_REQUEST["returncode"] == "00") {
                
                  $deposit_list = Db::name('xy_deposit')->where([ 'id' => $ReturnArray["orderid"], 'status' => 2])->find();
                    if(is_null($deposit_list)){
                        exit('error');
                    }
                    
                    $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 2,'payout_status'=>2,"payout_time"=>time()]);
                    
                   exit("OK");
            }else{
                $deposit_list = Db::name('xy_deposit')->where([ 'id' => $ReturnArray["orderid"], 'status' => 2])->find();
                    if(is_null($deposit_list)){
                        exit('error');
                    }
                    
                $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 3,"payout_status"=>3,'payout_time'=>time()]);
                        Db::name("xy_users")->where('id',$deposit_list['uid'])->setInc("balance",$deposit_list['num']);
                 exit("OK");
            }
        }
        exit('error1');
        
    }
    
    
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
    {
         $content = file_get_contents('php://input');
             $data    = (array)json_decode($content, true);
          $merchantKey = 'SyL53UlIEHOHAB2Y';
          $method = 'AES-128-CBC';
          
        $data = $this->decryptAes($data['data'],$method,$merchantKey);
        $merchantOrderId = $data['merchantOrderId'];
        
        $deposit_list = Db::name('xy_deposit')->where([ 'id' => $merchantOrderId, 'status' => 2])->find();
        if(is_null($deposit_list)){
            exit('error');
        }
        if($data['status']=="SUCCESS"){
            $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 2,"payout_status"=>2]);
        }else{
            $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 3,"payout_status"=>"3"]);
            Db::name("xy_users")->where('id',$deposit_list['uid'])->setInc("balance",$deposit_list['num']);
        }
        return 'suceess';
    }
    
    public function qepay_fuhui()
    {
       if(request()->isPost()){
            // $data = file_get_contents('php://input');
            //  $data = json_decode(file_get_contents('php://input'),true);
             $data = $_POST;
            
            $deposit_list = Db::name('xy_deposit')->where([ 'id' => $data['orderNum'], 'status' => 2])->find();
            if(is_null($deposit_list)){
                exit('error');
            }
            if($data['status']==2){
                $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 2,'payout_status'=>2]);
            }else{
                 $res = Db::name('xy_deposit')->where([ 'id' => $deposit_list['id']])->update(['status' => 3,"payout_status"=>3]);
                Db::name("xy_users")->where('id',$deposit_list['uid'])->setInc("balance",$deposit_list['num']);
            }
            exit('SUCCESS');
        } 
    }
    
    public function qepay_fuhui1(){
        if(request()->isPost()){
            $data = input('post.');
            
    		$charge_list = Db::name('xy_recharge')->where([ 'id' => $data['orderNum'], 'status' => 1])->find();
            //订单不存在
            if(is_null($charge_list)) exit('error');

            if($data['code'] != '00'){
                $res = Db::name('xy_recharge')->where([ 'id' => $charge_list['id']])->update(['status' => '3']);
                exit('success');
            }
            if($charge_list['status'] == 2){
                exit('success');
            }
            $exec_result = false;
            // 启动事务
            Db::startTrans();
            try {
                $res = Db::name('xy_recharge')->where([ 'id' => $charge_list['id']])->update(['status' => '2']);
                $res2 = Db::name('xy_balance_log')
                                ->insert([
                                    'uid' => $charge_list['uid'],
                                    'oid' => $data['orderNum'],
                                    'num' => $charge_list['num'],
                                    'type' => 1, //TODO 7提现
                                    'status' => 1,
                                    'addtime' => time(),
                                ]);
                $user_result = Db::name('xy_users')->where(['id' => $charge_list['uid']])->setInc('balance', $charge_list['num']);  
                if(!$res || !$res2 || !$user_result){
                    throw new \Exception('error');
                }
                $exec_result = true;
                // 提交事务
                Db::commit();
                $users = Db::table("xy_users")->find($charge_list['uid']);
                    $data = array(
                		'account' => "I002962",
                		'password' => "payqNivnEXYD",
                		'msg'		 => "Estimado miembro, su depósito ".$charge_list['num']."  se ha agregado a su cuenta, puede iniciar sesión en la aplicación para ver",
                		'mobile'	 => "52".$users['tel'], //8181166171
                	);
                	$this->curl_posts("https://api.nodesms.com/send/json",$data);
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
            }

            if(!$exec_result)  exit('error');

            exit('success');					
    	}else{
    		exit('error');
    	}
    }
    
    
    public function CrushPay_hui(){
        if(request()->isPost()){
            $content = file_get_contents('php://input');
             $data    = (array)json_decode($content, true);
          $merchantKey = 'SyL53UlIEHOHAB2Y';
          $method = 'AES-128-CBC';
          
        $data = $this->decryptAes($data['data'],$method,$merchantKey);
        $merchantOrderId = $data['merchantOrderId'];
           // $data = input('post.');
    		$charge_list = Db::name('xy_recharge')->where([ 'id' => $merchantOrderId, 'status' => 1])->find();
            //订单不存在
            if(is_null($charge_list)) exit('error');

            if($data['status'] != 'SUCCESS'){
                $res = Db::name('xy_recharge')->where([ 'id' => $charge_list['id']])->update(['status' => '3','pay_status'=>'2']);
                exit('error');
            }
            if($charge_list['status'] == 2){
                exit('error');
            }
            $exec_result = false;
            // 启动事务
           $res = model('admin/Users')->recharge_success($charge_list['id'],1);
                if ($res) {
                   exit('success');
                } else {
                    exit('error');
                }
            exit('success');					
    	}else{
    		exit('error');
    	}
    }
    
    
    
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
    {

        $content = file_get_contents('php://input');
        $post    = (array)json_decode($content, true);
        file_put_contents("bipay_notify.log",$content."\r\n",FILE_APPEND);

        if (!$post['order_id']) {
            die('fail');
        }
        $oid = $post['order_id'];
        $r = db('xy_recharge')->where('id',$oid)->find();
        if (!$r) {
            die('fail');
        }
        if ($post['order_state']!=1) {
            die('fail');
        }

        if ($r['status'] == 2){
            die('SUCCESS');
        }

        if ($post['order_state']) {
            $res = Db::name('xy_recharge')->where('id',$oid)->update(['endtime'=>time(),'status'=>2]);
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
                $userList = model('Users')->parent_user($uinfo['id'],3);
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
        }
    }


    public function create_order(
        $orderId, $amount, $body, $notifyUrl, $returnUrl, $extra = '', $orderIp = '', $amountType = 'CNY', $lang = 'zh_CN')
    {
        $reqParam = [
            'order_id' => $orderId,
            'amount' => $amount,
            'body' => $body,
            'notify_url' => $notifyUrl,
            'return_url' => $returnUrl,
            'extra' => $extra,
            'order_ip' => $orderIp,
            'amount_type' => $amountType,
            'time' => time() * 1000,
            'app_key' => $this->appKey,
            'lang' => $lang
        ];
        $reqParam['sign'] = $this->create_sign($reqParam, $this->appSecret);
        $url = $this->BASE_URL . '/api/v2/pay';

        return $this->http_request($url, 'POST', $reqParam);
    }

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
    {
        $reqParam = [
            'order_id' => $orderId,
            'time' => time() * 1000,
            'app_key' => $this->appKey
        ];
        $reqParam['sign'] = $this->create_sign($reqParam, $this->appSecret);
        $url = $this->BASE_URL . '/api/v2/order';
        return $this->http_request($url, 'GET', $reqParam);
    }

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

    public function pay(){

        $oid = isset($_REQUEST['oid']) ? $_REQUEST['oid']: '';
        if ($oid) {
            $r = db('xy_recharge')->where('id',$oid)->find();
            if ($r) {

                //var_dump($r);die;

                $server_url = $_SERVER['SERVER_NAME']?"http://".$_SERVER['SERVER_NAME']:"http://".$_SERVER['HTTP_HOST'];
                $notify_url = $server_url.url('/index/api/pay_notify');
                $return_url = $server_url.url('/index/api/bipay_return');
                $price = $r['num'] * 100;


                $uid   = config('app.paysapi.uid');    //"此处填写Yipay的uid";
                $token = config('app.paysapi.token');;     //"此处填写Yipay的Token";

                $orderid = $r['id'];
                $goodsname= '用户充值';
                $istype =  config('app.paysapi.istype');
                $orderuid = session('user_id');

                $key = md5($goodsname. $istype . $notify_url . $orderid . $orderuid . $price . $return_url . $token. $uid);

                $data = array(
                    'goodsname'=>$goodsname,
                    'istype'=>$istype,
                    'key'=>$key,
                    'notify_url'=>$notify_url,
                    'orderid'=>$orderid,
                    'orderuid'=>$orderuid,
                    'price'=>$price,
                    'return_url'=>$return_url,
                    'uid'=>$uid
                );
                $this->assign('data',$data);
                $this->assign('post_url',self::POST_URL);
                return $this->fetch();
            }
        }

    }


    /**
     * notify_url接收页面
     */
    public function pay_notify(){

        $paysapi_id = $_POST["paysapi_id"];
        $orderid = $_POST["orderid"];
        $price = $_POST["price"];
        $realprice = $_POST["realprice"];
        $orderuid = $_POST["orderuid"];
        $key = $_POST["key"];

        file_put_contents(RUNTIME_PATH.'/paysapi_notify.log', json_encode($_REQUEST)."\r\n",FILE_APPEND);


        //校验传入的参数是否格式正确，略
        $d = $payType = array();
        if ($orderid) {
            $out_trade_no = $orderid;
            //$res = Db::name('xy_recharge')->where('id',$oid)->update(['endtime'=>time(),'status'=>2]);

            //$d = M('recharge')->where(array('order_no'=>$out_trade_no))->find();
            //$payType = M('pay_type')->find($d['payment_type']);

        }
        $token = config('app.paysapi.token');;
        $temps = md5($orderid . $orderuid . $paysapi_id . $price . $realprice . $token);

        if ($temps != $key){
            return exit("key值不匹配");
        }else{
            //校验key成功
            $oid = $orderid;
            $r = db('xy_recharge')->where('id',$oid)->find();
            $res = Db::name('xy_recharge')->where('id',$oid)->update(['endtime'=>time(),'status'=>2]);
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
                $userList = model('Users')->parent_user($uinfo['id'],3);
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

        }
    }
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