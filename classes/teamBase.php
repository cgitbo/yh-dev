<?php
class teamBase
{
    /***
     *                  ___====-_  _-====___
     *            _--^^^#####//      \\#####^^^--_
     *         _-^##########// (    ) \\##########^-_
     *        -############//  |\^^/|  \\############-
     *      _/############//   (@::@)   \\############\_
     *     /#############((     \\//     ))#############\
     *    -###############\\    (oo)    //###############-
     *   -#################\\  / VV \  //#################-
     *  -###################\\/      \//###################-
     * _#/|##########/\######(   /\   )######/\##########|\#_
     * |/ |#/\#/\#/\/  \#/\##\  |  |  /##/\#/  \/\#/\#/\#| \|
     * `  |/  V  V  `   V  \#\| |  | |/#/  V   '  V  V  \|  '
     *    `   `  `      `   / | |  | | \   '      '  '   '
     *                     (  | |  | |  )
     *                    __\ | |  | | /__
     *                   (vvv(VVV)(VVV)vvv)                
     *                        神兽保佑
     *                       代码无BUG!
     * 				    Powered by lixingFan
     */

    /**
     * 根据用户level判断是否vip
     *
     * @param  int     $level 要判断的level
     * @return boolean true | false 成功返回true
     */
    static function isVipByUserLevel($level)
    {
        return $level >= 11;
    }

    /**
     * 根据激活订单active_id判断是否vip
     * 存在已支付并且未删除的订单
     *
     * @param int $active_id vip_order对应的active_id
     * @return boolean true | false 成功返回true
     */
    static function isVipByVipOrderActiveId($active_id)
    {
        return boolval((new IModel('vip_order'))->getObj('active_id = ' . $active_id . ' and pay_status = 1 and if_del = 0'));
    }

    /**
     * 根据uid 获取vip用户信息
     *
     * @param int $user_id
     * @return array | boolean 成功用户信息 失败false
     */
    static function getVipInfoByUserId($user_id)
    {
        $userRow = self::getMemberInfo($user_id);

        $isVip = self::isVipByUserLevel($userRow['level']);
        if (!$userRow || !$isVip) return false;
        return $userRow;
    }

    /**
     * 根据uid 获取用户信息
     *
     * @param int $user_id
     * @return array | boolean 成功用户信息 失败false
     */
    static function getMemberInfo($user_id)
    {
        return (new IModel('member as m, user as u'))->getObj("m.user_id = u.id and m.user_id=" . $user_id);
    }

    /**
     * 根据uid获取返回是否agent
     *
     * @param int $user_id
     * @return int agentLevel | boolean false 成功返回agentLevel 失败返回false
     */
    static function getAgentLevelByUserId($user_id)
    {
        // 是agent 一定是vip
        $userRow = self::getVipInfoByUserId($user_id);
        if (!$userRow) return false;
        // 同时存在则为agent
        if ($userRow['is_agent'] && $userRow['agent_level']) return $userRow['agent_level'];
        return false;
    }

    /**
     * 根据userROw返回是否agent
     *
     * @param array $userRow
     * @return int agentLevel | boolean false 成功返回agentLevel 失败返回false
     */
    static function getAgentLevelByUserRow($userRow)
    {
        if (!$userRow) return false;
        $isVip = self::isVipByUserLevel($userRow['level']);
        // 是agent 一定是vip
        if ($isVip && $userRow['is_agent'] && $userRow['agent_level']) return $userRow['agent_level'];
        return false;
    }

    /**
     * 升级vip金额验证是否正确
     *
     * @param  int $amount 要验证的金额
     * @return boolean true | false 成功返回true 失败返回false
     */
    static function isVipConfig($amount)
    {
        $vipConfig = self::vipStatusConfig();
        return array_key_exists($amount, $vipConfig);
    }

    /**
     * 推荐人信息验证
     *
     * @param  int    $from_id     要验证的uid
     * @param  string $parent_name 要验证的username
     * @return array['msg'] | int $from_id 成功返回uid 失败返回错误信息array['msg']
     */
    static function validateParentInfo($from_id, $parent_name = '')
    {
        $errMsg = array('msg' => '邀请人不正确');
        if (!$from_id && !$parent_name) return $errMsg;

        $userObj = new IModel('user');

        $where = null;
        if ($from_id) {
            $where = 'id = ' . $from_id;
        } elseif ($parent_name) {
            $where = 'username = "' . $parent_name . '"';
        } else {
            return $errMsg;
        }

        $userRow = $userObj->getObj($where);
        if ($userRow && self::isVipByUserLevel($userRow['level'])) {
            return $userRow['id'];
        }

        return $errMsg;
    }

    /**
     * 查找uid是否findId的父级
     *
     * @param int $uid    父级uid
     * @param int $findId 要查的uid
     * @return boolean
     */
    static function isPaternity($uid, $findId)
    {
        $fixUserArr = self::getFixUserArr();
        $findRow = $fixUserArr[$findId];
        if (!$findRow) return false;

        $parent_id = $findRow['parent_id'];
        while (true) {
            if (!$parent_id) return false;
            if ($parent_id == $uid) return true;
            $parent_id = $fixUserArr[$parent_id]['parent_id'];
        }
        return false;
    }

    /**
     * 获取所有用户的信息
     *
     * @return array key=>uid val=>userRow
     */
    static function getFixUserArr($where = '1')
    {
        $sql = new IQuery('user as u');
        $sql->join = 'left join member as m on u.id = m.user_id';
        $sql->where = $where;
        $allUserArr = $sql->find();

        // 固定会员结构 key=> uid val=>user
        $fixUserArr = array();
        foreach ($allUserArr as $key => $user) {
            $fixUserArr[$user['id']] = $user;
        }

        return $fixUserArr;
    }

    /**
     * 用户升级所需信息
     *
     * @return array string $key=>level, array $invited_sum=>邀请人数 $team_sum=>总团队人数 $min_sum=> 除去最大的人数后 剩下的人数的合
     */
    static function userLevelConfig()
    {
        return array(
            '11' => array(
                'invited_sum' => 7,
                'team_sum'    => 15,
            ),
            '12' => array(
                'invited_sum' => 7,
                'team_sum'    => 50,
                'min_sum'     => 20,
            ),
            '13' => array(
                'invited_sum' => 7,
                'team_sum'    => 150,
                'min_sum'     => 60,
            ),
            '21' => array(
                'invited_sum' => 7,
                'team_sum'    => 500,
                'min_sum'     => 200,
            ),
            '22' => array(
                'invited_sum' => 7,
                'team_sum'    => 1500,
                'min_sum'     => 600,
            ),
            '23' => array(
                'invited_sum' => 7,
                'team_sum'    => 5000,
                'min_sum'     => 2000,
            ),
            '31' => array(
                'invited_sum' => 7,
                'team_sum'    => 15000,
                'min_sum'     => 6000,
            ),
        );
    }

    /**
     * 成为vip后更新user对应的字段
     *
     * @return array string $key=>金额, array $val=>user对应的字段
     */
    static function vipStatusConfig()
    {
        return array(
            // vip
            '3900' => array(
                'level'         => '11',
                'active_amount' => '3900',
                'check_time'    => ITime::getDateTime(),
            ),

            // 代理商
            '49000' => array(
                'level'         => '11',
                'is_agent'      => '1',
                'agent_level'   => '1',
                'active_amount' => '49000',
                'check_time'    => ITime::getDateTime(),
            ),
            '69000' => array(
                'level'         => '11',
                'is_agent'      => '1',
                'agent_level'   => '2',
                'active_amount' => '69000',
                'check_time'    => ITime::getDateTime(),
            ),
            '89000' => array(
                'level'         => '11',
                'is_agent'      => '1',
                'agent_level'   => '3',
                'active_amount' => '89000',
                'check_time'    => ITime::getDateTime(),
            ),
        );
    }

    /**
     * 成为vip后自身奖励
     *
     * @param int $level
     * @return array
     */
    static function becomeVipStatusConfig($level = '')
    {
        $config = array(
            '11' => array(
                'sec_stocks' => 400,
            ),
        );
        if ($config[$level]) return $config[$level];
        return $config;
    }

    /**
     * 成为vipShop后自身的奖励
     *
     * @return array
     */
    static function becomeAgentStatusConfig($agent_level = '')
    {
        $config = array(
            '1' => array(
                'sec_stocks' => 5000,
            ),
            '2' => array(
                'sec_stocks' => 10000,
            ),
            '3' => array(
                'sec_stocks' => 15000,
            ),
        );
        if ($agent_level) return $config[$agent_level];
        return $config;
    }


    /**
     * level 对应的bonus
     *
     * array(
     *   '11' => array(
     *       'vip'        => children升级vip后的 balance奖励
     *       'sale'       => children购买商品后的 百分比balance奖励
     *       'sec_stocks' => 自身达到level后的 sec_stocks奖励
     *    ),
     *  ),
     * 
     * @param int $level 当前level对应的bonus
     * @return array $levelBonusConfig
     */
    static function levelBonusConfig($level = '')
    {
        $config = array(
            '11' => array(
                'vip'        => 1200,
                'sale'       => 3,
            ),
            '12' => array(
                'vip'        => 1500,
                'sale'       => 4,
            ),
            '13' => array(
                'vip'        => 1700,
                'sale'       => 5,
                'sec_stocks' => 200,
            ),
            '21' => array(
                'vip'        => 1900,
                'sale'       => 6,
                'sec_stocks' => 500,
            ),
            '22' => array(
                'vip'        => 2100,
                'sale'       => 7,
                'sec_stocks' => 1000,
            ),
            '23' => array(
                'vip'        => 2300,
                'sale'       => 8,
                'sec_stocks' => 1500,
            ),
            '31' => array(
                'vip'        => 2500,
                'sale'       => 9,
                'sec_stocks' => 5000,
            ),
            '32' => array(
                'vip'        => 2700,
                'sale'       => 10,
            ),
        );
        $default = array(
            'vip'  => 0,
            'sale' => 0,
        );
        if ($level) return $config[$level] ? $config[$level] : $default;
        return $config;
    }

    /**
     * agent 对应的bonus
     * 
     * array(
     *   '1' => array(
     *       'vip'  => children升级vip后的 balance奖励
     *       'sale' => children购买商品后的 百分比balance奖励
     *    ),
     *  ),
     * 
     * @return array $agentBonusConfig
     */
    static function agentBonusConfig()
    {
        return array(
            '1' => array(
                'vip'  => 80,
                'sale' => 1,
            ),
            '2' => array(
                'vip'  => 90,
                'sale' => 2,
            ),
            '3' => array(
                'vip'  => 100,
                'sale' => 3,
            ),
        );
    }

    /**
     * vipRecomAgent 对应的bonus
     *
     * @return void
     */
    static function vipRecomAgentBonusConfig()
    {
        return array(
            '1' => array(
                'bonus'  => 1500,
                'sec_stocks' => 300,
            ),
            '2' => array(
                'bonus'  => 2000,
                'sec_stocks' => 500,
            ),
            '3' => array(
                'bonus'  => 3000,
                'sec_stocks' => 1000,
            ),
        );
    }

    /**
     * vip邀请vip的奖励
     *
     * @return array
     */
    static function vipRecomVipBonusConfig()
    {
        return ['sec_stocks' => 200];
    }

    /**
     * 要分红的conf key=>level val=>where
     *
     * @param int $level
     * @return array $config
     */
    static function profitLevelConfig($level = '')
    {
        $config = array(
            '13' => 'level = 13 and is_bonus = 1',
            '21' => 'level = 21 and is_bonus = 1',
            '22' => 'level = 22 and is_bonus = 1',
            '23' => 'level = 23 and is_bonus = 1',
            '31' => 'level = 31 and is_bonus = 1',
        );
        if ($level) return $config[$level];
        return $config;
    }

    /**
     * 要分红的level对应的千分比 key=>level val=>千分比
     *
     * @param int $level
     * @return array $config
     */
    static function profitLevelBonusConfig($level = '')
    {
        $config = array(
            '13' => 8,
            '21' => 8,
            '22' => 8,
            '23' => 8,
            '31' => 8,
        );
        if ($level) return $config[$level];
        return $config;
    }

    /**
     * 根据当前level返回升级后的新level
     *
     * @param int $level 当前用户level
     * @return int | false $level 升级后level
     */
    static function getNewLevel($level)
    {
        switch ($level) {
            case 11:
                return 12;
                break;
            case 12:
                return 13;
                break;
            case 13:
                return 21;
                break;
            case 21:
                return 22;
                break;
            case 22:
                return 23;
                break;
            case 23:
                return 31;
                break;
            case 31:
                return 32;
                break;
            case 32:
                return 32;
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * 提现服务费百分比
     *
     * @return int 百分比
     */
    static function serviceChargeConf()
    {
        return 4;
    }

    /**
     * 要计算的报单金额的基数
     *
     * @param int $active_amount
     * @return int $realActiveAmount
     */
    static function getRealActiveAmount($active_amount)
    {
        $baseAmount = 3900;
        return $active_amount > $baseAmount ? $baseAmount : $active_amount;
    }

    /**
     * 生成vip订单号码
     *
     * @return int $orderNum 订单号
     */
    static function createVipOrderNum()
    {
        $newOrderNo = date('YmdHis') . rand(1000, 9999);

        if ((new IModel('vip_order'))->getObj('order_no = "' . $newOrderNo . '"')) {
            return self::createOrderNum();
        }
        return $newOrderNo;
    }
}
