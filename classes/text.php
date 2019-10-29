<?php

class Text
{
    /**
     * 根据用户等级显示文字
     *
     * @param int $level
     * @return string $text
     */
    static function levelShow($level)
    {
        $textConfig = array(
            '0'  => '注册会员',
            '11' => 'vip会员',
            '12' => 'vip会员',
            '13' => 'vip会员',
            '21' => 'vip会员',
            '22' => 'vip会员',
            '23' => 'vip会员',
            '31' => 'vip会员',
            '32' => 'vip会员',
        );

        return $textConfig[$level];
    }

    /**
     * 根据等级显示代理商
     *
     * @param int $agent_level
     * @return string $text
     */
    static function agentShow($agent_level)
    {
        $textConfig = array(
            '1' => '社区店',
            '2' => '标准店',
            '3' => '旗舰店',
        );

        return isset($textConfig[$agent_level]) ? $textConfig[$agent_level] : '';
    }
}
