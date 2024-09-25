<?php
!defined('MINUTE_IN_SECONDS')&&define( 'MINUTE_IN_SECONDS', 60 );
!defined('HOUR_IN_SECONDS')&&define( 'HOUR_IN_SECONDS',   60 * MINUTE_IN_SECONDS );
!defined('DAY_IN_SECONDS')&&define( 'DAY_IN_SECONDS',    24 * HOUR_IN_SECONDS   );

/**
 * Class Wp2sv_Auth
 */
class Wp2sv_Auth{
    protected $cookie_name;
    protected $cookie_name_secure;
    protected $trusted_cookie_name;
    protected $wp2sv;
    protected $recentlyCreatedToken;
    function __construct($wp2sv){
        $this->wp2sv=$wp2sv;
        $this->cookie_name='wp2sv_'.COOKIEHASH;
        $this->cookie_name_secure='wp2sv_sec_'.COOKIEHASH;
        $this->trusted_cookie_name='wp2sv_'.md5(get_current_user_id());
    }
    function isTrusted(){
        return !empty($_COOKIE[$this->trusted_cookie_name]);
    }
    function getToken(){
        if($this->recentlyCreatedToken){
            return $this->recentlyCreatedToken;
        }
        if ( ! $cookie_elements = $this->parseCookie() ) {
            return false;
        }
        return $cookie_elements['token'];
    }
    function destroyOthers(){
        $manager = Wp2sv_Session_Tokens::get_instance( get_current_user_id() );
        $manager->destroy_others($this->getToken());
    }
    function validateCookie(){
        if ( ! $cookie_elements = $this->parseCookie() ) {
		  return false;
    	}
        $username = $cookie_elements['username'];
        $hmac = $cookie_elements['hmac'];
        $token = $cookie_elements['token'];
        $expired = $expiration = $cookie_elements['expiration'];

    	// Quick check to see if an honest cookie has expired
    	if ( $expired < time() ) {
    		return false;
    	}
    
    	$user = get_user_by('login', $username);
    	if ( ! $user ) {
    		return false;
    	}
        if($user->ID !== get_current_user_id()){//Id not matched
            return false;
        }
    	$pass_frag = substr($user->user_pass, 8, 4);

        $secret=Wp2sv_Model::forUser($user)->secret_key;
        $key = wp_hash( $user->user_login . '|' . $pass_frag . '|' . $secret . '|' . $expiration . '|' . $token  );
        // If ext/hash is not present, compat.php's hash_hmac() does not support sha256.
        $algo = function_exists( 'hash' ) ? 'sha256' : 'sha1';
        $hash = hash_hmac( $algo, $user->user_login . '|' . $expiration . '|' . $token, $key );
    	if ( !hash_equals( $hash, $hmac ) ) {
    		return false;
    	}
        $manager = Wp2sv_Session_Tokens::get_instance( $user->ID );
        if ( ! $manager->verify( $token ) ) {
            return false;
        }
    
    	return $user->ID;
    }
    function generateCookie($user_id, $expiration, $token=''){
        $user = get_userdata($user_id);
        if(!$user){
            return '';
        }
        if ( ! $token ) {
            $manager = Wp2sv_Session_Tokens::instance( $user_id );
            $token = $manager->create( $expiration );
            $this->recentlyCreatedToken=$token;
        }

    	$pass_frag = substr($user->user_pass, 8, 4);
        $secret=Wp2sv_Model::forUser($user)->secret_key;
        $key = wp_hash( $user->user_login . '|' . $pass_frag . '|' . $secret . '|' . $expiration . '|' . $token  );
        // If ext/hash is not present, compat.php's hash_hmac() does not support sha256.
        $algo = function_exists( 'hash' ) ? 'sha256' : 'sha1';
        $hash = hash_hmac( $algo, $user->user_login . '|' . $expiration . '|' . $token, $key );
    	$cookie = $user->user_login .'|'. $expiration . '|'. $token . '|' . $hash;
        return $cookie;
    }
    function parseCookie($secure = ''){
        if ( '' === $secure ) {
            $secure = is_ssl();
        }
        if($secure) {
            $cookie_name = $this->cookie_name_secure;
        }else{
            $cookie_name = $this->cookie_name;
        }
        if ( empty($_COOKIE[$cookie_name]) )
			return false;
		$cookie = $_COOKIE[$cookie_name];
        $cookie_elements = explode('|', $cookie);
    	if ( count($cookie_elements) != 4 )
    		return false;
        
    	list($username ,$expiration, $token, $hmac) = $cookie_elements;
    	return compact('username', 'expiration','token', 'hmac');
    }
    function setCookie($user_id, $remember = false, $secure = ''){
        $remember=(bool)$remember;
        if ( $remember ) {
    		$expiration = time() + apply_filters('wp2sv_cookie_expiration', 30 * DAY_IN_SECONDS , $user_id, $remember);
            $expire = $expiration + ( 12 * HOUR_IN_SECONDS );
    	} else {
    		$expiration = time() + apply_filters('wp2sv_cookie_expiration', 2 * DAY_IN_SECONDS, $user_id, $remember);
    		$expire = 0;
    	}
        if ( '' === $secure ) {
            $secure = is_ssl();
        }
        $auth_cookie=$this->generateCookie($user_id,$expiration);
        if($secure) {
            $cookie_name = $this->cookie_name_secure;
        }else{
            $cookie_name = $this->cookie_name;
        }
        setcookie($cookie_name, $auth_cookie, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure, true);
        setcookie($this->trusted_cookie_name, $remember, time()+365 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, false, true);
	    if ( COOKIEPATH != SITECOOKIEPATH ){
	       	setcookie($cookie_name, $auth_cookie, $expire, SITECOOKIEPATH, COOKIE_DOMAIN, $secure, true);
            setcookie($this->trusted_cookie_name, $remember, time()+365 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, false, true);
        }
    }
    function clear_cookie(){
        setcookie($this->cookie_name,' ',time() - 31536000,COOKIEPATH,COOKIE_DOMAIN);
        setcookie($this->cookie_name,' ',time() - 31536000,SITECOOKIEPATH,COOKIE_DOMAIN);
        setcookie($this->cookie_name_secure,' ',time() - 31536000,COOKIEPATH,COOKIE_DOMAIN);
        setcookie($this->cookie_name_secure,' ',time() - 31536000,SITECOOKIEPATH,COOKIE_DOMAIN);
        setcookie('wp2sv_email_sent',' ',time() - 31536000,'/',COOKIE_DOMAIN);
    }
}