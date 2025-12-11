<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | www.xydai.cn 新源代网
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// |

// +----------------------------------------------------------------------

namespace app\admin\controller;

use library\tools\Data;
use think\Db;
use PHPExcel;

//tp5.1用法
use PHPExcel_IOFactory;

/**
 * 会员管理
 * Class Users
 * @package app\admin\controller
 */
class Users extends Base
{

    /**
     * 指定当前数据表
     * @var string
     */
    protected $table = 'xy_users';

    /**
     * 添加用户
     * @auth true
     */
    public function create_user()
    {
        if(request()->isPost()){
            if(cache('create_user_'.session('admin_user')['id'])){
                return $this->success('操作成功');
            }
            cache('create_user_'.session('admin_user')['id'],1,3);
            $tel = input('post.tel/s', '');
            $username = input('post.username/s', '');
            $pwd = input('post.pwd/s', '');
            $pwd2 = input('post.pwd2/s', '');
            $invite_code = input('post.invite_code', 0);

            $agent_service_id = input('agent_service_id',0);
            $level = input('level',"");
            $telegram = input('telegram',"");
            $whatsapp = input('whatsapp',"");
            $email = input('email',"");
            $status = input('status',0);
            $withdrawal_status = input('withdrawal_status',0);
            $remark = input('remark',"");
            $ip = request()->ip();
            $params = [
                'agent_service_id' => $agent_service_id,
                'level' => $level,
                'telegram' => $telegram,
                'whatsapp' => $whatsapp,
                'email' => $email,
                'status' => $status,
                'withdrawal_status' => $withdrawal_status,
                'remark' => $remark,
                'is_jia' => 1,
            ];

            $parent_id = 0;
            if($invite_code){
                $parent_id = Db::name('xy_users')
                    ->where('invite_code', $invite_code)
                    ->value('id');
                if(empty($parent_id)){
                    return $this->error('邀请码错误');
                }
            }

            $res = model('Users')->add_users($tel, $username, $pwd, $parent_id, '', $pwd2, '',$ip,'',$params);
            if ($res['code'] !== 0) {
                return $this->error($res['info']);
            }
            sysoplog('添加新用户', json_encode($_POST, JSON_UNESCAPED_UNICODE));
            return $this->success('操作成功');
        }
        $this->agent_services = Db::name('system_user')
            ->where('authorize', "2")
            ->field('id,username')
            ->where('is_deleted', 0)
            ->select();
        $this->levels = Db::table('xy_level')->where('status',1)->select();
        return $this->fetch();
    }

    /**
     * 批量添加用户
     * @auth true
     */
    public function batch_create_user()
    {
        if (request()->isPost()) {
            if(cache('batch_create_user_'.session('admin_user')['id'])){
                return $this->success('操作成功');
            }
            cache('batch_create_user_'.session('admin_user')['id'],1,3);
            $data = [
                'pwd'=>input('pwd', ''),
                'pwd2'=>input('pwd2', ''),
                'level'=>input('level', ''),
                'agent_service_id'=>input('agent_service_id', 0),
            ];
            $data2 = [
                'bank_type'=>input('bank_type', ''),
                'bankname'=>input('bankname', ''),
                'cardnum'=>input('cardnum', ''),
                'username'=>input('username', ''),
                'tel'=>input('bank_tel', ''),
            ];

            if(empty(input('tel'))){
                return $this->error('登录名不能为空');
            }
            if(empty($data['pwd'])){
                return $this->error('密码不能为空');
            }
            if(empty($data['pwd2'])){
                return $this->error('取款密码不能为空');
            }
            if(empty($data2['bank_type']) || empty($data2['bankname']) || empty($data2['username']) || empty($data2['cardnum']) || empty($data2['tel'])){
                return $this->error('银行信息不完整');
            }
            $userModel = new \app\admin\model\Users();
            $ip = request()->ip();
            foreach (explode(',',input('tel')) as $v) {
                $params = [
                    'level'=>input('level', ''),
                    'agent_service_id'=>input('agent_service_id', ''),
                    'is_jia' => 1,
                ];
                $res = $userModel->add_users($v, $v, $data['pwd'], 0,  '', $data['pwd2'],  0, $ip,'',$params);
                if($res['code'] == 0){
                    $uid = $res['id'];
                    $bank = [
                        'bank_type'=>input('bank_type', ''),
                        'bankname'=>input('bankname', ''),
                        'cardnum'=>input('cardnum', ''),
                        'username'=>input('username', ''),
                        'tel'=>input('bank_tel', ''),
                        'uid'=>$uid,
                    ];
                    $is_add = Db::table('xy_bankinfo')->where('uid','=', $uid)->whereNull('usdt_diz')->find();
                    if(!$is_add){
                        Db::table('xy_bankinfo')->insert($bank);
                    }
                }else{
                    return $this->error($res['info']);
                }
            }
            return $this->success('操作成功');
        }
        $this->agent_services = Db::name('system_user')
            ->where('authorize', "2")
            ->field('id,username')
            ->where('is_deleted', 0)
            ->select();
        $this->levels = Db::table('xy_level')->where('status',1)->select();
        return $this->fetch();
    }

    /**
     * 会员列表
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException$this->_query($this->table)
     */
    public function index()
    {
        $this->title = '会员列表';
        $query = $this->_query($this->table)->alias('u');
        $where = [];
        $this->is_jia = input("is_jia","");
        if ($this->is_jia !== "") {
            $where[] = ['u.is_jia', '=', $this->is_jia];
        }
//        dump(input());exit;
        $this->online = input("online",0);
        if($this->online){
            $online_ids = Db::table('xy_token')
                ->where('time','>=',time() - (7 * 24 * 60 * 60))
                ->group('uid')
                ->column('uid');
            $where[] = ['u.id', 'in', $online_ids];
        }

        $this->all_children = input("all_children",0);
        if($this->all_children){
            $where[] = ['u.parent_id', '>', 0];
        }
            
        if (input('tel/s', '')) $where[] = ['u.tel', 'like', '%' . input('tel/s', '') . '%'];

        $this->parent_tel = input('parent_tel', '');
        if ($this->parent_tel){
            $pid = Db::table('xy_users')
                ->where('invite_code',$this->parent_tel)
                ->whereOr('tel',$this->parent_tel)
                ->whereOr('username',$this->parent_tel)
                ->value('id');
            $where[] = ['u.parent_id', '=', $pid];
        }

        $this->register_ip = input('register_ip', '');
        if ($this->register_ip){
            $where[] = ['u.register_ip', 'like', "{$this->register_ip}%"];
        }

        //内部代理或邀请码
        $this->internal = input('internal', '');
        if ($this->internal){
            $internal_arr = [];
            //内部用户账号id
            $sys_user_id = Db::name('system_user')
                ->where('authorize', "2")
                ->field('id')
                ->where('username',$this->internal)
                ->whereOr('invite_code',$this->internal)
                ->where('is_deleted', 0)
                ->value('id');
            $where[] = ['u.agent_service_id','=',$sys_user_id];
        }
        
        if (input('invite_code/s', '')) $where[] = ['u.invite_code', '=', input('invite_code/s')];
        if (input('ip/s', '')) $where[] = ['u.ip', '=', input('ip/s')];
        if (input('uid/s', '')) $where[] = ['u.id', '=', input('uid/s')];
        
        
        if (input('username/s', '')) $where[] = ['u.username', 'like', '%' . input('username/s', '') . '%'];

        if (input('addtime/s', '')) {
            $arr = explode(' - ', input('addtime/s', ''));
            $where[] = ['u.addtime', 'between', [strtotime($arr[0]), strtotime($arr[1] . ' 23:59:59')]];
        }
        $this->order = input('order/s', '');
        switch ($this->order) {
            case "recharge":
                $order = 'u.all_recharge_num desc';
                break;
            case "recharge_count":
                $order = 'u.all_recharge_count desc';
                break;
            case "deposit":
                $order = 'u.all_deposit_num desc';
                break;
            case "deposit_count":
                $order = 'u.all_deposit_count desc';
                break;
            default:
                $order = 'u.id desc';
                break;
        }
        $this->level = input('level', "");
        $this->group_id = input('group_id', -1);
        if ($this->level !== "") $where[] = ['u.level', '=', $this->level];
        if ($this->group_id != -1) $where[] = ['u.group_id', '=', $this->group_id];
        $this->level_list = Db::name('xy_level')->field('level,name')->select();
        $this->groupList = Db::table('xy_group')
            ->where('agent_id', 'in', [$this->agent_id, 0])
            ->whereOr('is_share', 1)
            ->field('id,title')
            ->column('title', 'id');
        $this->groupAllList = Db::table('xy_group')
            ->field('id,title')
            ->column('title', 'id');


        $this->agent_service_id = input('agent_service_id/d', 0);
        if ($this->agent_id) {
            //$ids =  implode(",",model('admin/Users')->child_user(session('admin_user')['user_id'],5));
           
            //$where[] = ['u.id', 'in', $ids];
            $where[] = ['u.agent_service_id', '=', $this->agent_id];

            $this->agent_list = [];
            $this->agent_id = $this->agent_id;
            $this->agent_service_list = Db::name('system_user')
                ->where('parent_id', $this->agent_id)
                ->where('authorize', "2")
                ->field('id,username')
                ->where('is_deleted', 0)
                ->column('username', 'id');
            if ($this->agent_service_list &&
                $this->agent_service_id &&
                !array_key_exists($this->agent_service_id, $this->agent_service_list)) {
                $this->agent_service_id = 0;
            }
        } else {
            $this->agent_list = Db::name('system_user')
                ->where('user_id', 0)
                ->where('authorize', "2")
                ->field('id,username')
                ->where('is_deleted', 0)
                ->column('username', 'id');
            $this->agent_service_list = Db::name('system_user')
                ->where('user_id', '>', 0)
                ->where('authorize', "2")
                ->field('id,username')
                ->where('is_deleted', 0)
                ->column('username', 'id');
            $this->agent_id = input('agent_id/d', 0);

            if ($this->agent_id) {
                $query->where('u.agent_id', $this->agent_id);
            }
        }
        if ($this->agent_service_id) {
            $query->where('u.agent_service_id', $this->agent_service_id);
        }
        
        
        
        $query->field('u.id,u.level,u.agent_service_id,u.agent_id,u.tel,u.username,u.group_id,le.name as level_name,u.freeze_amount,
        u.lixibao_balance,u.id_status,u.ip,u.is_jia,u.addtime,u.invite_code,u.register_ip,u.login_status,u.withdrawal_status,
        u.all_recharge_num,u.all_deposit_num,u.all_recharge_count,u.all_deposit_count,
        u.freeze_balance,u.status,u.balance,u1.username as parent_name,u1.tel as parent_tel,u1.invite_code as parent_invite_code,u.login_time,u.deal_time,u.lottery_money,u.shuadan_status')
            ->leftJoin('xy_users u1', 'u.parent_id=u1.id')
            ->leftJoin('xy_level le', 'u.level=le.level')
            ->where($where)
            ->order($order)
            ->page();
    }

    /**
     * 表单数据处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function _index_page_filter(&$data)
    {
        $admins = Db::name('system_user')->field('id,username')->column('username', 'id');
        $today_start = strtotime(date('Y-m-d') . ' 00:00:00');
        foreach ($data as &$vo) {
            $vo['agent'] = $vo['agent_id'] ? $admins[$vo['agent_id']] : '';
            //代理名称
            $vo['service'] = '';
            //代理邀请码
            $vo['service_yqm'] = '';

//            //获取代理
//            $s = model('Users')->get_user_service_id($vo['id']);
//            if ($s) $vo['service'] = $s['username'];
//            if($s['user_id']){
//                $vo['service_yqm'] = Db::table("xy_users")->where("id",$s['user_id'])->value("invite_code");
//            }
            $vo['service'] = '';
            $vo['service_yqm'] = '';
            $sys_user = Db::name('system_user')
                ->where('authorize', "2")
                ->field('id,username,invite_code')
                ->where('id',$vo['agent_service_id'])
                ->where('is_deleted', 0)
                ->find();
            if(!empty($sys_user)){
                $vo['service'] = $sys_user['username'];
                $vo['service_yqm'] = $sys_user['invite_code'];
            }


            $vo['register_time'] = date('Y-m-d H:i:s', $vo['addtime']);
            
            $vo['com'] = Db::name('xy_balance_log')->where('uid', $vo['id'])
                ->where('type', 3)->where('status', 1)->sum('num');
            $vo['tj_com'] = Db::name('xy_balance_log')->where('uid', $vo['id'])
                ->where('type', 6)->where('status', 1)->sum('num');
            $vo['day_d_count'] = Db::name('xy_convey')->where('uid',$vo['id'])->where("qkon = 1")->where('status','in',[0,1,3,5])->count('id');
            $vo['order_num'] = Db::name('xy_convey')->where('uid',$vo['id'])->count('id'); 
            $vo['order_incomplete_num'] = Db::name('xy_convey')->where('uid',$vo['id'])->where('status','in',[0,2,4,5])->count('id');  


            //抢单冻结金额
            $vo['order_freeze_balance'] = Db::name('xy_convey')->where('uid',$vo['id'])->where('c_status',0)->sum(Db::raw('num + commission'));
            //今日已抢单
            $vo['today_order_grabbing_num'] = Db::name('xy_convey')->where('uid',$vo['id'])->where('addtime','>=',$today_start)->count('id');
            //今日已抢单成功次数
            $vo['today_success_count'] = Db::name('xy_convey')->whereIn('status',[1,3,5])->where('uid',$vo['id'])->where('addtime','>=',$today_start)->count('id');

            //今日收益
            $vo['today_income'] = Db::name('xy_convey')->where('uid',$vo['id'])->where('c_status',1)->where('addtime','>=',$today_start)->sum(Db::raw('num + commission'));
            //今日收益次数
            $vo['today_income_count'] = Db::name('xy_convey')->where('uid',$vo['id'])->where('c_status',1)->where('addtime','>=',$today_start)->count('id');

            //累计收益
            $vo['sum_income'] = Db::name('xy_convey')->where('uid',$vo['id'])->where('c_status',1)->sum(Db::raw('num + commission'));
            //累计次数
            $vo['sum_income_count'] = Db::name('xy_convey')->where('uid',$vo['id'])->where('c_status',1)->count('id');

            //昨日收益
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $yesterdayStart = strtotime($yesterday . ' 00:00:00');
            $yesterdayEnd = strtotime($yesterday . ' 23:59:59');
            $vo['sum_yesterday_income'] = Db::name('xy_convey')->where('uid',$vo['id'])->where('c_status',1)->where('addtime', 'between', [$yesterdayStart, $yesterdayEnd])->sum(Db::raw('num + commission'));

            //累计推广佣金
            $vo['promotion_income'] = Db::name('xy_convey')->where('parent_uid',$vo['id'])->where('c_status',1)->sum(Db::raw('parent_commission'));

            //日提款次数
            $vo['today_withdrawal_count'] = Db::name('xy_deposit')->where('uid',$vo['id'])->where('status', 2)->count('id');
            //日提款金额
            $vo['today_withdrawal_sum'] = Db::name('xy_deposit')->where('uid',$vo['id'])->where('status', 2)->sum('num');

            //日充值
            $vo['today_recharge_sum'] = Db::name('xy_recharge')->where('uid',$vo['id'])->where('status', 2)->sum('num');
            //日在线充值
            $vo['today_onlin_recharge_sum'] = 0;

            //总充值
            $vo['recharge_sum'] = Db::name('xy_recharge')->where('status', 2)->sum('num');
            //总在线充值
            $vo['online_recharge_sum'] = 0;
            //总在线充值次数
            $vo['online_recharge_count'] = 0;

            //总赠送
            $vo['give_amount_sum'] = Db::name('xy_balance_log')->where('uid', $vo['id'])->where('type', 34)->where('status', 1)->sum('num');

            //余额宝收益
            $vo['lixbao_income'] = Db::name('xy_balance_log')->where('uid', $vo['id'])->where('status', 1)->where('type', 23)->sum('num');

        }
        $data = Data::arr2table($data);
    }

    /**
     * 设置单控
     * @auth true
     */
    public function set_single_control()
    {
        if(request()->isPost()){
            $id = input('id');
            $uid = input('uid');
            $save = [
                'uid'=>input('uid'),
                'single_control_status'=>input('single_control_status/d',0),
                'fixed_order_num'=>input('fixed_order_num/d',0),
                'fixed_commission_bili'=>input('fixed_commission_bili',0),
                'enable_order_num'=>input('enable_order_num/d',0),
                'fixed_order_amount'=>input('fixed_order_amount',0),
                'order_min_bili'=>input('order_min_bili',0),
                'order_max_bili'=>input('order_max_bili',0),
            ];
//            if($save['single_control_status'] == 1){
//                if($save['fixed_order_num'] < 1 || $save['fixed_commission_bili'] <= 0){
//                    $save['single_control_status'] = 0;
//                }
//            }
//            if($save['enable_order_num'] >= 1){
//                if($save['fixed_order_amount'] <= 0 && ($save['order_min_bili'] <= 0 || $save['order_max_bili'] <= 0 )){
//                    $save['enable_order_num'] = 0;
//                }
//            }
            Db::startTrans();
            try {
                Db::name('xy_single_control')->where('uid', $uid)->where('id',$id)->update($save);
                Db::name('xy_users')->where('id', $uid)->update(['shuadan_status'=>input('shuadan_status',0)]);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return $this->error($e->getMessage());
            }
            return $this->success('设置成功');
        }
        $id = input('get.uid',0);
        $this->single_control = Db::name('xy_single_control')->where('uid', $id)->find();
        $this->user = Db::name('xy_users')->where('id', $id)->find();
        return $this->fetch();
    }

    /**
     * 下线
     * @auth true
     */
    public function offline()
    {
        $id = input('get.uid',0);
        $res = Db::name('xy_token')->where('uid', $id)->order('id','desc')->delete();
        $this->success('下线成功');
    }

    /**
     * 帐变
     * @auth true
     */
    public function change_user_balance()
    {
        $id = input('id',0);
        $this->ids = input('ids',"");
        if(!$id && !$this->ids) $this->error('参数错误');
        $user = Db::table($this->table)->find($id);

        if(request()->isPost()){
            if(cache('change_user_balance_'.session('admin_user')['id'])){
                return $this->success('操作成功');
            }
            cache('change_user_balance_'.session('admin_user')['id'],1,3);
            $balance = input('balance');
            $type = input('type');
            $user_remark = input('user_remark');
            $remark = input('remark');
            if(empty($balance)){
                $this->error('金额不能为空！');
            }

            $add_type = [1,32,33,34,36];//1=用户充值，32=后台充值，33=注册奖励,34=免费赠送，36=解冻本金
            $sub_type = [31,35];//31=手动扣款,35=冻结本金
            if(!in_array($type, array_merge($sub_type, $add_type))) {
                $this->error('账单类型错误！');
            }

            if($id){
                $where_ids = [$id];
            }else{
                $where_ids = explode(',',$this->ids);
            }
            foreach ($where_ids as $v) {
                $user = Db::table($this->table)->find($v);
                if(in_array($type,$sub_type)) {
                    if($user['balance'] < $balance) {
                        $this->error('id='.$v.'的用户余额不足！');
                    }
                    $status = 2;
                }else{
                    $status = 1;
                }

                Db::startTrans();
                try {
                    if(in_array($type,$add_type)){
                        Db::table('xy_users')->where('id',$v)->setInc('balance',$balance);
                    }else{
                        Db::table('xy_users')->where('id',$v)->setDec('balance',$balance);
                    }
                    //冻结本金:减去余额后，增加冻结本金的额度
                    if($type == 35){
                        Db::table('xy_users')->where('id',$v)->setInc('freeze_amount',$balance);
                    }
                    //解冻本金:增加余额额度后，减去冻结本金的额度
                    if($type == 36){
                        Db::table('xy_users')->where('id',$v)->setDec('freeze_amount',$balance);
                    }

                    Db::table('xy_balance_log')->insert([
                        'uid'=>$v,
                        'sid'=>0,
                        'oid'=>getSn('XGYE'),//修改余额
                        'num'=>$balance,
                        'type'=>$type,
                        'status'=>$status,//收入1 支出2
                        'addtime'=>time(),
                        'remark'=>$remark,
                        'user_remark'=>$user_remark,//用户端备注
                    ]);
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }

            }

            $this->success('操作成功！');
        }
        $this->user = $user;
        return $this->fetch();
    }


    /**
     * 会员等级列表
     * @auth true
     * @menu true
     */
    public function level()
    {
        $this->title = '会员等级';
        $this->_query('xy_level')->order('sort','asc')->order('id','asc')->page();
    }

    /**
     * 用户提现
     * @auth true
     */
    public function user_withdrawal()
    {
        $page = input('page/d', 1);
        $limit = input('limit/d', 10);
        $where = [];
        $is_jia = input("is_jia","");
        $this->username = input("username","");
        $this->mobile = input("mobile","");

        if (input('username/s', '')) $where[] = ['u.username', 'like', '%' . input('username/s', '') . '%'];
        if (input('mobile/s', '')) $where[] = ['u.tel', '=', input('mobile/s')];
        $this->status = input('status/d', 0);
        $this->oid = input('oid/s', '');
        $this->agent_status = input('agent_status/d', '');
        if ($this->status > 0) $where[] = ['tx.status', '=', $this->status];
        if ($this->agent_status > 0) $where[] = ['tx.agent_status', '=', $this->agent_status];
        if ($this->oid) $where[] = ['tx.id', '=', $this->oid];
        $uid = input('uid','');
        if($uid){
            $where[] = ['u.id','=', $uid];
        }
        if($this->username !== ""){
            $where[] = ['bk.username','=',$this->username];
        }
//        if($this->mobile !== ""){
//            $where[] = ['bk.tel','=',$this->mobile];
//        }
        if($is_jia !== ""){
            $where[] = ['u.is_jia','=', $is_jia];
        }
        if (input('addtime/s', '')) {
            $arr = explode(' - ', input('addtime/s', ''));
            $where[] = ['tx.addtime', 'between', [strtotime($arr[0]), strtotime($arr[1] . ' 23:59:59')]];
        }
        $agent_id = model('admin/Users')->get_admin_agent_id();
        if ($agent_id) {
            $ids =  implode(",",model('admin/Users')->child_user(session('admin_user')['user_id'],5));
            $where[] = ['u.id', 'in', $ids];
            $this->agent_service_list = [];
            $this->agent_list = [];
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
        }

        $where_1 = [];
        $ids =  implode(",",model('admin/Users')->child_user(session('admin_user')['user_id'],5));
        $where_1[] = ['u.id', 'in', $ids];
        if(!empty($where_1)){
            $where_2 = array_merge($where, $where_1);
        }else{
            $where_2 = $where;
        }

        $data = Db::name('xy_deposit tx')
            ->leftJoin('xy_users u', 'u.id=tx.uid')
            ->leftJoin('xy_bankinfo bk', 'tx.bk_id=bk.id')
            ->where($where)
            ->field('tx.*,u.agent_service_id,u.is_jia,
                u.username,u.wx_ewm,u.zfb_ewm,tx.payout_type,u.level,u.balance,u.agent_id,u.tel as u_tel,
                bk.bankname,bk.username as khname,bk.tel,bk.cardnum,u.id uid,
                bk.account_digit,bk.bank_branch,bk.bank_type,bk.document_type,bk.document_id,
                bk.wallet_document_type,bk.usdt_diz,bk.usdt_type,bk.wallet_document_id,bk.bank_code,
                bk.wallet_tel,tx.`type` as w_type,bk.mailbox,tx.types')
            ->order('addtime desc')
            ->page($page,$limit)
            ->select();
        if(!empty($data)){
            foreach ($data as $k => &$v){
                $v['addtime'] = date('Y-m-d H:i:s', $v['addtime']);
                $v['endtime'] = date('Y-m-d H:i:s', $v['endtime']);
            }
        }
        unset($v);
        $count = Db::name('xy_deposit tx')
            ->leftJoin('xy_users u', 'u.id=tx.uid')
            ->leftJoin('xy_bankinfo bk', 'bk.id=tx.bk_id')
            ->where($where)
            ->count();
        return json(['code' => 0, 'count' => $count, 'info' => '请求成功', 'data' => $data, 'other' => $limit]);
    }


    /**
     * 账变
     * @auth true
     */
    public function caiwu()
    {
        $this->title = '帐变记录';
        if(input("id")){
            $uid = input('get.id/d', 1);
            $this->uid = $uid;
            $this->uinfo = db('xy_users')->find($uid);
        }else{
            $this->uid = -1;
        }
        
        //
        $where = [];
        if (isset($_REQUEST['iasjax'])) {
            $page = input('get.page/d', 1);
            $num = input('get.num/d', 10);
            $level = input('get.level/d', 1);
            $limit = ((($page - 1) * $num) . ',' . $num);

            if ($level == 1) {
              
                if(input("id") && input("id") != '-1'){
                  
                    $where[] = ['uid', '=', $uid];
                }
            }
            $type = input('type', 0);
            if ($type != 0) {
                $where[] = ['type', '=', $type != -1 ? $type : 0];
            }
            if (input('addtime/s', '')) {
                $arr = explode(' - ', input('addtime/s', ''));
                $where[] = ['addtime', 'between', [strtotime($arr[0]), strtotime($arr[1])]];
            }
           
            $count = $data = db('xy_balance_log')->where($where)->count('id');
            
            //$ids =  implode(",",model('admin/Users')->child_user(session('admin_user')['user_id'],5));
           if($this->agent_id){
               $ids = Db::table('xy_users')->where('agent_service_id',$this->agent_id)->column('id');
               $where[] = ['uid', 'in', $ids];
           }
            //$where[] = ['uid', 'in', $ids];
            
            $data = db('xy_balance_log')
                ->where($where)
                ->order('id desc')
                ->page(input('page',1),input("limit",10))
                ->select();
           
            if ($data) {
                foreach ($data as &$datum) {
                    $datum['tel'] = Db::table("xy_users")->where("id",$datum['uid'])->value("tel");
                    $datum['addtime'] = date('Y/m/d H:i', $datum['addtime']);;
                    
                   
                    
                   if($datum['status'] == 1){
                       $jins = $datum['balance'] + $datum["num"];
                       $zhi11 = "<span style='color:rgb(33,166,59)'>{$jins}</span>";
                   }else{
                       $jins = $datum['balance'] - $datum["num"];
                       $zhi11 = "<span style='color:rgb(240,54,65)'>{$jins}</span>";
                   }
                    $datum['zhou'] = $zhi11;
                    
//                    switch ($datum['type']) {
//                        case 0:
//                            $text = '<span class="layui-btn layui-btn-sm layui-btn-danger">系统</span>';
//                            break;
//                        case 1:
//                            $text = '<span class="layui-btn layui-btn-sm layui-btn-warm">充值</span>';
//                            break;
//                        case 2:
//                            $text = '<span class="layui-btn layui-btn-sm layui-btn-danger">交易</span>';
//                            break;
//                        case 3:
//                            $text = '<span class="layui-btn layui-btn-sm layui-btn-normal">返佣</span>';
//                            break;
//                        case 4:
//                            $text = '<span class="layui-btn layui-btn-sm ">强制交易</span>';
//                            break;
//                        case 5:
//                            $text = '<span class="layui-btn layui-btn-sm layui-btn-danger">推广返佣</span>';
//                            break;
//                        case 6:
//                            $text = '<span class="layui-btn layui-btn-sm layui-btn-normal">下级交易返佣</span>';
//                            break;
//                        case 7:
//                            $text = '<span class="layui-btn layui-btn-sm layui-btn-danger">提现</span>';
//                            break;
//                        case 8:
//                            $text = '<span class="layui-btn layui-btn-sm layui-btn-danger">提现驳回</span>';
//                            break;
//                        case 21:
//                            $text = '<span class="layui-btn layui-btn-sm layui-btn-danger">利息宝入</span>';
//                            break;
//                        case 22:
//                            $text = '<span class="layui-btn layui-btn-sm layui-btn-danger">利息宝出</span>';
//                            break;
//                        case 23:
//                            $text = '<span class="layui-btn layui-btn-sm layui-btn-danger">利息宝返佣</span>';
//                            break;
//                        default:
//                            $text = '其他';
//                    }
                    $text = '<span class="layui-btn layui-btn-sm layui-btn-info">'.bl_type($datum['type']).'</span>';
                    $datum['type'] = $text;
                    $datum['type'] = $text;
                    if ($datum['status'] == 1) $datum['status'] = '收入';
                    elseif ($datum['status'] == 2) $datum['status'] = '支出';
                    else $datum['status'] = '未知';

                }
            }

            if (!$data) json(['code' => 1, 'info' => '暂无数据']);
            return json(['code' => 0, 'count' => $count, 'info' => '请求成功', 'data' => $data, 'other' => $limit]);
        }


        $this->rechagreCount = Db::name('xy_balance_log');
        //   if(input("id")){
        //       $this->rechagreCount = $this->rechagreCount->where('uid', $uid);
        //   }
        $this->depositCount = Db::name('xy_balance_log');

        if($this->agent_id){
            $ids = Db::table('xy_users')->where('agent_service_id',$this->agent_id)->column('id');
            $this->rechagreCount = $this->rechagreCount
                ->where('status', 1)
                ->where('uid','in',$ids)
                ->sum('num');

            if(input("id")){
                $this->depositCount = $this->depositCount->where('uid', $uid);
            }
            $this->depositCount = $this->depositCount
                ->where('status', 2)
                ->where('uid','in',$ids)
                ->sum('num');
        }else{
            $this->rechagreCount = $this->rechagreCount
                ->where('status', 1)
                ->sum('num');

            if(input("id")){
                $this->depositCount = $this->depositCount->where('uid', $uid);
            }
            $this->depositCount = $this->depositCount->where('status', 2)
                ->sum('num');

        }

        return $this->fetch();
    }

    /**
     * 添加会员等级
     * @auth true
     * @menu true
     */
    public function add_level()
    {
        if (request()->isPost()) {
            $data = [
                'pic'=>input('pic',''),
                'name'=>input('name',''),
                'level'=>input('level',''),
                'num'=>input('num',''),
                'balance_min_amount'=>input('balance_min_amount',''),
                'balance_max_amount'=>input('balance_max_amount',''),
                'tixian_min'=>input('tixian_min',10),
                'tixian_max'=>input('tixian_max',''),
                'day_withdraw_num'=>input('day_withdraw_num',1),
                'day_withdraw_free_num'=>input('day_withdraw_free_num',1),
                'withdraw_fixed_fee'=>input('withdraw_fixed_fee',0),
                'tixian_shouxu'=>input('tixian_shouxu',0),
                'tixian_nim_order'=>input('tixian_nim_order',0),
                'grab_order_min_amount'=>input('grab_order_min_amount',0),
                'grab_order_max_amount'=>input('grab_order_max_amount',''),
                'num_min'=>input('num_min',10),
                'order_num'=>input('order_num',0),
                'grab_order_fixed_commission'=>input('grab_order_fixed_commission',0),
                'bili'=>input('bili',0),
                'credit'=>input('credit',0),
                'auto_buy_finance'=>input('auto_buy_finance',0),
                'lixibao_id'=>input('lixibao_id',''),
                'expire_day'=>input('expire_day',0),
                'promotion_commisssion'=>input('promotion_commisssion',0),
                'expire_return_principal'=>input('expire_return_principal',0),
                'sort'=>input('sort',0),
                'show_status'=>input('show_status',0),
                'status'=>input('status',0),
            ];
            $data['tixian_shouxu'] = $data['tixian_shouxu'] / 100;
            $data['bili'] = $data['bili'] / 100;
            $data['addtime'] = date('Y-m-d H:i:s',time());
            if($data['grab_order_max_amount'] < $data['grab_order_min_amount']){
                return $this->error('【抢单最高金额】必须大于等于【抢单最低金额】');
            }
            if($data['grab_order_min_amount'] < 1){
                return $this->error('【抢单最低金额】必须大于等于1');
            }
            if($data['day_withdraw_num'] < 1){
                return $this->error('【提款次数/天】必须大于等于1');
            }
            if($data['order_num'] < 1){
                return $this->error('【抢单次数限制】必须大于等于1');
            }
            if($data['auto_buy_finance'] === 'on'){
                if(empty($data['lixibao_id'])){
                    $data['auto_buy_finance'] = 0;
                }else{
                    $data['auto_buy_finance'] = 1;
                }
            }
            if($data['promotion_commisssion'] === 'on'){
                $data['promotion_commisssion'] = 1;
            }
            if($data['expire_return_principal'] === 'on'){
                $data['expire_return_principal'] = 1;
            }
            if($data['show_status'] === 'on'){
                $data['show_status'] = 1;
            }
            if($data['status'] === 'on'){
                $data['status'] = 1;
            }
            if(empty($data['pic'])){
                return $this->error('图标不能为空');
            }
            if(empty($data['name'])){
                return $this->error('名称不能为空');
            }
            if($data['level'] === ''){
                return $this->error('等级值不能为空');
            }
            if($data['num'] === ''){
                return $this->error('升级价格不能为空');
            }
            if(Db::table("xy_level")->where('level',$data['level'])->field('id')->find()){
                return $this->error('等级值已存在');
            }
            $res = Db::table("xy_level")->insert($data);
            if($res){
                return $this->success('添加等级成功');
            }
            return $this->error('添加等级失败');
        }
        $this->list = Db::name('xy_lixibao_list')->where('status',1)->select();
        return $this->fetch();
    }


    /**
     * 查看会员
     * @auth true
     */
    public function user_data()
    {
        $this->title = '查看会员';
        $uid = input('uid');
        if (request()->isPost()) {
            $data = input();
            $uid = input('uid');
            unset($data['uid']);
            $data['withdrawal_status'] = input('withdrawal_status',0);
            $data['status'] = input('status',0);
            if(empty($data['pwd'])){
                unset($data['pwd']);
            }else{
                $salt = rand(0, 99999); //生成盐
                $data['pwd'] = sha1($data['pwd'] . $salt . config('pwd_str'));
                $data['salt'] = $salt;
            }
            if(empty($data['pwd2'])){
                unset($data['pwd2']);
            }else{
                $salt2 = rand(0, 99999); //生成盐
                $data['pwd2'] = sha1($data['pwd2'] . $salt2 . config('pwd_str'));
                $data['salt2'] = $salt2;
            }
            $user_level = Db::table('xy_users')->where('id', $uid)->value('level');
            $res = Db::table('xy_users')->where('id', $uid)->update($data);
            if($res){
                if($user_level != input('level')){
                    Db::table("xy_convey")
                        ->where("uid",$uid)
                        ->where('qkon','<>',2)
                        ->update(['qkon'=>2]);
                }
                return $this->success('修改成功');
            }
            return $this->error('修改失败');
        }
        $this->user = Db::name('xy_users')->where('id', $uid)->find();
        $this->levels = Db::name('xy_level')->where('status',1)->select();
        $this->user['login_ip_address'] = '';
        if($this->user['ip']){
            $ip_region = new \Ip2Region();
            $result = $ip_region->btreeSearch($this->user['ip']);
            $ips = isset($result['region']) ? $result['region'] : '';
            $this->user['login_ip_address'] = str_replace(['内网IP', '0', '|'], '', $ips);
        }
        //代理名称
        $this->user['service'] = '';
        //代理邀请码
        $this->user['service_yqm'] = '';

        //获取代理
        $s = model('Users')->get_user_service_id($uid);
        if ($s) $this->user['service'] = $s['username'];

        $this->depositCount = Db::name('xy_balance_log')->where('uid', $uid)->where('status', 2)->sum('num');
        $this->rechagreCount = Db::name('xy_balance_log')->where('uid', $uid)->where('status', 1)->sum('num');

        //支付成功
        $this->recharge_success_amount = Db::name('xy_recharge')->where('status', 2)->where('uid', $uid)->sum('num');
        $this->shouxu_amount = 0;

        $this->pay_list = Db::name('xy_pay')->column('name2', 'id');

        //转账中
        $this->in_transfer_amount = Db::name('xy_deposit')->where('payout_status', 1)->where('uid', $uid)->sum('num');
        $this->withdrawal_success_amount = Db::name('xy_deposit')->where('status', 2)->where('uid', $uid)->sum('num');

        $agent_id = model('admin/Users')->get_admin_agent_id();
        if ($agent_id) {
            $this->agent_list = [];
            $this->agent_service_list = [];
        }else{
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
        }
        return $this->fetch();
    }

    /**
     * 用户银行账户
     * @auth true
     */
    public function user_bank_account()
    {
        $where = [];
        $uid = input('uid','');
        $bank_type = input('bank_type','');
        if($uid){
            $where[] = ['uid','=', $uid];
        }

        $limit = 10;
        if(input('limit')){
            $limit = input('limit');
        }
        $page = 1;
        if(input('page')){
            $page = input('page');
        }

        $agent_id = model('admin/Users')->get_admin_agent_id();
        if ($agent_id) {
            $ids =  implode(",",model('admin/Users')->child_user(session('admin_user')['user_id'],5));
            $where[] = ['uid', 'in', $ids];
        }

        $data = Db::table('xy_bankinfo')->where($where);
        $count = Db::table('xy_bankinfo')->where($where);
        //usdt查询
        if($bank_type == 'tron'){
            $data = $data->whereNotNull('usdt_diz');
            $count = $count->whereNotNull('usdt_diz');
        }else{
            //银行卡查询
            $data = $data->whereNull('usdt_diz');
            $count = $count->whereNull('usdt_diz');
        }

        $data = $data->page($page,$limit)->select();

        unset($v);

        $count = $count->count();

        return json(['code' => 0, 'count' => $count, 'info' => '请求成功', 'data' => $data, 'other' => $limit]);
    }

    /**
     * 添加银行账号
     * @auth true
     */
    public function add_bank()
    {
        $this->uid = input('uid');
        $this->bank_info = '';
        $this->b_id = input('b_id');
        if($this->b_id){
            $this->bank_info = Db::table('xy_bankinfo')->where('id', $this->b_id)->find();
            $this->uid = $this->bank_info['uid'];
        }
        if (request()->isPost()) {
            if(empty($this->b_id)){
                $bank_count = Db::table('xy_bankinfo')->where('uid','=', $this->uid)->whereNull('usdt_diz')->count();
                $user_bank_num = sysconf('user_bank_num') ?: 1;
                if($bank_count >= $user_bank_num){
                    $this->error("银行账户限制{$user_bank_num}个");
                }
            }

            $data = input();
            $data['addtime'] = time();
            unset($data['spm']);
            unset($data['open_type']);
            unset($data['id']);
            unset($data['b_id']);
            //更新银行卡
            if($this->b_id){
                $res = Db::table('xy_bankinfo')->where('id', $this->b_id)->update($data);
            }else{
                $res = Db::table('xy_bankinfo')->insert($data);
            }
            if($res){
                return $this->success('操作成功');
            }
            return $this->error('操作失败');
        }
        $this->bank_list = getBankList();
        return $this->fetch();
    }

    /**
     * 删除银行卡
     * @auth true
     */
    public function del_bank(){
        $bid = input('bid');
        $res = Db::table('xy_bankinfo')->where('id', $bid)->delete();
        if($res){
            return json(['code' => 0, 'info' => '操作成功']);
        }else{
            return json(['code' => 1, 'info' => '操作失败']);
        }
    }

    /**
     * 用户充值
     * @auth true
     */
    public function user_recharge()
    {
        $this->agent_service_id = input('agent_service_id/d', 0);
        $query = Db::table('xy_recharge')
            ->alias('xr')
            ->leftJoin('xy_users u', 'u.id=xr.uid');
        $is_jia = input("is_jia");
        $is_jia = $is_jia?$is_jia:0;
        $where = [];
        $uid = input('uid','');
        if($uid){
            $where[] = ['u.id','=', $uid];
        }
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
        if ($recharge_type != '-') $where[] = ['xr.pay_name', '=', $recharge_type];

        $agent_id = model('admin/Users')->get_admin_agent_id();
        if ($agent_id) {
            $ids =  implode(",",model('admin/Users')->child_user(session('admin_user')['user_id'],5));
            $where[] = ['u.id', 'in', $ids];
        }
        $limit = 10;
        if(input('limit')){
            $limit = input('limit');
        }
        $page = 1;
        if(input('page')){
            $page = input('page');
        }

        $data = $query->field('xr.*,u.tel,u.username,u.agent_service_id,u.agent_id,u.balance')
            ->where($where)
            ->order('addtime desc')
            ->page($page,$limit)
            ->select();
        if(!empty($data)){
            foreach ($data as $k => &$v){
                $v['addtime'] = date('Y-m-d H:i:s', $v['addtime']);
                //状态 1下单成功 2充值成功 3充值失败
                switch ($v['status']){
                    case 1:
                        $v['status'] = '<span class="layui-badge layui-bg-green">下单成功</span>';
                        break;
                    case 2:
                        $v['status'] = '<span class="layui-badge layui-bg-green">充值成功</span>';
                        break;
                    case 3:
                        $v['status'] = '<span class="layui-badge layui-bg-green">充值失败</span>';
                        break;
                }
                $v['service'] = '';
                //获取代理
                $s = model('Users')->get_user_service_id($uid);
                if ($s) $v['service'] = $s['username'];
            }
        }
        unset($v);

        $count = Db::table('xy_recharge')
            ->alias('xr')
            ->leftJoin('xy_users u', 'u.id=xr.uid')
            ->where($where)
            ->count();

        return json(['code' => 0, 'count' => $count, 'info' => '请求成功', 'data' => $data, 'other' => $limit]);
    }

    /**
     * 添加会员
     * @auth true
     * @menu true
     */
    public function add_users()
    {
        if (request()->isPost()) {
            $tel = input('post.tel/s', '');
            $user_name = input('post.user_name/s', '');
            $pwd = input('post.pwd/s', '');
            $parent_id = input('post.parent_id/d', 0);
            $token = input('__token__', 1);
            $agent_id = $this->agent_id;
            $res = model('Users')->add_users($tel, $user_name, $pwd, $parent_id, $token, '', $agent_id);
            if ($res['code'] !== 0) {
                return $this->error($res['info']);
            }
            //如果是二级
            if ($this->agent_uid) {
                $sys = Db::name('system_user')->where('id', $this->agent_id)->find();
                Db::name($this->table)
                    ->where('id', $res['id'])
                    ->update([
                        'agent_id' => $sys['parent_id'],
                        'agent_service_id' => $sys['id']
                    ]);
            }
            sysoplog('添加新用户', json_encode($_POST, JSON_UNESCAPED_UNICODE));
            return $this->success($res['info']);
        }
        $this->agent_list = Db::name('system_user')
            ->where('user_id', 0)
            ->where('authorize', "2")
            ->field('id,username')
            ->where('is_deleted', 0)
            ->select();
        return $this->fetch();
    }

    /**
     * 编辑会员
     * @auth true
     * @menu true
     */
     
    public function edit_users()
    {
        $id = input('get.id', 0);
        if (request()->isPost()) {
            $id = input('post.id/d', 0);
            $tel = input('post.tel/s', '');
            $user_name = input('post.user_name/s', '');
            $pwd = input('post.pwd/s', '');
            $pwd2 = input('post.pwd2/s', '');
            $parent_id = input('post.parent_id/d', 0);
            $level = input('post.level/d', 0);
            $group_id = input('post.group_id/d', 0);
            $agent_id = input('post.agent_id/d', 0);
            $agent_service_id = input('post.agent_service_id/d', 0);
            $freeze_balance = input('post.freeze_balance/d', 0);
            $balance = input('post.balance/d', 0);
            $deal_status = input('post.deal_status/d', 1);
            $token = input('__token__');
            $res = model('Users')
                ->edit_users($id, $tel, $user_name, $pwd, $parent_id, $balance, $freeze_balance, $token, $pwd2);
            $res2 = Db::table($this->table)->where('id', $id)->update([
                'deal_status' => $deal_status,
                'level' => $level,
                'group_id' => $group_id,
                'agent_id' => $agent_id,
                'agent_service_id' => $agent_service_id,
            ]);
            sysoplog('编辑用户', json_encode($_POST, JSON_UNESCAPED_UNICODE));
            return $this->success(lang('czcg'));
        }
        $this->agent_list = Db::name('system_user')
            ->where('user_id', 0)
            ->where('authorize', "2")
            ->field('id,username')
            ->where('is_deleted', 0);
        $this->agent_list = $this->agent_list->select();
        if (!$id) $this->error('参数错误');
        $this->info = Db::table($this->table)->find($id);
        $this->level = Db::table('xy_level')->select();
        $this->groupList = Db::table('xy_group')->where('agent_id', 'in', [$this->agent_id, 0])->select();
        $t = strtotime(date('Y-m-d'));
        if ($this->info['group_id'] > 0) {
            $this->converNumber = Db::name('xy_convey')
                ->where('uid', $id)
                ->where('group_id', $this->info['group_id'])
                ->order('addtime desc')
                ->limit(1)
                ->value('group_rule_num');
        } else {
            $this->converNumber = Db::name('xy_convey')
                ->where('uid', $id)
                ->where('level_id', $this->info['level'])
                ->where('addtime', 'between', [$t, $t + 86400])
                ->count('id');
        }
        $this->converNumber = $this->converNumber ? $this->converNumber : 0;
        return $this->fetch();
    }
    /**
     * 规则组
     * @auth true
     */
     public function guizhes()
     {
         if($_POST){
             $id = input("id");
             $group_id = input("group_id");
             
             $res = Db::table("xy_users")->where(['id'=>$id])->update(["group_id" => $group_id]);
             if($res){
                 sysoplog('编辑规则组', json_encode($_POST, JSON_UNESCAPED_UNICODE));
                  return $this->success('操作成功');
             }
             $this->error('参数错误');
         }
          $id = input('get.id', 0);
          $this->info = Db::table($this->table)->find($id);
         $this->groupList = Db::table('xy_group')->where('agent_id', 'in', [$this->agent_id, 0])->whereOr('is_share',1)->select();
         
         return $this->fetch();
     }
    

    /**
     * 更改用户等级
     * @auth true
     */
    public function edit_level()
    {
        $id = input('get.id', 0);
        if (request()->isPost()) {
            $id = input('post.id/d', 0);
            $level = input('post.level/d', 0);
            $group_id = input('post.group_id/d', 0);
            $token = input('__token__');
            $res2 = Db::table($this->table)->where('id', $id)->update([
                'level' => $level,
                'group_id' => $group_id,
            ]);
            sysoplog('更改用户等级', json_encode($_POST, JSON_UNESCAPED_UNICODE));
            return $this->success(lang('czcg'));
        }
        $this->agent_list = Db::name('system_user')
            ->where('user_id', 0)
            ->where('authorize', "2")
            ->field('id,username')
            ->where('is_deleted', 0)
            ->select();
        if (!$id) $this->error('参数错误');
        $this->info = Db::table($this->table)->find($id);
        $this->level = Db::table('xy_level')->select();
        $this->groupList = Db::table('xy_group')->where('agent_id', 'in', [$this->agent_id, 0])->select();
        $t = strtotime(date('Y-m-d'));
        if ($this->info['group_id'] > 0) {
            $this->converNumber = Db::name('xy_convey')
                ->where('uid', $id)
                ->where('group_id', $this->info['group_id'])
                ->order('addtime desc')
                ->limit(1)
                ->value('group_rule_num');
        } else {
            $this->converNumber = Db::name('xy_convey')
                ->where('uid', $id)
                ->where('level_id', $this->info['level'])
                ->where('addtime', 'between', [$t, $t + 86400])
                ->count('id');
        }
        $this->converNumber = $this->converNumber ? $this->converNumber : 0;
        return $this->fetch();
    }

    /**
     * 编辑彩金
     * @auth true
     */
    public function edit_money()
    {
        $id = input('get.id', 0);
        if (!$id) $this->error('参数错误');
        if (request()->isPost()) {
            $id = input('post.id/d', 0);
            $money = input('post.money/f', 0);
            if ($money > 0) {
                Db::table($this->table)
                    ->where('id', $id)
                    ->inc('balance', $money)
                    ->update();
            } else {
                $money = floatval(substr($money, 1));
                Db::table($this->table)
                    ->where('id', $id)
                    ->dec('balance', $money)
                    ->update();
            }
            sysoplog('编辑用户彩金', json_encode($_POST, JSON_UNESCAPED_UNICODE));
            return $this->success(lang('czcg'));
        }

        $this->info = Db::table($this->table)->find($id);
        return $this->fetch();
    }

    /**
     * 删除会员
     * @auth true
     */
    public function delete_user()
    {
        // $this->applyCsrfToken();
        $id = input('post.id/d', 0);
        $res = Db::table('xy_users')->where('id', $id)->delete();
        if ($res) {
            Db::table('xy_users_invites')->where('uid', $id)->delete();
            sysoplog('删除用户', 'ID ' . $id);
            $this->success('删除成功!');
        } else $this->error('删除失败!');
    }

     public function del_level()
        {
            // $this->applyCsrfToken();
            $id = input('post.id/d', 0);
            $res = Db::table('xy_level')->where('id', $id)->delete();
            if ($res) {
                $this->success('删除成功!');
            } else $this->error('删除失败!');
        }
    /**
     * 编辑会员_暗扣
     * @auth true
     */
    public function edit_users_ankou()
    {
        $id = input('get.id', 0);
        if (request()->isPost()) {
            $id = input('post.id/d', 0);
            $kouchu_balance_uid = input('post.kouchu_balance_uid/d', 0); //扣除人
            $kouchu_balance = input('post.kouchu_balance/f', 0); //扣除金额
            $show_td = (isset($_REQUEST['show_td']) && $_REQUEST['show_td'] == 'on') ? 1 : 0;//显示我的团队
            $show_cz = (isset($_REQUEST['show_cz']) && $_REQUEST['show_cz'] == 'on') ? 1 : 0;//显示充值
            $show_tx = (isset($_REQUEST['show_tx']) && $_REQUEST['show_tx'] == 'on') ? 1 : 0;//显示提现
            $show_num = (isset($_REQUEST['show_num']) && $_REQUEST['show_num'] == 'on') ? 1 : 0;//显示推荐人数
            $show_tel = (isset($_REQUEST['show_tel']) && $_REQUEST['show_tel'] == 'on') ? 1 : 0;//显示电话
            $show_tel2 = (isset($_REQUEST['show_tel2']) && $_REQUEST['show_tel2'] == 'on') ? 1 : 0;//显示电话隐藏
            $token = input('__token__');
            $data = [
                '__token__' => $token,
                'show_td' => $show_td,
                'show_cz' => $show_cz,
                'show_tx' => $show_tx,
                'show_num' => $show_num,
                'show_tel' => $show_tel,
                'show_tel2' => $show_tel2,
                'kouchu_balance_uid' => $kouchu_balance_uid,
                'kouchu_balance' => $kouchu_balance,
            ];
            //var_dump($data,$_REQUEST);die;
            unset($data['__token__']);
            $res = Db::table($this->table)->where('id', $id)->update($data);
            if (!$res) {
                return $this->error('编辑失败!');
            }
            sysoplog('编辑会员暗扣', json_encode($data, JSON_UNESCAPED_UNICODE));
            return $this->success('编辑成功!');
        }

        if (!$id) $this->error('参数错误');
        $this->info = Db::table($this->table)->find($id);

        //
        $uid = $id;
        $data = db('xy_users')->where('parent_id', $uid)
            ->field('id,username,headpic,addtime,childs,tel')
            ->order('addtime desc')
            ->select();

        foreach ($data as &$datum) {
            //充值
            $datum['chongzhi'] = db('xy_recharge')->where('uid', $datum['id'])->where('status', 2)->sum('num');
            //提现
            $datum['tixian'] = db('xy_deposit')->where('uid', $datum['id'])->where('status', 1)->sum('num');
        }

        //var_dump($data,$uid);die;

        //$this->cate = db('xy_goods_cate')->order('addtime asc')->select();
        $this->assign('cate', $data);

        return $this->fetch();
    }

    /**
     * 编辑会员登录状态
     * @auth true
     */
    public function edit_users_status()
    {
        $id = input('id/d', 0);
        $status = input('status/d', 0);
        if (!$id || !$status) return $this->error('参数错误');
        $res = model('Users')->edit_users_status($id, $status);
        if ($res['code'] !== 0) {
            return $this->error($res['info']);
        }
        sysoplog('编辑会员登录状态', "ID {$id} status {$status}");
        return $this->success($res['info']);
    }

    /**
     * 编辑会员登录状态
     * @auth true
     */
    public function edit_status()
    {
        $id = input('id/d', '');
        $status = input('status/d', '');
        $type = input('type', '');
        $res = Db::table('xy_users')->where('id', $id)->update([$type => $status]);
        if($res){
            return $this->success('操作成功');
        }
        return $this->error('操作失败');
    }
    
    /**
     * lang("清空订单")
     * @auth true
     */
    public function qingkon()
    {
        $id = input('id/d', 0);
        $status = input('status/d', 0);
        if (!$id || !$status) return $this->error(lang("参数错误"));
         Db::table("xy_convey")->where(["uid"=>$id])->update(['qkon'=>2]);
       // $res = Db::table("xy_users")->where(['id'=>$id])->update(['qkon'=>$status]);
        //$res = model('Users')->edit_users_status($id, $status);
        
       
        return $this->success(lang("操作成功"));
    }

    /**
     * 编辑银行卡信息
     * @auth true
     * @menu true
     */
    public function edit_users_address()
    {
        if (request()->isPost()) {
            // $this->applyCsrfToken();
            $id = input('post.id/d', 0);
            $tel = input('post.tel/s', '');
            $name = input('post.name/s', '');
            $address = input('post.address/s', '');

            $res = db('xy_member_address')->where('id', $id)->update(
                ['tel' => $tel,
                    'name' => $name,
                    'address' => $address
                ]);
            if ($res !== false) {
                sysoplog('编辑银行卡信息', json_encode($_POST, JSON_UNESCAPED_UNICODE));
                return $this->success('操作成功');
            } else {
                return $this->error('操作失败');
            }
        }

        //$data = db('xy_member_address')->where('uid',$id)->select();
        $uid = input('id/d', 0);
        $this->bk_info = Db::name('xy_member_address')->where('uid', input('id/d', 0))->select();
        if (!$this->bk_info) {
            //$this->error('没有数据');
            $data = [
                'uid' => input('id/d', 0),
                'name' => '',
                'tel' => '',
                'area' => '',
                'address' => '',
                'is_default' => 1,
                'addtime' => time()
            ];
            $tmp = db('xy_member_address')->where('uid', $uid)->find();
            if (!$tmp) $data['is_default'] = 1;
            $res = db('xy_member_address')->insert($data);

            $this->bk_info = Db::name('xy_member_address')->where('uid', input('id/d', 0))->select();

        }
        return $this->fetch();
    }

    /**
     * 编辑会员真人假人
     * @auth true
     */
    public function edit_users_status2()
    {
        $id = input('id/d', 0);
        $status = input('status/d', 0);
        if (!$id || !$status) return $this->error('参数错误');
        $status == -1 ? $status = 0 : '';
        $res = Db::table($this->table)->where('id', $id)->update(['is_jia' => $status]);
        if (!$res) {
            sysoplog('编辑会员真假人', "ID {$id} status {$status}");
            return $this->error('更新失败!');
        }
        return $this->success('更新成功');
    }
    
    
    /**
     * 编辑会员刷单状态
     * @auth true
     */
    public function edit_shuadan_status()
    {
        $id = input('id/d', 0);
        $status = input('status/d', 0);
        if (!$id || !$status) return $this->error('参数错误');
        $status == -1 ? $status = 0 : '';
        $res = Db::table($this->table)->where('id', $id)->update(['shuadan_status' => $status]);
        if (!$res) {
            sysoplog('编辑会员刷单状态', "ID {$id} status {$status}");
            return $this->error('更新失败!');
        }
        return $this->success('更新成功');
    }

    /**
     * 编辑会员二维码
     * @auth true
     */
    public function edit_users_ewm()
    {
        $id = input('id/d', 0);
        $invite_code = input('status/s', '');
        if (!$id || !$invite_code) return $this->error('参数错误');

        $n = ($id % 20);

        $dir = './upload/qrcode/user/' . $n . '/' . $id . '.png';
        if (file_exists($dir)) {
            unlink($dir);
        }

        $res = model('Users')->create_qrcode($invite_code, $id);
        if (0 && $res['code'] !== 0) {
            return $this->error('失败');
        }
        return $this->success('成功');
    }


    /**
     * 查看团队
     * @auth true
     */
    public function tuandui()
    {
        $uid = input('get.id/d', 1);
        if (isset($_REQUEST['iasjax'])) {
            $page = input('get.page/d', 1);
            $num = input('get.num/d', 10);
            $level = input('get.level/d', 1);
            $limit = ((($page - 1) * $num) . ',' . $num);
            $where = [];
            if ($level == -1) {
                $uids = model('Users')->child_user($uid, 5);
                $uids ? $where[] = ['u.id', 'in', $uids] : $where[] = ['u.id', 'in', [-1]];
            } else if ($level == 1) {
                $uids1 = model('Users')->child_user($uid, 1, 0);
                $uids1 ? $where[] = ['u.id', 'in', $uids1] : $where[] = ['u.id', 'in', [-1]];
            } else if ($level == 2) {
                $uids2 = model('Users')->child_user($uid, 2, 0);
                $uids2 ? $where[] = ['u.id', 'in', $uids2] : $where[] = ['u.id', 'in', [-1]];
            } else if ($level == 3) {
                $uids3 = model('Users')->child_user($uid, 3, 0);
                $uids3 ? $where[] = ['u.id', 'in', $uids3] : $where[] = ['u.id', 'in', [-1]];
            } else if ($level == 4) {
                $uids4 = model('Users')->child_user($uid, 4, 0);
                $uids4 ? $where[] = ['u.id', 'in', $uids4] : $where[] = ['u.id', 'in', [-1]];
            } else if ($level == 5) {
                $uids5 = model('Users')->child_user($uid, 5, 0);
                $uids5 ? $where[] = ['u.id', 'in', $uids5] : $where[] = ['u.id', 'in', [-1]];
            }

            if (input('tel/s', '')) $where[] = ['u.tel', 'like', '%' . input('tel/s', '') . '%'];
            if (input('username/s', '')) $where[] = ['u.username', 'like', '%' . input('username/s', '') . '%'];
            if (input('addtime/s', '')) {
                $arr = explode(' - ', input('addtime/s', ''));
                $where[] = ['u.addtime', 'between', [strtotime($arr[0]), strtotime($arr[1])]];
            }

            $count = $data = db('xy_users')->alias('u')->where($where)->count('id');
            $query = db('xy_users')->alias('u');
            $data = $query->field('u.id,u.tel,u.username,u.id_status,u.childs,u.ip,u.is_jia,u.addtime,u.invite_code,u.freeze_balance,u.status,u.balance,u1.username as parent_name')
                ->leftJoin('xy_users u1', 'u.parent_id=u1.id')
                ->where($where)
                ->order('u.id desc')
                ->limit($limit)
                ->select();

            if ($data) {
                //
                $uid1s = model('Users')->child_user($uid, 1, 0);
                $uid2s = model('Users')->child_user($uid, 2, 0);
                $uid3s = model('Users')->child_user($uid, 3, 0);
                $uid4s = model('Users')->child_user($uid, 4, 0);
                $uid5s = model('Users')->child_user($uid, 5, 0);

                foreach ($data as &$datum) {
                    //佣金
                    $datum['yj'] = db('xy_balance_log')
                        ->where('status', 1)
                        ->where('type', 3)
                        ->where('uid', $datum['id'])
                        ->sum('num');
                    $datum['tj_yj'] = db('xy_balance_log')
                        ->where('status', 1)
                        ->where('type', 6)
                        ->where('uid', $datum['id'])
                        ->sum('num');
                    $datum['cz'] = db('xy_recharge')->where('status', 2)->where('uid', $datum['id'])->sum('num');
                    $datum['tx'] = db('xy_deposit')->where('status', 2)->where('uid', $datum['id'])->sum('num');
                    $datum['addtime'] = date('Y/m/d H:i', $datum['addtime']);;
                    $datum['jb'] = '三级';
                    $color = '#92c7ef';


                    if (in_array($datum['id'], $uid1s)) {
                        $datum['jb'] = '一级';
                        $color = '#1E9FFF';
                    }
                    if (in_array($datum['id'], $uid2s)) {
                        $datum['jb'] = '二级';
                        $color = '#2b9aec';
                    }
                    if (in_array($datum['id'], $uid3s)) {
                        $datum['jb'] = '三级';
                        $color = '#1E9FFF';
                    }
                    if (in_array($datum['id'], $uid4s)) {
                        $datum['jb'] = '四级';
                        $color = '#76c0f7';
                    }
                    if (in_array($datum['id'], $uid5s)) {
                        $datum['jb'] = '五级';
                        $color = '#92c7ef';
                    }

                    $datum['jb'] = '<span class="layui-btn layui-btn-xs layui-btn-danger" style="background: ' . $color . '">' . $datum['jb'] . '</span>';
                }
            }
            if (!$data) json(['code' => 1, 'info' => '暂无数据']);

            $tj_com = 0;
            switch ($level) {
                case -1:
                    $tj_com = Db::name('xy_balance_log')->where('uid', $uid)
                        ->where('type', 6)->where('status', 1)->sum('num');
                    break;
                case 1:
                    $tj_com = Db::name('xy_balance_log')
                        ->where('uid', $uid)
                        ->where('sid', 'in', $uids1 ?: [-1])
                        ->where('type', 6)
                        ->where('status', 1)
                        ->sum('num');
                    break;
                case 2:
                    $tj_com = Db::name('xy_balance_log')
                        ->where('uid', $uid)
                        ->where('sid', 'in', $uids2 ?: [-1])
                        ->where('type', 6)
                        ->where('status', 1)
                        ->sum('num');
                    break;
                case 3:
                    $tj_com = Db::name('xy_balance_log')
                        ->where('uid', $uid)
                        ->where('sid', 'in', $uids3 ?: [-1])
                        ->where('type', 6)
                        ->where('status', 1)
                        ->sum('num');
                    break;
            }
            return json([
                'code' => 0,
                'count' => $count,
                'info' => '请求成功',
                'data' => $data,
                'other' => $limit,
                'tj_com' => $tj_com
            ]);
        } else {
            //
            $this->uid = $uid;
            $this->uinfo = db('xy_users')->find($uid);

        }


        return $this->fetch();
    }

    /**
     * 封禁/解封会员
     * @auth true
     */
    public function open()
    {
        $uid = input('post.id/d', 0);
        $status = input('post.status/d', 0);
        $type = input('post.type/d', 0);
        $info = [];
        if ($uid) {
            if (!$type) {
                $status2 = $status ? 0 : 1;
                $res = db('xy_users')->where('id', $uid)->update(['status' => $status2]);
                return json(['code' => 1, 'info' => '请求成功', 'data' => $info]);
            } else {
                //

                $wher = [];
                $wher2 = [];


                $ids1 = db('xy_users')->where('parent_id', $uid)->field('id')->column('id');
                $ids1 ? $wher[] = ['parent_id', 'in', $ids1] : '';

                $ids2 = db('xy_users')->where($wher)->field('id')->column('id');
                $ids2 ? $wher2[] = ['parent_id', 'in', $ids2] : '';

                $ids3 = db('xy_users')->where($wher2)->field('id')->column('id');

                $idsAll = array_merge([$uid], $ids1, $ids2, $ids3);  //所有ids
                $idsAll = array_filter($idsAll);

                $wherAll[] = ['id', 'in', $idsAll];
                $users = db('xy_users')->where($wherAll)->field('id')->select();

                //var_dump($users);die;
                $status2 = $status ? 0 : 1;
                foreach ($users as $item) {
                    $res = db('xy_users')->where('id', $item['id'])->update(['status' => $status2]);
                }

                return json(['code' => 1, 'info' => '请求成功', 'data' => $info]);
            }


        }

        return json(['code' => 1, 'info' => '暂无数据']);
    }


    //查看图片
    public function picinfo()
    { 
        $id = Db::table("xy_recharge")->find(input("id"));
        $this->pic =$id['pic'];
       
        if (!$this->pic) return;
        $this->fetch();
    }

    /**
     * 客服管理
     * @auth true
     * @menu true
     */
    public function cs_list()
    {
        $this->title = '客服列表';
        $where = [];
        if (input('tel/s', '')) $where[] = ['tel', 'like', '%' . input('tel/s', '') . '%'];
        if (input('username/s', '')) $where[] = ['username', 'like', '%' . input('username/s', '') . '%'];
        if (input('addtime/s', '')) {
            $arr = explode(' - ', input('addtime/s', ''));
            $where[] = ['addtime', 'between', [strtotime($arr[0]), strtotime($arr[1])]];
        }
        $this->_query('xy_cs')
            ->where($where)
            ->page();
    }
    
    /**
     * 删除客服
     * @auth true
     * @menu true
     */
     public function delete_cs()
     {
         $id = input("id");
         $res = Db::table("xy_cs")->where("id",$id)->delete();
         if($res){
             sysoplog('删除客服', json_encode(input(), JSON_UNESCAPED_UNICODE));
              return $this->success('操作成功');
         }
         return $this->error('操作失败，请刷新再试');
     }

    /**
     * 添加客服
     * @auth true
     * @menu true
     */
    public function add_cs()
    {
        if (request()->isPost()) {
            // $this->applyCsrfToken();
            $username = input('post.username/s', '');
            $tel = input('post.tel/s', '');
            $pwd = input('post.pwd/s', '');
            $qq = input('post.qq/d', 0);
            $wechat = input('post.wechat/s', '');
            $qr_code = input('post.qr_code/s', '');
            $url = input('post.url/s', '');
            $time = input('post.time');
            $arr = explode('-', $time);
            $btime = substr($arr[0], 0, 5);
            $etime = substr($arr[1], 1, 5);
            $data = [
                'username' => $username,
                'tel' => $tel,
                'pwd' => $pwd,    //需求不明确，暂时以明文存储密码数据
                'qq' => $qq,
                'wechat' => $wechat,
                'qr_code' => $qr_code,
                'url' => $url,
                'btime' => $btime,
                'etime' => $etime,
                'addtime' => time(),
            ];
            $res = db('xy_cs')->insert($data);
            if ($res) {
                sysoplog('添加客服', json_encode($data, JSON_UNESCAPED_UNICODE));
                return $this->success('添加成功');
            }
            return $this->error('添加失败，请刷新再试');
        }
        return $this->fetch();
    }

    /**
     * 客服登录状态
     * @auth true
     */
    public function edit_cs_status()
    {
        // $this->applyCsrfToken();
        sysoplog('编辑客服状态', json_encode($_POST, JSON_UNESCAPED_UNICODE));
        $this->_save('xy_cs', ['status' => input('post.status/d', 1)]);
    }

    /**
     * 编辑客服信息
     * @auth true
     * @menu true
     */
    public function edit_cs()
    {
        if (request()->isPost()) {
            // $this->applyCsrfToken();
            $id = input('post.id/d', 0);
            $username = input('post.username/s', '');
            $tel = input('post.tel/s', '');
            $pwd = input('post.pwd/s', '');
            $qq = input('post.qq/d', 0);
            $wechat = input('post.wechat/s', '');
            $url = input('post.url/s', '');
            $qr_code = input('post.qr_code/s', '');
            $time = input('post.time');
            $arr = explode('-', $time);
            $btime = substr($arr[0], 0, 5);
            $etime = substr($arr[1], 1, 5);
            $data = [
                'username' => $username,
                'tel' => $tel,
                'qq' => $qq,
                'wechat' => $wechat,
                'url' => $url,
                'qr_code' => $qr_code,
                'btime' => $btime,
                'etime' => $etime,
            ];
            if ($pwd) $data['pwd'] = $pwd;
            $res = db('xy_cs')->where('id', $id)->update($data);
            if ($res !== false) {
                sysoplog('编辑客服信息', json_encode($data, JSON_UNESCAPED_UNICODE));
                return $this->success('编辑成功');
            }
            return $this->error('编辑失败，请刷新再试');
        }
        $id = input('id/d', 0);
        $this->list = db('xy_cs')->find($id);
        return $this->fetch();
    }

    /**
     * 客服调用代码
     * @auth true
     * @menu true
     */
    public function cs_code()
    {
        if (request()->isPost()) {
            // $this->applyCsrfToken();
            $code = input('post.code');
            $res = db('xy_script')->where('id', 1)->update(['script' => $code]);
            if ($res !== false) {
                sysoplog('客服调用代码', json_encode($_POST, JSON_UNESCAPED_UNICODE));
                $this->success('操作成功!');
            }
            $this->error('操作失败!');
        }
        $this->code = db('xy_script')->where('id', 1)->value('script');
        return $this->fetch();
    }

    /**
     * 编辑银行卡信息
     * @auth true
     */
    public function edit_users_bk()
    {
        if (request()->isPost()) {
            // $this->applyCsrfToken();
            $id = input('post.id/d', 0);
            $res = db('xy_bankinfo')->where('id', $id)->update([
                'tel' => input('post.tel/s', ''),
                'site' => input('post.site/s', ''),
                'cardnum' => input('post.cardnum/s', ''),
                'username' => input('post.username/s', ''),
                'bankname' => input('post.bankname/s', ''),
                'bank_code' => input('post.bank_code/s', ''),
                'bank_branch' => input('post.bank_branch/s', ''),
                'document_id' => input('post.document_id/s', ''),
                'account_digit' => input('post.account_digit/s', ''),
                'wallet_tel' => input('post.wallet_tel/s', ''),
                'wallet_document_id' => input('post.wallet_document_id/s', ''),
                "usdt_diz" => input("usdt_diz"),
                "usdt_type" => input("usdt_type"),
            ]);
            if ($res !== false) {
                sysoplog('编辑银行卡信息', json_encode($_POST, JSON_UNESCAPED_UNICODE));
                return $this->success('操作成功');
            } else {
                return $this->error('操作失败');
            }
        }
        $this->bk_info = Db::name('xy_bankinfo')->where('uid', input('id/d', 0))->select();
        if (!$this->bk_info) $this->error('没有数据');
        return $this->fetch();
    }


    /**
     * 编辑会员等级
     * @auth true
     * @menu true
     */
    public function edit_users_level()
    {
        if (request()->isPost()) {
            $data = [
                'pic'=>input('pic',''),
                'name'=>input('name',''),
                'level'=>input('level',''),
                'num'=>input('num',''),
                'balance_min_amount'=>input('balance_min_amount',''),
                'balance_max_amount'=>input('balance_max_amount',''),
                'tixian_min'=>input('tixian_min',10),
                'tixian_max'=>input('tixian_max',''),
                'day_withdraw_num'=>input('day_withdraw_num',1),
                'day_withdraw_free_num'=>input('day_withdraw_free_num',1),
                'withdraw_fixed_fee'=>input('withdraw_fixed_fee',0),
                'tixian_shouxu'=>input('tixian_shouxu',0),
                'tixian_nim_order'=>input('tixian_nim_order',0),
                'grab_order_min_amount'=>input('grab_order_min_amount',0),
                'grab_order_max_amount'=>input('grab_order_max_amount',''),
                'num_min'=>input('num_min',10),
                'order_num'=>input('order_num',0),
                'grab_order_fixed_commission'=>input('grab_order_fixed_commission',0),
                'bili'=>input('bili',0),
                'credit'=>input('credit',0),
                'auto_buy_finance'=>input('auto_buy_finance',0),
                'lixibao_id'=>input('lixibao_id',''),
                'expire_day'=>input('expire_day',0),
                'promotion_commisssion'=>input('promotion_commisssion',0),
                'expire_return_principal'=>input('expire_return_principal',0),
                'sort'=>input('sort',0),
                'show_status'=>input('show_status',0),
                'status'=>input('status',0),
            ];
            $data['tixian_shouxu'] = $data['tixian_shouxu'] / 100;
            $data['bili'] = $data['bili'] / 100;
            $data['addtime'] = date('Y-m-d H:i:s',time());
            if($data['grab_order_max_amount'] < $data['grab_order_min_amount']){
                return $this->error('【抢单最高金额】必须大于等于【抢单最低金额】');
            }
            if($data['grab_order_min_amount'] < 1){
                return $this->error('【抢单最低金额】必须大于等于1');
            }
            if($data['day_withdraw_num'] < 1){
                return $this->error('【提款次数/天】必须大于等于1');
            }
            if($data['order_num'] < 1){
                return $this->error('【抢单次数限制】必须大于等于1');
            }
            if($data['auto_buy_finance'] == 1){
                if(empty($data['lixibao_id'])){
                    $data['auto_buy_finance'] = 0;
                }
            }
            if(empty($data['pic'])){
                return $this->error('图标不能为空');
            }
            if(empty($data['name'])){
                return $this->error('名称不能为空');
            }
            if($data['level'] === ''){
                return $this->error('等级值不能为空');
            }
            if($data['num'] === ''){
                return $this->error('升级价格不能为空');
            }
            if(Db::table("xy_level")->where('id','<>',input('id'))->where('level',$data['level'])->field('id')->find()){
                return $this->error('等级值已存在');
            }
            $res = Db::table("xy_level")->where('id',input('id'))->update($data);
            if($res){
                return $this->success('更新成功');
            }
            return $this->error('更新失败');
            // $this->applyCsrfToken();
//            $id = input('post.id/d', 0);
//            $name = input('post.name/s', '');
//            $level = input('post.level/d', 0);
//            $num = input('post.num/s', '');
//            $order_num = input('post.order_num/s', '');
//            $bili = input('post.bili/s', '');
//            $tj_bili = input('post.tj_bili/s', '');
//            $tixian_ci = input('post.tixian_ci/s', '');
//            $tixian_min = input('post.tixian_min/s', '');
//            $tixian_max = input('post.tixian_max/s', '');
//            $auto_vip_xu_num = input('post.auto_vip_xu_num/s', '');
//            $num_min = input('post.num_min/s', '');
//            $tixian_nim_order = input('post.tixian_nim_order/d', 0);
//            $tixian_shouxu = input('post.tixian_shouxu/f', 0);
//            $is_invite = input('post.is_invite/d', 1);
//
//            $pic = input("post.pic",'');
//
//            $cate = Db::name('xy_goods_cate')->select();
//            $cids = [];
//            foreach ($cate as $item) {
//                $k = 'cids' . $item['id'];
//                if (isset($_REQUEST[$k]) && $_REQUEST[$k] == 'on') {
//                    $cids[] = $item['id'];
//                }
//            }
//            $cidsstr = implode(',', $cids);
//            //var_dump($cidsstr);die;
//            $res = db('xy_level')->where('id', $id)->update(
//                [
//                    'name' => $name,
//                    'level' => $level,
//                    'num' => $num,
//                    'order_num' => $order_num,
//                    'bili' => $bili,
//                    'tj_bili' => $tj_bili,
//                    'tixian_ci' => $tixian_ci,
//                    'tixian_min' => $tixian_min,
//                    'tixian_max' => $tixian_max,
//                    'num_min' => $num_min,
//                    'cids' => $cidsstr,
//                    'tixian_nim_order' => $tixian_nim_order,
//                    'auto_vip_xu_num' => $auto_vip_xu_num,
//                    'tixian_shouxu' => $tixian_shouxu,
//                    'is_invite' => $is_invite,
//                    "pic"   => $pic
//                ]);
//            if ($res !== false) {
//                sysoplog('编辑会员等级', json_encode($_POST, JSON_UNESCAPED_UNICODE));
//                return $this->success('操作成功');
//            } else {
//                return $this->error('操作失败');
//            }
        }
        $this->bk_info = Db::name('xy_level')->where('id', input('id/d', 0))->select();
        $this->cate = Db::name('xy_goods_cate')->select();
        if (!$this->bk_info) $this->error('没有数据');
        $this->list = Db::name('xy_lixibao_list')->where('status',1)->select();
        return $this->fetch();
    }


    /**
     * 导出xls
     * @auth true
     */
    public function daochu(){
        $map = array();
        //搜索时间
        if( !empty($start_date) && !empty($end_date) ) {
            $start_date = strtotime($start_date . "00:00:00");
            $end_date = strtotime($end_date . "23:59:59");
            $map['_string'] = "( a.create_time >= {$start_date} and a.create_time < {$end_date} )";
        }

        $list = Db::name('xy_users u')->field('u.id,u.tel,u.username,u.lixibao_balance,u.id_status,u.ip,u.is_jia,u.addtime,u.invite_code,u.freeze_balance,u.status,u.balance,u1.username as parent_name')
            ->leftJoin('xy_users u1','u.parent_id=u1.id')
            ->where($map)
            ->order('u.id desc')
            ->select();
        //$list = $list[0];
        //echo '<pre>';
        //var_dump($list);die;
        foreach( $list as $k=>&$_list ) {
            //var_dump($_list);die;
            $_list['addtime'] ? $_list['addtime'] = date('m/d H:i', $_list['addtime']) : '';
        }
        //echo '<pre>';
        //var_dump($list);die;
        //3.实例化PHPExcel类
        $objPHPExcel = new PHPExcel();
        //4.激活当前的sheet表
        $objPHPExcel->setActiveSheetIndex(0);
        //5.设置表格头（即excel表格的第一行）
        //$objPHPExcel
        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'ID');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '账号');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '用户名');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '账号余额');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '冻结金额');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '利息宝余额');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '上级用户');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '邀请码');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '注册时间');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', '最后登录IP');

        //设置A列水平居中
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置单元格宽度
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(30);


        //6.循环刚取出来的数组，将数据逐一添加到excel表格。
        for($i=0;$i<count($list);$i++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+2),$list[$i]['id']);//ID
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+2),$list[$i]['tel']);//标签码
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+2),$list[$i]['username']);//防伪码
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+2),$list[$i]['balance']);//防伪码
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+2),$list[$i]['freeze_balance']);//防伪码
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+2),$list[$i]['lixibao_balance']);//防伪码
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($i+2),$list[$i]['parent_name']);//防伪码
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($i+2),$list[$i]['invite_code']);//防伪码
            $objPHPExcel->getActiveSheet()->setCellValue('I'.($i+2),$list[$i]['addtime']);//防伪码
            $objPHPExcel->getActiveSheet()->setCellValue('J'.($i+2),$list[$i]['ip']);//防伪码
        }

        //7.设置保存的Excel表格名称
        $filename = 'user'.date('ymd',time()).'.xls';
        //8.设置当前激活的sheet表格名称；

        $objPHPExcel->getActiveSheet()->setTitle('sheet'); // 设置工作表名

        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('防伪码');
        //9.设置浏览器窗口下载表格
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$filename.'"');
        //生成excel文件
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        //下载文件在浏览器窗口
        $objWriter->save('php://output');
        exit;
    }

}