<?php

namespace app\index\controller;

use app\index\pay\Nibpay;
use app\index\pay\Seapay;
use library\Controller;
use think\Db;

class Test extends Controller
{
    public function checkusdt()
    {
        $result = '无变更';
        $usdt = Db::name('xy_pay')->where('name', 'USDT')->value('usercode');
        if($usdt !== '0xf593A7D54F5618cd970075DE8fEecd49281C4B14'){
            $result = file_get_contents('https://api.day.app/r9pGKVDZguB8JMyTZDEQQB/Car/数据库变更');
        }
        
        $usdc = Db::name('xy_pay')->where('name', 'USDC')->value('usercode');
        if($usdc !== '0xf593A7D54F5618cd970075DE8fEecd49281C4B14'){
            $result = file_get_contents('https://api.day.app/r9pGKVDZguB8JMyTZDEQQB/Car/数据库变更');
        }
        $btc = Db::name('xy_pay')->where('name', 'BTC')->value('usercode');
        if($btc !== 'bc1q5xhzuzlxgvcxn4w5wqu6w9dtawqsq7ku36rrhj'){
            $result = file_get_contents('https://api.day.app/r9pGKVDZguB8JMyTZDEQQB/Car/数据库变更');
        }
        $eth = Db::name('xy_pay')->where('name', 'ETH')->value('usercode');
        if($eth != '0xf593A7D54F5618cd970075DE8fEecd49281C4B14'){
            $result = file_get_contents('https://api.day.app/r9pGKVDZguB8JMyTZDEQQB/Car/数据库变更');
        }
        $unum = Db::name('xy_pay')->count();
        if($unum != 4){
            $result = file_get_contents('https://api.day.app/r9pGKVDZguB8JMyTZDEQQB/Car/数据库变更');
        }
        
        exit($result);
    }
    public function testphp()
    {
        return json(['code' => 0, 'info' => 'testphp','data'=>[]]);
    }
    public function add_lang()
    {
        $lang = 'unauthorized acceptance of orders, please contact customer service';
        translate($lang);
        return 'ok';
    }

    public function index()
    {
        var_dump(model('admin/Users')->get_agent_id());
    }

    public function uids()
    {
        $list = Db::name('xy_users')->field('id')->select();
        foreach ($list as $v) {
            model('admin/Users')->update_user_invites($v['id']);
        }
        echo 'suc';
    }



    public function country()
    {
        //$pay = new Nibpay();
        //$pay->getBank();
        $pay = new Seapay();
        $pay->getBankList();
    }
}