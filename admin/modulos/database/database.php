<?php
class modulo_database {

    public $arquivo = "database";
    static $error;

    static function query($sql){
        try{
            switch(database::$conntype){
                case "pdo" : 
                    $sth = database::getInstance()->prepare($sql);
                    $sth->execute();
                    //$_REQUEST['DEBUGSQL'][] = $sql;
                    return $sth;
                break;
                case "mysql" : $_REQUEST['DEBUGSQL'][] = $sql; return database::getInstance()->query($sql); break;
                case "postgresql" : $_REQUEST['DEBUGSQL'][] = $sql; return pg_query ( database::getInstance() , $sql ); break;
                default : self::$error = "Nenhum tipo de conexão definida."; throw new Exception("Nenhum tipo de conexão definida.");
            }
        }catch(Exception $e){
            self::$error = $e->getMessage();
            printr("QUERY ERROR : ".$e->getMessage());
            return false;
        }
    }

    static function fetch($sth){/* Return Object(s) */
        try{
            if(!$sth) throw new Exception("FETCH :: Statement não informado.");
            switch(database::$conntype){
                case "pdo" : return $sth->fetch(PDO::FETCH_OBJ); break;
                case "mysql" : return $sth->fetch_object(); break;
                case "postgresql" : return pg_fetch_object ( $sth ); break;
                default : self::$error = "Nenhum tipo de conexão definida."; throw new Exception("Nenhum tipo de conexão definida.");
            }
        }catch(Exception $e){
            self::$error = $e->getMessage();
            printr("FETCH ERROR : ".$e->getMessage());
            return false;
        }
    }
    
    static function result($sth){/* Return Array(s)*/
        try{
            switch(database::$conntype){
                case "pdo" : return $sth->fetch(PDO::FETCH_ASSOC); break;
                case "mysql" : return $sth->fetch_assoc(); break;
                case "postgresql" : return pg_fetch_array ( $sth ); break;
                default : self::$error = "Nenhum tipo de conexão definida."; throw new Exception("Nenhum tipo de conexão definida.");
            }
        }catch(Exception $e){
            self::$error = $e->getMessage();
            printr("RESULT ERROR : ".$e->getMessage());
            return false;
        }
    }
    
    static function rows($sth){/* Return Numero de Linhas consultadas */
        try{
            if(!$sth) throw new Exception("ROWS :: Statement não informado.");
            switch(database::$conntype){
                case "pdo" : return $sth->rowCount(); break;
                case "mysql" : return mysql_num_rows($sth); break;
                case "postgresql" : return pg_num_rows ( $sth ); break;
                default : self::$error = "Nenhum tipo de conexão definida."; throw new Exception("Nenhum tipo de conexão definida.");
            }
        }catch(Exception $e){
            self::$error = $e->getMessage();
            printr("ROWS ERROR : ".$e->getMessage());
            return false;
        }
    }

    static function num_fields($sth){/* Return Numero de colunas */
        try{
            switch(database::$conntype){
                case "pdo" : return $sth->columnCount(); break;
                case "mysql" : return mysqli_num_fields($sth); break;
                case "postgresql" : return pg_num_fields($sth); break;
                default : self::$error = "Nenhum tipo de conexão definida."; throw new Exception("Nenhum tipo de conexão definida.");
            }
        }catch(Exception $e){
            self::$error = $e->getMessage();
            printr("NUM FIELDS ERROR : ".$e->getMessage());
            return false;
        }
    }
   
    static function field_name($sth,$index){/* Return Nome da colunas */
        try{
            switch(database::$conntype){
                case "pdo" : return $sth->getColumnMeta($index)["name"]; break;
                case "mysql" : return mysqli_fetch_field_direct($sth,$index)->name; break;
                case "postgresql" : return pg_field_name($sth,$index); break;
                default : self::$error = "Nenhum tipo de conexão definida."; throw new Exception("Nenhum tipo de conexão definida.");
            }
        }catch(Exception $e){
            self::$error = $e->getMessage();
            printr("NAME FIELDS ERROR : ".$e->getMessage());
            return false;
        }
    }

    static function last_inserted_id($pg_resource=""){/** Retorno o id do ultimo registro adicionado */
        try{
            switch(database::$conntype){
                case "pdo" :
                    return  $_REQUEST['DEBUGSQL'][] = database::getInstance()->lastInsertId();
                break;
                case "mysql" : return $_REQUEST['DEBUGSQL'][] = mysqli_insert_id(database::getInstance()); 
                break;
                case "postgresql" : return $_REQUEST['DEBUGSQL'][] = pg_last_oid ( $pg_resource ); 
                break;
                default : self::$error = "Nenhum tipo de conexão definida."; throw new Exception("Nenhum tipo de conexão definida.");
            }
        }catch(Exception $e){
            self::$error = $e->getMessage();
            printr("QUERY ERROR : ".$e->getMessage());
            return false;
        }
    }

    static function results($sql)
    {
        $return = array();
        $query = query($sql);
        while( $fetch = self::fetch($query) ){ $return[sizeof($return)] = $fetch; }

        return $return;
    }

    static function db_quote($string)
    {
        try{
            switch(database::$conntype){
                case "pdo" :
                    // return  $_REQUEST['DEBUGSQL'][] = database::getInstance()->quote($string);
                    return  $_REQUEST['DEBUGSQL'][] = addslashes($string);
                break;
                case "mysql" : return $_REQUEST['DEBUGSQL'][] = mysql_real_escape_string($string); 
                break;
                case "postgresql" : return $_REQUEST['DEBUGSQL'][] = pg_escape_string($pg_resource); 
                break;
                default : self::$error = "Nenhum tipo de conexão definida."; throw new Exception("Nenhum tipo de conexão definida.");
            }
        }catch(Exception $e){
            self::$error = $e->getMessage();
            printr("QUERY ERROR (Quote) : ".$e->getMessage());
            return false;
        }
    }
}