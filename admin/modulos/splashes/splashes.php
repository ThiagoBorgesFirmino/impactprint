<?php
class modulo_splashes extends modulo_admin{

    public $arquivo = "splashes";

    public function pesquisa(){

		$t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
        $t->h1 = h1($this->modulo->nome);

        if(request("action")=="editar" || request("action")=="sair" || request("action")=="salvar" || substr(request('action'),0,6)=='salvar'){
            $this->editar();
            return;
        }

        if(request('action')=='excluir'){
            $splash = new splash(intval(request('id')));
            if($splash->id) {
                if($splash->exclui()) $_SESSION["sucesso"] = "Splash excluído com sucesso.";
            }
        }        

        $edicao = "";
        $grid = new grid();
        $filtro = new filtro();

        $sql = "SELECT 
            id, 
            nome Nome, 
            CONCAT('<img src=\"','".PATH_SITE."','img/splash/',imagem,'?x=".time()."','\" alt=\"\" />') Imagem,
            data_cadastro Data_Cadastro,
            st_ativo Status
            FROM splash ORDER BY nome";

        $grid->sql = $sql;
        $grid->metodo = $this->arquivo;

        $edicao .= $grid->render();

        $t->edicao = $edicao;
        
        $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }
    
    public function editar(){
        
        $t = new TemplateAdminPop('admin/tpl.admin-cadastro-generico.html');

        $t->h1 = h1($this->modulo->nome);

        $splash = new splash(intval(request('id')));


        if(substr(request('action'),0,6)=='salvar'){
            $erro = array();
            $next = substr(request('action'),7,strlen(request('action')));

            $splash->set_by_array(request("splash"));
            if(isset($_FILES["file_"])) $splash->setFile($_FILES["file_"]);
            if($splash->validaDados($erro)){
                $splash->salva();
                $_SESSION["sucesso"] = "Dados salvos com sucesso.";
            }

            if(sizeof($erro)>0) $_SESSION['erro'] = join('<br />', $erro);
            else{
                if(trim(@$next)!=''){
                    $this->afterSave($next,$this->arquivo);
                    return;
                }
            }
        }

        if(request('action')=='sair') $this->afterSave('sair',$this->arquivo,$splash);

        $t->parseBlock('BLOCK_TOOLBAR');

        $edicao = "";

        $edicao .= tag('div class="alert alert-info"',
            tag('p','Formato ideal para imagens:')
            .tag('p','.png com '.splash::WIDTH.'x'.splash::HEIGHT.'px no tamanho')
        );

        $edicao .= tag("table class='table'",
            tag('tr',
                tag('td', select("splash[st_ativo]", $splash->st_ativo, 'Status', array('S'=>'Sim','N'=>'Nao')))
                .tag('td', inputSimples("splash[nome]",$splash->nome,'Nome',20,60))
            )
            .tag("tr",
                tag('td', inputFile("file_",'','Imagem'))
                .tag("td", ($splash->imagem!=''?tag('img src="'.PATH_SITE.'img/splash/'.$splash->imagem.'?x='.time().'"') :'') )
            )
        );
            
        $t->edicao = $edicao;        
        $this->adm_instance->show($t);
    }
}