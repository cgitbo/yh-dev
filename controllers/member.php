<?php
/**
 * @brief 会员模块
 * @class Member
 * @note  后台
 */
class Member extends IController implements adminAuthorization
{
	public $checkRight  = 'all';
    public $layout='admin';
	private $data = array();

	function init()
	{

	}

	/**
	 * @brief 添加会员
	 */
	function member_edit()
	{
		$uid  = IFilter::act(IReq::get('uid'),'int');
		$userData = array();

		//编辑会员信息读取会员信息
		if($uid)
		{
			$userData = Api::run('getMemberInfo',$uid);
			if(!$userData)
			{
				$this->member_list();
				Util::showMessage("没有找到相关记录！");
				exit;
			}
			// 获取parent信息
			if($userData['parent_id'])
			{
				$parentData = Api::run('getMemberInfo',$userData['parent_id']);
				if($parentData) $userData['parent'] = $parentData;
			}
		}
		$this->setRenderData(array('userData' => $userData));
		$this->redirect('member_edit');
	}

	//保存会员信息
	function member_save()
	{
		$user_id    = IFilter::act(IReq::get('user_id'),'int');
		$user_name  = IFilter::act(IReq::get('username'));
		$email      = IFilter::act(IReq::get('email'));
		$password   = IReq::get('password');
		$repassword = IReq::get('repassword');
		$group_id   = IFilter::act(IReq::get('group_id'),'int');
		$truename   = IFilter::act(IReq::get('true_name'));
		$sex        = IFilter::act(IReq::get('sex'),'int');
		$telephone  = IFilter::act(IReq::get('telephone'));
		$mobile     = IFilter::act(IReq::get('mobile'));
		$province   = IFilter::act(IReq::get('province'),'int');
		$city       = IFilter::act(IReq::get('city'),'int');
		$area       = IFilter::act(IReq::get('area'),'int');
		$contact_addr = IFilter::act(IReq::get('contact_addr'));
		$zip        = IFilter::act(IReq::get('zip'));
		$qq         = IFilter::act(IReq::get('qq'));
		$exp        = IFilter::act(IReq::get('exp'),'int');
		$point      = IFilter::act(IReq::get('point'),'int');
		$status     = IFilter::act(IReq::get('status'),'int');

		$level         = IFilter::act(IReq::get('level'),'int');
		$parent_name   = IFilter::act(IReq::get('parent_name'));
		$active_amount = 3900;

		$re_real_name  = IFilter::act(IReq::get('re_real_name'));
		$is_bonus      = IFilter::act(IReq::get('is_bonus'));

		$_POST['area'] = "";
		if($province && $city && $area)
		{
			$_POST['area'] = ",{$province},{$city},{$area},";
		}

		if(!$user_id && $password == '')
		{
			$this->setError('请输入密码！');
		}

		if($password != $repassword)
		{
			$this->setError('两次输入的密码不一致！');
		}

		//创建会员操作类
		$userDB   = new IModel("user");
		$memberDB = new IModel("member");

		if($userDB->getObj("username='".$user_name."' and id != ".$user_id))
		{
			$this->setError('用户名重复');
		}

		if($email && $memberDB->getObj("email='".$email."' and user_id != ".$user_id))
		{
			$this->setError('邮箱重复');
		}

		// 不校验手机号码重复
		// if($mobile && $memberDB->getObj("mobile='".$mobile."' and user_id != ".$user_id))
		// {
		// 	$this->setError('手机号码重复');
		// }

		// parent验证
		if($parent_name)
		{
			// 得到parentid
			$parentId = Team::validateParentInfo('',$parent_name);
			if(is_array($parentId))
			{
				$this->setError($parentId['msg']);
			}
		}

		//操作失败表单回填
		if($errorMsg = $this->getError())
		{
			$this->setRenderData(array('userData' => $_POST));
			$this->redirect('member_edit',false);
			Util::showMessage($errorMsg);
		}

		$member = array(
			'email'        => $email,
			'true_name'    => $truename,
			'telephone'    => $telephone,
			'mobile'       => $mobile,
			'area'         => $_POST['area'],
			'contact_addr' => $contact_addr,
			'qq'           => $qq,
			'sex'          => $sex,
			'zip'          => $zip,
			'exp'          => $exp,
			'point'        => $point,
			'group_id'     => $group_id,
			'status'       => $status,
		);

		//添加新会员
		if(!$user_id)
		{
			$user = array(
				'username' => $user_name,
				'password' => md5($password),
				'level'    => $level,
				'parent_id'=> $parentId,
				'is_bonus' => $is_bonus,
			);
			// 会员
			if(Team::isVipByUserLevel($level))
			{
				$user['active_amount'] = $active_amount;
			}
			$userDB->setData($user);
			$user_id = $userDB->add();

			$member['user_id'] = $user_id;
			$member['time']    = ITime::getDateTime();

			$memberDB->setData($member);
			$memberDB->add();

			// 后台添加走team方法
			Team::init($user_id);
		}
		//编辑会员
		else
		{
			// 先查到修改前用户信息
			$user_info = $userDB->getObj('id = ' . $user_id);

			$user = array(
				'username' => $user_name,
				'level'    => $level,
				'is_bonus' => $is_bonus,
			);
			//修改密码
			if($password)
			{
				$user['password'] = md5($password);
			}
			$userDB->setData($user);
			$userDB->update('id = '.$user_id);

			// 修改用户名记录
			if($user_name != $user_info['username'])
			{
				$logObj = new log('db');
				$logObj->write('operation',array("管理员:".$this->admin['admin_name'],"修改了用户","将 ${user_info['username']} 修改为 ${user_name}, uid: ${user_id}"));
			}

			$member_info = $memberDB->getObj('user_id='.$user_id);

			//修改积分记录日志
			if($point != $member_info['point'])
			{
				$ctrlType = $point > $member_info['point'] ? '增加' : '减少';
				$diffPoint= $point-$member_info['point'];

				$pointObj = new Point();
				$pointConfig = array(
					'user_id' => $user_id,
					'point'   => $diffPoint,
					'log'     => '管理员'.$this->admin['admin_name'].'将积分'.$ctrlType.$diffPoint.'积分',
				);
				$pointObj->update($pointConfig);
			}

			// 重新实名
			if($re_real_name)
			{
				(new IModel('real_name'))->setData(['is_del' => 1])->update('user_id = '.$user_id);
			}

			$memberDB->setData($member);
			$memberDB->update("user_id = ".$user_id);

			// 修改level记录日志
			if($level != $user_info['level'])
			{
				$logObj = new Log('db');
				// 'level_change' => array('table' => 'level_upgrade_log','cols' => array('admin_id','user_id','level','level_log','note')),
				$logObj->write('level_change', array($this->admin['admin_id'], $user_id, $level, $user_info['level']));
			}
		}
		$this->redirect('member_list');
	}

	/**
	 * @brief 会员列表
	 */
	function member_list()
	{
		$search = IFilter::act(IReq::get('search'),'strict');
		$page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$keywords = IFilter::act(IReq::get('keywords'));

		$orderBy = IFilter::act(IReq::get('orderBy'));
		$sort = IFilter::act(IReq::get('sort'));
		$order = $orderBy && $sort ? $orderBy.' '.$sort : 'm.user_id desc';

		$is_empty = IReq::get('is_empty');

		$where = ' 1 ';
		if($search && $keywords)
		{
			$where .= " and $search like '%{$keywords}%' ";
		}

		if($is_empty != '')
		{
			$where .= " and u.is_empty = ".$is_empty;
		}

		$this->data['is_empty'] = $is_empty;
		$this->data['search'] = $search;
		$this->data['keywords'] = $keywords;

		// 搜索父级处理
		$parent_name = IFilter::act(IReq::get('parent_name'));
		if($parent_name) {
			$parentInfo = (new IModel('user'))->getObj('username = "' . $parent_name . '"');
			if($parentInfo['id']) $where .= ' and u.parent_id = '.$parentInfo['id'];
		}
		$this->data['parent_name'] = $parent_name;

		// 会员组
		$tb_user_group = new IModel('user_group');
		$data_group = $tb_user_group->query();
		$group      = array();
		foreach($data_group as $value)
		{
			$group[$value['id']] = $value['group_name'];
		}
		$this->data['group'] = $group;
		
		// 查数据
        $query = new IQuery("user as u");
        $query->join   = 'left join member as m on m.user_id = u.id';
        $query->where  = 'm.status != 2 and '.$where;
		
		// 得到所有用户的余额
		$query->fields = 'sum(m.balance) as allBalance,sum(u.revisit) as allRevisit,sum(fir_stocks) as allFifStocks,sum(sec_stocks) as allSecStocks';
		$sumRes = $query->find();
		$this->data['sum'] = $sumRes[0];

		// 用户信息
		$query->fields = 'm.*,u.*';
        $query->order  = $order;
		$query->page   = $page;
		$memberRow = $query->find();

		// 查出父id的用户名
		$query->join = 'left join member as m on m.user_id = u.id left join user as au on au.id = u.parent_id';
		$query->fields = 'u.id,au.username as parent_name';
		$query->where  = 'm.status != 2 and '.$where;
		$parentRow = $query->find();

		// 合并数据
		foreach ($parentRow as $key => $value) {
			if($value['id'] == $memberRow[$key]['id']) {
				$memberRow[$key]['parent_name'] = $value['parent_name'];
			}
		}

		// 渲染数据
		$this->setRenderData($this->data);
        $this->query = $query;
        $this->memberRow = $memberRow;
		$this->redirect('member_list');
	}

	/**
	 * 用户余额管理页面
	 */
	function member_balance()
	{
		$this->layout = '';
		$this->redirect('member_balance');
	}
	/**
	 * @brief 删除至回收站
	 */
	function member_reclaim()
	{
		$user_ids = IReq::get('check');
		$user_ids = is_array($user_ids) ? $user_ids : array($user_ids);
		$user_ids = IFilter::act($user_ids,'int');
		if($user_ids)
		{
			$ids = implode(',',$user_ids);
			if($ids)
			{
				$tb_member = new IModel('member');
				$tb_member->setData(array('status'=>'2'));
				$where = "user_id in (".$ids.")";
				$tb_member->update($where);
			}
		}
		$this->member_list();
	}
	//批量用户余额操作
    function member_recharge()
    {
    	$id       = IFilter::act(IReq::get('check'),'int');
    	$balance  = IFilter::act(IReq::get('balance'),'float');
    	$type     = IFIlter::act(IReq::get('type')); //操作类型 recharge充值,withdraw提现金
    	$even     = '';

    	if(!$id)
    	{
			die(JSON::encode(array('flag' => 'fail','message' => '请选择要操作的用户')));
			return;
    	}

    	//执行写入操作
    	$id = is_array($id) ? join(',',$id) : $id;
    	$memberDB = new IModel('member');
    	$memberData = $memberDB->query('user_id in ('.$id.')');

		$markDB   = new IModel('recharge_mark');
    	$alipay   = IFilter::act(IReq::get('alipay'),'float');
    	$wxpay    = IFilter::act(IReq::get('wxpay'),'float');
    	$bankpay  = IFilter::act(IReq::get('bankpay'),'float');
    	$moneypay = IFilter::act(IReq::get('moneypay'),'float');
    	$note     = IFilter::act(IReq::get('note'));

		foreach($memberData as $value)
		{
			//用户余额进行的操作记入account_log表
			$log = new AccountLog();
			$config=array
			(
				'user_id'  => $value['user_id'],
				'admin_id' => $this->admin['admin_id'],
				'event'    => $type,
				'num'      => $balance,
			);
			$re = $log->write($config);
			if($re == false)
			{
				die(JSON::encode(array('flag' => 'fail','message' => $log->error)));
			}

			if ($type == 'recharge' && ($alipay || $wxpay || $bankpay || $moneypay || $note))
			{
				$markDB->setData([
				'alipay' => $alipay,
				'wxpay' => $wxpay,
				'bankpay' => $bankpay,
				'moneypay' => $moneypay,
				'user_id' => $value['user_id'],
				'note' => $note,
				'time' => ITime::getDateTime(),
				'account_id' => $re,
				])->add();
			}
		}
		die(JSON::encode(array('flag' => 'success')));
    }
	/**
	 * @brief 用户组添加
	 */
	function group_edit()
	{
		$gid = (int)IReq::get('gid');
		//编辑会员等级信息 读取会员等级信息
		if($gid)
		{
			$tb_user_group = new IModel('user_group');
			$group_info = $tb_user_group->query("id=".$gid);

			if(is_array($group_info) && ($info=$group_info[0]))
			{
				$this->data['group'] = array(
					'group_id'	=>	$info['id'],
					'group_name'=>	$info['group_name'],
					'discount'	=>	$info['discount'],
					'minexp'	=>	$info['minexp'],
					'maxexp'	=>	$info['maxexp']
				);
			}
			else
			{
				$this->redirect('group_list',false);
				Util::showMessage("没有找到相关记录！");
				return;
			}
		}
		$this->setRenderData($this->data);
		$this->redirect('group_edit');
	}

	/**
	 * @brief 保存用户组修改
	 */
	function group_save()
	{
		$group_id = IFilter::act(IReq::get('group_id'),'int');
		$maxexp   = IFilter::act(IReq::get('maxexp'),'int');
		$minexp   = IFilter::act(IReq::get('minexp'),'int');
		$discount = IFilter::act(IReq::get('discount'),'float');
		$group_name = IFilter::act(IReq::get('group_name'));

		$group = array(
			'maxexp' => $maxexp,
			'minexp' => $minexp,
			'discount' => $discount,
			'group_name' => $group_name
		);

		if($discount > 100)
		{
			$errorMsg = '折扣率不能大于100';
		}

		if($maxexp <= $minexp)
		{
			$errorMsg = '最大经验值必须大于最小经验值';
		}

		if(isset($errorMsg) && $errorMsg)
		{
			$group['group_id'] = $group_id;
			$data = array($group);

			$this->setRenderData($data);
			$this->redirect('group_edit',false);
			Util::showMessage($errorMsg);
			exit;
		}
		$tb_user_group = new IModel("user_group");
		$tb_user_group->setData($group);

		if($group_id)
		{
			$affected_rows = $tb_user_group->update("id=".$group_id);
			$this->redirect('group_list');
		}
		else
		{
			$tb_user_group->add();
			$this->redirect('group_list');
		}
	}

	/**
	 * @brief 删除会员组
	 */
	function group_del()
	{
		$group_ids = IReq::get('check');
		$group_ids = is_array($group_ids) ? $group_ids : array($group_ids);
		$group_ids = IFilter::act($group_ids,'int');
		if($group_ids)
		{
			$ids = implode(',',$group_ids);
			if($ids)
			{
				$tb_user_group = new IModel('user_group');
				$where = "id in (".$ids.")";
				$tb_user_group->del($where);
			}
		}
		$this->redirect('group_list');
	}

	/**
	 * @brief 回收站
	 */
	function recycling()
	{
		$tb_user_group = new IModel('user_group');
		$data_group    = $tb_user_group->query();
		$group         = array();
		foreach($data_group as $value)
		{
			$group[$value['id']] = $value['group_name'];
		}
		$this->data['group'] = $group;
		$this->setRenderData($this->data);
        $page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
        $query = new IQuery("member as m");
        $query->join   = 'left join user as u on m.user_id = u.id left join user_group as gp on m.group_id = gp.id';
        $query->where  = 'm.status = 2';
        $query->fields = 'm.*,u.username,gp.group_name';
        $query->order  = 'm.user_id desc';
        $query->page   = $page;
        $this->query   = $query;
		$this->redirect('recycling');
	}

	/**
	 * @brief 彻底删除会员
	 */
	function member_del()
	{
		$user_ids = IReq::get('check');
		$user_ids = is_array($user_ids) ? $user_ids : array($user_ids);
		$user_ids = IFilter::act($user_ids,'int');
		if($user_ids)
		{
			$ids = implode(',',$user_ids);

			if($ids)
			{
				$tb_member = new IModel('member');
				$where = "user_id in (".$ids.")";
				$tb_member->del($where);

				$tb_user = new IModel('user');
				$where = "id in (".$ids.")";
				$tb_user->del($where);

				$logObj = new log('db');
				$logObj->write('operation',array("管理员:".$this->admin['admin_name'],"删除了用户","被删除的用户ID为：".$ids));
			}
		}
		$this->redirect('member_list');
	}

	/**
	 * @brief 从回收站还原会员
	 */
	function member_restore()
	{
		$user_ids = IReq::get('check');
		$user_ids = is_array($user_ids) ? $user_ids : array($user_ids);
		if($user_ids)
		{
			$user_ids = IFilter::act($user_ids,'int');
			$ids = implode(',',$user_ids);
			if($ids)
			{
				$tb_member = new IModel('member');
				$tb_member->setData(array('status'=>'1'));
				$where = "user_id in (".$ids.")";
				$tb_member->update($where);
			}
		}
		$this->redirect('recycling');
	}

	//[提现管理] 删除
	function withdraw_del()
	{
		$id = IFilter::act(IReq::get('id'));

		if($id)
		{
			$id = IFilter::act($id,'int');
			$withdrawObj = new IModel('withdraw');

			if(is_array($id))
			{
				$idStr = join(',',$id);
				$where = ' id in ('.$idStr.')';
			}
			else
			{
				$where = 'id = '.$id;
			}

			$withdrawObj->del($where);
			$this->redirect('withdraw_recycle');
		}
		else
		{
			$this->redirect('withdraw_recycle',false);
			Util::showMessage('请选择要删除的数据');
		}
	}

	//[提现管理] 回收站 删除,恢复
	function withdraw_update()
	{
		$id   = IFilter::act( IReq::get('id') , 'int' );
		$type = IReq::get('type') ;

		if($id)
		{
			$withdrawObj = new IModel('withdraw');

			$is_del = ($type == 'res') ? '0' : '1';
			$dataArray = array(
				'is_del' => $is_del
			);

			if(is_array($id))
			{
				$idStr = join(',',$id);
				$where = ' id in ('.$idStr.')';
			}
			else
			{
				$where = 'id = '.$id;
			}

			$dataArray = array(
				'is_del' => $is_del,
			);

			$withdrawObj->setData($dataArray);
			$withdrawObj->update($where);
			$this->redirect('withdraw_list');
		}
		else
		{
			if($type == 'del')
			{
				$this->redirect('withdraw_list',false);
			}
			else
			{
				$this->redirect('withdraw_recycle',false);
			}
			Util::showMessage('请选择要删除的数据');
		}
	}

	//[提现管理] 详情展示
	function withdraw_detail()
	{
		$id = IFilter::act( IReq::get('id'),'int' );

		if($id)
		{
			$withdrawObj = new IModel('withdraw');
			$where       = 'id = '.$id;
			$this->withdrawRow = $withdrawObj->getObj($where);

			$userDB = new IModel('user as u,member as m');
			$this->userRow = $userDB->getObj('u.id = m.user_id and u.id = '.$this->withdrawRow['user_id']);
			$this->redirect('withdraw_detail',false);
		}
		else
		{
			$this->redirect('withdraw_list');
		}
	}

	//[提现管理] 修改提现申请的状态
	function withdraw_status()
	{
		$id      = IFilter::act( IReq::get('id'),'int');
		$re_note = IFilter::act( IReq::get('re_note'),'string');
		$status  = IFilter::act(IReq::get('status'),'int');

		if($id && $status)
		{
			$withdrawObj = new IModel('withdraw');
			//提现成功
			if($status == 2)
			{
				$withdrawRow = $withdrawObj->getObj('id = '.$id);

				//用户余额进行的操作记入account_log表
				$log    = new AccountLog();
				$config = array
				(
					'user_id'  => $withdrawRow['user_id'],
					'admin_id' => $this->admin['admin_id'],
					'event'    => "withdraw",
					'num'      => $withdrawRow['amount'],
				);
				$result = $log->write($config);
				if($result == false)
				{
					$this->withdraw_detail();
					Util::showMessage($log->error);
				}
			}
			// 提现失败
			else if($status == -1)
			{
				Team::addFreeBalance($id);
			}

			$dataArray = array(
				're_note'=> $re_note,
				'status' => $status,
			);
			$withdrawObj->setData($dataArray);
			$where = "`id`= {$id} AND `status` = 0";
			$withdrawObj->update($where);

			//管理员操作日志
			$logObj = new log('db');
			$logObj->write('operation',array("管理员:".$this->admin['admin_name'],"修改了提现申请","ID值为：".$id));

			//发送事件
			plugin::trigger('withdrawStatusUpdate',$id);

			$this->withdraw_detail();
			Util::showMessage("更新成功");
		}
		else
		{
			$this->redirect('withdraw_list');
		}
	}

    //获取商户列表
    public function seller_list()
    {
        $where       = Util::search(IReq::get('search'));
        $page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
        $query = new IQuery("seller");
        $query->where  = 'is_del = 0 and '.$where;
        $query->order = 'id desc';
        $query->page   = $page;
        $this->query    = $query;
        $this->redirect('seller_list');
    }

	/**
	 * @brief 商家修改页面
	 */
	public function seller_edit()
	{
		$seller_id = IFilter::act(IReq::get('id'),'int');

		//修改页面
		if($seller_id)
		{
			$sellerDB        = new IModel('seller');
			$this->sellerRow = $sellerDB->getObj('id = '.$seller_id);
		}
		$this->redirect('seller_edit');
	}

	/**
	 * @brief 商户的增加动作
	 */
	public function seller_add()
	{
		$seller_id   = IFilter::act(IReq::get('id'),'int');
		$seller_name = IFilter::act(IReq::get('seller_name'));
		$email       = IFilter::act(IReq::get('email'));
		$password    = IFilter::act(IReq::get('password'));
		$repassword  = IFilter::act(IReq::get('repassword'));
		$truename    = IFilter::act(IReq::get('true_name'));
		$phone       = IFilter::act(IReq::get('phone'));
		$mobile      = IFilter::act(IReq::get('mobile'));
		$province    = IFilter::act(IReq::get('province'),'int');
		$city        = IFilter::act(IReq::get('city'),'int');
		$area        = IFilter::act(IReq::get('area'),'int');
		$cash        = IFilter::act(IReq::get('cash'),'float');
		$is_vip      = IFilter::act(IReq::get('is_vip'),'int');
		$is_lock     = IFilter::act(IReq::get('is_lock'),'int');
		$address     = IFilter::act(IReq::get('address'));
		$account     = IFilter::act(IReq::get('account'));
		$server_num  = IFilter::act(IReq::get('server_num'));
		$home_url    = IFilter::act(IReq::get('home_url'));
		$sort        = IFilter::act(IReq::get('sort'),'int');
		$discount     = IFilter::act(IReq::get('discount'),'float');

		if(!$seller_id && $password == '')
		{
			$errorMsg = '请输入密码！';
		}

		if($password != $repassword)
		{
			$errorMsg = '两次输入的密码不一致！';
		}

		//创建商家操作类
		$sellerDB = new IModel("seller");

		if($sellerDB->getObj("seller_name = '{$seller_name}' and id != {$seller_id}"))
		{
			$errorMsg = "登录用户名重复";
		}
		else if($sellerDB->getObj("true_name = '{$truename}' and id != {$seller_id}"))
		{
			$errorMsg = "商户真实全称重复";
		}
		//商户结算折扣率
		if (0 > $discount || 100 < $discount)
		{
		    $errorMsg = "商户结算折扣率请填写0~100的数字";
		}

		//操作失败表单回填
		if(isset($errorMsg))
		{
			$this->sellerRow = $_POST;
			$this->redirect('seller_edit',false);
			Util::showMessage($errorMsg);
		}

		//待更新的数据
		$sellerRow = array(
			'true_name' => $truename,
			'account'   => $account,
			'phone'     => $phone,
			'mobile'    => $mobile,
			'email'     => $email,
			'address'   => $address,
			'is_vip'    => $is_vip,
			'is_lock'   => $is_lock,
			'cash'      => $cash,
			'province'  => $province,
			'city'      => $city,
			'area'      => $area,
			'server_num'=> $server_num,
			'home_url'  => $home_url,
			'sort'      => $sort,
		    'discount'   => $discount,
		);

		//附件上传$_FILE
		if($_FILES)
		{
		    $uploadDir = IWeb::$app->config['upload'].'/seller';
			$uploadObj = new PhotoUpload($uploadDir);
			$uploadObj->setIterance(false);
			$photoInfo = $uploadObj->run();

			//商户资质上传
			if(isset($photoInfo['paper_img']['img']) && file_exists($photoInfo['paper_img']['img']))
			{
				$sellerRow['paper_img'] = $photoInfo['paper_img']['img'];
			}

			//logo图片处理
			if(isset($photoInfo['logo']['img']) && file_exists($photoInfo['logo']['img']))
			{
				$sellerRow['logo'] = $photoInfo['logo']['img'];
			}
		}

		//添加新会员
		if(!$seller_id)
		{
			$sellerRow['seller_name'] = $seller_name;
			$sellerRow['password']    = md5($password);
			$sellerRow['create_time'] = ITime::getDateTime();

			$sellerDB->setData($sellerRow);
			$sellerDB->add();
		}
		//编辑会员
		else
		{
			//修改密码
			if($password)
			{
				$sellerRow['password'] = md5($password);
			}

			$sellerDB->setData($sellerRow);
			$sellerDB->update("id = ".$seller_id);
		}
		$this->redirect('seller_list');
	}
	/**
	 * @brief 商户的删除动作
	 */
	public function seller_del()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$sellerDB = new IModel('seller');
		$data = array('is_del' => 1);
		$sellerDB->setData($data);

		if(is_array($id))
		{
			$sellerDB->update('id in ('.join(",",$id).')');
		}
		else
		{
			$sellerDB->update('id = '.$id);
		}
		$this->redirect('seller_list');
	}
	/**
	 * @brief 商户的回收站删除动作
	 */
	public function seller_recycle_del()
	{
		$id       = IFilter::act(IReq::get('id'),'int');
		$sellerDB = new IModel('seller');

		if(is_array($id))
		{
			$id = join(",",$id);
		}

		//删除商家扩展表数据
		$sellerExtTable = array("merch_ship_info","spec","delivery_extend","category_seller","delivery_doc","promotion","regiment","ticket","bill","takeself");
		foreach($sellerExtTable as $tableName)
		{
			$selletExtDB = new IModel($tableName);
			$selletExtDB->del('seller_id in ('.$id.')');
		}
		$sellerDB->del('id in ('.$id.')');
		$this->redirect('seller_recycle_list');
	}
	/**
	 * @brief 商户的回收站恢复动作
	 */
	public function seller_recycle_restore()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$sellerDB = new IModel('seller');
		$data = array('is_del' => 0);
		$sellerDB->setData($data);
		if(is_array($id))
		{
			$sellerDB->update('id in ('.join(",",$id).')');
		}
		else
		{
			$sellerDB->update('id = '.$id);
		}

		$this->redirect('seller_recycle_list');
	}
	//商户状态ajax
	public function ajax_seller_lock()
	{
		$id   = IFilter::act(IReq::get('id'));
		$lock = IFilter::act(IReq::get('lock'));
		$sellerObj = new IModel('seller');
		$sellerObj->setData(array('is_lock' => $lock));
		$sellerObj->update("id = ".$id);

		//短信通知状态修改
		plugin::trigger("updateSellerStatus",$id);
	}

	/***
 	**************************************************************
	*                                                            *
	*   .=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-.       *
	*    |                     ______                     |      *
	*    |                  .-"      "-.                  |      *
	*    |                 /            \                 |      *
	*    |     _          |              |          _     |      *
	*    |    ( \         |,  .-.  .-.  ,|         / )    |      *
	*    |     > "=._     | )(__/  \__)( |     _.=" <     |      *
	*    |    (_/"=._"=._ |/     /\     \| _.="_.="\_)    |      *
	*    |           "=._"(_     ^^     _)"_.="           |      *
	*    |               "=\__|IIIIII|__/="               |      *
	*    |              _.="| \IIIIII/ |"=._              |      *
	*    |    _     _.="_.="\          /"=._"=._     _    |      *
	*    |   ( \_.="_.="     `--------`     "=._"=._/ )   |      *
	*    |    > _.="                            "=._ <    |      *
	*    |   (_/                                    \_)   |      *
	*    |                                                |      *
	*    '-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-='      *
	*                                                            *
	*           LASCIATE OGNI SPERANZA, VOI CH'ENTRATE           *
	**************************************************************
	*/

	function activeVip()
	{
		$id = IFilter::act(IReq::get('id'));
		$amount = IFilter::act(IReq::get('amount'));
		$is_empty = IFilter::act(IReq::get('is_empty'));

		$userRow = Team::getVipInfoByUserId($id);
		if($userRow) {
			die(JSON::encode(array('flag' => 'fail','message' => '已经是vip了')));
			return;
		}

		//管理员操作日志
		$logObj = new log('db');
		$logObj->write('operation',array("管理员:".$this->admin['admin_name'],"激活了会员","uid值为：".$id.", 金额：".$amount));

		// 更新激活用户状态
		Team::updateVipUserLeve($id, $amount, $is_empty);

		// 走Team方法
		Team::init($id);

		die(JSON::encode(array('flag' => 'success')));
	}

	 // 后台--获取激活订单列表
	 function vip_order_list()
	 {
		 $page  = IReq::get('page') ? IFilter::act(IReq::get('page'), 'int') : 1;
		 $where = Util::search(IReq::get('search'));

		 $search = IFilter::act(IReq::get('user_type'));
		 $keyword = IFilter::act(IReq::get('keyword'));

		 if($search && $keyword) {
			 $userDB = new IModel('user');
			 $userRow = $userDB->query('username like "' . $keyword .'%"');
			 $idArr = [];
			 foreach ($userRow as $val) {
				 if($val['id']) $idArr[] = $val['id'];
			 }

			 if ($idArr) $where .= " and $search in (".join(',', $idArr).")";
		 }

		//  报单方式筛选
		 $orderType = IFilter::act(IReq::get('order_type'));
		 $orderConf = ['', ' and o.order_id is not null', ' and o.order_id is null'];

		 $where .= $orderConf[$orderType];

		 $query         = new IQuery('vip_order as o');
		 $query->join   = 'left join user as u on o.user_id = u.id left join order as od on o.order_id = od.id';
		 $query->fields = 'o.*, u.username, od.pay_status as o_pay_status, od.pay_time as o_pay_time, od.create_time as o_create_time';
		 $query->where  = $where;
		 $query->page   = $page;
		 $query->order  = 'o.id desc';
		 $userRow       = $query->find();
 
		 $query->join   = 'left join user as u on o.active_id = u.id';
		 $query->fields = 'o.*, u.username as active_name';
		 $activeRow     = $query->find();
 
		 $result = [];
		 foreach ($userRow as $key => $value) {
			 if($activeRow[$key]['id'] == $value['id']) {
				 $value['active_name'] = $activeRow[$key]['active_name'];
				 $result[$key] = $value;
			 }
		 }
		 $this->query = $query;
		 $this->log = $result;
		 $this->redirect('vip_order_list');
	}

	// 后台-余额报单导出
	function vip_order_report()
	{
		$where = Util::search(IReq::get('search'));
		
		$search = IFilter::act(IReq::get('user_type'));
		$keyword = IFilter::act(IReq::get('keyword'));

		if($search && $keyword) {
			$userDB = new IModel('user');
			$userRow = $userDB->query('username like "' . $keyword .'%"');
			$idArr = [];
			foreach ($userRow as $val) {
				if($val['id']) $idArr[] = $val['id'];
			}

			if ($idArr) $where .= " and $search in (".join(',', $idArr).")";
		}
		
		$orderType = IFilter::act(IReq::get('order_type'));
		$orderConf = ['', ' and o.order_id is not null', ' and o.order_id is null'];

		$where .= $orderConf[$orderType];

        $query         = new IQuery('vip_order as o');
		$query->join   = 'left join user as u on o.user_id = u.id';
		$query->fields = 'o.*, u.username';
		$query->where  = $where;
		$query->order  = 'o.id desc';
		$userRow       = $query->find();

		$query->join   = 'left join user as u on o.active_id = u.id';
		$query->fields = 'o.*, u.username as active_name';
		$activeRow     = $query->find();

		$result = [];
		foreach ($userRow as $key => $value) {
			if($activeRow[$key]['id'] == $value['id']) {
				$value['active_name'] = $activeRow[$key]['active_name'];
				$result[$key] = $value;
			}
		}

		$reportObj = new report('vip_order');
		$reportObj->setTitle(array("序号","付款人","vip会员","金额","状态","创建时间","支付时间",'下单方式'));

		$statusText = ['未支付', '已支付'];
		foreach($result as $k => $val)
		{
			$insertData = array($k+1,$val['active_name'],$val['username'],$val['order_amount'],$statusText[$val['pay_status']],$val['create_time'],$val['pay_time'],$val['order_id'] ? '新版':'旧版');
			$reportObj->setData($insertData);
		}
		$reportObj->toDownload();
	}

	// 后台--提现列表
	function withdraw_list()
	{
		$where = Util::search(IReq::get('search'));
		$page  = IReq::get('page') ? IFilter::act(IReq::get('page'), 'int') : 1;

		$query = new IQuery('withdraw as w');
		$query->join = 'left join member as m on w.user_id = m.user_id left join user as u on u.id = w.user_id';
		$query->fields = 'w.*,u.username,m.balance';
		$query->where = $where . ' and w.is_del = 0';
		$query->page   = $page;
		$query->order = 'w.id desc';

		$data['query'] = $query;
		$this->setRenderData($data);
		$this->redirect('withdraw_list');
	}

	// 后台--提现导出
	function withdraw_report()
	{
		$where = Util::search(IReq::get('search'));
		$query = new IQuery('withdraw as w');
		$query->join = 'left join member as m on w.user_id = m.user_id left join user as u on u.id = w.user_id';
		$query->fields = 'w.*,u.username,m.balance';
		$query->where = $where . ' and w.is_del = 0';
		$query->order = 'w.id desc';

		$result = $query->find();

		$reportObj = new report('withdraw_list');
		$reportObj->setTitle(array("序号","真实姓名","省份","城市","开户行","开户支行","账户","提现金额","服务费","用户名","状态","申请时间"));

		$statusText = ['-1'=>'失败','0'=>'未处理','2'=>'成功'];
		foreach($result as $k => $val)
		{
			$insertData = array(
				$k+1,
				$val['name'],
				$val['province'],
				$val['city'],
				$val['bank'],
				$val['bank_branch'],
				$val['card_num'],
				$val['amount'],
				$val['service_free'],
				$val['username'],
				$statusText[$val['status']],
				$val['time'],
			);
			$reportObj->setData($insertData);
		}
		$reportObj->toDownload();
	}

	// 后台--重置会员密码
	function member_reset_password()
	{
		$user_id = IFilter::act(IReq::get('user_id'),'int');
		$type = IFilter::act(IReq::get('type'));
		$result = array('code' => 0, 'msg' => '操作失败');
		if($user_id && $type)
		{
			switch ($type) {
				case 'all':
					$updateData = array('tran_password' => null, 'password' => md5('111111'));
					break;
				
				case 'login':
					$updateData = array('password' => md5('111111'));
					break;
				
				case 'trans':
					$updateData = array('tran_password' => null);
					break;
				
				default:
					$updateData = '';
					break;
			}
			$userDB = new IModel('user');
			if($updateData) {
				$userDB->setData($updateData);
				$userDB->update('id = '. $user_id);
				$result = array('code' => 1, 'msg' => '操作成功');
			}
		}
		echo JSON::encode($result);
		return;
	}

	/**
     * 用户重消管理操作
     */
    function member_revisit()
    {
    	$user_id = IFilter::act(IReq::get('uid'),'int');
    	$type = IFilter::act(IReq::get('type'));
    	$revisit = IFilter::act(IReq::get('value'));
    	$comment = IFilter::act(IReq::get('comment'));

    	if (!$user_id) {
    		echo JSON::encode(array('code'=>'0','msg'=>'请选择要操作的用户'));
			return;
    	}
		$userObj = new IModel('user');
		$where = 'id='.$user_id;
    	$userRow = $userObj->getObj($where);

    	//修改重消记录日志
		if ($type == 1) {
			$ctrlType = '减少';
			if($userRow['revisit'] < $revisit) {
				echo JSON::encode(array('code'=>'0','msg'=>'金额不足'));
				return;
			}
			$revisit  = $revisit * -1;
		}
		else if ($type == 0) {
			$ctrlType = '增加';
		}
		else {
			echo JSON::encode(array('code'=>'0','msg'=>'非法请求'));
			return;
		}

		$updateData = $userRow['revisit'] + $revisit;

		$revisit = abs($revisit);

		$userObj->setData(array('revisit'=>$updateData));

		if($userObj->update($where)) {
			$logDB = new IModel('revisit_log');
			$logConfig = array(
				'admin_id'  => $this->admin['admin_id'],
				'user_id'   => $user_id,
				'type'      => $type,
				'time'      => ITime::getDateTime(),
				'value'     => $revisit,
				'value_log' => $updateData,
				'note'      => '管理员 '.$ctrlType.' 金额 '.$revisit,
			);
			$logDB->setData($logConfig);
			if ($logDB->add()) {
				//管理员操作日志
				$logObj = new log('db');
				$logObj->write('operation',array("管理员:".$this->admin['admin_name'],$ctrlType.' 用户['.$user_id.']'.$userRow['user_name'].' '.$revisit.' vip消费，当前余额'.$updateData,$comment));
				echo JSON::encode(array('code'=>'1','msg'=>'操作成功'));
				return;
			}
		}
		
		echo JSON::encode(array('code'=>'0','msg'=>'操作失败'));
	}
	/**
     * 用户stocks管理操作
     */
    function member_stocks()
    {
    	$user_id = IFilter::act(IReq::get('uid'),'int');
    	$type = IFilter::act(IReq::get('type'));
    	$stocks = IFilter::act(IReq::get('value'));
    	$comment = IFilter::act(IReq::get('comment'));
    	$editType = IFilter::act(IReq::get('editType')); // 编辑类型 old or new

    	if (!$user_id) {
    		echo JSON::encode(array('code'=>'0','msg'=>'请选择要操作的用户'));
			return;
		}

		$typeConfig = array(
			'old' => ['1', 'fir_stocks'],  // 老股1
			'new' => ['2', 'sec_stocks'],  // 新股2
		);
		
		if(!$typeConfig[$editType]) die(JSON::encode(array('code'=>'0','msg'=>'非法请求')));

		$userObj = new IModel('user');
		$where = 'id='.$user_id;
    	$userRow = $userObj->getObj($where);

    	//修改stocks记录日志
		if ($type == 1) {
			$ctrlType = '减少';
			if($userRow[$typeConfig[$editType][1]] < $stocks) {
				echo JSON::encode(array('code'=>'0','msg'=>'金额不足'));
				return;
			}
			$stocks  = $stocks * -1;
		}
		else if ($type == 0) {
			$ctrlType = '增加';
		}
		else {
			echo JSON::encode(array('code'=>'0','msg'=>'非法请求'));
			return;
		}

		$updateData = $userRow[$typeConfig[$editType][1]] + $stocks;

		$userObj->setData(array($typeConfig[$editType][1]=>$updateData));
		
		$stocks = abs($stocks);

		if($userObj->update($where)) {
			$logDB = new IModel('sec_scocks_log');
			$logConfig = array(
				'admin_id'  => $this->admin['admin_id'],
				'user_id'   => $user_id,
				'type'      => $type,
				'datetime'  => ITime::getDateTime(),
				'value'     => $stocks,
				'value_log' => $updateData,
				'log_type'  => $typeConfig[$editType][0],
				'note'      => '管理员 '.$ctrlType.' 股 '.$stocks,
			);
			$logDB->setData($logConfig);
			if ($logDB->add()) {
				//管理员操作日志
				$logObj = new log('db');
				$logObj->write('operation',array("管理员:".$this->admin['admin_name'],$ctrlType.' 用户['.$user_id.']'.$userRow['user_name'].' '.$stocks.' 股，当前余额'.$updateData,$comment));
				echo JSON::encode(array('code'=>'1','msg'=>'操作成功'));
				return;
			}
		}
		
		echo JSON::encode(array('code'=>'0','msg'=>'操作失败'));
	}

	// 实名审核
	function real_name_act()
	{
		$id     = IFilter::act(IReq::get('id'));
		$status = IFilter::act(IReq::get('status'));

		if(is_array($id)) $id = join(',', $id);

		if((!$status || !$id)) {
			echo JSON::encode(array('code' => '0', 'msg' => '参数错误'));
        	return;
		}

		$query = new IModel('real_name');
		$realNameRow = $query->getObj("is_del = 0 and id in($id)");
		if(!$realNameRow) {
			echo JSON::encode(array('code' => '0', 'msg' => '信息不存在'));
        	return;
		}

		$query->setData(array('status' => $status))->update("id in($id)");

		echo JSON::encode(array('code' => '1', 'msg' => '审核成功'));
		return;
	}

	// update agent for vip
	function checkAgentStatus()
	{
		$id = IFilter::act(IReq::get('id'));
		$amount = IFilter::act(IReq::get('amount'));

		$userRow = Team::getVipInfoByUserId($id);

		if(!$userRow) die(JSON::encode(array('flag' => 'fail','message' => 'uid is not fond')));

		if($userRow['level'] < 11 || $userRow['is_agent'] || $userRow['agent_level'] > 0) {
			die(JSON::encode(array('flag' => 'fail','message' => 'is agent now')));
		}

        $vipConfig = Team::vipStatusConfig();
		$where = $vipConfig[intval($amount)];

		$is_agent = $where['is_agent'];
		$agent_level = $where['agent_level'];

		if(!$is_agent || !$agent_level) die(JSON::encode(array('flag' => 'fail','message' => 'only for agent methods')));

		// 更新库
		$db = new IModel('user');
		$db->setData([
			'is_agent'    => $is_agent,
			'agent_level' => $agent_level,
		])->update("id = ${id}");

		// 管理员操作日志
		$logObj = new log('db');
		$logObj->write('operation',array("管理员:".$this->admin['admin_name'],"升级了会员","uid值为：".$id.", 金额：".$amount));

		die(JSON::encode(array('flag' => 'success')));
	}

	function recharge_mark_report()
	{
		$where = Util::search(IReq::get('search'));
		
		$search = IFilter::act(IReq::get('user_type'));
		$keyword = IFilter::act(IReq::get('keyword'));

		if($search && $keyword) {
			$userDB = new IModel('user');
			$userRow = $userDB->query('username like "' . $keyword .'%"');
			$idArr = [];
			foreach ($userRow as $val) {
				if($val['id']) $idArr[] = $val['id'];
			}

			if ($idArr) $where .= " and $search in (".join(',', $idArr).")";
		}
		
		$orderType = IFilter::act(IReq::get('order_type'));
		$orderConf = ['', ' and o.order_id is not null', ' and o.order_id is null'];

		$where .= $orderConf[$orderType];

        $query         = new IQuery('vip_order as o');
		$query->join   = 'left join user as u on o.user_id = u.id';
		$query->fields = 'o.*, u.username';
		$query->where  = $where;
		$query->order  = 'o.id desc';
		$userRow       = $query->find();

		$query->join   = 'left join user as u on o.active_id = u.id';
		$query->fields = 'o.*, u.username as active_name';
		$activeRow     = $query->find();

		$result = [];
		foreach ($userRow as $key => $value) {
			if($activeRow[$key]['id'] == $value['id']) {
				$value['active_name'] = $activeRow[$key]['active_name'];
				$result[$key] = $value;
			}
		}

		$reportObj = new report('vip_order');
		$reportObj->setTitle(array("序号","付款人","vip会员","金额","状态","创建时间","支付时间",'下单方式'));

		$statusText = ['未支付', '已支付'];
		foreach($result as $k => $val)
		{
			$insertData = array($k+1,$val['active_name'],$val['username'],$val['order_amount'],$statusText[$val['pay_status']],$val['create_time'],$val['pay_time'],$val['order_id'] ? '新版':'旧版');
			$reportObj->setData($insertData);
		}
		$reportObj->toDownload();
	}

}