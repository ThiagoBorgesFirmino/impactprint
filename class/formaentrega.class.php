<?php

// Modelo de dados
class formaentrega extends base {

	var
		$id
		,$st_ativo
		,$st_fixo
		,$st_correio
		,$chave
		,$nome
		,$descricao
		,$correio_cod_servico
		,$frete_gratis_acima
		,$prazo_entrega_padrao
		,$imagem
	;

	private
		$cepEntrega
		,$valorProdutos
		,$qtdProdutos
		,$pesoProdutos;

	public function setContextoCompra(){

	}

	static function get($chave){
		$formaentrega = new formaentrega(array('chave'=>$chave));
		return $formaentrega;
	}

	static function opcoes($categoria_id=0){

		$return = array();
		$query = query($sql="SELECT * FROM formaentrega ORDER BY nome");

		while($fetch = fetch($query)){
			$return[$fetch->id] = $fetch->nome;
		}

		return $return;
	}

	public function getFreteGratisAcimaFormatado(){
		return money($this->frete_gratis_acima);
	}

	public function getImagem(){
		return PATH_SITE.'img/formaentrega/'.$this->imagem;
	}

	public function exclui(){
	
		// asdf
		if($this->id>0){

			// // Checa se existem pedidos relacionados
			// if(rows(query("select * from pedidoitem where item_id = {$this->id}"))>0){
				// throw new Exception("Pedidos relacionados, não é possível excluir");
			// }

			// query("DELETE FROM preco WHERE item_id = {$this->id}");
			query("DELETE FROM formaentregaregiaovalor WHERE formaentrega_id = {$this->id}");
			query("DELETE FROM formaentregaufvalor WHERE formaentrega_id = {$this->id}");
			query("DELETE FROM itemformaentregaconfig WHERE formaentrega_id = {$this->id}");

			// Executa exclusao
			return parent::exclui();

		}
	
	}
	
}

?>