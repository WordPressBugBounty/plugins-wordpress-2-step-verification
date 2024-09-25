<?php
/**
 * Created by PhpStorm.
 * User: as247
 * Date: 23-Oct-18
 * Time: 10:46 PM
 */

/**
 * Class Wp2sv_Abstract
 * @property-read Wp2sv_Recovery $recovery
 * @property-read Wp2sv_AppPass $app_password
 * @property-read Wp2sv_Backup_Code $backup_code
 * @property-read Wp2sv_Auth $auth
 * @property-read Wp2sv_OTP $otp
 * @property-read Wp2sv_Model $model
 * @property-read WP_User $user
 * @property-read Wp2sv_Handler $handler
 * @property-read Wp2sv_Email $email
 *
 */
class Wp2sv_Abstract
{
    /**
     * @var Wordpress2StepVerification
     */
    protected $wp2sv;

    function __construct($wp2sv)
    {
        $this->wp2sv=$wp2sv;
        $this->_construct();
    }
    function __get($name)
    {
        if($name=='app_password'){
            $name='app_pass';
        }
        if($name=='user'){
            return $this->wp2sv->user();
        }

        return $this->wp2sv->make($name);
    }
    protected function _construct(){

    }

    function post($var,$default=null){
        return isset($_POST[$var])?$_POST[$var]:$default;
    }
    function get($var,$default=null){
        return isset($_GET[$var])?$_GET[$var]:$default;
    }
    function request($var,$default=null){
        return isset($_REQUEST[$var])?$_REQUEST[$var]:$default;
    }
    function url($args=[],$admin=false){
        return $this->wp2sv->url($args,$admin);
    }
}
