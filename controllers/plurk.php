<?php
class Welcome extends Controller {

	function __construct()
	{
        parent::Controller();
        $this->load->library('plurk');	
	}
	
	function index()
	{ 
        $api_key = "xxxxxxx";
        $username = "xxxxxx";
        $password = "xxxxxx";
        $this->plurk->login($api_key, $username, $password);  
	}
}

/* End of file plurk.php */
/* Location: ./system/application/controllers/plurk.php */