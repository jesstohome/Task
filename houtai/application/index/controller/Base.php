<?php

namespace app\index\controller;

use app\admin\model\Convey;
use library\Controller;
use think\App;
use think\facade\Cookie;
use think\facade\Request;
use think\Db;


/**
 * 验证登录控制器
 */
class Base extends Controller
{
    protected $rule = ['__token__' => 'token'];
    protected $msg = ['__token__' => '无效token！'];
    protected $_uid;
    
    //用户id
    protected $usder_id = '';
    
    //token到期时间
    protected $tokenTime = "604800";  //7天

    //vip是否到期
    protected $vip_expire = false;

    //当前语言
    protected $language = "";
     
    
    protected $authentication = [];
    

    function __construct(App $app)
    {
        parent::__construct($app);
        
       // if (config('shop_status') == 0) exit();
        
        $langs = Db::table("xy_language")->where(['moryuy'=>1])->find();
        $token= Request::instance()->header('TOKEN');
        $this->language = Request::instance()->header('language')?Request::instance()->header('language'):$langs["link"];
        
        
        
        $tokenData = Db::table("xy_token")->where("token",$token)->order("time desc")->find();
     
        if(!in_array(Request::action(),$this->authentication)){
           if(!$tokenData){
            $this->success(yuylangs('登录失效'),[],-400);
          
             
            }
            if($tokenData['time'] + $this->tokenTime < time()){
                 $this->success(yuylangs('登录失效'),[],-400);
             
            } 
        }
        
        
        $uid = $tokenData['uid'];
        Cookie::forever('user_id', $uid);
        session('user_id', $uid);
        $this->usder_id = $uid;
        
        $controller = strtolower(\request()->controller());
        if ($controller == 'user') return;

        if (!$uid && request()->isPost()) {
            $this->error(yuylangs('no_login'));
        }

        $this->_uid = $uid;

        $this->console = Db::name('xy_script')->where('id', 1)->value('script');

        $userData = Db::name("xy_users")->find($uid);
        $user_level = Db::name('xy_level')->where('level',$userData['level'])->find();
        //自动购买利息宝
        if($user_level['auto_buy_finance'] == 1 && !empty($user_level['lixibao_id'])){
            $buy_lixibao = Db::name("xy_lixibao")->where('uid',$uid)->where('sid',$user_level['lixibao_id'])->find();
            if(empty($buy_lixibao)){
                $lixibao = Db::name("xy_lixibao_list")->where('id',$user_level['lixibao_id'])->find();
                if (!empty($lixibao) && $userData['balance'] >= $lixibao['min_num']) {
                    $yuji = $lixibao['min_num'] * $lixibao['bili'] * $lixibao['day'];
                    Db::startTrans();
                    try {
                        Db::name('xy_users')->where('id', $uid)->setInc('lixibao_balance', $lixibao['min_num']);  //利息宝月 +
                        Db::name('xy_users')->where('id', $uid)->setDec('balance', $lixibao['min_num']);  //余额 -
                        $endtime = time() + $lixibao['day'] * 24 * 60 * 60;
                        Db::name('xy_lixibao')->insert([
                            'uid' => $uid,
                            'num' => $lixibao['min_num'],
                            'addtime' => time(),
                            'endtime' => $endtime,
                            'sid' => $user_level['lixibao_id'],
                            'yuji_num' => $yuji,
                            'type' => 1,
                            'status' => 0,
                        ]);
                        $oid = Db::name('xy_lixibao')->getLastInsID();
                        Db::name('xy_balance_log')->insert([
                            //记录返佣信息
                            'uid' => $uid,
                            'oid' => $oid,
                            'num' => $lixibao['min_num'],
                            'type' => 21,
                            'status' => 2,
                            'addtime' => time(),
                            "balance" => $userData['balance']
                        ]);
                        Db::commit();
                    } catch (\Exception $e) {
                        Db::rollback();
                    }
                }
            }
        }

        if(config('master_bank') == 2){
             $level_list = Db::name('xy_level')
                ->field('level,`num`')
                ->where('num', '>', 0)
                ->order('level desc')->select();
                $new_vip_level = 1;
            foreach ($level_list as $v) {
                if ($v['num'] <= $userData['balance']) {
                    $new_vip_level = $v['level'];
                    break;
                }
            }
            Db::table("xy_users")->where(['id'=>$uid])->update(['level'=>$new_vip_level]);  
        }

        //vip过期判断
        if($userData['vip_expire_time'] != 0){
            if($userData['vip_expire_time'] < time()){
                $this->vip_expire = true;
            }
        }
        
        $uChats = url('support/index');
        $this->assign('user_service_chats', $uChats);
        
         $lang = cookie('think_var');
         if(!$lang){
             $langs = Db::table("xy_language")->where(['moryuy'=>1])->find();
             cookie('think_var',$langs['link']);
         }
        
    }

    public function showMessage($msg, $url = '-1')
    {
        if ($url == '-1') {
            echo "<script>alert('" . yuylangs('free_user_lxb') . "');window.history.back();</script>";
        } else {
            echo "<script>alert('" . yuylangs('free_user_lxb') . "');window.location.href='" . $url . "';</script>";
        }
        exit();
    }

    /**
     * 空操作 用于显示错误页面
     */
    public function _empty($name)
    {
        exit;
        return $this->fetch($name);
    }

    //图片上传为base64为的图片
    public function upload_base64($type, $img)
    {
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $img, $result)) {
            //$type_img = $result[2];  //得到图片的后缀
            $type_img = 'png';
            //上传 的文件目录

            $App = new \think\App();
            $new_files = $App->getRootPath() . 'upload' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m-d') . DIRECTORY_SEPARATOR;

            if (!file_exists($new_files)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                //服务器给文件夹权限
                mkdir($new_files, 0777, true);
            }
            //$new_files = $new_files.date("YmdHis"). '-' . rand(0,99999999999) . ".{$type_img}";
            $new_files = check_pic($new_files, ".{$type_img}");
            if (file_put_contents($new_files, base64_decode(str_replace($result[1], '', $img)))) {
                //上传成功后  得到信息
                $filenames = str_replace('\\', '/', $new_files);
                $file_name = substr($filenames, strripos($filenames, "/upload"));
                return $file_name;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * $type = 1 抢单
     * $type = 2 提现
     * 检查交易状态
     */
    public function check_deal($type = 1)
    {
        $uid = $this->usder_id;
        $uinfo = Db::name('xy_users')->where('id', $uid)->find();
        if ($uinfo['status'] == 2) return [
            'code' => 1,
            'info' => yuylangs('gzhybjy')
        ];
        //刷单状态
        if ($uinfo['shuadan_status'] != 1) return [
            'code' => 1,
            'info' => translate('Sin permiso para tomar pedidos, comuníquese con el servicio al cliente')
        ];
        //交易状态，0为冻结
        if ($uinfo['deal_status'] == 0) return [
            'code' => 1,
            'info' => yuylangs('gzhybdj')
        ];
        $uinfo['level'] = $uinfo['level'] ? intval($uinfo['level']) : 0;
        /*if ($uinfo['deal_status'] == 3) return [
            'code' => 1,
            'info' => yuylangs('gzhczwwcdd'),
            'url' => url('/index/order/index')
        ];*/
        //判断是否有未完成订单
        
        $order = Db::name('xy_convey')->where('uid', $uid)->where('status', 'in', [0, 5])->order("addtime desc")->find();
        if ($order) {
         
            if($order['rands'] > 1){
                if(!$order['is_pay']){
                    return [
                    'code' => 1,
                    'info' => yuylangs('gzhczwwcdd'),
                    'url' => url('/index/order/index')
                 ];  
                }
            }else{
                 return [
                    'code' => 1,
                    'info' => yuylangs('gzhczwwcdd'),
                    'url' => url('/index/order/index')
                 ];   
            }
        }
        $user_level = Db::table("xy_level")->where("level", $uinfo['level'])->find();
        //提现检查-最低余额要求
        if($type == 1){
            if ($uinfo['balance'] < $user_level['num_min']) return [
                'code' => 1,
                'info' => yuylangs('yedy') . ' ' . $user_level['num_min'] . ',' . yuylangs('wfjy'),
                'url' => url('index/ctrl/recharge')
            ];
        }
        //信用分限制
        if($uinfo['credit'] < $user_level['credit']){
            return [
                'code' => 1,
                'info' => translate('Credit score less than')." {$user_level['credit']}",
            ];
        }
        
        //是否昨天做过相同级别的任务
        if (config('is_same_yesterday_order') == 0 && $uinfo['group_id'] == 0) {
            $d1 = strtotime(date('Y-m-d')) - 86400;
            $d2 = strtotime(date('Y-m-d'));
            $oTd = Db::name('xy_convey')
                ->where('status', 1)
                ->where('uid', $uinfo['id'])
                ->where('level_id', $uinfo['level'])
                ->where('addtime', 'between', [$d1, $d2])
                //->where('addtime', '<', $d2)
                ->value('id');

            if ($oTd) {
                return [
                    'code' => 1,
                    'info' => yuylangs('order_error_level_num'),
                    'url' => url('/index/support/index'),
                    'endRal' => true
                ];
            }
        }

        if ($uinfo['group_id'] > 0) {
            //杀猪组，方案组模式
            $isRoll = Db::name('xy_group')
                ->where('id', $uinfo['group_id'])->value('is_roll');
            //如果不允许轮回 做单，目前isroll一直是1，所以下面的代码不执行
            if ($isRoll == 0) {
                //order_num
                // $max_order_num = Db::name('xy_group_rule')
                //     ->where('group_id', $uinfo['group_id'])
                //     ->order('order_num desc')
                //     ->value('order_num');
                  list($day_d_count, $groupRule, $all_order_num) = Convey::instance()->get_user_group_rule2($uinfo['id'], $uinfo['group_id']);
                
                // if (empty($all_order_num)) {
                //     return ['code' => 1, 'info' => yuylangs('hyddjycsbz'), 'endRal' => true];
                // }
                $u_order_num = Db::name('xy_convey')
                    ->where("qkon = 1")
                    ->where('group_id', $uinfo['group_id'])
                    ->where('uid', $uinfo['id'])
                    ->order('addtime desc')
                    ->limit(1)
                    ->value('group_rule_num');
                //如果是最后一单
                 $xy_group_rule1 = Db::table("xy_group_rule")->where("group_id",$uinfo['group_id'])->count();
                 $xy_group_rule2 = Db::table("xy_group_rule")->where("group_id",$uinfo['group_id'])->sum("add_orders1");
                $all_order_num1 = $xy_group_rule1 + $xy_group_rule2;
                $zuodanshu = Db::table("xy_convey")->where(["uid"=>$uinfo['id'],'group_id'=>$uinfo['group_id'],"qkon"=>1])->count();
             
         
              if($zuodanshu >= $all_order_num1){
                  return ['code' => 1, 'info' => yuylangs('hyddjycsbz'), 'endRal' => true];
              }
             
                // if ($u_order_num >= $all_order_num) {
                //     return ['code' => 1, 'info' => yuylangs('hyddjycsbz'), 'endRal' => true];
                // }
            }
        } else {
            //普通组
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
                 
            //获取可交易情况
         //  $orderSetting = Convey::instance()->get_user_order_setting($uid, $uinfo['level']);
           $order_num = $user_level["order_num"];
        
            if ($count >= $order_num) {
                return ['code' => 1, 'info' => yuylangs('hyddjycsbz'), 'endRal' => true];
            }
        }
        return false;
    }


    protected function getBankList($bank_data = [])
    {
         $fileurl = APP_PATH . "../config/bank.txt";
         $bank_data = file_get_contents($fileurl);
         $bank_data = explode("\n", $bank_data);
//        取消usdt
//        $bank_data = Db::table("xy_pay")->where('status',1)->where("is_payout",1)->find();
//        $bank_data = explode("\n", $bank_data["bank_code"]);
        $bank_list = [];
        foreach ($bank_data as $v) {
            $vS = explode('|', $v);
            if (count($vS) != 2) continue;
            $bank_list[trim($vS[0])] = trim($vS[1]);
        }
        return $bank_list;
    }
    
       //总进度
      public function dangqshuyu1($uid,$group_id)
    {
        $lastOrder = Db::name('xy_convey')
            ->where('uid', $uid)
            ->where("qkon = 1")
            //->where('group_is_active', 1)
            ->where('group_id', $group_id)
            ->where("duorw > 0")
            ->group("rands")
            ->order('oid desc')
            ->count();




       $feifzu =     Db::name('xy_convey')
            ->where('uid', $uid)
            ->where("qkon = 1")
           // ->where('group_is_active', 1)
            ->where('group_id', $group_id)
            ->where("duorw = 0")
            ->order('oid desc')
            ->count();

       if($lastOrder == 0 && $feifzu == 0){
           $sumOrder = 0;
           return $sumOrder;
       }else{
           $sumOrder = $lastOrder + $feifzu;
       }

//      $lastOrder1 = Db::name('xy_convey')
//            ->where('uid', $uid)
//            ->where("qkon = 1")
//          //  ->where('group_is_active', 1)
//            ->where('group_id', $group_id)
//            ->where("duorw > 0")
//            ->order('addtime desc')
//            ->find();
//    $lastOrder2 = Db::name('xy_convey')
//            ->where('uid', $uid)
//            ->where("qkon = 1")
//           // ->where('group_is_active', 1)
//            ->where('group_id', $group_id)
//            ->where("duorw > 0")
//            ->where("group_rule_num",$lastOrder1["group_rule_num"])
//            ->count();

    //   if($lastOrder2 >= $lastOrder1['duorw']){
    //       $sumOrder = $sumOrder + 1;
    //   }
            
    
     return $sumOrder;
    }

}
