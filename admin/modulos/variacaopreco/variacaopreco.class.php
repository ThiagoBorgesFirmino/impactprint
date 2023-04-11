<?php

class variacaopreco extends base {

    var $id;
    var $qtd_1;
    var $qtd_2;

    public function validaDados(&$erro=array()){

        if(intval($this->qtd_1)<=0){
            $erro[] = 'O campo quantidade 1 deve ser maior que zero';
        }

        if(intval($this->qtd_2)<=0){
            $erro[] = 'O campo quantidade 2 deve ser maior que zero';
        }

        if(intval($this->qtd_1)>intval($this->qtd_2)){
            $erro[] = 'O campo quantidade 2 deve ser maior que o campo quantidade 1';
        }

        if(sizeof($erro)==0){
            // Periodos conflitantes
            if(rows(query("select * from variacaopreco where {$this->qtd_1} BETWEEN qtd_1 AND qtd_2 ".($this->id>0?" AND id <> {$this->id} ":"")))>0){
                $erro[] = "O campo quantidade 1 {$this->qtd_1} entra em conflito com outros, ajuste a quantidade individualmente para fazer esse tipo de altera&ccedil;&atilde;o";
            }

            if(rows(query("select * from variacaopreco where {$this->qtd_2} BETWEEN qtd_1 AND qtd_2 ".($this->id>0?" AND id <> {$this->id} ":"")))>0){
                $erro[] = "O campo quantidade 2 {$this->qtd_2} entra em conflito com outros, ajuste a quantidade individualmente para fazer esse tipo de altera&ccedil;&atilde;o";
            }
        }

        return sizeof($erro)==0;

    }

    static function tabelaPreco(&$item){

        $return = array();

        $query = query(
        $sql = "
        SELECT
            variacaopreco.qtd_1
            ,variacaopreco.qtd_2
            ,ifnull(preco.preco,0) preco
            ,preco.id
        FROM
            variacaopreco
        INNER JOIN preco ON (
            preco.item_id = {$item->id}
        AND preco.qtd_1 = variacaopreco.qtd_1
        AND preco.qtd_2 = variacaopreco.qtd_2
        )
        ORDER BY
            variacaopreco.qtd_1
            ,variacaopreco.qtd_2;
        ");

        while($fetch=fetch($query)){
            if($fetch->preco > 0){
                $return[] = $fetch;
            }
        }

        return $return;
    }
}