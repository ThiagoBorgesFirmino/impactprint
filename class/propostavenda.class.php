<?php

class propostavenda extends base {

	var
		$id
		,$pedido_id
		,$propostastatus_id
		,$reprovado_motivo
		,$numero
		,$data_envio
		,$data_status
		,$data_cadastro
		,$html
		,$info;

	private
		$arrinfo;

	public function __get($chave){
		
		$part = substr($chave,0,5);
	
		if($part=='info_'){
			
			$info = $this->getInfo();
			$key = trim(substr($chave,5,strlen($chave)));
			$this->arrinfo = unserialize($info);
			
			return strip_tags(nl2br(@$this->arrinfo[$key]));
		}
	}
	
	public function setInfo($info){
		$_SESSION['propostavendainfo'] = $info;
	}
	
	public function getInfo(){
		return $_SESSION['propostavendainfo'];
	}
	
	public function getCodigoProposta(){
		return "{$this->pedido_id}-{$this->numero}";
	}
	
	public function getStatus(){
		$propostastatus = new propostastatus($this->propostastatus_id);
		return $propostastatus->descricao;
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
	
	public function itens(){
		if(!$this->arrinfo){
			$this->arrinfo = unserialize($this->getInfo());
		}
		$return = array();
		foreach($this->arrinfo['item'] as $item ){

			// printr($item);
		
			$obj = new propostavendaitem();
			
			foreach($item as $key=>$value){
				// $obj->$key = nl2br($value);
				$obj->$key = ($value);
			}
			
			$opcao = $obj->opcao;
			
			// Zera valores selecionados
			$obj->preco_opcao = '';
			$obj->item_qtd_opcao = '';
			$obj->sub_total_opcao = '';
			
			if($opcao==1){
				$obj->preco_opcao = $obj->preco;
				$obj->item_qtd_opcao = $obj->item_qtd;
				$obj->sub_total_opcao = $obj->sub_total;;
			}
			elseif($opcao==2){
				$obj->preco_opcao = $obj->preco2;
				$obj->item_qtd_opcao = $obj->item_qtd2;
				$obj->sub_total_opcao = $obj->sub_total2;;
			}
			elseif($opcao==3){
				$obj->preco_opcao = $obj->preco3;
				$obj->item_qtd_opcao = $obj->item_qtd3;
				$obj->sub_total_opcao = $obj->sub_total3;
			}
			
			$obj->opcao_1_checked = ($opcao==1?'checked':'');
			$obj->opcao_2_checked = ($opcao==2?'checked':'');
			$obj->opcao_3_checked = ($opcao==3?'checked':'');
			
			// printr($obj);
			
			$return[] = $obj;
		}

		return $return;
	}

	public function insere(){
		if(parent::insere()){
			$numero = query_col("SELECT IFNULL(max(numero),0)+1 numero FROM proposta WHERE pedido_id={$this->pedido_id}");
			$sql="UPDATE proposta SET numero = {$numero} WHERE id = {$this->id} ";
			$this->numero = $numero;
			query($sql);
			return true;
		}
		return false;
	}

	public function getDataEnvioFormat(){
		return formata_datahora_br($this->data_envio);
	}

	public function getDataCadastroFormat(){
		return formata_datahora_br($this->data_cadastro);
	}
	
	public function processa_html(){
	
		$t = new Template('tpl.email-proposta.html');

		$pedido = new pedido($this->pedido_id);
		
		$t->config = new config();
		$t->cadastro = new cadastro($pedido->cadastro_id);
		$t->vendedor = new cadastro($pedido->vendedor_id);
		$t->proposta = $this;
		$t->pedido = $pedido;
		
		foreach($this->itens() as $propostavendaitem){
			$t->propostavendaitem = $propostavendaitem;

			$preco2 = tofloat($propostavendaitem->preco2);

			if($preco2>0){
				$t->parseBlock('BLOCK_propostavendaitem_PRECO2');
				$t->parseBlock('BLOCK_propostavendaitem_QTD2');
				$t->parseBlock('BLOCK_propostavendaitem_SUBTOTAL2');
			}

			$preco3 = tofloat($propostavendaitem->preco3);

			if($preco3>0){
				$t->parseBlock('BLOCK_propostavendaitem_PRECO3');
				$t->parseBlock('BLOCK_propostavendaitem_QTD3');
				$t->parseBlock('BLOCK_propostavendaitem_SUBTOTAL3');
			}

			$t->parseBlock('BLOCK_propostavendaitem', true);
		}

		query("UPDATE proposta SET html = '".$t->getContent()."' WHERE id = {$this->id}");
	
	}
}

class propostavendaitem {
	function __get($key){
		return '';
	}
}

?>