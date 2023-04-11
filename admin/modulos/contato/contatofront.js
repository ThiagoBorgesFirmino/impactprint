$('#formContato').ajaxForm({
    dataType: 'json'
    ,type: 'POST'
    ,success: function(out){
        if(out.status){
            $('.js-contato-msg').html(out.msg).addClass('alert-success').fadeIn();
            $('.js-contato-btn').attr('disabled', true).val('OK, MENSAGEM ENVIADA');
        }
        else {
            $('.js-contato-msg').html(out.msg).addClass('alert-danger').fadeIn();
            $('.js-contato-btn').attr('disabled', false).val('ENVIAR');
        }
    }
    ,beforeSend: function(){
        $('.js-contato-btn').attr('disabled', true).val('AGUARDE...');
        $('.js-contato-msg').fadeOut().removeClass('alert-danger').removeClass('alert-success');
    }
    ,error: function(){
        $('.js-contato-msg').html('Falha ao enviar mensagem').addClass('alert-danger').fadeIn();
        $('.js-contato-btn').attr('disabled', false).val('ENVIAR');
    }
});

$('[name="contato[fone_com]"]').mask('?(99)9999-99999');