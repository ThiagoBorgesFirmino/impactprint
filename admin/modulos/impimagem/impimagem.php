<?php

class modulo_impimagem extends modulo_admin {

    public $arquivo = 'impimagem';

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
            $cmsitem = new cmsitem(intval(request('id')));
            if($cmsitem->id){
                $cmsitem->exclui();
            }
        }

        $edicao = '';

        $edicao .= '<h2>Coloque as imagens que deseja importar em '.PATH_SITE.'_importar/imagens</h2>';
        $edicao .= '<div class="alert alert-info">';
        $edicao .= '<p class="lead">O ideal é que as imagens tenham o nome REFERENCIA_COR_X.jpg Onde X é um número sequencial para não repetir o mesmo nome</p>';
        $edicao .= "<p><a href='".ADMIN_PATH."{$this->arquivo}/processar' class='btn btn-primary'>Processar</a></p>";
        $edicao .= '</div>';
        $edicao .= '<div class="row"><div class="col-md-6">';
        $edicao .= '<p class="lead">Padrão de cores:</p>';
        $edicao .= '<table class="table table-striped">';
        $edicao .= '<tr>';
        $edicao .= "<th>Sigla</th><th>Descrição</th>";
        $edicao .= '</tr>';
        $cores = results($sql="SELECT id, referencia, nome FROM cor ORDER BY referencia");

        foreach($cores as $cor){
            $edicao .= '<tr>';
            $edicao .= "<td>{$cor->referencia}</td><td>{$cor->nome}</td>";
            $edicao .= '</tr>';
        }

        $edicao .= '</table>';
        $edicao .= '</div></div>';

        $t->edicao = $edicao;

        $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }

    public function processar(){

        $t = new TemplateAdmin('admin/modulos/impimagem/tpl.impimagem.html');

        $edicao = '';

        ob_start();

        print h1($this->modulo->nome);

        $itens = $this->get_itens();
        $cores = $this->get_cores();

        $imagens = $this->get_imagens('_importar/imagens');
        $imagens_json = $this->get_imagens_json($imagens);

        // $this->debug($imagens);

        ?>
        <div id="jsItens"></div>
        <script id="personTpl" type="text/template">
            <h1>{{firstName}} {{lastName}}</h1>
            <p>Blog URL: <a href="{{blogURL}}">{{blogURL}}</a></p>
        </script>

        <div class="row">
            <div class="col-md-9 js-itens">
            </div>
            <div class="col-md-3 js-form">
            </div>
        </div>

        <script id="tpl-itens-2" type="x-tmpl-mustache">
            <table class="table table-striped">
            {{#resultados}}
            <tr>
                <td>
                    {{.}}
                    {{.referencia}}
                    {{referencia}}
                    {{resultados.referencia}}
                </td>
            </tr>
            {{#imagens}}
            <!--tr>
                <td>
                    <a href='#'><img src='{{src}}' data-id='{{id}}' /></a>
                </td>
            </tr-->
            {{/imagens}}
            {{/resultados}}
            </table>
        </script>

        <script>

            var itens = <?php echo json_encode($itens) ?>;
            var cores = <?php echo json_encode($cores) ?>;
            var imagens_json = <?php echo json_encode($imagens_json) ?>;

            var salvarProduto = function(){

            };

            var monta = function(){

                var targetContainer = $(".target-output"),
                    templateDefined = $(".target-output").data("template-chosen"),
                    template = $("#mustacheTempalte_"+templateDefined).html();

                var shows = { "shows" : [
                    { "category" : "children",
                        "description" : "<a href='#'>A show</a> about a cake",
                        "title" : "Strawberry Shortcake",
                        "video" : "none"
                    },
                    { "category" : "children",
                        "description" : "A show about a ice",
                        "title" : "Vanilla Ice",
                        "video" : "none"
                    }
                ] };


                var html = Mustache.to_html(template, shows);

                $(targetContainer).html(html);

                /*
                 console.log(imagens_json.length);
                 console.log(imagens_json);

                 var template = $('#tpl-itens').html();
                 Mustache.parse(template); // optional, speeds up future uses

                 var rendered = Mustache.to_html(template, {resultados:imagens_json});

                 $('.js-itens').html(rendered);
                 */
            }

            // $(document).ready(function(){
                // monta2();
            // });

        </script>
        <?php

        $edicao = ob_get_contents();
        ob_end_clean();

        $t->edicao = $edicao;

        $this->adm_instance->montaMenu($t);

        $opts = array(
            'include_js' => PATH_SITE.'admin/modulos/impimagem/impimagem.js'
        );

        $this->adm_instance->show($t, $opts);

    }

    public function processarbkp(){

        $t = new Template('admin/modulos/impimagem/tpl.impimagem.html');

        $edicao = '';

        ob_start();

        print h1($this->modulo->nome);

        $itens = $this->get_itens();
        $cores = $this->get_cores();

        $imagens = $this->get_imagens('_importar/imagens');
        $imagens_json = $this->get_imagens_json($imagens);

        // $this->debug($imagens);

        ?>

        <div class="row">
            <div class="col-md-9 js-itens">

            </div>
            <div class="col-md-3 js-form">

            </div>
        </div>

        <script id="tplItens" type="text/template">
            Template
            <table class="table table-striped">
            {{#resultados}}
            <tr>
                <td>
                    {{.}}
                    {{.referencia}}
                    {{referencia}}
                    {{resultados.referencia}}
                </td>
            </tr>
            {{#imagens}}
            <!--tr>
                <td>
                    <a href='#'><img src='{{src}}' data-id='{{id}}' /></a>
                </td>
            </tr-->
            {{/imagens}}
            {{/resultados}}
            </table>
        </script>

        <script>

            var itens = <?php echo json_encode($itens) ?>;
            var cores = <?php echo json_encode($cores) ?>;
            var imagens_json = <?php echo json_encode($imagens_json) ?>;

            var salvarProduto = function(){

            };

            var monta = function(){

                var targetContainer = $(".target-output"),
                    templateDefined = $(".target-output").data("template-chosen"),
                    template = $("#mustacheTempalte_"+templateDefined).html();

                var shows = { "shows" : [
                    { "category" : "children",
                        "description" : "<a href='#'>A show</a> about a cake",
                        "title" : "Strawberry Shortcake",
                        "video" : "none"
                    },
                    { "category" : "children",
                        "description" : "A show about a ice",
                        "title" : "Vanilla Ice",
                        "video" : "none"
                    }
                ] };


                var html = Mustache.to_html(template, shows);

                $(targetContainer).html(html);

                /*
                console.log(imagens_json.length);
                console.log(imagens_json);

                var template = $('#tpl-itens').html();
                Mustache.parse(template); // optional, speeds up future uses

                var rendered = Mustache.to_html(template, {resultados:imagens_json});

                $('.js-itens').html(rendered);
                */
            }

        </script>
        <?php

        $edicao = ob_get_contents();
        ob_end_clean();

        $t->edicao = $edicao;

        // $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);

    }

    public function salvar(){

    }

    private function get_imagens($path){

        $ret = array();

        $this->debug("Analisando pasta: {$path}");

        $files = dir($path);

        while($file=$files->read()){

            if($file{0} == '.'){
                continue;
            }

            $fullpath = $path.DIRECTORY_SEPARATOR.$file;

            if(is_dir($fullpath)){
                $ret = array_merge($ret, $this->get_imagens($fullpath));
            }
            else {
                if(strtolower(substr($file,-3))=='jpg'){
                    $ret[] = $fullpath;
                }
            }
        }

        return $ret;
    }

    private function get_imagens_json(&$imagens){

        $ret = array();

        foreach($imagens as $src){

            @list($referencia, $cor, $ordem) = explode('_', $imagem=substr(basename($src),0,-4) );

            if(!isset($ret[$referencia])){
                $ret[$referencia] = array();
            }

            $ret[$referencia][] = (object) array(
                'src' => $src
                ,'imagem' => $imagem
                ,'referencia' => $referencia
                ,'cor' => $cor
                ,'ordem' => $ordem
            );

            /*
            $ret[] = array(
                'src' => $src
                ,'imagem' => $imagem
                ,'referencia' => $referencia
                ,'cor' => $cor
                ,'ordem' => $ordem
            );
            */

        }

        $tmp = $ret;
        $ret = array();

        foreach($tmp as $referencia => $itens){
            $x = new stdClass();
            $x->referencia = "REF{$referencia}";
            $x->teste = "REF{$referencia}";
            $ret[] = $x;
            // $ret[] = "REF{$referencia}";
        }

        // $this->debug($ret);
        return $ret;
    }

    private function cria_imagem_tmp(&$imagens){

        foreach($imagens as $imagem){

            query("
            INSERT INTO imagem (

            )
            ");

        }
    }

    private function get_itens(){
        $ret = array();
        $query = query($sql="SELECT id, itemsku_id, referencia, nome FROM item");
        while($fetch=fetch($query)){
            $ret[] = $fetch;
        }
        return $ret;
    }

    private function get_item(&$itens,$referencia){
        $ret = null;
        foreach($itens as $item){
            if($item->referencia == $referencia){
                $ret = $item;
                break;
            }
        }
        return $ret;
    }

    private function get_cores(){
        $ret = array();
        $query = query($sql="SELECT id, referencia, nome FROM cor");
        while($fetch=fetch($query)){
            $ret[] = $fetch;
        }
        return $ret;
    }

    private function get_cor(&$itens,$referencia){
        $ret = null;
        foreach($itens as $item){
            if($item->referencia == $referencia){
                $ret = $item;
                break;
            }
        }
        return $ret;
    }

    private function debug($str){
        print '<pre>';
        print_r($str);
        print '</pre>';
        print PHP_EOL;
    }

}