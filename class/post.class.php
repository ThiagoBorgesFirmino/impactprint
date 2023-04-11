<?php
class post extends base {
	var $id;
	var $cadastro_id;
	var $st_ativo;
	var $st_enviado;
	var $titulo;
	var $autor;
	var $area;
	var $texto;
	var $data_cadastro;
	
	public function salva(){
		if($this->valida()){
			parent::salva();
		}
		return false;
	}
	public function valida(){
		$erro = '';
		if($this->titulo==''){
			$erro .= tag('p','Digite o titulo.');
		}
		if($this->texto==''){
			$erro .= tag('p','Digite o texto.');
		}
		if($this->autor==''){
			//$erro .= tag('p','Digite o autor.');
		}
		if($this->area==''){
			$erro .= tag('p','Digite a area.');
		}
		
		if($erro!=''){
			$_SESSION['post_erro'] = $erro;
			return false;
		}
		
		//$_SESSION['sucesso'] = tag('p','Dados salvos com sucesso!');
		return true;
	}
	
	public function getLink(){
		return config::get('URL')."index.php/post/{$this->id}/{$this->titulo}";
	}
	
	public function getDataFormatada(){
		return formata_data_br($this->data_cadastro);
	}
	public function getTextoFormatado(){
		return substr($this->texto,0,200)." ...";
	}
	public function getTextoFormatadoListagem(){
		return substr($this->texto,0,500)." ...";
	}
	public function TextoFormatadoListagemMeta(){
		return strip_tags(substr($this->texto,0,200)." ...");
	}
	
	public function salvaImagens(){
		if(file_tratamento("post_img_nova", $msg, $file)){
			$postimagem = new postimagem();
			$postimagem->post_id = $this->id;
			$postimagem->st_ativo = 'S';
			
			if($postimagem->salva()){
				$postimagem->imagem = "pi{$postimagem->id}-{$file['name']}";
				move_uploaded_file($file['tmp_name'],"img/postimagem/{$postimagem->imagem}");
				$postimagem->atualiza();
			}
		}
		
		if(array_key_exists("postimagem",$_REQUEST)){
			foreach($_REQUEST['postimagem'] as $key=>$value){
				$postimagem = new postimagem($value['id']);
				$postimagem->st_ativo = $value['st_ativo'];
				if(file_tratamento("post_img_{$postimagem->id}", $msg, $file)){
					$postimagem->imagem = "pi{$postimagem->id}-{$file['name']}";
					move_uploaded_file($file['tmp_name'],"img/postimagem/{$postimagem->imagem}");
				}
				$postimagem->atualiza();
			}
		}
		
		if(array_key_exists("img_excluir",$_REQUEST)){
			foreach($_REQUEST['img_excluir'] as $key=>$value){
				$postimagem = new postimagem($key);
				unlink("img/postimagem/{$postimagem->imagem}");
				$postimagem->exclui();
			}
		}
	}
	
	static function getUltimosPost(&$t){
		$query = query("SELECT * FROM post WHERE st_ativo='S' ORDER BY data_cadastro DESC");
		while($fetch=fetch($query)){
			
			$t->post = new post($fetch->id);
			
			if($fetch->id){		
				if( preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', $fetch->data_cadastro, $matches) ){
					list(,$ano,$mes,$dia,$hora,$minuto,$segundo)=$matches;
					$t->dia = $dia;
					$t->mes = get_mes($mes);
					$t->ano = $ano;
				}else{
					preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', $fetch->data_cadastro, $matches);
					list(,$ano,$mes,$dia)=$matches;
					$t->dia = $dia;
					$t->mes = get_mes($mes);
					$t->ano = $ano;
				}
			}
			
			$t->qtd_comments = rows(query($sql="SELECT * FROM postcomment WHERE post_id = {$fetch->id} AND st_ativo='S' AND (postcomment_id = 0 OR postcomment_id is NULL)"));
			
			
			// $_query =  query("SELECT * FROM postimagem WHERE post_id = {$fetch->id} AND st_ativo='S'");
			// while($_fetch=fetch($_query)){
				// $t->postimagem = $_fetch;
				// $t->parseBlock("BLOCK_POST_IMAGEM",true);
			// }
			// if(rows($_query)>0){
				// $t->parseBlock("BLOCK_IMAGENS",true);
			// }
		
			$t->parseBlock("BLOCK_BLOG_POSTS",true);
		}
	} 
}
?>