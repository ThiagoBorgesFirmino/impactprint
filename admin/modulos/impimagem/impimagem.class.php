<?php

class frasehome extends cmsitem {

    public function get_table_name(){
        return 'cmsitem';
    }

    public function salva(){
        $ret = parent::salva();
        return $ret;
    }

}