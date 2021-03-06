<?php
/**
 * 深蓝网络 Copyright (c) www.zhshenlan.com
 */

defined('IN_IA') or exit('Access Denied');

global $_GPC, $_W;
load()->func('tpl');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

$sql = 'SELECT * FROM ' . tablename('slwl_aicard_shop_category') . ' WHERE `weid` = :weid ORDER BY `parentid`, `displayorder` DESC';
$category = pdo_fetchall($sql, array(':weid' => $_W['uniacid']), 'id');

if (!empty($category)) {
    $parent = $children = array();
    foreach ($category as $key => $value) {
        if ($value['parentid'] == '0') {
            $parent[] = $value;
        } else {
            $children[] = $value;
        }
    }
}

// dump($parent);
// dump($children);

if ($operation == 'display') {


} else if ($operation == 'display_table') {
    $keyword = $_GPC['keyword'];

    $condition = " AND weid=:weid AND deleted='0' AND brandid='0' ";
    $params = array(':weid' => $_W['uniacid']);
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    if ($keyword != '') {
        $condition .= ' AND (title LIKE :title) ';
        $params[':title'] = '%'.$keyword.'%';
    }

    $sql = "SELECT * FROM " . tablename('slwl_aicard_shop_goods'). ' WHERE 1 '
        . $condition . " ORDER BY displayorder DESC, id DESC LIMIT "
        . ($pindex - 1) * $psize .',' .$psize;

    $list = pdo_fetchall($sql, $params);
    if ($list) {
        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('slwl_aicard_shop_goods') . ' WHERE 1 '. $condition, $params);
        // $pager = pagination($total, $pindex, $psize);
    }
    $data_return = array(
        'code' => '0',
        'msg' => '',
        'count' => $total,
        'data' => $list,
    );
    echo json_encode($data_return);
    exit;


} elseif ($operation == 'post') {
    $id = intval($_GPC['id']);

    if ($_W['ispost']) {

        $data = array(
            'weid' => intval($_W['uniacid']),
            'displayorder' => intval($_GPC['displayorder']),
            'title' => $_GPC['goodsname'],
            'intro' => $_GPC['intro'],
            'pcate' => intval($_GPC['category']['parentid']),
            'ccate' => intval($_GPC['category']['childid']),
            'thumb'=> $_GPC['pic'],
            'type' => intval($_GPC['type']),
            'isrecommand' => intval($_GPC['isrecommand']),
            'ishot' => intval($_GPC['ishot']),
            'isnew' => intval($_GPC['isnew']),
            'isdiscount' => intval($_GPC['isdiscount']),
            'istime' => intval($_GPC['istime']),
            'timestart' => strtotime($_GPC['timestart']),
            'timeend' => strtotime($_GPC['timeend']),
            'description' => $_GPC['description'],
            'content' => htmlspecialchars_decode($_GPC['content']),
            'goodssn' => $_GPC['goodssn'],
            'unit' => $_GPC['unit'],
            'createtime' => time(),
            'total' => intval($_GPC['total']),
            'totalcnf' => intval($_GPC['totalcnf']),
            'marketprice' => $_GPC['marketprice'],
            'weight' => $_GPC['weight'],
            'costprice' => $_GPC['costprice'],
            'originalprice' => $_GPC['originalprice'],
            'productprice' => $_GPC['productprice'],
            'productsn' => $_GPC['productsn'],
            'credit' => sprintf('%.2f', $_GPC['credit']),
            'maxbuy' => intval($_GPC['maxbuy']),
            'usermaxbuy' => intval($_GPC['usermaxbuy']),
            'hasoption' => intval($_GPC['hasoption']),
            'sales' => intval($_GPC['sales']),
            'status' => intval($_GPC['status']),
            'isfreeshopping' => intval($_GPC['isfreeshopping']),
        );

        $thumbs = array();
        if (!(empty($_GPC['thumbs']))) {
            foreach ($_GPC['thumbs'] as $k => $v) {
                $thumbs[] = $v;
            }
            $data['thumb_url'] = json_encode($thumbs);
        }

        // 处理，自定义参数
        if (!(empty($_GPC['param_tv']))) {
            $options = $_GPC['param_tv'];

            foreach ($options['title'] as $k => $v) {
                $tmp_param[$k]['title'] = $v;
            }

            foreach ($options['value'] as $k => $v) {
                $tmp_param[$k]['value'] = $v;
            }

            foreach ($tmp_param as $k=>$v){
                $param_items[] = $v;
            }

            $data['param'] = json_encode($param_items); // 压缩
        }

        if (!empty($id)) {
            pdo_update('slwl_aicard_shop_goods', $data, array('id' => $id));
        } else {
            $data['addtime'] = date('Y-m-d H:i:s', time());
            pdo_insert('slwl_aicard_shop_goods', $data);
            $id_good = pdo_insertid();
        }

        // 处理规格
        $data_spec = array();
        $data_spec['weid'] = intval($_W['uniacid']);
        $data_spec['goodsid'] = $id;
        $data_spec['content'] = '';

        if ($_GPC['spec']) {
            $spec = $_GPC['spec'];

            foreach ($spec['spec'] as $k => $v) {
                $tm = $v;

                $v_items = array();
                foreach ($tm['items'] as $key => $value) {
                    $v_items[] = $value;
                }
                $tm['items'] = $v_items;

                $spec_items_options[] = $tm;
            }

            $data_spec['content'] = json_encode($spec_items_options); // 压缩
        }

        $check_condition_spec = ' AND weid=:weid AND goodsid=:goodsid';
        $check_params_spec = array(':weid' => $_W['uniacid'], ':goodsid'=>$id);
        $check_one_spec = pdo_fetch("SELECT * FROM " . tablename('slwl_aicard_shop_spec') . ' WHERE 1 ' . $check_condition_spec, $check_params_spec);

        if ($check_one_spec) {
            unset($data_spec['goodsid']);
            pdo_update('slwl_aicard_shop_spec', $data_spec, array('goodsid' => $id));
            // pdo_debug();exit;
        } else {
            $data_spec['addtime'] = date('Y-m-d H:i:s', time());
            pdo_insert('slwl_aicard_shop_spec', $data_spec);
            $id_spec = pdo_insertid();
        }

        // 处理，规格项目表
        if (!(empty($_GPC['specop']))) {
            $specop = $_GPC['specop'];

            foreach ($specop['option_title'] as $k => $v) {
                $tmp_specop[$k]['option_title'] = $v;
            }

            foreach ($specop['option_thumb'] as $k => $v) {
                $tmp_specop[$k]['option_thumb'] = $v;
            }

            foreach ($specop['option_stock'] as $k => $v) {
                $tmp_specop[$k]['option_stock'] = $v;
            }

            foreach ($specop['option_price'] as $k => $v) {
                $tmp_specop[$k]['option_price'] = $v;
            }

            foreach ($specop['option_productprice'] as $k => $v) {
                $tmp_specop[$k]['option_productprice'] = $v;
            }

            foreach ($specop['option_costprice'] as $k => $v) {
                $tmp_specop[$k]['option_costprice'] = $v;
            }

            foreach ($specop['option_weight'] as $k => $v) {
                $tmp_specop[$k]['option_weight'] = $v;
            }

            $data_option = array();
            foreach ($tmp_specop as $k=>$v){
                // $specop_items[] = $v;

                $data_option[] = array(
                    'goodsid' => $id,
                    'title' => $v['option_title'],
                    'thumb' => $v['option_thumb'],
                    'productprice' => $v['option_productprice'],
                    // 'price' => $v['option_price'],
                    'costprice' => $v['option_costprice'],
                    'stock' => $v['option_stock'],
                    'weight' => $v['option_weight'],
                );
            }

            pdo_query("DELETE FROM " . tablename('slwl_aicard_shop_goods_option') . " WHERE goodsid=$id");

            foreach ($data_option as $k => $v) {
                pdo_insert('slwl_aicard_shop_goods_option', $v);
            }

        }

        iajax(0, '保存成功！');
        exit;
    }
    $one = pdo_fetch("SELECT * FROM " . tablename('slwl_aicard_shop_goods') . " where id=:id and weid=:weid", array(":id" => $id, ":weid" => $_W['uniacid']));
    $piclist = array();
    $pl = json_decode($one['thumb_url'], true);
    if (!(empty($pl))) {
        foreach ($pl as $k => $v) {
            $piclist[] = $v;
        }
    }

    // 处理，自定义参数
    if (!(empty($one['param']))) {
        $one_param = json_decode($one['param'], true);
    }

    // 处理，多规格
    $one_spec = pdo_fetch("SELECT * FROM " . tablename('slwl_aicard_shop_spec') . " where goodsid=:goodsid and weid=:weid", array(":goodsid" => $id, ":weid" => $_W['uniacid']));
    if (!(empty($one_spec['content']))) {
        $spec_items = json_decode($one_spec['content'], true);
    }

    // 处理，规格项目表
    $one_option = pdo_fetchall("SELECT * FROM " . tablename('slwl_aicard_shop_goods_option') . " where goodsid=:goodsid ORDER BY id ASC ", array(":goodsid" => $id));

    $html = '';
    if (!(empty($one_option)) && !(empty($spec_items))) {
        $html .= '<table class="layui-table">';
        $html .= '<thead>';
        $html .= '<tr>';
        $len = count($spec_items);
        $newlen = 1; //多少种组合
        $h = array(); //显示表格二维数组
        $rowspans = array(); //每个列的rowspan

        for ($i = 0; $i < $len; $i++) {
            //表头
            $html .= "<th style='width:80px;'>" . $spec_items[$i]['title'] . "</th>";
            //计算多种组合
            $itemlen = count($spec_items[$i]['items']);
            if ($itemlen <= 0) {
                $itemlen = 1;
            }
            $newlen *= $itemlen;
            //初始化 二维数组
            $h = array();
            for ($j = 0; $j < $newlen; $j++) {
                $h[$i][$j] = array();
            }
            //计算rowspan
            $l = count($spec_items[$i]['items']);
            $rowspans[$i] = 1;
            for ($j = $i + 1; $j < $len; $j++) {
                $rowspans[$i]*= count($spec_items[$j]['items']);
            }
        }
        $html .= '<th class="info"><div><div class="top-title">库存</div><div class="input-group form-group"><input type="text" class="form-control option_stock_all" value=""/><span class="input-group-btn"><a href="javascript:;" class="btn btn-double-down" title="批量设置" onclick="setCol(\'option_stock\');"><i class="icon iconfont icon-down"></i></a></span></div></div></th>';
        $html .= '<th class="success"><div><div class="top-title">销售价格</div><div class="input-group form-group"><input type="text" class="form-control option_price_all" value=""/><span class="input-group-btn"><a href="javascript:;" class="btn btn-double-down" title="批量设置" onclick="setCol(\'option_price\');"><i class="icon iconfont icon-down"></i></a></span></div></div></th>';
        $html .= '<th class="warning"><div><div class="top-title">市场价格</div><div class="input-group form-group"><input type="text" class="form-control option_productprice_all" value=""/><span class="input-group-btn"><a href="javascript:;" class="btn btn-double-down" title="批量设置" onclick="setCol(\'option_productprice\');"><i class="icon iconfont icon-down"></i></a></span></div></div></th>';
        $html .= '<th class="danger"><div><div class="top-title">成本价格</div><div class="input-group form-group"><input type="text" class="form-control option_costprice_all" value=""/><span class="input-group-btn"><a href="javascript:;" class="btn btn-double-down" title="批量设置" onclick="setCol(\'option_costprice\');"><i class="icon iconfont icon-down"></i></a></span></div></div></th>';
        $html .= '<th class="info"><div><div class="top-title">重量（克）</div><div class="input-group form-group"><input type="text" class="form-control option_weight_all" value=""/><span class="input-group-btn"><a href="javascript:;" class="btn btn-double-down" title="批量设置" onclick="setCol(\'option_weight\');"><i class="icon iconfont icon-down"></i></a></span></div></div></th>';
        $html .= '</tr></thead>';
        for ($m = 0; $m < $len; $m++) {
            $k = 0;
            $kid = 0;
            $n = 0;
            for ($j = 0; $j < $newlen; $j++) {
                $rowspan = $rowspans[$m];
                if ($j % $rowspan == 0) {
                    $h[$m][$j] = array("html" => "<td rowspan='" . $rowspan . "'>" . $spec_items[$m]['items'][$kid]['gditemname'] . "</td>", "id" => $spec_items[$m]['items'][$kid]['id']);
                } else {
                    $h[$m][$j] = array("html" => "", "id" => $spec_items[$m]['items'][$kid]['id']);
                }
                $n++;
                if ($n == $rowspan) {
                    $kid++;
                    if ($kid > count($spec_items[$m]['items']) - 1) {
                        $kid = 0;
                    }
                    $n = 0;
                }
            }
        }
        $hh = "";
        for ($i = 0; $i < $newlen; $i++) {
            $hh.="<tr>";
            $ids = array();
            for ($j = 0; $j < $len; $j++) {
                $hh.=$h[$j][$i]['html'];
                $ids[] = $h[$j][$i]['id'];
            }

            $hh .= '<td class="info">';
            $hh .= '<input name="specop[option_stock][' . $i . ']" type="text" class="form-control option_stock option_stock_' . $i . '" value="' . $one_option[$i]['stock'] . '"/></td>';
            $hh .= '<input name="specop[option_title][' . $i . ']" type="hidden" class="form-control option_title option_title_' . $i . '" value="' . $one_option[$i]['title'] . '"/>';
            $hh .= '<input name="specop[option_thumb][' . $i . ']" type="hidden" class="form-control option_thumb option_thumb_' . $i . '" value="' . $one_option[$i]['thumb'] . '"/>';
            $hh .= '</td>';
            $hh .= '<td class="success"><input name="specop[option_price][' . $i . ']" type="text" class="form-control option_price option_price_' . $i . '" value="' . $one_option[$i]['price'] . '"/></td>';
            $hh .= '<td class="warning"><input name="specop[option_productprice][' . $i . ']" type="text" class="form-control option_productprice option_productprice_' . $i . '" " value="' . $one_option[$i]['productprice'] . '"/></td>';
            $hh .= '<td class="danger"><input name="specop[option_costprice][' . $i . ']" type="text" class="form-control option_costprice option_costprice_' . $i . '" " value="' . $one_option[$i]['costprice'] . '"/></td>';
            $hh .= '<td class="info"><input name="specop[option_weight][' . $i . ']" type="text" class="form-control option_weight option_weight_' . $i . '" " value="' . $one_option[$i]['weight'] . '"/></td>';
            $hh .= '</tr>';
        }
        $html .= $hh;
        $html .= "</table>";

    }
    // dump($spec_items);

} elseif ($operation == 'goods_property') {
    global $_GPC, $_W;
    $id = intval($_GPC['id']);
    $type = $_GPC['type'];
    $data = intval($_GPC['data']);

    if (in_array($type, array('new', 'hot', 'recommand', 'discount'))) {
        $data = ($data==1?'0':'1');
        pdo_update("slwl_aicard_shop_goods", array("is" . $type => $data), array("id" => $id, "weid" => $_W['uniacid']));
        die(json_encode(array("result" => 0, "data" => $data)));
    }
    if (in_array($type, array('status'))) {
        $data = ($data==1?'0':'1');
        pdo_update("slwl_aicard_shop_goods", array($type => $data), array("id" => $id, "weid" => $_W['uniacid']));
        die(json_encode(array("result" => 0, "data" => $data)));
    }
    if (in_array($type, array('type'))) {
        $data = ($data==1?'2':'1');
        pdo_update("slwl_aicard_shop_goods", array($type => $data), array("id" => $id, "weid" => $_W['uniacid']));
        die(json_encode(array("result" => 0, "data" => $data)));
    }
    die(json_encode(array("result" => 2)));

} elseif ($operation == 'delete') {

    $post = file_get_contents('php://input');
    if (!$post) {iajax(1, '参数不存在'); }

    $params = @json_decode($post, true);
    if (!$params) { iajax(1, '参数解析出错'); }

    $ids = isset($params['ids']) ? $params['ids'] : '';
    if (!$ids) { iajax(1, 'ID为空'); }

    foreach ($ids as $k => $v) {
        $flags .= $v . ',';
    }
    $flags = substr($flags, 0, strlen($flags)-1);
    $where = ' id IN(' . $flags . ')';
    $where_option = ' goodid IN(' . $flags . ')';

    $rst_1 = @pdo_delete('slwl_aicard_shop_goods', $where);
    $rst_2 = @pdo_delete('slwl_aicard_shop_goods_option', $where_option);
    if ($rst_1 !== false && $rst_2 !== false) {
        iajax(0, '成功');
    } else {
        iajax(1, '不存在或已删除');
    }


} else {
    message('请求方式不存在');
}

include $this->template('web/shop/goods');

?>