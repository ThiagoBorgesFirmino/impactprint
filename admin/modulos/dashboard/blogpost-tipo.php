<?php

$rep_id = 'blogpost-tipo';
$titulo = 'Conteúdo por TIPO';

$sql =
    "
SELECT blogpost.tipo nome, COUNT(blogpost.id) qtd FROM blogpost
WHERE 1=1
GROUP BY blogpost.tipo
-- AND blogpost.st_ativo = 'S'
";

$data = array();
$total = 0;
$query = query($sql);
while($row=fetch($query)){

    $data[] = array(
        'x' => $row->nome
        ,'y' => floatval($row->qtd)
        ,'legendText' => $row->nome
    );

    $total += floatval($row->qtd);

}

$tmp = array();
foreach($data as $i => $item){
    $data[$i]['label'] = round(($item['y'] / $total) * 100, 2);
}

$param['data'] = $data;

require 'admin/modulos/dashboard/canvasjs/pie.php';