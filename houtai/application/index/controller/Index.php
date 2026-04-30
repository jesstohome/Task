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

/**
 * 应用入口
 * Class Index
 * @package app\index\controller
 */
class Index extends Base
{
   protected $authentication = ["home",'getTongji','get_level_list','get_msg','check_gift','claim_gift'];
    /**
     * 入口跳转链接
     */
    public function index()
    {
        $this->home();
    }

    public function home()
    {
        $uid = $this->usder_id;
        
        
        $info = Db::name('xy_users')->find($uid);
        //余额为扣除体验金后的金额
        $parameter['balance'] = number_format($info['balance'] - $info['lottery_money'],2);
        $parameter['level'] = $info['level'];
        $parameter['freeze_balance'] = number_format($info['freeze_amount'],2);//目前是以后台账变冻结为依据
        //$parameter['banner'] = Db::name('xy_banner')->select();
        $parameter['banner'] = [];
        
        $parameter['credit'] = $info['credit'];
        
    

        if (config('app_only')) {
            $dev = new \org\Mobile();
            $t = $dev->isMobile();
            if (!$t) {
                header('Location:/app');
            }
        }

        $list = [];
        //假数据
        // for($i=1;$i<=30;$i++){
        //     $v["addtime"] = date("m-d",time());
        //     $v["name"] = '52'.rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
        //     $v["today_income"] = number_format(rand(100000,1000000),2);
        //     $list[] = $v;
        // }
 
        $parameter['list'] = $list;

        $yes1 = strtotime(date("Y-m-d 00:00:00", strtotime("-1 day")));
        $yes2 = strtotime(date("Y-m-d 23:59:59", strtotime("-1 day")));
        


       
      //  $parameter['today_income'] = $parameter['tod_user_yongjin'] + $parameter['lixi_count_today'];
        
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y')); 
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1; 


        $beginYesterday=mktime(0,0,0,date('m'),date('d')-1,date('Y')); 
        $endYesterday=mktime(0,0,0,date('m'),date('d'),date('Y'))-1; 

        
        //今天收益
        // $parameter['yon1'] = number_format(Db::table('xy_balance_log')->where('uid', $uid)->where("type = 3 || type = 5 || type = 6")->where('addtime', 'between', [$beginToday, $endToday])->sum('num'),2);
        
        $parameter['yon1'] = number_format(Db::name('xy_convey')->where('uid',$uid)->where('c_status',1)->where('addtime', 'between', [$beginToday, $endToday])->sum('commission'),2);
        
         //昨日收益
    //   $parameter['Yesterdaysearnings'] = number_format(Db::table('xy_balance_log')->where('uid', $uid)->where("type = 3 || type = 5 || type = 6")->where('addtime', 'between', [$beginYesterday, $endYesterday])->sum('num'),2);
    
        $parameter['Yesterdaysearnings'] = number_format(Db::name('xy_convey')->where('uid',$uid)->where('c_status',1)->where('addtime', 'between', [$beginYesterday, $endYesterday])->sum('commission'),2);
       
        //总收益
        $zongyongjin = Db::table('xy_balance_log')->where('uid', $uid)->where("type = 5 || type = 6")->sum('num');
        
        
        //订单收益
        // $parameter['yon2'] = number_format(Db::table('xy_balance_log')->where('uid', $uid)->where("type = 3")->sum('num'),2);
        $orderyongjin = Db::name('xy_convey')->where('uid',$uid)->where('c_status',1)->sum('commission');
        
        $parameter['yon2'] = number_format($orderyongjin,2);
        
        //因为做单收益调整了日志，完整订单时把佣金和本金合到了一起，类型为3,所以再统计的时候不能再有type3,订单收益只能从订单处统计再和其它收益相加为总收益
        $parameter['yon3'] = number_format($zongyongjin + $orderyongjin,2);
        //团队收益
        $parameter['Teambenefits'] = number_format(Db::table('xy_balance_log')->where('uid', $uid)->where(" type = 5 || type = 6")->sum('num'),2);
        $parameter['dongjiejine'] = number_format($info['freeze_balance'],2);
        
        
        
        
        
       return json_encode(['code'=>0,"msg"=>'success',"data"=>$parameter]);
        
        
    }

    //获取首页弹框
    public function get_msg()
    {
        $id = input("id",1);
        $lang = input('lang','');
        if(empty($lang)){
            $lang= $this->language;
        }
        $notices = Db::name('xy_index_msg')->where('id', 1)->find();
        $content = $notices[$lang];
        $title = $notices["t_".$lang];

        // $parameter['index_icon'] = Db::name('xy_index_msg')->where('id', 'in',[2,3,4,12])->column($title,'id');
        $notice = htmlspecialchars_decode($content);
        return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => $notice]);
    }
    
    public function get_level_list()
    {
        $data = Db::table('xy_level')->where(['status'=>1,'show_status'=>1])->order('sort')->select();
        return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => $data]);
    }
    

    // 检查是否有礼包
    public function check_gift()
    {
        $uid = $this->usder_id;
        
        $where = [
                    ['uid', '=', $uid],
                    ['qkon', '=', 1],
                    ['order_mode', '=', 6],
                ];
        //已做单数
        $yizuo = Db::name('xy_convey')
                    ->where($where)
                    ->where('status', 'in', [1, 3, 5])
                    ->count('id');
                    
        $gift = Db::name('xy_gift_packages')->where('uid', $uid)->where('start_num', '<=', $yizuo)->where('status', 0)->find();
        if ($gift) {
            $gift_data = json_decode($gift['gift_data'], true);
            return json(['code' => 0, 'has_gift' => true, 'gift_id' => $gift['id'], 'selected' => $gift['selected_gift'], 'gift_data' => $gift_data]);
        } else {
            return json(['code' => 0, 'has_gift' => false]);
        }
    }

    // 领取礼包
    public function claim_gift()
    {
        $uid = $this->usder_id;
        $gift_id = input('gift_id', 0);
        //$selected_gift = input('selected_gift', 0); // 1,2,3
    
        $gift = Db::name('xy_gift_packages')->where('id', $gift_id)->where('uid', $uid)->where('status', 0)->find();
        if (!$gift) {
            return json(['code' => 1, 'info' => 'Gift package does not exist or has been claimed']);
        }
        
        $selected_gift = $gift['selected_gift']; // 1,2,3
        
        $gift_data = json_decode($gift['gift_data'], true);
        if (!$gift_data || !isset($gift_data['gift' . $selected_gift])) {
            return json(['code' => 1, 'info' => 'Gift package data error']);
        }
    
        $selected_data = $gift_data['gift' . $selected_gift];
        $order_model = new \app\admin\model\Convey();
        $message = '';
    
        try {
            if ($selected_gift == 1) {
                // 金额福利 - 纯余额操作，单独一个事务
                Db::startTrans();
                $amount = $selected_data['amount'];
                $current_balance = Db::name('xy_users')->where('id', $uid)->value('balance');
                Db::name('xy_users')->where('id', $uid)->setInc('balance', $amount);
                Db::name('xy_balance_log')->insert([
                    'uid'     => $uid,
                    'oid'     => 'LIBAO' . time() . rand(1000, 9999),
                    'num'     => $amount,
                    'type'    => 37,
                    'status'  => 1,
                    'addtime' => time(),
                    'balance' => $current_balance
                ]);
                $message = "Congratulations! You have received a gift package with $" . number_format($amount, 2) . " bonus!";
                Db::name('xy_gift_packages')->where('id', $gift_id)->update([
                    'status'         => 1,
                    'selected_gift'  => $selected_gift,
                    'claim_time'     => time(),
                    'is_completed'   => 1,
                    'completed_time' => time()
                ]);
                Db::name('xy_message')->insert([
                    'uid'     => $uid,
                    'type'    => 2,
                    'title'   => 'Get the gift pack',
                    'content' => $message,
                    'addtime' => time()
                ]);
                Db::commit();
                $result = ['code' => 0, 'info' => 'Gift pack successfully claimed'];
    
            } elseif ($selected_gift == 2) {
                $order_amount = floatval($selected_data['order_amount'] ?? 0);
                $commission   = floatval($selected_data['commission'] ?? 0);
                $message      = "Congratulations! You have received an order reward gift package with " . number_format($order_amount, 2) . " USD order amount and " . $commission . "% commission!";
    
                // ① 先单独提交礼包状态和消息
                Db::startTrans();
                Db::name('xy_gift_packages')->where('id', $gift_id)->update([
                    'status'         => 1,
                    'selected_gift'  => $selected_gift,
                    'claim_time'     => time(),
                    'is_completed'   => 1,
                    'completed_time' => time()
                ]);
                Db::name('xy_message')->insert([
                    'uid'     => $uid,
                    'type'    => 2,
                    'title'   => 'Get the gift pack',
                    'content' => $message,
                    'addtime' => time()
                ]);
                Db::commit();
    
                // ② 礼包状态提交后，再独立下单（create_order 内部有完整的自己的事务）
                Db::name('xy_users')->where('id', $uid)->update(['deal_status' => 2]);
                $result = $order_model->create_order($uid, 1, 'LB', $order_amount, $commission);
                if (is_array($result) && isset($result['code']) && $result['code'] != 0) {
                    throw new \Exception($result['info'] ?? 'Failed to create order');
                }
    
            } elseif ($selected_gift == 3) {
                $order_amount = floatval($selected_data['order_amount'] ?? 0);
                $commission   = 0;
                $order_count  = intval($selected_data['order_count'] ?? 0);
                $message      = "Congratulations! You have received a compound reward gift package with " . number_format($order_amount, 2) . " USD order amount and " . $order_count . " orders!";
    
                // ① 先单独提交礼包状态和消息
                Db::startTrans();
                Db::name('xy_gift_packages')->where('id', $gift_id)->update([
                    'status'         => 1,
                    'selected_gift'  => $selected_gift,
                    'claim_time'     => time(),
                    'is_completed'   => 1,
                    'completed_time' => time()
                ]);
                Db::name('xy_message')->insert([
                    'uid'     => $uid,
                    'type'    => 2,
                    'title'   => 'Get the gift pack',
                    'content' => $message,
                    'addtime' => time()
                ]);
                Db::commit();
    
                // ② 礼包状态提交后，循环独立下单
                // 每次 create_order 内部有完整独立的事务，互不干扰，不会累积锁
                for ($i = 0; $i < $order_count; $i++) {
                    Db::name('xy_users')->where('id', $uid)->update(['deal_status' => 2]);
                    $result = $order_model->create_order($uid, 1, 'LB', $order_amount, $commission);
                    if (is_array($result) && isset($result['code']) && $result['code'] != 0) {
                        continue;
                    }
                }
            }
            if($result['code'] == 0){
                $result['info'] = $message;
            }
            
    
            return json($result);
    
        } catch (\Exception $e) {
            try {
                Db::rollback();
            } catch (\Exception $re) {
                // 没有活跃事务时 rollback 会抛异常，忽略即可
            }
            return json(['code' => 1, 'info' => 'Claim failed：' . $e->getMessage()]);
        }
    }


    //获取首页图文
    public function getTongji()
    {
        $type = input('post.type/d', 1);
        $data = array();

        $data['user'] = Db::name('xy_users')->where('status', 1)->where('addtime', 'between', [strtotime(date('Y-m-d')) - 24 * 3600, time()])->count('id');
        $data['goods'] = Db::name('xy_goods_list')->count('id');;
        $data['price'] = Db::name('xy_convey')->where('status', 1)->where('endtime', 'between', [strtotime(date('Y-m-d')) - 24 * 3600, strtotime(date('Y-m-d'))])->sum('num');
        $user_order = Db::name('xy_convey')->where('status', 1)->where('addtime', 'between', [strtotime(date('Y-m-d')), time()])->field('uid')->Distinct(true)->select();
        $data['num'] = count($user_order);

        if ($data) {
            return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => $data]);
        } else {
            return json(['code' => 1, 'info' => yuylangs('zwsj')]);
        }
    }


    function getDanmu()
    {
        $barrages =    //弹幕内容
            array(
                array(
                    'info' => '用户173***4985开通会员成功',
                    'href' => '',

                ),
                array(
                    'info' => '用户136***1524开通会员成功',
                    'href' => '',
                    'color' => '#ff6600'

                ),
                array(
                    'info' => '用户139***7878开通会员成功',
                    'href' => '',
                    'bottom' => 450,
                ),
                array(
                    'info' => '用户159***7888开通会员成功',
                    'href' => '',
                    'close' => false,

                ), array(
                'info' => '用户151***7799开通会员成功',
                'href' => '',

            )
            );

        echo json_encode($barrages);
    }

}
