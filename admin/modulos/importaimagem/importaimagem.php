<?php

class modulo_importaimagem extends modulo_admin {

    public $arquivo = 'importaimagem';
    private $pathimg = 'admin/modulos/importaimagem/fila/';

    // Pesquisa
    public function pesquisa(){

        $t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
        $t->h1 = h1($this->modulo->nome);

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

                            }
                            catch(Exception $ex){
                            }
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

        ob_start();
        ?>
        <div class="row">
            <div class="col-sm-9">
        <?php

        $imagens = array();
        $dir = dir($this->pathimg);

        while($file = $dir->read()){

            if($file{0} == '.'){
                continue;
            }

            $imagens[] = $file;
        }

        foreach($imagens as $imagem){
            ?>
            <a href="#" style="display:inline-block;padding:10px;border:1px solid #eee;margin:10px;" class="js-imagem" data-imagem="<?php echo $imagem ?>">
                <img src="<?php echo PATH_SITE . $this->pathimg . $imagem ?>" style="width:100px;height:100px">
            </a>
            <?php
        }

        ?>
            </div>
            <div class="col-sm-3">
                <div class="js-analise">
                </div>
                <div class="js-salvar">
                    <br>
                    <div class="form-group">
                        <button type="button" class="btn btn-primary">Salvar</button>
                    </div>
                </div>
            </div>
        </div>
        <?php

        $edicao = ob_get_contents();
        ob_end_clean();

        $t->edicao = $edicao;

        $opts = array(
            'include_js' => PATH_SITE."admin/modulos/{$this->arquivo}/{$this->arquivo}admin.js?".time()
        );

        // $this->adm_instance->montaMenuSimples($t);
        $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t, $opts);
    }

    public function analise(){

        $edicao = '';

        ob_start();
        $imagem = request('imagem');
        list($referencia) = explode('_',$imagem);

        $referencia = substr($referencia,0,strlen($referencia)-4);

        echo inputHidden('imagem', $imagem);

        ?>
        <div class="text-center">
            <a href="#" style="display:inline-block;padding:10px;border:1px solid #eee;margin:10px;">
                <img src="<?php echo PATH_SITE . $this->pathimg . $imagem ?>" style="width:300px;height:300px">
            </a>
        </div>

        <label class="radio-inline">
            <input type="radio" name="tipoimagem" id="tipoimagem1" value="N"> Novo produto
        </label>

        <label class="radio-inline">
            <input type="radio" name="tipoimagem" id="tipoimagem2" value="E"> Produto existente
        </label>

        <div class="js-novo-produto">
            <?php print inputSimples('referencia', $referencia, 'Referencia:', 30, 50); ?>
            <?php print inputSimples('nome', $referencia, 'Nome:', 30, 200); ?>
        </div>

        <div class="js-existente">
            <?php print select('item_id', 0, 'Selecione o item', $this->item_opcoes()); ?>
        </div>

        <div class="js-cor">
            <?php print select('cor_id','','Cor',$this->cor_opcoes()); ?>
        </div>

        <?php

        $edicao = ob_get_contents();
        ob_end_clean();

        print $edicao;
    }

    public function salvar(){

        try {

            $imagem = request('imagem');
            $tipoimagem = request('tipoimagem');
            $referencia = request('referencia');
            $nome = request('nome');

            // $si = new SimpleImage($this->pathimg.$imagem);

            if($tipoimagem == 'N'){

                $_FILES = array();
                $_FILES['file_imagem']['name'] = $imagem;
                $_FILES['file_imagem']['tmp_name'] = $this->pathimg.$imagem;

                $cor = new cor(intval(request('cor_id')));

                $item = new item();
                $item->referencia = $referencia;
                $item->nome = $nome;

                if($cor->id){
                    $item->cor_id = $cor->id;
                }

                if(!$item->valida_atualizacao($erro)){
                    throw new Exception(join('<br>', $erro));
                }

                $item->salva();

                printr($_FILES);
                printr($item);

                print json_encode(array('status'=>true, 'Ok, salvo com sucesso'));
            }

        }
        catch(Exception $ex){
            print json_encode(array('status'=>false,'msg'=>$ex->getMessage()));
        }

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

    private function item_opcoes(){

        $ret = array();

        foreach(results("SELECT item.id, item.referencia, item.nome, cor.nome cor_nome FROM item LEFT JOIN cor ON ( item.cor_id = cor.id ) ORDER BY referencia") as $item){
            $text = "{$item->referencia} - {$item->nome}";
            if($item->cor_nome != ''){
                $text .= " - {$item->cor_nome}";
            }
            $ret[$item->id] = $text;
        }

        return $ret;

    }

    private function cor_opcoes(){

        // $ret = array('');
        $ret = array();

        foreach(results("SELECT cor.* FROM cor ORDER BY referencia") as $item){
            $text = "{$item->referencia} - {$item->nome}";
            $ret[$item->id] = $text;
        }

        return $ret;

    }

}