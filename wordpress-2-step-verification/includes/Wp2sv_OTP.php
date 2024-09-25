<?php
class Wp2sv_OTP implements JsonSerializable {
    var $secret_key;
    var $wp2sv;
    var $secret_key_length=32;
    function __construct(Wordpress2StepVerification $wp2sv){
		date_default_timezone_set('UTC');
        $this->wp2sv=$wp2sv;

        if($wp2sv->bound('model')) {
			if (!$wp2sv->model()->secret_key) {
				$wp2sv->model()->secret_key = $this->generateSecretKey();
			}
			$this->setSecretKey($wp2sv->model()->secret_key);
		}
    }

    function check($otp, $timespan=1, $secret=''){
        if(!$otp){
            return false;
        }
        $timespan=intval($timespan);
        if($timespan<1)$timespan=1;
        $otp_pass=$this->generate($timespan,$secret);
        foreach($otp_pass as $pass){
            if($otp==$pass)
                return true;
        }
        return false;
    }
    function time(){
        $wp2sv_local_diff_utc=get_site_option('wp2sv_local_diff_utc');
		return time()-$wp2sv_local_diff_utc;
    }
    function localTime(){
        $gmt=$this->time();
        return $gmt+( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
    }
    function syncTime(){
        $utc=$this->getInternetTime();
        if(!$utc){
            return false;
        }
		$wp2sv_local_diff_utc = time() - $utc;
		update_site_option( 'wp2sv_local_diff_utc', $wp2sv_local_diff_utc );
		return true;
    }
    function getInternetTime(){
        return wp2sv_get_time_ntp();
    }
    function generate($timespan=1, $secret_key=''){
        $timespan=abs(intval($timespan));
		$from = -$timespan;
		$to =  $timespan;
    	$timer = floor( $this->time() / 30 );
    	$this->setSecretKey($secret_key);
    	$secret_key=$this->getDecodedSecretKey();
        $result=array();
        if($secret_key) {
            for ($i = $from; $i <= $to; $i++) {
                $time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $timer + $i);
                $hm = hash_hmac('SHA1', $time, $secret_key, true);
                $offset = ord(substr($hm, -1)) & 0x0F;
                $hashpart = substr($hm, $offset, 4);
                $value = unpack("N", $hashpart);
                $value = $value[1];
                $value = $value & 0x7FFFFFFF;
                $value = $value % 1000000;
                $result[] = $value;
            }
        }
    	return $result;
    }
    function getDecodedSecretKey(){
        if(!$this->secret_key){
            return '';
        }
        return $this->base32Decode($this->secret_key);
    }
    function setSecretKey($key){
        if(!$key){
            return ;
        }
        $this->secret_key=$key;
    }
    function generateSecretKey(){
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // allowed characters in Base32
        $secret = '';
        for ( $i = 0; $i < $this->secret_key_length; $i++ ) {
            $secret .= substr( $chars, rand( 0, strlen( $chars ) - 1 ), 1 );
        }
        return strtolower($secret);
    }
    function base32Decode($input){
        $input=strtoupper($input);
        $keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567=";
        $buffer = 0;
        $bitsLeft = 0;
        $output = array();
        $i = 0;
        $count = 0;
        $stop=strlen($input);
        while ($i < $stop) {
            $val =$input[$i++];
            $val=strpos($keyStr,$val);
            if ($val >= 0 && $val < 32) {
                $buffer <<= 5;
                $buffer |= $val;
                $bitsLeft += 5;
                if ($bitsLeft >= 8) {
                    $output[$count++] = ($buffer >> ($bitsLeft - 8)) & 0xFF;
                    $bitsLeft -= 8;
                }
            }
        }
        if ($bitsLeft > 0) {
            $buffer <<= 5;
            $output[$count++] = ($buffer >> ($bitsLeft - 3)) & 0xFF;
        }
        unset($count);
        $output=array_map('chr',$output);
        $output=implode('',$output);
        return $output;
    }

    function toArray(){
		return [
			'secret_key'=>$this->secret_key,
		];
	}

    #[\ReturnTypeWillChange]
	function jsonSerialize()
	{
		return $this->toArray();
	}
}
