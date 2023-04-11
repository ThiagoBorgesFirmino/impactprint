<?php

// Classe com controles gerais para controle do projeto
class projeto {

	// Retorna itens mais vendidos
	static function getItensMaisVendidos($limit=10){

		$return = array();

		$sql =
		"
		SELECT
			SQL_CACHE
			DISTINCT
			item.id
			,item.st_ativo
			,item.referencia
			,item.nome
			,item.marca_id
			,item.preco
			,item.preco_de
			,item.qtd_estoque
			,item.imagem
			,item.splash_id
			,item.carimbo_id
			,item.carimbo_txt
			,item.itemopiniao_avaliacao_media
			,item.itemopiniao_qtd
			,item.qtd_vendas qtd_venda
		FROM
			item
		INNER JOIN itemcategoria ON (
			itemcategoria.item_id = item.id
		)
		INNER JOIN categoria ON (
			categoria.id = itemcategoria.categoria_id
		AND categoria.st_ativo = 'S'
		)
		INNER JOIN itemsite ON (
			itemsite.item_id = item.id
		AND itemsite.site_id = ".projeto::getSiteAtual()."
		)
		WHERE
			item.st_ativo = 'S'
		AND item.preco > 0
		AND item.qtd_estoque > 0
		AND item.imagem <> ''
		ORDER BY
			item.qtd_vendas DESC
		LIMIT
			{$limit}
		" ;

		// print $sql;

		$query = query($sql);

		while($fetch=fetch($query)){
			$item = new item();
			$item->load_by_fetch($fetch);
			$item->qtd_venda = ($fetch->qtd_venda);
			$return[] = $item;
		}


		// printr($return);

		return $return;

	}

	static function getImgLogo(){
		$sql = "
		SELECT * FROM slidebanner 	
		INNER JOIN slidebannersite ON (
			slidebannersite.slidebanner_id = slidebanner.id
		AND slidebannersite.site_id = ".projeto::getSiteAtual()."
		)
		WHERE nome = 'logo' AND tipo_banner = 'imagem' AND st_ativo = 'S' ";
		$fetch = fetch(query($sql));
		return $fetch->imagem;
	}

	// Retorna itens em destaque
	static function getItensEmDestaque($cat_id=0){

		// variavel de retorno
		$return = array();

		// Where da marca
		$whereMarca = "";

		// Checa se existem marcas na pesquisa
		if(request('marca')&&is_array(request('marca'))){
			foreach(request('marca') as $marca_str){
				$marca = new marca(array('nome'=>addslashes(urldecode($marca_str))));
				if($marca->id){
					if($whereMarca!=""){
						$whereMarca .= " OR ";
					}
					$whereMarca .= " item.marca_id = {$marca->id} "  ;
				}
			}
		}

		$sql =
			"
			SELECT
				DISTINCT
				item.id
				,item.st_ativo
				,item.st_kit
				,item.referencia
				,item.nome
				,item.marca_id
				,item.preco
				,item.preco_de
				,item.qtd_estoque
				,item.imagem
				,item.splash_id
				,item.carimbo_id
				,item.carimbo_txt
				,item.itemopiniao_avaliacao_media
				,item.itemopiniao_qtd
			FROM
				item
			INNER JOIN itemcategoria ON (
				itemcategoria.item_id = item.id
				".($cat_id >0?" AND item.id = itemcategoria.item_id AND itemcategoria.categoria_id = {$cat_id}":"")."
			)
			INNER JOIN categoria ON (
				categoria.id = itemcategoria.categoria_id
			AND itemcategoria.item_id = item.id
			AND categoria.st_ativo = 'S'
			)
			INNER JOIN itemsite ON (
				itemsite.item_id = item.id
			AND itemsite.site_id = ".projeto::getSiteAtual()."
			)
			WHERE
				item.st_ativo = 'S' -- Apenas itens ativos
			AND item.st_destaque = 'S'
			AND item.preco > 0
			AND item.qtd_estoque > 0
			AND item.itemsku_id IS NULL -- Apenas itens principais

			AND item.imagem <> ''
			".($whereMarca!='' ? "AND ( {$whereMarca} ) ":"")."
			GROUP BY
				item.id
			ORDER BY
				RAND()
			LIMIT
				100

			" ;

		// LIMIT
		//{$limit}
		//printr($cat_id);
		// printr( $sql);
		$query = query($sql);

		while($fetch=fetch($query)){
			$item = new item();
			$item->load_by_fetch($fetch);
			$return[] = $item;
		}

		return $return;

	}

	// Retorna ultimos produtos vistos
	static function getItensUltimosVistos($limit=3){

		$ret = array();

		// Verifica se existe a chave na sessao, se nao houver, cria
		if(!array_key_exists('ITENS_VISTOS', $_SESSION)){
			return $ret;
		}

		$reverse = array_reverse($_SESSION['ITENS_VISTOS']);
		$n = sizeof($reverse);

		// for($i=0;($i<$limit)&&($i<$n);$i++){
			// $ret[] = $reverse[$i];
		// }
		for($i=0;($i<$n);$i++){
			$ret[] = $reverse[$i];
		}

		return $ret;

	}

	// Carrega historico de vizuliacao
	static function getProdutosVistos($fetch){

		$p = new Template('tpl.part-prod-vistos.html');

		$item = new item($fetch->item_id);
		$p->list_item = $item;
		$p->path_p = PATH_SITE;
		$p->parseBlock('BLOCK_VISTOS_ITEM', true);

		return $p->getContent();

	}


	// Adiciona um item visto na sessao
	static function addItemVisto($item, $logado=0){

		// Verifica se existe a chave na sessao, se nao houver, cria
		if(!array_key_exists('ITENS_VISTOS', $_SESSION)){
			$_SESSION['ITENS_VISTOS'] = array();
		}

		// Verifica se o item ja nao existe na sessao
		$n = sizeof($_SESSION['ITENS_VISTOS']);

		for($i=0;($i<$n);$i++){
			if($_SESSION['ITENS_VISTOS'][$i]->id == $item->id){
				return;
			}
		}
		$_SESSION['ITENS_VISTOS'][] = $item;

		//salva visto para historico
		if($logado==1){
			$itemvistos = new itemvistos();
			$itemvistos->cadastro_id = $_SESSION['CADASTRO']->id;
			$itemvistos->item_id = $item->id;
			$itemvistos->salva();
		}
	}

	// Retorna paginas especiais
	static function getPaginasEspeciais(){

		$ret = array();

		//$sql = "SELECT id, nome, imagem, imagem_off FROM marca WHERE st_ativo = 'S' AND imagem <> '' ORDER BY nome";
		//$sql = "SELECT id, nome, imagem, imagem_off FROM marca INNER JOIN item ON (marca.id = item.marca_id) WHERE st_ativo = 'S' AND imagem <> '' ORDER BY nome";
		//$sql = "SELECT id, nome, imagem, imagem_off FROM marca, item WHERE st_ativo = 'S' AND imagem <> '' AND marca.id = item.marca_id ORDER BY nome";
		$sql = "SELECT id, nome, chave FROM pagina WHERE st_ativo = 'S' AND st_especial = 'S' ORDER BY pagina.ordem, pagina.nome";
		$query = query($sql);
		while($fetch=fetch_array($query)){
			$pagina = new pagina();
			$pagina->set_by_array($fetch);
			$ret[] = $pagina;
		}

		return $ret;

	}

	// Monta e-mail do pedido para o cliente
	static function montaEmailPedidoCliente($pedido){

		$cadastro = new cadastro($pedido->cadastro_id);

		$tEmail = new Template('tpl.email-pedido-cliente.html');

		$tEmail->config = new config();
		$tEmail->pedido = $pedido;
		$tEmail->cadastro = $cadastro;



		// printr($pedido->formapagamento_id);
		// printr(formapagamento::get('BOLETOBRADESCO'));

		// if($pedido->formapagamento_id==formapagamento::get('BOLETOBRADESCO')->id){
			// $tEmail->parseBlock('BLOCK_LINK_BOLETO');
		// }

		if($pedido->isDepositoBancario()){
			foreach($pedido->getContasDepositoBancario() as $depositobancario){
				// printr($depositobancario);
				$tEmail->depositobancario = $depositobancario;
				$tEmail->parseBlock('BLOCK_DEPOSITOBANCARIO_ITEM', true);
			}
			$tEmail->parseBlock('BLOCK_DEPOSITOBANCARIO');
		}

		if($pedido->isBoleto()){
			$tEmail->parseBlock('BLOCK_BOLETO_LINK');
		}

		if($pedido->isPagSeguro()){
			$tEmail->parseBlock('BLOCK_PAGSEGURO_LINK');
		}

		if($pedido->isPagamentoDigital()){
			$tEmail->parseBlock('BLOCK_PAGAMENTODIGITAL_LINK');
		}

		foreach($pedido->get_childs('pedidoitem') as $pedidoitem){
		
			//
			if($pedidoitem->kit_pedido_itens!=''){
				$carrinho = new carrinho();
				$itenskit = unserialize($pedidoitem->kit_pedido_itens);
				foreach($itenskit as $key=>$itemid){
					$kititem = new item($itemid);
					$tEmail->kititem = $kititem;
					$tEmail->parseBlock("BLOCK_KITITENS",true);
				}
			}
			//
		
			$tEmail->pedidoitem = $pedidoitem ;
			$tEmail->parseBlock('BLOCK_PEDIDOITEM', true);
		}

		return $tEmail->getContent();

	}

	// Monta e-mail que é enviado para o presenteado com o valepresente
	static function montaEmailValePresentePresenteado(pedido $pedido){

		$valepresente = new valepresente($pedido->valepresente_id);
		$cadastro = new cadastro($pedido->cadastro_id);

		$tEmail = new Template('tpl.email-presente-presenteado.html');

		$tEmail->config = new config();
		$tEmail->cadastro = $cadastro;
		$tEmail->valepresente = $valepresente;

		return $tEmail->getContent();

	}

	// Monta e-mail que é enviado para o comprador do vale presente
	static function montaEmailValePresenteComprador($pedido){

		$valepresente = new valepresente($pedido->valepresente_id);
		$cadastro = new cadastro($pedido->cadastro_id);

		$tEmail = new Template('tpl.email-presente-comprador.html');

		$tEmail->config = new config();
		$tEmail->pedido = $pedido;
		$tEmail->cadastro = $cadastro;
		$tEmail->valepresente = $valepresente;

		return $tEmail->getContent();

	}

	// Monta e-mail do pagamento em análise
	static function montaEmailPedidoAnalise($pedido, $pedidopagamento=null){

		$cadastro = new cadastro($pedido->cadastro_id);

		$pedidopagamento = new pedidopagamento(array('pedido_id'=>$pedido->id));

		$t = new Template('tpl.email-pedido-analise.html');

		$t->config = new config();
		$t->pedido = $pedido;
		$t->cadastro = $cadastro;
		// $t->pedidopagamento = $this;

		return $t->getContent();

	}

	// Monta e-mail do pagamento do pedido
	static function montaEmailPedidoPagamento($pedido, $pedidopagamento=null){

		$cadastro = new cadastro($pedido->cadastro_id);

		$pedidopagamento = new pedidopagamento(array('pedido_id'=>$pedido->id));

		$t = new Template('tpl.email-pedido-pagamento.html');

		$t->config = new config();
		$t->pedido = $pedido;
		$t->cadastro = $cadastro;
		// $t->pedidopagamento = $this;

		// printr($pedidopagamento);
		// $pedidopagamento->codigo_transacao = 'asdfadf';

		if($pedidopagamento && $pedidopagamento->codigo_transacao!=''){
			// $t->pedidopagamento = $pedidopagamento;
			// $t->parseBlock('BLOCK_CODIGO_TRANSACAO');
		}

		return $t->getContent();

	}

	// Monta e-mail do pedido em separação
	static function montaEmailPedidoSeparacao($pedido, $pedidoseparacao=null){

		$cadastro = new cadastro($pedido->cadastro_id);

		$pedidoseparacao = new pedidoseparacao(array('pedido_id'=>$pedido->id));

		$t = new Template('tpl.email-pedido-separacao.html');

		$t->config = new config();
		$t->pedido = $pedido;
		$t->cadastro = $cadastro;
		// $t->pedidopagamento = $this;

		return $t->getContent();
	}

	// Monta e-mail do cancelamento do pedido
	static function montaEmailPedidoCancelamento(pedido $pedido){

		$cadastro = new cadastro($pedido->cadastro_id);

		$t = new Template('tpl.email-pedido-cancelamento.html');

		$t->config = new config();
		$t->pedido = $pedido;
		$t->cadastro = $cadastro;
		// $t->pedidopagamento = $this;

		$email = new email();

		$email->addTo($cadastro->email, $cadastro->nome);
		$email->addBcc(config::get('EMAIL_ADMINISTRACAO'), config::get('EMPRESA'));

		return $t->getContent();

	}

	// Monta e-mail de contato do site
	static function montaEmailContato($contato){

		$contato->mensagem_br = nl2br($contato->mensagem);

		$t = new Template('tpl.email-contato.html');
		$t->config = new config();
		$t->contato = $contato;

		return $t->getContent();
	}

	// Monta e-mail de interesse no item
	static function montaEmailInteresseItem($iteminteresse){

		$tEmail = new Template('tpl.email-interesse.html');

		$item = new item($iteminteresse->item_id);

		$tEmail->config = new config();
		$tEmail->item = $item;

		return $tEmail->getContent();

	}

	// Monta e-mail de vencimento do boleto
	static function montaEmailVencimentoBoleto(pedido $pedido){

		$tEmail = new Template('tpl.email-vencimento-boleto.html');

		$pedido = new pedido($pedido->id);
		// $cadastro = new cadastro($pedido->cadastro_id);

		$tEmail->config = new config();
		$tEmail->pedido = $pedido;
		// $tEmail->cadastro = $cadastro;


		return $tEmail->getContent();

	}

	// Processa análise do pedido
	static function processaPedidoAnalise(pedidoanalise $pedidoanalise){

		$pedidoanalise->valor = tofloat($pedidoanalise->valor);

		// printr($pedidoanalise);

		$pedido = new pedido($pedidoanalise->pedido_id);

		if(rows(query($sql="SELECT * FROM pedidoanalise WHERE pedido_id = {$pedido->id}"))>0){
			throw new Exception('Este pedido já foi tem um registro de análise');
		}
		elseif($pedido->isPagoTotal()){
			throw new Exception('O pedido já foi pago totalmente, n&atilde;o pode ser adicionado');
		}
		elseif($pedidoanalise->valor>$pedido->valor_devido){
			throw new Exception('O valor inserido é maior que o valor devido, n&atilde;o é possível inserir');
		}
		elseif(!is_data($pedidoanalise->data_pagamento)){
			throw new Exception('Data do pagamento inválida');
		}
		else {
			$pedidoanalise->data_pagamento = to_bd_date(@$pedidoanalise->data_pagamento);
			$pedidoanalise->salva();
		}
	}

	// Processa o pagamento do pedido
	static function processaPedidoPagamento(pedidopagamento $pedidopagamento){

		// $pedidopagamento = new pedidopagamento();
		// $pedidopagamento->set_by_array($_REQUEST['pedidopagamento']);

		$pedidopagamento->valor = tofloat($pedidopagamento->valor);

		// printr($pedidopagamento);

		$pedido = new pedido($pedidopagamento->pedido_id);

		if(rows(query($sql="SELECT * FROM pedidopagamento WHERE pedido_id = {$pedido->id}"))>0){
			throw new Exception('Este pedido já foi pago');
		}
		elseif($pedido->isPagoTotal()){
			throw new Exception('O pedido já foi pago totalmente, n&atilde;o pode ser adicionado');
		}
		elseif($pedidopagamento->valor>$pedido->valor_devido){
			throw new Exception('O valor inserido é maior que o valor devido, n&atilde;o é possível inserir');
		}
		elseif($pedidopagamento->valor<$pedido->valor_devido){
			throw new Exception('O valor inserido é menor que o valor devido, n&atilde;o é possível inserir');
		}
		elseif(!is_data($pedidopagamento->data_pagamento)){
			throw new Exception('Data do pagamento inválida');
		}
		else {
			$pedidopagamento->data_pagamento = to_bd_date(@$pedidopagamento->data_pagamento);
			$pedidopagamento->salva();
		}
	}



	// Processa separacao do pedido
	static function processaPedidoSeparacao(pedidoseparacao $pedidoseparacao ){

		$pedido = new pedido($pedidoseparacao->pedido_id);

		if(rows(query($sql="SELECT * FROM pedidoseparacao WHERE pedido_id = {$pedido->id}"))>0){
			throw new Exception('Este pedido já foi separado');
		}
		else {
			$pedidoseparacao->data_separacao = to_bd_date(@$pedidoseparacao->data_separacao);
			$pedidoseparacao->salva();
		}
	}

	// Remove itens do estoque apos concretizacao no pedido
	static function procRetiraEstoqueByPedido($pedido){


		// Registra movimentacao de estoque
		foreach($pedido->get_childs('pedidoitem') as $pedidoitem){

			// Recarrega item
			$item = new item($pedidoitem->item_id);
			// Verifica se é kit
			if($item->st_kit=='S'){
				projeto::procKitRetiraEstoqueByPedido($item,$pedidoitem);
			}else{

				// Lanca um item na movimentacao de estoque
				$estoquemov = new estoquemov();

				$estoquemov->item_id         = $pedidoitem->item_id;
				$estoquemov->pedido_id       = $pedido->id;
				$estoquemov->estoquemov_tipo = "S";
				$estoquemov->qtd             = $pedidoitem->item_qtd*(-1);
				$estoquemov->log             = "Pedido realizado: {$pedido->id}";

				$estoquemov->salva();

				// Atualiza dados dados de estoque no item
				projeto::setItemEstoqueByEstoqueMov($pedidoitem->item_id);



				// Define saida total
				$qtd_saida_total = $pedidoitem->item_qtd;

				// Recupera lotes possiveis
				foreach($item->get_childs('lote', 'AND qtd_saldo > 0 AND data_vencimento > NOW()', 'ORDER BY data_vencimento') as $lote){

					// Retira itens do lote
					if($qtd_saida_total>0){

						$lotemov = new lotemov();

						$lotemov->lote_id = $lote->id;
						$lotemov->pedido_id = $pedido->id;
						$lotemov->item_id = $item->id;

						$lotemov->qtd_saida = $qtd_saida_total > $lote->qtd_saldo ? $lote->qtd_saldo : $qtd_saida_total;
						$lote->qtd_saldo = $lote->qtd_saldo - $lotemov->qtd_saida;

						$lote->salva();
						$lotemov->salva();

						$qtd_saida_total -= $lotemov->qtd_saida;
					}
				}
			}
		}
	}

	// Retira do estoque os itens do kit
	static function procKitRetiraEstoqueByPedido($kit, $pedidoitem){

		//$query = query("SELECT * FROM kititem WHERE kit_id = {$kit->id}");
		$itens_kit = unserialize($pedidoitem->kit_pedido_itens);

		foreach($itens_kit as $key=>$value){
			$item = new item($value);
			// Lanca um item na movimentacao de estoque
			$estoquemov = new estoquemov();

			$estoquemov->item_id         = $item->id;
			$estoquemov->pedido_id       = $pedidoitem->pedido_id;
			$estoquemov->estoquemov_tipo = "S";
			$estoquemov->qtd             = $pedidoitem->item_qtd*(-1);
			$estoquemov->log             = "Pedido realizado: {$pedidoitem->pedido_id} - Kit: {$kit->referencia}";

			$estoquemov->salva();

			// Atualiza dados dados de estoque no item
			projeto::setItemEstoqueByEstoqueMov($item->id);

			// Define saida total
			$qtd_saida_total = $pedidoitem->item_qtd;

			// Recupera lotes possiveis
			foreach($item->get_childs('lote', 'AND qtd_saldo > 0 AND data_vencimento >= NOW()', 'ORDER BY data_vencimento') as $lote){


				// Retira itens do lote
				if($qtd_saida_total>0){

					$lotemov = new lotemov();

					$lotemov->lote_id = $lote->id;
					$lotemov->pedido_id = $pedidoitem->pedido_id;
					$lotemov->item_id = $item->id;

					$lotemov->qtd_saida = $qtd_saida_total > $lote->qtd_saldo ? $lote->qtd_saldo : $qtd_saida_total;
					$lote->qtd_saldo = $lote->qtd_saldo - $lotemov->qtd_saida;

					$lote->salva();
					$lotemov->salva();

					$qtd_saida_total -= $lotemov->qtd_saida;
				}
			}
		}
	}



	// Adiciona itens no estoque apos cancelamento do pedido
	static function procAdicionaEstoqueByPedidoCancelado($pedido){

		foreach($pedido->get_childs('pedidoitem') as $pedidoitem){

			// Recarrega item
			$item = new item($pedidoitem->item_id);

			if($item->st_kit=='S'){
				projeto::procKitAdicionaEstoqueByPedidoCancelado($item,$pedidoitem);
			}else{
				// Lanca um item na movimentacao de estoque
				$estoquemov = new estoquemov();

				$estoquemov->item_id = $pedidoitem->item_id;
				$estoquemov->pedido_id = $pedido->id;
				$estoquemov->estoquemov_tipo = "E";
				$estoquemov->qtd = $pedidoitem->item_qtd;
				$estoquemov->data_hora = bd_now();
				$estoquemov->log = "Pedido Cancelado: {$pedidoitem->pedido_id}";

				$estoquemov->salva();

				// Atualiza dados dados de estoque no item
				projeto::setItemEstoqueByEstoqueMov($pedidoitem->item_id);

				// Define saida total
				$qtd_saida_total = $pedidoitem->item_qtd;

				// Recupera lotes associados a esse pedido
				foreach($item->get_childs('lotemov', "AND pedido_id = {$pedido->id}") as $lotemov){

					$atual = $lotemov;
					$lote = new lote($lotemov->lote_id);

					$lotemov = new lotemov();

					$lotemov->lote_id = $lote->id;
					$lotemov->pedido_id = $pedido->id;
					$lotemov->item_id = $item->id;

					$lotemov->qtd_saida = $atual->qtd_saida*-1;

					$lote->qtd_saldo = $lote->qtd_saldo + $atual->qtd_saida;

					$lote->salva();
					$lotemov->salva();

				}
			}
		}
	}

	// kit retorna ao estoque
	static function procKitAdicionaEstoqueByPedidoCancelado($kit,$pedidoitem){

		//$query = query("SELECT * FROM kititem WHERE kit_id = {$kit->id}");
		$itens_kit = unserialize($pedidoitem->kit_pedido_itens);
		// printr($itens_kit);
		// die();
		foreach($itens_kit as $key=>$value){
			$item = new item($value);

			// Lanca um item na movimentacao de estoque
			$estoquemov = new estoquemov();

			$estoquemov->item_id = $item->id;
			$estoquemov->pedido_id = $pedidoitem->pedido_id;
			$estoquemov->estoquemov_tipo = "E";
			$estoquemov->qtd = $pedidoitem->item_qtd;
			$estoquemov->data_hora = bd_now();
			$estoquemov->log = "Pedido Cancelado: {$pedidoitem->pedido_id} - Kit: {$kit->referencia}";

			$estoquemov->salva();

			// Atualiza dados dados de estoque no item
			projeto::setItemEstoqueByEstoqueMov($item->id);

			// Define saida total
			$qtd_saida_total = $pedidoitem->item_qtd;

			// Recupera lotes associados a esse pedido
			foreach($item->get_childs('lotemov', "AND pedido_id = {$pedidoitem->pedido_id}") as $lotemov){

				$atual = $lotemov;
				$lote = new lote($lotemov->lote_id);

				$lotemov = new lotemov();

				$lotemov->lote_id   = $lote->id;
				$lotemov->pedido_id = $pedidoitem->pedido_id;
				$lotemov->item_id   = $item->id;

				$lotemov->qtd_saida = $atual->qtd_saida*-1;

				$lote->qtd_saldo    = $lote->qtd_saldo + $atual->qtd_saida;

				$lote->salva();
				$lotemov->salva();

			}
		}
	}


	// Acerta quantidade em estoque de um item
	static function setItemEstoqueByEstoqueMov($item_id){
		query("UPDATE item SET qtd_estoque = (SELECT SUM(qtd) FROM estoquemov WHERE item_id = {$item_id}) WHERE id = {$item_id}");

		// Carrega item
		$item = new item($item_id);

		// Se for uma variacao, altera estoque do item pai
		if($item->itemsku_id>0){
			item::atualizaEstoquePrincipalByVariacao($item->itemsku_id);
		}
	}

	// Seta os dados do pagseguro para um pedido
	static function setPagSeguro($pedido){

		require_once 'pagseguro-php-2.1.0/source/PagSeguroLibrary/PagSeguroLibrary.php';

		// Instantiate a new payment request
		$paymentRequest = new PagSeguroPaymentRequest();

		$cadastro = new cadastro($pedido->cadastro_id);

		// Sets the currency
		$paymentRequest->setCurrency("BRL");

		$pedidoitem_arr = $pedido->get_childs('pedidoitem');

		$percentual_desconto = 1;
		if($pedido->valor_desconto>0){
			// Add an item for this payment request
			// $paymentRequest->addItem($pedido->id.'-desconto', "Desconto", 1, $pedido->valor_desconto*-1, 0, 0);
			$percentual_desconto =  ($pedido->valor_devido)/($pedido->valor_itens+$pedido->valor_frete);
		}

		$i=0;
		foreach($pedidoitem_arr as $pedidoitem){

			$i++;

			$item = new item($pedidoitem->item_id);

			if($pedidoitem->item_preco > 0){
				// Add an item for this payment request
				$paymentRequest->addItem(
				$item->id
				, "{$item->id} - {$item->referencia} - {$item->nome}"
				, $pedidoitem->item_qtd
				, $pedidoitem->item_preco*$percentual_desconto
				, 0
				// , ($i==sizeof($pedidoitem_arr) ? $pedido->valor_frete * $percentual_desconto : 0));
				, ($i==1 ? (($pedido->valor_frete/$pedidoitem->item_qtd) * $percentual_desconto) : 0) // Adiciona frete no primeiro item, ao inves do ultimo
				);
			}
		}



		if($pedido->valor_desconto>0){
			// Add an item for this payment request
			// $paymentRequest->addItem($pedido->id.'-desconto', "Desconto", 1, $pedido->valor_desconto*-1, 0, 0);
		}

		// Sets a reference code for this payment request, it is useful to identify this payment in future notifications.
		$paymentRequest->setReference($pedido->id);

		// Sets shipping information for this payment request
		$CODIGO_SEDEX = PagSeguroShippingType::getCodeByType('NOT_SPECIFIED');
		$paymentRequest->setShippingType($CODIGO_SEDEX);
		$paymentRequest->setShippingAddress(
			$pedido->cep
			,$pedido->logradouro
			,$pedido->numero
			,$pedido->complemento
			,$pedido->bairro
			,$pedido->cidade
			,strtoupper($pedido->uf)
			,'BRA'
		);

		// $cadastro->nome = 'JosÃ© Carlos';
		// $cadastro->sobrenome = 'Souza';

		$senderName = '';
		if($cadastro->tipo_pessoa=='F' || $cadastro->tipo_pessoa==''){
			$nome      = explode(' ',$cadastro->nome);
			$sobrenome = explode(' ',$cadastro->sobrenome);

			$nome      = trim(@$nome[0]);
			$sobrenome = trim(@$sobrenome[0]);

			// $nome      = trim($cadastro->nome);
			// $sobrenome = trim($cadastro->sobrenome);

			$senderName = "{$nome} {$sobrenome}" ;
		}
		else {
			$senderName = $cadastro->empresa;
		}


		if($cadastro->fone_res_ddd==''){
			$telefone = str_replace(array('(',')','-'),'',$cadastro->fone_res);
			$ddd_     = substr($telefone,0,2);
			$fone_    = substr($telefone,2);
			$paymentRequest->setSender($senderName, $cadastro->email, $ddd_, butil::getNumbers($fone_));
		}else{
			$paymentRequest->setSender($senderName, $cadastro->email, $cadastro->fone_res_ddd, butil::getNumbers($cadastro->fone_res));
		}




		try {

			/*
			* #### Crendencials #####
			*/

			$credentials = new PagSeguroAccountCredentials(config::get('PAGSEGURO_EMAIL'), config::get('PAGSEGURO_TOKEN'));

			// Register this payment request in PagSeguro, to obtain the payment URL for redirect your customer.
			$url = $paymentRequest->register($credentials);


			$pedido->pagseguro_checkout_url = $url;
			$pedido->pagseguro_checkout_code = $url;
			$pedido->pagseguro_checkout_date = butil::bd_now();

			$pedido->atualiza();

			//self::printPaymentUrl($url);
			// printr($paymentRequest);
			// die();

		}
		catch (PagSeguroServiceException $e) {
			die($e->getMessage());
		}
	}

	// Retorna uma lista de marcas que aparecem na home
	static function getMarcasHome($orderby='rand'){

		$ret = array();

		$sql =
		"
		SELECT
			SQL_CACHE
			DISTINCT
			marca.id
			,marca.nome
			,marca.imagem
			,marca.imagem_off
		FROM
			marca
		INNER JOIN item ON (
			marca.id = item.marca_id
		AND item.st_ativo = 'S'
		AND item.preco > 0 -- Apenas itens com preco
		AND item.imagem <> '' -- Apenas itens com imagem
		AND item.itemsku_id IS NULL -- Apenas itens principais da variacao
		)
		INNER JOIN itemsite ON (
			itemsite.item_id = item.id
		AND itemsite.site_id = ".projeto::getSiteAtual()."
		)
		WHERE
			marca.st_ativo = 'S'
		AND marca.imagem <> ''
		ORDER BY
			marca.nome";

		$query = query($sql);
		while($fetch=fetch_array($query))
		{
			$marca = new marca();
			$marca->set_by_array($fetch);
			$ret[] = $marca;
		}

		return $ret;
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

	public function getCategoriasAtivasSiteTodas(){

		$return = array();

		// Menu de categorias de produtos
		$sql =
		"
		SELECT
			categoria.id
			,categoria.categoria_id
			,categoria.nome
			,categoria.ordem
		FROM
			categoria
		INNER JOIN itemcategoria ON (
			itemcategoria.categoria_id = categoria.id
		)
		INNER JOIN item ON (
			itemcategoria.item_id = item.id
		AND item.st_ativo = 'S'
		AND item.imagem <> ''
		AND item.preco > 0
		AND item.qtd_estoque > 0
		)
		INNER JOIN itemsite ON (
			itemsite.item_id = item.id
		AND itemsite.site_id = ".projeto::getSiteAtual()."
		)
		WHERE
			categoria.st_ativo = 'S'
		AND categoria.st_lista_menu = 'S'
		GROUP BY
			categoria.id
			,categoria.nome
		ORDER BY
			categoria.ordem
			,categoria.nome

		";


		// UNION

		// SELECT
			// categoria.id
			// ,categoria.categoria_id
			// ,categoria.nome
			// ,categoria.ordem
		// FROM
			// categoria
		// INNER JOIN itemcategoria ON (
			// itemcategoria.categoria_id = categoria.id
		// )
		// INNER JOIN kit ON (
			// itemcategoria.kit_id = kit.id
			// AND kit.st_ativo = 'S'
			// AND kit.imagem <> ''
			// AND kit.preco > 0
			// AND kit.qtd_estoque > 0
		// )

		// WHERE
			// categoria.st_ativo = 'S'
		// AND categoria.st_lista_menu = 'S'

		// GROUP BY
			// categoria.id
			// ,categoria.nome
		// ORDER BY
			// 4,3

		//printr($sql);

		// $sql =
		// SELECT
			// categoria.id
			// ,categoria.categoria_id
			// ,categoria.nome
		// FROM
			// categoria
		// INNER JOIN itemcategoria ON (
			// itemcategoria.categoria_id = categoria.id
		// )

		// WHERE
			// categoria.st_ativo = 'S'
		// AND categoria.st_lista_menu = 'S'
		// AND EXISTS(SELECT item.* FROM item WHERE  item.st_ativo = 'S' AND item.imagem <> ''  AND item.preco > 0 AND item.qtd_estoque > 0 AND item.id IN(SELECT itemcategoria.item_id FROM itemcategoria WHERE itemcategoria.categoria_id = categoria_id))
		// AND EXISTS(SELECT kit.* FROM kit WHERE kit.st_ativo = 'S' AND kit.qtd_estoque > 0 AND kit.preco > 0 AND kit.imagem <> '' AND kit.id IN (SELECT itemcategoria.kit_id FROM itemcategoria WHERE itemcategoria.categoria_id = categoria_id))
		// GROUP BY
			// categoria.id
			// ,categoria.nome
		// ORDER BY
			// categoria.ordem
			// ,categoria.nome";

		$query = query($sql);

		while($fetch=fetch($query)){
			$categoria = new categoria();
			$categoria->load_by_fetch($fetch);
			$return[] = $categoria;
		}

		return $return;
	}

	// Retorna itens possiveis que podem ser comprados junto
	static function getItensCompreJunto($item_id=0, $limit=4){

		$return = array();

		$sql =
		"
		SELECT
			item.id
			,item.st_ativo
			,item.referencia
			,item.marca_id
			,item.nome
			,item.imagem
			,item.qtd_estoque
			,item.preco
			,item.marca_id
			,item.qtd_bonus
		FROM
			item
		INNER JOIN itemcomprejunto ON (
			item.id = itemcomprejunto.itemcomprejunto_id
		AND itemcomprejunto.item_id = {$item_id}
		)
		INNER JOIN itemsite ON (
			itemsite.item_id = item.id
		AND itemsite.site_id = ".projeto::getSiteAtual()."
		)
		WHERE
			1=1
		GROUP BY
			item.id
		LIMIT
			{$limit}
		" ;

		// print $sql;

		$query = query($sql);

		while($fetch=fetch_array($query)){
			// $item = new item($fetch->id);

			$item = new item();
			$item->set_by_array($fetch);

			$return[] = $item;
		}

		//printr($return);

		return $return;
	}

	// Retorna itens que podem ser vendidos como brinde
	static function getItensBrinde($item_id, $limit=3){

		$return = array();

		$ids = 0;
		$id_s = 0;

		if(is_array($item_id)){
			$ids = join(',', $item_id);

			//Verifica se é variação e adicionaa um array para não exibir o mesmo item a ser comprado na parte de brindes
			$arr = array();
			foreach($item_id as $key=>$value){
				$item = new item($value);
				if($item->itemsku_id>0){
					$arr[$item->itemsku_id] = $item->itemsku_id;
				}else{
					$arr[$value] = $value;
				}
			}

			$id_s =join(',',$arr);
			////

		}
		else {
			$id_s = $ids = $item_id;

			$item = new item($item_id);

			// verifica se é variação
			if($item->itemsku_id>0){
				$id_s = $item->itemsku_id;
			}

			//verifica se é kit e adiciona os itens que compõem o kit
			if($item->st_kit=='S'){
				$arr = array('0');
				foreach($item->getItensKitArr() as $key=>$value){
					$_item = new item($value);
					if($_item->itemsku_id>0){
						$arr[] = $_item->itemsku_id;
					}else{
						$arr[] = $value;
					}
				}
				$id_s =join(',',$arr);
			}
		}

		$preco = floatval(query_col($sql="SELECT sum(preco) FROM item WHERE id in ({$ids}) AND st_brinde = 'S'"));

		$sql =
		"
		SELECT
			SQL_CACHE
			item.id
			,item.st_ativo
			,item.referencia
			,item.marca_id
			,item.nome
			,item.imagem
			,item.qtd_estoque
			,item.preco
			,item.marca_id
		FROM
			item
		INNER JOIN itemgrupobrindes ON (
			item.id = itemgrupobrindes.item_id
		)
		WHERE
			EXISTS (SELECT id FROM grupobrindes WHERE grupobrindes.id = itemgrupobrindes.grupobrindes_id
			AND grupobrindes.st_ativo = 'S'
			AND grupobrindes.valor_inicial <= {$preco}  AND {$preco} <= grupobrindes.valor_final)
		AND item.st_ativo = 'S' -- Apenas itens ativos
		AND item.qtd_estoque > 5 -- Apenas brindes com mais de 5 itens com quantidade em estoque
		AND item.preco > 0 -- Apenas itens com preco
		AND item.itemsku_id IS NULL

		AND item.id NOT IN ({$id_s})

		ORDER BY
			rand()
		LIMIT
			{$limit}
		" ;

		//butil::__log($sql);

		//printr($sql);

		$query = query($sql);

		while($fetch=fetch_array($query)){
			// $item = new item($fetch->id);

			$item = new item();
			$item->set_by_array($fetch);

			$return[] = $item;
		}

		//printr($return);
		//die();

		return $return;

	}

	// Retorna apenas os itens que são brinde e que podem aparecer no frontend
	static function getItensBrindeFrontEnd($item_id, $limit=3){

		$return = array();

		//printr(projeto::getItensBrinde($item_id, $limit));

		foreach(projeto::getItensBrinde($item_id, $limit) as $brinde){

			$len = 13;
			$conteudo = strip_tags($brinde->nome);
			$brinde->nome = strlen($conteudo)>$len?substr($conteudo,0,$len).'...':$conteudo;


			if(projeto::isVendaPossivel($brinde)){
				$return[] = $brinde;
			}
		}

		// printr($return);
		// die();

		return $return;

	}

	// Retorna todos os itens de um kit
	static function getItensByKit($kit_id=0){

		$return = array();

		// $sql =
		// "
		// SELECT
			// item.id
		// FROM
			// item
		// INNER JOIN kititem ON (
			// item.id = kititem.item_id
		// )
		// WHERE
			// kititem.kit_id = {$kit_id}
		// GROUP BY
			// item.id
		// " ;
		
		$sql = "SELECT * FROM kititem WHERE kititem.kit_id = {$kit_id}";

		// print $sql;

		$query = query($sql);

		while($fetch=fetch($query)){
			$item = new item($fetch->item_id);
			$return[] = $item;
		}

		// printr($return);

		return $return;

	}

	// Retorna itens associados a um item, tendo como referencia outras compras relacionados
	static function getItensRelacionadosCompra($item_id){

		$return = array();

		$item_id = intval($item_id);

		$sql =
		"
		SELECT
			SQL_CACHE
			DISTINCT
			item.id
			,item.st_ativo
			,item.referencia
			,item.nome
			,item.descricao
			,item.marca_id
			,item.preco
			,item.preco_de
			,item.imagem
			,item.st_lancamento
			,item.splash_id
			,item.carimbo_id
			,item.carimbo_txt
			,item.qtd_bonus
			,item.itemopiniao_avaliacao_media
			,item.itemopiniao_qtd
		FROM
			pedidoitem
		INNER JOIN (select pedido_id from pedidoitem where item_id = {$item_id}) AS pedido_relacionado ON (
			pedido_relacionado.pedido_id = pedidoitem.pedido_id
		)
		INNER JOIN item ON (
			pedidoitem.item_id = item.id
		)
		INNER JOIN itemsite ON (
			itemsite.item_id = item.id
		AND itemsite.site_id = ".projeto::getSiteAtual()."
		)
		INNER JOIN pedido ON (
			pedido.id = pedidoitem.pedido_id
		)
		WHERE
			pedidoitem.item_id <> {$item_id}
		AND item.st_ativo = 'S'
		AND item.st_destaque = 'S'
		AND item.preco > 0
		AND item.qtd_estoque > 0
		AND item.imagem <> ''
		ORDER BY
			pedido.data_cadastro DESC
		";

		$query = query($sql);

		while($fetch=fetch($query)){
			$item = new item();
			$item->load_by_fetch($fetch);
			$return[] = $item;
		}

		return $return;
	}

	static function carrega_parceiros($t){
		$sql = "SELECT  * FROM cadparceiros WHERE st_ativo = 'S' ORDER BY ordem";
		$query = query($sql);
		while($fetch=fetch($query)){
			$t->parceiros = $fetch;
			$t->parseBlock("BLOCK_PARCEIROS",true);
		}
	}

	static function migra_enderecos($cadastro){
		$query = query("select * from endereco where cadastro_id = {$cadastro->id}");

		if(rows($query) == 0){

			$endereco = new endereco();

			$endereco->cadastro_id = $cadastro->id;
			$endereco->logradouro = $cadastro->logradouro;
			$endereco->cidade = $cadastro->cidade;
			$endereco->cep = $cadastro->cep;
			$endereco->uf = $cadastro->uf;
			$endereco->numero = $cadastro->numero;
			$endereco->complemento = $cadastro->complemento;
			$endereco->st_cobranca_padrao = 'S';
			$endereco->st_entrega_padrao = 'S';
			$endereco->bairro = $cadastro->bairro;

			$endereco->salva();
		}
	}



	// Retorna sugestoes de um item
	static function getItensSugestoes(){

	}

	// getPartProduto
	static function getPartProduto($item, $i=0, $posicao = 0, $tela=''){

		$p = new Template('tpl.part-produto.html');

		//$item = new item($item);

		// $item->load_by_fetch($itens[$i]);

		$item->chamada = nl2br($item->chamada);
		$item->descricao = nl2br($item->descricao);
		$item->nome_tag = stringAsTag($item->nome);

		// $item->qtd_estoque = 0;

		$p->path = PATH_SITE;
		$p->index = INDEX;

		$p->mais = "prods";

		//
		$verifica_item = true;
		//

		$splash = new splash($item->splash_id);
		if($splash->id){
			$p->list_splash = $splash;
			$p->parseBlock('BLOCK_SPLASH');
		}


		// Kit //
		if($item->st_kit=="S"){
			$cont = 0;			
			if($item->st_kit=='S'){
				$query = query("SELECT DISTINCT item_id,kit_id,COUNT(item_id)qtd_item FROM kititem WHERE kit_id = {$item->id} GROUP BY item_id");
				while($fetch=fetch($query)){
					$k_item = new item($fetch->item_id);
					$mqtd = $fetch->qtd_item;
					if($mqtd > $k_item->qtd_estoque){
						$verifica_item = false;
					}
					$cont++;
				}
			}			
			// $query = query("SELECT * FROM kititem WHERE kit_id = {$item->id}");
			// while($fetch=fetch($query)){
				// $kititem = new item($fetch->item_id);
				// if($kititem->qtd_estoque<1){
					// $verifica_item = false;
				// }
				// $cont++;
			// }
			if($verifica_item && $cont>0){
				$carimbo = new carimbo($item->carimbo_id);
				if($carimbo->id){
					$p->list_carimbo = $carimbo;
					$p->parseBlock('BLOCK_CARIMBO');
				}

				$p->parseBlock('BLOCK_COMPRAR');
			}
			else {
				$p->parseBlock('BLOCK_INDISPONIVEL');
			}
		}else{
			if($item->qtd_estoque > 0){
				$carimbo = new carimbo($item->carimbo_id);
				if($carimbo->id){
					$p->list_carimbo = $carimbo;
					$p->parseBlock('BLOCK_CARIMBO');
				}

				$p->parseBlock('BLOCK_COMPRAR');
			}
			else {
				$p->parseBlock('BLOCK_INDISPONIVEL');
			}
		}


		$p->list_item = $item;
		$p->posicao = $posicao++;
		$p->prod_tela = $tela;

		if($tela=='pesquisaProdutos'){
			if($i == 3){
				$p->estilo_ajuste ="style='margin-right:0'";
			}
		}

		if($tela=='destaque' || $tela=='home'){
			if((($i+1)%4)==0){
				//$p->estilo_ajuste = "style='margin-right:15px;'" ;
			}
		}

		if($tela=='pesquisaProdutos'){
			if((($i+1)%4)==0){
				$posicao = 1;
				$p->parseBlock('BLOCK_CLEAR');
			}
		}

		if($tela=='detalheProduto'){
			if((($i+1)%5)==0){
				$posicao = 1;
				$p->parseBlock('BLOCK_CLEAR');
			}
		}

		if($tela=='logadoDesejos'){
			$desejo = new desejo(array(
					'item_id' => $item->id
					,'cadastro_id' => $_SESSION['CADASTRO']->id
				)
			);
			// printr($desejo);
			if($desejo->id){
				$p->desejo = $desejo;
				$p->parseBlock('BLOCK_DESEJO');
			}
		}



		// Parse nas estrelas de avaliacao
		// if($item->itemopiniao_qtd>0){
			$p->parseBlock('BLOCK_AVALIACAO_ESTRELA');
		// }


		return $p->getContent();

	}

	static function copiarItem($item_id, $item_pai=0){

		$item_original  = new item($item_id);
		$item_copia = new item($item_original->id);

		$item_copia->id = 0;
		$item_copia->preco = 0.00;
		$item_copia->imagem = null;

		//printr($item_pai);

		if($item_pai>0){
			$item_copia->itemsku_id = $item_pai;

		}

		//printr($item_copia);
		$item_copia->salva();

		// $fetch_copia = fetch(query('select * from item where referencia = "'.$item_original->referencia.'" AND id <> '.$item_original->id.' '));

		$query = query("SELECT * FROM itemcategoria WHERE item_id ={$item_original->id}");
		while($fetch_itemcat = fetch($query)){
			query("INSERT INTO itemcategoria (item_id, categoria_id) VALUES({$item_copia->id}, {$fetch_itemcat->categoria_id})");
		}


		// Copiar variacao
		$query = (query($sql='select * from item where itemsku_id = '.$item_original->id));

		// die($sql);

		// Loop nas variacoes do item
		while($variacao = fetch($query)){
			//printr($variacao);
			projeto::copiarItem($variacao->id, $item_copia->id);
		}

		return $item_copia->id;

	}


	// Retorna itens que aparecem no site
	static function getSQLItens($opt=array()){

		// $categorias = @


		$sql =
		"
		SELECT
			SQL_CACHE
			DISTINCT
			item.id
			,item.st_ativo
			,item.referencia
			,item.nome
			,item.marca_id
			,item.preco
			,item.preco_de
			,item.imagem
			,item.splash_id
			,item.carimbo_id
			,item.carimbo_txt
			,item.itemopiniao_avaliacao_media
			,item.itemopiniao_qtd
		FROM
			item
		INNER JOIN itemcategoria ON (
			item.id = itemcategoria.item_id
		".($categoria->id>0?"AND itemcategoria.categoria_id = {$categoria->id}":"")."
		)
		INNER JOIN categoria ON (
			itemcategoria.categoria_id = categoria.id
		AND categoria.st_ativo = 'S'
		)

		WHERE
			item.st_ativo = 'S' -- Apenas itens ativos
		AND item.preco > 0 -- Apenas itens com preco
		-- AND item.qtd_estoque > 0 -- Apenas itens com estoque
		AND item.imagem <> '' -- Apenas itens com imagem
		AND item.itemsku_id IS NULL -- Apenas itens principais da variacao
		";
	}

	// Retorna se um item pode ser vendido no frontend ou nao
	public static function isVendaPossivel($item){
		if($item->st_kit=='S'){
			$query = query("SELECT DISTINCT item_id,kit_id,COUNT(item_id)qtd_item FROM kititem WHERE kit_id = {$item->id} GROUP BY item_id");
			while($fetch=fetch($query)){
				$k_item = new item($fetch->item_id);
				$mqtd = $fetch->qtd_item;
				if($mqtd > $k_item->qtd_estoque){
					return false;
				}
			}
			return true;
		}		
		
		// if($item->st_kit=="S"){
			// foreach($item->getItensKit() as $kititem){
				// $_item = new item($kititem->item_id);
				// if(!(($_item->st_ativo=='S')&&($_item->preco>0)&&($_item->qtd_estoque>0))){
					// return false;
				// }
			// }
		// }

		// return true;
		return (($item->st_ativo=='S')&&($item->preco>0)&&($item->qtd_estoque>0));
	}

	// Retorna opcoes de entrega, se baseando em um cep
	public function getOpcoesFormaEntrega(){

		$return = array();

		//
		$query = query($sql="");

		return $return;

	}

	// Retorna variacoes de um item
	public function getItemVariacoes($id=0){

		$ret = array();

		$id = intval($id);

		$query = query($sql="SELECT * FROM item WHERE itemsku_id = {$id} ORDER BY item.skuvariacao_tipo1, item.skuvariacao_tipo2");

		while($fetch=fetch($query)){
			$item = new item();
			$item->load_by_fetch($fetch);
			$ret[] = $item;
		}

		return $ret;

	}

	// Retorna variacoes de um item
	public function getItemVariacoesNivel($id=0, $nivel=1){

		$ret = array();

		$id = intval($id);

		$group = '';

		if($nivel==1){

		}
		if($nivel==2){

		}

		$query = query(
		$sql =
		"
		SELECT
			id
		FROM
			item WHERE itemsku_id = {$id}
		ORDER BY
			item.skuvariacao_tipo1, item.skuvariacao_tipo2 ");

		while($fetch=fetch($query)){
			$item = new item();
			$item->load_by_fetch($fetch);
			$ret[] = $item;
		}

		return $ret;

	}

	// Retorna formulario de redirecionamento para o ambiente de finalizacao de pagamento via Pagamento Digital
	static function getFormPagamentoDigital($pedido_id){

		$pedido_id = intval($pedido_id);

		$pedido = new pedido($pedido_id);
		$cadastro = new cadastro($pedido->cadastro_id);
		$pedidoitem_arr = $pedido->get_childs('pedidoitem');

		// Parametros
		$cod_loja = config::get('PAGAMENTODIGITAL_COD_LOJA');
		$email_loja = config::get('PAGAMENTODIGITAL_EMAIL');

		// $pedido->valor_frete = 1.00;

		$ret = '';

		$ret .= '<form name="formpagamentodigital" action="https://www.pagamentodigital.com.br/checkout/pay/" method="post" />'."\n";

		// $ret .= '<input name="cod_loja" type="text" value="'.$cod_loja.'" />'."\n";

		$ret .= '<input name="email_loja" type="text" value="'.$email_loja.'" />'."\n";
		$ret .= '<input name="id_pedido" type="text" value="'.$pedido->id.'" />'."\n";
		$ret .= '<input name="tipo_integracao" type="text" value="PAD" />'."\n";
		$ret .= '<input name="frete" type="text" value="'.$pedido->valor_frete.'" />'."\n";

		$i = 1;
		foreach($pedidoitem_arr as $pedidoitem){

			$item = new item($pedidoitem->item_id);

			// $pedidoitem->item_preco = 1.00;

			$ret .= '<input name="produto_codigo_'.$i.'" type="text" value="'.$item->id.'" />'."\n";
			$ret .= '<input name="produto_descricao_'.$i.'" type="text" value="'.$item->nome.'" />'."\n";
			$ret .= '<input name="produto_qtde_'.$i.'" type="text" value="'.$pedidoitem->item_qtd.'" />'."\n";
			$ret .= '<input name="produto_valor_'.$i.'" type="text" value="'.$pedidoitem->item_preco.'" />'."\n";
			// $ret .= '<input name="produto_extra_'.$i.'" type="text" value="'.$item->nome.'" />'."\n";

			$i ++;
		}

		// $ret .= '<input name="produto" type="text" value="Camisa Seleção Brasileira Oficial, Bola Adidas" />'."\n";
		// $ret .= '<input name="valor" type="text" value="399.90" />'."\n";
		// $ret .= '<input name="email" type="text" value="" />'."\n";

		$ret .= '<input name="nome" type="text" value="'.$cadastro->nome.' '.$cadastro->sobrenome.'" />'."\n";
		$ret .= '<input name="cpf" type="text" value="'.$cadastro->cpf.'" />'."\n";
		$ret .= '<input name="telefone" type="text" value="'.$cadastro->getFoneResCompleto().'" />'."\n";
		$ret .= '<input name="endereco" type="text" value="'.$cadastro->getEnderecoPagamentoDigital().'" />'."\n";
		$ret .= '<input name="cidade" type="text" value="'.$cadastro->cidade.'" />'."\n";
		$ret .= '<input name="estado" type="text" value="'.$cadastro->uf.'" />'."\n";
		$ret .= '<input name="cep" type="text" value="'.$cadastro->cep.'" />'."\n";
		$ret .= '<input name="free" type="text" value="'.$pedido->id.'" />'."\n";
		$ret .= '<input name="url_post" type="text" value="'.str_replace(PATH_SITE, '', config::get('URL')).INDEX.'pagamentodigital_retorno/" />'."\n";

		$ret .= '</form>'."\n";

		return $ret;
	}

	// Retorna formulario para o itaushopline
	static function getFormItauShopLine($pedido_id){

		$pedido_id = intval($pedido_id);

		$pedido = new pedido($pedido_id);
		$cadastro = new cadastro($pedido->cadastro_id);

		// parametros
		$identificacao = config::get('LOCAWEB_GATEWAY_IDENTIFICACAO');
		$modulo = 'ITAUSHOPLINE';
		$operacao = 'Pagamento';
		$ambiente = config::get('LOCAWEB_GATEWAY_AMBIENTE');
		// $ambiente = 'producao';

		// - dados do pedido
		$idioma = 'PT';
		$valor = str_replace(array('.',','),'',money($pedido->getValorTotal()));
		$_pedido = $pedido->id;
		$descricao = 'Ref. compra no site '.config::get('EMPRESA').' #'.$pedido->id;

		// - dados do cliente
		$nome = substr($cadastro->nome,0,30);
		$cpfcgc = substr($cadastro->cpf,0,14);
		$endereco = substr($cadastro->logradouro.' '.$cadastro->numero,0,40);
		$bairro = substr($cadastro->bairro,0,15);
		$cep = substr($cadastro->cep,0,8);
		$cidade = substr($cadastro->cidade,0,15);
		$estado = substr($cadastro->uf,0,2);

		// - dados adicionais
		$campo_livre = $pedido->id;

		$ret = '';

		$ret .= '<form method="POST" name="formlocaweb" action="https://comercio.locaweb.com.br/comercio.comp">';

		$ret .= '<!-- Parâmetros obrigatórios -->';
		$ret .= '<input type="text" name="identificacao" value="'.$identificacao.'" />';
		$ret .= '<input type="text" name="ambiente" value="'.$ambiente.'" />';
		$ret .= '<input type="text" name="modulo" value="'.$modulo.'" />';
		$ret .= '<input type="text" name="operacao" value="'.$operacao.'" />';
		$ret .= '<input type="text" name="pedido" value="'.$_pedido.'" />';
		$ret .= '<input type="text" name="valor" value="'.$valor.'" />';
		$ret .= '<input type="text" name="vencimento" value="" />';

		$ret .= '<!-- Parâmetros adicionais -->';
		$ret .= '<input type="text" name="nome" value="'.$nome.'" />';
		$ret .= '<input type="text" name="cpfcgc" value="'.$cpfcgc.'" />';
		$ret .= '<input type="text" name="endereco" value="'.$endereco.'" />';
		$ret .= '<input type="text" name="bairro" value="'.$bairro.'" />';
		$ret .= '<input type="text" name="cep" value="'.$cep.'" />';
		$ret .= '<input type="text" name="cidade" value="'.$cidade.'" />';
		$ret .= '<input type="text" name="estado" value="'.$estado.'" />';
		$ret .= '<input type="text" name="obs" value="" />';
		$ret .= '<input type="text" name="OBSAdicional1" value="" />';
		$ret .= '<input type="text" name="OBSAdicional2" value="" />';
		$ret .= '<input type="text" name="OBSAdicional3" value="" />';

		$ret .= '</form>';

		return $ret;

	}

	static function getFormLocawebRedecard($pedido_id){

		$pedido_id = intval($pedido_id);

		$pedido = new pedido($pedido_id);
		$cadastro = new cadastro($pedido->cadastro_id);
		$formapagamento = new formapagamento($pedido->formapagamento_id);

		// parametros
		$identificacao = config::get('LOCAWEB_GATEWAY_IDENTIFICACAO');
		$modulo = 'REDECARD';
		$operacao = 'Pagamento';
		$ambiente = config::get('LOCAWEB_GATEWAY_AMBIENTE');

		// dados do pedido
		$idioma = 'PT';
		$valor = str_replace(array('.',','),'',money($pedido->getValorTotal()));
		$_pedido = $pedido->id;
		$descricao = 'Ref. compra no site '.config::get('EMPRESA').' #'.$pedido->id;

		// dados do cliente
		$nome = substr($cadastro->nome,0,30);
		$cpfcgc = substr($cadastro->cpf,0,14);
		$endereco = substr($cadastro->logradouro.' '.$cadastro->numero,0,40);
		$bairro = substr($cadastro->bairro,0,15);
		$cep = substr($cadastro->cep,0,8);
		$cidade = substr($cadastro->cidade,0,15);
		$estado = substr($cadastro->uf,0,2);

		// define qual bandeira
		if($formapagamento->chave=='DINERS'){
			$bandeira = 'DINERS';
		}
		elseif($formapagamento->chave=='MASTERCARDREDECARD'){
			$bandeira = 'MASTERCARD';
		}

		// if(($formapagamento->chave=='DINERS')
		// || ($formapagamento->chave=='MASTERCARDREDECARD')
		// && $formapagamento->integrador=='LOCAWEB'){
			// // Printa boleto do bradesco
			// print projeto::getFormLocawebRedecard($pedido->id);
			// print "<script>document.forms.formlocaweb.submit();</script>";
		// }

		// dados adicionais
		$campo_livre = $pedido->id;

		$ret = '';

		$ret .= '<form method="POST" name="formlocaweb" action="https://comercio.locaweb.com.br/comercio.comp" target="_blank">';

		$ret .= '<!-- Parâmetros obrigatórios -->';
		$ret .= '<input type="hidden" name="identificacao" value="'.$identificacao.'" />';
		$ret .= '<input type="hidden" name="ambiente" value="'.$ambiente.'" />';
		$ret .= '<input type="hidden" name="modulo" value="'.$modulo.'" />';
		$ret .= '<input type="hidden" name="operacao" value="'.$operacao.'" />';
		$ret .= '<input type="hidden" name="bandeira" value="'.$bandeira.'" />';
		$ret .= '<input type="hidden" name="pedido" value="'.$_pedido.'" />';
		$ret .= '<input type="hidden" name="valor" value="'.$valor.'" />';
		$ret .= '<input type="hidden" name="parcelas" value="'.$pedido->parcelas.'" />';
		$ret .= '<input type="hidden" name="juros" value="0" />';

		$ret .= '</form>';

		return $ret;


	}

	// Retorna formulario de redirecionamento para o ambiente AMEX da locaweb
	static function getFormLocawebAmex($pedido_id){

		$pedido_id = intval($pedido_id);

		$pedido = new pedido($pedido_id);
		$cadastro = new cadastro($pedido->cadastro_id);

		// parametros
		$identificacao = config::get('LOCAWEB_GATEWAY_IDENTIFICACAO');
		$modulo = 'AMEX';
		$operacao = 'Pagamento';
		$ambiente = config::get('LOCAWEB_GATEWAY_AMBIENTE');
		// $ambiente = 'producao';

		// - dados do pedido
		$idioma = 'PT';
		$valor = str_replace(array('.',','),'',money($pedido->getValorTotal()));
		$_pedido = $pedido->id;
		$descricao = 'Ref. compra no site '.config::get('EMPRESA').' #'.$pedido->id;

		// - dados do cliente
		$nome = substr($cadastro->nome,0,30);
		$cpfcgc = substr($cadastro->cpf,0,14);
		$endereco = $cadastro->logradouro.' '.$cadastro->numero;
		$bairro = substr($cadastro->bairro,0,15);
		$cep = $cadastro->cep;
		$cidade = substr($cadastro->cidade,0,15);
		$estado = $cadastro->uf;

		// - dados adicionais
		$campo_livre = $pedido->id;

		$ret = '';

		$ret .= '<form method="POST" name="formlocaweb" action="https://comercio.locaweb.com.br/comercio.comp">'."\n";

		$ret .= '<!-- Parâmetros obrigatórios -->'."\n";

		$ret .= '<input type="text" name="identificacao" value="'.$identificacao.'" />'."\n";
		$ret .= '<input type="text" name="ambiente" value="'.$ambiente.'" />'."\n";
		$ret .= '<input type="text" name="modulo" value="'.$modulo.'" />'."\n";
		$ret .= '<input type="text" name="operacao" value="'.$operacao.'" />'."\n";
		$ret .= '<input type="text" name="MerchTxnRef" value="'.$pedido->id.'" />'."\n";
		$ret .= '<input type="text" name="valor" value="'.$valor.'" />'."\n";
		$ret .= '<input type="text" name="parcelas" value="'.$pedido->parcelas.'" />'."\n";
		$ret .= '<input type="text" name="PaymentPlan" value="PlanN" />'."\n";
		$ret .= '<input type="text" name="Locale" value="pt_BR" />'."\n";
		$ret .= '<input type="text" name="OrderInfo" value="'.$pedido->id.'" />'."\n";

		// $ret .= '<!-- Parâmetros adicionais -->'."\n";
		// $ret .= '<input type="text" name="AVS_Street01" value="" />'."\n";
		// $ret .= '<input type="text" name="AVS_City" value="" />'."\n";
		// $ret .= '<input type="text" name="AVS_StateProv" value="" />'."\n";
		// $ret .= '<input type="text" name="AVS_PostCode" value="" />'."\n";
		// $ret .= '<input type="text" name="AVS_Country" value="" />'."\n";
		// $ret .= '<input type="text" name="AVSLevel" value="" />'."\n";
		// $ret .= '<input type="text" name="BillTo_Title" value="" />'."\n";
		// $ret .= '<input type="text" name="BillTo_Firstname" value="" />'."\n";
		// $ret .= '<input type="text" name="BillTo_Middlename" value="" />'."\n";
		// $ret .= '<input type="text" name="BillTo_Lastname" value="" />'."\n";
		// $ret .= '<input type="text" name="BillTo_Phone" value="" />'."\n";
		// $ret .= '<input type="text" name="ShipTo_Fullname" value="" />'."\n";
		// $ret .= '<input type="text" name="ShipTo_Title" value="" />'."\n";
		// $ret .= '<input type="text" name="ShipTo_Firstname" value="" />'."\n";
		// $ret .= '<input type="text" name="ShipTo_Middlename" value="" />'."\n";
		// $ret .= '<input type="text" name="ShipTo_Lastname" value="" />'."\n";
		// $ret .= '<input type="text" name="ShipTo_Phone" value="" />'."\n";
		// $ret .= '<input type="text" name="ShipTo_Street01" value="" />'."\n";
		// $ret .= '<input type="text" name="ShipTo_City" value="" />'."\n";
		// $ret .= '<input type="text" name="ShipTo_StateProv" value="" />'."\n";
		// $ret .= '<input type="text" name="ShipTo_PostCode" value="" />'."\n";
		// $ret .= '<input type="text" name="ShipTo_Country" value="" />'."\n";
		// $ret .= '<input type="text" name="TicketNo" value="" />'."\n";

		$ret .= '</form>'."\n";

		return $ret;
	}

	//MÉTODO QUE RETORNA O VALOR DO FRETE DE UMA CAMPANHA
	public function valorFrete($uf, $cidade, $valor_compra){
		$return = 0;

		$valor_compra = floatval($valor_compra);

		if(($uf)&&($valor_compra)){
			if($cidade){
				//printr('Com cidade');
				$query = query('SELECT valor_frete FROM fretefaixalocal WHERE uf = "'.$uf.'" AND cidade="'.$cidade.'" AND '.$valor_compra.'>= valor_compra_minimo');
			}else{
				//printr('SEM cidade');
				$query = query('SELECT valor_frete FROM fretefaixalocal WHERE uf = "'.$uf.'" AND '.$valor_compra.'>= valor_compra_minimo');
				//printr($sql);
			}
			$fetch = fetch($query);
			if($fetch){
				$return = $fetch->valor_frete;
			}
		}
		return($return);
		die();
	}


	// Setar bonus
	public function setBonusByPedido($pedido_id=0){

		$pedido_id = intval($pedido_id);

		$pedido = new pedido($pedido_id);
		$bonus = new bonus(array('pedido_id'=>$pedido->id));

		// Caso ainda nao tenha criado o registro de bonus
		if(!$bonus->id){

			$qtd_bonus = 0;
			foreach($pedido->get_childs('pedidoitem') as $pedidoitem){
				$qtd_bonus += $pedidoitem->qtd_bonus;
			}

			// Só cria registro caso tenha algum bonus para usar
			if($qtd_bonus > 0){
				$bonus->pedido_id = $pedido->id;
				$bonus->cadastro_id =  $pedido->cadastro_id;
				$bonus->qtd_bonus = $qtd_bonus;
				$dias = config::get('VALIDADE_BONUS_CLIENTE');
				$bonus->data_validade = date('Y-m-d H:i:s', strtotime("+{$dias} days"));
				$bonus->salva();
			}
		}
	}

	// Retorna todos os bonus associados ao cadastro
	static function getBonusByCadastro($cadastro_id){

		// Variavel de retorno
		$ret = array();

		// Limpa
		$cadastro_id = intval($cadastro_id);

		$sql = "SELECT id, cadastro_id, pedido_id, qtd_bonus, data_cadastro FROM bonus WHERE cadastro_id = {$cadastro_id} ORDER BY data_cadastro DESC";

		$query = query($sql);

		while($fetch=fetch($query)){
			$bonus = new bonus();
			$bonus->load_by_fetch($fetch);
			$ret[] = $bonus;
		}

		return $ret;
	}

	//busca total de bonus que o cliente tem acumulado
	public function getBonusValorByCadastro($cadastro_id){
		$qtd_total_bonus = 0;
		$cadastro = new cadastro($cadastro_id);
		foreach($cadastro->get_childs('bonus') as $bonus){
			$qtd_total_bonus += $bonus->qtd_bonus;
		}
		$valor_moeda = ($qtd_total_bonus/100);
		return $valor_moeda;
		//die();
	}
	//Fim

	// Retornas formas de pagamento ativas para o frontend
	static function getFormasPagamento(){

		$ret = array();

		// Loop nas formas de pagamento ativas
		$query = query($sql="SELECT id, st_ativo, nome, chave, imagem, porcentagem_desconto, st_digita_dados_site FROM formapagamento WHERE st_ativo = 'S' ORDER BY nome");
		while($fetch=fetch($query)){
			$formapagamento = new formapagamento();
			$formapagamento->load_by_fetch($fetch);
			$ret[] = $formapagamento;
		}

		return $ret;

	}

	// Retorna cliente logado
	static function getLogado(){
		return @$_SESSION['CADASTRO'];
	}

	//
	public function testaGateway(){
		// 233
		$pedido = new pedido(233);
		printr($pedido->getValorFreteFormatado());
		$pedido->valor_frete = $pedido->valor_frete*.9;
		printr($pedido->getValorFreteFormatado());

		$pedido = new pedido(233);
		printr($pedido->getValorFreteFormatado());
		$pedido->valor_frete = $pedido->valor_frete/1.1;
		printr($pedido->getValorFreteFormatado());

		foreach($pedido->get_childs('pedidoitem') as $pedidoitem)
		{
			// $pedidoitem->item_preco = (1.20)*.9;
			// $pedidoitem->item_preco = $pedidoitem->item_preco*.9;
			$pedidoitem->item_preco = $pedidoitem->item_preco/1.1;

			// printr($pedidoitem);
			// printr($pedidoitem->item_preco);
			// printr($pedidoitem->item_preco/1.1);
			// printr(round($pedidoitem->item_preco/1.1,2));

			// printr($pedidoitem->item_preco*.9);
			printr($pedidoitem->getPrecoFormatado());

		}

		$gateway = new gateway(query_col("SELECT max(id) FROM gateway WHERE pedido_id = {$pedido->id}"));
		$out = new SimpleXMLElement($gateway->retorno_msg);

		// return @$out->$key;
		// printr(($gateway));

		$ret = "Número autorização: ".((string)$gateway->MKT_NUMERO_AUTORIZACAO);
		$ret .= "\n"."NSU: ".((string)$gateway->MKT_NSU_SITEF);
		print $ret;
	}

	// Retorna site atual
	static function getSiteAtual(){

		if(@$_SESSION['SITE_ID']){
			return $_SESSION['SITE_ID'];
		}

		$addr = $_SERVER['SERVER_NAME'];

		// troca bigforma.com.br para www.bigforma.com.br caso seja necessario
		if(strpos($addr,'www.')!==0){
			$addr = 'www.'.$addr;
		}

		$site = new site(array('nome'=>$addr));
		if(!$site->id){
			throw new Exception('Não foi possível identificar o site');
		}

		$_SESSION['SITE_ID'] = $site->id;
		$_SESSION['SITE_URL'] = "http://{$site->nome}";
		return $site->id;
	}

	static function getUrlSiteAtual(){

		if(@$_SESSION['SITE_URL']){
			return $_SESSION['SITE_URL'];
		}

		$addr = $_SERVER['SERVER_NAME'];

		// troca bigforma.com.br para www.bigforma.com.br caso seja necessario
		if(strpos($addr,'www.')!==0){
			$addr = 'www.'.$addr;
		}

		$site = new site(array('nome'=>$addr));
		if(!$site->id){
			throw new Exception('Não foi possível identificar o site');
		}

		$_SESSION['SITE_ID'] = $site->id;
		$_SESSION['SITE_URL'] = "http://{$site->nome}";

		return $_SESSION['SITE_URL'];

	}
	
	static function getUrlSiteAtualHTTPS(){

		if(@$_SESSION['SITE_URL_HTTPS']){
			return $_SESSION['SITE_URL_HTTPS'];
		}

		$addr = $_SERVER['SERVER_NAME'];

		// troca bigforma.com.br para www.bigforma.com.br caso seja necessario
		if(strpos($addr,'www.')!==0){
			$addr = 'www.'.$addr;
		}

		$site = new site(array('nome'=>$addr));
		if(!$site->id){
			throw new Exception('Não foi possível identificar o site');
		}

		$_SESSION['SITE_ID'] = $site->id;
		$_SESSION['SITE_URL_HTTPS'] = "https://{$site->nome}";

		return $_SESSION['SITE_URL_HTTPS'];

	}

}
