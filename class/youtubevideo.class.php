<?php

// Modelo de dados para a tabela youtubevideo
class youtubevideo extends base {

	var $id;
	var $item_id;
	var $youtube_id;
	var $st_ativo;
	var $title;
	var $author;
	var $content;
	var $url;
	var $original_url;
	var $published;
	var $updated;
	var $duration;
	var $favorite_count;
	var $view_count;
	var $thumbnail;
	var $dt_cadastro;
	var $dt_alteracao;

	public function exclui(){
		$id = intval($this->id);
		if($id>0){
			//Excluir
			return parent::exclui();
		}
	}

	public function getYoutubeId(){
		return $this->youtube_id;
	}

	public function getDetalhe(){
		return INDEX.'ugtv/'.butil::stringAsTag($this->title).'/'.$this->youtube_id;
	}

	public function getDetalheAbsoluto(){

		$url = 'http://'.$_SERVER['SERVER_NAME'];
		if(@$_SERVER['SERVER_PORT'] != '80'){
			$url .= ':'.$_SERVER['SERVER_PORT'].'';
		}

		return $url.$this->getDetalhe();
	}
	
	
	public function validaDadosSalva($item_id,$url=''){

        $this->original_url = $url;
		if($url==''){
			$this->original_url = $url = request('linkyoutube');
		}		
			
		// if($this->id ){
			// $url = $this->url;
		// }
		
		try {

            if(!$url){
                return;
            }

            $url = parse_url($link=$url);

            if(!$url){
                throw new Exception("URL inválida");
            }

            if(!isset($url['query'])){
                throw new Exception("URL inválida - não foi possível identificar a query string");
            }

            parse_str($url['query'], $param);
            if(!@$param['v']){
                throw new Exception("URL inválida - não foi possível identificar a query string - v");
            }

            $youtube_id = $param['v'];

            // Procurar o vídeo no youtube
            $youtubeapi = new youtubeapi();
            $video = $youtubeapi->get_video($link);

            //printr($video);die();

            if(!$video){
                throw new Exception('Não foi possível identificar o vídeo no youtube');
            }

            $video->url = $video->link;

            if(!$this->id){
                $youtubevideo = new youtubevideo(array(
                    'item_id' => $item_id
                    ,'url' => $video->url
                ));
            }
            else {
                $youtubevideo = $this;
            }

            $youtubevideo->youtube_id = $youtube_id;
            $youtubevideo->st_ativo = $this->st_ativo;

            // printr($video);
            // die("LINK YOUTUBE!!");

            $youtubevideo->title = addslashes($video->title);
            $youtubevideo->author = addslashes($video->author);
            //$youtubevideo->content = addslashes($video->content);
            $youtubevideo->url = $video->url;
            $youtubevideo->published = $video->published;
            $youtubevideo->thumbnail = $video->thumbnail;
            $youtubevideo->item_id = $item_id;
            $youtubevideo->original_url = $this->original_url;

            // printr($youtubevideo);die();

            $youtubevideo->salva();

        }
        catch (Exception $ex){
            //$_SESSION['erro'] = $ex->getMessage();
        }
	}
}