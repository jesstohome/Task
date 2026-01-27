<?php

namespace app\index\controller;

use think\App;
use think\Controller;
use think\Exception;
use think\Request;
use think\Db;
use think\View;

class My extends Base
{
    protected $msg = ['__token__' => 'post error'];

    /**
     * 首页
     */
    public function index()
    {
        $parameter["info"] = db('xy_users')->field('username,tel,level,id,headpic,balance,freeze_balance,lixibao_balance,invite_code,show_td')->find($this->usder_id);
      
      // $parameter["sell_y_num"] = db('xy_convey')->where('status', 1)->where('uid', $this->usder_id)->sum('commission');

        $level = $parameter["info"]['level'];
        !$level ? $level = 0 : '';

        $parameter["level_name"] = db('xy_level')->where('level', $level)->value('name');

        $parameter["info"]['lixibao_balance'] = number_format($parameter["info"]['lixibao_balance'], 2);
        $parameter["info"]['balance_all_format'] = number_format($parameter["info"]['balance']+$parameter["info"]['freeze_balance'],2);
        $parameter["info"]['balance_format'] = number_format($parameter["info"]['balance'],2);
        $parameter["info"]['freeze_balance_format'] = number_format($parameter["info"]['freeze_balance'],2);
       $parameter["rililv"] = config('lxb_bili') * 100 . '%';
        $parameter["lxb_shouyi"] = db('xy_lixibao')->where('status', 1)->where('uid', $this->usder_id)->sum('num');
        $uinfo = db('xy_users')->where('id', $this->usder_id)->find();
        $level_name = 'Free';
        if (!empty($uinfo['level'])) {
            //$order_num = db('xy_level')->where('level', $uinfo['level'])->value('order_num');
            $level_name = db('xy_level')->where('level', $uinfo['level'])->value('name');
            //$level_nums = db('xy_level')->where('level', $uinfo['level'])->value('num');
        }
        $parameter["level_name"] = $level_name;


        $msg_list = Db::name('xy_message')
            ->field('id,content')
            ->where('uid', $this->usder_id)
            ->where('is_read', 1)
            ->order('id desc')
            ->select();
        $this->msg = '';
        if ($msg_list) {
            Db::name('xy_message')
                ->where('uid', $this->usder_id)
                ->where('is_read', 1)
                ->update([
                    'is_read' => 2,
                    'read_time' => time()
                ]);
            foreach ($msg_list as $v) {
                
                if($v['content'] == '11-1'){
                    $lang= $this->language;
        
        $notices = Db::name('xy_index_msg')->where('id', 19)->find();
        $content = $notices[$lang];
        $title = $notices["t_".$lang];
        
        $parameter["msg"] = $content;
        
                }else{
                   $parameter["msg"] .= str_replace("'", ' ', $v['content']) . '<br>'; 
                }
                
                
            }
        }
        
        return json_encode(['code'=>0,'msg'=>'success','data'=>$parameter]);

    }

    /**
     * 获取收货地址
     */
    public function get_address()
    {
        $id = $this->usder_id;
        $data = db('xy_member_address')->where('uid', $id)->select();
        if ($data)
            return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => $data]);
        else
            return json(['code' => 1, 'info' => yuylangs('zwsj')]);
    }

    public function reload()
    {
        $id = $this->usder_id;;
        $user = db('xy_users')->find($id);

        $n = ($id % 20);

        $dir = './upload/qrcode/user/' . $n . '/' . $id . '.png';
        if (file_exists($dir)) {
            unlink($dir);
        }

        $res = model('admin/Users')->create_qrcode($user['invite_code'], $id);
        if (0 && $res['code'] !== 0) {
            return $this->error(yuylangs('qqcw'));
        }
        return $this->success(yuylangs('czcg'));
    }


    /**c
     * 添加收货地址
     */
    public function add_address()
    {
        if (request()->isPost()) {
            $uid = $this->usder_id;
            $name = input('post.name/s', '');
            $tel = input('post.tel/s', '');
            $address = input('post.address/s', '');
            $area = input('post.area/s', '');
            $token = input("token");//获取提交过来的令牌
            $data = ['__token__' => $token];
            $validate = \Validate::make($this->rule, $this->msg);
            if (!$validate->check($data)) {
                return json(['code' => 1, 'info' => $validate->getError()]);
            }
            $data = [
                'uid' => $uid,
                'name' => $name,
                'tel' => $tel,
                'area' => $area,
                'address' => $address,
                'addtime' => time()
            ];
            $tmp = db('xy_member_address')->where('uid', $uid)->find();
            if (!$tmp) $data['is_default'] = 1;
            $res = db('xy_member_address')->insert($data);
            if ($res)
                return json(['code' => 0, 'info' => yuylangs('czcg')]);
            else
                return json(['code' => 1, 'info' => yuylangs('czsb')]);
        }
        return json(['code' => 1, 'info' => yuylangs('qqcw')]);
    }

    /**
     * 编辑收货地址
     */
    public function edit_address()
    {
        if (request()->isPost()) {
             $uid = $this->usder_id;
            $name = input('post.name/s', '');
            $tel = input('post.tel/s', '');
            $address = input('post.address/s', '');

            $area = input('post.area/s', '');


            $ainfo = db('xy_member_address')->where('uid', $uid)->find();
            if ($ainfo) {
                $res = db('xy_member_address')
                    ->where('id', $ainfo['id'])
                    ->update([
                        'uid' => $uid,
                        'name' => $name,
                        'tel' => $tel,
                        'area' => $area,
                        'address' => $address,
                        //'addtime'   => time()
                    ]);
            } else {
                $res = db('xy_member_address')
                    ->insert([
                        'uid' => $uid,
                        'name' => $name,
                        'tel' => $tel,
                        'area' => $area,
                        'address' => $address,
                        'addtime' => time()
                    ]);
            }

            if ($res)
                return json(['code' => 0, 'info' => yuylangs('czcg')]);
            else
                return json(['code' => 1, 'info' => yuylangs('czsb')]);
        } elseif (request()->isGet()) {
             $uid = $this->usder_id;
            $parameter["info"] = db('xy_member_address')->where('uid', $uid)->find();
            
            return json(['code' => 0, 'info' => yuylangs('czcg'),'data'=>$parameter]);
            
           
        }
    }

    public function team()
    {
        $uid = $this->usder_id;
        //$this->info = db('xy_member_address')->where('uid',$id)->find();
        $uids = model('admin/Users')->child_user($uid, 5);
        array_push($uids, $uid);
        $uids ? $where[] = ['uid', 'in', $uids] : $where[] = ['uid', 'in', [-1]];

        $datum['sl'] = count($uids);
        $datum['yj'] = db('xy_convey')->where('status', 1)->where($where)->sum('num');
        $datum['cz'] = db('xy_recharge')->where('status', 2)->where($where)->sum('num');
        $datum['tx'] = db('xy_deposit')->where('status', 2)->where($where)->sum('num');


        //
        $uids1 = model('admin/Users')->child_user($uid, 1);
        $uids1 ? $where1[] = ['sid', 'in', $uids1] : $where1[] = ['sid', 'in', [-1]];
        $datum['log1'] = db('xy_balance_log')->where('uid', $uid)->where($where1)->where('f_lv', 1)->sum('num');

        $uids2 = model('admin/Users')->child_user($uid, 2);
        $uids2 ? $where2[] = ['sid', 'in', $uids2] : $where2[] = ['sid', 'in', [-1]];
        $datum['log2'] = db('xy_balance_log')->where('uid', $uid)->where($where2)->where('f_lv', 2)->sum('num');

        $uids3 = model('admin/Users')->child_user($uid, 3);
        $uids3 ? $where3[] = ['sid', 'in', $uids3] : $where3[] = ['sid', 'in', [-1]];
        $datum['log3'] = db('xy_balance_log')->where('uid', $uid)->where($where3)->where('f_lv', 3)->sum('num');


        $uids5 = model('admin/Users')->child_user($uid, 5);
        $uids5 ? $where5[] = ['sid', 'in', $uids5] : $where5[] = ['sid', 'in', [-1]];
        $datum['yj2'] = db('xy_convey')->where('status', 1)->where($where)->sum('commission');
        $datum['yj3'] = db('xy_balance_log')->where('uid', $uid)->where($where5)->where('type', 6)->sum('num');;


        $this->info = $datum;

        return $this->fetch();
    }

    public function caiwu()
    {
        $id = $this->usder_id;
        //$day = input('get.day/s', '');
        
        $parameter["page"] = input("page",1);
        $parameter["size"] = input("size",10);
        
        $where = [];
        

        $start = input('get.start/s', '');
        $end = input('get.end/s', '');
        if ($start || $end) {
            $start ? $start = strtotime($start) : $start = strtotime('2020-01-01');
            $end ? $end = strtotime($end . ' 23:59:59') : $end = time();
            $where[] = ['addtime', 'between', [$start, $end]];
        }


        $parameter['start'] = $start ? date('Y-m-d', $start) : '';
       $parameter['end'] = $end ? date('Y-m-d', $end) : '';
        
        if(input('get.type/d', 0)){
            $where['type'] = input('get.type/d', 0);
        }
        

            $parameter['list'] = Db::table('xy_balance_log')
                     ->where('uid', $id)->where($where)
                     ->order('id desc')
                     ->where("type != 2")
                     ->page($parameter["page"],$parameter["size"])
                     ->select();
            
            foreach($parameter['list'] as &$value){
                    $balance = (float)$value['balance'];
                    $num = (float)$value['num'];
                
                    if ($value['status'] == 1) {
                        $value['newnum'] = $balance + $num;
                    } else {
                        $value['newnum'] = $balance - $num;
                    }
                
                    // 格式化放在最后显示时再处理
                    $value['balance'] = number_format($balance, 2, '.', '');
                    $value['num'] = number_format($num, 2, '.', '');
                    $value['newnum'] = number_format($value['newnum'], 2, '.', '');
                
                    $value['addtime'] = $value['addtime'] - (60 * 60 * config('equation_of_time'));
            }
        $parameter['paging'] = 1;
        if(count($parameter['list']) < $parameter["size"]){
           $parameter['paging'] = 0; 
        } 
            
       return json_encode(['code'=>0,'msg'=>'success','data'=>$parameter]);
       
        //var_dump($_REQUEST);die;
    }


    public function headimg()
    {
        $uid = $this->usder_id;
        if (request()->isPost()) {
            $username = input('post.pic/s', '');
            $res = db('xy_users')->where('id', $this->usder_id)->update(['headpic' => $username]);
            if ($res !== false) {
                return json(['code' => 0, 'info' => yuylangs('czcg')]);
            } else {
                return json(['code' => 1, 'info' => yuylangs('czsb')]);
            }
        }
        $this->info = db('xy_users')->find($uid);
        return $this->fetch();
    }

    public function del_bank()
    {
        if (request()->isPost()){
            $uid = $this->usder_id;
            $bid = input('post.bid/d', 0);
            Db::name('xy_bankinfo')->where('uid', $uid)->where('id',$bid)->delete();
            return json_encode(['code'=>0,"info"=>'success',"data"=>'']);
        }
        return json_encode(['code'=>1,"info"=>'error',"data"=>'']);
    }

    public function bind_bank()
    {
        $id = input('post.id/d', 0);
        $uid = $this->usder_id;
        $info = db('xy_bankinfo')->where('uid', $uid)->order('id','desc')->select();
        $uinfo = db('xy_users')->find($uid);

        $bank_codes = Db::table("xy_pay")->where('status',1)->where("is_payout",1)->find();
     //   $parameter['bank_codes'] = explode("\n",$bank_codes['bank_code']);
          
         //$bank_list =   $this->getBankList();
        $parameter['bank_list'] = $this->getBankList($bank_codes['bank_code']);
        $parameter['tondao_type'] =explode("\n",$bank_codes['tondao_type']);;
        $parameter['py_status'] = $bank_codes["py_status"];
        $parameter['user_bank_num'] = sysconf('user_bank_num') ?: 1;
        $parameter['edit_card_switch'] = sysconf('edit_card_switch') ? true : false;
        $parameter['del_card_switch'] = sysconf('del_card_switch') ? true : false;
        $parameter['bank_name_switch'] = sysconf('bank_name_switch') ? true : false;
        $parameter['branch_bank_name_switch'] = sysconf('branch_bank_name_switch') ? true : false;
        $parameter['bank_cardnumber_switch'] = sysconf('bank_cardnumber_switch') ? true : false;
        $parameter['user_bank_name_switch'] = sysconf('user_bank_name_switch') ? true : false;
        $parameter['bank_phone_switch'] = sysconf('bank_phone_switch') ? true : false;
        $parameter['bank_mail_switch'] = sysconf('bank_mail_switch') ? true : false;
        $parameter['bank_cci_switch'] = sysconf('bank_cci_switch') ? true : false;

        $pwd2 = input('post.paypassword/s', '');
        $user_info = db('xy_users')->field('pwd2,salt2')->find($this->usder_id);
        // if ($user_info['pwd2'] == '') {
        //     header('Location:' . url('/index/ctrl/edit_pwd2'));
        //     exit;
        // }

        if (request()->isPost()) {
            //验证支付密码
            if ($user_info['pwd2'] != sha1($pwd2 . $user_info['salt2'] . config('pwd_str'))) return json(['code' => 1, 'info' => yuylangs('pass_error')]);

            $bankname = input('post.bank_name/s', '');
            $cardnum = input('post.bank_card_number/s', '');
            $username = input('post.name/s', '');
            $document_type = input('post.document_type/s', '');
            $document_id = input('post.document_id/s', '');
            $bank_code = input('post.bank_code/s', '');
            $bank_branch = input('post.routing_number/s', '');
            $bank_type = input('post.tx_type/s', '');
            $account_digit = input('post.swift_bic/s', '');
            $wallet_tel = input('post.wallet_tel/s', '');
            $wallet_document_id = input('post.wallet_document_id/s', '');
            $wallet_document_type = input('post.wallet_document_type/s', '');
            $site = input('post.bank_address/s', '');
            $tel = input('post.tel/s', '');
            $address = input('post.address/s', '');
            $usdt_type = input('post.usdt_type/s', '');
            $usdt_diz = input('post.usdt_address/s', '');
            $qq = input('post.qq/s', '');
            $cci= input('post.cci/s', '');
            $mailbox = input("post.mailbox",'');
            $type = input('types');

            if(empty($id)){
                $user_bank_count = db('xy_bankinfo')
                    ->where('uid', $uid)
                    ->where('bank_type', 'BANK')
//                ->where('cardnum', $cardnum)
                    ->count();
                $user_bank_num = sysconf('user_bank_num');
                if ($user_bank_count >= $user_bank_num) {
                    //return json(['code' => 1, 'info' => translate('exceeded the limit on the number of bank cards')]);
//                 return json(['code' => 1, 'info' => translate('The information has been bound and cannot be modified. Please contact online customer service')]);
                    // if($usdt_type == 1){
                    //   if($res['bankname'] || $res['cardnum']){
                    //         return json(['code' => 1, 'info' => yuylangs('The information has been bound and cannot be modified. Please contact online customer service')]);
                    //   }
                    // }else{
                    //   if($res['usdt_type'] || $res['usdt_diz']){
                    //         return json(['code' => 1, 'info' => yuylangs('The information has been bound and cannot be modified. Please contact online customer service')]);
                    //   }
                    // }
                }

                $bank_cardnumber_only_switch = sysconf('bank_cardnumber_only_switch');
                if($bank_cardnumber_only_switch == 1){
                    $cardnum_count = db('xy_bankinfo')
                        ->where('bank_type', 'BANK')
                        ->where('bankname', $bankname)
                        ->where('cardnum', $cardnum)
                        ->count();
                    if($cardnum_count > 0){
                        //return json(['code' => 1, 'info' => translate('The bank card number has been bound')]);
                    }
                }

                $bank_name_only_switch = sysconf('bank_name_only_switch');
                if($bank_name_only_switch == 1){
                    $cardnum_count = db('xy_bankinfo')
                        ->where('bank_type', 'BANK')
                        ->where('bankname', $bankname)
                        ->where('username', $username)
                        ->count();
                    if($cardnum_count > 0){
                        //return json(['code' => 1, 'info' => translate('The account name has been bound')]);
                    }
                }

                $bank_cci_only_switch = sysconf('bank_cci_only_switch');
                if($bank_cci_only_switch == 1){
                    $cardnum_count = db('xy_bankinfo')
                        ->where('bank_type', 'BANK')
                        ->where('cci', $cci)
                        ->where('bankname', $bankname)
                        ->count();
                    if($cardnum_count > 0){
                        //return json(['code' => 1, 'info' => translate('The CCi is already bound')]);
                    }
                }
            }else{
                $bank_cardnumber_only_switch = sysconf('bank_cardnumber_only_switch');
                if($bank_cardnumber_only_switch == 1){
                    $cardnum_count = db('xy_bankinfo')
                        ->where('bank_type', 'BANK')
                        ->where('bankname', $bankname)
                        ->where('cardnum', $cardnum)
                        ->where('id', '<>', $id)
                        ->count();
                    if($cardnum_count > 0){
                        //return json(['code' => 1, 'info' => translate('The bank card number has been bound')]);
                    }
                }

                $bank_name_only_switch = sysconf('bank_name_only_switch');
                if($bank_name_only_switch == 1){
                    $cardnum_count = db('xy_bankinfo')
                        ->where('bank_type', 'BANK')
                        ->where('bankname', $bankname)
                        ->where('username', $username)
                        ->where('id', '<>', $id)
                        ->count();
                    if($cardnum_count > 0){
                        //return json(['code' => 1, 'info' => translate('The account name has been bound')]);
                    }
                }

                $bank_cci_only_switch = sysconf('bank_cci_only_switch');
                if($bank_cci_only_switch == 1){
                    $cardnum_count = db('xy_bankinfo')
                        ->where('bank_type', 'BANK')
                        ->where('cci', $cci)
                        ->where('bankname', $bankname)
                        ->where('id', '<>', $id)
                        ->count();
                    if($cardnum_count > 0){
                        //return json(['code' => 1, 'info' => translate('The CCi is already bound')]);
                    }
                }
            }

            
            if($usdt_diz){
                $zhis = Db::table("xy_bankinfo")->where('usdt_diz', $usdt_diz)->find();
                if($zhis){
                     return json(['code' => 1, 'info' => yuylangs('czsb')]);
                }
                
                if(!$bank_type){
                    $bank_type = $usdt_type;
                }
                
            }else{
                $zhis = Db::table("xy_bankinfo")->where('cardnum', $cardnum)->where('uid','<>',$uid)->find();
                if($zhis){
                    return json(['code' => 1, 'info' => translate('The bank card has been bound')]);
                }
                //  $zhis1 = Db::table("xy_bankinfo")->where('username', $username)->where('uid','<>',$uid)->find();
                // if($zhis1){
                //     return json(['code' => 1, 'info' => translate('The name has been bound')]);
                // }
                //  $zhis2 = Db::table("xy_bankinfo")->where('tel', $tel)->where('uid','<>',$uid)->find();
                // if($zhis2){
                //     return json(['code' => 1, 'info' => translate('The mobile phone number has been bound')]);
                // }  
            }
            
            
           
            $data = array(
                'username' => $username,
                'bankname' => $bankname,
                'cardnum' => $cardnum,
                'document_type' => $document_type,
                'document_id' => $document_id,
                'bank_code' => $bank_code,
                'bank_branch' => $bank_branch,
                'bank_type' => $bank_type,
                'account_digit' => $account_digit,
                'wallet_tel' => $wallet_tel,
                'wallet_document_id' => $wallet_document_id,
                'wallet_document_type' => $wallet_document_type,
                'site' => $site,
                'address' => $address,
                'qq' => $qq,
                'tel' => $tel,
                "usdt_type" => $usdt_type,
                "usdt_diz" => $usdt_diz,
                'status' => 1,
                "mailbox" => $mailbox,
                "cci" => $cci,
            );
            if (!empty($id)) {
                $res = db('xy_bankinfo')->where('uid', $uid)->where('id',$id)->update($data);
            } else {
                $data['uid'] = $uid;
                $res = db('xy_bankinfo')->insert($data);
            }
            if ($res) {
                return json(['code' => 0, 'info' => yuylangs('czcg')]);
            } else {
                return json(['code' => 1, 'info' => yuylangs('czsb'), 'sql' => Db::name('xy_bankinfo')->getLastSql()]);
            }
        }
        $parameter['info'] = $info;
        //$this->assign('bank_list', $bank_list);
         return json_encode(['code'=>0,"msg"=>'success',"data"=>$parameter]);
        
    //     $c = config('default_country');
    //     $file = APP_PATH . request()->module() . '/view/my/bind_bank_' . $c . '.html';
    //     if (file_exists($file)) {
    //         return $this->fetch('bind_bank_' . $c);
    //     } else {
    //         return $this->fetch();
    //     }
    //   if (config('default_country') == 'AUS') {
    //         return $this->fetch('bind_bank_AUS');
    //     }
    //     if (config('default_country') == 'BRA') {
    //         return $this->fetch('bind_bank_BRA');
    //     }
    //     if (config('default_country') == 'INR') {
    //         return $this->fetch('bind_bank_INR');
    //     }
    //     return $this->fetch();
    }


    /**
     * 设置默认收货地址
     */
    public function set_address()
    {
        if (request()->isPost()) {
            $id = input('post.id/d', 0);
            Db::startTrans();
            $res = db('xy_member_address')->where('uid', $this->usder_id)->update(['is_default' => 0]);
            $res1 = db('xy_member_address')->where('id', $id)->update(['is_default' => 1]);
            if ($res && $res1) {
                Db::commit();
                return json(['code' => 0, 'info' => yuylangs('czcg')]);
            } else {
                Db::rollback();
                return json(['code' => 1, 'info' => yuylangs('czsb')]);
            }
        }
        return json(['code' => 1, 'info' => yuylangs('qqcw')]);
    }

    /**
     * 删除收货地址
     */
    public function del_address()
    {
        if (request()->isPost()) {
            $id = input('post.id/d', 0);
            $info = db('xy_member_address')->find($id);
            if ($info['is_default'] == 1) {
                return json(['code' => 1, 'info' => yuylangs('def_delete_address')]);
            }
            $res = db('xy_member_address')->where('id', $id)->delete();
            if ($res)
                return json(['code' => 0, 'info' => yuylangs('czcg')]);
            else
                return json(['code' => 1, 'info' => yuylangs('czsb')]);
        }
        return json(['code' => 1, 'info' => yuylangs('qqcw')]);
    }

    public function get_bot()
    {
        $data = model('admin/Users')->get_botuser($this->usder_id, 3);
        halt($data);
    }


    public function yue()
    {
        $uid = $this->usder_id;
        $this->info = db('xy_users')->find($uid);
        return $this->fetch();
    }


    public function edit_username()
    {
        $uid = $this->usder_id;
        if (request()->isPost()) {
            $username = input('post.username/s', '');
            $res = db('xy_users')->where('id', $this->usder_id)->update(['username' => $username]);
            if ($res !== false) {
                return json(['code' => 0, 'info' => yuylangs('czcg')]);
            } else {
                return json(['code' => 1, 'info' => yuylangs('czsb')]);
            }
        }
        $this->info = db('xy_users')->find($uid);
        return $this->fetch();
    }


    /**
     * 用户账号充值
     */
    public function user_recharge()
    {
        $tel = input('post.tel/s', '');
        $num = input('post.num/d', 0);
        $pic = input('post.pic/s', '');
        $real_name = input('post.real_name/s', '');
        $uid = $this->usder_id;

        if (!$pic || !$num) return json(['code' => 1, 'info' => yuylangs('cscw')]);
        //if(!is_mobile($tel)) return json(['code'=>1,'info'=>'手机号码格式不正确']);

        if (is_image_base64($pic))
            $pic = '/' . $this->upload_base64('xy', $pic);  //调用图片上传的方法
        else
            return json(['code' => 1, 'info' => yuylangs('tpgscw')]);
        $id = getSn('SY');
        $res = db('xy_recharge')
            ->insert([
                'id' => $id,
                'uid' => $uid,
                'tel' => $tel,
                'real_name' => $real_name,
                'pic' => $pic,
                'num' => $num,
                'addtime' => time()
            ]);
        if ($res)
            return json(['code' => 0, 'info' => yuylangs('czcg')]);
        else
            return json(['code' => 1, 'info' => yuylangs('czsbqshcs')]);
    }

    //邀请界面
    public function invite()
    {
        $uid = $this->usder_id;
       // $this->assign('pic', '/upload/qrcode/user/' . ($uid % 20) . '/' . $uid . '.png');
        $user = db('xy_users')->find($uid);
        $url = config('version').'/register?type=1&invite_code=' . $user['invite_code'];
         $lang= $this->language;
        $parameter["url"] = $url;
        $parameter["invite_code"] = $user['invite_code'];
        
        $msg = Db::name('xy_index_msg')->where('id', 13)->find();
        $content = '';
        $content = $msg[$lang];
        $title = $msg["t_".$lang];
        
        $parameter["invite_msg"] =  $content;
       
         return json_encode(['code'=>0,'msg'=>'success','data'=>$parameter]);
    }

    //我的资料
    public function do_my_info()
    {
        if (request()->isPost()) {
            $headpic = input('post.headpic/s', '');
            $wx_ewm = input('post.wx_ewm/s', '');
            $zfb_ewm = input('post.zfb_ewm/s', '');
            $nickname = input('post.nickname/s', '');
            $sign = input('post.sign/s', '');
            $data = [
                'nickname' => $nickname,
                'signiture' => $sign
            ];

            if ($headpic) {
                if (is_image_base64($headpic))
                    $headpic = '/' . $this->upload_base64('xy', $headpic);  //调用图片上传的方法
                else
                    return json(['code' => 1, 'info' => yuylangs('tpgscw')]);
                $data['headpic'] = $headpic;
            }

            if ($wx_ewm) {
                if (is_image_base64($wx_ewm))
                    $wx_ewm = '/' . $this->upload_base64('xy', $wx_ewm);  //调用图片上传的方法
                else
                    return json(['code' => 1, 'info' => yuylangs('tpgscw')]);
                $data['wx_ewm'] = $wx_ewm;
            }

            if ($zfb_ewm) {
                if (is_image_base64($zfb_ewm))
                    $zfb_ewm = '/' . $this->upload_base64('xy', $zfb_ewm);  //调用图片上传的方法
                else
                    return json(['code' => 1, 'info' => yuylangs('tpgscw')]);
                $data['zfb_ewm'] = $zfb_ewm;
            }


            $res = db('xy_users')->where('id', $this->usder_id)->update($data);
            if ($res !== false) {
                if ($headpic) session('avatar', $headpic);
                return json(['code' => 0, 'info' => yuylangs('czcg')]);
            } else {
                return json(['code' => 1, 'info' => yuylangs('czsb')]);
            }
        } elseif (request()->isGet()) {
            $info = db('xy_users')->field('username,headpic,nickname,signiture sign,wx_ewm,zfb_ewm')->find($this->usder_id);
            return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => $info]);
        }
    }

    // 消息
    public function activity()
    {
        $where[] = ['title', 'like', '%' . '活动' . '%'];

        $this->msg = db('xy_index_msg')->where($where)->select();
        return $this->fetch();
    }


    // 消息
    public function msg()
    {
       $parameter['info'] = db('xy_message')->alias('m')
           // ->leftJoin('xy_users u','u.id=m.sid')
           // ->leftJoin('xy_reads r', 'r.mid=m.id and r.uid=' . $this->usder_id)
            ->field('m.*')
            ->where('m.uid', 'in', [0, $this->usder_id])
            ->order('addtime desc')
            ->select();

       // $this->msg = db('xy_index_msg')->where('status', 1)->select();
      return json_encode(['code'=>0,'msg'=>'success','data'=>$parameter]);
        return $this->fetch();
    }

    // 消息
    public function detail()
    {
        $id = input('get.id/d', 0);

        $msg = db('xy_index_msg')->where('id', $id)->find();
        $lang= $this->language;
        
        $content = $msg[$lang];
        $title = $msg["t_".$lang];
        $data['content'] = $content;
        $data['title'] = $title;
        
        return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => $data]);
    }

    //记录阅读情况
    public function reads()
    {
        if (\request()->isPost()) {
            $id = input('post.id/d', 0);
            db('xy_reads')->insert(['mid' => $id, 'uid' => $this->usder_id, 'addtime' => time()]);
            return $this->success('成功');
        }
    }
    
    
    //修改头像
    public function headpicUpdatae()
    {
        $url = input("url",'');
        $uid = $this->usder_id;
        
        $res = Db::table("xy_users")->where(['id'=>$uid])->update(['headpic'=>$url]);
        if($res){
             return json(['code' => 0, 'info' => yuylangs('czcg')]);
        }else{
             return json(['code' => 1, 'info' => yuylangs('czsb')]);
        }
    }

    public function gonggao()
    {

    }

    //修改绑定手机号
    public function reset_tel()
    {
        $pwd = input('post.pwd', '');
        $verify = input('post.verify/s', '');
        $tel = input('post.tel/s', '');
        $userinfo = Db::table('xy_users')->field('id,pwd,salt')->find($this->usder_id);
        if ($userinfo['pwd'] != sha1($pwd . $userinfo['salt'] . config('pwd_str'))) return json(['code' => 1, 'info' => yuylangs('pass_error')]);
        if (config('app.verify')) {
            $verify_msg = Db::table('xy_verify_msg')->field('msg,addtime')->where(['tel' => $tel, 'type' => 3])->find();
            if (!$verify_msg) return json(['code' => 1, 'info' => yuylangs('yzmbcz')]);
            if ($verify != $verify_msg['msg']) return json(['code' => 1, 'info' => yuylangs('yzmcw')]);
            if (($verify_msg['addtime'] + (config('app.zhangjun_sms.min') * 60)) < time()) return json(['code' => 1, 'info' => yuylangs('yzmysx')]);
        }
        $res = db('xy_users')->where('id', $this->usder_id)->update(['tel' => $tel]);
        if ($res !== false)
            return json(['code' => 0, 'info' => yuylangs('czcg')]);
        else
            return json(['code' => 1, 'info' => yuylangs('czsb')]);
    }

    //团队佣金列表
    public function get_team_reward()
    {
        $uid = $this->usder_id;
        $lv = input('post.lv/d', 1);
        $num = Db::name('xy_reward_log')->where('uid', $uid)->where('addtime', 'between', strtotime(date('Y-m-d')) . ',' . time())->where('lv', $lv)->where('status', 1)->sum('num');

        if ($num) return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => $num]);
        return json(['code' => 1, 'info' => yuylangs('zwsj')]);
    }
}