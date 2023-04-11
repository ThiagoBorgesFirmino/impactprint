<?php

class modulo_bannerprincipal extends modulo_admin {

    public $arquivo = 'bannerprincipal';

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
            $bannerprincipal = new cmsitem(intval(request('id')));
           
            if($bannerprincipal->id){
                @unlink($bannerprincipal->img1);
                $bannerprincipal->exclui();
            }
        }

        $grid = new grid();

        $grid->sql = $sql = "SELECT id, concat('<img src=".PATH_SITE."', img1, ' width=300px />') Imagem, titulo Nome, data_publicacao Data_Publicacao, st_ativo Status FROM cmsitem WHERE 1=1 AND tipo = 'bannerprincipal' ORDER BY data_publicacao DESC";

        $filtro = new filtro();
        $grid->metodo = $this->arquivo;
        $grid->filtro = $filtro ;

        $t->grid = $grid->render();

        $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }

    public function editar($t){

        $t = new TemplateAdminPop('admin/tpl.admin-cadastro-generico.html');
        $t->h1 = h1($this->modulo->nome);

        $modulo = $this->modulo;

        $cmsitem = new bannerprincipal(intval(request('id')));

        if(substr(request('action'),0,6)=='salvar'){

            $next = substr(request('action'),7,strlen(request('action')));
            $cmsitem->set_by_array(@$_REQUEST['cmsitem']);
            $cmsitem->titulo = addslashes($cmsitem->titulo);
            $cmsitem->data_publicacao = butil::to_bd_datetime($cmsitem->data_publicacao.' '.$_REQUEST['cmsitem_hora_publicacao'].':00');
			//printr($cmsitem);
			//printr($corTittulo);
		

            try {

                $cmsitem->validaDados();
                $cmsitem->salva();

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
                $this->afterSave($next,$modulo->arquivo);
                return;
            }
        }

        $t->parseBlock('BLOCK_TOOLBAR');

        $t->h1 = h1("{$modulo->nome} - {$cmsitem->titulo}");

        $edicao = '';
        $edicao .= inputHidden('cmsitem[id]', $cmsitem->id);
        $edicao .= inputHidden('cmsitem[autor_id]', $cmsitem->autor_id ? $cmsitem->autor_id : butil::decode($_SESSION['CADASTRO']->id));
        $edicao .= inputHidden('cmsitem[tipo]', 'bannerprincipal');

        $edicao .= '<div class="box-block">';
		$edicao .= inputSimples('cmsitem[titulo]', $cmsitem->titulo, 'Titulo:', 60);
		//$edicao .= textArea('cmsitem[conteudo]', $cmsitem->conteudo, 'Texto:', 60);
		$edicao .= inputSimples('cmsitem[custom1]', $cmsitem->custom1, 'Link de destino:', 60);
		$edicao .= select('cmsitem[custom2]', $cmsitem->custom2, 'Destino:', array('_self'=>'_self','_blank'=>'_blank'));
		//$edicao .= select('cmsitem[custom3]', $cmsitem->custom3, 'Cor dos textos:', array('#FFF'=>'Branco','#000'=>'Preto'));
		$edicao .= '</div>';

        // $edicao .= '<div class="box-block">';
        // $edicao .= tag('h2', 'Texto do banner');
        // $edicao .= textArea('cmsitem[custom2]', $cmsitem->custom3, 'Duas linhas de texto:', 120);
        // $edicao .= '</div>';

		$edicao .= '<div class="box-block">';
		$edicao .= tag('h2', 'Data/Hora e status de publicação');
		$edicao .= select('cmsitem[st_ativo]', $cmsitem->st_ativo, 'Status:', array('N'=>'Rascunho','S'=>'Publicado'));
		$edicao .= inputData('cmsitem[data_publicacao]', $cmsitem->id?$cmsitem->getDataPublicacaoFormatado():date('d/m/Y'), 'Data da publicação');
		$edicao .= inputHora('cmsitem_hora_publicacao', $cmsitem->id?$cmsitem->getHoraPublicacaoFormatado():date('H:i'), 'Horário de publicação');
		$edicao .= '</div>';

        $edicao .= $this->add_imagem($cmsitem,'img1',1600,380,'DeskTop' );
		
        //$edicao .= $this->add_imagem($cmsitem,'img2',1010,606,'Mobile');

        $edicao .= str_repeat(tag('br'),5);

        $t->edicao = $edicao;

        // $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }

    static function widget(&$t){

        $bannerprincipal = 'bannerprincipal';
         $imagem = 'img1';
        if(IS_MOBILE){
            $bannerprincipal = 'bannerprincipalmobile';
            $imagem = 'img1';
        }

        if($t->exists($bannerprincipal)){

            $p = new Template(__DIR__.'/tpl.'.$bannerprincipal.'.html') ;
            $p->path = PATH_SITE;
            $opts = array();
            $itens = get_cmsitem('bannerprincipal',$opts);
            
            $i = 0;
            foreach($itens as $item){
                $banner = new bannerprincipal();
                
                $banner->load_by_fetch($item);    
                $banner->img1 = $banner->$imagem;
                $p->banner = $banner;
                $p->parseBlock('BLOCK_BANNERPRINCIPAL', true);
            }
           
    
            $t->$bannerprincipal = $p->getContent();
            // return $t->getContent();
            //if($has_banner)$t->$bannerprincipal = $p->getContent();
        }

    }

}
