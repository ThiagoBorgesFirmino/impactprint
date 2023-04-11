<?php

class mesa extends cmsitem {

    public function get_table_name(){
        return 'cmsitem';
    }

    public function salva(){
        $ret = parent::salva();
        if($ret){
            $this->salvaImagem('img1', 'img1');
            $this->salvaImagem('img2', 'img2');
        }
        return $ret;
    }

}