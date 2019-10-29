<?php
/**
 * @class log
 * @brief 日志记录类
 */
class Log
{
	private $logInfo = array(
		'operation' => array('table' => 'log_operation','cols' => array('author','action','content')),

		// 用户level变动日志表
		'level_change' => array('table' => 'level_upgrade_log','cols' => array('admin_id','user_id','level','level_log','note')),
		// 用户sec_scocks日志表
		'sec_scocks' => array('table' => 'sec_scocks_log','cols' => array('admin_id','user_id','type','event','value','value_log','from_uid','note')),
		// 用户免费额度变更表
		'free_balance' => array('table' => 'free_balance_log','cols' => array('admin_id','user_id','type','value','value_log','note','from_id')),
		// 用户订单金额变更表
		'cash_back' => array('table' => 'cash_back_log','cols' => array('admin_id','user_id','type','value','value_log','from_oid','note')),
	);

	//获取日志对象
	public function __construct($logType = 'db')
	{

	}

	/**
	 * @brief 写入日志
	 * @param string $type 日志类型
	 * @param array  $logs 日志内容数据
	 */
	public function write($type,$logs = array())
	{
		$logInfo = $this->logInfo;
		if(!isset($logInfo[$type]))
		{
			return false;
		}

		//组合日志数据
		$tableName = $logInfo[$type]['table'];
		$content = array(
			'datetime' => ITime::getDateTime(),
		);

		foreach($logInfo[$type]['cols'] as $key => $val)
		{
			$content[$val] = isset($logs[$val]) ? $logs[$val] : isset($logs[$key]) ? $logs[$key] : '';
		}

		$logObj = new IModel($tableName);
		$logObj->setData($content);
		return $logObj->add();
	}
}