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
            //单控状态
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
            //方案组状态
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
                    ['order_mode', '=', 6],
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
        
        // 检查是否触发复数订单选项
        $compound_trigger = model('admin/Convey')->check_compound_order_trigger($uid);
        if ($compound_trigger) {
            if ($compound_trigger['type'] === 'immediate_trigger') {
                //如果当前复数选项已经开始做，直接派发复数订单
                
                $existing_log = $compound_trigger['log'];
                if($existing_log && $existing_log['option_id'] > 0){
                    $order_model = new \app\admin\model\Convey();

                    foreach($existing_log['custom_options'] as $key => $value){
                        if($value['option_id'] == $existing_log['option_id']){
                            if($existing_log['completed_orders'] < $value['order_count']){
                                Db::name('xy_users')->where('id', $uid)->update(['deal_status' => 2]);

                                Db::name('xy_compound_order_log')
                                    ->where('id', $existing_log['id'])
                                    ->update([
                                        'completed_orders' => $existing_log['completed_orders'] + 1,
                                        'update_time' => time()
                                    ]);

                                $res = $order_model->create_order($uid, 1, 'FS', $value['amount_value'], $value['commission_value'], 1);
                                
                                return json($res);
                                
                            }else{
                                Db::name('xy_compound_order_log')
                                    ->where('id', $existing_log['id'])
                                    ->update([
                                        'status' => 2,
                                        'update_time' => time()
                                    ]);
                                //不去终止，自动开始下面的正常抢单动作。
                            }                           
                        }
                    }
                }else{
                    $res['data'] = $compound_trigger;
                    $res['code'] = 1;
                    $res['info'] = '';
                    $res['status'] = 1;
                    return json($res);
                }
                
                
            }
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
        
     
        if ($user['group_id'] > 0) {
            $res = model('admin/Convey')->create_order_group($uid, $cid);
        } else {
            $res = model('admin/Convey')->create_order($uid, $cid);
        }

        // 检查是否触发复数订单
        if ($res['code'] == 0 && $compound_trigger) {
            if ($compound_trigger['type'] === 'continue_order') {
                    Db::name('xy_compound_order_log')
                    ->where('id', $compound_trigger['log']['id'])
                    ->update([
                        'trigger_count' => $compound_trigger['log']['trigger_count'] - 1,
                        'update_time' => time()
                    ]);
                }
        }
        $gift = Db::name('xy_gift_packages')->where('uid',$uid)->where('status', 0)->find();
        //存在未完成的礼包的时候触发单数减1
        if($gift){
            if($gift['start_num'] > 0){
                Db::name('xy_gift_packages')->where('id',$gift['id'])->setDec('start_num',1);
            }
        }
        return json($res);
    }

    /**
     * 启动复数订单
     */
    public function start_compound_order()
    {
        if (!$this->usder_id) {
            return json(['code' => 1, 'info' => 'Please log in first.']);
        }

        $option_id = input('option_id/d');
        if (!$option_id) {
            return json(['code' => 1, 'info' => 'Please select an option']);
        }
        
        $uid = $this->usder_id;
        
        $existing_log = Db::name('xy_compound_order_log')
            ->where('uid', $uid)
            ->where('status', 1) // 进行中
            ->order('create_time DESC')
            ->find();
            
        if (!$existing_log) {
            return json(['code' => 1, 'info' => 'Parameter error']);
        }
        
        Db::name('xy_compound_order_log')
                    ->where('id', $existing_log['id'])
                    ->update([
                        'option_id' => $option_id,
                        'update_time' => time()
                    ]);
                    
        $existing_log['custom_options'] = json_decode($existing_log['custom_options'],1);
        $order_model = new \app\admin\model\Convey();
        
        foreach($existing_log['custom_options'] as $key => $value){
            if($value['option_id'] == $option_id && $value['order_count'] > 0){
                Db::name('xy_users')->where('id', $uid)->update(['deal_status' => 2]);
                $result = $order_model->create_order($uid, 1, 'FS', $value['amount_value'], $value['commission_value'], 1);
            }
        }
        
        // 获取最后一次订单ID作为触发订单
        // $last_order = Db::name('xy_convey')
        //     ->where('uid', $this->usder_id)
        //     ->where('status', 1)
        //     ->order('id DESC')
        //     ->find();

        // if (!$last_order) {
        //     return json(['code' => 1, 'info' => '未找到触发订单']);
        // }

        // $result = model('admin/Convey')->start_compound_order(
        //     $this->usder_id,
        //     $option_id,
        //     0
        // );

        return json($result);
    }

    /**
     * 处理复数订单的下一单（自动调用）
     */
    public function process_compound_order_next()
    {
        if (!$this->usder_id) {
            return json(['code' => 1, 'info' => '请先登录']);
        }

        // 检查用户是否有未完成的复数订单
        $existing_log = model('admin/Convey')->check_compound_order_trigger($this->usder_id);

        if ($existing_log && $existing_log['type'] === 'immediate_trigger') {
            // 有未完成的复数订单且需要立即触发
            return json([
                'code' => 0,
                'compound_order_trigger' => $existing_log
            ]);
        } elseif ($existing_log && $existing_log['type'] === 'continue_order') {
            // 有未完成的复数订单但需要继续下单
            return json(['code' => 0, 'continue_order' => true]);
        }

        // 没有未完成的复数订单
        return json(['code' => 0]);
    }
}