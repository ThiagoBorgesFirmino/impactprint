<?php

class modulo_categoriaespecial extends modulo_admin {

    public $arquivo = 'categoriaespecial';

    // Pesquisa
    public function pesquisa(){

        if(request('popup')){
            $t = new TemplateAdminPopup('admin/tpl.admin-cadastro-generico.html');
        }
        else {
            $t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
        }

        $t->h1 = h1($this->modulo->nome);

        if(request('action')=='categoriasDel'){
            try {

                $categoriaespecial = new categoriaespecial(intval(request('id')));

                if(sizeof($categoriaespecial->get_childs('itemcategoria'))>0){
                    throw new Exception('Existem itens associados a esta categoriaespecial, não é possível excluir');
                }

                $categoriaespecial->exclui();
                // query("UPDATE categoriaespecial SET ordem = (ordem-1) WHERE ordem > {$categoriaespecial->ordem} AND categoriaespecial_id = {$categoriaespecial->categoriaespecial_id} AND (especial is NULL OR especial='N') AND categoriaespecial.st_lista_menu = 'S' ");
                $_SESSION['sucesso'] = tag('p', 'Categoria '.$categoriaespecial->nome.' excluida com sucesso');
            }
            catch(Exception $ex){
                $_SESSION['erro'] = tag('p', $ex->getMessage());
            }
        }

        if(request('action')=='salvar'){

            foreach(is_array(@$_REQUEST['categoriaespecial'])?$_REQUEST['categoriaespecial']:array() as $id => $arrcategoriaespecial){

                $categoriaespecial = new categoriaespecial($id);
                $categoriaespecial->set_by_array($arrcategoriaespecial);

                $erro = array();
                if($categoriaespecial->validaDados($erro)){
                    $_SESSION['sucesso'] = 'Dados salvos com sucesso';
                    $categoriaespecial->salva();
                }
                else{
                    $_SESSION['erro'] = join('<br />', $erro);
                }
            }

            if(is_array(@$_REQUEST['categoriaespecialNova']) && $_REQUEST['categoriaespecialNova']['nome']!=''){

                $categoriaespecial = new categoriaespecial();
                $categoriaespecial->set_by_array($_REQUEST['categoriaespecialNova']);

                $erro = array();
                if($categoriaespecial->validaDados($erro)){

                    $categoriaespecial->ordem = query_col("SELECT ifnull(max(ordem)+1,1) ordem FROM categoria WHERE st_lista_menu = 'N'");
                    $categoriaespecial->salva();

                    $_SESSION['sucesso'] = 'Categoria '.$categoriaespecial->nome.' cadastrada com sucesso';

                }
                else{
                    $_SESSION['erro'] = join('<br />', $erro);
                }

                unset($categoriaespecial);
            }

            if(@$_FILES['src']['size']>0){

                $tam = getimagesize($_FILES['src']['tmp_name']);

                if($tam[0] != 895 && $tam[1] != 275){
                    $_SESSION['erro'] = 'A imagem da categoriaespecial deve ter 895x275 px';
                }
                else{
                    query('UPDATE slidebanner SET imagem = "'.$_FILES['src']['name'].'" WHERE tipo = "categoriaespecial"');
                    move_uploaded_file($_FILES['src']['tmp_name'], 'img/banner/'.$_FILES['src']['name']);
                }
            }
        }

        if(request('action')=='excluir'){
            $categoriaespecialespecial = new categoriaespecialespecial(intval(request('id')));
            $categoriaespecialespecial->exclui();
        }

        $edicao = '';
        $edicao .= inputHidden('id','');
        $edicao .= '<div class="well">';
        $edicao .= tag('legend', 'Cadastrar nova categoria');
        $edicao .= inputSimples('categoriaespecialNova[nome]', '', 'Nome da Categoria',40,30);
        $edicao .= '</div>';

        $edicao .= '<table class="table table-bordered table-striped table-hover">';
        $edicao .= tag('tr',
            tag('th width=70px', 'Id')
            .tag('th', 'Nome')
            .tag('th', 'Ativo?')
            .tag('th width=25px', 'Ordem')
            .tag('th width=25px', 'Excluir')
        );

        $ident = 0;
        $categoriaespecials = results($sql="SELECT categoriaespecial.* FROM categoria categoriaespecial WHERE categoriaespecial.st_lista_menu = 'N' ORDER BY ordem, nome");
        $edicao .= $this->categoriaespecialProcessa(0,$ident,$categoriaespecials);

        $edicao .= '</table><br />';

        $t->edicao = $edicao;

        $this->adm_instance->montaMenuSimples($t);
        $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }

    public function categoriaespecialProcessa($id=0, &$ident=0, &$categoriaespecials){

        /*
        // $results = results($sql="SELECT categoriaespecial.* FROM categoriaespecial WHERE categoriaespecial_id = {$id} AND categoriaespecial.st_lista_menu = 'S' ORDER BY ordem, nome");
        $results = array();
        foreach($categoriaespecials as $tmp){
            if(intval($tmp->categoria_id)==$id){
                $results[] = $tmp;
            }
        }

        $edicao = '';

        if(sizeof($results)>0){

            $edicao .= '<ul style="list-style-type:none">';
            for($i=0,$n=sizeof($results);$i<$n;$i++){

                $fetch = $results[$i];

                $edicao .= '<li class="js-categoriaespecial js-categoriaespecial-'.$fetch->categoria_id.'" style="'.(intval($fetch->categoria_id)=='0'?'':'display:none').'">';
                $edicao .= '<label class="checkbox js-categoriaespecial js-categoriaespecial-'.$fetch->categoria_id.'" style="font-weight:normal;'.(intval($fetch->categoria_id)=='0'?'':'display:none').'">'.str_repeat('&nbsp;',0).' '.$fetch->nome.'</label>';

                $ident ++ ;
                $edicao .= $this->categoriaespecialProcessa($fetch->id, $ident, $categoriaespecials);
                $ident -- ;
                $edicao .= '</li>';
            }
            $edicao .= '</ul>';
        }

        return $edicao;
        */

        $results = array();
        foreach($categoriaespecials as $tmp){
            if(intval($tmp->categoria_id)==$id){
                $results[] = $tmp;
            }
        }

        $edicao = '';

        for($i=0,$n=sizeof($results);$i<$n;$i++){

            $fetch = $results[$i];

            $edicao .= tag('tr',
                tag('td', $fetch->id)
                .tag('td',inputSimples("categoriaespecial[{$fetch->id}][nome]",$fetch->nome,'',100, 30, 'style="float:left;margin-left:'.($ident*50).'px;width:200px;"'))
                .tag('td',select("categoriaespecial[{$fetch->id}][st_ativo]",$fetch->st_ativo,'',array('S'=>'Sim','N'=>'Nao')))
                .tag('td',inputSimples("categoriaespecial[{$fetch->id}][ordem]",$i+1,'',10,10))
                .tag('td',tag('a class="del" href="javascript:categoriasDel('.$fetch->id.', \''.$fetch->nome.'\')" ','&nbsp;'))
            );

            $ident ++ ;
            // $edicao .= $this->categoriasProcessa($fetch->id, $ident, $zebra, $tag_estendida, $categorias);
            $ident -- ;
        }

        return $edicao;

    }

}
