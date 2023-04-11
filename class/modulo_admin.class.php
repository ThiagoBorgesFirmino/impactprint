<?php

class modulo_admin extends modulo {

    protected $modulo;
    protected $adm_instance;

    protected $blogpost;
    protected $front;
    protected $config;

    public function set_adm(&$obj){
        $this->adm_instance = $obj;
    }

    public function set_modulo($obj){
        $this->modulo = $obj;
    }

    public function set_blogpost($obj){
        $this->blogpost = $obj;
    }

    public function set_front($obj){
        $this->front = $obj;
    }

    public function set_config($obj){
        $this->config = $obj;
    }
	
	protected function add_imagem($blogpost, $key, $width, $height, $label = 'Imagem'){

        // Imagem Maior
        $edicao = '';
        $edicao .= '<div class="well">';
        $edicao .= tag('legend', "{$label} ({$width}px / {$height}px)");
        $edicao .= '<div style="text-align:center">';

        if($blogpost->$key!=''){
            $edicao .= tag("br");
            $edicao .= tag("img src='".PATH_SITE."{$blogpost->$key}' style='max-width:100%'");
            $edicao .= tag("br");
            $edicao .= tag("input type='checkbox' name='excluir{$key}' id='excluir{$key}' value='S'") . "<label for='excluir{$key}'>Excluir</label>";
        }
        else {
            $edicao .= tag("br");
            $edicao .= tag("div style='background-color:#fff;margin:auto;max-width:{$width}px;border:2px dashed #999'", tag("p style='line-height:{$height}px;font-size:10px'", "{$width}px/{$height}px"));
        }

        $edicao .= tag("br");
        $edicao .= tag("a href=\"javascript:void($('#{$key}').toggle())\"",'alterar/cadastrar');
        $edicao .= tag("div style='display:none' id='{$key}'", inputFile("file_{$key}", '', 'Escolha o arquivo') . inputSimples("file_{$key}", '', 'Ou copie aqui o link da imagem', 60, 256));

        $edicao .= '</div>';
        $edicao .= '</div>';

        return $edicao;

    }

	public function getActionLink(){
		if($this->arquivo!=''){
			return PATH_SITE.'admin.php/'."{$this->arquivo}/";
		}
		else {
			return PATH_SITE."admin.php/go_modulo/{$this->id}/";
		}
	}
	
	public function get_table_name(){
		return 'modulo';
	}

    protected function afterSave($next, $method, $obj=null){
        if(trim($next)!=''){
            $_REQUEST['action']='';
            switch($next){
                case 'sair':

                    // Se for pop-up, carrega template de pop-up
                    if(request('pop')){
                        ?>
                        <script>
                            try {

                                // Caso exista a janela anterior
                                if(parent){

                                    <?php
                                    if(array_key_exists('erro', $_SESSION)){
                                        ?>
                                    parent.opener.document.getElementById('erro_msg').innerHTML = ('<?php print $_SESSION['erro'] ?>');
                                    parent.opener.document.getElementById('erro_msg').style.display = '';
                                    <?php
                                    unset($_SESSION['erro']);
                                }

                                if(array_key_exists('sucesso', $_SESSION)){
                                    ?>
                                    parent.opener.document.getElementById('sucesso_msg').innerHTML = ('<?php print $_SESSION['sucesso'] ?>');
                                    parent.opener.document.getElementById('sucesso_msg').style.display = '';
                                    <?php
                                    unset($_SESSION['sucesso']);
                                }
                                ?>
                                }
                            }
                            catch(e){

                            }

                            // Fecha janela pop-up
                            //window.parent.location.href = parent.location.href;
                            window.opener.location.reload();
                            self.close();

                        </script>
                        <?php
                        // $t = new TemplateAdminPop('admin/tpl.admin-cadastro-generico.html');
                    }
                    else {
                        $this->$method();
                    }
                    break;
                case 'novo':
                    $_REQUEST['id'] = '0';
                    $_REQUEST['action'] = 'editar';
                    unset($_REQUEST['cadastro']);
                    $this->adm_instance->$method();
                    break;
                case 'avancar':
                    $_REQUEST['id'] = $obj->get_next_id();
                    $_REQUEST['action']='editar';
                    $this->adm_instance->$method();
                    break;
                case 'retroceder':
                    $_REQUEST['id'] = $obj->get_last_id();
                    $_REQUEST['action']='editar';
                    $this->adm_instance->$method();
                    break;
                default :
                    // Se for pop-up, carrega template de pop-up
                    if(request('pop')){
                        ?>
                        <script>
                            try {

                                // Caso exista a janela anterior
                                if(parent){

                                    <?php
                                    if(array_key_exists('erro', $_SESSION)){
                                        ?>
                                    parent.opener.document.getElementById('erro_msg').innerHTML = ('<?php print $_SESSION['erro'] ?>');
                                    parent.opener.document.getElementById('erro_msg').style.display = '';
                                    <?php
                                    unset($_SESSION['erro']);
                                }

                                if(array_key_exists('sucesso', $_SESSION)){
                                    ?>
                                    parent.opener.document.getElementById('sucesso_msg').innerHTML = ('<?php print $_SESSION['sucesso'] ?>');
                                    parent.opener.document.getElementById('sucesso_msg').style.display = '';
                                    <?php
                                    unset($_SESSION['sucesso']);
                                }
                                ?>
                                }
                            }
                            catch(e){

                            }

                            // Fecha janela pop-up
                            window.opener.location.reload();
                            self.close();

                        </script>
                        <?php
                        // $t = new TemplateAdminPop('admin/tpl.admin-cadastro-generico.html');
                    }
                    else {
                        $this->adm_instance->$method();
                    }
                    ;
            }
            return;
        }
    }

    protected function setLocation($url){
        die(header('location:'.PATH_SITE."admin.php/".$url));
    }

    protected function show(&$t){
        $this->adm_instance->show($t);
    }

}
