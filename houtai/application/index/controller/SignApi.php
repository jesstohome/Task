<?php
class SignApi
{

	public static function http_post($url, $data)
	{
		$options = array(    
			'http' => array(    
				'method' => 'POST',    
				'header' => 'Content-type:application/x-www-form-urlencoded',
				'header' => 'Content-Encoding : gzip',
				'content' => $data,    
				'timeout' => 15 * 60  
			)    
		); 		
		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);  
		return $result;		
	}



	public static function http_post_res($url, $data)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);  
		curl_setopt($ch, CURLOPT_POST, 1);  
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);  
		curl_setopt($ch, CURLOPT_MAXREDIRS, 4);  
		curl_setopt($ch, CURLOPT_ENCODING, "");
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1; zh-CN) AppleWebKit/535.12 (KHTML, like Gecko) Chrome/22.0.1229.79 Safari/535.12");  
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);  
	  
		$output = curl_exec($ch);  
		curl_close($ch);
		
		return $output;
	}


	public static function convToGBK($str) {
		if( mb_detect_encoding($str,"UTF-8, ISO-8859-1, GBK")!="UTF-8" ) {
			return  iconv("utf-8","gbk",$str);
		} else {
			return $str;
		}
	}


    public static function sign($signSource,$key) {
		if (!empty($key)) {
			  $signSource = $signSource."&key=".$key;
		}

		return 	md5($signSource);
	}



	public static function validateSignByKey($signSource, $key, $retsign) {
		if (!empty($key)) {
			 $signSource = $signSource."&key=".$key;
		}
		$signkey = md5($signSource);
		if($signkey == $retsign){
			return true;
		}
		return false;
	}

}


// $pay = new Demo();
// $pay->Pay();

class Pays 
{

    public function Pay($orderid, $amount, $notifyurl, $callbackurl)
    {
        // Pay_Index.html
        $host = 'https://';
        /**请改此段参数start**/
        $url = $host.'/Pay_Index';//下单地址
        $signKey = '4rg0v';//商户key
        $arraystr = [
            "pay_memberid" => '494', // 商户ID
            "pay_orderid" =>  $orderid, // 订单号
            "pay_amount" =>  $amount, // 交易金额
            "pay_bankcode" => 912, // 请求通道
            "pay_applydate" =>  date('Y-m-d H:i:s'), // 交易时间
            "pay_notifyurl" =>  $notifyurl, // 流水号
            "pay_callbackurl" => $callbackurl
        ];
        /**请改此段参数end**/
        $arraystr['pay_md5sign'] = $this->_createSign($arraystr, $signKey);
        //直接表单提交方式跳转收银台
//         echo $this->_createForm($url, $arraystr);
//         exit;
        //CURL方式
        $arraystr['pay_returntype'] = 2;
        $res = $this->curlPost($url, $arraystr);
        $res = json_decode($res, true);
        if ($res['status']=='success') {
//             header("Location: {$res['pay_url']}");//跳转收银台
            return $res;
        }else{
            return false;
        }
    }

    protected function _createForm($url, $data)
    {
        $str = '<!doctype html>
                <html>
                    <head>
                        <meta charset="utf8">
                        <title>正在跳转付款页</title>
                    </head>
                    <body onLoad="document.pay.submit()">
                    <form method="post" action="' . $url . '" name="pay">';

        foreach ($data as $k => $vo) {
            $str .= '<input type="hidden" name="' . $k . '" value="' . $vo . '">';
        }

        $str .= '</form>
                    <body>
                </html>';
        return $str;
    }
    
    function curlPost($url,$postData){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_URL,$url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
        curl_setopt($curl, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        curl_setopt($curl, CURLOPT_TIMEOUT,60); //超时时长，单位
        $result = curl_exec($curl) ;
        $HTTP_CODE= curl_getinfo($curl,CURLINFO_HTTP_CODE);
        curl_close($curl);
        return $result;
    }
    
    protected function _createSign($data, $key)
    {
        $md5str          = '';
        ksort($data);
		
        foreach ($data as $k => $vo) {
            $md5str = $md5str . $k . "=" . $vo . "&";
        }
		
		$sign = strtoupper(md5($md5str . "key=" . $key));

        return $sign;
    }

    public function callbackurl()
    {
        //直接页面跳转
        echo "支付成功";
//         $orderid    = I('request.orderid', '');//$_GET
//         $pay_status = M("Order")->where(['pay_orderid' => $orderid])->getField("pay_status");
//         if ($pay_status != 0) {
//             $this->EditMoney($orderid, '', 1);
//         } else {
//             exit("error");
//         }
    }

    public function notifyurl()
    {
        $data      = $_POST;//$_POST
        $sign      = $data['sign'];
        $key = "4rg0vdp3b6z4rivcou0tq0ig1630b1a8";
        $orderList = [];//查出订单,M('Order')->where(['pay_orderid' => $data['orderid']])->find()
		$ReturnArray = array( // 返回字段
            "memberid" => $data["memberid"], // 商户ID
            "orderid" =>  $data["orderid"], // 订单号
            "amount" =>  $data["amount"], // 交易金额
            "request_amount" => $data["request_amount"], // 请求金额
            "datetime" =>  $data["datetime"], // 交易时间
            "transaction_id" =>  $data["transaction_id"], // 支付流水号
            "returncode" => $data["returncode"],
        );
        unset($data['sign']);
        $md5Sign = $this->_createSign($ReturnArray, $key);
        $diff    = $orderList['pay_amount'] * 100 - $data['request_amount'] * 100;
        if ($md5Sign == $sign && $data["returncode"] == "00" && ($diff == 0 || abs($diff) <= 30)) {
            //您的业务逻辑
            
			return true;
        }
        return false;
    }

}

?>
