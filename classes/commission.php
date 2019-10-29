<?php

/***
 *      ┌─┐       ┌─┐ + +
 *   ┌──┘ ┴───────┘ ┴──┐++
 *   │                 │
 *   │       ───       │++ + + +
 *   ███████───███████ │+
 *   │                 │+
 *   │       ─┴─       │
 *   │                 │
 *   └───┐         ┌───┘
 *       │         │
 *       │         │   + +
 *       │         │
 *       │         └──────────────┐
 *       │                        │
 *       │                        ├─┐
 *       │                        ┌─┘
 *       │                        │
 *       └─┐  ┐  ┌───────┬──┐  ┌──┘  + + + +
 *         │ ─┤ ─┤       │ ─┤ ─┤
 *         └──┴──┘       └──┴──┘  + + + +
 *                神兽保佑
 *               代码无BUG!
 * 			Powered by lixingFan
 */

/**
 * @class Commission
 * @brief 商城佣金类库
 */
class Commission
{
    // 订单id
    private $order_id = 0;

    // 下订单的uid
    private $user_id = 0;

    // 下订单的商品金额
    private $real_amount = 0;

    /**
     * 构造函数
     *
     * @param int $order_id 订单id
     */
    function __construct($order_id)
    {
        $this->order_id = $order_id;
    }
    /**
     * 用户订单完成后初始化方法
     *
     * @param  int $order_id 订单id
     * @return void
     */
    public function init()
    {
        // 获取订单信息
        $orderRow = (new IModel('order'))->getObj('id = ' . $this->order_id . ' and pay_status = 1 and status = 5 and active_uid = 0');

        // 必须是已完成的
        if (!$orderRow || !$orderRow['user_id']) return false;

        $this->user_id = $orderRow['user_id'];

        // 远程商品的商品金额 就是订单应付金额
        $this->real_amount = $orderRow['goods_type'] == 'Ydui' ? $orderRow['payable_amount'] : $orderRow['real_amount'];

        // 更新parents佣金
        self::updateParentsCommission();

        // 更新分红
        self::updateProfitUsers();

        // 满3900返390
        self::cashBack();

        // 结束
        return true;
    }

    /**
     * vipParents佣金
     *
     * @return void
     */
    private function updateParentsCommission()
    {
        $user_id = $this->user_id;

        // 找到下单人的父级
        $vipParents = Team::getVipParentsInfo($user_id);

        if (!is_array($vipParents) || empty($vipParents)) return false;

        // 要更新的parents level数组 uid => percent
        $balanceArr = array();

        // 要更新的parents agent数组 uid => percent
        $agentArr = array();

        // 当前已经发到哪个level了
        $tmpLevel = null;

        // 当前level已经发到哪个百分比了
        $tmpCommission = 0;

        // 当前agentlevel
        $tmpAgentLevel = null;

        // 当前agent已经发到哪个百分比了
        $tmpAgentCommission = 0;

        // 佣金配置 level => percent
        $commissionConf = self::getLevelCommissionConfig();

        // 佣金配置 agent_id => percent
        $agentCommissionConf = self::getAgentCommissionConfig();

        // 循环所有parents
        foreach ($vipParents as $parent_id => $parentRow) {
            // 当前parent的level奖励
            $parentCommission = $commissionConf[$parentRow['level']];
            // 当前parent的有vip奖励并且level大于前一个parent
            if ($parentCommission && ($parentRow['level'] > $tmpLevel)) {

                // 当前应得百分比
                $levelCommission = $parentCommission - $tmpCommission;

                // 更新数组
                if ($levelCommission) $balanceArr[$parent_id] = $levelCommission;

                // 更新tmp
                $tmpLevel = $parentRow['level'];
                $tmpCommission = $parentCommission;
            }

            // 当前是否agent
            $agentLevel = Team::getAgentLevelByUserRow($parentRow);
            if ($agentLevel) {
                // 当前应得agent奖励
                $agentCommission = $agentCommissionConf[$agentLevel];
                // 当前有奖励并且level大于前一个
                if ($agentCommission && ($agentLevel > $tmpAgentLevel)) {

                    // 当前应得百分比
                    $agentFinal = $agentCommission - $tmpAgentCommission;

                    // 更新数组
                    if ($agentFinal) $agentArr[$parent_id] = $agentFinal;

                    // 更新tmp
                    $tmpAgentLevel = $agentLevel;
                    $tmpAgentCommission = $agentCommission;
                }
            }
        }

        // 获得实际 uid => balance
        $levelBalanceArr = self::getActualCommission($balanceArr);
        $agenBalanceArr = self::getActualCommission($agentArr);

        // 发level奖励和记录日志
        if ($levelBalanceArr) Team::recordAccountLog($levelBalanceArr, '14', $user_id, $this->order_id);

        // 发agent奖励和记录日志
        if ($agenBalanceArr) Team::recordAccountLog($agenBalanceArr, '17', $user_id, $this->order_id);

        return true;
    }

    /**
     * 分红
     *
     * @return void
     */
    private function updateProfitUsers()
    {
        // 获取分红的用户
        $userArr = self::getLevelProfitUsers();

        // 获取分红的金额
        $percents = self::getLevelProfitPercent();

        // uid数组 level => uid
        $uidArr = array();

        // percent数组 level => percent
        $percentArr = array();

        // 发放百分比分红
        foreach ($userArr as $level => $userRow) {
            if (empty($userRow)) continue;

            $uidArr[$level] = array_keys($userRow);

            // 得到当前level的金额 百分比
            $percent =  $percents[$level];

            // 当前level平均分多少
            if ($percent) $percentArr[$level] = $percent / count($userRow);
        }

        // 根据百分比得到实际的金额 level => balance
        $balanceArr = self::getActualCommission($percentArr);

        // 要更新的数组 uid => balance
        $updateArr = array();

        foreach ($uidArr as $level => $uids) {
            foreach ($uids as $key => $uid) {
                $updateArr[$uid] = $balanceArr[$level];
            }
        }

        // 发level奖励和记录日志
        if ($updateArr) Team::recordAccountLog($updateArr, '18', $this->user_id, $this->order_id);
    }

    /**
     * 满xx返xx
     *
     * @return void
     */
    private function cashBack()
    {
        $user_id = $this->user_id;
        $userDB = new IModel('user');
        $where = 'id = ' . $user_id;
        $userRow = $userDB->getObj($where);

        if (!$userRow) return false;

        // 更新用户cash_back 记录日志
        $curCashBack = $this->real_amount + $userRow['cash_back'];
        $res = $userDB->setData(array('cash_back' => $curCashBack))->update($where);
        if ($res) {
            $logObj = new Log('db');
            // 'admin_id','user_id','type','value','value_log','from_oid','note'
            $logObj->write('cash_back', array('', $user_id, '0', $this->real_amount, $curCashBack, $this->order_id));
        }

        // 满返基数
        $cashBackAmount = self::getBaseCashBackAmount();

        // 满返百分比
        $cashBackPercent = self::getCashBackPercent();

        // 可以返几次基数
        $cashBackCount = floor($curCashBack / $cashBackAmount);

        if (!$cashBackCount) return false;

        // 要返的金额 基数 * 次数
        $perCashBack = $cashBackAmount * $cashBackCount;

        // 返金额 要返的金额 * 百分比
        $cashBackBalance = round($perCashBack * $cashBackPercent, 2) / 100;

        if (!$cashBackBalance) return false;

        // 更新用户balance 和 记录日志
        Team::recordAccountLog(array($user_id => $cashBackBalance), '15', 0);

        // 更新cash_back
        $finalCashBack = round($curCashBack - $perCashBack, 2);
        $result = $userDB->setData(array('cash_back' => $finalCashBack))->update($where);
        if ($result) {
            $logObj = new Log('db');
            $logObj->write('cash_back', array('', $user_id, '1', $perCashBack, $finalCashBack, $this->order_id));
        }

        // 结束
        return true;
    }

    /**
     * 获得实际佣金金额
     *
     * @param array $balanceArr uid=>percent
     * @return array uid => balance
     */
    private function getActualCommission($balanceArr)
    {
        if (!is_array($balanceArr) || empty($balanceArr)) return false;

        $result = array();
        foreach ($balanceArr as $level => $percent) {
            $val = round(self::getCommissionRatio() * $percent / 100, 2);
            if ($val) $result[$level] = $val;
        }

        return $result;
    }

    /**
     * 获取商城分红的users
     *
     * @return array
     */
    private function getLevelProfitUsers()
    {
        // level 32 对应的用户信息
        $users = (new IModel('user'))->query('level = 32 and is_bonus = 1');

        $result = array();
        foreach ($users as $key => $user) {
            $result['32'][$user['id']] = $user;
        }
        return $result;
    }

    /**
     * 等级对应的分红百分比
     *
     * @return array
     */
    private function getLevelProfitPercent()
    {
        return array(
            '32' => 1,
        );
    }

    /**
     * 佣金计算比例 订单商品金额 / 5 * 4
     *
     * @return float
     */
    private function getCommissionRatio()
    {
        return round($this->real_amount * 2 * 4 / 10, 2);
    }

    /**
     * level对应的佣金百分比
     *
     * @param int $level
     * @return array
     */
    private function getLevelCommissionConfig($level = '')
    {
        $baseConfig = Team::levelBonusConfig($level);

        $config = array();
        if ($baseConfig) {
            foreach ($baseConfig as $key => $val) {
                $config[$key] = $val['sale'];
            }
        }

        return $config;
    }

    /**
     * agentLevel对应的佣金百分比
     *
     * @param int $agent_level
     * @return array
     */
    private function getAgentCommissionConfig($agent_level = '')
    {
        $baseConfig = Team::agentBonusConfig($agent_level);

        $config = array();
        if ($baseConfig) {
            foreach ($baseConfig as $key => $val) {
                $config[$key] = $val['sale'];
            }
        }

        return $config;
    }

    /**
     * 满返的基数
     *
     * @return int
     */
    private function getBaseCashBackAmount()
    {
        return 3900;
    }

    /**
     * 满返百分比
     *
     * @return int
     */
    private function getCashBackPercent()
    {
        return 10;
    }
}
