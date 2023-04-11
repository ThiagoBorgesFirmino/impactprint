<?php

class TemplateAdmin extends Template {

	public function __construct($filename, $accurate = false){
		parent::__construct('tpl.base-admin.html');
		$this->addFile('miolo',$filename);
	}
}

?>