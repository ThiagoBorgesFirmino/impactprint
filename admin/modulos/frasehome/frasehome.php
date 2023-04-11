<?php

class modulo_frasehome extends modulo_admin {

    public $arquivo = 'frasehome';

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
            ,concat('<img src=".PATH_SITE."',img1,' width=100px />') Imagem
            ,titulo Titulo
            ,custom1 Link
            ,ordem Ordem
            ,st_ativo Status
        FROM
            cmsitem
        WHERE 1=1
        AND tipo = 'frasehome'
        ORDER BY
            ordem, titulo
            ";

        $filtro = new filtro();
        $filtro->botao_novo = false;
        $grid->metodo = 'frasehome' ;
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

        $frasehome = new frasehome(intval(request('id')));

        if(substr(request('action'),0,6)=='salvar'){

            $erros = array();

            $next = substr(request('action'),7,strlen(request('action')));
            $frasehome->set_by_array(@$_REQUEST['frasehome']);
            $frasehome->ordem = intval($frasehome->ordem);

            if(!$frasehome->id){
                $frasehome->data_publicacao = date('Y-m-d H:i:s');
            }

            try {

                if(!$frasehome->titulo){
                    throw new Exception('Digite o titulo');
                }

                if(!$frasehome->salva()) {
                    throw new Exception('Falha ao salvar os dados');
                }

                if(request('excluirimg1')){
                    $frasehome->excluiImagem('img1');
                }

                if(request('excluirimg2')){
                    $frasehome->excluiImagem('img2');
                }

                if(request('excluirimg3')){
                    $frasehome->excluiImagem('img3');
                }

                $_SESSION['sucesso'] = 'Dados salvos com sucesso';

            }
            catch (Exception $ex){
                $_SESSION['erro'] = $ex->getMessage();
            }

            if(trim(@$next)!=''){
                $this->afterSave($next,'frasehome');
                return;
            }
        }

        if(request('action')=='sair'){
            $this->afterSave('sair','frasehome',$frasehome);
        }

        $t->parseBlock('BLOCK_TOOLBAR');

        $edicao = '';

        $edicao .= inputHidden('id', $frasehome->id);
        $edicao .= inputHidden('frasehome[id]', $frasehome->id);
        $edicao .= inputHidden('frasehome[autor_id]', $frasehome->autor_id ? $frasehome->autor_id : butil::decode($_SESSION['CADASTRO']->id));
        $edicao .= inputHidden('frasehome[tipo]', 'frasehome');

        $edicao .= tag('div class="well"',
            tag('h2', 'Dados básicos')
            .select('frasehome[st_ativo]', $frasehome->st_ativo, 'Ativo?:', array('S'=>'Sim','N'=>'Nao'))
            .inputSimples('frasehome[titulo]', $frasehome->titulo, 'Titulo:', 45, 50)
            .inputSimples('frasehome[custom1]', $frasehome->custom1, 'Link:', 200, 200)
            .inputSimples('frasehome[ordem]', $frasehome->ordem, 'Ordem de aparição:', 10, 10)
            .textArea('frasehome[custom2]', $frasehome->custom2, '3 Linhas de texto:')
        );

        $edicao .= $this->add_imagem($frasehome,'img1',200,200);

        $edicao .= tag('br clear="all"');

        $t->edicao = $edicao;

        // $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }

    static function widget(){

        $t = new Template(__DIR__.'/tpl.frasehome.html') ;
        $t->path = PATH_SITE;

        $opts = array('limit'=>3,'order_by'=>'ordem');
        $itens = get_cmsitem('frasehome',$opts);

        $i = 0;
        foreach($itens as $item){
            $t->frasehome = $item;
            $t->parseBlock('BLOCK_FRASEHOME');
            $i ++;
        }

        return $t->getContent();

    }

}