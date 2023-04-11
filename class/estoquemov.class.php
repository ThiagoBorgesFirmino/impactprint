<?php

class estoquemov extends base {

	var
		$id
		,$estoquemov_tipo
		,$item_id
		,$pedido_id
		
		,$lote_id //  adicionado 21/08/2014
		
		,$qtd
		,$lote_codigo
		,$localizacao
		,$log
		,$data_validade
		,$data_cadastro
		,$operador;

	// Valida movimentacao de estoque
	public function validaEstoquemov(&$erros=array()){

		if (is_numeric($this->item_id)) {
			$query = query('SELECT * FROM item WHERE id = '.$this->item_id.'');
			if(!mysql_num_rows($query)){
				$erros['item_id'] = 'Nenhum produto foi encontrado com esse ID, tente novamento com outro ID.';
			}
		}else{
			$erros['item_id'] = 'Digite um ID valido.';
		}

		if (!is_numeric($this->qtd)) {
			$erros['qtd'] = 'Digite apenas numeros para a Quantidade.';
		}

		$variacao = new item(array('itemsku_id'=>$this->item_id));

		if($variacao->id){
			$erros[] = 'Esse item tem variações, dê entrada no estoque pela variação e não pelo item principal';
		}

		return sizeof($erros)==0;

	}

	public function salva(){

		$qtd = intval($this->qtd);
		$this->estoquemov_tipo = $qtd>0?'E':'S';

		return parent::salva();

	}

}
?>