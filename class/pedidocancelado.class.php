<?php

class pedidocancelado extends base {

	var
		$id
		,$pedido_id
		,$emailmsg_id
		,$st_email
		,$obs
		,$data_cancelado
		,$data_cadastro
		;

	public function salva(){

		if(parent::salva()){

			$pedido = new pedido($this->pedido_id);
			$cadastro = new cadastro($pedido->cadastro_id);

			$pedido->atualizaPedidoStatus('CANCELADO');

			return true;
		}

		return false;

	}
	
	public function enviaEmailPedidoCancelado(){
		$pedido   = new pedido($this->pedido_id);
		$cadastro = new cadastro($pedido->cadastro_id);
		
		
		if($this->st_email=='S'){

			$email = new email();

			$t = new Template('tpl.email-pedido-cancelado.html');

			$t->config = new config();
			$t->pedido = $pedido;
			$t->cadastro = $cadastro;
			// $t->pedidoentregue = $this;

			$email->addTo($cadastro->email, $cadastro->nome);
			$email->addBcc(config::get('EMAIL_ADMINISTRACAO'), config::get('EMPRESA'));

			$email->addHtml($t->getContent());

			// print $t->getContent();

			$emailmsg = $email->send("Pedido {$pedido->id} - Cancelado");

			if(@$emailmsg->id){
				query("UPDATE `pedidocancelado` SET `emailmsg_id` = {$emailmsg->id} WHERE id = {$this->id} ");
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

	public function getDataCanceladoFormatado(){
		return formata_data_br($this->data_cancelado);
	}

	public function getStEmailFormatado(){
		if($this->st_email=='S'){
			return 'SIM';
		}
		if($this->st_email=='N'){
			return 'NÃO';
		}
	}

}

?>