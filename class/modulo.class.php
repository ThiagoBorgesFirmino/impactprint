<?php

class modulo extends base {

	var
		$id
		,$modulo_id
		,$st_ativo
		,$st_admin
		,$nome
		,$arquivo
		,$ordem;

	public function validaDados(&$erro=array()){
		return true;
	}
	
	// Get modulo root
	public function getModuloRoot(){

		$ret = $this;
		$pesquisa = $ret;

		while($pesquisa->id > 0){
			$ret = $pesquisa;
			$pesquisa = new modulo($pesquisa->modulo_id);
		}

		return $ret;
	}

	static function opcoes_root(){

		$return = array();

		$sql="SELECT id, nome FROM modulo WHERE st_ativo = 'S' ORDER BY modulo_id, nome";

		$query = query($sql);

		$return[0] = "Raiz";

		while($fetch=fetch($query)){
			$return[$fetch->id] = $fetch->nome;
		}

		return $return ;

	}

	// Get parents
	public function getParents(){

		$ret = array();

		$pesquisa = $this;
		// $ret[] = $pesquisa;

		while($pesquisa->id > 0){
			$ret[] = $pesquisa;
			$pesquisa = new modulo($pesquisa->modulo_id);
		}

		return $ret;
	}
	
	public function getActionLink(){
		if($this->arquivo!=''){
			return PATH_SITE.'admin.php/'."{$this->arquivo}/";
		}
		else {
			return PATH_SITE."admin.php/go_modulo/{$this->id}/";
		}
	}
	
}
