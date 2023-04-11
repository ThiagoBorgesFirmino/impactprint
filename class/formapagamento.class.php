<?php

// Modelo de dados
class formapagamento extends base {

	var
		$id
		,$st_ativo
		,$st_fixo
		,$chave
		,$nome
		,$formapagamento_tipo
		,$agencia
		,$nr_conta
		,$descricao
		,$porcentagem_desconto
		,$imagem
		,$integrador
		,$st_digita_dados_site
	;

	static function get($chave){
		$formapagamento = new formapagamento(array('chave'=>$chave));
		return $formapagamento;
	}

	public function getValorCarrinho(){

		$ret = 0;

		$carrinho = new carrinho();
		$valor_total = $carrinho->getValorTotalSemDesconto();

		$ret = $valor_total;

		if($valor_total>0&&$this->porcentagem_desconto>0){
			$ret = $carrinho->getValorTotalSoProd($this->porcentagem_desconto);
			//printr($carrinho->getValorDescontoFormaPagamentoFormatado());
			$_SESSION['desconto_boleto'.$this->id] = $carrinho->getValorDescontoFormaPagamentoFormatado();
		}

		return $ret;
	}

	public function getValorDesconto(){

	}

	public function getValorDescontoTxt(){
		$ret = '';
		if($this->porcentagem_desconto>0){
			$ret = "<br /><span style='color:red'>{$this->porcentagem_desconto}% de desconto</span>";
		}
		return $ret;
	}

	public function getValorCarrinhoFormatado(){
		return money($this->getValorCarrinho());
	}

	public function getValorCarrinhoPresente(){

		$ret = 0;

		$carrinho = new carrinhopresente();
		$valor_total = $carrinho->getValorTotal();

		$ret = $valor_total;

		if($valor_total>0
		&& $this->porcentagem_desconto>0){
			return $valor_total - ($valor_total * ($this->porcentagem_desconto/100));
		}

		return $ret;

	}

	public function getValorCarrinhoPresenteFormatado(){
		return money($this->getValorCarrinhoPresente());
	}

	static function opcoes($categoria_id=0){

		$return = array();
		$query = query($sql="SELECT * FROM formapagamento ORDER BY nome");

		while($fetch = fetch($query)){
			$return[$fetch->id] = $fetch->nome;
		}

		return $return;
	}

	static function opcoes_ativas($categoria_id=0){

		$return = array();
		$query = query($sql="SELECT * FROM formapagamento WHERE st_ativo = 'S' ORDER BY nome");

		while($fetch = fetch($query)){
			$return[$fetch->id] = $fetch->nome;
		}

		return $return;
	}

	public function isBoleto(){
		return strpos($this->chave,'BOLETO')>-1;
	}

	public function isCartaoCredito(){
		return $this->formapagamento_tipo == 'CC';
	}

	public function isDepositoBancario(){

	}

	public function getImagem(){
		return PATH_SITE.'img/formapagamento/'.$this->imagem;
	}
	
	public function getPathImagem(){
		return PATH_SITE.'img/formapagamento/'.$this->imagem;
	}

	public function getContasDepositoBancario(){

		$ret = array();
		$i = 1;

		for($i=1;$i<=3;$i++){

			$depositobancario_banco = config::get("DEPOSITOBANCARIO_BANCO_{$i}");
			$depositobancario_agencia = config::get("DEPOSITOBANCARIO_AGENCIA_{$i}");
			$depositobancario_conta = config::get("DEPOSITOBANCARIO_CONTA_{$i}");
			$depositobancario_favorecido = config::get("DEPOSITOBANCARIO_FAVORECIDO_{$i}");

			if($depositobancario_banco !=''
			&& $depositobancario_agencia != ''
			&& $depositobancario_conta != ''
			&& $depositobancario_favorecido != ''){
				$ret[] = (object) array(
					'depositobancario_banco' => $depositobancario_banco
					,'depositobancario_agencia' => $depositobancario_agencia
					,'depositobancario_conta' => $depositobancario_conta
					,'depositobancario_favorecido' => $depositobancario_favorecido
				);
			}
		}

		return $ret;

	}

	static function opcoes_integrador(){
		$ret = array();
		$opt = array('--','LOCAWEB');
		foreach($opt as $item){
			$ret[$item] = $item;
		}
		return $ret;
	}

}

?>