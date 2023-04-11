$('.js-cor').hover(
    /*
    function() {
        var obj = $(this);
        if(!obj.find('img').data('selected')){
            obj.find('img').css('opacity',1).css('border','1px solid #fff');
        }
    }
    ,function() {
        var obj = $(this);
        if(!obj.find('img').data('selected') && $('[name="cor_id"]').val() == ''){
            obj.find('img').css('opacity',1).css('border','0px');
        }
        else if(!obj.find('img').data('selected')){
            obj.find('img').css('opacity',.6).css('border','0px');
        }
    }
    */
);

$('.js-cor').bind('click', function(){

    var obj = $(this);

    if(obj.find('img').data('selected')){
        $('[name="cor_id"]').val('');
        $('.filtro-cor li img').css('opacity',1).css('border','0px').data('selected', false);
    }
    else {
        $('[name="cor_id"]').val(obj.data('id'));
        $('.filtro-cor li img').css('opacity',.6).css('border','0px solid #fff').data('selected', false);
        obj.find('img').css('opacity',1).css('border','1px solid #fff').data('selected', true);
    }

    var opts = {
        url: index + 'filtrar'
        ,data: $('.js-filtro *').serialize()
        ,beforeSend: function(){
            $('.container-produtos').animate({opacity:.7},100)
        }
        ,success: function(out){
            // $('.container-produtos').hide().html(out).delay(300).slideDown().delay(100).animate({opacity:1},100)
            $('.container-produtos').html(out).animate({opacity:1},100);
            rebind_img_js_detalhe();
        }
    };

    $.ajax(opts);

    return false;

});

$('.js-select-cor').bind('change', function(){

    var obj = $(this);

    $('[name="cor_id"]').val(obj.val());

    /*
    if(obj.find('img').data('selected')){
        $('[name="cor_id"]').val('');
        $('.filtro-cor li img').css('opacity',1).css('border','0px').data('selected', false);
    }
    else {
        $('[name="cor_id"]').val(obj.data('id'));
        $('.filtro-cor li img').css('opacity',.6).css('border','0px solid #fff').data('selected', false);
        obj.find('img').css('opacity',1).css('border','1px solid #fff').data('selected', true);
    }
    */

    var opts = {
        url: index + 'filtrar'
        ,data: $('.js-filtro *').serialize()
        ,beforeSend: function(){
            $('.container-produtos').animate({opacity:.7},100)
        }
        ,success: function(out){
            // $('.container-produtos').hide().html(out).delay(300).slideDown().delay(100).animate({opacity:1},100)
            $('.container-produtos').html(out).animate({opacity:1},100);
            rebind_img_js_detalhe();
        }
    };

    $.ajax(opts);

    return false;

});
