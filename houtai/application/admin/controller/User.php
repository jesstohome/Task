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
use think\Db;

/**
 * 系统用户管理
 * Class User
 * @package app\admin\controller
 */
class User extends Base
{

    /**
     * 指定当前数据表
     * @var string
     */
    public $table = 'SystemUser';

    /**
     * 系统用户管理
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
        $this->title = '系统用户';
        $query = $this->_query($this->table)
            ->like('username,phone,mail,nickname')
            ->where('authorize', '<>', '2')
            ->equal('status');
        $query->dateBetween('login_at,create_at')->where(['is_deleted' => '0'])->order('id desc')->page();
    }

    public function _index_page_filter(&$data)
    {
        foreach ($data as &$vo) {
            if($vo['authorize']){
                $vo['authorize_name'] = Db::table('system_auth')->where('id',$vo['authorize'])->value('title');
            }else{
                $vo['authorize_name'] = '-';
                if($vo['id'] == 10000){
                    $vo['authorize_name'] = '超级管理员';
                }
            }

        }
    }


    /**
     * 添加系统用户
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        $this->applyCsrfToken();
        $this->_form($this->table, 'form');
    }

    /**
     * 编辑系统用户
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
     * 重置系统用户密码
     * @auth true
     */
    public function sys_user_resetting_pwd()
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
     * 删除系统用户
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
     * 编辑系统用户new
     * @auth true
     */
    public function edit_sys_user()
    {
        if (request()->isPost()) {
            cache('edit_sys_user_'.session('admin_user')['id'],null);
            if(cache('edit_sys_user_'.session('admin_user')['id'])){
                return $this->success('操作成功');
            }
            cache('edit_sys_user_'.session('admin_user')['id'],1,3);
            $data = [
                'username'=>input('username',''),
                'nickname'=>input('nickname',''),
                'phone'=>input('phone',''),
                'status'=>input('status',''),
            ];
            foreach ($data as $v) {
                if(empty($v)){
                    return $this->error('请填写完整信息');
                }
            }
            $data['authorize'] = input('authorize');
            if(input('password')){
                $data['password'] = md5(input('password'));
            }
            $id = input('id');
            $res = Db::table('system_user')->where('id',$id)->update($data);
            if($res){
                return $this->success('操作成功');
            }
            return $this->error('操作失败');
        }
        $this->sys_user = Db::table('system_user')
            ->where('id',input('id'))
            ->find();
        $this->authorizes = Db::name('system_auth')
            ->where('status',1)
            ->where('id', '<>', 2)
            ->order('sort desc,id desc')->select();
        return $this->fetch();
    }

    /**
     * 添加系统用户new
     * @auth true
     */
    public function add_sys_user()
    {
        if (request()->isPost()) {
            cache('add_sys_user_'.session('admin_user')['id'],null);
            if(cache('add_sys_user_'.session('admin_user')['id'])){
                return $this->success('操作成功');
            }
            cache('add_sys_user_'.session('admin_user')['id'],1,3);
            $data = [
                'username'=>input('username',''),
                'password'=>input('password',''),
                'authorize'=>input('authorize',''),
                'nickname'=>input('nickname',''),
                'phone'=>input('phone',''),
                'status'=>input('status',''),
            ];
            foreach ($data as $v) {
                if(empty($v)){
                    return $this->error('请填写完整信息');
                }
            }
            $data['password'] = md5($data['password']);
            $res = Db::table('system_user')->insert($data);
            if($res){
                return $this->success('操作成功');
            }
            return $this->error('操作失败');
        }
        $this->authorizes = Db::name('SystemAuth')
            ->where('status',1)
            ->where('id', '<>', 2)
            ->order('sort desc,id desc')->select();
        return $this->fetch();
    }

    /**
     * 修改用户密码
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
                $this->success('密码修改成功，下次请使用新密码登录！', '');
            } else {
                $this->error('密码修改失败，请稍候再试！');
            }
        }
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
            // 刷新系统授权
            NodeService::applyUserAuth();
            // 用户权限处理
            $data['authorize'] = (isset($data['authorize']) && is_array($data['authorize'])) ? join(',', $data['authorize']) : '';

            if (!empty($data['user_id'])) {
                $isAgentSon = Db::name('xy_users')->where('id', $data['user_id'])->value('agent_id');
                if (empty($isAgentSon)) {
                    $this->error("业务员ID {$data['user_id']} 未绑定代理！");
                }
            }
            // 用户账号重复检查
            if (isset($data['id'])) unset($data['username']);
            elseif (Db::name($this->table)->where(['username' => $data['username'], 'is_deleted' => '0'])->count() > 0) {
                $this->error("账号{$data['username']}已经存在，请使用其它账号！");
            }
        } else {
            $data['authorize'] = explode(',', isset($data['authorize']) ? $data['authorize'] : '');
            $this->authorizes = Db::name('SystemAuth')
                ->where(['status' => '1'])
                ->where('id', '<>', 2)
                ->order('sort desc,id desc')->select();
        }
    }

    /**
     * 禁用系统用户
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        if (in_array('10000', explode(',', $this->request->post('id')))) {
            $this->error('系统超级账号禁止操作！');
        }
        $this->applyCsrfToken();
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 启用系统用户
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        $this->applyCsrfToken();
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * 删除系统用户
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function remove()
    {
        if (in_array('10000', explode(',', $this->request->post('id')))) {
            $this->error('系统超级账号禁止删除！');
        }
        $this->applyCsrfToken();
        $this->_delete($this->table);
    }

}
