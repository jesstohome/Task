<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | www.xydai.cn 新源代网
// +----------------------------------------------------------------------

namespace app\admin\controller;

use library\tools\Data;
use think\Db;

/**
 * 礼包管理
 * Class Gift
 * @package app\admin\controller
 */
class Gift extends Base
{

    /**
     * 指定当前数据表
     * @var string
     */
    protected $table = 'xy_gift_packages';

    /**
     * 礼包列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '礼包列表';
        $query = $this->_query($this->table)->alias('g');
        $where = [];

        if (input('uid/s', '')) $where[] = ['g.uid', '=', input('uid/s')];
        if (input('status/s', '')) $where[] = ['g.status', '=', input('status/s')];

        $query->field('g.*, u.username, u.tel')
            ->leftJoin('xy_users u', 'g.uid=u.id')
            ->where($where)
            ->order('g.id desc')
            ->page();

        return $this->fetch();
    }

    /**
     * 表单数据处理
     * @param array $data
     */
    protected function _form_filter(&$data)
    {
        if ($this->request->isPost()) {
            if (isset($data['gift_data']) && is_array($data['gift_data'])) {
                $data['gift_data'] = json_encode($data['gift_data']);
            }
        } else {
            if (isset($data['gift_data'])) {
                $data['gift_data'] = json_decode($data['gift_data'], true);
            }
        }
    }

    /**
     * 添加礼包
     * @auth true
     */
    public function add()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['created_at'] = time();
            if (Db::name($this->table)->insert($data)) {
                return $this->success('添加成功');
            } else {
                return $this->error('添加失败');
            }
        }
        return $this->fetch('form');
    }

    /**
     * 编辑礼包
     * @auth true
     */
    public function edit()
    {
        if (request()->isPost()) {
            $data = input('post.');
            if (Db::name($this->table)->where('id', $data['id'])->update($data)) {
                return $this->success('编辑成功');
            } else {
                return $this->error('编辑失败');
            }
        }
        $id = input('id', 0);
        $this->vo = Db::name($this->table)->find($id);
        return $this->fetch('form');
    }

    /**
     * 删除礼包
     * @auth true
     */
    public function delete()
    {
        $id = input('id', 0);
        if (Db::name($this->table)->where('id', $id)->delete()) {
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败');
        }
    }
}