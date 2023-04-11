<?php

class modulo_itemclasse extends modulo_admin {

    public $arquivo = 'itemclasse';

    const ATIVO = false;

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
            $itemclasse = new itemclasse(intval(request('id')));
            $itemclasse->exclui();
        }

        $grid = new grid();

        $sql =
            "
        SELECT
            itemclasse.id
            ,itemclasse.nome Nome
            ,itemclasse.ordem Ordem
        FROM
            itemclasse
        WHERE
            1=1
        ORDER BY
            itemclasse.ordem
            ,itemclasse.nome
        ";

        $grid->sql = $sql;

        $filtro = new filtro();

        $grid->metodo = 'itemclasse' ;
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

        $itemclasse = new itemclasse(intval(request('id')));
        $itemclasse->set_by_array(@$_REQUEST['itemclasse']);

        if(substr(request('action'),0,6)=='salvar'){

            $erros = array();

            $next = substr(request('action'),7,strlen(request('action')));

            $itemclasse->ordem = intval($itemclasse->ordem);

            if(!$itemclasse->nome){
                throw new Exception('Digite o nome da itemclasse');
            }

            if(!$itemclasse->salva()) {
                throw new Exception('Falha ao salvar os dados');
            }

            $_SESSION['sucesso'] = 'Dados salvos com sucesso';

            if(trim(@$next)!=''){
                $this->afterSave($next,'itemclasse');
                return;
            }
        }

        if(request('action')=='sair'){
            $this->afterSave('sair','itemclasse',$itemclasse);
        }

        $t->parseBlock('BLOCK_TOOLBAR');

        $edicao = '';

        $edicao .= inputHidden('id', $itemclasse->id);
        $edicao .= inputHidden('itemclasse[id]', $itemclasse->id);

        $edicao .= tag('div class="box-block" xstyle="width:49%;float:left;height:260px"',
            tag('h2', 'Dados básicos')
            .inputSimples('itemclasse[nome]', $itemclasse->nome, 'Nome:', 45, 50)
            .inputSimples('itemclasse[ordem]', $itemclasse->ordem, 'Ordem de aparição:', 10, 10)
        );

        $edicao .= tag('br clear="all"');

        $t->edicao = $edicao;

        // $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }
}
