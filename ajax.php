<?php


require 'global.php';

class AJAX {
	
	function __construct(){
		$this->config = new config();
	}

	//add novo contato ajax
	public function novoContato(){
		$indice       = request('Indice');
		$novo_contato = contato::novoContato($indice);
		print $novo_contato;
	}
	//
	//amostras
	public function setImagemAmostra(){
		$referencia = $_REQUEST['referencia'];
		
		if($referencia != ''){
			
			$item = new item(array('referencia'=>$referencia));
			
			if($item->id){		
				$item->descricao = $item->getDescricaoListagem();
				print json_encode($item);
			}
		}
	}
	
	public function carregaVendedor(){
		$vendedor_id = $_REQUEST['vendedor_id'];
		
		print json_encode(new cadastro($vendedor_id));
	}
	
	public function amostraStatus(){
		$status_id = $_REQUEST['status'];
		
		print json_encode(new amostrastatus($status_id));
	}
	
	public function autoCompletaCliente(){
		$busca  = $_REQUEST['busca'];		
		$busca  = trim($busca);
		$return = array();
		
		$query  = query("SELECT * FROM cadastro WHERE nome LIKE '%{$busca}%' AND tipocadastro_id = 2");
		
		while($fetch=fetch($query)){
			$return[$fetch->id] = $fetch->nome;
		}
		
		print json_encode($return);
	}
	//
	//Atividade
	public function addAtividade(){
		$cadastro_id  = intval($_REQUEST['cad_id']);
		$atividade_id = intval($_REQUEST['atv_id']);
		
		$cadastro = new cadastro($cadastro_id);
		$atividade = new atividade($atividade_id);
		
		$t            = new Template('crm/tpl.pop-atividade.html');
		$t->cadastro  = $cadastro;
		$t->atividade = $atividade;
		$t->path      = PATH_SITE;
		
		foreach(atividade::prioridadeOpcoes() as $key=>$value){
			$selected = '';
			if($atividade->prioridade == $key){
				$selected = 'selected';
			}
			$t->options = "<option value='{$key}'{$selected}>{$value}</option>";
			$t->parseBlock('BLOCK_PRIORIDADES', true);
		}
		
		$arr = array('N'=>'NAO','S'=>'SIM');
		foreach($arr as $key=>$value){
			$selected = '';
			if($atividade->completa == $key){
				$selected = 'selected';
			}
			$t->options = "<option value='{$key}'{$selected}>{$value}</option>";
			$t->parseBlock('BLOCK_COMPLETO', true);
		}
		
		$t->email_enviar = boxes::enviarEmail($atividade->id, $cadastro,'atividade','fechado');
		
		if($atividade->id){
			$t->data_atual = $atividade->getDataCadastroFormatada();
			$t->data_expiracao = $atividade->getDataFinalFormatada();
		}else{
			$t->data_atual = date('d/m/Y');
			$t->data_expiracao = date('d/m/Y',strtotime("+".config::get('AMOSTRA_DIAS')."days",strtotime(date('Y-m-d'))));
		}
		$t->databox = crm::selectDataBox();
		
		echo $t->getContent();
	}
	//salva atividade
	public function salvaAtividade(){
	
		$dados     = $_REQUEST['dados'];
		$atividade = new atividade();
		
		$atividade->set_by_array($dados[0]);
		
		if((!strlen($atividade->nome)>0)){
			echo 0;
			die();
		}
		if(!(strlen($atividade->observacao)>0)){
			echo 0;
			die();
		}
		
		//print_r($dados);
		if($atividade->salva()){
			echo 1;
		}else{
			echo 0;
		}	
	}
	
	public function refreshAtividade(){
		$cadastro_id = intval($_REQUEST['cad_id']);
		$cadastro = new cadastro($cadastro_id);
		
		$at_query = query($sql="SELECT * FROM atividade WHERE cadastro_id = {$cadastro->id} and vendedor_id = ".decode($_SESSION['CADASTRO']->id)." ORDER BY id DESC");
	
		$at_html = "<table>";
		$at_html .= tag('thead',
			tag('tr',
				tag('th','No. da Atividade').tag('th','T&iacute;tulo').tag('th','Prioridade').tag('th','Data de Cadastro').tag('th','Data de Expira&ccedil;&atilde;o')
			)
		);	
		$at_html .= "<tbody>";
		while($fetch=fetch($at_query)){
			$atividade = new atividade($fetch->id);
			$at_html .= tag('tr onclick="javascript:abrePopAtividade('.$cadastro->id.', '.$atividade->id.');"',tag('td',$atividade->id).tag('td',$atividade->nome).tag('td',$atividade->prioridade).tag('td',$atividade->getDataCadastroFormatada()).tag('td',$atividade->getDataFinalFormatada()));
		}
		$at_html .= "</tbody>";
		$at_html .= "</table>";
		
		$out = array();
		$out[0] = rows($at_query);
		$out[1] = $at_html;
		$out[2] = crm::alertaAtividade();
		echo json_encode($out);
	}
	
	public function finalizaAtivivdade(){
		$atv_id = $_REQUEST['atv_id'];
		
		$atividade = new atividade(intval($atv_id));
		$atividade->completa = 'S';
		if(!$atividade->salva()){
			echo 'Erro ao excluir Ativivdade, tente novamente.';
		}
		
	}

	public function popAtividadeInfo(){
		$atividade_id = $_REQUEST['atividade_id'];
		$atividade    = new atividade($atividade_id);
		
		$t = new Template('tpl.pop-info_atividade.html');
		
		$t->atividade = $atividade;
		
		$t->selected_s = $atividade->completa=='S'?'selected':'';
		$t->selected_n = $atividade->completa=='N'?'selected':'';
		
		$t->selected_a = $atividade->prioridade=='Alta'?'selected':'';
		$t->selected_m = $atividade->prioridade=='Media'?'selected':'';
		$t->selected_b = $atividade->prioridade=='Baixa'?'selected':'';
		
		$t->atv_id = $atividade->id;
		$t->path   = PATH_SITE;
		
		echo $t->getContent();
	}
	
	public function popOrcamentoInfo(){
		$pedido_id = $_REQUEST['pedido_id'];
		$pedido    = new pedido($pedido_id);

		$t = new Template('crm/tpl.pop-info_orcamento.html');

		$t->pedido       = $pedido;
		$t->qtd_proposta = rows(query("SELECT * FROM proposta WHERE pedido_id = {$pedido->id}"));

		$venda           = new venda(array('pedido_id'=>$pedido->id));
		$vendastatus     = new vendastatus($venda->vendastatus_id);

		if($vendastatus->id){
			$t->venda_status = "<br /><br />Foi gerado um pedido para este or&ccedil;amento, seu status atual &eacute; : <span style='color:#444; text-decoration:underline;'>{$vendastatus->descricao}</span>";
		}else{
			$t->venda_status = '<br /><br />Ainda n&atilde;o foi gerado pedido para este or&ccedil;amento.';
		}

		$zebra = true;
		foreach($pedido->get_childs('pedidoitem') as $key=>$value){
			$item    = new item($value->item_id);
			$t->item = $item;
			$t->background = $zebra?"style='background-color:#777;color:#eee;'":"";
			$t->parseBlock('BLOCK_PEDIDO_ITEM', true);
			$zebra = !$zebra;
		}

		$t->path = PATH_SITE;

		echo $t->getContent();
	}
	
	public function popPropostaInfo(){
		$proposta_id = $_REQUEST['proposta_id'];
		$proposta    = new proposta($proposta_id);
		
		$t = new Template('crm/tpl.pop-info_proposta.html');
				
		$propostastatus = new propostastatus($proposta->propostastatus_id);
		$t->propostastatus = $propostastatus;
		
		$zebra = true;
		foreach($proposta->itens() as $key=>$value){
			$item    = new item($value->item_id);
			$t->item = $item;
			$t->background = $zebra?"style='background-color:#777;color:#eee;'":"";
			$t->parseBlock('BLOCK_PEDIDO_ITEM', true);
			$zebra = !$zebra;
		}
		
		if($proposta->data_envio!=''){
			$t->parseBlock('DATA_ENVIO');
		}
		
		$t->proposta = $proposta;
		$t->path = PATH_SITE;
		echo $t->getContent();
	}
	
	public function alteraAtividade(){
		$alt_atividade = $_REQUEST['alt'];
		
		$atividade = new atividade($alt_atividade[0]['id']);
		$atividade->set_by_array($alt_atividade[0]);
		// print_r($atividade);
		// die();
		if($atividade->atualiza()){		
			echo 1;
		}else{
			echo 0;
		}
		
	}

	public function productHint($q=""){
		$out = array("html"=>"","status"=>false);
		if($q=="")die(json_encode($out));
		$search = addslashes($q);
		$search = limpa("%".str_replace(' ','%',$search).'%');
		$query = query($sql = "SELECT DISTINCT item.id,item.nome,item.referencia,item.imagem, item.tag_nome, item.seopro_url FROM item 
		INNER JOIN itemcategoria ON(itemcategoria.item_id = item.id)
		INNER JOIN categoria ON (categoria.id = itemcategoria.categoria_id)
		WHERE 1=1 
		AND (item.itemsku_id = 0 OR item.itemsku_id IS NULL) 
		AND item.st_ativo = 'S'
		AND item.imagem <>''
		AND (
			item.referencia LIKE '{$search}'
			OR item.nome LIKE '{$search}'
			OR item.descricao LIKE '{$search}'
			OR categoria.nome LIKE '{$search}'
		) LIMIT 5");

		//printr($sql);

		$return = "";		
		$has = false;
		while($fetch=fetch($query)){
			$item = new item();
			$item->load_by_fetch($fetch);
			$return .= tag("div class='prev_referencia' onclick='javascript: setProduto({$fetch->id});'", 
				tag("div style='padding-bottom:10px;' id='preview_item_$fetch->id'",
					tag("a href='".$item->getLink()."'",
						tag("table style='width:100%;'",
							tag("tr",
								tag("td style='width:135px;' valign='middle'",tag("div style='width:125px;'",$item->getTagImagemListagemSemLazy()))
								.tag("td valign='middle'",
									stringChange($q,$fetch->nome)
									."<br><span style='color:#484848;'>"
									.stringChange($q,$fetch->referencia)
									."</span>"
								)
							)
						)
					)
				)
			);

			unset($item);
			$has = true;
		}
		if($has)$return .= tag("a style='margin-left: 15px; color: #484848; font-weight: 600;'  onclick='javascript: document.getElementById(\"formBusca\").submit();'","Veja todos os resultados");
		//if($has)$return .= tag("a onclick='javascript: document.getElementById(\"formBusca2\").submit();'","Veja todos os resultados");
		$out["status"] = $has;
		$out["html"] = $return;
		echo json_encode($out);
		die();
	}	
	
	public function popAmostraInfo(){
		$amostra_id = $_REQUEST['amostra_id'];
	
		$amostra    = new amostra($amostra_id);
		
		$t = new Template('crm/tpl.pop-info_amostra.html');
		
		$t->path  = PATH_SITE;
		
		$status   = new amostrastatus($amostra->amostrastatus_id);
		
		$t->amostra   = $amostra;
		
		$t->status    = $status->descricao;
		
		
		if($amostra->proposta_id){
			$proposta = new proposta($amostra->proposta_id);
			$t->proposta = $proposta;
			$t->parseBlock('BLOCK_RELACIONAMENTO');
		}
		
		
		$query = query("SELECT * FROM amostraitem WHERE amostra_id = {$amostra->id}");
		$zebra = true;
		while($fetch=fetch($query)){
			$item = new item($fetch->item_id);
			$t->item = $item;
			$t->background = $zebra?"style='background-color:#777;color:#eee;'":"";
			$t->parseBlock("BLOCK_PEDIDO_ITEM", true);
			$zebra = !$zebra;
		}
		
		
		
		echo $t->getContent();
	}
	//Alertas
	public function alerta(){
	
		if(array_key_exists('alertas_controle',$_SESSION)){
			if($_REQUEST['qtd_alert']!=$_SESSION['alertas_controle']){
				$_SESSION['alertas_controle'] = $_REQUEST['qtd_alert'];
			}
		}else{
			$_SESSION['alertas_controle'] = $_REQUEST['qtd_alert'];
		}
	
		
		$arr_alertas = array();
		$i = 0;
		
		$pedidos = pedido::pedidosAlertaInstantaneo();
		
		foreach($pedidos as $key=>$value){		
			$pedido            = new pedido($value->id);
			$cadastro          = new cadastro($pedido->cadastro_id);		
			$pedidostatus      = new pedidostatus($pedido->pedidostatus_id);
	
			$add_dias_pedido   = config::get('PEDIDOS_DIAS');	
			
			$alerta = new stdClass;
			$alerta->evento         = 'ORCAMENTO';
			$alerta->cliente_id     = $cadastro->id;
			$alerta->cliente        = $cadastro->nome;
			$alerta->evento_id      = $pedido->id;
			$alerta->modulo         = 'orcamentos';
			$alerta->data_cadastro  = $pedido->getDataHoraCadastroFormatada();
			$alerta->data_expiracao = $pedido->getDataExpiracaoFormatada();
			$alerta->status         = $pedidostatus->descricao;		
	
			//$chave                  = str_replace('/','', date('Y/m/d', strtotime("+".$add_dias_pedido."days",strtotime($pedido->data_cadastro)))).$i;
			
			$alerta->expirar = '';
			$alerta->expirou = '';
			
			$data_expiracao = date('Y/m/d', strtotime($pedido->data_expiracao));
			$_data = date('Y/m/d', strtotime("+ 2 days",strtotime(date('Y/m/d'))));
			if($data_expiracao >= date('Y/m/d') && $data_expiracao <= $_data){
				$alerta->expirar = 'style="background-image:url('.PATH_SITE.'img/fundo_laranja.png);"';
			}
			
			if($data_expiracao < date('Y/m/d')){
				$alerta->expirou = 'style="background-image:url('.PATH_SITE.'img/fundo_vermelho.png);"';
			}
			
			
			$alerta->img_aviso = '';
			if($pedido->visualizado !='S'){
				$alerta->img_aviso = "<img src='".PATH_SITE."img/circulo_aviso.gif' id='circulo_{$pedido->id}' />";
			}
			
			//$arr_alertas[$chave] = $alerta;
			$arr_alertas[] = $alerta;
			
			$i++;
		}
		
		
		
		
		//ksort($arr_alertas);
		$arr_return = array();
		
		$arr_return[0] = $_SESSION['alertas_controle'];
		
		foreach($arr_alertas as $key=>$alerta){			
			// $key = substr($key, 0, 10);
			// $key_ = str_replace('/','-',$key);
			// $key_ = (date('d/m/Y', strtotime("-1 days",strtotime($key_))));
			
			// if($key == date('d/m/Y') || $key_ == date('d/m/Y')){
				
				$alerta->html = "
						<p>
							<span><a  onclick='$(\"#circulo_{$alerta->evento_id}\").hide();'  href='".PATH_SITE."admin.php/{$alerta->modulo}/?action=editar&id={$alerta->evento_id}' target='_blank'>{$alerta->evento} {$alerta->evento_id}</a></span>						
							<br />
							Data   : {$alerta->data_cadastro}
							<br />						
							Expira&ccedil;&atilde;o  : {$alerta->data_expiracao}
							<br />
							Status : {$alerta->status}
						</p>
				";
				
				
				$arr_return[] = $alerta;
			//}
		}
		
		echo json_encode($arr_return);
		//echo $arr_return;
	}
	//orcamento
	public function precoItemOrcamento(){
		$item_id = $_REQUEST['itemid'];
		$ajuste  = $_REQUEST['ajuste'];
		$qtd     = $_REQUEST['qtd'];
		
		$arr_pecos = array();
		$preco     = 0;
		
		$item        = new item($item_id);
		$tabelapreco = new tabelapreco($item->tabelapreco_id);
		
		$tabelaanterior = new tabelaprecovalores();
		$tabelaanterior->qtd = 0;

		foreach($tabelapreco->get_childs('tabelaprecovalores') as $key=>$tabelaprecovalores){
			if((intval($tabelaanterior->qtd) <= $qtd) && ((intval($tabelaprecovalores->qtd)-1) >= $qtd)){
				$preco = $tabelaanterior->calculo($ajuste,$item->custo, $tabelapreco->aliquota);
			}			
			$tabelaanterior = $tabelaprecovalores;
		}		
		
		if((intval($tabelaanterior->qtd) <= $qtd)){
			$preco = $tabelaanterior->calculo($ajuste,$item->custo, $tabelapreco->aliquota);
		}
		
		print $preco;
		
		
		// if($tabelapreco->id){
			// foreach($tabelapreco->get_childs('tabelaprecovalores') as $key=>$tabelaprecovalores){
				
				// if(($qtd >= $tabelaanterior->qtd) && ($qtd <=($tabelaprecovalores->qtd-1))){
					// $preco = $tabelaanterior->calculo($ajuste,$item->custo, $tabelapreco->aliquota);
				// }
				
				// $tabelaanterior = $tabelaprecovalores;
			// }
		// }else{
			// $preco = str_replace('.',',','0,00');
		// }
		
		// echo $preco;		
		//echo json_encode($tabela_qtd);
	}
	//Envio de email proposta
	public function enviarEmailProposta(){
	
		
		$proposta = new proposta(intval(request('proposta_id')));	
		$pedido   = new pedido($proposta->pedido_id);
		
		$cadastro = new cadastro($pedido->cadastro_id);
		$vendedor = new cadastro($pedido->vendedor_id);
		//printr($proposta);
		//printr($_REQUEST);
		//return;
		$proposta->data_envio = bd_now();
		$proposta->salva();

		$e = new email();
		
		// Orcamento Enviado

		$e->addTo($cadastro->email, $cadastro->nome);
		$e->addReplyTo($vendedor->email, $vendedor->nome);
		$e->addCc($vendedor->email, $vendedor->nome);
		$e->addBcc(config::get('EMAIL_ADMINISTRATIVO'), config::get('EMPRESA'));

		$e->addHtml($proposta->html);
		$e->send("Proposta {$proposta->numero} Ref. Or&ccedil;amento {$pedido->id}");

		//$_SESSION['sucesso'] = tag('p', 'Sua proposta foi enviada para o email '.$cadastro->email);
		
		// Altera status do orcamento para proposta enviada
		$pedidostatus = new pedidostatus(array('descricao'=>'Or&ccedil;amento Enviado'));
		if($pedidostatus->id){
			query("UPDATE pedido SET pedidostatus_id = {$pedidostatus->id} WHERE id = {$pedido->id}");
		}
		
		// Altera status da proposta 
		$propostastatus = new propostastatus(array('descricao'=>'Aguardando resposta'));
		if($propostastatus->id){
			query("UPDATE proposta SET propostastatus_id = {$propostastatus->id} WHERE id = {$proposta->id}");
		}
		
		// $t = new TemplateAdmin('admin/tpl.admin-cadastro-orcamento.html') ;
		// $this->orcamentosEditar($t);
		echo 1;
	}
	
	public function controleMenu(){
	
		$menu = $_REQUEST["menu"];
		$_SESSION["menu"] = 0;
		if ($menu == "block"){		
			$_SESSION["menu"] = 1;
		}
		
	
	}
	
	public function submenuAberto(){
		$id     = $_REQUEST['menu_id'];
		$aberto = $_REQUEST['aberto'];
		
		if($aberto == 'aberto'){
			$_SESSION['link_menu'][$id] = $id;
		}
		
		if($aberto == 'fechado'){
			if(isSet($_SESSION['link_menu'][$id])){
				unset($_SESSION['link_menu'][$id]);
			}
		}
	}
	
	public function submenuAbertoFront(){
		$id     = $_REQUEST['menu_id'];
		$aberto = $_REQUEST['aberto'];
		
		if(array_key_exists('link_menu_front', $_SESSION)){
			unset($_SESSION['link_menu_front']);
		}

		if($aberto == 'aberto'){
			$_SESSION['link_menu_front'][$id] = $id;
		}
		
	}
	
	// public function buscaCliente(){
		// $cliente = $_REQUEST['cliente'];
		
		// $query = query($sql="SELECT 
								// cadastro.id, 
								// cadastro.nome, 
								// cadastro.email, 
								// cadastro.nome_fantasia, 
								// cadastro.razao_social
							// FROM 
								// cadastro 
							// WHERE 
								// tipocadastro_id = 2 
								// AND (
										// email LIKE '%{$cliente}%' 
										// OR cadastro.nome LIKE '%{$cliente}%' 
										// OR cadastro.nome_fantasia LIKE '%{$cliente}%'									 
										// OR cadastro.razao_social LIKE '%{$cliente}%'									 
									// ) 
									
							// ");
		
		// $itens = array();
		
		// while($fetch=fetch($query)){
			// $itens[] = $fetch;
		// }
		
		// echo json_encode($itens);
	// }
	
	public function carregaTabelaPreco(){

		
		$tabela_id   = $_REQUEST['tabela'];
		$item_id     = $_REQUEST['item'];
		
		
		
		$item        = new item($item_id);
		$tabelapreco = new tabelapreco($tabela_id);
		
		$tabela      ='';
		$arr_        = array();
		
		
		$arr_[0]   = '<tr style="background-color:#cccccc;">'.tag('td width="50px" style="background-color:#444;"','');
		//$arr_[0]   = '<tr style="background-color:#cccccc;">'.tag('td width="50px" style="background-color:#777;"','<strong>id '.$tabelapreco->id.'</strong>');
		$arr_[1]   = '<tr>'.tag('td',$tabelapreco->ajuste_1.'%');
		$arr_[2]   = '<tr>'.tag('td',$tabelapreco->ajuste_2.'%');
		$arr_[3]   = '<tr>'.tag('td',$tabelapreco->ajuste_3.'%');
		$arr_[4]   = '<tr>'.tag('td',$tabelapreco->ajuste_4.'%');

		foreach($tabelapreco->get_childs('tabelaprecovalores') as $key=>$value){
			
			$tabelaprecovalores = new tabelaprecovalores($value->id);				
				
			$arr_[0] .= tag('td',$tabelaprecovalores->qtd);	
			$arr_[1] .= tag('td',$tabelaprecovalores->calculo($tabelapreco->ajuste_1,$item->custo,$tabelapreco->aliquota));
			$arr_[2] .= tag('td',$tabelaprecovalores->calculo($tabelapreco->ajuste_2,$item->custo,$tabelapreco->aliquota));
			$arr_[3] .= tag('td',$tabelaprecovalores->calculo($tabelapreco->ajuste_3,$item->custo,$tabelapreco->aliquota));
			$arr_[4] .= tag('td',$tabelaprecovalores->calculo($tabelapreco->ajuste_4,$item->custo,$tabelapreco->aliquota));
		}
		
		$arr_[0]   .= '</tr>';
		$arr_[1]   .= '</tr>';
		$arr_[2]   .= '</tr>';
		$arr_[3]   .= '</tr>';
		$arr_[4]   .= '</tr>';
		
		$tabela   .= '<table border="1" cellspacing="0" cellpading="0" style="width:100%;">';
		foreach($arr_ as $key=>$linha){
			$tabela .= $linha;
		}		
		$tabela   .= '</table>';
			
		  
		print $tabela;
	}
	
	public function buscaFornecedor(){
		$item_id = $_REQUEST['item'];
		
		$t = new Template('tpl.par-busca-fornecedor.html');
		
		// Categoria
		foreach(categoria::opcoes(0,$item_id) as $key=>$value){
			
			$t->categoria = new categoria($key);
			
			foreach(categoria::opcoes($key,$item_id) as $_key=>$_value){
				$t->sub_categoria = new categoria($_key);
				$t->parseBlock('BLOCK_SUB_CATEGORIA',true);
			}
			$t->parseBlock('BLOCK_CATEGORIA',true);
				
		}
		
		// Gravacao
		foreach(caracvalor::GravacoesOpcoes() as $key=>$value){
			if($item_id){
				$itemcarac = new itemcarac(array('item_id'=>$item_id,'caracvalor_id'=>$key));
				if($itemcarac->id){
					$t->gravacao = new gravacao($key);
					$t->parseBlock("BLOCK_GRAVACAO",true);
				}
			}else{			
				$t->gravacao = new gravacao($key);
				$t->parseBlock("BLOCK_GRAVACAO",true);
			}
		}
		
		// Materia Prima
		foreach(caracvalor::MateriaPrimaOpcoes() as $key=>$value){
			if($item_id){
				$itemcarac = new itemcarac(array('item_id'=>$item_id,'caracvalor_id'=>$key));
				if($itemcarac->id){
					$t->materia_prima = new gravacao($key);
					$t->parseBlock("BLOCK_MATERIA_PRIMA",true);
				}
			}else{			
				$t->materia_prima = new gravacao($key);
				$t->parseBlock("BLOCK_MATERIA_PRIMA",true);
			}
		}
		
		$t->item_id = $item_id;
		$t->ajax    = AJAX;
		$t->path    = PATH_SITE;

		echo $t->getContent();
	}
	
	public function Cotacao(){
		$item  = new item(intval($_REQUEST['item']));
		
		$t = new Template('tpl.part-cotacao.html');
		
		// Gravacao
		foreach(caracvalor::GravacoesOpcoes() as $key=>$value){
			if($item->id){
				$itemcarac = new itemcarac(array('item_id'=>$item->id,'caracvalor_id'=>$key));
				if($itemcarac->id){
					$t->gravacao = new gravacao($key);
					$t->parseBlock("BLOCK_GRAVACAO",true);
				}
			}else{			
				$t->gravacao = new gravacao($key);
				$t->parseBlock("BLOCK_GRAVACAO",true);
			}
		}
		
		// Materia Prima
		foreach(caracvalor::MateriaPrimaOpcoes() as $key=>$value){
			if($item->id){
				$itemcarac = new itemcarac(array('item_id'=>$item->id,'caracvalor_id'=>$key));
				if($itemcarac->id){
					$t->materia_prima = new gravacao($key);
					$t->parseBlock("BLOCK_MATERIA_PRIMA",true);
				}
			}else{			
				$t->materia_prima = new gravacao($key);
				$t->parseBlock("BLOCK_MATERIA_PRIMA",true);
			}
		}
		
		$t->item    = $item;
		$t->ajax    = AJAX;
		$t->path    = PATH_SITE;

		echo $t->getContent();
		//echo 'teste';
	}
	
	public function maisFornecedor(){
		$item_id = $_REQUEST['item'];
		
		$nome         = str_replace("'",'',$_REQUEST['nome']);
		$categoria    = $_REQUEST['categoria'];
		$materiaprima = $_REQUEST['materiaprima'];
		$gravacao     = $_REQUEST['gravacao'];
		
		$_arr = array();
		
		//$query = query("select distinct id, nome_fantasia from cadastro where tipocadastro_id = 6 and st_ativo = 'S' order by id");
		
		$query = query($sql="
			select distinct 
				cadastro.*
			from 
				cadastro 
			where 
				tipocadastro_id = 6 and st_ativo = 'S' 
				".($nome!=''?"AND (cadastro.nome like '%{$nome}%' OR cadastro.razao_social like '%{$nome}%' OR cadastro.nome_fantasia like '%{$nome}%' OR cadastro.email like '%{$nome}%' )":"")."
				".($categoria!=''?"AND (select COUNT(*) from fornecedorcategoria where fornecedorcategoria.categoria_id = {$categoria} and fornecedorcategoria.cadastro_id = cadastro.id)":"")."
				".($materiaprima!=''?"AND (select COUNT(*) from fornecedorcaracvalor where fornecedorcaracvalor.caracvalor_id = {$materiaprima} and fornecedorcaracvalor.cadastro_id = cadastro.id)":"")."
				".($gravacao!=''?"AND (select COUNT(*) from fornecedorcaracvalor where fornecedorcaracvalor.caracvalor_id = {$gravacao} and fornecedorcaracvalor.cadastro_id = cadastro.id)":"")."
				order by id;
		");
			 
		
		$dados = '<div style="border:0px solid;">';
		$dados = '<div style="height:400px; width:100%; overflow:scroll; overflow-y:visible; overflow-x:hidden;border:0px solid;">';
		$j = 0; 
		$zebra = true;
		
		while($fetch=fetch($query)){
			$fornecedor = new cadastro($fetch->id);
			$itemfornecedor = new itemfornecedor(array('item_id'=>$item_id,'cadastro_id'=>$fornecedor->id));
			
			$_inarray = false;
			if(array_key_exists('fornecedor',$_SESSION)){
				if(in_array($fornecedor->id,$_SESSION['fornecedor'])){
					$_inarray = true;
				}
			}
			
			if(!$itemfornecedor->id && !$_inarray){
				$dados .= tag('p style="width:100%; padding:4px; border:1px solid #ddd;'.($zebra?"background-color:#ecdecd;":"").'"',					
						"<span style='border:0px solid; display:block; text-align:left;'>
							<input type='checkbox' class='fornecedor' id='fornecedor_{$fornecedor->id}'value='{$fornecedor->id}' name='fornecedor[{$fornecedor->id}]' /> 
							<label for='fornecedor_{$fornecedor->id}'>
								<strong>{$fornecedor->nome_fantasia}</strong> <br /><span style='font-size:9px;margin-left:26px;'>{$fornecedor->email}</span>
							</label>
						</span>"					
					);
				$j++;
				$zebra=!$zebra;
			}			
		}
		
		$dados .='</div>';			
		$dados .='</div>';			
		
		echo $dados;		
	}
	
	
	public function maisFornecedorCotacao(){
		$item_id = $_REQUEST['item'];
		
		$_arr = array();
		
		//$query = query("select distinct id, nome_fantasia from cadastro where tipocadastro_id = 6 and st_ativo = 'S' order by id");
		
		$query = query($sql="
			select distinct cadastro.* 
			from cadastro
			inner join itemfornecedor ON( 
			itemfornecedor.item_id = {$item_id} AND
			itemfornecedor.cadastro_id = cadastro.id 
			)
			where cadastro.st_ativo = 'S' 
			order by cadastro.id;
		");
		
		$dados = '<div style="border:0px solid;">';
		$dados = '<div style="height:400px; width:100%; overflow:scroll; overflow-y:visible; overflow-x:hidden;border:0px solid;">';
		$j = 0; 
		$zebra = true;
		
		while($fetch=fetch($query)){			
			$fornecedor = new cadastro($fetch->id);
			$dados .= tag('p style="width:100%; padding:4px; border:1px solid #ddd;'.($zebra?"background-color:#ecdecd;":"").'"',					
					"<label>
						<span style='border:0px solid; display:block; text-align:left;'>						
							<input type='checkbox' class='fornecedor' id='fornecedor_{$fornecedor->id}'value='{$fornecedor->id}' name='fornecedor[{$fornecedor->id}]' /> 						
							<strong>{$fornecedor->nome_fantasia} </strong> <br /><span style='font-size:9px;margin-left:26px;'>{$fornecedor->email}</span>
						</span>
					</label>"					
				);
			$j++;
			$zebra=!$zebra;
					
		}
		
		$dados .='</div>';			
		$dados .='</div>';			
		
		echo $dados;		
	}
	
	public function addMaisFornecedores(){
		$arr     = $_REQUEST['_arr'];
		$item_id = $_REQUEST['item_id'];
		
		$_arr = array();
		
		$arr  = explode(',', $arr);			
		$data = '';
		for($i=0; $i<sizeof($arr); $i++){
			$fornecedor = new cadastro($arr[$i]);
	
			$itemfornecedor = new itemfornecedor(array('item_id'=>$item_id,'cadastro_id'=>$fornecedor->id));
			if(!$itemfornecedor->id){
				$itemfornecedor->item_id     = $item_id;
				$itemfornecedor->cadastro_id = $fornecedor->id;
				$itemfornecedor->salva();
			}
			
			$tFornecedor = new Template('tpl.part-fornecedor-produto.html');
			$tFornecedor->fornecedor         = $fornecedor;
			$tFornecedor->itemfornecedor     = $itemfornecedor;
			
			if(!$itemfornecedor->item_id){
				$tFornecedor->parseBlock('BLOCK_NAO_SALVO');
				$tFornecedor->parseBlock('BLOCK_NAO_SALVO_2');
			}else{
				$tFornecedor->parseBlock('BLOCK_SALVO');
				$tFornecedor->parseBlock('BLOCK_SALVO_2');
			}
			
			$tFornecedor->parseBlock('BLOCK_REMOVER');
			
			
			//$tFornecedor->index = INDEX;
			$tFornecedor->path  = PATH_SITE;
			$tFornecedor->ajax  = AJAX;
					
			$data .= $tFornecedor->getContent();			
		}
		
		///// criar sessao
		$_SESSION['fornecedor'] = $arr;
		//
		
		echo $data;
		die();
	}
	
	public function enviarCotacao(){
		//$arr           = $_REQUEST['_arr']; // fornecedores ids
		$fornecedor_id = $_REQUEST['fornecedor_id']; // fornecedores id
		
		$item          = new item(intval($_REQUEST['item_id']));		
		$materia_prima = new caracvalor(intval($_REQUEST['materia_prima']));
		$gravacao      = new caracvalor(intval($_REQUEST['gravacao']));
				
		// $_arr = array();		
		// $_arr  = explode(',', $arr);
		
		$t_email = new Template("tpl.email-cotacao.html");
		$t_email->item_nome = $item->nome;
		$t_email->imagem    = config::get('URL_COMPACTADA')."/img/produtos/1/{$item->imagem}";
		$t_email->quantidade    = intval($_REQUEST['quantidade']);
		$t_email->gravacao      = $gravacao->nome;
		$t_email->materia_prima = $materia_prima->nome;
		$t_email->observacao    = $_REQUEST['observacao'];
		$t_email->assinatura    = $_SESSION['CADASTRO']->assinatura;
		
		if($_REQUEST['quantidade']!="") $t_email->parseBlock('BLOCK_COT_QTD');
		if($gravacao->id) $t_email->parseBlock('BLOCK_COT_GRAVACAO');
		if($materia_prima->id) $t_email->parseBlock('BLOCK_COT_MATERIA');
		if($_REQUEST['observacao']!='') $t_email->parseBlock('BLOCK_COT_OBSERVACAO');
		
		
		$html = $t_email->getContent();
				
		$fornecedor = new cadastro($fornecedor_id);				
		
		$email = new email();			
		$email->addTo($fornecedor->email, "{$fornecedor->nome}");
		$email->addHtml($html);
		$email->send("Solicitacao de Cotacao - AfricaBrindes");
		
		$cotacaohistorico = new cotacaohistorico();
		$cotacaohistorico->item_id       = $item->id;
		$cotacaohistorico->fornecedores  = $fornecedor->id;
		$cotacaohistorico->qtd_item      = intval($_REQUEST['quantidade']);
		$cotacaohistorico->gravacao      = $gravacao->nome;
		$cotacaohistorico->materiaprima  = $materia_prima->nome;
		
		$cotacaohistorico->salva();
		
		/*for($i=0; $i<sizeof($_arr); $i++){	 // remover looping		
			$fornecedor = new cadastro($_arr[$i]);				
			
			$email = new email();			
			$email->addTo($fornecedor->email, "{$fornecedor->nome}");
			$email->addHtml($html);
			$email->send("Solicitacao de Cotacao - AfricaBrindes");
			
			$cotacaohistorico = new cotacaohistorico();
			$cotacaohistorico->item_id       = $item->id;
			$cotacaohistorico->fornecedores  = $fornecedor->id;
			$cotacaohistorico->qtd_item      = intval($_REQUEST['quantidade']);
			$cotacaohistorico->gravacao      = $gravacao->nome;
			$cotacaohistorico->materiaprima  = $materia_prima->nome;
			
			$cotacaohistorico->salva();
		}	*/

		echo "ok!";
	}
	
	public function excluirFornecedor(){
		$id = $_REQUEST['_id'];
		
		$itemfornecedor = new itemfornecedor(intval($id));
		
		if($itemfornecedor->id){			
			$itemfornecedor->exclui();
			echo 1;
		}else{
			echo 0;
		}	
		die();
	}
	
	public function salvaItemFornecedorCompra(){
		$indice = $_REQUEST['_indice'];
		$dados  = $_REQUEST["dados"];
		
		$_obj = new stdClass;
		foreach($dados as $key=>$value){
			$_obj->$value['name'] = $value['value'];
		}
		
		$itemfornecedorcompra = new itemfornecedorcompra();
		
		$itemfornecedor_id   = "itemfornecedorcompra_{$_obj->id}[{$indice}][itemfornecedor_id]";
		$data_entrada        = "itemfornecedorcompra_{$_obj->id}[{$indice}][data_entrada]";
		$quantidade          = "itemfornecedorcompra_{$_obj->id}[{$indice}][quantidade]";
		$valor_unitario      = "itemfornecedorcompra_{$_obj->id}[{$indice}][valor_unitario]";
		$valor_total         = "itemfornecedorcompra_{$_obj->id}[{$indice}][valor_total]";
		
		$itemfornecedorcompra->itemfornecedor_id  = $_obj->$itemfornecedor_id;
		$itemfornecedorcompra->data_entrada       = $_obj->$data_entrada;
		$itemfornecedorcompra->quantidade         = $_obj->$quantidade;
		$itemfornecedorcompra->valor_unitario     = $_obj->$valor_unitario;
		$itemfornecedorcompra->valor_total        = $_obj->$valor_total;
			
			
		$erro = '';
		if($_obj->$quantidade>0 && $_obj->$valor_unitario!=''){
			$itemfornecedorcompra->salva();
		}else{
			if(!$_obj->$quantidade){
				$erro .= '<script>$(document.getElementsByName("itemfornecedorcompra_'.$_obj->id.'['.$indice.'][quantidade]")).css("border","1px solid red");</script>';
			}
			if(!($_obj->$valor_unitario!='')){
				$erro .= '<script>$(document.getElementsByName("itemfornecedorcompra_'.$_obj->id.'['.$indice.'][valor_unitario]")).css("border","1px solid red");</script>';
			}		
		}		
		
		$out = array();
		
		if($itemfornecedorcompra->id){
			$linha = tag('tr height="25px" id="itemfornecedorcompra_linha_'.$itemfornecedorcompra->id.'"' , tag('td',$itemfornecedorcompra->getDataEntradaFormatada()).tag('td',$itemfornecedorcompra->quantidade).tag('td',$itemfornecedorcompra->getValorUnitarioFormatado()).tag('td',$itemfornecedorcompra->getValorTotalFormatado().'<span id="comandos_itemfornecedorcompra_'.$itemfornecedorcompra->id.'" style="border:0px solid; float: right;padding-right:30px;"><span onclick="javascript: excluirCompra('.$itemfornecedorcompra->id.');" class="exluir_ifc">X</span></span>'));		
			$out[0] = 1;
			$out[1] = $linha;
			echo json_encode($out);
		}else{
			$out[0] = 0;
			$out[1] = $erro;
			echo json_encode($out);
		}
	}
	
	public function excluirItemFornecedorCompra(){
		$id = request('_indice');
		$itemfornecedorcompra = new itemfornecedorcompra($id);
		if($itemfornecedorcompra->exclui()){
			echo 1;
		}else{
			echo 0;
		}
	}
	
	public function buscaItens(){
		$item_id    = request('item_id');
		$referencia = request('referencia');
		$nome       = request('nome');
		$categoria  = request('categoria');
		
		$where  = "where 1=1";

		$consulta = false;
		
		if($item_id){
			$where .= " AND item.id = {$item_id}";
			$consulta = true;
		}
		
		if($referencia){
			$where .= " AND referencia LIKE'%{$referencia}%'";
			$consulta = true;
		}
		
		if($nome){
			$where .= " AND nome LIKE'%{$nome}%'";
			$consulta = true;
		}
		
		if($categoria){
			$where .= " AND EXISTS(SELECT itemcategoria.* FROM itemcategoria WHERE itemcategoria.categoria_id = {$categoria} AND itemcategoria.item_id = item.id)";
			$consulta = true;
		}
		//echo $item_id." > ".$referencia." > ".$nome;
		// echo $categoria;
		// die();
	
	
		if($consulta){
			$query = query($sql="SELECT 
									item.*
								FROM 
									item 
								{$where}
								");
		}
		
		// echo $sql;
		// die();
		
		$itens = array();
		
		while($fetch=fetch($query)){
			$fetch->cor_select      = 'Cor :'.select_script('add_item_cor['.$fetch->id.']', '','', cor::opcoesByItemID($fetch->id), true, 'onchange="javascript: mudaItemImg('.$fetch->id.', this.value);"');
			$fetch->gravacao_select = 'Graca&ccedil;&atilde;o :'.select('add_item_gravacao['.$fetch->id.']', '','', gravacao::opcoesByItemID($fetch->id), true);
			//$fetch->cor_select = select('add_item_cor['.$fetch->id.']', '','', cor::opcoesByItemID($fetch->id));
			$itens[]           = $fetch;
		}
		
		//echo json_encode(array('sql'=>$sql));
		echo json_encode($itens);
	}
	
	public function trocaIamgem(){
		$item_id = $_REQUEST['item_id'];
		$cor_id  = $_REQUEST['cor_id'];
		
		$item    = new item($item_id);
		
		$itemcor = new itemcor(array('item_id'=>$item_id, 'cor_id'=>$cor_id));
		if($itemcor->id){
			echo PATH_SITE.'img/produtos/1/'.$itemcor->imagem;
		}else{
			echo PATH_SITE.'img/produtos/1/'.$item->imagem;
		}
	}
	
	public function precoItem(){
		$item_id            = intval(request('item_id'));
		$qtd                = intval(request('qtd'));
	
		$item               = new item($item_id);
		$tabelapreco        = new tabelapreco($item->tabelapreco_id);
		
		$tabelaanterior     = new tabelaprecovalores();
		$preco_item         = 0;
		

		foreach($tabelapreco->get_childs('tabelaprecovalores') as $key=>$tabelaprecovalores){
			if((intval($tabelaanterior->qtd) <= $qtd) && ((intval($tabelaprecovalores->qtd)-1) >= $qtd)){
				$preco_item = $tabelaanterior->calculo($tabelapreco->ajuste_1,$item->custo, $tabelapreco->aliquota);
			}			
			$tabelaanterior = $tabelaprecovalores;
		}		
		
		if((intval($tabelaanterior->qtd) <= $qtd)){
			$preco_item = $tabelaanterior->calculo($tabelapreco->ajuste_1,$item->custo, $tabelapreco->aliquota);
		}
		
		print $preco_item;
		die();
	}
	
	public function prevImgSplashe(){
		$splash_id = $_REQUEST['splash_id'];		
		$splash = new splash($splash_id); 		
		
		if($splash->id){
			echo PATH_SITE."img/splash/".$splash->imagem;		
		}
	}
	
	public function envioCampanhas(){
		$nome            = $_REQUEST['nome'];
		$email           = $_REQUEST['email'];
		$campanha_id     = $_REQUEST['campanha_id'];
		
		$newscampanha    = new newscampanha($campanha_id);
		                 
		$file_html       = new Template(DIRETORIO_CAMPANHA_HTML.$newscampanha->html);

		$email           = new email();
		
		$email->addTo($email, $nome);
		$email->addHtml($file_html);
		$email->send($newscampanha->assunto);
		
		return 1;
	}
	/**Retorna o endere�o com base no cep**/
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
	
	public function buscaCliente(){
		$nome         = '';
		$razao_social = '';
		$email        = '';
	
		if(isset($_REQUEST['nome'])){
			$nome          = $_REQUEST['nome'];
		}
		if(isset($_REQUEST['razaosocial'])){
			$razao_social  = $_REQUEST['razaosocial'];
		}
		if(isset($_REQUEST['email'])){
			$email         = $_REQUEST['email'];
		}
		
		$_or1='';
		$_or2='';
		
		if($nome!='' && $razao_social!=''){
			$_or1 = 'AND';
		}
		
		if(($nome!='' && $email!='')||($razao_social!='' && $email!='')){
			$_or2 = 'AND';
		}
	
		$arr = array();
		
		$sql = "SELECT 
					* 
				FROM 
					cadastro 
				WHERE 
					tipocadastro_id = 2 
					AND ( 
						".($nome!=""?" nome LIKE '%{$nome}%'":"")." 
							{$_or1}
						".($razao_social!=""?" razao_social LIKE '%{$razao_social}%'":"")."
							{$_or2}
						".($email!=""?" email LIKE '%{$email}%'":"")."
					)";
		
		$query = query($sql);
		$zebra = false;
		$arr[] = "<table class='cliente_busca_item_titulo'><tr><th>Nome</th><th>Raz&atilde;o Social</th><th>E-mail</th></tr></table><br />";
		while($fetch=fetch($query)){
			$arr[] = "<table onclick='javascript: addCliente(".json_encode($fetch).");' class='cliente_busca_item ".($zebra?'cliente_busca_item_zebra':'')."'><tr><td>{$fetch->nome}</td><td>{$fetch->razao_social}</td><td>{$fetch->email}</td></tr></table><br />";
			$zebra = !$zebra;
		}
		
		echo json_encode($arr);
	}

	public function addItemAmostra($_dados=''){		
		$array_itens = array();
		$_amostra_id = 0;
		
		if($_dados !=''){
			$dados       = $_dados;
			$array_itens = $dados;
			$_amostra_id = $array_itens['amostra_id'];
		}else{
			$dados       = $_REQUEST['dados'];
			$array_itens = json_decode($dados);
			$_amostra_id = $array_itens->amostra_id;
		}
		
		//print_r($dados);
		// echo json_decode($dados)->amostra_id;
		//die();
		
		$amostra = new amostra($_amostra_id);
		$_arr    = array();
		$i=0;
		foreach($array_itens as $key=>$value){
			if(is_array($value)){
				$value = (object)$value;
			}
			if($key!='amostra_id'){
				$item        = new item($value->id);			
				$amostraitem = new amostraitem(array('item_id'=>$item->id, 'amostra_id'=>$amostra->id));
				
				if(!$amostraitem->id){
					$itemcor    = new itemcor(array('cor_id'=>$value->cor_id,'item_id'=>$item->id));
					$cor        = new cor($value->cor_id);
					
					$caracvalor = new caracvalor($value->gravacao_id);
				
					$amostraitem->item_id    = $item->id;
					$amostraitem->amostra_id = $amostra->id?$amostra->id:null;
					
					$amostraitem->observacao = ($cor->id?"<strong>Cor:</strong>&nbsp;{$cor->nome}<br />":"").''.($caracvalor->id?"<strong>Grava&ccedil;&atilde;o:</strong>{$caracvalor->nome}":"");
					$amostraitem->imagem     = $itemcor->imagem!=""?$itemcor->imagem:$item->imagem;					
					
					if($amostra->id){
						$amostraitem->salva();
						
						if($amostraitem->id){
							$imagem = PATH_SITE."img/produtos/1/".($itemcor->id?$itemcor->imagem:$item->imagem);
							$status = $item->st_ativo=='S'?'Ativo':'Inativo';							
							$_arr[] = "<tr id='item_{$item->id}'><td><input type='hidden' name='item_mostra[{$item->id}]' value='{$amostraitem->id}' />{$item->id}</td><td><img src='{$imagem}' /></td><td>{$amostraitem->observacao}</td><td>{$item->referencia}</td><td>{$item->nome}</td><td>{$item->getDescricaoListagem()}</td><td>{$status}</td><td><span style='cursor:pointer;' onclick='javascript: $(\"#item_{$item->id}\").remove();'>REMOVER</span></td></tr>";
						}
					}else{
						// $unico = 0;
						// $size = $i;
						
						// if(array_key_exists('amostraitem',$_SESSION) && $i==0){
							// foreach($_SESSION['amostraitem'] as $key=>$value){
								// $obj = $_SESSION['amostraitem'][$key];
								// if($amostraitem->item_id == $obj->item_id){
									// $unico = 1;
								// }
							// }
							// $size = sizeof($_SESSION['amostraitem'])+1;
						// }
						
						
						$_SESSION['amostraitem'][$amostraitem->item_id] = $amostraitem;
						$imagem = PATH_SITE."img/produtos/1/".($itemcor->id?$itemcor->imagem:$item->imagem);
						$status = $item->st_ativo=='S'?'Ativo':'Inativo';							
						$_arr[] = "<tr id='item_{$item->id}'><td><input type='hidden' name='item_mostra[{$item->id}]' value='{$amostraitem->id}' />{$item->id}</td><td><img src='{$imagem}' /></td><td>{$amostraitem->observacao}</td><td>{$item->referencia}</td><td>{$item->nome}</td><td>{$item->getDescricaoListagem()}</td><td>{$status}</td><td><span style='cursor:pointer;' onclick='javascript: $(\"#item_{$item->id}\").remove();'>REMOVER</span></td></tr>";
						
					}
				}
			}
			$i++;
		}
		
		echo json_encode($_arr);
		die();
	}
	
	public function buscaPropostaOrcamento(){
		$cadastro_id = $_REQUEST['cadastro'];
		$tipo        = $_REQUEST['tipo'];
		
		$cadastro = new cadastro($cadastro_id);
		
		if($cadastro->id){
		
			$return   = '';			
			
			if($tipo=='orcamento'){
				$cont = 0;
				$return .= '<select name="amostra[pedido_id]">';
				$return .= '<option value="0"></option>';
				foreach($cadastro->get_childs('pedido') as $key=>$value){
					$return .= "<option value='{$value->id}'>{$value->id}</option>";
					$cont++;
				}
				$return .= '</select>';
				
				if($cont>0){
					echo $return;
				}else{
					echo 0;
				}				
			}
			
			if($tipo=='proposta'){
				$cont = 0;
				$return .= '<select name="amostra[proposta_id]" id="amostra_proposta">';
				$return .= '<option value="0"></option>';
				foreach($cadastro->get_childs('pedido') as $_key=>$pedido){
					foreach($pedido->get_childs('proposta') as $key=>$value){
						$return .= "<option value='{$value->id}'>{$value->id}</option>";
						$cont++;
					}
				}
				$return .= '</select>';
				$return .= '<script>
						$("#amostra_proposta").bind("change onchange", function(){
							$("#additemamostra").html("<tr><th>ID</th><th>Imagem</th><th>Caracteristicas</th><th>Referencia</th><th>Nome</th><th>Descricao</th><th>Status</th><th>Remover</th></tr>");
							$.ajax({
								url   : "'.PATH_SITE.'ajax.php/buscaProdutosProposta/",
								data  : {proposta_id : this.value},
								dataType : "json",
								success: function(out){								
									//console.log(out);
									for(i=0;i<out.length; i++){
										$("#additemamostra").append(out[i]);
									}								
									//$("#fundo_additem").fadeOut();
								}
							});
						});
				</script>';
				
				if($cont>0){
					echo $return;
				}else{
					echo 0;
				}				
			}		
		}else{
			echo 0;
		}
		
	}
	
	public function buscaProdutosProposta(){
		$proposta_id = $_REQUEST['proposta_id'];
		
		$proposta    = new proposta($proposta_id);
		
		$arr_info    = unserialize($proposta->info);
		
		$dados = array();
		$dados['amostra_id'] = 0;
		foreach($arr_info['item'] as $key=>$value){
			$dados ["itemadd[{$value['item_id']}]"]= array('id'=>$value['item_id'],'cor_id'=>$value['cor_id'],'gravacao_id'=>$value['gravacao_id']);
		}
		
		// $dados = stdClass();
		// $dados->amostra_id = 0;
		// foreach($arr_info['item'] as $key=>$value){
			// $content_dados = stdClass();
			
			// $content_dados->id          = $value['item_id'];
			// $content_dados->cor_id      = $value['cor_id'];
			// $content_dados->gravacao_id = $value['gravacao_id'];
			
			// $dados->$value['item_id']   = $content_dados;
		// }
		
		$this->addItemAmostra($dados);
	}
	
	public function addEstados(){
		$cont = $_REQUEST['Cont'];
		$nome = $_REQUEST['Nome'];
		$return = '';
		
		$select_estado = "<select name='{$nome}' id='{$nome}' onchange='javascript: maisCidades(this.value, {$cont});'>";
		$select_estado .="<option value=''></option>";
		foreach(estado::opcao() as $key=>$value){
			$select_estado .= "<option value='{$key}'>{$value}</option>";
		}
		$select_estado .= "</select>";
		
		
		$return .= "<table id='e_c_{$cont}'><tr><td><p><label for='cidadesatuacaotransportadora_novo[{$cont}][estado]'>Estado:</label></p>{$select_estado}</td><td><p><label for='cidadesatuacaotransportadora_novo[{$cont}][cidade]'>Cidade:</label></p> <span id='cidade_{$cont}'></span> </td><td><a href='javascript: $('#e_c_{$cont}').remove();'>Remover</a><br /></td></tr></table>";
		//$return .= "<script>$(\'#cidadesatuacaotransportadora_novo[{$cont}][estado]\').bind(\'change\', function(){maisCidades(this.value)});</script>";
		
		echo $return;
	}
	
	public function addCidades(){
		$nome      = $_REQUEST['Nome'];
		$estado_id = $_REQUEST['Estado'];
		
		$return    = "<select name='{$nome}' id='{$nome}'>";
		foreach(cidade::opcao($estado_id) as $key=>$value){
			$return .= "<option value='{$key}'>{$value}</option>";
		}
		
		$return .= "</select>";
		echo $return;
	}
	//salva etapas
	public function salvaEtapas(){
		$info = serialize($_POST);
		
		$rascunho = new rascunho(array('modulo'=>$_POST['salva_etapa'], 'cad_id'=>$_POST['id']));
		
		if($_POST['salva_etapa']!='orcamentos'){
			if(!$rascunho->id){
				$rascunho = new rascunho($_POST['rascunho_id']);
			}
			
			$rascunho->modulo      = $_POST['salva_etapa'];
			$rascunho->cad_id      = $_POST['id'];
			$rascunho->cadastro_id = decode($_SESSION['CADASTRO']->id);
			$rascunho->info        = $info;
			
			$rascunho->salva();
			$rascunho->link   = PATH_SITE."admin.php/{$rascunho->modulo}/?action=editar&rascunho_id={$rascunho->id}&pop=1";
			$rascunho->atualiza();
			
			$out = new stdClass;
			$out->rascunho_id  = $rascunho->id;
			$out->qtd_rascunho = rows(query("SELECT * FROM rascunho WHERE cadastro_id = {$rascunho->cadastro_id}"));
			
			echo json_encode($out);
		}
	}	
	// envia emails
	public function sendEmail(){
		$html    = '';
		$assunto = '';
		// $mensagem_id = 0;
		// if(isset($_REQUEST['mensagem_id'])){
			// $mensagem_id = $_REQUEST['mensagem_id'];
		// }
		// $msgemail = new msgemail($mensagem_id);
		
		$mensagem_livre = '';
		if(isset($_REQUEST['mensagem_livre'])){
			$mensagem_livre = $_REQUEST['mensagem_livre'];
		}
		// print_r($_REQUEST);
		// die();
		$modulo  = $_REQUEST['modulo'];
		$e = new email();

		$obj      = new $modulo($_REQUEST['modulo_id']);
		$vendedor = new cadastro($_REQUEST['vendedor_id']);

		$e->addCc($vendedor->email, $vendedor->nome);
		
		
		if($modulo =='venda'){
			$obj->data_envio_email = bd_now();
			$obj->atualiza();
			$modulo='pedido';
		}else{
			if($modulo=="atividade"){
				//$obj->observacao = $obj->observacao." \n<br /> -- enviado: ".formata_datahora_br(bd_now()).", <br />msg.: ".$mensagem_livre."<br /><br />";
				//$obj->observacao = $mensagem_livre;				
			}
			
			if($modulo=='proposta'){
				$obj->propostastatus = 2;
			}
			
			$obj->data_envio = bd_now();
			//echo json_encode($obj);
			$obj->salva();
			//die();
		}
		
		// $rascunho = new rascunho(array('modulo'=>'proposta','cad_id'=>$obj->id));
		// if($rascunho->id){
			// $rascunho->exclui();
		// }
		
		$html = '';
		$txt = '';
		if(method_exists($obj,'preparaEmail')){
			$html     = $obj->preparaEmail();
		}
		$assunto  = ucfirst(strtolower($modulo));			
		//$txt      .= 'Segue proposta em anexo.';
		
		//Adiciona contatos 
		if(isset($_REQUEST['email_to'])){
			$emailTo = explode(';', $_REQUEST['email_to']);
			foreach($emailTo as $key=>$value){
				if(trim($value) != ''){
					$e->addTo($value);
				}
			}
			
			$cadastro = new cadastro(array('email'=>$emailTo[0]));
			if($cadastro->id){
				//$txt .= "Sr(a) {$cadastro->nome} <br />";
			}
		}	
		
		if(isset($_REQUEST['email_cc'])){
			$emailCc = explode(';', $_REQUEST['email_cc']);
			foreach($emailCc as $key=>$value){
				if(trim($value) != ''){
					$e->addCc($value);
				}
			}
		}
		if(isset($_REQUEST['email_bcc'])){
			$emailBcc = explode(';', $_REQUEST['email_bcc']);
			foreach($emailBcc as $key=>$value){
				if(trim($value) != ''){
					$e->addBcc($value);
				}
			}
		}
		//
		
		// Refresh
		$obj->refresh();
		
		if(isset($_REQUEST['send_anexo'])){
			//$txt .= "Conforme solicitado, segue or&ccedil;amento para o(s) produto(s) abaixo relacionado(s) com as respectivas condi&ccedil;&otilde;es comerciais<br />";			
			//$txt .= "Em ".date('d/m/Y').", enviamos o or&ccedil;amento anexo, estamos a disposi&ccedil;&atilde;o para maiores informa&ccedil;&otilde;es em caso de d&uacute;vidas. Aguardo retorno.<br />";			
			
			//add pdf
			
			if($modulo=='proposta'){
				$e->AddAttachment($this->criarPdf($html,"{$modulo}_{$obj->pedido_id}-{$obj->numero}"));
			}else{
				$e->AddAttachment($this->criarPdf($html,"{$modulo}_{$obj->id}"));
			}
			
			// $txt .= "Segue {$assunto} em anexo.";			
			// $txt .= "<br /><br />";
			
			if($mensagem_livre!=''){
				$txt .= nl2br($mensagem_livre);
				$txt .= "<br /><br />";
			}
			
			//$txt .= "{$msgemail->mensagem}<br />";
			
			$txt .= "{$vendedor->assinatura}";
			
			$e->addHtml($txt);
			//
		}else{		
			$aux = '';
			if($mensagem_livre!=''){
				$aux .= $mensagem_livre;
				$aux .= "<br /><hr style='border-color:#fefefe;' /><br />";
			}
			$txt = '<br />';
			$txt .= "{$vendedor->assinatura}";
			
			$e->addHtml($aux.$html.$txt);
		}
		
		
		$titulo = '';
		if(isset($obj->nome)){
			$titulo = $obj->nome;
		}
		
		if($modulo=="atividade"){
			$assunto .= $titulo." ({$obj->id})";
		}
		if($modulo=="proposta"){
			$assunto .= " ".$obj->pedido_id."-".$obj->numero;
		}
		if($modulo=="pedido"){
			$assunto .= " ({$obj->numero})";
		}
		
		$e->send("{$assunto}, ".$this->config->EMPRESA);
		//
		
		
		
		$out = new stdClass;
		$out->msg    = "<p style='width:140px;'>E-mail enviado com sucesso!</p>";
		$out->data   = '';
		if(method_exists($obj,'getDataEnvioFormat')){
			$out->data   = $obj->getDataEnvioFormat();
		}
		$out->status = '';		
		if(method_exists($obj, 'getStatus')){
			$out->status = $obj->getStatus();
		}
		$out->data_envio = $obj->data_envio;
		echo json_encode($out);
		//unset($_REQUEST);	
	}	
	//Criar Pdf
	public function criarPdf($html, $nome_arquivo){
		
		define('DOMPDF_ENABLE_AUTOLOAD', true);
		define('DOMPDF_ENABLE_REMOTE', true);


		$config = new config();
		
		$html = str_replace('src="'.strtolower($config->URL).'','src="',iconv("UTF-8","ISO-8859-1//TRANSLIT",$html));
		
		require_once 'dompdf/dompdf_config.inc.php';
		$dompdf = new DOMPDF();
		$dompdf->load_html($html);
		$dompdf->set_paper('a4', 'portrait');
		//$dompdf->set_paper('letter', 'landscape');
		$dompdf->render();
		
		$caminho = "pdf_files/{$nome_arquivo}.pdf";
		
		$pdf = $dompdf->output();
		
		if(file_put_contents($caminho, $pdf)){		
			return $caminho;
		}else{
			printr('Erro no file put contents');
		}
	}
	
	public function viewMensagemEmail(){
		$msg_id = $_REQUEST['id'];
		$cad_id = $_REQUEST['cad_id'];
		$proposta_id = $_REQUEST['proposta_id'];
		$venda_id = $_REQUEST['venda_id'];
		
		$msgemail = new msgemail($msg_id);
		$cadastro = new cadastro($cad_id);
		$proposta = new proposta($proposta_id);
		$venda    = new venda($venda_id);
		
		$prop_num = $proposta->id."-".$proposta->numero;
		
		$msgemail->mensagem = str_replace('|*NOME*|',$cadastro->nome,$msgemail->mensagem);
		$msgemail->mensagem = str_replace('|*RAZAO_SOCIAL*|',$cadastro->empresa,$msgemail->mensagem);
		$msgemail->mensagem = str_replace('|*NOME_FANTASIA*|',$cadastro->nome_fantasia,$msgemail->mensagem);
		$msgemail->mensagem = str_replace('|*DATA_ENVIO*|',$proposta->getDataEnvioFormat2(),$msgemail->mensagem);
		$msgemail->mensagem = str_replace('|*NUMERO_ORCAMENTO*|',$proposta->pedido_id,$msgemail->mensagem);
		$msgemail->mensagem = str_replace('|*NUMERO_PROPOSTA*|',$prop_num,$msgemail->mensagem);
		$msgemail->mensagem = str_replace('|*NUMERO_PEDIDO*|',$venda->numero,$msgemail->mensagem);
		$msgemail->mensagem = str_replace('|*NUMERO_ORCAMENTO_PROPOSTA*|',"Orcto. nr.: {$proposta->pedido_id} / Proposta: {$prop_num}",$msgemail->mensagem);
		
		echo json_encode($msgemail);
		
	}
	/**** DATA ****/	
	public function getCalendarioMes(){
		$mes = intval($_REQUEST['mes']);
		$ano = intval($_REQUEST['ano']);
		
		if($mes<1){
			$mes = 12;
			$ano--;
		}
		if($mes>12){
			$mes = 1;
			$ano++;
		}
		
		$out = array();
		$out[0] = $mes;
		$out[1] = $ano;
		
		if($mes<10){
			$mes ='0'.$mes;
		}
		
		
		require_once("crm/calendario.php");
		
	
		$out[2] =  MostreCalendario($mes, $ano,'dia');
		echo json_encode($out);
	}	
	
	public function agendaAtividadeAjax(){
		$dia = intval($_REQUEST['dia']);
		$mes = intval($_REQUEST['mes']);
		$ano = intval($_REQUEST['ano']);
		
		echo crm::agendaAtividade($dia, $mes, $ano);	
	}
	/**************/
	
	
	public function caixaEnviarEmail(){
		$cad_id   = $_REQUEST['cadastro_id'];
		$id       = $_REQUEST['modulo_id'];
		$modulo   = $_REQUEST['modulo'];
		
		$cadastro = new cadastro($cad_id);
		
		$edicao = '';
		// $query = query("SELECT * FROM contato WHERE cadastro_id = {$cadastro->id}");
		// $contatos ='';
		// $_cont = 0;
		// while($fetch=fetch($query)){
			// $contatos.= tag("span class='box_email_contato'","
			// <span class='email_contato'>".$fetch->email."</span>
			// <span class='bt_email_add' onclick='javascript: addTo{$id}(\"{$fetch->email}\");'>Para</span>
			// <span class='bt_email_add' onclick='javascript: addCc{$id}(\"{$fetch->email}\");'>Cc</span>
			// <span class='bt_email_add' onclick='javascript: addBcc{$id}(\"{$fetch->email}\");'>Bcc</span>
			// ");
			// $_cont++;
		// }
		
		
		$mensagens = '';
		$msg_cont = 0;
		
		$mensagens .= '<div class="bt_add_email" xonclick="javascript:$(\'#mensagem_'.$id.'\').slideToggle();">Mensagem Pronta</div>';
		
		$proposta = new proposta();
		if($modulo=='proposta'){
			$proposta = new proposta($id);
			//$mensagens .='<option value="">'.$proposta->getDataEnvioFormat().'</option>';
			//$cobranca = "Prezado (Sr)a. <br />Referente ao or&ccedil;amento enviado no dia ".$proposta->getDataEnvioFormat2().", algum posicionamento referente ao mesmo? <br /> Fico no aguardo.";
			//$mensagens .= '<div class="bt_add_email" onclick="javascript: $(\'#mensagem_livre_'.$id.'\').val(\''.$cobranca.'\');">Msg. de Cobran&ccedil;a</div><br clear="all" />';
		}
		$venda = new venda();
		if($modulo=='venda'){
			$venda = new venda($id);
			$proposta = new proposta($venda->proposta_id);
		}
		
		
		$mensagens .= '<div id="mensagem_'.$id.'" class="box_contatos" style="margin: 0px 0px 6px; padding: 0px; xdisplay: none; width: auto !important; float: left; border: none;">';
 		$mensagens .= '<select id="msg_opcao_'.$id.'" ">';
 		$mensagens .= '<option value="0">---</option>';
		foreach(msgemail::opcao() as $key=>$value){
			$mensagens .="<option value='{$key}'>{$value}</option>";
			$msg_cont++;
		}
		$mensagens .='</select>';
		$mensagens .= '<span id="msg_mensagens_'.$id.'" style="float:left; width:auto; display:inline-block; margin-right:20px;"></span>';
		$mensagens .= '</div>';
		
		
		$edicao .= tag('div id="caixa_destinatarios_'.$id.'" class="xxbox caixa_destinatario"',
			tag('div class="bt_fechar_pop" onclick=" fechaBoxEnvioEmail_'.$id.'();"','x')
			.'<form></form>
			<form name="SendForm_'.$id.'" id="SendForm_'.$id.'" method="POST" enctype="multipart/form-data">
				
				<fieldset id="formsend_'.$id.'">
					<table width="100%">
							<tr>
								<td>							
									<em style="font-size:9px !important;">'.$modulo.' - '.$id.'</em><br />
									'.inputHidden('vendedor_id',$cadastro->cadastro_id).'
									'.inputHidden('modulo_id',$id).'
									'.inputHidden('modulo',$modulo).'
									
									<strong>Para</strong><input type="text" id="email_to_'.$id.'" name="email_to" value="'.$cadastro->email.'" style="width: 100% !important; min-width: 500px; line-height:20px; margin:3px; display:inline-block;" />
									<br clear="all" />
									<strong>Add Cc </strong> :  <input type="text" id="email_cc_'.$id.'" name="email_cc" value=" " style="width: 100% !important; min-width: 500px; line-height:20px; margin:3px; display:inline-block;" />
									<br clear="all"/> 
									<strong>Add Bcc</strong> :  <input type="text" id="email_bcc_'.$id.'" name="email_bcc" value=" " style="width: 100% !important; min-width: 500px; line-height:20px; pading:3px; display:inline-block;" />	
									<br />
									<input type="hidden" name="mensagem_id" id="mensagem_id_'.$id.'" value="" />
									
									Obs.: Digite os e-mails separados por \';\'(ponto e virgula).
									<br />ex.: exemplo@exemplo.com.br ; exemplo2@exemplo2.com.br
									<br />
									
									'.''/*($_cont>0?'<br clear="all" /><div class="bt_add_email" onclick="javascript:$(\'#emails_contato\').slideToggle();">Adicionar Contatos</div><br clear="all" />
									<div id="emails_contato" class="box_contatos">Click em Para,Cc ou Bcc para adicionar o e-mail:<br clear="all" />'.$contatos.'</div>':'')*/.'
									
									<br />
									Mensagem:<br />
									<textarea name="mensagem_livre" id="mensagem_livre_'.$id.'" onkeyup="javascript: msg_anterio=this.value;" rows="7" style="width:100%; float:left;text-transform:none !important;">'.config::get('MENSAGEM_EMAIL').'</textarea>
									<br clear="all" />										
									
									 '.($msg_cont>0?$mensagens:'').'
									
									
									<br clear="all" />
									
								</td>
								<td style="width:120px !important; overflow:hidden; padding:10px;">
									<span id="titulo_msg_'.$id.'"></span>
									'.($modulo!='atividade'?tag('div',checkbox2('send_anexo','1','Como Anexo( PDF ):','checked')):'').'
									<span id="caixa_acao" class="caixa_acao">
										<input type="button" value="ENVIAR" class="bt_envia_email" id="bt_envia_email_'.$id.'" />
									</span>
								</td>
							</tr>
						</table>
					</fieldset>
								
				</form>'
		);
		
		$edicao .= tag('script','			

				'.($modulo=='atividade'?'$("#mensagem_livre_'.$id.'").val(document.getElementById("observacao_atv").value)':"").'
				
				var msg_anterio = $("#mensagem_livre_'.$id.'").val();
				
				$("#msg_opcao_'.$id.'").bind("change",function(){
					//$("#msg_mensagens_'.$id.'").html("<img src=\"'.PATH_SITE.'img/carregando.gif\" width=\"35px\" />");
					$.ajax({
						url : "'.AJAX.'viewMensagemEmail/",
						data : {id : this.value, cad_id : '.$cadastro->id.', proposta_id : '.$proposta->id.', venda_id : '.$venda->id.'},
						dataType : "json",
						success : function(out){
							if(out.id>0){		
								//$("#msg_mensagens_'.$id.'").html(out.mensagem);
								$("#mensagem_livre_'.$id.'").val(out.mensagem);
								$("#mensagem_id_'.$id.'").val(out.id);
								$("#titulo_msg_'.$id.'").html("Mensagem <em>"+out.titulo+"</em> adicionada.");
							}else{
								//$("#msg_mensagens_'.$id.'").html("");
								$("#mensagem_livre_'.$id.'").val(msg_anterio);
								$("#mensagem_id_'.$id.'").val(0);
								$("#titulo_msg_'.$id.'").html("");
							}
						}
					});
				});
		
				
				function fechaBoxEnvioEmail_'.$id.'(){
					$("#fancybox-close").show();
					bt_fecha_cont = 0;
					$(\'#caixa_destinatarios_'.$id.'\').remove();
				}
				
				
				$("#bt_envia_email_'.$id.'").bind("click", function(){
					enviarEmail();					
				});
				
				function enviarEmail(){
					btacao = $("#caixa_acao").html();
					$("#caixa_acao").html("<img src=\''.PATH_SITE.'img/carregando.gif\' width=\'60px\' height=\'60px\' />");
					
					_form = document.getElementById("SendForm_'.$id.'");
					
					var formObj  = $(_form);
					var formURL  = "'.PATH_SITE.'ajax.php/sendEmail/?"+$(_form).serialize();
					//var formData = new FormData(_form);
					//var formData = $(_form).serialize();
					//console.log(formURL);
					$.ajax({
						url: formURL,
						type: "POST",
						//data:  formData,
						contentType: false,
						cache: false,
						processData:false,
						success: function(data, textStatus, jqXHR)
						{							
							obj = JSON.parse(data);
							
							$("#data_envio_'.$id.'").html(obj.data);
							$("#h_data_envio_'.$id.'").val(obj.data);
							$("#h_data_envio_gerarped_'.$id.'").val(obj.data_envio);
							
							$("#status_envio_'.$id.'").html(obj.status);
							
							$("#caixa_acao").html(obj.msg);
							
							setTimeout(function(){$("#caixa_acao").html(btacao);
								$("#bt_envia_email_'.$id.'").bind("click", function(){
									enviarEmail();					
								})
							;},2400);
						},
						error: function(jqXHR, textStatus, errorThrown) 
						{}          
					});
				}
				
				
				function addTo'.$id.'(email){
					er = RegExp(email,"i");
					
					if(!er.exec($("#email_to_'.$id.'").val())){
					
						$("#email_to_'.$id.'").val($("#email_to_'.$id.'").val().replace(" ",""));
						
						if($("#email_to_'.$id.'").val()!=""){
							_val = $("#email_to_'.$id.'").val()+";"+email; 
						}else{
							_val = email; 
						}	
						$("#email_to_'.$id.'").val(_val);
					}
					
				}
				
				
				function addCc'.$id.'(email){	
					er = RegExp(email,"i");
					
					if(!er.exec($("#email_cc_'.$id.'").val())){
					
						$("#email_cc_'.$id.'").val($("#email_cc_'.$id.'").val().replace(" ",""));
						
						if($("#email_cc_'.$id.'").val()!=""){
							_val = $("#email_cc_'.$id.'").val()+";"+email; 
						}else{
							_val = email; 
						}	
						$("#email_cc_'.$id.'").val(_val);
					}
				}
				
				
				function addBcc'.$id.'(email){
					er = RegExp(email,"i");
					
					if(!er.exec($("#email_bcc_'.$id.'").val())){
					
						$("#email_bcc_'.$id.'").val($("#email_bcc_'.$id.'").val().replace(" ",""));
						
						if($("#email_bcc_'.$id.'").val()!=""){
							_val = $("#email_bcc_'.$id.'").val()+";"+email; 
						}else{
							_val = email; 
						}	
						$("#email_bcc_'.$id.'").val(_val);
					}
				}
				
		');
		
		echo $edicao;
	}
	
	/*****************/
	/* CAMPANHA NEWS */
	/*****************/
	public function carregaModel(){
		$model = request('model');
		$out = array();
		
		if($model==''){
			$out['status'] = 0;
			$out['msg'] = "Erro, nenhum dado informado.";
			echo json_encode($out);
			die();
		}
		
		if(!file_exists($model)){
			$out['status'] = 0;
			$out['msg'] = "Erro, nenhum arquivo encontrado.";
			echo json_encode($out);
			die();
		}
		
		$t = new Template("newsletter/tpl.base-campanhas-editar.html");
		$t->config = new config();
		$t->filtroprodutos = filtroProdutos();
		
		$tmodel = new Template($model);
		//$tmodel->edit = "edit_";
		$t->model_miolo = $tmodel->getContent();
		
		$out['status'] = 1;
		$out['msg'] = $t->getContent();
		echo json_encode($out);
		die();
	}
	
	public function buscaProdutos(){
		$referencia   = limpa("%".str_replace(' ','%',trim(request('referencia'))).'%');
		$nome         = limpa("%".str_replace(' ','%',trim(request('nome'))).'%');
		$categoria_id = request('categoria_id');
		
		$sql = "
			SELECT DISTINCT item.* FROM 
				item 
			INNER JOIN itemcategoria ON(
				itemcategoria.item_id = item.id
			)
			INNER JOIN categoria ON(
				categoria.id = itemcategoria.categoria_id
				AND categoria.st_ativo = 'S'
				".($categoria_id>0?"AND categoria.id = {$categoria_id}":"")."
			)
			WHERE 1=1 
			AND item.st_amamos = 'S'
			".($referencia!='%%'?"AND item.referencia LIKE '%{$referencia}%'":"")."
			".($nome!='%%'?"AND item.nome LIKE '%{$nome}%'":"")."
			ORDER BY item.nome
		";
		
		$query = query($sql);
		$return = array();
		$cont = 0;
		while($fetch=fetch($query)){
			$item = new item($fetch->id);
			$return[$cont] = tag("span class='b_item' data-id='{$item->id}'",
					"<img src='".PATH_SITE."timthumb/timthumb.php/?src=".PATH_SITE."img/produtos/1/{$item->imagem}&w=55' align='left' data-id='{$item->id}' />
					{$item->nome}<br />{$item->referencia}
					"				
					.tag("div style='display:none;' id='itemInfo_{$item->id}'",
						tag("div style='width:100%'",							
							"<img src='".PATH_SITE."timthumb/timthumb.php/?src=".PATH_SITE."img/produtos/1/{$item->imagem}&w=285' data-id='{$item->id}' />"
						)
					)
				);
			$cont++;
		}
		
		echo json_encode($return);
		die();
	}
	/*****************/
	/* FIM CAMPANHAS */
	/*****************/
	
	public function getItemVideo(){
		$src = request("src");
		$html = '<iframe width="600px" height="337px" src="'.decode($src).'" frameborder="0" allowfullscreen></iframe>';
		echo $html;
		die();
	}

	
	public function ajxCadastrar(){
		//printr('teste');
		if(!token_ok())die("Algo esta errado.");

		//printr(1);
		//$carrinho = new carrinho();
		$cadastro = new cadastro();
		//$endereco = new endereco();
		//$gateway  = new gateway();
		
		//printr($carrinho);
		//printr($cadastro);

		$cad = request('cadastro');
		//printr(3);
		if(!is_email($cad['email'])){
			$erros = array("email"=>"Digite seu e-mail");
		
			$out['status'] = 0;
			$out['msg']    = "Corrija os erros".':<br />'.join("<br />",$erros);
			$out['erros']  = $erros;
			//printr('emailx');
			echo json_encode($out);
			die();
		}else{
			//printr('maily');
			$cadastro = new cadastro(array("email"=>$cad['email'],"tipocadastro_id"=>tipocadastro::getId("Cliente")));
			if($cadastro->id){
				$_SESSION['CADASTRO_SEM_LOGIN'] = $cadastro;
				$out['status'] = 1;
				$out['msg']    = "Aguarde, sua solicita&ccedil;&atilde;o de or&ccedil;amento est&aacute; sendo finalizada.";
				$out['redireciona'] = INDEX."pedido_finaliza/";
			
				addnews($cadastro);
				setLogado($cadastro);
				//printr($out);
				echo json_encode($out);
				die();
			}
		}		
		$cadastro->set_by_array(request('cadastro'));
		
		$out = array();		
		$erros = array();
		
		//printr(2);
		if($cadastro->validaClienteCheckout($erros)){
			$cadastro->tipocadastro_id    = tipocadastro::getId("Cliente");
			$cadastro->st_ativo           = "S";
			$cadastro->tipo_pessoa        = "J";
			$cadastro->cadastro_id        = cadastro::vendedorPadrao();
			$cadastro->salva();       				
			if($cadastro->id){
				addnews($cadastro);
				setLogado($cadastro);
			}
		}
		
		if(sizeof($erros)>0){
			$out['status'] = 0;
			$out['erros']  = $erros;
			if(!request('full_cad')){
				$out['js_function'] = "$('.q_form_erro').attr('title','');$('.q_form_erro').removeClass('q_form_erro');$('#h_form').fadeIn();$('#c_btn').removeClass('cjs');$('#full_cad').val(1);closeMessagem();";
			}else{
				$out['msg']    = 'Corrija os erros.<br />'.join("<br />",$erros);
			}
		}else{
			$_SESSION['CADASTRO_SEM_LOGIN'] = $cadastro;
		

			$out['status'] = 1;
			$out['msg']    = "Aguarde, sua solicita&ccedil;&atilde;o de or&ccedil;amento est&aacute; sendo finalizada.";
			$out['redireciona'] = INDEX."pedido_finaliza/";
		
		}
		
		echo json_encode($out);
		die();
	}

	public function editSubCategoria(){
		$pai_id = intval(request("pai_id"));
		$id     = intval(request("id"));

		$categoria = new categoria($id);

		$edicao = '';
		
		$edicao .= "<div style='width:80%;padding:5px;margin:0 auto;' id='subcats_{$categoria->id}'>";

		$edicao .= inputHidden('cat_id', $categoria->id);
		$edicao .= inputHidden('subcategoria[categoria_id]', $pai_id);

		$edicao .= tag('div class="well"',
			tag("h1",$categoria->nome)
			.tag("table class='table'",
				tag("tr",
					tag("td style='width:10%;'",select('subcategoria[st_ativo]', $categoria->st_ativo, 'Ativo?:', array('S'=>'Sim','N'=>'Nao')))
					.tag("td",inputSimples('subcategoria[nome]', $categoria->nome, 'Nome:'))
				)
			)
		);

		/** SEO */
		$opts = array();        
		$opts["modelo"]    = "categoria";
		$opts["modelo_id"] = $categoria->id;
		$seopro = new seopro($opts);
		$edicao .= $seopro->getEdit();
		/** END SEO */


		$edicao .= tag("div class='well'",
			tag("span class='btn btn-primary' style='width:100%;' onclick='javascript:salvaSubCategoria{$categoria->id}()'","SALVAR")
			.tag("p id='sub_msg' class='alert'","&nbsp;")
		);

		$edicao .= tag("script","
			function salvaSubCategoria{$categoria->id}(){				
				data = $('#subcats_{$categoria->id} *').serialize();
				$.ajax({
					url : '".AJAX."salvaSubCategoria/'
					,data : data
					,dataType : 'json'
					,success : function(out){
						$('#sub_msg').removeClass('alert-error').removeClass('alert-success').html('Aguarde ...');
						if(out.status){
							$('#sub_msg').removeClass('alert-danger').addClass('alert-success').html('Dados salvos com sucesso!'+out.erro);
							$('#sub_categorias_adm').append(out.html);
						}else{
							$('#sub_msg').removeClass('alert-success').addClass('alert-danger').html('Dados não salvos.'+out.erro);
						}
					}
				});
			}
		");

		$edicao .= "<div>";

		echo $edicao;

	}

	public function salvaSubCategoria(){
		$return = array("status"=>false,"erro"=>"");
		$has = false;
		$novo = true;
		if( request("subcategoria") ){
			$subcategoria = new categoria(intval(request("cat_id")));
			$subcategoria->set_by_array( request("subcategoria") );
			if($subcategoria->id)$novo=false;
			if($subcategoria->validaDados($erro)){
				if($subcategoria->salva()){
					
					$pai = new categoria($subcategoria->categoria_id);
					seopro::validaSalva(array("modelo"=>"categoria","modelo_id"=>$subcategoria->id)
						,array("metodo"=>"brindes","tag_nome"=>$subcategoria->tag_nome,"args"=>$pai->tag_nome) 
						,$erros
					);
					
					if($novo){
						$return["html"] =  tag("li id='sub_{$subcategoria->id}'",
							tag("span style='display:inline-block;' onclick='javascript: editSubCategoria(this)' data-pai_id={$subcategoria->categoria_id} data-id={$subcategoria->id}",$subcategoria->nome)
							.tag("a title='EXCLUIR' class='btn btn-danger' style='float:right;' onclick='javascript:excluirSubcategoria({$subcategoria->id})'", "X") );
					}
						$return["id"] = $subcategoria->id;
					if($erros!="")$return["erro"] .= $erros;
					$has = true;
				}
			}else{
				$return["erro"] .= join('<br />', $erro);
			}
		}

		$return['status'] = $has;
		echo json_encode($return);
	}

	public function excluiSubCategoria(){
		$id = intval(request("id"));
		if($id){
			query("DELETE FROM itemcategoria WHERE categoria_id = {$id}");
			query("DELETE FROM categoria WHERE categoria_id = {$id}");
			query("DELETE FROM seopro WHERE modelo = 'categoria' AND modelo_id = {$id}");
			query("DELETE FROM categoria WHERE id = {$id}");
			echo $id;
			die();
		}

		echo "";
	}
	
}

new UrlHandler(new AJAX());

?>