<?php
/**
 * @brief 用户中心模块
 * @class Ucenter
 * @note  前台
 */
class Ucenter extends IController implements userAuthorization
{
	public $layout = 'ucenter';

	public function init()
	{

	}
    public function index()
    {
    	//获取用户基本信息
		$user = Api::run('getMemberInfo');

		// 不存在二维码的先生成防止进去二维码页面为空情况
		if(!$user['share_qrcode']) Team::getQRCodeByUserId($user['id']);

		// 可用余额
		$freeBalance = Team::getUserFreeBalance($user['id']);

		//获取用户各项统计数据
		$statistics = Api::run('getMemberTongJi');

		//获取用户站内信条数
		$msgObj = new Mess($this->user['user_id']);
		$msgNum = $msgObj->needReadNum();

		//获取用户优惠券
		$propData= Api::run('getPropTongJi');

		$this->setRenderData(array(
			"user"       => $user,
			"statistics" => $statistics,
			"msgNum"     => $msgNum,
			"propData"   => $propData,
			"freeBalance"=> $freeBalance,
		));

        $this->initPayment();
        $this->redirect('index');
    }

	//[用户头像]上传
	function user_ico_upload()
	{
	 	$uploadDir= IWeb::$app->config['upload'].'/user_ico';
		$photoObj = new PhotoUpload($uploadDir);
		$photoObj->setIterance(false);
		$result   = current($photoObj->run());
		if($result && isset($result['flag']) && $result['flag'] == 1)
		{
			$user_id   = $this->user['user_id'];
			$user_obj  = new IModel('user');
			$dataArray = array(
				'head_ico' => $result['img'],
			);
			$user_obj->setData($dataArray);
			$user_obj->update('id = '.$user_id);

			$result['img'] = IUrl::creatUrl().$result['img'];
			ISafe::set('head_ico',$dataArray['head_ico']);
		}
		echo JSON::encode($result);
	}

    /**
     * @brief 我的订单列表
     */
    public function order()
    {
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0",false);
        $this->initPayment();
        $this->redirect('order');

    }
    /**
     * @brief 初始化支付方式
     */
    private function initPayment()
    {
        $payment = new IQuery('payment');
        $payment->fields = 'id,name,type';
        $payments = $payment->find();
        $items = array();
        foreach($payments as $pay)
        {
            $items[$pay['id']]['name'] = $pay['name'];
            $items[$pay['id']]['type'] = $pay['type'];
        }
        $this->payments = $items;
    }
    /**
     * @brief 订单详情
     * @return String
     */
    public function order_detail()
    {
        $id = IFilter::act(IReq::get('id'),'int');

        $orderObj = new order_class();
        $this->order_info = $orderObj->getOrderShow($id,$this->user['user_id']);

        if(!$this->order_info)
        {
        	IError::show(403,'订单信息不存在');
        }
        $this->redirect('order_detail',false);
    }

    //操作订单状态
	public function order_status()
	{
		$op    = IFilter::act(IReq::get('op'));
		$id    = IFilter::act( IReq::get('order_id'),'int' );
		$model = new IModel('order');

		switch($op)
		{
			case "cancel":
			{
				$model->setData(array('status' => 3));
				if($model->update("id = ".$id." and distribution_status = 0 and status = 1 and user_id = ".$this->user['user_id']))
				{
					order_class::resetOrderProp($id);
					$this->redirect("order_detail/id/$id");
				}
				//订单状态是付款或者发货则需要走退款退货申请流程
				else
				{
				    $order_goods_id = [];
				    $goodsList = Api::run('getOrderGoodsListByGoodsid',array('#order_id#',$id));
				    foreach($goodsList as $item)
				    {
				        $order_goods_id[] = $item['id'];
				    }

                    IReq::set('order_goods_id',$order_goods_id);
                    IReq::set('order_id',$id);
                    IReq::set('content','申请取消订单');
                    IReq::set('type','cancel');
				    $this->refunds_update();
				}
			}
			break;

			case "confirm":
			{
				$model->setData(array('status' => 5,'completion_time' => ITime::getDateTime()));
				if($model->update("id = ".$id." and status in (1,2) and distribution_status = 1 and user_id = ".$this->user['user_id']))
				{
					$orderRow = $model->getObj('id = '.$id);

					//确认收货后进行支付
					Order_Class::updateOrderStatus($orderRow['order_no']);

		    		//增加用户评论商品机会
					Order_Class::addGoodsCommentChange($id);
					
					// 订单完成走奖励方法
					(new Commission($id))->init();

		    		//确认收货以后直接跳转到评论页面
		    		$this->redirect('evaluation');
				}
				else
				{
				    $this->redirect('order');
				}
			}
			break;
		}
	}
    /**
     * @brief 我的地址
     */
    public function address()
    {
		//取得自己的地址
		$query = new IQuery('address');
        $query->where = 'user_id = '.$this->user['user_id'];
		$address = $query->find();
		$areas   = array();

		if($address)
		{
			foreach($address as $ad)
			{
				$temp = area::name($ad['province'],$ad['city'],$ad['area']);
				if(isset($temp[$ad['province']]) && isset($temp[$ad['city']]) && isset($temp[$ad['area']]))
				{
					$areas[$ad['province']] = $temp[$ad['province']];
					$areas[$ad['city']]     = $temp[$ad['city']];
					$areas[$ad['area']]     = $temp[$ad['area']];
				}
			}
		}

		$this->areas = $areas;
		$this->address = $address;
        $this->redirect('address');
    }
    /**
     * @brief 收货地址删除处理
     */
	public function address_del()
	{
		$id = IFilter::act( IReq::get('id'),'int' );
		$model = new IModel('address');
		$model->del('id = '.$id.' and user_id = '.$this->user['user_id']);
		$this->redirect('address');
	}
    /**
     * @brief 设置默认的收货地址
     */
    public function address_default()
    {
        $id = IFilter::act( IReq::get('id'),'int' );
        $default = IFilter::act(IReq::get('is_default'));
        $model = new IModel('address');
        if($default == 1)
        {
            $model->setData(array('is_default' => 0));
            $model->update("user_id = ".$this->user['user_id']);
        }
        $model->setData(array('is_default' => $default));
        $model->update("id = ".$id." and user_id = ".$this->user['user_id']);
        $this->redirect('address');
    }
    /**
     * @brief 售后申请页面
     */
    public function refunds_update()
    {
        $order_goods_id = IFilter::act( IReq::get('order_goods_id'),'int' );
        $order_id       = IFilter::act( IReq::get('order_id'),'int' );
        $user_id        = $this->user['user_id'];
        $content        = IFilter::act(IReq::get('content'),'text');
        $img_list       = IFilter::act(IReq::get("_imgList"));
        $type           = IFilter::act(IReq::get("type"));

        if(!$content || !$order_goods_id)
        {
            IError::show(403,"请填写售后原因和商品");
        }

        $orderDB      = new IModel('order');
        $orderRow     = $orderDB->getObj("id = ".$order_id." and user_id = ".$user_id);
        $refundResult = Order_Class::isRefundmentApply($orderRow,$order_goods_id,$type);

        //判断售后申请是否已经存在
        if($refundResult === true)
        {
            //售后申请数据
    		$updateData = array(
				'order_no'       => $orderRow['order_no'],
				'order_id'       => $order_id,
				'user_id'        => $user_id,
				'time'           => ITime::getDateTime(),
				'content'        => $content,
                'img_list'       => '',
				'seller_id'      => $orderRow['seller_id'],
				'order_goods_id' => join(",",$order_goods_id),
			);

            if(isset($img_list) && $img_list)
            {
                $img_list = explode(",",trim($img_list,","));
                $img_list = array_filter($img_list);
                if(count($img_list) > 5)
                {
                    IError::show(403,"最多上传5张图片");
                }

                $img_list = JSON::encode($img_list);
                $updateData['img_list'] = $img_list;
            }

            switch($type)
            {
                //换货
                case "exchange":
                {
            		$exchangeDB = new IModel('exchange_doc');
            		$exchangeDB->setData($updateData);
            		$id = $exchangeDB->add();

                    plugin::trigger('exchangeApplyFinish',$id);
            		$this->redirect('exchange');
                }
                break;

                //维修
                case "fix":
                {
            		$fixDB = new IModel('fix_doc');
            		$fixDB->setData($updateData);
            		$id = $fixDB->add();

                    plugin::trigger('fixApplyFinish',$id);
            		$this->redirect('fix');
                }
                break;

                //退款
                default:
                {
            		$refundsDB = new IModel('refundment_doc');
            		$refundsDB->setData($updateData);
            		$id = $refundsDB->add();

                    plugin::trigger('refundsApplyFinish',$id);
            		$this->redirect('refunds');
                }
            }
        }
        else
        {
            IError::show(403,$refundResult);
        }
    }
    /**
     * @brief 退款申请删除
     */
    public function refunds_del()
    {
        $id = IFilter::act( IReq::get('id'),'int' );
        $model = new IModel("refundment_doc");
        $result= $model->del("id = ".$id." and pay_status = 0 and user_id = ".$this->user['user_id']);
        $this->redirect('refunds');
    }
    /**
     * @brief 查看退款申请详情
     */
    public function refunds_detail()
    {
        $id = IFilter::act( IReq::get('id'),'int' );
        $refundDB = new IModel("refundment_doc");
        $refundRow = $refundDB->getObj("id = ".$id." and user_id = ".$this->user['user_id']);
        if($refundRow)
        {
        	//获取商品信息
        	$orderGoodsDB   = new IModel('order_goods');
        	$orderGoodsList = $orderGoodsDB->query("id in (".$refundRow['order_goods_id'].")");
        	if($orderGoodsList)
        	{
        		$refundRow['goods'] = $orderGoodsList;
        		$this->data = $refundRow;
        	}
        	else
        	{
	        	$this->redirect('refunds',false);
	        	Util::showMessage("没有找到要退款的商品");
        	}
        	$this->redirect('refunds_detail');
        }
        else
        {
        	$this->redirect('refunds',false);
        	Util::showMessage("退款信息不存在");
        }
    }
    /**
     * @brief 查看退款申请详情
     */
	public function refunds_edit()
	{
		$order_id = IFilter::act(IReq::get('order_id'),'int');
		if($order_id)
		{
			$orderDB  = new IModel('order');
			$orderRow = $orderDB->getObj('id = '.$order_id.' and user_id = '.$this->user['user_id']);
			if($orderRow)
			{
				$this->orderRow = $orderRow;
				$this->redirect('refunds_edit');
				return;
			}
		}
		$this->redirect('refunds');
	}

    /**
     * @brief 建议中心
     */
    public function complain_edit()
    {
        $id = IFilter::act( IReq::get('id'),'int' );
        $title = IFilter::act(IReq::get('title'),'string');
        $content = IFilter::act(IReq::get('content'),'string' );
        $user_id = $this->user['user_id'];
        $model = new IModel('suggestion');
        $model->setData(array('user_id'=>$user_id,'title'=>$title,'content'=>$content,'time'=>ITime::getDateTime()));
        if($id =='')
        {
            $model->add();
        }
        else
        {
            $model->update('id = '.$id.' and user_id = '.$this->user['user_id']);
        }
        $this->redirect('complain');
    }
    /**
     * @brief 删除消息
     * @param int $id 消息ID
     */
    public function message_del()
    {
        $id = IFilter::act( IReq::get('id') ,'int' );
        $msg = new Mess($this->user['user_id']);
        $msg->delMessage($id);
        $this->redirect('message');
    }
    public function message_read()
    {
        $id     = IFilter::act( IReq::get('id'),'int' );
        $msgObj = new Mess($this->user['user_id']);
        $content= $msgObj->readMessage($id);
        $result = array('status' => 'fail','error' => '读取内容错误');
        if($content)
        {
            $msgObj->writeMessage($id,1);
            $result = array('status' => 'success','data' => $content);
        }
        die(JSON::encode($result));
    }

    //[修改密码]修改动作
    function password_edit()
    {
    	$user_id    = $this->user['user_id'];

    	$fpassword  = IReq::get('fpassword');
    	$password   = IReq::get('password');
    	$repassword = IReq::get('repassword');

    	$userObj    = new IModel('user');
    	$where      = 'id = '.$user_id;
    	$userRow    = $userObj->getObj($where);

		if(!preg_match('|\w{6,32}|',$password))
		{
			$message = '密码格式不正确，请重新输入';
		}
    	else if($password != $repassword)
    	{
    		$message  = '二次密码输入的不一致，请重新输入';
    	}
    	else if(md5($fpassword) != $userRow['password'])
    	{
    		$message  = '原始密码输入错误';
    	}
    	else
    	{
    		$passwordMd5 = md5($password);
	    	$dataArray = array(
	    		'password' => $passwordMd5,
	    	);

	    	$userObj->setData($dataArray);
	    	$result  = $userObj->update($where);
	    	if($result)
	    	{
	    		ISafe::set('user_pwd',$passwordMd5);
	    		$message = '密码修改成功';
	    	}
	    	else
	    	{
	    		$message = '密码修改失败';
	    	}
		}

    	$this->redirect('password',false);
    	Util::showMessage($message);
    }

    //[个人资料]展示 单页
    function info()
    {
    	$userData = Api::run('getMemberInfo');
    	$this->setRenderData(array('userData' => $userData));
    	$this->redirect('info');
    }

    //[个人资料] 修改 [动作]
    function info_edit_act()
    {
		$email     = IFilter::act( IReq::get('email'),'string');
		$mobile    = IFilter::act( IReq::get('mobile'),'string');

    	$user_id   = $this->user['user_id'];
    	$memberObj = new IModel('member');
    	$where     = 'user_id = '.$user_id;

		if($email)
		{
			$memberRow = $memberObj->getObj('user_id != '.$user_id.' and email = "'.$email.'"');
			if($memberRow)
			{
				IError::show('邮箱已经被注册');
			}
		}

    	//地区
    	$province = IFilter::act( IReq::get('province','post') ,'string');
    	$city     = IFilter::act( IReq::get('city','post') ,'string' );
    	$area     = IFilter::act( IReq::get('area','post') ,'string' );
    	$areaArr  = array_filter(array($province,$city,$area));

    	$dataArray       = array(
    		'email'        => $email,
    		'true_name'    => IFilter::act( IReq::get('true_name') ,'string'),
    		'sex'          => IFilter::act( IReq::get('sex'),'int' ),
    		'birthday'     => IFilter::act( IReq::get('birthday') ),
    		'zip'          => IFilter::act( IReq::get('zip') ,'string' ),
    		'qq'           => IFilter::act( IReq::get('qq') , 'string' ),
    		'contact_addr' => IFilter::act( IReq::get('contact_addr'), 'string'),
    		'mobile'       => $mobile,
    		'telephone'    => IFilter::act( IReq::get('telephone'),'string'),
    		'area'         => $areaArr ? ",".join(",",$areaArr)."," : "",
    	);

    	$memberObj->setData($dataArray);
    	$memberObj->update($where);
    	$this->index();
    }

    //[账户余额] 展示[单页]
    function withdraw()
    {
    	$user_id   = $this->user['user_id'];

    	$memberObj = new IModel('member','balance');
    	$where     = 'user_id = '.$user_id;
		$memberRow = $memberObj->getObj($where);
		$memberRow['free_balance'] = Team::getUserFreeBalance($user_id);
		$memberRow['cur_free'] = Team::getFreeBalance($user_id);
		$this->memberRow = $memberRow;

		$bank = Team::getBankInfo($user_id);
		if(!$bank) {
			$this->redirect('bind_card', false);
			return Util::showMessage('请先绑定银行卡');
		}
		$this->serviceFree = Team::serviceChargeConf();
		$this->bank = $bank;

    	$this->redirect('withdraw');
    }

	//[账户余额] 提现动作
    function withdraw_act()
    {
    	$user_id = $this->user['user_id'];
    	$amount  = IFilter::act( IReq::get('amount','post') ,'float' );
		$message = '';
		
		$bank = Team::getBankInfo($user_id);
		if(!$bank) {
			$this->redirect('bind_card', false);
			return Util::showMessage('请先绑定银行卡');
		}

		// 提现密码验证
		$transPass = IFilter::act( IReq::get('password','post') ,'string');
		$isPass = Team::validateTranPasswd($user_id, $transPass);
		// 服务费 提现扣的千分比
		$serviceFree = Team::serviceChargeConf();

		$mixAmount = $this->_siteConfig->low_withdraw ? $this->_siteConfig->low_withdraw : 1;
		$memberObj = new IModel('member');
		$where     = 'user_id = '.$user_id;
		$memberRow = $memberObj->getObj($where,'balance');

		// 可用余额
		$free_balance = Team::getUserFreeBalance($user_id);

		//提现金额范围
		if(!$isPass)
		{
			$message = '提现密码不正确';
		}
		else if($amount <= $mixAmount)
		{
			$message = '提现的金额必须大于'.$mixAmount.'元';
		}
		else if($amount > $memberRow['balance'] || $amount > $free_balance)
		{
			$message = '提现的金额不能大于您的帐户余额';
		}
		else
		{
			// 免额度扣除
			$free = Team::calcFinalBalance($user_id, $amount);
			// 服务费
			$service_free = round(($amount - $free) * $serviceFree) / 100;

			$dataArray = array(
				'name'         => $bank['name'],
				'note'         => IFilter::act( IReq::get('note','post'), 'string'),
				'amount'       => $amount,
				'free_amount'  => $free,
				'user_id'      => $user_id,
				'time'         => ITime::getDateTime(),
				'service_free' => $service_free,
				'bank'         => $bank['bank'],
				'province'     => $bank['province'],
				'city'         => $bank['city'],
				'bank_branch'  => $bank['bank_branch'],
				'card_num'     => $bank['card_num'],
			);

	    	$obj = new IModel('withdraw');
	    	$obj->setData($dataArray);
	    	$id = $obj->add();
	    	if($id)
	    	{
	    	    plugin::trigger('withdrawApplyFinish',$id);
	    	}
	    	$this->redirect('withdraw');
		}

		if($message != '')
		{
			$this->memberRow = array('balance' => $memberRow['balance'], 'free_balance' => $free_balance, 'cur_free' => Team::getFreeBalance($user_id));
			$this->withdrawRow = $dataArray;
			$this->bank = Team::getBankInfo($user_id);
			$this->serviceFree = $serviceFree;
			$this->redirect('withdraw',false);
			Util::showMessage($message);
		}
    }

    //[账户余额] 提现详情
    function withdraw_detail()
    {
    	$user_id = $this->user['user_id'];

    	$id  = IFilter::act( IReq::get('id'),'int' );
    	$obj = new IModel('withdraw');
    	$where = 'id = '.$id.' and user_id = '.$user_id;
    	$this->withdrawRow = $obj->getObj($where);
    	$this->redirect('withdraw_detail');
    }

    //[提现申请] 取消
    function withdraw_del()
    {
    	$id = IFilter::act( IReq::get('id'),'int');
    	if($id)
    	{
    		$dataArray   = array('is_del' => 1);
    		$withdrawObj = new IModel('withdraw');
    		$where = 'id = '.$id.' and user_id = '.$this->user['user_id'];
    		$withdrawObj->setData($dataArray);
    		$withdrawObj->update($where);
    	}
    	$this->redirect('withdraw');
    }

    //[余额交易记录]
    function account_log()
    {
    	$user_id   = $this->user['user_id'];

    	$memberObj = new IModel('member');
    	$where     = 'user_id = '.$user_id;
    	$this->memberRow = $memberObj->getObj($where);
    	$this->redirect('account_log');
    }

    //[收藏夹]备注信息
    function edit_summary()
    {
    	$user_id = $this->user['user_id'];

    	$id      = IFilter::act( IReq::get('id'),'int' );
    	$summary = IFilter::act( IReq::get('summary'),'string' );

    	//ajax返回结果
    	$result  = array(
    		'isError' => true,
    	);

    	if(!$id)
    	{
    		$result['message'] = '收藏夹ID值丢失';
    	}
    	else if(!$summary)
    	{
    		$result['message'] = '请填写正确的备注信息';
    	}
    	else
    	{
	    	$favoriteObj = new IModel('favorite');
	    	$where       = 'id = '.$id.' and user_id = '.$user_id;

	    	$dataArray   = array(
	    		'summary' => $summary,
	    	);

	    	$favoriteObj->setData($dataArray);
	    	$is_success = $favoriteObj->update($where);

	    	if($is_success === false)
	    	{
	    		$result['message'] = '更新信息错误';
	    	}
	    	else
	    	{
	    		$result['isError'] = false;
	    	}
    	}
    	echo JSON::encode($result);
    }

    //[收藏夹]删除
    function favorite_del()
    {
    	$user_id = $this->user['user_id'];
    	$id      = IReq::get('id');

		if($id)
		{
			$id = IFilter::act($id,'int');

			$favoriteObj = new IModel('favorite');

			if(is_array($id))
			{
				$idStr = join(',',$id);
				$where = 'user_id = '.$user_id.' and id in ('.$idStr.')';
			}
			else
			{
				$where = 'user_id = '.$user_id.' and id = '.$id;
			}

			$favoriteObj->del($where);
			$this->redirect('favorite');
		}
		else
		{
			$this->redirect('favorite',false);
			Util::showMessage('请选择要删除的数据');
		}
    }

    //[我的积分] 单页展示
    function integral()
    {
    	$memberObj       = new IModel('member');
    	$this->memberRow = $memberObj->getObj("user_id = ".$this->user['user_id'],'point');
    	$this->redirect('integral',false);
    }

    //[我的积分]积分兑换优惠券 动作
    function trade_ticket()
    {
    	$ticketId = IFilter::act( IReq::get('ticket_id','post'),'int' );
    	if(!$ticketId)
    	{
    	    $this->setError("请选择要兑换的优惠券");
    	}
    	else
    	{
    		$nowTime   = ITime::getDateTime();
    		$ticketObj = new IModel('ticket');
    		$ticketRow = $ticketObj->getObj('id = '.$ticketId.' and point > 0 and start_time <= "'.$nowTime.'" and end_time > "'.$nowTime.'"');
    		if(empty($ticketRow))
    		{
    		    $this->setError("此优惠券不能兑换");
    		}
    		else
    		{
	    		$memberObj = new IModel('member');
	    		$where     = 'user_id = '.$this->user['user_id'];
	    		$memberRow = $memberObj->getObj($where,'point');

	    		if($ticketRow['point'] > $memberRow['point'])
	    		{
	    		    $this->setError("积分不足，无法兑换");
	    		}
	    		else
	    		{
	    			//生成红包
					$dataArray = array(
						'condition' => $ticketRow['id'],
						'name'      => $ticketRow['name'],
						'card_name' => 'T'.IHash::random(8),
						'card_pwd'  => IHash::random(8),
						'value'     => $ticketRow['value'],
						'start_time'=> $ticketRow['start_time'],
						'end_time'  => $ticketRow['end_time'],
						'is_send'   => 1,
					);
					$propObj = new IModel('prop');
					$propObj->setData($dataArray);
					$insert_id = $propObj->add();

					//更新用户prop字段
					$memberArray = array('prop' => "CONCAT(IFNULL(prop,''),'{$insert_id},')");
					$memberObj->setData($memberArray);
					$result = $memberObj->update('user_id = '.$this->user["user_id"],'prop');

					//优惠券成功
					if($result)
					{
						$pointConfig = array(
							'user_id' => $this->user['user_id'],
							'point'   => '-'.$ticketRow['point'],
							'log'     => '积分兑换优惠券，扣除了 -'.$ticketRow['point'].'积分',
						);
						$pointObj = new Point;
						$pointObj->update($pointConfig);
					}
	    		}
    		}
    	}

    	$this->redirect('redpacket',false);
    	if($error = $this->getError())
    	{
    	    Util::showMessage($error);
    	}
    }

    /**
     * 余额付款
     * T:支付失败;
     * F:支付成功;
     */
    function payment_balance()
    {
    	$urlStr  = '';
    	$user_id = intval($this->user['user_id']);
		$return  = array(
	    	'attach'    => IReq::get('attach'),
	    	'total_fee' => IReq::get('total_fee'),
	    	'order_no'  => IReq::get('order_no'),
	    	'sign'      => IReq::get('sign'),
		);

		$paymentDB  = new IModel('payment');
		$paymentRow = $paymentDB->getObj('class_name = "balance" ');
		if(!$paymentRow)
		{
			IError::show(403,'余额支付方式不存在');
		}

		$paymentInstance = Payment::createPaymentInstance($paymentRow['id']);
		$payResult       = $paymentInstance->callback($return,$paymentRow['id'],$money,$message,$orderNo);
		if($payResult == false)
		{
			IError::show(403,$message);
		}

    	$memberObj = new IModel('member');
    	$memberRow = $memberObj->getObj('user_id = '.$user_id);

    	if(empty($memberRow))
    	{
    		IError::show(403,'用户信息不存在');
		}

		// 可用余额 减去正在提现中的余额
        $free_balance = Team::getUserFreeBalance($user_id);

    	if($free_balance < $return['total_fee'])
    	{
    	    $recharge = $return['total_fee'] - $free_balance;
    	    $this->redirect('/ucenter/online_recharge/_msg/余额不足请充值 ￥'.$recharge);
    	    return;
    	}

		//检查订单状态
		$orderObj = new IModel('order');
		$orderRow = $orderObj->getObj('order_no  = "'.$return['order_no'].'" and pay_status = 0 and status = 1 and user_id = '.$user_id);
		if(!$orderRow)
		{
			IError::show(403,'订单号【'.$return['order_no'].'】已经被处理过，请查看订单状态');
		}

		//扣除余额并且记录日志
		$logObj = new AccountLog();
		$config = array(
			'user_id'  => $user_id,
			'event'    => 'pay',
			'num'      => $return['total_fee'],
			'order_no' => str_replace("_",",",$return['attach']),
		);
		$is_success = $logObj->write($config);
		if(!$is_success)
		{
			$orderObj->rollback();
			IError::show(403,$logObj->error ? $logObj->error : '用户余额更新失败');
		}

		//订单批量结算缓存机制
		$moreOrder = Order_Class::getBatch($orderNo);
		if($money >= array_sum($moreOrder))
		{
			foreach($moreOrder as $key => $item)
			{
				$order_id = Order_Class::updateOrderStatus($key);
				if(!$order_id)
				{
					$orderObj->rollback();
					IError::show(403,'订单修改失败');
				}
			}
		}
		else
		{
			$orderObj->rollback();
			IError::show(403,'付款金额与订单金额不符合');
		}

		//支付成功结果
		plugin::trigger('setCallback','/ucenter/order');
		$this->redirect('/site/success/message/'.urlencode("支付成功"));
    }

    //发票删除
    function invoice_del()
    {
		$id = IFilter::act( IReq::get('id'),'int' );
		$model = new IModel('invoice');
		$model->del('id = '.$id.' and user_id = '.$this->user['user_id']);
		$this->redirect('invoice');
    }

    //退款申请图片上传
    function refunds_img_upload()
    {
		$photoObj = new PhotoUpload(IWeb::$app->config['upload']."/refunds/".$this->user['user_id']);
		$photoObj->setIterance(false);
		$result   = current($photoObj->run());
		echo JSON::encode($result);
    }

    //商品评价申请图片上传
    function comment_img_upload()
    {
		$photoObj = new PhotoUpload(IWeb::$app->config['upload']."/comment/".$this->user['user_id']);
		$photoObj->setIterance(false);
		$result   = current($photoObj->run());
		echo JSON::encode($result);
    }

    //商品资源下载 隐藏真实地址
    function download()
    {
        $id = IFilter::act( IReq::get('id'),'int' );
        $goodsDownloadRelationObj = new IModel('order_download_relation');
        $goodsDownloadRelationRow = $goodsDownloadRelationObj->getObj($id);
        if(!$goodsDownloadRelationRow)
        {
            IError::show(403,'未找到记录');
        }

        $goodsExtendDownloadObj = new IModel('goods_extend_download');
        $goodsExtendDownloadRow = $goodsExtendDownloadObj->getObj('goods_id = '.$goodsDownloadRelationRow['goods_id'],'url,end_time,limit_num');
        if(!$goodsExtendDownloadRow)
        {
            IError::show(403,'未找到资源');
        }

        if(ITime::getDateTime() > $goodsExtendDownloadRow['end_time'])
        {
            IError::show(403,'资源到期,停止下载,到期时间:'.$goodsExtendDownloadRow['end_time']);
        }

        if($goodsDownloadRelationRow['num'] >= $goodsExtendDownloadRow['limit_num'])
        {
            IError::show(403,'资源限制下载'.$goodsExtendDownloadRow['limit_num'].'次');
        }

        $file = $goodsExtendDownloadRow['url'];
        if(stripos($file,"http") !== 0 && !file_exists($file))
        {
            IError::show(403,'资源已失效');
        }

        //更新下载次数
        $goodsDownloadRelationObj->setData(array('num' => 'num + 1'));
        $goodsDownloadRelationObj->update('id = '.$goodsDownloadRelationRow['id'],'num');

        header('Content-Type: application/x-zip-compressed');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }

    //换货申请删除
    public function exchange_del()
    {
        $id = IFilter::act( IReq::get('id'),'int' );
        $model = new IModel("exchange_doc");
        $result= $model->del("id = ".$id." and status = 0 and user_id = ".$this->user['user_id']);
        $this->redirect('exchange');
    }

    /**
     * @brief 查看退款申请详情
     */
    public function exchange_detail()
    {
        $id  = IFilter::act( IReq::get('id'),'int' );
        $db  = new IModel("exchange_doc");
        $row = $db->getObj("id = ".$id." and user_id = ".$this->user['user_id']);
        if($row)
        {
        	//获取商品信息
        	$orderGoodsDB   = new IModel('order_goods');
        	$orderGoodsList = $orderGoodsDB->query("id in (".$row['order_goods_id'].")");
        	if($orderGoodsList)
        	{
        		$row['goods'] = $orderGoodsList;
        		$this->data = $row;
        	}
        	else
        	{
	        	$this->redirect('exchange',false);
	        	Util::showMessage("没有找到申请售后的商品");
        	}
        	$this->redirect('exchange_detail');
        }
        else
        {
        	$this->redirect('exchange',false);
        	Util::showMessage("申请信息不存在");
        }
    }

    //维修申请删除
    public function fix_del()
    {
        $id = IFilter::act( IReq::get('id'),'int' );
        $model = new IModel("fix_doc");
        $result= $model->del("id = ".$id." and status = 0 and user_id = ".$this->user['user_id']);
        $this->redirect('fix');
    }

    /**
     * @brief 查看退款申请详情
     */
    public function fix_detail()
    {
        $id  = IFilter::act( IReq::get('id'),'int' );
        $db  = new IModel("fix_doc");
        $row = $db->getObj("id = ".$id." and user_id = ".$this->user['user_id']);
        if($row)
        {
        	//获取商品信息
        	$orderGoodsDB   = new IModel('order_goods');
        	$orderGoodsList = $orderGoodsDB->query("id in (".$row['order_goods_id'].")");
        	if($orderGoodsList)
        	{
        		$row['goods'] = $orderGoodsList;
        		$this->data = $row;
        	}
        	else
        	{
	        	$this->redirect('fix',false);
	        	Util::showMessage("没有找到申请售后的商品");
        	}
        	$this->redirect('fix_detail');
        }
        else
        {
        	$this->redirect('fix',false);
        	Util::showMessage("申请信息不存在");
        }
    }

    //退货物流信息更新
    function refunds_freight()
    {
        $user_freight_id = IFilter::act(IReq::get('user_freight_id'),'int');
        $user_delivery_code = IFilter::act(IReq::get('user_delivery_code'));
        $id = IFilter::act(IReq::get('id'),'int');

        $db = new IModel('refundment_doc');
        $db->setData([
            "pay_status" => 4,
            "user_freight_id" => $user_freight_id,
            "user_delivery_code" => $user_delivery_code,
            "user_send_time" => ITime::getDateTime(),
        ]);

        if($db->update("id = ".$id." and user_id = ".$this->user['user_id']))
        {
            plugin::trigger('refundDocUpdate',$id);
        }
        $this->redirect('refunds');
    }

    //换货物流信息更新
    function exchange_freight()
    {
        $user_freight_id = IFilter::act(IReq::get('user_freight_id'),'int');
        $user_delivery_code = IFilter::act(IReq::get('user_delivery_code'));
        $id = IFilter::act(IReq::get('id'),'int');

        $db = new IModel('exchange_doc');
        $db->setData([
            "status" => 4,
            "user_freight_id" => $user_freight_id,
            "user_delivery_code" => $user_delivery_code,
            "user_send_time" => ITime::getDateTime(),
        ]);

        if($db->update("id = ".$id." and user_id = ".$this->user['user_id']))
        {
            plugin::trigger('exchangeDocUpdate',$id);
        }
        $this->redirect('exchange');
    }

    //维修物流信息更新
    function fix_freight()
    {
        $user_freight_id = IFilter::act(IReq::get('user_freight_id'),'int');
        $user_delivery_code = IFilter::act(IReq::get('user_delivery_code'));
        $id = IFilter::act(IReq::get('id'),'int');

        $db = new IModel('fix_doc');
        $db->setData([
            "status" => 4,
            "user_freight_id" => $user_freight_id,
            "user_delivery_code" => $user_delivery_code,
            "user_send_time" => ITime::getDateTime(),
        ]);

        if($db->update("id = ".$id." and user_id = ".$this->user['user_id']))
        {
            plugin::trigger('fixDocUpdate',$id);
        }
        $this->redirect('fix');
	}
	

	/***
	 *
	 *
	 *                                                    __----~~~~~~~~~~~------___
	 *                                   .  .   ~~//====......          __--~ ~~
	 *                   -.            \_|//     |||\\  ~~~~~~::::... /~
	 *                ___-==_       _-~o~  \/    |||  \\            _/~~-
	 *        __---~~~.==~||\=_    -_--~/_-~|-   |\\   \\        _/~
	 *    _-~~     .=~    |  \\-_    '-~7  /-   /  ||    \      /
	 *  .~       .~       |   \\ -_    /  /-   /   ||      \   /
	 * /  ____  /         |     \\ ~-_/  /|- _/   .||       \ /
	 * |~~    ~~|--~~~~--_ \     ~==-/   | \~--===~~        .\
	 *          '         ~-|      /|    |-~\~~       __--~~
	 *                      |-~~-_/ |    |   ~\_   _-~            /\
	 *                           /  \     \__   \/~                \__
	 *                       _--~ _/ | .-~~____--~-/                  ~~==.
	 *                      ((->/~   '.|||' -_|    ~~-/ ,              . _||
	 *                                 -_     ~\      ~~---l__i__i__i--~~_/
	 *                                 _-~-__   ~)  \--______________--~~
	 *                               //.-~~~-~_--~- |-------~~~~~~~~
	 *                                      //.-~~~--\
	 *                               神兽保佑
	 *                              代码无BUG!
	 * 							Powered by lixingFan
	 */ 

	/**
	 * 个人中心--我的二维码
	 *
	 * @return void
	 */
	function my_qrcode()
	{
		$user_id = $this->user['user_id'];
		$this->qrcode = '/upload/qrcode/' . Team::getQRCodeByUserId($user_id);
		$this->redirect('my_qrcode');
	}

	/**
	 * 个人中心--激活vip
	 *
	 * @return void
	 */
	function active_vip()
	{
		$userRow = Api::run('getMemberInfo');
		
		if(Team::isVipByUserLevel($userRow['level']))
		{
			Util::showMessage('已经是vip会员了');
		}

		// 余额支付
		$paymentRow = (new IModel('payment'))->query('class_name = "balance" ');

		$this->setRenderData(array(
			"user"       => $userRow,
			"paymentRow" => $paymentRow,
		));

		$this->redirect('active_vip');
	}

	/**
	 * 个人中心--激活vip提交
	 *
	 * @return void
	 */
	function active_vip_act()
	{
		// 要激活vip的uid
        $active_id = IFilter::act(IReq::get('active_id'),'int');
		// 如果存在代表是帮激活的
		$user_id = $active_id ? $active_id : $this->user['user_id'];

		// 金额
		$amount  = IFilter::act(IReq::get('amount'), 'int');
		// callback
		plugin::trigger('setCallback', '/ucenter/index');

		// 废除老方法
		$this->setError("请用APP继续操作");

		// 根据用户level判断是否已经是vip会员
		$userRow = Api::run('getMemberInfo', $user_id);
		$isVip = Team::isVipByUserLevel($userRow['level']);
		if($isVip) $this->setError("已经是vip会员了");

		// 创建vip订单
		$order_id = Team::createVipOrder($user_id);

		if(is_array($order_id)) $this->setError($order_id['msg']);

		$result = array('code' => 0, 'msg' => '请求错误');
		if($error = $this->getError())
		{
			// error
			IError::show(403, $error);
		}
		elseif($order_id && Team::payVipOrder($order_id))
		{
			// 更新激活用户状态
			Team::updateVipUserLeve($user_id, $amount);

			// 跳转到成功页面
			$this->redirect('/site/success/message/' . urlencode("激活成功"));

			// 走Team方法
			Team::init($user_id);
		}
		else {
			IError::show(403, $result['msg']);
		}
	}

	/**
	 * 个人中心--我的会员
	 *
	 * @return void
	 */
	function team_list()
	{
		$page    = IFilter::act(IReq::get('page'), 'int');
		$user_id = IFilter::act(IReq::get('user_id'), 'int');
		$this_uid = $this->user['user_id'];
		// if(!$user_id) $user_id = $this_uid; // 不能看下面的人
		$user_id = $this_uid;

		// 只能查看当前的children
		if($user_id != $this_uid){
			$isPaternity = Team::isPaternity($this_uid, $user_id);
			if(!$isPaternity) IError::show(403, '非法请求');
		}

		$this->userRow = Api::run('getMemberInfo', $user_id);

		$query = new IQuery('user as u');
        $query->join = 'left join member as m on u.id = m.user_id';
        $query->where = 'u.parent_id = ' . $user_id;
		$query->page = $page ? $page : 1;
		$query->pagesize = 10;
		$this->query = $query;
		$this->redirect('team_list');
	}

	/**
	 * 个人中心--我的推荐--搜索--可以搜到下面的人
	 *
	 * @return void
	 */
	function team_search()
	{
		die(JSON::encode(array('flag' => 'success', 'data' => array())));
		return; // 不允许搜索
		$uid = $this->user['user_id'];
		$keyworld = IFilter::act(IReq::get('keyworld'));
		if (!$keyworld || !$uid) return;

		// vip才可以搜索
		$userRow = Team::getVipInfoByUserId($uid);
		if (!$userRow) return;

		// 只能往下查
		$where = 'm.status = 1';
		
		$sql = new IQuery('user as u');
		$sql->join = 'left join member as m on u.id = m.user_id';
		$sql->where = $where;

		// 当前符合条件的会员
		$sql->where = $where . ' and (u.username = "' . $keyworld . '" or m.mobile like "' . $keyworld . '%" or m.true_name like "' . $keyworld . '%")';
		$searchArr = $sql->find();

		// 固定会员结构 key=> uid val=>user
		$fixUserArr = Team::getFixUserArr('m.status = 1');

		// 当前符合条件的user属于uid的children
		$newArr = array();
		foreach ($searchArr as $key => $user) {
			$parent_id = $user['parent_id'];
			while (true) {
				if (!$parent_id) break;
				if ($uid == $parent_id) {
					$user['agent_show'] = Text::agentShow($user['agent_level']);
					$user['level_show'] = Text::levelShow($user['level']);
					array_push($newArr, $user);
					break;
				} else {
					$parent_id = $fixUserArr[$parent_id]['parent_id'];
				}
			}
		}

		die(JSON::encode(array('flag' => 'success', 'data' => $newArr)));
		return;
	}

	/**
	 * 个人中心--我的推荐--搜索--只搜自己推荐的人
	 */
	function team_search_self()
	{
		$user_id = $this->user['user_id'];
		$keyworld = IFilter::act(IReq::get('keyworld'));
		if (!$keyworld || !$user_id) return;

		// vip才可以搜索
		$userRow = Team::getVipInfoByUserId($user_id);
		if (!$userRow) return;

		$query = new IQuery('user as u');
        $query->join = 'left join member as m on u.id = m.user_id';
        $query->where = 'u.parent_id = '.$user_id.' and (u.username like "'.$keyworld.'%" or m.mobile like "'.$keyworld.'%" or m.true_name like "'.$keyworld.'%")';
		$data = $query->find();
		die(JSON::encode(array('flag' => 'success', 'data' => $data)));
		return;
	}

	/**
	 * 个人中心--添加会员
	 *
	 * @return void
	 */
	function add_member()
	{
		// 获取注册配置参数
		$reg_option = (new Config('site_config'))->reg_option;
		
		/*注册信息校验*/
		if($reg_option == 2) $this->setError('已关闭新用户注册');

		plugin::trigger('setCallback', '/ucenter/index');

		$from_id = IFilter::act(IReq::get('from_id'), 'int');
		if(!$from_id) $this->setError("非法请求");

		$fromInfo = Team::getVipInfoByUserId($from_id);
		if(!$fromInfo) $this->setError("非法请求");

		if($error = $this->getError())
		{
			IError::show(403, $error);
		}

		$this->setRenderData(array(
			'from_id' => $from_id,
		));

		$this->redirect('add_member');
	}

	/**
	 * 个人中心--添加会员
	 *
	 * @return void
	 */
	function add_member_act()
	{
        $true_name  = IFilter::act(IReq::get('true_name'));
        $mobile     = IFilter::act(IReq::get('mobile'));
		$mobile_code= IFilter::act(IReq::get('mobile_code','post'));
        $username   = IFilter::act(IReq::get('username'));
        $password   = IFilter::act(IReq::get('password'));
        $repassword = IFilter::act(IReq::get('repassword'));
        $captcha    = IFilter::act(IReq::get('captcha'));
		$_captcha   = ISafe::get('captcha');
		$from_id    = IFilter::act(IReq::get('from_id','post'));

		$isVip = Team::getVipInfoByUserId($this->user['user_id']);
		if(!$isVip) $this->setError('非法请求');
		
		//获取注册配置参数
		$reg_option = (new Config('site_config'))->reg_option;

		/*注册信息校验*/
		if($reg_option == 2) $this->setError('当前网站禁止新用户注册');

		// 邀请人验证
		$parent_id = Team::validateParentInfo($from_id);

		if(is_array($parent_id)) $this->setError('邀请人不正确');

		if(!preg_match('|\S{6,32}|',$password)) $this->setError('密码是字母，数字，下划线组成的6-32个字符');

		if($password != $repassword) $this->setError('2次密码输入不一致');

		//手机验证
		if($reg_option == 3)
		{
			if(IValidate::mobi($mobile) == false)
			{
				$this->setError("手机号格式不正确");
			}

			$_mobileCode = ISafe::get('code'.$mobile);
			if(!$mobile_code || !$_mobileCode || $mobile_code != $_mobileCode)
			{
				$this->setError("手机号验证码不正确");
			}
		}
		else if(!$_captcha || !$captcha || $captcha != $_captcha)
		{
			$this->setError('验证码不正确');
		}

		$userObj = new IModel('user');

		//登录名检查
		if(IValidate::name($username,2,12) == false) $this->setError('登录名必须是由2-12个字符，可以为字母、数字、下划线和中文');
		else if($userObj->getObj('username = "'.$username.'"')) $this->setError('登录名已经被注册');

		if($reg_option == 3) ISafe::clear('code'.$mobile);

		if($errorMsg = $this->getError()) {
			plugin::trigger('setCallback', '/ucenter/add_member/from_id/'.$parent_id);
			IError::show(403, $errorMsg);
		}

		// 插入user表
		$userArray = array(
			'username' => $username,
			'password' => md5($password),
			'parent_id'=> $parent_id,
		);
		$userObj->setData($userArray);
		$user_id = $userObj->add();
		if(!$user_id)
		{
			$userObj->rollback();
			plugin::trigger('setCallback', '/ucenter/add_member/from_id/'.$parent_id);
			IError::show(403, '用户创建失败');
		}
		
		// 插入member表
		$memberArray = array(
			'user_id'   => $user_id,
			'time'      => ITime::getDateTime(),
			'status'    => $reg_option == 1 ? 3 : 1,
			'mobile'    => $mobile,
			'true_name' => $true_name,
		);
		$memberObj = new IModel('member');
		$memberObj->setData($memberArray);
		$memberObj->add();

		// 注册成功就生成二维码
		Team::getQRCodeByUserId($user_id);
		$this->redirect('/ucenter/team_list/user_id/'.$parent_id);
	}

	// 钱包页
	function wallet()
	{
		$user_id = $this->user['user_id'];
		$user = Api::run('getMemberInfo');
		$bank = Team::getBankInfo($user_id);
		$freeBalance = Team::getUserFreeBalance($user_id);
		$this->setRenderData(array(
			'bank' => $bank,
			'user' => $user,
			'freeBalance' => $freeBalance,
		));

		$this->redirect('wallet');
	}

	// 设置页
	function setting()
	{
		$user = Api::run('getMemberInfo');
		$this->setRenderData(array(
			'user' => $user,
		));
		$this->redirect('setting');
	}

	// 我的银行卡页面
	function bind_card()
	{
		$bankRow = Team::getBankInfo($this->user['user_id']);
		$bankRow['addr'] = $bankRow['province'].$bankRow['city'].$bankRow['area'];
		$this->setRenderData(array('bankRow'=>$bankRow));
        $this->redirect('bind_card');
	}

	// 绑卡操作
	function bind_card_act()
	{
		$id          = IFilter::act(IReq::get('id'));
        $user_id     = $this->user['user_id'];
        $province    = IFilter::act(IReq::get('province'));
        $city        = IFilter::act(IReq::get('city'));
        $area        = IFilter::act(IReq::get('area'));
        $bank        = IFilter::act(IReq::get('bank'));
        $bank_branch = IFilter::act(IReq::get('bank_branch'));
        $card_num    = IFilter::act(IReq::get('card_num'));
        $name        = IFilter::act(IReq::get('name'));
        if(!$province||!$city||!$bank||!$bank_branch||!$card_num||!$name)
        {
            echo JSON::encode(array('flag' => 'fail', 'data' => '请完善所有信息后再提交'));
            return;
        }

        $bankCardObj =  new IModel('bank_card');
        $data = array(
            'user_id'     => $user_id,
            'name'        => $name,
            'province'    => $province,
            'city'        => $city,
            'area'        => $area,
            'bank'        => $bank,
            'card_num'    => $card_num,
            'bank_branch' => $bank_branch,
        );
        $bankCardObj->setData($data);
        if($id){
            $res = $bankCardObj->update('id = '.$id);
        }else{
            $res = $bankCardObj->add();
        }

        if($res){
            echo JSON::encode(array('flag'=>'success','data'=>'操作成功'));
            return;
        }

        echo JSON::encode(array('flag'=>'fail','data'=>'操作失败'));
        return;
	}

	// 转账页面
	function transfer()
	{
		$user_id = $this->user['user_id'];
		$type = IFilter::act(IReq::get('type'), 'string'); // 转账类型
		$userRow = (new IModel('user'))->getObj('id = '.$user_id);

		if(!$userRow['tran_password']) {
			// callback
			plugin::trigger('setCallback', '/ucenter/trans_password');
			IError::show(403, '请先设置提现密码');
		}

		if($type == 'revisit') {
			$this->free_balance = $userRow['revisit'];
		}
		else {
			$this->free_balance = Team::getUserFreeBalance($user_id);
		}
		$this->type = $type;
		$this->redirect('transfer');
	}

	// 转账提交
	function transfer_act()
	{
		$user_id = $this->user['user_id'];
		$cur_uid = IFilter::act(IReq::get('uid'), 'int');
		$amount  = IFilter::act(IReq::get('amount'), 'int');
		$password  = IFilter::act(IReq::get('password'), 'string');

		$type  = IFilter::act(IReq::get('type'), 'string'); // 转账类型

		$resData = array('flag' => 'fail', 'data'=> '操作失败');

		if(!$cur_uid || !$amount || !$password) {
			$resData['data'] = '信息不完善';
			echo JSON::encode($resData);
            return;
		}

		$isPass = Team::validateTranPasswd($user_id, $password);
		if(!$isPass) {
			$resData['data'] = '提现密码不正确';
			echo JSON::encode($resData);
            return;
		}

		$userDB = new IModel('user');
		$userRow = $userDB->getObj('id = '.$user_id);
		$curUserRow = $userDB->getObj('id = '.$cur_uid);

		if($type == 'revisit') {
			$userRevisit = $userRow['revisit'];

			if($amount > $userRevisit) {
				$resData['data'] = '转账金额不能大于可用余额';
				die(JSON::encode($resData));
			}

			// 扣当前用户
			$newRevisit = $userRevisit - $amount;
			$res = $userDB->setData(array('revisit'=>$newRevisit))->update('id = '.$user_id);

			if(!$res) die(JSON::encode($resData));

			$logDB = new IModel('revisit_log');
			$log = array(
				'user_id'   => $user_id,
				'type'      => '1',
				'time'      => ITime::getDateTime(),
				'value'     => $amount,
				'value_log' => $newRevisit,
				'event'     => '1',
				'note'      => '转账给 '.$curUserRow['username'].'，金额：'.$amount,
			);
			$logDB->setData($log)->add();

			$curRevisit = $curUserRow['revisit'] + $amount;
			$userDB->setData(array('revisit'=>$curRevisit))->update('id = '.$cur_uid);

			$cuLog = array(
				'user_id'   => $cur_uid,
				'type'      => '0',
				'time'      => ITime::getDateTime(),
				'value'     => $amount,
				'value_log' => $curRevisit,
				'event'     => '2',
				'note'      => '收到 '.$userRow['username'].'转账，金额：'.$amount,
				'from_uid'  => $user_id,
			);

			$logDB->setData($cuLog)->add();

			die(JSON::encode(array('flag'=>'success', 'data'=> '转账成功')));
		}

		$userFreeBalance = Team::getUserFreeBalance($user_id);
		if($amount > $userFreeBalance) {
			$resData['data'] = '转账金额不能大于可用余额';
			echo JSON::encode($resData);
            return;
		}

		// 扣除当前用户余额
		$memberDB = new IModel('member');
		$memberRow = $memberDB->getObj('user_id = '.$user_id);

		$newBalance = $memberRow['balance'] - $amount;
		$uRes = $memberDB->setData(array('balance'=>$newBalance))->update('user_id ='.$user_id);

		if(!$uRes) die(JSON::encode($resData));

		$accountLog = new IModel('account_log');
		$userLog = array(
			'user_id' => $user_id,
			'type' => '1',
			'event' => '22',
			'time' => ITime::getDateTime(),
			'amount' => $amount,
			'amount_log' => $newBalance,
			'note'      => '转账给 '.$curUserRow['username'].'，金额：'.$amount,
		);
		$accountLog->setData($userLog);
		$accountLog->add();
		
		// 给转账用户增加余额
		$curRow = $memberDB->getObj('user_id = '.$cur_uid);
		$curBalance = $curRow['balance'] + $amount;
		$memberDB->setData(array('balance'=>$curBalance))->update('user_id = '.$cur_uid);

		$curLog = array(
			'user_id' => $cur_uid,
			'type' => '0',
			'event' => '23',
			'time' => ITime::getDateTime(),
			'amount' => $amount,
			'amount_log' => $curBalance,
			'from_uid' => $user_id,
			'note'      => '收到 '.$userRow['username'].'转账，金额：'.$amount,
		);
		$accountLog->setData($curLog);
		$accountLog->add();

		echo JSON::encode(array('flag'=>'success', 'data'=> '转账成功'));
		return;
	}

	// 登录名验证
	function validateUsername()
	{
		$username = IFilter::act(IReq::get('username'), 'string');
		if(!$username) {
			echo JSON::encode(array('flag' => 'fail', 'data' => '信息不完善'));
            return;
		}
		$query = new IQuery('user as u');
		$query->join = 'left join member as m on u.id = m.user_id';
		$query->fields = 'm.user_id, u.username, u.head_ico, m.true_name, m.mobile';
		$query->where = 'u.username = "'.$username.'"';
		$res = $query->find();
		$recevieRow = $res[0];
		if(!$recevieRow) {
			echo JSON::encode(array('flag' => 'fail', 'data' => '用户不存在'));
            return;
		}
		echo JSON::encode(array('flag' => 'success', 'data' => $recevieRow));
		return;
	}

	// 修改提现密码页
	function tran_password()
	{
		$hasPass = false;
		$userRow = (new IModel('user'))->getObj('id = '.$this->user['user_id'], 'tran_password');
		if($userRow['tran_password']) {
			$hasPass = true;
		}
		$this->hasPass = $hasPass;
		$this->redirect('tran_password');
	}

    // [修改提现密码]修改动作
    function trans_password_edit()
    {
    	$user_id    = $this->user['user_id'];

    	$fpassword  = IReq::get('fpassword');
    	$password   = IReq::get('password');
    	$repassword = IReq::get('repassword');

    	$userObj    = new IModel('user');
    	$where      = 'id = '.$user_id;
    	$userRow    = $userObj->getObj($where);

		if(!preg_match('|\w{6,32}|',$password))
		{
			$message = '密码格式不正确，请重新输入';
		}
    	else if($password != $repassword)
    	{
    		$message  = '二次密码输入的不一致，请重新输入';
    	}
    	else if($userRow['tran_password'] && (md5($fpassword) != $userRow['tran_password']))
    	{
    		$message  = '原始密码输入错误';
    	}
    	else
    	{
    		$passwordMd5 = md5($password);
	    	$dataArray = array(
	    		'tran_password' => $passwordMd5,
	    	);

	    	$userObj->setData($dataArray);
	    	$result  = $userObj->update($where);
	    	if($result)
	    	{
				$userRow['tran_password'] = $passwordMd5;
	    		ISafe::set('user_tspwd',$passwordMd5);
	    		$message = '密码修改成功';
	    	}
	    	else
	    	{
	    		$message = '密码修改失败';
	    	}
		}
		$this->hasPass = boolval($userRow['tran_password']);
    	$this->redirect('tran_password',false);
    	Util::showMessage($message);
    }
}