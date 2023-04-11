<?php

class corgrupo extends base {

	var
		$id
		,$st_ativo
		,$st_fixo
		,$nome
		,$nome_es
		,$nome_in
		,$imagem
		,$data_cadastro;

	private
		$objFile;

	public function salva(){
		$return = false;
		if(parent::salva()){
			if($this->objFile){
				$nome = stringAsTag($this->nome);
				$this->imagem = "{$this->id}-{$nome}.jpg";
				$upload = new upload();
				$upload->setFile($this->objFile);
				$upload->diretorio = $_SERVER['DOCUMENT_ROOT'].PATH_SITE.PATH_IMG_COR;
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
	
	static function opcoes(){

		$return = array();

		$query = query($sql = "SELECT * FROM corgrupo ORDER BY nome ");

		while($fetch = fetch($query)){
			$return[$fetch->id] = $fetch->nome;
		}

		return $return;
	}

}

?>