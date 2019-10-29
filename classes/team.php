<?php

/**
 * Team相关方法
 */
class Team extends teamBase
{
    /**
     *                    _ooOoo_
     *                   o8888888o
     *                   88" . "88
     *                   (| -_- |)
     *                    O\ = /O
     *                ____/`---'\____
     *              .   ' \\| |// `.
     *               / \\||| : |||// \
     *             / _||||| -:- |||||- \
     *               | | \\\ - /// | |
     *             | \_| ''\---/'' | |
     *              \ .-\__ `-` ___/-. /
     *           ___`. .' /--.--\ `. . __
     *        ."" '< `.___\_<|>_/___.' >'"".
     *       | | : `- \`.;`\ _ /`;.`/ - ` : | |
     *         \ \ `-. \_ __\ /__ _/ .-` / /
     * ======`-.____`-.___\_____/___.-`____.-'======
     *                    `=---='
     *
     * 				Powered by lixingFan
     * .............................................
     *          佛祖保佑             永无BUG
     */

    /**
     * 用户激活vip后初始化方法
     *
     * @param  int $user_id 激活的uid
     * @return void
     */
    static function init($user_id)
    {
        $userRow = self::getVipInfoByUserId($user_id);

        // 判断当前用户是否vip
        if (!$userRow) return;

        // 更新parents team_sum
        self::updateColsWithCaseWhen($user_id, 'team_sum');

        // 更新parents level
        self::updateColsWithCaseWhen($user_id, 'level');

        // 如果是空单 不继续发奖励
        if ($userRow['is_empty']) return;

        // 更新自身成为vip的奖励
        self::updateBecomeVipBonus($user_id);

        // 更新parent sec_stocks奖励
        self::updateParentSecstocks($user_id);

        // 更新parents banlance
        self::updateParentsBonus($user_id);

        // 当前uid是vipAgent 给uid的sec_stocks奖励
        self::updateVipAgentBonus($user_id);

        // 当前uid是vipAgent 给uid的parent额外奖励
        self::updateVipRecomAgentBonus($user_id);

        // 更新符合条件的加权分红
        self::updateProfitUsers($user_id);

        // 结束
        return true;
    }

    /**
     * 更新自身成为vip的奖励
     *
     * @param int $user_id
     * @return void
     */
    static function updateBecomeVipBonus($user_id)
    {
        // 当前用户信息
        $userRow = self::getVipInfoByUserId($user_id);
        if (!$userRow) return false;

        // 当前用户对应的奖励
        $bonusArr = self::becomeVipStatusConfig($userRow['level']);

        // 更新sec_stocks
        if ($bonusArr && $bonusArr['sec_stocks']) {
            // 要更新的sec_stocks
            $updateSecStocks = $bonusArr['sec_stocks'] + $userRow['sec_stocks'];

            // 更新数据
            $result = (new IModel('user'))->setData(array('sec_stocks' => $updateSecStocks))->update('id = ' . $user_id);

            // log配置 'admin_id','user_id','type','event','value','value_log','from_id','note'
            $secScocksLog = array('', $user_id, '', '0', $bonusArr['sec_stocks'], $updateSecStocks);

            // 记录sec_stocks日志
            if ($result) return (new log('db'))->write('sec_scocks', $secScocksLog);
        }
        return true;
    }

    /**
     * 批量更新uid的parents team_sum | level
     *
     * @param int $user_id 当前升级vip的uid
     * @param int $cols    要更新的字段 team_sum | level (parents的team_sum +1 | 更新parents的level)
     * @return int | boolean 成功返回数字 失败返回false
     */
    static function updateColsWithCaseWhen($user_id, $cols)
    {
        // 暂时只支持这两个字段
        $colsConfig = array('team_sum', 'level');
        if (!in_array($cols, $colsConfig)) return false;

        // 当前用户信息
        $userRow = self::getVipInfoByUserId($user_id);
        if (!$userRow) return false;

        // 固定会员结构 key=> uid val=>user
        $fixUserArr = self::getFixUserArr();

        // 要更新的信息
        $updateArr = array();
        $parent_id = $userRow['parent_id'];

        while (true) {
            // 没parent_id退出循环
            if (!$parent_id) break;

            // 要更新的parent信息
            $parentRow = $fixUserArr[$parent_id];

            // 不存在 或者 不是vip 直接return
            if ((!$parentRow || !self::isVipByUserLevel($parentRow['level'])) && !$parentRow['parent_id']) break;

            // 要更新的字段是level
            if ($cols == 'level') {
                // 判断允许更新level 允许返回新level 否则返回false
                $newLevel = self::isLevelUpdate($parent_id);
                if ($newLevel) $updateArr[$parent_id] = $newLevel;
            } else {
                // 要更新的字段是team_sum 直接 +1
                $updateArr[$parent_id] = $parentRow['team_sum'] + 1;
            }

            // 更新parent_id
            $parent_id = $parentRow['parent_id'];
        }

        // level有更新 记录levelChange日志和发level奖励
        if ($cols == 'level' && $updateArr) {
            // 记录levelChange日志和发sec_stocks
            self::recordLeveChangeLog($updateArr, $user_id);
        }

        // 更新数据 team_sum || level
        if ($updateArr) return (new IModel('user'))->updateWithCase($cols, 'id', $updateArr);
        return false;
    }

    /**
     * 更新邀请人的邀请奖励
     *
     * @param int $user_id
     * @return void
     */
    static function updateParentSecstocks($user_id)
    {
        // 当前uid是不是vip
        $userRow = self::getVipInfoByUserId($user_id);
        if (!$userRow) return false;

        $parentRow = self::getVipInfoByUserId($userRow['parent_id']);
        if (!$parentRow) return false;

        // 奖励的金额
        $recomConf = self::vipRecomVipBonusConfig();

        // 要更新的sec_stocks
        $updateSecStocks = $parentRow['sec_stocks'] + $recomConf['sec_stocks'];

        // 更新数据
        $result = (new IModel('user'))->setData(array('sec_stocks' => $updateSecStocks))->update('id = ' . $parentRow['user_id']);

        // log配置 'admin_id','user_id','type','event','value','value_log','from_id','note'
        $secScocksLog = array('', $parentRow['user_id'], '', '1', $recomConf['sec_stocks'], $updateSecStocks, $user_id);

        // 记录sec_stocks日志
        if ($result) return (new log('db'))->write('sec_scocks', $secScocksLog);
    }

    /**
     * 根据uid更新满足条件的parents的banlance
     * 1. 当前是Vipshop 奖励sec_stocks
     * 2. parents的level奖励
     * 3. parents的agent奖励
     *
     * @param int $user_id 升级vip的uid
     * @return void
     */
    static function updateParentsBonus($user_id)
    {
        // 当前uid是不是vip
        $userRow = self::getVipInfoByUserId($user_id);
        if (!$userRow) return false;

        // 要更新的parents vip banlance数组 uid => value
        $banlanceArr = array();

        // 要更新的parents agent
        $agentArr = array();

        // 当前已经发到哪个level了
        $tmpLevel = null;

        // 当前已经发到哪个bonus了
        $tmpBonus = 0;

        // 当前agentlevel
        $tmpAgentLevel = null;

        // 当前店铺bonus
        $tmpAgentBonus = 0;

        // 所有level奖励配置
        $levelBonusConfig = self::levelBonusConfig();

        // 所有agent奖励配置
        $agentBonusConfig = self::agentBonusConfig();

        // 当前用户所有的vipParents信息
        $vipParents = self::getVipParentsInfo($user_id);

        // 循环所有parents
        foreach ($vipParents as $parent_id => $parentRow) {
            // 当前parent的level奖励
            $parentBonus = $levelBonusConfig[$parentRow['level']];
            // 当前parent的有vip奖励并且level大于前一个parent
            if ($parentBonus && $parentBonus['vip'] && ($parentRow['level'] > $tmpLevel)) {

                // 当前应得多少
                $levelBonus = $parentBonus['vip'] - $tmpBonus;

                // 更新数组
                if ($levelBonus) $banlanceArr[$parent_id] = $levelBonus;

                // 更新tmp
                $tmpLevel = $parentRow['level'];
                $tmpBonus = $parentBonus['vip'];
            }

            // 当前是否agent
            $agentLevel = self::getAgentLevelByUserRow($parentRow);
            if ($agentLevel) {
                // 当前应得agent奖励
                $agentBonus = $agentBonusConfig[$agentLevel];
                // 当前有奖励并且level大于前一个
                if ($agentBonus && $agentBonus['vip'] && ($agentLevel > $tmpAgentLevel)) {

                    // 当前应得多少
                    $vipShopFinal = $agentBonus['vip'] - $tmpAgentBonus;

                    // 更新数组
                    if ($vipShopFinal) $agentArr[$parent_id] = $vipShopFinal;

                    // 更新tmp
                    $tmpAgentLevel = $agentLevel;
                    $tmpAgentBonus = $agentBonus['vip'];
                }
            }
        }

        // 发level奖励和记录日志
        if ($banlanceArr) self::recordAccountLog($banlanceArr, '12', $user_id);

        // 发agent奖励和记录日志
        if ($agentArr) self::recordAccountLog($agentArr, '16', $user_id);

        return true;
    }

    /**
     * 当前vipAgent奖励sec_stocks
     *
     * @param int $user_id 当前vipAgent的uid
     * @return void
     */
    static function updateVipAgentBonus($user_id)
    {
        // 当前uid是不是vip
        $userRow = self::getVipInfoByUserId($user_id);
        if (!$userRow) return false;

        // 当前agentLevel
        $vipAgentLevel = self::getAgentLevelByUserRow($userRow);

        if (!$vipAgentLevel) return false;

        // 当前agent奖励配置
        $vipAgentConfig = self::becomeAgentStatusConfig($vipAgentLevel);

        // 给当前agent 奖励 sec_stocks
        if ($vipAgentConfig && is_array($vipAgentConfig) && $vipAgentConfig['sec_stocks']) {

            // 当前agent奖励
            $vipAgentSecStocks = $vipAgentConfig['sec_stocks'] + $userRow['sec_stocks'];

            // 更新uid的sec_stocks字段
            $result = (new IModel('user'))->setData(array('sec_stocks' => $vipAgentSecStocks))->update('id = ' . $user_id);

            // log配置 'admin_id','user_id','type','event','value','value_log','from_id','note'
            $vipAgentLog = array('', $user_id, '', '3', $vipAgentConfig['sec_stocks'], $vipAgentSecStocks);

            // 记录sec_stocks日志
            if ($result) return (new log('db'))->write('sec_scocks', $vipAgentLog);
        }

        return false;
    }

    /**
     * vip邀请agent的额外奖励
     *
     * @param int $user_id 当前agent的uid
     * @return void
     */
    static function updateVipRecomAgentBonus($user_id)
    {
        // 当前uid是不是vip
        $userRow = self::getVipInfoByUserId($user_id);
        if (!$userRow) return false;

        // vip邀请agent的conf
        $recomAgentConf = self::vipRecomAgentBonusConfig();

        // 当前agentLevel
        $vipAgentLevel = self::getAgentLevelByUserRow($userRow);

        if (!$vipAgentLevel) return false;

        // 当前parent奖励
        $agentParentId = $userRow['parent_id'];
        if ($agentParentId && $agentParentRow = self::getVipInfoByUserId($agentParentId)) {

            // 当前应得奖励
            $recomAgentBonus = $recomAgentConf[$vipAgentLevel];

            // 更新sec_stocks
            if ($recomAgentBonus['sec_stocks']) {

                // 要更新的sec_stocks
                $updateSecStocks = $recomAgentBonus['sec_stocks'] + $agentParentRow['sec_stocks'];

                // 更新数据
                $result = (new IModel('user'))->setData(array('sec_stocks' => $updateSecStocks))->update('id = ' . $agentParentId);

                // log配置 'admin_id','user_id','type','event','value','value_log','from_id','note'
                $secScocksLog = array('', $agentParentId, '', '1', $recomAgentBonus['sec_stocks'], $updateSecStocks, $user_id);

                // 记录sec_stocks日志
                if ($result) (new log('db'))->write('sec_scocks', $secScocksLog);
            }

            // 更新balance和日志
            if ($recomAgentBonus['bonus']) {
                self::recordAccountLog(array($agentParentId => $recomAgentBonus['bonus']), '11', $user_id);
            }
            return true;
        }

        return false;
    }

    /**
     * 给uid满足条件的parents分红
     *
     * @param int $user_id
     * @return void
     */
    static function updateProfitUsers($user_id)
    {
        // 当前uid是不是vip
        $userRow = self::getVipInfoByUserId($user_id);
        if (!$userRow) return false;

        // 得到要分红的profitUsers
        $profitUsers = self::getProfitUsers();

        // 得到分红的config
        $profitPercentConf = self::profitLevelBonusConfig();

        // 要分红的基数金额
        $active_amount = self::getRealActiveAmount($userRow['active_amount']);

        // 要分红的uid数组
        $uidArr = array();

        // 临时数组
        $tmpProfitArr = array();

        // level对应的bonus数组 key=>level val=>bonus
        $levelBonusArr = array();

        // 要更新的数组 key=>uid val=>bonus
        $updateArr = array();

        // 循环profitUsers得到tmp数组
        foreach ($profitUsers as $level => $users) {
            $tmpProfitArr[$level] = array();
            foreach ($users as $key => $user) {
                $tmpProfitArr[$level][$user['id']] = $user;
            }
        }

        // 得到要分红的数组
        foreach ($tmpProfitArr as $level => $users) {

            if (empty($users)) continue;

            $uidArr[$level] = array_keys($users);

            // 得到当前level的金额 千分比 *1000
            $percentBonus = $active_amount * $profitPercentConf[$level];

            // 当前level平均分多少
            if ($percentBonus) {

                $bonus = $percentBonus / count($users);

                // 保留两位小数
                $bonus = floor($bonus) / 1000;
                $levelBonusArr[$level] = $bonus;
            }
        }

        // 得到最终要更新的数组
        foreach ($uidArr as $level => $uids) {
            foreach ($uids as $key => $uid) {
                $updateArr[$uid] = $levelBonusArr[$level];
            }
        }

        // 写日志和更新奖励
        return self::recordAccountLog($updateArr, '13', $user_id);
    }

    /**
     * 根据uid判断是否允许更新level
     *
     * @param int $user_id 要判断的uid
     * @return int | boolean 成功返回要更新的level 失败返回false
     */
    static function isLevelUpdate($user_id)
    {
        // 当前用户信息
        $userRow = self::getVipInfoByUserId($user_id);
        if (!$userRow) return false;

        // 所有level对应的条件数组
        $levelConfig = self::userLevelConfig();

        // 当前用户的条件数组
        $userData = array(
            'invited_sum' => self::getInvitedSumByUserId($user_id),
            'team_sum'    => self::getTeamSumByUserId($user_id),
            'min_sum'     => self::getTeamMinSumByUserId($user_id),
        );

        // 需要更新的level
        $userLevel = $userRow['level'];

        while (true) {
            // 需要更新level的条件数组
            $updateArr = $levelConfig[$userLevel];

            // 数组不存在 返回false
            if (!$updateArr || !is_array($updateArr)) break;

            $flag = true;
            // 判断是否满足条件
            foreach ($updateArr as $key => $value) {
                if ($userData[$key] < $value) {
                    $flag = false;
                    break;
                }
            }
            if (!$flag) break;

            // 符合条件的更新level 把当前用户level传进 返回更新的level
            $userLevel = self::getNewLevel($userLevel);
        }

        // 有新level就返回新的
        if ($userLevel != $userRow['level']) return $userLevel;
        return false;
    }

    /**
     * 根据用户id获取邀请人数
     *
     * @param  int $user_id    要获取的uid
     * @return int $invitedSum uid对应的invitedSum
     */
    static function getInvitedSumByUserId($user_id)
    {
        $result = (new IModel('user'))->getObj('parent_id = ' . $user_id . ' and level >= 11', 'count(*) as invitedSum');
        return $result['invitedSum'] ? $result['invitedSum'] : 0;
    }

    /**
     * 根据用户id获取team_sum
     *
     * @param  int   $user_id 要获取的uid
     * @return array $teamSum uid对应的teamSum
     */
    static function getTeamSumByUserId($user_id)
    {
        $result = (new IModel('user'))->getObj('id = ' . $user_id . ' and level >= 11', 'team_sum');
        return $result['team_sum'] ? $result['team_sum'] : 0;
    }

    /**
     * 根据用户id获取 除去最大的人数后 剩下的人数的和
     *
     * @param  int $user_id    要获取的uid
     * @return int $teamMinSum uid对应的总团队人数除去最大的人数后剩下的人数的和
     */
    static function getTeamMinSumByUserId($user_id)
    {
        // 所有邀请的id
        $childrens = self::getVipChildrensByUserId($user_id);

        // userTeamSum
        $userTeamSum = self::getTeamSumByUserId($user_id);

        $tmp = array();
        foreach ($childrens as $children) {
            $tmp[] = intval(self::getTeamSumByUserId($children['id']));
        }

        // 求和 - 最大的
        $teamMinSum = $userTeamSum - max($tmp);

        return $teamMinSum;
    }

    /**
     * 根据用户id获取vipchildrens
     *
     * @param  int   $user_id   要获取的uid
     * @return array $childrens uid对应的childrens
     */
    static function getVipChildrensByUserId($user_id)
    {
        $result = (new IModel('user'))->query('parent_id = ' . $user_id . ' and level >= 11');
        return $result;
    }

    /**
     * 根据uid获取是parents信息
     *
     * @param int    $user_id 要获取parents的uid
     * @param string $all     是否获取所有的parents
     * @return array $vipParents
     */
    static function getVipParentsInfo($user_id, $all = '')
    {
        // 当前用户信息
        if ($all == 'all') $userRow = self::getMemberInfo($user_id);
        else {
            $userRow = self::getVipInfoByUserId($user_id);
        }
        if (!$userRow) return false;

        // 要更新的信息
        $result = array();
        $parent_id = $userRow['parent_id'];

        // 固定会员结构 key=> uid val=>user
        $fixUserArr = self::getFixUserArr();

        while (true) {
            // 没parent_id退出循环
            if (!$parent_id) break;

            // 要更新的parent信息
            $parentRow = $fixUserArr[$parent_id];

            // 不存在parent信息 并且parent没有parent_id 直接return
            if (!$parentRow && !$parentRow['parent_id']) break;

            // 是vip 更新数据
            $isVip = self::isVipByUserLevel($parentRow['level']);
            if ($all == 'all') $isVip = true;

            if ($isVip) {
                // int_sum
                $parentRow['invited_sum'] = self::getInvitedSumByUserId($parent_id);

                // min_sum
                $parentRow['min_sum']    = self::getTeamMinSumByUserId($parent_id);

                // 更新数据
                $result[$parent_id] = $parentRow;
            }

            // 更新parent_id
            $parent_id = $parentRow['parent_id'];
        }

        return $result;
    }

    /**
     * 获取加权分红users
     *
     * @return void
     */
    static function getProfitUsers()
    {
        $query = new IModel('user');
        $profitConfig = self::profitLevelConfig();

        $result = array();
        foreach ($profitConfig as $level => $where) {
            $result[$level] = $query->query($where);
        }

        return $result;
    }

    /**
     * 记录level变更和增加sec_stocks
     *
     * @param array $updateArr key=>uid value=>newValue
     * @param int   $from_uid 奖励来源uid
     * @return void
     */
    static function recordLeveChangeLog($updateArr, $from_uid)
    {
        // 不满足条件 直接return
        if (!$updateArr && !is_array($updateArr)) return false;

        // 得到所有uid
        $uidArr = array_keys($updateArr);
        $uidStr = implode(',', $uidArr);

        $userObj = new IModel('user');
        // 查出当前users的信息
        $usersArr = $userObj->query('id in(' . $uidStr . ')', 'id, level, sec_stocks');

        // 要添加level_upgrade记录的data
        $levelLog = array();

        // 要添加sec_stocks记录的data
        $secStocksLog = array();

        // 要更新level的数组
        $secStocksData = array();

        // 所有奖励
        $levelBonusConfig = self::levelBonusConfig();

        foreach ($usersArr as $key => $user) {
            $levelLog[] = array(
                'user_id'   => $user['id'],
                'level'     => $updateArr[$user['id']],
                'level_log' => $user['level'],
                'datetime'  => ITime::getDateTime(),
            );

            // 当前uid的level奖励
            $levelBonus = $levelBonusConfig[$updateArr[$user['id']]];

            if ($levelBonus && $levelBonus['sec_stocks']) {

                // 当前uid应该获得的sec_stocks
                $levelSecStocks = $levelBonus['sec_stocks'] + $user['sec_stocks'];

                $secStocksData[$user['id']] = $levelSecStocks;

                $secStocksLog[] = array(
                    'user_id'   => $user['id'],
                    'value'     => $levelBonus['sec_stocks'],
                    'value_log' => $levelSecStocks,
                    'from_uid'  => $from_uid,
                    'datetime'  => ITime::getDateTime(),
                );
            }
        }

        // 写levelChange日志
        $levelLogObj = new IModel('level_upgrade_log');
        $levelLogObj->setData($levelLog);
        $levelLogObj->batchAdd();

        // 更新sec_stocks
        if ($secStocksData) $userObj->updateWithCase('sec_stocks', 'id', $secStocksData);

        // 记录sec_stocks日志
        if ($secStocksLog) (new IModel('sec_scocks_log'))->setData($secStocksLog)->batchAdd();

        return true;
    }

    /**
     * 记录账户余额表和更新balance
     *
     * @param array $updateArr 要更新的数组 key=>uid val=>val
     * @param int   $event     报单:11分享佣金,12level奖励,13分红,16agent奖励;
     *                         购物:14零售佣金,15返现佣金,17agent佣金,18订单分红;
     *                         其他:21提现手续费,22转出,23转入;
     * @param int   $from_uid  奖励来源uid
     * @param int   $from_oid  奖励来源oid
     * @return void
     */
    static function recordAccountLog($updateArr, $event, $from_uid, $from_oid = '')
    {
        if (!$updateArr || !is_array($updateArr)) return false;

        $userIds = array_keys($updateArr);
        $uidStr = implode(',', $userIds);

        // 要更新的users
        $query = new IQuery('user as u');
        $query->join = 'left join member as m on u.id = m.user_id';
        $query->where = 'u.id in (' . $uidStr . ')';
        $usersArr = $query->find();

        // 要更新的余额数组 key=>uid val=>newVal
        $balanceArr = array();
        // 要更新的account_log日志数组
        $logArr = array();

        // 要更新的税收数组
        $taxesArr = array();
        // 要更新的税收日志
        $taxesLogArr = array();

        // 要更新的重消数组
        $revisitArr = array();
        // 要更新的重消日志
        $revisitLogArr = array();

        foreach ($usersArr as $key => $user) {
            // 处理一下应得金额
            $oldBalance = $updateArr[$user['id']];
            $curBalance = self::processingReward($oldBalance);

            // 税收金额
            if ($curBalance['taxes'] > 0) {
                $taxesArr[$user['id']] = $curBalance['taxes'] + $user['taxes'];
                // taxes日志
                $taxesLogArr[] = array(
                    'user_id'    => $user['id'],
                    'time'       => ITime::getDateTime(),
                    'value'      => $curBalance['taxes'],
                    'value_log'  => $curBalance['taxes'] + $user['taxes'],
                    'from_uid'   => $from_uid,
                );
            }

            // 重复消费金额
            if ($curBalance['revisit'] > 0) {
                $revisitArr[$user['id']] = $curBalance['revisit'] + $user['revisit'];
                // revisit日志
                $revisitLogArr[] = array(
                    'user_id'    => $user['id'],
                    'time'       => ITime::getDateTime(),
                    'value'      => $curBalance['revisit'],
                    'value_log'  => $curBalance['revisit'] + $user['revisit'],
                    'from_uid'   => $from_uid,
                );
            }

            // 最终金额
            $finalBalance = $oldBalance - $curBalance['taxes'] - $curBalance['revisit'];

            // 要更新的余额
            $updateBalance = $finalBalance + $user['balance'];

            // 余额数组
            $balanceArr[$user['id']] = $updateBalance;

            // balance日志
            $logArr[] = array(
                'user_id'    => $user['id'],
                'event'      => $event,
                'time'       => ITime::getDateTime(),
                'amount'     => $finalBalance,
                'amount_log' => $updateBalance,
                'from_uid'   => $from_uid,
                'from_oid'   => $from_oid,
            );
        }

        // 数据库
        $userObj = new IModel('user');
        $memberObj = new IModel('member');
        $accountLogObj = new IModel('account_log');
        $taxesLogObj = new IModel('taxes_log');
        $revisitLogObj = new IModel('revisit_log');

        // 更新balance
        if ($balanceArr) $res = $memberObj->updateWithCase('balance', 'user_id', $balanceArr);
        // balance日志
        if ($res) $accountLogObj->setData($logArr)->batchAdd();

        // 更新税收
        if ($taxesArr) $taxesRes = $userObj->updateWithCase('taxes', 'id', $taxesArr);
        // 税收日志
        if ($taxesRes) $taxesLogObj->setData($taxesLogArr)->batchAdd();

        // 更新重复消费
        if ($revisitArr) $revisitRes = $userObj->updateWithCase('revisit', 'id', $revisitArr);
        // 重复消费日志
        if ($revisitRes) $revisitLogObj->setData($revisitLogArr)->batchAdd();

        return true;
    }

    /**
     * 加工奖励金额
     * 1.税收 10%
     * 2.积分账户 6%
     *
     * @param int    $reward 要处理的金额
     * @param string $type taxes || revisit
     * @return array taxes=>税收 revisit=>重复消费
     */
    static function processingReward($reward, $type = '')
    {
        $result = array(
            'taxes'   => round($reward / 10, 2),
            'revisit' => round($reward * 6 / 100, 2),
        );

        if ($result[$type]) return $result[$type];
        return $result;
    }

    /**
     * 个人中心相关
     * *********************************************************
     */

    /**
     * 根据用户ID获取用户分享二维码
     *
     * @param  int    $user_id 要获取的uid
     * @return string $user['share_qrcode] 二维码图片名称
     */
    static function getQRCodeByUserId($user_id)
    {
        $userObj = new IModel('user');
        $userRow = $userObj->getObj('id = ' . $user_id, 'share_qrcode');

        // 本身有就取自身的
        if ($userRow['share_qrcode']) {
            return $userRow['share_qrcode'];
        }

        $dir = './upload/qrcode/';
        $timeDir = date('Ymd') . '/';
        $fileName = $user_id . '_' . time() . '.png';
        if (!is_dir($dir)) {
            mkdir($dir, 0777);
        }
        if (!is_dir($dir . $timeDir)) {
            mkdir($dir . $timeDir, 0777);
        }

        $fileUrl = $dir . $timeDir . $fileName;

        // 没有就生成二维码图片
        $data = 'http://hzyh.ehanone.com/simple/reg/from_id/' . $user_id;
        $size = 12;
        QRcode::png($data, $fileUrl, QR_ECLEVEL_M, $size);

        $userObj->setData(array('share_qrcode' => $timeDir . $fileName));
        $userObj->update('id = ' . $user_id);

        return $timeDir . $fileName;
    }

    /**
     * 更新用户vip标识
     *
     * @param  int $user_id 要更新的uid
     * @param  int $amount  vip金额
     * @param  int $is_empty 是否空单
     * @return int | boolean false 成功返回更新的number 失败返回false
     */
    static function updateVipUserLeve($user_id, $amount, $is_empty = 0)
    {
        $amount = intval($amount);
        $vipConfig = self::vipStatusConfig();
        $where = $vipConfig[$amount];

        $data = array();
        if ($where && is_array($where)) {
            foreach ($where as $key => $value) {
                $data[$key] = $value;
            }
        }

        $data['is_empty'] = $is_empty;
        if ($data && is_array($data)) {
            return (new IModel('user'))->setData($data)->update('id = ' . $user_id);
        }

        return false;
    }

    /**
     * 创建vip订单
     *
     * @param  int $active_id 要激活的uid
     * @return int $order_id | array $error['msg'] 成功返回订单id 失败返回错误信息array['msg']
     */
    static function createVipOrder($active_id)
    {
        $user_id = IWeb::$app->getController()->user['user_id'];
        $amount  = IFilter::act(IReq::get('amount'), 'int');
        $payment = IFilter::act(IReq::get('payment'), 'int');

        if (!self::isVipConfig($amount)) return array('msg' => '金额不正确');

        $query = new IModel('vip_order');

        $data = array(
            'order_no'     => self::createVipOrderNum(),
            'user_id'      => $user_id,
            'active_id'    => $active_id,
            'pay_type'     => $payment,
            'create_time'  => ITime::getDateTime(),
            'order_amount' => $amount,
        );

        $query->setData($data);
        $order_id = $query->add();

        if ($order_id) return $order_id;
        return array('msg' => '订单生成失败');
    }

    /**
     * vip支付方法
     *
     * @param int $id 支付的订单id
     * @return boolean true | errorPage 支付成功返回true 失败跳转到错误页面
     */
    static function payVipOrder($id)
    {
        $user_id = IWeb::$app->getController()->user['user_id'];

        // 默认callback页面
        plugin::trigger('setCallback', '/ucenter/index');

        $paymentDB  = new IModel('payment');
        $paymentRow = $paymentDB->getObj('class_name = "balance" ');
        if (!$paymentRow) {
            IError::show(403, '余额支付方式不存在');
        }

        $memberObj = new IModel('member');
        $memberRow = $memberObj->getObj('user_id = ' . $user_id);

        if (empty($memberRow)) {
            IError::show(403, '用户信息不存在');
        }

        $orderObj = new IModel('vip_order');
        $orderRow = $orderObj->getObj('id = ' . $id . ' and pay_status = 0 and status = 1');
        if (!$orderRow) {
            IError::show(403, '订单号【' . $orderRow['order_no'] . '】已经被处理过，请查看订单状态');
        }

        // 可用余额 减去正在提现中的余额
        $free_balance = self::getUserFreeBalance($user_id);

        if ($free_balance < $orderRow['order_amount']) {
            $recharge = $orderRow['order_amount'] - $free_balance;

            plugin::trigger('setCallback', '/ucenter/online_recharge');
            IError::show(403, '余额不足请充值 ￥' . $recharge);
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
            IError::show(403, $logObj->error ? $logObj->error : '用户余额更新失败');
        }

        // 支付成功更新订单状态
        $orderObj->setData(array(
            'status'     => 2,
            'pay_status' => 1,
            'pay_time'   => ITime::getDateTime(),
        ));

        return $orderObj->update('id = ' . $id);
    }

    /**
     * 根据uid获取银行卡信息
     *
     * @param int $user_id
     * @return array $bankInfo
     */
    static function getBankInfo($user_id)
    {
        return (new IModel('bank_card'))->getObj('user_id =' . $user_id . ' and is_del = 0');
    }

    /**
     * 根据uid获取免提现额度
     *
     * @param int $user_id
     * @return int $free_balance
     */
    static function getFreeBalance($user_id)
    {
        $memberRow = (new IModel('member'))->getObj('user_id = ' . $user_id, 'free_balance');
        return $memberRow['free_balance'] > 0 ? $memberRow['free_balance'] : 0;
    }

    /**
     * 扣除服务费后实际金额
     *
     * @param int $user_id 要提现的uid
     * @param int $balance 要提现的金额
     * @return int $curBalance 扣除的免费额度
     */
    static function calcFinalBalance($user_id, $balance)
    {
        // 免费额度
        $free_balance = self::getFreeBalance($user_id);
        // 服务费千分比
        $serviceChangeConf = self::serviceChargeConf();

        // 要扣服务费的金额
        $serviceBalance = $balance - $free_balance;
        if ($serviceBalance > 0) {
            $updateFree = 0;
            // 扣除手续费
            $finalBalance = round($balance - ($serviceBalance * $serviceChangeConf / 1000), 2);
        } else {
            $updateFree = abs($serviceBalance);
        }

        if ($free_balance > 0) {
            // 更新免费额度
            $res = (new IModel('member'))->setData(array('free_balance' => $updateFree))->update('user_id=' . $user_id);
            // 记录日志
            $curBalance = $free_balance - $updateFree;
            $freeFinal = $free_balance - $curBalance;
            // 'free_balance' => array('cols' => array('admin_id','user_id','type','value','value_log','note')),
            if ($res) (new Log('db'))->write('free_balance', array('', $user_id, '1', $curBalance, $freeFinal, '提现申请扣除' . $curBalance . '免费额度'));
        }

        return round($curBalance, 2);
    }

    /**
     * 获取用户的可使用余额
     *
     * @param int $user_id
     * @return int $free_balance
     */
    static function getUserFreeBalance($user_id)
    {
        // 总余额
        $allBalance = (new IModel('member'))->getObj('user_id = ' . $user_id, 'balance');
        // 已经申请未处理的余额
        $curBalance = (new IModel('withdraw'))->query('user_id= ' . $user_id . ' and is_del = 0 and status = 0', 'sum(amount) as c_balance');

        // 可以余额
        $free_balance = $allBalance['balance'] - $curBalance[0]['c_balance'];
        return round($free_balance, 2);
    }

    /**
     * 提现失败增加免费额度
     *
     * @param int $withdraw_id 提现订单id
     * @return void
     */
    static function addFreeBalance($withdraw_id)
    {
        $withdrawRow = (new IModel('withdraw'))->getObj('id = ' . $withdraw_id);
        if (!$withdrawRow || !$withdrawRow['free_amount']) return false;

        $user_id = $withdrawRow['user_id'];
        $memberDB = new IModel('member');
        $memberRow = $memberDB->getObj('user_id = ' . $user_id);

        // 更新用户免费额度
        $finalBalance = $memberRow['free_balance'] + $withdrawRow['free_amount'];
        $res = $memberDB->setData(array('free_balance' => $finalBalance))->update('user_id = ' . $user_id);

        // 记录日志
        $logArr = array(
            IWeb::$app->getController()->admin['admin_id'],
            $user_id,
            '0',
            $withdrawRow['free_amount'],
            $finalBalance,
            '提现失败增加' . $withdrawRow['free_amount'] . '免费额度',
            $withdraw_id,
        );
        if ($res) return (new Log('db'))->write('free_balance', $logArr);
    }

    /**
     * 验证支付密码
     *
     * @param int    $user_id 当前udid
     * @param string $passwd  要验证的支付密码
     * @return boolean
     */
    static function validateTranPasswd($user_id, $passwd)
    {
        $userRow = (new IModel('user'))->getObj('id = ' . $user_id, 'tran_password');
        return $userRow['tran_password'] == md5($passwd);
    }

    /**
     * @brief 统计vip用户的数据
     * @param string $start 开始日期 Y-m-d
     * @param string $end   结束日期 Y-m-d
     * @return array array(level => 人数);
     */
    public static function userLevelCount($start = '', $end = '')
    {
        $where = ' 1 ';
        if ($start) $where .= ' and check_time >= "' . $start . ' 00:00:00"';
        if ($end) $where .= ' and check_time < "' . $end . ' 00:00:00"';

        $db = new IQuery('user');
        $db->fields = 'count(level) as c_level, `level`';
        $db->where  = $where . ' and level >= 11';
        $db->group  = 'level';

        return $db->find();
    }

    /**
     * 统计用户奖励总额
     * @param int $user_id 当前uid
     */
    public static function getUserCount($user_id)
    {
        $db         = new IQuery('account_log');
        $db->where  = 'user_id = ' . $user_id . ' and event in(11,12,13,14,15,16,17,18) and type = 0';
        $db->fields = 'sum(amount) as c_amount, event';
        $db->group  = 'event';

        $res = $db->find();

        $conf = [
            11 => 'invited',
            12 => 'manage',
            13 => 'divi',
            14 => 'sale',
            15 => 'cash',
            16 => 'sub',
            17 => 'share',
        ];

        $result = [];
        foreach ($conf as $value) {
            $result[$value] = '0.00';
        }

        foreach ($res as $val) {
            $result[$conf[$val['event']]] = $val['c_amount'];
        }

        return $result;
    }

    // vip订单支付成功回调
    static function vipOrderCallback($order_id)
    {
        $orderDB  = new IModel('order');
        $orderRow = $orderDB->getObj('id = ' . $order_id . ' and pay_status = 1', 'id, pay_status, pay_time, status, vip_order_id');
        if (!$orderRow) return false;

        $where = 'id = ' . $orderRow['vip_order_id'];
        $vipOrderDB  = new IModel('vip_order');
        $vipOrderRow = $vipOrderDB->getObj($where . ' and pay_status = 0');

        if (!$vipOrderRow) return false;

        $vipOrderDB->setData([
            'status'     => $orderRow['status'],
            'pay_time'   => $orderRow['pay_time'],
            'pay_status' => $orderRow['pay_status'],
        ])->update($where);

        // 更新激活用户状态
        self::updateVipUserLeve($vipOrderRow['active_id'], $vipOrderRow['order_amount']);

        // 走self方法
        self::init($vipOrderRow['active_id']);
    }

    // vip订单回调-最新
    static function vipOrderServerCallback($order_id)
    {
        $orderDB  = new IModel('order');
        $orderRow = $orderDB->getObj('id = ' . $order_id . ' and pay_status = 1');
        if (!$orderRow) return false;

        // 生成会员的订单
        $query = new IModel('vip_order');

        $data = array(
            'order_no'     => Team::createVipOrderNum(),
            'user_id'      => $orderRow['user_id'],
            'active_id'    => $orderRow['active_uid'],
            'pay_type'     => $orderRow['pay_type'],
            'create_time'  => ITime::getDateTime(),
            'order_amount' => $orderRow['real_amount'],
            'order_id'     => $order_id,
            'status'     => $orderRow['status'],
            'pay_time'   => $orderRow['pay_time'],
            'pay_status' => $orderRow['pay_status'],
        );

        $query->setData($data);
        $order_id = $query->add();

        // 更新激活用户状态
        self::updateVipUserLeve($orderRow['active_uid'], $orderRow['real_amount']);

        // 走self方法
        self::init($orderRow['active_uid']);
    }

    // 返回是否已经存在vip订单并且支付完成
    static function isVipOrder($active_id)
    {
        $sql = new IModel('order');
        $orderRow = $sql->getObj('pay_status = 1 and active_uid = ' . $active_id, 'id');
        return $orderRow ? true : false;
    }
}
