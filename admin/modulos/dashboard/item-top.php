<?php

$rep_id = 'item-top';
$titulo = 'Top 20 produtos orçados';

$sql =
    "
SELECT item.referencia nome, COUNT(pedidoitem.id) qtd
FROM item
JOIN pedidoitem ON (pedidoitem.item_id = item.id)
GROUP BY item.referencia
ORDER BY 2 DESC
limit 20
";

$data = array();
$total = 0;
$query = query($sql);
while($row=fetch($query)){

    $data[] = array(
        // 'x' => $row->nome
        'y' => floatval($row->qtd)
        // ,'legendText' => $row->nome
        ,'indexLabel' => $row->qtd
        ,'label' => $row->nome
    );

    $total += floatval($row->qtd);

}

$tmp = array();
foreach($data as $i => $item){
    // $data[$i]['label'] = round(($item['y'] / $total) * 100, 2);
}

$param['data'] = $data;

require 'admin/modulos/dashboard/canvasjs/column.php';