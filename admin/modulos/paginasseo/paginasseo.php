<?php
class modulo_paginasseo extends modulo_admin {
    
    public $arquivo = 'paginasseo';
    public  $paginas = array(
        "index"=>"Home"               
       ,"contato"=>"Contato"              
       ,"brindes"=>"Produtos"             
       ,"Promocoes"=>"Promo&ccedil;&otilde;es"
       //,"cadastro"=>"Cadastro"              
       ,"sobre"=>"Sobre"              
       //,"Novidades"=>"Novidades"              
       //,"login"=>"Login" 
   );

    public function pesquisa(){
        if(request('popup')){
            $t = new TemplateAdminPopup('admin/tpl.admin-cadastro-generico.html');
        }
        else {
            $t = new TemplateAdmin('admin/tpl.admin-cadastro-generico.html');
        }

        $t->h1 = h1($this->modulo->nome);

        if(request('action')=='editar' || substr(request('action'),0,6)=='salvar'|| request('action')=='sair'){
            $this->editar($t);
            return;
        }

        $edicao = '';

        $edicao .= "<div class='new_grid_adm'><ul>";
        foreach( $this->paginas as $key=>$value ){
            $edicao .= tag("li id='cat_{$key}'",
                tag("table class='table'",
                    tag("tr onclick='javascript: openEditPagina(\"{$this->arquivo}\",\"{$key}\");'",
                        tag("th","Página")
                    )
                    .tag("tr",
                        tag("td onclick='javascript: openEditPagina(\"{$this->arquivo}\",\"{$key}\");'",$value)
                    )
                )
            );
        }
        $edicao .= "</ul></div>";

        $edicao .= tag("script",
            "function openEditPagina(modulo,id){
                _url = '".PATH_SITE."admin.php/'+modulo+'/?action=editar&pop=1&metodo='+id;
                window.open(_url,'pop_'+id,'width=1024,resizable=no,scrollbars=yes,status=no,titlebar=no,toolbar=no,height='+$(window).height());
            }"
        );
        

        $t->edicao = $edicao;
        
        $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t);
    }

    public function editar(){
        if(request('pop')){
            $t = new TemplateAdminPop('admin/tpl.admin-cadastro-generico.html');
        }

        $t->h1 = h1($this->modulo->nome." - ".($this->paginas[request("metodo")]));

        $args = "";
        $metodo = request("metodo");
        if($metodo == "Promocoes" || $metodo == "Novidades"){
            $args = $metodo;
            $metodo = "brindes";
        }

        $opts = array();        
        $opts["modelo"]   = "seopro";
        $opts["metodo"]   = $metodo;
        $opts["args"]     = $args;
        $seopro = new seopro($opts);
        
        if(substr(request('action'),0,6)=="salvar"){

            $next = substr(request('action'),7,strlen(request('action')));

            $seopro = seopro::validaSalva(array("modelo"=>"seopro","metodo"=>$metodo,"args"=>$args) , array("metodo"=>$metodo,"args"=>$args), $erros);
            if($erros!=""){
                $_SESSION["erro"] = tag("p",$erros);
            }else{
                $_SESSION["sucesso"] = tag("p","Dados salvos com sucesso!");
               
                if(trim(@$next)!=''){
                    $this->afterSave($next,$this->arquivo,$seopro);
                    return;
                }

            }
        }
        
        if(request('action')=='sair'){
            $this->afterSave('sair',$this->arquivo,$seopro);
        }

        $t->parseBlock('BLOCK_TOOLBAR');

        $edicao = "";

        $opts = array();
        $opts["url_tag"] = ($metodo=="index"?false:true);

        if($seopro->url_tag=="")$seopro->url_tag = $metodo.($args!=""?"/{$args}":"");
        $edicao .= $seopro->getEdit($opts);


        $edicao .= tag('br clear="all"');
        
        $t->edicao = $edicao;

        // $opts = array(
        //     'include_js' => PATH_SITE."admin/modulos/{$this->arquivo}/cadastroclienteadmin.js"
        // );

        // $this->adm_instance->montaMenu($t);
        $this->adm_instance->show($t, $opts);
    }

}