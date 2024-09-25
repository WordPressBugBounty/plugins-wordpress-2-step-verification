<?php
class Wp2sv_Setup extends Wp2sv_Base {
    protected $page;

    function _construct(){
        add_action('admin_menu',array($this,'menu'));
        add_action('network_admin_menu',array($this,'menu'));
        add_action('template_redirect',array($this,'menu'),100);
        add_action( 'admin_notices', array($this,'notice') );
        add_action('wp_ajax_wp2sv',[$this,'ajax']);
        add_action('wp_ajax_wp2sv_setup_data',[$this,'setupData']);
        add_action('admin_enqueue_scripts', function($hook){
            if($this->page==$hook){
                do_action('wp2sv_setup_scripts','admin');
            }
        });
        add_action('wp_enqueue_scripts',function(){
            $post=get_post();
            if($post && has_shortcode($post->post_content,'wp2sv_setup')) {
                do_action('wp2sv_setup_scripts','page');
            }
        });
        add_action( 'profile_personal_options', array( $this, 'profilePersonalOptions') );
        add_shortcode('wp2sv_setup',function (){
            if(apply_filters('wp2sv_setup_render_shortcode',is_page())) {
                update_option('wp2sv_setup_page_id', get_the_ID(),false);
                return $this->view();
            }
        });
        add_action('wp2sv_setup_scripts',function(){
            $this->enqueueScripts();
        });
    }

	/**
	 * @return WP_Query
	 */
    protected function getTheWpQuery(){
        return $GLOBALS['wp_the_query'];
    }
    function profilePersonalOptions(){
        ?>
        <h3><?php _e('2-Step Verification','wordpress-2-step-verification');?></h3>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><?php _e('Status:','wordpress-2-step-verification');?></th>
                <td><label><?php echo $this->model->status();?></label>
                    <a class="button" href="<?php echo $this->url()?>"> <?php _e('Edit','wordpress-2-step-verification');?> </a></td>
            </tr>
            </tbody>
        </table>
        <?php
    }
    function enqueueScripts(){
        $setupData = $this->getData();
        wp_add_inline_script('wp2sv-setup',"var wp2sv_setup = " . wp_json_encode( $setupData ) . ';','before');
        wp_enqueue_style('wp2sv-setup');
        wp_enqueue_script('wp2sv-setup');

    }
    function setupData(){
        wp_send_json($this->getData());
    }
    function getData(){
        $unusedBackupCodes=$this->backup_code->getCodes('unused');
        if(function_exists('wp_timezone_string')){
            $timezone=wp_timezone_string();
        }else{
            $timezone=get_option('gmt_offset');
            if($timezone>=0){
                $timezone='UTC+'.$timezone;
            }else {
                $timezone = 'UTC-' . $timezone;
            }
        }
        return [
            'time'=>[
                'local'=>$this->otp->localTime(),
                'server'=>$this->otp->time(),
                'timezone'=>$timezone,
            ],
            'otp'=>$this->otp,
            'user_login'=>$this->user->user_login,
            'user_email'=>$this->user->user_email,
            'user_display_name'=>$this->user->display_name,
            'enabled'=>$this->isEnabled(),
            'enabled_at'=>$this->model->enabled_at,
            'emails'=>$this->model->getEmails(),
            'mobile_dev'=>wp2sv_get_device_name($this->model->mobile_dev),
            'mobile_at'=>'',
            'backup_codes'=>$unusedBackupCodes,
            'app_passwords'=>$this->getAppPasswords(),
            'active_sessions'=>count(Wp2sv_Session_Tokens::get_instance($this->user->ID)->get_all()),
            '_nonce'=>wp_create_nonce('wp2sv_setup'),
        ];
    }
    function getAppPasswords(){
        $passwords=$this->app_password->getPasswords();
        foreach ($passwords as $i=>&$password){
            $password['i']=$i;
        }
        return array_values($passwords);
    }

    function menu(){
        if(!function_exists('add_users_page')){
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        $this->page=add_users_page( __('Wordpress 2-step verification','wordpress-2-step-verification'), __('2-Step Verification','wordpress-2-step-verification'), 'read', 'wp2sv',
            array($this,'render')
        );

        add_action('admin_bar_menu',array($this, 'adminBar'));
    }
    protected function getQrCodeData($secret){
        $display=$this->user->user_login;
        $name=parse_url(get_bloginfo('wpurl'),PHP_URL_HOST);
        $display=$name.'%3A'.$display;
        $data=sprintf("otpauth://totp/%s?secret=%s&issuer=%s",$display,$secret,$name);
        return $data;
    }
    function getQrCodeUrl($data, $size=144){
        $data=urlencode($data);
        $qr_url=sprintf('https://api.qrserver.com/v1/create-qr-code/?size=%1$sx%1$s&data=%2$s',$size,$data);
        //$qr_url2=sprintf("https://quickchart.io/qr?size=%s&text=%s",$size,$data);
        return $qr_url;
    }

    /**
     * @param WP_Admin_Bar $wp_admin_bar
     * @return void
     */
    function adminBar($wp_admin_bar){
        $logout=$wp_admin_bar->get_node('logout');
        $wp_admin_bar->remove_menu('logout');
        $wp_admin_bar->add_menu( array(
            'parent' => 'user-actions',
            'id'     => '2-step-verification',
            'title'  => __( '2-Step Verification' , 'wordpress-2-step-verification' ),
            'href' => menu_page_url('wp2sv',false),
        ) );
        $wp_admin_bar->add_menu( get_object_vars($logout) );
    }
    function view(){
        do_action('wp2sv_setup_view');
        if($this->request('wp2sv-page')=='app-passwords'){
            return Wp2sv_View::make('setup.app-passwords');
        }else {
			return Wp2sv_View::make('setup.index',$this->getData());
        }

    }
    function notice(){
        $message=$this->model['message'];
        if($message){
            printf('<div class="notice notice-success is-dismissible"><p>%s</p></div>',$message);
        }
    }

    function ajax(){
        $action=$this->request('wp2sv_action');
        $action=str_replace('-','_',$action);
        $allowed_actions=[
            'qrcode','send_email','test_code',
            'enable','disable','backup_codes','time_sync',
            'remove_app','remove_email','primary_email',
            'password_create','password_remove','download_backup_codes',
            'destroy_other_sessions',
        ];
        if(!in_array($action,$allowed_actions)){
            wp_send_json_error(['message'=>'action not allowed']);
        }
        if(!wp_verify_nonce($this->request('wp2sv_nonce'),'wp2sv')){
            wp_send_json_error(['message'=>'action not allowed']);
        }
        $method='ajax_'.$action;
        $result=[];
        if(method_exists($this,$method)) {
            $result = $this->$method();
        }
        if($result) {
            wp_send_json_success($result);
        }else{
            wp_send_json_error();
        }
    }
    protected function ajax_download_backup_codes(){
        echo Wp2sv_View::make('setup.backup-codes-txt',[
                'codes'=>$this->backup_code->getCodes('codes'),
            'user_login'=>$this->user->user_login,
        ]);
        die;
    }
    protected function ajax_password_create(){
        return $this->app_password->create($this->request('name'));
    }
    protected function ajax_password_remove(){
        return $this->app_password->revoke($this->request('index'));
    }
    protected function ajax_time_sync(){
        $this->otp->syncTime();
        return array('server'=>$this->otp->time(),'local'=>$this->otp->localTime());
    }
    protected function ajax_remove_app(){
        $this->model->mobile_dev='';
        return true;
    }
    protected function ajax_remove_email(){
        $email=$this->request('email');
        return $this->model->removeEmail($email);
    }
    protected function ajax_primary_email(){
        $email=$this->request('email');
        return $this->model->primaryEmail($email);

    }
    protected function ajax_qrcode(){
        $secret=$this->otp->generateSecretKey();
        $data=$this->getQrCodeData($secret);
        $data = [
            'data'=>$data,
            'url'=>$this->getQrCodeUrl($data),
            'secret'=>$secret
        ];
        wp_send_json_success($data);
    }
    protected function ajax_send_email(){
        $email=$this->post('email');
        $email=sanitize_email($email);
        $dupe=md5(strtolower($email));//dupe check
        $emails=$this->model->getEmails(false);
        if($this->model->enabled && isset($emails[$dupe])){
            wp_send_json_error(['message'=>__('You are already using this email as a second step.','wordpress-2-step-verification')]);
        }

        if(!$email){
            wp_send_json_error(['message'=>__('Please enter a valid email.','wordpress-2-step-verification')]);
        }
        if($result=$this->email->sendCodeToEmail($email)){
            wp_send_json_success();
        }else {
            wp_send_json_error(['message'=>__('Failed to send email. Please make sure wp mail is configured properly. You can pick one of these <a target="_blank" href="http://wp.org/plugins/search/smtp+mail/">plugins</a> to help configure wp mail.','wordpress-2-step-verification')]);
        }
    }
    protected function ajax_test_code(){
        $code=$this->request('code');
        $secret=$this->request('secret');
        if(!$secret){
            $secret=$this->model->secret_key;
        }
        $changeDevice=$this->request('changeDevice');
        $updateEmail=$this->request('updateEmail');
        $email=sanitize_email($this->request('email'));
        if($email){
            $checkResult=$this->email->check($code);
        }else{
            $checkResult=$this->otp->check($code,1,$secret);
        }
        if ($checkResult) {
            if($changeDevice){
                $this->configureEmailOrApp();
            }elseif($updateEmail&&$email){
                $this->updateEmail($email);
            }
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }
    protected function ajax_enable(){
        if($this->configureEmailOrApp()) {
            $this->model->enable();
            wp_send_json_success();
        }
        wp_send_json_error();
    }
    protected function ajax_disable(){
        $this->model->disable(true);
    }
    protected function ajax_backup_codes(){
        if(!$this->backup_code->getCodes() || $this->request('generate')){
            $this->backup_code->generate();
        }
        $backup_codes=$this->backup_code->getCodes();
        $codes=[];
        foreach($backup_codes['codes'] as $code=>$unsed){
            $codes[]=['code'=>implode(' ',str_split($code,4)),'used'=>!$unsed];
        }
        $codes=array_chunk($codes,2,true);
        $backup_codes['codes']=$codes;
        $backup_codes['date']=mysql2date(get_option( 'date_format' ),$backup_codes['last']);
        wp_send_json_success($backup_codes);
    }
    protected function ajax_destroy_other_sessions(){
		$this->auth->destroyOthers();
		wp_send_json_success();
    }
    protected function updateEmail($email){
        $this->model->updateEmail($email);
        wp_send_json_success(['emails'=>$this->model->getEmails()]);
    }
    protected function configureEmailOrApp(){
        if($email=$this->post('email')) {
            $this->model->updateEmail($email);
            if(!$this->auth->validateCookie()){
                $this->auth->setCookie($this->model->ID);
            }
        }elseif($device=$this->post('device')){
            $secret=$this->request('secret');
            if(!in_array($device,['android','iphone'])){
                $device='android';
            }
            $this->model->secret_key=$secret;
            $this->model->mobile_dev=$device;
            $this->auth->setCookie($this->model->ID);//Secret key changed
            $this->auth->destroyOthers();
        }

        return true;
    }
    function render(){
        echo $this->view();
    }
}
