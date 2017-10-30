<?php

class widgetLoginza extends cmsWidget {
    
    public $is_cacheable = false;
	
    public function run(){
		
		$template = cmsTemplate::getInstance();
		$template->addJS('http://loginza.ru/js/widget.js');
		
		$this->setWrapper('wrapper');

		$token_url  = urlencode('http://' . $_SERVER['HTTP_HOST'] . '/loginza/auth?group_id='.$this->getOption('group_id'));
		$providers = $this->getOption('provider');
		
        return array(
			'token_url' => $token_url,
			'providers' => $providers,
		);

    }
}