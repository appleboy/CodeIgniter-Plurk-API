<?php

class Welcome extends Controller {

	function Welcome()
	{
        parent::Controller();
        $this->load->library('plurk');	
	}
	
	function index()
	{ 
        $api_key = "KckmBcIbpAwz2z2fpN3pg4fZZRjYrsuv";
        $username = "xxxxxx";
        $password = "xxxxxx";
        $this->plurk->login($api_key, $username, $password);  
        $this->load->view('welcome_message');
	}
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */