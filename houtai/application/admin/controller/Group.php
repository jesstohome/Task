<?php

namespace app\admin\controller;

use library\tools\Data;
use think\Db;

//tp5.1用法
use PHPExcel_IOFactory;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\exception\PDOException;

/**
 * 叠加组
 * Class Users
 * @package app\admin\controller
 */
class Group extends Base
{
    /**
     * 指定当前数据表
     * @var string
     */
    protected $table = 'xy_group';
    protected $table_rule = 'xy_group_rule';

    /**
     * 叠加组列表
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws PDOException
     */
    public function index()
    {
        $this->title = '模式分组';
        $this->agent = input('agent','');
        $this->group_name = input('group_name','');
        $this->is_share = input('is_share','');
        $query = $this->_query($this->table);

        $agentList = Db::name('system_user')->column('username', 'id');
        $this->agentList = $agentList;
        $this->agentList[0] = 'system';
        if(!empty($this->agent)){
            $agent_id = Db::name('system_user')->where('username','like',"{$this->agent}%")->value('id');
            if(!empty($agent_id)){
                $query = $query->where('agent_id',$agent_id);
            }else{
                $query = $query->where('agent_id',-999);
            }
        }
        if(!empty($this->group_name)){
            $query = $query->where('title','like',"{$this->group_name}%");
        }
        if(is_numeric($this->is_share)){
            $query = $query->where('is_share',$this->is_share);
        }
        if ($this->agent_id > 0) $query->where('agent_id', $this->agent_id);
        $query->order('agent_id asc,id desc')->page(true, true, false, 0, 'aaa');
        
    }

    /**
     * 表单数据处理
     * @param array $data
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    protected function _index_page_filter(&$data)
    {
        foreach ($data as &$vo) {
            $vo['agent'] = '-';
            if(!empty($vo['agent_id'])){
                $vo['agent'] = Db::name('system_user')->where('id',$vo['agent_id'])->value('username');
            }
            $vo['rule_count'] = Db::name('xy_group_rule')
                ->where('group_id', $vo['id'])
                ->count('id');
            $vo['user_count'] = Db::name('xy_users')
                ->where('group_id', $vo['id'])
                ->count('id');
        }
        $data = Data::arr2table($data);
    }

    /**
     * 删除方案分组-new
     * @auth true
     */
    public function del_group()
    {
        $this->title = '删除方案分组';
        $id = input('id');
        Db::table('xy_group')->where('id',$id)->delete();;
        $this->success('删除成功');
    }

//    /**
//     * 删除方案分组
//     * @auth true
//     * @menu true
//     */
//    public function del_group_rule()
//    {
//        if (request()->isPost()) {
//            $id = input('id');
//            $res = Db::table('xy_group_rule')->where('id',$id)->delete();
//            if($res){
//                $this->success('删除成功');
//            }
//            $this->error('删除失败');
//        }
//    }

    /**
     * 编辑方案分组
     * @auth true
     * @menu true
     */
    public function edit_group()
    {
        if (request()->isPost()) {
            $data = input();
            if(cache('group_edit')){
                return $this->success('操作成功');
            }
            cache('group_edit',1,3);
            Db::startTrans();
            try {
                Db::table('xy_group')->where('id',$data['group_id'])->update([
                    'title'=>$data['title'],
                    'order_num'=>$data['order_num'],
                    'agent_id'=>$data['agent_id'],
                    'status'=>$data['status'],
                    'is_default'=>$data['is_default'],
                    'is_team_mode'=>$data['is_team_mode'],
                    'is_share'=>$data['is_share'],
                ]);
                if(!empty($data['bind_ids'])) {
                    Db::table('xy_users')
                        ->where('id','in',$data['bind_ids'])
                        ->update(['group_id'=>$data['group_id']]);
                }
                foreach ($data['set_params'] as $v) {
                    if(empty($v['order_num'])){
                        throw new Exception('参数不完整');
                    }
                    $save = [
                        'order_num'=>$v['order_num'],
                        'commission_type'=>$v['commission_type'],
                        'commission_value'=>$v['commission_value'],
                        'order_type'=>$v['order_type'],
                        'order_price_type'=>$v['order_price_type'],
                        'order_price'=>$v['order_price'],
                        'freeze_principal'=>$v['freeze_principal'],
                        'trigger_level'=>$v['trigger_level'],
                    ];
                    if((int)$v['id'] > 0){
                        Db::table('xy_group_rule')->where('id',$v['id'])->update($save);
                    }else{
                        $save['group_id'] = $data['group_id'];
                        Db::table('xy_group_rule')->insert($save);
                    }
                }
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return $this->error($e->getMessage());
            }
            return $this->success('操作成功');
        }
        $this->title = '编辑方案分组';
        $id = input('id');
        $this->group = Db::table('xy_group')->where('id',$id)->find();;
        $this->group_rule_list = Db::table('xy_group_rule')->where('group_id',$id)->select();;
        foreach ($this->group_rule_list as &$v) {
            if($v['order_type'] == 0 && $v['order_price_type'] == 0){
                $ite = explode('-',$v['order_price']);
                $v['order_price_min'] = $ite[0] * 100;
                $v['order_price_max'] = $ite[1] * 100;
            }
            if(($v['commission_type']) == 0){
                $v['commission_value'] = $v['commission_value'] * 100;
            }
            if(($v['order_type']) == 1 && $v['order_price_type'] == 0){
                $v['order_price'] = $v['order_price'] * 100;
            }
        }
        $this->agents = Db::name('system_user')->where('status',1)->field('id,username')->select();
        $this->levels = Db::name('xy_level')->where('status',1)->field('id,name,level')->select();
        $this->_form($this->table, 'edit_group');
    }

    /**
     * 查看方案组用户
     * @auth true
     * @menu true
     */
    public function view_group_users()
    {
        $this->title = '查看方案组用户';
        $id = input('id');
        $this->_query('xy_users')->where('group_id',$id)->page();;
    }
    public function _view_group_users_page_filter(&$data)
    {
        foreach ($data as &$vo) {
            $res = Db::name('xy_single_control')->where('uid',$vo['id'])->find();
            $vo['addtime'] = date('Y-m-d H:i:s',$vo['addtime']);
            $vo['shuadan_status_name'] = $vo['shuadan_status'] ? '开启' : '关闭';
            $vo['single_control_status_name'] = $res['single_control_status'] ? '开启' : '关闭';
            $vo['fixed_commission_bili_name'] = $res['fixed_commission_bili'] * 100 . '%';
            $vo['is_jia_name'] = $vo['is_jia'] ? '内部' : '外部';
            $vo['order_min_bili'] = $res['order_min_bili'] * 100;
            $vo['order_max_bili'] = $res['order_max_bili'] * 100;
            $vo['enable_order_num'] = $res['enable_order_num'];
            $vo['fixed_commission_bili'] = $res['fixed_commission_bili'] * 100;
            $vo['fixed_order_amount'] = $res['fixed_order_amount'];
            $vo['fixed_order_num'] = $res['fixed_order_num'];
        }
    }

    /**
     * 查看计划
     * @auth true
     * @menu true
     */
    public function view_plan()
    {
        $this->title = '查看计划';
        $id = input('id');
        $this->_query('xy_group_rule')->where('group_id',$id)->page();;
    }
    public function _view_plan_page_filter(&$data)
    {
        foreach ($data as &$vo) {
            switch ($vo['order_type']){
                case 0:
                    $vo['order_type_name'] = '默认模式';
                    break;
                case 1:
                    $vo['order_type_name'] = '叠加模式';
                    break;
                case 2:
                    $vo['order_type_name'] = '固定补单模式';
                    break;
            }

            if($vo['order_price_type'] == 0){
                $tem = explode('-',$vo['order_price']);
                $arr1 = 0;
                $arr2 = 0;
                foreach ($tem as $k=> $item) {
                    $arr1 = $item * 100;
                    $arr2 = $item * 100;
                }
                $vo['balance_bili'] =  implode('-',[$arr1,$arr2]).'%';
                $vo['fixed_amount'] = '-';
            }else{
                $vo['balance_bili'] = '-';
                $vo['fixed_amount'] = $vo['order_price'];
            }

            if($vo['commission_type'] == 0){
                $vo['order_bili'] = $vo['commission_value'] * 100 .'%';
                $vo['order_fixed_amount'] = '-';
            }else{
                $vo['order_bili'] = '-';
                $vo['order_fixed_amount'] = $vo['order_price'];
            }
            $vo['level_name'] = is_numeric($vo['trigger_level']) ? Db::table('xy_level')->where('level',$vo['trigger_level'])->value('name') : '-';
        }
    }

    /**
     * 添加叠加组
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        // $this->applyCsrfToken();
        //$this->_form($this->table, 'form');
        if (request()->isPost()) {
            $data = input();
            if(cache('group_add')){
                return $this->success('操作成功');
            }
            cache('group_add',1,3);
            Db::startTrans();
            try {
                $g_id = Db::table('xy_group')->insertGetId([
                    'title'=>$data['title'],
                    'order_num'=>$data['order_num'],
                    'agent_id'=>$data['agent_id'],
                    'status'=>$data['status'],
                    'is_default'=>$data['is_default'],
                    'is_team_mode'=>$data['is_team_mode'],
                    'is_share'=>$data['is_share'],
                ]);
                if($g_id){
                    if(!empty($data['bind_ids'])) {
                        Db::table('xy_users')
                            ->where('id','in',$data['bind_ids'])
                            ->update(['group_id'=>$g_id]);
                    }
                    foreach ($data['set_params'] as $v) {
                        if(empty($v['order_num'])){
                            throw new Exception('参数不完整');
                        }
                        $save = [
                            'group_id'=>$g_id,
                            'order_num'=>$v['order_num'],
                            'commission_type'=>$v['commission_type'],
                            'commission_value'=>$v['commission_value'],
                            'order_type'=>$v['order_type'],
                            'order_price_type'=>$v['order_price_type'],
                            'order_price'=>$v['order_price'],
                            'freeze_principal'=>$v['freeze_principal'],
                            'trigger_level' => $v['trigger_level']
                        ];
                        Db::table('xy_group_rule')->insert($save);
                    }
                }
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return $this->error($e->getMessage());
            }
            return $this->success('操作成功');
        }
        $where = [];
        if($this->agent_id){
            $where[] = ['id','=',$this->agent_id];
        }
        $this->agents = Db::name('system_user')->where('status','=',1)->where($where)->field('id,username')->select();
        $this->levels = Db::name('xy_level')->where('status',1)->field('id,name,level')->select();
        $this->_form($this->table, 'add');
    }

    public function get_users()
    {
        if (request()->isPost()) {
            $parent_name = input('parent_name');
            $username = input('username');
            $user_type = input('user_type',-99);
            $level = input('level');
            $model = Db::table('xy_users')->where('id','>', 0);
            if(!empty($parent_name)){
                $ids = Db::table('xy_users')->where('tel',$parent_name)->column('id');
                $model = $model->where('parent_id','in',$ids);
            }
            if(!empty($username)){
                $model = $model->where('tel','like',"{$username}%");
            }
            if(in_array($user_type,[0,1])){
                $model = $model->where('is_jia',$user_type);
            }
            if($level){
                $model = $model->where('level',$level);
            }
            $data = $model->field('id,tel')->limit(50)->select();
            $this->success('ok',$data);
        }
    }

    protected function _add_form_result($result, $data)
    {
        sysoplog('添加叠加组', json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 编辑叠加组
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit()
    {
        // $this->applyCsrfToken();
        $this->_form($this->table, 'form');
    }

    protected function _edit_form_result($result, $data)
    {
        sysoplog('编辑叠加组', json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 删除叠加组
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function remove()
    {
        // $this->applyCsrfToken();
        $this->_delete($this->table);
    }

    /**
     * 删除结果处理
     * @param boolean $result
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    protected function _remove_delete_result($result)
    {
        if ($result) {
            $id = $this->request->post('id/d');
            Db::name('xy_users')
                ->where('group_id', $id)
                ->update(['group_id' => 0]);
            sysoplog('删除叠加组', "ID {$id}");
        }
    }

    /**
     * 规则配置
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function rule()
    {
        $group_id = $this->request->get('group_id/d', 0);
        if (!$group_id) {
            $this->error('数据不存在');
        }
        $this->title = '叠加规则列表';
        $this->group_id = $group_id;
        $this->com_types = [0 => '百分比', 1 => "固定值"];
        $this->order_types = [0 => '默认模式', 1 => "叠加模式"];
        $query = Db::name($this->table_rule)
            ->where('group_id', $group_id);
        $this->list = $query->select();
        $this->fetch();
    }

    /**
     * 添加规则
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function rule_add()
    {
        $this->group_id = $this->request->get('group_id/d', 0);
        if (!$this->group_id) {
            $this->error('数据不存在');
        }
        // $this->applyCsrfToken();
        $this->_form($this->table_rule, 'rule_form');
    }

    /**
     * 编辑规则
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function rule_edit()
    {
        // $this->applyCsrfToken();
        $this->_form($this->table_rule, 'rule_form');
    }

    /**
     * 删除规则
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function rule_remove()
    {
        // $this->applyCsrfToken();
        $this->_delete($this->table_rule);
    }

    /**
     * 复制模式分组
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function copy()
    {
        return 1;
    }
}
