<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<title>微信支付</title>
	<meta charset="UTF-8">
	<meta http-equiv="pragma" content="no-cache">
	<meta http-equiv="cache-control" content="no-cache">
	<meta http-equiv="expires" content="0">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<link href="//cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
	正在支付......
</body>

<script type='text/javascript'>
if(history.replaceState)
{
	history.replaceState(null, null, '/ucenter/order');
}

function onBridgeReady()
{
	var sendData = '<?php echo $sendData ? $sendData : "";?>';
	if(!sendData)
	{
		alert("发起支付的参数缺失");
		return;
	}
	window.location.href='weixinPay://<?php echo $sendData;?>';

	setTimeout(function(){
		window.location.href='<?php echo $orderDetailUrl;?>';
	},3500);
}

//调用支付接口
onBridgeReady();
</script>
</html>