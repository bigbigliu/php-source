<?php
global $_GPC, $_W;
$GLOBALS['frames'] = $this->getMainMenu();
$list = pdo_getall('yzqzk_sun_type',array('uniacid' => $_W['uniacid']),array(),'','num ASC');
if($_GPC['op']=='delete'){
    $res=pdo_delete('yzqzk_sun_type',array('id'=>$_GPC['id']));
    if($res){
        message('删除成功',$this->createWebUrl('type',array()),'success');
    }else{
        message('删除失败','','error');
    }
}
if($_GPC['op']=='change'){
	 $res=pdo_update('yzqzk_sun_type',array('state'=>$_GPC['state']),array('id'=>$_GPC['id']));
    if($res){
        message('操作成功',$this->createWebUrl('type',array()),'success');
    }else{
        message('操作失败','','error');
    }
}
include $this->template('web/type');