<?php

class pedidoanalise extends base {

	var
		$id
		,$pedido_id
		,$emailmsg_id
		,$st_email
		,$formapagamento_id
		,$codigo_transacao
		,$valor
		,$obs
		,$data_pagamento
		,$data_cadastro
		;

	public function salva(){

		if(parent::salva()){

			// Carrega dados do pedido e cadastro
			$pedido = new pedido($this->pedido_id);
			$cadastro = new cadastro($pedido->cadastro_id);
			$pedidostatus = new pedidostatus(array('chave'=>'EMANALISE'));

			// Atualiza status do pedido
			$pedido->atualizaPedidoStatus('EMANALISE');

			// Recarrega dados do pedido
			$pedido = new pedido($this->pedido_id);

			// Caso tenha solicitado o envio do e-mail
			if($this->st_email=='S'){

				$email = new email();

				$email->addTo($cadastro->email, $cadastro->nome);
				$email->addBcc(config::get('EMAIL_ADMINISTRACAO'), config::get('EMPRESA'));

				$email->addHtml(projeto::montaEmailPedidoAnalise($pedido, $this));

				$emailmsg = $email->send("Pedido {$pedido->id} - {$pedidostatus->descricao}");

				if(@$emailmsg->id){
					query("UPDATE `pedidoanalise` SET `emailmsg_id` = {$emailmsg->id} WHERE id = {$this->id} ");
				}
			}
		}
	}

	public function getLinkAdminEmailmsg(){
		$return = '';
		if($this->emailmsg_id>0){
			$return .= tag('a href="'.PATH_SITE.'admin.php/seeemailmsg/'.$this->emailmsg_id.''.'"','ver e-mail');
		}
		return $return ;
	}

	public function getValorFormatado(){
		return money($this->valor);
	}

	public function getDataPagamentoFormatado(){
		return formata_data_br($this->data_pagamento);
	}

	public function getStEmailFormatado(){
		if($this->st_email=='S'){
			return 'SIM';
		}
		if($this->st_email=='N'){
			return 'NÃO';
		}
	}

	public function getFormapagamentoNome(){
		$formapagamento = new formapagamento($this->formapagamento_id);
		return $formapagamento->nome;
	}

}

?>