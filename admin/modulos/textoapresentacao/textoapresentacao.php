<?php

class modulo_textoapresentacao extends modulo_admin {

    public $arquivo = 'textoapresentacao';

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
            ,titulo Texto
            ,ordem Ordem
            ,st_ativo Status
        FROM
            cmsitem
        WHERE 1=1
        AND tipo = 'textoapresentacao'
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

        $textoapresentacao = new textoapresentacao(intval(request('id')));

        if(substr(request('action'),0,6)=='salvar'){

            $erros = array();

            $next = substr(request('action'),7,strlen(request('action')));
            $textoapresentacao->set_by_array(@$_REQUEST['textoapresentacao']);
            $textoapresentacao->ordem = intval($textoapresentacao->ordem);

            if(!$textoapresentacao->id){
                $textoapresentacao->data_publicacao = date('Y-m-d H:i:s');
            }

            try {

                if(!$textoapresentacao->titulo){
                    throw new Exception('Digite o titulo');
                }

                if(!$textoapresentacao->salva()) {
                    throw new Exception('Falha ao salvar os dados');
                }

                if(request('excluirimg1')){
                    $textoapresentacao->excluiImagem('img1');
                }

                if(request('excluirimg2')){
                    $textoapresentacao->excluiImagem('img2');
                }

                if(request('excluirimg3')){
                    $textoapresentacao->excluiImagem('img3');
                }

                $_SESSION['sucesso'] = 'Dados salvos com sucesso';

            }
            catch (Exception $ex){
                $_SESSION['erro'] = $ex->getMessage();
            }

            if(trim(@$next)!=''){
                $this->afterSave($next,'textoapresentacao');
                return;
            }
        }

        if(request('action')=='sair'){
            $this->afterSave('sair','textoapresentacao',$textoapresentacao);
        }

        $t->parseBlock('BLOCK_TOOLBAR');

        $edicao = '';

        $edicao .= inputHidden('id', $textoapresentacao->id);
        $edicao .= inputHidden('textoapresentacao[id]', $textoapresentacao->id);
        $edicao .= inputHidden('textoapresentacao[autor_id]', $textoapresentacao->autor_id ? $textoapresentacao->autor_id : butil::decode($_SESSION['CADASTRO']->id));
        $edicao .= inputHidden('textoapresentacao[tipo]', 'textoapresentacao');

        $edicao .= tag('div class="well"',
            tag('h2', 'Dados básicos')
            .select('textoapresentacao[st_ativo]', $textoapresentacao->st_ativo, 'Ativo?:', array('S'=>'Sim','N'=>'Nao'))
            .inputSimples('textoapresentacao[titulo]', $textoapresentacao->titulo, 'Título:', 45, 50)
            // .inputSimples('textoapresentacao[custom1]', $textoapresentacao->custom1, 'Link:', 200, 200)
            .inputSimples('textoapresentacao[ordem]', $textoapresentacao->ordem, 'Ordem de aparição:', 10, 10)
            .textArea('textoapresentacao[custom2]', $textoapresentacao->custom2, 'Linhas de texto:')
        );

        $edicao .= $this->add_imagem($textoapresentacao,'img1',300,100);

        $edicao .= tag('br clear="all"');

        $t->edicao = $edicao;

        // $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }

    static function widget(){

        $t = new Template(__DIR__.'/tpl.textoapresentacao.html') ;
        $t->path = PATH_SITE;

        $opts = array('limit'=>1,'order_by'=>'ordem');
        $itens = get_cmsitem('textoapresentacao',$opts);

        $i = 0;
        foreach($itens as $item){
            $t->textoapresentacao = $item;
            $t->parseBlock('BLOCK_TEXTOAPRESENTACAO');
            $i ++;
        }

        return $t->getContent();

    }

}