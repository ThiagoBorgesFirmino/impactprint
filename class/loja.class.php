<?php 

class loja extends base {

	var
		$id,
		$status,
		$cadastro_id,
		$representante_id,
		$endereco_id,
		$cod_integracao,
		$imagem,
		$st_aparece_site,
		$web_site;

	static function status(){
		return array( 'P' => 'Em analise', 'A' => 'Aprovado', 'R' => 'Reprovado' );
	}

	static function st_aparece_site(){
		return array( 'S' => 'Sim', 'N'	=> 'Nao' );
	}

	public function salva(){
	
		if(parent::salva()){
		
			$file_imagem = @$_FILES['file_imagem'];			

			if($file_imagem['name']!='' ) {

				$image_name = "img/loja/{$this->id}.jpg" ;

				$path_fisico = "../{$image_name}";
				//echo $path_fisico;
				@unlink($path_fisico);
				copy($file_imagem['tmp_name'], $path_fisico);

				query("UPDATE loja SET imagem = '{$image_name}' WHERE id = {$this->id}");
			}
		}
	}

	public function get_by_credenciais($email, $senha){

		$sql = 
		"
		SELECT
			loja.*
		FROM
			loja
		INNER JOIN cadastro ON ( cadastro.id = loja.cadastro_id )
		AND loja.status = 'A'
		AND cadastro.email = '%1s'
		AND cadastro.senha = '%2s'
		";

		$query = query(sprintf($sql, $email, encode($senha)));

		$fetch = fetch($query);

		if(@$fetch->id){
			$this->load_by_fetch($fetch);
			return true;
		}

		return false;

	}

	public function is_email_cadastrado($email){
	
		$sql = 
		"
		SELECT
			loja.*
		FROM
			loja
		INNER JOIN cadastro ON ( cadastro.id = loja.cadastro_id )
		AND cadastro.email = '%1s'
		";

		$query = query(sprintf($sql, $email));
		$fetch = fetch($query);

		if(@$fetch->id){
			return true;
		}

		return false;

	}


}

?>