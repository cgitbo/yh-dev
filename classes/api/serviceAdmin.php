<?php

class ServiceAdmin
{
    // admin接口权限校验
    function __construct()
    {
        if (!IWeb::$app->getController()->admin['admin_id']) throw new IException("API 403 Error");
    }
    
    // 后台--股权日志
    public function getScocksLogList()
    {
        $page   = IReq::get('page') ? IFilter::act(IReq::get('page'), 'int') : 1;
        $where  = Util::search(IReq::get('search'));

        $sql = new IQuery('sec_scocks_log as l');
        $sql->join = 'left join user as u on u.id = l.user_id';
        $sql->where = $where . ' and l.if_del = 0';
        $sql->page = $page;
        $sql->order = "l.id desc";
        return $sql;
    }

    // 后台--重消日志
    public function getRevisitLogList()
    {
        $page   = IReq::get('page') ? IFilter::act(IReq::get('page'), 'int') : 1;
        $where  = Util::search(IReq::get('search'));

        $sql = new IQuery('revisit_log as l');
        $sql->join = 'left join user as u on u.id = l.user_id';
        $sql->where = $where . ' and l.if_del = 0';
        $sql->page = $page;
        $sql->order = "l.id desc";
        return $sql;
    }

    // 后台获取用户升级列表
    public function getLevelUpgradeList()
    {
        $where  = Util::search(IReq::get('search'));
        $page   = IReq::get('page') ? IFilter::act(IReq::get('page'), 'int') : 1;
        $query = new IQuery('level_upgrade_log as l');
        $query->join = 'left join user as u on l.user_id = u.id and l.if_del = 0';
        $query->where = $where;
        $query->page = $page;
        $query->order = 'l.id desc';
        return $query;
    }

    // 后台获取用户邀请统计
    public function getUserInvitedList()
    {
        $sort = IFilter::act(IReq::get('sort'));
        $sort = $sort ? $sort : 'invited_sum';

        $sortConf = ['invited_sum', 'team_sum', 'team_vip', 'team_new', 'team_empty'];

        $where = Util::search(IReq::get('search'));
        $tree = new Tree($where);
        $res = $tree->getTeamSum();

        if ($res && is_array($res) && in_array($sort, $sortConf)) {
            array_multisort(array_column($res, $sort), SORT_DESC, $res);
            return $res;
        }
        return [];
    }

    // 后台--拨比统计
    public function getDialOutRate()
    {
        $where  = Util::search(IReq::get('search'));

        // 新的报单金额配置
        $amountList = array_keys(Team::vipStatusConfig());

        // 所有报单人数和报单金额
        $userDB = new IModel('user');

        // 统计每个金额的总数
        foreach ($amountList as $amount) {
            $data['user'][$amount] = $userDB->query($where . ' and level >=11 and is_empty = 0 and active_amount = ' . $amount, 'count(id) as countUser, sum(active_amount) as countAmount')[0];
        }

        // 统计总人数和总金额
        foreach ($data['user'] as $value) {
            $data['allUser'] += $value['countUser'];
            $data['allAmount'] += $value['countAmount'];
        }

        // 替换查询条件给下面查询使用
        $where = str_replace("check_time", "time", $where);

        // 所有奖励金额
        $logDB = new IModel('account_log');
        $logRow = $logDB->query($where . ' and event in (11,12,13,16)', 'sum(amount) as allBonus');
        $data['allBonus'] = $logRow[0] ? $logRow[0]['allBonus'] : 0;

        // 所有vip消费
        // vip消费是奖励金额6% 税10% 所以实际奖励是84%
        $data['allRevisit'] = round($data['allBonus'] / 84 * 6, 2);

        // 3900成本按400算 4.9? 6.9? 8.9?
        $productCost = $data[3900]['countUser'] * 400;

        // vip消费按0.13算 
        $revisitCost = $data['allRevisit'] * 13 / 100;

        // 总成本 = 奖励 + 产品成本 + 重消成本
        $allCost = $data['allBonus'] + $productCost + $revisitCost;

        // 拨出比例 = 总奖励 / 总收入 * 100
        $data['percent'] = round($allCost / $data['allAmount'] * 100, 2);

        return $data;
    }

    // 后台实名列表
    public function getRealNameList()
    {
        $where  = Util::search(IReq::get('search'));
        $page   = IReq::get('page') ? IFilter::act(IReq::get('page'), 'int') : 1;

        $query        = new IQuery('real_name as r');
        $query->join  = 'left join user as u on r.user_id = u.id';
        $query->where = $where;
        $query->page  = $page;
        $query->fields = 'r.*, u.username';
        $query->order = 'r.id desc';
        return $query;
    }
}
