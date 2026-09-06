<?php

namespace app\admin\controller;

use app\admin\service\NodeService;
use library\tools\Data;
use think\Db;
use PHPExcel;
use PHPExcel_IOFactory;

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
    protected $inviteTable = 'xy_agent_invite_code';

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
        $this->title = lang('代理列表');
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
     * 邀请码列表
     * @auth true
     * @menu true
     */
    public function invite_code()
    {
        $this->title = lang('邀请码列表');
        $this->is_admin = $this->agent_id == 0;
        $this->agent_username = input('agent_username/s', '');
        $this->status = input('status', '');
        $this->agent_select_id = input('agent_id/d', 0);
        $this->agents = Db::name($this->table)
            ->where('authorize', '2')
            ->where('is_deleted', 0)
            ->field('id,username')
            ->order('id desc')
            ->select();
        if (!$this->is_admin) {
            $this->agents = Db::name($this->table)
                ->where('id', $this->agent_id)
                ->where('authorize', '2')
                ->where('is_deleted', 0)
                ->field('id,username')
                ->select();
            $this->agent_select_id = $this->agent_id;
        }
        $query = $this->_query($this->inviteTable)->alias('c')
            ->leftJoin('system_user a', 'a.id=c.agent_id')
            ->leftJoin('xy_users u', 'u.id=c.used_user_id')
            ->field('c.*,a.username as agent_name,u.username as used_username');
        $this->applyInviteCodeWhere($query);
        return $query->where('c.is_deleted', 0)->order('c.status asc, c.id desc')->page();
    }

    /**
     * 生成代理邀请码
     * @auth true
     */
    public function create_invite_code()
    {
        $agentId = input('post.agent_id/d', 0);
        $number = input('post.number/d', 0);

        if ($this->agent_id > 0) {
            $agentId = $this->agent_id;
        }
        if ($agentId <= 0) {
            return $this->error(lang('请选择代理'));
        }
        if ($number <= 0 || $number > 1000) {
            return $this->error(lang('生成数量必须在1到1000之间'));
        }

        $agent = Db::name($this->table)
            ->where('id', $agentId)
            ->where('authorize', '2')
            ->where('is_deleted', 0)
            ->find();
        if (empty($agent)) {
            return $this->error(lang('代理不存在'));
        }

        $data = [];
        for ($i = 0; $i < $number; $i++) {
            $data[] = [
                'agent_id' => $agentId,
                'invite_code' => $this->makeAgentInviteCode(),
                'status' => 0,
                'create_at' => date('Y-m-d H:i:s'),
                'update_at' => date('Y-m-d H:i:s'),
            ];
        }
        $res = Db::name($this->inviteTable)->insertAll($data);
        if ($res) {
            sysoplog(lang('生成邀请码'), lang('代理ID') . $agentId . ',' . lang('数量') . $number);
            return $this->success(lang('操作成功'));
        }
        return $this->error(lang('操作失败'));
    }
    
    /**
     * 刷新单个代理的邀请码（生成一个新的未使用邀请码）
     * @auth true
     */
    public function refresh_invite_code()
    {
        $agentId = input('post.agent_id/d', 0);
        if ($this->agent_id > 0) {
            $agentId = $this->agent_id;
        }
        if ($agentId <= 0) {
            return $this->error(lang('请选择代理'));
        }
        $agent = Db::name($this->table)
            ->where('id', $agentId)
            ->where('is_deleted', 0)
            ->find();
        if (empty($agent)) {
            return $this->error(lang('代理不存在'));
        }
    
        $data = [
            'agent_id'    => $agentId,
            'invite_code' => $this->makeAgentInviteCode(),
            'status'      => 0,
            'create_at'   => date('Y-m-d H:i:s'),
            'update_at'   => date('Y-m-d H:i:s'),
        ];
        $res = Db::name($this->inviteTable)->insert($data);
        if ($res) {
            sysoplog(lang('刷新邀请码'), lang('代理ID') . $agentId);
            return $this->success(lang('操作成功'));
        }
        return $this->error(lang('操作失败'));
    }

    /**
     * 删除代理邀请码
     * @auth true
     */
    public function del_invite_code()
    {
        $id = input('id', '');
        $ids = array_filter(array_map('intval', explode(',', $id)));
        if (empty($ids)) {
            return $this->error(lang('参数错误'));
        }

        $query = Db::name($this->inviteTable)->whereIn('id', $ids);
        if ($this->agent_id > 0) {
            $query->where('agent_id', $this->agent_id);
        }
        $res = $query->update([
            'is_deleted' => 1,
            'update_at' => date('Y-m-d H:i:s'),
        ]);
        if ($res !== false) {
            sysoplog(lang('删除邀请码'), 'ID ' . join(',', $ids));
            return $this->success(lang('操作成功'));
        }
        return $this->error(lang('操作失败'));
    }

    /**
     * 批量删除代理邀请码
     * @auth true
     */
    public function batch_del_invite_code()
    {
        return $this->del_invite_code();
    }

    /**
     * 导出未使用代理邀请码
     * @auth true
     */
    public function export_invite_code()
    {
        $query = Db::name($this->inviteTable)->alias('c')
            ->leftJoin('system_user a', 'a.id=c.agent_id')
            ->field('c.id,c.invite_code,c.status,c.create_at,a.username as agent_name')
            ->where('c.is_deleted', 0)
            ->where('c.status', 0);

        $this->applyInviteCodeWhere($query, true);
        $list = $query->order('c.id desc')->select();

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setCellValue('A1', lang('ID'));
        $objPHPExcel->getActiveSheet()->setCellValue('B1', lang('邀请码'));
        $objPHPExcel->getActiveSheet()->setCellValue('C1', lang('代理名称'));
        $objPHPExcel->getActiveSheet()->setCellValue('D1', lang('使用状态'));
        $objPHPExcel->getActiveSheet()->setCellValue('E1', lang('创建时间'));
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(24);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(16);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(24);

        foreach ($list as $i => $vo) {
            $row = $i + 2;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $vo['id']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $vo['invite_code']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $vo['agent_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, lang('未使用'));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $vo['create_at']);
        }

        $filename = 'agent_invite_code_' . date('YmdHis') . '.xls';
        $objPHPExcel->getActiveSheet()->setTitle(lang('邀请码列表'));
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . $filename . '"');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    protected function _invite_code_page_filter(&$data)
    {
        foreach ($data as &$vo) {
            $vo['status_name'] = intval($vo['status']) === 1 ? lang('已使用') : lang('未使用');
            $vo['agent_name'] = $vo['agent_name'] ?: '-';
        }
    }

    /**
     * 代理邀请码查询条件
     * @param \think\db\Query $query
     * @param bool $export
     */
    private function applyInviteCodeWhere(&$query, $export = false)
    {
        if ($this->agent_id > 0) {
            $query->where('c.agent_id', $this->agent_id);
            return;
        }

        $agentId = input('agent_id/d', 0);
        if ($agentId > 0) {
            $query->where('c.agent_id', $agentId);
        }
        $agentUsername = trim(input('agent_username/s', ''));
        if ($agentUsername !== '') {
            $query->where('a.username', 'like', '%' . $agentUsername . '%');
        }
        if (!$export) {
            $status = input('status', '');
            if ($status !== '') {
                $query->where('c.status', intval($status));
            }
        }
    }

    /**
     * 生成唯一代理邀请码
     * @return string
     */
    private function makeAgentInviteCode()
    {
        do {
            $code = strtoupper(substr(md5(uniqid('', true) . mt_rand(100000, 999999)), 0, 8));
            $exists = Db::name($this->inviteTable)->where('invite_code', $code)->count()
                + Db::name($this->table)->where('invite_code', $code)->count()
                + Db::name($this->table_user)->where('invite_code', $code)->count();
        } while ($exists > 0);
        return $code;
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
            
            $agentInviteCode = Db::table('xy_agent_invite_code')
            ->where(['agent_id' => $vo['id'], 'status' => 0, 'is_deleted' => 0])
            ->order('id DESC')
            ->find();
            
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
                return $this->error(lang('请选择代理'));
            }
            $res = Db::table('xy_users')->where('agent_service_id',$this->id)->update(['agent_service_id'=>$migrate_user_id]);
            if($res){
                return $this->success(lang('操作成功'));
            }
            return $this->error(lang('操作失败'));
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
        return $this->error(lang('操作失败'));
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
            return $this->success(lang('操作成功'));
        }
        return $this->error(lang('操作失败'));
    }

    /**
     * 编辑代理
     * @auth true
     */
    public function edit_agent()
    {
        if (request()->isPost()) {
            if(cache('edit_agent_'.session('admin_user')['id'])){
                return $this->success(lang('操作成功'));
            }
            cache('edit_agent_'.session('admin_user')['id'],1,3);
            $data = input();
            $data['google_verify'] = input('google_verify/d', 1);
            $id = input('id');
            if(!empty($data['password'])){
                $data['password'] = md5($data['password']);
            }

            $invite_code = input('invite_code','');
            if(!empty($invite_code)){
                $count_user = Db::table('system_user')->where('id','<>',$id)->where('invite_code',$invite_code)->count();
                if($count_user > 0){
                    return $this->error(lang('该邀请码已存在'));
                }
            }

            unset($data['id']);
            unset($data['spm']);
            unset($data['open_type']);
            $res = Db::table('system_user')->where('id',$id)->update($data);
            if($res){
                return $this->success(lang('操作成功'));
            }
            return $this->error(lang('操作失败'));
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
                return $this->success(lang('操作成功'));
            }
            cache('agent_add_'.session('admin_user')['id'],1,3);
            $data = input();

            $invite_code = input('invite_code','');
            if(!empty($invite_code)){
                $count_user = Db::table('system_user')->where('invite_code',$invite_code)->count();
                if($count_user > 0){
                    return $this->error(lang('该邀请码已存在'));
                }
            }

//            if(!empty($invitation_code)){
//                $user = Db::table('xy_users')->where('invite_code',$invitation_code)->find();
//                if(empty($user)){
//                    return $this->error(lang('该邀请码的用户不存在'));
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
                return $this->success(lang('操作成功'));
            }
            return $this->error(lang('操作失败'));
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
                $this->error(lang("账号{$data['username']}已经存在，请使用其它账号！"));
            }
            
            $resUserId = Db::table("xy_users")->find($data["user_id"]);
            if(!$resUserId){
                 $this->error(lang('uid不存在'));
            }
           
            
            if ($this->agent_id == 0) {
                //$data['parent_id'] = 0;
            } else {
                $data['parent_id'] = $this->agent_id;
            }
            if (!isset($data['id']) && $data['parent_id'] > 0) {
                if (!$data['phone']) $this->error(lang('手机号必填'));
                if (Db::name($this->table_user)->where(['tel' => $data['phone']])->count('id') > 0) {
                    $this->error(lang("手机号 {$data['phone']} 已经存在，请使用其它手机号！"));
                }
                if (Db::name($this->table_user)->where(['username' => $data['username']])->count('id') > 0) {
                    $this->error(lang("账号 {$data['username']} 已经存在，请使用其它账号！"));
                }
            }
            //用户权限处理
            $data['authorize'] = 2;
            /*if (!empty($data['user_id'])) {
                $isAgentSon = Db::name('xy_users')->where('id', $data['user_id'])->value('agent_id');
                if (empty($isAgentSon)) {
                    $this->error(lang("业务员ID {$data['user_id']} 未绑定代理！"));
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
                    sysoplog(lang('添加代理'), lang('新代理ID') . $data['id']);
                } else {
                    sysoplog(lang('编辑代理'), lang('新数据包') . json_encode($data, JSON_UNESCAPED_UNICODE));
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
                $this->error(lang('两次输入的密码不一致！'));
            }
            $result = NodeService::checkpwd($post['password']);
            if (empty($result['code'])) $this->error($result['msg']);
            if (Data::save($this->table, ['id' => $post['id'], 'password' => md5($post['password'])], 'id')) {
                sysoplog(lang('修改代理用户密码'), 'ID ' . $post['id']);
                $this->success(lang('密码修改成功，下次请使用新密码登录！'), '');
            } else {
                $this->error(lang('密码修改失败，请稍候再试！'));
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
