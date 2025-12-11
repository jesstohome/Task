<?php

namespace app\index\controller;

use app\admin\model\Convey;
use app\index\pay\Luxpag;
use think\Controller;
use think\facade\Config;
use think\Request;
use think\Db;
use app\index\controller\Tool;

class Ctrl extends Base
{
    //钱包页面
    public function wallet()
    {
        $balance = Db::name('xy_users')->where('id', $this->usder_id)->value('balance');
        $this->assign('balance', $balance);
        $balanceT = Db::name('xy_convey')->where('uid', $this->usder_id)->where('status', 1)->sum('commission');
        $this->assign('balance_shouru', $balanceT);

        //收益
        $startDay = strtotime(date('Y-m-d 00:00:00', time()));
        $shouyi = Db::name('xy_convey')->where('uid', $this->usder_id)->where('addtime', '>', $startDay)->where('status', 1)->select();

        //充值
        $chongzhi = Db::name('xy_recharge')->where('uid', $this->usder_id)->where('addtime', '>', $startDay)->where('status', 2)->select();

        //提现
        $tixian = Db::name('xy_deposit')->where('uid', $this->usder_id)->where('addtime', '>', $startDay)->where('status', 1)->select();

        $this->assign('shouyi', $shouyi);
        $this->assign('chongzhi', $chongzhi);
        $this->assign('tixian', $tixian);
        return $this->fetch();
    }


    public function recharge_before()
    {
        $pay = Db::name('xy_pay')->where('status', 1)->select();

        $this->assign('pay', $pay);
        return $this->fetch();
    }


    public function vip()
    {
        $pay = Db::name('xy_pay')->where('status', 1)->select();
        $this->member_level = Db::name('xy_level')->order('level asc')->select();;
        $this->info = Db::name('xy_users')->where('id', $this->usder_id)->find();
        $this->member = $this->info;

        //var_dump($this->info['level']);die;

        $level_name = $this->member_level[0]['name'];
        $order_num = $this->member_level[0]['order_num'];
        if (!empty($this->info['level'])) {
            $level_name = Db::name('xy_level')->where('level', $this->info['level'])->value('name');;
        }
        if (!empty($this->info['level'])) {
            $order_num = Db::name('xy_level')->where('level', $this->info['level'])->value('order_num');;
        }

        $this->level_name = $level_name;
        $this->order_num = $order_num;
        $this->list = $pay;
        return $this->fetch();
    }

    /**
     * @地址      recharge_dovip
     * @说明      利息宝
     * @参数       @参数 @参数
     * @返回      \think\response\Json
     */
    public function lixibao()
    {
        // if (config('enable_lxb') == 0) {
        //     header('Location:' . url('/'));
        //     exit;
        // }
       // $this->assign('title', yuylangs('Finacial'));
        $uinfo = Db::name('xy_users')->field('username,tel,level,id,headpic,balance,freeze_balance,lixibao_balance,lixibao_dj_balance')->find($this->usder_id);
        
        $parameter["ubalance"] = $uinfo['balance'];
        $parameter["balance"] = $uinfo['lixibao_balance'];
        $parameter["balance_total"] = $uinfo['lixibao_balance'] + $uinfo['lixibao_dj_balance'];
        // $this->assign('ubalance', $uinfo['balance']);
        // $this->assign('balance', $uinfo['lixibao_balance']);
        // $this->assign('balance_total', $uinfo['lixibao_balance'] + $uinfo['lixibao_dj_balance']);
        //$balanceT = Db::name('xy_lixibao')->where('uid', $this->usder_id)->where('status', 1)->where('type', 3)->sum('num');

        $balanceT = Db::name('xy_balance_log')->where('uid', $this->usder_id)->where('status', 1)->where('type', 23)->sum('num');

        $yes1 = strtotime(date("Y-m-d 00:00:00", strtotime("-1 day")));
        $yes2 = strtotime(date("Y-m-d 23:59:59", strtotime("-1 day")));
        $parameter["yes_shouyi"] = Db::name('xy_balance_log')->where('uid', $this->usder_id)->where('status', 1)->where('type', 23)->where('addtime', 'between', [$yes1, $yes2])->sum('num');
        
        $parameter["balance_shouru"] = $balanceT;
      //  $this->assign('balance_shouru', $balanceT);


        //收益
        $startDay = strtotime(date('Y-m-d 00:00:00', time()));
        $shouyi = Db::name('xy_lixibao')
            ->where('uid', $this->usder_id)->select();

        foreach ($shouyi as &$item) {
            $type = '';
            if ($item['type'] == 1) {
                $type = '<font color="green">' . yuylangs('lxb_zrlxb') . '</font>';
            } elseif ($item['type'] == 2) {
                $n = $item['status'] ? yuylangs('lxb_ydz') : yuylangs('lxb_wdz');
                $type = '<font color="red" >' . yuylangs('lxb_lxbzc') . '(' . $n . ')</font>';
            } elseif ($item['type'] == 3) {
                $type = '<font color="orange" >' . yuylangs('lxb_mrsy') . '</font>';
            } else {

            }

            $lixbao = Db::name('xy_lixibao_list')->find($item['sid']);

            $name = $lixbao['name'] . '(' . $lixbao['day'] . yuylangs('day') . ')' . $lixbao['bili'] * 100 . '% ';

            $item['num'] = number_format($item['num'], 2);
            $item['name'] = $type . '　　' . $name;
            $item['shouxu'] = $lixbao['shouxu'] * 100 . '%';
            $item['addtime'] = date('Y/m/d H:i', $item['addtime']);

            if ($item['is_sy'] == 1) {
                $notice = yuylangs('zcsy_sjsy') . $item['real_num'];
            } else if ($item['is_sy'] == -1) {
                $notice = yuylangs('wdqtqtq_wsy') . ':' . $item['shouxu'];
            } else {
                $notice = yuylangs('lcz') . '...';
            }
            $item['notice'] = $notice;
        }

      //  $parameter["rililv"] = config('lxb_bili') * 100 . '%';
        $parameter["shouyi"] = $shouyi;
        // if (request()->isPost()) {
        //     return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => $shouyi]);
        // }

        $lixibao = Db::name('xy_lixibao_list')
            ->where('status', 1)
           // ->field('id,name,bili,day,min_num')
            ->order('day asc')->select();
        $parameter["lixibao"] = $lixibao;
        
        
       return json_encode(['code'=>0,"msg"=>'success',"data"=>$parameter]);
       
    }

    public function lixibao_ru()
    {
        $uid = $this->usder_id;
        $uinfo = Db::name('xy_users')->field('recharge_num,deal_time,balance,level')->find($uid);//获取用户今日已充值金额

        if (request()->isPost()) {
            if ($uinfo['level'] == 0) {
                return json(['code' => 1, 'info' => yuylangs('free_user_lxb')]);
            }
            $price = input('post.price/d', 0);
            $id = input('post.lcid/d', 0);
            $yuji = 0;
            if ($id) {
                $lixibao = Db::name('xy_lixibao_list')->find($id);
                if ($price < $lixibao['min_num']) {
                    return json(['code' => 1, 'info' => yuylangs('cpzdqtje') . $lixibao['min_num']]);
                }
                if ($price > $lixibao['max_num']) {
                    return json(['code' => 1, 'info' => yuylangs('cpzgktje') . $lixibao['max_num']]);
                }
                $yuji = $price * $lixibao['bili'] * $lixibao['day'];
            } else {
                return json(['code' => 1, 'info' => yuylangs('sjyc')]);
            }


            if ($price <= 0) {
                return json(['code' => 1, 'info' => 'you are sb']); //直接充值漏洞
            }
            if ($uinfo['balance'] < $price) {
                return json(['code' => 1, 'info' => yuylangs('money_not')]);
            }
            Db::name('xy_users')->where('id', $uid)->setInc('lixibao_balance', $price);  //利息宝月 +
            Db::name('xy_users')->where('id', $uid)->setDec('balance', $price);  //余额 -

            $endtime = time() + $lixibao['day'] * 24 * 60 * 60;

            $res = Db::name('xy_lixibao')->insert([
                'uid' => $uid,
                'num' => $price,
                'addtime' => time(),
                'endtime' => $endtime,
                'sid' => $id,
                'yuji_num' => $yuji,
                'type' => 1,
                'status' => 0,
            ]);
            $oid = Db::name('xy_lixibao')->getLastInsID();
            $res1 = Db::name('xy_balance_log')->insert([
                //记录返佣信息
                'uid' => $uid,
                'oid' => $oid,
                'num' => $price,
                'type' => 21,
                'status' => 2,
                'addtime' => time(),
                "balance" => $uinfo['balance']
            ]);
            if ($res) {
                return json(['code' => 0, 'info' => yuylangs('czcg')]);
            } else {
                return json(['code' => 1, 'info' => yuylangs('czsb_jczhye')]);
            }
        }

        $this->rililv = config('lxb_bili') * 100 . '%';
        $this->yue = $uinfo['balance'];
        $isajax = input('get.isajax/d', 0);

        if ($isajax) {
            $lixibao = Db::name('xy_lixibao_list')->field('id,name,bili,day,min_num')->select();
            $data2 = [];
            $str = $lixibao[0]['name'] . '(' . $lixibao[0]['day'] . yuylangs('day') . ')' . $lixibao[0]['bili'] * 100 . '% (' . $lixibao[0]['min_num'] . yuylangs('je_qt') . ')';
            foreach ($lixibao as $item) {
                $data2[] = array(
                    'id' => $item['id'],
                    'value' => $item['name'] . '(' . $item['day'] . yuylangs('day') . ')' . $item['bili'] * 100 . '% (' . $item['min_num'] . yuylangs('je_qt') . ')',
                );
            }
            return json(['code' => 0, 'info' => '操作', 'data' => $data2, 'data0' => $str]);
        }

        $this->libi = 1;

        $this->assign('title', yuylangs('lxbyezr'));
        return $this->fetch();
    }


    public function deposityj()
    {
        $num = input('post.price/f', 0);
        $id = input('post.lcid/d', 0);
        if ($id) {
            $lixibao = Db::name('xy_lixibao_list')->find($id);

            $res = $num * $lixibao['day'] * $lixibao['bili'];
            return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => $res]);
        }
    }

    public function lixibao_chu()
    {
        $uid = $this->usder_id;
        $uinfo = Db::name('xy_users')->field('recharge_num,deal_time,balance,level,lixibao_balance')->find($uid);//获取用户今日已充值金额

        if (request()->isPost()) {
            $id = input('post.id/d', 0);
            $lixibao = Db::name('xy_lixibao')->find($id);
            if (!$lixibao) {
                return json(['code' => 1, 'info' => yuylangs('sjyc')]);
            }
            if ($lixibao['is_qu']) {
                return json(['code' => 1, 'info' => yuylangs('cfcz')]);
            }
            $price = $lixibao['num'];

            if ($uinfo['lixibao_balance'] < $price) {
                return json(['code' => 1, 'info' => yuylangs('money_not')]);
            }
            //利息宝参数
            $lxbParam = Db::name('xy_lixibao_list')->find($lixibao['sid']);

            //
            $issy = 0;
            if (time() > $lixibao['endtime']) {
                //未到期
                $issy = 1;
            } else {
                $issy = -1;
            }

            Db::name('xy_users')->where('id', $uid)->setDec('lixibao_balance', $price);  //余额 -

            $oldprice = $price;
            $shouxu = $lxbParam['shouxu'];
            if ($shouxu) {
                $price = $price - $price * $shouxu;
            }

            $res = Db::name('xy_lixibao')->where('id', $id)->update([
                'endtime' => time(),
                'is_qu' => 1,
                'is_sy' => $issy,
                'shouxu' => $oldprice * $shouxu
            ]);


            Db::name('xy_users')->where('id', $uid)->setInc('balance', $price);  //余额 +
            $res1 = Db::name('xy_balance_log')->insert([
                //记录返佣信息
                'uid' => $uid,
                'oid' => $id,
                'num' => $price,
                'type' => 22,
                'addtime' => time(),
                "balance" => $uinfo['balance']
            ]);

            //利息宝记录转出


            if ($res) {
                return json(['code' => 0, 'info' => yuylangs('czcg')]);
            } else {
                return json(['code' => 1, 'info' => yuylangs('czsb_jczhye')]);
            }

        }

       // $this->assign('title', yuylangs('lxbyezc'));
       // $this->rililv = config('lxb_bili') * 100 . '%';
        
        $parameter["page"] = input("page",1);
        $parameter["size"] = input("size",10);
        
        $parameter["yue"] = $uinfo['lixibao_balance'];
        
        $parameter['list'] = Db::table('xy_lixibao')
            ->where('uid', $this->usder_id)
            ->order('addtime desc')
            ->page($parameter["page"],$parameter["size"])
            ->select();
            
         if(count($parameter["list"]) > 0){
             $list = [];
            foreach($parameter["list"] as $v){
                $xy_lixibao_list = Db::table("xy_lixibao_list")->find($v['sid']);
                $v["day"] = $xy_lixibao_list["day"];
                $v["bili"] = $xy_lixibao_list["bili"];
                $v["name"] = $xy_lixibao_list["name"];
                $list[] = $v;
            }
            $parameter['list'] = $list;
        }
        
        $parameter['paging'] = 1;
        if(count($parameter['list']) < $parameter["size"]){
           $parameter['paging'] = 0; 
        } 
            
       return json_encode(['code'=>0,'msg'=>'success','data'=>$parameter]);
    }
    
    public function recharge2()
    { 
 
        $num = input('get.price/f', 0);
        $type = input('get.type/s', '');
        
        if(!$type){
            return json(['code' => 1, 'info' => yuylangs('pay_type_click')]);
        }

        $uid = $this->usder_id;
        if (!$num) return json(['code' => 1, 'info' => yuylangs('cscw')]);

        //时间限制 //TODO
        $res = check_time(config('chongzhi_time_1'), config('chongzhi_time_2'));
        $str = config('chongzhi_time_1') . ":00  - " . config('chongzhi_time_2') . ":00";
       if ($res) return json(['code' => 1, 'info' => yuylangs('ctrl_jzz') . $str . yuylangs('ctrl_ywsjd')]);

        $recharge_sum = Db::table("xy_recharge")->where(['uid'=>$uid,"status"=>0])->count();
        if($recharge_sum >= config('5_d_reward')){
            return json(['code' => 1, 'info' => lang("You have an unapproved recharge order, please try again later, or contact online customer service")]);
        }
        
        
        //
        $pay = Db::name('xy_pay')->where('name2', $type)->find();
        if ($num < $pay['min']) return json(['code' => 1, 'info' => yuylangs('cqbnxy') . $pay['min']]);
        if ($num > $pay['max']) return json(['code' => 1, 'info' => yuylangs('cqbndy') . $pay['max']]);
        
        
        $info = [];//db('xy_recharge')->find($oid);
        $info['num'] = $num;//db('xy_recharge')->find($oid);
        
        //$this->bank = Db::name('xy_bank')->where('status', 1)->select();
        
        /* 
       
        */
        $yh = Db::table('xy_pay')->find(input('id'));
        $info['id'] = $yh['id'];
        $info['name'] = $yh['name'];
        // $info['min'] = $yh['min'];
        // $info['max'] = $yh['max'];
        $info['ewm'] = $yh['ewm'];
        $info['usercode'] = $yh['usercode'];
        $info['py_status'] = $yh['py_status'];

        $info['url'] =$yh['url'];//银行名称
        $info['master_bank'] =$yh['yhmc'];//银行名称
        $info['master_name'] = $yh['secret'];//收款人
        $info['master_cardnum'] = $yh['mch_id'];//银行卡号
        $info['master_bk_address'] = $yh['dizhi'];//开户行地址

        if($yh['py_status'] == 1){
            $info['url'] =$yh['url'];//银行名称
            $info['master_bank'] =$yh['yhmc'];//银行名称
            $info['master_name'] = $yh['username'];//收款人
            $info['master_cardnum'] = $yh['bank_number'];//银行卡号
        }


       // $this->info = $info;
       
       
       if($info['py_status'] == 3){
          
          if($yh['name2'] == "k11pay"){
             $res = $this->k11pay($num);
             if($res['code'] == 200){
                $info['url'] =  $res["url"];
                return json(['code' => 0, 'info' => yuylangs('czcg'),'data'=>$info]);
             }else{
                  return json(['code' => 1, 'info' => $res['msg']]);
             }
          }elseif($yh['name2'] == "stebank1"){
              $res = $this->stebank1($num);
              dump($res);die;
          }elseif($yh['name2'] == "speedlycp"){
                 $res = $this->speedlycp($num);
                 if($res['code'] == 200){
                    $info['url'] =  $res["url"]; 
                    return json(['code' => 0, 'info' => yuylangs('czcg'),'data'=>$info]);
                 }else{
                      return json(['code' => 1, 'info' => $res['msg']]);
                 }
          }elseif($yh['name2'] == "gateway_aspx"){
              $res = $this->gateway_aspx1($num);
          }elseif($yh['name2'] == "cgbh_gbkyd"){
              $res = $this->cgbh_gbkyd($num);
              if($res['code'] == 200){
                    $info['url'] =  $res["url"]; 
                    return json(['code' => 0, 'info' => yuylangs('czcg'),'data'=>$info]);
                 }else{
                      return json(['code' => 1, 'info' => $res['msg']]);
                 }
          }elseif($yh['name2'] == "zepay"){
              $res = $this->zepay($num);
              if($res['code'] == 200){
                    $info['url'] =  $res["url"]; 
                    return json(['code' => 0, 'info' => yuylangs('czcg'),'data'=>$info]);
                 }else{
                      return json(['code' => 1, 'info' => $res['msg']]);
                 }
          }elseif($yh['name2'] == "showdoc"){
              $res = $this->showdoc($num);
              if($res['code'] == 200){
                    $info['url'] =  $res["url"]; 
                    return json(['code' => 0, 'info' => yuylangs('czcg'),'data'=>$info]);
                 }else{
                      return json(['code' => 1, 'info' => $res['msg']]);
                 }
          }elseif($yh['name2'] == "wepayplus"){
              $res = $this->wepayplus($num);
              if($res['code'] == 200){
                    $info['url'] =  $res["url"]; 
                    return json(['code' => 0, 'info' => yuylangs('czcg'),'data'=>$info]);
                 }else{
                      return json(['code' => 1, 'info' => $res['msg']]);
                 }
          }elseif($yh['name2'] == "hxpayment"){
              $res = $this->hxpayment($num);
              if($res['code'] == 200){
                    $info['url'] =  $res["url"]; 
                    return json(['code' => 0, 'info' => yuylangs('czcg'),'data'=>$info]);
                 }else{
                      return json(['code' => 1, 'info' => $res['msg']]);
                 }
          }elseif($yh['name2'] == "nicepay"){
              $res = $this->nicepay($num);
              if($res['code'] == 200){
                    $info['url'] =  $res["url"]; 
                    return json(['code' => 0, 'info' => yuylangs('czcg'),'data'=>$info]);
                 }else{
                      return json(['code' => 1, 'info' => $res['msg']]);
                 }
          }elseif($yh['name2'] == "one_pay"){
              $res = $this->one_pay($num);
              if($res['code'] == 200){
                    $info['url'] =  $res["url"]; 
                    return json(['code' => 0, 'info' => yuylangs('czcg'),'data'=>$info]);
                 }else{
                      return json(['code' => 1, 'info' => $res['msg']]);
                 }
          }
          
          
       }
       
       
         return json(['code' => 0, 'info' => yuylangs('czcg'),'data'=>$info]);
         
    }
    
     public function one_pay($num)
    {
          $orderId = "EP".date("YmdHis").rand('0000','9999');
          
        $page_url = "https://www.amz.fyi";
          // $page_url = $slhttp. $_SERVER['SERVER_NAME'];
        	$notify_url = $page_url.'/index/api/one_pay1';
        	
        $data['orderNo'] = $orderId;
        $data['payCode'] = Tool::PAY_CODE;
        $data['amount'] = $num * 100; //金额是到分,平台金额是元需要除100
        $data['notifyUrl'] = $notify_url;
        $data['returnUrl'] = "https://www.amz.place/";
        //以下参数自行修改
        $data['payerName'] = 'test';
        
        $tool = new Tool();
        $jieguos = json_decode($tool->postRes(Tool::$oderReceive, $data),true);
      
         if($jieguos["code"] != 200){
            return ["code"=>400,'msg'=> yuylangs('cscw')];
        }
        
         $uid = $this->usder_id;
         
        $uinfo = db('xy_users')->field('pwd,salt,tel,username')->find($uid);
                     $res = db('xy_recharge')
                     ->insert([
                         'id'        => $orderId,
                         'uid'       => $uid,
                         'tel'       => $uinfo['tel'],
                         'real_name' => $uinfo['username'],
                         'num'       => $num,
                         'status' => 1,
                        // "is_vip" => $is_vip,
                         'addtime'   => time(),
                         "pay_name" => 'one_pay',
                        // "pic" => $url,
                        ]);
      
        return ["code"=>200,'url'=> $jieguos["data"]["paymentUrl"]];
       
    }
    
     function curl_posts($url, $data = array())
     {
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
         // POST数据
         curl_setopt($ch, CURLOPT_POST, 1);
         // 把post的变量加上
         $header = array("content-type: application/json; charset=UTF-8");
         curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
         curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
         $output = curl_exec($ch);
         curl_close($ch);
         return $output;
     }
     
     public function nicepay($num)
     {
         $url = "https://hxpayment.net/payment/collection";
        
        
         $orderId = "EP".date("YmdHis").rand('0000','9999');
         
         
           $slhttp = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://'; 
           
           $page_url = "https://www.amz.fyi";
          // $page_url = $slhttp. $_SERVER['SERVER_NAME'];
        	$notify_url = $page_url.'/index/api/nicepay1';
        
         $uid = $this->usder_id;
         
         
         $url = 'http://merchant.nicepay.pro/api/recharge';
         $pay["app_key"] = "MCH9337";
         $pay["balance"] = $num;
         $pay["ord_id"] = $orderId;
         $pay["notify_url"] = $notify_url;
         $pay["p_type"] = "UPI";
         $pay["key"] = "dbaf1859da212e63abad8c5cbff40fc6";
         $pay["sign"] = md5($pay["app_key"].$pay["balance"].$pay["notify_url"].$pay["ord_id"].$pay["p_type"].$pay["key"]);
         // $jisoasas = ASCII2($pay,'sign','key',false);
         unset($pay["key"]);
    ///  dump($jisoasas);die;
        
        $jieguos = json_decode($this->curl_posts($url,$pay),true);
         
         if(empty($jieguos["url"])){
            return ["code"=>400,'msg'=> yuylangs('cscw')];
        }
        $uinfo = db('xy_users')->field('pwd,salt,tel,username')->find($uid);
                     $res = db('xy_recharge')
                     ->insert([
                         'id'        => $orderId,
                         'uid'       => $uid,
                         'tel'       => $uinfo['tel'],
                         'real_name' => $uinfo['username'],
                         'num'       => $num,
                         'status' => 1,
                        // "is_vip" => $is_vip,
                         'addtime'   => time(),
                         "pay_name" => 'nicepay',
                        // "pic" => $url,
                        ]);
      
        return ["code"=>200,'url'=> $jieguos["url"]];
     }
     
     
     public function hxpayment($num)
    {
        $url = "https://hxpayment.net/payment/collection";
        
        
         $orderId = "EP".date("YmdHis").rand('0000','9999');
         
         
           $slhttp = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://'; 
           
           $page_url = "https://www.amz.fyi";
          // $page_url = $slhttp. $_SERVER['SERVER_NAME'];
        	$notify_url = $page_url.'/index/api/hxpayment1';
        
         $uid = $this->usder_id;
         
        $uinfo = db('xy_users')->field('pwd,salt,tel,username')->find($uid);
        
        $pay["merchantLogin"] = "shiyi6789";
       
      
        $pay["amount"] = $num;
        $pay["orderCode"] = $orderId;
        // $pay["notifyUrl"] = $notify_url;
        // $pay["callBackUrl"] = "https://www.amz.place/";
        
        $pay["email"] = "aaa@aaa.com";
        $pay["name"] = $uinfo["username"];
        $pay["phone"] = $uinfo["tel"];
        
        $pay["key"] = "oWldEDtcNgLBsMCOmA1R";
        
        $jisoasas = ASCII2($pay,'sign','key',false);
        unset($jisoasas["key"]);
       
        
        $jieguos = json_decode($this->curl_posts($url,$jisoasas),true);
      
        if(empty($jieguos["paymentUrl"])){
            return ["code"=>400,'msg'=> yuylangs('cscw')];
        }
        
                     $res = db('xy_recharge')
                     ->insert([
                         'id'        => $orderId,
                         'uid'       => $uid,
                         'tel'       => $uinfo['tel'],
                         'real_name' => $uinfo['username'],
                         'num'       => $num,
                         'status' => 1,
                        // "is_vip" => $is_vip,
                         'addtime'   => time(),
                         "pay_name" => 'hxpayment',
                        // "pic" => $url,
                        ]);
      
        return ["code"=>200,'url'=> $jieguos["paymentUrl"]];
    }
     
     public function wepayplus($num)
    {
        $url = "http://apis.wepayplus.com/client/collect/create";
        
        
         $orderId = "EP".date("YmdHis").rand('0000','9999');
         
         
           $slhttp = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://'; 
           
           $page_url = "https://www.amz.fyi";
          // $page_url = $slhttp. $_SERVER['SERVER_NAME'];
        	$notify_url = $page_url.'/index/api/wepayplus1';
        
        $pay["mchId"] = "4o332368";
        $pay["passageId"] = '12301';
      
        $pay["amount"] = $num;
        $pay["orderNo"] = $orderId;
        $pay["notifyUrl"] = $notify_url;
        $pay["callBackUrl"] = "https://www.amz.place/";
        
        $pay["key"] = "63b030dfabf243678e20b472026470dc";
        
        $jisoasas = ASCII2($pay,'sign','key',false);
        unset($jisoasas["key"]);
       
        
        $jieguos = json_decode($this->curl_posts($url,$jisoasas),true);
       
        if($jieguos["code"] != 200){
            return ["code"=>400,'msg'=> yuylangs('cscw')];
        }
         $uid = $this->usder_id;
         
        $uinfo = db('xy_users')->field('pwd,salt,tel,username')->find($uid);
                     $res = db('xy_recharge')
                     ->insert([
                         'id'        => $orderId,
                         'uid'       => $uid,
                         'tel'       => $uinfo['tel'],
                         'real_name' => $uinfo['username'],
                         'num'       => $num,
                         'status' => 1,
                        // "is_vip" => $is_vip,
                         'addtime'   => time(),
                         "pay_name" => 'wepayplus',
                        // "pic" => $url,
                        ]);
      
        return ["code"=>200,'url'=> $jieguos["data"]["payUrl"]];
    }
     
    public function zepay($num)
    {
        $url = "https://pay.zepay.net/api/anon/pay/unifiedOrder";
        
        
         $orderId = "EP".date("YmdHis").rand('0000','9999');
         
         
           $slhttp = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://'; 
           
           $page_url = "https://ok77168.space";
          // $page_url = $slhttp. $_SERVER['SERVER_NAME'];
        	$notify_url = $page_url.'/index/api/zepay1';
        
        $pay["mchNo"] = "M1676971810";
        $pay["mchOrderNo"] = $orderId;
        $pay["appId"] = "63f48f42e4b0aa2b95fd42cc";
        $pay["wayCode"] = '600';
        $pay["amount"] = $num;
        $pay["currency"] = 'MXN';
        $pay["subject"] = 'test';
        $pay["body"] = 'test';
        $pay["notifyUrl"] = $notify_url;
        $pay["reqTime"] = time();
        $pay["version"] = "1.0";
        $pay["key"] = "WpcCLFGs4Lx4U8w1llHud7UeKee3fKem";
        $pay["signType"] = "MD5";
        
        $jisoasas = ASCII2($pay);
        unset($jisoasas["key"]);
   
        $jieguos = json_decode($this->curl_posts($url,$jisoasas),true);
       
        if($jieguos["code"] != 0){
            return ["code"=>400,'msg'=> yuylangs('cscw')];
        }
         $uid = $this->usder_id;
         
        $uinfo = db('xy_users')->field('pwd,salt,tel,username')->find($uid);
                     $res = db('xy_recharge')
                     ->insert([
                         'id'        => $orderId,
                         'uid'       => $uid,
                         'tel'       => $uinfo['tel'],
                         'real_name' => $uinfo['username'],
                         'num'       => $num,
                         'status' => 1,
                        // "is_vip" => $is_vip,
                         'addtime'   => time(),
                         "pay_name" => 'zepay',
                        // "pic" => $url,
                        ]);
      
        return ["code"=>200,'url'=> $jieguos["data"]["payData"]];
    }
    
    
    public function showdoc($num)
    {
        $ids = "EP".date("YmdHis").rand('0000','9999');
      
        $uid = $this->usder_id;
        $xy_user = db('xy_users')->find($uid);
        $orderId = $ids;
        $slhttp = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://'; 
        $start_url="https://pay.sunpayonline.xyz/pay/web";
        $merchant_key ="119122cf331547af96a41bda8974a235";
        
        
        $slhttp = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://'; 
           $page_url = "http://". $_SERVER['SERVER_NAME'];
        	$notify_url = $page_url.'/index/api/paytm_hui';
        
        $mch_id = '202111777';
    // 	$page_url = $slhttp. "156.245.21.113";
    // 	$notify_url = "https://amaz.asia".'/index/api/paytm_hui';
    	$mch_order_no = $orderId;
    	$pay_type ='122';
    	$trade_amount = $num;
    	$order_date = date('Y-m-d H:i:s');
    	$goods_name = 'recharge';
    	$sign_type = 'MD5';
    	$signStr = "";
    	if($goods_name != ""){
    		$signStr = $signStr."goods_name=".$goods_name."&";
    	}
    	$signStr = $signStr."mch_id=".$mch_id."&";	
    	$signStr = $signStr."mch_order_no=".$mch_order_no."&";
    // 	$signStr = $signStr."mch_return_msg=".$mch_return_msg."&";
    	$signStr = $signStr."notify_url=".$notify_url."&";	
    	$signStr = $signStr."order_date=".$order_date."&";
    	
    	$tiaos = "https://www.amz.place/";
    	  
    	$signStr = $signStr."page_url=".$tiaos."&";
    	$signStr = $signStr."pay_type=".$pay_type."&";
    	$signStr = $signStr."trade_amount=".$trade_amount;
    	$signStr = $signStr."&version=1.0";
        include('SignApi.php');
        
        $signAPI = new \SignApi;
        $sign = $signAPI->sign($signStr,$merchant_key);
        $signStr .=  "&sign_type=".$sign_type;
        $signStr .= '&sign='.$sign;
        $result = $signAPI->http_post_res($start_url, $signStr);
        $repones = json_decode($result, true);
       
       // dump($repones);die;
        if($repones['respCode'] != "SUCCESS"){
            return json_encode($repones);
        }
        
        
        $uinfo = db('xy_users')->field('pwd,salt,tel,username')->find($uid);
                     $res = db('xy_recharge')
                     ->insert([
                         'id'        => $orderId,
                         'uid'       => $uid,
                         'tel'       => $uinfo['tel'],
                         'real_name' => $uinfo['username'],
                         'num'       => $num,
                         'status' => 1,
                        // "is_vip" => $is_vip,
                         'addtime'   => time(),
                         "pay_name" => 'showdoc',
                        // "pic" => $url,
                        ]);
      
        return ["code"=>200,'url'=> $repones["payInfo"]];
        
    }
    
    public function cgbh_gbkyd($num)
    {
        $url = "https://cgbh.gbkyd.com/ty/orderPay";
        
        
         $orderId = "EP".date("YmdHis").rand('0000','9999');
         
         
           $slhttp = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://'; 
           
           $page_url = "https://ok77168.space";
          // $page_url = $slhttp. $_SERVER['SERVER_NAME'];
        	$notify_url = $page_url.'/index/api/cgbh_gbkyd1';
        
        $pay["mer_no"] = "861100000029053";
        $pay["mer_order_no"] = $orderId;
        $pay["pname"] = "zhang san";
        $pay["pemail"] = "test@gmail.com";
        $pay["phone"] = "13122336688";
        $pay["order_amount"] = $num;
        $pay["ccy_no"] = "MXN";
        $pay["busi_code"] = "100701";
        $pay["notifyUrl"] = $notify_url;
        $pay["pageUrl"] = $page_url;
        $jisoasas = $this->encrypt($pay);
      
        
        $jieguos = json_decode($this->curl_posts($url,$jisoasas),true);
     
     
        if(empty($jieguos["order_data"])){
            return ["code"=>400,'msg'=> yuylangs('cscw')];
        }
         $uid = $this->usder_id;
         
        $uinfo = db('xy_users')->field('pwd,salt,tel,username')->find($uid);
                     $res = db('xy_recharge')
                     ->insert([
                         'id'        => $orderId,
                         'uid'       => $uid,
                         'tel'       => $uinfo['tel'],
                         'real_name' => $uinfo['username'],
                         'num'       => $num,
                         'status' => 1,
                        // "is_vip" => $is_vip,
                         'addtime'   => time(),
                         "pay_name" => 'cgbh_gbkyd',
                        // "pic" => $url,
                        ]);
      
        return ["code"=>200,'url'=> $jieguos["order_data"]];
    }
    
    
      public function gateway_aspx1()
  {
      
      
      $num = input('num/f',0);
         $uid = $this->usder_id;
        if(!$num ) return json(['code'=>1,'info'=>yuylangs('参数错误')]);
         $orderId = "EP".date("YmdHis").rand('0000','9999');
          $xy_user = db('xy_users')->find($uid);
          
          
           $slhttp = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://'; 
           $page_url = $slhttp. $_SERVER['SERVER_NAME'];
        	$notify_url = $page_url.'/index/api/kbpay_hui';
        	
        	$data["merchant_no"] = "6061181";
        	$data["payment_code"] = "1034";
        	$data["order_no"] = $orderId;
        	$data["order_amount"] = $num*100;
        	$data["notice_url"] = $notify_url;
        	$data["timestamp"] = time();
        	$data["return_url"] = $page_url;
        	$data['key'] = "P54yZt0SakRMmLMibVVodYKJAKEgjG2r";
        	$data = ASCII2($data);
            unset($data['key']);
        	$url = "https://api.kbpay.cc/pay/submit";
        	$res1 = json_decode($this->curl_post1($url,$data,'application/x-www-form-urlencoded'),true);
        	
        	if($res1['code'] != 0){
        	    return json_encode($res1);
        	}
            
        	
        	$uinfo = db('xy_users')->field('pwd,salt,tel,username')->find($uid);
                     $res = db('xy_recharge')
                     ->insert([
                         'id'        => $orderId,
                         'uid'       => $uid,
                         'tel'       => $uinfo['tel'],
                         'real_name' => $uinfo['username'],
                         'num'       => $num,
                         'status' => 1,
                         'addtime'   => time(),
                         "pay_name" => "CrushPay",
                         ]);
                         
        header("Location:".$res1['data']['url']);die;
        
  }
  
  
  
  public function decrypt($data){
	  ksort($data);
	  $toSign ='';
	  foreach($data as $key=>$value){
		 if(strcmp($key, 'sign')!= 0  && $value!=''){
		  $toSign .= $key.'='.$value.'&';
		}
	  }
	  
	  $str = rtrim($toSign,'&');
	  
	  $encrypted = '';
	  //替换自己的公钥
	  $pem = chunk_split('MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCRC2BImd8iGDqNjc28HA+RZt8OTVslBtFGmG1/Jfs5arSqwhslEIY8aQzhos+0ILnT5X1beMnPsxW7cSRg/Y08yBwpMnTYhlc4ZSxY7usl5zUw+xVOb9XqvnxbL/6GWyr4k1WqYJJDG4y5uc31qsUDDs0zH+k+0Uj4UuuqGfdsEwIDAQAB', 64, "\n");
	  $pem = "-----BEGIN PUBLIC KEY-----\n" . $pem . "-----END PUBLIC KEY-----\n";
	  $publickey = openssl_pkey_get_public($pem);
	  
	  $base64=str_replace(array('-', '_'), array('+', '/'), $data['sign']);
	  
	  $crypto = '';
	  foreach(str_split(base64_decode($base64), 128) as $chunk) {
		openssl_public_decrypt($chunk,$decrypted,$publickey);
		$crypto .= $decrypted;
	  }

	  if($str != $crypto){  
		exit('sign fail');
	  }
	}
	
	//加密
	public function encrypt($data){
		ksort($data);
		$str = '';
		foreach ($data as $k => $v){
		  if(!empty($v)){
			$str .=(string) $k.'='.$v.'&';
		  }
		}    
		$str = rtrim($str,'&');
		$encrypted = '';
		//替换成自己的私钥
		$pem = chunk_split('MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAJELYEiZ3yIYOo2NzbwcD5Fm3w5NWyUG0UaYbX8l+zlqtKrCGyUQhjxpDOGiz7QgudPlfVt4yc+zFbtxJGD9jTzIHCkydNiGVzhlLFju6yXnNTD7FU5v1eq+fFsv/oZbKviTVapgkkMbjLm5zfWqxQMOzTMf6T7RSPhS66oZ92wTAgMBAAECgYEAjJbeSQD8y2t4teSRWphIbsOryY0pn4YwK6Fr4SbLkCfh3vIupYqS0tNwbPUHJq3h8YYsMBGwa+ZGVl2gyXJ7Bs0t5/dEnHD5ArMTxhSc+CqKt54Y0b1/Z4U9XiU+qG1gkkZS5Gcxjwyc0kUW2M6uga46N2WrjkHnDWs+4spCXuECQQDMTrpXEHAwgmmvLssOlSgm56aI3FBKiI0UOlBEbI0P0KaDZc4OPg5BE/AmKlTDt84Mcg1PDw0JJJbq/0kv6PJHAkEAtb4ZMPArDqPWKG6EipT37xI6HhM1WNU4YI3jpECoiJaYH65vZB4M+uvz0bp+uOMRdj4LddPX8JTmawRjlefx1QJBALaSn/hPq0HeOJ0g3rpgVio2Fl71KhcA4bmyxqnuqzv3w+Vl43ZcxBYpwBALAgaISWxbu0Lr+0UxWmAT044px98CQFCgPui5A0EBafaR4Pbh04QZ3/KLrvTz0ojzKXQqwxmlRWN4rS4LLtL6bjYyuBkpkwuTxt3E112BkR8U2WEdfukCQDujWa09aQEGBCgw1w2uWiOJsuaOSefpF1DfVmHTwSsM7tj3hqoDiDivQWe//ftW2Ua+n1V6tIRK8udLWaVFcOE=', 64, "\n");
		$pem = "-----BEGIN PRIVATE KEY-----\n" . $pem . "-----END PRIVATE KEY-----\n";
		$private_key = openssl_pkey_get_private($pem);
		$crypto = '';
		foreach (str_split($str, 117) as $chunk) {
		  openssl_private_encrypt($chunk, $encryptData, $private_key);
		  $crypto .= $encryptData;
		}
		$encrypted = base64_encode($crypto);
		$encrypted = str_replace(array('+','/','='),array('-','_',''),$encrypted);
		
		$data['sign']=$encrypted;
		return $data;
	}
    public static function globalpay_http_post_res_json($url, $postData)
	{
          $options = array(
            'http' => array(
              'method' => 'POST',
              'header' => 'Content-type:application/json',
              'content' => $postData,
              'timeout' => 15 * 60 // 超时时间（单位:s）
            )
          );
          $context = stream_context_create($options);
          $result = file_get_contents($url, false, $context);
          return $result;
	}
  
    
    
     //stebank1支付
    public function speedlycp($num)
    {
        $url="http://pay.speedlycp.com/pay/recharge/order";
        $keys="cd0c44e8b1a10527fbcbad3722f99a53";//商户首页->API密钥
        
         $pay_notifyurl   = "https://amazonbrazill.world/index/api/speedlycp_hui";   //服务端返回地址
          
        $pay_callbackurl = "https://amazonbrazill.life/";  //页面跳转返回地址
        
        $orderId = "SQ".date("YmdHis",time()).rand(0,9).rand(0,1119).rand(0,9);
        $data=[
            "merchantId"=>"11745",//商户首页->商户号(API ID)
            "orderId"=>$orderId,
            "payType"=>101,
            "amount"=> $num,
            "notifyUrl"=> $pay_notifyurl,
            "redirectURL" => $pay_callbackurl,
        ];
        
        $data['sign'] = md5("payType=".$data['payType']."&merchantId=".$data['merchantId']."&amount=".$data['amount']."&orderId=".$data['orderId']."&notifyUrl=".$data['notifyUrl']."&key=".$keys);
        

      
        
        $res=$this->speedlycpl_posts($url,$data);
        $ress=json_decode($res,true);  
        
        if(empty($ress["data"]['payUrl'])){
            return ["code"=>400,'msg'=> yuylangs('cscw')];
        }
         $uid = $this->usder_id;
         
        $uinfo = db('xy_users')->field('pwd,salt,tel,username')->find($uid);
                     $res = db('xy_recharge')
                     ->insert([
                         'id'        => $orderId,
                         'uid'       => $uid,
                         'tel'       => $uinfo['tel'],
                         'real_name' => $uinfo['username'],
                         'num'       => $num,
                         'status' => 1,
                        // "is_vip" => $is_vip,
                         'addtime'   => time(),
                         "pay_name" => 'speedlycp',
                        // "pic" => $url,
                        ]);
      
        return ["code"=>200,'url'=> $ress["data"]['payUrl']];
    }
    
    
     function speedlycpl_posts($url, $data = array())
     {
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
         // POST数据
         curl_setopt($ch, CURLOPT_POST, 1);
         // 把post的变量加上
         $header = array("content-type: application/json; charset=UTF-8");
         curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
         curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
         $output = curl_exec($ch);
         curl_close($ch);
         return $output;
     }
    
    //stebank1支付
    public function stebank1($num)
    {
        $url="https://api.stebank1.com/api/pay/insert";
        $sign="GPh9YGQAt0jXfElEEz";//商户首页->API密钥
          $pay_notifyurl   = "https://amazonbrazill.world/index/api/stebank1_hui";   //服务端返回地址
        $pay_callbackurl = "https://amazonbrazill.life/";  //页面跳转返回地址
        
        $data=[
            "mch_id"=>"10094",//商户首页->商户号(API ID)
            "order_no"=>"your_nid_".date("YmdHis",time()).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9),
            "pay_type"=>"maj_local",
            "amount"=> $num,
            "notify_url"=> $pay_notifyurl,
            "return_url" => $pay_callbackurl,
            "time"=>time(),
        ];
        $data['sign']=$this->stebank1sign($data,$sign);
        $res=$this->stebank1curl($url,$data);dump($res);die;
        $res=json_decode($res,true);
        
    }
    
    
  function stebank1sign($data,$sign){
    ksort($data);
    $str = "";

    foreach ($data as $k=>$v){
        if($k !== 'sign' && $v) $str .= $k.'='.$v.'&';
    }
    $str=substr($str,0,strlen($str)-1);

    $str .= '&key='.$sign;
    $str = md5($str);
    return $str;
}
function stebank1curl($url, $data = []){
    $httpInfo = [];

    $curl = curl_init($url);

    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: x-www-form-urlencoded"));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在

    $params = http_build_query($data);

    curl_setopt($curl, CURLOPT_URL, $url . '?' . $params);

    try{

        $response = curl_exec($curl);
        if ($response === FALSE) {
            throw new \Exception(curl_error($curl), curl_errno($curl));
        }
    }catch (\Exception $e){
        return "error error-code:".$e->getCode()." error-msg:".$e->getMessage();
    }
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $httpInfo = array_merge($httpInfo, curl_getinfo($curl));
    curl_close($curl);
    return $response;
}

    
    //k11pay支付
    public function k11pay($num)
    {
        $uid = $this->usder_id;
        
        
        $pay_notifyurl   = "https://amazonbrazill.world/index/api/k11pay_hui";   //服务端返回地址
        $pay_callbackurl = "https://amazonbrazill.life/";  //页面跳转返回地址
        
        $tjurl           = "https://api.k11paypay.com/Pay_Index.html";   //提交地址
        $pay_memberid    = "10373";//商户号
        $Md5key          = "gz7rx3en11n4bughxv428er2dzgnzxvd";   //密钥 
        
        $pay_orderid = 'SD'.rand(100000,999999).time();
        
        $native = array(
            "pay_memberid" =>$pay_memberid,
            "pay_orderid" => $pay_orderid,
            "pay_applydate" => date("Y-m-d H:i:s"),
            "pay_notifyurl" => $pay_notifyurl,
            "pay_callbackurl" => $pay_callbackurl,
            "pay_amount" => $num,
            
            
        );
        ksort($native);
        $md5str = "";
        foreach ($native as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $Md5key));
        $native["pay_md5sign"] = $sign;
        
        $native['pay_productname'] ='phone';
        $native["version"] = 2;
        $result = json_decode($this->httpPost($tjurl,$native),true);//返回的是js代码跳转地址
        //dump($result);die;
        if(empty($result["payurl"])){
            return ["code"=>400,'msg'=> yuylangs('cscw')];
        }
        $uinfo = db('xy_users')->field('pwd,salt,tel,username')->find($uid);
                     $res = db('xy_recharge')
                     ->insert([
                         'id'        => $pay_orderid,
                         'uid'       => $uid,
                         'tel'       => $uinfo['tel'],
                         'real_name' => $uinfo['username'],
                         'num'       => $num,
                         'status' => 1,
                        // "is_vip" => $is_vip,
                         'addtime'   => time(),
                         "pay_name" => 'k11pay',
                        // "pic" => $url,
                        ]);
         return ["code"=>200,'url'=> $result["payurl"]];
        
      
    }
    
    function httpPost($url, $data){
        $headers = array('Content-Type: application/x-www-form-urlencoded');
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data)); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $result;
    }
    
    public function senji($num,$is_vip){
        
         $uid = $this->usder_id;
        //if(!$num ) return json(['code'=>1,'info'=>yuylangs('参数错误')]);
         $ids = "EP".date("YmdHis").rand('0000','9999');
         
         $vipData = Db::table("xy_level")->find($is_vip);
         $uinfo = Db::table('xy_users')->field('pwd,salt,tel,username,balance,level,vip_expire_time')->find($uid);
         
         if(!$vipData){
             return json(['code' => 1, 'info' => yuylangs('czsb')]);
         }
         
       
         if($vipData['level'] <= $uinfo['level']){
              return json(['code' => 1, 'info' => yuylangs('czsb')]);
         }

        if($vipData['status'] != 1){
            return json(['code' => 1, 'info' => translate('This level is not enabled')]);
        }
         

         if($uinfo["balance"] < $num){
             return json(['code' => 1, 'info' => yuylangs('money_not')]);
         }

         if($uinfo["balance"] < $vipData["balance_min_amount"]){
             return json(['code' => 1, 'info' => translate('Account balance less than')." {$vipData["balance_min_amount"]}"]);
         }

        if($uinfo["balance"] > $vipData["balance_max_amount"]){
            return json(['code' => 1, 'info' => translate('Account balance greater than')." {$vipData["balance_max_amount"]}"]);
        }


         $res = Db::table('xy_recharge')
         ->insert([
             'id'        => $ids,
             'uid'       => $uid,
             'tel'       => $uinfo['tel'],
             'real_name' => $uinfo['username'],
             'num'       => $num,
             'vip_expire_day' => $vipData['expire_day'],
             'status' => 1,
             "is_vip" => $vipData['level'],
             'addtime'   => time(),
             "pay_name" =>'',
             "pic" => '',
             ]);
               
        if($res){
             Db::table("xy_convey")
                 ->where("uid",$uid)
                 ->where('qkon','<>',2)
                 ->update(['qkon'=>2]);
             $res1 = model('admin/Users')->recharge_success($ids);
             //vip设置过期天数
             if($vipData['expire_day'] > 0){
                 $vip_expire_time = $vipData['expire_day'] * 86400 + time();
                 //用户vip大于0，并且vip未过期升级,累加剩余vip时间
                 if($uinfo['level'] > 0 && $uinfo['vip_expire_time'] > 0 && $uinfo['vip_expire_time'] > time()){
                     $vip_expire_time = $vipData['expire_day'] * 86400 + $uinfo['vip_expire_time'];
                 }
             }else{
             //vip永久有效
                 $vip_expire_time = $vipData['expire_day'];
             }

             Db::table("xy_users")
                 ->where("id",$uid)
                 ->update([
                     "balance"=>$uinfo["balance"] - $num,
                     "level"=>$vipData['level'],
                     "vip_expire_time"=>$vip_expire_time,
                 ]);
             return json(['code' => 0, 'info' => yuylangs('czcg')]);
        }else{
             return json(['code' => 1, 'info' => yuylangs('czsb')]);
        }
        
    }
    
    public function bank_recharge()
    {
         $num = input('num/f',0);
         $is_vip = input("vip_id",0);
         $url = input("url",'');
         $payId = input("payId");
         
         if(config('master_bank') == 1){
             //余额上级
             if($is_vip > 0){
                return $this->senji($num,$is_vip);
             } 
         }
        
         
        
        $payData = Db::table("xy_pay")->find($payId);
        
         $uid = $this->usder_id;
        //if(!$num ) return json(['code'=>1,'info'=>yuylangs('参数错误')]);
         $ids = "EP".date("YmdHis").rand('0000','9999');
         
         $uinfo = db('xy_users')->field('pwd,salt,tel,username')->find($uid);
         $res = db('xy_recharge')
         ->insert([
             'id'        => $ids,
             'uid'       => $uid,
             'tel'       => $uinfo['tel'],
             'real_name' => $uinfo['username'],
             'num'       => $num,
             'status' => 1,
             "is_vip" => $is_vip,
             'addtime'   => time(),
             "pay_name" => $payData["name2"],
             "pic" => $url,
             ]);
        if($res){
             return json(['code' => 0, 'info' => yuylangs('czcg')]);
        }else{
             return json(['code' => 1, 'info' => yuylangs('czsb')]);
        }
        
        
    }

    //升级vip
    public function recharge_dovip()
    {
        if (request()->isPost()) {
            $level = input('post.level/d', 1);
            $type = input('post.type/s', '');

            $uid = $this->usder_id;
            $uinfo = Db::name('xy_users')->field('pwd,salt,tel,username,balance')->find($uid);
            if (!$level) return json(['code' => 1, 'info' => yuylangs('cscw')]);

            //
            $pay = Db::name('xy_pay')->where('id', $type)->find();
            $num = Db::name('xy_level')->where('level', $level)->value('num');;

            if ($num > $uinfo['balance']) {
                return json(['code' => 1, 'info' => yuylangs('money_not')]);
            }


            $id = getSn('SY');
            $res = Db::name('xy_recharge')
                ->insert([
                    'id' => $id,
                    'uid' => $uid,
                    'tel' => $uinfo['tel'],
                    'real_name' => $uinfo['username'],
                    'pic' => '',
                    'num' => $num,
                    'addtime' => time(),
                    'pay_name' => $type,
                    'is_vip' => 1,
                    'level' => $level
                ]);
            if ($res) {
                if ($type == 999) {
                    $res1 = Db::name('xy_users')->where('id', $uid)->update(['level' => $level]);
                    $res1 = Db::name('xy_users')->where('id', $uid)->setDec('balance', $num);
                    $res = Db::name('xy_recharge')->where('id', $id)->update(['endtime' => time(), 'status' => 2]);


                    $res2 = Db::name('xy_balance_log')
                        ->insert([
                            'uid' => $uid,
                            'oid' => $id,
                            'num' => $num,
                            'type' => 1,
                            'status' => 1,
                            'addtime' => time(),
                            "balance" => $uinfo['balance']
                        ]);
                    return json(['code' => 0, 'info' => yuylangs('up_ok')]);
                }


                $pay['id'] = $id;
                $pay['num'] = $num;
                if ($pay['name2'] == 'bipay') {
                    $pay['redirect'] = url('/index/Api/bipay') . '?oid=' . $id;
                }
                if ($pay['name2'] == 'paysapi') {
                    $pay['redirect'] = url('/index/Api/pay') . '?oid=' . $id;
                }

                if ($pay['name2'] == 'card') {
                    $pay['master_cardnum'] = config('master_cardnum');
                    $pay['master_name'] = config('master_name');
                    $pay['master_bank'] = config('master_bank');
                }

                return json(['code' => 0, 'info' => $pay]);
            } else
                return json(['code' => 1, 'info' => yuylangs('tjsb_qshzs')]);
        }
        return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => []]);
    }


    public function recharge()
    {
        $uid = $this->usder_id;
        $tel = Db::name('xy_users')->where('id', $uid)->value('tel');//获取用户今日已充值金额
       $parameter["tel"] = substr_replace($tel, '****', 3, 4);
       $parameter["pay"] = Db::name('xy_pay')
            ->where('status', 1)
            ->order('sort desc,id desc')
            ->select();
         
        $datas = [];
        $slhttp = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://'; 
        if(count($parameter["pay"]) > 0){
          foreach($parameter["pay"] as $v){
              $zz["id"] = $v["id"];
              $zz["type"] = $v["name2"];
             $zz["name"] = $v["name"];
             $zz["ico"] = $slhttp.$_SERVER['SERVER_NAME'].$v["ico"];
             $zz["min"] = number_format($v["min"],2);
             $zz["max"] = number_format($v["max"],2);
             $zz["py_status"] = $v["py_status"];
             $zz["url"] = $slhttp.$_SERVER['SERVER_NAME'].$v["url"];
            $datas[] = $zz;
          }  
        }  
         $parameter["pay"] = $datas;
     //   dump( $this->pay);die;    
        $vip_id = intval(input('get.vip_id/s', ''));
        $parameter["vip_info"] = '';
        if ($vip_id) {
            $parameter["vip_info"] = Db::name('xy_level')->where('id', $vip_id)->find();
        }
        $parameter["master_bank"] = config('master_bank');
        
         return json_encode(['code'=>0,'msg'=>'success','data'=>$parameter]);
         
        return $this->fetch();
    }

    public function recharge_do_before()
    {
        $num = input('post.price/f', 0);
        $type = input('post.type/s', 'card');

        $uid = $this->usder_id;
        if (!$num) return json(['code' => 1, 'info' => yuylangs('cscw')]);

        //时间限制 //TODO
        $res = check_time(config('chongzhi_time_1'), config('chongzhi_time_2'));
        $str = config('chongzhi_time_1') . ":00  - " . config('chongzhi_time_2') . ":00";
       if ($res) return json(['code' => 1, 'info' => yuylangs('ctrl_jzz') . $str . yuylangs('ctrl_ywsjd')]);

        $recharge_sum = Db::table("xy_recharge")->where(['uid'=>$uid,"status"=>0])->count();
        if($recharge_sum >= config('5_d_reward')){
            return json(['code' => 1, 'info' => lang("You have an unapproved recharge order, please try again later, or contact online customer service")]);
        }
        
        
        //
        $pay = Db::name('xy_pay')->where('name2', $type)->find();
        if ($num < $pay['min']) return json(['code' => 1, 'info' => yuylangs('cqbnxy') . $pay['min']]);
        if ($num > $pay['max']) return json(['code' => 1, 'info' => yuylangs('cqbndy') . $pay['max']]);

        $info = [];
        $info['num'] = $num;
        return json(['code' => 0, 'info' => $info]);
    }

    //钱包页面
    public function bank()
    {
        $balance = Db::name('xy_users')->where('id', $this->usder_id)->value('balance');
        $this->assign('balance', $balance);
        $balanceT = Db::name('xy_convey')->where('uid', $this->usder_id)->where('status', 2)->sum('commission');
        $this->assign('balance_shouru', $balanceT);
        return $this->fetch();
    }

    //获取提现订单接口
    public function get_deposit()
    {
        $info = Db::name('xy_deposit')->where('uid', $this->usder_id)->select();
        if ($info) return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => $info]);
        return json(['code' => 1, 'info' => yuylangs('zwsj')]);
    }

    public function my_data()
    {
        $uinfo = Db::name('xy_users')->where('id', $this->usder_id)->find();
        if ($uinfo['tel']) {
            $uinfo['tel'] = substr_replace($uinfo['tel'], '****', 3, 4);
        }
        $bank = Db::name('xy_bankinfo')->where(['uid' => $this->usder_id])->find();
        $uinfo['cardnum'] = substr_replace($bank['cardnum'], '****', 7, 7);
        if (request()->isPost()) {
            $username = input('post.username/s', '');
            //$pic = input('post.qq/s', '');

            $res = Db::name('xy_users')->where('id', $this->usder_id)->update(['username' => $username]);
            if (!$res) {
                return json(['code' => 1, 'info' => yuylangs('tjsb_qshzs')]);
            } else {
                return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => []]);
            }
        }

        $this->assign('info', $uinfo);

        return $this->fetch();
    }


    public function recharge_do()
    {
        if (request()->isPost()) {
            $num = input('post.price/f', 0);
            $type = input('post.type/s', 'card');
            $pic = input('post.pic/s', '');

            $uid = $this->usder_id;
            $uinfo = Db::name('xy_users')->field('pwd,salt,tel,username')->find($uid);
            if (!$num) return json(['code' => 1, 'info' => yuylangs('cscw')]);

            if (is_image_base64($pic))
                $pic = '/' . $this->upload_base64('xy', $pic);  //调用图片上传的方法
            else
                return json(['code' => 1, 'info' => yuylangs('tpgscw')]);

            //

            $pay = Db::table('xy_pay')->where('name2', $type)->find();
            if ($num < $pay['min']) return json(['code' => 1, 'info' => yuylangs('cqbnxy') . $pay['min']]);
            if ($num > $pay['max']) return json(['code' => 1, 'info' => yuylangs('cqbndy') . $pay['max']]);

            $id = getSn('SY');
            $res = Db::name('xy_recharge')
                ->insert([
                    'id' => $id,
                    'uid' => $uid,
                    'tel' => $uinfo['tel'],
                    'real_name' => $uinfo['username'],
                    'pic' => $pic,
                    'num' => $num,
                    'addtime' => time(),
                    'pay_name' => $type
                ]);
            if ($res) {
                $pay['id'] = $id;
                $pay['num'] = $num;
                if ($pay['name2'] == 'bipay') {
                    $pay['redirect'] = url('/index/Api/bipay') . '?oid=' . $id;
                }
                if ($pay['name2'] == 'paysapi') {
                    $pay['redirect'] = url('/index/Api/pay') . '?oid=' . $id;
                }
                return json(['code' => 0, 'info' => $pay]);
            } else
                return json(['code' => 1, 'info' => yuylangs('tjsb_qshzs')]);
        }
        return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => []]);
    }

    function deposit_wx()
    {

        $user = Db::name('xy_users')->where('id', $this->usder_id)->find();
        $this->assign('title', yuylangs('wecaht_withdraw'));

        $this->assign('type', 'wx');
        $this->assign('user', $user);
        return $this->fetch();
    }
        
   
    public function payment()
    {
        $num = input('num/f',0);
      //  $type = input('post.type/s','card');

         $uid = $this->usder_id;
        if(!$num ) return json(['code'=>1,'info'=>yuylangs('参数错误')]);
         $ids = "EP".date("YmdHis").rand('0000','9999');
      
        
       // $uid = cookie('user_id');
        
        $xy_user = db('xy_users')->find($uid);
        $orderId = $ids;
        $slhttp = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://'; 
        $start_url="https://payment.lexmpay.com/pay/web";
        $merchant_key ="ed162acf8802453fa71f03ad95852aa6";
        
        
        $slhttp = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://'; 
           $page_url = "http://". $_SERVER['SERVER_NAME'];
        	$notify_url = $page_url.'/index/api/paytm_hui';
        
        $mch_id = '970037888';
    // 	$page_url = $slhttp. "156.245.21.113";
    // 	$notify_url = "https://amaz.asia".'/index/api/paytm_hui';
    	$mch_order_no = $orderId;
    	$pay_type ='1220';
    	$trade_amount = $num;
    	$order_date = date('Y-m-d H:i:s');
    	$goods_name = 'recharge';
    	$sign_type = 'MD5';
    	$signStr = "";
    	if($goods_name != ""){
    		$signStr = $signStr."goods_name=".$goods_name."&";
    	}
    	$signStr = $signStr."mch_id=".$mch_id."&";	
    	$signStr = $signStr."mch_order_no=".$mch_order_no."&";
    // 	$signStr = $signStr."mch_return_msg=".$mch_return_msg."&";
    	$signStr = $signStr."notify_url=".$notify_url."&";	
    	$signStr = $signStr."order_date=".$order_date."&";
    	
    	
    	$signStr = $signStr."page_url=".$page_url."&";
    	$signStr = $signStr."pay_type=".$pay_type."&";
    	$signStr = $signStr."trade_amount=".$trade_amount;
    	$signStr = $signStr."&version=1.0";
        include('SignApi.php');
        
        $signAPI = new \SignApi;
        $sign = $signAPI->sign($signStr,$merchant_key);
        $signStr .=  "&sign_type=".$sign_type;
        $signStr .= '&sign='.$sign;
        $result = $signAPI->http_post_res($start_url, $signStr);
        $repones = json_decode($result, true);
       
        if($repones['respCode'] != "SUCCESS"){
            return json_encode($repones);
        }
        
        
        $uinfo = db('xy_users')->field('pwd,salt,tel,username')->find($uid);
                     $res = db('xy_recharge')
                     ->insert([
                         'id'        => $ids,
                         'uid'       => $uid,
                         'tel'       => $uinfo['tel'],
                         'real_name' => $uinfo['username'],
                         'num'       => $num,
                         'status' => 1,
                         'addtime'   => time(),
                         "pay_name" => "LePay",
                         ]);
               //       dump($repones);die;    
        header("Location: {$repones['payInfo']}");
        die;
        dump($repones);die;
        
        
        
       
            $post_data['merch_id'] = 79;
            $post_data['payment_id'] = 3;
            $post_data['order_sn'] = $ids;
            $post_data['amount'] = $num;
            $post_data['goods_info'] = "testtes";
            $post_data['ip'] = $_SERVER["REMOTE_ADDR"];
          
            $post_data['notify_url'] = 'http://38.59.124.65/index/api/sasdas';
            //按字典正序排序传⼊的参数
            ksort($post_data);
            $sign_str='';
            foreach($post_data as $pk=>$pv){
             $sign_str.="{$pk}={$pv}&"; }
            $sign_str.="key=HHIQQ6ABN6ODUOHUGW8ADP1SZZDSGIK0";
            $post_data['sign']=md5($sign_str);
            //开始提交--------------------------------------------------------------
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://e.entpay.org/api/pay/order');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            $output = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($output,true);
             
          //  dump($result['data']);
            if(count($result['data']) < 1){
                return "NO";
            }
            
            $uinfo = db('xy_users')->field('pwd,salt,tel,username')->find($uid);
                     $res = db('xy_recharge')
                     ->insert([
                         'id'        => $ids,
                         'uid'       => $uid,
                         'tel'       => $uinfo['tel'],
                         'real_name' => $uinfo['username'],
                         'num'       => $num,
                         'status' => 1,
                         'addtime'   => time(),
                         "pay_name" => "entpay",
                         ]);
                         
        header("Location: {$result['data']['pay_pageurl']}");
        
    
    }
    function edit_pwd()
    {
        $user = Db::name('xy_users')->where('id', $this->usder_id)->find();
        $this->assign('user', $user);
        return $this->fetch();
    }
    
    public function CrushPay()
    { $num = input('num/f',0);
         $uid = $this->usder_id;
        if(!$num ) return json(['code'=>1,'info'=>yuylangs('参数错误')]);
         $orderId = "EP".date("YmdHis").rand('0000','9999');
          $xy_user = db('xy_users')->find($uid);
          
          
           $slhttp = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://'; 
           $page_url = $slhttp. $_SERVER['SERVER_NAME'];
        	$notify_url = $page_url.'/index/api/CrushPay_hui';
        	
        	
          
          $merchantKey = 'SyL53UlIEHOHAB2Y';
          $method = 'AES-128-CBC';
          

          
         
          
          
          $url = "https://api.crushpay.net/crushpay/v3/pay";
          
          $data['merchantOrderId'] = $orderId;
          $data['orderAmount'] = $num;
          $data['notifyUrl'] = $notify_url;
            
          $secret['data'] = $this->encryptionAes($data,$method,$merchantKey);
         
          $resa = json_decode($this->post_token($secret,$url,"6A8XM3G6UA9NGGMDPNBGVXARBKCJBQJE"),true);
         
          if($resa['code'] != '0000'){
               return json_encode($res);
          }
         
         
          
           $uinfo = db('xy_users')->field('pwd,salt,tel,username')->find($uid);
                     $res = db('xy_recharge')
                     ->insert([
                         'id'        => $orderId,
                         'uid'       => $uid,
                         'tel'       => $uinfo['tel'],
                         'real_name' => $uinfo['username'],
                         'num'       => $num,
                         'status' => 1,
                         'addtime'   => time(),
                         "pay_name" => "CrushPay",
                         ]);
                         
  
          header("Location: {$resa['data']['payUrl']}");die; 
    }
    
    
    /**
 * post 带token请求
 */ 
  function post_token($data,$url,$token)
  {
        $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
         // POST数据
         curl_setopt($ch, CURLOPT_POST, 1);
         // 把post的变量加上
         $header = array("content-type: application/json; charset=UTF-8","Authorization: $token");
         curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
         curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
         $output = curl_exec($ch);
         curl_close($ch);
         return $output;
  } 
    
     /**加密
     * @param array $data
     * @return string
     */
    public function encryptionAes($data,$method,$merchantKey)
    {
        $jsonData = json_encode($data,true);
        $aesSecret = bin2hex(openssl_encrypt($jsonData, $method,$merchantKey,  OPENSSL_RAW_DATA, $merchantKey));
        return $aesSecret;
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
    
 function curl_post1($url, $post_data, $header = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    } 
    
    
    public function titopay()
    {
        $num = input('num/f',0);
         $uid = $this->usder_id;
        if(!$num ) return json(['code'=>1,'info'=>yuylangs('参数错误')]);
         $ids = "EP".date("YmdHis").rand('0000','9999');
         
            $post_data['merNo'] = '50001';
            $post_data['terNo'] = '50001002';
            $post_data['orderNo'] = $ids;
            $post_data['orderAmount'] = $num;
            $post_data['orderCurrency'] = "USD";
            $post_data['paymentMethod'] = 'Ewallet';
            $post_data['billFirstName'] = 'name';
            $post_data['billEmail'] = '123@123.com';
            $post_data['billPhone'] = '18888888888';
            $post_data['returnUrl'] = get_http_type().$_SERVER['SERVER_NAME'];
            $post_data['notifyUrl'] = get_http_type().$_SERVER['SERVER_NAME'].'/index/api/rpay_hui111';
            $post_data['ip'] = $_SERVER["REMOTE_ADDR"];
         
          
            $url = "https://secures.titopay.com/ewallet";
            $key = "K24ebf6k";
            
            $signInfo = $post_data['merNo'] . $post_data['terNo'] .$post_data['orderNo']. $post_data['orderCurrency']. $post_data['orderAmount'].$post_data['returnUrl'].$key;
          
            $signInfo = $this->encrypt_sha256($signInfo);
            $post_data['signInfo'] = $signInfo;
            
          
            
            
            $res = $this->curPost($url,$post_data);
            dump(json_decode($res,true));die;
         
    }
    
    
    
      function curPost($url,$data)

    {

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_HEADER, 0);//不抓取头部信息。只返回数据

        curl_setopt($curl, CURLOPT_TIMEOUT,1000);//超时设置

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//1表示不返回bool值

        curl_setopt($curl, CURLOPT_POST, 1);

        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));//重点

        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {

            return curl_error($curl);

        }

        curl_close($curl);

        return $response;

    }

    
    
    public function encrypt_sha256($str = ''){

    return hash("sha256", $str);

  }
  

  
  
  public function gateway_aspx()
  {
      
      
      $num = input('num/f',0);
         $uid = $this->usder_id;
        if(!$num ) return json(['code'=>1,'info'=>yuylangs('参数错误')]);
         $orderId = "EP".date("YmdHis").rand('0000','9999');
          $xy_user = db('xy_users')->find($uid);
          
          
           $slhttp = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://'; 
           $page_url = $slhttp. $_SERVER['SERVER_NAME'];
        	$notify_url = $page_url.'/index/api/kbpay_hui';
        	
        	$data["merchant_no"] = "6061181";
        	$data["payment_code"] = "1034";
        	$data["order_no"] = $orderId;
        	$data["order_amount"] = $num*100;
        	$data["notice_url"] = $notify_url;
        	$data["timestamp"] = time();
        	$data["return_url"] = $page_url;
        	$data['key'] = "P54yZt0SakRMmLMibVVodYKJAKEgjG2r";
        	$data = ASCII2($data);
            unset($data['key']);
        	$url = "https://api.kbpay.cc/pay/submit";
        	$res1 = json_decode($this->curl_post1($url,$data,'application/x-www-form-urlencoded'),true);
        	
        	if($res1['code'] != 0){
        	    return json_encode($res1);
        	}
            
        	
        	$uinfo = db('xy_users')->field('pwd,salt,tel,username')->find($uid);
                     $res = db('xy_recharge')
                     ->insert([
                         'id'        => $orderId,
                         'uid'       => $uid,
                         'tel'       => $uinfo['tel'],
                         'real_name' => $uinfo['username'],
                         'num'       => $num,
                         'status' => 1,
                         'addtime'   => time(),
                         "pay_name" => "CrushPay",
                         ]);
                         
        header("Location:".$res1['data']['url']);die;
        
  }
  
    
    public function entpay()
    {
        $num = input('num/f',0);
      //  $type = input('post.type/s','card');

         $uid = $this->usder_id;
        if(!$num ) return json(['code'=>1,'info'=>yuylangs('参数错误')]);
         $ids = "EP".date("YmdHis").rand('0000','9999');
         
        //时间限制 //TODO
        // $res = check_time(config('chongzhi_time_1'),config('chongzhi_time_2'));
        // $str = config('chongzhi_time_1').":00  - ".config('chongzhi_time_2').":00";
        // if($res) return json(['code'=>1,'info'=>yuylangs('禁止在').$str.yuylangs('以外的时间段执行当前操作!')]);


        // //
        // $pay = db('xy_pay')->where('name2',$type)->find();
        // if ($num < $pay['min']) return json(['code'=>1,'info'=>yuylangs('pat_q_min100').$pay['min']]);
        // if ($num > $pay['max']) return json(['code'=>1,'info'=>yuylangs('充值不能大于').$pay['max']]);
        
        //参数如下，务必按顺序传值
       
            $post_data['merch_id'] = 79;
            $post_data['payment_id'] = 3;
            $post_data['order_sn'] = $ids;
            $post_data['amount'] = $num;
            $post_data['goods_info'] = "testtes";
            $post_data['ip'] = $_SERVER["REMOTE_ADDR"];
          
            $post_data['notify_url'] = 'http://104.233.194.101/index/api/sasdas';
            //按字典正序排序传⼊的参数
            ksort($post_data);
            $sign_str='';
            foreach($post_data as $pk=>$pv){
             $sign_str.="{$pk}={$pv}&"; }
            $sign_str.="key=26723493ca6c668c03666cb47dec9830ac153639";
            $post_data['sign']=md5($sign_str);
            //开始提交--------------------------------------------------------------
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://e.entpay.org/api/pay/order');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            $output = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($output,true);
             
          //  dump($result['data']);
            if(count($result['data']) < 1){
                return "NO";
            }
            
            $uinfo = db('xy_users')->field('pwd,salt,tel,username')->find($uid);
                     $res = db('xy_recharge')
                     ->insert([
                         'id'        => $ids,
                         'uid'       => $uid,
                         'tel'       => $uinfo['tel'],
                         'real_name' => $uinfo['username'],
                         'num'       => $num,
                         'status' => 1,
                         'addtime'   => time(),
                         "pay_name" => "entpay",
                         ]);
                         
        header("Location: {$result['data']['pay_pageurl']}");
        
    }
    
    
    
    function edit_pwd2()
    {
        $user = Db::name('xy_users')->where('id', $this->usder_id)->find();
        $this->assign('user', $user);
        return $this->fetch();
    }

    public function set_pwd2()
    {
        if (!request()->isPost()) return json(['code' => 1, 'info' => yuylangs('qqcw')]);
        $uid = $this->usder_id;
        $o_pwd = input('old_pwd/s', '');
        $pwd = input('new_pwd/s', '');
        $uinfo = Db::name('xy_users')->field('pwd2,salt2,tel')->find($uid);
        if ($uinfo['pwd2']) {
            if ($uinfo['pwd2'] != sha1($o_pwd . $uinfo['salt2'] . config('pwd_str'))) {
                return json(['code' => 1, 'info' => yuylangs('pass_error')]);
            }
        }
       
        $res = model('admin/Users')->reset_pwd($uinfo['tel'], $pwd, 2);
        
        $info = Db::name('xy_bankinfo')->where('uid', $uid)->find();
        // if (empty($info)) {
        //     $res['url'] = url('/index/my/bind_bank');
        // }
        return json($res);
    }

    function deposit()
    {
        $user = Db::name('xy_users')->where('id', $this->usder_id)->find();
        $user['tel'] = substr_replace($user['tel'], '****', 3, 4);
        $bank = Db::name('xy_bankinfo')->where(['uid' => $this->usder_id])->find();
        if (!$bank) {
            return $this->redirect(url('index/my/bind_bank'));
        }
        //$bank['cardnum'] = substr_replace($bank['cardnum'], '****', 7, 7);
        $this->assign('info', $bank);
        $this->assign('user', $user);
        //提现限制
        $level = $user['level'];
        !$user['level'] ? $level = 0 : '';
        $ulevel = Db::name('xy_level')->where('level', $level)->find();
        $this->usdt_pay_info = Db::name('xy_pay')->where('name2', 'bit')->find();
        $this->shouxu = $ulevel['tixian_shouxu'];
        $notices = Db::name('xy_index_msg')->where('id', 14)->find();
        
         $lang= $this->language;
        
         $content = $notices[$lang];
        $title = $notices["t_".$lang];
        
        $this->desc_info = $content;
        
        
        $this->pysa = Db::table("xy_pay")->find('8');
        
         $bank2 = Db::name('xy_bankinfo')->where(['uid' => $this->usder_id])->select();
        return $this->fetch();
    }

    function deposit_zfb()
    {
        $user = Db::name('xy_users')->where('id', $this->usder_id)->find();
        $this->assign('title', yuylangs('alipay_withdraw'));

        $this->assign('type', 'zfb');
        $this->assign('user', $user);
        return $this->fetch('deposit_zfb');
    }
    
    public function jiaoyUsdt()
    {
         $uid = $this->usder_id;
         
         $res = db('xy_bankinfo')
                ->where('uid', $uid)
                ->find();
        if($res['usdt_type'] || $res['usdt_diz']){
           return json(['code' => 1]);
        }else{
             return json(['code' => 2]);
        }
    }

    //提现接口
    public function do_deposit()
    {
        $res = check_time(config('tixian_time_1'), config('tixian_time_2'));
        $str = config('tixian_time_1') . ":00  - " . config('tixian_time_2') . ":00";
        if ($res) return json(['code' => 1, 'info' => yuylangs('ctrl_jzz') . $str . yuylangs('ctrl_ywsjd')]);

        //交易密码
        $pwd2 = input('post.paypassword/s', '');
        $info = Db::name('xy_users')->field('pwd2,salt2')->find($this->usder_id);
        if ($info['pwd2'] == '') {
            return json(['code' => 2, 'info' => yuylangs('not_jymm')]);
        }
        $userOrderCheck = $this->check_deal(2);
        if ($userOrderCheck && empty($userOrderCheck['endRal'])) return json($userOrderCheck);

        //银行卡
        $bankinfo = Db::name('xy_bankinfo')->where('uid', $this->usder_id)->where('id',input('bid', ''))->where('status', 1)->find();
        $type = input('post.type/s', '');
        if (!$bankinfo) {
            return json(['code' => 3, 'info' => yuylangs('not_put_bank')]);
        }
       // $bankList = $this->getBankList();
        // if (!isset($bankList[$bankinfo['bank_code']])) {
        //     return json(['code' => 3, 'info' => yuylangs('bank_q_nums')]);
        // }
        if (request()->isPost()) {
            if(sysconf('withdrawal_switch') != 1){
                $msg = sysconf('withdrawal_close_msg');
                return json([
                    'code' => 1,
                    'info' => $msg ?: translate('withdrawal not enabled'),
                ]);
            }

            $uid = $this->usder_id;
            if ($info['pwd2'] != sha1($pwd2 . $info['salt2'] . config('pwd_str')) ) {
                return json(['code' => 1, 'info' => yuylangs('pass_error')]);
            }
            $num = input('post.num', 0);
            $bkid = input('post.bid/d', '');
           // $token = input('post.token', '');
            $USDT_code = input('post.USDT_code/s', '');
           
           
            if (!$USDT_code && $type == 'USDT') {
                return json(['code' => 1, 'info' => yuylangs('with_q_usdt')]);
            }
            if ($num <= 0) return json(['code' => 1, 'info' => yuylangs('cscw')]);
            
           $bank_codes = Db::table("xy_pay")->where("is_payout",1)->find();
           if($bank_codes["py_status"] == 2){
               $types = 2;
           }else{
               $types = 1;
           }
            
            
            $uinfo = Db::name('xy_users')->field('id,withdrawal_status,recharge_num,deal_time,balance,level,group_id')->find($uid);
            $level = !empty($uinfo['level']) ? intval($uinfo['level']) : 0;
            $ulevel = Db::name('xy_level')->where('level', $level)->find();
            //用户未开启提现
            if($uinfo['withdrawal_status'] != 1){
                return json(['code' => 1, 'info' => translate('withdrawal not enabled')]);
            }
            //叠加组必须做完最后一单才行
            if ($uinfo['group_id'] > 0) {
                $max_order_num = Db::name('xy_group_rule')
                    ->where('group_id', $uinfo['group_id'])
                    ->order('order_num desc')
                    ->value('order_num');
                //如果规则组没有规则
                if (empty($max_order_num)) {
                    return json(['code' => 1, 'info' => yuylangs('hyddjycsbz')]);
                }
                $u_order_num = Db::name('xy_convey')
                    ->where('group_id', $uinfo['group_id'])
                    ->where('uid', $uinfo['id'])
                    ->order('addtime desc')
                    ->limit(1)
                    ->value('group_rule_num');
                //如果是最后一单
                if ($u_order_num < $max_order_num) {
                    return json([
                        'code' => 1,
                        'info' => sprintf(yuylangs('selfLevel_err'), $max_order_num),
                       // 'url' => url('index/rot_order/index')
                    ]);
                }
            } else {
                //提现限制
                // if ($level == 0) {
                //     return json(['code' => 1, 'info' => yuylangs('free_user_tx')]);
                // }

                $userSetting = Convey::instance()->get_user_order_setting($uinfo['id'], $level);
                if ($userSetting['min_deposit_order'] != $level['tixian_nim_order']) {
                    $ulevel['tixian_nim_order'] = $userSetting['min_deposit_order'];
                }

                $onum = Db::name('xy_convey')
                    ->where('uid', $uid)
                  //  ->where('level_id', $level)
                    ->where('addtime', 'between', [strtotime(date('Y-m-d')), time()])
                    ->count('id');
                   // dump($onum);die;
                $tixian_nim_order = $ulevel['tixian_nim_order'];
                $single_control = Db::name('xy_single_control')->where('uid', $uid)->find();
                $fixed_order_num = $single_control['fixed_order_num'];
                if($fixed_order_num > 0 && $single_control['single_control_status'] == 1){
                    $tixian_nim_order = $fixed_order_num;
                }
                if ($onum < $tixian_nim_order) {
                    return json([
                        'code' => 1,
                        'info' => sprintf(yuylangs('selfLevel_err'), $tixian_nim_order),
                        //'url' => url('index/rot_order/index'),
                         'min' => $tixian_nim_order
                    ]);
                }
            }

            $tixian_min = sysconf('withdrawal_min_amount');
            if($ulevel['tixian_min'] > 0){
                $tixian_min = $ulevel['tixian_min'];
            }
            if ($num < $tixian_min) {
                return json(['code' => 1, 'info' => yuylangs('userLevel_withdraw') . $ulevel['tixian_min'] . '-' . $ulevel['tixian_max'] . '!']);
            }

            $tixian_max = sysconf('withdrawal_max_amount');
            if($ulevel['tixian_max'] > 0){
                $tixian_max = $ulevel['tixian_max'];
            }
            if ($num >= $tixian_max) {
                return json(['code' => 1, 'info' => yuylangs('userLevel_withdraw') . $ulevel['tixian_min'] . '-' . $ulevel['tixian_max'] . '!']);
            }

            if ($level['tixian_nim_order']) {
                $ulevel['tixian_nim_order'] = $userSetting['min_deposit_order'];
            }

            $day_withdrawal_amount = sysconf('day_withdrawal_amount');
            if($day_withdrawal_amount > 0){
                $day_tx_amount = Db::name('xy_deposit')
                    ->where('uid', $uid)
                    ->where('addtime', 'between', [strtotime(date('Y-m-d 00:00:00')), time()])
                    ->where('status','in',[1,2])
                    ->sum('num');
                if($day_tx_amount + $num > $day_withdrawal_amount){
                    return json(['code' => 1, 'info' =>translate('daily withdrawal limit of ').$day_withdrawal_amount]);
                }
            }
            
            if ($num > $uinfo['balance']) return json(['code' => 1, 'info' => yuylangs('money_not')]);
            
            $zhis = $uinfo['balance']  - $num;
            
            if(config('master_bk_address') == 1){
                if($zhis <= config('free_balance')){
                 return  json(['code' => 1, 'info' => yuylangs('Experience money cannot be withdrawn')]);
               }   
            }
            
            
            //ruguo
            $new_balance = $uinfo['balance'] - $num;
            if(config('lxb_sy_bili5') == 1){
                if ($new_balance < $ulevel['num_min']) return json(['code' => 1, 'info' => yuylangs('with_ok_money') . config('currency') . ($uinfo['balance'] - $ulevel['num_min'])]);
            }

            $tixianCi = Db::name('xy_deposit')->where('uid', $uid)->where('addtime', 'between', [strtotime(date('Y-m-d 00:00:00')), time()])->count();
            if ($uinfo['deal_time'] == strtotime(date('Y-m-d'))) {
                //提现次数限制
                $day_max_withdrawal_count = sysconf('day_max_withdrawal_count');
                if($ulevel['day_withdraw_num'] > 0){
                    $day_max_withdrawal_count = $ulevel['day_withdraw_num'];
                }
                if ($tixianCi + 1 > $day_max_withdrawal_count) {
                    return  json(['code' => 1, 'info' => yuylangs('selfLevel_today_error')]);
                }
            } else {
                //重置最后交易时间
                Db::name('xy_users')->where('id', $uid)->update([
                    'deal_time' => strtotime(date('Y-m-d')),
                    'deal_count' => 0,
                    'recharge_num' => 0,
                    'deposit_num' => 0
                ]);
            }

            $shouxu_type = 1;//手续费类型：1-比例，2-固定金额
            $tixian_shouxu = config('withdrawal_fee_rate') ? config('withdrawal_fee_rate') / 100 : 0;
            $real_num = $num - ($num * $tixian_shouxu);//比例计算手续费
            //如果超过免费提现次数
            $free_withdrawal_count = sysconf('free_withdrawal_count');
            if($ulevel['day_withdraw_free_num'] > 0){
                $free_withdrawal_count = $ulevel['day_withdraw_free_num'];
            }
            if ($tixianCi + 1 >= $free_withdrawal_count) {
                $shouxu_type = 1;//手续费类型：1-比例，2-固定金额
                $withdrawal_excess_rate = sysconf('withdrawal_excess_rate') / 100;
                $real_num = $num - ($num * $withdrawal_excess_rate);//比例计算手续费
                $tixian_shouxu = $withdrawal_excess_rate;

                //如果等级设置固定手续费或者比例手续费
                if($ulevel['withdraw_fixed_fee'] > 0 || $ulevel['tixian_shouxu'] > 0){
                    //如果存在固定手续费，按照固定手续费计算,否则按照比例计算
                    if($ulevel['withdraw_fixed_fee'] > 0){
                        $shouxu_type = 2;
                        $real_num = $num - $ulevel['withdraw_fixed_fee'];
                        $tixian_shouxu = $ulevel['withdraw_fixed_fee'];
                    }else{
                        $shouxu_type = 1;//手续费类型：1-比例，2-固定金额
                        $real_num = $num - ($num * $ulevel['tixian_shouxu']);//比例计算手续费
                        $tixian_shouxu = $ulevel['tixian_shouxu'];
                    }
                }
            }
            $usdt_pay_info = Db::name('xy_pay')->where('name2', 'bit')->find();
            $id = getSn('CO');


            $user = Db::name('xy_users')->where('id', $this->usder_id)->find();
            try {
                Db::startTrans();
                $ddd = [
                    'id' => $id,
                    'uid' => $uid,
                    'bk_id' => $bkid,
                    'num' => $num,
                    'addtime' => time(),
                    'usdt' => $USDT_code,
                    'type' => $type,
                    'shouxu' => $tixian_shouxu,
                    'shouxu_type'=>$shouxu_type,
                    'real_num' => $real_num,
                    "types" => $types,
                    "before_balance" => $user['balance'],
                    "after_balance" => $user['balance']-$num,
                    "extra_params"=>json_encode($bankinfo,256),
                ];
                if (!empty($usdt_pay_info) && $type == 'USDT') {
                    $ddd['num2'] = $ddd['real_num'] * $usdt_pay_info['secret'];
                }
                $res = Db::name('xy_deposit')->insert($ddd);

                //提现日志
                $res2 = Db::name('xy_balance_log')
                    ->insert([
                        'uid' => $uid,
                        'oid' => $id,
                        'num' => $num,
                        'type' => 7, //TODO 7提现
                        'status' => 2,
                        'addtime' => time(),
                        "balance" => $uinfo['balance']
                    ]);
                $res1 = Db::name('xy_users')->where('id', $this->usder_id)->setDec('balance', $num);
                if ($res && $res1) {
                    Db::commit();
                    return json(['code' => 0, 'info' => yuylangs('czcg')]);
                } else {
                    Db::rollback();
                    return json(['code' => 1, 'info' => yuylangs('czsb')]);
                }
            } catch (\Exception $e) {
                Db::rollback();
                return json(['code' => 1, 'info' => yuylangs('czsb_jczhye'), 'msg' => $e->getMessage()]);
            }
        }
        return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => $bankinfo]);
    }

    //提现支付
    private function do_deposit_pay()
    {
        //https://sandbox.transfersmile.com/

    }

    //////get请求获取参数，post请求写入数据，post请求传人bkid则更新数据//////////
    public function do_bankinfo()
    {
        if (request()->isPost()) {
            $token = input('post.token', '');
            $data = ['__token__' => $token];
            $validate = \Validate::make($this->rule, $this->msg);
            if (!$validate->check($data)) return json(['code' => 1, 'info' => $validate->getError()]);

            $username = input('post.username/s', '');
            $bankname = input('post.bankname/s', '');
            $cardnum = input('post.cardnum/s', '');
            $site = input('post.site/s', '');
            $tel = input('post.tel/s', '');
            $status = input('post.default/d', 0);
            $bkid = input('post.bkid/d', 0); //是否为更新数据

            if (!$username) return json(['code' => 1, 'info' => yuylangs('khrmcbt')]);
            if (mb_strlen($username) > 30) return json(['code' => 1, 'info' => yuylangs('khrmczdcd')]);
            if (!$bankname) return json(['code' => 1, 'info' => yuylangs('yhmcbt')]);
            if (!$cardnum) return json(['code' => 1, 'info' => yuylangs('yhkbt')]);
            if (!$tel) return json(['code' => 1, 'info' => yuylangs('sjhbt')]);

            if ($bkid)
                $cardn = Db::table('xy_bankinfo')->where('id', '<>', $bkid)->where('cardnum', $cardnum)->count();
            else
                $cardn = Db::table('xy_bankinfo')->where('cardnum', $cardnum)->count();

            if ($cardn) return json(['code' => 1, 'info' => yuylangs('yhkhycz')]);

            $data = ['uid' => $this->usder_id, 'bankname' => $bankname, 'cardnum' => $cardnum, 'tel' => $tel, 'site' => $site, 'username' => $username];
            if ($status) {
                Db::table('xy_bankinfo')->where(['uid' => $this->usder_id])->update(['status' => 0]);
                $data['status'] = 1;
            }

            if ($bkid)
                $res = Db::table('xy_bankinfo')->where('id', $bkid)->where('uid', $this->usder_id)->update($data);
            else
                $data['addtime'] = time();
                $res = Db::table('xy_bankinfo')->insert($data);

            if ($res !== false)
                return json(['code' => 0, 'info' => yuylangs('czcg')]);
            else
                return json(['code' => 1, 'info' => yuylangs('czsb')]);
        }
        $bkid = input('id/d', 0); //是否为更新数据
        $where = ['uid' => $this->usder_id];
        if ($bkid !== 0) $where['id'] = $bkid;
        $info = Db::name('xy_bankinfo')->where($where)->select();
        if (!$info) return json(['code' => 1, 'info' => yuylangs('zwsj')]);
        return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => $info]);
    }

    //切换银行卡状态
    public function edit_bankinfo_status()
    {
        $id = input('post.id/d', 0);

        Db::table('bankinfo')->where(['uid' => $this->usder_id])->update(['status' => 0]);
        $res = Db::table('bankinfo')->where(['id' => $id, 'uid' => $this->usder_id])->update(['status' => 1]);
        if ($res !== false)
            return json(['code' => 0, 'info' => yuylangs('czcg')]);
        else
            return json(['code' => 1, 'info' => yuylangs('czsb')]);
    }

    //获取下级会员
    public function bot_user()
    {
        if (request()->isPost()) {
            $uid = input('post.id/d', 0);
            $token = ['__token__' => input('post.token', '')];
            $validate = \Validate::make($this->rule, $this->msg);
            if (!$validate->check($token)) return json(['code' => 1, 'info' => $validate->getError()]);
        } else {
            $uid = $this->usder_id;
        }
        $page = input('page/d', 1);
        $num = input('num/d', 10);
        $limit = ((($page - 1) * $num) . ',' . $num);
        $data = Db::name('xy_users')->where('parent_id', $uid)->field('id,username,headpic,addtime,childs,tel')->limit($limit)->order('addtime desc')->select();
        if (!$data) return json(['code' => 1, 'info' => yuylangs('zwsj')]);
        return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => $data]);
    }

    //修改密码
    public function set_pwd()
    {
        if (!request()->isPost()) return json(['code' => 1, 'info' => yuylangs('qqcw')]);
        $o_pwd = input('old_pwd/s', '');
        $pwd = input('new_pwd/s', '');
        $type = input('type/d', 1);
        $uinfo = Db::name('xy_users')->find($this->usder_id);
        if($type == 1){
            if ($uinfo['pwd'] != sha1($o_pwd . $uinfo['salt'] . config('pwd_str'))) return json(['code' => 1, 'info' => yuylangs('pass_error')]);
        }elseif($type == 2){
            if ($uinfo['pwd2'] != sha1($o_pwd . $uinfo['salt2'] . config('pwd_str'))) return json(['code' => 1, 'info' => yuylangs('pass_error')]);
        }
        
        
        $res = model('admin/Users')->reset_pwd($uinfo['tel'], $pwd, $type);
        return json($res);
    }

    public function set()
    {
        $uid = $this->usder_id;
        $this->info = Db::name('xy_users')->find($uid);
        return $this->fetch();
    }
    

    //我的下级
    public function get_user()
    {
        $uid = $this->usder_id;
        $type = input('post.type/d', 1);
        $page = input('page/d', 1);
        $num = input('num/d', 10);
        $limit = ((($page - 1) * $num) . ',' . $num);
        $uinfo = Db::name('xy_users')->field('*')->find($this->usder_id);
        $other = [];
        if ($type == 1) {
            $uid = $this->usder_id;
            $data = Db::name('xy_users')->where('parent_id', $uid)
                ->field('id,username,headpic,addtime,childs,tel')
                ->limit($limit)
                ->order('addtime desc')
                ->select();

            //总的收入  总的充值
            
           $ids1 = Db::name('xy_users')->where('parent_id', $uid)->field('id')->column('id');
           $cond=implode(',',$ids1);
            $cond = !empty($cond) ? $cond = " uid in ($cond)":' uid=-1';
            $other = [];
           $other['chongzhi'] = Db::name('xy_recharge')->where($cond)->where('status', 2)->sum('num');
           $other['tixian'] = Db::name('xy_deposit')->where($cond)->where('status', 2)->sum('num');
           $other['xiaji'] = count($ids1);
           
           

            $uids = model('admin/Users')->child_user($uid, 5);
            $uids ? $where[] = ['uid', 'in', $uids] : $where[] = ['uid', 'in', [-1]];
            $uids ? $where2[] = ['uid', 'in', $uids] : $where2[] = ['uid', 'in', [-1]];

            $other['chongzhi'] = Db::name('xy_recharge')->where($where2)->where('status', 2)->sum('num');
            $other['tixian'] = Db::name('xy_deposit')->where($where2)->where('status', 2)->sum('num');
            $other['xiaji'] = count($uids);


            //var_dump($uinfo);die;

            $iskou = 0;
            foreach ($data as &$datum) {
                $datum['addtime'] = date('Y/m/d H:i', $datum['addtime']);
                empty($datum['headpic']) ? $datum['headpic'] = '/public/img/head.png' : '';
                //充值
                $datum['chongzhi'] = Db::name('xy_recharge')->where('uid', $datum['id'])->where('status', 2)->sum('num');
                //提现
                $datum['tixian'] = Db::name('xy_deposit')->where('uid', $datum['id'])->where('status', 2)->sum('num');

                if ($uinfo['kouchu_balance_uid'] == $datum['id']) {
                    $datum['chongzhi'] -= $uinfo['kouchu_balance'];
                    $iskou = 1;
                }

                if ($uinfo['show_tel2']) {
                    $datum['tel'] = substr_replace($datum['tel'], '****', 3, 4);
                }
                if (!$uinfo['show_tel']) {
                    $datum['tel'] = yuylangs('wqx');
                }
                if (!$uinfo['show_num']) {
                    $datum['childs'] = yuylangs('wqx');
                }
                if (!$uinfo['show_cz']) {
                    $datum['chongzhi'] = yuylangs('wqx');
                }
                if (!$uinfo['show_tx']) {
                    $datum['tixian'] = yuylangs('wqx');
                }
            }

            $other['chongzhi'] -= $uinfo['kouchu_balance'];
            return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => $data, 'other' => $other]);

        } else if ($type == 2) {
            $ids1 = Db::name('xy_users')->where('parent_id', $uid)->field('id')->column('id');
            $cond = implode(',', $ids1);
            $cond = !empty($cond) ? $cond = " parent_id in ($cond)" : ' parent_id=-1';

            //获取二代ids
            $ids2 = Db::name('xy_users')->where($cond)->field('id')->column('id');
            $cond2 = implode(',', $ids2);
            $cond2 = !empty($cond2) ? $cond2 = " uid in ($cond2)" : ' uid=-1';
            $other = [];
            $other['chongzhi'] = Db::name('xy_recharge')->where($cond2)->where('status', 2)->sum('num');
            $other['tixian'] = Db::name('xy_deposit')->where($cond2)->where('status', 2)->sum('num');
            $other['xiaji'] = count($ids2);


            $data = Db::name('xy_users')->where($cond)
                ->field('id,username,headpic,addtime,childs,tel')
                ->limit($limit)
                ->order('addtime desc')
                ->select();

            //总的收入  总的充值

            foreach ($data as &$datum) {
                empty($datum['headpic']) ? $datum['headpic'] = '/public/img/head.png' : '';
                $datum['addtime'] = date('Y/m/d H:i', $datum['addtime']);
                //充值
                $datum['chongzhi'] = Db::name('xy_recharge')->where('uid', $datum['id'])->where('status', 2)->sum('num');
                //提现
                $datum['tixian'] = Db::name('xy_deposit')->where('uid', $datum['id'])->where('status', 2)->sum('num');

                if ($uinfo['show_tel2']) {
                    $datum['tel'] = substr_replace($datum['tel'], '****', 3, 4);
                }
                if (!$uinfo['show_tel']) {
                    $datum['tel'] = yuylangs('wqx');
                }
                if (!$uinfo['show_num']) {
                    $datum['childs'] = yuylangs('wqx');
                }
                if (!$uinfo['show_cz']) {
                    $datum['chongzhi'] = yuylangs('wqx');
                }
                if (!$uinfo['show_tx']) {
                    $datum['tixian'] = yuylangs('wqx');
                }
            }

            return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => $data, 'other' => $other]);


        } else if ($type == 3) {
            $ids1 = Db::name('xy_users')->where('parent_id', $uid)->field('id')->column('id');
            $cond = implode(',', $ids1);
            $cond = !empty($cond) ? $cond = " parent_id in ($cond)" : ' parent_id=-1';
            $ids2 = Db::name('xy_users')->where($cond)->field('id')->column('id');

            $cond2 = implode(',', $ids2);
            $cond2 = !empty($cond2) ? $cond2 = " parent_id in ($cond2)" : ' parent_id=-1';

            //获取三代的ids
            $ids22 = Db::name('xy_users')->where($cond2)->field('id')->column('id');
            $cond22 = implode(',', $ids22);
            $cond22 = !empty($cond22) ? $cond22 = " uid in ($cond22)" : ' uid=-1';
            $other = [];
            $other['chongzhi'] = Db::name('xy_recharge')->where($cond22)->where('status', 2)->sum('num');
            $other['tixian'] = Db::name('xy_deposit')->where($cond22)->where('status', 2)->sum('num');
            $other['xiaji'] = count($ids22);

            //获取四代ids
            $cond4 = implode(',', $ids22);
            $cond4 = !empty($cond4) ? $cond4 = " parent_id in ($cond4)" : ' parent_id=-1';
            $ids4 = Db::name('xy_users')->where($cond4)->field('id')->column('id'); //四代ids

            //充值
            $cond44 = implode(',', $ids4);
            $cond44 = !empty($cond44) ? $cond44 = " uid in ($cond44)" : ' uid=-1';
            $other['chongzhi4'] = Db::name('xy_recharge')->where($cond44)->where('status', 2)->sum('num');
            $other['tixian4'] = Db::name('xy_deposit')->where($cond44)->where('status', 2)->sum('num');
            $other['xiaji4'] = count($ids4);


            //获取五代
            $cond5 = implode(',', $ids4);
            $cond5 = !empty($cond5) ? $cond5 = " parent_id in ($cond5)" : ' parent_id=-1';
            $ids5 = Db::name('xy_users')->where($cond5)->field('id')->column('id'); //五代ids

            //充值
            $cond55 = implode(',', $ids5);
            $cond55 = !empty($cond55) ? $cond55 = " uid in ($cond55)" : ' uid=-1';
            $other['chongzhi5'] = Db::name('xy_recharge')->where($cond55)->where('status', 2)->sum('num');
            $other['tixian5'] = Db::name('xy_deposit')->where($cond55)->where('status', 2)->sum('num');
            $other['xiaji5'] = count($ids5);

            $other['chongzhi_all'] = $other['chongzhi'] + $other['chongzhi4'] + $other['chongzhi5'];
            $other['tixian_all'] = $other['tixian'] + $other['tixian4'] + $other['tixian5'];

            $data = Db::name('xy_users')->where($cond2)
                ->field('id,username,headpic,addtime,childs,tel')
                ->limit($limit)
                ->order('addtime desc')
                ->select();

            //总的收入  总的充值

            foreach ($data as &$datum) {
                $datum['addtime'] = date('Y/m/d H:i', $datum['addtime']);
                empty($datum['headpic']) ? $datum['headpic'] = '/public/img/head.png' : '';
                //充值
                $datum['chongzhi'] = Db::name('xy_recharge')->where('uid', $datum['id'])->where('status', 2)->sum('num');
                //提现
                $datum['tixian'] = Db::name('xy_deposit')->where('uid', $datum['id'])->where('status', 2)->sum('num');

                if ($uinfo['show_tel2']) {
                    $datum['tel'] = substr_replace($datum['tel'], '****', 3, 4);
                }
                if (!$uinfo['show_tel']) {
                    $datum['tel'] = yuylangs('wqx');
                }
                if (!$uinfo['show_num']) {
                    $datum['childs'] = yuylangs('wqx');
                }
                if (!$uinfo['show_cz']) {
                    $datum['chongzhi'] = yuylangs('wqx');
                }
                if (!$uinfo['show_tx']) {
                    $datum['tixian'] = yuylangs('wqx');
                }
            }
            return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => $data, 'other' => $other]);
        }


        return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => $data]);
    }

    /**
     * 充值记录
     */
    public function recharge_admin()
    {  
        $parameter["page"] = input("page",1);
        $parameter["size"] = input("size",10);
        $id = $this->usder_id;
        $where = [];
        $parameter['rechagreCount'] = Db::name('xy_recharge')
            ->where('uid', $id)
            ->where('status', 2)
            ->sum('num');
        
         $parameter['list'] = Db::table('xy_recharge')
                             ->where('uid', $id)->where($where)
                             ->field("id,num,addtime,status")
                             ->order('id desc')
                             ->page($parameter["page"],$parameter["size"])
                             ->select();
        $parameter['paging'] = 1;
        if(count($parameter['list']) < $parameter["size"]){
           $parameter['paging'] = 0; 
        } 
            
       return json_encode(['code'=>0,'msg'=>'success','data'=>$parameter]);
    }
    
    
    function pivate_key_encrypt($data, $pivate_key)
	{
        $pivate_key = '-----BEGIN PRIVATE KEY-----'."\n".$pivate_key."\n".'-----END PRIVATE KEY-----';
        $pi_key = openssl_pkey_get_private($pivate_key);
        $crypto = '';
        foreach (str_split($data, 117) as $chunk) {
            openssl_private_encrypt($chunk, $encryptData, $pi_key);
            $crypto .= $encryptData;
        }

        return base64_encode($crypto);
    }

    function public_key_decrypt($data, $public_key)
    {
        $public_key = '-----BEGIN PUBLIC KEY-----'."\n".$public_key."\n".'-----END PUBLIC KEY-----';
        $data = base64_decode($data);
        $pu_key =  openssl_pkey_get_public($public_key);
        $crypto = '';
        foreach (str_split($data, 128) as $chunk) {
            openssl_public_decrypt($chunk, $decryptData, $pu_key);
            $crypto .= $decryptData;
        }

        return $crypto;
    }
   
	
	public function prepaidOrder()
	{
	    
	    $num = input('num/f',0);
         $uid = $this->usder_id;
        if(!$num ) return json(['code'=>1,'info'=>yuylangs('参数错误')]);
         $orderId = "EP".date("YmdHis").rand('0000','9999');
          $xy_user = db('xy_users')->find($uid);
	    
	    
	// 平台公钥，从密钥配置中获取
	// platform public key, from Secret key config
	$platPublicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDvvUjP1FBofLC2nl6QoAqmMDJsw+5NvsxwmrbzAWVJKwTeUIKxzB2DA7bHXDW/05ZpzyzFS8/G50zGmGnInDAYHDqBcIm/++ZbFrqeJGk4Vz1iYef70N2JRfo1eI6J1XeUk18ITn5CSGSoPYsj0X3AVKUCs3TEs86Js4kgLShT5wIDAQAB';
    // 商户私钥，商户自己生成
    // mchchant private key
    $mchPrivateKey = 'MIICdQIBADANBgkqhkiG9w0BAQEFAASCAl8wggJbAgEAAoGBAO+9SM/UUGh8sLaeXpCgCqYwMmzD7k2+zHCatvMBZUkrBN5QgrHMHYMDtsdcNb/TlmnPLMVLz8bnTMaYacicMBgcOoFwib/75lsWup4kaThXPWJh5/vQ3YlF+jV4jonVd5STXwhOfkJIZKg9iyPRfcBUpQKzdMSzzomziSAtKFPnAgMBAAECgYBdeJ966HyxQGxlxzl3ie6c/Q2r+nhfN5TeEnRiKpki/fLX+uv6Bms7Oad58ynBsO1kM7Jw+i34jxYQGDymSr805hvX3i1bvlTqww2fu2bMhNnuF3e2gnSb0S8Z2Jv8U6yxclJ1pAV/ur72bMqBdseoxfKIEVR1XWweYCzplfbaAQJBAPo3seU52hskR4IUuJJJHy4lHuPP3rrxic9B3C9V/LzTAHslW40i9N9MURnjo0pBSGskKGteW8e4H2c9oVxaSzECQQD1R5mVMsi/Fp/DiDaXq6QGPrTPoQfS9xNxeynrjASvEyJzjrbzGTiRqgq37EnaVz46V4PXRU+TbS4P6KcFsxqXAkAb5tIDiav0ktsWelEKnvTHJISJSsi/d+eyINn4vVHtjGnlUYkf9+HudIgmpueyhA0bRXDsaB077CA0Vv8DWV5BAkBOyxJ2UFsWr7DhAlfvPy8w5mH1NRirV73CPbuItHEowK/XiWgSDe8TNBm/XcOXxWDzIvvyYoyeonsily1YcmG/AkBoJxKAd+ZpCkSndt/sSBCGk5BrSRaxlFn14QadcLZzPf0C1d4xaxKp/7by7GWNkSXtM2yzrooFqyzIzo75TNyI';
    // 商户ID，从商户信息中获取
    // merchent ID from vntask, from User info 
    $merchantCode = 'S820220914122535000007';
    // 支付金额 pay money
    $payMoney = (int)$num;
 
    //BT (PERMATA BANK)
    $method = 'BT';
    // 商户订单号
    // Merchant system unique order number
    $orderNum = $orderId;
    // 描述
    // The virtual account description
    $productDetail = 'Test Pay';
    $dateTime = date("YmdHis",time());
    // 邮箱
    // Customer's email address
    $email = 'test@test.com';
    // 手机号码
    // Customer's mobile number
    $phone = '082112345678';
    // 在付款确认页面显示的转账对象
    // Display name on bank confirmation display
    $name = 'Neo';
    // 回调地址
    // url for callback
    $notifyUrl = get_http_type().$_SERVER['SERVER_NAME'].'/index/api/qepay_fuhui1';
    // 重定向地址
    // url for redirect
    $redirectUrl = get_http_type().$_SERVER['SERVER_NAME'];
    // 订单过期时间 Order expiration time
    // 非必填
    $expiryPeriod = '1000';

    $params = array(
        'merchantCode' => $merchantCode,
        'payMoney' => $payMoney,
        'method' => $method,
        'orderNum' => $orderNum,
        'productDetail' => $productDetail,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'notifyUrl' => $notifyUrl,
        'redirectUrl' => $redirectUrl,
        'dateTime' => $dateTime,
        'expiryPeriod' => $expiryPeriod
    );

    ksort($params);
    $params_str = '';
    foreach ($params as $key => $val) {
        $params_str = $params_str . $val;
    }


    $sign = $this->pivate_key_encrypt($params_str, $mchPrivateKey);

    $params['sign'] = $sign;

    $params_string = json_encode($params);
    $url = 'https://openapi.klikpay.link/gateway/prepaidOrder';
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($params_string))
    );
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    //execute post
    $request = json_decode(curl_exec($ch),true);
    
    if(empty($request['url'])){
        return json_encode($request);
    }
    
    
     $uinfo = db('xy_users')->field('pwd,salt,tel,username')->find($uid);
                     $res = db('xy_recharge')
                     ->insert([
                         'id'        => $orderId,
                         'uid'       => $uid,
                         'tel'       => $uinfo['tel'],
                         'real_name' => $uinfo['username'],
                         'num'       => $num,
                         'status' => 1,
                         'addtime'   => time(),
                         "pay_name" => "prepaidOrder",
                         ]);
                         
  
          header("Location: {$request['url']}");die; 
          
    
    dump($request);die;
    
	

	
	    
	}

    /**
     * 提现记录
     */
    public function deposit_admin()
    {
         $parameter["page"] = input("page",1);
        $parameter["size"] = input("size",10);
        $id = $this->usder_id;
        $where = [];
        $parameter['depositCount'] = Db::name('xy_deposit')
            ->where('uid', $id)
            ->where('status', 2)
            ->sum('num');
        
         $parameter['list'] = Db::table('xy_deposit')
                             ->where('uid', $id)->where($where)
                             ->field("id,num,addtime,status")
                             ->order('id desc')
                             ->page($parameter["page"],$parameter["size"])
                             ->select();
         foreach ($parameter['list'] as &$v){
             $v['num'] = number_format($v['num'],2);
         }
         unset($v);
        $parameter['paging'] = 1;
        if(count($parameter['list']) < $parameter["size"]){
           $parameter['paging'] = 0; 
        } 
            
       return json_encode(['code'=>0,'msg'=>'success','data'=>$parameter]);
    }

    /**
     * 团队
     */
    public function junior()
    {
        $ajax = input('ajax');
        if ($ajax == 1) {
            $uid = $this->usder_id;
            $arr = [];
            $start = input('start');
            $end = input('end');

            if (empty($start) && empty($end)) {
                $arr['date_range'] = yuylangs('team_all');
            }
            if ($start && $end) {
                $arr['date_range'] = $start . '~' . $end;
            } elseif ($start) {
                $arr['date_range'] = $start;
            }

            if (empty($start)) {
                $start = 0;
            } else {
                $start = strtotime($start);
            }
            if (empty($end)) {
                $end = time();
            } else {
                $end = strtotime($end);
            }

            //计算五级团队余额
            $uidAlls5 = model('admin/Users')->child_user($uid, 3, 1);
            //团队业绩
            $arr['team_yj'] = Db::name('xy_convey')
                ->where('status', 1)
                ->where('addtime', 'between', [$start, $end])
                ->where('uid', 'in', $uidAlls5 ? $uidAlls5 : [-1])
                ->sum('commission');
            $arr['team_count'] = count($uidAlls5);
            //我得到的佣金
            $arr['team_rebate'] = Db::name('xy_balance_log')
                ->where('addtime', 'between', [$start, $end])
                ->where('uid', $uid)
                ->where('type', 'in', [3, 6])
                ->where('status', 1)
                ->sum('num');
       

            $uids2 = model('admin/Users')->child_user($uid, 1, 0);
            $arr['team1_count'] = count($uids2);
            $arr['team1_yj'] = Db::name('xy_convey')
                ->where('status', 1)
                ->where('addtime', 'between', [$start, $end])
                ->where('uid', 'in', $uids2 ? $uids2 : [-1])
                ->sum('commission');
            //我得到的佣金
            $arr['team1_rebate'] = Db::name('xy_balance_log')
                ->where('addtime', 'between', [$start, $end])
                ->where('sid', 'in', $uids2 ? $uids2 : -1)
                ->where('uid', $uid)
                ->where('type', 6)
                ->where('status', 1)
                ->sum('num');

            $uids3 = model('admin/Users')->child_user($uid, 2, 0);
            $arr['team2_count'] = count($uids3);
            $arr['team2_yj'] = Db::name('xy_convey')
                ->where('status', 1)
                ->where('addtime', 'between', [$start, $end])
                ->where('uid', 'in', $uids3 ? $uids3 : [-1])
                ->sum('commission');
            //我得到的佣金
            $arr['team2_rebate'] = Db::name('xy_balance_log')
                ->where('addtime', 'between', [$start, $end])
                ->where('sid', 'in', $uids3 ? $uids3 : [-1])
                ->where('uid', $uid)
                ->where('type', 6)
                ->where('status', 1)
                ->sum('num');

            $uids4 = model('admin/Users')->child_user($uid, 3, 0);
            $arr['team3_count'] = count($uids4);
            $arr['team3_yj'] = Db::name('xy_convey')
                ->where('status', 1)
                ->where('addtime', 'between', [$start, $end])
                ->where('uid', 'in', $uids4 ? $uids4 : [-1])
                ->sum('commission');
            //我得到的佣金
            $arr['team3_rebate'] = Db::name('xy_balance_log')
                ->where('addtime', 'between', [$start, $end])
                ->where('sid', 'in', $uids4 ? $uids4 : [-1])
                ->where('uid', $uid)
                ->where('type', 6)
                ->where('status', 1)
                ->sum('num');
            return json($arr);
        }
        
        
        
        
        
        $uid = $this->usder_id;
        $this->user = Db::name('xy_users')->find($uid);
        if ($this->user['level'] == 0) {
            $this->showMessage(yuylangs('free_user_lxb'));
        }
        $where = [];
        $this->level = $level = input('get.level/d', 1);
        $this->uinfo = Db::name('xy_users')->where('id', $uid)->find();
        $this->tj_bili = Db::name('xy_level')->where('level', $this->uinfo['level'])->value('tj_bili');
        $this->tj_bili = explode("/", $this->tj_bili);
        $this->tj_bili[0] = isset($this->tj_bili[0]) ? floatval($this->tj_bili[0]) * 100 : 0;
        $this->tj_bili[1] = isset($this->tj_bili[1]) ? floatval($this->tj_bili[1]) * 100 : 0;
        $this->tj_bili[2] = isset($this->tj_bili[2]) ? floatval($this->tj_bili[2]) * 100 : 0;

        //计算五级团队余额
        $uidAlls5 = model('admin/Users')->child_user($uid, 5, 1);
        $uidAlls5 ? $whereAll[] = ['id', 'in', $uidAlls5] : $whereAll[] = ['id', 'in', [-1]];
        $uidAlls5 ? $whereAll2[] = ['uid', 'in', $uidAlls5] : $whereAll2[] = ['id', 'in', [-1]];
        $this->teamyue = Db::name('xy_users')->where($whereAll)->sum('balance');
        $this->teamcz = Db::name('xy_recharge')->where($whereAll2)->where('status', 2)->sum('num');
        $this->teamtx = Db::name('xy_deposit')->where($whereAll2)->where('status', 2)->sum('num');
        $this->teamls = Db::name('xy_balance_log')->where($whereAll2)->sum('num');
        $this->teamyj = Db::name('xy_convey')->where('status', 1)->where($whereAll2)->sum('commission');

        $uids1 = model('admin/Users')->child_user($uid, 1, 0);
        $this->zhitui = count($uids1);
        $uidsAll = model('admin/Users')->child_user($uid, 5, 1);
        $this->tuandui = count($uidsAll);

        $start = input('get.start/s', '');
        $end = input('get.end/s', '');
        if ($start || $end) {
            $start ? $start = strtotime($start) : $start = strtotime('2020-01-01');
            $end ? $end = strtotime($end . ' 23:59:59') : $end = time();
            $where[] = ['addtime', 'between', [$start, $end]];
        }

        $this->start = $start ? date('Y-m-d', $start) : '';
        $this->end = $end ? date('Y-m-d', $end) : '';

        $uids5 = model('admin/Users')->child_user($uid, $level, 0);
        $uids5 ? $where[] = ['u.id', 'in', $uids5] : $where[] = ['u.id', 'in', [-1]];

        $this->today = date("Y-m-d", time());
        $this->yesterday = date("Y-m-d", strtotime("-1 day"));
        $this->week = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y")));

        $this->_query('xy_users')->alias('u')
            ->where($where)->order('id desc')->page();

    }


}