<?php

class modulo_paginainstitucional extends modulo_admin {

    public $arquivo = 'paginainstitucional';

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
            -- ,concat('<img src=".PATH_SITE."upload/', img1, ' width=300px />') Imagem
            ,titulo Titulo
            -- ,custom1 Link
            ,ordem Ordem
            ,st_ativo Status
        FROM
            cmsitem
        WHERE 1=1
        AND tipo = 'paginainstitucional'
        ORDER BY
            ordem, titulo
            ";

        $filtro = new filtro();

        $grid->metodo = 'paginainstitucional' ;
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

        $paginainstitucional = new paginainstitucional(intval(request('id')));

        if(substr(request('action'),0,6)=='salvar'){

            $erros = array();

            $next = substr(request('action'),7,strlen(request('action')));
            $paginainstitucional->set_by_array(@$_REQUEST['paginainstitucional']);
            $paginainstitucional->ordem = intval($paginainstitucional->ordem);

            if(!$paginainstitucional->id){
                $paginainstitucional->data_publicacao = date('Y-m-d H:i:s');
            }

            try {

                if(!$paginainstitucional->titulo){
                    throw new Exception('Digite o titulo');
                }

                if(!$paginainstitucional->salva()) {
                    throw new Exception('Falha ao salvar os dados');
                }

                if(request('excluirimg1')){
                    $paginainstitucional->excluiImagem('img1');
                }

                if(request('excluirimg2')){
                    $paginainstitucional->excluiImagem('img2');
                }

                if(request('excluirimg3')){
                    $paginainstitucional->excluiImagem('img3');
                }

                $_SESSION['sucesso'] = 'Dados salvos com sucesso';

            }
            catch (Exception $ex){
                $_SESSION['erro'] = $ex->getMessage();
            }

            if(trim(@$next)!=''){
                $this->afterSave($next,'paginainstitucional');
                return;
            }
        }

        if(request('action')=='sair'){
            $this->afterSave('sair','paginainstitucional',$paginainstitucional);
        }

        $t->parseBlock('BLOCK_TOOLBAR');

        $edicao = '';

        // butil::printr($paginainstitucional->chave);

        $edicao .= inputHidden('id', $paginainstitucional->id);
        $edicao .= inputHidden('paginainstitucional[id]', $paginainstitucional->id);
        $edicao .= inputHidden('paginainstitucional[autor_id]', $paginainstitucional->autor_id ? $paginainstitucional->autor_id : butil::decode($_SESSION['CADASTRO']->id));
        $edicao .= inputHidden('paginainstitucional[tipo]', 'paginainstitucional');

        $edicao .= tag('div class="box-block" xstyle="width:49%;float:left;height:260px"',
            tag('h2', 'Dados básicos')
            .inputSimples('paginainstitucional[titulo]', $paginainstitucional->titulo, 'Título:', 45, 50)
            // .inputSimples('paginainstitucional[custom1]', $paginainstitucional->custom1, 'Link:', 200, 200)
            .inputSimples('paginainstitucional[ordem]', $paginainstitucional->ordem, 'Ordem de aparição:', 10, 10)
            .editor('paginainstitucional[conteudo]', $paginainstitucional->conteudo, 'Conteúdo:')
        );

        // $edicao .= $this->add_imagem($paginainstitucional,'img1',256,60);

        $edicao .= tag('br clear="all"');

        $t->edicao = $edicao;

        // $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }

    static function widget(){

        $t = new Template('admin/modulos/paginainstitucional/tpl.paginainstitucional.html') ;

        $opts = array('order_by'=>'ordem');
        $itens = get_cmsitem('paginainstitucional',$opts);

        $i = 0;
        foreach($itens as $item){
            $t->index = INDEX;
            $t->paginainstitucional = $item;
            $t->parseBlock('BLOCK_PAGINAINSTITUCIONAL');
            $i ++;
        }

        return $t->getContent();

    }

}