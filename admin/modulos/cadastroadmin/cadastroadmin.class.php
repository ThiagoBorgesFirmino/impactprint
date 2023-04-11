<?php

class cadastroadmin extends cadastro {

    function __construct($id=0) {
        $this->tipocadastro_id = cadastro::TIPOCADASTRO_ADMINISTRADOR;
        parent::__construct($id);
    }

    public function get_table_name(){
        return 'cadastro';
    }

    public function salva(){
        $this->tipocadastro_id = cadastro::TIPOCADASTRO_ADMINISTRADOR;
        $ret = parent::salva();
        return $ret;
    }

    public function validaCadastro(&$erros=array()){
        if(!is_set($this->nome) ){
            $erros['nome'] = 'Digite o nome';
        }
        if(!is_email($this->email) ){
            $erros['email'] = 'Digite o e-mail corretamente';
        }
        if(!$this->validaUnicoCadastro('email')){
            $erros['email'] = 'Já existe um administrador cadastrado com este e-mail';
        }
        if($this->cpf != '' && !is_cpf($this->cpf)){
            $erros['empresa'] = "Digite seu CPF corretamente";
        }
        if($this->cpf != '' && !$this->validaUnicoCadastro('cpf')){
            $erros['cpf'] = 'Já existe um administrador cadastrado com este CPF';
        }
        if(!is_set($this->login)){
            $erros['login'] = 'Digite o login';
        }
        if(!$this->validaUnicoCadastro('login')){
            $erros['login'] = 'Já existe um administrador cadastrado com este login';
        }
        if($this->id){
            if(request('alterar_senha')){
                if(!is_set($this->senha)){
                    $erros['senha'] = "Digite a senha";
                }
            }
        }
        else {
            if(!is_set($this->senha)){
                $erros['senha'] = "Digite a senha";
            }
        }
        if($erros){
            return sizeof($erros);
        }else{
            return true;
        }
        // return sizeof($erros)==0;
    }

}