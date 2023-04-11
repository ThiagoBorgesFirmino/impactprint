<?php

// Modelo de dados para lote
class lote extends base {

	var
		$id
		,$item_id
		,$st_estoquemov

		,$lote_codigo
		,$data_vencimento

		,$qtd
		,$qtd_saldo

		,$fornecedor
		,$localizacao
		,$nr_nota
		,$data_entrada_nota

		,$st_preco_ifbrindes
		,$preco_ifbrindes
		,$preco_antigo

		,$data_cadastro
	;

	public function salva(){

		$ret = false;
		if(parent::salva()){

			$ret = true;

			// Checa se ja gerou o lote
			if($this->qtd_saldo<=0){

				if($this->st_preco_ifbrindes == 'S'){

					// Tira o lote do estado de promoção
					$this->unsetPromocaoLote();
				}
			}
		}

		return $ret;

	}

	public function getPrecoifbrindesFormatado(){
		return money($this->preco_ifbrindes);
	}

	public function getPrecoAntigoFormatado(){
		return money($this->preco_antigo);
	}

	public function setPromocaoLote(){

		// Checa se o preco ifbrindes esta setado
		if(floatval($this->preco_ifbrindes)<=0){
			throw new Exception("Preço ifbrindes inválido");
		}

		// Checa se o preco ifbrindes ja esta configurado
		if($this->st_preco_ifbrindes == 'S'){
			throw new Exception("Este lote já está com o preço ifbrindes configurado");
		}

		// Checa se o lote tem saldo para alguma promocao
		if($this->qtd_saldo <= 0){
			throw new Exception("Este lote não tem saldo para ser colocado em promoção");
		}

		// Checa se o item já não está em promoção em outro lote
		$pesquisa = new lote(array('item_id'=>$this->item_id, 'st_preco_ifbrindes'=>'S'));
		if($pesquisa->id && $pesquisa->id != $this->id){
			throw new Exception("Já existe uma promoção de lote associada a esse item {$lote->lote_codigo} ({$lote->id})");
		}

		// Recupera dados do item
		$item = new item($this->item_id);

		// Seta dados no lote
		$this->st_preco_ifbrindes = 'S';
		$this->preco_antigo = $item->preco;
		$this->preco_ifbrindes = $this->preco_ifbrindes;
		$this->salva();

		// Salva novo preco no item
		$item->preco = $this->preco_ifbrindes;
		$item->salva();

		// Salva menor preco do item principal
		if($item->itemsku_id > 0){
			item::atualizaEstoquePrincipalByVariacao($item->itemsku_id);
		}
	}

	public function unsetPromocaoLote(){

		if($this->st_preco_ifbrindes == 'N'){
			throw new Exception("Este lote não está com o preço ifbrindes configurado");
		}

		// Recupera dados do item
		$item = new item($this->item_id);

		// Seta dados no lote
		$this->st_preco_ifbrindes = 'N';
		$this->salva();

		// Salva novo preco no item
		$item->preco = $this->preco_antigo;
		$item->salva();
		
		// Salva menor preco do item principal
		if($item->itemsku_id > 0){
			item::atualizaEstoquePrincipalByVariacao($item->itemsku_id);
		}
	}

	public function validaDados(&$erro=array()){

		$this->qtd = intval($this->qtd);
		$this->qtd_saldo = intval($this->qtd_saldo);

		$variacao = new item(array('itemsku_id'=>$this->item_id));

		if($this->item_id ==''){
			$erro[] = 'Digite o código do lote';
		}

		if($variacao->id){
			$erro[] = 'Esse item tem variações, dê entrada no lote pela variação e não pelo item principal';
		}

		if($this->lote_codigo==''){
			$erro[] = 'Digite o código do lote';
		}

		if($this->qtd_saldo > $this->qtd){
			$erro[] = 'A quantidade de saldo é maior que a quantidade do lote';
		}

		if($this->qtd == 0){
			$erro[] = 'Digite a quantidade de entrada do lote';
		}

		if(!butil::is_data($this->data_vencimento)){
			$erro[] = 'Digite a data de vencimento corretamente';
		}

		if($this->data_entrada_nota!='' && !butil::is_data($this->data_entrada_nota)){
			$erro[] = 'Digite a data de entrada da nota';
		}

		return sizeof($erro)==0;
	}

	public function isVencido(){
		$ret = false;
		if($this->data_vencimento != ''
		&& $this->data_vencimento != '0000-00-00' ){
			$dias = intval(query_col("SELECT DATEDIFF(data_vencimento, curdate()) dias FROM lote WHERE id = {$this->id}"));
			return $dias <= 0 ;
		}
		return $ret;
	}

	public function getDiasVencimento(){
		$ret = 0;
		if($this->data_vencimento != ''
		&& $this->data_vencimento != '0000-00-00' ){
			$dias = intval(query_col("SELECT DATEDIFF(data_vencimento, curdate()) dias FROM lote WHERE id = {$this->id}"));
			$ret = $dias;
		}
		return $ret;
	}

	public function getDiasVencidos(){
		if($this->isVencido()){
			// return
		}
	}

	public function getDataVencimentoFormatado(){
		return butil::formata_data_br($this->data_vencimento);
	}

	public function getDataEntradaNotaFormatado(){
		return butil::formata_data_br($this->data_entrada_nota);
	}

	// Executa exclusao
	public function exclui(){

		if($this->id>0){

			// Checa dependencias que impedem a exclusao
			if(rows(query("SELECT * FROM lotemov WHERE lote_id = {$this->id}"))>0){
				throw new Exception("Movimentações de lote relacionados, não é possível excluir");
			}

			// Executa exclusao
			return parent::exclui();

		}
	}

}

?>