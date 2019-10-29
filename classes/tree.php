<?php
class Tree
{
    // 树结构
    private $tree = [];

    // 所有用户数据 uid => user
    private $allUserArr = [];

    // 当前条件用户数据 uid => user
    private $curUserArr = [];

    // 构造函数
    function __construct($where = '1')
    {
        $sql = new IQuery('user as u');
        $sql->join = 'left join member as m on u.id = m.user_id';
        $sql->where = 'level >= 11';
        $sql->fields = 'id, username, true_name, parent_id, level, is_empty, is_agent, active_amount, check_time';
        $allUserArr = $sql->find();

        // 当前条件的用户
        $sql->where = $where . ' and level >= 11';
        $curUserArr = $sql->find();

        $userArr = [];
        // 将用户id作为数组key,并创建children单元
        foreach ($allUserArr as $key => $user) {
            $userArr[$user['id']] = $user;
            $userArr[$user['id']]['children'] = array();
            $userArr[$user['id']]['invited_sum'] = 0;
            $userArr[$user['id']]['team_sum'] = 0;
            $userArr[$user['id']]['team_vip'] = 0;
            $userArr[$user['id']]['team_new'] = 0;
            $userArr[$user['id']]['team_empty'] = 0;
            if ($user['id'] == 0) unset($allUserArr[$key]);
        }

        $curUser = [];
        foreach ($curUserArr as $k => $value) {
            $curUser[$value['id']] = $value;
            if ($value['id'] == 0) unset($curUserArr[$k]);
        }

        $this->allUserArr = $userArr;
        $this->curUserArr = $curUser;
    }

    // 打印树结构
    public function printTree($id = null)
    {
        return $this->getTree($this->getTeamSum($id));
    }

    // 得到树结构
    private function getTree($users)
    {
        $tree = $users;

        // 利用引用，将每个用户添加到父类children数组中，这样一次遍历即可形成树形结构。
        foreach ($tree as $key => $item) {
            if ($item['parent_id'] != 0) {
                // 注意：此处必须传引用否则结果不对
                $tree[$item['parent_id']]['children'][] = &$tree[$key];
                // 如果children为空，则删除该children元素（可选）
                // if ($tree[$key]['children'] == null) unset($tree[$key]['children']);
            }
        }

        // 删除无用的非根节点数据
        foreach ($tree as $key => $user) {
            if ($user['parent_id'] != 0) unset($tree[$key]);
        }

        return $this->tree = $tree;
    }

    /**
     * 获取teamSum
     *
     * @param int $id 指定获取某一个人的
     * @return void
     */
    public function getTeamSum($id = null)
    {
        $userArr = $this->allUserArr;
        $curUserArr = $this->curUserArr;

        $amountArr = array_keys(Team::vipStatusConfig());

        foreach ($curUserArr as $user) {
            $parent_id = $user['parent_id'];
            $isAgent = false;

            if ($userArr[$parent_id]) $userArr[$parent_id]['invited_sum'] += 1;

            while (true) {
                if (!$parent_id) break;
                if ($parent_id == $userArr[$parent_id]['id']) {
                    if (in_array($user['active_amount'], $amountArr)) {
                        $userArr[$parent_id]['team_vip'] += 1;

                        if (!$user['is_agent'] && !$isAgent) $userArr[$parent_id]['team_new'] += 1;
                        
                        if ($user['is_empty']) $userArr[$parent_id]['team_empty'] += 1;
                    }
                    $userArr[$parent_id]['team_sum'] += 1;
                }

                if ($userArr[$parent_id]['is_agent']) $isAgent = true;

                $parent_id = $userArr[$parent_id]['parent_id'];
            }
        }

        if ($id) return $userArr[$id];
        return $userArr;
    }
}
