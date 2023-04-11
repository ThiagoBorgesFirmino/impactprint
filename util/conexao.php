<?php
include "admin/modulos/database/database.class.php";
database::initPDO( array("conn"=>"mysql:host=".BD_HOST.";dbname=".BD_DATABASE, "user"=>BD_USER, "password"=>BD_PASS) );
