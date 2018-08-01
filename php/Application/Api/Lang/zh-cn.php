<?php
/**
 * 简体中文语言包
 */
return array(
    /* 接口语言变量 */  
    'API_LANG'     => array(
		//公共提示,号段[100000-100030]
		'100000'=>'系统错误',
		'100001'=>'服务器时间异常',
		'100002'=>'签名信息不能为空',
		'100003'=>'参数校验失败',
		'100004'=>'您的账号已过期，请重新登录',
		'100005'=>'用户不存在',
		'100006'=>'未知错误',
		'100007'=>'调用失败',
		'100008'=>'接口参数错误',
		'100009'=>'接口不存在',
		'100010'=>'更新成功',
		'100011'=>'更新失败',
		'100012'=>'数据无修改',
		'100013'=>'请求成功',
		'100014'=>'系统提示 ：',
		'100015'=>'没有更多数据了',
		'100016'=>'提交成功',
		'100017'=>'提交失败',
		'100018'=>'请求成功',
		'100019'=>'请求失败',
		'100020'=>'会员入住申请审核中',
		'100021'=>'会员入住申请已通过',
		'100022'=>'删除成功',
		'100023'=>'删除失败',

        
		//用户提示,号段[100031-100100]
		'100031'=>'用户名不能为空',
		'100032'=>'密码不能为空',
		'100033'=>'用户不存在或被禁用',
		'100034'=>'密码错误',
		'100035'=>'手机号格式不正确',
		'100036'=>'两次密码输入不一致',
		'1000361'=>'原密码错误',
		'100037'=>'登录失败',
		'100038'=>'确认密码不能为空',
		'100039'=>'验证码不能为空',
		'100040'=>'用户ID不能为空',
		'100041'=>'用户hashid不能为空',
		'100042'=>'用户头像ID不能为空',
		'100043'=>'原始密码不能为空',
		'100044'=>'新密码不能为空',
		'100045'=>'手机号不能为空',
		'100046'=>'注册类型不能为空[0普通注册 1手机注册 2QQ注册3微信注册 4新浪微博注册]',
		'100047'=>'注册类型错误[0普通注册,1手机注册,2QQ注册3微信注册4新浪微博注册]',
		'1000470'=>'请选择登录方式【1QQ 2微信 3支付宝 4新浪微博】',
		'100048'=>'授权ID不能为空',
		'100049'=>'登录成功',
		'100050'=>'注册成功',
		'100051'=>'注册失败',
		'100052'=>'日期格式有误[yyyy-mm-dd]',
		'100053'=>'个性签名字不能超过32个字符',
		'100054'=>'职业不能超过16个字符',
		'100055'=>'已经变更过一次性别了',
		'100056'=>'用户昵称不能为空',
		'100057'=>'用户头像不能为空',
		'100058'=>'用户性别不能为空[1-男 2-女]',
		'1000590'=>'被关在者ID不能为空',
		'1000591'=>'关注类型不能为空[1关注 2取消关注]',
		'1000592'=>'关注类型不存在[1关注 2取消关注]',
		'1000593'=>'关注成功',
		'1000594'=>'取消成功',
		'1000595'=>'被关注的用户不存在',
		'100060'=>'已经绑定过手机号了',
		'100061'=>'绑定成功',
		'100070'=>'拉黑成功',
		'100071'=>'已取消拉黑',
		'100072'=>'类型不存在[1拉黑 2取消拉黑]',
		'100073'=>'用户不存在',
		'100074'=>'拉黑用户不能为空',
		'100075'=>'拉黑类型不能为空[1拉黑 2取消拉黑]',
		'100076'=>'对方用户ID不能为空',
		'100077'=>'关注类型不能为空[1关注-2未关注]',
		'100078'=>'对方环信账号不能为空',
		'100079'=>'一天内对同一个用户只能举报一次',
		'100080'=>'不能和自己聊天',
		'100081'=>'不能关注自己',
		'100082'=>'不能大于当前时间',
        '100083'=>'请先登录',
        '100084'=>'收货详细地址',
        '100085'=>'收货人',
        '100086'=>'收货地区',
        '100087'=>'收货人手机号码',
        '100088'=>'收货地址ID',
		'100089'=>'请输入省',
		'100090'=>'请输入市',
		'100091'=>'请输入区',
		
		//'100056'=>'',
		//'100056'=>'',
		//'100056'=>'',
		//短息发送,号段[100101-100115]
		'100101'=>'短信发送类型不能为空[1手机短信注册 2找回密码 3手机短信登录 4解除手机绑定 5手机绑定 6绑定第三方]',
		'100102'=>'短信发送类型错误[1手机短信注册 2找回密码 3手机短信登录 4解除手机绑定 5手机绑定 6绑定第三方]',
		'100103'=>'手机号未注册',
		'100104'=>'短息发送频率太快',
		'100105'=>'短信发送次数已达上限',
		'100106'=>'短信内容不能为空',
		'100107'=>'短信发送失败',
		'100108'=>'短信验证码不能为空',
		'100109'=>'手机验证码错误',
		'100110'=>'发送成功',
		'100111'=>'搜索关键字不能为空',
		'100112'=>'手机号已注册',
		//直播室,号段[100201-100300]
		'100201'=>'直播室不存在',
		'100202'=>'筛选类型不能为空[1关注 2区域 3附近]',
		'100203'=>'筛选类型不存在[1关注 2区域 3附近]',
		'100204'=>'你已被禁播，禁播时间至：',
		'100205'=>'用户类型不能为空[1男 2女 3全部]',
		'100206'=>'用户类型不存在[1男 2女 3全部]',
		'100207'=>'区域ID不能为空',
		'100208'=>'用户位置不能为空[用户经纬度值]',
		'100209'=>'用户未开播',
		'100210'=>'直播室ID不能为空',
		'100211'=>'直播记录类型不能为空[1最新 2最热]',
		'100212'=>'直播记录类型不存在',
		'100213'=>'直播记录不存在',
		'100214'=>'直播记录ID不能为空',
		'100215'=>'这次直播没有下播，无法播放',
		'100216'=>'用户类型不能为空[1主播-2观众]',
		'100217'=>'视频过短，无法播放',
		'100218'=>'这次直播没有下播，无法保存',
		'100219'=>'视频过短，无法保存',
		//文件上传,号段[100301-100350]
		'100301'=>'上传名称不能为空',
    		//实名认证,号段[100401-100500]
		'100402'=>'真实姓名不能为空',
		'100403'=>'手机号码不能为空',
		'100404'=>'银行卡号不能为空',
		'100405'=>'开户银行不能为空',
		'100406'=>'开户省份ID不能为空',
		'100407'=>'开户市ID不能为空',
		'100408'=>'支行名称不能为空',
		'100409'=>'证件类型不能为空[1身份证]',
		'100410'=>'证件号不能为空',
		'100411'=>'证件正面照ID不能为空',
		'100412'=>'证件反面照ID不能为空',
		'100413'=>'手持证件正面照ID不能为空',
		'100414'=>'手机号格式有误',
		'100415'=>'证件类型有误',
		'100416'=>'开户地址有误',
		'100417'=>'申请失败',
		'100418'=>'已提交审核',
    		//用户中心,号段[100501-100700]
		'100501'=>'票数量不能为空',
		'100502'=>'当前票数量不足',
		'100503'=>'账户未通过实名认证',
		'100504'=>'票数不能小于10',
		'100505'=>'钻石换算失败',
		'100506'=>'兑换成功',
		'100507'=>'钻石数量有误',
		'100508'=>'赠送成功',
		'100509'=>'票数不足',
		'100510'=>'提现金额必须大于',
		'100511'=>'提现申请成功',
		'100512'=>'钻石不足',
    	//支付,号段[100701-100800]
		'100701'=>'商品不存在',
		'100702'=>'商品已下架',
		'100703'=>'商品价格有误',
		'100704'=>'支付方式有误[1支付宝 2微信]',
		'100705'=>'获取微信prepay_id失败',
		'100706'=>'支付类型不能为空[1支付宝 2微信]',
		'100707'=>'商品ID',
		'100708'=>'列表数',
        '100709'=>'页码(从1开始)',

    	//系统设置,号段[100801-100900]
		'100801'=>'设置类型不能为空[1未关注私信设置 2开播提醒全局设置 3开播提醒个人设置]',
		'100802'=>'设置类型不存在[1未关注私信设置 2开播提醒全局设置 3开播提醒个人设置]',
		'100803'=>'设置成功',
		'100804'=>'关注者ID不能为空',
    	//意见反馈,号段[100901-101000]
		'1009010'=>'投诉商家不能为空',
    	'100901'=>'反馈内容不能为空',
    	'100902'=>'联系方式不能为空',
    	'100903'=>'反馈内容不少于6个字符',
    	'100904'=>'一天只能提交一条',
    	'100905'=>'提交成功',
    	//文章列表,号段[101001-101100]
    	'101001'=>'文章类型不能为空[1帮助与反馈-2关于我们]',
    	'101002'=>'协议类型不能为空[1服务与帮助条款-2主播协议]',
    	'101003'=>'文章类型不存在',
    	//分享
    	'101101'=>'分享类型不能为空[1直播-2回放]',
    	'101102'=>'分享类型不存在',

    		
    	//商家端
    	'101601'=>'请输入开户人',
    	'101602'=>'请输入银行账号',
		'101603'=>'请输入负责人',
		'101604'=>'请输入手机号码',
		'101605'=>'请选择商户类型',
		'101606'=>'请选择促销提拨金比例',
		'101607'=>'请选择省',
		'101608'=>'请选择市',
		'1016081'=>'请选择区',
		'101609'=>'请输入详细地址',
		'101610'=>'请输入密码',
		'101611'=>'请选择证件类型',
		'101612'=>'请输入证件姓名',
		'101613'=>'请输入证件号',
		'101614'=>'请上传证件照正面',
		'101615'=>'请上传证件照反面',
		'101616'=>'请上传营业执照',
		'101617'=>'请输入店家详情介绍',
		'101618'=>'请上传店头照片',
		'101619'=>'请上传收款码',
		'101620'=>'请上传店家照片',
		'101621'=>'请输入营业电话',
		'101622'=>'请输入邀请码',
		'101623'=>'请输入提现金额',
		'101624'=>'请输入提拨金',
		'101625'=>'请输入银行卡',
		'101626'=>'请输入张数id',
		'101627'=>'商家不存在',
		'101628'=>'请输入金额',
		'101629'=>'请输入经度',
		'101630'=>'请输入纬度',
		'101631'=>'距离排序【1asc从近到远 2desc从远到近】',
		'101632'=>'请输入当前网页的URL',
		
		//福利商城
		'101701'=>'请输入商品分类【空为所有分类】',
		'101702'=>'请输入商品排序类型【空综合 1新品 2热门 3价格 4折扣 5限量】',
		'101703'=>'请输入下单类型【1立即购买 2购物车】',
		'101704'=>'请输入商品id',
		'101705'=>'请输入商品规格id',
		'101706'=>'请输入商品数量',
		'101707'=>'请输入购买商品信息',
		'101708'=>'请输入购物车id',
		'101709'=>'商品数据为空',
		'101710'=>'购物车为空',
		'101711'=>'请输入买家留言',
		'101712'=>'请输入商家编号',
		'101713'=>'库存不足',
		'101714'=>'加入购物车成功',
		'101715'=>'加入购物车失败',
		'101716'=>'购物车操作类型【1修改 2删除】',
		'101717'=>'商品数量不能小于1',
		'101718'=>'修改购物车成功',
		'101719'=>'修改购物车失败',
		'101720'=>'取消收藏成功',
		'101721'=>'收藏成功',
		'101722'=>'收藏失败',
		'101723'=>'请输入消息id',
		'101724'=>'请输入修改的个人信息',
		'101725'=>'请输入鑫利豆',
		'101726'=>'请输入享利豆',
		'101727'=>'请输入福利豆',
		'101728'=>'请输入订单分类【0或空全部订单 1未完成 2待取货 3已完成】',
		'101729'=>'请输入订单id',
		'1017290'=>'订单id不能为空',
		'101730'=>'请输入退款说明',
		'101731'=>'请上传退款凭证(多个图片用逗号隔开)',
		'101732'=>'该订单已申请退款，请不要重复提交退款',
		'101733'=>'请输入姓名',
		'101734'=>'请输入电话',
		'101735'=>'请输入订单分类【0或空全部订单 1今日订单 2历史订单】',
		
		
		//用户中心
		'101800'=>'请输入用户名',
		'101801'=>'请输入手机号',
		
		
		
		'101802'=>'请输入公司名称',
		'101803'=>'请输入公司地址',
		'101804'=>'请输入招聘岗位',
		'101805'=>'请输入招聘人数',
		'101806'=>'请输入待遇',
		'101807'=>'请输入岗位描述',
		'101808'=>'请输入招聘id',
		'101809'=>'请上传公司logo',
		'101810'=>'请输入推广形态【1消费让利推广 2商家视频】',
		'101811'=>'请输入推广方式【以米为单位的数字】',
		'101812'=>'请输入推广人数',
		'101813'=>'请输入推广单价',
		'101814'=>'请输入推广期间【给我时间格式（2018-06-06）】',
		'101815'=>'请输入视频时长',
		'101816'=>'请输入观看人数',
		'101817'=>'请输入营业电话',
		'101818'=>'请输入商家地址',
		'101826'=>'请输入商家id',
		'101827'=>'没有需要支付的订单',
		'101828'=>'请输入订单类型【1让利推广 2商家视频】',
		'101829'=>'请先绑定手机',
		'101830'=>'请输入第三方id',
		'101831'=>'请输入订单类型【order(福利商城) advertisement(让利推广) video(商家视频)】',
		'101832'=>'请输入id',
	),
);
