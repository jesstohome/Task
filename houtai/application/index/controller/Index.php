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
   protected $authentication = ["home",'getTongji','get_level_list','get_msg'];
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
        $parameter['balance'] = number_format($info['balance'],2);
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
        $parameter['yon1'] = number_format(Db::table('xy_balance_log')->where('uid', $uid)->where("type = 3 || type = 5 || type = 6")->where('addtime', 'between', [$beginToday, $endToday])->sum('num'),2);
        
         //昨日收益
       $parameter['Yesterdaysearnings'] = number_format(Db::table('xy_balance_log')->where('uid', $uid)->where("type = 3 || type = 5 || type = 6")->where('addtime', 'between', [$beginYesterday, $endYesterday])->sum('num'),2);
       
        //总收益
        $parameter['yon3'] = number_format(Db::table('xy_balance_log')->where('uid', $uid)->where("type = 3 || type = 5 || type = 6")->sum('num'),2);
        
        //订单收益
        $parameter['yon2'] = number_format(Db::table('xy_balance_log')->where('uid', $uid)->where("type = 3")->sum('num'),2);
        
      
        
        //团队收益
        $parameter['Teambenefits'] = number_format(Db::table('xy_balance_log')->where('uid', $uid)->where(" type = 5 || type = 6")->sum('num'),2);
        
        
        
        
        
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
