<?php

class promocao extends base{

	var
	$id
	,$st_ativo
	,$nome
	,$tipo_promocao
	,$valor
	,$porcentagem
	,$data_validade	
	,$data_cadastro;
	
	public function exclui(){
		$query = query("SELECT * FROM itempromocao WHERE promocao_id = {$this->id}");
		while($fetch=fetch($query)){
			$item = new item($fetch->item_id);
			$item->preco = $fetch->preco_original;
			$item->salva();
		}
	
		query("DELETE FROM itempromocao WHERE promocao_id = {$this->id}");
		$_SESSION['sucesso'] = "<p>Promo&ccedil;&atilde;o exclu&iacute;da com sucesso!";
		parent::exclui();
	}
	
	public function salva(){	
		if($this->id){
			$promocao = new promocao($this->id);
			if(($promocao->st_ativo!=$this->st_ativo)&&($this->st_ativo=='N')){
				$query = query("SELECT * FROM itempromocao WHERE promocao_id = {$this->id}");
				while($fetch=fetch($query)){
					$item = new item($fetch->item_id);
					$item->preco = $fetch->preco_original;
					$item->salva();
				}
			}elseif(($promocao->st_ativo!=$this->st_ativo)&&($this->st_ativo=='S')){
				$query = query("SELECT * FROM itempromocao WHERE promocao_id = {$this->id}");
				while($fetch=fetch($query)){
					$itempromocao = new itempromocao($fetch->id);
					$item = new item($fetch->item_id);
					
					if($this->tipo_promocao=='P'){
						$itempromocao->preco_original = $item->preco;
						$item->preco = $item->preco - ($item->preco*($this->porcentagem/100));
					}
					if($this->tipo_promocao=='V'){
						$precoatual = $item->preco;
						$item->preco = $itempromocao->preco_original;
						$itempromocao->preco_original = $precoatual;
					}
					
					$itempromocao->atualiza();
					$item->salva();
				}				
			}
		}
		
		parent::salva();
		
	}
	
	public function getDataValidadeFormatada(){
		return formata_data_br($this->data_validade);
	}
	
	public function getTipoPromocaoEditada(){
		if($this->tipo_promocao == 'P'){
			return 'Porcentagem(%)'; 
		}
		if($this->tipo_promocao == 'V'){
			return 'Valor($)'; 
		}
		return '';
	}
	
}

?>