<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Request;

/**
 * 复数订单管理
 * Class CompoundOrder
 * @package app\admin\controller
 */
class CompoundOrder extends Base
{
    /**
     * 复数订单配置列表
     */
    public function config()
    {
        if (Request::isPost()) {
            $data = Request::post();
            $result = $this->updateConfig($data);
            return json($result);
        }

        $config = Db::name('xy_compound_order_config')
            ->order('id DESC')
            ->find();

        if (!$config) {
            // 创建默认配置
            $config = [
                'trigger_order_count' => 10,
                'status' => 1,
                'create_time' => time(),
                'update_time' => time()
            ];
            Db::name('xy_compound_order_config')->insert($config);
        }

        $this->assign('config', $config);
        return $this->fetch();
    }

    /**
     * 更新配置
     */
    private function updateConfig($data)
    {
        try {
            $update_data = [
                'trigger_order_count' => intval($data['trigger_order_count']),
                'status' => intval($data['status']),
                'update_time' => time()
            ];

            $result = Db::name('xy_compound_order_config')
                ->where('id', $data['id'])
                ->update($update_data);

            if ($result !== false) {
                return ['code' => 0, 'info' => '配置更新成功'];
            } else {
                return ['code' => 1, 'info' => '配置更新失败'];
            }
        } catch (\Exception $e) {
            return ['code' => 1, 'info' => '更新失败: ' . $e->getMessage()];
        }
    }

    /**
     * 复数订单选项管理
     */
    public function options()
    {
        $config_id = Request::param('config_id', 1);
        $id = Request::param('id', 0);
        $is_edit = Request::param('is_edit', 0);

        if (Request::isPost()) {
            $data = Request::post();
            $result = $this->saveOption($data);
            return json($result);
        }

        // 如果是编辑模式且有ID参数
        if ($is_edit && $id) {
            $option = Db::name('xy_compound_order_options')
                ->where('id', $id)
                ->find();

            if (!$option) {
                return json(['code' => 1, 'info' => '选项不存在']);
            }

            $this->assign('option', $option);
            $this->assign('is_edit', true);
            $this->assign('config_id', $config_id);
            return $this->fetch('edit_option');
        }

        // 如果有ID参数，则是编辑模式
        if ($id) {
            $option = Db::name('xy_compound_order_options')
                ->where('id', $id)
                ->find();

            if (!$option) {
                return json(['code' => 1, 'info' => '选项不存在']);
            }

            $this->assign('option', $option);
            $this->assign('is_edit', true);
        } else {
            $this->assign('is_edit', false);
        }

        $options = Db::name('xy_compound_order_options')
            ->where('config_id', $config_id)
            ->order('sort ASC')
            ->select();

        $this->assign('options', $options);
        $this->assign('config_id', $config_id);
        return $this->fetch();
    }

    /**
     * 保存选项
     */
    private function saveOption($data)
    {
            $option_data = [
                'config_id' => intval($data['config_id']),
                'title' => trim($data['title']),
                'description' => trim($data['description']),
                'order_count' => intval($data['order_count']),
                'amount_type' => 1,
                'amount_value' => floatval($data['amount_value']),
                'commission_type' => 1,
                'commission_value' => floatval($data['commission_value']),
                'sort' => intval($data['sort']),
                'status' => 1,
                'update_time' => time()
            ];

            if (isset($data['id']) && $data['id']) {
                // 更新
                $result = Db::name('xy_compound_order_options')
                    ->where('id', $data['id'])
                    ->update($option_data);
            } else {
                // 新增
                $option_data['create_time'] = time();
                $result = Db::name('xy_compound_order_options')->insert($option_data);
            }

            if ($result !== false) {
                return $this->success('操作成功');
            } else {
                return $this->error('保存失败');
            }
    }

    /**
     * 删除选项
     */
    public function deleteOption()
    {
        $id = Request::param('id');
        if (!$id) {
            return json(['code' => 1, 'info' => '参数错误']);
        }

        try {
            $result = Db::name('xy_compound_order_options')
                ->where('id', $id)
                ->delete();

            if ($result) {
                return json(['code' => 0, 'info' => '删除成功']);
            } else {
                return json(['code' => 1, 'info' => '删除失败']);
            }
        } catch (\Exception $e) {
            return json(['code' => 1, 'info' => '删除失败: ' . $e->getMessage()]);
        }
    }

    /**
     * 编辑选项
     */
    public function edit_option()
    {
        $id = Request::param('id', 0);
        $config_id = Request::param('config_id', 1);

        if (Request::isPost()) {
            $data = Request::post();
            $result = $this->saveOption($data);
            return $this->success('操作成功');
            return json($result);
        }

        // 获取选项数据用于编辑
        if ($id) {
            $option = Db::name('xy_compound_order_options')
                ->where('id', $id)
                ->find();

            if (!$option) {
                return json(['code' => 1, 'info' => '选项不存在']);
            }

            $this->assign('option', $option);
        }

        $this->assign('config_id', $config_id);
        return $this->fetch();
    }

    /**
     * 复数订单日志
     */
    public function logs()
    {
        $where = [];
        $uid = Request::param('uid');
        $username = Request::param('username');
        $status = Request::param('status');

        // 用户ID搜索
        if ($uid) {
            $where[] = ['l.uid', '=', $uid];
        }

        // 用户名搜索
        if ($username) {
            $user_ids = Db::name('xy_users')
                ->where('username', 'like', '%' . $username . '%')
                ->column('id');
            if ($user_ids) {
                $where[] = ['l.uid', 'in', $user_ids];
            } else {
                $where[] = ['l.uid', '=', 0]; // 无匹配结果
            }
        }

        // 状态搜索
        if ($status !== '' && $status !== null) {
            $where[] = ['l.status', '=', $status];
        }

        $logs = Db::name('xy_compound_order_log')
            ->alias('l')
            ->join('xy_users u', 'l.uid = u.id', 'LEFT')
            ->field('l.*, u.username')
            ->where($where)
            ->order('l.create_time DESC')
            ->paginate(20);

        // 获取分页数据
        $logs_data = $logs->getCollection()->toArray();

        // 处理每条记录的自定义选项数据
        foreach ($logs_data as &$log) {
            // 解析custom_options JSON
            $custom_options = json_decode($log['custom_options'], true);
            $log['custom_options_parsed'] = $custom_options ?: [];

            // 根据option_id找到选中的选项
            $selected_option = null;
            if ($custom_options && $log['option_id']) {
                foreach ($custom_options as $option) {
                    if (isset($option['option_id']) && $option['option_id'] == $log['option_id']) {
                        $selected_option = $option;
                        break;
                    }
                }
            }
            $log['selected_option'] = $selected_option;
        }

        $this->assign('logs', $logs_data);
        $this->assign('pagination', $logs->render());
        return $this->fetch();
    }

    /**
     * 删除复数订单日志
     */
    public function deleteLog()
    {
        $id = Request::param('id');
        if (!$id) {
            return json(['code' => 1, 'info' => '参数错误']);
        }

        try {
            $result = Db::name('xy_compound_order_log')
                ->where('id', $id)
                ->delete();

            if ($result) {
                return $this->success('操作成功');
            } else {
                return $this->error('操作失败');
            }
        } catch (\Exception $e) {
            return json(['code' => 1, 'info' => '删除失败: ' . $e->getMessage()]);
        }
    }

    /**
     * 执行数据库表创建
     */
    // public function createTables()
    // {
    //     try {
    //         // 读取SQL文件
    //         $sql_file = APP_PATH . '../compound_order_tables.sql';
    //         if (!file_exists($sql_file)) {
    //             return json(['code' => 1, 'info' => 'SQL文件不存在']);
    //         }

    //         $sql = file_get_contents($sql_file);

    //         // 分割SQL语句
    //         $statements = array_filter(array_map('trim', explode(';', $sql)));

    //         $success = 0;
    //         $errors = [];

    //         foreach ($statements as $statement) {
    //             if (empty($statement) || strpos($statement, '--') === 0) {
    //                 continue; // 跳过注释和空行
    //             }

    //             try {
    //                 Db::execute($statement);
    //                 $success++;
    //             } catch (\Exception $e) {
    //                 $errors[] = $e->getMessage();
    //             }
    //         }

    //         if (empty($errors)) {
    //             return json(['code' => 0, 'info' => "数据库表创建成功，共执行 {$success} 条语句"]);
    //         } else {
    //             return json(['code' => 1, 'info' => '部分语句执行失败: ' . implode('; ', $errors)]);
    //         }
    //     }
    // }
    /**
     * 启动用户复数订单
     */
    public function start_user_compound_order()
    {
        $uid = Request::param('uid');
        if (!$uid) {
            return json(['code' => 0, 'info' => '用户ID不能为空']);
        }

        // 检查用户是否存在
        $user = Db::name('xy_users')->where('id', $uid)->find();
        if (!$user) {
            return json(['code' => 0, 'info' => '用户不存在']);
        }

        if (Request::isPost()) {
            $data = Request::post();
            $options = isset($data['options']) ? $data['options'] : [];

            if (empty($options)) {
                return json(['code' => 0, 'info' => '请至少配置一个复数订单选项']);
            }

            // 检查用户是否已经有进行中的复数订单
            $existing_log = Db::name('xy_compound_order_log')
                ->where('uid', $uid)
                ->where('status', 1)
                ->find();

            if ($existing_log) {
                return json(['code' => 0, 'info' => '该用户已有进行中的复数订单']);
            }

            // 获取用户今日完成的订单数作为触发单数
            // $today_start = strtotime(date('Y-m-d'));
            // $trigger_count = Db::name('xy_convey')
            //     ->where('uid', $uid)
            //     ->where('status', 1)
            //     ->where('addtime', '>=', $today_start)
            //     ->count();

            // 创建复数订单记录，使用第一个选项作为主要选项
            //$first_option = reset($options);
            $log_data = [
                'uid' => $uid,
                'config_id' => 1, // 默认配置ID
                'option_id' => 0, // 自定义选项，触发时用户选择了哪个选项
                'trigger_order_id' => '', // 暂时为空
                'total_orders' => 0,//触发前执行到了第几单
                'completed_orders' => 0,//当前复数任务做到了第几单
                'status' => 1, // 进行中
                'custom_options' => json_encode($options), // 保存自定义选项数据
                'trigger_count' => $data['trigger_count'], // 触发时的订单数,接下来的第几单触发
                'create_time' => time(),
                'update_time' => time()
            ];

            $result = Db::name('xy_compound_order_log')->insert($log_data);

            if ($result) {
                return json(['code' => 1, 'info' => '复数订单已启动']);
            } else {
                return json(['code' => 0, 'info' => '启动失败，请重试']);
            }
        }

        // 获取默认的复数订单选项作为模板
        $config = Db::name('xy_compound_order_config')
            ->where('status', 1)
            ->find();

        if (!$config) {
            // 创建默认配置
            $config = [
                'trigger_order_count' => 10,
                'status' => 1,
                'create_time' => time(),
                'update_time' => time()
            ];
            Db::name('xy_compound_order_config')->insert($config);
        }

        $default_options = Db::name('xy_compound_order_options')
            ->where('config_id', $config['id'] ?? 1)
            ->where('status', 1)
            ->order('sort ASC')
            ->select();

        // 获取用户今日完成的订单数
        // $today_start = strtotime(date('Y-m-d'));
        // $trigger_count = Db::name('xy_convey')
        //     ->where('uid', $uid)
        //     ->where('status', 1)
        //     ->where('addtime', '>=', $today_start)
        //     ->count();

        $this->assign('user', $user);
        $this->assign('default_options', $default_options);
        //$this->assign('trigger_count', $trigger_count);
        return $this->fetch('start_user_compound_order');
    }
}