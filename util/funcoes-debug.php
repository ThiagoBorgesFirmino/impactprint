<?php
function debug_info($script_start){
    $script_end = microtime(true);

    if(DEBUG=='1' && isset($_REQUEST['DEBUGSQL']) && strtolower(@$_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest' ){

        print '<br clear="all"><br><pre>';

        print_r("\n".'Start '.($script_start));
        print_r("\n".'End '.($script_end));
        print_r("\n".'Tempo de execução '.($script_end-$script_start).' segundos');
        print_r("\n".'Total de consultas SQL: '.sizeof($_REQUEST['DEBUGSQL']));

        foreach($_REQUEST['DEBUGSQL'] as $i => $sql){
            print_r("\n".($i+1));
            print_r("\n".$sql);
            print_r("\n");
        }

        unset($_REQUEST['DEBUGSQL']);

        print_r("\n".'Session ('.sizeof($_SESSION).')');
        print_r($_SESSION);

        print_r("\n".'Request ('.sizeof($_REQUEST).')');
        print_r($_REQUEST);

        print_r("\n".'Files ('.sizeof($_FILES).')');
        print_r($_FILES);

        print '</pre>';
    }
}