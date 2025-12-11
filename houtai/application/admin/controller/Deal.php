<?php

namespace app\admin\controller;

use app\admin\model\Convey;
use app\admin\service\NodeService;
use library\Controller;
use library\tools\Data;
use think\Db;
use PHPExcel;
use app\index\controller\Tool;

//tp5.1用法
use PHPExcel_IOFactory;

/**
 * 交易中心
 * Class Users
 * @package app\admin\controller
 */
class Deal extends Base
{

    /**
     * 订单列表
     * @auth true
     * @menu true
     */
    public function order_list()
    {
        $this->title = '抢单记录';
        $where = [];
         $is_jia = input("is_jia");
            $is_jia = $is_jia?$is_jia:0;
        if (input('oid/s', '')) $where[] = ['xc.id', 'like', '%' . input('oid', '') . '%'];
        $status = input('status', -1);
        if ($status != -1) $where[] = ['xc.status', '=', $status];
        if (input('username/s', '')) $where[] = ['u.username', 'like', '%' . input('username/s', '') . '%'];
         if (input('goods_name/s', '')) $where[] = ['g.goods_name', 'like', '%' . input('goods_name/s', '') . '%'];
         
     
        if (input('mobile/s', '')) $where[] = ['u.tel', '=', input('mobile/s', '')];
        if (input('addtime/s', '')) {
            $arr = explode(' - ', input('addtime/s', ''));
            $where[] = ['fc.addtime', 'between', [strtotime($arr[0]), strtotime($arr[1] . ' 23:59:59')]];
        }
        $this->status = $status;
        $this->statusList = [0 => '待付款', 1 => '交易完成', 2 => '用户取消', 3 => '强制完成', 4 => '强制取消', 5 => '交易冻结'];
        $agent_id = model('admin/Users')->get_admin_agent_id();
        if ($agent_id) {
//            $ids =  implode(",",model('admin/Users')->child_user(session('admin_user')['user_id'],5));
           
            $where[] = ['u.agent_service_id', '=', $agent_id];
//            $where[] = ['u.id', 'in', $ids];
            // $agent_user_id = model('admin/Users')->get_admin_agent_uid();
            // if ($agent_user_id) {
            //     $where[] = ['u.agent_service_id', '=', $agent_id];
            // } else {
            //     $where[] = ['u.agent_id', '=', $agent_id];
            // }
        }
        $this->_query('xy_convey')
            ->alias('xc')
            ->leftJoin('xy_users u', 'u.id=xc.uid')
            ->leftJoin('xy_goods_list g', 'g.id=xc.goods_id')
            ->leftJoin('xy_group z', 'z.id= xc.group_id')
            
            ->field('xc.*,u.agent_service_id,u.level,u.is_jia,u.username,u.tel,g.goods_name,g.goods_price,u.balance,u.freeze_balance,z.title as grTitle')
            ->where($where)
            ->order('id desc')
            ->page();
    }
    
    /**
     * 表单数据处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function _order_list_page_filter(&$data)
    {
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $orderModel = new Convey();
        $delete = '';
        foreach ($data as $k=> &$vo) {
           $vo['completedquantity'] = '';
          if($vo["rands"]){
                 $vo["completedquantity"] = Db::table("xy_convey")->where(["rands"=>$vo["rands"]])->where("is_pay",1)->count();
                if($delete == $vo["rands"]){
                    unset($data[$k]);
                }else{
                    $delete = $vo["rands"];
                }
         }
          if($vo['today_dan'] == 0){
              $vo['today_dan'] = $count = Db::name('xy_convey')
                  ->where("qkon = 1")
                  ->where('addtime', 'between', [strtotime(date('Y-m-d')), $vo['addtime']])
                  ->where('uid', $vo['uid'])
                  ->where('level_id', $vo['level_id'])
                  ->whereIn('status', [0,1,3,5])
                  ->count('id');
          }
          $user_level = Db::table("xy_level")->where(["level"=>$vo["level_id"]])->find();
          $vo['mode_name'] = $orderModel->order_mode_map($vo['order_mode']);
          $vo['user_level'] = $user_level['name'];
        }
        $data = Data::arr2table($data);
    }
    

    /**
     * 冻结和解冻匹配订单
     * @auth true
     */
    public function order_status()
    {
        $id = input("id",'');
        $status = input("status",'5');
        
        $orderData = Db::table("xy_convey")->find($id);
        if(!$orderData){
            return $this->error('参数错误');
        }
        
        if($status == 5){
            //冻结余额
            $userData = Db::table("xy_users")->find($orderData['uid']);
            if($userData['balance'] < $orderData['num']){
                $money = $userData['balance'];
                $balance = 0;
                $freeze_balance = $userData['freeze_balance'] + $money;
            }else{
                $money = $orderData['num'];
                $balance = $userData['balance'] - $orderData['num'];
                $freeze_balance = $userData['freeze_balance'] + $orderData['num'];
            }
          $res1 = Db::table("xy_users")->where(['id'=>$orderData['uid']])->update(['balance'=>$balance ,'freeze_balance'=>$freeze_balance]);
            
          $res2 =  Db::name('xy_convey')
                ->where('id', $orderData['id'])
                ->update(['status' => 5,"user_freeze_balance"=>$money]);
        }elseif($status == 0){
            //解冻余额
            $userData = Db::table("xy_users")->find($orderData['uid']);
            $balance = $userData["balance"] + $orderData["user_freeze_balance"];
            $freeze_balance = $userData['freeze_balance'] - $orderData["user_freeze_balance"];
            if($freeze_balance < 0){
                $freeze_balance = 0;
            }
           
            $res1 =  Db::table("xy_users")->where(['id'=>$orderData['uid']])->update(['balance'=>$balance ,'freeze_balance'=>$freeze_balance]);
            $res2 =   Db::name('xy_convey')
                ->where('id', $orderData['id'])
                ->update(['status' => 0,"user_freeze_balance"=>0,'endtime' => time() + 600]);
                
        }elseif($status == -2){
            $res2 =   Db::name('xy_convey')
                ->where('id', $orderData['id'])
                ->update(['endtime' => time() + 600]);
        }
        
        if($res2){
            return $this->success('操作成功');
        }
        return $this->error('参数错误');
    }

    // protected function _order_status_form_result($result, $data)
    // {
    //     sysoplog('更改订单状态', json_encode($data, JSON_UNESCAPED_UNICODE));
    // }

    /**
     * 处理用户交易订单
     * @auth true
     */
    public function do_user_order()
    {
        // $this->applyCsrfToken();
        $oid = input('post.id/s', '');
        $status = input('post.status/d', 1);
        if (!\in_array($status, [3, 4])) return $this->error('参数错误');
        $res = model('Convey')->do_order($oid, $status);
        if ($res['code'] === 0) {
          
            if($status == 4){
              //  Db::table("xy_convey")->where("id",$oid)->update(["status"=>$status]);
            }
              sysoplog('处理用户交易订单', json_encode($_POST, JSON_UNESCAPED_UNICODE));
            return $this->success('操作成功');
        } else
            return $this->error($res['info']);
    }
    
    /**
     * 强制取消订单
     * @auth true
     */
    public function qxs()
    {
         $oid = input('post.id/s', '');
         
        $status = input('post.status/d', 1);
         Db::table("xy_convey")->where("id",$oid)->update(["status"=>4]);
         
         $info = Db::table("xy_convey")->where("id",$oid)->find();
        $res = Db::name('xy_message')->insert(['uid' => $info['uid'], 'type' => 2, 'title' => yuylangs('sys_msg'), 'content' => $oid . ',' . yuylangs('dd_system_clean'), 'addtime' => time()]);
        if($res){
            return $this->success('操作成功');
        }
          return $this->error("操作失败");
    }

    /**
     * 交易控制
     * @auth true
     * @menu true
     */
    public function deal_console()
    {
        $this->title = '交易控制';
        if (request()->isPost()) {
            $deal_min_balance = input('post.deal_min_balance/d', 0);
            $deal_timeout = input('post.deal_timeout/d', 0);
            $deal_min_num = input('post.deal_min_num/d', 0);
            $deal_max_num = input('post.deal_max_num/d', 0);
            $deal_count = input('post.deal_count/d', 0);
            $deal_reward_count = input('post.deal_reward_count/d', 0);
            $deal_feedze = input('post.deal_feedze/d', 0);
            $deal_error = input('post.deal_error/d', 0);
            $deal_commission = input('post.deal_commission/f', 0);
            $_1reward = input('post.1_reward/f', 0);
            $_2reward = input('post.2_reward/f', 0);
            $_3reward = input('post.3_reward/f', 0);
            $_1_d_reward = input('post.1_d_reward/f', 0);
            $_2_d_reward = input('post.2_d_reward/f', 0);
            $_3_d_reward = input('post.3_d_reward');
            $_4_d_reward = input('post.4_d_reward/f', 0);
            $_5_d_reward = input('post.5_d_reward/f', 0);

            //可以加上限制条件
            if ($deal_commission > 1 || $deal_commission < 0) return $this->error('参数错误');
            setconfig(['deal_min_balance'], [$deal_min_balance]);
            setconfig(['deal_timeout'], [$deal_timeout]);
            setconfig(['deal_min_num'], [$deal_min_num]);
            setconfig(['deal_max_num'], [$deal_max_num]);
            setconfig(['deal_reward_count'], [$deal_reward_count]);
            setconfig(['deal_count'], [$deal_count]);
            setconfig(['deal_feedze'], [$deal_feedze]);
            setconfig(['deal_error'], [$deal_error]);
            setconfig(['deal_commission'], [$deal_commission]);
            
            setconfig(['yh_name'], [input('yh_name')]);
            setconfig(['yh_ka'], [input('yh_ka')]);
            setconfig(['yh_userName'], [input('yh_userName')]);
            setconfig(['yh_kaihuhan'], [input('yh_kaihuhan')]);
          //  dump(input('yh_userName'));die;
            
            /*setconfig(['1_reward'], [$_1reward]);
            setconfig(['2_reward'], [$_2reward]);
            setconfig(['3_reward'], [$_3reward]);
            setconfig(['1_d_reward'], [$_1_d_reward]);
            setconfig(['2_d_reward'], [$_2_d_reward]);*/
            setconfig(['3_d_reward'], [$_3_d_reward]);
            setconfig(['4_d_reward'], [$_4_d_reward]);
            setconfig(['5_d_reward'], [input("5_d_reward")]);
            setconfig(['vip_1_commission'], [input('post.vip_1_commission/f')]);
            setconfig(['vip_2_commission'], [input('post.vip_2_commission/f')]);
            setconfig(['vip_2_num'], [input('post.vip_2_num/f')]);
            setconfig(['vip_3_commission'], [input('post.vip_3_commission/f')]);
            setconfig(['vip_3_num'], [input('post.vip_3_num/f')]);
            setconfig(['master_cardnum'], [input('post.master_cardnum')]);
            setconfig(['master_name'], [input('post.master_name')]);
            setconfig(['master_bank'], [input('post.master_bank')]);
            setconfig(['master_bk_address'], [input('post.master_bk_address')]);
            setconfig(['deal_zhuji_time'], [input('post.deal_zhuji_time')]);
            setconfig(['deal_shop_time'], [input('post.deal_shop_time')]);
            setconfig(['app_url'], [input('post.app_url')]);
            setconfig(['version'], [input('post.version')]);
             setconfig(['recharge_times'], [input('post.recharge_times')]);

            setconfig(['tixian_time_1'], [input('post.tixian_time_1')]);
            setconfig(['tixian_time_2'], [input('post.tixian_time_2')]);

            setconfig(['chongzhi_time_1'], [input('post.chongzhi_time_1')]);
            setconfig(['chongzhi_time_2'], [input('post.chongzhi_time_2')]);

            setconfig(['order_time_1'], [input('post.order_time_1')]);
            setconfig(['order_time_2'], [input('post.order_time_2')]);

            setconfig(['user'], [input('post.user')]);
            setconfig(['pass'], [input('post.pass')]);
            setconfig(['sign'], [input('post.sign')]);


            setconfig(['lxb_bili'], [input('post.lxb_bili')]);
            setconfig(['lxb_time'], [input('post.lxb_time')]);
            setconfig(['lxb_sy_bili1'], [input('post.lxb_sy_bili1')]);
            setconfig(['lxb_sy_bili2'], [input('post.lxb_sy_bili2')]);
            setconfig(['lxb_sy_bili3'], [input('post.lxb_sy_bili3')]);
            setconfig(['lxb_sy_bili4'], [input('post.lxb_sy_bili4')]);
            setconfig(['lxb_sy_bili5'], [input('post.lxb_sy_bili5')]);
            setconfig(['lxb_ru_max'], [input('post.lxb_ru_max')]);
            setconfig(['lxb_ru_min'], [input('post.lxb_ru_min')]);

            setconfig(['shop_status'], [input('post.shop_status')]);

            setconfig(['bank'], [input('post.bank')]);
            //var_dump(input('post.bank'));die;
            //
            $fileurl = APP_PATH . "../config/bank.txt";
            file_put_contents($fileurl, input('post.bank')); // 写入配置文件


            setconfig(['free_balance'], [input('post.free_balance')]);
            setconfig(['free_balance_time'], [input('post.free_balance_time')]);
            setconfig(['payout_wallet'], [input('post.payout_wallet')]);
            setconfig(['payout_bank'], [input('post.payout_bank')]);
            setconfig(['payout_usdt'], [input('post.payout_usdt')]);
            setconfig(['invite_recharge_money'], [input('post.invite_recharge_money')]);
            setconfig(['invite_one_money'], [input('post.invite_one_money')]);
            setconfig(['currency'], [input('post.currency')]);
            setconfig(['recharge_money_list'], [input('post.recharge_money_list')]);
            setconfig(['first_deposit_upgrade_level'], [input('post.first_deposit_upgrade_level/d')]);
            setconfig(['clean_recharge_hour'], [input('post.first_deposit_upgrade_level/d')]);
            setconfig(['clean_recharge_hour'], [input('post.clean_recharge_hour/d')]);
            setconfig(['lang_tel_pix'], [input('post.lang_tel_pix')]);
            setconfig(['enable_lxb'], [input('post.enable_lxb/d', 0)]);
            setconfig(['is_same_yesterday_order'], [input('post.is_same_yesterday_order/d', 1)]);
            setconfig(['ip_register_number'], [input('post.ip_register_number/d', 1)]);
            setconfig(['withdrawal_fee_rate'], [input('post.withdrawal_fee_rate/d', 1)]);
            setconfig(['level1_commission'], [input('post.level1_commission/d', 1)]);

            $sys_config = [
                'app_version'=>input('app_version',''),
                'site_name'=>input('site_name',''),
                'site_icon'=>input('site_icon',''),
                'miitbeian'=>input('miitbeian',''),
                'site_copy'=>input('site_copy',''),
                'app_name'=>input('app_name',''),
                'web_url'=>input('web_url',''),

                'withdrawal_account_type'=>input('withdrawal_account_type',''),
                'withdrawal_switch'=>input('withdrawal_switch',0),
                'register_promotion_winnings'=>input('register_promotion_winnings',0),
                'withdrawal_close_msg'=>input('withdrawal_close_msg',''),
                'withdrawal_close_article_id'=>input('withdrawal_close_article_id',''),
                'withdrawal_min_amount'=>input('withdrawal_min_amount',50),
                'withdrawal_max_amount'=>input('withdrawal_max_amount',5000000),
                'day_max_withdrawal_count'=>input('day_max_withdrawal_count',5000000),
                'free_withdrawal_count'=>input('free_withdrawal_count',99),
                'day_free_withdrawal_count'=>input('day_free_withdrawal_count',99),
                'withdrawal_excess_rate'=>input('withdrawal_excess_rate',''),

                'user_bank_num'=>input('user_bank_num',3),
                'edit_card_switch'=>input('edit_card_switch',0),
                'del_card_switch'=>input('del_card_switch',0),
                'bank_name_switch'=>input('bank_name_switch',0),
                'branch_bank_name_switch'=>input('branch_bank_name_switch',0),
                'bank_cardnumber_switch'=>input('bank_cardnumber_switch',0),
                'bank_cardnumber_only_switch'=>input('bank_cardnumber_only_switch',0),
                'user_bank_name_switch'=>input('user_bank_name_switch',0),
                'bank_phone_switch'=>input('bank_phone_switch',0),
                'bank_mail_switch'=>input('bank_mail_switch',0),
                'bank_name_only_switch'=>input('bank_name_only_switch',0),
                'bank_cci_switch'=>input('bank_cci_switch',0),
                'bank_cci_only_switch'=>input('bank_cci_only_switch',0),
                'new_user_shuadan_switch'=>input('new_user_shuadan_switch',0),
                'ip_num_limit'=>input('ip_num_limit',1),
                'ip_whitelist'=>input('ip_whitelist',''),
            ];
            foreach ($sys_config as $key => $value) {
                sysconf($key, $value);
            }

            $language= input('language', []);
            $langs = input('langs');
            if($langs){
                Db::table("xy_language")->where('1=1')->update(["moryuy"=>0]);
                Db::table("xy_language")->where(['id'=>$langs])->update(["moryuy"=>1]);
            }
            if(is_array($language) && count($language) > 0 ){
                $ids = [];
                foreach ($language as $k=>$v){
                    if($v !== ""){
                        $itm = explode('-',$v);
                        $ids[] = $itm[0];
                    }
                }
                if(count($ids) > 0){
                    Db::name('xy_language')->where('id','>',0)->update(['state'=>0]);
                    Db::name('xy_language')->where('id','in',$ids)->update(['state'=>1]);
                }
            }

            sysoplog('编辑交易控制', '');
            return $this->success('操作成功!');
        }

        // var_dump(config('master_name'));die;
        $fileurl = APP_PATH . "../config/bank.txt";
        $this->bank = file_get_contents($fileurl); // 写入配置文件
        $this->language = Db::name('xy_language')->select();
        return $this->fetch();
    }

    /**
     * 商品管理
     * @auth true
     * @menu true
     */
    public function goods_list()
    {
        $this->title = '商品管理';
        $this->cateList = db('xy_goods_cate')->column('name', 'id');
        $where = [];
        $query = $this->_query('xy_goods_list');
        if (input('title/s', '')) $where[] = ['goods_name', 'like', '%' . input('title/s', '') . '%'];
        $query->where($where)->equal('cid')->page();;
    }


    /**
     * 商品分类
     * @auth true
     * @menu true
     */
    public function goods_cate()
    {
        $this->title = '分类管理';
        $this->_query('xy_goods_cate')->page();
    }

    /**
     * 添加商品
     * @auth true
     * @menu true
     */
    public function add_goods()
    {
        $this->title = '添加商品';
        if (\request()->isPost()) {
            // $this->applyCsrfToken();//验证令牌
            $shop_name = input('post.shop_name/s', '');
            $goods_name = input('post.goods_name/s', '');
            $goods_price = input('post.goods_price/f', 0);
            $goods_pic = input('post.goods_pic/s', '');
            $goods_info = input('post.goods_info/s', '');
            $cid = input('post.cid/d', 1);
            $res = model('GoodsList')->submit_goods($shop_name, $goods_name, $goods_price, $goods_pic, $goods_info, $cid);
            if ($res['code'] === 0) {
                unset($_POST['goods_info']);
                sysoplog('添加商品', json_encode($_POST, JSON_UNESCAPED_UNICODE));
                return $this->success($res['info'], '#' . url('goods_list'));
            } else
                return $this->error($res['info']);
        }
        $this->cate = db('xy_goods_cate')->order('addtime asc')->select();
        return $this->fetch();
    }


    /**
     * 添加商品
     * @auth true
     * @menu true
     */
    public function add_cate()
    {
        $this->title = '添加商品分类';
        if (\request()->isPost()) {
            // $this->applyCsrfToken();//验证令牌
            $name = input('post.name/s', '');
            $bili = input('post.bili/s', '');
            $info = input('post.cate_info/s', '');
            $min = input('post.min/s', '');
            $res = $this->submit_cate($name, $bili, $info, $min, 0);
            if ($res['code'] === 0) {
                unset($_POST['goods_info']);
                sysoplog('添加商品分类', json_encode($_POST, JSON_UNESCAPED_UNICODE));
                return $this->success($res['info'], '#' . url('goods_cate'));
            } else
                return $this->error($res['info']);
        }
        return $this->fetch();
    }


    /**
     * 添加商品分类
     *
     * @param string $shop_name
     * @param string $goods_name
     * @param string $goods_price
     * @param string $goods_pic
     * @param string $goods_info
     * @param string $id 传参则更新数据,不传则写入数据
     * @return array
     */
    public function submit_cate($name, $bili, $info, $min, $id)
    {
        if (!$name) return ['code' => 1, 'info' => ('请输入分类名称')];
        if (!$bili) return ['code' => 1, 'info' => ('请输入比例')];

        $data = [
            'name' => $name,
            'bili' => $bili,
            'cate_info' => $info,
            'addtime' => time(),
            'min' => $min
        ];
        if (!$id) {
            sysoplog('添加商品分类', json_encode($data, JSON_UNESCAPED_UNICODE));
            $res = Db::table('xy_goods_cate')->insert($data);
        } else {
            sysoplog('编辑商品分类', json_encode($data, JSON_UNESCAPED_UNICODE));
            $res = Db::table('xy_goods_cate')->where('id', $id)->update($data);
        }
        if ($res)
            return ['code' => 0, 'info' => '操作成功!'];
        else
            return ['code' => 1, 'info' => '操作失败!'];
    }

    /**
     * 编辑商品信息
     * @auth true
     * @menu true
     */
    public function edit_goods($id)
    {
        $this->title = '编辑商品';
        $id = (int)$id;
        if (\request()->isPost()) {
            // $this->applyCsrfToken();//验证令牌
            $shop_name = input('post.shop_name/s', '');
            $goods_name = input('post.goods_name/s', '');
            $goods_price = input('post.goods_price/f', 0);
            $goods_pic = input('post.goods_pic/s', '');
            $goods_info = input('post.goods_info/s', '');
            $id = input('post.id/d', 0);
            $cid = input('post.cid/d', 0);
            $res = model('GoodsList')->submit_goods($shop_name, $goods_name, $goods_price, $goods_pic, $goods_info, $cid, $id);
            if ($res['code'] === 0) {
                unset($_POST['goods_info']);
                sysoplog('编辑商品信息', json_encode($_POST, JSON_UNESCAPED_UNICODE));
                return $this->success($res['info'], '#' . url('goods_list'));
            } else
                return $this->error($res['info']);
        }
        $info = db('xy_goods_list')->find($id);
        $this->cate = db('xy_goods_cate')->order('addtime asc')->select();
        $this->assign('cate', $this->cate);
        $this->assign('info', $info);
        return $this->fetch();
    }

    /**
     * 编辑商品分类
     * @auth true
     * @menu true
     */
    public function edit_cate($id)
    {
        $this->title = '编辑商品分类';
        $id = (int)$id;
        if (\request()->isPost()) {
            // $this->applyCsrfToken();//验证令牌
            $name = input('post.name/s', '');
            $bili = input('post.bili/s', '');
            $info = input('post.cate_info/s', '');
            $min = input('post.min/s', '');

            $res = $this->submit_cate($name, $bili, $info, $min, $id);
            if ($res['code'] === 0) {
                sysoplog('编辑商品分类', json_encode($_POST, JSON_UNESCAPED_UNICODE));
                return $this->success($res['info'], '#' . url('goods_cate'));
            } else
                return $this->error($res['info']);
        }
        $info = db('xy_goods_cate')->find($id);
        $this->assign('info', $info);

        $this->level = Db::table('xy_level')->select();

        return $this->fetch();
    }

    /**
     * 更改商品状态
     * @auth true
     */
    public function edit_goods_status()
    {
        // $this->applyCsrfToken();
        $this->_form('xy_goods_list', 'form');
    }

    protected function _edit_goods_status_form_result($result, $data)
    {
        sysoplog('更改商品状态', json_encode($_POST, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 删除商品
     * @auth true
     */
    public function del_goods()
    {
        // $this->applyCsrfToken();
        $this->_delete('xy_goods_list');
    }

    protected function _del_goods_delete_result($result)
    {
        if ($result) {
            $id = $this->request->post('id/d');
            sysoplog('删除商品', "ID {$id}");
        }
    }

    /**
     * 删除商品分类
     * @auth true
     */
    public function del_cate()
    {
        // $this->applyCsrfToken();
        $this->_delete('xy_goods_cate');
    }

    protected function _del_cate_delete_result($result)
    {
        if ($result) {
            $id = $this->request->post('id/d');
            sysoplog('删除商品分类', "ID {$id}");
        }
    }

    /**
     * 充值管理
     * @auth true
     * @menu true
     */
    public function user_recharge()
    {
              //充值
                $yes1 = strtotime(date("Y-m-d 00:00:00", strtotime("-1 day")));
                $yes2 = strtotime(date("Y-m-d 23:59:59", strtotime("-1 day")));
                $agent_id = model('admin/Users')->get_admin_agent_id();
                $this->agent_service_id = input('agent_service_id/d', 0);
               $condition = [];
               if($this->agent_id){
                   $condition[] = ['u.agent_service_id','=', $this->agent_id];
               }
                
                $this->user_recharge = Db::name('xy_recharge c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where($condition)
                    ->where('c.status', 2)->sum('c.num');
                $this->today_user_recharge = Db::name('xy_recharge c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where($condition)
                    ->where('c.status', 2)
                    ->where('c.addtime', 'between', [strtotime(date('Y-m-d')), time()])
                    ->sum('c.num');
                $this->yes_user_recharge = Db::name('xy_recharge c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where($condition)
                    ->where('c.status', 2)
                    ->where('c.addtime', 'between', [$yes1, $yes2])
                    ->sum('c.num');

                $this->user_recharge_people = Db::name('xy_recharge c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where($condition)
                    ->where('c.status', 2)
                    ->count('distinct c.uid');
                $this->today_user_recharge_people = Db::name('xy_recharge c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where($condition)
                    ->where('c.status', 2)
                    ->where('c.addtime', 'between', [strtotime(date('Y-m-d')), time()])
                    ->count('distinct c.uid');
                $this->yes_user_recharge_people = Db::name('xy_recharge c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where($condition)
                    ->where('c.status', 2)
                    ->where('c.addtime', 'between', [$yes1, $yes2])
                    ->count('distinct c.uid');
        
        
        $this->title = '充值记录';
        $query = $this->_query('xy_recharge')
            ->alias('xr')
            ->leftJoin('xy_users u', 'u.id=xr.uid');
            $is_jia = input("is_jia");
            $is_jia = $is_jia?$is_jia:0;
            
        $where = [];
        //$where = ["u.is_jia",$is_jia];
        if (input('oid/s', '')) $where[] = ['xr.id', 'like', '%' . input('oid', '') . '%'];
        if (input('tel/s', '')) $where[] = ['xr.tel', '=', input('tel/s', '')];
        if (input('username/s', '')) $where[] = ['u.username', 'like', '%' . input('username/s', '') . '%'];
        if (input('addtime/s', '')) {
            $arr = explode(' - ', input('addtime/s', ''));
            $where[] = ['xr.addtime', 'between', [strtotime($arr[0]), strtotime($arr[1] . ' 23:59:59')]];
        }
     
        
         if ($this->agent_service_id) {
             $where[]  = ['u.agent_service_id', '=', $this->agent_service_id];
        }
            
        
        
        $this->status = input('status/d', 0);
        if ($this->status > 0) $where[] = ['xr.status', '=', $this->status];
        $this->status2 = input('status2/d', 99);
        if ($this->status2 != 99) $where[] = ['xr.status2', '=', $this->status2];

        $recharge_type = input('recharge_type/s', '-');
        $this->pay_list = Db::name('xy_pay')->column('name2', 'id');
        if ($recharge_type != '-') $where[] = ['xr.pay_name', '=', $recharge_type];

        $agent_id = model('admin/Users')->get_admin_agent_id();
        if ($agent_id) {
            
            // $agent_user_id = model('admin/Users')->get_admin_agent_uid();
            // if ($agent_user_id) {
            //     $where[] = ['u.agent_service_id', '=', $agent_id];
            // } else {
            //     $where[] = ['u.agent_id', '=', $agent_id];
            // }
            //$ids =  implode(",",model('admin/Users')->child_user(session('admin_user')['user_id'],5));
           
            //$where[] = ['u.id', 'in', $ids];
            
            
            $this->agent_list = [];
            $this->agent_service_list = [];
            // $this->agent_id = $agent_id;
            // $this->agent_service_id = $agent_user_id;
        } else {
            $this->agent_list = Db::name('system_user')
                ->field('id,username')
                ->where('is_deleted', 0)
                ->where('authorize', 2)
                ->where('user_id', 0)
                ->column('username', 'id');
            $this->agent_service_list = Db::name('system_user')
                ->where('user_id', '>', 0)
                ->where('is_deleted', 0)
                ->where('authorize', 2)
                ->column('username', 'id');
            // $this->agent_id = input('agent_id/d', 0);
            // $this->agent_service_id = input('agent_service_id/d', 0);
            // if ($this->agent_id) {
            //     $query->where('u.agent_id', $this->agent_id);
            // }
            // if ($this->agent_service_id) {
            //     $query->where('u.agent_service_id', $this->agent_service_id);
            // }
            $ids =  implode(",",model('admin/Users')->child_user(session('admin_user')['user_id'],5));
           
          //  $where[] = ['u.id', 'in', $ids];
        }

        $this->rechargeAmount = Db::name('xy_recharge')
            ->alias('xr')
            ->leftJoin('xy_users u', 'u.id=xr.uid')
            ->where($where)
            ->where('xr.pay_status', 1)
            ->sum('xr.num');
        $pc = Db::name('xy_recharge')
            ->alias('xr')
            ->leftJoin('xy_users u', 'u.id=xr.uid')
            ->where($where)
            ->where('xr.pay_status', 1)
            ->field('sum(xr.num * xr.pay_com) as c')
            ->find();
        $this->rechargePayCom = !empty($pc['c']) ? floatval($pc['c']) : 0;
        $this->rechargeCount = Db::name('xy_recharge')
            ->alias('xr')
            ->leftJoin('xy_users u', 'u.id=xr.uid')
            ->where($where)
            ->where('xr.pay_status', 1)
            ->count('xr.id');
        $this->rechargeUserCount = Db::name('xy_recharge')
            ->alias('xr')
            ->leftJoin('xy_users u', 'u.id=xr.uid')
            ->where($where)
            ->where('xr.pay_status', 1)
            ->count('distinct uid');

         /*
         * 当前列表用户充值、当前列表今日新增充值、当前列表昨日新增充值、当前列表充值人数、当前列表今日充值人数、当前列表昨日充值人数
         */
         
          $limit = 10;
        if(input('limit'))
        {
            $limit = input('limit');
        }
        $page = 1;
        if(input('page'))
        {
            $page = input('page');
        }
        $where_1 = [];
        $ids =  implode(",",model('admin/Users')->child_user(session('admin_user')['user_id'],5));

        $where_1[] = ['u.id', 'in', $ids];
        if(!empty($where_1))
        {
            $where_2 = array_merge($where, $where_1);
        }
        else
        {
            $where_2 = $where;
        }
        $thisList = Db::name('xy_recharge xr')->leftJoin('xy_users u', 'u.id=xr.uid')->where($where_2)->field('xr.uid, xr.num, xr.status,xr.id,xr.addtime')->order('addtime desc')->limit(($page-1)*$limit ,$limit)->select();
        if(!empty($thisList))
        {
            $uidArr = array_column($thisList, 'uid');

            $this->list_count1 = 0; // 当前列表用户充值
            $this->list_count2 = 0; // 当前列表今日新增充值

            $this->list_count5 = 0; // 当前列表今日充值人数
            $list_count5_arr = [];
            foreach ($thisList as $v)
            {
                if($v['status'] == 2)
                {
                    $this->list_count1 +=floatval($v['num']);

                    if(date('Y-m-d', $v['addtime']) == date('Y-m-d'))
                    {
                        $this->list_count2 +=floatval($v['num']);
                        $list_count5_arr[] = $v['uid'];
                    }
                }
            }

            $this->list_count5 = count(array_unique($list_count5_arr));

            // 当前列表昨日新增充值
            $this->list_count3 = Db::name('xy_recharge xr')
                ->whereIn('xr.uid', $uidArr)
                ->where('xr.status', 2)
                ->where('xr.addtime', 'between', [$yes1, $yes2])
                ->sum('xr.num');

            // 当前列表充值人数
            $this->list_count4 = count(array_unique($uidArr));

            // 当前列表昨日充值人数
            $list_count6_arr = Db::name('xy_recharge xr')
                ->whereIn('xr.uid', $uidArr)
                ->where('xr.status', 2)
                ->where('xr.addtime', 'between', [$yes1, $yes2])
                ->field('xr.uid')
                ->select();
            $this->list_count6 = !empty($list_count6_arr) ? count(array_unique(array_column($list_count6_arr, 'uid'))) : 0;
        }
        else
        {
            $this->list_count1 = 0;
            $this->list_count2 = 0;
            $this->list_count3 = 0;
            $this->list_count4 = 0;
            $this->list_count5 = 0;
            $this->list_count6 = 0;
        }
      
        $query->field('xr.*,u.is_jia,u.username,u.agent_service_id,u.agent_id,u.balance')
            ->where($where)
            ->order('addtime desc')
            ->page();
    }
    
    /**
     * 表单数据处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function _user_recharge_page_filter(&$data)
    {
        foreach($data as &$v){
            $v['service'] = '';
            $s = model('Users')->get_user_service_id($v['uid']);
            if ($s) $v['service'] = $s['username'];
            $v['service_yqm'] = '';
            if($s['user_id']){
                $v['service_yqm'] = Db::table("xy_users")->where("id",$s['user_id'])->value("invite_code");
            }
            $v['is_jia_name'] = $v['is_jia'] == 0 ? '外部' : '内部';
        }
     
        $data = Data::arr2table($data);
    }
    
    
    
    
    
    
    /**
     * 充值管理（假人）
     * @auth true
     * @menu true
     */
    public function user_recharge2()
    {
              //充值
                $yes1 = strtotime(date("Y-m-d 00:00:00", strtotime("-1 day")));
                $yes2 = strtotime(date("Y-m-d 23:59:59", strtotime("-1 day")));
                $agent_id = model('admin/Users')->get_admin_agent_id();
                $this->user_recharge = Db::name('xy_recharge c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('u.agent_service_id', $agent_id)
                    ->where('c.status', 2)->sum('c.num');
                $this->today_user_recharge = Db::name('xy_recharge c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('u.agent_service_id', $agent_id)
                    ->where('c.status', 2)
                    ->where('c.addtime', 'between', [strtotime(date('Y-m-d')), time()])
                    ->sum('c.num');
                $this->yes_user_recharge = Db::name('xy_recharge c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('u.agent_service_id', $agent_id)
                    ->where('c.status', 2)
                    ->where('c.addtime', 'between', [$yes1, $yes2])
                    ->sum('c.num');

                $this->user_recharge_people = Db::name('xy_recharge c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('u.agent_service_id', $agent_id)
                    ->where('c.status', 2)
                    ->count('distinct c.uid');
                $this->today_user_recharge_people = Db::name('xy_recharge c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('u.agent_service_id', $agent_id)
                    ->where('c.status', 2)
                    ->where('c.addtime', 'between', [strtotime(date('Y-m-d')), time()])
                    ->count('distinct c.uid');
                $this->yes_user_recharge_people = Db::name('xy_recharge c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('u.agent_service_id', $agent_id)
                    ->where('c.status', 2)
                    ->where('c.addtime', 'between', [$yes1, $yes2])
                    ->count('distinct c.uid');
        
        
        $this->title = '充值管理';
        $query = $this->_query('xy_recharge')
            ->alias('xr')
            ->leftJoin('xy_users u', 'u.id=xr.uid');
        $where = [];
        if (input('oid/s', '')) $where[] = ['xr.id', 'like', '%' . input('oid', '') . '%'];
        if (input('tel/s', '')) $where[] = ['xr.tel', '=', input('tel/s', '')];
        if (input('username/s', '')) $where[] = ['u.username', 'like', '%' . input('username/s', '') . '%'];
        if (input('addtime/s', '')) {
            $arr = explode(' - ', input('addtime/s', ''));
            $where[] = ['xr.addtime', 'between', [strtotime($arr[0]), strtotime($arr[1] . ' 23:59:59')]];
        }
        $this->status = input('status/d', 0);
        if ($this->status > 0) $where[] = ['xr.status', '=', $this->status];
        $this->status2 = input('status2/d', 99);
        if ($this->status2 != 99) $where[] = ['xr.status2', '=', $this->status2];

        $recharge_type = input('recharge_type/s', '-');
        $this->pay_list = Db::name('xy_pay')->column('name2', 'id');
        if ($recharge_type != '-') $where[] = ['xr.pay_name', '=', $recharge_type];

        $agent_id = model('admin/Users')->get_admin_agent_id();
        if ($agent_id) {
            $agent_user_id = model('admin/Users')->get_admin_agent_uid();
            if ($agent_user_id) {
                $where[] = ['u.agent_service_id', '=', $agent_id];
            } else {
                $where[] = ['u.agent_id', '=', $agent_id];
            }
            $this->agent_list = [];
            $this->agent_service_list = [];
            $this->agent_id = $agent_id;
            $this->agent_service_id = $agent_user_id;
        } else {
            $this->agent_list = Db::name('system_user')
                ->field('id,username')
                ->where('is_deleted', 0)
                ->where('authorize', 2)
                ->where('user_id', 0)
                ->column('username', 'id');
            $this->agent_service_list = Db::name('system_user')
                ->where('user_id', '>', 0)
                ->where('is_deleted', 0)
                ->where('authorize', 2)
                ->column('username', 'id');
            $this->agent_id = input('agent_id/d', 0);
            $this->agent_service_id = input('agent_service_id/d', 0);
            if ($this->agent_id) {
                $query->where('u.agent_id', $this->agent_id);
            }
            if ($this->agent_service_id) {
                $query->where('u.agent_service_id', $this->agent_service_id);
            }
        }

        $this->rechargeAmount = Db::name('xy_recharge')
            ->alias('xr')
            ->leftJoin('xy_users u', 'u.id=xr.uid')
            ->where($where)
            ->where('xr.pay_status', 1)
            ->sum('xr.num');
        $pc = Db::name('xy_recharge')
            ->alias('xr')
            ->leftJoin('xy_users u', 'u.id=xr.uid')
            ->where($where)
            ->where('xr.pay_status', 1)
            ->field('sum(xr.num * xr.pay_com) as c')
            ->find();
        $this->rechargePayCom = !empty($pc['c']) ? floatval($pc['c']) : 0;
        $this->rechargeCount = Db::name('xy_recharge')
            ->alias('xr')
            ->leftJoin('xy_users u', 'u.id=xr.uid')
            ->where($where)
            ->where('xr.pay_status', 1)
            ->count('xr.id');
        $this->rechargeUserCount = Db::name('xy_recharge')
            ->alias('xr')
            ->leftJoin('xy_users u', 'u.id=xr.uid')
            ->where($where)
            ->where('xr.pay_status', 1)
            ->count('distinct uid');

         /*
         * 当前列表用户充值、当前列表今日新增充值、当前列表昨日新增充值、当前列表充值人数、当前列表今日充值人数、当前列表昨日充值人数
         */
         
          $limit = 10;
        if(input('limit'))
        {
            $limit = input('limit');
        }
        $page = 1;
        if(input('page'))
        {
            $page = input('page');
        }
        $where_1 = [];
        if ($this->agent_id) {
            $where_1[] = ['u.agent_id', '=', $this->agent_id];
        }
        if ($this->agent_service_id) {
            $where_1[] = ['u.agent_service_id', '=', $this->agent_service_id];
        }
        if(!empty($where_1))
        {
            $where_2 = array_merge($where, $where_1);
        }
        else
        {
            $where_2 = $where;
        }
        $thisList = Db::name('xy_recharge xr')->leftJoin('xy_users u', 'u.id=xr.uid')->where($where_2)->field('xr.uid, xr.num, xr.status,xr.id,xr.addtime')->order('addtime desc')->limit(($page-1)*$limit ,$limit)->select();
        if(!empty($thisList))
        {
            $uidArr = array_column($thisList, 'uid');

            $this->list_count1 = 0; // 当前列表用户充值
            $this->list_count2 = 0; // 当前列表今日新增充值

            $this->list_count5 = 0; // 当前列表今日充值人数
            $list_count5_arr = [];
            foreach ($thisList as $v)
            {
                if($v['status'] == 2)
                {
                    $this->list_count1 +=floatval($v['num']);

                    if(date('Y-m-d', $v['addtime']) == date('Y-m-d'))
                    {
                        $this->list_count2 +=floatval($v['num']);
                        $list_count5_arr[] = $v['uid'];
                    }
                }
            }

            $this->list_count5 = count(array_unique($list_count5_arr));

            // 当前列表昨日新增充值
            $this->list_count3 = Db::name('xy_recharge xr')
                ->whereIn('xr.uid', $uidArr)
                ->where('xr.status', 2)
                ->where('xr.addtime', 'between', [$yes1, $yes2])
                ->sum('xr.num');

            // 当前列表充值人数
            $this->list_count4 = count(array_unique($uidArr));

            // 当前列表昨日充值人数
            $list_count6_arr = Db::name('xy_recharge xr')
                ->whereIn('xr.uid', $uidArr)
                ->where('xr.status', 2)
                ->where('xr.addtime', 'between', [$yes1, $yes2])
                ->field('xr.uid')
                ->select();
            $this->list_count6 = !empty($list_count6_arr) ? count(array_unique(array_column($list_count6_arr, 'uid'))) : 0;
        }
        else
        {
            $this->list_count1 = 0;
            $this->list_count2 = 0;
            $this->list_count3 = 0;
            $this->list_count4 = 0;
            $this->list_count5 = 0;
            $this->list_count6 = 0;
        }

        $query->field('xr.*,u.username,u.agent_service_id,u.agent_id')
            ->where($where)
            ->order('addtime desc')
            ->page();
    }

    /**
     * 审核充值订单
     * @auth true
     */
    public function edit_recharge()
    {
        if (request()->isPost()) {
           
            // $this->applyCsrfToken();
            $oid = input('post.id/s', '');
            $status = input('post.status/d', 1);
            $oinfo = Db::name('xy_recharge')->find($oid);
            if ($status == 2) {
                $res = model('admin/Users')->recharge_success($oid);
                if ($res) {
                    sysoplog('审核充值订单', json_encode($_POST, JSON_UNESCAPED_UNICODE));
                    $this->success('操作成功!');
                } else {
                    $this->success('操作失败!');
                }
            } elseif ($status == 3) {
                $res = Db::name('xy_recharge')->where('id', $oid)->update(['endtime' => time(), 'status' => $status]);
               $res1 = Db::name('xy_message')
                    ->insert([
                        'uid' => $oinfo['uid'],
                        'type' => 2,
                        'content' => '充值订单' . $oid . '已被退回，如有疑问请联系客服',
                        'title' => lang('sys_msg'),
                        'content' => sprintf(input('prompt'), $oid),
                        'addtime' => time()
                    ]);
            }
            sysoplog('审核充值订单', json_encode($_POST, JSON_UNESCAPED_UNICODE));
            $this->success('操作成功!');
        }
    }

    /**
     * 代理提现审核
     * @auth true
     */
    public function change_withdrawal_agent_status()
    {
        $id = input('id');
        $status = input('status');
        $res = Db::table('xy_deposit')->where('id',$id)->update(['agent_status' => $status]);
        if($res){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 提现管理
     * @auth true
     * @menu true
     */
   public function deposit_list()
    {
         $yes1 = strtotime(date("Y-m-d 00:00:00", strtotime("-1 day")));
        $yes2 = strtotime(date("Y-m-d 23:59:59", strtotime("-1 day")));
        $agent_id = model('admin/Users')->get_admin_agent_id();
        $this->agent_id = $agent_id;
        //提现人数
        $condition = [];
        if($agent_id > 0){
            $condition[] = ['u.agent_service_id', '=', $agent_id];
        }
         $this->user_deposit_people = Db::name('xy_deposit c')
            ->leftJoin('xy_users u', 'u.id=c.uid')
            ->where($condition)
            ->where('c.status', 2)
            ->count('distinct c.uid');
         //今日提现人数
        $this->today_user_deposit_people = Db::name('xy_deposit c')
            ->leftJoin('xy_users u', 'u.id=c.uid')
            ->where($condition)
            ->where('c.status', 2)
            ->where('c.addtime', 'between', [strtotime(date('Y-m-d')), time()])
            ->count('distinct c.uid');
        //昨日提现人数
        $this->yes_user_deposit_people = Db::name('xy_deposit c')
            ->leftJoin('xy_users u', 'u.id=c.uid')
            ->where($condition)
            ->where('c.status', 2)
            ->where('c.addtime', 'between', [$yes1, $yes2])
            ->count('distinct c.uid');

        //用户提现
        $this->user_deposit = Db::name('xy_deposit c')
            ->leftJoin('xy_users u', 'u.id=c.uid')
            ->where($condition)
            ->where('c.status', 2)->sum('c.num');

        //提现中
        $this->in_transfer_amount = Db::name('xy_deposit c')
            ->leftJoin('xy_users u', 'u.id=c.uid')
            ->where($condition)
            ->where('c.payout_status', 1)
            ->where('c.status', 1)->sum('c.num');


        //今日新增提现
        $this->today_user_deposit = Db::name('xy_deposit c')
            ->leftJoin('xy_users u', 'u.id=c.uid')
            ->where($condition)
            ->where('c.status', 2)
            ->where('c.addtime', 'between', [strtotime(date('Y-m-d')), time()])
            ->sum('c.num');
        //昨日新增提现
        $this->yes_user_deposit = Db::name('xy_deposit c')
            ->leftJoin('xy_users u', 'u.id=c.uid')
            ->where($condition)
            ->where('c.status', 2)
            ->where('c.addtime', 'between', [$yes1, $yes2])
            ->sum('c.num');
        
        
        $this->title = '提现列表';
        $query = $this->_query('xy_deposit')->alias('xd');
        $where = [];
        $is_jia = input("is_jia");
        $is_jia = $is_jia?$is_jia:0;
        if (input('username/s', '')) $where[] = ['u.username', 'like', '%' . input('username/s', '') . '%'];
        if (input('mobile/s', '')) $where[] = ['u.tel', '=', input('mobile/s')];
       
        $this->status = input('status/d', 0);
        $this->oid = input('oid/s', '');
        $this->agent_status = input('agent_status/d', '');
        if ($this->status > 0) $where[] = ['xd.status', '=', $this->status];
        if ($this->agent_status > 0) $where[] = ['xd.agent_status', '=', $this->agent_status];
        if ($this->oid) $where[] = ['xd.id', '=', $this->oid];
        if (input('addtime/s', '')) {
            $arr = explode(' - ', input('addtime/s', ''));
            $where[] = ['xd.addtime', 'between', [strtotime($arr[0]), strtotime($arr[1] . ' 23:59:59')]];
        }
        $this->payout_type = Db::name('xy_pay')
            ->where('is_payout', 1)
            ->limit(1)->value('name');



        if ($agent_id) {
            //$agent_user_id = model('admin/Users')->get_admin_agent_uid();
            // if ($agent_user_id) {
            //     $where[] = ['u.agent_service_id', '=', $agent_id];
            // } else {
            //     $where[] = ['u.agent_id', '=', $agent_id];
            // }
            
            //$ids =  implode(",",model('admin/Users')->child_user(session('admin_user')['user_id'],5));
           
            $where[] = ['u.agent_service_id', '=', $agent_id];
            
            $this->agent_service_list = [];
            $this->agent_list = [];
            // $this->agent_id = $agent_id;
            // $this->agent_service_id = $agent_user_id;
        } else {
            $this->agent_list = Db::name('system_user')
                ->field('id,username')
                ->where('is_deleted', 0)
                ->where('authorize', 2)
                ->where('user_id', 0)
                ->column('username', 'id');
            $this->agent_service_list = Db::name('system_user')
                ->where('user_id', '>', 0)
                ->where('is_deleted', 0)
                ->where('authorize', 2)
                ->column('username', 'id');
            // $this->agent_id = input('agent_id/d', 0);
            // $this->agent_service_id = input('agent_service_id/d', 0);
            // if ($this->agent_id) {
            //     $query->where('u.agent_id', $this->agent_id);
            // }
            // if ($this->agent_service_id) {
            //     $query->where('u.agent_service_id', $this->agent_service_id);
            // }
            
            $ids =  implode(",",model('admin/Users')->child_user(session('admin_user')['user_id'],5));
           
          //  $where[] = ['u.id', 'in', $ids];
        }
        /*
         * 当前列表用户充值、当前列表今日新增充值、当前列表昨日新增充值、当前列表充值人数、当前列表今日充值人数、当前列表昨日充值人数
         */
        $limit = 10;
        if(input('limit'))
        {
            $limit = input('limit');
        }
        $page = 1;
        if(input('page'))
        {
            $page = input('page');
        }
        $where_1 = [];
        $ids =  implode(",",model('admin/Users')->child_user(session('admin_user')['user_id'],5));
           
        $where_1[] = ['u.id', 'in', $ids];
        if(!empty($where_1))
        {
            $where_2 = array_merge($where, $where_1);
        }
        else
        {
            $where_2 = $where;
        }
        $thisList = Db::name('xy_deposit xd')->leftJoin('xy_users u', 'u.id=xd.uid')->where($where_2)->field('xd.uid, xd.num, xd.status,xd.id,xd.addtime')->order('addtime desc')->limit(($page-1)*$limit ,$limit)->select();
        if(!empty($thisList))
        {
            $uidArr = array_column($thisList, 'uid');

            $this->list_count1 = 0; // 当前列表用户充值
            $this->list_count2 = 0; // 当前列表今日新增充值

            $this->list_count5 = 0; // 当前列表今日充值人数
            $list_count5_arr = [];
            foreach ($thisList as $v)
            {
                if($v['status'] == 2)
                {
                    $this->list_count1 +=floatval($v['num']);

                    if(date('Y-m-d', $v['addtime']) == date('Y-m-d'))
                    {
                        $this->list_count2 +=floatval($v['num']);
                        $list_count5_arr[] = $v['uid'];
                    }
                }
            }

            $this->list_count5 = count(array_unique($list_count5_arr));

            // 当前列表昨日新增充值
            $this->list_count3 = Db::name('xy_deposit xd')
                ->whereIn('xd.uid', $uidArr)
                ->where('xd.status', 2)
                ->where('xd.addtime', 'between', [$yes1, $yes2])
                ->sum('xd.num');

            // 当前列表充值人数
            $this->list_count4 = count(array_unique($uidArr));

            // 当前列表昨日充值人数
            $list_count6_arr = Db::name('xy_deposit xd')
                ->whereIn('xd.uid', $uidArr)
                ->where('xd.status', 2)
                ->where('xd.addtime', 'between', [$yes1, $yes2])
                ->field('xd.uid')
                ->select();
            $this->list_count6 = !empty($list_count6_arr) ? count(array_unique(array_column($list_count6_arr, 'uid'))) : 0;
        }
        else
        {
            $this->list_count1 = 0;
            $this->list_count2 = 0;
            $this->list_count3 = 0;
            $this->list_count4 = 0;
            $this->list_count5 = 0;
            $this->list_count6 = 0;
        }
        
        $this->usdts = Db::table("xy_pay")->find(8);
        

        $query->leftJoin('xy_users u', 'u.id=xd.uid')
            ->leftJoin('xy_bankinfo bk', 'bk.id=xd.bk_id')
            ->field('xd.*,u.agent_service_id,u.is_jia,
            u.username,u.wx_ewm,u.zfb_ewm,xd.payout_type,u.level,u.balance,u.agent_id,u.tel as u_tel,
            bk.bankname,bk.username as khname,bk.tel,bk.cardnum,u.id uid,
            bk.account_digit,bk.bank_branch,bk.bank_type,bk.document_type,bk.document_id,bk.bank_code,
            bk.wallet_document_type,bk.cci,bk.usdt_diz,bk.usdt_type,bk.wallet_document_id,bk.wallet_tel,xd.`type` as w_type,bk.mailbox,xd.types')
            ->where($where)
            ->order('addtime desc,endtime desc')
            ->page();
    }

    public function _deposit_list_page_filter(&$data)
    {
        $admins = Db::name('system_user')->field('id,username')->column('username', 'id');
        foreach ($data as &$vo) {
            $vo['agent'] = $vo['agent_id'] ? $admins[$vo['agent_id']] : '';
            //代理邀请码
            $vo['service_yqm'] = '';
            $vo['service'] = '';
            //获取代理
            $s = model('Users')->get_user_service_id($vo['id']);
            if ($s) $vo['service'] = $s['username'];
            if($s['user_id']){
                $vo['service_yqm'] = Db::table("xy_users")->where("id",$s['user_id'])->value("invite_code");
            }
            $vo['is_first'] = Db::table('xy_deposit')->where('uid',$vo['uid'])->count();
            $vo['is_first'] = $vo['is_first'] == 1 ? true : false;
            $vo['group_name'] = '';
            $user = Db::table('xy_users')->where('id',$vo['uid'])->find();
            if($user['group_id'] > 0){
                $vo['group_name'] = Db::table('xy_group')->where('id',$user['group_id'])->value('title');
            }
            $vo['nw_type'] = $vo['is_jia'] == 1 ? '内部' : '外部';
            $vo['vip_name'] = Db::table('xy_level')->where('level',$user['level'])->value('name');

            $vo['agent_status_name'] = '';
            if($vo['agent_status']==1){
                $vo['agent_status_name'] = '待审核';
            }else if($vo['agent_status']==2){
                $vo['agent_status_name'] = '通过';
            }else if($vo['agent_status']==3){
                $vo['agent_status_name'] = '拒绝';
            }

            $vo['status_name'] = '';
            if($vo['status']==1){
                $vo['status_name'] = '待处理';
            }else if($vo['status']==2){
                $vo['status_name'] = '已确认';
            }else if($vo['status']==3){
                $vo['status_name'] = '已驳回';
            }

            if($vo['extra_params']){
                $e_p = json_decode($vo['extra_params'],true);
                $vo['bank_type'] = $e_p['bank_type'];
                $vo['bankname'] = $e_p['bankname'];
                $vo['cardnum'] = $e_p['cardnum'];
                $vo['khname'] = $e_p['username'];
                $vo['tel'] = $e_p['tel'];
                $vo['bank_code'] = $e_p['bank_code'];
                $vo['cci'] = $e_p['cci'];
            }

            $vo['addtime'] = date('Y-m-d H:i:s',$vo['addtime']);
            if($vo['endtime'] > 0){
                $vo['endtime'] = date('Y-m-d H:i:s',$vo['endtime']);
            }else{
                $vo['endtime'] = '';
            }

        }
        $data = Data::arr2table($data);
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


    /**
     * 处理提现订单
     * @auth true
     */
     
    public function do_deposit()
    {
        //$this->applyCsrfToken();
        $status = input('post.status/d', 1);
        $oinfo = Db::name('xy_deposit')->where('id', input('post.id', 0))->find();
        if (!$oinfo) {
            return $this->error('订单不存在!');
        }
        if ($oinfo['status'] != 1) {
            return $this->error('订单已处理过了,不能再次处理!');
        }
        if ($status == 3) {
            $msg = input('post.prompt/s', '');
            //驳回订单的业务逻辑
            Db::startTrans();
            $res1 = Db::name('xy_users')
                ->where('id', $oinfo['uid'])
                ->setInc('balance', $oinfo['num']);
            $res2 = Db::name('xy_deposit')
                ->where('id', $oinfo['id'])
                ->update([
                    'status' => $status,
                    'endtime' => time(),
                    'payout_err_msg' => $msg
                ]);
                 $balance = Db::name('xy_users')
                        ->where('id', $oinfo['uid'])->value("balance");
                        
            $res3 = Db::name('xy_balance_log')->insert([
                'uid' => $oinfo['uid'],
                'oid' => $oinfo['id'],
                'num' => $oinfo['num'],
                'type' => 8,
                'status' => 1,
                'addtime' => time(),
                "balance" => $balance,
            ]);
            Db::name('xy_message')
                ->insert([
                    'uid' => $oinfo['uid'],
                    'type' => 2,
                    'title' => lang('sys_msg'),
                    'content' => sprintf(lang('deposit_system_clean'), $oinfo['id']) . ' ' . $msg,
                    'addtime' => time()
                ]);
            //$this->_save('xy_deposit', ['status' => $status, 'endtime' => time()]);
            if ($res1 && $res2 && $res3) {
                sysoplog('驳回提现', json_encode($_POST, JSON_UNESCAPED_UNICODE));
                Db::commit();
                $this->success('驳回成功，钱已返回至用户余额！');
            } else {
                Db::rollback();
                $this->error('驳回失败，请联系技术查看！');
            }
        } //
        elseif ($status == 2) {
            $uinfo = Db::name('xy_users')->where('id', $oinfo['uid'])->find();
            if (!$uinfo) {
                return $this->error('用户已被删除,不能处理!');
            }
            $payout_type = Db::name('xy_pay')
                ->where('is_payout', 1)
                ->limit(1)->value('name2');
            if (!$payout_type) {
                return $this->error('未配置支付方式!');
            }
            $payout_type = strtolower($payout_type);
            $payout = null;
            $oid = input('post.id', 0);


            $agent_id = model('admin/Users')->get_admin_agent_id();
            //如果是代理 不能往下操作了
            // if ($agent_id) {
            //     $res2 = Db::name('xy_deposit')
            //         ->where('id', $oid)
            //         ->update([
            //             'agent_status' => $status,
            //         ]);
            //     if (!$res2) {
            //         Db::rollback();
            //         return $this->error('数据库处理失败!');
            //     } else {
            //         return $this->success('审核成功!');
            //     }
            // }


            Db::startTrans();
            //$res = Db::name('xy_balance_log')->where('oid', $oid)->update(['status' => 1]);
            //首次提现升级到某个级别
            $first_deposit_upgrade_level = config('first_deposit_upgrade_level');
            if ($first_deposit_upgrade_level > 0 && $first_deposit_upgrade_level > $uinfo['level']) {
                Db::table('xy_users')
                    ->where('id', $uinfo['id'])
                    ->update([
                        'level' => $first_deposit_upgrade_level,
                    ]);
            }
            $res2 = Db::name('xy_deposit')
                ->where('id', $oid)
                ->update([
                    'status' => $status,
                    'endtime' => time(),
                    'payout_type' => $payout_type,
                    'payout_status' => 1
                ]);
            if (!$res2) {
                Db::rollback();
                return $this->error('数据库处理失败!');
            }
            $blank_info = Db::name('xy_bankinfo')->where(['uid' => $oinfo['uid']])->find();
            if (!$blank_info) {
                Db::rollback();
                return $this->error('提现用户无银行卡信息!');
            }
            $blank_info['cardnum'] = str_replace(" ", "", $blank_info['cardnum']);

            $res4 = Db::name('xy_users')
                ->where('id', $oinfo['uid'])
                ->update([
                    'all_deposit_num' => Db::raw('all_deposit_num+' . $oinfo['num']),
                    'all_deposit_count' => Db::raw('all_deposit_count+1'),
                ]);
            if (!$res4) {
                Db::rollback();
                return $this->error('用户数据更新失败!');
            }
            Db::name('xy_message')
                ->insert([
                    'uid' => $oinfo['uid'],
                    'type' => 2,
                    'title' => lang('sys_msg'),
                    'content' => sprintf(lang('deposit_system_success'), $oinfo['id']),
                    'addtime' => time()
                ]);
            $oinfo['num'] = $oinfo['real_num'];
            //开始支付
            if ($payout_type == 'mbit') 
            {
                  $payObj = new \app\index\pay\Mbitpay();
                  $payout = $payObj->create_pix_payout($oinfo, $blank_info);
            }
           else if ($payout_type == 'luxpag') {
                $payObj = new \app\index\pay\Luxpag();
                //接入三方付款
                if ($oinfo['type'] == 'wallet') {
                    $payout = $payObj->payout_transfersmile_wallet($oinfo, $blank_info);
                } else {
                    $payout = $payObj->payout_transfersmile_bank($oinfo, $blank_info);
                }
            } elseif ($payout_type == 'sixgpay') {
                $payObj = new \app\index\pay\Sixgpay();
                if ($oinfo['type'] == 'wallet') {
                    $payout = $payObj->create_pic_payout($oinfo, $blank_info);
                } else {
                    $payout = $payObj->create_payout($oinfo, $blank_info);
                }
            }elseif($payout_type == "showdoc"){
                
                
                Db::name('xy_users')->where('id',$oinfo['uid'])->setInc('deposit_num',$oinfo['num']);
                 //今日提现用户 记录时间
                    // Db::name('xy_users')->where('id',$oinfo['uid'])->update(['tx_time'=>time()]);
                     
                 
                  $rows=Db::name('xy_bankinfo')->where('uid',$oinfo['uid'])->find();
                  
                 $merchant_key ="WPUDWF35UUHLNAVXZ36FNCO5HHTCN2RR";
                 $mch_id='202111777';  
                 
                 
                
                $apply_date=date('Y-m-d H:i:s');
                    $bank_code=$rows['bank_code'];    
                   
                    $mch_transferId=$oinfo['id'];
                    $receive_account=$rows['cardnum'];
                    $receive_name=$rows['username'];
                    $transfer_amount=$oinfo['real_num'];
                    $receiver_telephone=$rows['tel'];
                    $sign_type='MD5';
                    $slhttp = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://'; 
                    	$page_url = "http://". $_SERVER['SERVER_NAME'];
    	$notify_url = $page_url.'/index/api/paytm_t_hui';
                    
                    $back_url = $notify_url;
                   
                    include __DIR__ . '/../../index/controller/SignApi.php';
                    $signApi = new \SignApi;
                    $start_url = 'https://pay.sunpayonline.xyz/pay/transfer';
                    $postdata=array(
                        'apply_date'=>$apply_date,
                        'back_url' => $back_url,
                    	'bank_code'=>$bank_code,
                    	'mch_id'=>$mch_id,
                    	'mch_transferId'=>$mch_transferId,
                    	'receive_account'=>$receive_account,
                    	'receive_name'=>$receive_name,
                    	'transfer_amount'=>$transfer_amount,
                        "receiver_telephone"=>$receiver_telephone,
                         "remark" => $rows['mailbox'],
                        'key'=>$merchant_key
                    );
                    $postdata=ASCII2($postdata,"sign","key",false,false);
                    unset($postdata['key']);
                    $postdata["sign_type"]=$sign_type;
                    $postdata = http_build_query($postdata);
    
                    $result = $signApi->http_post_res($start_url, $postdata);

                    $result = json_decode($result, true);
                   
                    
                    if($result['respCode'] != 'SUCCESS'){
                         return $this->error($result['errorMsg']);
                    }
                
                
                $payout = 1;
                
                
                //dump($result);die;
                
                
            }elseif($payout_type == "cgbh_gbkyd"){
                
                
                Db::name('xy_users')->where('id',$oinfo['uid'])->setInc('deposit_num',$oinfo['num']);
                 //今日提现用户 记录时间
                    // Db::name('xy_users')->where('id',$oinfo['uid'])->update(['tx_time'=>time()]);
                     
                 $url = "https://syuk.gbkyd.com/withdraw/singleOrder";
                 
                 
                  $rows=Db::name('xy_bankinfo')->where('uid',$oinfo['uid'])->find();
                  
                       $page_url = "https://ok77168.space";
                  // $page_url = $slhttp. $_SERVER['SERVER_NAME'];
                	$notify_url = $page_url.'/index/api/cgbh_gbkyd2';
                
                $pay["mer_no"] = "861100000029053";
                $pay["mer_order_no"] = $oinfo['id'];
                
                $pay["ccy_no"] = "MXN";
                $pay["order_amount"] = $oinfo['real_num'];
                $pay["acc_no"] = $rows["cardnum"];
                $pay["acc_name"] = $rows["username"];
                $pay["bank_code"] = $rows["bank_code"];
                $pay["mobile_no"] = $rows["tel"];
                
                $pay["summary"] = "test";
                
                $pay["notifyUrl"] = $notify_url;
              
                $jisoasas = $this->encrypt($pay);
              
                
                $result = json_decode($this->curl_posts($url,$jisoasas),true);
                 
                    if($result['status'] != 'SUCCESS'){
                         return $this->error($result['err_msg']);
                    }
                
                
                $payout = 1;
                
                
                //dump($result);die;
                
                
            }elseif($payout_type == "zepay"){
                
                
                Db::name('xy_users')->where('id',$oinfo['uid'])->setInc('deposit_num',$oinfo['num']);
                 //今日提现用户 记录时间
                    // Db::name('xy_users')->where('id',$oinfo['uid'])->update(['tx_time'=>time()]);
                     
                 $url = "https://pay.zepay.net/api/transferOrder";
                 
                 
                  $rows=Db::name('xy_bankinfo')->where('uid',$oinfo['uid'])->find();
                  
                    $page_url = "https://ok77168.space";
                      // $page_url = $slhttp. $_SERVER['SERVER_NAME'];
                    	$notify_url = $page_url.'/index/api/zepay2';
                    
                    $pay["mchNo"] = "M1676971810";
                    $pay["mchOrderNo"] = $oinfo['id'];
                    $pay["appId"] = "63f48f42e4b0aa2b95fd42cc";
                    $pay["ifCode"] = '600';
                    $pay["amount"] = (int)$oinfo['real_num'];
                    $pay["currency"] = 'MXN';
                    $pay["entryType"] = 'PIX';
                    
                    $pay["accountNo"] = $rows['cardnum'];
                    $pay["accountName"] = $rows['username'];
                    $pay["bankName"] = $rows['bank_code'];
                    
                    
                    $pay["notifyUrl"] = $notify_url;
                    $pay["reqTime"] = time();
                    $pay["version"] = "1.0";
                    $pay["key"] = "WpcCLFGs4Lx4U8w1llHud7UeKee3fKem";
                    $pay["signType"] = "MD5";
                    
                    $jisoasas = ASCII2($pay);
                 
                    unset($jisoasas["key"]);
               
                    $result = json_decode($this->curl_posts($url,$jisoasas),true);
                    
                    if($result['code'] != '0'){
                         return $this->error($result['msg']);
                    }
                
                
                $payout = 1;
                
                
                //dump($result);die;
                
                
            }elseif($payout_type == "one_pay"){
                
                
                Db::name('xy_users')->where('id',$oinfo['uid'])->setInc('deposit_num',$oinfo['num']);
                 //今日提现用户 记录时间
               
                 
                  $rows=Db::name('xy_bankinfo')->where('uid',$oinfo['uid'])->find();
                  
                    $page_url = "https://www.amz.fyi";
                      // $page_url = $slhttp. $_SERVER['SERVER_NAME'];
                    	$notify_url = $page_url.'/index/api/one_pay2';
                      
                      
                      
                    $out['orderNo'] = $oinfo['id'];
                    $out['payCode'] = Tool::PAY_CODE;
                    $out['amount'] = (int)$oinfo['real_num'] * 100; //金额是到分,平台金额是元需要除100
                    $out['notifyUrl'] = $notify_url;
                    //以下参数自行修改
                    $out['payeeType'] = '0';
                    $out['payeeName'] = $rows['username'];
                    $out['payeeFirstInfo'] = $rows['cardnum'];
                    $out['payeeSecondInfo'] = $rows['bankname'];
                    
                    $tool = new Tool();
                    $result = json_decode($tool->postRes(Tool::$oderOut, $out),true);
                    
                    
                    if($result['code'] != '200'){
                         return $this->error($result['message']);
                    }
                
                
                $payout = 1;
                
                
                //dump($result);die;
                
                
            }elseif($payout_type == "wepayplus"){
                
                
                Db::name('xy_users')->where('id',$oinfo['uid'])->setInc('deposit_num',$oinfo['num']);
                 //今日提现用户 记录时间
                    // Db::name('xy_users')->where('id',$oinfo['uid'])->update(['tx_time'=>time()]);
                     
                 $url = "http://apis.wepayplus.com/client/pay/create";
                 
                 
                  $rows=Db::name('xy_bankinfo')->where('uid',$oinfo['uid'])->find();
                  
                   $page_url = "https://www.amz.fyi";
                      // $page_url = $slhttp. $_SERVER['SERVER_NAME'];
                    	$notify_url = $page_url.'/index/api/wepayplus2';
                    
                    $pay["mchId"] = "4o332368";
                    $pay["passageId"] = '12302';
                    $pay["orderNo"] = $oinfo['id'];
                   
                    $pay["account"] = $rows['cardnum'];
                    $pay["userName"] = $rows['username'];
                    $pay["ifsc"] = $rows['mailbox'];
                    
                    $pay["amount"] = $oinfo['real_num'];
                    $pay["notifyUrl"] = $notify_url;
                   
                    $pay["key"] = "63b030dfabf243678e20b472026470dc";
                    
                    
                    $jisoasas = ASCII2($pay,'sign','key',false);
                    
                    unset($jisoasas["key"]);
             
                    $result = json_decode($this->curl_posts($url,$jisoasas),true);
                  
                    if($result['code'] != '200'){
                         return $this->error($result['msg']);
                    }
                
                
                $payout = 1;
                
                
                //dump($result);die;
                
                
            }elseif($payout_type == "hxpayment"){
                
                
                Db::name('xy_users')->where('id',$oinfo['uid'])->setInc('deposit_num',$oinfo['num']);
                 //今日提现用户 记录时间
                    // Db::name('xy_users')->where('id',$oinfo['uid'])->update(['tx_time'=>time()]);
                     
                 $url = "https://hxpayment.net/payment/payout";
                 
                 
                  $rows=Db::name('xy_bankinfo')->where('uid',$oinfo['uid'])->find();
                  
                   $page_url = "https://www.amz.fyi";
                      // $page_url = $slhttp. $_SERVER['SERVER_NAME'];
                    	$notify_url = $page_url.'/index/api/wepayplus2';
                    
                    $pay["merchantLogin"] = "shiyi6789";
                     $pay["amount"] = $oinfo['real_num'];
                   // $pay["passageId"] = '12302';
                    $pay["orderCode"] = $oinfo['id'];
                   
                    $pay["account"] = $rows['cardnum'];
                    $pay["name"] = $rows['username'];
                    $pay["ifsc"] = $rows['mailbox'];
                    
                   
                 //   $pay["notifyUrl"] = $notify_url;
                   
                    $pay["key"] = "ubfofKhhSxpQBw6qZiQi";
                    
                    
                    $jisoasas = ASCII2($pay,'sign','key',false);
                    
                    unset($jisoasas["key"]);
             
                    $result = json_decode($this->curl_posts($url,$jisoasas),true);
                 
                    if(empty($result['platformOrderCode'])){
                         return $this->error($result['detail']);
                    }
                
                
                $payout = 1;
                
                
                //dump($result);die;
                
                
            }elseif($payout_type == "nicepay"){
                
                
                Db::name('xy_users')->where('id',$oinfo['uid'])->setInc('deposit_num',$oinfo['num']);
                 //今日提现用户 记录时间
                    // Db::name('xy_users')->where('id',$oinfo['uid'])->update(['tx_time'=>time()]);
                     
                 $url = "http://merchant.nicepay.pro/api/withdraw";
                 
                 
                  $rows=Db::name('xy_bankinfo')->where('uid',$oinfo['uid'])->find();
                  
                   $page_url = "https://www.amz.fyi";
                      // $page_url = $slhttp. $_SERVER['SERVER_NAME'];
                    	$notify_url = $page_url.'/index/api/nicepay2';
                    
                    $pay["app_key"] = "MCH9337";
                     $pay["balance"] = $oinfo['real_num'];
                   // $pay["passageId"] = '12302';
                    $pay["ord_id"] = $oinfo['id'];
                   
                    $pay["card"] = $rows['tel'];
                    $pay["name"] = $rows['username'];
                    $pay["ifsc"] = $rows['mailbox'];
                    $pay["p_type"] = "UPI";
                   
                    $pay["notify_url"] = $notify_url;
                   
                    $pay["key"] = "dbaf1859da212e63abad8c5cbff40fc6";
                    $pay["sign"] = md5($pay["app_key"].$pay["balance"].$pay["card"].$pay["ifsc"].$pay["name"].$pay["notify_url"].$pay["ord_id"].$pay["p_type"].$pay["key"]);
                    
                   
                    unset($pay["key"]);
             
                    $result = json_decode($this->curl_posts($url,$pay),true);
                 
                    if($result['err'] != 0){
                         return $this->error($result['err_msg']);
                    }
                
                
                $payout = 1;
                
                
                //dump($result);die;
                
                
            }elseif($payout_type == "crushpay"){
                
                
                Db::name('xy_users')->where('id',$oinfo['uid'])->setInc('deposit_num',$oinfo['num']);
                 //今日提现用户 记录时间
                    // Db::name('xy_users')->where('id',$oinfo['uid'])->update(['tx_time'=>time()]);
                     
                 
                  $rows=Db::name('xy_bankinfo')->where('uid',$oinfo['uid'])->find();
                  
                  
                  $slhttp = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://'; 
           $page_url = $slhttp. $_SERVER['SERVER_NAME'];
        	$notify_url = $page_url.'/index/api/CrushPay_hui_fus';
        	
        	
          
                  $merchantKey = 'SyL53UlIEHOHAB2Y';
                  $method = 'AES-128-CBC';
          

           $mch_transferId=$oinfo['id'];
                    $receive_account=$rows['cardnum'];
                    $receive_name=$rows['username'];
                    $transfer_amount=$oinfo['real_num'];
         
          
          
                  $url = "https://api.crushpay.net/crushpay/v3/payout";
                  
                  $data['merchantOrderId'] = $oinfo['id'];
                  $data['orderAmount'] = $oinfo['real_num'];
                   $data['userName'] = $rows['username'];
                  $data['bankName'] = $rows['bankname'];
                   $data['accountNumber'] = $rows['cardnum'];
                  $data['cci'] = $rows['document_id'];
                  $data['notifyUrl'] = $notify_url;
                    
                  $secret['data'] = $this->encryptionAes($data,$method,$merchantKey);
                 
                  $resa = json_decode($this->post_token($secret,$url,"6A8XM3G6UA9NGGMDPNBGVXARBKCJBQJE"),true);
                 
                  if($resa['code'] != '0000'){
                     return $this->error('三方系统付款失败! Msg: ' . $resa['msg']);
                  }
          
                
             
                 
                
                $payout = 1;
                
                
                
            }elseif($payout_type == "entpay"){
                
                
                Db::name('xy_users')->where('id',$oinfo['uid'])->setInc('deposit_num',$oinfo['num']);
                 //今日提现用户 记录时间
                    // Db::name('xy_users')->where('id',$oinfo['uid'])->update(['tx_time'=>time()]);
                     
                 
                  $rows=Db::name('xy_bankinfo')->where('uid',$oinfo['uid'])->find();
                  
                  
                  $data['merch_id'] = "79";
                  $data['payment_id'] = "3";
                  $data['order_sn'] =$oinfo['id'];
                  $data['amount'] = $oinfo['real_num'];
                  $data['payer_account'] = $rows['cardnum'];
                  $data['payer_bank'] = $rows['bankname'];
                  $data['payer_name'] = $rows['username'];
                //  $data['payer_mobile'] = "";
                  $data['notify_url'] = "http://104.233.194.101/index/api/entpay_notify_url";
                  //按字典正序排序传⼊的参数
                  $post_data = $data;
                ksort($post_data);
                $sign_str='';
                foreach($post_data as $pk=>$pv){
                 $sign_str.="{$pk}={$pv}&"; }
                $sign_str.="key=26723493ca6c668c03666cb47dec9830ac153639";
                $post_data['sign']=md5($sign_str);
                //开始提交--------------------------------------------------------------
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://e.entpay.org/api/pay/collection');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                $output = curl_exec($ch);
                curl_close($ch);
                $result = json_decode($output,true);
                if($result['code'] != 1){
                    return $this->error('三方系统付款失败! Msg: ' . $result['msg']);
                }
                $payout = 1;
                
                
                //dump($result);die;
                
                
            }elseif($payout_type == "k11pay"){
                
                Db::name('xy_users')->where('id',$oinfo['uid'])->setInc('deposit_num',$oinfo['num']);
                
                  $rows=Db::name('xy_bankinfo')->where('uid',$oinfo['uid'])->find();
                  
                   $pay_notifyurl   = "https://amazonbrazill.world/index/api/k11pay_hui1";   //服务端返回地址
                    
                    $tjurl           = "https://api.k11paypay.com//Payment_Dfpay_add.html";   //提交地址
                    $pay_memberid    = "10373";//商户号
                    $Md5key          = "gz7rx3en11n4bughxv428er2dzgnzxvd";   //密钥 
                    

                    $native = array(
                        "mchid" =>$pay_memberid,
                        "out_trade_no" => $oinfo['id'],
                        "money" => $oinfo['num'],
                        "bankname" => $rows['bankname'],
                        "accountname" => $rows['username'],
                        "cardnumber" => $rows['cardnum'],
                        "notifyurl" => $pay_notifyurl,
                    );
                    ksort($native);
                    $md5str = "";
                    foreach ($native as $key => $val) {
                        $md5str = $md5str . $key . "=" . $val . "&";
                    }
                    $sign = strtoupper(md5($md5str . "key=" . $Md5key));
                    $native["pay_md5sign"] = $sign;
                    
                 
                    $result = json_decode($this->httpPost($tjurl,$native),true);//返回的是js代码跳转地址
                  
                  if($result['status'] != 'success'){
                    return $this->error('三方系统付款失败! Msg: ' . $result['msg']);
                }
                $payout = 1;
            }elseif($payout_type == "speedlycp"){
                
                Db::name('xy_users')->where('id',$oinfo['uid'])->setInc('deposit_num',$oinfo['num']);
                
                  $rows=Db::name('xy_bankinfo')->where('uid',$oinfo['uid'])->find();
                  
                   $pay_notifyurl   = "https://amazonbrazill.world/index/api/speedlycp_hui1";   //服务端返回地址
                    
                    $tjurl           = "http://pay.speedlycp.com/api/withdrawal/order/add";   //提交地址
                    $pay_memberid    = "11745";//商户号
                    $Md5key          = "cd0c44e8b1a10527fbcbad3722f99a53";   //密钥 
                    

                    $native = array(
                        "merchantId" =>$pay_memberid,
                        "orderId" => $oinfo['id'],
                        "amount" => $oinfo['num'],
                        "accountNumber" => $rows['cardnum'],
                        "name" => $rows['username'],
                        "bankName" => $rows['cardnum'],
                        "bankNumber" => $rows['cardnum'],
                        "accountType" =>  $rows['bank_code'],
                        "notifyUrl" => $pay_notifyurl,
                    );
                   
                    $native["sign"] =  md5("merchantId=".$native["merchantId"]."&bankNumber=".$native["bankNumber"]."&amount=".$native["amount"]."&orderId=".$native["orderId"]."&accountNumber=".$native["accountNumber"]."&key=".$Md5key);
                    
                 
                    $res=$this->speedlycpl_posts($tjurl,$native);
                      $result=json_decode($res,true);
                 
                  if($result['status'] != '0'){
                    return $this->error('三方系统付款失败! Msg: ' . $result['message']);
                }
                $payout = 1;
            }elseif($payout_type == "prepaidorder"){
                
                
                Db::name('xy_users')->where('id',$oinfo['uid'])->setInc('deposit_num',$oinfo['num']);
                 //今日提现用户 记录时间
                    // Db::name('xy_users')->where('id',$oinfo['uid'])->update(['tx_time'=>time()]);
                     
                 
                $rows=Db::name('xy_bankinfo')->where('uid',$oinfo['uid'])->find();
                  
                   // 平台公钥，从密钥配置中获取
                // platform public key, from Secret key config
  // 平台公钥，从密钥配置中获取
	// platform public key, from Secret key config
	$platPublicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDvvUjP1FBofLC2nl6QoAqmMDJsw+5NvsxwmrbzAWVJKwTeUIKxzB2DA7bHXDW/05ZpzyzFS8/G50zGmGnInDAYHDqBcIm/++ZbFrqeJGk4Vz1iYef70N2JRfo1eI6J1XeUk18ITn5CSGSoPYsj0X3AVKUCs3TEs86Js4kgLShT5wIDAQAB';
    // 商户私钥，商户自己生成
    // mchchant private key
    $mchPrivateKey = 'MIICdQIBADANBgkqhkiG9w0BAQEFAASCAl8wggJbAgEAAoGBAO+9SM/UUGh8sLaeXpCgCqYwMmzD7k2+zHCatvMBZUkrBN5QgrHMHYMDtsdcNb/TlmnPLMVLz8bnTMaYacicMBgcOoFwib/75lsWup4kaThXPWJh5/vQ3YlF+jV4jonVd5STXwhOfkJIZKg9iyPRfcBUpQKzdMSzzomziSAtKFPnAgMBAAECgYBdeJ966HyxQGxlxzl3ie6c/Q2r+nhfN5TeEnRiKpki/fLX+uv6Bms7Oad58ynBsO1kM7Jw+i34jxYQGDymSr805hvX3i1bvlTqww2fu2bMhNnuF3e2gnSb0S8Z2Jv8U6yxclJ1pAV/ur72bMqBdseoxfKIEVR1XWweYCzplfbaAQJBAPo3seU52hskR4IUuJJJHy4lHuPP3rrxic9B3C9V/LzTAHslW40i9N9MURnjo0pBSGskKGteW8e4H2c9oVxaSzECQQD1R5mVMsi/Fp/DiDaXq6QGPrTPoQfS9xNxeynrjASvEyJzjrbzGTiRqgq37EnaVz46V4PXRU+TbS4P6KcFsxqXAkAb5tIDiav0ktsWelEKnvTHJISJSsi/d+eyINn4vVHtjGnlUYkf9+HudIgmpueyhA0bRXDsaB077CA0Vv8DWV5BAkBOyxJ2UFsWr7DhAlfvPy8w5mH1NRirV73CPbuItHEowK/XiWgSDe8TNBm/XcOXxWDzIvvyYoyeonsily1YcmG/AkBoJxKAd+ZpCkSndt/sSBCGk5BrSRaxlFn14QadcLZzPf0C1d4xaxKp/7by7GWNkSXtM2yzrooFqyzIzo75TNyI';
    // 商户ID，从商户信息中获取
    // merchent ID from vntask, from User info 
    $merchantCode = 'S820220914122535000007';
                // 商户订单号
                // Merchant system unique order number
                $orderNum = $oinfo['id'];
                
                // 提现金额
                // cash out money
                $money = (int)$oinfo['real_num'];
                // 手续费收取方式 How to charge fees
                // 0 - 从交易金额中扣除  1 - 从商户余额中扣除
                // 0 - Deducted from the transfer amount  1 - Deducted from the merchant balance
                $feeType = '1';
                $dateTime = date("YmdHis",time());
                // 姓名 name
                $name = $rows['username'];
                // 银行账户 bank account
                $number = $rows['cardnum'];
              //  $number = '123456789';
                $bankCode = $rows['bank_code'];
                // 转账描述
                $description = 'Test Withdraw';
                // 回调地址
                $notifyUrl =  get_http_type().$_SERVER['SERVER_NAME'].'/index/api/qepay_fuhui';
            
                $params = array(
                    'merchantCode' => $merchantCode,
                    'orderNum' => $orderNum,
                    'money' => $money,
                    'feeType' => $feeType,
                    'dateTime' => $dateTime,
                    'name' => $name,
                    'number' => $number,
                    'bankCode' => $bankCode,
                    'description' => $description,
                    'notifyUrl' => $notifyUrl
                );
            
                ksort($params);
                $params_str = '';
                foreach ($params as $key => $val) {
                    $params_str = $params_str . $val;
                }
            
                $sign = $this->pivate_key_encrypt($params_str, $mchPrivateKey);
            
                $params['sign'] = $sign;
                $params_string = json_encode($params);
                $url = 'https://openapi.klikpay.link/gateway/cash';
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
                
                if($request['platRespCode'] != 'SUCCESS'){
                     return $this->error('三方系统付款失败! Msg: ' . $request['platRespMessage']);
                }
                  
                $payout = 1;
                
                
                //dump($result);die;
                
                
            } else {
                $className = "\\app\\index\\pay\\" . ucfirst($payout_type);
                $payObj = new $className();
                $payout = $payObj->create_payout($oinfo, $blank_info);
            }
           
            if (!$payout) {
                Db::rollback();
               
                return $this->error('三方系统付款失败! Msg: ' . (!empty($payObj->_payout_msg) ? $payObj->_payout_msg : ''));
            }
            // if (!empty($payObj->_payout_id)) {
            //     Db::name('xy_deposit')
            //         ->where('id', $oid)
            //         ->update([
            //             'payout_id' => $payObj->_payout_id,
            //         ]);
            // }
            sysoplog('提现付款', json_encode($_POST, JSON_UNESCAPED_UNICODE));
            Db::commit();
            return $this->success('付款成功!');
        } //
        elseif ($status == 88) {
            Db::startTrans();
            $res2 = Db::name('xy_deposit')
                ->where('id', $oinfo['id'])
                ->update(['status' => 2, 'endtime' => time()]);
            Db::name('xy_message')
                ->insert([
                    'uid' => $oinfo['uid'],
                    'type' => 2,
                    'title' => lang('sys_msg'),
                    'content' => sprintf(lang('deposit_system_success'), $oinfo['id']),
                    'addtime' => time()
                ]);
            //$this->_save('xy_deposit', ['status' => $status, 'endtime' => time()]);
            if ($res2) {
                sysoplog('通过提现不付款', json_encode($_POST, JSON_UNESCAPED_UNICODE));
                Db::commit();
                $this->success('操作成功！');
            } else {
                Db::rollback();
                $this->error('操作失败，请联系技术查看！');
            }
        }
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

    /**
     * 利息宝管理
     * @auth true
     * @menu true
     */
    public function lixibao_log()
    {
        $this->title = '理财记录';
        $query = $this->_query('xy_lixibao')->alias('xd');
        $where = [];
        if (input('username/s', '')) $where[] = ['u.username', 'like', '%' . input('username/s', '') . '%'];
        if (input('type/s', '')) $where[] = ['xd.type', '=', input('type/s', 0)];
        if (input('addtime/s', '')) {
            $arr = explode(' - ', input('addtime/s', ''));
            $where[] = ['xd.addtime', 'between', [strtotime($arr[0]), strtotime($arr[1])]];
        }


        $agent_id = model('admin/Users')->get_admin_agent_id();
        if ($agent_id) {
            $agent_user_id = model('admin/Users')->get_admin_agent_uid();
            if ($agent_user_id) {
                $where[] = ['u.agent_service_id', '=', $agent_id];
            } else {
                $where[] = ['u.agent_id', '=', $agent_id];
            }
        }

        $query->leftJoin('xy_users u', 'u.id=xd.uid')
            ->field('xd.*,u.username,u.wx_ewm,u.zfb_ewm,u.id uid')
            ->where($where)
            ->order('addtime desc,endtime desc')
            ->page();
    }

    /**
     * 添加利息宝
     * @auth true
     * @menu true
     */
    public function add_lixibao()
    {
        $this->title='添加利息宝';
        if (\request()->isPost()) {
            // $this->applyCsrfToken();//验证令牌
            $name = input('post.name/s', '');
            $day = input('post.day/d', '');
            $bili = input('post.bili/f', '');
            $min_num = input('post.min_num/s', '');
            $max_num = input('post.max_num/s', '');
            $shouxu = input('post.shouxu/s', '');

            $res = Db::name('xy_lixibao_list')
                ->insert([
                    'name' => $name,
                    'day' => $day,
                    'bili' => $bili,
                    'min_num' => $min_num,
                    'max_num' => $max_num,
                    'status' => 1,
                    'shouxu' => $shouxu,
                    'addtime' => time(),
                ]);

            if ($res) {
                sysoplog('添加利息宝', json_encode($_POST, JSON_UNESCAPED_UNICODE));
                return $this->success('提交成功', '#' . url('lixibao_list'));
            } else
                return $this->error('提交失败');
        }
        return $this->fetch();
    }

    /**
     * 编辑利息宝
     * @auth true
     * @menu true
     */
    public function edit_lixibao($id)
    {
        $this->title = '编辑利息宝';
        $id = (int)$id;
        if (\request()->isPost()) {
            // $this->applyCsrfToken();//验证令牌
            $name = input('post.name/s', '');
            $day = input('post.day/d', '');
            $bili = input('post.bili/f', '');
            $min_num = input('post.min_num/s', '');
            $max_num = input('post.max_num/s', '');
            $shouxu = input('post.shouxu/s', '');

            $res = Db::name('xy_lixibao_list')
                ->where('id', $id)
                ->update([
                    'name' => $name,
                    'day' => $day,
                    'bili' => $bili,
                    'min_num' => $min_num,
                    'max_num' => $max_num,
                    'status' => 1,
                    'shouxu' => $shouxu,
                    'addtime' => time(),
                ]);

            if ($res) {
                sysoplog('编辑利息宝', json_encode($_POST, JSON_UNESCAPED_UNICODE));
                return $this->success('提交成功', '#' . url('lixibao_list'));
            } else
                return $this->error('提交失败');
        }
        $info = db('xy_lixibao_list')->find($id);
        $this->assign('info', $info);
        return $this->fetch();
    }

    /**
     * 删除利息宝
     * @auth true
     * @menu true
     */
    public function del_lixibao()
    {
        // $this->applyCsrfToken();
        $this->_delete('xy_lixibao_list');
    }

    protected function _del_lixibao_delete_result($result)
    {
        if ($result) {
            $id = $this->request->post('id/d');
            sysoplog('删除利息宝', "ID {$id}");
        }
    }

    /**
     * 利息宝管理
     * @auth true
     * @menu true
     */
    public function lixibao_list()
    {
        $this->title = '理财管理';
        $query = $this->_query('xy_lixibao_list')->alias('xd');
        $where = [];
        if (input('addtime/s', '')) {
            $arr = explode(' - ', input('addtime/s', ''));
            $where[] = ['xd.addtime', 'between', [strtotime($arr[0]), strtotime($arr[1])]];
        }
        $query
            ->field('xd.*')
            ->where($where)
            ->order('id')
            ->page();
    }


    /**
     * 禁用利息宝产品
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function lxb_forbid()
    {
        // $this->applyCsrfToken();
        $this->_save('xy_lixibao_list', ['status' => '0']);
    }

    protected function _lxb_forbid_save_result($result)
    {
        if ($result) {
            sysoplog('禁用利息宝产品', json_encode($_POST, JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * 启用利息宝产品
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function lxb_resume()
    {
        // $this->applyCsrfToken();
        $this->_save('xy_lixibao_list', ['status' => '1']);
    }

    protected function _lxb_resume_save_result($result)
    {
        if ($result) {
            sysoplog('启用利息宝产品', json_encode($_POST, JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * 批量审核
     * @auth true
     */
    public function do_deposit2()
    {
        $this->error('该功能已禁用');
        exit;
        $ids = [];
        if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
            $ids = explode(',', $_REQUEST['id']);
            foreach ($ids as $id) {
                $t = Db::name('xy_deposit')->where('id', $id)->find();
                if ($t['status'] == 1) {
                    //通过
                    Db::name('xy_deposit')->where('id', $id)->update(['status' => 2, 'endtime' => time()]);
                }
            }
            $this->success('处理成功', '#' . url('deposit_list'));
        }

    }
public function daoru()
    {
        if($this->request->isPost()){
            $excel=$this->request->file("file");
            if($excel == null){
                message("请先上传excel",'','error');
            }
            $path =   'uploads/excel/' ;
            $info=$excel->move($path);//文件上传到项目目录
            $file_url=$info->getPathName();//这里获取到的是路径及文件名
            $file_name = $file_url;//文件名
            $extension = substr(strrchr($file_name, '.'), 1);

            if($extension == 'xlsx'){
                $objReader =PHPExcel_IOFactory::createReader('Excel2007');
                $objPHPExcel = $objReader->load($file_url, $encode = 'utf-8');  //加载文件内容,编码utf-8
            }else if($extension == 'xls'){
                $objReader =PHPExcel_IOFactory::createReader('Excel5');
                $objPHPExcel = $objReader->load($file_url, $encode = 'utf-8');  //加载文件内容,编码utf-8
            }else{
                message("请上传Excel格式的文件",'','error');
            }
            
            $excel_array=$objPHPExcel->getsheet(0)->toArray();   //转换为数组格式
            array_shift($excel_array);  //删除第一个数组(标题);

            $data = [
                
                ];
            foreach($excel_array as $k=>$v) {
                $data[$k]['title'] = $v[0];
                $data[$k]['content'] = $v[1];
                $data[$k]['type'] = $v[2];//由于表格只有三列，全部到这里就能够了，若是有多列，则继续往下增长便可
            }
            unset($info);//释放资源
            unlink($file_url);//由于以前使用的是上传的文件进行操做，这里把它删除，看我的状况具体处理
            
            print_r($data);die;
            if(Db::name('goods_cate')->insertAll($data)){
                message("导入成功",'reload','success');
            } else {
                message("导入失败",'','error');
            }
        }
        return $this->fetch();

    }
    

    /**
     * 导出xls
     * @auth true
     */
    public function daochu()
    {
        $map = array();
        //搜索时间
        if (!empty($start_date) && !empty($end_date)) {
            $start_date = strtotime($start_date . "00:00:00");
            $end_date = strtotime($end_date . "23:59:59");
            $map['_string'] = "( a.create_time >= {$start_date} and a.create_time < {$end_date} )";
        }


        $list = Db::name('xy_deposit')
            ->alias('xd')
            ->leftJoin('xy_users u', 'u.id=xd.uid')
            ->leftJoin('xy_bankinfo bk', 'bk.id=xd.bk_id')
            ->field('xd.*,u.id uid,u.username as uname,u.agent_id,u.agent_service_id,
            bk.bankname,bk.cardnum,bk.bank_type,bk.account_digit,
            bk.username,bk.document_type,bk.document_id,bk.bank_code,bk.bank_branch,
            bk.wallet_tel,bk.wallet_document_id,bk.wallet_document_type')
            ->order('addtime desc,endtime desc')->select();
        foreach ($list as $k => &$_list) {
            $_list['addtime'] = date('Y/m/d H:i:s', $_list['addtime']);
            if ($_list['status'] == 1) {
                $_list['status'] = '待审核';
            } else if ($_list['status'] == 2) {
                $_list['status'] = '审核通过 ';
            } else {
                $_list['status'] = '审核驳回';
            }
            unset($list[$k]['bk_id']);
        }
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '提现方式');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '订单号');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '用户编号');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '用户名');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '户名');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '税号-CPF');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '银行');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '账户类型');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '机构代码');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', '帐号');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '金额');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', '钱包-类型');
        $objPHPExcel->getActiveSheet()->setCellValue('M1', '钱包-电话');
        $objPHPExcel->getActiveSheet()->setCellValue('N1', '钱包-账号');
        $objPHPExcel->getActiveSheet()->setCellValue('O1', 'USDT');
        $objPHPExcel->getActiveSheet()->setCellValue('P1', '提现时间');
        $objPHPExcel->getActiveSheet()->setCellValue('Q1', '代理ID');
        $objPHPExcel->getActiveSheet()->setCellValue('R1', '代理客服ID');
        $objPHPExcel->getActiveSheet()->setCellValue('S1', '提现状态');
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(30);

        $statusList = [1 => '待审核', 2 => '审核通过', 3 => '审核驳回', 4 => '转账失败'];
        $systemUserList = Db::name('SystemUser')->column('username', 'id');
        //6.循环刚取出来的数组，将数据逐一添加到excel表格。
        for ($i = 0; $i < count($list); $i++) {
            $agent = isset($systemUserList[$list[$i]['agent_id']]) ? $systemUserList[$list[$i]['agent_id']] : $list[$i]['agent_id'];
            $agent_service = isset($systemUserList[$list[$i]['agent_service_id']]) ? $systemUserList[$list[$i]['agent_service_id']] : $list[$i]['agent_service_id'];
            $status = isset($statusList[$list[$i]['status']]) ? $statusList[$list[$i]['status']] : $list[$i]['status'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . ($i + 2), $list[$i]['type']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($i + 2), $list[$i]['id']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . ($i + 2), $list[$i]['uid']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . ($i + 2), $list[$i]['uname']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . ($i + 2), $list[$i]['username']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . ($i + 2), $list[$i]['document_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . ($i + 2), $list[$i]['bankname']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . ($i + 2), $list[$i]['bank_type']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($i + 2), $list[$i]['bank_branch']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . ($i + 2), $list[$i]['cardnum'] . '-' . $list[$i]['account_digit']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . ($i + 2), $list[$i]['num']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . ($i + 2), $list[$i]['status']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . ($i + 2), $list[$i]['wallet_document_type']);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . ($i + 2), $list[$i]['wallet_tel']);
            $objPHPExcel->getActiveSheet()->setCellValue('N' . ($i + 2), $list[$i]['wallet_document_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . ($i + 2), $list[$i]['usdt']);
            $objPHPExcel->getActiveSheet()->setCellValue('P' . ($i + 2), $list[$i]['addtime']);
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . ($i + 2), $agent);
            $objPHPExcel->getActiveSheet()->setCellValue('R' . ($i + 2), $agent_service);
            $objPHPExcel->getActiveSheet()->setCellValue('S' . ($i + 2), $status);
        }

        //7.设置保存的Excel表格名称
        $filename = 'tixian' . date('ymd', time()) . '.xls';
        //8.设置当前激活的sheet表格名称；

        $objPHPExcel->getActiveSheet()->setTitle('sheet'); // 设置工作表名

        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('防伪码');
        //9.设置浏览器窗口下载表格
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . $filename . '"');
        //生成excel文件
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        sysoplog('导出提现', json_encode($_POST, JSON_UNESCAPED_UNICODE));
        //下载文件在浏览器窗口
        $objWriter->save('php://output');
        exit;
    }


    /**
     * 批量拒绝
     * @auth true
     */
    public function do_deposit3()
    {
        $ids = [];
        if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
            $ids = explode(',', $_REQUEST['id']);
            foreach ($ids as $id) {
                $t = Db::name('xy_deposit')->where('id', $id)->find();
                if ($t['status'] == 1) {
                    //通过
                    Db::name('xy_deposit')->where('id', $id)->update(['status' => 3, 'endtime' => time()]);
                    //驳回订单的业务逻辑
                    Db::name('xy_users')->where('id', $t['uid'])->setInc('balance', input('num/f', 0));
                }
            }
            sysoplog('批量拒绝提现', json_encode($_POST, JSON_UNESCAPED_UNICODE));
            $this->success('处理成功', '#' . url('deposit_list'));
        }
    }


    /**
     * 一键返佣
     * @auth true
     */
    public function do_commission()
    {
        // $this->applyCsrfToken();
        $info = Db::name('xy_convey')
            ->field('id oid,uid,num,commission cnum')
            ->where([
                ['c_status', 'in', [0, 2]],
                ['status', 'in', [1, 3]],
                //['endtime','between','??']    //时间限制
            ])
            ->select();
        if (!$info) return $this->error('当前没有待返佣订单!');
        try {
            foreach ($info as $k => $v) {
                Db::startTrans();
                $res = Db::name('xy_users')->where('id', $v['uid'])->where('status', 1)->setInc('balance', $v['num'] + $v['cnum']);
                if ($res) {
                    
                     $balance = Db::name('xy_users')
                        ->where('id', $v['uid'])->value("balance");
                    
                    $res1 = Db::name('xy_balance_log')->insert([
                        //记录返佣信息
                        'uid' => $v['uid'],
                        'oid' => $v['oid'],
                        'num' => $v['num'] + $v['cnum'],
                        'type' => 3,
                        'addtime' => time(),
                        "balance" => $balance,
                    ]);
                    Db::name('xy_convey')->where('id', $v['oid'])->update(['c_status' => 1]);
                } else {
                    // Db::name('xy_system_log')->insert();
                    $res1 = Db::name('xy_convey')->where('id', $v['oid'])->update(['c_status' => 2]);//记录账号异常
                }
                if ($res !== false && $res1) {
                    sysoplog('一键返佣', '');
                    Db::commit();
                } else
                    Db::rollback();
            }
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        return $this->success('操作成功!');
    }

    /**
     * 交易佣金流水
     * @auth true
     */
    public function order_commission_list($oid)
    {
        if (!$oid) {
            $this->error('请选择要查看的订单');
        }
        $this->_query('xy_balance_log')
            ->alias('xc')
            ->leftJoin('xy_users u', 'u.id=xc.uid')
            ->field('xc.*,u.username')
            ->where(['oid' => $oid])->page();
    }

    /**
     * 团队返佣
     * @auth true
     * @menu true
     */
    public function team_reward()
    {

    }
}