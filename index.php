<?php

require 'global.php';

class Site {

	// Construtor da classe site
	function __construct(){
		$this->seopro = new seopro();
		$this->config = new config();
		$this->carrinho = new carrinho();
	}

	// Destrutor da classe Site
	function __destruct(){
		unset($this->config);
		unset($this->carrinho);
	}

	private function uordem(){
		$ordem = 1;
		$query = query("SELECT * FROM categoria ORDER BY nome");
		while($fetch=fetch($query)){
			query("UPDATE categoria SET ordem = $ordem WHERE id=$fetch->id");
			$ordem++;
		}
	}

	// Pagina index
	public function index(){

        $t = new TemplateSite('tpl.index.html');
		
		modulo_bannerprincipal::widget($t);

        // Produtos em destaque
        $p = new Template('tpl.part-produto.html');
        $destaques = get_produtos(array('apenas_principal'=>true,'categoria_id' => config::get('CATEGORIA_ID_DESTAQUES'),'limit' => 9));
        if(sizeof($destaques) > 0){
            foreach($destaques as $item){
                $t->produto = tpl_part_produto($item, $p);
                $t->block('BLOCK_DESTAQUE');
			}
			$t->block('BLOCK_DESTAQUES');
        }

		// promocoes
        $promocoes = get_produtos(array('apenas_principal'=>true,'categoria_id' => config::get('CATEGORIA_ID_PROMOCOES'),'limit'=>9));
        if(sizeof($promocoes) > 0){
            foreach($promocoes as $item){
                $t->produto = tpl_part_produto($item, $p);
                $t->block('BLOCK_PROMOCAO');
            }
            $t->block('BLOCK_PROMOCOES');
        }

		// lancamentos
		$lancamentos = get_produtos(array('apenas_principal'=>true,'categoria_id' => config::get('CATEGORIA_ID_LANCAMENTOS'),'limit'=>9));
        if(sizeof($lancamentos) > 0){
            foreach($lancamentos as $item){
                $t->produto = tpl_part_produto($item, $p);
                $t->block('BLOCK_LANCAMENTO');
            }
            $t->block('BLOCK_LANCAMENTOS');
        }

		$seo_opts = array();

		if( is_object( $this->seopro ) ){
			if($this->seopro->title!="")$seo_opts["title"] = $this->seopro->title;
			if($this->seopro->description!="")$seo_opts["description"] = $this->seopro->description;
			if($this->seopro->keywords!="")$seo_opts["keywords"] = $this->seopro->keywords;
			if($this->seopro->url!="")$seo_opts["url"] = $this->seopro->url;
		}

        set_SEO($t, $seo_opts);

		$t->home_selected="home_selected";
		$opts = array(
			'include_css' => PATH_SITE.'css/home.min.css?'.date("His"),
			'include_js' => PATH_SITE.'js/home.js?'.date("His")
        );

		$this->show($t, true, "home_selected", $opts);
	}

	public function sobre(){

        if(request('enviar')){
            try {
                modulo_contato::enviar();
                print json_encode(array('status'=>true,'msg'=>'Seu contato foi enviado com sucesso, em breve lhe retornaremos.'));
            }
            catch(Exception $ex){
                print json_encode(array('status'=>false,'msg'=>$ex->getMessage()));
            }
            return;
		}

		$t = new TemplateSite('tpl.sobre.html');

		$pagina = new pagina(array('chave'=>'PAGINA_SOBRE'));

		$sobreempresa = new cmsitem(["tipo"=>"sobreempresa_principal" ]);
		
		$t->sobreempresa = $sobreempresa;
		
		$query = query("SELECT * FROM destaquesobre WHERE st_ativo='S'");
		while($fetch=fetch($query)){
			$t->destaquesobre = $fetch;
			$t->parseBlock('BLOCK_DESTAQUESOBRE',true);
		}
		

		$t->pagina = $pagina = new pagina(array('chave'=>'PAGINA_SOBRE'));
	   
		$query = query($sql="SELECT id, nome, imagem, especifique from cadastro WHERE tipocadastro_id=".cadastro::TIPOCADASTRO_CLIENTESSATISFEITOS." AND st_ativo='S' LIMIT 4");

		while($fetch=fetch($query)){
			$t->cliente = $fetch;
			$t->parseBlock("BLOCK_CLIENTES_SATISFEITOS",true);
		}

	   $seo_opts = array();

	   if( is_object( $this->seopro ) ){
		   if($this->seopro->title!="")$seo_opts["title"] = $this->seopro->title;
		   if($this->seopro->description!="")$seo_opts["description"] = $this->seopro->description;
		   if($this->seopro->keywords!="")$seo_opts["keywords"] = $this->seopro->keywords;
		   if($this->seopro->url!="")$seo_opts["url"] = $this->seopro->url;
	   }

	   set_SEO($t, $seo_opts);

        $opts = array(
			'include_js' => PATH_SITE.'admin/modulos/contato/contatofront.js',
			'include_css' => PATH_SITE.'css/sobre.css?'.date("His"),
			'include_css' => PATH_SITE.'css/sobre.min.css?'.date("His"),
			'institucional' => true
		);

		//modulo_clientessatisfeitos::widget($t);

		$t->sobre_selected="sobre_selected";

		//$t->block('BLOCK_MENU_INSTITUCIONAL');

		$this->show($t, true, 'sobre_selected', $opts);

	}
	
	public function contato(){

        $t = new TemplateSite('tpl.contato.html');
		if(request('fuckoff')){
			//$this->setLocation('');
			print json_encode(array('status'=>false,'msg'=>'Você é um robo (; '));
		}else{
			if(request('enviar')){
				try {
					if(request('rodape')){
						modulo_contato::enviar(true);
					}else{
						modulo_contato::enviar();
					}
					print json_encode(array('status'=>true,'msg'=>'Seu contato foi enviado com sucesso, em breve lhe retornaremos.'));

				}
				catch(Exception $ex){
					print json_encode(array('status'=>false,'msg'=>$ex->getMessage()));
				}
				return;
			}

			$t->contato = modulo_contato::widget();

			$seo_opts = array( 'title' => 'Contato' );

			if( is_object( $this->seopro ) ){
				if($this->seopro->title!="")$seo_opts["title"] = $this->seopro->title;
				if($this->seopro->description!="")$seo_opts["description"] = $this->seopro->description;
				if($this->seopro->keywords!="")$seo_opts["keywords"] = $this->seopro->keywords;
				if($this->seopro->url!="")$seo_opts["url"] = $this->seopro->url;
			}
			set_SEO($t, $seo_opts);

			$opts = array(
				'include_js' => PATH_SITE.'admin/modulos/contato/contatofront.js'
				//,'include_css' => PATH_SITE.'css/contato.css?'.date("His")
				,'include_css' => PATH_SITE.'css/contato.min.css?'.date("His")
			);

			$t-> contato_selected = '';

			$this->show($t, true, 'contato_selected', $opts);
		}
	}

    public function sac(){
        $t = new TemplateSite('tpl.sac.html');

        if(request('f')){
            $arquivo = base64_decode(request('f'));
            $aquivoNome = $arquivo;
            $arquivoLocal = dirname(__FILE__).'/download/'.$aquivoNome;
            if (!file_exists($arquivoLocal)){
                $_SESSION['erro'] = tag('p','Erro, arquivo não encontrado.');
            }

            header("Pragma: public"); // required
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false); // required for certain browsers
            header("Content-Type: application/pdf");
            header("Content-Disposition: attachment; filename=\"".$aquivoNome."\";" );
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: ".filesize($arquivoLocal));

            ob_clean();
            flush();
            readfile($arquivoLocal);
        }

        if(request('enviar')){
            try {
                modulo_contato::enviar();
                print json_encode(array('status'=>true,'msg'=>'Seu contato foi enviado com sucesso, em breve lhe retornaremos.'));
            }
            catch(Exception $ex){
                print json_encode(array('status'=>false,'msg'=>$ex->getMessage()));
            }
            return;
        }

        $query = query("SELECT * FROM download WHERE st_ativo ='S' ORDER BY id DESC");
        while($fetch=fetch($query)){
            $t->download = $fetch;
            $t->file = base64_encode($fetch->arquivo);
            $t->parseBlock('BLOCK_SACC_DOWNLOAD',true);
        }

        if(config::get("HABILITA_DOWNLOAD_SACC")=='S'){
            $t->parseBlock('BLOCK_DOWNLOADS');
        }

        $pagina = new pagina(array('chave'=>'PAGINA_SACC'));
        $t->contato = modulo_contato::widget();

        set_SEO($t, array(
            'title' => $pagina->nome
        ));

        $opts = array(
            'include_js' => PATH_SITE.'admin/modulos/contato/contatofront.js'
        );

        $this->show($t, true, 'sacc_selected', $opts);
    }

	public function cadastro($carrinho=false){

        $t = new TemplateSite('tpl.cadastro.html');

		if(request('carrinho')){
			$carrinho = true;
		}

		$t->cadastro = new cadastrocliente();

        $cadastro = new cadastrocliente(intval(request('id')));
		$cadastro->set_by_array(request('cadastro'));
        $cadastro->cnpj = preg_replace('/\D/', '',$cadastro->cnpj);

		if(request('enviar')==1){

            try {

                if(!token_ok()){
                    throw new Exception('Invalido');
                }

                $novo = intval($cadastro->id) == 0;

                if(request("cad_login")){
                    $return_login = $this->login($carrinho);
                    if($return_login[0]==0){
                        throw new Exception($return_login[1]);
                        // $_SESSION['erro'] = $return_login[1];
                    }
                    if($return_login[0]==1){
                        $_SESSION['sucesso'] =  $return_login[1];
                        if($carrinho){
                            // $this->pedido();
                            die(json_encode(array('status'=>true, 'url_redirect'=>INDEX.'pedido')));
                        }
                    }
                }
                else {

                    if($novo || request('senha')){
                        $cadastro->senha = encode(request('senha'));
                    }

                    if(!$cadastro->validaCadastro($erro)){
                        throw new Exception(join('<br>', $erro));
                    }

                    if(request('st_recebe_post')!='S'){
                        $cadastro->st_recebe_post = 'N';
                    }
                    else {
                        $cadastro->st_recebe_post = 'S';
                    }

                    $cadastro->tipocadastro_id = cadastro::TIPOCADASTRO_CLIENTE;
                    $cadastro->cadastro_id = cadastro::vendedorPadrao();
                    $cadastro->salva();

                    $this->setLogado($cadastro);

                    $_SESSION['sucesso'] = tag("p","Dados salvos com sucesso!");

                    if($novo){
                        $cadastro->enviaEmailBemVindo();
                    }

                    if($this->carrinho->getQtdItens()>0){
                        print json_encode(array('status'=>true, 'url_redirect'=>INDEX.'pedido'));
                    }
                    else {
                        print json_encode(array('status'=>true, 'url_redirect'=>INDEX));
                    }
                }
            }
            catch(Exception $ex){
                print json_encode(array('status'=>false, 'msg'=>$ex->getMessage()));
            }
            return;
		}

		$t->cadastro = $cadastro;

		if($this->isLogado()){
            $t->cadastro = $cadastro = new cadastrocliente(intval($_SESSION['CADASTRO']->id));
			// $t->senha =  decode($_SESSION['CADASTRO']->senha);
            $t->c_readonly = "readonly";
            $t->msgextra = "<div class='alert alert-info'><span class='glyphicon glyphicon-info-sign'></span> Preencha apenas se quiser alterar a senha atual</div>";
		}

		$t->token = session_id();

		if($carrinho){
			$t->parseBlock("BLOCK_LOGIN");
			// $t->parseBlock("BLOCK_BREADCRUMBS");
			$t->parseBlock("BLOCK_CAD_CARRIONHO");
		}

        /*
        foreach(comoconheceu::opcoes() as $id => $nome){
            $t->comoconheceu_id = $id;
            $t->comoconheceu_nome = $nome;
            $t->comoconheceu_checked = $id == $cadastro->comoconheceu_id ? 'checked' : '';
            $t->parseBlock('BLOCK_COMOCONHECEU');
        }
        */

        $seo_opts = array();

		if( is_object( $this->seopro ) ){
			if($this->seopro->title!="")$seo_opts["title"] = $this->seopro->title;
			if($this->seopro->description!="")$seo_opts["description"] = $this->seopro->description;
			if($this->seopro->keywords!="")$seo_opts["keywords"] = $this->seopro->keywords;
			if($this->seopro->url!="")$seo_opts["url"] = $this->seopro->url;
		}

        set_SEO($t, $seo_opts);

		$this->show($t, '', 'cadastro_selected');
	}

	public function minhascompras(){
		if(!$this->isLogado()){$this->index();die();}

		if(request('get_itens')){

			$t = new Template('tpl.minhas_compras-itens.html');
			$id = intval(request('get_itens'));
			$cadastro = $_SESSION['CADASTRO'];

			$query = query($sql =
			"
			SELECT
				pedidoitem.*
			FROM
				pedidoitem
			INNER JOIN pedido ON (
				pedido.id = pedidoitem.pedido_id
			AND pedido.id = {$id}
			)
			INNER JOIN cadastro ON (
				cadastro.id = pedido.cadastro_id
			AND cadastro.id = {$cadastro->id}
			)
			ORDER BY pedidoitem.id DESC
			");

			while($fetch=fetch($query)){
				$pedidoitem = new pedidoitem();
				$pedidoitem->load_by_fetch($fetch);

				$info = unserialize($pedidoitem->info);

				$t->sub_total = @$info->sub_total;

				$t->pedidoitem = $pedidoitem;
				$item = new item($pedidoitem->item_id);

				if($item->itemsku_id){
					$item_pai = new item($item->itemsku_id);
					$item->nome = $item_pai->nome;
				}

				$t->item = $item;
				$t->parseBlock('BLOCK_PEDIDOITEM', true);
			}

			$t->path = PATH_SITE;
			print $t->getContent();
			return;
		}

		if(request('get_proposta')){
			$id = intval(request('get_proposta'));
			$query = query("SELECT * FROM proposta WHERE pedido_id = {$id} ORDER BY numero DESC LIMIT 1");
			$fetch = fetch($query);

			$out = array();

			if(@$fetch->id){
				$out['status'] = 1;
				$out['msg'] = $fetch->html;
			}else{
				$out['status'] = 0;
				$out['msg'] = 'Ainda não foi gerado proposta para este orçamento.';
			}
			echo json_encode($out);
			die();
		}

		$t = new TemplateSite('tpl.historico.html');

		$cadastro = $_SESSION['CADASTRO'];

		$query = query($sql =
		"
		SELECT
			pedido.*
		FROM
			pedido
		INNER JOIN cadastro ON (
			cadastro.id = pedido.cadastro_id
		AND cadastro.id = {$cadastro->id}
		)
		ORDER BY pedido.data_cadastro DESC
		");

		while($fetch=fetch($query)){
			$pedido = new pedido();
			$pedido->load_by_fetch($fetch);
			$t->pedido = $pedido;
			$t->pedidostatus = new pedidostatus($pedido->pedidostatus_id);
			$t->parseBlock('BLOCK_PEDIDO', true);
		}

		$this->show($t);

	}


    public function brindes($cat_string='', $subcat_string=''){
        return $this->produtos($cat_string, $subcat_string);
    }

	public function produtos($cat_string='', $subcat_string=''){

		if(array_key_exists("LAST_CATEGORIA",$_SESSION)){
			unset( $_SESSION["LAST_CATEGORIA"] );
		}

		$t = new TemplateSite('tpl.produtos.html');

        $categoria = new categoria(array('tag_nome'=>$cat_string, 'categoria_id'=>0));
        $categoriasub = null;
        if($subcat_string != ''){
            $categoriasub = new categoria(array('categoria_id'=>$categoria->id,'tag_nome'=>$subcat_string));
        }

		if($categoria->id){
			$_SESSION['LAST_CATEGORIA'] = $categoria->id;
			$_SESSION['URL_CONTINUAR_ORCANDO'] = isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']:$_SERVER['ORIG_PATH_INFO'];
		}

        $opts = array();
        $opts['apenas_principal'] = true;

        if($categoriasub && $categoriasub->id){
            $opts['categoria_id'] = $t->categoria_id = $categoriasub->id;
        }
        elseif($categoria && $categoria->id){
            $opts['categoria_id'] = $t->categoria_id = $categoria->id;
        }

        $cores = get_cores($opts);
        $produtos = get_produtos($opts);

        // Parse cores
        if(sizeof($cores)>0){
            foreach($cores as $cor){
                if($cor->imagem){
                    $cor->txt = '<img src="'.PATH_SITE.'img/cores/'.$cor->imagem.'" />';
                }
                else {
                    $cor->txt = $cor->nome;
                }
                $t->cor = $cor;
                $t->block('BLOCK_FILTRO_COR_ITEM');
            }
            $t->block('BLOCK_FILTRO_COR');
        }

        // Parse produtos
        $i = 0;
        $n = sizeof($produtos);

        $p = new Template('tpl.part-produto.html');
        while($i < $n){
			$item = @$produtos[$i++];

			$t->produto = tpl_part_produto($item, $p);
			$t->block('BLOCK_PRODUTOS');

            // $prod1 = @$produtos[$i++];
            // $prod2 = @$produtos[$i++];
            // $prod3 = @$produtos[$i++];
			// $prod4 = @$produtos[$i++];

            // $has = false;

            // for($x = 1; $x <= 4; $x++){

                // $prod = 'prod'.$x;
                // $item = $$prod;

                // if(!$item){
                    // break;
                // }

                // $has = true;
                // $t->produto = tpl_part_produto($item, $p);

                // $t->block('BLOCK_PRODUTOS');
                // $t->block('BLOCK_COL');
            // }

            // if($has){
                // $t->block('BLOCK_ROW');
            // }
        }

        if($categoria->id){
            if($categoriasub && $categoriasub->id){
                $t->h1 = '<a href="'.$categoriasub->getLink().'">'.$categoriasub->nome.'</a>';
                $t->h2 = '<h2>'.$categoriasub->nome.'</h2>';
                $t->categoriatrilha = modulo_categoria::breadcrumb($categoriasub);
			}
			else if($categoria && $categoria->id){
                $t->h1 = '<a id="tamanho_div da" href="'.$categoria->getLink().'">'.$categoria->nome.'</a>';
				$t->h2 = '<h2>'.$categoria->nome.'</h2>';
            }
            else {
                $t->h1 = '<a href="'.INDEX.'produtos/'.$categoria->tag_nome.'">'.$categoria->nome.'</a>';
                $t->h2 = '<h2>'.$categoria->nome.'</h2>';
            }

			$t->menucategoriapai = $categoria;
			// if($categoria->id)$t->parseBlock("BLOCK_PRODUTO_ALL_CAT");
			// else $t->parseBlock("BLOCK_PRODUTO_ALL");


            $categorias = get_categorias(array('categoria_id' => $categoria->id));

			//printr($categorias);
            $t->produtotodos= 'produtotodos';
			foreach($categorias as $categoriaitem){
                if($categoriaitem->tag_nome == ''){
                    $categoriaitem->salva(); // Retirar esse trecho depois
                }
                if($categoriasub && $categoriaitem->id == $categoriasub->id){
                    $t->menucategoriaselected = 'menucategoriaselected';
					$t->produtotodos= '';
                }
                else {
                    $t->menucategoriaselected = '';
                }
                $t->menucategoria = $categoriaitem;
				$t->parseBlock('BLOCK_CATEGORIAS_ITEM');


				// printr($categoria->tag_nome);
				// printr($categoriaitem->tag_nome);
				// printr('++++++++++++++++++');
			}

            if(sizeof($categorias)>0){
                $t->parseBlock('BLOCK_CATEGORIAS');
            }

        }
        else {
			$t->h1 = "PRODUTOS";
			
            $_SESSION['script'] = '
            <script>
                $(document).ready(function(){
                    $("#menunav2").slideToggle();
                });
            </script>
                ';
        }

		$t->token = session_id();
		$t->listagem_produtos = 'produtos';

		if(config::get("HABILITA_BANNER_LISTAGEM")== 'S'){
			if($categoriasub && $categoriasub->imagem_tema){
				$t->imagemcategoria = PATH_SITE."img/categorias/tema/{$categoriasub->imagem_tema}";
				$t->parseBlock("BLOCK_IMAGEM_CATEGORIA");
			}
			elseif($categoria && $categoria->imagem_tema){
				$t->imagemcategoria = PATH_SITE."img/categorias/tema/{$categoria->imagem_tema}";
				$t->parseBlock("BLOCK_IMAGEM_CATEGORIA");
			}
			else {
				$t->parseBlock("BLOCK_BANNER_PRODUTOS");
			}
		}


		$seo_opts = array(
			'title' => $categoria->id ? "Brindes em {$categoria->nome}".($categoriasub?" e {$categoriasub->nome}":"") : 'Brindes'
			,'description' => $categoria->id ? "Brindes em {$categoria->nome}".($categoriasub?" e {$categoriasub->nome}":"") : 'Brindes'
			,'keywords' => $categoria->id ? "Brindes, {$categoria->nome}".($categoriasub?", {$categoriasub->nome}":"") : ''
		);
		if( is_object( $this->seopro ) ){
			if($this->seopro->title!="")$seo_opts["title"] = $this->seopro->title;
			if($this->seopro->description!="")$seo_opts["description"] = $this->seopro->description;
			if($this->seopro->keywords!="")$seo_opts["keywords"] = $this->seopro->keywords;
			if($this->seopro->url!="")$seo_opts["url"] = $this->seopro->url;
		}
		set_SEO($t, $seo_opts);

        $opts = array(
            'include_js' => PATH_SITE.'js/produtos.js?300920162100'
            ,'include_css' => PATH_SITE.'css/listagem.min.css?'.date("His")
            //,'include_css' => PATH_SITE.'css/listagem.css?'.date("His")
        );


		// VERIFICA QUAL PARAMETRO ESTA CHEGANDO NA VARIAVEL
		if($cat_string == "Promocoes"){
			$t-> promocoes_selected = 'produtos_selected';
		}else{
			$t-> produtos_selected = 'produtos_selected';
		}


		$this->show($t, true, 'produtos_selected', $opts);
	}

    public function filtrar(){

        $t = new Template('tpl.produtos-filtrar.html');

        $categoria = new categoria(intval(request('categoria_id')));
        $cor = new cor(intval(request('cor_id')));

        $opts = array();

        if($categoria && $categoria->id){
            $opts['categoria_id'] = $categoria->id;
        }

        if($cor && $cor->id){
            $opts['cor_id'] = $cor->id;
        }
        else {
            $opts['apenas_principal'] = true;
        }

        $produtos = get_produtos($opts);

        // Parse produtos
        $i = 0;
        $n = sizeof($produtos);

        $p = new Template('tpl.part-produto.html');
        while($i < $n){

            $prod1 = @$produtos[$i++];
            $prod2 = @$produtos[$i++];
            $prod3 = @$produtos[$i++];
            $prod4 = @$produtos[$i++];

            $has = false;

            for($x = 1; $x <= 4; $x++){

                $prod = 'prod'.$x;
                $item = $$prod;

                if(!$item){
                    break;
                }

                $has = true;
                $t->produto = tpl_part_produto($item, $p);

                $t->block('BLOCK_PRODUTOS');
                $t->block('BLOCK_COL');
            }

            if($has){
                $t->block('BLOCK_ROW');
            }
        }

        $t->show();

    }

	public function promocoes($cat_string='', $subcat_string=''){

        if(array_key_exists("LAST_CATEGORIA",$_SESSION)){
            unset( $_SESSION["LAST_CATEGORIA"] );
        }

		$t = new TemplateSite('tpl.produtos.html');
	
		
		$categoria = new categoria(array('categoria_id' => config::get('CATEGORIA_ID_PROMOCOES')));
        $categoriasub = null;
        if($subcat_string != ''){
			    $categoriasub = new categoria(array('tag_nome'=>$subcat_string));
			}
			
		if($categoria->id){
			$_SESSION['LAST_CATEGORIA'] = $categoria->id;
			$_SESSION['URL_CONTINUAR_ORCANDO'] = $_SERVER['PATH_INFO'];
		}
			
        $opts = array();
        $opts['st_destaque'] = 'S';
        $opts['apenas_principal'] = true;
		
        if($categoriasub && $categoriasub->id){
			$opts['categoria_id'] = $categoriasub->id;
        }
        elseif($categoria && $categoria->id){
			$opts['categoria_id'] = $categoria->id;
        }
		
		
        $produtos = get_produtos($opts);
        $i = 0;
        $n = sizeof($produtos);

        $p = new Template('tpl.part-produto.html');
        while($i < $n){

            $prod1 = @$produtos[$i++];
            $prod2 = @$produtos[$i++];
            $prod3 = @$produtos[$i++];
            $prod4 = @$produtos[$i++];

            $has = false;

            for($x = 1; $x <= 4; $x++){

                $prod = 'prod'.$x;
                $item = $$prod;

                if(!$item){
                    break;
                }

                $has = true;
                $t->produto = tpl_part_produto($item, $p);

                $t->block('BLOCK_PRODUTOS');
                $t->block('BLOCK_COL');

            }

            if($has){
                $t->block('BLOCK_ROW');
            }
        }

        $t->token = session_id();

        $t->listagem_produtos = $categoria->nome;
        $t->h1 = "PROMOÇÕES";
        //$t->parseBlock("BLOCK_BANNER_PROMOCAO");

		$seo_opts = array(
			'title' => $categoria->id ? "Brindes em {$categoria->nome}".($categoriasub?" e {$categoriasub->nome}":"") : 'Brindes',
			'description' => $categoria->id ? "Brindes em {$categoria->nome}".($categoriasub?" e {$categoriasub->nome}":"") : 'Brindes',
			'keywords' => $categoria->id ? "Brindes, {$categoria->nome}".($categoriasub?", {$categoriasub->nome}":"") : ''
		);

		set_SEO($t, $seo_opts);

		if( is_object( $this->seopro ) ){
			if($this->seopro->title!="")$seo_opts["title"] = $this->seopro->title;
			if($this->seopro->description!="")$seo_opts["description"] = $this->seopro->description;
			if($this->seopro->keywords!="")$seo_opts["keywords"] = $this->seopro->keywords;
			if($this->seopro->url!="")$seo_opts["url"] = $this->seopro->url;
		}

		$opts = array(
            'include_js' => PATH_SITE.'js/produtos.js?300920162100'
            ,'include_css' => PATH_SITE.'css/listagem.min.css?'.date("His")
            //,'include_css' => PATH_SITE.'css/listagem.css?'.date("His")
        );

		if( is_object( $this->seopro ) ){
			if($this->seopro->title!="")$seo_opts["title"] = $this->seopro->title;
			if($this->seopro->description!="")$seo_opts["description"] = $this->seopro->description;
			if($this->seopro->keywords!="")$seo_opts["keywords"] = $this->seopro->keywords;
			if($this->seopro->url!="")$seo_opts["url"] = $this->seopro->url;
		}


        $this->show($t, true, "promocoes_selected", $opts);
    }

    public function indiqueproduto(){

        try {

            $destinatario = request('email');
            $nome_destinatario = request('nome');
            $remetente = request('remetente');
            $item_id = request('item_id');

            $item = new item($item_id);

            if(!$item->id) {
                throw new Exception("Ocorreu um erro, tente mais tarde.");
            }

            $erros = '';
            if(!is_email($destinatario)){
                $erros .= "Digite um e-mail válido.<br />";
            }
            if($nome_destinatario==''){
                $erros .= "Digite o nome do seu amigo.<br />";
            }
            if($remetente==''){
                $erros .= "Digite seu nome.<br />";
            }

            if($erros!=''){
                throw new Exception($erros);
            }

            // printr($item);
            // die();
            $tIndique = new Template('tpl.email-indique-produto.html');
			$tIndique->PATH_IMG = 'https://ajungimgteste.ajungsolutions.com';
            $tIndique->item = $item;
            $tIndique->nome_destinatario = $nome_destinatario;
            $tIndique->remetente = $remetente;
            $tIndique->config = new config();

            $email = new email();
            $email->addTo($destinatario,$nome_destinatario);
            $email->addHtml($tIndique->getContent());
            $email->send("Indicação - ".config::get('EMPRESA'));

			$sucesso = array('status'=>true,'msg'=>"E-mail enviado com sucesso!");
            echo json_encode($sucesso);

        }
        catch(Exception $ex){
			$error = array('status'=>false,'msg'=>$ex->getMessage());
            echo json_encode($error);
        }
    }

    public function brinde($nome='', $id=0){
        return $this->detalhe($nome, $id);
    }

	public function detalhe($nome='', $id=0){

		$t = new TemplateSite('tpl.detalhe.html');

        $item = $itempai = new item(array("tag_nome"=>$nome));
        if($item->itemsku_id>0){
            $cor_id = $item->cor_id;
            $item = $itempai = new item($item->itemsku_id);
        }
		if($item->fornecedor_2 == 'XBZ' || $item->fornecedor_2 == "SPOT" || $item->fornecedor_2 == "BEST GIFT"){
			$t->parseBlock("BLOCK_ITEM_XBZ");
		}
		if($item->fornecedor_2 =='ASIA'){
			//die('w');
			 $t->parseBlock("BLOCK_ITEM_ASIA_TRUE");
		}
        if(!$item->id) {
            return $this->index();
            // return false;
        }

        if($item->st_ativo != 'S'){
            return $this->index();
		}
		
		// compartilhamento do produto no facebook 
		$t->url_site = config::get("URL")."brinde/{$item->tag_nome}";

        $categorias = results(
        $sql =
			"SELECT categoria.id, categoria.tag_nome, categoria.nome, pai.tag_nome pai_tag_nome
			FROM categoria
			JOIN itemcategoria ON (categoria.id = itemcategoria.categoria_id AND itemcategoria.item_id = {$item->id})
			JOIN item ON (item.id = itemcategoria.item_id)
			LEFT JOIN categoria pai ON (pai.id = categoria.categoria_id)
			WHERE 1=1
			AND item.st_ativo = 'S'
			AND categoria.st_ativo = 'S'
			-- ORDER BY categoria.nome
			GROUP BY categoria.id, categoria.tag_nome, categoria.nome,pai_tag_nome"
		);

		
        if(sizeof($categorias) > 0){
            $tmp = array();
            $i = 0;
            foreach($categorias as $categoriaitem){
                $categoriamais = new categoria();
                $categoriamais->load_by_fetch($categoriaitem);
                if($categoriaitem->pai_tag_nome){
                    $categoriamais->tag_nome = $categoriaitem->pai_tag_nome.'/'.$categoriamais->tag_nome;
                }
                $tmp[] = "<a href='{$categoriamais->getLink()}'>{$categoriamais->nome}</a>";
                if($i++==3) {
                    break;
                }
            }
            $t->categoriamais = join(', ', $tmp);
			$t->parseBlock('BLOCK_CATEGORIA_MAIS');
			
        }

        $t->categoriaprincipal = $categoriaprincipal = $item->getCategoriaPrincipal();
        if(!$categoriaprincipal){
            return $this->index();
        }

        $t->produto = $item;
        $t->categoriatrilha = modulo_categoria::breadcrumb($categoriaprincipal);

        if(trim($item->infoadicional1) != ''){
            $t->block('BLOCK_INFOADICIONAL');
        }

		

        $detalhes = results(
			$sql =
			"
			SELECT DISTINCT id,cor_id,imagem,imagem_d1,imagem_d2,imagem_d3,imagem_d4,imagem_d5,imagem_d6,imagem_d7,imagem_d8,imagem_d9,imagem_d10,imagem_d11,imagem_d12,imagem_d13,imagem_d14,imagem_d15,imagem_d16
			FROM item
			WHERE (id = {$item->id} OR itemsku_id = {$item->id})
			AND st_ativo = 'S'  GROUP BY id,cor_id,imagem,imagem_d1,imagem_d2,imagem_d3,imagem_d4,imagem_d5,imagem_d6,imagem_d7,imagem_d8,imagem_d9,imagem_d10,imagem_d11,imagem_d12,imagem_d13,imagem_d14,imagem_d15,imagem_d16 ORDER BY itemsku_id
			");

		//printr($sql);

        $qtd_detalhe = config::get("QTD_IMAGENS_DETALHE");
        $keys = array();

		if(!file_exists("img/produtos/{$item->imagem}")){
			$t->imagemdetalheprincipal = PATH_IMG."produtos/{$item->imagem}?w=".config::get('IMG3_TAMANHO');
		}else{
			$t->imagemdetalheprincipal = PATH_SITE."img/produtos/{$item->imagem}";
		};

		foreach($detalhes as $detalhe){
            for($i=0;$i<=$qtd_detalhe;$i++){

                $imagem = "imagem";

                if($i>0){
					$imagem = "imagem_d{$i}";
                }

                if($imagem == 'imagem' && isset($keys[$detalhe->cor_id])){
					continue;
                }
			
				if($detalhe->$imagem!=""){
					if(!file_exists("img/produtos/{$detalhe->$imagem}")){
						$t->indice = $i;
						$t->imagem_detalhe = PATH_IMG."produtos/{$detalhe->$imagem}?w".config::get('IMG2_TAMANHO');
						$t->imagemampliada = PATH_IMG."produtos/{$detalhe->$imagem}?w=".config::get('IMG2_TAMANHO');
						$t->imagemzoom = PATH_IMG."produtos/{$detalhe->$imagem}?w=".config::get('IMG3_TAMANHO');
						$t->parseBlock("BLOCK_IMAGEM_DETALHE",true);
						$t->parseBlock("BLOCK_IMAGEM_DETALHE_MOBILE",true);
					}else{
						$t->indice = $i;
						 $t->imagem_detalhe = PATH_SITE."img/produtos/{$detalhe->$imagem}";
						 $t->imagemampliada = PATH_SITE."img/produtos/{$detalhe->$imagem}";
						 $t->imagemzoom = PATH_SITE."img/produtos/{$detalhe->$imagem}";
							$t->parseBlock("BLOCK_IMAGEM_DETALHE",true);
						 $t->parseBlock("BLOCK_IMAGEM_DETALHE_MOBILE",true);
					}
				}
				$keys[$detalhe->cor_id] = $detalhe;
            }
        }

     	// Cores
        if(config::get("HABILITA_COR")== 'S'){

            $itemcor = new itemcor(array('item_id'=>$item->id,'st_default'=>'S'));
          
            $query = query("SELECT * FROM item WHERE (itemsku_id = {$item->id}) AND st_ativo = 'S' AND imagem<>'' ");

            $temcor = false;
            $select_cor = '';

            while($fetch=fetch($query)){

                $cor = new cor($fetch->cor_id);
                $tmp = new item($fetch->id);

                $select_cor .= '<option value="'.$cor->id.'" data-src="'.PATH_SITE.'img/produtos/'.$tmp->imagem.'" data-timsrc="'.PATH_SITE.'img/produtos/orignal/'.$tmp->imagem.'" data-corid="'.$cor->id.'" data-itemid="'.$tmp->id.'" data-itemreferencia="'.$tmp->referencia.'" data-preco="'.$tmp->getPrecoFormatado().'">'.$cor->nome.'</option>';
               	$temcor = true;
				
            }

            if($temcor){
                $t->select_cor =
                "
                <div class='col-md-10 text-right padding_0 d-table-select'>
					<table style='width:100%' >
						<tr>
							<td style='width:80px'>
								<span>Cor</span>
							</td>
							<td>
							<div class='div-select'>
								<div class='d_select'>
									<select name='cor_id' class='form-control select_det select_cor js-select-cor'>
										<option value='1'>Selecione uma cor</option>
										{$select_cor}
									</select>
								</div>
							</div>
							</td>
						</tr>
					</table>
                </div>

                ";
            }
        }

        if(modulo_gravacao::ATIVO){

            $tem_gravacao = false;
            $select_gravacao = '';

            foreach(gravacao::opcoesByItem($item) as $gravacao_id => $gravacao_nome){
                $select_gravacao .= '<option value="'.$gravacao_id.'">'.$gravacao_nome.'</option>';
                $tem_gravacao = true;
            }

            if($tem_gravacao){
                $t->select_gravacao =
                "
					<div class='col-md-10 text-right padding_0 d-table-select'>
						<table style='width:100%' cellspacing='10'>
							<tr>
								<td style='width:80px;'>
									<span>Gravação</span>
								</td>
								<td>
									<div class='div-select'>
										<div class='d_select'>
											<select name='gravacao_id' class='form-control select_det js-select-gravacao'>
												<option value=''>Selecione uma gravação</option>
												{$select_gravacao}
											</select>
										</div>
									</div>
								</td>
							</tr>
						</table>
					</div>
                ";
            }
        }

        // if(modulo_variacaopreco::ATIVO){

        //     $tabela_precos = '';
        //     $tem_preco = false;

        //     foreach(variacaopreco::tabelaPreco($item) as $preco){
        //         $tabela_precos .=
        //         "
        //         <tr>
        //             <td>{$preco->qtd_1}</td>
        //             <td>R$ ".number_format($preco->preco,2,',','')."</td>
        //         </tr>
        //         ";
        //         $tem_preco = true;
        //     }

        //     if($tem_preco){

        //         $t->tabela_precos =
        //         "
        //         <table class='table table-precos table-condensed'>
        //             <tr>
        //                 <th>Quantidade</th>
        //                 <th>Preço Unitário*</th>
        //             </tr>
        //             {$tabela_precos}
        //         </table>
        //         <p style='margin:0px;padding:0px'>* Uma gravação inclusa.</p>
        //         <br>
        //         ";

        //     }
        // }

        $t->parseBlock("BLOCK_CONVENCIONAL");

        // if($itempai->disponibilidade=='S'){
        // }
		$t->parseBlock("BLOCK_ORCAMENTO_DISPONIVEL");

        $listagem_produtos = 'produtos';

        $p = new Template('tpl.part-produto.html');

        $opts = array();
        $opts['relacionado_id'] = $item->id;
        $opts['limit'] = 12;
        $opts['order_by'] = 'rand()';
        $opts['apenas_principal'] = true;

        $produtos = get_produtos($opts);

		if(array_key_exists("LAST_CATEGORIA",$_SESSION)){
			unset($_SESSION["LAST_CATEGORIA"]);
		}

		$t->listagem_produtos = $listagem_produtos;
		$t->listagem_produtos_bread = str_replace("_"," ",$listagem_produtos);
		$t->token = session_id();


		$seo_opts = array(
			'title' => "Brinde {$item->nome}"
			,'description' => "Brindes ".join(', ', array($item->nome,$item->referencia,$item->descricao,$item->getUrlKeywords()))
			,'keywords' => $item->getUrlKeywords()
			,'image' => PATH_IMG . 'produtos/' . $detalhe->imagem
			,'url' => $item->getLink()
		);
		if( is_object( $this->seopro ) ){
			if($this->seopro->title!="")$seo_opts["title"] = $this->seopro->title;
			if($this->seopro->description!="")$seo_opts["description"] = $this->seopro->description;
			if($this->seopro->keywords!="")$seo_opts["keywords"] = $this->seopro->keywords;
			if($this->seopro->url!="")$seo_opts["url"] = $this->seopro->url;
		}

        set_SEO($t, $seo_opts);

        $opts = array(
            'include_js' => PATH_SITE.'js/detalhe.js'
            ,'include_css' => PATH_SITE.'css/detalhe.min.css?'.date("His") 
        );

		
		if(sizeof($produtos) > 0){
			foreach($produtos as $item){
				$t->produto = tpl_part_produto($item, $p);
				$t->block('BLOCK_PRODUTOS');
				if($item->st_amamos=='S'){
					$listagem_produtos = 'lancamentos';
				}
			}
		}

		$this->show($t, true, '', $opts);
	}

	public function midia(){
		$t = new TemplateSite('tpl.midia.html');

		// $t->width = "width:100%;";
		// $t->none = "display:none;";
		// $t->border = "border:0px;";

		$catalogo = new catalogo(array('st_ativo'=>'S'));
		$t->catalogo = $catalogo;

		$query = query("SELECT id FROM catalogoimagem WHERE catalogo_id = {$catalogo->id} ORDER BY id DESC");
		$cont= 0;
		while($fetch=fetch($query)){
			$catalogoimagem = new catalogoimagem($fetch->id);
			if($cont==0){
				$t->catalogoimagemum = $catalogoimagem;
			}
			$t->catalogoimagem =  $catalogoimagem;
			$t->parseBlock('BLOCK_CATALOGO', true);
			$cont++;
			//printr($fetch);
		}

		$t->midia_selected="midia_selected";

		$this -> show($t);
	}

	public function clientes(){
		$t = new TemplateSite('tpl.clientes.html');

		// $t->width = "width:100%;";
		// $t->none = "display:none;";
		// $t->border = "border:0px;";


		$query = query("SELECT * FROM clientes WHERE st_ativo = 'S' ");
		while($fetch=fetch($query)){
			$t->clientes = new clientes($fetch->id);
			$t->parseBlock('BLOCK_CLIENTES',true);
		}
		$t->cliente_selected="cliente_selected";

		$this -> show($t);
	}

	public function pedido(){

        $debug = false;

		if(request('add_item')){
			if(!token_ok()){
                die();
            }
		}

		$t = new TemplateSite('tpl.pedido.html');
		$t->h1 = 'Carrinho de Or&ccedil;amento';
		//$t->carrinho = $this->carrinho;
		$itens = $this->carrinho->get_itens();
		$qtdItens = 0;
		$i = 1;

		if($this->config->HABILITA_MATERIA_PRIMA=='S'){
			//$t->parseBlock('BLOCK_MATERIA_TITULO');
		}
		if($this->config->HABILITA_GRAVACAO=='S'){
			//$t->parseBlock('BLOCK_GRAVACAO_TITULO');
		}
		if($this->config->HABILITA_COR=='S'){
			//$t->parseBlock('BLOCK_COR_TITULO');
		}
		if($this->config->HABILITA_QTD_COR_LOGO=='S'){
			$t->parseBlock('BLOCK_QTD_COR_LOGO_TITULO');
		}

		$qtdItem = $this->carrinho->getQtdItens();

		foreach (array_reverse($itens) as $item){

            if($debug){
                printr("item no carrinho");
                printr($item);
            }

			if($item->item_qtd>0){

                // $cor_id = request('cor_id');
				// $cor = new cor(intval($cor_id));
                $cor = modulo_cache::model('cor',$item->cor_id);

                if($debug){
                    printr("cor");
                    printr($cor);
                }

				$itemcor = new itemcor();
				if($item->item_id&&$cor->id){
					$itemcor = new itemcor(array('item_id'=>$item->item_id,'cor_id'=>$cor->id));
					if($itemcor->imagem != ''
                    && file_exists('img/produtos/'.$itemcor->imagem)){
						$item->item_imagem = $itemcor->imagem;
					}
				}

				$_item = new item($item->item_id);
				$item->sub_total =  $_item->getCalculaSubTotal($item->item_qtd);
				//$item->sub_total =  money(intval($item->item_qtd)*floatval(str_replace(",",".",$item->item_preco)));
				$item->item_preco_formatado = $_item->getPrecoFormatado();

				if(file_exists("img/produtos/".$item->item_imagem)){
					$t->imagem_produto = PATH_SITE."timthumb/timthumb.php?src=".PATH_SITE."img/produtos/".$item->item_imagem."&w=233";
				}else{
					$t->imagem_produto = PATH_IMG."produtos/".$item->item_imagem."?w=".config::get('IMG1_TAMANHO');
				}
				

				$t->list_pedido = $item;

				// Carrega Possiveis Gravacoes
				$caracvalor = new caracvalor();
				$temGravacoes = false;

				// if($item->cor_imagem!=''){
                if($cor->id){
					$t->parseBlock("BLOCK_ITEMCOR",true);
					//$t->parseBlock("BLOCK_ITEMCORMOB",true);
				}

				if(modulo_gravacao::ATIVO){

					$tem_gravacao = false;
					$select_gravacao = '';

					if($item->item_itemsku_id){
						$_item = new item($item->item_itemsku_id);
					}else{
						$_item = new item($item->item_id);
					}
					//printr($_item);
					foreach(gravacao::opcoesByItem($_item) as $gravacao_id => $gravacao_nome){
						$select_gravacao .= '<option value="'.$gravacao_id.'" '.($item->gravacao_id==$gravacao_id?"selected":"").'>'.$gravacao_nome.'</option>';
						$tem_gravacao = true;
					}

					if($tem_gravacao != ""){
						$t-> select_gravacao = $select_gravacao;
						$t-> parseBlock('BLOCK_GRACACAO_PEDIDO', true);

					}
				}

				$t->parseBlock('BLOCK_LIST_PEDIDO', true);
				$qtdItens ++;
				$i ++;
			}
		}

		foreach(optionsEstado() as $uf=>$nome){

			$e = new stdClass();
			$e->uf = $uf;
			$e->nome = $nome;
			$e->selected = '';

			if(@$_REQUEST['cadastro']['uf']){
				$e->selected = (@$_REQUEST['cadastro']['uf']==$uf)?'selected':'';
			}
		}

		if($qtdItens==0){
			$_SESSION['erro'] = 'Nenhum produto adicionado ao seu pedido';
			//return $this->index();
			$this->setLocation('');
		}

		$link_back = '';
		if(array_key_exists("URL_CONTINUAR_ORCANDO",$_SESSION)){
			$link_back = $_SESSION['URL_CONTINUAR_ORCANDO'];
		}
		$t->link_back = str_replace("//","/",INDEX.$link_back);

		partDadosClienteCheckout($t);

		$t->token = session_id();

        set_SEO($t
            ,array(
                'title' => "Pedido"
            ));

		$opts = array(
			'include_js' => PATH_SITE.'js/pedido.js?'.date("His")
			,'include_css' => PATH_SITE.'css/pedido.min.css?'.date("His")
			//,'include_css' => PATH_SITE.'css/pedido.css?'.date("His")
		);

		$this->show($t, '','', $opts);
	}

	public function itemExcluir(){

        $item_id = request('item');



		foreach($this->carrinho->get_itens() as $key=>$value){
			if($value->item_id == $item_id){
				unset($_SESSION['S_CARRINHO']['itens'][$key]);
			}
		}

		$out = array();

		$qtdItem = $this->carrinho->getQtdItens();

		if($qtdItem>0){
			$out[0] = 1;
			$out[1] = $item_id;
		}
        else {
			$out[0] = 2;
		}
		echo json_encode($out);
	}

	public function pedido_finalizado(){

        if(!$this->isLogado()){
			header("Location: ".INDEX);
			die();
		}

        $cadastro = $_SESSION['CADASTRO'];
		$cadastro = new cadastro($cadastro->id);

		$vendedor = new cadastro($cadastro->cadastro_id);

		$pedido = new pedido();
		$pedido->cadastro_id = $cadastro->id;
		$pedido->vendedor_id = ($vendedor->id?$vendedor->id:cadastro::vendedorPadrao());
		$pedido->pedidostatus_id = 1; // Lancado
		$pedido->anexo = $this->carrinho->getImagemAnexo();

		if(!$pedido->salva()){
			$_SESSION['erro'] = tag("p","Ocorreu um erro ao solicitar seu or&ccedil;amento, entre em contato conosco e informe o ocorrido.");
            header('location:'.PATH_SITE.'index.php/pedido_confirmacao');
            die();
		}

        // INSERE OS ITENS
        $itens = $this->carrinho->get_itens();
        foreach ($itens as $item){
            if($item->item_qtd>0){

                $pedidoitem = new pedidoitem();
                $pedidoitem->item_id = $item->item_id;
				$pedidoitem->pedido_id = $pedido->id;
				
				// VERIFICA SE O ITEM_QT2 E 3 ESTAO CHEGANDO VAZIO 
				if(($item->item_qtd2 == '') or ($item->item_qtd2 == NULL)) $item->item_qtd2 = 0;
				if(($item->item_qtd3 == '') or ($item->item_qtd3 == NULL)) $item->item_qtd3 = 0;
                $pedidoitem->item_preco = 0;
                $pedidoitem->item_qtd = $item->item_qtd;
                $pedidoitem->item_qtd2 = $item->item_qtd2;
                $pedidoitem->item_qtd3 = $item->item_qtd3;
                $pedidoitem->info = serialize($item);
                $pedidoitem->insere();
            }
        }

        $tEmail = new Template('tpl.email-pedido-cliente.html');
        $tEmail->config = $this->config;
        $tEmail->pedido = $pedido;
        $tEmail->cadastro = $cadastro;

        $e = new email();

        // if($_SERVER['SERVER_NAME']=='localhost'){
        //     $e->addTo('fgregorio@gmail.com', 'Felipe Gregorio');
        //     $e->send("SOLICITAÇÃO DE ORÇAMENTO - {$cadastro->nome}");
        // }
        // else {
            $e->addTo($cadastro->email, $cadastro->nome);
            $e->addCc($vendedor->email, $vendedor->nome);
            //$e->addReplyTo($vendedor->email, $vendedor->nome);
            $e->addHtml($tEmail->getContent());
            $e->addBcc($this->config->EMAIL_ADMINISTRACAO, $this->config->EMPRESA);
            // $e->send("SOLICITAÇÃO DE ORÇAMENTO - {$cadastro->nome}");
        //}

        //
        $t = new TemplateSite('tpl.pedido-finalizado.html');
        $t->cadastro = $cadastro;
        $t->pedido = $pedido;

        $qtdItem = $this->carrinho->getQtdItens();
        $qtdItens = 0;
        $i = 0;
        foreach (array_reverse($itens) as $item){
            if($item->item_qtd>0){

                //$item->sub_total =  money(intval($item->item_qtd)*intval($item->item_preco));
                //$_item = new item($item->item_id);
                $_item = new item($item->item_id);
                $item->sub_total =  $_item->getCalculaSubTotal($item->item_qtd);
                $item->item_preco = $_item->getPrecoFormatado();

                $t->list_pedido = $item;

                if($item->cor_id){
                    $t->parseBlock("BLOCK_ITEMCOR",true);
                }
                if($item->item_qtd2!=''){
                    $t->parseBlock("BLOCK_QTD_2",true);
                }
                if($item->item_qtd3!=''){
                    $t->parseBlock("BLOCK_QTD_3",true);
                }

                $t->parseBlock('BLOCK_LIST_PEDIDO', true);
                $qtdItens ++;
                $i ++;
            }
        }
        $this->carrinho->clear();
        $this->show($t, true);
	}

    public function pedido_confirma($id){

        if(!$this->isLogado()){
            header("Location: ".INDEX);
            die();
        }

        $pedido = new pedido($id);

        $cadastro = $_SESSION['CADASTRO'];
        $cadastro = new cadastro($cadastro->id);
        $vendedor = new cadastro($cadastro->cadastro_id);

        if($pedido->cadastro_id != $cadastro->id){
            header("LLocation: ".INDEX);
            die();
        }

        //
        $t = new TemplateSite('tpl.pedido-finalizado.html');
        $t->cadastro = $cadastro;
        $t->pedido = $pedido;

        $qtdItens = 0;
        $i = 0;

        foreach($pedido->get_childs('pedidoitem') as $pedidoitem){

            //$item->sub_total =  money(intval($item->item_qtd)*intval($item->item_preco));
            //$_item = new item($item->item_id);
            // $_item = new item($item->item_id);
            $item = new item($pedidoitem->item_id);

            // $item->sub_total =  $_item->getCalculaSubTotal($item->item_qtd);
            // $item->item_preco = $_item->getPrecoFormatado();

            $t->list_pedido = $item;

            if($item->cor_imagem!=''){
                $t->parseBlock("BLOCK_ITEMCOR",true);
            }

            if($item->item_qtd2!=''){
                $t->parseBlock("BLOCK_QTD_2",true);
            }
            if($item->item_qtd3!=''){
                $t->parseBlock("BLOCK_QTD_3",true);
            }
            //$t->parseBlock("BLOCK_QTD_ESPECIAL",true);

            $t->parseBlock('BLOCK_LIST_PEDIDO', true);
            $qtdItens ++;
            $i ++;

        }

        /*
        $qtdItem = $this->carrinho->getQtdItens();
        $qtdItens = 0;
        $i = 0;

        foreach (array_reverse($itens) as $item){
            if($item->item_qtd>0){

                //$item->sub_total =  money(intval($item->item_qtd)*intval($item->item_preco));
                //$_item = new item($item->item_id);
                $_item = new item($item->item_id);
                $item->sub_total =  $_item->getCalculaSubTotal($item->item_qtd);
                $item->item_preco = $_item->getPrecoFormatado();

                $t->list_pedido = $item;

                if($item->cor_imagem!=''){
                    $t->parseBlock("BLOCK_ITEMCOR",true);
                }
                if(ClienteEspecial()){
                    $t->parseBlock("BLOCK_PRECO_ESPECIAL",true);
                }
                else {
                    if($item->item_qtd2!=''){
                        $t->parseBlock("BLOCK_QTD_2",true);
                    }
                    if($item->item_qtd3!=''){
                        $t->parseBlock("BLOCK_QTD_3",true);
                    }
                    //$t->parseBlock("BLOCK_QTD_ESPECIAL",true);
                }

                $t->parseBlock('BLOCK_LIST_PEDIDO', true);
                $qtdItens ++;
                $i ++;
            }
        }
        */

        $this->show($t, true);

    }

	public function cadastro_carrinho(){
		$this->cadastro(true);
	}


	public function catalogo_online(){
		$t = new TemplateSite("tpl.catalogo-online2.html");


		if(request('f')){
			$arquivo = base64_decode(request('f'));
			$aquivoNome = $arquivo;
			$arquivoLocal = dirname(__FILE__).'/img/catalogo/download/'.$aquivoNome;
			if (!file_exists($arquivoLocal)){
				$_SESSION['erro'] = tag('p','Erro, arquivo não encontrado.');
			}

			header("Pragma: public"); // required
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private", false); // required for certain browsers
			header("Content-Type: application/pdf");
			header("Content-Disposition: attachment; filename=\"".$aquivoNome."\";" );
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".filesize($arquivoLocal));

			ob_clean();
			flush();
			readfile($arquivoLocal);
		}


		$catalogo = new catalogo(array('st_ativo'=>'S'));
		if($catalogo->id){

			if($catalogo->arquivo!=''){
				$t->parseBlock("BLOCK_CATALOGO_DOWNLOAD");
				$catalogo->arquivo = base64_encode($catalogo->arquivo);
			}
			$t->catalogo = $catalogo;

			//$t->parseBlock("BLOCK_CATALOGO_LIB");
			$t->parseBlock("BLOCK_CATALOGO");
		}else{
			$t->parseBlock("BLOCK_SEM_CATALOGO");
		}


		$this->show($t);
	}

	public function showCatalogo($id){
		$t = new Template("tpl.sho-catalogo-online.html");
		$t->path = PATH_SITE;

		$catalogo = new catalogo($id);
		if($catalogo->id){
			$t->pathfile = $catalogo->pathfile;
			$query = query("SELECT * FROM catalogoimagem WHERE catalogo_id = {$catalogo->id} ORDER BY ordem, imagem, id");
			while($fetch=fetch($query)){
				$catalogoimagem = new catalogoimagem($fetch->id);
				$t->catalogoimagem = $catalogoimagem;
				$t->parseBlock("BLOCK_PAGES",true);
				//$t->parseBlock("BLOCK_CAMINHOS",true);
			}
		}

		echo $t->getContent();
		die();
	}

	public function blog($page=0){
		$t = new TemplateSite('tpl.blog.html');

		if($page<1){$page=0;}

		$pageLimit = $page*10;

		$totalPosts = rows(query($sql="SELECT * FROM post WHERE st_ativo='S' LIMIT {$pageLimit},999999999999999999"));
		$query = query("SELECT * FROM post WHERE st_ativo='S' ORDER BY data_cadastro DESC LIMIT {$pageLimit},10");
		$qtdPagina = rows($query);

		while($fetch=fetch($query)){

			$t->post = new post($fetch->id);

			if($fetch->id){
				if( preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', $fetch->data_cadastro, $matches) ){
					list(,$ano,$mes,$dia,$hora,$minuto,$segundo)=$matches;
					$t->dia = $dia;
					$t->mes = get_mes($mes);
					$t->ano = $ano;
				}else{
					preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', $fetch->data_cadastro, $matches);
					list(,$ano,$mes,$dia)=$matches;
					$t->dia = $dia;
					$t->mes = get_mes($mes);
					$t->ano = $ano;
				}
			}

			$t->qtd_comments = rows(query($sql="SELECT * FROM postcomment WHERE post_id = {$fetch->id} AND st_ativo='S' AND (postcomment_id = 0 OR postcomment_id is NULL)"));


			$_query =  query("SELECT * FROM postimagem WHERE post_id = {$fetch->id} AND st_ativo='S'");
			while($_fetch=fetch($_query)){
				$t->postimagem = $_fetch;
				$t->parseBlock("BLOCK_POST_IMAGEM",true);
			}
			if(rows($_query)>0){
				$t->parseBlock("BLOCK_IMAGENS",true);
			}

			$t->parseBlock("BLOCK_BLOG_POSTS",true);
		}


		if($totalPosts>10 || $page>0){
			if($page>0){
				$t->postsrecentes = $page-1;
				$t->parseBlock("BLOCK_POSTS_RECENTES");
			}
			if($totalPosts>$qtdPagina){
				$t->postsantigos = $page+1;
				$t->parseBlock("BLOCK_POSTS_ANTIGOS");
			}
			$t->parseBlock("BLOCK_POSTS_NAVEGACAO");
		}

		$this->show($t, true, "blog_selected");
	}

	public function post($id=0){

		$post = new post($id);
		if(!$post->id){header("Location:".INDEX."blog");}

		$t = new TemplateSite('tpl.post-blog.html');

		$_query =  query("SELECT * FROM postimagem WHERE post_id = {$post->id} AND st_ativo='S'");
		while($_fetch=fetch($_query)){
			$t->postimagem = $_fetch;
			$t->parseBlock("BLOCK_POST_IMAGEM",true);
		}

		if( preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', $post->data_cadastro, $matches)){
			list(,$ano,$mes,$dia,$hora,$minuto,$segundo)=$matches;
			$t->dia = $dia;
			$t->mes = get_mes($mes);
			$t->ano = $ano;
		}else{
			preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', $post->data_cadastro, $matches);
			list(,$ano,$mes,$dia)=$matches;
			$t->dia = $dia;
			$t->mes = get_mes($mes);
			$t->ano = $ano;
		}

		$t->qtd_comments = rows($query = query("SELECT * FROM postcomment WHERE post_id = {$post->id} AND st_ativo='S' AND (postcomment_id = 0 OR postcomment_id is NULL)"));
		while($fetch=fetch($query)){
			$t->comments = $fetch;

			$_query = query($sql="SELECT * FROM postcomment WHERE post_id = {$post->id} AND st_ativo='S' AND postcomment_id = {$fetch->id}");
			while($_fetch=fetch($_query)){
				$t->comments_reply = $_fetch;
				$t->parseBlock("BLOCK_POSTS_COMMENTS_REPLY",true);
			}

			$t->parseBlock("BLOCK_POSTS_COMMENTS",true);
		}

		$t->post = $post;
		$cadastro = new cadastro(@$_SESSION["CADASTRO"]->id);
		$t->cadastro = $cadastro;
		$t->token = session_id();

		$t->meta_description = $post->TextoFormatadoListagemMeta();
		$t->meta_title = $post->titulo;

		$this->show($t, true);
	}

	public function comentapost(){
		if(!token_ok()){die();}
		$postcomment = new postcomment();
		$postcomment->set_by_array($_REQUEST['postcomment']);
		$postcomment->st_ativo = "N";
		if($postcomment->cadastro_id == 0){
			$postcomment->cadastro_id = null;
		}
		$out = array();
		if($postcomment->valida($erros)){
			$postcomment->salva();
			$out[0] = 1;
			$out[1] = "Comentário enviado, aguardando moderação.";
		}else{
			$out[0] = 0;
			$out[1] = $erros;
		}
		echo json_encode($out);
	}

	public function alterar_cadastro(){
		$t = new TemplateSite('tpl.alterar-cadastro.html');
		$this->show($t, true);
	}

	public function get_destaques_home(&$t){

		$sql = "SELECT
					SQL_CACHE
					DISTINCT item.* FROM
					item
				INNER JOIN itemcategoria ON (
					item.id = itemcategoria.item_id
				)
				INNER JOIN categoria ON (
					itemcategoria.categoria_id = categoria.id
				AND categoria.st_ativo = 'S'
				AND categoria.st_fixo = 'N'
				)
				WHERE
					item.st_ativo = 'S'
				AND item.st_destaque = 'S'
				ORDER BY rand(), item.referencia, item.nome, item.descricao
				LIMIT 3
				";

		$query = query($sql);
		$itens = array();

		while($fetch=fetch($query)){
			$itens[] = $fetch;
		}

		$temAlgum = false;

		//$cont_produto_1 = '';
		for($i=0,$posicao=1,$n=sizeof($itens);$i<$n;$i++){

			$p = new Template('tpl.part-produto.html');
			$item = new item();
			$item->load_by_fetch($itens[$i]);

			traduz($item);

			$item->chamada = nl2br($item->chamada);
			$item->descricao = nl2br($item->descricao);
			$item->nome_tag = stringAsTag($item->nome);

			$p->path = PATH_SITE;
			$p->index = INDEX;
			$p->list_item = $item;

			$splash = new splash($item->splash_id);

			if($splash->id){
				$p->list_splash = $splash;
				$p->parseBlock('BLOCK_SPLASH');
			}

			if($item->preco>0&&$this->config->MOSTRA_PRECO_SITE){
				if($this->config->MOSTRA_PRECO_SITE_APENAS_LOGADO=='S'
					&&$this->isLogado()){

					$cadastro = $_SESSION['CADASTRO'];

					if ($cadastro->tipocadastro_id == '3' ){
						if($item->preco_de<>"0.00"){
								$p->parseBlock('BLOCK_PRECO');
								$p->parseBlock('BLOCK_PRECO_DE');
						}
						$p->parseBlock('BLOCK_PRECO');
					}
				}
			}

			if((($i+1)%3)==0){
				$posicao = 3;
				$p->margin = 'margin-right:8px;';
			}

			//$temCor = false;
			// $sql = "SELECT cor.* FROM cor INNER JOIN itemcor ON ( cor.id = itemcor.cor_id AND itemcor.item_id = {$item->id} ) WHERE cor.st_ativo = 'S' ORDER BY cor.nome";
			// foreach(results($sql) as $cor){
				// $p->list_cor = $cor;
				// $p->parseBlock('BLOCK_LIST_COR', true);
				// $temCor = true;
			// }
			// if($temCor){
				// $p->parseBlock('BLOCK_CORES');
			// }

			//$cont_produto_1 .= $p->getContent();

			$t->produto = $p->getContent();
			$t->parseBlock('BLOCK_LIST_ITEM', true);
			unset($item);

			$temAlgum = true;
		}

		//$t->cont_produto_1 = $cont_produto_1;

		if($temAlgum){
			$t->parseBlock('BLOCK_ITENS' );
		}
	}

	// Retorna categorias que tem produtos relacionados
	public function getCategoriasAtivasSite($categoria_id=0, &$categorias){

		$return = array();

		foreach($categorias as $categoria){
			if(intval($categoria->categoria_id)==$categoria_id){
				$return[] = $categoria;
			}
		}

		return $return;
	}

	public function getCategoriasAtivasSiteTodas($st_fixo){

		$return = array();

		// Menu de categorias de produtos
		$sql =
		"
		SELECT
			categoria.id
			,categoria.categoria_id
			,categoria.nome
			,categoria.descricao
		FROM
			categoria
		INNER JOIN itemcategoria ON (
			itemcategoria.categoria_id = categoria.id
		)
		INNER JOIN item ON (
			itemcategoria.item_id = item.id
		AND item.st_ativo = 'S'
		)
		WHERE
			categoria.st_ativo = 'S'
		AND (especial is NULL OR especial='N')
		AND categoria.st_lista_menu = 'S'
		AND categoria.st_fixo = '{$st_fixo}'
		GROUP BY
			categoria.id
			,categoria.nome
		ORDER BY
			categoria.ordem
			,categoria.nome
		";

		$query = query($sql);

		while($fetch=fetch($query)){
			$categoria = new categoria();
			$categoria->load_by_fetch($fetch);
			$return[] = $categoria;
		}


		return $return;
	}


	public function montaMenu(& $t, $selected='', $cat_id=0){
        $t->menu = "<div class='menu_drop dropdown-menu multi-level'>
						<ul class=''>
							<li class='visible-sm visible-xs fechasubmenu'>
								<a href='#'>VOLTAR</a>
							</li>".$this->montaMenuProcessa($cat_id)."
						</ul>
					</div>";

        $t->menumobile = "<div class='dropdown-mobile'> 
								<ul class=''>
									<li class='visible-sm visible-xs fechasubmenu'>
										<a href='#'>VOLTAR</a>
									</li>".$this->montaMenuProcessa($cat_id)."
								</ul>
							</div>";
	}

	public function montaMenuListagem(& $t, $selected){
        $t->menulistagem = "<p class='p_listagem'>PRODUTOS PROMOCIONAIS ORIGINAIS.</p><div class='d_menu_listagem'><ul class=''>".$this->montaMenuProcessa()."</ul></div>";
	}

	private function montaMenuProcessa($categoria_id=0){
		$ret = '';
		$links = [];
		$limit = 0;
		$principais = [];
		$categoria = new categoria();

		$categorias = results(
			"SELECT categoria.* FROM categoria 
			INNER JOIN itemcategoria ON itemcategoria.categoria_id = categoria.id 
			INNER JOIN item ON item.id = itemcategoria.item_id
			WHERE categoria.st_ativo = 'S'
			AND (categoria.categoria_id = '0' OR categoria.categoria_id IS NULL) AND IFNULL(categoria.st_lista_menu,'S') = 'S'
			GROUP BY categoria.nome");

		foreach ($categorias as $tmp) {
			if ($tmp->categoria_id == $categoria_id) {
				$principais[] = $tmp;
			}
		}

		$total_categorias = sizeof($principais);
		$categoria_colunas =  ceil($total_categorias / 4);

		foreach ($principais as $principal) {
			$categoria->load_by_fetch($principal);
			$links[] = "<a href='{$categoria->getLink()}'>$categoria->nome</a>";
		}
		for ($col = 1; $col <= 4; $col++) {
			$ret .= '<li class="cat_cols">'.implode(array_slice($links, $limit, $categoria_colunas)).'</li>';
			$limit += $categoria_colunas;
		}

		return $ret;
	}


    private function montaMenuProcessaMobile($categoria_id=0){

        /*
        <li>
            <a href="#" class="sub_subcategoria possui_sub">Canetas Plásticas</a>
            <div id="sub_sub" class="sub_categorias_generico_sub sub_sub">
                <ul id="sub_produtos_sub">
                    <li class="voltar_sub"><span class="voltar_submenu_sub"> Voltar</span></li>
                        <a href="#">Caneta</a>
                </ul>
            </div>
        </li>
         *
         */

        $ret = '';

        $categorias = results("SELECT * FROM categoria WHERE st_ativo = 'S' AND IFNULL(st_lista_menu,'S') = 'S' ORDER BY ordem,nome");

        $principais = array();
        foreach($categorias as $tmp){
            if($tmp->categoria_id == $categoria_id){
                $principais[] = $tmp;
            }
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
            $ret .= "
            <li>
                <a class='sub_subcategoria ".(sizeof($filhos)>0?"possui_sub":"")."' href='#' xhref='{$categoria->getLink()}'>
                    {$categoria->nome}
                </a>
                ";

            if(sizeof($filhos) > 0){
                $ret .= '
                <div id="sub_sub" class="sub_categorias_generico_sub sub_sub">
                    <ul id="sub_produtos_sub">';

                $ret .= '
                <li class="voltar_sub"><span class="voltar_submenu_sub"> Voltar</span></li>
                ';

                foreach($filhos as $filho){
                    $categoria->load_by_fetch($filho);
                    $ret .= "<a href='{$categoria->getLink()}'>{$categoria->nome}</a>";
                }

                $ret .= '
                    </ul>
                </div>
                ';
            }

            $ret .= "</li>";

        }

        return $ret;
    }

	public function countPinterest(&$t){
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, 'http://pinterestapi.co.uk/rafafarias96/likes');
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 0);
		$json = curl_exec($ch);
		curl_close($ch);

		$count = json_decode($json, true);
		$count = $count['meta']['count'];

		printr($json);
		printr($count);
		die();
		//$t->pinterestcount = $count;
	}

	// Template show
	protected function show(&$t, $menu=true, $selected='', $opts=array()){

		$t->path = PATH_SITE;
		$t->index = INDEX;
		$t->config = $this->config;

		redesSociais($t);

		if($t->exists("seopro")){
			$t->seopro = $this->seopro;
		}

		if($t->exists('slidebanner')){
			$t->slidebanner = new slidebanner();
		}

        if($t->exists('menupaginainstitucional')){
            $t->menupaginainstitucional = modulo_paginainstitucional::widget();
        }

        if($t->exists('carrinho')){
            $t->carrinho = new carrinho();
        }

		if($t->exists('time')){
            $t->time = date("His");
		}

		//Begin Whatsapp
		if(config::get('WHATSAPP') != '' && config::get('WHATSAPP') != "#"){

			$nao_pode_conter = array("(", ")", "+", "-", " ");
			$substitui_por   = array("", "", "", "", "");
			$novo_telefone = str_replace($nao_pode_conter, $substitui_por, config::get('WHATSAPP'));
			$phone = $novo_telefone;
			$message = 'Olá, tudo bem? ';
			// DO NOT EDIT BELOW
			$message = urlencode($message);
			$message = str_replace('+','%20',$message);
			$iphone = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone");
			$android = strpos($_SERVER['HTTP_USER_AGENT'],"Android");
			$palmpre = strpos($_SERVER['HTTP_USER_AGENT'],"webOS");
			$berry = strpos($_SERVER['HTTP_USER_AGENT'],"BlackBerry");
			$ipod = strpos($_SERVER['HTTP_USER_AGENT'],"iPod");
			// check if is a mobile
			if ($iphone || $android || $palmpre || $ipod || $berry == true){
				$t->UrlWhats ="whatsapp://send?phone=55$phone&text=$message";	
				//ORecho 
				"<script>window.location='whatsapp://send?phone='.$phone.'&text='.$message</script>";
			}
			// all others
			else {
				$t->UrlWhats="https://web.whatsapp.com/send?phone=55$phone&text=$message";
				"<script>window.location='https://web.whatsapp.com/send?phone='.$phone.'&text='.$message</script>"
				;
			}
		}
		//End Whatsapp

		// Logado
		if($this->isLogado()){
			$t->cliente = $cliente = $_SESSION['CADASTRO'];
            // $t->vendedor = new cadastro($cliente->cadastro_id);
			$t->parseBlock('BLOCK_CLIENTE_LOGADO');
            $t->parseBlock('BLOCK_CLIENTE_LOGADO_MOBILE');
		}
        else {
			$t->parseBlock('BLOCK_CLIENTE_NAO_LOGADO');
            $t->parseBlock('BLOCK_CLIENTE_NAO_LOGADO_MOBILE');
		}

		$this->montaMenu($t, $selected);
        //$t->contato_rodape = modulo_contato::widget_rodape();

		//$this->countPinterest($t);

		// if($this->isLogado()){
			// $t->logado = $_SESSION['CADASTRO'];
			// $t->parseBlock('BLOCK_LOGADO');
		// }
		// else{
			// $t->parseBlock('BLOCK_NAO_LOGADO');
		// }
		// configuração de idiomas

		if($this->config->HABILITA_BARRA_IDIOMAS=='S'){
			if($this->config->HABILITA_ESPANHOL=='S'){
				$t->parseBlock('BLOCK_IDIOMA_ESPANHOL');
			}
			if($this->config->HABILITA_INGLES=='S'){
				$t->parseBlock('BLOCK_IDIOMA_INGLES');
			}
			$t->parseBlock('BLOCK_IDIOMAS');
		}

		if(array_key_exists('erro', $_SESSION)){
			$t->erro = $_SESSION['erro'];
			$t->parseBlock('BLOCK_MSG_ERRO');
			unset($_SESSION['erro']);
		}

		if(array_key_exists('sucesso', $_SESSION)){
			$t->sucesso = $_SESSION['sucesso'];
			$t->parseBlock('BLOCK_MSG_SUCESSO');
			unset($_SESSION['sucesso']);
		}

		if(array_key_exists('script', $_SESSION)){
			$t->script = $_SESSION['script'];
			unset($_SESSION['script']);
		}

        if(isset($opts['include_js'])){
            $loop = is_array($opts['include_js']) ? $opts['include_js'] : array($opts['include_js']);
            foreach($loop as $include){
                $t->includejs = $include;
                $t->block('BLOCK_INCLUDE_JS');
            }
        }

        if(isset($opts['include_css'])){
            $loop = is_array($opts['include_css']) ? $opts['include_css'] : array($opts['include_css']);
            foreach($loop as $include){
                $t->includecss = $include;
                $t->block('BLOCK_INCLUDE_CSS');
            }
        }

        if(isset($_SESSION['erromodal'])){
            list($titulo,$mensagem) = $_SESSION['erromodal'];
            unset($_SESSION['erromodal']);
            $t->modal_titulo = $titulo;
            $t->modal_mensagem = $mensagem;
            $t->block('BLOCK_MODAL');
        }

        if(BOOTSTRAP_LESS == '1'){
            $t->block('BLOCK_BOOTSTRAP_LESS');
        }
        else {
            $t->block('BLOCK_BOOTSTRAP_MIN');
        }

        if(JS_DEV == '1'){
            $t->block('BLOCK_JS_DEV');
        }
        else {
            $t->block('BLOCK_JS_MIN');
        }

			// EXIBE O ENDERECO 
			if(config::get("LOGRADOURO") != ""){
				$t->parseBlock("BLOCK_LOGRADOURO_FOOTER");
				$t->parseBlock("BLOCK_LOGRADOURO_FOOTER_ICON");
			}
	
			// EXIBE O NUMERO
			if(config::get("NUMERO") != ""){
				$t->parseBlock("BLOCK_NUMERO_FOOTER");
			}
			// EXIBE BAIRRO
			if(config::get("BAIRRO") != ""){
				$t->parseBlock("BLOCK_BAIRRO_FOOTER");
			}
			// EXIBE CIDADE 
			if(config::get("CIDADE") != ""){
				$t->parseBlock("BLOCK_CIDADE_FOOTER");
			}
			// EXIBE ESTADO 
			if(config::get("ESTADO") != ""){
				$t->parseBlock("BLOCK_ESTADO_FOOTER");
			}
			// EXIBE CONTATO
			if(config::get("EMAIL_CONTATO") != ""){
				$t->parseBlock("BLOCK_EMAIL_CONTATO_FOOTER");
				$t->parseBlock("BLOCK_EMAIL_CONTATO_TOP");
				$t->parseBlock("BLOCK_EMAIL_CONTATO_TOP_MOBILE");
			}

			// EXIBE TELEFONE
			if(config::get("TELEFONE") != ""){
				$t->parseBlock("BLOCK_TELEFONE_FOOTER");
				$t->parseBlock("BLOCK_TELEFONE_TOP");
				$t->parseBlock("BLOCK_TELEFONE_TOP_ICON");
				$t->parseBlock("BLOCK_TELEFONE_TOP_MOBILE");
			}
	
			// EXIBE TELEFONE 2
			if(config::get("TELEFONE2") != ""){
				$t->parseBlock("BLOCK_TELEFONE2_TOP");
			}

			// EXIBE WHATSAPP NO TOPO
			if(config::get("WHATSAPP") != ""){
				$t->parseBlock("BLOCK_WHATSAPP_TOP_ICON");
			}
	
			// EXIBE WHATSAPP
			if(config::get("WHATSAPP") != ""){
				$t->parseBlock("BLOCK_WHATSAPP_FOOTER");
			}


		$t->show();
	}

	public function verificaLogado(){
		$out = '';
		if($this->isLogado()){
			$t = new Template('tpl.personalizacao_cordao.html');

			$id = $_REQUEST['id'];
			//$chave  = $_REQUEST['chave'];

			$personalizacaocategoria = new personalizacaocategoria($id);
			//$arr = explode(',',$personalizacao->array_categorias[$indice][$chave]['material']);

			// $t->tipo   = $personalizacao->array_categorias[$indice][$chave]['nome'];
			// $t->indice = $indice;
			// $t->chave  = $chave;
			$t->index  = INDEX;

			$query = query("SELECT * FROM personalizacaocategoriamaterial WHERE personalizacaocategoria_id = {$personalizacaocategoria->id}");

			while($fetch=fetch($query)){
				$material = new material($fetch->material_id);
				$t->material = $material;
				$t->parseBlock('BLOCK_MATERIAL',true);
			}

			$out = $t->getContent();
		}else{
			$t = new Template('tpl.login.html');
			$t->h1 = 'Login';
			$t->index = INDEX;
			//$t->redireciona = $_SESSION['uri_personal'];
			$out = $t->getContent();
		}

		echo $out;
	}

	public function addimagemImpressao(){
		$file = $_FILES['imagem_impressao'];
		$nome = $_REQUEST['nomeimpresso'];
		$arr = explode('.',$file['name']);
		$extensao = $arr[sizeof($arr)-1];
		$filename = $nome.'_'.$_SESSION['CADASTRO']->id.'.'.$extensao;
		move_uploaded_file($file['tmp_name'],"img/upload/{$filename}");

		unset($_REQUEST['nomeimpresso']);
		unset($_FILES['imagem_impressao']);

		echo $filename;

		die();
	}

	//
	public function carregaPersonalizacao(){
		// $indice = $_REQUEST['indice'];
		// $chave  = $_REQUEST['chave'];
		$material_id = $_REQUEST['material'];


		$material = new material($material_id);


		// $personalizacao = new personalizacao();
		// $arr = $personalizacao->array_material[$material];

		$out = array();

		$html_1 = '';
		$html_2 = '';

	// Largura
		$html_1 .= '<h3>Largura</h3>';
		foreach(explode(';',$material->largura) as $key=>$value){
			if($value!=''){
				$html_1 .= tag('span class="itens_personalizacao"',
								'<input type="radio" value="" class="largura" name="largura" id="largura_'.$key.'"><label for="largura_'.$key.'">'.$value.'</label><br />'
							);
			}
		}
		$html_1 .= $material->largura_checkbox=='S'?'<label>Outro valor:<br /><input type="text" class="largura" style="border:1px solid #ccc; line-height:18px; xmargin-left:12px;" value="" name="largura_checkbox" id="largura_'.($key+1).'"  onkeyup="javascript: Especial(this);" /></label><br /><br />':'<br /><br />';
		$html_2 .= '<li><p>Largura</p><input type="hidden" class="valida" value="" name="cordao[largura]" id="input_largura" /><span style="color:#126288; font-weight:bold;" id="value_largura"></span></li>';

	// Acabamento
		$html_1 .= '<h3>Acabamento</h3>';
		foreach(explode(';',$material->acabamento) as $key=>$value){
			if($value!=''){
				$html_1 .= tag('span class="itens_personalizacao"',
								'<input type="radio" value="" class="acabamento" name="acabamento" id="acabamento_'.$key.'"><label for="acabamento_'.$key.'">'.$value.'</label><br />'
							);
			}
		}
		$html_1 .= $material->acabamento_checkbox=='S'?'<label>Outro valor:<br /><input type="text" class="acabamento" style="border:1px solid #ccc; line-height:18px; padding:0;xmargin-left:12px;" value="" name="acabamento_checkbox" id="acabamento_'.($key+1).'"  onkeyup="javascript: Especial(this);" /></label><br /><br />':'<br /><br />';
		$html_2 .= '<li><p>Acabamento</p><input type="hidden" class="valida" value="" name="cordao[acabamento]" id="input_acabamento" /><span style="color:#126288; font-weight:bold;" id="value_acabamento"></span></li>';

	// Impressao
		$html_1 .= '<h3>Impress&atilde;o</h3>';
		foreach(explode(';',$material->impressao) as $key=>$value){
			if($value!=''){
				$html_1 .= tag('span class="itens_personalizacao"',
								'<input type="radio" value="" class="impressao" name="impressao" id="impressao_'.$key.'"><label for="impressao_'.$key.'">'.$value.'</label><br />'
							);
			}
		}
		$html_1 .= $material->impressao_checkbox=='S'?'<label>Outro valor:<br /><input type="text" class="impressao" style="border:1px solid #ccc; line-height:18px; padding:0;xmargin-left:12px;" value="" name="impressao_checkbox" id="impressao_'.($key+1).'"  onkeyup="javascript: Especial(this);" /></label><br /><br />':'<br /><br />';
		$html_2 .= '<li><p>Impress&atilde;o</p><input type="hidden" class="valida" value="" name="cordao[impressao]" id="input_impressao" /><span style="color:#126288; font-weight:bold;" id="value_impressao"></span></li>';

	// Cor Impresso Frente/Verso
		$html_1 .= '<div style="border:1px solid #ddd;"><h4>Cores de Impress&atilde;o</h4>';
		// Frente
		$html_1 .= '<h3>Frente</h3>';
		foreach(explode(';',$material->corimpressofrente) as $key=>$value){
			if($value!=''){
				$html_1 .= tag('span class="itens_personalizacao"',
								'<input type="radio" value="" class="corimpressofrente" name="corimpressofrente" id="corimpressofrente_'.$key.'"><label for="corimpressofrente_'.$key.'">'.$value.'</label><br />'
							);
			}
		}
		$img_corfrente = "corimpressofrente_".($key+1)."";
		$html_1 .= $material->corimpressofrente_checkbox=='S'?
			'<label>Outro valor:<br />
					<input type="hidden" id="'.$img_corfrente.'" value="" class="corimpressofrente" />

					<form id="imagem_frente" method="POST" enctype="multipart/form-data">
						<input type="hidden"  value="corimpressofrente" name="nomeimpresso" />
						<span id="imagem_corimpressofrente">
							<input type="file" name="imagem_impressao" id="corimpressofrente_img" />
						</span>
					</form>
				</label><br /><br />':'<br /><br />';
		// Verso
		$html_1 .= '<h3>Verso</h3>';
		foreach(explode(';',$material->corimpressoverso) as $key=>$value){
			if($value!=''){
				$html_1 .= tag('span class="itens_personalizacao"',
								'<input type="radio" value="" class="corimpressoverso" name="corimpressoverso" id="corimpressoverso_'.$key.'"><label for="corimpressoverso_'.$key.'">'.$value.'</label><br />'
							);
			}
		}
		$img_corverso = "corimpressoverso_".($key+1)."";
		$html_1 .= $material->corimpressoverso_checkbox=='S'?
				'<label>Outro valor:<br />
					<input type="hidden" id="'.$img_corverso.'" value="" class="corimpressoverso" />

					<form id="imagem_verso" method="POST" enctype="multipart/form-data">
						<input type="hidden"  value="corimpressoverso" name="nomeimpresso" />
						<span id="imagem_corimpressoverso">
							<input type="file" name="imagem_impressao" id="corimpressoverso_img" />
						</span>
					</form>
				</label><br /><br />':'<br /><br />';

		$html_1 .= '</div>';
		$html_2 .= '<li><p>Cor de Impress&atilde;o - Frente</p><input type="hidden" class="valida" value="" name="cordao[corimpressofrente]" id="input_corimpressofrente" /><span style="color:#126288; font-weight:bold;" id="value_corimpressofrente"></span></li>';
		$html_2 .= '<li><p>Cor de Impress&atilde;o - Verso</p><input type="hidden" class="valida" value="" name="cordao[corimpressoverso]" id="input_corimpressoverso" /><span style="color:#126288; font-weight:bold;" id="value_corimpressoverso"></span></li>';

	// Quantidade
		$html_1 .= '<h3>Quantidade</h3>';
		foreach(explode(';',$material->quantidade) as $key=>$value){
			if($value!=''){
				$html_1 .= tag('span class="itens_personalizacao"',
								'<input type="radio" value="" class="quantidade" name="quantidade" id="quantidade_'.$key.'"><label for="quantidade_'.$key.'">'.$value.'</label><br />'
							);
			}
		}
		$html_1 .= $material->quantidade_checkbox=='S'?'<label>Outro valor:<br /><input type="text" class="quantidade" style="border:1px solid #ccc; line-height:18px; padding:0;xmargin-left:12px;" value="" name="quantidade_checkbox" id="quantidade_'.($key+1).'"  onkeyup="javascript: Especial(this);" /></label><br /><br />':'<br /><br />';
		$html_2 .= '<li><p>Quantidade</p><input type="hidden" class="valida" value="" name="cordao[quantidade]" id="input_quantidade" /><span style="color:#126288; font-weight:bold;" id="value_quantidade"></span></li>';



		// foreach($arr['personalizacao'] as $key=>$value){
			// if(isset($value[1])){
				// $html_1 .= '<h3>'.$value[0].'</h3>';
			// }

			// $cont = 0;
			// foreach($value as $_key=>$_value){
				// if($_key>0){
					// $html_1 .= tag('span class="itens_personalizacao"',
							// $_value=='especial'?'<input type="text" class="'.$key.'" style="border:1px solid #ccc; line-height:18px; margin-left:12px;" value="" name="'.$key.'_'.$material.'" id="'.$key.'_'.$_key.'"  onkeyup="javascript: Especial(this);" /><br />':
							// '<input type="radio" value="" class="'.$key.'" name="'.$key.'_'.$material.'" id="'.$key.'_'.$_key.'"><label for="'.$key.'_'.$_key.'">'.$_value.'</label><br />'
							// );
				// }
			// }
			// $html_1 .= '<br />';
			// if(isset($value[1])){
				// $html_2 .= '<li><p>'.$value[0].'</p><input type="hidden" class="valida" value="" name="cordao['.$key.']" id="input_'.$key.'" /><span style="color:#126288; font-weight:bold;" id="value_'.$key.'"></span></li>';
			// }
		// }


		$html_1 .= '
			<script>
					function ajaxImagem(){
						$("#corimpressoverso_img").bind("change",function(){
							data = (new Date()).getTime();
							$( "#imagem_verso").ajaxSubmit({
								url : "'.INDEX.'addimagemImpressao/?data="+data,
								success : function(data){
									document.getElementById("'.$img_corverso.'").value = data;
									obj = document.getElementById("'.$img_corverso.'");
									Especial(obj);
								}
							});
						});


						$("#corimpressofrente_img").bind("change",function(){
							data = (new Date()).getTime();
							$( "#imagem_frente").ajaxSubmit({
								url : "'.INDEX.'addimagemImpressao/?data="+data,
								success : function(data){
									document.getElementById("'.$img_corfrente.'").value = data;
									obj = document.getElementById("'.$img_corfrente.'");
									Especial(obj);
								}
							});
						});
					}
					ajaxImagem();
				</script>
		';


		$out[0] = $html_1;
		$out[1] = $html_2;

		echo json_encode($out);
		die();
	}

	// Trabalhe Conosco
	public function trabalhe(){
		$t = new TemplateSite('tpl.pag-trabalhe-conosco.html');

		$opcoes_estado = tag("option value='' ", "--");
		foreach(optionsEstado() as $uf=>$nome){
			$opcoes_estado .= tag("option value='{$uf}' ".(@$_REQUEST['representante']['estado']==$uf?'selected':'')."", $nome);
		}

		$t->opcoes_estado = $opcoes_estado;

		if(request('enviar_funcionario')){

			$funcionario = (object) $_REQUEST['funcionario'] ;

			$erros = array();
			$js = array();

			if(!is_set(@$funcionario->nome))
			{
				$erros['nome'] = tag('p', 'Digite seu nome');
				$js[] = js("lightObj(document.forms.formFuncionario.elements['funcionario[nome]'])");
			}

			if(!is_set(@$funcionario->telefone))
			{
				$erros['telefone'] = tag('p', 'Digite seu telefone corretamente');
				$js[] = js("lightObj(document.forms.formFuncionario.elements['funcionario[telefone]'])");
			}

			if(!is_email(@$funcionario->email))
			{
				$erros['email'] = tag('p', 'Digite seu e-mail corretamente');
				$js[] = js("lightObj(document.forms.formFuncionario.elements['funcionario[email]'])");
			}

			$file_funcionario = @$_FILES['file_funcionario'] ;

			if(!$file_funcionario['tmp_name'])
			{
				$erros['file_funcionario'] = tag('p', 'Selecione o seu arquivo de curriculo para upload');
				$js[] = js("lightObj(document.forms.formFuncionario.elements['file_funcionario'])");
			}

			if(@$file_funcionario['tmp_name'] && (!is_file_doc_by_filename($file_funcionario['name']) ))
			{
				$erros['file_funcionario'] = tag('p', 'O arquivo precisa estar em formato .doc ou .docx');
				$js[] = js("lightObj(document.forms.formFuncionario.elements['file_funcionario'])");
			}

			if(sizeof($erros)==0)
			{

				$extensao = explode('.', $file_funcionario['name']);
				$extensao = $extensao[sizeof($extensao)-1];

				$unique_name = time().'.'.$extensao;;

				$funcionario->file_unique_name = $unique_name;
				$funcionario->file_friendly_name = $file_funcionario['name'];

				move_uploaded_file($file_funcionario['tmp_name'], 'curriculos/'.$unique_name);

				$tEmail = new Template('tpl.email-trabalhe-funcionario.html');

				$tEmail->funcionario = $funcionario;
				$tEmail->config = $this->config;

				$objEmail = new email();

				$objEmail->addHtml($tEmail->getContent());
				$objEmail->addTo($this->config->get('EMAIL_TRABALHE_FUNCIONARIO'), $this->config->EMPRESA);

				$objEmail->addBcc($this->config->EMAIL_ADMINISTRACAO, $this->config->EMPRESA);
				$objEmail->send("Solicitação Trabalhe Conosco - Funcionario - {$funcionario->nome}");

				$_SESSION['sucesso'] = tag('p', 'Seu currículo foi enviado com sucesso.');

			}
			else
			{
				$_SESSION['erro'] = join('', $erros) . '<br />';
				$_SESSION['script'] = join('', $js);
				$t->funcionario = $funcionario;
			}
		}

		if(request('enviar_representante')){

			$representante = (object) $_REQUEST['representante'] ;

			$erros = array();
			$js = array();

			if(!is_set(@$representante->nome))
			{
				$erros['nome'] = tag('p', 'Digite seu nome');
				$js[] = js("lightObj(document.forms.formRepresentante.elements['representante[nome]'])");
			}

			if(!is_set(@$representante->telefone))
			{
				$erros['telefone'] = tag('p', 'Digite seu telefone corretamente');
				$js[] = js("lightObj(document.forms.formRepresentante.elements['representante[telefone]'])");
			}

			if(!is_set(@$representante->empresa))
			{
				$erros['empresa'] = tag('p', 'Digite sua empresa corretamente');
				$js[] = js("lightObj(document.forms.formRepresentante.elements['representante[empresa]'])");
			}

			if(!is_email(@$representante->email))
			{
				$erros['email'] = tag('p', 'Digite seu e-mail corretamente');
				$js[] = js("lightObj(document.forms.formRepresentante.elements['representante[email]'])");
			}

			if(!is_set(@$representante->estado))
			{
				$erros['estado'] = tag('p', 'Selecione seu estado corretamente');
				$js[] = js("lightObj(document.forms.formRepresentante.elements['representante[estado]'])");
			}

			$file_representante = @$_FILES['file_representante'] ;

			if(!$file_representante['tmp_name'])
			{
				$erros['file_representante'] = tag('p', 'Selecione a sua ficha cadastral para upload');
				$js[] = js("lightObj(document.forms.formRepresentante.elements['file_representante'])");
			}

			if(@$file_representante['tmp_name'] && (!is_file_doc_by_filename($file_representante['name']) ))
			{
				$erros['file_representante'] = tag('p', 'O arquivo precisa estar em formato .doc ou .docx');
				$js[] = js("lightObj(document.forms.formRepresentante.elements['file_representante'])");
			}

			if(sizeof($erros)==0)
			{

				$extensao = explode('.', $file_representante['name']);
				$extensao = $extensao[sizeof($extensao)-1];

				$unique_name = time().'.'.$extensao;;

				$representante->file_unique_name = $unique_name;
				$representante->file_friendly_name = $file_representante['name'];

				move_uploaded_file($file_representante['tmp_name'], 'curriculos/'.$unique_name);

				$tEmail = new Template('tpl.email-trabalhe-representante.html');

				$tEmail->representante = $representante;
				$tEmail->config = $this->config;

				$objEmail = new email();
				$objEmail->addHtml($tEmail->getContent());
				$objEmail->addTo($this->config->get('EMAIL_TRABALHE_REPRESENTANTE'), $this->config->EMPRESA);
				$objEmail->addBcc($this->config->EMAIL_ADMINISTRACAO, $this->config->EMPRESA);
				$objEmail->send("Solicitação Trabalhe Conosco - Representante - {$representante->nome}");

				$_SESSION['sucesso'] = tag('p', 'Seu cadastro foi efetuado com sucesso.');

			}
			else
			{
				$_SESSION['erro'] = join('', $erros) . '<br />';
				$_SESSION['script'] = join('', $js);
				$t->representante = $representante;
			}
		}

		$t->h1 = 'Trabalhe conosco';
		$this->show($t);
	}

	// Catalogo
	public function catalogo(){
		$t = new TemplateSite('tpl.catalogo.html');
		$this->show($t);
	}

	public function pag_empresa(){

		$t = new TemplateSite("tpl.empresa.html");

		$pagina = new pagina(array('chave'=>'Empresa'));
		if(!$pagina->id){
		}
		$t->empresa_conteudo = $pagina->conteudo;

		$t->h1 = 'Empresa';

		/*$pagina = new pagina(array('chave'=>'Miss-o'));
		if(!$pagina->id){}
		$t->missao_conteudo = $pagina->conteudo;

		$pagina = new pagina(array('chave'=>'Clientes'));
		if(!$pagina->id){}
		$t->cliente_conteudo = $pagina->conteudo;*/

		$this->show($t);
	}


	//Pagina produtos especiais
	public function produtosespeciais($cat_id){

		$t = new TemplateSite('tpl.pag-produtoespeciais.html');

		$t->h1 = "Produtos especiais";

		//Parse Categorias
		// $categorias = $this->getCategoriasAtivasSiteTodas('S');

		// if(!isset($categorias)){
			// $cat_id = $cat_id==0?$categorias[0]->id:$cat_id;

			// foreach($categorias as $cat){

				// $cat->selected = $cat_id==$cat->id?'selected':'';

				// if($cat_id == $cat->id){
					// $t->descricao = $cat->descricao;
				// }

				// $t->categoria = $cat;
				// $t->parseBlock('BLOCK_LIST_CAT', true);
			// }

		// }

		// //Banner
		$query = 'SELECT * FROM slidebanner WHERE tipo = "categoria"';
		$result = results($query);
		$t->banner = $result[0]->imagem;

		// //Categorias selecionada
		// $itemCat = new categoria(intval($cat_id));

		// //Parse PRODUTOS
		// $sql = "SELECT
			// DISTINCT item.* FROM
			// item
		// INNER JOIN itemcategoria ON (
				// item.id = itemcategoria.item_id
			// ".($itemCat->id>0?"AND itemcategoria.categoria_id = {$itemCat->id}":"")."
		// )
		// INNER JOIN categoria ON (
			// itemcategoria.categoria_id = categoria.id
		// AND categoria.st_ativo = 'S'
		// AND categoria.st_fixo = 'S'
		// )
		// WHERE
			// item.st_ativo = 'S'
		// ";

		// $query = query($sql);
		// $rows = rows($query);

		// if($rows==0) {
			// $_SESSION['erro'] = tag('p','Nenhum produto encontrado');
		// }
		// else{

			// $itens = array();

			// while($fetch=fetch($query)){
				// $itens[] = $fetch;
			// }

			// for($i=0,$j=1,$n=sizeof($itens);$i<$n;$i++){

				// $item = new item();
				// $item->load_by_fetch($itens[$i]);

				// $t->style = $j%3==0?'margin-right:0':'';
				// $j++;

				// $t->item = $item;
				// $t->parseBlock('BLOCK_PRODUTO_ESPECIAL', true);
			// }
		// }

		$t->parseBlock('BLOCK_PRODUTOS_ESPECIAIS');

		$this->show($t, false);

	}

	//Pagina materia prima
	public function materiaprima(){

		$t = new TemplateSite('tpl.pag-materiaprima.html');

		$t->h1 = "Matéria Prima";

		$this->show($t);
	}

	// Pagina padrao Pagina
	public function pagina($chave){

		$pagina = new pagina(array('chave'=>$chave));
		if(!$pagina->id){
			die();
		}

		traduz($pagina);
		switch($pagina->st_tipopagina){
			case 'THTML':

				if(file_exists('tpl.pag-'.$pagina->chave.'.html')){
					$t = new TemplateSite('tpl.pag-'.$pagina->chave.'.html');
				}
				else {
					$t = new TemplateSite('tpl.pagina.html');
					$pai = $pagina;

					if($pagina->pagina_id>0){
						$pai = new pagina($pagina->pagina_id);
						//$t->pai = $pai;
						//$t->parseBlock('BLOCK_PAI');
					}

					$temSubPagina = false;
					foreach(results("SELECT * FROM pagina WHERE pagina_id = {$pai->id} ORDER BY ordem") as $result){
						traduz($result);
						$result->selected = ($pagina->chave==$result->chave?"selected":"");
						if(!$temSubPagina&&$pai->id==$pagina->id&&$pai->conteudo==''){
						//	$t->pai = $pagina;
							//$t->parseBlock('BLOCK_PAI');
							$pagina = $result;

							$result->selected = "selected";
						}
						//$t->sub_pagina_item = $result;
						//$t->parseBlock('BLOCK_SUB_PAGINA_ITEM', true);
						$temSubPagina = true;
					}
					if($temSubPagina){
						//$t->parseBlock('BLOCK_SUB_PAGINA');
					}
				}

				//$t->banner_tema = 'img/banner-'.$pagina->chave.'.jpg';
				$t->h1 = $pagina->nome;
				$t->pagina = $pagina;
				if ($pai && $pai ->nome == 'Empresa'){
					$t->selected = 'selected';
				}


				$this->show($t);

			break;

			case 'TPHP':
				eval($pagina->conteudo);
			break;
		}
	}

	public function pagina_institucional(){
		$t= new TemplateSite('tpl.pag_institucional.html');

		$institucional = new institucional(array('nome'=>'institucional'));

		$t->institucional = $institucional;

		$this->imagemBannerShow($t,'empresa',0);

		$this->show($t);
	}

	public function representante(){
		$t = new TemplateSite('tpl.pag-onde_comprar.html');

		$t->h1 = 'Onde Comprar';
		$this->show($t);
	}

	public function ajax_ondecomprar_select_estado(){

		$query = query($sql=
			"
			SELECT
				DISTINCT
				mapa_uf
				,count(cadastro.id) qtd
			FROM
				cadastro
			WHERE
				tipocadastro_id = ".tipocadastro::getId('LOJA')."
			AND cadastro.st_ativo = 'S'
			AND cadastro.st_aparece_mapa = 'S'
			AND cadastro.mapa_uf <> ''
			GROUP BY
				cadastro.mapa_uf
			ORDER BY 1
			");

		$opts = optionsEstado();

		echo "<select name='uf' id='uf' class=' select'>";
		echo tag("option value='0'", 'Selecione o estado');
		while($fetch=fetch($query)){
			$fetch->mapa_uf = strtoupper($fetch->mapa_uf);
			echo tag("option value='{$fetch->mapa_uf}'", @$opts[$fetch->mapa_uf]);
		}

		echo "</select>";
	}

	public function ajax_ondecomprar_select_cidade(){

		$uf = limpa(request('uf'));

		$query = query($sql=
			"
			SELECT
				DISTINCT
				mapa_cidade
				,count(cadastro.id) qtd
			FROM
				cadastro
			WHERE
				tipocadastro_id = ".tipocadastro::getId('LOJA')."
			AND cadastro.st_ativo = 'S'
			AND cadastro.st_aparece_mapa = 'S'
			AND cadastro.mapa_uf = '{$uf}'
			GROUP BY
				cadastro.mapa_cidade
			");

		$opts = optionsEstado();

		print "<select name='cidade' id='cidade' class='select'>";
		echo tag("option value='0'", 'Cidade');
		//print tag("option value='0'", '');
		while($fetch=fetch($query)){
			$fetch->mapa_uf = strtoupper($fetch->mapa_uf);
			print tag("option value='{$fetch->mapa_cidade}'", $fetch->mapa_cidade);
		}

		print "</select>";

	}

	public function ajax_ondecomprar_loja(){

		$uf = limpa(strtoupper(str_replace("/",'',request('uf'))));

		$cidade = limpa(request('cidade'));

		$optionsEstado = optionsEstado();

		$sql = "SELECT
					cadastro.*
				FROM
					cadastro
				WHERE
					cadastro.tipocadastro_id = ".tipocadastro::getId("LOJA")."
				AND	cadastro.st_ativo = 'S'
				AND cadastro.st_aparece_mapa = 'S'
				AND cadastro.mapa_uf = '{$uf}'
				".($cidade!=''?"AND cadastro.mapa_cidade = '{$cidade}'":"")."
				ORDER BY
					cadastro.nome" ;

		$query = query($sql);

		$t = new Template('tpl.pag-onde-comprar-pesquisa.html');

		$qtdLojas = 0;

		$paginas = 1;
		$porpagina = 4;
		$i = 0;
		$x = 1;

		while($fetch=fetch($query)){

			$i++;

			$t->list_mapa = $fetch;

			if($fetch->mapa_imagem!=''
				&&file_exists($fetch->mapa_imagem)) {
				$t->path = PATH_SITE;
				$t->parseBlock('BLOCK_LIST_MAPA_IMAGEM');
			}

			if($fetch->mapa_website!=''){
				$t->parseBlock('BLOCK_WEB_SITE');
			}

			if(($x%3)==0){
				//$t->parseBlock('BLOCK_CLEAR');
			}

			$t->parseBlock('BLOCK_LIST_MAPA', true);
			$qtdLojas ++;
			$x ++;
		}

		if($qtdLojas==0){
			$t->parseBlock('BLOCK_NAO_TEM');
		}

		print $t->getContent();

	}

	public function pagina_contato($pagina){

		$t = new TemplateSite('tpl.pag-contato.html');
		$t->h1 = $pagina->nome;
		$t->pagina = $pagina;

		$contato = new stdClass();

		$contato->nome = '';
		$contato->email = '';
		$contato->mensagem = '';

		if(array_key_exists('contato',$_REQUEST) && is_array($_REQUEST['contato'])){
			foreach ($_REQUEST['contato'] as $key => $value){
				$contato->$key = $value;
			}
		}

		$assuntos = array();

		$email_destino = '' ;

		if($assunto_opcoes = @$this->config->ASSUNTO_OPCOES)
		{
			$options = @json_decode($assunto_opcoes );
			if(is_array($options)) {
				foreach ($options as $objOption){
					$objOption->text = rawurldecode($objOption->text);
					$t->assunto_opcao = $objOption;
					$t->parseBlock('BLOCK_ASSUNTO_OPCAO', true);
					$assuntos[$objOption->value] = $objOption->text;
				}
			}
			$t->parseBlock('BLOCK_ASSUNTO_OPCOES');
		}
		else
		{
			$t->parseBlock('BLOCK_ASSUNTO_TXT');
		}
		if(request('enviar')){

			$erros = array();
			if(!token_ok()){die();}

			if($contato->nome=='' || $contato->nome=='Nome'){
				$erros[] = '<p>Digite seu nome</p>' ;
			}
			/*if($contato->empresa=='' || $contato->empresa =='Empresa'){
				$erros[] = '<p>Digite sua empresa</p>' ;
			}*/

			if(!is_email($contato->email)){
				$erros[] = '<p>Digite seu e-mail</p>' ;
			}
			/*if($contato->fone_com=='' || $contato->fone_com =='Telefone'){
				$erros[] = '<p>Digite seu telefone</p>' ;
			}*/

			if($contato->mensagem=='' || $contato->mensagem=='Mensagem') {
				$erros[] = '<p>Digite sua mensagem</p>' ;
			}

			if($contato->assunto==''){
				$erros[] = '<p>Selecione um assunto</p>' ;
			}

			if(sizeof($erros)==0){

				$contato->mensagem_br = nl2br($contato->mensagem);

				$email = new email();

				$destinatario = array_search($contato->assunto, $assuntos);

				if(sizeof($assuntos)>0){
					$email->addTo($destinatario, $this->config->EMPRESA);
				}
				else {
					$email->addTo($this->config->EMAIL_CONTATO, $contato->assunto);
				}

				$email->addReplyTo($contato->email, $contato->nome);

				if(request('cc')=='sim'){
					$email->addCc($contato->email, $contato->nome);
				}

				$tEmailContato = new Template('tpl.email-contato.html');
				$tEmailContato->contato = $contato;

				$email->addBcc($this->config->EMAIL_ADMINISTRATIVO, $this->config->EMPRESA);

				if(request('assunto_txt')){
					$tEmailContato->assunto_txt = request('assunto_txt');
					$email->addHtml($tEmailContato->getContent());
					$email->send("Contato pelo site ".request('assunto_txt')." - {$contato->nome}");
				}
				else {
					$email->addHtml($tEmailContato->getContent());
					$email->send("{$contato->assunto} - {$contato->nome}");
				}

				unset($email);
				$t->sucesso = tag('p', "A ".config::get("EMPRESA")." agradece seu contato. Em breve retornaremos.");
				$t->parseBlock('BLOCK_MSG_SUCESSO');
			}
			else {
				$t->erro = join('', $erros).'<br />';
				$t->parseBlock('BLOCK_MSG_ERRO');
			}
		}
		$t->contato = $contato;

		$this->imagemBannerShow($t,'contato',0);

		$this->show($t);
	}

	public function enviaContatoAjax(){
		$assuntos = array();

		$email_destino = '' ;

		if($assunto_opcoes = @$this->config->ASSUNTO_OPCOES)
		{
			$options = @json_decode($assunto_opcoes );
			if(is_array($options)) {
				foreach ($options as $objOption){
					$objOption->text = rawurldecode($objOption->text);
					$assuntos[$objOption->value] = $objOption->text;
				}
			}
		}


		$contato = new stdClass();

		$contato->nome = '';
		$contato->email = '';
		$contato->mensagem = '';

		if(array_key_exists('contato',$_REQUEST) && is_array($_REQUEST['contato'])){
			foreach ($_REQUEST['contato'] as $key => $value){
				$contato->$key = $value;
			}
		}

		$contato->mensagem_br = nl2br($contato->mensagem);
		$email = new email();
		$destinatario = array_search($contato->assunto, $assuntos);
		if(sizeof($assuntos)>0){
			$email->addTo($destinatario, $this->config->EMPRESA);
		}
		else {
			$email->addTo($this->config->EMAIL_CONTATO, $contato->assunto);
		}

		$email->addReplyTo($contato->email, $contato->nome);

		if(request('cc')=='sim'){
			$email->addCc($contato->email, $contato->nome);
		}

		$tEmailContato = new Template('tpl.email-contato.html');
		$tEmailContato->contato = $contato;

		$email->addBcc($this->config->EMAIL_ADMINISTRATIVO, $this->config->EMPRESA);


		$email->addHtml($tEmailContato->getContent());
		$email->send("{$contato->assunto} - {$contato->nome}");


		echo "Mensagem enviada com sucesso.";
		// unset($email);
		// $t->sucesso = tag('p', "A ".config::get("EMPRESA")." agradece seu contato. Em breve retornaremos.");
		// $t->parseBlock('BLOCK_MSG_SUCESSO');
	}


	// briefing
	public function pagina_briefing($pagina){

		$t = new TemplateSite('tpl.pag-briefing.html');
		$item = new item(intval(request('item_id')));

		if($item->id){
			$t->item_ref = $item;
			$t->parseBlock('BLOCK_ITEM_REFERENCIA');
		}

		$t->pagina = $pagina;
		$t->h1 = $pagina->nome;

		$briefing = new briefing();

		if(request('enviar')){

			if(!token_ok())die();

			if(array_key_exists('briefing',$_REQUEST)){
					$briefing->set_by_array($_REQUEST['briefing']);
			}
			if($briefing->validaDados($erros)){

				if($briefing->salva()){

					$tEmail = new Template('tpl.email-pedido-briefing.html');

					$tEmail->config = $this->config;
					$tEmail->briefing = $briefing;
					$tEmail->item_ref = $item;

					if($item->id){
						$tEmail->item_ref = $item;
						$tEmail->parseBlock('BLOCK_ITEM_REFERENCIA');
					}

					$e = new email();
					$e->addTo($this->config->EMAIL_BRIEFING, $this->config->EMPRESA);
					$e->addReplyTo($briefing->email, $briefing->nome);
					$e->addHtml($tEmail->getContent());
					$e->addBcc($this->config->EMAIL_ADMINISTRACAO, $this->config->EMPRESA);
					$e->send("Solicitação briefing - {$briefing->id}");

					$_SESSION['sucesso'] = tag('p', 'Seu briefing foi enviado com sucesso. Em breve entraremos em contato.');

				}
				else {
					$_SESSION['erro'] = tag('p', 'Falha ao salvar os dados do seu briefing');
				}
			}
			else {
				$js = '';
				$str_erro = '';
				$msg = new Msg();
				$_SESSION['erro'] ="";
				foreach(is_array(@$erros)?@$erros:array() as $key => $erro){

					$js .= js("document.forms.formBriefing.elements['briefing[{$key}]'].style.backgroundColor = 'yellow'");
					$str_erro .= tag('p',$erro);
					$key_display = "{$key}_display";

					$msg->$key = "{$erro}";
					$msg->$key_display = "display:inline";

					//$t->msg = $msg;
					$_SESSION['erro'] .= tag('p', $erros[$key]);
				}

				$_SESSION['script'] = $js;
				$_SESSION['erro'] = $str_erro;
			}
		}

		$t->briefing = $briefing;
		//$t->banner_tema = 'img/banner-briefing.jpg';
		$this->show($t);
	}

	public function pagina_produtos($pagina){
		$this->prods(null,null,null,null);
	}

	// TODO: Adaptar de maneira generica
	public function buscar($busca, $item_id, $item_nome_tag){

		//$busca = request('busca');
		if(!$busca){
			$busca = urldecode(request('busca'));
		}
		else {
			$busca = urldecode($busca);
		}

		$like = limpa("%".str_replace(' ','%',$busca).'%');
		$t = new TemplateSite('tpl.produtos.html');

		// VERIFICA SE NAO ESTA VENDO UM DETALHE
		if($item_id&&$item_nome_tag){
			$this->detalhes($item_id, 0);
			return;
		}

        $opts = array();
        $opts['busca'] = $busca;
        $opts['apenas_principal'] = true;
		$produtos = get_produtos($opts, $vars);
		


		//printr($produtos);

        if(sizeof($produtos)==1){
            $item = $produtos[0];
            header('Location: '.$item->getLink());
        }

        if(sizeof($produtos)==0){
            $_SESSION['erromodal'] = array('Ops...','Não encontramos nenhum, resultado para sua busca por '.strip_tags($busca).', aproveite e conheça toda nossa linha de produtos');
            return $this->index();
        }

        $t->h1 = 'BUSCA';
        $t->busca = "Resultados para busca por '{$busca}'";
        $t->parseBlock('BLOCK_TITULO_BUSCA');

        $i = 0;
        $n = sizeof($produtos);

        $p = new Template('tpl.part-produto.html');
        while($i < $n){

            $prod1 = @$produtos[$i++];
            $prod2 = @$produtos[$i++];
            $prod3 = @$produtos[$i++];
            $prod4 = @$produtos[$i++];

            $has = false;

            for($x = 1; $x <= 3; $x++){

                $prod = 'prod'.$x;
                $item = $$prod;

                if(!$item){
                    break;
                }

                // $t->item = $has = $item;
                $has = true;
                $t->produto = tpl_part_produto($item, $p);

                $t->block('BLOCK_PRODUTOS');
                $t->block('BLOCK_COL');

            }

            if($has){
                $t->block('BLOCK_ROW');
            }
        }

		$t->token = session_id();
		$this->show($t, true);
	}

	// Processa FILTRO DE CORES
	private function processaFiltro(&$t, &$sql){

		$sqlWhere = "";

		// Cores
		$sqlFiltro =
			"
			SELECT
				DISTINCT cor.*
			FROM
				cor
			INNER JOIN itemcor ON (
				itemcor.cor_id = cor.id
			)
			INNER JOIN ($sql) tmp_item ON (
				tmp_item.id = itemcor.item_id
			)
			";

		$query = query($sqlFiltro);
		$cor_id = array();
		$temCor = false;

		while($fetch=fetch($query)){
			traduz($fetch);
			$t->list_cor = $fetch;
			$t->checked = in_array($fetch->id, is_array(request('cor_id'))?request('cor_id'):array())?'checked':'';
			if($t->checked=='checked'){
				$cor_id[] = $fetch->id;
			}
			$t->parseBlock('BLOCK_FILTRO_COR', true);
			$temCor = true;
		}
		if(sizeof($cor_id)>0){

			$sqlWhere .= " AND item.id IN (
								SELECT
									itemcor.item_id
								FROM
									itemcor
								WHERE
									itemcor.cor_id IN (" .join(',', $cor_id). ")
							)";

		}
		if($temCor){
			$t->parseBlock('BLOCK_FILTRO_CORES');
		}

		// Gravacoes
		$sqlFiltro =
			"
			SELECT
				SQL_CACHE
				DISTINCT caracvalor.*
			FROM
				caracvalor
			INNER JOIN itemcarac ON (
				itemcarac.carac_id = 2
			AND itemcarac.caracvalor_id = caracvalor.id
			)
			INNER JOIN ($sql) tmp_item ON (
				tmp_item.id = itemcarac.item_id
			)
			WHERE
				caracvalor.carac_id = 2
			";

		$query = query($sqlFiltro);
		$gravacao_id = array();
		$temGravacao = false;
		while($fetch=fetch($query)){
			traduz($fetch);
			$t->list_gravacao = $fetch;
			$t->checked = in_array($fetch->id, is_array(request('gravacao_id'))?request('gravacao_id'):array())?'checked':'';
			if($t->checked=='checked'){
				$gravacao_id[] = $fetch->id;
			}
			$t->parseBlock('BLOCK_FILTRO_GRAVACAO', true);
			$temGravacao = true;
		}
		if(sizeof($gravacao_id)>0){
			$sqlWhere .=
					"AND item.id IN (
						SELECT
							item_id
						FROM
							itemcarac
						WHERE
							caracvalor_id IN (".join(',', $gravacao_id).")
					) ";
		}
		if($temGravacao){
			$t->parseBlock('BLOCK_FILTRO_GRAVACOES');
		}

		// Materia prima
		$sqlFiltro =
			"
			SELECT
				SQL_CACHE
				DISTINCT caracvalor.*
			FROM
				caracvalor
			INNER JOIN itemcarac ON (
				itemcarac.carac_id = 1
			AND itemcarac.caracvalor_id = caracvalor.id
			)
			INNER JOIN ($sql) tmp_item ON (
				tmp_item.id = itemcarac.item_id
			)
			WHERE
				caracvalor.carac_id = 1
			";

		//print $sql;

		$query = query($sqlFiltro);
		$materia_prima_id = array();
		$temMateriaPrima = false;
		while($fetch=fetch($query)){
			traduz($fetch);
			$t->list_materia = $fetch;
			$t->checked = request('materia_prima_id')==$fetch->id?'checked':'';
			$t->checked = in_array($fetch->id, is_array(request('materia_prima_id'))?request('materia_prima_id'):array())?'checked':'';
			if($t->checked=='checked'){
				$materia_prima_id[] = $fetch->id;
			}
			$t->parseBlock('BLOCK_FILTRO_MATERIA', true);
			$temMateriaPrima = true;
		}
		if(sizeof($materia_prima_id)>0){
			$sqlWhere .=
					"AND item.id IN (
						SELECT
							item_id
						FROM
							itemcarac
						WHERE
							caracvalor_id IN (".join(',', $materia_prima_id).")
					) ";
		}
		if($temMateriaPrima){
			$t->parseBlock('BLOCK_FILTRO_MATERIAS');
		}

		$t->parseBlock('BLOCK_FILTRO');

		if($sqlWhere!=""){

			$sql .= $sqlWhere;

		}
	}

	public function imagemBannerShow($t,$tela='',$cat=0){

		if($cat && $cat->categoria_id>0){
			$cat = new categoria($cat->categoria_id);
		}
		$obj = new stdClass();
		$obj->imagem_tema = "pulcor_brindesv.jpg";

		switch($tela){
			case 'produtos':

				$t->titulo_cat = "Categorias";
				$t->fundobanner = "fundo_banner2";
				$t->categoria = $obj;
				$t->parseBlock('BLOCK_BANNER_TEMA');

			;break;
			case 'detalhe':

				$t->categoria = $obj;
				$t->titulo_cat = "Categorias";
				$t->fundobanner = "fundo_banner2";
				$t->parseBlock('BLOCK_BANNER_TEMA');

			;break;
			case 'cordao':
				$cat = new stdClass();
				$cat->imagem_tema = "pulcor_cordao_2v.jpg";
				$t->categoria = $cat;
				$t->titulo_cat = "Produtos";
				$t->fundobanner = "fundo_banner2";
				$t->parseBlock('BLOCK_BANNER_TEMA');

			;break;
			case 'home':
				$t->fundobanner = "";
				$t->parseBlock('BLOCK_BANNER_TEMA_DEFAULT');
			;break;

			case 'cadastro':
				$cat = new stdClass();
				$cat->imagem_tema = "pulcor_cordao_2v.jpg";
				$t->categoria = $cat;
				$t->titulo_cat = "";
				$t->fundobanner = "fundo_banner3";
				$t->parseBlock('BLOCK_BANNER_TEMA');

			;break;

			case 'carrinho':
				$cat = new stdClass();
				$cat->imagem_tema = "pulcor_cordao_2v.jpg";
				$t->categoria = $cat;
				$t->titulo_cat = "";
				$t->fundobanner = "fundo_banner3";
				$t->parseBlock('BLOCK_BANNER_TEMA');

			;break;

			case 'contato':
				$cat = new stdClass();
				$cat->imagem_tema = "pulcor_cordao_2v.jpg";
				$t->categoria = $cat;
				$t->titulo_cat = "";
				$t->fundobanner = "fundo_banner3";
				$t->parseBlock('BLOCK_BANNER_TEMA');

			;break;

			case 'empresa':
				$cat = new stdClass();
				$cat->imagem_tema = "pulcor_cordao_2v.jpg";
				$t->categoria = $cat;
				$t->titulo_cat = "";
				$t->fundobanner = "fundo_banner3";
				$t->parseBlock('BLOCK_BANNER_TEMA');

			;break;

			default :
				$t->fundobanner = "";
				$t->parseBlock('BLOCK_BANNER_TEMA_DEFAULT');
			;break;
		}

	}

	// Processa PRODUTOS
	private function processaProdutos(& $t, $sql, $joga_detalhe = true){

		$query = query($sql);
		$rows = rows($query);

		$h1='';
		if($rows==1
		&& $joga_detalhe){ // gambi no h1
			// achou só 1 joga direto pro detalhe
			$fetch = fetch($query);
			$this->det($fetch->id);
			die();
		}
		elseif ($rows==0) {
			if($h1!=''){
				$_SESSION['erro'] = tag('p','Nenhum produto encontrado em '.$h1);
			}
		}

		// Habilita ou nao paginacao
		if($this->config->HABILITA_PAGINADOR=='S'){
			$this->montaPaginacao($t, $sql, $rows);
		}

		$query = query($sql);

		$itens = array();

		while($fetch=fetch($query)){
			$itens[] = $fetch;
		}

		$temAlgum = false;

		for($i=0,$posicao=1,$n=sizeof($itens);$i<$n;$i++){

			$p = new Template('tpl.part-produto.html');

			if((($i+1)%3)==0){
				$posicao = 1;
				$p->margin = 'margin-right:0px;';
			}

			$item = new item();
			$item->load_by_fetch($itens[$i]);

			$splash = new splash($item->splash_id);

			if($splash->id){
				$p->list_splash = $splash;
				$p->parseBlock('BLOCK_SPLASH');
			}

			traduz($item);

			$item->chamada = nl2br($item->chamada);
			$item->descricao = nl2br($item->descricao);
			$item->nome_tag = stringAsTag($item->nome);

			$p->path = PATH_SITE;
			$p->index = INDEX;

			$p->list_item = $item;
			$p->posicao = $posicao++;

			if((($i+1)%3)==0){
				$posicao = 1;
				//$p->parseBlock('BLOCK_CLEAR');
			}
			if($item->preco>0){
				if($this->isLogado()){
					$cadastro = $_SESSION['CADASTRO'];
					if ($cadastro->tipocadastro_id == '3' ){
						if($item->preco_de<>"0.00"){
							$p->parseBlock('BLOCK_PRECO_DE');
						}
						$p->parseBlock('BLOCK_PRECO');
					}
				}
			}

			if($item->preco_de>0){
				if($this->isLogado()){

				}
			}

			$t->produto = $p->getContent();
			$t->parseBlock('BLOCK_LIST_ITEM', true);
			unset($item);

			$temAlgum = true;
		}

		if($temAlgum){
			$t->parseBlock('BLOCK_ITENS');
		}
	}

	// Muda o padrao de PAGINACAO
	public function muda_paginacao(){
		$qtd = intval(request('qtd'));
		if($qtd>0){
			$_SESSION['PADRAO_PAGINACAO']=$qtd;
		}
		header('location:'.$_SERVER['HTTP_REFERER']);
	}

	private function montaPaginacao($t, &$sql, $rows){

		$qtdProdutos = @$_SESSION['PADRAO_PAGINACAO']?@$_SESSION['PADRAO_PAGINACAO']:$this->config->QTD_PRODUTOS_PAGINA;

		$paginas = ceil($rows/$qtdProdutos);

		if($paginas>1){

			if(!intval(request('pagina'))>0){
				$_REQUEST['pagina']=1;
			}

			if(!intval(request('comeca'))>0){
				$_REQUEST['comeca']=1;
			}

			for($i=intval(request('comeca')), $limit=0, $c=1
				; ($i <= intval($paginas) && ($c <=6 ))
				; $i++, $c++, $limit += $qtdProdutos ){

				$t->comeca_prev = request('comeca');

				if($i==intval(request('comeca'))){
					if((request('pagina')-1)>0){
						$t->pagina_prev = request('pagina')-1;
						if((request('pagina')-1)<request('comeca')){
							$t->comeca_prev = request('comeca')-6;
						}
						$t->parseBlock('BLOCK_PAGINA_PREV_1');
					}
				}

				$t->pagina = $i;
				$t->comeca = intval(request('comeca'));

				if(request('pagina')==$i){
					$limit = ($i-1)*$qtdProdutos;
					$sql .= " LIMIT {$limit}, {$qtdProdutos}";
					$t->pagina_style = 'style="color:#2e362e; font-weight:bold;"';
				}
				else {
					$t->pagina_style = '';
				}

				$t->parseBlock('BLOCK_PAGINA_1', true);
				$t->parseBlock('BLOCK_PAGINA_2', true);

				if($i==($paginas-1)||$c==6){
					if((request('pagina')+1)<=$paginas){
						$t->pagina_next = request('pagina')+1;
						if((request('pagina')+1)==(request('comeca')+6)){
							$t->comeca = request('pagina')+1;
						}
						$t->parseBlock('BLOCK_PAGINA_NEXT_1');
					}
				}
			}

			$t->paginas = $paginas;
			$t->comeca_ultima = (floor($paginas/6)*6)+1;

			$t->parseBlock('BLOCK_PAGINADOR_1');
			$t->parseBlock('BLOCK_PAGINADOR_2');
		}

	}

	private function processaOutrosDados($t, $item){

		$outrosdados = '';

		if(!empty($item->profundidade)
			||!empty($item->largura)
			||!empty( $item->altura)){

			$lit = array() ;
			$num = array() ;

			if(!empty($item->altura)){
				$lit[] = "Alt." ;
				$num[] = $item->altura ;
			}
			if(!empty($item->largura)){
				$lit[] = "Larg." ;
				$num[] = $item->largura ;
			}
			if(!empty( $item->profundidade)){
				$lit[] = "Profund." ;
				$num[] = $item->profundidade ;
			}
		}
		if(!empty($item->largura)){
			$outrosdados .= "<br><strong>&nbsp;Largura:</strong> {$item->largura} cm";
		}
		if(!empty($item->altura)){
			$outrosdados .= "<br><strong>&nbsp;Altura:</strong> {$item->altura} cm";
		}
		if(!empty($item->diametro)){
			$outrosdados .= "<br><strong>&nbsp;Diâmetro:</strong> {$item->diametro} cm";
		}
		if(!empty($item->profundidade)){
			$outrosdados .= "<br><strong>&nbsp;Profundidade:</strong> {$item->profundidade} cm";
		}
		if(!empty($item->peso)&&floatval($item->peso)>0){
			$outrosdados .= "<br><strong>&nbsp;Aba:</strong> {$item->peso} cm";
		}
		if(!empty($item->energia)){
			$outrosdados .= "<br><strong>Energia:</strong> {$item->energia}" ;
		}
		if(!empty( $item->garantia)){
			$outrosdados .= "<br><strong>Garantia:</strong> {$item->garantia}" ;
		}
		if(!empty($item->disponibilidade)){
			$outrosdados .= "<br><strong>Disponibilidade:</strong> {$item->disponibilidade}" ;
		}
		if(!empty($item->material)){
			$outrosdados .= "<br><strong>Material:</strong> {$item->material}" ;
		}

		//$t->outrosdados = $outrosdados ;
	}

	private function processaSubCategoria($t, $categoria_id ,$cat_id){
		// Menu de categorias de produtos
		$sql = "SELECT
					SQL_CACHE
					categoria.id
					,categoria.nome
					,categoria.nome_es
					,categoria.nome_in
				FROM
					categoria
				INNER JOIN itemcategoria ON (
					itemcategoria.categoria_id = categoria.id
				)
				INNER JOIN item ON (
					itemcategoria.item_id = item.id
				AND item.st_ativo = 'S'
				)
				WHERE categoria.st_ativo = 'S'
				AND categoria.categoria_id = {$categoria_id}
				GROUP BY
					categoria.id
					,categoria.nome
				ORDER BY
					categoria.ordem
					,categoria.nome";


		$tem = false;
		$query = query($sql);
		while($fetch=fetch($query)){
			traduz($fetch);
			$t->selected = '';
			$fetch->nome_tag = stringAsTag($fetch->nome);
			$t->sub_categoria = $fetch;
			if($cat_id==$fetch->id){
				$t->selected = 'style="text-decoration:underline;"';
			}
			$t->parseBlock('BLOCK_SUB_CATEGORIA', true);
			$tem = true;
		}

		if($tem){
			$t->parseBlock('BLOCK_SUB_CATEGORIAS', true);
		}
	}

	public function pedido_alt_obs(){
		if(!token_ok()){die();}
		$obs = request('obs');

		foreach($this->carrinho->get_itens() as $key => $item_carrinho){
			if( $item_carrinho->item_qtd > 0
				&& $item_carrinho->item_id == request('item_id')){

				$_SESSION['S_CARRINHO']['itens'][$key]->item_obs = $obs;

				$this->pedido();
				return;
			}
		}
		header('location:'.INDEX);
	}

	public function verificaEmail(){
		$email = $_REQUEST['Email'];
		$email = trim($email);
		$cadastro = new cadastro(array('email'=>$email));
		$out = array();
		if($cadastro->id){
			$out[0] = 'E-mail j&aacute; est&aacute; cadastrado.';
			$out[1] = 1;
			echo json_encode($out);
		}else{
			echo $out[1] = 0;
		}
	}

	public function proc_news($email){
		$erro = false;
		$e_msg ="";
		/*if(($nome == "") || ($nome == 'Nome')){
			$erro = true;
			$e_msg .="Digite seu nome<br />";
		}*/

		$email = limpa($email);
		$email = addslashes($email);

		if(!is_email($email)){
			$erro = true;
			$e_msg .="Digite seu e-mail corretamente<br />";
		}

		if((rows(query("SELECT * FROM newscadastro WHERE email = '".$email."'")))>0){
			$erro = true;
			$e_msg .="Este e-mail já existe<br />";
		}

		if($erro){
			echo"<div class='erro-news' style='display:block'>".tag('p',$e_msg.'')."</div>";
		}else{
			$news_cad = new newscadastro();
			$news_cad->st_ativo ='S';
			//$news_cad->nome = $nome;
			$news_cad->email = $email;
			$news_cad->salva();

			echo"<div class='sucesso-news' style='display:block'>".tag('p', 'Cadastro efetuado com sucesso!')."</div>";
		}
	}

	public function cadastro_confirmacao($cadastro_id){
		$cadastro = new cadastro(intval($cadastro_id));
		if($cadastro->id&&$cadastro->st_confirmado=='N'){
			$t = new TemplateSite('tpl.cadastro-confirmacao.html');
			$t->cadastro = $cadastro;
			$this->show($t);
		}
	}

	public function mandaEmailNovoCadastro($cadastro_id){
		$cadastro = new cadastro(intval($cadastro_id));
		if($cadastro->id){

			// Manda e-mail para o cliente com senha
			// $t = new Template('tpl.email-novo-cadastro-modelo-2.html');
			// $e = new email();
			// Manda e-mail para a empresa com a senha em branco
			$t = new Template('tpl.email-novo-cadastro-modelo-2.html');
			$e = new email();

			// $conheceu  = results('select nome from comoconheceu where id = '.$cadastro->comoconheceu_id.'');
			// if($conheceu != null){
				// $t->conheceu = $conheceu[0]->nome;
				// $cadastro->especifique = "";
			// }else{
				// $t->conheceu = "Outros -";
			// }
			$cadastro->senha = decode($cadastro->senha);
			$cadastro->senha = "";
			$t->cadastro = $cadastro;
			$t->config = $this->config;

			$e->addTo($this->config->EMAIL_ADMINISTRACAO, $cadastro->nome);

			$e->addHtml($t->getContent());
			$e->send("Novo cadastro {$cadastro->nome}");
		}
	}

	// Envia senha para o usuario
	public function senha(){

        if(request('enviar')) {

            try {

                if(!is_email(request('email'))){
                    throw new Exception('E-mail inválido');
                }

                $cadastro = new cadastro(array('email'=>request('email'),'tipocadastro_id'=>2));
                if(!$cadastro->id) {
                    throw new Exception('Não foi possível identificar o seu cadastro');
                }

                $cadastro->senha = decode($cadastro->senha);

                $tEmail = new Template('tpl.email-senha.html');
                $tEmail->config = $this->config;
                $tEmail->cadastro = $cadastro;

                $email = new email();
                $email->addHtml($tEmail->getContent());
                $email->addTo($cadastro->email, $cadastro->nome);
                $email->send(config::get('EMPRESA'). ' - Sua senha');

                unset($email);

                print json_encode(array('status' => true, 'msg' => "{$cadastro->nome} sua senha foi enviada para {$cadastro->email}"));

            }
            catch (Exception $ex) {
                print json_encode(array('status' => false, 'msg' => $ex->getMessage()));
            }
        }
	}

	// public function recuperarSenha(){
		// $t = new Template('tpl.recuperar-senha.html');
		// $t->index = INDEX;
		// echo $t->getContent();
		// die();
	// }


	// Altera quantidade do pedido
	public function pedido_alt(){

        try {

            if(!token_ok()){
                throw new Exception('Problema no processamento');
            }

            $qtd = intval(request('qtd'));
            $qtd = $qtd < 0 ? 0 : $qtd;

            $qtd_indice = intval(request('qtd_indice'));

            if($qtd_indice==1){
                $campo_qtd = 'item_qtd';
            }
            elseif($qtd_indice==2){
                $campo_qtd = 'item_qtd2';
            }
            elseif($qtd_indice==3){
                $campo_qtd = 'item_qtd3';
            }
            else {
                throw new Exception('Nao foi possivel identificar a quantidade');
            }

            foreach($this->carrinho->get_itens() as $key => $item_carrinho){
                if($item_carrinho->item_qtd > 0
                && $item_carrinho->unique_id == request('unique_id')){

                    if($qtd==0){
                        $_SESSION['S_CARRINHO']['itens'][$key]->item_qtd  = $qtd;
                        $_SESSION['S_CARRINHO']['itens'][$key]->item_qtd2 = $qtd;
                        $_SESSION['S_CARRINHO']['itens'][$key]->item_qtd3 = $qtd;
                    }
                    else{
                        if($qtd < $item_carrinho->item_qtd_minima && $this->config->HABILITA_QUANTIDADE_MINIMA=='S'){
                            throw new Exception('Quantidade abaixo do mínimo');
                        }
                        else {
                            $_SESSION['S_CARRINHO']['itens'][$key]->$campo_qtd = $qtd;
                        }
                    }
                }
            }

            print json_encode(array('status'=>true,'msg'=>'Quantidade alterada'));

        }
        catch(Exception $ex){
            print json_encode(array('status'=>false,'msg'=>$ex->getMessage()));
        }


	}

	public function removerItemCarrinho(){
		$item_id = $_REQUEST['itemid'];
		foreach($this->carrinho->get_itens() as $key => $item_carrinho){
			if($item_carrinho->item_id==$item_id){
				unset($_SESSION['S_CARRINHO']['itens'][$key]);
				echo 1;
			}
		}
	}

	// Altera cor
	public function pedido_alt_cor(){

		if(!token_ok()){die();}
		$cor = new cor(intval(request('cor_id')));

		foreach($this->carrinho->get_itens() as $key => $item_carrinho){
			if( $item_carrinho->item_qtd > 0
				&& $item_carrinho->unique_id == request('unique_id')){

				if($cor->id){
					$_SESSION['S_CARRINHO']['itens'][$key]->cor_id = $cor->id;
					$_SESSION['S_CARRINHO']['itens'][$key]->cor_nome = $cor->nome;
				}
				else {
					$_SESSION['S_CARRINHO']['itens'][$key]->cor_id = '';
					$_SESSION['S_CARRINHO']['itens'][$key]->cor_nome = '';
				}

				$this->pedido();
				return;
			}
		}

		header('location:'.INDEX);
	}

	// Altera materia prima
	public function pedido_alt_materia_prima(){

		if(!token_ok()){die();}

		$materia_prima = new materia_prima(intval(request('materia_prima_id')));

		if(!$materia_prima->id){
			die();
		}

		foreach($this->carrinho->get_itens() as $key => $item_carrinho){
			if( $item_carrinho->item_qtd > 0
				&& $item_carrinho->unique_id == request('unique_id')){

				$_SESSION['S_CARRINHO']['itens'][$key]->materia_prima_id = $materia_prima->id;
				$_SESSION['S_CARRINHO']['itens'][$key]->materia_prima_nome = $materia_prima->nome;

				$this->pedido();
				return;
			}
		}

		header('location:'.INDEX);
	}

	// Altera materia prima
	public function pedido_alt_gravacao(){
		if(!token_ok()){die();}
		$gravacao = new gravacao(intval(request('gravacao_id')));

		traduz($gravacao);
		foreach($this->carrinho->get_itens() as $key => $item_carrinho){
			if( $item_carrinho->item_qtd > 0
				&& $item_carrinho->unique_id == request('unique_id')){

				if($gravacao->id){
					$_SESSION['S_CARRINHO']['itens'][$key]->gravacao_id = $gravacao->id;
					$_SESSION['S_CARRINHO']['itens'][$key]->gravacao_nome = $gravacao->nome;
				}
				else {
					$_SESSION['S_CARRINHO']['itens'][$key]->gravacao_id = "";
					$_SESSION['S_CARRINHO']['itens'][$key]->gravacao_nome = "";
				}

				$this->pedido();
				return;
			}
		}

		header('location:'.INDEX);
	}

	public function pedido_alt_qtd_cor_logo(){

		if(!token_ok()){die();}

		$qtd_cor_logo = intval(request('qtd_cor_logo'));

		foreach($this->carrinho->get_itens() as $key => $item_carrinho){
			if( $item_carrinho->item_qtd > 0
				&& $item_carrinho->unique_id == request('unique_id')){

				$_SESSION['S_CARRINHO']['itens'][$key]->item_qtd_cor_logo = $qtd_cor_logo;

				$this->pedido();
				return;
			}
		}

		header('location:'.INDEX);
	}

	//Adiciona um item no pedido
	public function pedido_add($finaliza=false){

		$item = new item(intval(request('item_id')));
		if($item->temVariacao()){
			$_SESSION['erro'] = tag('p','Esse produto possui variação, escolha uma cor para prosseguir.');
			$this->setLocation("detalhe/{$item->tag_nome}");
			die();
		}

		if($item->itemsku_id){
			$pai = new item($item->itemsku_id);
			$item->nome = $pai->nome;
			$item->descricao = $pai->descricao;
			$item->chamada = $pai->chamada;
			$item->qtd_minima = $pai->qtd_minima;
			if($item->tabela1=='')$item->tabela1=$pai->tabela1;
			if($item->tabela2=='')$item->tabela2=$pai->tabela2;
			if($item->tabela3=='')$item->tabela3=$pai->tabela3;
		}

		$qtd = intval(request('qtd1'));
		$qtd2 = intval(request('qtd2'));
		$qtd3 = intval(request('qtd3'));

		$qtd_cor_logo = intval(request('qtd_cor_logo'));

		$gravacao = new gravacao(intval(request('gravacao_id')));
		$materia_prima = new materia_prima(intval(request('materia_prima_id')));
		$tecido = new stdClass();
		$cor = new cor(intval(request('cor_id')));

		// $itemcor = new itemcor();

		// if($item->id&&$cor->id){
			// $itemcor->get_by_id(array('item_id'=>$item->id,'cor_id'=>$cor->id));
			// if($itemcor->id&&$itemcor->imagem&&file_exists('img/produtos/1/'.$itemcor->imagem)){
				// $item->imagem = $itemcor->imagem;
			// }
		// }

		// $itemcor->get_by_id(array('item_id'=>$item->id));


		// if($itemcor->id && request('cor_id') == 0 ){
			// $_SESSION['erro'] = tag('p','Escolha uma cor');
			// header('location:'.INDEX.'detalhe/'.$item->tag_nome.'');
			// die();
		// }


		if(!$item->id){die();}

		$item->qtd = $qtd<=0?1:$qtd;
		$item->qtd2 = $qtd2<=0?'':$qtd2;
		$item->qtd3 = $qtd3<=0?'':$qtd3;

		$item->qtd_cor_logo = $qtd_cor_logo;

		if($item->qtd<$item->qtd_minima){
			$_SESSION['erro'] = tag('p',"A quantidade mínima é {$item->qtd_minima}, ajustamos o seu pedido para ela");
			$item->qtd=$item->qtd_minima;
		}

		if($item->qtd2>0 && $item->qtd2 < $item->qtd_minima){
			$_SESSION['erro'] = tag('p',"A quantidade mínima é {$item->qtd_minima}, ajustamos o seu pedido para ela");
			$item->qtd2=$item->qtd_minima;
		}

		if($item->qtd3>0 && $item->qtd3 < $item->qtd_minima){
			$_SESSION['erro'] = tag('p',"A quantidade mínima é {$item->qtd_minima}, ajustamos o seu pedido para ela");
			$item->qtd3=$item->qtd_minima;
		}

		$erro = "";

		if(@$gravacao->id=='0'&&request('gravacao_id')){
			$erro .= tag('p',"Escolha o tipo de Gravação");
		}
		if(@$tecido->id == '0'&&request('tecido_id')){
			$erro .= tag('p',"Escolha o tipo de Tecido");
		}
		if ($erro != ""){
			$_SESSION['erro'] = $erro;
		}

		foreach($this->carrinho->get_itens() as $key => $item_carrinho){
			if( $item_carrinho->item_id > 0
				&& $item_carrinho->item_id == request('item_id')
				&& @$item_carrinho->cor_id == request('cor_id')
				&& @$item_carrinho->gravacao_id == request('gravacao_id')
				&& @$item_carrinho->tecido_id == request('tecido_id')
				){
			}
		}

		$item->imagem_peq = $item->imagem;
		$item->imagem = $item->imagem;

		if($this->carrinho->verificaItem($item->id,$cor->id)){
			$this->carrinho->add_item(array($item,$cor,$gravacao,$tecido,$materia_prima));
		}

		if(!$finaliza){
			header('location:'.INDEX.'pedido');
			die();
		}
	}

	//Mostra uma confirmacao dos dados do pedido
	public function pedido_confirmacao(){
		//SO LOGADO
		// if(!$this->isLogado()){
			// header('location:'.INDEX.'#c');
			// die();
		// }
		$itens = $this->carrinho->get_itens();
		if(sizeof($itens)==0){
			$this->index();
			return;
		}

		// UMA LOJA FAZENDO PEDIDO
		if($_SESSION['AREA']=='CLIENTE'){

			header('location:'.INDEX.'pedido_finaliza/#c');
			die();

			$t = new TemplateSite('tpl.pedido-confirmacao-cliente.html');
			$t->h1 = 'Confirme seu orçamento';

			foreach ($itens as $item){
				if($item->item_qtd>0){
					$t->list_pedido = $item;
					$t->parseBlock('BLOCK_LIST_PEDIDO', true);
				}
			}

			$t->logado = $_SESSION['CADASTRO'];
			$t->endereco = $_SESSION['CADASTRO']->get_parent('endereco');
			$t->vendedor = $_SESSION['CADASTRO']->getVendedor();

			$this->show($t);
		}
		// UMA LOJA FAZENDO PEDIDO
		elseif($_SESSION['AREA']=='REPRESENTANTE'){
			$t = new TemplateSite('tpl.pedido-confirmacao-representante.html');
			$t->h1 = 'Confirme seu orçamento';
			// CHEGA SE ESTA CHEGANDO UM CLIENTE_ID, QUER DIZER QUE O USUARIO SELECIONOU ALGUEM NO COMBO
			if(request('cliente_id')){
				$cliente = new cliente();
				$cliente->get_by_id(intval(request('cliente_id')));
				if($cliente->id){
					$_SESSION['CLIENTE']=$cliente;
				}
			}

			// CHEGA SE O REPRESENTANTE TEM CLIENTES ASSOCIADAS A ELE
			$temLoja = false;
			$query = query($sql=sprintf(site::SQL_CLIENTES_REPRESENTANTE, $_SESSION['REPRESENTANTE']->id));
			while($fetch=fetch($query)){
				$fetch->selecionado = (@$_SESSION['CLIENTE']&&$_SESSION['CLIENTE']->id==$fetch->id?'selected':'');
				$t->list_cliente = $fetch;
				$t->parseBlock('BLOCK_LIST_CLIENTE', true);
				$temLoja = true;
			}

			if(!$temLoja){
				$_SESSION['erro'] = tag('p', 'Não existem clientes associadas ao seu cadastro '.tag('a href="'.INDEX.'contato"','entre em contato'));
			}
			else {
				$t->parseBlock('BLOCK_TEM_CLIENTES');
			}

			foreach ($itens as $item){
				if($item->item_qtd>0){
					$t->list_pedido = $item;
					$t->parseBlock('BLOCK_LIST_PEDIDO', true);
				}
			}

			if(@$_SESSION['CLIENTE']){
				$cad_cliente = $_SESSION['CLIENTE']->get_parent('cadastro');
				$t->cad_cliente = $cad_cliente;
			}
			$t->cad_representante = $_SESSION['CADASTRO'];
			$t->imagem_cabecalho = PATH_SITE.'img/testeira/pedido.jpg';
			$this->show($t);
		}
	}


	public function altItensPedido(){
		if(isset($_REQUEST['item'])){
			foreach($this->carrinho->get_itens() as $key => $item_carrinho){
				foreach($_REQUEST['item'] as $item_id=>$value){
					if($item_carrinho->item_id == $item_id){
						$_SESSION['S_CARRINHO']['itens'][$key]->item_qtd    = $value['qtd'];
						$_SESSION['S_CARRINHO']['itens'][$key]->item_qtd2   = @$value['qtd2'];
						$_SESSION['S_CARRINHO']['itens'][$key]->item_qtd3   = @$value['qtd3'];

						if(isset($value['cor_id'])){
							$_SESSION['S_CARRINHO']['itens'][$key]->cor_id      = $value['cor_id'];
							$cor = new cor($value['cor_id']);
							$_SESSION['S_CARRINHO']['itens'][$key]->cor_nome      = $cor->nome;
						}

						if(isset($value['gravacao_id'])){
							$_SESSION['S_CARRINHO']['itens'][$key]->gravacao_id = $value['gravacao_id'];
							$gravacao = new gravacao($value['gravacao_id']);
							$_SESSION['S_CARRINHO']['itens'][$key]->gravacao_nome = $gravacao->nome;
						}
					}
				}
			}
		}
	}

	public function visualizarTelaFinalizacao($pedido_id){
		$t = new TemplateSite('tpl.pedido-finaliza-cliente.html');
		$t->h1 = 'Finalização';

		$pedido = new pedido($pedido_id);
		$t->pedido = $pedido;
		$t->vendedor = new cadastro($pedido->vendedor_id);

		$this->show($t);
	}

	public function atualizar_carrinho(){

		$datas = request('data');
		
		foreach ($_SESSION['S_CARRINHO']['itens'] as $index => $item) {
			foreach ($datas as $key => $data) {
				if($item->item_id == $data['id']){
					$item->item_qtd = $data['qtd'];
					$item->item_qtd2 = $data['qtd2'];
					$item->item_qtd3 = $data['qtd3'];
				}
			}
		}

	}

	// Finaliza o pedido, manda e-mail, avisa na tela
	public function pedido_finaliza(){

		if($this->config->HABILITA_LOGIN=='S'){
			$this->pedido_finaliza_com_login();
		}
		else {
			$this->pedido_finaliza_sem_login();
		}
	}


	private function pedido_finaliza_com_login(){

		// So logado
		if(!$this->isLogado()){
            die($this->setLocation('cadastro_carrinho'));
		}

		$this->altItensPedido();

		// Checa quantidade de itens dentro do carrinho
		if($this->carrinho->getQtdItens()==0){
			$_SESSION['erro'] = tag('p', 'Nenhum produto adicionado ao seu pedido');
			$this->index();
			return;
		}

		$this->pedido_finalizado();
	}

	private function pedido_finaliza_sem_login(){

		if(isset($_SESSION['CADASTRO_SEM_LOGIN'])){

			$cadastro = $_SESSION['CADASTRO_SEM_LOGIN'];
			
			// $t = new TemplateSite('tpl.pedido-finaliza-cliente.html');
			$t = new TemplateSite('tpl.pedido-finalizado.html');
			// $t->h1 = 'Finalização';

			$vendedor = new cadastro($cadastro->cadastro_id);
			$pedido = new pedido();

			// verifica se possui vendedor setado, caso não tenha seta o vendedor padrão 
			if($vendedor->id == ""){
				$vendedor->id = cadastro::vendedorPadrao();
			}else{
				$pedido->vendedor_id = $vendedor->id;
			}

			$pedido->cadastro_id = $cadastro->id;
			$pedido->pedidostatus_id = 1; // Lancado
			$pedido->anexo = $this->carrinho->getImagemAnexo();
			
			$pedido->insere();

			if($pedido->id){
				// INSERE OS ITENS
				$itens = $this->carrinho->get_itens();

				foreach ($itens as $item){
					if($item->item_qtd>0){
						$pedidoitem = new pedidoitem();
						$pedidoitem->item_id = $item->item_id;
						$pedidoitem->pedido_id = $pedido->id;
						$pedidoitem->item_preco = 0;
						$pedidoitem->item_qtd = $item->item_qtd;
						if(($item->item_qtd2 == '') or ($item->item_qtd2 == NULL)) $item->item_qtd2 = 0;
						if(($item->item_qtd3 == '') or ($item->item_qtd3 == NULL)) $item->item_qtd3 = 0;
						$pedidoitem->item_qtd2 = $item->item_qtd2;
						$pedidoitem->item_qtd3 = $item->item_qtd3;
						$pedidoitem->info = serialize($item);
						$pedidoitem->insere();

						$t->list_pedido = $pedidoitem;

						if($item->cor_id){
							// $t->cor_nome = $item->cor_nome;
							$t->parseBlock("BLOCK_ITEMCOR",true);

						}

						$t->parseBlock("BLOCK_LIST_PEDIDO",true);
					}
				}

				$tEmail = new Template('tpl.email-pedido-cliente.html');

				$tEmail->config = $this->config;
				// $tEmail->pedido = $pedido;
				$tEmail->cadastro = $cadastro;
				$e = new email();
				$e->addTo($cadastro->email, $cadastro->nome);
				$e->addCc($vendedor->email, $vendedor->nome);
				$e->addReplyTo($vendedor->email, $vendedor->nome);
				$e->addHtml($tEmail->getContent());
				$e->addBcc($this->config->EMAIL_ADMINISTRACAO, $this->config->EMPRESA);
				$e->send("Pedido de orçamento  {$cadastro->nome}");

				$this->carrinho->clear();
				$t->pedido = $pedido;
				//$t->vendedor = $vendedor;
				// $_SESSION['CADASTRO'] = $cadastro;
				$t->cadastro = $cadastro;

				$this->show($t);
				if(isset($_SESSION['CADASTRO_SEM_LOGIN']))unset($_SESSION['CADASTRO_SEM_LOGIN']);
			}
			else {
				$_SESSION['erro'] = tag('p', 'Atenção, houve uma falha na finalização do seu pedido');
				header('location:'.PATH_SITE.'index.php/pedido_confirmacao#c');
				die();
			}

		}else{
			$_SESSION['erro'] = tag('p','Atenção, digite seu e-mail corretamente para poder prosseguir');
			$this->setLocation('pedido');
		}
	}
	/**Adiciona um anexo ao pedido**/
	public function pedido_add_anexo(){
		$out = array();
		$imagem = @$_FILES['arquivo'];

		if($imagem['tmp_name']){

			$nom = strtolower($imagem['name']);
			$par = explode('.',basename($nom));

			if ((count($par)==1)){
				//$_SESSION['erro'] = tag('p','Tipo de arquivo invalido');
				//$this->pedido();

				$out[0] = 0;
				$out[1] = 'Tipo de arquivo invalido';

				echo json_encode($out);
				exit;
			}

			$ext = $par[count($par)-1];
			if (($ext == 'jpg')||($ext == 'jpeg')){
			}
			else {
			}

			$tmppath = $_SERVER['DOCUMENT_ROOT']."".PATH_SITE ;
			move_uploaded_file($imagem['tmp_name'],$ori=$tmppath.'tmp/'.session_id().'.'.$ext);

			$this->carrinho->setImagemAnexo('tmp/'.session_id().'.'.$ext);
			//$_SESSION['sucesso'] = tag('p','Arquivo enviado com sucesso');
			//$this->pedido();
			$out[0] = 1;
			$out[1] = 'Arquivo anexo com sucesso.';

			echo json_encode($out);
			exit;
		}
		else {
			// $_SESSION['erro'] = tag('p','Erro ao enviar imagem');
			// $this->pedido();
			$out[0] = 0;
			$out[1] = 'Erro ao enviar imagem';

			echo json_encode($out);
		}
	}

	/**Remover anexo do pedido**/
	public function remover_anexo(){
		$this->carrinho->setImagemAnexo('');
		//$this->pedido();
	}

	/**Efetua login**/
	public function login($carrinho=false){

		unset($_SESSION['AREA']);
		unset($_SESSION['CLIENTE']);

		if(@$_REQUEST['enviar']==1){

			$out = array();

			//if(!token_ok()){die();}
			$email = limpa($_REQUEST['login']);
			$senha = limpa($_REQUEST['senha']);

			$cadastro = new cadastro();
			if($cadastro->authCliente($email, $senha) || $cadastro->authClienteEspecial($email, $senha)){
				if($cadastro->st_ativo=='S'){

                    $this->setLogado($cadastro);
					$_SESSION['AREA']='CLIENTE';

					$out[0] = 1;
					$out[1] = tag('span class="l_sucesso"',"Seja bem vindo!");
					$out[2] = $cadastro->id;

                    if(request('redireciona')){
                        $out[3] = request('redireciona');
                        // $this->setLocation(request('redireciona'));
                    }
                    elseif($this->carrinho->getQtdItens()>0){
                        $out[3] = INDEX.'pedido';
                        // $this->setLocation('pedido');
                    }
                    else {
                        $out[3] = INDEX.'';
                        // $this->setLocation('');
                    }

					if($carrinho){
						return $out;
						die();
					}

					echo json_encode($out);
					die();

					//$_SESSION['sucesso'] = tag('p',"Olá {$cadastro->nome}, seja bem vindo");

					// if(request('redireciona')){
						// $this->setLocation(request('redireciona'));
					// }
					// if($this->carrinho->getQtdItens()>0){
						// $this->setLocation('pedido');
					// }
					// else {
						// $this->setLocation('');
					// }
				}
				else{


					$out[0] = 0;
					$out[1] = tag('span class="l_erro"','Cadastro Inativo!');
					$out[2] = 0;

					if($carrinho){
						return $out;
						die();
					}

					echo json_encode($out);
					die();
					//$_SESSION['erro'] = tag('p', 'Seu cadastro está inativo, por favor entre em contato');
				}
			}
			elseif($cadastro->authVendedor($email, $senha)){

				if($cadastro->st_ativo=='S'){
					$this->setLogado($cadastro);
					$_SESSION['AREA']='CLIENTE';

					$out[0] = 1;
					$out[1] = tag('span class="l_sucesso"',"Seja bem vindo!");

					if($carrinho){
						return $out;
						die();
					}

					echo json_encode($out);
					die();
					//$_SESSION['sucesso'] = tag('p',"Olá {$cadastro->nome}, seja bem vindo");

					//if(request('redireciona')){
						//$this->setLocation(request('redireciona'));
					//}
					//elseif($this->carrinho->getQtdItens()>0){
						//$this->setLocation('pedido');
					//}
					//else {
						//$this->setLocation('');
					//}

				}
				else{


					$out[0] = 0;
					$out[1] = tag('span class="l_erro"','Cadastro Inativo!');

					if($carrinho){
						return $out;
						die();
					}

					echo json_encode($out);
					die();
					//$_SESSION['erro'] = tag('p', 'Seu cadastro está inativo, por favor entre em contato');
				}
			}
			else{


				$out[0] = 0;
				$out[1] = tag('span class="l_erro"','Usu&aacute;rio ou Senha Inv&aacute;lido!');

				if($carrinho){
					return $out;
					die();
				}

				echo json_encode($out);
				die();
				//$js = js("lightObj(document.forms.formLogin.elements['_email'])");
				//$js .= js("lightObj(document.forms.formLogin.elements['_senha'])");
				//$_SESSION['erro'] = tag('p', 'Usuário ou senha inválidos');
				//$_SESSION['script'] = $js;
			}

			//if($this->carrinho->getQtdItens()>0){
				//header("location:".PATH_SITE.'index.php/pedido');
				//die();
			//}
			//else {
			//}
            return;
		}

		$t = new TemplateSite('tpl.login.html');

        $seo_opts =array(
			'title' => "Login"
		);
		if( is_object( $this->seopro ) ){
			if($this->seopro->title!="")$seo_opts["title"] = $this->seopro->title;
			if($this->seopro->description!="")$seo_opts["description"] = $this->seopro->description;
			if($this->seopro->keywords!="")$seo_opts["keywords"] = $this->seopro->keywords;
			if($this->seopro->url!="")$seo_opts["url"] = $this->seopro->url;
		}
		set_SEO($t, $seo_opts);

        $this->show($t);
	}


	/**Checa se o cabra tá logado ou não**/
	protected function isLogado(){
		if(array_key_exists('CADASTRO',$_SESSION)){
			if (@$_SESSION['SESSAO_ID'] === session_id()
				&& @$_SESSION['CADASTRO']->id
				&& rows(query('SELECT * FROM cadastro WHERE ( tipocadastro_id = '.tipocadastro::getId('CLIENTE').' or tipocadastro_id = '.tipocadastro::getId('VENDEDOR').' or tipocadastro_id = '.tipocadastro::getId('CLIENTEESPECIAL').' ) AND st_ativo = "S" AND id = '.intval(@$_SESSION['CADASTRO']->id))) === 1){
				$_SESSION['LOGADO']=true;
				return true;
			}
		}
		return false;
	}

	/**Seta um USUARIO como LOGADO**/
	private function setLogado($cadastro){
		$_SESSION['CADASTRO']=$cadastro;
		$_SESSION['SESSAO_ID']=session_id();
	}

	/**Retorna o endereço com base no cep**/
	public function get_endereco_json($cep){

		$sedex = new sedex();
		$sedex->cepDestino = $cep;
		$sedex->cepSaida = $cep;
		$sedex->calcula();

		$sedex->ok = 1;
		$json = array();

		if($sedex->ok){
			print "(".json_encode($sedex->enderecoDestino).")";
		}
		die();
	}

	/**Retorna o endereço com base no cep**/
	private function get_endereco($cep){

		$sedex = new sedex();
		$sedex->cepDestino = $cep;
		$sedex->cepSaida = $cep;
		$sedex->calcula();

		$sedex->ok = 1;
		$json = array();

		if($sedex->ok){
			return $sedex->enderecoDestino;
		}
	}
	/****Grupo de funções relacionadas a extranet****/
	public function en_historico(){

		if(!$this->isLogado()){
			$_SESSION['erro'] = 'É necessário estar logado para acessar essa área';
			$this->setLocation('index');
		}

		$cadastro = new cadastro($_SESSION['CADASTRO']->id);
		$vendedor = new cadastro($_SESSION['CADASTRO']->cadastro_id);

		$t = new TemplateSite('tpl.en-historico.html');

		$sql = "SELECT
					pedidoitem.*
				FROM
					pedidoitem
				INNER JOIN pedido ON (
					pedidoitem.pedido_id = pedido.id
				AND pedido.cadastro_id = {$cadastro->id}
				)
				ORDER BY
					pedido_id DESC";

		$temPedido = false;
		$bgcolor = '#ffffff';
		foreach(results($sql) as $result){
			$pedidoitem = new pedidoitem($result->id);
			if((!isset($pedido))||(isset($pedido)&&$pedido->id!=$result->pedido_id)){
				$pedido = new pedido($result->pedido_id);
				$t->list_pedido = new pedido($result->pedido_id);
				$t->parseBlock('BLOCK_LIST_PEDIDOITEM_PEDIDO_ID');
				$t->parseBlock('BLOCK_LIST_PEDIDOITEM_PEDIDO_DATA_HORA');
				$bgcolor = ($bgcolor=='#f4f4f4'?'#ffffff':'#f4f4f4');
				$t->bgcolor = $bgcolor;
			}

			$t->list_pedidoitem = $pedidoitem;
			$t->list_item = new item($pedidoitem->item_id);

			$info = unserialize($pedidoitem->info);

			$t->list_cor = new cor($info->cor_id);
			$t->list_gravacao = new gravacao($info->gravacao_id);
			$t->parseBlock('BLOCK_LIST_PEDIDOITEM', true);
			$temPedido = true;
		}

		$t->vendedor = $vendedor;

		if($temPedido){
			$t->parseBlock('BLOCK_PEDIDOS');
		}

		if(request('enviar')){

			$mensagem = request('mensagem');

			while(true){

				if(!$mensagem){
					$_SESSION['erro'] = tag('p','Digite a sua mensagem');
					break;
				}

				$tEmail = new Template('tpl.email-msg-vendedor.html');

				$tEmail->config = $this->config;
				$tEmail->cadastro = $cadastro;
				$tEmail->vendedor = $vendedor;

				$tEmail->mensagem = nl2br($mensagem);

				$e = new email();

				$e->addCc($cadastro->email, $cadastro->nome);
				$e->addTo($vendedor->email, $vendedor->nome);
				$e->addReplyTo($vendedor->email, $vendedor->nome);

				$e->addHtml($tEmail->getContent());

				$e->addBcc($this->config->EMAIL_ADMINISTRACAO, $this->config->EMPRESA);
				$e->send("Mensagem enviada por {$cadastro->nome}");

				$_SESSION['sucesso'] = tag('p','Sua mensagem foi enviada com sucesso. Em breve entraremos em contato.');

				break;
			}
			$t->mensagem = $mensagem;
		}

		$t->h1 = 'Histórico de pedidos';
		$this->show($t);

	}

	public function en_propostas(){

		if(!$this->isLogado()){
			$_SESSION['erro'] = 'É necessário estar logado para acessar essa área';
			$this->setLocation('index');
		}

		$t = new TemplateSite('tpl.en-propostas.html');

		$sql = "SELECT
					proposta.*
				FROM
					proposta
				INNER JOIN pedido ON (
					pedido.id = proposta.pedido_id
				)
				INNER JOIN cadastro ON (
					cadastro.id = pedido.cadastro_id
				)
				ORDER BY id DESC";

		$temProposta = false;
		foreach(results($sql) as $result){
			$t->proposta = new proposta($result->id);
			$t->parseBlock('BLOCK_PROPOSTA');
			$temProposta = true;
		}

		if($temProposta){
			$t->parseBlock('BLOCK_PROPOSTAS');
		}
		$this->show($t);

	}

	/**Logout**/
	public function logout(){
		unset($_SESSION['CADASTRO']);
		unset($_SESSION['LOGADO']);
		header('location:'.PATH_SITE);
	}

	// Indicar PRODUTO
	public function pop_indique_produto($item_id){

		$t = new Template('tpl.pop-indique-produto.html');

		$item = new item(intval($item_id));
		if(!$item->id)die();

		$t->item = $item;
		$t->path = PATH_SITE;
		$t->index = INDEX;
		$t->config = $this->config;
		$t->token = session_id();

		$t->show();

	}

	public function pop_indique_produto_enviar(){

		if(request('enviar')=='sim'){

			$msg = "";

			if(!token_ok())die();

			$item = new item(intval(request('item_id')));

			if(!$item->id)die();

			$indicacao = new stdClass();

			foreach($_REQUEST['indicacao'] as $k=>$v){
				$indicacao->$k = $v;
			}

			$erro = array();
			if($indicacao->usuario_nome==""){
				$msg .= "Digite seu nome" . "\n";
			}
			if(!is_email($indicacao->usuario_email)){
				$msg .= "Digite seu e-mail corretamente" . "\n";
			}
			if($indicacao->amigo_nome==""){
				$msg .= "Digite o nome do seu amigo" . "\n";
			}
			if(!is_email($indicacao->amigo_email)){
				$msg = "Digite o e-mail do seu amigo" . "\n";
			}
			if($msg==""){

				$tEmail = new Template('tpl.email-indique-produto.html');
				$tEmail->indicacao = $indicacao;
				$tEmail->config = new config();

				$item->imagem_med = PATH_MED.$item->imagem;

				$tEmail->item = $item;

				$e = new email();
				$e->addTo($indicacao->amigo_email,$indicacao->amigo_nome);
				$e->addReplyTo($indicacao->usuario_email, $indicacao->usuario_nome);
				$e->addHtml($tEmail->getContent());

				$e->addBcc($this->config->EMAIL_ADMINISTRACAO, $this->config->EMPRESA);
				$e->send("Indicação enviada por - {$indicacao->amigo_nome}");

				$msg = "OK:Obrigado {$indicacao->usuario_nome}, sua indicação foi enviada para {$indicacao->amigo_nome}";

			}
			else {
				$msg = "ERRO:".$msg;
			}

			echo $msg;

		}
	}

	// Duvidas sobre um PRODUTO
	public function pop_duvida_produto($item_id){
		$t = new Template('tpl.pop-duvida-produto.html');

		$item = new item(intval($item_id));
		if(!$item->id)die();

		$t->item = $item;

		$t->path = PATH_SITE;
		$t->index = INDEX;
		$t->config = $this->config;
		$t->token = session_id();

		$t->show();
	}

	public function pop_duvida_produto_enviar(){
		if(request('enviar')=='sim'){

			$msg = "";

			if(!token_ok())die();

			$item = new item(intval(request('item_id')));
			if(!$item->id)die();

			$duvida = new stdClass();

			foreach($_REQUEST['duvida'] as $k=>$v){
				$duvida->$k = $v;
			}

			$erro = array();
			if($duvida->usuario_nome==""){
				$msg .= "Digite seu nome" . "\n";
			}
			if(!is_email($duvida->usuario_email)){
				$msg .= "Digite seu e-mail corretamente" . "\n";
			}
			if($duvida->pergunta==""){
				$msg .= "Digite a sua pergunta" . "\n";
			}
			if($msg==""){
				$tEmail = new Template('tpl.email-duvida-produto.html');
				$tEmail->duvida = $duvida;
				$tEmail->config = new config();
				$item->imagem_med = PATH_MED.$item->imagem;
				$tEmail->item = $item;

				$e = new email();
				$e->addTo(config::get('EMAIL_CONTATO'),config::get('EMPRESA'));
				$e->addReplyTo($duvida->usuario_email, $duvida->usuario_nome);
				$e->addHtml($tEmail->getContent());
				$e->addBcc($this->config->EMAIL_ADMINISTRACAO, $this->config->EMPRESA);
				$e->send("Duvida enviada por - {$duvida->usuario_nome}");

				$msg = "OK:Obrigado {$duvida->usuario_nome}, sua pergunta foi enviada, em breve retornaremos";
			}
			else {
				$msg = "ERRO: ".$msg;
			}
			print $msg;
		}
	}

	public function idioma($idioma){
		$_SESSION['IDIOMA']	= $idioma;
		$this->index();
	}
	private function setLocation($url){
		die(header('location:'.INDEX.$url));
	}

	// Set breadcrumb
	private function setBreadcrumb(&$t, $links=array(), $enable_back=false){

		// $tags = bsite::getTagCloud();
		$i=0;
		$n=sizeof($links);

		if($n>0){

			// Força o link da home
			$home = array('Home'=>INDEX);
			list($title, $link) = each($home);
			$t->breadcrumb = (object)array('link'=>$link, 'title'=> str_replace("<br>","",$title));
			$t->parseBlock('BLOCK_BREADCRUMB_ITEM_DIVIDE');
			$t->parseBlock('BLOCK_BREADCRUMB_ITEM', true);

			// Parse nos outros itens
			for(; $i<$n; $i++){
				list($title, $link) = each($links);
				$t->breadcrumb = (object)array('link'=>$link, 'title'=> str_replace("<br>","",$title));
				if($i<($n-1)){
					$t->parseBlock('BLOCK_BREADCRUMB_ITEM_DIVIDE');
				}
				$t->parseBlock('BLOCK_BREADCRUMB_ITEM', true);
			}

			if($enable_back){
				$t->parseBlock('BLOCK_BREADCRUMB_BACK');
			}

		}

		if($i>0){
			$t->parseBlock('BLOCK_BREADCRUMB');
		}
	}

    public function download(){

        if(!$this->isLogado()){
            return $this->index();
        }

        if(request('get')){

            set_time_limit(0);
            ini_set('memory_limit', '-1');
            ob_implicit_flush(true);

            try {

                $where = '';
                $nomearquivo = '';

                if($buscar=request('buscar')){

                    $where = "
                WHERE
                    item.referencia = '{$buscar}'
                AND item.st_ativo = 'S'
                ";

                    $nomearquivo = stringAsTag($buscar);

                }
                else {

                    $categoria = new categoria(array(
                        'id' => intval(request('get'))
                    ,'st_ativo' => 'S'
                    ));

                    if(!$categoria->id){
                        return;
                    }

                    $where = "
                INNER JOIN itemcategoria ON (
                    itemcategoria.item_id = item.id
                AND itemcategoria.categoria_id = {$categoria->id}
                )
                WHERE
                    item.st_ativo = 'S'
                ";

                    $nomearquivo = stringAsTag($categoria->nome);

                }

                if(trim($where)==''){
                    return;
                }

                $files = array();
                $campos = array('imagem','imagem_d1','imagem_d2','imagem_d3','imagem_d4','imagem_d5','imagem_d6','imagem_d7','imagem_d8','imagem_d9','imagem_d10','imagem_d11','imagem_d12','imagem_d13','imagem_d14','imagem_d15','imagem_d16','imagem_d17','imagem_d18','imagem_d19','imagem_d20');

                $sql =
                "
                SELECT
                    DISTINCT
                    item.id
                    ,item.referencia
                    ,CASE WHEN item.itemsku_id > 0 THEN (SELECT referencia FROM item a WHERE a.id = item.itemsku_id) ELSE item.referencia END referencia_pai
                    ,item.imagem
                    ,item.imagem_d1
                    ,item.imagem_d2
                    ,item.imagem_d3
                    ,item.imagem_d4
                    ,item.imagem_d5
                    ,item.imagem_d6
                    ,item.imagem_d7
                    ,item.imagem_d8
                    ,item.imagem_d9
                    ,item.imagem_d10
                    ,item.imagem_d11
                    ,item.imagem_d12
                    ,item.imagem_d13
                    ,item.imagem_d14
                    ,item.imagem_d15
                    ,item.imagem_d16
                    ,item.imagem_d17
                    ,item.imagem_d18
                    ,item.imagem_d19
                    ,item.imagem_d20
                FROM
                    item

                @where

                ORDER BY
                    item.referencia
                ";

                $itens = results($tmp = str_replace("@where", $where, $sql));

                if(sizeof($itens)==0){
                    throw new Exception("Não conseguimos identificar nenhum item para download");
                }

                foreach($itens as $fetch){

                    foreach($campos as $campo){
                        $imagem = $fetch->$campo;
                        if($imagem && file_exists($file='img/produtos/original/'.$imagem)){
                            $files[] = array(
                                'file' => $file
                            ,'folder' => $fetch->referencia_pai
                            );
                        }
                    }

                    $where = "WHERE itemsku_id = {$fetch->id}";
                    $filhos = results(str_replace("@where", $where, $sql));

                    foreach($filhos as $fetch){
                        foreach($campos as $campo){
                            $imagem = $fetch->$campo;
                            if($imagem && file_exists($file='img/produtos/original/'.$imagem)){
                                $files[] = array(
                                    'file' => $file
                                ,'folder' => $fetch->referencia_pai
                                );
                            }
                        }
                    }
                }

                if(sizeof($files)==0){
                    throw new Exception("Nenhum arquivo encontrado para download");
                }

                $arquivo = "upload/restrict/{$nomearquivo}.zip";

                if(file_exists($arquivo)){
                    unlink($arquivo);
                }

                $zip = createZip($files, $arquivo);

                if(!$zip){
                    throw new Exception("Não foi possível criar o arquivo para download");
                }

                header('Content-Type:application/octet-stream');
                header('Content-Disposition: attachment; filename='.basename($arquivo));
                readfile($arquivo);

                return;

            }
            catch(Exception $ex){
                $mensagemerro = $ex->getMessage();
            }

        }

        $t = new TemplateSite('tpl.download.html');

        if(isset($mensagemerro)){
            $t->mensagemerro = $mensagemerro;
            $t->parseBlock('BLOCK_DOWNLOAD_ERRO');
        }

        $categorias = array();
        $query = query(
        "
        SELECT
            categoria.id
            ,categoria.categoria_id
            ,categoria.nome
        FROM
            categoria
        WHERE
            categoria.st_ativo = 'S'
        AND EXISTS (SELECT 1 FROM itemcategoria WHERE itemcategoria.categoria_id = categoria.id LIMIT 1)
        ORDER BY
            categoria.ordem");

        while($fetch=fetch($query)){
            $categorias[] = $fetch;
        }

        $itens = $this->getcategorias($categorias);
        $principais = $this->getcategorias($categorias);

        $i = 0;
        $n = sizeof($principais);

        while($i < $n){

            $item1 = @$principais[$i++];
            $item2 = @$principais[$i++];

            $has = false;

            for($x = 1; $x <= 2; $x++){

                $item = 'item'.$x;
                $item = $$item;

                if(!$item){
                    break;
                }

                // $t->item = $has = $item;
                $has = true;

                $t->linkdownload = INDEX.'download/?get='.$item->id;
                $t->categoria = $item;
                $t->block('BLOCK_DOWNLOADITEM');

                foreach($this->getcategorias($categorias, $item->id, 2) as $categoria){
                    $t->linkdownload = INDEX.'download/?get='.$categoria->id;
                    $t->categoria = $categoria;
                    $t->block('BLOCK_DOWNLOADITEM');
                }

                $t->block('BLOCK_COL');

            }

            if($has){
                $t->block('BLOCK_ROW');
            }
        }

        set_SEO($t, array(
            'title' => 'Download'
        ));

        $this->show($t);

    }


    private function getcategorias(&$categorias, $categoria_id=0, $nivel=1, $recursivo = false){
        $ret = array();
        foreach($categorias as $categoria){
            if(intval($categoria->categoria_id) == intval($categoria_id)){
                $categoria->nivel = $nivel;
                $ret[] = $categoria;
                $nivel ++;
                // $ret = array_merge($ret, $this->getcategorias($categorias, $categoria->id, $nivel));
                $nivel --;
            }
        }
        return $ret;
    }

    public function institucional($key){

        $t = new TemplateSite('admin/modulos/paginainstitucional/tpl.paginainstitucional-frontend.html');
        $t->paginainstitucional = $paginainstitucional = new cmsitem(array('st_ativo'=>'S', 'tipo' => 'paginainstitucional', 'chave'=> limpa($key)));

        if(!$paginainstitucional->id){
            return $this->index();
        }

        $this->show($t);
    }

    public function skype(){

        // Logado
        if($this->isLogado()){
            // return $this->contato();
            header('location:'.INDEX.'contato');
        }
        else {
            header('location:'.INDEX.'cadastro');
        }
    }

    public function tmp(){

        $itens = results("select * from item");
        foreach($itens as $tmp){
            $item = new item();
            $item->load_by_fetch($tmp);
            $item->salva();
        }

    }

}

class Msg {
	public function __get($key){
		return '';
	}
}

new UrlAmigavel(new Site());

// @mysql_close();

if(DEBUG=='1' && $_SERVER['SERVER_NAME'] == 'assis'
&& isset($_REQUEST['DEBUGSQL'])
&& strtolower(@$_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest' ){

    print '<br clear="all">';
    print '<pre>';
    print_r("\n".'Total de consultas: '.sizeof($_REQUEST['DEBUGSQL']));
    foreach($_REQUEST['DEBUGSQL'] as $i => $sql){
        print_r("\n".($i+1));
        print_r("\n".$sql);
        print_r("\n");
    }
    print '</pre>';

}
