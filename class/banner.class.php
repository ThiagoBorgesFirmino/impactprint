<?php

class banner extends cmsitem {

    public function salva(){

        $ret = null;

        if(!$this->id){
            $this->chave = butil::stringAsTag($this->titulo);
        }

        $ret = parent::salva();

        if($ret){
            $this->salvaImagem('img1', 'img1');
            $this->salvaImagem('img2', 'img2');
            $this->salvaImagem('img3', 'img3');
        }

        return $ret;
    }

    public function get_table_name(){
        return 'cmsitem';
    }

    public function getLink(){
        return $this->custom1;
    }

    public function getTarget(){
        return $this->custom2;
    }

}