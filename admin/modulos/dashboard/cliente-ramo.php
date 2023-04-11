<?php

$rep_id = 'cliente-ramo';
$titulo = 'Clientes por ramo';

$sql =
"
SELECT comoconheceu.nome,count(cadastro.id) qtd
FROM cadastro
LEFT OUTER JOIN comoconheceu ON ( cadastro.comoconheceu_id = comoconheceu.id )
WHERE 1=1
AND cadastro.tipocadastro_id = 2
GROUP BY comoconheceu.nome
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