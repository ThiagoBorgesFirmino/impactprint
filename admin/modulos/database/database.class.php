<?php

    class database {

        public static $instance;
        public static $conntype;
        public static $dns;
        public static $user;
        public static $pass;
        public static $base;

        const MESSAGE_CON_ERRO = "<p style='text-align:center;padding:40px;color:#444;'>Não foi possível conectar ao banco de dados.<br> Verifique e tente novamente.</p>";

        private function __construct() {}

        public static function initPDO($db){
            self::$conntype = "pdo";
            self::$dns  = $db["conn"];
            self::$user = $db["user"];
            self::$pass = $db["password"];

            if (!isset(self::$instance)) {
                try{
                    self::$instance = new PDO(self::$dns,self::$user,self::$pass,array(PDO::MYSQL_ATTR_INIT_COMMAND => ($sql="SET NAMES utf8")));            
                    self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$instance->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);
                    $_REQUEST['DEBUGSQL'][] = $sql;
                }catch(PDOException $e){
                    echo self::MESSAGE_CON_ERRO;
                    die();
                    // printr($e->getMessage());
                }
            }
            
        }
        public static function initMySql($db){
            self::$conntype = "mysql";
            self::$dns  = $db["host"];
            self::$user = $db["user"];
            self::$pass = $db["password"];
            self::$base = $db["database"];

            if (!isset(self::$instance)) {
                try{
                    self::$instance = new mysqli(self::$dns,self::$user,self::$pass,self::$base);
                    if (!self::$instance->connect_errno){                       
                        query($sql='SET NAMES utf8');
                    }else{ throw new Exception(self::$instance->connect_error); }
                }catch(Exception $e){
                    echo self::MESSAGE_CON_ERRO;
                    die();
                    // printr($e->getMessage());
                }
            }
        }
        public static function initPG($db){
            self::$conntype = "postgresql";
            self::$dns  = $db["conn"];
            self::$user = $db["user"];
            self::$pass = $db["password"];
            self::$base = $db["database"];

            if (!isset(self::$instance)) {
                try{
                    $con_string = "host=".self::$dns." port=5432 dbname=".self::$base." user=".self::$user." password=".self::$pass."";
                    self::$instance = pg_connect($con_string);
                    $_REQUEST['DEBUGSQL'][] = self::$instance;           
                    query('SET NAMES utf8');
                }catch(Exception $e){
                    echo self::MESSAGE_CON_ERRO;
                    die();
                    // printr($e->getMessage());
                }
            }
        }
        public static function getInstance() {
            return self::$instance;
        }

    }