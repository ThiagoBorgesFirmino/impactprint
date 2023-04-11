<?php

class proposta extends base {

	var
		$id
		,$pedido_id
		,$propostastatus_id
		//,$reprovado_motivo
		,$numero
		,$data_envio
		,$data_status
		,$data_cadastro
		,$html
		,$info
		,$encpt_type;

	private
		$arrinfo;

	public function __get($chave){
	
		$part = substr($chave,0,5);
	
		if($part=='info_'){

			$key = trim(substr($chave,5,strlen($chave)));
			
			if(!$this->arrinfo){
				if($this->encpt_type=="json"){
					$this->arrinfo = json_decode($this->info);
				}else{
					 $this->arrinfo = unserialize($this->info);
				}
			}

			return @$this->arrinfo[$key];
		}
		
		if($part=='infn_'){

			$key = trim(substr($chave,5,strlen($chave)));

			if(!$this->arrinfo){
				if($this->encpt_type=="json")$this->arrinfo = json_decode($this->info);
				else $this->arrinfo = unserialize($this->info);
			}

			return strip_tags(@$this->arrinfo[$key]);
		}
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
		
		// REMOVENDO AS QUEBRAS DE LINHA DO SERIALIZE
		$v = $this->info;       
		$v = str_replace("\n", "", $v);
		$v = str_replace("\r", "", $v);
		$v = preg_replace('/\s/',' ',$v);                   
		
		$data = preg_replace_callback(
			'/s:(\d+):"(.*?)";/',
			function($m){
				return 's:' . strlen($m[2]) . ':"' . $m[2] . '";';
			},
			$v
		);
		
		$this->arrinfo = unserialize($data);
		
		if(!$this->arrinfo){
			if($this->encpt_type=="json")$this->arrinfo = json_decode($this->info);
			else $this->arrinfo = unserialize($this->info);
		}

		$return = array();
		foreach($this->arrinfo['item'] as $item ){

			// printr($item);
		
			$obj = new propostaitem();
			
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
				$obj->sub_total_opcao = $obj->sub_total;
			}
			elseif($opcao==2){
				$obj->preco_opcao = $obj->preco2;
				$obj->item_qtd_opcao = $obj->item_qtd2;
				$obj->sub_total_opcao = $obj->sub_total2;
			}
			elseif($opcao==3){
				$obj->preco_opcao = $obj->preco3;
				$obj->item_qtd_opcao = $obj->item_qtd3;
				$obj->sub_total_opcao = $obj->sub_total3;
			}
			
			$obj->opcao_1_checked = ($opcao==1?'checked':'');
			$obj->opcao_2_checked = ($opcao==2?'checked':'');
			$obj->opcao_3_checked = ($opcao==3?'checked':'');
			
			$obj->descricao_html = nl2br($obj->descricao);
			
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
	
	
	public function getTotalProposta($info=array()){
		$total = 0;
		foreach($info['item'] as $propostaitem){			
			$total = $total+tofloat($propostaitem['sub_total']);
		}
		return $total;		
	}
	
	public function setTotalProposta($total){
		if($this->encpt_type=="json")$info = json_decode($this->info);
		else $info = unserialize($this->info);

		$info['total'] = money($total);
		$this->info = serialize($info);		
	}	
	
	public function processa_html($ret=false){
	
		$t = new Template('tpl.email-proposta.html');

		$pedido = new pedido($this->pedido_id);
		
		$t->proposta = $this;
		$t->config = new config();
		$t->cadastro = new cadastro($pedido->cadastro_id);
		$t->vendedor = new cadastro($pedido->vendedor_id);
		$t->pedido = $pedido;
		
		//$total = 0;
		
		foreach($this->itens() as $propostaitem){
			$item = new item($propostaitem->item_id);
			$t->link = $item->getLink();
			
			//$propostaitem->sub_total = money(str_replace(",",".",$propostaitem->sub_total));
			$t->propostaitem = $propostaitem;

			//printr($propostaitem);
			
			//$total = $total+tofloat($propostaitem->sub_total);
			
			$preco2 = tofloat($propostaitem->preco2);

			$p_qtd2 = $propostaitem->qtd2;
			if($propostaitem->qtd2>0){
				$t->parseBlock("BLOCK_PROPOSTA2");
			}
			
			if($preco2>0){
				$t->parseBlock('BLOCK_PROPOSTAITEM_PRECO2');
				$t->parseBlock('BLOCK_PROPOSTAITEM_QTD2');
				$t->parseBlock('BLOCK_PROPOSTAITEM_SUBTOTAL2');
			}

			$preco3 = tofloat($propostaitem->preco3);

			if($preco3>0){
				$t->parseBlock('BLOCK_PROPOSTAITEM_PRECO3');
				$t->parseBlock('BLOCK_PROPOSTAITEM_QTD3');
				$t->parseBlock('BLOCK_PROPOSTAITEM_SUBTOTAL3');
			}

			$t->parseBlock('BLOCK_PROPOSTAITEM', true);
		}
		
		if($this->info_local_entrega!=''){
			$t->parseBlock("BLOCK_LOCAL_ENTREGA");
		}
		if($this->info_local_cobranca!=''){
			$t->parseBlock("BLOCK_LOCAL_COBRANCA");
		}
		
		$this->html = $t->getContent();	

		if($ret) return $this->html;
		else query("UPDATE proposta SET html = '".$t->getContent()."' WHERE id = {$this->id}");
	}

	/** */
	public function processa_html_v2(){
	
		$t = new Template('tpl.email-proposta_v2.html');
		if($t->exists("config"))$t->config = new config();		
		
		$pedido = new pedido($this->pedido_id);
		
		if($t->exists("proposta"))$t->proposta = $this;
		if($t->exists("config"))$t->config = new config();
		if($t->exists("cadastro"))$t->cadastro = new cadastro($pedido->cadastro_id);
		if($t->exists("vendedor"))$t->vendedor = new cadastro($pedido->vendedor_id);
		if($t->exists("pedido"))$t->pedido = $pedido;

		$break_count = 0;
		foreach($this->itens() as $propostaitem){
			
			if($t->exists("produto")){
				$t->produto =  $item = new item($propostaitem->item_id);
			} 
			//$propostaitem->sub_total = money(str_replace(",",".",$propostaitem->sub_total));			


			$path =  getcwd();
			if(file_exists($path."/img/produtos/$item->imagem")){ 
				// die("aq");
				//$propostaitem->imagem_proposta="teste.png";
				$propostaitem->imagem_proposta = config::get('URL')."img/produtos/$item->imagem";
			}else{
				$propostaitem->imagem_proposta = PATH_IMG."produtos". DIRECTORY_SEPARATOR ."$item->imagem".'?f=j';
			}

			if($t->exists("propostaitem"))$t->propostaitem = $propostaitem;

			//printr($propostaitem); 
			
			if($propostaitem->item_qtd > 0){
				$t->parseBlock("BLOCK_PROPOSTA_QTD");
			
			}
			
			if($propostaitem->item_qtd2 > 0){
				$t->parseBlock("BLOCK_PROPOSTA_QTD2");
			
			}	
			
			if($propostaitem->item_qtd3 > 0 ){
				$t->parseBlock("BLOCK_PROPOSTA_QTD3");
				
			}
			
		
			if($t->exists("gravacao"))
			{
				$verifica = new caracvalor($propostaitem->gravacao_id);
				$t->gravacao = new caracvalor($propostaitem->gravacao_id);
				if($verifica->id)
				{
					$t->parseBlock("BLOCK_GRAVACAO_ON");
				}
			}
			// printr(); die();
			// if(file_exists("img/produtos/".$item->$campo)){
			// 	$path_imagem_produto = PATH_SITE.$path_img.$item->$campo;
			// }else{
			// 	$path_imagem_produto = PATH_IMG."produtos".DIRECTORY_SEPARATOR.$item->$campo;
			// }
				

			//$t->path_imagem_produto = ( isset($propostaitem->aplicacao) && $propostaitem->aplicacao!="" ? $propostaitem->aplicacao : config::get("URL") . "img/produtos/{$item->imagem}");
			//$t->path_imagem_produto = $propostaitem->imagem;

			$c= 0;
			foreach( range(1,7) as $val ){
				$imagem = "imagem_d{$val}";
				// var_dump($imagem);
				// die();
				if($item->$imagem!=""){
					switch($c){
						// case 0 : $t->imagem_d1 = $item->$imagem; $t->parseBlock("BLOCK_DETALHE_1"); $c++; break
						case 1 : $t->imagem_d2 = $item->$imagem; $t->parseBlock("BLOCK_DETALHE_2"); $c++; break;
						default : break;
					}
				}
			}
			$break_count++;

			$t->count = $break_count;

			/** Verificar se o count é ímpar para add o page-break */
			if( ($break_count % 2)>0 )$t->parseBlock("BLOCK_PAGE_BREAK",true);

			$t->parseBlock('BLOCK_PRODUTOS', true);
			$t->parseBlock('BLOCK_PRODUTOS_RESUMO', true);
		}


		// printr($this->itens());
		
		// printr($pedido);
		
		return $t->getContent();
		die();
		/** *** *** */
	}
}

class propostaitem {
	function __get($key){
		return '';
	}
}
?>