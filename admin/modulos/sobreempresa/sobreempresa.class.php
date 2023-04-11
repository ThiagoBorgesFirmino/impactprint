<?php

class sobreempresa extends cmsitem {

    public function get_table_name(){
        return 'cmsitem';
    }

    public function salva(){
        $ret = parent::salva();
        if($ret){
            // $this->salvaImagem('img1', 'img1');
        }
        return $ret;
    }

}