<?php

class valepresente extends base {

	var
		$id
		,$codigo

		,$st_ativo
		,$st_clicado

		,$pedido_id
		,$tipovalepresente_id
		,$cadastro_id
		,$emailmsg_id

		,$valor

		,$nome
		,$email
		,$mensagem

		,$data_utilizacao
		,$data_validade
		,$data_cadastro;

	// ao salvar, gera codigo que sera enviado por e-mail
	public function salva(){

		$novo = !$this->id;

		if(parent::salva()){

			if($novo){

				$codigo = md5($this->id.$this->data_cadastro.$this->nome.$this->email);
				query("UPDATE valepresente SET codigo = '{$codigo}', data_validade = DATE_ADD(data_cadastro, INTERVAL 100 DAY) WHERE id = {$this->id}");

				$this->refresh();

			}

			return true;

		}
		return false;

	}

	public function getMensagemHtml(){
		return nl2br($this->mensagem);
	}

	public function validaDados(&$erro=array()){
		return sizeof($erro)==0;
	}

	public function getValorFormatado(){
		return money($this->valor);
	}

	public function getDataUtilizacaoFormatada(){
		return formata_data_br($this->data_utilizacao);
	}

	public function getDataValidadeFormatada(){
		return formata_data_br($this->data_validade);
	}

	// Aliast to getDataValidadeFormatada
	public function getDataValidadeFormatado(){
		return $this->getDataValidadeFormatada();
	}

	public function getDataCadastroFormatado(){
		return formata_datahora_br($this->data_cadastro);
	}

	public function isDataValidadeOk(){
		return rows(query("SELECT * FROM valepresente WHERE id = {$this->id} AND data_validade >= curdate()"))==1;
	}

	public function getLinkAdminEmailmsg(){
		$return = '';
		if($this->emailmsg_id>0){
			$return .= tag('a href="'.PATH_SITE.'admin.php/seeemailmsg/'.$this->emailmsg_id.''.'"','ver e-mail');
		}
		return $return ;
	}


}

?>