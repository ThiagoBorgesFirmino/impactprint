<?php

class modulo_destaquehome extends modulo_admin {

    public $arquivo = 'destaquehome';

    // Pesquisa
    public function pesquisa(){

        if(request('popup')){
            $t = new TemplateAdminPopup('admin/tpl.admin-cadastro-generico.html');
        }
        else {
            $t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
        }

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
            ,concat('<img src=".PATH_SITE."', img1, ' width=100px />') Imagem
            ,titulo Texto
            ,custom1 Link
            ,ordem Ordem
            ,st_ativo Status
        FROM
            cmsitem
        WHERE 1=1
        AND tipo = '{$this->arquivo}'
        ORDER BY
            ordem, titulo
            ";

        $filtro = new filtro();

        $grid->metodo = $this->arquivo;
        $grid->filtro = $filtro ;

        $edicao = '';
        $edicao .= $grid->render();

        $t->edicao = $edicao;

        $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }

    public function editar($t){

        // Se for pop-up, carrega template de pop-up
        if(request('pop')){
            $t = new TemplateAdminPop('admin/tpl.admin-cadastro-generico.html');
        }

        $t->h1 = h1($this->modulo->nome);

        $destaquehome = new destaquehome(intval(request('id')));

        if(substr(request('action'),0,6)=='salvar'){

            $erros = array();

            $next = substr(request('action'),7,strlen(request('action')));
            $destaquehome->set_by_array(@$_REQUEST['destaquehome']);
            $destaquehome->ordem = intval($destaquehome->ordem);

            if(!$destaquehome->id){
                $destaquehome->data_publicacao = date('Y-m-d H:i:s');
            }

            try {

                if(!$destaquehome->titulo){
                    throw new Exception('Digite o titulo');
                }

                if(!$destaquehome->salva()) {
                    throw new Exception('Falha ao salvar os dados');
                }

                if(request('excluirimg1')){
                    $destaquehome->excluiImagem('img1');
                }

                if(request('excluirimg2')){
                    $destaquehome->excluiImagem('img2');
                }

                if(request('excluirimg3')){
                    $destaquehome->excluiImagem('img3');
                }

                $_SESSION['sucesso'] = 'Dados salvos com sucesso';

            }
            catch (Exception $ex){
                $_SESSION['erro'] = $ex->getMessage();
            }

            if(trim(@$next)!=''){
                $this->afterSave($next,'destaquehome');
                return;
            }
        }

        if(request('action')=='sair'){
            $this->afterSave('sair',$this->arquivo,$destaquehome);
        }

        $t->parseBlock('BLOCK_TOOLBAR');

        $edicao = '';

        $edicao .= inputHidden('id', $destaquehome->id);
        $edicao .= inputHidden('destaquehome[id]', $destaquehome->id);
        $edicao .= inputHidden('destaquehome[autor_id]', $destaquehome->autor_id ? $destaquehome->autor_id : butil::decode($_SESSION['CADASTRO']->id));
        $edicao .= inputHidden('destaquehome[tipo]', 'destaquehome');

        $edicao .= tag('div class="box-block" xstyle="width:49%;float:left;height:260px"',
            tag('h2', 'Dados básicos')
            .select('destaquehome[st_ativo]', $destaquehome->st_ativo, 'Ativo?:', array('S'=>'Sim','N'=>'Nao'))
            .inputSimples('destaquehome[titulo]', $destaquehome->titulo, 'Texto:', 45, 50)
            .inputSimples('destaquehome[custom1]', $destaquehome->custom1, 'Link:', 200, 200)
            .inputSimples('destaquehome[ordem]', $destaquehome->ordem, 'Ordem de aparição:', 10, 10)
        );

        $edicao .= $this->add_imagem($destaquehome,'img1',630,390);

        $edicao .= tag('br clear="all"');

        $t->edicao = $edicao;

        // $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }

    static function widget(){

        $t = new Template(__DIR__.'/tpl.destaquehome.html') ;
        $t->path = PATH_SITE;

        $opts = array('limit'=>4,'order_by'=>'ordem');
        $itens = get_cmsitem('destaquehome',$opts);

        $i = 0;
        foreach($itens as $item){
            $t->destaquehome = $item;
            $t->parseBlock('BLOCK_DESTAQUEHOME');
            $i ++;
        }

        return $t->getContent();

    }
	
	static function widgetmobile(){

        $t = new Template(__DIR__.'/tpl.destaquehomemobile.html') ;
        $t->path = PATH_SITE;

        $opts = array('limit'=>4,'order_by'=>'ordem');
        $itens = get_cmsitem('destaquehome',$opts);

        $i = 0;
        foreach($itens as $item){
            // $t->destaquehome = $item;
            // $t->parseBlock('BLOCK_DESTAQUEHOME_MOBILE');
            // $i ++;
			
			$t->destaquehome = $item;
            $t->active = $i==1?'active':'';

            // $t->frasehome = $item;
            // $t->parseBlock('BLOCK_FRASEHOME');
            // $i ++;

            $t->i = $i ++;
            $t->parseBlock('BLOCK_BANNERPRINCIPALMOBILE', true);
            $t->parseBlock('BLOCK_BANNERPRINCIPALMOBILE_THUMB', true);
        }

        return $t->getContent();

    }

}