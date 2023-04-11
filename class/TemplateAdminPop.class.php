<?php

class TemplateAdminPop extends Template {

	public function __construct($filename, $accurate = false){
		parent::__construct('tpl.base-admin-pop.html');
		$this->addFile('miolo',$filename);
	}
}
