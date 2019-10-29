<?php
/**
 * @copyright Copyright(c) 2018 mereca.com
 * @file app_alipay.php
 * @brief  APP支付宝支付
 * @author shiyan
 * @date 2018-12-15
 */

class app_alipay extends paymentPlugin
{
    public $name = '支付宝APP支付';
	public $postCharset = 'utf-8';
	public $signType = 'RSA2';
	public $method = 'alipay.trade.app.pay';
	public $format = 'json';
	public $version = '1.0';
	//公钥(支付宝公钥)
	public $pubKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAjHdQlvIvqCcUPzGIxkSg33mERpPfzjiGwTRUzDF8mYbgiM1/urfmvcJWORhbFr4vavO3ePfzRZp8BONy1pr7g46Fpj4cohCP9hUe55pWXl85nZwHrwuEvOhQx3BFLFLZaHbVEO3LqTt/dXxiZjSUdb7UXgbL8cA8ZU5S9c8YswNQ+afktVR4YySIBC9SC+Zwac5noutYmQ2AQtfplBY4lIwF84lTtvUvIU5rd+2R3aX26dZl2BPVRxa1mkhU6k0Nr3/J3o3Ush7LaKz1nzlnV8eWadQ/THvQxj9gj8yKjWFx3nFf8w4zI9FakP2u/FyyqvYX2HGHyNaIVXaH3jeSAwIDAQAB';
	//私钥
	public $priKey = 'MIIEowIBAAKCAQEAt5gymCIBCmW5SyUAcJYZG1S6LoLmQGItup9AqCfdGpvnXDtPNC55GI/j8p1/ndHMnrxM6Jewrhzr4z7qFZAT2yKj72obylxu1bqgj0FUtTzOcAN1d7EdxTCOUML7ZFxumCdYYQk9FE3OOhIDqZyQ+lKA1kYVbtjW36Pdcoa6YN0znaFg0ip/VdbLEerZIeGDda0ivYA36ed/fRfalorQaj6Q3dxRElgldrN08fT7w9BQEwlPdkqUFOIaGgeYsSUmJ1u2a1LT7gIb2MqVBlIQ32g2rHnndnIf1wGhxUWWsGLLE2/U+IChxruVVHSz841kW8PYgI3TW8i6evx3536rHQIDAQABAoIBAGwbBH+sViyHJYpn6VBiMbp0M4U7stTqer7PE0Vw47LNZnhavBKf4tJht/meYAzQAsrdWfQDjheYFBYlb7Tut6JrTVimhGKt9t2HHQ/9iiGGApDWmI64Di2Un9hSV7EK9FxHnrTUudCA2BQ9k0aGWJ/tgMurTOeOa0gYt0a+qnRqC1dSumebTzfFjhi0WVWQ9MXWWKvA5cIxX9Dy8r8YjfvTs5bci/8xPbjAHT+df8+qibXb/fAaSfnbR4uzkZBQid7EAsIwrMm/f8f0iQwZdKHaNuiVA41yYa+4+9q9Wg/aQr7aQFW+JK6enkSSGxw+f8HHMykybg7gHi234C35dEECgYEA8D2iktU4S3ypHKs7PJMfbXYqRkf+BtqeJOlPn4k1TFym4S0FD3zWI2eHPwS+qOdZFOD1XvcSAHRJGk0Fh+3X+6Q41YXLw3DACkx3/V41cfKn/hUuW9UVtEnzh3Rqut5Jz/MzBWQs6w4LSH95XCijUewIkxoQ9HKSpMjYz4DW/kUCgYEAw6NNRYOx0n8E9q6HDU6X/D2A7xl10O2XpXihoxIJEtL6XKhqOtvpRxPscjd+NjjHrWa2eE3qseBu9ThPSc0sAkamT7T8IcmcmNsJpz30JkXnctSyzpN0+Jlc8TN8S2g+75NeLznnkOdKF99rxoJXurw2oLgdAN4a5Gu2zBDGkvkCgYEAsI1rQ6Nk2r0DfykrwGmSyBv2F25i9mCFpjS8Kk9olvTkQ0mVlXs12BEGaL6w62oRonFsgdzrIuBStPxzmyClAK8AgZLxW3EqAKeP6ujoOBSPdv/T8PMZH0TVru9UXH5uGl/tWAH1rMzGaAIeiybmV5cx+gFHAo6MzIM6KszRs00CgYArpuFT9GmV/S9/VzvdFT9GUfbV4slt/8WJb1wphZmusJKaYB2r2mu3p1NnvMgVkx/CqhtmxoPqgphfcNwILJZ4P4lWWZy0cUbWuHDz9xfl/k0BS0JGY5KC8b1SOFmwfaclT62BPhtUMrdOklR665Rlnx9VRx95lRVNCFe2OrwECQKBgBR80rnmtE5PfYiFafMfpM6KJJJhnptb4QxOSp/jCETa9rv8vx3RLz90og+3CtqP19t8abQuoBI+IT+ijNMu3fBQSMMozs0VoWQrlKreI38PjVrjEzJ5+dlTwqspKQmPYfQLYN9le00Jry55fOQxdHMTKEfyMGxd3SLgPA9Z3t/v';
	
	/**
	 * @see paymentplugin::getSubmitUrl()
	 */
	public function getSubmitUrl()
	{
		return 'https://openapi.alipay.com/gateway.do?charset=utf-8';
	}

	/**
	 * @see paymentplugin::notifyStop()
	 */
	public function notifyStop()
	{
		echo "success";
	}

	/**
	 * @see paymentplugin::callback()
	 */
	public function callback($callbackData,&$paymentId,&$money,&$message,&$orderNo)
	{
		$sign = $callbackData['sign'];
				
		unset($callbackData['sign_type']);
		unset($callbackData['sign']);
		$logObj = new IFileLog('callback/'.date('Y-m-d').'.log');
	    $logObj->write('_GET');
	    $logObj->write($_GET);
	    $logObj->write('_POST');			
	    $logObj->write($_POST);			
		if($this->verify($this->argSort($callbackData),$sign,$this->signType))
		{
			//回传数据
			$orderNo = $callbackData['out_trade_no'];
			$money   = $callbackData['total_amount'];
			$logObj->write('callbackData');			
			$logObj->write($callbackData);			
					
			//记录等待发货流水号
			if($callbackData['trade_status'] == 'TRADE_SUCCESS' && isset($callbackData['trade_no']))
			{
				$this->recordTradeNo($orderNo,$callbackData['trade_no']);
			}

			if($callbackData['trade_status'] == 'TRADE_FINISHED' || $callbackData['trade_status'] == 'TRADE_SUCCESS')
			{
				return true;
			}
		}
				
		return false;
	}

	/**
	 * @see paymentplugin::serverCallback()
	 */
	public function serverCallback($callbackData,&$paymentId,&$money,&$message,&$orderNo)
	{
		return $this->callback($callbackData,$paymentId,$money,$message,$orderNo);
	}

	/**
	 * @see paymentplugin::getSendData()
	 */
	public function getSendData($payment)
	{
		$return = array();
		
		$return['app_id']      = $payment['M_AppId'];
		$return['timestamp']   = date("Y-m-d H:i:s");
		$return['method']      = $this->method;
		$return['format']      = $this->format;
		$return['sign_type']   = $this->signType;
		$return['charset']     = $this->postCharset;
		$return['version']     = $this->version;
		$return['notify_url']  = $this->serverCallbackUrl;
		$return['biz_content'] = "{\"body\":\"订单支付\","
                . "\"subject\": \"".$payment['R_Name']."\","
                . "\"out_trade_no\": \"".$payment['M_OrderNO']."\","
                . "\"timeout_express\": \"30m\"," 
                . "\"total_amount\": \"".number_format($payment['M_Amount'], 2, '.', '')."\","
                . "\"product_code\":\"QUICK_MSECURITY_PAY\","
				. "\"goods_type\":\"1\""
                . "}";
				
		//签名结果与签名方式加入请求提交参数组中
		$return['sign'] = $this->sign($this->argSort($return),$this->signType);
				
		ksort($return);

		return array('params' => $return, 'url' => $this->getSignContent($return,true));
	}
	
	/**
	 * 对数组排序
	 * @param $para 排序前的数组
	 * return 排序后的数组
	 */
	private function argSort($para)
	{
		ksort($para);
		reset($para);
		return $para;
	}
	
	/**
	 * 生成签名结果
	 * return 签名结果字符串
	 */
	private function sign($data,$signType = "RSA")
	{
		$data = $this->getSignContent($data);
		
		$res = "-----BEGIN RSA PRIVATE KEY-----\n" .
				wordwrap($this->priKey, 64, "\n", true) .
				"\n-----END RSA PRIVATE KEY-----";
				
		if ("RSA2" == $signType)
		{
			openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
		}
		else
		{
			openssl_sign($data, $sign, $res);
		}

		$sign = base64_encode($sign);
		
		return $sign;
	}
	
	/**
	 * 验证签名结果
	 * return 签名结果字符串
	 */
	private function verify($data,$sign,$signType = 'RSA')
	{		
		$data = $this->getSignContent($data);
		
		$res = "-----BEGIN PUBLIC KEY-----\n" .
				wordwrap($this->pubKey, 64, "\n", true) .
				"\n-----END PUBLIC KEY-----";
						
		$result = false;
		
		if ("RSA2" == $signType)
		{
			$result = (openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256)===1);
		}
		else
		{
			$result = (openssl_verify($data, base64_decode($sign), $res)===1);
		}

		return $result;
	}
	
	/**
	 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
	 * @param $para 需要拼接的数组
	 * return 拼接完成以后的字符串
	 */
	private function getSignContent($params,$encode = false)
	{
		ksort($params);

		$stringToBeSigned = "";

		$i = 0;

		foreach ($params as $k => $v)
		{
			if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1))
			{
				$v = $this->characet($v, $this->postCharset);

				$v = $encode ? urlencode($v) : $v;

				if ($i == 0) {
					$stringToBeSigned .= "$k" . "=" . "$v";
				} else {
					$stringToBeSigned .= "&" . "$k" . "=" . "$v";
				}
				$i++;
			}
		}

		unset($k,$v);

		return $stringToBeSigned;
	}
	
	/**
	 * 转换字符集编码
	 * @param $data
	 * @param $targetCharset
	 * @return string
	 */
	private function characet($data, $targetCharset)
	{
		if (!empty($data))
		{
			$fileType = $this->postCharset;
			
			if (strcasecmp($fileType, $targetCharset) != 0)
			{
				$data = mb_convert_encoding($data, $targetCharset, $fileType);
			}
		}
		return $data;
	}
	
	/**
	 * 校验$value是否非空
	 *  if not set ,return true;
	 *  if is null , return true;
	 **/
	private function checkEmpty($value)
	{
		if (!isset($value))
			return true;
		if ($value === null)
			return true;
		if (trim($value) === "")
			return true;
		return false;
	}

	/**
	 * @param 获取配置参数
	 */
	public function configParam()
	{
		$result = array(
			'M_AppId'  => 'APPID',
			'M_PartnerId' => 'PID',
			'M_Email'     => '支付宝账号',
		);
		
		return $result;
	}

    public function refundMoney($payment, $amount)
    {
        $config = [
            'app_id'      => $payment['M_AppId'],
            'method'      => 'alipay.trade.refund',
            'charset'     => $this->postCharset,
            'sign_type'   => $this->signType,
            'timestamp'   => date("Y-m-d H:i:s"),
            'version'     => $this->version,
            'biz_content' => json_encode([
                'out_trade_no'  => $payment['M_OrderNO'],
                'refund_amount' => number_format($amount, 2, '.', ''),
            ]),
        ];
        $config['sign'] = $this->getSign($config);

        $data = json_decode($this->post($config), true);

        $method = 'alipay_trade_refund_response';

        if (isset($data[$method])) {
            $result = $data[$method];
            if (isset($result['code']) && $result['code'] == '10000') {
                $this->refundTradeNo($payment['M_RefundId'], $result['trade_no']);
            } else {
                throw new IException($result['msg'] . ' - ' . $result['sub_msg'], $result['code']);
            }
        }
    }

    protected function getSign($config)
    {
        $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($this->priKey, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";

        openssl_sign($this->getSignContent($config), $sign, $res, OPENSSL_ALGO_SHA256);

        return base64_encode($sign);
    }

    protected function post($data)
    {

        $ch = curl_init($this->getSubmitUrl());

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}