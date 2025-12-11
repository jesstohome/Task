<?php

namespace app\index\controller;

use app\admin\model\Convey;
use think\Controller;
use think\Request;
use think\Db;


/**
 * 下单控制器
 */
class RotOrder extends Base
{
    /**
     * 首页
     */
    public function index()
    {
        
        $uid = $this->usder_id;
        
        
        $uinfo = Db::name('xy_users')->field("id,tel,username,invite_code,balance,freeze_balance,group_id,level")->find($uid);
       
        $uinfo['level'] = $uinfo['level'] > 0 ? intval($uinfo['level']) : 0;
        $uinfo['balance_format'] = number_format($uinfo['balance'],2);
        
        $parameter['lock_deal'] = number_format($uinfo['freeze_balance'],2);
        $parameter['level_info'] = Db::name('xy_level')->where('level', $uinfo['level'])->find();
        $parameter['level_name'] = $parameter['level_info']['name']; //级别名称

        $single_control = Db::name('xy_single_control')->where('uid',$uid)->find();
        
        if(!empty($single_control) && $single_control['single_control_status'] == 1 &&
            $single_control['fixed_order_num'] >= 1
        ){
            $where = [
                ['uid', '=', $uid],
                ['qkon', '=', 1],
                //   ['level_id', '=', $uinfo['level']],
                ['addtime', 'between', strtotime(date('Y-m-d')) . ',' . time()],
            ];

            $parameter['day_deal'] = number_format(Db::name('xy_convey')
                ->where($where)
                ->where('status', 'in', [1, 3, 5])
                ->sum('commission'),2);

            //已接单数量
            $parameter['day_d_count'] = Db::name('xy_convey')
                ->where("qkon = 1")
                ->where($where)
                ->where('status', 'in', [0, 1, 3, 5])
                ->count('id');

            //已接单数量
            $parameter['day_completed_count'] = Db::name('xy_convey')
                ->where("qkon = 1")
                ->where($where)
                ->where('status', 'in', [1, 3, 5])
                ->count('id');

            $orderSetting = Convey::instance()->get_user_order_setting($uid, $uinfo['level']);

            $parameter['order_num'] = $single_control['fixed_order_num']; //级别 订单数量
            $parameter['level_nums'] = $orderSetting['min_money']; //级别 最低金额
            $parameter['level_bili'] = $single_control['fixed_commission_bili']; //级别 佣金比例
        }else{
            
            if ($uinfo['group_id'] >0) {
                //进入了杀猪组  必须做完了一轮 才能进入下一轮
                //杀猪组信息
                $groupInfo = Db::name('xy_group')->where('id', $uinfo['group_id'])->find();
                
                if (empty($groupInfo)) exit();
                $day_deal = 0;
                
                list($day_d_count, $groupRule, $all_order_num) = Convey::instance()->get_user_group_rule($uinfo['id'], $uinfo['group_id']);
                // $day_d_count = $day_d_count + 1;
                if ($day_d_count > 0) $day_d_count = $day_d_count - 1;

                
                $day_d_count = $this->dangqshuyu1($uinfo['id'], $uinfo['group_id']);

                

                $max_order_num = Db::name('xy_group_rule')
                    ->where('group_id', $uinfo['group_id'])
                    ->order('order_num desc')
                    ->value('order_num');

                $where = [
                    ['uid', '=', $uid],
                    ['qkon', '=', 1],
                    //   ['level_id', '=', $uinfo['level']],
                    ['addtime', 'between', strtotime(date('Y-m-d')) . ',' . time()],
                ];
                

                //$parameter['day_deal'] = $day_d_count;
                $parameter['day_deal'] = number_format(Db::name('xy_convey')
                    ->where($where)
                    ->where('status', 'in', [1, 3, 5])
                    ->sum('commission'),2);

                $parameter['day_completed_count'] = $day_d_count;
                $parameter['day_d_count'] = $day_d_count; //已接单数量
                $parameter['order_num'] = $all_order_num; //级别 订单数量
                $parameter['level_nums'] = number_format($groupInfo['money'],2); //级别 最低金额
                $parameter['level_bili'] = $groupInfo['bili'] / 100; //级别 佣金比例
            } else {
                //普通模式
                $where = [
                    ['uid', '=', $uid],
                    ['qkon', '=', 1],
                    //   ['level_id', '=', $uinfo['level']],
                    ['addtime', 'between', strtotime(date('Y-m-d')) . ',' . time()],
                ];

                if(config('3_d_reward') == 1){
                    $where[] = ['level_id', '=', $uinfo['level']];
                }

                //已做单数量
                $parameter['day_deal'] = number_format(Db::name('xy_convey')
                    ->where($where)
                    ->where('status', 'in', [1, 3, 5])
                    ->sum('commission'),2);

                $parameter['day_completed_count'] = Db::name('xy_convey')
                    ->where($where)
                    ->where('status', 'in', [1, 3, 5])
                    ->count('id');

                //已接单数量
                $parameter['day_d_count'] = Db::name('xy_convey')
                    ->where("qkon = 1")
                    ->where($where)
                    ->where('status', 'in', [0, 1, 3, 5])
                    ->count('id');

                $orderSetting = Convey::instance()->get_user_order_setting($uid, $uinfo['level']);

                $parameter['order_num'] = $orderSetting['order_num']; //级别 订单数量
                $parameter['level_nums'] = number_format($orderSetting['min_money'],2); //级别 最低金额
                $parameter['level_bili'] = $orderSetting['bili']; //级别 佣金比例
            }
        }

          
        $parameter['order_incomplete_num'] = Db::name('xy_convey')
            ->where('uid', $uid)
            ->where("qkon = 1")
            ->where('status', 'in', [0, 5])
            ->count('id');
        $parameter['uinfo'] = $uinfo;
        $parameter['price'] = number_format($uinfo['balance'],2); //余额
        $parameter['level_list'] = Db::table('xy_level')->select(); //级别列表
        
        
        
        $notices = Db::name('xy_index_msg')->where('id', 20)->find();
        
         $lang= $this->language;
        
         $content = $notices[$lang];
        $title = $notices["t_".$lang];
        $parameter['desc_info'] = $content;
        
        $notices = Db::name('xy_index_msg')->where('id', 9)->find();
        
        
        
        $parameter["deal_zhuji_time"] = config('deal_zhuji_time');
        $parameter["deal_shop_time"] = config('deal_shop_time');
        
        $content = $notices[$lang];
        $title = $notices["t_".$lang];
        $parameter['rule_msg'] = $content;
        
       return json_encode(['code'=>0,'msg'=>'success','data'=>$parameter]);
    }

    /**
     *创建抢单抢单
     */
    public function submit_order()
    {
        $tmp = $this->check_deal();
        if ($tmp) return json($tmp);
        
        //$res = check_time(9, 22);
        //if($res) return json(['code'=>1,'info'=>'禁止在9:00~22:00以外的时间段执行当前操作!']);
        $cid = input('get.cid/d', 1);

        $res = check_time(config('order_time_1'), config('order_time_2'));
        $str = config('order_time_1') . ":00  - " . config('order_time_2') . ":00";
      //  if ($res) return json(['code' => 1, 'info' => yuylangs('qd_time_desc_1') . $str . yuylangs('qd_time_desc_2')]);
        $uid = $this->usder_id;
        $user = Db::name('xy_users')->find($uid);
        if ($user['level'] == 0) {
            if ($user['addtime'] + 86400 < time()) {
                //return json(['code' => 1, 'info' => yuylangs('free_end_time')]);
            }
        }

        //判断vip过期
        if ($this->vip_expire) {
            return json(['code' => 1, 'info' => translate('VIP has expired, please recharge')]);
        }
        
        if(config('master_cardnum') == 1){
           //获取收款地址信息
            $add_id = Db::name('xy_member_address')->where('uid', $uid)->value('id');
            if (!$add_id) return json([
                'code' => 1,
                'info' => yuylangs('not_address'),
               // 'url' => url('/index/my/edit_address')
            ]); 
        }
        
        //判断商品组
        $count = Db::name('xy_goods_list')->where('cid', '=', $cid)->count();
        if ($count < 1) return json(['code' => 1, 'info' => yuylangs('qd_error_kucun')]);
        //检查交易状态
        // $sleep = mt_rand(config('min_time'),config('max_time'));
        $res = Db::name('xy_users')->where('id', $uid)
            ->update(['deal_status' => 2]);//将账户状态改为等待交易
        //if ($res === false) return json(['code' => 1, 'info' => yuylangs('qd_error')]);
        // session_write_close();//解决sleep造成的进程阻塞问题
        // sleep($sleep);

        //单控设置优先触发
        $single_control = Db::name('xy_single_control')->where('uid',$uid)->find();
        if($single_control){
            if($single_control['single_control_status'] == 1){
                $res = model('admin/Convey')->create_order_single_control($uid, $cid);
                return json($res);
            }
        }
//        if($single_control){
//            if($single_control['single_control_status'] == 1 &&
//                $single_control['fixed_order_num'] >= 1 &&
//                $single_control['fixed_commission_bili'] > 0
//            ){
//                $res = model('admin/Convey')->create_order_single_control($uid, $cid);
//                return json($res);
//            }
//        }
        
     
        if ($user['group_id'] > 0) {
            //判断是否要出图片
            // $real = input('get.real/d', 0);
            // if (!$real) {
            //     list($orderNum, $groupRule) = model('admin/Convey')->get_user_group_rule($user['id'], $user['group_id']);
            //     // if ($groupRule['image']) {
            //     //     return json(['code' => 1, 'info' => '', 'image' => $groupRule['image'], 'real' => $real]);
            //     // }
            // }
            $res = model('admin/Convey')->create_order_group($uid, $cid);
        } else {
            $res = model('admin/Convey')->create_order($uid, $cid);
        }

        return json($res);
    }

    /**
     * 停止抢单
     */
    public function stop_submit_order()
    {
        $uid = $this->usder_id;
        $res = Db::name('xy_users')->where('id', $uid)->where('deal_status', 2)->update(['deal_status' => 1]);
        if ($res) {
            return json(['code' => 0, 'info' => yuylangs('czcg')]);
        } else {
            return json(['code' => 1, 'info' => yuylangs('czsb')]);
        }
    }
  

}