<?php
	class catalogo extends base {
		var $id;
		var $st_ativo;
		var $titulo;
		var $edicao;
		var $arquivo;
		var $pathfile;
		var $data_cadastro;
		
		
		public function salva(){
			if($this->st_ativo=='S'){
				query("UPDATE catalogo SET st_ativo = 'N'");
			}
			
			if(array_key_exists('file',$_SESSION)){
				$this->arquivo = str_replace(" ","_",strtolower(stringAsTag($this->titulo)))."_".str_replace("/","",$this->edicao).".pdf";
				move_uploaded_file($_SESSION['file']['tmp_name'], "img/catalogo/download/{$this->arquivo}");
				unset($_SESSION['file']);
			}
			
			parent::salva();
		}
		
		public function exclui(){
			if(is_dir($this->pathfile)){
				$diretorio = dir($this->pathfile);
				while($arquivo = $diretorio->read()){
					if(($arquivo != '.') && ($arquivo != '..')){
						unlink($this->pathfile.'/'.$arquivo);
					}
				}
				$diretorio->close();
				@rmdir($this->pathfile);
			}
			query("DELETE FROM catalogoimagem WHERE catalogo_id = {$this->id}");
			
			parent::exclui();
		}
		
		public function validaDados(&$msg=''){
			if(!is_set($this->titulo)){
				$msg .= tag("p","Digite o título desse catálogo.");
			}
			if(!is_set($this->edicao)){
				$msg .= tag("p","Digite a edição desse catálogo.");
			}
			
			if( file_tratamento("file_pdf", $msg, $file) ){
				$arr = explode(".",$file['name']);				
				if(strtolower($arr[sizeof($arr)-1])!="pdf"){
					$msg .= tag("p","O arquivo para downlod precisa ser .pdf.");
				}else{
					$_SESSION['file'] = $file;
				}
			}
			
			return $msg=='';
		}
		
		public function getDataCadastroFormatada(){
			return formata_datahora_br($this->data_cadastro);
		}
	}
?>