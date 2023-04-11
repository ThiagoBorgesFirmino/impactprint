<?php
	class itemxml extends base {
	
		var
			$id,
			$item_id,
			$anunciante;
			
		public function salva(){
			parent::salva();
		}
		
		public function limpa($anunciante){
			if($anunciante !=''){				
				query("DELETE FROM itemxml WHERE anunciante = '{$anunciante}'");
			}
		}
	}
?>