<?php

class Wp2sv_Force extends Wp2sv_Base
{
    protected $enabled=false;
    protected $mode='';
    protected $redirect_url='';
    protected function _construct()
    {
        $this->enabled=wp2sv_setting('force_enable',false);
        $this->mode=wp2sv_setting('force_mode','popup');
        $this->redirect_url=wp2sv_setting('force_redirect_url','');
        //Handle at template redirect to make sure admin_menu registered for menu_page_url
        add_action('template_redirect',[$this,'handle'],101);
        add_action('admin_init',[$this,'handle']);
    }
    function handle(){
        if($this->isEnabledForce() && !$this->is_ajax()){
            if($this->mode==='popup'|| $this->mode==='notice'){
                $this->handlePopup();
            }else{
                $this->handleRedirect();
            }
        }
    }
    protected function is_ajax(){
        return function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : (defined( 'DOING_AJAX' ) && DOING_AJAX);
    }
    function handleRedirect(){
        $redirectUrl=$this->getSetupUrl();
        if($redirectUrl && !$this->isInSetupPage() && !$this->isCurrentUrlMatch($redirectUrl)){
            wp_redirect($redirectUrl);
            exit;
        }
        add_action('wp_enqueue_scripts',[$this,'enqueueScripts']);
        add_action('wp_footer',[$this,'renderNotice']);
    }
    function handlePopup(){
        $setupUrl=$this->getSetupUrl();
        if($setupUrl && !$this->isInSetupPage() && !$this->isCurrentUrlMatch($setupUrl)){
            add_action('wp_enqueue_scripts',[$this,'enqueueScripts']);
            add_action('admin_enqueue_scripts',[$this,'enqueueScripts']);
            add_action('wp_footer',[$this,'renderPopup']);
            add_action('admin_footer',[$this,'renderPopup']);
            add_action('wp2sv_setup_header',[$this,'hidePopup']);
        }
    }
    function isEnabledForce(){
        if(!$this->enabled){
            return false;
        }
        $enabledRoles=wp2sv_setting('force_roles',[]);
        $user=wp_get_current_user();
        if($user instanceof WP_User){
            $userRoles=$user->roles??[];
            foreach ($userRoles as $role){
                if(in_array($role,$enabledRoles)){
                    return true;
                }
            }
        }
        return false;
    }

    function enqueueScripts(){
        wp_enqueue_style('wp2sv-popup');
    }
    function renderPopup(){
        echo Wp2sv_View::make('setup.force-popup',[
            'setup_url'=>$this->getSetupUrl(),
            'dismissible'=>$this->mode==='notice',
        ]);
    }
    function hidePopup(){
        echo '<style>#wp2sv-force-popup{display:none}</style>';
    }
    function renderNotice(){
        echo Wp2sv_View::make('setup.force-notice',[
            'setup_url'=>$this->getSetupUrl(),
        ]);
    }
    protected function getSetupUrl(){
        $redirect_url=$this->redirect_url;
        if(!$redirect_url){
            if(function_exists('wc_get_endpoint_url')) {
                //Generate woocommerce url to wp2sv_setup page
                if(wp2sv_setting('show_in_woocommerce')) {
                    $redirect_url = wc_get_endpoint_url('wp2sv-setup', '', wc_get_page_permalink('myaccount'));
                }else{
                    //as woocommerce prevent access to backend page, so we use a frontend page when available
                    if($setup_page=wp2sv_setup_page_id()){
                        $redirect_url=get_permalink($setup_page);
                    }
                }
            }else{
                //Generate wp2sv_setup page url
                $redirect_url=$this->url();
            }
        }
        return $redirect_url;
    }

    function isInSetupPage(){
        if(function_exists('wc_get_endpoint_url') && wp2sv_setting('show_in_woocommerce')){
            return is_wc_endpoint_url('wp2sv-setup');
        }
        return wp2sv_is_setup_page();
    }
    function isCurrentUrlMatch($redirect_url){
        $redirectPath=parse_url($redirect_url,PHP_URL_PATH);
        $redirectQuery=parse_url($redirect_url,PHP_URL_QUERY);
        $redirectQuery=wp_parse_args($redirectQuery);
        if($redirectPath==='/'){
            //compare query
            foreach ($redirectQuery as $key=>$value){
                $currentValue=$_GET[$key]??'';
                if($currentValue!==$value){
                    return false;
                }
            }

        }
        $currentPathFromRequest=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
        return $redirectPath===$currentPathFromRequest;
    }
}