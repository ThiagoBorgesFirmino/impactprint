<?php

class pedidoentregue extends base {

	var
		$id
		,$pedido_id
		,$emailmsg_id
		,$st_email
		,$obs
		,$data_entregue
		,$data_cadastro
		;

	public function salva(){

		if(parent::salva()){

			// Carrega pedido
			$pedido = new pedido($this->pedido_id);
			
			// Carrega cadastro
			$cadastro = new cadastro($pedido->cadastro_id);

			// Atualiza status do pedido
			$pedido->atualizaPedidoStatus('FINALIZADO');

			// Recarrega pedido
			$pedido = new pedido($this->pedido_id);
			
			//printr($this);
			
			if($this->st_email=='S'){

				$email = new email();

				$t = new Template('tpl.email-pedido-finalizado.html');

				$t->config = new config();
				$t->pedido = $pedido;
				$t->cadastro = $cadastro;
				$t->pedidoentregue = $this;

				$email->addTo($cadastro->email, $cadastro->nome);
				$email->addBcc(config::get('EMAIL_ADMINISTRACAO'), config::get('EMPRESA'));

				$email->addHtml($t->getContent());

				$emailmsg = $email->send("Pedido {$pedido->id} - Entregue");

				//printr($emailmsg);
				
				if(@$emailmsg->id){
					query("UPDATE `pedidoentregue` SET `emailmsg_id` = {$emailmsg->id} WHERE id = {$this->id} ");
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

	public function getDataEntregueFormatado(){
		return formata_data_br($this->data_entregue);
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