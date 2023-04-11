<?php

class splash extends base {

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

	const WIDTH = 80;
	const HEIGHT = 80;

	public function salva(){
		
		$return = false;
		if(parent::salva()){

			if($this->objFile){
				
				$extensao = substr($this->objFile['name'],-3);
				
				$this->imagem = stringAsTag("{$this->nome} {$this->id}").".{$extensao}";
				
				$upload = new upload();
				
				$upload->setFile($this->objFile);
				// $upload->diretorio = $_SERVER['DOCUMENT_ROOT'].PATH_SITE.PATH_IMG_SPLASH;
				$upload->diretorio = PATH_ABS.DIRECTORY_SEPARATOR.'img/splash/';				

				$upload->grava($this->imagem);
				
				unset($upload);
				
				query("update ".$this->get_table_name()." set imagem = '{$this->imagem}' where id = {$this->id}");
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
		
		$widthMax = self::WIDTH;
		$heightMax = self::HEIGHT;

		
		if(file_tratamento('file_nova', $msg, $file)){
			list($width,$height) = getimagesize($file['tmp_name']);
			if($width > $widthMax || $height > $heightMax){
				$erro[] = tag('p','O Splash tem que ser menor que '.$widthMax.'px por '.$heightMax.'px.');
			}
		}else{
			if($msg!="") $erro[] = tag('p',$msg);
		}

		if(!is_set($this->nome)) $erro[] = tag('p',"Digite o nome do splash.");
		if(!$this->valida_unico('nome')) $erro[] = tag('p',"Já existe um splash com esse nome '{$this->nome}'.");
		
		
		if(sizeof($erro)>0){
			return false;
		}
		return true;
	}

	public function validaExclusao(&$erro=array()){
		if(!$this->id){
			$erro[] = "Chave nao definida";
		}
		if(sizeof(results("select * from item where splash_id = {$this->id}"))>0){
			$erro[] = "Produtos relacionados, não é possível excluir";
		}
		return sizeof($erro)==0;
	}
	
	static function opcoes(){

		$return = array();
		$query = query($sql="select * from splash order by nome");
		while($fetch=fetch($query)){
			$return[$fetch->id] = $fetch->nome;
		}
		return $return;

	}

	static function getNomeSplash($id=""){
		return @fetch(query("SELECT nome FROM splash WHERE id = ".intval($id)))->nome;
	}

	public function exclui(){
		query("UPDATE item SET splash_id = null WHERE splash_id = {$this->id}");
		return parent::exclui();
	}

	
}

?>