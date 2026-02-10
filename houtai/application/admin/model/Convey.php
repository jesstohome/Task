<?php

namespace app\admin\model;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\exception\ThrowableError;
use think\Model;
use think\Db;
use Cookie;

class Convey extends Model
{

    protected $table = 'xy_convey';

    //订单模式
    public $order_mode_array = [
        "single_control_level_bili"=>1,//单控等级卡单，兼容旧订单
        "single_control_level"=>1,//单控等级卡单
        "single_control_bili"=>2,//单控比例卡单
        "single_control_fixed_amount"=>3,//单控固定金额卡单
        "group_bili"=>4,//方案组比例卡单
        "group_fixed_amount"=>5,//方案组固定金额卡单
        "level_amount"=>6,//等级卡单
        "superposition"=>7,//方案组叠加模式卡单
        "fixed_replenishment_order"=>8,//方案组固定补单卡单
    ];

    //映射订单模式
    public function order_mode_map($val)
    {
        $val = (string)$val;
        $data = [
            '1'=>'单控等级比例卡单',
            '2'=>'单控比例卡单',
            '3'=>'单控固定金额卡单',
            '4'=>'方案组比例卡单',
            '5'=>'方案组固定金额卡单',
            '6'=>'等级卡单',
            '7'=>'方案组叠加模式卡单',
            '8'=>'方案组固定补单卡单'
        ];
        return isset($data[$val]) ? $data[$val] : '-' ;
    }

//    public $order_mode_map = [
//        "single_control_level_bili"=>1,//单控等级比例卡单【订单金额=会员等级设置的比例，佣金按照单控设置计算】
//        "single_control_bili"=>2,//单控比例卡单
//        "single_control_fixed_amount"=>3,//单控固定金额卡单
//        "group_bili"=>4,//方案组比例卡单
//        "group_fixed_amount"=>5,//方案组固定金额卡单
//        "level_amount"=>6,//等级卡单
//        "superposition"=>7,//方案组叠加模式卡单
//        "fixed_replenishment_order"=>8,//方案组固定补单卡单
//    ];


    public static function instance(): Convey
    {
        return new self();
    }

    /**
     * 创建订单
     * @param int $uid 用户编号
     * @param int $cid 商品组
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     * @throws \think\exception\PDOException
     */
    public function create_order($uid, $cid = 1)
    {
        $add_id = Db::name('xy_member_address')->where('uid', $uid)->value('id');//获取收款地址信息s
         if(config('master_cardnum') == 1){
            if (!$add_id) return ['code' => 1, 'info' => yuylangs('wszshdz')];
         }else{
             $add_id = 1;
         }
         
          $uinfo = Db::name('xy_users')->find($uid);
         $where1 = "1=1";
         if(config('3_d_reward') == 1){
                $where1 = ["level_id"=>$uinfo['level']];
            }
            
           
          $count = Db::name('xy_convey')
                ->where("qkon = 1")
                ->where('addtime', 'between', [strtotime(date('Y-m-d')), time()])
                ->where('uid', $uinfo['id'])
                ->where($where1)
                ->where('status', 1)
                ->count('id');//统计当天完成交易的订单
      
         $order_num = Db::table("xy_level")->where("level", $uinfo['level'])->value("order_num");
        
        if ($count >= $order_num) {
            return ['code' => 1, 'info' => yuylangs('hyddjycsbz'), 'endRal' => true];
        }
        
       
        if ($uinfo['deal_status'] != 2) return ['code' => 1, 'info' => yuylangs('qdyzz')];
        $level = $uinfo['level'] ? intval($uinfo['level']) : 0;
        $orderSetting = $this->get_user_order_setting($uid, $level);
        if ($uinfo['balance'] < $orderSetting['min_money']) {
            return [
                'code' => 1,
                'info' => sprintf(yuylangs('zhyebz'), ($orderSetting['min_money'] - $uinfo['balance']) . ""),
                'url' => url('index/ctrl/recharge')
            ];
        }
        $user_level = Db::table("xy_level")->where("level", $uinfo['level'])->find();
        $min = $user_level['grab_order_min_amount'];
        $max = $user_level['grab_order_max_amount'];
        //获取今天的订单数
        list($orderNum) = $this->get_user_group_rule($uinfo['id'], $uinfo['group_id']);
        //查询有没有打针订单
        $inyectar = $this->get_inyectar($uid, $orderNum);
        //打针
        if ($inyectar) {
            $min = $max = $uinfo['balance'] * $inyectar['scale'];
        }
        $goods = $this->rand_order($min, $max,$uid,$cid);
        
        
        if($goods['code'] == 1){
               return $goods;
           } 

        $id = getSn('UB');
        Db::startTrans();
        $res = Db::name('xy_users')->where('id', $uid)->update(['deal_status' => 3, 'deal_time' => strtotime(date('Y-m-d')), 'deal_count' => Db::raw('deal_count+1')]);//将账户状态改为交易中
         $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y')); 
                $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1; 
                
        $today_dan = Db::table('xy_convey')
            ->where('qkon',1)
            ->where("level_id",$uinfo['level'])
            ->where("uid = $uid")
            ->where("addtime >= $beginToday && addtime <= $endToday")
            ->count();
//        $commission = $goods['num'] * $orderSetting['bili'];  //交易佣金按照会员等级
//        //设置固定金额按照固定金额算
//        if($user_level['grab_order_fixed_commission'] > 0){
//            $commission = $user_level['grab_order_fixed_commission'];
//        }

        //等级设置佣金比例按照佣金比例计算，否则按照固定金额计算
        if($user_level['bili'] > 0){
            //0=百分比  1=固定值
            $commission_type = 0;
            $commission_value = $user_level['bili'] * 100;
            $commission = $goods['num'] * $user_level['bili'];
        }else{
            $commission_type = 1;
            $commission_value = $commission = $user_level['grab_order_fixed_commission'] ?: 0;
        }

        //等级卡单
        $order_mode = $this->order_mode_array['level_amount'];
        //插入佣金记录
        $c_data = [
            'id' => $id,
            'uid' => $uid,
            'level_id' => $uinfo['level'],
            'num' => $goods['num'],
            'addtime' => time(),
            'endtime' => time() + config('deal_timeout'),
            'add_id' => $add_id,
            'goods_id' => $goods['id'],
            'goods_count' => $goods['count'],
            'commission' => $commission,
            'user_balance' => $uinfo['balance'],
            'user_freeze_balance' => $uinfo['freeze_balance'],
            "today_dan" => $today_dan+1,
            "order_min_price" => $min,
            "order_max_price" => $max,
            "commission_type" => $commission_type,
            "commission_value" => $commission_value,
            "order_mode" => $order_mode,
        ];

        //查出用户推荐人 发放推荐人佣金
        if ($uinfo['parent_id'] > 0) {
            $pLevel = Db::name('xy_users')->where(['id' => $uinfo['parent_id']])->value('level');
            if ($pLevel) {
                $p_level_data = Db::name('xy_level')->where('level', $pLevel)->find();
                //一级开启享受佣金
                if($p_level_data['promotion_commisssion']){
                    $tj_bili = config('level1_commission') / 100;
                    if ($tj_bili > 0) {
                        $c_data['parent_commission'] = $c_data['commission'] * floatval($tj_bili);
                        $c_data['parent_uid'] = $uinfo['parent_id'];
                    }
                }
            }
        }
        $res1 = Db::name($this->table)
            ->insert($c_data);
        if ($inyectar) {
            Db::name('xy_inyectar')
                ->where('id', $inyectar['id'])
                ->update([
                    'in_time' => time(),
                    'in_amount' => $goods['num'],
                    'in_oid' => $id
                ]);
        }
        if ($res && $res1) {
            Db::commit();
            return ['code' => 0, 'info' => yuylangs('qd_ok'), 'oid' => $id, 'orderNum' => $orderNum];
        } else {
            Db::rollback();
            return ['code' => 1, 'info' => yuylangs('qd_sb')];
        }
    }


    /**
     * 创建单控订单
     * @param int $uid 用户编号
     * @param int $cid 商品组
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     * @throws \think\exception\PDOException
     */
    public function create_order_single_control($uid, $cid = 1)
    {
        $add_id = Db::name('xy_member_address')->where('uid', $uid)->value('id');//获取收款地址信息s
        if(config('master_cardnum') == 1){
            if (!$add_id) return ['code' => 1, 'info' => yuylangs('wszshdz')];
        }else{
            $add_id = 1;
        }

        $uinfo = Db::name('xy_users')->find($uid);
        $where1 = "1=1";
//        if(config('3_d_reward') == 1){
//            $where1 = ["level_id"=>$uinfo['level']];
//        }

        $count = Db::name('xy_convey')
            ->where("qkon = 1")
            ->where('addtime', 'between', [strtotime(date('Y-m-d')), time()])
            ->where('uid', $uinfo['id'])
            ->where($where1)
            ->where('status', 1)
            ->count('id');//统计当天完成交易的订单

        $single_control = Db::name('xy_single_control')->where('uid',$uid)->find();
        $order_num = $single_control['fixed_order_num'];
        $user_level = Db::table("xy_level")->where("level", $uinfo['level'])->find();
        //单控未设置固定单量，按照vip等级单量计算
        if($order_num < 1){
            $order_num = $user_level['order_num'];
        }

        if ($count >= $order_num) {
            return ['code' => 1, 'info' => yuylangs('hyddjycsbz'), 'endRal' => true];
        }

        if ($uinfo['deal_status'] != 2) return ['code' => 1, 'info' => yuylangs('qdyzz')];
        $level = $uinfo['level'] ? intval($uinfo['level']) : 0;
        $orderSetting = $this->get_user_order_setting($uid, $level);
        if ($uinfo['balance'] < $orderSetting['min_money']) {
            return [
                'code' => 1,
                'info' => sprintf(yuylangs('zhyebz'), ($orderSetting['min_money'] - $uinfo['balance']) . ""),
                'url' => url('index/ctrl/recharge')
            ];
        }

        $min = $user_level['grab_order_min_amount'];
        $max = $user_level['grab_order_max_amount'];

        list($orderNum) = $this->get_user_group_rule($uinfo['id'], $uinfo['group_id']);
        $inyectar = $this->get_inyectar($uid, $orderNum);
        //打针
        if ($inyectar) {
            $min = $user_level['grab_order_min_amount'] * $inyectar['scale'];
            $max = $user_level['grab_order_max_amount'] * $inyectar['scale'];
        }

        //单控会员等级卡单
        $order_mode = $this->order_mode_array['single_control_level'];
        $get_order_type = 1;//1=随机价格商品，2=固定金额商品
        //如果配置启用单数、当前做单数大于等于配置单数触发配置
        $tag = 1;//未触发单控
        if($single_control['enable_order_num'] >= 1 && $count + 1 == $single_control['enable_order_num']){
            //如果设置固定抢单金额或者抢单最小和最大比例
            if($single_control['fixed_order_amount'] >= 1 || ($single_control['order_min_bili'] > 0 && $single_control['order_max_bili']) > 0){
                $tag = 2;//触发单控
                //优先抢单比例,其次固定金额
                if($single_control['order_min_bili'] > 0 && $single_control['order_max_bili'] > 0){
                    //单控比例卡单
                    $order_mode = $this->order_mode_array['single_control_bili'];
                    $min = $single_control['order_min_bili'] / 100 * $uinfo['balance'];
                    $max = $single_control['order_max_bili'] / 100 * $uinfo['balance'];
                    if ($inyectar) {
                        $min = $uinfo['balance'] * $inyectar['scale'] * ($single_control['order_min_bili'] / 100);
                        $max = $uinfo['balance'] * $inyectar['scale'] * ($single_control['order_max_bili'] / 100);
                    }
                }else{
                    //单控固定金额卡单
                    $get_order_type = 2;//2=固定金额商品
                    $order_mode = $this->order_mode_array['single_control_fixed_amount'];
                    $max = $min = $single_control['fixed_order_amount'];
                    if ($inyectar) {
                        $max = $min = $single_control['fixed_order_amount'] * $inyectar['scale'];
                    }
                }
            }
        }
        if($get_order_type == 1){
            $goods = $this->rand_order($min, $max, $uid, $cid);
        }else{
            $goods = $this->fixed_amount_order($min,$uid,$cid);
        }
        if($goods['code'] == 1){
            return $goods;
        }

        $id = getSn('UB');
        Db::startTrans();
        $res = Db::name('xy_users')->where('id', $uid)->update(['deal_status' => 3, 'deal_time' => strtotime(date('Y-m-d')), 'deal_count' => Db::raw('deal_count+1')]);//将账户状态改为交易中
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;

        $today_dan = Db::table('xy_convey')
            ->where("uid = $uid")
            ->where('qkon',1)
            ->where("level_id",$uinfo['level'])
            ->where("addtime >= $beginToday && addtime <= $endToday")
            ->count();

        //触发单控
        if($tag == 2 && $single_control['fixed_commission_bili'] > 0){
            //如果单控设置固定佣金比例,按照固定佣金比例计算
            //佣金类型：0=百分比  1=固定值
            $commission_type = 0;
            $commission_value = $single_control['fixed_commission_bili'];
            $commission = $goods['num'] * $single_control['fixed_commission_bili'] / 100;  //单控固定佣金比例
        }else{
        //如果等级设置佣金比例按照等级佣金比例计算，否则按照固定佣金计算
            if($user_level['bili'] > 0){
                //佣金类型：0=百分比  1=固定值
                $commission_type = 0;
                $commission_value = $user_level['bili'];
                $commission = $goods['num'] * $user_level['bili'];
            }else{
                //佣金类型：0=百分比  1=固定值
                $commission_type = 1;
                $commission_value = $user_level['grab_order_fixed_commission'];
                $commission = $user_level['grab_order_fixed_commission'];
            }
        }
        //如果单控设置固定佣金比例,按照固定佣金比例计算
//        if($single_control['fixed_commission_bili'] > 0){
//            //佣金类型：0=百分比  1=固定值
//            $commission_type = 0;
//            $commission_value = $single_control['fixed_commission_bili'];
//            $commission = $goods['num'] * $single_control['fixed_commission_bili'] / 100;  //单控固定佣金比例
//        }else{
//            //如果等级设置佣金比例按照等级佣金比例计算，否则按照固定佣金计算
//            if($user_level['bili'] > 0){
//                //佣金类型：0=百分比  1=固定值
//                $commission_type = 0;
//                $commission_value = $user_level['bili'];
//                $commission = $goods['num'] * $user_level['bili'];
//            }else{
//                //佣金类型：0=百分比  1=固定值
//                $commission_type = 1;
//                $commission_value = $user_level['grab_order_fixed_commission'];
//                $commission = $user_level['grab_order_fixed_commission'];
//            }
//        }


        //插入佣金记录
        $c_data = [
            'id' => $id,
            'uid' => $uid,
            'level_id' => $uinfo['level'],
            'num' => $goods['num'],
            'addtime' => time(),
            'endtime' => time() + config('deal_timeout'),
            'add_id' => $add_id,
            'goods_id' => $goods['id'],
            'goods_count' => $goods['count'],
            'commission' => $commission,
            'user_balance' => $uinfo['balance'],
            'user_freeze_balance' => $uinfo['freeze_balance'],
            "today_dan" => $today_dan+1,
            "order_min_price" => $min,
            "order_max_price" => $max,
            "commission_type" => $commission_type,
            "commission_value" => $commission_value,
            "order_mode" => $order_mode,
        ];

        //查出用户推荐人 发放推荐人佣金
        if ($uinfo['parent_id'] > 0) {
            $pLevel = Db::name('xy_users')->where(['id' => $uinfo['parent_id']])->value('level');
            if ($pLevel) {
                $p_level_data = Db::name('xy_level')->where('level', $pLevel)->find();
                //一级开启享受佣金
                if($p_level_data['promotion_commisssion']){
                    $tj_bili = config('level1_commission') / 100;
                    if ($tj_bili > 0) {
                        $c_data['parent_commission'] = $c_data['commission'] * floatval($tj_bili);
                        $c_data['parent_uid'] = $uinfo['parent_id'];
                    }
                }
            }
        }
        $res1 = Db::name($this->table)
            ->insert($c_data);
        if ($inyectar) {
            Db::name('xy_inyectar')
                ->where('id', $inyectar['id'])
                ->update([
                    'in_time' => time(),
                    'in_amount' => $goods['num'],
                    'in_oid' => $id
                ]);
        }
        if ($res && $res1) {
            Db::commit();
            return ['code' => 0, 'info' => yuylangs('qd_ok'), 'oid' => $id, 'orderNum' => $orderNum];
        } else {
            Db::rollback();
            return ['code' => 1, 'info' => yuylangs('qd_sb')];
        }
    }


    
    public function dangqshuyu($uid,$group_id)
    {
        $lastOrder = Db::name('xy_convey')
            ->where('uid', $uid)
            ->where("qkon = 1")
            ->where('group_is_active', 1)
            ->where('group_id', $group_id)
            ->where("duorw > 0")
            ->group("rands")
            ->order('oid desc')
            ->count();
            
            
        
       
       $feifzu =     Db::name('xy_convey')
            ->where('uid', $uid)
            ->where("qkon = 1")
            ->where('group_is_active', 1)
            ->where('group_id', $group_id)
            ->where("duorw = 0")
            ->order('oid desc')
            ->count();  
       
       if($lastOrder == 0 && $feifzu == 0){
           $sumOrder = 1;
           return $sumOrder;
       }else{
           $sumOrder = $lastOrder + $feifzu;
       }
        
      $lastOrder1 = Db::name('xy_convey')
            ->where('uid', $uid)
            ->where("qkon = 1")
            ->where('group_is_active', 1)
            ->where('group_id', $group_id)
            ->where("duorw > 0")
            ->order('addtime desc')
            ->find();
    $lastOrder2 = Db::name('xy_convey')
            ->where('uid', $uid)
            ->where("qkon = 1")
            ->where('group_is_active', 1)
            ->where('group_id', $group_id)
            ->where("duorw > 0")
            ->where("group_rule_num",$lastOrder1["group_rule_num"])
            ->count();
            
      if($lastOrder2 >= $lastOrder1['duorw']){
          $sumOrder = $sumOrder + 1;
      }
            
    
     return $sumOrder;
    }
    
    
    
    
        
    /**
     * 创建杀猪组订单
     * @param int $uid 用户编号
     * @param int $cid 商品组
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     * @throws \think\exception\PDOException
     */
    public function create_order_group($uid, $cid = 1)
    {
        $add_id = Db::name('xy_member_address')->where('uid', $uid)->value('id');//获取收款地址信息s
        if(config('master_cardnum') == 1){
            if (!$add_id) return ['code' => 1, 'info' => yuylangs('wszshdz')];
         }else{
             $add_id = 1;
         }
        $uinfo = Db::name('xy_users')->find($uid);
        if ($uinfo['deal_status'] != 2) return ['code' => 1, 'info' => yuylangs('qdyzz')];
        $groupInfo = Db::name('xy_group')->where('id', $uinfo['group_id'])->find();
        //是否符合级别最低金额
        $user_level = Db::table("xy_level")->where("level", $uinfo['level'])->find();
        if ($uinfo['balance'] < $user_level['num_min']) {
            return [
                'code' => 1,
                'info' => sprintf(yuylangs('zhyebz'), ($groupInfo['money'] - $uinfo['balance']) . ""),
                'url' => url('index/ctrl/recharge')
            ];
        }
        
        list($orderNum, $groupRule) = $this->get_user_group_rule($uinfo['id'], $uinfo['group_id']);
        
        $inyectar = $this->get_inyectar($uid, $orderNum);
         
        $orderNum = $this->dangqshuyu($uinfo['id'], $uinfo['group_id']); //当前属于任务第几单  ///
     
//        if (empty($groupRule)) {
//            return ['code' => 1, 'info' => yuylangs('qd_sb')];
//        }
        
        $groupRule = Db::table("xy_group_rule")->where("group_id",$uinfo['group_id'])->where("order_num",$orderNum)->find();

        $groupInfo = Db::name('xy_group')->where('id', $uinfo['group_id'])->find();
        if($orderNum > $groupInfo['order_num']){
            return ['code' => 1, 'info' => yuylangs('hyddjycsbz'), 'endRal' => true];
        }

        $level = Db::name('xy_level')->where('level', $uinfo['level'])->find();
        $add_orders1 = 0;
        $time = time();
        $get_order_type = 1;//1=随机价格商品订单，2=固定金额订单
        list($day_d_count1, $groupRule1, $all_order_num1) = Convey::instance()->get_user_group_rule($uinfo['id'], $uinfo['group_id']);
        if(!empty($groupRule)){
            $add_orders1 = $groupRule["add_orders1"];  //是否多任务
            if($add_orders1){
                $add_orders1 = $add_orders1 + 1;
            }

            $orderListData = [];
            switch ($groupRule['order_type']){
                //订单模式：0-默认模式，1-叠加模式，2-固定补单模式
                case 0:
                    //订单价格计算类型：0-比例，1-固定金额
                    if($groupRule['order_price_type'] == 0){
                        $price_arr = explode('-',$groupRule['order_price']);
                        //打针
                        if ($inyectar) {
                            $min = $uinfo['balance'] * $inyectar['scale'] * $price_arr[0];
                            $max = $uinfo['balance'] * $inyectar['scale'] * $price_arr[1];
                        }else{
                            $min = $uinfo['balance'] * $price_arr[0];
                            $max = $uinfo['balance'] * $price_arr[1];
                        }
                        //方案组比例卡单
                        $order_mode = $this->order_mode_array['group_bili'];
                    }else{
                        //打针
                        if ($inyectar) {
                            $min = $max = $groupRule['order_price'] * $inyectar['scale'];
                        }else{
                            $min = $max = $groupRule['order_price'];
                        }
                        $get_order_type = 2;//1=随机价格商品订单，2=固定金额订单
                        //方案组固定金额卡单
                        $order_mode = $this->order_mode_array['group_fixed_amount'];
                    }
                    break;
                case 1:
                    //方案组叠加模式卡单
                    $order_mode = $this->order_mode_array['superposition'];
                    $keys = Db::table("xy_convey")->where(['uid'=>$uinfo['id'],'group_id'=>$uinfo['group_id'],'group_is_active'=>1,'group_rule_num'=>$orderNum])->count();
                    $oP = explode('|', $groupRule['order_price']);
                    if(empty($oP[$keys])){
                        $bl = $oP[0];
                    }else{
                        $bl = $oP[$keys];
                    }
                    //打针
                    if ($inyectar) {
                        $min = $max = $uinfo['balance'] * $inyectar['scale'] * $bl;
                    }else{
                        $min = $max = $uinfo['balance'] * $bl;
                    }
                    break;
                case 2:
                    $get_order_type = 2;//1=随机价格商品订单，2=固定金额订单
                    //方案组固定补单模式卡单
                    $order_mode = $this->order_mode_array['fixed_replenishment_order'];
                    //打针
                    if ($inyectar) {
                        $min = $max = $uinfo['balance'] * $inyectar['scale'] + $groupRule['order_price'];
                    }else{
                        $min = $max = $uinfo['balance'] + $groupRule['order_price'];
                    }
                    break;
                default :
                    return ['code' => 1, 'info' => translate('Mode error, please contact the administrator')];
            }
        }else{
            //打针
            if ($inyectar) {
                $min = $level['grab_order_min_amount'] * $inyectar['scale'];
                $max = $level['grab_order_max_amount'] * $inyectar['scale'];
            }else{
                $min = $level['grab_order_min_amount'];
                $max = $level['grab_order_max_amount'];
            }
            //等级卡单
            $order_mode = $this->order_mode_array['level_amount'];
        }

        if($get_order_type == 1){
            $goods = $this->rand_order($min, $max, $uid, $cid);
        }else{
            $goods = $this->fixed_amount_order($min,$uid,$cid);
        }

       if($goods['code'] == 1){
           return $goods;
       }


        //计算佣金
        if(!empty($groupRule)){
            if ($groupRule['commission_type'] == 1) {
                //固定佣金
                $commission_type = 1;
                $commission_value = $commission = $groupRule['commission_value'];
            } else {
                //百分比佣金
                $commission_type = 0;
                $commission_value = $groupRule['commission_value'] * 100;
                $commission = $goods['num'] * $groupRule['commission_value'];
            }
            //$commission = $this->get_commission($goods['num'], $groupRule);
        }else{
            //等级设置佣金比例按照佣金比例计算，否则按照固定金额计算
            if($level['bili'] > 0){
                //0=百分比  1=固定值
                $commission_type = 0;
                $commission_value = $level['bili'] * 100;
                $commission = $goods['num'] * $level['bili'];
            }else{
                $commission_type = 1;
                $commission_value = $commission = $level['grab_order_fixed_commission'] ?: 0;
            }
        }

            $ids = [getSn('UB')];
            $c_data = [
                'id' => $ids[0],
                'uid' => $uid,
                'level_id' => $uinfo['level'],
                'num' => $goods['num'],
                'addtime' => $time,
                'endtime' => $time + config('deal_timeout'),
                'add_id' => $add_id,
                'goods_id' => $goods['id'],
                'goods_count' => $goods['count'],
                'commission' => $commission,  //交易佣金按照会员等级
                'group_id' => $uinfo['group_id'],
                'group_rule_num' => $orderNum,
                'group_zero' => $groupRule['freeze_principal'],
                'user_balance' => $uinfo['balance'],
                'user_freeze_balance' => $uinfo['freeze_balance'],
                "group_count" =>  $all_order_num1, //折叠数量
                "duorw" => $add_orders1,
                "rwdans"  =>  $lastOrder1 = Db::name('xy_convey')->where('uid', $uid)->where("qkon = 1")->where('group_is_active', 1)->where('group_id', $uinfo['group_id'])->count(),
                "order_min_price" => $min,
                "order_max_price" => $max,
                "commission_type" => $commission_type,
                "commission_value" => $commission_value,
                "order_mode" => $order_mode,
            ];
            
             //多任务
            if($add_orders1){
                 //分组随机数
                $fenzhRes = Db::table("xy_convey")->where(['uid' => $uid,"qkon"=>1,'group_id' => $uinfo['group_id'],"group_completedornot"=>1])->find();
                if($fenzhRes){
                    $c_data["rands"] = $fenzhRes["rands"];
                    $c_data["zhuass"] = $fenzhRes["zhuass"] + 1;
                }else{
                    $c_data["rands"] = rand(1,99999).time();
                    $c_data["zhuass"] = 1;
                }   
            }
        
        
        $other_data = [];
        //查出用户推荐人 发放推荐人佣金
        if ($uinfo['parent_id'] > 0) {
            $pLevel = Db::name('xy_users')->where(['id' => $uinfo['parent_id']])->value('level');
            if ($pLevel) {
                $plevel_data = Db::name('xy_level')->where('level', $pLevel)->find();
                if($plevel_data['promotion_commisssion'] == 1){
                    $tj_bili = config('level1_commission') / 100;
                    if ($tj_bili) {
                        if (isset($c_data)) $c_data['parent_commission'] = floatval($c_data['commission']) * floatval($tj_bili);
                        $other_data['parent_uid'] = $uinfo['parent_id'];
                    }
                }
            }
        }
        //事务处理
        Db::startTrans();
        //将账户状态改为交易中
        $res = Db::name('xy_users')->where('id', $uid)
            ->update(['deal_status' => 3,
                'deal_time' => strtotime(date('Y-m-d')),
                'deal_count' => Db::raw('deal_count+1')
            ]);
        //插入订单记录
        $res1 = Db::name($this->table)->insert(array_merge($c_data, $other_data));
        
        if ($inyectar) {
            Db::name('xy_inyectar')
                ->where('id', $inyectar['id'])
                ->update([
                    'in_time' => time(),
                    'in_amount' => $goods['num'],
                    'in_oid' => $ids[0]
                ]);
        }
        if ($res && $res1) {
            Db::commit();
            return ['code' => 0, 'info' => yuylangs('qd_ok'), 'oid' => $ids[0], 'orderNum' => $orderNum];
        } else {
            Db::rollback();
            return ['code' => 1, 'info' => yuylangs('qd_sb')];
        }
    }

    /**
     * 旧的分组下单
     * @param $uid
     * @param $cid
     */
    public function old_create_order_group($uid, $cid = 1)
    {

        $add_id = Db::name('xy_member_address')->where('uid', $uid)->value('id');//获取收款地址信息s
        if(config('master_cardnum') == 1){
            if (!$add_id) return ['code' => 1, 'info' => yuylangs('wszshdz')];
        }else{
            $add_id = 1;
        }
        $uinfo = Db::name('xy_users')->find($uid);
        if ($uinfo['deal_status'] != 2) return ['code' => 1, 'info' => yuylangs('qdyzz')];
        $groupInfo = Db::name('xy_group')->where('id', $uinfo['group_id'])->find();
        //是否符合级别最低金额
        $user_level = Db::table("xy_level")->where("level", $uinfo['level'])->find();
        if ($uinfo['balance'] < $user_level['num_min']) {
            return [
                'code' => 1,
                'info' => sprintf(yuylangs('zhyebz'), ($groupInfo['money'] - $uinfo['balance']) . ""),
                'url' => url('index/ctrl/recharge')
            ];
        }
        list($orderNum, $groupRule) = $this->get_user_group_rule($uinfo['id'], $uinfo['group_id']);

        $inyectar = $this->get_inyectar($uid, $orderNum);

        $orderNum = $this->dangqshuyu($uinfo['id'], $uinfo['group_id']); //当前属于任务第几单  ///

//        if (empty($groupRule)) {
//            return ['code' => 1, 'info' => yuylangs('qd_sb')];
//        }

        $groupRule = Db::table("xy_group_rule")->where("group_id",$uinfo['group_id'])->where("order_num",$orderNum)->find();


        $add_orders1 = $groupRule["add_orders1"];  //是否多任务
        if($add_orders1){
            $add_orders1 = $add_orders1 + 1;
        }

        $time = time();
        $orderListData = [];
        list($day_d_count1, $groupRule1, $all_order_num1) = Convey::instance()->get_user_group_rule($uinfo['id'], $uinfo['group_id']);

        if ($groupRule['order_type'] == 1) {
            $keys = Db::table("xy_convey")->where(['uid'=>$uinfo['id'],'group_id'=>$uinfo['group_id'],'group_is_active'=>1,'group_rule_num'=>$orderNum])->count();

            $oP = explode('|', $groupRule['order_price']);

            if(empty($oP[$keys])){
                $bl = $oP[0];
            }else{
                $bl = $oP[$keys];
            }
            $min = $max = $uinfo['balance'] * $bl;
            //打针
            if ($inyectar) {
                $min = $max = $uinfo['balance'] * $bl * $inyectar['scale'];
            }
            $goods = $this->rand_order($min, $max, $uid, $cid);
        }else{
            $min = $uinfo['balance'] * config('deal_min_num') / 100;
            $max = $uinfo['balance'] * config('deal_max_num') / 100;
            //打针
            if ($inyectar) {
                $min = $max = $uinfo['balance'] * $inyectar['scale'];
            }
            $goods = $this->rand_order($min, $max, $uid, $cid);

        }




        if($goods['code'] == 1){
            return $goods;
        }

        //计算佣金
        $commission = $this->get_commission($goods['num'], $groupRule);
        $ids = [getSn('UB')];
        $c_data = [
            'id' => $ids[0],
            'uid' => $uid,
            'level_id' => $uinfo['level'],
            'num' => $goods['num'],
            'addtime' => $time,
            'endtime' => $time + config('deal_timeout'),
            'add_id' => $add_id,
            'goods_id' => $goods['id'],
            'goods_count' => $goods['count'],
            'commission' => $commission,  //交易佣金按照会员等级
            'group_id' => $uinfo['group_id'],
            'group_rule_num' => $orderNum,
            'user_balance' => $uinfo['balance'],
            'user_freeze_balance' => $uinfo['freeze_balance'],
            "group_count" =>  $all_order_num1, //折叠数量
            "duorw" => $add_orders1,
            "rwdans"  =>  $lastOrder1 = Db::name('xy_convey')->where('uid', $uid)->where("qkon = 1")->where('group_is_active', 1)->where('group_id', $uinfo['group_id'])->count()
        ];

        //多任务
        if($add_orders1){
            //分组随机数
            $fenzhRes = Db::table("xy_convey")->where(['uid' => $uid,"qkon"=>1,'group_id' => $uinfo['group_id'],"group_completedornot"=>1])->find();
            if($fenzhRes){
                $c_data["rands"] = $fenzhRes["rands"];
                $c_data["zhuass"] = $fenzhRes["zhuass"] + 1;
            }else{
                $c_data["rands"] = rand(1,99999).time();
                $c_data["zhuass"] = 1;
            }
        }


        $other_data = [];
        //查出用户推荐人 发放推荐人佣金
        if ($uinfo['parent_id'] > 0) {
            $pLevel = Db::name('xy_users')->where(['id' => $uinfo['parent_id']])->value('level');
            if ($pLevel) {
                $plevel_data = Db::name('xy_level')->where('level', $pLevel)->find();
                if($plevel_data['promotion_commisssion'] == 1){
                    $tj_bili = config('level1_commission') / 100;
                    if ($tj_bili) {
                        if (isset($c_data)) $c_data['parent_commission'] = floatval($c_data['commission']) * floatval($tj_bili);
                        $other_data['parent_uid'] = $uinfo['parent_id'];
                    }
                }
            }
        }
        //事务处理
        Db::startTrans();
        //将账户状态改为交易中
        $res = Db::name('xy_users')->where('id', $uid)
            ->update(['deal_status' => 3,
                'deal_time' => strtotime(date('Y-m-d')),
                'deal_count' => Db::raw('deal_count+1')
            ]);
        //插入订单记录
        $res1 = Db::name($this->table)->insert(array_merge($c_data, $other_data));

        if ($inyectar) {
            Db::name('xy_inyectar')
                ->where('id', $inyectar['id'])
                ->update([
                    'in_time' => time(),
                    'in_amount' => $goods['num'],
                    'in_oid' => $ids[0]
                ]);
        }
        if ($res && $res1) {
            Db::commit();
            return ['code' => 0, 'info' => yuylangs('qd_ok'), 'oid' => $ids, 'orderNum' => $orderNum];
        } else {
            Db::rollback();
            return ['code' => 1, 'info' => yuylangs('qd_sb')];
        }
    }

    /**
     * 获取用户可交易情况
     * @param $uid int 用户编号
     * @param $level_id int 级别编号
     * @return array [总订单量，佣金比例，最低金额，提现订单限制]
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function get_user_order_setting($uid, $level_id)
    {
        $setting = Db::name('xy_users_setting')
            ->where('uid', $uid)
            ->where('date', date('Y-m-d'))
            ->find();
        if ($setting) {
            return [
                'order_num' => $setting['order_num'],
                'bili' => $setting['bili'],
                'min_money' => $setting['min_money'],
                'min_deposit_order' => $setting['min_deposit_order'],
            ];
        }
        $level = Db::name('xy_level')->where('level', $level_id)->find();
        return [
            'order_num' => $level['order_num'],
            'bili' => $level['bili'],
            'min_money' => $level['num_min'],
            'min_deposit_order' => $level['tixian_nim_order'],
        ];
    }

    /**
     * 获取用户当前做单情况
     * @param $uid int 用户编号
     * @param $group_id int 叠加组
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function get_user_group_rule($uid, $group_id)
    {
        if (!$group_id) {
            //普通组
            $uinfo = Db::name('xy_users')->find($uid);
            $uinfo['level'] = $uinfo['level'] > 0 ? $uinfo['level'] : 0;
            $orderNum = Db::name('xy_convey')
                ->where([
                    ['uid', '=', $uid],
                    ["qkon",'=','1'],
                    ['level_id', '=', $uinfo['level']],
                    ['addtime', 'between', strtotime(date('Y-m-d')) . ',' . time()],
                ])
                ->where('status', 'in', [0, 1, 3, 5])
                ->count('id');
            $all_order_num = Db::name('xy_level')->where('level', $uinfo['level'])->value('order_num');
            return [$orderNum, 0, $all_order_num];
        }
        $groupInfo = Db::name('xy_group')->where('id', $group_id)->find();
        //总单数
        $all_order_num = intval($groupInfo['order_num']);
        //判断当前第几单
        $orderNum = 1;
        $lastOrder = Db::name('xy_convey')
            ->where('uid', $uid)
            ->where("qkon = 1")
            ->where('group_is_active', 1)
            ->where('group_id', $group_id)
            ->order('oid desc')
            ->find();
            
         if (!empty($lastOrder)) {
            $orderNum = $lastOrder['group_rule_num'] + 1;
        }   
            
       
   
        $groupRule = Db::name('xy_group_rule')
            ->where('group_id', $group_id)
            ->where('order_num', $orderNum)
            ->find();
        if (empty($groupRule)) {
            //如果没有 就从第一单开始
            $orderNum = 1;
            $groupRule = Db::name('xy_group_rule')
                ->where('group_id', $group_id)
                ->where('order_num', $orderNum)
                ->find();
        } else {
            //叠加 用户已经做了的单数
            if ($orderNum > 1) {
                $add_num = Db::name('xy_group_rule')
                    ->where('group_id', $group_id)
                    ->where('order_num', '<', $orderNum)
                    ->sum('add_orders');
                $all_order_num += intval($add_num);
            }
        }
        
        return [$orderNum, $groupRule, $all_order_num];
    }
    
    
    
    
     /**
     * 获取用户当前做单情况2
     * @param $uid int 用户编号
     * @param $group_id int 叠加组
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function get_user_group_rule2($uid, $group_id)
    {
        if (!$group_id) {
            //普通组
            $uinfo = Db::name('xy_users')->find($uid);
            $uinfo['level'] = $uinfo['level'] > 0 ? $uinfo['level'] : 0;
            $orderNum = Db::name('xy_convey')
                ->where([
                    ['uid', '=', $uid],
                    ["qkon",'=','1'],
                    ['level_id', '=', $uinfo['level']],
                    ['addtime', 'between', strtotime(date('Y-m-d')) . ',' . time()],
                ])
                ->where('status', 'in', [0, 1, 3, 5])
                ->count('id');
            $all_order_num = Db::name('xy_level')->where('level', $uinfo['level'])->value('order_num');
            return [$orderNum, 0, $all_order_num];
        }
        $groupInfo = Db::name('xy_group')->where('id', $group_id)->find();
        //总单数
        $all_order_num = intval($groupInfo['order_num']);
        //判断当前第几单
        $orderNum = 1;
        $lastOrder = Db::name('xy_convey')
            ->where('uid', $uid)
            ->where("qkon = 1")
            ->where('group_is_active', 1)
            ->where('group_id', $group_id)
            ->order('oid desc')
            ->find();
        if (!empty($lastOrder)) {
            $orderNum = $lastOrder['group_rule_num'] + 1;
        }
        $groupRule = Db::name('xy_group_rule')
            ->where('group_id', $group_id)
            ->where('order_num', $orderNum)
            ->find();
        if (empty($groupRule)) {
            //如果没有 就从第一单开始
            $orderNum = 1;
            $groupRule = Db::name('xy_group_rule')
                ->where('group_id', $group_id)
                ->where('order_num', $orderNum)
                ->find();
        } else {
            //叠加 用户已经做了的单数
         //   if ($orderNum > 1) {
                $add_num = Db::name('xy_group_rule')
                    ->where('group_id', $group_id)
                 
                    ->sum('add_orders1');
                $all_order_num += intval($add_num);
           // }
        }
        
        return [$orderNum, $groupRule, $all_order_num];
    }
    

    /**
     * 获取打针比例
     * @param $uid int 用户编号
     * @param $order_num int 当前第几单
     * @return array|null|\PDOStatement|string|Model
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    private function get_inyectar($uid, $order_num)
    {
        if ($order_num > 1) $order_num = $order_num + 1;
        //优先执行 指定单
        $in = Db::name('xy_inyectar')
            ->where('uid', $uid)
            ->where('order_num', $order_num)
            ->where('date', date('Y-m-d'))
            ->where('in_time', 0)
            ->find();
        if (!$in) {
            //下一单
            $in = Db::name('xy_inyectar')
                ->where('uid', $uid)
                ->where('order_num', 0)
                ->where('date', date('Y-m-d'))
                ->where('in_time', 0)
                ->find();
        }
        return $in;
    }

    /**
     * 计算佣金
     * */
    private function get_commission($price, $groupRule)
    {
        if ($groupRule['commission_type'] == 1) {
            //固定佣金
            $commission = $groupRule['commission_value'];
        } else {
            //百分比佣金
            $commission = $price * $groupRule['commission_value'];
//            $commission = $price * ($groupRule['commission_value'] / 100);
        }
        return $commission;
    }

    /**
     * $num 固定价格
     * 固定金额订单商品
     */
    private function fixed_amount_order($num,$uid,$cid)
    {
        $user_goods_ids = get_user_order_goods_ids($uid);
        $goods = Db::name('xy_goods_list')
            ->orderRaw('rand()')
            ->where('goods_price', $num)
            ->where('cid', '=', $cid)
            ->whereNotIn('id',$user_goods_ids)
            ->find();
        if (!$goods) {
            //return ['code' => 1, 'info' => yuylangs('qdsbkcbz') . '--' . $num];
            $rand_goods = Db::name('xy_goods_list')
                ->where('cid', '=', $cid)
                ->orderRaw('rand()')
                ->find();

            $gid = Db::name('xy_goods_list')->insertGetId([
                'shop_name'=>$rand_goods['shop_name'],
                'goods_name'=>$rand_goods['goods_name'],
                'goods_info'=>$rand_goods['goods_info'],
                'goods_price'=>$num,
                'goods_pic'=>$rand_goods['goods_pic'],
                'addtime'=>time(),
                'status'=>1,
                'cid'=>$rand_goods['cid']
            ]);
            if(empty($gid)){
                return ['code'=>1,'info'=>lang('系统错误')];
            }
            $goods = Db::name('xy_goods_list')
                ->where('id',$gid)
                ->find();
        }
        $count = 1;
        return ['count' => $count, 'code'=>0, 'id' => $goods['id'], 'num' => $num, 'cid' => $goods['cid']];
    }

    /**
     * 随机生成订单商品
     */
    private function rand_order($min, $max, $uid, $cid)
    {
        //生成总价格
        $num = mt_rand($min, $max);//随机交易额
        //单价商品
        $user_goods_ids = get_user_order_goods_ids($uid);
        $goods = Db::name('xy_goods_list')
            ->orderRaw('rand()')
            ->where('goods_price', 'between', [0, $num])
            ->where('cid', '=', $cid)
            ->whereNotIn('id',$user_goods_ids)
            ->find();
           
        if (!$goods) {
            $rand_goods = Db::name('xy_goods_list')
                ->where('cid', '=', $cid)
                ->orderRaw('rand()')
                ->find();

            $gid = Db::name('xy_goods_list')->insertGetId([
                'shop_name'=>$rand_goods['shop_name'],
                'goods_name'=>$rand_goods['goods_name'],
                'goods_info'=>$rand_goods['goods_info'],
                'goods_price'=>$num,
                'goods_pic'=>$rand_goods['goods_pic'],
                'addtime'=>time(),
                'status'=>1,
                'cid'=>$rand_goods['cid']
            ]);
            if(empty($gid)){
                return ['code'=>1,'info'=>lang('系统错误')];
            }
            $goods = Db::name('xy_goods_list')
                ->where('id',$gid)
                ->find();
            return ['count' => 1, 'code'=>0, 'id' => $goods['id'], 'num' => $num, 'cid' => $goods['cid']];
            //return ['code' => 1, 'info' => yuylangs('qdsbkcbz') . '--' . $num];
        }
        //数量 = 总价/单价
        $count = round($num/$goods['goods_price']);
        //数量 * 单价 != 总价
        if($count * $goods['goods_price'] != $num){
            //如果数量 * 单价 > 总价
            if($count * $goods['goods_price'] > $num){
                //计算差值，并加到总价上
                $zhis = $count * $goods['goods_price'] - $num;
                $num = $num + $zhis;
            }else{
            //如果数量 * 单价 < 总价
                //计算差值,在总价上减去
                 $zhis = $num - $count * $goods['goods_price'];
                $num = $num - $zhis;
            }
        }
        return ['count' => $count, 'code'=>0, 'id' => $goods['id'], 'num' => $num, 'cid' => $goods['cid']];
    }

    /**
     * 处理订单
     *
     * @param string $oid 订单号
     * @param int $status 操作      1会员确认付款 2会员取消订单 3后台强制付款 4后台强制取消
     * @param int $uid 用户ID    传参则进行用户判断
     * @param int $uid 收货地址
     * @return array
     */
    public function do_order($oid, $status, $uid = '', $add_id = '', $pingfen = 0, $pinglun = '')
    {
        $info = Db::name('xy_convey')->find($oid);
        if (!$info) return ['code' => 1, 'info' => yuylangs('order_sn_none')];
        if ($uid && $info['uid'] != $uid) return ['code' => 1, 'info' => yuylangs('cscw')];
        if (!in_array($info['status'], [0, 5])) return ['code' => 1, 'info' => yuylangs('ddycl')];

//        if(time() > $info['endtime'] ){
//            return ['code' => 1, 'info' => yuylangs('ddycl')];
//        }
        $user = Db::name('xy_users')->where('id', $info['uid'])->find();

        $tmp = [
            //'endtime' => time() + config('deal_feedze'),
            'status' => in_array($status, [2, 4]) ? $status : 5,
         //  'status' => $status,
            'is_pay' => in_array($status, [2, 4]) ? 0 : 1,
            'pay_time' => time(),
            'pingfen'   => $pingfen,
            'pinglun'   => $pinglun
        ];
        $add_id ? $tmp['add_id'] = $add_id : '';  
        Db::startTrans();
        $res = Db::name('xy_convey')->where('id', $oid)->update($tmp);
        if (in_array($status, [1, 3])) {
            //TODO 判断余额是否足够
            
            if ($user['balance'] < $info['num']) {
                //把幸运订单降级为普通订单
                
                Db::rollback();
                
                if($info['group_zero'] == 1){
                    Db::table("xy_convey")->where('id', $oid)->update(["group_zero"=>0]);
                }
                return [
                    'code' => 1,
                    'info' => sprintf(yuylangs('zhyebz'), ($info['num'] - $user['balance']) . ""),
                    'url' => url('index/ctrl/recharge')
                ];
            }
            //是否为多单模式
            $isGroup = false;
            $isMultipleOrder = false;
            if ($info['group_id'] > 0) {
                $isGroup = true;
                $o_g_ids = Db::name('xy_convey')
                    ->where('uid', $info['uid'])
                    ->where('group_is_active', 1)
                    ->where('group_id', $info['group_id'])
                    ->where('group_rule_num', $info['group_rule_num'])
                    ->column('id');
             //   return $o_g_ids;
                if (count($o_g_ids) > 1) {
                    $isMultipleOrder = true;
                }
                
                
                //校验是否是最后一单
                // $groupInfo = Db::name('xy_group')->where('id', $info['group_id'])->find();
                if($info['rands']){
                     $order_num1 = Db::table("xy_convey")->where('qkon',1)->where("rands",$info['rands'])->count();
                     if($order_num1 >= $info["duorw"]){
                         Db::table("xy_convey")->where(['rands'=>$info['rands']])->update(["group_completedornot"=>2]);
                     }    
                }
                
                if(!$info['duorw']){
                    Db::table("xy_convey")->where(['rands'=>$info['rands']])->update(["group_completedornot"=>2]);
                }
                
              
            }
            //付款
            if (!$info['is_pay']) {
                try {
                
                    $res1 = Db::name('xy_users')
                        ->where('id', $info['uid'])
                        ->dec('balance', $info['num'])
                        ->inc('freeze_balance', $info['num'] + $info['commission']) //冻结商品金额 + 佣金
                        ->update([
                            'deal_status' => 1,
                            'status' => 1
                        ]);
                    //商品支出
                    $res2 = Db::name('xy_balance_log')->insert([
                        'uid' => $info['uid'],
                        'sid' => $info['uid'],
                        'oid' => $oid,
                        'num' => $info['num'],
                        'type' => 2,
                        'status' => 2,
                        'addtime' => time(),
                        "balance" => $user['balance']
                    ]);
                    //交易佣金
                    $res8 = Db::name('xy_balance_log')->insert([
                        'uid' => $info['uid'],
                        'sid' => $info['uid'],
                        'oid' => $oid,
                        'num' => $info['commission'],
                        'type' => 3,
                        'status' => 1,
                        'addtime' => time(),
                        "balance" => $user['balance']
                    ]);
                    //商品收入
                    $res2 = Db::name('xy_balance_log')->insert([
                        'uid' => $info['uid'],
                        'sid' => $info['uid'],
                        'oid' => $oid,
                        'num' => $info['num'],
                        'type' => 2,
                        'status' => 1,
                        'addtime' => time(),
                        "balance" => $user['balance']
                    ]);
                    if ($res && $res1 && $res2) {
                         //提交事物
                           Db::commit();
                    } else {
                        Db::rollback();
                        return ['code' => 1, 'info' => yuylangs('czsb')];
                    }
                } catch (Exception $th) {
                    Db::rollback();
                    return ['code' => 1, 'info' => yuylangs('czsb')];
                }
            }
            //系统通知
            $isAllOk = true;
            if ($status == 3) {
                Db::name('xy_message')->insert(['uid' => $info['uid'], 'type' => 2, 'title' => yuylangs('sys_msg'), 'content' => $oid . ',' . yuylangs('dd_pay_system'), 'addtime' => time()]);
            }
            
            
            
          
            if (!$isMultipleOrder) {
                $c_status = Db::name('xy_convey')->where('id', $oid)->value('c_status');
                //判断是否已返还佣金
                if ($c_status === 0) $this->deal_reward($info['uid'], $oid, $info['num'], $info['commission']);
            } else {
                
                 
                  
                //校验他是不是最后一单
                 $order_num1 = Db::table("xy_convey")->where("rands",$info['rands'])->where('qkon',1)->count();
                 if($info['rands']){
                    //  $this->deal_reward($info['uid'], $oid, $info['num'], $info['commission']);  //测试给他 使用的
                   //提交事物
                         
                              Db::commit();   
                      if($order_num1 >= $info["duorw"]){
                         $oList = Db::table("xy_convey")->where("rands",$info['rands'])->where('qkon',1)->select(); 
                         foreach($oList as $val){
                              if (  $val['c_status'] == 0) {   //注释的
                                  $this->deal_reward($val['uid'], $val['id'], $val['num'], $val['commission']);
                               } 
                         } 
                       
                      
                     }   
                 }else{
                      $this->deal_reward($info['uid'], $oid, $info['num'], $info['commission']);  //测试给他 使用的
                 }
                  
            }
            
             // //多单模式      ------------卡
              $xy_group_rule1 = Db::table("xy_group_rule")->where("group_id",$user['group_id'])->count();
            $xy_group_rule2 = Db::table("xy_group_rule")->where("group_id",$user['group_id'])->sum("add_orders1");
           $all_order_num1 = $xy_group_rule1 + $xy_group_rule2;
             $zuodanshu = Db::table("xy_convey")->where(["group_is_active" => 1,'group_id'=>$user['group_id'],"qkon"=>1,'uid'=>$user['id']])->count();

            //更新等级
            if($info['group_id']){
                $groupRuleInfo = Db::name('xy_group_rule')
                    ->where('group_id', $info['group_id'])
                    ->where('order_num', $info['group_rule_num'])
                    ->find();
                if ($groupRuleInfo) {
                    if ($groupRuleInfo['trigger_level'] > $user['level']) {
                        Db::name('xy_users')->where('id', $info['uid'])->update(['level'=>$groupRuleInfo['trigger_level']]);
                    }
                }
            }
            //注释
              if ($zuodanshu >= $all_order_num1) {
//                    Db::name('xy_convey')
//                        ->where('uid', $user['id'])
//                        ->where('group_id', $user['group_id'])
//                        ->update([
//                            'group_is_active' => 0
//                        ]);
                }  
            
            return ['code' => 0, 'info' => yuylangs('czcg')];
            
           
        } //
        elseif (in_array($status, [2, 4])) {
            
            //判断幸运订单是否可以取消
            if($status == 2 && $info['group_zero'] == 0){
                return [
                    'code' => 1,
                    'info' => yuylangs('czsb'),
                    'url' => url('index/ctrl/recharge')
                ];
            }
            
            $res1 = Db::name('xy_users')->where('id', $info['uid'])
                ->update([
                    'deal_status' => 1,
                ]);
            if ($status == 4) Db::name('xy_message')->insert(['uid' => $info['uid'], 'type' => 2, 'title' => yuylangs('sys_msg'), 'content' => $oid . ',' . yuylangs('dd_system_clean'), 'addtime' => time()]);
            //系统通知
            if ($res && $res1 !== false) {
                Db::commit();
                return ['code' => 0, 'info' => yuylangs('czcg')];
            } else {
                Db::rollback();
                return ['code' => 1, 'info' => yuylangs('czsb'), 'data' => $res1];
            }
        }
         Db::rollback();
        
    }

    //计算代数佣金比例
    private function get_tj_bili($tj_bili, $lv)
    {
        $tj_bili = explode("/", $tj_bili);
        $tj_bili[0] = isset($tj_bili[0]) ? floatval($tj_bili[0]) : 0;
        $tj_bili[1] = isset($tj_bili[1]) ? floatval($tj_bili[1]) : 0;
        $tj_bili[2] = isset($tj_bili[2]) ? floatval($tj_bili[2]) : 0;
        return isset($tj_bili[$lv - 1]) ? $tj_bili[$lv - 1] : 0;
    }

    /**
     * 交易返佣
     *
     * @return void
     */
    public function deal_reward($uid, $oid, $num, $cnum)
    {
        Db::name('xy_users')->where('id', $uid)->setInc('balance', $num + $cnum);
        Db::name('xy_users')->where('id', $uid)->setDec('freeze_balance', $num + $cnum);
        //Db::name('xy_balance_log')->where('oid', $oid)->update(['status' => 1]);
        //将订单状态改为已返回佣金
        
        Db::name('xy_convey')
            ->where('id', $oid)
            ->update(['c_status' => 1, 'status' => 1]);
            
            
        Db::name('xy_reward_log')->insert(['oid' => $oid, 'uid' => $uid, 'num' => $num, 'addtime' => time(), 'type' => 2, 'status' => 2]);
            
            
        //记录充值返佣订单
        /************* 发放交易奖励 *********/
        //之后下单人级别>0 才发放层级奖励
        $level = Db::name('xy_users')->where('id', $uid)->value('level');
        if ($level > 0) {
            $userList = model('admin/Users')->parent_user($uid, 3);
        } else $userList = [];
     
        //发放佣金
        if ($userList) { 
            foreach ($userList as $v) {
                if ($v['level'] == 0) continue;
                $tj_bili = Db::name('xy_level')->where('level', $v['level'])->value('tj_bili');
                $price = $this->get_tj_bili($tj_bili, intval($v['lv'])) * $cnum;
                if ($v['status'] === 1) {
                    Db::name('xy_reward_log')
                        ->insert([
                            'uid' => $v['id'],
                            'sid' => $v['pid'],
                            'oid' => $oid,
                            'num' => $price,
                            'lv' => $v['lv'],
                            'type' => 2,
                            'status' => 2,
                            'addtime' => time(),
                        ]);
                    $res = Db::name('xy_users')
                        ->where('id', $v['id'])
                        ->where('status', 1)
                        ->setInc('balance', $price);
                    //下级佣金
                    
                     $balance = Db::name('xy_users')
                        ->where('id', $v['id'])->value("balance");
                    
                    $res2 = Db::name('xy_balance_log')->insert([
                        'uid' => $v['id'],
                        'sid' => $uid,
                        'oid' => $oid,
                        'num' => $price,
                        'type' => 6,
                        'status' => 1,
                        'addtime' => time(),
                        "balance" => $balance
                    ]);
                }
            }
        }
        /************* 发放交易奖励 *********/
    }
} 