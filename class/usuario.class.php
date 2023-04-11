<?php 

class usuario extends base {

	var
		$id,
		$status,
		$cadastro_id,
		$endereco_id;

	static function status(){
		return array( 'A' => 'Ativo', 'I' => 'Inativo' );
	}

	public function is_valido_admin( $email, $senha ){
	
		$sql = "SELECT 
					usuario.id 
				FROM 
					usuario
					,cadastro 
				WHERE 
					usuario.cadastro_id = cadastro.id
				AND usuario.status = 'A'
				AND cadastro.email = '%s'
				AND cadastro.senha = '%s'" ;

		$query = query(sprintf($sql, ($email), ($senha) ));
		$fetch=fetch($query);

		if(@$fetch->id){
			$this->get_by_id($fetch->id);
			return true;
		}

		return false;	
	}

}

?>