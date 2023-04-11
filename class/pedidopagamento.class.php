<?php

class pedidopagamento extends base {

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

			// Atualiza status do pedido
			$pedido->atualizaPedidoStatus('PAGO');

			// Atualiza valores do pedido
			 $pedido->atualizaValores();

			// Pega os itens do pedido
			projeto::setBonusByPedido($pedido->id);

			// Recarrega dados do pedido
			$pedido = new pedido($this->pedido_id);

			// Caso tenha solicitado o envio do e-mail
			if($this->st_email=='S'){

				$email = new email();

				$email->addTo($cadastro->email, $cadastro->nome);
				$email->addBcc(config::get('EMAIL_ADMINISTRACAO'), config::get('EMPRESA'));

				$email->addHtml(projeto::montaEmailPedidoPagamento($pedido, $this));

				$emailmsg = $email->send("Pedido {$pedido->id} - Pagamento confirmado");

				if(@$emailmsg->id){
					query("UPDATE `pedidopagamento` SET `emailmsg_id` = {$emailmsg->id} WHERE id = {$this->id} ");
				}

			}

			// Verifica se é um valepresente, se for, manda e-mail para o presenteado com o código
			if($pedido->isValePresente()){

				$email = new email();

				$valepresente = new valepresente($pedido->valepresente_id);

				$email->addTo($valepresente->email, $valepresente->nome);
				$email->addCc($cadastro->email, $cadastro->nome);
				$email->addBcc(config::get('EMAIL_ADMINISTRACAO'), config::get('EMPRESA'));

				$email->addHtml(projeto::montaEmailValePresentePresenteado($pedido));

				$emailmsg = $email->send("{$valepresente->nome}, você ganhou um vale presente !!");

				if(@$emailmsg->id){
					query("UPDATE `valepresente` SET `emailmsg_id` = {$emailmsg->id} WHERE id = {$valepresente->id} ");
				}

			}
			
			return true;
			
		}
		
		return false;
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