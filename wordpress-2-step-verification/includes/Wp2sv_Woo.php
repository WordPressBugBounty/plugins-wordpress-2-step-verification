<?php


class Wp2sv_Woo extends Wp2sv_Base
{
	function _construct()
	{
		if(wp2sv_setting('show_in_woocommerce')){
			$this->hooks();
		}

	}
	function hooks(){
		add_filter('woocommerce_account_menu_items', [$this, 'accountMenuItems']);
		add_filter('woocommerce_get_query_vars', [$this, 'queryVars']);
		add_action('woocommerce_account_wp2sv-setup_endpoint', [$this, 'setupPage']);
		add_filter('woocommerce_endpoint_wp2sv-setup_title', [$this, 'pageTitle']);
		add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
		add_action('woocommerce_settings_pages', [$this, 'endpointOption']);

	}

	function enqueueScripts()
	{
		if (function_exists('is_wc_endpoint_url') &&
			is_wc_endpoint_url('wp2sv-setup')) {
			do_action('wp2sv_setup_scripts', 'woo');
		}
	}

	function endpointOption($fields)
	{
		$pos = 0;
		foreach ($fields as $field) {
			if ($field['id'] === 'woocommerce_logout_endpoint') {
				break;
			}
			$pos++;
		}
		if (isset($pos)) {
			array_splice($fields, $pos + 1, 0, [[
				'title' => __('Wp2sv', 'woocommerce'),
				'desc' => __('Endpoint for the "My account &rarr; 2-Step verification" page.', 'woocommerce'),
				'id' => 'woocommerce_wp2sv_endpoint',
				'type' => 'text',
				'default' => 'wp2sv_setup',
				'desc_tip' => true,
			]]);
		}
		return $fields;
	}

	function pageTitle()
	{
		return __('2-Step Verification', 'wordpress-2-step-verification');
	}

	function setupPage()
	{
		echo do_shortcode('[wp2sv_setup]');
	}

	function queryVars($queries)
	{
		$queries['wp2sv-setup'] = get_option('woocommerce_wp2sv_endpoint', 'wp2sv-setup');
		return $queries;
	}

	function accountMenuItems($items)
	{
		$keys = array_keys($items);
		$values = array_values($items);
		$pos = count($keys);
		foreach ($keys as $pos => $key) {
			if ($key === 'edit-account') {
				$pos++;
				break;
			}
		}
		array_splice($keys, $pos, 0, 'wp2sv-setup');
		array_splice($values, $pos, 0, __('2-Step Verification', 'wordpress-2-step-verification'));
		$items = array_combine($keys, $values);
		return $items;
	}
}
