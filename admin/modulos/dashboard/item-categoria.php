<?php

$rep_id = 'item-categoria';
$titulo = 'Produtos por categoria';

$sql =
"
SELECT categoria.nome, COUNT(itemcategoria.id) qtd
FROM categoria
JOIN itemcategoria ON (itemcategoria.categoria_id = categoria.id)
WHERE IFNULL(categoria.categoria_id,0) = 0
AND 1=1
GROUP BY categoria.nome
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