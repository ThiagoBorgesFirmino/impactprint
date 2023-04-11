<?php

class modulo_marcadagua extends modulo_admin {

    public $arquivo = 'marcadagua';

    // Pesquisa
    public function pesquisa(){

        if(request('popup')){
            $t = new TemplateAdminPopup('admin/tpl.admin-cadastro-generico.html');
        }
        else {
            $t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
        }

        $t->h1 = h1($this->modulo->nome);

        /*

        $config = new config(array('chave'=>'HABILITA_MARCA_DAGUA'));

        $tamanho1 = new config(array('chave'=>'IMG1_TAMANHO'));
        $tamanho2 = new config(array('chave'=>'IMG2_TAMANHO'));
        $tamanho3 = new config(array('chave'=>'IMG3_TAMANHO'));

        $marca1 = new config(array('chave'=>'IMG1_HABILITA_MARCA_DAGUA'));
        $marca2 = new config(array('chave'=>'IMG2_HABILITA_MARCA_DAGUA'));
        $marca3 = new config(array('chave'=>'IMG3_HABILITA_MARCA_DAGUA'));

        */

        if(request('action')=='salvar'){
            try {

                foreach(request('config') as $key => $valor){
                    $config = new config(array('chave'=>$key));
                    $config->grupo = 'Configuração de imagens';
                    $config->chave = $key;
                    $config->valor = $valor;
                    $config->salva();
                    // printr($config);
                }

                if(request('gerar_imagens')=='S'){

                    set_time_limit(0);
                    ini_set('memory_limit', '-1');
                    ob_implicit_flush(true);

                    $path = 'img/produtos/original/';
                    $dir = dir($path);

                    $x = 0;

                    $sql = array();

                    $imagens = $this->get_imagens();

                    $opacidade = config::get($key_opacidade = 'MARCA_DAGUA_OPACIDADE');

                    while($file = $dir->read()){

                        if($file{0}=='.'){
                            continue;
                        }

                        if(!in_array($file, $imagens)){
                            continue;
                        }

                        for($i=1;$i<=3;$i++){

                            try {

                                /*
                                $dest = "img/produtos/{$i}/{$file}";

                                $tamanho = config::get($key_tamanho = 'IMG'.$i.'_TAMANHO');
                                $marca = config::get($key_marca = 'IMG'.$i.'_HABILITA_MARCA_DAGUA');

                                $si = new SimpleImage($source);

                                if($marca == 'S'){
                                    $si->overlay('img/marcadagua.png', 'center', $opacidade);
                                    // $si->save($path_marca_dagua, 100);
                                }

                                $si->resize($tamanho, $tamanho);
                                $si->save($dest,100);
                                */

                                /*
                                query(
                                "
                                INSERT INTO cronjob (tipo,processado,dt_cadastro,param1,param2,param3)
                                VALUES (
                                    'cortarimagem'
                                    ,0
                                    ,NOW()
                                    ,'{$file}'
                                    ,'".config::get($key_tamanho = 'IMG'.$i.'_TAMANHO')."'
                                    ,'".config::get($key_marca = 'IMG'.$i.'_HABILITA_MARCA_DAGUA')."'
                                )");
                                */

                                $sql[] = "(
                                    'cortarimagem'
                                    ,0
                                    ,NOW()
                                    ,'{$file}'
                                    ,'{$i}'
                                    ,'".config::get($key_tamanho = 'IMG'.$i.'_TAMANHO')."'
                                    ,'".config::get($key_marca = 'IMG'.$i.'_HABILITA_MARCA_DAGUA')."'
                                )";

                            }
                            catch(Exception $ex){
                            }
                        }
                    }

                    if(sizeof($sql)>0){
                        foreach(array_chunk($sql, 100) as  $sql){
                            query(
                            "
                            INSERT INTO cronjob (tipo,processado,dt_cadastro,param1,param2,param3,param4)
                            VALUES
                            ".join(',', $sql)."
                            ;");
                        }
                    }
                }

                // printr($novaconfig);

                /*
                 * $novaconfig = (object) request('config');
                if($novaconfig->valor != $config->valor){

                    set_time_limit(0);
                    ini_set('memory_limit', '-1');
                    ob_implicit_flush(true);

                    $config->valor = $novaconfig->valor;
                    $config->salva();

                    $out = array();

                    if($config->valor == 'S'){
                        $path = 'img/produtos/marcadagua/';
                    }
                    else {
                        $path = 'img/produtos/original/';
                    }

                    $dir = dir($path);

                    while($file = $dir->read()){

                        if($file{0}=='.'){
                            continue;
                        }

                        if(is_file($source=$path.$file)){
                            $dest = 'img/produtos/'.$file;
                            copy($source, $dest);
                        }
                    }

                    // Limpa cache timthumb
                    $dir = dir($path='timthumb/cache/');
                    while($file = $dir->read()){

                        if($file{0}=='.'){
                            continue;
                        }

                        if(pathinfo($file, PATHINFO_EXTENSION)=='txt'){
                            unlink($path.$file);
                        }
                    }

                    $_SESSION['sucesso'] = 'Ok, dados salvos e imagens alteradas';

                }
                else {
                    $_SESSION['sucesso'] = 'Ok, nada a alterar';
                }
                */

            }
            catch(Exception $ex){
                $_SESSION['erro'] = $ex->getMessage();
            }
        }

        $edicao = '';

        $opacidade = new config(array('chave'=> $key_opacidade = 'MARCA_DAGUA_OPACIDADE'));
        $arr_opacidade = array();

        for($i =.1; $i <= .9 ; $i+=.1){
            $arr_opacidade["{$i}"] = $i;
        }

        $edicao .= tag('div class="well" xstyle="width:49%;float:left;height:260px"',
            tag('legend', 'Configuração geral')
            .select("config[{$key_opacidade}]", $opacidade->valor, 'Opacidade da marca dagua:', $arr_opacidade)
            .select("gerar_imagens", '', 'Gerar novas imagens ao salvar?', array('N'=>'Não','S'=>'Sim'))
        );

        for($i=1;$i<=3;$i++){

            $edicao .= '<div class="well">';

            $tamanho = new config(array('chave'=> $key_tamanho = 'IMG'.$i.'_TAMANHO'));
            $marca = new config(array('chave'=> $key_marca = 'IMG'.$i.'_HABILITA_MARCA_DAGUA'));

            $edicao .= tag('legend', 'Config imagem '.$i);
            $edicao .= select("config[{$key_marca}]", $marca->valor, 'Marca dagua ativa no site?:', array('N'=>'Não','S'=>'Sim'));
            $edicao .= inputSimples("config[{$key_tamanho}]", $tamanho->valor, 'Tamanho da imagem (quadrada):', 50, 50);

            $edicao .= '</div>';

        }

        $t->edicao = $edicao;

        $this->adm_instance->montaMenuSimples($t);
        $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }

    private function get_imagens(){

        $ret = array();

        $itens = results("SELECT * FROM item");

        foreach($itens as $item){

            foreach(range(0,20) as $i) {

                if ($i == 0) {
                    $imagem = 'imagem';
                }
                else {
                    $imagem = 'imagem_d' . $i;
                }

                if (isset($item->$imagem) && $item->$imagem != '') {
                    $ret[] = $item->$imagem;
                }
            }
        }

        return $ret;

    }
}