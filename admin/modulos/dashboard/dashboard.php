<?php

class modulo_dashboard extends modulo_admin {

    public $arquivo = 'dashboard';

    // Pesquisa
    public function pesquisa(){

        $t = new TemplateAdmin('admin/modulos/dashboard/tpl.dashboard.html');
        $t->h1 = h1($this->modulo->nome);

        $edicao = '';
        ob_start();

        // print '<div style="float:left;width:49%">';
        print '<div class="row">';
        print '<div class="col-md-6">';
        require_once 'admin/modulos/dashboard/item-categoria.php';
        print '</div>';
        print '<div class="col-md-6">';
        require_once 'admin/modulos/dashboard/cliente-ramo.php';
        print '</div>';
        print '</div>';

        print '<div class="row">';
        print '<div class="col-md-6">';
        require_once 'admin/modulos/dashboard/orcamento-dia.php';
        print '</div>';
        print '<div class="col-md-6">';
        require_once 'admin/modulos/dashboard/item-top.php';
        print '</div>';
        print '</div>';
        // print '</div>';

        // if($_SERVER['SERVER_NAME']=='localhost'){
            // require_once 'admin/modulos/dashboard/blogpost-tipo.php';
        // }

        $edicao = ob_get_contents();
        ob_end_clean();

        $t->edicao = $edicao;

        $this->adm_instance->montaMenu($t);

        $opts = array('include_js' => array(
            PATH_SITE.'admin/js/canvasjs-1.7.0/canvasjs.min.js'
            )
        );

        $this->adm_instance->show($t,$opts);
    }

    static function widget_admin(){


    }

}
