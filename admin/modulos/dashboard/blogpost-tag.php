<?php

$rep_id = 'blogpost-tag';
$titulo = 'Conteúdo por TAG';

$sql =
"
SELECT blogtag.nome, COUNT(blogpost.id) qtd FROM blogpost
  JOIN blogposttag ON ( blogposttag.blogpost_id = blogpost.id )
  JOIN blogtag ON ( blogtag.id = blogposttag.blogtag_id )
WHERE 1=1
GROUP BY blogtag.nome
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