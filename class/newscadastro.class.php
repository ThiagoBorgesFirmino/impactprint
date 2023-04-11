<?php

class newscadastro extends base {

	var
		$id,
		$st_ativo,
		$nome,
		$email,
		$data_cadastro,
		$sexo;

	public function validaDados(& $erros=array()){
		$erros = array();

		if(($this->nome==" ")||($this->nome=="Nome")){
			$erros[] = 'Nome em branco';
		}

		if($this->email==" "){
			$erros[] = 'Email em branco';
		}

		if(!is_email($this->email)){
			$erros[] = 'Email inválido';
		}

		// if(!$this->valida_unico('email')){
			// $erros[] = 'E-mail já existe no banco de dados';
		// }
		
		// if((!$this->sexo)||($this->sexo == 'N')){
			// $erros[] = 'Escolha um sexo';
		// }
		//printr($erros = array());
		return sizeof($erros)==0;
	}

	public function salva(){
		if(parent::salva()){
			if($this->st_ativo=='S'){
				query("UPDATE cadastro SET st_newsletter = 'S' where email = '{$this->email}'");
			}
			
		}
	}
	
}


?>