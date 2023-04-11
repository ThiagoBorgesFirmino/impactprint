<?php

function site_url(){
    $url = 'http://'.$_SERVER['SERVER_NAME'];
    if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80'){
        $url = 'https://'.$_SERVER['SERVER_NAME'];
    }
    return $url;
}


function stringChange($q,$str){
    if( ($start = strrpos(strtoupper($str),strtoupper($q)))===false ) return $str;
    $len = strlen($q);
    $sub =  substr($str, $start, $len); 
    return str_replace($sub,"<strong>{$sub}</strong>",$str);
}

function get_produtos($opt = array(), &$pagInfo=array(), &$t='', $categoria_nome='', $paginacao = false){

    if($paginacao){
        if( !(isset($opt["imagem_especial"]) && $opt["imagem_especial"]) ) $_SESSION['OPTS'] = $opt;   
       if($categoria_nome == '' && array_key_exists('tag_nome',$opt)) $categoria_nome = $opt['tag_nome'];
    }else{
        if( !(isset($opt["imagem_especial"]) && $opt["imagem_especial"]) ) $_SESSION["list_opts"] = $opt;
    }

    $ret = array();

    $pagInfo['paginar'] = !@$pagInfo['paginar']?false:true;
    
    if(isset($opt['busca'])) $opt['busca'] = '%'.str_replace(' ','%',$opt['busca']).'%';
   
    //printr($opt);die();

    $sql =
        "
        SELECT
            DISTINCT
            item.*,
            splash.imagem splash_imagem
            ".( (isset($opt['maisvendidos']) && $opt['maisvendidos']) ? ", COUNT(pedidoitem.id) pedidos": "" )."
            ,( SELECT cat.nome FROM itemcategoria as ic,categoria as cat WHERE ic.categoria_id = cat.id AND ic.item_id = item.id AND NOT (cat.categoria_id > 0) AND cat.st_lista_menu = 'S' LIMIT 1 ) categoria_nome
        FROM
            item
            ".((!$opt['apenas_principal']) ?"LEFT JOIN item itempai ON (itempai.id = item.itemsku_id)" : "")."
            ".( (isset($opt['maisvendidos']) && $opt['maisvendidos']) ?" LEFT JOIN pedidoitem ON ( item.id = pedidoitem.item_id) ": "" )."
        INNER JOIN 
            itemcategoria 
        ON 
            ((itemcategoria.item_id = item.id || itemcategoria.item_id = item.itemsku_id)".(isset($opt['categoria_id']) && $opt['categoria_id'] > 0?"
        AND 
            itemcategoria.categoria_id = {$opt['categoria_id']}" : "" ).")
        LEFT JOIN
            splash 
        ON 
            (splash.id = item.splash_id AND splash.st_ativo = 'S')
        INNER JOIN 
            categoria 
        ON 
            (categoria.st_ativo = 'S' AND categoria.id = itemcategoria.categoria_id)
        WHERE
            item.st_ativo = 'S'
            AND item.imagem <> ''"
            .(isset($opt['apenas_principal']) ?"  AND (item.itemsku_id = 0 OR item.itemsku_id is NULL) ":" AND (itempai.id IS NULL OR itempai.st_ativo = 'S') ")
            .(isset($opt['cor_id']) ? " AND item.cor_id = {$opt['cor_id']} " : '')
            .(isset($opt['pai_id']) ? " AND item.itemsku_id = {$opt['pai_id']} " : "")
            .(isset($opt["list_colecao"]) ? " AND item.colecao_id > 0  " : "")
            .(isset($opt['colecao_id']) ? " AND item.colecao_id = {$opt['colecao_id']} " : "")
            .(isset($opt['relacionado_id']) ?
            "
            AND item.id IN (SELECT itemcategoria.item_id FROM itemcategoria WHERE itemcategoria.categoria_id IN ( SELECT itemcategoria.categoria_id FROM itemcategoria WHERE itemcategoria.item_id = {$opt['relacionado_id']}))
            AND item.id <> {$opt['relacionado_id']}" : "" ).(isset($opt['busca']) ? "
            AND (item.referencia like '{$opt['busca']}'
            OR  item.nome like '{$opt['busca']}'
            OR  item.descricao like '{$opt['busca']}'
            OR  exists (SELECT 1 FROM categoria, itemcategoria WHERE categoria.st_ativo = 'S' AND categoria.id = itemcategoria.categoria_id AND itemcategoria.item_id = item.id AND categoria.nome like '{$opt['busca']}'))" : "" )
            .(isset($opt['st_lancamento']) ? " AND item.st_lancamento = '".$opt['st_lancamento']."' " : "" )
            .(isset($opt['st_amamos']) ? " AND item.st_amamos = '".$opt['st_amamos']."' " : "" )
            .(isset($opt['st_destaque']) ? " AND item.st_destaque = '".$opt['st_destaque']."' " : "" )
            .(isset($opt['tipo_produto']) ? " AND item.tipo_produto = '".$opt['tipo_produto']."' " : "" )
            
            //FILTRO FAIXA PRECO
            .(isset($opt['filtro_preco']) 
                ?" AND (SELECT COUNT(faixaprecos.id) FROM faixaprecos WHERE faixaprecos.id = ".$opt['filtro_preco']." AND item.preco BETWEEN faixaprecos.de AND faixaprecos.ate) > 0 " 
                : "" )

            .( isset($opt["faixa_preco"]) 
                ? " AND item.preco >= ".( is_set($opt["faixa_preco"]["de"]) ? $opt["faixa_preco"]["de"] : 0 )." "
                .( is_set( $opt["faixa_preco"]["ate"] ) ? " AND item.preco <= ".$opt["faixa_preco"]["ate"]." " : " " )
                : "" )

            .( isset($opt["imagem_especial"]) && $opt["imagem_especial"] ? "
                AND item.imagem_especial <> ''
            " : "" )

        .( (isset($opt['maisvendidos']) && $opt['maisvendidos']) ?" GROUP BY item.id ": "" )

        ." ORDER BY
        ".(isset($opt['order_by']) ? $opt['order_by']  : "item.referencia, item.nome" );

    if(isset($opt['limit'])) $sql .= " LIMIT {$opt['limit']}";

    
    //************   PAGINADOR *****************/
    //printr($sql);
    
    if($paginacao){
        if(isset($_REQUEST['pagina'])){
            $pagina = $_REQUEST['pagina'];
        }else{
            $pagina = 1;
        }

        if(isset($opt['filtro_itens'])){
            $qtd_produtos_pagina = $opt['filtro_itens'];
        }else{
            $qtd_produtos_pagina = 12;
        }
            
        $inicio    = $ini = ($pagina-1)*$qtd_produtos_pagina;
        $qtd_itens = $fim = isset($_REQUEST['itenspagina'])?$_REQUEST['itenspagina']:$qtd_produtos_pagina;
        $pag_visible  = 7;
        $pag_meio     = (floor($pag_visible/2))+(($pag_visible%2)>0?1:0);
        $pag_dif      = $pag_visible-$pag_meio;
        $pagina_atual = floor($inicio/$qtd_itens);
        $rows    = rows(query($sql));
        $paginas = (floor($rows/$qtd_itens))+(($rows%$qtd_itens)>0?1:0);

        for($i=($pagina_atual>=$pag_meio?($pagina_atual-$pag_dif):0); $i<($pagina_atual>=$pag_meio?($pagina_atual+$pag_dif)+1:$pag_visible) && $i<$paginas; $i++){
            $indice = $qtd_itens*$i;
            $t->indice = $i+1;
            $t->pagina = $i+1;
            $t->selected = ($indice==$inicio?'selected':'');			
            $t->parseBlock("BLOCK_PAGINADOR_LINK",true);
        }
        
        $t->indice_prev = 1;
        if($inicio>0){//PREV
            $t->indice_prev = $pagina-1;
            $t->parseBlock('BLOCK_PAGINADOR_LINK_PREV');
        }
        if($paginas>1 && $inicio<$indice){//NEXT
            $t->parseBlock('BLOCK_PAGINADOR_LINK_NEXT');
        }		
        if($paginas > $pag_visible && ($pagina+$pag_dif)<$paginas){//ULTIMA PAGINA	
                $t->max_paginas = $paginas;
                $t->max         = $paginas;
                $t->parseBlock("BLOCK_PAGINADOR_MAX");
            }		
            
        $t->ultima= $paginas;
        
        if($paginas>1)$t->parseBlock("BLOCK_PAGINADOR");

        $request_filtro = '';
        if(array_key_exists('f',$_REQUEST) && sizeof($_REQUEST['f'])>0){
            $cont = 0;
            if(request('busca')){
                $request_filtro .= "?busca=".request('busca');
                $cont++;
            }
            foreach($_REQUEST['f'] as $key=>$value){
                ($cont==0?$request_filtro .= "?f[{$key}]={$value}":$request_filtro .= "&f[{$key}]={$value}");
                $cont++;
            }
        }		
        
        $order = ' ';
        $metodo = 'brindes';
        $t->url = INDEX.$metodo."/".$categoria_nome."?";
        if(isset($opt['busca'])){
            $t->url = index."buscar?busca=".$opt['buscaurl']."&";
        }
        $limit = " LIMIT {$ini},{$fim}";
        $sql .= $order.$limit;
    }
    //************   PAGINADOR *****************/
    
    $pagInfo["sql"] = $sql;

    $splashs = array();

    $keys = array();

    $query = query($sql);

    while($fetch=fetch($query)){

        $item = new item();
        $item->load_by_fetch($fetch);

        if($item->itemsku_id>0){
            $key = $item->itemsku_id;
        }
        else {
            $key = $item->id;
        }

        if(isset($keys[$key.$item->cor_id])){
            continue;
        }
        
        $ret[] = $item;

        $keys[$key.$item->cor_id] = $item;
    }

    return $ret;

}



function get_categorias($opt = array(), &$pagInfo=array()){

    $ret = array();

    // $ano_mes = limpa($ano_mes);

    $pagInfo['order_by'] = @$pagInfo['order_by']?$pagInfo['order_by']:'categoria.ordem, categoria.nome';
    $pagInfo['paginar'] = !@$pagInfo['paginar']?false:true;

    if(isset($opt['busca'])){
        $opt['busca'] = '%'.str_replace(' ','%',$opt['busca']).'%';
    }

    $sql =
    "
    SELECT
        SQL_CACHE
        DISTINCT
        categoria.*
    FROM
        categoria
    WHERE
        categoria.st_ativo = 'S'
    ".(isset($opt['categoria_id']) ? " AND categoria.categoria_id = {$opt['categoria_id']} " : "")."
    ".(isset($opt['st_lista_menu']) ? " AND categoria.st_lista_menu = '{$opt['st_lista_menu']}' " : "")."
    ORDER BY
        ".$pagInfo['order_by']."
    ";

    if(isset($opt['limit'])){
        $sql .= "LIMIT {$opt['limit']}";
    }

    $query = query($sql);
    while($fetch=fetch($query)){
        $categoria = new categoria();
        $categoria->load_by_fetch($fetch);
        $ret[] = $categoria;
    }

    return $ret;

}

function get_cmsitem($tipo=null,$opts=array()){

    $ret = array();

    $opts['order_by'] = isset($opts['order_by'])?$opts['order_by']:'cmsitem.data_publicacao DESC';

    $sql =
        "
    SELECT
        SQL_CACHE
        DISTINCT
        cmsitem.*
    FROM
        cmsitem
    WHERE
        cmsitem.st_ativo = 'S'
    AND now() >= cmsitem.data_publicacao
    ".(isset($tipo) ? " AND cmsitem.tipo = '{$tipo}'" : "")."
    ORDER BY
        ".$opts['order_by']."
    ";

    if(isset($opts['limit'])){
        $sql .= "LIMIT {$opts['limit']}";
    }

    $query = query($sql);
    while($fetch=fetch($query)){
        if(isset($tipo)){
            $obj = new $tipo();
            $obj->load_by_fetch($fetch);
        }
        else {
            $obj = new cmsitem();
            $obj->load_by_fetch($fetch);
        }
        $ret[] = $obj;
    }

    return $ret;

}

function get_cores($opt = array()){

    $ret = array();

    $sql =
    "
    SELECT
        SQL_CACHE
        DISTINCT
        cor.*
    FROM
        cor
    INNER JOIN item ON (
        item.cor_id = cor.id
    AND item.st_ativo = 'S'
    AND item.imagem <> ''
    )
    ".(isset($opt['categoria_id']) ?
        "
    INNER JOIN itemcategoria ON (
        (itemcategoria.item_id = item.id || itemcategoria.item_id = item.itemsku_id)
    AND itemcategoria.categoria_id = {$opt['categoria_id']}
    )
    INNER JOIN categoria ON (
        categoria.st_ativo = 'S'
    AND categoria.id = itemcategoria.categoria_id
    )
    " : "" )."
    WHERE
        cor.st_ativo = 'S'
    ORDER BY
    ".(isset($opt['order_by']) ? $opt['order_by']  : "cor.referencia, cor.nome" );

    if(isset($opt['limit'])){
        $sql .= "LIMIT {$opt['limit']}";
    }

    $query = query($sql);
    while($fetch=fetch($query)){

        $cor = new cor();
        $cor->load_by_fetch($fetch);

        $ret[] = $cor;
    }

    return $ret;

}

function tpl_part_produto(&$item, &$tpl){
    $tpl->item = $item;
    $tpl->path = PATH_SITE;
    $tpl->index = INDEX;

    if(@$item->splash_imagem){
        // $tpl->splash = $item->splash;
        $item->splash_imagem = $item->splash_imagem.'?d='.date('His');
        $tpl->parseBlock('BLOCK_SPLASH');
    }

    if(@$item->disponibilidade == 'S'){
        $tpl->parseBlock('BLOCK_BT_ORCAR_ON');
    }

    $str = $tpl->getContent();
    $tpl->reloadfile();
    return $str;
}