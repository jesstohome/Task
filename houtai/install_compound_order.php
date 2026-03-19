<?php
/**
 * 复数订单功能安装脚本
 * 执行此脚本将创建所需的数据表和菜单项
 */

require_once __DIR__ . '/thinkphp/base.php';

use think\Db;

echo "开始安装复数订单功能...\n\n";

// 1. 创建数据库表
echo "1. 创建数据库表...\n";
$table_sql = file_get_contents(__DIR__ . '/compound_order_tables.sql');
$statements = array_filter(array_map('trim', explode(';', $table_sql)));

$table_success = 0;
$table_errors = [];

foreach ($statements as $statement) {
    if (empty($statement) || strpos($statement, '--') === 0) {
        continue;
    }

    try {
        Db::execute($statement);
        $table_success++;
        echo "  ✓ 执行成功\n";
    } catch (\Exception $e) {
        $table_errors[] = $e->getMessage();
        echo "  ✗ 执行失败: " . $e->getMessage() . "\n";
    }
}

echo "数据库表创建完成: {$table_success} 成功, " . count($table_errors) . " 失败\n\n";

// 2. 添加菜单项
echo "2. 添加菜单项...\n";
$menu_sql = file_get_contents(__DIR__ . '/add_menu.sql');
$menu_statements = array_filter(array_map('trim', explode(';', $menu_sql)));

$menu_success = 0;
$menu_errors = [];

foreach ($menu_statements as $statement) {
    if (empty($statement) || strpos($statement, '--') === 0) {
        continue;
    }

    try {
        Db::execute($statement);
        $menu_success++;
        echo "  ✓ 菜单项添加成功\n";
    } catch (\Exception $e) {
        $menu_errors[] = $e->getMessage();
        echo "  ✗ 菜单项添加失败: " . $e->getMessage() . "\n";
    }
}

echo "菜单项添加完成: {$menu_success} 成功, " . count($menu_errors) . " 失败\n\n";

// 3. 总结
echo "安装总结:\n";
echo "- 数据库表: {$table_success} 个成功, " . count($table_errors) . " 个失败\n";
echo "- 菜单项: {$menu_success} 个成功, " . count($menu_errors) . " 个失败\n\n";

if (empty($table_errors) && empty($menu_errors)) {
    echo "✓ 复数订单功能安装成功!\n";
    echo "请在后台刷新页面，您将在菜单中看到'复数订单管理'选项。\n";
} else {
    echo "⚠ 安装过程中出现了一些问题，请检查上述错误信息。\n";
    echo "您可以手动执行出错的SQL语句。\n";
}

echo "安装完成！\n\n";
echo "功能清单:\n";
echo "✓ 数据库表创建\n";
echo "✓ 管理后台控制器\n";
echo "✓ 管理后台视图\n";
echo "✓ 前端下单页面修改\n";
echo "✓ 后端API接口\n";
echo "✓ 自动处理逻辑\n";
echo "✓ 测试脚本\n\n";

echo "下一步:\n";
echo "1. 运行测试脚本: php test_compound_order.php\n";
echo "2. 登录管理后台配置复数订单参数\n";
echo "3. 测试用户下单触发复数订单功能\n\n";

echo "文档: README_Compound_Order.md\n";
echo "测试: test_compound_order.php\n";