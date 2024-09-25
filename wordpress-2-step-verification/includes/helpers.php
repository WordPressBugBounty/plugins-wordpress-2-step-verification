<?php
/**
 * @return Wp2sv
 */
function wp2sv()
{
    return Wp2sv::getInstance();
}
function wp2sv_is_strict_mode(){
    return defined('WP2SV_STRICT_MODE') && WP2SV_STRICT_MODE;
}

/**
 * @param string $server
 * @param int $port
 * @param int $timeout
 * @return int|string|false
 */
function wp2sv_get_time_ntp($server = '0.pool.ntp.org', $port = 123, $timeout = 5)
{
    $servers = array();
    foreach ((array)$server as $s) {
        if (!is_array($s)) {
            $s = array('host' => $s);
        }
        if (!isset($s['port'])) $s['port'] = $port;
        if (!isset($s['timeout'])) $s['timeout'] = $timeout;
        if (!empty($s['host']))
            $servers[] = $s;
    }
    foreach ($servers as $ntp) {
        try {
            $client = new Wp2sv_Ntp_Client(new Wp2sv_Ntp_Socket($ntp['host'], $ntp['port'], $ntp['timeout']));
            $time = $client->getTime();
        } catch (Exception $e) {
            $time = 0;
        }
        if ($time > 0) {
            return $time;
        }
    }
    $time_stamp = wp_remote_get('http://www.timeanddate.com/scripts/ts.php');

    if (!is_object($time_stamp)) {
        $time_stamp = $time_stamp['body'];
        $time_stamp = explode(' ', $time_stamp);
        $time_stamp = $time_stamp[0];
    } else {
        return 0;
    }
    $time_stamp = (int)$time_stamp;
    if ($time_stamp > 0)
        return $time_stamp;
    return 0;
}


function wp2sv_url($path = '', $echo = false)
{
    return wp2sv()->plugin_url($path, $echo);
}

function wp2sv_public($path = '')
{
    return wp2sv_url('public/' . ltrim($path, '/'), false);
}

function wp2sv_assets($path = '')
{
    return wp2sv_public('assets/' . ltrim($path, '/'));
}

function wp2sv_get_device_name($device)
{
    switch ($device) {
        case 'android':
            $name = 'Android';
            break;
        case 'iphone':
            $name = 'iPhone';
            break;
        case 'blackberry':
            $name = 'BlackBerry';
            break;
        default:
            $name = '';
    }
    return $name;
}

function wp2sv_value($value)
{
    if ($value instanceof Closure) {
        return $value();
    }
    return $value;
}

function wp2sv_str_studly($value)
{
    static $studlyCache = [];
    $key = $value;
    if (isset($studlyCache[$key])) {
        return $studlyCache[$key];
    }

    $value = ucwords(str_replace(['-', '_'], ' ', $value));

    return $studlyCache[$key] = str_replace(' ', '', $value);

}

function wp2sv_setting($key=null,$default=null){
	$settings=get_option('wp2sv_settings');
	if(func_num_args()===0){
		return $settings;
	}
	if(!is_array($settings)){
		$settings=[];
	}
	if(is_array($key)){
		if($default===true){
			$settings=$key;
		}else{
			$settings=$key+$settings;
		}
		update_option('wp2sv_settings',$settings);
	}else {
		if (isset($settings[$key])) {
			return $settings[$key];
		}
	}
	return $default;
}


function wp2sv_array_last($array){
    if(!is_array($array)){
        return null;
    }
    $keys=array_keys($array);
    return $array[end($keys)];
}

function wp2sv_setup_page_id(){
    return apply_filters('wp2sv_setup_page_id', get_option('wp2sv_setup_page_id'));
}
function wp2sv_is_setup_page(){
    return is_page(wp2sv_setup_page_id());
}