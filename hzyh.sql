/*
 Navicat Premium Data Transfer

 Source Server         : rds
 Source Server Type    : MySQL
 Source Server Version : 50720
 Source Host           : rm-bp187mg1x0k70k3s6oo.mysql.rds.aliyuncs.com:3306
 Source Schema         : hzyh

 Target Server Type    : MySQL
 Target Server Version : 50720
 File Encoding         : 65001

 Date: 29/10/2019 10:19:44
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for yh_account_log
-- ----------------------------
DROP TABLE IF EXISTS `yh_account_log`;
CREATE TABLE `yh_account_log`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '管理员ID',
  `user_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '用户id',
  `type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0增加,1减少',
  `event` tinyint(3) NOT NULL COMMENT '操作类型，意义请看accountLog类 报单(11分享佣金,12level奖励,13分红),订单(14零售佣金,15返现佣金),16agent奖励(报单),订单(17agent佣金,18订单分红),21,提现手续费,22转出,23转入',
  `time` datetime(0) NOT NULL COMMENT '发生时间',
  `amount` decimal(15, 2) NOT NULL COMMENT '金额',
  `amount_log` decimal(15, 2) NOT NULL COMMENT '每次增减后面的金额记录',
  `note` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '备注',
  `from_uid` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '奖励来源uid',
  `from_oid` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单奖励来源oid',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `admin_id`(`admin_id`) USING BTREE,
  CONSTRAINT `yh_account_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `yh_user` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE = InnoDB AUTO_INCREMENT = 844360 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '账户余额日志表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yh_ad_manage
-- ----------------------------
DROP TABLE IF EXISTS `yh_ad_manage`;
CREATE TABLE `yh_ad_manage`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '广告ID',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '广告名称',
  `type` tinyint(1) NOT NULL COMMENT '广告类型 1:img 2:flash 3:文字 4:code',
  `position_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '广告位ID',
  `link` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '链接地址',
  `order` smallint(5) NOT NULL DEFAULT 0 COMMENT '排列顺序',
  `start_time` date NULL DEFAULT NULL COMMENT '开始时间',
  `end_time` date NULL DEFAULT NULL COMMENT '结束时间',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '图片、flash路径，文字，code等',
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '描述',
  `goods_cat_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '绑定的商品分类ID',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `position_id`(`position_id`) USING BTREE,
  INDEX `order`(`order`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '广告记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_ad_position
-- ----------------------------
DROP TABLE IF EXISTS `yh_ad_position`;
CREATE TABLE `yh_ad_position`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '广告位ID',
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '广告位名称',
  `width` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '广告位宽度,px或者%',
  `height` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '广告位高度,px或者%',
  `fashion` tinyint(1) NOT NULL COMMENT '1:轮显;2:随即',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1:开启; 0: 关闭',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `name`(`name`, `status`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '广告位记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_address
-- ----------------------------
DROP TABLE IF EXISTS `yh_address`;
CREATE TABLE `yh_address`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `accept_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '收货人姓名',
  `zip` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '邮编',
  `telphone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '联系电话',
  `country` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '国ID',
  `province` int(11) UNSIGNED NOT NULL COMMENT '省ID',
  `city` int(11) UNSIGNED NOT NULL COMMENT '市ID',
  `area` int(11) UNSIGNED NOT NULL COMMENT '区ID',
  `address` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '收货地址',
  `mobile` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '手机',
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否默认,0:为非默认,1:默认',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  CONSTRAINT `yh_address_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `yh_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 2210 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '收货信息表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_admin
-- ----------------------------
DROP TABLE IF EXISTS `yh_admin`;
CREATE TABLE `yh_admin`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `admin_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户名',
  `password` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '密码',
  `role_id` int(11) UNSIGNED NOT NULL COMMENT '角色ID',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Email',
  `last_ip` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '最后登录IP',
  `last_time` datetime(0) NULL DEFAULT NULL COMMENT '最后登录时间',
  `is_del` tinyint(1) NOT NULL DEFAULT 0 COMMENT '删除状态 1删除,0正常',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `admin_name`(`admin_name`) USING BTREE,
  INDEX `role_id`(`role_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '管理员用户表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_admin_role
-- ----------------------------
DROP TABLE IF EXISTS `yh_admin_role`;
CREATE TABLE `yh_admin_role`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '角色名称',
  `rights` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '权限',
  `is_del` tinyint(1) NOT NULL DEFAULT 0 COMMENT '删除状态 1删除,0正常',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '后台角色分组表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_announcement
-- ----------------------------
DROP TABLE IF EXISTS `yh_announcement`;
CREATE TABLE `yh_announcement`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '公告标题',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '公告内容',
  `time` datetime(0) NOT NULL COMMENT '发布时间',
  `keywords` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '关键词',
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '描述',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '公告消息表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_areas
-- ----------------------------
DROP TABLE IF EXISTS `yh_areas`;
CREATE TABLE `yh_areas`  (
  `area_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) UNSIGNED NOT NULL COMMENT '上一级的id值',
  `area_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '地区名称',
  `sort` int(10) UNSIGNED NOT NULL DEFAULT 99 COMMENT '排序',
  PRIMARY KEY (`area_id`) USING BTREE,
  INDEX `area_name`(`area_name`) USING BTREE,
  INDEX `parent_id`(`parent_id`) USING BTREE,
  INDEX `sort`(`sort`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 659004406 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '地区信息' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_article
-- ----------------------------
DROP TABLE IF EXISTS `yh_article`;
CREATE TABLE `yh_article`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '内容',
  `category_id` int(11) UNSIGNED NOT NULL COMMENT '分类ID',
  `create_time` datetime(0) NOT NULL COMMENT '发布时间',
  `keywords` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '关键词',
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '描述',
  `visibility` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否显示 0:不显示,1:显示',
  `top` tinyint(1) NOT NULL DEFAULT 0 COMMENT '置顶',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `style` tinyint(1) NOT NULL DEFAULT 0 COMMENT '标题字体 0正常 1粗体,2斜体',
  `color` varchar(7) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '标题颜色',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `sort`(`sort`) USING BTREE,
  INDEX `category_id`(`category_id`, `visibility`) USING BTREE,
  INDEX `visibility`(`visibility`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文章表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_article_category
-- ----------------------------
DROP TABLE IF EXISTS `yh_article_category`;
CREATE TABLE `yh_article_category`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '分类名称',
  `parent_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父分类',
  `issys` tinyint(1) NOT NULL DEFAULT 0 COMMENT '系统分类',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `path` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '路径',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'SEO标题',
  `keywords` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'SEO关键词和检索关键词',
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'SEO描述',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `parent_id`(`parent_id`) USING BTREE,
  INDEX `sort`(`sort`) USING BTREE,
  INDEX `issys`(`issys`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文章分类' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_attribute
-- ----------------------------
DROP TABLE IF EXISTS `yh_attribute`;
CREATE TABLE `yh_attribute`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `model_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '模型ID',
  `type` tinyint(1) NULL DEFAULT NULL COMMENT '输入控件的类型,1:单选,2:复选,3:下拉,4:输入框',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '名称',
  `value` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '属性值(逗号分隔)',
  `search` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否支持搜索0不支持1支持',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `model_id`(`model_id`, `search`) USING BTREE,
  CONSTRAINT `yh_attribute_ibfk_1` FOREIGN KEY (`model_id`) REFERENCES `yh_model` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '属性表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_bank_card
-- ----------------------------
DROP TABLE IF EXISTS `yh_bank_card`;
CREATE TABLE `yh_bank_card`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NULL DEFAULT NULL COMMENT '用户id',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '卡类型，1.银行卡，2.',
  `province` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '开户行所在省',
  `city` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '开户行所在市',
  `bank` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '开户行',
  `bank_branch` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '开户支行',
  `card_num` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '卡号',
  `name` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '户名',
  `is_del` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0.正常，1.删除',
  `area` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '开户行所在地区',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1680 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_banner
-- ----------------------------
DROP TABLE IF EXISTS `yh_banner`;
CREATE TABLE `yh_banner`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order` smallint(5) UNSIGNED NOT NULL COMMENT '排序',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'Banner名称',
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '链接地址',
  `img` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '图片文件',
  `type` enum('mobile','pc') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'pc' COMMENT '类型,pc:电脑端;mobile:手机端',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  `seller_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商家ID',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `seller_id`(`seller_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '幻灯片表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_bill
-- ----------------------------
DROP TABLE IF EXISTS `yh_bill`;
CREATE TABLE `yh_bill`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `seller_id` int(11) UNSIGNED NOT NULL COMMENT '商家ID',
  `apply_time` datetime(0) NULL DEFAULT NULL COMMENT '申请结算时间',
  `pay_time` datetime(0) NULL DEFAULT NULL COMMENT '支付结算时间',
  `admin_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '管理员ID',
  `is_pay` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0:未结算,1:已结算',
  `apply_content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '申请结算文本',
  `pay_content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '支付结算文本',
  `start_time` date NULL DEFAULT NULL COMMENT '结算起始时间',
  `end_time` date NULL DEFAULT NULL COMMENT '结算终止时间',
  `log` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '结算明细',
  `order_ids` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'order表主键ID，结算的ID',
  `amount` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '结算的金额',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `seller_id`(`seller_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商家货款结算单表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_brand
-- ----------------------------
DROP TABLE IF EXISTS `yh_brand`;
CREATE TABLE `yh_brand`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '品牌ID',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '品牌名称',
  `logo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'logo地址',
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '网址',
  `description` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '描述',
  `sort` smallint(5) NOT NULL DEFAULT 0 COMMENT '排序',
  `category_ids` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '品牌分类,逗号分割id',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `sort`(`sort`) USING BTREE,
  INDEX `category_ids`(`category_ids`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '品牌表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_brand_category
-- ----------------------------
DROP TABLE IF EXISTS `yh_brand_category`;
CREATE TABLE `yh_brand_category`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '分类名称',
  `goods_category_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品分类ID',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `goods_category_id`(`goods_category_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '品牌分类表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_call_log
-- ----------------------------
DROP TABLE IF EXISTS `yh_call_log`;
CREATE TABLE `yh_call_log`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '用户id',
  `caller` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '主叫',
  `answer` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '被叫',
  `time` datetime(0) NOT NULL COMMENT '发生时间',
  `end_time` datetime(0) NULL DEFAULT NULL COMMENT '结束时间',
  `time_count` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '通话时长',
  `note` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '备注',
  `answer_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '对方通讯录名称',
  `active_uid` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '要激活会员的uid',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4109 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '电话记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_call_rechange
-- ----------------------------
DROP TABLE IF EXISTS `yh_call_rechange`;
CREATE TABLE `yh_call_rechange`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '用户id',
  `mobile` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '手机号',
  `card_no` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '卡号',
  `pass` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '密码',
  `time` datetime(0) NULL DEFAULT NULL COMMENT '充值时间',
  `note` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 773 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '电话充值记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_call_register
-- ----------------------------
DROP TABLE IF EXISTS `yh_call_register`;
CREATE TABLE `yh_call_register`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '用户id',
  `mobile` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '手机号',
  `time` datetime(0) NULL DEFAULT NULL COMMENT '注册时间',
  `token` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'token',
  `note` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 32526 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '电话注册记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_cash_back_log
-- ----------------------------
DROP TABLE IF EXISTS `yh_cash_back_log`;
CREATE TABLE `yh_cash_back_log`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '管理员ID',
  `user_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '用户id',
  `type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0增加,1减少',
  `datetime` datetime(0) NOT NULL COMMENT '发生时间',
  `value` decimal(15, 2) NOT NULL COMMENT '金额',
  `value_log` decimal(15, 2) NOT NULL COMMENT '每次增减后面的金额记录',
  `note` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '备注',
  `from_oid` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '来源订单id',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `admin_id`(`admin_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 506 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户订单金额变动日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_category
-- ----------------------------
DROP TABLE IF EXISTS `yh_category`;
CREATE TABLE `yh_category`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '分类名称',
  `parent_id` int(11) UNSIGNED NOT NULL COMMENT '父分类ID',
  `sort` smallint(5) NOT NULL DEFAULT 0 COMMENT '排序',
  `visibility` tinyint(1) NOT NULL DEFAULT 1 COMMENT '首页是否显示 1显示 0 不显示',
  `keywords` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'SEO关键词和检索关键词',
  `descript` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'SEO描述',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'SEO标题title',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `parent_id`(`parent_id`, `visibility`) USING BTREE,
  INDEX `sort`(`sort`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '产品分类表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_category_extend
-- ----------------------------
DROP TABLE IF EXISTS `yh_category_extend`;
CREATE TABLE `yh_category_extend`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) UNSIGNED NOT NULL COMMENT '商品ID',
  `category_id` int(11) UNSIGNED NOT NULL COMMENT '商品分类ID',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `goods_id`(`goods_id`) USING BTREE,
  INDEX `category_id`(`category_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品与其分类关系表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_category_extend_seller
-- ----------------------------
DROP TABLE IF EXISTS `yh_category_extend_seller`;
CREATE TABLE `yh_category_extend_seller`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) UNSIGNED NOT NULL COMMENT '商品ID',
  `category_id` int(11) UNSIGNED NOT NULL COMMENT '商品分类ID',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `goods_id`(`goods_id`) USING BTREE,
  INDEX `category_id`(`category_id`) USING BTREE,
  CONSTRAINT `yh_category_extend_seller_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `yh_category_seller` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商家店内商品分类与商品关系表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_category_rate
-- ----------------------------
DROP TABLE IF EXISTS `yh_category_rate`;
CREATE TABLE `yh_category_rate`  (
  `category_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品分类ID',
  `category_rate` decimal(15, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商品分类手续费',
  PRIMARY KEY (`category_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品分类手续费设置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_category_seller
-- ----------------------------
DROP TABLE IF EXISTS `yh_category_seller`;
CREATE TABLE `yh_category_seller`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '分类名称',
  `parent_id` int(11) UNSIGNED NOT NULL COMMENT '父分类ID',
  `sort` smallint(5) NOT NULL DEFAULT 0 COMMENT '排序',
  `keywords` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'SEO关键词和检索关键词',
  `descript` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'SEO描述',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'SEO标题title',
  `seller_id` int(11) UNSIGNED NOT NULL COMMENT '商家ID',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `parent_id`(`parent_id`) USING BTREE,
  INDEX `sort`(`sort`) USING BTREE,
  INDEX `seller_id`(`seller_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商家店内商品分类表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_collection_doc
-- ----------------------------
DROP TABLE IF EXISTS `yh_collection_doc`;
CREATE TABLE `yh_collection_doc`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(11) UNSIGNED NOT NULL COMMENT '订单号',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `amount` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '金额',
  `time` datetime(0) NOT NULL COMMENT '时间',
  `payment_id` int(11) UNSIGNED NOT NULL COMMENT '支付方式ID',
  `admin_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '管理员id',
  `pay_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '支付状态，0:准备，1:支付成功',
  `note` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '收款备注',
  `if_del` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0:未删除 1:删除',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `order_id`(`order_id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `if_del`(`if_del`) USING BTREE,
  INDEX `payment_id`(`payment_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4952 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '收款单' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_commend_goods
-- ----------------------------
DROP TABLE IF EXISTS `yh_commend_goods`;
CREATE TABLE `yh_commend_goods`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `commend_id` int(11) UNSIGNED NOT NULL COMMENT '推荐类型ID 1:最新商品 2:特价商品 3:热卖排行 4:推荐商品',
  `goods_id` int(11) UNSIGNED NOT NULL COMMENT '商品ID',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `goods_id`(`goods_id`) USING BTREE,
  INDEX `commend_id`(`commend_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '推荐类商品' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_comment
-- ----------------------------
DROP TABLE IF EXISTS `yh_comment`;
CREATE TABLE `yh_comment`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) UNSIGNED NOT NULL COMMENT '商品ID',
  `order_no` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '订单编号',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `time` datetime(0) NOT NULL COMMENT '购买时间',
  `comment_time` date NOT NULL COMMENT '评论时间',
  `contents` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '评论内容',
  `recontents` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '回复评论内容',
  `recomment_time` date NOT NULL COMMENT '回复评论时间',
  `point` tinyint(1) NOT NULL DEFAULT 0 COMMENT '评论的分数',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '评论状态：0：未评论 1:已评论',
  `seller_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商家ID',
  `order_goods_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '订单商品表中的ID',
  `img_list` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '评价图片',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  `origin` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '远程商品',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `goods_id`(`goods_id`) USING BTREE,
  INDEX `order_goods_id`(`order_goods_id`) USING BTREE,
  INDEX `user_id`(`user_id`, `status`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1946 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品评论表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_cost_point
-- ----------------------------
DROP TABLE IF EXISTS `yh_cost_point`;
CREATE TABLE `yh_cost_point`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '活动名称',
  `sort` smallint(5) NOT NULL COMMENT '顺序',
  `goods_id` int(11) NOT NULL COMMENT '商品id',
  `point` int(11) NOT NULL COMMENT '所需要的积分',
  `is_close` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否关闭 0:否 1:是',
  `user_group` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '允许参与活动的用户组,all表示所有用户组',
  `seller_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商家ID',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `type`(`seller_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品积分兑换表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_delivery
-- ----------------------------
DROP TABLE IF EXISTS `yh_delivery`;
CREATE TABLE `yh_delivery`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '快递名称',
  `description` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '快递描述',
  `area_groupid` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '配送区域id',
  `firstprice` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '配送地址对应的首重价格',
  `secondprice` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '配送地区对应的续重价格',
  `type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '配送类型 0先付款后发货 1先发货后付款',
  `first_weight` int(11) UNSIGNED NOT NULL COMMENT '首重重量(克)',
  `second_weight` int(11) UNSIGNED NOT NULL COMMENT '续重重量(克)',
  `first_price` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '首重价格',
  `second_price` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '续重价格',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '开启状态 1启用 0关闭',
  `sort` smallint(5) NOT NULL DEFAULT 99 COMMENT '排序',
  `is_save_price` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否支持物流保价 1支持保价 0  不支持保价',
  `save_rate` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '保价费率',
  `low_price` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '最低保价',
  `price_type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '费用类型 0统一设置 1指定地区费用',
  `open_default` tinyint(1) NOT NULL DEFAULT 1 COMMENT '其他地区是否启用默认费用 1启用 0 不启用',
  `is_delete` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除 0:未删除 1:删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `status`(`status`) USING BTREE,
  INDEX `sort`(`sort`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '配送方式表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_delivery_doc
-- ----------------------------
DROP TABLE IF EXISTS `yh_delivery_doc`;
CREATE TABLE `yh_delivery_doc`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '发货单ID',
  `order_id` int(11) UNSIGNED NOT NULL COMMENT '订单ID',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `admin_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '管理员ID',
  `seller_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '商户ID',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '收货人',
  `postcode` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '邮编',
  `telphone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '联系电话',
  `country` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '国ID',
  `province` int(11) UNSIGNED NOT NULL COMMENT '省ID',
  `city` int(11) UNSIGNED NOT NULL COMMENT '市ID',
  `area` int(11) UNSIGNED NOT NULL COMMENT '区ID',
  `address` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '收货地址',
  `mobile` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '手机',
  `time` datetime(0) NOT NULL COMMENT '创建时间',
  `freight` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '运费',
  `delivery_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '物流单号',
  `delivery_type` int(11) NOT NULL COMMENT '物流方式',
  `note` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '管理员添加的备注信息',
  `if_del` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0:未删除 1:已删除',
  `freight_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '货运公司ID',
  `express_template` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '快递单模板',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `order_id`(`order_id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `delivery_code`(`delivery_code`) USING BTREE,
  INDEX `freight_id`(`freight_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 589 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '发货单' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_delivery_extend
-- ----------------------------
DROP TABLE IF EXISTS `yh_delivery_extend`;
CREATE TABLE `yh_delivery_extend`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `delivery_id` int(11) UNSIGNED NOT NULL COMMENT '配送方式关联ID',
  `area_groupid` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '单独配置地区id',
  `firstprice` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '单独配置地区对应的首重价格',
  `secondprice` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '单独配置地区对应的续重价格',
  `first_weight` int(11) UNSIGNED NOT NULL COMMENT '首重重量(克)',
  `second_weight` int(11) UNSIGNED NOT NULL COMMENT '续重重量(克)',
  `first_price` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '默认首重价格',
  `second_price` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '默认续重价格',
  `is_save_price` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否支持物流保价 1支持保价 0  不支持保价',
  `save_rate` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '保价费率',
  `low_price` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '最低保价',
  `price_type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '费用类型 0统一设置 1指定地区费用',
  `open_default` tinyint(1) NOT NULL DEFAULT 1 COMMENT '其他地区是否启用默认费用 1启用 0 不启用',
  `seller_id` int(11) UNSIGNED NOT NULL COMMENT '商家ID',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `delivery_id`(`delivery_id`) USING BTREE,
  INDEX `seller_id`(`seller_id`) USING BTREE,
  CONSTRAINT `yh_delivery_extend_ibfk_1` FOREIGN KEY (`delivery_id`) REFERENCES `yh_delivery` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商家配送方式扩展表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_discussion
-- ----------------------------
DROP TABLE IF EXISTS `yh_discussion`;
CREATE TABLE `yh_discussion`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) UNSIGNED NOT NULL COMMENT '商品ID',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `time` datetime(0) NOT NULL COMMENT '评论时间',
  `contents` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '评论内容',
  `is_check` tinyint(1) NOT NULL DEFAULT 0 COMMENT '审核状态,0未审核 1已审核',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `goods_id`(`goods_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品讨论表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_email_registry
-- ----------------------------
DROP TABLE IF EXISTS `yh_email_registry`;
CREATE TABLE `yh_email_registry`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Email',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `email`(`email`(15)) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = 'Email订阅表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_exchange_doc
-- ----------------------------
DROP TABLE IF EXISTS `yh_exchange_doc`;
CREATE TABLE `yh_exchange_doc`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_no` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '订单号',
  `order_id` int(11) UNSIGNED NOT NULL COMMENT '订单ID',
  `user_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '用户ID',
  `time` datetime(0) NULL DEFAULT NULL COMMENT '时间',
  `admin_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '管理员ID',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态,0:申请中 1:已拒绝 2:已完成 3:等待买家发货 4:等待商家确认',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '申请原因',
  `dispose_time` datetime(0) NULL DEFAULT NULL COMMENT '处理时间',
  `dispose_idea` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '处理意见',
  `if_del` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0:未删除 1:已删除',
  `order_goods_id` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '订单与商品关联ID集合',
  `seller_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商家ID',
  `img_list` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '图片',
  `user_freight_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '用户发货时货运公司ID',
  `user_delivery_code` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户发货时快递单号',
  `user_send_time` datetime(0) NULL DEFAULT NULL COMMENT '发货时间',
  `seller_freight_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '商家发货时货运公司ID',
  `seller_delivery_code` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '商家发货时快递单号',
  `seller_send_time` datetime(0) NULL DEFAULT NULL COMMENT '发货时间',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `order_id`(`order_id`) USING BTREE,
  INDEX `seller_id`(`seller_id`) USING BTREE,
  INDEX `if_del`(`if_del`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `status`(`status`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '售后换货单' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_expresswaybill
-- ----------------------------
DROP TABLE IF EXISTS `yh_expresswaybill`;
CREATE TABLE `yh_expresswaybill`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `freight_type` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '货运代号',
  `freight_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '货运公司名称',
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '网址',
  `config` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '快递单打印配置JSON',
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '描述',
  `is_open` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否开启:0关闭;1:开启;',
  `seller_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商家ID',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 19 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '快递单打印物流公司' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_favorite
-- ----------------------------
DROP TABLE IF EXISTS `yh_favorite`;
CREATE TABLE `yh_favorite`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `goods_id` int(11) UNSIGNED NOT NULL COMMENT '商品ID',
  `time` datetime(0) NOT NULL COMMENT '收藏时间',
  `summary` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注',
  `cat_id` int(11) UNSIGNED NOT NULL COMMENT '商品分类',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `cat_id`(`cat_id`) USING BTREE,
  INDEX `goods_id`(`goods_id`) USING BTREE,
  CONSTRAINT `yh_favorite_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `yh_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 62 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '收藏夹表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_find_password
-- ----------------------------
DROP TABLE IF EXISTS `yh_find_password`;
CREATE TABLE `yh_find_password`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `hash` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '随机值',
  `addtime` int(11) NOT NULL COMMENT '申请找回的时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `hash`(`hash`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  CONSTRAINT `yh_find_password_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `yh_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 3105 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '找回密码' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_fix_doc
-- ----------------------------
DROP TABLE IF EXISTS `yh_fix_doc`;
CREATE TABLE `yh_fix_doc`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_no` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '订单号',
  `order_id` int(11) UNSIGNED NOT NULL COMMENT '订单ID',
  `user_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '用户ID',
  `time` datetime(0) NULL DEFAULT NULL COMMENT '时间',
  `admin_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '管理员ID',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态,0:申请中 1:已拒绝 2:已完成 3:等待买家发货 4:等待商家确认',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '申请原因',
  `dispose_time` datetime(0) NULL DEFAULT NULL COMMENT '处理时间',
  `dispose_idea` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '处理意见',
  `if_del` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0:未删除 1:已删除',
  `order_goods_id` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '订单与商品关联ID集合',
  `seller_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商家ID',
  `img_list` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '图片',
  `user_freight_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '用户发货时货运公司ID',
  `user_delivery_code` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户发货时快递单号',
  `user_send_time` datetime(0) NULL DEFAULT NULL COMMENT '发货时间',
  `seller_freight_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '商家发货时货运公司ID',
  `seller_delivery_code` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '商家发货时快递单号',
  `seller_send_time` datetime(0) NULL DEFAULT NULL COMMENT '发货时间',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `order_id`(`order_id`) USING BTREE,
  INDEX `seller_id`(`seller_id`) USING BTREE,
  INDEX `if_del`(`if_del`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `status`(`status`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '售后维修单' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_free_balance_log
-- ----------------------------
DROP TABLE IF EXISTS `yh_free_balance_log`;
CREATE TABLE `yh_free_balance_log`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '管理员ID',
  `user_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '用户id',
  `type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0增加,1减少',
  `datetime` datetime(0) NOT NULL COMMENT '发生时间',
  `value` decimal(15, 2) NOT NULL COMMENT '金额',
  `value_log` decimal(15, 2) NOT NULL COMMENT '每次增减后面的记录',
  `note` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '备注',
  `from_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'withdraw对应的id',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `admin_id`(`admin_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 79 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '免费额度日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_freight_company
-- ----------------------------
DROP TABLE IF EXISTS `yh_freight_company`;
CREATE TABLE `yh_freight_company`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `freight_type` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '货运代号',
  `freight_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '货运公司名称',
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '网址',
  `sort` smallint(5) NOT NULL DEFAULT 99 COMMENT '排序',
  `is_del` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0:未删除 1:删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `sort`(`sort`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '货运公司' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_goods
-- ----------------------------
DROP TABLE IF EXISTS `yh_goods`;
CREATE TABLE `yh_goods`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '商品ID',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '商品名称',
  `goods_no` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '商品的货号',
  `model_id` int(11) UNSIGNED NOT NULL COMMENT '模型ID',
  `sell_price` decimal(15, 2) NOT NULL COMMENT '销售价格',
  `market_price` decimal(15, 2) NULL DEFAULT NULL COMMENT '市场价格',
  `cost_price` decimal(15, 2) NULL DEFAULT NULL COMMENT '成本价格',
  `up_time` datetime(0) NULL DEFAULT NULL COMMENT '上架时间',
  `down_time` datetime(0) NULL DEFAULT NULL COMMENT '下架时间',
  `create_time` datetime(0) NOT NULL COMMENT '创建时间',
  `store_nums` int(11) NOT NULL DEFAULT 0 COMMENT '库存',
  `img` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '原图',
  `ad_img` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '宣传图',
  `is_del` tinyint(1) NOT NULL DEFAULT 0 COMMENT '商品状态 0正常 1已删除 2下架 3申请上架',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '商品描述',
  `keywords` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'SEO关键词',
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'SEO描述',
  `search_words` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '产品搜索词库,逗号分隔',
  `weight` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '重量',
  `point` int(11) NOT NULL DEFAULT 0 COMMENT '积分',
  `unit` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '计件单位。如:件,箱,个',
  `brand_id` int(11) NOT NULL DEFAULT 0 COMMENT '品牌ID',
  `visit` int(11) NOT NULL DEFAULT 0 COMMENT '浏览次数',
  `favorite` int(11) NOT NULL DEFAULT 0 COMMENT '收藏次数',
  `sort` smallint(5) NOT NULL DEFAULT 99 COMMENT '排序',
  `spec_array` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '商品信息json数据',
  `exp` int(11) NOT NULL DEFAULT 0 COMMENT '经验值',
  `comments` int(11) NOT NULL DEFAULT 0 COMMENT '评论次数',
  `sale` int(11) NOT NULL DEFAULT 0 COMMENT '销量',
  `grade` int(11) NOT NULL DEFAULT 0 COMMENT '评分总数',
  `seller_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '卖家ID',
  `is_share` tinyint(1) NOT NULL DEFAULT 0 COMMENT '共享商品 0不共享 1共享',
  `is_delivery_fee` tinyint(1) NOT NULL DEFAULT 0 COMMENT '免运费 0收运费 1免运费',
  `promo` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '默认:普通,groupon:团购,time:限时抢购,costpoint:积分兑换',
  `active_id` int(11) NOT NULL DEFAULT 0 COMMENT '活动ID主键',
  `type` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'default' COMMENT 'default:实体,code:到店服务,download:知识付费下载',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `is_del`(`is_del`) USING BTREE,
  INDEX `sort`(`sort`) USING BTREE,
  INDEX `sale`(`sale`) USING BTREE,
  INDEX `grade`(`grade`) USING BTREE,
  INDEX `sell_price`(`sell_price`) USING BTREE,
  INDEX `name`(`name`) USING BTREE,
  INDEX `goods_no`(`goods_no`) USING BTREE,
  INDEX `is_share`(`is_share`) USING BTREE,
  INDEX `brand_id`(`brand_id`, `is_del`) USING BTREE,
  INDEX `brand_id_2`(`brand_id`, `sell_price`) USING BTREE,
  INDEX `brand_id_3`(`brand_id`, `grade`) USING BTREE,
  INDEX `brand_id_4`(`brand_id`, `sale`) USING BTREE,
  INDEX `store_nums`(`store_nums`, `is_del`) USING BTREE,
  INDEX `seller_id`(`seller_id`, `is_del`) USING BTREE,
  INDEX `seller_id_2`(`seller_id`, `sell_price`) USING BTREE,
  INDEX `seller_id_3`(`seller_id`, `grade`) USING BTREE,
  INDEX `seller_id_4`(`seller_id`, `sale`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品信息表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_goods_attribute
-- ----------------------------
DROP TABLE IF EXISTS `yh_goods_attribute`;
CREATE TABLE `yh_goods_attribute`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) UNSIGNED NOT NULL COMMENT '商品ID',
  `attribute_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '属性ID',
  `attribute_value` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '属性值',
  `model_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '模型ID',
  `order` smallint(5) NOT NULL DEFAULT 99 COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `goods_id`(`goods_id`) USING BTREE,
  INDEX `attribute_id`(`attribute_id`, `attribute_value`) USING BTREE,
  INDEX `order`(`order`) USING BTREE,
  CONSTRAINT `yh_goods_attribute_ibfk_1` FOREIGN KEY (`attribute_id`) REFERENCES `yh_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '属性值表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_goods_car
-- ----------------------------
DROP TABLE IF EXISTS `yh_goods_car`;
CREATE TABLE `yh_goods_car`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '购物内容',
  `create_time` datetime(0) NOT NULL COMMENT '创建时间',
  `unselected` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '未选择结算的商品信息',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  CONSTRAINT `yh_goods_car_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `yh_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 629 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '购物车' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_goods_extend_download
-- ----------------------------
DROP TABLE IF EXISTS `yh_goods_extend_download`;
CREATE TABLE `yh_goods_extend_download`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL COMMENT '商品ID',
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '下载地址',
  `seller_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '商家ID',
  `end_time` date NULL DEFAULT NULL COMMENT '截至时间',
  `limit_num` smallint(6) NULL DEFAULT 0 COMMENT '限制下载次数',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `goods_id`(`goods_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品下载资源地址' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_goods_photo
-- ----------------------------
DROP TABLE IF EXISTS `yh_goods_photo`;
CREATE TABLE `yh_goods_photo`  (
  `id` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '图片的md5值',
  `img` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '原始图片路径',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '图片表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_goods_photo_relation
-- ----------------------------
DROP TABLE IF EXISTS `yh_goods_photo_relation`;
CREATE TABLE `yh_goods_photo_relation`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) UNSIGNED NOT NULL COMMENT '商品ID',
  `photo_id` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '图片ID,图片的md5值',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `goods_id`(`goods_id`) USING BTREE,
  INDEX `photo_id`(`photo_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '相册商品关系表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_goods_rate
-- ----------------------------
DROP TABLE IF EXISTS `yh_goods_rate`;
CREATE TABLE `yh_goods_rate`  (
  `goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品ID',
  `goods_rate` decimal(15, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '单品手续费',
  PRIMARY KEY (`goods_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '单个商品手续费设置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_group_price
-- ----------------------------
DROP TABLE IF EXISTS `yh_group_price`;
CREATE TABLE `yh_group_price`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) UNSIGNED NOT NULL COMMENT '产品ID',
  `product_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '货品ID',
  `group_id` int(11) UNSIGNED NOT NULL COMMENT '用户组ID',
  `price` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '价格',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `goods_id`(`goods_id`) USING BTREE,
  INDEX `group_id`(`group_id`) USING BTREE,
  INDEX `product_id`(`product_id`) USING BTREE,
  CONSTRAINT `yh_group_price_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `yh_user_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '记录某件商品对于某组会员的价格关系表，优先权大于组设定的折扣率' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_guide
-- ----------------------------
DROP TABLE IF EXISTS `yh_guide`;
CREATE TABLE `yh_guide`  (
  `order` smallint(5) UNSIGNED NOT NULL COMMENT '排序',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '导航名字',
  `link` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '链接地址',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`order`, `_hash`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '首页导航栏' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_help
-- ----------------------------
DROP TABLE IF EXISTS `yh_help`;
CREATE TABLE `yh_help`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cat_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '帮助分类，如果为0则代表着是下面的帮助单页',
  `sort` smallint(5) NOT NULL DEFAULT 99 COMMENT '顺序',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '内容',
  `dateline` int(11) NOT NULL COMMENT '发布时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `cat_id`(`cat_id`) USING BTREE,
  INDEX `sort`(`sort`) USING BTREE,
  CONSTRAINT `yh_help_ibfk_1` FOREIGN KEY (`cat_id`) REFERENCES `yh_help_category` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE = InnoDB AUTO_INCREMENT = 54 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '帮助内容' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_help_category
-- ----------------------------
DROP TABLE IF EXISTS `yh_help_category`;
CREATE TABLE `yh_help_category`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题',
  `sort` smallint(5) NOT NULL COMMENT '顺序',
  `position_left` tinyint(1) NOT NULL COMMENT '是否在帮助内容、列表页面的左侧显示',
  `position_foot` tinyint(1) NOT NULL COMMENT '是否在整站页面下方显示',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `sort`(`sort`) USING BTREE,
  INDEX `position_left`(`position_left`) USING BTREE,
  INDEX `position_foot`(`position_foot`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '帮助分类' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_invoice
-- ----------------------------
DROP TABLE IF EXISTS `yh_invoice`;
CREATE TABLE `yh_invoice`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '用户id',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '发票类型,1:普通发票,2:增值税专用发票',
  `company_name` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '单位名称',
  `taxcode` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '纳税人识别码',
  `address` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '注册地址',
  `telphone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '注册电话',
  `bankname` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '开户银行',
  `bankno` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '银行账户',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  CONSTRAINT `yh_invoice_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `yh_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '发票表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_keyword
-- ----------------------------
DROP TABLE IF EXISTS `yh_keyword`;
CREATE TABLE `yh_keyword`  (
  `word` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '关键词',
  `goods_nums` int(11) NOT NULL DEFAULT 1 COMMENT '产品数量',
  `hot` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否为热门',
  `order` smallint(5) NOT NULL DEFAULT 99 COMMENT '关键词排序',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`word`, `_hash`) USING BTREE,
  INDEX `hot`(`hot`) USING BTREE,
  INDEX `order`(`order`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '关键词' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_level_upgrade_log
-- ----------------------------
DROP TABLE IF EXISTS `yh_level_upgrade_log`;
CREATE TABLE `yh_level_upgrade_log`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '管理员ID',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `level` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户等级',
  `level_log` tinyint(2) NOT NULL COMMENT '变更前的等级',
  `datetime` datetime(0) NOT NULL COMMENT '发生时间',
  `note` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '备注',
  `if_del` tinyint(1) NULL DEFAULT 0 COMMENT '是否删除1为删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `admin_id`(`admin_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 526 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户level变动日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_log_error
-- ----------------------------
DROP TABLE IF EXISTS `yh_log_error`;
CREATE TABLE `yh_log_error`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `file` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '文件',
  `line` smallint(5) UNSIGNED NOT NULL COMMENT '出错文件行数',
  `content` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '内容',
  `datetime` datetime(0) NOT NULL COMMENT '时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '错误日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_log_operation
-- ----------------------------
DROP TABLE IF EXISTS `yh_log_operation`;
CREATE TABLE `yh_log_operation`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `author` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '操作人员',
  `action` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '动作',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '内容',
  `datetime` datetime(0) NOT NULL COMMENT '时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8185 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '日志操作记录' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_log_sql
-- ----------------------------
DROP TABLE IF EXISTS `yh_log_sql`;
CREATE TABLE `yh_log_sql`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `content` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '执行的SQL语句',
  `runtime` decimal(15, 2) UNSIGNED NOT NULL COMMENT '语句执行时间(秒)',
  `datetime` datetime(0) NOT NULL COMMENT '发生的时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = 'SQL日志记录' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_marketing_sms
-- ----------------------------
DROP TABLE IF EXISTS `yh_marketing_sms`;
CREATE TABLE `yh_marketing_sms`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `content` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '短信内容',
  `send_nums` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '发送成功数',
  `time` datetime(0) NOT NULL COMMENT '发送时间',
  `rev_info` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '收件人信息',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '营销短信' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_member
-- ----------------------------
DROP TABLE IF EXISTS `yh_member`;
CREATE TABLE `yh_member`  (
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `true_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '真实姓名',
  `telephone` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '联系电话',
  `mobile` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '手机',
  `area` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '地区',
  `contact_addr` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '联系地址',
  `qq` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'QQ',
  `sex` tinyint(1) NOT NULL DEFAULT 1 COMMENT '性别1男2女',
  `birthday` date NULL DEFAULT NULL COMMENT '生日',
  `group_id` int(11) NULL DEFAULT NULL COMMENT '分组',
  `exp` int(11) NOT NULL DEFAULT 0 COMMENT '经验值',
  `point` int(11) NOT NULL DEFAULT 0 COMMENT '积分',
  `message_ids` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '消息ID',
  `time` datetime(0) NULL DEFAULT NULL COMMENT '注册日期时间',
  `zip` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '邮政编码',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '用户状态 1正常状态 2 删除至回收站 3锁定',
  `prop` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '用户拥有的工具',
  `balance` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '用户余额',
  `last_login` datetime(0) NULL DEFAULT NULL COMMENT '最后一次登录时间',
  `custom` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户习惯方式,配送和支付方式等信息',
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Email',
  `free_balance` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '免手续费额度',
  PRIMARY KEY (`user_id`) USING BTREE,
  INDEX `group_id`(`group_id`) USING BTREE,
  INDEX `mobile`(`mobile`) USING BTREE,
  INDEX `email`(`email`) USING BTREE,
  INDEX `status`(`status`, `true_name`) USING BTREE,
  CONSTRAINT `yh_member_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `yh_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户信息表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_merch_ship_info
-- ----------------------------
DROP TABLE IF EXISTS `yh_merch_ship_info`;
CREATE TABLE `yh_merch_ship_info`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ship_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '发货点名称',
  `ship_user_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '发货人姓名',
  `sex` tinyint(1) NOT NULL DEFAULT 0 COMMENT '性别 0:女 1:男',
  `country` int(11) NULL DEFAULT NULL COMMENT '国id',
  `province` int(11) NOT NULL COMMENT '省id',
  `city` int(11) NOT NULL COMMENT '市id',
  `area` int(11) NOT NULL COMMENT '地区id',
  `postcode` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '邮编',
  `address` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '具体地址',
  `mobile` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '手机',
  `telphone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '电话',
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1为默认地址，0则不是',
  `note` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '备注',
  `addtime` datetime(0) NOT NULL COMMENT '保存时间',
  `is_del` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1为删除，0为未删除',
  `seller_id` int(11) UNSIGNED NOT NULL COMMENT '商家ID',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商家发货点信息' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_message
-- ----------------------------
DROP TABLE IF EXISTS `yh_message`;
CREATE TABLE `yh_message`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '内容',
  `time` datetime(0) NOT NULL COMMENT '发送时间',
  `rev_info` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '收件人信息',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '会员消息' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_model
-- ----------------------------
DROP TABLE IF EXISTS `yh_model`;
CREATE TABLE `yh_model`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '模型ID',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '模型名称',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '模型表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_notify_registry
-- ----------------------------
DROP TABLE IF EXISTS `yh_notify_registry`;
CREATE TABLE `yh_notify_registry`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) UNSIGNED NOT NULL COMMENT '商品ID',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'emaill',
  `mobile` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '手机',
  `register_time` datetime(0) NOT NULL COMMENT '登记时间',
  `notify_time` datetime(0) NULL DEFAULT NULL COMMENT '通知时间',
  `notify_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0未通知1仅邮件通知2仅短信通知3已邮件、短信通知',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `goods_id`(`goods_id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  CONSTRAINT `yh_notify_registry_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `yh_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '到货通知表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_oauth
-- ----------------------------
DROP TABLE IF EXISTS `yh_oauth`;
CREATE TABLE `yh_oauth`  (
  `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '名称',
  `config` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '配置信息',
  `file` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '接口文件名称',
  `description` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '描述',
  `is_close` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否关闭;0开启,1关闭',
  `logo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'logo',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '认证方案oauth2.0' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_oauth_user
-- ----------------------------
DROP TABLE IF EXISTS `yh_oauth_user`;
CREATE TABLE `yh_oauth_user`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `oauth_user_id` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '第三方平台的用户唯一标识',
  `oauth_id` smallint(5) UNSIGNED NOT NULL COMMENT 'oauth表关联平台id',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '系统内部的用户id',
  `datetime` datetime(0) NOT NULL COMMENT '绑定时间',
  `openid` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'openid参数只针对微信',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `oauth_user_id`(`oauth_user_id`, `oauth_id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  CONSTRAINT `yh_oauth_user_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `yh_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = 'oauth开发平台绑定用户表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_online_recharge
-- ----------------------------
DROP TABLE IF EXISTS `yh_online_recharge`;
CREATE TABLE `yh_online_recharge`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户id',
  `recharge_no` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '充值单号',
  `account` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '充值金额',
  `time` datetime(0) NOT NULL COMMENT '时间',
  `payment_name` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '充值方式名称',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '充值状态 0:未成功 1:充值成功',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  CONSTRAINT `yh_online_recharge_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `yh_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 3035 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '在线充值表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_order
-- ----------------------------
DROP TABLE IF EXISTS `yh_order`;
CREATE TABLE `yh_order`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_no` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '订单号',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `pay_type` int(11) NOT NULL COMMENT '用户支付方式ID,当为0时表示货到付款',
  `distribution` int(11) NULL DEFAULT NULL COMMENT '用户选择的配送ID',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '订单状态 1生成订单,2支付订单,3取消订单(客户触发),4作废订单(管理员触发),5完成订单,6退款(订单完成后),7部分退款(订单完成后)',
  `pay_status` tinyint(1) NULL DEFAULT 0 COMMENT '支付状态 0：未支付; 1：已支付;',
  `distribution_status` tinyint(1) NULL DEFAULT 0 COMMENT '配送状态 0：未发送,1：已发送,2：部分发送',
  `accept_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '收货人姓名',
  `postcode` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '邮编',
  `telphone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '联系电话',
  `country` int(11) NULL DEFAULT NULL COMMENT '国ID',
  `province` int(11) NULL DEFAULT NULL COMMENT '省ID',
  `city` int(11) NULL DEFAULT NULL COMMENT '市ID',
  `area` int(11) NULL DEFAULT NULL COMMENT '区ID',
  `address` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '收货地址',
  `mobile` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '手机',
  `payable_amount` decimal(15, 2) NULL DEFAULT 0.00 COMMENT '应付商品总金额',
  `real_amount` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '实付商品总金额(会员折扣,促销规则折扣)',
  `payable_freight` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '总运费金额',
  `real_freight` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '实付运费',
  `pay_time` datetime(0) NULL DEFAULT NULL COMMENT '付款时间',
  `send_time` datetime(0) NULL DEFAULT NULL COMMENT '发货时间',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '下单时间',
  `completion_time` datetime(0) NULL DEFAULT NULL COMMENT '订单完成时间',
  `invoice` tinyint(1) NOT NULL DEFAULT 0 COMMENT '发票：0不索要1索要',
  `postscript` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户附言',
  `note` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '管理员备注和促销规则描述',
  `if_del` tinyint(1) NULL DEFAULT 0 COMMENT '是否删除1为删除',
  `insured` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '保价',
  `invoice_info` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '发票信息JSON数据',
  `taxes` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '税金',
  `promotions` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '促销优惠金额和会员折扣',
  `discount` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '订单折扣或涨价',
  `order_amount` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '订单总金额',
  `prop` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '使用的道具id',
  `accept_time` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户收货时间',
  `exp` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '增加的经验',
  `point` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '增加的积分',
  `type` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '默认:普通,groupon:团购,time:限时抢购,costpoint:积分兑换',
  `trade_no` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '支付平台交易号',
  `takeself` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '自提点ID',
  `checkcode` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '自提方式的验证码',
  `active_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '促销活动ID',
  `seller_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商家ID',
  `is_checkout` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否给商家结算货款 0:未结算 1:已结算',
  `prorule_ids` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '促销活动规格ID串，逗号分隔',
  `spend_point` int(11) NOT NULL DEFAULT 0 COMMENT '花费的积分数',
  `servicefee_amount` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '订单手续费总金额',
  `goods_type` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'default' COMMENT 'default:实体,code:到店服务,download:知识付费下载',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  `vip_order_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT 'vip订单id',
  `active_uid` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '要激活会员的uid',
  `gift` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '赠品id',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `if_del`(`if_del`) USING BTREE,
  INDEX `order_no`(`order_no`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `seller_id`(`seller_id`) USING BTREE,
  INDEX `status`(`status`) USING BTREE,
  INDEX `order_amount`(`order_amount`) USING BTREE,
  INDEX `completion_time`(`completion_time`) USING BTREE,
  INDEX `send_time`(`send_time`) USING BTREE,
  INDEX `create_time`(`create_time`) USING BTREE,
  INDEX `distribution_status`(`distribution_status`) USING BTREE,
  INDEX `pay_status`(`pay_status`) USING BTREE,
  INDEX `accept_name`(`accept_name`) USING BTREE,
  INDEX `is_checkout`(`is_checkout`) USING BTREE,
  INDEX `checkcode`(`checkcode`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6065 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '订单表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_order_code_relation
-- ----------------------------
DROP TABLE IF EXISTS `yh_order_code_relation`;
CREATE TABLE `yh_order_code_relation`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL COMMENT '订单ID',
  `goods_id` int(11) NOT NULL COMMENT '商品ID',
  `code` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '验证码字符串',
  `seller_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '商家ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '生成时间',
  `use_time` datetime(0) NULL DEFAULT NULL COMMENT '使用时间',
  `is_used` tinyint(1) NOT NULL DEFAULT 0 COMMENT '使用状态 0未用 1已用',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `order_id`(`order_id`) USING BTREE,
  INDEX `code`(`code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '虚拟商品订单服务验证码关系' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_order_delivery_trace
-- ----------------------------
DROP TABLE IF EXISTS `yh_order_delivery_trace`;
CREATE TABLE `yh_order_delivery_trace`  (
  `delivery_code` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '快递单号',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '物流跟踪信息',
  `update_time` datetime(0) NULL DEFAULT NULL COMMENT '时间',
  PRIMARY KEY (`delivery_code`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '订单物流跟踪表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_order_download_relation
-- ----------------------------
DROP TABLE IF EXISTS `yh_order_download_relation`;
CREATE TABLE `yh_order_download_relation`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL COMMENT '订单ID',
  `goods_id` int(11) NOT NULL COMMENT '商品ID',
  `seller_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '商家ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '生成时间',
  `num` smallint(6) NOT NULL DEFAULT 0 COMMENT '下载次数',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `order_id`(`order_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '虚拟商品订单下载关系' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_order_goods
-- ----------------------------
DROP TABLE IF EXISTS `yh_order_goods`;
CREATE TABLE `yh_order_goods`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(11) UNSIGNED NOT NULL COMMENT '订单ID',
  `goods_id` int(11) UNSIGNED NOT NULL COMMENT '商品ID',
  `img` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '商品图片',
  `product_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '货品ID',
  `goods_price` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '商品原价',
  `real_price` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '实付金额',
  `goods_nums` int(11) NOT NULL DEFAULT 1 COMMENT '商品数量',
  `goods_weight` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '重量',
  `goods_array` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '商品和货品名称name和规格value串json数据格式',
  `is_send` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否已发货 0:未发货;1:已发货;2:已经退款',
  `delivery_id` int(11) NOT NULL DEFAULT 0 COMMENT '配送单ID',
  `seller_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商家ID',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `goods_id`(`goods_id`) USING BTREE,
  INDEX `order_id`(`order_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9617 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '订单商品表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_order_goods_servicefee
-- ----------------------------
DROP TABLE IF EXISTS `yh_order_goods_servicefee`;
CREATE TABLE `yh_order_goods_servicefee`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(11) UNSIGNED NOT NULL COMMENT '订单ID',
  `order_goods_id` int(11) UNSIGNED NOT NULL COMMENT '订单商品ID',
  `type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '手续费类型 0:默认;1:单品;2:分类',
  `rate` decimal(15, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '手续费率',
  `discount` decimal(15, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商户结算折扣率',
  `amount` decimal(15, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '手续费总额',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `order_id`(`order_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '订单商品手续费表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_order_log
-- ----------------------------
DROP TABLE IF EXISTS `yh_order_log`;
CREATE TABLE `yh_order_log`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '订单id',
  `user` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '操作人：顾客或admin或seller',
  `action` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '动作',
  `addtime` datetime(0) NULL DEFAULT NULL COMMENT '添加时间',
  `result` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '操作的结果',
  `note` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `order_id`(`order_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6246 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '订单日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_payment
-- ----------------------------
DROP TABLE IF EXISTS `yh_payment`;
CREATE TABLE `yh_payment`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '支付名称',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1:线上、2:线下',
  `class_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '支付类名称',
  `description` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '描述',
  `logo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '支付方式logo图片路径',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '安装状态 0启用 1禁用',
  `order` smallint(5) NOT NULL DEFAULT 99 COMMENT '排序',
  `note` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '支付说明',
  `config_param` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '配置参数,json数据对象',
  `client_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1:PC端 2:移动端 3:通用',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 54 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '支付方式表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_plugin
-- ----------------------------
DROP TABLE IF EXISTS `yh_plugin`;
CREATE TABLE `yh_plugin`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '插件ID',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '插件名称',
  `class_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '插件类库名称',
  `config_param` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '配置参数',
  `description` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '描述说明',
  `is_open` tinyint(1) NOT NULL DEFAULT 1 COMMENT '安装状态 0禁用 1启用',
  `sort` smallint(5) NOT NULL DEFAULT 99 COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `class_name`(`class_name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '插件表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_point_log
-- ----------------------------
DROP TABLE IF EXISTS `yh_point_log`;
CREATE TABLE `yh_point_log`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户id',
  `datetime` datetime(0) NOT NULL COMMENT '发生时间',
  `value` int(11) NOT NULL COMMENT '积分增减 增加正数 减少负数',
  `intro` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '积分改动说明',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  CONSTRAINT `yh_point_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `yh_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '积分增减记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_products
-- ----------------------------
DROP TABLE IF EXISTS `yh_products`;
CREATE TABLE `yh_products`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) UNSIGNED NOT NULL COMMENT '商品ID',
  `products_no` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '货品的货号(以商品的货号加横线加数字组成)',
  `spec_array` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'json规格数据',
  `store_nums` int(11) NOT NULL DEFAULT 0 COMMENT '库存',
  `market_price` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '市场价格',
  `sell_price` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '销售价格',
  `cost_price` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '成本价格',
  `weight` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '重量',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `goods_id`(`goods_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '货品表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_promotion
-- ----------------------------
DROP TABLE IF EXISTS `yh_promotion`;
CREATE TABLE `yh_promotion`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `start_time` datetime(0) NOT NULL COMMENT '开始时间',
  `end_time` datetime(0) NOT NULL COMMENT '结束时间',
  `sort` smallint(5) NOT NULL COMMENT '顺序',
  `condition` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '活动生效条件 当type=0<促销规则消费额度>,当type=1<限时抢购商品ID>,type=2<特价商品分类ID>,type=3<特价商品ID>,type=4<特价商品品牌ID>,type=5<无意义>',
  `type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '活动类型 0:购物车促销规则 1:商品限时抢购 2:商品分类特价 3:商品单品特价 4:商品品牌特价 5:新用户注册促销规则',
  `award_value` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '奖励值 type=0,5<奖励值>,type=1<抢购价格>,type=2,3,4<特价折扣>',
  `name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '活动名称',
  `intro` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '活动介绍',
  `award_type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '奖励方式:0商品限时抢购 1减金额 2奖励折扣 3赠送积分 4赠送优惠券 5赠送赠品 6免运费 7商品特价 8赠送经验',
  `is_close` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否关闭 0:否 1:是',
  `user_group` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '允许参与活动的用户组,all表示所有用户组',
  `seller_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商家ID',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `type`(`type`, `seller_id`) USING BTREE,
  INDEX `start_time`(`start_time`, `end_time`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '记录促销活动的表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_prop
-- ----------------------------
DROP TABLE IF EXISTS `yh_prop`;
CREATE TABLE `yh_prop`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '道具名称',
  `card_name` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '道具的卡号',
  `card_pwd` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '道具的密码',
  `start_time` datetime(0) NOT NULL COMMENT '开始时间',
  `end_time` datetime(0) NOT NULL COMMENT '结束时间',
  `value` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '面值',
  `type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '道具类型 0:优惠券',
  `condition` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '条件数据 type=0时,表示ticket的表id,模型id',
  `is_close` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否关闭 0:正常,1:关闭,2:下订单未支付时临时锁定',
  `img` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '道具图片',
  `is_userd` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否被使用过 0:未使用,1:已使用',
  `is_send` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否被发送过 0:否 1:是',
  `seller_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '所属商户ID',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '道具表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_quick_naviga
-- ----------------------------
DROP TABLE IF EXISTS `yh_quick_naviga`;
CREATE TABLE `yh_quick_naviga`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) UNSIGNED NOT NULL COMMENT '管理员id',
  `naviga_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '导航名称',
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '导航链接',
  `is_del` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除1为删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `admin_id`(`admin_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '管理员快速导航' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_real_name
-- ----------------------------
DROP TABLE IF EXISTS `yh_real_name`;
CREATE TABLE `yh_real_name`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `time` datetime(0) NOT NULL COMMENT '时间',
  `name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '真名',
  `id_num` char(18) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '身份证号码',
  `front_img` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '正面身份证',
  `back_img` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '反面身份证',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '-1失败,0未处理,1处理中,2成功',
  `is_del` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0未删除,1已删除',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3069 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户真人验证' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_recharge_mark
-- ----------------------------
DROP TABLE IF EXISTS `yh_recharge_mark`;
CREATE TABLE `yh_recharge_mark`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `time` datetime(0) NOT NULL COMMENT '时间',
  `note` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注',
  `account_id` int(11) UNSIGNED NOT NULL COMMENT '余额日志表id',
  `alipay` decimal(15, 2) NOT NULL COMMENT '备注支付宝金额',
  `wxpay` decimal(15, 2) NOT NULL COMMENT '备注微信金额',
  `bankpay` decimal(15, 2) NOT NULL COMMENT '备注刷卡金额',
  `moneypay` decimal(15, 2) NOT NULL COMMENT '备注现金金额',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 89 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '充值记录备注表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_refer
-- ----------------------------
DROP TABLE IF EXISTS `yh_refer`;
CREATE TABLE `yh_refer`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `question` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '咨询内容',
  `user_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '咨询人会员ID，非会员为空',
  `goods_id` int(11) UNSIGNED NOT NULL COMMENT '产品ID',
  `answer` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '回复内容',
  `admin_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '回复的管理员ID',
  `seller_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '回复的商户ID',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '0：待回复 1已回复',
  `time` datetime(0) NULL DEFAULT NULL COMMENT '咨询时间',
  `reply_time` datetime(0) NULL DEFAULT NULL COMMENT '回复时间',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `goods_id`(`goods_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '咨询表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_refundment_doc
-- ----------------------------
DROP TABLE IF EXISTS `yh_refundment_doc`;
CREATE TABLE `yh_refundment_doc`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_no` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '订单号',
  `order_id` int(11) UNSIGNED NOT NULL COMMENT '订单ID',
  `user_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '用户ID',
  `amount` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '退款金额',
  `time` datetime(0) NULL DEFAULT NULL COMMENT '时间',
  `admin_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '管理员ID',
  `pay_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态,0:申请中 1:已拒绝 2:已完成 3:等待买家发货 4:等待商家确认',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '申请原因',
  `dispose_time` datetime(0) NULL DEFAULT NULL COMMENT '处理时间',
  `dispose_idea` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '处理意见',
  `if_del` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0:未删除 1:已删除',
  `order_goods_id` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '订单与商品关联ID集合',
  `seller_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商家ID',
  `way` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '退款方式,balance:用户余额 other:其他方式 origin:原路退回',
  `trade_no` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '支付平台退款流水号',
  `img_list` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '图片',
  `user_freight_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '用户发货时货运公司ID',
  `user_delivery_code` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户发货时快递单号',
  `user_send_time` datetime(0) NULL DEFAULT NULL COMMENT '发货时间',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `order_id`(`order_id`) USING BTREE,
  INDEX `seller_id`(`seller_id`) USING BTREE,
  INDEX `if_del`(`if_del`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `pay_status`(`pay_status`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 401 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '售后退款单' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_regiment
-- ----------------------------
DROP TABLE IF EXISTS `yh_regiment`;
CREATE TABLE `yh_regiment`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '团购标题',
  `start_time` datetime(0) NOT NULL COMMENT '开始时间',
  `end_time` datetime(0) NOT NULL COMMENT '结束时间',
  `store_nums` int(11) NOT NULL DEFAULT 0 COMMENT '库存量',
  `sum_count` int(11) NOT NULL DEFAULT 0 COMMENT '已销售量',
  `limit_min_count` int(11) NOT NULL DEFAULT 0 COMMENT '每人限制最少购买数量',
  `limit_max_count` int(11) NOT NULL DEFAULT 0 COMMENT '每人限制最多购买数量',
  `intro` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '介绍',
  `is_close` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0开启,1关闭,2待审核',
  `regiment_price` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '团购价格',
  `sell_price` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '原来价格',
  `goods_id` int(11) UNSIGNED NOT NULL COMMENT '关联商品id',
  `img` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '商品图片',
  `sort` smallint(5) NOT NULL DEFAULT 99 COMMENT '排序',
  `seller_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商家ID',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `is_close`(`is_close`, `start_time`, `end_time`) USING BTREE,
  INDEX `goods_id`(`goods_id`) USING BTREE,
  INDEX `sort`(`sort`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '团购' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_relation
-- ----------------------------
DROP TABLE IF EXISTS `yh_relation`;
CREATE TABLE `yh_relation`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) UNSIGNED NOT NULL COMMENT '商品ID',
  `article_id` int(11) UNSIGNED NOT NULL COMMENT '文章ID',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `goods_id`(`goods_id`) USING BTREE,
  INDEX `article_id`(`article_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文章商品关系表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_revisit_log
-- ----------------------------
DROP TABLE IF EXISTS `yh_revisit_log`;
CREATE TABLE `yh_revisit_log`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '管理员ID',
  `user_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '用户id',
  `type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0增加,1减少',
  `time` datetime(0) NOT NULL COMMENT '发生时间',
  `value` decimal(15, 2) NOT NULL COMMENT '金额',
  `value_log` decimal(15, 2) NOT NULL COMMENT '每次增减后面的记录',
  `note` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '备注',
  `from_uid` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '来源uid',
  `if_del` tinyint(1) NULL DEFAULT 0 COMMENT '是否删除1为删除',
  `event` int(1) UNSIGNED NULL DEFAULT NULL COMMENT '类型 1,转出 2,接收转账',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `admin_id`(`admin_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 825313 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '重复消费日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_right
-- ----------------------------
DROP TABLE IF EXISTS `yh_right`;
CREATE TABLE `yh_right`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '权限名字',
  `right` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '权限码(控制器+动作)',
  `is_del` tinyint(1) NOT NULL DEFAULT 0 COMMENT '删除状态 1删除,0正常',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 174 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '权限资源码' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_search
-- ----------------------------
DROP TABLE IF EXISTS `yh_search`;
CREATE TABLE `yh_search`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `keyword` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '搜索关键字',
  `num` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '搜索次数',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `keyword`(`keyword`(12)) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '搜索关键字' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_sec_scocks_log
-- ----------------------------
DROP TABLE IF EXISTS `yh_sec_scocks_log`;
CREATE TABLE `yh_sec_scocks_log`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '管理员ID',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0增加,1减少',
  `event` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0升级获得,1邀请获得,2后台获得',
  `datetime` datetime(0) NOT NULL COMMENT '发生时间',
  `value` int(11) NOT NULL COMMENT '金额',
  `value_log` int(11) NOT NULL COMMENT '每次增减后面的金额记录',
  `note` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '备注',
  `if_del` tinyint(1) NULL DEFAULT 0 COMMENT '是否删除1为删除',
  `from_uid` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '奖励来源uid',
  `log_type` int(1) UNSIGNED NOT NULL DEFAULT 2 COMMENT '默认2新股2，1表示老股1',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `admin_id`(`admin_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 18956 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户干股2日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_seller
-- ----------------------------
DROP TABLE IF EXISTS `yh_seller`;
CREATE TABLE `yh_seller`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `seller_name` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '商家登录用户名',
  `password` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '商家密码',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '加入时间',
  `login_time` datetime(0) NULL DEFAULT NULL COMMENT '最后登录时间',
  `is_vip` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否是特级商家',
  `is_del` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0:未删除,1:已删除',
  `is_lock` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0:未锁定,1:已锁定',
  `true_name` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '商家真实名称',
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '电子邮箱',
  `mobile` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '手机号码',
  `phone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '座机号码',
  `paper_img` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '执照证件照片',
  `cash` decimal(15, 2) NULL DEFAULT NULL COMMENT '保证金',
  `country` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '国ID',
  `province` int(11) UNSIGNED NOT NULL COMMENT '省ID',
  `city` int(11) UNSIGNED NOT NULL COMMENT '市ID',
  `area` int(11) UNSIGNED NOT NULL COMMENT '区ID',
  `address` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '地址',
  `account` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '收款账号信息',
  `server_num` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'QQ号码',
  `home_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '企业URL网站',
  `sort` smallint(5) NOT NULL DEFAULT 99 COMMENT '排序',
  `tax` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '税率',
  `seller_message_ids` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '商户消息ID',
  `grade` int(11) NOT NULL DEFAULT 0 COMMENT '评分总数',
  `sale` int(11) NOT NULL DEFAULT 0 COMMENT '总销量',
  `comments` int(11) NOT NULL DEFAULT 0 COMMENT '评论次数',
  `logo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'LOGO图标',
  `discount` decimal(15, 2) UNSIGNED NOT NULL DEFAULT 100.00 COMMENT '商户结算折扣率',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  UNIQUE INDEX `seller_name`(`seller_name`, `_hash`) USING BTREE,
  INDEX `seller_name_2`(`seller_name`, `password`) USING BTREE,
  INDEX `true_name`(`true_name`) USING BTREE,
  INDEX `is_vip`(`is_vip`) USING BTREE,
  INDEX `is_del`(`is_del`) USING BTREE,
  INDEX `is_lock`(`is_lock`) USING BTREE,
  INDEX `email`(`email`) USING BTREE,
  INDEX `sort`(`sort`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商家表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_seller_message
-- ----------------------------
DROP TABLE IF EXISTS `yh_seller_message`;
CREATE TABLE `yh_seller_message`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '内容',
  `time` datetime(0) NOT NULL COMMENT '发送时间',
  `rev_info` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '收件人信息',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商家消息' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_seller_openid_relation
-- ----------------------------
DROP TABLE IF EXISTS `yh_seller_openid_relation`;
CREATE TABLE `yh_seller_openid_relation`  (
  `seller_id` int(11) UNSIGNED NOT NULL COMMENT '商家ID',
  `openid` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '微信openid',
  `datetime` datetime(0) NOT NULL COMMENT '绑定时间',
  PRIMARY KEY (`seller_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户的openid关系表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_session
-- ----------------------------
DROP TABLE IF EXISTS `yh_session`;
CREATE TABLE `yh_session`  (
  `id` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'session的唯一id',
  `value` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'session序列化数据',
  `time` datetime(0) NOT NULL COMMENT '存储时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = 'session会话表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_spec
-- ----------------------------
DROP TABLE IF EXISTS `yh_spec`;
CREATE TABLE `yh_spec`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '规格名称',
  `value` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '规格值',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '显示类型 1文字 2图片',
  `note` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注说明',
  `is_del` tinyint(1) NULL DEFAULT 0 COMMENT '是否删除1删除',
  `seller_id` int(11) NULL DEFAULT 0 COMMENT '商家ID',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `is_del`(`is_del`) USING BTREE,
  INDEX `seller_id`(`seller_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '规格表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_spec_photo
-- ----------------------------
DROP TABLE IF EXISTS `yh_spec_photo`;
CREATE TABLE `yh_spec_photo`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `address` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '图片地址',
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '图片名称',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '规格图片表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_suggestion
-- ----------------------------
DROP TABLE IF EXISTS `yh_suggestion`;
CREATE TABLE `yh_suggestion`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '内容',
  `time` datetime(0) NOT NULL COMMENT '提问时间',
  `admin_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '管理员ID',
  `re_content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '回复内容',
  `re_time` datetime(0) NULL DEFAULT NULL COMMENT '回复时间',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '意见箱表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_takeself
-- ----------------------------
DROP TABLE IF EXISTS `yh_takeself`;
CREATE TABLE `yh_takeself`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '名称',
  `sort` smallint(5) NOT NULL DEFAULT 99 COMMENT '排序',
  `province` int(11) NOT NULL COMMENT '省份ID',
  `city` int(11) NOT NULL COMMENT '城市ID',
  `area` int(11) NOT NULL COMMENT '地区ID',
  `address` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '地址',
  `phone` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '座机号码',
  `mobile` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '手机号码',
  `seller_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '商家ID',
  `logo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'logo图片',
  `_hash` int(11) UNSIGNED NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`, `_hash`) USING BTREE,
  INDEX `seller_id`(`seller_id`) USING BTREE,
  INDEX `province`(`province`, `city`, `area`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '自提点' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_taxes_log
-- ----------------------------
DROP TABLE IF EXISTS `yh_taxes_log`;
CREATE TABLE `yh_taxes_log`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '管理员ID',
  `user_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '用户id',
  `type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0增加,1减少',
  `event` tinyint(3) NOT NULL COMMENT '操作类型，意义请看accountLog类 11分享佣金,12level奖励,13分红,14零售佣金,15返现佣金,16agent奖励,21税收,22,提现手续费',
  `time` datetime(0) NOT NULL COMMENT '发生时间',
  `value` decimal(15, 2) NOT NULL COMMENT '金额',
  `value_log` decimal(15, 2) NOT NULL COMMENT '每次增减后面的金额记录',
  `note` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '备注',
  `from_uid` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '来源uid',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `admin_id`(`admin_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 821754 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '税收日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_ticket
-- ----------------------------
DROP TABLE IF EXISTS `yh_ticket`;
CREATE TABLE `yh_ticket`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '优惠券名称',
  `value` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '优惠券面额值',
  `start_time` datetime(0) NULL DEFAULT NULL COMMENT '开始时间',
  `end_time` datetime(0) NULL DEFAULT NULL COMMENT '结束时间',
  `point` smallint(5) NOT NULL DEFAULT 0 COMMENT '兑换优惠券所需积分,如果是0表示禁止兑换',
  `seller_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '卖家ID',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `start_time`(`start_time`, `end_time`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '优惠券类型表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_user
-- ----------------------------
DROP TABLE IF EXISTS `yh_user`;
CREATE TABLE `yh_user`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户名',
  `password` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '密码',
  `head_ico` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '头像',
  `parent_id` int(11) NULL DEFAULT 0 COMMENT '分享id',
  `level` tinyint(4) NULL DEFAULT 0 COMMENT '等级:0.表示非激活状态，11.会员，12vip，13经销商、21区县，22市，23省，31总，32合伙人',
  `team_sum` int(11) NULL DEFAULT 0 COMMENT '团队人数，新增vip时更新',
  `active_amount` decimal(15, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT 'vip金额',
  `check_time` datetime(0) NULL DEFAULT NULL COMMENT 'vip激活时间',
  `is_bonus` int(2) UNSIGNED NULL DEFAULT 1 COMMENT '参与分红 默认1参加 2后台操作不参加',
  `is_agent` int(2) UNSIGNED NULL DEFAULT 0 COMMENT '是否加盟店，默认0不是，1是',
  `agent_level` tinyint(4) NULL DEFAULT 0 COMMENT '加盟店级别，1社区店，2标准店，3旗舰店',
  `fir_stocks` int(11) NULL DEFAULT 0 COMMENT '干股1',
  `sec_stocks` int(11) NULL DEFAULT 0 COMMENT '干股2',
  `tran_password` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '交易密码',
  `share_qrcode` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '分享二维码',
  `taxes` decimal(15, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT ' 税收',
  `revisit` decimal(15, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '重复消费',
  `cash_back` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '订单消费金额用于满返',
  `is_empty` int(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否空单 0不是 1是(空单) 默认0',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 15251 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_user_group
-- ----------------------------
DROP TABLE IF EXISTS `yh_user_group`;
CREATE TABLE `yh_user_group`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户组ID',
  `group_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '组名',
  `discount` decimal(15, 2) NOT NULL DEFAULT 100.00 COMMENT '折扣率',
  `minexp` int(11) NULL DEFAULT NULL COMMENT '最小经验',
  `maxexp` int(11) NULL DEFAULT NULL COMMENT '最大经验',
  `message_ids` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '消息ID',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户组' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_vip_order
-- ----------------------------
DROP TABLE IF EXISTS `yh_vip_order`;
CREATE TABLE `yh_vip_order`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_no` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '订单号',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `pay_type` int(11) NOT NULL COMMENT '用户支付方式ID',
  `active_id` int(11) UNSIGNED NOT NULL COMMENT '要激活的用户id',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '订单状态 1生成订单,2支付订单,3取消订单(客户触发),4作废订单(管理员触发),5完成订单,6退款(订单完成后)',
  `pay_status` tinyint(1) NULL DEFAULT 0 COMMENT '支付状态 0：未支付; 1：已支付;',
  `pay_time` datetime(0) NULL DEFAULT NULL COMMENT '付款时间',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '下单时间',
  `completion_time` datetime(0) NULL DEFAULT NULL COMMENT '订单完成时间',
  `note` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '管理员备注',
  `if_del` tinyint(1) NULL DEFAULT 0 COMMENT '是否删除1为删除',
  `order_amount` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '订单总金额',
  `point` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '增加的积分,预留',
  `sec_stocks` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '增加的股权,预留',
  `accept_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '收货人姓名',
  `province` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '省ID',
  `city` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '市ID',
  `area` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '区ID',
  `address` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '收货地址',
  `distribution` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '1:自提,2:物流',
  `distribution_status` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '配送状态 0：未发送,1：已发送',
  `mobile` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '手机',
  `postscript` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户附言',
  `freight_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '货运公司ID',
  `delivery_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '物流单号',
  `delivery_time` datetime(0) NOT NULL COMMENT '发货时间',
  `order_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '订单表关联id',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `if_del`(`if_del`) USING BTREE,
  INDEX `order_no`(`order_no`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `order_amount`(`order_amount`) USING BTREE,
  INDEX `completion_time`(`completion_time`) USING BTREE,
  INDEX `create_time`(`create_time`) USING BTREE,
  INDEX `pay_status`(`pay_status`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7279 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = 'vip会员激活表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_wechat_template_message
-- ----------------------------
DROP TABLE IF EXISTS `yh_wechat_template_message`;
CREATE TABLE `yh_wechat_template_message`  (
  `short_id` varchar(120) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '模板短ID',
  `template_id` varchar(120) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '模板长ID',
  PRIMARY KEY (`short_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '模板消息ID关系表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_withdraw
-- ----------------------------
DROP TABLE IF EXISTS `yh_withdraw`;
CREATE TABLE `yh_withdraw`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `time` datetime(0) NOT NULL COMMENT '时间',
  `amount` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '金额',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '开户姓名',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '-1失败,0未处理,1处理中,2成功',
  `note` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户备注',
  `re_note` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '回复备注信息',
  `is_del` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0未删除,1已删除',
  `free_amount` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '免服务费额度',
  `service_free` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '提现手续费',
  `bank` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '开户行',
  `province` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '开户行所在省',
  `city` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '开户行所在市',
  `bank_branch` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '开户支行',
  `card_num` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '卡号',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  CONSTRAINT `yh_withdraw_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `yh_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 654 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '提现记录' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_wz_member
-- ----------------------------
DROP TABLE IF EXISTS `yh_wz_member`;
CREATE TABLE `yh_wz_member`  (
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `true_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '真实姓名',
  `telephone` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '联系电话',
  `mobile` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '手机',
  `area` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '地区',
  `contact_addr` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '联系地址',
  `qq` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'QQ',
  `sex` tinyint(1) NOT NULL DEFAULT 1 COMMENT '性别1男2女',
  `birthday` date NULL DEFAULT NULL COMMENT '生日',
  `group_id` int(11) NULL DEFAULT NULL COMMENT '分组',
  `exp` int(11) NOT NULL DEFAULT 0 COMMENT '经验值',
  `point` int(11) NOT NULL DEFAULT 0 COMMENT '积分',
  `message_ids` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '消息ID',
  `time` datetime(0) NULL DEFAULT NULL COMMENT '注册日期时间',
  `zip` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '邮政编码',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '用户状态 1正常状态 2 删除至回收站 3锁定',
  `prop` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '用户拥有的工具',
  `balance` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '用户余额',
  `last_login` datetime(0) NULL DEFAULT NULL COMMENT '最后一次登录时间',
  `custom` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户习惯方式,配送和支付方式等信息',
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Email',
  `freeze_balance` decimal(15, 2) NOT NULL DEFAULT 0.00 COMMENT '冻结余额',
  `qrcode` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '二维码地址',
  `equity` int(11) NOT NULL DEFAULT 0 COMMENT '股权',
  `protocol` int(1) NOT NULL DEFAULT 0 COMMENT '协议',
  `is_equity` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否允许股权增长',
  PRIMARY KEY (`user_id`) USING BTREE,
  INDEX `group_id`(`group_id`) USING BTREE,
  INDEX `mobile`(`mobile`) USING BTREE,
  INDEX `email`(`email`) USING BTREE,
  INDEX `status`(`status`, `true_name`) USING BTREE,
  CONSTRAINT `yh_wz_member_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `yh_wz_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户信息表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yh_wz_user
-- ----------------------------
DROP TABLE IF EXISTS `yh_wz_user`;
CREATE TABLE `yh_wz_user`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户名',
  `password` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '密码',
  `head_ico` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '头像',
  `invite_id` int(11) NULL DEFAULT 0 COMMENT '分享id',
  `parent_id` int(11) NOT NULL DEFAULT 0 COMMENT '上级用户id（由邀请人设置）',
  `level` tinyint(4) NULL DEFAULT 0 COMMENT '等级:0.表示非激活状态，10表示销售代表，购买1500的，11.经销商，12助理主任，13主任、21高级主任，22助理经理，23经理，31高级经理，32总监，33高级总监',
  `path` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '顺序结构',
  `group_code` int(255) NOT NULL DEFAULT 0 COMMENT '团队编号',
  `tran_password` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '交易密码',
  `pathway` tinyint(2) NOT NULL DEFAULT 0 COMMENT '分区数量，0.默认，2.两个轨道，3.三个轨道，4.四个轨道',
  `is_reporter` int(11) NOT NULL DEFAULT 0 COMMENT '是否允许报单:0表示不允许，1表示允许',
  `is_wxshare` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否允许分享微信体验装:0表示不允许，1表示允许',
  `share_qrcode` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '分享二维码',
  `money` decimal(15, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '报单金额',
  `reporter_ratio` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '报单中心提成百分比',
  `profit` int(2) UNSIGNED NULL DEFAULT 1 COMMENT '参与分红 默认1参加 2不参加后台手动增加',
  `preparation` decimal(11, 2) NOT NULL DEFAULT 0.00 COMMENT '预备报单金',
  `m_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '后台审核状态：0 待审核 1 审核通过 2 审核失败 3 作废',
  `check_time` datetime(0) NULL DEFAULT NULL COMMENT '后台审核时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3464 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户表' ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
