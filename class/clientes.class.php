<?php
	class clientes extends base{
		var $id;
		var $st_ativo;
		var $nome;
		var $imagem;
		var $data_cadastro;
	
		
		public function ValidarSalvar(){
			$erros = array();
			if($this->nome==''){
				$erros['nome'] = 'Digite um nome.';
			}
			
			if(array_key_exists('clientes_imagem',$_FILES)){
				$file = $_FILES['clientes_imagem'];
				if($file['size']>0){
					if(file_tratamento('clientes_imagem',$erroimagem)){
						
						
						// if(is_jpeg($file['name'])){
						
							list($width,$height) = getimagesize($file['tmp_name']);
								
							if($width==153 && $height==152){
							
								if(!(sizeof($erros)>0)){
								
									if(parent::salva()){
										$this->imagem = $this->nome.'_'.$this->id.".jpg";
										parent::atualiza();
										move_uploaded_file($file['tmp_name'], 'img/clientes/'.$this->imagem);									
									}
								}
							}else{
								$erros['imagem'] = 'A imagem precisa estar com tamanho 153x152px.';
							}
						// }else{
							// $erros['imagem'] = 'A imagem precisa ser .jpg';
						// }
					}else{
						$erros['imagem'] = $erroimagem;
					}
				}
			}
			
			if(sizeof($erros)>0){
				$_SESSION['erro'] = join('<br />', $erros);
			}else{
				if(parent::salva()){
					$_SESSION['sucesso'] = tag('p','Dados salvos com sucesso.');
				}
			}
		}
	
	
	}
?>