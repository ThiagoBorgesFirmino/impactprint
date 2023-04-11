<?php

class cadastrocliente extends cadastro {

    function __construct($id=0) {
        $this->tipocadastro_id = cadastro::TIPOCADASTRO_CLIENTE;
        parent::__construct($id);
    }

    public function get_table_name(){
        return 'cadastro';
    }

    public function salva(){
        $this->tipocadastro_id = cadastro::TIPOCADASTRO_CLIENTE;
        $ret = parent::salva();
        return $ret;
    }

    public function validaCadastro(&$erros=array(), $re_senha='',$admin=false){
        if(!is_email($this->email) ){
            $erros['email'] = 'Digite seu e-mail corretamente';
        }
        if(!$this->validaUnicoCadastro('email')){
            $erros['email'] = 'Já existe um cliente cadastrado com este e-mail';
        }

        // if(!$this->validaUnicoCadastro('cnpj')){
        //     $erros['email'] = 'Já existe um cliente cadastrado com este CNPJ';
        // }

        if(!is_set($this->nome) ){
            $erros['nome'] = 'Digite seu nome';
        }
      
        // if(!is_set($this->empresa)){
        //     $erros['empresa'] = "Digite sua empresa";
        // }

        // if(!is_set($this->fone_com)){
        //     $erros['fone_com'] = "Digite seu telefone";
        // }

     
        return sizeof($erros)==0;
    }

 
    public function validaCadastroCheckout(&$erros=array()){
        if(!is_email($this->email) ){
            $erros['email'] = 'Digite seu e-mail corretamente';
        }
        if(!$this->validaUnicoCadastro('email')){
            $erros['email'] = 'Já existe um cliente cadastrado com este e-mail';
        }

        // if($this->senha==""){
        //     $erros['senha'] = 'Digite sua senha';
        // }
       
        if(!is_set($this->nome) ){
            $erros['nome'] = 'Digite seu nome';
        }        
        if(!is_set($this->empresa)){
            $erros['empresa'] = "Digite sua empresa";
        }
        // if(!is_set($this->fone_com)){
        //     $erros['fone_com'] = "Digite seu telefone";
        // }
        
        return sizeof($erros)==0;
    }


}