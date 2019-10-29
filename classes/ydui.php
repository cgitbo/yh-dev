<?php

class Ydui
{
	//CURL资源句柄
	private static $curl = null;

	// baseurl
	private static $baseUrl = 'https://116.62.232.151/service/api/';
	// private static $baseUrl = 'http://localhost:80/service/api/';

	/**
	 * 直接获得Ydui结果
	 *
	 * @param string $apiName
	 * @param array  $postData
	 * @return mixed
	 */
	public static function getData($apiName, $postData = [], $isReturn = 1)
	{
		$result = self::api($apiName, $postData, $isReturn);
		if ($result['status'] == 'success') return $result['data'];
		return [];
	}

	/**
	 * @brief 调用ydui接口
	 * 比如：Ydui::api('接口名',array('参数名' => '参数值'));
	 *
	 * @param string $apiName api名称
	 * @param array  $postData 传递参数
	 * @param int    $isReturn 是否返回结果。1:返回; 0:直接输出;
	 * @return mixed
	 */
	public static function api($apiName, $postData = [], $isReturn = 1)
	{
		$api_account = isset(IWeb::$app->config['ydui_account']) ? IWeb::$app->config['ydui_account'] : "";
		$api_key     = isset(IWeb::$app->config['ydui_key'])    ? IWeb::$app->config['ydui_key']    : "";

		if (!$api_account || !$api_key) {
			return array('status' => 'fail', 'error' => 'API账号或者API密钥未填写到后台系统');
		}

		$time = self::getTime();
		$rand = self::getRand();
		$postUrl = self::$baseUrl;

		// 需要签名的数据
		$signData = array(
			'method' => $apiName,
			'time'   => $time,
			'rand'   => $rand,
		);

		// 实际发送的数据
		$postData['api_account'] = $api_account;
		$postData['method']      = $apiName;
		$postData['time']        = $time;
		$postData['rand']        = $rand;
		$postData['sign']        = self::sign($signData, $api_key);

		if (self::$curl == null) {
			self::$curl = curl_init($postUrl);
			curl_setopt(self::$curl, CURLOPT_POST, 1);
			curl_setopt(self::$curl, CURLOPT_RETURNTRANSFER, $isReturn);
			curl_setopt(self::$curl, CURLOPT_HEADER, false);
			curl_setopt(self::$curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt(self::$curl, CURLOPT_SSL_VERIFYHOST, 0);
		}

		curl_setopt(self::$curl, CURLOPT_POSTFIELDS, http_build_query($postData));

		$result = curl_exec(self::$curl);
		if (!$result) {
			$errorMsg = curl_error(self::$curl);
			$errorMsg = $errorMsg ? $errorMsg : "CURL异常出错";
			return array('status' => 'fail', 'error' => $errorMsg);
		}

		$resultArray = JSON::decode($result);

		if ($resultArray == null) return array('status' => 'fail', 'error' => $result);

		if ($resultArray['status'] == "success" && $resultArray['data']) {
			return array('status' => 'success', 'data' => $resultArray['data']);
		}

		return array('status' => 'fail', 'error' => $resultArray['error']);
	}

	/**
	 * @brief 加密算法
	 * @param array  $param 加密的数据
	 * @param string $api_key API密钥
	 * @return 签名数据
	 */
	private static function sign($param, $api_key)
	{
		ksort($param);
		reset($param);
		return md5(http_build_query($param) . $api_key);
	}

	/**
	 * @brief 获取时间戳
	 * @return 时间戳
	 */
	private static function getTime()
	{
		return time();
	}

	/**
	 * @brief 获取随机数
	 * @return 随机数
	 */
	private static function getRand()
	{
		return rand(1000000, 99999999);
	}
}
