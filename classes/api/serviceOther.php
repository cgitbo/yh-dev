<?php

/**
 * service其他接口
 */
class ServiceOther
{
    // 获取图形验证码
    public function getCaptcha()
    {
        //配置参数
        $width      = IReq::get('w') ? IReq::get('w') : 130;
        $height     = IReq::get('h') ? IReq::get('h') : 45;
        $wordLength = IReq::get('l') ? IReq::get('l') : 5;
        $fontSize   = IReq::get('s') ? IReq::get('s') : 25;

        if (max($width, $height, $wordLength, $fontSize) > 300) {
            die(JSON::encode(array('status' => 'fail', 'error' => 'max size error')));
        }

        //创建验证码
        $ValidateObj = new Captcha();
        $ValidateObj->width  = $width;
        $ValidateObj->height = $height;
        $ValidateObj->maxWordLength = $wordLength;
        $ValidateObj->minWordLength = $wordLength;
        $ValidateObj->fontSize      = $fontSize;
        $ValidateObj->CreateImage($text);

        //设置验证码
        ISafe::set('captcha', $text);
        die(JSON::encode(array('status' => 'success', 'error' => '')));
    }

    /**
     * 获取城市数据
     *
     * @param string $type province city area
     * @return []
     */
    static function getAreas()
    {
        $query = new IModel('areas');
        $citys = $query->query('', 'parent_id, area_name, area_id');

        $provinceArr = [];
        $citysArr = [];
        $areasArr = [];

        // 固定结构 key=>value
        $tmp = [];
        foreach ($citys as $value) {
            $tmp[$value['area_id']] = $value;
        }

        // 生成树结构
        $tree = $tmp;
        foreach ($tree as $k => $item) {
            if ($item['parent_id'] != 0) $tree[$item['parent_id']]['children'][] = &$tree[$k];
        }

        // 删除无用的非根节点数据
        foreach ($tree as $key => $val) {
            if ($val['parent_id'] != 0) unset($tree[$key]);
        }

        // 遍历出结果
        foreach ($tree as $province) {
            $provinceTmp = [];
            $provinceTmp['label'] = $province['area_name'];
            $provinceTmp['value'] = $province['area_id'];
            $provinceArr[] = $provinceTmp;
            if (!is_array($province['children'])) {
                $citysArr[] = [];
                $areasArr[] = [];
                continue;
            }
            $citystmp = [];
            $areasTmp = [];
            foreach ($province['children'] as $city) {
                $cityTmp['label'] = $city['area_name'];
                $cityTmp['value'] = $city['area_id'];
                $citystmp[] = $cityTmp;
                if (!is_array($city['children'])) {
                    $areasArr[] = [];
                    continue;
                }
                $areaTmps = [];
                foreach ($city['children'] as $area) {
                    $areaTmp = [];
                    $areaTmp['label'] = $area['area_name'];
                    $areaTmp['value'] = $area['area_id'];
                    $areaTmps[] = $areaTmp;
                }
                $areasTmp[] = $areaTmps;
            }
            $citysArr[] = $citystmp;
            $areasArr[] = $areasTmp;
        }

        return array('province' => $provinceArr, 'citys' => $citysArr, 'areas' => $areasArr);
    }

    // 找回密码--获取手机验证码
    public function getMobileCode()
    {
        $username = IFilter::act(IReq::get('username'));
        $mobile = IFilter::act(IReq::get('mobile'));

        if ($username === null || !IValidate::name($username)) {
            die(JSON::encode(array('status' => 'fail', 'error' => '请输入正确的用户名')));
        }

        if ($mobile === null || !IValidate::mobi($mobile)) {
            die(JSON::encode(array('status' => 'fail', 'error' => '请输入正确的手机号码')));
        }

        $userDB = new IModel('user as u , member as m');
        $userRow = $userDB->getObj('u.username = "' . $username . '" and m.mobile = "' . $mobile . '" and u.id = m.user_id');

        if (!$userRow) {
            die(JSON::encode(array('status' => 'fail', 'error' => '手机号码与用户名不符合')));
        }
        $findPasswordDB = new IModel('find_password');
        $dataRow = $findPasswordDB->query('user_id = ' . $userRow['user_id'], '*', 'addtime desc');
        $dataRow = current($dataRow);

        //120秒是短信发送的间隔
        if (isset($dataRow['addtime']) && (time() - $dataRow['addtime'] <= 120)) {
            die(JSON::encode(array('status' => 'fail', 'error' => '申请验证码的时间间隔过短，请稍候再试')));
        }
        $mobile_code = rand(10000, 99999);
        $findPasswordDB->setData(array(
            'user_id' => $userRow['user_id'],
            'hash'    => $mobile_code,
            'addtime' => time(),
        ));
        if ($findPasswordDB->add()) {
            $result = _hsms::findPassword($mobile, array('{mobile_code}' => $mobile_code));
            if ($result == 'success') {
                die(JSON::encode(array('status' => 'success', 'error' => '')));
            }
            die(JSON::encode(array('status' => 'fail', 'error' => $result)));
        }
        die(JSON::encode(array('status' => 'fail', 'error' => '请求失败')));
    }

    // 找回密码--提交新密码
    public function findPassWordByMobile()
    {
        $username = IReq::get('username');
        if ($username === null || !IValidate::name($username)) {
            die(JSON::encode(array('status' => 'fail', 'error' => '请输入正确的用户名')));
        }

        $mobile = IReq::get("mobile");
        if ($mobile === null || !IValidate::mobi($mobile)) {
            die(JSON::encode(array('status' => 'fail', 'error' => '请输入正确的电话号码')));
        }

        $mobile_code = IFilter::act(IReq::get('mobile_code'));
        if ($mobile_code === null) {
            die(JSON::encode(array('status' => 'fail', 'error' => '请输入短信校验码')));
        }

        $pwd   = IReq::get("password");
        $repwd = IReq::get("repassword");

        if ($pwd == null || strlen($pwd) < 6 || $repwd != $pwd) {
            die(JSON::encode(array('status' => 'fail', 'error' => '新密码至少六位，且两次输入的密码应该一致。')));
        }

        $userDB = new IModel('user as u , member as m');
        $userRow = $userDB->getObj('u.username = "' . $username . '" and m.mobile = "' . $mobile . '" and u.id = m.user_id');
        if (!$userRow) die(JSON::encode(array('status' => 'fail', 'error' => '用户名与手机号码不匹配')));

        $findPasswordDB = new IModel('find_password');
        $dataRow = $findPasswordDB->getObj('user_id = ' . $userRow['user_id'] . ' and hash = "' . $mobile_code . '"');
        if (!$dataRow) die(JSON::encode(array('status' => 'fail', 'error' => '您输入的短信校验码错误')));

        //短信验证码已经过期
        if (time() - $dataRow['addtime'] > 3600) {
            $findPasswordDB->del("user_id = " . $userRow['user_id']);
            die(JSON::encode(array('status' => 'fail', 'error' => '您的短信校验码已经过期了，请重新找回密码')));
        }

        $user_id = $userRow['user_id'];

        $this_uid   = IWeb::$app->getController()->user['user_id'];

        $addtime = time() - 3600 * 72;
        $where  = " `hash`='$mobile_code' AND addtime > $addtime ";
        $where .= $this_uid ? " and user_id = " . $this_uid : "";

        $row = $findPasswordDB->getObj($where);
        if (!$row) die(JSON::encode(array('status' => 'fail', 'error' => '校验码已经超时')));

        if ($row['user_id'] != $user_id) die(JSON::encode(array('status' => 'fail', 'error' => '验证码不属于此用户')));

        //开始修改密码
        $col = 'password';
        $type = IReq::get("type");
        if ($type == 'trans') $col = 'tran_password';

        $pwd = md5($pwd);
        $userDB = new IModel("user");
        $userDB->setData(array($col => $pwd));
        if ($userDB->update("id='{$row['user_id']}'")) {
            $findPasswordDB->del("`hash`='{$mobile_code}'");
            die(JSON::encode(array('status' => 'success', 'error' => '')));
        }
        die(JSON::encode(array('status' => 'fail', 'error' => '密码修改失败，请重试')));
    }

    // 用户注册
    public function register()
    {
        $mobile     = IFilter::act(IReq::get('mobile', 'post'));
        $username   = IFilter::act(IReq::get('username', 'post'));
        $mobile_code = IFilter::act(IReq::get('mobile_code', 'post'));
        $password   = IReq::get('password', 'post');
        $repassword = IReq::get('repassword', 'post');
        $captcha    = IFilter::act(IReq::get('captcha', 'post'));

        $_captcha   = ISafe::get('captcha');

        // 邀请人
        $from_id     = IFilter::act(IReq::get('from_id', 'post'));
        $parent_name = IFilter::act(IReq::get('parent_name', 'post'));

        // 邀请人验证
        $parent_id = Team::validateParentInfo($from_id, $parent_name);

        if (is_array($parent_id)) die(JSON::encode(array('status' => 'fail', 'error' => '邀请人不正确')));

        //获取注册配置参数
        $siteConfig = new Config('site_config');
        $reg_option = $siteConfig->reg_option;

        /*注册信息校验*/
        if ($reg_option == 2) die(JSON::encode(array('status' => 'fail', 'error' => '当前网站禁止新用户注册')));

        if (!preg_match('|\S{6,32}|', $password)) die(JSON::encode(array('status' => 'fail', 'error' => '密码是字母，数字，下划线组成的6-32个字符')));

        if ($password != $repassword) die(JSON::encode(array('status' => 'fail', 'error' => '2次密码输入不一致')));

        if ($reg_option != 3 && (!$_captcha || !$captcha || $captcha != $_captcha)) {
            die(JSON::encode(array('status' => 'fail', 'error' => '图形验证码输入不正确')));
        }

        //手机验证
        if (IValidate::mobi($mobile) == false) die(JSON::encode(array('status' => 'fail', 'error' => '手机号格式不正确')));

        if ($reg_option == 3) {
            $_mobileCode = ISafe::get('code' . $mobile);
            if (!$mobile_code || !$_mobileCode || $mobile_code != $_mobileCode) {
                die(JSON::encode(array('status' => 'fail', 'error' => '手机号验证码不正确')));
            }
        }

        //登录名检查
        if (IValidate::name($username) == false) {
            die(JSON::encode(array('status' => 'fail', 'error' => '登录名必须是由2-20个字符，可以为字母、数字、下划线和中文')));
        }

        $userObj = new IModel('user');
        $userRow = $userObj->getObj('username = "' . $username . '"');
        if ($userRow) die(JSON::encode(array('status' => 'fail', 'error' => '登录名已经被注册')));

        // 默认提现密码6个1
        $transPass = '111111';
        //插入user表
        $userArray = array(
            'username'      => $username,
            'password'      => md5($password),
            'parent_id'     => $parent_id,
            'tran_password' => md5($transPass),
        );
        $userObj->setData($userArray);
        $user_id = $userObj->add();
        if (!$user_id) {
            $userObj->rollback();
            die(JSON::encode(array('status' => 'fail', 'error' => '用户创建失败')));
        }

        $userArray['id'] = $user_id;
        $userArray['head_ico'] = "";

        //插入member表
        $memberArray = array(
            'user_id' => $user_id,
            'time'    => ITime::getDateTime(),
            'status'  => $reg_option == 1 ? 3 : 1,
            'mobile'  => $mobile,
        );
        $memberObj = new IModel('member');
        $memberObj->setData($memberArray);
        $memberObj->add();

        // 注册成功就生成二维码
        Team::getQRCodeByUserId($user_id);

        if ($reg_option == 3) ISafe::clear('code' . $mobile);

        //通知事件用户注册完毕
        plugin::trigger("userRegFinish", $userArray);

        die(JSON::encode(array('status' => 'success', 'error' => '')));
    }

    // 分页信息
    public function getDataPaging($data, $query)
    {
        $result = [];
        if (is_array($data)) {
            $result['data'] = $data;
            $result['curPage'] = $query->paging->index;
            $result['totalPage'] = method_exists($query->paging, getTotalPage) ? $query->paging->getTotalPage() : null;
            $result['limit'] = $query->paging->pagesize;
        }

        return $result;
    }

    // 后台注册配置 0正常 1邮箱 2关闭 3手机
    public function getRegOption()
    {
        $siteConfig = new Config('site_config');
        return $siteConfig->reg_option;
    }

    // 支付
    public function doPay()
    {
        //获得相关参数
        $order_id   = IReq::get('order_id');
        $recharge   = IReq::get('recharge');
        $payment_id = IFilter::act(IReq::get('payment_id'), 'int');

        // 给payment::getPaymentInfo 不处理Ydui商品使用
        $origin   = IReq::get('origin');

        if ($order_id) {
            $order_id = explode("_", IReq::get('order_id'));
            $order_id = IFilter::act($order_id, 'int');

            //获取订单信息
            $orderDB  = new IModel('order');
            $orderRow = $orderDB->getObj('id = ' . current($order_id));

            if (empty($orderRow)) die(JSON::encode(array('status' => 'fail', 'error' => '要支付的订单信息不存在')));

            //判断订单是否已经支付成功了
            if ($orderRow['pay_status'] == 1) die(JSON::encode(array('status' => 'fail', 'error' => '订单已经支付成功')));

            // 判断是否vip订单 如果是判断是否已经成功了
            if ($orderRow['active_uid']) {
                $isVip = Team::getVipInfoByUserId($orderRow['active_uid']);
                if ($isVip) die(JSON::encode(array('status' => 'fail', 'error' => '已经是vip会员了')));
            }

            //更新支付方式
            if ($payment_id) {
                $orderDB->setData(['pay_type' => $payment_id]);
                foreach ($order_id as $id) {
                    $orderDB->update($id);
                }
            } else {
                $payment_id = $orderRow['pay_type'];
            }
        }

        //获取支付方式类库
        $paymentInstance = Payment::createServicePaymentInstance($payment_id);

        if ($paymentInstance && is_string($paymentInstance)) die(JSON::encode(array('status' => 'fail', 'error' => $paymentInstance)));

        //在线充值
        if ($recharge !== null) {
            $recharge   = IFilter::act($recharge, 'float');
            $paymentRow = Payment::getPaymentById($payment_id);

            //account:充值金额; paymentName:支付方式名字
            $reData   = array('account' => $recharge, 'paymentName' => $paymentRow['name']);
            $payment = Payment::getServicePaymentInfo($payment_id, 'recharge', $reData);
        }
        //订单支付
        else if ($order_id) {
            $payment = Payment::getServicePaymentInfo($payment_id, 'order', $order_id, $origin);
        }
        //其他情况
        else {
            die(JSON::encode(array('status' => 'fail', 'error' => '发生支付错误')));
        }

        if ($payment && is_string($payment)) die(JSON::encode(array('status' => 'fail', 'error' => $payment)));

        $sendData = $paymentInstance->getSendData($payment);

        if ($sendData && is_string($sendData)) die(JSON::encode(array('status' => 'fail', 'error' => $sendData)));

        // 余额支付
        if ($payment_id == 1) {
            foreach ($sendData as $key => $item) {
                IReq::set($key, $item);
            }
            return $this->payBalance();
        }

        return $sendData;
    }

    // 余额支付
    function payBalance()
    {
        $urlStr  = '';
        $user_id = intval(IWeb::$app->getController()->user['user_id']);

        $return  = array(
            'attach'    => IReq::get('attach'),
            'total_fee' => IReq::get('total_fee'),
            'order_no'  => IReq::get('order_no'),
            'sign'      => IReq::get('sign'),
        );

        $paymentDB  = new IModel('payment');
        $paymentRow = $paymentDB->getObj('class_name = "balance" ');

        if (!$paymentRow) die(JSON::encode(array('status' => 'fail', 'error' => '余额支付方式不存在')));

        $paymentInstance = Payment::createPaymentInstance($paymentRow['id']);
        $payResult       = $paymentInstance->callback($return, $paymentRow['id'], $money, $message, $orderNo);

        if ($payResult == false) die(JSON::encode(array('status' => 'fail', 'error' => $message)));

        $memberObj = new IModel('member');
        $memberRow = $memberObj->getObj('user_id = ' . $user_id);

        if (empty($memberRow)) die(JSON::encode(array('status' => 'token30401', 'error' => '用户信息不存在')));

        // 可用余额 减去正在提现中的余额
        $free_balance = Team::getUserFreeBalance($user_id);

        if ($free_balance < $return['total_fee']) {
            $recharge = $return['total_fee'] - $free_balance;
            die(JSON::encode(array('status' => 'fail', 'error' => '余额不足请充值 ￥' . $recharge)));
        }

        //检查订单状态
        $orderObj = new IModel('order');
        $orderRow = $orderObj->getObj('order_no  = "' . $return['order_no'] . '" and pay_status = 0 and status = 1 and user_id = ' . $user_id);

        if (!$orderRow) {
            die(JSON::encode(array('status' => 'fail', 'error' => '订单号【' . $return['order_no'] . '】已经被处理过，请查看订单状态')));
        }

        //扣除余额并且记录日志
        $logObj = new AccountLog();
        $config = array(
            'user_id'  => $user_id,
            'event'    => 'pay',
            'num'      => $return['total_fee'],
            'order_no' => str_replace("_", ",", $return['attach']),
        );
        $is_success = $logObj->write($config);
        if (!$is_success) {
            $orderObj->rollback();
            die(JSON::encode(array('status' => 'fail', 'error' => $logObj->error ? $logObj->error : '用户余额更新失败')));
        }

        //订单批量结算缓存机制
        $moreOrder = Order_Class::getBatch($orderNo);
        if ($money >= array_sum($moreOrder)) {
            foreach ($moreOrder as $key => $item) {
                $order_id = Order_Class::updateOrderStatus($key);
                if (!$order_id) {
                    $orderObj->rollback();
                    die(JSON::encode(array('status' => 'fail', 'error' => '订单修改失败')));
                }
            }
        } else {
            $orderObj->rollback();
            die(JSON::encode(array('status' => 'fail', 'error' => '付款金额与订单金额不符合')));
        }

        //支付成功结果
        return 'success';
    }

    // 获取 APP新版本
    public function getAppVersion()
    {
        $type = IReq::get('type');
        $type = $type ? IFilter::act($type) : 2;
        return $type == 2 ? IWeb::$app->config['app_version'] : IWeb::$app->config['android_version'];
    }

    //发送注册验证码
    public function getRegMobileCode()
    {
        $mobile   = IReq::get('mobile');
        $captcha  = IReq::get('captcha');
        $_captcha = ISafe::get('captcha');

        if (IValidate::mobi($mobile) == false) {
            die(JSON::encode(array('status' => 'fail', 'error' => '请填写正确的手机号码')));
        }

        $mobile_code = rand(1000, 9999);
        $result = _hsms::checkCode($mobile, array('{mobile_code}' => $mobile_code));
        if ($result == 'success') {
            //删除图形验证码防止重复提交
            ISafe::set('captcha', '');
            ISafe::set("code" . $mobile, $mobile_code);
            return $mobile_code;
        }
        die(JSON::encode(array('status' => 'fail', 'error' => $result)));
    }

    // 获取协议
    public function getRegAgreement()
    {
        $siteConfig = new Config('site_config');
        return $siteConfig->reg_agreement;
    }

    // 获取关于我们
    public function getAboutUs()
    {
        $siteConfig = new Config('site_config');
        return $siteConfig->about_us;
    }

    // 获取手机号码
    public function getPhoneWhitelist()
    {
        $pluginDB = new IModel('plugin');
        $pluginRow  = $pluginDB->getObj('class_name = "anyCall"');
        if (!$pluginRow['config_param']) die(JSON::encode(array('status' => 'fail', 'error' => '后台参数不完整')));

        $config = JSON::decode($pluginRow['config_param']);

        $whiteName = $config['whiteName'];
        $whitelist = $config['whiteList'];
        $phoneArr = explode(',', $whitelist);

        foreach ($phoneArr as $value) {
            $phone[] = [
                type => '主要',
                phone => trim($value),
            ];
        }

        $result['name'] = $whiteName;
        $result['phone'] = $phone;

        return $result;
    }

    // 获取wxAccessToken
    public function getWxAccessToken()
    {
        return wechat::getAccessToken();
    }

    // 获取getWxOpenId
    public function getWxOpenId()
    {
        return wechat::getWxOpenId();
    }

    // 检查订单是否支付成功
    public function isPayOrderPaySuccess()
    {
        $type = IFilter::act(IReq::get('type'));
        $id = IFilter::act(IReq::get('id'));

        $db = [
            'order' => 'order',
            'recharge' => 'online_recharge',
        ];

        if (!$type || !$id || !in_array($type, array_keys($db))) throw new Exception("非法请求", 9001);

        $where = 'id = ' . $id;
        $where .= $type == 'order' ? ' and pay_status = 1' : ' and status = 1';
        $query = new IModel($db[$type]);
        $result = $query->getObj($where);

        if ($result) return 'SUCCESS';
        return 'FAIL';
    }
}
