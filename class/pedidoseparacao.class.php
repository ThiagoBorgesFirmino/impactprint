<?php

// Modelo de dados para
class pedidoseparacao extends base {

	var
		$id
		,$pedido_id
		,$emailmsg_id
		,$st_email
		,$obs
		,$data_separacao
		,$data_cadastro;

	public function salva(){

		if(parent::salva()){

			// Carrega dados do pedido e cadastro
			$pedido = new pedido($this->pedido_id);
			$cadastro = new cadastro($pedido->cadastro_id);

			// Atualiza status do pedido
			$pedido->atualizaPedidoStatus('SEPARADO');

			// Recarrega dados do pedido
			$pedido = new pedido($this->pedido_id);

			// // Faz separacao do lote para entrega
			// // Se fazia a separacao do lote apenas nesse passo do pedido, agora se faz na finalizacao

			// foreach($pedido->get_childs('pedidoitem') as $pedidoitem){

				// $item = new item($pedidoitem->item_id);
				// $qtd_saida_total = $pedidoitem->item_qtd;

				// // Recupera lotes possiveis
				// foreach($item->get_childs('lote', 'AND qtd_saldo > 0 AND data_vencimento > NOW()', 'ORDER BY data_vencimento DESC') as $lote){

					// if($qtd_saida_total>0){

						// // $lote = new lote($lotemov->lote_id);
						// $lotemov = new lotemov();

						// $lotemov->lote_id = $lote->id;
						// $lotemov->pedido_id = $pedido->id;
						// $lotemov->item_id = $item->id;

						// $lotemov->qtd_saida = $qtd_saida_total > $lote->qtd_saldo ? $lote->qtd_saldo : $qtd_saida_total;
						// $lote->qtd_saldo = $lote->qtd_saldo - $lotemov->qtd_saida;

						// $lote->salva();
						// $lotemov->salva();

						// $qtd_saida_total -= $lotemov->qtd_saida;
					// }
				// }
			// }

			// Caso tenha solicitado o envio do e-mail
			if($this->st_email=='S'){

				$email = new email();

				$email->addTo($cadastro->email, $cadastro->nome);
				$email->addBcc(config::get('EMAIL_ADMINISTRACAO'), config::get('EMPRESA'));

				$email->addHtml(projeto::montaEmailPedidoSeparacao($pedido, $this));

				$emailmsg = $email->send("Pedido {$pedido->id} - Pedido em separação");

				if(@$emailmsg->id){
					query("UPDATE `pedidoseparacao` SET `emailmsg_id` = {$emailmsg->id} WHERE id = {$this->id} ");
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

	public function getDataSeparacaoFormatado(){
		return formata_data_br($this->data_separacao);
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