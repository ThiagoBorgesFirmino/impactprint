<?php

class bonus extends base{

	var
		$id
		,$st_utilizado
		,$cadastro_id
		,$pedido_id
		,$qtd_bonus
		,$data_cadastro
		,$data_validade;
		
	public function validaBonus(&$erros=array()){			
		
		if($this->cadastro_id){
			if(is_numeric($this->cadastro_id)){
				$query = query('SELECT * FROM cadastro WHERE id='.$this->cadastro_id.'');
				$fetch = fetch($query);
				if(!$fetch){
					$erros['cadastro_id'] = 'Digite um id de Cadastro valido.';
				}		
			}
			else {
				$erros['cadastro_id'] = 'Digite apenas numeros para o Cadastro.';
			}
		}
		else {
			$erros['cadastro_id'] = 'Digite algum valor para Cadastro id.';
		}
		
		if($this->pedido_id){
			if(is_numeric($this->pedido_id)){
				$query = query('SELECT * FROM pedido WHERE id='.$this->pedido_id.'');
				$fetch = fetch($query);
				$query = query('SELECT * FROM bonus WHERE pedido_id = '.$this->pedido_id.'');
				if(fetch($query)){
					$erros['pedido_id'] = 'Ja existe um pedido cadastrado com esse ID.';
				}
				if(!$fetch){
					$erros['pedido_id'] = 'Digite um id de Pedido valido.';
				}
			}
			else {
				$erros['pedido_id'] = 'Digite apenas numeros para o Pedido.';
			}
		}
		else{
			$erros['pedido_id'] = 'Digite algum valor para Pedido id.';
		}
		
		if(!$this->qtd_bonus){
			$erros['qtd_bonus'] = 'Digite alguma Quantidade.';
		}
		else{
			if(!is_numeric($this->qtd_bonus)){
				$erros['qtd_bonus'] = 'Digite apenas numeros para Quantidade.';
			}
		}
		
		return sizeof($erros)==0;

	}
	
	public function isBonusVencido(){
		// ret
		$id = intval($this->id);
		$sql = "SELECT 1, data_validade FROM bonus WHERE id = {$id} AND data_validade<CURDATE()";
		return rows(query($sql))==1;
	}
	
	public function getDataCadastroFormatado(){
		// printr($this);
		return formata_datahora_br($this->data_cadastro);
	}
	
	public function getDataValidadeFormatado(){
		if($this->isBonusVencido() && $this->st_utilizado == 'N'){
			return "<span style='color:red; font-size:10px;'>CUPOM EXPIROU<br />".formata_datahora_br($this->data_validade)."</span>";
		}else{
			return formata_datahora_br($this->data_validade);
		}
	}
	
	public function getStUtilizadoFormatado(){
		if($this->isBonusVencido() && $this->st_utilizado == 'N'){
			return '--';
		}
		if($this->st_utilizado == 'S'){
			return 'Sim';
		}
		if($this->st_utilizado == 'N'){
			return 'Não';
		}
		return '';
	}
	
}

?>