<?php

class pedidoitem extends base {

    var $id;
    var $pedido_id;
    var $item_id;
    var $item_preco;
    var $item_qtd;
    var $item_qtd2;
    var $item_qtd3;
    var $info;
    var $tipo_produto;

    var $kit_pedido_itens;

	public function __get($key){
		if($key=='gravacao_id'||$key=='item_referencia'){

			$serial = @unserialize($this->info);
						
			if($serial&&property_exists($serial,$key)) {
				return $serial->$key;
			}
		}
		
		
		$part = substr($key,0,5);
		
		if($part=='info_'){
			$chave = trim(substr($key,5,strlen($key)));
			$arrinfo = unserialize($this->info);			
			return @$arrinfo->$chave;
		}
		return '';
	}

	// private function _getInfo(){

	// }

	public function getPrecoFormatado(){
		return money($this->item_preco);
	}
	
	public function getItemPrecoFormatado(){
		return $this->getPrecoFormatado();
	}
	
	public function getSubTotalFormatado(){
		return money($this->item_preco*$this->item_qtd);
	}
	public function getSubTotal(){
		return $this->item_preco*$this->item_qtd;
	}
	
	public function getQtdLiteral(){
		$return = '';
		if($this->item_qtd>0){
			$return .= $this->item_qtd;
		}
		if($this->item_qtd2>0){
			$return .= ', '.$this->item_qtd2;
		}
		if($this->item_qtd3>0){
			$return .= ', '.$this->item_qtd3;
		}
		return $return;
	}

	public function getImagem(){
		$serial = unserialize($this->info);
		return @$serial->item_imagem;
		//item_imagem
	}
	
	public function getItemNome(){
		$ret = '';
		if($this->id&&$this->item_id){
			$item = new item(intval($this->item_id));
			$tmp = $item->nome;
			// Caso nao tenha imagem e seja uma variacao, procura no pai
			if(!$tmp && $item->itemsku_id > 0){
				$item = new item(intval($item->itemsku_id));
				$tmp = $item->nome;
			}
			
			if($tmp!=''){
				$ret = $tmp;
			}
		}
		return $ret;
	}
	
	public function getItemImagem(){
		$ret = '';
		if($this->id&&$this->item_id){
			$item = new item(intval($this->item_id));
			$tmp = $item->imagem;
			// Caso nao tenha imagem e seja uma variacao, procura no pai
			if(!$tmp && $item->itemsku_id > 0){
				$item = new item(intval($item->itemsku_id));
				$tmp = $item->imagem;
			}
			
			if($tmp!=''){
				$ret = $tmp;
			}
		}
		return $ret;
	}

	public function getVariacaoDescricao(){
		$item = new item($this->item_id);
		return $item->getVariacaoDescricao();
	}
	
	// uso especifico para o google analytics, vai retornar sem "enter" 
	public function getVariacaoDescricaoGa(){
		return str_replace(array("\n", "\r"), '', $this->getVariacaoDescricao());
	}

	public function getVariacaoDescricaoHtml(){
		return nl2br($this->getVariacaoDescricao());
	}

	public function getItemReferencia(){
		$ret = '';
		if($this->id&&$this->item_id){
			$item = new item(intval($this->item_id));
			$ret = $item->referencia;
			// $tmp = $item->referencia;
			// // Caso nao tenha imagem e seja uma variacao, procura no pai
			// if(!$tmp && $item->itemsku_id > 0){
				// $item = new item(intval($item->itemsku_id));
				// $tmp = $item->referencia;
			// }
			
			// if($tmp!=''){
				// $ret = $tmp;
			// }
		}
		return $ret;
	}
	
	public function getCorReferencia(){
		if($this->id&&$this->cor_id){
			$cor = new cor(intval($this->cor_id));
			return $cor->referencia;
		}
	}
	
	public function getCorNome(){
		if($this->id&&$this->cor_id){
			$cor = new cor(intval($this->cor_id));
			return $cor->nome;
		}
	}

	public function getInfoHtml(){

		$return = '';

		if($this->info&&$this->info!=''){

			$serial = @unserialize($this->info);

			foreach(get_object_vars($serial) as $key=>$value){
				if(!property_exists($this,$key)){
					if($value){
						switch($key){
							case 'info_finalidade':
								$key = 'Finalidade do produto';
							break;
							case 'info_medida':
								$key = 'Medidas';
							break;
							case 'info_numero_cores':
								$key = 'Numero de cores';
							break;
							case 'info_descricao_projeto':
								$key = 'Descricao do projeto';
							break;
							case 'cor_nome':
								$key = 'Cor';
							break;
							case 'materia_prima_nome':
								$key = 'Matéria Prima';
							break;
							case 'gravacao_nome':
								$key = 'Gravação';
							break;
							default:
								$key = '';
							break;
						}
						if($key!=''){
							$return .= "<b>{$key}:</b> {$value}<br/>";
						}
					}
				}
			}
		}

		return $return;
	}

	public function getInfoTxt(){

		$return = '';

		if($this->info&&$this->info!=''){

			$serial = @unserialize($this->info);

			foreach(get_object_vars($serial) as $key=>$value){
				if(!property_exists($this,$key)){
					if($value){
						switch($key){
							case 'info_finalidade':
								$key = 'Finalidade';
							break;
							case 'info_medida':
								$key = 'Medidas';
							break;
							case 'info_numero_cores':
								$key = 'Numero de cores';
							break;
							case 'info_descricao_projeto':
								$key = 'Descricao do projeto';
							break;
							case 'cor_nome':
								$key = 'Cor';
							break;
							case 'materia_prima_nome':
								$key = 'Matéria Prima';
							break;
							case 'gravacao_nome':
								$key = 'Gravação';
							break;
							default:
								$key = '';
							break;
						}
						if($key!=''){
							$return .= "{$key}: {$value}\n";
						}
					}
				}
			}
		}

		return $return;
	}

	public function getMsgEmbalagemHtml(){
		return str_replace("\n","<br />",$this->msg_embalagem);
	}

	public function getMsgPresenteFormatado(){
		return $this->getMsgEmbalagemHtml();
	}
	
	public function getStEmbalagemFormatado(){
		if($this->isEmbalagem()){
			return 'Sim';
		}
		else {
			return 'Não';
		}
	}
	
	public function getMsgEmbalagemEmail(){
	
		if($this->isEmbalagem()){
		
			return '<b>Com Embalagem</b><br>
					<b>Mensagem do cart&atilde;o:</b><br>
					'.$this->getMsgEmbalagemHtml() ;
		
		}
		else {
			return '<b>Sem embalagem</b>';
		}
	}
	
	public function isEmbalagem(){
		return $this->st_embalagem=='S';
	}
}
