<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/13
 * Time: 9:23
 */
global $_GPC, $_W;
$GLOBALS['frames'] = $this->getMainMenu();
$where=" WHERE  uniacid=".$_W['uniacid'] ." and couponid =".$_GPC['couponid'];
//if($_GPC['keywords']){
//    $op=$_GPC['keywords'];
//    $where.=" and money LIKE  '%$op%'";
//}
$page = max(1, intval($_GPC['page']));
$size = intval($_GPC['psize']) ? intval($_GPC['psize']) : 10;
$start= $page * $size;

$total=pdo_fetchcolumn("select count(*) from " . tablename("yzpx_sun_coupon_getlog") .$where);
$pager = pagination($total, $page, $size);

$sql = 'SELECT * FROM '.tablename('yzpx_sun_coupon_getlog')."{$where} ORDER BY id DESC LIMIT ".(($page-1) * $size).','.$size;
$info = pdo_fetchall($sql);
//var_dump($info);exit;
foreach ($info as $key =>$value){
    $info[$key]['createtime']=date('Y-m-d H:i:s',$value['createtime']);
    if($value['usetime']){
        $info[$key]['usetime']=date('Y-m-d H:i:s',$value['usetime']);
    }
    $user=pdo_get('yzpx_sun_user',array('id'=>$value['uid']),array('user_name'));
    $info[$key]['username']=$user['user_name'];
}



include $this->template('web/coupongetlog');