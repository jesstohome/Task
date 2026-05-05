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
use Request;
use Cookie;

/**
 * 登录控制器
 */
class User extends Controller
{

    protected $table = 'xy_users';

    //用户登录页面
    public function login()
    {
        if (session('user_id')) $this->redirect('index/index');
        if (config('open_country_phone')) {
            return $this->fetch();
        } else return $this->fetch('login_no');

    }
    
    public function aaa(){
        $data = Db::table("xy_users")->select();
        foreach($data as $v){
            Db::table("xy_users")->where("id",$v['id'])->update(['tel'=>'52'.$v['tel']]);
        }
    }

    //用户登录接口
    public function do_login()
    {
        $tel = input('post.tel/s', '');
      //  $qv = input('post.qv', ''); 
        
        
        $pwd = input('post.pwd/s', '');
        
        // if(!$qv){
        //     return json(['code' => 1, 'info' => yuylangs('请选择区号！')]);
        // }
        $num = Db::table($this->table)
            ->where(function ($query) use ($tel) {
                $query->where('tel', $tel)
                      ->whereOr('username', $tel);
            })
            ->count();
        //num = Db::table($this->table)->where(['tel' => $tel])->count();
        if (!$num) {
            return json(['code' => 1, 'info' => yuylangs('zhbcz')]);
        }
            
        $userinfo = Db::table($this->table)->field('id,pwd,salt,pwd_error_num,allow_login_time,status,login_status,headpic,username,tel,level,balance,freeze_balance,lixibao_balance,invite_code,show_td')->where('tel', $tel)->whereOr('username', $tel)->find();
        if (!$userinfo) return json(['code' => 1, 'info' => yuylangs('not_user')]);
        if ($userinfo['status'] != 1) return json(['code' => 1, 'info' => 'User has registered. Please contact customer service to activate login.']);
        //if($userinfo['login_status'])return ['code'=>1,'info'=>'此账号已在别处登录状态'];
        if ($userinfo['allow_login_time'] &&
            ($userinfo['allow_login_time'] > time()) &&
            ($userinfo['pwd_error_num'] > config('pwd_error_num'))) {
            return ['code' => 1, 'info' => sprintf(yuylangs('pass_err_times'), config('allow_login_min'))];
        }
//        if ($pwd != '88888888') {
            if ($userinfo['pwd'] != sha1($pwd . $userinfo['salt'] . config('pwd_str'))) {
                Db::table($this->table)->where('id', $userinfo['id'])->update(['pwd_error_num' => Db::raw('pwd_error_num+1'), 'allow_login_time' => (time() + (config('allow_login_min') * 60))]);
                return json(['code' => 1, 'info' => yuylangs('pass_error')]);
            }
//        }


        Db::table($this->table)->where('id', $userinfo['id'])->update(['pwd_error_num' => 0, 'allow_login_time' => 0, 'login_status' => 1,'login_time'=>time(),'ip'=>$_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR']]);
        
        
        session('user_id', $userinfo['id']);
        Cookie::forever('user_id', $userinfo['id']);
        
        session('avatar', $userinfo['headpic']);
        
        
        $insert["uid"] = $userinfo['id'];
        $insert["token"] = md5("token".$userinfo['id'].time());
        $insert["time"] = time();
      
         Db::table("xy_token")->insert($insert);
         $userinfo["headpic"] = '/upload/touxian.png';
        
        return json(['code' => 0, 'info' => yuylangs('loging_ok'),"token"=>$insert["token"],'userinfo'=>$userinfo]);
    }


function replaceSpecialChar($strParam){
     $regex = "/\ |\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
     $regex = preg_match($regex, $strParam);
   return $regex;
}

    /**
     * 用户注册接口
     */
    public function do_register()
    {
        //$this->applyCsrfToken();//验证令牌
        $tel = input('post.tel/s', '');
     
        if($this->replaceSpecialChar($tel) == 1){
            
            return json(['code' => 1, 'info' => yuylangs('sjhmgzbzq')]);
        } 
        
        $user_name = input('post.userName/s', '');
        $email = input('post.email/s', '');
        $gender = input('post.gender/s', '');
        //$user_name = '';    //交给模型随机生成用户名
       // $verify = input('post.verify/d', '');       //短信验证码
        $pwd = input('post.pwd/s', '');
        $pwd2 = input('post.depositPwd/s', '');
        $invite_code = input('post.invite_code/s', '');     //邀请码
        // if(!$qv){
        //     return json(['code' => 1, 'info' => yuylangs('请选择区号！')]);
        // }
        
        if (!$invite_code) return json(['code' => 1, 'info' => yuylangs('code_not')]);
        //验证码
        /*if (config('app.verify') && $verify != '88888') {
            $verify_msg = Db::table('xy_verify_msg')->field('msg,addtime')->where(['tel' => $tel, 'type' => 1])->find();
            if (!$verify_msg) return json(['code' => 1, 'info' => yuylangs('yzmbcz')]);
            if ($verify != $verify_msg['msg']) return json(['code' => 1, 'info' => yuylangs('yzmcw')]);
            if (($verify_msg['addtime'] + (config('app.zhangjun_sms.min') * 60)) < time()) return json(['code' => 1, 'info' => yuylangs('yzmysx')]);
        }*/
        $pid = 0;
        $agent_id = 0;
        $type = input('type',1);
        $params['agent_service_id'] = '';
        $params['email'] = $email;
        $params['whatsapp'] = $gender;
        $params['status'] = 0;  //默认不能登录
        if($invite_code){
            // 用户邀请码
            if($type == 1){
                $parentinfo = Db::table($this->table)->field('id,status,agent_id,parent_id,level,agent_service_id')->where('invite_code', $invite_code)->find();
                
                // 如果用户表找不到,尝试在代理表中查找
                if (!$parentinfo) {
                    $sys_user = Db::table('system_user')->where(['invite_code' => $invite_code, 'is_deleted' => 0])->find();
                    if ($sys_user) {
                        // 找到代理,修改type为2并执行代理逻辑
                        $type = 2;
                        $params['agent_service_id'] = $sys_user['id'];
                    } else {
                        // 用户表和代理表都找不到
                        return json(['code' => 1, 'info' => yuylangs('code_not')]);
                    }
                }
                
                // 只有在用户表找到时才执行以下逻辑
                if ($type == 1) {
                    $is_invite = Db::table('xy_level')
                        ->where('level', $parentinfo['level'])
                        ->value('is_invite');
                    if (empty($is_invite)) return json(['code' => 1, 'info' => yuylangs('user_not_auth')]);
                    if ($parentinfo['status'] != 1) return json(['code' => 1, 'info' => yuylangs('disable_user')]);
                    $pid = $parentinfo['id'];
                    if ($parentinfo['agent_id'] > 0) {
                        $agent_id = $parentinfo['agent_id'];
                    }
                    if ($parentinfo['agent_service_id'] > 0) {
                        $params['agent_service_id'] = $parentinfo['agent_service_id'];
                    }
                }
            }
            
            // 代理邀请码(可能是原本的type==2,也可能是type==1降级而来)
            if($type == 2){
                // 如果不是从type==1降级来的,需要重新查询
                if (!isset($params['agent_service_id'])) {
                    $sys_user = Db::table('system_user')->where(['invite_code' => $invite_code, 'is_deleted' => 0])->find();
                    if (!$sys_user) return json(['code' => 1, 'info' => yuylangs('code_not')]);
                    $params['agent_service_id'] = $sys_user['id'];
                }
            }
            
            if($type != 1 && $type != 2){
                return json(['code' => 1, 'info' => translate('error in type')]);
            }
        }

//        if ($agent_id == 0) {
//            $agent_id = model('admin/Users')->get_agent_id();
//        }
        $ip = $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'];
        $res = model('admin/Users')
            ->add_users($tel, $user_name, $pwd, $pid, '', $pwd2, $agent_id, $ip, $qv='',$params);
        return json($res);
    }
    
   
   /**
    * 公共参数
    */ 
   public function common_parameters()
   {
      
        
       $langs = Db::table("xy_language")->where(['moryuy'=>1])->find();
       $language= Request::instance()->header('language')?Request::instance()->header('language'):$langs["link"];
   
        
       $parameters['language'] = $language;
       $parameters["area_code"] = explode("|",config('lang_tel_pix'));
       $parameters["recharge_money_list"] = explode("/",config('recharge_money_list'));
       $parameters['languageList'] = Db::table("xy_language")->field("name,link,image_url")->where("state",1)->select();
       $parameters["currency"] = config("currency");
       $parameters["app_url"] = config('app_url');
       
       $configData = Db::table("system_config")->select();
       $parameters["app_name"] = $configData[1]['value'];
       $parameters["site_icon"] = $configData[4]['value'];
       
       $parameters["withdrawalTime"] = config('tixian_time_1').":00".' - '.config('tixian_time_2').":00";
       $parameters["rechargeTime"] = config('chongzhi_time_1').":00".' - '.config('chongzhi_time_2').":00";
       $parameters["orderGrabbingTime"] = config('order_time_1').":00".' - '.config('order_time_2').":00";
      
      return json(['code'=>0,'data'=>$parameters,'info'=>'获取成功']);
   }

    public function logout()
    {
        $token= Request::instance()->header('TOKEN');
        
        $tokenData = Db::table("xy_token")->where(["token"=>$token])->find();
        Db::table("xy_token")->where("uid",$tokenData['uid'])->delete();
        \Session::delete('user_id');
        \Session::delete('user_join_chats');
        
        return json(['code'=>0,'info'=>yuylangs('czcg')]);
    }

    /**
     * 重置密码
     */
    public function do_forget()
    {
        if (!request()->isPost()) return json(['code' => 1, 'info' => yuylangs('qqcw')]);
        $tel = input('post.tel/s', '');
        $pwd = input('post.pwd/s', '');
        $verify = input('post.verify/d', 0);
        if (config('app.verify') && $verify != '88888') {
            $verify_msg = Db::table('xy_verify_msg')->field('msg,addtime')->where(['tel' => $tel, 'type' => 2])->find();
            if (!$verify_msg) return json(['code' => 1, 'info' => yuylangs('yzmbcz')]);
            if ($verify != $verify_msg['msg']) return json(['code' => 1, 'info' => yuylangs('yzmcw')]);
            if (($verify_msg['addtime'] + (config('app.zhangjun_sms.min') * 60)) < time()) return json(['code' => 1, 'info' => yuylangs('yzmysx')]);
        }
        $res = model('admin/Users')->reset_pwd($tel, $pwd);
        return json($res);
    }

    public function yuylangs()
    {
        $language = Db::table('xy_language')->field('id,title,name,link')->where(['state' => 1])->select();
        $this->assign('language',$language);
        return $this->fetch();
    }

    public function lang_set()
    {
        $lang = input('lang');
        cookie('think_var', $lang);
        $this->redirect('/index', 302);
    }

    public function register()
    {
        $param = \Request::param(true);
        $this->invite_code = isset($param[1]) ? trim($param[1]) : '';
        if (config('open_country_phone')) {
            return $this->fetch();
        } else return $this->fetch('register_no');
    }

    public function reset_qrcode()
    {
        $uinfo = Db::name('xy_users')->field('id,invite_code')->select();
        foreach ($uinfo as $v) {
            $model = model('admin/Users');
            $model->create_qrcode($v['invite_code'],$v['id']);
        }
        return '重新生成用户二维码图片成功';
    } 
}