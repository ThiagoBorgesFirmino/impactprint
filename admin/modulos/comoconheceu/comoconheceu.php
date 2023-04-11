<?php

class modulo_comoconheceu extends modulo_admin {

    public $arquivo = 'comoconheceu';

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
            $comoconheceu = new comoconheceu(intval(request('id')));
            if($comoconheceu->id){
                $comoconheceu->exclui();
            }
        }

        $grid = new grid();

        $grid->sql = $sql =
        "
        SELECT
            id
            ,nome Nome
        FROM
            comoconheceu
        WHERE 1=1
        ORDER BY
            nome
        ";

        $filtro = new filtro();

        $grid->metodo = 'comoconheceu' ;
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

        $comoconheceu = new comoconheceu(intval(request('id')));

        if(substr(request('action'),0,6)=='salvar'){

            $erros = array();

            $next = substr(request('action'),7,strlen(request('action')));
            $comoconheceu->set_by_array(@$_REQUEST['comoconheceu']);

            if(!$comoconheceu->id){
                $comoconheceu->data_cadastro = date('Y-m-d H:i:s');
            }

            try {

                if(!$comoconheceu->nome){
                    throw new Exception('Digite o nome');
                }

                if(!$comoconheceu->salva()) {
                    throw new Exception('Falha ao salvar os dados');
                }

                $_SESSION['sucesso'] = 'Dados salvos com sucesso';

            }
            catch (Exception $ex){
                $_SESSION['erro'] = $ex->getMessage();
            }

            if(trim(@$next)!=''){
                $this->afterSave($next,$this->arquivo);
                return;
            }
        }

        if(request('action')=='sair'){
            $this->afterSave('sair',$this->arquivo,$comoconheceu);
        }

        $t->parseBlock('BLOCK_TOOLBAR');

        $edicao = '';

        $edicao .= inputHidden('id', $comoconheceu->id);
        $edicao .= inputHidden('comoconheceu[id]', $comoconheceu->id);

        $edicao .= tag('div class="well"',
            tag('h2', 'Dados básicos')
            .inputSimples('comoconheceu[nome]', $comoconheceu->nome, 'Nome:')
        );

        $edicao .= tag('br clear="all"');

        $t->edicao = $edicao;

        // $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }

}