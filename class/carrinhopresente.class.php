<?php

class carrinhopresente {

	function __construct(){
		if ( ! @$_SESSION['S_CARRINHOPRESENTE']
			|| ! is_array($_SESSION['S_CARRINHOPRESENTE']) ){
			$_SESSION['S_CARRINHOPRESENTE'] = array();
		}
	}

	public function getValorTotal(){
		if($this->isValePresenteDefinido()){
			// printr(1);
			return $this->getValePresente()->valor;
		}
		// printr(2);
		return 0;
	}

	public function getValorTotalFormatado(){
		return money($this->getValorTotal());
	}

	public function getValorProdutos(){
		$return = 0;
		if($this->isValePresenteDefinido()){
			$return = $this->getValePresente()->valor;
		}
		return $return;
	}

	public function getValorProdutosFormatado(){
		return money($this->getValorProdutos());
	}

	// Retorna valor do desconto
	public function getValorDesconto(){
		$return = 0;
	}

	public function getValorDescontoFormatado(){
		return money($this->getValorDesconto());
	}

	// Retorna valor do frete
	public function getValorFrete(){
		$return = 0;
	}

	public function getValorFreteFormatado(){
		return money($this->getValorFrete());
	}

	// Define se o valepresente foi definido para esta compra
	public function isValePresenteDefinido(){
		// printr($_SESSION['S_CARRINHOPRESENTE']);
		if(array_key_exists('valepresente',$_SESSION['S_CARRINHOPRESENTE'])){
			return @$this->getValePresente()->valor>0;
		}
		return false;
	}

	// Associa o valepresente ao pedido
	public function setValePresente($valepresente){
		$_SESSION['S_CARRINHOPRESENTE']['valepresente'] = $valepresente;
	}
	
	// Retorna objeto do vale presente
	public function getValePresente(){
		// printr($_SESSION);
		if(array_key_exists('valepresente',$_SESSION['S_CARRINHOPRESENTE'])){
			return @$_SESSION['S_CARRINHOPRESENTE']['valepresente'];
		}
	}
	
	public function getTipoValePresente(){
		return new tipovalepresente($this->getValePresente()->tipovalepresente_id);
	}
	
	// Forma de pagamento
	public function setFormaPagamentoId($formapagamento_id){
		$_SESSION['S_CARRINHO']['formapagamento_id'] = $formapagamento_id;
	}

	public function getFormaPagamentoId(){
		return @$_SESSION['S_CARRINHO']['formapagamento_id'];
	}

	// Desconto para a forma de pagamento
	public function getValorDescontoFormaPagamento(){
		$ret = 0;
		$formapagamento = new formapagamento($this->getFormaPagamentoId());
		if($formapagamento->porcentagem_desconto>0){
			return $this->getValorProdutos() * ($formapagamento->porcentagem_desconto/100);
		}
		return $ret;
	}

	public function getValorDescontoFormaPagamentoFormatado(){
		return money($this->getValorDescontoFormaPagamento());
	}
	

	// Retorna o maximo de parcelas possiveis
	public function getMaximoParcelas(){
		return getMaximoParcelas($this->getValorTotal());
	}
	
	// Retorna o minimo a pagar por parcela
	public function getValorMinimoParcela(){
		$parcelas = $this->getMaximoParcelas();
		return ($this->getValorTotal()/$parcelas);
	}
	
	public function getValorMinimoParcelaFormatado(){
		return money($this->getValorMinimoParcela());
	}
	
	// Limpa o carrinho
	public function limpa(){
		$_SESSION['S_CARRINHOPRESENTE'] = array();
	}

	// Alias to limpa
	public function clear(){
		$this->limpa();
	}
}

?>
