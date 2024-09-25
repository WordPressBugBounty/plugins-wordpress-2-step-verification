<?php


class Wp2sv_Compatibility
{
	protected $domain='wordpress-2-step-verification';
	protected $translationFile;
	public function __construct()
	{
		$this->withWordpress4();
	}

	function withWordpress4(){
		wp_register_script('wp-i18n',wp2sv_public('vendor/wp-i18n/i18n.min.js'));
		global $wp_scripts;
		$obj=$wp_scripts->query('wp2sv-setup');
		if($obj) {
			if (!in_array('wp-i18n', $obj->deps, true)) {
				$obj->deps[] = 'wp-i18n';
			}
		}
		$locale=get_locale();
		$this->translationFile=WP_CONTENT_DIR.'/languages/plugins/'.$this->domain.'-'.$locale.'.json';
		if(!file_exists($this->translationFile) && substr($locale,0,2) !=='en'){
			add_action('admin_notices',[$this,'noticeThatTranslationNotExists']);
		}
		$script=$this->getJsonTranslations();
		$data=$wp_scripts->get_data('wp2sv-setup','data');
		if($data){
			$script="$data\n$script";
		}
		$wp_scripts->add_data('wp2sv-setup','data',$script);
	}
	protected function getJsonTranslations($file=''){
		if(!$file){
			$file=$this->translationFile;
		}
		$json_translations='';
		if(file_exists($file)){
			$json_translations=file_get_contents($file);
		}
		if ( ! $json_translations ) {
			// Register empty locale data object to ensure the domain still exists.
			$json_translations = '{ "locale_data": { "messages": { "": {} } } }';
		}

		$output = <<<JS
( function( domain, translations ) {
	var localeData = translations.locale_data[ domain ] || translations.locale_data.messages;
	localeData[""].domain = domain;
	wp.i18n.setLocaleData( localeData, domain );
} )( "{$this->domain}", {$json_translations} );
JS;
		return $output;
	}
	function noticeThatTranslationNotExists(){
		$class = 'notice notice-error';
		$messages=[];
		$messages[] = __('Wordpress 2-step verification is not fully supported for your wordpress version, translation is not supported by default.','wordpress-2-step-verification');
		$translationFile=str_replace(ABSPATH,'',$this->translationFile);
		/* translators: %s is replaced with json translation file path */
		$messages[] = sprintf(__('To fix this issue upgrade to Wordpress 5.x or copy json translation to <code>%s</code>. You may also create empty file to hide this message.','wordpress-2-step-verification'),$translationFile);
		$message=join("<br>",$messages);
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), ( $message ) );
	}
}
