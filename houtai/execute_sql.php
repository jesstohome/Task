<?php
// 执行复数订单表创建脚本
require_once __DIR__ . '/../thinkphp/base.php';

use think\Db;

// 读取SQL文件
$sql = file_get_contents(__DIR__ . '/compound_order_tables.sql');

// 分割SQL语句
$statements = array_filter(array_map('trim', explode(';', $sql)));

$success = 0;
$errors = [];

foreach ($statements as $statement) {
    if (empty($statement) || strpos($statement, '--') === 0) {
        continue; // 跳过注释和空行
    }

    try {
        Db::execute($statement);
        $success++;
        echo "执行成功: " . substr($statement, 0, 50) . "...\n";
    } catch (\Exception $e) {
        $errors[] = $e->getMessage();
        echo "执行失败: " . $e->getMessage() . "\n";
    }
}

echo "\n执行完成!\n";
echo "成功: {$success} 条\n";
echo "失败: " . count($errors) . " 条\n";

if (!empty($errors)) {
    echo "\n错误详情:\n";
    foreach ($errors as $error) {
        echo "- {$error}\n";
    }
}