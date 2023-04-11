<?php

class cadastrovendedor extends cadastro {

    function __construct($id=0) {
        $this->tipocadastro_id = cadastro::TIPOCADASTRO_VENDEDOR;
        parent::__construct($id);
    }

    public function get_table_name(){
        return 'cadastro';
    }

    public function salva(){
        $this->tipocadastro_id = cadastro::TIPOCADASTRO_VENDEDOR;
        if($this->tipocadastro_id == cadastro::TIPOCADASTRO_VENDEDOR && $this->st_fixo == 'S'){			
            $this->st_ativo = 'S';
			query("UPDATE cadastro SET st_fixo = 'N' WHERE tipocadastro_id = 3");			
        }
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
            $erros['email'] = 'Já existe um vendedor cadastrado com este e-mail';
        }
        // if(!is_set($this->fone_com)){
        //     $erros['empresa'] = "Digite seu telefone";
        // }
        if($this->cpf != '' && !is_cpf($this->cpf)){
            $erros['empresa'] = "Digite seu CPF corretamente";
        }
        if($this->cpf != '' && !$this->validaUnicoCadastro('cpf')){
            $erros['cpf'] = 'Já existe um vendedor cadastrado com este CPF';
        }
        if(!is_set($this->login)){
            $erros['login'] = 'Digite o login';
        }
        if(!$this->validaUnicoCadastro('login')){
            $erros['login'] = 'Já existe um vendedor cadastrado com este login';
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
    }

}