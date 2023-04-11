<?php

class caracvalor extends base {

	var
		$id
		,$carac_id
		,$st_ativo
		,$nome
		,$nome_es
		,$nome_in
		,$descricao
		,$descricao_es
		,$descricao_in
		,$imagem
		,$serial;

	private
		$objFile;

	private
		$arrSerial;

	public function __get($key){
		if(substr($key,0,6)=='serial'){
			if(!$arrSerial){
				$this->arrSerial = unserialize($this->serial);
			}
			list($key, $field) = explode('_', $key);
			return $this->arrSerial[$field];
		}
	}

	public function validaDados(&$erro=array()){
		return sizeof($erro)==0;
	}

	public function setFile($file){
		if($file['tmp_name']!=''){
			$this->objFile = $file;
		}
	}

	public function salva(){

		$this->serial = serialize($this->serial);

		if(parent::salva()){
			// Só vale para matéria prima
			if($this->carac_id==1){

				if($this->objFile&&$this->objFile['name']!=''){

					$image_name = $this->id.'.jpg' ;

					foreach ( array('img/materiaprima/130x130/') as $path ){

						$path_fisico = "{$path}{$image_name}";
						//echo $path_fisico;
						@unlink($path_fisico);
						copy($this->objFile['tmp_name'], $path_fisico);

						$temp  = explode('x',$path) ;
						$width = (int) preg_replace( '/[a-zA-Z\/_-]/', '', $temp[0]);
						$height= (int) preg_replace( '/[a-zA-Z\/_-]/', '', $temp[1]);

						recortaImagem($path_fisico, $width, $height);
						query('UPDATE caracvalor SET imagem = \''.$image_name.'\' WHERE id = '.$this->id);

					}
				}
			}
		}
		return true;
	}

	public function getColecoesMateriaPrima(){

		$return = array();
		$query = query($sql="SELECT * FROM caracvalor WHERE carac_id = 1 AND st_ativo = 'S' ORDER BY nome");

		while($fetch=fetch($query)){

			$serial = unserialize($fetch->serial);
			//printr($serial);

			if(!in_array($serial['colecao'], $return)){
				$return[] = $serial['colecao'];
			}

		}

		//printr($return);

		return $return;
	}

	public function getMateriaPrimaByColecao($colecao){

		$return = array();
		$query = query($sql="SELECT * FROM caracvalor WHERE carac_id = 1 AND st_ativo = 'S' ORDER BY nome");

		while($fetch=fetch($query)){

			$serial = unserialize($fetch->serial);

			if($serial['colecao']==$colecao){
				$fetch->serial = $serial;
				$return[] =	$fetch;
			}
		}

		//printr($return);

		return $return;
	}

	public function getMateriaPrimaByItemId($item_id){
		$return = array();

		$item_id = intval($item_id);

		if($item_id>0){

			$query = query($sql="SELECT
									caracvalor.id
									,caracvalor.nome
								FROM
									itemcarac
								INNER JOIN caracvalor ON (itemcarac.caracvalor_id = caracvalor.id)
								WHERE
									1=1
								AND st_ativo = 'S'
								AND itemcarac.item_id = {$item_id}
								AND itemcarac.carac_id = 1
								ORDER BY
									caracvalor.nome
								");

			while($fetch=fetch($query)){
				$return[] =	$fetch;
			}
		}
		return $return;
	}

	public function getGravacoesByItemId($item_id){

		$return = array();

		$item_id = intval($item_id);

		if($item_id>0){

			$query = query($sql="SELECT
									caracvalor.id
									,caracvalor.nome
								FROM
									itemcarac
								INNER JOIN caracvalor ON (itemcarac.caracvalor_id = caracvalor.id)
								WHERE
									1=1
								AND st_ativo = 'S'
								AND itemcarac.item_id = {$item_id}
								AND itemcarac.carac_id = 2
								ORDER BY
									caracvalor.nome
								");

			while($fetch=fetch($query)){
				$return[] =	$fetch;
			}
		}
		return $return;
	}

	public function exclui(){
		query("DELETE FROM itemcarac WHERE caracvalor_id = {$this->id}");
		return parent::exclui();
	}
}

?>