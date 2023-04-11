<?php

class modulo_cadastrocliente extends modulo_admin {

    public $arquivo = 'cadastrocliente';

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
            $cadastrocliente = new cadastrocliente(intval(request('id')));
            if($cadastrocliente->id){
                $cadastrocliente->exclui();
            }
        }

        $grid = new grid();

        $sql =
            "
        SELECT
            cadastro.id
            ,cadastro.nome Nome
            ,cadastro.email Email
            ,cadastro.empresa Empresa
            ,cadastro.fone_res Fone_comercial
            ,comoconheceu.nome Ramo_atuacao
            -- ,cadastro.fone_cel Fone_celular
            ,cadastro.data_cadastro Data_cadastro
            ,cadastro.st_ativo Status
        FROM
            cadastro
        LEFT OUTER JOIN comoconheceu ON (
            cadastro.comoconheceu_id = comoconheceu.id
        )
        WHERE
            1=1
        AND	(tipocadastro_id = ".cadastro::TIPOCADASTRO_CLIENTE.")"
        .($_SESSION['CADASTRO']->tipocadastro_id == cadastro::TIPOCADASTRO_VENDEDOR
        ?" AND (cadastro_id = ".decode($_SESSION['CADASTRO']->id)." OR cadastro.id IN (SELECT cadastro_id FROM pedido WHERE vendedor_id = ".decode($_SESSION['CADASTRO']->id).")) "
        :""	)."ORDER BY id DESC";

        $grid->sql = $sql;

        $filtro = new filtro();

        $filtro->add_input('Nome','Nome');
        $filtro->add_input('Email','Email');
        $filtro->add_input('Empresa','Empresa');
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
        
        if(request('pop')){
            $t = new TemplateAdminPop('admin/tpl.admin-cadastro-generico.html');
        }

        $t->h1 = h1($this->modulo->nome);

        $cadastrocliente = new cadastrocliente(intval(request('id')));

        if(substr(request('action'),0,6)=='salvar'){

            $erros = array();

            $next = substr(request('action'),7,strlen(request('action')));

            $cadastrocliente->set_by_array(@$_REQUEST['cadastro']);
            $cadastrocliente->cnpj = preg_replace('/\D/', '',$cadastrocliente->cnpj);
            $cadastrocliente->cpf = preg_replace('/\D/', '',$cadastrocliente->cpf);
            
            if(!$cadastrocliente->id) $cadastrocliente->data_cadastro = date('Y-m-d H:i:s');
            
            try {
                
                if(request('senha_nova')||!$cadastrocliente->id) $cadastrocliente->senha = encode(request('senha_nova'));
                
                if(!$cadastrocliente->validaCadastro($erros,'',true)){
                    $cadastrocliente->senha = decode($cadastrocliente->senha);
                    throw new Exception(join('<br>', $erros));
                }
                
                if(!$cadastrocliente->salva()) throw new Exception('Falha ao salvar os dados');
                
                $_SESSION['sucesso'] = 'Dados salvos com sucesso';
            
            }catch (Exception $e){
              
                $_SESSION['erro'] = $e->getMessage();
            }

            if(trim(@$next)!=''){
                $this->afterSave($next,$this->arquivo);
                return;
            }
        }

        if(request('action')=='sair'){
            $this->afterSave('sair',$this->arquivo,$cadastrocliente);
        }

        $t->parseBlock('BLOCK_TOOLBAR');

        $edicao = '';

        $edicao .= inputHidden('id', $cadastrocliente->id);
        $edicao .= inputHidden('cadastro[id]', $cadastrocliente->id);

        $edicao .= tag('div class="well"',
            tag('legend', 'Dados básicos')
            .select('cadastro[st_ativo]', $cadastrocliente->st_ativo, 'Ativo?:', array('S'=>'Sim','N'=>'Nao'))
            .inputSimples('cadastro[nome]', $cadastrocliente->nome, 'Nome: <span style="color:red;">*</span>')
            .inputSimples('cadastro[empresa]', $cadastrocliente->empresa, 'Empresa:')
            .inputSimples('cadastro[cnpj]', $cadastrocliente->cnpj, 'CNPJ:')
            .inputSimples('cadastro[inscricao_estadual]', $cadastrocliente->inscricao_estadual, 'IE:') 
            .$this->adm_instance->boxVendedor($cadastrocliente)
            .select('cadastro[comoconheceu_id]', $cadastrocliente->comoconheceu_id, 'Ramo de atuação:', comoconheceu::opcoes())
            .inputSimples('cadastro[site]', $cadastrocliente->site, 'Site:', 60, 100)
        );
        
        $edicao .= tag('div class="well"',
            tag('legend','Endereço')
            //.$this->adm_instance->boxEndereco($cadastrocliente)
            .inputSimples('cadastro[cep]', $cadastrocliente->cep, 'CEP:', 10, 10)
            .inputSimples('cadastro[logradouro]', $cadastrocliente->logradouro, 'Logradouro:', 45, 50)
            .inputSimples('cadastro[numero]', $cadastrocliente->numero, 'Numero:', 15, 15)
            
            .inputSimples('cadastro[complemento]', $cadastrocliente->complemento, 'Complemento:', 30, 30)
            .inputSimples('cadastro[bairro]', $cadastrocliente->bairro, 'Bairro:', 30, 30)
            .inputSimples('cadastro[cidade]', $cadastrocliente->cidade, 'Cidade:', 30, 30)
            .inputSimples('cadastro[uf]', $cadastrocliente->uf, 'Estado:', 2, 2)
            .tag('br clear="all"')
        );

        $edicao .=$this->adm_instance->boxContato($cadastrocliente);

        $edicao .= tag('br clear="all"');

        $t->edicao = $edicao;
       
        $opts = array('include_js' => PATH_SITE."admin/modulos/{$this->arquivo}/cadastroclienteadmin.js");

       
        $this->adm_instance->show($t, $opts);
    }

    static function widget(){

        $t = new Template('admin/modulos/cadastrocliente/tpl.cadastrocliente.html') ;
        $t->path = PATH_SITE;

        $opts = array('limit'=>5,'order_by'=>'ordem');
        $itens = get_cmsitem('cadastrocliente',$opts);

        $i = 0;
        foreach($itens as $item){
            $t->cadastrocliente = $item;
            $t->parseBlock('BLOCK_FRASEHOME');
            $i ++;
        }

        return $t->getContent();

    }

}