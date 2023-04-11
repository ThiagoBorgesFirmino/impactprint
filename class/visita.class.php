<?php

class visita extends base {

	var
		$id
		,$categoria_id
		,$item_id
		,$cadastro_id
		,$ip
		,$sessao_id
		,$url
		,$url_from
		,$data_cadastro;

		static function clear(){
			query("DELETE FROM visita WHERE data_cadastro < CURDATE() - INTERVAL 10 DAY");
		}
}

?>