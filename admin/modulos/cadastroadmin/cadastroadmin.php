<?php

class modulo_cadastroadmin extends modulo_admin {

    public $arquivo = 'cadastroadmin';

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
            $cadastroadmin = new cadastroadmin(intval(request('id')));
            if($cadastroadmin->id){
                $cadastroadmin->exclui();
            }
        }

        $grid = new grid();

        $sql =
        "
        SELECT
            cadastro.id
            ,cadastro.nome Nome
            ,cadastro.email Email
            ,cadastro.skype Skype
            -- ,cadastro.empresa Empresa
            ,cadastro.fone_res Fone_comercial
            ,cadastro.fone_cel Fone_celular
            ,cadastro.data_cadastro Data_cadastro
            ,cadastro.st_ativo Status
        FROM
            cadastro
        WHERE
            1=1
        ".($_SESSION['CADASTRO']->email!='dev@ajung.com.br'? " AND email <> 'dev@ajung.com.br' ": "" )."
        AND	tipocadastro_id = ".cadastro::TIPOCADASTRO_ADMINISTRADOR."
        ORDER BY id desc";

        $grid->sql = $sql;

        $filtro = new filtro();

        $filtro->add_input('Nome','Nome');
        $filtro->add_input('Email','Email');
        $filtro->add_periodo('Data_cadastro','Data cadastro');

        $filtro->excel = $this->adm_instance->boxExpExcel($sql,$this->arquivo,$filtro);

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

        $cadastroadmin = new cadastroadmin(intval(request('id')));

        if(substr(request('action'),0,6)=='salvar'){

            $erros = array();

            $next = substr(request('action'),7,strlen(request('action')));

            $cadastroadmin->set_by_array(@$_REQUEST['cadastro']);
            $cadastroadmin->cnpj = preg_replace('/\D/', '',$cadastroadmin->cnpj);
            $cadastroadmin->cpf = preg_replace('/\D/', '',$cadastroadmin->cpf);

            if(!$cadastroadmin->id){
                $cadastroadmin->data_cadastro = date('Y-m-d H:i:s');
            }

            try {

                if(request('senha_nova')||!$cadastroadmin->id){
                    $cadastroadmin->senha = encode(request('senha_nova'));
                }

                if(!$cadastroadmin->validaCadastro($erro)){
                    $cadastroadmin->senha = encode( $cadastroadmin->senha);
                    throw new Exception(join('<br>', $erro));
                }

                if(!$cadastroadmin->salva()) {
                    $cadastroadmin->senha = decode(request('senha_nova'));
                    throw new Exception('Falha ao salvar os dados');
                }

                // Salva permissoes
                $modulo_ids = request('modulo_id');
                $atualizados = array(0);
                foreach(is_array($modulo_ids) ? $modulo_ids : array() as $modulo_id){
                    $permissao = new permissao();
                    $permissao->get_by_id(array('cadastro_id'=>$cadastroadmin->id,'modulo_id'=>$modulo_id));
                    if(!$permissao->id){
                        $permissao->modulo_id = $modulo_id;
                        $permissao->cadastro_id = $cadastroadmin->id;
                        $permissao->salva();
                    }
                    $atualizados[] = $permissao->id;
                    unset($permissao);
                }
                query('DELETE FROM permissao WHERE cadastro_id = '.$cadastroadmin->id.' AND id NOT IN ('.join(',',$atualizados).')');

                $_SESSION['sucesso'] = 'Dados salvos com sucesso';

                if(trim(@$next)!=''){
                    $this->afterSave($next,$this->arquivo);
                    return;
                }
            }
            catch (Exception $ex){
                $_SESSION['erro'] = $ex->getMessage();
            }
        }

        if(request('action')=='sair'){
            $this->afterSave('sair',$this->arquivo,$cadastroadmin);
        }

        //$t->parseBlock('BLOCK_TOOLBAR');
        // if($_SESSION["CADASTRO"]->email=="dev@ajung.com.br")$t->parseBlock('BLOCK_TOOLBAR');
        $t->parseBlock('BLOCK_TOOLBAR');

        $edicao = '';

        $edicao .= inputHidden('id', $cadastroadmin->id);
        $edicao .= inputHidden('cadastro[id]', $cadastroadmin->id);

        $edicao .= tag('div class="well"',
            tag('legend', 'Dados básicos')
            .select('cadastro[st_ativo]', $cadastroadmin->st_ativo, 'Ativo?:', array('S'=>'Sim','N'=>'Nao'))
            .inputSimples('cadastro[nome]', $cadastroadmin->nome, 'Nome:')
        );

        $edicao .= $this->adm_instance->boxContato($cadastroadmin);
        $edicao .= $this->adm_instance->boxDocsPessoais($cadastroadmin);

        $edicao .= tag('div class="well"',
            tag('legend', 'Dados de login')
            .$this->adm_instance->boxSenha($cadastroadmin)
            .tag('br clear="all"')
        );

        $edicao .= tag('div class="well"',
            tag('legend', 'Permissões de acesso ao sistema administrativo')
            .$this->adm_instance->permissoes(0, intval($cadastroadmin->id))
            .tag('br clear="all"')
        );

        $edicao .= tag('br clear="all"');

        $t->edicao = $edicao;

        $opts = array(

        );

        $opts = array(
            'include_js' => PATH_SITE."admin/modulos/{$this->arquivo}/cadastroadminadmin.js"
        );

        // $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t, $opts);
    }

    static function widget(){

        $t = new Template('admin/modulos/cadastroadmin/tpl.cadastroadmin.html') ;
        $t->path = PATH_SITE;

        $opts = array('limit'=>5,'order_by'=>'ordem');
        $itens = get_cmsitem('cadastroadmin',$opts);

        $i = 0;
        foreach($itens as $item){
            $t->cadastroadmin = $item;
            $t->parseBlock('BLOCK_FRASEHOME');
            $i ++;
        }

        return $t->getContent();

    }

}