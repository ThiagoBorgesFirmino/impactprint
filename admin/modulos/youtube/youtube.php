<?php

class modulo_youtube extends modulo_admin {

    public $arquivo = 'youtube';

    // Pesquisa
    public function pesquisa(){

        $t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
        $t->h1 = h1($this->modulo->nome);

        if(request('action')=='editar'
        ||substr(request('action'),0,6)=='salvar'){
            $this->editar($t);
            return;
        }

        if(request('action')=='excluir'){
            $cmsitem = new cmsitem(intval(request('id')));
            if($cmsitem->id){
                $cmsitem->exclui();
            }
        }

        $grid = new grid();

        $grid->sql = $sql =
            "
        SELECT
            id
            ,concat('<img src=".PATH_SITE."upload/', img1, ' width=300px />') Imagem
            ,titulo Nome
            ,data_publicacao Data_Publicacao
            ,st_ativo Status
        FROM
            cmsitem
        WHERE 1=1
        AND tipo = 'youtube'
        ORDER BY
            data_publicacao DESC
            ";

        $filtro = new filtro();

        $grid->metodo = $this->arquivo;
        $grid->filtro = $filtro ;

        $t->grid = $grid->render();

        $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }

    public function editar($t){

        $t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
        $t->h1 = h1($this->modulo->nome);

        $modulo = $this->modulo;

        $cmsitem = new youtube(intval(request('id')));

        if(substr(request('action'),0,6)=='salvar'){

            $next = substr(request('action'),7,strlen(request('action')));
            $cmsitem->set_by_array(@$_REQUEST['cmsitem']);
            $cmsitem->titulo = addslashes($cmsitem->titulo);            

            try {
			
				if(!$cmsitem->id){
					
					$url = parse_url($link=$_REQUEST['link']);

					if(!$url){
						throw new Exception("URL inválida");
					}

					// Procurar o vídeo no youtube
					$youtubeapi = new youtubeapi();
					$video = $youtubeapi->get_video($link);

					if(!$video){
						throw new Exception('Não foi possível identificar o vídeo no youtube');
					}

                    // printr($video);
                    // die();

					$cmsitem->custom1 = addslashes($video->videoId);

					$cmsitem->titulo = addslashes($video->title);
					$cmsitem->chamada = addslashes($video->author);
					$cmsitem->conteudo = addslashes($video->content);
					$cmsitem->link = $video->link;

					// $cmsitem->data_publicacao = $video->published;
					$cmsitem->data_publicacao = $video->updated;

                    @$_REQUEST['file_img1'] = $video->thumbnail;

					// file_put_contents('upload/'.)
					// $cmsitem->img1 = $video->thumbnail;

					$cmsitem->salva();

				}
				else {

					$cmsitem->data_publicacao = trim(butil::to_bd_date($cmsitem->data_publicacao).' '.$_REQUEST['cmsitem_hora_publicacao']);

					$cmsitem->validaDados();
					$cmsitem->salva();

				}

                $cmsitem = new cmsitem($cmsitem->id);

                if(request('excluirimg1')){
                    $cmsitem->excluiImagem('img1');
                }

                if(request('excluirimg2')){
                    $cmsitem->excluiImagem('img2');
                }

                if(request('excluirimg3')){
                    $cmsitem->excluiImagem('img3');
                }

                $_SESSION['sucesso'] = 'Dados salvos com sucesso';

            }
            catch (Exception $ex){
                $_SESSION['erro'] = $ex->getMessage();
            }

            if(trim(@$next)!=''){
                $this->afterSave($next,'youtube');
                return;
            }
        }

        if(request('action')=='sair'){
            $this->afterSave('sair',$this->modulo->arquivo,$cmsitem);
        }

        $t->parseBlock('BLOCK_TOOLBAR');

        $t->h1 = h1("{$modulo->nome} - {$cmsitem->titulo}");
		
		$edicao = '';
		$edicao .= inputHidden('cmsitem[id]', $cmsitem->id);
		$edicao .= inputHidden('cmsitem[autor_id]', $cmsitem->autor_id ? $cmsitem->autor_id : butil::decode($_SESSION['CADASTRO']->id));
		$edicao .= inputHidden('cmsitem[tipo]', 'youtube');
		
		if(!$cmsitem->id){
		
			$edicao .= '<div class="box-block">';
			$edicao .= inputSimples('link', '', 'Cole aqui o link do vídeo no youtube:', 60);
			$edicao .= '</div>';
		
		}
		else {

			$edicao .= '<div class="box-block">';
			$edicao .= inputSimples('cmsitem[titulo]', $cmsitem->titulo, 'Titulo:', 60);
            $edicao .= inputSimples('cmsitem[custom1]', $cmsitem->custom1, 'Código do vídeo:', 60);
			$edicao .= '</div>';

			$edicao .= '<div class="box-block">';
			$edicao .= tag('h2', 'Data/Hora e status de publicação');
			$edicao .= select('cmsitem[st_ativo]', $cmsitem->st_ativo, 'Status:', array('N'=>'Rascunho','S'=>'Publicado'));
			$edicao .= inputData('cmsitem[data_publicacao]', $cmsitem->id?$cmsitem->getDataPublicacaoFormatado():date('d/m/Y'), 'Data da publicação');
			$edicao .= inputHora('cmsitem_hora_publicacao', $cmsitem->id?$cmsitem->getHoraPublicacaoFormatado():date('H:i:s'), 'Horário de publicação');
			$edicao .= '</div>';

			// $edicao .= $this->add_imagem($cmsitem,'img1',460,260);
		
		}

        $edicao .= str_repeat(tag('br'),5);

        $t->edicao = $edicao;

        // $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }

    static function widget(){

        $t = new Template('admin/modulos/youtube/tpl.youtube.html') ;

        $opts = array('limit'=>1);
        $itens = get_cmsitem('youtube',$opts);

        $i = 0;
        foreach($itens as $item){
            $t->youtube = $item;
            if($i == 0){
                $t->parseBlock('BLOCK_YOUTUBE_ULTIMO');
            }
            else {
                $t->parseBlock('BLOCK_YOUTUBE');
            }
            $i ++;
        }

        return $t->getContent();

    }

}
