<?php
global $_GPC, $_W;
$GLOBALS['frames'] = $this->getMainMenu();
$info=pdo_get('byjs_sun_activity',array('uniacid' => $_W['uniacid']));
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$pindex = max(1, intval($_GPC['page']));
$psize = 10;
$condition = ' WHERE `uniacid` = :uniacid ';
$params = array(':uniacid' => $_W['uniacid']);

if (!empty($_GPC['keyword'])) {
    $condition .= ' AND `title` LIKE :title';
    $params[':title'] = '%' . trim($_GPC['keyword']) . '%';
}

$sql = 'SELECT COUNT(*) FROM ' . tablename('byjs_sun_activity') .$condition ;

$total = pdo_fetchcolumn($sql, $params);

if (!empty($total)) {
    $sql = 'SELECT * FROM  ' . tablename('byjs_sun_activity') .$condition.' ORDER BY  `sort`  DESC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $pager = pagination($total, $pindex, $psize);
}
if($operation == 'done'){
    $id = intval($_GPC['id']);
    $row = pdo_fetch("SELECT id FROM " . tablename('byjs_sun_activity') . " WHERE id = :id", array(':id' => $id));
    if (empty($row)) {
        message('抱歉，活动不存在或是已经被删除！');
    }
    $status= intval($_GPC['status']);
    if($status == 1)
    {
        $condition = ' WHERE `uniacid` = :uniacid AND `status` =:status ';
        $params = array(':uniacid' => $_W['uniacid'],':status'=>$status);
        $active = pdo_fetch("SELECT id FROM " . tablename('byjs_sun_activity') .$condition , $params);

        pdo_update('byjs_sun_activity', array('status'=>$status), array('id' => $id));

        message('活动开启成功！', referer(), 'success');

    }else{
        pdo_update('byjs_sun_activity', array('status'=>$status), array('id' => $id));

        message('活动关闭成功！', referer(), 'success');

    }
}
if($operation == 'delete'){
    $id = intval($_GPC['id']);

    $res = pdo_delete('byjs_sun_activity',array('id'=>$id));
    if($res){
        message('删除成功！',referer(), 'success');
    }else{
        message('删除失败！');
    }
}

include $this->template('web/activitylist');