<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Request;

/**
 * 订单列表
 */
class Order extends Base
{


    public function index()
    {
        $parameter['status'] = $status = input('get.status/d', 0);
        $parameter["page"] = input("page",1);
        $parameter["size"] = input("size",10);
        
        $where = [];
        if ($status) {
            $status == -1 ? $status = 0 : '';
            $where['xc.status'] = $status;
        }
        $uid = $this->usder_id;
        $parameter['balance'] = Db::name('xy_users')
            ->where('id', $uid)->value('balance');//获取用户今日已充值金额
            
            
        $parameter['list'] = Db::table('xy_convey')
            ->alias('xc')
            ->join('xy_goods_list xg', 'xc.goods_id=xg.id')
            ->where('xc.uid', $this->usder_id)
            ->field('xc.*,xg.goods_name,xg.shop_name,xg.goods_price,xg.goods_pic,xc.rands,xc.group_count')
            ->order('xc.addtime desc') //xc.status asc,
            ->where($where)
            ->page($parameter["page"],$parameter["size"])
            ->select();
        
        
       
          $delete = "";
        foreach($parameter['list'] as $k=>&$value){
            $value['endtime'] = $value['endtime'] - (60*60 * config('equation_of_time'));
            $value['addtime'] = $value['addtime'] - (60*60 * config('equation_of_time'));
            $value['pay_time'] = $value['pay_time'] - (60*60 * config('equation_of_time'));
            $value['goods_price'] = number_format($value['goods_price'],2);
            $value['num'] = number_format($value['num'],2);
            $value['commission'] = number_format($value['commission'],2);

            $value["time_limit"] = $value['endtime'] - (time() - (60*60 * config('equation_of_time')));
            
            
            
            if($value["rands"]){
                if($delete == $value["rands"]){
                    unset($parameter['list'][$k]);
                }else{
                    $value["completedquantity"] = Db::table("xy_convey")->where(["rands"=>$value["rands"]])->where("is_pay",1)->count();
                    $delete = $value["rands"];
                }
            }
        }
      //  dump($parameter['list']);die;;
        
        $parameter['paging'] = 1;
        if(count($parameter['list']) < $parameter["size"]){
           $parameter['paging'] = 0; 
        } 
            
       return json_encode(['code'=>0,'msg'=>'success','data'=>$parameter]);

    }


    /**
     * 获取订单列表
     */
    public function order_list()
    {
        $page = input('post.page/d', 1);
        $num = input('post.num/d', 10);
        $limit = ((($page - 1) * $num) . ',' . $num);
        $type = input('post.type/d', 1);
        switch ($type) {
            case 1: //获取待处理订单
                $type = 0;
                break;
            case 2: //获取冻结中订单
                $type = 5;
                break;
            case 3: //获取已完成订单
                $type = 1;
                break;
        }
        $data = db('xy_convey')
            ->where('xc.uid', $this->usder_id)
            ->where('xc.status', $type)
            ->alias('xc')
            ->leftJoin('xy_goods_list xg', 'xc.goods_id=xg.id')
            ->field('xc.*,xg.goods_name,xg.shop_name,xg.goods_price,xg.goods_pic')
            ->order('xc.status asc,xc.addtime desc')
            ->limit($limit)
            ->select();

        foreach ($data as &$datum) {
            $datum['endtime'] = date('Y/m/d H:i:s', $datum['endtime']);
            $datum['addtime'] = date('Y/m/d H:i:s', $datum['addtime']);
        }


        if (!$data) json(['code' => 1, 'info' => yuylangs('zwsj')]);
        return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => $data]);
    }

    /**
     * 获取单笔订单详情
     */
    public function order_info()
    {
        if (\request()->isPost()) {
            $oid = input('post.id', '');
            if(is_array($oid)){
                $oid = $oid[0];
            }
            
            
            $oinfo = Db::table('xy_convey')
                ->alias('xc')
                ->leftJoin('xy_member_address ar', 'ar.uid=xc.uid', 'ar.is_default=1')
                ->leftJoin('xy_goods_list xg', 'xg.id=xc.goods_id')
                ->leftJoin('xy_users u', 'u.id=xc.uid')
                ->field('xc.id oid,xc.commission,xc.addtime,xc.endtime,xc.status,xc.num,xc.goods_count,xc.add_id,xg.goods_name,xg.goods_price,xg.shop_name,xg.goods_pic,ar.name,ar.tel,ar.address,u.balance,xc.group_rule_num,xc.group_zero,xc.group_id,xc.rands,xc.group_count,xc.duorw,xc.is_pay')
                ->where('xc.id', $oid)
                ->where('xc.uid', $this->usder_id)
                ->find();
            
         
            
            if (!$oinfo) return json(['code' => 1, yuylangs('zwsj')]);
            $oinfo['endtime'] = date('Y/m/d H:i:s', $oinfo['endtime'] - (60*60 * config('equation_of_time')));
            $oinfo['addtime'] = date('Y/m/d H:i:s', $oinfo['addtime'] - (60*60 * config('equation_of_time')));
            $oinfo['yuji'] = $oinfo['commission'] + $oinfo['num'];
            $oinfo['commission'] = number_format($oinfo['commission'],2);
            $oinfo['num'] = number_format($oinfo['num'],2);
            $oinfo['goods_price'] = number_format($oinfo['goods_price'],2);
            $oinfo['isluck'] = 0;
            
            //检验是否是方案组的幸运订单
            if($oinfo['group_id'] > 0 && $oinfo['group_rule_num'] >0){
                // $groupRule = Db::table("xy_group_rule")->where("group_id",$oinfo['group_id'])->where("order_num",$oinfo['group_rule_num'])->find();
                // if($groupRule){
                //     $oinfo['isluck'] = 1;
                // }
                $oinfo['isluck'] = $oinfo['group_zero'];
            }

            //校验是否是分组订单
            $group_data = [];
            if($oinfo["duorw"] > 0){
                $group_data = Db::table('xy_convey')
                ->alias('xc')
                ->leftJoin('xy_member_address ar', 'ar.uid=xc.uid', 'ar.is_default=1')
                ->leftJoin('xy_goods_list xg', 'xg.id=xc.goods_id')
                ->leftJoin('xy_users u', 'u.id=xc.uid')
                ->field('xc.id oid,xc.commission,xc.addtime,xc.endtime,xc.status,xc.num,xc.goods_count,xc.add_id,xg.goods_name,xg.goods_price,xg.shop_name,xg.goods_pic,ar.name,ar.tel,ar.address,u.balance,xc.group_rule_num,xc.group_id,xc.duorw,xc.is_pay')
                ->where('xc.rands', $oinfo['rands'])
                ->where('xc.uid', $this->usder_id)
                ->select();
                
                foreach($group_data as &$value){
                     $value['endtime'] = $value['endtime'] - (60*60 * config('equation_of_time'));
                    $value['addtime'] = $value['addtime'] - (60*60 * config('equation_of_time'));
                    //$value['pay_time'] = $value['pay_time'] - (60*60 * 14);
                }
                
                
                
                $oinfo['completedquantity'] = Db::table("xy_convey")->where('rands',$oinfo['rands'])->where("is_pay",1)->count();
                
            }else{
                $group_data[] = $oinfo;
                $oinfo['completedquantity'] = 1;
            }
            
            return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => $oinfo,"group_data"=>$group_data]);
        }
    }

    public function order_info_list()
    {
        if (\request()->isPost()) {
            $oid = input('post.id');
            if (is_array($oid)) {
                $orderList = db('xy_convey')
                    ->alias('xc')
                    ->leftJoin('xy_member_address ar', 'ar.uid=xc.uid', 'ar.is_default=1')
                    ->leftJoin('xy_goods_list xg', 'xg.id=xc.goods_id')
                    ->leftJoin('xy_users u', 'u.id=xc.uid')
                    ->field('xc.id oid,xc.commission,xc.addtime,xc.endtime,xc.status,xc.num,xc.goods_count,xc.add_id,xg.goods_name,xg.goods_price,xg.shop_name,xg.goods_pic,ar.name,ar.tel,ar.address,u.balance')
                    ->where('xc.id', 'in', $oid)
                    ->where('xc.uid', $this->usder_id)
                    ->select();
            } else {
                $orderList[] = db('xy_convey')
                    ->alias('xc')
                    ->leftJoin('xy_member_address ar', 'ar.uid=xc.uid', 'ar.is_default=1')
                    ->leftJoin('xy_goods_list xg', 'xg.id=xc.goods_id')
                    ->leftJoin('xy_users u', 'u.id=xc.uid')
                    ->field('xc.id oid,xc.commission,xc.addtime,xc.endtime,xc.status,xc.num,xc.goods_count,xc.add_id,xg.goods_name,xg.goods_price,xg.shop_name,xg.goods_pic,ar.name,ar.tel,ar.address,u.balance')
                    ->where('xc.id', $oid)
                    ->where('xc.uid', $this->usder_id)
                    ->find();

            }
            if (!$orderList) return json(['code' => 1, yuylangs('zwsj')]);
            foreach ($orderList as $k => $oinfo) {
                $orderList[$k]['endtime'] = date('Y/m/d H:i:s', $oinfo['endtime']);
                $orderList[$k]['addtime'] = date('Y/m/d H:i:s', $oinfo['addtime']);
                $orderList[$k]['yuji'] = $oinfo['commission'] + $oinfo['num'];
            }
            return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => $orderList]);
        }
    }

    /**
     * 处理订单
     */
    public function do_order()
    {
        if (request()->isPost()) {
            $oid = input('post.oid');
            $status = input('post.status/d', 1);
            $add_id = input('post.add_id/d', 0);
            $pingfen = input('post.pingfen/d', 0);
            $pinglun = input('post.pinglun/s', '');
            $uid = $this->usder_id;
            if (!\in_array($status, [1, 2])) return json(['code' => 1, 'info' => yuylangs('cscw')]);
         
            if (is_array($oid)) {
                $uinfo = Db::name('xy_users')->where('id', $uid)->find();
                $oidList = [];
                $all_amount = 0;
                foreach ($oid as $o) {
                    $order = Db::name('xy_convey')
                        ->field('id,num')
                        ->where('id', $o)
                        ->where('uid', $uid)
                        ->where('status', 0)
                        ->find();
                    if (!empty($order['id'])) {
                        $oidList[] = $o;
                        $all_amount += floatval($order['num']);
                    }
                }
                
                if (empty($oidList)) {
                    return json(['code' => 1, 'info' => yuylangs('qqcw')]);
                }
                if ($uinfo['balance'] < $all_amount) return [
                    'code' => 1,
                    'info' => sprintf(yuylangs('zhyebz'), ($all_amount - $uinfo['balance']) . ""),
                    'url' => url('index/ctrl/recharge')
                ];
                foreach ($oidList as $v) {
                    $res = model('admin/Convey')->do_order($v, $status, $this->usder_id, $add_id, $pingfen, $pinglun);
                    if ($res['code'] == 1) {
                        return json($res);
                    }
                }
            } else {
                $res = model('admin/Convey')->do_order($oid, $status, $this->usder_id, $add_id, $pingfen, $pinglun);
            }
            return json($res);
        }
        return json(['code' => 1, 'info' => yuylangs('qqcw')]);
    }

    /**
     * 获取充值订单
     */
    public function get_recharge_order()
    {
        $uid = $this->usder_id;
        $page = input('post.page/d', 1);
        $num = input('post.num/d', 10);
        $limit = ((($page - 1) * $num) . ',' . $num);
        $info = db('xy_recharge')->where('uid', $uid)->order('addtime desc')->limit($limit)->select();
        if (!$info) return json(['code' => 1, 'info' => yuylangs('zwsj')]);
        return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => $info]);
    }

    /**
     * 验证提现密码
     */
    public function check_pwd2()
    {
        if (!request()->isPost()) return json(['code' => 1, 'info' => yuylangs('qqcw')]);
        $pwd2 = input('post.pwd2/s', '');
        $info = db('xy_users')->field('pwd2,salt2')->find($this->usder_id);
        if ($info['pwd2'] == '') return json(['code' => 1, 'info' => yuylangs('not_jymm')]);
        if ($info['pwd2'] != sha1($pwd2 . $info['salt2'] . config('pwd_str'))) return json(['code' => 1, 'info' => yuylangs('pass_error')]);
        return json(['code' => 0, 'info' => yuylangs('czcg')]);
    }
}