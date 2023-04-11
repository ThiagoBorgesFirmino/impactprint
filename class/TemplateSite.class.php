<?php

class TemplateSite extends Template {

	public function __construct($filename, $accurate = false){
		parent::__construct('tpl.base-index.html');
		$this->addFile('miolo',$filename);
	}
}

?>