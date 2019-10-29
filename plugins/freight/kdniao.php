<?php
/**
 * @copyright (c) 2019 bilfow.com
 * @file kdniao.php
 * @brief 快递鸟的快递查询接口
 * @date 2019/02/22
 * @version 5.0
 */

/**
 * @class kdniao
 * @brief 物流查询类
 */
class kdniao implements freight_inter
{
	private $appid  = '1265771';
	private $appkey = '62d39e9f-caa9-4f55-be18-b4a73d877a79';

	/**
	 * @brief 查询物流快递轨迹(实时+订阅)
	 * @param $ShipperCode  string 物流公司代号
	 * @param $LogisticCode string 快递单号
	 * @return mixed
	 */
	public function line($ShipperCode,$LogisticCode)
	{
		//1,先查询delivery_trace表数据
		$traceData = order_class::readTrace($LogisticCode);
		
		if($traceData && !empty($traceData['content']))
		{
			$data = JSON::decode($traceData['content']);
			
			//当物流轨迹存在，或者最后更新时间小于五分钟，则直接输出数据库信息
			if (!empty($data['Traces']) || ITime::getDiffSec(ITime::getDateTime(),$traceData['update_time']) < 300)
			{
				return $this->response($data);
			}
		}
		
		return $this->realTime($ShipperCode,$LogisticCode);
	}

	/**
	 * @brief 订阅物流快递轨迹
	 * @param $ShipperCode  string 物流公司编号
	 * @param $LogisticCode string 快递单号
	 */
	public function subscribe($ShipperCode,$LogisticCode)
	{
		$params = ['ShipperCode' => $ShipperCode, 'LogisticCode'=> $LogisticCode];
		
		$sendData = JSON::encode($params);
		$curlData = [
			'RequestData' => $sendData,
			'EBusinessID' => $this->appid,
			'RequestType' => '1008',
			'DataType'    => 2,
			'DataSign'    => base64_encode(md5($sendData.$this->appkey)),
		];
		
		$result     = $this->curlSend("http://api.kdniao.com/api/dist",$curlData);
		$resultJson = JSON::decode($result);
		
		if(!isset($resultJson['Success']) || $resultJson['Success'] == false)
		{
			return "订阅失败：".var_export($result,true);
		}
		
		return true;
	}

	/**
	 * @brief 物流订阅推送接口
	 * @param $callbackData mixed 物流回传信息
	 */
	public function subCallback($callbackData)
	{
		$result = ['EBusinessID' => $this->appid, 'UpdateTime' => ITime::getDateTime(), 'Success' => false, 'Reason' => ''];
		
		if(!empty($callbackData['RequestData']))
		{
			$RequestData = JSON::decode($callbackData['RequestData']);
			
			if(!empty($RequestData['Data']))
			{
				foreach($RequestData['Data'] as $k => $v)
				{
					if(!empty($v['LogisticCode']) && !empty($v['Success']))
					{
						order_class::saveTrace($v['LogisticCode'],JSON::encode(['Traces' => $v['Traces'], 'State' => $v['State'], 'EstimatedDeliveryTime' => !empty($v['EstimatedDeliveryTime']) ? $v['EstimatedDeliveryTime'] : '']));
					}
				}
				$result['Success'] = true;
			}	
		}
		
		return $result;
	}
	
	/**
	 * @brief 物流轨迹统一数据格式
	 * @param $result 结果处理
	 * @return array 通用的结果集 array('result' => 'success或者fail','data' => array( array('time' => '时间','station' => '地点'),......),'reason' => '失败原因')
	 */
	public function response($result)
	{
		$status       = 'fail';
		$data         = [];
		$message      = '暂无物流记录';
		$state        = 0;
		$estimateTime = '';

		if(isset($result['Traces']) && $result['Traces'])
		{
			foreach($result['Traces'] as $key => $val)
			{
				$data[$key]['time']   = $val['AcceptTime'];
				$data[$key]['station']= $val['AcceptStation'];
			}
			
			array_multisort(array_column($data,'time'),SORT_DESC,$data);
			
			$status = 'success';
			$message = '';
		}
		
		if(isset($result['Message']))
		{
			$message = $result['Message'];
		}
		else if(isset($result['Reason']))
		{
			$message = $result['Reason'];
		}
		
		if (isset($result['State']) && $result['State'])
		{
			$state = $result['State'];
		}
		
		if (isset($result['EstimatedDeliveryTime']) && $result['EstimatedDeliveryTime'])
		{
			$estimateTime = $result['EstimatedDeliveryTime'];
		}
		
		return ['result' => $status, 'data' => $data, 'state' => $state, 'estimateTime' => $estimateTime, 'reason' => $message];
	}

	/**
	 * @brief 即时查询物流快递轨迹
	 * @param $ShipperCode string 物流公司代号
	 * @param $LogisticCode string 物流单号
	 * @return array 通用的结果集 array('result' => 'success或者fail','data' => array( array('time' => '时间','station' => '地点'),......),'reason' => '失败原因')
	 */
	private function realTime($ShipperCode,$LogisticCode)
	{
		$params = ['ShipperCode' => $ShipperCode, 'LogisticCode'=> $LogisticCode];
		
		$sendData = JSON::encode($params);
		
		$curlData = [
			'RequestData' => $sendData,
			'EBusinessID' => $this->appid,
			'RequestType' => '1002',
			'DataType'    => 2,
			'DataSign'    => base64_encode(md5($sendData.$this->appkey)),
		];
		
		$result     = $this->curlSend("http://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx",$curlData);
		$resultJson = JSON::decode($result);
		
		if(!$resultJson)
		{
			die(var_export($result));
		}
		
		//保存到本地数据库
		order_class::saveTrace($LogisticCode,JSON::encode(['Traces' => !empty($resultJson['Traces']) ? $resultJson['Traces'] : [], 'State' => $resultJson['State'], 'EstimatedDeliveryTime' => '']));
		
		//重新订阅
		$this->subscribe($ShipperCode,$LogisticCode);
		
		return $this->response($resultJson);
	}
	
	/**
	 * @brief CURL模拟提交数据
	 * @param $url string 提交的url
	 * @param $data array 要发送的数据
	 * @return mixed 返回的数据
	 */
	private function curlSend($url,$data)
	{
		$data = $this->encodeData($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
		return curl_exec($ch);
	}

	//进行数据的string字符串编码
	private function encodeData($datas)
	{
	    $temps = [];
	    foreach ($datas as $key => $value)
		{
			$temps[] = sprintf('%s=%s', $key, $value);
	    }
	    $post_data = join('&', $temps);
	    return $post_data;
	}
}
