<?php
global $_W, $_GPC;
include 'common.php';
load() -> func('tpl');
$all_net=$this->get_allnet(); 

$base=$this->get_base(); 
$title=$base['title'];
$mid=$this->get_mid(); 


$gz=$this->guanzhu(); 
//判断是否需要进入强制关注页
if($gz==1){
	if ($_W['fans']['follow']==0){
		include $this -> template('follow');
		exit;
	};
}else{
	//取得用户授权
	mc_oauth_userinfo();
}

//获取当前用户的信息
$member=$this->getmember(); 

//查询该用户是否有认证商家和认证状态
$res=pdo_fetch("SELECT * FROM ".tablename('bc_community_mall_seller')." WHERE weid=:uniacid AND mid=:mid",array(':uniacid'=>$_W['uniacid'],':mid'=>$mid));
if(empty($res)){
	message('您还不是商家，请先认证吧！', $this -> createMobileUrl('mall_authentication'), 'error');
	return false;
}else if($res['rz']==0){
	message('您的认证尚未通过，请稍后重试！', $this -> createMobileUrl('mall'), 'error');
	return false;
}else if($res['rz']==1){
	message('您的认证资料不合格，请完善！', $this -> createMobileUrl('mall_authentication'), 'error');
	return false;
}else if($res['rz']==3){
	message('您的认证资料待审核！', $this -> createMobileUrl('mall_authentication'), 'error');
	return false;
}


//获取卖家的消息列表
	$count=0;
	$pindex = max(1, intval($_GPC['page']));
	$psize = 6;	
	$total = pdo_fetchcolumn("SELECT count(id) FROM " . tablename('bc_community_mall_messages') . " WHERE weid=:uniacid AND mid=:mid AND usertype=2 ORDER BY id DESC", array(':uniacid' => $_W['uniacid'],':mid'=>$mid));
	$count = ceil($total / $psize);




	include $this -> template('mall_manage');

	
	



?>