<?php
namespace Api\Helper;
use User\Api\UserApi;
/**
 * 用户助手
 */
class UserHelper extends BaseHelper{
	//初始化接口
	public function apiRun($parame = ''){
		//接口分发
		$Parame		= !empty($parame) ? json_decode($parame,true) : '';
		$ac			= $Parame['ac'];
		$isapi		= $Parame['isapi'];
		if ($isapi === true){
			return !empty($ac) ? $this->$ac($Parame) : array('Code' =>'100009','Msg'=>$this->Lang['100009']);
		}
		return array('Code' =>'100007','Msg'=>$this->Lang['100007']);
	}

	//登录
	private function login($Parame){
		//登录验证
		$username 	= safe_replace($Parame['username']);//过滤
		$password	= md5($Parame['password']);
		
		$user = M('user')->where(array('phone'=>$username, 'type'=>2, 'status'=>1))->find();
		if(empty($user['id'])){
			return array('Code' =>'100033','Msg'=>$this->Lang['100033']);
		}
		if($user['password'] != $password){
			return array('Code' =>'100034','Msg'=>$this->Lang['100034']);
		}
		//UC登录成功	
		if($user['id'] > 0){
			if(!empty($user) && $user['id'] > 0){
				//绑定极光设备
				if(!empty($Parame['jpushid'])){
					if($user['jpushid'] != $Parame['jpushid']){
						M('user')->where(array('jpushid'=>$Parame['jpushid']))->save(array('jpushid'=>''));
						M('user')->where(array('phone'=>$username))->save(array('jpushid'=>$Parame['jpushid']));
						$msg 			= '检测到您的迷路客账号在别处登录，如果不是本人操作，请及时修改密码';
						//sendJPush(array($user['jpushid']),$msg,array('type'=>2));//type=2异常登录
					}
				}
				/*登录成功后*/
				$data					= array();
				$data['uid']			= intval($user['id']);
				$data['hashid']			= $this->create_hashid($user['id']);
				$data['face']			= $user['avatar'];
				$data['nickname']		= !empty($user['nickname']) ? $user['nickname'] : '';
				$data['phone']			= !empty($username) ? $username : '';
				$data['check_status']	= $user['check_status'];
				return array('Code' =>'0','Msg'=>$this->Lang['100049'],'Data'=>$data);
			} else {
				return array('Code' =>'100037','Msg'=>$this->Lang['100037']);
			}
		} else { //登录失败
			return array('Code' => '100037','Msg'=>$this->Lang['100037']);
		}
	}
	
	//注册
	private function register($Parame){
		$username			= safe_replace($Parame['username']);
		$mobile				= safe_replace($Parame['username']);
		
		if (empty($username)){
			return array('Code' =>'100031','Msg'=>$this->Lang['100031']);
		}
		if (Mobile_check($mobile,array(1)) == false){
			return array('Code' =>'100035','Msg'=>$this->Lang['100035']);
		}
		if ($Parame['password'] !== $Parame['repeatpwd']){
			return array('Code' =>'100036','Msg'=>$this->Lang['100036']);
		}
		$_POST				= array();
		$_POST['username']	= $username;
		$_POST['mobile']	= $mobile;
		//开始注册
		$password			= $Parame['password'];
		$user 				= new UserApi;
		$uid 				= $user->register($username, $password);//返回ucentermember数据表用户主键id
		//注册成功
		if(0 < $uid){
			//自动分配到对应组里面
			$accessinfo					= M('auth_group_access')->where(array('uid'=>$uid,'group_id'=>$Parame['groupid']))->find();
			if (empty($accessinfo)){
				M('auth_group_access')->add(array('uid'=>$uid,'group_id'=>$Parame['groupid']));
			}
			//记录日志
			//addUserLog('新会员注册', $uid);
			//调用登陆
			$ucuserinfo					= $user->info($uid);
			if(!empty($ucuserinfo) && $ucuserinfo['id'] > 0){
				D("member")->login($ucuserinfo['id']);
				//上级id
				if(!empty($Parame['code'])){
					$pid = M('member')->where(array('code'=>$Parame['code']))->getField('uid');
					if($pid>0){
						M('member')->where(array('uid'=>$uid))->save(array('pid'=>$pid));
						M('member')->where(array('uid'=>$pid))->save(array('next_people_count'=>array('exp',"next_people_count+1")));
					}
				}
				//用户信息
				$memberInfo 			= M('member')->where(array('uid'=>$uid))->field(array('nickname','face','last_login_time'))->find();
				/*登录成功后*/
				$data['uid']			= intval($ucuserinfo['id']);
				$data['hashid']			= $this->create_hashid($uid);
				$data['username']		= !empty($ucuserinfo['username']) ? $ucuserinfo['username'] : '';
				$data['email']			= !empty($ucuserinfo['email']) ? $ucuserinfo['email'] : '';
				$data['mobile']			= !empty($ucuserinfo['mobile']) ? $ucuserinfo['mobile'] : '';
				$data['nickname']		= !empty($memberInfo['nickname']) ? $memberInfo['nickname'] : '';
				$data['face']			= $memberInfo['face']>0 ? 'http://'.WEB_DOMAIN.get_cover($memberInfo['face'],'path') : '';
				return array('Code' =>'0','Msg'=>"注册成功!",'Data'=>$data);
			} else {
				return array('Code' =>'100006','Msg'=>$this->Lang['100006']);
			}
		} else {
			//注册失败，显示错误信息
			$code= ($uid * (-1)-1)+100100;
			return array('Code' =>$code,'Msg'=>$user->showRegError($uid));
		}
	}
	
	//获取用户信息
	private function getUserInfo($Parame){
		//用户ID检验
		$ucheck = $this->check_user($Parame['uid'], $Parame['hashid']);
		if ($ucheck['Code'] > 0) return $ucheck;


		//获取数据
		$MainTab					= 'ucenter_member';
		$MainAlias					= 'main';
		$MainField					= array('id as uid');
		/*
		 * 灵活定义关联查询
		 * Ralias 	关联表别名
		 * Ron    	关联条件
		 * Rfield	关联查询字段，
		 * */
		$RelationTab				= array();
		$RelationTab['member']		= array('Ralias'=>'me','Ron'=>'me ON me.uid=main.id','Rfield'=>array());
		$RelationTab				= $this->getRelationTab($RelationTab);
		$tables	  					= $RelationTab['tables'];
		$RelationFields				= $RelationTab['fields'];
		//主表模型
		$MainModel 					= M($MainTab)->alias($MainAlias);
		$model						= !empty($tables) ? $MainModel->join ( $tables ,'LEFT') : $MainModel;
		//检索条件
		$map[$MainAlias.'.status']  = 1;
		$map[$MainAlias.'.id']  	= intval($Parame['uid']);
		$map['me.uid']  			= intval($Parame['uid']);
		//检索字段
		$fields						= (empty($MainField) ? get_fields_string($MainModel->getDbFields(),$MainAlias).',' : get_fields_string($MainField,$MainAlias).',') . $RelationFields;
		$fields						= trim($fields,',');
		//列表数据
		$info 						= $this->getOne($model,$map,$fields);
		if (!empty($info)){
			$gender					= array('保密','男','女');
			$emotion				= array('保密','单身','恋爱中','已婚','同性');
			$liveno					= M('direct_seeding_room')->where(array('uid'=>$info['uid']))->getField('liveno');
			//数据格式化
			$pic					= intval($info['face']) > 0 ? get_cover(intval($info['face']),'path') : '';
			$info['face']			= !empty($pic) ? 'http://'.WEB_DOMAIN.$pic : '';
			$info['uid']			= intval($info['uid']);
			$info['account']		= $info['account']*1;
			$info['gender']			= $gender[intval($info['gender'])];
			$info['isgender']		= intval($info['isgender']);
			$info['province']		= intval($info['province']);
			$info['birthday']		= date('Y-m-d',$info['birthday']);
			$info['area']			= intval($info['area']);
			$info['county']			= intval($info['county']);
			$info['level']			= intval($info['level']);
			$info['age']			= intval($info['age']);
			$info['emotion']		= $emotion[intval($info['emotion'])];
			$info['liveno']			= !empty($liveno) ? $liveno*1 : 0;
			$info['ticket_num']		= intval($info['ticket_num']);
			//粉丝、关注
			$followModel 			= M('user_follow');
			$info['follow'] 		= $followModel->where(array('fid'=>$info['uid']))->count(); 	//粉丝数
			$info['attention'] 		= $followModel->where(array('uid'=>$info['uid']))->count(); 	//关注数
			$info['record_num'] 	= M('direct_seeding_record')->where(array('uid'=>$info['uid'],'start_time'=>array('gt',0),'end_time'=>array('gt',0)))->count(); //直播记录数
				

				
				
			unset($info['login']);
			unset($info['last_login_ip']);
			unset($info['last_login_time']);
			unset($info['rules']);
			unset($info['score']);
		}
		$info						= !empty($info) ? $info : (object)$info;
		return array('Code' =>'0','Msg'=>'ok','Data'=>$info);
	}
	//更新用户信息
	private function update($Parame){
		//用户ID检验
		$ucheck = $this->check_user($Parame['uid'], $Parame['hashid']);
		if ($ucheck['Code'] > 0){
			return $ucheck;
		}
		$data		= json_decode($Parame['updata']);
		if (!empty($data)){
			$member			= D("member");
			//重构入库字段 防止模拟其它字段
			$allowField		= array('nickname','face','gender','birthday','signature','emotion','occupation','address','province','area','county','gender');
			$updata['uid']	= $Parame['uid'];
			foreach ($data as $key=>$val){
				if (in_array($key, $allowField)) $updata[$key] = $val;
			}
			//参数整理
			if (!empty($updata['birthday'])){
				$isData			= strtotime( date('Y-m-d', strtotime($updata['birthday'])) ) === strtotime( $updata['birthday'] );
				if (!$isData){
					return array('Code' =>'100052','Msg'=>$this->Lang['100052']);
				}
				$birthday					= strtotime($updata['birthday']);
				if ($birthday > NOW_TIME){
					return array('Code' =>'100082','Msg'=>$this->Lang['100082']);
				}
				//计算年龄
				$updata['age']				= 2017-date('Y',$birthday);
				$updata['zodiac']			= get_animal(date('Y',$birthday));
				$updata['constellation']	= get_constellation(date('m',$birthday),date('d',$birthday));
				$updata['birthday']			= $birthday;
			}
			if (!empty($updata['signature'])){
				if (mb_strlen($updata['signature'],'utf-8') > 32){
					return array('Code' =>'100053','Msg'=>$this->Lang['100053']);
				}
			}
			if (!empty($updata['occupation'])){
				if (mb_strlen($updata['occupation'],'utf-8') > 16){
					return array('Code' =>'100053','Msg'=>$this->Lang['100053']);
				}
			}
			if ($updata['gender'] > 0){
				if (!in_array($updata['gender'], array(1,2))){
					$updata['gender']	= 1;
				}
				//检查性别是否修改过一次
				$genderInfo		= $member->where(array('uid'=>$Parame['uid']))->field('isgender,gender')->find();
				if (($genderInfo['isgender'] >= 1 && $updata['gender'] != $genderInfo['gender'] && $Parame['checkGender'] == true) || ($updata['gender'] != $genderInfo['gender'] && $genderInfo['isgender'] >= 2)){
					return array('Code' =>'100055','Msg'=>$this->Lang['100055']);
				}
				$updata['isgender'] = $genderInfo['isgender']+1;
			}
			if (!empty($updata['province'])){
				$updata['province_name']		= M('area')->where(array('id'=>$updata['province']))->getField('area');
			}
			if (!empty($updata['area'])){
				$updata['area_name']		= M('area')->where(array('id'=>$updata['area']))->getField('area');
			}
			//......


			$res			= $member->update($updata);
			if(intval($res) > 0){
				//如果是头像 返回头像地址
				if (isset($updata['face']) && $updata['face'] >0){
					$pic					= intval($updata['face']) > 0 ? get_cover(intval($updata['face']),'path') : '';
					$updata['face']			= !empty($pic) ? 'http://'.WEB_DOMAIN.$pic : '';
				}
				return array('Code' =>'0','Msg'=>$this->Lang['100010'],'Data'=>$updata);
			} else {
				return array('Code' =>'100011','Msg'=>$this->Lang['100011']);
			}
		}
		else{
			return array('Code' =>'100012','Msg'=>$this->Lang['100012']);
		}
	}
	//密码修改
	private function setPwd($Parame){
		//用户ID检验
		$uid 		= intval($Parame['uid']);
		$hashid 	= trim($Parame['hashid']);
		$user 		= M('user')->where(array('id'=>$uid))->find();
		if($user['id']<1){
			return array('Code' =>'100073','Msg'=>$this->Lang['100073']);
		}
		if (md5($uid.C('DATA_AUTH_KEY').$user['last_login_time']) !== $hashid){
			return array('Code' =>'100004','Msg'=>$this->Lang['100004']);
		}
		if($user['password'] != md5($Parame['oldpwd'])){
			return array('Code' =>'1000361','Msg'=>$this->Lang['1000361']);
		}
		if ($Parame['newpwd'] != $Parame['repeatpwd']){
			return array('Code' =>'100036','Msg'=>$this->Lang['100036']);
		}
		$data				= array();
		$data['password']	= md5($Parame['newpwd']);
		$res = M('user')->where(array('uid'=>$uid))->save($data);
		if($res != false){
			return array('Code' =>'0','Msg'=>$this->Lang['100010']);
		}
		return array('Code' =>'100011','Msg'=>$this->Lang['100011']);
	}
	
	//第三方登录
	protected function oauthLogin($Parame){
		$type 	= $Parame['type'];
		$openid = $Parame['openid'];
		$userOauth = M('user_oauth')->where(array('type'=>$type, 'openid'=>$openid))->find();
		if(empty($userOauth['id'])){
			$oauthId = M('user_oauth')->add(array('type'=>$type, 'openid'=>$openid, 'nickname'=>$Parame['nickname'], 'sex'=>$Parame['sex'], 'headimgurl'=>$Parame['headimgurl']));
			return array('Code' =>'0','Msg'=>$this->Lang['101829'],'Data'=>array('status'=>1,'oauth_id'=>$oauthId));
		}else{
			if($userOauth['uid']>0){
				$uid					= $userOauth['uid'];
				//绑定极光设备
				if(!empty($Parame['jpushid'])){
					$jpushid 	= M('user')->where(array('id'=>$uid))->getField('jpushid');
					if($jpushid != $Parame['jpushid']){
						M('user')->where(array('jpushid'=>$Parame['jpushid']))->save(array('jpushid'=>''));
						M('user')->where(array('id'=>$uid))->save(array('jpushid'=>$Parame['jpushid']));
						$msg 			= '检测到您的迷路客账号在别处登录，如果不是本人操作，请及时修改密码';
						//sendJPush(array($user['jpushid']),$msg,array('type'=>2));//type=2异常登录
					}
				}
				//用户信息
				$user 				= M('user')->where(array('id'=>$uid))->find();
				/*登录成功后*/
				$data				= array();
				$data['uid']		= $uid;
				$data['hashid']		= $this->create_hashid($uid);
				$data['face']		= $user['avatar'];
				$data['nickname']	= !empty($user['nickname']) ? $user['nickname'] : '';
				$data['phone']		= !empty($user['phone']) ? $user['phone'] : '';
				$data['status']		= 0;
				$data['oauth_id']	= $userOauth['id'];
				return array('Code' =>'0','Msg'=>$this->Lang['100049'],'Data'=>$data);
			}else{
				return array('Code' =>'0','Msg'=>$this->Lang['101829'],'Data'=>array('status'=>1,'oauth_id'=>$userOauth['id']));
			}
		}
	}
	
	//绑定第三方
	protected function oauthBindOne($Parame){
		
		$oauthId			= $Parame['oauthid'];
		$mobile				= $Parame['username'];
		$checkcode			= $Parame['checkcode'];
		$code				= $Parame['code'];
		
		//首先验证手机号是否合法
		if (Mobile_check($mobile,array(1,2,3,4)) == false){
			return array('Code' =>'100035','Msg'=>$this->Lang['100035']);
		}
		//验证验证码是否正确//测试账号，不需要验证码
		$ischeck			= R('Api/Sms/checkcode', array('mobile'=>$mobile,'code'=>$checkcode,'type'=>6));
		if ($ischeck <= 0){
			return array('Code' =>'100109','Msg'=>$this->Lang['100109']);
		}
		
		//验证手机号是否注册，未注册执行注册
		$count = M('user')->where(array('phone'=>$mobile))->count('id');
		//未注册,开始注册
		if($count<1){
			
			
			$password			= md5($mobile);
			$nickname			= '省鑫用户';
			$platform_code 		= M('platform_config')->where(array('name'=>'CODE'))->getField('value');
			if($code == $platform_code){	//平台邀请码
				$pid			= 0;
			}else{
				$pid 			= M('user')->where(array('code'=>strtoupper($code)))->getField('id');
				if($pid < 1){
					return array('Code' =>'1','Msg'=>'邀请码不存在,请填写正确的邀请码');
				}
			}
			
			$userOauth = M('user_oauth')->where(array('id'=>$oauthId))->find();
			if(empty($userOauth)){
				return array('Code' =>'1','Msg'=>'第三方绑定信息不存在');
			}
			//我的邀请码
			$my_code 					= randomCode();
			$data = array(
					'type'				=> 1,
					'code' 				=> $my_code,
					'phone'				=> $mobile,
					'password'			=> $password,
					'pid'				=> $pid,
					'nickname'			=> $userOauth['nickname'],
					'sex'				=> $userOauth['sex'],
					'avatar'			=> $userOauth['headimgurl'],
					'create_time'		=> NOW_TIME, 
					'status'			=> 1,
					'check_status' 		=> 2
			);
			
			$uid 	= M('user')->add($data);
			//注册成功
			if($uid > 0){
				//记录日志
				//addUserLog('新会员注册', $uid);
				//调用登陆
				$user	= M('user')->where(array('id'=>$uid))->find();
				if(!empty($user) && $user['id'] > 0){
					//删除验证码
					R('Api/Sms/delcode', array('mobile'=>$mobile,'code'=>$Parame['checkcode'],'type'=>6));
					//绑定极光设备
					if(!empty($Parame['jpushid'])){
						if($user['jpushid'] != $Parame['jpushid']){
							M('user')->where(array('jpushid'=>$Parame['jpushid']))->save(array('jpushid'=>''));
							M('user')->where(array('phone'=>$mobile))->save(array('jpushid'=>$Parame['jpushid']));
							$msg 			= '检测到您的迷路客账号在别处登录，如果不是本人操作，请及时修改密码';
							//sendJPush(array($user['jpushid']),$msg,array('type'=>2));//type=2异常登录
						}
					}
					/* 环信 */
					if (empty($user['hx_username'])) {
						/* 环信注册 */
						vendor('Hx.Hx');
						$hx       				= new \vendor\Hx\Hxcall();
						$user['hx_username'] 	= md5(C('DATA_AUTH_KEY') . $user['phone']);         //加密账号信息，避免暴露
						$user['hx_password'] 	= md5(C('DATA_AUTH_KEY') . $user['password']);  	//加密密码信息
						$hx_res   				= $hx->hx_register($user['hx_username'], $user['hx_password']);
						M('user')->where(array('id' => $user['id']))->save(array('hx_username' => $user['hx_username'], 'hx_password' => $user['hx_password']));
					}
					/*登录成功后*/
					$data						= array();
					$data['uid']				= intval($user['id']);
					$data['hashid']				= $this->create_hashid($uid);
					$data['face']				= $user['avatar'];
					$data['nickname']			= !empty($user['nickname']) ? $user['nickname'] : '';
					$data['phone']				= !empty($user['phone']) ? $user['phone'] : '';
					$data['hx_username']		= !empty($user['hx_username']) ? $user['hx_username'] : '';
					$data['hx_password']		= !empty($user['hx_password']) ? $user['hx_password'] : '';
					return array('Code' =>'0','Msg'=>$this->Lang['100049'],'Data'=>$data);
				} else {
					return array('Code' =>'100006','Msg'=>$this->Lang['100006']);
				}
			}
		}else{
			$user	= M('user')->where(array('phone'=>$mobile))->find();
			if(!empty($user) && $user['id'] > 0){
				//绑定第三方
				M('user_oauth')->where(array('id'=>$oauthId))->save(array('uid'=>$user['id']));
				//删除验证码
				R('Api/Sms/delcode', array('mobile'=>$mobile,'code'=>$Parame['checkcode'],'type'=>6));
				//绑定极光设备
				if(!empty($Parame['jpushid'])){
					if($user['jpushid'] != $Parame['jpushid']){
						M('user')->where(array('jpushid'=>$Parame['jpushid']))->save(array('jpushid'=>''));
						M('user')->where(array('phone'=>$mobile))->save(array('jpushid'=>$Parame['jpushid']));
						$msg 			= '检测到您的迷路客账号在别处登录，如果不是本人操作，请及时修改密码';
						//sendJPush(array($user['jpushid']),$msg,array('type'=>2));//type=2异常登录
					}
				}
				/* 环信 */
				if (empty($user['hx_username'])) {
					/* 环信注册 */
					vendor('Hx.Hx');
					$hx       				= new \vendor\Hx\Hxcall();
					$user['hx_username'] 	= md5(C('DATA_AUTH_KEY') . $user['phone']);         //加密账号信息，避免暴露
					$user['hx_password'] 	= md5(C('DATA_AUTH_KEY') . $user['password']);  	//加密密码信息
					$hx_res   				= $hx->hx_register($user['hx_username'], $user['hx_password']);
					M('user')->where(array('id' => $user['id']))->save(array('hx_username' => $user['hx_username'], 'hx_password' => $user['hx_password']));
				}
				/*登录成功后*/
				$data						= array();
				$data['uid']				= intval($user['id']);
				$data['hashid']				= $this->create_hashid($uid);
				$data['face']				= $user['avatar'];
				$data['nickname']			= !empty($user['nickname']) ? $user['nickname'] : '';
				$data['phone']				= !empty($user['phone']) ? $user['phone'] : '';
				$data['hx_username']		= !empty($user['hx_username']) ? $user['hx_username'] : '';
				$data['hx_password']		= !empty($user['hx_password']) ? $user['hx_password'] : '';
				return array('Code' =>'0','Msg'=>$this->Lang['100049'],'Data'=>$data);
			} else {
				return array('Code' =>'100006','Msg'=>$this->Lang['100006']);
			}
		}
	}
	
	protected function oauthBindTwo($Parame){
		$username			= safe_replace($Parame['username']);
		$mobile				= safe_replace($Parame['username']);
		
		//验证密码
		if ($Parame['password'] !== $Parame['repeatpwd']){
			return array('Code' =>'100036','Msg'=>$this->Lang['100036']);
		}
		
		//开始注册
		$password			= $Parame['password'];
		$user 				= new UserApi;
		$uid 				= $user->register($username, $password);//返回ucentermember数据表用户主键id
		//注册成功
		if(0 < $uid){
			//自动分配到对应组里面
			$accessinfo					= M('auth_group_access')->where(array('uid'=>$uid,'group_id'=>$Parame['groupid']))->find();
			if (empty($accessinfo)){
				M('auth_group_access')->add(array('uid'=>$uid,'group_id'=>$Parame['groupid']));
			}
			//调用登陆
			$ucuserinfo					= $user->info($uid);
			if(!empty($ucuserinfo) && $ucuserinfo['id'] > 0){
				D("member")->login($ucuserinfo['id']);
				//绑定第三方登录
				M('user_oauth')->where(array('openid'=>$Parame['openid']))->save(array('uid'=>$uid));
				//修改用户扩展信息
				$userOauth = M('user_oauth')->where(array('openid'=>$Parame['openid']))->find();
				M('member')->where(array('uid'=>$uid))->save(array('nickname'=>$userOauth['nickname'], 'sex'=>$userOauth['sex'], 'face'=>$userOauth['headimgurl']));
				//绑定极光设备
				if(!empty($Parame['jpushid'])){
					$memberModel 		= M('member');
					$jpushid 			= $memberModel->where(array('uid'=>$uid))->getField('jpushid');
					if($jpushid != $Parame['jpushid']){
						$memberModel->where(array('jpushid'=>$Parame['jpushid']))->save(array('jpushid'=>''));
						$memberModel->where(array('uid'=>$uid))->save(array('jpushid'=>$Parame['jpushid']));
						$msg 			= '检测到您的迷路客账号在别处登录，如果不是本人操作，请及时修改密码';
						//sendJPush(array($jpushid),$msg,array('type'=>2));//type=2异常登录
					}
				}
				//用户信息
				$memberInfo 			= M('member')->where(array('uid'=>$uid))->field(array('nickname','face','last_login_time'))->find();
				/*登录成功后*/
				$data['uid']			= intval($ucuserinfo['id']);
				$data['hashid']			= $this->create_hashid($uid);
				$data['nickname']		= !empty($memberInfo['nickname']) ? $memberInfo['nickname'] : '';
				$data['face']			= $memberInfo['face'];
				return array('Code' =>'0','Msg'=>"绑定成功!",'Data'=>$data);
			} else {
				return array('Code' =>'100006','Msg'=>$this->Lang['100006']);
			}
		} else {
			//绑定失败，显示错误信息
			$code= ($uid * (-1)-1)+100100;
			return array('Code' =>$code,'Msg'=>$user->showRegError($uid));
		}
	}

	//第三方登录
	protected function thirdLogin($Parame){
		//检查第三方注册类型是否正确 2QQ注册,3微信注册,4新浪微博注册
		if (!in_array($Parame['type'], array(2,3,4))){
			return array('Code' =>'100047','Msg'=>$this->Lang['100047']);
		}
		$password				= md5($Parame['authId'].$Parame['authId']);
		//是否首次登录
		$model					= M('ucenter_auth_user');
		$authInfo				= $model->where(array('authmd5'=>$password))->field(true)->find();
		$updata['authid']		= $Parame['authId'];
		$updata['authtype']		= $Parame['type'];
		$updata['authmd5']		= $password;
		$updata['auth_name']	= $Parame['authName'];
		$updata['update_time']	= NOW_TIME;
		$updata['authface']		= $Parame['authHead'];
		if (empty($authInfo) || $authInfo['id'] <= 0){
			$updata['create_time']		= NOW_TIME;
			$auth_user_id				= $model->add($updata);
		}else{
			$auth_user_id				= $authInfo['id'];
			$model->where(array('id'=>$auth_user_id))->save($updata);
		}
		$memberinfo						= array();
		$loginType						= array(2=>'QQ',3=>'WX',4=>'WB');
		//开始第三方登录用户自动注册操作
		$uid							= M('ucenter_member')->where(array('auth_user_id'=>$auth_user_id))->getField('id');
		if (!empty($uid) && $uid > 0){
			$user 				= new UserApi;
			$ucuserinfo			= $user->info($uid);
			if(!empty($memberinfo) && $ucuserinfo['status'] != 1){
				return array('Code' =>'100033','Msg'=>$this->Lang['100033']);
			}
				
		}else{
			$username				= $loginType[$Parame['type']].randomString(14,7);
			$_POST					= array();
			$_POST['username']		= $username;

			//开始注册
			$user 					= new UserApi;
			$uid 					= $user->register($username, $password);//返回ucentermember数据表用户主键id
			$ucuserinfo				= $user->info($uid);
			//注册成功
			if(0 < $uid && !empty($ucuserinfo)){
				D("member")->login($ucuserinfo['id']);
				//自动分配到对应组里面
				$accessinfo			= M('auth_group_access')->where(array('uid'=>$uid,'group_id'=>$Parame['groupid']))->find();
				if (empty($accessinfo)){
					M('auth_group_access')->add(array('uid'=>$uid,'group_id'=>$Parame['groupid']));
				}
				M('ucenter_member')->where(array('id'=>$uid))->setField(array('auth_user_id'=>intval($auth_user_id)));
				//创建直播室
				$last_login_time		= M('member')->where(array('uid'=>$uid))->getField('last_login_time');
				R('Api/Stream/apiRun', array('parame'=>json_encode(array('uid'=>$uid,'hashid'=>md5($uid.C('DATA_AUTH_KEY').$last_login_time),'ac'=>'create','isapi'=>true))),'Helper');
				//调用登陆
				$Member 	= D("Member");
				$memberinfo	= $Member->login($uid);
				if($memberinfo){
					//设置用户昵称
					if (!empty($Parame['authName'])){
						M('member')->where(array('uid'=>$uid))->save(array('nickname'=>$Parame['authName']));
					}
					//设置性别
					if (!empty($Parame['gender'])){
						M('member')->where(array('uid'=>$uid))->save(array('gender'=>$Parame['gender']));
					}
				}
				/* 上传第三方头像 */
				$path 						= $Parame['authHead'];
				//判断文件是否存在
				$ci 						= curl_init();
				curl_setopt_array($ci,array(
				CURLOPT_URL 			=> $path,	//请求的url地址
				CURLOPT_RETURNTRANSFER 	=> true, 	//文件流的形式返回，而不是直接输出
				CURLOPT_CONNECTTIMEOUT 	=> 5, 		//最长等待时间
				));
				$file 						= curl_exec($ci);
				$httpcode 					= curl_getinfo($ci,CURLINFO_HTTP_CODE);
				//上传头像
				if($httpcode == 200){
					$fielname				= './Uploads/Picture/copy.png';
					if( file_put_contents($fielname, $file) ){
						//调用上传头像接口
						$url 				= WEB_DOMAIN.'/Api/Upload/uploadFace';
						$data = array(
							'pic'			=> curl_file_create ( realpath($fielname) ),//new \CURLFile($path),
							'uid' 			=> $uid,
							'hashid' 		=> md5($uid.C('DATA_AUTH_KEY').$last_login_time),
							'uploadname' 	=> 'pic',
							'time' 			=> NOW_TIME,
							'hash' 			=> '0a51a705ca8ec26d83af2f1b239aae67',
						);
						$rel 				= CurlHttp($url,$data,'POST');
						$rel 				= json_decode($rel,true);
						unlink($fielname); //删除文件
						if($rel['Code'] === 0){
							M('member')->where(array('uid'=>$uid))->save(array('face'=>$rel['Data']['id']));
						}
					}
				}
			}else {
				//注册失败，显示错误信息
				return array('Code' =>(($uid * (-1)-1)+100100),'Msg'=>$user->showRegError($uid));
			}
		}
		//绑定极光设备
		if(!empty($Parame['jpushid'])){
			$memberModel 		= M('member');
			$jpushid 			= $memberModel->where(array('uid'=>$uid))->getField('jpushid');
			if($jpushid != $Parame['jpushid']){
				$memberModel->where(array('jpushid'=>$Parame['jpushid']))->save(array('jpushid'=>''));
				$memberModel->where(array('uid'=>$uid))->save(array('jpushid'=>$Parame['jpushid']));
				$msg 			= '检测到您的迷路客账号在别处登录，如果不是本人操作，请及时修改密码';
				sendJPush(array($jpushid),$msg,array('type'=>2));//type=2异常登录
			}
		}


		//环信账号密码
		$info 					= M('direct_seeding_room')->where(array('uid'=>$ucuserinfo['id']))->field(array('hx_username','hx_password'))->find();
		//用户信息
		$memberInfo 			= M('member')->where(array('uid'=>$uid))->field(array('nickname','face'))->find();

		/*登录成功后*/
		$data['uid']			= intval($ucuserinfo['id']);
		$data['hashid']			= $this->create_hashid($uid);
		$data['username']		= !empty($ucuserinfo['username']) ? $ucuserinfo['username'] : '';
		$data['email']			= !empty($ucuserinfo['email']) ? $ucuserinfo['email'] : '';
		$data['mobile']			= !empty($ucuserinfo['mobile']) ? $ucuserinfo['mobile'] : '';
		$data['isfirst']		= 0;
		$data['hx_username']	= !empty($info['hx_username']) ? $info['hx_username'] : '';
		$data['hx_password']	= !empty($info['hx_password']) ? $info['hx_password'] : '';
		$data['nickname']		= !empty($memberInfo['nickname']) ? $memberInfo['nickname'] : '';
		$data['face']			= $memberInfo['face']>0 ? 'http://'.WEB_DOMAIN.get_cover($memberInfo['face'],'path') : '';

		return array('Code' =>'0','Msg'=>"登陆成功!",'Data'=>$data);
	}
	
	//手机注册验证手机
	private function mobileRegister($Parame){
		
		$mobile				= $Parame['username'];
		$checkcode			= $Parame['checkcode'];
		$code				= $Parame['code'];
		$jpushid 			= $Parame['jpushid'];
		
		//首先验证手机号是否合法
		if (Mobile_check($mobile,array(1,2,3,4)) == false){
			return array('Code' =>'100035','Msg'=>$this->Lang['100035']);
		}
		//验证验证码是否正确//测试账号，不需要验证码
		$ischeck			= R('Api/Sms/checkcode', array('mobile'=>$mobile,'code'=>$checkcode,'type'=>1));
		if ($ischeck <= 0){
			return array('Code' =>'100109','Msg'=>$this->Lang['100109']);
		}
		//验证手机号是否注册
		$count = M('user')->where(array('phone'=>$mobile,'type'=>1))->count('id');
		if($count>0){
			return array('Code' =>'100112','Msg'=>$this->Lang['100112']);
		}
		//未注册,开始注册
		$password			= md5($mobile);
		$nickname			= '省鑫用户';
		$platform_code 		= M('platform_config')->where(array('name'=>'CODE'))->getField('value');
		if($code == $platform_code){	//平台邀请码
			$pid			= 0;
		}else{
			$pid 			= M('user')->where(array('code'=>strtoupper($code)))->getField('id');
			if($pid < 1){
				return array('Code' =>'1','Msg'=>'邀请码不存在,请填写正确的邀请码');
			}
		}
		
		//我的邀请码
		$my_code 			= randomCode();
		$uid 				= M('user')->add(array(
									'type' => 1,
									'code' => $my_code,
									'phone' => $mobile, 
									'password' => $password, 
									'pid' => $pid, 
									'nickname' => $nickname, 
									'jpushid' => $jpushid, 
									'create_time' => NOW_TIME, 
									'status' => 1,
									'check_status' => 2
							));
		//注册成功
		if($uid > 0){
			//记录日志
			//addUserLog('新会员注册', $uid);
			//调用登陆
			$user	= M('user')->where(array('id'=>$uid))->find();
			if(!empty($user) && $user['id'] > 0){
				//删除验证码
				R('Api/Sms/delcode', array('mobile'=>$mobile,'code'=>$Parame['checkcode'],'type'=>1));
				//绑定极光设备
				if(!empty($Parame['jpushid'])){
					if($user['jpushid'] != $Parame['jpushid']){
						M('user')->where(array('jpushid'=>$Parame['jpushid']))->save(array('jpushid'=>''));
						M('user')->where(array('phone'=>$mobile))->save(array('jpushid'=>$Parame['jpushid']));
						$msg 			= '检测到您的迷路客账号在别处登录，如果不是本人操作，请及时修改密码';
						//sendJPush(array($user['jpushid']),$msg,array('type'=>2));//type=2异常登录
					}
				}
				/* 环信 */
				if (empty($user['hx_username'])) {
					/* 环信注册 */
					vendor('Hx.Hx');
					$hx       				= new \vendor\Hx\Hxcall();
					$user['hx_username'] 	= md5(C('DATA_AUTH_KEY') . $user['phone']);         //加密账号信息，避免暴露
					$user['hx_password'] 	= md5(C('DATA_AUTH_KEY') . $user['password']);  	//加密密码信息
					$hx_res   				= $hx->hx_register($user['hx_username'], $user['hx_password']);
					M('user')->where(array('id' => $user['id']))->save(array('hx_username' => $user['hx_username'], 'hx_password' => $user['hx_password']));
				}
				/*登录成功后*/
				$data						= array();
				$data['uid']				= intval($user['id']);
				$data['hashid']				= $this->create_hashid($uid);
				$data['face']				= $user['avatar'];
				$data['nickname']			= !empty($user['nickname']) ? $user['nickname'] : '';
				$data['phone']				= !empty($user['phone']) ? $user['phone'] : '';
				$data['hx_username']		= !empty($user['hx_username']) ? $user['hx_username'] : '';
				$data['hx_password']		= !empty($user['hx_password']) ? $user['hx_password'] : '';
				return array('Code' =>'0','Msg'=>$this->Lang['100049'],'Data'=>$data);
			} else {
				return array('Code' =>'100006','Msg'=>$this->Lang['100006']);
			}
		}
	}
	
	//手机短信登录
	private function mobileMsgLogin($Parame){
		$mobile				= $Parame['username'];
		$checkcode			= $Parame['checkcode'];
		$jpushid 			= $Parame['jpushid'];
		
		//首先验证手机号是否合法
		if (Mobile_check($mobile,array(1,2,3,4)) == false){
			return array('Code' =>'100035','Msg'=>$this->Lang['100035']);
		}
		//验证验证码是否正确//测试账号，不需要验证码
		$ischeck			= R('Api/Sms/checkcode', array('mobile'=>$mobile,'code'=>$checkcode,'type'=>3));
		if ($ischeck <= 0){
			return array('Code' =>'100109','Msg'=>$this->Lang['100109']);
		}
		//验证手机号是否注册
		$count = M('user')->where(array('phone'=>$mobile,'type'=>1))->count('id');
		if($count <= 0){
			return array('Code' =>'100103','Msg'=>$this->Lang['100103']);
		}
		
		$user = M('user')->where(array('phone'=>$mobile))->find();
		//删除验证码
		R('Api/Sms/delcode', array('mobile'=>$mobile,'code'=>$Parame['checkcode'],'type'=>3));
		//绑定极光设备
		if(!empty($Parame['jpushid'])){
			if($user['jpushid'] != $Parame['jpushid']){
				M('user')->where(array('jpushid'=>$Parame['jpushid']))->save(array('jpushid'=>''));
				M('user')->where(array('phone'=>$mobile))->save(array('jpushid'=>$Parame['jpushid']));
				$msg 			= '检测到您的迷路客账号在别处登录，如果不是本人操作，请及时修改密码';
				//sendJPush(array($user['jpushid']),$msg,array('type'=>2));//type=2异常登录
			}
		}
		
		/* 环信 */
		if (empty($user['hx_username'])) {
			/* 环信注册 */
			vendor('Hx.Hx');
			$hx       				= new \vendor\Hx\Hxcall();
			$user['hx_username'] 	= md5(C('DATA_AUTH_KEY') . $user['phone']);         //加密账号信息，避免暴露
			$user['hx_password'] 	= md5(C('DATA_AUTH_KEY') . $user['password']);  	//加密密码信息
			$hx_res   				= $hx->hx_register($user['hx_username'], $user['hx_password']);
			M('user')->where(array('id' => $user['id']))->save(array('hx_username' => $user['hx_username'], 'hx_password' => $user['hx_password']));
		}
		
		/*登录成功后*/
		$data						= array();
		$data['uid']				= intval($user['id']);
		$data['hashid']				= $this->create_hashid($user['id']);
		$data['face']				= $user['avatar'];
		$data['nickname']			= !empty($user['nickname']) ? $user['nickname'] : '';
		$data['phone']				= !empty($user['phone']) ? $user['phone'] : '';
		$data['phone']				= !empty($user['phone']) ? $user['phone'] : '';
		$data['hx_username']		= !empty($user['hx_username']) ? $user['hx_username'] : '';
		$data['hx_password']		= !empty($user['hx_password']) ? $user['hx_password'] : '';
		return array('Code' =>'0','Msg'=>$this->Lang['100049'],'Data'=>$data);
		
	}
	
	//用户是否绑定手机号
	private function checkMobile($Parame){
		//用户ID检验
		$ucheck = $this->check_user($Parame['uid'], $Parame['hashid']);
		if ($ucheck['Code'] > 0){
			return $ucheck;
		}
		$mobile			= M('ucenter_member')->where(array('username'=>$Parame['mobile']))->getField('username');
		$mobile			= !empty($mobile) ? $mobile : '';
		$data['mobile']	= $mobile;
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}
	//手机绑定
	private function bindMobile($Parame){
		//用户ID检验
		$uid 		= intval($Parame['uid']);
		$hashid 	= trim($Parame['hashid']);
		$user 		= M('user')->where(array('id'=>$uid))->find();
		if(empty($user['id'])){
			return array('Code' =>'100073','Msg'=>$this->Lang['100073']);
		}
		if (md5($uid.C('DATA_AUTH_KEY').$user['last_login_time']) !== $hashid){
			return array('Code' =>'100004','Msg'=>$this->Lang['100004']);
		}
		//验证用户是否已经绑定手机号
		$userId			= M('user')->where(array('phone'=>$Parame['mobile']))->getField('id');
		if (empty($userId)){
			//首先验证手机号是否合法
			if (Mobile_check($Parame['mobile'],array(1)) == false){
				return array('Code' =>'100035','Msg'=>$this->Lang['100035']);
			}
			//验证验证码是否正确
			$ischeck			= R('Api/Sms/checkcode', array('mobile'=>$Parame['mobile'],'code'=>$Parame['checkcode'],'type'=>5));
			if ($ischeck <= 0){
				return array('Code' =>'100109','Msg'=>$this->Lang['100109']);
			}
			//删除验证码
			R('Api/Sms/delcode', array('mobile'=>$Parame['mobile'],'code'=>$Parame['checkcode'],'type'=>5));
			M('user')->where(array('id'=>$Parame['uid']))->setField('phone',$Parame['mobile']);
			return array('Code' =>'0','Msg'=>$this->Lang['100061']);
		}else{
			return array('Code' =>'100060','Msg'=>$this->Lang['100060']);
		}

	}

	//查看用户信息
	private function userInfo($Parame){
		/* 用户ID检验 */
		$ucheck = $this->check_user($Parame['uid'], $Parame['hashid']);
		if ($ucheck['Code'] > 0) return $ucheck;

		/* 用户详情 */
		$uid 							= $Parame['vid'];
		$model 		 					= M('member')->alias('m')->join(array(
		C('DB_PREFIX').('direct_seeding_room').' r ON r.uid=m.uid',
		),'LEFT');
		$field 						= _string_fields(array(
			'm'=>array('uid','nickname','face','occupation','age','gender','signature','level','area','area_name','give_diamonds_num'), 	//主表信息
			'r'=>array('liveno','hx_username','no_follow_send'),
		));
		$where 							= array('m.uid'=>$uid);
		$info 							= $model->where($where)->field($field)->find();
		//用户不存在
		if(empty($info)) return array('Code' =>'100073','Msg'=>$this->Lang['100073']);

		/* 数据处理 */
		$info['face'] 				= $info['face']>0 ? 'http://'.WEB_DOMAIN.get_cover($info['face'],'path') : '';
		//收到的票总数
		$info['receive_ticket_num'] = M('contribution_all')->where(array('uid2'=>$info['uid']))->sum('ticket_num');
		if(empty($info['receive_ticket_num'])) $info['receive_ticket_num'] = '0';

		//粉丝、关注
		$followModel 				= M('user_follow');
		$info['follow'] 			= $followModel->where(array('fid'=>$info['uid']))->count(); 	//粉丝数
		$info['attention'] 			= $followModel->where(array('uid'=>$info['uid']))->count(); 	//关注数
		//是否已关注
		$is_follow 		 			= $followModel->where(array('fid'=>$info['uid'],'uid'=>$Parame['uid']))->count();
		$info['is_follow'] 			= $is_follow>0 ? 1 : 0;
		//是否已认证
		$is_auth 		 			= M('authentication_info')->where(array('uid'=>$info['uid'],'status'=>2))->count();
		$info['is_auth'] 			= $is_auth>0 ? 1 : 0;
		//是否已拉黑
		$is_black 		 			= M('user_blacklist')->where(array('bid'=>$info['uid'],'uid'=>$Parame['uid']))->count();
		$info['is_black'] 			= $is_black>0 ? 1 : 0;
		//贡献榜前三头像
		$contributionModel 			= M('contribution_all')->alias('m')->join(array(
		C('DB_PREFIX').('member').' u ON u.uid=m.uid1',
		),'LEFT');
		$contribution 				= $contributionModel->where(array('m.uid2'=>$uid))->limit(3)->order(array('m.ticket_num'=>'desc'))->field(array('u.face'))->select();
		$info['contribution'] 		= array();
		for ($i=0; $i<3; $i++){
			$info['contribution'][] = $contribution[$i]['face']>0 ? 'http://'.WEB_DOMAIN.get_cover($contribution[$i]['face'],'path') : '';
		}

		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$info);
	}

	//搜索用户
	private function userSearch($Parame){
		/* 用户ID检验 */
		$ucheck = $this->check_user($Parame['uid'], $Parame['hashid']);
		if ($ucheck['Code'] > 0) return $ucheck;

		/* 用户列表 */
		$model 		 				= M('member')->alias('m')->join(array(
		C('DB_PREFIX').('ucenter_member').' u ON u.id=m.uid',
		C('DB_PREFIX').('direct_seeding_room').' r ON r.uid=m.uid',
		),'LEFT');
		$field 						= _string_fields(array(
			'm'=>array('uid','nickname','face','age','gender','signature','level'), 	//主表信息
		));
		$where 						= array('u.status'=>1,'m.uid'=>array('neq',$Parame['uid']));
		$where['_complex'][] 		= array(
			'_logic' 				=> 'OR',
			'r.liveno' 				=> $Parame['keyword'],
			'm.nickname' 			=> array('like','%'.$Parame['keyword'].'%'),
		);
		$order 						= array('m.empirical_value'=>'desc');
		$limit 						= 20;
		// 		$Parame['page'] 			= $Parame['page']<=1 ? 1 : $Parame['page'];
		$list  						= $this->getLists($model,$where,$order,$field,$Parame['page'],$limit,false);
		if(!empty($list)){
			$followModel 			= M('user_follow');
			foreach($list as $k => $v){
				$list[$k]['face'] 			= $v['face']>0 ? 'http://'.WEB_DOMAIN.get_cover($v['face'],'path') : '';
				//是否已关注
				$is_follow 		 			= $followModel->where(array('fid'=>$v['uid'],'uid'=>$Parame['uid']))->count();
				$list[$k]['is_follow'] 		= $is_follow>0 ? 1 : 0;
			}
		}

		/* 返回参数 */
		$data 						= array(
			'list' 					=> $list,
			'total' 				=> $this->_total,
			'remainder' 			=> $this->_remainder,
			'page' 					=> $Parame['page'],
		);
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>$data);
	}

	//举报用户
	private function userReport($Parame){
		/* 用户ID检验 */
		$ucheck = $this->check_user($Parame['uid'], $Parame['hashid']);
		if ($ucheck['Code'] > 0) return $ucheck;

		/* 用户是否存在 */
		$count 						= M('ucenter_member')->where(array('id'=>$Parame['fid'],'status'=>1))->count();
		if($count <= 0) return array('Code' =>'100073','Msg'=>$this->Lang['100073']);

		/* 举报信息 */
		$model 						= M('user_report');
		//一天内对同一个用户只能举报一次
		$time 						= $model->where(array('uid'=>$Parame['uid'],'fid'=>$Parame['fid']))->order(array('create_time'=>'desc'))->getField('create_time');
		if(!empty($time)){
			if(date('Ymd',$time) == date('Ymd',NOW_TIME)) return array('Code' =>'100079','Msg'=>$this->Lang['100079']);
		}
		//添加举报信息
		$data 						= array(
			'uid' 			=> $Parame['uid'],
			'fid' 			=> $Parame['fid'],
			'create_time' 	=> NOW_TIME,
		);
		$rel 						= $model->add($data);

		/* 返回参数 */
		if($rel > 0){
			return array('Code' =>'0','Msg'=>$this->Lang['100013']);
		}else{
			return array('Code' =>'100000','Msg'=>$this->Lang['100000']);
		}

	}

	/**
	 * 验证hashid
	 */
	private function checkHashid($Parame){
		/* 用户ID检验 */
		$ucheck = $this->check_user($Parame['uid'], $Parame['hashid']);
		if ($ucheck['Code'] > 0) return $ucheck;

		return array('Code' =>'0','Msg'=>$this->Lang['100013']);
	}

	/**
	 * 验证hashid
	 */
	private function getSign($Parame){
		Vendor('Alipay.AopClient');
		$aop 					= new \AopClient();
		$aop->rsaPrivateKey 	= C('RSAPRIVATEKEY');
		
		$params 				= array();
		$params['apiname'] 		= 'com.alipay.account.auth';
		$params['method'] 		= 'alipay.open.auth.sdk.code.get';
		$params['app_id'] 		= C('APPID');
		$params['app_name'] 	= 'mc';
		$params['biz_type'] 	= 'openservice';
		$params['pid'] 			= '2088031857953115';
		$params['product_id'] 	= 'APP_FAST_LOGIN';
		$params['scope'] 		= 'kuaijie';
		$params['target_id'] 	= NOW_TIME;
		$params['auth_type'] 	= 'AUTHACCOUNT';
		$params['sign_type'] 	= 'RSA2';
		
		$paramsString = $aop->getSignContent($params);
		$sign = $aop->generateSign($params, $params['sign_type']);
		$str = $paramsString.'&sign='.$sign;
		
		return array('Code' =>'0','Msg'=>$this->Lang['100013'],'Data'=>array('sign'=>$str));
	}
}
?>