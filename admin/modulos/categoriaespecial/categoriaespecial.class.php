<?php

class categoriaespecial extends categoria {

    public function get_table_name(){
        return 'categoria';
    }

    function __construct($id=0) {
        $this->st_lista_menu = 'N';
        parent::__construct($id);
    }
}
