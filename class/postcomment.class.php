<?php
class postcomment extends base {
	var $id;
	var $post_id;
	var $cadastro_id;
	var $postcomment_id;
	var $st_ativo;
	var $st_aviseme;
	var $autor;
	var $email;
	var $website;
	var $comentario;
	var $data_cadastro;

	public function valida(&$erros=''){
		if($this->autor==''){
			$erros .= "Digite seu nome. <br />";
		}
		if(!is_email($this->email)){
			$erros .= "Digite seu e-mail corretamente. <br />";
		}
		if($this->comentario==''){
			$erros .= "Digite seu assunto.";
		}
		if($erros!=''){
			return false;
		}
		return true;
	}
	
	public function salva(){
		if($this->postcomment_id && $this->st_ativo=='S'){
			$postcomment = new postcomment($this->postcomment_id);
			$post = new post($postcomment->post_id);
			if($postcomment->st_aviseme=='S'){
				$email = new email();
				$email->addTo($postcomment->email,$postcomment->autor);
				$email->addHtml("<h2>Seu comentário recebeu um reply</h2><br /><em>{$this->comentario}</em><br />Autor:{$this->autor}<br /><a href='".config::get('URL')."index.php/post/{$post->id}/{$post->titulo}'>Veja o post</a>");
				$email->send(config::get('EMPRESA')." - Comentário Reply.");
			}
		}
		parent::salva();
	}
}
