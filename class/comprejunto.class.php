<?php

// Modelo de dados item
class comprejunto extends base {

	var
		$id
		,$item1
		,$item2
	;

	public function __construct($item1, $item2){
		$this->item1 = $item1;
		$this->item2 = $item2;
	}
	
	// Retorna o link para um produto
	public function getLink(){
		return INDEX.'det/'.$this->id.'/'.$this->getUrlKeywords();
	}

	public function getPreco(){
		return $this->item1->preco + $this->item2->preco;
	}
	
	public function getPrecoDe(){
		return $this->item1->preco_de + $this->item2->preco_de;
	}
	
	public function getPrecoFormatado(){
		return money($this->getPreco());
	}

	public function getPrecoDeFormatado(){
		return money($this->getPrecoDe());
	}

	public function getPrecoBoleto(){
		$ret = $this->getPreco();
		$boleto = new formapagamento(array('formapagamento_tipo'=>'BB', 'st_ativo'=>'S'));
		if($boleto->id&&$boleto->porcentagem_desconto>0){
			$ret = $this->getPreco() - ($this->getPreco()/$boleto->porcentagem_desconto);
		}
		return $ret;
	}

	public function getPrecoBoletoFormatado(){
		return money($this->getPrecoBoleto());
	}
	
	public function getQtdBonus(){
	
		// printr('Item 1');
		// printr($this->item1->id);
		// printr($this->item1->qtd_bonus);
		
		// printr('<br />Item 2');
		// printr($this->item2->id);
		// printr($this->item2->qtd_bonus);
		
		// return 1;
		return ( intval($this->item1->qtd_bonus) + intval($this->item2->qtd_bonus) );
	}

	public function getMaximoParcelas(){
		return getMaximoParcelas($this->getPreco());
	}

	public function getValorMinimoParcela(){
		$parcelas = $this->getMaximoParcelas();
		return ($this->getPreco()/$parcelas);
	}

	public function getValorMinimoParcelaFormatado(){
		return money($this->getValorMinimoParcela());
	}

	public function getHtmlPreco1(){

		$return = '' ;
		$maximoParcelas = $this->getMaximoParcelas();

		if($this->getPreco() < $this->getPrecoDe()){
			/*'.$this->getLink().'*/
			$return .= '<span class="s-prod-valor"> <s> de R$ '.$this->getPrecoDeFormatado().'</s> por <a>R$ '.$this->getPrecoFormatado().'</a></span> <br>' ;
			$return .= '<span class="s-prod-valor-v">à vista: R$ '.$this->getPrecoBoletoFormatado().' no boleto </span> <br>' ;
		}
		else {
			$return .= '<span class="s-prod-valor"> por R$ '.$this->getPrecoFormatado().'</span> <br>' ;
			$return .= '<span class="s-prod-valor-v">à vista: R$ '.$this->getPrecoBoletoFormatado().' no boleto </span> <br>' ;
		}

		if($maximoParcelas>1){
			$return .= '<span class="s-prod-valor-parcelado"> ou em '.$maximoParcelas.'x de R$ '.$this->getValorMinimoParcelaFormatado().' no cartão</span> <br>';
		}
		return $return;
	}

	public function getHtmlPreco2(){

		$return = '' ;
		$maximoParcelas = $this->getMaximoParcelas();

		if(($maximoParcelas<=1) && ($this->getPreco() < $this->getPrecoDe())){
			$return .= '<span class="s-preco-detalhe"><s>de R$ '.$this->getPrecoDeFormatado().' </s>&nbsp;por R$'.$this->getPrecoFormatado().'</span>';
		}

		if(($maximoParcelas>1) && ($this->getPreco() < $this->getPrecoDe())){
			$return .= '<span class="s-preco-detalhe"><s>de R$ '.$this->getPrecoDeFormatado().' </s>&nbsp;por R$ '.$this->getPrecoFormatado().'<br>';
			$return .= 'ou em '.$maximoParcelas.'x de R$ '.$this->getValorMinimoParcelaFormatado().' no cartão </span>';
		}

		if($this->getPreco() < $this->getPrecoDe()){
			$return .= '<span class="s-preco-vista">à vista <br>  <span class="p-preco-vista"><span class="rs">R$</span><span class="p-preco-vista" id="spanPreco"> '.$this->getPrecoBoletoFormatado().' </span><span class="s-boleto">no boleto</span></span></span>';
		}
		else {
			$return .= '<span class="s-preco-detalhe">por R$ '.$this->getPrecoFormatado().'<br>';
			$return .= 'ou em '.$maximoParcelas.'x de R$ '.$this->getValorMinimoParcelaFormatado().' no cartão </span>';
			$return .= '<span class="s-preco-vista">à vista  <br>  <span class="p-preco-vista">R$ '.$this->getPrecoBoletoFormatado().' </span><span class="s-boleto"> no boleto </span>';
		}
		return $return;
	}

}

?>