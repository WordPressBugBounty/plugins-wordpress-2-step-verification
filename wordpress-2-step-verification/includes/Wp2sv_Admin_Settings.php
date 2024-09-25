<?php


class Wp2sv_Admin_Settings
{
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct()
	{
		add_action( 'admin_menu', array( $this, 'page' ) );
		add_action( 'admin_init', array( $this, 'init' ) );
        add_action('update_option_wp2sv_settings',array($this,'updated'),10,2);
	}

    function updated($oldvalue,$value){
        if(!empty($value['show_in_woocommerce'])){
            update_option( 'woocommerce_queue_flush_rewrite_rules', 'yes' );
        }
    }

	/**
	 * Add options page
	 */
	public function page()
	{
		// This page will be under "Settings"
		add_options_page(
			__('Wordpress 2-step verification settings','wordpress-2-step-verification'),
			__('Wp2sv Settings','wordpress-2-step-verification'),
			'delete_users',
			'wp2sv-settings',
			array( $this, 'render' )
		);
	}

	/**
	 * Options page callback
	 */
	public function render()
	{
		// Set class property
		$this->options = wp2sv_setting();
        if(!is_array($this->options)){
            $this->options=[];
        }
        if(!isset($this->options['max_attempts'])){
            $this->options['max_attempts']=5;
        }
        if(!isset($this->options['attempts_lock'])){
            $this->options['attempts_lock']=15;
        }
        if(!isset($this->options['max_emails'])){
            $this->options['max_emails']=10;
        }
        if(!isset($this->options['emails_lock'])){
            $this->options['emails_lock']=30;
        }
        if(!isset($this->options['emails_expiration'])){
            $this->options['emails_expiration']=30;
        }
        //<script src="//unpkg.com/alpinejs" defer></script>
		?>
		<div class="wrap">
			<h1><?php _e('Wp2sv Settings','wordpress-2-step-verification')?></h1>
			<form method="post" action="options.php" x-data="{force_mode:''}">
				<?php
				// This prints out all hidden setting fields
				settings_fields( 'wp2sv_settings' );
                $this->form();
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register and add settings
	 */
	public function init()
	{
		register_setting(
			'wp2sv_settings',
			'wp2sv_settings',
			array( $this, 'sanitize' ) // Sanitize
		);

		add_settings_section(
			'wp2sv_general_section',
			__('General settings','wordpress-2-step-verification'),
			function(){

            },
			'wp2sv_settings'
		);

		add_settings_field(
			'max_attempts',
			__('Max Attempts','wordpress-2-step-verification'),
			function (){
				$this->textField('max_attempts',__('Maximum failed attempts allowed before lockout','wordpress-2-step-verification'),'small-text');
            },
			'wp2sv_settings',
			'wp2sv_general_section'
		);

		add_settings_field(
			'attempts_lock',
			__('Lockout Time','wordpress-2-step-verification'),
			function (){
				$this->textField('attempts_lock',__('minutes','wordpress-2-step-verification'),'small-text');
			},
			'wp2sv_settings',
			'wp2sv_general_section'
		);

		add_settings_field(
			'max_emails',
			__('Max Emails','wordpress-2-step-verification'),
			function (){
				$this->textField('max_emails',__('Max number of emails user may request before lockout','wordpress-2-step-verification'),'small-text');
			}, // Callback
			'wp2sv_settings',
			'wp2sv_general_section'
		);
		add_settings_field(
			'emails_lock',
			__('Email Lockout Time','wordpress-2-step-verification'),
			function (){
				$this->textField('emails_lock',__('minutes','wordpress-2-step-verification'),'small-text');
			}, // Callback
			'wp2sv_settings',
			'wp2sv_general_section'
		);

        add_settings_field(
            'emails_expiration',
            __('Email Expiration','wordpress-2-step-verification'),
            function (){
                $this->textField('emails_expiration',__('minutes','wordpress-2-step-verification'),'small-text');
            }, // Callback
            'wp2sv_settings',
            'wp2sv_general_section'
        );

        add_settings_section(
            'wp2sv_force_section',
            __('Force to enable','wordpress-2-step-verification'),
            function(){

            }, // Callback
            'wp2sv_settings'
        );

        add_settings_field(
            'force_enable',
            __('Enable','wordpress-2-step-verification'), // Title
            function (){
                $this->checkBox('force_enable',__('Force users to enable 2-step','wordpress-2-step-verification'));
            }, // Callback
            'wp2sv_settings',
            'wp2sv_force_section'
        );

        add_settings_field(
            'force_roles',
            __('Roles','wordpress-2-step-verification'),
            function (){
                $this->rolesCheckList('force_roles',__('Force 2-step for selected roles','wordpress-2-step-verification'));
            }, // Callback
            'wp2sv_settings',
            'wp2sv_force_section'
        );

        //Create setting field for force_mode with 2 modes popup and redirect
        add_settings_field(
            'force_mode',
            __('Force Mode','wordpress-2-step-verification'),
            function (){
                $this->forceModeSelection('force_mode',__('Choose to show popup or redirect when wp2sv is not enabled','wordpress-2-step-verification'));
            }, // Callback
            'wp2sv_settings',
            'wp2sv_force_section'
        );
        //Create setting fied for force redirect url
        add_settings_field(
            'force_redirect_url',
            __('Redirect URL','wordpress-2-step-verification'),
            function (){
                $this->urlField('force_redirect_url',__('Custom redirect url','wordpress-2-step-verification'));
            }, // Callback
            'wp2sv_settings',
            'wp2sv_force_section'
        );

		if(class_exists('\WooCommerce')){
			add_settings_section(
				'wp2sv_woo_integration', // ID
				__('WooCommerce Integration','wordpress-2-step-verification'), // Title
				function(){
					echo '';
				}, // Callback
				'wp2sv_settings' // Page
			);
			add_settings_field(
				'wp2sv_show_in_woo', // ID
				__('Show in WooCommerce','wordpress-2-step-verification'), // Title
				array( $this, 'showInMyAccount' ), // Callback
				'wp2sv_settings', // Page
				'wp2sv_woo_integration' // Section
			);
		}
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize( $input )
	{
		$new_input = array();
		if( isset( $input['show_in_woocommerce'] ) )
			$new_input['show_in_woocommerce'] = $input['show_in_woocommerce'] ? '1':'';
		else{
		    $new_input['show_in_woocommerce']='';
		}

		$numericFields=[
		    'max_attempts',
            'attempts_lock',
            'max_emails',
            'emails_lock',
            'emails_expiration'
        ];
		foreach ($numericFields as $field){
            $new_input[$field]=isset($input[$field])?absint($input[$field]):'';
		}
        $new_input['force_enable']=!empty($input['force_enable'])?1:'';
        $new_input['force_mode']=!empty($input['force_mode'])?$input['force_mode']:'popup';
        $new_input['force_redirect_url']=!empty($input['force_redirect_url'])?$input['force_redirect_url']:'';
        $new_input['force_roles']=isset($input['force_roles'])?$input['force_roles']:[];


		return $new_input;
	}

    function form(){
        do_settings_sections('wp2sv_settings');
    }
    function textField($name,$desc='', $class='regular-text'){
        $value=isset($this->options[$name])?$this->options[$name]:'';
        printf('<input type="text" name="wp2sv_settings[%1$s]" value="%2$s" class="%3$s">',$name,$value,$class);
        printf('<p class="description">%s</p>',$desc);
	}

    function urlField($name,$desc=''){
        $value=isset($this->options[$name])?$this->options[$name]:'';
        printf('<input class="regular-text" type="url" name="wp2sv_settings[%1$s]" value="%2$s">',$name,$value);
        printf('<p class="description">%s</p>',$desc);
    }
    function checkBox($name,$desc=''){
        $checked=!empty($this->options[$name]);
        printf('<input type="hidden" name="wp2sv_settings[%1$s]" value=""><label><input type="checkbox" name="wp2sv_settings[%1$s]" value="1" %2$s>%3$s</label>',$name,checked($checked,true,false),$desc);
    }
    function rolesCheckList($name,$desc=''){
        $roles=get_editable_roles();
        $value=isset($this->options[$name])?$this->options[$name]:[];
        printf('<input type="hidden" name="wp2sv_settings[%1$s][]" value="">',$name);
        foreach ($roles as $role=>$roleInfo){
            $checked=in_array($role,$value);
            printf('<label><input type="checkbox" name="wp2sv_settings[%1$s][]" value="%2$s" %3$s>%4$s</label><br>',$name,$role,checked($checked,true,false),$roleInfo['name']);
        }
        printf('<p class="description">%s</p>',$desc);
    }
    function forceModeSelection($name,$desc=''){
        $modes=[
            'notice'=>__('Dismissible popup','wordpress-2-step-verification'),
            'popup'=>__('Popup','wordpress-2-step-verification'),
            'redirect'=>__('Redirect','wordpress-2-step-verification'),
        ];
        $value=isset($this->options[$name])?$this->options[$name]:array_keys($modes)[0];
        printf('<select name="wp2sv_settings[%1$s]">',$name);
        foreach ($modes as $mode=>$label){
            $selected=selected($value,$mode,false);
            printf('<option value="%1$s" %2$s>%3$s</option>',$mode,$selected,$label);
        }
        printf('</select>');
        printf('<p class="description">%s</p>',$desc);
    }
    function showInMyAccount(){
	    $checked=!empty($this->options['show_in_woocommerce']);
	    printf('<input type="hidden" name="wp2sv_settings[show_in_woocommerce]" value=""><label><input type="checkbox" name="wp2sv_settings[show_in_woocommerce]" value="1" %1$s>%2$s</label>',checked($checked,true,false),__('Show Wp2sv setup in WooCommerce My Account page','wordpress-2-step-verification'));
	}
}
