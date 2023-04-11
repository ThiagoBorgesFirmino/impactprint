<?php

class modulo_categoria extends modulo_admin {

    public $arquivo = 'categoria';

    public function pesquisa(){

        if(request('popup')){
            $t = new TemplateAdminPopup('admin/tpl.admin-cadastro-generico.html');
        }
        else {
            $t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
        }

        $t->h1 = h1($this->modulo->nome);

        if(request('action')=='categoriasDel'){
            try {
                $categoria = new categoria(intval(request('id')));
                if(rows(query("select 1 from categoria where categoria_id = {$categoria->id}"))>0){
                    throw new Exception('Existem categorias filhas desta, delete elas primeiro');
                }
                $categoria->exclui();
                query("UPDATE categoria SET ordem = (ordem-1) WHERE ordem > {$categoria->ordem} AND categoria_id = {$categoria->categoria_id} AND (especial is NULL OR especial='N') AND categoria.st_lista_menu = 'S' ");
                $_SESSION['sucesso'] = 'Categoria '.$categoria->nome.' excluida com sucesso';
            }
            catch(Exception $ex){
                $_SESSION['erro'] = $ex->getMessage();
            }
        }
        
		if(request('action')=='editar'||substr(request('action'),0,6)=='salvar' || request('action')=='sair'){
            $this->editar($t);
            return;
        }

        if(request('action')=='excluir'){
            $categoria = new categoria(intval(request('id')));
            if($categoria->id){
                $categoria->exclui();
            }
        }
        $edicao = '';

        // $edicao .= tag("table class='table'",
        //     tag("tr",tag("td", tag("span class='btn btn-primary'  onclick='javascript: openEdit(\"{$this->arquivo}\",0);'","Nova Categoria") ))
        // );

        $sql = "SELECT DISTINCT
				categoria.id
				,categoria.nome Nome
				-- ,(select pai.nome from categoria as pai where id = categoria.categoria_id) Categoria_Pai
				,categoria.st_ativo Status
				FROM categoria 
				WHERE categoria.st_fixo = 'N' AND categoria.st_lista_menu = 'S' 
                AND (categoria.categoria_id=0 OR categoria.categoria_id IS NULL) 
                ORDER BY categoria.nome,categoria.id";


        $grid = new grid($this->adm_instance);
        $filtro = new filtro($this->adm_instance);

        $grid->metodo = $this->arquivo;
        $grid->sql = $sql;

        // $edicao .= tag("div class='well'",tag("tr",tag("td",
        //     tag("span class='btn btn-success' onclick='javascript:abrePopup(\"".PATH_SITE."admin.php/categoria/categoriadestaquedefinir\",\"categoriaemdestaque\");' ",
        //         "Definir Categoria Em Destaque"
        //     )
        // )));

        $edicao .= $grid->render();

        $t->edicao = $edicao;
		
        $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }
	public function editar($t){
       
		if(request('pop')){
            $t = new TemplateAdminPop('admin/tpl.admin-cadastro-generico.html');
        }

        $t->h1 = h1($this->modulo->nome);

        $categoria = new categoria(intval(request('id')));
		
		if(substr(request('action'),0,6)=='salvar'){
            
            $next = substr(request('action'),7,strlen(request('action')));

			if(request("categoria")){
   
				$categoria->set_by_array(request("categoria"));

				$erro = array();
				if($categoria->validaDados($erro)){
					
					$_SESSION['sucesso'] = 'Dados salvos com sucesso';
                    
                    if($categoria->salva()){                    
                        seopro::validaSalva(array("modelo"=>"categoria","modelo_id"=>$categoria->id) , array("metodo"=>"brindes","tag_nome"=>$categoria->tag_nome), $erros);
                        // $categoria->atualiza();
                       /// $this->salvaBanners($categoria);
                       // $this->salvaTabelaPrecos($categoria);
                    }else{
                        printr(mysql_error());
                    }
					if(trim(@$next)!=''){
                        $this->afterSave($next,$this->arquivo,$categoria);
                        return;
                    }
				}
				else{
					$_SESSION['erro'] = join('<br />', $erro);
				}					
			}
        }
		
		 if(request('action')=='sair'){
            $this->afterSave('sair',$this->arquivo,$categoria);
        }
		
        // toolboxAdmin($t,array(),$this->adm_instance);
        $t->parseBlock('BLOCK_TOOLBAR');

        $edicao = '';

		$edicao .= inputHidden('id', $categoria->id);
        $edicao .= inputHidden('categoria[id]', $categoria->id);


        $edicao .= '<ul class="nav nav-tabs" id="nav-tabs">';
        $edicao .= '<li class="active"><a href="#dados" data-toggle="tab" >Dados Básicos</a></li>';
        $edicao .= ($categoria->id != 0)?'<li><a href="#subcategorias" data-toggle="tab">Sub Categorias</a></li>':'';
        $edicao .='<li><a href="#seopro" data-toggle="tab">SEO</a></li>';
        //$edicao .='<li><a href="#tabelafator" data-toggle="tab">Tabelas de Fatores</a></li>';
        $edicao .= "</ul>";

        $edicao .=  '<div class="tab-content">';
        $edicao .= tag("div class='tab-pane active' id='dados'",$this->dados($categoria));
        $edicao .= ($categoria->id != 0) ? tag("div class='tab-pane' id='subcategorias'",$this->subcategorias($categoria)) : '';
        $edicao .= tag("div class='tab-pane' id='seopro'",$this->seopro($categoria));
        //$edicao .= tag("div class='tab-pane' id='tabelafator'",$this->tabelafator($categoria));
        $edicao .=  '</div>';
		
        $edicao .= tag('br clear="all"');

        $t->edicao = $edicao;

        $opts = array(
            'include_js' => PATH_SITE."admin/modulos/{$this->arquivo}/categoriaadmin.js?v=1_2"
        );

        // $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t, $opts);
	}
    public function categoriasProcessa($id=0, &$ident=0, &$zebra, $tag_estendida = '', &$categorias){

        /*
		$results = results(
            $sql="SELECT categoria.* FROM categoria
            WHERE st_fixo = 'N'
            AND (especial IS NULL OR especial='N')
            AND categoria_id = {$id}
            AND categoria.st_lista_menu = 'S'  ORDER BY ordem, nome");
        */

        $results = array();

        foreach($categorias as $categoria){
            if(intval($categoria->categoria_id) == $id){
                $results[] = $categoria;
            }
        }

        // printr($sql);

        $edicao = '';
        $pai = new categoria();
        foreach($categorias as $categoria){
            if(intval($categoria->id) == $id){
                $pai = $categoria;
                break;
            }
        }

        for($i=0,$n=sizeof($results);$i<$n;$i++){

            $fetch = $results[$i];

            $edicao .= tag('tr',
                // tag('td '.($zebra?'style="background-color:#eeeeee"':''), $fetch->id)
                tag('td',$fetch->id)
                .tag('td',
                    inputSimples("categoria[{$fetch->id}][nome]",trim($fetch->nome),'',100, 30, 'style="float:left;margin-left:'.($ident*50).'px;width:200px;"')
                    .inputHidden("categoria[{$fetch->id}][nivel]",$ident)
                    .inputHidden("tag_estendida_{$fetch->id}",trim("{$tag_estendida} {$pai->nome} {$fetch->nome}"))
                    // .(!$ident?$this->imagemTema($fetch->id).$this->imagemIcone($fetch->id):'')
                    .($this->imagemTema($fetch))
                )
                .tag('td', select("categoria[{$fetch->id}][st_ativo]",$fetch->st_ativo,'',array('S'=>'Sim','N'=>'Nao')))
                .tag('td', inputSimples("categoria[{$fetch->id}][ordem]",$i+1,'',10,10))
                .tag('td', $i>0?tag('a class="up" href="javascript:categoriasUp('.$fetch->id.')" ','&nbsp;'):'')
                .tag('td', ($i<($n-1))?tag('a class="down" href="javascript:categoriasDown('.$fetch->id.')" ','&nbsp;'):'')
                .tag('td', tag('a class="del" href="javascript:categoriasDel('.$fetch->id.', \''.$fetch->nome.'\')" ','&nbsp;'))
            );

            $ident ++ ;
            $edicao .= $this->categoriasProcessa($fetch->id, $ident, $zebra, $tag_estendida, $categorias);
            $ident -- ;
        }

        return $edicao;

    }
    public function imagemTema($categoria){
        if($categoria->categoria_id && $this->config->HABILITA_IMAGEM_TEMA_SUB != "S"){
            return "";
        }
        $return = '';

        if($this->config->HABILITA_IMAGEM_TEMA=='S'){
            $return .= '<div style="float:right;padding:5px;margin:3px;background-color:#dddddd"><b>Imagem tema</b><br />';

            if($categoria->id
                && $categoria->imagem_tema != ''
                && file_exists('img/categorias/tema/'.$categoria->imagem_tema)){
                $return .= tag('a href="'.PATH_SITE.'img/categorias/tema/'.$categoria->imagem_tema.'" target="_blank"','ver') . ' | ';
            }

            $file = inputFile('imagem_tema_'.$categoria->id, '', 'Imagem tema '.$categoria->nome.':');

            $return .= tag("a href='#' data-file='".$file."' class='js-alterar-imagem' data-imagem='imagem_tema_".$categoria->id."'",'alterar/cadastrar');
            $return .= tag('br');
            $return .= tag('span', 'Tamanho sugerido: 1280x430px');
            $return .= tag("div style='display:none' id='imagem_tema_".$categoria->id."'",'');

            $return .= '</div>';

        }

        return $return;
    }
    public function imagemIcone($categoria_id=0){

        $return = '';

        if($this->config->HABILITA_IMAGEM_ICONE=='S'){

            $return .= '<div style="float:right;padding:5px;margin:3px;background-color:#dddddd"><b>Imagem icone</b><br />';

            $categoria = new categoria(intval($categoria_id));

            if($categoria->id&&$categoria->imagem_icone!=''&&file_exists('img/categorias/icone/'.$categoria->imagem_icone)){
                $return .= tag('a href="'.PATH_SITE.'img/categorias/icone/'.$categoria->imagem_icone.'" target="_blank"','ver') . ' | ';
            }

            $return .= tag('a href="javascript:void($(\'#imagem_icone_'.$categoria->id.'\').toggle())" ','alterar/cadastrar');
            $return .= tag('div style="display:none" id="imagem_icone_'.$categoria->id.'"'
                ,inputFile('imagem_icone_'.$categoria->id, '', 'Imagem icone '.$categoria->nome.':')
            );

            $return .= '</div>';

        }

        return $return;
    }
    static function breadcrumb(categoria &$categoria, $negrito=false){

        $ret = '';

        if(!$negrito){
            foreach($categoria->trilha() as $tmp){
                $ret .= '<li>'. ($ret!=''?'/':'' ).'<a href="'.$tmp->getLink().'">'.mb_convert_case((strtolower($tmp->nome)), MB_CASE_TITLE, "UTF-8").'</a></li>';   
            }
        }else{
            foreach($categoria->trilha(true, true) as $tmp){
                $ret .= '<li><a style="font-weight:bold" href="'.$tmp->getLink().'">'.mb_convert_case((strtolower($tmp->nome)), MB_CASE_TITLE, "UTF-8").'</a></li>';
                break;
            }
        }

        if($ret!=''){
            $ret = '<ol style="margin-left: -42px" class="">'.$ret.'</ol>';
        }

        return $ret;
    }
    private function dados(&$categoria){
        $edicao = tag('div class="well"',
            tag("table class='table'",
                tag("tr",
                    tag("td style='width:10%;'",select('categoria[st_ativo]', $categoria->st_ativo, 'Ativo?:', array('S'=>'Sim','N'=>'Nao')))
                    .tag("td",inputSimples('categoria[nome]', $categoria->nome, 'Nome:', null, 25,'',25))
                )
            )
          
            .modulo_categoria::cadastroBanners($categoria,array("icone"=>false,"banner_mobile"=>false,"qtd_banner"=>categoria::QTD_BANNER ))
        );

        return $edicao;
    }
    public function subcategorias(&$categoria){
        $edicao = "";
         /** Sub categorias */
         if($categoria->id){
            $q = query("SELECT id,st_ativo,nome FROM categoria WHERE categoria_id = {$categoria->id} ORDER BY nome");
            $edicao .= "<div class='well'>";
            $edicao .= tag("table class='table'",tag("tr",
                tag("td",tag("h2","Sub-Categorias"))
                .tag("td align='right'",
                    tag("span class='btn btn-primary' onclick='javascript: editSubCategoria(this);' data-pai_id='{$categoria->id}' data-id='0'",
                        "<i class='glyphicon glyphicon-plus'></i> Add Nova Sub-Categoria"
                    ) 
                )
            ));
            
            $edicao .= "<div class='new_grid_adm'><ul id='sub_categorias_adm'>";
            while($fetch=fetch($q)){
                $edicao .= tag("li id='sub_{$fetch->id}' style='position:relative;'",
                    tag("span style='display:block;padding:2px;' onclick='javascript: editSubCategoria(this);' data-pai_id={$categoria->id} data-id={$fetch->id}",
                        getStatus($fetch->st_ativo)
                        ."&nbsp;&nbsp;".$fetch->nome
                    )
                    .tag("a title='EXCLUIR' class='btn btn-danger' style='position:absolute;top:4px;right:4px;z-index:2;' onclick='javascript:excluirSubcategoria({$fetch->id})'", "X") 
                );
            }
            $edicao .= "</ul></div>";

            $edicao .= "</div>";

            $edicao .= tag("script",
                "
                function editSubCategoria(elem){
                    pai_id = $(elem).data('pai_id');
                    id     = $(elem).data('id');

                    $.ajax({
                        url : '".AJAX."editSubCategoria/'
                        ,data : {'pai_id':pai_id, 'id':id}
                        ,success : function(out){
                            $.fancybox(
								out,
							{
								padding     : 0,				 
								openEffect  : 'elastic',
								openSpeed   : 350,
								closeEffect : 'elastic',
								closeSpeed  : 350
							});
                        }
                    });
                }

                function excluirSubcategoria(id){
                    $.ajax({
                        url : '".AJAX."excluiSubCategoria/'
                        ,data : {'id':id}
                        ,success : function(out){
                            if(out){
                                $('#sub_'+out).remove();
                            }else{
                                alert('# Ocorreu um erro ao tentar excluir a categoria, tente mais tarde.');
                            }
                        }
                    });
                }
                "
            );
        }
        /** END Subs */

        return $edicao;
    }
    private function seopro(&$categoria){
         /** SEO */
         $opts = array();        
         $opts["modelo"]    = "categoria";
         $opts["modelo_id"] = $categoria->id;
         $seopro = new seopro($opts);
        return $seopro->getEdit();
         /** END SEO */
    }
    static function cadastroBanners($categoria,$opts=array()){
        foreach( array("banner_desk"=>true,"banner_mobile"=>true,"icone"=>true,"qtd_banner"=>1) as $key=>$value) if(!isset($opts[$key]))$opts[$key]=$value;

        $return = "";

        if(config::get("HABILITA_IMAGEM_TEMA")=="S"){
           
            $tipo = categoria::TIPO_BANNER;
            $query = "SELECT * FROM cmsitem WHERE tipo = '{$tipo}' AND custom1 = {$categoria->id} ORDER BY id";
            $result = results($query);

            for($i=0;$i<$opts["qtd_banner"];$i++){
                
                $return .= "<div style='border:1px solid #AAA; border-bottom-width:3px; border-right-width:3px; padding:6px;margin-bottom:10px;'>";
                
                $cmsitem = isset($result[$i]) ? $result[$i] : new cmsitem();                
                $return .= inputHidden("item{$i}[id]",$cmsitem->id);

                if($opts["banner_desk"]){
                    $return .= tag("table class='table' id='banner_{$i}'",
                        tag("tr",
                            tag("td",inputFile("src_{$i}","","Banners Desktop ( ".($i+1)." ) (".categoria::WIDTH_BANNER_DESK." x ".categoria::HEIGHT_BANNER_DESK." px):")
                            )
                            .tag("td",checkbox( "excluir_banner_{$cmsitem->id}", 1, "Excluir") )
                        )
                        .tag("tr",
                            tag("td colspan='2'",
                                tag("span id='_categoria_tema_{$cmsitem->id}'",($cmsitem->img1!=""?
                                "<img id='imagem_tema{$cmsitem->id}' src='".PATH_SITE.categoria::PATH_IMAGEM_TEMA."{$cmsitem->img1}' style='width:100%;max-width:300px;' />"
                                :""))
                            )    
                        )
                    );
                }
                if($opts["banner_mobile"]){
                    $return .= tag("table class='table' id='banner_mob_{$i}'",
                        tag("tr",
                            tag("td",inputFile("src_{$i}_mobile","","Banner Mobile ( ".($i+1)." ) (".categoria::WIDTH_BANNER_MOBILE." x ".categoria::HEIGHT_BANNER_MOBILE." px):"))
                            //.tag("td", checkbox( "excluir_banner_tema_mobile_{$cmsitem->id}", 1, "Excluir") )
                        )
                        .tag("tr",
                            tag("td",
                            tag("span id='_categoria_tema_mobile_{$cmsitem->id}'",($cmsitem->img2!=""?"<img id='imagem_tema_mobile{$cmsitem->id}' src='".PATH_SITE.categoria::PATH_IMAGEM_TEMA."{$cmsitem->img2}' style='width:100%;max-width:300px;' />":""))
                            )
                        )
                    );
                }

                $return .= "</div>";
            }
        }

        $return .= "<br>";
        $return .= (config::get("HABILITA_IMAGEM_ICONE")=="S"?
            ($opts["icone"]?
                tag("table class='table'",
                    tag("tr",
                        tag("td",inputFile("imagem_icone_{$categoria->id}","","Imagem icone (".categoria::WIDTH_ICONE." x ".categoria::HEIGHT_ICONE." px):"))
                        .tag("td",
                            checkbox( "excluir_imagem_icone_{$categoria->id}", 1, "Excluir")
                        )
                    )
                )
                .tag("span id='_categoria_icone_{$categoria->id}'",($categoria->imagem_icone!=""?"<br /><img id='imagem_icone{$categoria->id}' src='".PATH_SITE."img/categorias/icone/{$categoria->imagem_icone}?d=".date("His")."' width='103px' />":""))
            :"")
        :"");
        
        return $return;
    }
  

    static function menu(&$t,$selected='', $cat_id=0){
        if($t->exists("menu"))$t->menu = "<div class='menu'><ul class=''>".self::montaMenuProcessa($cat_id)."</ul></div>";
		if($t->exists("menumobile"))$t->menumobile = "<div class='sub-categoria'><ul>".self::montaMenuProcessaMobile($cat_id)."</ul></div>";
        if($t->exists("menu_categorias")) $t->menu_categorias = self::menuCategorias();
    }

    static function montaMenuProcessa($categoria_id=0){		
		$ret = '';

		$categorias = results($sql="SELECT DISTINCT categoria.* FROM categoria 
					INNER JOIN itemcategoria ON (categoria.id = itemcategoria.categoria_id)
					INNER JOIN item ON (item.id = itemcategoria.item_id AND item.st_ativo='S')
					WHERE categoria.st_ativo = 'S' 
					AND IFNULL(categoria.st_lista_menu,'S') = 'S' 
					ORDER BY categoria.ordem,categoria.nome");
        
        $i = 0;
		$arraytop = array();
		$principais = array();
		foreach($categorias as $tmp){
			if($tmp->categoria_id == $categoria_id){
				$principais[] = $tmp;				
				$arraytop[sizeof($arraytop)] = $tmp;
			}
        }
        
		if(sizeof($arraytop) > 0){
			$_SESSION['TOP_CATEGORIAS'] = $arraytop;
		}

		$categoria = new categoria();

		foreach($principais as $principal){

			$filhos = array();
			foreach($categorias as $tmp){
				if($tmp->categoria_id == $principal->id){
					$filhos[] = $tmp;
				}
			}

			$categoria->load_by_fetch($principal);
			$ret .= "<li class='list_mobil ".(sizeof($filhos)>0?"li_menu_prod":"")." ".($categoria->id==$categoria_id?'produtotodos':'')."'><a href='{$categoria->getLink()}'>{$categoria->nome}</a>";

			$ret .= "</li>";
		}	
		return $ret;
	}

	static function montaMenuProcessaMobile($categoria_id=0){
		
		$ret = '';

		$categorias = results($sql="SELECT DISTINCT categoria.* FROM categoria 
					INNER JOIN itemcategoria ON (categoria.id = itemcategoria.categoria_id)
					INNER JOIN item ON (item.id = itemcategoria.item_id AND item.st_ativo='S')
					WHERE categoria.st_ativo = 'S' 
					AND IFNULL(categoria.st_lista_menu,'S') = 'S' 
					ORDER BY categoria.ordem,categoria.nome");
		
		$i = 0;
		$arraytop = array();
		$principais = array();
		foreach($categorias as $tmp){
			if($tmp->categoria_id == $categoria_id){
				$principais[] = $tmp;

				$arraytop[sizeof($arraytop)] = $tmp;
			}
		}
		if(sizeof($arraytop) > 0){
			$_SESSION['TOP_CATEGORIAS'] = $arraytop;
		}

		$categoria = new categoria();

		foreach($principais as $principal){

			$filhos = array();
			foreach($categorias as $tmp){
				if($tmp->categoria_id == $principal->id){
					$filhos[] = $tmp;
				}
			}
			$categoria->load_by_fetch($principal);
			$ret .= "<li class='list_mobil ".(sizeof($filhos)>0?"li_menu_prod":"")." ".(sizeof($filhos)==0?"li_menu_sem":"")." ".($categoria->id==$categoria_id?'produtotodos':'')."' ><a href='{$categoria->getLink()}' class='link-categoria' data-id='{$categoria->id}'>{$categoria->nome} <div class='seta-sub'></div><div class='seta'></div></a>";

			if(sizeof($filhos)>0){
				$ret .= "<ul class='sub-cat sub_{$categoria->id}' id='{$categoria->id}' data-id='{$categoria->id}'>";
				foreach($filhos as $filho){
					$categoria->load_by_fetch($filho);
					$ret .= "<li><a href='{$categoria->getLink()}'>{$categoria->nome}</a></li>";
				}
				$ret .= "</ul>";
			}
			$ret .= "</li>";
		}	
		return $ret;
    }
    
    static function menuCategorias(){
        $key = date("YmdHs")."menucategorias";
        $cachepath = "menucategorias";
        $temp = "";
        $path = PATH_SITE;
            
        if( !($temp=mycache::get($key,$cachepath)) ){
            mycache::clear("",$cachepath);  

            $p = new Template(dirname(__FILE__).DIRECTORY_SEPARATOR."tpl.menu-categorias.html");
            setVars($p);

            $categorias = results($sql="SELECT DISTINCT categoria.* FROM categoria 
					INNER JOIN itemcategoria ON (categoria.id = itemcategoria.categoria_id)
					INNER JOIN item ON (item.id = itemcategoria.item_id AND item.st_ativo='S')
					WHERE categoria.st_ativo = 'S' 
					AND IFNULL(categoria.st_lista_menu,'S') = 'S' 
                    GROUP BY categoria.id
                    ORDER BY categoria.ordem,categoria.nome
                ");
            
            $i = 0;
            $arraytop = array();
            $principais = array();
            foreach($categorias as $tmp){
                if(!$tmp->categoria_id){
                    $arraytop[sizeof($arraytop)] = $tmp;
                    $query = query("SELECT item.* FROM item 
                        INNER JOIN itemcategoria ON (
                            itemcategoria.item_id = item.id
                            AND itemcategoria.categoria_id = {$tmp->id}
                        )
                        WHERE item.st_ativo='S' AND item.imagem<>'' LIMIT 1
                    ");
                    if($fetch = fetch($query)){
                        $item = new item();
                        $item->load_by_fetch($fetch);
                        $tmp->item = $item;
                    }
                }
                $principais[$tmp->categoria_id][] = $tmp;				
            }
            
            if(sizeof($arraytop) > 0) $_SESSION['TOP_CATEGORIAS'] = $arraytop;
            
            $loopcat = function($cat_id=0,$nivel=0) use (&$loopcat,&$principais,&$p){
                if(!isset($principais[$cat_id])) return false;   
                
                $has_vermais = false;
                $indice = 1;

                // VERIFICA SE O MENU ESTA SENDO MONTADO NO DESK OU MOBILE 
                $detected = new Mobile_Detect;
                
                if($detected->isMobile() == 'true'){
                    foreach( $principais[$cat_id] as $key=>$principal ){
                        $categoria = new categoria();                    
                        
                        if( $nivel==0 && $indice>categoria::QTD_CATEGORIAS_PRINCIPAIS_MOBILE ){   
                            $categoria->load_by_fetch($principal);
                            if($p->exists("categoria1")) $p->categoria1 = $categoria;
                            if($p->blockExists("BLOCK_CATEGORIAS_1")) $p->parseBlock("BLOCK_CATEGORIAS_1",true);
                            $has_vermais = true;
    
                        }else{
    
                            $varcat = "categoria{$nivel}";
                            $categoria->load_by_fetch($principal);
                            if($p->exists($varcat)) $p->$varcat = $categoria;
                            
                            $ret = $loopcat($categoria->id,($nivel+1));
    
                            if($ret){
                                if($p->exists("linkfilho")) $p->linkfilho = 'link_com_filho';
                            }else{
                                if($p->exists("linkfilho")) $p->linkfilho = 'link_sem_filho';
                            }
    
                            if($nivel==0 && $ret){  
                                if(isset($principal->item)){
                                    $tpl = new Template("tpl.part-produto.html");
                                    if($p->exists("produto_destaque")) $p->produto_destaque = tpl_part_produto($principal->item, $tpl,true,false,array('relacionados'=>true));
                                }
                                if($p->blockExists("BLOCK_CATEGORIA_SUB")) $p->parseBlock("BLOCK_CATEGORIA_SUB",true);
                            }
                            if($p->blockExists("BLOCK_CATEGORIAS_{$nivel}")) $p->parseBlock("BLOCK_CATEGORIAS_{$nivel}",true);
                        
                        }
    
                        unset($categoria);
    
                        if($nivel==0) $indice++;
    
                    }
    
                }else{
                    foreach( $principais[$cat_id] as $key=>$principal ){
                        $categoria = new categoria();                    
                        
                        if( $nivel==0 && $indice>categoria::QTD_CATEGORIAS_PRINCIPAIS ){   
                            $categoria->load_by_fetch($principal);
                            if($p->exists("categoria1")) $p->categoria1 = $categoria;
                            if($p->blockExists("BLOCK_CATEGORIAS_1")) $p->parseBlock("BLOCK_CATEGORIAS_1",true);
                            $has_vermais = true;
    
                        }else{
    
                            $varcat = "categoria{$nivel}";
                            $categoria->load_by_fetch($principal);
                            if($p->exists($varcat)) $p->$varcat = $categoria;
                            
                            $ret = $loopcat($categoria->id,($nivel+1));
    
                            if($ret){
                                if($p->exists("linkfilho")) $p->linkfilho = 'link_com_filho';
                            }else{
                                if($p->exists("linkfilho")) $p->linkfilho = 'link_sem_filho';
                            }
    
                            if($nivel==0 && $ret){  
                                if(isset($principal->item)){
                                    $tpl = new Template("tpl.part-produto.html");
                                    if($p->exists("produto_destaque")) $p->produto_destaque = tpl_part_produto($principal->item, $tpl,true,false,array('relacionados'=>true));
                                }
                                if($p->blockExists("BLOCK_CATEGORIA_SUB")) $p->parseBlock("BLOCK_CATEGORIA_SUB",true);
                            }
                            if($p->blockExists("BLOCK_CATEGORIAS_{$nivel}")) $p->parseBlock("BLOCK_CATEGORIAS_{$nivel}",true);
                        
                        }
    
                        unset($categoria);
    
                        if($nivel==0) $indice++;
    
                    }

                    if($has_vermais){
                        $catvermais  = new stdClass();
                        $catvermais->link = INDEX."brindes";
                        $catvermais->nome = "Ver Mais >";
                        $catvermais->id = $cat_id;
                        if($p->exists("categoria0")) $p->categoria0 = $catvermais;
                        if($p->blockExists("BLOCK_CATEGORIA_SUB")) $p->parseBlock("BLOCK_CATEGORIA_SUB",true);
                        if($p->blockExists("BLOCK_CATEGORIAS_0")) $p->parseBlock("BLOCK_CATEGORIAS_0",true);   
                    }
    
                }
                
                
                
                
              

                return true;
            };
            $loopcat();
        
            $temp = mycache::add($key,$p,$cachepath);
        }

        return $temp;
    }

    public function categoriadestaquedefinir(){
        $t = new TemplateAdminPop('admin/tpl.admin-cadastro-generico.html');

        $cmsitem = new cmsitem( array("tipo"=>"categoriaemdestaque") );
       
        if(request('action')=='salvar'){
            $cmsitem->set_by_array( $_REQUEST["cmsitem"] );
            $cmsitem->data_publicacao = to_bd_date($cmsitem->data_publicacao);
            $cmsitem->data_expiracao = to_bd_date($cmsitem->data_expiracao);
            $cmsitem->tipo = "categoriaemdestaque";

            $cmsitem->salva();

            $_SESSION["sucesso"] = tag("p","Dados salvos com sucesso.");

        }

        if($cmsitem->data_publicacao=="") $cmsitem->data_publicacao = bd_now();
        if($cmsitem->data_expiracao=="") $cmsitem->data_expiracao = date('Y-m-d H:i:s', strtotime("+ 30 days",strtotime(bd_now())) );

        if(request('action')=='sair'){
            $this->afterSave('sair',$this->arquivo,$categoria);
        }
		
        $this->adm_instance->montaMenuSimples($t);

        $t->h1 = h1("Categoria Em Destaque");

        $edicao  = "";
        
        $edicao .= tag("table class='table'",
            tag("tr",
                tag("td", select("cmsitem[titulo]",$cmsitem->titulo,"Selecione a Categoria:",categoria::opcoesPai(),true) )
                .tag("td", select("cmsitem[st_ativo]",$cmsitem->st_ativo,"Status:", array("S"=>"Ativo","N"=>"Inativo") ) )
            )
            .tag("tr",
                tag("td", inputData("cmsitem[data_publicacao]",formata_data_br($cmsitem->data_publicacao),"Data de Publicação:") )
                .tag("td", inputData("cmsitem[data_expiracao]",formata_data_br($cmsitem->data_expiracao),"Data de Expiração:") )
            )
        );

        $t->edicao = $edicao;

        $opts = array(
            'include_js' => PATH_SITE."admin/modulos/{$this->arquivo}/categoriaadmin.js"
        );

        $this->adm_instance->show($t, $opts);
    }
    
    static function categoriaDestaque(&$t){
        if($t->exists("categoriadestaque")){

            $query = query($sql = "SELECT DISTINCT cmsitem.* FROM cmsitem 
            INNER JOIN categoria ON ( categoria.id = cmsitem.titulo AND categoria.st_ativo='S' )
            INNER JOIN itemcategoria ON ( itemcategoria.categoria_id = categoria.id )
            INNER JOIN item ON ( item.id = itemcategoria.item_id AND item.st_ativo='S' AND item.imagem<>'')
            WHERE cmsitem.tipo = 'categoriaemdestaque' AND cmsitem.st_ativo ='S' 
            AND ( NOW() BETWEEN cmsitem.data_publicacao AND cmsitem.data_expiracao) ");

            //printr($sql);
           
           if($fetch=fetch($query)){

                $categoria = new categoria(intval($fetch->titulo));
                $tp = new Template(dirname(__FILE__)."/tpl.categoria-destaque.html");
                setVars($tp);
                $tp->categoria = $categoria;

                $q = query("SELECT item.* FROM item 
                    INNER JOIN itemcategoria ON ( itemcategoria.item_id = item.id AND itemcategoria.categoria_id = {$categoria->id} )
                    WHERE 1=1 AND (item.itemsku_id=0 OR item.itemsku_id IS NULL) AND item.st_ativo='S' AND item.imagem<>'' ORDER BY item.id DESC  LIMIT 20 ");
                $item = new item();
                while($prod=fetch($q)){
                    $item->load_by_fetch($prod);
                    
                    if($item->imagem !="" && file_exists("img/produtos/".$item->imagem)){
                        $tp->caminhoImg = config::get('URL')."img/produtos/".$item->imagem;
                    }else{
                        $tp->caminhoImg ="https://t7.com.br/wp-content/uploads/2018/05/".$item->imagem;
                    }
                    $tp->item = $item;
                    $tp->block("BLOCK_PRODUTO");
                }

                $t->categoriadestaque = $tp->getContent();
            }
        }
    }

    // private function tabelafator($categoria){
    //     $categoria->st_tabelapreco = "";
    //     $edicao = "<div class='well'>";

    //     $edicao .= tag("div class='alert alert-info'","Ao salvar, se selecionada uma tabela, esta será aplicada à todos os produtos desta categoria.");

    //     $edicao .= select_script("categoria[st_tabelapreco]",$categoria->st_tabelapreco,"ATIVAR TABELA PARA ATUALIZAR OS ITENS?",
    //         array("N"=>"INATIVO","S"=>"ATIVO")
    //         ,false
    //         ,"onchange='javascript: if(this.value==\"S\") $(\"#_tabela_preco\").show(); else $(\"#_tabela_preco\").hide(); '");

    //     $edicao .= "<div id='_tabela_preco' ".($categoria->st_tabelapreco!="S"?"style='display:none;'":"").">";
    //     // $edicao .= select("categoria[fator_id]",$categoria->fator_id,"Tabela Fator",fator::tabelaOpcoes(),true);
    //     $edicao .= tag("label","Tabela de Fatores:");
    //   //  $edicao .= select_script("categoria[fator_id]",$categoria->fator_id,"",fator::tabelaOpcoes(),true,"onchange='javascript:PreviewTabelaItem(this.value,0,\"".PATH_SITE."ajax.php/PreviewTabelaItem\");'");
    //   $edicao .= InputHidden("fator_id" , 0);  
    //   $edicao .= "<br>";

    //     /** ### */ 
    //    // $edicao .=  tag("div id='tabeladeprecos'", modulo_item::getTabelaPreview( new item(), false, array("categoria_tabelapreco"=>$categoria->tabelapreco) ) );
    //     /** ### */

    //     $edicao .= "</div>";
        
    //     return $edicao."</div>";
    // }

    // private function salvaTabelaPrecos($categoria){
    //     $preco = request("preco");
    //     $categoria->tabelapreco = json_encode($preco);
    //     if($categoria->atualiza()){

    //         // if( $categoria->st_tabelapreco=="S" ){
    //         //     query("DELETE FROM preco 
    //         //         WHERE preco.item_id IN (
    //         //             SELECT item.id FROM item 
    //         //             INNER JOIN itemcategoria ON (
    //         //                 itemcategoria.item_id=item.id 
    //         //                 AND itemcategoria.categoria_id = {$categoria->id}
    //         //             ) 
    //         //         ) ");

    //         //     foreach($preco as $key=>$value){
    //         //         modulo_fator::padrao($value["fator"]);
    //         //         $sql = "INSERT INTO preco (item_id,qtd_1,qtd_2,fator) SELECT itemcategoria.item_id,".$value["qtd_1"].",".$value["qtd_2"].",'".toFloat($value["fator"])."' FROM itemcategoria WHERE itemcategoria.categoria_id = {$categoria->id};";
    //         //         query($sql);
    //         //     }
    //         // }

    //     }
    // }
}
