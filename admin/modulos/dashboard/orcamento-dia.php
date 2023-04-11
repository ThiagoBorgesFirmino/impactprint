<?php

$rep_id = 'orcamento-dia';
$titulo = 'Volume de orçamentos no decorrer do tempo';

$sql =
"
SELECT DATE_FORMAT(data_cadastro, '%y-%m-%d') nome, count(pedido.id) qtd
FROM pedido
GROUP BY DATE_FORMAT(data_cadastro, '%y-%m-%d');
";

$data = array();
$total = 0;
$query = query($sql);
while($row=fetch($query)){

    list($ano, $mes, $dia) = explode('-', $row->nome);
    $row->nome = "{$dia}/{$mes}/{$ano}";
    $data[] = array(
        'y' => floatval($row->qtd)
        ,'legendText' => $row->nome
        ,'label' => $row->nome.' ('.$row->qtd.')'
    );

    $total += floatval($row->qtd);

}

$tmp = array();
foreach($data as $i => $item){
    // $data[$i]['label'] = round(($item['y'] / $total) * 100, 2);
}

$param['data'] = $data;
require 'admin/modulos/dashboard/canvasjs/line.php';