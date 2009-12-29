<?php

/**
 * load dependencies.
 */
@include(APPPATH.'config/plurk_config'.EXT);
@include(APPPATH.'config/plurk_constant'.EXT);
@include(APPPATH.'libraries/Common'.EXT);

/**
 * This is a PHP Plurk API.
 *
 * @category  API
 * @version   php-plurk-api 1.4 Beta
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link      http://code.google.com/p/php-plurk-api
 *
 */
Class Plurk Extends Common {

    /**
     * User name
     * @var string $username
     */
    protected $username;

    /**
     * Password
     * @var string $password
     */
    protected $password;

    /**
     * API KEY
     * @var $api_key
     */
    protected $api_key;

    /**
     * Login status
     * @var bool $is_login
     */
    protected $is_login = FALSE;

    /**
     * Current HTTP Status Code
     * @var int $http_status
     */
    protected $http_status;

    /**
     * Current HTTP Server Response
     * @var JSON object $http_response
     */
    protected $http_response;

    /**
     * User infomation
     * @var JSON object $user_info
     */
    protected $user_info;

    /**
     * The unique user id.
     * @var int $uid
     */
    protected $uid;

    /**
     * The unique nick_name of the user, for example amix.
     * @var string $nick_name
     */
    protected $nick_name;

    /**
     * The non-unique display name of the user, for example Amir S. Only set if it's non empty.
     * @var string $display_name
     */
    protected $display_name;
    /**
     * If 1 then the user has a profile picture, otherwise the user should use the default.
     * @var int $has_profile_image
     */
    protected $has_profile_image;

    /**
     * Specifies what the latest avatar (profile picture) version is.
     * @var string $avatar
     */
    protected $avatar;

    /**
     * The user's location, a text string, for example Aarhus Denmark.
     * @var string $location
     */
    protected $location;

    /**
     * date_of_birth: The user's birthday.
     * @var string $date_of_birth
     */
    protected $date_of_birth;

    /**
     * The user's full name, like Amir Salihefendic.
     * @var string $full_name
     */
    protected $full_name;

    /**
     * 1 is male, 0 is female.
     * @var int $gender;
     */
    protected $gender;

    /**
     * The profile title of the user.
     * @var string $page_title
     */
    protected $page_title;

    /**
     * User's karma value.
     * @var int $karma
     */
    protected $karma;

    /**
     * How many friends has the user recruited.
     * @var int $recruited;
     */
    protected $recruited;

    /**
     * Can be not_saying, single, married, divorced, engaged, in_relationship, complicated, widowed, open_relationship
     * @var string $relationship
     */
    protected $relationship;

    /**
     * fans count
     * @var int $fans_count
     */
    protected $fans_count;

    /**
     * alert count
     * @var int $alerts_count
     */
    protected $alerts_count;

    /**
     * friends count
     * @var int $friends_count
     */
    protected $friends_count;

    /**
     * Plurk Privacy
     * @var boolean $privacy
     */
    protected $privacy;



    function __construct() {}

    /**
     * function plurk
     * Connect to Plurk
     *
     * @param $url
     * @param $array
     * @return JSON object
     */
    function plurk($url, $array)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS , http_build_query($array));
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        
        curl_setopt($ch, CURLOPT_USERAGENT, PLURK_AGENT);

        curl_setopt($ch, CURLOPT_COOKIEFILE, PLURK_COOKIE_PATH);
        curl_setopt($ch, CURLOPT_COOKIEJAR, PLURK_COOKIE_PATH);

        $response = curl_exec($ch);
        
        $this->http_response = $response;
        $this->http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return json_decode($response);
    }

    /**
     * function register
     * Register a new Plurk account. Should be HTTPS
     *
     * @param string $nick_name The user's nick name. Should be longer than 3 characters. Should be ASCII. Nick name can only contain letters, numbers and _.
     * @param string $full_name Can't be empty.
     * @param string $password Should be longer than 3 characters.
     * @param string $gender Should be male or female.
     * @param string $date_of_birth Should be YYYY-MM-DD, example 1985-05-13.
     * $param string $email (Optional) Must be a valid email.
     * @return JSON object
     * @see /API/Users/register
     */
    function register($nick_name = '', $full_name = '', $password = '', $gender = 'male', $date_of_birth = '0000-00-00', $email = NULL)
    {

        if(strlen($nick_name) < 4)
            $this->log('nick name should be longer than 3 characters.');

        if ( ! preg_match('/^[\w_]+$/', $str))
            $this->log('nick name should be ASCII, numbers and _.');

        if($full_name == "")
            $this->log('full name can not be empty.');

        if(strlen($password) < 4)
            $this->log('password should be longer than 3 characters.');

        $gender = strtolower($gender);

        if($gender != 'male' && $gender != 'female')
            $this->log('should be male or female.');

        if ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email))
            $this->log('must be a valid email.');

        $array = array(
            'api_key'       => $this->api_key,
            'nick_name'     => $nick_name,
            'full_name'     => $full_name,
            'password'      => $password,
            'gender'        => $gender,
            'date_of_birth' => $date_of_birth
        );

        if(isset($email)) $array['email'] = $email;

        return $this->plurk(PLURK_REGISTER, $array);
    }

    /**
     * function login
     * Login an already created user. Login creates a session cookie, which can be used to access the other methods.
     *
     * @param $username The user's nick name or email.
     * @param $password The user's password.
     * @param $api_key Your Plurk API key.
     * @return boolean
     * @see /API/Users/login
     */
    function login($api_key = '', $username = '', $password = '')
    {

        $array = array(
            'username' => $username,
            'password' => $password,
            'api_key'  => $api_key,
        );

        $result = $this->plurk(PLURK_LOGIN, $array);

        ($this->http_status == '200') ? $this->is_login = TRUE : $this->is_login = FALSE;

        if($this->is_login)
        {
            $this->log('Login Success');

            $this->username = $username;
            $this->password = $password;
            $this->api_key = $api_key;
            $this->user_info = $result;
            $this->fans_count = $result->fans_count;
            $this->alerts_count = $result->alerts_count;
            $this->friends_count = $result->friends_count;
            $this->privacy = $result->privacy;
        }
        else
        {
            $this->log('Login Failed!');

            exit('Please Login Again');
        }

        return $this->is_login;

    }

    /**
     * function update_picture
     * pdate a user's profile picture. You can read more about how to render an avatar via user data.
     *
     * @param string $profile_image The new profile image.
     * @return boolean
     * @see /API/Users/updatePicture
     */
    function update_picture($profile_image = '')
    {
        //  RFC 1867
        
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);
                   
        $array['api_key'] = $this->api_key;
        $array['profile_image'] = "@" . $upload_image;
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, PLURK_UPDATE_PICTURE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $array);
          
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        
        curl_setopt($ch, CURLOPT_USERAGENT, PLURK_AGENT);

        curl_setopt($ch, CURLOPT_COOKIEFILE, PLURK_COOKIE_PATH);
        curl_setopt($ch, CURLOPT_COOKIEJAR, PLURK_COOKIE_PATH);
        
        $result = curl_exec($ch);

        $this->http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->http_response = $result;        

        return json_decode($result);
        
    }

    /**
     * function update
     * Update a user's information (such as email, password or privacy). Should be HTTPS
     *
     * @param string $current_password User's current password.
     * @param string $full_name Change full name.
     * @param string $new_password Change password.
     * @param string $email Change email.
     * @param string $display_name User's display name, can be empty and full unicode. Must be shorter than 15 characters.
     * @param string $privacy User's privacy settings. The option can be world (whole world can view the profile), only_friends (only friends can view the profile) or only_me (only the user can view own plurks).
     * @param string $date_of_birth Should be YYYY-MM-DD, example 1985-05-13.
     * @return boolean
     * @see /API/Users/update
     */
    function update($current_password = NULL, $full_name = NULL, $new_password = NULL, $email = NULL, $display_name = NULL, $privacy = NULL, $date_of_birth = NULL)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        if($full_name == "")
            $this->log('full name can not be empty.');

        if(strlen($current_password) < 4)
            $this->log('password should be longer than 3 characters.');

        if($full_name == "")
            $this->log('full name can not be empty.');

        if ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email))
            $this->log('must be a valid email.');

        if(strlen($display_name) < 16)
            $this->log('Display name must be shorter than 15 characters.');

        $array = array(
            'api_key'          => $this->api_key,
            'current_password' => $current_password,
        );

        if(isset($full_name)) $array['full_name'] = $full_name;
        if(isset($new_password)) $array['new_password'] = $new_password;
        if(isset($display_name)) $array['display_name'] = $display_name;
        if(isset($email)) $array['email'] = $email;
        if(isset($privacy)) $array['prvacy'] = $privacy;
        if(isset($date_of_birth)) $array['date_of_birth'] = $date_of_birth;

        $this->plurk(PLURK_UPDATE, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;

    }

    /**
     * function get_plurks_polling
     *
     * @param time $offset Return plurks newer than offset, use timestamp.
     * @param int $limit The max number of plurks to be returned (default 50). 
     * @return JSON object
     * @see /API/Polling/getPlurks
     */
    function get_plurks_polling($offset = NULL, $limit = 50)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $offset = (isset($offset)) ? $offset : array_shift(explode("+", date("c", $offset)));

        $array = array(
            'api_key' => $this->api_key,
            'offset'  => $offset,
            'limit'   => $limit,
        );

        return $this->plurk(PLURK_POLLING_GET_PLURK, $array);
    }

    /**
     * function get_plurks
     *
     * @param int $plurk_id The unique id of the plurk. Should be passed as a number, and not base 36 encoded.
     * @return JSON object
     * @see /API/Timeline/getPlurk
     */
    function get_plurk($plurk_id = 0)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key'   => $this->api_key,
            'plurk_id'  => $plurk_id,
        );

        return $this->plurk(PLURK_TIMELINE_GET_PLURK, $array);
    }

    /**
     * function get_plurks
     *
     * @param time $offset Return plurks older than offset, use timestamp.
     * @param int $limit How many plurks should be returned? Default is 20.
     * @param int $only_user The numeric ID of the user who's plurks should be returned.
     * @param boolean $only_responded Setting it to true will only return responded plurks.
     * @param boolean $only_private Setting it to true will only return private plurks.
     * @return JSON object
     * @see /API/Timeline/getPlurks
     */
    function get_plurks($offset = 0, $limit = 20, $only_user = NULL, $only_responded = NULL, $only_private = NULL)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key'       => $this->api_key,
            'offset'        => $offset,
            'limit'         => $limit
        );

        if(isset($only_user)) $array['only_user'] = $only_user;
        if(isset($only_responded)) $array['only_responded'] = $only_responded;
        if(isset($only_private)) $array['only_private'] = $only_private;

        return $this->plurk(PLURK_TIMELINE_GET_PLURKS, $array);
    }

    /**
     * function getPlurks
     * Compatible with RLPlurkAPI
     * Gets the plurks for the user. Only 25 plurks are fetch at a time as this
     * is limited on the server.
     * The array returned is ordered most recent post first followed by
     * previous posts.
     *
     * @param int    $int_uid          The UID to fetch plurks for.
     * @param string $date_from        The date/time to start fetching plurks. This must be in the <yyyy-mm-dd>T<hh:mm:ss> format assumed to be UTC time.
     * @param string $date_offset      The date/time offset that fetches plurks earlier than this offset. The format is the same as $date_from.
     * @param bool   $fetch_responses  If true, populate the responses_fetch value with the array of responses.
     * @param bool   $self_plurks_only If true, return only self plurks.
     * @return array The array (numerical) of plurks (an associative subarray).
     * @todo Should rewrite.
     */
    function getPlurks($int_uid = null, $date_from = null, $date_offset = null,
        $fetch_responses = false, $self_plurks_only = false)
    {
        printf("getPlurks: This function is not implemented yet.\n");
    }

    /**
     * function get_unread_plurks
     *
     * @param time $offset Return plurks older than offset, use timestamp
     * @param int $limit Limit the number of plurks that is retunred.
     * @return JSON object
     * @see /API/Timeline/getUnreadPlurks
     */
    function get_unread_plurks($offset = null ,$limit = 10)
    {
        // $offset seens it's not working now. by whatup.tw
        if( ! isset($offset)) $offset = time();

        $date = array_shift(explode("+", date("c", $offset)));

        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key'   => $this->api_key,
            'offset'    => $date,
            'limit'     => $limit
        );

        $result = $this->plurk(PLURK_TIMELINE_GET_UNREAD_PLURKS, $array);
        return $result;
    }

    /**
     * function getUnreadPlurks
     * Compatible with RLPlurkAPI
     * Get the unread plurks.
     *
     * @param bool $fetch_responses If true, populate the responses_fetch value with the array of responses.
     * @return array The array (numerical) of unread plurks (an associative subarray).
     * @todo $fetch_responses not implemented
     */
    function getUnreadPlurks($fetch_responses = false)
    {
        return $this-> get_unread_plurks(null, 10);
    }

    /**
     * function mute_plurks
     *
     * @param $ids The plurk ids, eg. array(123,456,789)
     * @return boolean
     * @see /API/Timeline/mutePlurks
     */
    function mute_plurks($ids)
    {

        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key'    => $this->api_key,
            'ids'        => json_encode($ids),
        );

        $this->plurk(PLURK_TIMELINE_MUTE_PLURKS, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;

    }

    /**
     * function unmute_plurks
     *
     * @param $ids The plurk ids, eg. array(123,456,789)
     * @return boolean
     * @see /API/Timeline/unmutePlurks
     */
    function unmute_plurks($ids)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key'    => $this->api_key,
            'ids'        => json_encode($ids),
        );

        $result = $this->plurk(PLURK_TIMELINE_UNMUTE_PLURKS, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;
    }

    /**
     * function mutePlurk
     * Compatible with RLPlurkAPI
     * Mute or unmute plurks
     *
     * @param array $int_plurk_id The plurk id to be muted/unmuted.
     * @param bool  $bool_setmute If true, this plurk is to be muted, else,
     *                            unmute it.
     *
     * @return bool Returns true if successful or false otherwise.
     */
    function mutePlurk($int_plurk_id, $bool_setmute)
    {

        if (!is_int($int_plurk_id) || ! is_bool($bool_setmute))
        {
            return false;
        }

        if ($bool_setmute == true)
        {
            return mute_plurks(array($int_plurk_id));
        }
        else if ($bool_setmute == false)
        {
            return unmute_plurks(array($int_plurk_id));
        }

        return false;
    }

    /**
     * function mark_plurk_as_read
     *
     * @param $ids The plurk ids, eg. array(123,456,789)
     * @return boolean
     * @see /API/Timeline/markAsRead
     */
    function mark_plurk_as_read($ids)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key'    => $this->api_key,
            'ids'        => json_encode($ids),
        );

        $this->plurk(PLURK_TIMELINE_MARK_AS_READ, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;
    }

    /**
     * function add_Plurk
     *
     * @param string $lang The plurk's language.
     * @param string $qualifier The Plurk's qualifier, must be in English. please see documents/README
     * @param string $content The Plurk's text.
     * @param $limited_to Limit the plurk only to some users (also known as private plurking). limited_to should be a Array list of friend ids, e.g. limited_to = array(3,4,66,34) will only be plurked to these user ids.
     * @param string $lang The plurk's language.
     * @param int $no_commetns If set to 1, then responses are disabled for this plurk. If set to 2, then only friends can respond to this plurk.
     * @return boolean
     * @see /API/Timeline/plurkAdd
     */
    function add_plurk($lang = 'en', $qualifier = 'says', $content = 'test from roga-plurk-api', $limited_to = NULL, $no_comments = 0)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        if (mb_strlen($content) > 140)
        {
            $this->log('this message should shorter than 140 characters.');
        }

        $array = array(
            'api_key'     => $this->api_key,
            'qualifier'   => 'likes',
            'content'     => $content,
            'lang'        => $lang,
            'no_comments' => $no_comments
        );

        // roga.2009-12-14: need to confirm.
        if (isset($limited_to)) $array['limited_to'] = json_encode($limited_to);

        return $this->plurk(PLURK_TIMELINE_PLURK_ADD, $array);

    }

    /**
     * function addPlurk
     * Compatible with RLPlurkAPI
     *
     * @return boolean
     */
    function addPlurk($lang = 'en', $qualifier = 'says', $content = 'test from roga-plurk-api', $limited_to = NULL, $no_comments = 0)
    {
        return $this->add_plurk($lang, $qualifier, $content, $limited_to, $no_comments);
    }

    /**
     * function upload_picture
     * to upload a picture to Plurk, you should do a multipart/form-data POST request
     * to /API/Timeline/uploadPicture. This will add the picture to Plurk's CDN network
     * and return a image link that you can add to /API/Timeline/plurkAdd
     *
     * @param string $upload_image
     * @return JSON object
     * @see /API/Timeline/uploadPicture
     */
    function upload_picture($upload_image = '')
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);
                   
        $array['api_key'] = $this->api_key;
        $array['image'] = "@" . $upload_image;
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, PLURK_TIMELINE_UPLOAD_PICTURE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $array);
          
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        
        curl_setopt($ch, CURLOPT_USERAGENT, PLURK_AGENT);

        curl_setopt($ch, CURLOPT_COOKIEFILE, PLURK_COOKIE_PATH);
        curl_setopt($ch, CURLOPT_COOKIEJAR, PLURK_COOKIE_PATH);
        
        $result = curl_exec($ch);

        $this->http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->http_response = $result;
        
        return json_decode($result);
    }

    /**
     * function delete_plurk
     *
     * @param int $plurk_id: The id of the plurk.
     * @return boolean
     * @see /API/Timeline/plurkDelete
     */
    function delete_plurk($plurk_id = 0)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key'  => $this->api_key,
            'plurk_id' => $plurk_id
        );

        $result = $this->plurk(PLURK_TIMELINE_PLURK_DELETE, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;
    }

    /**
     * function deletePlurk
     * Compatible with RLPlurkAPI
     * delete a plurk
     *
     * @param array $int_plurk_id The plurk id to be deleted.
     * @return bool Returns true if successful or false otherwise.
     */
    function deletePlurk($int_plurk_id)
    {
        return delete_plurk($int_plurk_id);
    }

    /**
     * function edit_plurk
     *
     * @param int $plurk_id The id of the plurk.
     * @param string $content The content of plurk.
     * @return boolean
     * @see /API/Timeline/plurkEdit
     */
    function edit_plurk($plurk_id = 0, $content = '')
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        if (mb_strlen($content) > 140)
        {
            $this->log('this message should shorter than 140 characters.');
        }

        $array = array(
            'api_key'  => $this->api_key,
            'plurk_id' => $plurk_id,
            'content'  => urlencode($content)
        );

        $result = $this->plurk(PLURK_TIMELINE_PLURK_EDIT, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;
    }

    /**
     * function get_responses
     *
     * @param int $plurk_id: The plurk that the responses should be added to.
     * @param int $offset: Only fetch responses from an offset, should be 5, 10 or 15.
     * @return JSON object
     * @see /API/Responses/get
     */
    function get_responses($plurk_id = 0, $offset = 0)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key' => $this->api_key,
            'plurk_id' => $plurk_id,
            'offset'  => $offset
        );

        return $this->plurk(PLURK_GET_RESPONSE, $array);
    }

    /**
     * function getResponses
     * Compatible with RLPlurkAPI
     * Get the responses of a plurk. This method will load "temporary" friends who have responded to the plurk.
     *
     * @param int $int_plurk_id The plurk ID
     * @return array The array of responses.
     */
    function getResponses($int_plurk_id)
    {
        return $this->get_responses($int_plurk_id, 0);
    }

    /**
     * function add_response
     *
     * @param int $plurk_id The plurk that the responses should be added to.
     * @param string $content The response's text.
     * @param string $qualifier The Plurk's qualifier, please see documents/README
     * @return boolean
     * @see /API/Responses/responseAdd
     */
    function add_response($plurk_id = 0, $content = '', $qualifier = 'says')
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        if (mb_strlen($content) > 140)
        {
            $this->log('this message should shorter than 140 characters.');
        }

        $array = array(
            'api_key'   => $this->api_key,
            'plurk_id'  => $plurk_id,
            'content'   => $content,
            'qualifier' => $qualifier
        );

        $result = $this->plurk(PLURK_ADD_RESPONSE, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;
    }

    /**
     * function respondToPlurk
     * Compatible with RLPlurkAPI
     * Respond to a plurk.
     *
     * @param int    $int_plurk_id     The plurk ID number to respond to.
     * @param string $string_lang      The plurk language.
     * @param string $string_qualifier The qualifier to use for this response.
     * @param string $string_content   The content to be posted as a reply.
     *
     * @return mixed false on failure, otherwise the http response from plurk.
     */
    function respondToPlurk($int_plurk_id = 0, $string_lang = 'en', $string_qualifie = 'says', $string_content = 'test from roga-plurk-api')
    {
        return $this->add_response($int_plurk_id, $string_content, $string_qualifie);
    }

    /**
     * function delete_response
     *
     * @param int $response_id The plurk that the responses should be added to.
     * @param int $plurk_id The plurk that the response belongs to.
     * @return boolean
     * @see /API/Responses/responseDelete
     */
    function delete_response($plurk_id = 0, $response_id = 0)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key'     => $this->api_key,
            'plurk_id'    => $plurk_id,
            'response_id' => $response_id
        );

        $result = $this->plurk(PLURK_DELERE_RESPONSE, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;
    }

    /**
     * function get_own_profile
     *
     * @return JSON object
     * @see /API/Profile/getOwnProfile
     */
    function get_own_profile()
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array('api_key' => $this->api_key);

        return $this->plurk(PLURK_GET_OWN_PROFILE, $array);
    }

    /**
     * function get_public_profile
     *
     * @param int $user_id The user_id of the public profile. Can be integer (like 34) or nick name (like amix).
     * @return JSON object
     * @see /API/Profile/getPublicProfile
     */
    function get_public_profile($user_id = 0)
    {

        $array = array(
            'api_key' => $this->api_key,
            'user_id' => $user_id
        );

        return $this->plurk(PLURK_GET_PUBLIC_PROFILE, $array);
    }

    /**
     * function get_friends
     *
     * @param int|string $user_id The user_id of the public profile. Can be integer (like 34) or nick name (like amix).
     * @param int $offset The offset, can be 10, 20, 30 etc.
     * @return JSON objects
     * @see /API/FriendsFans/getFriendsByOffset
     */
    function get_friends($user_id = 0, $offset = 0)
    {

        $array = array(
            'api_key' => $this->api_key,
            'user_id' => $user_id,
            'offset'  => $offset
        );

        return $this->plurk(PLURK_GET_FRIENDS, $array);
    }

    /**
     * function get_fans
     *
     * @param int|string $user_id The user_id of the public profile. Can be integer (like 34) or nick name (like amix).
     * @param int $offset The offset, can be 10, 20, 30 etc.
     * @return JSON object
     * @see /API/FriendsFans/getFansByOffset
     */
    function get_fans($user_id = 0, $offset = 0)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key' => $this->api_key,
            'user_id' => $user_id,
            'offset'  => $offset
        );

        return $this->plurk(PLURK_GET_FANS, $array);
    }

    /**
     * function get_following
     *
     * @param int $offset The offset, can be 10, 20, 30 etc.
     * @return JSON object
     * @see /API/FriendsFans/getFollowingByOffset
     */
    function get_following($offset = 0)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key' => $this->api_key,
            'offset'  => $offset
        );

        return $this->plurk(PLURK_GET_FOLLOWING, $array);
    }

    /**
     * function become_friend
     *
     * @param int $friend_id The ID of the user you want to befriend.
     * @return boolean
     * @see /API/FriendsFans/becomeFriend
     */
    function become_friend($friend_id = 0)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key'   => $this->api_key,
            'friend_id' => $friend_id
        );

        $result =  $this->plurk(PLURK_BECOME_FRIEND, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;
    }

    /**
     * function remove_Friend
     *
     * @param int $friend_id The ID of the user you want to befriend.
     * @return boolean
     * @see /API/FriendsFans/removeAsFriend
     */
    function remove_Friend($friend_id = 0)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key'   => $this->api_key,
            'friend_id' => $friend_id
        );

        $result =  $this->plurk(PLURK_REMOVE_FRIEND, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;
    }

    /**
     * function become_fan
     *
     * @param int $fan_id Become fan of fan_id. To stop being a fan of someone, user /API/FriendsFans/setFollowing?fan_id=FAN_ID&follow=false.
     * @return boolean
     * @see /API/FriendsFans/becomeFan
     */
    function become_fan($fan_id = 0)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key' => $this->api_key,
            'fan_id'  => $fan_id
        );

        $result =  $this->plurk(PLURK_BECOME_FAN, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;
    }

    /**
     * function set_following
     * Update following of user_id. A user can befriend someone, but can unfollow them. This request is also used to stop following someone as a fan.
     *
     * @param int $user_id The ID of the user you want to follow/unfollow
     * @param boolean $follow true if the user should be followed, and false if the user should be unfollowed.
     * @return boolean
     * @see /API/FriendsFans/setFollowing
     */
    function set_following($user_id = 0, $follow = false)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key' => $this->api_key,
            'user_id' => $user_id,
            'follow'  => $follow
        );

        $result =  $this->plurk(PLURK_SET_FOLLOWING, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;
    }

    /**
     * function get_completion
     * Returns a JSON object of the logged in users friends (nick name and full name).
     *
     * @return JSON object
     * @see /API/FriendsFans/getCompletion
     */
    function get_completion()
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array('api_key' => $this->api_key);

        return $this->plurk(PLURK_GET_COMPLETION, $array);
    }

    /**
     * function get_active
     * Return a JSON list of current active alert
     *
     * @return JSON object
     * @see /API/Alerts/getActive
     */
    function get_active()
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array('api_key' => $this->api_key);

        return $this->plurk(PLURK_GET_ACTIVE, $array);
    }

    /**
     * function getAlerts
     * Compatible with RLPlurkAPI
     *
     * @return JSON object
     */
    function getAlerts()
    {
        return $this->get_active();
    }

    /**
     * function get_history
     * Return a JSON list of past 30 alerts.
     *
     * @param
     * @return JSON object
     * @see /API/Alerts/getHistory
     */
    function get_history()
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array('api_key' => $this->api_key);

        return $this->plurk(PLURK_GET_HISTORY, $array);
    }

    /**
     * function add_as_fan
     * Accept user_id as fan.
     *
     * @param int $user_id The user_id that has asked for friendship.
     * @return Boolean
     * @see /API/Alerts/addAsFan
     */
    function add_as_fan($user_id = 0)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key' => $this->api_key,
            'user_id' => $user_id
        );

        $result = $this->plurk(PLURK_ADD_AS_FAN, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;
    }

    /**
     * function add_as_friend
     * Accept user_id as friend.
     *
     * @param int $user_id The user_id that has asked for friendship.
     * @return Boolean
     * @see /API/Alerts/addAsFriend
     */
    function add_as_friend($user_id = 0)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key' => $this->api_key,
            'user_id' => $user_id
        );

        $result = $this->plurk(PLURK_ADD_AS_FRIEND, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;
    }

    /**
     * function add_all_as_fan
     * Accept all friendship requests as fans.
     *
     * @return Boolean
     * @see /API/Alerts/addAllAsFan
     */
    function add_all_as_fan()
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array('api_key' => $this->api_key);

        $result = $this->plurk(PLURK_ADD_ALL_AS_FAN, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;
    }


    /**
     * function add_all_as_friends
     * Accept all friendship requests as friends.
     *
     * @return boolean
     * @see /API/Alerts/addAllAsFriends
     */
    function add_all_as_friends()
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array('api_key' => $this->api_key);

        $result = $this->plurk(PLURK_ADD_ALL_AS_FRIEND, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;
    }

    /**
     * function deny_friendship
     * Deny friendship to user_id.
     *
     * @param int $user_id The user_id that has asked for friendship.
     * @return Boolean
     * @see /API/Alerts/denyFriendship
     */
    function deny_friendship($user_id = 0)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key' => $this->api_key,
            'user_id' => $user_id
        );

        $result = $this->plurk(PLURK_DENY_FRIEND, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;
    }

    /**
     * function befriend
     * Compatible with RLPlurkAPI
     *
     * @param array $array_uid The array of firend uids.
     * @todo should modify in a better way
     */
    function befriend($array_uid = null, $bool_befriend = false)
    {
        $return = true;

        if ($bool_befriend == false)
        {
            foreach($array_uid as $friend_id)
            {
                $return = ($return && $this->deny_friendship($friend_id));
            }
        }
        else if ($bool_befriend == true)
        {
            foreach($array_uid as $friend_id)
            {
                $return = ($return && $this->add_as_friend($friend_id));
            }
        }

        return $return;
    }

    /**
     * function denyFriendMakeFan
     * Compatible with RLPlurkAPI
     *
     * @param array $array_uid The array of friend requests uids.
     * @todo should modify in a better way
     */
    function denyFriendMakeFan($array_uid = null)
    {
        $return = true;

        if (!is_array($array_uid))
        {
            return false;
        }

         foreach ($array_uid as $friend_id)
         {
             $return = ($return && $this->add_as_fan($friend_id));
         }

        return $return;
    }

    /**
     * function remove_notification
     * Remove notification to user with id user_id.
     *
     * @param int $user_id The user_id that the current user has requested friendship for.
     * @return Boolean
     * @see /API/Alerts/removeNotification
     */
    function remove_notification($user_id = 0)
    {

        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key' => $this->api_key,
            'user_id' => $user_id
        );

        $result = $this->plurk(PLURK_REMOVE_NOTIFY, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;
    }

    /**
     * function search_plurk
     * Returns the latest 20 plurks on a search term.
     *
     * @param string $query The query after Plurks.
     * @param int $offset A plurk_id of the oldest Plurk in the last search result.
     * @return JSON object
     * @see /API/PlurkSearch/search
     */
    function search_plurk($query = '', $offset = 0)
    {

        /* offset: A plurk_id of the oldest Plurk in the last search result.  */

        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key' => $this->api_key,
            'query'   => $query,
            'offset'  => $offset
        ) ;

        return $this->plurk(PLURK_SEARCH, $array);
    }

    /**
     * function search_user
     * Returns 10 users that match query, users are sorted by karma.
     *
     * @param string $query The query after users.
     * @param int $offset Page offset, like 10, 20, 30 etc.
     * @return JSON object
     * @see /API/UserSearch/search
     */
    function search_user($query = '', $offset = 0)
    {
        /* offset: Page offset, like 10, 20, 30 etc. */

        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key' => $this->api_key,
            'query'   => $query,
            'offset'  => $offset
        ) ;

        return $this->plurk(PLURK_USER_SEARCH, $array);
    }

    /**
     * function get_emoticons
     * Emoticons are a big part of Plurk since they make it easy to express feelings.
     * <a href="http://www.plurk.com/Help/extraSmilies">Check out current Plurk emoticons.</a> This call returns a JSON object that looks like:
     * $link http://www.plurk.com/Help/extraSmilies Check out current Plurk emoticons.
     *
     * @return JSON object
     * @see /API/Emoticons/get
     */
    function get_emoticons()
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array('api_key' => $this->api_key);

        $result = $this->plurk(PLURK_GET_EMOTIONS, $array);

        return $result;
    }

    /**
     * function get_blocks
     *
     * @param int $offset What page should be shown, e.g. 0, 10, 20.
     * @return JSON list
     * @see /API/Blocks/get
     */
    function get_blocks($offset = 0)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
          'api_key' => $this->api_key,
          'offset'  => $offset,
        );

        return $this->plurk(PLURK_GET_BLOCKS, $array);

    }

    /**
     * function getBlockedUsers
     * Compatible with RLPlurkAPI
     * Get my list of blocked users.
     *
     * @return array Returns an array of blocked users.
     */
    function getBlockedUsers()
    {
        return $this->get_blocks(0);
    }

    /**
     * funciton block_user
     *
     * @param int $user_id The id of the user that should be blocked.
     * @return boolean
     * @see /API/Blocks/block
     */
    function block_user($user_id = 0)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key' => $this->api_key,
            'user_id' => $user_id,
        );

        $this->plurk(PLURK_BLOCK, $array);
        return ($this->http_status == '200') ? TRUE : FALSE;
    }

    /**
     * function blockUser
     * Compatible with RLPlurkAPI
     *
     * @param array $array_uid The array of user ids to be blocked.
     * @return bool Returns true if successful or false otherwise.
     */
    function blockUser($array_uid = null)
    {
        $return = true;

        if (!is_array($array_uid))
        {
            return false;
        }

        foreach ($array_uid as $friend_id)
        {
            $return = ($return && $this->block_user($friend_id));
        }

        return $return;
    }

    /**
     * function unblock_user
     *
     * @param user_id: The id of the user that should be unblocked.
     * @return boolean
     * @see /API/Blocks/unblock
     */
    function unblock_user($user_id = 0)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key' => $this->api_key,
            'user_id' => $user_id,
        );

        $this->plurk(PLURK_UNBLOCK, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;
    }

    /**
     * function unblockUser
     * Compatible with RLPlurkAPI
     *
     * @param array $array_uid The array of user ids to be unblocked.
     * @return bool Returns true if successful or false otherwise.
     */
    function unblockUser($array_uid = null)
    {
        $return = true;

        if (!is_array($array_uid))
        {
            return false;
        }

        foreach ($array_uid as $friend_id)
        {
            $return = ($return && $this->unblock_user($friend_id));
        }

        return $return;
    }

    /**
     * function get_cliques
     *
     * @return JSON object
     * @see /API/Cliques/get_cliques
     */
    function get_cliques()
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array('api_key' => $this->api_key);

        return $this->plurk(PLURK_GET_CLIQUES, $array);
    }

    /**
     * function get_clique
     * get users from clique
     *
     * @param string $clique_name The name of the new clique
     * @return array
     * @see /API/Cliques/get_clique
     */
    function get_clique($clique_name = '')
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key'     => $this->api_key,
            'clique_name' => $clique_name
        );

        return $this->plurk(PLURK_GET_CLIQUE, $array);
    }


    /**
     * function create_clique
     * create clique
     *
     * @param string $clique_name The name of the new clique
     * @return boolean
     * @see /API/Cliques/create_clique
     */
    function create_clique($clique_name = '')
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key'     => $this->api_key,
            'clique_name' => $clique_name
        );

        $result =  $this->plurk(PLURK_CREATE_CLIQUE, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;

    }

    /**
     * function delete_clique
     * delete clique
     *
     * @param string $clique_name The name of the new clique
     * @return boolean
     * @see
     */
    function delete_clique($clique_name = '')
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key'     => $this->api_key,
            'clique_name' => $clique_name,
        );

        $result = $this->plurk(PLURK_DELETE_CLIQUE, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;

    }

    /**
     * function rename_clique
     * rename clique
     *
     * @param string $clique_name The name of the current clique.
     * @param string $new_name The name of the new clique.
     * @return boolean
     * @see /API/Cliques/rename_clique
     */
    function rename_clique($clique_name = '', $new_name = '')
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key'     => $this->api_key,
            'clique_name' => $clique_name,
            'new_name'    => $new_name
        );

        $result = $this->plurk(PLURK_RENAME_CLIQUE, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;
    }

    /**
     * function add_to_clique
     * add friend to clique
     *
     * @param string $clique_name The name of the clique to add.
     * @param int $user_id The user to add to the clique
     * @return boolean
     * @see /API/Cliques/add
     */
    function add_to_clique($clique_name = '', $user_id = 0)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key'     => $this->api_key,
            'clique_name' => $clique_name,
            'user_id'     => $user_id
        );

        $result = $this->plurk(PLURK_ADD_TO_CLIQUE, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;
    }

    /**
     * function remove_from_clique()
     * remove friend from clique
     *
     * @param string $clique_name The name of the clique to delete
     * @param int $user_id The user to remove from the clique
     * @return boolean
     * @see /API/Cliques/remove
     */
    function remove_from_clique($clique_name = '', $user_id = 0)
    {
        if( ! $this->is_login) exit(PLURK_NOT_LOGIN);

        $array = array(
            'api_key'     => $this->api_key,
            'clique_name' => $clique_name,
            'user_id'     => $user_id
        );

        $result = $this->plurk(PLURK_REMOVE_FROM_CLIQUE, $array);

        return ($this->http_status == '200') ? TRUE : FALSE;
    }

    /**
     * function uidToNickname
     * Compatible with RLPlurkAPI
     * Translates a uid to the corresponding nickname.
     *
     * @param int $uid The uid to be translated.
     * @return string The nick_name corresponding to the given uid.
     * @todo not test yet. (?)
     */
    function uidToNickname($uid)
    {
        if (!is_int($uid)) {
            return false;
        }

        if ($uid == $this->uid) {
            return (string) $this->nick_name;
        }

        foreach ($this->get_friends() as $friend) {
            if ($friend['uid'] == $uid) {
                return (string) $friend['nick_name'];
            }
        }

        /*
         * We don't know who this is, just return the string "User $uid"
         */
        return 'User ' . $uid;

    }

    /**
     * function nicknameToUid
     * Compatible with RLPlurkAPI
     * Retrieve a user's uid from given his/her plurk nick name.
     *
     * @param string $string_nick_name The nickname of the user to retrieve the uid from.
     * @return int The uid of the given nickname.
     * @todo not implemented yet
     */
    function nicknameToUid($string_nick_name)
    {
        printf("nicknameToUid: This function is not implemented yet.\n");
    }

    /**
     * function get_login_status
     * Get login status
     *
     * @return boolean
     */
    function get_login_status()
    {
        return ($this->is_login) ? TRUE : FALSE;
    }

    /**
     * function isLoggedIn
     * Compatible with RLPlurkAPI
     *
     * @return boolean true if we are logged in, false otherwise.
     */
    function isLoggedIn()
    {
        return $this->get_login_status();
    }

    /**
     * function get_http_status
     * Get HTTP Status Code
     *
     * @return int
     */
    function get_http_status()
    {
        return $this->http_status;
    }

    /**
     * function get_http_response
     * Get HTTP Server Response
     *
     * @return int
     */
    function get_http_response()
    {
        return $this->http_response;
    }

    /**
     * function get_user_info
     * Get user information
     *
     * @return JSON object
     */
    function get_user_info()
    {
        return $this->user_info;
    }

    /**
     * function uidToUserinfo
     * Compatible with RLPlurkAPI
     * Retrieve a user's information given a plurk uid.
     *
     * @param int $int_uid The uid of the plurk member.
     * @return array The associative array of user information.
     * @todo not implemented yet
     */
    function uidToUserinfo($int_uid)
    {
        printf("uidToUserinfo: This function is not implemented yet.\n");
    }


    /**
     * function getPermalink
     * Compatible with RLPlurkAPI
     * Convert a plurk ID to a permalink URL.
     *
     * @param int $plurk_id The plurk ID number.
     * @return string The permalink URL address.
     */
    function getPermalink($plurk_id)
    {
        if (!is_int($plurk_id))
        {
            return '';
        }

        return "http://www.plurk.com/p/" . base_convert($plurk_id, 10, 36);
    }

    /**
     * function permalinkToPlurkID
     * Compatible with RLPlurkAPI
     * Convert a plurk permalink URL address to a plurk ID.
     *
     * @param string $string_permalink The plurk permalink URL address.
     * @return int The plurk ID number.
     */
    function permalinkToPlurkID($string_permalink)
    {
        $base36number = str_replace('http://www.plurk.com/p/', '', $string_permalink);

        return (int) base_convert($base36number, 36, 10);
    }

    /**
     * function getCities
     * Compatible with RLPlurkAPI
     * Get cities.
     *
     * @param int $int_uid       The user's UID to be passed to the getCities.
     * @param int $int_region_id The region's ID to be passed to the getCities.
     * @return array The associative array of cities within a region and their details.
     * @todo not implemented yet
     */
    function getCities($int_uid = null, $int_region_id = null)
    {
        printf("getCities: This function is not implemented yet.\n");
    }

    /**
     * function getRegions
     * Compatible with RLPlurkAPI
     * Get the regions in a given country.
     *
     * @param int $int_uid        The user's UID to be passed to the getRegions.
     * @param int $int_country_id The country's ID to be passed to the getRegions.
     * @return array The associative array of regions in a country and their details.
     * @todo not implemented yet
     */
    function getRegions($int_uid = null, $int_country_id = null)
    {
        printf("getRegions: This function is not implemented yet.\n");
    }

    /**
     * function getCountries
     * Compatible with RLPlurkAPI
     * Get thie list of countries.
     *
     * @param int $int_uid The user's UID to be passed to the getCountries.
     * @return array The associative array of countries and their details.
     * @todo not implemented yet
     */
    function getCountries($int_uid = null)
    {
        printf("getCountries: This function is not implemented yet.\n");
    }

}
