<?php

class textoapresentacao extends cmsitem {

    public function get_table_name(){
        return 'cmsitem';
    }

    public function salva(){
        $ret = parent::salva();
        if($ret){
            $this->salvaImagem('img1', 'img1');
        }
        return $ret;
    }

    public function getTexto(){
        return ($this->custom2);
        return nl2br($this->custom2);
    }

}