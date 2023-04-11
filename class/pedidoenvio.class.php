<?php

class pedidoenvio extends base {

	var
		$id
		,$pedido_id
		,$emailmsg_id
		,$st_email
		,$transportadora
		,$rastreamento
		,$obs
		,$data_envio
		,$data_cadastro
		;

	public function salva(){
	
		if(parent::salva()){
			if($this->st_email=='S'){
			
				$pedido = new pedido($this->pedido_id);
				$cadastro = new cadastro($pedido->cadastro_id);
				
				$pedido->atualizaPedidoStatus('ENVIADO');
				
				$pedido = new pedido($this->pedido_id);
			
				$email = new email();
				
				$t = new Template('tpl.email-pedido-rastreamento.html');
				
				$t->config = new config();
				$t->pedido = $pedido;
				$t->cadastro = $cadastro;
				$t->pedidoenvio = $this;
				
				$email->addTo($cadastro->email, $cadastro->nome);
				$email->addBcc(config::get('EMAIL_ADMINISTRACAO'), config::get('EMPRESA'));
				
				$email->addHtml($t->getContent());
				
				// print $t->getContent();	
				
				$emailmsg = $email->send("Pedido {$pedido->id} - Enviado");
				
				if(@$emailmsg->id){
					query("UPDATE `pedidoenvio` SET `emailmsg_id` = {$emailmsg->id} WHERE id = {$this->id} ");
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
	
	public function getDataEnvioFormatado(){
		return formata_data_br($this->data_envio);
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