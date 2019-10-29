<?php
class Temp
{
    /**
     * 临时方法相关
     * 没卵用
     */

    /**
     * 老的用户信息根据parent移到库里
     *
     * @return void
     */
    static function moveOldData()
    {
        $oldObj        = new IQuery('wz_user as u');
        $oldObj->join  = 'left join wz_member as m on u.id = m.user_id';
        $oldObj->where = 'u.level >= 11';
        $oldRow        = $oldObj->find();

        $userObj   = new IModel('user');
        $memberObj = new IModel('member');

        $curIndex = 1;
        foreach ($oldRow as $key => $user) {

            if ($user['id'] == $curIndex) {
                $userData = array(
                    'username'      => $user['username'],
                    'password'      => $user['password'],
                    'head_ico'      => $user['head_ico'],
                    'parent_id'     => $user['parent_id'], // 父级就是邀请人
                    'active_amount' => $user['money'],
                    'check_time'    => $user['check_time'],
                    'tran_password' => $user['tran_password'],
                    'username'      => $user['username'],
                );

                $memberData = array(
                    'user_id'      => $user['user_id'],
                    'true_name'    => $user['true_name'],
                    'telephone'    => $user['telephone'],
                    'mobile'       => $user['mobile'],
                    'area'         => $user['area'],
                    'contact_addr' => $user['contact_addr'],
                    'qq'           => $user['qq'],
                    'sex'          => $user['sex'],
                    'birthday'     => $user['birthday'],
                    'group_id'     => $user['group_id'],
                    'exp'          => $user['exp'],
                    'point'        => $user['point'],
                    'message_ids'  => $user['message_ids'],
                    'time'         => $user['time'],
                    'zip'          => $user['zip'],
                    'status'       => $user['status'],
                    'prop'         => $user['prop'],
                    'balance'      => $user['balance'],
                    'custom'       => $user['custom'],
                    'email'        => $user['email'],
                );

                $userObj->setData($userData);
                $userObj->add();

                $memberObj->setData($memberData);
                $memberObj->add();
                $curIndex++;
            } else {
                while (true) {
                    if ($user['id'] == $curIndex) {
                        $userData = array(
                            'username'      => $user['username'],
                            'password'      => $user['password'],
                            'head_ico'      => $user['head_ico'],
                            'parent_id'     => $user['parent_id'], // 父级就是邀请人
                            'active_amount' => $user['money'],
                            'check_time'    => $user['check_time'],
                            'tran_password' => $user['tran_password'],
                            'username'      => $user['username'],
                        );

                        $memberData = array(
                            'user_id'      => $user['user_id'],
                            'true_name'    => $user['true_name'],
                            'telephone'    => $user['telephone'],
                            'mobile'       => $user['mobile'],
                            'area'         => $user['area'],
                            'contact_addr' => $user['contact_addr'],
                            'qq'           => $user['qq'],
                            'sex'          => $user['sex'],
                            'birthday'     => $user['birthday'],
                            'group_id'     => $user['group_id'],
                            'exp'          => $user['exp'],
                            'point'        => $user['point'],
                            'message_ids'  => $user['message_ids'],
                            'time'         => $user['time'],
                            'zip'          => $user['zip'],
                            'status'       => $user['status'],
                            'prop'         => $user['prop'],
                            'balance'      => $user['balance'],
                            'custom'       => $user['custom'],
                            'email'        => $user['email'],
                        );

                        $userObj->setData($userData);
                        $userObj->add();

                        $memberObj->setData($memberData);
                        $memberObj->add();
                        $curIndex++;
                        break;
                    } else {
                        $userData = array(
                            'username' => 'null_' . $curIndex
                        );
                        $memberData = array(
                            'user_id' => $curIndex
                        );

                        $userObj->setData($userData);
                        $userObj->add();

                        $memberObj->setData($memberData);
                        $memberObj->add();
                        $curIndex++;
                    }
                }
            }
        }
        die;
    }

    /**
     * 原来的point/5变成revisit
     *
     * @return void
     */
    static function movePointToRevisit()
    {
        $oldObj        = new IQuery('wz_user as u');
        $oldObj->join  = 'left join wz_member as m on u.id = m.user_id';
        $oldObj->where = '';
        $oldRow        = $oldObj->find();

        $userObj   = new IModel('user');
        $memberObj = new IModel('member');

        $updateArr = array();
        foreach ($oldRow as $key => $user) {
            if ($user['point'] > 0) {
                $revisit = $user['point'] / 5;
                if ($revisit > 0) {
                    $updateArr[$user['id']] = $revisit;

                    // revisit日志
                    $revisitLogArr[] = array(
                        'user_id'   => $user['id'],
                        'time'      => ITime::getDateTime(),
                        'value'     => $revisit,
                        'value_log' => $revisit,
                        'note'      => '系统自动将积分' . $user['point'] . '转换为' . $revisit,
                    );
                }
            }
        }

        $mRes = $memberObj->setData(array('point' => 0))->update('user_id > 0');

        $rRes = $userObj->updateWithCase('revisit', 'id', $updateArr);

        if ($rRes) $lRes = (new IModel('revisit_log'))->setData($revisitLogArr)->batchAdd();

        var_dump('member-' . $mRes);
        var_dump('revisit-' . $rRes);
        var_dump('log-' . $lRes);
        die;
    }

    /**
     * 计算所有uid的team_sum
     *
     * @return void
     */
    static function updateTeamSum()
    {
        $userArr = Team::getFixUserArr();

        foreach ($userArr as $uid => $user) {
            $parent_id = $user['parent_id'];

            while (true) {
                if (!$parent_id) break;

                if ($parent_id == $userArr[$parent_id]['id']) {
                    $userArr[$parent_id]['childrens'][$user['id']] = $user;
                }

                $parent_id = $userArr[$parent_id]['parent_id'];
            }
        }

        $updateArr = array();
        foreach ($userArr as $uid => $user) {
            $count = count($user['childrens']);
            if ($count > 0) $updateArr[$uid] = $count;
        }

        $res = (new IModel('user'))->updateWithCase('team_sum', 'id', $updateArr);

        var_dump($res);
        die;
    }

    /**
     * 生成老的uid对应balance的log
     *
     * @return void
     */
    static function addOldBalanceLog()
    {
        $userArr = self::getFixUserArr(' m.balance > 0 ');

        foreach ($userArr as $uid => $user) {
            // balance日志
            $logArr[] = array(
                'user_id'    => $uid,
                'event'      => 1,
                'time'       => ITime::getDateTime(),
                'amount'     => $user['balance'],
                'amount_log' => $user['balance'],
            );
        }

        // balance日志
        $accountLogObj = new IModel('account_log');
        $res = $accountLogObj->setData($logArr)->batchAdd();

        var_dump($res);
        die;
    }

    /**
     * 添加邀请人sec奖励
     *
     * @return void
     */
    static function addInvitedSec()
    {
        $userDB = new IModel('user');

        $userArr = $userDB->query('level >= 11', 'id, parent_id, active_amount, sec_stocks', 'id desc');

        $users3900  = $userDB->query('level >= 11 and is_empty = 0 and active_amount = 3900', 'id, parent_id, active_amount, sec_stocks', 'id desc');

        $users49000 = $userDB->query('level >= 11 and is_empty = 0 and active_amount = 49000', 'id, parent_id, active_amount, sec_stocks', 'id desc');

        $users69000 = $userDB->query('level >= 11 and is_empty = 0 and active_amount = 69000', 'id, parent_id, active_amount, sec_stocks', 'id desc');

        $users89000 = $userDB->query('level >= 11 and is_empty = 0 and active_amount = 89000', 'id, parent_id, active_amount, sec_stocks', 'id desc');

        $allUsers = array_merge($users3900, $users49000, $users69000, $users89000);

        $fixUsers = [];
        foreach ($userArr as $value) {
            $fixUsers[$value['id']] = $value;
        }

        $updateArr = [];
        $secStocksLog = [];
        foreach ($allUsers as $user) {
            $parent_id = $user['parent_id'];
            if ($parent_id) {
                $newSec = $fixUsers[$parent_id]['sec_stocks'] + 200;
                $updateArr[$parent_id] = $newSec;
                $secStocksLog[] = array(
                    'user_id'   => $parent_id,
                    'value'     => 200,
                    'value_log' => $newSec,
                    'event'     => 1,
                    'from_uid'  => $user['id'],
                    'datetime'  => ITime::getDateTime(),
                );
                $fixUsers[$parent_id]['sec_stocks'] = $newSec;
            }
        }

        // 更新sec_stocks
        if ($updateArr) $res = $userDB->updateWithCase('sec_stocks', 'id', $updateArr);

        // 记录sec_stocks日志
        if ($res) (new IModel('sec_scocks_log'))->setData($secStocksLog)->batchAdd();
        
        return $res;
    }
}
