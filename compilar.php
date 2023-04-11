<?php
// Compila CSS

// exec('lessc -x css/style.less css/site.min-'.date('ymdhis').'.css');

// Compila JS
$time = microtime();
error_reporting(E_ALL);

require 'util/php-html-css-js-minifier.php';

$js = array(
    'js/jquery.flexslider.js'
    ,'js/lib/jquery.lazyload.js'
    // ,'js/jquery-ui-1.11.2/jquery-ui.min.js'
    ,'js/jquery.form.js'
    ,'js/jquery.maskedinput.min.js'
    // ,'js/fancybox/jquery.mousewheel-3.0.6.pack.js'
    ,'js/fancybox/jquery.fancybox.js'
    ,'js/formata_validadcoes.js'
    // ,'js/fancybox/helpers/jquery.fancybox-buttons.js'
    // ,'js/fancybox/helpers/jquery.fancybox-thumbs.js'
    // ,'js/fancybox/helpers/jquery.fancybox-media.js'
    // ,'ts.pinterest.com/js/pinit.js'
    ,'js/geral.js'
    // ,'js/bootstrap.min.js'
    /*
    */
);

$tmp = '';
$jstmp = '';
foreach($js as $jsfile){
    $tmp = file_get_contents($jsfile);
    $jstmp .= fn_minify_js($tmp);
    $jstmp .= "\n";
}

// print $jstmp;

unlink('js/site.min.js');
file_put_contents('js/site.min.js',$jstmp);

print "Tempo: ". microtime()-$time.' Segundos';

$css = file_get_contents('css/site2.css');
$css = str_replace('../../img','../img',$css);
$css = fn_minify_css($css);
file_put_contents('css/site.min.css',$css);