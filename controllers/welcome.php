<?php
class Welcome extends Controller {
	function __construct()
	{
    parent::Controller();
    $this->config->load('plurk_config');
    $this->load->library('plurk');
	}
	
	function index()
	{ 
    $api_key = $this->config->item('api_key');
    $username = $this->config->item('username');
    $password = $this->config->item('password');
    $this->plurk->login($api_key, $username, $password);  
    
    /**
     ******************************************
     * @Get plurks
     *
     * set plurk id = {123, 456, 789}
     ******************************************/
    
    echo "<h1>----- get plurks -----</h1>";
    echo "<pre>";
    print_r($this->plurk->get_plurks());
    echo "</pre>";
    
    /*
    echo "<h1> ----- get someone's plurk ----- </h1>";
    print_r($plurk->get_plurk(123));
    
    echo "<h1> ----- get unread plurks ----- </h1>";
    print_r($plurk->get_unread_plurks());
    
    echo "<h1> ----- mark plurk as read ----- </h1>";
    $plurk->mark_plurk_as_read(array(123,456,789));
    
    echo "<h1> ----- add plurk ----- </h1>";
    $plurk->add_plurk('en', 'says', 'Hello World');
    
    echo "<h1> ----- edit plurk ----- </h1>";
    $plurk->edit_plurk(123, 'be edited');
    
    echo "<h1> ----- delete plurk ----- </h1>";
    $plurk->delete_plurk(123);
    
    echo "<h1> ----- mute plurks ----- </h1>";
    print_r($plurk->mute_plurks(123));
    
    echo "<h1> ----- unmute plurks ----- </h1>";
    print_r($plurk->unmute_plurks(123));
    */
    
    /**
     ******************************************
     * @Get alerts
     *
     ******************************************/
    
    /*
    echo "<h1> ----- get active alerts ----- </h1>";
    print_r($plurk->get_active());
    
    echo "<h1> ----- get a list of past 30 alerts ----- </h1>";
    print_r($plurk->get_history());
    
    echo "<h1> ----- remove notification ----- </h1>";
    $plurk->remove_notification(123);
    */
    
    /**
     ******************************************
     * @Get plurk's responses
     *
     ******************************************/
    
    /*
    echo "<h1> ----- get responses ----- </h1>";
    echo "set plurk id = 123</h1>";
    print_r($plurk->get_responses(123));
    
    echo "<h1> ----- add response ----- </h1>";
    echo "set plurk id = 123</h1>";
    print_r($plurk->add_response(123, 'test response', 'says'));
    
    echo "<h1> ----- delete response ----- </h1>";
    echo "set plurk id = 123, response id = 456</h1>";
    $plurk->delete_response(123, 456);
    */
    
    /**
     ******************************************
     * @Control user 
     *
     ******************************************/
    
    /*
    echo "<h1> ----- get own profile ----- </h1>";
    print_r($plurk->get_own_profile());
    
    echo "<h1> ----- get user public profile ----- </h1>";
    echo "set user id = 123</h1>";
    print_r($plurk->get_public_profile(123));
    
    echo "<h1> ----- get user info ----- </h1>";
    print_r($plurk->get_user_info());
    
    echo "<h1> ------ get users friends (nick name and full name)</h1>";
    print_r($plurk->get_completion());
    
    echo "<h1> ----- get block user's list ----- </h1>"; 
    print_r($plurk->get_blocks());
    
    echo "<h1> ----- block user ----- </h1>"; 
    $plurk->block_user(5366984);
    
    echo "<h1> ----- unblock user ----- </h1>"; 
    $plurk->unblock_user(5366984);
    */
    
    /**
     ******************************************
     * @Control friends 
     *
     * set user id = 123
     * set friend id = 789
     ******************************************/
    
    /*
    echo "<h1> ----- get someone's friends ----- </h1>";
    print_r($plurk->get_friends(123));
    
    echo "<h1> ----- become someone's friend ----- </h1>";
    $plurk->become_friend(789);
    
    echo "<h1> ----- remove friend ----- </h1>";
    $plurk->remove_friend(789);
    
    echo "<h1> ----- accept friendship request as friend ----- </h1>";
    $plurk->add_as_friend(789);
    
    echo "<h1> ----- accept all friendship requests as friends ----- </h1>";
    $plurk->add_all_as_friends();
    
    echo "<h1> ----- deny friendship ----- </h1>";
    $plurk->deny_friendship(789);
    */
    
    /*
     ******************************************
     * @Control fans
     *
     * set user id = 123
     * set fan id = 789
     ******************************************/
     
    /*
    echo "<h1> ----- get following ----- </h1>";
    print_r($plurk->get_following());
    
    echo "<h1> ----- get someone's fans ----- </h1>";
    print_r($plurk->get_fans(123));
    
    echo "<h1> ----- become someone's fan ----- </h1>";
    $plurk->become_fan(5366983);
    
    echo "<h1> ----- accept a friendship request as fan ----- </h1>";
    plurk->add_as_fan(789);
    
    echo "<h1> ----- accept all friendship requests as fans ----- </h1>";
    $plurk->add_all_as_fan();
    */
    
    /* can't use */
    //echo "<h1> ----- set user following ----- </h1>"; 
    //echo "user id = 789</h1>";
    //echo ($plurk->set_following(3440147, $follow = FALSE)) ? 'success' : 'disable';
    
    
    /*
     ******************************************
     * @Search 
     *
     ******************************************/
    
    /*
    echo "<h1> ----- search plurk ----- </h1>"; 
    print_r($plurk->search_plurk('php-plurk-api'));
    
    echo "<h1> ----- search user ----- </h1>"; 
    print_r($plurk->search_user('roga lin'));
    
    echo "<h1> ----- get emoticons ----- </h1>"; 
    print_r($plurk->get_emoticons());
    */
    
    
    /*
     ******************************************
     * @Clique
     *
     ******************************************/
    
    /*
    echo "<h1> ----- get clique list ----- </h1>"; 
    print_r($plurk->get_cliques());
    
    echo "<h1> ----- create a clique ----- </h1>"; 
    print_r($plurk->create_clique("test"));
    
    echo "<h1> ----- rename clique ----- </h1>"; 
    print_r($plurk->rename_clique("test","test1"));
    
    echo "<h1> ----- get clique ----- </h1>"; 
    print_r($plurk->get_clique('test1'));
    
    echo "<h1> ----- add a user to a clique ----- </h1>"; 
    print_r($plurk->add_to_clique("test1", 3440147));
    
    echo "<h1> ----- remove a user from a clique ----- </h1>"; 
    print_r($plurk->remove_from_clique("test1", 3440147));
    
    echo "<h1> ----- delete a clique ----- </h1>"; 
    print_r($plurk->delete_clique("test1"));  
    */      
	}
}
/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */