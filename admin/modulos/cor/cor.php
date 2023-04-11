<?php

class modulo_cor extends modulo_admin {

    public $arquivo = 'cor';

    // Pesquisa
    public function pesquisa(){

        if(request('popup')){
            $t = new TemplateAdminPopup('admin/tpl.admin-cadastro-generico.html');
        }
        else {
            $t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
        }

        $t->h1 = h1($this->modulo->nome);

        if(request('action')=='salvar'){

            try {

                $_sucesso = '';
                $_erro    = '';

                foreach(is_array(@$_REQUEST['cor'])?$_REQUEST['cor']:array() as $id => $arrcor){

                    $cor = new cor($id);
                    $cor->set_by_array($arrcor);

                    //if(isSet($_FILES["file_{$id}"])){
                       // $cor->setFile($_FILES["file_{$id}"]);
                    //}

                    $erro = array();
                    if($cor->validaDados($erro)){
                        //printr($cor);
                        $cor->salva();
                        $_SESSION['sucesso'] = "Dados salvos com sucesso.";
                    }
                    else{
                        $_erro .= join('<br />', $erro);
                    }
                }

                if(is_array(@$_REQUEST['corNova']) && $_REQUEST['corNova']['nome']!=''){

                    $cor = new cor();

                    $cor->set_by_array($_REQUEST['corNova']);
                    //$cor->setFile($_FILES["file_nova"]);

                    $erro = array();
                    if($cor->validaDados($erro)){
                        if(!$cor->salva()){
                            $_SESSION['erro'] = 'Houve uma falha ao salvar os dados';
                        }
                        else {
                            $_SESSION['sucesso'] = "Dados salvos com sucesso.";
                        }
                    }
                    else{
                        $_erro .= join('<br />', $erro);
                    }

                    unset($cor);
                }

                foreach(is_array(@$_REQUEST['corExcluir'])?$_REQUEST['corExcluir']:array() as $id => $arrcor){
                    $cor = new cor($id);
                    $cor->exclui();
                    unset($cor);
                }
            }
            catch(Exception $ex){
                $_SESSION['erro'] = $ex->getMessage();
            }
        }

        $edicao = '';

        $edicao .= '<div class="well">';
        $edicao .= tag('legend', 'Nova Cor');

        $edicao .= select("corNova[st_ativo]", 'S', 'Status Ativo', array('S'=>'Sim','N'=>'Nao'));
        //$edicao .= inputFile("file_nova",'','Imagem');
        $edicao .= inputSimples("corNova[nome]",'','Nome',40,60);
        //$edicao .= inputSimples("corNova[referencia]",'','Codigo',40,60);

        $edicao .= '</div>';

        $edicao .= '<table class="table table-bordered table-striped table-hover">';

        $edicao .= tag('tr',
            tag('th', 'ID')
            .tag('th', 'Status Ativo')
            .tag('th', 'Nome')
            //.tag('th', 'Codigo')
            //.tag('th', 'Imagem')
            .tag('th', 'Excluir')
        );

        $query = query($sql="SELECT * FROM cor ORDER BY nome");

        while($fetch=fetch($query) ){

            //$serial = unserialize($fetch->serial);

            $edicao .= tag('tr',
                tag('td',$fetch->id)
                .tag('td', select("cor[{$fetch->id}][st_ativo]", $fetch->st_ativo, '', array('S'=>'Sim','N'=>'Nao')))
                .tag('td', inputSimples("cor[{$fetch->id}][nome]",$fetch->nome,'',20,60))
                //.tag('td', inputSimples("cor[{$fetch->id}][referencia]",$fetch->referencia,'',2,3))
                // .tag('td', ($fetch->imagem!=''?tag('img style="border:1px solid #888;" src="'.PATH_SITE.'img/cores/'.$fetch->imagem.'"').tag('br'):'')

                //     //.inputFile("file_{$fetch->id}",'','')
                //     ."<div>
				// 	<a href='javascript: imgIcon({$fetch->id});'> cadastrar imagem</a>
				// 	</div>
				// 	<div id='imagem_icone_{$fetch->id}'style='display:none;'></div>"

                // )

                .tag('td', checkbox("corExcluir[{$fetch->id}][excluir]",'Sim','Excluir'))
            );

        }

        $edicao .= tag('script',
            'function imgIcon(cor_id){
				$("#imagem_icone_"+cor_id+"").html("<input type=\'file\' name=\'file_"+cor_id+"\' id=\'file_"+cor_id+"\'size=\'40\'>");
				$("#imagem_icone_"+cor_id+"").toggle();
			}'
        );

        $edicao .= '</table>';

        $t->edicao = $edicao;

        $this->adm_instance->montaMenuSimples($t);
        $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }

}