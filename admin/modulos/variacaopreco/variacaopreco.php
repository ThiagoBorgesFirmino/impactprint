<?php

class modulo_variacaopreco extends modulo_admin {

    public $arquivo = 'variacaopreco';
    const ATIVO = true;

    public function pesquisa(){

        if(request('popup')){
            $t = new TemplateAdminPopup('admin/tpl.admin-cadastro-generico.html');
        }
        else {
            $t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
        }

        $t->h1 = h1($this->modulo->nome);

        if(request('action')=='salvar'){

            $erros = '';

            foreach(is_array(@$_REQUEST['variacaopreco'])?$_REQUEST['variacaopreco']:array() as $id => $arrcor){

                $variacaopreco = new variacaopreco($id);

                $qtd_1 = $variacaopreco->qtd_1;
                $qtd_2 = $variacaopreco->qtd_2;

                $variacaopreco->set_by_array($arrcor);

                $erro = array();
                if($variacaopreco->validaDados($erro)){
                    $variacaopreco->salva();
                    query("UPDATE preco SET qtd_1 = {$variacaopreco->qtd_1} WHERE qtd_1 = {$qtd_1}");
                    query("UPDATE preco SET qtd_2 = {$variacaopreco->qtd_2} WHERE qtd_2 = {$qtd_2}");
                }
                else{
                    $erros .= join('<br />', $erro);
                }
            }

            if(is_array(@$_REQUEST['variacaoprecoNova']) && $_REQUEST['variacaoprecoNova']['qtd_1']!=''){

                $variacaopreco = new variacaopreco();
                $variacaopreco->set_by_array($_REQUEST['variacaoprecoNova']);

                $erro = array();
                if($variacaopreco->validaDados($erro)){
                    if(!$variacaopreco->salva()){
                        $erros .= tag('p', 'Houve uma falha ao salvar os dados');
                    }
                }
                else{
                    $erros .= join('<br />', $erro);
                }
                unset($variacaopreco);
            }

            foreach(is_array(@$_REQUEST['variacaoprecoExcluir'])?$_REQUEST['variacaoprecoExcluir']:array() as $id => $arrvariacaopreco){

                $variacaopreco = new variacaopreco($id);

                if(rows(query("SELECT * FROM preco WHERE qtd_1 = {$variacaopreco->qtd_1} AND qtd_2 = {$variacaopreco->qtd_2} AND preco > 0 "))){
                    $erros .= tag('p', 'Não é possivel excluir, existem produtos relacionados com preço maior que zero');
                }
                else {
                    $variacaopreco->exclui();
                }
                unset($variacaopreco);
            }

            if($erros!=''){
                $_SESSION['erro'] = $erros;
            }
        }

        $edicao = '';

        $edicao .= '<div class="well">';
        $edicao .= tag('legend', 'Nova Variação de Preço');
        //$edicao .= select("variacaoprecoNova[st_ativo]", 'S', 'Status Ativo', array('S'=>'Sim','N'=>'Nao'));
        //$edicao .= inputFile("file_nova",'','Imagem');
        $edicao .= inputSimples("variacaoprecoNova[qtd_1]",'','Quantidade 1',40,60);
        $edicao .= inputSimples("variacaoprecoNova[qtd_2]",'','Quantidade 2',40,60);
        $edicao .= '</div>';

        $edicao .= '<table class="table table-bordered table-striped table-hover">';

        $edicao .= tag('tr',
            tag('th', 'Quantidade 1')
            .tag('th', 'Quantidade 2')
            //.tag('th', 'Nome')
            .tag('th', 'Excluir')
        );

        $query = query($sql="SELECT * FROM variacaopreco ORDER BY qtd_1");

        while($fetch=fetch($query) ){

            $edicao .= tag('tr',
                //tag('td '.($zebra?'style="background-color:#eeeeee"':''), select("cor[{$fetch->id}][st_ativo]", $fetch->st_ativo, '', array('S'=>'Sim','N'=>'Nao')))
                //.tag('td '.($zebra?'style="background-color:#eeeeee"':''), ($fetch->imagem!=''?tag('img src="'.PATH_SITE.'img/cor/'.$fetch->imagem.'?x='.mktime().'"').tag('br'):'').inputFile("file_{$fetch->id}",'',''))
                tag('td', inputSimples("variacaopreco[{$fetch->id}][qtd_1]",$fetch->qtd_1,'',20,60))
                .tag('td', inputSimples("variacaopreco[{$fetch->id}][qtd_2]",$fetch->qtd_2,'',20,60))
                .tag('td', checkbox("variacaoprecoExcluir[{$fetch->id}][excluir]",'Sim','Excluir'))
            );
        }

        $edicao .= '</table>';
        $edicao .= str_repeat(tag('br'), 1);

        $t->edicao = $edicao;

        $this->adm_instance->montaMenuSimples($t);
        $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }

}
