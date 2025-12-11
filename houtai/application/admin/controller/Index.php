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

use app\admin\service\NodeService;
use library\Controller;
use library\tools\Data;
use think\Console;
use think\Db;
use think\exception\HttpResponseException;

/**
 * 系统公共操作
 * Class Index
 * @package app\admin\controller
 */
class Index extends Base
{

    /**
     * 显示后台首页
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $this->title = '系统管理后台';
        NodeService::applyUserAuth(true);
        $this->menus = NodeService::getMenuNodeTree();
//        $this->menus1 = NodeService::getMenuNodeTree1();
        if (empty($this->menus) && !NodeService::islogin()) {
            $this->redirect('@admin/login');
        } else {
            $this->fetch();
        }
    }

    public function test()
    {
        NodeService::applyUserAuth(true);
        $this->menus = NodeService::getMenuNodeTree();
        echo json_encode($this->menus);
    }

    /**
     * 后台首页
     * @auth true
     * @menu true
     */
    public function main()
    {
        $this->title = '统计数据';
        $type = input('type/s', '');

        if ($type == 'shop') {
           $timeData = input('addtime','');
            return $this->index_shop($timeData);
        } elseif ($type == 'agent') {
            return $this->index_agent();
        }

        //用户
        $agent_id = model('admin/Users')->get_admin_agent_id();
        $tuigljie = "";
        if ($agent_id) {
            $adminData = Db::table("system_user")->find($agent_id);
//            $code = Db::table("xy_users")->where("id",$adminData["user_id"])->value("invite_code");
            $tuigljie = sysconf('web_url').'/register?type=2&invite_code='.$adminData['invite_code'];;
        }
        $this->agent_id = $agent_id;
        $this->assign("tuigljie",$tuigljie);
        $this->fetch();
    }

    private function getAgentWhere($pix = '')
    {
        $where = [];
        if (input('addtime/s', '')) {
            $arr = explode(' - ', input('addtime/s', ''));
            $where[] = [$pix . 'addtime', 'between', [strtotime($arr[0]), strtotime($arr[1]) + 86400]];
        }
        return $where;
    }

    private function index_agent()
    {
        $agent_id = model('admin/Users')->get_admin_agent_id();
        if ($agent_id) {
            return $this->index_agent_service($agent_id);
        }//
        $this->list = Db::name('system_user')->field('id,username,user_id')
           // ->where('user_id', 0)
            ->where('authorize', 2)
            ->where('is_deleted', 0)
            ->select();
          
          $addtime = input("addtime");
          $where1 = "1=1";
          if($addtime){
              $times = explode(" - ",$addtime);
              $time1 = strtotime($times[0]);
              $time2 = strtotime($times[1]);
              $where1 = "c.addtime >= $time1 && c.addtime <= $time2";
          }
          
        $today = strtotime(date('Y-m-d'));
        foreach ($this->list as $k => $v) {
            $this->list[$k]['service_count'] = Db::name('system_user')->alias('su')
                ->join('xy_users u', 'su.user_id=u.id')
                ->where('u.agent_service_id', $v['id'])
                ->where($this->getAgentWhere('u.'))
                ->count('u.id');
                
           
             $ids =  implode(",",model('admin/Users')->child_user($v['user_id'],5));  
            
            $this->list[$k]['user_count'] = Db::name('xy_users')
                ->where('id','in' ,$ids)

                ->where($this->getAgentWhere())
                ->count('id');
                
           
                
                
            $this->list[$k]['user_balance'] = Db::name('xy_users')
                ->where('id','in' ,$ids)
   
               
                ->where($this->getAgentWhere())
                ->sum('balance');
            
                
                
            $this->list[$k]['recharge_count'] = Db::name('xy_recharge c')
                ->leftJoin('xy_users u', 'u.id=c.uid')
                ->where('u.id','in' ,$ids)
     
                ->where($this->getAgentWhere('c.'))
                ->where('c.status', 2)
                ->sum('c.num');
            
                
                
                
            $this->list[$k]['today_recharge_count'] = Db::name('xy_recharge c')
                ->leftJoin('xy_users u', 'u.id=c.uid')
                ->where('u.id','in' ,$ids)
      
                ->where('c.status', 2)
                ->where($where1)
                ->sum('c.num');
            
                
                
            $this->list[$k]['deposit_count'] = Db::name('xy_deposit c')
                ->leftJoin('xy_users u', 'u.id=c.uid')
                 ->where('u.id','in' ,$ids)
       
                ->where('c.status', 2)
                ->where($this->getAgentWhere('c.'))
                ->sum('c.num');
            
                
                
            $this->list[$k]['today_deposit_count'] = Db::name('xy_deposit c')
                ->leftJoin('xy_users u', 'u.id=c.uid')
                ->where('u.id','in' ,$ids)
       
                ->where('c.status', 2)
                ->where($where1)
                ->sum('c.num');
            
        }
       
        return $this->fetch('index_agent');
    }

    private function index_agent_service($agent_id)
    {
        $this->list = Db::name('system_user')
            ->where('id', $agent_id)
            ->field('id,username')
            ->find();
        $today = strtotime(date('Y-m-d'));
        $this->list['user_count'] = Db::name('xy_users')
            ->where('agent_service_id', $agent_id)
            ->where($this->getAgentWhere())
            ->count('id');
        $this->list['user_balance'] = Db::name('xy_users')
            ->where('agent_service_id',$agent_id)
            ->where($this->getAgentWhere())
            ->sum('balance');
        $this->list['recharge_count'] = Db::name('xy_recharge c')
            ->leftJoin('xy_users u', 'u.id=c.uid')
            ->where('u.agent_service_id',$agent_id)
            ->where($this->getAgentWhere('c.'))
            ->where('c.status', 2)
            ->sum('c.num');
        $this->list['today_recharge_count'] = Db::name('xy_recharge c')
            ->leftJoin('xy_users u', 'u.id=c.uid')
            ->where('u.agent_service_id', $agent_id)
            ->where('c.status', 2)
            ->where('c.addtime', '>', $today)
            ->sum('c.num');
        $this->list['deposit_count'] = Db::name('xy_deposit c')
            ->leftJoin('xy_users u', 'u.id=c.uid')
            ->where('u.agent_service_id', $agent_id)
            ->where($this->getAgentWhere('c.'))
            ->where('c.status', 2)
            ->sum('c.num');
        $this->list['today_deposit_count'] = Db::name('xy_deposit c')
            ->leftJoin('xy_users u', 'u.id=c.uid')
            ->where('u.agent_service_id', $agent_id)
            ->where('c.status', 2)
            ->where('c.addtime', '>', $today)
            ->sum('c.num');
        return $this->fetch('index_agent_service');
    }

//    private function index_agent_service($agent_id)
//    {
//        $agent_user_id = model('admin/Users')->get_admin_agent_uid();
//
//        if ($agent_user_id) return '暂无数据';
//        $this->list = Db::name('system_user')
//            ->alias('su')
//            ->join('xy_users u', 'su.user_id=u.id')
//            ->where('u.id', $agent_id)
////            ->where('u.agent_id', $agent_id)
//            ->field('su.id,u.username')->select();
//        $today = strtotime(date('Y-m-d'));
//        foreach ($this->list as $k => $v) {
//            $this->list[$k]['user_count'] = Db::name('xy_users')
//                ->where('agent_service_id', $v['id'])
//                ->where($this->getAgentWhere())
//                ->count('id');
//            $this->list[$k]['user_balance'] = Db::name('xy_users')
//                ->where('agent_service_id', $v['id'])
//
//                ->where($this->getAgentWhere())
//                ->sum('balance');
//            $this->list[$k]['recharge_count'] = Db::name('xy_recharge c')
//                ->leftJoin('xy_users u', 'u.id=c.uid')
//                ->where('u.agent_service_id', $v['id'])
//                ->where($this->getAgentWhere('c.'))
//                ->where('c.status', 2)
//                ->sum('c.num');
//            $this->list[$k]['today_recharge_count'] = Db::name('xy_recharge c')
//                ->leftJoin('xy_users u', 'u.id=c.uid')
//                ->where('u.agent_service_id', $v['id'])
//                ->where('c.status', 2)
//                ->where('c.addtime', '>', $today)
//                ->sum('c.num');
//            $this->list[$k]['deposit_count'] = Db::name('xy_deposit c')
//                ->leftJoin('xy_users u', 'u.id=c.uid')
//                ->where('u.agent_service_id', $v['id'])
//                ->where($this->getAgentWhere('c.'))
//                ->where('c.status', 2)
//                ->sum('c.num');
//            $this->list[$k]['today_deposit_count'] = Db::name('xy_deposit c')
//                ->leftJoin('xy_users u', 'u.id=c.uid')
//                ->where('u.agent_service_id', $v['id'])
//                ->where('c.status', 2)
//                ->where('c.addtime', '>', $today)
//                ->sum('c.num');
//        }
//        return $this->fetch('index_agent_service');
//    }

    private function index_shop($timeData='')
    {
        $this->think_ver = \think\App::VERSION;
        $this->mysql_ver = Db::query('select version() as ver')[0]['ver'];
        //昨天
        $yes1 = strtotime(date("Y-m-d 00:00:00", strtotime("-1 day")));
        $yes2 = strtotime(date("Y-m-d 23:59:59", strtotime("-1 day")));
        
        
        $addtimes1 = "0";
        $addtimes2 = time();
        if($timeData){
            $timeRes = explode(" - ",$timeData);
            $addtimes1 = strtotime($timeRes[0]);
            $addtimes2 = strtotime($timeRes[1])+86400;
        }

        //$this->goods_num = Db::name('xy_goods_list')->count('id');
        //$this->today_goods_num = Db::name('xy_goods_list')->where('addtime', 'between', [strtotime(date('Y-m-d')), time()])->count('id');
        //$this->yes_goods_num = Db::name('xy_goods_list')->where('addtime', 'between', [$yes1, $yes2])->count('id');
      
     
        //用户
        $agent_id = model('admin/Users')->get_admin_agent_id();
      
        if ($agent_id) {
            // $agent_user_id = model('admin/Users')->get_admin_agent_uid();
            // if ($agent_user_id) {
             $ids =  implode(",",model('admin/Users')->child_user(session('admin_user')['user_id'],5));
         
          
             
            

                $this->users_num = Db::name('xy_users')
                ->where('addtime', 'between', [$addtimes1,$addtimes2])
                  ->where('id','in', $ids)
                    
                    ->count('id');
              
               

                //订单数量
                $this->order_num = Db::name('xy_convey')->alias('c')
                    ->where('c.addtime', 'between', [$addtimes1,$addtimes2])
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('u.id','in', $ids)
                    
                    ->count('c.id');
              
               

                //订单总额
                $this->order_sum = Db::name('xy_convey c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('c.addtime', 'between', [$addtimes1,$addtimes2])
                   ->where('u.id','in', $ids)
                    ->sum('c.num');
              
              

                //充值
                $this->user_recharge = Db::name('xy_recharge c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('c.addtime', 'between', [$addtimes1,$addtimes2])
                   ->where('u.id','in', $ids)
                    ->where('c.status', 2)->sum('c.num');
               
               

                $this->user_recharge_people = Db::name('xy_recharge c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('c.addtime', 'between', [$addtimes1,$addtimes2])
                    ->where('u.id','in', $ids)
                    ->where('c.status', 2)
                    
                    ->count('distinct c.uid');
               
              
                $this->user_deposit_people = Db::name('xy_deposit c')
                    ->where('c.addtime', 'between', [$addtimes1,$addtimes2])
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('u.id','in', $ids)
                    ->where('c.status', 2)
                    
                    ->count('distinct c.uid');
               
               

                //提现
                $this->user_deposit = Db::name('xy_deposit c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('u.id','in', $ids)
                    ->where('c.addtime', 'between', [$addtimes1,$addtimes2])
                    ->where('c.status', 2)->sum('c.num');
               
              

                //抢单佣金
                $this->user_yongjin = Db::name('xy_convey c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('c.addtime', 'between', [$addtimes1,$addtimes2])
                    ->where('u.id','in', $ids)
                    ->where('c.status', 1)
                    
                    ->sum('c.commission');
              
            

                //利息宝
                $this->user_lixibao = Db::name('xy_lixibao c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('u.id','in', $ids)
                    ->where('c.type', 1)
                    ->where('c.addtime', 'between', [$addtimes1,$addtimes2])
                    ->where('c.is_sy', 0)->sum('c.num');
               
               

                //下级返佣
                $this->user_fanyong = Db::name('xy_balance_log c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('c.addtime', 'between', [$addtimes1,$addtimes2])
                    ->where('u.id','in', $ids)
                    ->where('c.type', 6)
                    ->where('c.status', 1)
                    
                    ->sum('c.num');
               
               

                //用户余额
                $this->user_yue = Db::name('xy_users')
                 
                    ->where('addtime', 'between', [$addtimes1,$addtimes2])
                    ->where('id','in', $ids)
                    ->sum('balance');
                $this->user_djyue = Db::name('xy_users')
                  
                    ->where('addtime', 'between', [$addtimes1,$addtimes2])
                    ->where('id','in', $ids)
                    ->sum('freeze_balance');
                $this->today_lxbsy = Db::name('xy_balance_log c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('u.id','in', $ids)
                    ->where('c.type', 23)
                    
                    ->where('c.status', 1)
                    ->where('c.addtime', 'between', [strtotime(date('Y-m-d')), time()])
                    ->sum('c.num');
                $this->today_lxbzc = Db::name('xy_balance_log c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('u.id','in', $ids)
                    ->where('c.type', 22)
                    
                    ->where('c.status', 1)
                    ->where('c.addtime', 'between', [strtotime(date('Y-m-d')), time()])
                    ->sum('c.num');
                    
               
            
            
        }  else {
            
        
              


                $this->users_num = Db::name('xy_users')
                    ->where('addtime', 'between', [$addtimes1,$addtimes2])
                    ->count('id');
                    
               
              

                //订单数量
                $this->order_num = Db::name('xy_convey')->alias('c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('c.addtime', 'between', [$addtimes1,$addtimes2])
                    ->count('c.id');
               
               
                //订单总额
                $this->order_sum = Db::name('xy_convey c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('c.addtime', 'between', [$addtimes1,$addtimes2])
                    ->sum('c.num');
               
               

                $this->order_sum_people = Db::name('xy_convey c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    
                    ->count('distinct uid');
              
              
                $this->user_recharge_people = Db::name('xy_recharge c')
                ->where('c.addtime', 'between', [$addtimes1,$addtimes2])
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    
                    ->where('c.status', 2)
                    ->count('distinct uid');
               
               

                //充值
                $this->user_recharge = Db::name('xy_recharge c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('c.addtime', 'between', [$addtimes1,$addtimes2])
                    ->where('c.status', 2)
                    ->sum('c.num');
               
              

                $this->user_recharge_people = Db::name('xy_recharge c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('c.addtime', 'between', [$addtimes1,$addtimes2])
                    ->where('c.status', 2)
                    ->count('distinct uid');
              
               
                $this->user_deposit_people = Db::name('xy_deposit c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('c.addtime', 'between', [$addtimes1,$addtimes2])
                    ->where('c.status', 2)
                    ->count('distinct c.uid');
              
             

                //提现
                $this->user_deposit = Db::name('xy_deposit c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('c.addtime', 'between', [$addtimes1,$addtimes2])
                    ->where('c.status', 2)
                    ->sum('c.num');
              
            

                //抢单佣金
                $this->user_yongjin = Db::name('xy_convey c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('c.addtime', 'between', [$addtimes1,$addtimes2])
                    ->where('c.status', 1)
                    ->sum('c.commission');
              
              
                //利息宝
                $this->user_lixibao = Db::name('xy_lixibao c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('c.addtime', 'between', [$addtimes1,$addtimes2])
                    ->where('c.type', 1)
                    ->where('c.is_sy', 0)->sum('c.num');
               
               

                //下级返佣
                $this->user_fanyong = Db::name('xy_balance_log c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('c.addtime', 'between', [$addtimes1,$addtimes2])
                    ->where('c.type', 6)
                    ->where('c.status', 1)
                    ->sum('c.num');
                
               

                //用户余额
                $this->user_yue = Db::name('xy_users')
                 
                    ->where('addtime', 'between', [$addtimes1,$addtimes2])
                    ->sum('balance');
                $this->user_djyue = Db::name('xy_users')
                   ->where('addtime', 'between', [$addtimes1,$addtimes2])
                    
                    ->sum('freeze_balance');
                $this->today_lxbsy = Db::name('xy_balance_log c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    
                    ->where('c.type', 23)
                    ->where('c.status', 1)
                    ->where('c.addtime', 'between', [strtotime(date('Y-m-d')), time()])
                    ->sum('c.num');
                $this->today_lxbzc = Db::name('xy_balance_log c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    
                    ->where('c.type', 22)
                    ->where('c.status', 1)
                    ->where('c.addtime', 'between', [strtotime(date('Y-m-d')), time()])
                    ->sum('c.num');
                    
                //假    
                
                
               
           

           
        }
        $isVersion = '';
        if (!session('check_update_version')) {
            $isVersion = $this->Update(1);
        }
        $this->assign('has_version', $isVersion);
        return $this->fetch('index_shop');
    }

    /**
     * 修改密码
     * @param integer $id
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function pass($id)
    {
        $this->applyCsrfToken();
        if (intval($id) !== intval(session('admin_user.id'))) {
            $this->error('只能修改当前用户的密码！');
        }
        if (!NodeService::islogin()) {
            $this->error('需要登录才能操作哦！');
        }
        if ($this->request->isGet()) {
            $this->verify = true;
            $this->_form('SystemUser', 'admin@user/pass', 'id', [], ['id' => $id]);
        } else {
            $data = $this->_input([
                'password' => $this->request->post('password'),
                'repassword' => $this->request->post('repassword'),
                'oldpassword' => $this->request->post('oldpassword'),
            ], [
                'oldpassword' => 'require',
                'password' => 'require|min:4',
                'repassword' => 'require|confirm:password',
            ], [
                'oldpassword.require' => '旧密码不能为空！',
                'password.require' => '登录密码不能为空！',
                'password.min' => '登录密码长度不能少于4位有效字符！',
                'repassword.require' => '重复密码不能为空！',
                'repassword.confirm' => '重复密码与登录密码不匹配，请重新输入！',
            ]);
            $user = Db::name('SystemUser')->where(['id' => $id])->find();
            if (md5($data['oldpassword']) !== $user['password']) {
                $this->error('旧密码验证失败，请重新输入！');
            }
            $result = NodeService::checkpwd($data['password']);
            if (empty($result['code'])) $this->error($result['msg']);
            if (Data::save('SystemUser', ['id' => $user['id'], 'password' => md5($data['password'])])) {
                $this->success('密码修改成功，下次请使用新密码登录！', '');
            } else {
                $this->error('密码修改失败，请稍候再试！');
            }
        }
    }

    /**
     * 修改用户资料
     * @param integer $id 会员ID
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function info($id = 0)
    {
        if (!NodeService::islogin()) {
            $this->error('需要登录才能操作哦！');
        }
        $this->applyCsrfToken();
        if (intval($id) === intval(session('admin_user.id'))) {
            $this->_form('SystemUser', 'admin@user/form', 'id', [], ['id' => $id]);
        } else {
            $this->error('只能修改登录用户的资料！');
        }
    }

    /**
     * 清理运行缓存
     * @auth true
     */
    public function clearRuntime()
    {
        if (!NodeService::islogin()) {
            $this->error('需要登录才能操作哦！');
        }
        try {
            Console::call('clear');
            Console::call('xclean:session');
            $this->success('清理运行缓存成功！');
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $e) {
            $this->error("清理运行缓存失败，{$e->getMessage()}");
        }
    }

    /**
     * 压缩发布系统
     * @auth true
     */
    public function buildOptimize()
    {
        if (!NodeService::islogin()) {
            $this->error('需要登录才能操作哦！');
        }
        try {
            Console::call('optimize:route');
            Console::call('optimize:schema');
            Console::call('optimize:autoload');
            Console::call('optimize:config');
            $this->success('压缩发布成功！');
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $e) {
            $this->error("压缩发布失败，{$e->getMessage()}");
        }
    }

    /**
     * 检查更新
     * @auth true
     */
    public function Update($isreturn)
    {
        $version = config("version");
        $isHtml = $isreturn ? 0 : 1;
        $con = '已经是最新版';
        session('check_update_version', 1);
        if ($isreturn) return $con;

        echo $con;
        die;
    }

    /**
     * 获取充值与提现数量
     * @auth true
     */
    public function order_info()
    {
        if (!NodeService::islogin()) {
            $this->error('需要登录才能操作哦！');
        }

        $agent_id = model('admin/Users')->get_admin_agent_id();
        if ($agent_id) {
            $agent_user_id = model('admin/Users')->get_admin_agent_uid();
            if ($agent_user_id) {
                $deposit = Db::name('xy_deposit c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('u.agent_service_id', $agent_id)
                    ->where('c.status', 1)
                    
                    ->count('c.id');
                $recharge = Db::name('xy_recharge c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('u.agent_service_id', $agent_id)
                    ->where('c.status', 1)
                    
                    ->count('c.id');
                    $deposit1 = Db::name('xy_deposit c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('u.agent_service_id', $agent_id)
                    ->where('c.status', 1)
                 //   ->where("u.is_jia = 1")
                    ->count('c.id');
                $recharge1 = Db::name('xy_recharge c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('u.agent_service_id', $agent_id)
                    ->where('c.status', 1)
                 //   ->where("u.is_jia = 1")
                    ->count('c.id');
            } else {
                $deposit = Db::name('xy_deposit c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('u.agent_id', $agent_id)
                    ->where('c.status', 1)
                    
                    ->count('c.id');
                $recharge = Db::name('xy_recharge c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('u.agent_id', $agent_id)
                    ->where('c.status', 1)
                    
                    ->count('c.id');
                    $deposit1 = Db::name('xy_deposit c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('u.agent_id', $agent_id)
                    ->where('c.status', 1)
                 //   ->where("u.is_jia = 1")
                    ->count('c.id');
                $recharge1 = Db::name('xy_recharge c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('u.agent_id', $agent_id)
                    ->where('c.status', 1)
                 //   ->where("u.is_jia = 1")
                    ->count('c.id');
            }
        } else {
            $deposit = Db::name('xy_deposit c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('c.status', 1)
                    
                    ->count('c.id');
            $recharge = Db::name('xy_recharge c')
                ->leftJoin('xy_users u', 'u.id=c.uid')
                ->where('c.status', 1)
                
                ->count('c.id');
                
                $deposit1 = Db::name('xy_deposit c')
                    ->leftJoin('xy_users u', 'u.id=c.uid')
                    ->where('c.status', 1)
                  //  ->where("u.is_jia = 1")
                    ->count('c.id');
            $recharge1 = Db::name('xy_recharge c')
                ->leftJoin('xy_users u', 'u.id=c.uid')
                ->where('c.status', 1)
             //   ->where("u.is_jia = 1")
                ->count('c.id');
            
        }
        echo json_encode(['deposit' => $deposit, 'recharge' => $recharge,'deposit1' => $deposit1, 'recharge1' => $recharge1, 'date' => date('Y-m-d H:i:s')]);

    }

    public function clear()
    {
        $isVersion = $this->Update(0);
    }

}
