<?php

/**
 * 随便打相关接口
 */
class anyCall
{
    function __construct()
    {
        $configData = new Config('site_config');

        $ShareId  = $configData->shareID;
        $ParentId = $configData->parentID;

        if (!$ParentId || !$ShareId) throw new Exception("API参数未填写到后台", 10009);

        $uid = IWeb::$app->getController()->user['user_id'];

        if (!$uid) die(JSON::encode(['status' => 'token30401', 'error' => 'userToken不存在']));

        $this->uid      = $uid;
        $this->ShareId  = $ShareId;
        $this->ParentId = $ParentId;
    }

    // 获取验证码
    public function getCallCode()
    {
        $mobile = IFilter::act(IReq::get('mobile'));
        if (!$mobile || !IValidate::mobi($mobile)) throw new Exception("手机号不正确", 10009);

        $postUrl = 'SbdVoip/login/smsInfo';
        $postData = [
            Mobile   => $mobile,
            ParentId => $this->ParentId,
        ];
        return $this->_run($postUrl, $postData);
    }

    // 注册 默认账号密码是一样的
    public function registerCall()
    {
        $mobile = IFilter::act(IReq::get('mobile'));
        if (!$mobile || !IValidate::mobi($mobile)) throw new Exception("手机号不正确", 10009);

        $postUrl = 'SbdVoip/login/shareRegister';
        $postData = [
            Mobile  => $mobile,
            ShareId => $this->ShareId,
        ];

        $sql = new IModel('call_register');
        $sql->setData([
            user_id => $this->uid,
            mobile  => $mobile,
            time    => ITime::getDateTime(),
        ])->add();

        return $this->_run($postUrl, $postData);
    }

    // 登录
    public function loginCall()
    {
        $mobile = IFilter::act(IReq::get('mobile'));
        if (!$mobile || !IValidate::mobi($mobile)) throw new Exception("手机号不正确", 10009);

        $Password = IFilter::act(IReq::get('password'));
        if (!$Password) $this->setError('密码不能为空');

        $postUrl = 'SbdVoip/login/wxLogin';
        $postData = [
            Mobile   => $mobile,
            Password => $Password,
            ParentId => $this->ParentId,
        ];
        return $this->_run($postUrl, $postData);
    }

    // 获取token
    public function getCallToken()
    {
        $mobile = IFilter::act(IReq::get('mobile'));
        if (!$mobile || !IValidate::mobi($mobile)) throw new Exception("手机号不正确", 10009);

        $postUrl = 'SbdVoip/sign/wxGetToken';
        $postData = [
            Mobile   => $mobile,
            ParentId => $this->ParentId,
        ];
        return $this->_run($postUrl, $postData);
    }

    // 打电话给用户
    public function callUser()
    {
        // 对方号码
        $answerer = IFilter::act(IReq::get('answerer'));
        // 通讯录名称
        $answer_name = IFilter::act(IReq::get('answer_name'));

        // 自己的号码
        $caller = IFilter::act(IReq::get('caller'));
        if (!$caller || !IValidate::mobi($caller) || !$answerer || !IValidate::mobi($answerer)) throw new Exception("手机号不正确", 10009);

        if ($answerer == $caller) throw new Exception("不能打电话给自己", 10009);

        // token
        $token = IFilter::act(IReq::get('token'));
        if (!$token) throw new Exception("token不能为空", 10009);

        $postUrl = 'SbdVoip/call/wxCallBack';
        $postData = [
            Caller   => $caller,
            Calle164 => $answerer,
        ];

        $sql = new IModel('call_log');
        $sql->setData([
            user_id => $this->uid,
            caller  => $caller,
            answer  => $answerer,
            time    => ITime::getDateTime(),
            answer_name => $answer_name,
        ])->add();

        $header = ['Authorization:' . $token];
        return $this->_run($postUrl, $postData, $header);
    }

    // 充值
    public function rechangeCall()
    {
        // 手机号
        $mobile = IFilter::act(IReq::get('mobile'));
        if (!$mobile || !IValidate::mobi($mobile)) throw new Exception("手机号不正确", 10009);

        // 卡号
        $cardno = IFilter::act(IReq::get('cardno'));
        if (!$cardno) throw new Exception("卡号不正确", 10009);

        // 密码
        $password = IFilter::act(IReq::get('password'));
        if (!$password) throw new Exception("密码不正确", 10009);

        // token
        $token = IFilter::act(IReq::get('token'));
        if (!$token) throw new Exception("token不能为空", 10009);

        $postUrl = 'SbdVoip/recharge/wxPayInfo';
        $postData = [
            Cardno   => $cardno,
            Pwd      => $password,
            Mobile   => $mobile,
            ParentId => $this->ParentId,
        ];

        $sql = new IModel('call_rechange');
        $sql->setData([
            user_id => $this->uid,
            mobile  => $mobile,
            card_no => $cardno,
            pass    => $password,
            time    => ITime::getDateTime(),
        ])->add();

        $header = ['Authorization:' . $token];
        return $this->_run($postUrl, $postData, $header);
    }

    // 获取余额信息
    public function getCallBalance()
    {
        // token
        $token = IFilter::act(IReq::get('token'));
        if (!$token) throw new Exception("token不能为空", 10009);

        $postUrl = 'SbdVoip/userInfo/wxGetBlance';
        $header = ['Authorization:' . $token];
        return $this->_run($postUrl, [], $header);
    }

    // 重置密码
    public function resetCallPass()
    {
        $token = IFilter::act(IReq::get('token'));
        if (!$token) throw new Exception("token不能为空", 10009);

        // 新密码
        $newPass = IFilter::act(IReq::get('newPass'));
        if (!$newPass) throw new Exception("密码不正确", 10009);

        $postData = [
            Theme => $newPass,
        ];

        $postUrl = 'SbdVoip/login/wxUpdatePwd';
        $header = ['Authorization:' . $token];
        return $this->_run($postUrl, $postData, $header);
    }

    // 签到
    public function checkCallIn()
    {
        $uid   = IFilter::act(IReq::get('uid'));
        $times = IFilter::act(IReq::get('times'));
        if (!$uid || !$times) throw new Exception("参数不完整", 10009);

        $postData = [
            subid => $uid,
            continuity => $times,
        ];

        $postUrl = 'SbdVoip/admin/addWeeks';
        return $this->_run($postUrl, $postData);
    }

    // 通话记录
    public function getCallLog()
    {
        $mobile = IFilter::act(IReq::get('mobile'));
        if (!$mobile || !IValidate::mobi($mobile)) throw new Exception("手机号不正确", 10009);

        $postUrl = 'SbdVoip/call/wxCallLog';
        $postData = [
            Caller   => $mobile,
            ParentID => $this->ParentId,
        ];

        return $this->_run($postUrl, $postData);
    }

    // 本地通话记录
    public function getLocalCallLog()
    {
        $mobile = IFilter::act(IReq::get('mobile'));
        if (!$mobile || !IValidate::mobi($mobile)) throw new Exception("手机号不正确", 10009);

        $limit  = IReq::get('limit') ? IFilter::act(IReq::get('limit'), 'int') : 10;
        $page   = IReq::get('page') ? IFilter::act(IReq::get('page'), 'int') : 1;
        $query  = new IQuery('call_log');
        $query->where = "caller = '" . $mobile . "'";
        $query->order = 'id desc';
        $query->page  = $page;
        $query->pagesize = $limit;
        return $query;
    }

    // 请求
    private function _run($postUrl, $postData = [], $header = '')
    {
        $baseUrl = 'https://sbd.sbdznkj.com/';
        $reqUrl = $baseUrl . $postUrl;

        $reqData = Api::httpRequest($reqUrl, $postData, $header);

        // 只处理errorCode为2000的
        if ($reqData['errorCode'] == 2000) return $reqData;

        // 处理签到的返回值
        else if (isset($reqData['flag']) && $postUrl == 'SbdVoip/admin/addWeeks') {

            if ($reqData['flag'] == 0) return 'success';

            $flagRes = [
                '0'    => 'success',
                '-1'   => '非法请求',
                '-2'   => '已签到',
                '-3'   => '签到失败,请重试',
                '-100' => '过期账户无法签到',
            ];

            throw new Exception($flagRes[$reqData['flag']], 10009);
        }

        // 不满足上面的一律拉黑处理
        throw new Exception($reqData['data'], 10009);
    }
}
