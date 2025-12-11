<?php
set_time_limit(0);
date_default_timezone_set("Asia/shanghai");
//自定义抓取图片地址
$url = 'https://www.amazon.com.mx/Rosamonte-Yerba-Mate-500-g/dp/B07D189N21/ref=zg_bs_grocery_51?_encoding=UTF8&psc=1&refRID=6Q9BG48N86TTT14KV3BX';

$ip_arr = get_ips();
$ip = trim(get_rand_ip($ip_arr)); //随机ip
$content = get_content_by_url($url, $ip);

//获取标题
preg_match("/<span id=\"productTitle\" class=\"a-size-large product-title-word-break\">[\s]*(.*?)[\s]*<\/span>/i", $content, $match_title);
if (isset($match_title[1]) && $match_title[1]) {
    $title = $match_title[1];
    echo '标题为：' . $title . '<br />';
} else {
    echo '没有获取到标题，程序终止：';
    exit;
}
//获取价格
preg_match("/a\-size\-medium\sa\-color\-price\spriceBlockBuyingPriceString\"\>(.*)?\<\/span\>/i", $content, $match_price);
if (isset($match_title[1]) && $match_price[1]) {
    $price = $match_price[1];
    echo '价格为：' . $price . '<br />';
} else {
    echo '没有获取到价格，程序终止：';
}
//获取图片
preg_match("/data-old-hires\=\"(.*)?\"\sonload/i", $content, $match_img);
if (isset($match_img[1]) && $match_img[1]) {
    $img_url = $match_img[1];
    echo '图片地址为：' . $img_url . '<br />';
    echo "<img src='$img_url' width=300, height=300>";
} else {
    echo '没有获取图片地址，程序终止：';
    exit;
}

function get_rand_ip($ip_arr)
{
    if (empty($ip_arr)) {
        return false;
    }
    $ip_count = count($ip_arr);
    $rand_num = rand(0, $ip_count - 1);
    return trim($ip_arr[$rand_num]);
}

function get_ips()
{
    $fp = fopen('ip.txt', 'r+');
    $ip_arr = array();
    while ($line = fgets($fp)) {
        array_push($ip_arr, $line);
    }
    fclose($fp);
    return $ip_arr;
}

function get_content_by_url($url, $ip = '127.0.0.1')
{
    if (empty($url)) {
        return;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 5.0)');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    if (!empty($ip)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:' . $ip, 'CLIENT-IP:' . $ip));  //构造IP
    }

    $content = curl_exec($ch);
    return $content;
}