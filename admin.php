<?php

$script_start = microtime(true);
require 'global.php';

class admin {

	private $config;

	function __construct(){
		$this->config = new config();
		//printr($_REQUEST);
	}

    public function __call($name, $arguments){

        $file = "admin/modulos/{$name}/{$name}.php";

		
        if(file_exists($file)){

            $this->filtraLogado();
			$modulo = new modulo(array('arquivo'=>$name));
			
			//printr($modulo);
			//modulo_admin::seedModulo($modulo,$name,$arguments);/** Cria mo módulo caso não exista e se o user for admin já recebe a permissão */

            $this->filtraPermissao($modulo);
            $this->setModuloAtual($modulo);

            require_once $file;
            $x = 'modulo_'.$name;
            $metodo = 'pesquisa';

            $arg1 = $arg2 = $arg3 = $arg4 = $arg5 = '';

            if($arguments && is_array($arguments)){
                for($i=0;$i<=4;$i++){
                    if(array_key_exists($i, $arguments)){
                        $var = "arg".($i+1);
                        $$var = clean_input($arguments[$i]);
                        // $arg"{$i}" = $params[$i];
                    }
                }
            }

            $tmp = new $x();
            $tmp->set_adm($this);
            $tmp->set_modulo($modulo);
            $tmp->set_config($this->config);

            if($arg1 != '' && method_exists($tmp,$arg1)){
                $metodo = $arg1;
            }
            $tmp->$metodo();

            /*
            butil::printr($tmp);
            butil::printr($file);
            butil::printr($name);
            butil::printr($modulo);
            butil::printr($metodo);
            butil::printr($arguments);
            */
        }
    }

	public function index(){
		
		$this->filtraLogado();
		$t = new TemplateAdmin('admin/tpl.admin-index.html');

		$this->dashboard($t);

		$this->montaMenu($t);
		$this->show($t);
	}

	private function dashboard($t){


		// Top 10 por produto
		$sql =
			"
			SELECT
				COUNT(visita.id) count_visita_id
				,item.*
			FROM
				item
			INNER JOIN visita ON (
				item.id = visita.item_id
			AND date_format(visita.data_cadastro,'%d/%m/%Y') = date_format(curdate(),'%d/%m/%Y')
			)
			GROUP BY
				visita.item_id
			ORDER BY
				1 DESC
			LIMIT 15
			";

		$query = query($sql);
		$i = 1;
		foreach(results($sql) as $result){
			$result->pos = $i++;
			$t->rel_list_item = $result;
			$t->parseBlock('BLOCK_REL_LIST_ITEM', true);
		}

		// Top 10 por categoria
		$sql =
			"
			SELECT
				COUNT(visita.id) count_visita_id
				,categoria.*
			FROM
				categoria
			INNER JOIN visita ON (
				categoria.id = visita.categoria_id
			AND date_format(visita.data_cadastro,'%d/%m/%Y') = date_format(curdate(),'%d/%m/%Y')
			)
			GROUP BY
				visita.categoria_id
			ORDER BY
				1 DESC
			LIMIT 15
			";

		$query = query($sql);
		$i = 1;
		foreach(results($sql) as $result){
			$result->pos = $i++;
			$t->rel_list_categoria = $result;
			$t->parseBlock('BLOCK_REL_LIST_CATEGORIA', true);
		}

		// Estatisticas por como conheceu
		// $sql =
			// "
			// SELECT
				// COUNT(cadastro.id) count_cadastro_id
				// ,comoconheceu.*
			// FROM
				// comoconheceu
			// INNER JOIN cadastro ON (
				// comoconheceu.id = cadastro.comoconheceu_id
			// )
			// GROUP BY
				// comoconheceu.id
			// ORDER BY
				// 1 DESC
			// LIMIT 10
			// ";

		// $query = query($sql);
		// $i = 1;
		// foreach(results($sql) as $result){
			// $result->pos = $i++;
			// $t->rel_list_comoconheceu = $result;
			// $t->parseBlock('BLOCK_REL_LIST_COMOCONHECEU', true);
		// }

		$t->hoje = date('d/m/Y');
	}

    public function show(&$t, $opts=array()){

		$t->path = PATH_SITE;
		$t->index = PATH_SITE.'admin.php/';
		$t->config = $this->config;
		$t->token = session_id();
		$t->logado = @$_SESSION['CADASTRO'];

		//$t->configuracao = new configuracao();

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
			$t->parseBlock('BLOCK_SCRIPT');
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
		
		$t->show();
		//$t->montaMenu();
	}
	
	
	public function montaMenu($t){

        $modulos = results($sql =
        "
        SELECT
            modulo.*
        FROM
            modulo, permissao
		WHERE
		    modulo.id = permissao.modulo_id
		AND permissao.cadastro_id = ".intval(decode($_SESSION['CADASTRO']->id))."
		AND st_ativo = 'S'
		ORDER BY ordem, nome");

		$t->menu = $this->montaMenuProcessa(0, 0, $modulos);

		$moduloAtual = $this->getModuloAtual();
		$moduloRoot = ($moduloAtual?$moduloAtual->getModuloRoot():NULL) ;

		if($moduloAtual){
			$this->montaMenuTrilha($t, $moduloAtual);
		}

	}

	private function montaMenuProcessa($modulo_id=0, $nivel=0, &$modulos){

		$ret = '';

		$nodes = $this->getModulosFilhos($modulo_id, $modulos);
		if(sizeof($nodes)>0){
			foreach($nodes as $node){
				$moreNodes = $this->getModulosFilhos($node->id, $modulos);
				if(sizeof($moreNodes)>0){
					$nivel ++;
					if($nivel>1){
						$ret .= "<li class='dropdown-submenu'><a href='#' class='dropdown-toggle' data-toggle='dropdown'>{$node->nome}<b class='caret'></b></a>";
					}
					else {
						$ret .= "<li class='dropdown'><a href='#' class='dropdown-toggle' data-toggle='dropdown'>{$node->nome}<b class='caret'></b></a>";
					}
					$ret .= "<ul class='dropdown-menu'>";
					$ret .= $this->montaMenuProcessa($node->id, $nivel, $modulos);
					$nivel --;
					$ret .= "</ul></li>";
				}
				else {
					$ret .= "<li><a href='".$node->getActionLink()."'>{$node->nome}</a>";
					$ret .= "</li>";
				}
			}
		}

		return $ret;

	}
	
	private function getModulosFilhos($modulo_id, &$modulos){

		$ret = array();

        foreach($modulos as $fetch){
            if(intval($fetch->modulo_id) == $modulo_id){

                $modulo = new modulo_admin();
                $modulo->load_by_fetch($fetch);

                $ret[] = $modulo;
            }
        }

		return $ret;

	}

    // Monta trilha de navegacao
    private function montaMenuTrilha($t, $moduloAtual){

        $ret = '';

        $trilha = $moduloAtual->getParents();
        $trilha = array_reverse($trilha);

        $i = 0;
        $n = sizeof($trilha)-1;
        foreach($trilha as $trilhaitem){

            if($i==($n)){
                $ret .= '<li class="active">';
                if($i>0){
                    // $ret .= ' <span class="divider">/</span>';
                }
                $ret .= $trilhaitem->nome;
                $ret .= '</li>';
            }
            else {
                $ret .= '<li>';
                if($i>0){
                    // $ret .= ' <span class="divider">/</span>';
                }
                $ret .= '<a href="'.$trilhaitem->getActionLink().'">'.$trilhaitem->nome.'</a>';
                $ret .= '</li>';
            }
            $i ++;
        }

        if($i>1){
            $t->trilha = '
            <ol class="breadcrumb">
                '.$ret.'
            </ol>';
        }
    }

    private function setModuloAtual($modulo){
        $_SESSION['MODULOATUAL'] = @$modulo;
    }

	private function getModuloAtual(){
		return @$_SESSION['MODULOATUAL'];
	}

    public function go_modulo($arquivo, $action='', $param1='', $param2='', $param3=''){

        $this->filtraLogado();

        $modulo = new modulo(array('arquivo'=>$arquivo));
        if(!$modulo->id){
            $modulo = new modulo(array('id'=>intval($arquivo)));
        }

        $this->filtraPermissao($modulo->id);
        $this->setModuloAtual($modulo);

        $modulos = results($sql =
                "
        SELECT
            modulo.*
        FROM
            modulo, permissao
        WHERE
            modulo.id = permissao.modulo_id
        AND permissao.cadastro_id = ".intval(decode($_SESSION['CADASTRO']->id))."
        AND st_ativo = 'S'
        ORDER BY ordem, nome");

        // Checa se tem modulos filhos
        $nodes = $this->getModulosFilhos($modulo->id, $modulos);

        if(sizeof($nodes)>0){

            $t = new TemplateAdmin('admin/tpl.modulo-list.html');
            $t->modulo_atual = $modulo;

            foreach($nodes as $node){
                $t->modulo = $node;
                $t->parseBlock('BLOCK_MODULO', true);
            }

            // Só monta menu-lateral caso não seja pop-up
            if(!request('pop')){
                $this->montaMenu($t);
            }

            $this->show($t);

        }
        else {
            $_SESSION['erro'] = 'Módulo não disponível';
            die($this->index());
        }
    }

	public function logout(){
        session_destroy();
        die(header('location:'.PATH_SITE.'admin'));
	}

	public function login(){

		if(request('logar')=='sim'){

			$this->filtraToken();

			$cadastro = new cadastro(
				array(
					'email;login'=>request('login')
					,'st_ativo'=>'S'
					,'senha'=>encode(request('senha'))
					,'tipocadastro_id'=>array(tipocadastro::getId('ADMINISTRATIVO'),tipocadastro::getId('VENDEDOR'))
				)
			);

			if($cadastro->id){
				$cadastro->id = encode($cadastro->id);
				$_SESSION['CADASTRO'] = $cadastro;
                header('location:'.PATH_SITE.'admin.php/dashboard');
				// header('location:'.PATH_SITE.'admin.php');
				return;
			}
			else {
				$_SESSION['erro'] = 'Usuário ou senha inválidos';
			}
		}

		$t = new Template('admin/tpl.admin-login.html') ;
		$this->show($t);
	}
	
	public function nossossites(){
		$this->filtraLogado();
		$this->filtraPermissao('nossossites');
		
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		
		if(request('action')=='editar'
			||substr(request('action'),0,6)=='salvar'){
			$this->nossossitesEditar($t);
			return;
		}		
		if(request('action')=='excluir'){
			$nossossites = new nossossites(intval(request('id')));
			$nossossites->exclui();
		}
		
		
		$t->h1 = h1('Nossos Sites');
		
		$sql          = "SELECT 
							id,
							nome Nome,
							link Link,
							st_ativo Status
						FROM 
							nossossites";
							
		$grid         = new grid();
		$filtro       = new filtro();
		$grid->sql    = $sql;
		$grid->filtro = $filtro;
		
		$t->grid = $grid->render();
		
		$this->montaMenu($t);
		$this->show($t);
	}
	
	public function nossossitesEditar($t){
		$this->filtraLogado();
		$this->filtraPermissao('nossossites');
		
		$nossossites = new nossossites(request('id'));
		
		if(substr(request('action'),0,6)=='salvar'){

			$next = substr(request('action'),7,strlen(request('action')));

			$nossossites->set_by_array($_REQUEST['nossossites']);
			if($nossossites->verificaDados($msg)){
				$nossossites->salva();
				$_SESSION['sucesso'] = 'Dados salvos com sucesso';

				if(trim(@$next)!=''){
					$this->afterSave($next,'nossossites');
					return;
				}
			}else{
				$_SESSION['erro'] = tag('p',$msg);
			}
		}	
		
		
		$t->parseBlock('BLOCK_TOOLBAR');
		$edicao = '';
		$edicao .= tag('h1','Site '.$nossossites->nome);
		
		$edicao .= inputHidden('id',$nossossites->id);
		$edicao .= select('nossossites[st_ativo]',$nossossites->st_ativo,'Ativo',array('S'=>'SIM','N'=>'NAO'));
		$edicao .= inputSimples('nossossites[nome]',$nossossites->nome,'Nome',100,200);
		$edicao .= inputSimples('nossossites[link]',$nossossites->link,'Link',100,200);
		
		$t->edicao = $edicao;
		
		$this->montaMenu($t);
		$this->show($t);
	}


	public function clientesEditar($t){

		$this->filtraLogado();
		$this->filtraPermissao('clientes');
		
		// Se for pop-up, carrega template de pop-up
		if(request('pop')){
			$t = new TemplateAdminPop('admin/tpl.admin-cadastro-generico.html');
		}

		$cadastro = new cadastro(intval(request('id')));
		$cadastro->set_by_array(@$_REQUEST['cadastro']);

		//printr($cadastro);

		if(!$cadastro->id){
			$cadastro->tipocadastro_id = tipocadastro::getId('CLIENTE');
		}

		if(substr(request('action'),0,6)=='salvar'){

			if(request('senha_nova')||!$cadastro->id){
				$cadastro->senha = encode(request('senha_nova'));
			}

			$erros = array();

			$next = substr(request('action'),7,strlen(request('action')));

			if(!$cadastro->validaCadastroAdmin($erros)){
				$msg = '';
				foreach($erros as $erro){
					$msg .= tag('p',$erro);
				}
				$_SESSION['erro'] = $msg;
				$this->afterSave('','clientes');
			}
			else {
				if(file_tratamento("img_cliente",$msg,$file)){
					$filenome = strtolower($cadastro->nome);
					$filenome = str_replace("","_",$filenome).".jpg";
					$cadastro->imagem = $filenome;
					move_uploaded_file($file['tmp_name'], "img/cliente/{$filenome}");
				}
			
				$_SESSION['sucesso'] = 'Dados salvos com sucesso';		

				// if($_SESSION['CADASTRO']->tipocadastro_id == tipocadastro::getId('VENDEDOR')){
					// $cadastro->cadastro_id = $_SESSION['CADASTRO'];
				// }else{
					// $cadastro->cadastro_id = cadastro::vendedorPadrao();
				// }
				
				$cadastro->salva();

				if(request('popup')){
					print js("parent.opener.callback('cliente');this.close()");
					die();
				}
				else {
					if(trim(@$next)!=''){
						$this->afterSave($next,'clientes');
						return;
					}
				}
			}
		}
		
		if(request('action')=='sair'){$this->afterSave('sair','clientes',$cadastro);}

		$edicao = '';

		$t->parseBlock('BLOCK_TOOLBAR');

		$t->h1 = h1('Cliente '.$cadastro->nome);

		$edicao .= inputHidden('id', $cadastro->id);
		$edicao .= inputHidden('cadastro[id]', $cadastro->id);

		$edicao .= tag('div class="box-block" xstyle="width:49%;float:left;height:260px"',
					tag('h2', 'Dados básicos')
					
					.tag("table",
						tag("tr",
							tag("td",
								select('cadastro[st_ativo]', $cadastro->st_ativo, 'Ativo?:', array('S'=>'Sim','N'=>'Nao')).tag('span class="help"','Caso esteja inativo, o usuario não conseguirá logar no site')
								.inputSimples('cadastro[nome]', $cadastro->nome, 'Nome Completo:', 45, 50)
								
								.tag("table",
									tag("tr",
										tag("td style='width:140px !important;'",
											select('cadastro[tipocadastro_id]',$cadastro->tipocadastro_id,"Tipo de Cadastro",tipocadastro::opcoesCliente())
										)
										.tag("td",
											$this->boxVendedor($cadastro)
										)
									)
								)
								.select('cadastro[st_recebe_post]',$cadastro->st_recebe_post,"Receber Post do Blog",array('S'=>'SIM','N'=>'NAO'))
								//.select('cadastro[sexo]', $cadastro->sexo, 'Sexo:', array('M'=>'Masculino','F'=>'Feminino'))
								//.inputData('cadastro[data_nascimento]', formata_data_br($cadastro->data_nascimento), 'Data de nascimento:')
								//.select('cadastro[comoconheceu_id]', $cadastro->comoconheceu_id, 'Como conheceu:', comoconheceu::opcoes(), true)
								
								.select('cadastro[tabela]',$cadastro->tabela,"Tabela",array("tabela1"=>"Tabela 1","tabela2"=>"Tabela 2","tabela3"=>"Tabela 3"))
								.inputNumero("cadastro[over]",$cadastro->over,"Excedente ( % )",3,3)
							)
						)
					)					
										
		);

		$edicao .= tag('br clear="all"');
		$edicao .= $this->boxContato($cadastro,'xstyle="float:left;width:49%;height:138px"');
		$edicao .= tag('br clear="all"');
		$edicao .= $this->boxDocsPessoais($cadastro,'xstyle="float:left;width:49%;height:116px"');

		$edicao .= tag('br clear="all"');

		$edicao .= tag('div class="box-block" xstyle="width:49%;float:left;height:335px"',
					tag('h2', 'Dados da empresa')
					.inputSimples('cadastro[empresa]', $cadastro->empresa, 'Empresa:', 45, 50)
					//.inputSimples('cadastro[nome_fantasia]', $cadastro->nome_fantasia, 'Nome Fantasia:', 45, 50)
					.inputSimples('cadastro[cnpj]', $cadastro->cnpj, 'CNPJ:', 30, 25)
                    .inputSimples('cadastro[como_conheceu]', $cadastro->como_conheceu, 'Ramo de atuação:', 60, 100)
					.inputSimples('cadastro[inscricao_estadual]', $cadastro->inscricao_estadual, 'Inscricao Estadual:', 30, 30)
					.inputSimples('cadastro[inscricao_municipal]', $cadastro->inscricao_municipal, 'Inscricao Municipal:', 30, 30)
                    .inputSimples('cadastro[site]', $cadastro->site, 'Site:', 60, 100)
					.tag('br clear="all"')
		);
		
		$edicao .= "
		
			<script>
			
				var f = document.forms.formPrincipal;

				if(f.elements['cadastro[cnpj]']){
					$('#cadastro[cnpj]').mask('99.999.999/9999-99');
				}
				
			</script>
			
		";
		
		$edicao .= tag('br clear="all"');

		$edicao .= $this->boxEndereco($cadastro,'xstyle="width:49%;float:left;height:335px"');

		$edicao .= tag('br clear="all"');
		
		$edicao .= tag('div class="box-block"',
			tag('h2', 'Imagem do Cliente')
			.($cadastro->imagem!=''?"<img src='".PATH_SITE."img/cliente/{$cadastro->imagem}'":"")
			."<br />"
			.tag("p","A imagem deve ter 185x85px.")
			.tag('span','<input type="file" name="img_cliente" />')
			.tag('br clear="all"')
		);

		$edicao .= tag('br clear="all"');

		$edicao .= tag('div class="box-block"',
			tag('h2', 'Dados de Login')
			.$this->boxSenha($cadastro)
			.tag('br clear="all"')
		);

		$edicao .= tag('br clear="all"');

		$edicao .= '<br clear="all" />';

		// Mostra na tela os pedidos de orçamento que o cliente já fez
		if($cadastro->id){
		
			/*
			$edicao .= h1('Pedidos');

			$pedidos = results("SELECT * FROM pedido WHERE cadastro_id = {$cadastro->id} ORDER BY id DESC");

			if(sizeof($pedidos)==0){
				$edicao .= tag('p class="aux"','Ainda não existem pedidos associados a esse cliente');
			}
			else {
				$edicao .= '<table class="grid">';
				$edicao .= '	<tr>';
				$edicao .= '		<th>ID</th>';
				$edicao .= '		<th>Data da solicitação</th>';
				$edicao .= '		<th>Workflow</th>';
				$edicao .= '	</tr>';
			}

			foreach($pedidos as $pedido){

				$edicao .= '<tr>';
				$edicao .= '	<th>ID</th>';
				$edicao .= '	<th>Data da solicitação</th>';
				$edicao .= '	<th>Workflow</th>';
				$edicao .= '</tr>';

			}
			*/
		}


		$t->edicao = $edicao;

		if(!request('pop')){
			$this->montaMenu($t);
		}
		
		$this->show($t);
	}

	public function produtoexclusivo(){
		$this->filtraLogado();
		$this->filtraPermissao('produtoexclusivo');
		
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		
		$t->h1 = h1('Produtos Exclusivos para Clientes Especiais');
		
		$edicao = '';
		
		// Clientes
		$edicao .= tag("h2","Clientes Especiais");
		$edicao .= "<div class='box_sel_clientes'>";
		$query = query("SELECT * FROM cadastro WHERE tipocadastro_id=7");
		while($fetch=fetch($query)){
			$edicao .= tag("span data-id='{$fetch->id}'","{$fetch->nome}<br />{$fetch->email}");
		}
		$edicao .= "</div>";
		
		$edicao .= tag("div id='cliente_selecionado'","&nbsp;");

		$edicao .= tag("div class='box_msg'",tag("div id='px_mensagens'","&nbsp;"));
		
		// Produtos 
		$edicao .= "<br />";
		$edicao .= "<table class='pe_table'>";
		$edicao .= tag("tr",tag("td",tag("h2","Produtos ON")).tag("td",tag("h2","Produtos OFF")));
		$edicao .= "<tr><td>";
		
		$edicao .= "<div id='selecionar_produtos' class='box_sel_produtos'>";
		$edicao .= "</div>";
		
		$edicao .= "</td><td>";
		
		// Exclusivos
		$edicao .= "<div id='produtos_selecionados' class='box_sel_produtos box_prods_selecionados'>";
		$edicao .= "</div>";
		
		$edicao .= "</td></tr></table>";
		
		$edicao .= "<script>
			$(document).ready(function(){				 
				$('.box_sel_clientes span').bind('click',function(){
					_id = $(this).data('id');
					$.ajax({
						url : '".PATH_SITE."admin.php/selecionaCliente/',
						data : {cliente_id : _id},
						dataType : 'json',
						success : function(out){
							if(out[0]==1){
								$('#cliente_selecionado').html(out[1]);
								carregaProdutos(out[2]);
								carregaProdutosCliente(out[2]);
							}
						}
					});
					$('#px_mensagens').fadeOut();
				});
			});
			
			function carregaProdutos(cliente_id){
				$.ajax({
					url : '".PATH_SITE."admin.php/carregaProdutos/',
					data : {cliente : eval(cliente_id)},
					dataType : 'json',
					cache   :false,
					success : function(out){
						if(out[0]==1){
							for(i=0; i<out[1].length; i++){
								$('#selecionar_produtos').append(out[1][i]);
								$('#prods_'+i).fadeIn();
							}
							addAcoes();
						}
					}
				});
			}
			
			function carregaProdutosCliente(cliente_id){	
				$.ajax({
					url : '".PATH_SITE."admin.php/carregaProdutosCliente/',
					data : {cliente : eval(cliente_id)},
					dataType : 'json',
					cache   :false,
					success : function(result){
						if(result[0]==1){
							$('#produtos_selecionados').html(result[1]);
							addAcoes();
						}
					}
				});
			}
			
			function addAcoes(){
				$('.selprodutos').unbind('click');
				$('#produtos_selecionados span').unbind('click');	
				
				$('#selecionar_produtos span').bind('click',function(){
					if(!$('#cadastro_id').val()){
						$('#px_mensagens').addClass('msg_erro');
						$('#px_mensagens').html('Selecione um dos Clientes listados acima.');
						$('#px_mensagens').show();
						return false;
					}					
					
					item_id = $(this).data('id');
					cadastro_id = $('#cadastro_id').val();
					$.ajax({
						url : '".PATH_SITE."admin.php/addProdutoExclusivo/',
						data : {item : item_id, cliente : cadastro_id},
						dataType : 'json',
						cache : false,
						success : function(out){
							if(out[0]==1){
								$('#px_mensagens').removeClass('msg_erro');
								$('#px_mensagens').addClass('msg_sucesso');
								$('#px_mensagens').html(out[1]);
								$('#px_mensagens').show();
							}
							if(out[0]==0){
								$('#px_mensagens').removeClass('msg_sucesso');
								$('#px_mensagens').addClass('msg_erro');
								$('#px_mensagens').html(out[1]);								
								$('#px_mensagens').show();
							}
							out = '';
							setTimeout(function(){ $('#px_mensagens').fadeOut(); },3000);
							addAcoes();
						}
					});
					$('#produtos_selecionados').prepend($(this)[0].outerHTML);
					$(this).remove();
				});
				
				$('#produtos_selecionados span').bind('click',function(){	
					if(!$('#cadastro_id').val()){
						$('#px_mensagens').addClass('msg_erro');
						$('#px_mensagens').html('Selecione um dos Clientes listados acima.');
						$('#px_mensagens').show();
						return false;
					}					
					
					item_id = $(this).data('id');
					cadastro_id = $('#cadastro_id').val();
					$.ajax({
						url : '".PATH_SITE."admin.php/removeProdutoExclusivo/',
						data : {item : item_id, cliente : cadastro_id},
						dataType : 'json',
						cache : false,
						success : function(out){
							if(out[0]==1){
								$('#px_mensagens').removeClass('msg_erro');
								$('#px_mensagens').addClass('msg_sucesso');
								$('#px_mensagens').html(out[1]);
								$('#px_mensagens').show();
							}
							if(out[0]==0){
								$('#px_mensagens').removeClass('msg_sucesso');
								$('#px_mensagens').addClass('msg_erro');
								$('#px_mensagens').html(out[1]);								
								$('#px_mensagens').show();
							}
							out = '';
							setTimeout(function(){ $('#px_mensagens').fadeOut(); },3000);
							addAcoes();
						}
					});
					$('#selecionar_produtos').prepend($(this)[0].outerHTML);
					$(this).remove();
				});
			}
		</script>";
		
		$t->edicao = $edicao;
		
		$this->montaMenu($t);
		$this->show($t);
	}
	
	public function selecionaCliente(){
		$cadastro = new cadastro(intval(request('cliente_id')));
		$out = array();
		if($cadastro->id){
			$out[0] = 1;
			$out[1] = tag("span",
							inputHidden('cadastro_id',$cadastro->id)
							.tag("table",
								tag("tr",
									tag("td rowspan='2'","<img src='".PATH_SITE."img/cliente/{$cadastro->imagem}' width='110px' />")
									.tag("td","{$cadastro->nome} ({$cadastro->empresa})")
								)
								.tag("tr",
									tag("td","{$cadastro->email}")
								)
							)
						);
			$out[2] = $cadastro->id;
			echo json_encode($out);
		}
		
		die();
	}
	
	public function addProdutoExclusivo(){
		$item = new item(intval(request('item')));
		$cadastro = new cadastro(intval(request('cliente')));
		$out = array();
		if($cadastro->id && $item->id){
			$produtoexclusivo = new produtoexclusivo(array("cadastro_id"=>$cadastro->id,"item_id"=>$item->id));
			$produtoexclusivo->cadastro_id = $cadastro->id;
			$produtoexclusivo->item_id = $item->id;
			if($produtoexclusivo->salva()){
				$out[0] = 1;
				$out[1] = "Produto salvo para este cliente.";
			}else{
				$out[0] = 0;
				$out[1] = "Ocorreu um erro ao tentar salvar, verifique se há um cliente selecionado e selecione o produto novamente.";
			}
		}else{
			$out[0] = 0;
			$out[1] = "Ocorreu um erro ao tentar salvar, verifique se há um cliente selecionado e selecione o produto novamente.";
		}
		
		echo json_encode($out);
		die();
	}
	public function removeProdutoExclusivo(){
		$item = new item(intval(request('item')));
		$cadastro = new cadastro(intval(request('cliente')));
		$out = array();
		$produtoexclusivo = new produtoexclusivo(array("cadastro_id"=>$cadastro->id,"item_id"=>$item->id));
		if($produtoexclusivo->id){
			$produtoexclusivo->exclui();
			$out[0] = 1;
			$out[1] = "Produto removido com sucesso!";
		}else{
			$out[0] = 0;
			$out[1] = "Ocorreu um erro ao remover o produto, tente novamente.";
		}
		echo json_encode($out);
		die();
	}
	
	public function carregaProdutos(){
		$cadastro = new cadastro(intval(request('cliente')));
		$query = query($sql="
			SELECT 
			item.* FROM 
					item 
				WHERE 
					item.imagem<>'' 
					AND ( item.itemsku_id = 0 OR item.itemsku_id is NULL)
					AND item.id NOT IN(select produtoexclusivo.item_id from produtoexclusivo where produtoexclusivo.cadastro_id = {$cadastro->id})
			"			
		);
		$produtos = array();
		$i = 0;
		while($fetch=fetch($query)){
			$produtos[$i] = tag("span class='selprodutos' id='prods_{$i}' style='display:none;' data-id='{$fetch->id}'","<img src='".PATH_SITE."timthumb/timthumb.php?src=".PATH_SITE."img/produtos/{$fetch->imagem}&w=80' />");
			$i++;
		}
		
		$out = array();
		$out[0] = 1;
		$out[1] = $produtos;
		
		echo json_encode($out);
		$out = array();
		die();
	}
	
	public function carregaProdutosCliente(){
		$cadastro = new cadastro(intval(request('cliente')));
		$query = query("
			SELECT 
			item.* FROM 
					item 
				INNER JOIN produtoexclusivo ON(
					item.id = produtoexclusivo.item_id
					AND produtoexclusivo.cadastro_id = {$cadastro->id}
				)
				WHERE 
					item.imagem<>'' 
			"			
		);
		$produtos = '';
		while($fetch=fetch($query)){
			$produtos .= tag("span class='selprodutos' data-id='{$fetch->id}'","<img src='".PATH_SITE."timthumb/timthumb.php?src=".PATH_SITE."img/produtos/{$fetch->imagem}&w=80' />");
		}
		
		$out = array();
		$out[0] = 1;
		$out[1] = $produtos;
		
		echo json_encode($out);
		$out = array();
		die();
	}
	
	public function fornecedor(){

		$this->filtraLogado();
		$this->filtraPermissao('fornecedor');

		if(request('popup')){
			$t = new TemplateAdminPopup('admin/tpl.admin-cadastro-generico.html');
		}
		else {
			$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		}
		
		$t->h1 = h1('Fornecedores');

		if(request('action')=='editar'
			||substr(request('action'),0,6)=='salvar'){
			$this->fornecedorEditar($t);
			return;
		}

		if(request('action')=='excluir'){
			$cadastro = new cadastro(intval(request('id')));
			$cadastro->exclui();
		}

		$grid = new grid();
		
		$sql =
				"
				SELECT
					cadastro.id
					,cadastro.nome Nome
					,cadastro.email Email
					,cadastro.empresa Empresa
					,cadastro.fone_com FoneComercial
					,cadastro.fone_cel FoneCelular
					,cadastro.data_cadastro DataCadastro
					,cadastro.st_ativo Status
				FROM
					cadastro
				LEFT OUTER JOIN comoconheceu ON (
					cadastro.comoconheceu_id = comoconheceu.id
				)
				WHERE
					tipocadastro_id = ".tipocadastro::getId('FORNECEDOR')."
					
					ORDER BY
					cadastro.nome					
					";
		
		$grid->sql = $sql;

		$filtro = new filtro();

		$filtro->add_input('Nome','Nome:');
		$filtro->add_input('Email','Email:');
		$filtro->add_input('Empresa','Empresa:');
		$filtro->add_periodo('DataCadastro','Data cadastro:');

		$grid->filtro = $filtro ;

		$edicao = '';
		
		$edicao .= $this->boxExpExcel($sql,'Fornecedores',$filtro);
		$edicao .= $grid->render();
		
		$t->edicao = $edicao;
		
		$this->montaMenu($t);
		$this->show($t);
	}

	public function fornecedorEditar($t){

		$this->filtraLogado();
		$this->filtraPermissao('fornecedor');

		$cadastro = new cadastro(intval(request('id')));
		$cadastro->set_by_array(@$_REQUEST['cadastro']);

		$cadastro->tipocadastro_id = tipocadastro::getId('FORNECEDOR');

		if(substr(request('action'),0,6)=='salvar'){

			$erros = array();

			$next = substr(request('action'),7,strlen(request('action')));

			/*if(!$cadastro->validaCliente($erros)){
				$msg = '';
				foreach($erros['cadastro'] as $erro){
					$msg .= tag('p',$erro);
				}
				$_SESSION['erro'] = $msg;
				//$this->afterSave('','clientes');
			}*/
			
			if($cadastro->empresa == ''){
				$_SESSION['erro'] = tag('p','Digite a razão social');
			}
			else {
				$_SESSION['sucesso'] = 'Dados salvos com sucesso';
				$cadastro->salva();

				if(request('st_newsletter')=='S'){
					$newscadastro = new newscadastro(array('email'=>$cadastro->email));
					$newscadastro->nome = $cadastro->nome;
					$newscadastro->email = $cadastro->email;
					$newscadastro->st_ativo = 'S';
					$newscadastro->salva();
				}
				else {
					query("UPDATE `newscadastro` SET `st_ativo` = 'N' WHERE `email` = '{$cadastro->email}'");
				}
				
				if(request('popup')){
					print js("parent.opener.callback('cliente');this.close()");
					die();
				}
				else {
					if(trim(@$next)!=''){
						$this->afterSave($next,'clientes');
						return;
					}
				}
			}
		}

		$edicao = '';

		$t->parseBlock('BLOCK_TOOLBAR');
		
		$t->h1 = h1('Fornecedor '.$cadastro->nome);

		$edicao .= inputHidden('id', $cadastro->id);
		$edicao .= inputHidden('cadastro[id]', $cadastro->id);

		$edicao .= tag('div class="box-block" style="width:49%;float:left;height:140px"',
					tag('h2', 'Dados básicos')
					.select('cadastro[st_ativo]', $cadastro->st_ativo, 'Ativo?:', array('S'=>'Sim','N'=>'Nao'))
					.inputSimples('cadastro[nome]', $cadastro->nome, 'Nome para Contato:', 45, 50)
					//.$this->boxVendedor($cadastro)
					//.select('cadastro[sexo]', $cadastro->sexo, 'Sexo:', array('M'=>'Masculino','F'=>'Feminino'))
					//.inputData('cadastro[data_nascimento]', formata_data_br($cadastro->data_nascimento), 'Data de nascimento:')
					//.select('cadastro[comoconheceu_id]', $cadastro->comoconheceu_id, 'Como conheceu:', comoconheceu::opcoes(), true)
					//.checkbox('st_newsletter', 'S', 'Quer receber newsletter?', $cadastro->id ? $cadastro->getStNewsletterChecked() : 'checked' )
		);

		$edicao .= $this->boxContato($cadastro,'style="float:left;width:49%;height:140px"');
		//$edicao .= $this->boxDocsPessoais($cadastro,'style="float:left;width:49%;height:116px"');

		$edicao .= tag('br clear="all"');

		$edicao .= tag('div class="box-block" style="width:49%;float:left;height:250px"',
					tag('h2', 'Dados da empresa')
					.inputSimples('cadastro[empresa]', $cadastro->empresa, 'Raz&atilde;o Social:', 45, 50)
					.inputSimples('cadastro[nome_fantasia]', $cadastro->nome_fantasia, 'Nome Fantasia:', 45, 50)
					.inputSimples('cadastro[cnpj]', $cadastro->cnpj, 'CNPJ:', 25, 20)
					.inputSimples('cadastro[inscricao_estadual]', $cadastro->inscricao_estadual, 'Inscricao Estadual:', 30, 30)
					// .tag('br clear="all"')
					// .tag('br clear="all"')
					//.tag('div style="float:left; border:1px solid #999;"',		
					// tag('p', 'Dados Bancarios1')
					// .inputSimples('cadastro[agencia]', $cadastro->agencia, 'Agencia:', 6, 30)
					// .inputSimples('cadastro[conta]', $cadastro->conta, 'Conta:', 30, 30)
					// .inputSimples('cadastro[banco]', $cadastro->banco, 'Banco:', 30, 30)
					//)
					// .tag('div style="float:left; margin-left:10px; border:1px solid #999;"',		
					// tag('p', 'Dados Bancarios2')
					// .inputSimples('cadastro[agencia2]', $cadastro->agencia2, 'Agencia:', 6, 30)
					// .inputSimples('cadastro[conta2]', $cadastro->conta2, 'Conta:', 30, 30)
					// .inputSimples('cadastro[banco2]', $cadastro->banco2, 'Banco:', 30, 30)
					// )
					//.inputSimples('cadastro[inscricao_municipal]', $cadastro->inscricao_municipal, 'Inscricao Municipal:', 30, 30)
					.tag('br clear="all"')
		);

		$edicao .= $this->boxEndereco($cadastro,'style="width:49%;float:left;height:340px"');

		$edicao .= tag('br clear="all"');

		// $edicao .= tag('div class="box-block"',
			// tag('h2', 'Senha de acesso')
			// .$this->boxSenha($cadastro)
			// .tag('br clear="all"')
		// );		
		
		// $edicao .= tag('div class="box-block"',
			// tag('h2', 'Mensagem')			
			// .inputSimples('fornecedor[mensagem]', $cadastro->mensagem, 'Mensagem:', 45, 50)
		// );

		$edicao .= tag('br clear="all"');

		$edicao .= '<br clear="all" />';

		// Mostra na tela os pedidos de orçamento que o cliente já fez
		if($cadastro->id){
		
			/*
			$edicao .= h1('Pedidos');

			$pedidos = results("SELECT * FROM pedido WHERE cadastro_id = {$cadastro->id} ORDER BY id DESC");

			if(sizeof($pedidos)==0){
				$edicao .= tag('p class="aux"','Ainda não existem pedidos associados a esse cliente');
			}
			else {
				$edicao .= '<table class="grid">';
				$edicao .= '	<tr>';
				$edicao .= '		<th>ID</th>';
				$edicao .= '		<th>Data da solicitação</th>';
				$edicao .= '		<th>Workflow</th>';
				$edicao .= '	</tr>';
			}

			foreach($pedidos as $pedido){

				$edicao .= '<tr>';
				$edicao .= '	<th>ID</th>';
				$edicao .= '	<th>Data da solicitação</th>';
				$edicao .= '	<th>Workflow</th>';
				$edicao .= '</tr>';

			}
			*/
		}


		$t->edicao = $edicao;

		if(!request('popup')){
			$this->montaMenu($t);
		}
		
		$this->show($t);
	}
	
	public function lojas(){

		$this->filtraLogado();
		$this->filtraPermissao('lojas');

		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Lojas');

		if(request('action')=='editar'
			||substr(request('action'),0,6)=='salvar'){
			$this->lojasEditar($t);
			return;
		}

		if(request('action')=='excluir'){
			$cadastro = new cadastro(intval(request('id')));
			$cadastro->exclui();
		}

		$grid = new grid();

		$grid->sql = "SELECT
						id
						,nome Nome
						,email Email
						,empresa Empresa
						,fone_com FoneComercial
						,mapa_cidade Cidade
						,mapa_uf Estado
						,data_cadastro DataCadastro
						,st_ativo Status
					  FROM
						cadastro
					  WHERE
						1=1
					  AND
						tipocadastro_id = ".tipocadastro::getId('LOJA')
					  .($_SESSION['CADASTRO']->tipocadastro_id==tipocadastro::getId('VENDEDOR')
						?" AND (cadastro_id = ".decode($_SESSION['CADASTRO']->id)." OR id IN (SELECT cadastro_id FROM pedido WHERE vendedor_id = ".decode($_SESSION['CADASTRO']->id)."))"
						:""	);

		//print $grid->sql;

		$filtro = new filtro();

		$filtro->add_input('Nome','Nome:');
		$filtro->add_input('Email','Email:');
		$filtro->add_input('Empresa','Empresa:');
		$filtro->add_periodo('DataCadastro','Data cadastro:');

		$grid->filtro = $filtro ;

		$t->grid = $grid->render();

		$this->montaMenu($t);
		$this->show($t);
	}

	public function lojasEditar($t){

		$this->filtraLogado();
		$this->filtraPermissao('lojas');
		
		//printr(intval(request('id')));
		
		$cadastro = new cadastro(intval(request('id')));
		$cadastro->set_by_array(@$_REQUEST['cadastro']);

		//printr($cadastro);

		$cadastro->tipocadastro_id = tipocadastro::getId('LOJA');

		if(substr(request('action'),0,6)=='salvar'){

			if(request('senha_nova')||!$cadastro->id){
				$cadastro->senha = encode(request('senha_nova'));
			}

			$erros = array();

			$next = substr(request('action'),7,strlen(request('action')));

			if(!$cadastro->validaCliente($erros)){
				$msg = '';
				foreach($erros as $erro){
					$msg .= tag('p',$erro);
				}
				$_SESSION['erro'] = $msg;
				//$this->afterSave('','clientes');
			}
			else {

				$_SESSION['sucesso'] = 'Dados salvos com sucesso';			
				$cadastro->salva();

				if(trim(@$next)!=''){
					$this->afterSave($next,'lojas');
					return;
				}
			}
		}

		$edicao = '';

		$t->parseBlock('BLOCK_TOOLBAR');

		$t->h1 = h1('Representante '.$cadastro->nome);

		$edicao .= inputHidden('id', $cadastro->id);
		$edicao .= inputHidden('cadastro[id]', $cadastro->id);

		$edicao .= tag('div class="box-block" style="width:49%;float:left;xxheight:240px"',
					tag('h2', 'Dados básicos')
					.select('cadastro[st_ativo]', $cadastro->st_ativo, 'Ativo?:', array('S'=>'Sim','N'=>'Nao'))
					.inputSimples('cadastro[nome]', $cadastro->nome, 'Nome do Responsável:', 45, 100)
				//	.$this->boxVendedor($cadastro)
		);

		//$edicao .= $this->boxContato($cadastro,'style="float:left;width:49%;height:118px"');

		$edicao .= tag('br clear="all"');
		$arr=array("S" => "SIM", "N" => "NAO");
		$edicao .= tag('div class="box-block" style=""',
					tag('h2', 'Configuração site, dados que apareceram no site')
					.tag('div style="float:left;width:49%"'
						,/*select('cadastro[st_loja_site]', $cadastro->st_mapa_site, 'Loja aparece no site?:', 
						array('S'=>'Sim','N'=>'Nao')).tag('span class="help"','Define se a loja irá aparecer no onde-comprar, sim ou não')
						.*/inputSimples('cadastro[empresa]', $cadastro->empresa, 'Nome da Loja:', 45, 100)
						//.inputSimples('cadastro[loja_web_site]', $cadastro->mapa_website, 'Web-site Loja:', 45, 100)
						.inputSimples('cadastro[email]', $cadastro->email, 'e-mail da Loja:', 45, 100)
						.select('cadastro[st_aparece_mapa]', $cadastro->st_aparece_mapa, 'Loja aparece no site?', $arr)

						.inputSimples('cadastro[fone_com]', $cadastro->fone_com, 'Telelefone da Loja:', 16, 50)
						.inputSimples('cadastro[mapa_website]', $cadastro->mapa_website, 'Site:', 40, 30)
						/*.inputFile('file_loja_imagem', '', 'Imagem Loja:')
						.($cadastro->mapa_imagem!=''?tag('img width="60px" src="'.PATH_SITE.'img/lojas/1/'.$cadastro->mapa_imagem.'"'):'')*/
					)
					.tag('div style="float:left;width:49%"'
						,$this->boxEnderecoSite($cadastro)
					)
		);
		
		$edicao .= tag('br clear="all"');
		$edicao .= '<br clear="all" />';
		// Mostra na tela os pedidos de orçamento que o cliente já fez
		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}
	
	public function tabelaInfo($id=0){
		$item = new item($id);
		if($item->id){
			if($item->itemsku_id){
				$pai = new item($item->itemsku_id);
				$item->tabela1 = $item->tabela1>0?$item->tabela1:$pai->tabela1;
				$item->tabela2 = $item->tabela2>0?$item->tabela1:$pai->tabela2;
				$item->tabela3 = $item->tabela3>0?$item->tabela1:$pai->tabela3;
				$item->tabela1_st = $item->tabela1_st>0?$item->tabela1_st:$pai->tabela1_st;
				$item->tabela2_st = $item->tabela2_st>0?$item->tabela2_st:$pai->tabela2_st;
				$item->tabela3_st = $item->tabela3_st>0?$item->tabela3_st:$pai->tabela3_st;
			}
			
			$echo = "";
			// $echo .= tag("p","Tabela 1 : ".$item->tabela1);
			// $echo .= tag("p","Tabela 2 : ".$item->tabela2);
			// $echo .= tag("p","Tabela 3 : ".$item->tabela3);
			// $echo .= tag("p","Tabela 1 ST : ".$item->tabela1_st);
			// $echo .= tag("p","Tabela 2 ST : ".$item->tabela2_st);
			// $echo .= tag("p","Tabela 3 ST : ".$item->tabela3_st);
			
			$echo .= tag("div style='padding:10px;'",
				tag("table class='grade' style='border:1px solid #999;'",
					tag("tr",
						tag("th","Tabela 1")
						.tag("th","Tabela 2")
						.tag("th","Tabela 3")
					)
					.tag("tr",
						tag("td",$item->tabela1)
						.tag("td",$item->tabela2)
						.tag("td",$item->tabela3)
					)
					.tag("tr",
						tag("th","Tabela 1 ST")
						.tag("th","Tabela 2 ST")
						.tag("th","Tabela 3 ST")
					)
					.tag("tr",
						tag("td",$item->tabela1_st)
						.tag("td",$item->tabela2_st)
						.tag("td",$item->tabela3_st)
					)
				)
			);
			
			echo $echo;
		}
		die();
	}


	public function verProposta($id){
		$proposta = new proposta($id);
		$proposta->processa_html();
		
		
		print $proposta->html;
		die();
	}

	public function propostas(){
		
		$this->filtraLogado();
		$this->filtraPermissao('propostas');

		if(request('popup')){
            $t = new TemplateAdminPopup('admin/tpl.admin-cadastro-generico.html');
        }
        else {
            $t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		}
		
		$t->h1 = h1('Propostas');

		if(request('action')=='editar' || request('action')=='sair'
			||substr(request('action'),0,6)=='salvar'){
			$t = new TemplateAdmin('admin/tpl.admin-cadastro-proposta.html') ;
			$this->propostasEditar($t);
			return;
		}

		if(request('action')=='excluir'){
			$proposta = new proposta(intval(request('id')));
			$proposta->exclui();
		}
		
		if(request('action')=='propostasAlterarPropostaStatus'){
		
			$proposta = new proposta(intval(request('id')));
		
			$propostastatus = new propostastatus($proposta->propostastatus_id);
			if(strtoupper($propostastatus->descricao) == 'APROVADO'){
				$_SESSION['erro'] = 'Essa proposta já foi aprovada, não é permitido alterar';
				$this->setLocation('propostas/?action=editar&id='.$proposta->id);
			}
		
			$propostastatus = new propostastatus(intval(request('propostastatus_id')));
			$proposta->propostastatus_id = $propostastatus->id;
			
			if(strtoupper($propostastatus->descricao)=='REPROVADO'){
				$proposta->reprovado_motivo = request('reprovado_motivo');
			}
			else {
				$proposta->reprovado_motivo = '';
			}
			
			$_SESSION['sucesso'] = 'Status da proposta alterado com sucesso';
			
			$proposta->salva();
			
			if(strtoupper($propostastatus->descricao)=='APROVADO'){
			
				$proposta = new proposta(intval(request('id')));
				$pedido = new pedido($proposta->pedido_id);
				$cadastro = new cadastro($pedido->cadastro_id);
			
				$venda = new venda();
				
				$venda->pedido_id = $proposta->pedido_id;
				$venda->proposta_id = $proposta->id;
				$venda->data_cadastro = bd_now();
				// $venda->info = $proposta->info;
				
				$info = unserialize($proposta->info);
				
				$info['cliente_empresa'] = $cadastro->empresa;
				$info['cliente_cnpj'] = $cadastro->cnpj;
				$info['cliente_inscricao_estadual'] = $cadastro->inscricao_estadual;
				$info['cliente_logradouro'] = $cadastro->logradouro;
				$info['cliente_complemento'] = $cadastro->complemento;
				$info['cliente_numero'] = $cadastro->numero;
				$info['cliente_cidade'] = $cadastro->cidade;
				$info['cliente_uf'] = $cadastro->uf;
				$info['cliente_cep'] = $cadastro->cep;
				$info['cliente_fone_res'] = $cadastro->fone_res;
				$info['cliente_fone_com'] = $cadastro->fone_com;
				$info['cliente_fone_cel'] = $cadastro->fone_cel;
				$info['cliente_email'] = $cadastro->email;
				$info['cliente_nome'] = $cadastro->nome;
				
				printr($info);
				
				$venda->info = serialize($info);
				
				$venda->salva();
				
				// Foi criado o registro da venda, use essa tela para complementar os dados
				$_SESSION['sucesso'] = 'Foi criado o registro da venda, use essa tela para complementar os dados';
				
				$pedidostatus = new pedidostatus(array('descricao'=>'Orçamento Aprovado'));
				if($pedidostatus->id){
					query("UPDATE pedido SET pedidostatus_id = {$pedidostatus->id} WHERE id = {$pedido->id}");
				}
				
				// Manda para a pagina da venda
				$this->setLocation('vendas/?action=editar&id='.$venda->id);
				
			}
			
			// die();
			$this->setLocation('propostas/?action=editar&id='.$proposta->id);
		}
		
		// propostasAddItem
		
		// if(request('action')=='propostasAddItem'){
			
		// 	$proposta = new proposta(intval(request('id')));

		// 	$pai = $item = new item(request('item_id'));
		// 	//$item = new item(request('item_id'));
		// 	$gravacao = new gravacao(request('gravacao_id'));
		// 	$materia_prima = new materia_prima(request('materia_prima_id'));
		// 	$cor = new cor(request('cor_id'));
			
		// 	$imagem = $item->imagem;
			
		// 	if($item->itemsku_id){
		// 		$pai = new item($item->itemsku_id);
		// 	}

			
		// 	if($cor->id>0){
		// 		$itemcor = new itemcor(array('item_id'=>$item->id,'cor_id'=>$cor->id));
		// 		if($itemcor->imagem){
		// 			$imagem = $itemcor->imagem;
		// 		}
		// 	}

		// 	$final = '';
		// 	$descric = '';    

		// 	if($pai->descricao) $descric = 'Descrição: '.$pai->descricao;
			
		// 	$info = unserialize($proposta->info);
		// 	$info['item'][] = array (
		// 		'item_id' => $item->id
		// 		,'nome' => $item->nome
		// 		,'referencia' => $item->referencia
		// 		,'imagem' => config::get('URL').'img/produtos/'.$imagem
		// 		,'item_qtd' => intval(request('item_qtd')) 
		// 		,'item_qtd2' => intval(request('item_qtd2')) 
		// 		,'item_qtd3' => intval(request('item_qtd3')) 
		// 		,'cor_id' => $cor->id
		// 		,'cor_nome' => $cor->id
		// 		,'descricao'=> $descric . PHP_EOL . $final
		// 		,'gravacao_id' => $gravacao->id
		// 		,'gravacao_nome' => $gravacao->nome
		// 		,'materia_prima_id' => $materia_prima->id
		// 		,'materia_prima_nome' => $materia_prima->nome
		// 		,'txt' => ($cor->id>0?"\nCor: {$cor->nome}":"")
		// 				 .($gravacao->id>0?"\nGravação: {$gravacao->nome}":"")
		// 				 .($materia_prima->id>0?"\nMatéria Prima: {$materia_prima->nome}":"")
		// 	);
		
			
		// 	$_SESSION['sucesso'] = 'Item adicionado com sucesso na proposta';
			
		// 	$proposta->info = serialize($info);
		// 	$proposta->salva();
		// 	$proposta->processa_html();
			
		// 	$this->setLocation('propostas/?action=editar&id='.$proposta->id);
		// }

		// propostasAddItem		
		if(request('action')=='propostasAddItem'){
		
			$proposta = new proposta(intval(request('id')));

			$pai = $item = new item(request('item_id'));
			$gravacao = new gravacao(request('gravacao_id'));
			$materia_prima = new materia_prima(request('materia_prima_id'));
			// $cor = new cor(request('cor_id'));
			
			$imagem = $item->imagem;
			
			// if($cor->id>0){
			// 	$itemcor = new itemcor(array('item_id'=>$item->id,'cor_id'=>$cor->id));
			// 	if($itemcor->imagem){
			// 		$imagem = $itemcor->imagem;
			// 	}
			// };
            if(request('cor_id')){
                $item = new item(request('cor_id'));
                $item->preco = $pai->preco;
                //$pedidoitem->item_id = $item->id;
			}

				if($item->itemsku_id){
				$pai = new item($item->itemsku_id);
			}

			$imagem = $item->imagem;

			$final = '';
            $resp = '';
            $descric = '';            

            if($pai->largura) $resp .= ' Largura '.$pai->largura.PHP_EOL;
            if($pai->altura) $resp .= ' Altura '.$pai->altura.PHP_EOL;
            if($pai->profundidade) $resp .= ' Profundidade '. $pai->profundidade.PHP_EOL;
			if(!(strlen($resp) <= 5)) $final .= ' Dimensões:'.PHP_EOL.$resp.PHP_EOL;            
			if($gravacao->id) $final .= "Gravação: ". $gravacao->nome.PHP_EOL;
            $cor = new cor($item->cor_id);
            if($cor->id) $final .= 'Cor: '. $cor->nome.PHP_EOL;
            if($pai->descricao) $descric = 'Descrição: '.$pai->descricao;
			
			
			$info = unserialize($proposta->info);
			$info['item'][] = array (
				'item_id' => $item->id
				,'nome' => $item->nome
				,'referencia' => $item->referencia
				// ,'imagem' => config::get('URL').'img/produtos/'.$imagem
				,'imagem' => config::get("URL")."timthumb/timthumb.php?src=".config::get("URL")."img/produtos/{$imagem}&w=120"
				,'item_qtd' => intval(request('item_qtd')) 
				,'item_qtd2' => intval(request('item_qtd2')) 
				,'item_qtd3' => intval(request('item_qtd3')) 
				// ,'descricao'=> $descric . PHP_EOL . $final . PHP_EOL . $pedidoitem->info_txt
				,'descricao'=> $descric . PHP_EOL . $final
				,'cor_id' => $cor->id
				,'cor_nome' => $cor->id
				,'gravacao_id' => $gravacao->id
				,'gravacao_nome' => $gravacao->nome
				,'materia_prima_id' => $materia_prima->id
				,'materia_prima_nome' => $materia_prima->nome
				,'txt' => ($cor->id>0?"\nCor: {$cor->nome}":"")
						 .($gravacao->id>0?"\nGravação: {$gravacao->nome}":"")
						 .($materia_prima->id>0?"\nMatéria Prima: {$materia_prima->nome}":"")
				
				,'itemsku_id'=>$item->itemsku_id
			);
						
			$_SESSION['sucesso'] = 'Item adicionado com sucesso na proposta';
			
			$proposta->info = serialize($info);
			$proposta->salva();
			$proposta->processa_html();
			
			$this->setLocation('propostas/?action=editar&id='.$proposta->id);
			
		}


		if(request('action')=='propostasVerProposta'){

			$proposta = new proposta(intval(request('id')));

			print tag('div style="padding:2px"',
						tag('a href="'.PATH_SITE.'admin.php/propostas/?action=editar&id='.$proposta->id.'"','voltar para a proposta')
						.tag('a href="javascript:window.print()"','imprimir'));

			print $proposta->html;
			die();
		}

		// Deleta um item da proposta
		if(request('action')=='propostasDelItem'){
		
			$proposta = new proposta(intval(request('id')));
		
			$propostaitemindice = request('propostaitemindice');
			$info = unserialize($proposta->info);
			
			array_splice($info['item'], $propostaitemindice, 1);
		
			
			$proposta->info = serialize($info);
			$proposta->salva();
			$proposta->processa_html();
			
			
			
			$_SESSION['sucesso'] = 'Foi excluido o item da sua proposta';
			$this->setLocation('propostas/?action=editar&id='.$proposta->id);
			die();
		}
		
		$grid = new grid();

		$sql =
			"
			SELECT
				proposta.id
				,concat(pedido.id,'-',proposta.numero) proposta
				,cadastro.empresa
				,cadastro.nome contato
				,vendedor.nome representante
				-- ,concat(propostastatus.descricao,case when upper(propostastatus.descricao) = 'REPROVADO' then concat('-',proposta.reprovado_motivo) else '' end)  status
				,pedido.data_cadastro
			FROM
				proposta
			INNER JOIN pedido ON (
				pedido.id = proposta.pedido_id
			)
			INNER JOIN cadastro ON (
				pedido.cadastro_id = cadastro.id
			)
			LEFT OUTER JOIN comoconheceu ON (
				cadastro.comoconheceu_id = comoconheceu.id
			)
			INNER JOIN cadastro AS vendedor ON (
				pedido.vendedor_id = vendedor.id
			)
			INNER JOIN pedidostatus ON (
				pedido.pedidostatus_id = pedidostatus.id
			)
			INNER JOIN propostastatus ON (
				proposta.propostastatus_id = propostastatus.id
			)
			".($_SESSION['CADASTRO']->tipocadastro_id==tipocadastro::getId('VENDEDOR')
			?" AND pedido.vendedor_id = ".decode($_SESSION['CADASTRO']->id)." "
			:""	)."
			ORDER BY
				pedido.id DESC, proposta.numero DESC
			";
			
		$grid->sql = $sql;

		$filtro = new filtro();

		$filtro->begin_block();
		$filtro->add_input('id','Num. Orçamento:');
		$filtro->add_input('empresa','Empresa:');
		$filtro->end_block();
		$filtro->begin_block();
		$filtro->add_input('contato','Contato:');
		$filtro->add_periodo('data_cadastro','Per&iacute;odo:');
		$filtro->end_block();
		$filtro->add_clear();
		$filtro->botao_novo = false;
		
		$grid->titulo_filtro = 'status,representante';

		$grid->metodo = 'propostas';
		$grid->filtro = $filtro ;

		$edicao = '';
		
		// $edicao .= $this->boxExpExcel($sql,'Orçamentos',$filtro);
		$edicao .= $grid->render();
		
		$t->edicao = $edicao;
		
		$this->montaMenu($t);
		$this->show($t);
	
	}
	
	public function propostasEditar(){
		
		//$t = new TemplateAdmin('admin/tpl.admin-cadastro-proposta.html') ;
		$t = new TemplateAdminPop('admin/tpl.admin-cadastro-proposta.html');
	
		
		$proposta = new proposta(intval(request('id')));
		
		if(!$proposta->id){
			$_SESSION['erro'] = 'Proposta não encontrada';
			$this->setLocation('');
		}
		
		if($proposta->data_envio!=''){
			// $_SESSION['erro'] = 'Essa proposta já foi enviada, não é possível editar';
			// $this->setLocation("orcamentos/?action=editar&id={$proposta->pedido_id}");
		}
		
		if(substr(request('action'),0,6)=='salvar'){
			
			// Checa se a proposta já não está aprovada
			$propostastatus = new propostastatus($proposta->propostastatus_id);
			if(strtoupper($propostastatus->descricao) == 'APROVADO'){
				$_SESSION['erro'] = 'Essa proposta já foi aprovada, não é permitido alterar';
			}
			else {
			
				// $proposta->propostastatus_id = 1;
				$proposta->html = '';
				// $proposta->info = serialize($_REQUEST['proposta']);
				
				$info = unserialize($proposta->info);
				
				foreach($_REQUEST['proposta'] as $key => $value){
					if(!is_array(@$info[$key])){
						$info[$key] = $value;
					}
				}
				
				$escolheuOpcao = true;
				$temAlgumItem = false;
				
				foreach(@$_REQUEST['proposta']['item'] as $key => $value){
					
					if(!is_array(@$info[$key])){
						// printr($value);
						$info['item'][$key] = $value;
						$opcao = intval(@$info['item'][$key]['opcao']);
						if($opcao==0){
							$escolheuOpcao = false;
						}
						$temAlgumItem = true;
					}
				}

				// printr($info);
				
				$proposta->info = serialize($info);
				
				$proposta->salva();
				$proposta->processa_html();

				$sucesso = "Sua proposta foi editada com sucesso";
				
				if($escolheuOpcao && $temAlgumItem){
					$sucesso .= " <a href='".PATH_SITE."admin.php/orcamentos/?action=pedidosProposta2Venda&id={$proposta->pedido_id}&proposta_id={$proposta->id}' style='font-size:14px;font-weight:bold'>clique aqui para gerar o pedido de venda</a>" ;
				}
				
				$_SESSION['sucesso'] = tag('p', $sucesso);
			
			}
		}
		
		if(request('action')=='sair'){$this->afterSave('sair','propostas');}
		
		$pedido = new pedido($proposta->pedido_id);
		$cadastro = new cadastro($pedido->cadastro_id);
		$vendedor = new cadastro($pedido->vendedor_id);
		$pedidostatus = new pedidostatus($pedido->pedidostatus_id);
		$propostastatus = new propostastatus($proposta->propostastatus_id);

		$t->proposta = $proposta;
		$t->propostastatus = $propostastatus;
		$t->pedido = $pedido;
		$t->cadastro = $cadastro;
		
		// OPCOES DE PROPOSTASTATUS
		foreach(propostastatus::opcoes() as $id=>$descricao){
			// $t->list_propostastatus = new propostastatus($id);
			$t->list_propostastatus = new propostastatus($id);
			$t->parseBlock('BLOCK_LIST_PROPOSTASTATUS', true);
		}
		
		$i=0;
		$cont = 0;
		$total1 = 0;
		$total2 = 0;
		$total3 = 0;

		// // NOVO CALCULO PARA DEFINIR O TOTAL NA TPL
		// foreach($proposta->itens() as $propostaitem){
		
			
		// }
		// // SOMANDO O TOTAL DOS PRODUTOS COTADOS 
		// $total = $total1 + $total2 + $total3;
		
		foreach($proposta->itens() as $propostaitem){
			
			//printr($propostaitem);

			$total1 += toFloat($propostaitem->sub_total);
		 	$total2 += toFloat($propostaitem->sub_total2);
		 	$total3 += toFloat($propostaitem->sub_total3);
			
			$item = new item($propostaitem->item_id);
		
			$propostaitem->disabled = ($proposta->data_envio!=''?'readonly class="disabled"':'');
		
			// printr($propostaitem);
		
			$t->list_item = $item ;
			$t->list_pedidoitem = $propostaitem ;
			
			$cont++;
			$t->cont = $cont;
			
			$t->propostaindice = $i;
			
			// Caso ainda nao tenha sido enviada
			if($proposta->data_envio==''){
				$t->parseBlock('BLOCK_LIST_PEDIDOITEMPROPOSTA_EXCLUI');
			}
			
			// $t->parseBlock('BLOCK_LIST_PEDIDOITEM', true);	
			$t->parseBlock('BLOCK_LIST_PEDIDOITEMPROPOSTA', true);
			$t->parseBlock('BLOCK_LIST_PEDIDOITEMPROPOSTA_FORNECEDOR', true);
			$t->parseBlock('BLOCK_LIST_PEDIDOITEMPROPOSTA_SCRIPT', true);
			
			$i+=1;
			
			$temItem = true;
		}

		// // SOMANDO O TOTAL DOS PRODUTOS COTADOS 
		$total = $total1 + $total2 + $total3;
		$t->total_pedido = money($total);

		foreach(explode("\n",$this->config->PADRAO_OPCAO_FRETE) as $opcao_frete){
			$opcao_frete = trim(str_replace(array("<br />","\n","\r"),"", $opcao_frete));
			$t->opcao_frete_selected = ($opcao_frete == $proposta->info_frete ? "selected" : "");
			$t->opcao_frete = $opcao_frete;
			$t->parseBlock('BLOCK_OPCAO_FRETE', true);
		}
		
		foreach(explode("\n",$this->config->PADRAO_FORMA_PAGAMENTO) as $forma_pagamento){
			$forma_pagamento = trim(str_replace(array("<br />","\n","\r"),"", $forma_pagamento));
			$t->forma_pagamento_selected = ($forma_pagamento == $proposta->info_forma_pagamento ? "selected" : "");
			$t->forma_pagamento = $forma_pagamento;
			$t->parseBlock('BLOCK_FORMA_PAGAMENTO', true);
		}
		
		if($this->config->HABILITA_VARIACAO_PRECO=='S' && $proposta->data_envio == '' ){
			$t->parseBlock('BLOCK_OPCAO_PRECO_SUGESTAO');
		}
		
		if($proposta->data_envio==''){
			$t->parseBlock('BLOCK_ADICIONE');
			$t->parseBlock('BLOCK_EDICAO_PROPOSTA');
		}
		else {
			$t->parseBlock('BLOCK_VISUALIZA_PROPOSTA');
		}

		$this->show($t);
	}

	public function propostasEditarEmpresa(){

		$this->filtraLogado();
		
		if(request('popup')){
			$t = new TemplateAdminPopup('admin/tpl.admin-cadastro-generico.html');
		}
		else {
			$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		}
		
		// $this->filtraPermissao('clientes');

		$proposta = new proposta(intval(request('id')));

		if(substr(request('action'),0,6)=='salvar'){

			$proposta = new proposta(intval(request('id')));
			
			if(substr(request('action'),0,6)=='salvar'){
				
				// $proposta->propostastatus_id = 1;
				$proposta->html = '';
				
				$info = unserialize($proposta->info);
				
				// $proposta->info = serialize($_REQUEST['proposta']);
				// printr($info);
				
				foreach( $_REQUEST['proposta'] as $key => $value ){
					$info[$key] = $value;
				}
				
				// printr($info);
				// printr($_REQUEST['proposta']);
				// printr($proposta);
				
				$proposta->info = serialize($info);
				$proposta->salva();
				// $proposta->processa_html();

				$_SESSION['sucesso'] = tag('p', "Sua proposta foi editada com sucesso");
				
				if(request('popup')){
					print js("parent.opener.callback('empresa');this.close()");
					die();
				}
			}
		}

		$edicao = '';

		$t->parseBlock('BLOCK_TOOLBAR');

		$t->h1 = h1('Proposta Empresa '.$proposta->getCodigoProposta());

		$edicao .= inputHidden('id', $proposta->id);
		// $edicao .= inputHidden('proposta[id]', $proposta->id);

		$edicao .= tag('div class="box-block"',
					tag('h2', 'Dados da empresa')
					.inputSimples('proposta[empresa_empresa]', $proposta->info_empresa_empresa, 'Raz&atilde;o Social:', 45, 50)
					.inputSimples('proposta[empresa_nome_fantasia]', $proposta->info_empresa_nome_fantasia, 'Nome Fantasia:', 45, 50)
					.inputSimples('proposta[empresa_cnpj]', $proposta->info_empresa_cnpj, 'CNPJ:', 20, 20)
					.inputSimples('proposta[empresa_inscricao_estadual]', $proposta->info_empresa_inscricao_estadual, 'Inscricao Estadual:', 30, 30)
					// .inputSimples('proposta[empresa_inscricao_municipal]', $proposta->info_empresa_inscricao_municipal, 'Inscricao Municipal:', 30, 30)
					.tag('br clear="all"')
		);
		
		$edicao .= tag('div class="box-block"',
					tag('h2', 'Dados de contato')
					.inputSimples('proposta[empresa_telefone]', $proposta->info_empresa_telefone, 'Telefone:', 45, 100)
					.inputSimples('proposta[empresa_email]', $proposta->info_empresa_email, 'Email:', 45, 100)
					.tag('br clear="all"')
		);
		
		$edicao .= tag('div class="box-block" ',
					tag('h2', 'Endereço')
					.inputSimples('proposta[empresa_cep]', $proposta->info_empresa_cep, 'CEP:', 10, 10)
					.inputSimples('proposta[empresa_logradouro]', $proposta->info_empresa_logradouro, 'Logradouro:', 45, 50)
					.inputSimples('proposta[empresa_numero]', $proposta->info_empresa_numero, 'Numero:', 15, 15)
					.inputSimples('proposta[empresa_complemento]', $proposta->info_empresa_complemento, 'Complemento:', 30, 30)
					.inputSimples('proposta[empresa_bairro]', $proposta->info_empresa_bairro, 'Bairro:', 30, 30)
					.inputSimples('proposta[empresa_cidade]', $proposta->info_empresa_cidade, 'Cidade:', 30, 30)
					.inputSimples('proposta[empresa_uf]', $proposta->info_empresa_uf, 'Estado:', 4, 4)
					.tag('br clear="all"')
		);
		
		$edicao .= tag('br clear="all"');
		
		// Mostra na tela os pedidos de orçamento que o cliente já fez

		$t->edicao = $edicao;

		if(!request('popup')){
			$this->montaMenu($t);
		}
		
		$this->show($t);
	}
	
	public function propostasNovo($t){

		if(request('orcamento_id')){
		
			try {
		
				$pedido = new pedido(intval(request('orcamento_id')));
				
				if(!$pedido->id){
					throw new Exception('Não foi possível localizar o orçamento');
				}
				
				// Se o usuario logado for um vendedor, o cliente precisa ser dele
				if($_SESSION['CADASTRO']->tipocadastro_id==tipocadastro::getId('VENDEDOR')){
					if($pedido->vendedor_id!=$_SESSION['CADASTRO']->id){
						throw new Exception('Este orçamento não está associado ao seu cadastro');
					}
				}
				
				$vendedor = new cadastro($pedido->vendedor_id);
				
				$proposta = new proposta();

				$proposta->pedido_id = $pedido->id;
				$proposta->propostastatus_id = 1;
				$proposta->html = '';
				
				$info = array();
				
				// Loop nos itens
				foreach($pedido->get_childs('pedidoitem') as $pedidoitem){
				
					$item = new item($pedidoitem->item_id);
					
					$imagem = $item->imagem;
			
					// $info = unserialize($proposta->info);
					$info['item'][] = array
					(
						'item_id' => $item->id
						,'nome' => $item->nome
						,'referencia' => $item->referencia
						,'imagem' => config::get('URL').'img/produtos/'.$imagem
						,'item_qtd' => $pedidoitem->item_qtd
						,'item_qtd2' => $pedidoitem->item_qtd2
						,'item_qtd3' => $pedidoitem->item_qtd3
						,'descricao' => $item->descricao
					);
				
				}
				
				$info['validade_proposta'] = "15 dias";
				$info['prazo_entrega'] ="A combinar";
				$info['obs'] = config::get('TEXTO_PADRAO_OBSERVACAO');
				$info['forma_pagamento'] = '';
				$info['local_entrega'] = '';
				$info['local_cobranca'] = '';
				$info['total'] = '';
				$info['total_icms'] = '';
				$info['total_impostos'] = '';
				$info['vendedor'] = $vendedor->nome;
				
				$proposta->info = serialize($info);
				$proposta->salva();
						
				$_SESSION['sucesso'] = 'Complemente os dados da proposta';
				$this->setLocation('propostas/?action=editar&id='.$proposta->id);
						
			}
			catch (Exception $ex){
				$_SESSION['erro'] = $ex->getMessage();
			}
		}

		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Criar nova proposta');

		$this->montaMenuSimples($t);

		$edicao = inputSimples('orcamento_id','','Digite o número do orçamento', 60, 100);

		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}
	
	public function propostas2venda(){
		
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-proposta-venda.html') ;
		if(request('pop')){
			$t = new TemplateAdminPop('admin/tpl.admin-cadastro-proposta-venda.html') ;
		}
		
		// Valida proposta
		if(!$_SESSION['propostavenda']->id){
			$_SESSION['erro'] = 'Proposta não encontrada';
			$this->setLocation('');
		}
		
		// Checa se ja gerou venda para a proposta
		if(rows(query("SELECT * FROM venda WHERE pedido_id = ".$_SESSION['propostavenda']->pedido_id))>0){
			$_SESSION['erro'] = 'Já foi gerado um pedido de venda para este orçamento, não é possível gerar mais um';
			$this->setLocation('orcamento/?action=editar&id='.$_SESSION['propostavenda']->pedido_id);
		}
				
		
		// Carrega dados do pedido
		$pedido = new pedido($_SESSION['propostavenda']->pedido_id);
		
		// Carrega dados do cadastro
		$cadastro = new cadastro($pedido->cadastro_id);
		
		// Deleta um item da proposta de venda
		if(request('action')=='propostas2vendaDelItem'){
			$propostavendaindice = request('propostavendaindice');			
			$info = unserialize($_SESSION['propostavenda']->getInfo());
			array_splice($info['item'], $propostavendaindice, 1);
			$_SESSION['propostavenda']->setInfo(serialize($info));
			$_SESSION['sucesso'] = 'Foi excluido o item';
		}
		
		if(request('action')=='salvar' ||request('action')=='gerarPedido'){
			
			$propostastatus = new propostastatus($_SESSION['propostavenda']->propostastatus_id);
			
			$_SESSION['propostavenda']->html = '';
			
			$info = unserialize($_SESSION['propostavenda']->getInfo());
			
			foreach($_REQUEST['propostavenda'] as $key => $value){
				if(!is_array(@$info[$key])){
					$info[$key] = $value;
				}
			}
			
			if(isset($_REQUEST['propostavenda']['item'])){	
				foreach(@$_REQUEST['propostavenda']['item'] as $key => $value){
					if(!is_array(@$info[$key])){
						$info['item'][$key] = $value;
					}
				}
			}
			
			// Pega todos os valores salvos no cadastro e joga para a sessao da venda
			foreach(get_class_vars(get_class($cadastro)) as $key => $value ){
				$info['cliente_'.$key] = $cadastro->$key;
			}
			
			$info['local_entrega_html'] = nl2br($info['local_entrega']);
			
			$_SESSION['propostavenda']->setInfo(serialize($info));
			
			$erros = array();
			
			$total_item = tofloat($_SESSION['propostavenda']->info_total_item);
			$total_icms = tofloat($_SESSION['propostavenda']->info_total_icms);
			$total = tofloat($_SESSION['propostavenda']->info_total);
		
			if(sizeof($erros)==0){
			
				if(request('action')=='gerarPedido'){
				
					// Valida se selecionou a opcao de preco para todas e digitou o nome
					if(isset($_REQUEST['propostavenda']['item'])){	
						foreach(@$_REQUEST['propostavenda']['item'] as $key => $value){
						
							$item = (object) $value;

							// Valida o nome
							if(!is_set($item->nome)){
								$_SESSION['erro'] = 'Nome inválido para o item '.$item->referencia;
								$this->setLocation('propostas2venda/?action=editar&pop=1&id='.$_SESSION['propostavenda']->id);
							}
						
							// Valida a opcao
							if(intval(@$item->opcao)==0){
								$_SESSION['erro'] = 'Escolha uma opção de preço para o item '.$item->referencia;
								$this->setLocation('propostas2venda/?action=editar&pop=1&id='.$_SESSION['propostavenda']->id);
							}
							
						}
					}
					
					// Valida se digitou os precos
					$info = unserialize($_SESSION['propostavenda']->getInfo());

					$itens = array();
					foreach(@$_SESSION['propostavenda']->itens() as $key => $value){
					
						$i = sizeof($itens);
						$item = (object) $value;
						
						// printr($item);
						
						if(tofloat(@$item->preco_opcao)==0){
							$_SESSION['erro'] = 'Preço inválido para o item '.$item->referencia;
							$this->setLocation('propostas2venda/?action=editar&pop=1&id='.$_SESSION['propostavenda']->id);
						}
						
						if(tofloat(@$item->item_qtd_opcao)==0){
							$_SESSION['erro'] = 'Quantidade inválida para o item '.$item->referencia;
							// die();
							$this->setLocation('propostas2venda/?action=editar&pop=1&id='.$_SESSION['propostavenda']->id);
						}
						
						$tmp_preco_opcao = $item->preco_opcao;
						$tmp_item_qtd_opcao = $item->item_qtd_opcao;
						$tmp_sub_total_opcao = $item->sub_total_opcao;
					
						$item->preco = $tmp_preco_opcao;
						$item->item_qtd = $tmp_item_qtd_opcao;
						$item->sub_total = $tmp_sub_total_opcao;
						
						unset($item->preco2);
						unset($item->item_qtd2);
						unset($item->sub_total2);
						
						unset($item->preco3);
						unset($item->item_qtd3);
						unset($item->sub_total3);
						
						$itens[$i] = (array)$item;
					}
					
					$info['item'] = $itens;
					
					// Grava
					$_SESSION['propostavenda']->setInfo(serialize($info));
										
					// Salva venda
					$venda = new venda();
					
					$venda->pedido_id = $_SESSION['propostavenda']->pedido_id;
					$venda->proposta_id = $_SESSION['propostavenda']->id;
					$venda->data_cadastro = bd_now();
					// $venda->info = $proposta->info;
					
					$info = unserialize($_SESSION['propostavenda']->getInfo());
				
					// Alimenta dados do cliente
					$info['cliente_empresa'] = $cadastro->empresa;
					$info['cliente_cnpj'] = $cadastro->cnpj;
					$info['cliente_inscricao_estadual'] = $cadastro->inscricao_estadual;
					$info['cliente_logradouro'] = $cadastro->logradouro;
					$info['cliente_complemento'] = $cadastro->complemento;
					$info['cliente_numero'] = $cadastro->numero;
					$info['cliente_cidade'] = $cadastro->cidade;
					$info['cliente_uf'] = $cadastro->uf;
					$info['cliente_cep'] = $cadastro->cep;
					$info['cliente_fone_res'] = $cadastro->fone_res;
					$info['cliente_fone_com'] = $cadastro->fone_com;
					$info['cliente_fone_cel'] = $cadastro->fone_cel;
					$info['cliente_email'] = $cadastro->email;
					$info['cliente_nome'] = $cadastro->nome;
					
					$info['obs_venda'] = '';
					
					$venda->data_envio_pedido = to_bd_date($_SESSION['propostavenda']->info_data_emissao);
					$venda->data_previsao_entrega = to_bd_date($_SESSION['propostavenda']->info_prazo_entrega);
					$venda->data_previsao_entrega = to_bd_date($_SESSION['propostavenda']->info_prazo_entrega);
					
					$venda->info = serialize($info);
					
					$venda->salva();
					
					// Foi criado o registro da venda, use essa tela para complementar os dados
					$_SESSION['sucesso'] = 'Foi criado o registro da venda, use essa tela para complementar os dados';

					// Muda o status do pedido de venda
					$pedidostatus = new pedidostatus(array('descricao'=>'Orçamento Aprovado'));
					if($pedidostatus->id){
						query("UPDATE pedido SET pedidostatus_id = {$pedidostatus->id} WHERE id = {$pedido->id}");
					}
					
					// Muda o status da proposta 
					$propostastatus = new propostastatus(array('descricao'=>'APROVADO'));
					if($propostastatus->id){
						query("UPDATE proposta SET propostastatus_id = {$propostastatus->id} WHERE id = ".$_SESSION['propostavenda']->id);
					}
					
					// Mostra a mensagem de sucesso do pedido de venda
					$_SESSION['sucesso'] = tag('p', "O pedido de venda foi gerado com sucesso, use o formulário para complementar os dados");
					
					// Manda para a pagina da venda
					
					$this->setLocation('vendas/?action=editar&pop=1&id='.$venda->id);
				
				}
			}
			else {
				$_SESSION['erro'] = '<b>Atenção, corrija os erros abaixo:</b><br />'.join('<br />', $erros);
			}
		}
		
		$pedido = new pedido($_SESSION['propostavenda']->pedido_id);
		$cadastro = new cadastro($pedido->cadastro_id);
		$vendedor = new cadastro($pedido->vendedor_id);
		$pedidostatus = new pedidostatus($pedido->pedidostatus_id);
		$propostastatus = new propostastatus($_SESSION['propostavenda']->propostastatus_id);

		$t->propostavenda = $_SESSION['propostavenda'];
		$t->propostastatus = $propostastatus;
		$t->pedido = $pedido;
		$t->cadastro = $cadastro;
		
		// opcoes de propostastatus
		foreach(propostastatus::opcoes() as $id=>$descricao){
			// $t->list_propostastatus = new propostastatus($id);
			$t->list_propostastatus = new propostastatus($id);
			$t->parseBlock('BLOCK_LIST_PROPOSTASTATUS', true);
		}
		
		$i=0;
	
		if($pedido->personalizados==''){
			// Parse itens do pedido
			$absolute_path = getcwd();
			foreach($_SESSION['propostavenda']->itens() as $propostaitem){
				
				$item = new item($propostaitem->item_id);
				
				if(file_exists($absolute_path."/img/produtos/{$item->imagem}")){
					$t->imagem_produto = PATH_SITE."timthumb/timthumb.php?src=".PATH_SITE."img/produtos/".$item->imagem."&w=80";
				 }else{
					 $t->imagem_produto = PATH_IMG."produtos/".$item->imagem."?w=80";
				 }
			
				$propostaitem->disabled = ($_SESSION['propostavenda']->data_envio!=''?'readonly class="disabled"':'');
				$propostaitem->disabled = '';
			
				$t->list_pedidoitem = $propostaitem ;
				
				$t->propostaindice = $i++;
				
				// Caso ainda nao tenha sido enviada
				if($_SESSION['propostavenda']->data_envio==''){
					$t->parseBlock('BLOCK_LIST_PEDIDOITEMPROPOSTA_EXCLUI');
				}
				
				// $t->parseBlock('BLOCK_LIST_PEDIDOITEM', true);	
				$t->parseBlock('BLOCK_LIST_PEDIDOITEMPROPOSTA', true);
				$t->parseBlock('BLOCK_LIST_PEDIDOITEMPROPOSTA_SCRIPT', true);
				
				$temItem = true;
			}
		}else{
			$personalizados = unserialize($pedido->personalizados);
			$t->personalizado_titulo = $personalizados['tipo'];
			foreach($personalizados as $key=>$value){
				if($key!='tipo'){
					$t->personalizados = '<strong style="text-transform:uppercase;">'.$key.':</strong> ';
					$t->especificacao  = $value;
					$t->parseBlock('BLOCK_PERSONALIZADO_ITEM',true);
				}
			}
			
			$t->parseBlock('BLOCK_PERSONALIZADO');
		}
		
		if($_SESSION['propostavenda']->info_entrega_cep!=''){
			$t->parseBlock('BLOCK_ENDERECO_ENTREGA');
		}
		else {
			$t->parseBlock('BLOCK_ENDERECO_ENTREGA_MESMO');
		}
		
		foreach(explode("\n",$this->config->PADRAO_OPCAO_FRETE) as $opcao_frete){
			$opcao_frete = trim(str_replace(array("<br />","\n","\r"),"", $opcao_frete));
			$t->opcao_frete_selected = ($opcao_frete == $_SESSION['propostavenda']->info_frete ? "selected" : "");
			$t->opcao_frete = $opcao_frete;
			$t->parseBlock('BLOCK_OPCAO_FRETE', true);
		}
		
		foreach(explode("\n",$this->config->PADRAO_FORMA_PAGAMENTO) as $forma_pagamento){
			$forma_pagamento = trim(str_replace(array("<br />","\n","\r"),"", $forma_pagamento));
			$t->forma_pagamento_selected = ($forma_pagamento == $_SESSION['propostavenda']->info_forma_pagamento ? "selected" : "");
			$t->forma_pagamento = $forma_pagamento;
			$t->parseBlock('BLOCK_FORMA_PAGAMENTO', true);
		}
		
		if($this->config->HABILITA_VARIACAO_PRECO=='S' && $_SESSION['propostavenda']->data_envio == '' ){
			$t->parseBlock('BLOCK_OPCAO_PRECO_SUGESTAO');
		}
		if(!request('pop')){
			$this->montaMenu($t);
		}
		$this->show($t);
	}

	public function vendas(){
	
		
	
		$this->filtraLogado();
		// $this->filtraPermissao('propostas');
		
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html') ;
		$t->h1 = h1('Pedidos');

		$t->config = new config();

		if(request('action')=='editar' || request('action')=='sair'
			||substr(request('action'),0,6)=='salvar'){
			$t = new TemplateAdmin('admin/tpl.admin-cadastro-venda.html') ;
			
			$this->vendasEditar($t);
			return;
		}

		if(request('action')=='excluir'){
			$venda = new venda(intval(request('id')));
			$venda->exclui();
		}
		
		if(request('action')=='vendasAlterarVendaStatus'){

			//printr($_REQUEST);

			$venda = new venda(intval(request('id')));
			// $venda->set_by_array($_REQUEST['venda']);
			// $venda->atualiza();
			
			query($sql="UPDATE venda SET vendastatus_id = ".intval(request('vendastatus_id')). " WHERE id = ". intval($venda->id));
			
			// printr($sql);
			
			$t = new TemplateAdmin('admin/tpl.admin-cadastro-venda.html') ;
			$this->vendasEditar($t);
			return;
		}
		
		if(request('action')=='vendasEnviar'){
		
			// $venda->vendastatus_id = 1;
			
			$venda = new venda(intval(request('id')));
			
			$venda->set_by_array($_REQUEST['venda']);
			
			// printr($venda);
			
			$info = unserialize($venda->info);
			
			foreach($_REQUEST['venda'] as $key => $value){
				if(!is_array(@$info[$key])){
					$info[$key] = $value;
				}
			}
			$info['obs_venda_html'] = nl2br($info['obs_venda']);	
						
			$venda->info = serialize($info);
			$venda->data_envio_email = bd_now();
			$venda->salva();
		
			// Manda proposta para o cliente
		
			$venda = new venda(intval(request('id')));
			$pedido = new pedido($venda->pedido_id);
			$cadastro = new cadastro($pedido->cadastro_id);
			$vendedor = new cadastro($pedido->vendedor_id);
			//printr($proposta);
			//printr($_REQUEST);
			//return;
			$e = new email();
			
			// Orcamento Enviado
			
			if($venda->id == 50){
			
				print "teste";
				
				$e->addTo('felipe@ajung.com.br', $cadastro->nome);
				// $e->addReplyTo($vendedor->email, $vendedor->nome);
				// $e->addCc($vendedor->email, $vendedor->nome);
				// $e->addBcc($this->config->EMAIL_ADMINISTRATIVO, $this->config->EMPRESA);

				$e->addHtml($venda->processa_html(true));
				$e->send("Pedido de venda {$venda->id} Ref. Orçamento {$pedido->id} " .$venda->info_empresa_nome_fantasia);
			
			
			}
			else {
			
				$e->addTo($cadastro->email, $cadastro->nome);
				$e->addReplyTo($vendedor->email, $vendedor->nome);
				$e->addCc($vendedor->email, $vendedor->nome);
				$e->addBcc($this->config->EMAIL_ADMINISTRATIVO, $this->config->EMPRESA);

				$e->addHtml($venda->processa_html(true));
				$e->send("Pedido de venda {$venda->id} Ref. Orçamento {$pedido->id} " .$venda->info_empresa_nome_fantasia);
			
			}

			

			$_SESSION['sucesso'] = tag('p', 'Seu documento de venda foi enviado para o email '.$cadastro->email);
			
			$t = new TemplateAdmin('admin/tpl.admin-cadastro-venda.html') ;
			$this->vendasEditar($t);
			return;
		}

		if(request('action')=='vendasVer'){
			 $venda = new venda(intval(request('id')));
		
			print tag('div style="padding:2px"',
			tag('a href="'.PATH_SITE.'admin.php/vendas/?action=editar&pop=1&id='.$venda->id.'"','voltar para o pedido')
						.tag('a href="javascript:window.print()"','imprimir'));
						
			print $venda->processa_html(true);
			die();
		}

		
		$grid = new grid();

		$sql = "
			SELECT
				venda.id
				,venda.id Num_Pedido
				,pedido.id orcamento
				,venda.data_cadastro
				,cadastro.empresa empresa
				,vendedor.nome vendedor
				,vendedor.id vendedor_id
				,vendastatus.descricao status
				,vendastatus.id status_id
				-- ,venda.nota_fiscal
				,venda.info
				-- ,'total'
				,venda.data_cadastro Criado_Em

			FROM
				venda
			INNER JOIN pedido ON (
				pedido.id = venda.pedido_id
			)
			LEFT JOIN vendastatus ON (
				venda.vendastatus_id = vendastatus.id
			)
			INNER JOIN cadastro ON (
				pedido.cadastro_id = cadastro.id
			)
			INNER JOIN cadastro AS vendedor ON (
				pedido.vendedor_id = vendedor.id
			)
			".($_SESSION['CADASTRO']->tipocadastro_id==tipocadastro::getId('VENDEDOR')
			?" AND pedido.vendedor_id = ".decode($_SESSION['CADASTRO']->id)." "
			:""	)."
			";
		
		

		$grid->sql = $sql;
		//printr($sql);

		$filtro = new filtro();

		$filtro->add_input('id','Venda:');
		$filtro->add_input('orcamento','Num. Orçamento:');
		$filtro->add_input('empresa','Empresa:');
		// $filtro->add_input('contato','Contato:');
		// $filtro->add_periodo('data_cadastro','Per&iacute;odo:');
		
		$grid->titulo_filtro = 'status,representante';

		$grid->metodo = 'vendas';
		$grid->filtro = $filtro ;
		$grid->nao_aparece = "info,Num_Pedido,vendedor_id,status_id,Criado_Em";

		$edicao = '';
		
		//$edicao .= $this->boxExpExcel($sql,'Vendas',$filtro);
		$edicao .= $grid->render();
		
		$t->edicao = $edicao;
		
		$this->montaMenu($t);
		$this->show($t);
	}
	
	public function vendasEditar($t){
	
		$venda = new venda(intval(request('id')));

		if(!$venda->id){
			$_SESSION['erro'] = 'O processo para criar a venda passa pela aprovação de alguma proposta';
			header('location:'.PATH_SITE.'admin.php/vendas');
			//$this->vendasNovo($t);
			die();
		}
		
		// Se for pop-up, carrega template de pop-up
		if(request('pop')){
			$t = new TemplateAdminPop('admin/tpl.admin-cadastro-venda.html');
		}

		if(substr(request('action'),0,6)=='salvar'){
			
			// $venda->vendastatus_id = 1;
			
			$venda->set_by_array($_REQUEST['venda']);
			
			// printr($venda);
			
			$info = unserialize($venda->info);
			
			foreach($_REQUEST['venda'] as $key => $value){
				if(!is_array(@$info[$key])){
					$info[$key] = $value;
				}
			}
			$info['obs_venda_html'] = nl2br($info['obs_venda']);	
			
			$venda->info = serialize($info);
			$venda->salva();
			
			$_SESSION['sucesso'] = tag('p', "Sua venda foi editada com sucesso");			
		}
		
		if(request('action')=='sair'){$this->afterSave('sair','vendas');}
		
		$pedido = new pedido($venda->pedido_id);
		$cadastro = new cadastro($pedido->cadastro_id);
		$vendedor = new cadastro($pedido->vendedor_id);
		$vendastatus = new vendastatus($venda->vendastatus_id);
		// $vendastatus = new vendastatus($venda->vendastatus_id);

		$t->venda = $venda;
		$t->vendastatus = $vendastatus;
		$t->cadastro = $cadastro;
		$t->vendedor = $vendedor;

		$temItem = false;
		// Parse itens da venda
		foreach($venda->itens() as $vendaitem){
		
			// if(!$vendaitem->item_id){
				// $_SESSION['erro'] = 'venda antiga, não é possível editar';
				// $this->setLocation('orcamentos/?action=editar&id='.$venda->pedido_id);
			// }
			
			// $item = new item($vendaitem->item_id);
		
			// $t->list_item = $item ;
			$t->list_pedidoitem = $vendaitem ;
			
			$t->parseBlock('BLOCK_LIST_PEDIDOITEMVENDA', true);	
			
			$temItem = true;
		}
		
		if($venda->info_entrega_cep!=''){
			$t->parseBlock('BLOCK_ENDERECO_ENTREGA');
		}
		else {
			$t->parseBlock('BLOCK_ENDERECO_ENTREGA_MESMO');
		}
		
		if($venda->anexo!=''){
			$t->parseBlock('BLOCK_ANEXO');
		}
		
		if($venda->obs!=''){
			$venda->obs = nl2br($venda->obs);
			$t->parseBlock('BLOCK_OBSERVACOES');
		}
		
		// OPCOES DE PROPOSTASTATUS
		foreach(vendastatus::opcoes() as $id=>$descricao){
			$t->list_vendastatus = new vendastatus($id);
			$t->parseBlock('BLOCK_LIST_VENDASTATUS', true);
		}

		// Só monta menu-lateral caso não seja pop-up
		if(!request('pop')){
			$this->montaMenu($t);
		}
		
		$this->show($t);
	}
	
	public function vendasEditarEmpresa(){

		$this->filtraLogado();
		
		if(!$_SESSION['propostavenda']->id){
			$_SESSION['erro'] = 'Proposta não encontrada';
			$this->setLocation('');
		}
		
		if(request('popup')){
			$t = new TemplateAdminPopup('admin/tpl.admin-cadastro-generico.html');
		}
		else {
			$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		}
		
		if(substr(request('action'),0,6)=='salvar'){
			
			$info = unserialize($_SESSION['propostavenda']->getInfo());
			
			foreach($_REQUEST['propostavenda'] as $key => $value){
				if(!is_array(@$info[$key])){
					$info[$key] = $value;
				}
			}
			
			$info = serialize($info);
			
			$_SESSION['propostavenda']->setInfo($info);
			
			$_SESSION['sucesso'] = tag('p', "Os dados da empresa foram editados com foi editada com sucesso");
			
			if(request('popup')){
				print js("parent.opener.callback('empresa');this.close()");
				die();
			}
		}

		$edicao = '';

		$t->parseBlock('BLOCK_TOOLBAR');

		$t->h1 = h1('Venda Empresa');

		// $edicao .= inputHidden('id', $proposta->id);
		// $edicao .= inputHidden('proposta[id]', $proposta->id);

		$edicao .= tag('div class="box-block"',
					tag('h2', 'Dados da empresa')
					.inputSimples('propostavenda[empresa_empresa]', $_SESSION['propostavenda']->info_empresa_empresa, 'Raz&atilde;o Social:', 45, 50)
					.inputSimples('propostavenda[empresa_nome_fantasia]', $_SESSION['propostavenda']->info_empresa_nome_fantasia, 'Nome Fantasia:', 45, 50)
					.inputSimples('propostavenda[empresa_cnpj]', $_SESSION['propostavenda']->info_empresa_cnpj, 'CNPJ:', 20, 20)
					.inputSimples('propostavenda[empresa_inscricao_estadual]', $_SESSION['propostavenda']->info_empresa_inscricao_estadual, 'Inscricao Estadual:', 30, 30)
					// .inputSimples('proposta[empresa_inscricao_municipal]', $_SESSION['propostavenda']->info_empresa_inscricao_municipal, 'Inscricao Municipal:', 30, 30)
					.tag('br clear="all"')
		);
		
		$edicao .= tag('div class="box-block"',
					tag('h2', 'Dados de contato')
					.inputSimples('propostavenda[empresa_telefone]', $_SESSION['propostavenda']->info_empresa_telefone, 'Telefone:', 45, 100)
					.inputSimples('propostavenda[empresa_email]', $_SESSION['propostavenda']->info_empresa_email, 'Email:', 45, 100)
					.tag('br clear="all"')
		);
		
		$edicao .= tag('div class="box-block" ',
					tag('h2', 'Endereço')
					.inputSimples('propostavenda[empresa_cep]', $_SESSION['propostavenda']->info_empresa_cep, 'CEP:', 10, 10)
					.inputSimples('propostavenda[empresa_logradouro]', $_SESSION['propostavenda']->info_empresa_logradouro, 'Logradouro:', 45, 50)
					.inputSimples('propostavenda[empresa_numero]', $_SESSION['propostavenda']->info_empresa_numero, 'Numero:', 15, 15)
					.inputSimples('propostavenda[empresa_complemento]', $_SESSION['propostavenda']->info_empresa_complemento, 'Complemento:', 30, 30)
					.inputSimples('propostavenda[empresa_bairro]', $_SESSION['propostavenda']->info_empresa_bairro, 'Bairro:', 30, 30)
					.inputSimples('propostavenda[empresa_cidade]', $_SESSION['propostavenda']->info_empresa_cidade, 'Cidade:', 30, 30)
					.inputSimples('propostavenda[empresa_uf]', $_SESSION['propostavenda']->info_empresa_uf, 'Estado:', 4, 4)
					.tag('br clear="all"')
		);
		
		$edicao .= tag('br clear="all"');
		
		// Mostra na tela os pedidos de orçamento que o cliente já fez

		$t->edicao = $edicao;

		if(!request('popup')){
			$this->montaMenu($t);
		}
		
		$this->show($t);
	}
	
	public function vendasEditarEnderecoEntrega(){
	
		$this->filtraLogado();
		
		if(!$_SESSION['propostavenda']->id){
			$_SESSION['erro'] = 'Proposta não encontrada';
			$this->setLocation('');
		}
		
		if(request('popup')){
			$t = new TemplateAdminPopup('admin/tpl.admin-cadastro-generico.html');
		}
		else {
			$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		}
		
		if(substr(request('action'),0,6)=='salvar'){
			
			$info = unserialize($_SESSION['propostavenda']->getInfo());
			
			// zera opcoes entrega_
			foreach( array_keys($info) as $key => $value){
				if(strpos($key,'entrega_')>-1){
					if(!is_array(@$info[$key])){
						$info[$key] = $value;
					}
				}
			}
			
			foreach($_REQUEST['propostavenda'] as $key => $value){
				if(!is_array(@$info[$key])){
					$info[$key] = $value;
				}
			}
			
			if(!is_set($_SESSION['propostavenda']->info_entrega_empresa)){
				$erros[] = 'Razão social inválida';
			}
			
			if(!is_cnpj($_SESSION['propostavenda']->info_entrega_cnpj)){
				$erros[] = 'CNPJ inválido';
			}
			
			if(!is_set($_SESSION['propostavenda']->info_entrega_cep)){
				$erros[] = 'CEP inválido';
			}
			
			if(!is_set($_SESSION['propostavenda']->info_entrega_numero)){
				$erros[] = 'Número do endereço inválido';
			}
			
			if(!is_set($_SESSION['propostavenda']->info_entrega_bairro)){
				$erros[] = 'Bairro do endereço inválido';
			}
			
			if(!is_set($_SESSION['propostavenda']->info_entrega_cidade)){
				$erros[] = 'Cidade do endereço inválida';
			}
			
			if(!is_set($_SESSION['propostavenda']->info_entrega_uf)){
				$erros[] = 'UF do endereço inválida';
			}
			
			$info = serialize($info);
			$_SESSION['propostavenda']->setInfo($info);
			
			if(sizeof($erros)==0){
				
				$_SESSION['sucesso'] = tag('p', "Os dado do endereço de entrega foram editados com sucesso");
				
				if(request('popup')){
					print js("parent.opener.callback('empresa');this.close()");
					die();
				}
				
			}
			else {
				$_SESSION['erro'] = '<b>Atenção, corrija os erros abaixo:</b><br />'.join('<br />', $erros);
			}
		}

		$edicao = '';

		$t->parseBlock('BLOCK_TOOLBAR');

		$t->h1 = h1('Venda Empresa');

		// $edicao .= inputHidden('id', $proposta->id);
		// $edicao .= inputHidden('proposta[id]', $proposta->id);

		$edicao .= tag('div class="box-block"',
					tag('h2', 'Dados da empresa')
					.inputSimples('propostavenda[entrega_empresa]', $_SESSION['propostavenda']->info_entrega_empresa, 'Raz&atilde;o Social:', 45, 50)
					.inputSimples('propostavenda[entrega_nome_fantasia]', $_SESSION['propostavenda']->info_entrega_nome_fantasia, 'Nome Fantasia:', 45, 50)
					.inputSimples('propostavenda[entrega_cnpj]', $_SESSION['propostavenda']->info_entrega_cnpj, 'CNPJ:', 20, 20)
					.inputSimples('propostavenda[entrega_inscricao_estadual]', $_SESSION['propostavenda']->info_entrega_inscricao_estadual, 'Inscricao Estadual:', 30, 30)
					// .inputSimples('proposta[entrega_inscricao_municipal]', $_SESSION['propostavenda']->info_entrega_inscricao_municipal, 'Inscricao Municipal:', 30, 30)
					.tag('br clear="all"')
		);
		
		$edicao .= tag('div class="box-block" ',
					tag('h2', 'Endereço')
					.inputSimples('propostavenda[entrega_cep]', $_SESSION['propostavenda']->info_entrega_cep, 'CEP:', 10, 10)
					.inputSimples('propostavenda[entrega_logradouro]', $_SESSION['propostavenda']->info_entrega_logradouro, 'Logradouro:', 45, 50)
					.inputSimples('propostavenda[entrega_numero]', $_SESSION['propostavenda']->info_entrega_numero, 'Numero:', 15, 15)
					.inputSimples('propostavenda[entrega_complemento]', $_SESSION['propostavenda']->info_entrega_complemento, 'Complemento:', 30, 30)
					.inputSimples('propostavenda[entrega_bairro]', $_SESSION['propostavenda']->info_entrega_bairro, 'Bairro:', 30, 30)
					.inputSimples('propostavenda[entrega_cidade]', $_SESSION['propostavenda']->info_entrega_cidade, 'Cidade:', 30, 30)
					.inputSimples('propostavenda[entrega_uf]', $_SESSION['propostavenda']->info_entrega_uf, 'Estado:', 4, 4)
					.tag('br clear="all"')
		);
		
		$edicao .= tag('br clear="all"');
		
		// Mostra na tela os pedidos de orçamento que o cliente já fez

		$t->edicao = $edicao;

		if(!request('popup')){
			$this->montaMenu($t);
		}
		
		$this->show($t);
	
	}
	
	public function pedidos(){

		$this->filtraLogado();
		$this->filtraPermissao('pedidos');

		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html') ;
		$t->h1 = h1('Pedidos');

		if(request('action')=='editar'
			||substr(request('action'),0,6)=='salvar'){
			$t = new TemplateAdmin('admin/tpl.admin-cadastro-pedido.html') ;
			$this->pedidosEditar($t);
			return;
		}

		if(request('action')=='excluir'){
			$pedido = new pedido(intval(request('id')));
			$pedido->exclui();
		}

		if(request('action')=='pedidosAlterarPedidoStatus'
			||request('action')=='pedidosAlterarVendedor'){

			//printr($_REQUEST);

			$pedido = new pedido(intval(request('id')));
			$pedido->set_by_array($_REQUEST['pedido']);
			$pedido->atualiza();

			$t = new TemplateAdmin('admin/tpl.admin-cadastro-pedido.html') ;
			$this->pedidosEditar($t);
			return;
		}

		if(request('action')=='pedidosAddPedidoItem'){
			
			$pedidoitem = new pedidoitem();
			$pedidoitem->set_by_array($_REQUEST['pedidoitem']);

			//printr($_REQUEST);
			//printr(new gravacao(intval(request('gravacao_id'))));
			//$gravacao = new gravacao(4);
			//printr($gravacao);
			//die();

			$cor = new cor(intval(request('cor_id')));
			$item = new item($pedidoitem->item_id);

			$objs = array($item,$cor);
			$o = new stdClass();

			foreach ($objs as $obj){
				//printr($obj);
				$class_name = get_class($obj);
				foreach(get_object_vars($obj) as $key=>$value){
					$p = $class_name.'_'.$key;
					$o->$p = $obj->$key;
				}
			}
			
			$pedidoitem->cor_id = $cor->id;
			$pedidoitem->item_preco = $item->preco;
			$pedidoitem->info = serialize($o);

			$pedidoitem->salva();

			$t = new TemplateAdmin('admin/tpl.admin-cadastro-pedido.html') ;
			$this->pedidosEditar($t);
			return;
			//printr($pedidoitem);
		}

		if(request('action')=='delPedidoItem'){
			$pedidoitem = new pedidoitem(intval(request('pedidoitem_id')));
			$pedidoitem->exclui();
			$t = new TemplateAdmin('admin/tpl.admin-cadastro-pedido.html') ;
			$this->pedidosEditar($t);
			return;
		}

		if(request('action')=='verCliente'){

			$_REQUEST['id'] = intval(request('cadastro_id'));
			$_REQUEST['action'] = 'editar';

			$this->clientes();
			return;
		}

		$grid = new grid();

		$grid->sql =
		"
			SELECT
				pedido.id
				,cadastro.empresa
				,cadastro.nome contato
				,vendedor.nome vendedor
				,pedidostatus.descricao status
				,pedido.data_cadastro
			FROM
				pedido
			INNER JOIN cadastro ON (
				pedido.cadastro_id = cadastro.id
			)
			INNER JOIN cadastro AS vendedor ON (
				pedido.vendedor_id = vendedor.id
			)
			INNER JOIN pedidostatus ON (
				pedido.pedidostatus_id = pedidostatus.id
			)
			"
			.($_SESSION['CADASTRO']->tipocadastro_id==tipocadastro::getId('VENDEDOR')
			?" AND vendedor_id = ".decode($_SESSION['CADASTRO']->id)." "
			:""	)."
			ORDER BY
				pedido.id DESC
		";

		$filtro = new filtro();

		$filtro->add_input('id','Num. Pedido:');
		$filtro->add_input('empresa','Empresa:');
		$filtro->add_input('contato','Contato:');
		$filtro->add_periodo('data_cadastro','Per&iacute;odo:');

		$grid->filtro = $filtro ;
		$t->grid = $grid->render();
		
		$this->montaMenu($t);
		$this->show($t);
	}

	public function pedidosEditar($t){

		$pedido = new pedido(intval(request('id')));

		if(!$pedido->id){
			header('location:'.PATH_SITE.'admin.php/pedidosNovo');
			//$this->pedidosNovo($t);
			die();
		}

		$cadastro = new cadastro($pedido->cadastro_id);
		$vendedor = new cadastro($pedido->vendedor_id);
		$pedidostatus = new pedidostatus($pedido->pedidostatus_id);

		$t->pedido = $pedido;
		$t->cadastro = $cadastro;
		$t->vendedor = $vendedor;
		$t->pedidostatus = $pedidostatus;
		//$t->propostastatus = $propostastatus;

		// OPCOES DE PROPOSTASTATUS
		foreach(pedidostatus::opcoes() as $id=>$descricao){
			$t->list_pedidostatus = new pedidostatus($id);
			$t->parseBlock('BLOCK_LIST_PEDIDOSTATUS', true);
		}

		// OPCOES DE VENDEDOR
		foreach(cadastro::opcoesVendedor() as $id=>$nome){
			$t->list_vendedor = new cadastro($id);
			$t->parseBlock('BLOCK_LIST_VENDEDOR', true);
		}

		if($_SESSION['CADASTRO']->tipocadastro_id == tipocadastro::getId('ADMINISTRATIVO')){
			$t->parseBlock('BLOCK_ALTERAR_VENDEDOR');
		}

		// PARSE ITENS DO PEDIDO
		$i=1;
		$temItem = false;
		foreach($pedido->get_childs('pedidoitem') as $pedidoitem){
			//printr($pedidoitem);
			$item = $pedidoitem->get_parent('item');
			foreach(get_class_vars(get_class($item)) as $key=>$value){
				if(!property_exists($pedidoitem,$key)){
					$pedidoitem->$key = $item->$key;
				}
				//print $key;
			}

			$cor = $pedidoitem->get_parent('cor');
			foreach(get_class_vars(get_class($cor)) as $key=>$value){
				if(!property_exists($pedidoitem,"cor_$key")){
					$pedidoitem->{"cor_$key"} = $cor->$key;
				}
			}

			//printr($cor);

			//$pedidoitem->{"itemcor_imagem"} = "";
			$itemcor = new itemcor(array('item_id'=>$item->id,'cor_id'=>$cor->id));
			//printr($itemcor);
			if($itemcor->id){
				foreach(get_class_vars(get_class($itemcor)) as $key=>$value){
					if(!property_exists($pedidoitem,"itemcor_$key")){
						$pedidoitem->{"itemcor_$key"} = $itemcor->$key;
					}
				}
			}

			$serial = unserialize($pedidoitem->info);

			foreach(get_object_vars(($serial)) as $key=>$value){
				if(!property_exists($pedidoitem,$key)){
					$pedidoitem->$key = $serial->$key;
				}
			}

			//printr($pedidoitem);

			$t->list_pedidoitem = $pedidoitem ;
			$t->parseBlock('BLOCK_LIST_PEDIDOITEM', true);
			$temItem = true;
		}
		if($temItem){
			$t->parseBlock('BLOCK_ITENS');
		}

		$this->montaMenu($t);
		$this->show($t);
	}

	public function pedidosNovo($t){

		if(request('email')){
			$cadastro = new cadastro(array('email'=>request('email'),'tipocadastro_id'=>tipocadastro::getId('CLIENTE')));
			if($cadastro->id){

				//printr($cadastro);
				//printr($_SESSION['CADASTRO']);

				// Se o usuario logado for um vendedor, o cliente precisa ser dele
				if($_SESSION['CADASTRO']->tipocadastro_id==tipocadastro::getId('VENDEDOR')){
					if($cadastro->cadastro_id == decode($_SESSION['CADASTRO']->id)){
						// Prossegue
						$pedido = new pedido();
						$pedido->cadastro_id = $cadastro->id;
						$pedido->vendedor_id = decode($_SESSION['CADASTRO']->id);
						$pedido->pedidostatus_id = 1; // Lancado
						$pedido->salva();
						$_SESSION['sucesso'] = tag('p','Pedido criado');
						header('location:'.PATH_SITE."admin.php/pedidos/?id={$pedido->id}&action=editar");
						die();
					}
					else {
						// Para e mostra mensagem
						$_SESSION['erro'] = tag('p','Este cliente não está associado ao seu cadastro');
					}
				}
				// Nao é vendedor, acesso geral
				else {
					$pedido = new pedido();
					$pedido->cadastro_id = $cadastro->id;
					$pedido->vendedor_id = $cadastro->cadastro_id;
					$pedido->pedidostatus_id = 1; // Lancado
					$pedido->salva();
					$_SESSION['sucesso'] = tag('p','Pedido criado');
					header('location:'.PATH_SITE."admin.php/pedidos/?id={$pedido->id}&action=editar");
					die();
				}
			}
		}


		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Criar novo pedido de orçamento');

		$this->montaMenuSimples($t);

		$edicao = inputSimples('email','','Digite o e-email do cliente', 60, 100);

		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}

	public function produtos(){

		$this->filtraLogado();
		$this->filtraPermissao('produtos');

		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Produtos');
		
		if(request('action')=='editar' || request('action')=='sair' || request('action')=='salva_variacao'
			||substr(request('action'),0,6)=='salvar'){
			$this->produtosEditar($t);
			return;
		}

		if(request('action')=='excluir'){
			$item = new item(intval(request('id')));
			$item->exclui();
		}

		$grid = new grid();

		$grid->sql =
        'SELECT
            item.id
            ,concat(\'<img width="70px" src="'.PATH_SITE.'img/produtos/\',item.imagem,\'"/>\') Imagem
            ,item.referencia Referencia
            ,item.nome Nome
            ,item.descricao Descricao
            ,item.st_amamos Lancamentos
            ,item.st_ativo Status
        FROM
            item
        WHERE
            1=1
            AND ( itemsku_id = 0 OR itemsku_id is NULL )
        ' ;

		$filtro = new filtro();
		
		$filtro->begin_block();
		$filtro->add_input('Referencia','Referência:');
		$filtro->add_input('Nome','Nome:');
		$filtro->end_block();
		$filtro->begin_block();
		$filtro->add_input('Descricao','Descrição:');
		$filtro->add_status('Status','Status:');
		$filtro->end_block();
		$filtro->begin_block();
		$filtro->add_status('Lancamentos','Lancamentos:');
		$filtro->add_categoria('categoria_id');
		$filtro->end_block();
		$filtro->add_clear();

		$grid->metodo = 'produtos' ;
		$grid->filtro = $filtro ;
        $grid->botao_excluir = $_SESSION['CADASTRO']->st_pode_excluir_produtos == 'S' ;

		$edicao = '';
		
		$sql = "SELECT 
					item.id
					,item.referencia Referencia
					,item.tabela1 Tabela_1
					,item.tabela2 Tabela_2
					,item.tabela3 Tabela_3
					-- ,item.tabela1_st Tabela_1_ST
					-- ,item.tabela2_st Tabela_2_ST
					-- ,item.tabela3_st Tabela_3_ST
				FROM item
				WHERE
					item.tabela1 LIKE '%,%'
					OR item.tabela2 LIKE '%,%'
					OR item.tabela3 LIKE '%,%'

					-- OR item.tabela1_st LIKE '%,%'
					-- OR item.tabela2_st LIKE '%,%'
					-- OR item.tabela3_st LIKE '%,%'
				";		
		
		$edicao .= $this->boxExpExcel($sql,'ProdutosSite',$filtro);

		$t->edicao = $edicao;
		
		$t->grid = $grid->render();

		$this->montaMenu($t);
		$this->show($t);
	}
	
	public function produtosEditar($t){

		$this->filtraLogado();
		$this->filtraPermissao('produtos');
		
		// Se for pop-up, carrega template de pop-up
		if(request('pop')){
			$t = new TemplateAdminPop('admin/tpl.admin-cadastro-generico.html');
		}

		$item = new item(intval(request('id')));

		// Ação de salvar produto

		if(substr(request('action'),0,6)=='salvar'){
			$erros = array();

			$next = substr(request('action'),7,strlen(request('action')));

			$item->set_by_array($_REQUEST['item']);
			$item->preco = toFloat($item->preco);
			$item->preco_de = toFloat($item->preco_de);
			
			$item->preco_1 = toFloat($item->preco_1);
			$item->preco_2 = toFloat($item->preco_2);
			$item->preco_3 = toFloat($item->preco_3);

            if(intval($item->itemclasse_id)==0){
                $item->itemclasse_id = 'null';
            }

            if(intval($item->splash_id)==0){
                $item->splash_id = 'null';
            }
			
			// Valida dados do item
			if($item->valida_atualizacao($erro)){
			
				$item->salva();

				/*BEGIN SALVA CATEGORIA*/
				$categoria_ids = request('categoria_id');
				
				$atualizados = array(0);
				foreach(is_array($categoria_ids) ? $categoria_ids : array() as $categoria_id){
					$itemcategoria = new itemcategoria();
					$itemcategoria->reset_vars();
					$itemcategoria->get_by_id(array('item_id'=>$item->id,'categoria_id'=>$categoria_id));
					if(!@$itemcategoria->id){
						$itemcategoria->categoria_id = $categoria_id;
						$itemcategoria->item_id = $item->id;
						$itemcategoria->salva();
					}
					$atualizados[] = $itemcategoria->id;
				}
				query('DELETE FROM itemcategoria WHERE item_id = '. $item->id . ' AND id NOT IN ('.join(',',$atualizados).')');
				/*END SALVA CATEGORIA*/

				/*BEGIN SALVA CARAC (GRAVACAO, MATERIA PRIMA)*/
				$caracvalor = request('caracvalor');

				foreach(is_array($caracvalor) ? $caracvalor : array() as $carac_id => $arrcaracvalor_id){

					$atualizados = array(0);
					$gravacoes_atualizadas = array(0);
					foreach($arrcaracvalor_id as $caracvalor_id){

						$itemcarac = new itemcarac();
						$itemcarac->reset_vars();
						$itemcarac->get_by_id(array('carac_id'=>$carac_id, 'item_id'=>$item->id, 'caracvalor_id'=>$caracvalor_id));
						if(!@$itemcarac->id){
							$itemcarac->carac_id = $carac_id;
							$itemcarac->caracvalor_id = $caracvalor_id;
							$itemcarac->item_id = $item->id;
							
							
							
							$itemcarac->salva();
						}
						$atualizados[] = $itemcarac->id;
						$gravacoes_atualizadas[] = $caracvalor_id;
					}
					
					query('DELETE FROM itemcarac WHERE carac_id = '.$carac_id.' AND item_id = '. $item->id . ' and ID not in ('.join(',',$atualizados).')');
				}
				/*END SALVA CARAC (GRAVACAO, MATERIA PRIMA)*/
			
				// Salva variação de preço
				if($this->config->HABILITA_VARIACAO_PRECO=='S'){
				
					/*BEGIN SALVA PRECO*/
					$atualizados = array(0);
					foreach(is_array(@$_REQUEST['preco'])?$_REQUEST['preco']:array() as $id => $arrpreco){

						$preco = new preco(
							array(
								'item_id' => $arrpreco['item_id']
								,'qtd_1' => $arrpreco['qtd_1']
								,'qtd_2' => $arrpreco['qtd_2']
							)
						);
						
						$preco->set_by_array($arrpreco);
						$preco->preco = tofloat($preco->preco);
						
						$erro = array();
						if($preco->validaDados($erro)){
							$preco->preco = tofloat($preco->preco);
							$preco->salva();

						}
						else{
							$_SESSION['erro'] = join('<br />', $erro);
						}
						$atualizados[] = $preco->id;
						
					}
					query('DELETE FROM preco WHERE item_id = '.$item->id.' and ID not in ('.join(',',$atualizados).')');
					
					/*END SALVA PRECO*/
				}

				$_SESSION['sucesso'] = 'Dados salvos com sucesso';

				if(trim(@$next)!=''){
					$this->afterSave($next,'produtos',$item);
					return;
				}				
				
				// video
				if(request('linkyoutube')){
					$youtubevideo = new youtubevideo(array('item_id'=>$item->id));
					$youtubevideo->st_ativo = request('linkyoutube_status');
					$youtubevideo->validaDadosSalva($item->id,request('linkyoutube'));
				}

				$item = new item($item->id);

			}
			else {
				$_SESSION['erro'] = tag('p', join('<br>',$erro));
			}
		}
		
		if(request('action')=='sair'){$this->afterSave('sair','produtos',$item);}
		
		if(request('action')=='salva_variacao'){
			$erro = '';
			$sucesso = '';
			$out = array();

			
			$itempai = new item(intval(request('itemvariacao_itempai')));
			$item    = new item(intval(request('itemvariacao_id')));
			
			// if(!$item->id){
				// $item->set_by_array( (array)$itempai );
				// $item->id = intval(request('itemvariacao_id'));
			// }
			
			$item->set_by_array($_REQUEST['itemvariacao']);
			$item->itemsku_id = $itempai->id;
			$item->cor_id = intval(request("itemvariacao_cor"));
			
			if($item->validaVariacao($erro)){
			
				if(file_tratamento("img_variacao", $msg, $file)){					
					if(!isImagemJPG($file['name'])){
						$erro .= "A imagem deve ser .jpg";
					}else{				
						list($w, $h) = getimagesize($file['tmp_name']);
						if($w!=1000 || $h!=1000){
							$erro .= "A imagem deve ter o tamanha de 1000x1000px";
						}else{
							$filename = $item->referencia.".jpg";						
							move_uploaded_file($file['tmp_name'], "img/produtos/{$filename}");
							$item->imagem = $filename;
						}
					}
				}else{
					$erro .= $msg;
				}		

				$item->salva2();							
				$sucesso .= tag("p","Varia&ccedil;&atilde;o salva com sucesso.");
			}	
			
			if($erro !=''){
				$out['status'] = 0;
				$out['msg'] = $erro;
			}else{
				$out['status'] = 1;
				$out['msg'] = $sucesso;
			}
			
			echo json_encode($out);			
			die();
		}

		$t->parseBlock('BLOCK_TOOLBAR');

		$t->h1 = h1('Produtos '.$item->referencia);

		// carrega campos de edicao

		$edicao = '';

		$edicao .= '<div class="box-block">'.tag('h2', 'Dados básicos').'<div class="c-box" xstyle="float:left;width:33%">';

		$edicao .= "<table style='width:100%;'><tr><td>";
		
			$edicao .= inputHidden('item[id]', $item->id);
			$edicao .= inputSimples('item[referencia]', $item->referencia, 'Referencia:', 30, 50); //tag('p','A referencia precisa estar no formato `LLNNNCC`, onde <br />LL = letras(2x), <br />NNN = números(3x), <br />CC = código da cor(2x)')
			$edicao .= inputSimples('item[nome]', $item->nome, bandeira_br() . ' Nome:', 30, 50);

			if($this->config->HABILITA_ESPANHOL=='S'){
				$edicao .= inputSimples('item[nome_es]', $item->nome_es, bandeira_es() . ' Nome:', 30, 50);
			}

			if($this->config->HABILITA_INGLES=='S'){
				$edicao .= inputSimples('item[nome_in]', $item->nome_in, bandeira_in() . ' Nome:', 30, 50);
			}

			$edicao .= select('item[st_ativo]', $item->st_ativo, 'Ativo?:', array('S'=>'Sim','N'=>'Nao'));
			$edicao .= tag('p',tag('small','Produtos inativos n&atilde;o aparecem no catalogo do site')); ;
			$edicao .= select('item[st_amamos]', $item->st_amamos, 'Aparece em Lançamentos?', array('N'=>'Nao', 'S'=>'Sim'));
            $edicao .= select('item[st_destaque]', $item->st_destaque, 'Aparece em Promoções?', array('N'=>'Nao', 'S'=>'Sim'));
            $edicao .= select('item[itemclasse_id]', $item->itemclasse_id, 'Classe de produtos:', itemclasse::opcoes(array('blank'=>1)));

			if($this->config->HABILITA_LANCAMENTO=='S'){
				$edicao .= select('item[st_lancamento]', $item->st_lancamento, 'Lançamento?:', array('S'=>'Sim','N'=>'Nao'));
			}

			if($this->config->HABILITA_QUANTIDADE_MINIMA=='S'){
				$edicao .= inputSimples('item[qtd_minima]', $item->qtd_minima, ' Quantidade mínima:', 30, 50);
			}

			if($this->config->HABILITA_PRECO=='S'){
				$edicao .= inputDecimal('item[preco]', $item->preco, ' Preço:');
				//$edicao .= inputDecimal('item[preco_de]', $item->preco_de, ' Preço DE:');
			}

			$edicao .= '</div><div style="float:left;width:33%">';

			$edicao .= textArea('item[chamada]', $item->chamada, bandeira_br() . ' Chamada:', 35, 2);

			if($this->config->HABILITA_ESPANHOL=='S'){
				$edicao .= textArea('item[chamada_es]', $item->chamada_es, bandeira_es() . ' Chamada:', 35, 2);
			}

			if($this->config->HABILITA_INGLES=='S'){
				$edicao .= textArea('item[chamada_in]', $item->chamada_in, bandeira_in() . ' Chamada:', 35, 2);
			}

			$edicao .= textArea('item[descricao]', $item->descricao, bandeira_br() . ' Descricao:', 35, 4);

			if($this->config->HABILITA_ESPANHOL=='S'){
				$edicao .= textArea('item[descricao_es]', $item->descricao_es, bandeira_es() . ' Descricao:', 35, 4);
			}

			if($this->config->HABILITA_INGLES=='S'){
				$edicao .= textArea('item[descricao_in]', $item->descricao_in, bandeira_in() . ' Descricao:', 35, 4);
			}

			$edicao .= inputSimples('item[seo_keywords]', $item->seo_keywords, ' SEO Keywords:', 30, 200);
			$edicao .='<br>';$edicao .='<br>';
		
		    $edicao .= "</td><td valign='top'>";
		
			$edicao .= '</div><div style="float:right">';

			$edicao .= inputSimples('item[altura]', $item->altura, 'Altura ( mm ):', 15, 100);
			$edicao .= inputSimples('item[largura]', $item->largura, 'Largura ( mm ):', 15, 100);
			$edicao .= inputSimples('item[profundidade]', $item->profundidade, 'Profundidade ( mm ):', 15, 100);
			$edicao .= inputSimples('item[diametro]', $item->diametro, 'Diâmetro ( mm ):', 15, 100);
			$edicao .= inputSimples('item[peso]', $item->peso, 'Peso ( Kg ):', 15, 10);
			$edicao .= inputSimples('item[garantia]', $item->garantia, 'Garantia:', 15, 200) ;
			$edicao .= inputSimples('item[energia]', $item->energia, 'Energia ( ex: 110V ):', 15, 200);
			//$edicao .= inputSimples('item[disponibilidade]', $item->disponibilidade, 'Disponibilidade:', 15, 10) ;
            $edicao .= inputSimples('item[medida_gravacao]', $item->medida_gravacao, 'Medidas para gravação (CxD):', 15, 200) ;
            $edicao .= inputSimples('item[tamanho_total]', $item->tamanho_total, 'Tamanho total (CxD):', 15, 200);
			$edicao .= select('item[disponibilidade]',$item->disponibilidade, 'Disponibilidade:',array('S'=>'Disponível','N'=>'Indisponível'));


		$edicao .= "</td></tr>";

		$edicao .= "<tr><td>";
		$edicao .= inputDecimal("item[tabela1]",money($item->tabela1),"Tabela 1 ( R$ )");
		$edicao .= inputDecimal("item[tabela2]",money($item->tabela2),"Tabela 2 ( R$ )");
		$edicao .= inputDecimal("item[tabela3]",money($item->tabela3),"Tabela 3 ( R$ )");
		$edicao .= "</td><td>";
		// $edicao .= inputDecimal("item[tabela1_st]",money($item->tabela1_st),"Tabela 1 ST( R$ )");
		// $edicao .= inputDecimal("item[tabela2_st]",money($item->tabela2_st),"Tabela 2 ST( R$ )");
		// $edicao .= inputDecimal("item[tabela3_st]",money($item->tabela3_st),"Tabela 3 ST( R$ )");
		$edicao .="</td></tr>";
		
		$edicao .="</table></div><br clear='all' />";
		
		if($this->config->HABILITA_INFORMACAO_FORNECEDOR=='S'){
			$edicao .='<table class="t-fornecedor">';
			$edicao .='<tr>';
			$edicao .='<td>';
			$edicao .='Fornecedor';
			$edicao .='</td>';
			$edicao .='<td>';
			$edicao .='Código';
			$edicao .='</td>';
			$edicao .='<td>';
			$edicao .='Preço';
			$edicao .='</td>';
			$edicao .='<td>';
			$edicao .='Data';
			$edicao .='</td>';
			$edicao .='</tr>';
			$edicao .='<tr style="height:20px;">';
			$edicao .='<td style="height:20px;">';
			$edicao .= inputSimples('item[fornecedor_1]', $item->fornecedor_1, ' ', 10, 50);
			$edicao .='</td>';
			$edicao .='<td>';
			$edicao .= inputSimples('item[codigo_1]', $item->codigo_1, ' ', 5, 50);
			$edicao .='</td>';
			$edicao .='<td>';
			$edicao .= inputDecimal('item[preco_1]', $item->preco_1, ' ', 5, 50);
			$edicao .='</td>';
			$edicao .='<td>';
			$edicao .= inputData('item[data_1]', $item->data_1, ' ', 5, 50);
			$edicao .='</td>';
			$edicao .='</tr>';
			$edicao .='<tr>';
			$edicao .='<td>';
			$edicao .= inputSimples('item[fornecedor_2]', $item->fornecedor_2, ' ',10, 50);
			$edicao .='</td>';
			$edicao .='<td>';
			$edicao .= inputSimples('item[codigo_2]', $item->codigo_2, ' ', 5, 50);
			$edicao .='</td>';
			$edicao .='<td>';
			$edicao .= inputSimples('item[preco_2]', $item->preco_2, ' ', 5, 50);
			$edicao .='</td>';
			$edicao .='<td>';
			$edicao .= inputData('item[data_2]', $item->data_2, ' ', 5, 50);
			$edicao .='</td>';
			$edicao .='</tr>';
			$edicao .='<tr>';
			$edicao .='<td>';
			$edicao .= inputSimples('item[fornecedor_3]', $item->fornecedor_3, ' ', 10, 50);
			$edicao .='</td>';
			$edicao .='<td>';
			$edicao .= inputSimples('item[codigo_3]', $item->codigo_3, ' ', 5, 50);
			$edicao .='</td>';
			$edicao .='<td>';
			$edicao .= inputSimples('item[preco_3]', $item->preco_3, ' ', 5, 50);
			$edicao .='</td>';
			$edicao .='<td>';
			$edicao .= inputData('item[data_3]', $item->data_3, ' ', 5, 50);
			$edicao .='</td>';
			$edicao .='</tr>';
			$edicao .='</table>';
		}
	

		$edicao .= '<br clear="all"/></div><br clear="all"/>';

        /*
		$edicao .= '<div class="box-block">';
		$edicao .= tag('h2', 'Informações Adicionais');
		$edicao .= tag("table style='border-bottom:3px solid;'",tag("tr",tag("td",inputSimples('item[infoadicional1]', $item->infoadicional1, 'Info 1:', 165,165)).tag("td",inputSimples('item[infoadicional1_link]', $item->infoadicional1_link, 'Info 1 Link:', 165,165)))
			.tag("tr",tag("td colspan=2",inputSimples('item[infoadicional1_tooltipe]', $item->infoadicional1_tooltipe, 'Info 1 Tooltip:', 330,330,"style='width:600px;'"))));
		
		$edicao .= tag("table style='border-bottom:3px solid;'",tag("tr",tag("td",inputSimples('item[infoadicional2]', $item->infoadicional2, 'Info 2:', 165,165)).tag("td",inputSimples('item[infoadicional2_link]', $item->infoadicional2_link, 'Info 2 Link:', 165,165)))
			.tag("tr",tag("td colspan=2",inputSimples('item[infoadicional2_tooltipe]', $item->infoadicional2_tooltipe, 'Info 2 Tooltip:', 330,330,"style='width:600px;'"))));
		
		$edicao .= tag("table style='border-bottom:3px solid;'",tag("tr",tag("td",inputSimples('item[infoadicional3]', $item->infoadicional3, 'Info 3:', 165,165)).tag("td",inputSimples('item[infoadicional3_link]', $item->infoadicional3_link, 'Info 3 Link:', 165,165)))
			.tag("tr",tag("td colspan=2",inputSimples('item[infoadicional3_tooltipe]', $item->infoadicional3_tooltipe, 'Info 3 Tooltip:', 330,330,"style='width:600px;'"))));
			
		$edicao .= '</div>';
		$edicao .= '<br clear="all"/>';
        */
			
		if($this->config->HABILITA_SPLASH=='S'){
			$edicao .= '<div class="box-block">';
			$edicao .= tag('h2', 'Splash');
			$edicao .= select('item[splash_id]', $item->splash_id, 'Splash:', splash::opcoes(), true);
			$edicao .= '</div>';
			$edicao .= '<br clear="all"/>';
		}
		
		if($this->config->HABILITA_COR=='S'){
			/* Variacaes  de cor*/
			$query = query("SELECT item.*, cor.nome cornome FROM item INNER JOIN cor ON ( item.cor_id = cor.id AND cor.st_ativo = 'S') where item.itemsku_id = {$item->id}");
			$variacoes = "<table class='grid'>";
			$variacoes .= tag("tr",
					tag("th","Referencia")
					.tag("th","Nome")
					.tag("th","Status")
					.tag("th","Editar")
					.tag("th","Excluir")		
			);
			while($fetch=fetch($query)){
				$variacoes .= tag("tr id='row_variacao{$fetch->id}'",
					tag("td",$fetch->referencia)
					.tag("td",$fetch->cornome)
					.tag("td",($fetch->st_ativo=="S"?"Ativo":"Inativo"))
					.tag("td","<span style='cursor:pointer;' class='add_variacao' data-id='{$fetch->id}' data-itemid='{$item->id}'><img src='".PATH_SITE."img/assets/edit.png' alt='Editar' /></span>")
					.tag("td","<span style='cursor:pointer;' class='excluir_variacao' data-id='{$fetch->id}'><img src='".PATH_SITE."img/assets/cursor_x.png' alt='Excluir' /></span>")
				); 		
			}
			$variacoes .= "</table>";
			
			$edicao .= tag("div class='box-block' style='position:relative;'",
				tag("h2","Variações de Cor")
				.tag("div", "&nbsp;".(rows($query)>0?$variacoes:"") )
				.( $item->id ? 
					tag("span class='bt_afirmar btn btn-primary add_variacao' data-id='0' data-itemid='{$item->id}'", "Add nova") 
					: tag("span class='bt_negar btn' ", "Salve o produto, para liberar a adição das variações.") 
				)
				.tag("div id='show_variacao'","&nbsp;")
				
				.tag("script","
					$(document).ready(function(){
						$('.add_variacao').bind('click',function(){
							_id = $(this).data('id');
							_itemid = $(this).data('itemid');
							$.ajax({
								url : '".PATH_SITE."admin.php/addVariacaoItem/'
								,data : {id : _id, itemid : _itemid}
								,success : function(out){
									$('#show_variacao').html(out);
								}
							});
						});
						
						$('.excluir_variacao').bind('click',function(){
							id = $(this).data('id');
							$.ajax({
								url : '".PATH_SITE."admin.php/excluirItemVariacao/'
								,dataType : 'json'
								,data : {item_id : id}
								,success : function(out){
									if(out['status']==1){
										$('#row_variacao'+id).remove();
									}
									if(out['status']==0){
										alert(out['msg']);
									}
								}
							});
						});
					});
				")
			);
			$edicao .= '<br clear="all"/>';
		}
		
		$edicao .= '<div class="box-block">';
		$edicao .= tag("h2","Video");
		$edicao .= tag("p","Adicione o link do video do youtube.");
		
		$youtubevideo = new youtubevideo(array('item_id'=>$item->id));
		
		$edicao .= "<table style='width:100%;'><tr><td>";
		
		$edicao .= inputSimples("linkyoutube",(request('linkyoutube')?request('linkyoutube'):$youtubevideo->original_url),'Link Youtube',200,200);
		$edicao .= select("linkyoutube_status",$youtubevideo->st_ativo,'Status',array("S"=>"Ativo","N"=>"Inativo"));
		
		$edicao .= "</td><td>&nbsp;";
	
		if($youtubevideo->id){
			$_src = encode($youtubevideo->url.'?rel=0&version=3&loop=1&autoplay=1');
			$edicao .= '<div class="box_video">
						<img src="'.$youtubevideo->thumbnail.'" width="240px" height="135px" class="thumbvideo" id="thumbvideo" data-src="'.$_src.'" />
					</div>'
				.'<script>
				
				$("#thumbvideo").bind("click",function(){
					_src = $(this).data("src");
					$.ajax({
						url : "'.PATH_SITE.'ajax.php/getItemVideo/"
						,data : {src : _src}
						,success : function(out){
							$.fancybox(
								out,
							{
								padding     : 0,				 
								openEffect  : "elastic",
								openSpeed   : 350,
								closeEffect : "elastic",
								closeSpeed  : 350,
								closeBtn    : false 
							});
						} 
					});
				});				
				
				</script>'
			;
		}
		
		$edicao .= "</td></tr></table>";
				
		
		$edicao .= '</div>';
		$edicao .= '<br clear="all"/>';

		$edicao .= '<div class="box-block">';
		$edicao .= tag('h2', 'Organização');
		$edicao .= '<div>';

		$edicao .= tag('h3', 'Categorias');
		
		$ident = 0;
		$zebra = true;
		
		$edicao .= $this->produtosCategoriaProcessa(0, $ident, $zebra, intval($item->id), 'N');
		
		$edicao .= '<br />';
		// $edicao .= tag('h3', 'Cord&otilde;es');
		// $edicao .= $this->produtosCategoriaProcessa(0, $ident, $zebra, intval($item->id), 'S',true);
		
		$edicao .= '</div>';
		
		$edicao .= '<div style="float:left;width:49%;">';

		if($this->config->HABILITA_MATERIA_PRIMA=='S'){

			$edicao .= tag('p', 'Matéria prima');

			$query = query($sql=
						"
						SELECT
							caracvalor.*
							,CASE WHEN itemcarac.id > 0 THEN 'checked' ELSE '' END checked
						FROM
							caracvalor
						LEFT JOIN itemcarac ON (
								caracvalor.id = itemcarac.caracvalor_id
							AND itemcarac.item_id = {$item->id}
						)
						WHERE
							caracvalor.carac_id = 1
						ORDER BY
							caracvalor.nome
						");

			$limite = 15;

			$i=0;
			while($fetch=fetch($query)){
				$edicao .= "<input type='checkbox' name='caracvalor[1][]' value='{$fetch->id}' {$fetch->checked} />{$fetch->nome} <br/>"  ;
			}
		}

		$edicao .= '</div>';

		$edicao .= '</div>';
		$edicao .= '<br clear="all"/>';

		if($this->config->HABILITA_VARIACAO_PRECO=='S'){
		
			$edicao .= '<div class="box-block">';
			$edicao .= tag('h2', 'Preço');
			
			$sql="
			select
				variacaopreco.qtd_1
				,variacaopreco.qtd_2
				,ifnull(preco.preco,0) preco
				,preco.id
			from
				variacaopreco
			left outer join
				preco on (
					preco.item_id = {$item->id}
				and preco.qtd_1 = variacaopreco.qtd_1
				and preco.qtd_2 = variacaopreco.qtd_2
			)
			order by 
				variacaopreco.qtd_1
				,variacaopreco.qtd_2;
			";
			
			$query = query($sql);
				
			$i=0;
			$edicao .= '<table class="grid">';
			
			$edicao .= '<tr>';
			
			$edicao .= tag('th','Quantidade 1');
			$edicao .= tag('th','Quantidade 2');
			$edicao .= tag('th','Preço');
			
			$edicao .= '</tr>';
			
			$contador = 0;
			
			$zebra = true;
			
			while($fetch=fetch($query)){
				
				$style = ($zebra?'style="background-color:#eeeeee"':'');
				
				$edicao .= inputHidden("preco[{$contador}][item_id]",$item->id) ;
				
				$edicao .= tag('tr', 
								tag('td '.$style, inputReadOnly("preco[{$contador}][qtd_1]",$fetch->qtd_1,'',20,60)) 
								.tag('td '.$style, inputReadOnly("preco[{$contador}][qtd_2]",$fetch->qtd_2,'',20,60)) 
								.tag('td '.$style, inputDecimal("preco[{$contador}][preco]",money($fetch->preco),'',20,60)));
			
				$contador ++;
				$zebra =! $zebra ;
			}
			
			$edicao .= '</table>';		
			$edicao .= '</div>';
			$edicao .= '<br clear="all"/>';
		}
		
		if($this->config->HABILITA_GRAVACAO=='S'){
		
			$edicao .= '<div class="box-block">';
			$edicao .= tag('h2','Gravações');
		
			$edicao .= js(
						"
						function gravacao_idClick(_gravacao_id){
						
							var objChechboxGravacao = document.getElementById('caracvalor_2_'+_gravacao_id);
						
							if(objChechboxGravacao.checked){
								$('#divPrecoGravacao'+_gravacao_id).show('slow');
							}
							else {
								$('#divPrecoGravacao'+_gravacao_id).hide('slow');
							}
							
						}
						");

			$edicao .= tag('p', 'Gravação');

			$query = query($sql=
						"
						SELECT
							caracvalor.*
							,CASE WHEN itemcarac.id > 0 THEN 'checked' ELSE '' END checked
						FROM
							caracvalor
						LEFT JOIN itemcarac ON (
								caracvalor.id = itemcarac.caracvalor_id
							AND itemcarac.item_id = {$item->id}
						)
						WHERE
							caracvalor.carac_id = 2
						ORDER BY
							caracvalor.nome
						"
					);

			$limite = 15;

			$i=0;
			$contador = 0;
			
			while($fetch=fetch($query)){
				$gravacao = $fetch;
				$edicao .= "<input type='checkbox' name='caracvalor[2][]' id='caracvalor_2_{$gravacao->id}' value='{$gravacao->id}' {$gravacao->checked} onclick='gravacao_idClick({$gravacao->id})'/> <label for='caracvalor_2_{$gravacao->id}'>{$gravacao->nome}</label> "  ;
				$edicao .= '<br />';
			}
			$edicao .= '</div>';
		}


        $edicao .= '<div class="box-block">';

        $edicao .= '<div style="background-color:#eeeeee;padding:3px;margin:3px">';
        $edicao .= '<table class="grid">';

        // $imagens = array('imagem', 'imagem_d1', 'imagem_d2', 'imagem_d3', 'imagem_d4', 'imagem_d5', 'imagem_d6', 'imagem_d7', 'imagem_d8', 'imagem_d9', 'imagem_d10', 'imagem_d11', 'imagem_d12', 'imagem_d13', 'imagem_d14');

        $path_img = 'img/produtos/';
        $date = base64_encode(date("H:i:s"));

        $zebra = true;
        foreach(range(0,20) as $i){

            if($i==0){
                $imagem = 'imagem';
            }
            else {
                $imagem = 'imagem_d'.$i;
            }

            if($i > $this->config->QTD_IMAGENS_DETALHE){
                break;
            }

            $style = ($zebra ? 'style="background-color:#eeeeee"' : '');

            $edicao .= inputHidden($imagem, $item->$imagem);

            $edicao .= '	<tr>';
            $edicao .= '		<th colspan="2"><br><b>Imagem '.($i==0?'Principal':'Detalhe '.$i).'</b></th>';
            $edicao .= '	</tr>';
            $edicao .= '	<tr>';
            $edicao .= '		<td width="100px">Imagem:</td>';

            if($item->$imagem != ''){
                $edicao .= '<td>' . tag('a target="_blank" href="' . PATH_SITE . $path_img . $item->$imagem . '?a=' . $date . '"', tag('img width="40px" src="' . PATH_SITE . $path_img . $item->$imagem . '?a=' . $date . '"')) ;
                $edicao .=  checkbox("imagem_excluir[]", $imagem, 'Excluir imagem?');
                $edicao .= '</td>';

            }
            else {
                $edicao .= ' <td>Imagem não definida</td>';
            }

            $edicao .= '<tr '.$style.'>';
            $edicao .= '	<td>Alterar/Cadastrar:</td>';
            $edicao .= '	<td>' . inputFile("file_{$imagem}", '', '') ;
            $edicao .=  "<div style='display:none' id='file_{$imagem}_marca'>";
            $edicao .=  checkbox("habilita_marca_dagua_{$imagem}", 1, 'Aplicar marca dagua?');
            $edicao .=  "</div>";
            $edicao .= '	</td>';
            $edicao .= '</tr>';

            $edicao .= "
            <script>
                $('#file_{$imagem}').bind('change', function(){
                    var obj = $(this);
                    $('#'+obj.attr('id')+'_marca').show();
                });
            </script>
            ";

            // $i ++;

        }

        $edicao .= '</table>';
        $edicao .= '</div>';

        $edicao .= '</div>';

        /*
		$edicao .= '<div class="box-block">';

		$edicao .= tag('h2', 'Imagens');

		$edicao .= tag('div class="box-info"',
			'
			- arquivo .jpg<br/>
			- tamanho m&iacute;nimo de 1000x1000px<br/>
			');

		$imagem = 'imagem';
		$path_img = 'img/produtos/';
		$edicao .= inputHidden('imagem', $item->$imagem);

		$edicao .= '<div style="background-color:#eeeeee;padding:3px;margin:3px">';
		$edicao .= '<table class="grid">';
		$edicao .= '	<tr>';
		$edicao .= '		<td colspan="2"><b>Imagem Principal</b></td>';
		$edicao .= '	</tr>';
		$edicao .= '	<tr>';
		$edicao .= '		<td width="100px">Imagem:</td>';
		$edicao .= '		<td>'.tag('a target="_blank" href="'.PATH_SITE.$path_img.$item->imagem.'"',tag('img width="40px" src="'.PATH_SITE.$path_img.$item->imagem.'"')).'</td>';
		$edicao .= '	</tr>';

		//if($this->config->HABILITA_COR=='N'){
			$edicao .= '<tr>';
			$edicao .= '	<td>Alterar/Cadastrar:</td>';
			$edicao .= '	<td>'.inputFile('file_imagem', '', 'Imagem Principal:').'</td>';
			$edicao .= '</tr>';
		//}

		$edicao .= '</table>';
		$edicao .= '</div>';

		$zebra = true;


		$i = 1 ;
		while($i <= $this->config->QTD_IMAGENS_DETALHE){
			$style = ($zebra?'style="background-color:#eeeeee"':'');
			$imagem_d = 'imagem_d'.$i ;
			$edicao .= inputHidden('imagem_d'.$i, $item->$imagem_d);

			$edicao .= '<div style="background-color:#eeeeee;padding:3px;margin:3px">';
			$edicao .= '<table class="grid">';

			$edicao .= '<tr '.$style.'>';
			$edicao .= '	<td colspan="2"><b>Imagem detalhe '.$i.'</b></td>';
			$edicao .= '</tr>';
			$edicao .= '<tr '.$style.'>';
			$edicao .= '	<td width="100px">Imagem:</td>';
			$edicao .= '	<td>'.tag('a target="_blank" href="'.PATH_SITE.$path_img.$item->$imagem_d.'"',tag('img width="40px" src="'.PATH_SITE.$path_img.$item->$imagem_d.'"')).'</td>';
			$edicao .= '</tr>';
			$edicao .= '<tr '.$style.'>';
			$edicao .= '	<td>Alterar/Cadastrar:</td>';
			$edicao .= '	<td>'.inputFile('file_imagem_d'.$i, '', 'Imagem Detalhe '.$i.':').'</td>';
			$edicao .= '</tr>';
			$edicao .= '<tr '.$style.'>';
			$edicao .= '	<td>Excluir</td>';
			$edicao .= '	<td>'.tag('input type="checkbox" name="imagem_excluir[]" value="'.$i.'"').'</td>';
			$edicao .= '</tr>';

			$edicao .= '</table>';
			$edicao .= '</div>';


			$i++;
			$zebra = !$zebra;
		}

		// $edicao .= '</table>';

		// $edicao .= '</div>';
        */

		//if($this->config->HABILITA_COR=='S'){
		if(false){

			$edicao .= js(
			'
			function makeInputFileCor(cor_id){
				document.getElementById("div_file_cor_"+cor_id).innerHTML = "<input type=\'file\' id=\'file_cor_"+cor_id+"\' name=\'file_cor_"+cor_id+"\' />";
			}
			
			');
		
			$edicao .= '<div class="box-block">';

			$edicao .= tag('h2', 'Imagens de cor');

			$edicao .= '<table class="grid">';

			$edicao .= tag('tr'
								,tag('th width="80px"', 'Tem a cor?')
								.tag('th width="100px"', 'Nome')
								.tag('th', '&nbsp;')
							);

			$zebra = true;

			foreach(results("SELECT * FROM cor ORDER BY nome") as $cor ){

				$itemcor = new itemcor(array('item_id'=>intval($item->id),'cor_id'=>$cor->id));

				$style = ($zebra?'style="background-color:#eeeeee"':'');

				$edicao .= tag('tr'
								,tag('td '.$style, tag("input xonclick=\"javascript:itemDetalheCor(this.checked)\" type='checkbox' name='cor_id[]' value='{$cor->id}' ".($itemcor->id?'checked':'')." "))
								.tag('td '.$style, $cor->nome)
								//.tag('td '.$style, $this->detalheCor($cor,$itemcor))
								.tag('td '.$style, 
									'<div style="xdisplay:none;padding:5px;margin:3px;background-color:#dddddd">'
									.'<table width="600px">'
									.'	<tr>'
									.'		<td width="200px">Default:</td>'
									.'		<td>'.tag('input type="radio" name="st_default" value="'.$cor->id.'" '.($itemcor->st_default=='S'?'checked':'').' ').'</td>'
									.'	</tr>'
									.'	<tr>'
									.'		<td>Ativo:</td>'
									.'		<td>'.select('cor['.$cor->id.'][st_ativo]',$itemcor->st_ativo,'',array('S'=>'Sim','N'=>'Nao')).'</td>'
									.'	</tr>'
									.'	<tr>'
									.'		<td>Imagem:</td>'
									.'		<td>'.($itemcor->imagem!=''?tag('a target="_blank" href="'.PATH_SITE.'img/produtos/'.$itemcor->imagem.'"',tag('img width="40px" src="'.PATH_SITE.'img/produtos/'.$itemcor->imagem.'"')):'').'</td>'
									.'	</tr>'
									.'	<tr>'
									.'		<td>Alterar/Cadastrar:</td>'
									.'		<td><div id="div_file_cor_'.$cor->id.'">'.tag("a href='javascript:makeInputFileCor(\"{$cor->id}\")'",'alterar/cadastrar imagem').'</div></td>'
									.'	</tr>'
									.'</table>'
									.'</div>'
								
								)
								//.tag('td '.$style, select('cor['.$cor->id.'][st_default]',$itemcor->st_default,'',array('S'=>'Sim','N'=>'Nao')))
							);

				//$edicao .= $itemcor->id?js("itemDetalheCor(true)"):"";
				unset($itemcor);
				$zebra = !$zebra;
			}

			$edicao .= '</table>';
			$edicao .= '</div>';
		}


		// Joga variavel de edicao para o template
		$t->edicao = $edicao;

		// Só monta menu-lateral caso não seja pop-up
		if(!request('pop')){
			$this->montaMenu($t);
		}
		
		$this->show($t);
	}

	public function itemtag(){
		$query = query("SELECT * FROM item");
		while($fetch=fetch($query)){
			$item = new item($fetch->id);
			$item->salva2();
		}
		
		printr("Finalizado!");
	}
	
	public function addVariacaoItem(){
		$id     = request('id');
		$itemid = request('itemid');
		
		$t = new Template('admin/tpl.part-variacao-item.html');
		$t->path = PATH_SITE;
		$item = new item($id);
		$itempai = new item($itemid);
		
		$item->referencia = ($item->id?$item->referencia:$itempai->referencia);
		$item->preco = ($item->preco>0?$item->preco:$itempai->preco);
		
		$t->item = $item;
		$t->itempai = $itempai;
		
		foreach(cor::opcoes() as $key=>$value){
			$cor = new cor($key);
			$t->cor  = $cor;
			$t->cor_selected = ($item->cor_id==$cor->id?'selected':'');
			$t->parseBlock("BLOCK_CORES",true);
		}
		$t->selected_ativo = ($item->st_ativo=='S'?"selected":"");
		$t->selected_inativo = ($item->st_ativo=='N'?"selected":"");
		
		if($item->imagem!=''){
			$t->parseBlock("BLOCK_IMAGEM_VARIACAO");
		}
		
		echo $t->getContent();
		die();	
	}
	
	public function getCorCodigo(){
		$cor = new cor(intval(request('id')));
		echo $cor->referencia;
		die();
	}
	
	public function excluirItemVariacao(){
		$item = new item(intval(request('item_id')));
		$out = array();
		
		if($item->exclui()){
			$out['status'] = 1;
			$out['msg'] = "Item excluido com sucesso.";
		}else{
			$out['status'] = 0;
			$out['msg'] = "Erro ao tentar excluir variacao. Verifique se existe orçamentos com esse item.";
		}
		
		echo json_encode($out);
		die();
	}
	
	public function produtosCategoriaProcessa($id=0, &$ident=0, &$zebra, $item_id=0, $st_fixo, $especial=false){
	
		$results = results($sql=
							"
							SELECT
								categoria.*
							FROM
								categoria
							WHERE
								categoria_id = {$id}
							AND categoria.st_lista_menu = 'S'
							".($especial?'AND especial="S"':'AND (especial is NULL OR especial="N")')."
							-- AND categoria.st_fixo = '{$st_fixo}'
							ORDER BY
								ordem, nome
							");
	
		$edicao = '';
		
		for($i=0,$n=sizeof($results);$i<$n;$i++){
		
			$zebra = !$zebra;
			$fetch = $results[$i];
			
			// onclick='if(this.checked)document.getElementById(\"categoria_{$fetch->id}\").checked=true'
			
			$checked = '';
			
			// Se estiver editado e ja tiver clicado no salvar
			if(request('categoria_id'))
			{
				$checked = in_array($fetch->id, $_REQUEST['categoria_id'])?'checked':'';
			}
			else
			{
				$checked = rows(query($sql="SELECT id FROM itemcategoria WHERE item_id = {$item_id} AND categoria_id = {$fetch->id}"))>0?'checked':'';
			}
			
			// $edicao .= checkbox('tr class='.($zebra?'troca':'')
			$edicao .= checkbox('categoria_id[]', $fetch->id, str_repeat('&nbsp;',$ident*10).$fetch->nome, $checked);
			
			$ident ++ ;
			$edicao .= $this->produtosCategoriaProcessa($fetch->id, $ident, $zebra, $item_id, $st_fixo);
			$ident -- ;
		}
		
		return $edicao;
		
	}
	
	private function detalheCor($cor, $itemcor){

		$return = '';

		$return .= '<div style="xdisplay:none;padding:5px;margin:3px;background-color:#dddddd">';
		$return .= '<table width="600px">';
		$return .= '	<tr>';
		$return .= '		<td width="200px">Default:</td>';
		$return .= '		<td>'.tag('input type="radio" name="st_default" value="'.$cor->id.'" '.($itemcor->st_default=='S'?'checked':'').' ').'</td>';
		$return .= '	</tr>';
		$return .= '	<tr>';
		$return .= '		<td>Ativo:</td>';
		$return .= '		<td>'.select('cor['.$cor->id.'][st_ativo]',$itemcor->st_ativo,'',array('S'=>'Sim','N'=>'Nao')).'</td>';
		$return .= '	</tr>';
		$return .= '	<tr>';
		$return .= '		<td>Imagem:</td>';
		$return .= '		<td>'.($itemcor->imagem!=''?tag('a target="_blank" href="'.PATH_SITE.'img/produtos/'.$itemcor->imagem.'"',tag('img width="40px" src="'.PATH_SITE.PATH_PEQ.$itemcor->imagem.'"')):'').'</td>';
		$return .= '	</tr>';
		$return .= '	<tr>';
		$return .= '		<td>Alterar/Cadastrar:</td>';
		// $return .= '		<td>'.inputFile("file_cor_{$cor->id}",'','').'</td>';
		$return .= '		<td><div id="div_file_cor_'.$cor->id.'">'.tag("a href='javascript:makeInputFileCor(\"{$cor->id}\")'",'alterar/cadastrar imagem').'</div></td>';
		$return .= '	</tr>';
		$return .= '</table>';
		$return .= '</div>';

		return $return;
	} 
	
	
	public function nossosClientes(){
		$this->filtraLogado();
		$this->filtraPermissao('nossosClientes');
		
		//$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Clientes');
		
			
		
		if(request('action')=='editar' || request('action')=='sair'
			||substr(request('action'),0,6)=='salvar'){
			$this->nossosClientesEditar($t);
			return;
		}
		
		if(request('action')=='excluir'){
			$clientes = new clientes(intval(request('id')));
			$clientes->exclui();
			$_SESSION['sucesso'] = tag('p','Item exclu&iacute;do com sucesso.');
		}
		
		$edicao = '';
		
		$grid = new grid();
		$sql = "SELECT 
					id 
					,concat('<img src=\"".PATH_SITE."img/clientes/',imagem,'\" width=\"100px\" />') Imagem
					,nome Nome
					,st_ativo Status
				FROM clientes";
				
		$grid->sql = $sql;

		$filtro = new filtro();

		$grid->filtro = $filtro ;
		$grid->metodo = 'nossosClientes' ;
		$edicao .= $grid->render();
		
		
		$t->edicao = $edicao;
		
		$this->montaMenu($t);
		$this->show($t);
	}
	
	public function nossosClientesEditar($t){
		$this->filtraLogado();
		$this->filtraPermissao('nossosClientes');
		
		if(request('pop')){
			$t = new TemplateAdminPop('admin/tpl.admin-cadastro-generico.html');
		}
	
		
		$clientes = new clientes(request('id'));
		
		if(substr(request('action'),0,6)=='salvar'){
			$next = substr(request('action'),7,strlen(request('action')));
			
			
			$clientes->set_by_array($_REQUEST['clientes']);
			$clientes->ValidarSalvar();
			
			if(trim(@$next)!=''){
				$this->afterSave($next,'clientes');
				return;
			}
			
		}
		if(request('action')=='sair'){$this->afterSave('sair','nossosClientes',$clientes);}
		
		$t->parseBlock('BLOCK_TOOLBAR');
		
		$edicao = '';
		
		$edicao .= inputHidden('id',$clientes->id);
		$edicao .= "<table><tr><td>";
		$edicao .= select('clientes[st_ativo]',$clientes->st_ativo,'Ativo:',array('S'=>'SIM','N'=>'NAO'));
		$edicao .= inputSimples('clientes[nome]',$clientes->nome,'Nome:',80, 160);
		$edicao .= "</td><td valign='top'>";
		$edicao .= inputFile('clientes_imagem','','Imagem:');
		$edicao .= tag('p','A imagem deve ser .jpg  e ter tamanho igual a 153x152px.');
		$edicao .= "<br /><br /><img src='".PATH_SITE."img/clientes/{$clientes->imagem}' alt='' width='153px' height='152px' align='center' />";
		$edicao .= "</td></tr></table>";
		
		$t->edicao = $edicao;
		
		//$this->montaMenu($t);
		if(!request('pop')){
			$this->montaMenu($t);
		}
		$this->show($t);
		
	}
	
	public function clientesEmpresa(){
		
		$this->filtraLogado();
		$this->filtraPermissao('clientesEmpresa');
		
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Clientes Empresa');
		
		if(request('action')=='editar'
			||substr(request('action'),0,6)=='salvar'){
			$this->clientesEmpresaEditar($t);
			return;
		}
		
		if(request('action')=='excluir'){
			$cadastro = new cadastro(intval(request('id')));
			$cadastro->exclui();
		}
		
		$grid = new grid();

		$sql =
				"
				SELECT
					cadastro.id
					-- ,concat('<img src=".PATH_SITE."img/cliente/',cadastro.imagem,' width=130 height=120 />') Imagem
					,cadastro.nome Nome
					,cadastro.data_cadastro Data_Cadastro
					,cadastro.st_ativo Status
				FROM
					cadastro
				WHERE
					tipocadastro_id = ".tipocadastro::getId('CLIENTEEMPRESA')."

					ORDER BY
					cadastro.nome
					";

		$grid->sql = $sql;


		$filtro = new filtro();

		$filtro->add_input('Nome','Nome:');
		$filtro->add_input('Email','Email:');
		$filtro->add_input('Empresa','Empresa:');
		$filtro->add_periodo('DataCadastro','Data cadastro:');

		$grid->filtro = $filtro ;

		$edicao = '';
		$edicao .= $grid->render();

		$t->edicao = $edicao;
		
		$this->montaMenu($t);
		$this->show($t);
	
	}
	
	public function clientesEmpresaEditar($t){
		
		$this->filtraLogado();
		$this->filtraPermissao('clientesEmpresa');

		$cadastro = new cadastro(intval(request('id')));
		$cadastro->set_by_array(@$_REQUEST['cadastro']);

		$cadastro->tipocadastro_id = tipocadastro::getId('CLIENTEEMPRESA');
		
		if(substr(request('action'),0,6)=='salvar'){
		
			$erros = array();

			//$next = substr(request('action'),7,strlen(request('action')));
			
			if(request('cadastro[nome]')){
				$erros[] = "Cadastre o nome do Cliente";
			}
			
			// if(($_FILES['src1']['name'] == '') && ($cadastro->id == 0)){
				// $erros[] = "Cadastre uma imagem";
			// }
			
			if(sizeof($erros) > 0){
				$msg = '';
				foreach($erros as $erro){
					$msg .= tag('p',$erro);
				}
				$_SESSION['erro'] = $msg;
			}
			else {

				$_SESSION['sucesso'] = 'Dados salvos com sucesso';

				// $file_name = $_FILES['src1']['name'];
				// move_uploaded_file($_FILES['src1']['tmp_name'], 'img/cliente/'.$file_name);
				
				// if($_FILES['src1']['size'] > 0){
					// $cadastro->imagem = $_FILES['src1']['name'];
				// }
				
				$cadastro->salva();

				if(request('popup')){
					print js("parent.opener.callback('cliente');this.close()");
					die();
				}
			}
		}
		
		$edicao = '';
		
		$t->parseBlock('BLOCK_TOOLBAR');
		
		$t->h1 = h1('Cliente '.$cadastro->nome);
		
		$edicao = '';
		
		$edicao .= inputHidden('cadastro[id]', $cadastro->id);
		$edicao .= tag('div class="box-block" style="width:50%"',
					tag('h2', 'Dados básicos')
					.select("cadastro[st_ativo]", $cadastro->st_ativo, 'Status Ativo', array('S'=>'Sim','N'=>'Nao'))
					.inputSimples('cadastro[nome]', $cadastro->nome, 'Nome:', 50, 50)
					.inputSimples('cadastro[empresa]', $cadastro->empresa, 'Raz&atilde;o Social:', 50, 60)
		);
		
		// $edicao .= '<div class="box-block" style="width:50%">';
		// $edicao .= tag('h2', 'Imagem');
		
		// $edicao .='<div>';
		// $edicao .='<img src="'.PATH_SITE.'img/cliente/'.$cadastro->imagem.'" width="235" height="225" alt="" />';
		// $edicao .='</div>';
		// $edicao .='<br />';
		// $edicao .='Selecionar Imagem&nbsp;&nbsp;(Tamanho min. 235x225px)<br />';
		// $edicao .= inputHidden('cadastro[imagem]', $cadastro->imagem);
		// $edicao .= tag('input type="file" name="src1" onchange="uploadImg(this, 1),"');
		
		// $edicao .= tag('br clear="all"');			
		// $edicao .= '</div>';	
		
		$t->edicao = $edicao;

		if(!request('popup')){
			$this->montaMenu($t);
		}
		
		$this->show($t);
	}

	public function _paginas(){

		$this->filtraLogado();
		$this->filtraPermissao('paginas');

		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Páginas');

		if(request('action')=='editar'
			||substr(request('action'),0,6)=='salvar'){
			$this->paginasEditar($t);
			return;
		}

		if(request('action')=='excluir'){
			$pagina = new pagina(intval(request('id')));
			$pagina->exclui();
		}

		$grid = new grid();

		$grid->sql = "SELECT
							pagina.id
							,pagina.chave
							,CASE WHEN pagina_pai.id > 0 THEN CONCAT(pagina_pai.nome,' - ',pagina.nome) ELSE pagina.nome END nome
							,pagina.st_tipopagina
							,pagina.st_fixo fixo
						FROM
							pagina
						LEFT OUTER JOIN (SELECT * FROM pagina WHERE pagina_id = 0) AS pagina_pai ON (
							pagina_pai.id = pagina.pagina_id
						)
						WHERE
							1=1
						-- AND 
							-- pagina.id = 36
						AND
							pagina.st_ativo = 'S'
						".($_SESSION['CADASTRO']->email!='dev@ajung.com.br'
							?
							"
							AND (
								pagina.chave IN (
									select valor from config where st_tipocampo = 'TPAGINA'
								)
								OR pagina.pagina_id > 0
							)
							"
							:
							'')
						."
						ORDER BY 3";

		$filtro = new filtro();

		if($_SESSION['CADASTRO']->email!='dev@ajung.com.br'){
			$filtro->botao_novo = false;
		}

		if(config::get('HABILITA_SUB_PAGINA')=='S'){
			$filtro->botao_novo = true;
		}

		$filtro->add_input('Nome','Nome:');

		if($_SESSION['CADASTRO']->email=='dev@ajung.com.br'){
			$filtro->add_input('Chave','Chave:');
		}

		$grid->filtro = $filtro ;

		$t->grid = $grid->render();

		$this->montaMenu($t);
		$this->show($t);
	}
	
	
	public function paginasEditar($t){

		$this->filtraLogado();
		$this->filtraPermissao('paginas');

		$pagina = new pagina(intval(request('id')));

		if(substr(request('action'),0,6)=='salvar'){

			$next = substr(request('action'),7,strlen(request('action')));

			$pagina->set_by_array($_REQUEST['pagina']);
			if(!$pagina->chave){
				$pagina->chave = stringAsTag($pagina->nome);
			}
			$pagina->salva();

			$pagina = new pagina($pagina->id);

			$_SESSION['sucesso'] = 'Dados salvos com sucesso';

			if(trim(@$next)!=''){
				$this->afterSave($next,'paginas');
				return;
			}
		}

		$edicao = '';

		// Joga variavel de edicao para o template

		if($pagina->id){

			//printr($pagina);

			$edicao .= inputHidden('pagina[id]', $pagina->id);

			switch($pagina->st_tipopagina){

				case 'THTML':

					if($_SESSION['CADASTRO']->email == 'dev@ajung.com.br') {
						$edicao .= select('pagina[st_tipopagina]', $pagina->st_tipopagina, 'Tipo da pagina?:', array('THTML'=>'THTML','TPHP'=>'TPHP'));
					}

					if(config::get('HABILITA_SUB_PAGINA')=='S'){
						$edicao .= select('pagina[pagina_id]', $pagina->pagina_id, 'Dentro de:', pagina::opcoesHTML(), true);

						$edicao .= inputSimples('pagina[ordem]', $pagina->ordem, bandeira_br() . ' Ordem:', 30, 50);
					}

					$edicao .= inputSimples('pagina[nome]', $pagina->nome, bandeira_br() . ' Nome:', 30, 50);

					if($this->config->HABILITA_ESPANHOL=='S'){
						$edicao .= inputSimples('pagina[nome_es]', $pagina->nome_es, bandeira_es() . ' Nome:', 30, 50);
					}

					if($this->config->HABILITA_INGLES=='S'){
						$edicao .= inputSimples('pagina[nome_in]', $pagina->nome_in, bandeira_in() . ' Nome:', 30, 50);
					}

					$edicao .= editor('pagina[conteudo]', $pagina->conteudo, bandeira_br().' Conteúdo:');

					if($this->config->HABILITA_ESPANHOL=='S'){
						$edicao .= editor('pagina[conteudo_es]', $pagina->conteudo_es, bandeira_es() . ' Conteúdo:');
					}

					if($this->config->HABILITA_INGLES=='S'){
						$edicao .= editor('pagina[conteudo_in]', $pagina->conteudo_in, bandeira_in() . ' Conteúdo:');
					}

				break;

				case 'TPHP':

					if($_SESSION['CADASTRO']->email == 'dev@ajung.com.br') {
						$edicao .= select('pagina[st_tipopagina]', $pagina->st_tipopagina, 'Tipo da pagina?:', array('THTML'=>'THTML','TPHP'=>'TPHP'));
					}

					$edicao .= inputSimples('pagina[nome]', $pagina->nome, bandeira_br() . ' Nome:', 30, 50);

					if($this->config->HABILITA_ESPANHOL=='S'){
						$edicao .= inputSimples('pagina[nome_es]', $pagina->nome_es, bandeira_es() . ' Nome:', 30, 50);
					}

					if($this->config->HABILITA_INGLES=='S'){
						$edicao .= inputSimples('pagina[nome_in]', $pagina->nome_in, bandeira_in() . ' Nome:', 30, 50);
					}

					if($pagina->var_name!=''){

						//$edicao .= editor('pagina[var_value]', $pagina->var_value, bandeira_br().' '.$pagina->var_name);

						if($this->config->HABILITA_ESPANHOL=='S'){
							$edicao .= editor('pagina[var_value_es]', $pagina->var_value_es, bandeira_es().' '.$pagina->var_name);
						}

						if($this->config->HABILITA_INGLES=='S'){
							$edicao .= editor('pagina[var_value_in]', $pagina->var_value_in, bandeira_in().' '.$pagina->var_name);
						}
					}

					if($_SESSION['CADASTRO']->email=='dev@ajung.com.br'){

						$edicao .= textArea('pagina[conteudo]', $pagina->conteudo, bandeira_br().' Conteúdo:', 100, 20);

					}
					else {

						$edicao .= tag('br');
						$edicao .= tag('br');
						$edicao .= tag('div style="background-color:#eeeeee;border:1px solid #000000;padding:5px;"', $pagina->conteudo);

					}
				break;
			}
		}
		else {
			if($_SESSION['CADASTRO']->email=='dev@ajung.com.br'){

				$edicao .= select('pagina[st_tipopagina]', $pagina->st_tipopagina, 'Ativo?:', array('THTML'=>'THTML','TPHP'=>'TPHP'));

				$edicao .= inputSimples('pagina[chave]', $pagina->chave, bandeira_br() . ' Chave:', 30, 50);

				if(config::get('HABILITA_SUB_PAGINA')=='S'&&$pagina->st_tipopagina=='THTML'){
					$edicao .= select('pagina[pagina_id]', $pagina->pagina_id, 'Dentro de:', pagina::opcoesHTML());

					$edicao .= inputSimples('pagina[ordem]', $pagina->ordem, bandeira_br() . ' Ordem:', 30, 50);
				}

				$edicao .= inputSimples('pagina[nome]', $pagina->nome, bandeira_br() . ' Nome:', 30, 50);

				if($this->config->HABILITA_ESPANHOL=='S'){
					$edicao .= inputSimples('pagina[nome_es]', $pagina->nome_es, bandeira_es() . ' Nome:', 30, 50);
				}

				if($this->config->HABILITA_INGLES=='S'){
					$edicao .= inputSimples('pagina[nome_in]', $pagina->nome_in, bandeira_in() . ' Nome:', 30, 50);
				}

				$edicao .= editor('pagina[conteudo]', $pagina->conteudo, bandeira_br().' Conteúdo:');

				if($this->config->HABILITA_ESPANHOL=='S'){
					$edicao .= editor('pagina[conteudo_es]', $pagina->conteudo_es, bandeira_es() . ' Conteúdo:');
				}

				if($this->config->HABILITA_INGLES=='S'){
					$edicao .= editor('pagina[conteudo_in]', $pagina->conteudo_in, bandeira_in() . ' Conteúdo:');
				}
			}
			elseif(config::get('HABILITA_SUB_PAGINA')=='S'){

				$edicao .= select('pagina[pagina_id]', $pagina->pagina_id, 'Dentro de:', pagina::opcoesHTML());

				$edicao .= inputSimples('pagina[ordem]', $pagina->ordem, bandeira_br() . ' Ordem:', 30, 50);

				$edicao .= inputSimples('pagina[nome]', $pagina->nome, bandeira_br() . ' Nome:', 30, 50);

				if($this->config->HABILITA_ESPANHOL=='S'){
					$edicao .= inputSimples('pagina[nome_es]', $pagina->nome_es, bandeira_es() . ' Nome:', 30, 50);
				}

				if($this->config->HABILITA_INGLES=='S'){
					$edicao .= inputSimples('pagina[nome_in]', $pagina->nome_in, bandeira_in() . ' Nome:', 30, 50);
				}

				$edicao .= editor('pagina[conteudo]', $pagina->conteudo, bandeira_br().' Conteúdo:');

				if($this->config->HABILITA_ESPANHOL=='S'){
					$edicao .= editor('pagina[conteudo_es]', $pagina->conteudo_es, bandeira_es() . ' Conteúdo:');
				}

				if($this->config->HABILITA_INGLES=='S'){
					$edicao .= editor('pagina[conteudo_in]', $pagina->conteudo_in, bandeira_in() . ' Conteúdo:');
				}

			}
		}

		$t->parseBlock('BLOCK_TOOLBAR');

		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}
	
	
	// Paginas
	public function conteudo(){

		$this->filtraLogado();
		$this->filtraPermissao('conteudo');
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		
		//$t->parseBlock('BLOCK_TOOLBAR');

		$t->h1 = tag('h1','Conteúdos');
		
		$edicao = '';	
		
		$edicao .= tag('ul class="ul_lista"',
			// tag('li',tag('a href="'.PATH_SITE.'admin.php/homeContadores"','Contadores ( home )'))
			tag('li',tag('a href="'.PATH_SITE.'admin.php/conteudoSobre"','Sobre'))
			// .tag('li',tag('a href="'.PATH_SITE.'admin.php/conteudoSacc"','SAC'))
			// .tag('li',tag('a href="'.PATH_SITE.'admin.php/opiniao"','Opiniões'))
			// .tag('li',tag('a href="'.PATH_SITE.'admin.php/mosaico"','Mosaico'))
			// .tag('li',tag('a href="'.PATH_SITE.'admin.php/blog"','Blog'))
			// .tag('li',tag('a href="'.PATH_SITE.'admin.php/catalogoOnline"','Catálogo Online'))
		);
		
		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}
	
	public function homeContadores(){
		$this->filtraLogado();
		$this->filtraPermissao('conteudo');
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		
		$t->h1 = tag('h1','Contadores ( home )');
		
		$pagina = new pagina(array('chave'=>'HOME_CONTADOR'));
		
		if(request('action')=='salvar'){
			$pagina->set_by_array($_REQUEST['pagina']);
			$width = 40;
			$height = 40;
			$msg = '';
			if( file_tratamento("produtos_img", $msg, $file) ){
				list($w, $h) = getimagesize($file['tmp_name']);			
				if( isImagemJPG($file['name']) || isImagemPNG($file['name']) || isImagemGIF($file['name']) ){
					if($w!=$width || $h!=$height){
						$msg .= tag("p", "A imagem `". $file['name'] ."´ tem que ter {$width} x {$height} px.");
					}else{
						$pagina->var_value = $file['name'];
						move_uploaded_file($file['tmp_name'], "img/contadores/".$file['name']);
					}				
				}else{
					$msg .= tag('p', 'A imagem `'. $file['name'] .'´ precisa ser JPG, PNG ou GIF');
				}
			}else{
				$msg .= $msg;
			}
						
			if( file_tratamento("clientes_img", $msg, $file) ){
				list($w, $h) = getimagesize($file['tmp_name']);			
				if( isImagemJPG($file['name']) || isImagemPNG($file['name']) || isImagemGIF($file['name']) ){
					if($w!=$width || $h!=$height){
						$msg .= tag("p", "A imagem `". $file['name'] ."´ tem que ter {$width} x {$height} px.");
					}else{
						$pagina->var_value_es = $file['name'];
						move_uploaded_file($file['tmp_name'], "img/contadores/".$file['name']);
					}				
				}else{
					$msg .= tag('p', 'A imagem `'. $file['name'] .'´ precisa ser JPG, PNG ou GIF');
				}
			}else{
				$msg .= $msg;
			}
			
			if( file_tratamento("anosmercado_img", $msg, $file) ){
				list($w, $h) = getimagesize($file['tmp_name']);			
				if( isImagemJPG($file['name']) || isImagemPNG($file['name']) || isImagemGIF($file['name']) ){
					if($w!=$width || $h!=$height){
						$msg .= tag("p", "A imagem `". $file['name'] ."´ tem que ter {$width} x {$height} px.");
					}else{
						$pagina->var_value_in = $file['name'];
						move_uploaded_file($file['tmp_name'], "img/contadores/".$file['name']);
					}				
				}else{
					$msg .= tag('p', 'A imagem `'. $file['name'] .'´ precisa ser JPG, PNG ou GIF');
				}
			}else{
				$msg .= $msg;
			}
			
			if($msg==''){
				$pagina->salva();
				$_SESSION['sucesso'] = tag("p","Dados salvos com sucesso!");
			}else{
				$_SESSION['erro'] = $msg;
			}
			
		}
		
		$edicao = '';	
		
		$edicao .= tag('table class="box_ferramentas"',
					tag('tr',
						tag('td style="text-align:left;"',"<input type='button' onclick='enviar(\"salvar\")' value='Salvar' class='bt_afirmar btn' />"."<a href='".PATH_SITE."admin.php/conteudo/'><span class='bt_negar btn'>Voltar</span></a>")
					)
				);
				
		
		$edicao .= inputHidden("id",$pagina->id);
		$edicao .= inputHidden("pagina[chave]",'HOME_CONTADOR');
		$edicao .= "<table style='border-bottom:1px solid;'><tr><td><label style='font-size:14px;'>Produtos<br /><input type='file' name='produtos_img' /></label></td><td>".($pagina->var_value!=''?"<img src='".PATH_SITE."img/contadores/{$pagina->var_value}' />":"&nbsp;")." </td></tr>";
		$edicao .= "<tr><td colspan='2'>NUM: ".inputSimples('pagina[nome]',$pagina->nome,'',50,50)."( INFO: total itens no site: ".item::qtdItensSite()." )</td></tr></table><br />";
		
		$edicao .= "<table style='border-bottom:1px solid;'><tr><td><label style='font-size:14px;'>Clientes Satisfeitos<br /><input type='file' name='clientes_img' /></label></td><td>".($pagina->var_value!=''?"<img src='".PATH_SITE."img/contadores/{$pagina->var_value_es}' />":"&nbsp;")." </td></tr>";
		$edicao .= "<tr><td colspan='2'>NUM: ".inputSimples('pagina[nome_es]',$pagina->nome_es,'',50,50)."( INFO: total clientes no site: ".cadastro::totalClientesSite()." )</td></tr></table><br />";
		
		$edicao .= "<table style='border-bottom:1px solid;'><tr><td><label style='font-size:14px;'>Anos de Mercado<br /><input type='file' name='anosmercado_img' /></label></td><td>".($pagina->var_value!=''?"<img src='".PATH_SITE."img/contadores/{$pagina->var_value_in}' />":"&nbsp;")." </td></tr>";
		$edicao .= "<tr><td colspan='2'>NUM: ".inputSimples('pagina[nome_in]',$pagina->nome_in,'',50,50)."</td></tr></table><br />";
		
		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}
	
	
	public function conteudoSobre(){

		$this->filtraLogado();
		$this->filtraPermissao('conteudo');
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		
		//$t->parseBlock('BLOCK_TOOLBAR');

		$t->h1 = tag('h1','Conteúdo Sobre');
		
		$edicao = '';	
		
		$edicao .= tag('table class="box_ferramentas"',
						tag('tr',
							//tag('td style="text-align:right;"',"<input type='submit' value='Salvar' class='bt_afirmar' />")
							tag('td style="text-align:left;"',"<a href='".PATH_SITE."admin.php/conteudo/'><span class='bt_negar btn'>Voltar</span></a>")
						)
					);
		
		// Destaque Sobre 
		$edicao .= '<div class="lt_box">';
		$edicao .= tag('h2','Destaque Sobre');
		$edicao .= inputHidden('destaquesobre_id',0);		
		$query = query("SELECT * FROM destaquesobre");
		while($fetch=fetch($query)){
			$edicao .= tag('span class="sb_box"',tag('h3',inputSimples('destaquesobre['.$fetch->id.'][titulo]',$fetch->titulo,'',100,100) )
					.inputHidden('destaquesobre['.$fetch->id.'][st_ativo]','S')
					//.inputHidden('destaquesobre['.$fetch->id.'][titulo]',$fetch->titulo)
					."<textarea rows='8' name='destaquesobre[{$fetch->id}][texto]' style='width:90%;margin:0 auto; display:block;'>{$fetch->texto}</textarea>"
					."<input type='button' value='salvar' data-id='{$fetch->id}' class='bt_afirmar sv_destaquesobre' />"
					."<span class='msg'></span>"
				);
		}			
		$edicao .= "<script>			
			$(document).ready(function(){
				$('.sv_destaquesobre').bind('click',function(){
					obj = $(this);
					obj.next().html('Aguarde...');
					
					data_destaque = $('#formPrincipal');
					$('#destaquesobre_id').val(obj.data('id'));					
					$.ajax({
						url : '".PATH_SITE."admin.php/salvaDestaquesobre/?'+data_destaque.serialize(),
						success : function(out){
							console.log(out);
							console.log(obj.parent().children('.msg'));
							obj.next().html(out);
						}
					});					
				});
			});			
			</script>
		";		
		$edicao .= '</div>';


		
		$pagina = new pagina(array('chave'=>'PAGINA_SOBRE'));
		$edicao .= tag('div  class="lt_box"',tag('h2','Sobre')
			.inputHidden('pagina_id',$pagina->id)
			.inputHidden('pagina[st_ativo]','S')
			.inputHidden('pagina[chave]','PAGINA_SOBRE')
			.inputHidden('pagina[nome]','Sobre ')
			
			.tag('table',
				tag('tr',
					tag('td',
						tag('label',"<p>Imagem Sobre(1035x369px)</p><input type='file' name='imagemsobre' />")
						.($pagina->imagem!=''?"<br /><br /><img id='imgsobre_{$pagina->id}' src='".PATH_SITE."img/{$pagina->imagem}' width='300px' />":"")
					)
                    /*
					.tag('td',
						tag('label style="border:0px solid;display:inline-block;margin:0 6px 0 10px;"',
							tag('p','Texto Home')
							.textArea('pagina[conteudo]',$pagina->conteudo,'',40,8)
						)
						.tag('label style="border:0px solid;display:inline-block;margin:0 6px 0 10px;"',
							tag('p','Texto Home Comentario')
							.textArea('pagina[var_value]',$pagina->var_value,'',40,8)
						)
					)
                    */
				)
				.tag('tr',
					tag('td colspan="1"',
						"<input type='button' value='salvar' data-id='{$pagina->id}' class='bt_afirmar sv_paginasobre' />"
						."<span id='msg_sobre'></span>"
					)
				)
			)
		);
		
		$edicao .= "<script>			
			$(document).ready(function(){
				
			
				
				$('.sv_paginasobre').bind('click',function(){
					obj = $(this);
					obj.next().html('Aguarde...');
					
					data_paginasobre = $('#formPrincipal');
					$('#pagina_id').val(obj.data('id'));										
					
					var options = {
						dataType:'json',
						beforeSubmit: showRequest,
						success: showResponse,
						resetForm: false
					};
					$('#formPrincipal').ajaxForm(options);	
					$('#formPrincipal').attr('action','".PATH_SITE."admin.php/salvaPaginaSobre/')
					
					function showRequest(formData, jqForm, options){}

					function showResponse(responseText, statusText, xhr, form){
						obj.next().html(responseText.msg);
						if(responseText.img!=''){
							$('#imgsobre_{$pagina->id}').attr('src',responseText.img);
						}
					}
					
					$('#formPrincipal').submit();		
				});
			
			});			
			</script>";
		
		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}
	public function salvaDestaquesobre(){
		$id = intval($_REQUEST['destaquesobre_id']);
		$destaquesobre = new destaquesobre($id);
		$destaquesobre->set_by_array($_REQUEST['destaquesobre'][$id]);			
		
		if($destaquesobre->salva()){
			echo "<p style='color:#176e0b;font-size:14px;padding-left:6px;display:inline-block;'>Dados salvos com sucesso!</p>";
		}else{
			echo "<p style='color:#6e250b;font-size:14px;padding-left:6px;display:inline-block;'>Erro ao salvar, tente mais tarde.</p>";
		}
	}
	public function salvaOquefazemos(){
		$id = intval($_REQUEST['oquefazemos_id']);
		$oquefazemos = new oquefazemos($id);
		$oquefazemos->set_by_array($_REQUEST['oquefazemos'][$id]);			
		
		if($oquefazemos->salva()){
			echo "<p style='color:#176e0b;font-size:14px;padding-left:6px;display:inline-block;'>Dados salvos com sucesso!</p>";
		}else{
			echo "<p style='color:#6e250b;font-size:14px;padding-left:6px;display:inline-block;'>Erro ao salvar, tente mais tarde.</p>";
		}
	}
	public function salvaPaginaSobre(){		
		$id = intval($_REQUEST['pagina_id']);
		$pagina = new pagina($id);
		$pagina->set_by_array($_REQUEST['pagina']);		
		
		$out = new stdclass();
		$out->img = "";
		$out->msg = "";
		
		if(file_tratamento('imagemsobre', $msg, $file)){		
			$arr = getimagesize($file['tmp_name']);	
			if($arr[0]!=1035 || $arr[1]!=369){
				$out->msg .= "<p style='color:#6e250b;font-size:14px;padding-left:6px;display:inline-block;'>A imagem precisa ter 1035x369px.</p>";
			}else{
				$pagina->imagem = "sobre_".$file['name'];
				move_uploaded_file($file['tmp_name'],'img/'.$pagina->imagem);
				$out->img = PATH_SITE."img/{$pagina->imagem}";
			}
		}else{
			$out->msg .=  "<p style='color:#6e250b;font-size:14px;padding-left:6px;display:inline-block;'>{$msg}</p>";
		}
		
		if($pagina->salva()){
			$out->msg .= "<p style='color:#176e0b;font-size:14px;padding-left:6px;display:inline-block;'>Dados salvos com sucesso!</p>";
		}else{
			$out->msg .= "<p style='color:#6e250b;font-size:14px;padding-left:6px;display:inline-block;'>Erro ao salvar, tente mais tarde.</p>";
		}
		
		echo json_encode($out);
		die();
	}
	
	public function conteudoSacc(){
		$this->filtraLogado();
		$this->filtraPermissao('conteudo');
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = tag('h1','Conteúdo SAC');
		
		$edicao = '';	
		
		$edicao .= tag('table class="box_ferramentas"',
						tag('tr',
							//tag('td style="text-align:right;"',"<input type='submit' value='Salvar' class='bt_afirmar' />")
							tag('td style="text-align:left;"',"<a href='".PATH_SITE."admin.php/conteudo/'><span class='bt_negar'>Voltar</span></a>")
						)
					);
		
		$pagina = new pagina(array('chave'=>'PAGINA_SACC'));
		$edicao .= tag('div  class="lt_box"',tag('h2','SAC')
			.inputHidden('pagina_id',$pagina->id)
			.inputHidden('pagina[st_ativo]','S')
			.inputHidden('pagina[chave]','PAGINA_SACC')
			.inputHidden('pagina[nome]','SAC ')
			
			.tag('table',
				tag('tr',
					tag('td',
						($pagina->chave == 'PAGINA_SACC' ?
							tag('p','Telefone')
							.inputSimples('pagina[telefone]',$pagina->telefone,'',100,100)
							.tag('p','E-mail')
							.inputSimples('pagina[email]',$pagina->email,'',100,100)
							: '' )
					)
				)
				.tag('tr',
					tag('td',
						tag('label style="border:0px solid;display:inline-block;margin:0 6px 0 10px;"',
							tag('p','Texto SAC')
							.textArea('pagina[conteudo]',$pagina->conteudo,'',60,8)
						)
					)
                    /*
					.tag('td',
						tag('label style="border:0px solid;display:inline-block;margin:0 6px 0 10px;"',
							tag('p','Texto Home Sacc')
							.textArea('pagina[var_value]',$pagina->var_value,'',60,8)
						)
					)
                    */
				)
				.tag('tr',
					tag('td',
						"<input type='button' value='salvar' data-id='{$pagina->id}' class='bt_afirmar sv_paginasacc' />"
						."<span id='msg_sacc'></span>"
					)
				)
			)
		);
		
		$edicao .= "<script>			
			$(document).ready(function(){				
				$('.sv_paginasacc').bind('click',function(){
					obj = $(this);
					obj.next().html('Aguarde...');
					
					data_paginasobre = $('#formPrincipal');
					$('#pagina_id').val(obj.data('id'));										
					
					var options = {
						dataType:'json',
						beforeSubmit: showRequest,
						success: showResponse,
						resetForm: false
					};
					$('#formPrincipal').ajaxForm(options);	
					$('#formPrincipal').attr('action','".PATH_SITE."admin.php/salvaPaginaSacc/')
					
					function showRequest(formData, jqForm, options){}

					function showResponse(responseText, statusText, xhr, form){
						obj.next().html(responseText.msg);
					}
					
					$('#formPrincipal').submit();		
				});
			
			});			
			</script>";
			
			/*
			$edicao .= '<div  class="lt_box">';
			$edicao .= tag('h2','Downloads');
			$edicao .= tag('p',
				inputHidden('download_novo[st_ativo]','S')
				.inputSimples('download_novo[nome]','','Nome',100,120)
				."<br /><label>Arquivo<br /><input type='file' name='download_novo_file' /></label>"
				."<br /><input type='button' value='salvar' class='bt_afirmar sv_download' />"
				."<span id='msg_down'></span>"
			);
			
			$edicao .= "<br />";
			
			$query = query("SELECT * FROM download ORDER BY id DESC");
			$edicao .= "<table class='grade' border='1'>";
			$edicao .= tag('tr',tag('th','Nome').tag('th','Arquivo').tag('th','Ativo').tag('th','Excluir'));
			$edicao .= "<tbody id='download_linhas'>";
			while($fetch=fetch($query)){
				$edicao .= tag("tr id='linha_{$fetch->id}'",
					tag('td',
						$fetch->nome
					)
					.tag('td',
						tag("a href='".PATH_SITE."download/{$fetch->arquivo}' target='_blank'",$fetch->arquivo)
					)
					.tag('td',
						select("st_ativo[{$fetch->id}]",$fetch->st_ativo,'',array('S'=>'SIM','N'=>'NAO'))
					)
					.tag('td',
						"<span  class='bt_negar download_x' data-id='{$fetch->id}'>x</span>"
					)
				);
			}
			$edicao .= "</tbody></table>";
			$edicao .= '</div>';
			
			$edicao .= "<script>			
			$(document).ready(function(){				
				$('.sv_download').bind('click',function(){
					obj = $(this);
					obj.next().html('Aguarde...');
					
					data_paginasobre = $('#formPrincipal');										
					
					var options = {
						dataType:'json',
						beforeSubmit: showRequest,
						success: showResponse,
						resetForm: false
					};
					$('#formPrincipal').ajaxForm(options);	
					$('#formPrincipal').attr('action','".PATH_SITE."admin.php/salvaDownload/')
					
					function showRequest(formData, jqForm, options){}

					function showResponse(responseText, statusText, xhr, form){
						obj.next().html(responseText.msg);
						_linha = responseText.linha;
						$('#download_linhas').prepend(_linha);
						deletarDownload();
					}
					
					$('#formPrincipal').submit();		
				});
			
			});	

			function deletarDownload(){
				$('.download_x').bind('click',function(){
					x_id = $(this).data('id');			
					$(this).parent().html('Aguarde...');
					$.ajax({
						url : '".PATH_SITE."admin.php/deletarDownload/',
						data : {id : x_id},
						dataType:'json',
						success : function(out){
							$('#msg_down').html(out.msg);
							$('#linha_'+out.id).remove();
						}
					});
				});
			}
			
			deletarDownload();
			
			</script>";
			*/
		
		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}
	public function salvaPaginaSacc(){
		$pagina = new pagina(intval($_REQUEST['pagina_id']));
		$pagina->set_by_array($_REQUEST['pagina']);
		$out = new stdclass();
		$out->msg = '';
		if($pagina->conteudo!='' && $pagina->var_value!=''){
			if($pagina->salva()){
				$out->msg .= "<p style='color:#176e0b;font-size:14px;padding-left:6px;display:inline-block;'>Dados salvos com sucesso!</p>";
			}else{
				$out->msg .= "<p style='color:#6e250b;font-size:14px;padding-left:6px;display:inline-block;'>Erro ao salvar, tente mais tarde.</p>";
			}
		}else{
			$out->msg .= "<p style='color:#6e250b;font-size:14px;padding-left:6px;display:inline-block;'>Digite o(s) texto(s).</p>";			
		}
		echo json_encode($out);
		die();
	}
	public function salvaDownload(){
		$download = new download();
		$download->set_by_array($_REQUEST['download_novo']);
		$erro = false;
		$out = new stdclass();
		$out->linha = "";
		$out->msg = "";
		
		if($download->nome==''){
			$out->msg .= tag("p style='color:#6e250b;font-size:14px;padding-left:6px;display:inline-block;'",'Digite o nome do arquivo.');
			$erro = true;
		}		
		
		if(file_tratamento('download_novo_file', $msg, $file)){
			$arr = explode('.',$file['name']);			
			$download->arquivo = $download->nome.".".$arr[sizeof($arr)-1];
			move_uploaded_file($file['tmp_name'],"download/{$download->arquivo}");
		}else{
			$out->msg .=  "<p style='color:#6e250b;font-size:14px;padding-left:6px;display:inline-block;'>{$msg}</p>";
			$erro = true;
		}
		
		if(!$erro){
			if($download->salva()){
				$out->msg .= tag("p style='color:#176e0b;font-size:14px;padding-left:6px;display:inline-block;'",'Arquivo salvo com sucesso!');
				$out->linha .= tag("tr id='linha_{$download->id}'",
					tag('td',
						$download->nome
					)
					.tag('td',
						tag("a href='".PATH_SITE."download/{$download->arquivo}' target='_blank'",$download->arquivo)
					)
					.tag('td',
						select("st_ativo[{$download->id}]",$download->st_ativo,'',array('S'=>'SIM','N'=>'NAO'))
					)
					.tag('td',
						"<span  class='bt_negar download_x' data-id='{$download->id}'>x</span>"
					)
				);
			}else{
				$out .= tag("p style='color:#6e250b;font-size:14px;padding-left:6px;display:inline-block;'",'Erro ao salvar novo arquivo, tente mais tarde.');
			}
		}
		echo json_encode($out);
		die();
	}
	public function deletarDownload(){
		$id = $_REQUEST['id'];
		$download = new download($id);
		$out = new stdclass();
		$out->id = $id;
		@unlink("download/{$download->arquivo}");
		if($download->exclui()){
			$out->msg = tag("p style='color:#176e0b;font-size:14px;padding-left:6px;display:inline-block;'",'Arquivo excluído com sucesso.');
		}else{
			$out .= tag("p style='color:#6e250b;font-size:14px;padding-left:6px;display:inline-block;'",'Erro ao excluir o arquivo, tente mais tarde.');
		}
		echo json_encode($out);
		die();
	}
	
	public function opiniao(){
		$this->filtraLogado();
		$this->filtraPermissao('conteudo');
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');		
		$t->h1 = tag('h1','Opiniões');
		
		$edicao = '';	
		
		$edicao .= tag('table class="box_ferramentas"',
						tag('tr',
							//tag('td style="text-align:right;"',"<input type='submit' value='Salvar' class='bt_afirmar' />")
							tag('td style="text-align:left;"',"<a href='".PATH_SITE."admin.php/conteudo/'><span class='bt_negar'>Voltar</span></a>")
						)
					);
		
		$edicao .= tag('a href="'.PATH_SITE.'admin.php/editarOpiniao"',tag('span class="bt_afirmar"','incluir nova Opinião'));
		
		$query = query('SELECT * FROM opiniao ORDER BY ordem');	
		$edicao .= "<br />";
		$edicao .= "<table class='grade'>";
		$edicao .= tag('tr',tag('th','Titulo').tag('th','Autor').tag('th','Ordem').tag('th','Ativo').tag('th','Data de Cadastro'));
		while($fetch=fetch($query)){
			$edicao .= tag('tr onclick="javascript:window.location=\''.PATH_SITE.'admin.php/editarOpiniao/'.$fetch->id.'\'" ',
				 tag('td',$fetch->titulo)
				.tag('td',$fetch->autor)
				.tag('td',$fetch->ordem)
				.tag('td',getStAtivoFormatado($fetch->st_ativo))
				.tag('td',$fetch->data_cadastro)
			);
		}
		$edicao .= "</table>";
		
		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}
	public function editarOpiniao($id=0){
		$this->filtraLogado();
		$this->filtraPermissao('conteudo');
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');		
		
		if(array_key_exists('id',$_REQUEST)){
			$id = intval(request('id'));
		}
		$opiniao = new opiniao($id);
		
		if(request('action')=='salvar'){
			$erros = array();
			
			$opiniao->set_by_array(request('opiniao'));
			$opiniao->salva();
		}

		$t->h1 = tag('h1','Opinião - '.$opiniao->titulo);
			
		$edicao = '';	
		
		
		$edicao .= tag('table class="box_ferramentas"',
						tag('tr',
							tag('td style="text-align:left;"',
								"<input type='submit' value='Salvar' class='bt_afirmar' onclick='javascript: $(\"#action\").val(\"salvar\");' />"
								."<a href='".PATH_SITE."admin.php/opiniao'><span class='bt_negar'>Voltar</span></a>"
							)
						)
					);
		
		$edicao .= tag('div class="lt_box"',
			tag('div class="lt_box"',
				tag('table style="width:100%;"',
					tag('tr',
						tag('td',
							inputHidden('id',$opiniao->id)
							.inputSimples('opiniao[titulo]',$opiniao->titulo,'Titulo',100,160)
						)
						.tag('td',
							select('opiniao[st_ativo]',$opiniao->st_ativo,'Ativo',array('S'=>'SIM','N'=>'NAO'))
							.inputSimples('opiniao[ordem]',($opiniao->ordem>0?$opiniao->ordem:100),'Ordem',100,160)
						)
					)
					.tag('tr',
						tag('td colspan="2"',
							tag('label',
								'Texto<br /><textarea name="opiniao[texto]" id="opiniao_nova[texto]" style="width:400px; height:150px;">'.$opiniao->texto.'</textarea>'
							)
						)
					)
					.tag('tr',
						tag('td',
							inputSimples('opiniao[autor]',$opiniao->autor,'Autor',100,160)
						)
						.tag('td',
							inputSimples('opiniao[area]',$opiniao->area,'Area',100,160)
						)
					)
					.tag('tr',
						tag('td',
							tag('label ',tag('span class="help"',' Imagem( tamanho : 155x155px;  Imagem : png ou gif com fundo transparente. )').'<br />'
								."<input type='file' name='img_opiniao' />"
							)
						)
						.tag('td',
							($opiniao->imagem!=''?"<img src='".PATH_SITE."img/opiniao/{$opiniao->imagem}' />":"&nbsp;")
						)
					)
				)
			)
		);
		
		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}
	
	public function mosaico(){
		$this->filtraLogado();
		$this->filtraPermissao('conteudo');
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');		
		$t->h1 = tag('h1','Mosaico');
		
		$edicao = '';	
		
		$edicao .= tag('table class="box_ferramentas"',
						tag('tr',
							//tag('td style="text-align:right;"',"<input type='submit' value='Salvar' class='bt_afirmar' />")
							tag('td style="text-align:left;"',"<a href='".PATH_SITE."admin.php/conteudo/'><span class='bt_negar'>Voltar</span></a>")
						)
					);
					
		$Tmosaico = new Template('part_mosaico_admin.html');
		$mosaico = new mosaico();
		$query = query("SELECT MAX(posicao) POS FROM mosaico LIMIT 1");
		$pos = fetch($query);
		$blocos = '';
		if($pos->POS>27){
			$pagina = floor($pos->POS/27);
			$resto = $pos->POS%27;
			
			for($i=1;$i<=($pagina-1);$i++){
				$posicao = $i*27;
				$blocos .= $mosaico->addBlock($posicao); // bloco 1
				$blocos .= $mosaico->addBlock($posicao+12); // bloco 2
				$blocos .= $mosaico->addBlock($posicao+18); // bloco 3
			}
			
			$posicaoFinal = $pagina*27;
			if($resto>=1&&$resto<=12){
				$blocos .= $mosaico->addBlock($posicaoFinal);
			}
			if($resto>=13&&$resto<=18){
				$blocos .= $mosaico->addBlock($posicaoFinal);
				$blocos .= $mosaico->addBlock($posicaoFinal+12);
			}
			if($resto>=19&&$resto<=27){
				$blocos .= $mosaico->addBlock($posicaoFinal);
				$blocos .= $mosaico->addBlock($posicaoFinal+12);
				$blocos .= $mosaico->addBlock($posicaoFinal+18);
			}			
		}
		
		$Tmosaico->blocos = $blocos;


		$Tmosaico->filtroprodutos = filtroProdutos();
		
		$Tmosaico->path = PATH_SITE;			
		
		$Tmosaico->mosaico = new mosaico();
		$edicao .= $Tmosaico->getContent();
		
					
		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}
	public function salvaItemMosaico(){
		$item_id = $_REQUEST['item_id'];
		$pos = $_REQUEST['pos'];
		
		$out = array();
		$out[0] = 0;
		$out[1] = '';
		// item atual na posicao
		$mosaico = new mosaico(array("posicao"=>$pos));
		if($mosaico->id){
			$item = new item($mosaico->item_id);
			$out[1] = "<span class='bt_excluir'>X</span><img src='".PATH_SITE."img/produtos/{$item->imagem}' class='deck_imagem draggable' data-id='{$item->id}' />";
		}
		
		//$mosaico = new mosaico(array("item_id"=>$item_id,"posicao"=>$pos));
		$mosaico->item_id = $item_id;
		$mosaico->posicao = $pos;
		if($mosaico->salva()){
			$out[0] = 1;
		}else{
			$out[0] = 0;
		}
		echo json_encode($out);
		die();
	}
	public function excluirItemMosaico(){
		$pos = $_REQUEST['pos'];
		$mosaico = new mosaico(array("posicao"=>$pos));
		if($mosaico->id){
			$out = array();
			$item = new item($mosaico->item_id);
			if($mosaico->exclui()){
				$out[0] = 1;
				$out[1] = "<img src='".PATH_SITE."img/produtos/{$item->imagem}' class='deck_imagem draggable' data-id='{$item->id}' />";
			}else{
				$out[0] = 0;
			}
			echo json_encode($out);
		}
		die();
	}
	public function addBlockMosaico(){
		$qtdblock = $_REQUEST['qtdblock'];
		$mosaico = new mosaico();
		$out = array();
		$out[0] = 1;
		$out[1] = $mosaico->addBlock($qtdblock);		
		
		echo json_encode($out);
	}
	
	public function blog(){
		$this->filtraLogado();
		$this->filtraPermissao('conteudo');
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');		
		$t->h1 = tag('h1','Blog');
		
		$edicao = '';	
		
		$edicao .= tag('table class="box_ferramentas"',
						tag('tr',
							//tag('td style="text-align:right;"',"<input type='submit' value='Salvar' class='bt_afirmar' />")
							tag('td style="text-align:left;"',"<a href='".PATH_SITE."admin.php/conteudo/'><span class='bt_negar'>Voltar</span></a>")
						)
					);
		
		$edicao .= tag('a href="'.PATH_SITE.'admin.php/editarPost"',tag('span class="bt_afirmar"','incluir novo Post'));
		
		
		$_ini = $ini = request('ini')?intval(request('ini'))-1:0;
		$fim = 15;
		
		$rows = rows(query("SELECT * FROM post"));
		if($rows>$fim){
			$resto = ($rows%$fim)>0?1:0;
			$pages = (floor($rows/$fim))+$resto;
			$edicao .= "<div class='paginador'>";
			$edicao .= "<a href='".PATH_SITE."admin.php/blog/?ini=1'><<</a>";
			for($i=0;$i<$pages;$i++){
				$selected = '';
				if($i==$ini){$selected='selected';}
				$_i = $i+1;
				$edicao .= "<a href='".PATH_SITE."admin.php/blog/?ini={$_i}' class='{$selected}'>{$_i}</a>";
			}
			$edicao .= "<a href='".PATH_SITE."admin.php/blog/?ini={$pages}'>>></a>";
			$edicao .= "</div>";
		}
		
		$ini = $ini*$fim;
		$query = query("SELECT * FROM post ORDER BY id DESC LIMIT {$ini},{$fim}");	
		$edicao .= "<br />";
		$edicao .= "<table class='grade'>";
		$edicao .= tag('tr',tag('th','Id').tag('th','Titulo').tag('th','Autor').tag('th','Publicado').tag('th','Data da Postagem'));
		while($fetch=fetch($query)){
			$edicao .= tag('tr onclick="javascript:window.location=\''.PATH_SITE.'admin.php/editarPost/'.$fetch->id.'\'" ',
				 tag('td',$fetch->id)
				.tag('td',$fetch->titulo)
				.tag('td',$fetch->autor)
				.tag('td',($fetch->st_ativo=='S'?'<img src="'.PATH_SITE.'admin/assets/bola_verde.png" />':'<img src="'.PATH_SITE.'admin/assets/bola_vermelha.png" />'))
				.tag('td',$fetch->data_cadastro)
			);
		}
		$edicao .= "</table>";
		
		if($rows>$fim){
			$resto = ($rows%$fim)>0?1:0;
			$pages = (floor($rows/$fim))+$resto;
			$edicao .= "<div class='paginador'>";
			$edicao .= "<a href='".PATH_SITE."admin.php/blog/?ini=1'><<</a>";
			for($i=0;$i<$pages;$i++){
				$selected = '';
				if($i==$_ini){$selected='selected';}
				$_i = $i+1;
				$edicao .= "<a href='".PATH_SITE."admin.php/blog/?ini={$_i}' class='{$selected}'>{$_i}</a>";
			}
			$edicao .= "<a href='".PATH_SITE."admin.php/blog/?ini={$pages}'>>></a>";
			$edicao .= "</div>";
		}
		
		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}
	public function editarPost($id=0){
		$this->filtraLogado();
		$this->filtraPermissao('conteudo');
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');		
		
		if(array_key_exists('id',$_REQUEST)){
			$id = intval(request('id'));
		}
		$post = new post($id);
		$sucesso = '';
		
		if(request('action')=='salvar'){
			$erros = '';
			$out= array();
			$post->set_by_array(request('post'));
			$post->salva();
			$post->salvaImagens();
			
			//printr($_REQUEST);die();
			
			if(array_key_exists('post_erro',$_SESSION)){
				$out[0] = 0;
				$out[1] = $_SESSION['erro'] = $_SESSION['post_erro'];
				$out[2] = 1;	
				unset($_SESSION['post_erro']);
			}else{
				$out[0] = 1;
				$out[1] = $sucesso .= tag("p","Dados Salvos com sucesso!");
				$out[2] = 1;				
			}
			
			// echo json_encode($out);
			// die();
		}
		
		if(request('action')=='salvaComments'){
			$postcomment = new postcomment(request('postcomment_id'));
			$out= array();
			if($postcomment->id){
				$postcomment->set_by_array($_REQUEST['postcomment'][$postcomment->id]);
				$postcomment->salva();
				$out[0] = 1;
				$out[1] = $sucesso .= tag("p","Comentario Salvo!");
				$out[3] = $postcomment->id;
			}
			
			// echo json_encode($out);
			// die();
		}
		
		if($sucesso!=''){
			$_SESSION['sucesso'] = $sucesso;
		}

		$t->h1 = tag('h1','Post - '.$post->titulo);
			
		$edicao = '';	
		
		
		$edicao .= tag('table class="box_ferramentas"',
						tag('tr',
							tag('td style="text-align:left;"',
								"<input type='button' value='Salvar' class='bt_afirmar' onclick='javascript: salvarPost();' />"
								."<a href='".PATH_SITE."admin.php/blog'><span class='bt_negar'>Voltar</span></a>"
							)
						)
					);
					
		$edicao .= tag("div style='display:none;' class='msg_salvo'","&nbsp;");
		
		$edicao .= tag('div class="lt_box"',
			tag('div class="lt_box"',
				tag('table style="width:100%;"',
					tag('tr',
						tag('td',
							inputHidden('id',$post->id)
							.inputSimples('post[titulo]',$post->titulo,'Titulo',100,160)
						)
						.tag('td',
							select('post[st_ativo]',$post->st_ativo,'Publicado',array('S'=>'SIM','N'=>'NAO'))
						)
					)
					.tag('tr',
						tag('td colspan="2"',
							tag('div style="border:0px solid; width:100%;"',
								//textAreaCkeditor("post[texto]", $post->texto, "Texto", null, null, null, 'style="width:100%; height:100px;"')
								'<br /><strong>Texto</strong><br /> <textarea name="post[texto]" class="ckeditor">'.$post->texto.'</textarea>'
							)
						)
					)
					.tag('tr',
						tag('td colspan="2"',
							inputSimples('post[autor]',$post->autor,'Autor',100,160)
							.inputSimples('post[area]',$post->area,'Area',100,160)
						)
					)
				)
			)
		);
		$edicao .= tag("h2","Imagens <span onclick='javascript: $(\"#p_comments\").slideToggle();' style='cursor:pointer;color:#9a5a06;'>(clique aqui para esconder/mostrar as imagens)</span>");
		$edicao .= "<div id='p_comments' style='display:none;'>";
		
		$edicao .= tag("h3","Nova Imagem");
		$edicao .= tag("p style='color:red;'", "Tamanho ideal para as imagens : 1048x437px");
		$edicao .= "<table>";
		$edicao .= tag('tr',
						tag('td',"<input type='file' name='post_img_nova'")
					);
		$edicao .= "</table>";
		$edicao .= "<hr />";
		$edicao .= "<br />";
		
		
		if($post->id){
			$query = query("SELECT * FROM postimagem WHERE post_id = {$post->id}");
			$edicao .= '<div class="lt_box">';
			$edicao .= '<table class="grid">';
			while($fetch=fetch($query)){
				$edicao .= tag('tr',
								inputHidden("postimagem[{$fetch->id}][id]",$fetch->id)
								.tag('td',select("postimagem[{$fetch->id}][st_ativo]",$fetch->st_ativo,"Ativo",array("S"=>"SIM","N"=>"NAO")))
								.tag('td',"<input type='file' name='post_img_{$fetch->id}'")
								.tag('td','<img src="'.PATH_SITE.'img/postimagem/'.$fetch->imagem.'" width="340px" />')
								.tag('td',checkbox( "img_excluir[{$fetch->id}]", $fetch->id, "Excluir"))
							);
			}
			$edicao .= '</table>';
			$edicao .= '</div>';
		}
		$edicao .= "</div>";
		
		$edicao .= "<br />";
		$edicao .= "<hr />";
		$edicao .= "<div>";
		$edicao .= tag("h2","Comentários");
		$edicao .= inputHidden('postcomment_id',0);
		$query = query("SELECT * FROM postcomment WHERE post_id={$post->id}  AND (postcomment_id = 0 OR postcomment_id is NULL)");
		while($fetch=fetch($query)){
			$_query = query("SELECT * FROM postcomment WHERE post_id = {$post->id}  AND postcomment_id = {$fetch->id}");
			$_rows = rows($_query);
			$edicao .= "<p>";
			$edicao .= tag("table style='width:100%;border:1px solid #DDD;'",
							tag("tr",
								tag("td",
									"<input type='hidden' value='{$fetch->st_ativo}' name='postcomment[{$fetch->id}][st_ativo]' id='postcomment_ativo_{$fetch->id}' />"
									.tag("span class='pc_on_off' data-id='{$fetch->id}'",tag("span class='c_inicador ".($fetch->st_ativo=='S'?"on":"off")."'","&nbsp;"))
								)
								.tag("td","Autor: {$fetch->autor}")
								.tag("td","E-mail: {$fetch->email}")
								.tag("td width='18%'","Website: {$fetch->website}")
							)
							.tag("tr",
								tag("td colspan='3'",textArea("postcomment[{$fetch->id}][comentario]",$fetch->comentario,'',null, 4, $max_length=null, "style='width:100%;'"))
								.tag("td valign='middle' align='center'",
									"<span class='bt_afirmar com_salva' data-id='{$fetch->id}' id='com_salva_{$fetch->id}'>Salva Comentario</span>"
									.($_rows>0?"<br /><span class='bt_afirmar' onclick='javascript: $(\"#comments_reply_{$fetch->id}\").slideToggle();'>Mostrar Replys</span>":"")
								)
							)
						
			);
			$edicao .= "<span id='comments_reply_{$fetch->id}' style='display:none;width:100%;'>";
			while($_fetch=fetch($_query)){
				$edicao .= tag("table style='width:95%;border:1px solid #DDD;margin-left:5%;'",
							tag("tr",
								tag("td",
									"<input type='hidden' value='{$_fetch->st_ativo}' name='postcomment[{$_fetch->id}][st_ativo]' id='postcomment_ativo_{$_fetch->id}' />"
									.tag("span class='pc_on_off' data-id='{$_fetch->id}'",tag("span class='c_inicador ".($_fetch->st_ativo=='S'?"on":"off")."'","&nbsp;"))
								)
								.tag("td","Autor: {$_fetch->autor}")
								.tag("td","E-mail: {$_fetch->email}")
								.tag("td width='18%'","Website: {$_fetch->website}")
							)
							.tag("tr",
								tag("td colspan='3'",textArea("postcomment[{$_fetch->id}][comentario]",$_fetch->comentario,'',null, 4, $max_length=null, "style='width:100%;'"))
								.tag("td valign='middle' align='center'","<span class='bt_afirmar com_salva' data-id='{$_fetch->id}' id='com_salva_{$_fetch->id}'>Salva Comentario</sapan>")
							)
						
				);
			}
			$edicao .= "</span>";
			$edicao .= "</p>";
		}
		
		$edicao .= "<script>
			$(document).ready(function(){
				$('.pc_on_off').bind('click',function(){
					if($(this).children('.c_inicador').hasClass('on')){
						$(this).children('.c_inicador').switchClass('on','off',300);
						$('#postcomment_ativo_'+$(this).data('id')).val('N');
					}else{
						$(this).children('.c_inicador').switchClass('off','on',300);				
						$('#postcomment_ativo_'+$(this).data('id')).val('S');
					}
				});
			
				$('.com_salva').bind('click',function(){
					$('#action').val('salvaComments');
					$('#postcomment_id').val($(this).data('id'));
					$(this).html('Enviando...');
					$('#formPrincipal').submit();
				});
				
				/*
				$('#formPrincipal').ajaxForm({
					dataType:  'json',
					beforeSubmit : function(){							
					},
					success : function(out){
						if(out[0]==1){
							if(out[2]){
								$('.msg_salvo').addClass('sucesso');
								$('.msg_salvo').html(out[1]);
								$('.msg_salvo').show();
							}else{
								//alert('#com_salva_'+out[3]);
								$('#com_salva_'+out[3]).html(out[1]);
								setTimeout(function(){
									$('#com_salva_'+out[3]).html('Salva Comentario');
								},600);
							}
						}else{
							if(out[2]){
								$('.msg_salvo').addClass('erro');
								$('.msg_salvo').html(out[1]);
								$('.msg_salvo').show();
							}else{
								//alert('#com_salva_'+out[3]);
								$('#com_salva_'+out[3]).html(out[1]);
								setTimeout(function(){
									$('#com_salva_'+out[3]).html('Salva Comentario');
								},600);
							}
						}
					}
				}); 
				*/
			
			});
			
			function salvarPost(){
				$('.msg_salvo').html('Aguarde...'); 
				$('.msg_salvo').show();
				$('#action').val('salvar');
				$('#formPrincipal').submit();
			}
			
		</script>";
		$edicao .= "</div>";
		
		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}
	public function envioposts(){
		$this->filtraLogado();
		$this->filtraPermissao('envioposts');
		
		if(substr(request('action'),0,6)=='salvar'){
			$this->enviarEmailPosts();
			die();
		}
		
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Envio de Posts(e-mails)');
		$edicao = '';
		
		$edicao .= tag("table style='width:100%;'",
			tag("tr",
				tag("td colspan='2' align='right'",
					"<span class='bt_afirmar btn' id='enviar_email'>Enviar E-mail</span>"
				)
			)
			.tag("tr",
				tag("td",tag("h2","Posts não enviados"))
				.tag("td align='right'",tag("p style='color:#8b301f;font-size:13px;'","Selecione os posts a serem enviados para os clientes que desejam receber atualizações."))
			)
		);
		
		$query = query("SELECT * FROM post WHERE st_ativo ='S' AND (st_enviado = 'N' OR st_enviado is NULL) ORDER BY data_cadastro DESC");
		$edicao .= "<table class='grid'>";
		$edicao .= tag("tr",tag("th","Publicação").tag("th","Título").tag("th","Autor").tag("th","Selecionar"));
		while($fetch=fetch($query)){
			$post = new post($fetch->id);
			$edicao .= tag("tr",
				tag("td",
					$post->getDataFormatada()
				)
				.tag("td",
					$post->titulo
				)
				.tag("td",
					$post->autor
				)
				.tag("td",
					"<input type='checkbox' value='{$post->id}' name='posts[]' />"
				)
			);
		}
		$edicao .= "</table>";
		
		$edicao .= "<script>
			$(document).ready(function(){
				$('#enviar_email').bind('click',function(){
					enviar('salvar');
				});
			});
		</script>";
		
		$t->edicao = $edicao;
		
		$this->montaMenu($t);
		$this->show($t);
	}
	public function enviarEmailPosts(){
		$this->filtraLogado();
		$this->filtraPermissao('envioposts');		
		
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Enviar E-mail');
		$edicao = '';
		
		$edicao .= tag("span class='bt_afirmar btn enviar_posts'","Enviar");
		$edicao .= "
			<script>
				$('.enviar_posts').bind('click',function(){
					url = '".PATH_SITE."admin.php/enviandoPosts/';
					param = '';
					window.open(url, param, 'width=700,resizable=no,scrollbars=yes,status=no,titlebar=no,toolbar=no,height=500');
				});
			</script>
		";
		
		if(isset($_REQUEST['posts'])){
			if(sizeof($_REQUEST['posts'])>0){
				
				$tp = new Template("tpl.email-posts.html");
				$tp->config = new config();			
				foreach(request('posts') as $key=>$value){
					$post = new post($value);
					$tp->post = $post;
					$tp->parseBlock('BLOCK_POSTS',true);
				}
				$tp->parseBlock('BLOCK_ULTIMOS_POSTS');
				
				$_SESSION['postsId'.$_SESSION['CADASTRO']->id] = $_REQUEST['posts'];
				$_SESSION['emailPosts'.$_SESSION['CADASTRO']->id] = $tp->getContent();
				
				$edicao .= $tp->getContent();
			}else{
				$edicao .= tag("p","Nenhum post selecionado!");
			}
		}else{
				$edicao .= tag("p","Nenhum post selecionado!");
			}
		
		$t->edicao = $edicao;
		
		$this->montaMenu($t);
		$this->show($t);
	}
	public function enviandoPosts(){
	
		$posts_id = join(',',$_SESSION['postsId'.$_SESSION['CADASTRO']->id]);
		
		$edicao = '';
		$edicao .= '<script type="text/javascript" src="'.PATH_SITE.'js/jquery-1.11.1.min.js"></script>';
		
		$edicao .= "<h2 style='font-family:Tahoma;'>Enviando Posts</h2>";
		$edicao .= "<p style='border:1px solid #000;padding:10px;background:#000;color:#FFF;font-family:Tahoma;'>Não feche essa tela, até que os e-mails sejam enviados. Caso esta tela seja fechada os envios serão cancelados.</p>";

		$edicao .= tag("style","
			table{
				width:100%;
				border-collapse:collapse;
				padding:0;
				margin:0;
			}
			tr{
				background:#dddcdc;
			}
			tr:nth-child(2n){
				background:#edeef3;
			}
			td{
				border:1px solid #999;
				padding:10px;
				margin:0;
				font-family:Tahoma;
				font-size:13px;
			}
		");
		
		$edicao .= tag("table",
			tag("tr",
				tag("td",tag("span style='color:$444;font-weight:bold;'","Total de e-mails a enviar"))
				.tag("td",tag("span id='total_email'","0"))
				
				.tag("td",tag("span style='color:$444;font-weight:bold;'","Total de e-mails enviados"))
				.tag("td",tag("span id='total_emails_enviados'","0"))
			)
			.tag("tr",
				tag("td",tag("span style='color:$444;font-weight:bold;'","Porcentagem de envio"))
				.tag("td",tag("span id='porcetagem_envio'","0")."%")

				.tag("td",tag("span style='color:$444;font-weight:bold;'","Faltam enviar"))
				.tag("td",tag("span id='envio_falta'","0"))
			)
			.tag("tr",
				tag("td colspan='2'",tag("span style='color:$444;font-weight:bold;'","Timer para próximo envio"))
				.tag("td colspan='2'",tag("span id='timer_proximo'","<img src='".PATH_SITE."img/loading2.gif' width='30px' />"))
			)
		);
		
		
		$edicao .= "<div id='envio_msg' style='font-family:Tahoma;font-size:15px; color:#333; text-align:center;padding:10px;border:1px solid #999;background:#a7ef9d;display:none; margin-top:20px;'>Enviado cococ</div>";
		
		$filepath = "emails_posts/emailPosts".$_SESSION['CADASTRO']->id.".html";
		$file = fopen($filepath,"w+");
		fwrite($file,$_SESSION['emailPosts'.$_SESSION['CADASTRO']->id]);
		fclose($file);
		
		$query = query("SELECT * FROM cadastro WHERE st_ativo='S' AND st_recebe_post='S' AND(tipocadastro_id=2 OR tipocadastro_id=7)");
		
		$edicao .= "<script>";
		$edicao .= "\n var arr_cadastro = new Array();";
		$cont = 0;
		while(($fetch=fetch($query))){
			// if($cont>2){
				// break;
			// }
			$edicao .= "\n val = new Array(); \n val['nome']='{$fetch->nome}';val['email']='{$fetch->email}';";
			$edicao .= "\n arr_cadastro[{$cont}] = val;";
			$cont++;
		}
		
		//$edicao .= "\n console.log(arr_cadastro);";
		
		//$edicao .= "\n var time = 7200;";
		$edicao .= "\n var pausaEnvioDefault = 120; /*Math.floor(time/60);*/";
		$edicao .= "\n var pausaEnvio = pausaEnvioDefault;";
		$edicao .= "\n var _cont = 0;";	
		
		$edicao .= "\n 
			$('#total_email').html(arr_cadastro.length);
			$('#envio_falta').html(arr_cadastro.length);
			\n 
			function timer(){
				if( pausaEnvio == 0 ){
					pausaEnvio = pausaEnvioDefault;
					enviar();
				}
				else {
					pausaEnvio--;
					$('#timer_proximo').html(pausaEnvio + ' segundos');
					setTimeout('timer()', 1000 );
				}
			}
			
			function enviar(){
				$.ajax({
					url : '".PATH_SITE."admin.php/enviarPost/',
					data: {clienteNome:arr_cadastro[_cont]['nome'],clienteEmail:arr_cadastro[_cont]['email'], arquivo:'{$filepath}'},
					success : function(out){
						console.log(out);
						//arr_cadastro.splice(_cont,1);
						
						$('#envio_falta').html(arr_cadastro.length-_cont);
						$('#total_emails_enviados').html(_cont);
						$('#porcetagem_envio').html( Math.round((_cont/arr_cadastro.length)*100) );
						if(arr_cadastro.length!=_cont){
							timer();
						}else{
							$('#envio_msg').html('Todos os e-mails foram enviados!!');
							$('#envio_msg').show();
							excluirEmail();
							alert('Todos os e-mails foram enviados!!');
						}
					}
				});
				
				_cont++;
			}
			
			function excluirEmail(){
				$.ajax({
					url : '".PATH_SITE."admin.php/excluirPostEmail/',
					data: {arquivo:'{$filepath}',posts : '{$posts_id}'},
					success : function(out){}
				});
			}
		
			enviar();
		";
		
		$edicao .= "\n </script>";
		
		echo $edicao;
		die();
	}
	public function enviarPost(){
		$nome = $_REQUEST['clienteNome'];
		$_email = $_REQUEST['clienteEmail'];
		$file = $_REQUEST['arquivo'];
		
		$email = new email();
		$email->AddTo(trim($_email),$nome);
		$email->addHtml(file_get_contents($file));
		$email->send("Posts - ".config::get('EMPRESA')."");
		
		die();
	}
	public function excluirPostEmail(){
		query("UPDATE post SET st_enviado='S' WHERE id IN(".$_REQUEST['posts'].")");	
		unlink($_REQUEST['arquivo']);
		die();
	}
	
	public function catalogoOnline(){
		$this->filtraLogado();
		$this->filtraPermissao('conteudo');
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');		
		$t->h1 = tag('h1','Catálogo Online');
		
		if(request('action')== 'excluir'){
			$catalogo = new catalogo(intval(request('id')));
			$catalogo->exclui();
			$_SESSION['sucesso'] = tag('p','Catálogo excluído com sucesso!');
		}
		 //
		$pathfile_temp = "img/catalogo/tmp_".str_replace("/","",date("M/Y"));
		if(is_dir($pathfile_temp)){
			$diretorio = dir($pathfile_temp);
			while($arquivo = $diretorio->read()){
				if(($arquivo != '.') && ($arquivo != '..')){
					unlink($pathfile_temp.'/'.$arquivo);
				}
			}
			$diretorio->close();
			@rmdir($pathfile_temp);
		}
		//
		
		$edicao = '';	
		$edicao .= inputHidden('id',0);
		
		$edicao .= tag('table class="box_ferramentas"',
						tag('tr',
							//tag('td style="text-align:right;"',"<input type='submit' value='Salvar' class='bt_afirmar' />")
							tag('td style="text-align:left;"',"<a href='".PATH_SITE."admin.php/conteudo/'><span class='bt_negar'>Voltar</span></a>")
						)
					);
		
		$edicao .= tag('a href="'.PATH_SITE.'admin.php/catalogoOnlineEditar"',tag('span class="bt_afirmar"','incluir novo Catálogo'));
		
		$query = query('SELECT * FROM catalogo');	
		$edicao .= "<br />";
		$edicao .= "<table class='grade'>";
		$edicao .= tag('tr',tag('th','Titulo').tag('th','Edição').tag('th','Publicado').tag('th','Data de Cadastro').tag('th','Excluir'));
		while($fetch=fetch($query)){
			$catalogo = new  catalogo($fetch->id);
			$edicao .= tag('tr',
				 tag('td onclick="javascript:window.location=\''.PATH_SITE.'admin.php/catalogoOnlineEditar/'.$catalogo->id.'\'"  ',$catalogo->titulo)
				.tag('td onclick="javascript:window.location=\''.PATH_SITE.'admin.php/catalogoOnlineEditar/'.$catalogo->id.'\'" ',$catalogo->edicao)
				.tag('td onclick="javascript:window.location=\''.PATH_SITE.'admin.php/catalogoOnlineEditar/'.$catalogo->id.'\'" ',getStAtivoFormatado($catalogo->st_ativo))
				.tag('td onclick="javascript:window.location=\''.PATH_SITE.'admin.php/catalogoOnlineEditar/'.$catalogo->id.'\'" ',$catalogo->getDataCadastroFormatada())
				.tag('td','<span class="ca_excluir" data-id="'.$catalogo->id.'">X</span>')
			);
		}
		$edicao .= "</table>";
		$edicao .= tag("script",
			"$('.ca_excluir').bind('click',function(){
				id = $(this).data('id');
				if(confirm('Tem certeza que deseja excluir este Catálogo ?')) {
					document.forms[0].elements.id.value = id;
					enviar('excluir');
				}
			});			
			"
		);
		
		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}
		
	
	public function catalogoOnlineEditar($id=0){
		$this->filtraLogado();
		$this->filtraPermissao('conteudo');
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
	
		$id = request('id')?request('id'):$id;
		$catalogo = new catalogo($id);
		
		if(request('action')=='salvar'){
				
			$msg = '';
			$sucesso = false;
			
			$catalogo->set_by_array(request('catalogo'));
			if( $catalogo->validaDados($msg) ){
				if(!$catalogo->id){
					$catalogo->pathfile = "img/catalogo/".str_replace(" ","_",strtolower(stringAsTag($catalogo->titulo)))."_".str_replace("/","",$catalogo->edicao);
				}
				$catalogo->salva();	
				if(!is_dir($catalogo->pathfile)){
					mkdir($catalogo->pathfile, 0777); // rename
				}
					
				$pathfile_temp = "img/catalogo/tmp_".str_replace("/","",$catalogo->edicao);
				
				if( is_dir($catalogo->pathfile) && is_dir($pathfile_temp) ){
				
					$diretorio = dir($pathfile_temp);
					while($arquivo = $diretorio->read()){
						if(($arquivo != '.') && ($arquivo != '..')){
							$catalogoimagem = new catalogoimagem();
							$catalogoimagem->catalogo_id = $catalogo->id;
							$catalogoimagem->imagem = $arquivo;
							$arr = explode("_",$arquivo);
							$ordem = explode('.',$arr[sizeof($arr)-1]);
							$catalogoimagem->ordem = is_numeric($ordem[0]) ? $ordem[0] : 0;							
							$catalogoimagem->salva();
							copy($pathfile_temp.'/'.$arquivo, $catalogo->pathfile.'/'.$arquivo);
						}
					}
				}	
				
				if(is_dir($pathfile_temp)){
					$diretorio = dir($pathfile_temp);
					while($arquivo = $diretorio->read()){
						if(($arquivo != '.') && ($arquivo != '..')){
							unlink($pathfile_temp.'/'.$arquivo);
						}
					}
					$diretorio->close();
					@rmdir($pathfile_temp);
				}
				
				$sucesso = true;
				
			}
			
			if(array_key_exists("catalogoimagem",$_REQUEST)){
				foreach($_REQUEST['catalogoimagem'] as $key=>$value){
					$catalogoimagem = new catalogoimagem($key);
					$catalogoimagem->set_by_array($value);
					$catalogoimagem->salva();
				}
			}
			
			if(array_key_exists("excluir",$_REQUEST)){
				foreach($_REQUEST['excluir'] as $key=>$value){
					$catalogoimagem = new catalogoimagem($key);
					@unlink($catalogo->pathfile."/".$catalogoimagem->imagem);
					$catalogoimagem->exclui();
				}
			}
			
			if($msg!=''){
				$_SESSION['erro'] = $msg;
			}
			if($sucesso){
				$_SESSION['sucesso'] = tag("p","Dados salvas com suceso.");
			}	
		}
		
		$max_file_uploads = intval(ini_get("max_file_uploads"))-1;
		
		$edicao  = '';		

		$edicao .= tag('table class="box_ferramentas"',
						tag('tr',
							tag('td style="text-align:left;"',
								"<input type='button' value='Salvar' class='bt_afirmar' onclick='javascript: $(\"#action\").val(\"salvar\"); this.form.submit();' />"
								."<a href='".PATH_SITE."admin.php/catalogoOnline'><span class='bt_negar'>Voltar</span></a>"
							)
						)
					);		
		
		$catalogo->edicao = $catalogo->edicao!=''?$catalogo->edicao:date("M/Y");
		$edicao .= "<table><tr><td valign='top'>";
		$edicao .= inputHidden("id",$catalogo->id);
		$edicao .= inputSimples("catalogo[titulo]", $catalogo->titulo,"Titulo",200,200);
		$edicao .= select("catalogo[st_ativo]",$catalogo->st_ativo,"Publicado",array("N"=>"NAO",'S'=>"SIM"));		
		$edicao .= "</td><td style='padding-left:10px;' valign='top'>";		
		$edicao .= inputReadOnly("catalogo[edicao]",$catalogo->edicao,"Edicao",100,100);
		$edicao .= "<br /><label>PDF para download ( tamanho max do arquivo permitido pelo servidor : ".ini_get('upload_max_filesize')." )</label><input type='file' name='file_pdf' />";
		
		if($catalogo->arquivo!=''){
			$edicao .= "<br /><a style='color:blue;font-size:13px;' href='".PATH_SITE."img/catalogo/download/{$catalogo->arquivo}' target='_blank'> Ver Arquvio</a>";
		}
		
		$edicao .= "</td></tr></table>";
		
		
		
		$edicao .= "<br />";
		//$edicao .= "<label>Selecionar Imagens <small>(Tamanho recomendado para as imagens 461x600px)</small></label><br /><input id='upload' name='upload[]' type ='file' multiple onchange='verificaSize(this);'  /><br />";
		
		$edicao .= "<label>Selecionar Imagens <small>( - Tamanho recomendado para as imagens 461x600px; - Extensões aceitas : JPG, PNG ou GIF)</small></label>";
		$partAddImage  = new Template("tpl.part-add-catalogoimagem-ajax.html");
		$partAddImage->path = PATH_SITE;
		
		$pathfile = "img/catalogo/tmp_".str_replace("/","",date("M/Y"));
		if(is_dir($pathfile)){
			$d = dir($pathfile);
			$cont = false;
			while (false !== ($entry = $d->read())) {
				if($entry!='.' && $entry!='..'){
					$partAddImage->imagem = '<img class="co_thumb" src="'.PATH_SITE.$pathfile.'/'.$entry.'" />';
					$partAddImage->parseBlock("BLOCK_IMAGENS_TMP",true);
					$cont = true;
				}
			}
			
			if($cont){
				$partAddImage->parseBlock("BLOCK_IMAGENS_TEMPORARIAS");
			}
		}		
		
		$edicao .= $partAddImage->getContent();
		
		
		// $edicao .= "<script>
			// function verificaSize(obj){
				// if($(obj)[0].files.length > ".$max_file_uploads."){
					// alert('O envio excede o limite de ".$max_file_uploads." arquivos  por upload definido pelo servidor.');
				// }
			// }
		// </script>";
		
		$query = query("SELECT * FROM catalogoimagem WHERE catalogo_id = {$catalogo->id} ORDER BY ordem, imagem, id");
		if(rows($query)>0){
			$edicao .= "<br />";
			$edicao .= "<h2>Paginas</h2>";
			$edicao .= tag("p","Arraste as paginas para mudar a ordem e clique no botão de Salvar.");
			$edicao .= "<div class='box_pages_catalogo' id='sortable'>";
			$cont =1;
			while($fetch=fetch($query)){
				$edicao .= tag("div class='img_catalogo' id='{$fetch->ordem}' data-ordem='{$fetch->ordem}' data-id='{$fetch->id}'",
								"<input type='hidden' name='catalogoimagem[{$fetch->id}][ordem]' id='h_catalogoimagem_{$fetch->id}' value='{$fetch->ordem}' />"
								.tag("table style='width:100%;'",
									tag('tr',
										tag('td',
											tag("img src='".PATH_SITE."{$catalogo->pathfile}/{$fetch->imagem}' ")
											.tag("label class='pag_excluir'","Excluir ".checkbox("excluir[{$fetch->id}]",$fetch->id,""))
										)
										// .tag('td align="right"',
											// inputSimples("catalogoimagem[{$fetch->id}][ordem]",$fetch->ordem,"Ordem",10,10,"style='width:60px !important;'")
											// .checkbox("excluir[{$fetch->id}]",$fetch->id,"Excluir")
										// )
									)
								)								
							);
			 $cont++;
			}
			$edicao .= "</div><br />";
			
			$edicao .=  tag("script", '
						var sort = 1;
						$(function() {
							$( "#sortable" ).sortable({
								stop: function( event, ui ) {									
									if(sort==1){
										sort = 0;
										//console.log(ui.item);
										velha_ordem = $(ui.item[0]).data("ordem");
										nova_ordem = $(ui.item.context.previousElementSibling).data("ordem");									
									
										velho = $("#"+velha_ordem);										
										
										// console.log(velha_ordem);
										// console.log(nova_ordem);
										// console.log( velho[0] );
										// console.log("-----------");
										
										if(velha_ordem > nova_ordem){
											nova_ordem = nova_ordem+1;
											
											// for(i = velha_ordem-1;  i >= nova_ordem; i--){	
												// console.log( document.getElementById(i) );
											// }
										
											//setTimeout( function(){
												for(i = velha_ordem-1, order=velha_ordem;  i >= nova_ordem; i--, order--){
													$(document.getElementById(i)).attr("data-ordem",order);
													$("#h_catalogoimagem_"+$(document.getElementById(i)).data("id")).val(order);
													$(document.getElementById(i)).attr("id",order);	

													console.log( document.getElementById(order) );
												}
											//},1000);
										
										}else{		
											// for(i = velha_ordem+1;  i <= nova_ordem; i++){												
												// console.log( document.getElementById(i) );
											// }
										
											// setTimeout( function(){
												
												// console.log("change");
												
												for(i = velha_ordem+1, order =velha_ordem;  i <= nova_ordem; i++, order++){													
													
													
													$(document.getElementById(i)).attr("data-ordem",order);
													$("#h_catalogoimagem_"+$(document.getElementById(i)).data("id")).val(order);
													$(document.getElementById(i)).attr("id",order);			

													console.log( document.getElementById(order) );
												}
											//},1000);
										}
										
										//setTimeout( function(){
											velho.attr("data-ordem",nova_ordem);
											$("#h_catalogoimagem_"+velho.data("id")).val(nova_ordem);
											velho.attr("id",nova_ordem);
											
											//console.log( "Velho" );
											//console.log( velho[0] );
										//}, 1100);
										
										sort = 1;
									}
								}
							});
							$( "#sortable" ).disableSelection();
						});
			  ');
		}
		
		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}
	
	public function salvarImagemCatalogo(){
		if(request('nova_imagem')){
		
			$pathfile = "img/catalogo/tmp_".str_replace("/","",date("M/Y"));
			
			if(!is_dir($pathfile)){
				echo "Criar pasta tmp.";
				mkdir($pathfile, 0777); // rename
			}
			
			
			$data = explode(",",$_POST['slice']);			
			$filename = str_replace(" ","-",stringAsTag_v2($_POST['filename']));			
			file_put_contents($pathfile."/".$filename, decode($data[1]) , FILE_APPEND);
			
			printr($_POST['filename']);
			printr($data);
			printr($filename);
			
			echo "/n Dados Salvos!";
			
			die();
			
			if( file_tratamento("file_nova", $msg, $file) ){
				
				$catalogo->set_by_array(request('catalogo'));
				
				if($catalogo->id){
					$catalogo->pathfile = "img/catalogo/".str_replace(" ","_",strtolower(stringAsTag($catalogo->titulo)))."_".str_replace("/","",$catalogo->edicao);
				}else{
					$catalogo->pathfile = "img/catalogo/tmp_".str_replace("/","",$catalogo->edicao);
				}
				
				if(!is_dir($catalogo->pathfile)){
					mkdir($catalogo->pathfile, 0777); // rename
				}
				
				$filename = str_replace(" ","-",stringAsTag_v2($file['name']));
				//move_uploaded_file( $file['tmp_name'] , $catalogo->pathfile."/".$filename );
				//echo json_encode($catalogo);
			}
			die();
		}
	}
	
	public function tmpImagemCatalogo(){
		if(request('excluir')){
			$pathfile = "img/catalogo/tmp_".str_replace("/","",date("M/Y"))."/";
			if(is_dir($pathfile)){
				$diretorio = dir($pathfile);
				while($arquivo = $diretorio->read()){
					if(($arquivo != '.') && ($arquivo != '..')){
						unlink($pathfile.$arquivo);
					}
				}
				$diretorio->close();
				rmdir($pathfile) or die("erro ao excluir diretório");
				echo 1;
			}
		}
		die();
	}
	/***********************************/
	
	public function institucional(){
	
		$this->filtraLogado();
		$this->filtraPermissao('Institucional');

		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('<br />Institucional');
		
		
		$institucional = new institucional(array('nome'=>'institucional'));
		
		if(substr(request('action'),0,6)=='salvar'){
			$imagem1_nome = '';
			$imagem2_nome = '';	
			$destino      = "img/banner/";
			
			if(array_key_exists('imagem_1',$_FILES)){
				$imagem1_nome = "institucional_".$_FILES['imagem_1']['name'];
				move_uploaded_file($_FILES['imagem_1']['tmp_name'], $destino.$imagem1_nome);
			}
			if(array_key_exists('imagem_2',$_FILES)){
				$imagem2_nome = "institucional_".$_FILES['imagem_2']['name'];
				move_uploaded_file($_FILES['imagem_2']['tmp_name'], $destino.$imagem2_nome);
				
			}
			
			$institucional->set_by_array($_REQUEST['institucional']);
			$institucional->imagem_1 = $imagem1_nome;
			$institucional->imagem_2 = $imagem2_nome;
			
			$institucional->salva();
			clearstatcache();
			$_SESSION['sucesso'] = tag('p','Dados salvos com sucesso.');
		}
		
				
		$edicao = '';
		$edicao .= "<input type='hidden' name='institucional[nome]' value='institucional' />";
		$edicao .= "<input type='hidden' name='institucional[id]' value='{$institucional->id}' />";
		$edicao .= "Imagem 1<br />";
		$edicao .= '<span style="font-size:10px">(tamanho recomendado 246x150px.)</span><br />';
		$edicao .= "<input type='file' name='imagem_1' />";
		$edicao .= "<br /><img src='".PATH_SITE."img/banner/{$institucional->imagem_1}' />";
		$edicao .= "<br />";
		$edicao .= "<br />";
		$edicao .= "Imagem 2<br />";
		$edicao .= '<span style="font-size:10px">(tamanho recomendado 246x150px.)</span><br />';
		$edicao .= "<input type='file' name='imagem_2' />";
		$edicao .= "<br /><img src='".PATH_SITE."img/banner/{$institucional->imagem_2}'  />";
		
		$edicao .= "<br /><br />Texto<br />";
		$edicao .= "<textarea name='institucional[texto]' style='width:800px; height:300px;'>{$institucional->texto}</textarea>";
		$edicao .= "<br />";
		
		
		$t->parseBlock('BLOCK_TOOLBAR');
		$t->edicao = $edicao;
		
		$this->montaMenu($t);
		$this->show($t);
	}

	public function clienteempresa(){
		$this->filtraLogado();
		$this->filtraPermissao('Institucional');
		
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('<br />Pagina Clientes');		
		
		if(request('action')=='editar'
			||substr(request('action'),0,6)=='salvar'){
			$this->clienteempresaEditar($t);
			return;
		}		
		
		if(request('action')=='excluir'){
			$cliente = new cadastro(intval(request('id')));
			$cliente->exclui();
		}

		$grid = new grid();

		$grid->sql =
					'
					SELECT
						id
						,nome Nome
						,empresa Empresa
						,st_ativo Status
					FROM
						cadastro
					WHERE
						1=1
						AND tipocadastro_id = 5
					' ;
					
		
		$filtro = new filtro();

		$filtro->add_input('Nome','Nome:');

		$grid->filtro = $filtro ;

		$t->grid = $grid->render();

		$this->montaMenu($t);
		$this->show($t);			
		
	}
	
	public function clienteempresaEditar($t){	
		$this->filtraLogado();
		$this->filtraPermissao('clienteempresa');
		
		$cliente = new cadastro(intval(request('id')));

		if(substr(request('action'),0,6)=='salvar'){

			$next = substr(request('action'),7,strlen(request('action')));

			$cliente->set_by_array($_REQUEST['cadastro']);
						
			if($_FILES['file_1']['size']>0){
				$imagem      = $_FILES['file_1'];
				$imagem_nome = "cliente_".str_replace("/","_",$cliente->empresa).".jpg";
				$caminho     = "img/cliente/cliente_".str_replace("/","_",$cliente->empresa).".jpg";
				
				
				list($width,$height) = getimagesize($imagem['tmp_name']);
				
				if($width!=205 && $height!=205){
					$_SESSION['erro'] = tag('p','A imagem precisa estar com 205x205px de tamanho.');
				}elseif($imagem['type']!='image/jpeg'){
					$_SESSION['erro'] = tag('p','A imagem precisa ser .jpg .');
				}else{				
					move_uploaded_file($imagem['tmp_name'], $caminho);				
					$cliente->imagem = $imagem_nome;				
					$cliente->salva();
					$cliente = new cadastro($cliente->id);
					$_SESSION['sucesso'] = 'Dados salvos com sucesso.';	
				}
				
			}else{
				$cliente->salva();
				$cliente = new cadastro($cliente->id);
				$_SESSION['sucesso'] = 'Dados salvos com sucesso.';
			}
			if(trim(@$next)!=''){
				$this->afterSave($next,'clienteempresa');
				return;
			}
		}

		$t->parseBlock('BLOCK_TOOLBAR');

		$t->h1 = h1('<br />Clientes');

		$edicao = '';
		
		$edicao .= select('cadastro[st_ativo]',$cliente->st_ativo,'Ativo:',array('S'=>'Sim','N'=>'Nao'));
		
		$edicao .= "<input type='hidden' value='{$cliente->id}' name='cadastro[id]' />";
		
		$edicao .= inputSimples('cadastro[nome]',$cliente->nome,'Nome:',50,200);
		
		$edicao .= inputSimples('cadastro[empresa]',$cliente->empresa,'Empresa:',50,200);

		$edicao .= "<br /><br />";
		$edicao .= "Imagem do Cliente:<br />";
		$edicao .= "(A imagem dever ter 205x205px de tamanho, e ser .jpg .)<br />";
		$edicao .= "<input type='file' name='file_1' />";
		$edicao .= "<br /><img src='".PATH_SITE."img/cliente/{$cliente->imagem}' />";
		
		
		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
		
	}
	
	public function noticias(){

		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Not&iacute;cias');

		if(request('action')=='editar'
			||substr(request('action'),0,6)=='salvar'){
			$this->noticiasEditar($t);
			return;
		}

		if(request('action')=='excluir'){
			$noticia = new noticia(intval(request('id')));
			$noticia->exclui();
		}

		$grid = new grid();

		$grid->sql =
					'
					SELECT
						id
						,titulo
						,data_publicacao Data_Publicacao
						,st_ativo Status
					FROM
						noticia
					WHERE
						1=1
					' ;

		$filtro = new filtro();

		$filtro->add_input('Nome','Nome:');

		$grid->filtro = $filtro ;

		$t->grid = $grid->render();

		$this->montaMenu($t);
		$this->show($t);
	}

	public function noticiasEditar($t){

		$this->filtraLogado();
		$this->filtraPermissao('noticias');

		$noticia = new noticia(intval(request('id')));

		if(substr(request('action'),0,6)=='salvar'){

			$next = substr(request('action'),7,strlen(request('action')));

			$noticia->set_by_array($_REQUEST['noticia']);
			$noticia->salva();

			$noticia = new noticia($noticia->id);

			$_SESSION['sucesso'] = 'Dados salvos com sucesso';

			if(trim(@$next)!=''){
				$this->afterSave($next,'noticias');
				return;
			}
		}

		$t->parseBlock('BLOCK_TOOLBAR');

		$t->h1 = h1('Noticia');

		$edicao = '';
		$edicao .= inputHidden('noticia[id]', $noticia->id);

		$edicao .= tag('h2', 'Dados básicos');

		$edicao .= select('noticia[st_ativo]', $noticia->st_ativo, 'Ativo?:', array('S'=>'Sim','N'=>'Nao'));

		$edicao .= inputSimples('noticia[titulo]', $noticia->titulo, 'Titulo:', 45, 50);
		if($this->config->HABILITA_ESPANHOL=='S'){
			$edicao .= inputSimples('noticia[titulo_es]', $noticia->titulo_es, 'Titulo:', 45, 50);
		}
		if($this->config->HABILITA_INGLES=='S'){
			$edicao .= inputSimples('noticia[titulo_in]', $noticia->titulo_in, 'Titulo:', 45, 50);
		}

		$edicao .= editor('noticia[conteudo]', $noticia->conteudo, 'Conteúdo:');
		if($this->config->HABILITA_ESPANHOL=='S'){
			$edicao .= editor('noticia[conteudo_es]', $noticia->conteudo_es, 'Conteúdo:');
		}
		if($this->config->HABILITA_INGLES=='S'){
			$edicao .= editor('noticia[conteudo_in]', $noticia->conteudo_in, 'Conteúdo:');
		}

		$edicao .= inputData('noticia[data_publicacao]', $noticia->data_publicacao, 'Data da publicação');

		$edicao .= str_repeat(tag('br'),5);

		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}

	public function textbox(){
		//$this->filtraLogado();
		//$this->filtraPermissao('paginas');
		$t = new TemplateAdmin('admin/tpl.textbox.html');

		if(request('salvar')){
			$parse = '';
		}

		$t->thtml = request('thtml');

		$this->montaMenu($t);
		$this->show($t);
	}

    public function selectlinks(){
		echo select('personalizacaolinkrodape[personalizacaocategoria_id]','','Categoria',personalizacaocategoria::opcoes(), true);
	}
	
	public function acabamento(){
		$this->filtraLogado();
		$this->filtraPermissao('acabamento');
		
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Acabamentos para Personaliza&ccedil;&atilde;o');
		
		
		if(request('action')=='editar'
			||substr(request('action'),0,6)=='salvar'){
			$this->acabamentoEditar($t);
			return;
		}

		if(request('action')=='excluir'){
			$acabamento = new acabamento(intval(request('id')));
			$acabamento->exclui();
		}
			
	
		$grid = new grid();
		$grid->sql = "SELECT 
						acabamento.id
						,acabamento.nome Nome
						,concat('<img width=\"80px\" height=\"80px\" src=".PATH_SITE."img/acabamento/',acabamento.imagem,' />') Imagem
						,acabamento.st_ativo Status
					FROM
						acabamento";
		
		$filtro = new filtro();
		$grid->filtro = $filtro ;
		$t->grid = $grid->render();
		
		
		$this->montaMenu($t);
		$this->show($t);
	}
	
	public function acabamentoEditar($t){
		$this->filtraLogado();
		$this->filtraPermissao('acabamento');

		$acabamento = new acabamento(intval(request('id')));
		
		if(substr(request('action'),0,6)=='salvar'){
			$erros = array();

			$next = substr(request('action'),7,strlen(request('action')));
			
			$acabamento->set_by_array($_REQUEST['acabamento']);
			if($acabamento->validaDados()){
			
				$acabamento->salva();
				if(array_key_exists('imagem',$_FILES)){
					$acabamento->imagem = $filename = $acabamento->nome."-".$acabamento->id."jpg";
					move_uploaded_file($_FILES['imagem']['tmp_name'],"img/acabamento/{$filename}");
					$acabamento->atualiza();
				}
				
				$_SESSION['sucesso'] = tag('p','Dados salvos com sucesso.');
				
				if(trim(@$next)!=''){
					$this->afterSave($next,'acabamento',$acabamento);
					return;
				}
			
			}
			
			
		}
		
		$t->parseBlock('BLOCK_TOOLBAR');
		$t->h1 = h1('Acabamento '.$acabamento->nome);
		
		$edicao = '';
		
		$edicao .= inputHidden("id",$acabamento->id);
		$edicao .= select('acabamento[st_ativo]',$acabamento->st_ativo,'Ativo',array('S'=>'SIM','N'=>'NAO'));
		$edicao .= inputSimples("acabamento[nome]",$acabamento->nome,'Nome',60,160);
		$edicao .= inputFile('imagem','','Imagem<br /><p>A imagem deve ser .jpg.</p>');
		
		$edicao .= tag('div',
			'<img width="150px" height="150px" src="'.PATH_SITE.'img/acabamento/'.$acabamento->imagem.'" />'
		);
		
		
		$t->edicao = $edicao;
		$this->montaMenu($t);
		$this->show($t);
	}
	
	public function material(){
		$this->filtraLogado();
		$this->filtraPermissao('material');
		
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Materiais para Personaliza&ccedil;&atilde;o');
		
		if(request('action')=='editar'
			||substr(request('action'),0,6)=='salvar'){
			$this->materialEditar($t);
			return;
		}

		if(request('action')=='excluir'){
			$material = new material(intval(request('id')));
			$material->exclui();
		}
			
	
		$grid = new grid();
		$grid->sql = "SELECT id, tipocordao Tipo_de_Cordao, st_ativo Status FROM material";
		
		$filtro = new filtro();
		$grid->filtro = $filtro ;
		$t->grid = $grid->render();
		

		$this->montaMenu($t);
		$this->show($t);
	}
	
	public function materialEditar($t){
		$this->filtraLogado();
		$this->filtraPermissao('produtos');

		$material = new material(intval(request('id')));
		
		if(substr(request('action'),0,6)=='salvar'){
			$erros = array();

			$next = substr(request('action'),7,strlen(request('action')));
			
			$material->largura_checkbox = 'N';	
			$material->acabamento_checkbox = 'N';	
			$material->impressao_checkbox = 'N';	
			$material->corimpressofrente_checkbox ='N';	
			$material->corimpressoverso_checkbox = 'N';	
			$material->quantidade_checkbox = 'N';
			
			
			$material->set_by_array($_REQUEST['material']);
			//printr($material);
			//die();
			if($material->tipocordao!=''){
				$query = query($sql="SELECT * FROM material WHERE tipocordao = '{$material->tipocordao}' AND id <> {$material->id}");
				//printr($sql);
				if(rows($query)>0){
					$_SESSION['erro'] = tag('p','J&aacute; existe um tipo de cord&atilde;o cadastrado com esse nome.');
				}else{
					$material->salva();
					$_SESSION['sucesso'] = tag('p','Dados salvos com sucesso.');
				
					if(trim(@$next)!=''){
						$this->afterSave($next,'material',$material);
						return;
					}
				}
			
			}else{
				$_SESSION['erro'] = tag('p','O tipo de cord&atilde;o n&atilde;o pode estar em branco.');
			}
		}
		
		$t->parseBlock('BLOCK_TOOLBAR');
		$t->h1 = h1('Material '.$material->tipocordao);
		
		$edicao = '';
		
		$edicao .= inputHidden('id',$material->id);
		$edicao .= select('material[st_ativo]',$material->st_ativo,'Ativo',array('S'=>'SIM','N'=>'NAO'));
		$edicao .= inputSimples('material[tipocordao]',$material->tipocordao,'Tipo de Cord&atilde;o',70,160);
		$edicao .= tag('p style="margin-top:10px;margin-bottom:10px;padding:6px;background:#f9e1e1;color:red;font-size:12px;"','Nos campos abaixo insira seus valores separados por ";"(Ponto e Virgura) , caso tenha mais de uma op&ccedil;&atilde;o.<br /> Por exemplo: Largura = 1mm;2mm;3mm;...');
		
		$edicao .= tag('table',tag('tr',tag('td',inputSimples('material[largura]',$material->largura,'Largura',70,160))
					.tag('td valign="bottom"',checkbox('material[largura_checkbox]', 'S','<span style="font-size:9px;">Habilitar campo livre para o cliente.</span>',$material->largura_checkbox=='S'?'checked':''))));
		$edicao .= tag('table',tag('tr',tag('td',inputSimples('material[acabamento]',$material->acabamento,'Acabamento',70,160))
					.tag('td',checkbox('material[acabamento_checkbox]', 'S','<span style="font-size:9px;">Habilitar campo livre para o cliente.</span>',$material->acabamento_checkbox=='S'?'checked':''))));
		
		$edicao .= tag('table',tag('tr',tag('td',inputSimples('material[impressao]',$material->impressao,'Impress&atilde;o',70,160))
					.tag('td',checkbox('material[impressao_checkbox]', 	'S','<span style="font-size:9px;">Habilitar campo livre para o cliente.</span>',$material->impressao_checkbox=='S'?'checked':''))));
		
		$edicao .= tag('div style="border:1px solid #ddd; margin-top:10px; margin-bottom:10px;"','Cores de Impress&atilde;o'
					.tag('table',tag('tr',tag('td',inputSimples('material[corimpressofrente]',$material->corimpressofrente,'Frente',70,160))
						.tag('td',checkbox('material[corimpressofrente_checkbox]', 'S','<span style="font-size:9px;">Habilitar p/ cliente add imagem.</span>',$material->corimpressofrente_checkbox=='S'?'checked':''))))
					
					.tag('table',tag('tr',tag('td',inputSimples('material[corimpressoverso]',$material->corimpressoverso,'Verso',70,160))
						.tag('td',checkbox('material[corimpressoverso_checkbox]','S','<span style="font-size:9px;">Habilitar p/ cliente add imagem.</span>',$material->corimpressoverso_checkbox=='S'?'checked':''))))
				);
		$edicao.= tag('table',tag('tr',tag('td',inputSimples('material[quantidade]',$material->quantidade,'Quantidade',70,160))
					.tag('td',checkbox('material[quantidade_checkbox]', 'S','<span style="font-size:9px;">Habilitar campo livre para o cliente.</span>',$material->quantidade_checkbox=='S'?'checked':''))));
						
		
		$t->edicao = $edicao;
		$this->montaMenu($t);
		$this->show($t);
	}
	
	public function logo(){
		$this->filtraLogado();
		$this->filtraPermissao('produtos');
		
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Logo');
		
		if(request('action')=='editar'
			||substr(request('action'),0,6)=='salvar'){
			$this->logoEditar($t);
			return;
		}

		if(request('action')=='excluir'){
			$logo = new logo(intval(request('id')));
			$logo->exclui();
		}


		$grid = new grid();
		$grid->sql = "SELECT id, nome, CASE st_padrao WHEN 'S' THEN 'LOGO PADRAO' WHEN 'N' THEN 'ESPECIFICO' END as Padrao, st_ativo Status FROM logo";
		
		$filtro = new filtro();
		$grid->filtro = $filtro ;
		$t->grid = $grid->render();
		
		$this->montaMenu($t);
		$this->show($t);

	}
	
	public function logoEditar($t){
		$this->filtraLogado();
		$this->filtraPermissao('logo');

		$logo = new logo(intval(request('id')));	
		
		if(substr(request('action'),0,6)=='salvar'){
			$next = substr(request('action'),7,strlen(request('action')));
			$logo->set_by_array(request('logo'));
			if($logo->salva()){
				$_SESSION['sucesso'] = tag('p','Dados salvos com sucesso.');
			
				if(trim(@$next)!=''){
					$this->afterSave($next,'logo',$logo);
					return;
				}			
			}
		}
		
		$t->parseBlock('BLOCK_TOOLBAR');
		$t->h1 = h1('Logo '.$logo->nome);
		
		$edicao = '';
		
		$edicao .= inputHidden('id',$logo->id);
		if($logo->st_padrao=='S'){
			$edicao .= tag("p style='padding:6px; border:1px solid #ccc; color:#444; font-size:12px;background:#eee;'","Logo Padrão - <span style='color:#28628f;'>escolha outro logo e o defina com padrão para que esse não seja mais o padrão.</span>");
		}else{
			$edicao .= select('logo[st_ativo]',$logo->st_ativo,'Ativo',array('S'=>'SIM','N'=>'NAO'));
			$edicao .= select('logo[st_padrao]',$logo->st_padrao,'Padrão <span style="color:red;">( O logo padrão irá aparecer nas páginas que não tem logo definido. )</span>',array('N'=>'NAO','S'=>'SIM'));
		}
		$edicao .= inputSimples('logo[nome]',$logo->nome,'Nome',80,100);
		$edicao .= "<br /><br /><label>Imagem* <p style='color:#aa5335;font-size:12px;'>A imagem deve ter 221x137px</p><br /><input type='file' class='jfilestyle' name='novo_logo_img' /></label>";
		if($logo->imagem!=''){
			$edicao .= "<br /><img src='".PATH_SITE."img/logos/{$logo->imagem}'  />";
		}
		
		$edicao .= "<script>
				$(':file').jfilestyle({
					input: true
				});
		</script>";
			
		$t->edicao = $edicao;
		$this->montaMenu($t);
		$this->show($t);
	}
	
	public function paginalogo($pagina,$pid=0){	
			
		if(request('action_logo')=='addLogo'){
			$logo_id = request('logo_id');
			$logo = new logo(intval($logo_id));
			$logopagina = new logopagina(intval(request('logopagina_id')));
			$logopagina->logo_id           = $logo_id;
			$logopagina->pagina            = $pagina;
			$logopagina->personalizacao_id = $pid>0?$pid:NULL;			
			if($logopagina->salva()){
			
				$obj = new stdClass();
				$obj->msg = tag('p','Logo adicionado com sucesso!');
				$obj->imagem = PATH_SITE.'img/logos/'.$logo->imagem;
				$obj->logo_pagina_id = $logopagina->id;
				
				echo json_encode($obj);
			
			}
			
			
			die();
		}		
		
		$logo_pagina = logo::carregalogo($pagina,$pid);
		
		$edicao = '';
		$edicao .= inputHidden('logo_id',0);
		$edicao .= inputHidden('action_logo','addLogo');
		$edicao .= inputHidden('logopagina_id',$logo_pagina->logopagina_id);
		
		$edicao .= tag('span class="bt_afirmar" onclick="javascript: $(\'#block_logo\').slideToggle();"','Definir logo');
		
		$query = query("SELECT * FROM logo WHERE st_ativo = 'S'");
	
		$edicao .= '<div style="border:1px solid #999;display:none;" id="block_logo">';
		$edicao .= '<table style="padding:4px;"><tr>';
		$edicao .= '<td style="border:0px solid #ddd;width:50%;">';
		$edicao .= '<p style="border:1px solid #ddd;color:#28628f;background:#eee;padding:4px 6px;">Escolher Logo <span style="color:green;" id="logo_msg"></span></p>';
		while($fetch=fetch($query)){
			$edicao .= tag('span class="lt_box_logos" data-id="'.$fetch->id.'"','<img src="'.PATH_SITE.'img/logos/'.$fetch->imagem.'" width="100px" />');
		}
		$edicao .= '</td><td style="border-left:1px solid #999;padding:4px;" valign="top">';
		$edicao .= '<p style="border:1px solid #ddd;color:#28628f;background:#eee;padding:4px 6px;">Logo Atual</p>';
		$edicao .= '<span id="caixa_img_logo"><img src="'.PATH_SITE.'img/logos/'.$logo_pagina->imagem.'" align="right" /></span>';
		$edicao .= '</td></tr></table>';
		$edicao .= '</div>';
		$edicao .= "<script>
			$(document).ready(function(){
				$('.lt_box_logos').bind('click',function(){
					id = $(this).data('id');
					document.getElementById('action').value = 'editar';
					document.getElementById('logo_id').value = id;
					addLogo();
				});
			});
			
			function addLogo(){
				$( '#formPrincipal' ).ajaxSubmit({
					dataType : 'json',
					beforeSubmit : function(){
						$('#caixa_img_logo').html('<img src=\"".PATH_SITE."img/ajax.gif\" alt=\"Enviando...\" />');
					},
					success : function(data){
						$('#logo_msg').html(data.msg);
						$('#logopagina_id').val(data.logo_pagina_id);
						img  = data.imagem+'?'+(new Date()).getTime();
						_img = '<img src=\"'+img+'\" id=\"imagem_logo\" align=\"right\" />';
						$('#caixa_img_logo').html(_img);
						
						setTimeout(function(){
							$('#logo_msg').html('');
						},4000);
					}
				});
			}
		</script>";
		
		$edicao .= "<br /><br /><br />";
		return $edicao;
	}

	public function modulos(){

		$this->filtraLogado();
		// $this->filtraPermissao('modulos');

		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = butil::tag('h1','Módulos');

		if(request('action')=='modulosUp'){

			$modulo = new modulo(intval(request('id')));

			$results = results("SELECT * FROM modulo WHERE ordem < {$modulo->ordem} AND modulo_id = {$modulo->modulo_id} ORDER BY ordem DESC");
			$anterior = new modulo($results[0]->id);

			$modulo->ordem = $modulo->ordem-1;
			$anterior->ordem = $anterior->ordem+1;

			$modulo->salva();
			$anterior->salva();

			$_SESSION['sucesso'] = butil::tag('p', 'Ordem alterada com sucesso para o item '.$modulo->nome);

		}

		if(request('action')=='modulosDown'){

			$modulo = new modulo(intval(request('id')));

			$results = results("SELECT * FROM modulo WHERE ordem > {$modulo->ordem} AND modulo_id = {$modulo->modulo_id} ORDER BY ordem");
			$proximo = new modulo($results[0]->id);

			$proximo->ordem = $proximo->ordem-1;
			$modulo->ordem = $modulo->ordem+1;

			$modulo->salva();
			$proximo->salva();

			$_SESSION['sucesso'] = butil::tag('p', 'Ordem alterada com sucesso para o item '.$modulo->nome);

		}

		if(request('action')=='modulosDel'){

			//printr($_REQUEST);
			//verfica se nao tem produtos relacionados

			$modulo = new modulo(intval(request('id')));
			$modulo->exclui();
			query("UPDATE modulo SET ordem = (ordem-1) WHERE ordem > {$modulo->ordem} AND modulo_id = {$modulo->modulo_id}");
			$_SESSION['sucesso'] = butil::tag('p', 'modulo '.$modulo->nome.' excluido com sucesso');

		}

		if(request('action')=='salvar'){

			foreach(is_array(@$_REQUEST['modulo'])?$_REQUEST['modulo']:array() as $id => $arrmodulo){

				$modulo = new modulo($id);
				$modulo->set_by_array($arrmodulo);

				$erro = array();
				if($modulo->validaDados($erro)){
					$modulo->salva();
				}
				else{
					$_SESSION['erro'] = join('<br />', $erro);
				}
			}

			/*CADASTRA UMA faixa NOVA*/
			if(is_array(@$_REQUEST['moduloNova']) && $_REQUEST['moduloNova']['nome']!=''){

				$modulo = new modulo();
				$modulo->set_by_array($_REQUEST['moduloNova']);

				$erro = array();
				if($modulo->validaDados($erro)){
					$modulo->ordem = query_col("SELECT ifnull(max(ordem)+1,1) ordem FROM modulo WHERE modulo_id = {$modulo->modulo_id}");
					$modulo->salva();
					$_SESSION['sucesso'] = butil::tag('p', 'modulo '.$modulo->nome.' cadastrada com sucesso');
				}
				else{
					$_SESSION['erro'] = join('<br />', $erro);
				}

				unset($modulo);
			}

			//printr($_REQUEST);
		}

		$this->montaMenuSimples($t);

		$edicao = '';

		$edicao = inputHidden('id', '');

		$edicao .= '<div class="box-block">';
		$edicao .= tag('h2', 'Cadastrar novo módulo');
		$edicao .= select('moduloNova[modulo_id]', '', 'Dentro de:', modulo::opcoes_root() );
		$edicao .= inputSimples('moduloNova[nome]', '', 'Nome do modulo',40,30);
        $edicao .= inputSimples('moduloNova[arquivo]', '', 'Arquivo do modulo',40,30);
		$edicao .= tag('br clear="all"');
		$edicao .= '</div>';

		$edicao .= str_repeat(tag('br'), 2);

		$edicao .= '<div class="box-block">';
		$edicao .= tag('h2', 'Módulos já cadastrados');

		$edicao .= '<table class="grid">';
		$edicao .= tag('tr',
				tag('th', 'Nome')
				.tag('th', 'Arquivo')
				.tag('th', 'Ordem')
				.tag('th', 'Dentro de ')
				.tag('th', 'Status')
				.tag('th width=25px', '&nbsp;')
				.tag('th width=25px', '&nbsp;')
				.tag('th width=25px', 'Excluir')
			);

		$edicao .= tag('tr',
					tag('td colspan="7"', tag('b','Geral'))
				);

		$edicao .= $this->listaModulo(0,0);

		$edicao .= tag('br clear="all"');
		$edicao .= '</div>';

		$edicao .= '</table>';

		$t->edicao = $edicao ;

		$this->montaMenu($t);
		$this->show($t);
	}
	
	public function montaMenuSimples($t){
		$t->parseBlock('BLOCK_TOOLBAR_SIMPLES_1');
		$t->parseBlock('BLOCK_TOOLBAR_SIMPLES_2');
	}
	
	private function listaModulo($modulo_id=0,$ident=0){

		$edicao = '';

		static $ident = 0;

		$results = results($sql="SELECT modulo.* FROM modulo WHERE modulo_id = {$modulo_id}	ORDER BY modulo_id, ordem, nome");

		for($i=0,$n=sizeof($results);$i<$n;$i++){

			$fetch = $results[$i];
			//if(intval($fetch->modulo_id)>0){

			if(rows(query("SELECT * FROM modulo WHERE modulo_id = {$fetch->id}"))>0){
				
				/*
				$edicao .= butil::tag('tr',
					butil::tag('td colspan="6"', butil::tag('b',$fetch->nome))
				);
				*/
				
				$edicao .= tag('tr',
					tag('td', inputHidden("modulo[{$fetch->id}][modulo_id]",$fetch->modulo_id).inputSimples("modulo[{$fetch->id}][nome]",$fetch->nome,'',20,30))
					.tag('td', inputSimples("modulo[{$fetch->id}][arquivo]",$fetch->arquivo,'',20,30))
					.tag('td', inputSimples("modulo[{$fetch->id}][ordem]",$i,'',20,30))
					.tag('td', select("modulo[{$fetch->id}][modulo_id]",$fetch->modulo_id,'', modulo::opcoes_root()))
					.tag('td', select("modulo[{$fetch->id}][st_ativo]", $fetch->st_ativo, '', array('S'=>'Sim','N'=>'Nao')))
					.tag('td', $i>0?tag('a class="up" href="javascript:modulosUp('.$fetch->id.')" ','&nbsp;'):'')
					.tag('td', ($i<($n-1))?tag('a class="down" href="javascript:modulosDown('.$fetch->id.')" ','&nbsp;'):'')
					.tag('td', tag('a class="del" href="javascript:modulosDel('.$fetch->id.')" ','&nbsp;'))
				);
				
				$ident ++;
				$edicao .= $this->listaModulo($fetch->id,$ident);
				$ident --;
			}
			else {

				$edicao .= tag('tr',
					tag('td', str_repeat('&nbsp',$ident*10).inputHidden("modulo[{$fetch->id}][modulo_id]",$fetch->modulo_id).inputSimples("modulo[{$fetch->id}][nome]",$fetch->nome,'',20,30))
					.tag('td', inputSimples("modulo[{$fetch->id}][arquivo]",$fetch->arquivo,'',20,30))
					.tag('td', inputSimples("modulo[{$fetch->id}][ordem]",$i,'',20,30))
					.tag('td', select("modulo[{$fetch->id}][modulo_id]",$fetch->modulo_id,'', modulo::opcoes_root()))
					.tag('td', select("modulo[{$fetch->id}][st_ativo]", $fetch->st_ativo, '', array('S'=>'Sim','N'=>'Nao')))
					.tag('td', $i>0?tag('a class="up" href="javascript:modulosUp('.$fetch->id.')" ','&nbsp;'):'')
					.tag('td', ($i<($n-1))?tag('a class="down" href="javascript:modulosDown('.$fetch->id.')" ','&nbsp;'):'')
					.tag('td', tag('a class="del" href="javascript:modulosDel('.$fetch->id.')" ','&nbsp;'))
				);

			}

		}

		return $edicao ;
	}
	
	private function atualizaModulo($modulo_id=0){

		$results = results($sql=
				"
				SELECT
					modulo.*
				FROM
					modulo
				WHERE
					modulo_id = {$modulo_id}
				ORDER BY
					modulo_id, ordem, nome");

		//printr($results);

		$ordem = 1;

		for($i=0,$n=sizeof($results);$i<$n;$i++){

			$fetch = $results[$i];
			//if(intval($fetch->modulo_id)>0){

			if(rows(query("SELECT * FROM modulo WHERE modulo_id = {$fetch->id}"))>0){
				$this->atualizaModulo($fetch->id);
			}
			else {

				query("UPDATE modulo SET ordem = {$ordem} WHERE id = {$fetch->id}");
				$ordem ++;
			}
		}
	}

	public function textos(){

		$this->filtraLogado();
		$this->filtraPermissao('textos');

		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Textos');

		if(request('action')=='editar'
			||substr(request('action'),0,6)=='salvar'){
			$this->textosEditar($t);
			return;
		}

		if(request('action')=='excluir'){
			$texto = new texto(intval(request('id')));
			$texto->exclui();
		}

		$grid = new grid();

		$grid->sql = 'SELECT
						texto.id
						,texto.nome
						,texto.st_ativo Status
					FROM
						texto
					WHERE
						1=1
						' ;

		$filtro = new filtro();

		$filtro->add_input('Nome','Nome:');

		$grid->filtro = $filtro ;

		$t->grid = $grid->render();

		$this->montaMenu($t);
		$this->show($t);
	}

	public function textosEditar($t){

		$this->filtraLogado();
		$this->filtraPermissao('textos');

		$texto = new texto(intval(request('id')));

		if(substr(request('action'),0,6)=='salvar'){

			$next = substr(request('action'),7,strlen(request('action')));

			$texto->set_by_array($_REQUEST['texto']);
			$texto->salva();

			$texto = new texto($texto->id);

			$_SESSION['sucesso'] = 'Dados salvos com sucesso';

			if(trim(@$next)!=''){
				$this->afterSave($next,'categorias');
				return;
			}
		}

		$t->parseBlock('BLOCK_TOOLBAR');

		$t->h1 = h1('Texto '.$texto->nome);

		$edicao = '';
		$edicao .= inputHidden('texto[id]', $texto->id);

		$edicao .= tag('h2', 'Dados básicos');

		$edicao .= select('texto[st_ativo]', $texto->st_ativo, 'Ativo?:', array('S'=>'Sim','N'=>'Nao'));

		$edicao .= inputSimples('texto[nome]', $texto->nome, 'Nome:', 45, 50);
		if($this->config->HABILITA_ESPANHOL=='S'){
			$edicao .= inputSimples('texto[nome_es]', $texto->nome_es, 'Nome:', 45, 50);
		}
		if($this->config->HABILITA_INGLES=='S'){
			$edicao .= inputSimples('texto[nome_in]', $texto->nome_in, 'Nome:', 45, 50);
		}

		$edicao .= editor('texto[conteudo]', $texto->conteudo, 'Conteúdo:');
		if($this->config->HABILITA_ESPANHOL=='S'){
			$edicao .= editor('texto[conteudo_es]', $texto->conteudo_es, 'Conteúdo:');
		}
		if($this->config->HABILITA_INGLES=='S'){
			$edicao .= editor('texto[conteudo_in]', $texto->conteudo_in, 'Conteúdo:');
		}

		$edicao .= str_repeat(tag('br'),5);

		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}

	public function representantes(){

		$this->filtraLogado();
		$this->filtraPermissao('representantes');

		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Representantes');

		if(request('action')=='editar'
			||substr(request('action'),0,6)=='salvar'){
			$this->vendedoresEditar($t);
			return;
		}

		if(request('action')=='excluir'){
			$cadastro = new cadastro(intval(request('id')));
			$cadastro->exclui();
			$_SESSION['sucesso'] = tag("p","Representante excluído com sucesso.");
		}

		$grid = new grid();

		$grid->sql = "SELECT id, nome Nome, email Email, fone_com FoneComercial, fone_res FoneResidencial, fone_cel FoneCelular, st_ativo Status, st_fixo fixo from cadastro WHERE 1=1 AND tipocadastro_id = ".tipocadastro::getId('VENDEDOR');

		$filtro = new filtro();

		$filtro->add_input('Nome','Nome:');
		$filtro->add_input('Email','Email:');

		$grid->filtro = $filtro ;
		$grid->metodo = 'representantes';

		$t->grid = $grid->render();

		$this->montaMenu($t);
		$this->show($t);
	}

	public function crm_emails(){

		$this->filtraLogado();
		$this->filtraPermissao('crm_emails');

		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('e-Mail marketing - Base de e-mails');

		if(request('action')=='editar' || request('action')=='sair'
			||substr(request('action'),0,6)=='salvar'){
			$this->crm_emailsEditar($t);
			return;
		}

		if(request('action')=='excluir'){
			$newscadastro = new newscadastro(intval(request('id')));
			$newscadastro->exclui();
		}

		$grid = new grid() ;

		$edicao = '';

		$sql = 'select id, nome, email, st_ativo Status from newscadastro order by id desc';

		$grid->sql = $sql;

		//print $grid->sql;

		$filtro = new filtro();

		$filtro->add_input('nome','Nome:');
		$filtro->add_input('email','email:');
		$filtro->add_status('Status','Status:');

		$grid->filtro = $filtro ;
		$grid->metodo = 'crm_emails' ;
		$filtro->excel = $this->boxExpExcel($sql,'Base-de-emails',$filtro);

		$grid-> ordem_desc = true;
		$grid->filtro = $filtro ;
		
		$edicao .= $grid->render();
	
		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}

	public function crm_emailsEditar($t){

		$this->filtraLogado();
		$this->filtraPermissao('crm_emails');
		
		if(request('popup')){
			$t = new TemplateAdminPopup('admin/tpl.admin-cadastro-generico.html');
		}

		$t->parseBlock('BLOCK_TOOLBAR');

		$t->h1 = h1('Base de e-mails - editar');

		$newscadastro = new newscadastro(intval(request('id')));

		if(substr(request('action'),0,6)=='salvar'){

			$newscadastro->set_by_array($_REQUEST['newscadastro']);

			if($newscadastro->validaDados($erro)){
				$_SESSION['sucesso'] = tag('p', 'Dados salvos com sucesso');
			}
			else {
				$_SESSION['erro'] = tag('p', 'Houve uma falha ao salvar os dados');
			}

			$newscadastro->salva();

			$next = substr(request('action'),7,strlen(request('action')));

			if(trim(@$next)!=''){
				$this->afterSave($next,'crm_emails');
				return;
			}
		}
		if(request('action')=='sair'){$this->afterSave('sair','crm_emails',$newscadastro);}

		$edicao = '';

		$edicao .= inputHidden('newscadastro[id]', $newscadastro->id);

		$edicao .= "<div class='box-block'>";
		$edicao .= tag('h2', 'Dados básicos');

		$edicao .= select('newscadastro[st_ativo]', $newscadastro->st_ativo, 'Ativo?:', array('S'=>'Sim','N'=>'Nao'));
		$edicao .= inputSimples('newscadastro[nome]', $newscadastro->nome, 'Nome Completo:', 45, 50);
		$edicao .= inputSimples('newscadastro[email]', $newscadastro->email, 'eMail:', 50, 60);
		
		$edicao .= "</div>";
		
		$t->edicao = $edicao ;

		if(!request('pop')){
			$this->montaMenu($t);
		}
		$this->show($t);
	}

	public function crm_campanhas(){

		$this->filtraLogado();

		if(request('action')=='crm_campanhas_send_1'){
			$this->crm_campanhas_send_1();
		}

		$t = new TemplateAdmin('admin/tpl.crm-campanhas.html');

		if(substr(request('action'),0,6)=='salvar'){
			$newscampanha = new newscampanha();
			$newscampanha->set_by_array($_REQUEST['newscampanha']);
			
			// no servidor de producao
			if(DEBUG==0){
				$newscampanha->lista_sql = str_replace( ' " ' , " \ \ ' "  ,$newscampanha->lista_sql);
			}
			if(DEBUG==1){
				$newscampanha->lista_sql = str_replace( ' " ' , " \ \ ' " ,$newscampanha->lista_sql);
			}
			
			// no servidor de teste
			
			//printr($newscampanha);
			$newscampanha->salva();
		}

		$sql = "SELECT * FROM newscampanha ORDER BY id DESC";
		foreach(results($sql) as $newscampanha){
			$t->newscampanha = $newscampanha ;
			$t->parseBlock('BLOCK_NEWSCAMPANHA', true);
		}

		$sql = "SELECT * FROM newstemplate";
		foreach(results($sql) as $newstemplate){
			$t->newstemplate = $newstemplate ;
			$t->parseBlock('BLOCK_NEWSTEMPLATE', true);
		}

		$this->montaMenu($t);
		$this->show($t);
	}

	public function crm_campanhas_load_vars($newstemplate_id){

		$newstemplate = new newstemplate(intval($newstemplate_id));

		if($newstemplate->id){
			$t = new Template($newstemplate->html, false, true);

			$htmlTextos = '';

			//printr($t->properties);
			print '<h2>Configuração do e-mail:</h2>';

			//printr($t);
			foreach(is_array(@$t->properties['textolinha'])?@$t->properties['textolinha']:array() as $v){
				print inputSimples("textolinha[$v]", '', $v.':', 60, 50, "onchange=\"updatePreview()\"");
			}
			foreach(is_array(@$t->properties['textogrande'])?@$t->properties['textogrande']:array() as $v){
				print textArea("textogrande[$v]", '', $v.':', 60, 5, '',  "onchange=\"updatePreview()\"");
			}

			$lines = explode("\n", $newstemplate->html);

			foreach ($lines as $line){
				// IDENTIFICA PRODUTOS
				if(preg_match('/{item_([a-z]{1,})_([0-9]{1,})}/',$line,$match)) {
					//printr($match);
					if(!@$itens[$match[2]]){
						$itens[$match[2]] = array();
					}
					$itens[$match[2]][] = $match[1];
				}
			}

			if(sizeof(@$itens)>0){
				echo tag('p style="clear:both"', 'Digite a referencia dos '.sizeof($itens).' produtos, que fazem parte dessa campanha');
				$i=1;
				foreach($itens as $item){
					echo tag('div style="float:left;width:100px"',
						inputSimples('item_referencia[]', '', "Ref. {$i}", 20, 20, "onchange=\"refProdutoChange(this,{$i})\"")
						.tag("div id='divRef{$i}'", '&nbsp;')
						);
					$i ++;
				}
				echo tag('br clear="all"');
			}

			$i=1;
			$imagem = is_array(@$t->properties['imagem'])?$t->properties['imagem']:array();
			//printr($imagem);

			//printr($t);

			if(sizeof($imagem)>0){
				for($i=1, $n=(sizeof($imagem)/2); $i<=$n; $i++){
					echo tag('p',tag('b',"Imagem {$i}"));
					echo inputSimples("imagem[$i][href]", '', "Link {$i} ex: http://www.google.com.br", 100, 100);
					echo '<br />';
					echo inputHidden("imagem[$i][src]", '', "SRC {$i}", 100, 100);
					//echo inputSimples("imagem_src_{$i}",'','',100, '',"onchange=\"updatePreview()\"");
					echo tag('input type="file" name="src_'.$i.'" onchange="uploadImg(this,'.$i.')"');
				}
			}
		}
	}

	public function crm_campanhas_load_vars_item(){

		$item = new item();
		$item->get_by_referencia(request('item_ref'));

		$indice = request('indice');

		if($item->id){
			echo tag('p',tag('b', tag('small',"{$item->referencia}<br>{$item->nome}")));
			foreach (get_class_vars(get_class($item)) as $key_name => $value ){
				echo inputHidden("item_{$key_name}_{$indice}", $item->$key_name);
			}
			foreach ( array('peq'=>PATH_PEQ, 'int'=>PATH_INT, 'med'=>PATH_MED, 'grd'=>PATH_GRD, 'gig'=>PATH_GIG) as $path_key => $path_value ){
				echo inputHidden("item_imagem_{$path_key}_{$indice}", $this->config->URL.$path_value.$item->imagem);
			}
			echo inputHidden("item_link_{$indice}", $this->config->URL.'index.php/det/'.$item->id);
		}
		else {
			echo tag('p','Não encontrado');
		}
	}

	public function crm_campanhas_preview(){

		$newstemplate = new newstemplate();
		$newstemplate->get_by_id(intval(request('newstemplate_id')));

		if($newstemplate->id){

			// printr($newstemplate);
		
			//$newstemplate->html .= '<br /><center><a href="'.configuracao::get('URL').'admin/cad.crm.php?del={news_envio->email}">clique aqui para remover o seu e-mail da lista</a></center>' ;

			// $newstemplate->html = '<meta name="content" content="Content-type:text/html; charset:iso-8859-1" />' . $newstemplate->html;
			
			$t = new Template($newstemplate->html,false,true);

			//printr($_REQUEST);

			if(@$_REQUEST['textolinha']&&is_array($_REQUEST['textolinha'])){
				$textolinha = new stdClass();
				foreach($_REQUEST['textolinha'] as $k=>$v){
					$textolinha->$k = ($v);
					//$textolinha->$k = htmlentities($v);
				}
				$t->textolinha = $textolinha;
				//printr($textolinha);
			}

			if(@$_REQUEST['textogrande']&&is_array($_REQUEST['textogrande'])){
				$textogrande = new stdClass();
				foreach($_REQUEST['textogrande'] as $k=>$v){
					$textogrande->$k = nl2br($v);
				}
				$t->textogrande = $textogrande;
				//printr($textolinha);
			}

			//printr($_REQUEST);

			if(@$_REQUEST['imagem']&&is_array($_REQUEST['imagem'])){
				foreach($_REQUEST['imagem'] as $indice=>$arrImagem){
					//foreach($P)
					$imagem = new stdClass();
					$imagem->href = '';
					$imagem->src = '';
					foreach($arrImagem as $k=>$v){
						if($v){
							$imagem->$k = $v;
						}
					}
					if(@$imagem->src){
						$t->imagem = $imagem;
						$t->parseBlock('BLOCK_IMAGEM_'.$indice);
					}
				}
			}

			if(@$t->properties['config']){
				$t->config = $this->config;
			}

			foreach ( $t->vars as $var_index => $var_name ){
				if(@$_REQUEST[$var_name]&&(!is_array(@$_REQUEST[$var_name]))){
					//print tag('p',$var_name);
					//print $_REQUEST[$var_name];
					$t->$var_name = nl2br($_REQUEST[$var_name]);
				}
			}

			$filename = 'admin/newspreview/preview'.mktime().'.html';

			$file = fopen($filename,'w');
			
			if($newstemplate->nome == 'HTML'){
				fwrite($file, ($t->getContent()));
			}
			else {
				fwrite($file, iconv('utf-8','iso-8859-1',$t->getContent()));
			}
			
			//fwrite($file, utf8_encode($t->getContent()));
			
			fclose($file);

			print tag('br').tag('h2', 'Preview:');
			echo '<iframe src="'.PATH_SITE.$filename.'" width="100%" height="500px" border="1" bordercolor="#eeeeee"></iframe>';
			//echo tag('div style="padding:10px;background-color:#eeeeee"', $t->getContent());

			print "<textarea name='newscampanha[html]' style='display:none;width:100%;height:400px'>".$t->getContent()."</textarea>";
			//print "<textarea width='100%' height=200px >".print_r($_REQUEST)."</textarea>";
		}
		else {
			print '';
		}
	}

	public function crm_campanhas_upload_img(){
		//printr($_REQUEST);
		//printr($_FILES);
		$arr = array();
		foreach($_FILES as $key => $file) {

			if($file['tmp_name']!=''){

				list($src,$i) = explode('_', $key);

				@unlink($src='img/newsletter/'.$file['name']);
				copy($file['tmp_name'], 'img/newsletter/'.$file['name']);

				$arr[] = str_replace('src_','',$key).';'.$this->config->URL.'img/newsletter/'.$file['name'];
			}
        }
		print join('@',$arr);
	}

	public function crm_campanhas_salvar(){
	}

	public function crm_campanhas_send($newscampanha_id){

		$this->filtraLogado();

		$newscampanha = new newscampanha(intval($newscampanha_id));
		if(!$newscampanha->id){
			die();
		}

		$t = new TemplateAdmin('admin/tpl.crm-campanhas-send.html');
		$totalemails = 0;
		
		$qtdTotalEnviar = 0;
		$qtdAtualEnviado = 0;
		
		$enviar = 0;
		
		foreach(results($newscampanha->lista_sql) as $envio){

			$newsfila = new newsfila();
			
			// $newsfila->get_by_news_email($envio->email);
			
			$newsfila = new newsfila(
				array(
					'news_email' => $envio->email
					,'newscampanha_id' => $newscampanha->id
				)
			);
			
			if(!$newsfila->id)
			{
				$e = new stdClass();
				
				$e->nome = $envio->nome;
				$e->email = $envio->email;
				$t->envio = $e;
				$t->parseBlock('BLOCK_ENVIO', true);
				
				$enviar ++;
			}
			else
			{
				$qtdAtualEnviado ++ ;
			}
			
			$qtdTotalEnviar ++ ;
		}

		if($enviar>0)
		{
			$t->parseBlock('BLOCK_PROCESSA');
		}
		else
		{
			if($qtdTotalEnviar > 0)
			{
				$t->parseBlock('BLOCK_REINICIAR');
			}
		}
		
		$t->qtdTotalEnviar = $qtdTotalEnviar;
		$t->qtdAtualEnviado = $qtdAtualEnviado;
		$t->newscampanha = $newscampanha;

		$this->montaMenu($t);
		$this->show($t);
	}

	public function ajax_crm_campanhas_send(){

		$this->filtraLogado();

		$news_email = request('email');
		$news_nome = request('nome');

		if(!is_email($news_email)){
			die();
		}

		$newscampanha = new newscampanha(intval(request('newscampanha_id')));
		
		if(!$newscampanha->id){
			die();
		}

		$newsenvio = new newsenvio();

		$newsenvio->newscampanha_id = $newscampanha->id;
		$newsenvio->is_enviado = 1;
		$newsenvio->is_lido = 0;
		$newsenvio->news_email = $news_email;
		$newsenvio->news_nome = $news_nome;
		$newsenvio->data_envio = bd_now();
		
		$newsfila = new newsfila();
			
		$newsfila->get_by_id(
			array(
				'newscampanha_id' => $newscampanha->id
				,'news_email' => $news_email
			)
		);
		
		$newsfila->newscampanha_id = $newscampanha->id;
		$newsfila->news_email = $news_email;
		
		if(!$newsfila->insere()){
			die();
		}

		if(!$newsenvio->insere()){
			die();
		}

		$email = new email();

		$email->addHtml($newscampanha->html);
		$email->addTo($news_email, $news_nome);
		$email->send($newscampanha->assunto);

		print json_encode($newsenvio);
	}

	public function ajax_crm_campanhas_zera(){
	
		$this->filtraLogado();

		$newscampanha = new newscampanha(intval(request('newscampanha_id')));
		
		if(!$newscampanha->id){
			die();
		}

		query("delete from newsfila where newscampanha_id = {$newscampanha->id}");

		print "OK";
	}
	
	public function crm_campanhas_send_1(){

		$this->filtraLogado();

		if(!is_email(request('email_teste'))){
			$_SESSION['erro'] = 'Digite um e-mail válido';
			$this->setLocation('crm_campanhas');
		}

		$newscampanha = new newscampanha(intval(request('newscampanha_id')));
		if(!$newscampanha->id){
			$_SESSION['erro'] = 'Campanha não encontrada';
			$this->setLocation('crm_campanhas');
		}

		$newsenvio = new newsenvio();

		$newsenvio->newscampanha_id = $newscampanha->id;
		$newsenvio->is_enviado = 1;
		$newsenvio->is_lido = 0;
		$newsenvio->news_email = request('email_teste');
		$newsenvio->data_envio = bd_now();

		if(!$newsenvio->insere()){
			$_SESSION['erro'] = 'Falha na inserção do envio';
			$this->location('crm_campanhas');
		}

		$email = new email();

		$email->addHtml($newscampanha->html);
		$email->addTo(request('email_teste'));
		$email->send($newscampanha->assunto);

		$_SESSION['sucesso'] = 'E-mail enviado para '.request('email_teste');
	}

	public function crm_campanhas_del($newscampanha_id){
		$newscampanha = new newscampanha(intval($newscampanha_id));
		$newscampanha->exclui();
		$this->setLocation('crm_campanhas');
	}

	public function crm_campanhas_view($newscampanha_id){
		$newscampanha = new newscampanha(intval($newscampanha_id));
		print '<div style="background-color:#eeeeee"><a href="'.PATH_SITE.'admin.php/crm_campanhas">voltar</a></div>';
		print $newscampanha->html;
	}

	public function configuracao(){

		$this->filtraLogado();
		$this->filtraPermissao('configuracao');

		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');

		$t->h1 = h1('Configuração');

		if(request('action')=='salvar'){

			/*ATUALIZA AS CONFIGURACOES ATUAIS*/
			foreach(is_array(@$_REQUEST['config'])?$_REQUEST['config']:array() as $id => $arrConfig){

				$config = new config($id);
				
				if($config->st_tipocampo == 'TOPTIONS')
				{
					// printr($arrConfig);
					// printr(json_encode($arrConfig['options']));
					
					$options = array();
					if(is_array(@$arrConfig['options'])){
						foreach($arrConfig['options'] as $i => $arrOption){
							if(@$arrOption['value']==''&&@$arrOption['text']==''){
								unset($arrConfig['options'][$i]);
							}
							else {
								$arrConfig['options'][$i]['text'] = rawurlencode($arrConfig['options'][$i]['text']);
							}
						}
						$options = $arrConfig['options'];
					}
					
					$arrConfig['valor'] = json_encode($options);
				}
			
				$config->set_by_array($arrConfig);
				$config->salva();

				if($config->chave=='EMAIL_CONTATO'){
					//printr($config);
					//printr($arrConfig);
					//die();
				}

				$_SESSION['sucesso'] = tag('p', 'Dados salvos com sucesso');

				unset($config);
				
			}

			/*CADASTRA UMA CONFIG NOVA*/
			if(is_array(@$_REQUEST['configNova']) && $_REQUEST['configNova']['valor']!=''){

				$config = new config();
				$config->set_by_array($_REQUEST['configNova']);
				$config->salva();

			}

			/*EXCLUI AS CONFIGS MARCADAS*/
			foreach(is_array(@$_REQUEST['configExcluir'])?$_REQUEST['configExcluir']:array() as $id => $arrConfig){

				$config = new config($id);
				$config->exclui();

				unset($config);
			}

			//printr($_REQUEST);
		}

		$edicao = '';

		$this->montaMenuSimples($t);

		$edicao .= tag('div class="box-info"',
							'
							As informações aqui relacionadas são utilizadas como parametros dentro do site
							');

		$edicao .= '<table class="grid">';

		$queryGrupo = query($sql="SELECT
									DISTINCT
										grupo
									FROM
										config
									ORDER
										BY grupo");

		while($fetchGrupo=fetch($queryGrupo) ){

			$first = true;

			$queryConfig = query($sql="SELECT * FROM config WHERE grupo = '{$fetchGrupo->grupo}' ORDER BY chave");
			while($fetchConfig=fetch($queryConfig) ){

				if(($_SESSION['CADASTRO']->email!='dev@ajung.com.br')
				&&$fetchConfig->st_admin=='S'){
					continue;
				}

				if($first){
					$edicao .= tag('tr', tag('td colspan="4"', tag('h2', $fetchGrupo->grupo)));
				}

				$edicao .= tag('tr',
								tag('td','&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$fetchConfig->chave)
								.tag('td', $this->getCampoConfig($fetchConfig))
								.tag('td', nl2br($fetchConfig->obs))
								.tag('td', ($_SESSION['CADASTRO']->email=='dev@ajung.com.br'?select("config[{$fetchConfig->id}][st_admin]", $fetchConfig->st_admin, '', array('S'=>'Sim','N'=>'Nao')):''))
								.tag('td', ($fetchConfig->st_podeexcluir=='S'?checkbox("configExcluir[{$fetchConfig->id}][excluir]",'Sim','Excluir'):''))
							);

				$first = false;

			}
		}

		$edicao .= '</table>';
		$edicao .= str_repeat(tag('br'), 3);

		if(($_SESSION['CADASTRO']->email=='dev@ajung.com.br')){


			$edicao .= tag('h3', 'Nova configuração');

			$edicao .= tag('div class="box-info"',
								'
								Uso interno da equipe de desenvolvimento
								');

			$edicao .= inputSimples('configNova[grupo]', '', 'Grupo', 45, 50);
			$edicao .= inputSimples('configNova[chave]', '', 'Chave interna', 45, 50);
			$edicao .= select('configNova[st_tipocampo]', '', 'Tipo de campo', 
					array(	'TLINE'=>'TLINE'
							,'TMULTIPLE'=>'TMULTIPLE'
							,'TOPTIONS'=>'TOPTIONS'
							,'TBOOLEAN'=>'TBOOLEAN'
							,'TPAGINA'=>'TPAGINA'
						)
					);
					
			$edicao .= textArea("configNova[valor]",'','Valor');

		}

		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}

	public function getCampoConfig($fetchConfig){

		$return = '';

		if($fetchConfig->st_podealterar=='S'){
		
			// printr($fetchConfig);

			if($fetchConfig->st_tipocampo=='TLINE'){
				
				if($fetchConfig->chave == 'SMTP_SENHA'){							
					$return .= inputPass("config[{$fetchConfig->id}][valor]",$fetchConfig->valor,'',40,400);					
					//$return .= inputSimples("config[{$fetchConfig->id}][valor]",$fetchConfig->valor,'',40,400);
				}else{			
					$return .= inputSimples("config[{$fetchConfig->id}][valor]",$fetchConfig->valor,'',40,400);
				}
			}
			elseif($fetchConfig->st_tipocampo=='TMULTIPLE'){
				$return .= textArea("config[{$fetchConfig->id}][valor]",$fetchConfig->valor,'',60,5);
			}
			elseif($fetchConfig->st_tipocampo=='TOPTIONS'){
			
				$options = @json_decode($fetchConfig->valor);
			
				$return .= 'Opções';
				$return .= '<table id="table'.$fetchConfig->chave.'">';
				
				$i = 0;
				
				if(is_array($options))
				{
					foreach ($options as $objOption){

						$objOption->text = rawurldecode(utf8_decode($objOption->text));
					
						$return .= tag('tr'
									,tag('td', inputSimples("config[{$fetchConfig->id}][options][{$i}][text]",$objOption->text,'',30,400))
									.tag('td', inputSimples("config[{$fetchConfig->id}][options][{$i}][value]",$objOption->value,'',30,400))
								);
						$i++;
					}
					
				}
				
				$return .= '</table>';
				
				$return .= js('table'.$fetchConfig->chave.'Contador = '.$i);
				
				
				if ($i < 10){				
					$return .= tag('input type="button" value="+" onclick="$(\'#table'.$fetchConfig->chave.'\').append(\'<tr><td><input type=text class=text size=30 name=\\\'config['.$fetchConfig->id.'][options][\'+table'.$fetchConfig->chave.'Contador+\'][text]\\\' /></td><td><input type=text class=text size=30 name=\\\'config['.$fetchConfig->id.'][options][\'+table'.$fetchConfig->chave.'Contador++ +\'][value]\\\' /></td></tr>\') "');
				}
				
			}
			elseif($fetchConfig->st_tipocampo=='TBOOLEAN'){
				$return .= select("config[{$fetchConfig->id}][valor]",$fetchConfig->valor,'',array('S'=>'Sim','N'=>'Não'));
			}
			elseif($fetchConfig->st_tipocampo=='TPAGINA'){
				//printr(pagina::opcoes());
				$return .= select("config[{$fetchConfig->id}][valor]",$fetchConfig->valor,'',pagina::opcoes());
			}
		}

		return $return;
	}

	public function permissoes($modulo_id='0', $cadastro_id, &$modulos = array(), &$permissoes = array()){

		$return = '';

        if(sizeof($modulos)==0){
            $modulos = results($sql="SELECT modulo.* FROM modulo WHERE st_ativo = 'S' ORDER BY ordem,nome");
            $permissoes = results($sql="SELECT permissao.* FROM permissao WHERE cadastro_id = {$cadastro_id} ");
        }

        $tmp = array();
        foreach($modulos as $modulo){
            if(intval($modulo->modulo_id) == intval($modulo_id)){
                $tmp[] = $modulo;
            }
        }

        if(sizeof($tmp)=='') {
            return;
        }

		$c = new cadastro($cadastro_id);

        // $query = query($sql="SELECT modulo.* FROM modulo WHERE modulo.modulo_id = {$modulo_id} AND st_ativo = 'S' ORDER BY ordem,nome");

        $return .= '<ul style="list-style-type:none">';
		foreach($tmp as $fetch){
        // while($fetch=fetch($query)){

			if($fetch->st_admin=='S' && $_SESSION['CADASTRO']->email != 'dev@ajung.com.br'){
				continue;
			}

			//if($c->tipocadastro_id==tipocadastro::getId('VENDEDOR')&&$fetch->st_vendedor=='N'){
			//	continue;
			//}

            $fetchPermissao = false;
            foreach($permissoes as $permissao){
                if(intval($permissao->modulo_id) == intval($fetch->id)){
                    $fetchPermissao = true;
                    break;
                }
            }

			$return .= '<li><label style="font-weight:normal;"><input type="checkbox" '.($fetchPermissao?'checked':'').' name="modulo_id[]" value="'.$fetch->id.'"> '.$fetch->nome.'</label>';

			// $sql="SELECT modulo.* FROM modulo WHERE modulo.modulo_id = {$fetch->id} AND st_ativo = 'S' ORDER BY ordem, nome";
			// if(rows(query($sql))>0){
            $return .= $this->permissoes($fetch->id, $cadastro_id, $modulos, $permissoes);
			// }

			$return .= '</li>';

		}
		$return .= '</ul>';

		return $return;
	}

	public function faixa(){

		$this->filtraLogado();
		$this->filtraPermissao('faixa');

		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Faixa de Preço');

		if(request('action')=='salvar'){

			/*ATUALIZA AS faixaURACOES ATUAIS*/
			foreach(is_array(@$_REQUEST['faixa'])?$_REQUEST['faixa']:array() as $id => $arrfaixa){

				$faixa = new faixa($id);
				$faixa->set_by_array($arrfaixa);
				$faixa->qtd_de = intval($faixa->qtd_de);
				$faixa->qtd_ate = intval($faixa->qtd_ate);

				$erro = array();
				if($faixa->validaDados($erro)){
					$faixa->salva();
				}
				else{
					$_SESSION['erro'] = join('<br />', $erro);
				}
			}

			/*CADASTRA UMA faixa NOVA*/
			if(is_array(@$_REQUEST['faixaNova']) && $_REQUEST['faixaNova']['qtd_de']!=''){

				$faixa = new faixa();
				$faixa->set_by_array($_REQUEST['faixaNova']);

				$faixa->qtd_de = intval($faixa->qtd_de);
				$faixa->qtd_ate = intval($faixa->qtd_ate);

				$erro = array();
				if($faixa->validaDados($erro)){
					$faixa->salva();
				}
				else{
					$_SESSION['erro'] = join('<br />', $erro);
				}
				unset($faixa);
			}

			/*EXCLUI AS faixaS MARCADAS*/
			foreach(is_array(@$_REQUEST['faixaExcluir'])?$_REQUEST['faixaExcluir']:array() as $id => $arrfaixa){

				$faixa = new faixa($id);
				$faixa->exclui();

				unset($faixa);
			}

			//printr($_REQUEST);
		}

		$edicao = '';

		$this->montaMenuSimples($t);

		$edicao .= tag('div class="box-info"',
							'
							Aqui você configura as possíveis variações de quantidade que podem influir no preço do produto
							');

		$edicao .= '<table class="grid">';
		$edicao .= tag('tr',
				tag('th', 'Quantidade inicial')
				.tag('th', 'Quantidade final')
				.tag('th', '&nbsp;')
			);

		$query = query($sql="SELECT * FROM faixa ORDER BY qtd_de");
		while($fetch=fetch($query) ){

			$edicao .= tag('tr',
				tag('td', inputSimples("faixa[{$fetch->id}][qtd_de]",$fetch->qtd_de,'',40,30))
				.tag('td', inputSimples("faixa[{$fetch->id}][qtd_ate]",$fetch->qtd_ate,'',40,30))
				.tag('td', checkbox("faixaExcluir[{$fetch->id}][excluir]",'Sim','Excluir'))
			);

		}

		$edicao .= '</table>';
		$edicao .= str_repeat(tag('br'), 3);


		$edicao .= tag('h3', 'Nova Faixa de Preço');

		$edicao .= inputSimples('faixaNova[qtd_de]', '', 'Quantidade inicial',40,30);
		$edicao .= inputSimples('faixaNova[qtd_ate]', '', 'Quantidade final',40,30);

		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}

	public function materiaprima(){

		$this->filtraLogado();
		$this->filtraPermissao('materiaprima');

		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Matéria Prima');

		if(request('action')=='salvar'){

			/*ATUALIZA AS MATERIAS PRIMAS ATUAIS*/
			foreach(is_array(@$_REQUEST['caracvalor'])?$_REQUEST['caracvalor']:array() as $id => $arrcaracvalor){

				$caracvalor = new caracvalor($id);
				$caracvalor->set_by_array($arrcaracvalor);

				$caracvalor->setFile($_FILES["file_{$id}"]);

				$erro = array();
				if($caracvalor->validaDados($erro)){

					//printr($caracvalor);
					$caracvalor->salva();
				}
				else{
					$_SESSION['erro'] = join('<br />', $erro);
				}
			}

			/*CADASTRA UMA faixa NOVA*/
			if(is_array(@$_REQUEST['caracvalorNova']) && $_REQUEST['caracvalorNova']['nome']!=''){

				$caracvalor = new caracvalor();
				$caracvalor->set_by_array($_REQUEST['caracvalorNova']);

				$caracvalor->setFile($_FILES["file_nova"]);

				$erro = array();
				if($caracvalor->validaDados($erro)){
					$caracvalor->salva();
				}
				else{
					$_SESSION['erro'] = join('<br />', $erro);
				}
				unset($caracvalor);
			}

			/*EXCLUI AS faixaS MARCADAS*/
			foreach(is_array(@$_REQUEST['caracvalorExcluir'])?$_REQUEST['caracvalorExcluir']:array() as $id => $arrcaracvalor){

				$caracvalor = new caracvalor($id);
				$caracvalor->exclui();

				unset($caracvalor);
			}

			//printr($_REQUEST);
		}

		$edicao = '';

		$this->montaMenuSimples($t);

		$edicao .= tag('div class="box-info"',
						tag('p','Formato ideal para imagens:')
						.tag('p','.jpg com 130x130px no tamanho')
					);

		$edicao .= '<table class="grid">';

		$edicao .= tag('tr',
				tag('th', 'Status Ativo')
				.tag('th', 'Imagem')
				#.tag('th', 'Coleção')
				.tag('th', 'Nome')
				.tag('th', 'Descricao')
				.tag('th', 'Excluir')
			);

		$query = query($sql="SELECT * FROM caracvalor WHERE carac_id = 1 ORDER BY nome");
		while($fetch=fetch($query) ){

			$serial = unserialize($fetch->serial);

			$edicao .= tag('tr',
				tag('td', select("caracvalor[{$fetch->id}][st_ativo]", $fetch->st_ativo, '', array('S'=>'Sim','N'=>'Nao')))
				.tag('td', ($fetch->imagem!=''?tag('img src="'.PATH_SITE.'img/materiaprima/130x130/'.$fetch->imagem.'"').tag('br'):'').inputFile("file_{$fetch->id}",'',''))
				#.tag('td', inputSimples("caracvalor[{$fetch->id}][serial][colecao]",$serial['colecao'],'',20,60))
				.tag('td', inputSimples("caracvalor[{$fetch->id}][nome]",$fetch->nome,'',10,60))
				.tag('td', textArea("caracvalor[{$fetch->id}][descricao]",$fetch->descricao,'',20,5))
				.tag('td', checkbox("caracvalorExcluir[{$fetch->id}][excluir]",'Sim','Excluir'))
			);

		}

		$edicao .= '</table>';
		$edicao .= str_repeat(tag('br'), 3);

		$edicao .= tag('h3', 'Nova Matéria Prima');

		$edicao .= inputHidden("caracvalorNova[carac_id]",'1');
		$edicao .= select("caracvalorNova[st_ativo]", 'S', 'Status Ativo', array('S'=>'Sim','N'=>'Nao'));
		$edicao .= inputFile("file_nova",'','Imagem');
		#$edicao .= inputSimples("caracvalorNova[serial][colecao]",'','Coleção',20,60);
		$edicao .= inputSimples("caracvalorNova[nome]",'','Nome',40,60);
		$edicao .= textArea("caracvalorNova[descricao]",'','Descricao');

		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}

	public function status(){
		
		$this->filtraLogado();
		$this->filtraPermissao('status');

		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Status');

		if(request('action')=='salvar'){

			/*ATUALIZA AS MATERIAS PRIMAS ATUAIS*/
			foreach(is_array(@$_REQUEST['pedidostatus'])?$_REQUEST['pedidostatus']:array() as $id => $arrpedidostatus){

				$pedidostatus = new pedidostatus($id);
				$pedidostatus->set_by_array($arrpedidostatus);

				//$pedidostatus->setFile($_FILES["file_{$id}"]);

				$erro = array();
								
				/*if($pedidostatus->validaDados($erro)){
					
					$pedidostatus->salva();
				}
				else{
					$_SESSION['erro'] = join('<br />', $erro);
				}*/
				
				$pedidostatus->salva();
			}

			/*CADASTRA UMA faixa NOVA*/
			if(is_array(@$_REQUEST['pedidostatusNovo']) && $_REQUEST['pedidostatusNovo']['descricao']!=''){

				$nomeStatus =  $_REQUEST['pedidostatusNovo']['descricao'];
				
				$query = query($sql="INSERT INTO pedidostatus (id, descricao, ordem) values('','$nomeStatus','')");
				
				print $nomeStatus;

				/*$erro = array();
				if($pedidostatus->validaDados($erro)){
					if(!$pedidostatus->salva()){
						$_SESSION['erro'] = tag('p', 'Houve uma falha ao salvar os dados');
					}
				}
				else{
					$_SESSION['erro'] = join('<br />', $erro);
				}*/
				
				$pedidostatus->salva();
				unset($pedidostatus);
			}

			/*EXCLUI AS faixaS MARCADAS*/
			foreach(is_array(@$_REQUEST['pedidostatusExcluir'])?$_REQUEST['pedidostatusExcluir']:array() as $id => $arrpedidostatus){

				$pedidostatus = new pedidostatus($id);
				$pedidostatus->exclui();

				unset($pedidostatus);
			}

			//printr($_REQUEST);
		}

		$this->montaMenuSimples($t);

		$edicao = '';

		$edicao = inputHidden('id', '');

		$edicao .= '<table class="grid">';
		$edicao .= tag('tr',
				tag('th colspan="2"', 'Nome')
			);

		//$query = query();
		
		$query = query($sql="SELECT * FROM pedidostatus");
		$zebra = true;
		while($fetch=fetch($query) ){
			$edicao .= tag('tr',
				tag('td '.($zebra?'style="background-color:#eeeeee"':''), inputSimples("pedidostatus[{$fetch->id}][descricao]",$fetch->descricao,'',20,60))
				.tag('td '.($zebra?'style="background-color:#eeeeee"':''), checkbox("pedidostatusExcluir[{$fetch->id}][excluir]",'Sim','Excluir'))
			);
			$zebra = !$zebra;
		}
		$edicao .= '</table>';
		$edicao .= '<br /><br /><div class="box-block">';
		$edicao .= tag('h2', 'Cadastrar um novo Status');
		$edicao .= inputSimples('pedidostatusNovo[descricao]', '', 'Nome do Status',40,30);

		
		$edicao .= '</div>';

		$t->edicao = $edicao ;
		
		$this->montaMenu($t);
		$this->show($t);
	}
	
	public function vendastatus(){
		
		$this->filtraLogado();
		$this->filtraPermissao('vendastatus');

		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Status da Venda');

		if(request('action')=='salvar'){

			/*ATUALIZA AS MATERIAS PRIMAS ATUAIS*/
			foreach(is_array(@$_REQUEST['vendastatus'])?$_REQUEST['vendastatus']:array() as $id => $arrvendastatus){

				$vendastatus = new vendastatus($id);
				$vendastatus->set_by_array($arrvendastatus);

				//$vendastatus->setFile($_FILES["file_{$id}"]);

				$erro = array();
								
				/*if($vendastatus->validaDados($erro)){
					
					$vendastatus->salva();
				}
				else{
					$_SESSION['erro'] = join('<br />', $erro);
				}*/
				
				$vendastatus->salva();
			}

			/*CADASTRA UMA faixa NOVA*/
			if(is_array(@$_REQUEST['vendastatusNovo']) && $_REQUEST['vendastatusNovo']['descricao']!=''){

				$nomeStatus =  $_REQUEST['vendastatusNovo']['descricao'];
				
				$query = query($sql="INSERT INTO vendastatus (id, descricao, ordem) values('','$nomeStatus','')");
				
				// print $nomeStatus;

				/*$erro = array();
				if($vendastatus->validaDados($erro)){
					if(!$vendastatus->salva()){
						$_SESSION['erro'] = tag('p', 'Houve uma falha ao salvar os dados');
					}
				}
				else{
					$_SESSION['erro'] = join('<br />', $erro);
				}*/
				
				// $vendastatus->salva();
				unset($vendastatus);
			}

			/*EXCLUI AS faixaS MARCADAS*/
			foreach(is_array(@$_REQUEST['vendastatusExcluir'])?$_REQUEST['vendastatusExcluir']:array() as $id => $arrvendastatus){

				$vendastatus = new vendastatus($id);
				$vendastatus->exclui();

				unset($vendastatus);
			}

			//printr($_REQUEST);
		}

		$this->montaMenuSimples($t);

		$edicao = '';

		$edicao = inputHidden('id', '');

		$edicao .= '<table class="grid">';
		$edicao .= tag('tr',
				tag('th colspan="2"', 'Nome')
			);

		//$query = query();
		
		$query = query($sql="SELECT * FROM vendastatus");
		$zebra = true;
		while($fetch=fetch($query) ){
			$edicao .= tag('tr',
				tag('td '.($zebra?'style="background-color:#eeeeee"':''), inputSimples("vendastatus[{$fetch->id}][descricao]",$fetch->descricao,'',20,60))
				.tag('td '.($zebra?'style="background-color:#eeeeee"':''), checkbox("vendastatusExcluir[{$fetch->id}][excluir]",'Sim','Excluir'))
			);
			$zebra = !$zebra;
		}
		$edicao .= '</table>';
		$edicao .= '<br /><br /><div class="box-block">';
		$edicao .= tag('h2', 'Cadastrar um novo Status');
		$edicao .= inputSimples('vendastatusNovo[descricao]', '', 'Nome do Status',40,30);

		
		$edicao .= '</div>';

		$t->edicao = $edicao ;
		
		$this->montaMenu($t);
		$this->show($t);
	}


	public function destaqueflash(){

		$this->filtraLogado();
		$this->filtraPermissao('destaqueflash');

		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Banners - Home');
		$slidebanner= new slidebanner();
		
		$query= 'SELECT * FROM slidebanner WHERE tipo = "banner"';
		$result = results($query);
		
		$width_default  = 1920; //1216;
		$height_default = 470; //315; //635;

		if(request('action')=='salvar'){
			$erro ='';
			for($i=0 ; $i < sizeof($result) ; $i++){
				$slide_banner= new slidebanner();
				$slide_banner->id = $_REQUEST['item'.$i]['id'];
				$slide_banner->titulo = $_REQUEST['item'.$i]['titulo'];
				$slide_banner->texto = $_REQUEST['item'.$i]['texto'];
				$slide_banner->link   = $_REQUEST['item'.$i]['link'];
				$slide_banner->tipo   = 'banner';
				
				if($_FILES['src_'.$i]['name']!=""){
					list($width,$height) = getimagesize($_FILES['src_'.$i]['tmp_name']);
					if($width!=$width_default || $height!=$height_default){
						$erro .= tag('p','A imagem '.$_FILES['src_'.$i]['name'].' deve ter '.$width_default.'x'.$height_default.'px.');
					}else{				
						$file_name ='banner_'.$_FILES['src_'.$i]['name'];
						$slide_banner->imagem = $file_name;
						move_uploaded_file($_FILES['src_'.$i]['tmp_name'], 'img/banner/'.$file_name);
					}
				}
				$slide_banner->salva();
			
			}
		
			$_SESSION['sucesso'] = tag('p', 'Dados salvos com sucesso');
			if($erro!=''){
				$_SESSION['erro'] = tag('p',$erro);
			}
		}	
		
		$query= 'SELECT * FROM slidebanner WHERE tipo = "banner" ORDER BY id';
		$result = results($query);
		
		$slidebanner= new slidebanner();
		$edicao ='';
		$this->montaMenuSimples($t);
		
		$edicao .=tag('h5','Tamanho dos banners: <span style="color:red;">'.$width_default.'x'.$height_default.'px</span>');
		
		for($i=0 ; $i < sizeof($result) ; $i++){

			$slidebanner= $result[$i];
			
			$edicao .= '<div class="box-block">';
			$num = $i+1;
			$edicao .= tag('h4', 'Banner '.$num);

			$edicao .= tag('table style="width:100%;"',
				tag('tr',
					tag('td',
						'<input type=hidden name=item'.$i.'[id]   value='.$slidebanner->id.' />'
						.inputSimples('item'.$i.'[titulo]',$slidebanner->titulo,'Titulo',100,100)
						.inputSimples('item'.$i.'[texto]',$slidebanner->texto,'Texto',100,100)
						.inputSimples('item'.$i.'[link]',$slidebanner->link,'Link (ex.: http://www.site.com.br <em>OU</em> <br />mailto:examplo@examplo.com.br)',95,90)
					)
					.tag('td',
						tag('input type="file" name="src_'.$i.'"  onchange="uploadImg(this,'.$i.'),"')
						.'<br /><div class="block_img" >'
						.'<img src="'.PATH_SITE.'img/banner/'.$slidebanner->imagem.'" width="500px" alt="" />'
						.'</div>'
					)
				)
			);			
			$edicao .= '</div>';
		}
		
		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}
	
	public function banners(){
		$this->filtraLogado();
		$this->filtraPermissao('banners');

		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Banners');
		
		$edicao = '';


		$edicao .= tag('ul class="ul_lista"',
			 // tag('li',tag('a href="'.PATH_SITE.'admin.php/bannerpagina/bannersobre/Banner_Sobre"','Banner Sobre'))
			tag('li',tag('a href="'.PATH_SITE.'admin.php/bannerpagina/bannerprodutoamamos/Banner_Lancamentos"','Banner Lançamentos'))
			.tag('li',tag('a href="'.PATH_SITE.'admin.php/bannerpagina/bannerproduto/Banner_Produto"','Banner Produto'))
            .tag('li',tag('a href="'.PATH_SITE.'admin.php/bannerpagina/bannerpromocao/Banner_Promocoes"','Banner Promoções'))
			// .tag('li',tag('a href="'.PATH_SITE.'admin.php/bannerpagina/bannercatalogoonline/Banner_Catalogo_Online"','Banner Catálogo Online'))
			// .tag('li',tag('a href="'.PATH_SITE.'admin.php/bannerpagina/bannerblog/Banner_Blog"','Banner Blog'))
			// .tag('li',tag('a href="'.PATH_SITE.'admin.php/bannerpagina/bannersacc/Banner_Sacc"','Banner Sacc'))
			// .tag('li',tag('a href="'.PATH_SITE.'admin.php/bannerpagina/bannercontato/Banner_Contato"','Banner Contato'))
			// .tag('li',tag('a href="'.PATH_SITE.'admin.php/bannerpagina/bannerdetalhe/banner_Detalhe"','banner Detalhe'))
			// .tag('li',tag('a href="'.PATH_SITE.'admin.php/bannerpagina/bannercarrinho/Banner_Carrinho"','Banner Carrinho'))
			// .tag('li',tag('a href="'.PATH_SITE.'admin.php/bannerpagina/bannercadastro/Banner_Cadastro"','Banner Cadastro'))
			// .tag('li',tag('a href="'.PATH_SITE.'admin.php/bannerpagina/bannerhistorico/Banner_Historico(Orcamentos)"','Banner Histórico'))
		);
		
		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}
	
	public function bannerpagina($pagina, $titulo){
		$this->filtraLogado();
		$this->filtraPermissao('banners');
		
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Banner '.str_replace("_"," ",$titulo) );
		
		$slidebanner = new slidebanner(array('tipo'=>$pagina));
		
		$width  = 1920;
		$height = 470;
		if(request('action') == 'salvar'){
			$slidebanner->set_by_array($_REQUEST['slidebanner']);
			if( file_tratamento("imagem", $msg, $file) ){
				list($w, $h) = getimagesize($file['tmp_name']);
				if($w!=$width && $h!=$height){
					$_SESSION['erro'] = tag("p","A imagem deve ter {$width} x {$height} px");
				}else{
					if( isImagemJPG( $file['name'] ) || isImagemPNG( $file['name'] ) || isImagemGIF( $file['name'] ) ){
				
						$slidebanner->salva();
						$slidebanner->imagem = $slidebanner->id."_".$file['name'];
						move_uploaded_file($file['tmp_name'] , "img/banner/{$slidebanner->id}_".$file['name']);
						$slidebanner->atualiza();
						$_SESSION['sucesso'] = tag('p','Banner salvo com sucesso.');					
					}else{
						$_SESSION['erro'] = tag('p','Insira uma imagem (jpg, png ou gif).');
					}
				}
			}else{
				$_SESSION['erro'] = $msg;
			}
		}
		
		$edicao = '';
		
		$edicao .= tag('table class="box_ferramentas"',
					tag('tr',
						tag('td style="text-align:left;"',"<input type='button' value='Salvar' class='bt_afirmar btn' onclick='enviar(\"salvar\");' />"
						."<a href='".PATH_SITE."admin.php/banners/'><span class='bt_negar btn'>Voltar</span></a>")
					)
				);
					
		$edicao .= tag("h2","Imagem");
		$edicao .= tag("p","- A imagem precisa ter {$width} x {$height} px ; <br /> - A imagem precisa ser jpg, png ou gif.");
		$edicao .= inputHidden("slidebanner[tipo]",$pagina);
		$edicao .= "<br /><input type='file' name='imagem' />";
		
		if($slidebanner->id){
			$edicao .= "<br /><img src='".PATH_SITE."img/banner/{$slidebanner->imagem}' width='600px' />";
		}
		
		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}

	public function labels(){

		$this->filtraLogado();
		$this->filtraPermissao('labels');

		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Tradução de Labels');

		if(request('action')=='salvar'){

			/*ATUALIZA AS labelURACOES ATUAIS*/
			foreach(is_array(@$_REQUEST['label'])?$_REQUEST['label']:array() as $id => $arrlabel){

				$label = new label($id);
				$label->set_by_array($arrlabel);
				$label->salva();

				unset($label);
			}

			/*CADASTRA UMA label NOVA*/
			if(is_array(@$_REQUEST['labelNova']) && $_REQUEST['labelNova']['valor']!=''){

				$label = new label();
				$label->set_by_array($_REQUEST['labelNova']);
				$label->salva();

			}

			/*EXCLUI AS labelS MARCADAS*/
			foreach(is_array(@$_REQUEST['labelExcluir'])?$_REQUEST['labelExcluir']:array() as $id => $arrlabel){

				$label = new label($id);
				$label->exclui();

				unset($label);
			}

			$_SESSION['sucesso'] = tag('p', 'Seus dados foram salvos');
			//printr($_REQUEST);
		}

		$edicao = '';

		$this->montaMenuSimples($t);

		$edicao .= tag('div class="box-info"',
							'
							As informações aqui relacionadas são utilizadas como parametros dentro do site
							');

		$edicao .= '<table class="grid">';

		$edicao .= tag('tr',
							tag('th', 'Chave interna')
							.tag('th', bandeira_br().'Valor')
							.($this->config->HABILITA_ESPANHOL=='S'?tag('th', bandeira_es().'Valor'):'')
							.($this->config->HABILITA_INGLES=='S'?tag('th', bandeira_in().'Valor'):'')
						);

		$querylabel = query($sql="SELECT * FROM label ORDER BY chave");
		while($fetchlabel=fetch($querylabel) ){
			$edicao .= tag('tr',
							tag('td', $fetchlabel->chave)
							.tag('td', inputSimples("label[{$fetchlabel->id}][valor]", $fetchlabel->valor, '', 30, 200))
							.($this->config->HABILITA_ESPANHOL=='S'?tag('td', inputSimples("label[{$fetchlabel->id}][valor_es]", $fetchlabel->valor_es, '', 30, 200)):'')
							.($this->config->HABILITA_INGLES=='S'?tag('td', inputSimples("label[{$fetchlabel->id}][valor_in]", $fetchlabel->valor_in, '', 30, 200)):'')
							.tag('td', '')
						);
		}


		$edicao .= '</table>';
		$edicao .= str_repeat(tag('br'), 3);
		$edicao .= tag('h3', 'Novo Label de Tradução');

		$edicao .= tag('div class="box-info"',
							'
							Uso interno da equipe de desenvolvimento
							');

		//$edicao .= inputSimples('labelNova[grupo]', '', 'Grupo', 45, 50);
		$edicao .= inputSimples('labelNova[chave]', '', 'Chave interna', 30, 50);
		$edicao .= inputSimples('labelNova[valor]', '', bandeira_br().'Valor:', 30, 50);
		if($this->config->HABILITA_ESPANHOL=='S'){
			$edicao .= inputSimples('labelNova[valor_es]', '', bandeira_es().'Valor:', 30, 50);
		}
		if($this->config->HABILITA_INGLES=='S'){
			$edicao .= inputSimples('labelNova[valor_in]', '', bandeira_in().'Valor:', 30, 50);
		}

		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
	}

	// Exportar para o excel
	public function expexcel(){
	
		$sql = $_SESSION['EXP_EXCEL_SQL'] ;
		$filename = $_SESSION['EXP_EXCEL_FILENAME'] ;
	
		$gridexcel = new gridexcel();
		
		$gridexcel->filtro = $_SESSION['EXP_EXCEL_FILTRO'];
		$gridexcel->sql = $sql;
		
		header("Content-type: application/csv");
		header("Content-Disposition: attachment; filename={$filename}.csv");
		header("Pragma: no-cache");
		header("Expires: 0");

		// printr($_REQUEST);
		
		print $gridexcel->render();
	
	}
	
	public function	ajax_get_sugestao_preco(){
		$return = 0;
		
		//
		$item_id = intval(request('item_id'));
		$gravacao_id = intval(request('gravacao_id'));
		
		$qtd = intval(request('qtd'));
		
		$fetchPreco = fetch(query("SELECT ifnull(preco,0) preco from preco WHERE item_id = {$item_id} AND {$qtd} BETWEEN qtd_1 AND qtd_2 "));
		$fetchPrecoGravacao = fetch(query("SELECT ifnull(preco,0) preco from precogravacao WHERE item_id = {$item_id} AND gravacao_id = {$gravacao_id} AND {$qtd} BETWEEN qtd_1 AND qtd_2 "));
		
		$return = floatval(floatval(@$fetchPreco->preco)+floatval(@$fetchPrecoGravacao->preco));
		print money($return);
	}

	public function ajax_get_proposta(){
		$proposta = new proposta(intval(request('proposta_id')));
		if($proposta->id)
		{
			// printr($proposta);
			print json_encode(unserialize($proposta->info));
		}
	}

	public function boxSenha($cadastro){

		$return = '';

		$return .= inputSimples('cadastro[login]', $cadastro->login, 'login:', 40, 50);
		if($cadastro->id){
			$return .= inputSimples('senha_nova', '', 'senha:', 40, 50);
			$return .= tag('br ').tag('input onclick="document.getElementById(\'senha_nova\').disabled=!this.checked" type="checkbox" value="1" name="alterar_senha"').' * marque aqui para alterar a senha já existente';
			$return .= tag('script','document.getElementById(\'senha_nova\').disabled=true;');
		}
		else {
			$return .= inputSimples('senha_nova', $cadastro->senha, 'senha:', 40, 50);
		}

		//$return .= tag('pp('senha_nova', $cadastro->senha, 'senha:', 40, 50);

		return $return;
	}

	public function boxVendedor($cadastro){
		$return = '';

		if($_SESSION['CADASTRO']->tipocadastro_id==tipocadastro::getId('VENDEDOR')){
			$return .= inputHidden('cadastro[cadastro_id]', $cadastro->cadastro_id?$cadastro->cadastro_id:decode($_SESSION['CADASTRO']->id));
		}
		else{
			$return .= select('cadastro[cadastro_id]', $cadastro->cadastro_id, 'Atendimento (representante):', cadastro::opcoesVendedor());
		}

		return $return;
	}

	private function boxEndereco($cadastro, $style=''){

		return tag('div class="box-block" '.$style,
					tag('h2', 'Endereço')
					.inputSimples('cadastro[cep]', $cadastro->cep, 'CEP:', 10, 10)
					.inputSimples('cadastro[logradouro]', $cadastro->logradouro, 'Logradouro:', 45, 50)
					.inputSimples('cadastro[numero]', $cadastro->numero, 'Numero:', 15, 15)
					.inputSimples('cadastro[complemento]', $cadastro->complemento, 'Complemento:', 30, 30)
					.inputSimples('cadastro[bairro]', $cadastro->bairro, 'Bairro:', 30, 30)
					.inputSimples('cadastro[cidade]', $cadastro->cidade, 'Cidade:', 30, 30)
					.inputSimples('cadastro[uf]', $cadastro->uf, 'Estado:', 2, 2)
					.tag('br clear="all"')
		);
	}

	private function boxEnderecoSite($cadastro, $style=''){

		return 	tag('br').tag('b', 'Endereço/loja')
					.tag('br')
					.inputSimples('cadastro[mapa_cep]', $cadastro->mapa_cep, 'CEP:', 13, 10)
					.inputSimples('cadastro[mapa_logradouro]', $cadastro->mapa_logradouro, 'Logradouro:', 45, 50)
					.inputSimples('cadastro[mapa_numero]', $cadastro->mapa_numero, 'Numero:', 15, 15)
					.inputSimples('cadastro[mapa_complemento]', $cadastro->mapa_complemento, 'Complemento:', 30, 30)
					.inputSimples('cadastro[mapa_bairro]', $cadastro->mapa_bairro, 'Bairro:', 30, 30)
					.inputSimples('cadastro[mapa_cidade]', $cadastro->mapa_cidade, 'Cidade:', 30, 30)
					.selectEstado('cadastro[mapa_uf]',$cadastro->mapa_uf, 'Escolha um estado:') 
					//.inputSimples('cadastro[mapa_uf]', $cadastro->mapa_uf, 'Estado:', 4, 4)
					.tag('br clear="all"');
	}

	public function boxContato($cadastro, $style=''){

        $ret = '';

        $ret .= '<div class="well">';
        $ret .= tag('legend', 'Contato');
        $ret .= inputSimples('cadastro[email]', $cadastro->email, 'Email:', 45, 100);
        $ret .= inputSimples('cadastro[fone_res]', $cadastro->fone_res, 'Tel. Comercial:');
        $ret .= inputSimples('cadastro[fone_cel]', $cadastro->fone_cel, 'Tel. Celular:');
        $ret .= inputSimples('cadastro[skype]', $cadastro->skype, 'Skype:');
        $ret .= '</div>';

        return $ret;

		return tag('div class="well" '.$style,
					tag('legend', 'Contato')
					.inputSimples('cadastro[email]', $cadastro->email, 'Email:', 45, 100)
					.inputSimples('cadastro[fone_res]', $cadastro->fone_res, 'Tel. Comercial:')
					//.tag('div class="float-left"', inputSimples('cadastro[fone_res]', $cadastro->fone_res, 'Tel. Residencial:', 12, 50))
					.inputSimples('cadastro[fone_cel]', $cadastro->fone_cel, 'Tel. Celular:')
					//.tag('div class="float-left"', inputSimples('cadastro[fone_nextel]', $cadastro->fone_nextel, 'Nextel:', 12, 50))
				);
	}

	public function boxDocsPessoais($cadastro, $style=''){

		return tag('div class="well" '.$style,
					tag('legend', 'Documentos pessoais')
					.inputSimples('cadastro[rg]', $cadastro->rg, 'RG:', 30, 30)
					.inputSimples('cadastro[rg_emissor]', $cadastro->rg_emissor, 'Orgao emissor RG:', 10, 30)
					.inputSimples('cadastro[cpf]', $cadastro->cpf, 'CPF:', 20, 20)
					.tag('br clear="all"')
				);
	}

	public function boxExpExcel($sql,$filename,$filtro){
	
		$_SESSION['EXP_EXCEL_SQL'] = $sql;
		$_SESSION['EXP_EXCEL_FILENAME'] = $filename;
		$_SESSION['EXP_EXCEL_FILTRO'] = $filtro;
		// return tag('div class="box-block" style="text-align:right" ', tag('a href="'.$this->montaLinkAdmin('expexcel').'/filtro[nome]=fabio"','Exportar excel'));
		
		return js(
			"
			function expExcel(btn)
			{
				
				btn.form.action='".PATH_SITE."admin.php/expexcel';
				btn.form.setAttribute('action','".PATH_SITE."admin.php/expexcel')
				btn.form.submit();
				
				btn.form.setAttribute('action','')
				
			}
			"
		)
        .'<button type="button" class="btn btn-default" onclick="expExcel(this)"><span class="glyphicon glyphicon-download-alt"></span> Exportar Excel (CSV)</button>' ;
		// .tag('div xclass="box-block" style="float:right;text-align:right" ',tag('input type="button" class="bt_afirmar btn btn-primary" value="Exportar Excel" onclick="expExcel(this)"'))
		;
	}
	
	private function afterSave($next, $method, $obj=null){
		if(trim($next)!=''){
			$_REQUEST['action']='';
			switch($next){
				case 'sair':
					// Se for pop-up, carrega template de pop-up
					if(request('pop')){
						?>
						<script>
							try {

								// Caso exista a janela anterior
								if(parent){

									<?php
									if(array_key_exists('erro', $_SESSION)){
										?>
										parent.opener.document.getElementById('erro_msg').innerHTML = ('<?php print $_SESSION['erro'] ?>');
										parent.opener.document.getElementById('erro_msg').style.display = '';
										<?php
										unset($_SESSION['erro']);
									}

									if(array_key_exists('sucesso', $_SESSION)){
										?>
										parent.opener.document.getElementById('sucesso_msg').innerHTML = ('<?php print $_SESSION['sucesso'] ?>');
										parent.opener.document.getElementById('sucesso_msg').style.display = '';
										<?php
										unset($_SESSION['sucesso']);
									}
									?>
								}
							}
							catch(e){

							}

							// Fecha janela pop-up
							self.close();

						</script>
						<?php
						// $t = new TemplateAdminPop('admin/tpl.admin-cadastro-generico.html');
					}
					else {
						$this->$method();
					}
				break;
				case 'novo':
					$_REQUEST['id'] = '0';
					$_REQUEST['action'] = 'editar';
					unset($_REQUEST['cadastro']);
					$this->$method();
				break;
				case 'avancar':
					$_REQUEST['id'] = $obj->get_next_id();
					$_REQUEST['action']='editar';
					$this->$method();
				break;
				case 'retroceder':
					$_REQUEST['id'] = $obj->get_last_id();
					$_REQUEST['action']='editar';
					$this->$method();
				break;
				default :
					// Se for pop-up, carrega template de pop-up
					if(request('pop')){
						?>
						<script>
							try {

								// Caso exista a janela anterior
								if(parent){

									<?php
									if(array_key_exists('erro', $_SESSION)){
										?>
										parent.opener.document.getElementById('erro_msg').innerHTML = ('<?php print $_SESSION['erro'] ?>');
										parent.opener.document.getElementById('erro_msg').style.display = '';
										<?php
										unset($_SESSION['erro']);
									}

									if(array_key_exists('sucesso', $_SESSION)){
										?>
										parent.opener.document.getElementById('sucesso_msg').innerHTML = ('<?php print $_SESSION['sucesso'] ?>');
										parent.opener.document.getElementById('sucesso_msg').style.display = '';
										<?php
										unset($_SESSION['sucesso']);
									}
									?>
								}
							}
							catch(e){

							}

							// Fecha janela pop-up
							self.close();

						</script>
						<?php
						// $t = new TemplateAdminPop('admin/tpl.admin-cadastro-generico.html');
					}
					else {
						$this->$method();
					}
				;
			}
			return;
		}
	}


	private function filtraLogado(){
		if(!$this->isLogado()){
			die($this->login());
		}
	}

	private function filtraPermissao($arquivo){

        if(is_object($arquivo)){
            $modulo = $arquivo;
        }
        else {

            $modulo = new modulo(array(
                    'arquivo' => $arquivo
                ,'st_ativo' => 'S'
                )
            );

            if(!$modulo->id){
                $modulo = new modulo(array(
                        'id' => $arquivo
                    ,'st_ativo' => 'S'
                    )
                );
            }
        }

        $permissao = new permissao(array(
                'cadastro_id'=>intval(decode($_SESSION['CADASTRO']->id))
                ,'modulo_id'=>intval($modulo->id)
            )
        );

        if(!$permissao->id){
            echo tag('h1','Seu usuário não tem acesso a esse módulo');
            echo tag('a href="javascript:history.back()"','voltar');
            die();
        }
	}

	private function filtraToken(){
		if(!token_ok())die();
	}

	private function isLogado(){

		$cadastro = @$_SESSION['CADASTRO'] ;

		if($cadastro&&$cadastro->id){
			$id = intval(decode($cadastro->id));
			$rows = rows(query("SELECT * FROM cadastro WHERE st_ativo = 'S' AND id = {$id} AND tipocadastro_id IN (".tipocadastro::getId('ADMINISTRATIVO').",".tipocadastro::getId('VENDEDOR').")"));
			return $rows>0;
		}
		return false;
	}

	public function get_item($referencia){

		$referencia = urldecode($referencia);

		$item = new item(array('referencia'=>$referencia));

		if($this->config->HABILITA_COR=='S'){
			if($item->id){
				$item->html_cor = select('cor_id','','Cor',item::opcoesCor($item->id));
			}
		}
		if($this->config->HABILITA_GRAVACAO=='S'){
			$item->html_gravacao = select('gravacao_id','','Gravacao',gravacao::opcoesByItem($item));
		}
		if($this->config->HABILITA_MATERIA_PRIMA=='S'){
			$item->html_materia_prima = select('materia_prima_id','','Materia prima',materia_prima::opcoesByItem($item));
		}
		
		echo json_encode($item);
	}

	private function setLocation($url){
		// header('location:'.PATH_SITE."admin.php/".$url);
		// die();
		if(!headers_sent()){
			header('location:'.PATH_SITE."admin.php/".$url);
		}
		else {
			?>
			<script>
				window.location = "<?php echo PATH_SITE."admin.php/".$url ; ?>";
			</script>
			<?php
		}
		die();
	}

	private function montaLinkAdmin($url){
		return PATH_SITE.'admin.php/'.$url;
	}
	
	public function verpedido(){
		
		// $t = new Template
		
		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
		$t->h1 = h1('Pedido');
		
		$edicao = '';
		
		$proposta = new proposta(17);
		$config = new config();
		$pedido = new pedido($proposta->pedido_id);
		$cadastro = new cadastro($pedido->cadastro_id);
		$vendedor = new cadastro($pedidovendedor->id);
	
		$info = (object) unserialize($proposta->info);
			
		//printr($info);
		//printr($cadastro);
		
		$edicao .= '<div class="box-block" style="float:left; height:230px; width:48%;">';
		$edicao .= '<h2>Dados da Empresa</h2>';
		$edicao .= '<table width=100%">';
		$edicao .= '<tr><td>';
		$edicao .= '<b>EMPRESA:</b>';
		$edicao .= '</td><td>';
		$edicao .= $config->EMPRESA;
		$edicao .= '</td></tr>';
		$edicao .= '<tr><td>';
		$edicao .= '<b>ENDEREÇO:</b>';
		$edicao .= '</td><td>';
		$edicao .= $config->LOGRADOURO.','.$config->NUMERO;
		$edicao .= '</td></tr>';
		$edicao .= '<tr><td>';
		$edicao .= '<b>CEP:</b>';
		$edicao .= '</td><td>';
		$edicao .= $config->CEP;
		$edicao .= '</td></tr>';
		$edicao .= '<tr><td>';
		$edicao .= '<b>CIDADE:</b>';
		$edicao .= '</td><td>';
		$edicao .= $config->CIDADE;
		$edicao .= '</td></tr>';
		$edicao .= '<tr><td>';
		$edicao .= '<b>UF:</b>';
		$edicao .= '</td><td>';
		$edicao .= $config->ESTADO;
		$edicao .= '</td></tr>';
		$edicao .= '<tr><td>';
		$edicao .= '<b>TEL:</b>';
		$edicao .= '</td><td>';
		$edicao .= $config->TELEFONE;
		$edicao .= '</td></tr>';
		$edicao .= '<tr><td>';
		$edicao .= '<b>FAX:</b>';
		$edicao .= '</td><td>';
		$edicao .= $config->FAX;
		$edicao .= '</td></tr>';
		$edicao .= '<tr><td>';
		$edicao .= '<b>CNPJ:</b>';
		$edicao .= '</td><td>';
		$edicao .= $config->CNPJ;
		$edicao .= '</td></tr>';
		$edicao .= '<tr><td>';
		$edicao .= '<b>IE:</b>';
		$edicao .= '</td><td>';
		$edicao .= $config->IE;
		$edicao .= '</td></tr>';
		$edicao .= '<tr><td>';
		$edicao .= '<b>CONTATO:</b>';
		$edicao .= '</td><td>';
		$edicao .= $config->CONTATO;
		$edicao .= '</td></tr>';
		$edicao .= '<tr><td>';
		$edicao .= '<b>EMAIL:</b>';
		$edicao .= '</td><td>';
		$edicao .= $config->EMAIL_CONTATO;
		$edicao .= '</td></tr>';
		$edicao .= '</table>';
		$edicao .= '</div>';
		
		
		$edicao .= '<div class="box-block" style="float:right; height:230px; margin-right:5px; width:48%;">';
		$edicao .= '<h2>Dados do Cliente</h2>';
		$edicao .= '<table width=100%">';
		$edicao .= '<tr><td>';
		$edicao .= '<b>EMPRESA:</b>';
		$edicao .= '</td><td>';
		$edicao .= $cadastro->nome;
		$edicao .= '</td></tr>';
		$edicao .= '<tr><td>';
		$edicao .= '<b>CNPJ/CPF:</b>';
		$edicao .= '</td><td>';
		$edicao .= $cadastro->cnpj;
		$edicao .= '/';
		$edicao .= $cadastro->cpf;
		$edicao .= '</td></tr>';
		$edicao .= '<tr><td>';
		$edicao .= '<b>CEP:</b>';
		$edicao .= '</td><td>';
		$edicao .= $cadastro->logradouro.','.$cadastro->numero.'-'.$cadastro->complemento;
		$edicao .= '</td></tr>';
		$edicao .= '<tr><td>';
		$edicao .= '<b>BAIRRO:</b>';
		$edicao .= '</td><td>';
		$edicao .= $cadastro->bairro;
		$edicao .= '</td></tr>';
		$edicao .= '</td></tr>';
		$edicao .= '<tr><td>';
		$edicao .= '<b>CEP:</b>';
		$edicao .= '</td><td>';
		$edicao .= $cadastro->cep;
		$edicao .= '</td></tr>';
		$edicao .= '<tr><td>';
		$edicao .= '<b>CIDADE:</b>';
		$edicao .= '</td><td>';
		$edicao .= $cadastro->cidade;
		$edicao .= '</td></tr>';
		$edicao .= '<tr><td>';
		$edicao .= '<b>UF:</b>';
		$edicao .= '</td><td>';
		$edicao .= $cadastro->uf;
		$edicao .= '</td></tr>';
		$edicao .= '<tr><td>';
		$edicao .= '<b>TEL:</b>';
		$edicao .= '</td><td>';
		$edicao .= $cadastro->fone_com;
		$edicao .= '</td></tr>';
		$edicao .= '<tr><td>';
		$edicao .= '<b>EMAIL:</b>';
		$edicao .= '</td><td>';
		$edicao .= $cadastro->email;
		$edicao .= '</td></tr>';
		$edicao .= '</table>';
		$edicao .= '</div>';
		$edicao .= '<br clear="all">';
		$edicao .= '<br clear="all">';
		
		// loop nos itens
		$edicao .= '<table class="grid" width="100%">';
		$edicao .= '<tr>';
		$edicao .= '<th>';
		$edicao .= '<b>ITEM</b>';
		$edicao .= '</th>';
		$edicao .= '<th>';
		$edicao .= '<b>PRODUTO</b>';
		$edicao .= '</th>';
		$edicao .= '<th>';
		$edicao .= '<b>DESCRIÇÃO</b>';
		$edicao .= '</th>';
		$edicao .= '<th>';
		$edicao .= '<b>QTDE</b>';
		$edicao .= '</th>';
		$edicao .= '<th>';
		$edicao .= '<b>UNITÁRIO</b>';
		$edicao .= '</th>';
		$edicao .= '<th>';
		$edicao .= '<b>TOTAL</b>';
		$edicao .= '</th>';
		$edicao .= '</tr>';
		$cont = 1;
		foreach($info->item as $item)
		{
		
			$item = (object) $item;
			
			
			//printr ($item);
			
			$edicao .= '<tr>';
				$edicao .= '<td>';
				$edicao .= $cont;
				$edicao .= '</td>';
				$edicao .= '<td>';
				$edicao .= $item->referencia;
				$edicao .= '</td>';
				$edicao .= '<td>';
				$edicao .= $item->nome;
				$edicao .= '</td>';
				$edicao .= '<td>';
				$edicao .= $item->item_qtd;
				$edicao .= '</td>';
				$edicao .= '<td>';
				$edicao .= $item->preco;
				$edicao .= '</td>';
				$edicao .= '<td>';
				$edicao .= $item->sub_total;
				$edicao .= '</td>';
			$edicao .= '</tr>';	
			//printr($item);
			
			$cont++;
		}
		
		$edicao .= '</table>';
		$edicao .= '<br>';
		$edicao .= '<br>';
		
		//printr($info);
		
		$edicao .= '<table>';
		$edicao .= '<tr>';
		$edicao .= '<td>';
		$edicao .= '<b>LOCAL DE ENTREGA:</b>';
		$edicao .= '</td>';
		$edicao .= '<td>';
		$edicao .= $info->local_entrega;
		$edicao .= '</td>';
		$edicao .= '</tr>';
		$edicao .= '<tr>';
		$edicao .= '<td>';
		$edicao .= '<b>LOCAL DE COBRANÇA:</b>';
		$edicao .= '</td>';
		$edicao .= '<td>';
		$edicao .= $info->local_cobranca;
		$edicao .= '</td>';
		$edicao .= '</tr>';
		$edicao .= '<tr>';
		$edicao .= '<td>';
		$edicao .= '<b>MODALIDADE DE FRETE:</b>';
		$edicao .= '</td>';
		$edicao .= '<td>';
		$edicao .= $info->frete;
		$edicao .= '</td>';
		$edicao .= '</tr>';
		$edicao .= '<tr>';
		$edicao .= '<td>';
		$edicao .= '<b>CONDIÇÃO DE PAGAMENTO:</b>';
		$edicao .= '</td>';
		$edicao .= '<td>';
		$edicao .= $info->forma_pagamento;
		$edicao .= '</td>';
		$edicao .= '</tr>';
		$edicao .= '<tr>';
		$edicao .= '<td>';
		$edicao .= '<b>TOTAL DAS MERCADORIAS:</b>';
		$edicao .= '</td>';
		$edicao .= '<td>';
		$edicao .= $info->total;
		$edicao .= '</td>';
		$edicao .= '</tr>';
		$edicao .= '<tr>';
		$edicao .= '<td>';
		$edicao .= '<b>TOTAL DE ICMS:</b>';
		$edicao .= '</td>';
		$edicao .= '<td>';
		$edicao .= $info->total_icms;
		$edicao .= '</td>';
		$edicao .= '</tr>';
		$edicao .= '<tr>';
		$edicao .= '<td>';
		$edicao .= '<b>TOTAL COM IMPOSTOS:</b>';
		$edicao .= '</td>';
		$edicao .= '<td>';
		$edicao .= $info->total_impostos;
		$edicao .= '</td>';
		$edicao .= '</tr>';
		$edicao .= '<tr>';
		$edicao .= '<td>';
		$edicao .= '<b>VENDEDOR RESPONSÁVEL:</b>';
		$edicao .= '</td>';
		$edicao .= '<td>';
		$edicao .= $info->vendedor;
		$edicao .= '</td>';
		$edicao .= '</tr>';
		$edicao .= '</table>';
		
		// printr($proposta);
		
		$t->edicao = $edicao;

		$this->montaMenu($t);
		$this->show($t);
		
	}

	public function popupFornecedorObs(){
	
		$id = intval(request('id'));
		$item = new item($id);
		if($item->id){
		
			$output = '<style>';
			$output .= '* {font-family:arial;font-size:12px}';
			$output .= 'table {border:1px solid #eeeeeee}';
			$output .= '</style>';
		
			$output .= "<div>";
			$output .= "		Item: {$item->nome} - {$item->referencia}<br />";
			$output .= "		<hr />";
			$output .= "		<table class='grid t-fornecedor-orc'>";
			$output .= "			<tr>";
			$output .= "				<th>Fornecedor</th>";
			$output .= "				<th>Código</th>";
			$output .= "				<th>Preço</th>";
			$output .= "				<th>Data</th>";
			$output .= "			</tr>";
			$output .= "			<tr>";
			$output .= "				<td><input type='text' value='{$item->fornecedor_1}' name='fornecedor_1_{cont}' /></td>";
			$output .= "				<td><input type='text' value='{$item->codigo_1}' name='codigo_1_{cont}' /></td>";
			$output .= "				<td><input type='text' value='{$item->preco_1}' name='preco_1_{cont}' onkeypress='return formataMoeda(this,event)'/></td>";
			$output .= "				<td><input type='text' value='{$item->data_1}' name='data_1_{cont}' /></td>";
			$output .= "			</tr>";
			$output .= "			<tr>";
			$output .= "				<td><input type='text' value='{$item->fornecedor_2}' name='fornecedor_2_{cont}' /></td>";
			$output .= "				<td><input type='text' value='{$item->codigo_2}' name='codigo_2_{cont}' /></td>";
			$output .= "				<td><input type='text' value='{$item->preco_2}' name='preco_2_{cont}' onkeypress='return formataMoeda(this,event)'/></td>";
			$output .= "				<td><input type='text' value='{$item->data_2}' name='data_2_{cont}' /></td>";
			$output .= "			</tr>";
			$output .= "			<tr>";
			$output .= "				<td><input type='text' value='{$item->fornecedor_3}' name='fornecedor_3_{cont}' /></td>";
			$output .= "				<td><input type='text' value='{$item->codigo_3}' name='codigo_3_{cont}' /></td>";
			$output .= "				<td><input type='text' value='{$item->preco_3}' name='preco_3_{cont}' onkeypress='return formataMoeda(this,event)'/></td>";
			$output .= "				<td><input type='text' value='{$item->data_3}' name='data_3_{cont}' /></td>";
			$output .= "			</tr>";
			$output .= "			<tr>";
			/*$output .= "				<td colspan="4">";
			$output .= "					<a href="javascript:atualizaObservacao({cont})">Salvar</a>";
			$output .= "				</td>";
			$output .= "			<tr>";*/
			$output .= "		</table>";
			$output .= "</div>";
		
		
			print $output ;
			
		}
	
	}
	
}

new UrlHandler(new admin());

//@mysql_close();

debug_info($script_start);
