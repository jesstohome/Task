<?php
$result = [];
$pageSize = 100;
for ($i = 0; $i < 10; $i++) {
    $data = file_get_contents('https://my.xiapibuy.com/api/v4/search/search_items?' .
        'by=relevancy&keyword=0&limit=' . $pageSize . '&newest=' . ($pageSize * $i) . '&order=desc&' .
        'page_type=search&scenario=PAGE_GLOBAL_SEARCH&version=2&lang=en');
    $data = json_decode($data, true);
    foreach ($data['items'] as $val) {
        $result[] = [
            'title' => $val['item_basic']['name'],
            'price' => sprintf("%.2f", $val['item_basic']['price'] / 10000),
            'image' => 'https://cf.shopee.com.my/file/' . $val['item_basic']['image'],
        ];
    }
}
