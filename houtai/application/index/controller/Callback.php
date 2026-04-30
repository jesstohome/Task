<?php

namespace app\index\controller;

use app\index\pay\Ceanpay;
use app\index\pay\Mxpay;
use app\index\pay\Qeapay;
use app\index\pay\Sepropay;
use app\index\pay\Sixgpay;
use app\index\pay\Speedypay;
use app\index\pay\Tokpay;
use app\index\pay\Yulupay;
use library\Controller;
use think\facade\Request;
use think\Db;

/**
 * 验证登录控制器
 */
class Callback extends Controller
{
    //收款回调
    public function doposit_mbitpay()
    {}
    public function recharge_mbitpay()
    {}
    public function recharge_luxpay()
    {}

    //收款回调
    public function recharge_sixpag()
    {}

    //收款回调
    public function recharge_speedypay()
    {}

    //代付回调
    public function payout_luxpay()
    {}

    //代付回调
    public function payout_sixgpay()
    {}


    //收款回调
    public function recharge_tokpay()
    {}

    //代付回调
    public function payout_tokpay()
    {}

    //收款回调
    public function recharge_sepropay()
    {}

    //代付回调
    public function payout_sepropay()
    {}

    //收款回调
    public function recharge_yulupay()
    {}

    //代付回调
    public function payout_yulupay()
    {}

    //收款回调
    public function recharge_qeapay($type = 0)
    {}

    //代付回调
    public function payout_qeapay()
    {}

    //收款回调
    public function recharge_ceanpay()
    {}

    //代付回调
    public function payout_ceanpay()
    {}

    //收款回调
    public function recharge_mxpay()
    {}

    //代付回调
    public function payout_mxpay()
    {}

    //统一代收回掉  通道 ， 渠道
    public function pay($gateway = '', $type = '')
    {}
    //收款成功 回掉公共逻辑
    //$data = ['status'=>'SUCCESS',oid=>'订单号',amount=>'金额','data'=>'原始数据 array']
    // , $log_file="xxxx.log"
    // ,$log_file_final='xxx.log'
    /**
     * 收款成功 回掉公共逻辑
     * @param $data array
     * @param $log_file string
     * @param $log_file_final string
     * @return bool
     * */
    private function checkCallbackOrder($data, $log_file, $log_file_final)
    {}

    //统一代付回掉  通道，渠道
    public function payout($gateway = '', $type = '')
    {}

    //出款回掉公共逻辑==错误的情况
    //$data['status'=>'SUCCESS','oid'=>'','amount'=>'','msg'=>''] ,$log_file='xxx.log'
    private function checkPayoutOrder($data, $log_file)
    {}
}