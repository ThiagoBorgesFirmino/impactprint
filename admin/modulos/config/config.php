<?php

class modulo_config extends modulo_admin {

    public $arquivo = 'config';

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
            $config = new config(intval(request('id')));
            $config->exclui();
        }

        $sql =
        "
        SELECT
            distinct grupo
        FROM
            config
        WHERE 1=1
        ORDER BY 1
        ";

        $edicao = '';
        $edicao = '<div class="well"><div class="list-group">';
        foreach(results($sql) as $item){
            $edicao .= "<a href='".PATH_SITE."admin.php/config/listar/?grupo={$item->grupo}' class='list-group-item'><span class='glyphicon glyphicon-chevron-right icon-chevron-right'></span> $item->grupo</a>";
        }
        $edicao .= '</div></div>';

        $t->edicao = $edicao;

        $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }

    public function listar(){

        $t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
        $t->h1 = h1($this->modulo->nome.' - '.request('grupo'));

        if(request('action')=='editar'
            ||substr(request('action'),0,6)=='salvar'){
            $this->editar($t);
            return;
        }

        if(request('action')=='excluir'){
            $config = new config(intval(request('id')));
            $config->exclui();
        }

        $sql =
            "
        SELECT
            id
            ,CASE WHEN obs <> '' THEN obs ELSE chave END descricao
            ,CASE WHEN chave LIKE 'JS%' THEN 'script' ELSE valor END descricao2
        FROM
            config
        WHERE 1=1
        AND grupo = '".request('grupo')."'
        ";

        $grid = new grid();
        $grid->metodo = $this->arquivo ;
        $grid->sql = $sql;

        $t->edicao = $grid->render();

        $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);

    }

    public function editar($t){

        $t = new TemplateAdminPop('admin/tpl.admin-cadastro-generico.html');
        $t->h1 = h1($this->modulo->nome);

        $modulo = $this->modulo;

        $config = new config(intval(request('id')));

        if(substr(request('action'),0,6)=='salvar'){

            $next = substr(request('action'),7,strlen(request('action')));
            $config->set_by_array(@$_REQUEST['config']);
            $config->valor = mysql_real_escape_string($_REQUEST['config']['valor']);

            try {

                $config->validaDados();
                $config->salva();

                $config = new config($config->id);

                $_SESSION['sucesso'] = 'Dados salvos com sucesso';

                if(request('action')=='salvar_sair'){
                    if($config->grupo!=''){
                        $this->setLocation("{$this->arquivo}/listar/?grupo={$config->grupo}");
                    }
                }
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

        $t->h1 = h1("{$modulo->nome}");

        $edicao = '';
        $edicao .= inputHidden('config[id]', $config->id);

        $edicao .= '<div class="box-block">';
        $edicao .= inputSimples('config[grupo]', $config->grupo, 'Grupo:', 60);
        $edicao .= inputSimples('config[chave]', $config->chave, 'Chave:', 60);
        $edicao .= textArea('config[valor]', $config->valor, 'Valor:', 35, 4);
        $edicao .= inputSimples('config[obs]', $config->obs, 'Observações:', 60);
        // $edicao .= select('config[target]', $config->target, 'Destino:', array('_self'=>'_self','_blank'=>'_blank'));
        $edicao .= '</div>';

        $edicao .= str_repeat(tag('br'),5);

        $t->edicao = $edicao;

        // $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }

    static function widget(){

        $banners = bsite::getBannersPrincipais();

        $t = new Template('admin/modulos/config/tpl.config.html') ;
        // $t->path = PATH_SITE;

        $i = 1;
        foreach($banners as $banner){

            $t->banner = $banner;
            $t->active = $i==1?'active':'';

            if($banner->chamada!=''){
                $t->parseBlock('BLOCK_BANNERPRINCIPAL_TEXTO', true);
            }

            $t->i = $i ++;
            $t->parseBlock('BLOCK_BANNERPRINCIPAL', true);
            $t->parseBlock('BLOCK_BANNERPRINCIPAL_THUMB', true);
        }		// $banners = bsite::getBannersSelos();

        return $t->getContent();

    }

}
