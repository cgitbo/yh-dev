<?php
/**
 * @copyright Copyright(c) 2011 aircheng.com
 * @file site.php
 * @brief
 * @author webning
 * @date 2011-03-22
 * @version 0.6
 * @note
 */
/**
 * @brief Site
 * @class Site
 * @note
 */
class Site extends IController
{
    public $layout='site';

	function init()
	{

	}

	function index()
	{
		$this->index_slide = Api::run('getBannerList');
		$this->redirect('index');
	}

	//[首页]商品搜索
	function search_list()
	{
		$this->word = IFilter::act(IReq::get('word'),'text');
		$cat_id     = IFilter::act(IReq::get('cat'),'int');

		if(preg_match("|^[\w\x7f\s*-\xff*]+$|",$this->word))
		{
			//搜索关键字
			$tb_sear     = new IModel('search');
			$search_info = $tb_sear->getObj('keyword = "'.$this->word.'"','id');

			//如果是第一页，相应关键词的被搜索数量才加1
			if($search_info && intval(IReq::get('page')) < 2 )
			{
				//禁止刷新+1
				$allow_sep = "30";
				$flag = false;
				$time = ICookie::get('step');
				if($time)
				{
					if (time() - $time > $allow_sep)
					{
						ICookie::set('step',time());
						$flag = true;
					}
				}
				else
				{
					ICookie::set('step',time());
					$flag = true;
				}
				if($flag)
				{
					$tb_sear->setData(array('num'=>'num + 1'));
					$tb_sear->update('id='.$search_info['id'],'num');
				}
			}
			elseif( !$search_info )
			{
				//如果数据库中没有这个词的信息，则新添
				$tb_sear->setData(array('keyword'=>$this->word,'num'=>1));
				$tb_sear->add();
			}
		}
		else
		{
			IError::show(403,'请输入正确的查询关键词');
		}
		$this->cat_id = $cat_id;
		$this->redirect('search_list');
	}

	//[site,ucenter头部分]自动完成
	function autoComplete()
	{
		$word = IFilter::act(IReq::get('word'));
		$isError = true;
		$data    = array();

		if($word != '' && $word != '%' && $word != '_')
		{
			$wordObj  = new IModel('keyword');
			$wordList = $wordObj->query('word like "'.$word.'%" and word != "'.$word.'"','word, goods_nums','',10);

			if($wordList)
			{
				$isError = false;
				$data = $wordList;
			}
		}

		//json数据
		$result = array(
			'isError' => $isError,
			'data'    => $data,
		);

		echo JSON::encode($result);
	}

	//[首页]邮箱订阅
	function email_registry()
	{
		$email  = IReq::get('email');
		$result = array('isError' => true);

		if(!IValidate::email($email))
		{
			$result['message'] = '请填写正确的email地址';
		}
		else
		{
			$emailRegObj = new IModel('email_registry');
			$emailRow    = $emailRegObj->getObj('email = "'.$email.'"');

			if($emailRow)
			{
				$result['message'] = '此email已经订阅过了';
			}
			else
			{
				$dataArray = array(
					'email' => $email,
				);
				$emailRegObj->setData($dataArray);
				$status = $emailRegObj->add();
				if($status == true)
				{
					$result = array(
						'isError' => false,
						'message' => '订阅成功',
					);
				}
				else
				{
					$result['message'] = '订阅失败';
				}
			}
		}
		echo JSON::encode($result);
	}

	//[列表页]商品
	function pro_list()
	{
		$this->catId = IFilter::act(IReq::get('cat'),'int');//分类id

		if($this->catId == 0)
		{
			IError::show(403,'缺少分类ID');
		}

		//查找分类信息
		$catObj       = new IModel('category');
		$this->catRow = $catObj->getObj('id = '.$this->catId);

		if($this->catRow == null)
		{
			IError::show(403,'此分类不存在');
		}

		//获取子分类
		$this->childId = goods_class::catChild($this->catId);
		$this->redirect('pro_list');
	}
	//咨询
	function consult()
	{
		$this->goods_id = IFilter::act(IReq::get('id'),'int');
		if($this->goods_id == 0)
		{
			IError::show(403,'缺少商品ID参数');
		}

		$goodsObj   = new IModel('goods');
		$goodsRow   = $goodsObj->getObj('id = '.$this->goods_id);
		if(!$goodsRow)
		{
			IError::show(403,'商品数据不存在');
		}

		//获取次商品的评论数和平均分
		$goodsRow['apoint'] = $goodsRow['comments'] ? round($goodsRow['grade']/$goodsRow['comments']) : 0;

		$this->goodsRow = $goodsRow;
		$this->redirect('consult');
	}

	//咨询动作
	function consult_act()
	{
		$goods_id   = IFilter::act(IReq::get('goods_id','post'),'int');
		$captcha    = IFilter::act(IReq::get('captcha','post'));
		$question   = IFilter::act(IReq::get('question','post'));
		$_captcha   = ISafe::get('captcha');
		$message    = '';

    	if(!$captcha || !$_captcha || $captcha != $_captcha)
    	{
    		$message = '验证码输入不正确';
    	}
    	else if(!$question)
    	{
    		$message = '咨询内容不能为空';
    	}
    	else if(!$goods_id)
    	{
    		$message = '商品ID不能为空';
    	}
    	else
    	{
    		$goodsObj = new IModel('goods');
    		$goodsRow = $goodsObj->getObj('id = '.$goods_id);
    		if(!$goodsRow)
    		{
    			$message = '不存在此商品';
    		}
    	}

		//有错误情况
    	if($message)
    	{
    		IError::show(403,$message);
    	}
    	else
    	{
			$dataArray = array(
				'question' => $question,
				'goods_id' => $goods_id,
				'user_id'  => isset($this->user['user_id']) ? $this->user['user_id'] : 0,
				'time'     => ITime::getDateTime(),
			);
			$referObj = new IModel('refer');
			$referObj->setData($dataArray);
			$referObj->add();
			plugin::trigger('setCallback','/site/products/id/'.$goods_id);
			$this->redirect('/site/success');
    	}
	}

	//公告详情页面
	function notice_detail()
	{
		$this->notice_id = IFilter::act(IReq::get('id'),'int');
		if($this->notice_id == '')
		{
			IError::show(403,'缺少公告ID参数');
		}
		else
		{
			$noObj           = new IModel('announcement');
			$this->noticeRow = $noObj->getObj('id = '.$this->notice_id);
			if(empty($this->noticeRow))
			{
				IError::show(403,'公告信息不存在');
			}
			$this->redirect('notice_detail');
		}
	}

	//文章列表页面
	function article()
	{
		$catId  = IFilter::act(IReq::get('id'),'int');
		$catRow = Api::run('getArticleCategoryInfo',$catId);
		$queryArticle = $catRow ? Api::run('getArticleListByCatid',$catRow['id']) : Api::run('getArticleList');
		$this->setRenderData(array("catRow" => $catRow,'queryArticle' => $queryArticle));
		$this->redirect('article');
	}

	//文章详情页面
	function article_detail()
	{
		$this->article_id = IFilter::act(IReq::get('id'),'int');
		if($this->article_id == '')
		{
			IError::show(403,'缺少咨询ID参数');
		}
		else
		{
			$articleObj       = new IModel('article');
			$this->articleRow = $articleObj->getObj('id = '.$this->article_id);
			if(empty($this->articleRow))
			{
				IError::show(403,'资讯文章不存在');
				exit;
			}

			//关联商品
			$this->relationList = Api::run('getArticleGoods',array("#article_id#",$this->article_id));
			$this->redirect('article_detail');
		}
	}

	//商品展示
	function products()
	{
		$goods_id = IFilter::act(IReq::get('id'),'int');

		if(!$goods_id)
		{
			IError::show(403,"传递的参数不正确");
		}

		//使用商品id获得商品信息
		$tb_goods = new IModel('goods');
		$goods_info = $tb_goods->getObj('id='.$goods_id." AND is_del=0");
		if(!$goods_info)
		{
			IError::show(403,"这件商品不存在");
		}

		//品牌名称
		if($goods_info['brand_id'])
		{
			$tb_brand = new IModel('brand');
			$brand_info = $tb_brand->getObj('id='.$goods_info['brand_id']);
			if($brand_info)
			{
				$goods_info['brand'] = $brand_info['name'];
			}
		}

		//获取商品分类
		$categoryObj = new IModel('category_extend as ca,category as c');
		$categoryList= $categoryObj->query('ca.goods_id = '.$goods_id.' and ca.category_id = c.id','c.id,c.name','ca.id desc',1);
		$categoryRow = null;
		if($categoryList)
		{
			$categoryRow = current($categoryList);
		}
		$goods_info['category'] = $categoryRow ? $categoryRow['id'] : 0;

		//商品图片
		$tb_goods_photo = new IQuery('goods_photo_relation as g');
		$tb_goods_photo->fields = 'p.id AS photo_id,p.img ';
		$tb_goods_photo->join = 'left join goods_photo as p on p.id=g.photo_id ';
		$tb_goods_photo->where =' g.goods_id='.$goods_id;
		$tb_goods_photo->order =' g.id asc';
		$goods_info['photo'] = $tb_goods_photo->find();

		//商品是否参加营销活动
		if($goods_info['promo'] && $goods_info['active_id'])
		{
			$activeObj    = new Active($goods_info['promo'],$goods_info['active_id'],$this->user['user_id'],$goods_id);
			$activeResult = $activeObj->data();
			if(is_string($activeResult))
			{
				IError::show(403,$activeResult);
			}
			else
			{
				$goods_info[$goods_info['promo']] = $activeResult;
				$goods_info['activeTemplate']     = $activeObj->productTemplate();
			}
		}

		//获得扩展属性
		$tb_attribute_goods = new IQuery('goods_attribute as g');
		$tb_attribute_goods->join  = 'left join attribute as a on a.id=g.attribute_id ';
		$tb_attribute_goods->fields=' a.name,g.attribute_value ';
		$tb_attribute_goods->where = "goods_id='".$goods_id."' and attribute_id!=''";
		$goods_info['attribute'] = $tb_attribute_goods->find();

		//购买记录
		$tb_shop = new IQuery('order_goods as og');
		$tb_shop->join = 'left join order as o on o.id=og.order_id';
		$tb_shop->fields = 'count(*) as totalNum';
		$tb_shop->where = 'og.goods_id='.$goods_id.' and o.status = 5';
		$shop_info = $tb_shop->find();
		$goods_info['buy_num'] = 0;
		if($shop_info)
		{
			$goods_info['buy_num'] = $shop_info[0]['totalNum'];
		}

		//购买前咨询
		$tb_refer    = new IModel('refer');
		$refeer_info = $tb_refer->getObj('goods_id='.$goods_id,'count(*) as totalNum');
		$goods_info['refer'] = 0;
		if($refeer_info)
		{
			$goods_info['refer'] = $refeer_info['totalNum'];
		}

		//网友讨论
		$tb_discussion = new IModel('discussion');
		$discussion_info = $tb_discussion->getObj('goods_id='.$goods_id,'count(*) as totalNum');
		$goods_info['discussion'] = 0;
		if($discussion_info)
		{
			$goods_info['discussion'] = $discussion_info['totalNum'];
		}

		//获得商品的价格区间
		$tb_product = new IModel('products');
		$product_info = $tb_product->getObj('goods_id='.$goods_id,'max(sell_price) as maxSellPrice ,max(market_price) as maxMarketPrice');
		if(isset($product_info['maxSellPrice']) && $goods_info['sell_price'] != $product_info['maxSellPrice'])
		{
			$goods_info['sell_price']   .= "-".$product_info['maxSellPrice'];
		}

		if(isset($product_info['maxMarketPrice']) && $goods_info['market_price'] != $product_info['maxMarketPrice'])
		{
			$goods_info['market_price'] .= "-".$product_info['maxMarketPrice'];
		}

		//获得会员价
		$countsumInstance = new countsum();
		$goods_info['group_price'] = $countsumInstance->groupPriceRange($goods_id);

		//获取商家信息
		if($goods_info['seller_id'])
		{
			$sellerDB = new IModel('seller');
			$goods_info['seller'] = $sellerDB->getObj('id = '.$goods_info['seller_id']);
		}

		//增加浏览次数
		$visit    = ISafe::get('visit');
		$checkStr = "#".$goods_id."#";
		if($visit && strpos($visit,$checkStr) !== false)
		{
		}
		else
		{
			$tb_goods->setData(array('visit' => 'visit + 1'));
			$tb_goods->update('id = '.$goods_id,'visit');
			$visit = $visit === null ? $checkStr : $visit.$checkStr;
			ISafe::set('visit',$visit);
		}

		//数据处理用于显示
		$goods_info['weight'] = common::formatWeight($goods_info['weight']);

		$this->setRenderData($goods_info);
		$this->redirect('products');
	}
	//商品讨论更新
	function discussUpdate()
	{
		$goods_id = IFilter::act(IReq::get('id'),'int');
		$content  = IFilter::act(IReq::get('content'),'text');
		$captcha  = IReq::get('captcha');
		$_captcha = ISafe::get('captcha');
		$return   = array('isError' => true , 'message' => '');

		if(!$this->user['user_id'])
		{
			$return['message'] = '请先登录系统';
		}
    	else if(!$captcha || !$_captcha || $captcha != $_captcha)
    	{
    		$return['message'] = '验证码输入不正确';
    	}
    	else if(trim($content) == '')
    	{
    		$return['message'] = '内容不能为空';
    	}
    	else
    	{
    		$return['isError'] = false;

			//插入讨论表
			$tb_discussion = new IModel('discussion');
			$dataArray     = array(
				'goods_id' => $goods_id,
				'user_id'  => $this->user['user_id'],
				'time'     => ITime::getDateTime(),
				'contents' => $content,
			);
			$tb_discussion->setData($dataArray);
			$tb_discussion->add();

			$return['time']     = $dataArray['time'];
			$return['contents'] = $content;
			$return['username'] = $this->user['username'];
    	}
    	echo JSON::encode($return);
	}

	//获取货品数据
	function getProduct()
	{
		$goods_id = IFilter::act(IReq::get('goods_id'),'int');
		$specJSON = IReq::get('specJSON');
		if(!$specJSON || !is_array($specJSON))
		{
			echo JSON::encode(array('flag' => 'fail','message' => '规格值不符合标准'));
			exit;
		}

		//获取货品数据
		$tb_products = new IModel('products');
		$procducts_info = $tb_products->getObj("goods_id = ".$goods_id." and spec_array = '".IFilter::act(htmlspecialchars_decode(JSON::encode($specJSON)))."'");

		//匹配到货品数据
		if(!$procducts_info)
		{
			echo JSON::encode(array('flag' => 'fail','message' => '没有找到相关货品'));
			exit;
		}

		//获得会员价
		$countsumInstance = new countsum();
		$group_price = $countsumInstance->getGroupPrice($procducts_info['id'],'product');

		//会员价格
		if($group_price !== null)
		{
			$procducts_info['group_price'] = $group_price;
		}

		//处理数据内容
		$procducts_info['weight'] = common::formatWeight($procducts_info['weight']);
		echo JSON::encode(array('flag' => 'success','data' => $procducts_info));
	}

	//顾客评论ajax获取
	function comment_ajax()
	{
		$goods_id = IFilter::act(IReq::get('goods_id'),'int');
		$page     = IFilter::act(IReq::get('page'),'int') ? IReq::get('page') : 1;

		$commentDB = new IQuery('comment as c');
		$commentDB->join   = 'left join goods as go on c.goods_id = go.id left join user as u on u.id = c.user_id';
		$commentDB->fields = 'u.head_ico,u.username,c.*';
		$commentDB->where  = 'c.goods_id = '.$goods_id.' and c.status = 1';
		$commentDB->order  = 'c.id desc';
		$commentDB->page   = $page;
		$data     = $commentDB->find();
		$pageHtml = $commentDB->getPageBar("javascript:void(0);",'onclick="comment_ajax([page])"');

		echo JSON::encode(array('data' => $data,'pageHtml' => $pageHtml));
	}

	//购买记录ajax获取
	function history_ajax()
	{
		$goods_id = IFilter::act(IReq::get('goods_id'),'int');
		$page     = IFilter::act(IReq::get('page'),'int') ? IReq::get('page') : 1;

		$orderGoodsDB = new IQuery('order_goods as og');
		$orderGoodsDB->join   = 'left join order as o on og.order_id = o.id left join user as u on o.user_id = u.id';
		$orderGoodsDB->fields = 'o.user_id,og.goods_price,og.goods_nums,o.create_time as completion_time,u.username';
		$orderGoodsDB->where  = 'og.goods_id = '.$goods_id.' and o.status in (5,2)';
		$orderGoodsDB->order  = 'o.create_time desc';
		$orderGoodsDB->page   = $page;

		$data = $orderGoodsDB->find();
		$pageHtml = $orderGoodsDB->getPageBar("javascript:void(0);",'onclick="history_ajax([page])"');

		echo JSON::encode(array('data' => $data,'pageHtml' => $pageHtml));
	}

	//讨论数据ajax获取
	function discuss_ajax()
	{
		$goods_id = IFilter::act(IReq::get('goods_id'),'int');
		$page     = IFilter::act(IReq::get('page'),'int') ? IReq::get('page') : 1;

		$discussDB = new IQuery('discussion as d');
		$discussDB->join = 'left join user as u on d.user_id = u.id';
		$discussDB->where = 'd.goods_id = '.$goods_id;
		$discussDB->order = 'd.id desc';
		$discussDB->fields = 'u.username,d.time,d.contents';
		$discussDB->page = $page;

		$data = $discussDB->find();
		$pageHtml = $discussDB->getPageBar("javascript:void(0);",'onclick="discuss_ajax([page])"');

		echo JSON::encode(array('data' => $data,'pageHtml' => $pageHtml));
	}

	//买前咨询数据ajax获取
	function refer_ajax()
	{
		$goods_id = IFilter::act(IReq::get('goods_id'),'int');
		$page     = IFilter::act(IReq::get('page'),'int') ? IReq::get('page') : 1;

		$referDB = new IQuery('refer as r');
		$referDB->join = 'left join user as u on r.user_id = u.id';
		$referDB->where = 'r.goods_id = '.$goods_id;
		$referDB->order = 'r.id desc';
		$referDB->fields = 'u.username,u.head_ico,r.time,r.question,r.reply_time,r.answer';
		$referDB->page = $page;

		$data = $referDB->find();
		$pageHtml = $referDB->getPageBar("javascript:void(0);",'onclick="refer_ajax([page])"');

		echo JSON::encode(array('data' => $data,'pageHtml' => $pageHtml));
	}

	//评论列表页
	function comments_list()
	{
		$id   = IFilter::act(IReq::get("id"),'int');
		$type = IFilter::act(IReq::get("type"));
		$data = array();

		//评分级别
		$type_config = array('bad'=>'1','middle'=>'2,3,4','good'=>'5');
		$point       = isset($type_config[$type]) ? $type_config[$type] : "";

		//查询评价数据
		$this->commentQuery = Api::run('getListByGoods',$id,$point);
		$this->commentCount = Comment_Class::get_comment_info($id);
		$this->goods        = Api::run('getGoodsInfo',array("#id#",$id));
		if(!$this->goods)
		{
			IError::show("商品信息不存在");
		}
		$this->redirect('comments_list');
	}

	//提交评论页
	function comments()
	{
		$id = IFilter::act(IReq::get('id'),'int');

		if(!$id)
		{
			IError::show(403,"传递的参数不完整");
		}

		if(!isset($this->user['user_id']) || $this->user['user_id']==null )
		{
			IError::show(403,"登录后才允许评论");
		}

		$result = Comment_Class::can_comment($id,$this->user['user_id']);
		if(is_string($result))
		{
			IError::show(403,$result);
		}

		$this->comment      = $result;
		$this->commentCount = Comment_Class::get_comment_info($result['goods_id']);
		$this->goods        = Comment_Class::goodsInfo($id);
		if(!$this->goods)
		{
			IError::show("商品信息不存在");
		}
		$this->redirect("comments");
	}

	/**
	 * @brief 进行商品评论 ajax操作
	 */
	public function comment_add()
	{
		$id      = IFilter::act(IReq::get('id'),'int');
		$content = IFilter::act(IReq::get("contents"));
		$img_list= IFilter::act(IReq::get("_imgList"));

		if(!$id || !$content)
		{
			IError::show(403,"填写完整的评论内容");
		}

		if(!isset($this->user['user_id']) || !$this->user['user_id'])
		{
			IError::show(403,"未登录用户不能评论");
		}

		$data = array(
			'point'        => IFilter::act(IReq::get('point'),'float'),
			'contents'     => $content,
			'status'       => 1,
			'img_list'     => '',
			'comment_time' => ITime::getNow("Y-m-d"),
		);

		if($data['point']==0)
		{
			IError::show(403,"请选择分数");
		}

        if(isset($img_list) && $img_list)
        {
            $img_list   = trim($img_list,',');
            $img_list   = explode(",",$img_list);
            if(count($img_list) > 5){
                IError::show(403,"最多上传5张图片");
            }
            $img_list   = array_filter($img_list);
            $img_list   = JSON::encode($img_list);
            $data['img_list'] = $img_list;
        }

		$result = Comment_Class::can_comment($id,$this->user['user_id']);
		if(is_string($result))
		{
			IError::show(403,$result);
		}

		$tb_comment = new IModel("comment");
		$tb_comment->setData($data);
		$re         = $tb_comment->update("id={$id}");

		if($re)
		{
			$commentRow = $tb_comment->getObj('id = '.$id);

			//同步更新goods表,comments,grade
			$goodsDB = new IModel('goods');
			$goodsDB->setData(array(
				'comments' => 'comments + 1',
				'grade'    => 'grade + '.$commentRow['point'],
			));
			$goodsDB->update('id = '.$commentRow['goods_id'],array('grade','comments'));

			//同步更新seller表,comments,grade
			$sellerDB = new IModel('seller');
			$sellerDB->setData(array(
				'comments' => 'comments + 1',
				'grade'    => 'grade + '.$commentRow['point'],
			));
			$sellerDB->update('id = '.$commentRow['seller_id'],array('grade','comments'));
			$this->redirect("/site/comments_list/id/".$commentRow['goods_id']);
		}
		else
		{
			IError::show(403,"评论失败");
		}
	}

	function pic_show()
	{
		$this->layout="";

		$id   = IFilter::act(IReq::get('id'),'int');
		$item = Api::run('getGoodsInfo',array('#id#',$id));
		if(!$item)
		{
			IError::show(403,'商品信息不存在');
		}
		$photo = Api::run('getGoodsPhotoRelationList',array('#id#',$id));
		$this->setRenderData(array("id" => $id,"item" => $item,"photo" => $photo));
		$this->redirect("pic_show");
	}

	function help()
	{
		$id       = IFilter::act(IReq::get("id"),'int');
		$tb_help  = new IModel("help");
		$help_row = $tb_help->getObj($id);
		if($help_row)
		{
		    $tb_help_cat = new IModel("help_category");
		    $cat_row     = $tb_help_cat->getObj("id={$help_row['cat_id']}");

		    $this->cat_row  = $cat_row;
		    $this->help_row = $help_row;
		}

		if(!isset($cat_row) || !$cat_row)
		{
			IError::show(403,"帮助信息或者帮助分类不存在");
		}

		$this->redirect("help");
	}

	function help_list()
	{
		$id          = IFilter::act(IReq::get("id"),'int');
		$tb_help_cat = new IModel("help_category");
		$cat_row     = $tb_help_cat->getObj("id={$id}");

		//帮助分类数据存在
		if($cat_row)
		{
			$this->helpQuery = Api::run('getHelpListByCatId',$id);
			$this->cat_row   = $cat_row;
		}
		else
		{
			$this->helpQuery = Api::run('getHelpList');
			$this->cat_row   = array('id' => 0,'name' => '站点帮助');
		}
		$this->redirect("help_list");
	}

	//团购页面
	function groupon()
	{
		$id = IFilter::act(IReq::get("id"),'int');

		//指定某个团购
		if($id)
		{
			$this->regiment_list = Api::run('getRegimentRowById',array('#id#',$id));
			$this->regiment_list = $this->regiment_list ? array($this->regiment_list) : array();
		}
		else
		{
			$this->regiment_list = Api::run('getRegimentList');
		}

		if(!$this->regiment_list)
		{
			IError::show('当前没有可以参加的团购活动');
		}

		//往期团购
		$this->ever_list = Api::run('getEverRegimentList');
		$this->redirect("groupon");
	}

	//品牌列表页面
	function brand()
	{
		$id   = IFilter::act(IReq::get('id'),'int');
		$name = IFilter::act(IReq::get('name'));
		$this->setRenderData(array('id' => $id,'name' => $name));
		$this->redirect('brand');
	}

	//品牌专区页面
	function brand_zone()
	{
		$brandId  = IFilter::act(IReq::get('id'),'int');
		$brandRow = Api::run('getBrandInfo',$brandId);
		if(!$brandRow)
		{
			IError::show(403,'品牌信息不存在');
		}
		$this->setRenderData(array('brandId' => $brandId,'brandRow' => $brandRow));
		$this->redirect('brand_zone');
	}

	//商家主页
	function home()
	{
		$seller_id = IFilter::act(IReq::get('id'),'int');
		$cat_id    = IFilter::act(IReq::get('cat'),'int');
		$sellerRow = Api::run('getSellerInfo',$seller_id);
		if(!$sellerRow)
		{
			IError::show(403,'商户信息不存在');
		}
		$this->setRenderData(array('sellerRow' => $sellerRow,'seller_id' => $seller_id,'cat_id' => $cat_id));
		$this->redirect('home');
	}


	/***
	 *                 .-~~~~~~~~~-._       _.-~~~~~~~~~-.
	 *             __.'              ~.   .~              `.__
	 *           .'//                  \./                  \\`.
	 *         .'//                     |                     \\`.
	 *       .'// .-~"""""""~~~~-._     |     _,-~~~~"""""""~-. \\`.
	 *     .'//.-"                 `-.  |  .-'                 "-.\\`.
	 *   .'//______.============-..   \ | /   ..-============.______\\`.
	 * .'______________________________\|/______________________________`.
	 *
	 */

	//  分类商品列表
	function goods_list()
	{
		$cat_id = IFilter::act(IReq::get('id'), 'int');
		$page = IFilter::act(IReq::get('page'), 'int');

		$result = Ydui::api('getBothGoods', array('cat_id'=>$cat_id, 'page'=>$page));

		$goodsArr = array();
		$pageHtml = null;
		if($result['status'] == 'success' && $result['data']) {
			$goodsArr = $result['data']['goods'];
			$pageHtml = $result['data']['page'];
		}
		$this->goods = $goodsArr;
		$this->pageHtml = $pageHtml;
		$this->redirect('goods_list');
	}

	// 商品详情
	function item()
	{
		$id = IFilter::act(IReq::get('id'), 'int');
		$result = Ydui::api('bothProducts', array('id'=>$id));

		$goodsArr = array();
		if($result['status'] == 'success' && $result['data']) {
			$goodsArr = $result['data'];
		}
		else {
			IError::show(403, '当前商品已经下架啦~');
		}
		$this->goods = $goodsArr;
		$this->redirect('item');
	}
	
	// 加购物车
	function joinCart()
	{
		$user_id = $this->user['user_id'];
		if (!$user_id) {
			echo JSON::encode(array('flag' => 'fail', 'data' => '请先登录'));
			return;
		}

    	$link      = IReq::get('link');
		$goods_id  = IFilter::act(IReq::get('goods_id'), 'int');
		$goods_num = IFilter::act(IReq::get('goods_num'), 'int');
		$goods_num = $goods_num == 0 ? 1 : $goods_num;
		$type      = IFilter::act(IReq::get('type'));

		// 写购物车表
		$cart = new Cart('Ydui');
		$addResult = $cart->add($goods_id, $goods_num, $type);
		if ($link != '') {
			if ($addResult === false) {
				$this->cart(false);
				Util::showMessage($cart->getError());
			} else {
				$this->redirect($link);
			}
		} else {
			if ($addResult === false) {
				$result = array(
					'flag' => 'fail',
					'message' => $cart->getError(),
				);
			} else {
				$result = array(
					'flag' => 'success',
					'message' => '添加成功',
				);
			}
			echo JSON::encode($result);
		}
	}

	// 购物车展示
	function showCart()
    {
    	$cartObj  = new Cart('Ydui');
    	$cartList = $cartObj->getMyCart();
    	$data['count']= $cartList['count'];
    	$data['sum']  = $cartList['sum'];
    	echo JSON::encode($data);
	}

	//购物车页面及商品价格计算[复杂]
	function cart()
	{
		//防止页面刷新
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);

		//开始计算购物车中的商品价格
		$cartObj  = new Cart('Ydui');

		$result = Api::run('getYduiGoodsCount', $cartObj->getMyCart(true));

		if(is_string($result))
		{
			IError::show($result,403);
		}

		//返回值
		$this->final_sum = $result['final_sum'];
		$this->goodsList = $result['goodsList'];
		$this->count     = $result['count'];
		$this->weight    = $result['weight'];

		//渲染视图
		$this->redirect('cart');
	}

	// 计算购物车中未选中的商品
	public function exceptCartGoodsAjax()
	{
		$data    = IFilter::act(IReq::get('data'));
		$data    = $data ? join(",",$data) : "";
		$cartObj = new Cart('Ydui');
		$result  = $cartObj->setUnselected($data);

		echo JSON::encode(array("result" => $result));
	}

	// 计算购物车选中的商品金额和明细[ajax]
	function promotionRuleAjax()
	{
		$cartObj = new Cart('Ydui');
		$buyInfo = $cartObj->getMyCart(false);
    	$countSumResult = Api::run('getYduiGoodsCount', $buyInfo);
		echo JSON::encode($countSumResult);
	}
	
	// 订单结算
	function cart2()
	{
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0",false);
		$id        = IFilter::act(IReq::get('id'),'int');
		$type      = IFilter::act(IReq::get('type'));//goods,product
		$buy_num   = IReq::get('num') ? IFilter::act(IReq::get('num'),'int') : 1;
		$tourist   = IReq::get('tourist');//游客方式购物
		$origin    = IFilter::act(IReq::get('origin')); // Ydui商品

		if($origin !== 'Ydui') IError::show(403,'非法请求');

    	//必须为登录用户
    	if($tourist === null && $this->user['user_id'] == null)
    	{
    		if($id == 0 || $type == '')
    		{
    			$this->redirect('/simple/login?tourist&callback=/site/cart2');
    		}
    		else
    		{
    			$url = '/simple/login?tourist&callback=/site/cart2/id/'.$id.'/type/'.$type.'/num/'.$buy_num;
    			$this->redirect($url);
    		}
		}

		//游客的user_id默认为0
    	$user_id = ($this->user['user_id'] == null) ? 0 : $this->user['user_id'];

		// 必须是vip用户
		$userRow = Team::getVipInfoByUserId($user_id);
		if(!$userRow) IError::show(403, '请先成为vip会员再进行购买');

		//计算商品 通过Ydui
		if(!$id) {
			// 购物车结算
			$cartObj = new Cart('Ydui');
			$buyInfo = $cartObj->getMyCart(false);
			$result = Api::run('getYduiGoodsCount', $buyInfo, true);
		}
		else {
			// 购买单品结算
			$result = Ydui::getData('getCountum', array('buyInfo' => array('id'=>$id,'type'=>$type,'buy_num'=>$buy_num)));
		}

		if($result['error'])
		{
			IError::show(403,$result['error']);
		}

    	//获取收货地址
    	$addressObj  = new IModel('address');
		$addressList = $addressObj->query('user_id = '.$user_id,"*","is_default desc");
		
		// 默认地址
		$defaultAddress = $addressObj->getObj('user_id = '.$user_id. ' and is_default = 1');

		//更新$addressList数据
    	foreach($addressList as $key => $val)
    	{
    		$temp = area::name($val['province'],$val['city'],$val['area']);
    		if(isset($temp[$val['province']]) && isset($temp[$val['city']]) && isset($temp[$val['area']]))
    		{
	    		$addressList[$key]['province_str'] = $temp[$val['province']];
	    		$addressList[$key]['city_str']     = $temp[$val['city']];
	    		$addressList[$key]['area_str']     = $temp[$val['area']];
    		}
		}
		
		// 更新默认地址数据
		if($defaultAddress) {
			$temp = area::name($defaultAddress['province'],$defaultAddress['city'],$defaultAddress['area']);
			if($temp) {
				$defaultAddress['province_str'] = $temp[$defaultAddress['province']];
				$defaultAddress['city_str']     = $temp[$defaultAddress['city']];
				$defaultAddress['area_str']     = $temp[$defaultAddress['area']];
			}
		}

		// 用户的可用优惠
		$this->revisit = $userRow['revisit'];

    	//返回值
		$this->gid       = $id;
		$this->type      = $type;
		$this->num       = $buy_num;
    	$this->final_sum = $result['final_sum'];
    	$this->promotion = $result['promotion'];
    	$this->proReduce = $result['proReduce'];
    	$this->sum       = $result['sum'];
    	$this->goodsList = $result['goodsList'];
    	$this->count       = $result['count'];
    	$this->reduce      = $result['reduce'];
    	$this->weight      = $result['weight'];
    	$this->freeFreight = $result['freeFreight'];
    	$this->sellerData  = $result['seller'];
    	$this->spend_point = $result['spend_point'];
    	$this->goodsType   = $result['goodsType'];

		//自提点列表
		$this->takeselfList = $result['takeself'];

		//收货地址列表
		$this->addressList = $addressList;

		// 默认地址
		$this->defaultAddress = $defaultAddress;

		//获取商品税金
		$this->goodsTax    = $result['tax'];

    	//渲染页面
    	$this->redirect('cart2');
	}

	/**
	 * 生成订单和支付 复杂
	 */
    function cart3()
    {
		//防止表单重复提交
    	if(IReq::get('timeKey'))
    	{
    		if(ISafe::get('timeKey') == IReq::get('timeKey'))
    		{
	    		IError::show(403,'订单数据不能被重复提交');
    		}
    		ISafe::set('timeKey',IReq::get('timeKey'));
    	}

    	$address_id    = IFilter::act(IReq::get('radio_address'),'int');
    	$delivery_id   = IFilter::act(IReq::get('delivery_id'),'int');
    	$accept_time   = IFilter::act(IReq::get('accept_time'));
    	$payment       = IFilter::act(IReq::get('payment'),'int');
    	$accept_name   = IFilter::act(IReq::get('accept_name'));
    	$order_message = IFilter::act(IReq::get('message'));
    	$gid           = IFilter::act(IReq::get('direct_gid'),'int');
    	$num           = IFilter::act(IReq::get('direct_num'),'int');
    	$type          = IFilter::act(IReq::get('direct_type'));//商品或者货品 goods / products
    	$dataArray     = [];
		$user_id       = ($this->user['user_id'] == null) ? 0 : $this->user['user_id'];
		
		$origin        = IFilter::act(IReq::get('origin'));
    	$revisit       = IFilter::act(IReq::get('revisit'),'float');

		//计算商品 通过Ydui
		if(!$gid) {
			// 购物车结算
			$cartObj = new Cart('Ydui');
			$buyInfo = $cartObj->getMyCart(false);
			$goodsResult = Api::run('getYduiGoodsCount', $buyInfo, true);
		}
		else {
			// 购买单品结算
			$goodsResult = Ydui::getData('getCountum', array('buyInfo' => array('id'=>$gid,'type'=>$type,'buy_num'=>$num)));
			if($goodsResult['goodsList'] && $goodsResult['goodsList'][0]) $productID = [$goodsResult['goodsList'][0]['product_id']];
		}

		if($goodsResult['error'])
		{
			echo JSON::encode(array('flag'=>'fail','msg'=>$goodsResult['error']));
			return;
		}

		//1,访客; 2,注册用户
		if($user_id == 0)
		{
			$addressRow = ISafe::get('address');
		}
		else
		{
			$addressDB   = new IModel('address');
			$addressRow  = $addressDB->getObj('id = '.$address_id.' and user_id = '.$user_id);
		}

		//配送方式
		$deliveryObj = new IModel('delivery');
		$deliveryRow = $deliveryObj->getObj($delivery_id);

		if (!$deliveryRow)
		{
			echo JSON::encode(array('flag'=>'fail','msg'=>'配送方式不存在'));
			return;
		}

		//1,在线支付
		if($deliveryRow['type'] == 0 && $payment == 0)
		{
			echo JSON::encode(array('flag'=>'fail','msg'=>'请选择正确的支付方式'));
			return;
		}
		//2,货到付款
		else if($deliveryRow['type'] == 1)
		{
			$payment = 0;
		}

		if(!$addressRow)
		{
			echo JSON::encode(array('flag'=>'fail','msg'=>'收货地址信息不存在'));
			return;
		}

		// 优惠金额判断是否够
		$userDB = new IModel('user');
		$userRow = $userDB->getObj('id = '.$user_id);
		if(!$userRow) {
			IError::show(403, '必须是登录用户才能下单');
		}

		// 地址信息 从库中读取的
		$accept_name   = IFilter::act($addressRow['accept_name'],'name');
		$province      = $addressRow['province'];
		$city          = $addressRow['city'];
		$area          = $addressRow['area'];
		$address       = IFilter::act($addressRow['address']);
		$mobile        = IFilter::act($addressRow['mobile'],'mobile');
		$telphone      = isset($addressRow['telphone']) ? IFilter::act($addressRow['telphone'],'phone') : "";
		$zip           = isset($addressRow['zip']) ? IFilter::act($addressRow['zip'],'zip') : "";

		// 购物车结算
		if(!$gid) {
			$cartObj = new Cart('Ydui');
			$buyInfo = $cartObj->getMyCart(false);

			$goodsId = [];
			$productId = [];
			$num = [];
			if($buyInfo['goods'] && $buyInfo['goods']['data']) {
				foreach ($buyInfo['goods']['data'] as $key => $goods) {
					$goodsId[] = $goods['goods_id'];
					$productId[] = 0;
					$num[] = $goods['count'];
				}
			}

			if($buyInfo['product'] && $buyInfo['product']['data']) {
				foreach ($buyInfo['product']['data'] as $key => $products) {
					$goodsId[] = $products['goods_id'];
					$productId[] = $products['id'];
					$num[] = $products['count'];
				}
			}

			$data = Delivery::getDelivery($province,$deliveryRow['id'],$goodsId,$productId,$num,$origin);
		}
		else {
			// 单品直接购买
			$data = Delivery::getDelivery($province,$deliveryRow['id'],$gid,$productID,$num,$origin);
		}

		//检查订单重复
    	$checkData = array(
    		"mobile" => $mobile,
    	);
    	$result = order_class::checkRepeat($checkData,$goodsResult['goodsList']);
    	if(is_string($result))
    	{
			echo JSON::encode(array('flag'=>false,'msg'=>$result));
			return;
    	}
		if(!$gid)
		{
			//清空购物车
			$cartObj = new Cart('Ydui');
			$cartObj->clear();
		}

    	//判断商品是否存在
    	if(is_string($goodsResult) || !$goodsResult['goodsList'])
    	{
			echo JSON::encode(array('flag'=>'fail','msg'=>'商品数据不存在'));
			return;
    	}

		$paymentObj = new IModel('payment');
		$paymentRow = $paymentObj->getObj('id = '.$payment,'type,name');

		if(!$paymentRow)
		{
			echo JSON::encode(array('flag'=>'fail','msg'=>'支付方式不存在'));
			return;
		}

		// 初始化最终金额为实际金额
		$realGoodsAmount = $goodsResult['final_sum'];
		$realDeliveryAmount = $data['price'];

		// 存在使用了优惠
		if($revisit > 0) {
			// 不能超过实际可用金额
			if($userRow['revisit'] < $revisit) {
				echo JSON::encode(array('flag'=>'fail','msg'=>'可用优惠金额不足'));
				return;
			}

			// 总金额 商品金额 + 运费金额
			$allAmount = round($goodsResult['final_sum'] + $data['price'], 2);

			// 如果优惠金额大于总金额 实付优惠金额 = 总金额
			if($revisit > $allAmount) $revisit = $allAmount;

			$realGoodsAmount = $realGoodsAmount - $revisit;

			if($realGoodsAmount <= 0) {
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
		);

		$dataArray['order_amount'] = $dataArray['order_amount'] <= 0 ? 0 : $dataArray['order_amount'];

		//生成订单插入order表中
		$orderObj  = new IModel('order');
		$orderObj->setData($dataArray);
		$order_id = $orderObj->add();

		if($order_id == false)
		{
			echo JSON::encode(array('flag'=>'fail','msg'=>'订单生成错误'));
			return;
		}

		/*将订单中的商品插入到order_goods表*/
		$orderInstance = new Order_Class();
		$orderGoodsResult = $orderInstance->insertOrderGoods($order_id,$goodsResult);
		if($orderGoodsResult !== true)
		{
			echo JSON::encode(array('flag'=>'fail','msg'=>$orderGoodsResult));
			return;
		}
		
		// 如果使用了优惠
		if($revisit > 0)
		{
			// 还剩余优惠的金额
			$update = $userRow['revisit'] - $revisit;
			$res = $userDB->setData(array('revisit' => $update))->update('id = '.$user_id);

			if($res) {
				$log = array(
					'user_id'   => $user_id,
					'type'      => '1',
					'time'      => ITime::getDateTime(),
					'value'     => $revisit,
					'value_log' => $update,
					'note'      => '用户: '.$userRow['username']. ' 在订单号：'. $dataArray['order_no'] . ' 使用了优惠 '. $revisit, 
				);
				$logDB = new IModel('revisit_log');
				$logDB->setData($log)->add();
			}
		}
		
		//收货地址的处理
		if($user_id && $address_id)
		{
			$addressDefRow = $addressDB->getObj('user_id = '.$user_id.' and is_default = 1');
			if(!$addressDefRow)
			{
				$addressDB->setData(array('is_default' => 1));
				$addressDB->update('user_id = '.$user_id.' and id = '.$address_id);
			}
		}

		//订单金额小于等于0直接免单
		if($dataArray['order_amount'] <= 0)
		{
			Order_Class::updateOrderStatus($dataArray['order_no']);
			plugin::trigger('setCallback','/ucenter/order');
			echo JSON::encode(array('flag'=>'success','status'=>'finish','url'=>'/site/success/message/'.urlencode("订单确认成功，等待发货")));
			return;
		}
		else
		{
			echo JSON::encode(array('flag'=>'success','order_id'=>$order_id));
			return;
		}
	}
	
	// app下载
	function app_download()
	{
		$version = Api::run('getAppVersion');

        $file_path = '/public/app/';

        $file_name = 'app_'.$version.'.apk';

		$locationUrl = IUrl::creatUrl($file_path.$file_name);
		header('location: '.$locationUrl);
		exit;
	}
}
