<?php

/**
 * service商品相关接口
 */
class ServiceGoods
{
    // 计算ydui购物车数据
    public function getYduiCartFormat($cartValue, $result)
    {
        $goodsIdArray = array();

        if (isset($cartValue['goods']) && $cartValue['goods']) {
            $goodsIdArray = array_keys($cartValue['goods']);
            $result['goods']['id'] = $goodsIdArray;

            foreach ($goodsIdArray as $gid) {
                $result['goods']['data'][$gid] = array(
                    'id'       => $gid,
                    'type'     => 'goods',
                    'goods_id' => $gid,
                    'count'    => $cartValue['goods'][$gid],
                );

                //购物车中的种类数量累加
                $result['count'] += $cartValue['goods'][$gid];
            }
        }

        if (isset($cartValue['product']) && $cartValue['product']) {
            $productIdArray          = array_keys($cartValue['product']);
            $result['product']['id'] = $productIdArray;

            $productData = Ydui::getData('bothProductCount', array('productIdStr' => join(",", $productIdArray)));

            foreach ($productData as $proVal) {
                $result['product']['data'][$proVal['id']] = array(
                    'id'         => $proVal['id'],
                    'type'       => 'product',
                    'goods_id'   => $proVal['goods_id'],
                    'count'      => $cartValue['product'][$proVal['id']],
                    'sell_price' => $proVal['sell_price'],
                );

                if (!in_array($proVal['goods_id'], $goodsIdArray)) {
                    $goodsIdArray[] = $proVal['goods_id'];
                }

                //购物车中的种类数量累加
                $result['count'] += $cartValue['product'][$proVal['id']];
            }
        }

        if ($goodsIdArray) {
            $goodsArray = array();
            $goodsData = Ydui::getData('bothGoodsCount', array('goods_str' => join(",", $goodsIdArray)));
            foreach ($goodsData as $goodsVal) {
                $goodsArray[$goodsVal['id']] = $goodsVal;
            }

            foreach ($result['goods']['data'] as $key => $val) {
                if (isset($goodsArray[$val['goods_id']])) {
                    $result['goods']['data'][$key]['img']        = $goodsArray[$val['goods_id']]['img'];
                    $result['goods']['data'][$key]['name']       = $goodsArray[$val['goods_id']]['name'];
                    $result['goods']['data'][$key]['sell_price'] = $goodsArray[$val['goods_id']]['sell_price'];

                    //购物车中的金额累加
                    $result['sum']   += $result['goods']['data'][$key]['sell_price'] * $val['count'];
                }
            }

            foreach ($result['product']['data'] as $key => $val) {
                if (isset($goodsArray[$val['goods_id']])) {
                    $result['product']['data'][$key]['img']  = $goodsArray[$val['goods_id']]['img'];
                    $result['product']['data'][$key]['name'] = $goodsArray[$val['goods_id']]['name'];

                    //购物车中的金额累加
                    $result['sum']   += $result['product']['data'][$key]['sell_price'] * $val['count'];
                }
            }
        }

        return $result;
    }

    /**
     * 计算ydui商品价格
     *
     * @param array $buyInfo  购买信息
     * @param string $noSlice 是否区分可自提和不可自提 默认区分
     * @return void
     */
    public function getYduiGoodsCount($buyInfo, $noSlice = '')
    {
        $result = array(
            'final_sum'   => 0,       // 金额
            'weight'      => 0,       // 重量
            'count'       => 0,       // 数量
            'error'       => '',      // 错误信息
            'goodsList'   => array(), // 商品列表
        );

        $goodsCount = 0;
        $goodsList = array();
        $productList = array();

        //过滤空数组
        foreach ($buyInfo as $key => $val) {
            if (isset($val['id']) && !$val['id']) {
                unset($buyInfo[$key]);
                continue;
            }

            if (isset($val['id']) && is_array($val['id']) && $val['id']) {
                $goodsCount += count($val['id']);
            }
        }

        // goods
        if (isset($buyInfo['goods']['id']) && $buyInfo['goods']['id']) {
            //购物车中的商品数据
            $goodsIdStr = join(',', $buyInfo['goods']['id']);
            $goodsList = Ydui::getData('bothGoodsCount', array('goods_str' => $goodsIdStr));

            //开始计算
            foreach ($goodsList as $key => $val) {
                //检查库存
                if ($buyInfo['goods']['data'][$val['goods_id']]['count'] <= 0 || $buyInfo['goods']['data'][$val['goods_id']]['count'] > $val['store_nums']) {
                    $goodsList[$key]['name'] .= "【无库存】";
                    $goodsList[$key]['error'] = "<商品：" . $val['name'] . "> 购买数量超出库存，请重新调整购买数量。";
                }

                $goodsList[$key]['count']     = $buyInfo['goods']['data'][$val['goods_id']]['count'];
                $current_sum_all              = $goodsList[$key]['sell_price'] * $goodsList[$key]['count'];
                $goodsList[$key]['final_sum'] = round($current_sum_all, 2);

                //全局统计
                $result['weight']    += $val['weight'] * $goodsList[$key]['count'];
                $result['final_sum'] += $current_sum_all;
                $result['count']     += $goodsList[$key]['count'];
            }
        }

        // product
        if (isset($buyInfo['product']['id']) && $buyInfo['product']['id']) {
            //购物车中的货品数据
            $productIdStr = join(',', $buyInfo['product']['id']);
            $productList  = Ydui::getData('bothProductCount', array('productIdStr' => $productIdStr));

            //开始计算
            foreach ($productList as $key => $val) {
                //检查库存
                if ($buyInfo['product']['data'][$val['product_id']]['count'] <= 0 || $buyInfo['product']['data'][$val['product_id']]['count'] > $val['store_nums']) {
                    $productList[$key]['name'] .= "【无库存】";
                    $productList[$key]['error'] = "<货品：" . $val['name'] . "> 购买数量超出库存，请重新调整购买数量。";
                }

                $productList[$key]['count']     = $buyInfo['product']['data'][$val['product_id']]['count'];
                $current_sum_all                = $productList[$key]['sell_price']  * $productList[$key]['count'];
                $productList[$key]['final_sum'] = round($current_sum_all, 2);

                //全局统计
                $result['weight']    += $val['weight'] * $productList[$key]['count'];
                $result['final_sum'] += $current_sum_all;
                $result['count']     += $productList[$key]['count'];
            }
        }

        $result['final_sum'] = round($result['final_sum'], 2);
        $result['final_sum'] = $result['final_sum'] <= 0 ? 0 : $result['final_sum'];

        $resultList = array_merge($goodsList, $productList);
        if (!$resultList) {
            $result['error'] = "当前没有选购商品，请重新选择商品下单";
        }

        // 不区分自提和非自提
        if ($noSlice) {
            $result['goodsList'] = $resultList;
            return $result;
        }

        // 自提和非自提分类
        $pickList = array('no_pick' => [], 'default' => []);
        foreach ($resultList as $key => $value) {
            if ($value['is_pick'] == 1) {
                $pickList['no_pick'][] = $value;
            } else {
                $pickList['default'][] = $value;
            }
        }

        $result['goodsList'] = $pickList;

        return $result;
    }

    // 加入购物车
    public function joinCart()
    {
        $user_id = IWeb::$app->getController()->user['user_id'];
        if (!$user_id) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        $goods_id  = IFilter::act(IReq::get('goods_id'), 'int');
        $goods_num = IFilter::act(IReq::get('goods_num'), 'int');
        $goods_num = $goods_num == 0 ? 1 : $goods_num;
        $type      = IFilter::act(IReq::get('type'));

        if (!$goods_id || !$type) die(JSON::encode(array('status' => 'fail', 'error' => '商品信息不能为空')));

        // 写购物车表
        $cart = new Cart('Ydui');
        $addResult = $cart->add($goods_id, $goods_num, $type);

        if ($addResult === false) {
            $result = array(
                'status' => 'fail',
                'error' => $cart->getError(),
            );
        } else {
            $result = array(
                'status' => 'success',
                'error' => '添加成功',
            );
        }
        die(JSON::encode($result));
    }

    // 获取购物车数据
    public function getCartList()
    {
        $user_id = IWeb::$app->getController()->user['user_id'];
        if (!$user_id) die(JSON::encode(array('status' => 'token30401', 'error' => '请先登录')));

        //开始计算购物车中的商品价格
        $cartObj  = new Cart('Ydui');

        $result = Api::run('getYduiGoodsCount', $cartObj->getMyCart(true), true);

        if (is_string($result)) die(JSON::encode(array('status' => 'fail', 'error' => $result)));

        $countSumResult = Api::run('getYduiGoodsCount', $cartObj->getMyCart(false), true);

        $goodsArray = [];
        if (!empty($result['goodsList'])) {
            // 用id做key方便查找
            foreach ($result['goodsList'] as $v) {
                $v['isCheck'] = false;
                $goodsArray[$v['id']] = $v;
            }

            foreach ($countSumResult['goodsList'] as $value) {
                $goodsArray[$value['id']]['isCheck'] = true;
            }
        }

        $countSumResult['goodsList'] = array_merge($goodsArray);

        return $countSumResult;
    }

    // 未选中的商品提交到数据库
    public function exceptCartGoods()
    {
        $data    = IFilter::act(IReq::get('data'));
        $data    = $data ? join(",", $data) : "";
        $cartObj = new Cart('Ydui');
        $cartObj->setUnselected($data);

        return Api::run('getCartList');
    }

    // 删除购物车
    public function removeCart()
    {
        $goods_id  = IFilter::act(IReq::get('goods_id'), 'int');
        $type      = IFilter::act(IReq::get('type'));

        $cartObj   = new Cart('Ydui');
        $cartInfo  = $cartObj->getMyCart();
        $delResult = $cartObj->del($goods_id, $type);

        if ($delResult === false) {
            die(JSON::encode(array('status' => 'fail', 'error' => $cartObj->getError())));
        }

        $goodsRow = $cartInfo[$type]['data'][$goods_id];
        $cartInfo['sum']   -= $goodsRow['sell_price'] * $goodsRow['count'];
        $cartInfo['count'] -= $goodsRow['count'];

        return $cartInfo;
    }

    // 清空购物车
    public function clearCart()
    {
        $cartObj = new Cart('Ydui');
        return $cartObj->clear();
    }

    // 商品购买--订单结算
    public function shopping()
    {
        $id        = IFilter::act(IReq::get('id'), 'int');
        $type      = IFilter::act(IReq::get('type')); //goods,product
        $buy_num   = IReq::get('goods_num') ? IFilter::act(IReq::get('goods_num'), 'int') : 1;
        $origin    = IFilter::act(IReq::get('origin')); // Ydui商品
        $active_id = IFilter::act(IReq::get('active_id')); // 要开通会员的uid
        $gift_id    = IFilter::act(IReq::get('gift_id')); // 赠品id

        $user_id   = IWeb::$app->getController()->user['user_id'];

        if ($origin !== 'Ydui') die(JSON::encode(array('status' => 'fail', 'error' => '非法请求')));

        //游客的user_id默认为0
        $user_id = ($user_id == null) ? 0 : $user_id;

        // 必须是vip用户
        $userRow = Team::getVipInfoByUserId($user_id);
        if ($active_id) {
            // 判断是否已经是vip会员
            $isVip = Team::getVipInfoByUserId($active_id);
            if (Team::isVipOrder($active_id) || $isVip) die(JSON::encode(array('status' => 'fail', 'error' => '已经是vip会员了')));
            // vip订单必须有赠品id
            if (!$gift_id) die(JSON::encode(array('status' => 'fail', 'error' => '请升级到新版本APP')));
        }

        if (!$userRow && !$active_id) die(JSON::encode(array('status' => 'fail', 'error' => '请先成为vip会员再进行购买')));

        //计算商品 通过Ydui
        if ($active_id && $gift_id) { // vip订单并且有赠品
            // 暂时只针对没有规格的商品
            $cart = new Cart('Ydui');
            $cartValue = ['goods' => [$id => 1, $gift_id => 1], 'product' => []];
            $buyInfo = $cart->cartFormat($cartValue);
            $result = Api::run('getYduiGoodsCount', $buyInfo, true);
        } else if (!$id) {
            // 购物车结算
            $cartObj = new Cart('Ydui');
            $buyInfo = $cartObj->getMyCart(false);
            $result = Api::run('getYduiGoodsCount', $buyInfo, true);
        } else {
            // 购买单品结算
            $result = Ydui::getData('getCountum', array('buyInfo' => array('id' => $id, 'type' => $type, 'buy_num' => $buy_num)));
        }

        if ($result['error']) die(JSON::encode(array('status' => 'fail', 'error' => $result['error'])));

        // 默认地址
        $addressObj  = new IModel('address');
        $defaultAddress = $addressObj->getObj('user_id = ' . $user_id . ' and is_default = 1');

        // 更新默认地址数据
        if ($defaultAddress) {
            $temp = area::name($defaultAddress['province'], $defaultAddress['city'], $defaultAddress['area']);
            if ($temp) {
                $defaultAddress['province_str'] = $temp[$defaultAddress['province']];
                $defaultAddress['city_str']     = $temp[$defaultAddress['city']];
                $defaultAddress['area_str']     = $temp[$defaultAddress['area']];
            }
        }

        // 配送方式
        $deliveryList = Api::run('getDeliveryList');

        // 可使用金额 -- 积分
        $revisit = $userRow['revisit'];

        $result['id'] = $id;
        $result['type'] = $type;
        $result['buy_num'] = $buy_num;
        $result['revisit'] = $revisit;
        $result['deliveryList'] = $deliveryList;
        $result['defaultAddress'] = $defaultAddress;

        if ($active_id) {
            $result['active_id'] = $active_id;
            $result['gift_id']   = $gift_id;
        }

        return $result;
    }

    // 商品购买--获得配送方式金额
    public function getOrderDelivery()
    {
        $productId    = IFilter::act(IReq::get("productId"));
        $goodsId      = IFilter::act(IReq::get("goodsId"));
        $province     = IFilter::act(IReq::get("province"));
        $distribution = IFilter::act(IReq::get("distribution"), 'int');
        $num          = IReq::get("num") ? IFilter::act(IReq::get("num")) : 1;

        $productId = is_array($productId) ? $productId : explode(',', $productId);
        $goodsId   = is_array($goodsId)   ? $goodsId   : explode(',', $goodsId);
        $num       = is_array($num)       ? $num       : explode(',', $num);

        // 远程的现在只处理 Ydui商品 用于商品运费计算
        $origin = IFilter::act(IReq::get("origin"), 'string');
        $origin = $origin == 'Ydui' ? 'Ydui' : '';

        $data         = array();
        if ($distribution) {
            $data = Delivery::getDelivery($province, $distribution, $goodsId, $productId, $num, $origin);
        } else {
            $delivery     = new IModel('delivery');
            $deliveryList = $delivery->query('is_delete = 0 and status = 1');
            foreach ($deliveryList as $item) {
                $data[$item['id']] = Delivery::getDelivery($province, $item['id'], $goodsId, $productId, $num, $origin);
            }
        }
        return $data;
    }

    // 商品购买--订单提交支付
    public function confirmOrder()
    {
        if (IReq::get('timeKey')) {
            if (ISafe::get('timeKey') == IReq::get('timeKey')) {
                die(JSON::encode(array('status' => 'fail', 'error' => '订单数据不能被重复提交')));
            }
            ISafe::set('timeKey', IReq::get('timeKey'));
        }
        $user_id   = IWeb::$app->getController()->user['user_id'];

        $address_id    = IFilter::act(IReq::get('radio_address'), 'int');
        $delivery_id   = IFilter::act(IReq::get('delivery_id'), 'int');
        $accept_time   = IFilter::act(IReq::get('accept_time'));
        $payment       = IFilter::act(IReq::get('payment'), 'int');
        $accept_name   = IFilter::act(IReq::get('accept_name'));
        $order_message = IFilter::act(IReq::get('message'));
        $gid           = IFilter::act(IReq::get('direct_gid'), 'int');
        $num           = IFilter::act(IReq::get('direct_num'), 'int');
        $type          = IFilter::act(IReq::get('direct_type')); //商品或者货品 goods / products
        $dataArray     = [];
        $user_id       = ($user_id == null) ? 0 : $user_id;

        $origin        = IFilter::act(IReq::get('origin'));
        $revisit       = IFilter::act(IReq::get('revisit'), 'float');

        $active_id     = IFilter::act(IReq::get('active_id'), 'int'); // 如果存在 要开通会员的uid
        $gift_id       = IFilter::act(IReq::get('gift_id'), 'int'); // 赠品id
        $package_id    = IFilter::act(IReq::get('id'), 'int'); // 套餐商品id
        if ($active_id) {
            // 判断是否已经是vip会员
            $isVip = Team::getVipInfoByUserId($active_id);
            if (Team::isVipOrder($active_id) || $isVip) die(JSON::encode(array('status' => 'fail', 'error' => '已经是vip会员了')));
        }

        //计算商品 通过Ydui
        if ($active_id && $gift_id) { // vip订单并且有赠品
            // 暂时只针对没有规格的商品
            $cart = new Cart('Ydui');
            $cartValue = ['goods' => [$package_id => 1, $gift_id => 1], 'product' => []];
            $buyInfo = $cart->cartFormat($cartValue);
            $goodsResult = Api::run('getYduiGoodsCount', $buyInfo, true);
        } else if (!$gid) {
            // 购物车结算
            $cartObj = new Cart('Ydui');
            $buyInfo = $cartObj->getMyCart(false);
            $goodsResult = Api::run('getYduiGoodsCount', $buyInfo, true);
        } else {
            // 购买单品结算
            $goodsResult = Ydui::getData('getCountum', array('buyInfo' => array('id' => $gid, 'type' => $type, 'buy_num' => $num)));
            if ($goodsResult['goodsList'] && $goodsResult['goodsList'][0]) $productID = [$goodsResult['goodsList'][0]['product_id']];
        }

        if ($goodsResult['error']) die(JSON::encode(array('status' => 'fail', 'error' => $goodsResult['error'])));

        //1,访客; 2,注册用户
        if ($user_id == 0) {
            $addressRow = ISafe::get('address');
        } else {
            $addressDB   = new IModel('address');
            $addressRow  = $addressDB->getObj('id = ' . $address_id . ' and user_id = ' . $user_id);
        }

        //配送方式
        $deliveryObj = new IModel('delivery');
        $deliveryRow = $deliveryObj->getObj($delivery_id);

        if (!$deliveryRow) die(JSON::encode(array('status' => 'fail', 'error' => '配送方式不存在')));

        //1,在线支付
        if ($deliveryRow['type'] == 0 && $payment == 0) die(JSON::encode(array('status' => 'fail', 'error' => '请选择正确的支付方式')));

        //2,货到付款
        else if ($deliveryRow['type'] == 1) {
            $payment = 0;
        }

        if (!$addressRow) die(JSON::encode(array('status' => 'fail', 'error' => '收货地址信息不存在')));

        // 优惠金额判断是否够
        $userDB = new IModel('user');
        $userRow = $userDB->getObj('id = ' . $user_id);
        if (!$userRow) die(JSON::encode(array('status' => 'token30401', 'error' => '必须是登录用户才能下单')));

        // 地址信息 从库中读取的
        $accept_name   = IFilter::act($addressRow['accept_name'], 'name');
        $province      = $addressRow['province'];
        $city          = $addressRow['city'];
        $area          = $addressRow['area'];
        $address       = IFilter::act($addressRow['address']);
        $mobile        = IFilter::act($addressRow['mobile'], 'mobile');
        $telphone      = isset($addressRow['telphone']) ? IFilter::act($addressRow['telphone'], 'phone') : "";
        $zip           = isset($addressRow['zip']) ? IFilter::act($addressRow['zip'], 'zip') : "";

        // 购物车结算
        if (!$gid) {
            $cartObj = new Cart('Ydui');
            $buyInfo = $cartObj->getMyCart(false);

            // vip 订单商品运费计算
            if ($active_id && $gift_id) {
                $cart = new Cart('Ydui');
                $cartValue = ['goods' => [$package_id => 1, $gift_id => 1], 'product' => []];
                $buyInfo = $cart->cartFormat($cartValue);
            }

            $goodsId = [];
            $productId = [];
            $num = [];
            if ($buyInfo['goods'] && $buyInfo['goods']['data']) {
                foreach ($buyInfo['goods']['data'] as $key => $goods) {
                    $goodsId[] = $goods['goods_id'];
                    $productId[] = 0;
                    $num[] = $goods['count'];
                }
            }

            if ($buyInfo['product'] && $buyInfo['product']['data']) {
                foreach ($buyInfo['product']['data'] as $key => $products) {
                    $goodsId[] = $products['goods_id'];
                    $productId[] = $products['id'];
                    $num[] = $products['count'];
                }
            }

            $data = Delivery::getDelivery($province, $deliveryRow['id'], $goodsId, $productId, $num, $origin);
        } else {
            // 单品直接购买
            $data = Delivery::getDelivery($province, $deliveryRow['id'], $gid, $productID, $num, $origin);
        }

        //检查订单重复
        $checkData = array(
            "mobile" => $mobile,
        );
        $result = order_class::checkRepeat($checkData, $goodsResult['goodsList']);
        if (is_string($result)) die(JSON::encode(array('status' => 'fail', 'error' => $result)));

        if (!$gid && !($active_id && $gift_id)) {
            //清空购物车
            $cartObj = new Cart('Ydui');
            $cartObj->clear();
        }

        //判断商品是否存在
        if (is_string($goodsResult) || !$goodsResult['goodsList']) die(JSON::encode(array('status' => 'fail', 'error' => '商品数据不存在')));

        $paymentObj = new IModel('payment');
        $paymentRow = $paymentObj->getObj('id = ' . $payment, 'type,name');

        if (!$paymentRow) die(JSON::encode(array('status' => 'fail', 'error' => '支付方式不存在')));

        // 初始化最终金额为实际金额
        $realGoodsAmount = $goodsResult['final_sum'];
        $realDeliveryAmount = $data['price'];

        // 存在使用了优惠 开通会员不能使用优惠
        if ($revisit > 0 && !$active_id) {
            // 不能超过实际可用金额
            if ($userRow['revisit'] < $revisit) die(JSON::encode(array('status' => 'fail', 'error' => '可用优惠金额不足')));

            // 总金额 商品金额 + 运费金额
            $allAmount = round($goodsResult['final_sum'] + $data['price'], 2);

            // 如果优惠金额大于总金额 实付优惠金额 = 总金额
            if ($revisit > $allAmount) $revisit = $allAmount;

            $realGoodsAmount = $realGoodsAmount - $revisit;

            if ($realGoodsAmount <= 0) {
                $realDeliveryAmount = $realGoodsAmount + $realDeliveryAmount;
                $realGoodsAmount = 0;
            }
        }

        // Ydui商品只生成一个订单
        //生成的订单数据
        $dataArray = array(
            'order_no'            => Order_Class::createOrderNum(),
            'user_id'             => $user_id,
            'accept_name'         => isset($accept_name) ? $accept_name : "",
            'pay_type'            => $payment,
            'distribution'        => isset($delivery_id) ? $delivery_id : "",
            'postcode'            => isset($zip) ? $zip : "",
            'telphone'            => isset($telphone) ? $telphone : "",
            'province'            => isset($province) ? $province : "",
            'city'                => isset($city) ? $city : "",
            'area'                => isset($area) ? $area : "",
            'address'             => isset($address) ? $address : "",
            'mobile'              => $mobile,
            'create_time'         => ITime::getDateTime(),
            'postscript'          => $order_message,
            'accept_time'         => isset($accept_time) ? $accept_time : "",

            //商品价格
            'payable_amount'      => $goodsResult['final_sum'],
            'real_amount'         => $realGoodsAmount,

            //运费价格
            'payable_freight'     => $data['price'],
            'real_freight'        => $realDeliveryAmount,

            //优惠价格 revisit
            'promotions'          => $revisit,

            //订单应付总额    订单总额 + 运费金额
            'order_amount'        => $realGoodsAmount + $realDeliveryAmount,

            //商家ID
            'seller_id'           => 0, // Ydui 下单的默认0

            //商品类型
            'goods_type'          => 'Ydui', // Ydui商品

            // 自提点 设置了快递方式为2的为自提点
            'takeself'            => $delivery_id == 2 ? '1' : '',

            // 激活会员的uid
            'active_uid'          => $active_id,
        );

        $dataArray['order_amount'] = $dataArray['order_amount'] <= 0 ? 0 : $dataArray['order_amount'];
        // $dataArray['order_amount'] = $dataArray['order_amount'] <= 0 ? 0 : 0.01;

        if ($gift_id) $dataArray['gift'] = $gift_id;

        //生成订单插入order表中
        $orderObj  = new IModel('order');
        $orderObj->setData($dataArray);
        $order_id = $orderObj->add();

        if ($order_id == false) die(JSON::encode(array('status' => 'fail', 'error' => '订单生成错误')));

        /*将订单中的商品插入到order_goods表*/
        $orderInstance = new Order_Class();
        $orderGoodsResult = $orderInstance->insertOrderGoods($order_id, $goodsResult);

        if ($orderGoodsResult !== true) die(JSON::encode(array('status' => 'fail', 'error' => $orderGoodsResult)));

        // 如果使用了优惠
        if ($revisit > 0 && !$active_id) {
            // 还剩余优惠的金额
            $update = $userRow['revisit'] - $revisit;
            $res = $userDB->setData(array('revisit' => $update))->update('id = ' . $user_id);

            if ($res) {
                $log = array(
                    'user_id'   => $user_id,
                    'type'      => '1',
                    'time'      => ITime::getDateTime(),
                    'value'     => $revisit,
                    'value_log' => $update,
                    'note'      => '用户: ' . $userRow['username'] . ' 在订单号：' . $dataArray['order_no'] . ' 使用了优惠 ' . $revisit,
                );
                $logDB = new IModel('revisit_log');
                $logDB->setData($log)->add();
            }
        }

        //收货地址的处理
        if ($user_id && $address_id) {
            $addressDefRow = $addressDB->getObj('user_id = ' . $user_id . ' and is_default = 1');
            if (!$addressDefRow) {
                $addressDB->setData(array('is_default' => 1));
                $addressDB->update('user_id = ' . $user_id . ' and id = ' . $address_id);
            }
        }

        //订单金额小于等于0直接免单
        if ($dataArray['order_amount'] <= 0) {
            Order_Class::updateOrderStatus($dataArray['order_no']);
            return ['type' => 0, 'order_id' => $order_id];
        }
        return ['type' => 1, 'order_id' => $order_id];
    }
}
