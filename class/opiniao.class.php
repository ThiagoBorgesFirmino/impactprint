<?php
	class opiniao extends base {
		var $id;
		var $st_ativo;
		var $titulo;
		var $texto;
		var $autor;
		var $area;
		var $imagem;
		var $ordem;
		var $data_cadastro;
		
		public function salva(){
			if($this->valida()){
				parent::salva();
			}
		}
		public function valida(){
		
			$width  = 155;
			$height = 155;
			
			$erro = '';
			if($this->titulo==''){
				$erro .= tag('p','Digite o titulo.');
			}
			if($this->texto==''){
				$erro .= tag('p','Digite o texto.');
			}
			if($this->autor==''){
				$erro .= tag('p','Digite o autor.');
			}
			if($this->area==''){
				$erro .= tag('p','Digite a area do autor.');
			}
			
			if(file_tratamento('img_opiniao',$msg,$file)){
				if(isImagemPNG($file['name']) || isImagemGIF($file['name'])){
				
					list($w,$h) = getimagesize($file['tmp_name']);
					if($w!=$width || $h!=$height){
						$erro .= tag('p','O tamanho da imagem não pode ser maior que '.$width.'x'.$height.'px.');
					}else{
						$this->imagem = $file['name'];
						move_uploaded_file($file['tmp_name'],"img/opiniao/{$this->imagem}");
					}
				
				}else{
					$erro .= tag('p','A imagem deve ser png ou gif .');
				}
			}else{
				$erro .= $msg;
			}
			
			if($erro!=''){
				$_SESSION['erro'] = $erro;
				return false;
			}
			
			$_SESSION['sucesso'] = tag('p','Dados salvos com sucesso!');
			return true;
		}

		static function opinioes(&$t){
			$query = query("SELECT * FROM opiniao WHERE st_ativo = 'S' ORDER BY ordem");
			while($fetch=fetch($query)){
				$t->opiniao = $fetch;				
				if($fetch->imagem!="")$t->parseBlock("BLOCK_OP_IMAGEM");
				$t->parseBlock("BLOCK_OPINIAO",true);
			}
		}
	}
?>