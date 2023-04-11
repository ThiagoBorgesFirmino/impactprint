<?php

class modulo_mesa extends modulo_admin {

    public $arquivo = 'mesa';

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
            -- ,concat('<img src=".PATH_SITE."upload/', img1, ' width=118px />') Imagem
            ,titulo Nome
            ,custom1 Local
            ,concat('Mesa ',ordem) Ordem
            ,st_ativo Status
        FROM
            cmsitem
        WHERE 1=1
        AND tipo = 'mesa'
        ORDER BY
            custom1, ordem
        ";

        $filtro = new filtro();

        $grid->metodo = 'mesa' ;
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

        $mesa = new mesa(intval(request('id')));

        if(substr(request('action'),0,6)=='salvar'){

            $erros = array();

            $next = substr(request('action'),7,strlen(request('action')));
            $mesa->set_by_array(@$_REQUEST['mesa']);

            $tmp = array($mesa->custom2,$mesa->custom3);

            if($mesa->custom4 && is_email($mesa->custom4)){
                $tmp[] = "<a href=mailto:{$mesa->custom4}>{$mesa->custom4}</a>";
            }

            if($mesa->custom5 != ''){
                $tmp[] = "Skype: {$mesa->custom5}";
            }

            $mesa->titulo = join('<br>', $tmp);
            $mesa->ordem = intval($mesa->ordem);

            // if(!$mesa->id){
                $mesa->data_publicacao = date('Y-m-d');
            // }

            try {

                if(!$mesa->custom2){
                    throw new Exception('Digite o contato da pessoa 1');
                }

                if(!$mesa->custom3){
                    throw new Exception('Digite o contato da pessoa 2');
                }

                if(!$mesa->salva()) {
                    throw new Exception('Falha ao salvar os dados');
                }

                if(request('excluirimg1')){
                    $mesa->excluiImagem('img1');
                }

                if(request('excluirimg2')){
                    $mesa->excluiImagem('img2');
                }

                if(request('excluirimg3')){
                    $mesa->excluiImagem('img3');
                }

                $_SESSION['sucesso'] = 'Dados salvos com sucesso';

            }
            catch (Exception $ex){
                $_SESSION['erro'] = $ex->getMessage();
            }

            if(trim(@$next)!=''){
                $this->afterSave($next,'mesa');
                return;
            }
        }

        if(request('action')=='sair'){
            $this->afterSave('sair','mesa',$mesa);
        }

        $t->parseBlock('BLOCK_TOOLBAR');

        $edicao = '';

        $edicao .= inputHidden('id', $mesa->id);
        $edicao .= inputHidden('mesa[id]', $mesa->id);
        $edicao .= inputHidden('mesa[autor_id]', $mesa->autor_id ? $mesa->autor_id : butil::decode($_SESSION['CADASTRO']->id));
        $edicao .= inputHidden('mesa[tipo]', 'mesa');

        $edicao .= tag('div class="box-block" xstyle="width:49%;float:left;height:260px"',
            tag('h2', 'Mesa / Localização')
            .inputSimples('mesa[ordem]', $mesa->ordem, 'Mesa (Ordem de aparição):', 10, 10)
            .select('mesa[custom1]', $mesa->custom1, 'Localização:', array('SPDENTRO'=>'Dentro de SP','SPFORA'=>'Fora de SP'))
        );

        $edicao .= tag('div class="box-block" xstyle="width:49%;float:left;height:260px"',
            tag('h2', 'Pessoa 1')
            .inputSimples('mesa[custom2]', $mesa->custom2, 'Nome / Cargo:', 200, 200)
        );

        $edicao .= $this->add_imagem($mesa,'img1',118,118);

        $edicao .= tag('div class="box-block" xstyle="width:49%;float:left;height:260px"',
            tag('h2', 'Pessoa 2')
            .inputSimples('mesa[custom3]', $mesa->custom3, 'Nome / Cargo:', 200, 200)
        );

        $edicao .= $this->add_imagem($mesa,'img2',118,118);

        $edicao .= tag('div class="box-block" xstyle="width:49%;float:left;height:260px"',
            tag('h2', 'Contato')
            .inputSimples('mesa[custom4]', $mesa->custom4, 'E-mail:', 60, 200)
            .inputSimples('mesa[custom5]', $mesa->custom5, 'Skype:', 200, 200)
        );

        $edicao .= tag('br clear="all"');

        $t->edicao = $edicao;

        // $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }

    static function widget(&$tplpai){

        $opts = array('order_by'=>'ordem');

        $tmp = get_cmsitem('mesa',$opts);
        $itens = array();

        foreach($tmp as $item){
            if(!isset($itens[$item->custom1])){
                $itens[$item->custom1] = array();
            }
            $itens[$item->custom1][] = $item;
        }

        if(isset($itens['SPDENTRO'])){
            $t = new Template('admin/modulos/mesa/tpl.mesa.html') ;
            $t->path = PATH_SITE;
            foreach($itens['SPDENTRO'] as $item){
                $t->mesa = $item;
                if($item->custom2 != ''){
                    list($nome) = explode(' ',$item->custom2);
                    $t->nome = $nome;
                    $t->parseBlock('BLOCK_MESA_PESSOA1');
                }
                if($item->custom3 != ''){
                    list($nome) = explode(' ',$item->custom3);
                    $t->nome = $nome;
                    $t->parseBlock('BLOCK_MESA_PESSOA2');
                }
                $t->parseBlock('BLOCK_MESA');
            }
            $tplpai->spdentro = $t->getContent();
        }

        if(isset($itens['SPFORA'])){
            $t = new Template('admin/modulos/mesa/tpl.mesa.html') ;
            $t->path = PATH_SITE;
            foreach($itens['SPFORA'] as $item){
                $t->mesa = $item;
                if($item->custom2 != ''){
                    list($nome) = explode(' ',$item->custom2);
                    $t->nome = $nome;
                    $t->parseBlock('BLOCK_MESA_PESSOA1');
                }
                if($item->custom3 != ''){
                    list($nome) = explode(' ',$item->custom3);
                    $t->nome = $nome;
                    $t->parseBlock('BLOCK_MESA_PESSOA2');
                }
                $t->parseBlock('BLOCK_MESA');
            }
            $tplpai->spfora = $t->getContent();
        }

    }

}
