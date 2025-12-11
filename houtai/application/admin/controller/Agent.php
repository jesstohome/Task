<?php

namespace app\admin\controller;

use app\admin\service\NodeService;
use library\tools\Data;
use think\Db;
use PHPExcel;

/**
 * 代理管理
 * Class Agent
 * @package app\admin\controller
 */
class Agent extends Base
{
    /**
     * 指定当前数据表
     * @var string
     */
    protected $table = 'system_user';
    protected $table_user = 'xy_users';

    /**
     * 代理列表
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        if ($this->agent_id > 0 && $this->agent_uid > 0) return '<h1>无权限</h1>';
        $this->title = '代理列表';
        $this->is_admin = $this->agent_id == 0;
        $this->parent = input('parent','');
        $this->nickname = input('nickname','');
        $this->phone = input('phone','');
        $query = $this->_query($this->table)->where('authorize', '2');

        if (input('create_at', '')) {
            $arr = explode(' - ', input('create_at', ''));
            $query->whereBetweenTime('create_at', $arr[0]. ' 00:00:00',$arr[1] . ' 23:59:59');
        }
        if ($this->agent_id > 0) {
            $query->where('parent_id', $this->agent_id);
        } else {
            if($this->parent !== ""){
                $user = Db::name('xy_users')->where('invite_code',$this->parent)->find();
                $query->where('parent_id',$user['agent_service_id'] > 0 ? $user['agent_service_id'] : -9999);
                $sys_user = Db::name('system_user')->where('username',$this->parent)->find();
                $sys_user_id = $sys_user['id'] ? $sys_user['id'] : -9999;
                $query->whereOr('parent_id',$sys_user_id);
            }
            $parent_id = input('parent_id/d', "");
            if($parent_id !== ""){
                $query->where('parent_id', $parent_id);
            }
            if ($parent_id > 0) {
                $aname = Db::name($this->table)->where('id', $parent_id)->value('username');
                $this->title =  $this->title ."({$aname})";
            }
            if($this->nickname !== ""){
                $query->where('nickname','like','%'.trim($this->nickname).'%');
            }
            if($this->phone !== ""){
                $query->where('phone','like','%'.trim($this->phone).'%');
            }
        }
        $query->where('is_deleted', 0);
        return $query->like('username,phone')->order('id DESC')->page();
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
        foreach ($data as &$vo) {
            $vo['invite_link'] = '';
            if($vo['invite_code']){
                $vo['invite_link'] = sysconf('web_url').'/register?type=2&invite_code='.$vo['invite_code'];
            }
            if($vo['nickname'] == ''){
                $vo['nickname'] = '-';
            }
            $vo['parent_name'] = '';
            if($vo['parent_id']>0){
                $vo['parent_name'] = Db::name('system_user')->where('id',$vo['parent_id'])->value('username');
            }
        }
        $data = Data::arr2table($data);
    }

    /**
     * 转移会员
     * @auth true
     */
    public function migrate_user()
    {
        $this->id = input('id');
        if (request()->isPost()) {
            $migrate_user_id = input('migrate_user_id','');
            if(!$migrate_user_id){
                return $this->error('请选择代理');
            }
            $res = Db::table('xy_users')->where('agent_service_id',$this->id)->update(['agent_service_id'=>$migrate_user_id]);
            if($res){
                return $this->success('操作成功');
            }
            return $this->error('操作失败');
        }
        $this->agents = Db::name('system_user')
            ->where('authorize', "2")
            ->field('id,username')
            ->where('is_deleted', 0)
            ->select();
        return $this->fetch();
    }

    /**
     * 重置密码
     * @auth true
     */
    public function resetting_pwd()
    {
        $id = input('id');
        $pwd = '123456';
        $res = Db::table('system_user')->where('id',$id)->update(['password' => md5($pwd)]);
        if($res){
            return $this->success('重置成功，密码为：'.$pwd);
        }
        return $this->error('操作失败');
    }

    /**
     * 删除代理
     * @auth true
     */
    public function del_agent()
    {
        $id = input('id');
        $res = Db::table('system_user')->where('id',$id)->update(['is_deleted' => 1]);
        if($res){
            return $this->success('操作成功');
        }
        return $this->error('操作失败');
    }

    /**
     * 编辑代理
     * @auth true
     */
    public function edit_agent()
    {
        if (request()->isPost()) {
            if(cache('edit_agent_'.session('admin_user')['id'])){
                return $this->success('操作成功');
            }
            cache('edit_agent_'.session('admin_user')['id'],1,3);
            $data = input();
            $id = input('id');
            if(!empty($data['password'])){
                $data['password'] = md5($data['password']);
            }

            $invite_code = input('invite_code','');
            if(!empty($invite_code)){
                $count_user = Db::table('system_user')->where('id','<>',$id)->where('invite_code',$invite_code)->count();
                if($count_user > 0){
                    return $this->error('该邀请码已存在');
                }
            }

            unset($data['id']);
            unset($data['spm']);
            unset($data['open_type']);
            $res = Db::table('system_user')->where('id',$id)->update($data);
            if($res){
                return $this->success('操作成功');
            }
            return $this->error('操作失败');
        }
        $id = input('id');
        $this->system_user = Db::name('system_user')
            ->where('id', $id)
            ->where('authorize', "2")
            ->where('is_deleted', 0)
            ->find();

        $this->agents = Db::name('system_user')
            ->where('authorize', "2")
            ->field('id,username')
            ->where('is_deleted', 0)
            ->select();
        return $this->fetch();
    }

    /**
     * 添加代理
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        if (request()->isPost()) {
            if(cache('agent_add_'.session('admin_user')['id'])){
                return $this->success('操作成功');
            }
            cache('agent_add_'.session('admin_user')['id'],1,3);
            $data = input();

            $invite_code = input('invite_code','');
            if(!empty($invite_code)){
                $count_user = Db::table('system_user')->where('invite_code',$invite_code)->count();
                if($count_user > 0){
                    return $this->error('该邀请码已存在');
                }
            }

//            if(!empty($invitation_code)){
//                $user = Db::table('xy_users')->where('invite_code',$invitation_code)->find();
//                if(empty($user)){
//                    return $this->error('该邀请码的用户不存在');
//                }
//                $data['user_id'] = $user['id'];
//            }


            unset($data['invitation_code']);
            unset($data['spm']);
            unset($data['open_type']);

            $data['create_at'] = date('Y-m-d H:i:s',time());
            $data['authorize'] = 2;
            $data['password'] = md5($data['password']);
            $res = Db::table('system_user')->insert($data);
            if($res){
                return $this->success('操作成功');
            }
            return $this->error('操作失败');
        }
        $this->agents = Db::name('system_user')
            ->where('authorize', "2")
            ->field('id,username')
            ->where('is_deleted', 0)
            ->select();
        return $this->fetch();
    }

    /**
     * 编辑代理
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit()
    {
        $this->applyCsrfToken();
        $this->_form($this->table, 'form');
    }

    /**
     * 表单数据处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function _form_filter(&$data)
    {
        if ($this->request->isPost()) { 
            if (isset($data['username'])) $data['username'] = strtolower($data['username']);
            // 用户账号重复检查
            if (isset($data['id'])) unset($data['username']);
            elseif (Db::name($this->table)->where(['username' => $data['username'], 'is_deleted' => '0'])->count() > 0) {
                $this->error("账号{$data['username']}已经存在，请使用其它账号！");
            }
            
            $resUserId = Db::table("xy_users")->find($data["user_id"]);
            if(!$resUserId){
                 $this->error('uid不存在');
            }
           
            
            if ($this->agent_id == 0) {
                //$data['parent_id'] = 0;
            } else {
                $data['parent_id'] = $this->agent_id;
            }
            if (!isset($data['id']) && $data['parent_id'] > 0) {
                if (!$data['phone']) $this->error('手机号必填');
                if (Db::name($this->table_user)->where(['tel' => $data['phone']])->count('id') > 0) {
                    $this->error("手机号 {$data['phone']} 已经存在，请使用其它手机号！");
                }
                if (Db::name($this->table_user)->where(['username' => $data['username']])->count('id') > 0) {
                    $this->error("账号 {$data['username']} 已经存在，请使用其它账号！");
                }
            }
            //用户权限处理
            $data['authorize'] = 2;
            /*if (!empty($data['user_id'])) {
                $isAgentSon = Db::name('xy_users')->where('id', $data['user_id'])->value('agent_id');
                if (empty($isAgentSon)) {
                    $this->error("业务员ID {$data['user_id']} 未绑定代理！");
                }
            }*/
        } else {
            $data['user_id'] = !empty($data['user_id']) ? $data['user_id'] : 0;
            $this->agent_list = Db::name('system_user')
                ->where('parent_id', 0)
                ->where('user_id', 0)
                ->where('authorize', "2")
                ->field('id,username')
                ->where('is_deleted', 0);
            if ($this->agent_id) $this->agent_list->where('id', $this->agent_id);
            $this->agent_list = $this->agent_list->select();

            $this->is_admin = $this->agent_id == 0;
        }
    }

    public function _form_result(&$result, &$data)
    {
        if ($this->request->isPost()) {
            if ($result !== false) {
                //开户
                if (!isset($data['id']) && $data['parent_id'] > 0) {
                    $data['id'] = $result;
                    //添加用户
                    $res = model('Users')->add_users(
                        $data['phone'], $data['username'], '123456', 0,
                        '', '123456', $data['parent_id']);
                    if ($res['code'] == 0) {
                        //添加成功了
                        Db::name($this->table_user)
                            ->where('id', $res['id'])
                            ->update(['agent_service_id' => $data['id']]);
                        Db::name($this->table)
                            ->where('id', $data['id'])
                            ->update(['user_id' => $res['id']]);
                    }
                    sysoplog('添加代理', '新代理ID ' . $data['id']);
                } else {
                    sysoplog('编辑代理', '新数据包 ' . json_encode($data, JSON_UNESCAPED_UNICODE));
                }
            }
        }
        return true;
    }

    /**
     * 修改代理用户密码
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function pass()
    {
        $this->applyCsrfToken();
        if ($this->request->isGet()) {
            $this->verify = false;
            $this->_form($this->table, 'pass');
        } else {
            $post = $this->request->post();
            if ($post['password'] !== $post['repassword']) {
                $this->error('两次输入的密码不一致！');
            }
            $result = NodeService::checkpwd($post['password']);
            if (empty($result['code'])) $this->error($result['msg']);
            if (Data::save($this->table, ['id' => $post['id'], 'password' => md5($post['password'])], 'id')) {
                sysoplog('修改代理用户密码', 'ID ' . $post['id']);
                $this->success('密码修改成功，下次请使用新密码登录！', '');
            } else {
                $this->error('密码修改失败，请稍候再试！');
            }
        }
    }

    /**
     * 禁用代理户
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        if (in_array('10000', explode(',', $this->request->post('id')))) {
            $this->error('error！');
        }
        $this->applyCsrfToken();
        $this->_save($this->table, ['status' => '0']);
    }

    protected function _forbid_save_result($result, $data)
    {
        sysoplog('禁用代理户', json_encode($_POST, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 启用代理用户
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        $this->applyCsrfToken();
        $this->_save($this->table, ['status' => '1']);
    }

    protected function _resume_save_result($result, $data)
    {
        sysoplog('启用代理用户', json_encode($_POST, JSON_UNESCAPED_UNICODE));
    }
}