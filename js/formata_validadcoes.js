$(document).ready(function(){
	$('.so_num').bind('keyup', function(){
		_val = $(this).val();						
		_num = _val.replace(/[a-z]/ig,'');
		this.value = _num;
	});
});