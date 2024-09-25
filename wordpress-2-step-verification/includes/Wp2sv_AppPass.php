<?php
class Wp2sv_AppPass{
    protected $user_id=0;
    function __construct($user_id=0){
        if(!$user_id&&function_exists('get_current_user_id')&&function_exists('wp_get_current_user')){
            $user_id=get_current_user_id();
        }
        $this->user_id=$user_id;
    }
    function create($app_name){
        $pass=$this->randomPassword();
        $the_pass=array(
            'h'=>$this->hashPassword($pass),//the hash
            'c'=>current_time('mysql'),//created time
            'u'=>false,//used
            'n'=>$app_name,
        );
        $passwords=$this->getPasswords();
        $passwords[]=$the_pass;
        end($passwords);
        $the_pass['i']=key($passwords);//index
        $the_pass['p']=$pass;//plaintext pass
        $this->updatePasswords($passwords);
        return $the_pass;
    }
    function verify($password,&$index=false){
        $application_pass=$this->getPasswords();
        if(!is_array($application_pass)){
            return false;
        }
        $password=$this->cleanPass($password);
        foreach($application_pass as $index=> $app_pass){
            if($this->checkPassword($password, $app_pass['h'])){
                $this->passwordUsed($index);
                return true;
            }
        }
        return false;
    }

    function revoke($index){

        if ($password = $this->getPassword($index)) {
            if(class_exists('WP_Session_Tokens')) {
                if (!empty($password['t'])) {
                    $manager = WP_Session_Tokens::get_instance(get_current_user_id());
                    foreach ($password['t'] as $token => $notuse) {
                        $manager->destroy($token);
                    }
                }
            }
            $this->updatePassword($index,false);
            return true;
        }
        return false;
    }
    function attachUserToken($cookie, $app_password_used, $user_id=0){
        if($user_id) {
            $this->user_id = $user_id;
        }

        if($app_password_used!==null&&class_exists('WP_Session_Tokens')&&$this->user_id){
            $cookie_elements = explode('|', $cookie);
            if ( count( $cookie_elements ) !== 4 ) {
                return false;
            }
            $token=$cookie_elements[2];
            if($pass=$this->getPassword($app_password_used)){
                $pass['t'][$token]=true;
                $this->updatePassword($app_password_used,$pass);
                return true;
            }
        }
        return false;
    }
    function getPasswords(){
        $pass=Wp2sv_Model::forUser($this->user_id)->app_passwords;
        if(!is_array($pass)){
            $pass=array();
        }
        return $pass;
    }
    protected function passwordUsed($index){
        $passwords=$this->getPasswords();
        if(isset($passwords[$index]['u'])){
            $passwords[$index]['u']=current_time('mysql');
            $this->updatePasswords($passwords);
        }
    }
    protected function randomPassword($length=16){
        $chars = 'abcdefghijklmnopqrstuvwxyz';
        $password = '';
        for ( $i = 0; $i < $length; $i++ ) {
            $password .= substr($chars, wp_rand(0, strlen($chars) - 1), 1);
        }
        return $password;
    }
    protected function hashPassword($plain){
        return $this->getHasher()->HashPassword($plain);
    }
    protected function checkPassword($pass, $hash){
        return $this->getHasher()->CheckPassword($pass,$hash);
    }

    /**
     * @return PasswordHash
     */
    protected function getHasher(){
        global $wp_hasher;
        // If the stored hash is longer than an MD5, presume the
        // new style phpass portable hash.
        if ( empty($wp_hasher) ) {
            require_once( ABSPATH . WPINC . '/class-phpass.php');
            // By default, use the portable hash from phpass
            $wp_hasher = new PasswordHash(8, true);
        }
        return $wp_hasher;
    }

    protected function getPassword($index){
        $pass=$this->getPasswords();
        return isset($pass[$index])?$pass[$index]:false;
    }
    protected function updatePasswords($passwords){
        if(!is_array($passwords)){
            $passwords=array();
        }
        Wp2sv_Model::forUser($this->user_id)->app_passwords=$passwords;
    }
    protected function updatePassword($key, $pass){
        $passwords=$this->getPasswords();
        if($pass) {
            $passwords[$key] = $pass;
        }else{
            unset($passwords[$key]);
        }
        $this->updatePasswords($passwords);
    }
    protected function cleanPass($password){
        $password=str_replace(' ','',$password);
        return $password;
    }
    protected function hashKey($token){
        // If ext/hash is not present, use sha1() instead.
        if ( function_exists( 'hash' ) ) {
            return hash( 'sha256', $token );
        } else {
            return sha1( $token );
        }
    }


}