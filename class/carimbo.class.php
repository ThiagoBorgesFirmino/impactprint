<?php

class carimbo extends base {

	var
		$id
		,$st_ativo
		,$st_fixo
		,$referencia
		,$nome
		,$nome_es
		,$nome_in
		,$descricao
		,$descricao_es
		,$descricao_in
		,$imagem
		,$data_cadastro;

	private
		$objFile;

	public function salva(){
		$return = false;
		if(parent::salva()){
			if($this->objFile){
				
				$extensao = substr($this->objFile['name'],-3);
				
				$this->imagem = stringAsTag("{$this->nome} {$this->id}").".{$extensao}";
				
				$upload = new upload();
				
				$upload->setFile($this->objFile);
				// $upload->diretorio = $_SERVER['DOCUMENT_ROOT'].PATH_SITE.PATH_IMG_SPLASH;
				$upload->diretorio = 'img/carimbo/';
				$upload->grava($this->imagem);
				
				unset($upload);
				
				query("UPDATE ".$this->get_table_name()." SET imagem = '{$this->imagem}' WHERE id = {$this->id}");
			}
			$return = true;
		}
		return $return;
	}

	public function setFile($file){
		if($file['tmp_name']!=''){
			$this->objFile = $file;
		}
	}

	public function validaDados(&$erro=array()){
		return true;
	}

	public function validaExclusao(&$erro=array()){
		if(!$this->id){
			$erro[] = "Chave nao definida";
		}
		if(sizeof(results("SELECT * FROM item WHERE carimbo_id = {$this->id}"))>0){
			$erro[] = "Produtos relacionados, não é possível excluir";
		}
		return sizeof($erro)==0;
	}
	
	public function exclui(){
		if(parent::exclui()){
			if($this->imagem!=''){
				@unlink("img/carimbo/{$this->imagem}");
			}
			return true;
		}
		return false;
	}
	
	static function opcoes(){

		$return = array();
		$query = query($sql="SELECT * FROM carimbo ORDER BY nome");
		while($fetch=fetch($query)){
			$return[$fetch->id] = $fetch->nome;
		}
		return $return;
	}
	
}

?>