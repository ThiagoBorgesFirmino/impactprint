<?php

class modulo_cadastrovendedor extends modulo_admin {

    public $arquivo = 'cadastrovendedor';

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
            $cadastrovendedor = new cadastrovendedor(intval(request('id')));
            if($cadastrovendedor->id){
                $cadastrovendedor->exclui();
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
			,cadastro.st_fixo fixo
        FROM
            cadastro
        WHERE
            1=1
        AND	tipocadastro_id = ".cadastro::TIPOCADASTRO_VENDEDOR."
        ORDER BY id DESC";

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

        $cadastrovendedor = new cadastrovendedor(intval(request('id')));

        if(substr(request('action'),0,6)=='salvar'){

            $erros = array();

            $next = substr(request('action'),7,strlen(request('action')));

            $cadastrovendedor->set_by_array(@$_REQUEST['cadastro']);
            $cadastrovendedor->cnpj = preg_replace('/\D/', '',$cadastrovendedor->cnpj);
            $cadastrovendedor->cpf = preg_replace('/\D/', '',$cadastrovendedor->cpf);

            if(!$cadastrovendedor->id){
                $cadastrovendedor->data_cadastro = date('Y-m-d H:i:s');
            }

            try {

                if(request('senha_nova')||!$cadastrovendedor->id){
                    $cadastrovendedor->senha = encode(request('senha_nova'));
                }

                if(!$cadastrovendedor->validaCadastro($erro)){
                    $cadastrovendedor->senha = decode($cadastrovendedor->senha);
                    throw new Exception(join('<br>', $erro));
                }

                if(!$cadastrovendedor->salva()) {
                    throw new Exception('Falha ao salvar os dados');
                }

                // Salva permissoes
                $modulo_ids = request('modulo_id');
                $atualizados = array(0);
                foreach(is_array($modulo_ids) ? $modulo_ids : array() as $modulo_id){
                    $permissao = new permissao();
                    $permissao->get_by_id(array('cadastro_id'=>$cadastrovendedor->id,'modulo_id'=>$modulo_id));
                    if(!$permissao->id){
                        $permissao->modulo_id = $modulo_id;
                        $permissao->cadastro_id = $cadastrovendedor->id;
                        $permissao->salva();
                    }
                    $atualizados[] = $permissao->id;
                    unset($permissao);
                }
                query('DELETE FROM permissao WHERE cadastro_id = '.$cadastrovendedor->id.' AND id NOT IN ('.join(',',$atualizados).')');

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
            $this->afterSave('sair',$this->arquivo,$cadastrovendedor);
        }

        
        $t->parseBlock('BLOCK_TOOLBAR');

        $edicao = '';

        $edicao .= inputHidden('id', $cadastrovendedor->id);
        $edicao .= inputHidden('cadastro[id]', $cadastrovendedor->id);

        $html_vendedor ='';
        
        if($_SESSION["CADASTRO"]->tipocadastro_id == cadastro::TIPOCADASTRO_VENDEDOR){
            $html_vendedor;
        }else{
            $html_vendedor = $this->defineVendedorPadrao($cadastrovendedor);
        }

        $edicao .= tag('div class="well"',
            tag('legend', 'Dados básicos')
            .$html_vendedor
            .select('cadastro[st_ativo]', $cadastrovendedor->st_ativo, 'Ativo?:', array('S'=>'Sim','N'=>'Nao'))
            .inputSimples('cadastro[nome]', $cadastrovendedor->nome, 'Nome:')
        );

        $edicao .= $this->adm_instance->boxContato($cadastrovendedor);
        $edicao .= $this->adm_instance->boxDocsPessoais($cadastrovendedor);

        $edicao .= tag('div class="well"',
        tag('legend','Endereço')
        .inputSimples('cadastro[cep]', $cadastrovendedor->cep, 'CEP:', 10, 10)
        .inputSimples('cadastro[logradouro]', $cadastrovendedor->logradouro, 'Logradouro:', 45, 50)
        .inputSimples('cadastro[numero]', $cadastrovendedor->numero, 'Numero:', 15, 15)
        
        .inputSimples('cadastro[complemento]', $cadastrovendedor->complemento, 'Complemento:', 30, 30)
        .inputSimples('cadastro[bairro]', $cadastrovendedor->bairro, 'Bairro:', 30, 30)
        .inputSimples('cadastro[cidade]', $cadastrovendedor->cidade, 'Cidade:', 30, 30)
        .inputSimples('cadastro[uf]', $cadastrovendedor->uf, 'Estado:', 2, 2)
        .tag('br clear="all"')
    );

        $edicao .= tag('div class="well"',
            tag('legend', 'Dados de login')
            .$this->adm_instance->boxSenha($cadastrovendedor)
            .tag('br clear="all"')
        );

        $edicao .= tag('div class="well"',
            tag('legend', 'Permissões de acesso ao sistema administrativo')
            .$this->adm_instance->permissoes(0, intval($cadastrovendedor->id))
            .tag('br clear="all"')
        );

        $edicao .= tag('br clear="all"');

        $t->edicao = $edicao;

        $opts = array(

        );

        $opts = array(
            'include_js' => PATH_SITE."admin/modulos/{$this->arquivo}/cadastrovendedoradmin.js"
        );

        // $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t, $opts);
    }

    static function widget(){

        $t = new Template('admin/modulos/cadastrovendedor/tpl.cadastrovendedor.html') ;
        $t->path = PATH_SITE;

        $opts = array('limit'=>5,'order_by'=>'ordem');
        $itens = get_cmsitem('cadastrovendedor',$opts);

        $i = 0;
        foreach($itens as $item){
            $t->cadastrovendedor = $item;
            $t->parseBlock('BLOCK_FRASEHOME');
            $i ++;
        }

        return $t->getContent();

    }
    public function defineVendedorPadrao($cadastro){
        $ret = "";
        if( $cadastro->st_fixo =="S" ){
            $ret .= tag("p style='background:#CCC;padding:5px;font-size:14px;font-weight:bold;'","Este é o Vendedor Padrão.");
        }else{
            $ret .= select('cadastro[st_fixo]', $cadastro->st_fixo, 'Vendedor Padrão:', array('N'=>'Nao','S'=>'Sim'));
        }
        return $ret;
    }

}