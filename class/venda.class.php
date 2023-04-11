<?php

class venda extends base {

	var
		$id
		,$pedido_id
		,$proposta_id
		,$vendastatus_id
		,$nota_fiscal
		,$data_cadastro
		,$data_envio_pedido
		,$data_previsao_entrega
		,$data_entrega
		,$data_envio_email
		,$html
		,$info;

	private
		$arrinfo;

	public function __get($chave){
	
		$part = substr($chave,0,5);
	
		if($part=='info_'){

			$key = trim(substr($chave,5,strlen($chave)));

			if(!$this->arrinfo){
				$this->arrinfo = unserialize($this->info);
			}

			// return nl2br(@$this->arrinfo[$key]);
			return (@$this->arrinfo[$key]);
		}
	}
	
	
	
	public function getCodigoProposta(){
		return "{$this->pedido_id}-{$this->numero}";
	}
	
	public function getValorTotal(){
	
		$return = 0;
	
		foreach($this->itens() as $item){
			if($item->opcao==1){
				$return += $item->sub_total;
			}
			if($item->opcao==2){
				$return += $item->sub_total2;
			}
			if($item->opcao==3){
				$return += $item->sub_total3;
			}
		}
		
		return $return;
	}
	
	public function getValorTotalFormatado(){
		return money($this->getValorTotal());
	}
	
	public function getDataCadastroFormatado(){
		return formata_data_br($this->data_cadastro);
	}
	
	public function getDataEnvioPedidoFormatado(){
		return formata_data_br($this->data_envio_pedido);
	}
	
	public function getDataEnvioEmailFormatado(){
		return formata_data_br($this->data_envio_email);
	}
	
	public function getDataPrevisaoEntregaFormatado(){
		return formata_data_br($this->data_previsao_entrega);
	}
	
	public function getDataEntregaFormatado(){
		return formata_data_br($this->data_entrega);
	}
	
	public function itens(){
		if(!$this->arrinfo){
			$this->arrinfo = unserialize($this->info);
		}
		$return = array();
		foreach($this->arrinfo['item'] as $item ){

			// printr($item);
		
			$obj = new vendaitem();
			
			foreach($item as $key=>$value){
				// $obj->$key = nl2br($value);
				$obj->$key = ($value);
			}
			
			$obj->descricao_html = nl2br($obj->descricao);
			$obj->obs_venda_html = nl2br($obj->obs_venda);
			
			$return[] = $obj;
		}

		return $return;
	}
	
	public function salva(){
		$this->data_envio_pedido = to_bd_date($this->data_envio_pedido);
		$this->data_previsao_entrega = to_bd_date($this->data_previsao_entrega);
		$this->data_entrega = to_bd_date($this->data_entrega);
		if(parent::salva()){
			$this->processa_html();			
		}
	}

	public function insere(){
		return parent::insere();
	}

	public function getDataEnvioFormat(){
		return formata_datahora_br($this->data_envio);
	}

	public function getDataCadastroFormat(){
		return formata_datahora_br($this->data_cadastro);
	}

	public function getDataEnvioPedido(){
		return formata_datahora_br($this->data_envio_pedido);
	}
	
	public function processa_html($ret=false){
	
		$t = new Template('tpl.email-venda.html');

		$data_pedido = formata_data_br($this->data_envio_pedido);

		$pedido = new pedido($this->pedido_id);
		
		$t->config = new config();
		$t->vendedor = new cadastro($pedido->vendedor_id);
		$t->cadastro = new cadastro($pedido->cadastro_id);
		$t->data_pedido = $data_pedido;
		$t->venda = $this;
		
		
		if($this->info_entrega_cep!=''){
			$t->parseBlock('BLOCK_ENDERECO_ENTREGA');
		}
		else {
			$t->parseBlock('BLOCK_ENDERECO_ENTREGA_MESMO');
		}
		
		foreach($this->itens() as $vendaitem){
			$t->vendaitem = $vendaitem;
			$t->parseBlock('BLOCK_VENDAITEM', true);
		}
		
		if($ret)return $t->getContent();

		query("UPDATE venda SET html = '" . base64_encode($t->getContent()) . "' WHERE id = {$this->id}");
	}
}

class vendaitem {
	function __get($key){
		return '';
	}
}
?>