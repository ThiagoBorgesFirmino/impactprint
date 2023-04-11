<?php

class modulo_gravacao extends modulo_admin {

    public $arquivo = 'gravacao';

    const ATIVO = true;
    const CARAC_ID = 2;

    public function pesquisa(){

        if(request('popup')){
            $t = new TemplateAdminPopup('admin/tpl.admin-cadastro-generico.html');
        }
        else {
            $t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
        }

        $t->h1 = h1($this->modulo->nome);

        if(request('action')=='salvar'){

            foreach(is_array(@$_REQUEST['caracvalor'])?$_REQUEST['caracvalor']:array() as $id => $arrcaracvalor){
				
                $caracvalor = new caracvalor($id);
                $caracvalor->set_by_array($arrcaracvalor);

                $erro = array();
                if($caracvalor->validaDados($erro)){
                    $caracvalor->salva();
                }
                else{
                    $_SESSION['erro'] = join('<br />', $erro);
                }
            }

            if(is_array(@$_REQUEST['caracvalorNova']) && $_REQUEST['caracvalorNova']['nome']!=''){

                $caracvalor = new caracvalor();
                $caracvalor->set_by_array($_REQUEST['caracvalorNova']);

                $erro = array();
                if($caracvalor->validaDados($erro)){
                    $caracvalor->salva();
					// printr($caracvalor);
					// die();
                }
                else{
                    $_SESSION['erro'] = join('<br />', $erro);
                }
                unset($caracvalor);
            }

            foreach(is_array(@$_REQUEST['caracvalorExcluir'])?$_REQUEST['caracvalorExcluir']:array() as $id => $arrcaracvalor){
                $caracvalor = new caracvalor($id);
                $caracvalor->exclui();
                unset($caracvalor);
            }

            $_SESSION["sucesso"] = tag("p","Dados salvos com sucesso.");
        }

        $edicao = '';

        $edicao .= '<div class="well">';
        $edicao .= tag('legend', 'Cadastrar nova gravação');
        $edicao .= inputHidden("caracvalorNova[carac_id]",'2');
        $edicao .= select("caracvalorNova[st_ativo]", 'S', 'Status Ativo', array('S'=>'Sim','N'=>'Nao'));
        //$edicao .= inputFile("file_nova",'','Imagem');
        $edicao .= inputSimples("caracvalorNova[nome]",'','Nome',40,60);
        //$edicao .= textArea("caracvalorNova[descricao]",'','Descricao');
        $edicao .= '</div>';

        $edicao .= '<table class="table table-bordered table-striped table-hover">';

        $edicao .= tag('tr',
            tag('th', 'Status Ativo')
            //.tag('th', 'Imagem')
            .tag('th', 'Nome')
            //.tag('th', 'Descricao')
            .tag('th', 'Excluir')
        );

        $query = query($sql="SELECT * FROM caracvalor WHERE carac_id = 2 ORDER BY nome");
        while($fetch=fetch($query) ){
            $edicao .= tag('tr',
                tag('td', select("caracvalor[{$fetch->id}][st_ativo]", $fetch->st_ativo, '', array('S'=>'Sim','N'=>'Nao')))
                //.tag('td', ($fetch->imagem!=''?tag('img src="'.PATH_SITE.'img/materiaprima/130x130/'.$fetch->imagem.'"').tag('br'):'').inputFile("file_{$fetch->id}",'',''))
                .tag('td', inputSimples("caracvalor[{$fetch->id}][nome]",$fetch->nome,'',40,60))
                //.tag('td '.($zebra?'style="background-color:#eeeeee"':''), textArea("caracvalor[{$fetch->id}][descricao]",$fetch->descricao,'', 40, 5))
                .tag('td', checkbox("caracvalorExcluir[{$fetch->id}][excluir]",'Sim','Excluir'))
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
