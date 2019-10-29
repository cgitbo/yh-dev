<?php

/**
 * service个人中心相关接口
 */
class ServiceUcenter
{
    // 必须是登录用户才能使用
    function __construct()
    {
        $userid = IWeb::$app->getController()->user['user_id'];
        if (!$userid) die(JSON::encode(['status' => 'token30401', 'error' => 'userToken不存在']));
        $this->uid = $userid;
    }

    /**
     * @brief 用户登录接口
     */
    public function userLogin()
    {
        $userRow = IWeb::$app->getController()->user;
        plugin::trigger("userLoginCallback", $userRow);
    }

    //用户中心-用户信息
    public function getUserInfo($userid = '')
    {
        $userid = $userid ? $userid : IWeb::$app->getController()->user['user_id'];
        $userid = IFilter::act($userid, 'int');
        if (!$userid) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        $where = 'm.user_id = u.id and m.user_id=' . $userid;
        $tb_member = new IModel('member as m,user as u');
        $info = $tb_member->getObj($where);

        if ($info) {
            // 除去正在提现的余额 剩余的金额
            $info['remain_balance']  = Team::getUserFreeBalance($info['user_id']);
            $info['is_vip']          = Team::isVipByUserLevel($info['level']);
            $info['is_agent']        = boolval(Team::getAgentLevelByUserRow($info));
            $info['agent_text']      = Text::agentShow($info['agent_level']);
            $info['vip_text']        = Text::levelShow($info['level']);
            $info['service_percent'] = Team::serviceChargeConf();
            $info['total_reward']    = Team::getUserCount($info['user_id']);

            // 是vip没二维码就先生成
            if ($info['is_vip'] && !$info['share_qrcode']) {
                $info['share_qrcode'] = Team::getQRCodeByUserId($info['user_id']);
            }

            if ($info['group_id']) {
                $userGroup = new IModel('user_group');
                $groupRow  = $userGroup->getObj('id = ' . $info['group_id']);
                $info['group_name'] = $groupRow ? $groupRow['group_name'] : "";
            } else {
                $info['group_name'] = "";
            }
        }
        return $info;
    }

    //用户中心-获取银行卡信息
    public function getMemberBankInfo($userid = '')
    {
        $userid = $userid ? IFilter::act($userid, 'int') : IWeb::$app->getController()->user['user_id'];
        if (!$userid) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        return (new IModel('bank_card'))->getObj('user_id =' . $userid . ' and is_del = 0');
    }

    // 用户中心-修改银行卡信息
    public function editBankInfo($user_id = '')
    {
        $user_id = $user_id ? IFilter::act($user_id, 'int') : IWeb::$app->getController()->user['user_id'];
        if (!$user_id) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        $id          = IFilter::act(IReq::get('id'));
        $province    = IFilter::act(IReq::get('province'));
        $city        = IFilter::act(IReq::get('city'));
        $area        = IFilter::act(IReq::get('area'));
        $bank        = IFilter::act(IReq::get('bank'));
        $bank_branch = IFilter::act(IReq::get('bank_branch'));
        $card_num    = IFilter::act(IReq::get('card_num'));
        $name        = IFilter::act(IReq::get('true_name'));

        if (!$province || !$city || !$bank || !$bank_branch || !$card_num || !$name) {
            die(JSON::encode(array('status' => 'fail', 'error' => '请完善所有信息后再提交')));
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
        if ($id) {
            $res = $bankCardObj->update('id = ' . $id);
        } else {
            $res = $bankCardObj->add();
        }

        if ($res) return 'success';

        die(JSON::encode(array('status' => 'fail', 'error' => '操作失败')));
    }

    //用户中心-vip消费日志
    public function getUcenterRevisitLog($userid = '')
    {
        $userid = $userid ? IFilter::act($userid, 'int') : IWeb::$app->getController()->user['user_id'];
        if (!$userid) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));
        $page   = IReq::get('page') ? IFilter::act(IReq::get('page'), 'int') : 1;
        $limit  = IReq::get('limit') ? IFilter::act(IReq::get('limit'), 'int') : 10;
        $query  = new IQuery('revisit_log');
        $query->where = "user_id = " . $userid . ' and if_del = 0';
        $query->order = 'id desc';
        $query->page  = $page;
        $query->pagesize = $limit;
        return $query;
    }

    //用户中心-股权日志
    public function getUcenterStocksLog($userid = '')
    {
        $userid = $userid ? IFilter::act($userid, 'int') : IWeb::$app->getController()->user['user_id'];
        if (!$userid) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));
        $limit  = IReq::get('limit') ? IFilter::act(IReq::get('limit'), 'int') : 10;
        $page   = IReq::get('page') ? IFilter::act(IReq::get('page'), 'int') : 1;
        $query  = new IQuery('sec_scocks_log');
        $query->where = "user_id = " . $userid . ' and if_del = 0';
        $query->order = 'id desc';
        $query->page  = $page;
        $query->pagesize = $limit;
        return $query;
    }

    // 用户中心-余额日志
    public function getUcenterAccountLog($userid = '')
    {
        $userid = $userid ? IFilter::act($userid, 'int') : IWeb::$app->getController()->user['user_id'];
        if (!$userid) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));
        $limit  = IReq::get('limit') ? IFilter::act(IReq::get('limit'), 'int') : 10;
        $page   = IReq::get('page') ? IFilter::act(IReq::get('page'), 'int') : 1;
        $query  = new IQuery('account_log as l');
        $query->join = 'left join user as u on l.from_uid = u.id';
        // $query->where = "l.event in(1,2,3,4,5,6,21,22,23) and l.user_id = " . $userid;
        $query->where = "l.user_id = " . $userid;
        $query->fields = 'l.*, u.parent_id';
        $query->order = 'l.id desc';
        $query->page  = $page;
        $query->pagesize = $limit;
        return $query;
    }

    // 用户中心--消息列表
    public function getMessageList($userid = '')
    {
        $userid = $userid ? IFilter::act($userid, 'int') : IWeb::$app->getController()->user['user_id'];

        $limit  = IReq::get('limit') ? IFilter::act(IReq::get('limit'), 'int') : 10;
        $page   = IReq::get('page') ? IFilter::act(IReq::get('page'), 'int') : 1;

        $msgObj = new Mess($userid);
        $msgIds = $msgObj->getAllMsgIds();
        $msgIds = $msgIds ? $msgIds : 0;

        $query  = new IQuery('message');
        $query->where = "id in(" . $msgIds . ")";
        $query->order = "id desc";
        $query->page = $page;
        $query->pagesize = $limit;
        $query->msg  = $msgObj;

        $result = $query->find();

        foreach ($result as $key => $value) {
            $result[$key]['is_read'] = $query->msg->is_read($value['id']);
        }

        return $query->setData($result);
    }

    // 删除短消息
    public function delMessage($userid = '')
    {
        $userid = $userid ? IFilter::act($userid, 'int') : IWeb::$app->getController()->user['user_id'];
        $id = IFilter::act(IReq::get('id'), 'int');
        $msg = new Mess($userid);
        return boolval($msg->delMessage($id));
    }

    // 读取短消息
    public function readMessage($userid = '')
    {
        $userid = $userid ? IFilter::act($userid, 'int') : IWeb::$app->getController()->user['user_id'];
        $id     = IFilter::act(IReq::get('id'), 'int');
        $msgObj = new Mess($userid);
        $content = $msgObj->readMessage($id);
        $result = array('status' => 'fail', 'error' => '读取内容错误');
        if ($content) {
            $msgObj->writeMessage($id, 1);
            $result = array('status' => 'success', 'data' => $content);
        }
        die(JSON::encode($result));
    }

    //用户中心-提现记录
    public function getWithdrawLog($userid = '')
    {
        $userid = $userid ? IFilter::act($userid, 'int') : IWeb::$app->getController()->user['user_id'];
        if (!$userid) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        $limit  = IReq::get('limit') ? IFilter::act(IReq::get('limit'), 'int') : 10;
        $page   = IReq::get('page') ? IFilter::act(IReq::get('page'), 'int') : 1;
        $query  = new IQuery('withdraw');
        $query->where = "user_id = " . $userid . " and is_del = 0";
        $query->order = "id desc";
        $query->page  = $page;
        $query->pagesize = $limit;
        $resultData = $query->find();

        foreach ($resultData as $key => $val) {
            $resultData[$key]['status_text'] = AccountLog::getWithdrawStatus($val['status']);
        }

        return $query->setData($resultData);
    }

    // 用户中心-评论商品列表
    public function getUcenterCommonList($userid = '')
    {
        $userid = $userid ? IFilter::act($userid, 'int') : IWeb::$app->getController()->user['user_id'];
        if (!$userid) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        $status = IFilter::act(IReq::get('common_status'), 'int');
        $page   = IReq::get('page') ? IFilter::act(IReq::get('page'), 'int') : 1;
        $limit   = IReq::get('limit') ? IFilter::act(IReq::get('limit'), 'int') : 10;
        $where  = "c.user_id = {$userid}";
        $where .= ($status === '') ? "" : " and c.status = {$status}";

        $query = new IQuery('comment as c');
        $query->join   = "left join goods as go on go.id = c.goods_id";
        $query->fields = "c.*";
        $query->where  = $where;
        $query->page   = $page;
        $query->pagesize = $limit;
        $query->order  = 'c.id desc';
        $result = $query->find();

        foreach ($result as $key => $val) {
            $goodsRow = comment_class::goodsInfo($val['id']);
            $result[$key] = array_merge($val, $goodsRow);
        }
        return $query->setData($result);
    }

    // 获取评价商品信息
    public function getCommonDetail($userid = '')
    {
        $userid = $userid ? IFilter::act($userid, 'int') : IWeb::$app->getController()->user['user_id'];
        $id = IFilter::act(IReq::get('id'), 'int');

        if (!$id) die(JSON::encode(array('status' => 'fail', 'error' => '传递的参数不完整')));

        $comment = Comment_Class::can_comment($id, $userid);
        if (is_string($comment)) die(JSON::encode(array('status' => 'fail', 'error' => $comment)));

        $result['comment'] = $comment;
        $result['commentCount'] = Comment_Class::get_comment_info($comment['goods_id']);
        $result['goods'] = Comment_Class::goodsInfo($id);

        return $result;
    }

    // 获取当前订单的评价商品
    public function getOrderComment($userid = '')
    {
        $userid = $userid ? IFilter::act($userid, 'int') : IWeb::$app->getController()->user['user_id'];
        $order_id = IFilter::act(IReq::get('order_id'), 'int');

        if (!$order_id) die(JSON::encode(array('status' => 'fail', 'error' => '传递的参数不完整')));

        $orderRow = (new IModel('order'))->getObj('id = ' . $order_id);
        if (!$orderRow) die(JSON::encode(array('status' => 'fail', 'error' => '没有订单')));

        $where  = "user_id = {$userid} and order_no = '" . $orderRow['order_no'] . "'";

        $query = new IQuery('comment');
        $query->where  = $where;

        return $query;
    }

    // 评论图片上传
    public function uploadCommonImg()
    {
        $userid = IWeb::$app->getController()->user['user_id'];
        //商品评价申请图片上传
        $photoObj = new PhotoUpload(IWeb::$app->config['upload'] . "/comment/" . $userid);
        $photoObj->setIterance(false);
        return current($photoObj->run());
    }

    // 评论提交
    public function updateCommonGoods($userid = '')
    {
        $userid = $userid ? IFilter::act($userid, 'int') : IWeb::$app->getController()->user['user_id'];

        $id      = IFilter::act(IReq::get('id'), 'int');
        $content = IFilter::act(IReq::get("contents"));
        $point   = IFilter::act(IReq::get('point'), 'float');
        $img_list = IFilter::act(IReq::get("_imgList"));

        if (!$id || !$content || $point == 0) die(JSON::encode(array('status' => 'fail', 'error' => '填写完整的评论内容')));

        $data = array(
            'point'        => $point,
            'contents'     => $content,
            'status'       => 1,
            'img_list'     => '',
            'comment_time' => ITime::getNow("Y-m-d"),
        );

        if (isset($img_list) && $img_list) {
            $img_list   = trim($img_list, ',');
            $img_list   = explode(",", $img_list);
            if (count($img_list) > 5) die(JSON::encode(array('status' => 'fail', 'error' => '最多上传5张图片')));

            $img_list   = array_filter($img_list);
            $img_list   = JSON::encode($img_list);
            $data['img_list'] = $img_list;
        }

        $result = Comment_Class::can_comment($id, $userid);
        if (is_string($result)) die(JSON::encode(array('status' => 'fail', 'error' => $result)));

        $tb_comment = new IModel("comment");
        $re = $tb_comment->setData($data)->update("id={$id}");

        if (!$re) die(JSON::encode(array('status' => 'fail', 'error' => '评论失败')));

        return 'success';
    }

    // 用户中心-地址列表
    public function getUcenterAddressList($userid = '')
    {
        $userid = $userid ? IFilter::act($userid, 'int') : IWeb::$app->getController()->user['user_id'];
        if (!$userid) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));
        $query  = new IModel('address');
        $address = $query->query('user_id = ' . $userid, "*", "is_default desc");

        $areas   = array();
        if ($address) {
            foreach ($address as $ad) {
                $temp = area::name($ad['province'], $ad['city'], $ad['area']);
                if (isset($temp[$ad['province']]) && isset($temp[$ad['city']]) && isset($temp[$ad['area']])) {
                    $areas[$ad['province']] = $temp[$ad['province']];
                    $areas[$ad['city']]     = $temp[$ad['city']];
                    $areas[$ad['area']]     = $temp[$ad['area']];
                }
            }
        }

        return array('areas' => $areas, 'address' => $address);
    }

    // 用户中心-地址添加/编辑
    public function addressEdit($userid = '')
    {
        $userid = $userid ? IFilter::act($userid, 'int') : IWeb::$app->getController()->user['user_id'];
        if (!$userid) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        $id          = IFilter::act(IReq::get('id'), 'int');
        $accept_name = IFilter::act(IReq::get('accept_name'), 'name');
        $province    = IFilter::act(IReq::get('province'), 'int');
        $city        = IFilter::act(IReq::get('city'), 'int');
        $area        = IFilter::act(IReq::get('area'), 'int');
        $address     = IFilter::act(IReq::get('address'));
        $zip         = IFilter::act(IReq::get('zip'));
        $telphone    = IFilter::act(IReq::get('telphone'));
        $mobile      = IFilter::act(IReq::get('mobile'));
        $is_default  = IFilter::act(IReq::get('is_default'), 'int');

        // 整合的数据
        $sqlData = array(
            'user_id'     => $userid,
            'accept_name' => $accept_name,
            'zip'         => $zip,
            'telphone'    => $telphone,
            'province'    => $province,
            'city'        => $city,
            'area'        => $area,
            'address'     => $address,
            'mobile'      => $mobile,
            'is_default'  => $is_default ? $is_default : 0,
        );

        $checkArray = $sqlData;
        unset($checkArray['telphone'], $checkArray['zip'], $checkArray['is_default']);
        foreach ($checkArray as $val) {
            if (!$val) die(JSON::encode(array('status' => 'fail', 'error' => '请完整填写收件信息')));
        }

        $model = new IModel('address');
        $model->setData($sqlData);
        if ($id) {
            $model->update("id = " . $id . " and user_id = " . $userid);
        } else {
            $id = $model->add();
        }

        // 把其他的默认取消
        if ($is_default) {
            $model->setData(array('is_default' => 0))->update('id !=  ' . $id . ' and is_default = 1 and user_id = ' . $userid);
        }

        return $id;
    }

    // 用户中心-地址删除
    public function addressDel($userid = '')
    {
        $userid = $userid ? IFilter::act($userid, 'int') : IWeb::$app->getController()->user['user_id'];
        if (!$userid) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));
        $id = IFilter::act(IReq::get('id'), 'int');
        $model = new IModel('address');
        return $model->del('id = ' . $id . ' and user_id = ' . $userid);
    }

    // 用户中心-根据id获取地区列表
    public function getAreaChild()
    {
        $parent_id = intval(IReq::get("parent_id"));
        $areaDB    = new IModel('areas');
        $data      = $areaDB->query("parent_id=$parent_id", '*', 'sort asc');
        return $data;
    }

    // 用户中心-获取地址
    public function getAddressInfo($userid = '')
    {
        $userid = $userid ? IFilter::act($userid, 'int') : IWeb::$app->getController()->user['user_id'];
        $query  = new IQuery('address');
        $query->where = 'user_id = ' . $userid;
        $query->order = 'is_default desc';

        $addressList = $query->find();
        //更新$addressList数据
        foreach ($addressList as $key => $val) {
            $temp = area::name($val['province'], $val['city'], $val['area']);
            if (isset($temp[$val['province']]) && isset($temp[$val['city']]) && isset($temp[$val['area']])) {
                $addressList[$key]['province_str'] = $temp[$val['province']];
                $addressList[$key]['city_str']     = $temp[$val['city']];
                $addressList[$key]['area_str']     = $temp[$val['area']];
            }
        }
        return $addressList;
    }

    // 用户中心-获取订单列表
    public function getOrderListByState($userid = '')
    {
        $userid = $userid ? IFilter::act($userid, 'int') : IWeb::$app->getController()->user['user_id'];
        if (!$userid) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        $page   = IReq::get('page') ? IFilter::act(IReq::get('page'), 'int') : 1;
        $limit = IFilter::act(IReq::get('limit'), 'int');
        $limit = $limit ? $limit : 10;

        // 0全部 1待付款 2待收货 3待评价 4售后
        $state = IFilter::act(IReq::get('state'), 'int');

        $resultData = [];
        $orderGoodsDB = new IModel('order_goods');

        // `status` => '订单状态 1生成订单,2支付订单,3取消订单(客户触发),4作废订单(管理员触发),5完成订单,6退款(订单完成后),7部分退款(订单完成后)',
        // `pay_status` => '支付状态 0：未支付; 1：已支付;',
        // `distribution_status` => '配送状态 0：未发送,1：已发送,2：部分发送',
        $where  = 'o.if_del = 0 and o.user_id = ' . $userid;
        $fields = 'o.id, o.order_no, o.user_id, o.pay_type, o.distribution, o.distribution_status, o.status, o.pay_status, o.create_time, o.order_amount, o.goods_type';
        // 配置where
        $stateConf = [
            0 => ['where' => $where],
            1 => ['where' => $where . ' and o.status = 1 and o.pay_status = 0 and o.distribution_status = 0'],
            2 => ['where' => $where . ' and o.status = 2 and o.pay_status = 1'],
            3 => [
                'where' => $where . ' and o.status = 5 and o.pay_status = 1 and o.distribution_status = 1 and c.status = 0',
                'join' => 'right join comment as c on c.order_no = o.order_no',
                'fields' => 'c.status as r_status,',
                'status_text' => ['未评价', '已评价'],
            ],
            4 => [
                'where' => $where,
                'join' => 'right join refundment_doc as rc on rc.order_no = o.order_no',
                'fields' => 'rc.pay_status as r_status,',
                'status_text' => ['申请中', '已拒绝', '已完成', '等待买家发货', '等待商家确认'],
            ],
        ];

        if (!$stateConf[$state]) die(JSON::encode(array('status' => 'fail', 'error' => '状态不正确')));

        $query         = new IQuery('order as o');
        $query->join   = $stateConf[$state]['join'];
        $query->where  = $stateConf[$state]['where'];
        $query->fields = $stateConf[$state]['fields'] . $fields;
        $query->page   = $page;
        $query->order  = "o.id desc";
        $query->pagesize = $limit;
        $resultData = $query->find();

        foreach ($resultData as $key => $value) {
            $order_id = $value['id'];

            $resultData[$key]['goods'] = $orderGoodsDB->query('order_id = ' . $order_id);

            if ($stateConf[$state]['status_text']) $resultData[$key]['status_text'] = $stateConf[$state]['status_text'][$value['r_status']];
            else $resultData[$key]['status_text'] = Order_Class::orderStatusText(Order_Class::getOrderStatus($value));

            $resultData[$key]['isCancel']  = order_class::isCancel($value);
            $resultData[$key]['isGoPay']   = order_class::isGoPay($value);
            $resultData[$key]['isConfirm'] = order_class::isConfirm($value);
            $resultData[$key]['isRefund']  = order_class::isRefund($value);
        }

        return $query->setData($resultData);
    }

    // 个人中心-订单删除
    public function orderDel()
    {
        $user_id = IWeb::$app->getController()->user['user_id'];
        if (!isset($user_id) || !$user_id) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));
        $id = IFilter::act(IReq::get('id'), 'int');
        if (!$id) die(JSON::encode(array('status' => 'fail', 'error' => '非法请求')));

        $query = new IModel('order');
        $query->setData(['if_del' => 1]);
        return $query->update('id = ' . $id . ' and user_id = ' . $user_id);
    }

    // 个人中心-订单详情
    public function getOrderDetail($user_id = '')
    {
        $user_id = $user_id ? IFilter::act($user_id, 'int') : IWeb::$app->getController()->user['user_id'];
        if (!$user_id) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));
        $id = IFilter::act(IReq::get('id'), 'int');
        if (!$id) die(JSON::encode(array('status' => 'fail', 'error' => '非法请求')));

        $orderObj = new order_class();
        $orderInfo = $orderObj->getOrderShow($id, $user_id);

        if ($orderInfo) {
            $orderInfo['order_status'] = Order_Class::orderStatusText(Order_Class::getOrderStatus($orderInfo));

            IReq::set('order_id', $orderInfo['order_id']);
            $orderInfo['goods'] = $this->getOrderGoodsList();

            IReq::set('order_id', $id);
            $orderInfo['comment']   = $this->getOrderComment()->find();

            $orderInfo['isCancel'] = order_class::isCancel($orderInfo);
            $orderInfo['isGoPay'] = order_class::isGoPay($orderInfo);
            $orderInfo['isConfirm'] = order_class::isConfirm($orderInfo);
            $orderInfo['isRefund'] = order_class::isRefund($orderInfo);
        }

        return $orderInfo;
    }

    // 获取订单商品信息和发货单号
    public function getOrderGoodsList()
    {
        $order_id = IFilter::act(IReq::get('order_id'), 'int');
        if (!$order_id) die(JSON::encode(array('status' => 'fail', 'error' => '非法请求')));

        $query         = new IQuery('order_goods as og');
        $query->join   = 'left join delivery_doc as dc on og.delivery_id = dc.id';
        $query->where  = 'og.order_id =' . $order_id;
        $query->fields = 'og.*,dc.delivery_code';

        return $query->find();
    }

    // 个人中心--添加/删除收藏夹
    public function goodsFavoriteEdit()
    {
        $user_id = IWeb::$app->getController()->user['user_id'];
        if (!isset($user_id) || !$user_id) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        $goods_id = IFilter::act(IReq::get('goods_id'), 'int');
        if ($goods_id == 0) die(JSON::encode(array('status' => 'fail', 'error' => '商品id值不能为空')));

        $favoriteObj = new IModel('favorite');
        $goodsRow    = $favoriteObj->getObj('user_id = ' . $user_id . ' and goods_id = ' . $goods_id);

        // 已收藏的取消收藏
        if ($goodsRow) {
            if (is_array($goods_id)) {
                $idStr = join(',', $goods_id);
                $where = 'user_id = ' . $user_id . ' and goods_id in (' . $idStr . ')';
            } else {
                $where = 'user_id = ' . $user_id . ' and goods_id = ' . $goods_id;
            }
            $favoriteObj->del($where);
        } else {
            $catObj = new IModel('category_extend');
            $catRow = $catObj->getObj('goods_id = ' . $goods_id);
            $cat_id = $catRow ? $catRow['category_id'] : 0;

            $dataArray   = array(
                'user_id'  => $user_id,
                'goods_id' => $goods_id,
                'time'     => ITime::getDateTime(),
                'cat_id'   => $cat_id,
            );
            $favoriteObj->setData($dataArray);
            $favoriteObj->add();

            //商品收藏信息更新
            $goodsDB = new IModel('goods');
            $goodsDB->setData(array("favorite" => "favorite + 1"));
            $result = $goodsDB->update("id = " . $goods_id, 'favorite');
        }

        return $this->getGoodsFavoriteIds();
    }

    // 商品收藏商品id列表
    public function getGoodsFavoriteIds()
    {
        //获取收藏夹信息
        $userid = IWeb::$app->getController()->user['user_id'];
        if (!isset($userid) || !$userid) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));
        $cat_id = IFilter::act(IReq::get('cat_id'), 'int');

        $where = 'user_id = ' . $userid;
        $where .= $cat_id ? ' and cat_id = ' . $cat_id : "";
        $favorite = (new IModel('favorite'))->query($where, 'goods_id');

        $ids = [];
        foreach ($favorite as $val) {
            array_push($ids, $val['goods_id']);
        }

        return $ids;
    }

    // 商品收藏列表
    public function getFavoriteList()
    {
        $ids = $this->getGoodsFavoriteIds();
        return Ydui::getData('bothGoodsCount', array('goods_str' => join(',', $ids)));
    }

    // 用户上传头像
    public function uploadUserIco()
    {
        $userid = IWeb::$app->getController()->user['user_id'];
        if (!isset($userid) || !$userid) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        $uploadDir = IWeb::$app->config['upload'] . '/user_ico';
        $photoObj = new PhotoUpload($uploadDir);
        $photoObj->setIterance(false);
        $result   = current($photoObj->run());

        if ($result && isset($result['flag']) && $result['flag'] == 1) {
            $userDB = new IModel('user');
            $userDB->setData(['head_ico' => $result['img']]);
            $userDB->update('id = ' . $userid);
            $result['img'] = IUrl::creatUrl() . $result['img'];
        }
        return $result;
    }

    // 用户股权信息
    public function getStocksInfo()
    {
        $userid = IWeb::$app->getController()->user['user_id'];
        if (!isset($userid) || !$userid) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        $userRow = $this->getUserInfo($userid);

        $realRow = $this->getRealNameInfo($userid);
        if (!$realRow) die(JSON::encode(array('status' => 'fail', 'error' => '请先实名')));

        $result = [];
        if ($userRow && $userRow['is_vip']) {

            $time = $userRow['check_time'] ? $userRow['check_time'] : '2019-06-11 00:00:00';
            $curTime = strtotime($time);

            $userDB = new IModel('user');
            while (true) {
                $checkTime = ITime::getDateTime('', $curTime);
                if ($userDB->getObj('check_time = "' . $checkTime . '" and id !=' . $userid)) {
                    $curTime++;
                } else {
                    break;
                }
            }

            $dateTime = explode('-', ITime::getDateTime('Y-m-d'));

            $result['user_id']       = $userRow['user_id'];
            $result['username']      = $userRow['username'];
            $result['true_name']     = $realRow['name'];
            // $result['active_amount'] = $userRow['active_amount'];
            $result['active_amount'] = 0;
            $result['ID_card']       = $realRow['id_num'];
            $result['serial_no']     = '0' . ($curTime - 1383838438);
            $result['sec_stocks']    = $userRow['sec_stocks'];
            $result['time']          = $dateTime[0] . '年' . $dateTime[1] . '月' . $dateTime[2] . '日';
        }

        return $result;
    }

    // 获取我的推荐
    public function getMyTeam($userid = '')
    {
        $userid = $userid ? IFilter::act($userid, 'int') : IWeb::$app->getController()->user['user_id'];
        if (!$userid) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        $page  = IFilter::act(IReq::get('page'), 'int');
        $limit = IReq::get('limit') ? IFilter::act(IReq::get('limit'), 'int') : 10;

        $keyworld = IFilter::act(IReq::get('keyworld'));

        $query = new IQuery('user as u');
        $query->join = 'left join member as m on u.id = m.user_id';
        $query->where = 'u.parent_id = ' . $userid . ' and (u.username like "' . $keyworld . '%" or m.mobile like "' . $keyworld . '%" or m.true_name like "' . $keyworld . '%")';
        $query->page = $page ? $page : 1;
        $query->pagesize = $limit;

        $data = $query->find();

        foreach ($data as $key => $info) {
            $data[$key]['is_vip'] = Team::isVipByUserLevel($info['level']);
            $data[$key]['is_agent'] = boolval(Team::getAgentLevelByUserRow($info));
            $data[$key]['agent_text'] = Text::agentShow($info['agent_level']);
            $data[$key]['vip_text'] = Text::levelShow($info['level']);
        }

        return $query->setData($data);
    }

    // 修改密码
    public function changePassword($userid = '')
    {
        $userid = $userid ? IFilter::act($userid, 'int') : IWeb::$app->getController()->user['user_id'];
        if (!$userid) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        $oldPassword = IFilter::act(IReq::get('oldPassword'));
        $password = IFilter::act(IReq::get('password'));
        $rePassword = IFilter::act(IReq::get('rePassword'));

        $changeType = IFilter::act(IReq::get('changeType'));

        if (!in_array($changeType, ['tran_password', 'password'])) {
            die(JSON::encode(array('status' => 'fail', 'error' => '非法请求')));
        }

        $where = 'id = ' . $userid;
        $userDB = new IModel('user');
        $userRow = $userDB->getObj($where);

        if (!preg_match('|\w{6,32}|', $password)) {
            die(JSON::encode(array('status' => 'fail', 'error' => '密码格式不正确，请重新输入')));
        }

        if ($password != $rePassword) {
            die(JSON::encode(array('status' => 'fail', 'error' => '二次密码输入的不一致，请重新输入')));
        }

        if ($userRow[$changeType] && (md5($oldPassword) != $userRow[$changeType]) && ($changeType == 'tran_password' && $userRow['tran_password'])) {
            die(JSON::encode(array('status' => 'fail', 'error' => '原始密码输入错误')));
        }

        $passwordMd5 = md5($password);
        $dataArray = array(
            $changeType => $passwordMd5,
        );

        $userDB->setData($dataArray);
        $result  = $userDB->update($where);

        if ($result) return 'success';

        die(JSON::encode(array('status' => 'fail', 'error' => '密码修改失败')));
    }

    // 登录名验证
    public function validateUsername()
    {
        $userid = IWeb::$app->getController()->user['user_id'];
        if (!$userid) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        $username = IFilter::act(IReq::get('username'), 'string');
        if (!$username) {
            die(JSON::encode(array('status' => 'fail', 'error' => '信息不完善')));
        }

        $userRow = $this->getUserInfo($userid);
        if ($username == $userRow['username']) {
            die(JSON::encode(array('status' => 'fail', 'error' => '不能转给自己')));
        }

        $query = new IQuery('user as u');
        $query->join = 'left join member as m on u.id = m.user_id';
        $query->fields = 'm.user_id, u.username, u.head_ico, m.true_name, m.mobile';
        $query->where = 'u.username = "' . $username . '"';
        $res = $query->find();
        $recevieRow = $res[0];

        if (!$recevieRow) {
            die(JSON::encode(array('status' => 'fail', 'error' => '用户不存在')));
        }
        return $recevieRow;
    }

    // 转账给用户
    public function trans2user()
    {
        $user_id = IWeb::$app->getController()->user['user_id'];
        if (!$user_id) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        $curUsername = IFilter::act(IReq::get('curname'));
        $amount  = IFilter::act(IReq::get('amount'), 'int');
        $password  = IFilter::act(IReq::get('password'), 'string');

        $type  = IFilter::act(IReq::get('transType'), 'string'); // 转账类型 revisit 积分转账 balance 余额

        if (!$curUsername || !$amount || !$password) {
            die(JSON::encode(array('status' => 'fail', 'error' => '信息不完善')));
        }

        $userRow = $this->getUserInfo($user_id);
        if ($curUsername == $userRow['username']) {
            die(JSON::encode(array('status' => 'fail', 'error' => '不能转给自己')));
        }

        $isPass = Team::validateTranPasswd($user_id, $password);
        if (!$isPass) die(JSON::encode(array('status' => 'fail', 'error' => '提现密码不正确')));

        $userDB = new IModel('user');
        $userRow = $userDB->getObj('id = ' . $user_id);
        $curUserRow = $userDB->getObj('username = "' . $curUsername . '"');
        $cur_uid = $curUserRow['id'];

        if ($type == 'revisit') {
            $userRevisit = $userRow['revisit'];

            if ($amount > $userRevisit) die(JSON::encode(array('status' => 'fail', 'error' => '转账金额不能大于可用余额')));

            // 扣当前用户
            $newRevisit = $userRevisit - $amount;
            $res = $userDB->setData(array('revisit' => $newRevisit))->update('id = ' . $user_id);

            if (!$res) die(JSON::encode(array('status' => 'fail', 'error' => '转账失败')));

            $logDB = new IModel('revisit_log');
            $log = array(
                'user_id'   => $user_id,
                'type'      => '1',
                'time'      => ITime::getDateTime(),
                'value'     => $amount,
                'value_log' => $newRevisit,
                'event'     => '1',
                'note'      => '转账给 ' . $curUserRow['username'] . '，金额：' . $amount,
            );
            $logDB->setData($log)->add();

            $curRevisit = $curUserRow['revisit'] + $amount;
            $userDB->setData(array('revisit' => $curRevisit))->update('id = ' . $cur_uid);

            $cuLog = array(
                'user_id'   => $cur_uid,
                'type'      => '0',
                'time'      => ITime::getDateTime(),
                'value'     => $amount,
                'value_log' => $curRevisit,
                'event'     => '2',
                'note'      => '收到 ' . $userRow['username'] . '转账，金额：' . $amount,
                'from_uid'  => $user_id,
            );

            $logDB->setData($cuLog)->add();

            return 'success';
        }

        $userFreeBalance = Team::getUserFreeBalance($user_id);
        if ($amount > $userFreeBalance) die(JSON::encode(array('status' => 'fail', 'error' => '转账金额不能大于可用余额')));

        // 扣除当前用户余额
        $memberDB = new IModel('member');
        $memberRow = $memberDB->getObj('user_id = ' . $user_id);

        $newBalance = $memberRow['balance'] - $amount;
        $uRes = $memberDB->setData(array('balance' => $newBalance))->update('user_id =' . $user_id);

        if (!$uRes) die(JSON::encode(array('status' => 'fail', 'error' => '转账失败')));

        $accountLog = new IModel('account_log');
        $userLog = array(
            'user_id'    => $user_id,
            'type'       => '1',
            'event'      => '22',
            'time'       => ITime::getDateTime(),
            'amount'     => $amount,
            'amount_log' => $newBalance,
            'note'       => '转账给 ' . $curUserRow['username'] . '，金额：' . $amount,
        );
        $accountLog->setData($userLog);
        $accountLog->add();

        // 给转账用户增加余额
        $curRow = $memberDB->getObj('user_id = ' . $cur_uid);
        $curBalance = $curRow['balance'] + $amount;
        $memberDB->setData(array('balance' => $curBalance))->update('user_id = ' . $cur_uid);

        $curLog = array(
            'user_id'    => $cur_uid,
            'type'       => '0',
            'event'      => '23',
            'time'       => ITime::getDateTime(),
            'amount'     => $amount,
            'amount_log' => $curBalance,
            'from_uid'   => $user_id,
            'note'       => '收到 ' . $userRow['username'] . '转账，金额：' . $amount,
        );
        $accountLog->setData($curLog);
        $accountLog->add();

        return 'success';
    }

    // 用户提现
    public function withdraw()
    {
        $user_id = IWeb::$app->getController()->user['user_id'];
        if (!$user_id) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        if (!$this->getRealNameInfo()) die(JSON::encode(array('status' => 'fail', 'error' => '请先实名')));

        $amount  = IFilter::act(IReq::get('amount', 'post'), 'float');

        $bank = Team::getBankInfo($user_id);
        if (!$bank) die(JSON::encode(array('status' => 'fail', 'error' => '请先绑定银行卡')));

        // 提现密码验证
        $transPass = IFilter::act(IReq::get('password', 'post'), 'string');
        $isPass = Team::validateTranPasswd($user_id, $transPass);

        // 服务费 提现扣的百分比
        $serviceFree = Team::serviceChargeConf();

        $siteConfig = new Config('site_config');
        $mixAmount =  $siteConfig->low_withdraw ? $siteConfig->low_withdraw : 1;
        $memberObj = new IModel('member');
        $where     = 'user_id = ' . $user_id;
        $memberRow = $memberObj->getObj($where, 'balance');

        // 可用余额
        $free_balance = Team::getUserFreeBalance($user_id);

        //提现金额范围
        if (!$isPass) {
            $message = '提现密码不正确';
        } else if ($amount <= $mixAmount) {
            $message = '提现的金额必须大于' . $mixAmount . '元';
        } else if ($amount > $memberRow['balance'] || $amount > $free_balance) {
            $message = '提现的金额不能大于您的帐户余额';
        }

        if ($message != '') die(JSON::encode(array('status' => 'fail', 'error' => $message)));

        // 免额度扣除
        $free = Team::calcFinalBalance($user_id, $amount);
        // 服务费
        $service_free = round(($amount - $free) * $serviceFree) / 100;

        $dataArray = array(
            'name'         => $bank['name'],
            'note'         => IFilter::act(IReq::get('note', 'post'), 'string'),
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
        if ($id) {
            plugin::trigger('withdrawApplyFinish', $id);
            return 'success';
        }
        die(JSON::encode(array('status' => 'fail', 'error' => '提现失败')));
    }

    // 激活会员
    public function activateMember()
    {
        die(JSON::encode(array('status' => 'fail', 'error' => '请升级到新版本APP')));

        $user_id = IWeb::$app->getController()->user['user_id'];
        if (!$user_id) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        // 要激活vip的uid
        $active_id = IFilter::act(IReq::get('active_id'), 'int');
        // 如果存在代表是帮激活的
        $user_id = $active_id ? $active_id : $user_id;

        // 金额
        $amount  = IFilter::act(IReq::get('amount'), 'int');
        if (!$amount) die(JSON::encode(array('status' => 'fail', 'error' => 'amount 不能为空')));

        $password = IFilter::act(IReq::get('password'));
        $isPass = Team::validateTranPasswd($user_id, $password);
        if (!$isPass) die(JSON::encode(array('status' => 'fail', 'error' => '提现密码不正确')));

        // 根据用户level判断是否已经是vip会员
        $userRow = $this->getUserInfo($user_id);
        $isVip = Team::isVipByUserLevel($userRow['level']);
        if ($isVip) die(JSON::encode(array('status' => 'fail', 'error' => '已经是vip会员了')));

        // 创建vip订单
        $order_id = $this->createVipOrder($user_id);

        if (is_array($order_id)) die(JSON::encode(array('status' => 'fail', 'error' => $order_id['msg'])));

        $payStatus = $this->payVipOrder($order_id);

        if ($order_id && $payStatus) {
            // 更新激活用户状态
            Team::updateVipUserLeve($user_id, $amount);
            // 走Team方法
            Team::init($user_id);
            return 'success';
        }
        die(JSON::encode(array('status' => 'fail', 'error' => '请求错误')));
    }

    // 创建激活会员的订单
    public function createVipOrder($active_id)
    {
        $user_id = IWeb::$app->getController()->user['user_id'];
        if (!$user_id) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        $active_id    = $active_id ? IFilter::act($active_id, 'int') : IFilter::act(IReq::get('active_id'), 'int');
        $amount       = IFilter::act(IReq::get('amount'), 'int');
        $payment      = IFilter::act(IReq::get('payment'), 'int');
        $accept_name  = IFilter::act(IReq::get('accept_name'));
        $mobile       = IFilter::act(IReq::get('mobile'));
        $province     = IFilter::act(IReq::get('province'), 'int');
        $city         = IFilter::act(IReq::get('city'), 'int');
        $area         = IFilter::act(IReq::get('area'), 'int');
        $address      = IFilter::act(IReq::get('address'));
        $distribution = IFilter::act(IReq::get('distribution'), 'int');

        $mustData = [
            $active_id, $amount, $payment, $accept_name, $province,  $city, $area, $address, $distribution,
        ];
        if (in_array('', $mustData)) {
            die(JSON::encode(array('status' => 'fail', 'error' => 'API接口缺少必要参数')));
        }

        if (!Team::isVipConfig($amount)) die(JSON::encode(array('status' => 'fail', 'error' => '金额不正确')));

        $query = new IModel('vip_order');

        $data = array(
            'order_no'     => Team::createVipOrderNum(),
            'user_id'      => $user_id,
            'active_id'    => $active_id,
            'pay_type'     => $payment,
            'create_time'  => ITime::getDateTime(),
            'order_amount' => $amount,
            'accept_name'  => $accept_name,
            'mobile'       => $mobile,
            'province'     => $province,
            'city'         => $city,
            'area'         => $area,
            'address'      => $address,
            'distribution' => $distribution,
        );

        $query->setData($data);
        $order_id = $query->add();

        if ($order_id) return $order_id;

        die(JSON::encode(array('status' => 'fail', 'error' => '订单生成失败')));
    }

    // 会员激活订单余额支付
    public function payVipOrder($id)
    {
        $user_id = IWeb::$app->getController()->user['user_id'];
        if (!$user_id) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        $id = $id ? IFilter::act($id, 'int') : IFilter::act(IReq::get('order_id'));
        if (!$id) die(JSON::encode(array('status' => 'fail', 'error' => 'order_id 不存在')));

        $paymentDB  = new IModel('payment');
        $paymentRow = $paymentDB->getObj('class_name = "balance" ');
        if (!$paymentRow) die(JSON::encode(array('status' => 'fail', 'error' => '余额支付方式不存在')));

        $memberObj = new IModel('member');
        $memberRow = $memberObj->getObj('user_id = ' . $user_id);

        if (empty($memberRow)) die(JSON::encode(array('status' => 'fail', 'error' => '用户信息不存在')));

        $orderObj = new IModel('vip_order');
        $orderRow = $orderObj->getObj('id = ' . $id . ' and pay_status = 0 and status = 1');
        if (!$orderRow) die(JSON::encode(array('status' => 'fail', 'error' => '订单号【' . $orderRow['order_no'] . '】已经被处理过，请查看订单状态')));

        // 可用余额 减去正在提现中的余额
        $free_balance = Team::getUserFreeBalance($user_id);

        if ($free_balance < $orderRow['order_amount']) {
            $recharge = $orderRow['order_amount'] - $free_balance;
            die(JSON::encode(array('status' => 'fail', 'error' => '余额不足请充值 ￥' . $recharge)));
        }

        //扣除余额并且记录日志
        $logObj = new AccountLog();
        $config = array(
            'user_id'  => $user_id,
            'event'    => 'pay',
            'num'      => $orderRow['order_amount'],
            'order_no' => $orderRow['order_no'],
        );
        if (!$logObj->write($config)) {
            $orderObj->rollback();
            die(JSON::encode(array('status' => 'fail', $logObj->error ? $logObj->error : '用户余额更新失败')));
        }

        // 支付成功更新订单状态
        $orderObj->setData(array(
            'status'     => 2,
            'pay_status' => 1,
            'pay_time'   => ITime::getDateTime(),
        ));

        return $orderObj->update('id = ' . $id);
    }

    // 获取激活金额
    public function getBecomVipAmount()
    {
        $user_id = IWeb::$app->getController()->user['user_id'];
        if (!$user_id) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        $status = Team::vipStatusConfig();

        $result = [];
        foreach ($status as $key => $value) {
            $tmp['active_amount'] = $value['active_amount'];
            $tmp['level_show'] = Text::levelShow($value['level']);
            $tmp['agent_show'] = Text::agentShow($value['agent_level']);
            $result[] = $tmp;
        }

        return $result;
    }

    // 实名图片上传
    function uploadRealNamePhoto()
    {
        $user_id = IWeb::$app->getController()->user['user_id'];
        if (!$user_id) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        //调用文件上传类
        $uploadDir = IWeb::$app->config['upload'] . '/real_name\/' . date('Ymd');
        $photoObj = new PhotoUpload($uploadDir);
        $result   = current($photoObj->run());
        return $result;
    }

    // 实名信息上传
    function realNameSave()
    {
        $user_id = IWeb::$app->getController()->user['user_id'];
        if (!$user_id) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        $id  = IFilter::act(IReq::get('id'), 'int');
        $name  = IFilter::act(IReq::get('real_name'));
        $id_num  = IFilter::act(IReq::get('id_num'));
        $back_img  = IFilter::act(IReq::get('back_img'));
        $front_img  = IFilter::act(IReq::get('front_img'));

        if (IValidate::id($id_num) == false) {
            die(JSON::encode(array('status' => 'fail', 'error' => '身份证格式不正确')));
        }

        $realDB = new IModel('real_name');
        $realRow = $realDB->getObj('is_del = 0 and user_id = ' . $user_id);

        if ($realRow['status'] == 2) die(JSON::encode(array('status' => 'fail', 'error' => '已通过验证')));

        if ($realRow && $realRow['status'] == 0) {
            die(JSON::encode(array('status' => 'fail', 'error' => '正在审核中')));
        }

        $verifyArray = array(
            'user_id'   =>  $user_id,
            'time'      =>  ITime::getDateTime(),
            'name'      =>  $name,
            'id_num'    =>  $id_num,
            'front_img' =>  $front_img,
            'back_img'  =>  $back_img,
            'status'    =>  0,
        );
        $realDB->setData($verifyArray);
        if ($id) {
            $realDB->update('id = ' . $id);
        } else {
            $realDB->add();
        }

        return 'success';
    }

    // 获取实名信息
    public function getRealNameInfo($user_id = '')
    {
        $user_id = $user_id ? IFilter::act($user_id, 'int') : IWeb::$app->getController()->user['user_id'];

        return (new IModel('real_name'))->getObj('status = 2 and is_del = 0 and user_id = ' . $user_id);
    }

    // 操作订单状态
    public function updateOrderStatus($user_id = '')
    {
        $user_id = $user_id ? IFilter::act($user_id, 'int') : IWeb::$app->getController()->user['user_id'];
        $op    = IFilter::act(IReq::get('op'));
        $id    = IFilter::act(IReq::get('order_id'), 'int');

        if (!$op || !$id) die(JSON::encode(array('status' => 'fail', 'error' => '非法请求')));

        $model = new IModel('order');

        switch ($op) {
            case "cancel":
                $model->setData(array('status' => 3));
                if ($model->update("id = " . $id . " and distribution_status = 0 and status = 1 and user_id = " . $user_id)) {
                    order_class::resetOrderProp($id);
                }
                //订单状态是付款或者发货则需要走退款退货申请流程
                else {
                    $order_goods_id = [];
                    $goodsList = Api::run('getOrderGoodsListByGoodsid', array('#order_id#', $id));
                    foreach ($goodsList as $item) {
                        $order_goods_id[] = $item['id'];
                    }

                    IReq::set('order_goods_id', $order_goods_id);
                    IReq::set('order_id', $id);
                    IReq::set('content', '申请取消订单');
                    IReq::set('type', 'cancel');

                    $orderRow = $model->getObj('id = ' . $id);
                    if ($orderRow && $orderRow['pay_status'] == 1) return $this->updateRefunds();
                    die(JSON::encode(array('status' => 'fail', 'error' => '状态不正确')));
                }
                break;

            case "confirm":
                $model->setData(array('status' => 5, 'completion_time' => ITime::getDateTime()));
                if ($model->update("id = " . $id . " and status in (1,2) and distribution_status = 1 and user_id = " . $user_id)) {
                    $orderRow = $model->getObj('id = ' . $id);

                    //确认收货后进行支付
                    Order_Class::updateOrderStatus($orderRow['order_no']);

                    //增加用户评论商品机会
                    Order_Class::addGoodsCommentChange($id);

                    // 订单完成走奖励方法
                    (new Commission($id))->init();
                    break;
                } else {
                    die(JSON::encode(array('status' => 'fail', 'error' => '状态不正确')));
                }
            default:
                die(JSON::encode(array('status' => 'fail', 'error' => 'fail')));
        }

        return 'success';
    }

    // 售后申请页面
    public function updateRefunds($user_id = '')
    {
        $user_id = $user_id ? IFilter::act($user_id, 'int') : IWeb::$app->getController()->user['user_id'];

        $order_goods_id = IFilter::act(IReq::get('order_goods_id'));
        $order_id       = IFilter::act(IReq::get('order_id'), 'int');
        $content        = IFilter::act(IReq::get('content'), 'text');
        $img_list       = IFilter::act(IReq::get("_imgList"));
        $type           = IFilter::act(IReq::get("type"));

        if (!$content || !$order_goods_id || !$order_id) die(JSON::encode(array('status' => 'fail', 'error' => '请填写售后原因和商品')));

        if (is_string($order_goods_id)) $order_goods_id = explode(',', $order_goods_id);

        $orderDB      = new IModel('order');
        $orderRow     = $orderDB->getObj("id = " . $order_id . " and user_id = " . $user_id);

        if (!$orderRow) die(JSON::encode(array('status' => 'fail', 'error' => '订单不正确')));

        $refundResult = Order_Class::isRefundmentApply($orderRow, $order_goods_id, $type);
        //判断售后申请是否已经存在
        if ($refundResult !== true)  die(JSON::encode(array('status' => 'fail', 'error' => $refundResult)));

        //售后申请数据
        $updateData = array(
            'order_no'       => $orderRow['order_no'],
            'order_id'       => $order_id,
            'user_id'        => $user_id,
            'time'           => ITime::getDateTime(),
            'content'        => $content,
            'img_list'       => '',
            'seller_id'      => $orderRow['seller_id'],
            'order_goods_id' => join(",", $order_goods_id),
        );

        if (isset($img_list) && $img_list) {
            $img_list = explode(",", trim($img_list, ","));
            $img_list = array_filter($img_list);

            if (count($img_list) > 5) die(JSON::encode(array('status' => 'fail', 'error' => '最多上传5张图片')));

            $img_list = JSON::encode($img_list);
            $updateData['img_list'] = $img_list;
        }

        switch ($type) {
                //换货
            case "exchange": {
                    $exchangeDB = new IModel('exchange_doc');
                    $exchangeDB->setData($updateData);
                    $id = $exchangeDB->add();

                    plugin::trigger('exchangeApplyFinish', $id);
                }
                break;

                //维修
            case "fix": {
                    $fixDB = new IModel('fix_doc');
                    $fixDB->setData($updateData);
                    $id = $fixDB->add();

                    plugin::trigger('fixApplyFinish', $id);
                }
                break;

                //退款
            default: {
                    $refundsDB = new IModel('refundment_doc');
                    $refundsDB->setData($updateData);
                    $id = $refundsDB->add();

                    plugin::trigger('refundsApplyFinish', $id);
                }
        }
        return 'success';
    }

    // 售后图片上传
    function uploadRefundsImg()
    {
        $photoObj = new PhotoUpload(IWeb::$app->config['upload'] . "/refunds/" . $this->user['user_id']);
        $photoObj->setIterance(false);
        $result   = current($photoObj->run());
        return $result;
    }

    // 售后详情
    public function getRefundsDetail($user_id = '')
    {
        $user_id = $user_id ? IFilter::act($user_id, 'int') : IWeb::$app->getController()->user['user_id'];

        $id = IFilter::act(IReq::get('id'), 'int');
        $type = IFilter::act(IReq::get("type"));

        $refindsConf = ['refundment', 'exchange', 'fix'];
        if (!$id || !$type || !in_array($type, $refindsConf)) die(JSON::encode(array('status' => 'fail', 'error' => '参数不正确')));

        $sql = new IModel($type . '_doc');

        $result = $sql->getObj('id = ' . $id);

        if ($result) {
            $orderGoodsDB   = new IModel('order_goods');
            $result['goods'] =  $orderGoodsDB->query("id in (" . $result['order_goods_id'] . ")");
            $result['status_text'] = Order_Class::refundmentText($result['status']);

            $result['user_freight'] = $result['user_freight_id'] ? Api::run('getFreightCompanyById', array('freight_id' => $result['user_freight_id'])) : [];

            $result['seller_freight'] = $result['seller_freight_id'] ? Api::run('getFreightCompanyById', array('freight_id' => $result['seller_freight_id'])) : [];

            $result['way'] = $result['way'] ? Order_Class::refundWay($result['way']) : '';
        }

        return $result;
    }

    // 更新售后物流单
    public function updateRefundsFreight($user_id = '')
    {
        $user_id = $user_id ? IFilter::act($user_id, 'int') : IWeb::$app->getController()->user['user_id'];

        $id                 = IFilter::act(IReq::get('id'), 'int');
        $type               = IFilter::act(IReq::get("type"));
        $user_freight_id    = IFilter::act(IReq::get('freight_id'), 'int');
        $user_delivery_code = IFilter::act(IReq::get('delivery_code'));

        if (!$id || !$type || !$user_freight_id || !$user_delivery_code) {
            die(JSON::encode(array('status' => 'fail', 'error' => '参数不正确')));
        }

        $updateData = [
            "status" => 4,
            "user_freight_id" => $user_freight_id,
            "user_delivery_code" => $user_delivery_code,
            "user_send_time" => ITime::getDateTime(),
        ];

        $where = "id = " . $id . " and user_id = " . $user_id;

        switch ($type) {
                //换货
            case "exchange":
                $exchangeDB = new IModel('exchange_doc');
                $exchangeDB->setData($updateData);
                $id = $exchangeDB->setData($updateData)->update($where);

                plugin::trigger('exchangeDocUpdate', $id);

                break;

                //维修
            case "fix":
                $fixDB = new IModel('fix_doc');
                $id = $fixDB->setData($updateData)->update($where);

                plugin::trigger('fixDocUpdate', $id);

                break;

                //退款
            default:
                $refundsDB = new IModel('refundment_doc');
                $refundsDB->setData($updateData);
                $id = $refundsDB->setData($updateData)->update($where);

                plugin::trigger('refundDocUpdate', $id);
        }
        return 'success';
    }

    //物流轨迹查询,2种参数形式：(1)发货单ID; (2)物流公司编号+快递单号
    public function getFreightDetail()
    {
        $id   = IFilter::act(IReq::get('id'), 'int');
        $code = IFilter::act(IReq::get('code'));

        if (!$id) die(JSON::encode(array('status' => 'fail', 'error' => '发货单信息不存在')));

        if ($code) {
            $db  = new IModel('freight_company');
            $row = $db->getObj($id);
            $freightData = [["freight_type" => $row['freight_type'], "delivery_code" => $code]];
        } else {
            $tb_freight = new IQuery('delivery_doc as d');
            $tb_freight->join  = 'left join freight_company as f on f.id = d.freight_id';
            $tb_freight->where = 'd.id = ' . $id;
            $tb_freight->fields = 'd.*,f.freight_type';
            $freightData = $tb_freight->find();
        }

        $freightData = current($freightData);
        if ($freightData && $freightData['freight_type'] && $freightData['delivery_code']) {

            $result = freight_facade::line($freightData['freight_type'], $freightData['delivery_code'], 'kdniao');

            if ($result['result'] == 'success') return $result;

            $reason = isset($result['reason']) ? $result['reason'] : '物流接口发生错误';
        }

        die(JSON::encode(array('status' => 'fail', 'error' => $reason ? $reason : '缺少物流信息')));
    }

    // 激活会员v2接口
    public function activateMemberV2()
    {
        // 要激活vip的uid
        $active_id = IFilter::act(IReq::get('active_id'), 'int');
        // 如果存在代表是帮激活的
        $active_id = $active_id ? $active_id : $this->uid;

        // 金额
        $amount  = IFilter::act(IReq::get('amount'), 'int');
        if (!$amount) die(JSON::encode(array('status' => 'fail', 'error' => 'amount 不能为空')));

        $password = IFilter::act(IReq::get('password'));
        $isPass = Team::validateTranPasswd($this->uid, $password);
        if (!$isPass) die(JSON::encode(array('status' => 'fail', 'error' => '提现密码不正确')));

        // 根据用户level判断是否已经是vip会员
        $userRow = $this->getUserInfo($active_id);
        $isVip = Team::isVipByUserLevel($userRow['level']);
        if ($isVip) die(JSON::encode(array('status' => 'fail', 'error' => '已经是vip会员了')));

        // 创建vip订单
        $order_id = $this->createVipOrder($active_id);

        if (is_array($order_id)) die(JSON::encode(array('status' => 'fail', 'error' => $order_id['msg'])));

        $this->payVipOrderV2($order_id);

        return 'success';
    }

    // 会员激活订单支付v2
    public function payVipOrderV2($id)
    {
        $id = $id ? IFilter::act($id, 'int') : IFilter::act(IReq::get('order_id'));
        if (!$id) die(JSON::encode(array('status' => 'fail', 'error' => 'order_id 不存在')));

        $orderObj = new IModel('vip_order');
        $orderRow = $orderObj->getObj('id = ' . $id . ' and pay_status = 0 and status = 1 and user_id = ' . $this->uid);
        if (!$orderRow) die(JSON::encode(array('status' => 'fail', 'error' => '订单号【' . $orderRow['order_no'] . '】已经被处理过，请查看订单状态')));

        // 生成普通订单进行支付
        $curRes = $this->createVipMemberOrder($orderRow);
        if (is_string($curRes)) die(JSON::encode(array('status' => 'fail', 'error' => $curRes)));

        IReq::set('order_id', $curRes['order_id']);
        IReq::set('payment_id', $curRes['payment_id']);
        IReq::set('origin', 'Ydui');
        return Api::run('doPay');
    }

    // 创建开通会员订单
    public function createVipMemberOrder($orderRow)
    {
        $gid  = IFilter::act(IReq::get('goods_id'), 'int');
        $num  = IFilter::act(IReq::get('buy_num'), 'int');
        $type = IFilter::act(IReq::get('goods_type')); //商品或者货品 goods / products

        $goodsResult = Ydui::getData('getCountum', array('buyInfo' => array('id' => $gid, 'type' => $type, 'buy_num' => $num)));
        if (is_string($goodsResult) || !$goodsResult['goodsList']) return '商品数据不存在';
        if ($goodsResult['error']) return $goodsResult['error'];

        //生成的订单数据
        $dataArray = array(
            'order_no'            => Order_Class::createOrderNum(),
            'user_id'             => $orderRow['active_id'],
            'accept_name'         => $orderRow['accept_name'],
            'pay_type'            => $orderRow['pay_type'],
            'distribution'        => $orderRow['distribution'] == 2 ? 1 : 2,
            'postcode'            => "",
            'telphone'            => "",
            'province'            => $orderRow['province'],
            'city'                => $orderRow['city'],
            'area'                => $orderRow['area'],
            'address'             => $orderRow['address'],
            'mobile'              => $orderRow['mobile'],
            'create_time'         => ITime::getDateTime(),
            'postscript'          => $orderRow['postscript'],
            'accept_time'         => "",

            'pay_status'          => $orderRow['pay_status'],

            //商品价格
            'payable_amount'      => $orderRow['order_amount'],
            'real_amount'         => $orderRow['order_amount'],

            //运费价格
            'payable_freight'     => 0,
            'real_freight'        => 0,

            //订单应付总额    订单总额 + 运费金额
            'order_amount'        => $orderRow['order_amount'],

            //商家ID
            'seller_id'           => 0, // Ydui 下单的默认0

            //商品类型
            'goods_type'          => 'Ydui', // Ydui商品

            // 自提点 设置了快递方式为2的为自提点
            'takeself'            => $orderRow['distribution'] == 2 ? '1' : '',

            'vip_order_id'        => $orderRow['id'],
        );

        //生成订单插入order表中
        $orderObj  = new IModel('order');
        $order_id = $orderObj->setData($dataArray)->add();

        /*将订单中的商品插入到order_goods表*/
        $orderInstance = new Order_Class();
        $orderGoodsResult = $orderInstance->insertOrderGoods($order_id, $goodsResult);
        if ($orderGoodsResult !== true) return $orderGoodsResult;

        return [
            'order_id'   => $order_id,
            'order_no'   => $dataArray['order_no'],
            'payment_id' => $dataArray['pay_type'],
        ];
    }
}
