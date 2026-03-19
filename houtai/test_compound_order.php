<?php
/**
 * 复数订单功能测试脚本
 * 用于验证复数订单功能的各个组件是否正常工作
 */

require_once __DIR__ . '/thinkphp/base.php';

use think\Db;
use app\admin\model\Convey;

echo "开始测试复数订单功能...\n\n";

// 1. 检查数据库表是否存在
echo "1. 检查数据库表...\n";
$tables = ['xy_compound_order_config', 'xy_compound_order_options', 'xy_compound_order_log'];
foreach ($tables as $table) {
    $exists = Db::query("SHOW TABLES LIKE '{$table}'");
    if ($exists) {
        echo "  ✓ 表 {$table} 存在\n";
    } else {
        echo "  ✗ 表 {$table} 不存在\n";
    }
}
echo "\n";

// 2. 检查配置数据
echo "2. 检查配置数据...\n";
$config = Db::name('xy_compound_order_config')->find();
if ($config) {
    echo "  ✓ 配置存在: 触发订单数 {$config['trigger_order_count']}, 状态 " . ($config['status'] ? '启用' : '禁用') . "\n";
} else {
    echo "  ✗ 配置不存在\n";
}

$options_count = Db::name('xy_compound_order_options')->count();
echo "  ✓ 选项数量: {$options_count}\n";
echo "\n";

// 3. 检查模型方法
echo "3. 检查模型方法...\n";
$convey = new Convey();

if (method_exists($convey, 'check_compound_order_trigger')) {
    echo "  ✓ check_compound_order_trigger 方法存在\n";
} else {
    echo "  ✗ check_compound_order_trigger 方法不存在\n";
}

if (method_exists($convey, 'get_compound_order_options')) {
    echo "  ✓ get_compound_order_options 方法存在\n";
} else {
    echo "  ✗ get_compound_order_options 方法不存在\n";
}

if (method_exists($convey, 'start_compound_order')) {
    echo "  ✓ start_compound_order 方法存在\n";
} else {
    echo "  ✗ start_compound_order 方法不存在\n";
}

if (method_exists($convey, 'process_compound_order_next')) {
    echo "  ✓ process_compound_order_next 方法存在\n";
} else {
    echo "  ✗ process_compound_order_next 方法不存在\n";
}
echo "\n";

// 4. 检查控制器方法
echo "4. 检查控制器方法...\n";
$rotOrderController = 'app\\index\\controller\\RotOrder';

if (method_exists($rotOrderController, 'start_compound_order')) {
    echo "  ✓ RotOrder::start_compound_order 方法存在\n";
} else {
    echo "  ✗ RotOrder::start_compound_order 方法不存在\n";
}

if (method_exists($rotOrderController, 'process_compound_order_next')) {
    echo "  ✓ RotOrder::process_compound_order_next 方法存在\n";
} else {
    echo "  ✗ RotOrder::process_compound_order_next 方法不存在\n";
}
echo "\n";

// 5. 检查前端文件
echo "5. 检查前端文件...\n";
$view_file = __DIR__ . '/application/index/view/rot_order/index.html';
if (file_exists($view_file)) {
    $content = file_get_contents($view_file);
    if (strpos($content, 'compoundOrderModal') !== false) {
        echo "  ✓ 前端模态框存在\n";
    } else {
        echo "  ✗ 前端模态框不存在\n";
    }

    if (strpos($content, 'showCompoundOrderOptions') !== false) {
        echo "  ✓ 前端JavaScript函数存在\n";
    } else {
        echo "  ✗ 前端JavaScript函数不存在\n";
    }
} else {
    echo "  ✗ 前端文件不存在\n";
}
echo "\n";

// 6. 检查管理后台
echo "6. 检查管理后台...\n";
$admin_controller = 'app\\admin\\controller\\CompoundOrder';
if (class_exists($admin_controller)) {
    echo "  ✓ 管理控制器存在\n";
} else {
    echo "  ✗ 管理控制器不存在\n";
}

$admin_view_dir = __DIR__ . '/application/admin/view/compound_order';
if (is_dir($admin_view_dir)) {
    echo "  ✓ 管理视图目录存在\n";
} else {
    echo "  ✗ 管理视图目录不存在\n";
}
echo "\n";

echo "测试完成！\n";
echo "如果所有检查都显示 ✓ ，则复数订单功能已正确安装。\n";
echo "如果有 ✗ 标记的项目，请检查相应组件是否正确部署。\n";