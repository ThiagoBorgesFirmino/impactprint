<?php

// Modelo de dados para a tabela pedido
class pedido extends base {

	var
		$id
		//,$erp_id
		,$id_millenium
		,$pedidostatus_id
		,$formapagamento_id
		,$cadastro_id
		,$valepresente_id
		,$cupomdesconto_id
		,$parceiro_id
		,$site_id
		,$st_processado_erp
		,$dt_processado_erp
		,$obs
		,$sessao_ip
		,$valor_itens
		,$valor_frete
		,$valor_embalagem
		,$valor_desconto
		,$valor_total
		,$valor_pago
		,$valor_devido
		,$parcelas
		,$tipo_frete
		,$prazo_entrega
		,$destinatario
		,$logradouro
		,$numero
		,$complemento
		,$bairro
		,$cidade
		,$uf
		,$cep
		,$pagseguro_checkout_code
		,$pagseguro_checkout_date
		,$pagseguro_checkout_url
		,$data_cadastro
		//,$data_vencimento_boleto
		,$obs_loja
		,$pedidoorigem_id
		,$vendedor_id
			
		,$id_tray		
		;

	public function salva(){

		// throw new Exception("Pedido em manutenção");

		$novo = !$this->id;
		if(parent::salva()){

			//query("UPDATE pedido SET erp_id = '".config::get('MILLENIUM_FAIXA_PEDIDO')."' + id WHERE id = {$this->id}");

			if($novo){
				// if($this->cupomdesconto_id>0){
					// // Seta utlizacao do cupom de desconto
					// $cupomdesconto = new cupomdesconto($this->cupomdesconto_id);
					// $cupomdesconto->utilizou();
				// }

				// if($this->isBoleto()){
					// // 5 dias para vencimento
					// $this->data_vencimento_boleto = to_bd_date(date("d/m/Y", time() + (5 * 86400)));
					// $this->atualiza();
				// }

			}

			return true;

		}
		return false;
	}

	public function getTipoPagamentoClearSale(){
		$return = '';
		if($this->isBoleto()){
			$return = bclearsale::TIPOPAGAMENTO_BLOQUETOBANCARIO;
		}
		elseif($this->isCartaoCredito()){
			$return = bclearsale::TIPOPAGAMENTO_CARTAODECREDITO;
		}
		return $return;
	}

	public function getTipoCartaoClearSale(){

		$return = '';

		if($this->formapagamento_id==formapagamento::get('VISA')->id)  {
			$return = bclearsale::BANDEIRACARTAO_VISA;
		}
		elseif($this->formapagamento_id==formapagamento::get('MASTERCARD')->id)  {
			$return = bclearsale::BANDEIRACARTAO_MASTERCARD;
		}
		else {
			$return = bclearsale::BANDEIRACARTAO_OUTROS;
		}

		return $return;
	}

	public function getItensClearSale(){
		$return = array();
		foreach( $this->get_childs('pedidoitem') as $pedidoitem)
		{
			$tmp = new stdClass();

			$item = $pedidoitem->get_parent('item');

			$tmp->ID = $pedidoitem->item_id;
			$tmp->Nome = $item->nome;
			$tmp->Qtd = $pedidoitem->item_id;
			$tmp->Valor = $pedidoitem->item_preco;
			$tmp->Categoria = '';

			$return[] = $tmp;
		}

		return $return;
	}

	public function getFControlMetodoPagamento(){
		return "3" ; //
	}

	// Verifica se o pedido foi pago totalmente
	public function isPagoTotal(){
		return $this->valor_devido == 0;
	}

	// Verifica se o pedido foi enviado
	public function isEnviado(){
		$pedidoenvio = new pedidoenvio();
		$fetch = fetch(query($sql = "SELECT * FROM pedidoenvio WHERE pedido_id = {$this->id} ORDER BY id DESC"));
		if(@$fetch){
			$pedidoenvio->load_by_fetch($fetch);
		}
		return $pedidoenvio->id;
	}

	// Verifica se o pedido foi entregue
	public function isEntregue(){
		$pedidoentregue = new pedidoentregue();
		$fetch = fetch(query($sql = "SELECT * FROM pedidoentregue WHERE pedido_id = {$this->id} ORDER BY id DESC"));
		if(@$fetch){
			$pedidoentregue->load_by_fetch($fetch);
		}
		return $pedidoentregue->id;
	}

	// Verifica se o pedido é um valepresente ou nao
	public function isValePresente(){
		return (bool)(intval($this->valepresente_id)>0);
	}

	// Retorna se um pedido foi cancelado
	public function isCancelado(){
		$pedidostatus = new pedidostatus(array('chave'=>'CANCELADO'));
		return $this->pedidostatus_id == $pedidostatus->id;
	}
	
	public function isCancelavel(){
		$pedidostatus = new pedidostatus($this->pedidostatus_id);
		if($pedidostatus->chave=='PAGO' || $pedidostatus->chave=='SEPARADO' || $pedidostatus->chave=='ENVIADO' || $pedidostatus->chave=='FINALIZADO'){
			return false;
		}
		return true;
	}

	public function atualizaValores(){
		if($this->id){
			$valor_pago = floatval(@query_col("SELECT SUM(valor) FROM pedidopagamento WHERE pedido_id = {$this->id}"));
			$valor_devido = ($this->valor_itens + $this->valor_frete) - ($valor_pago + $this->valor_desconto);
			query("UPDATE pedido SET valor_pago = {$valor_pago}, valor_devido = {$valor_devido} WHERE id = {$this->id}");
		}
	}

	public function isBoleto(){
		return (
			$this->formapagamento_id==formapagamento::get('BOLETOBRADESCO')->id
		|| 	$this->formapagamento_id==formapagamento::get('BOLETOITAU')->id
		);
	}

	public function isCartaoCredito(){
		return (
			$this->formapagamento_id==formapagamento::get('VISA')->id
		||	$this->formapagamento_id==formapagamento::get('MASTERCARD')->id
		);
	}

	public function isPagSeguro(){
		return (
			$this->formapagamento_id==formapagamento::get('PAGSEGURO')->id
		);
	}

	public function isRedecard(){
		return (
			$this->formapagamento_id==formapagamento::get('DINERS')->id
			|| $this->formapagamento_id==formapagamento::get('MASTERCARDREDECARD')->id
		);
	}

	// Retorna se a forma de pagamento é deposito bancário ou não
	public function isDepositoBancario(){
		return (
			$this->formapagamento_id==formapagamento::get('DEPOSITOBANCARIO')->id
		);
	}

	// Retorna se a forma de pagamento é amex ou não
	public function isAmex(){
		return (
			$this->formapagamento_id==formapagamento::get('AMEX')->id
		);
	}

	// Retorna se a forma de pagamento é PAGAMENTODIGITAL ou não
	public function isPagamentoDigital(){
		return $this->formapagamento_id==formapagamento::get('PAGAMENTODIGITAL')->id;
	}

	// Retorna se a forma de pagamento é cielo (MASTERCARD/VISA)
	public function isCielo(){
		return (
			(
				$this->formapagamento_id==formapagamento::get('VISA')->id
			||	$this->formapagamento_id==formapagamento::get('MASTERCARD')->id
			)
		);
	}

	public function isBoletoVencido(){
		if($this->isBoleto()){
			if(!$this->isPagoTotal()){
				// if(rows("select ")>0)
			}
		}
	}

	public function getHtmlReimpressaoBoleto(){
		if($this->isBoleto()){
			return tag('a href="'.$this->getLinkBoleto().'" style="color:#bc272d;" target="_blank"','Clique aqui para re-imprimir o boleto') ;
		}
	}

	public function getDataHoraFormatada(){
		return formata_datahora_br($this->data_cadastro);
	}

	public function getDataHoraPagamentoFormatada(){
		$pedidopagamento = new pedidopagamento();
		$fetch = fetch(query($sql = "SELECT * FROM pedidopagamento WHERE pedido_id = {$this->id} ORDER BY id DESC"));
		if(@$fetch){
			$pedidopagamento->load_by_fetch($fetch);
		}
		return formata_datahora_br($pedidopagamento->data_pagamento);
	}

	public function getDataHoraEnvioFormatada(){
		$pedidoenvio = new pedidoenvio();
		$fetch = fetch(query($sql = "SELECT * FROM pedidoenvio WHERE pedido_id = {$this->id} ORDER BY id DESC"));
		if(@$fetch){
			$pedidoenvio->load_by_fetch($fetch);
		}
		return formata_datahora_br($pedidoenvio->data_envio);
	}

	public function getDataHoraEntregaFormatada(){
		$pedidoentregue = new pedidoentregue();
		$fetch = fetch(query($sql = "SELECT * FROM pedidoentregue WHERE pedido_id = {$this->id} ORDER BY id DESC"));
		if(@$fetch){
			$pedidoentregue->load_by_fetch($fetch);
		}
		return formata_datahora_br($pedidoentregue->data_entregue);
	}

	public function getDataHoraCanceladoFormatada(){
		$pedidocancelado = new pedidocancelado();
		$fetch = fetch(query($sql = "SELECT * FROM pedidocancelado WHERE pedido_id = {$this->id} ORDER BY id DESC"));
		if(@$fetch){
			$pedidocancelado->load_by_fetch($fetch);
		}
		return formata_datahora_br($pedidocancelado->data_cancelado);
	}

	public function getDataFormatada(){
		return formata_data_br($this->data_cadastro);
	}

	// Retorna o link para impressao do boleto
	public function getLinkBoleto(){

		$url = config::get('URL').INDEX.'boleto/'.encode(md5($this->id));
		if(IS_LOCAL=='1'){
			$url = str_replace(PATH_SITE.PATH_SITE,PATH_SITE,$url);
		}

		return $url;

	}

	public function getLinkPagSeguro(){
	
		$url = config::get('URL').INDEX.'ped_pagseguro/'.encode(md5($this->id));
		if(IS_LOCAL=='1'){
			$url = str_replace(PATH_SITE.PATH_SITE,PATH_SITE,$url);
		}

		return $url;
		// return $this->pagseguro_checkout_url;
	}

	// Retorna o link para finalizar o pagamento via redecard
	public function getLinkRedecard(){
		return str_replace(PATH_SITE, '', config::get('URL')).INDEX.'redecard/'.encode(md5($this->id));
	}

	// Retorna o link para finalizar o pagamento via amex
	public function getLinkAmex(){
		return str_replace(PATH_SITE, '', config::get('URL')).INDEX.'amex/'.encode(md5($this->id));
	}

	// Retorna o link para finalizar o pagamento via pagamento digital
	public function getLinkPagamentoDigital(){
	
		$url = config::get('URL').INDEX.'pagamentodigital/'.encode(md5($this->id));
		if(IS_LOCAL=='1'){
			$url = str_replace(PATH_SITE.PATH_SITE,PATH_SITE,$url);
		}
	
		return $url;
		return str_replace(PATH_SITE, '', config::get('URL')).INDEX.'pagamentodigital/'.encode(md5($this->id));
	}

	// Retorna link para pagamento cielo
	public function getLinkCielo(){
		return str_replace(PATH_SITE, '', config::get('URL')).INDEX.'cielo/'.encode(md5($this->id));
	}

	// Retorna o link direcionando para o historico de pedidos
	public function getLinkHistoricoPedidosAbsoluto(){
		return config::get('URL').INDEX.'us_acomp/';
	}

	public function getDataCadastroFormatado(){
		return formata_data_br($this->data_cadastro);
	}

	public function getDataVencimentoBoletoFormatado(){
		return formata_data_br($this->data_vencimento_boleto);
	}

	public function getQtdItens(){
		return intval(query_col("SELECT count(id) FROM pedidoitem WHERE pedido_id = {$this->id}"));
	}

	public function getQtdTotalItens(){
		return intval(query_col("SELECT sum(item_qtd) FROM pedidoitem WHERE pedido_id = {$this->id}"));
	}

	public function getQtdProdutos(){
		return intval(query_col("SELECT SUM(item_qtd) FROM pedidoitem WHERE pedido_id = {$this->id}"));
	}

	public function getValorTotal(){
		return $this->valor_total;
	}

	public function getValorTotalFormatado(){
		return money($this->valor_total);
	}

	public function getValorPagoFormatado(){
		return money($this->valor_pago);
	}

	public function getValorItensFormatado(){
		return money($this->valor_itens);
	}

	public function getValorProdutosFormatado(){
		return $this->getValorItensFormatado();
	}

	public function getValorDevidoFormatado(){
		return money($this->valor_devido);
	}

	public function getValorFreteFormatado(){
		return money($this->valor_frete);
	}

	public function getValorEmbalagemFormatado(){
		return money($this->valor_embalagem);
	}

	public function getFormapagamentoNome(){
		$formapagamento = new formapagamento($this->formapagamento_id);
		return $formapagamento->nome;
	}

	public function getPedidostatusNome(){
		$pedidostatus = new pedidostatus($this->pedidostatus_id);
		return $pedidostatus->descricao;
	}

	public function getFormapagamentoImagem(){
		$formapagamento = new formapagamento($this->formapagamento_id);
		// printr($formapagamento);
		return PATH_SITE.'img/formapagamento/'.$formapagamento->imagem;
	}

	public function getFormapagamentoImagemAbsoluto(){
		$formapagamento = new formapagamento($this->formapagamento_id);
		// printr($formapagamento);
		return config::get('URL').'img/formapagamento/'.$formapagamento->imagem;
	}

	public function getValorParcela(){
		return $this->valor_total/$this->parcelas;
	}

	public function getValorParcelaFormatado(){
		return money($this->getValorParcela());
	}

	public	function atualiza_pedido_status($pedido_status_id){
		return query( 'update pedido set pedido_status_id = ' . $pedido_status_id . ' where id = ' . $this->id ) ;
	}

	public	function get_referencia_itens() {
		$return = array();
		foreach ( $this->get_childs('pedido_item') as $pedido_item ){
			$return[] = $pedido_item->item_referencia;
		}
		return $return;
	}

	public function getValorDescontoFormatado(){
		return money($this->valor_desconto);
	}

	public function exclui(){
		/*foreach ($this->get_childs('pedido_item') as $pedido_item){
			$pedido_item->adiciona_estoque("Pedido {$this->id} excluído, retornado {$pedido_item->qtd} ao estoque");
		}*/
		$_SESSION['sucesso'] = tag('p','Pedido excluído com sucesso!');
		return $this->excluiPedido();
	}


	public function excluiPedido(){
		foreach ( $this->get_related_objects() as $obj ){
			$obj->exclui();
		}

		// $excluiEstoque = 'DELETE FROM estoquemov WHERE pedido_id = '.$this->id;
		// query($excluiEstoque);

		// $excluiTrilha = 'DELETE FROM pedidotrilha WHERE pedido_id = '.$this->id;
		// query($excluiTrilha);

		$excluiItem = 'DELETE FROM pedidoitem WHERE pedido_id = '.$this->id;
		query($excluiItem);

		$excluiPedido = 'DELETE FROM pedido WHERE id = '.$this->id;
		query($excluiPedido);
	}


	// Retorna o endereco de entrega formatado
	public function getEntregaFormatada(){
		return $this->getEntregaFormatada1();
	}

	// Retorna os "enters" setados como br
	public function getEntregaFormatada1(){

		return $this->destinatario.'<br />'
				.' '.$this->logradouro.', '.$this->numero.' '.($this->complemento!=''?' - '.$this->complemento:'').'<br />'
				.' '.$this->cep.' - '.$this->bairro.' - '.$this->cidade.' - '.$this->uf;

	}

	// Retorna os "enters" setados como \n
	public function getEntregaFormatada2(){

		return $this->destinatario."\n"
				.' '.$this->logradouro.', '.$this->numero.' '.($this->complemento!=''?' - '.$this->complemento:'')."\n"
				.' '.$this->cep.' - '.$this->bairro.' - '.$this->cidade.' - '.$this->uf;

	}

	public function atualizaPedidoStatus($pedidoStatusChave, $mandaEmail=false){

		if($this->id>0){

			$old = new pedidostatus($this->pedidostatus_id);
			$new = new pedidostatus(array('chave'=>$pedidoStatusChave));

			if($old->id && $new->id){

				$pedidotrilha = new pedidotrilha();

				$pedidotrilha->pedido_id = $this->id;
				$pedidotrilha->msg = "Atualizado de {$old->descricao} para {$new->descricao}";

				$pedidotrilha->salva();

				query("UPDATE pedido SET pedidostatus_id = {$new->id} WHERE id = {$this->id}");

			}
		}
	}

	public function atualizaPedidoStatusId($pedidoStatusId, $mandaEmail=false){

		if($this->id>0){

			$old = new pedidostatus($this->pedidostatus_id);
			$new = new pedidostatus($pedidoStatusId);

			if($old->id && $new->id){

				$pedidotrilha = new pedidotrilha();

				$pedidotrilha->pedido_id = $this->id;
				$pedidotrilha->msg = "Atualizado de {$old->descricao} para {$new->descricao}";

				$pedidotrilha->salva();

				query("UPDATE pedido SET pedidostatus_id = {$new->id} WHERE id = {$this->id}");

			}
		}
	}

	public function getContasDepositoBancario(){

		$ret = array();

		if($this->isDepositoBancario()){
			$formapagamento = new formapagamento($this->formapagamento_id);
			$ret = $formapagamento->getContasDepositoBancario();
		}

		return $ret;
	}

}
?>
