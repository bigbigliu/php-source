<?php
global $_W, $_GPC;
include 'common.php';
load() -> func('tpl');
$base=$this->get_base(); 
$title=$base['title'];
//查询缓存是否存在，如果有，就直接登录后台，如果没有就显示登录页
$webtoken = $_SESSION['webtoken']; //cache_load('webtoken');
if($webtoken==''){
	header('Location: '.$_W['siteroot'].'web/index.php?c=user&a=login&referer='.urlencode($_W['siteroot'].'app/'.$this->createMobileUrl('manage_login_go')));
}else{
	
	$nav=intval($_GPC['nav']);
	if($nav==0){
		$nav=1;
	}
	
	//通过缓存查找到管理员信息
	$manageid = $_SESSION['manageid']; //cache_load('manageid');
	
	$manage=pdo_fetch("SELECT * FROM ".tablename('bc_community_jurisdiction')." WHERE weid=:uniacid AND id=:id",array(':uniacid'=>$_W['uniacid'],'id'=>$manageid));
	$v=0;
	if($manage['lev']==2 || $manage['lev']==3){		
		$v=1;		
	}
	
	//获取角色列表
	$rolelsit=pdo_fetchall("SELECT * FROM ".tablename('bc_community_authority')." WHERE weid=:uniacid ORDER BY id DESC",array(':uniacid'=>$_W['uniacid']));
	//获取当前商家详情
	$id=intval($_GPC['id']);
	$seller=pdo_fetch("SELECT * FROM ".tablename('bc_community_mall_seller')." WHERE weid=:uniacid AND id=:id",array(':uniacid'=>$_W['uniacid'],':id'=>$id));

	if(empty($seller)){
		message('信息有误，请联系管理员', $this -> createMobileUrl('manag_mall_seller',array()), 'error');
		return false;
	}

	

}
	


	
	
	include $this->template('manage_sellerinfo_edit');





?>